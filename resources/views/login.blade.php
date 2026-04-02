<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập — {{ config('app.name', 'Laravel') }}</title>

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <style>
        :root { font-family: ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: #f4f4f5; color: #18181b; }
        .card { width: 100%; max-width: 400px; padding: 2rem; background: #fff; border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,.08); }
        h1 { margin: 0 0 1.5rem; font-size: 1.25rem; font-weight: 600; }
        label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.35rem; }
        input { width: 100%; box-sizing: border-box; padding: 0.5rem 0.75rem; margin-bottom: 1rem;
            border: 1px solid #d4d4d8; border-radius: 6px; font-size: 1rem; }
        input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        button { width: 100%; padding: 0.625rem 1rem; background: #18181b; color: #fff; border: none;
            border-radius: 6px; font-size: 1rem; font-weight: 500; cursor: pointer; }
        button:hover { background: #27272a; }
        .error { padding: 0.75rem; margin-bottom: 1rem; background: #fef2f2; color: #b91c1c;
            border-radius: 6px; font-size: 0.875rem; }
        .success { padding: 0.75rem; margin-bottom: 1rem; background: #dcfce7; color: #166534;
            border-radius: 6px; font-size: 0.875rem; }
    </style>
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

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">

            <label for="password">Mật khẩu</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">

            <button type="submit">Đăng nhập</button>
        </form>
    </div>
</body>
</html>
