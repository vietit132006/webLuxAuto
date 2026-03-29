@extends('layouts.site')

@section('title', $vehicle->title)

@section('content')
<style>
    .vehicle-detail {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    @media (min-width: 900px) {
        .vehicle-detail { grid-template-columns: 1.3fr 1fr; }
    }
    .vd-img {
        aspect-ratio: 16 / 10;
        background: #0a0d12;
        border-radius: 12px;
        overflow: hidden;
    }
    .vd-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }
    .vd-head {
        margin: 0 0 0.5rem;
        font-size: 1.6rem;
        font-weight: 700;
        color: var(--text);
    }
    .vd-meta {
        font-size: 0.9375rem;
        color: var(--muted);
        margin-bottom: 1rem;
    }
    .vd-specs {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem 1rem;
        margin: 1rem 0 1.25rem;
        font-size: 0.9375rem;
        color: var(--text);
    }
    .vd-price {
        font-size: 1.35rem;
        font-weight: 700;
        color: var(--accent);
        margin: 0 0 1rem;
    }
    .vd-desc {
        line-height: 1.6;
        color: var(--text);
    }
    .vd-actions {
        display: flex;
        gap: 0.75rem;
        margin-top: 1.5rem;
    }
    .vd-actions a {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.6rem 1rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        color: var(--text);
        text-decoration: none;
    }
    .vd-actions a:hover {
        border-color: var(--accent-dim);
        color: var(--accent);
    }
</style>

<div class="wrap">
    <div class="vehicle-detail">
        <div class="vd-left">
            <div class="vd-img">
                @if ($vehicle->image_url)
                    <img src="{{ $vehicle->image_url }}" alt="{{ $vehicle->title }}">
                @endif
            </div>
        </div>
        <div class="vd-right">
            <h1 class="vd-head">{{ $vehicle->title }}</h1>
            <p class="vd-meta">Đời {{ $vehicle->year }}@if ($vehicle->mileage_km) · {{ number_format($vehicle->mileage_km, 0, ',', '.') }} km @endif</p>
            <p class="vd-price">{{ number_format($vehicle->price, 0, ',', '.') }} đ</p>
            <div class="vd-specs">
                <div>Nhiên liệu: <strong>{{ $vehicle->fuel_type }}</strong></div>
                <div>Hộp số: <strong>{{ $vehicle->transmission }}</strong></div>
                @if ($vehicle->color)
                    <div>Màu: <strong>{{ $vehicle->color }}</strong></div>
                @endif
            </div>
            @if ($vehicle->description)
                <div class="vd-desc">{{ $vehicle->description }}</div>
            @endif
            <div class="vd-actions">
                <a href="{{ route('vehicles.index') }}">← Quay lại danh sách</a>
            </div>
        </div>
    </div>
</div>
@endsection
