@extends('layouts.admin')

@section('title', 'Quản lý tin tức')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-news-index.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    <div class="header-actions">
        <h1 class="page-title">Quản lý tin tức</h1>

        @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
            <a href="{{ route('admin.news.create') }}" class="btn-add">+ Viết bài mới</a>
        @endif
    </div>

    @if(session('success'))

        <div id="success-alert" class="flash-alert">
            <span>✅ {{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="closeAlert()" aria-label="Đóng">&times;</button>
        </div>

        <script>
            function closeAlert() {
                const alertBox = document.getElementById('success-alert');
                if (alertBox) {
                    alertBox.classList.add('hide');
                    setTimeout(() => {
                        alertBox.remove();
                    }, 500);
                }
            }

            setTimeout(() => {
                closeAlert();
            }, 2000);
        </script>
    @endif

    <form class="search-bar" method="get" action="{{ route('admin.news.index') }}">
        <input type="search" name="q" value="{{ $search ?? '' }}" placeholder="Tìm theo tiêu đề bài viết…" autocomplete="off">
        <button type="submit">Tìm kiếm</button>
    </form>

    @if ($news->isEmpty())
        <div class="empty-state">Không có bài viết phù hợp. Thử bộ lọc khác hoặc <a href="{{ route('admin.news.index') }}">xóa tìm kiếm</a>.</div>
    @else
        <div class="table-responsive">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th width="100">Hình ảnh</th>
                        <th>Tiêu đề</th>
                        <th>Trạng thái</th>
                        <th>Ngày tạo</th>
                        <th width="160">Hành động</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($news as $item)
                    <tr>
                        <td>
                            @if($item->image)
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->title }}" class="table-img">
                            @else
                                <span class="admin-news-index-inline-6">Trống</span>
                            @endif
                        </td>
                        <td class="admin-news-index-inline-5">{{ $item->title }}</td>

                        <td>
                            @if($item->status == 1)
                                <span class="admin-news-index-inline-4">Đã xuất bản</span>
                            @else
                                <span class="admin-news-index-inline-3">Đang ẩn</span>
                            @endif
                        </td>

                        <td class="admin-news-index-inline-2">{{ $item->created_at->format('d/m/Y') }}</td>

                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.news.show', $item->news_id) }}" class="btn-sm btn-view">Xem</a>

                                @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                                    <a href="{{ route('admin.news.edit', $item->news_id) }}" class="btn-sm btn-edit">Sửa</a>

                                    <form class="admin-news-index-inline-1" action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-sm btn-delete">Xóa</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($news->hasPages())
            <div class="pagination-wrap">
                {{ $news->links('pagination.lux') }}
            </div>
        @endif
    @endif
</div>
@endsection