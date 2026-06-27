<?php

namespace App\Services;

use App\Models\TestDriveActivityLog;
use App\Models\TestDriveFile;
use App\Models\TestDriveNote;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class TestDriveService
{
    public function query(array $filters = []): Builder
    {
        $query = Ticket::query()
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->with(['user', 'car.brand', 'car.carModel.brand'])
            ->select('support_tickets.*');

        $this->applyFilters($query, $filters);

        return $query
            ->orderByRaw("FIELD(status, 'pending', 'approved', 'completed', 'rejected')")
            ->orderByDesc('created_at')
            ->orderByDesc('ticket_id');
    }

    public function findForShow(int $id): Ticket
    {
        return Ticket::query()
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->with([
                'user',
                'car.brand',
                'car.carModel.brand',
                'statusHistories.changedBy',
                'notes.user',
                'files.uploadedBy',
                'activityLogs.user',
            ])
            ->findOrFail($id);
    }

    public function stats(array $filters = []): array
    {
        $query = Ticket::query()
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->select('support_tickets.*');

        $this->applyFilters($query, $filters);

        $total = (clone $query)->count();
        $approved = (clone $query)->where('status', Ticket::STATUS_APPROVED)->count();
        $completed = (clone $query)->where('status', Ticket::STATUS_COMPLETED)->count();
        $rejected = (clone $query)->where('status', Ticket::STATUS_REJECTED)->count();
        $converted = $this->convertedCount(clone $query);

        return [
            'total' => $total,
            'approved' => $approved,
            'completed' => $completed,
            'rejected' => $rejected,
            'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 1) : 0,
            'conversion_rate' => $total > 0 ? round(($converted / $total) * 100, 1) : 0,
            'converted' => $converted,
        ];
    }

    public function updateStatus(Ticket $booking, array $data, ?User $actor): array
    {
        return DB::transaction(function () use ($booking, $data, $actor): array {
            $lockedBooking = $this->lockBooking($booking->ticket_id);
            $oldStatus = (string) $lockedBooking->status;
            $newStatus = (string) $data['status'];

            if (!Ticket::isValidTestDriveTransition($oldStatus, $newStatus)) {
                throw ValidationException::withMessages([
                    'status' => 'Chuyển trạng thái không hợp lệ.',
                ]);
            }

            if ($oldStatus === $newStatus) {
                return [$this->freshForNotification($lockedBooking), false, false];
            }

            $lockedBooking->forceFill(['status' => $newStatus])->save();

            $lockedBooking->statusHistories()->create([
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'changed_by' => $actor?->getKey(),
                'note' => $this->blankToNull($data['note'] ?? null),
            ]);

            $this->recordActivity(
                $lockedBooking,
                $actor,
                TestDriveActivityLog::ACTION_STATUS_CHANGED,
                Ticket::labelForTestDriveStatus($oldStatus),
                Ticket::labelForTestDriveStatus($newStatus),
                trim(Ticket::labelForTestDriveStatus($oldStatus) . ' → ' . Ticket::labelForTestDriveStatus($newStatus))
            );

            return [
                $this->freshForNotification($lockedBooking),
                true,
                in_array($newStatus, [Ticket::STATUS_APPROVED, Ticket::STATUS_COMPLETED, Ticket::STATUS_REJECTED], true),
            ];
        });
    }

    public function updateAppointment(Ticket $booking, array $data, ?User $actor): Ticket
    {
        return DB::transaction(function () use ($booking, $data, $actor): Ticket {
            $lockedBooking = $this->lockBooking($booking->ticket_id);
            $before = $this->appointmentSnapshot($lockedBooking);
            $payload = [
                'appointment_date' => $data['appointment_date'] ?: null,
                'appointment_time' => $data['appointment_time'] ?: null,
                'showroom' => $this->blankToNull($data['showroom'] ?? null),
                'sales_person' => $this->blankToNull($data['sales_person'] ?? null),
            ];

            $lockedBooking->forceFill($payload)->save();
            $lockedBooking->refresh();

            $after = $this->appointmentSnapshot($lockedBooking);

            if ($before !== $after) {
                $this->recordActivity(
                    $lockedBooking,
                    $actor,
                    TestDriveActivityLog::ACTION_APPOINTMENT_UPDATED,
                    $this->formatSnapshot($before),
                    $this->formatSnapshot($after),
                    'Cập nhật thông tin lịch hẹn'
                );
            }

            return $this->findForShow($lockedBooking->ticket_id);
        });
    }

    public function storeNote(Ticket $booking, string $note, ?User $actor): TestDriveNote
    {
        return DB::transaction(function () use ($booking, $note, $actor): TestDriveNote {
            $lockedBooking = $this->lockBooking($booking->ticket_id);
            $createdNote = $lockedBooking->notes()->create([
                'user_id' => $actor?->getKey(),
                'note' => $note,
            ]);

            $this->recordActivity(
                $lockedBooking,
                $actor,
                TestDriveActivityLog::ACTION_NOTE_ADDED,
                null,
                $note,
                'Thêm ghi chú nội bộ'
            );

            return $createdNote;
        });
    }

    /**
     * @param  array<int, UploadedFile>  $documents
     * @return array<int, TestDriveFile>
     */
    public function storeFiles(Ticket $booking, array $documents, ?User $actor): array
    {
        return DB::transaction(function () use ($booking, $documents, $actor): array {
            $lockedBooking = $this->lockBooking($booking->ticket_id);
            $createdFiles = [];

            foreach ($documents as $document) {
                $path = $document->store('test-drive-files/' . $lockedBooking->ticket_id, 'public');
                $createdFiles[] = $lockedBooking->files()->create([
                    'file_name' => $document->getClientOriginalName(),
                    'file_path' => $path,
                    'uploaded_by' => $actor?->getKey(),
                ]);
            }

            $this->recordActivity(
                $lockedBooking,
                $actor,
                TestDriveActivityLog::ACTION_FILE_UPLOADED,
                null,
                collect($createdFiles)->pluck('file_name')->implode(', '),
                'Upload tài liệu lái thử'
            );

            return $createdFiles;
        });
    }

    public function deleteFile(Ticket $booking, TestDriveFile $file, ?User $actor): void
    {
        $filePath = $file->file_path;

        DB::transaction(function () use ($booking, $file, $actor): void {
            $lockedBooking = $this->lockBooking($booking->ticket_id);
            $lockedFile = TestDriveFile::query()
                ->where('ticket_id', $lockedBooking->ticket_id)
                ->whereKey($file->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $this->recordActivity(
                $lockedBooking,
                $actor,
                TestDriveActivityLog::ACTION_FILE_DELETED,
                $lockedFile->file_name,
                null,
                'Xóa tài liệu lái thử'
            );

            $lockedFile->delete();
        });

        Storage::disk('public')->delete($filePath);
    }

    public function salesPeopleOptions(): array
    {
        $users = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->where('status', true)
            ->orderBy('name')
            ->pluck('name')
            ->filter()
            ->all();

        $assignedNames = Ticket::query()
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->whereNotNull('sales_person')
            ->distinct()
            ->orderBy('sales_person')
            ->pluck('sales_person')
            ->filter()
            ->all();

        return collect($users)
            ->merge($assignedNames)
            ->unique()
            ->values()
            ->all();
    }

    private function applyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('ticket_id', 'like', "%{$search}%")
                        ->orWhere('subject', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('showroom', 'like', "%{$search}%")
                        ->orWhere('sales_person', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('car', function (Builder $carQuery) use ($search): void {
                            $carQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%")
                                ->orWhere('vin', 'like', "%{$search}%")
                                ->orWhere('internal_code', 'like', "%{$search}%")
                                ->orWhereHas('carModel', function (Builder $modelQuery) use ($search): void {
                                    $modelQuery->where('name', 'like', "%{$search}%")
                                        ->orWhereHas('brand', function (Builder $brandQuery) use ($search): void {
                                            $brandQuery->where('name', 'like', "%{$search}%");
                                        });
                                });
                        });
                });
            })
            ->when($filters['status'] ?? null, function (Builder $query, string $status): void {
                $query->where('status', $status);
            })
            ->when($filters['sales_person'] ?? null, function (Builder $query, string $salesPerson): void {
                $query->where('sales_person', $salesPerson);
            })
            ->when($filters['created_from'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($filters['created_to'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '<=', Carbon::parse($date)->endOfDay());
            })
            ->when($filters['appointment_from'] ?? null, function (Builder $query, string $date): void {
                $query->where('appointment_date', '>=', Carbon::parse($date)->toDateString());
            })
            ->when($filters['appointment_to'] ?? null, function (Builder $query, string $date): void {
                $query->where('appointment_date', '<=', Carbon::parse($date)->toDateString());
            });
    }

    private function convertedCount(Builder $query): int
    {
        return $query
            ->whereNotNull('car_id')
            ->whereExists(function ($subQuery): void {
                $subQuery->selectRaw('1')
                    ->from('orders')
                    ->join('order_details', 'order_details.order_id', '=', 'orders.order_id')
                    ->whereColumn('orders.user_id', 'support_tickets.user_id')
                    ->whereColumn('order_details.car_id', 'support_tickets.car_id')
                    ->whereColumn('orders.created_at', '>=', 'support_tickets.created_at')
                    ->whereNotIn('orders.status', [3, '3', 'cancelled', 'canceled', 'cancel']);
            })
            ->count();
    }

    private function lockBooking(int $ticketId): Ticket
    {
        return Ticket::query()
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->whereKey($ticketId)
            ->lockForUpdate()
            ->firstOrFail();
    }

    private function freshForNotification(Ticket $booking): Ticket
    {
        return $booking->fresh(['user', 'car.brand', 'car.carModel.brand']) ?? $booking;
    }

    private function appointmentSnapshot(Ticket $booking): array
    {
        return [
            'appointment_date' => $booking->appointment_date?->format('Y-m-d'),
            'appointment_time' => $booking->appointment_time ? substr((string) $booking->appointment_time, 0, 5) : null,
            'showroom' => $booking->showroom,
            'sales_person' => $booking->sales_person,
        ];
    }

    private function formatSnapshot(array $snapshot): string
    {
        return collect($snapshot)
            ->map(fn ($value, string $key): string => $key . ': ' . ($value ?: 'N/A'))
            ->implode('; ');
    }

    private function recordActivity(
        Ticket $ticket,
        ?User $actor,
        string $action,
        ?string $oldValue,
        ?string $newValue,
        ?string $description = null
    ): void {
        $ticket->activityLogs()->create([
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'old_value' => $this->blankToNull($oldValue),
            'new_value' => $this->blankToNull($newValue),
            'description' => $this->blankToNull($description),
        ]);
    }

    private function blankToNull(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
