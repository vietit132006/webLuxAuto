<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveSessionCar extends Model
{
    protected $fillable = [
        'live_session_id',
        'car_id',
        'promotion_id',
        'display_order',
        'live_price',
        'live_note',
        'is_focus',
        'is_active',
        'pinned_at',
    ];

    protected function casts(): array
    {
        return [
            'display_order' => 'integer',
            'live_price' => 'decimal:2',
            'is_focus' => 'boolean',
            'is_active' => 'boolean',
            'pinned_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function liveSession(): BelongsTo
    {
        return $this->belongsTo(LiveSession::class);
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function displayPrice(): ?float
    {
        return $this->live_price !== null ? (float) $this->live_price : null;
    }
}
