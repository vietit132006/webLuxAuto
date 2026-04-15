<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    protected $table = 'brands';
    protected $primaryKey = 'brand_id';
    public $timestamps = false;

    protected $fillable = [
        'name',
        'country',
    ];

    // 1 hãng có nhiều xe
    public function cars()
    {
        return $this->hasMany(Car::class, 'brand_id', 'brand_id');
    }
}
