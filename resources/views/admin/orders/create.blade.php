@extends('layouts.admin')
@section('title', 'Tạo đơn hàng mới (Bán hàng)')

@section('content')
<style>
    .form-wrap {
        max-width: 800px;
        margin: 0 auto;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 2rem;
    }
    .form-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 2rem;
        color: var(--text);
        text-align: center;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .label {
        display: block;
        margin-bottom: 0.5rem;
        color: var(--muted);
        font-weight: 600;
        font-size: 0.9rem;
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
        padding: 1rem 2rem;
        border-radius: 8px;
        font-weight: 800;
        width: 100%;
        cursor: pointer;
        font-size: 1rem;
        margin-top: 1rem;
        transition: 0.2s;
    }
    .btn-submit:hover { background: #e4d08a; }

    .error-msg {
        color: #f87171;
        font-size: 0.85rem;
        margin-top: 0.3rem;
    }
</style>

<div class="wrap">
    <div class="form-wrap">
        <h1 class="form-title">Tạo đơn hàng mới (Bán hàng)</h1>

        @if($errors->any())
            <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid #f87171; color: #f87171; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                <ul style="margin: 0; padding-left: 1.5rem;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.orders.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="label">Chọn khách hàng</label>
                <select name="user_id" class="select" required>
                    <option value="">-- Chọn khách hàng --</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="label">Chọn xe cần bán</label>
                <select name="car_id" class="select" required>
                    <option value="">-- Chọn xe --</option>
                    @foreach($cars as $car)
                        <option value="{{ $car->car_id }}">{{ $car->name }} - {{ number_format($car->price, 0, ',', '.') }} đ (Kho: {{ $car->stock }})</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="label">Trạng thái ban đầu</label>
                <select name="status" class="select" required>
                    <option value="0">⏳ Chờ xử lý</option>
                    <option value="1">💸 Đã đặt cọc</option>
                    <option value="2">✅ Hoàn tất (Giao xe)</option>
                    <option value="3">❌ Hủy bỏ</option>
                </select>
                <p style="font-size: 0.8rem; color: var(--muted); margin-top: 5px;">* Nếu chọn "Hoàn tất", hệ thống sẽ tự động giảm tồn kho xe.</p>
            </div>

            <button type="submit" class="btn-submit">TẠO ĐƠN HÀNG</button>
            <a href="{{ route('admin.orders.index') }}" style="display: block; text-align: center; margin-top: 1rem; color: var(--muted); text-decoration: none;">Hủy và quay lại</a>
        </form>
    </div>
</div>
@endsection
