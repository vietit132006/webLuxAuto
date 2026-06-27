@extends('layouts.admin')
@section('title', 'Nhật ký hệ thống')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-system-logs.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    <div class="panel">
        <div class="panel-header">
            <h2 class="panel-title">Nhật ký hoạt động hệ thống</h2>
        </div>
        <div class="panel-body">
            @if($logs->isEmpty())
                <div class="admin-system-logs-inline-5">
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
                            <td class="admin-system-logs-inline-4">
                                {{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('H:i:s - d/m/Y') : 'N/A' }}
                            </td>
                            <td>
                                <div class="log-user">
                                    <div class="log-avatar">👤</div>
                                    <div>
                                        <div class="admin-system-logs-inline-3">{{ $log->user->name ?? 'Hệ thống' }}</div>
                                        <div class="admin-system-logs-inline-2">{{ $log->user->email ?? '' }}</div>
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

                <div class="admin-system-logs-inline-1">
                    {{ $logs->links('pagination.lux') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection