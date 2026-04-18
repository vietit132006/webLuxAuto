@extends('layouts.site')
@section('title', 'Hồ sơ của tôi')

@section('content')
<style>
    /* CSS Căn chỉnh cho trang Hồ sơ tràn màn hình */
    .profile-wrap {
        max-width: 98%; /* Tràn ra sát 2 lề */
        margin: 2rem auto;
        background: var(--surface);
        padding: 2.5rem;
        border-radius: 12px;
        border: 1px solid var(--border);
    }

    .profile-title { margin-top: 0; margin-bottom: 0.5rem; color: var(--accent); }
    .profile-desc { color: var(--muted); margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1.5rem; }

    /* Chia lưới 2 cột cho form để không bị kéo giãn quá dài */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1.5rem 2rem;
        margin-bottom: 2rem;
    }

    .form-group { margin-bottom: 0; }
    .form-group.full-width { grid-column: span 2; }

    .form-label { display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600; }
    .form-control { width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff; transition: border-color 0.3s; }
    .form-control:focus { outline: none; border-color: var(--accent); }
    .form-control:disabled { background: rgba(255,255,255,0.05); color: var(--muted); cursor: not-allowed; }

    .section-title { border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; margin-bottom: 1.5rem; grid-column: span 2; margin-top: 1rem; }

    .btn-submit { background: var(--accent); color: #000; padding: 1rem 3rem; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem; transition: 0.3s; }
    .btn-submit:hover { filter: brightness(1.1); box-shadow: 0 4px 12px rgba(201, 169, 98, 0.2); }

    /* Responsive cho điện thoại */
    @media (max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .form-group.full-width, .section-title { grid-column: span 1; }
        .btn-submit { width: 100%; }
    }
</style>

<div class="wrap profile-wrap">
    <h1 class="profile-title">Hồ sơ cá nhân</h1>
    <p class="profile-desc">Quản lý thông tin bảo mật và tài khoản của bạn.</p>

    @if(session('success'))
        <div id="success-alert" style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #34d399; font-weight: 600; transition: opacity 0.5s ease;">
            ✅ {{ session('success') }}
        </div>
        <script>setTimeout(() => { document.getElementById('success-alert').style.opacity = '0'; setTimeout(() => document.getElementById('success-alert').remove(), 500); }, 2000);</script>
    @endif

    @if(session('force_logout'))
        <script>
            alert("{{ session('force_logout') }}");
            window.location.href = "{{ route('logout') }}";
        </script>
    @endif

    <form method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Họ và Tên</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-control">
            </div>

            <div class="form-group">
                <label class="form-label">Số điện thoại</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-control">
            </div>

            <div class="form-group full-width">
                <label class="form-label">Địa chỉ Email</label>
                <input type="email" value="{{ $user->email }}" disabled class="form-control">
                <small style="color: var(--muted); display: block; margin-top: 5px;">* Email dùng để đăng nhập không thể thay đổi.</small>
            </div>

            <h3 class="section-title">Đổi Mật Khẩu (Tùy chọn)</h3>

            <div class="form-group full-width">
                <label class="form-label">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" placeholder="Nhập mật khẩu hiện tại nếu muốn đổi..." class="form-control">
                @error('current_password') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control">
                @error('new_password') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nhập lại mật khẩu mới</label>
                <input type="password" name="new_password_confirmation" class="form-control">
            </div>
        </div>

        <div style="text-align: right;">
            <button type="submit" class="btn-submit">Cập nhật thông tin</button>
        </div>
    </form>
</div>
@endsection
