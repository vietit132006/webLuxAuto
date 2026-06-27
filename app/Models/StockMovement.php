<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    public const ACTION_IMPORT = 'import';
    public const ACTION_SALE = 'sale';
    public const ACTION_ADJUSTMENT = 'adjustment';
    public const ACTION_INVENTORY_CHECK = 'inventory_check';
    public const ACTION_RESERVED = 'reserved';
    public const ACTION_DELIVERY = 'delivery';
    public const ACTION_CANCEL_ORDER = 'cancel_order';
    public const ACTION_RETURN = 'return';

    public const ACTION_TYPES = [
        self::ACTION_IMPORT,
        self::ACTION_SALE,
        self::ACTION_ADJUSTMENT,
        self::ACTION_INVENTORY_CHECK,
        self::ACTION_RESERVED,
        self::ACTION_DELIVERY,
        self::ACTION_CANCEL_ORDER,
        self::ACTION_RETURN,
    ];

    public const ACTION_LABELS = [
        self::ACTION_IMPORT => 'Nhập kho',
        self::ACTION_SALE => 'Bán xe',
        self::ACTION_ADJUSTMENT => 'Điều chỉnh',
        self::ACTION_INVENTORY_CHECK => 'Kiểm tra tồn',
        self::ACTION_RESERVED => 'Giữ chỗ',
        self::ACTION_DELIVERY => 'Giao xe',
        self::ACTION_CANCEL_ORDER => 'Hủy đơn',
        self::ACTION_RETURN => 'Hoàn trả',
    ];

    protected $fillable = [
        'car_id',
        'user_id',
        'action_type',
        'quantity_before',
        'quantity_change',
        'quantity_after',
        'reason',
        'note',
        'ip_address',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before' => 'integer',
            'quantity_change' => 'integer',
            'quantity_after' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public static function labelFor(string $actionType): string
    {
        return self::ACTION_LABELS[$actionType] ?? $actionType;
    }

    public static function badgeClassFor(string $actionType): string
    {
        return 'stock-badge-' . str_replace('_', '-', $actionType);
    }
}
