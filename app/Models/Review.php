<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Review extends Model
{
    use SoftDeletes;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_HIDDEN = 'hidden';
    public const STATUS_REPORTED = 'reported';

    public const VERIFIED_TEST_DRIVE = 'test_drive';
    public const VERIFIED_DEPOSIT = 'deposit';
    public const VERIFIED_PURCHASE = 'purchase';
    public const VERIFIED_SERVICE = 'service';
    public const VERIFIED_NONE = 'none';

    public const STATUSES = [
        self::STATUS_PENDING => 'Chờ duyệt',
        self::STATUS_APPROVED => 'Đã duyệt',
        self::STATUS_REJECTED => 'Từ chối',
        self::STATUS_HIDDEN => 'Đã ẩn',
        self::STATUS_REPORTED => 'Bị báo cáo',
    ];

    public const VERIFIED_TYPES = [
        self::VERIFIED_TEST_DRIVE => 'Đã lái thử',
        self::VERIFIED_DEPOSIT => 'Đã đặt cọc',
        self::VERIFIED_PURCHASE => 'Đã mua xe',
        self::VERIFIED_SERVICE => 'Đã sử dụng dịch vụ',
        self::VERIFIED_NONE => 'Chưa xác minh',
    ];

    protected $table = 'reviews';

    protected $primaryKey = 'review_id';

    protected $fillable = [
        'user_id',
        'car_id',
        'title',
        'rating',
        'comment',
        'status',
        'verified_type',
        'order_id',
        'ticket_id',
        'service_record_id',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejected_reason',
        'reply_content',
        'replied_by',
        'replied_at',
        'is_featured',
        'helpful_count',
        'report_count',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'is_featured' => 'boolean',
            'helpful_count' => 'integer',
            'report_count' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'approved_at' => 'datetime',
            'rejected_at' => 'datetime',
            'replied_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        $refresh = function (Review $review): void {
            app(\App\Services\ReviewRatingService::class)->refreshForCar((int) $review->car_id);
        };

        static::saved($refresh);
        static::deleted($refresh);
        static::restored($refresh);
    }

    public static function statusOptions(): array
    {
        return self::STATUSES;
    }

    public static function verifiedTypeOptions(): array
    {
        return self::VERIFIED_TYPES;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function car(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'car_id', 'car_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    public function serviceRecord(): BelongsTo
    {
        return $this->belongsTo(ServiceRecord::class, 'service_record_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ReviewImage::class, 'review_id', 'review_id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ReviewReport::class, 'review_id', 'review_id')
            ->latest();
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReviewVote::class, 'review_id', 'review_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by', 'user_id');
    }

    public function repliedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'replied_by', 'user_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
    }

    public function statusBadgeClass(): string
    {
        return 'review-status-' . str_replace('_', '-', (string) $this->status);
    }

    public function verifiedLabel(): string
    {
        return self::VERIFIED_TYPES[$this->verified_type] ?? self::VERIFIED_TYPES[self::VERIFIED_NONE];
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function canBeShown(): bool
    {
        return $this->isApproved() && !$this->trashed();
    }

    public function starsText(): string
    {
        $rating = max(0, min(5, (int) $this->rating));

        return str_repeat('★', $rating) . str_repeat('☆', 5 - $rating);
    }

    public function needsAttention(): bool
    {
        return (int) $this->rating <= 2 || $this->status === self::STATUS_REPORTED;
    }
}
