@extends('layouts.site')

@section('title', 'Danh sách xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-index.css')
    @endif
@endpush


@push('styles')
@endpush

@section('content')
@php
    $hasStatusFilter = request()->has('status') && request('status') !== '';
    $hasFilters = request()->filled('keyword')
        || request()->filled('brand_id')
        || $hasStatusFilter
        || request()->filled('min_price')
        || request()->filled('max_price');
    $selectedBrand = request()->filled('brand_id')
        ? $brands->firstWhere('brand_id', (int) request('brand_id'))
        : null;
    $selectedStatus = match ((string) request('status')) {
        '1' => 'Sẵn sàng',
        '2' => 'Đã đặt cọc',
        '3' => 'Đã bán',
        default => null,
    };
@endphp

<div class="cars-page">
    <section class="cars-hero">
        <div class="cars-wrap">
            <div class="cars-hero__grid">
                <div>
                    <div class="cars-kicker">Kho xe Lux Auto</div>
                    <h1 class="cars-title">Danh sách xe</h1>
                    <p class="cars-lead">
                        Lọc nhanh theo thương hiệu, ngân sách và trạng thái bán hàng để tìm mẫu xe phù hợp trước khi xem chi tiết hoặc đưa vào danh sách so sánh.
                    </p>
                </div>

                <div class="cars-quick-actions" aria-label="Lối tắt danh sách xe">
                    <a href="{{ route('compare.index') }}" id="lux-compare-bar" class="cars-btn cars-btn--compare">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75v16.5m9-16.5v16.5M3.75 8.25h16.5M3.75 15.75h16.5" />
                        </svg>
                        So sánh đã chọn (<span id="lux-compare-n">0</span>)
                    </a>
                    <a href="{{ route('promotions.index') }}" class="cars-btn cars-btn--soft">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3.75h4.864c.89 0 1.705.497 2.112 1.289l.706 1.373 1.526.22a2.375 2.375 0 0 1 1.317 4.05l-1.103 1.075.261 1.52a2.375 2.375 0 0 1-3.447 2.504l-1.365-.718-1.365.718a2.375 2.375 0 0 1-3.447-2.504l.261-1.52-1.103-1.075a2.375 2.375 0 0 1 1.317-4.05l1.526-.22.706-1.373A2.375 2.375 0 0 1 9.568 3.75Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 20.25h4.5" />
                        </svg>
                        Khuyến mãi
                    </a>
                </div>
            </div>

            <form class="cars-search" method="get" action="{{ route('cars.index') }}">
                @if(request()->filled('brand_id'))
                    <input type="hidden" name="brand_id" value="{{ request('brand_id') }}">
                @endif
                @if($hasStatusFilter)
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request()->filled('min_price'))
                    <input type="hidden" name="min_price" value="{{ request('min_price') }}">
                @endif
                @if(request()->filled('max_price'))
                    <input type="hidden" name="max_price" value="{{ request('max_price') }}">
                @endif

                <div class="cars-search__field">
                    <label class="sr-only" for="cars-keyword">Tìm theo hãng hoặc dòng xe</label>
                    <input id="cars-keyword" class="cars-input" type="search" name="keyword" value="{{ request('keyword') }}" placeholder="Tìm theo hãng, dòng xe hoặc phiên bản..." autocomplete="off">
                </div>
                <button class="cars-search__button" type="submit">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197M18 10.5a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" />
                    </svg>
                    Tìm kiếm
                </button>
            </form>
        </div>
    </section>

    <div class="cars-wrap">
        <div class="cars-workspace">
            <aside class="filter-panel" aria-label="Bộ lọc xe">
                <div class="filter-panel__head">
                    <h2 class="filter-panel__title">Bộ lọc</h2>
                    <p class="filter-panel__hint">Tinh chỉnh kết quả theo thương hiệu, trạng thái và khoảng giá.</p>
                </div>

                <form class="filter-form" action="{{ route('cars.index') }}" method="get">
                    <input type="hidden" name="keyword" value="{{ request('keyword') }}">

                    <div class="filter-group">
                        <label class="filter-label" for="filter-brand">Hãng xe</label>
                        <select id="filter-brand" name="brand_id" class="filter-select">
                            <option value="">Tất cả các hãng</option>
                            @foreach($brands as $brand)
                                <option value="{{ $brand->brand_id }}" {{ request('brand_id') == $brand->brand_id ? 'selected' : '' }}>
                                    {{ $brand->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label class="filter-label" for="filter-status">Trạng thái</label>
                        <select id="filter-status" name="status" class="filter-select">
                            <option value="">Tất cả trạng thái</option>
                            <option value="1" {{ (string) request('status') === '1' ? 'selected' : '' }}>Sẵn sàng</option>
                            <option value="2" {{ (string) request('status') === '2' ? 'selected' : '' }}>Đã đặt cọc</option>
                            <option value="3" {{ (string) request('status') === '3' ? 'selected' : '' }}>Đã bán</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <span class="filter-label">Mức giá (VNĐ)</span>
                        <div class="price-fields">
                            <label class="sr-only" for="filter-min-price">Giá từ</label>
                            <input id="filter-min-price" type="number" name="min_price" class="filter-input" placeholder="Từ" value="{{ request('min_price') }}" min="0" inputmode="numeric">

                            <label class="sr-only" for="filter-max-price">Giá đến</label>
                            <input id="filter-max-price" type="number" name="max_price" class="filter-input" placeholder="Đến" value="{{ request('max_price') }}" min="0" inputmode="numeric">
                        </div>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-submit">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044c0 .318-.126.623-.352.848l-6.298 6.299a1.2 1.2 0 0 0-.351.848v3.686a1.2 1.2 0 0 1-.658 1.071l-2.4 1.2A1.2 1.2 0 0 1 9.2 18.696v-4.883a1.2 1.2 0 0 0-.351-.848L2.55 6.666A1.2 1.2 0 0 1 2.2 5.818V4.774c0-.54.384-1.006.917-1.096A53.17 53.17 0 0 1 12 3Z" />
                            </svg>
                            Lọc xe
                        </button>
                        <a href="{{ route('cars.index') }}" class="filter-reset">Xóa bộ lọc</a>
                    </div>
                </form>
            </aside>

            <main class="cars-results">
                <div class="results-bar">
                    <div>
                        <p class="results-eyebrow">Kết quả tìm kiếm</p>
                        <h2 class="results-title">{{ number_format($cars->total()) }} xe phù hợp</h2>
                    </div>
                    @if($cars->total() > 0)
                        <div class="results-range">
                            Hiển thị {{ number_format($cars->firstItem()) }}-{{ number_format($cars->lastItem()) }} / {{ number_format($cars->total()) }}
                        </div>
                    @endif
                </div>

                @if($hasFilters)
                    <div class="active-filters" aria-label="Bộ lọc đang áp dụng">
                        @if(request()->filled('keyword'))
                            <span class="filter-chip">Từ khóa: {{ request('keyword') }}</span>
                        @endif
                        @if($selectedBrand)
                            <span class="filter-chip">Hãng: {{ $selectedBrand->name }}</span>
                        @endif
                        @if($selectedStatus)
                            <span class="filter-chip">Trạng thái: {{ $selectedStatus }}</span>
                        @endif
                        @if(request()->filled('min_price'))
                            <span class="filter-chip">Từ {{ number_format((float) request('min_price'), 0, ',', '.') }} VNĐ</span>
                        @endif
                        @if(request()->filled('max_price'))
                            <span class="filter-chip">Đến {{ number_format((float) request('max_price'), 0, ',', '.') }} VNĐ</span>
                        @endif
                    </div>
                @endif

                @if($cars->isEmpty())
                    <div class="empty-state">
                        <div>
                            <div class="empty-state__icon" aria-hidden="true">
                                <svg width="28" height="28" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 6 8.25A2.25 2.25 0 0 1 8.068 6.9h7.864A2.25 2.25 0 0 1 18 8.25l2.25 5.25M5.25 13.5h13.5m-12 0v3.75m10.5-3.75v3.75M7.5 17.25h.008v.008H7.5v-.008Zm9 0h.008v.008H16.5v-.008Z" />
                                </svg>
                            </div>
                            <h2>Chưa tìm thấy xe phù hợp</h2>
                            <p>Hãy thử nới khoảng giá, đổi hãng xe hoặc <a href="{{ route('cars.index') }}">xóa bộ lọc</a> để xem toàn bộ kho xe.</p>
                        </div>
                    </div>
                @else
                    <div class="cars-grid">
                        @foreach($cars as $car)
                            @php
                                $carId = $car->car_id ?? $car->id;
                                $brandName = $car->carModel?->brand?->name ?? $car->brand?->name ?? null;
                                $modelName = $car->carModel?->name ?? null;
                                $physicalStock = $car->physicalStock();
                                $availableStock = $car->availableStock();
                                if ($physicalStock <= 0) {
                                    $statusText = 'Hết hàng';
                                    $statusClass = 'is-sold';
                                } elseif ($availableStock <= 0) {
                                    $statusText = 'Đã giữ hết';
                                    $statusClass = 'is-reserved';
                                } else {
                                    $statusText = match ((int) $car->status) {
                                        2 => 'Đã đặt cọc',
                                        3 => 'Đã bán',
                                        default => 'Sẵn sàng',
                                    };
                                    $statusClass = match ((int) $car->status) {
                                        2 => 'is-reserved',
                                        3 => 'is-sold',
                                        default => 'is-ready',
                                    };
                                }
                                $mileageText = is_null($car->mileage_km)
                                    ? 'Đang cập nhật'
                                    : number_format($car->mileage_km, 0, ',', '.') . ' km';
                                $transmissionText = $car->carModel?->transmission ?: 'Đang cập nhật';
                                $cardAlt = trim(($brandName ? $brandName . ' ' : '') . $car->name);
                            @endphp

                            <article class="car-card">
                                <a class="car-card__media" href="{{ route('cars.show_public', $carId) }}" aria-label="Xem chi tiết {{ $car->name }}">
                                    @if($car->image)
                                        <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $cardAlt }}" loading="lazy">
                                    @else
                                        <div class="car-card__empty-img">
                                            <svg width="34" height="34" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 6 8.25A2.25 2.25 0 0 1 8.068 6.9h7.864A2.25 2.25 0 0 1 18 8.25l2.25 5.25M5.25 13.5h13.5m-12 0v3.75m10.5-3.75v3.75M7.5 17.25h.008v.008H7.5v-.008Zm9 0h.008v.008H16.5v-.008Z" />
                                            </svg>
                                            Chưa có ảnh
                                        </div>
                                    @endif
                                    <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                </a>

                                <div class="car-card__body">
                                    <div>
                                        <div class="car-card__brand">
                                            {{ $brandName ? $brandName . ($modelName ? ' - ' . $modelName : '') : 'Đang cập nhật dòng xe' }}
                                        </div>
                                        <h3 class="car-card__title" title="{{ $car->name }}">{{ $car->name }}</h3>
                                    </div>

                                    <div class="car-specs" aria-label="Thông số nhanh">
                                        <div class="car-spec">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25m10.5-2.25v2.25M3.75 9.75h16.5M5.25 5.25h13.5c.828 0 1.5.672 1.5 1.5v11.25c0 .828-.672 1.5-1.5 1.5H5.25c-.828 0-1.5-.672-1.5-1.5V6.75c0-.828.672-1.5 1.5-1.5Z" />
                                            </svg>
                                            <span class="car-spec__label">Đời xe</span>
                                            <span class="car-spec__value">{{ $car->year ?? 'Đang cập nhật' }}</span>
                                        </div>

                                        <div class="car-spec">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75a7.5 7.5 0 1 1 15 0M12 12l3.75-3.75M8.25 18.75h7.5" />
                                            </svg>
                                            <span class="car-spec__label">Số km</span>
                                            <span class="car-spec__value" title="{{ $mileageText }}">{{ $mileageText }}</span>
                                        </div>

                                        <div class="car-spec">
                                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h3m-6 6h9m-6 6h3M6.75 3.75h10.5A1.5 1.5 0 0 1 18.75 5.25v13.5a1.5 1.5 0 0 1-1.5 1.5H6.75a1.5 1.5 0 0 1-1.5-1.5V5.25a1.5 1.5 0 0 1 1.5-1.5Z" />
                                            </svg>
                                            <span class="car-spec__label">Hộp số</span>
                                            <span class="car-spec__value" title="{{ $transmissionText }}">{{ $transmissionText }}</span>
                                        </div>
                                    </div>

                                    <div class="car-card__footer">
                                        <div class="car-card__price">{{ number_format($car->price, 0, ',', '.') }} VNĐ</div>

                                        <div class="car-card__actions">
                                            <a class="car-card__link" href="{{ route('cars.show_public', $carId) }}">
                                                Chi tiết
                                            </a>
                                            <button type="button" class="lux-btn-cmp" data-id="{{ $carId }}" aria-pressed="false" aria-label="Thêm {{ $car->name }} vào danh sách so sánh">
                                                <svg width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75v16.5m9-16.5v16.5M3.75 8.25h16.5M3.75 15.75h16.5" />
                                                </svg>
                                                <span class="lux-cmp-label">So sánh</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>

                    @if ($cars->hasPages())
                        <div class="pagination-wrap">
                            {{ $cars->links('pagination.lux') }}
                        </div>
                    @endif
                @endif
            </main>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var KEY = 'lux_compare_ids';

    function readIds() {
        var raw = localStorage.getItem(KEY) || '';
        return raw ? raw.split(',').map(function (value) {
            return parseInt(value, 10);
        }).filter(Boolean) : [];
    }

    function refreshBar() {
        var arr = readIds();
        var bar = document.getElementById('lux-compare-bar');
        var num = document.getElementById('lux-compare-n');

        if (bar && num) {
            num.textContent = arr.length;
            bar.style.display = arr.length ? 'inline-flex' : 'none';
            bar.href = @json(route('compare.index')) + '?ids=' + encodeURIComponent(arr.join(','));
        }

        document.querySelectorAll('.lux-btn-cmp').forEach(function (btn) {
            var id = parseInt(btn.getAttribute('data-id'), 10);
            var label = btn.querySelector('.lux-cmp-label');
            var active = arr.indexOf(id) !== -1;

            btn.classList.toggle('is-active', active);
            btn.setAttribute('aria-pressed', active ? 'true' : 'false');

            if (label) {
                label.textContent = active ? 'Đã chọn' : 'So sánh';
            }
        });
    }

    refreshBar();

    document.querySelectorAll('.lux-btn-cmp').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = parseInt(btn.getAttribute('data-id'), 10);
            var arr = readIds();

            if (arr.indexOf(id) !== -1) {
                alert('Xe đã có trong danh sách so sánh.');
                return;
            }

            if (arr.length >= 4) {
                alert('Tối đa 4 xe so sánh.');
                return;
            }

            arr.push(id);
            localStorage.setItem(KEY, arr.join(','));
            refreshBar();
        });
    });
})();
</script>
@endpush
