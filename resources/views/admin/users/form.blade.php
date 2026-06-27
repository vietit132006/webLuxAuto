@extends('layouts.admin')

@section('title', isset($user) ? 'Sửa người dùng' : 'Thêm người dùng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-users-form.css')
    @endif
@endpush

@section('content')
@php
    $currentAdminRole = old('admin_role', isset($user) ? $user->adminRoleName() : null);
@endphp

<div class="admin-user-form-page">
    <div class="admin-user-form-header">
        <div>
            <h1>{{ isset($user) ? 'Sửa người dùng' : 'Thêm người dùng' }}</h1>
            <p>Hệ thống / Người dùng</p>
        </div>
        <a class="admin-user-secondary" href="{{ route('admin.users.index') }}">Quay lại</a>
    </div>

    @if(session('error'))
        <div class="admin-user-alert is-error">{{ session('error') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-user-alert is-error">{{ $errors->first() }}</div>
    @endif

    <form class="admin-user-form" method="POST" action="{{ isset($user) ? route('admin.users.update', $user->user_id) : route('admin.users.store') }}">
        @csrf
        @if(isset($user))
            @method('PUT')
        @endif

        <div class="admin-user-grid">
            <div class="admin-user-field">
                <label for="name">Họ tên</label>
                <input id="name" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required>
            </div>

            <div class="admin-user-field">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
            </div>
        </div>

        <div class="admin-user-grid">
            <div class="admin-user-field">
                <label for="password">Mật khẩu {{ isset($user) ? '(bỏ trống nếu không đổi)' : '' }}</label>
                <input id="password" type="password" name="password" {{ isset($user) ? '' : 'required' }}>
            </div>

            <div class="admin-user-field">
                <label for="phone">Số điện thoại</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
            </div>
        </div>

        <div class="admin-user-grid">
            <div class="admin-user-field">
                <label for="admin_role">Vai trò</label>
                <select id="admin_role" name="admin_role">
                    <option value="" {{ $currentAdminRole ? '' : 'selected' }}>Khách hàng / Không gán vai trò quản trị</option>
                    @foreach($adminRoles as $role)
                        <option value="{{ $role->name }}" {{ $currentAdminRole === $role->name ? 'selected' : '' }}>
                            {{ $role->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="admin-user-field">
                <label for="status">Trạng thái tài khoản</label>
                <select id="status" name="status" required>
                    <option value="1" {{ (string) old('status', $user->status ?? 1) === '1' ? 'selected' : '' }}>Hoạt động</option>
                    <option value="0" {{ (string) old('status', $user->status ?? 1) === '0' ? 'selected' : '' }}>Bị khóa</option>
                </select>
            </div>
        </div>

        <div class="admin-user-actions">
            <button class="admin-user-primary" type="submit">Lưu người dùng</button>
            <a class="admin-user-secondary" href="{{ route('admin.users.index') }}">Hủy</a>
        </div>
    </form>
</div>
@endsection
