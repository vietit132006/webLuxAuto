@extends('layouts.site')

@section('title', $promotion->seo_title ?: $promotion->title)

@push('meta')
    @if($promotion->seo_description)
        <meta name="description" content="{{ $promotion->seo_description }}">
    @endif
@endpush

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-promotions.css')
    @endif
@endpush

@section('content')
@php
    $heroImage = $promotion->bannerUrl()
        ?: 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=1800&q=85';
    $money = fn ($value) => number_format((float) $value, 0, ',', '.') . ' đ';
@endphp

<div class="promo-page">
    <section class="promo-detail-hero" style="--promo-hero-image: url('{{ $heroImage }}')">
        <div class="promo-hero__content">
            <nav class="promo-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('promotions.index') }}">Khuyến mãi</a>
                <span>/</span>
                <span>{{ $promotion->promotion_code }}</span>
            </nav>
            <span class="promo-kicker">{{ $promotion->typeLabel() }}</span>
            <h1>{{ $promotion->title }}</h1>
            <p>{{ $promotion->short_description ?: $promotion->discountLabel() }}</p>
            <div class="promo-hero-actions">
                <a href="{{ route('cars.index') }}" class="promo-btn promo-btn--primary">Xem xe áp dụng</a>
                <a href="{{ route('ticket.create') }}" class="promo-btn promo-btn--ghost">Liên hệ tư vấn</a>
                @if($applicableCars->first())
                    <a href="{{ route('ticket.create', ['type' => 'test_drive', 'car_id' => $applicableCars->first()->car_id]) }}" class="promo-btn promo-btn--ghost">Đặt lịch lái thử</a>
                @endif
            </div>
        </div>
    </section>

    <main class="promo-wrap promo-detail-main">
        <div class="promo-detail-layout">
            <article class="promo-detail-content">
                <section>
                    <div class="promo-section-head">
                        <div>
                            <h2>Nội dung chương trình</h2>
                            <p>{{ $promotion->discountLabel() }}</p>
                        </div>
                    </div>
                    <div class="promo-rich-text">
                        {!! nl2br(e($promotion->content ?: $promotion->short_description ?: $promotion->discountLabel())) !!}
                    </div>
                </section>

                @if($promotion->terms)
                    <section>
                        <div class="promo-section-head">
                            <div>
                                <h2>Điều kiện áp dụng</h2>
                                <p>{{ $promotion->targetSummary() }}</p>
                            </div>
                        </div>
                        <div class="promo-rich-text">
                            {!! nl2br(e($promotion->terms)) !!}
                        </div>
                    </section>
                @endif

                <section id="xe-ap-dung">
                    <div class="promo-section-head">
                        <div>
                            <h2>Xe áp dụng</h2>
                            <p>{{ $promotion->targetSummary() }}</p>
                        </div>
                    </div>

                    <div class="promo-cars-grid">
                        @forelse($applicableCars as $car)
                            @php
                                $availableStock = $car->availableStock();
                                $physicalStock = $car->physicalStock();
                                $stockLabel = $physicalStock <= 0
                                    ? 'Hết hàng'
                                    : ($availableStock <= 0 ? 'Đã giữ hết' : 'Khả dụng ' . $availableStock);
                            @endphp
                            <article class="promo-car-card">
                                <a href="{{ route('cars.show_public', $car->car_id) }}" class="promo-car-card__image">
                                    @if($car->image)
                                        <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->title }}" loading="lazy">
                                    @else
                                        <img src="https://images.unsplash.com/photo-1549924231-f129b911e442?auto=format&fit=crop&w=800&q=80" alt="{{ $car->title }}" loading="lazy">
                                    @endif
                                    <span class="{{ $availableStock > 0 ? 'is-available' : 'is-unavailable' }}">{{ $stockLabel }}</span>
                                </a>
                                <div class="promo-car-card__body">
                                    <h3><a href="{{ route('cars.show_public', $car->car_id) }}">{{ $car->title }}</a></h3>
                                    <p>{{ $car->vin ? 'VIN ' . $car->vin : ($car->color ?: 'Đang cập nhật') }}</p>
                                    <strong>{{ $money($car->sale_price ?: $car->price) }}</strong>
                                    <div class="promo-car-actions">
                                        <a href="{{ route('cars.show_public', $car->car_id) }}">Xem xe</a>
                                        <a href="{{ route('ticket.create', ['type' => 'test_drive', 'car_id' => $car->car_id]) }}">Lái thử</a>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="promo-empty">
                                <strong>Chưa có xe áp dụng</strong>
                                <span>Vui lòng liên hệ tư vấn để kiểm tra tình trạng chương trình.</span>
                            </div>
                        @endforelse
                    </div>
                </section>
            </article>

            <aside class="promo-detail-side">
                <div class="promo-info-panel">
                    <h2>Thông tin ưu đãi</h2>
                    <dl>
                        <div>
                            <dt>Mã khuyến mãi</dt>
                            <dd>{{ $promotion->promotion_code }}</dd>
                        </div>
                        <div>
                            <dt>Giá trị</dt>
                            <dd>{{ $promotion->discountLabel() }}</dd>
                        </div>
                        <div>
                            <dt>Thời gian</dt>
                            <dd>
                                {{ $promotion->start_at?->format('d/m/Y') ?: 'Không giới hạn' }}
                                -
                                {{ $promotion->end_at?->format('d/m/Y') ?: 'Không giới hạn' }}
                            </dd>
                        </div>
                        <div>
                            <dt>Đối tượng</dt>
                            <dd>{{ $promotion->targetSummary() }}</dd>
                        </div>
                    </dl>
                </div>

                @if($promotion->gift_description)
                    <div class="promo-info-panel">
                        <h2>Quà tặng đi kèm</h2>
                        <p>{{ $promotion->gift_description }}</p>
                    </div>
                @endif

                <div class="promo-side-cta">
                    <strong>Nhận báo giá theo ưu đãi</strong>
                    <span>Gửi yêu cầu tư vấn để sale xác nhận xe, tồn kho và chương trình đang áp dụng.</span>
                    <a href="{{ route('ticket.create') }}">Nhận báo giá</a>
                </div>
            </aside>
        </div>

        @if($relatedPromotions->isNotEmpty())
            <section class="promo-related">
                <div class="promo-section-head">
                    <div>
                        <h2>Ưu đãi liên quan</h2>
                        <p>Cùng nhóm {{ $promotion->typeLabel() }}.</p>
                    </div>
                </div>
                <div class="promo-grid">
                    @foreach($relatedPromotions as $related)
                        <article class="promo-card">
                            <a href="{{ route('promotions.show', $related->slug) }}" class="promo-card__media">
                                @if($related->bannerUrl())
                                    <img src="{{ $related->bannerUrl() }}" alt="{{ $related->banner_alt ?: $related->title }}" loading="lazy">
                                @else
                                    <img src="https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=900&q=80" alt="{{ $related->title }}" loading="lazy">
                                @endif
                            </a>
                            <div class="promo-card__body">
                                <span class="promo-type">{{ $related->typeLabel() }}</span>
                                <h3><a href="{{ route('promotions.show', $related->slug) }}">{{ $related->title }}</a></h3>
                                <p>{{ $related->short_description ?: $related->discountLabel() }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif
    </main>
</div>
@endsection
