@extends('layouts.admin')

@section('title', 'Báo cáo tỷ lệ chuyển đổi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports.css')
    @endif
@endpush

@section('content')
<div class="reports-page">
    <div class="reports-header">
        <div>
            <h1 class="reports-title">Báo cáo tỷ lệ chuyển đổi</h1>
            <p class="reports-subtitle">Funnel theo chuỗi lái thử → báo giá → đơn hàng → giao xe, tránh chia cho 0 khi thiếu dữ liệu.</p>
        </div>
    </div>

    <form class="reports-filter" method="get" action="{{ route('admin.reports.conversion') }}">
        <div class="reports-filter-grid">
            <div class="reports-field">
                <label for="date_from">Từ ngày</label>
                <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}">
            </div>
            <div class="reports-field">
                <label for="date_to">Đến ngày</label>
                <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}">
            </div>
        </div>
        <div class="reports-filter-actions">
            <a class="reports-button reports-button-secondary" href="{{ route('admin.reports.conversion') }}">Đặt lại</a>
            <button class="reports-button" type="submit">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
                </svg>
                Lọc
            </button>
        </div>
    </form>

    <div class="reports-stats-grid">
        <div class="reports-stat"><span>Tổng lịch lái thử</span><strong>{{ number_format($stats['test_drives']) }}</strong></div>
        <div class="reports-stat"><span>Tổng báo giá</span><strong>{{ number_format($stats['quotes']) }}</strong></div>
        <div class="reports-stat"><span>Tổng đơn hàng</span><strong>{{ number_format($stats['orders']) }}</strong></div>
        <div class="reports-stat"><span>Xe đã giao</span><strong>{{ number_format($stats['delivered']) }}</strong></div>
        <div class="reports-stat"><span>Lái thử → Báo giá</span><strong>{{ number_format($stats['test_drive_to_quote'], 1) }}%</strong></div>
        <div class="reports-stat"><span>Báo giá → Đơn hàng</span><strong>{{ number_format($stats['quote_to_order'], 1) }}%</strong></div>
        <div class="reports-stat"><span>Đơn hàng → Giao xe</span><strong>{{ number_format($stats['order_to_delivery'], 1) }}%</strong></div>
    </div>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Funnel chuyển đổi</h2>
        </div>
        <div class="reports-funnel">
            @foreach($funnel as $step)
                <div class="reports-funnel-step">
                    <span>{{ $step['label'] }}</span>
                    <strong>{{ number_format($step['value']) }}</strong>
                    <em>{{ number_format($step['rate'], 1) }}%</em>
                </div>
            @endforeach
        </div>
    </section>

    <div class="reports-chart-grid">
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Biểu đồ funnel</h2>
            </div>
            <div class="reports-chart"><canvas id="conversionFunnelChart"></canvas></div>
        </section>
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Báo giá theo trạng thái</h2>
            </div>
            <div class="reports-chart"><canvas id="quoteStatusChart"></canvas></div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.Chart) return;

        Chart.defaults.color = '#cbd5e1';
        Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.16)';

        new Chart(document.getElementById('conversionFunnelChart'), {
            type: 'bar',
            data: {
                labels: @json($funnelChart['labels']),
                datasets: [{
                    label: 'Số lượng',
                    data: @json($funnelChart['data']),
                    backgroundColor: ['#60a5fa', '#34d399', '#f59e0b', '#c084fc'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: (context) => 'Tỷ lệ: ' + @json($funnelChart['rates'])[context.dataIndex] + '%'
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false } },
                    y: { beginAtZero: true }
                }
            }
        });

        new Chart(document.getElementById('quoteStatusChart'), {
            type: 'doughnut',
            data: {
                labels: @json($quoteStatusChart['labels']),
                datasets: [{
                    data: @json($quoteStatusChart['data']),
                    backgroundColor: ['#94a3b8', '#60a5fa', '#34d399', '#f87171', '#f59e0b']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, boxHeight: 12 } } }
            }
        });
    });
</script>
@endpush
