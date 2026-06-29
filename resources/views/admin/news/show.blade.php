@extends('layouts.admin')

@section('title', 'Chi tiết tin tức')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-news-show.css')
    @endif
@endpush

@section('content')
<div class="news-show-page">
    <div class="news-show-head">
        <div>
            <p class="news-show-kicker">CMS Tin tức</p>
            <h1>{{ $news->title }}</h1>
        </div>
        <div class="news-show-actions">
            <a class="news-show-btn is-secondary" href="{{ route('admin.news.index') }}">Danh sách</a>
            <a class="news-show-btn is-secondary" href="{{ route('news.show', $news->slug) }}" target="_blank" rel="noopener">Frontend</a>
            @can('news.edit')
                <a class="news-show-btn is-primary" href="{{ route('admin.news.edit', $news) }}">Sửa bài</a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="news-show-alert">{{ session('success') }}</div>
    @endif

    <div class="news-show-grid">
        <aside class="news-show-side">
            @if ($news->thumbnailUrl())
                <img class="news-show-cover" src="{{ $news->thumbnailUrl() }}" alt="{{ $news->thumbnail_alt ?: $news->title }}">
            @else
                <div class="news-show-cover-empty">Lux Auto</div>
            @endif

            <dl class="news-meta-list">
                <div><dt>ID</dt><dd>#{{ $news->id }}</dd></div>
                <div><dt>Slug</dt><dd>{{ $news->slug }}</dd></div>
                <div><dt>Trạng thái</dt><dd><span class="news-badge {{ $news->statusBadgeClass() }}">{{ $news->statusLabel() }}</span></dd></div>
                <div><dt>Chuyên mục</dt><dd>{{ $news->category?->name ?? 'Chưa phân loại' }}</dd></div>
                <div><dt>Tác giả</dt><dd>{{ $news->author?->name ?? 'Hệ thống' }}</dd></div>
                <div><dt>Nổi bật</dt><dd>{{ $news->is_featured ? 'Có' : 'Không' }}</dd></div>
                <div><dt>Lượt xem</dt><dd>{{ number_format($news->views_count) }}</dd></div>
                <div><dt>Thời gian đọc</dt><dd>{{ $news->reading_time ?? 1 }} phút</dd></div>
                <div><dt>Ngày đăng</dt><dd>{{ $news->effectivePublishedAt()?->format('d/m/Y H:i') ?? 'Chưa đăng' }}</dd></div>
                <div><dt>Ngày tạo</dt><dd>{{ $news->created_at?->format('d/m/Y H:i') }}</dd></div>
            </dl>

            @if ($news->tags->isNotEmpty())
                <div class="news-tag-list">
                    @foreach ($news->tags as $tag)
                        <span>{{ $tag->name }}</span>
                    @endforeach
                </div>
            @endif
        </aside>

        <article class="news-show-content">
            @if ($news->summary)
                <p class="news-summary">{{ $news->summary }}</p>
            @endif

            <div class="news-prose">
                {!! $news->content !!}
            </div>

            <div class="news-detail-panels">
                <section>
                    <h2>SEO</h2>
                    <p><strong>Title:</strong> {{ $news->seo_title ?: $news->title }}</p>
                    <p><strong>Description:</strong> {{ $news->seo_description ?: $news->summary ?: 'Chưa có' }}</p>
                    <p><strong>Keywords:</strong> {{ $news->seo_keywords ?: 'Chưa có' }}</p>
                    <p><strong>Canonical:</strong> {{ $news->canonical_url ?: 'Theo URL mặc định' }}</p>
                </section>

                <section>
                    <h2>Liên kết bán hàng</h2>
                    <p><strong>Hãng:</strong> {{ $news->relatedBrand?->name ?? 'Không chọn' }}</p>
                    <p><strong>Model:</strong> {{ $news->relatedModel?->brand?->name }} {{ $news->relatedModel?->name ?? 'Không chọn' }}</p>
                    <p><strong>Xe:</strong> {{ $news->relatedCar?->title ?? 'Không chọn' }}</p>
                    <p><strong>CTA:</strong> {{ \App\Models\News::ctaTypes()[$news->cta_type ?: 'none'] ?? 'Không hiển thị' }}</p>
                </section>
            </div>
        </article>
    </div>
</div>
@endsection
