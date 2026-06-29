@extends('layouts.admin')

@section('title', $mode === 'create' ? 'Tạo lịch dịch vụ' : 'Sửa lịch dịch vụ')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-after-sales.css')
    @endif
@endpush

@section('content')
@php
    $isEdit = $mode === 'edit';
    $appointmentDate = old('appointment_date', $appointment->appointment_date ? $appointment->appointment_date->format('Y-m-d') : now()->format('Y-m-d'));
    $appointmentTime = old('appointment_time', $appointment->appointment_time ? substr((string) $appointment->appointment_time, 0, 5) : '');
@endphp

<div class="after-page">
    @include('admin.warranties.partials.flash')

    <div class="after-header">
        <div>
            <a href="{{ $isEdit ? route('admin.service-appointments.show', $appointment) : route('admin.service-appointments.index') }}" class="after-link">Quay lại</a>
            <h1 class="after-title">{{ $isEdit ? 'Sửa lịch ' . $appointment->appointment_code : 'Tạo lịch dịch vụ' }}</h1>
            <p class="after-subtitle">Dùng cho bảo dưỡng định kỳ, bảo hành, kiểm tra xe hoặc sửa chữa sau bán hàng.</p>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.service-appointments.update', $appointment) : route('admin.service-appointments.store') }}" class="after-panel after-form">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="after-form-grid">
            <div class="after-field after-field-wide">
                <label for="warranty_id">Bảo hành liên quan</label>
                <select id="warranty_id" name="warranty_id" class="after-control">
                    <option value="">Không liên kết bảo hành</option>
                    @foreach($warranties as $warranty)
                        <option value="{{ $warranty->id }}" @selected((string) old('warranty_id', $appointment->warranty_id) === (string) $warranty->id)>
                            {{ $warranty->warranty_code }} - {{ $warranty->user?->name ?? 'N/A' }} - {{ $warranty->car?->name ?? 'Chưa có xe' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="user_id">Khách hàng</label>
                <select id="user_id" name="user_id" class="after-control">
                    <option value="">Tự lấy từ bảo hành</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}" @selected((string) old('user_id', $appointment->user_id) === (string) $user->user_id)>
                            {{ $user->name }}{{ $user->phone ? ' - ' . $user->phone : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="car_id">Xe</label>
                <select id="car_id" name="car_id" class="after-control">
                    <option value="">Tự lấy từ bảo hành</option>
                    @foreach($cars as $car)
                        <option value="{{ $car->car_id }}" @selected((string) old('car_id', $appointment->car_id) === (string) $car->car_id)>
                            {{ $car->name }} - {{ $car->vin ?: 'Chưa VIN' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="service_type">Loại dịch vụ</label>
                <select id="service_type" name="service_type" class="after-control" required>
                    @foreach($serviceTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('service_type', $appointment->service_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="status">Trạng thái</label>
                <select id="status" name="status" class="after-control" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $appointment->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="appointment_date">Ngày hẹn</label>
                <input id="appointment_date" type="date" name="appointment_date" class="after-control" value="{{ $appointmentDate }}" required>
            </div>

            <div class="after-field">
                <label for="appointment_time">Giờ hẹn</label>
                <input id="appointment_time" type="time" name="appointment_time" class="after-control" value="{{ $appointmentTime }}">
            </div>

            <div class="after-field">
                <label for="assigned_staff_id">Nhân viên phụ trách</label>
                <select id="assigned_staff_id" name="assigned_staff_id" class="after-control">
                    <option value="">Chưa gán</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->user_id }}" @selected((string) old('assigned_staff_id', $appointment->assigned_staff_id) === (string) $user->user_id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="service_location">Địa điểm</label>
                <input id="service_location" name="service_location" class="after-control" value="{{ old('service_location', $appointment->service_location) }}" maxlength="255">
            </div>

            <div class="after-field after-field-wide">
                <label for="customer_note">Ghi chú khách hàng</label>
                <textarea id="customer_note" name="customer_note" class="after-control">{{ old('customer_note', $appointment->customer_note) }}</textarea>
            </div>

            <div class="after-field after-field-wide">
                <label for="internal_note">Ghi chú nội bộ</label>
                <textarea id="internal_note" name="internal_note" class="after-control">{{ old('internal_note', $appointment->internal_note) }}</textarea>
            </div>
        </div>

        <div class="after-filter-actions">
            <a href="{{ $isEdit ? route('admin.service-appointments.show', $appointment) : route('admin.service-appointments.index') }}" class="after-button-secondary">Hủy</a>
            <button type="submit" class="after-button">{{ $isEdit ? 'Lưu lịch hẹn' : 'Tạo lịch hẹn' }}</button>
        </div>
    </form>
</div>
@endsection
