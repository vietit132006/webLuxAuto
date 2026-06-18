@extends('layouts.site')

@section('title', 'Trang chủ')

@section('content')
@php
    $heroCar = $featuredCars->first();
    $heroImage = $heroCar && $heroCar->image
        ? asset('storage/' . $heroCar->image)
        : 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1800&q=85';
@endphp

<style>
    .home-page {
        margin-top: -2rem;
    }

    .home-wrap {
        width: min(1180px, calc(100% - 2rem));
        margin: 0 auto;
    }

    .home-hero {
        min-height: clamp(440px, 62vh, 620px);
        display: flex;
        align-items: flex-end;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        background:
            linear-gradient(90deg, rgba(12, 15, 20, 0.96) 0%, rgba(12, 15, 20, 0.78) 42%, rgba(12, 15, 20, 0.22) 100%),
            var(--hero-image) center / cover no-repeat;
    }

    .home-hero__content {
        width: min(1180px, calc(100% - 2rem));
        margin: 0 auto;
        padding: 4.5rem 0 3.75rem;
    }

    .home-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 1rem;
        color: var(--accent);
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .home-kicker::before {
        content: "";
        width: 34px;
        height: 1px;
        background: currentColor;
    }

    .home-hero h1 {
        max-width: 680px;
        margin: 0;
        color: #fff;
        font-size: clamp(2.25rem, 6vw, 5rem);
        font-weight: 850;
        line-height: 0.95;
    }

    .home-hero p {
        max-width: 590px;
        margin: 1.25rem 0 0;
        color: #c6d0df;
        font-size: clamp(1rem, 2vw, 1.16rem);
        line-height: 1.75;
    }

    .home-hero__actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.75rem;
    }

    .home-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.55rem;
        min-height: 44px;
        padding: 0.72rem 1.1rem;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 0.94rem;
        font-weight: 800;
        text-decoration: none;
        cursor: pointer;
        transition: color 0.2s ease, background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .home-btn svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .home-btn--primary {
        background: linear-gradient(135deg, var(--accent), #ead990);
        color: #0c0f14;
        box-shadow: 0 16px 34px -22px rgba(201, 169, 98, 0.9);
    }

    .home-btn--primary:hover {
        color: #0c0f14;
        box-shadow: 0 18px 42px -22px rgba(201, 169, 98, 1);
    }

    .home-btn--ghost {
        background: rgba(255, 255, 255, 0.06);
        border-color: rgba(255, 255, 255, 0.16);
        color: var(--text);
    }

    .home-btn--ghost:hover {
        border-color: rgba(201, 169, 98, 0.45);
        color: var(--accent);
        background: rgba(201, 169, 98, 0.08);
    }

    .home-section {
        padding: 4rem 0;
    }

    .home-section--soft {
        background:
            linear-gradient(180deg, rgba(20, 26, 34, 0.42), rgba(12, 15, 20, 0));
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .section-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .section-eyebrow {
        margin: 0 0 0.45rem;
        color: var(--accent);
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .section-title {
        margin: 0;
        color: var(--text);
        font-size: clamp(1.5rem, 3vw, 2.35rem);
        font-weight: 850;
        line-height: 1.08;
    }

    .section-copy {
        max-width: 620px;
        margin: 0.75rem 0 0;
        color: var(--muted);
        line-height: 1.75;
    }

    .featured-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
    }

    .feature-car {
        display: flex;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: linear-gradient(180deg, #151b24, #0d1118);
        color: inherit;
        min-width: 0;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .feature-car:hover {
        border-color: rgba(201, 169, 98, 0.48);
        box-shadow: 0 22px 40px -28px rgba(0, 0, 0, 0.9);
    }

    .feature-car__media {
        position: relative;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background: #090d13;
    }

    .feature-car__media img {
        width: 100%;
        height: 100%;
        display: block;
        object-fit: cover;
        transition: transform 0.28s ease;
    }

    .feature-car:hover .feature-car__media img {
        transform: scale(1.035);
    }

    .feature-car__empty {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        font-size: 0.9rem;
    }

    .feature-car__badge {
        position: absolute;
        top: 12px;
        left: 12px;
        display: inline-flex;
        align-items: center;
        min-height: 28px;
        padding: 0 0.7rem;
        border-radius: 999px;
        background: rgba(12, 15, 20, 0.72);
        border: 1px solid rgba(255, 255, 255, 0.16);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 800;
        backdrop-filter: blur(8px);
    }

    .feature-car__body {
        padding: 1.05rem;
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.85rem;
    }

    .feature-car__brand {
        color: var(--muted);
        font-size: 0.82rem;
        font-weight: 700;
    }

    .feature-car__title {
        margin: 0.15rem 0 0;
        color: var(--text);
        font-size: 1.12rem;
        line-height: 1.28;
        font-weight: 850;
    }

    .feature-car__specs {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.55rem;
    }

    .feature-car__spec {
        min-width: 0;
        padding: 0.62rem 0.68rem;
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.035);
    }

    .feature-car__label {
        display: block;
        margin-bottom: 0.16rem;
        color: var(--muted);
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .feature-car__value {
        display: block;
        color: var(--text);
        font-size: 0.88rem;
        font-weight: 750;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .feature-car__bottom {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
        margin-top: auto;
        padding-top: 0.2rem;
    }

    .feature-car__price {
        color: var(--accent);
        font-size: 1.06rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .feature-car__link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 38px;
        padding: 0 0.82rem;
        border-radius: 8px;
        border: 1px solid rgba(201, 169, 98, 0.45);
        color: var(--accent);
        font-size: 0.86rem;
        font-weight: 800;
        white-space: nowrap;
    }

    .feature-car__link:hover {
        background: var(--accent);
        color: #0c0f14;
    }

    .empty-featured {
        padding: 2rem;
        border: 1px dashed var(--border);
        border-radius: 12px;
        color: var(--muted);
        text-align: center;
        background: rgba(255, 255, 255, 0.025);
    }

    .intro-layout {
        display: grid;
        grid-template-columns: minmax(0, 0.88fr) minmax(0, 1.12fr);
        gap: 1.25rem;
        align-items: stretch;
    }

    .intro-panel {
        min-height: 360px;
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        background:
            linear-gradient(180deg, rgba(12, 15, 20, 0.05), rgba(12, 15, 20, 0.78)),
            url("https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=1200&q=85") center / cover no-repeat;
    }

    .intro-content {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .intro-card {
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.15rem;
        background: rgba(20, 26, 34, 0.72);
    }

    .intro-card svg {
        width: 28px;
        height: 28px;
        color: var(--accent);
        margin-bottom: 0.85rem;
    }

    .intro-card h3 {
        margin: 0 0 0.45rem;
        color: var(--text);
        font-size: 1.02rem;
    }

    .intro-card p {
        margin: 0;
        color: var(--muted);
        font-size: 0.94rem;
        line-height: 1.65;
    }

    .journey {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 1rem;
        margin-top: 1.25rem;
    }

    .journey-step {
        border-top: 1px solid var(--border);
        padding-top: 1rem;
    }

    .journey-step span {
        color: var(--accent);
        font-size: 0.78rem;
        font-weight: 850;
        letter-spacing: 0.1em;
        text-transform: uppercase;
    }

    .journey-step h3 {
        margin: 0.45rem 0 0.35rem;
        color: var(--text);
        font-size: 1.03rem;
    }

    .journey-step p {
        margin: 0;
        color: var(--muted);
        font-size: 0.92rem;
        line-height: 1.65;
    }

    @media (max-width: 980px) {
        .featured-grid,
        .intro-layout,
        .journey {
            grid-template-columns: 1fr;
        }

        .intro-content {
            grid-template-columns: 1fr;
        }

        .intro-panel {
            min-height: 280px;
        }
    }

    @media (max-width: 640px) {
        .home-wrap,
        .home-hero__content {
            width: min(100% - 1.5rem, 1180px);
        }

        .home-hero {
            min-height: 520px;
            background:
                linear-gradient(180deg, rgba(12, 15, 20, 0.88) 0%, rgba(12, 15, 20, 0.74) 48%, rgba(12, 15, 20, 0.95) 100%),
                var(--hero-image) center / cover no-repeat;
        }

        .home-hero__content {
            padding: 3rem 0 2rem;
        }

        .home-hero__actions,
        .section-head,
        .feature-car__bottom {
            align-items: stretch;
            flex-direction: column;
        }

        .home-btn,
        .feature-car__link {
            width: 100%;
        }

        .home-section {
            padding: 3rem 0;
        }

        .feature-car__specs {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="home-page">
    <section class="home-hero" style="--hero-image: url('{{ $heroImage }}');">
        <div class="home-hero__content">
            <div class="home-kicker">Showroom xe tuyển chọn</div>
            <h1>Lux Auto</h1>
            <p>
                Không gian dành cho người yêu ô tô: khám phá các mẫu xe nổi bật, so sánh thông số quan trọng
                và chọn chiếc xe phù hợp với phong cách di chuyển của bạn.
            </p>
            <div class="home-hero__actions">
                <a class="home-btn home-btn--primary" href="{{ route('cars.index') }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0h7.5m0 0a1.5 1.5 0 0 0 3 0m-3 0H6.75m13.5-7.5H3.75m16.5 0-2.25-4.5A2.25 2.25 0 0 0 15.988 5.5H8.012A2.25 2.25 0 0 0 6 6.75l-2.25 4.5" />
                    </svg>
                    Xem xe nổi bật
                </a>
                <a class="home-btn home-btn--ghost" href="{{ route('compare.index') }}">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75v16.5m9-16.5v16.5M3.75 8.25h16.5M3.75 15.75h16.5" />
                    </svg>
                    So sánh xe
                </a>
            </div>
        </div>
    </section>

    <section class="home-section home-section--soft" id="featured-cars">
        <div class="home-wrap">
            <div class="section-head">
                <div>
                    <p class="section-eyebrow">Xe nổi bật</p>
                    <h2 class="section-title">Những mẫu xe đáng chú ý</h2>
                    <p class="section-copy">
                        Danh sách được chọn từ kho xe đang bán, ưu tiên hình ảnh rõ ràng, thông tin giá,
                        đời xe và số km để bạn đánh giá nhanh trước khi xem chi tiết.
                    </p>
                </div>
                @if ($featuredCars->isNotEmpty())
                    <a class="home-btn home-btn--ghost" href="{{ route('cars.index') }}">Xem tất cả xe</a>
                @endif
            </div>

            @if ($featuredCars->isEmpty())
                <div class="empty-featured">
                    Chưa có xe nổi bật. Hãy đánh dấu xe là nổi bật trong trang quản trị để hiển thị tại đây.
                </div>
            @else
                <div class="featured-grid">
                    @foreach ($featuredCars as $car)
                        @php
                            $brandName = $car->carModel?->brand?->name ?? $car->brand?->name ?? null;
                            $modelName = $car->carModel?->name ?? null;
                            $statusText = match ((int) $car->status) {
                                2 => 'Đã cọc',
                                3 => 'Đã bán',
                                default => 'Sẵn sàng',
                            };
                        @endphp
                        <article class="feature-car">
                            <a class="feature-car__media" href="{{ route('cars.show_public', $car->car_id) }}">
                                @if ($car->image)
                                    <img src="{{ asset('storage/' . $car->image) }}" alt="{{ trim(($brandName ? $brandName . ' ' : '') . $car->name) }}" loading="lazy">
                                @else
                                    <div class="feature-car__empty">Chưa có ảnh</div>
                                @endif
                                <span class="feature-car__badge">{{ $statusText }}</span>
                            </a>
                            <div class="feature-car__body">
                                <div>
                                    <div class="feature-car__brand">
                                        {{ $brandName ? $brandName . ($modelName ? ' - ' . $modelName : '') : 'Đang cập nhật dòng xe' }}
                                    </div>
                                    <h3 class="feature-car__title">{{ $car->name }}</h3>
                                </div>

                                <div class="feature-car__specs">
                                    <div class="feature-car__spec">
                                        <span class="feature-car__label">Đời xe</span>
                                        <span class="feature-car__value">{{ $car->year ?? '-' }}</span>
                                    </div>
                                    <div class="feature-car__spec">
                                        <span class="feature-car__label">Số km</span>
                                        <span class="feature-car__value">{{ number_format($car->mileage_km ?? 0, 0, ',', '.') }} km</span>
                                    </div>
                                    <div class="feature-car__spec">
                                        <span class="feature-car__label">Màu</span>
                                        <span class="feature-car__value">{{ $car->color ?: '-' }}</span>
                                    </div>
                                </div>

                                <div class="feature-car__bottom">
                                    <div class="feature-car__price">{{ number_format($car->price, 0, ',', '.') }} VNĐ</div>
                                    <a class="feature-car__link" href="{{ route('cars.show_public', $car->car_id) }}">Chi tiết</a>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    <section class="home-section">
        <div class="home-wrap">
            <div class="section-head">
                <div>
                    <p class="section-eyebrow">Giới thiệu về ô tô</p>
                    <h2 class="section-title">Hiểu chiếc xe trước khi chọn</h2>
                    <p class="section-copy">
                        Một chiếc ô tô tốt không chỉ nằm ở thương hiệu hay giá bán. Cảm giác lái, công năng,
                        lịch sử sử dụng và chi phí vận hành mới là những yếu tố giúp xe phù hợp lâu dài.
                    </p>
                </div>
            </div>

            <div class="intro-layout">
                <div class="intro-panel" aria-hidden="true"></div>
                <div>
                    <div class="intro-content">
                        <div class="intro-card">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 6 8.25A2.25 2.25 0 0 1 8.068 6.9h7.864A2.25 2.25 0 0 1 18 8.25l2.25 5.25M5.25 13.5h13.5m-12 0v3.75m10.5-3.75v3.75M7.5 17.25h.008v.008H7.5v-.008Zm9 0h.008v.008H16.5v-.008Z" />
                            </svg>
                            <h3>Thiết kế và công năng</h3>
                            <p>Sedan phù hợp di chuyển lịch lãm, SUV rộng rãi cho gia đình, còn coupe và sportback tạo cảm giác lái cá tính hơn.</p>
                        </div>
                        <div class="intro-card">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2m5-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <h3>Lịch sử và số km</h3>
                            <p>Số km, lịch bảo dưỡng và tình trạng nội ngoại thất giúp phản ánh cách xe đã được sử dụng qua thời gian.</p>
                        </div>
                        <div class="intro-card">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v18m6-14.25H9.75a2.25 2.25 0 0 0 0 4.5h4.5a2.25 2.25 0 0 1 0 4.5H6" />
                            </svg>
                            <h3>Giá trị sở hữu</h3>
                            <p>Giá mua chỉ là một phần. Bảo hiểm, bảo dưỡng, phụ tùng và khả năng giữ giá cũng cần được cân nhắc.</p>
                        </div>
                        <div class="intro-card">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m9 12.75 2.25 2.25L15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <h3>Trải nghiệm thực tế</h3>
                            <p>Ngồi thử, lái thử và so sánh trực tiếp giúp bạn cảm nhận độ êm, tầm nhìn, cách âm và tiện nghi của xe.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="journey">
                <div class="journey-step">
                    <span>01</span>
                    <h3>Khám phá</h3>
                    <p>Lọc danh sách theo nhu cầu, ngân sách, đời xe và thương hiệu bạn quan tâm.</p>
                </div>
                <div class="journey-step">
                    <span>02</span>
                    <h3>So sánh</h3>
                    <p>Đặt các mẫu xe cạnh nhau để nhìn rõ khác biệt về giá, thông số và tình trạng.</p>
                </div>
                <div class="journey-step">
                    <span>03</span>
                    <h3>Ra quyết định</h3>
                    <p>Xem chi tiết xe, kiểm tra thông tin và liên hệ showroom khi đã tìm thấy lựa chọn phù hợp.</p>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
