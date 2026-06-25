<?php

namespace App\Exports;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query(): Builder
    {
        return Order::query()
            ->with(['user', 'details.car'])
            ->when($this->filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('order_code', 'like', "%{$search}%")
                        ->orWhere('order_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(($this->filters['status'] ?? '') !== '', function (Builder $query): void {
                $query->whereIn('status', $this->statusFilterValues((int) $this->filters['status']));
            })
            ->when($this->filters['date_from'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($this->filters['date_to'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '<=', Carbon::parse($date)->endOfDay());
            })
            ->orderByDesc('created_at')
            ->orderByDesc('order_id');
    }

    public function headings(): array
    {
        return [
            'Mã đơn',
            'Khách hàng',
            'Email',
            'Xe',
            'Tổng tiền',
            'Tiền cọc',
            'Ngày cọc',
            'Trạng thái',
            'Ngày tạo',
        ];
    }

    public function map($order): array
    {
        $cars = $order->details
            ->map(function ($detail): string {
                $name = $detail->car?->name ?? 'Xe đã xóa';
                $price = number_format((float) $detail->price, 0, ',', '.');

                return "{$name} x{$detail->quantity} ({$price} đ)";
            })
            ->implode('; ');

        return [
            $order->display_code,
            $order->user?->name ?? 'Khách ẩn danh',
            $order->user?->email,
            $cars,
            (float) $order->total_price,
            (float) ($order->deposit_amount ?? 0),
            $order->deposit_date?->format('d/m/Y H:i'),
            $order->status_label,
            $order->created_at?->format('d/m/Y H:i'),
        ];
    }

    private function statusFilterValues(int $status): array
    {
        return match ($status) {
            Order::STATUS_PENDING => [0, '0', 'pending'],
            Order::STATUS_DEPOSITED => [1, '1', 'deposit', 'deposited'],
            Order::STATUS_COMPLETED => [2, '2', 'complete', 'completed', 'done'],
            Order::STATUS_CANCELLED => [3, '3', 'cancel', 'canceled', 'cancelled'],
            default => [$status, (string) $status],
        };
    }
}
