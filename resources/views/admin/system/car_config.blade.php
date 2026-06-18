@extends('layouts.admin')
@section('title', 'Cấu hình xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-system-car-config.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Cấu hình chung cho danh mục Xe</h2>
        </div>
        <div class="panel-body admin-system-car-config-inline-4">
            @if(session('success'))
                <div class="admin-system-car-config-inline-3">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.system.car_config.update') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="label">Số tiền đặt cọc mặc định (VNĐ)</label>
                    <input type="number" name="default_deposit_amount" class="input" value="{{ $settings['default_deposit_amount'] ?? '20000000' }}" placeholder="Ví dụ: 20000000">
                    <p class="admin-system-car-config-inline-2">* Hệ thống sẽ dùng giá trị này khi khách hàng thực hiện đặt cọc trực tuyến.</p>
                </div>

                <div class="form-group">
                    <label class="label">Thời gian giữ xe sau khi cọc (Giờ)</label>
                    <input type="number" name="hold_time_hours" class="input" value="{{ $settings['hold_time_hours'] ?? '24' }}" placeholder="Ví dụ: 24">
                    <p class="admin-system-car-config-inline-2">* Khoảng thời gian mặc định để hệ thống tự động hủy đơn nếu không hoàn tất thanh toán.</p>
                </div>

                <div class="form-group">
                    <label class="label">Đơn vị tiền tệ hiển thị</label>
                    <select name="currency_display" class="select">
                        <option value="đ" {{ ($settings['currency_display'] ?? 'đ') == 'đ' ? 'selected' : '' }}>Việt Nam Đồng (đ)</option>
                        <option value="USD" {{ ($settings['currency_display'] ?? '') == 'USD' ? 'selected' : '' }}>Đô la Mỹ (USD)</option>
                        <option value="VNĐ" {{ ($settings['currency_display'] ?? '') == 'VNĐ' ? 'selected' : '' }}>VNĐ</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="label">Chính sách bảo mật & đặt cọc (Hiển thị tại trang chi tiết xe)</label>
                    <textarea name="deposit_policy_text" class="input admin-system-car-config-inline-1">{{ $settings['deposit_policy_text'] ?? '🛡️ Xe sẽ được giữ chân trong 24h kể từ khi thanh toán tiền cọc thành công. Lux Auto cam kết hoàn tiền 100% nếu xe không đúng mô tả.' }}</textarea>
                </div>

                <button type="submit" class="btn-submit">LƯU CẤU HÌNH</button>
            </form>
        </div>
    </div>
</div>
@endsection