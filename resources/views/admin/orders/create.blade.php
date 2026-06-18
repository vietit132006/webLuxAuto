@extends('layouts.admin')
@section('title', 'Tạo đơn hàng mới (Bán hàng)')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-orders-create.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    <div class="form-wrap">
        <h1 class="form-title">Tạo đơn hàng mới (Bán hàng)</h1>

        @if($errors->any())
            <div class="admin-orders-create-inline-4">
                <ul class="admin-orders-create-inline-3">
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
                <p class="admin-orders-create-inline-2">* Nếu chọn "Hoàn tất", hệ thống sẽ tự động giảm tồn kho xe.</p>
            </div>

            <button type="submit" class="btn-submit">TẠO ĐƠN HÀNG</button>
            <a class="admin-orders-create-inline-1" href="{{ route('admin.orders.index') }}">Hủy và quay lại</a>
        </form>
    </div>
</div>
@endsection