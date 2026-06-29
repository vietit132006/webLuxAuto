<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Warranty extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_VOID = 'void';

    public const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_EXPIRED,
        self::STATUS_VOID,
    ];

    public const STATUS_LABELS = [
        self::STATUS_ACTIVE => 'Đang bảo hành',
        self::STATUS_EXPIRED => 'Hết hạn',
        self::STATUS_VOID => 'Hủy bảo hành',
    ];

    protected $fillable = [
        'warranty_code',
        'order_id',
        'delivery_id',
        'user_id',
        'car_id',
        'vin',
        'license_plate',
        'start_date',
        'end_date',
        'warranty_months',
        'mileage_limit',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'warranty_months' => 'integer',
            'mileage_limit' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Warranty $warranty): void {
            if (blank($warranty->warranty_code)) {
                $warranty->warranty_code = self::nextWarrantyCode();
            }
        });
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function formatWarrantyCode(int $number): string
    {
        return 'BH' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    public static function nextWarrantyCode(): string
    {
        $lastCode = self::query()
            ->whereNotNull('warranty_code')
            ->where('warranty_code', 'like', 'BH%')
            ->orderByRaw('CAST(SUBSTRING(warranty_code, 3) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('warranty_code');

        $nextNumber = $lastCode ? ((int) substr($lastCode, 2)) + 1 : 1;

        return self::formatWarrantyCode($nextNumber);
    }

    public static function labelForStatus(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ($status ?: 'N/A');
    }

    public static function monthsBetweenDates(mixed $startDate, mixed $endDate): int
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->startOfDay();

        if ($end->lessThanOrEqualTo($start)) {
            return 1;
        }

        $months = 0;

        while ($months < 120 && $start->copy()->addMonthsNoOverflow($months + 1)->lessThanOrEqualTo($end)) {
            $months++;
        }

        if ($start->copy()->addMonthsNoOverflow($months)->lessThan($end)) {
            $months++;
        }

        return max(1, $months);
    }

    public function scopeExpiringWithin(Builder $query, int $days = 30): Builder
    {
        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::labelForStatus($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return 'warranty-badge-' . str_replace('_', '-', (string) $this->status);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (!$this->end_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->end_date->copy()->startOfDay(), false);
    }

    public function getEffectiveWarrantyMonthsAttribute(): int
    {
        if (!$this->start_date || !$this->end_date) {
            return (int) ($this->warranty_months ?: 0);
        }

        return self::monthsBetweenDates($this->start_date, $this->end_date);
    }

    public function getCarDisplayNameAttribute(): string
    {
        if (!$this->car) {
            return 'Chưa gán xe';
        }

        return trim(
            ($this->car->carModel?->brand?->name ? $this->car->carModel->brand->name . ' ' : '') .
            ($this->car->carModel?->name ? $this->car->carModel->name . ' ' : '') .
            $this->car->name
        );
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function serviceAppointments(): HasMany
    {
        return $this->hasMany(ServiceAppointment::class)->latest('appointment_date');
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class)->latest('service_date');
    }
}
