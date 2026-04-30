@extends('layouts.site')

@section('title', $car->brand->name . ' ' . $car->name)

@section('content')
    <style>
        /* --- BREADCRUMB --- */
        .breadcrumb {
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: var(--muted);
        }

        .breadcrumb a {
            color: var(--text);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            color: var(--accent);
        }

        /* --- LAYOUT CHÍNH --- */
        .product-detail {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2.5rem;
            background: var(--surface);
            padding: 0;
            border-radius: 0;
        }

        @media (min-width: 992px) {
            .product-detail {
                grid-template-columns: 1.4fr 1fr;
            }
        }

        /* --- BÊN TRÁI: HÌNH ẢNH --- */
        .pd-image-wrapper {
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            aspect-ratio: 16 / 10;
            background: #0a0d12;
            position: relative;
        }

        .pd-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
        }

        .badge-hot {
            position: absolute;
            top: 15px;
            left: 15px;
            background: #e63946;
            color: #fff;
            padding: 0.4rem 1rem;
            border-radius: 30px;
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            box-shadow: 0 4px 10px rgba(230, 57, 70, 0.4);
        }

        /* --- BÊN PHẢI: THÔNG TIN --- */
        .pd-info {
            display: flex;
            flex-direction: column;
        }

        .pd-brand {
            font-size: 1rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .pd-title {
            font-size: 2.2rem;
            font-weight: 800;
            color: var(--text);
            margin: 0 0 1rem;
            line-height: 1.2;
        }

        .pd-price {
            font-size: 2rem;
            font-weight: 700;
            color: var(--accent);
            /* Giả sử màu accent của bạn là màu vàng kim/cam */
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* --- LƯỚI THÔNG SỐ (KIỂU CARD NHỎ) --- */
        .pd-specs-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .spec-card {
            background: rgba(255, 255, 255, 0.03);
            /* Chỉnh lại màu này nếu site của bạn nền sáng */
            border: 1px solid var(--border);
            padding: 1rem;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .spec-card .label {
            font-size: 0.85rem;
            color: var(--muted);
        }

        .spec-card .value {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--text);
        }

        /* --- NÚT LIÊN HỆ --- */
        .pd-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: auto;
        }

        .btn-primary-cta {
            background: var(--accent);
            color: #0c0f14;
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 700;
            text-decoration: none;
            text-transform: uppercase;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(201, 169, 98, 0.3);
            /* Chỉnh màu shadow theo var(--accent) của bạn */
        }

        .btn-primary-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(201, 169, 98, 0.4);
        }

        .btn-secondary-cta {
            background: transparent;
            color: var(--text);
            border: 2px solid var(--border);
            text-align: center;
            padding: 1rem;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s;
        }

        .btn-secondary-cta:hover {
            border-color: var(--text);
        }

        /* --- MÔ TẢ CHI TIẾT DƯỚI CÙNG --- */
        .pd-description-box {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
        }

        .pd-description-box h3 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--text);
        }

        .pd-desc-content {
            line-height: 1.8;
            color: var(--text);
            font-size: 1.05rem;
        }

        /* --- CSS Nút Đặt Cọc Lux Auto --- */
        .deposit-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-top: 2rem;
            text-align: center;
        }

        .btn-deposit {
            width: 100%;
            background: linear-gradient(135deg, var(--accent) 0%, #b89453 100%);
            color: #000;
            border: none;
            padding: 1.2rem;
            border-radius: 8px;
            font-size: 1.25rem;
            font-weight: 800;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 10px 20px rgba(201, 169, 98, 0.2);
            display: block;
            text-decoration: none;
        }

        .btn-deposit:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 25px rgba(201, 169, 98, 0.4);
            background: linear-gradient(135deg, #fcebb6 0%, var(--accent) 100%);
        }

        .btn-deposit.login-to-book {
            background: #1f2937;
            color: var(--text);
            box-shadow: none;
        }

        .btn-deposit.login-to-book:hover {
            background: #374151;
        }

        .deposit-policy {
            color: var(--muted);
            font-size: 0.85rem;
            margin-top: 1rem;
            line-height: 1.5;
        }

        /* --- ĐÁNH GIÁ --- */
        .pd-rating-summary {
            margin: -1rem 0 1.25rem;
            font-size: 1rem;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: 0.35rem;
            flex-wrap: wrap;
        }

        .pd-rating-summary .stars-fill {
            color: #fbbf24;
            letter-spacing: 1px;
            font-size: 1.05rem;
        }

        .reviews-section {
            margin-top: 2.5rem;
            padding-top: 2rem;
            border-top: 1px solid var(--border);
            scroll-margin-top: 88px;
        }

        .reviews-section h3 {
            font-size: 1.35rem;
            margin: 0 0 1rem;
            color: var(--text);
        }

        .reviews-summary-bar {
            background: rgba(251, 191, 36, 0.08);
            border: 1px solid rgba(251, 191, 36, 0.25);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            margin-bottom: 1.5rem;
            display: flex;
            flex-wrap: wrap;
            align-items: baseline;
            gap: 0.75rem 1.5rem;
        }

        .reviews-summary-bar .big {
            font-size: 2rem;
            font-weight: 800;
            color: var(--accent);
            line-height: 1;
        }

        .reviews-summary-bar .sub {
            font-size: 0.9rem;
            color: var(--muted);
        }

        .review-flash {
            background: #d1fae5;
            color: #065f46;
            padding: 0.85rem 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            font-weight: 600;
            border: 1px solid #34d399;
        }

        .review-form {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.75rem;
        }

        .review-form label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.4rem;
            font-size: 0.9rem;
        }

        .review-form select,
        .review-form textarea {
            width: 100%;
            max-width: 420px;
            padding: 0.55rem 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: #0a0d12;
            color: var(--text);
            font-family: inherit;
        }

        .review-form textarea {
            min-height: 100px;
            resize: vertical;
            max-width: 100%;
        }

        .review-form .btn-send {
            margin-top: 0.85rem;
            padding: 0.65rem 1.35rem;
            border-radius: 8px;
            border: none;
            background: var(--accent);
            color: #0c0f14;
            font-weight: 700;
            cursor: pointer;
            font-family: inherit;
        }

        .review-form .hint {
            font-size: 0.85rem;
            color: var(--muted);
            margin-top: 0.5rem;
        }

        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .review-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1rem 1.15rem;
        }

        .review-item__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 1rem;
            flex-wrap: wrap;
            margin-bottom: 0.5rem;
        }

        .review-item__name {
            font-weight: 700;
            color: var(--text);
        }

        .review-item__date {
            font-size: 0.8rem;
            color: var(--muted);
        }

        .review-item__stars {
            color: #fbbf24;
            letter-spacing: 2px;
            font-size: 0.95rem;
        }

        .review-item__text {
            color: var(--text);
            line-height: 1.6;
            font-size: 0.95rem;
            margin: 0;
        }

        .reviews-empty {
            color: var(--muted);
            padding: 1rem 0;
            font-size: 0.95rem;
        }

        .pagination-wrap {
            margin-top: 1.25rem;
            display: flex;
            justify-content: center;
        }
    </style>

    <div class="wrap">
        <div class="breadcrumb">
            <a href="{{ route('home') }}">Trang chủ</a> &nbsp; / &nbsp;
            <a href="{{ route('cars.index') }}">Danh sách xe</a> &nbsp; / &nbsp;
            <span style="color: var(--accent)">{{ $car->brand->name ?? 'Hãng khác' }} {{ $car->name }}</span>
        </div>

        <div class="product-detail">
            <div class="pd-left">
                <div class="pd-image-wrapper">
                    @if ($car->is_featured)
                        <div class="badge-hot">Xe nổi bật</div>
                    @endif

                    @if ($car->image)
                        <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->name }}">
                    @else
                        <img src="https://via.placeholder.com/800x500?text=Chua+co+hinh+anh" alt="Chưa có hình">
                    @endif
                </div>
            </div>

            <div class="pd-right">
                <div class="pd-info">
                    <div class="pd-brand">{{ $car->brand->name ?? 'Hãng xe' }}</div>
                    <h1 class="pd-title">{{ $car->name }}</h1>
                    @if (isset($car->status))
                        <div style="margin-top: 0.5rem; margin-bottom: 1.5rem;">
                            <span
                                style="display: inline-block; background: {{ $car->status == 1 ? 'var(--accent)' : '#4b5563' }}; color: {{ $car->status == 1 ? '#000' : '#fff' }}; padding: 0.4rem 1rem; border-radius: 50px; font-size: 0.9rem; font-weight: bold;">
                                <i style="margin-right: 5px;">{{ $car->status == 1 ? '✨' : '🔄' }}</i>
                                {{ $car->status == 1 ? 'Mới 100%' : 'Xe lướt (Cũ)' }}
                            </span>
                        </div>
                    @endif
                    @if (($reviewCount ?? 0) > 0)
                        <div class="pd-rating-summary">
                            <span class="stars-fill"
                                aria-hidden="true">{{ str_repeat('★', (int) round($avgRating ?? 0)) }}{{ str_repeat('☆', max(0, 5 - (int) round($avgRating ?? 0))) }}</span>
                            <strong
                                style="color: var(--text);">{{ number_format((float) ($avgRating ?? 0), 1) }}/5</strong>
                            <span>— {{ $reviewCount }} đánh giá</span>
                            <a href="#danh-gia" style="font-size: 0.85rem; font-weight: 600;">Xem chi tiết ↓</a>
                        </div>
                    @endif

                    <div class="pd-price">
                        {{ number_format($car->price, 0, ',', '.') }} VNĐ
                    </div>
                    @if ($errors->any())
                        <div
                            style="background: #fee2e2; border: 1px solid #ef4444; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-top: 1rem; margin-bottom: 1rem;">
                            <strong style="display: block; margin-bottom: 0.5rem;">⚠️ Chú ý:</strong>
                            <ul style="margin: 0; padding-left: 1.5rem;">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <div class="deposit-box">
                        @auth
                            <form action="{{ route('order.deposit', $car->car_id) }}" method="POST"
                                onsubmit="return confirm('Bạn xác nhận muốn đặt cọc 20.000.000 VNĐ để giữ chiếc {{ $car->name }} này chứ?');">
                                @csrf
                                <button type="submit" class="btn-deposit">
                                    ĐẶT CỌC NGAY
                                    <span
                                        style="display:block; font-weight:500; font-size:0.9rem; margin-top:5px; color: rgba(0,0,0,0.7);">
                                        Phí giữ xe: 20.000.000 VNĐ
                                    </span>
                                </button>
                            </form>
                        @else
                            <a href="{{ route('login') }}" class="btn-deposit login-to-book">
                                ĐĂNG NHẬP ĐỂ ĐẶT CỌC
                                <span
                                    style="display:block; font-weight:500; font-size:0.9rem; margin-top:5px; color: var(--muted);">
                                    Vui lòng đăng nhập tài khoản của bạn
                                </span>
                            </a>
                        @endauth

                        <div class="deposit-policy">
                            🛡️ Xe sẽ được giữ chân trong 24h kể từ khi thanh toán tiền cọc thành công. Lux Auto cam kết
                            hoàn tiền 100% nếu xe không đúng mô tả.
                        </div>
                    </div>

                    <div class="pd-specs-grid">

                        <div class="spec-card">
                            <span class="label">Năm sản xuất</span>
                            <span class="value">{{ $car->year ?? 'Cập nhật sau' }}</span>
                        </div>

                        <div class="spec-card">
                            <span class="label">Đã đi (Odo)</span>
                            <span class="value">
                                @if ($car->mileage_km)
                                    {{ number_format($car->mileage_km, 0, ',', '.') }} km
                                @else
                                    {{ isset($car->status) && $car->status == 1 ? 'Xe mới 100%' : 'Chưa cập nhật' }}
                                @endif
                            </span>
                        </div>

                        <div class="spec-card">
                            <span class="label">Nhiên liệu</span>
                            <span class="value">{{ $car->fuel ?? 'Cập nhật sau' }}</span>
                        </div>

                        <div class="spec-card">
                            <span class="label">Hộp số</span>
                            <span class="value">{{ $car->transmission ?? 'Cập nhật sau' }}</span>
                        </div>

                        @if ($car->color)
                            <div class="spec-card">
                                <span class="label">Màu ngoại thất</span>
                                <span class="value">{{ $car->color }}</span>
                            </div>
                        @endif

                    </div>

                    <div class="pd-actions">
                        <button type="button" class="btn-secondary-cta" id="btn-add-compare"
                            data-car-id="{{ $car->car_id }}" style="cursor: pointer; font-family: inherit;">
                            Thêm vào so sánh
                        </button>

                        <a href="{{ route('ticket.create', ['type' => 'test_drive', 'car_id' => $car->car_id]) }}"
                            class="btn-secondary-cta">✉️ Đặt lịch lái thử</a>
                        <a href="{{ route('ticket.create') }}" class="btn-secondary-cta">Yêu cầu hỗ trợ</a>
                    </div>
                </div>
            </div>
        </div>

        @if ($car->description)
            <div class="pd-description-box">
                <h3>Thông tin chi tiết</h3>
                <div class="pd-desc-content">
                    {!! nl2br(e($car->description)) !!}
                </div>
            </div>
        @endif

        <section id="danh-gia" class="reviews-section">
            <h3>Đánh giá từ khách hàng</h3>

            @if (($reviewCount ?? 0) > 0)
                <div class="reviews-summary-bar">
                    <span class="big">{{ number_format((float) ($avgRating ?? 0), 1) }}</span>
                    <div>
                        <div style="color: #fbbf24; letter-spacing: 3px; font-size: 1.1rem;">
                            @for ($s = 1; $s <= 5; $s++)
                                {{ $s <= round($avgRating ?? 0) ? '★' : '☆' }}
                            @endfor
                        </div>
                        <div class="sub">{{ $reviewCount }} lượt đánh giá</div>
                    </div>
                </div>
            @else
                <p class="reviews-empty">Chưa có đánh giá nào cho xe này. Hãy là người đầu tiên chia sẻ trải nghiệm.</p>
            @endif

            @if (session('review_success'))
                <div class="review-flash" role="status">✓ {{ session('review_success') }}</div>
            @endif

            @auth
                @if (auth()->user()->role === 'customer')
                    <div class="review-form">
                        <form action="{{ route('cars.reviews.store', $car->car_id) }}" method="post">
                            @csrf
                            @error('review')
                                <p style="color:#f87171;font-size:0.9rem;margin:0 0 0.75rem;font-weight:600;">{{ $message }}
                                </p>
                            @enderror
                            <label for="rating">Điểm đánh giá</label>
                            <select name="rating" id="rating" required>
                                @for ($r = 5; $r >= 1; $r--)
                                    <option value="{{ $r }}" @selected(old('rating', $userReview?->rating ?? 5) == $r)>{{ $r }} sao
                                    </option>
                                @endfor
                            </select>
                            @error('rating')
                                <p style="color:#f87171;font-size:0.85rem;margin:0.35rem 0 0;">{{ $message }}</p>
                            @enderror

                            <label for="comment" style="margin-top: 1rem;">Nhận xét (tuỳ chọn)</label>
                            <textarea name="comment" id="comment" maxlength="2000" placeholder="Chia sẻ trải nghiệm của bạn về xe này…">{{ old('comment', $userReview?->comment ?? '') }}</textarea>
                            @error('comment')
                                <p style="color:#f87171;font-size:0.85rem;margin:0.35rem 0 0;">{{ $message }}</p>
                            @enderror

                            @if ($canReview ?? false)
                                <button type="submit"
                                    class="btn-send">{{ $userReview ? 'Cập nhật đánh giá' : 'Gửi đánh giá' }}</button>
                                @if ($userReview)
                                    <p class="hint">Bạn đã đánh giá trước đó — gửi lại để chỉnh sửa.</p>
                                @else
                                    <p class="hint">Chỉ khách đã đặt cọc hoặc mua xe mới có thể gửi đánh giá.</p>
                                @endif
                            @else
                                <p class="hint" style="margin-top: 0.85rem; color: #fbbf24;">
                                    Bạn cần đặt lịch lái thử hoặc đặt cọc xe này trước khi gửi đánh giá.
                                </p>
                            @endif
                        </form>
                    </div>
                @else
                    <p style="color: var(--muted); font-size: 0.9rem; margin-bottom: 1.25rem;">Tài khoản nhân viên chỉ xem đánh
                        giá của khách.</p>
                @endif
            @else
                <p style="margin-bottom: 1.25rem;">
                    <a href="{{ route('login') }}" style="font-weight: 600;">Đăng nhập</a>
                    để gửi đánh giá cho sản phẩm này.
                </p>
            @endauth

            <div class="reviews-list">
                @forelse($reviews ?? [] as $review)
                    <article class="review-item">
                        <div class="review-item__head">
                            <span class="review-item__name">{{ $review->user->name ?? 'Khách hàng' }}</span>
                            <span class="review-item__date">{{ $review->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="review-item__stars" aria-label="{{ $review->rating }} trên 5 sao">
                            @for ($s = 1; $s <= 5; $s++)
                                {{ $s <= (int) $review->rating ? '★' : '☆' }}
                            @endfor
                        </div>
                        @if ($review->comment)
                            <p class="review-item__text">{{ $review->comment }}</p>
                        @else
                            <p class="review-item__text" style="color: var(--muted); font-style: italic;">(Không có nhận
                                xét)</p>
                        @endif
                    </article>
                @empty
                @endforelse
            </div>

            @if (isset($reviews) && $reviews->hasPages())
                <div class="pagination-wrap">{{ $reviews->links('pagination.lux') }}</div>
            @endif
        </section>
    </div>
    @push('scripts')
        <script>
            (function() {
                var KEY = 'lux_compare_ids';
                var btn = document.getElementById('btn-add-compare');
                if (!btn) return;
                btn.addEventListener('click', function() {
                    var id = parseInt(btn.getAttribute('data-car-id'), 10);
                    if (!id) return;
                    var raw = localStorage.getItem(KEY) || '';
                    var arr = raw ? raw.split(',').map(function(x) {
                        return parseInt(x, 10);
                    }).filter(Boolean) : [];
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
                    window.location.href = @json(route('compare.index')) + '?ids=' + encodeURIComponent(arr.join(
                        ','));
                });
            })();
        </script>
    @endpush
@endsection
