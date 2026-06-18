@extends('layouts.site')

@section('title', 'Livestream bán xe - Lux Auto')

@push('styles')
<style>
    .live-page {
        margin-top: -2rem;
    }

    .live-wrap {
        width: min(1280px, calc(100% - 2rem));
        margin: 0 auto;
    }

    .live-hero {
        padding: 2.25rem 0 1.4rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        background: linear-gradient(180deg, rgba(20, 26, 34, 0.62), rgba(12, 15, 20, 0));
    }

    .live-hero__grid {
        display: grid;
        grid-template-columns: minmax(0, 1fr) auto;
        gap: 1rem;
        align-items: end;
    }

    .live-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.75rem;
        color: #fca5a5;
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .live-kicker::before {
        content: "";
        width: 34px;
        height: 1px;
        background: currentColor;
    }

    .live-title {
        margin: 0;
        color: #fff;
        font-size: clamp(2rem, 4vw, 3.55rem);
        font-weight: 950;
        line-height: 1;
    }

    .live-title span {
        color: #f87171;
    }

    .live-copy {
        max-width: 720px;
        margin: 1rem 0 0;
        color: #c4ccda;
        line-height: 1.75;
    }

    .live-status {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        min-height: 36px;
        padding: 0 0.85rem;
        border: 1px solid rgba(248, 113, 113, 0.42);
        border-radius: 999px;
        background: rgba(248, 113, 113, 0.1);
        color: #fecaca;
        font-size: 0.84rem;
        font-weight: 900;
        white-space: nowrap;
    }

    .live-status__dot {
        width: 9px;
        height: 9px;
        border-radius: 999px;
        background: #ef4444;
        box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.14);
    }

    .live-status--offline {
        border-color: rgba(148, 163, 184, 0.36);
        background: rgba(148, 163, 184, 0.09);
        color: #cbd5e1;
    }

    .live-status--offline .live-status__dot {
        background: #94a3b8;
        box-shadow: 0 0 0 4px rgba(148, 163, 184, 0.14);
    }

    .live-stage {
        padding-top: 1.25rem;
    }

    .live-player-card {
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: linear-gradient(180deg, #151b24, #0d1118);
        box-shadow: 0 24px 48px -34px rgba(0, 0, 0, 0.95);
    }

    .live-player {
        position: relative;
        aspect-ratio: 16 / 9;
        background: #000;
    }

    .live-player iframe {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        border: 0;
    }

    .live-player-empty {
        display: grid;
        height: 100%;
        min-height: 280px;
        place-items: center;
        padding: 2rem;
        color: var(--muted);
        text-align: center;
    }

    .live-player-empty svg {
        width: 48px;
        height: 48px;
        margin-bottom: 0.75rem;
        color: var(--accent);
    }

    .live-player-caption {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1rem 1.1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        color: var(--muted);
    }

    .live-player-caption strong {
        color: var(--text);
    }

    .live-section-head {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
        margin: 2rem 0 1rem;
    }

    .live-section-eyebrow {
        margin: 0 0 0.28rem;
        color: var(--accent);
        font-size: 0.72rem;
        font-weight: 850;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .live-section-title {
        margin: 0;
        color: var(--text);
        font-size: clamp(1.35rem, 2.4vw, 2rem);
        font-weight: 950;
    }

    .live-products-count {
        color: var(--muted);
        font-size: 0.9rem;
        white-space: nowrap;
    }

    .live-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 292px), 1fr));
        gap: 1rem;
    }

    .live-car {
        display: flex;
        min-width: 0;
        min-height: 100%;
        flex-direction: column;
        overflow: hidden;
        border: 1px solid var(--border);
        border-radius: 12px;
        background: linear-gradient(180deg, #151b24, #0d1118);
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
    }

    .live-car:hover {
        border-color: rgba(248, 113, 113, 0.52);
        transform: translateY(-2px);
        box-shadow: 0 24px 42px -30px rgba(0, 0, 0, 0.95);
    }

    .live-car__media {
        position: relative;
        display: block;
        aspect-ratio: 16 / 10;
        overflow: hidden;
        background: #090d13;
    }

    .live-car__media img {
        display: block;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.28s ease;
    }

    .live-car:hover .live-car__media img {
        transform: scale(1.035);
    }

    .live-car__empty {
        display: grid;
        height: 100%;
        place-items: center;
        color: var(--muted);
    }

    .live-badge {
        position: absolute;
        top: 12px;
        left: 12px;
        min-height: 28px;
        display: inline-flex;
        align-items: center;
        padding: 0 0.72rem;
        border: 1px solid rgba(248, 113, 113, 0.44);
        border-radius: 999px;
        background: rgba(127, 29, 29, 0.72);
        color: #fff;
        font-size: 0.76rem;
        font-weight: 900;
        backdrop-filter: blur(8px);
    }

    .live-car__body {
        display: flex;
        flex: 1;
        flex-direction: column;
        gap: 0.9rem;
        padding: 1rem;
    }

    .live-car__brand {
        color: var(--muted);
        font-size: 0.82rem;
        font-weight: 760;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .live-car__title {
        display: -webkit-box;
        min-height: 2.75em;
        margin: 0.18rem 0 0;
        overflow: hidden;
        color: var(--text);
        font-size: 1.12rem;
        font-weight: 900;
        line-height: 1.28;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
    }

    .live-car__meta {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.55rem;
    }

    .live-car__meta span {
        min-width: 0;
        padding: 0.62rem 0.68rem;
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.035);
        color: #dbe3ef;
        font-size: 0.84rem;
        font-weight: 780;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .live-car__price {
        margin-top: auto;
        color: var(--accent);
        font-size: 1.22rem;
        font-weight: 950;
        line-height: 1.1;
    }

    .live-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-height: 42px;
        padding: 0 0.95rem;
        border: 1px solid transparent;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--accent), #ead990);
        color: #0c0f14;
        font-size: 0.9rem;
        font-weight: 900;
        text-align: center;
        text-decoration: none;
        box-shadow: 0 16px 34px -24px rgba(201, 169, 98, 0.95);
    }

    .live-btn:hover {
        color: #0c0f14;
        box-shadow: 0 18px 40px -24px rgba(201, 169, 98, 1);
    }

    .live-empty {
        grid-column: 1 / -1;
        display: grid;
        place-items: center;
        min-height: 240px;
        padding: 2rem;
        border: 1px dashed var(--border);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.025);
        color: var(--muted);
        text-align: center;
    }

    .live-btn:focus-visible,
    .live-car__media:focus-visible {
        outline: 2px solid rgba(201, 169, 98, 0.9);
        outline-offset: 3px;
    }

    @media (prefers-reduced-motion: reduce) {
        .live-car,
        .live-car__media img {
            transition: none;
        }

        .live-car:hover {
            transform: none;
        }

        .live-car:hover .live-car__media img {
            transform: none;
        }
    }

    @media (max-width: 760px) {
        .live-wrap {
            width: min(100% - 1.5rem, 1280px);
        }

        .live-hero__grid,
        .live-section-head,
        .live-player-caption {
            display: grid;
            grid-template-columns: 1fr;
            align-items: start;
        }

        .live-products-count {
            white-space: normal;
        }
    }
</style>
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
