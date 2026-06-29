<?php

namespace App\Exports;

use App\Models\Warranty;
use App\Support\AfterSalesQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class WarrantiesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query(): Builder
    {
        $query = Warranty::query()
            ->with(['user', 'car.carModel.brand', 'order', 'delivery'])
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        AfterSalesQuery::applyWarrantyFilters($query, $this->filters);

        return $query;
    }

    public function headings(): array
    {
        return [
            'Mã bảo hành',
            'Mã đơn hàng',
            'Khách hàng',
            'Số điện thoại',
            'Xe',
            'VIN',
            'Biển số',
            'Ngày bắt đầu',
            'Ngày kết thúc',
            'Tháng bảo hành',
            'Giới hạn km',
            'Trạng thái',
            'Số ngày còn lại',
        ];
    }

    public function map($warranty): array
    {
        return [
            $warranty->warranty_code,
            $warranty->order?->display_code,
            $warranty->user?->name,
            $warranty->user?->phone,
            $warranty->car_display_name,
            $warranty->vin,
            $warranty->license_plate,
            $warranty->start_date?->format('d/m/Y'),
            $warranty->end_date?->format('d/m/Y'),
            $warranty->warranty_months,
            $warranty->mileage_limit,
            $warranty->status_label,
            $warranty->days_remaining,
        ];
    }
}
