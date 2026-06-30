@extends('layouts.admin')

@section('title', 'Quản lý đánh giá')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reviews.css')
    @endif
@endpush

@section('content')
<div class="admin-reviews-page">
    <div class="reviews-admin-head">
        <div>
            <h1>Quản lý đánh giá</h1>
            <p>Marketing / Kiểm duyệt phản hồi khách hàng</p>
        </div>
        <div class="reviews-head-actions">
            @canany(['reports.view', 'reviews.view'])
                <a class="reviews-secondary" href="{{ route('admin.reports.reviews') }}">Báo cáo</a>
            @endcanany
            @can('reviews.export')
                <a class="reviews-primary" href="{{ route('admin.reviews.export', request()->query()) }}">Xuất Excel</a>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="reviews-alert is-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="reviews-alert is-error">{{ $errors->first() }}</div>
    @endif

    <section class="reviews-stats-grid">
        <div class="reviews-stat"><span>Tổng</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div class="reviews-stat"><span>Chờ duyệt</span><strong>{{ number_format($stats['pending']) }}</strong></div>
        <div class="reviews-stat"><span>Đã duyệt</span><strong>{{ number_format($stats['approved']) }}</strong></div>
        <div class="reviews-stat"><span>Bị báo cáo</span><strong>{{ number_format($stats['reported']) }}</strong></div>
        <div class="reviews-stat is-alert"><span>1-2 sao</span><strong>{{ number_format($stats['low_rating']) }}</strong></div>
        <div class="reviews-stat"><span>Điểm TB</span><strong>{{ $stats['avg_rating'] > 0 ? number_format($stats['avg_rating'], 1) : '0.0' }}</strong></div>
    </section>

    <form class="reviews-filter" method="get" action="{{ route('admin.reviews.index') }}">
        <div class="reviews-filter-field is-wide">
            <label for="q">Từ khóa</label>
            <input id="q" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Khách hàng, xe, tiêu đề, nội dung">
        </div>
        <div class="reviews-filter-field">
            <label for="status">Trạng thái</label>
            <select id="status" name="status">
                <option value="">Tất cả</option>
                @foreach($statusOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="reviews-filter-field">
            <label for="rating">Sao</label>
            <select id="rating" name="rating">
                <option value="">Tất cả</option>
                @for($star = 5; $star >= 1; $star--)
                    <option value="{{ $star }}" @selected((string) $filters['rating'] === (string) $star)>{{ $star }} sao</option>
                @endfor
            </select>
        </div>
        <div class="reviews-filter-field">
            <label for="verified_type">Xác minh</label>
            <select id="verified_type" name="verified_type">
                <option value="">Tất cả</option>
                @foreach($verifiedTypeOptions as $value => $label)
                    <option value="{{ $value }}" @selected($filters['verified_type'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="reviews-filter-field">
            <label for="brand_id">Hãng</label>
            <select id="brand_id" name="brand_id">
                <option value="">Tất cả</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->brand_id }}" @selected((string) $filters['brand_id'] === (string) $brand->brand_id)>{{ $brand->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="reviews-filter-field">
            <label for="model_id">Model</label>
            <select id="model_id" name="model_id">
                <option value="">Tất cả</option>
                @foreach($models as $model)
                    <option value="{{ $model->id }}" @selected((string) $filters['model_id'] === (string) $model->id)>{{ $model->brand?->name }} {{ $model->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="reviews-filter-field">
            <label for="car_id">Xe</label>
            <select id="car_id" name="car_id">
                <option value="">Tất cả</option>
                @foreach($cars as $car)
                    <option value="{{ $car->car_id }}" @selected((string) $filters['car_id'] === (string) $car->car_id)>{{ $car->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="reviews-filter-field">
            <label for="has_images">Ảnh</label>
            <select id="has_images" name="has_images">
                <option value="">Tất cả</option>
                <option value="1" @selected($filters['has_images'] === '1')>Có ảnh</option>
                <option value="0" @selected($filters['has_images'] === '0')>Không ảnh</option>
            </select>
        </div>
        <div class="reviews-filter-field">
            <label for="date_from">Từ ngày</label>
            <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] }}">
        </div>
        <div class="reviews-filter-field">
            <label for="date_to">Đến ngày</label>
            <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] }}">
        </div>
        <div class="reviews-filter-field">
            <label for="sort">Sắp xếp</label>
            <select id="sort" name="sort">
                <option value="latest" @selected($filters['sort'] === 'latest')>Mới nhất</option>
                <option value="oldest" @selected($filters['sort'] === 'oldest')>Cũ nhất</option>
                <option value="rating_desc" @selected($filters['sort'] === 'rating_desc')>Sao cao</option>
                <option value="rating_asc" @selected($filters['sort'] === 'rating_asc')>Sao thấp</option>
                <option value="reports_desc" @selected($filters['sort'] === 'reports_desc')>Báo cáo nhiều</option>
            </select>
        </div>
        <div class="reviews-filter-actions">
            <button type="submit">Lọc</button>
            <a href="{{ route('admin.reviews.index') }}">Xóa lọc</a>
        </div>
    </form>

    <section class="reviews-table-wrap">
        <table class="reviews-table">
            <thead>
                <tr>
                    <th>Ngày gửi</th>
                    <th>Khách hàng</th>
                    <th>Xe</th>
                    <th>Điểm</th>
                    <th>Nội dung</th>
                    <th>Trạng thái</th>
                    <th>Xác minh</th>
                    <th>Ảnh</th>
                    <th>Phản hồi</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reviews as $review)
                    <tr class="{{ $review->needsAttention() ? 'needs-attention' : '' }}">
                        <td class="reviews-muted">{{ $review->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <strong>{{ $review->user?->name ?? 'Khách hàng' }}</strong>
                            <span class="reviews-muted">{{ $review->user?->email }}</span>
                        </td>
                        <td>
                            <strong>{{ $review->car?->name ?? 'Xe đã xóa' }}</strong>
                            <span class="reviews-muted">{{ $review->car?->carModel?->brand?->name }} {{ $review->car?->carModel?->name }}</span>
                        </td>
                        <td><span class="reviews-stars">{{ $review->starsText() }}</span></td>
                        <td class="reviews-content">
                            @if($review->title)
                                <strong>{{ $review->title }}</strong>
                            @endif
                            <span>{{ \Illuminate\Support\Str::limit($review->comment ?: 'Không có nội dung.', 120) }}</span>
                            @if($review->needsAttention())
                                <em>Cần xử lý</em>
                            @endif
                        </td>
                        <td><span class="review-status {{ $review->statusBadgeClass() }}">{{ $review->statusLabel() }}</span></td>
                        <td><span class="review-verify">{{ $review->verifiedLabel() }}</span></td>
                        <td>{{ number_format((int) $review->images_count) }}</td>
                        <td>{{ $review->reply_content ? 'Đã phản hồi' : 'Chưa' }}</td>
                        <td>
                            <div class="reviews-actions">
                                <a href="{{ route('admin.reviews.show', $review) }}">Xem</a>
                                @can('reviews.moderate')
                                    @if(!$review->isApproved())
                                        <form method="post" action="{{ route('admin.reviews.approve', $review) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit">Duyệt</button>
                                        </form>
                                    @endif
                                    @if($review->isApproved())
                                        <form method="post" action="{{ route('admin.reviews.hide', $review) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit">Ẩn</button>
                                        </form>
                                        <form method="post" action="{{ route('admin.reviews.featured', $review) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit">{{ $review->is_featured ? 'Bỏ nổi bật' : 'Nổi bật' }}</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="reviews-empty">Chưa có đánh giá phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>

    @if($reviews->hasPages())
        <div class="reviews-pagination">{{ $reviews->links('pagination.lux') }}</div>
    @endif
</div>
@endsection
