<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTestDriveAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('test_drives.edit');
    }

    public function rules(): array
    {
        return [
            'appointment_date' => ['nullable', 'date'],
            'appointment_time' => ['nullable', 'date_format:H:i'],
            'showroom' => ['nullable', 'string', 'max:255'],
            'sales_person' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function attributes(): array
    {
        return [
            'appointment_date' => 'ngày hẹn',
            'appointment_time' => 'giờ hẹn',
            'showroom' => 'showroom',
            'sales_person' => 'nhân viên phụ trách',
        ];
    }
}
