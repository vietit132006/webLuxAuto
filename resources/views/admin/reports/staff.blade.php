@extends('layouts.admin')

@section('title', 'Báo cáo nhân viên sale')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports.css')
    @endif
@endpush

@section('content')
<div class="reports-page">
    <div class="reports-header">
        <div>
            <h1 class="reports-title">Báo cáo nhân viên sale</h1>
            <p class="reports-subtitle">Đo workload, báo giá, đơn hàng, xe đã giao, doanh thu và tỷ lệ chốt theo từng nhân viên.</p>
        </div>
        <div class="reports-actions">
            <a class="reports-button" href="{{ route('admin.reports.staff.export', request()->query()) }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <form class="reports-filter" method="get" action="{{ route('admin.reports.staff') }}">
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
                <label for="user_id">Nhân viên</label>
                <select id="user_id" name="user_id">
                    <option value="">Tất cả</option>
                    @foreach($staff as $user)
                        <option value="{{ $user->user_id }}" @selected((string) $filters['user_id'] === (string) $user->user_id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="reports-filter-actions">
            <a class="reports-button reports-button-secondary" href="{{ route('admin.reports.staff') }}">Đặt lại</a>
            <button class="reports-button" type="submit">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
                </svg>
                Lọc
            </button>
        </div>
    </form>

    <div class="reports-stats-grid">
        <div class="reports-stat"><span>Khách phụ trách</span><strong>{{ number_format($stats['customers']) }}</strong></div>
        <div class="reports-stat"><span>Báo giá đã tạo</span><strong>{{ number_format($stats['quotes']) }}</strong></div>
        <div class="reports-stat"><span>Báo giá accepted</span><strong>{{ number_format($stats['accepted_quotes']) }}</strong></div>
        <div class="reports-stat"><span>Đơn hàng</span><strong>{{ number_format($stats['orders']) }}</strong></div>
        <div class="reports-stat"><span>Xe đã giao</span><strong>{{ number_format($stats['delivered']) }}</strong></div>
        <div class="reports-stat"><span>Doanh thu</span><strong class="is-money">{{ number_format($stats['revenue'], 0, ',', '.') }} đ</strong></div>
        <div class="reports-stat"><span>Tỷ lệ chốt</span><strong>{{ number_format($stats['closing_rate'], 1) }}%</strong></div>
    </div>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Doanh thu theo nhân viên</h2>
        </div>
        <div class="reports-chart"><canvas id="staffRevenueChart"></canvas></div>
    </section>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Hiệu suất nhân viên</h2>
            <span class="reports-panel-note">{{ number_format($rows->count()) }} nhân viên</span>
        </div>
        <div class="reports-table-wrap">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Nhân viên</th>
                        <th>Khách phụ trách</th>
                        <th>Lịch lái thử</th>
                        <th>Báo giá đã tạo</th>
                        <th>Báo giá accepted</th>
                        <th>Đơn hàng</th>
                        <th>Xe đã giao</th>
                        <th>Doanh thu</th>
                        <th>Tỷ lệ chốt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>
                                <span class="reports-main-text">{{ $row['user']->name }}</span>
                                <span class="reports-sub-text">{{ $row['user']->email }}</span>
                            </td>
                            <td>{{ number_format($row['customers_count']) }}</td>
                            <td>{{ number_format($row['test_drives_count']) }}</td>
                            <td>{{ number_format($row['quotes_count']) }}</td>
                            <td>{{ number_format($row['accepted_quotes_count']) }}</td>
                            <td>{{ number_format($row['orders_count']) }}</td>
                            <td>{{ number_format($row['delivered_count']) }}</td>
                            <td class="reports-money">{{ number_format($row['revenue'], 0, ',', '.') }} đ</td>
                            <td><span class="reports-badge is-info">{{ number_format($row['closing_rate'], 1) }}%</span></td>
                        </tr>
                    @empty
                        <tr><td class="reports-empty" colspan="9">Không có nhân viên phù hợp bộ lọc.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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

        new Chart(document.getElementById('staffRevenueChart'), {
            type: 'bar',
            data: {
                labels: @json($staffRevenueChart['labels']),
                datasets: [{ label: 'Doanh thu', data: @json($staffRevenueChart['data']), backgroundColor: 'rgba(245, 158, 11, 0.72)', borderColor: '#f59e0b', borderWidth: 1 }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { labels: { boxWidth: 12, boxHeight: 12 } },
                    tooltip: { callbacks: { label: (context) => new Intl.NumberFormat('vi-VN').format(context.raw || 0) + ' đ' } }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { callback: (value) => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value) } },
                    y: { grid: { display: false } }
                }
            }
        });
    });
</script>
@endpush
