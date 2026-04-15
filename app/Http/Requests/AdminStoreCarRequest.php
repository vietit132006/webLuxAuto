<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminStoreCarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'brand_id' => ['required', 'integer', Rule::exists('brands', 'brand_id')],
            'price' => ['required', 'numeric', 'gt:0'],
            'year' => ['required', 'integer', 'min:1900', 'max:'.((int) date('Y') + 1)],
            'color' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:65535'],
            'stock' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:5120'],
            'mileage_km' => ['nullable', 'integer', 'min:0'],
            'fuel_type' => ['nullable', 'string', 'max:100'],
            'transmission' => ['nullable', 'string', 'max:100'],
            'is_featured' => ['sometimes', 'boolean'],
        ];
    }
}
