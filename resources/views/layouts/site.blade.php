<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Lux Auto') — {{ config('app.name', 'Lux Auto') }}</title>
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
            font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
        }
        a { color: var(--accent); text-decoration: none; }
        a:hover { color: #e4d08a; }
        .wrap { max-width: 1120px; margin: 0 auto; padding: 0 1.25rem; }
        header.site {
            border-bottom: 1px solid var(--border);
            background: rgba(12, 15, 20, 0.92);
            backdrop-filter: blur(8px);
            position: sticky;
            top: 0;
            z-index: 40;
        }
        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 64px;
        }
        .logo {
            font-weight: 700;
            font-size: 1.125rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: var(--text);
        }
        .logo span { color: var(--accent); }
        nav.links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            flex-wrap: wrap;
        }
        nav.links a.nav-link {
            color: var(--muted);
            font-size: 0.9375rem;
            font-weight: 500;
        }
        nav.links a.nav-link:hover { color: var(--text); }
        nav.links a.nav-cta {
            padding: 0.4rem 0.9rem;
            border-radius: 6px;
            border: 1px solid var(--border);
            color: var(--text);
            font-size: 0.875rem;
        }
        nav.links a.nav-cta:hover {
            border-color: var(--accent-dim);
            color: var(--accent);
        }
        main.site-main { padding: 2rem 0 4rem; }
        .grid-cards {
            display: grid;
            gap: 1.25rem;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        }
        .v-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            overflow: hidden;
            transition: border-color 0.2s, transform 0.2s;
        }
        .v-card:hover {
            border-color: var(--accent-dim);
            transform: translateY(-2px);
        }
        .v-card__img-wrap {
            aspect-ratio: 16 / 10;
            background: #0a0d12;
            overflow: hidden;
        }
        .v-card__img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }
        .v-card__body { padding: 1rem 1.1rem 1.15rem; }
        .v-card__title {
            margin: 0 0 0.35rem;
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text);
        }
        .v-card__meta {
            font-size: 0.8125rem;
            color: var(--muted);
            margin: 0 0 0.75rem;
        }
        .v-card__row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem 1rem;
            font-size: 0.8125rem;
            color: var(--muted);
            margin-bottom: 0.75rem;
        }
        .v-card__price {
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--accent);
            margin: 0;
        }
        footer.site {
            border-top: 1px solid var(--border);
            padding: 2rem 0;
            color: var(--muted);
            font-size: 0.875rem;
            text-align: center;
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="site">
        <div class="wrap nav-inner">
            <a href="{{ route('home') }}" class="logo">Lux <span>Auto</span></a>
            <nav class="links">

                @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>

                    <a href="{{ route('admin.cars.index') }}" class="nav-link">Quản lý xe</a>
                @else
                    <a href="{{ route('cars.index') }}" class="nav-link">Danh sách xe</a>
                <a href="{{ route('home') }}" class="nav-link">Trang chủ</a>

                @endif

                @auth
                    <span style="font-size: 0.9375rem; color: var(--muted);">
                        Chào, <strong style="color: var(--text);">{{ auth()->user()->name }}</strong>
                    </span>
                    <a href="{{ route('logout') }}" class="nav-cta">Đăng xuất</a>
                @else
                    <a href="{{ route('login') }}" class="nav-cta">Đăng nhập</a>
                @endauth
            </nav>
        </div>
    </header>

    <main class="site-main">
        @yield('content')
    </main>

    <footer class="site">
        <div class="wrap">
            © {{ date('Y') }} Lux Auto. Giá và thông tin chỉ mang tính minh họa.
        </div>
    </footer>
    @stack('scripts')
</body>
</html>
