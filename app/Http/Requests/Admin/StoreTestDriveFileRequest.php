<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreTestDriveFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->can('test_drives.edit');
    }

    public function rules(): array
    {
        return [
            'documents' => ['required', 'array', 'min:1', 'max:10'],
            'documents.*' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function attributes(): array
    {
        return [
            'documents' => 'tài liệu',
            'documents.*' => 'tài liệu',
        ];
    }
}
