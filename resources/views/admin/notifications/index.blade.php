@extends('layouts.admin')

@section('title', 'Thong bao noi bo')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-notifications.css')
    @endif
@endpush

@section('content')
    <div class="admin-notifications-page">
        <div class="notifications-header">
            <div>
                <p class="notifications-kicker">Trung tam van hanh</p>
                <h1>Thong bao noi bo</h1>
                <p>{{ number_format($unreadTotal) }} thong bao chua doc dang hien thi theo quyen cua tai khoan.</p>
            </div>

            @can('notifications.mark_read')
                <form method="POST" action="{{ route('admin.notifications.read-all') }}">
                    @csrf
                    <button type="submit" class="notifications-primary-action">
                        Danh dau tat ca da doc
                    </button>
                </form>
            @endcan
        </div>

        @if (session('success'))
            <div class="notifications-alert" role="status">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('admin.notifications.index') }}" class="notifications-filter-panel">
            <label>
                <span>Tu khoa</span>
                <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tieu de, noi dung, module">
            </label>

            <label>
                <span>Module</span>
                <select name="module">
                    <option value="">Tat ca</option>
                    @foreach ($moduleOptions as $module => $label)
                        <option value="{{ $module }}" @selected($filters['module'] === $module)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Priority</span>
                <select name="priority">
                    <option value="">Tat ca</option>
                    @foreach ($priorityOptions as $priority => $label)
                        <option value="{{ $priority }}" @selected($filters['priority'] === $priority)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>

            <label>
                <span>Trang thai</span>
                <select name="read_status">
                    <option value="">Tat ca</option>
                    <option value="unread" @selected($filters['read_status'] === 'unread')>Chua doc</option>
                    <option value="read" @selected($filters['read_status'] === 'read')>Da doc</option>
                </select>
            </label>

            <label>
                <span>Tu ngay</span>
                <input type="date" name="date_from" value="{{ $filters['date_from'] }}">
            </label>

            <label>
                <span>Den ngay</span>
                <input type="date" name="date_to" value="{{ $filters['date_to'] }}">
            </label>

            <div class="notifications-filter-actions">
                <button type="submit">Loc</button>
                <a href="{{ route('admin.notifications.index') }}">Xoa loc</a>
            </div>
        </form>

        <div class="notifications-list-panel">
            @forelse ($notifications as $notification)
                @php
                    $isRead = $notification->reads->isNotEmpty();
                @endphp

                <article class="notification-card {{ $isRead ? 'is-read' : 'is-unread' }} {{ $notification->priorityBadgeClass() }}">
                    <div class="notification-card-main">
                        <div class="notification-card-topline">
                            <span class="notification-module-badge">{{ $notification->moduleLabel() }}</span>
                            <span class="notification-priority-badge {{ $notification->priorityBadgeClass() }}">
                                {{ $notification->priorityLabel() }}
                            </span>
                            <span class="notification-read-state {{ $isRead ? 'is-read' : 'is-unread' }}">
                                {{ $isRead ? 'Da doc' : 'Chua doc' }}
                            </span>
                        </div>

                        <h2>{{ $notification->title }}</h2>

                        @if ($notification->message)
                            <p>{{ $notification->message }}</p>
                        @endif

                        <div class="notification-card-meta">
                            <span>{{ $notification->created_at?->format('d/m/Y H:i') }}</span>
                            <span>{{ $notification->createdBy?->name ?? 'He thong' }}</span>
                            <span>{{ $notification->type }}</span>
                        </div>
                    </div>

                    <div class="notification-card-actions">
                        @can('notifications.mark_read')
                            <form method="POST" action="{{ route('admin.notifications.read', $notification) }}">
                                @csrf
                                <button type="submit">
                                    {{ $notification->action_url ? 'Xem chi tiet' : 'Danh dau da doc' }}
                                </button>
                            </form>
                        @else
                            @if ($notification->action_url)
                                <a href="{{ $notification->action_url }}">Xem chi tiet</a>
                            @endif
                        @endcan
                    </div>
                </article>
            @empty
                <div class="notifications-empty">
                    Khong co thong bao phu hop voi bo loc hien tai.
                </div>
            @endforelse
        </div>

        <div class="notifications-pagination">
            {{ $notifications->links() }}
        </div>
    </div>
@endsection
