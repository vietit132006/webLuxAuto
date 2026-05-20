@extends('layouts.admin')

@section('title', 'Bảng điều khiển Admin')

@section('content')

    {{-- Font Awesome Icon CDN --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        .dashboard-header {
            margin-bottom: 2rem;
        }

        .dashboard-title {
            font-size: 1.9rem;
            font-weight: 800;
            margin: 0 0 0.5rem;
            color: var(--text);
            letter-spacing: -0.5px;
        }

        .dashboard-subtitle {
            color: var(--muted);
            font-size: 0.95rem;
        }

        /* --- LƯỚI THỐNG KÊ --- */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(1, 1fr);
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        @media (min-width: 768px) {
            .stat-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .stat-card {
            background: linear-gradient(145deg, var(--surface), rgba(255, 255, 255, 0.025));
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
            transition: all 0.25s ease;
            box-shadow: 0 12px 30px -18px rgba(0, 0, 0, 0.65);
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: "";
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(201, 169, 98, 0.16), transparent 40%);
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            border-color: var(--accent-dim);
            box-shadow: 0 18px 40px -20px rgba(0, 0, 0, 0.85);
        }

        .stat-card:hover::before {
            opacity: 1;
        }

        .stat-icon {
            width: 58px;
            height: 58px;
            min-width: 58px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.45rem;
            color: var(--accent);
            background: rgba(201, 169, 98, 0.1);
            border: 1px solid rgba(201, 169, 98, 0.22);
            position: relative;
            z-index: 1;
        }

        .stat-info {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .stat-label {
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 0.45rem;
            font-weight: 700;
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 850;
            color: var(--text);
            line-height: 1;
        }

        /* --- KHU VỰC NỘI DUNG CHÍNH --- */
        .main-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 1024px) {
            .main-grid {
                grid-template-columns: 2fr 1fr;
            }
        }

        .panel {
            background: linear-gradient(145deg, var(--surface), rgba(255, 255, 255, 0.018));
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 1.5rem;
            box-shadow: 0 12px 32px -22px rgba(0, 0, 0, 0.8);
        }

        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .panel-title {
            font-size: 1.18rem;
            font-weight: 800;
            margin: 0;
            color: var(--text);
        }

        .panel-link {
            color: var(--accent);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 700;
            transition: opacity 0.2s ease;
        }

        .panel-link:hover {
            opacity: 0.8;
        }

        .recent-table {
            width: 100%;
            border-collapse: collapse;
        }

        .recent-table th {
            color: var(--muted);
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
        }

        .recent-table th,
        .recent-table td {
            padding: 1rem 0;
            border-bottom: 1px solid var(--border);
            text-align: left;
        }

        .recent-table tr:last-child td {
            border-bottom: none;
        }

        .recent-table tbody tr {
            transition: background 0.2s ease;
        }

        .recent-table tbody tr:hover {
            background: rgba(255, 255, 255, 0.025);
        }

        .car-meta {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .car-name {
            font-weight: 700;
            color: var(--text);
        }

        .car-brand {
            font-size: 0.82rem;
            color: var(--muted);
        }

        /* --- THAO TÁC NHANH --- */
        .action-list {
            display: flex;
            flex-direction: column;
            gap: 0.9rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.9rem;
            padding: 0.95rem 1rem;
            background: rgba(255, 255, 255, 0.025);
            border: 1px solid var(--border);
            border-radius: 12px;
            color: var(--text);
            text-decoration: none;
            font-weight: 700;
            transition: all 0.22s ease;
        }

        .action-btn i {
            width: 22px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: var(--accent);
            font-size: 1rem;
            transition: color 0.22s ease;
        }

        .action-btn:hover {
            background: var(--accent);
            color: #0c0f14;
            border-color: var(--accent);
            transform: translateX(4px);
            box-shadow: 0 10px 25px -15px rgba(201, 169, 98, 0.7);
        }

        .action-btn:hover i {
            color: #0c0f14;
        }

        .empty-text {
            color: var(--muted);
            text-align: center;
            padding: 2rem 0;
            margin: 0;
        }
    </style>

    <div class="wrap">
        <div class="dashboard-header">
            <h1 class="dashboard-title">Bảng điều khiển</h1>
            <p class="dashboard-subtitle">Chào mừng bạn quay lại hệ thống quản lý Lux Auto.</p>
        </div>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-car-side"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Tổng số xe</div>
                    <div class="stat-value">{{ number_format($totalCars) }}</div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-layer-group"></i>
                </div>

                <div class="stat-info">
                    <div class="stat-label">Tổng model xe</div>
                    <div class="stat-value">
                        {{ number_format($totalCarModels ?? 0) }}
                    </div>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fa-solid fa-tags"></i>
                </div>
                <div class="stat-info">
                    <div class="stat-label">Hãng xe</div>
                    <div class="stat-value">{{ number_format($totalBrands) }}</div>
                </div>
            </div>
        </div>

        <div class="main-grid">
            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Xe mới cập nhật</h2>
                    <a href="{{ route('admin.cars.index') }}" class="panel-link">Xem tất cả →</a>
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
                                                {{ $car->brand->name ?? 'N/A' }} • Đời {{ $car->year }}
                                            </span>
                                        </div>
                                    </td>
                                    <td style="font-weight: 700; color: var(--accent);">
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

            <div class="panel">
                <div class="panel-header">
                    <h2 class="panel-title">Thao tác nhanh</h2>
                </div>

                <div class="action-list">
                    <a href="{{ route('admin.cars.create') }}" class="action-btn">
                        <i class="fa-solid fa-plus"></i>
                        Thêm xe mới
                    </a>

                    <a href="{{ route('admin.cars.index') }}" class="action-btn">
                        <i class="fa-solid fa-list-check"></i>
                        Quản lý danh sách xe
                    </a>

                    <a href="{{ route('admin.reports.sales') }}" class="action-btn">
                        <i class="fa-solid fa-chart-line"></i>
                        Báo cáo doanh số
                    </a>

                    <a href="{{ route('admin.promotions') }}" class="action-btn">
                        <i class="fa-solid fa-percent"></i>
                        Nội dung khuyến mãi
                    </a>

                    <a href="{{ route('admin.brands.index') }}" class="action-btn">
                        <i class="fa-solid fa-building"></i>
                        Quản lý hãng xe
                    </a>

                    <a href="/" target="_blank" class="action-btn">
                        <i class="fa-solid fa-globe"></i>
                        Xem trang khách hàng
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
