<?php

namespace App\Http\Requests\Admin;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TestDriveIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('test_drives.view');
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(Ticket::testDriveStatusValues())],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'appointment_from' => ['nullable', 'date'],
            'appointment_to' => ['nullable', 'date', 'after_or_equal:appointment_from'],
            'sales_person' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function filters(): array
    {
        $validated = $this->validated();

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'status' => $validated['status'] ?? '',
            'created_from' => $validated['created_from'] ?? '',
            'created_to' => $validated['created_to'] ?? '',
            'appointment_from' => $validated['appointment_from'] ?? '',
            'appointment_to' => $validated['appointment_to'] ?? '',
            'sales_person' => trim((string) ($validated['sales_person'] ?? '')),
        ];
    }

    public function attributes(): array
    {
        return [
            'q' => 'từ khóa',
            'status' => 'trạng thái',
            'created_from' => 'ngày tạo từ',
            'created_to' => 'ngày tạo đến',
            'appointment_from' => 'ngày hẹn từ',
            'appointment_to' => 'ngày hẹn đến',
            'sales_person' => 'nhân viên phụ trách',
        ];
    }
}
