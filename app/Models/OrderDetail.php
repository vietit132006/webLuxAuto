<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    public $timestamps = false; // Bảng này của bạn không có created_at/updated_at

    protected $fillable = [
        'order_id',
        'car_id',
        'quantity',
        'price'
    ];

    // Mối quan hệ ngược lại với Đơn hàng
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    // Mối quan hệ với Xe
    public function car()
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }
}
