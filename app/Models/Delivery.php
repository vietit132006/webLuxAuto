<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Delivery extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_PREPARING = 'preparing';
    public const STATUS_READY = 'ready';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_PREPARING,
        self::STATUS_READY,
        self::STATUS_DELIVERED,
        self::STATUS_CANCELLED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ giao',
        self::STATUS_PREPARING => 'Đang chuẩn bị',
        self::STATUS_READY => 'Sẵn sàng giao',
        self::STATUS_DELIVERED => 'Đã giao',
        self::STATUS_CANCELLED => 'Hủy giao',
    ];

    public const CHECKLIST_OPTIONS = [
        'exterior_checked' => 'Đã kiểm tra ngoại thất',
        'interior_checked' => 'Đã kiểm tra nội thất',
        'keys_handed_over' => 'Đã bàn giao chìa khóa',
        'documents_handed_over' => 'Đã bàn giao giấy tờ xe',
        'usage_guided' => 'Đã hướng dẫn sử dụng xe',
        'insurance_handed_over' => 'Đã bàn giao bảo hiểm',
        'handover_minutes_signed' => 'Đã ký biên bản giao xe',
    ];

    protected $fillable = [
        'order_id',
        'user_id',
        'car_id',
        'expected_delivery_date',
        'actual_delivery_date',
        'delivery_location',
        'delivery_staff_id',
        'status',
        'note',
        'checklist_data',
        'stock_deducted_at',
    ];

    protected function casts(): array
    {
        return [
            'expected_delivery_date' => 'datetime',
            'actual_delivery_date' => 'datetime',
            'checklist_data' => 'array',
            'stock_deducted_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function checklistOptions(): array
    {
        return self::CHECKLIST_OPTIONS;
    }

    public static function completedChecklistCount(?array $checklist): int
    {
        return count(array_filter($checklist ?: []));
    }

    public static function missingChecklistLabels(?array $checklist): array
    {
        $checklist = $checklist ?: [];

        return collect(self::CHECKLIST_OPTIONS)
            ->filter(fn (string $label, string $key): bool => empty($checklist[$key]))
            ->values()
            ->all();
    }

    public static function checklistIsComplete(?array $checklist): bool
    {
        return self::missingChecklistLabels($checklist) === [];
    }

    public static function labelForStatus(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ($status ?: 'N/A');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::labelForStatus($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return 'delivery-badge-' . str_replace('_', '-', (string) $this->status);
    }

    public function getCompletedChecklistCountAttribute(): int
    {
        return self::completedChecklistCount($this->checklist_data);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function deliveryStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_staff_id', 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(DeliveryFile::class)->latest();
    }
}
