@extends('layouts.site')
@section('title', 'Hồ sơ của tôi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/profile-index.css')
    @endif
@endpush


@section('content')

<div class="wrap profile-wrap">
    <h1 class="profile-title">Hồ sơ cá nhân</h1>
    <p class="profile-desc">Quản lý thông tin bảo mật và tài khoản của bạn.</p>

    @if(session('success'))
        <div class="profile-index-inline-4" id="success-alert">
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
                <small class="profile-index-inline-3">* Email dùng để đăng nhập không thể thay đổi.</small>
            </div>

            <h3 class="section-title">Đổi Mật Khẩu (Tùy chọn)</h3>

            <div class="form-group full-width">
                <label class="form-label">Mật khẩu hiện tại</label>
                <input type="password" name="current_password" placeholder="Nhập mật khẩu hiện tại nếu muốn đổi..." class="form-control">
                @error('current_password') <div class="profile-index-inline-2">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Mật khẩu mới</label>
                <input type="password" name="new_password" class="form-control">
                @error('new_password') <div class="profile-index-inline-2">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Nhập lại mật khẩu mới</label>
                <input type="password" name="new_password_confirmation" class="form-control">
            </div>
        </div>

        <div class="profile-index-inline-1">
            <button type="submit" class="btn-submit">Cập nhật thông tin</button>
        </div>
    </form>
</div>
@endsection