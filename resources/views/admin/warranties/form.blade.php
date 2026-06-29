@extends('layouts.admin')

@section('title', $mode === 'create' ? 'Tạo bảo hành' : 'Sửa bảo hành')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-after-sales.css')
    @endif
@endpush

@section('content')
@php
    $isEdit = $mode === 'edit';
    $startDate = old('start_date', $warranty->start_date ? $warranty->start_date->format('Y-m-d') : now()->format('Y-m-d'));
    $endDate = old('end_date', $warranty->end_date ? $warranty->end_date->format('Y-m-d') : now()->addMonthsNoOverflow(36)->format('Y-m-d'));
@endphp

<div class="after-page">
    @include('admin.warranties.partials.flash')

    <div class="after-header">
        <div>
            <a href="{{ $isEdit ? route('admin.warranties.show', $warranty) : route('admin.warranties.index') }}" class="after-link">Quay lại</a>
            <h1 class="after-title">{{ $isEdit ? 'Sửa bảo hành ' . $warranty->warranty_code : 'Tạo hồ sơ bảo hành' }}</h1>
            <p class="after-subtitle">Thông tin có thể tự lấy từ đơn hàng đã giao, nhưng vẫn cho phép chỉnh VIN, biển số, thời hạn và ghi chú.</p>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.warranties.update', $warranty) : route('admin.warranties.store') }}" class="after-panel after-form">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="after-form-grid">
            <div class="after-field after-field-wide">
                <label for="order_id">Đơn hàng</label>
                <select id="order_id" name="order_id" class="after-control" required>
                    <option value="">Chọn đơn đã giao</option>
                    @foreach($orders as $order)
                        @php($firstCar = $order->details->first()?->car)
                        <option value="{{ $order->order_id }}" @selected((string) old('order_id', $warranty->order_id) === (string) $order->order_id)>
                            {{ $order->display_code }} - {{ $order->user?->name ?? 'N/A' }} - {{ $firstCar?->name ?? 'Chưa có xe' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="user_id">Khách hàng</label>
                <select id="user_id" name="user_id" class="after-control">
                    <option value="">Tự lấy từ đơn hàng</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}" @selected((string) old('user_id', $warranty->user_id) === (string) $user->user_id)>
                            {{ $user->name }}{{ $user->phone ? ' - ' . $user->phone : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="car_id">Xe</label>
                <select id="car_id" name="car_id" class="after-control">
                    <option value="">Tự lấy từ đơn hàng</option>
                    @foreach($cars as $car)
                        <option value="{{ $car->car_id }}" @selected((string) old('car_id', $warranty->car_id) === (string) $car->car_id)>
                            {{ $car->name }} - {{ $car->vin ?: 'Chưa VIN' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="vin">VIN</label>
                <input id="vin" name="vin" class="after-control" value="{{ old('vin', $warranty->vin) }}" maxlength="255" placeholder="Tự lấy từ xe nếu bỏ trống">
            </div>

            <div class="after-field">
                <label for="license_plate">Biển số</label>
                <input id="license_plate" name="license_plate" class="after-control" value="{{ old('license_plate', $warranty->license_plate) }}" maxlength="255">
            </div>

            <div class="after-field">
                <label for="start_date">Ngày bắt đầu</label>
                <input id="start_date" type="date" name="start_date" class="after-control" value="{{ $startDate }}" required>
            </div>

            <div class="after-field">
                <label for="end_date">Ngày kết thúc</label>
                <input id="end_date" type="date" name="end_date" class="after-control" value="{{ $endDate }}">
            </div>

            <div class="after-field">
                <label for="warranty_months">Số tháng</label>
                <input id="warranty_months" type="number" name="warranty_months" class="after-control" min="1" max="120" value="{{ old('warranty_months', $warranty->warranty_months ?: 36) }}" required>
            </div>

            <div class="after-field">
                <label for="mileage_limit">Giới hạn km</label>
                <input id="mileage_limit" type="number" name="mileage_limit" class="after-control" min="0" max="2000000" value="{{ old('mileage_limit', $warranty->mileage_limit) }}">
            </div>

            <div class="after-field">
                <label for="status">Trạng thái</label>
                <select id="status" name="status" class="after-control" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $warranty->status ?: \App\Models\Warranty::STATUS_ACTIVE) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="after-field after-field-full">
                <label for="note">Ghi chú</label>
                <textarea id="note" name="note" class="after-control" rows="4">{{ old('note', $warranty->note) }}</textarea>
            </div>
        </div>

        <div class="after-filter-actions">
            <a href="{{ $isEdit ? route('admin.warranties.show', $warranty) : route('admin.warranties.index') }}" class="after-button-secondary">Hủy</a>
            <button type="submit" class="after-button">{{ $isEdit ? 'Lưu bảo hành' : 'Tạo bảo hành' }}</button>
        </div>
    </form>
</div>
@endsection
