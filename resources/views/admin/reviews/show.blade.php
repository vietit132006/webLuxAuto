@extends('layouts.admin')

@section('title', 'Chi tiết đánh giá')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-reviews.css')
    @endif
@endpush

@section('content')
<div class="admin-reviews-page">
    <div class="reviews-admin-head">
        <div>
            <h1>Chi tiết đánh giá</h1>
            <p>{{ $review->user?->name ?? 'Khách hàng' }} / {{ $review->car?->name ?? 'Xe đã xóa' }}</p>
        </div>
        <div class="reviews-head-actions">
            <a class="reviews-secondary" href="{{ route('admin.reviews.index') }}">Quay lại</a>
            @can('reviews.delete')
                <form method="post" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Xóa đánh giá này?');">
                    @csrf
                    @method('DELETE')
                    <button class="reviews-danger" type="submit">Xóa</button>
                </form>
            @endcan
        </div>
    </div>

    @if(session('success'))
        <div class="reviews-alert is-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="reviews-alert is-error">{{ $errors->first() }}</div>
    @endif

    <div class="review-detail-layout">
        <section class="review-detail-main">
            <div class="review-detail-card">
                <div class="review-detail-title">
                    <div>
                        <span class="reviews-stars">{{ $review->starsText() }}</span>
                        @if($review->needsAttention())
                            <em>Cần xử lý</em>
                        @endif
                    </div>
                    <span class="review-status {{ $review->statusBadgeClass() }}">{{ $review->statusLabel() }}</span>
                </div>

                <h2>{{ $review->title ?: 'Không có tiêu đề' }}</h2>
                <p class="review-detail-comment">{{ $review->comment ?: 'Không có nội dung.' }}</p>

                @if($review->images->isNotEmpty())
                    <div class="review-detail-gallery">
                        @foreach($review->images as $image)
                            <a href="{{ $image->imageUrl() }}" target="_blank" rel="noopener">
                                <img src="{{ $image->imageUrl() }}" alt="Ảnh đánh giá {{ $loop->iteration }}">
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="review-detail-card">
                <h2>Thông tin liên quan</h2>
                <dl class="review-detail-grid">
                    <div><dt>Khách hàng</dt><dd>{{ $review->user?->name ?? 'N/A' }}</dd></div>
                    <div><dt>Email</dt><dd>{{ $review->user?->email ?? 'N/A' }}</dd></div>
                    <div><dt>Xe</dt><dd>{{ $review->car?->name ?? 'N/A' }}</dd></div>
                    <div><dt>Hãng / model</dt><dd>{{ trim(($review->car?->carModel?->brand?->name ?? '') . ' ' . ($review->car?->carModel?->name ?? '')) ?: 'N/A' }}</dd></div>
                    <div><dt>Xác minh</dt><dd>{{ $review->verifiedLabel() }}</dd></div>
                    <div><dt>Ngày gửi</dt><dd>{{ $review->created_at?->format('d/m/Y H:i') }}</dd></div>
                    <div><dt>Đơn hàng</dt><dd>{{ $review->order ? $review->order->display_code : 'N/A' }}</dd></div>
                    <div><dt>Lái thử</dt><dd>{{ $review->ticket ? $review->ticket->display_code : 'N/A' }}</dd></div>
                    <div><dt>Dịch vụ</dt><dd>{{ $review->serviceRecord?->record_code ?? 'N/A' }}</dd></div>
                </dl>
            </div>

            <div class="review-detail-card">
                <h2>Lịch sử xử lý</h2>
                <div class="review-timeline">
                    <div><strong>Gửi đánh giá</strong><span>{{ $review->created_at?->format('d/m/Y H:i') }}</span></div>
                    @if($review->approved_at)
                        <div><strong>Duyệt bởi {{ $review->approvedBy?->name ?? 'N/A' }}</strong><span>{{ $review->approved_at?->format('d/m/Y H:i') }}</span></div>
                    @endif
                    @if($review->rejected_at)
                        <div><strong>Từ chối bởi {{ $review->rejectedBy?->name ?? 'N/A' }}</strong><span>{{ $review->rejected_at?->format('d/m/Y H:i') }}</span><p>{{ $review->rejected_reason }}</p></div>
                    @endif
                    @if($review->replied_at)
                        <div><strong>Phản hồi bởi {{ $review->repliedBy?->name ?? 'N/A' }}</strong><span>{{ $review->replied_at?->format('d/m/Y H:i') }}</span><p>{{ $review->reply_content }}</p></div>
                    @endif
                </div>
            </div>
        </section>

        <aside class="review-detail-side">
            @can('reviews.moderate')
                <div class="review-detail-card">
                    <h2>Kiểm duyệt</h2>
                    <div class="review-action-stack">
                        <form method="post" action="{{ route('admin.reviews.approve', $review) }}">
                            @csrf
                            @method('PATCH')
                            <button class="reviews-primary" type="submit">Duyệt đánh giá</button>
                        </form>
                        <form method="post" action="{{ route('admin.reviews.hide', $review) }}">
                            @csrf
                            @method('PATCH')
                            <button class="reviews-secondary" type="submit">Ẩn đánh giá</button>
                        </form>
                        @if($review->isApproved())
                            <form method="post" action="{{ route('admin.reviews.featured', $review) }}">
                                @csrf
                                @method('PATCH')
                                <button class="reviews-secondary" type="submit">{{ $review->is_featured ? 'Bỏ nổi bật' : 'Đánh dấu nổi bật' }}</button>
                            </form>
                        @endif
                    </div>
                    <form class="review-admin-form" method="post" action="{{ route('admin.reviews.reject', $review) }}">
                        @csrf
                        @method('PATCH')
                        <label for="rejected_reason">Lý do từ chối</label>
                        <textarea id="rejected_reason" name="rejected_reason" required>{{ old('rejected_reason', $review->rejected_reason) }}</textarea>
                        <button class="reviews-danger" type="submit">Từ chối</button>
                    </form>
                </div>
            @endcan

            @can('reviews.reply')
                <div class="review-detail-card">
                    <h2>Phản hồi showroom</h2>
                    <form class="review-admin-form" method="post" action="{{ route('admin.reviews.reply', $review) }}">
                        @csrf
                        @method('PATCH')
                        <label for="reply_content">Nội dung phản hồi</label>
                        <textarea id="reply_content" name="reply_content" required>{{ old('reply_content', $review->reply_content) }}</textarea>
                        <button class="reviews-primary" type="submit">Lưu phản hồi</button>
                    </form>
                </div>
            @endcan

            <div class="review-detail-card">
                <h2>Báo cáo từ khách</h2>
                <div class="review-report-list">
                    @forelse($review->reports as $report)
                        <div>
                            <strong>{{ $report->reason }}</strong>
                            <span>{{ $report->user?->name ?? 'Ẩn danh' }} - {{ $report->created_at?->format('d/m/Y H:i') }}</span>
                            @if($report->note)
                                <p>{{ $report->note }}</p>
                            @endif
                        </div>
                    @empty
                        <p>Chưa có báo cáo.</p>
                    @endforelse
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
