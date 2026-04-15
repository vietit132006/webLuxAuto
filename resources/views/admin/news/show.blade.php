@extends('layouts.admin')
@section('title', 'Chi tiết tin tức')

@section('content')
<style>
    .detail-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid var(--border);
    }
    .detail-title {
        margin: 0;
        font-size: 1.5rem;
        color: var(--accent);
    }
    .btn-group {
        display: flex;
        gap: 10px;
    }
    .btn-back {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text);
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: 600;
        border: 1px solid var(--border);
        transition: 0.2s;
    }
    .btn-back:hover { background: rgba(255, 255, 255, 0.1); }

    .btn-edit-main {
        background: var(--accent);
        color: #000;
        padding: 0.5rem 1rem;
        border-radius: 6px;
        text-decoration: none;
        font-weight: bold;
        transition: 0.2s;
    }
    .btn-edit-main:hover { background: #e4d08a; }

    .detail-grid {
        display: grid;
        grid-template-columns: 350px 1fr;
        gap: 2rem;
        align-items: start;
    }

    /* Cột trái: Thông tin & Ảnh */
    .info-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
    }
    .info-img {
        width: 100%;
        border-radius: 8px;
        margin-bottom: 1.5rem;
        border: 1px solid var(--border);
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px dashed var(--border);
    }
    .info-label { color: var(--muted); font-size: 0.9rem; }
    .info-value { color: var(--text); font-weight: 600; text-align: right; }

    /* Cột phải: Nội dung chi tiết */
    .content-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 2.5rem;
    }
    .content-title {
        font-size: 1.8rem;
        color: var(--text);
        margin-top: 0;
        margin-bottom: 1rem;
        line-height: 1.4;
    }
    .content-summary {
        font-size: 1.1rem;
        color: var(--accent);
        font-style: italic;
        margin-bottom: 2rem;
        padding-left: 1rem;
        border-left: 3px solid var(--accent);
    }
    .content-body {
        font-size: 1.05rem;
        color: #d1d5db;
        line-height: 1.8;
    }
</style>

<div class="wrap" style="max-width: 1200px;">
    <div class="detail-header">
        <h1 class="detail-title">Kiểm duyệt bài viết</h1>
        <div class="btn-group">
            <a href="{{ route('admin.news.index') }}" class="btn-back">← Trở về danh sách</a>
            <a href="{{ route('admin.news.edit', $news->news_id) }}" class="btn-edit-main">Sửa bài viết này</a>
        </div>
    </div>

    <div class="detail-grid">
        <div class="info-card">
            @if($news->image)
                <img src="{{ asset('storage/' . $news->image) }}" alt="Ảnh bìa" class="info-img">
            @else
                <div style="width: 100%; height: 200px; background: #0a0d12; display: flex; align-items: center; justify-content: center; color: var(--muted); border-radius: 8px; margin-bottom: 1.5rem; border: 1px dashed var(--border);">
                    CHƯA CÓ ẢNH BÌA
                </div>
            @endif

            <div class="info-row">
                <span class="info-label">ID bài viết:</span>
                <span class="info-value">#{{ $news->news_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Đường dẫn (Slug):</span>
                <span class="info-value" style="word-break: break-all; color: #60a5fa;">{{ $news->slug }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Trạng thái:</span>
                <span class="info-value">
                    @if($news->status == 1)
                        <span style="color: #34d399;">Đã xuất bản</span>
                    @else
                        <span style="color: #f87171;">Đang lưu nháp</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày tạo:</span>
                <span class="info-value">{{ $news->created_at ? $news->created_at->format('H:i - d/m/Y') : 'N/A' }}</span>
            </div>
            <div class="info-row" style="border: none; margin-bottom: 0;">
                <span class="info-label">Cập nhật lần cuối:</span>
                <span class="info-value">{{ $news->updated_at ? $news->updated_at->format('H:i - d/m/Y') : 'N/A' }}</span>
            </div>
        </div>

        <div class="content-card">
            <h2 class="content-title">{{ $news->title }}</h2>

            @if($news->summary)
                <div class="content-summary">
                    {{ $news->summary }}
                </div>
            @endif

            <div class="content-body">
                {!! nl2br(e($news->content)) !!}
            </div>
        </div>
    </div>
</div>
@endsection
