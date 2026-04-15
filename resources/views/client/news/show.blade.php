@extends('layouts.site')
@section('title', $article->title)

@section('content')
<style>
    /* Khung ngoài cùng mở rộng ra 1200px cho cân bằng 2 bên */
    .wrap-article {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 2rem;
    }

    .article-hero {
        text-align: center;
        margin: 4rem 0 3rem;
    }
    .article-title {
        font-size: 2.75rem;
        color: var(--accent);
        margin: 0 0 1.5rem;
        line-height: 1.3;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .article-meta {
        color: var(--muted);
        font-size: 1.05rem;
        display: flex;
        justify-content: center;
        gap: 2rem;
        font-weight: 500;
    }

    /* Ảnh bìa siêu rộng và bo góc sang trọng */
    .article-img {
        width: 100%;
        height: 550px;
        object-fit: cover;
        border-radius: 16px;
        margin-bottom: -50px; /* Kéo khung chữ xích lên đè vào ảnh một chút */
        position: relative;
        z-index: 1;
        border: 1px solid var(--border);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }

    /* Khung chữ thiết kế dạng thẻ (Card) nổi lên trên */
    .article-body-container {
        max-width: 900px; /* Khung chữ vừa phải để mắt không bị mỏi khi đọc */
        margin: 0 auto;
        background: var(--surface);
        padding: 4rem;
        border-radius: 16px;
        border: 1px solid var(--border);
        font-size: 1.15rem;
        line-height: 1.8;
        color: var(--text);
        position: relative;
        z-index: 2;
        box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }

    .share-section {
        margin-top: 4rem;
        padding-top: 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .btn-back-news {
        color: #0c0f14;
        background: var(--accent);
        font-weight: bold;
        padding: 0.6rem 1.5rem;
        border-radius: 50px;
        text-decoration: none;
        transition: transform 0.2s;
    }
    .btn-back-news:hover {
        transform: scale(1.05);
    }

    /* Khu vực tin tức liên quan */
    .related-section {
        margin: 5rem 0 3rem;
        border-top: 1px solid rgba(255,255,255,0.05);
        padding-top: 4rem;
    }
    .related-title {
        font-size: 1.75rem;
        color: var(--text);
        margin-bottom: 2.5rem;
        text-align: center;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
    }
    .r-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.3s, border-color 0.3s;
    }
    .r-card:hover {
        transform: translateY(-5px);
        border-color: var(--accent-dim);
    }
    .r-card img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-bottom: 1px solid var(--border);
    }
    .r-card-placeholder {
        width: 100%;
        height: 220px;
        background: #0a0d12;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.1);
        font-size: 1.2rem;
        font-weight: bold;
        border-bottom: 1px solid var(--border);
    }
    .r-card-body {
        padding: 1.5rem;
    }
    .r-card-title {
        font-size: 1.2rem;
        margin: 0.5rem 0 0;
        line-height: 1.4;
    }
    .r-card-title a {
        color: var(--text);
        text-decoration: none;
        transition: color 0.2s;
    }
    .r-card-title a:hover {
        color: var(--accent);
    }
</style>

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
            <div style="font-weight: bold; font-size: 1.25rem; color: var(--accent); margin-bottom: 2rem; padding-bottom: 2rem; border-bottom: 1px dashed var(--border);">
                {{ $article->summary }}
            </div>
        @endif

        <div class="article-content">
            {!! nl2br(e($article->content)) !!}
        </div>

        <div class="share-section">
            <span style="color: var(--muted); font-size: 0.95rem;">Chia sẻ:
                <a href="#" style="color: #3b82f6; margin-left: 10px; text-decoration: none;">Facebook</a> •
                <a href="#" style="color: #0ea5e9; margin-left: 5px; text-decoration: none;">Twitter</a>
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
                        <div style="font-size: 0.85rem; color: var(--accent); font-weight: 600;">
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
