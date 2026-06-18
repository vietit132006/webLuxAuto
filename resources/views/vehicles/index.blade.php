@extends('layouts.site')

@section('title', 'Danh sách xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/vehicles-index.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    @if (session('success'))
        <p class="flash-ok" role="status">{{ session('success') }}</p>
    @endif
    <h1 class="page-title">Danh sách xe</h1>

    <form class="search-bar" method="get" action="{{ route('vehicles.index') }}">
        <input type="search" name="q" value="{{ $search }}" placeholder="Tìm theo hãng hoặc dòng xe…" autocomplete="off">
        <button type="submit">Tìm kiếm</button>
    </form>

    @if ($vehicles->isEmpty())
        <div class="empty-state">Không có xe phù hợp. Thử bộ lọc khác hoặc <a href="{{ route('vehicles.index') }}">xóa tìm kiếm</a>.</div>
    @else
        <div class="grid-cards">
            @foreach ($vehicles as $vehicle)
                @include('partials.vehicle-card', ['vehicle' => $vehicle])
            @endforeach
        </div>

        @if ($vehicles->hasPages())
            <div class="pagination-wrap">
                {{ $vehicles->links('pagination.lux') }}
            </div>
        @endif
    @endif
</div>
@endsection