@extends('layouts.admin')

@section('title', 'Bảo dưỡng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-after-sales.css')
    @endif
@endpush

@section('content')
<div class="after-page">
    @include('admin.warranties.partials.flash')

    <div class="after-header">
        <div>
            <h1 class="after-title">Bảo dưỡng</h1>
            <p class="after-subtitle">Quản lý lịch hẹn bảo dưỡng, bảo hành, sửa chữa và phân công nhân viên phụ trách.</p>
        </div>
        <div class="after-actions">
            @can('services.create')
                <a href="{{ route('admin.service-appointments.create') }}" class="after-button">Tạo lịch hẹn</a>
            @endcan
        </div>
    </div>

    <div class="after-stats-grid">
        <div class="after-stat"><span>Tổng lịch</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div class="after-stat"><span>Chờ xác nhận</span><strong>{{ number_format($stats['pending']) }}</strong></div>
        <div class="after-stat"><span>Đã xác nhận</span><strong>{{ number_format($stats['confirmed']) }}</strong></div>
        <div class="after-stat"><span>Hoàn thành</span><strong>{{ number_format($stats['completed']) }}</strong></div>
        <div class="after-stat"><span>Đã hủy</span><strong>{{ number_format($stats['cancelled']) }}</strong></div>
    </div>

    <div class="after-stats-grid">
        <div class="after-stat"><span>Lịch hôm nay</span><strong>{{ number_format($reminders['today']) }}</strong></div>
        <div class="after-stat"><span>Lịch sắp tới</span><strong>{{ number_format($reminders['upcoming']) }}</strong></div>
        <div class="after-stat"><span>Đến hạn bảo dưỡng</span><strong>{{ number_format($reminders['next_services']) }}</strong></div>
        <div class="after-stat"><span>BH sắp hết hạn</span><strong>{{ number_format($reminders['expiring_warranties']) }}</strong></div>
        <div class="after-stat"><span>Cửa sổ nhắc</span><strong>30 ngày</strong></div>
    </div>

    <form method="GET" action="{{ route('admin.service-appointments.index') }}" class="after-filter">
        <div class="after-filter-grid">
            <div class="after-field after-field-wide">
                <label for="q">Tìm kiếm</label>
                <input id="q" class="after-control" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Mã lịch, khách hàng, xe, mã bảo hành">
            </div>
            <div class="after-field">
                <label for="status">Trạng thái</label>
                <select id="status" class="after-control" name="status">
                    <option value="">Tất cả</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="after-field">
                <label for="service_type">Loại dịch vụ</label>
                <select id="service_type" class="after-control" name="service_type">
                    <option value="">Tất cả</option>
                    @foreach($serviceTypeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['service_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="after-field">
                <label for="assigned_staff_id">Nhân viên</label>
                <select id="assigned_staff_id" class="after-control" name="assigned_staff_id">
                    <option value="">Tất cả</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->user_id }}" @selected((string) $filters['assigned_staff_id'] === (string) $user->user_id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="after-field">
                <label for="date_from">Từ ngày hẹn</label>
                <input id="date_from" class="after-control" type="date" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="after-field">
                <label for="date_to">Đến ngày hẹn</label>
                <input id="date_to" class="after-control" type="date" name="date_to" value="{{ $filters['date_to'] }}">
            </div>
        </div>
        <div class="after-filter-actions">
            <a href="{{ route('admin.service-appointments.index') }}" class="after-button-secondary">Xóa lọc</a>
            <button type="submit" class="after-button">Lọc</button>
        </div>
    </form>

    <div class="after-table-wrap">
        <table class="after-table">
            <thead>
                <tr>
                    <th>Mã lịch</th>
                    <th>Khách hàng</th>
                    <th>Xe</th>
                    <th>Loại dịch vụ</th>
                    <th>Ngày hẹn</th>
                    <th>Nhân viên</th>
                    <th>Trạng thái</th>
                    <th>Lịch sử</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($appointments as $appointment)
                    <tr>
                        <td><a class="after-code" href="{{ route('admin.service-appointments.show', $appointment) }}">{{ $appointment->appointment_code }}</a></td>
                        <td>
                            <span class="after-main-text">{{ $appointment->user?->name ?? 'N/A' }}</span>
                            <span class="after-sub-text">{{ $appointment->user?->phone ?? $appointment->user?->email }}</span>
                        </td>
                        <td>
                            <span class="after-main-text">{{ $appointment->car?->name ?? 'Chưa gán xe' }}</span>
                            <span class="after-sub-text">{{ $appointment->car?->vin ?: $appointment->car?->license_plate }}</span>
                        </td>
                        <td>{{ $appointment->service_type_label }}</td>
                        <td>{{ $appointment->appointment_date?->format('d/m/Y') }} {{ $appointment->appointment_time_label }}</td>
                        <td>{{ $appointment->assignedStaff?->name ?? 'Chưa gán' }}</td>
                        <td><span class="{{ $appointment->status_badge_class }}">{{ $appointment->status_label }}</span></td>
                        <td>{{ number_format($appointment->service_records_count) }} hồ sơ</td>
                        <td>
                            <div class="after-row-actions">
                                <a href="{{ route('admin.service-appointments.show', $appointment) }}" class="after-button-ghost">Xem</a>
                                @can('services.edit')
                                    <a href="{{ route('admin.service-appointments.edit', $appointment) }}" class="after-button-secondary">Sửa</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="after-empty" colspan="9">Chưa có lịch hẹn phù hợp.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($appointments->hasPages())
        <div class="after-pagination">{{ $appointments->links('pagination.lux') }}</div>
    @endif
</div>
@endsection
