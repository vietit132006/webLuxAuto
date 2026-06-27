@extends('layouts.site')

@section('title', 'So sánh xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-compare.css')
    @endif
@endpush


@section('content')

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
        <span class="client-compare-inline-4" id="cmp-count"></span>
    </div>

    @if($cars->isEmpty())
        <div class="cmp-empty">
            Chưa có xe nào để so sánh. Vào <a class="client-compare-inline-3" href="{{ route('cars.index') }}">danh sách xe</a> và bấm « Thêm so sánh ».
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
                                    <div class="client-compare-inline-2">Chưa có ảnh</div>
                                @endif
                                <h3>{{ $car->brand->name ?? '' }} {{ $car->name }}</h3>
                                <div class="meta">Đời {{ $car->year ?? '—' }}</div>
                                <a class="client-compare-inline-1" href="{{ route('cars.show_public', $car->car_id) }}">Chi tiết →</a>
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