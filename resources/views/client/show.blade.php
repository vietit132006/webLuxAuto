@extends('layouts.site')

@php
    $carModel = $car->carModel;
    $brandName = $carModel?->brand?->name ?? $car->brand?->name ?? 'Hãng khác';
    $modelName = $carModel?->name;
    $fullName = trim($brandName . ' ' . ($modelName ? $modelName . ' ' : '') . $car->name);
    $statusText = match ((int) $car->status) {
        2 => 'Đã đặt cọc',
        3 => 'Đã bán',
        default => 'Sẵn sàng',
    };
    $quickStatusText = match ((int) $car->status) {
        2 => 'Đã đặt cọc',
        3 => 'Đã bán',
        default => 'Xe mới 100%',
    };
    $statusClass = match ((int) $car->status) {
        2 => 'is-reserved',
        3 => 'is-sold',
        default => 'is-ready',
    };
    $canDepositCar = (int) $car->status === 1;
    $mileageText = is_null($car->mileage_km)
        ? 'Đang cập nhật'
        : number_format($car->mileage_km, 0, ',', '.') . ' km';
    $depositAmount = 20000000;
    $galleryImages = $car->images ?? collect();
    $youtubeId = '';

    if ($car->video_url) {
        preg_match(
            '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i',
            $car->video_url,
            $match
        );
        $youtubeId = $match[1] ?? '';
    }

    $roundedRating = (int) round($avgRating ?? 0);
@endphp

@section('title', $fullName)

@push('styles')
<style>
    .detail-page {
        margin-top: -2rem;
    }

    .detail-wrap {
        width: min(1280px, calc(100% - 2rem));
        margin: 0 auto;
    }

    .detail-hero {
        padding: 2rem 0 1.35rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        background: linear-gradient(180deg, rgba(20, 26, 34, 0.62), rgba(12, 15, 20, 0));
    }

    .detail-breadcrumb {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.42rem;
        margin-bottom: 1rem;
        color: var(--muted);
        font-size: 0.88rem;
    }

    .detail-breadcrumb a {
        color: #d9e1ec;
        font-weight: 700;
    }

    .detail-breadcrumb a:hover {
        color: var(--accent);
    }

    .detail-breadcrumb__current {
        color: var(--accent);
        font-weight: 800;
    }

    .detail-hero__grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1.25rem;
        align-items: end;
    }

    .detail-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.75rem;
        color: var(--accent);
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .detail-kicker::before {
        content: "";
        width: 34px;
        height: 1px;
        background: currentColor;
    }

    .detail-title {
        max-width: 860px;
        margin: 0;
        color: #fff;
        font-size: clamp(2rem, 4vw, 3.55rem);
        font-weight: 950;
        line-height: 1.02;
    }

    .detail-subline {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.6rem;
        margin-top: 1rem;
        color: #c4ccda;
    }

    .status-pill,
    .feature-pill {
        display: inline-flex;
        align-items: center;
        min-height: 30px;
        padding: 0 0.78rem;
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 999px;
        background: rgba(255, 255, 255, 0.05);
        font-size: 0.8rem;
        font-weight: 850;
    }

    .status-pill.is-ready {
        border-color: rgba(52, 211, 153, 0.38);
        color: #a7f3d0;
    }

    .status-pill.is-reserved {
        border-color: rgba(251, 191, 36, 0.42);
        color: #fde68a;
    }

    .status-pill.is-sold {
        border-color: rgba(148, 163, 184, 0.32);
        color: #cbd5e1;
    }

    .feature-pill {
        border-color: rgba(201, 169, 98, 0.36);
        color: var(--accent);
    }

    .detail-price {
        justify-self: end;
        min-width: 260px;
        padding: 1rem 1.1rem;
        border: 1px solid rgba(201, 169, 98, 0.28);
        border-radius: 12px;
        background: rgba(201, 169, 98, 0.08);
        text-align: right;
    }

    .detail-price span {
        display: block;
        color: var(--muted);
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .detail-price strong {
        display: block;
        margin-top: 0.25rem;
        color: var(--accent);
        font-size: clamp(1.45rem, 3vw, 2.1rem);
        font-weight: 950;
        line-height: 1.1;
    }

    .detail-layout {
        display: grid;
        grid-template-columns: minmax(0, 1.38fr) minmax(360px, 0.82fr);
        gap: 1.25rem;
        align-items: start;
        padding-top: 1.25rem;
    }

    .media-stack,
    .detail-side,
    .content-panel,
    .reviews-section {
        min-width: 0;
    }

    .media-main {
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 12px;
        aspect-ratio: 16 / 10;
        background: #090d13;
        box-shadow: 0 24px 48px -34px rgba(0, 0, 0, 0.95);
    }

    .media-main img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .media-empty {
        display: grid;
        height: 100%;
        place-items: center;
        color: var(--muted);
        text-align: center;
    }

    .media-empty svg {
        width: 46px;
        height: 46px;
        margin-bottom: 0.75rem;
        color: var(--accent);
    }

    .media-thumbs {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(96px, 1fr));
        gap: 0.65rem;
        margin-top: 0.75rem;
    }

    .media-thumb {
        position: relative;
        overflow: hidden;
        min-height: 72px;
        padding: 0;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #090d13;
        color: var(--text);
        cursor: pointer;
    }

    .media-thumb img {
        display: block;
        width: 100%;
        height: 100%;
        aspect-ratio: 16 / 10;
        object-fit: cover;
        opacity: 0.78;
        transition: opacity 0.2s ease;
    }

    .media-thumb:hover,
    .media-thumb.is-active {
        border-color: rgba(201, 169, 98, 0.55);
    }

    .media-thumb:hover img,
    .media-thumb.is-active img {
        opacity: 1;
    }

    .content-panel,
    .detail-card,
    .reviews-section {
        border: 1px solid var(--border);
        border-radius: 12px;
        background: linear-gradient(180deg, #151b24, #0d1118);
    }

    .content-panel {
        margin-top: 1rem;
        padding: 1.2rem;
    }

    .content-panel h2,
    .detail-card h2,
    .reviews-section h2 {
        margin: 0;
        color: var(--text);
        font-size: 1.18rem;
        font-weight: 900;
    }

    .description-copy {
        margin-top: 0.85rem;
        color: #cbd5e1;
        line-height: 1.75;
        overflow-wrap: anywhere;
        white-space: pre-line;
    }

    .video-frame {
        position: relative;
        overflow: hidden;
        margin-top: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 12px;
        background: #000;
        aspect-ratio: 16 / 9;
    }

    .video-frame video,
    .video-frame iframe {
        display: block;
        width: 100%;
        height: 100%;
        border: 0;
    }

    .detail-side {
        position: sticky;
        top: 92px;
        display: grid;
        gap: 1rem;
    }

    .detail-card {
        padding: 1rem;
    }

    .rating-summary {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 1rem;
        color: var(--muted);
        font-size: 0.92rem;
    }

    .stars {
        color: #fbbf24;
        letter-spacing: 0.06em;
        font-size: 1rem;
        white-space: nowrap;
    }

    .rating-summary strong {
        color: var(--text);
    }

    .spec-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        column-gap: 3rem;
        row-gap: 0;
        margin-top: 0.65rem;
        padding: 0.1rem 0 0;
    }

    .spec-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        min-width: 0;
        min-height: 54px;
        padding: 0.85rem 0;
        border-bottom: 1px solid rgba(255, 255, 255, 0.075);
    }

    .spec-label {
        display: block;
        flex: 0 1 auto;
        color: #8b97ab;
        font-size: 0.92rem;
        font-weight: 500;
        line-height: 1.4;
    }

    .spec-value {
        display: block;
        flex: 1 1 auto;
        overflow: hidden;
        color: #f8fafc;
        font-size: 0.96rem;
        font-weight: 900;
        line-height: 1.4;
        text-align: right;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .deposit-card {
        border: 1px solid rgba(201, 169, 98, 0.28);
        border-radius: 12px;
        padding: 1rem;
        background: rgba(201, 169, 98, 0.07);
    }

    .deposit-card__copy {
        margin: 0 0 0.85rem;
        color: #d9e1ec;
        line-height: 1.6;
        font-size: 0.92rem;
    }

    .detail-actions {
        display: grid;
        gap: 0.65rem;
    }

    .detail-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 46px;
        padding: 0.72rem 1rem;
        border-radius: 8px;
        border: 1px solid transparent;
        font-family: inherit;
        font-size: 0.94rem;
        font-weight: 850;
        text-align: center;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s ease, background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .detail-btn svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .detail-btn--primary {
        width: 100%;
        background: linear-gradient(135deg, var(--accent), #ead990);
        color: #0c0f14;
        box-shadow: 0 16px 34px -24px rgba(201, 169, 98, 0.95);
    }

    .detail-btn--primary:hover {
        color: #0c0f14;
        box-shadow: 0 18px 40px -24px rgba(201, 169, 98, 1);
    }

    .detail-btn--ghost {
        background: rgba(255, 255, 255, 0.04);
        border-color: rgba(255, 255, 255, 0.1);
        color: var(--text);
    }

    .detail-btn--ghost:hover {
        border-color: rgba(201, 169, 98, 0.45);
        background: rgba(201, 169, 98, 0.1);
        color: var(--accent);
    }

    .detail-btn--disabled {
        width: 100%;
        background: rgba(148, 163, 184, 0.11);
        border-color: rgba(148, 163, 184, 0.2);
        color: #cbd5e1;
        cursor: not-allowed;
    }

    .detail-btn__sub {
        display: block;
        margin-top: 0.14rem;
        font-size: 0.78rem;
        font-weight: 700;
        opacity: 0.78;
    }

    .alert-box {
        margin-bottom: 1rem;
        padding: 0.9rem 1rem;
        border-radius: 8px;
        background: rgba(248, 113, 113, 0.1);
        border: 1px solid rgba(248, 113, 113, 0.42);
        color: #fecaca;
    }

    .alert-box strong {
        display: block;
        margin-bottom: 0.45rem;
        color: #fff;
    }

    .alert-box ul {
        margin: 0;
        padding-left: 1.1rem;
    }

    .reviews-section {
        margin-top: 1.25rem;
        padding: 1.2rem;
        scroll-margin-top: 92px;
    }

    .reviews-head {
        display: flex;
        justify-content: space-between;
        align-items: end;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .reviews-head p {
        margin: 0.4rem 0 0;
        color: var(--muted);
    }

    .reviews-score {
        min-width: 180px;
        padding: 0.85rem 1rem;
        border: 1px solid rgba(251, 191, 36, 0.25);
        border-radius: 10px;
        background: rgba(251, 191, 36, 0.08);
        text-align: right;
    }

    .reviews-score strong {
        display: block;
        color: var(--accent);
        font-size: 2rem;
        line-height: 1;
    }

    .reviews-score__meta {
        color: var(--muted);
        font-size: 0.86rem;
    }

    .review-flash {
        margin-bottom: 1rem;
        padding: 0.85rem 1rem;
        border: 1px solid rgba(52, 211, 153, 0.42);
        border-radius: 8px;
        background: rgba(52, 211, 153, 0.12);
        color: #bbf7d0;
        font-weight: 800;
    }

    .review-form {
        margin-bottom: 1rem;
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.035);
    }

    .review-form label {
        display: block;
        margin-bottom: 0.42rem;
        color: #dbe3ef;
        font-size: 0.86rem;
        font-weight: 850;
    }

    .review-form select,
    .review-form textarea {
        width: 100%;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: #0a0d12;
        color: var(--text);
        font: inherit;
    }

    .review-form select {
        max-width: 260px;
        min-height: 42px;
        padding: 0 0.72rem;
    }

    .review-form textarea {
        min-height: 112px;
        padding: 0.72rem;
        resize: vertical;
    }

    .review-form select:focus,
    .review-form textarea:focus {
        outline: none;
        border-color: rgba(201, 169, 98, 0.7);
        box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.14);
    }

    .form-error {
        margin: 0.4rem 0 0;
        color: #fca5a5;
        font-size: 0.86rem;
        font-weight: 700;
    }

    .review-comment-label {
        margin-top: 1rem;
    }

    .form-hint {
        margin: 0.65rem 0 0;
        color: var(--muted);
        font-size: 0.88rem;
        line-height: 1.55;
    }

    .form-hint--warning {
        color: #fde68a;
    }

    .review-submit {
        width: auto;
        margin-top: 0.85rem;
    }

    .reviews-list {
        display: grid;
        gap: 0.75rem;
    }

    .review-item {
        padding: 1rem;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 10px;
        background: rgba(255, 255, 255, 0.025);
    }

    .review-item__head {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        margin-bottom: 0.45rem;
    }

    .review-item__name {
        color: var(--text);
        font-weight: 900;
    }

    .review-item__date {
        color: var(--muted);
        font-size: 0.82rem;
        white-space: nowrap;
    }

    .review-item__text {
        margin: 0.45rem 0 0;
        color: #dbe3ef;
        line-height: 1.65;
    }

    .muted-italic {
        color: var(--muted);
        font-style: italic;
    }

    .reviews-empty {
        margin: 0;
        padding: 1rem 0;
        color: var(--muted);
    }

    .pagination-wrap {
        display: flex;
        justify-content: center;
        margin-top: 1rem;
    }

    .detail-btn:focus-visible,
    .media-thumb:focus-visible,
    .detail-breadcrumb a:focus-visible,
    .reviews-section a:focus-visible {
        outline: 2px solid rgba(201, 169, 98, 0.9);
        outline-offset: 3px;
    }

    @media (prefers-reduced-motion: reduce) {
        .detail-btn,
        .media-thumb img {
            transition: none;
        }
    }

    @media (max-width: 1080px) {
        .detail-layout,
        .detail-hero__grid {
            grid-template-columns: 1fr;
        }

        .detail-price {
            justify-self: stretch;
            text-align: left;
        }

        .detail-side {
            position: static;
        }
    }

    @media (max-width: 700px) {
        .detail-wrap {
            width: min(100% - 1.5rem, 1280px);
        }

        .spec-grid,
        .reviews-head {
            grid-template-columns: 1fr;
        }

        .spec-item {
            min-height: 50px;
        }

        .reviews-head {
            display: grid;
            align-items: start;
        }

        .reviews-score {
            min-width: 0;
            text-align: left;
        }

        .review-item__head {
            display: grid;
            gap: 0.25rem;
        }

        .review-item__date {
            white-space: normal;
        }

        .media-thumbs {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }
</style>
@endpush

@section('content')
<div class="detail-page">
    <section class="detail-hero">
        <div class="detail-wrap">
            <nav class="detail-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('cars.index') }}">Danh sách xe</a>
                <span>/</span>
                <span class="detail-breadcrumb__current">{{ $fullName }}</span>
            </nav>

            <div class="detail-hero__grid">
                <div>
                    <div class="detail-kicker">{{ $brandName }}{{ $modelName ? ' - ' . $modelName : '' }}</div>
                    <h1 class="detail-title">{{ $car->name }}</h1>
                    <div class="detail-subline">
                        <span class="status-pill {{ $statusClass }}">{{ $statusText }}</span>
                        @if($car->is_featured)
                            <span class="feature-pill">Xe nổi bật</span>
                        @endif
                        <span>Đời {{ $car->year ?? 'đang cập nhật' }}</span>
                        <span>{{ $mileageText }}</span>
                    </div>
                </div>

                <div class="detail-price">
                    <span>Giá niêm yết</span>
                    <strong>{{ number_format($car->price, 0, ',', '.') }} VNĐ</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="detail-wrap">
        <div class="detail-layout">
            <div class="media-stack">
                <div class="media-main">
                    @if($car->image)
                        <img id="detail-main-image" src="{{ asset('storage/' . $car->image) }}" alt="{{ $fullName }}">
                    @else
                        <div class="media-empty">
                            <div>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 6 8.25A2.25 2.25 0 0 1 8.068 6.9h7.864A2.25 2.25 0 0 1 18 8.25l2.25 5.25M5.25 13.5h13.5m-12 0v3.75m10.5-3.75v3.75M7.5 17.25h.008v.008H7.5v-.008Zm9 0h.008v.008H16.5v-.008Z" />
                                </svg>
                                <div>Chưa có hình ảnh</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($car->image || $galleryImages->isNotEmpty())
                    <div class="media-thumbs" aria-label="Album ảnh xe">
                        @if($car->image)
                            <button type="button" class="media-thumb is-active" data-image="{{ asset('storage/' . $car->image) }}" data-alt="{{ $fullName }}">
                                <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $fullName }}" loading="lazy">
                            </button>
                        @endif
                        @foreach($galleryImages as $image)
                            @if($image->image_path)
                                <button type="button" class="media-thumb" data-image="{{ asset('storage/' . $image->image_path) }}" data-alt="{{ $fullName }} - ảnh {{ $loop->iteration + 1 }}">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $fullName }} - ảnh {{ $loop->iteration + 1 }}" loading="lazy">
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if($car->description)
                    <section class="content-panel">
                        <h2>Thông tin chi tiết</h2>
                        <div class="description-copy">{!! nl2br(e($car->description)) !!}</div>
                    </section>
                @endif

                @if($car->video_file || $youtubeId)
                    <section class="content-panel">
                        <h2>Video trải nghiệm</h2>
                        <div class="video-frame">
                            @if($car->video_file)
                                <video controls poster="{{ $car->image ? asset('storage/' . $car->image) : '' }}">
                                    <source src="{{ asset('storage/' . $car->video_file) }}" type="video/mp4">
                                </video>
                            @elseif($youtubeId)
                                <iframe
                                    src="https://www.youtube.com/embed/{{ $youtubeId }}"
                                    title="Video trải nghiệm {{ $fullName }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            @endif
                        </div>
                    </section>
                @endif
            </div>

            <aside class="detail-side">
                <section class="detail-card">
                    @if(($reviewCount ?? 0) > 0)
                        <div class="rating-summary">
                            <span class="stars" aria-hidden="true">{{ str_repeat('★', $roundedRating) }}{{ str_repeat('☆', max(0, 5 - $roundedRating)) }}</span>
                            <strong>{{ number_format((float) ($avgRating ?? 0), 1) }}/5</strong>
                            <span>{{ $reviewCount }} đánh giá</span>
                            <a href="#danh-gia">Xem đánh giá</a>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert-box">
                            <strong>Chú ý</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h2>Thông số nhanh</h2>
                    <div class="spec-grid">
                        <div class="spec-item">
                            <span class="spec-label">Năm sản xuất</span>
                            <span class="spec-value">{{ $car->year ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Tình trạng</span>
                            <span class="spec-value">{{ $quickStatusText }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Số km</span>
                            <span class="spec-value" title="{{ $mileageText }}">{{ $mileageText }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Xuất xứ</span>
                            <span class="spec-value">{{ $carModel?->origin ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Kiểu dáng</span>
                            <span class="spec-value">{{ $carModel?->body_type ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Hộp số</span>
                            <span class="spec-value" title="{{ $carModel?->transmission ?? 'Cập nhật sau' }}">{{ $carModel?->transmission ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Động cơ</span>
                            <span class="spec-value" title="{{ $carModel?->engine ?? 'Cập nhật sau' }}">{{ $carModel?->engine ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Nhiên liệu</span>
                            <span class="spec-value">{{ $carModel?->fuel_type ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Màu ngoại thất</span>
                            <span class="spec-value">{{ $car->color ?: 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Màu nội thất</span>
                            <span class="spec-value">{{ $car->interior_color ?: 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Số chỗ ngồi</span>
                            <span class="spec-value">{{ $carModel?->seats ? $carModel->seats . ' chỗ' : 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Dẫn động</span>
                            <span class="spec-value">{{ $carModel?->drive_type ?? 'Cập nhật sau' }}</span>
                        </div>
                    </div>
                </section>

                <section class="deposit-card">
                    <p class="deposit-card__copy">
                        Phí giữ xe: <strong>{{ number_format($depositAmount, 0, ',', '.') }} VNĐ</strong>. Lux Auto xác nhận thông tin xe trước khi hoàn tất giao dịch.
                    </p>

                    @if($canDepositCar)
                        @auth
                            <form action="{{ route('order.deposit', $car->car_id) }}" method="POST" onsubmit="return confirm('Bạn xác nhận muốn đặt cọc {{ number_format($depositAmount, 0, ',', '.') }} VNĐ để giữ chiếc {{ $car->name }} này chứ?');">
                                @csrf
                                <button type="submit" class="detail-btn detail-btn--primary">
                                    Đặt cọc ngay
                                    <span class="detail-btn__sub">{{ number_format($depositAmount, 0, ',', '.') }} VNĐ</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="detail-btn detail-btn--primary">
                                Đăng nhập để đặt cọc
                            </a>
                        @endauth
                    @else
                        <button type="button" class="detail-btn detail-btn--disabled" disabled>{{ $statusText }}</button>
                    @endif
                </section>

                <div class="detail-actions">
                    <button type="button" class="detail-btn detail-btn--ghost" id="btn-add-compare" data-car-id="{{ $car->car_id }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75v16.5m9-16.5v16.5M3.75 8.25h16.5M3.75 15.75h16.5" />
                        </svg>
                        Thêm vào so sánh
                    </button>
                    <a href="{{ route('ticket.create', ['type' => 'test_drive', 'car_id' => $car->car_id]) }}" class="detail-btn detail-btn--ghost">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 6 8.25A2.25 2.25 0 0 1 8.068 6.9h7.864A2.25 2.25 0 0 1 18 8.25l2.25 5.25M5.25 13.5h13.5m-12 0v3.75m10.5-3.75v3.75" />
                        </svg>
                        Đặt lịch lái thử
                    </a>
                    <a href="{{ route('ticket.create') }}" class="detail-btn detail-btn--ghost">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-5l-5 5v-5Z" />
                        </svg>
                        Yêu cầu hỗ trợ
                    </a>
                </div>
            </aside>
        </div>

        <section id="danh-gia" class="reviews-section">
            <div class="reviews-head">
                <div>
                    <h2>Đánh giá từ khách hàng</h2>
                    <p>{{ ($reviewCount ?? 0) > 0 ? 'Nhận xét từ khách đã trải nghiệm hoặc đặt cọc xe.' : 'Chưa có đánh giá nào cho xe này.' }}</p>
                </div>

                @if(($reviewCount ?? 0) > 0)
                    <div class="reviews-score">
                        <strong>{{ number_format((float) ($avgRating ?? 0), 1) }}</strong>
                        <span class="stars" aria-hidden="true">{{ str_repeat('★', $roundedRating) }}{{ str_repeat('☆', max(0, 5 - $roundedRating)) }}</span>
                        <div class="reviews-score__meta">{{ $reviewCount }} lượt đánh giá</div>
                    </div>
                @endif
            </div>

            @if(session('review_success'))
                <div class="review-flash" role="status">{{ session('review_success') }}</div>
            @endif

            @auth
                @if(auth()->user()->role === 'customer')
                    <div class="review-form">
                        <form action="{{ route('cars.reviews.store', $car->car_id) }}" method="post">
                            @csrf
                            @error('review')
                                <p class="form-error">{{ $message }}</p>
                            @enderror

                            <label for="rating">Điểm đánh giá</label>
                            <select name="rating" id="rating" required>
                                @for($r = 5; $r >= 1; $r--)
                                    <option value="{{ $r }}" @selected(old('rating', $userReview?->rating ?? 5) == $r)>{{ $r }} sao</option>
                                @endfor
                            </select>
                            @error('rating')
                                <p class="form-error">{{ $message }}</p>
                            @enderror

                            <label for="comment" class="review-comment-label">Nhận xét</label>
                            <textarea name="comment" id="comment" maxlength="2000" placeholder="Chia sẻ trải nghiệm của bạn về xe này...">{{ old('comment', $userReview?->comment ?? '') }}</textarea>
                            @error('comment')
                                <p class="form-error">{{ $message }}</p>
                            @enderror

                            @if($canReview ?? false)
                                <button type="submit" class="detail-btn detail-btn--primary review-submit">
                                    {{ $userReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
                                </button>
                                @if($userReview)
                                    <p class="form-hint">Bạn đã đánh giá trước đó, gửi lại để chỉnh sửa nội dung.</p>
                                @endif
                            @else
                                <p class="form-hint form-hint--warning">Bạn cần đặt lịch lái thử hoặc đặt cọc xe này trước khi gửi đánh giá.</p>
                            @endif
                        </form>
                    </div>
                @else
                    <p class="reviews-empty">Tài khoản nhân viên chỉ xem đánh giá của khách.</p>
                @endif
            @else
                <p class="reviews-empty"><a href="{{ route('login') }}">Đăng nhập</a> để gửi đánh giá cho sản phẩm này.</p>
            @endauth

            <div class="reviews-list">
                @forelse($reviews ?? [] as $review)
                    <article class="review-item">
                        <div class="review-item__head">
                            <span class="review-item__name">{{ $review->user->name ?? 'Khách hàng' }}</span>
                            <span class="review-item__date">{{ $review->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="stars" aria-label="{{ $review->rating }} trên 5 sao">
                            @for($s = 1; $s <= 5; $s++)
                                {{ $s <= (int) $review->rating ? '★' : '☆' }}
                            @endfor
                        </div>
                        @if($review->comment)
                            <p class="review-item__text">{{ $review->comment }}</p>
                        @else
                            <p class="review-item__text muted-italic">Không có nhận xét.</p>
                        @endif
                    </article>
                @empty
                @endforelse
            </div>

            @if(isset($reviews) && $reviews->hasPages())
                <div class="pagination-wrap">{{ $reviews->links('pagination.lux') }}</div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var mainImage = document.getElementById('detail-main-image');
    var thumbs = document.querySelectorAll('.media-thumb');

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            if (!mainImage) {
                return;
            }

            mainImage.src = thumb.getAttribute('data-image');
            mainImage.alt = thumb.getAttribute('data-alt') || mainImage.alt;
            thumbs.forEach(function (item) {
                item.classList.remove('is-active');
            });
            thumb.classList.add('is-active');
        });
    });

    var KEY = 'lux_compare_ids';
    var btn = document.getElementById('btn-add-compare');

    if (!btn) {
        return;
    }

    btn.addEventListener('click', function () {
        var id = parseInt(btn.getAttribute('data-car-id'), 10);
        var raw = localStorage.getItem(KEY) || '';
        var arr = raw ? raw.split(',').map(function (x) {
            return parseInt(x, 10);
        }).filter(Boolean) : [];

        if (!id) {
            return;
        }

        if (arr.indexOf(id) !== -1) {
            alert('Xe này đã có trong danh sách so sánh.');
            return;
        }

        if (arr.length >= 4) {
            alert('Chỉ có thể so sánh tối đa 4 xe.');
            return;
        }

        arr.push(id);
        localStorage.setItem(KEY, arr.join(','));
        window.location.href = @json(route('compare.index')) + '?ids=' + encodeURIComponent(arr.join(','));
    });
})();
</script>
@endpush
