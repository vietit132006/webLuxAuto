@extends('layouts.site')
@section('title', 'Hồ sơ của tôi')

@section('content')
<div class="wrap" style="max-width: 600px; margin: 3rem auto; background: var(--surface); padding: 2.5rem; border-radius: 12px; border: 1px solid var(--border);">
    <h1 style="margin-top: 0; margin-bottom: 0.5rem; color: var(--accent);">Hồ sơ cá nhân</h1>
    <p style="color: var(--muted); margin-bottom: 2rem;">Quản lý thông tin bảo mật và tài khoản của bạn.</p>

    @if(session('success'))
        <div id="success-alert" style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #34d399; font-weight: 600; transition: opacity 0.5s ease;">
            ✅ {{ session('success') }}
        </div>
        <script>setTimeout(() => { document.getElementById('success-alert').style.opacity = '0'; setTimeout(() => document.getElementById('success-alert').remove(), 500); }, 2000);</script>
    @endif
        @if(session('force_logout'))
        <script>
            // 1. Lệnh alert sẽ làm trình duyệt khựng lại và hiện hộp thoại
            alert("{{ session('force_logout') }}");

            // 2. Ngay sau khi người dùng bấm nút "OK", dòng code dưới đây mới chạy
            // Nó sẽ tự động gọi đường dẫn Đăng xuất của bạn để đẩy về trang đăng nhập
            window.location.href = "{{ route('logout') }}";
        </script>
    @endif
    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Địa chỉ Email</label>
            <input type="email" value="{{ $user->email }}" disabled style="width: 100%; padding: 0.8rem; border-radius: 6px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--muted); cursor: not-allowed;">
            <small style="color: var(--muted); display: block; margin-top: 5px;">* Email dùng để đăng nhập không thể thay đổi.</small>
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Họ và Tên</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
        </div>

        <div style="margin-bottom: 2rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Số điện thoại</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
        </div>

        <h3 style="border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1.5rem;">Đổi Mật Khẩu (Tùy chọn)</h3>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Mật khẩu hiện tại</label>
            <input type="password" name="current_password" placeholder="Nhập mật khẩu hiện tại nếu muốn đổi..." style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
            @error('current_password') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 2rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Mật khẩu mới</label>
                <input type="password" name="new_password" style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
                @error('new_password') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Nhập lại mật khẩu mới</label>
                <input type="password" name="new_password_confirmation" style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
            </div>
        </div>

        <button type="submit" style="background: var(--accent); color: #000; padding: 0.8rem 2rem; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; width: 100%; font-size: 1rem;">Cập nhật thông tin</button>
    </form>
</div>
@endsection
