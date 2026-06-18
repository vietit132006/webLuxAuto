@extends('layouts.site')
@section('title', $article->title)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-news-show.css')
    @endif
@endpush


@section('content')

<div class="wrap-article">

    <div class="article-hero">
        <h1 class="article-title">{{ $article->title }}</h1>
        <div class="article-meta">
            <span>🗓️ Đăng lúc: {{ $article->created_at->format('H:i - d/m/Y') }}</span>
            <span>✍️ Tác giả: Lux Auto</span>
        </div>
    </div>

    @if($article->image)
        <img src="{{ asset('storage/' . $article->image) }}" alt="{{ $article->title }}" class="article-img">
    @endif

    <div class="article-body-container">

        @if($article->summary)
            <div class="client-news-show-inline-5">
                {{ $article->summary }}
            </div>
        @endif

        <div class="article-content">
            {!! nl2br(e($article->content)) !!}
        </div>

        <div class="share-section">
            <span class="client-news-show-inline-4">Chia sẻ:
                <a class="client-news-show-inline-3" href="#">Facebook</a> •
                <a class="client-news-show-inline-2" href="#">Twitter</a>
            </span>
            <a href="{{ route('news.index') }}" class="btn-back-news">← Xem thêm tin khác</a>
        </div>
    </div>

    @if($relatedNews->isNotEmpty())
    <div class="related-section">
        <h3 class="related-title">Có thể bạn quan tâm</h3>
        <div class="related-grid">
            @foreach($relatedNews as $related)
                <article class="r-card">
                    <a href="{{ route('news.show', $related->slug) }}">
                        @if($related->image)
                            <img src="{{ asset('storage/' . $related->image) }}" alt="{{ $related->title }}">
                        @else
                            <div class="r-card-placeholder">LUX AUTO</div>
                        @endif
                    </a>
                    <div class="r-card-body">
                        <div class="client-news-show-inline-1">
                            {{ $related->created_at->format('d/m/Y') }}
                        </div>
                        <h4 class="r-card-title">
                            <a href="{{ route('news.show', $related->slug) }}">{{ $related->title }}</a>
                        </h4>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection