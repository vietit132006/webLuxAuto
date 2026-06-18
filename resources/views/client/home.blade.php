@extends('layouts.site')

@section('title', 'Trang chủ')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-home.css')
    @endif
@endpush


@section('content')
@php
    $heroCar = $featuredCars->first();
    $heroImage = $heroCar && $heroCar->image
        ? asset('storage/' . $heroCar->image)
        : 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=1800&q=85';
@endphp


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