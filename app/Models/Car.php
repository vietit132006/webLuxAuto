<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'reserved_quantity',
        'avg_rating',
        'reviews_count',
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
            'reserved_quantity' => 'integer',
            'avg_rating' => 'decimal:2',
            'reviews_count' => 'integer',
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

    public function stockReservations()
    {
        return $this->hasMany(StockReservation::class, 'car_id', 'car_id');
    }

    public function activeStockReservations()
    {
        return $this->stockReservations()
            ->where('status', StockReservation::STATUS_ACTIVE);
    }

    public function quotes()
    {
        return $this->hasMany(Quote::class, 'car_id', 'car_id');
    }

    public function liveSessionCars()
    {
        return $this->hasMany(LiveSessionCar::class, 'car_id', 'car_id');
    }

    public function liveSessions()
    {
        return $this->belongsToMany(LiveSession::class, 'live_session_cars', 'car_id', 'live_session_id')
            ->withPivot([
                'promotion_id',
                'display_order',
                'live_price',
                'live_note',
                'is_focus',
                'is_active',
                'pinned_at',
            ])
            ->withTimestamps();
    }

    public function liveLeads()
    {
        return $this->hasMany(LiveLead::class, 'car_id', 'car_id');
    }

    public function warranties()
    {
        return $this->hasMany(Warranty::class, 'car_id', 'car_id');
    }

    public function serviceAppointments()
    {
        return $this->hasMany(ServiceAppointment::class, 'car_id', 'car_id');
    }

    public function serviceRecords()
    {
        return $this->hasMany(ServiceRecord::class, 'car_id', 'car_id');
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

    public function physicalStock(): int
    {
        return (int) ($this->stock_quantity ?? $this->stock ?? 0);
    }

    public function reservedStock(): int
    {
        return max(0, (int) ($this->reserved_quantity ?? 0));
    }

    public function availableStock(): int
    {
        return max(0, $this->physicalStock() - $this->reservedStock());
    }

    public function isSaleBlockedByStatus(): bool
    {
        $status = strtolower((string) $this->status);
        $blockedStatuses = ['3', 'sold', 'hidden', 'out_of_stock', 'out-of-stock', 'inactive'];

        return in_array($status, $blockedStatuses, true);
    }

    public function saleableStock(): int
    {
        return $this->isSaleBlockedByStatus() ? 0 : $this->availableStock();
    }

    public function isOutOfStock(): bool
    {
        return $this->physicalStock() <= 0;
    }

    public function isFullyReserved(): bool
    {
        return $this->physicalStock() > 0 && $this->availableStock() <= 0;
    }

    public function isAvailableForSale(): bool
    {
        return $this->saleableStock() > 0;
    }

    public function scopeAvailableForSale(Builder $query): Builder
    {
        return $query
            ->whereRaw('(COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0)) > 0')
            ->where(function (Builder $statusQuery): void {
                $statusQuery->whereNull('status')
                    ->orWhere('status', '!=', 3);
            });
    }

    public function scopeWithActiveBrand(Builder $query): Builder
    {
        return $query->whereHas('carModel.brand', function (Builder $brandQuery): void {
            $brandQuery->active();
        });
    }

    public function getPhysicalStockAttribute(): int
    {
        return $this->physicalStock();
    }

    public function getReservedStockAttribute(): int
    {
        return $this->reservedStock();
    }

    public function getAvailableStockAttribute(): int
    {
        return $this->availableStock();
    }

    public function getSaleableStockAttribute(): int
    {
        return $this->saleableStock();
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
