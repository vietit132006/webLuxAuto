@extends('layouts.admin')

@section('title', 'Quản lý tin tức')

@section('content')
<style>
    .header-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    .page-title {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
    }

    /* Nút Thêm mới */
    .btn-add {
        background: var(--accent);
        color: #0c0f14;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        font-size: 0.9rem;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        transition: all 0.2s ease;
    }
    .btn-add:hover {
        background: #e4d08a;
        transform: translateY(-1px);
    }

    .search-bar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-bottom: 1.75rem;
    }
    .search-bar input[type="search"] {
        flex: 1;
        min-width: 200px;
        padding: 0.6rem 0.9rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        font-size: 1rem;
    }
    .search-bar input:focus {
        outline: none;
        border-color: var(--accent-dim);
        box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15);
    }
    .search-bar button {
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        border: none;
        background: var(--accent);
        color: #0c0f14;
        font-weight: 600;
        cursor: pointer;
    }

    /* --- CSS CHO BẢNG ADMIN --- */
    .table-responsive {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--surface);
    }
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .admin-table th, .admin-table td {
        padding: 0.85rem 1rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .admin-table th {
        background: rgba(255, 255, 255, 0.03);
        color: var(--muted);
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .admin-table tr:last-child td {
        border-bottom: none;
    }
    .admin-table tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }
    .table-img {
        width: 80px;
        height: 50px;
        object-fit: cover;
        border-radius: 4px;
        background: #000;
    }

    /* --- CSS CHO CÁC NÚT HÀNH ĐỘNG --- */
    .action-btns {
        display: flex;
        gap: 0.35rem;
    }
    .btn-sm {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.35rem 0.6rem;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
        border: 1px solid transparent;
        transition: all 0.2s ease;
    }

    .btn-view { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
    .btn-view:hover { background: #3b82f6; color: #fff; }

    .btn-edit { background: rgba(234, 179, 8, 0.1); color: #facc15; }
    .btn-edit:hover { background: #eab308; color: #fff; }

    .btn-delete { background: rgba(239, 68, 68, 0.1); color: #f87171; }
    .btn-delete:hover { background: #ef4444; color: #fff; }

    /* CSS Phân trang */
    .pagination-wrap { margin-top: 2rem; display: flex; justify-content: center; }
    .lux-pag__inner { display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem; justify-content: center; }
    .lux-pag__btn, .lux-pag__num { display: inline-flex; align-items: center; justify-content: center; padding: 0.45rem 0.75rem; border-radius: 6px; border: 1px solid var(--border); color: var(--text); font-size: 0.875rem; }
    .lux-pag__btn:hover, .lux-pag__num:hover { border-color: var(--accent-dim); color: var(--accent); }
    .lux-pag__btn--disabled { color: var(--muted); cursor: not-allowed; opacity: 0.65; }
    .lux-pag__num--current { background: var(--surface); border-color: var(--accent-dim); color: var(--accent); font-weight: 600; }
    .lux-pag__dots { padding: 0.45rem 0.35rem; color: var(--muted); font-size: 0.875rem; }
    .empty-state { padding: 2rem; text-align: center; color: var(--muted); border: 1px dashed var(--border); border-radius: 12px; }
</style>

<div class="wrap">
    <div class="header-actions">
        <h1 class="page-title">Quản lý tin tức</h1>

        @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
            <a href="{{ route('admin.news.create') }}" class="btn-add">+ Viết bài mới</a>
        @endif
    </div>

    @if(session('success'))
        <style>
            .flash-alert {
                background-color: #d1fae5;
                color: #065f46;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                border: 1px solid #34d399;
                font-weight: 600;
                display: flex;
                justify-content: space-between;
                align-items: center;
                transition: opacity 0.5s ease, transform 0.5s ease;
            }
            .flash-alert.hide {
                opacity: 0;
                transform: translateY(-10px);
                pointer-events: none;
            }
            .btn-close-alert {
                background: none;
                border: none;
                color: #065f46;
                font-size: 1.5rem;
                line-height: 1;
                cursor: pointer;
                padding: 0 0 0 1rem;
                transition: transform 0.2s;
            }
            .btn-close-alert:hover {
                transform: scale(1.2);
                color: #047857;
            }
        </style>

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
                                <span style="font-size: 0.75rem; color: var(--muted); padding: 0.5rem; border: 1px dashed var(--border); border-radius: 4px; display: inline-block;">Trống</span>
                            @endif
                        </td>
                        <td style="font-weight: 600; color: var(--text);">{{ $item->title }}</td>

                        <td>
                            @if($item->status == 1)
                                <span style="color: #34d399; background: rgba(52, 211, 153, 0.1); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Đã xuất bản</span>
                            @else
                                <span style="color: #f87171; background: rgba(239, 68, 68, 0.1); padding: 0.2rem 0.5rem; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Đang ẩn</span>
                            @endif
                        </td>

                        <td style="color: var(--muted); font-size: 0.9rem;">{{ $item->created_at->format('d/m/Y') }}</td>

                        <td>
                            <div class="action-btns">
                                <a href="{{ route('admin.news.show', $item->news_id) }}" class="btn-sm btn-view">Xem</a>

                                @if(auth()->check() && in_array(auth()->user()->role, ['admin', 'staff']))
                                    <a href="{{ route('admin.news.edit', $item->news_id) }}" class="btn-sm btn-edit">Sửa</a>

                                    <form action="{{ route('admin.news.destroy', $item->news_id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Bạn có chắc chắn muốn xóa bài viết này không?');">
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
