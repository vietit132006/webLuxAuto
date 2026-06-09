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
            position: relative;
        }

        a { color: var(--accent); text-decoration: none; transition: 0.2s; }
        a:hover { color: #e4d08a; }

        .wrap { max-width: 1120px; margin: 0 auto; padding: 0 1.25rem; }

        /* --- HEADER BÓNG BẨY --- */
        header.site {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(12, 15, 20, 0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }

        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            min-height: 70px;
        }

        .logo {
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: 0.08em;
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

        .site-menu-title {
            display: none;
        }

        .site-menu-toggle {
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
            transition: color 0.2s ease, background 0.2s ease, border-color 0.2s ease;
        }

        .site-menu-toggle:hover {
            border-color: rgba(201, 169, 98, 0.45);
            color: var(--accent);
            background: rgba(201, 169, 98, 0.08);
        }

        .site-menu-toggle svg {
            width: 22px;
            height: 22px;
        }

        .site-menu-overlay {
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
            z-index: 80;
        }

        /* Hiệu ứng gạch chân sang trọng cho Menu */
        nav.links a.nav-link {
            color: var(--text);
            font-size: 0.9375rem;
            font-weight: 500;
            position: relative;
            padding: 0.5rem 0;
            opacity: 0.8;
        }
        nav.links a.nav-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: 0;
            left: 0;
            background-color: var(--accent);
            transition: width 0.3s ease;
        }
        nav.links a.nav-link:hover { opacity: 1; }
        nav.links a.nav-link:hover::after { width: 100%; }

        nav.links a.nav-cta {
            padding: 0.5rem 1.2rem;
            border-radius: 6px;
            border: 1px solid var(--accent-dim);
            color: var(--accent);
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        nav.links a.nav-cta:hover {
            background: var(--accent);
            color: #000;
            box-shadow: 0 4px 15px rgba(201, 169, 98, 0.3);
        }

        /* --- HIỆU ỨNG NÚT LIVE --- */
        .nav-live {
            display: flex !important;
            align-items: center;
            gap: 6px;
            color: #ef4444 !important;
            font-weight: 800 !important;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            opacity: 1 !important;
        }
        .nav-live::after { display: none !important; } /* Tắt gạch chân cho nút Live */
        .live-dot {
            width: 8px;
            height: 8px;
            background-color: #ef4444;
            border-radius: 50%;
            box-shadow: 0 0 8px #ef4444;
            animation: pulse-live 1.5s infinite;
        }
        @keyframes pulse-live {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
        }

        /* --- MENU DROPDOWN TÀI KHOẢN --- */
        .nav-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            height: 100%;
        }
        .nav-dropdown-toggle {
            cursor: pointer;
            font-size: 0.9375rem;
            color: var(--muted);
            padding: 0.5rem 0;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: color 0.2s;
        }
        .nav-dropdown-toggle:hover { color: var(--text); }

        .nav-dropdown-menu {
            position: absolute;
            right: 0;
            top: 130%;
            background: rgba(20, 26, 34, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.05);
            border-radius: 10px;
            min-width: 180px;
            padding: 0.5rem 0;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.5);
            opacity: 0;
            visibility: hidden;
            transform: translateY(15px);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: 50;
        }
        .nav-dropdown:hover .nav-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        .nav-dropdown-menu a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.6rem 1.2rem;
            color: var(--text);
            font-size: 0.9rem;
            font-weight: 500;
        }
        .nav-dropdown-menu a svg { color: var(--accent); width: 18px; height: 18px; }
        .nav-dropdown-menu a.text-danger svg { color: #ef4444; }
        .nav-dropdown-menu a:hover {
            background: rgba(255,255,255,0.03);
            color: var(--accent);
            padding-left: 1.5rem; /* Hiệu ứng thụt lùi khi trỏ chuột */
        }
        .nav-dropdown-menu a.text-danger:hover { color: #ef4444; }

        .dropdown-divider {
            height: 1px;
            background: rgba(255,255,255,0.05);
            margin: 0.5rem 0;
        }

        main.site-main { padding: 2rem 0 4rem; }

        /* --- CARDS --- */
        .grid-cards { display: grid; gap: 1.25rem; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .v-card { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: border-color 0.2s, transform 0.2s; }
        .v-card:hover { border-color: var(--accent-dim); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
        .v-card__img-wrap { aspect-ratio: 16 / 10; background: #0a0d12; overflow: hidden; }
        .v-card__img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .v-card__body { padding: 1rem 1.1rem 1.15rem; }
        .v-card__title { margin: 0 0 0.35rem; font-size: 1.05rem; font-weight: 600; color: var(--text); }
        .v-card__meta { font-size: 0.8125rem; color: var(--muted); margin: 0 0 0.75rem; }
        .v-card__row { display: flex; flex-wrap: wrap; gap: 0.5rem 1rem; font-size: 0.8125rem; color: var(--muted); margin-bottom: 0.75rem; }
        .v-card__price { font-size: 1.05rem; font-weight: 600; color: var(--accent); margin: 0; }

        /* --- FOOTER --- */
        footer.site {
            border-top: 1px solid var(--border);
            padding: 2rem 0;
            color: var(--muted);
            font-size: 0.875rem;
            text-align: center;
        }

        /* --- FLOATING CONTACT BUTTONS --- */
        .floating-contact-wrapper {
            position: fixed;
            bottom: 30px;
            right: 30px;
            display: flex;
            flex-direction: column-reverse;
            align-items: center;
            z-index: 9999;
        }
        .contact-menu {
            display: flex;
            flex-direction: column-reverse;
            gap: 15px;
            margin-bottom: 15px;
            opacity: 0;
            visibility: hidden;
            transform: translateY(20px) scale(0.8);
            transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.5);
            transition: transform 0.2s ease, filter 0.2s;
            cursor: pointer;
        }
        .f-btn:hover { filter: brightness(1.1); transform: scale(1.1) !important; color: white; }

        .f-main {
            background: linear-gradient(135deg, var(--accent), #e4d08a);
            color: #000;
            position: relative;
            z-index: 2;
            box-shadow: 0 4px 15px rgba(201, 169, 98, 0.4);
        }
        .f-main svg { width: 28px; height: 28px; }
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

        .f-zalo { background-color: #0068ff; font-weight: bold; font-size: 15px; font-family: Arial, sans-serif; }
        .f-messenger { background: linear-gradient(45deg, #00B2FF, #006AFF); }
        .f-telegram { background-color: #2AABEE; }

        @media (max-width: 768px) {
            header.site {
                z-index: 120;
            }

            body.site-menu-open {
                overflow: hidden;
            }

            .nav-inner {
                min-height: 64px;
            }

            .site-menu-toggle {
                display: inline-flex;
                margin-left: auto;
            }

            .site-menu-overlay {
                display: block;
            }

            body.site-menu-open .site-menu-overlay {
                opacity: 1;
                pointer-events: auto;
            }

            body.site-menu-open .floating-contact-wrapper {
                display: none;
            }

            nav.links {
                position: fixed;
                top: 0;
                left: 0;
                z-index: 130;
                width: min(320px, 84vw);
                height: 100vh;
                padding: 1rem;
                display: flex;
                flex-direction: column;
                align-items: stretch;
                gap: 0.35rem;
                flex-wrap: nowrap;
                overflow-y: auto;
                background: linear-gradient(180deg, #141a22, #0d1118);
                border-right: 1px solid var(--border);
                box-shadow: 18px 0 45px -28px rgba(0, 0, 0, 0.85);
                transform: translateX(-100%);
                transition: transform 0.28s ease;
            }

            body.site-menu-open nav.links {
                transform: translateX(0);
            }

            .site-menu-title {
                display: block;
                padding: 0.85rem 0.85rem 1.1rem;
                margin-bottom: 0.35rem;
                border-bottom: 1px solid rgba(255, 255, 255, 0.08);
                color: var(--text);
                font-size: 1.08rem;
                font-weight: 850;
                letter-spacing: 0.08em;
                text-transform: uppercase;
            }

            .site-menu-title span {
                color: var(--accent);
            }

            nav.links a.nav-link,
            nav.links a.nav-cta {
                width: 100%;
                min-height: 44px;
                display: flex;
                align-items: center;
                padding: 0.72rem 0.85rem;
                border-radius: 8px;
                color: var(--text);
                opacity: 1;
            }

            nav.links a.nav-link::after {
                display: none;
            }

            nav.links a.nav-link:hover,
            nav.links a.nav-cta:hover {
                background: rgba(201, 169, 98, 0.1);
                color: var(--accent);
                padding-left: 0.85rem;
            }

            nav.links a.nav-cta {
                justify-content: center;
                margin-top: 0.35rem;
                border-color: rgba(201, 169, 98, 0.45);
                color: var(--accent);
            }

            .nav-live {
                justify-content: flex-start;
            }

            .nav-dropdown {
                width: 100%;
                height: auto;
                display: block;
                padding: 0.7rem 0.85rem;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
                margin-top: 0.4rem;
            }

            .nav-dropdown-toggle {
                padding: 0 0 0.65rem;
                color: var(--muted);
            }

            .nav-dropdown-toggle svg {
                display: none;
            }

            .nav-dropdown-menu {
                position: static;
                min-width: 0;
                opacity: 1;
                visibility: visible;
                transform: none;
                padding: 0;
                border: 0;
                border-radius: 0;
                background: transparent;
                box-shadow: none;
            }

            .nav-dropdown-menu a {
                min-height: 40px;
                padding: 0.58rem 0;
                border-radius: 8px;
            }

            .nav-dropdown-menu a:hover {
                padding-left: 0.5rem;
            }

            .dropdown-divider {
                margin: 0.45rem 0;
            }
        }

        @media (max-width: 420px) {
            .wrap {
                padding: 0 1rem;
            }

            .logo {
                font-size: 1.08rem;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

<header class="site">
    <div class="wrap nav-inner">
        <a href="{{ route('home') }}" class="logo">Lux <span>Auto</span></a>

        <button type="button" class="site-menu-toggle" id="site-menu-toggle" aria-label="Mở menu"
            aria-controls="site-menu" aria-expanded="false">
            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16" />
            </svg>
        </button>

        <nav class="links" id="site-menu">
            <div class="site-menu-title">Lux <span>Auto</span></div>
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

            <a href="{{ route('livestream') }}" class="nav-link nav-live">
                <span class="live-dot"></span> LIVE
            </a>

            {{-- Menu Tài khoản / Đăng nhập --}}
            @auth
                <div class="nav-dropdown">
                    <span class="nav-dropdown-toggle">
                        <strong style="color: var(--text);">{{ auth()->user()->name }}</strong>
                        <svg style="width: 14px; height: 14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </span>
                    <div class="nav-dropdown-menu">
                        <a href="{{ route('profile.index') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Hồ sơ của tôi
                        </a>
                        <a href="{{ route('order.history') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            Đơn hàng
                        </a>
                        <a href="{{ route('ticket.history') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                            Hỗ trợ
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('logout') }}" class="text-danger">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            Đăng xuất
                        </a>
                    </div>
                </div>
            @else
                <a href="{{ route('register') }}" class="nav-link">Đăng ký</a>
                <a href="{{ route('login') }}" class="nav-cta">Đăng nhập</a>
            @endauth
        </nav>
    </div>
</header>
<button type="button" class="site-menu-overlay" id="site-menu-overlay" aria-label="Đóng menu"></button>

<main class="site-main">
    @yield('content')
</main>

<footer class="site">
    <div class="wrap">
        © {{ date('Y') }} Lux Auto. Đẳng cấp xe sang.
    </div>
</footer>

<div class="floating-contact-wrapper">
    <div class="f-btn f-main" title="Liên hệ Lux Auto">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
    </div>

    <div class="contact-menu">
        <a href="https://t.me/@Viet5553" target="_blank" class="f-btn f-telegram" title="Chat Telegram">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM16.64 8.8C16.49 10.38 15.82 14.34 15.48 16.17C15.33 16.94 15.05 17.2 14.78 17.22C14.18 17.28 13.73 16.83 13.16 16.45C12.26 15.86 11.75 15.49 10.88 14.92C9.88 14.26 10.53 13.9 11.11 13.3C11.26 13.15 13.88 10.77 13.93 10.56C13.94 10.53 13.94 10.45 13.89 10.41C13.84 10.37 13.78 10.39 13.72 10.4C13.64 10.42 12.33 11.29 9.78 13.01C9.4 13.27 9.06 13.4 8.76 13.39C8.42 13.38 7.78 13.2 7.3 13.04C6.71 12.85 6.25 12.75 6.29 12.42C6.31 12.25 6.55 12.08 7.02 11.88C9.87 10.64 11.77 9.85 12.72 9.45C15.43 8.32 16.01 8.12 16.39 8.11C16.47 8.11 16.66 8.13 16.78 8.24C16.88 8.33 16.91 8.46 16.92 8.54C16.9 8.59 16.66 8.68 16.64 8.8Z"/>
            </svg>
        </a>

        <a href="https://zalo.me/0343011584" target="_blank" class="f-btn f-zalo" title="Chat Zalo">
            Zalo
        </a>

        <a href="https://www.facebook.com/nguyen.duc.viet.913614?locale=vi_VN" target="_blank" class="f-btn f-messenger" title="Chat Messenger">
            <svg width="26" height="26" fill="white" viewBox="0 0 24 24"><path d="M12 2C6.477 2 2 6.145 2 11.259c0 2.923 1.536 5.516 3.937 7.185v3.42l3.585-1.968a10.666 10.666 0 0 0 2.478.293c5.523 0 10-4.145 10-9.259S17.523 2 12 2zm1.094 12.392-2.825-3.023-5.545 3.023 6.082-6.46 2.894 3.023 5.476-3.023-6.082 6.46z"/></svg>
        </a>
    </div>
</div>

<script>
    (() => {
        const body = document.body;
        const toggleButton = document.getElementById('site-menu-toggle');
        const overlay = document.getElementById('site-menu-overlay');
        const menu = document.getElementById('site-menu');

        if (!toggleButton || !overlay || !menu) {
            return;
        }

        const setMenuOpen = (isOpen) => {
            body.classList.toggle('site-menu-open', isOpen);
            toggleButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        };

        toggleButton.addEventListener('click', () => {
            setMenuOpen(!body.classList.contains('site-menu-open'));
        });

        overlay.addEventListener('click', () => setMenuOpen(false));

        menu.querySelectorAll('a').forEach((link) => {
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
