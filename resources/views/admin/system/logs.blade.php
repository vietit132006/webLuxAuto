@extends('layouts.admin')
@section('title', 'Nhật ký hệ thống')

@section('content')
<style>
    .log-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    .log-table th, .log-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .log-table th {
        color: var(--muted);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .log-action {
        font-weight: 600;
        color: var(--accent);
    }
    .log-user {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }
    .log-avatar {
        width: 32px;
        height: 32px;
        background: #0ea5e9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.8rem;
    }
</style>

<div class="wrap">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Nhật ký hoạt động hệ thống</h2>
        </div>
        <div class="panel-body">
            @if($logs->isEmpty())
                <div style="padding: 4rem; text-align: center; color: var(--muted);">
                    Chưa có nhật ký hoạt động nào được ghi lại.
                </div>
            @else
                <table class="log-table">
                    <thead>
                        <tr>
                            <th>Thời gian</th>
                            <th>Người thực hiện</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td style="color: var(--muted); font-size: 0.9rem;">
                                {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('H:i:s - d/m/Y') : 'N/A' }}
                            </td>
                            <td>
                                <div class="log-user">
                                    <div class="log-avatar">👤</div>
                                    <div>
                                        <div style="font-weight: bold; color: var(--text);">{{ $log->user->name ?? 'Hệ thống' }}</div>
                                        <div style="font-size: 0.8rem; color: var(--muted);">{{ $log->user->email ?? '' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="log-action">
                                {{ $log->action }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 2rem; display: flex; justify-content: center;">
                    {{ $logs->links('pagination.lux') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
