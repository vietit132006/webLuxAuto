@extends('layouts.admin')
@section('title', 'Đăng nhập 2 lớp (2FA)')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-system-2fa.css')
    @endif
@endpush


@section('content')
<div class="wrap">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Cài đặt Xác thực 2 lớp (2FA)</h2>
        </div>
        <div class="panel-body admin-system-2fa-inline-10">
            @if(session('success'))
                <div class="admin-system-2fa-inline-9">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <p class="admin-system-2fa-inline-8">
                Xác thực 2 lớp giúp tăng cường bảo mật cho tài khoản của bạn bằng cách yêu cầu mã OTP được gửi qua email mỗi khi bạn đăng nhập.
            </p>

            <div class="admin-system-2fa-inline-7">
                <div class="admin-system-2fa-inline-6">{{ $isEnabled ? '🛡️' : '🔓' }}</div>
                <div class="admin-system-2fa-inline-5">
                    <div class="admin-system-2fa-inline-4">
                        Trạng thái: {!! $isEnabled ? '<span class="admin-system-2fa-inline-3">ĐANG BẬT</span>' : '<span class="admin-system-2fa-inline-2">ĐANG TẮT</span>' !!}
                    </div>
                    <div class="admin-system-2fa-inline-1">
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