@extends('layouts.admin')

@section('title', 'Vai trò')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-roles.css')
    @endif
@endpush

@section('content')
<div class="admin-roles-page">
    <div class="admin-roles-header">
        <div>
            <h1>Vai trò</h1>
            <p>Hệ thống / Vai trò</p>
        </div>

        @can('roles.create')
            <a class="admin-roles-primary" href="{{ route('admin.roles.create') }}">Thêm vai trò</a>
        @endcan
    </div>

    @if(session('success'))
        <div class="admin-roles-alert is-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="admin-roles-alert is-error">{{ session('error') }}</div>
    @endif

    <div class="admin-roles-table-wrap">
        <table class="admin-roles-table">
            <thead>
                <tr>
                    <th>Vai trò</th>
                    <th>Guard</th>
                    <th>Người dùng</th>
                    <th>Quyền</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                    <tr>
                        <td>
                            <strong>{{ $role->name }}</strong>
                            @if($role->name === 'Super Admin')
                                <span class="admin-roles-badge is-gold">Bảo vệ</span>
                            @endif
                        </td>
                        <td>{{ $role->guard_name }}</td>
                        <td>{{ number_format($role->users_count) }}</td>
                        <td>
                            <span class="admin-roles-badge">{{ $role->permissions->count() }} quyền</span>
                        </td>
                        <td class="admin-roles-actions">
                            @can('roles.edit')
                                <a href="{{ route('admin.roles.edit', $role) }}">Sửa</a>
                            @endcan

                            @can('roles.delete')
                                @if($role->name !== 'Super Admin')
                                    <form method="POST" action="{{ route('admin.roles.destroy', $role) }}" onsubmit="return confirm('Xóa vai trò này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit">Xóa</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="admin-roles-empty">Chưa có vai trò.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-roles-pagination">
        {{ $roles->links() }}
    </div>
</div>
@endsection
