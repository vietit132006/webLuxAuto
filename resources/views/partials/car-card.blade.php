@php
    /** @var \App\Models\Car $car */
@endphp

<article class="v-card">
    <div class="v-card__img-wrap">
        @if ($car->image)
            <a href="{{ route('cars.show_public', $car->car_id) }}">

                <img class="v-card__img"
                     src="{{ asset('storage/' . $car->image) }}"
                     alt="{{ $car->brand->name ?? 'Hãng' }} {{ $car->name }}"
                     loading="lazy">

            </a>
        @else
            <a href="{{ route('cars.show_public', $car->car_id) }}"
               class="v-card__img"
               style="display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:0.875rem;background:#f3f4f6;">
               Chưa có ảnh
            </a>
        @endif
    </div>

    <div class="v-card__body">
        <h3 class="v-card__title">
            <a href="{{ route('cars.show_public', $car->car_id) }}" style="color:inherit;text-decoration:none;">
                {{ $car->brand->name ?? '' }} {{ $car->name }}
            </a>
        </h3>

        <p class="v-card__meta">
            Đời {{ $car->year }}
        </p>

        <div class="v-card__row">
            @if ($car->color)
                <span>Màu: {{ $car->color }}</span>
            @endif
        </div>

        <p class="v-card__price" style="font-weight: bold; color: var(--accent); margin-top: 10px;">
            {{ number_format($car->price, 0, ',', '.') }} đ
        </p>
    </div>
</article>
