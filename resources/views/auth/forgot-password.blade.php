@extends('layouts.site')
@section('title', 'Quên mật khẩu')

@section('content')
<div class="wrap" style="max-width: 450px; margin: 4rem auto; background: var(--surface); padding: 2.5rem; border-radius: 12px; border: 1px solid var(--border);">
    <h1 style="margin-top: 0; color: var(--accent); text-align: center;">Quên Mật Khẩu?</h1>
    <p style="color: var(--muted); text-align: center; margin-bottom: 2rem; font-size: 0.95rem;">
        Đừng lo lắng! Hãy nhập địa chỉ email bạn đã đăng ký, chúng tôi sẽ gửi cho bạn một liên kết để đặt lại mật khẩu.
    </p>

    @if(session('status'))
        <div style="background: rgba(52, 211, 153, 0.1); color: #34d399; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #34d399; text-align: center;">
            ✅ {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; font-weight: 600;">Địa chỉ Email</label>
            <input type="email" name="email" required placeholder="Ví dụ: name@example.com" style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
            @error('email') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
        </div>

        <button type="submit" style="width: 100%; background: var(--accent); color: #000; padding: 0.8rem; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">
            Gửi liên kết khôi phục
        </button>
        
        <div style="text-align: center; margin-top: 1.5rem;">
            <a href="{{ route('login') }}" style="color: var(--muted); font-size: 0.9rem;">← Quay lại trang Đăng nhập</a>
        </div>
    </form>
</div>
@endsection