<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // Chỉ định khóa chính vì bảng của bạn dùng order_id thay vì id
    protected $primaryKey = 'order_id';

    // Bảng của bạn dường như chỉ có created_at, không có updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'total_price',
        'status' // 0: Chờ xử lý, 1: Đã cọc, 2: Hoàn thành, 3: Hủy
    ];

    // Mối quan hệ: 1 Đơn hàng có nhiều Chi tiết đơn hàng
    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    // Mối quan hệ: Đơn hàng thuộc về 1 Khách hàng
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
