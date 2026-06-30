@extends('layouts.admin')

@section('title', 'Báo cáo đánh giá')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reports-reviews.css')
    @endif
@endpush

@section('content')
<div class="review-report-page">
    <div class="review-report-head">
        <div>
            <h1>Báo cáo đánh giá</h1>
            <p>Phân tích chất lượng phản hồi khách hàng theo xe, hãng và trạng thái kiểm duyệt.</p>
        </div>
        <div class="review-report-actions">
            @can('reviews.view')
                <a class="report-secondary" href="{{ route('admin.reviews.index') }}">Quản lý đánh giá</a>
            @endcan
            @canany(['reports.view', 'reviews.export'])
                <a class="report-primary" href="{{ route('admin.reports.reviews.export', request()->query()) }}">Xuất Excel</a>
            @endcanany
        </div>
    </div>

    <form class="review-report-filter" method="get" action="{{ route('admin.reports.reviews') }}">
        <div class="report-filter-field">
            <label for="date_from">Từ ngày</label>
            <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}">
        </div>
        <div class="report-filter-field">
            <label for="date_to">Đến ngày</label>
            <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}">
        </div>
        <div class="report-filter-field">
            <label for="brand_id">Hãng</label>
            <select id="brand_id" name="brand_id">
                <option value="">Tất cả</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->brand_id }}" @selected((string) $filters['brand_id'] === (string) $brand->brand_id)>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="report-filter-field">
            <label for="model_id">Model</label>
            <select id="model_id" name="model_id">
                <option value="">Tất cả</option>
                @foreach($models as $model)
                    <option value="{{ $model->id }}" @selected((string) $filters['model_id'] === (string) $model->id)>{{ $model->brand?->name }} {{ $model->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="report-filter-field">
            <label for="car_id">Xe</label>
            <select id="car_id" name="car_id">
                <option value="">Tất cả</option>
                @foreach($cars as $car)
                    <option value="{{ $car->car_id }}" @selected((string) $filters['car_id'] === (string) $car->car_id)>{{ $car->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="report-filter-field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="report-filter-field">
            <label for="rating">Sao</label>
            <select id="rating" name="rating">
                <option value="">Tất cả</option>
                @for($star = 5; $star >= 1; $star--)
                    <option value="{{ $star }}" @selected((string) $filters['rating'] === (string) $star)>{{ $star }} sao</option>
                @endfor
            </select>
        </div>
        <div class="report-filter-actions">
            <button type="submit">Lọc</button>
            <a href="{{ route('admin.reports.reviews') }}">Xóa lọc</a>
        </div>
    </form>

    <section class="review-report-stats">
        <div><span>Điểm trung bình</span><strong>{{ number_format($stats['avg_rating'], 1) }}</strong></div>
        <div><span>Tổng đánh giá</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div><span>Chờ duyệt</span><strong>{{ number_format($stats['pending']) }}</strong></div>
        <div><span>Đã duyệt</span><strong>{{ number_format($stats['approved']) }}</strong></div>
        <div><span>Bị từ chối</span><strong>{{ number_format($stats['rejected']) }}</strong></div>
        <div class="is-alert"><span>1-2 sao</span><strong>{{ number_format($stats['low_rating']) }}</strong></div>
        <div><span>Tích cực</span><strong>{{ number_format($stats['positive_rate'], 1) }}%</strong></div>
    </section>

    <div class="review-report-grid">
        <section class="report-panel">
            <h2>Phân bố sao</h2>
            <div class="rating-bars">
                @foreach([5, 4, 3, 2, 1] as $star)
                    @php
                        $count = (int) ($distribution[$star] ?? 0);
                        $percent = $stats['total'] > 0 ? round($count / $stats['total'] * 100) : 0;
                    @endphp
                    <div class="rating-bar-row">
                        <span>{{ $star }} sao</span>
                        <i><b style="width: {{ $percent }}%"></b></i>
                        <strong>{{ $count }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="report-panel">
            <h2>Đánh giá theo tháng</h2>
            <div class="mini-chart">
                @php $maxMonth = max(1, (int) collect($monthlyChart['counts'])->max()); @endphp
                @foreach($monthlyChart['labels'] as $index => $label)
                    @php $value = (int) ($monthlyChart['counts'][$index] ?? 0); @endphp
                    <div><span>{{ $label }}</span><i style="height: {{ max(6, round($value / $maxMonth * 110)) }}px"></i><strong>{{ $value }}</strong></div>
                @endforeach
            </div>
        </section>

        <section class="report-panel">
            <h2>Đánh giá theo hãng</h2>
            <div class="rating-bars">
                @php $maxBrand = max(1, (int) collect($brandChart['counts'])->max()); @endphp
                @forelse($brandChart['labels'] as $index => $label)
                    @php $count = (int) ($brandChart['counts'][$index] ?? 0); @endphp
                    <div class="rating-bar-row">
                        <span>{{ $label }}</span>
                        <i><b style="width: {{ round($count / $maxBrand * 100) }}%"></b></i>
                        <strong>{{ $count }}</strong>
                    </div>
                @empty
                    <p class="report-empty">Chưa có dữ liệu.</p>
                @endforelse
            </div>
        </section>

        <section class="report-panel">
            <h2>Top xe</h2>
            <div class="top-car-columns">
                <div>
                    <h3>Điểm cao</h3>
                    @forelse($topHighCars as $row)
                        <p><strong>{{ $row->name }}</strong><span>{{ number_format((float) $row->avg_rating, 1) }}/5 - {{ $row->reviews_count }} đánh giá</span></p>
                    @empty
                        <p class="report-empty">Chưa có dữ liệu.</p>
                    @endforelse
                </div>
                <div>
                    <h3>Điểm thấp</h3>
                    @forelse($topLowCars as $row)
                        <p><strong>{{ $row->name }}</strong><span>{{ number_format((float) $row->avg_rating, 1) }}/5 - {{ $row->reviews_count }} đánh giá</span></p>
                    @empty
                        <p class="report-empty">Chưa có dữ liệu.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    <section class="report-panel">
        <h2>Đánh giá xấu cần xử lý</h2>
        <div class="bad-review-list">
            @forelse($badReviews as $review)
                <a href="{{ route('admin.reviews.show', $review) }}">
                    <strong>{{ $review->starsText() }} - {{ $review->car?->name ?? 'Xe đã xóa' }}</strong>
                    <span>{{ $review->user?->name ?? 'Khách hàng' }} / {{ $review->statusLabel() }} / {{ $review->created_at?->format('d/m/Y H:i') }}</span>
                    <p>{{ \Illuminate\Support\Str::limit($review->comment ?: 'Không có nội dung.', 160) }}</p>
                </a>
            @empty
                <p class="report-empty">Chưa có đánh giá xấu trong bộ lọc hiện tại.</p>
            @endforelse
        </div>
    </section>

    <section class="report-panel">
        <h2>Đánh giá mới nhất</h2>
        <div class="report-table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Ngày</th>
                        <th>Khách</th>
                        <th>Xe</th>
                        <th>Điểm</th>
                        <th>Trạng thái</th>
                        <th>Nội dung</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $rev)
                        <tr>
                            <td>{{ $rev->created_at?->format('d/m/Y') }}</td>
                            <td>{{ $rev->user?->name ?? 'N/A' }}</td>
                            <td>{{ $rev->car?->name ?? 'N/A' }}</td>
                            <td><span class="report-stars">{{ $rev->starsText() }}</span></td>
                            <td>{{ $rev->statusLabel() }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($rev->comment ?: 'Không có nội dung.', 120) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="report-empty">Chưa có đánh giá phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($reviews->hasPages())
            <div class="report-pagination">{{ $reviews->links('pagination.lux') }}</div>
        @endif
    </section>
</div>
@endsection
