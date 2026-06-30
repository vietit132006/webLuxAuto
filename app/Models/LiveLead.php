<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class LiveLead extends Model
{
    public const TYPE_QUOTE_REQUEST = 'quote_request';
    public const TYPE_TEST_DRIVE_REQUEST = 'test_drive_request';
    public const TYPE_CONSULTATION = 'consultation';
    public const TYPE_DEPOSIT_INTEREST = 'deposit_interest';

    public const TYPES = [
        self::TYPE_QUOTE_REQUEST => 'Yeu cau bao gia',
        self::TYPE_TEST_DRIVE_REQUEST => 'Dat lich lai thu',
        self::TYPE_CONSULTATION => 'Can tu van',
        self::TYPE_DEPOSIT_INTEREST => 'Quan tam dat coc',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_CONVERTED = 'converted';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_NEW => 'Moi',
        self::STATUS_CONTACTED => 'Da lien he',
        self::STATUS_CONVERTED => 'Da chuyen doi',
        self::STATUS_CANCELLED => 'Da huy',
    ];

    protected $fillable = [
        'live_session_id',
        'car_id',
        'user_id',
        'customer_name',
        'phone',
        'email',
        'lead_type',
        'message',
        'status',
        'assigned_to',
    ];

    protected function casts(): array
    {
        return [
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function quote(): HasOne
    {
        return $this->hasOne(Quote::class, 'live_lead_id');
    }

    public function testDrive(): HasOne
    {
        return $this->hasOne(Ticket::class, 'live_lead_id');
    }

    public function leadTypeLabel(): string
    {
        return self::TYPES[$this->lead_type] ?? (string) $this->lead_type;
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
    }

    public function statusBadgeClass(): string
    {
        return 'live-lead-status-' . str_replace('_', '-', (string) $this->status);
    }

    public function customerDisplayName(): string
    {
        return $this->user?->name ?: ($this->customer_name ?: 'Khach livestream');
    }
}
