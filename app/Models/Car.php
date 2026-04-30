<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'cars';
    protected $primaryKey = 'car_id';

    // 👉 Nếu DB có created_at thì bật
    public $timestamps = true;

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
        'fuel',
        'transmission',
        'is_featured',
        'status',
        'gallery',
        'video_url',
        'video_file',
        'engine',
        'interior_color',
        'origin',
        'body_type',
        'seats',
        'doors',
        'drive_type',
    ];

    // ✅ Laravel 13 chuẩn
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'integer',
            'stock' => 'integer',
            'gallery' => 'array',
            'seats' => 'integer',
            'doors' => 'integer',
        ];
    }

    // ========================
    // RELATIONSHIPS
    // ========================

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

    // ========================
    // ACCESSORS (PRO LEVEL)
    // ========================

    // 👉 Tên hiển thị
    public function getTitleAttribute(): string
    {
        return $this->brand
            ? $this->brand->name . ' ' . $this->name
            : $this->name;
    }

    // 👉 Format giá
    public function getPriceFormattedAttribute(): string
    {
        return number_format($this->price, 0, ',', '.') . ' VNĐ';
    }

    // 👉 URL ảnh chuẩn
    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : 'https://via.placeholder.com/800x500';
    }
}
