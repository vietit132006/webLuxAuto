<?php

namespace App\Services;

use App\Models\AdminNotification;
use App\Models\AdminNotificationRead;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;
use Throwable;

class AdminNotificationService
{
    private const MODULE_PERMISSION_MAP = [
        'quotes' => ['quotes.view'],
        'orders' => ['orders.view'],
        'test_drives' => ['test_drives.view'],
        'deliveries' => ['orders.view'],
        'live' => ['live.leads.view'],
        'reviews' => ['reviews.view', 'reviews.moderate'],
        'services' => ['services.view'],
        'warranties' => ['warranties.view'],
        'inventory' => ['inventory.view', 'inventory.adjust'],
        'tickets' => ['tickets.view'],
        'customers' => ['customers.view'],
        'promotions' => ['promotions.view'],
        'news' => ['news.view'],
    ];

    public function create(
        string $module,
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        array $data = [],
        string $priority = AdminNotification::PRIORITY_NORMAL,
        ?User $createdBy = null
    ): AdminNotification {
        $module = $this->normalizeModule($module);
        $priority = $this->normalizePriority($priority);

        return AdminNotification::create([
            'module' => $module,
            'type' => trim($type),
            'title' => trim($title),
            'message' => $message ? trim($message) : null,
            'action_url' => $actionUrl ? trim($actionUrl) : null,
            'priority' => $priority,
            'data' => $data === [] ? null : $data,
            'created_by' => $createdBy?->getKey(),
        ]);
    }

    public function createOnce(
        string $module,
        string $type,
        string $title,
        ?string $message = null,
        ?string $actionUrl = null,
        array $data = [],
        string $priority = AdminNotification::PRIORITY_NORMAL,
        ?User $createdBy = null,
        ?int $cooldownHours = null
    ): AdminNotification {
        $module = $this->normalizeModule($module);
        $priority = $this->normalizePriority($priority);
        $type = trim($type);
        $actionUrl = $actionUrl ? trim($actionUrl) : null;

        $existingQuery = AdminNotification::query()
            ->where('module', $module)
            ->where('type', $type)
            ->where('action_url', $actionUrl);

        if ($cooldownHours !== null) {
            $existingQuery->where('created_at', '>=', now()->subHours($cooldownHours));
        }

        $existing = $existingQuery
            ->orderByDesc('created_at')
            ->first();

        if ($existing) {
            return $existing;
        }

        return $this->create($module, $type, $title, $message, $actionUrl, $data, $priority, $createdBy);
    }

    public function markAsRead(AdminNotification $notification, User $user): AdminNotificationRead
    {
        if (!$this->canViewNotification($user, $notification)) {
            throw new AuthorizationException('You cannot read this notification.');
        }

        return AdminNotificationRead::updateOrCreate(
            [
                'notification_id' => $notification->getKey(),
                'user_id' => $user->getKey(),
            ],
            [
                'read_at' => now(),
            ]
        );
    }

    public function markAllAsRead(User $user): int
    {
        if (!$this->tableReady()) {
            return 0;
        }

        $notifications = $this->visibleQueryFor($user)
            ->unreadFor($user)
            ->pluck('id');

        if ($notifications->isEmpty()) {
            return 0;
        }

        $now = now();
        $rows = $notifications
            ->map(fn (int $notificationId): array => [
                'notification_id' => $notificationId,
                'user_id' => $user->getKey(),
                'read_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->all();

        AdminNotificationRead::upsert(
            $rows,
            ['notification_id', 'user_id'],
            ['read_at', 'updated_at']
        );

        return count($rows);
    }

    public function unreadCount(User $user): int
    {
        if (!$this->tableReady()) {
            return 0;
        }

        return (int) $this->visibleQueryFor($user)
            ->unreadFor($user)
            ->count();
    }

    public function unreadCountByModule(User $user): array
    {
        if (!$this->tableReady()) {
            return [];
        }

        return $this->visibleQueryFor($user)
            ->unreadFor($user)
            ->selectRaw('module, COUNT(*) as aggregate')
            ->groupBy('module')
            ->pluck('aggregate', 'module')
            ->map(fn ($value): int => (int) $value)
            ->all();
    }

    public function latestUnread(User $user, int $limit = 10): Collection
    {
        if (!$this->tableReady()) {
            return collect();
        }

        return $this->visibleQueryFor($user)
            ->unreadFor($user)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function visibleQueryFor(User $user): Builder
    {
        $modules = $this->allowedModulesFor($user);

        return AdminNotification::query()
            ->when($modules === [], fn (Builder $query): Builder => $query->whereRaw('1 = 0'))
            ->when($modules !== [], fn (Builder $query): Builder => $query->whereIn('module', $modules));
    }

    public function allowedModulesFor(User $user): array
    {
        if (!$this->userCanAny($user, ['notifications.view'])) {
            return [];
        }

        return collect(array_keys(AdminNotification::MODULES))
            ->filter(fn (string $module): bool => $this->canViewModule($user, $module))
            ->values()
            ->all();
    }

    public function canViewNotification(User $user, AdminNotification $notification): bool
    {
        return $this->canViewModule($user, (string) $notification->module);
    }

    public function canViewModule(User $user, string $module): bool
    {
        if (!$this->userCanAny($user, ['notifications.view'])) {
            return false;
        }

        $permissions = self::MODULE_PERMISSION_MAP[$module] ?? [];

        return $permissions !== [] && $this->userCanAny($user, $permissions);
    }

    public function tableReady(): bool
    {
        try {
            return Schema::hasTable('admin_notifications')
                && Schema::hasTable('admin_notification_reads');
        } catch (Throwable) {
            return false;
        }
    }

    private function normalizeModule(string $module): string
    {
        $module = trim($module);

        if (!array_key_exists($module, AdminNotification::MODULES)) {
            throw new InvalidArgumentException("Unsupported admin notification module [{$module}].");
        }

        return $module;
    }

    private function normalizePriority(string $priority): string
    {
        $priority = trim($priority);

        return array_key_exists($priority, AdminNotification::PRIORITIES)
            ? $priority
            : AdminNotification::PRIORITY_NORMAL;
    }

    private function userCanAny(User $user, array $permissions): bool
    {
        try {
            return $user->canAny($permissions);
        } catch (Throwable) {
            return false;
        }
    }
}
