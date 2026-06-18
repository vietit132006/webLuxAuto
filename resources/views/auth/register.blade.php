<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký — {{ config('app.name', 'Laravel') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/auth-register.css')
    @endif
</head>
<body>

<div class="card">
    <h1>Đăng ký</h1>

    {{-- Thông báo thành công --}}
    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    {{-- Hiển thị lỗi --}}
    @if ($errors->any())
        <div class="error">
            <ul class="auth-register-inline-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <label for="name">Tên</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required>

        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" required>

        <label for="phone">SĐT</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" required>

        <label for="password">Mật khẩu</label>
        <input id="password" type="password" name="password" required>

        <label for="password_confirmation">Nhập lại mật khẩu</label>
        <input id="password_confirmation" type="password" name="password_confirmation" required>

        <button type="submit">Đăng ký</button>
    </form>

    <p class="auth-register-inline-2">
        Đã có tài khoản?
        <a class="auth-register-inline-1" href="{{ route('login') }}">Đăng nhập</a>
    </p>
</div>

</body>
</html>