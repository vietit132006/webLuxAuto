<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LiveSession extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_LIVE = 'live';
    public const STATUS_ENDED = 'ended';
    public const STATUS_CANCELLED = 'cancelled';

    public const STATUSES = [
        self::STATUS_DRAFT => 'Nhap',
        self::STATUS_SCHEDULED => 'Da len lich',
        self::STATUS_LIVE => 'Dang phat song',
        self::STATUS_ENDED => 'Da ket thuc',
        self::STATUS_CANCELLED => 'Da huy',
    ];

    public const PLATFORMS = [
        'youtube' => 'YouTube',
        'facebook' => 'Facebook',
        'tiktok' => 'TikTok',
        'other' => 'Khac',
    ];

    protected $fillable = [
        'live_code',
        'title',
        'slug',
        'description',
        'platform',
        'video_id',
        'video_url',
        'thumbnail',
        'status',
        'starts_at',
        'ends_at',
        'host_user_id',
        'is_active',
        'is_public',
        'replay_enabled',
        'views_count',
        'peak_viewers',
        'cta_label',
        'cta_url',
        'created_by',
        'featured_car_ids',
    ];

    protected function casts(): array
    {
        return [
            'featured_car_ids' => 'array',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'replay_enabled' => 'boolean',
            'views_count' => 'integer',
            'peak_viewers' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LiveSession $session): void {
            if (!$session->live_code) {
                $session->live_code = self::generateLiveCode();
            }

            if (!$session->slug && $session->title) {
                $session->slug = self::uniqueSlug($session->title);
            }
        });

        static::saving(function (LiveSession $session): void {
            if (!$session->live_code) {
                $session->live_code = self::generateLiveCode();
            }

            if (!$session->platform) {
                $session->platform = 'youtube';
            }

            if (!$session->status) {
                $session->status = self::STATUS_DRAFT;
            }

            if ($session->is_active) {
                $session->status = self::STATUS_LIVE;
            }

            if ($session->status !== self::STATUS_LIVE) {
                $session->is_active = false;
            }
        });
    }

    public function sessionCars(): HasMany
    {
        return $this->hasMany(LiveSessionCar::class)
            ->orderBy('display_order')
            ->orderBy('id');
    }

    public function activeSessionCars(): HasMany
    {
        return $this->sessionCars()->where('is_active', true);
    }

    public function cars(): BelongsToMany
    {
        return $this->belongsToMany(Car::class, 'live_session_cars', 'live_session_id', 'car_id')
            ->withPivot([
                'promotion_id',
                'display_order',
                'live_price',
                'live_note',
                'is_focus',
                'is_active',
                'pinned_at',
            ])
            ->withTimestamps();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(LiveLead::class);
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_user_id', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function scopePubliclyVisible(Builder $query): Builder
    {
        return $query->where('is_public', true);
    }

    public function scopeLiveNow(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_LIVE)
            ->where('is_active', true)
            ->whereNotNull('video_id')
            ->where('video_id', '!=', '');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->where('status', self::STATUS_SCHEDULED)
            ->where(function (Builder $dateQuery): void {
                $dateQuery->whereNull('starts_at')
                    ->orWhere('starts_at', '>=', now());
            });
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? (string) $this->status;
    }

    public function statusBadgeClass(): string
    {
        return 'live-status-' . str_replace('_', '-', (string) $this->status);
    }

    public function platformLabel(): string
    {
        return self::PLATFORMS[$this->platform] ?? ucfirst((string) $this->platform);
    }

    public function isLive(): bool
    {
        if (!$this->is_active || $this->status !== self::STATUS_LIVE || !$this->video_id) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        return !$this->ends_at || $this->ends_at->isFuture();
    }

    public function isScheduled(): bool
    {
        return $this->status === self::STATUS_SCHEDULED
            || ($this->starts_at && $this->starts_at->isFuture() && !$this->is_active);
    }

    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED
            || ($this->ends_at && $this->ends_at->isPast() && !$this->is_active);
    }

    public function canShowFrontend(): bool
    {
        if (!$this->is_public) {
            return false;
        }

        if ($this->isLive() || $this->isScheduled()) {
            return true;
        }

        return $this->isEnded() && $this->replay_enabled && (bool) $this->video_id;
    }

    public function canShowPlayer(): bool
    {
        return $this->isLive() || ($this->isEnded() && $this->replay_enabled && (bool) $this->video_id);
    }

    public function frontendStateLabel(): string
    {
        if ($this->isLive()) {
            return 'Dang phat song';
        }

        if ($this->isScheduled()) {
            return 'Sap phat song';
        }

        if ($this->isEnded()) {
            return $this->replay_enabled ? 'Xem lai livestream' : 'Phien live da ket thuc';
        }

        return 'Phien live dang tam tat';
    }

    public function conversionRate(): float
    {
        $leadCount = $this->relationLoaded('leads') ? $this->leads->count() : $this->leads()->count();

        if ($leadCount <= 0) {
            return 0.0;
        }

        $converted = $this->relationLoaded('leads')
            ? $this->leads->where('status', LiveLead::STATUS_CONVERTED)->count()
            : $this->leads()->where('status', LiveLead::STATUS_CONVERTED)->count();

        return round(($converted / $leadCount) * 100, 1);
    }

    public static function statusOptions(): array
    {
        return self::STATUSES;
    }

    public static function platformOptions(): array
    {
        return self::PLATFORMS;
    }

    public static function generateLiveCode(): string
    {
        $lastCode = static::query()
            ->where('live_code', 'like', 'LIVE%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('live_code');

        $nextNumber = 1;

        if ($lastCode && preg_match('/^LIVE(\d+)$/', $lastCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        return 'LIVE' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($title) ?: 'livestream';
        $slug = $baseSlug;
        $counter = 2;

        while (static::query()
            ->when($ignoreId, fn (Builder $query): Builder => $query->whereKeyNot($ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }
}
