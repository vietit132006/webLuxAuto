<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Lux Auto') — {{ config('app.name', 'Lux Auto') }}</title>
    @stack('meta')

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/layout-site.css')
    @endif
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/partial-saved-login-switcher.css')
    @endif
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/partial-car-card.css')
    @endif
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
                <div class="nav-dropdown" data-account-menu>
                    <button type="button" class="nav-dropdown-toggle" data-account-menu-trigger
                        aria-haspopup="true" aria-expanded="false">
                        <span class="nav-account-avatar">
                            {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </span>
                        <span class="nav-account-name">{{ auth()->user()->name }}</span>
                        <svg class="nav-account-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                            stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m6 9 6 6 6-6" />
                        </svg>
                    </button>
                    <div class="nav-dropdown-menu">
                        <div class="nav-dropdown-profile">
                            <span class="nav-account-avatar">
                                {{ \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr(auth()->user()->name ?? 'A', 0, 1)) }}
                            </span>
                            <div>
                                <strong>{{ auth()->user()->name }}</strong>
                                <span>{{ auth()->user()->email }}</span>
                            </div>
                        </div>

                        @if($isAccountSwitching && $accountSwitcher)
                            <form method="POST" action="{{ route('account-switch.restore') }}">
                                @csrf
                                <button type="submit" class="account-switch-restore">
                                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0 4-4m-4 4h14m-5 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h7a3 3 0 0 1 3 3v1"/></svg>
                                    Quay lại {{ $accountSwitcher->name }}
                                </button>
                            </form>
                            <div class="dropdown-divider"></div>
                        @endif

                        <a href="{{ route('profile.index') }}">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 7.5a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                            </svg>
                            Thông tin tài khoản
                        </a>
                        <button type="button" data-open-account-switcher>
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M7.5 21 3 16.5m0 0L7.5 12M3 16.5h15M16.5 3 21 7.5m0 0L16.5 12M21 7.5H6" />
                            </svg>
                            Chuyển đổi tài khoản
                        </button>
                        <div class="dropdown-divider"></div>
                        <a href="{{ route('logout') }}" class="text-danger">
                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3-6 3 3m0 0-3 3m3-3H9" />
                            </svg>
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

@include('partials.saved-login-switcher')

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

        menu.querySelectorAll('a, [data-open-account-switcher]').forEach((link) => {
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
