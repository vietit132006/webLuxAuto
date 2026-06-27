@extends('layouts.admin')

@section('title', 'Bảng điều khiển Admin')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-cars-dashboard.css')
    @endif
@endpush

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <div class="wrap">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Bảng điều khiển</h1>
            <p class="dashboard-subtitle">Chào mừng bạn quay lại hệ thống quản lý Lux Auto.</p>
        </div>

        <div class="stat-grid">
            @if($canViewCars)
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-car-side"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Tổng số xe</div>
                        <div class="stat-value">{{ number_format($totalCars ?? 0) }}</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>

                    <div class="stat-info">
                        <div class="stat-label">Tổng model xe</div>
                        <div class="stat-value">{{ number_format($totalCarModels ?? 0) }}</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-tags"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Hãng xe</div>
                        <div class="stat-value">{{ number_format($totalBrands ?? 0) }}</div>
                    </div>
                </div>
            @endif

            @if($canViewInventory)
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Tổng tồn kho</div>
                        <div class="stat-value">{{ number_format($totalInventoryUnits ?? 0) }}</div>
                    </div>
                </div>
            @endif

            @if($canViewReports)
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Doanh thu hoàn tất</div>
                        <div class="stat-value">{{ number_format($totalRevenue ?? 0, 0, ',', '.') }} đ</div>
                    </div>
                </div>
            @endif

            @if($canViewTestDrives && $testDriveStats)
                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Tổng lịch lái</div>
                        <div class="stat-value">{{ number_format($testDriveStats['total'] ?? 0) }}</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Hoàn thành</div>
                        <div class="stat-value">{{ number_format($testDriveStats['completed'] ?? 0) }}</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-label">Chuyển đổi đơn hàng</div>
                        <div class="stat-value">{{ number_format($testDriveStats['conversion_rate'] ?? 0, 1) }}%</div>
                    </div>
                </div>
            @endif
        </div>

        <div class="main-grid">
            @if($canViewCars)
                <div class="panel">
                    <div class="panel-header">
                        <h2 class="panel-title">Xe mới cập nhật</h2>
                        <a href="{{ route('admin.cars.index') }}" class="panel-link">Xem tất cả</a>
                    </div>

                    @if ($recentCars->isEmpty())
                        <p class="empty-text">Chưa có dữ liệu xe.</p>
                    @else
                        <table class="recent-table">
                            <thead>
                                <tr>
                                    <th>Mẫu xe</th>
                                    <th>Giá bán</th>
                                    <th>Trạng thái</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentCars as $car)
                                    <tr>
                                        <td>
                                            <div class="car-meta">
                                                <span class="car-name">{{ $car->name }}</span>
                                                <span class="car-brand">
                                                    {{ $car->brand->name ?? 'N/A' }} · Đời {{ $car->year }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="admin-cars-dashboard-inline-1">
                                            {{ number_format($car->price, 0, ',', '.') }} đ
                                        </td>
                                        <td>
                                            @if ($car->is_available)
                                                <span class="badge badge-success">Còn hàng</span>
                                            @else
                                                <span class="badge badge-danger">Hết hàng</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            @endif

            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Thao tác nhanh</h2>
                </div>

                <div class="action-list">
                    @can('cars.create')
                        <a href="{{ route('admin.cars.create') }}" class="action-btn">
                            <i class="fa-solid fa-plus"></i>
                            Thêm xe mới
                        </a>
                    @endcan

                    @can('cars.view')
                        <a href="{{ route('admin.cars.index') }}" class="action-btn">
                            <i class="fa-solid fa-list-check"></i>
                            Quản lý danh sách xe
                        </a>
                    @endcan

                    @can('reports.view')
                        <a href="{{ route('admin.reports.sales') }}" class="action-btn">
                            <i class="fa-solid fa-chart-line"></i>
                            Báo cáo doanh số
                        </a>
                    @endcan

                    @can('promotions.view')
                        <a href="{{ route('admin.promotions') }}" class="action-btn">
                            <i class="fa-solid fa-percent"></i>
                            Nội dung khuyến mãi
                        </a>
                    @endcan

                    @can('news.view')
                        <a href="{{ route('admin.news.index') }}" class="action-btn">
                            <i class="fa-solid fa-newspaper"></i>
                            Tin tức
                        </a>
                    @endcan

                    @can('reviews.view')
                        <a href="{{ route('admin.reports.reviews') }}" class="action-btn">
                            <i class="fa-solid fa-star"></i>
                            Đánh giá
                        </a>
                    @endcan

                    @can('test_drives.view')
                        <a href="{{ route('admin.test_drives.index') }}" class="action-btn">
                            <i class="fa-solid fa-calendar-check"></i>
                            Lịch lái thử
                        </a>
                    @endcan

                    @can('roles.view')
                        <a href="{{ route('admin.roles.index') }}" class="action-btn">
                            <i class="fa-solid fa-shield-halved"></i>
                            Vai trò
                        </a>
                    @endcan

                    <a href="/" target="_blank" class="action-btn">
                        <i class="fa-solid fa-globe"></i>
                        Xem trang khách hàng
                    </a>
                </div>
            </div>
        </div>

        @if($canViewInventoryHistory)
            <div class="panel stock-activity-panel">
                <div class="panel-header">
                    <h2 class="panel-title">Hoạt động tồn kho gần đây</h2>
                    <a href="{{ route('admin.stock-movements.index') }}" class="panel-link">Xem tất cả</a>
                </div>

                @if (($recentStockMovements ?? collect())->isEmpty())
                    <p class="empty-text">Chưa có biến động tồn kho.</p>
                @else
                    <div class="stock-activity-list">
                        @foreach ($recentStockMovements as $movement)
                            <div class="stock-activity-item">
                                <div class="stock-activity-main">
                                    <span class="stock-activity-car">{{ $movement->car->name ?? 'Xe đã xóa' }}</span>
                                    <span class="stock-activity-meta">
                                        {{ $movement->created_at?->format('d/m/Y H:i') }}
                                    </span>
                                </div>
                                <div class="stock-activity-delta {{ $movement->quantity_change >= 0 ? 'is-positive' : 'is-negative' }}">
                                    {{ $movement->quantity_change > 0 ? '+' : '' }}{{ $movement->quantity_change }}
                                    <span>({{ $movement->action_type }})</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
