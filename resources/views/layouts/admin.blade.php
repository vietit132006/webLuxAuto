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

    <style>
        :root {
            --bg: #0c0f14;
            --surface: #141a22;
            --border: #243042;
            --text: #e8edf4;
            --muted: #8b97ab;
            --accent: #c9a962;
            --accent-dim: #9a7b3c;
            --sidebar-width: 260px;
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            display: flex;
            overflow-x: hidden;
        }

        a {
            text-decoration: none;
            transition: all 0.2s ease-in-out;
        }

        /* --- SIDEBAR BÊN TRÁI --- */
        .admin-sidebar {
            width: var(--sidebar-width);
            background: var(--surface);
            border-right: 1px solid var(--border);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            z-index: 100;
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            height: 64px;
            display: flex;
            align-items: center;
            padding: 0 1.5rem;
            border-bottom: 1px solid var(--border);
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: var(--text);
            flex-shrink: 0;
        }

        .sidebar-brand span {
            color: var(--accent);
        }

        .sidebar-nav {
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
            overflow-y: auto;
        }

        /* Custom thanh cuộn cho Sidebar */
        .sidebar-nav::-webkit-scrollbar {
            width: 5px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: var(--border);
            border-radius: 10px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb:hover {
            background: var(--muted);
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--muted);
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
            transition: all 0.2s ease-in-out;
        }

        .sidebar-link:focus-visible {
            outline: 2px solid rgba(201, 169, 98, 0.55);
            outline-offset: 2px;
        }

        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.04);
            color: var(--text);
            transform: translateX(3px);
        }

        /* Highlight menu đang được chọn */
        .sidebar-link.active {
            background: rgba(201, 169, 98, 0.1);
            color: var(--accent);
            border-left: 3px solid var(--accent);
        }

        /* Highlight đặc biệt cho nút Live */
        .sidebar-link-live.active {
            background: rgba(239, 68, 68, 0.1) !important;
            color: #ef4444 !important;
            border-left: 3px solid #ef4444 !important;
        }

        .sidebar-link-live.active svg {
            filter: drop-shadow(0 0 5px rgba(239, 68, 68, 0.5));
        }

        .sidebar-group {
            display: flex;
            flex-direction: column;
            gap: 0.12rem;
        }

        .sidebar-parent {
            width: 100%;
            border: 0;
            border-left: 3px solid transparent;
            background: transparent;
            cursor: pointer;
            font: inherit;
            text-align: left;
        }

        .sidebar-parent.active {
            border-left-color: var(--accent);
        }

        .sidebar-icon {
            width: 20px;
            height: 20px;
            flex: 0 0 auto;
            margin-right: 10px;
        }

        .sidebar-label {
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sidebar-parent .sidebar-label {
            flex: 1;
        }

        .sidebar-caret {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
            margin-left: auto;
            color: var(--muted);
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .sidebar-group.is-open .sidebar-caret {
            color: var(--accent);
            transform: rotate(180deg);
        }

        .sidebar-group-children {
            display: flex;
            flex-direction: column;
            gap: 0.12rem;
            max-height: 0;
            overflow: hidden;
            padding-left: 0.55rem;
            opacity: 0;
            transition: max-height 0.24s ease, opacity 0.18s ease, margin 0.18s ease;
        }

        .sidebar-group.is-open .sidebar-group-children {
            max-height: 440px;
            margin: 0.12rem 0 0.28rem;
            opacity: 1;
        }

        .sidebar-child-link {
            padding: 0.62rem 0.85rem;
            border-radius: 7px;
            font-size: 0.9rem;
        }

        .sidebar-child-link .sidebar-icon {
            width: 18px;
            height: 18px;
            margin-right: 9px;
        }

        @media (prefers-reduced-motion: reduce) {
            .sidebar-caret,
            .sidebar-group-children {
                transition: none;
            }
        }

        /* --- KHU VỰC NỘI DUNG BÊN PHẢI --- */
        .admin-wrapper {
            flex: 0 0 calc(100vw - var(--sidebar-width));
            width: calc(100vw - var(--sidebar-width));
            max-width: calc(100vw - var(--sidebar-width));
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            min-width: 0;
            transition: margin-left 0.3s ease;
        }

        .admin-topbar {
            height: 64px;
            border-bottom: 1px solid var(--border);
            background: rgba(12, 15, 20, 0.95);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 0 2rem;
            position: sticky;
            top: 0;
            z-index: 40;
        }

        .mobile-menu-toggle {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: rgba(255, 255, 255, 0.035);
            color: var(--text);
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.2s ease;
        }

        .mobile-menu-toggle:hover {
            border-color: rgba(201, 169, 98, 0.45);
            color: var(--accent);
            background: rgba(201, 169, 98, 0.08);
        }

        .mobile-menu-toggle svg {
            width: 22px;
            height: 22px;
        }

        .mobile-menu-overlay {
            display: none;
            position: fixed;
            inset: 0;
            border: 0;
            padding: 0;
            background: rgba(0, 0, 0, 0.55);
            cursor: pointer;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease;
            z-index: 90;
        }

        .admin-topbar-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .admin-account-menu {
            position: relative;
            flex: 0 0 auto;
        }

        .admin-account-trigger {
            min-height: 42px;
            display: inline-flex;
            align-items: center;
            gap: 0.7rem;
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            padding: 0.32rem 0.48rem 0.32rem 0.36rem;
            background: rgba(255, 255, 255, 0.035);
            color: var(--text);
            cursor: pointer;
            font: inherit;
            transition: border-color 0.2s ease, background 0.2s ease, color 0.2s ease;
        }

        .admin-account-trigger:hover,
        .admin-account-trigger:focus-visible,
        .admin-account-menu.is-open .admin-account-trigger {
            border-color: rgba(201, 169, 98, 0.48);
            background: rgba(201, 169, 98, 0.08);
            outline: none;
        }

        .admin-account-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex: 0 0 auto;
            border: 1px solid rgba(201, 169, 98, 0.42);
            background: linear-gradient(135deg, rgba(201, 169, 98, 0.24), rgba(15, 23, 42, 0.95));
            color: var(--accent);
            font-size: 0.9rem;
            font-weight: 850;
        }

        .admin-account-summary {
            display: grid;
            min-width: 0;
            text-align: left;
            line-height: 1.15;
        }

        .admin-account-summary strong {
            max-width: 180px;
            overflow: hidden;
            color: var(--text);
            font-size: 0.9rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-account-summary span {
            max-width: 180px;
            overflow: hidden;
            color: var(--muted);
            font-size: 0.74rem;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-account-chevron {
            width: 16px;
            height: 16px;
            flex: 0 0 auto;
            color: var(--muted);
            transition: transform 0.2s ease, color 0.2s ease;
        }

        .admin-account-menu.is-open .admin-account-chevron {
            color: var(--accent);
            transform: rotate(180deg);
        }

        .admin-account-dropdown {
            position: absolute;
            top: calc(100% + 0.65rem);
            right: 0;
            width: min(280px, calc(100vw - 2rem));
            border: 1px solid rgba(201, 169, 98, 0.22);
            border-radius: 12px;
            padding: 0.45rem;
            background: rgba(20, 26, 34, 0.98);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.45);
            opacity: 0;
            pointer-events: none;
            transform: translateY(8px);
            transition: opacity 0.18s ease, transform 0.18s ease;
            z-index: 70;
        }

        .admin-account-menu.is-open .admin-account-dropdown {
            opacity: 1;
            pointer-events: auto;
            transform: translateY(0);
        }

        .admin-account-dropdown::before {
            content: '';
            position: absolute;
            top: -6px;
            right: 18px;
            width: 10px;
            height: 10px;
            border-top: 1px solid rgba(201, 169, 98, 0.22);
            border-left: 1px solid rgba(201, 169, 98, 0.22);
            background: rgba(20, 26, 34, 0.98);
            transform: rotate(45deg);
        }

        .admin-account-card {
            display: flex;
            gap: 0.7rem;
            align-items: center;
            padding: 0.65rem 0.7rem 0.75rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            margin-bottom: 0.35rem;
        }

        .admin-account-card .admin-account-avatar {
            width: 40px;
            height: 40px;
        }

        .admin-account-card strong,
        .admin-account-card span {
            display: block;
            max-width: 184px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .admin-account-card strong {
            color: var(--text);
            font-size: 0.94rem;
        }

        .admin-account-card span {
            color: var(--muted);
            font-size: 0.78rem;
        }

        .admin-account-item {
            width: 100%;
            min-height: 42px;
            display: flex;
            align-items: center;
            gap: 0.65rem;
            border: 0;
            border-radius: 8px;
            padding: 0.62rem 0.72rem;
            background: transparent;
            color: var(--text);
            cursor: pointer;
            font: inherit;
            font-size: 0.9rem;
            font-weight: 650;
            text-align: left;
            transition: color 0.2s ease, background 0.2s ease;
        }

        .admin-account-item:hover,
        .admin-account-item:focus-visible {
            background: rgba(201, 169, 98, 0.1);
            color: var(--accent);
            outline: none;
        }

        .admin-account-item svg {
            width: 18px;
            height: 18px;
            flex: 0 0 auto;
            color: var(--accent);
        }

        .admin-account-item.is-danger {
            color: #fca5a5;
        }

        .admin-account-item.is-danger svg {
            color: #f87171;
        }

        .admin-account-item.is-danger:hover,
        .admin-account-item.is-danger:focus-visible {
            color: #fecaca;
            background: rgba(239, 68, 68, 0.12);
        }

        .admin-account-divider {
            height: 1px;
            margin: 0.35rem 0;
            background: rgba(255, 255, 255, 0.06);
        }

        .btn-logout {
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            border: 1px solid var(--border);
            color: var(--muted);
            font-size: 0.875rem;
        }

        .btn-logout:hover {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #ef4444;
        }

        .account-switcher {
            display: flex;
            align-items: center;
            gap: 0.45rem;
            min-width: 0;
        }

        .account-switcher select {
            width: 240px;
            height: 36px;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #0a0d12;
            color: var(--text);
            padding: 0 0.65rem;
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }

        .account-switcher select:focus {
            border-color: rgba(201, 169, 98, 0.7);
            box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.12);
        }

        .account-switcher button,
        .account-switch-restore {
            min-height: 36px;
            border: 1px solid rgba(201, 169, 98, 0.45);
            border-radius: 8px;
            background: rgba(201, 169, 98, 0.1);
            color: var(--accent);
            padding: 0 0.75rem;
            font-size: 0.84rem;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
            white-space: nowrap;
        }

        .account-switcher button:hover,
        .account-switch-restore:hover {
            border-color: var(--accent);
            background: var(--accent);
            color: #0c0f14;
        }

        .account-switch-notice {
            display: flex;
            align-items: center;
            gap: 0.55rem;
            min-width: 0;
            color: #cbd5e1;
            font-size: 0.84rem;
        }

        .account-switch-notice strong {
            color: #f8fafc;
        }

        .account-switch-notice form {
            margin: 0;
        }

        .admin-main {
            padding: 2rem;
            flex: 1;
            min-width: 0;
        }

        .wrap {
            max-width: 100%;
            padding: 0;
        }

        @media (max-width: 768px) {
            .admin-sidebar {
                width: min(var(--sidebar-width), 82vw);
                transform: translateX(-100%);
                z-index: 100;
                box-shadow: 18px 0 45px -28px rgba(0, 0, 0, 0.85);
            }

            body.sidebar-open {
                overflow: hidden;
            }

            body.sidebar-open .admin-sidebar {
                transform: translateX(0);
            }

            body.sidebar-open .mobile-menu-overlay {
                opacity: 1;
                pointer-events: auto;
            }

            .admin-wrapper {
                flex: 1 1 auto;
                width: 100%;
                max-width: 100%;
                margin-left: 0;
            }

            .admin-main {
                padding: 1rem;
            }

            .admin-topbar {
                justify-content: space-between;
                gap: 0.75rem;
                padding: 0 1rem;
            }

            .mobile-menu-toggle {
                display: inline-flex;
            }

            .mobile-menu-overlay {
                display: block;
            }

            .admin-topbar-info {
                min-width: 0;
                gap: 0.75rem;
                margin-left: auto;
                font-size: 0.88rem;
            }

            .admin-topbar-info span {
                min-width: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .account-switcher {
                display: none;
            }

            .account-switch-notice {
                gap: 0.4rem;
            }
        }

        @media (max-width: 480px) {
            .admin-topbar-info {
                gap: 0.55rem;
                font-size: 0.82rem;
            }

            .admin-account-summary {
                display: none;
            }

            .admin-account-trigger {
                gap: 0.45rem;
            }

            .admin-topbar-info>a:not(.btn-logout) {
                display: none;
            }

            .btn-logout {
                padding: 0.38rem 0.65rem;
            }
        }
    </style>
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

            <div style="flex: 1;"></div>

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
