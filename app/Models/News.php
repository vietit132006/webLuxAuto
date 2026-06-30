<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_ARCHIVED = 'archived';

    public const CTA_NONE = 'none';
    public const CTA_QUOTE = 'quote';
    public const CTA_TEST_DRIVE = 'test_drive';
    public const CTA_CAR_DETAIL = 'car_detail';
    public const CTA_CONTACT = 'contact';

    protected $table = 'news';

    protected $fillable = [
        'category_id',
        'author_id',
        'title',
        'slug',
        'summary',
        'content',
        'thumbnail',
        'thumbnail_alt',
        'status',
        'is_featured',
        'published_at',
        'scheduled_at',
        'views_count',
        'reading_time',
        'seo_title',
        'seo_description',
        'seo_keywords',
        'canonical_url',
        'related_brand_id',
        'related_model_id',
        'related_car_id',
        'cta_type',
        'cta_label',
        'cta_url',
    ];

    protected function casts(): array
    {
        return [
            'category_id' => 'integer',
            'author_id' => 'integer',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'views_count' => 'integer',
            'reading_time' => 'integer',
            'related_brand_id' => 'integer',
            'related_model_id' => 'integer',
            'related_car_id' => 'integer',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT => 'Nháp',
            self::STATUS_PUBLISHED => 'Đã xuất bản',
            self::STATUS_SCHEDULED => 'Hẹn giờ đăng',
            self::STATUS_ARCHIVED => 'Lưu trữ',
        ];
    }

    public static function ctaTypes(): array
    {
        return [
            self::CTA_NONE => 'Không hiển thị CTA',
            self::CTA_QUOTE => 'Nhận báo giá',
            self::CTA_TEST_DRIVE => 'Đặt lịch lái thử',
            self::CTA_CAR_DETAIL => 'Xem xe',
            self::CTA_CONTACT => 'Liên hệ tư vấn',
        ];
    }

    public function statusLabel(): string
    {
        return self::statuses()[$this->status] ?? 'Không rõ';
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PUBLISHED => 'is-success',
            self::STATUS_SCHEDULED => 'is-warning',
            self::STATUS_ARCHIVED => 'is-muted',
            default => 'is-draft',
        };
    }

    public function isPublished(): bool
    {
        return $this->status === self::STATUS_PUBLISHED;
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isArchived(): bool
    {
        return $this->status === self::STATUS_ARCHIVED;
    }

    public function effectivePublishedAt()
    {
        return $this->published_at ?: $this->scheduled_at ?: $this->created_at;
    }

    public function thumbnailUrl(): ?string
    {
        if (!$this->thumbnail) {
            return null;
        }

        if (str_starts_with($this->thumbnail, 'http://') || str_starts_with($this->thumbnail, 'https://')) {
            return $this->thumbnail;
        }

        if (str_starts_with($this->thumbnail, '/storage/')) {
            return $this->thumbnail;
        }

        if (str_starts_with($this->thumbnail, 'storage/')) {
            return '/' . ltrim($this->thumbnail, '/');
        }

        return Storage::url($this->thumbnail);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(NewsCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id', 'user_id');
    }

    public function relatedBrand(): BelongsTo
    {
        return $this->belongsTo(Brand::class, 'related_brand_id', 'brand_id');
    }

    public function relatedModel(): BelongsTo
    {
        return $this->belongsTo(CarModel::class, 'related_model_id');
    }

    public function relatedCar(): BelongsTo
    {
        return $this->belongsTo(Car::class, 'related_car_id', 'car_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(NewsTag::class, 'news_tag', 'news_id', 'tag_id')->withTimestamps();
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where(function (Builder $statusQuery): void {
            $statusQuery->where(function (Builder $publishedQuery): void {
                $publishedQuery->where('status', self::STATUS_PUBLISHED)
                    ->where(function (Builder $timeQuery): void {
                        $timeQuery->whereNull('published_at')
                            ->orWhere('published_at', '<=', now());
                    });
            })->orWhere(function (Builder $scheduledQuery): void {
                $scheduledQuery->where('status', self::STATUS_SCHEDULED)
                    ->where(function (Builder $timeQuery): void {
                        $timeQuery->where('scheduled_at', '<=', now())
                            ->orWhere('published_at', '<=', now());
                    });
            });
        });
    }

    public function scopePublishedFirst(Builder $query): Builder
    {
        return $query->orderByRaw('COALESCE(published_at, scheduled_at, created_at) desc')
            ->orderByDesc('id');
    }
}
