<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    public const STATUS_PENDING = 0;
    public const STATUS_DEPOSITED = 1;
    public const STATUS_COMPLETED = 2;
    public const STATUS_CANCELLED = 3;
    public const DEPOSIT_METHOD_CASH = 'cash';
    public const DEPOSIT_METHOD_BANK_TRANSFER = 'bank_transfer';
    public const DEPOSIT_METHOD_CARD = 'card';
    public const DEPOSIT_METHOD_OTHER = 'other';
    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ xử lý',
        self::STATUS_DEPOSITED => 'Đã cọc',
        self::STATUS_COMPLETED => 'Hoàn tất',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    public const DEPOSIT_METHOD_LABELS = [
        self::DEPOSIT_METHOD_CASH => 'Tiền mặt',
        self::DEPOSIT_METHOD_BANK_TRANSFER => 'Chuyển khoản',
        self::DEPOSIT_METHOD_CARD => 'Thẻ',
        self::DEPOSIT_METHOD_OTHER => 'Khác',
    ];

    protected $primaryKey = 'order_id';

    const UPDATED_AT = null;

    protected $fillable = [
        'order_code',
        'user_id',
        'total_price',
        'deposit_amount',
        'deposit_date',
        'deposit_method',
        'deposit_reference',
        'deposit_note',
        'deposit_confirmed_by',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'total_price' => 'decimal:2',
            'deposit_amount' => 'decimal:2',
            'deposit_date' => 'datetime',
            'deposit_confirmed_by' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::created(function (Order $order): void {
            if (!blank($order->order_code)) {
                return;
            }

            self::assignOrderCode($order);
        });
    }

    public static function formatOrderCode(int $number): string
    {
        return 'DH' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    public static function nextOrderCode(): string
    {
        $lastCode = self::query()
            ->whereNotNull('order_code')
            ->where('order_code', 'regexp', '^DH[0-9]{6}$')
            ->orderByRaw('CAST(SUBSTRING(order_code, 3) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('order_code');

        $nextNumber = $lastCode ? ((int) substr($lastCode, 2)) + 1 : 1;

        return self::formatOrderCode($nextNumber);
    }

    private static function assignOrderCode(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $freshOrder = self::query()
                ->whereKey($order->getKey())
                ->lockForUpdate()
                ->first();

            if (!$freshOrder || !blank($freshOrder->order_code)) {
                return;
            }

            $freshOrder->forceFill([
                'order_code' => self::nextOrderCode(),
            ])->saveQuietly();

            $order->forceFill([
                'order_code' => $freshOrder->order_code,
            ]);
        });
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function depositMethodOptions(): array
    {
        return self::DEPOSIT_METHOD_LABELS;
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

    public static function labelForDepositMethod(?string $method): string
    {
        if (!$method) {
            return 'N/A';
        }

        return self::DEPOSIT_METHOD_LABELS[$method] ?? $method;
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

    public function getDepositMethodLabelAttribute(): string
    {
        return self::labelForDepositMethod($this->deposit_method);
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float) $this->total_price - (float) ($this->deposit_amount ?? 0));
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function depositConfirmer()
    {
        return $this->belongsTo(User::class, 'deposit_confirmed_by', 'user_id');
    }

    public function statusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'order_id', 'order_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
}
