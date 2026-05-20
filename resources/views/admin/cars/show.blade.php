@extends('layouts.admin')

@section('title', $car->title)

@section('content')

    <style>
        :root {
            --gold: #c9a962;
            --gold-light: #e4d08a;
            --dark-bg: #0a0d10;
            --card-bg: linear-gradient(145deg, #12171e, #0a0d10);
            --border: rgba(255, 255, 255, 0.08);
            --text: #e5e7eb;
            --muted: #6b7280;
        }

        .car-detail-wrap {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* === HEADER === */
        .detail-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }

        .header-left {
            flex: 1;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--muted);
            margin-bottom: 12px;
        }

        .breadcrumb a {
            color: var(--muted);
            text-decoration: none;
            transition: 0.2s;
        }

        .breadcrumb a:hover {
            color: var(--gold);
        }

        .car-title {
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            margin: 0 0 8px;
            line-height: 1.2;
        }

        .car-subtitle {
            font-size: 15px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .car-subtitle span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .header-right {
            text-align: right;
        }

        .price-tag {
            font-size: 36px;
            font-weight: 800;
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .price-note {
            font-size: 13px;
            color: var(--muted);
        }

        /* === BADGES === */
        .badges {
            display: flex;
            gap: 10px;
            margin-top: 15px;
            flex-wrap: wrap;
        }

        .badge {
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .badge-new {
            background: linear-gradient(135deg, #10b981, #059669);
            color: #fff;
        }

        .badge-used {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #000;
        }

        .badge-featured {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: #000;
        }

        .badge-status {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid var(--border);
            color: var(--text);
        }

        .badge-status.sold {
            background: rgba(239, 68, 68, 0.2);
            border-color: #ef4444;
            color: #ef4444;
        }

        .badge-status.reserved {
            background: rgba(249, 115, 22, 0.2);
            border-color: #f97316;
            color: #f97316;
        }

        /* === MAIN GRID === */
        .detail-grid {
            display: grid;
            grid-template-columns: 1.4fr 1fr;
            gap: 30px;
        }

        @media (max-width: 1000px) {
            .detail-grid {
                grid-template-columns: 1fr;
            }
        }

        /* === GALLERY === */
        .gallery-section {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .main-image-container {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 16/10;
        }

        .main-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            cursor: zoom-in;
            transition: transform 0.4s ease;
        }

        .main-image:hover {
            transform: scale(1.02);
        }

        .image-counter {
            position: absolute;
            bottom: 15px;
            right: 15px;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(10px);
            padding: 8px 16px;
            border-radius: 30px;
            font-size: 13px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .thumbnail-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 10px;
            margin-top: 15px;
        }

        .thumbnail {
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }

        .thumbnail:hover,
        .thumbnail.active {
            border-color: var(--gold);
            transform: scale(1.05);
        }

        .thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* === INFO CARDS === */
        .info-column {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .info-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .info-card-title {
            font-size: 14px;
            font-weight: 700;
            color: var(--gold);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px dashed var(--border);
        }

        /* === SPECS GRID === */
        .specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 0;
        }

        .spec-item {
            padding: 14px 0;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .spec-item:nth-child(odd) {
            padding-right: 15px;
            border-right: 1px solid var(--border);
        }

        .spec-item:nth-child(even) {
            padding-left: 15px;
        }

        .spec-item:nth-last-child(-n+2) {
            border-bottom: none;
        }

        .spec-label {
            font-size: 13px;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .spec-label svg {
            width: 16px;
            height: 16px;
            opacity: 0.6;
        }

        .spec-value {
            font-weight: 600;
            color: #fff;
            text-align: right;
        }

        /* === VIN & LICENSE === */
        .vin-section {
            background: rgba(201, 169, 98, 0.08);
            border: 1px solid rgba(201, 169, 98, 0.2);
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
        }

        .vin-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
        }

        .vin-row:not(:last-child) {
            border-bottom: 1px dashed rgba(255, 255, 255, 0.1);
        }

        .vin-label {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .vin-value {
            font-family: 'Monaco', 'Consolas', monospace;
            font-size: 14px;
            font-weight: 600;
            color: var(--gold);
            letter-spacing: 1px;
        }

        /* === DESCRIPTION === */
        .description-content {
            color: var(--text);
            line-height: 1.8;
            font-size: 15px;
        }

        /* === VIDEO SECTION === */
        .video-container {
            margin-top: 25px;
        }

        .video-wrapper {
            position: relative;
            border-radius: 16px;
            overflow: hidden;
            background: #000;
            aspect-ratio: 16/9;
        }

        .video-wrapper video,
        .video-wrapper iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        /* === ACTION BUTTONS === */
        .action-bar {
            display: flex;
            gap: 12px;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: #000;
            flex: 1;
            justify-content: center;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(201, 169, 98, 0.4);
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        .btn-delete:hover {
            background: #ef4444;
            color: #fff;
        }

        /* === MODAL === */
        .lightbox {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.95);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .lightbox.active {
            display: flex;
        }

        .lightbox img {
            max-width: 95%;
            max-height: 95%;
            object-fit: contain;
            border-radius: 8px;
        }

        .lightbox-close {
            position: absolute;
            top: 20px;
            right: 30px;
            font-size: 40px;
            color: #fff;
            cursor: pointer;
            opacity: 0.7;
            transition: 0.2s;
        }

        .lightbox-close:hover {
            opacity: 1;
        }

        .lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 50px;
            color: #fff;
            cursor: pointer;
            opacity: 0.5;
            transition: 0.2s;
            user-select: none;
        }

        .lightbox-nav:hover {
            opacity: 1;
        }

        .lightbox-prev {
            left: 30px;
        }

        .lightbox-next {
            right: 30px;
        }

        /* === TIMELINE (Owner history) === */
        .owner-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 16px;
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 10px;
            margin-top: 15px;
        }

        .owner-info svg {
            width: 20px;
            height: 20px;
            color: #10b981;
        }

        .owner-info span {
            color: #10b981;
            font-weight: 600;
        }

        /* .vehicle-meta-box {
                                    background: rgba(255, 255, 255, 0.025);
                                    border: 1px solid var(--border);
                                    border-radius: 12px;
                                    overflow: hidden;
                                } */

        .meta-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            padding: 14px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .meta-row:last-child {
            border-bottom: none;
        }

        .meta-label {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.6px;
            font-weight: 600;
            white-space: nowrap;
        }

        .meta-value {
            font-size: 14px;
            color: var(--text);
            font-weight: 700;
            text-align: right;
            word-break: break-word;
        }

        .meta-row:first-child .meta-value {
            font-family: 'Monaco', 'Consolas', monospace;
            letter-spacing: 0.8px;
        }
    </style>

    <div class="car-detail-wrap">

        <!-- HEADER -->
        <div class="detail-header">
            <div class="header-left">
                <div class="breadcrumb">
                    <a href="{{ route('admin.cars.index') }}" style="color: var(--muted); transition: 0.2s;"
                        onmouseover="this.style.color='var(--accent)'" onmouseout="this.style.color='var(--muted)'">
                        <svg style="width: 28px; height: 28px;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    <span style="color: var(--text);">{{ $car->title }}</span>
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
                    @if ($car->status == 1)
                        <span class="badge badge-new">Xe mới</span>
                    @else
                        <span class="badge badge-used">Xe đã qua sử dụng</span>
                    @endif

                    @if ($car->is_featured)
                        <span class="badge badge-featured">⭐ Nổi bật</span>
                    @endif

                    @if ($car->status == 2)
                        <span class="badge badge-status reserved">Đã đặt cọc</span>
                    @elseif($car->status == 3)
                        <span class="badge badge-status sold">Đã bán</span>
                    @else
                        <span class="badge badge-status">Sẵn sàng</span>
                    @endif
                </div>
            </div>

            <div class="header-right">
                <div class="price-tag">{{ number_format($car->price) }} ₫</div>
                <div class="price-note">Giá niêm yết</div>
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
                        <div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--muted);">
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
                        <h3
                            style="color: var(--gold); font-size: 14px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; display:flex; align-items:center; gap:8px;">

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
                            <span class="meta-label">Số VIN</span>
                            <span class="meta-value">{{ $car->vin ?? '---' }}</span>
                        </div>

                        <div class="meta-row">
                            <span class="meta-label">Biển số</span>
                            <span class="meta-value">{{ $car->license_plate ?? 'Chưa có' }}</span>
                        </div>

                        @if ($car->owner_count)
                            <div class="meta-row">
                                <span class="meta-label">Đời chủ</span>
                                <span class="meta-value">{{ $car->owner_count }} đời chủ</span>
                            </div>
                        @endif
                    </div>
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
