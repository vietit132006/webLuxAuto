@extends('layouts.admin')
@section('title', isset($news) ? 'Sửa bài viết' : 'Viết bài mới')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-news-form.css')
    @endif
@endpush


@section('content')
<div class="wrap admin-news-form-inline-21">
    <div class="admin-news-form-inline-20">
        <h1 class="admin-news-form-inline-19">
            {{ isset($news) ? 'Sửa bài viết' : 'Tạo Bài Viết Mới' }}
        </h1>
        <a class="admin-news-form-inline-18" href="{{ route('admin.news.index') }}">← Quay lại danh sách</a>
    </div>

    <form method="POST" action="{{ isset($news) ? route('admin.news.update', $news->news_id) : route('admin.news.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($news)) @method('PUT') @endif

        <div class="admin-news-form-inline-17">

            <div>
                <div class="admin-news-form-inline-14">
                    <label class="admin-news-form-inline-10">Tiêu đề bài viết (*)</label>
                    <input class="admin-news-form-inline-16" type="text" name="title" value="{{ old('title', $news->title ?? '') }}" required placeholder="Nhập tiêu đề thật hấp dẫn...">
                    @error('title') <div class="admin-news-form-inline-12">{{ $message }}</div> @enderror
                </div>

                <div class="admin-news-form-inline-14">
                    <label class="admin-news-form-inline-10">Tóm tắt ngắn (Hiển thị ở danh sách tin)</label>
                    <textarea class="admin-news-form-inline-15" name="summary" rows="3" placeholder="Viết một đoạn ngắn gọn tóm tắt nội dung bài viết...">{{ old('summary', $news->summary ?? '') }}</textarea>
                </div>

                <div class="admin-news-form-inline-14">
                    <label class="admin-news-form-inline-10">Nội dung chi tiết (*)</label>
                    <textarea class="admin-news-form-inline-13" name="content" rows="15" required placeholder="Nội dung bài viết chi tiết...">{{ old('content', $news->content ?? '') }}</textarea>
                    @error('content') <div class="admin-news-form-inline-12">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <div class="admin-news-form-inline-11">
                    <label class="admin-news-form-inline-10">Trạng thái</label>
                    <select class="admin-news-form-inline-9" name="status">
                        <option value="1" {{ old('status', $news->status ?? 1) == 1 ? 'selected' : '' }}>✅ Xuất bản ngay</option>
                        <option value="0" {{ old('status', $news->status ?? 1) == 0 ? 'selected' : '' }}>📝 Lưu nháp (Ẩn)</option>
                    </select>

                    <button class="admin-news-form-inline-8" type="submit">
                        Lưu Bài Viết
                    </button>
                </div>

                <div class="admin-news-form-inline-7">
                    <label class="admin-news-form-inline-6">Ảnh bìa bài viết</label>

                    @if(isset($news) && $news->image)
                        <div class="admin-news-form-inline-5">
                            <img class="admin-news-form-inline-4" src="{{ asset('storage/' . $news->image) }}" alt="Ảnh hiện tại">
                            <div class="admin-news-form-inline-3">Ảnh hiện tại</div>
                        </div>
                    @endif

                    <input class="admin-news-form-inline-2" type="file" name="image" accept="image/*">
                    <small class="admin-news-form-inline-1">Định dạng: JPG, PNG. Dung lượng tối đa: 2MB.</small>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection