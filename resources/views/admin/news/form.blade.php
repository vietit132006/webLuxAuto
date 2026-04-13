@extends('layouts.admin')
@section('title', isset($news) ? 'Sửa bài viết' : 'Viết bài mới')

@section('content')
<div class="wrap" style="max-width: 900px; background: var(--surface); padding: 2.5rem; border-radius: 12px; border: 1px solid var(--border);">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
        <h1 style="margin: 0; font-size: 1.5rem; color: var(--accent);">
            {{ isset($news) ? 'Sửa bài viết' : 'Tạo Bài Viết Mới' }}
        </h1>
        <a href="{{ route('admin.news.index') }}" style="color: var(--muted); font-size: 0.9rem; text-decoration: underline;">← Quay lại danh sách</a>
    </div>

    <form method="POST" action="{{ isset($news) ? route('admin.news.update', $news->news_id) : route('admin.news.store') }}" enctype="multipart/form-data">
        @csrf
        @if(isset($news)) @method('PUT') @endif

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem;">

            <div>
                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Tiêu đề bài viết (*)</label>
                    <input type="text" name="title" value="{{ old('title', $news->title ?? '') }}" required placeholder="Nhập tiêu đề thật hấp dẫn..." style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff; font-size: 1.1rem;">
                    @error('title') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Tóm tắt ngắn (Hiển thị ở danh sách tin)</label>
                    <textarea name="summary" rows="3" placeholder="Viết một đoạn ngắn gọn tóm tắt nội dung bài viết..." style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff; resize: vertical; font-family: inherit;">{{ old('summary', $news->summary ?? '') }}</textarea>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Nội dung chi tiết (*)</label>
                    <textarea name="content" rows="15" required placeholder="Nội dung bài viết chi tiết..." style="width: 100%; padding: 1rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff; resize: vertical; font-family: inherit; line-height: 1.6;">{{ old('content', $news->content ?? '') }}</textarea>
                    @error('content') <div style="color: #f87171; font-size: 0.85rem; margin-top: 5px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div>
                <div style="background: rgba(255, 255, 255, 0.02); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border); margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; color: var(--text); font-weight: 600;">Trạng thái</label>
                    <select name="status" style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff; margin-bottom: 1rem;">
                        <option value="1" {{ old('status', $news->status ?? 1) == 1 ? 'selected' : '' }}>✅ Xuất bản ngay</option>
                        <option value="0" {{ old('status', $news->status ?? 1) == 0 ? 'selected' : '' }}>📝 Lưu nháp (Ẩn)</option>
                    </select>

                    <button type="submit" style="width: 100%; background: var(--accent); color: #000; padding: 0.8rem; border: none; border-radius: 6px; font-weight: bold; cursor: pointer; font-size: 1rem;">
                        Lưu Bài Viết
                    </button>
                </div>

                <div style="background: rgba(255, 255, 255, 0.02); padding: 1.5rem; border-radius: 8px; border: 1px solid var(--border);">
                    <label style="display: block; margin-bottom: 1rem; color: var(--text); font-weight: 600;">Ảnh bìa bài viết</label>

                    @if(isset($news) && $news->image)
                        <div style="margin-bottom: 1rem;">
                            <img src="{{ asset('storage/' . $news->image) }}" alt="Ảnh hiện tại" style="width: 100%; border-radius: 6px; border: 1px solid var(--border);">
                            <div style="text-align: center; font-size: 0.8rem; color: var(--muted); margin-top: 5px;">Ảnh hiện tại</div>
                        </div>
                    @endif

                    <input type="file" name="image" accept="image/*" style="width: 100%; color: var(--muted); font-size: 0.9rem;">
                    <small style="display: block; margin-top: 0.5rem; color: var(--muted); font-size: 0.8rem;">Định dạng: JPG, PNG. Dung lượng tối đa: 2MB.</small>
                </div>
            </div>

        </div>
    </form>
</div>
@endsection
