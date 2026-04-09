@extends('layouts.site')
@section('title', 'Đặt lại mật khẩu')

@section('content')
<div class="wrap" style="max-width: 450px; margin: 4rem auto; background: var(--surface); padding: 2.5rem; border-radius: 12px; border: 1px solid var(--border);">
    <h1 style="margin-top: 0; color: var(--accent); text-align: center;">Tạo Mật Khẩu Mới</h1>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Email</label>
            <input type="email" name="email" value="{{ $email ?? old('email') }}" required readonly style="width: 100%; padding: 0.8rem; border-radius: 6px; background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--muted);">
            @error('email') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Mật khẩu mới</label>
            <input type="password" name="password" required style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
            @error('password') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
        </div>

        <div style="margin-bottom: 2rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Nhập lại mật khẩu</label>
            <input type="password" name="password_confirmation" required style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
        </div>

        <button type="submit" style="width: 100%; background: var(--accent); color: #000; padding: 0.8rem; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Lưu mật khẩu & Đăng nhập
        </button>
    </form>
</div>
@endsection