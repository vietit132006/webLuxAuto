@extends('layouts.admin')

@section('title', 'Chuyên mục tin tức')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-news-categories.css')
    @endif
@endpush

@section('content')
<div class="news-cat-page">
    <div class="news-cat-head">
        <div>
            <p class="news-cat-kicker">CMS Tin tức</p>
            <h1>Chuyên mục</h1>
        </div>
        <div class="news-cat-actions">
            <a class="news-cat-btn is-secondary" href="{{ route('admin.news.index') }}">Bài viết</a>
            @can('news_categories.create')
                <a class="news-cat-btn is-primary" href="{{ route('admin.news-categories.create') }}">Thêm chuyên mục</a>
            @endcan
        </div>
    </div>

    @if (session('success'))
        <div class="news-cat-alert is-success">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="news-cat-alert is-danger">{{ $errors->first() }}</div>
    @endif

    <form class="news-cat-filter" method="get" action="{{ route('admin.news-categories.index') }}">
        <label>
            <span>Tìm chuyên mục</span>
            <input type="search" name="q" value="{{ $search }}" placeholder="Tên hoặc slug">
        </label>
        <button class="news-cat-btn is-primary" type="submit">Tìm</button>
        <a class="news-cat-btn is-ghost" href="{{ route('admin.news-categories.index') }}">Xóa lọc</a>
    </form>

    <div class="news-cat-table-wrap">
        <table class="news-cat-table">
            <thead>
                <tr>
                    <th>Tên chuyên mục</th>
                    <th>Slug</th>
                    <th>Số bài viết</th>
                    <th>Trạng thái</th>
                    <th>Thứ tự</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($categories as $category)
                    <tr>
                        <td>
                            <strong>{{ $category->name }}</strong>
                            @if ($category->description)
                                <span>{{ \Illuminate\Support\Str::limit($category->description, 90) }}</span>
                            @endif
                        </td>
                        <td>{{ $category->slug }}</td>
                        <td class="news-cat-number">{{ number_format($category->news_count) }}</td>
                        <td>
                            <span class="news-cat-badge {{ $category->is_active ? 'is-success' : 'is-muted' }}">
                                {{ $category->is_active ? 'Hiển thị' : 'Đang ẩn' }}
                            </span>
                        </td>
                        <td>{{ $category->sort_order }}</td>
                        <td>
                            <div class="news-cat-row-actions">
                                @can('news_categories.edit')
                                    <a class="news-cat-mini" href="{{ route('admin.news-categories.edit', $category) }}">Sửa</a>
                                    <form method="post" action="{{ route('admin.news-categories.toggle-status', $category) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="news-cat-mini is-toggle" type="submit">{{ $category->is_active ? 'Ẩn' : 'Hiện' }}</button>
                                    </form>
                                @endcan
                                @can('news_categories.delete')
                                    <form method="post" action="{{ route('admin.news-categories.destroy', $category) }}" onsubmit="return confirm('Xóa chuyên mục này?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="news-cat-mini is-danger" type="submit" @disabled($category->news_count > 0)>Xóa</button>
                                    </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="news-cat-empty">Chưa có chuyên mục phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($categories->hasPages())
        <div class="news-cat-pagination">
            {{ $categories->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection
