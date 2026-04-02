<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'cars';
    protected $primaryKey = 'car_id';
    public $timestamps = false;

    protected $fillable = [
        'brand_id',
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
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'integer',
            'mileage_km' => 'integer',
            'is_featured' => 'boolean',
        ];
    }

    // xe thuộc 1 hãng
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    // title hiển thị
    public function getTitleAttribute(): string
    {
        return $this->brand->name . ' ' . $this->model;
    }
}
