@extends('layouts.admin')

@section('title', $car->title)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-cars-show.css')
    @endif
@endpush


@section('content')


    <div class="car-detail-wrap">
        @php
            $conditionText = match ($car->vehicle_condition ?? 'new') {
                'used' => 'Xe cũ',
                'display' => 'Xe trưng bày',
                'test_drive' => 'Xe lái thử',
                default => 'Xe mới',
            };

            $conditionClass = match ($car->vehicle_condition ?? 'new') {
                'used' => 'badge-used',
                'display' => 'badge-display',
                'test_drive' => 'badge-test-drive',
                default => 'badge-new',
            };

            $statusText = match ((int) $car->status) {
                2 => 'Đã đặt cọc',
                3 => 'Đã bán',
                default => 'Sẵn sàng',
            };

            $statusClass = match ((int) $car->status) {
                2 => 'reserved',
                3 => 'sold',
                default => '',
            };

            $listPrice = $car->list_price ?? $car->price;
            $salePrice = $car->sale_price;
            $actualPrice = $salePrice ?? $listPrice;
            $formatMoney = fn ($value) => number_format((int) ($value ?? 0)) . ' ₫';
            $physicalStock = $car->physicalStock();
            $reservedStock = $car->reservedStock();
            $availableStock = $car->saleableStock();
            $activeReservations = $car->activeStockReservations ?? collect();
        @endphp

        <!-- HEADER -->
        <div class="detail-header">
            <div class="header-left">
                <div class="breadcrumb">
                    <a class="admin-cars-show-inline-5" href="{{ route('admin.cars.index') }}"
                        onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
                        <svg class="admin-cars-show-inline-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.cars.index') }}">Quản lý xe</a>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span>{{ $car->brand->name ?? 'N/A' }}</span>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="admin-cars-show-inline-3">{{ $car->title }}</span>
                </div>

                <h1 class="car-title">{{ $car->title }}</h1>

                <div class="car-subtitle">
                    @if ($car->carModel)
                        <span>
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            {{ $car->carModel->name ?? 'N/A' }}
                        </span>
                    @endif
                    <span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $car->year ?? 'N/A' }}
                    </span>
                    <span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        {{ number_format($car->mileage_km ?? 0) }} km
                    </span>
                </div>

                <div class="badges">
                    <span class="badge {{ $conditionClass }}">{{ $conditionText }}</span>

                    @if ($car->is_featured)
                        <span class="badge badge-featured">Nổi bật</span>
                    @endif

                    <span class="badge badge-status {{ $statusClass }}">{{ $statusText }}</span>
                </div>
            </div>

            <div class="header-right">
                <div class="price-tag">{{ $formatMoney($actualPrice) }}</div>
                <div class="price-note">Giá bán thực tế</div>
                <div class="price-note">Niêm yết: <strong>{{ $formatMoney($listPrice) }}</strong></div>
                @if ($car->estimated_rolling_price !== null)
                    <div class="price-note">Lăn bánh dự kiến: <strong>{{ $formatMoney($car->estimated_rolling_price) }}</strong></div>
                @endif
            </div>
        </div>

        <!-- MAIN GRID -->
        <div class="detail-grid">

            <!-- LEFT: GALLERY -->
            <div class="gallery-section">
                @php
                    $galleryImages = $car->images ?? collect();
                    $imageUrls = [];

                    if (!empty($car->image)) {
                        $imageUrls[] = asset('storage/' . $car->image);
                    }

                    foreach ($galleryImages as $img) {
                        if (!empty($img->image_path)) {
                            $imageUrls[] = asset('storage/' . $img->image_path);
                        }
                    }

                    $mainImageUrl = $imageUrls[0] ?? null;
                @endphp
                <div class="main-image-container">
                    @if ($mainImageUrl)
                        <img src="{{ $mainImageUrl }}" class="main-image" id="mainImage" onclick="openLightbox(0)">
                    @else
                        <div class="admin-cars-show-inline-2">
                            Chưa có ảnh
                        </div>
                    @endif

                    <div class="image-counter">
                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ count($imageUrls) }} ảnh
                    </div>
                </div>

                @if (count($imageUrls) > 1)
                    <div class="thumbnail-grid">
                        @foreach ($imageUrls as $index => $url)
                            <div class="thumbnail {{ $index === 0 ? 'active' : '' }}"
                                onclick="changeImage('{{ $url }}', this, {{ $index }})">
                                <img src="{{ $url }}" alt="Image {{ $index + 1 }}">
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- VIDEO -->
                @if ($car->video_file || $car->video_url)
                    <div class="video-container">
                        <h3 class="admin-cars-show-inline-1">

                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round">
                                <polygon points="23 7 16 12 23 17 23 7"></polygon>
                                <rect x="1" y="5" width="15" height="14" rx="2" ry="2"></rect>
                            </svg>

                            Video xe
                        </h3>
                        <div class="video-wrapper">
                            @if ($car->video_file)
                                <video controls>
                                    <source src="{{ asset('storage/' . $car->video_file) }}" type="video/mp4">
                                </video>
                            @elseif($car->video_url)
                                @php
                                    preg_match(
                                        '/(?:v=|\/embed\/|\/watch\?v=|youtu\.be\/)([^&\?\/]+)/',
                                        $car->video_url,
                                        $m,
                                    );
                                    $videoId = $m[1] ?? null;
                                @endphp
                                @if ($videoId)
                                    <iframe src="https://www.youtube.com/embed/{{ $videoId }}"
                                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                        allowfullscreen></iframe>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- RIGHT: INFO -->
            <div class="info-column">

                <!-- VIN & LICENSE -->
                <div class="info-card">
                    <div class="vehicle-meta-box">
                        <div class="meta-row">
                            <span class="meta-label">Mã nội bộ</span>
                            <span class="meta-value">{{ $car->internal_code ?? '---' }}</span>
                        </div>

                        <div class="meta-row">
                            <span class="meta-label">Số VIN</span>
                            <span class="meta-value">{{ $car->vin ?? '---' }}</span>
                        </div>

                        <div class="meta-row">
                            <span class="meta-label">Biển số</span>
                            <span class="meta-value">{{ $car->license_plate ?? 'Chưa có' }}</span>
                        </div>

                        @if ($car->owner_count !== null)
                            <div class="meta-row">
                                <span class="meta-label">Đời chủ</span>
                                <span class="meta-value">{{ $car->owner_count }} đời chủ</span>
                            </div>
                        @endif

                        <div class="meta-row">
                            <span class="meta-label">Vị trí xe</span>
                            <span class="meta-value">{{ $car->current_location ?? 'Chưa cập nhật' }}</span>
                        </div>

                        <div class="meta-row">
                            <span class="meta-label">Ngày nhập kho</span>
                            <span class="meta-value">{{ $car->stock_in_date?->format('d/m/Y') ?? 'Chưa nhập' }}</span>
                        </div>

                        <div class="meta-row">
                            <span class="meta-label">Ngày lăn bánh</span>
                            <span class="meta-value">{{ $car->on_road_date?->format('d/m/Y') ?? 'Chưa nhập' }}</span>
                        </div>

                    </div>
                </div>

                <div class="info-card inventory-card">
                    <div class="info-card-title">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7.5 12 3l9 4.5-9 4.5L3 7.5Zm0 0v9l9 4.5 9-4.5v-9M12 12v9" />
                        </svg>
                        Tình trạng tồn kho
                    </div>

                    <div class="inventory-metrics">
                        <div>
                            <span>Tồn vật lý</span>
                            <strong>{{ number_format($physicalStock, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span>Đã giữ</span>
                            <strong>{{ number_format($reservedStock, 0, ',', '.') }}</strong>
                        </div>
                        <div>
                            <span>Khả dụng</span>
                            <strong>{{ number_format($availableStock, 0, ',', '.') }}</strong>
                        </div>
                    </div>

                    @if ($activeReservations->isNotEmpty())
                        <div class="reservation-list">
                            @foreach ($activeReservations as $reservation)
                                <div class="reservation-row">
                                    <div>
                                        <strong>
                                            @if ($reservation->order)
                                                <a href="{{ route('admin.orders.show', $reservation->order->order_id) }}">
                                                    {{ $reservation->order->display_code }}
                                                </a>
                                            @else
                                                Chưa gắn đơn hàng
                                            @endif
                                        </strong>
                                        <span>{{ $reservation->user->name ?? $reservation->order?->user?->name ?? 'Khách hàng' }}</span>
                                    </div>
                                    <div class="reservation-meta">
                                        <span>SL: {{ $reservation->quantity }}</span>
                                        <span>{{ $reservation->reserved_at?->format('d/m/Y H:i') ?? '---' }}</span>
                                        <span>{{ $reservation->status }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="reservation-empty">Chưa có giữ chỗ đang hoạt động.</p>
                    @endif
                </div>

                <div class="info-card">
                    <div class="info-card-title">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 10v-1m0 0c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Chi phí lăn bánh dự kiến
                    </div>

                    <table class="rolling-cost-table">
                        <tbody>
                            <tr>
                                <th>Giá niêm yết</th>
                                <td>{{ $formatMoney($listPrice) }}</td>
                            </tr>
                            <tr>
                                <th>Giá khuyến mãi</th>
                                <td>{{ $salePrice !== null ? $formatMoney($salePrice) : 'Chưa áp dụng' }}</td>
                            </tr>
                            <tr>
                                <th>Giá bán thực tế</th>
                                <td>{{ $formatMoney($actualPrice) }}</td>
                            </tr>
                            <tr>
                                <th>Phí trước bạ</th>
                                <td>{{ $formatMoney($car->registration_fee) }}</td>
                            </tr>
                            <tr>
                                <th>Phí biển số</th>
                                <td>{{ $formatMoney($car->license_plate_fee) }}</td>
                            </tr>
                            <tr>
                                <th>Phí đăng kiểm</th>
                                <td>{{ $formatMoney($car->inspection_fee) }}</td>
                            </tr>
                            <tr>
                                <th>Phí bảo hiểm</th>
                                <td>{{ $formatMoney($car->insurance_fee) }}</td>
                            </tr>
                            <tr>
                                <th>Phí dịch vụ khác</th>
                                <td>{{ $formatMoney($car->other_fees) }}</td>
                            </tr>
                            <tr>
                                <th>Khu vực đăng ký</th>
                                <td>{{ $car->registration_area ?: 'Chưa cập nhật' }}</td>
                            </tr>
                            <tr class="rolling-total-row">
                                <th>Tổng lăn bánh</th>
                                <td>{{ $formatMoney($car->estimated_rolling_price) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- SPECS -->
                <div class="info-card">
                    <div class="info-card-title">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2" />
                        </svg>
                        Thông số kỹ thuật
                    </div>

                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Hãng xe</span>
                            <span class="spec-value" id="spec-brand">{{ $car->brand->name ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Dòng xe</span>
                            <span class="spec-value" id="spec-model">{{ $car->carModel->name ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Năm SX</span>
                            <span class="spec-value">{{ $car->year ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Số Km</span>
                            <span class="spec-value">{{ number_format($car->mileage_km ?? 0) }} km</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Tình trạng</span>
                            <span class="spec-value">{{ $conditionText }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Nhiên liệu</span>
                            <span class="spec-value" id="spec-fuel_type">{{ $car->fuel ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Hộp số</span>
                            <span class="spec-value" id="spec-transmission">{{ $car->transmission ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Động cơ</span>
                            <span class="spec-value" id="spec-engine">{{ $car->engine ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Dẫn động</span>
                            <span class="spec-value" id="spec-drive_type">{{ $car->drive_type ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Kiểu dáng</span>
                            <span class="spec-value" id="spec-body_type">{{ $car->body_type ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Xuất xứ</span>
                            <span class="spec-value" id="spec-origin">{{ $car->origin ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Số chỗ</span>
                            <span class="spec-value" id="spec-seats">{{ $car->seats ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Số cửa</span>
                            <span class="spec-value" id="spec-doors">{{ $car->doors ?? '---' }}</span>
                        </div>
                    </div>
                </div>

                <!-- COLORS -->
                <div class="info-card">
                    <div class="info-card-title">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        Màu sắc
                    </div>
                    <div class="specs-grid">
                        <div class="spec-item">
                            <span class="spec-label">Ngoại thất</span>
                            <span class="spec-value">{{ $car->color ?? '---' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Nội thất</span>
                            <span class="spec-value">{{ $car->interior_color ?? '---' }}</span>
                        </div>
                    </div>
                </div>

                <!-- DESCRIPTION -->
                @if ($car->description)
                    <div class="info-card">
                        <div class="info-card-title">
                            <svg width="20" height="20" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Mô tả chi tiết
                        </div>
                        <div class="description-content">
                            {!! nl2br(e($car->description)) !!}
                        </div>
                    </div>
                @endif

                <!-- ACTIONS -->
                <div class="action-bar">
                    <a href="{{ route('admin.cars.edit', $car->car_id) }}" class="btn btn-edit">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Chỉnh sửa
                    </a>

                    <form action="{{ route('admin.cars.destroy', $car->car_id) }}" method="POST"
                        onsubmit="return confirm('⚠️ Bạn có chắc chắn muốn xóa xe này?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-delete">
                            <svg width="18" height="18" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                            Xóa
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </div>

    <!-- LIGHTBOX -->
    <div id="lightbox" class="lightbox" onclick="closeLightbox(event)">
        <span class="lightbox-close" onclick="closeLightbox()">&times;</span>
        <span class="lightbox-nav lightbox-prev" onclick="navLightbox(-1, event)">&#10094;</span>
        <img id="lightboxImg" src="">
        <span class="lightbox-nav lightbox-next" onclick="navLightbox(1, event)">&#10095;</span>
    </div>

    <script>
        // Image gallery
        let allImages = @json($imageUrls ?? []);
        let currentIndex = 0;

        function changeImage(src, el, index) {
            document.getElementById('mainImage').src = src;
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
            currentIndex = index;
        }

        function openLightbox(index) {
            currentIndex = index;
            document.getElementById('lightboxImg').src = allImages[currentIndex];
            document.getElementById('lightbox').classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLightbox(e) {
            if (!e || e.target.id === 'lightbox') {
                document.getElementById('lightbox').classList.remove('active');
                document.body.style.overflow = '';
            }
        }

        function navLightbox(dir, e) {
            e.stopPropagation();
            currentIndex += dir;
            if (currentIndex < 0) currentIndex = allImages.length - 1;
            if (currentIndex >= allImages.length) currentIndex = 0;
            document.getElementById('lightboxImg').src = allImages[currentIndex];
        }

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (!document.getElementById('lightbox').classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') navLightbox(-1, e);
            if (e.key === 'ArrowRight') navLightbox(1, e);
        });

        // === API: Lấy thông số từ CarModel và fill vào UI (nếu đang trống/placeholder) ===
        (function() {
            const modelId = @json($car->car_model_id);
            if (!modelId) return;

            const url = @json(route('admin.cars.modelSpecs', ['id' => $car->car_model_id]));
            const setText = (id, value) => {
                const el = document.getElementById(id);
                if (!el) return;
                if (value === null || value === undefined || value === '') return;
                const current = (el.textContent || '').trim();
                if (current === '' || current === '---' || current === 'N/A') {
                    el.textContent = value;
                }
            };

            fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.ok ? r.json() : Promise.reject(r))
                .then(data => {
                    if (!data) return;
                    setText('spec-model', data.name);
                    setText('spec-engine', data.engine);
                    setText('spec-fuel_type', data.fuel_type);
                    setText('spec-transmission', data.transmission);
                    setText('spec-body_type', data.body_type);
                    setText('spec-drive_type', data.drive_type);
                    setText('spec-seats', data.seats);
                    setText('spec-doors', data.doors);
                    setText('spec-origin', data.origin);
                    if (data.brand && data.brand.name) setText('spec-brand', data.brand.name);
                })
                .catch(() => {});
        })();
    </script>

@endsection
