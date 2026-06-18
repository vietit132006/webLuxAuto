@extends('layouts.admin')
@section('title', 'Chi tiết tin tức')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-news-show.css')
    @endif
@endpush


@section('content')

<div class="wrap admin-news-show-inline-6">
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
                <div class="admin-news-show-inline-5">
                    CHƯA CÓ ẢNH BÌA
                </div>
            @endif

            <div class="info-row">
                <span class="info-label">ID bài viết:</span>
                <span class="info-value">#{{ $news->news_id }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Đường dẫn (Slug):</span>
                <span class="info-value admin-news-show-inline-4">{{ $news->slug }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Trạng thái:</span>
                <span class="info-value">
                    @if($news->status == 1)
                        <span class="admin-news-show-inline-3">Đã xuất bản</span>
                    @else
                        <span class="admin-news-show-inline-2">Đang lưu nháp</span>
                    @endif
                </span>
            </div>
            <div class="info-row">
                <span class="info-label">Ngày tạo:</span>
                <span class="info-value">{{ $news->created_at ? $news->created_at->format('H:i - d/m/Y') : 'N/A' }}</span>
            </div>
            <div class="info-row admin-news-show-inline-1">
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