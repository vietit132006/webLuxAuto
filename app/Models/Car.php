<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Car extends Model
{
    use SoftDeletes;

    protected $table = 'cars';

    protected $primaryKey = 'car_id';

    public $timestamps = false;

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
        'fuel_type',
        'transmission',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'decimal:2',
            'stock' => 'integer',
            'created_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'car_id', 'car_id');
    }

    public function promotions(): HasMany
    {
        return $this->hasMany(Promotion::class, 'car_id', 'car_id');
    }

    public function activePromotion(): ?Promotion
    {
        return $this->promotions()->activeNow()->orderByDesc('promotion_id')->first();
    }

    public function getTitleAttribute(): string
    {
        return $this->brand->name.' '.$this->name;
    }

    public function getDiscountedPriceAttribute(): string
    {
        $base = (float) $this->price;
        $promo = $this->relationLoaded('promotions')
            ? $this->promotions->first()
            : $this->activePromotion();
        if (! $promo) {
            return number_format($base, 2, '.', '');
        }

        $final = $this->applyPromotionAmount($base, $promo);

        return number_format(max(0, $final), 2, '.', '');
    }

    public function getFinalPriceFloatAttribute(): float
    {
        return (float) $this->discounted_price;
    }

    protected function applyPromotionAmount(float $base, Promotion $promotion): float
    {
        if ($promotion->discount_type === 'percent') {
            $pct = min(100, max(0, (float) $promotion->discount_value));

            return $base * (1 - $pct / 100);
        }

        return $base - (float) $promotion->discount_value;
    }
}
