<?php

namespace App\Exports;

use App\Models\Ticket;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class TestDrivesExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(private readonly Builder $query)
    {
    }

    public function query(): Builder
    {
        return $this->query;
    }

    public function headings(): array
    {
        return [
            'Mã lịch',
            'Khách hàng',
            'Email',
            'Số điện thoại',
            'Xe',
            'Ngày hẹn',
            'Showroom',
            'Nhân viên',
            'Trạng thái',
            'Ngày tạo',
        ];
    }

    public function map($ticket): array
    {
        /** @var Ticket $ticket */
        $appointment = $ticket->appointment_date
            ? $ticket->appointment_date->format('d/m/Y') . ($ticket->appointment_time ? ' ' . substr((string) $ticket->appointment_time, 0, 5) : '')
            : null;

        $carName = $ticket->car
            ? trim(($ticket->car->brand->name ?? '') . ' ' . $ticket->car->name)
            : null;

        return [
            $ticket->display_code,
            $ticket->user?->name,
            $ticket->user?->email,
            $ticket->user?->phone,
            $carName,
            $appointment,
            $ticket->showroom,
            $ticket->sales_person,
            $ticket->test_drive_status_label,
            $ticket->created_at?->format('d/m/Y H:i'),
        ];
    }
}
