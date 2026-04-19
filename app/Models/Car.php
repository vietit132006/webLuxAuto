<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'cars';
    protected $primaryKey = 'car_id';

    // Tắt tự động timestamps vì bảng của bạn chỉ có created_at (DB tự sinh)
    public $timestamps = false;

    // Khai báo các cột được phép lưu dữ liệu
    protected $fillable = [
        'name',
        'brand_id',
        'price',
        'year',
        'color',
        'description',
        'stock',
        'image',
        'mileage_km',
        'fuel',          // Đã đổi từ fuel_type thành fuel
        'transmission',
        'is_featured',
        'status'         // Đã bổ sung thêm status
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'integer',
            'stock' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'car_id', 'car_id');
    }

    public function inventoryLogs()
    {
        return $this->hasMany(InventoryLog::class, 'car_id', 'car_id');
    }

    public function getTitleAttribute(): string
    {
        return $this->brand ? $this->brand->name . ' ' . $this->name : $this->name;
    }
}
