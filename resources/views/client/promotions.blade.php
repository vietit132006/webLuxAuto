@extends('layouts.site')

@section('title', 'Khuyến mãi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-promotions.css')
    @endif
@endpush

@section('content')
@php
    $heroPromotion = $featuredPromotions->first() ?? $promotions->first();
    $heroImage = $heroPromotion?->bannerUrl()
        ?: 'https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=1800&q=85';
    $money = fn ($value) => number_format((float) $value, 0, ',', '.') . ' đ';
@endphp

<div class="promo-page">
    <section class="promo-hero" style="--promo-hero-image: url('{{ $heroImage }}')">
        <div class="promo-hero__content">
            <span class="promo-kicker">Ưu đãi Lux Auto</span>
            <h1>{{ $heroPromotion?->title ?? 'Khuyến mãi xe sang' }}</h1>
            <p>{{ $heroPromotion?->short_description ?? 'Cập nhật các chương trình hỗ trợ giá, quà tặng và dịch vụ hậu mãi đang áp dụng tại showroom.' }}</p>
            <div class="promo-hero-actions">
                @if($heroPromotion)
                    <a href="{{ route('promotions.show', $heroPromotion->slug) }}" class="promo-btn promo-btn--primary">Xem ưu đãi</a>
                @endif
                <a href="{{ route('cars.index') }}" class="promo-btn promo-btn--ghost">Xem xe đang bán</a>
            </div>
        </div>
    </section>

    <main class="promo-wrap promo-main">
        <form class="promo-filter" method="get" action="{{ route('promotions.index') }}">
            <div>
                <label for="promotion_type">Loại ưu đãi</label>
                <select id="promotion_type" name="promotion_type">
                    <option value="">Tất cả loại</option>
                    @foreach($promotionTypes as $value => $label)
                        <option value="{{ $value }}" @selected($filters['promotion_type'] === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="brand_id">Hãng xe</label>
                <select id="brand_id" name="brand_id">
                    <option value="">Tất cả hãng</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->brand_id }}" @selected((string) $filters['brand_id'] === (string) $brand->brand_id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="model_id">Model xe</label>
                <select id="model_id" name="model_id">
                    <option value="">Tất cả model</option>
                    @foreach($carModels as $model)
                        <option value="{{ $model->id }}" @selected((string) $filters['model_id'] === (string) $model->id)>
                            {{ $model->brand?->name }} {{ $model->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="promo-filter-actions">
                <button type="submit">Lọc</button>
                <a href="{{ route('promotions.index') }}">Xóa lọc</a>
            </div>
        </form>

        @if($featuredPromotions->isNotEmpty())
            <section class="promo-featured">
                <div class="promo-section-head">
                    <div>
                        <h2>Ưu đãi nổi bật</h2>
                        <p>Các chương trình showroom đang ưu tiên tư vấn.</p>
                    </div>
                </div>

                <div class="promo-featured-grid">
                    @foreach($featuredPromotions as $promotion)
                        <article class="promo-card is-featured">
                            <a href="{{ route('promotions.show', $promotion->slug) }}" class="promo-card__media">
                                @if($promotion->bannerUrl())
                                    <img src="{{ $promotion->bannerUrl() }}" alt="{{ $promotion->banner_alt ?: $promotion->title }}" loading="lazy">
                                @else
                                    <img src="https://images.unsplash.com/photo-1555215695-3004980ad54e?auto=format&fit=crop&w=900&q=80" alt="{{ $promotion->title }}" loading="lazy">
                                @endif
                            </a>
                            <div class="promo-card__body">
                                <span class="promo-type">{{ $promotion->typeLabel() }}</span>
                                <h3><a href="{{ route('promotions.show', $promotion->slug) }}">{{ $promotion->title }}</a></h3>
                                <p>{{ $promotion->short_description ?: $promotion->discountLabel() }}</p>
                                <div class="promo-card__meta">
                                    <strong>{{ $promotion->discountLabel() }}</strong>
                                    <span>{{ $promotion->end_at ? 'Đến ' . $promotion->end_at->format('d/m/Y') : 'Không giới hạn' }}</span>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        <div class="promo-layout">
            <section class="promo-list-section">
                <div class="promo-section-head">
                    <div>
                        <h2>Chương trình đang áp dụng</h2>
                        <p>{{ $promotions->total() }} ưu đãi phù hợp.</p>
                    </div>
                </div>

                <div class="promo-grid">
                    @forelse($promotions as $promotion)
                        <article class="promo-card">
                            <a href="{{ route('promotions.show', $promotion->slug) }}" class="promo-card__media">
                                @if($promotion->bannerUrl())
                                    <img src="{{ $promotion->bannerUrl() }}" alt="{{ $promotion->banner_alt ?: $promotion->title }}" loading="lazy">
                                @else
                                    <img src="https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?auto=format&fit=crop&w=900&q=80" alt="{{ $promotion->title }}" loading="lazy">
                                @endif
                            </a>
                            <div class="promo-card__body">
                                <span class="promo-type">{{ $promotion->typeLabel() }}</span>
                                <h3><a href="{{ route('promotions.show', $promotion->slug) }}">{{ $promotion->title }}</a></h3>
                                <p>{{ $promotion->short_description ?: $promotion->targetSummary() }}</p>
                                <div class="promo-card__meta">
                                    <strong>{{ $promotion->discountLabel() }}</strong>
                                    <span>{{ $promotion->targetSummary() }}</span>
                                </div>
                                <div class="promo-card__actions">
                                    <a href="{{ route('promotions.show', $promotion->slug) }}">Chi tiết</a>
                                    <a href="{{ route('cars.index') }}">Xem xe</a>
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="promo-empty">
                            <strong>Chưa có khuyến mãi phù hợp</strong>
                            <span>Thử xóa bộ lọc hoặc quay lại sau.</span>
                        </div>
                    @endforelse
                </div>

                @if($promotions->hasPages())
                    <div class="promo-pagination">{{ $promotions->links('pagination.lux') }}</div>
                @endif
            </section>

            <aside class="promo-side">
                <h2>Sắp hết hạn</h2>
                <div class="promo-ending-list">
                    @forelse($endingSoonPromotions as $promotion)
                        <a href="{{ route('promotions.show', $promotion->slug) }}">
                            <strong>{{ $promotion->title }}</strong>
                            <span>{{ $promotion->end_at?->diffForHumans() }} · {{ $promotion->discountLabel() }}</span>
                        </a>
                    @empty
                        <div class="promo-side-empty">Chưa có ưu đãi sắp hết hạn.</div>
                    @endforelse
                </div>

                <div class="promo-side-cta">
                    <strong>Cần tư vấn ưu đãi theo xe?</strong>
                    <span>Đội ngũ Lux Auto sẽ kiểm tra tồn kho và chương trình tốt nhất cho mẫu xe bạn quan tâm.</span>
                    <a href="{{ route('ticket.create') }}">Liên hệ tư vấn</a>
                </div>
            </aside>
        </div>
    </main>
</div>
@endsection
