@extends('layouts.admin')

@section('title', 'Báo cáo giữ chỗ xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports.css')
    @endif
@endpush

@section('content')
@php
    $reservationBadge = fn (string $status): string => match ($status) {
        \App\Models\StockReservation::STATUS_ACTIVE => 'is-info',
        \App\Models\StockReservation::STATUS_COMPLETED => 'is-success',
        \App\Models\StockReservation::STATUS_CANCELLED => 'is-danger',
        \App\Models\StockReservation::STATUS_RELEASED => 'is-neutral',
        \App\Models\StockReservation::STATUS_EXPIRED => 'is-warning',
        default => 'is-neutral',
    };
@endphp

<div class="reports-page">
    <div class="reports-header">
        <div>
            <h1 class="reports-title">Báo cáo giữ chỗ xe</h1>
            <p class="reports-subtitle">Theo dõi vòng đời giữ xe từ đặt cọc, hoàn tất, giải phóng đến hủy/hết hạn.</p>
        </div>
        <div class="reports-actions">
            <a class="reports-button" href="{{ route('admin.reports.reservations.export', request()->query()) }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <form class="reports-filter" method="get" action="{{ route('admin.reports.reservations') }}">
        <div class="reports-filter-grid">
            <div class="reports-field">
                <label for="date_from">Từ ngày</label>
                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="reports-field">
                <label for="date_to">Đến ngày</label>
                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
            </div>
            <div class="reports-field">
                <label for="status">Trạng thái</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    @foreach($reservationStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="reports-field">
                <label for="car_id">Xe</label>
                <select id="car_id" name="car_id">
                    <option value="">Tất cả</option>
                    @foreach($carsForFilter as $car)
                        <option value="{{ $car->car_id }}" @selected((string) $filters['car_id'] === (string) $car->car_id)>
                            {{ $car->carModel?->brand?->name }} {{ $car->carModel?->name }} {{ $car->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="reports-field">
                <label for="user_id">Khách hàng</label>
                <select id="user_id" name="user_id">
                    <option value="">Tất cả</option>
                    @foreach($customerUsers as $user)
                        <option value="{{ $user->user_id }}" @selected((string) $filters['user_id'] === (string) $user->user_id)>{{ $user->name }} {{ $user->phone ? '- ' . $user->phone : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="reports-filter-actions">
            <a class="reports-button reports-button-secondary" href="{{ route('admin.reports.reservations') }}">Đặt lại</a>
            <button class="reports-button" type="submit">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
                </svg>
                Lọc
            </button>
        </div>
    </form>

    <div class="reports-stats-grid">
        <div class="reports-stat"><span>Đang giữ active</span><strong>{{ number_format($stats['active']) }}</strong></div>
        <div class="reports-stat"><span>Đã hoàn tất</span><strong>{{ number_format($stats['completed']) }}</strong></div>
        <div class="reports-stat"><span>Đã hủy</span><strong>{{ number_format($stats['cancelled']) }}</strong></div>
        <div class="reports-stat"><span>Đã giải phóng</span><strong>{{ number_format($stats['released']) }}</strong></div>
        <div class="reports-stat"><span>Đã hết hạn</span><strong>{{ number_format($stats['expired']) }}</strong></div>
    </div>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Danh sách giữ chỗ</h2>
            <span class="reports-panel-note">{{ number_format($reservations->total()) }} dòng</span>
        </div>
        <div class="reports-table-wrap">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Xe</th>
                        <th>Số lượng</th>
                        <th>Trạng thái</th>
                        <th>Người giữ</th>
                        <th>Ngày giữ</th>
                        <th>Ngày giải phóng</th>
                        <th>Lý do giải phóng</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservations as $reservation)
                        <tr>
                            <td>
                                @if($reservation->order)
                                    <a class="reports-link" href="{{ route('admin.orders.show', $reservation->order_id) }}">{{ $reservation->order->display_code }}</a>
                                @else
                                    <span class="reports-muted">Chưa gắn đơn</span>
                                @endif
                            </td>
                            <td>
                                <span class="reports-main-text">{{ $reservation->user?->name ?? $reservation->order?->user?->name ?? 'N/A' }}</span>
                                <span class="reports-sub-text">{{ $reservation->user?->phone ?? $reservation->user?->email }}</span>
                            </td>
                            <td>
                                <span class="reports-main-text">{{ $reservation->car?->name ?? 'Xe đã xóa' }}</span>
                                <span class="reports-sub-text">{{ $reservation->car?->carModel?->brand?->name }} {{ $reservation->car?->carModel?->name }}</span>
                            </td>
                            <td>{{ number_format($reservation->quantity) }}</td>
                            <td><span class="reports-badge {{ $reservationBadge($reservation->status) }}">{{ $reservationStatusOptions[$reservation->status] ?? $reservation->status }}</span></td>
                            <td>{{ $reservation->reservedBy?->name ?? 'Hệ thống' }}</td>
                            <td>{{ $reservation->reserved_at?->format('d/m/Y H:i') ?? $reservation->created_at?->format('d/m/Y H:i') }}</td>
                            <td>{{ $reservation->released_at?->format('d/m/Y H:i') ?? 'Chưa có' }}</td>
                            <td>{{ $reservation->release_reason ?? 'Không có' }}</td>
                        </tr>
                    @empty
                        <tr><td class="reports-empty" colspan="9">Không có dữ liệu giữ chỗ phù hợp bộ lọc.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reservations->hasPages())
            <div class="reports-pagination">{{ $reservations->links('pagination.lux') }}</div>
        @endif
    </section>
</div>
@endsection
