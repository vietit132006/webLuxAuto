@extends('layouts.site')

@php
    $seoTitle = $article->seo_title ?: $article->title;
    $seoDescription = $article->seo_description ?: ($article->summary ?: \Illuminate\Support\Str::limit(strip_tags($article->content), 155));
    $canonicalUrl = $article->canonical_url ?: route('news.show', $article->slug);
    $thumbnailUrl = $article->thumbnailUrl();
    $ogImage = $thumbnailUrl ? (str_starts_with($thumbnailUrl, 'http') ? $thumbnailUrl : url($thumbnailUrl)) : null;
    $ctaType = $article->cta_type ?: \App\Models\News::CTA_NONE;
    $ctaLabel = $article->cta_label ?: (\App\Models\News::ctaTypes()[$ctaType] ?? 'Liên hệ tư vấn');
    $ctaUrl = $article->cta_url;

    if (!$ctaUrl) {
        $ctaUrl = match ($ctaType) {
            \App\Models\News::CTA_TEST_DRIVE => $article->related_car_id
                ? route('ticket.create', ['type' => 'test_drive', 'car_id' => $article->related_car_id])
                : route('ticket.create', ['type' => 'support']),
            \App\Models\News::CTA_CAR_DETAIL => $article->related_car_id
                ? route('cars.show_public', $article->related_car_id)
                : route('cars.index', array_filter(['brand_id' => $article->related_brand_id])),
            \App\Models\News::CTA_QUOTE => $article->related_car_id
                ? route('cars.show_public', $article->related_car_id)
                : route('cars.index', array_filter(['brand_id' => $article->related_brand_id])),
            \App\Models\News::CTA_CONTACT => route('ticket.create'),
            default => null,
        };
    }
@endphp

@section('title', $seoTitle)

@push('meta')
    <meta name="description" content="{{ $seoDescription }}">
    @if ($article->seo_keywords)
        <meta name="keywords" content="{{ $article->seo_keywords }}">
    @endif
    <link rel="canonical" href="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $seoTitle }}">
    <meta property="og:description" content="{{ $seoDescription }}">
    <meta property="og:type" content="article">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif
@endpush

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-news-show.css')
    @endif
@endpush

@section('content')
<article class="article-page">
    <header class="article-head">
        <div class="article-breadcrumb">
            <a href="{{ route('news.index') }}">Tin tức</a>
            <span>{{ $article->category?->name ?? 'LUXAUTO' }}</span>
        </div>
        <h1>{{ $article->title }}</h1>
        <p>{{ $article->summary }}</p>
        <div class="article-meta">
            <span>{{ $article->author?->name ?? 'Lux Auto' }}</span>
            <span>{{ $article->effectivePublishedAt()?->format('d/m/Y H:i') }}</span>
            <span>{{ number_format($article->views_count) }} lượt xem</span>
            <span>{{ $article->reading_time ?? 1 }} phút đọc</span>
        </div>
    </header>

    @if ($article->thumbnailUrl())
        <img class="article-cover" src="{{ $article->thumbnailUrl() }}" alt="{{ $article->thumbnail_alt ?: $article->title }}">
    @endif

    <div class="article-layout">
        <div class="article-main">
            <div class="article-content">
                {!! $article->content !!}
            </div>

            @if ($article->tags->isNotEmpty())
                <div class="article-tags">
                    @foreach ($article->tags as $tag)
                        <span>{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif

            @if ($ctaUrl && $ctaType !== \App\Models\News::CTA_NONE)
                <div class="article-cta">
                    <div>
                        <span>Gợi ý tiếp theo</span>
                        <strong>{{ $ctaLabel }}</strong>
                    </div>
                    <a href="{{ $ctaUrl }}">{{ $ctaLabel }}</a>
                </div>
            @endif

            @if ($relatedCars->isNotEmpty())
                <section class="article-related-cars">
                    <div class="article-section-head">
                        <h2>Xe liên quan</h2>
                        <span>{{ $relatedCars->count() }} xe</span>
                    </div>
                    <div class="related-car-grid">
                        @foreach ($relatedCars as $car)
                            @php
                                $stockLabel = $car->isOutOfStock() ? 'Hết hàng' : ($car->isFullyReserved() ? 'Đã giữ hết' : 'Có thể bán');
                                $stockClass = $car->isOutOfStock() ? 'is-danger' : ($car->isFullyReserved() ? 'is-warning' : 'is-success');
                            @endphp
                            <article class="related-car-card">
                                <a class="related-car-media" href="{{ route('cars.show_public', $car->car_id) }}">
                                    @if ($car->image)
                                        <img src="{{ asset('storage/' . $car->image) }}" alt="{{ $car->title }}" loading="lazy">
                                    @else
                                        <span>Lux Auto</span>
                                    @endif
                                </a>
                                <div class="related-car-body">
                                    <h3><a href="{{ route('cars.show_public', $car->car_id) }}">{{ $car->title }}</a></h3>
                                    <p>{{ number_format((float) $car->price, 0, ',', '.') }} đ</p>
                                    <span class="stock-badge {{ $stockClass }}">{{ $stockLabel }}</span>
                                    <div class="related-car-actions">
                                        <a href="{{ route('cars.show_public', $car->car_id) }}">Xem xe</a>
                                        <a href="{{ route('cars.show_public', $car->car_id) }}">Nhận báo giá</a>
                                        <a href="{{ route('ticket.create', ['type' => 'test_drive', 'car_id' => $car->car_id]) }}">Lái thử</a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($relatedNews->isNotEmpty())
                <section class="article-related-news">
                    <div class="article-section-head">
                        <h2>Bài viết liên quan</h2>
                        <span>Cùng chủ đề</span>
                    </div>
                    <div class="related-news-grid">
                        @foreach ($relatedNews as $related)
                            <article class="related-news-card">
                                <a href="{{ route('news.show', $related->slug) }}">
                                    @if ($related->thumbnailUrl())
                                        <img src="{{ $related->thumbnailUrl() }}" alt="{{ $related->thumbnail_alt ?: $related->title }}" loading="lazy">
                                    @else
                                        <span>Lux Auto</span>
                                    @endif
                                </a>
                                <div>
                                    <span>{{ $related->effectivePublishedAt()?->format('d/m/Y') }}</span>
                                    <h3><a href="{{ route('news.show', $related->slug) }}">{{ $related->title }}</a></h3>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        <aside class="article-sidebar">
            <section>
                <h2>Bài mới nhất</h2>
                @foreach ($latestNews as $item)
                    <a class="article-side-link" href="{{ route('news.show', $item->slug) }}">
                        <span>{{ $item->effectivePublishedAt()?->format('d/m/Y') }}</span>
                        <strong>{{ $item->title }}</strong>
                    </a>
                @endforeach
            </section>

            <section>
                <h2>Xem nhiều</h2>
                @foreach ($popularNews as $item)
                    <a class="article-side-link" href="{{ route('news.show', $item->slug) }}">
                        <span>{{ number_format($item->views_count) }} lượt xem</span>
                        <strong>{{ $item->title }}</strong>
                    </a>
                @endforeach
            </section>
        </aside>
    </div>
</article>
@endsection
