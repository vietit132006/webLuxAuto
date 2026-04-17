@extends('layouts.site')
@section('title', 'Livestream Bán Xe - Lux Auto')

@section('content')
<div class="wrap">
    <div style="margin-bottom: 2rem; display: flex; align-items: center; gap: 10px;">
        <span style="display: inline-block; width: 15px; height: 15px; background-color: #ef4444; border-radius: 50%; animation: blink 1s infinite;"></span>
        <h1 style="margin: 0; font-size: 2rem; color: #f8fafc;">LUX AUTO <span style="color: #ef4444;">TRỰC TIẾP</span></h1>
    </div>

    <div style="background: var(--surface); padding: 1.5rem; border-radius: 16px; border: 1px solid var(--border); box-shadow: 0 10px 30px rgba(0,0,0,0.5); margin-bottom: 3rem;">
        <div style="position: relative; padding-bottom: 56.25%; height: 0; overflow: hidden; border-radius: 12px; background: #000;">
            <iframe
                src="https://www.youtube.com/embed/{{ $liveVideoId }}?autoplay=1&mute=0&live=1"
                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"
                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                allowfullscreen>
            </iframe>
        </div>
        <p style="margin-top: 1.5rem; font-size: 1.1rem; color: var(--muted); text-align: center;">
            Đang phát sóng trực tiếp: <strong style="color: var(--text);">Siêu ưu đãi các dòng xe lướt hạng sang</strong>. Chốt cọc ngay hôm nay!
        </p>
    </div>

    <h2 style="margin-bottom: 1.5rem; border-left: 4px solid var(--accent); padding-left: 10px; color: var(--accent);">GIỎ HÀNG TRONG LIVE</h2>

    <div class="grid-cards">
        @forelse($featuredCars as $car)
            <div class="v-card" style="border: 2px solid var(--border); transition: border-color 0.3s;">
                <div class="v-card__img-wrap" style="position: relative;">
                    @if($car->image)
                        <img src="{{ asset('storage/' . $car->image) }}" class="v-card__img" alt="{{ $car->name }}">
                    @endif
                    <span style="position: absolute; top: 10px; left: 10px; background: rgba(239, 68, 68, 0.9); color: white; padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 0.8rem;">
                        ĐANG LÊN SÓNG
                    </span>
                </div>
                <div class="v-card__body">
                    <h3 class="v-card__title">{{ $car->name }}</h3>
                    <div class="v-card__row">
                        <span>Năm: {{ $car->year }}</span> •
                        <span>{{ $car->status == 1 ? 'Mới 100%' : 'Xe Lướt' }}</span>
                    </div>
                    <p class="v-card__price">{{ number_format($car->price, 0, ',', '.') }} đ</p>

                    <a href="{{ route('cars.show_public', $car->car_id) }}" style="display: block; width: 100%; text-align: center; background: var(--accent); color: #000; padding: 0.8rem; border-radius: 8px; font-weight: bold; margin-top: 1rem; transition: background 0.2s;">
                        XEM VÀ ĐẶT CỌC NGAY
                    </a>
                </div>
            </div>
        @empty
            <p style="color: var(--muted); grid-column: span 4; text-align: center;">Chưa có sản phẩm nào được ghim trong phiên live này.</p>
        @endforelse
    </div>
</div>

<style>
    @keyframes blink { 0% { opacity: 1; } 50% { opacity: 0.2; } 100% { opacity: 1; } }
    .v-card:hover { border-color: var(--accent) !important; }
</style>
@endsection
