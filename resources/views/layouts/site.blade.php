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
            position: relative; /* Thêm để an toàn cho nút nổi */
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

        /* ... Các CSS Card bạn giữ nguyên ... */
        .grid-cards { display: grid; gap: 1.25rem; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .v-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: border-color 0.2s, transform 0.2s; }
        .v-card:hover { border-color: var(--accent-dim); transform: translateY(-2px); }
        .v-card__img-wrap { aspect-ratio: 16 / 10; background: #0a0d12; overflow: hidden; }
        .v-card__img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .v-card__body { padding: 1rem 1.1rem 1.15rem; }
        .v-card__title { margin: 0 0 0.35rem; font-size: 1.05rem; font-weight: 600; color: var(--text); }
        .v-card__meta { font-size: 0.8125rem; color: var(--muted); margin: 0 0 0.75rem; }
        .v-card__row { display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; font-size: 0.8125rem; color: var(--muted); margin-bottom: 0.75rem; }
        .v-card__price { font-size: 1.05rem; font-weight: 600; color: var(--accent); margin: 0; }

        footer.site {
            border-top: 1px solid var(--border);
            padding: 2rem 0;
            color: var(--muted);
            font-size: 0.875rem;
            text-align: center;
        }

        /* THÊM CSS CHO NÚT LIÊN HỆ NỔI */
        .floating-contact {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column;
            gap: 15px;
            z-index: 9999;
        }
        .f-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            color: white;
            font-size: 26px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
            transition: transform 0.3s ease;
        }
        .f-btn:hover { transform: scale(1.1); color: white; }
        .f-zalo { background-color: #0068ff; font-weight: bold; font-size: 16px; font-family: Arial, sans-serif; }
        .f-messenger { background: linear-gradient(45deg, #00B2FF, #006AFF); }
        .f-phone { background-color: #10b981; position: relative; }
        .f-phone::before, .f-phone::after {
            content: ''; position: absolute;
            border: 2px solid #10b981;
            left: -10px; right: -10px; top: -10px; bottom: -10px;
            border-radius: 50%;
            animation: pulse 1.5s linear infinite;
            opacity: 0;
        }
        .f-phone::after { animation-delay: 0.5s; }
        @keyframes pulse {
            0% { transform: scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: scale(1.2); opacity: 0; }
        }
        .floating-contact-wrapper {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column-reverse; /* Xếp từ dưới lên trên */
            align-items: center;
            z-index: 9999;
        }

        /* Khối chứa các nút phụ */
        .contact-menu {
            display: flex;
            flex-direction: column-reverse;
            gap: 15px;
            margin-bottom: 15px;
            /* Ẩn mặc định */
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px) scale(0.8);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55); /* Hiệu ứng nảy nhẹ */
        }

        /* Khi di chuột vào khu vực tổng -> Hiện các nút phụ */
        .floating-contact-wrapper:hover .contact-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) scale(1);
        }

        .f-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            text-decoration: none;
            color: white;
            font-size: 26px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.4);
            transition: transform 0.2s ease, filter 0.2s;
            cursor: pointer;
        }
        .f-btn:hover { filter: brightness(1.1); transform: scale(1.1) !important; color: white; }

        /* Nút Tổng (Gốc) */
        .f-main {
            background-color: var(--accent); /* Màu vàng gold của Lux Auto */
            color: #000;
            position: relative;
            z-index: 2;
        }

        /* Hiệu ứng nhịp tim cho nút Tổng */
        .f-main::before, .f-main::after {
            content: ''; position: absolute;
            border: 2px solid var(--accent);
            left: -10px; right: -10px; top: -10px; bottom: -10px;
            border-radius: 50%;
            animation: pulse 1.5s linear infinite;
            opacity: 0;
        }
        .f-main::after { animation-delay: 0.5s; }
        @keyframes pulse {
            0% { transform: scale(0.5); opacity: 0; }
            50% { opacity: 1; }
            100% { transform: scale(1.2); opacity: 0; }
        }

        /* Màu các nút phụ */
        .f-zalo { background-color: #0068ff; font-weight: bold; font-size: 15px; font-family: Arial, sans-serif; }
        .f-messenger { background: linear-gradient(45deg, #00B2FF, #006AFF); }
        .f-telegram { background-color: #2AABEE; }
        .f-phone { background-color: #10b981; }
    </style>
    @stack('styles')
</head>
<body>
<header class="site">
    <div class="wrap nav-inner">
        <a href="{{ route('home') }}" class="logo">Lux <span>Auto</span></a>
        <nav class="links">

            {{-- Menu cho Admin/Staff --}}
            @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                <a href="{{ route('admin.dashboard') }}" class="nav-link">Dashboard</a>
                <a href="{{ route('admin.cars.index') }}" class="nav-link">Quản lý xe</a>
                <a href="{{ route('compare.index') }}" class="nav-link">So sánh xe</a>
                <a href="{{ route('promotions.index') }}" class="nav-link">Khuyến mãi</a>

            {{-- Menu cho Khách hàng --}}
            @else
                <a href="{{ route('home') }}" class="nav-link">Trang chủ</a>
                <a href="{{ route('cars.index') }}" class="nav-link">Danh sách xe</a>
                <a href="{{ route('compare.index') }}" class="nav-link">So sánh xe</a>
                <a href="{{ route('promotions.index') }}" class="nav-link">Khuyến mãi</a>
                <a href="{{ route('news.index') }}" class="nav-link">Tin tức</a>
            @endif

            <a href="{{ route('livestream') }}" class="nav-link" style="color: #ef4444; font-weight: 900; text-transform: uppercase;">🔴 Live</a>

            {{-- Menu Tài khoản / Đăng nhập --}}
            @auth
                <a href="{{ route('ticket.history') }}" class="nav-link" style="color: var(--accent); font-weight: bold;">Hỗ trợ 🎧</a>

                <a href="{{ route('order.history') }}" class="nav-link" style="color: var(--accent);">Đơn hàng</a>
                <a href="{{ route('profile.index') }}" class="nav-link" style="color: var(--accent);">Hồ sơ</a>
                <span style="font-size: 0.9375rem; color: var(--muted);">
                    Chào, <strong style="color: var(--text);">{{ auth()->user()->name }}</strong>
                </span>
                <a href="{{ route('logout') }}" class="nav-cta">Đăng xuất</a>
            @else
                <a href="{{ route('register') }}" class="nav-link">Đăng ký</a>
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
            © {{ date('Y') }} Lux Auto.
        </div>
    </footer>

<div class="floating-contact-wrapper">
        <div class="f-btn f-main" title="Liên hệ Lux Auto">
            💬
        </div>

        <div class="contact-menu">

            <a href="https://t.me/@Viet5553" target="_blank" class="f-btn f-telegram" title="Chat Telegram">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM16.64 8.8C16.49 10.38 15.82 14.34 15.48 16.17C15.33 16.94 15.05 17.2 14.78 17.22C14.18 17.28 13.73 16.83 13.16 16.45C12.26 15.86 11.75 15.49 10.88 14.92C9.88 14.26 10.53 13.9 11.11 13.3C11.26 13.15 13.88 10.77 13.93 10.56C13.94 10.53 13.94 10.45 13.89 10.41C13.84 10.37 13.78 10.39 13.72 10.4C13.64 10.42 12.33 11.29 9.78 13.01C9.4 13.27 9.06 13.4 8.76 13.39C8.42 13.38 7.78 13.2 7.3 13.04C6.71 12.85 6.25 12.75 6.29 12.42C6.31 12.25 6.55 12.08 7.02 11.88C9.87 10.64 11.77 9.85 12.72 9.45C15.43 8.32 16.01 8.12 16.39 8.11C16.47 8.11 16.66 8.13 16.78 8.24C16.88 8.33 16.91 8.46 16.92 8.54C16.9 8.59 16.66 8.68 16.64 8.8Z"/>
                </svg>
            </a>

            <a href="https://zalo.me/0343011584" target="_blank" class="f-btn f-zalo" title="Chat Zalo">
                Zalo
            </a>

            <a href="https://www.facebook.com/nguyen.duc.viet.913614?locale=vi_VN" target="_blank" class="f-btn f-messenger" title="Chat Messenger">
                <svg width="28" height="28" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.145 2 11.259c0 2.923 1.536 5.516 3.937 7.185v3.42l3.585-1.968a10.666 10.666 0 0 0 2.478.293c5.523 0 10-4.145 10-9.259S17.523 2 12 2zm1.094 12.392-2.825-3.023-5.545 3.023 6.082-6.46 2.894 3.023 5.476-3.023-6.082 6.46z"/></svg>
            </a>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
