<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'car_id',
        'change_qty',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'change_qty' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }
}
