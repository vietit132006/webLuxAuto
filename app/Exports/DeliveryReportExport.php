<?php

namespace App\Exports;

use App\Models\Delivery;
use App\Support\AdminReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DeliveryReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query(): Builder
    {
        $query = Delivery::query()
            ->with(['order.user', 'car.carModel.brand', 'deliveryStaff'])
            ->orderByDesc('actual_delivery_date')
            ->orderByDesc('expected_delivery_date');

        AdminReportQuery::applyDeliveryFilters($query, $this->filters);

        return $query;
    }

    public function headings(): array
    {
        return [
            'Mã đơn hàng',
            'Khách hàng',
            'Xe',
            'Ngày giao dự kiến',
            'Ngày giao thực tế',
            'Nhân viên giao',
            'Trạng thái giao xe',
            'Thời điểm trừ tồn kho',
            'Địa điểm giao',
        ];
    }

    public function map($delivery): array
    {
        return [
            $delivery->order?->display_code,
            $delivery->order?->user?->name,
            $this->carName($delivery),
            $delivery->expected_delivery_date?->format('d/m/Y H:i'),
            $delivery->actual_delivery_date?->format('d/m/Y H:i'),
            $delivery->deliveryStaff?->name,
            $delivery->status_label,
            $delivery->stock_deducted_at?->format('d/m/Y H:i'),
            $delivery->delivery_location,
        ];
    }

    private function carName(Delivery $delivery): ?string
    {
        if (!$delivery->car) {
            return null;
        }

        return trim(
            ($delivery->car->carModel?->brand?->name ? $delivery->car->carModel->brand->name . ' ' : '') .
            ($delivery->car->carModel?->name ? $delivery->car->carModel->name . ' ' : '') .
            $delivery->car->name
        );
    }
}
