@extends('layouts.admin')
@section('title', 'Quản lý Người dùng')

@section('content')
<div class="wrap">
    <div class="header-actions" style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
        <h1 class="page-title" style="margin: 0; font-size: 1.5rem;">Quản lý Người dùng</h1>
        <a href="{{ route('admin.users.create') }}" style="background: var(--accent); color: #000; padding: 0.5rem 1rem; border-radius: 6px; font-weight: bold;">+ Thêm thành viên</a>
    </div>

    @if(session('success'))
        <div id="success-alert" style="background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #34d399; font-weight: 600; transition: opacity 0.5s ease;">
            ✅ {{ session('success') }}
        </div>
        <script>setTimeout(() => { document.getElementById('success-alert').style.opacity = '0'; setTimeout(() => document.getElementById('success-alert').remove(), 500); }, 2000);</script>
    @endif

    @if(session('error'))
        <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #f87171; font-weight: 600;">
            ❌ {{ session('error') }}
        </div>
    @endif

    <form method="get" action="{{ route('admin.users.index') }}" style="margin-bottom: 1.5rem; display: flex; gap: 10px;">
        <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Tìm tên hoặc email..." style="padding: 0.6rem 1rem; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); flex: 1; max-width: 300px;">
        <button type="submit" style="padding: 0.6rem 1.2rem; border-radius: 8px; background: var(--accent); color: #000; font-weight: bold; border: none; cursor: pointer;">Tìm kiếm</button>
    </form>

    <div class="table-responsive" style="background: var(--surface); border: 1px solid var(--border); border-radius: 12px; overflow: hidden;">
        <table class="admin-table" style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="background: rgba(255,255,255,0.05); color: var(--muted); text-transform: uppercase; font-size: 0.8rem;">
                    <th style="padding: 1rem;">ID</th>
                    <th style="padding: 1rem;">Họ Tên</th>
                    <th style="padding: 1rem;">Email / SĐT</th>
                    <th style="padding: 1rem;">Vai trò</th>
                    <th style="padding: 1rem;">Trạng thái</th>
                    <th style="padding: 1rem; text-align: right;">Hành động</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                <tr style="border-top: 1px solid var(--border);">
                    <td style="padding: 1rem;">#{{ $user->user_id }}</td>
                    <td style="padding: 1rem; font-weight: bold; color: var(--text);">{{ $user->name }}</td>
                    <td style="padding: 1rem; color: var(--muted); font-size: 0.9rem;">
                        {{ $user->email }}<br>
                        {{ $user->phone ?? '---' }}
                    </td>
                    <td style="padding: 1rem;">
                        @if($user->role == 'admin')
                            <span style="background: rgba(239, 68, 68, 0.2); color: #f87171; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">Admin</span>
                        @elseif($user->role == 'staff')
                            <span style="background: rgba(59, 130, 246, 0.2); color: #60a5fa; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">Nhân viên</span>
                        @else
                            <span style="background: rgba(156, 163, 175, 0.2); color: #9ca3af; padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.75rem; font-weight: bold; text-transform: uppercase;">Khách</span>
                        @endif
                    </td>
                    <td style="padding: 1rem;">
                        <span style="color: {{ $user->status ? '#34d399' : '#f87171' }}; font-weight: bold;">
                            {{ $user->status ? 'Hoạt động' : 'Đã khóa' }}
                        </span>
                    </td>
                    <td style="padding: 1rem; text-align: right;">
                        <a href="{{ route('admin.users.edit', $user->user_id) }}" style="color: #facc15; margin-right: 10px;">Sửa</a>
                        @if(auth()->id() != $user->user_id)
                        <form action="{{ route('admin.users.destroy', $user->user_id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Khóa tài khoản này?');">
                            @csrf @method('DELETE')
                            <button type="submit" style="background: none; border: none; color: #f87171; cursor: pointer; padding: 0;">Xóa</button>
                        </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div style="margin-top: 1.5rem;">
        {{ $users->links() }}
    </div>
</div>
@endsection
