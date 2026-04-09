<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin Dashboard') — Lux Auto</title>
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
        }
        a { text-decoration: none; transition: 0.2s; }

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
        }
        .sidebar-brand span { color: var(--accent); }
        .sidebar-nav {
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            flex: 1;
        }
        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: var(--muted);
            border-radius: 8px;
            font-weight: 500;
        }
        .sidebar-link:hover {
            background: rgba(255, 255, 255, 0.03);
            color: var(--text);
        }
        /* Highlight menu đang được chọn */
        .sidebar-link.active {
            background: rgba(201, 169, 98, 0.1);
            color: var(--accent);
            border-left: 3px solid var(--accent);
        }

        /* --- KHU VỰC NỘI DUNG BÊN PHẢI --- */
        .admin-wrapper {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
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
            border-color: #ef4444;
            color: #ef4444;
        }
        .admin-main {
            padding: 2rem;
            flex: 1;
        }

        /* Ghi đè class wrap cũ của bạn để full màn hình bên Admin */
        .wrap { max-width: 100%; padding: 0; }
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
                📊 Bảng điều khiển
            </a>

            <a href="{{ route('admin.cars.index') }}" class="sidebar-link {{ request()->routeIs('admin.cars.*') ? 'active' : '' }}">
                🚘 Quản lý kho xe
            </a>
            <a href="{{ route('admin.brands.index') }}" class="sidebar-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}">
        🏢 Quản lý hãng xe
    </a>
    <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
        👥 Quản lý người dùng
    </a>
        </nav>
    </aside>

    <div class="admin-wrapper">
        <header class="admin-topbar">
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
