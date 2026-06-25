@extends('layouts.admin')

@section('title', $customer->exists ? 'Sửa khách hàng' : 'Thêm khách hàng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-customers.css')
    @endif
@endpush

@section('content')
@php
    $isEdit = $customer->exists;
@endphp

<div class="admin-customers-page is-form">
    <div class="admin-customers-head">
        <div>
            <h1>{{ $isEdit ? 'Sửa khách hàng' : 'Thêm khách hàng' }}</h1>
            <p>Bán hàng / Khách hàng</p>
        </div>

        <a class="admin-customers-secondary" href="{{ $isEdit ? route('admin.customers.show', $customer) : route('admin.customers.index') }}">Quay lại</a>
    </div>

    @if($errors->any())
        <div class="admin-customers-alert is-error">{{ $errors->first() }}</div>
    @endif

    <form class="customer-form" method="post" action="{{ $isEdit ? route('admin.customers.update', $customer) : route('admin.customers.store') }}">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <div class="customer-form-grid">
            <div class="customer-form-field">
                <label for="customer_code">Mã khách hàng</label>
                <input id="customer_code" name="customer_code" type="text" value="{{ old('customer_code', $customer->customer_code) }}" placeholder="Tự động">
            </div>

            <div class="customer-form-field">
                <label for="full_name">Họ tên</label>
                <input id="full_name" name="full_name" type="text" value="{{ old('full_name', $customer->full_name) }}" required>
            </div>

            <div class="customer-form-field">
                <label for="phone">Số điện thoại</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone', $customer->phone) }}" required>
            </div>

            <div class="customer-form-field">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email', $customer->email) }}">
            </div>

            <div class="customer-form-field">
                <label for="gender">Giới tính</label>
                <select id="gender" name="gender">
                    <option value="">Chưa cập nhật</option>
                    @foreach($genderOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('gender', $customer->gender) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="customer-form-field">
                <label for="birthday">Ngày sinh</label>
                <input id="birthday" name="birthday" type="date" value="{{ old('birthday', $customer->birthday?->format('Y-m-d')) }}">
            </div>

            <div class="customer-form-field">
                <label for="source">Nguồn khách</label>
                <select id="source" name="source">
                    <option value="">Chưa rõ</option>
                    @foreach($sourceOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('source', $customer->source) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="customer-form-field">
                <label for="status">Trạng thái</label>
                <select id="status" name="status" required>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $customer->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="customer-form-field">
                <label for="province">Tỉnh/thành</label>
                <input id="province" name="province" type="text" value="{{ old('province', $customer->province) }}">
            </div>

            <div class="customer-form-field">
                <label for="occupation">Nghề nghiệp</label>
                <input id="occupation" name="occupation" type="text" value="{{ old('occupation', $customer->occupation) }}">
            </div>

            <div class="customer-form-field is-wide">
                <label for="interested_car">Xe quan tâm</label>
                <input id="interested_car" name="interested_car" type="text" value="{{ old('interested_car', $customer->interested_car) }}">
            </div>

            <div class="customer-form-field is-wide">
                <label for="address">Địa chỉ</label>
                <textarea id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
            </div>

            <div class="customer-form-field is-wide">
                <label for="note">Ghi chú</label>
                <textarea id="note" name="note" rows="4">{{ old('note', $customer->note) }}</textarea>
            </div>
        </div>

        <div class="customer-form-actions">
            <button class="admin-customers-primary" type="submit">Lưu khách hàng</button>
            <a class="admin-customers-secondary" href="{{ $isEdit ? route('admin.customers.show', $customer) : route('admin.customers.index') }}">Hủy</a>
        </div>
    </form>
</div>
@endsection
