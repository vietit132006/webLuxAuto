@extends('layouts.admin')

@section('title', 'Lịch sử dịch vụ')

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
            <h1 class="after-title">Lịch sử dịch vụ</h1>
            <p class="after-subtitle">Lưu toàn bộ lịch sử bảo dưỡng, sửa chữa, bảo hành, chi phí và nhắc lịch tiếp theo.</p>
        </div>
        <div class="after-actions">
            @can('service_records.create')
                <a href="{{ route('admin.service-records.create') }}" class="after-button">Tạo lịch sử</a>
            @endcan
        </div>
    </div>

    <div class="after-stats-grid">
        <div class="after-stat"><span>Tổng hồ sơ</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div class="after-stat"><span>Hoàn thành</span><strong>{{ number_format($stats['completed']) }}</strong></div>
        <div class="after-stat"><span>Đã hủy</span><strong>{{ number_format($stats['cancelled']) }}</strong></div>
        <div class="after-stat is-money"><span>Tổng chi phí</span><strong>{{ number_format($stats['total_cost'], 0, ',', '.') }} đ</strong></div>
        <div class="after-stat"><span>Nhắc lịch 30 ngày</span><strong>{{ number_format($stats['next_due']) }}</strong></div>
    </div>

    <form method="GET" action="{{ route('admin.service-records.index') }}" class="after-filter">
        <div class="after-filter-grid">
            <div class="after-field after-field-wide">
                <label for="q">Tìm kiếm</label>
                <input id="q" class="after-control" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Mã lịch sử, khách hàng, xe, công việc">
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
                <label for="handled_by">Người xử lý</label>
                <select id="handled_by" class="after-control" name="handled_by">
                    <option value="">Tất cả</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->user_id }}" @selected((string) $filters['handled_by'] === (string) $user->user_id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="after-field">
                <label for="date_from">Từ ngày</label>
                <input id="date_from" class="after-control" type="date" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="after-field">
                <label for="date_to">Đến ngày</label>
                <input id="date_to" class="after-control" type="date" name="date_to" value="{{ $filters['date_to'] }}">
            </div>
        </div>
        <div class="after-filter-actions">
            <a href="{{ route('admin.service-records.index') }}" class="after-button-secondary">Xóa lọc</a>
            <button type="submit" class="after-button">Lọc</button>
        </div>
    </form>

    <div class="after-table-wrap">
        <table class="after-table">
            <thead>
                <tr>
                    <th>Mã lịch sử</th>
                    <th>Khách hàng</th>
                    <th>Xe</th>
                    <th>Loại</th>
                    <th>Ngày dịch vụ</th>
                    <th>Chi phí</th>
                    <th>Lịch tiếp</th>
                    <th>Người xử lý</th>
                    <th>Trạng thái</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $record)
                    <tr>
                        <td><a class="after-code" href="{{ route('admin.service-records.show', $record) }}">{{ $record->record_code }}</a></td>
                        <td>
                            <span class="after-main-text">{{ $record->user?->name ?? 'N/A' }}</span>
                            <span class="after-sub-text">{{ $record->user?->phone ?? $record->user?->email }}</span>
                        </td>
                        <td>
                            <span class="after-main-text">{{ $record->car?->name ?? 'Chưa gán xe' }}</span>
                            <span class="after-sub-text">{{ $record->car?->vin ?: $record->car?->license_plate }}</span>
                        </td>
                        <td>{{ $record->service_type_label }}</td>
                        <td>{{ $record->service_date?->format('d/m/Y') }}</td>
                        <td class="after-money">{{ number_format((float) $record->total_cost, 0, ',', '.') }} đ</td>
                        <td>
                            <span class="after-main-text">{{ $record->next_service_date?->format('d/m/Y') ?? 'Chưa hẹn' }}</span>
                            <span class="after-sub-text">{{ $record->next_service_mileage ? number_format($record->next_service_mileage) . ' km' : '' }}</span>
                        </td>
                        <td>{{ $record->handledBy?->name ?? 'Chưa gán' }}</td>
                        <td><span class="{{ $record->status_badge_class }}">{{ $record->status_label }}</span></td>
                        <td>
                            <div class="after-row-actions">
                                <a href="{{ route('admin.service-records.show', $record) }}" class="after-button-ghost">Xem</a>
                                @can('service_records.edit')
                                    <a href="{{ route('admin.service-records.edit', $record) }}" class="after-button-secondary">Sửa</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="after-empty" colspan="10">Chưa có lịch sử dịch vụ phù hợp.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($records->hasPages())
        <div class="after-pagination">{{ $records->links('pagination.lux') }}</div>
    @endif
</div>
@endsection
