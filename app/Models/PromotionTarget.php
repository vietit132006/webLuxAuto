<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionTarget extends Model
{
    public const TYPE_ALL = 'all';
    public const TYPE_BRAND = 'brand';
    public const TYPE_MODEL = 'model';
    public const TYPE_CAR = 'car';

    public const TYPES = [
        self::TYPE_ALL => 'Toàn bộ xe',
        self::TYPE_BRAND => 'Hãng xe',
        self::TYPE_MODEL => 'Model xe',
        self::TYPE_CAR => 'Xe cụ thể',
    ];

    protected $fillable = [
        'promotion_id',
        'target_type',
        'target_id',
    ];

    protected function casts(): array
    {
        return [
            'target_id' => 'integer',
        ];
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'target_id', 'brand_id');
    }

    public function carModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'target_id', 'id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'target_id', 'car_id');
    }

    public function targetTypeLabel(): string
    {
        return self::TYPES[$this->target_type] ?? (string) $this->target_type;
    }

    public function targetLabel(): string
    {
        return match ($this->target_type) {
            self::TYPE_ALL => 'Toàn bộ xe',
            self::TYPE_BRAND => $this->brand?->name ? 'Hãng ' . $this->brand->name : 'Hãng đã xóa',
            self::TYPE_MODEL => $this->carModel
                ? trim(($this->carModel->brand?->name ? $this->carModel->brand->name . ' ' : '') . $this->carModel->name)
                : 'Model đã xóa',
            self::TYPE_CAR => $this->car?->title ?? 'Xe đã xóa',
            default => 'Đối tượng không xác định',
        };
    }
}
