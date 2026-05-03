<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarImage extends Model
{
    // Khai báo các cột có thể nhập liệu
    protected $fillable = [
        'car_id',
        'image_path',
        'sort_order',
    ];

    // Thiết lập quan hệ ngược lại với Car (nếu cần)
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }
}
