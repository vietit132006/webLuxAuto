@extends('layouts.site')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-show.css')
    @endif
@endpush


@php
    $carModel = $car->carModel;
    $brandName = $carModel?->brand?->name ?? $car->brand?->name ?? 'Hãng khác';
    $modelName = $carModel?->name;
    $fullName = trim($brandName . ' ' . ($modelName ? $modelName . ' ' : '') . $car->name);
    $statusText = match ((int) $car->status) {
        2 => 'Đã đặt cọc',
        3 => 'Đã bán',
        default => 'Sẵn sàng',
    };
    $quickStatusText = match ((int) $car->status) {
        2 => 'Đã đặt cọc',
        3 => 'Đã bán',
        default => 'Xe mới 100%',
    };
    $statusClass = match ((int) $car->status) {
        2 => 'is-reserved',
        3 => 'is-sold',
        default => 'is-ready',
    };
    $canDepositCar = (int) $car->status === 1;
    $mileageText = is_null($car->mileage_km)
        ? 'Đang cập nhật'
        : number_format($car->mileage_km, 0, ',', '.') . ' km';
    $depositAmount = 20000000;
    $galleryImages = $car->images ?? collect();
    $youtubeId = '';

    if ($car->video_url) {
        preg_match(
            '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i',
            $car->video_url,
            $match
        );
        $youtubeId = $match[1] ?? '';
    }

    $roundedRating = (int) round($avgRating ?? 0);
@endphp

@section('title', $fullName)

@push('styles')
@endpush

@section('content')
<div class="detail-page">
    <section class="detail-hero">
        <div class="detail-wrap">
            <nav class="detail-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('home') }}">Trang chủ</a>
                <span>/</span>
                <a href="{{ route('cars.index') }}">Danh sách xe</a>
                <span>/</span>
                <span class="detail-breadcrumb__current">{{ $fullName }}</span>
            </nav>

            <div class="detail-hero__grid">
                <div>
                    <div class="detail-kicker">{{ $brandName }}{{ $modelName ? ' - ' . $modelName : '' }}</div>
                    <h1 class="detail-title">{{ $car->name }}</h1>
                    <div class="detail-subline">
                        <span class="status-pill {{ $statusClass }}">{{ $statusText }}</span>
                        @if($car->is_featured)
                            <span class="feature-pill">Xe nổi bật</span>
                        @endif
                        <span>Đời {{ $car->year ?? 'đang cập nhật' }}</span>
                        <span>{{ $mileageText }}</span>
                    </div>
                </div>

                <div class="detail-price">
                    <span>Giá niêm yết</span>
                    <strong>{{ number_format($car->price, 0, ',', '.') }} VNĐ</strong>
                </div>
            </div>
        </div>
    </section>

    <div class="detail-wrap">
        <div class="detail-layout">
            <div class="media-stack">
                <div class="media-main">
                    @if($car->image)
                        <img id="detail-main-image" src="{{ asset('storage/' . $car->image) }}" alt="{{ $fullName }}">
                    @else
                        <div class="media-empty">
                            <div>
                                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 6 8.25A2.25 2.25 0 0 1 8.068 6.9h7.864A2.25 2.25 0 0 1 18 8.25l2.25 5.25M5.25 13.5h13.5m-12 0v3.75m10.5-3.75v3.75M7.5 17.25h.008v.008H7.5v-.008Zm9 0h.008v.008H16.5v-.008Z" />
                                </svg>
                                <div>Chưa có hình ảnh</div>
                            </div>
                        </div>
                    @endif
                </div>

                @if($car->image || $galleryImages->isNotEmpty())
                    <div class="media-thumbs" aria-label="Album ảnh xe">
                        @if($car->image)
                            <button type="button" class="media-thumb is-active" data-image="{{ asset('storage/' . $car->image) }}" data-alt="{{ $fullName }}">
                                <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $fullName }}" loading="lazy">
                            </button>
                        @endif
                        @foreach($galleryImages as $image)
                            @if($image->image_path)
                                <button type="button" class="media-thumb" data-image="{{ asset('storage/' . $image->image_path) }}" data-alt="{{ $fullName }} - ảnh {{ $loop->iteration + 1 }}">
                                    <img src="{{ asset('storage/' . $image->image_path) }}" alt="{{ $fullName }} - ảnh {{ $loop->iteration + 1 }}" loading="lazy">
                                </button>
                            @endif
                        @endforeach
                    </div>
                @endif

                @if($car->description)
                    <section class="content-panel">
                        <h2>Thông tin chi tiết</h2>
                        <div class="description-copy">{!! nl2br(e($car->description)) !!}</div>
                    </section>
                @endif

                @if($car->video_file || $youtubeId)
                    <section class="content-panel">
                        <h2>Video trải nghiệm</h2>
                        <div class="video-frame">
                            @if($car->video_file)
                                <video controls poster="{{ $car->image ? asset('storage/' . $car->image) : '' }}">
                                    <source src="{{ asset('storage/' . $car->video_file) }}" type="video/mp4">
                                </video>
                            @elseif($youtubeId)
                                <iframe
                                    src="https://www.youtube.com/embed/{{ $youtubeId }}"
                                    title="Video trải nghiệm {{ $fullName }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                            @endif
                        </div>
                    </section>
                @endif
            </div>

            <aside class="detail-side">
                <section class="detail-card">
                    @if(($reviewCount ?? 0) > 0)
                        <div class="rating-summary">
                            <span class="stars" aria-hidden="true">{{ str_repeat('★', $roundedRating) }}{{ str_repeat('☆', max(0, 5 - $roundedRating)) }}</span>
                            <strong>{{ number_format((float) ($avgRating ?? 0), 1) }}/5</strong>
                            <span>{{ $reviewCount }} đánh giá</span>
                            <a href="#danh-gia">Xem đánh giá</a>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert-box">
                            <strong>Chú ý</strong>
                            <ul>
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <h2>Thông số nhanh</h2>
                    <div class="spec-grid">
                        <div class="spec-item">
                            <span class="spec-label">Năm sản xuất</span>
                            <span class="spec-value">{{ $car->year ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Tình trạng</span>
                            <span class="spec-value">{{ $quickStatusText }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Số km</span>
                            <span class="spec-value" title="{{ $mileageText }}">{{ $mileageText }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Xuất xứ</span>
                            <span class="spec-value">{{ $carModel?->origin ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Kiểu dáng</span>
                            <span class="spec-value">{{ $carModel?->body_type ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Hộp số</span>
                            <span class="spec-value" title="{{ $carModel?->transmission ?? 'Cập nhật sau' }}">{{ $carModel?->transmission ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Động cơ</span>
                            <span class="spec-value" title="{{ $carModel?->engine ?? 'Cập nhật sau' }}">{{ $carModel?->engine ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Nhiên liệu</span>
                            <span class="spec-value">{{ $carModel?->fuel_type ?? 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Màu ngoại thất</span>
                            <span class="spec-value">{{ $car->color ?: 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Màu nội thất</span>
                            <span class="spec-value">{{ $car->interior_color ?: 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Số chỗ ngồi</span>
                            <span class="spec-value">{{ $carModel?->seats ? $carModel->seats . ' chỗ' : 'Cập nhật sau' }}</span>
                        </div>
                        <div class="spec-item">
                            <span class="spec-label">Dẫn động</span>
                            <span class="spec-value">{{ $carModel?->drive_type ?? 'Cập nhật sau' }}</span>
                        </div>
                    </div>
                </section>

                <section class="deposit-card">
                    <p class="deposit-card__copy">
                        Phí giữ xe: <strong>{{ number_format($depositAmount, 0, ',', '.') }} VNĐ</strong>. Lux Auto xác nhận thông tin xe trước khi hoàn tất giao dịch.
                    </p>

                    @if($canDepositCar)
                        @auth
                            <form action="{{ route('order.deposit', $car->car_id) }}" method="POST" onsubmit="return confirm('Bạn xác nhận muốn đặt cọc {{ number_format($depositAmount, 0, ',', '.') }} VNĐ để giữ chiếc {{ $car->name }} này chứ?');">
                                @csrf
                                <button type="submit" class="detail-btn detail-btn--primary">
                                    Đặt cọc ngay
                                    <span class="detail-btn__sub">{{ number_format($depositAmount, 0, ',', '.') }} VNĐ</span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="detail-btn detail-btn--primary">
                                Đăng nhập để đặt cọc
                            </a>
                        @endauth
                    @else
                        <button type="button" class="detail-btn detail-btn--disabled" disabled>{{ $statusText }}</button>
                    @endif
                </section>

                <div class="detail-actions">
                    <button type="button" class="detail-btn detail-btn--ghost" id="btn-add-compare" data-car-id="{{ $car->car_id }}">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75v16.5m9-16.5v16.5M3.75 8.25h16.5M3.75 15.75h16.5" />
                        </svg>
                        Thêm vào so sánh
                    </button>
                    <a href="{{ route('ticket.create', ['type' => 'test_drive', 'car_id' => $car->car_id]) }}" class="detail-btn detail-btn--ghost">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 6 8.25A2.25 2.25 0 0 1 8.068 6.9h7.864A2.25 2.25 0 0 1 18 8.25l2.25 5.25M5.25 13.5h13.5m-12 0v3.75m10.5-3.75v3.75" />
                        </svg>
                        Đặt lịch lái thử
                    </a>
                    <a href="{{ route('ticket.create') }}" class="detail-btn detail-btn--ghost">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-5l-5 5v-5Z" />
                        </svg>
                        Yêu cầu hỗ trợ
                    </a>
                </div>
            </aside>
        </div>

        <section id="danh-gia" class="reviews-section">
            <div class="reviews-head">
                <div>
                    <h2>Đánh giá từ khách hàng</h2>
                    <p>{{ ($reviewCount ?? 0) > 0 ? 'Nhận xét từ khách đã trải nghiệm hoặc đặt cọc xe.' : 'Chưa có đánh giá nào cho xe này.' }}</p>
                </div>

                @if(($reviewCount ?? 0) > 0)
                    <div class="reviews-score">
                        <strong>{{ number_format((float) ($avgRating ?? 0), 1) }}</strong>
                        <span class="stars" aria-hidden="true">{{ str_repeat('★', $roundedRating) }}{{ str_repeat('☆', max(0, 5 - $roundedRating)) }}</span>
                        <div class="reviews-score__meta">{{ $reviewCount }} lượt đánh giá</div>
                    </div>
                @endif
            </div>

            @if(session('review_success'))
                <div class="review-flash" role="status">{{ session('review_success') }}</div>
            @endif

            @auth
                @if(auth()->user()->role === 'customer')
                    <div class="review-form">
                        <form action="{{ route('cars.reviews.store', $car->car_id) }}" method="post">
                            @csrf
                            @error('review')
                                <p class="form-error">{{ $message }}</p>
                            @enderror

                            <label for="rating">Điểm đánh giá</label>
                            <select name="rating" id="rating" required>
                                @for($r = 5; $r >= 1; $r--)
                                    <option value="{{ $r }}" @selected(old('rating', $userReview?->rating ?? 5) == $r)>{{ $r }} sao</option>
                                @endfor
                            </select>
                            @error('rating')
                                <p class="form-error">{{ $message }}</p>
                            @enderror

                            <label for="comment" class="review-comment-label">Nhận xét</label>
                            <textarea name="comment" id="comment" maxlength="2000" placeholder="Chia sẻ trải nghiệm của bạn về xe này...">{{ old('comment', $userReview?->comment ?? '') }}</textarea>
                            @error('comment')
                                <p class="form-error">{{ $message }}</p>
                            @enderror

                            @if($canReview ?? false)
                                <button type="submit" class="detail-btn detail-btn--primary review-submit">
                                    {{ $userReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}
                                </button>
                                @if($userReview)
                                    <p class="form-hint">Bạn đã đánh giá trước đó, gửi lại để chỉnh sửa nội dung.</p>
                                @endif
                            @else
                                <p class="form-hint form-hint--warning">Bạn cần đặt lịch lái thử hoặc đặt cọc xe này trước khi gửi đánh giá.</p>
                            @endif
                        </form>
                    </div>
                @else
                    <p class="reviews-empty">Tài khoản nhân viên chỉ xem đánh giá của khách.</p>
                @endif
            @else
                <p class="reviews-empty"><a href="{{ route('login') }}">Đăng nhập</a> để gửi đánh giá cho sản phẩm này.</p>
            @endauth

            <div class="reviews-list">
                @forelse($reviews ?? [] as $review)
                    <article class="review-item">
                        <div class="review-item__head">
                            <span class="review-item__name">{{ $review->user->name ?? 'Khách hàng' }}</span>
                            <span class="review-item__date">{{ $review->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="stars" aria-label="{{ $review->rating }} trên 5 sao">
                            @for($s = 1; $s <= 5; $s++)
                                {{ $s <= (int) $review->rating ? '★' : '☆' }}
                            @endfor
                        </div>
                        @if($review->comment)
                            <p class="review-item__text">{{ $review->comment }}</p>
                        @else
                            <p class="review-item__text muted-italic">Không có nhận xét.</p>
                        @endif
                    </article>
                @empty
                @endforelse
            </div>

            @if(isset($reviews) && $reviews->hasPages())
                <div class="pagination-wrap">{{ $reviews->links('pagination.lux') }}</div>
            @endif
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var mainImage = document.getElementById('detail-main-image');
    var thumbs = document.querySelectorAll('.media-thumb');

    thumbs.forEach(function (thumb) {
        thumb.addEventListener('click', function () {
            if (!mainImage) {
                return;
            }

            mainImage.src = thumb.getAttribute('data-image');
            mainImage.alt = thumb.getAttribute('data-alt') || mainImage.alt;
            thumbs.forEach(function (item) {
                item.classList.remove('is-active');
            });
            thumb.classList.add('is-active');
        });
    });

    var KEY = 'lux_compare_ids';
    var btn = document.getElementById('btn-add-compare');

    if (!btn) {
        return;
    }

    btn.addEventListener('click', function () {
        var id = parseInt(btn.getAttribute('data-car-id'), 10);
        var raw = localStorage.getItem(KEY) || '';
        var arr = raw ? raw.split(',').map(function (x) {
            return parseInt(x, 10);
        }).filter(Boolean) : [];

        if (!id) {
            return;
        }

        if (arr.indexOf(id) !== -1) {
            alert('Xe này đã có trong danh sách so sánh.');
            return;
        }

        if (arr.length >= 4) {
            alert('Chỉ có thể so sánh tối đa 4 xe.');
            return;
        }

        arr.push(id);
        localStorage.setItem(KEY, arr.join(','));
        window.location.href = @json(route('compare.index')) + '?ids=' + encodeURIComponent(arr.join(','));
    });
})();
</script>
@endpush