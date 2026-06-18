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
               class="v-card__img partial-car-card-inline-3">
               Chưa có ảnh
            </a>
        @endif
    </div>

    <div class="v-card__body">
        <h3 class="v-card__title">
            <a class="partial-car-card-inline-2" href="{{ route('cars.show_public', $car->car_id) }}">
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

        <p class="v-card__price partial-car-card-inline-1">
            {{ number_format($car->price, 0, ',', '.') }} đ
        </p>
    </div>
</article>