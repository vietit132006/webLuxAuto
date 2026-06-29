<?php

namespace App\Exports;

use App\Models\Customer;
use App\Support\AdminReportQuery;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CustomerReportExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    public function __construct(private readonly array $filters = [])
    {
    }

    public function query(): Builder
    {
        $query = Customer::query()
            ->with('creator')
            ->withCount('quotes')
            ->orderByDesc('created_at')
            ->orderByDesc('customer_id');

        AdminReportQuery::applyCustomerFilters($query, $this->filters);

        return $query;
    }

    public function headings(): array
    {
        return [
            'Mã khách',
            'Khách hàng',
            'Số điện thoại',
            'Email',
            'Nguồn',
            'Trạng thái',
            'Xe quan tâm',
            'Số báo giá',
            'Người tạo',
            'Ngày tạo',
        ];
    }

    public function map($customer): array
    {
        return [
            $customer->customer_code,
            $customer->full_name,
            $customer->phone,
            $customer->email,
            $customer->source,
            $customer->statusLabel(),
            $customer->interested_car,
            $customer->quotes_count,
            $customer->creator?->name,
            $customer->created_at?->format('d/m/Y H:i'),
        ];
    }
}
