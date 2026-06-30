<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdminNotification extends Model
{
    public const PRIORITY_LOW = 'low';
    public const PRIORITY_NORMAL = 'normal';
    public const PRIORITY_HIGH = 'high';
    public const PRIORITY_URGENT = 'urgent';

    public const PRIORITIES = [
        self::PRIORITY_LOW => 'Thap',
        self::PRIORITY_NORMAL => 'Binh thuong',
        self::PRIORITY_HIGH => 'Cao',
        self::PRIORITY_URGENT => 'Khan cap',
    ];

    public const MODULES = [
        'quotes' => 'Bao gia',
        'orders' => 'Don hang',
        'test_drives' => 'Lai thu',
        'deliveries' => 'Giao xe',
        'live' => 'Livestream',
        'reviews' => 'Danh gia',
        'services' => 'Bao duong',
        'warranties' => 'Bao hanh',
        'inventory' => 'Ton kho',
        'tickets' => 'Ho tro',
        'customers' => 'Khach hang',
        'promotions' => 'Khuyen mai',
        'news' => 'Tin tuc',
    ];

    protected $fillable = [
        'type',
        'module',
        'title',
        'message',
        'action_url',
        'priority',
        'data',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'data' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function reads(): HasMany
    {
        return $this->hasMany(AdminNotificationRead::class, 'notification_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[$this->priority] ?? (string) $this->priority;
    }

    public function priorityBadgeClass(): string
    {
        return 'notification-priority-' . str_replace('_', '-', (string) $this->priority);
    }

    public function moduleLabel(): string
    {
        return self::MODULES[$this->module] ?? (string) $this->module;
    }

    public function isReadBy(User $user): bool
    {
        return $this->reads()
            ->where('user_id', $user->getKey())
            ->whereNotNull('read_at')
            ->exists();
    }

    public function scopeUnreadFor(Builder $query, User $user): Builder
    {
        return $query->whereDoesntHave('reads', function (Builder $readQuery) use ($user): void {
            $readQuery->where('user_id', $user->getKey())
                ->whereNotNull('read_at');
        });
    }

    public function scopeForModule(Builder $query, ?string $module): Builder
    {
        return $module ? $query->where('module', $module) : $query;
    }

    public function scopeByPriority(Builder $query, ?string $priority): Builder
    {
        return $priority ? $query->where('priority', $priority) : $query;
    }
}
