@extends('layouts.admin')

@section('title', 'Báo cáo khách hàng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports.css')
    @endif
@endpush

@section('content')
<div class="reports-page">
    <div class="reports-header">
        <div>
            <h1 class="reports-title">Báo cáo khách hàng</h1>
            <p class="reports-subtitle">Theo dõi nguồn khách và các bước từ khách mới đến mua xe.</p>
        </div>
        <div class="reports-actions">
            <a class="reports-button" href="{{ route('admin.reports.customers.export', request()->query()) }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <form class="reports-filter" method="get" action="{{ route('admin.reports.customers') }}">
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
                <label for="source">Nguồn khách</label>
                <select id="source" name="source">
                    <option value="">Tất cả</option>
                    @foreach($sourceOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['source'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="reports-field">
                <label for="status">Trạng thái</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    @foreach($customerStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="reports-filter-actions">
            <a class="reports-button reports-button-secondary" href="{{ route('admin.reports.customers') }}">Đặt lại</a>
            <button class="reports-button" type="submit">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
                </svg>
                Lọc
            </button>
        </div>
    </form>

    <div class="reports-stats-grid">
        <div class="reports-stat"><span>Khách hàng mới</span><strong>{{ number_format($stats['new']) }}</strong></div>
        <div class="reports-stat"><span>Đã báo giá</span><strong>{{ number_format($stats['quoted']) }}</strong></div>
        <div class="reports-stat"><span>Đã lái thử</span><strong>{{ number_format($stats['test_drive']) }}</strong></div>
        <div class="reports-stat"><span>Đã đặt cọc</span><strong>{{ number_format($stats['deposit']) }}</strong></div>
        <div class="reports-stat"><span>Đã mua xe</span><strong>{{ number_format($stats['purchased']) }}</strong></div>
    </div>

    <div class="reports-chart-grid">
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Biểu đồ nguồn khách</h2>
            </div>
            <div class="reports-chart"><canvas id="customerSourceChart"></canvas></div>
        </section>
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Ghi chú vận hành</h2>
            </div>
            <div class="reports-list">
                @foreach($sourceChart['labels'] as $index => $label)
                    <div class="reports-list-item">
                        <div>
                            <span class="reports-main-text">{{ $label }}</span>
                            <span class="reports-sub-text">Nguồn khách hàng</span>
                        </div>
                        <span class="reports-badge is-info">{{ number_format($sourceChart['data'][$index] ?? 0) }}</span>
                    </div>
                @endforeach
            </div>
        </section>
    </div>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Danh sách khách hàng</h2>
            <span class="reports-panel-note">{{ number_format($customers->total()) }} khách</span>
        </div>
        <div class="reports-table-wrap">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Mã khách</th>
                        <th>Khách hàng</th>
                        <th>Nguồn</th>
                        <th>Trạng thái</th>
                        <th>Xe quan tâm</th>
                        <th>Số báo giá</th>
                        <th>Người tạo</th>
                        <th>Ngày tạo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td><a class="reports-link" href="{{ route('admin.customers.show', $customer->customer_id) }}">{{ $customer->customer_code }}</a></td>
                            <td>
                                <span class="reports-main-text">{{ $customer->full_name }}</span>
                                <span class="reports-sub-text">{{ $customer->phone }} {{ $customer->email ? '· ' . $customer->email : '' }}</span>
                            </td>
                            <td>{{ $customer->source ?? 'Chưa có' }}</td>
                            <td><span class="reports-badge is-info">{{ $customer->statusLabel() }}</span></td>
                            <td>{{ $customer->interested_car ?? 'Chưa cập nhật' }}</td>
                            <td>{{ number_format($customer->quotes_count) }}</td>
                            <td>{{ $customer->creator?->name ?? 'Hệ thống' }}</td>
                            <td>{{ $customer->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="reports-empty" colspan="8">Không có khách hàng phù hợp bộ lọc.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="reports-pagination">{{ $customers->links('pagination.lux') }}</div>
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

        new Chart(document.getElementById('customerSourceChart'), {
            type: 'doughnut',
            data: {
                labels: @json($sourceChart['labels']),
                datasets: [{
                    data: @json($sourceChart['data']),
                    backgroundColor: ['#60a5fa', '#34d399', '#f59e0b', '#f87171', '#c084fc', '#22d3ee', '#a3e635', '#f472b6']
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
