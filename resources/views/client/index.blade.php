@extends('layouts.site')

@section('title', 'Danh sách xe')

@section('content')
<style>
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .page-title {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
    }
    .btn-add {
        background: var(--accent);
        color: #0c0f14;
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .btn-add:hover {
        filter: brightness(1.05);
        color: #0c0f14;
    }
    .search-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1.75rem;
    }
    .search-bar input[type="search"] {
        flex: 1;
        min-width: 200px;
        padding: 0.6rem 0.9rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        font-size: 1rem;
    }
    .search-bar input:focus {
        outline: none;
        border-color: var(--accent-dim);
        box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15);
    }
    .search-bar button {
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        border: none;
        background: var(--accent);
        color: #0c0f14;
        font-weight: 600;
        cursor: pointer;
    }
    .search-bar button:hover { filter: brightness(1.05); }
    .pagination-wrap {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }
    .lux-pag__inner {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.35rem;
        justify-content: center;
    }
    .lux-pag__btn,
    .lux-pag__num {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.45rem 0.75rem;
        border-radius: 6px;
        border: 1px solid var(--border);
        color: var(--text);
        font-size: 0.875rem;
    }
    .lux-pag__btn:hover,
    .lux-pag__num:hover {
        border-color: var(--accent-dim);
        color: var(--accent);
    }
    .lux-pag__btn--disabled {
        color: var(--muted);
        cursor: not-allowed;
        opacity: 0.65;
    }
    .lux-pag__num--current {
        background: var(--surface);
        border-color: var(--accent-dim);
        color: var(--accent);
        font-weight: 600;
    }
    .lux-pag__dots {
        padding: 0.45rem 0.35rem;
        color: var(--muted);
        font-size: 0.875rem;
    }
    .empty-state {
        padding: 2rem;
        text-align: center;
        color: var(--muted);
        border: 1px dashed var(--border);
        border-radius: 12px;
    }
</style>

<div class="wrap">
    <div class="header-actions">
        <h1 class="page-title">Danh sách xe</h1>
    </div>

    <form class="search-bar" method="get" action="{{ route('cars.index') }}">
        <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Tìm theo hãng hoặc dòng xe…" autocomplete="off">
        <button type="submit">Tìm kiếm</button>
    </form>
    @if ($cars->isEmpty())
        <div class="empty-state">Không có xe phù hợp. Thử bộ lọc khác hoặc <a href="{{ route('cars.index') }}">xóa tìm kiếm</a>.</div>
    @else
        <div class="grid-cards">
            @foreach ($cars as $car)
                @include('partials.car-card', ['car' => $car])
            @endforeach
        </div>

        @if ($cars->hasPages())
            <div class="pagination-wrap">
                {{ $cars->links('pagination.lux') }}
            </div>
        @endif
    @endif
</div>
@endsection
