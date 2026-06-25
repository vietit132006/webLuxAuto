<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    public const STATUS_NEW = 'new';
    public const STATUS_CONSULTING = 'consulting';
    public const STATUS_QUOTED = 'quoted';
    public const STATUS_TEST_DRIVE = 'test_drive';
    public const STATUS_DEPOSIT = 'deposit';
    public const STATUS_PURCHASED = 'purchased';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_NEW => 'Mới',
        self::STATUS_CONSULTING => 'Đang tư vấn',
        self::STATUS_QUOTED => 'Đã báo giá',
        self::STATUS_TEST_DRIVE => 'Lái thử',
        self::STATUS_DEPOSIT => 'Đã đặt cọc',
        self::STATUS_PURCHASED => 'Đã mua',
        self::STATUS_CANCELLED => 'Đã hủy',
    ];

    public const SOURCES = [
        'Facebook',
        'TikTok',
        'Google',
        'Website',
        'Livestream',
        'Referral',
        'Walk-in',
        'Zalo',
    ];

    public const GENDERS = [
        'male' => 'Nam',
        'female' => 'Nữ',
        'other' => 'Khác',
    ];

    protected $table = 'customers';
    protected $primaryKey = 'customer_id';

    protected $fillable = [
        'customer_code',
        'full_name',
        'phone',
        'email',
        'gender',
        'birthday',
        'address',
        'province',
        'occupation',
        'source',
        'interested_car',
        'status',
        'note',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function interactions(): HasMany
    {
        return $this->hasMany(CustomerInteraction::class, 'customer_id', 'customer_id');
    }

    public function quotes(): HasMany
    {
        return $this->hasMany(Quote::class, 'customer_id', 'customer_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function genderLabel(): string
    {
        return self::GENDERS[$this->gender] ?? 'Chưa cập nhật';
    }

    public static function sourceOptions(): array
    {
        return array_combine(self::SOURCES, self::SOURCES);
    }
}
