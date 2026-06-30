<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Services\AdminNotificationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminNotificationController extends Controller
{
    public function __construct(private readonly AdminNotificationService $notifications)
    {
    }

    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $this->filters($request);
        $allowedModules = $this->notifications->allowedModulesFor($user);

        $query = $this->notifications
            ->visibleQueryFor($user)
            ->with(['createdBy'])
            ->with([
                'reads' => fn ($readQuery) => $readQuery
                    ->where('user_id', $user->getKey())
                    ->whereNotNull('read_at'),
            ]);

        $query
            ->when($filters['q'] !== '', function (Builder $builder) use ($filters): void {
                $search = $filters['q'];

                $builder->where(function (Builder $inner) use ($search): void {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('message', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('module', 'like', "%{$search}%");
                });
            })
            ->forModule($filters['module'])
            ->byPriority($filters['priority'])
            ->when($filters['read_status'] === 'unread', fn (Builder $builder): Builder => $builder->unreadFor($user))
            ->when($filters['read_status'] === 'read', function (Builder $builder) use ($user): void {
                $builder->whereHas('reads', function (Builder $readQuery) use ($user): void {
                    $readQuery->where('user_id', $user->getKey())
                        ->whereNotNull('read_at');
                });
            })
            ->when($filters['date_from'] !== '', fn (Builder $builder): Builder => $builder->where('created_at', '>=', $filters['date_from'] . ' 00:00:00'))
            ->when($filters['date_to'] !== '', fn (Builder $builder): Builder => $builder->where('created_at', '<=', $filters['date_to'] . ' 23:59:59'));

        $notifications = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $moduleOptions = Arr::only(AdminNotification::MODULES, $allowedModules);

        return view('admin.notifications.index', [
            'filters' => $filters,
            'moduleOptions' => $moduleOptions,
            'notifications' => $notifications,
            'priorityOptions' => AdminNotification::PRIORITIES,
            'unreadTotal' => $this->notifications->unreadCount($user),
        ]);
    }

    public function read(Request $request, AdminNotification $notification): RedirectResponse|JsonResponse
    {
        try {
            $this->notifications->markAsRead($notification, $request->user());
        } catch (AuthorizationException) {
            abort(403);
        }

        $target = $this->redirectTarget($request, $notification->action_url);

        if ($request->expectsJson()) {
            return response()->json([
                'redirect_url' => $target,
            ]);
        }

        return redirect()->to($target);
    }

    public function readAll(Request $request): RedirectResponse|JsonResponse
    {
        $count = $this->notifications->markAllAsRead($request->user());

        if ($request->expectsJson()) {
            return response()->json([
                'marked' => $count,
                'total' => $this->notifications->unreadCount($request->user()),
                'modules' => $this->notifications->unreadCountByModule($request->user()),
            ]);
        }

        return back()->with('success', 'Da danh dau ' . number_format($count) . ' thong bao la da doc.');
    }

    public function unreadSummary(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'total' => $this->notifications->unreadCount($user),
            'modules' => $this->notifications->unreadCountByModule($user),
            'latest' => $this->notifications
                ->latestUnread($user, 10)
                ->map(fn (AdminNotification $notification): array => $this->notificationPayload($notification))
                ->values(),
        ]);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'module' => ['nullable', Rule::in(array_keys(AdminNotification::MODULES))],
            'priority' => ['nullable', Rule::in(array_keys(AdminNotification::PRIORITIES))],
            'read_status' => ['nullable', Rule::in(['read', 'unread'])],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'module' => (string) ($validated['module'] ?? ''),
            'priority' => (string) ($validated['priority'] ?? ''),
            'read_status' => (string) ($validated['read_status'] ?? ''),
            'date_from' => (string) ($validated['date_from'] ?? ''),
            'date_to' => (string) ($validated['date_to'] ?? ''),
        ];
    }

    private function notificationPayload(AdminNotification $notification): array
    {
        return [
            'id' => $notification->getKey(),
            'title' => $notification->title,
            'message' => Str::limit((string) $notification->message, 120),
            'module' => $notification->module,
            'module_label' => $notification->moduleLabel(),
            'priority' => $notification->priority,
            'priority_label' => $notification->priorityLabel(),
            'priority_class' => $notification->priorityBadgeClass(),
            'created_at' => $notification->created_at?->toIso8601String(),
            'created_at_human' => $notification->created_at?->diffForHumans(),
            'read_url' => route('admin.notifications.read', $notification),
        ];
    }

    private function redirectTarget(Request $request, ?string $actionUrl): string
    {
        if (!$actionUrl) {
            return route('admin.notifications.index');
        }

        if (Str::startsWith($actionUrl, ['/'])) {
            return url($actionUrl);
        }

        $host = parse_url($actionUrl, PHP_URL_HOST);

        if ($host && $host === $request->getHost()) {
            return $actionUrl;
        }

        return route('admin.notifications.index');
    }
}
