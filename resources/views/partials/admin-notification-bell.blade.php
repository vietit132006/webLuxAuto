@if ($adminNotificationsEnabled ?? false)
    <div class="admin-notification-menu"
        data-admin-notifications
        data-summary-url="{{ route('admin.notifications.unread-summary') }}"
        data-csrf-token="{{ csrf_token() }}">
        <button type="button"
            class="admin-notification-trigger"
            data-admin-notification-trigger
            aria-haspopup="true"
            aria-expanded="false"
            aria-label="Mo thong bao noi bo">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15 17h5l-1.4-1.4A2 2 0 0 1 18 14.2V11a6 6 0 1 0-12 0v3.2c0 .5-.2 1-.6 1.4L4 17h5" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.5 19a2.5 2.5 0 0 0 5 0" />
            </svg>
            <span class="admin-notification-count {{ ($adminNotificationUnreadTotal ?? 0) > 0 ? '' : 'is-hidden' }}"
                data-notification-total>
                {{ ($adminNotificationUnreadTotal ?? 0) > 99 ? '99+' : ($adminNotificationUnreadTotal ?? 0) }}
            </span>
        </button>

        <div class="admin-notification-dropdown" role="menu">
            <div class="admin-notification-header">
                <div>
                    <strong>Thong bao noi bo</strong>
                    <span data-notification-header-count>{{ number_format($adminNotificationUnreadTotal ?? 0) }} chua doc</span>
                </div>
                @can('notifications.mark_read')
                    <form method="POST" action="{{ route('admin.notifications.read-all') }}" data-notification-read-all-form>
                        @csrf
                        <button type="submit" class="admin-notification-mark-all">Doc tat ca</button>
                    </form>
                @endcan
            </div>

            <div class="admin-notification-list" data-notification-latest>
                @forelse (($adminLatestNotifications ?? collect()) as $notification)
                    <form method="POST" action="{{ route('admin.notifications.read', $notification) }}" class="admin-notification-row-form">
                        @csrf
                        <button type="submit" class="admin-notification-item {{ $notification->priorityBadgeClass() }}" role="menuitem">
                            <span class="admin-notification-item-main">
                                <span class="admin-notification-title">{{ $notification->title }}</span>
                                <span class="admin-notification-meta">
                                    <span>{{ $notification->moduleLabel() }}</span>
                                    <span>{{ $notification->created_at?->diffForHumans() }}</span>
                                </span>
                            </span>
                            <span class="admin-notification-priority {{ $notification->priorityBadgeClass() }}">
                                {{ $notification->priorityLabel() }}
                            </span>
                        </button>
                    </form>
                @empty
                    <div class="admin-notification-empty" data-notification-empty>
                        Chua co thong bao moi.
                    </div>
                @endforelse
            </div>

            <a href="{{ route('admin.notifications.index') }}" class="admin-notification-view-all" role="menuitem">
                Xem tat ca thong bao
            </a>
        </div>
    </div>
@endif
