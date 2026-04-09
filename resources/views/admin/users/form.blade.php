@extends('layouts.admin')
@section('title', isset($user) ? 'Sửa Người Dùng' : 'Thêm Người Dùng')

@section('content')
<div class="wrap" style="max-width: 600px; background: var(--surface); padding: 2rem; border-radius: 12px; border: 1px solid var(--border);">
    <h1 style="margin-top: 0; margin-bottom: 1.5rem;">{{ isset($user) ? 'Sửa thông tin: ' . $user->name : 'Thêm Người Dùng Mới' }}</h1>

    <form method="POST" action="{{ isset($user) ? route('admin.users.update', $user->user_id) : route('admin.users.store') }}">
        @csrf
        @if(isset($user)) @method('PUT') @endif

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text);">Họ Tên (*)</label>
            <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text);">Email (*)</label>
            <input type="email" name="email" value="{{ old('email', $user->email ?? '') }}" required style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
            @error('email') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">Email đã tồn tại!</div> @enderror
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text);">Mật khẩu {{ isset($user) ? '(Bỏ trống nếu không đổi)' : '(*)' }}</label>
            <input type="password" name="password" {{ isset($user) ? '' : 'required' }} style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
        </div>

        <div style="margin-bottom: 1.25rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text);">Số điện thoại</label>
            <input type="text" name="phone" value="{{ old('phone', $user->phone ?? '') }}" style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text);">Chức vụ (*)</label>
                <select name="role" required style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
                    <option value="customer" {{ old('role', $user->role ?? '') == 'customer' ? 'selected' : '' }}>Khách hàng (Customer)</option>
                    <option value="staff" {{ old('role', $user->role ?? '') == 'staff' ? 'selected' : '' }}>Nhân viên (Staff)</option>
                    <option value="admin" {{ old('role', $user->role ?? '') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                </select>
            </div>
            <div>
                <label style="display: block; margin-bottom: 0.5rem; color: var(--text);">Trạng thái (*)</label>
                <select name="status" required style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
                    <option value="1" {{ old('status', $user->status ?? 1) == 1 ? 'selected' : '' }}>Hoạt động</option>
                    <option value="0" {{ old('status', $user->status ?? 1) == 0 ? 'selected' : '' }}>Bị khóa</option>
                </select>
            </div>
        </div>

        <div>
            <button type="submit" style="background: var(--accent); color: #000; padding: 0.8rem 1.5rem; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Lưu Thông Tin</button>
            <a href="{{ route('admin.users.index') }}" style="color: var(--muted); margin-left: 15px;">Hủy</a>
        </div>
    </form>
</div>
@endsection
