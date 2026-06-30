<?php

namespace App\Exports;

use App\Models\ServiceRecord;
use App\Support\AfterSalesQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ServiceReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query(): Builder
    {
        $query = ServiceRecord::query()
            ->with(['serviceAppointment', 'warranty', 'user', 'car.carModel.brand', 'handledBy'])
            ->orderByDesc('service_date')
            ->orderByDesc('id');

        AfterSalesQuery::applyRecordFilters($query, $this->filters);

        return $query;
    }

    public function headings(): array
    {
        return [
            'Mã lịch sử',
            'Mã lịch hẹn',
            'Mã bảo hành',
            'Khách hàng',
            'Xe',
            'Loại dịch vụ',
            'Ngày dịch vụ',
            'Số km',
            'Chi phí công',
            'Chi phí phụ tùng',
            'Tổng chi phí',
            'Ngày bảo dưỡng tiếp',
            'Km bảo dưỡng tiếp',
            'Người xử lý',
            'Trạng thái',
        ];
    }

    public function map($record): array
    {
        return [
            $record->record_code,
            $record->serviceAppointment?->appointment_code,
            $record->warranty?->warranty_code,
            $record->user?->name,
            $this->carName($record),
            $record->service_type_label,
            $record->service_date?->format('d/m/Y'),
            $record->mileage,
            (float) $record->labor_cost,
            (float) $record->parts_cost,
            (float) $record->total_cost,
            $record->next_service_date?->format('d/m/Y'),
            $record->next_service_mileage,
            $record->handledBy?->name,
            $record->status_label,
        ];
    }

    private function carName(ServiceRecord $record): ?string
    {
        if (!$record->car) {
            return null;
        }

        return trim(
            ($record->car->carModel?->brand?->name ? $record->car->carModel->brand->name . ' ' : '') .
            ($record->car->carModel?->name ? $record->car->carModel->name . ' ' : '') .
            $record->car->name
        );
    }
}
