<?php

namespace App\Http\Requests\Admin;

use App\Models\Ticket;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTestDriveStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('test_drives.edit');
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::in(Ticket::testDriveStatusValues())],
            'note' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'trạng thái',
            'note' => 'ghi chú',
        ];
    }
}
