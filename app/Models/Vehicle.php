<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'brand',
    'model',
    'year',
    'price',
    'mileage_km',
    'fuel_type',
    'transmission',
    'color',
    'description',
    'image_url',
    'is_featured',
])]
class Vehicle extends Model
{
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'integer',
            'mileage_km' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    public function getTitleAttribute(): string
    {
        return "{$this->brand} {$this->model}";
    }
}
