<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Đăng nhập — {{ config('app.name', 'Laravel') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/auth-login.css')
    @endif
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/partial-saved-login-switcher.css')
    @endif
</head>
<body>
    <div class="card">
        <h1>Đăng nhập</h1>

        @if(session('success'))
            <div class="success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">

            <label for="password">Mật khẩu</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">

            <button class="auth-login-inline-5" type="submit">Đăng nhập</button>

            <div class="auth-login-inline-4">
                <a class="auth-login-inline-3" href="{{ route('password.request') }}">Quên mật khẩu?</a>
            </div>
        </form>

        <div class="lsa-login-shortcut" data-saved-login-shortcut hidden>
            <button type="button" class="lsa-inline-switch" data-open-account-switcher>
                Chuyển đổi tài khoản đã lưu
            </button>
        </div>

        <p class="auth-login-inline-2">
            Chưa có tài khoản?
            <a class="auth-login-inline-1" href="{{ route('register') }}">Đăng ký</a>
        </p>
    </div>
    @include('partials.saved-login-switcher')
</body>
</html>