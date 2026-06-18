@extends('layouts.site')
@section('title', 'Đặt lại mật khẩu')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/auth-reset-password.css')
    @endif
@endpush


@section('content')
<div class="wrap auth-reset-password-inline-9">
    <h1 class="auth-reset-password-inline-8">Tạo Mật Khẩu Mới</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="auth-reset-password-inline-6">
            <label class="auth-reset-password-inline-3">Email</label>
            <input class="auth-reset-password-inline-7" type="email" name="email" value="{{ $email ?? old('email') }}" required readonly>
            @error('email') <div class="auth-reset-password-inline-5">{{ $message }}</div> @enderror
        </div>

        <div class="auth-reset-password-inline-6">
            <label class="auth-reset-password-inline-3">Mật khẩu mới</label>
            <input class="auth-reset-password-inline-2" type="password" name="password" required>
            @error('password') <div class="auth-reset-password-inline-5">{{ $message }}</div> @enderror
        </div>

        <div class="auth-reset-password-inline-4">
            <label class="auth-reset-password-inline-3">Nhập lại mật khẩu</label>
            <input class="auth-reset-password-inline-2" type="password" name="password_confirmation" required>
        </div>

        <button class="auth-reset-password-inline-1" type="submit">
            Lưu mật khẩu & Đăng nhập
        </button>
    </form>
</div>
@endsection