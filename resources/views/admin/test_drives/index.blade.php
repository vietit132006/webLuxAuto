@extends('layouts.admin')

@section('title', 'Quản lý lịch lái thử')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-test-drives-index.css')
    @endif
@endpush

@php
    $filters = $filters ?? [];
    $stats = $stats ?? [];
@endphp

@section('content')
<div class="test-drive-page">
    <div class="test-drive-header">
        <div>
            <h1>Quản lý lịch lái thử</h1>
            <p>Bán hàng / Lái thử</p>
        </div>

        @can('test_drives.export')
            <a class="btn-action btn-secondary" href="{{ route('admin.test_drives.export', request()->query()) }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M4 17v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2" />
                </svg>
                Export Excel
            </a>
        @endcan
    </div>

    @if(session('success'))
        <div class="flash-alert is-success">{{ session('success') }}</div>
    @endif

    @if(session('warning'))
        <div class="flash-alert is-warning">{{ session('warning') }}</div>
    @endif

    @if($errors->any())
        <div class="flash-alert is-error">{{ $errors->first() }}</div>
    @endif

    <div class="test-drive-stats">
        <div class="stat-card">
            <span>Tổng lịch lái</span>
            <strong>{{ number_format($stats['total'] ?? 0) }}</strong>
        </div>
        <div class="stat-card">
            <span>Đã duyệt</span>
            <strong>{{ number_format($stats['approved'] ?? 0) }}</strong>
        </div>
        <div class="stat-card">
            <span>Hoàn thành</span>
            <strong>{{ number_format($stats['completed'] ?? 0) }}</strong>
        </div>
        <div class="stat-card">
            <span>Đã hủy</span>
            <strong>{{ number_format($stats['rejected'] ?? 0) }}</strong>
        </div>
        <div class="stat-card">
            <span>Tỷ lệ hoàn thành</span>
            <strong>{{ number_format($stats['completion_rate'] ?? 0, 1) }}%</strong>
        </div>
        <div class="stat-card">
            <span>Chuyển đổi đơn hàng</span>
            <strong>{{ number_format($stats['conversion_rate'] ?? 0, 1) }}%</strong>
        </div>
    </div>

    <form class="filter-panel" method="get" action="{{ route('admin.test_drives.index') }}">
        <div class="filter-grid">
            <div class="filter-field is-wide">
                <label for="test-drive-q">Tìm kiếm</label>
                <input id="test-drive-q" name="q" type="search" value="{{ $filters['q'] ?? '' }}" placeholder="Tên khách, email, SĐT, xe, biển số, VIN, mã lịch">
            </div>

            <div class="filter-field">
                <label for="test-drive-status">Trạng thái</label>
                <select id="test-drive-status" name="status">
                    <option value="">Tất cả</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="test-drive-sales-person">Nhân viên</label>
                <select id="test-drive-sales-person" name="sales_person">
                    <option value="">Tất cả</option>
                    @foreach($salesPeople as $person)
                        <option value="{{ $person }}" @selected(($filters['sales_person'] ?? '') === $person)>{{ $person }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="created-from">Tạo từ</label>
                <input id="created-from" name="created_from" type="date" value="{{ $filters['created_from'] ?? '' }}">
            </div>

            <div class="filter-field">
                <label for="created-to">Tạo đến</label>
                <input id="created-to" name="created_to" type="date" value="{{ $filters['created_to'] ?? '' }}">
            </div>

            <div class="filter-field">
                <label for="appointment-from">Hẹn từ</label>
                <input id="appointment-from" name="appointment_from" type="date" value="{{ $filters['appointment_from'] ?? '' }}">
            </div>

            <div class="filter-field">
                <label for="appointment-to">Hẹn đến</label>
                <input id="appointment-to" name="appointment_to" type="date" value="{{ $filters['appointment_to'] ?? '' }}">
            </div>
        </div>

        <div class="filter-actions">
            <button class="btn-action btn-primary" type="submit">Lọc</button>
            <a class="btn-action btn-secondary" href="{{ route('admin.test_drives.index') }}">Xóa lọc</a>
        </div>
    </form>

    <div class="table-wrap">
        <table class="test-drive-table">
            <thead>
                <tr>
                    <th>Mã lịch</th>
                    <th>Khách hàng</th>
                    <th>Xe</th>
                    <th>Lịch hẹn</th>
                    <th>Phụ trách</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    @php
                        $carName = $booking->car ? trim(($booking->car->brand->name ?? '') . ' ' . $booking->car->name) : null;
                        $appointment = $booking->appointment_date
                            ? $booking->appointment_date->format('d/m/Y') . ($booking->appointment_time ? ' ' . substr((string) $booking->appointment_time, 0, 5) : '')
                            : null;
                    @endphp
                    <tr>
                        <td>
                            <span class="code-pill">{{ $booking->display_code }}</span>
                        </td>
                        <td>
                            <div class="main-text">{{ $booking->user->name ?? 'Khách vãng lai' }}</div>
                            <div class="sub-text">{{ $booking->user->email ?? '' }}</div>
                            <div class="sub-text">{{ $booking->user->phone ?? '' }}</div>
                        </td>
                        <td>
                            <div class="main-text">{{ $carName ?: 'Chưa xác định' }}</div>
                            <div class="sub-text">
                                {{ $booking->car?->license_plate ?: 'Chưa có biển số' }}
                                @if($booking->car?->vin)
                                    <span>VIN {{ $booking->car->vin }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="main-text">{{ $appointment ?: 'Chưa đặt lịch' }}</div>
                            <div class="sub-text">{{ $booking->showroom ?: 'Chưa chọn showroom' }}</div>
                        </td>
                        <td>
                            <div class="main-text">{{ $booking->sales_person ?: 'Chưa phân công' }}</div>
                        </td>
                        <td>
                            <span class="status-badge {{ $booking->test_drive_status_badge_class }}">{{ $booking->test_drive_status_label }}</span>
                        </td>
                        <td class="date-cell">{{ $booking->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <a class="btn-detail" href="{{ route('admin.test_drives.show', $booking->ticket_id) }}">Chi tiết</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty-cell" colspan="8">Chưa có lịch lái thử phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($bookings->hasPages())
        <div class="pagination-wrap">
            {{ $bookings->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection
