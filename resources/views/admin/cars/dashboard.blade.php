@extends('layouts.admin')

@section('title', 'Bảng điều khiển Admin')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-cars-dashboard.css')
    @endif
@endpush

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    @php
        $carStatusLabels = [
            '0' => 'Ẩn',
            '1' => 'Sẵn sàng',
            '2' => 'Đã cọc',
            '3' => 'Đã bán',
        ];
    @endphp

    <div class="dashboard-page">
        <div class="dashboard-header">
            <div>
                <p class="dashboard-kicker">Tổng quan showroom</p>
                <h1 class="dashboard-title">Bảng điều khiển</h1>
                <p class="dashboard-subtitle">
                    Theo dõi tồn kho, giữ chỗ, pipeline bán hàng, giao xe và doanh thu trong {{ $dashboardRange['label'] }}.
                </p>
            </div>

            <form class="dashboard-custom-range" method="GET" action="{{ route('admin.dashboard') }}">
                <input type="hidden" name="range" value="custom">
                <label>
                    <span>Từ ngày</span>
                    <input type="date" name="from" value="{{ $dashboardRange['from_input'] }}">
                </label>
                <label>
                    <span>Đến ngày</span>
                    <input type="date" name="to" value="{{ $dashboardRange['to_input'] }}">
                </label>
                <button type="submit" class="dashboard-filter-button">
                    <i class="fa-solid fa-filter"></i>
                    Lọc
                </button>
            </form>
        </div>

        <div class="dashboard-range-bar" aria-label="Bộ lọc thời gian">
            @foreach ($dashboardRangeOptions as $key => $label)
                @continue($key === 'custom')
                <a
                    href="{{ route('admin.dashboard', ['range' => $key]) }}"
                    class="dashboard-range-pill {{ $dashboardRange['key'] === $key ? 'is-active' : '' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
            <span class="dashboard-range-pill is-readonly {{ $dashboardRange['key'] === 'custom' ? 'is-active' : '' }}">
                {{ $dashboardRangeOptions['custom'] }}
            </span>
        </div>

        @if (empty($dashboardStats))
            <div class="panel dashboard-empty-permission">
                <i class="fa-solid fa-shield-halved"></i>
                <p>Tài khoản hiện tại chưa có quyền xem số liệu dashboard.</p>
            </div>
        @else
            <div class="stat-grid">
                @foreach ($dashboardStats as $stat)
                    <div class="stat-card stat-card-{{ $stat['tone'] }}">
                        <div class="stat-icon">
                            <i class="fa-solid {{ $stat['icon'] }}"></i>
                        </div>
                        <div class="stat-info">
                            <div class="stat-label">{{ $stat['label'] }}</div>
                            <div class="stat-value">{{ $stat['value'] }}</div>
                            <div class="stat-meta">{{ $stat['meta'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        @if ($dashboardCharts['revenue'] || $dashboardCharts['orders'] || $dashboardCharts['quoteStatuses'] || $dashboardCharts['testDriveStatuses'])
            <div class="dashboard-section">
                <div class="section-heading">
                    <div>
                        <h2>Biểu đồ vận hành</h2>
                        <p>Doanh thu, đơn hàng và trạng thái pipeline theo dữ liệu thật.</p>
                    </div>
                </div>

                <div class="chart-grid">
                    @if ($dashboardCharts['revenue'])
                        <div class="panel chart-panel chart-panel-wide">
                            <div class="panel-header">
                                <h3 class="panel-title">Doanh thu 12 tháng gần nhất</h3>
                            </div>
                            <div class="chart-frame">
                                <canvas id="dashboardRevenueChart" aria-label="Doanh thu theo tháng"></canvas>
                            </div>
                        </div>
                    @endif

                    @if ($dashboardCharts['orders'])
                        <div class="panel chart-panel">
                            <div class="panel-header">
                                <h3 class="panel-title">Đơn hàng theo tháng</h3>
                            </div>
                            <div class="chart-frame">
                                <canvas id="dashboardOrdersChart" aria-label="Đơn hàng theo tháng"></canvas>
                            </div>
                        </div>
                    @endif

                    @if ($dashboardCharts['quoteStatuses'])
                        <div class="panel chart-panel">
                            <div class="panel-header">
                                <h3 class="panel-title">Báo giá theo trạng thái</h3>
                            </div>
                            <div class="chart-frame chart-frame-donut">
                                <canvas id="dashboardQuoteStatusChart" aria-label="Báo giá theo trạng thái"></canvas>
                            </div>
                        </div>
                    @endif

                    @if ($dashboardCharts['testDriveStatuses'])
                        <div class="panel chart-panel">
                            <div class="panel-header">
                                <h3 class="panel-title">Lái thử theo trạng thái</h3>
                            </div>
                            <div class="chart-frame chart-frame-donut">
                                <canvas id="dashboardTestDriveStatusChart" aria-label="Lái thử theo trạng thái"></canvas>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="dashboard-section">
            <div class="section-heading">
                <div>
                    <h2>Bảng dữ liệu nhanh</h2>
                    <p>Các danh sách được eager load và giới hạn số dòng để dashboard tải gọn.</p>
                </div>
            </div>

            <div class="panel-grid">
                @if ($canViewCars)
                    <div class="panel panel-wide">
                        <div class="panel-header">
                            <h3 class="panel-title">Xe mới cập nhật</h3>
                            <a href="{{ route('admin.cars.index') }}" class="panel-link">Xem tất cả</a>
                        </div>

                        @if ($recentCars->isEmpty())
                            <p class="empty-text">Chưa có dữ liệu xe.</p>
                        @else
                            <div class="table-wrap">
                                <table class="dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Tên xe</th>
                                            <th>Giá bán</th>
                                            <th>Tồn vật lý</th>
                                            <th>Đã giữ</th>
                                            <th>Có thể bán</th>
                                            <th>Trạng thái</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentCars as $car)
                                            @php
                                                $carStatus = (string) $car->status;
                                                $carStatusLabel = $carStatusLabels[$carStatus] ?? ($car->status ?: 'N/A');
                                            @endphp
                                            <tr>
                                                <td>
                                                    <div class="car-meta">
                                                        <span class="car-name">{{ $car->name }}</span>
                                                        <span class="car-brand">
                                                            {{ $car->modelInfo?->brand?->name ?? 'N/A' }}
                                                            · {{ $car->modelInfo?->name ?? 'N/A' }}
                                                            · Đời {{ $car->year ?? 'N/A' }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="money-cell">{{ number_format((float) $car->price, 0, ',', '.') }} đ</td>
                                                <td class="number-cell">{{ number_format($car->physical_stock) }}</td>
                                                <td class="number-cell is-reserved">{{ number_format($car->reserved_stock) }}</td>
                                                <td class="number-cell is-available">{{ number_format($car->available_stock) }}</td>
                                                <td>
                                                    <span class="status-badge car-status-{{ $carStatus }}">
                                                        {{ $carStatusLabel }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($canViewOrders)
                    <div class="panel panel-wide">
                        <div class="panel-header">
                            <h3 class="panel-title">Đơn hàng gần đây</h3>
                            <a href="{{ route('admin.orders.index') }}" class="panel-link">Xem tất cả</a>
                        </div>

                        @if ($recentOrders->isEmpty())
                            <p class="empty-text">Chưa có đơn hàng trong khoảng thời gian này.</p>
                        @else
                            <div class="table-wrap">
                                <table class="dashboard-table">
                                    <thead>
                                        <tr>
                                            <th>Mã đơn</th>
                                            <th>Khách hàng</th>
                                            <th>Tổng tiền</th>
                                            <th>Trạng thái đơn</th>
                                            <th>Giao xe</th>
                                            <th>Ngày tạo</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentOrders as $order)
                                            <tr>
                                                <td>
                                                    <a href="{{ route('admin.orders.show', $order->order_id) }}" class="code-pill">
                                                        {{ $order->display_code }}
                                                    </a>
                                                </td>
                                                <td>
                                                    <div class="main-text">{{ $order->user->name ?? 'N/A' }}</div>
                                                    <div class="sub-text">{{ $order->user->email ?? '' }}</div>
                                                </td>
                                                <td class="money-cell">{{ number_format((float) $order->total_price, 0, ',', '.') }} đ</td>
                                                <td>
                                                    <span class="status-badge {{ $order->status_badge_class }}">
                                                        {{ $order->status_label }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($order->delivery)
                                                        <span class="status-badge {{ $order->delivery->status_badge_class }}">
                                                            {{ $order->delivery->status_label }}
                                                        </span>
                                                    @else
                                                        <span class="muted">Chưa tạo</span>
                                                    @endif
                                                </td>
                                                <td class="date-cell">{{ $order->created_at?->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($canViewOrders)
                    <div class="panel">
                        <div class="panel-header">
                            <h3 class="panel-title">Lịch giao xe sắp tới</h3>
                        </div>

                        @if ($upcomingDeliveries->isEmpty())
                            <p class="empty-text">Chưa có lịch giao xe phù hợp.</p>
                        @else
                            <div class="compact-list">
                                @foreach ($upcomingDeliveries as $delivery)
                                    <div class="compact-item">
                                        <div>
                                            <a href="{{ route('admin.orders.show', $delivery->order_id) }}" class="compact-code">
                                                {{ $delivery->order?->display_code ?? ('DH' . str_pad((string) $delivery->order_id, 6, '0', STR_PAD_LEFT)) }}
                                            </a>
                                            <div class="main-text">{{ $delivery->order?->user?->name ?? 'N/A' }}</div>
                                            <div class="sub-text">{{ $delivery->car?->name ?? 'Chưa gán xe' }}</div>
                                        </div>
                                        <div class="compact-side">
                                            <span class="date-cell">{{ $delivery->expected_delivery_date?->format('d/m/Y H:i') ?? 'Chưa hẹn' }}</span>
                                            <span class="status-badge {{ $delivery->status_badge_class }}">
                                                {{ $delivery->status_label }}
                                            </span>
                                            <span class="sub-text">{{ $delivery->deliveryStaff?->name ?? 'Chưa gán NV' }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if ($canViewInventoryHistory)
                    <div class="panel">
                        <div class="panel-header">
                            <h3 class="panel-title">Hoạt động tồn kho gần đây</h3>
                            <a href="{{ route('admin.stock-movements.index') }}" class="panel-link">Xem tất cả</a>
                        </div>

                        @if ($recentStockMovements->isEmpty())
                            <p class="empty-text">Chưa có biến động tồn kho trong khoảng thời gian này.</p>
                        @else
                            <div class="stock-activity-list">
                                @foreach ($recentStockMovements as $movement)
                                    <div class="stock-activity-item">
                                        <div class="stock-activity-main">
                                            <span class="stock-activity-car">{{ $movement->car->name ?? 'Xe đã xóa' }}</span>
                                            <span class="stock-activity-meta">
                                                {{ \App\Models\StockMovement::labelFor($movement->action_type) }}
                                                · {{ $movement->user->name ?? 'Hệ thống' }}
                                                · {{ $movement->created_at?->format('d/m/Y H:i') }}
                                            </span>
                                            @if ($movement->reason || $movement->note)
                                                <span class="stock-activity-reason">{{ $movement->reason ?: $movement->note }}</span>
                                            @endif
                                        </div>
                                        <div class="stock-activity-delta {{ $movement->quantity_change >= 0 ? 'is-positive' : 'is-negative' }}">
                                            {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if ($canViewOrders)
                    <div class="panel">
                        <div class="panel-header">
                            <h3 class="panel-title">Top xe bán chạy</h3>
                        </div>

                        @if ($topSellingCars->isEmpty())
                            <p class="empty-text">Chưa có xe bán trong khoảng thời gian này.</p>
                        @else
                            <div class="rank-list">
                                @foreach ($topSellingCars as $index => $item)
                                    <div class="rank-item">
                                        <span class="rank-number">{{ $index + 1 }}</span>
                                        <div class="rank-main">
                                            <span>{{ $item->name }}</span>
                                            <small>{{ number_format((float) $item->sold_amount, 0, ',', '.') }} đ</small>
                                        </div>
                                        <strong>{{ number_format((int) $item->sold_quantity) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if ($canViewQuotes || $canViewTestDrives)
                    <div class="panel">
                        <div class="panel-header">
                            <h3 class="panel-title">Top xe được quan tâm</h3>
                        </div>

                        @if ($topInterestedCars->isEmpty())
                            <p class="empty-text">Chưa có lượt quan tâm trong khoảng thời gian này.</p>
                        @else
                            <div class="rank-list">
                                @foreach ($topInterestedCars as $index => $item)
                                    <div class="rank-item">
                                        <span class="rank-number">{{ $index + 1 }}</span>
                                        <div class="rank-main">
                                            <span>{{ $item['name'] }}</span>
                                            <small>
                                                {{ number_format($item['quote_count']) }} báo giá ·
                                                {{ number_format($item['test_drive_count']) }} lái thử
                                            </small>
                                        </div>
                                        <strong>{{ number_format($item['total']) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                <div class="panel">
                    <div class="panel-header">
                        <h3 class="panel-title">Lối tắt vận hành</h3>
                    </div>

                    <div class="action-list">
                        @can('cars.create')
                            <a href="{{ route('admin.cars.create') }}" class="action-btn">
                                <i class="fa-solid fa-plus"></i>
                                Thêm xe mới
                            </a>
                        @endcan

                        @can('customers.view')
                            <a href="{{ route('admin.customers.index') }}" class="action-btn">
                                <i class="fa-solid fa-address-book"></i>
                                Khách hàng CRM
                            </a>
                        @endcan

                        @can('quotes.view')
                            <a href="{{ route('admin.quotes.index') }}" class="action-btn">
                                <i class="fa-solid fa-file-invoice-dollar"></i>
                                Báo giá
                            </a>
                        @endcan

                        @can('orders.view')
                            <a href="{{ route('admin.orders.index') }}" class="action-btn">
                                <i class="fa-solid fa-receipt"></i>
                                Đơn hàng
                            </a>
                        @endcan

                        @can('test_drives.view')
                            <a href="{{ route('admin.test_drives.index') }}" class="action-btn">
                                <i class="fa-solid fa-calendar-check"></i>
                                Lịch lái thử
                            </a>
                        @endcan

                        @can('reports.view')
                            <a href="{{ route('admin.reports.sales') }}" class="action-btn">
                                <i class="fa-solid fa-chart-line"></i>
                                Báo cáo doanh số
                            </a>
                        @endcan

                        <a href="/" target="_blank" class="action-btn">
                            <i class="fa-solid fa-globe"></i>
                            Xem trang khách hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const payload = @json($dashboardCharts);

            if (!window.Chart || !payload) {
                return;
            }

            const rootStyles = getComputedStyle(document.documentElement);
            const textColor = rootStyles.getPropertyValue('--text').trim() || '#f8fafc';
            const mutedColor = rootStyles.getPropertyValue('--muted').trim() || '#94a3b8';
            const accentColor = rootStyles.getPropertyValue('--accent').trim() || '#c9a962';
            const gridColor = 'rgba(148, 163, 184, 0.16)';
            const currencyFormatter = new Intl.NumberFormat('vi-VN');

            Chart.defaults.color = mutedColor;
            Chart.defaults.font.family = "'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif";

            const baseOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: {
                            boxWidth: 12,
                            boxHeight: 12,
                            color: mutedColor,
                            usePointStyle: true,
                        },
                    },
                    tooltip: {
                        backgroundColor: '#0a0d12',
                        borderColor: 'rgba(201, 169, 98, 0.35)',
                        borderWidth: 1,
                        titleColor: textColor,
                        bodyColor: mutedColor,
                    },
                },
            };

            const renderCartesian = (id, type, chartData, datasetOptions, extraOptions = {}) => {
                const canvas = document.getElementById(id);

                if (!canvas || !chartData) {
                    return;
                }

                new Chart(canvas, {
                    type,
                    data: {
                        labels: chartData.labels || [],
                        datasets: [{
                            data: chartData.data || [],
                            borderWidth: 2,
                            borderRadius: 8,
                            tension: 0.35,
                            fill: false,
                            ...datasetOptions,
                        }],
                    },
                    options: {
                        ...baseOptions,
                        scales: {
                            x: {
                                grid: {
                                    color: gridColor,
                                },
                                ticks: {
                                    color: mutedColor,
                                },
                            },
                            y: {
                                beginAtZero: true,
                                grid: {
                                    color: gridColor,
                                },
                                ticks: {
                                    color: mutedColor,
                                    callback: (value) => currencyFormatter.format(value),
                                },
                            },
                        },
                        ...extraOptions,
                    },
                });
            };

            const renderDonut = (id, chartData, colors) => {
                const canvas = document.getElementById(id);

                if (!canvas || !chartData) {
                    return;
                }

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: chartData.labels || [],
                        datasets: [{
                            data: chartData.data || [],
                            backgroundColor: colors,
                            borderColor: '#0f131a',
                            borderWidth: 2,
                            hoverOffset: 4,
                        }],
                    },
                    options: {
                        ...baseOptions,
                        cutout: '64%',
                    },
                });
            };

            renderCartesian('dashboardRevenueChart', 'line', payload.revenue, {
                label: 'Doanh thu',
                borderColor: accentColor,
                backgroundColor: 'rgba(201, 169, 98, 0.18)',
                pointBackgroundColor: accentColor,
                pointBorderColor: '#0a0d12',
                pointRadius: 4,
                fill: true,
            });

            renderCartesian('dashboardOrdersChart', 'bar', payload.orders, {
                label: 'Đơn hàng',
                backgroundColor: 'rgba(96, 165, 250, 0.72)',
                borderColor: 'rgba(96, 165, 250, 1)',
            }, {
                scales: {
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            color: mutedColor,
                        },
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: gridColor,
                        },
                        ticks: {
                            precision: 0,
                            color: mutedColor,
                        },
                    },
                },
            });

            renderDonut('dashboardQuoteStatusChart', payload.quoteStatuses, [
                'rgba(148, 163, 184, 0.78)',
                'rgba(96, 165, 250, 0.78)',
                'rgba(52, 211, 153, 0.78)',
                'rgba(248, 113, 113, 0.78)',
                'rgba(251, 191, 36, 0.78)',
            ]);

            renderDonut('dashboardTestDriveStatusChart', payload.testDriveStatuses, [
                'rgba(251, 191, 36, 0.78)',
                'rgba(96, 165, 250, 0.78)',
                'rgba(248, 113, 113, 0.78)',
                'rgba(52, 211, 153, 0.78)',
            ]);
        })();
    </script>
@endpush
