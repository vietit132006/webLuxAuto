@php
    /** @var \App\Models\Vehicle $vehicle */
@endphp
<article class="v-card">
    <div class="v-card__img-wrap">
        @if ($vehicle->image_url)
            <img class="v-card__img" src="{{ $vehicle->image_url }}" alt="{{ $vehicle->brand }} {{ $vehicle->model }}" loading="lazy">
        @else
            <div class="v-card__img" style="display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:0.875rem;">Chưa có ảnh</div>
        @endif
    </div>
    <div class="v-card__body">
        <h3 class="v-card__title">{{ $vehicle->brand }} {{ $vehicle->model }}</h3>
        <p class="v-card__meta">Đời {{ $vehicle->year }}@if ($vehicle->mileage_km) · {{ number_format($vehicle->mileage_km, 0, ',', '.') }} km @endif</p>
        <div class="v-card__row">
            <span>{{ $vehicle->fuel_type }}</span>
            <span>{{ $vehicle->transmission }}</span>
            @if ($vehicle->color)
                <span>{{ $vehicle->color }}</span>
            @endif
        </div>
        <p class="v-card__price">{{ number_format($vehicle->price, 0, ',', '.') }} đ</p>
    </div>
</article>
