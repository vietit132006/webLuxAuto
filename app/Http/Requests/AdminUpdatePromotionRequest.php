<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminUpdatePromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'car_id' => ['sometimes', 'integer', Rule::exists('cars', 'car_id')],
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:65535'],
            'discount_type' => ['sometimes', Rule::in(['percent', 'fixed'])],
            'discount_value' => ['sometimes', 'numeric', 'gt:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            $type = $this->input('discount_type');
            $value = $this->input('discount_value');
            if ($type === 'percent' && $value !== null && (float) $value > 100) {
                $validator->errors()->add('discount_value', 'Percent cannot exceed 100.');
            }
        });
    }
}
