<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            display: flex;
            overflow-x: hidden;
        }

        a { text-decoration: none; transition: all 0.2s ease-in-out; }

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
        .sidebar-brand span { color: var(--accent); }

        .sidebar-nav {
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
            flex: 1;
            overflow-y: auto;
        }

        /* Custom thanh cuộn cho Sidebar */
        .sidebar-nav::-webkit-scrollbar { width: 5px; }
        .sidebar-nav::-webkit-scrollbar-track { background: transparent; }
        .sidebar-nav::-webkit-scrollbar-thumb { background: var(--border); border-radius: 10px; }
        .sidebar-nav::-webkit-scrollbar-thumb:hover { background: var(--muted); }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--muted);
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            border-left: 3px solid transparent;
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
            filter: drop-shadow(0 0 5px rgba(239,68,68,0.5));
        }

        /* --- KHU VỰC NỘI DUNG BÊN PHẢI --- */
        .admin-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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

        .admin-topbar-info {
            display: flex;
            align-items: center;
            gap: 1.5rem;
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

        .admin-main {
            padding: 2rem;
            flex: 1;
        }

        .wrap { max-width: 100%; padding: 0; }

        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-wrapper { margin-left: 0; }
            .admin-main { padding: 1rem; }
            .admin-topbar { padding: 0 1rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

    <aside class="admin-sidebar">
        <a href="{{ route('home') }}" class="sidebar-brand" target="_blank" title="Mở trang khách hàng">
            Lux <span>Auto</span>
        </a>
        <nav class="sidebar-nav">
            <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                </svg>
                Bảng điều khiển
            </a>

            <a href="{{ route('admin.cars.index') }}" class="sidebar-link {{ request()->routeIs('admin.cars.*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12" />
                </svg>
                Quản lý kho xe
            </a>

            <a href="{{ route('admin.live.index') }}" class="sidebar-link sidebar-link-live {{ request()->routeIs('admin.live.*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
                </svg>
                Quản lý Livestream
            </a>

            <a href="{{ route('admin.brands.index') }}" class="sidebar-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008ZM17.25 15h.008v.008h-.008V15Z" />
                </svg>
                Quản lý hãng xe
            </a>

            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Quản lý người dùng
            </a>

            <a href="{{ route('admin.news.index') }}" class="sidebar-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 0 1-2.25 2.25M16.5 7.5V18a2.25 2.25 0 0 0 2.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 0 0 2.25 2.25h13.5M6 7.5h3v3H6v-3Z" />
                </svg>
                Quản lý tin tức
            </a>

            <a href="{{ route('admin.orders.index') }}" class="sidebar-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                </svg>
                Quản lý Đơn hàng
            </a>

            <a href="{{ route('admin.tickets.index') }}" class="sidebar-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 20.25c4.97 0 9-3.694 9-8.25s-4.03-8.25-9-8.25S3 7.444 3 12c0 2.104.859 4.023 2.273 5.48.432.447.74 1.04.586 1.641a4.483 4.483 0 0 1-.923 1.785A5.969 5.969 0 0 0 6 21c1.282 0 2.47-.402 3.445-1.087.81.22 1.668.337 2.555.337Z" />
                </svg>
                Quản lý Hỗ trợ
            </a>

            <a href="{{ route('admin.test_drives.index') }}" class="sidebar-link {{ request()->routeIs('admin.test_drives.*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
                Đặt lịch lái thử
            </a>

            <div style="margin: 1.5rem 0 0.5rem; padding: 0 1rem; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: var(--border);">Báo cáo & Phân tích</div>

            <a href="{{ route('admin.reports.sales') }}" class="sidebar-link {{ request()->routeIs('admin.reports.sales') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                </svg>
                Doanh số
            </a>

            <a href="{{ route('admin.reports.inventory') }}" class="sidebar-link {{ request()->routeIs('admin.reports.inventory') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                </svg>
                Tồn kho
            </a>

            <a href="{{ route('admin.reports.inventory_check') }}" class="sidebar-link {{ request()->routeIs('admin.reports.inventory_check') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.35 3.836c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m8.9-4.414c.376.023.75.05 1.124.08 1.131.094 1.976 1.057 1.976 2.192V16.5A2.25 2.25 0 0 1 18 18.75h-2.25m-7.5-10.5H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V18.75m-7.5-10.5h6.375c.621 0 1.125.504 1.125 1.125v9.375m-8.25-3 1.5 1.5 3-3.75" />
                </svg>
                Kiểm tra tồn
            </a>

            <a href="{{ route('admin.reports.customers') }}" class="sidebar-link {{ request()->routeIs('admin.reports.customers') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
                Khách hàng
            </a>

            <a href="{{ route('admin.reports.reviews') }}" class="sidebar-link {{ request()->routeIs('admin.reports.reviews') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z" />
                </svg>
                Đánh giá
            </a>

            <a href="{{ route('admin.promotions') }}" class="sidebar-link {{ request()->routeIs('admin.promotions*') ? 'active' : '' }}">
                <svg style="width: 20px; height: 20px; flex-shrink: 0; margin-right: 10px;" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z" />
                </svg>
                Khuyến mãi
            </a>
        </nav>
    </aside>

    <div class="admin-wrapper">
        <header class="admin-topbar">
            <div style="flex: 1;"></div>

            <div class="admin-topbar-info">
                <span>Xin chào, <strong style="color: var(--accent);">{{ auth()->user()->name ?? 'Admin' }}</strong></span>
                <a href="{{ route('profile.index') }}" style="color: var(--text); font-size: 0.9rem;">Hồ sơ của tôi</a>
                <a href="{{ route('logout') }}" class="btn-logout">Đăng xuất</a>
            </div>
        </header>

        <main class="admin-main">
            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
