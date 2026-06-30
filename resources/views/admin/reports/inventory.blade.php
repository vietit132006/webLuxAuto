@extends('layouts.admin')

@section('title', 'Báo cáo tồn kho')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports.css')
    @endif
@endpush

@section('content')
@php
    $stockBadge = fn ($car): string => $car->isOutOfStock() ? 'is-danger' : ($car->isFullyReserved() ? 'is-warning' : 'is-success');
    $stockLabel = fn ($car): string => $car->isOutOfStock() ? 'Hết hàng' : ($car->isFullyReserved() ? 'Đã giữ hết' : 'Có thể bán');
    $statusLabel = fn ($status): string => match ((int) $status) { 2 => 'Đã cọc', 3 => 'Đã bán', default => 'Sẵn sàng' };
@endphp

<div class="reports-page">
    <div class="reports-header">
        <div>
            <h1 class="reports-title">Báo cáo tồn kho</h1>
            <p class="reports-subtitle">Theo dõi tồn vật lý, xe đã giữ chỗ và lượng xe còn có thể bán.</p>
        </div>
        <div class="reports-actions">
            <a class="reports-button reports-button-secondary" href="{{ route('admin.reports.inventory_check') }}">Kiểm tra tồn</a>
            <a class="reports-button" href="{{ route('admin.reports.inventory.export', request()->query()) }}">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 21h14" />
                </svg>
                Export Excel
            </a>
        </div>
    </div>

    <form class="reports-filter" method="get" action="{{ route('admin.reports.inventory') }}">
        <div class="reports-filter-grid">
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
                <label for="status">Trạng thái xe</label>
                <select id="status" name="status">
                    <option value="">Tất cả</option>
                    @foreach($carStatusOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string) $filters['status'] === (string) $value)>{{ $statusLabel($label) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="reports-field">
                <label for="stock_state">Tình trạng tồn</label>
                <select id="stock_state" name="stock_state">
                    <option value="">Tất cả</option>
                    @foreach($stockStateOptions as $value => $label)
                        <option value="{{ $value }}" @selected($filters['stock_state'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="reports-filter-actions">
            <a class="reports-button reports-button-secondary" href="{{ route('admin.reports.inventory') }}">Đặt lại</a>
            <button class="reports-button" type="submit">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18M7 12h10M10 18h4" />
                </svg>
                Lọc
            </button>
        </div>
    </form>

    <div class="reports-stats-grid">
        <div class="reports-stat"><span>Tồn kho vật lý</span><strong>{{ number_format($stats['physical_stock']) }}</strong></div>
        <div class="reports-stat"><span>Xe đã giữ chỗ</span><strong>{{ number_format($stats['reserved_stock']) }}</strong></div>
        <div class="reports-stat"><span>Xe có thể bán</span><strong>{{ number_format($stats['available_stock']) }}</strong></div>
        <div class="reports-stat"><span>Xe hết hàng</span><strong>{{ number_format($stats['out_of_stock']) }}</strong></div>
        <div class="reports-stat"><span>Đã giữ hết</span><strong>{{ number_format($stats['fully_reserved']) }}</strong></div>
        <div class="reports-stat"><span>Giá trị tồn kho</span><strong class="is-money">{{ number_format($stats['inventory_value'], 0, ',', '.') }} đ</strong></div>
    </div>

    <div class="reports-chart-grid">
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Tồn kho theo hãng</h2>
            </div>
            <div class="reports-chart"><canvas id="inventoryBrandChart"></canvas></div>
        </section>
        <section class="reports-panel">
            <div class="reports-panel-head">
                <h2 class="reports-panel-title">Cảnh báo tồn kho</h2>
            </div>
            <div class="reports-list">
                <div>
                    <p class="reports-panel-note">Xe tồn lâu</p>
                    @forelse($oldStockCars as $car)
                        <div class="reports-list-item">
                            <div>
                                <span class="reports-main-text">{{ $car->name }}</span>
                                <span class="reports-sub-text">{{ $car->carModel?->brand?->name }} {{ $car->carModel?->name }}</span>
                            </div>
                            <span class="reports-badge is-warning">{{ $car->stock_in_date?->diffInDays(now()) }} ngày</span>
                        </div>
                    @empty
                        <p class="reports-muted">Chưa có xe tồn lâu theo bộ lọc.</p>
                    @endforelse
                </div>
                <div>
                    <p class="reports-panel-note">Xe sắp hết hàng</p>
                    @forelse($lowStockCars as $car)
                        <div class="reports-list-item">
                            <div>
                                <span class="reports-main-text">{{ $car->name }}</span>
                                <span class="reports-sub-text">{{ $car->carModel?->brand?->name }} {{ $car->carModel?->name }}</span>
                            </div>
                            <span class="reports-badge is-info">{{ $car->availableStock() }} còn bán</span>
                        </div>
                    @empty
                        <p class="reports-muted">Không có xe sắp hết hàng.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <section class="reports-panel">
        <div class="reports-panel-head">
            <h2 class="reports-panel-title">Bảng tồn kho</h2>
            <span class="reports-panel-note">{{ number_format($cars->total()) }} xe</span>
        </div>
        <div class="reports-table-wrap">
            <table class="reports-table">
                <thead>
                    <tr>
                        <th>Mã xe</th>
                        <th>Tên xe</th>
                        <th>Hãng</th>
                        <th>Model</th>
                        <th>Tồn vật lý</th>
                        <th>Đã giữ chỗ</th>
                        <th>Có thể bán</th>
                        <th>Giá bán</th>
                        <th>Giá trị tồn</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cars as $car)
                        @php($unitPrice = (int) ($car->sale_price ?? $car->list_price ?? $car->price ?? 0))
                        <tr>
                            <td>
                                <a class="reports-link" href="{{ route('admin.cars.show', $car->car_id) }}">#{{ $car->car_id }}</a>
                                <span class="reports-sub-text">{{ $car->internal_code ?? $car->vin }}</span>
                            </td>
                            <td><span class="reports-main-text">{{ $car->name }}</span></td>
                            <td>{{ $car->carModel?->brand?->name ?? 'N/A' }}</td>
                            <td>{{ $car->carModel?->name ?? 'N/A' }}</td>
                            <td>{{ number_format($car->physicalStock()) }}</td>
                            <td>{{ number_format($car->reservedStock()) }}</td>
                            <td>{{ number_format($car->availableStock()) }}</td>
                            <td class="reports-money">{{ number_format($unitPrice, 0, ',', '.') }} đ</td>
                            <td>{{ number_format($car->physicalStock() * $unitPrice, 0, ',', '.') }} đ</td>
                            <td><span class="reports-badge {{ $stockBadge($car) }}">{{ $stockLabel($car) }}</span></td>
                        </tr>
                    @empty
                        <tr><td class="reports-empty" colspan="10">Không có xe phù hợp bộ lọc.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($cars->hasPages())
            <div class="reports-pagination">{{ $cars->links('pagination.lux') }}</div>
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

        new Chart(document.getElementById('inventoryBrandChart'), {
            type: 'bar',
            data: {
                labels: @json($stockByBrandChart['labels']),
                datasets: [
                    { label: 'Tồn vật lý', data: @json($stockByBrandChart['physical']), backgroundColor: 'rgba(96, 165, 250, 0.62)' },
                    { label: 'Đã giữ', data: @json($stockByBrandChart['reserved']), backgroundColor: 'rgba(245, 158, 11, 0.72)' }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { labels: { boxWidth: 12, boxHeight: 12 } } },
                scales: {
                    x: { stacked: true, grid: { display: false } },
                    y: { stacked: true, beginAtZero: true }
                }
            }
        });
    });
</script>
@endpush
