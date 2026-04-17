@extends('layouts.admin')
@section('title', 'Đăng nhập 2 lớp (2FA)')

@section('content')
<div class="wrap">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Cài đặt Xác thực 2 lớp (2FA)</h2>
        </div>
        <div class="panel-body" style="padding: 2rem;">
            @if(session('success'))
                <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: bold;">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <p style="color: var(--muted); margin-bottom: 2rem;">
                Xác thực 2 lớp giúp tăng cường bảo mật cho tài khoản của bạn bằng cách yêu cầu mã OTP được gửi qua email mỗi khi bạn đăng nhập.
            </p>

            <div style="display: flex; align-items: center; gap: 1.5rem; padding: 1.5rem; background: rgba(255, 255, 255, 0.02); border-radius: 12px; border: 1px solid var(--border);">
                <div style="font-size: 2rem;">{{ $isEnabled ? '🛡️' : '🔓' }}</div>
                <div style="flex: 1;">
                    <div style="font-weight: bold; font-size: 1.1rem; color: var(--text);">
                        Trạng thái: {!! $isEnabled ? '<span style="color: #4ade80;">ĐANG BẬT</span>' : '<span style="color: #f87171;">ĐANG TẮT</span>' !!}
                    </div>
                    <div style="font-size: 0.9rem; color: var(--muted);">
                        {{ $isEnabled ? 'Tài khoản của bạn đang được bảo vệ bởi lớp bảo mật thứ hai.' : 'Hãy bật 2FA để bảo vệ tài khoản của bạn tốt hơn.' }}
                    </div>
                </div>
                <form action="{{ route('admin.system.2fa.toggle') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: {{ $isEnabled ? '#f87171' : 'var(--accent)' }}; color: #000000; border: none; padding: 0.8rem 1.5rem; border-radius: 8px; font-weight: bold; cursor: pointer;">
                        {{ $isEnabled ? 'Tắt xác thực' : 'Bật xác thực ngay' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
