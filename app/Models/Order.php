<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public const STATUS_PENDING = 0;
    public const STATUS_DEPOSITED = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_CANCELLED = 3;

    protected $primaryKey = 'order_id';

    const UPDATED_AT = null;

    protected $fillable = [
        'order_code',
        'user_id',
        'total_price',
        'deposit_amount',
        'deposit_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'deposit_date' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Order $order): void {
            if (!blank($order->order_code)) {
                return;
            }

            $order->forceFill([
                'order_code' => self::formatOrderCode((int) $order->getKey()),
            ])->saveQuietly();
        });
    }

    public static function formatOrderCode(int $orderId): string
    {
        return 'DH' . str_pad((string) $orderId, 6, '0', STR_PAD_LEFT);
    }

    public static function statusOptions(): array
    {
        return [
            self::STATUS_PENDING => 'Chờ xử lý',
            self::STATUS_DEPOSITED => 'Đã cọc',
            self::STATUS_COMPLETED => 'Hoàn tất',
            self::STATUS_CANCELLED => 'Đã hủy',
        ];
    }

    public static function normalizeStatus(mixed $status): ?int
    {
        if ($status === null || $status === '') {
            return null;
        }

        if (is_numeric($status)) {
            return (int) $status;
        }

        return match ((string) $status) {
            'pending' => self::STATUS_PENDING,
            'deposited', 'deposit' => self::STATUS_DEPOSITED,
            'completed', 'complete', 'done' => self::STATUS_COMPLETED,
            'cancelled', 'canceled', 'cancel' => self::STATUS_CANCELLED,
            default => null,
        };
    }

    public static function labelForStatus(mixed $status): string
    {
        $normalized = self::normalizeStatus($status);

        if ($normalized !== null && array_key_exists($normalized, self::statusOptions())) {
            return self::statusOptions()[$normalized];
        }

        return $status === null || $status === '' ? 'N/A' : (string) $status;
    }

    public function getDisplayCodeAttribute(): string
    {
        return $this->order_code ?: self::formatOrderCode((int) $this->getKey());
    }

    public function getStatusLabelAttribute(): string
    {
        return self::labelForStatus($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        $normalized = self::normalizeStatus($this->status);

        return $normalized === null ? 'badge-unknown' : 'badge-' . $normalized;
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id', 'order_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
}
