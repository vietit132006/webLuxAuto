@extends('layouts.admin')

@section('title', 'Bảo hành')

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
            <h1 class="after-title">Bảo hành</h1>
            <p class="after-subtitle">Theo dõi hồ sơ bảo hành sau giao xe, ngày hết hạn, VIN, biển số và lịch sử dịch vụ liên quan.</p>
        </div>
        <div class="after-actions">
            <a href="{{ route('admin.warranties.export', request()->query()) }}" class="after-button-secondary">Export Excel</a>
            @can('warranties.create')
                <a href="{{ route('admin.warranties.create') }}" class="after-button">Tạo bảo hành</a>
            @endcan
        </div>
    </div>

    <div class="after-stats-grid">
        <div class="after-stat"><span>Tổng hồ sơ</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div class="after-stat"><span>Đang bảo hành</span><strong>{{ number_format($stats['active']) }}</strong></div>
        <div class="after-stat"><span>Sắp hết hạn 30 ngày</span><strong>{{ number_format($stats['expiring']) }}</strong></div>
        <div class="after-stat"><span>Hết hạn</span><strong>{{ number_format($stats['expired']) }}</strong></div>
        <div class="after-stat"><span>Đã hủy</span><strong>{{ number_format($stats['void']) }}</strong></div>
    </div>

    <form method="GET" action="{{ route('admin.warranties.index') }}" class="after-filter">
        <div class="after-filter-grid">
            <div class="after-field after-field-wide">
                <label for="q">Tìm kiếm</label>
                <input id="q" class="after-control" type="search" name="q" value="{{ $filters['q'] }}" placeholder="Mã BH, khách hàng, SĐT, VIN, biển số">
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
                <label for="expiring">Sắp hết hạn</label>
                <select id="expiring" class="after-control" name="expiring">
                    <option value="">Tất cả</option>
                    <option value="1" @selected($filters['expiring'] === '1')>Trong 30 ngày</option>
                </select>
            </div>
            <div class="after-field">
                <label for="date_from">Từ ngày bắt đầu</label>
                <input id="date_from" class="after-control" type="date" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="after-field">
                <label for="date_to">Đến ngày hết hạn</label>
                <input id="date_to" class="after-control" type="date" name="date_to" value="{{ $filters['date_to'] }}">
            </div>
        </div>
        <div class="after-filter-actions">
            <a href="{{ route('admin.warranties.index') }}" class="after-button-secondary">Xóa lọc</a>
            <button type="submit" class="after-button">Lọc</button>
        </div>
    </form>

    <div class="after-table-wrap">
        <table class="after-table">
            <thead>
                <tr>
                    <th>Mã bảo hành</th>
                    <th>Khách hàng</th>
                    <th>Xe</th>
                    <th>VIN / Biển số</th>
                    <th>Bắt đầu</th>
                    <th>Kết thúc</th>
                    <th>Còn lại</th>
                    <th>Trạng thái</th>
                    <th>Dịch vụ</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @forelse($warranties as $warranty)
                    <tr>
                        <td><a class="after-code" href="{{ route('admin.warranties.show', $warranty) }}">{{ $warranty->warranty_code }}</a></td>
                        <td>
                            <span class="after-main-text">{{ $warranty->user?->name ?? 'N/A' }}</span>
                            <span class="after-sub-text">{{ $warranty->user?->phone ?? $warranty->user?->email }}</span>
                        </td>
                        <td>
                            <span class="after-main-text">{{ $warranty->car?->name ?? 'Chưa gán xe' }}</span>
                            <span class="after-sub-text">{{ $warranty->car?->carModel?->brand?->name }} {{ $warranty->car?->carModel?->name }}</span>
                        </td>
                        <td>
                            <span class="after-main-text">{{ $warranty->vin ?: 'Chưa có VIN' }}</span>
                            <span class="after-sub-text">{{ $warranty->license_plate ?: 'Chưa có biển số' }}</span>
                        </td>
                        <td>{{ $warranty->start_date?->format('d/m/Y') }}</td>
                        <td>{{ $warranty->end_date?->format('d/m/Y') }}</td>
                        <td>
                            @if($warranty->days_remaining === null)
                                <span class="after-muted">N/A</span>
                            @elseif($warranty->days_remaining >= 0)
                                {{ number_format($warranty->days_remaining) }} ngày
                            @else
                                Quá hạn {{ number_format(abs($warranty->days_remaining)) }} ngày
                            @endif
                        </td>
                        <td><span class="{{ $warranty->status_badge_class }}">{{ $warranty->status_label }}</span></td>
                        <td>
                            <span class="after-main-text">{{ number_format($warranty->service_appointments_count) }} lịch hẹn</span>
                            <span class="after-sub-text">{{ number_format($warranty->service_records_count) }} lịch sử</span>
                        </td>
                        <td>
                            <div class="after-row-actions">
                                <a href="{{ route('admin.warranties.show', $warranty) }}" class="after-button-ghost">Xem</a>
                                @can('warranties.edit')
                                    <a href="{{ route('admin.warranties.edit', $warranty) }}" class="after-button-secondary">Sửa</a>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="after-empty" colspan="10">Chưa có hồ sơ bảo hành phù hợp.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($warranties->hasPages())
        <div class="after-pagination">{{ $warranties->links('pagination.lux') }}</div>
    @endif
</div>
@endsection
