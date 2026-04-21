@extends('layouts.admin')
@section('title', 'Quản lý Livestream')

@section('content')
<div class="wrap">
    <div class="header-actions" style="margin-bottom: 2rem;">
        <h1 class="page-title" style="margin: 0; font-size: 1.8rem; color: #f8fafc;">Quản lý <span style="color: #ef4444;">Livestream</span></h1>
        <p style="color: var(--muted); margin-top: 0.5rem;">Cấu hình luồng phát sóng trực tiếp và ghim xe nổi bật</p>
    </div>

    {{-- Hiển thị thông báo --}}
    @if(session('success'))
        <div style="padding: 1rem; margin-bottom: 1.5rem; background: rgba(16, 185, 129, 0.1); border: 1px solid #10b981; color: #10b981; border-radius: 8px; font-weight: bold;">
            ✅ {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.live.update') }}" method="POST">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 2rem;">

            <div style="background: var(--surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border); height: fit-content;">
                <h3 style="color: var(--accent); margin-top: 0; margin-bottom: 1.5rem;">Cấu hình Video</h3>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; font-weight: bold; margin-bottom: 0.5rem;">ID hoặc Link Video YouTube</label>
                    <input type="text" id="youtube_input" name="video_id" value="{{ old('video_id', $live->video_id) }}" placeholder="Dán link YouTube hoặc ID vào đây..." style="width: 100%; padding: 0.8rem; border-radius: 6px; border: 1px solid var(--border); background: #0a0d12; color: var(--text);">
                    <small style="color: var(--muted); display: block; margin-top: 8px; line-height: 1.4;">
                        💡 <b>Mẹo:</b> Bạn có thể dán <b>toàn bộ đường link</b>, hệ thống sẽ tự động lọc lấy ID.<br>
                        Ví dụ: <code>https://youtube.com/watch?v=<b>ypYFF1BQrpo</b></code>
                    </small>
                </div>

                <div style="background: rgba(239, 68, 68, 0.1); padding: 1rem; border-radius: 8px; border: 1px dashed #ef4444;">
                    <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: bold; color: #ef4444;">
                        <input type="checkbox" name="is_active" value="1" {{ $live->is_active ? 'checked' : '' }} style="width: 20px; height: 20px; accent-color: #ef4444;">
                        BẬT LUỒNG PHÁT SÓNG
                    </label>
                    <p style="font-size: 0.85rem; color: var(--muted); margin: 5px 0 0 30px;">Nếu tắt, khách hàng sẽ không xem được Live.</p>
                </div>

                <button type="submit" style="width: 100%; padding: 1rem; margin-top: 1.5rem; background: var(--accent); color: #000; border: none; border-radius: 8px; font-weight: bold; font-size: 1.1rem; cursor: pointer; transition: 0.2s;">
                    💾 LƯU CẤU HÌNH LIVE
                </button>
            </div>

            <div style="background: var(--surface); padding: 1.5rem; border-radius: 12px; border: 1px solid var(--border);">
                <h3 style="color: var(--accent); margin-top: 0; margin-bottom: 1.5rem;">Ghim xe lên màn hình Live</h3>

                @php
                    // Lấy mảng ID xe đang được chọn (tránh lỗi nếu null)
                    $selectedCarIds = $live->featured_car_ids ?? [];
                @endphp

                <div style="max-height: 500px; overflow-y: auto; padding-right: 10px;">
                    <div style="display: grid; gap: 1rem;">
                        @foreach($cars as $car)
                            <label style="display: flex; align-items: center; gap: 15px; background: #0a0d12; padding: 1rem; border-radius: 8px; border: 1px solid var(--border); cursor: pointer; transition: 0.2s;">
                                <input type="checkbox" name="car_ids[]" value="{{ $car->car_id }}"
                                    {{ in_array($car->car_id, $selectedCarIds) ? 'checked' : '' }}
                                    style="width: 20px; height: 20px; accent-color: var(--accent);">

                                @if($car->image)
                                    <img src="{{ asset('storage/' . $car->image) }}" style="width: 80px; height: 50px; object-fit: cover; border-radius: 4px;">
                                @else
                                    <div style="width: 80px; height: 50px; background: #243042; border-radius: 4px; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: #8b97ab;">No Image</div>
                                @endif

                                <div style="flex: 1;">
                                    <h4 style="margin: 0 0 5px; color: var(--text);">{{ $car->name }}</h4>
                                    <p style="margin: 0; color: var(--accent); font-weight: bold; font-size: 0.9rem;">{{ number_format($car->price, 0, ',', '.') }} VNĐ</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

<style>
    label:hover { border-color: var(--accent) !important; }
</style>

<script>
    // Script tự động trích xuất ID YouTube khi người dùng dán Link
    document.getElementById('youtube_input').addEventListener('input', function(e) {
        let url = e.target.value.trim();

        // Regex để bắt ID từ nhiều định dạng link YouTube khác nhau
        let regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|studio\.youtube\.com\/video\/)([^#\&\?\/]*).*/;
        let match = url.match(regExp);

        if (match && match[2].length === 11) {
            // Nếu tìm thấy ID hợp lệ, thay thế nội dung ô input bằng ID đó
            e.target.value = match[2];
            e.target.style.borderColor = '#10b981'; // Đổi viền xanh báo hiệu thành công
        } else {
            e.target.style.borderColor = 'var(--border)';
        }
    });
</script>
@endsection
