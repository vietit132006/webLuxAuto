@extends('layouts.site')

@section('title', 'Tin tức LUXAUTO')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-news-index.css')
    @endif
@endpush

@section('content')
<div class="news-page">
    <header class="news-hero">
        <p>Tạp chí LUXAUTO</p>
        <h1>Tin tức xe sang và tư vấn mua xe</h1>
        <form class="news-search" action="{{ route('news.index') }}" method="get">
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tìm bài viết, hãng xe, kinh nghiệm mua xe">
            @if ($filters['category'])
                <input type="hidden" name="category" value="{{ $filters['category'] }}">
            @endif
            <button type="submit">Tìm kiếm</button>
        </form>
    </header>

    <nav class="news-category-strip" aria-label="Chuyên mục tin tức">
        <a class="{{ empty($filters['category']) ? 'is-active' : '' }}" href="{{ route('news.index', array_filter(['q' => $filters['q'] ?: null])) }}">Tất cả</a>
        @foreach ($categories as $category)
            <a class="{{ $filters['category'] === $category->slug ? 'is-active' : '' }}"
               href="{{ route('news.index', array_filter(['category' => $category->slug, 'q' => $filters['q'] ?: null])) }}">
                {{ $category->name }}
                <span>{{ $category->news_count }}</span>
            </a>
        @endforeach
    </nav>

    @if ($featuredNews->isNotEmpty())
        <section class="featured-news">
            <div class="section-head">
                <h2>Bài viết nổi bật</h2>
                <span>{{ $featuredNews->count() }} bài</span>
            </div>
            <div class="featured-grid">
                @foreach ($featuredNews as $featured)
                    <article class="featured-card">
                        <a class="featured-media" href="{{ route('news.show', $featured->slug) }}">
                            @if ($featured->thumbnailUrl())
                                <img src="{{ $featured->thumbnailUrl() }}" alt="{{ $featured->thumbnail_alt ?: $featured->title }}" loading="lazy">
                            @else
                                <span>Lux Auto</span>
                            @endif
                        </a>
                        <div class="featured-body">
                            <div class="news-meta-line">
                                <span>{{ $featured->category?->name ?? 'Tin LUXAUTO' }}</span>
                                <span>{{ $featured->effectivePublishedAt()?->format('d/m/Y') }}</span>
                            </div>
                            <h3><a href="{{ route('news.show', $featured->slug) }}">{{ $featured->title }}</a></h3>
                            <p>{{ \Illuminate\Support\Str::limit($featured->summary, 150) }}</p>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>
    @endif

    <div class="news-layout">
        <section class="news-list-section">
            <div class="section-head">
                <h2>Danh sách bài viết</h2>
                <span>{{ number_format($news->total()) }} kết quả</span>
            </div>

            <div class="news-grid">
                @forelse ($news as $item)
                    <article class="news-card">
                        <a class="news-card-media" href="{{ route('news.show', $item->slug) }}">
                            @if ($item->thumbnailUrl())
                                <img src="{{ $item->thumbnailUrl() }}" alt="{{ $item->thumbnail_alt ?: $item->title }}" loading="lazy">
                            @else
                                <span>Lux Auto</span>
                            @endif
                        </a>
                        <div class="news-card-body">
                            <div class="news-meta-line">
                                <span>{{ $item->category?->name ?? 'Tin LUXAUTO' }}</span>
                                <span>{{ $item->effectivePublishedAt()?->format('d/m/Y') }}</span>
                            </div>
                            <h3><a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a></h3>
                            <p>{{ \Illuminate\Support\Str::limit($item->summary, 145) }}</p>
                            <div class="news-card-foot">
                                <span>{{ $item->reading_time ?? 1 }} phút đọc</span>
                                <a href="{{ route('news.show', $item->slug) }}">Đọc bài</a>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="news-empty">
                        <h2>Chưa có bài viết phù hợp</h2>
                        <p>Hãy thử từ khóa khác hoặc xóa bộ lọc chuyên mục.</p>
                        <a href="{{ route('news.index') }}">Xem tất cả tin tức</a>
                    </div>
                @endforelse
            </div>

            @if ($news->hasPages())
                <div class="news-pagination">
                    {{ $news->links('pagination.lux') }}
                </div>
            @endif
        </section>

        <aside class="news-sidebar">
            <section>
                <h2>Bài mới nhất</h2>
                @foreach ($latestNews as $item)
                    <a class="sidebar-news-link" href="{{ route('news.show', $item->slug) }}">
                        <span>{{ $item->effectivePublishedAt()?->format('d/m/Y') }}</span>
                        <strong>{{ $item->title }}</strong>
                    </a>
                @endforeach
            </section>

            <section>
                <h2>Xem nhiều</h2>
                @foreach ($popularNews as $item)
                    <a class="sidebar-news-link" href="{{ route('news.show', $item->slug) }}">
                        <span>{{ number_format($item->views_count) }} lượt xem</span>
                        <strong>{{ $item->title }}</strong>
                    </a>
                @endforeach
            </section>
        </aside>
    </div>
</div>
@endsection
