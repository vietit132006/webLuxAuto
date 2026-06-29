@extends('layouts.admin')

@section('title', 'Báo cáo giao xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports.css')
    @endif
@endpush

@section('content')
@php
    $deliveryBadge = fn (?string $status): string => match ($status) {
        \App\Models\Delivery::STATUS_PENDING => 'is-warning',
        \App\Models\Delivery::STATUS_PREPARING => 'is-info',
        \App\Models\Delivery::STATUS_READY => 'is-ready',
        \App\Models\Delivery::STATUS_DELIVERED => 'is-success',
        \App\Models\Delivery::STATUS_CANCELLED => 'is-danger',
        default => 'is-neutral',
    };
@endphp

<div class="reports-page">
    <div class="reports-header">
        <div>
            <h1 class="reports-title">Báo cáo giao xe</h1>
            <p class="reports-subtitle">Theo dõi kế hoạch bàn giao, trạng thái giao xe và thời điểm trừ tồn kho.</p>
        </div>
        <div class="reports-actions">
            <a class="reports-button" href="{{ route('admin.reports.deliveries.export', request()->query()) }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <form class="reports-filter" method="get" action="{{ route('admin.reports.deliveries') }}">
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
                <label for="status">Trạng thái giao xe</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    @foreach($deliveryStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="reports-field">
                <label for="delivery_staff_id">Nhân viên giao</label>
                <select id="delivery_staff_id" name="delivery_staff_id">
                    <option value="">Tất cả</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->user_id }}" @selected((string) $filters['delivery_staff_id'] === (string) $user->user_id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="reports-filter-actions">
            <a class="reports-button reports-button-secondary" href="{{ route('admin.reports.deliveries') }}">Đặt lại</a>
            <button class="reports-button" type="submit">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
                </svg>
                Lọc
            </button>
        </div>
    </form>

    <div class="reports-stats-grid">
        <div class="reports-stat"><span>Tổng lịch giao</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div class="reports-stat"><span>Chờ giao</span><strong>{{ number_format($stats['pending']) }}</strong></div>
        <div class="reports-stat"><span>Đang chuẩn bị</span><strong>{{ number_format($stats['preparing']) }}</strong></div>
        <div class="reports-stat"><span>Sẵn sàng giao</span><strong>{{ number_format($stats['ready']) }}</strong></div>
        <div class="reports-stat"><span>Đã giao</span><strong>{{ number_format($stats['delivered']) }}</strong></div>
        <div class="reports-stat"><span>Hủy giao</span><strong>{{ number_format($stats['cancelled']) }}</strong></div>
        <div class="reports-stat"><span>Tỷ lệ giao thành công</span><strong>{{ number_format($stats['success_rate'], 1) }}%</strong></div>
    </div>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Giao xe theo trạng thái</h2>
        </div>
        <div class="reports-chart"><canvas id="deliveryStatusChart"></canvas></div>
    </section>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Bảng giao xe</h2>
            <span class="reports-panel-note">{{ number_format($deliveries->total()) }} lịch</span>
        </div>
        <div class="reports-table-wrap">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Mã đơn hàng</th>
                        <th>Khách hàng</th>
                        <th>Xe</th>
                        <th>Ngày giao dự kiến</th>
                        <th>Ngày giao thực tế</th>
                        <th>Nhân viên giao</th>
                        <th>Trạng thái</th>
                        <th>Thời điểm trừ tồn kho</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($deliveries as $delivery)
                        <tr>
                            <td><a class="reports-link" href="{{ route('admin.orders.show', $delivery->order_id) }}">{{ $delivery->order?->display_code ?? 'DH' . str_pad((string) $delivery->order_id, 6, '0', STR_PAD_LEFT) }}</a></td>
                            <td>
                                <span class="reports-main-text">{{ $delivery->order?->user?->name ?? 'N/A' }}</span>
                                <span class="reports-sub-text">{{ $delivery->order?->user?->email ?? $delivery->order?->user?->phone }}</span>
                            </td>
                            <td>
                                <span class="reports-main-text">{{ $delivery->car?->name ?? 'Chưa gán xe' }}</span>
                                <span class="reports-sub-text">{{ $delivery->car?->carModel?->brand?->name }} {{ $delivery->car?->carModel?->name }}</span>
                            </td>
                            <td>{{ $delivery->expected_delivery_date?->format('d/m/Y H:i') ?? 'Chưa hẹn' }}</td>
                            <td>{{ $delivery->actual_delivery_date?->format('d/m/Y H:i') ?? 'Chưa giao' }}</td>
                            <td>{{ $delivery->deliveryStaff?->name ?? 'Chưa gán' }}</td>
                            <td><span class="reports-badge {{ $deliveryBadge($delivery->status) }}">{{ $delivery->status_label }}</span></td>
                            <td>{{ $delivery->stock_deducted_at?->format('d/m/Y H:i') ?? 'Chưa trừ' }}</td>
                        </tr>
                    @empty
                        <tr><td class="reports-empty" colspan="8">Không có lịch giao xe phù hợp bộ lọc.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($deliveries->hasPages())
            <div class="reports-pagination">{{ $deliveries->links('pagination.lux') }}</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.Chart) return;

        Chart.defaults.color = '#cbd5e1';
        Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.16)';

        new Chart(document.getElementById('deliveryStatusChart'), {
            type: 'bar',
            data: {
                labels: @json($deliveryStatusChart['labels']),
                datasets: [{ label: 'Lịch giao', data: @json($deliveryStatusChart['data']), backgroundColor: 'rgba(52, 211, 153, 0.6)', borderColor: '#34d399', borderWidth: 1 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, boxHeight: 12 } } },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true }
                }
            }
        });
    });
</script>
@endpush
