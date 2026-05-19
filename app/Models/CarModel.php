<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $table = 'car_models';

    protected $fillable = [
        'brand_id',
        'name',
        'engine',
        'fuel_type',
        'transmission',
        'body_type',
        'drive_type',
        'seats',
        'doors',
        'origin',
    ];

    // 1. Một Mẫu xe (ví dụ Porsche 911) có thể có nhiều chiếc xe cụ thể trong kho
    public function cars()
    {
        return $this->hasMany(Car::class, 'car_model_id');
    }

    // 2. Một Mẫu xe thì phải thuộc về một Hãng xe nào đó (ví dụ Porsche)
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }
}
