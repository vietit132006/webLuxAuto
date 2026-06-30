<?php

namespace App\Exports;

use App\Models\Order;
use App\Support\AdminReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class SalesReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query(): Builder
    {
        $query = Order::query()
            ->with(['user', 'quote.user', 'depositConfirmer', 'details.car.carModel.brand', 'delivery'])
            ->select('orders.*')
            ->orderByDesc('created_at')
            ->orderByDesc('order_id');

        AdminReportQuery::applyOrderFilters($query, $this->filters);

        return $query;
    }

    public function headings(): array
    {
        return [
            'Mã đơn',
            'Khách hàng',
            'Xe',
            'Nhân viên phụ trách',
            'Tổng tiền',
            'Tiền cọc',
            'Còn lại',
            'Trạng thái đơn',
            'Trạng thái giao xe',
            'Ngày tạo',
            'Ngày giao thực tế',
        ];
    }

    public function map($order): array
    {
        $cars = $order->details
            ->map(function ($detail): string {
                $car = $detail->car;
                $name = $car
                    ? trim(($car->carModel?->brand?->name ? $car->carModel->brand->name . ' ' : '') . ($car->carModel?->name ? $car->carModel->name . ' ' : '') . $car->name)
                    : 'Xe đã xóa';

                return $name . ' x' . $detail->quantity;
            })
            ->implode('; ');

        return [
            $order->display_code,
            $order->user?->name,
            $cars,
            $order->quote?->user?->name ?? $order->depositConfirmer?->name,
            (float) $order->total_price,
            (float) ($order->deposit_amount ?? 0),
            $order->remaining_amount,
            $order->status_label,
            $order->delivery?->status_label,
            $order->created_at?->format('d/m/Y H:i'),
            $order->delivery?->actual_delivery_date?->format('d/m/Y H:i'),
        ];
    }
}
