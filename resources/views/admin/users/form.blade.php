@extends('layouts.admin')
@section('title', isset($user) ? 'Sửa Người Dùng' : 'Thêm Người Dùng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-users-form.css')
    @endif
@endpush


@section('content')
<div class="wrap admin-users-form-inline-9">
    <h1 class="admin-users-form-inline-8">{{ isset($user) ? 'Sửa thông tin: ' . $user->name : 'Thêm Người Dùng Mới' }}</h1>

    <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user->user_id) : route('admin.users.store') }}">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div class="admin-users-form-inline-6">
            <label class="admin-users-form-inline-4">Họ Tên (*)</label>
            <input class="admin-users-form-inline-3" type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required>
        </div>

        <div class="admin-users-form-inline-6">
            <label class="admin-users-form-inline-4">Email (*)</label>
            <input class="admin-users-form-inline-3" type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required>
            @error('email') <div class="admin-users-form-inline-7">Email đã tồn tại!</div> @enderror
        </div>

        <div class="admin-users-form-inline-6">
            <label class="admin-users-form-inline-4">Mật khẩu {{ isset($user) ? '(Bỏ trống nếu không đổi)' : '(*)' }}</label>
            <input class="admin-users-form-inline-3" type="password" name="password" {{ isset($user) ? '' : 'required' }}>
        </div>

        <div class="admin-users-form-inline-6">
            <label class="admin-users-form-inline-4">Số điện thoại</label>
            <input class="admin-users-form-inline-3" type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}">
        </div>

        <div class="admin-users-form-inline-5">
            <div>
                <label class="admin-users-form-inline-4">Chức vụ (*)</label>
                <select class="admin-users-form-inline-3" name="role" required>
                    <option value="customer" {{ old('role', $user->role ?? '') == 'customer' ? 'selected' : '' }}>Khách hàng (Customer)</option>
                    <option value="staff" {{ old('role', $user->role ?? '') == 'staff' ? 'selected' : '' }}>Nhân viên (Staff)</option>
                    <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                </select>
            </div>
            <div>
                <label class="admin-users-form-inline-4">Trạng thái (*)</label>
                <select class="admin-users-form-inline-3" name="status" required>
                    <option value="1" {{ old('status', $user->status ?? 1) == 1 ? 'selected' : '' }}>Hoạt động</option>
                    <option value="0" {{ old('status', $user->status ?? 1) == 0 ? 'selected' : '' }}>Bị khóa</option>
                </select>
            </div>
        </div>

        <div>
            <button class="admin-users-form-inline-2" type="submit">Lưu Thông Tin</button>
            <a class="admin-users-form-inline-1" href="{{ route('admin.users.index') }}">Hủy</a>
        </div>
    </form>
</div>
@endsection