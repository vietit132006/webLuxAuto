@extends('layouts.site')

@section('title', 'Bảng điều khiển Admin')

@section('content')
<style>
    .dashboard-header {
        margin-bottom: 2rem;
    }
    .dashboard-title {
        font-size: 1.8rem;
        font-weight: 700;
        margin: 0 0 0.5rem;
        color: var(--text);
    }
    .dashboard-subtitle {
        color: var(--muted);
        font-size: 0.95rem;
    }

    /* --- LƯỚI THỐNG KÊ (STAT CARDS) --- */
    .stat-grid {
        display: grid;
        grid-template-columns: repeat(1, 1fr);
        gap: 1.5rem;
        margin-bottom: 2.5rem;
    }
    @media (min-width: 768px) {
        .stat-grid { grid-template-columns: repeat(3, 1fr); }
    }
    .stat-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        border-color: var(--accent-dim);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        background: rgba(201, 169, 98, 0.1); /* Màu accent mờ */
    }
    .stat-info {
        flex: 1;
    }
    .stat-label {
        color: var(--muted);
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 0.3rem;
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 800;
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
        .main-grid { grid-template-columns: 2fr 1fr; }
    }

    /* Bảng xe mới thêm */
    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .panel-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    .panel-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin: 0;
    }
    .panel-link {
        color: var(--accent);
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 600;
    }
    .panel-link:hover { text-decoration: underline; }

    .recent-table {
        width: 100%;
        border-collapse: collapse;
    }
    .recent-table th, .recent-table td {
        padding: 1rem 0;
        border-bottom: 1px solid var(--border);
        text-align: left;
    }
    .recent-table tr:last-child td { border-bottom: none; }
    .car-meta { display: flex; flex-direction: column; gap: 4px; }
    .car-name { font-weight: 600; color: var(--text); }
    .car-brand { font-size: 0.8rem; color: var(--muted); }

    /* Hành động nhanh (Quick Actions) */
    .action-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }
    .action-btn {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border);
        border-radius: 8px;
        color: var(--text);
        text-decoration: none;
        font-weight: 600;
        transition: all 0.2s;
    }
    .action-btn:hover {
        background: var(--accent);
        color: #0c0f14;
        border-color: var(--accent);
    }
    .action-btn i { font-size: 1.2rem; }
</style>

<div class="wrap">
    <div class="dashboard-header">
        <h1 class="dashboard-title">Bảng điều khiển</h1>
        <p class="dashboard-subtitle">Chào mừng bạn quay lại hệ thống quản lý Lux Auto.</p>
    </div>

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-icon">🚘</div>
            <div class="stat-info">
                <div class="stat-label">Tổng số xe</div>
                <div class="stat-value">{{ number_format($totalCars) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-info">
                <div class="stat-label">Tổng xe tồn kho</div>
                <div class="stat-value">{{ number_format($totalStock) }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">🏷️</div>
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

            @if($recentCars->isEmpty())
                <p style="color: var(--muted); text-align: center; padding: 2rem 0;">Chưa có dữ liệu xe.</p>
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
                        @foreach($recentCars as $car)
                        <tr>
                            <td>
                                <div class="car-meta">
                                    <span class="car-name">{{ $car->name }}</span>
                                    <span class="car-brand">{{ $car->brand->name ?? 'N/A' }} • Đời {{ $car->year }}</span>
                                </div>
                            </td>
                            <td style="font-weight: 600; color: var(--accent);">
                                {{ number_format($car->price, 0, ',', '.') }} đ
                            </td>
                            <td>
                                @if($car->stock > 0)
                                    <span style="color: #4ade80; font-size: 0.85rem; font-weight: bold;">Còn hàng</span>
                                @else
                                    <span style="color: #f87171; font-size: 0.85rem; font-weight: bold;">Hết hàng</span>
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
                    <i>➕</i> Thêm xe mới
                </a>
                <a href="{{ route('admin.cars.index') }}" class="action-btn">
                    <i>📋</i> Quản lý danh sách xe
                </a>
                <a href="#" class="action-btn"> <i>🏢</i> Quản lý hãng xe
                </a>
                <a href="/" target="_blank" class="action-btn">
                    <i>🌐</i> Xem trang khách hàng
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
