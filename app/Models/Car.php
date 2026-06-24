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
        'internal_code',
        'name',
        'price',
        'list_price',
        'sale_price',
        'registration_fee',
        'license_plate_fee',
        'inspection_fee',
        'insurance_fee',
        'other_fees',
        'estimated_rolling_price',
        'registration_area',
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
        'stock_in_date',
        'on_road_date',
        'vehicle_condition',
        'current_location',
        'stock_quantity',
        'stock',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'integer',
            'list_price' => 'integer',
            'sale_price' => 'integer',
            'registration_fee' => 'integer',
            'license_plate_fee' => 'integer',
            'inspection_fee' => 'integer',
            'insurance_fee' => 'integer',
            'other_fees' => 'integer',
            'estimated_rolling_price' => 'integer',
            'mileage_km' => 'integer',
            'stock_quantity' => 'integer',
            'stock' => 'integer',
            'stock_in_date' => 'date',
            'on_road_date' => 'date',
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

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class, 'car_id', 'car_id');
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

    public function getEstimatedRollingPriceFormattedAttribute(): ?string
    {
        if ($this->estimated_rolling_price === null) {
            return null;
        }

        return number_format($this->estimated_rolling_price, 0, ',', '.') . ' VNĐ';
    }

    public function carModel()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id', 'id');
    }

    // Mối quan hệ: Thông qua Dòng xe để lấy Hãng xe (Brand)
    public function brand()
    {
        return $this->hasOneThrough(
            Brand::class,      // Bảng đích muốn lấy
            CarModel::class,   // Bảng trung gian
            'id',              // Khóa ngoại ở bảng CarModel
            'brand_id',        // Khóa ngoại ở bảng Brands
            'car_model_id',    // Khóa ngoại ở bảng Cars
            'brand_id'         // Khóa ở bảng CarModel
        );
    }
}
