@extends('layouts.admin')

@section('title', 'Quản lý xe')

@section('content')
    <style>
        /* --- HEADER & TIÊU ĐỀ --- */
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .page-title {
            margin: 0;
            font-size: 1.8rem;
            font-weight: 800;
            color: var(--text);
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
        }

        .page-subtitle {
            margin: 0.35rem 0 0;
            color: var(--muted);
            font-size: 0.95rem;
        }

        /* --- NÚT BẤM CHÍNH (PRIMARY BUTTON) --- */
        .lux-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--accent), #e4d08a);
            color: #000;
            border: none;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 15px -3px rgba(201, 169, 98, 0.4);
        }

        .lux-btn-primary svg {
            width: 18px;
            height: 18px;
            transition: transform 0.3s ease;
        }

        .lux-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px -3px rgba(201, 169, 98, 0.6), 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        .lux-btn-primary:hover svg {
            transform: rotate(90deg);
        }

        .lux-btn-primary:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px -3px rgba(201, 169, 98, 0.4);
        }

        /* --- THANH TÌM KIẾM (SEARCH BAR) --- */
        .search-bar {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .search-input-wrapper {
            position: relative;
            flex: 1;
            min-width: 250px;
        }

        .search-input-wrapper svg {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            color: var(--muted);
            transition: color 0.3s ease;
        }

        .search-bar input[type="search"] {
            width: 100%;
            padding: 0.7rem 1rem 0.7rem 2.5rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: rgba(0, 0, 0, 0.2);
            color: var(--text);
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--accent);
            background: var(--surface);
            box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15), inset 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .search-bar input:focus+svg,
        .search-input-wrapper:focus-within svg {
            color: var(--accent);
        }

        .btn-search {
            background: var(--surface);
            color: var(--text);
            border: 1px solid var(--border);
            padding: 0.7rem 1.2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.3s ease;
        }

        .btn-search:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: var(--muted);
            color: #fff;
        }

        /* --- THÔNG BÁO (FLASH ALERT) --- */
        .lux-flash-alert {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #34d399;
            padding: 1rem 1.5rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 8px 20px -5px rgba(16, 185, 129, 0.15);
            transition: opacity 0.5s ease, transform 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .lux-flash-alert.hide {
            opacity: 0;
            transform: translateY(-15px) scale(0.98);
            pointer-events: none;
        }

        .lux-flash-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .lux-flash-content svg {
            width: 22px;
            height: 22px;
            filter: drop-shadow(0 0 5px rgba(52, 211, 153, 0.5));
        }

        .btn-close-alert {
            background: none;
            border: none;
            color: rgba(52, 211, 153, 0.6);
            cursor: pointer;
            padding: 4px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
        }

        .btn-close-alert svg {
            width: 20px;
            height: 20px;
        }

        .btn-close-alert:hover {
            background: rgba(52, 211, 153, 0.15);
            color: #34d399;
            transform: rotate(90deg);
        }

        /* --- BẢNG ADMIN (TABLE) --- */
        .table-responsive {
            overflow-x: auto;
            border-radius: 14px;
            border: 1px solid var(--border);
            background: linear-gradient(145deg, var(--surface), #0f141a);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.5);
        }

        .admin-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            min-width: 980px;
        }

        .admin-table th,
        .admin-table td {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.04);
            vertical-align: middle;
        }

        .admin-table th {
            background: rgba(0, 0, 0, 0.32);
            color: var(--muted);
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-weight: 800;
            white-space: nowrap;
        }

        .admin-table tr:last-child td {
            border-bottom: none;
        }

        .admin-table tr {
            transition: background 0.2s ease;
        }

        .admin-table tr:hover {
            background: rgba(255, 255, 255, 0.035);
        }

        .car-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 260px;
        }

        .table-img {
            width: 82px;
            height: 54px;
            object-fit: cover;
            border-radius: 10px;
            background: #000;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
            flex-shrink: 0;
        }

        .no-image {
            width: 82px;
            height: 54px;
            border-radius: 10px;
            border: 1px dashed rgba(255, 255, 255, 0.16);
            color: var(--muted);
            background: rgba(0, 0, 0, 0.22);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.72rem;
            flex-shrink: 0;
        }

        .car-name {
            display: block;
            color: var(--text);
            font-weight: 800;
            font-size: 1rem;
            line-height: 1.25;
            margin-bottom: 4px;
        }

        .car-model {
            display: block;
            color: var(--muted);
            font-size: 0.82rem;
            line-height: 1.35;
        }

        .info-main {
            display: block;
            color: var(--text);
            font-weight: 700;
            white-space: nowrap;
        }

        .info-sub {
            display: block;
            margin-top: 4px;
            color: var(--muted);
            font-size: 0.82rem;
            white-space: nowrap;
        }

        .price-text {
            color: var(--accent);
            font-weight: 900;
            white-space: nowrap;
        }

        .status-stack {
            display: flex;
            align-items: center;
            gap: 7px;
            flex-wrap: wrap;
            min-width: 150px;
        }

        .lux-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 5px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 800;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .badge-available {
            background: rgba(16, 185, 129, 0.1);
            border-color: rgba(16, 185, 129, 0.32);
            color: #34d399;
        }

        .badge-deposit {
            background: rgba(245, 158, 11, 0.1);
            border-color: rgba(245, 158, 11, 0.32);
            color: #fbbf24;
        }

        .badge-sold {
            background: rgba(239, 68, 68, 0.1);
            border-color: rgba(239, 68, 68, 0.32);
            color: #f87171;
        }

        .badge-featured {
            background: rgba(201, 169, 98, 0.12);
            border-color: rgba(201, 169, 98, 0.35);
            color: var(--accent);
        }

        .badge-normal {
            background: rgba(148, 163, 184, 0.08);
            border-color: rgba(148, 163, 184, 0.2);
            color: var(--muted);
        }

        /* --- NÚT HÀNH ĐỘNG (ACTION BUTTONS) --- */
        .lux-action-btns {
            display: flex;
            align-items: center;
            gap: 8px;
            justify-content: flex-end;
            flex-wrap: nowrap;
        }

        .lux-btn-action {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(0, 0, 0, 0.25);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--muted);
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-family: inherit;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .lux-btn-action svg {
            width: 16px;
            height: 16px;
            transition: transform 0.3s ease;
        }

        .lux-btn-action:hover {
            transform: translateY(-2px);
            color: var(--text);
        }

        .lux-btn-action:hover svg {
            transform: scale(1.1);
        }

        .lux-view:hover {
            background: rgba(56, 189, 248, 0.08);
            border-color: rgba(56, 189, 248, 0.5);
            color: #38bdf8;
            box-shadow: 0 4px 12px rgba(56, 189, 248, 0.15);
        }

        .lux-edit:hover {
            background: rgba(201, 169, 98, 0.08);
            border-color: rgba(201, 169, 98, 0.5);
            color: var(--accent);
            box-shadow: 0 4px 12px rgba(201, 169, 98, 0.15);
        }

        .lux-delete:hover {
            background: rgba(239, 68, 68, 0.08);
            border-color: rgba(239, 68, 68, 0.5);
            color: #ef4444;
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.15);
        }

        @media (max-width: 768px) {
            .lux-btn-action span {
                display: none;
            }

            .lux-btn-action {
                padding: 8px;
            }
        }

        /* --- PHÂN TRANG & TRỐNG --- */
        .pagination-wrap {
            margin-top: 2rem;
            display: flex;
            justify-content: center;
        }

        .empty-state {
            padding: 4rem 2rem;
            text-align: center;
            color: var(--muted);
            border: 1px dashed var(--border);
            border-radius: 12px;
            background: rgba(0, 0, 0, 0.1);
        }
    </style>

    <div class="wrap">
        <div class="header-actions">
            <div>
                <h1 class="page-title">Quản lý danh sách xe</h1>
                <p class="page-subtitle">Theo dõi nhanh thông tin quan trọng. Chi tiết ảnh, video và mô tả xem tại nút Xem.
                </p>
            </div>

            @if (auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                <a href="{{ route('admin.cars.create') }}" class="lux-btn-primary">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Thêm xe mới
                </a>
            @endif
        </div>

        @if (session('success'))
            <div id="success-alert" class="lux-flash-alert">
                <div class="lux-flash-content">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
                <button type="button" class="btn-close-alert" onclick="closeAlert()" aria-label="Đóng">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <script>
                function closeAlert() {
                    const alertBox = document.getElementById('success-alert');
                    if (alertBox) {
                        alertBox.classList.add('hide');
                        setTimeout(() => {
                            alertBox.remove();
                        }, 500);
                    }
                }
                setTimeout(() => {
                    closeAlert();
                }, 3000);
            </script>
        @endif

        <form class="search-bar" method="get" action="{{ route('admin.cars.index') }}">
            <div class="search-input-wrapper">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="search" name="q" value="{{ $search ?? '' }}"
                    placeholder="Tìm theo tên xe, VIN, biển số..." autocomplete="off">
            </div>
            <button type="submit" class="btn-search">Tìm kiếm</button>
        </form>

        @if ($cars->isEmpty())
            <div class="empty-state">
                <svg style="width: 48px; height: 48px; margin: 0 auto 15px; opacity: 0.3;" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                Không có xe phù hợp. Thử bộ lọc khác hoặc <a href="{{ route('admin.cars.index') }}"
                    style="color: var(--accent); font-weight: bold;">xóa tìm kiếm</a>.
            </div>
        @else
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Xe</th>
                            <th>VIN / Biển số</th>
                            <th>Năm / Số km</th>
                            <th>Màu sắc</th>
                            <th>Giá bán</th>
                            <th>Trạng thái</th>
                            <th width="230" style="text-align: right;">Hành động</th>
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

                                $brandName = $car->carModel?->brand?->name ?? null;
                                $modelName = $car->carModel?->name ?? null;
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

                                <td>
                                    <span class="info-main">{{ $car->vin ?? 'Chưa nhập VIN' }}</span>
                                    <span class="info-sub">Biển số: {{ $car->license_plate ?: 'Chưa có' }}</span>
                                </td>

                                <td>
                                    <span class="info-main">{{ $car->year }}</span>
                                    <span class="info-sub">{{ number_format($car->mileage_km ?? 0, 0, ',', '.') }}
                                        km</span>
                                </td>

                                <td>
                                    <span class="info-main">Ngoại thất: {{ $car->color ?: '-' }}</span>
                                    <span class="info-sub">Nội thất: {{ $car->interior_color ?: '-' }}</span>
                                </td>

                                <td>
                                    <span class="price-text">{{ number_format($car->price, 0, ',', '.') }} VNĐ</span>
                                </td>

                                <td>
                                    <div class="status-stack">
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

                                            <form action="{{ route('admin.cars.destroy', $car->car_id) }}" method="POST"
                                                style="margin: 0;"
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
