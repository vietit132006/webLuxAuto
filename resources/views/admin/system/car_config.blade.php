@extends('layouts.admin')
@section('title', 'Cấu hình xe')

@section('content')
<style>
    .form-group {
        margin-bottom: 2rem;
    }
    .label {
        display: block;
        margin-bottom: 0.8rem;
        color: var(--muted);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .input, .select {
        width: 100%;
        padding: 0.8rem;
        background: #0a0d12;
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        font-size: 1rem;
    }
    .input:focus, .select:focus {
        border-color: var(--accent);
        outline: none;
    }
    .btn-submit {
        background: var(--accent);
        color: #000;
        border: none;
        padding: 1rem 2.5rem;
        border-radius: 8px;
        font-weight: 800;
        cursor: pointer;
        font-size: 1rem;
        transition: 0.2s;
        display: inline-block;
    }
    .btn-submit:hover { background: #e4d08a; }
</style>

<div class="wrap">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Cấu hình chung cho danh mục Xe</h2>
        </div>
        <div class="panel-body" style="padding: 2rem;">
            @if(session('success'))
                <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: bold;">
                    ✅ {{ session('success') }}
                </div>
            @endif

            <form action="{{ route('admin.system.car_config.update') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label class="label">Số tiền đặt cọc mặc định (VNĐ)</label>
                    <input type="number" name="default_deposit_amount" class="input" value="{{ $settings['default_deposit_amount'] ?? '20000000' }}" placeholder="Ví dụ: 20000000">
                    <p style="font-size: 0.8rem; color: var(--muted); margin-top: 5px;">* Hệ thống sẽ dùng giá trị này khi khách hàng thực hiện đặt cọc trực tuyến.</p>
                </div>

                <div class="form-group">
                    <label class="label">Thời gian giữ xe sau khi cọc (Giờ)</label>
                    <input type="number" name="hold_time_hours" class="input" value="{{ $settings['hold_time_hours'] ?? '24' }}" placeholder="Ví dụ: 24">
                    <p style="font-size: 0.8rem; color: var(--muted); margin-top: 5px;">* Khoảng thời gian mặc định để hệ thống tự động hủy đơn nếu không hoàn tất thanh toán.</p>
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
                    <textarea name="deposit_policy_text" class="input" style="height: 120px;">{{ $settings['deposit_policy_text'] ?? '🛡️ Xe sẽ được giữ chân trong 24h kể từ khi thanh toán tiền cọc thành công. Lux Auto cam kết hoàn tiền 100% nếu xe không đúng mô tả.' }}</textarea>
                </div>

                <button type="submit" class="btn-submit">LƯU CẤU HÌNH</button>
            </form>
        </div>
    </div>
</div>
@endsection
