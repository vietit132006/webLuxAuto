@extends('layouts.admin')

@section('title', 'Báo cáo hậu mãi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-after-sales.css')
    @endif
@endpush

@section('content')
<div class="after-page">
    <div class="after-header">
        <div>
            <h1 class="after-title">Báo cáo hậu mãi</h1>
            <p class="after-subtitle">Tổng hợp lịch bảo dưỡng, lịch hoàn thành, chi phí dịch vụ, xe sắp bảo dưỡng và bảo hành sắp hết hạn.</p>
        </div>
        <div class="after-actions">
            <a class="after-button" href="{{ route('admin.reports.services.export', request()->query()) }}">Export Excel</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.reports.services') }}" class="after-filter">
        <div class="after-filter-grid">
            <div class="after-field">
                <label for="date_from">Từ ngày</label>
                <input id="date_from" class="after-control" type="date" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="after-field">
                <label for="date_to">Đến ngày</label>
                <input id="date_to" class="after-control" type="date" name="date_to" value="{{ $filters['date_to'] }}">
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
                <label for="status">Trạng thái hồ sơ</label>
                <select id="status" class="after-control" name="status">
                    <option value="">Tất cả</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="after-field">
                <label for="handled_by">Nhân viên</label>
                <select id="handled_by" class="after-control" name="handled_by">
                    <option value="">Tất cả</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->user_id }}" @selected((string) $filters['handled_by'] === (string) $user->user_id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="after-field after-field-wide">
                <label for="q">Tìm kiếm</label>
                <input id="q" class="after-control" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Mã hồ sơ, khách hàng, xe, công việc">
            </div>
        </div>
        <div class="after-filter-actions">
            <a class="after-button-secondary" href="{{ route('admin.reports.services') }}">Đặt lại</a>
            <button class="after-button" type="submit">Lọc</button>
        </div>
    </form>

    <div class="after-stats-grid">
        <div class="after-stat"><span>Tổng lịch bảo dưỡng</span><strong>{{ number_format($stats['appointments']) }}</strong></div>
        <div class="after-stat"><span>Lịch hoàn thành</span><strong>{{ number_format($stats['completed_appointments']) }}</strong></div>
        <div class="after-stat"><span>Lịch hủy</span><strong>{{ number_format($stats['cancelled_appointments']) }}</strong></div>
        <div class="after-stat"><span>Hồ sơ dịch vụ</span><strong>{{ number_format($stats['records']) }}</strong></div>
        <div class="after-stat is-money"><span>Chi phí dịch vụ</span><strong>{{ number_format($stats['service_cost'], 0, ',', '.') }} đ</strong></div>
        <div class="after-stat"><span>Xe đến hạn bảo dưỡng</span><strong>{{ number_format($stats['next_due']) }}</strong></div>
        <div class="after-stat"><span>BH sắp hết hạn</span><strong>{{ number_format($stats['expiring_warranties']) }}</strong></div>
    </div>

    <div class="after-layout">
        <section class="after-panel">
            <div class="after-panel-head">
                <h2 class="after-panel-title">Bảng lịch sử dịch vụ</h2>
                <span class="after-meta">{{ number_format($records->total()) }} hồ sơ</span>
            </div>
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
                            <th>Bảo dưỡng tiếp</th>
                            <th>Người xử lý</th>
                            <th>Trạng thái</th>
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
                                    <span class="after-sub-text">{{ $record->car?->carModel?->brand?->name }} {{ $record->car?->carModel?->name }}</span>
                                </td>
                                <td>{{ $record->service_type_label }}</td>
                                <td>{{ $record->service_date?->format('d/m/Y') }}</td>
                                <td class="after-money">{{ number_format((float) $record->total_cost, 0, ',', '.') }} đ</td>
                                <td>{{ $record->next_service_date?->format('d/m/Y') ?? 'Chưa hẹn' }}</td>
                                <td>{{ $record->handledBy?->name ?? 'Chưa gán' }}</td>
                                <td><span class="{{ $record->status_badge_class }}">{{ $record->status_label }}</span></td>
                            </tr>
                        @empty
                            <tr><td class="after-empty" colspan="9">Không có dữ liệu hậu mãi phù hợp bộ lọc.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($records->hasPages())
                <div class="after-pagination">{{ $records->links('pagination.lux') }}</div>
            @endif
        </section>

        <aside class="after-stack">
            <section class="after-panel">
                <div class="after-panel-head">
                    <h2 class="after-panel-title">Top xe bảo dưỡng nhiều</h2>
                </div>
                <div class="after-timeline">
                    @forelse($topCars as $car)
                        <div class="after-timeline-item">
                            <div>
                                <span class="after-main-text">{{ $car->name }}</span>
                                <span class="after-sub-text">{{ number_format($car->services_count) }} lượt dịch vụ</span>
                            </div>
                            <strong class="after-money">{{ number_format((float) $car->total_cost, 0, ',', '.') }} đ</strong>
                        </div>
                    @empty
                        <div class="after-empty">Chưa có dữ liệu top xe.</div>
                    @endforelse
                </div>
            </section>
        </aside>
    </div>
</div>
@endsection
