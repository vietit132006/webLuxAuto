<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRecord extends Model
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    public const STATUS_LABELS = [
        self::STATUS_COMPLETED => 'Hoàn thành',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    protected $fillable = [
        'record_code',
        'service_appointment_id',
        'warranty_id',
        'user_id',
        'car_id',
        'service_type',
        'service_date',
        'mileage',
        'problem_description',
        'work_performed',
        'parts_replaced',
        'labor_cost',
        'parts_cost',
        'total_cost',
        'next_service_date',
        'next_service_mileage',
        'handled_by',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'service_date' => 'date',
            'mileage' => 'integer',
            'labor_cost' => 'decimal:2',
            'parts_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'next_service_date' => 'date',
            'next_service_mileage' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (ServiceRecord $record): void {
            if (blank($record->record_code)) {
                $record->record_code = self::nextRecordCode();
            }
        });
    }

    public static function statusOptions(): array
    {
        return self::STATUS_LABELS;
    }

    public static function formatRecordCode(int $number): string
    {
        return 'LSBH' . str_pad((string) $number, 6, '0', STR_PAD_LEFT);
    }

    public static function nextRecordCode(): string
    {
        $lastCode = self::query()
            ->whereNotNull('record_code')
            ->where('record_code', 'like', 'LSBH%')
            ->orderByRaw('CAST(SUBSTRING(record_code, 5) AS UNSIGNED) DESC')
            ->lockForUpdate()
            ->value('record_code');

        $nextNumber = $lastCode ? ((int) substr($lastCode, 4)) + 1 : 1;

        return self::formatRecordCode($nextNumber);
    }

    public static function labelForStatus(?string $status): string
    {
        return self::STATUS_LABELS[$status] ?? ($status ?: 'N/A');
    }

    public function scopeNextServiceWithin(Builder $query, int $days = 30): Builder
    {
        return $query->whereBetween('next_service_date', [now()->toDateString(), now()->addDays($days)->toDateString()]);
    }

    public function getServiceTypeLabelAttribute(): string
    {
        return ServiceAppointment::labelForServiceType($this->service_type);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::labelForStatus($this->status);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return 'service-badge-' . str_replace('_', '-', (string) $this->status);
    }

    public function serviceAppointment(): BelongsTo
    {
        return $this->belongsTo(ServiceAppointment::class);
    }

    public function warranty(): BelongsTo
    {
        return $this->belongsTo(Warranty::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function handledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handled_by', 'user_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(ServiceFile::class)->latest();
    }
}
