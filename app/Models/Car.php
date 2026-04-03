<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
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
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'price' => 'integer',
            'stock' => 'integer',
        ];
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id', 'brand_id');
    }

    public function getTitleAttribute(): string
    {
        return $this->brand->name . ' ' . $this->name;
    }
}
