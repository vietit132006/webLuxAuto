@extends('layouts.site')
@section('title', 'Tin Tức & Đánh Giá Xe')

@section('content')
<style>
    /* CSS dành riêng cho trang Tin tức (Kế thừa tông màu của Lux Auto) */
    .news-header {
        text-align: center;
        margin-bottom: 3.5rem;
    }
    .news-header h1 {
        font-size: 2.5rem;
        color: var(--accent);
        margin-bottom: 1rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .news-header p {
        color: var(--muted);
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto 2rem;
        line-height: 1.6;
    }

    /* Form tìm kiếm */
    .search-form {
        display: flex;
        max-width: 550px;
        margin: 0 auto;
        gap: 0.5rem;
    }
    .search-form input {
        flex: 1;
        padding: 0.8rem 1.5rem;
        border-radius: 50px; /* Bo tròn hoàn toàn */
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        font-size: 1rem;
        transition: border-color 0.3s;
    }
    .search-form input:focus {
        outline: none;
        border-color: var(--accent);
    }
    .search-form button {
        padding: 0.8rem 2rem;
        border-radius: 50px;
        border: none;
        background: var(--accent);
        color: #000;
        font-weight: bold;
        cursor: pointer;
        transition: transform 0.2s, background 0.2s;
    }
    .search-form button:hover {
        background: var(--accent-dim);
        color: #fff;
    }

    /* Lưới (Grid) Bài viết */
    .news-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 2rem;
    }
    .news-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        transition: transform 0.3s ease, border-color 0.3s ease;
        display: flex;
        flex-direction: column;
    }
    .news-card:hover {
        transform: translateY(-8px);
        border-color: var(--accent-dim);
        box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    .news-card__img {
        width: 100%;
        height: 220px;
        object-fit: cover;
        border-bottom: 1px solid var(--border);
        transition: transform 0.5s ease;
    }
    .news-card:hover .news-card__img {
        transform: scale(1.05); /* Hiệu ứng zoom nhẹ ảnh khi hover */
    }
    .img-wrap {
        overflow: hidden; /* Cắt phần ảnh bị zoom tràn ra ngoài */
    }
    .news-card__img-placeholder {
        width: 100%;
        height: 220px;
        background: #0a0d12;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--muted);
        border-bottom: 1px solid var(--border);
        font-weight: 600;
        letter-spacing: 2px;
    }
    .news-card__body {
        padding: 1.5rem;
        flex: 1;
        display: flex;
        flex-direction: column;
        background: var(--surface); /* Đè lên ảnh nếu zoom */
        position: relative;
        z-index: 2;
    }
    .news-card__date {
        font-size: 0.85rem;
        color: var(--accent);
        margin-bottom: 0.75rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .news-card__title {
        font-size: 1.25rem;
        color: var(--text);
        margin: 0 0 1rem;
        line-height: 1.4;
    }
    .news-card__title a {
        color: inherit;
    }
    .news-card__summary {
        color: var(--muted);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        flex: 1;
    }
    .news-card__readmore {
        display: inline-block;
        color: var(--text);
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        transition: color 0.2s;
    }
    .news-card__readmore:hover {
        color: var(--accent);
    }

    .empty-news {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--surface);
        border-radius: 16px;
        border: 1px dashed var(--border);
        color: var(--muted);
    }
    .pagination-container {
        margin-top: 3rem;
        display: flex;
        justify-content: center;
    }
</style>

<div class="wrap">
    <div class="news-header">
        <h1>Tin Tức & Đánh Giá Xe</h1>
        <p>Cập nhật những thông tin mới nhất về thị trường ô tô, đánh giá chuyên sâu và các chương trình ưu đãi đặc biệt từ hệ thống Lux Auto.</p>

        <form class="search-form" action="{{ route('news.index') }}" method="GET">
            <input type="text" name="q" value="{{ $search ?? '' }}" placeholder="Nhập từ khóa tìm kiếm..." autocomplete="off">
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>

    @if($news->isEmpty())
        <div class="empty-news">
            <h3 style="color: var(--text); font-size: 1.5rem; margin-top: 0;">Không tìm thấy bài viết nào!</h3>
            <p>Có vẻ như không có bài viết nào phù hợp với từ khóa "<strong>{{ $search }}</strong>".</p>
            <a href="{{ route('news.index') }}" style="color: var(--accent); text-decoration: underline; margin-top: 10px; display: inline-block; font-weight: bold;">← Xem tất cả bài viết</a>
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
                            🗓️ {{ $item->created_at->format('d/m/Y') }}
                        </div>
                        <h2 class="news-card__title">
                            <a href="{{ route('news.show', $item->slug) }}">{{ $item->title }}</a>
                        </h2>
                        <p class="news-card__summary">
                            {{ Str::limit($item->summary, 120, '...') }}
                        </p>
                        <div>
                            <a href="{{ route('news.show', $item->slug) }}" class="news-card__readmore">Đọc tiếp →</a>
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
