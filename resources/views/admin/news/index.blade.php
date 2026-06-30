@extends('layouts.admin')

@section('title', 'Quản lý tin tức')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-news-index.css')
    @endif
@endpush

@section('content')
<div class="news-admin-page">
    <div class="news-admin-head">
        <div>
            <p class="news-admin-kicker">CMS Tin tức</p>
            <h1>Quản lý bài viết</h1>
        </div>
        <div class="news-admin-actions">
            @can('news_categories.view')
                <a class="news-btn news-btn-secondary" href="{{ route('admin.news-categories.index') }}">Chuyên mục</a>
            @endcan
            @can('news.create')
                <a class="news-btn news-btn-primary" href="{{ route('admin.news.create') }}">Viết bài mới</a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="news-alert is-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="news-alert is-danger">{{ $errors->first() }}</div>
    @endif

    <div class="news-stat-grid">
        <div class="news-stat"><span>Tổng bài</span><strong>{{ number_format($stats['total']) }}</strong></div>
        <div class="news-stat"><span>Đã xuất bản</span><strong>{{ number_format($stats['published']) }}</strong></div>
        <div class="news-stat"><span>Hẹn giờ</span><strong>{{ number_format($stats['scheduled']) }}</strong></div>
        <div class="news-stat"><span>Bản nháp</span><strong>{{ number_format($stats['draft']) }}</strong></div>
    </div>

    <form class="news-filter" method="get" action="{{ route('admin.news.index') }}">
        <label>
            <span>Từ khóa</span>
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Tiêu đề, tóm tắt, slug">
        </label>

        <label>
            <span>Chuyên mục</span>
            <select name="category_id">
                <option value="">Tất cả</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $filters['category_id'] === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span>Trạng thái</span>
            <select name="status">
                <option value="">Tất cả</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </label>

        <label>
            <span>Tác giả</span>
            <select name="author_id">
                <option value="">Tất cả</option>
                @foreach ($authors as $author)
                    <option value="{{ $author->user_id }}" @selected((string) $filters['author_id'] === (string) $author->user_id)>
                        {{ $author->name }}
                    </option>
                @endforeach
            </select>
        </label>

        <label>
            <span>Nổi bật</span>
            <select name="featured">
                <option value="">Tất cả</option>
                <option value="1" @selected((string) $filters['featured'] === '1')>Có</option>
                <option value="0" @selected((string) $filters['featured'] === '0')>Không</option>
            </select>
        </label>

        <label>
            <span>Từ ngày</span>
            <input type="date" name="from" value="{{ $filters['from'] }}">
        </label>

        <label>
            <span>Đến ngày</span>
            <input type="date" name="to" value="{{ $filters['to'] }}">
        </label>

        <div class="news-filter-actions">
            <button type="submit" class="news-btn news-btn-primary">Lọc</button>
            <a class="news-btn news-btn-ghost" href="{{ route('admin.news.index') }}">Xóa lọc</a>
        </div>
    </form>

    <div class="news-table-wrap">
        <table class="news-table">
            <thead>
                <tr>
                    <th>Ảnh</th>
                    <th>Tiêu đề</th>
                    <th>Chuyên mục</th>
                    <th>Tác giả</th>
                    <th>Trạng thái</th>
                    <th>Nổi bật</th>
                    <th>Lượt xem</th>
                    <th>Ngày đăng</th>
                    <th>Ngày tạo</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($news as $item)
                    <tr>
                        <td>
                            @if ($item->thumbnailUrl())
                                <img class="news-thumb" src="{{ $item->thumbnailUrl() }}" alt="{{ $item->thumbnail_alt ?: $item->title }}">
                            @else
                                <span class="news-thumb-empty">Lux Auto</span>
                            @endif
                        </td>
                        <td class="news-title-cell">
                            <a href="{{ route('admin.news.show', $item) }}">{{ $item->title }}</a>
                            <span>{{ $item->slug }}</span>
                        </td>
                        <td>{{ $item->category?->name ?? 'Chưa phân loại' }}</td>
                        <td>{{ $item->author?->name ?? 'Hệ thống' }}</td>
                        <td><span class="news-badge {{ $item->statusBadgeClass() }}">{{ $item->statusLabel() }}</span></td>
                        <td>
                            <span class="news-badge {{ $item->is_featured ? 'is-warning' : 'is-muted' }}">
                                {{ $item->is_featured ? 'Có' : 'Không' }}
                            </span>
                        </td>
                        <td class="news-number">{{ number_format($item->views_count) }}</td>
                        <td>{{ $item->effectivePublishedAt()?->format('d/m/Y H:i') ?? 'Chưa đăng' }}</td>
                        <td>{{ $item->created_at?->format('d/m/Y') }}</td>
                        <td>
                            <div class="news-row-actions">
                                <a class="news-mini-btn" href="{{ route('admin.news.show', $item) }}">Xem</a>
                                @can('news.edit')
                                    <a class="news-mini-btn is-edit" href="{{ route('admin.news.edit', $item) }}">Sửa</a>
                                @endcan
                                @can('news.delete')
                                    <form method="post" action="{{ route('admin.news.destroy', $item) }}" onsubmit="return confirm('Xóa bài viết này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="news-mini-btn is-danger" type="submit">Xóa</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="news-empty">Chưa có bài viết phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($news->hasPages())
        <div class="news-pagination">
            {{ $news->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection
