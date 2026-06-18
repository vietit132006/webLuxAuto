@extends('layouts.admin')

@section('title', 'Chi tiết model xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-car-models-show.css')
    @endif
@endpush


@section('content')
    @include('admin.car_models._style')

    <div class="wrap model-wrap">
        <div class="model-header">
            <div>
                <h1 class="model-title">Chi tiết model xe</h1>
                <p class="model-subtitle">Thông tin kỹ thuật và các xe đang sử dụng model này.</p>
            </div>

            <div class="admin-car-models-show-inline-20">
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
                <h3 class="form-section-title admin-car-models-show-inline-19">Tình trạng sử dụng</h3>

                <div class="admin-car-models-show-inline-18">
                    <div>
                        <div class="admin-car-models-show-inline-17">Xe đang dùng model này</div>
                        <div class="admin-car-models-show-inline-16">{{ $carModel->cars_count }}</div>
                    </div>

                    @if ($carModel->cars_count > 0)
                        <span class="spec-pill admin-car-models-show-inline-15">Không thể xóa</span>
                    @else
                        <span class="spec-pill admin-car-models-show-inline-14">Có thể xóa</span>
                    @endif
                </div>

                @if ($carModel->cars_count === 0)
                    <form class="admin-car-models-show-inline-13" action="{{ route('admin.car-models.destroy', $carModel->id) }}" method="POST"
                        onsubmit="return confirm('Bạn có chắc chắn muốn xóa model này không?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="lux-btn-danger admin-car-models-show-inline-12">Xóa model này</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="model-card admin-car-models-show-inline-11">
            <div class="admin-car-models-show-inline-10">
                <h3 class="admin-car-models-show-inline-9">Xe đang sử dụng model</h3>
                <span class="count-badge">{{ $carModel->cars_count }}</span>
            </div>

            @if ($cars->isEmpty())
                <div class="empty-state admin-car-models-show-inline-8">Chưa có xe nào sử dụng model này.</div>
            @else
                <div class="table-responsive">
                    <table class="model-table admin-car-models-show-inline-7">
                        <thead>
                            <tr>
                                <th>Tên xe</th>
                                <th>VIN</th>
                                <th>Biển số</th>
                                <th>Năm</th>
                                <th>Giá</th>
                                <th class="admin-car-models-show-inline-2">Chi tiết</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cars as $car)
                                <tr>
                                    <td class="admin-car-models-show-inline-6">{{ $car->name }}</td>
                                    <td class="admin-car-models-show-inline-5">{{ $car->vin ?? '-' }}</td>
                                    <td class="admin-car-models-show-inline-4">{{ $car->license_plate ?? '-' }}</td>
                                    <td class="admin-car-models-show-inline-4">{{ $car->year ?? '-' }}</td>
                                    <td class="admin-car-models-show-inline-3">
                                        {{ number_format($car->price ?? 0, 0, ',', '.') }} VNĐ
                                    </td>
                                    <td class="admin-car-models-show-inline-2">
                                        <a href="{{ route('admin.cars.show', $car->car_id) }}" class="lux-btn-secondary">Xem xe</a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($carModel->cars_count > 8)
                    <div class="admin-car-models-show-inline-1">
                        Chỉ hiển thị 8 xe gần nhất.
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection