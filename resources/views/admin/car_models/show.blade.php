@extends('layouts.admin')

@section('title', 'Chi tiết model xe')

@section('content')
    @include('admin.car_models._style')

    <div class="wrap model-wrap">
        <div class="model-header">
            <div>
                <h1 class="model-title">Chi tiết model xe</h1>
                <p class="model-subtitle">Thông tin kỹ thuật và các xe đang sử dụng model này.</p>
            </div>

            <div style="display:flex;gap:.75rem;flex-wrap:wrap;">
                <a href="{{ route('admin.car-models.index') }}" class="lux-btn-muted">← Danh sách</a>
                <a href="{{ route('admin.car-models.edit', $carModel->id) }}" class="lux-btn-primary">Sửa model</a>
            </div>
        </div>

        @if (session('success'))
            <div class="flash-alert flash-success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="flash-alert flash-error">{{ session('error') }}</div>
        @endif

        <div class="detail-grid">
            <div class="model-card detail-card">
                <h2 class="detail-title">{{ $carModel->name }}</h2>
                <div class="detail-brand">{{ $carModel->brand?->name ?? 'Chưa có hãng xe' }}</div>

                <div class="detail-list">
                    <div class="detail-row">
                        <span class="detail-label">Động cơ</span>
                        <span class="detail-value">{{ $carModel->engine ?? '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Nhiên liệu</span>
                        <span class="detail-value">{{ $carModel->fuel_type ?? '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Hộp số</span>
                        <span class="detail-value">{{ $carModel->transmission ?? '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Kiểu dáng</span>
                        <span class="detail-value">{{ $carModel->body_type ?? '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Dẫn động</span>
                        <span class="detail-value">{{ $carModel->drive_type ?? '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Số chỗ / Số cửa</span>
                        <span class="detail-value">
                            {{ $carModel->seats ? $carModel->seats . ' chỗ' : '-' }}
                            @if ($carModel->doors)
                                / {{ $carModel->doors }} cửa
                            @endif
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Xuất xứ</span>
                        <span class="detail-value">{{ $carModel->origin ?? '-' }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Ngày tạo</span>
                        <span class="detail-value">{{ $carModel->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                </div>
            </div>

            <div class="model-card detail-card">
                <h3 class="form-section-title" style="margin-bottom:1rem;">Tình trạng sử dụng</h3>

                <div style="display:flex;align-items:center;justify-content:space-between;gap:1rem;border:1px solid var(--border);border-radius:14px;padding:1.1rem;">
                    <div>
                        <div style="color:var(--muted);font-size:.85rem;">Xe đang dùng model này</div>
                        <div style="font-size:2rem;font-weight:900;color:var(--accent);">{{ $carModel->cars_count }}</div>
                    </div>

                    @if ($carModel->cars_count > 0)
                        <span class="spec-pill" style="color:#fbbf24;border-color:rgba(251,191,36,.25);background:rgba(251,191,36,.08);">Không thể xóa</span>
                    @else
                        <span class="spec-pill" style="color:#34d399;border-color:rgba(52,211,153,.25);background:rgba(52,211,153,.08);">Có thể xóa</span>
                    @endif
                </div>

                @if ($carModel->cars_count === 0)
                    <form action="{{ route('admin.car-models.destroy', $carModel->id) }}" method="POST"
                        style="margin-top:1rem;"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa model này không?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="lux-btn-danger" style="width:100%;">Xóa model này</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="model-card" style="margin-top:1.25rem;">
            <div style="padding:1.2rem 1.4rem;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;gap:1rem;">
                <h3 style="margin:0;color:var(--text);font-size:1.05rem;">Xe đang sử dụng model</h3>
                <span class="count-badge">{{ $carModel->cars_count }}</span>
            </div>

            @if ($cars->isEmpty())
                <div class="empty-state" style="padding:2.5rem 1.5rem;">Chưa có xe nào sử dụng model này.</div>
            @else
                <div class="table-responsive">
                    <table class="model-table" style="min-width:760px;">
                        <thead>
                            <tr>
                                <th>Tên xe</th>
                                <th>VIN</th>
                                <th>Biển số</th>
                                <th>Năm</th>
                                <th>Giá</th>
                                <th style="text-align:right;">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cars as $car)
                                <tr>
                                    <td style="color:var(--text);font-weight:800;">{{ $car->name }}</td>
                                    <td style="color:var(--muted);font-family:Monaco,Consolas,monospace;">{{ $car->vin ?? '-' }}</td>
                                    <td style="color:var(--muted);">{{ $car->license_plate ?? '-' }}</td>
                                    <td style="color:var(--muted);">{{ $car->year ?? '-' }}</td>
                                    <td style="color:var(--accent);font-weight:800;white-space:nowrap;">
                                        {{ number_format($car->price ?? 0, 0, ',', '.') }} VNĐ
                                    </td>
                                    <td style="text-align:right;">
                                        <a href="{{ route('admin.cars.show', $car->car_id) }}" class="lux-btn-secondary">Xem xe</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($carModel->cars_count > 8)
                    <div style="padding:1rem 1.4rem;color:var(--muted);border-top:1px solid var(--border);">
                        Chỉ hiển thị 8 xe gần nhất.
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
