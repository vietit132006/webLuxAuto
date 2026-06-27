@extends('layouts.site')

@section('title', 'Livestream bán xe - Lux Auto')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-livestream.css')
    @endif
@endpush


@push('styles')
@endpush

@section('content')
<div class="live-page">
    <section class="live-hero">
        <div class="live-wrap">
            <div class="live-hero__grid">
                <div>
                    <div class="live-kicker">Phòng phát sóng Lux Auto</div>
                    <h1 class="live-title">Lux Auto <span>trực tiếp</span></h1>
                    <p class="live-copy">
                        Theo dõi phiên live để xem xe đang được giới thiệu, kiểm tra giá và chuyển sang chi tiết xe khi muốn đặt cọc hoặc đặt lịch lái thử.
                    </p>
                </div>

                <div class="live-status {{ $isLiveActive ? '' : 'live-status--offline' }}" aria-label="Trạng thái livestream">
                    <span class="live-status__dot" aria-hidden="true"></span>
                    {{ $isLiveActive ? 'Đang phát sóng' : 'Chưa phát sóng' }}
                </div>
            </div>
        </div>
    </section>

    <main class="live-wrap live-stage">
        <section class="live-player-card">
            <div class="live-player">
                @if($liveVideoId)
                    <iframe
                        src="https://www.youtube.com/embed/{{ $liveVideoId }}?autoplay=1&mute=1&rel=0"
                        title="Livestream bán xe Lux Auto"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen></iframe>
                @else
                    <div class="live-player-empty">
                        <div>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m15.75 10.5 4.72-4.72a.75.75 0 0 1 1.28.53v11.38a.75.75 0 0 1-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 0 0 2.25-2.25v-9a2.25 2.25 0 0 0-2.25-2.25h-9A2.25 2.25 0 0 0 2.25 7.5v9A2.25 2.25 0 0 0 4.5 18.75Z" />
                            </svg>
                            <div>Chưa có video livestream được cấu hình.</div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="live-player-caption">
                <div>{{ $isLiveActive ? 'Đang phát sóng' : 'Phiên live đang tạm tắt' }}: <strong>Ưu đãi xe tuyển chọn trong phiên live</strong></div>
                <div>{{ $featuredCars->count() }} xe đang được ghim</div>
            </div>
        </section>

        <section>
            <div class="live-section-head">
                <div>
                    <p class="live-section-eyebrow">Giỏ hàng trong live</p>
                    <h2 class="live-section-title">Xe đang lên sóng</h2>
                </div>
                <div class="live-products-count">{{ $featuredCars->count() }} mẫu xe</div>
            </div>

            <div class="live-grid">
                @forelse($featuredCars as $car)
                    @php
                        $brandName = $car->carModel?->brand?->name ?? $car->brand?->name ?? null;
                        $modelName = $car->carModel?->name ?? null;
                        $statusText = match ((int) $car->status) {
                            2 => 'Đã đặt cọc',
                            3 => 'Đã bán',
                            default => 'Sẵn sàng',
                        };
                    @endphp

                    <article class="live-car">
                        <a class="live-car__media" href="{{ route('cars.show_public', $car->car_id) }}" aria-label="Xem chi tiết {{ $car->name }}">
                            @if($car->image)
                                <img src="{{ asset('storage/' . $car->image) }}" alt="{{ trim(($brandName ? $brandName . ' ' : '') . $car->name) }}" loading="lazy">
                            @else
                                <div class="live-car__empty">Chưa có ảnh</div>
                            @endif
                            <span class="live-badge">Đang lên sóng</span>
                        </a>

                        <div class="live-car__body">
                            <div>
                                <div class="live-car__brand">
                                    {{ $brandName ? $brandName . ($modelName ? ' - ' . $modelName : '') : 'Đang cập nhật dòng xe' }}
                                </div>
                                <h3 class="live-car__title">{{ $car->name }}</h3>
                            </div>

                            <div class="live-car__meta">
                                <span>Đời {{ $car->year ?? 'đang cập nhật' }}</span>
                                <span>{{ $statusText }}</span>
                            </div>

                            <div class="live-car__price">{{ number_format($car->price, 0, ',', '.') }} VNĐ</div>
                            <a href="{{ route('cars.show_public', $car->car_id) }}" class="live-btn">Xem và đặt cọc</a>
                        </div>
                    </article>
                @empty
                    <div class="live-empty">
                        Chưa có xe nào được ghim trong phiên live này.
                    </div>
                @endforelse
            </div>
        </section>
    </main>
</div>
@endsection