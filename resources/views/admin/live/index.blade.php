@extends('layouts.admin')
@section('title', 'Quản lý Livestream')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-live-index.css')
    @endif
@endpush


@section('content')
<div class="wrap">
    <div class="header-actions admin-live-index-inline-27">
        <h1 class="page-title admin-live-index-inline-26">Quản lý <span class="admin-live-index-inline-25">Livestream</span></h1>
        <p class="admin-live-index-inline-24">Cấu hình luồng phát sóng trực tiếp và ghim xe nổi bật</p>
    </div>

    {{-- Hiển thị thông báo --}}
    @if(session('success'))
        <div class="admin-live-index-inline-23">
            ✅ {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin.live.update') }}" method="POST">
        @csrf

        <div class="admin-live-index-inline-22">

            <div class="admin-live-index-inline-21">
                <h3 class="admin-live-index-inline-10">Cấu hình Video</h3>

                <div class="admin-live-index-inline-20">
                    <label class="admin-live-index-inline-19">ID hoặc Link Video YouTube</label>
                    <input class="admin-live-index-inline-18" type="text" id="youtube_input" name="video_id" value="{{ old('video_id', $live->video_id) }}" placeholder="Dán link YouTube hoặc ID vào đây...">
                    <small class="admin-live-index-inline-17">
                        💡 <b>Mẹo:</b> Bạn có thể dán <b>toàn bộ đường link</b>, hệ thống sẽ tự động lọc lấy ID.<br>
                        Ví dụ: <code>https://youtube.com/watch?v=<b>ypYFF1BQrpo</b></code>
                    </small>
                </div>

                <div class="admin-live-index-inline-16">
                    <label class="admin-live-index-inline-15">
                        <input class="admin-live-index-inline-14" type="checkbox" name="is_active" value="1" {{ $live->is_active ? 'checked' : '' }}>
                        BẬT LUỒNG PHÁT SÓNG
                    </label>
                    <p class="admin-live-index-inline-13">Nếu tắt, khách hàng sẽ không xem được Live.</p>
                </div>

                <button class="admin-live-index-inline-12" type="submit">
                    💾 LƯU CẤU HÌNH LIVE
                </button>
            </div>

            <div class="admin-live-index-inline-11">
                <h3 class="admin-live-index-inline-10">Ghim xe lên màn hình Live</h3>

                @php
                    // Lấy mảng ID xe đang được chọn (tránh lỗi nếu null)
                    $selectedCarIds = $live->featured_car_ids ?? [];
                @endphp

                <div class="admin-live-index-inline-9">
                    <div class="admin-live-index-inline-8">
                        @foreach($cars as $car)
                            <label class="admin-live-index-inline-7">
                                <input class="admin-live-index-inline-6" type="checkbox" name="car_ids[]" value="{{ $car->car_id }}"
                                    {{ in_array($car->car_id, $selectedCarIds) ? 'checked' : '' }}>

                                @if($car->image)
                                    <img class="admin-live-index-inline-5" src="{{ asset('storage/' . $car->image) }}">
                                @else
                                    <div class="admin-live-index-inline-4">No Image</div>
                                @endif

                                <div class="admin-live-index-inline-3">
                                    <h4 class="admin-live-index-inline-2">{{ $car->name }}</h4>
                                    <p class="admin-live-index-inline-1">{{ number_format($car->price, 0, ',', '.') }} VNĐ</p>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>


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