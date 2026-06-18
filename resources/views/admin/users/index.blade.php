@extends('layouts.admin')
@section('title', 'Quản lý Người dùng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-users-index.css')
    @endif
@endpush


@section('content')
<div class="wrap">
    <div class="header-actions admin-users-index-inline-25">
        <h1 class="page-title admin-users-index-inline-24">Quản lý Người dùng</h1>
        <a class="admin-users-index-inline-23" href="{{ route('admin.users.create') }}">+ Thêm thành viên</a>
    </div>

    @if(session('success'))
        <div class="admin-users-index-inline-22" id="success-alert">
            ✅ {{ session('success') }}
        </div>
        <script>setTimeout(() => { document.getElementById('success-alert').style.opacity = '0'; setTimeout(() => document.getElementById('success-alert').remove(), 500); }, 2000);</script>
    @endif

    @if(session('error'))
        <div class="admin-users-index-inline-21">
            ❌ {{ session('error') }}
        </div>
    @endif

    <form class="admin-users-index-inline-20" method="get" action="{{ route('admin.users.index') }}">
        <input class="admin-users-index-inline-19" type="search" name="q" value="{{ $search ?? '' }}" placeholder="Tìm tên hoặc email...">
        <button class="admin-users-index-inline-18" type="submit">Tìm kiếm</button>
    </form>

    <div class="table-responsive admin-users-index-inline-17">
        <table class="admin-table admin-users-index-inline-16">
            <thead>
                <tr class="admin-users-index-inline-15">
                    <th class="admin-users-index-inline-8">ID</th>
                    <th class="admin-users-index-inline-8">Họ Tên</th>
                    <th class="admin-users-index-inline-8">Email / SĐT</th>
                    <th class="admin-users-index-inline-8">Vai trò</th>
                    <th class="admin-users-index-inline-8">Trạng thái</th>
                    <th class="admin-users-index-inline-7">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr class="admin-users-index-inline-14">
                    <td class="admin-users-index-inline-8">#{{ $user->user_id }}</td>
                    <td class="admin-users-index-inline-13">{{ $user->name }}</td>
                    <td class="admin-users-index-inline-12">
                        {{ $user->email }}<br>
                        {{ $user->phone ?? '---' }}
                    </td>
                    <td class="admin-users-index-inline-8">
                        @if($user->role == 'admin')
                            <span class="admin-users-index-inline-11">Admin</span>
                        @elseif($user->role == 'staff')
                            <span class="admin-users-index-inline-10">Nhân viên</span>
                        @else
                            <span class="admin-users-index-inline-9">Khách</span>
                        @endif
                    </td>
                    <td class="admin-users-index-inline-8">
                        <span style="color: {{ $user->status ? '#34d399' : '#f87171' }}; font-weight: bold;">
                            {{ $user->status ? 'Hoạt động' : 'Đã khóa' }}
                        </span>
                    </td>
                    <td class="admin-users-index-inline-7">
                        <a class="admin-users-index-inline-6" href="{{ route('admin.users.edit', $user->user_id) }}">Sửa</a>
                        @if(auth()->id() != $user->user_id && $user->status && (auth()->user()->role === 'admin' || $user->role === 'customer'))
                        <form class="admin-users-index-inline-5" action="{{ route('account-switch.switch') }}" method="POST">
                            @csrf
                            <input type="hidden" name="user_id" value="{{ $user->user_id }}">
                            <button class="admin-users-index-inline-4" type="submit">Chuyển</button>
                        </form>
                        @endif
                        @if(auth()->id() != $user->user_id)
                        <form class="admin-users-index-inline-3" action="{{ route('admin.users.destroy', $user->user_id) }}" method="POST" onsubmit="return confirm('Khóa tài khoản này?');">
                            @csrf @method('DELETE')
                            <button class="admin-users-index-inline-2" type="submit">Xóa</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="admin-users-index-inline-1">
        {{ $users->links() }}
    </div>
</div>
@endsection