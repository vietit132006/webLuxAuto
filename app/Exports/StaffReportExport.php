<?php

namespace App\Exports;

use App\Support\AdminReportQuery;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StaffReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function collection(): Collection
    {
        return AdminReportQuery::staffRows($this->filters);
    }

    public function headings(): array
    {
        return [
            'Nhân viên',
            'Email',
            'Số khách phụ trách',
            'Số lịch lái thử',
            'Số báo giá đã tạo',
            'Báo giá accepted',
            'Số đơn hàng',
            'Số xe đã giao',
            'Doanh thu',
            'Tỷ lệ chốt',
        ];
    }

    public function map($row): array
    {
        return [
            $row['user']->name,
            $row['user']->email,
            $row['customers_count'],
            $row['test_drives_count'],
            $row['quotes_count'],
            $row['accepted_quotes_count'],
            $row['orders_count'],
            $row['delivered_count'],
            $row['revenue'],
            $row['closing_rate'] . '%',
        ];
    }
}
