@php
    $car = $sessionCar->car;
    $brandName = $car?->carModel?->brand?->name;
    $modelName = $car?->carModel?->name;
    $listPrice = (float) ($car?->list_price ?: $car?->price ?: 0);
    $sellingPrice = (float) ($sessionCar->live_price ?: $car?->sale_price ?: $car?->price ?: 0);
    $availableStock = $car ? $car->saleableStock() : 0;
    $isSold = $car ? $car->isSaleBlockedByStatus() : true;
    $stockBadge = $isSold
        ? ['Da ban', 'is-sold']
        : ($car?->isOutOfStock()
            ? ['Het hang', 'is-out']
            : ($car?->isFullyReserved()
                ? ['Da giu het', 'is-reserved']
                : ['Con co the ban', 'is-available']));
@endphp

<article class="live-car {{ $isFocus ? 'is-focus' : '' }}">
    <a class="live-car__media" href="{{ $car ? route('cars.show_public', $car->car_id) : '#' }}" aria-label="Xem chi tiet {{ $car?->name }}">
        @if($car?->image)
            <img src="{{ asset('storage/' . $car->image) }}" alt="{{ trim(($brandName ? $brandName . ' ' : '') . $car->name) }}" loading="lazy">
        @else
            <div class="live-car__empty">Chua co anh</div>
        @endif
        <span class="live-badge {{ $stockBadge[1] }}">{{ $stockBadge[0] }}</span>
        @if($sessionCar->is_focus)
            <span class="live-badge is-focus">Dang focus</span>
        @endif
    </a>

    <div class="live-car__body">
        <div>
            <div class="live-car__brand">{{ trim(($brandName ?: 'Dang cap nhat hang') . ($modelName ? ' / ' . $modelName : '')) }}</div>
            <h3 class="live-car__title">{{ $car?->name ?: 'Xe khong con ton tai' }}</h3>
        </div>

        <div class="live-car__meta">
            <span>Doi {{ $car?->year ?: 'N/A' }}</span>
            <span>Ton kha dung {{ $availableStock }}</span>
        </div>

        <div class="live-car__prices">
            <span>Gia niem yet {{ $listPrice > 0 ? number_format($listPrice, 0, ',', '.') . ' VNĐ' : 'Lien he' }}</span>
            <strong>{{ $sellingPrice > 0 ? number_format($sellingPrice, 0, ',', '.') . ' VNĐ' : 'Lien he' }}</strong>
        </div>

        @if($sessionCar->promotion)
            <div class="live-promotion">{{ $sessionCar->promotion->promotion_code }} - {{ $sessionCar->promotion->title }}</div>
        @endif

        @if($sessionCar->live_note)
            <p class="live-car__note">{{ $sessionCar->live_note }}</p>
        @endif

        <div class="live-card-actions">
            @if($car)
                <a href="{{ route('cars.show_public', $car->car_id) }}" class="live-btn live-btn-secondary">Chi tiet</a>
                <button type="button" class="live-btn live-btn-primary" data-live-action data-lead-type="quote_request" data-car-id="{{ $car->car_id }}" data-car-name="{{ $car->name }}">Bao gia</button>
                <button type="button" class="live-btn live-btn-secondary" data-live-action data-lead-type="test_drive_request" data-car-id="{{ $car->car_id }}" data-car-name="{{ $car->name }}">Lai thu</button>
                @if($availableStock > 0 && !$isSold)
                    @auth
                        <form method="post" action="{{ route('order.deposit', $car->car_id) }}" onsubmit="return confirm('Xac nhan dat coc xe {{ $car->name }}?');">
                            @csrf
                            <button class="live-btn live-btn-deposit" type="submit">Dat coc</button>
                        </form>
                    @else
                        <button type="button" class="live-btn live-btn-deposit" data-live-action data-lead-type="deposit_interest" data-car-id="{{ $car->car_id }}" data-car-name="{{ $car->name }}">Quan tam coc</button>
                    @endauth
                @else
                    <span class="live-btn live-btn-disabled">Khong the dat coc</span>
                @endif
            @endif
        </div>
    </div>
</article>
