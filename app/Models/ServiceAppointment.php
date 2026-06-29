<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceAppointment extends Model
{
    public const TYPE_MAINTENANCE = 'maintenance';
    public const TYPE_WARRANTY = 'warranty';
    public const TYPE_REPAIR = 'repair';
    public const TYPE_INSPECTION = 'inspection';

    public const SERVICE_TYPES = [
        self::TYPE_MAINTENANCE,
        self::TYPE_WARRANTY,
        self::TYPE_REPAIR,
        self::TYPE_INSPECTION,
    ];

    public const SERVICE_TYPE_LABELS = [
        self::TYPE_MAINTENANCE => 'Bảo dưỡng định kỳ',
        self::TYPE_WARRANTY => 'Bảo hành',
        self::TYPE_REPAIR => 'Sửa chữa',
        self::TYPE_INSPECTION => 'Kiểm tra xe',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_NO_SHOW = 'no_show';

    public const STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_CONFIRMED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
        self::STATUS_NO_SHOW,
    ];

    public const STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ xác nhận',
        self::STATUS_CONFIRMED => 'Đã xác nhận',
        self::STATUS_IN_PROGRESS => 'Đang xử lý',
        self::STATUS_COMPLETED => 'Hoàn thành',
        self::STATUS_CANCELLED => 'Đã hủy',
        self::STATUS_NO_SHOW => 'Khách không đến',
    ];

    protected $fillable = [
        'appointment_code',
        'user_id',
        'car_id',
        'warranty_id',
        'service_type',
        'appointment_date',
        'appointment_time',
        'service_location',
        'assigned_staff_id',
        'status',
        'customer_note',
        'internal_note',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceAppointment $appointment): void {
            if (blank($appointment->appointment_code)) {
                $appointment->appointment_code = self::nextAppointmentCode();
            }
        });
    }

    public static function serviceTypeOptions(): array
    {
        return self::SERVICE_TYPE_LABELS;
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function formatAppointmentCode(int $number): string
    {
        return 'DV' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    public static function nextAppointmentCode(): string
    {
        $lastCode = self::query()
            ->whereNotNull('appointment_code')
            ->where('appointment_code', 'like', 'DV%')
            ->orderByRaw('CAST(SUBSTRING(appointment_code, 3) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('appointment_code');

        $nextNumber = $lastCode ? ((int) substr($lastCode, 2)) + 1 : 1;

        return self::formatAppointmentCode($nextNumber);
    }

    public static function labelForServiceType(?string $type): string
    {
        return self::SERVICE_TYPE_LABELS[$type] ?? ($type ?: 'N/A');
    }

    public static function labelForStatus(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ($status ?: 'N/A');
    }

    public function scopePendingOrConfirmedToday(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [self::STATUS_PENDING, self::STATUS_CONFIRMED])
            ->whereDate('appointment_date', now()->toDateString());
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return self::labelForServiceType($this->service_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::labelForStatus($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return 'service-badge-' . str_replace('_', '-', (string) $this->status);
    }

    public function getAppointmentTimeLabelAttribute(): string
    {
        return $this->appointment_time ? substr((string) $this->appointment_time, 0, 5) : 'Chưa hẹn giờ';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function assignedStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_staff_id', 'user_id');
    }

    public function serviceRecords(): HasMany
    {
        return $this->hasMany(ServiceRecord::class)->latest('service_date');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ServiceFile::class)->latest();
    }
}
