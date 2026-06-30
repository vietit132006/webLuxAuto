@extends('layouts.admin')

@section('title', $mode === 'create' ? 'Tạo lịch sử dịch vụ' : 'Sửa lịch sử dịch vụ')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-after-sales.css')
    @endif
@endpush

@section('content')
@php
    $isEdit = $mode === 'edit';
    $serviceDate = old('service_date', $record->service_date ? $record->service_date->format('Y-m-d') : now()->format('Y-m-d'));
    $nextDate = old('next_service_date', $record->next_service_date ? $record->next_service_date->format('Y-m-d') : '');
@endphp

<div class="after-page">
    @include('admin.warranties.partials.flash')

    <div class="after-header">
        <div>
            <a href="{{ $isEdit ? route('admin.service-records.show', $record) : route('admin.service-records.index') }}" class="after-link">Quay lại</a>
            <h1 class="after-title">{{ $isEdit ? 'Sửa lịch sử ' . $record->record_code : 'Tạo lịch sử dịch vụ' }}</h1>
            <p class="after-subtitle">Ghi nhận số km, vấn đề khách báo, công việc đã làm, phụ tùng thay thế và chi phí.</p>
        </div>
    </div>

    <form method="POST" action="{{ $isEdit ? route('admin.service-records.update', $record) : route('admin.service-records.store') }}" class="after-panel after-form">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="after-form-grid">
            <div class="after-field after-field-wide">
                <label for="service_appointment_id">Lịch hẹn liên quan</label>
                <select id="service_appointment_id" name="service_appointment_id" class="after-control">
                    <option value="">Không liên kết lịch hẹn</option>
                    @foreach($appointments as $appointment)
                        <option value="{{ $appointment->id }}" @selected((string) old('service_appointment_id', $record->service_appointment_id) === (string) $appointment->id)>
                            {{ $appointment->appointment_code }} - {{ $appointment->user?->name ?? 'N/A' }} - {{ $appointment->appointment_date?->format('d/m/Y') }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field after-field-wide">
                <label for="warranty_id">Bảo hành liên quan</label>
                <select id="warranty_id" name="warranty_id" class="after-control">
                    <option value="">Không liên kết bảo hành</option>
                    @foreach($warranties as $warranty)
                        <option value="{{ $warranty->id }}" @selected((string) old('warranty_id', $record->warranty_id) === (string) $warranty->id)>
                            {{ $warranty->warranty_code }} - {{ $warranty->user?->name ?? 'N/A' }} - {{ $warranty->car?->name ?? 'Chưa có xe' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="user_id">Khách hàng</label>
                <select id="user_id" name="user_id" class="after-control">
                    <option value="">Tự lấy từ lịch hẹn/bảo hành</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}" @selected((string) old('user_id', $record->user_id) === (string) $user->user_id)>
                            {{ $user->name }}{{ $user->phone ? ' - ' . $user->phone : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="car_id">Xe</label>
                <select id="car_id" name="car_id" class="after-control">
                    <option value="">Tự lấy từ lịch hẹn/bảo hành</option>
                    @foreach($cars as $car)
                        <option value="{{ $car->car_id }}" @selected((string) old('car_id', $record->car_id) === (string) $car->car_id)>
                            {{ $car->name }} - {{ $car->vin ?: 'Chưa VIN' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="service_type">Loại dịch vụ</label>
                <select id="service_type" name="service_type" class="after-control" required>
                    @foreach($serviceTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('service_type', $record->service_type) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="status">Trạng thái</label>
                <select id="status" name="status" class="after-control" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $record->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="service_date">Ngày dịch vụ</label>
                <input id="service_date" type="date" name="service_date" class="after-control" value="{{ $serviceDate }}" required>
            </div>

            <div class="after-field">
                <label for="mileage">Số km hiện tại</label>
                <input id="mileage" type="number" name="mileage" class="after-control" min="0" max="2000000" value="{{ old('mileage', $record->mileage) }}">
            </div>

            <div class="after-field">
                <label for="handled_by">Người xử lý</label>
                <select id="handled_by" name="handled_by" class="after-control">
                    <option value="">Chưa gán</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->user_id }}" @selected((string) old('handled_by', $record->handled_by) === (string) $user->user_id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="after-field">
                <label for="labor_cost">Chi phí công</label>
                <input id="labor_cost" type="number" name="labor_cost" class="after-control" min="0" step="1000" value="{{ old('labor_cost', (float) ($record->labor_cost ?? 0)) }}">
            </div>

            <div class="after-field">
                <label for="parts_cost">Chi phí phụ tùng</label>
                <input id="parts_cost" type="number" name="parts_cost" class="after-control" min="0" step="1000" value="{{ old('parts_cost', (float) ($record->parts_cost ?? 0)) }}">
            </div>

            <div class="after-field">
                <label for="next_service_date">Ngày bảo dưỡng tiếp</label>
                <input id="next_service_date" type="date" name="next_service_date" class="after-control" value="{{ $nextDate }}">
            </div>

            <div class="after-field">
                <label for="next_service_mileage">Km bảo dưỡng tiếp</label>
                <input id="next_service_mileage" type="number" name="next_service_mileage" class="after-control" min="0" max="2000000" value="{{ old('next_service_mileage', $record->next_service_mileage) }}">
            </div>

            <div class="after-field after-field-wide">
                <label for="problem_description">Vấn đề khách báo</label>
                <textarea id="problem_description" name="problem_description" class="after-control">{{ old('problem_description', $record->problem_description) }}</textarea>
            </div>

            <div class="after-field after-field-wide">
                <label for="work_performed">Công việc đã làm</label>
                <textarea id="work_performed" name="work_performed" class="after-control">{{ old('work_performed', $record->work_performed) }}</textarea>
            </div>

            <div class="after-field after-field-wide">
                <label for="parts_replaced">Phụ tùng thay thế</label>
                <textarea id="parts_replaced" name="parts_replaced" class="after-control">{{ old('parts_replaced', $record->parts_replaced) }}</textarea>
            </div>

            <div class="after-field after-field-wide">
                <label for="note">Ghi chú</label>
                <textarea id="note" name="note" class="after-control">{{ old('note', $record->note) }}</textarea>
            </div>
        </div>

        <div class="after-filter-actions">
            <a href="{{ $isEdit ? route('admin.service-records.show', $record) : route('admin.service-records.index') }}" class="after-button-secondary">Hủy</a>
            <button type="submit" class="after-button">{{ $isEdit ? 'Lưu lịch sử' : 'Tạo lịch sử' }}</button>
        </div>
    </form>
</div>
@endsection
