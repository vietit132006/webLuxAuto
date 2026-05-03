<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $table = 'cars';
    protected $primaryKey = 'car_id';
    public $timestamps = true;

    protected $fillable = [
        'car_model_id',
        'vin',
        'license_plate',
        'name',
        'price',
        'year',
        'color',
        'interior_color',
        'description',
        'image',
        'mileage_km',
        'is_featured',
        'status',
        'video_url',
        'video_file',
        'owner_count',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'integer',
            'mileage_km' => 'integer',
            'is_featured' => 'boolean', // Nên cast cái này về boolean
        ];
    }

    // ========================
    // RELATIONSHIPS
    // ========================

    // Kết nối tới bảng thông số chung (CarModel)
    public function modelInfo()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id', 'id');
    }

    // Kết nối tới Album ảnh (CarImage)
    public function images()
    {
        return $this->hasMany(CarImage::class, 'car_id', 'car_id');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class, 'car_id', 'car_id');
    }

    // ========================
    // ACCESSORS
    // ========================

    // Lấy tên đầy đủ: Hãng + Tên dòng + Phiên bản
    public function getTitleAttribute(): string
    {
        // Load sẵn modelInfo và brand để tránh lỗi N+1 query
        if (!$this->relationLoaded('modelInfo')) {
            $this->load('modelInfo.brand');
        }

        return $this->modelInfo
            ? ($this->modelInfo->brand->name . ' ' . $this->modelInfo->name . ' ' . $this->name)
            : $this->name;
    }

    public function getPriceFormattedAttribute(): string
    {
        return number_format($this->price, 0, ',', '.') . ' VNĐ';
    }
}
