<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockReservation extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_RELEASED = 'released';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_COMPLETED,
        self::STATUS_RELEASED,
        self::STATUS_EXPIRED,
        self::STATUS_CANCELLED,
    ];

    protected $fillable = [
        'car_id',
        'order_id',
        'quote_id',
        'user_id',
        'quantity',
        'status',
        'reserved_by',
        'reserved_at',
        'expires_at',
        'released_at',
        'release_reason',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'reserved_at' => 'datetime',
            'expires_at' => 'datetime',
            'released_at' => 'datetime',
            'completed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class, 'quote_id', 'quote_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function reservedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reserved_by', 'user_id');
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
