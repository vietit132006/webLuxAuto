<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') — Lux Auto</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/layout-admin.css')
    @endif
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/partial-saved-login-switcher.css')
    @endif
    @stack('styles')
</head>

<body>
    <aside class="admin-sidebar" id="admin-sidebar">
        <a href="{{ route('home') }}" class="sidebar-brand" target="_blank" title="Mở trang khách hàng">
            Lux <span>Auto</span>
        </a>
        @include('partials.admin-sidebar-menu')
    </aside>
    <button type="button" class="mobile-menu-overlay" id="admin-menu-overlay" aria-label="Đóng menu"></button>

    <div class="admin-wrapper">
        <header class="admin-topbar">
            <button type="button" class="mobile-menu-toggle" id="admin-menu-toggle" aria-label="Mở menu quản trị"
                aria-controls="admin-sidebar" aria-expanded="false">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </button>

            <div class="layout-admin-inline-1"></div>

            <div class="admin-topbar-info">
                @if($isAccountSwitching && $accountSwitcher)
                    <div class="account-switch-notice">
                        <span>Đang xem: <strong>{{ auth()->user()->name ?? 'Tài khoản' }}</strong></span>
                        <form method="POST" action="{{ route('account-switch.restore') }}">
                            @csrf
                            <button type="submit" class="account-switch-restore">Quay lại {{ $accountSwitcher->name }}</button>
                        </form>
                    </div>
                @endif

                <div class="admin-account-menu" data-account-menu>
                    <button type="button" class="admin-account-trigger" data-account-menu-trigger
                        aria-haspopup="true" aria-expanded="false">
                        <span class="admin-account-avatar">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </span>
                        <span class="admin-account-summary">
                            <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                            <span>{{ auth()->user()->email ?? 'admin@luxauto.local' }}</span>
                        </span>
                        <svg class="admin-account-chevron" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div class="admin-account-dropdown" role="menu">
                        <div class="admin-account-card">
                            <span class="admin-account-avatar">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </span>
                            <div>
                                <strong>{{ auth()->user()->name ?? 'Admin' }}</strong>
                                <span>{{ auth()->user()->email ?? '' }}</span>
                            </div>
                        </div>

                        <a href="{{ route('profile.index') }}" class="admin-account-item" role="menuitem">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                            </svg>
                            Thông tin tài khoản
                        </a>
                        <button type="button" class="admin-account-item" data-open-account-switcher role="menuitem">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h15M16.5 3 21 7.5m0 0L16.5 12M21 7.5H6" />
                            </svg>
                            Chuyển đổi tài khoản
                        </button>
                        <div class="admin-account-divider"></div>
                        <a href="{{ route('logout') }}" class="admin-account-item is-danger" role="menuitem">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-6 3 3m0 0-3 3m3-3H9" />
                            </svg>
                            Đăng xuất
                        </a>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-main">
            @yield('content')
        </main>
    </div>

    @include('partials.saved-login-switcher')

    <script>
        (() => {
            const body = document.body;
            const toggleButton = document.getElementById('admin-menu-toggle');
            const overlay = document.getElementById('admin-menu-overlay');
            const sidebar = document.getElementById('admin-sidebar');

            if (!toggleButton || !overlay || !sidebar) {
                return;
            }

            const setMenuOpen = (isOpen) => {
                body.classList.toggle('sidebar-open', isOpen);
                toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            };

            toggleButton.addEventListener('click', () => {
                setMenuOpen(!body.classList.contains('sidebar-open'));
            });

            overlay.addEventListener('click', () => setMenuOpen(false));

            const syncSidebarGroup = (group, isOpen) => {
                const trigger = group.querySelector('[data-sidebar-group-toggle]');
                const panel = trigger
                    ? document.getElementById(trigger.getAttribute('aria-controls'))
                    : group.querySelector('.sidebar-group-children');

                if (!trigger) {
                    return;
                }

                group.classList.toggle('is-open', isOpen);
                trigger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');

                if (panel) {
                    panel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
                    panel.querySelectorAll('a').forEach((link) => {
                        link.tabIndex = isOpen ? 0 : -1;
                    });
                }
            };

            sidebar.querySelectorAll('[data-sidebar-group]').forEach((group) => {
                syncSidebarGroup(group, group.classList.contains('is-open'));

                const trigger = group.querySelector('[data-sidebar-group-toggle]');

                trigger.addEventListener('click', () => {
                    const isOpen = !group.classList.contains('is-open');

                    syncSidebarGroup(group, isOpen);
                });
            });

            sidebar.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => setMenuOpen(false));
            });

            window.addEventListener('keydown', (event) => {
                if (event.key === 'Escape') {
                    setMenuOpen(false);
                }
            });

            window.addEventListener('resize', () => {
                if (window.innerWidth > 768) {
                    setMenuOpen(false);
                }
            });
        })();
    </script>

    @stack('scripts')
</body>

</html>