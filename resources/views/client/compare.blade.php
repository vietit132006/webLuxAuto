@extends('layouts.site')

@section('title', 'So sánh xe')

@section('content')
<style>
    .cmp-head { margin-bottom: 1.5rem; }
    .cmp-head h1 { font-size: clamp(1.35rem, 3vw, 1.75rem); margin: 0 0 0.5rem; }
    .cmp-head p { color: var(--muted); margin: 0; }
    .cmp-toolbar { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; margin-bottom: 1.25rem; }
    .cmp-toolbar a, .cmp-toolbar button {
        display: inline-flex; align-items: center; padding: 0.55rem 1rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem;
        cursor: pointer; border: none; font-family: inherit;
    }
    .btn-gold { background: var(--accent); color: #0c0f14; text-decoration: none; }
    .btn-ghost { background: transparent; border: 1px solid var(--border); color: var(--text); }
    .cmp-table-wrap { overflow-x: auto; border-radius: 12px; border: 1px solid var(--border); background: var(--surface); }
    .cmp-table { width: 100%; border-collapse: collapse; min-width: 640px; }
    .cmp-table th, .cmp-table td { padding: 0.85rem 1rem; border-bottom: 1px solid var(--border); text-align: left; vertical-align: top; }
    .cmp-table th { width: 140px; color: var(--muted); font-size: 0.8rem; text-transform: uppercase; }
    .cmp-car { min-width: 180px; }
    .cmp-car img { width: 100%; max-height: 120px; object-fit: cover; border-radius: 8px; background: #0a0d12; }
    .cmp-car h3 { margin: 0.5rem 0 0.25rem; font-size: 1rem; }
    .cmp-car .meta { font-size: 0.8rem; color: var(--muted); }
    .cmp-price { color: var(--accent); font-weight: 800; font-size: 1.05rem; }
    .cmp-empty { text-align: center; padding: 3rem 1.5rem; color: var(--muted); border: 1px dashed var(--border); border-radius: 12px; }
</style>

@if(empty($ids))
<script>
(function () {
    var raw = localStorage.getItem('lux_compare_ids');
    if (raw) {
        window.location.replace(@json(route('compare.index')) + '?ids=' + encodeURIComponent(raw));
    }
})();
</script>
@endif

<div class="wrap">
    <div class="cmp-head">
        <h1>So sánh xe</h1>
        <p>Tối đa 4 xe. Thêm xe từ danh sách hoặc trang chi tiết bằng nút « Thêm so sánh ».</p>
    </div>

    <div class="cmp-toolbar">
        <a href="{{ route('cars.index') }}" class="btn-gold">← Chọn thêm xe</a>
        <button type="button" class="btn-ghost" id="cmp-clear">Xóa danh sách so sánh</button>
        <span id="cmp-count" style="color: var(--muted); font-size: 0.9rem;"></span>
    </div>

    @if($cars->isEmpty())
        <div class="cmp-empty">
            Chưa có xe nào để so sánh. Vào <a href="{{ route('cars.index') }}" style="color: var(--accent);">danh sách xe</a> và bấm « Thêm so sánh ».
        </div>
    @else
        <div class="cmp-table-wrap">
            <table class="cmp-table">
                <thead>
                    <tr>
                        <th></th>
                        @foreach($cars as $car)
                            <td class="cmp-car">
                                @if($car->image)
                                    <img src="{{ asset('storage/' . $car->image) }}" alt="">
                                @else
                                    <div style="height:100px;display:flex;align-items:center;justify-content:center;color:var(--muted);font-size:0.85rem;">Chưa có ảnh</div>
                                @endif
                                <h3>{{ $car->brand->name ?? '' }} {{ $car->name }}</h3>
                                <div class="meta">Đời {{ $car->year ?? '—' }}</div>
                                <a href="{{ route('cars.show_public', $car->car_id) }}" style="font-size: 0.85rem; color: var(--accent); font-weight: 600;">Chi tiết →</a>
                            </td>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Giá</th>
                        @foreach($cars as $car)
                            <td class="cmp-price">{{ number_format($car->price, 0, ',', '.') }} đ</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Tồn kho</th>
                        @foreach($cars as $car)
                            <td>{{ $car->stock ?? 0 }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Odo</th>
                        @foreach($cars as $car)
                            <td>
                                @if($car->mileage_km)
                                    {{ number_format($car->mileage_km, 0, ',', '.') }} km
                                @else
                                    —
                                @endif
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Nhiên liệu</th>
                        @foreach($cars as $car)
                            <td>{{ $car->fuel ?? '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Hộp số</th>
                        @foreach($cars as $car)
                            <td>{{ $car->transmission ?? '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Màu</th>
                        @foreach($cars as $car)
                            <td>{{ $car->color ?? '—' }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <th>Tình trạng</th>
                        @foreach($cars as $car)
                            <td>{{ isset($car->status) && $car->status == 1 ? 'Mới 100%' : 'Xe lướt' }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    @endif
</div>

@push('scripts')
<script>
(function () {
    var KEY = 'lux_compare_ids';
    function syncFromUrl() {
        var params = new URLSearchParams(window.location.search);
        var ids = params.get('ids');
        if (ids) localStorage.setItem(KEY, ids);
    }
    syncFromUrl();

    function updateToolbar() {
        var raw = localStorage.getItem(KEY) || '';
        var n = raw ? raw.split(',').filter(Boolean).length : 0;
        var el = document.getElementById('cmp-count');
        if (el) el.textContent = n ? ('Đang lưu ' + n + ' xe trong trình duyệt') : '';
    }
    updateToolbar();

    var clearBtn = document.getElementById('cmp-clear');
    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            localStorage.removeItem(KEY);
            window.location.href = @json(route('compare.index'));
        });
    }
})();
</script>
@endpush
@endsection
