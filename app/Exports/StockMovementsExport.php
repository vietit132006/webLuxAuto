<?php

namespace App\Exports;

use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StockMovementsExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query(): Builder
    {
        return StockMovement::query()
            ->with(['car', 'user'])
            ->when($this->filters['car_id'] ?? null, fn (Builder $query, $carId) => $query->where('car_id', $carId))
            ->when($this->filters['user_id'] ?? null, fn (Builder $query, $userId) => $query->where('user_id', $userId))
            ->when($this->filters['action_type'] ?? null, fn (Builder $query, $actionType) => $query->where('action_type', $actionType))
            ->when($this->filters['date_from'] ?? null, function (Builder $query, $date) {
                $query->where('created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($this->filters['date_to'] ?? null, function (Builder $query, $date) {
                $query->where('created_at', '<=', Carbon::parse($date)->endOfDay());
            })
            ->when($this->filters['q'] ?? null, function (Builder $query, $search) {
                $query->where(function (Builder $inner) use ($search) {
                    $inner->where('reason', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('car', function (Builder $carQuery) use ($search) {
                            $carQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('vin', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%")
                                ->orWhere('internal_code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('user', function (Builder $userQuery) use ($search) {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'Xe',
            'Loại thao tác',
            'Trước',
            'Thay đổi',
            'Sau',
            'Người thực hiện',
            'Lý do',
            'Thời gian',
        ];
    }

    public function map($movement): array
    {
        return [
            $movement->car?->name ?? 'Xe da xoa',
            $movement->action_type,
            $movement->quantity_before,
            $movement->quantity_change,
            $movement->quantity_after,
            $movement->user?->name ?? 'Hệ thống',
            $movement->reason,
            $movement->created_at?->format('d/m/Y H:i'),
        ];
    }
}
