@extends('layouts.site')
@section('title', 'Quên mật khẩu')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/auth-forgot-password.css')
    @endif
@endpush


@section('content')
<div class="wrap auth-forgot-password-inline-11">
    <h1 class="auth-forgot-password-inline-10">Quên Mật Khẩu?</h1>
    <p class="auth-forgot-password-inline-9">
        Đừng lo lắng! Hãy nhập địa chỉ email bạn đã đăng ký, chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.
    </p>

    @if(session('status'))
        <div class="auth-forgot-password-inline-8">
            ✅ {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="auth-forgot-password-inline-7">
            <label class="auth-forgot-password-inline-6">Địa chỉ Email</label>
            <input class="auth-forgot-password-inline-5" type="email" name="email" required placeholder="Ví dụ: name@example.com">
            @error('email') <div class="auth-forgot-password-inline-4">{{ $message }}</div> @enderror
        </div>

        <button class="auth-forgot-password-inline-3" type="submit">
            Gửi liên kết khôi phục
        </button>
        
        <div class="auth-forgot-password-inline-2">
            <a class="auth-forgot-password-inline-1" href="{{ route('login') }}">← Quay lại trang Đăng nhập</a>
        </div>
    </form>
</div>
@endsection