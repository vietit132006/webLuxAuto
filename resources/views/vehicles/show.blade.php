@extends('layouts.site')

@section('title', $vehicle->title)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/vehicles-show.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    @if (session('success'))
        <p class="flash-ok" role="status">{{ session('success') }}</p>
    @endif
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
                <a href="{{ route('vehicles.edit', $vehicle) }}" class="btn-edit">Sửa</a>
                <form class="vehicles-show-inline-1" action="{{ route('vehicles.destroy', $vehicle) }}" method="post" onsubmit="return confirm('Xóa xe này? Hành động không hoàn tác.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-del">Xoá</button>
                </form>
                <a href="{{ route('vehicles.index') }}">← Quay lại danh sách</a>
            </div>
        </div>
    </div>
</div>
@endsection