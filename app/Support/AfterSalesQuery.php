<?php

namespace App\Support;

use App\Models\ServiceAppointment;
use App\Models\ServiceRecord;
use App\Models\Warranty;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AfterSalesQuery
{
    public static function applyWarrantyFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('warranty_code', 'like', "%{$search}%")
                        ->orWhere('vin', 'like', "%{$search}%")
                        ->orWhere('license_plate', 'like', "%{$search}%")
                        ->orWhereHas('order', function (Builder $orderQuery) use ($search): void {
                            $orderQuery->where('order_code', 'like', "%{$search}%")
                                ->orWhere('order_id', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('car', function (Builder $carQuery) use ($search): void {
                            $carQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('vin', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->where('start_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->where('end_date', '<=', $date))
            ->when(!empty($filters['expiring']), fn (Builder $query) => $query->expiringWithin(30));
    }

    public static function applyAppointmentFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('appointment_code', 'like', "%{$search}%")
                        ->orWhere('service_location', 'like', "%{$search}%")
                        ->orWhereHas('warranty', fn (Builder $warrantyQuery) => $warrantyQuery->where('warranty_code', 'like', "%{$search}%"))
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('car', function (Builder $carQuery) use ($search): void {
                            $carQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('vin', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['service_type'] ?? null, fn (Builder $query, string $type) => $query->where('service_type', $type))
            ->when($filters['assigned_staff_id'] ?? null, fn (Builder $query, int $staffId) => $query->where('assigned_staff_id', $staffId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->where('appointment_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->where('appointment_date', '<=', $date));
    }

    public static function applyRecordFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('record_code', 'like', "%{$search}%")
                        ->orWhere('problem_description', 'like', "%{$search}%")
                        ->orWhere('work_performed', 'like', "%{$search}%")
                        ->orWhereHas('serviceAppointment', fn (Builder $appointmentQuery) => $appointmentQuery->where('appointment_code', 'like', "%{$search}%"))
                        ->orWhereHas('warranty', fn (Builder $warrantyQuery) => $warrantyQuery->where('warranty_code', 'like', "%{$search}%"))
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('car', function (Builder $carQuery) use ($search): void {
                            $carQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('vin', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $query, string $status) => $query->where('status', $status))
            ->when($filters['service_type'] ?? null, fn (Builder $query, string $type) => $query->where('service_type', $type))
            ->when($filters['handled_by'] ?? null, fn (Builder $query, int $staffId) => $query->where('handled_by', $staffId))
            ->when($filters['date_from'] ?? null, fn (Builder $query, string $date) => $query->where('service_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn (Builder $query, string $date) => $query->where('service_date', '<=', $date));
    }

    public static function cleanWarrantyFilters(array $input): array
    {
        $status = (string) ($input['status'] ?? '');

        return [
            'q' => trim((string) ($input['q'] ?? '')),
            'status' => in_array($status, Warranty::STATUSES, true) ? $status : '',
            'date_from' => self::cleanDate($input['date_from'] ?? null),
            'date_to' => self::cleanDate($input['date_to'] ?? null),
            'expiring' => !empty($input['expiring']) ? '1' : '',
        ];
    }

    public static function cleanAppointmentFilters(array $input): array
    {
        $status = (string) ($input['status'] ?? '');
        $type = (string) ($input['service_type'] ?? '');

        return [
            'q' => trim((string) ($input['q'] ?? '')),
            'status' => in_array($status, ServiceAppointment::STATUSES, true) ? $status : '',
            'service_type' => in_array($type, ServiceAppointment::SERVICE_TYPES, true) ? $type : '',
            'assigned_staff_id' => self::cleanInt($input['assigned_staff_id'] ?? null),
            'date_from' => self::cleanDate($input['date_from'] ?? null),
            'date_to' => self::cleanDate($input['date_to'] ?? null),
        ];
    }

    public static function cleanRecordFilters(array $input): array
    {
        $status = (string) ($input['status'] ?? '');
        $type = (string) ($input['service_type'] ?? '');

        return [
            'q' => trim((string) ($input['q'] ?? '')),
            'status' => in_array($status, ServiceRecord::STATUSES, true) ? $status : '',
            'service_type' => in_array($type, ServiceAppointment::SERVICE_TYPES, true) ? $type : '',
            'handled_by' => self::cleanInt($input['handled_by'] ?? null),
            'date_from' => self::cleanDate($input['date_from'] ?? null),
            'date_to' => self::cleanDate($input['date_to'] ?? null),
        ];
    }

    private static function cleanDate(mixed $value): string
    {
        $value = trim((string) $value);

        if ($value === '') {
            return '';
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    private static function cleanInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }
}
