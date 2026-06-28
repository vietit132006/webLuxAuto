@extends('layouts.admin')

@section('title', 'Quản lý xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-cars-index.css')
    @endif
@endpush


@section('content')

    <div class="wrap">
        <div class="header-actions">
            <div>
                <h1 class="page-title">Quản lý danh sách xe</h1>
                <p class="page-subtitle">Theo dõi nhanh thông tin quan trọng. Chi tiết ảnh, video và mô tả xem tại nút Xem.
                </p>
            </div>

            @if (auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                <div class="header-action-group">
                    <div class="excel-actions">
                        <form class="excel-import-form" method="post" action="{{ route('admin.cars.import') }}"
                            enctype="multipart/form-data">
                            @csrf
                            <input id="car-import-file" class="file-input-hidden" type="file" name="file"
                                accept=".xlsx,.xls,.csv" onchange="this.form.submit()">
                            <label for="car-import-file" class="lux-btn-secondary" title="Import Excel">
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 16.5V3.75m0 0 4.5 4.5M12 3.75l-4.5 4.5M3.75 16.5v2.25A2.25 2.25 0 0 0 6 21h12a2.25 2.25 0 0 0 2.25-2.25V16.5" />
                                </svg>
                                Import Excel
                            </label>
                        </form>

                        <a href="{{ route('admin.cars.export') }}" class="lux-btn-secondary" title="Export danh sách xe">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 3.75v12.75m0 0 4.5-4.5m-4.5 4.5-4.5-4.5M3.75 16.5v2.25A2.25 2.25 0 0 0 6 21h12a2.25 2.25 0 0 0 2.25-2.25V16.5" />
                            </svg>
                            Export danh sách xe
                        </a>

                        <a href="{{ route('admin.cars.inventory.export') }}" class="lux-btn-secondary"
                            title="Export báo cáo tồn kho">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 7.5 12 3l9 4.5-9 4.5L3 7.5Zm0 0V16.5l9 4.5 9-4.5V7.5M12 12v9" />
                            </svg>
                            Báo cáo tồn kho
                        </a>

                        <a href="{{ route('admin.cars.import.template') }}" class="lux-btn-secondary"
                            title="Tải file mẫu import">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5A3.375 3.375 0 0 0 10.125 2.25H8.25m.75 12 3 3m0 0 3-3m-3 3v-6m-4.5 9h9A2.25 2.25 0 0 0 18.75 18V9.75a2.25 2.25 0 0 0-.659-1.591l-5.25-5.25A2.25 2.25 0 0 0 11.25 2.25H7.5A2.25 2.25 0 0 0 5.25 4.5V18A2.25 2.25 0 0 0 7.5 20.25Z" />
                            </svg>
                            File mẫu
                        </a>
                    </div>

                    <a href="{{ route('admin.cars.create') }}" class="lux-btn-primary">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Thêm xe mới
                    </a>
                </div>
            @endif
        </div>

        @if (session('success'))
            <div id="success-alert" class="lux-flash-alert lux-flash-success">
                <div class="lux-flash-content">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>

                <button type="button" class="btn-close-alert" onclick="closeAlert('success-alert')" aria-label="Đóng">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div id="error-alert" class="lux-flash-alert lux-flash-error">
                <div class="lux-flash-content">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v3m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>

                <button type="button" class="btn-close-alert" onclick="closeAlert('error-alert')" aria-label="Đóng">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        @if ($errors->has('file'))
            <div id="import-error-alert" class="lux-flash-alert lux-flash-error">
                <div>
                    <div class="lux-flash-content">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v3m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z" />
                        </svg>
                        <span>File import có lỗi, chưa ghi dữ liệu vào kho.</span>
                    </div>
                    <ul class="import-error-list">
                        @foreach ($errors->get('file') as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>

                <button type="button" class="btn-close-alert" onclick="closeAlert('import-error-alert')" aria-label="Đóng">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        @endif

        <form class="search-bar" method="get" action="{{ route('admin.cars.index') }}">
            <div class="search-input-wrapper">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="search" name="q" value="{{ $search ?? '' }}"
                    placeholder="Tìm theo tên xe, VIN, biển số, mã nội bộ, vị trí..." autocomplete="off">
            </div>
            <button type="submit" class="btn-search">Tìm kiếm</button>
        </form>

        @if ($cars->isEmpty())
            <div class="empty-state">
                <svg class="admin-cars-index-inline-4" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Không có xe phù hợp. Thử bộ lọc khác hoặc <a class="admin-cars-index-inline-3" href="{{ route('admin.cars.index') }}">xóa tìm kiếm</a>.
            </div>
        @else
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Xe</th>
                            <th>Mã / VIN</th>
                            <th>Năm / Số km</th>
                            <th>Kho / Tồn / Màu sắc</th>
                            <th>Giá</th>
                            <th>Trạng thái</th>
                            <th class="admin-cars-index-inline-2" width="230">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($cars as $car)
                            @php
                                $statusClass = match ((int) $car->status) {
                                    2 => 'badge-deposit',
                                    3 => 'badge-sold',
                                    default => 'badge-available',
                                };

                                $statusText = match ((int) $car->status) {
                                    2 => 'Đã cọc',
                                    3 => 'Đã bán',
                                    default => 'Sẵn sàng',
                                };

                                $conditionText = match ($car->vehicle_condition ?? 'new') {
                                    'used' => 'Cũ',
                                    'display' => 'Trưng bày',
                                    'test_drive' => 'Lái thử',
                                    default => 'Mới',
                                };

                                $conditionClass = match ($car->vehicle_condition ?? 'new') {
                                    'used' => 'badge-condition-used',
                                    'display' => 'badge-condition-display',
                                    'test_drive' => 'badge-condition-test',
                                    default => 'badge-condition-new',
                                };

                                $stockInDate = $car->stock_in_date?->format('d/m/Y');
                                $onRoadDate = $car->on_road_date?->format('d/m/Y');
                                $listPrice = $car->list_price ?? $car->price;
                                $salePrice = $car->sale_price;
                                $brandName = $car->carModel?->brand?->name ?? null;
                                $modelName = $car->carModel?->name ?? null;
                                $physicalStock = $car->physicalStock();
                                $reservedStock = $car->reservedStock();
                                $availableStock = $car->saleableStock();
                                $inventoryBadgeText = null;
                                $inventoryBadgeClass = null;

                                if ($car->isSaleBlockedByStatus()) {
                                    $inventoryBadgeText = 'Đã khóa bán';
                                    $inventoryBadgeClass = 'badge-sold';
                                } elseif ($physicalStock <= 0) {
                                    $inventoryBadgeText = 'Hết hàng';
                                    $inventoryBadgeClass = 'badge-sold';
                                } elseif ($availableStock <= 0) {
                                    $inventoryBadgeText = 'Đã giữ hết';
                                    $inventoryBadgeClass = 'badge-deposit';
                                }
                            @endphp
                            <tr>
                                <td>
                                    <div class="car-cell">
                                        @if ($car->image)
                                            <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->name }}"
                                                class="table-img">
                                        @else
                                            <span class="no-image">No Image</span>
                                        @endif

                                        <div>
                                            <span class="car-name">{{ $car->name }}</span>
                                            <span class="car-model">
                                                {{ $brandName ? $brandName . ' - ' : '' }}{{ $modelName ?? 'Chưa gán dòng xe' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                <td data-label="Mã / VIN">
                                    <span class="info-main">Mã NB: {{ $car->internal_code ?: 'Chưa nhập' }}</span>
                                    <span class="info-sub">VIN: {{ $car->vin ?? 'Chưa nhập VIN' }}</span>
                                    <span class="info-sub">Biển số: {{ $car->license_plate ?: 'Chưa có' }}</span>
                                </td>

                                <td data-label="Năm / Số km">
                                    <span class="info-main">{{ $car->year }}</span>
                                    <span class="info-sub">{{ number_format($car->mileage_km ?? 0, 0, ',', '.') }}
                                        km</span>
                                    <span class="info-sub">Nhập kho: {{ $stockInDate ?: 'Chưa nhập' }}</span>
                                    <span class="info-sub">Lăn bánh: {{ $onRoadDate ?: 'Chưa nhập' }}</span>
                                </td>

                                <td data-label="Kho / Tồn / Màu sắc">
                                    <span class="info-main">Vị trí: {{ $car->current_location ?: 'Chưa cập nhật' }}</span>
                                    <span class="info-sub">Tồn vật lý: {{ number_format($physicalStock, 0, ',', '.') }}</span>
                                    <span class="info-sub">Đã giữ: {{ number_format($reservedStock, 0, ',', '.') }}</span>
                                    <span class="info-sub">Khả dụng: {{ number_format($availableStock, 0, ',', '.') }}</span>
                                    @if ($inventoryBadgeText)
                                        <span class="lux-badge {{ $inventoryBadgeClass }} inventory-stock-badge">{{ $inventoryBadgeText }}</span>
                                    @endif
                                    <span class="info-main">Ngoại thất: {{ $car->color ?: '-' }}</span>
                                    <span class="info-sub">Nội thất: {{ $car->interior_color ?: '-' }}</span>
                                </td>

                                <td data-label="Giá">
                                    <span class="price-text">Niêm yết: {{ number_format($listPrice, 0, ',', '.') }} VNĐ</span>
                                    <span class="info-sub">Khuyến mãi: {{ $salePrice !== null ? number_format($salePrice, 0, ',', '.') . ' VNĐ' : 'Chưa áp dụng' }}</span>
                                    <span class="info-sub">Lăn bánh: {{ $car->estimated_rolling_price !== null ? number_format($car->estimated_rolling_price, 0, ',', '.') . ' VNĐ' : 'Chưa nhập' }}</span>
                                </td>

                                <td data-label="Trạng thái">
                                    <div class="status-stack">
                                        <span class="lux-badge {{ $conditionClass }}">{{ $conditionText }}</span>
                                        <span class="lux-badge {{ $statusClass }}">{{ $statusText }}</span>
                                        @if ((int) $car->is_featured === 1)
                                            <span class="lux-badge badge-featured">Nổi bật</span>
                                        @else
                                            <span class="lux-badge badge-normal">Thường</span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="lux-action-btns">
                                        <a href="{{ route('admin.cars.show', $car->car_id) }}"
                                            class="lux-btn-action lux-view" title="Xem chi tiết">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                            </svg>
                                            <span>Xem</span>
                                        </a>

                                        @if (auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                                            <a href="{{ route('admin.cars.edit', $car->car_id) }}"
                                                class="lux-btn-action lux-edit" title="Chỉnh sửa">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                                </svg>
                                                <span>Sửa</span>
                                            </a>

                                            <form class="admin-cars-index-inline-1" action="{{ route('admin.cars.destroy', $car->car_id) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc chắn muốn xóa chiếc xe này không? Hành động này không thể hoàn tác!');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="lux-btn-action lux-delete" title="Xóa xe">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                        stroke-width="1.5">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                                    </svg>
                                                    <span>Xóa</span>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($cars instanceof \Illuminate\Pagination\LengthAwarePaginator && $cars->hasPages())
                <div class="pagination-wrap">
                    {{ $cars->appends(request()->query())->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
