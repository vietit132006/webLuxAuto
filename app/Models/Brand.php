<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Brand extends Model
{
    protected $table = 'brands';
    protected $primaryKey = 'brand_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'country',
        'logo',
        'slug',
        'description',
        'is_active',
        'sort_order',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function carModels(): HasMany
    {
        return $this->hasMany(CarModel::class, 'brand_id', 'brand_id');
    }

    public function cars(): HasManyThrough
    {
        return $this->hasManyThrough(
            Car::class,
            CarModel::class,
            'brand_id',
            'car_model_id',
            'brand_id',
            'id'
        );
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getLogoUrlAttribute(): ?string
    {
        if (! $this->logo) {
            return null;
        }

        foreach (['http://', 'https://', '/storage/'] as $prefix) {
            if (str_starts_with($this->logo, $prefix)) {
                return $this->logo;
            }
        }

        if (str_starts_with($this->logo, 'storage/')) {
            return '/' . ltrim($this->logo, '/');
        }

        return '/storage/' . ltrim($this->logo, '/');
    }
}
