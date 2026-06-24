@extends('layouts.admin')

@section('title', $role->exists ? 'Sửa vai trò' : 'Thêm vai trò')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-roles.css')
    @endif
@endpush

@section('content')
@php
    $isProtected = $role->exists && $role->name === 'Super Admin';
@endphp

<div class="admin-roles-page">
    <div class="admin-roles-header">
        <div>
            <h1>{{ $role->exists ? 'Sửa vai trò' : 'Thêm vai trò' }}</h1>
            <p>Hệ thống / Vai trò</p>
        </div>
        <a class="admin-roles-secondary" href="{{ route('admin.roles.index') }}">Quay lại</a>
    </div>

    @if($errors->any())
        <div class="admin-roles-alert is-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form class="admin-role-form" method="POST" action="{{ $role->exists ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
        @csrf
        @if($role->exists)
            @method('PUT')
        @endif

        <div class="admin-role-panel">
            <label for="name">Tên vai trò</label>
            <input id="name" type="text" name="name" value="{{ old('name', $role->name) }}" {{ $isProtected ? 'readonly' : '' }} required>
        </div>

        <div class="admin-role-permission-grid">
            @foreach($permissionGroups as $group)
                <section class="admin-role-permission-group">
                    <div class="admin-role-permission-title">
                        <h2>{{ $group['label'] }}</h2>
                        <span>{{ $group['permissions']->count() }}</span>
                    </div>

                    <div class="admin-role-checkbox-list">
                        @foreach($group['permissions'] as $permission)
                            <label class="admin-role-checkbox">
                                <input
                                    type="checkbox"
                                    name="permissions[]"
                                    value="{{ $permission['name'] }}"
                                    {{ $selectedPermissions->contains($permission['name']) || $isProtected ? 'checked' : '' }}
                                    {{ $isProtected ? 'disabled' : '' }}
                                >
                                <span>
                                    <strong>{{ $permission['label'] }}</strong>
                                    <em>{{ $permission['name'] }}</em>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        <div class="admin-role-actions">
            <button class="admin-roles-primary" type="submit">Lưu vai trò</button>
            <a class="admin-roles-secondary" href="{{ route('admin.roles.index') }}">Hủy</a>
        </div>
    </form>
</div>
@endsection
