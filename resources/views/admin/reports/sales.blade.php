@extends('layouts.admin')

@section('title', 'Báo cáo doanh thu')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports.css')
    @endif
@endpush

@section('content')
@php
    $statusBadge = function ($status): string {
        return match (\App\Models\Order::normalizeStatus($status)) {
            \App\Models\Order::STATUS_DEPOSITED => 'is-info',
            \App\Models\Order::STATUS_COMPLETED => 'is-success',
            \App\Models\Order::STATUS_CANCELLED => 'is-danger',
            default => 'is-warning',
        };
    };
@endphp

<div class="reports-page">
    <div class="reports-header">
        <div>
            <h1 class="reports-title">Báo cáo doanh thu</h1>
            <p class="reports-subtitle">Chỉ ghi nhận doanh thu từ đơn hoàn tất và đã giao xe nếu có lịch giao.</p>
        </div>
        <div class="reports-actions">
            <a class="reports-button" href="{{ route('admin.reports.sales.export', request()->query()) }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <form class="reports-filter" method="get" action="{{ route('admin.reports.sales') }}">
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
                <label for="brand_id">Hãng xe</label>
                <select id="brand_id" name="brand_id">
                    <option value="">Tất cả</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->brand_id }}" @selected((string) $filters['brand_id'] === (string) $brand->brand_id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="reports-field">
                <label for="model_id">Model xe</label>
                <select id="model_id" name="model_id">
                    <option value="">Tất cả</option>
                    @foreach($models as $model)
                        <option value="{{ $model->id }}" @selected((string) $filters['model_id'] === (string) $model->id)>
                            {{ $model->brand?->name ? $model->brand->name . ' - ' : '' }}{{ $model->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="reports-field">
                <label for="user_id">Nhân viên</label>
                <select id="user_id" name="user_id">
                    <option value="">Tất cả</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->user_id }}" @selected((string) $filters['user_id'] === (string) $user->user_id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="reports-field">
                <label for="status">Trạng thái đơn</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    @foreach($orderStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string) $filters['status'] === (string) $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="reports-filter-actions">
            <a class="reports-button reports-button-secondary" href="{{ route('admin.reports.sales') }}">Đặt lại</a>
            <button class="reports-button" type="submit">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
                </svg>
                Lọc
            </button>
        </div>
    </form>

    <div class="reports-stats-grid">
        <div class="reports-stat"><span>Tổng doanh thu</span><strong class="is-money">{{ number_format($stats['total_revenue'], 0, ',', '.') }} đ</strong></div>
        <div class="reports-stat"><span>Tổng số đơn</span><strong>{{ number_format($stats['total_orders']) }}</strong></div>
        <div class="reports-stat"><span>Đơn đã giao</span><strong>{{ number_format($stats['delivered_orders']) }}</strong></div>
        <div class="reports-stat"><span>Đơn đã cọc</span><strong>{{ number_format($stats['deposited_orders']) }}</strong></div>
        <div class="reports-stat"><span>Đơn đã hủy</span><strong>{{ number_format($stats['cancelled_orders']) }}</strong></div>
        <div class="reports-stat"><span>Tổng tiền cọc</span><strong class="is-money">{{ number_format($stats['total_deposit'], 0, ',', '.') }} đ</strong></div>
        <div class="reports-stat"><span>Tiền còn lại</span><strong class="is-money">{{ number_format($stats['total_remaining'], 0, ',', '.') }} đ</strong></div>
    </div>

    <div class="reports-chart-grid">
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Doanh thu và đơn hàng theo tháng</h2>
            </div>
            <div class="reports-chart"><canvas id="salesMonthlyChart"></canvas></div>
        </section>
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Doanh thu theo hãng xe</h2>
            </div>
            <div class="reports-chart"><canvas id="salesBrandChart"></canvas></div>
        </section>
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Doanh thu theo model xe</h2>
            </div>
            <div class="reports-chart"><canvas id="salesModelChart"></canvas></div>
        </section>
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Doanh thu theo nhân viên phụ trách</h2>
            </div>
            <div class="reports-chart"><canvas id="salesStaffChart"></canvas></div>
        </section>
    </div>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Danh sách đơn hàng theo bộ lọc</h2>
            <span class="reports-panel-note">{{ number_format($orders->total()) }} dòng</span>
        </div>
        <div class="reports-table-wrap">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Mã đơn</th>
                        <th>Khách hàng</th>
                        <th>Xe</th>
                        <th>Nhân viên</th>
                        <th>Tổng tiền</th>
                        <th>Tiền cọc</th>
                        <th>Còn lại</th>
                        <th>Trạng thái</th>
                        <th>Giao xe</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td><a class="reports-link" href="{{ route('admin.orders.show', $order->order_id) }}">{{ $order->display_code }}</a></td>
                            <td>
                                <span class="reports-main-text">{{ $order->user?->name ?? 'Khách ẩn danh' }}</span>
                                <span class="reports-sub-text">{{ $order->user?->email ?? $order->user?->phone }}</span>
                            </td>
                            <td>
                                @foreach($order->details as $detail)
                                    <span class="reports-main-text">
                                        {{ $detail->car?->carModel?->brand?->name }} {{ $detail->car?->carModel?->name }} {{ $detail->car?->name ?? 'Xe đã xóa' }}
                                    </span>
                                    <span class="reports-sub-text">SL {{ $detail->quantity }} · {{ number_format($detail->price, 0, ',', '.') }} đ</span>
                                @endforeach
                            </td>
                            <td>{{ $order->quote?->user?->name ?? $order->depositConfirmer?->name ?? 'Chưa gán' }}</td>
                            <td class="reports-money">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                            <td>{{ number_format($order->deposit_amount ?? 0, 0, ',', '.') }} đ</td>
                            <td>{{ number_format($order->remaining_amount, 0, ',', '.') }} đ</td>
                            <td><span class="reports-badge {{ $statusBadge($order->status) }}">{{ $order->status_label }}</span></td>
                            <td>
                                @if($order->delivery)
                                    <span class="reports-badge {{ $order->delivery->status === \App\Models\Delivery::STATUS_DELIVERED ? 'is-success' : 'is-info' }}">{{ $order->delivery->status_label }}</span>
                                @else
                                    <span class="reports-badge is-neutral">Chưa có lịch</span>
                                @endif
                            </td>
                            <td>{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="reports-empty" colspan="10">Không có đơn hàng phù hợp bộ lọc.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="reports-pagination">{{ $orders->links('pagination.lux') }}</div>
        @endif
    </section>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.Chart) return;

        const money = (value) => new Intl.NumberFormat('vi-VN').format(value) + ' đ';
        Chart.defaults.color = '#cbd5e1';
        Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.16)';

        const baseOptions = {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { labels: { boxWidth: 12, boxHeight: 12 } },
                tooltip: { callbacks: { label: (context) => `${context.dataset.label}: ${money(context.raw || 0)}` } }
            },
            scales: {
                x: { grid: { display: false } },
                y: { beginAtZero: true, ticks: { callback: (value) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value) } }
            }
        };

        new Chart(document.getElementById('salesMonthlyChart'), {
            type: 'bar',
            data: {
                labels: @json($monthlyChart['labels']),
                datasets: [
                    { label: 'Doanh thu', data: @json($monthlyChart['revenue']), backgroundColor: 'rgba(245, 158, 11, 0.72)', borderColor: '#f59e0b', borderWidth: 1 },
                    { label: 'Số đơn', data: @json($monthlyChart['orders']), type: 'line', yAxisID: 'orders', borderColor: '#60a5fa', backgroundColor: '#60a5fa', tension: 0.32 }
                ]
            },
            options: {
                ...baseOptions,
                scales: {
                    x: { grid: { display: false } },
                    y: baseOptions.scales.y,
                    orders: { position: 'right', beginAtZero: true, grid: { drawOnChartArea: false } }
                }
            }
        });

        const barChart = (id, labels, data, label) => new Chart(document.getElementById(id), {
            type: 'bar',
            data: { labels, datasets: [{ label, data, backgroundColor: 'rgba(59, 130, 246, 0.62)', borderColor: '#60a5fa', borderWidth: 1 }] },
            options: { ...baseOptions, indexAxis: 'y' }
        });

        barChart('salesBrandChart', @json($brandChart['labels']), @json($brandChart['revenue']), 'Doanh thu');
        barChart('salesModelChart', @json($modelChart['labels']), @json($modelChart['revenue']), 'Doanh thu');
        barChart('salesStaffChart', @json($staffChart['labels']), @json($staffChart['revenue']), 'Doanh thu');
    });
</script>
@endpush
