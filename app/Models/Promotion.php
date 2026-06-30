<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Promotion extends Model
{
    use SoftDeletes;

    public const TYPE_CASH_DISCOUNT = 'cash_discount';
    public const TYPE_PERCENT_DISCOUNT = 'percent_discount';
    public const TYPE_GIFT = 'gift';
    public const TYPE_INSURANCE_SUPPORT = 'insurance_support';
    public const TYPE_REGISTRATION_FEE_SUPPORT = 'registration_fee_support';
    public const TYPE_INSTALLMENT_SUPPORT = 'installment_support';
    public const TYPE_MAINTENANCE_PACKAGE = 'maintenance_package';
    public const TYPE_SERVICE_DISCOUNT = 'service_discount';
    public const TYPE_OTHER = 'other';

    public const TYPES = [
        self::TYPE_CASH_DISCOUNT => 'Giảm tiền mặt',
        self::TYPE_PERCENT_DISCOUNT => 'Giảm theo phần trăm',
        self::TYPE_GIFT => 'Quà tặng',
        self::TYPE_INSURANCE_SUPPORT => 'Hỗ trợ bảo hiểm',
        self::TYPE_REGISTRATION_FEE_SUPPORT => 'Hỗ trợ lệ phí trước bạ',
        self::TYPE_INSTALLMENT_SUPPORT => 'Hỗ trợ trả góp',
        self::TYPE_MAINTENANCE_PACKAGE => 'Gói bảo dưỡng',
        self::TYPE_SERVICE_DISCOUNT => 'Ưu đãi dịch vụ',
        self::TYPE_OTHER => 'Khác',
    ];

    public const DISCOUNT_FIXED = 'fixed';
    public const DISCOUNT_PERCENT = 'percent';
    public const DISCOUNT_NONE = 'none';

    public const DISCOUNT_TYPES = [
        self::DISCOUNT_FIXED => 'Giảm số tiền cố định',
        self::DISCOUNT_PERCENT => 'Giảm theo phần trăm',
        self::DISCOUNT_NONE => 'Không giảm trực tiếp',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_ARCHIVED = 'archived';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Nháp',
        self::STATUS_ACTIVE => 'Đang hoạt động',
        self::STATUS_SCHEDULED => 'Sắp diễn ra',
        self::STATUS_EXPIRED => 'Đã hết hạn',
        self::STATUS_ARCHIVED => 'Lưu trữ',
    ];

    protected $fillable = [
        'promotion_code',
        'title',
        'slug',
        'short_description',
        'content',
        'banner_image',
        'banner_alt',
        'promotion_type',
        'discount_type',
        'discount_value',
        'max_discount_value',
        'gift_description',
        'terms',
        'start_at',
        'end_at',
        'status',
        'is_featured',
        'is_public',
        'auto_apply',
        'usage_limit',
        'usage_count',
        'priority',
        'seo_title',
        'seo_description',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'max_discount_value' => 'decimal:2',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
            'auto_apply' => 'boolean',
            'usage_limit' => 'integer',
            'usage_count' => 'integer',
            'priority' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function targets(): HasMany
    {
        return $this->hasMany(PromotionTarget::class);
    }

    public function quotePromotions(): HasMany
    {
        return $this->hasMany(QuotePromotion::class);
    }

    public function orderPromotions(): HasMany
    {
        return $this->hasMany(OrderPromotion::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function scopeEffective(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_ACTIVE)
            ->where(function (Builder $dateQuery): void {
                $dateQuery->whereNull('start_at')
                    ->orWhere('start_at', '<=', now());
            })
            ->where(function (Builder $dateQuery): void {
                $dateQuery->whereNull('end_at')
                    ->orWhere('end_at', '>=', now());
            })
            ->where(function (Builder $usageQuery): void {
                $usageQuery->whereNull('usage_limit')
                    ->orWhereColumn('usage_count', '<', 'usage_limit');
            });
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_public', true)->effective();
    }

    public function scopeOrderedForDisplay(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_featured')
            ->orderByDesc('priority')
            ->orderBy('end_at')
            ->orderByDesc('created_at');
    }

    public function statusLabel(): string
    {
        if ($this->status === self::STATUS_ACTIVE) {
            if ($this->isScheduled()) {
                return self::STATUSES[self::STATUS_SCHEDULED];
            }

            if ($this->isExpired()) {
                return self::STATUSES[self::STATUS_EXPIRED];
            }
        }

        return self::STATUSES[$this->status] ?? (string) $this->status;
    }

    public function statusBadgeClass(): string
    {
        if ($this->status === self::STATUS_ACTIVE && $this->isScheduled()) {
            return 'promotion-status-scheduled';
        }

        if ($this->status === self::STATUS_ACTIVE && $this->isExpired()) {
            return 'promotion-status-expired';
        }

        return 'promotion-status-' . str_replace('_', '-', (string) $this->status);
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->promotion_type] ?? (string) $this->promotion_type;
    }

    public function discountLabel(): string
    {
        $discountType = $this->discount_type ?: self::DISCOUNT_NONE;
        $value = (float) ($this->discount_value ?? 0);

        if ($discountType === self::DISCOUNT_FIXED && $value > 0) {
            return number_format($value, 0, ',', '.') . ' đ';
        }

        if ($discountType === self::DISCOUNT_PERCENT && $value > 0) {
            $label = rtrim(rtrim(number_format($value, 2, ',', '.'), '0'), ',') . '%';

            if ((float) ($this->max_discount_value ?? 0) > 0) {
                $label .= ' tối đa ' . number_format((float) $this->max_discount_value, 0, ',', '.') . ' đ';
            }

            return $label;
        }

        return $this->gift_description ?: 'Ưu đãi phi tiền mặt';
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE
            && ! $this->isScheduled()
            && ! $this->isExpired()
            && ! $this->hasReachedUsageLimit();
    }

    public function isExpired(): bool
    {
        return $this->end_at instanceof Carbon && $this->end_at->isPast();
    }

    public function isScheduled(): bool
    {
        return $this->start_at instanceof Carbon && $this->start_at->isFuture();
    }

    public function isApplicableToCar(Car $car): bool
    {
        $this->loadMissing('targets');
        $car->loadMissing('modelInfo.brand');

        if ($this->targets->isEmpty()) {
            return false;
        }

        $brandId = $car->modelInfo?->brand_id;
        $modelId = $car->car_model_id;

        return $this->targets->contains(function (PromotionTarget $target) use ($brandId, $modelId, $car): bool {
            return match ($target->target_type) {
                PromotionTarget::TYPE_ALL => true,
                PromotionTarget::TYPE_BRAND => $brandId !== null && (int) $target->target_id === (int) $brandId,
                PromotionTarget::TYPE_MODEL => $modelId !== null && (int) $target->target_id === (int) $modelId,
                PromotionTarget::TYPE_CAR => (int) $target->target_id === (int) $car->car_id,
                default => false,
            };
        });
    }

    public function calculateDiscountAmount(float $price): float
    {
        $value = (float) ($this->discount_value ?? 0);

        if ($value <= 0) {
            return 0.0;
        }

        $amount = match ($this->discount_type) {
            self::DISCOUNT_FIXED => $value,
            self::DISCOUNT_PERCENT => max(0, $price) * ($value / 100),
            default => 0,
        };

        $maxDiscount = (float) ($this->max_discount_value ?? 0);

        if ($maxDiscount > 0 && $amount > $maxDiscount) {
            $amount = $maxDiscount;
        }

        return round(max(0, min($amount, max(0, $price))), 2);
    }

    public function hasReachedUsageLimit(): bool
    {
        return $this->usage_limit !== null && (int) $this->usage_count >= (int) $this->usage_limit;
    }

    public function bannerUrl(): ?string
    {
        if (! $this->banner_image) {
            return null;
        }

        if (str_starts_with($this->banner_image, 'http://') || str_starts_with($this->banner_image, 'https://')) {
            return $this->banner_image;
        }

        if (str_starts_with($this->banner_image, '/storage/')) {
            return $this->banner_image;
        }

        return asset('storage/' . ltrim($this->banner_image, '/'));
    }

    public function targetSummary(int $limit = 3): string
    {
        $this->loadMissing('targets.brand', 'targets.carModel.brand', 'targets.car.modelInfo.brand');

        if ($this->targets->isEmpty()) {
            return 'Chưa chọn đối tượng';
        }

        if ($this->targets->contains(fn (PromotionTarget $target): bool => $target->target_type === PromotionTarget::TYPE_ALL)) {
            return 'Toàn bộ xe';
        }

        $labels = $this->targets
            ->map(fn (PromotionTarget $target): string => $target->targetLabel())
            ->filter()
            ->values();

        $suffix = $labels->count() > $limit ? ' +' . ($labels->count() - $limit) : '';

        return $labels->take($limit)->implode(', ') . $suffix;
    }

    public function refreshUsageCount(): void
    {
        $this->forceFill([
            'usage_count' => $this->quotePromotions()->count() + $this->orderPromotions()->count(),
        ])->saveQuietly();
    }

    public static function generatePromotionCode(): string
    {
        $lastCode = static::withTrashed()
            ->where('promotion_code', 'like', 'KM%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('promotion_code');

        $nextNumber = 1;

        if ($lastCode && preg_match('/^KM(\d+)$/', $lastCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return 'KM' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'khuyen-mai';
        $slug = $baseSlug;
        $counter = 2;

        while (static::withTrashed()
            ->when($ignoreId, fn (Builder $query): Builder => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
