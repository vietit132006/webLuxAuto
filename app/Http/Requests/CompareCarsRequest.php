<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CompareCarsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $ids = $this->query('ids', $this->input('ids'));
        if (is_string($ids)) {
            $ids = array_values(array_filter(array_map('intval', explode(',', $ids))));
        }
        if (! is_array($ids)) {
            $ids = [];
        }
        $this->merge(['ids' => $ids]);
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:2', 'max:3'],
            'ids.*' => ['integer', Rule::exists('cars', 'car_id')],
        ];
    }
}
