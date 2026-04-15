@extends('layouts.site')

@section('title', 'Danh sách xe')

@section('content')
<style>
    /* TOÀN BỘ CSS CỦA BẠN ĐƯỢC GIỮ NGUYÊN 100% */
    .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; }
    .page-title { margin: 0; font-size: 1.75rem; font-weight: 700; }
    .btn-add { background: var(--accent); color: #0c0f14; padding: 0.6rem 1.2rem; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; }
    .btn-add:hover { filter: brightness(1.05); color: #0c0f14; }
    .search-bar { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.75rem; }
    .search-bar input[type="search"] { flex: 1; min-width: 200px; padding: 0.6rem 0.9rem; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); font-size: 1rem; }
    .search-bar input:focus { outline: none; border-color: var(--accent-dim); box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15); }
    .search-bar button { padding: 0.6rem 1.2rem; border-radius: 8px; border: none; background: var(--accent); color: #0c0f14; font-weight: 600; cursor: pointer; }
    .search-bar button:hover { filter: brightness(1.05); }
    .pagination-wrap { margin-top: 2rem; display: flex; justify-content: center; }
    .lux-pag__inner { display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem; justify-content: center; }
    .lux-pag__btn, .lux-pag__num { display: inline-flex; align-items: center; justify-content: center; padding: 0.45rem 0.75rem; border-radius: 6px; border: 1px solid var(--border); color: var(--text); font-size: 0.875rem; }
    .lux-pag__btn:hover, .lux-pag__num:hover { border-color: var(--accent-dim); color: var(--accent); }
    .lux-pag__btn--disabled { color: var(--muted); cursor: not-allowed; opacity: 0.65; }
    .lux-pag__num--current { background: var(--surface); border-color: var(--accent-dim); color: var(--accent); font-weight: 600; }
    .lux-pag__dots { padding: 0.45rem 0.35rem; color: var(--muted); font-size: 0.875rem; }
    .empty-state { padding: 2rem; text-align: center; color: var(--muted); border: 1px dashed var(--border); border-radius: 12px; }
    .shop-container { display: flex; gap: 2rem; max-width: 1200px; margin: 2rem auto; padding: 0 1rem; }

    /* CỘT BỘ LỌC (SIDEBAR) */
    .filter-sidebar { width: 25%; background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; height: fit-content; position: sticky; top: 20px; }
    .filter-group { margin-bottom: 1.5rem; }
    .filter-title { font-weight: bold; color: var(--accent); margin-bottom: 0.8rem; text-transform: uppercase; font-size: 0.9rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; }
    .filter-input, .filter-select { width: 100%; background: #0a0d12; border: 1px solid var(--border); color: var(--text); padding: 0.6rem; border-radius: 6px; margin-bottom: 0.5rem; }
    .price-range { display: flex; align-items: center; gap: 10px; }
    .price-range input { width: 100%; }
    .btn-filter { width: 100%; background: var(--accent); color: #000; font-weight: bold; border: none; padding: 0.8rem; border-radius: 6px; cursor: pointer; transition: 0.3s; }
    .btn-filter:hover { background: #e4d08a; }
    .btn-reset { display: block; text-align: center; color: var(--muted); margin-top: 10px; text-decoration: none; font-size: 0.85rem; }

    /* CỘT DANH SÁCH XE */
    .car-list-area { width: 75%; }
    .car-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; }
</style>

<div class="wrap">

    <div class="header-actions">
        <h1 class="page-title">Danh sách xe</h1>
    </div>

    <form class="search-bar" method="get" action="{{ route('cars.index') }}">
        <input type="search" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo hãng hoặc dòng xe…" autocomplete="off">
        <button type="submit">Tìm kiếm</button>
    </form>

    <div class="shop-container">

        <aside class="filter-sidebar">
            <form action="{{ route('cars.index') }}" method="GET">

                <input type="hidden" name="keyword" value="{{ request('keyword') }}">

                <div class="filter-group">
                    <div class="filter-title">🏢 Hãng xe</div>
                    <select name="brand_id" class="filter-select">
                        <option value="">-- Tất cả các hãng --</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->brand_id }}" {{ request('brand_id') == $brand->brand_id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="filter-group">
                    <div class="filter-title">✨ Tình trạng</div>
                    <select name="status" class="filter-select">
                        <option value="">-- Tất cả --</option>
                        <option value="1" {{ request('status') == '1' ? 'selected' : '' }}>Mới 100%</option>
                        <option value="0" {{ request('status') == '0' ? 'selected' : '' }}>Xe lướt (Cũ)</option>
                    </select>
                </div>

                <div class="filter-group">
                    <div class="filter-title">💰 Mức giá (VNĐ)</div>
                    <div class="price-range">
                        <input type="number" name="min_price" class="filter-input" placeholder="Từ..." value="{{ request('min_price') }}" min="0">
                        <span>-</span>
                        <input type="number" name="max_price" class="filter-input" placeholder="Đến..." value="{{ request('max_price') }}" min="0">
                    </div>
                </div>

                <button type="submit" class="btn-filter">LỌC XE</button>
                <a href="{{ route('cars.index') }}" class="btn-reset">Xóa bộ lọc</a>
            </form>
        </aside>

        <main class="car-list-area">

            <div style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center;">
                <h2 style="margin: 0;">Kết quả ({{ $cars->total() }})</h2>
            </div>

            @if($cars->isEmpty())
                <div class="empty-state">
                    Không có xe phù hợp. Thử bộ lọc khác hoặc <a href="{{ route('cars.index') }}" style="color: var(--accent);">xóa tìm kiếm</a>.
                </div>
            @else
                <div class="grid-cards car-grid">
                    @foreach($cars as $car)
    <div style="background: #0a0d12; border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: transform 0.3s, box-shadow 0.3s;" onmouseover="this.style.transform='translateY(-5px)'; this.style.borderColor='var(--accent-dim)';" onmouseout="this.style.transform='translateY(0)'; this.style.borderColor='var(--border)';">

        <div style="height: 220px; overflow: hidden; position: relative;">
            @if($car->image)
                <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->name }}" style="width: 100%; height: 100%; object-fit: cover;">
            @else
                <div style="width: 100%; height: 100%; background: #15181f; display: flex; align-items: center; justify-content: center; color: var(--muted); font-size: 0.9rem;">
                    [ Chưa có hình ảnh ]
                </div>
            @endif

            @if(isset($car->status))
                <span style="position: absolute; top: 10px; right: 10px; background: {{ $car->status == 1 ? 'var(--accent)' : '#4b5563' }}; color: {{ $car->status == 1 ? '#000' : '#fff' }}; padding: 0.3rem 0.8rem; border-radius: 50px; font-size: 0.8rem; font-weight: bold;">
                    {{ $car->status == 1 ? 'Mới 100%' : 'Xe lướt' }}
                </span>
            @endif
        </div>

<div style="padding: 1.5rem; display: flex; flex-direction: column;">

            <h3 style="color: var(--text); font-size: 1.2rem; margin: 0 0 0.8rem; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $car->name }}">
                {{ $car->name }}
            </h3>

            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.6rem; margin-bottom: 1.2rem; font-size: 0.85rem; color: var(--muted);">

                <div style="display: flex; align-items: center; gap: 5px;">
                    <span style="font-size: 1rem;">🏢</span>
                    {{ $car->brand ? $car->brand->name : 'Đang cập nhật' }}
                </div>

                <div style="display: flex; align-items: center; gap: 5px;">
                    <span style="font-size: 1rem;">📅</span>
                    {{ $car->year ?? 'Đang cập nhật' }}
                </div>

            </div>
            <div style="color: var(--accent); font-size: 1.3rem; font-weight: 900; margin-bottom: 1.5rem;">
                {{ number_format($car->price, 0, ',', '.') }} đ
            </div>

            <a href="{{ route('cars.show_public', $car->car_id ?? $car->id) }}" style="display: block; text-align: center; background: transparent; border: 1px solid var(--accent); color: var(--accent); padding: 0.75rem; border-radius: 8px; text-decoration: none; font-weight: bold; transition: all 0.3s;" onmouseover="this.style.background='var(--accent)'; this.style.color='#000';" onmouseout="this.style.background='transparent'; this.style.color='var(--accent)';">
                Xem Chi Tiết
            </a>

        </div>
    </div>
@endforeach
                </div>

                @if ($cars->hasPages())
                    <div class="pagination-wrap">
                        {{ $cars->links('pagination.lux') }}
                    </div>
                @endif
            @endif

        </main>
    </div> </div>
@endsection
