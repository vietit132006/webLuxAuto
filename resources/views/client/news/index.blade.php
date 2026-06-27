@extends('layouts.site')
@section('title', 'Tin Tức & Đánh Giá Xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-news-index.css')
    @endif
@endpush


@section('content')

<div class="wrap-news">
    <div class="news-header">
        <h1>Tin Tức & Sự kiện</h1>
        <p>Khám phá thế giới xe sang với những đánh giá chuyên sâu, tin tức thị trường mới nhất và các đặc quyền dành riêng cho khách hàng của Lux Auto.</p>

        <form class="search-form" action="{{ route('news.index') }}" method="GET">
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Tìm kiếm bài viết, đánh giá xe..." autocomplete="off">
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>

    @if($news->isEmpty())
        <div class="empty-news">
            <h3 class="client-news-index-inline-3">Không tìm thấy bài viết nào!</h3>
            <p class="client-news-index-inline-2">Rất tiếc, chúng tôi không có bài viết nào phù hợp với từ khóa "<strong>{{ $search }}</strong>".</p>
            <a class="client-news-index-inline-1" href="{{ route('news.index') }}">Hiển thị tất cả tin tức</a>
        </div>
    @else
        <div class="news-grid">
            @foreach($news as $item)
                <article class="news-card">
                    <a href="{{ route('news.show', $item->slug) }}" class="img-wrap">
                        @if($item->image)
                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="news-card__img">
                        @else
                            <div class="news-card__img-placeholder">LUX AUTO</div>
                        @endif
                    </a>
                    <div class="news-card__body">
                        <div class="news-card__date">
                            {{ $item->created_at->format('d/m/Y') }}
                        </div>
                        <h2 class="news-card__title">
                            <a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a>
                        </h2>
                        <p class="news-card__summary">
                            {{ Str::limit($item->summary, 140, '...') }}
                        </p>

                        <div class="news-card__footer">
                            <a href="{{ route('news.show', $item->slug) }}" class="news-card__readmore">
                                Đọc chi tiết
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                    <polyline points="12 5 19 12 12 19"></polyline>
                                </svg>
                            </a>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($news->hasPages())
            <div class="pagination-container">
                {{ $news->links('pagination.lux') }}
            </div>
        @endif
    @endif
</div>
@endsection