<?php

namespace App\Exports;

use App\Models\StockReservation;
use App\Support\AdminReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ReservationReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query(): Builder
    {
        $query = StockReservation::query()
            ->with(['order.user', 'car.carModel.brand', 'user', 'reservedBy'])
            ->orderByDesc('reserved_at')
            ->orderByDesc('created_at');

        AdminReportQuery::applyReservationFilters($query, $this->filters);

        return $query;
    }

    public function headings(): array
    {
        return [
            'Mã đơn hàng',
            'Khách hàng',
            'Xe',
            'Số lượng',
            'Trạng thái giữ chỗ',
            'Người giữ',
            'Ngày giữ',
            'Ngày giải phóng',
            'Lý do giải phóng',
            'Hết hạn',
        ];
    }

    public function map($reservation): array
    {
        return [
            $reservation->order?->display_code,
            $reservation->user?->name ?? $reservation->order?->user?->name,
            $this->carName($reservation),
            $reservation->quantity,
            $reservation->status,
            $reservation->reservedBy?->name,
            $reservation->reserved_at?->format('d/m/Y H:i'),
            $reservation->released_at?->format('d/m/Y H:i'),
            $reservation->release_reason,
            $reservation->expires_at?->format('d/m/Y H:i'),
        ];
    }

    private function carName(StockReservation $reservation): ?string
    {
        if (!$reservation->car) {
            return null;
        }

        return trim(
            ($reservation->car->carModel?->brand?->name ? $reservation->car->carModel->brand->name . ' ' : '') .
            ($reservation->car->carModel?->name ? $reservation->car->carModel->name . ' ' : '') .
            $reservation->car->name
        );
    }
}
