@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-users-index.css')
    @endif
@endpush

@section('content')
<div class="admin-users-page">
    <div class="admin-users-header">
        <div>
            <h1>Quản lý người dùng</h1>
            <p>Hệ thống / Người dùng</p>
        </div>

        @can('users.create')
            <a class="admin-users-primary" href="{{ route('admin.users.create') }}">Thêm người dùng</a>
        @endcan
    </div>

    @if(session('success'))
        <div class="admin-users-alert is-success" id="success-alert">{{ session('success') }}</div>
        <script>
            setTimeout(() => {
                const alert = document.getElementById('success-alert');
                if (!alert) return;
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            }, 2200);
        </script>
    @endif

    @if(session('error'))
        <div class="admin-users-alert is-error">{{ session('error') }}</div>
    @endif

    <form class="admin-users-filter" method="get" action="{{ route('admin.users.index') }}">
        <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Tìm tên, email hoặc SĐT...">
        <button type="submit">Tìm kiếm</button>
    </form>

    <div class="admin-users-table-wrap">
        <table class="admin-users-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Họ tên</th>
                    <th>Email / SĐT</th>
                    <th>Vai trò</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($users as $user)
                    @php
                        $roleName = $user->adminRoleLabel();
                        $isSuperAdmin = $user->hasRole('Super Admin');
                    @endphp
                    <tr>
                        <td>#{{ $user->user_id }}</td>
                        <td>
                            <strong>{{ $user->name }}</strong>
                            @if($isSuperAdmin)
                                <span class="admin-users-badge is-gold">Super Admin</span>
                            @endif
                        </td>
                        <td class="admin-users-muted">
                            {{ $user->email }}<br>
                            {{ $user->phone ?? '---' }}
                        </td>
                        <td>
                            <span class="admin-users-badge">{{ $roleName }}</span>
                        </td>
                        <td>
                            <span class="admin-users-status {{ $user->status ? 'is-active' : 'is-locked' }}">
                                {{ $user->status ? 'Hoạt động' : 'Đã khóa' }}
                            </span>
                        </td>
                        <td class="admin-users-actions">
                            @can('users.edit')
                                <a href="{{ route('admin.users.edit', $user->user_id) }}">Sửa</a>

                                @if(auth()->id() != $user->user_id)
                                    <form action="{{ route('admin.users.toggle-status', $user->user_id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit">{{ $user->status ? 'Khóa' : 'Mở khóa' }}</button>
                                    </form>
                                @endif
                            @endcan

                            @if(auth()->id() != $user->user_id && $user->status && auth()->user()?->can('users.edit'))
                                <form action="{{ route('account-switch.switch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $user->user_id }}">
                                    <button class="is-success" type="submit">Chuyển</button>
                                </form>
                            @endif

                            @can('users.delete')
                                @if(auth()->id() != $user->user_id)
                                    <form action="{{ route('admin.users.destroy', $user->user_id) }}" method="POST" onsubmit="return confirm('Xóa người dùng này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="is-danger" type="submit">Xóa</button>
                                    </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="admin-users-empty">Chưa có người dùng.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="admin-users-pagination">
        {{ $users->links() }}
    </div>
</div>
@endsection
