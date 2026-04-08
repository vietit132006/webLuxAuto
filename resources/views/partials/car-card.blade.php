@php
    /** @var \App\Models\Car $car */
@endphp

<article class="v-card">
    <div class="v-card__img-wrap">
        @if ($car->image)
            <a href="{{ route('admin.cars.show', $car) }}">
                <img class="v-card__img"
                     src="{{ $car->image }}"
                     alt="{{ $car->brand->name }} {{ $car->name }}"
                     loading="lazy">
            </a>
        @else
            <a href="{{ route('admin.cars.show', $car) }}"
               class="v-card__img"
               style="display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:0.875rem;">
               Chưa có ảnh
            </a>
        @endif
    </div>

    <div class="v-card__body">
        <h3 class="v-card__title">
            <a href="{{ route('admin.cars.show', $car) }}" style="color:inherit;text-decoration:none;">
                {{ $car->brand->name }} {{ $car->name }}
            </a>
        </h3>

        <p class="v-card__meta">
            Đời {{ $car->year }}
        </p>

        <div class="v-card__row">
            @if ($car->color)
                <span>{{ $car->color }}</span>
            @endif
        </div>

        <p class="v-card__price">
            {{ number_format($car->price, 0, ',', '.') }} đ
        </p>
    </div>
</article>
