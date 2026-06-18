@extends('layouts.admin')

@section('title', 'Chi tiết lịch lái thử')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-test-drives-show.css')
    @endif
@endpush


@section('content')
<div class="wrap admin-test-drives-show-inline-27">

    <a class="admin-test-drives-show-inline-26" href="{{ route('admin.test_drives.index') }}" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
        <svg class="admin-test-drives-show-inline-15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        Quay lại danh sách
    </a>

    <div class="admin-test-drives-show-inline-25">
        <h1 class="admin-test-drives-show-inline-24">
            Chi tiết yêu cầu <span class="admin-test-drives-show-inline-19">#{{ $booking->ticket_id }}</span>
        </h1>

        @php
            $badgeStyle = match($booking->status) {
                'pending' => 'background: rgba(234, 179, 8, 0.1); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3);',
                'approved' => 'background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3);',
                'rejected' => 'background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3);',
                'completed' => 'background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.3);',
                default => 'background: rgba(100, 116, 139, 0.1); color: #94a3b8; border: 1px solid rgba(100, 116, 139, 0.3);',
            };
            $statusText = match($booking->status) {
                'pending' => 'Chờ xử lý',
                'approved' => 'Đã duyệt',
                'rejected' => 'Đã huỷ',
                'completed' => 'Hoàn thành',
                default => ucfirst($booking->status),
            };
        @endphp
        <span style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px; border-radius: 30px; font-size: 0.9rem; font-weight: bold; {{ $badgeStyle }}">
            <span class="admin-test-drives-show-inline-23"></span>
            {{ $statusText }}
        </span>
    </div>

@if(session('success'))
        <div class="admin-test-drives-show-inline-22" id="lux-toast-alert">
            <svg class="admin-test-drives-show-inline-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="admin-test-drives-show-inline-21">
            <svg class="admin-test-drives-show-inline-20" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="lux-grid">

        <div class="lux-card">
            <h2 class="admin-test-drives-show-inline-12">
                Thông tin yêu cầu
            </h2>

            <div class="info-group">
                <div class="info-label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                    Khách hàng
                </div>
                <div class="info-value">{{ $booking->user->name ?? 'Khách vãng lai (ID: ' . $booking->user_id . ')' }}</div>
                <div class="info-sub">{{ $booking->user->email ?? 'Không có email' }}</div>
            </div>

            <div class="info-group">
                <div class="info-label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" /></svg>
                    Dòng xe lái thử
                </div>
                <div class="info-value admin-test-drives-show-inline-19">
                    {{ $booking->car ? (($booking->car->brand->name ?? '') . ' ' . $booking->car->name) : 'Không xác định' }}
                </div>
                @if($booking->car)
                    <div class="info-sub">Đời xe: {{ $booking->car->year ?? '—' }} • Tồn kho: {{ $booking->car->stock ?? 0 }} chiếc</div>
                @endif
            </div>

            <div class="info-group">
                <div class="info-label">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" /></svg>
                    Tiêu đề & Nội dung
                </div>
                <div class="info-value admin-test-drives-show-inline-18">{{ $booking->subject }}</div>
                <div class="admin-test-drives-show-inline-17">{{ $booking->message }}</div>
            </div>

            <div class="admin-test-drives-show-inline-16">
                <svg class="admin-test-drives-show-inline-15" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Gửi lúc: <span class="admin-test-drives-show-inline-14">{{ $booking->created_at?->format('H:i - d/m/Y') }}</span>
            </div>
        </div>

        <div class="lux-card admin-test-drives-show-inline-13">
            <h2 class="admin-test-drives-show-inline-12">
                Cập nhật tiến độ
            </h2>

            @if($booking->status === 'completed')
                <div class="admin-test-drives-show-inline-11">
                    <svg class="admin-test-drives-show-inline-10" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    <p class="admin-test-drives-show-inline-9">Lịch lái thử này đã hoàn tất. Quy trình đóng và không thể cập nhật thêm.</p>
                </div>
            @else
                <form method="post" action="{{ route('admin.test_drives.updateStatus', $booking->ticket_id) }}">
                    @csrf
                    <div class="admin-test-drives-show-inline-8">
                        <label class="admin-test-drives-show-inline-7">
                            Chọn trạng thái mới
                        </label>
                        <select class="admin-test-drives-show-inline-6" name="status">
                            @foreach(['pending' => 'Chờ xử lý', 'approved' => 'Đã duyệt', 'rejected' => 'Đã huỷ', 'completed' => 'Hoàn thành'] as $k => $v)
                                <option class="admin-test-drives-show-inline-5" value="{{ $k }}" @selected(old('status', $booking->status) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button class="admin-test-drives-show-inline-4" type="submit" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        Lưu Trạng Thái
                    </button>

                    <div class="admin-test-drives-show-inline-3">
                        <strong>Quy trình chuẩn:</strong><br>
                        • <span class="admin-test-drives-show-inline-2">Chờ xử lý</span> → Duyệt hoặc Hủy.<br>
                        • <span class="admin-test-drives-show-inline-1">Đã duyệt</span> → Đợi khách đến lái → Hoàn thành.
                    </div>
                </form>
            @endif
        </div>

    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Tìm thông báo theo ID
        const alertBox = document.getElementById('lux-toast-alert');

        if (alertBox) {
            // Hẹn giờ 2 giây (2000 milliseconds)
            setTimeout(function() {
                // Bước 1: Làm mờ và trượt nhẹ lên trên
                alertBox.style.opacity = '0';
                alertBox.style.transform = 'translateY(-10px)';

                // Bước 2: Đợi 0.5s cho hiệu ứng mờ kết thúc rồi xóa hẳn khỏi giao diện
                setTimeout(function() {
                    alertBox.remove();
                }, 500); // 500ms này khớp với thời gian transition trong CSS

            }, 2000);
        }
    });
</script>
@endsection