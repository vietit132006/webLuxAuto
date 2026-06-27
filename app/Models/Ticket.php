<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    public const TYPE_SUPPORT = 'support';
    public const TYPE_TEST_DRIVE = 'test_drive';

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_COMPLETED = 'completed';

    public const TEST_DRIVE_STATUS_LABELS = [
        self::STATUS_PENDING => 'Chờ xử lý',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Đã hủy',
        self::STATUS_COMPLETED => 'Hoàn thành',
    ];

    public const TEST_DRIVE_STATUS_BADGES = [
        self::STATUS_PENDING => 'badge-pending',
        self::STATUS_APPROVED => 'badge-approved',
        self::STATUS_REJECTED => 'badge-rejected',
        self::STATUS_COMPLETED => 'badge-completed',
    ];

    private const TEST_DRIVE_TRANSITIONS = [
        self::STATUS_PENDING => [self::STATUS_APPROVED, self::STATUS_REJECTED],
        self::STATUS_APPROVED => [self::STATUS_COMPLETED],
        self::STATUS_REJECTED => [],
        self::STATUS_COMPLETED => [],
    ];

    protected $table = 'support_tickets';
    protected $primaryKey = 'ticket_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ticket_type',
        'car_id',
        'subject',
        'message',
        'status',
        'appointment_date',
        'appointment_time',
        'showroom',
        'sales_person',
        'admin_reply',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'created_at' => 'datetime',
        ];
    }

    public static function testDriveStatusOptions(): array
    {
        return self::TEST_DRIVE_STATUS_LABELS;
    }

    public static function testDriveStatusValues(): array
    {
        return array_keys(self::TEST_DRIVE_STATUS_LABELS);
    }

    public static function labelForTestDriveStatus(?string $status): string
    {
        if (!$status) {
            return 'N/A';
        }

        return self::TEST_DRIVE_STATUS_LABELS[$status] ?? ucfirst($status);
    }

    public static function badgeClassForTestDriveStatus(?string $status): string
    {
        return self::TEST_DRIVE_STATUS_BADGES[$status] ?? 'badge-unknown';
    }

    public static function isValidTestDriveTransition(string $from, string $to): bool
    {
        if ($from === $to) {
            return true;
        }

        return in_array($to, self::TEST_DRIVE_TRANSITIONS[$from] ?? [], true);
    }

    public static function nextTestDriveStatusOptionsFor(?string $status): array
    {
        $nextStatuses = self::TEST_DRIVE_TRANSITIONS[$status ?? ''] ?? [];

        return collect($nextStatuses)
            ->mapWithKeys(fn (string $next): array => [$next => self::labelForTestDriveStatus($next)])
            ->all();
    }

    public function getDisplayCodeAttribute(): string
    {
        return 'LT' . str_pad((string) $this->ticket_id, 6, '0', STR_PAD_LEFT);
    }

    public function getTestDriveStatusLabelAttribute(): string
    {
        return self::labelForTestDriveStatus($this->status);
    }

    public function getTestDriveStatusBadgeClassAttribute(): string
    {
        return self::badgeClassForTestDriveStatus($this->status);
    }

    public function nextTestDriveStatusOptions(): array
    {
        return self::nextTestDriveStatusOptionsFor($this->status);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(TestDriveStatusHistory::class, 'ticket_id', 'ticket_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(TestDriveNote::class, 'ticket_id', 'ticket_id')
            ->orderBy('created_at')
            ->orderBy('id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(TestDriveFile::class, 'ticket_id', 'ticket_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(TestDriveActivityLog::class, 'ticket_id', 'ticket_id')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'test_drive_id', 'ticket_id')
            ->orderByDesc('created_at')
            ->orderByDesc('quote_id');
    }
}
