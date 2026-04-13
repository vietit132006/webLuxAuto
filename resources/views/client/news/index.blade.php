@extends('layouts.site')
@section('title', 'Tin Tức & Đánh Giá Xe')

@section('content')
<style>
    /* Nới rộng khung chứa tối đa để tận dụng không gian 2 bên */
    .wrap-news {
        max-width: 1400px; /* Tăng từ 1120px lên 1400px */
        margin: 0 auto;
        padding: 0 2rem;
    }

    .news-header {
        text-align: center;
        margin-bottom: 4rem;
    }
    .news-header h1 {
        font-size: 2.75rem;
        color: var(--accent);
        margin-bottom: 1.25rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 800;
    }
    .news-header p {
        color: var(--muted);
        font-size: 1.15rem;
        max-width: 700px;
        margin: 0 auto 2.5rem;
        line-height: 1.7;
    }

    /* Form tìm kiếm */
    .search-form {
        display: flex;
        max-width: 600px;
        margin: 0 auto;
        gap: 0.5rem;
        position: relative;
    }
    .search-form input {
        flex: 1;
        padding: 1rem 1.5rem;
        border-radius: 50px;
        border: 1px solid var(--border);
        background: rgba(20, 26, 34, 0.8);
        color: var(--text);
        font-size: 1.05rem;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }
    .search-form input:focus {
        outline: none;
        border-color: var(--accent);
        box-shadow: 0 0 0 4px rgba(201, 169, 98, 0.1);
        background: var(--surface);
    }
    .search-form button {
        padding: 0 2.5rem;
        border-radius: 50px;
        border: none;
        background: var(--accent);
        color: #0c0f14;
        font-weight: 700;
        font-size: 1.05rem;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
        position: absolute;
        right: 5px;
        top: 5px;
        bottom: 5px;
    }
    .search-form button:hover {
        background: #e4d08a;
    }

    /* Hệ thống Lưới (Grid) Tối ưu hóa - Tự động co giãn theo màn hình */
    .news-grid {
        display: grid;
        /* Công thức tự động: Tối thiểu 350px, nếu màn hình rộng thì tự dàn ngang */
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 2.5rem;
    }

    .news-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
        display: flex;
        flex-direction: column;
        height: 100%; /* Cân bằng chiều cao các thẻ */
    }
    .news-card:hover {
        transform: translateY(-10px);
        border-color: var(--accent-dim);
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }

    .img-wrap {
        position: relative;
        overflow: hidden;
        aspect-ratio: 16/10; /* Giữ tỷ lệ khung hình chuẩn cho ảnh xe */
    }
    .news-card__img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.6s cubic-bezier(0.165, 0.84, 0.44, 1);
    }
    .news-card:hover .news-card__img {
        transform: scale(1.08);
    }
    .news-card__img-placeholder {
        width: 100%;
        height: 100%;
        background: #0a0d12;
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.1);
        font-size: 1.5rem;
        font-weight: 800;
        letter-spacing: 4px;
    }

    .news-card__body {
        padding: 2rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        background: linear-gradient(to bottom, var(--surface), #0c0f14);
    }
    .news-card__date {
        font-size: 0.85rem;
        color: var(--accent);
        margin-bottom: 1rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .news-card__title {
        font-size: 1.4rem;
        color: var(--text);
        margin: 0 0 1rem;
        line-height: 1.4;
        font-weight: 700;
    }
    .news-card__title a {
        color: inherit;
        text-decoration: none;
        transition: color 0.2s;
    }
    .news-card__title a:hover {
        color: var(--accent);
    }
    .news-card__summary {
        color: var(--muted);
        font-size: 1rem;
        line-height: 1.7;
        margin-bottom: 2rem;
        flex: 1; /* Đẩy nút "Đọc tiếp" xuống dưới cùng */
    }

    .news-card__footer {
        border-top: 1px solid rgba(255,255,255,0.05);
        padding-top: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .news-card__readmore {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--text);
        font-weight: 600;
        font-size: 0.95rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        text-decoration: none;
        transition: color 0.2s;
    }
    .news-card__readmore:hover {
        color: var(--accent);
    }
    .news-card__readmore svg {
        transition: transform 0.2s;
    }
    .news-card:hover .news-card__readmore svg {
        transform: translateX(5px);
        color: var(--accent);
    }

    .empty-news {
        text-align: center;
        padding: 5rem 2rem;
        background: var(--surface);
        border-radius: 16px;
        border: 1px dashed var(--border);
        color: var(--muted);
        max-width: 800px;
        margin: 0 auto;
    }
    .pagination-container {
        margin-top: 4rem;
        display: flex;
        justify-content: center;
    }
</style>

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
            <h3 style="color: var(--text); font-size: 1.75rem; margin-top: 0;">Không tìm thấy bài viết nào!</h3>
            <p style="font-size: 1.1rem; margin-bottom: 2rem;">Rất tiếc, chúng tôi không có bài viết nào phù hợp với từ khóa "<strong>{{ $search }}</strong>".</p>
            <a href="{{ route('news.index') }}" style="background: var(--accent); color: #000; padding: 0.8rem 2rem; border-radius: 50px; text-decoration: none; font-weight: bold; display: inline-block;">Hiển thị tất cả tin tức</a>
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
