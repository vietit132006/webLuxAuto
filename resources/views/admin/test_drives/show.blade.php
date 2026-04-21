@extends('layouts.admin')

@section('title', 'Chi tiết lịch lái thử')

@section('content')
<div class="wrap" style="max-width: 1000px; margin: 0 auto; padding: 2rem 1rem;">

    <a href="{{ route('admin.test_drives.index') }}" style="color: var(--accent); text-decoration: none; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 5px; margin-bottom: 1.5rem; transition: 0.2s;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
        <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" /></svg>
        Quay lại danh sách
    </a>

    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
        <h1 style="margin: 0; font-size: 2rem; color: var(--text);">
            Chi tiết yêu cầu <span style="color: var(--accent);">#{{ $booking->ticket_id }}</span>
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
            <span style="display: inline-block; width: 8px; height: 8px; border-radius: 50%; background-color: currentColor;"></span>
            {{ $statusText }}
        </span>
    </div>

@if(session('success'))
        <div id="lux-toast-alert" style="padding: 1rem 1.5rem; margin-bottom: 2rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #34d399; border-radius: 8px; font-weight: bold; display: flex; align-items: center; gap: 10px; transition: opacity 0.5s ease, transform 0.5s ease;">
            <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="padding: 1rem 1.5rem; margin-bottom: 2rem; background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #f87171; border-radius: 8px; font-weight: bold; display: flex; align-items: center; gap: 10px;">
            <svg style="width: 20px; height: 20px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ $errors->first() }}
        </div>
    @endif

    <div class="lux-grid">

        <div class="lux-card">
            <h2 style="margin: 0 0 1.5rem; font-size: 1.2rem; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
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
                <div class="info-value" style="color: var(--accent);">
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
                <div class="info-value" style="margin-bottom: 0.5rem;">{{ $booking->subject }}</div>
                <div style="background: rgba(0,0,0,0.2); padding: 1rem; border-radius: 8px; border: 1px solid var(--border); color: var(--muted); font-size: 0.95rem; line-height: 1.6; white-space: pre-wrap;">{{ $booking->message }}</div>
            </div>

            <div style="margin-top: 1.5rem; font-size: 0.85rem; color: var(--muted); display: flex; align-items: center; gap: 5px;">
                <svg style="width: 16px; height: 16px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                Gửi lúc: <span style="color: var(--text);">{{ $booking->created_at?->format('H:i - d/m/Y') }}</span>
            </div>
        </div>

        <div class="lux-card" style="height: fit-content;">
            <h2 style="margin: 0 0 1.5rem; font-size: 1.2rem; color: var(--text); border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                Cập nhật tiến độ
            </h2>

            @if($booking->status === 'completed')
                <div style="background: rgba(16, 185, 129, 0.05); border: 1px dashed rgba(16, 185, 129, 0.4); padding: 1.5rem; border-radius: 8px; text-align: center; color: var(--muted);">
                    <svg style="width: 40px; height: 40px; color: #34d399; margin: 0 auto 10px;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" /></svg>
                    <p style="margin: 0; font-size: 0.95rem;">Lịch lái thử này đã hoàn tất. Quy trình đóng và không thể cập nhật thêm.</p>
                </div>
            @else
                <form method="post" action="{{ route('admin.test_drives.updateStatus', $booking->ticket_id) }}">
                    @csrf
                    <div style="margin-bottom: 1.2rem;">
                        <label style="display: block; font-size: 0.9rem; color: var(--text); font-weight: bold; margin-bottom: 0.5rem;">
                            Chọn trạng thái mới
                        </label>
                        <select name="status" style="width: 100%; background: #0a0d12; border: 1px solid var(--border); color: var(--text); padding: 0.8rem 1rem; border-radius: 8px; outline: none; cursor: pointer; font-size: 0.95rem;">
                            @foreach(['pending' => 'Chờ xử lý', 'approved' => 'Đã duyệt', 'rejected' => 'Đã huỷ', 'completed' => 'Hoàn thành'] as $k => $v)
                                <option value="{{ $k }}" style="background: var(--bg);" @selected(old('status', $booking->status) === $k)>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <button type="submit" style="width: 100%; padding: 0.9rem; border-radius: 8px; background: var(--accent); color: #000; border: none; font-weight: bold; font-size: 1rem; cursor: pointer; transition: 0.2s; box-shadow: 0 4px 10px rgba(201, 169, 98, 0.2);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                        Lưu Trạng Thái
                    </button>

                    <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(0,0,0,0.2); border-radius: 8px; font-size: 0.85rem; color: var(--muted); border-left: 3px solid var(--border);">
                        <strong>Quy trình chuẩn:</strong><br>
                        • <span style="color: #facc15;">Chờ xử lý</span> → Duyệt hoặc Hủy.<br>
                        • <span style="color: #34d399;">Đã duyệt</span> → Đợi khách đến lái → Hoàn thành.
                    </div>
                </form>
            @endif
        </div>

    </div>
</div>

<style>
    .lux-grid {
        display: grid;
        grid-template-columns: 1.8fr 1fr;
        gap: 1.5rem;
    }
    .lux-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .info-group {
        margin-bottom: 1.2rem;
        padding-bottom: 1.2rem;
        border-bottom: 1px dashed var(--border);
    }
    .info-group:last-of-type {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    .info-label {
        font-size: 0.8rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: bold;
    }
    .info-label svg {
        width: 16px;
        height: 16px;
        color: var(--accent);
    }
    .info-value {
        font-size: 1.1rem;
        color: var(--text);
        font-weight: 500;
    }
    .info-sub {
        font-size: 0.85rem;
        color: var(--muted);
        margin-top: 4px;
    }

    /* Đảm bảo hiển thị tốt trên điện thoại */
    @media (max-width: 900px) {
        .lux-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
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
