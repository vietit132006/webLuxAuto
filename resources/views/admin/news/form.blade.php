@extends('layouts.admin')

@php
    $isEdit = isset($news) && $news?->exists;
    $tagValue = old('tags', $isEdit ? $news->tags->pluck('name')->implode(', ') : '');
    $dateTimeValue = fn ($value) => $value ? $value->format('Y-m-d\TH:i') : '';
@endphp

@section('title', $isEdit ? 'Sửa bài viết' : 'Viết bài mới')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-news-form.css')
    @endif
@endpush

@section('content')
<div class="news-form-page">
    <div class="news-form-head">
        <div>
            <p class="news-form-kicker">CMS Tin tức</p>
            <h1>{{ $isEdit ? 'Sửa bài viết' : 'Viết bài mới' }}</h1>
        </div>
        <a class="news-form-link" href="{{ route('admin.news.index') }}">Quay lại danh sách</a>
    </div>

    @if ($errors->any())
        <div class="news-form-alert">{{ $errors->first() }}</div>
    @endif

    <form id="news-form" class="news-form" method="post" action="{{ $isEdit ? route('admin.news.update', $news) : route('admin.news.store') }}" enctype="multipart/form-data">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <div class="news-form-grid">
            <div class="news-form-main">
                <section class="news-panel">
                    <div class="news-panel-head">
                        <h2>Nội dung chính</h2>
                        <span>Article</span>
                    </div>

                    <label class="news-field">
                        <span>Tiêu đề <b>*</b></span>
                        <input type="text" name="title" value="{{ old('title', $news->title ?? '') }}" required maxlength="255" placeholder="Ví dụ: Kinh nghiệm chọn SUV hạng sang cho gia đình">
                        @error('title') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="news-field">
                        <span>Slug</span>
                        <input type="text" name="slug" value="{{ old('slug', $news->slug ?? '') }}" maxlength="255" placeholder="tu-dong-sinh-neu-de-trong">
                        @error('slug') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="news-field">
                        <span>Tóm tắt</span>
                        <textarea name="summary" rows="4" maxlength="2000" placeholder="Đoạn giới thiệu ngắn hiển thị ở danh sách tin.">{{ old('summary', $news->summary ?? '') }}</textarea>
                        @error('summary') <small>{{ $message }}</small> @enderror
                    </label>

                    <div class="news-field">
                        <span>Nội dung <b>*</b></span>
                        <div class="news-editor-toolbar" aria-label="Công cụ soạn thảo">
                            <button type="button" data-command="formatBlock" data-value="h2">H2</button>
                            <button type="button" data-command="formatBlock" data-value="h3">H3</button>
                            <button type="button" data-command="bold"><strong>B</strong></button>
                            <button type="button" data-command="italic"><em>I</em></button>
                            <button type="button" data-command="insertUnorderedList">List</button>
                            <button type="button" data-command="formatBlock" data-value="blockquote">Quote</button>
                            <button type="button" data-command="createLink">Link</button>
                            <button type="button" data-command="removeFormat">Clear</button>
                        </div>
                        <div id="news-editor" class="news-editor" contenteditable="true">{!! old('content', $news->content ?? '') !!}</div>
                        <textarea id="news-content-input" name="content" hidden>{{ old('content', $news->content ?? '') }}</textarea>
                        @error('content') <small>{{ $message }}</small> @enderror
                    </div>

                    <div class="news-two-col">
                        <label class="news-field">
                            <span>Chuyên mục</span>
                            <select name="category_id">
                                <option value="">Chưa phân loại</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id', $news->category_id ?? '') === (string) $category->id)>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category_id') <small>{{ $message }}</small> @enderror
                        </label>

                        <label class="news-field">
                            <span>Tags</span>
                            <input type="text" name="tags" value="{{ $tagValue }}" placeholder="SUV, tư vấn mua xe, Mercedes">
                            @error('tags') <small>{{ $message }}</small> @enderror
                        </label>
                    </div>
                </section>

                <section class="news-panel">
                    <div class="news-panel-head">
                        <h2>SEO</h2>
                        <span>Search</span>
                    </div>

                    <label class="news-field">
                        <span>SEO title</span>
                        <input type="text" name="seo_title" value="{{ old('seo_title', $news->seo_title ?? '') }}" maxlength="255">
                    </label>

                    <label class="news-field">
                        <span>SEO description</span>
                        <textarea name="seo_description" rows="3" maxlength="500">{{ old('seo_description', $news->seo_description ?? '') }}</textarea>
                    </label>

                    <div class="news-two-col">
                        <label class="news-field">
                            <span>SEO keywords</span>
                            <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $news->seo_keywords ?? '') }}" maxlength="255">
                        </label>

                        <label class="news-field">
                            <span>Canonical URL</span>
                            <input type="url" name="canonical_url" value="{{ old('canonical_url', $news->canonical_url ?? '') }}" maxlength="255">
                        </label>
                    </div>
                </section>

                <section class="news-panel">
                    <div class="news-panel-head">
                        <h2>Liên kết bán hàng</h2>
                        <span>CRM</span>
                    </div>

                    <div class="news-three-col">
                        <label class="news-field">
                            <span>Hãng xe liên quan</span>
                            <select name="related_brand_id">
                                <option value="">Không chọn</option>
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->brand_id }}" @selected((string) old('related_brand_id', $news->related_brand_id ?? '') === (string) $brand->brand_id)>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="news-field">
                            <span>Model xe liên quan</span>
                            <select name="related_model_id">
                                <option value="">Không chọn</option>
                                @foreach ($models as $model)
                                    <option value="{{ $model->id }}" @selected((string) old('related_model_id', $news->related_model_id ?? '') === (string) $model->id)>
                                        {{ $model->brand?->name }} {{ $model->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="news-field">
                            <span>Xe liên quan</span>
                            <select name="related_car_id">
                                <option value="">Không chọn</option>
                                @foreach ($cars as $car)
                                    <option value="{{ $car->car_id }}" @selected((string) old('related_car_id', $news->related_car_id ?? '') === (string) $car->car_id)>
                                        {{ $car->title }} - {{ number_format((float) $car->price, 0, ',', '.') }} đ
                                    </option>
                                @endforeach
                            </select>
                        </label>
                    </div>

                    <div class="news-three-col">
                        <label class="news-field">
                            <span>CTA type</span>
                            <select name="cta_type">
                                @foreach ($ctaTypes as $value => $label)
                                    <option value="{{ $value }}" @selected(old('cta_type', $news->cta_type ?? 'none') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>

                        <label class="news-field">
                            <span>CTA label</span>
                            <input type="text" name="cta_label" value="{{ old('cta_label', $news->cta_label ?? '') }}" maxlength="255">
                        </label>

                        <label class="news-field">
                            <span>CTA URL</span>
                            <input type="text" name="cta_url" value="{{ old('cta_url', $news->cta_url ?? '') }}" maxlength="255">
                        </label>
                    </div>
                </section>
            </div>

            <aside class="news-form-side">
                <section class="news-panel">
                    <div class="news-panel-head">
                        <h2>Xuất bản</h2>
                        <span>Status</span>
                    </div>

                    <label class="news-field">
                        <span>Trạng thái</span>
                        <select name="status">
                            @foreach ($statuses as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $news->status ?? 'draft') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('status') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="news-field">
                        <span>Hẹn giờ đăng</span>
                        <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at', $dateTimeValue($news->scheduled_at ?? null)) }}">
                        @error('scheduled_at') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="news-field">
                        <span>Ngày xuất bản</span>
                        <input type="datetime-local" name="published_at" value="{{ old('published_at', $dateTimeValue($news->published_at ?? null)) }}">
                        @error('published_at') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="news-field">
                        <span>Tác giả</span>
                        <select name="author_id">
                            <option value="">Người đang đăng nhập</option>
                            @foreach ($authors as $author)
                                <option value="{{ $author->user_id }}" @selected((string) old('author_id', $news->author_id ?? auth()->id()) === (string) $author->user_id)>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </select>
                    </label>

                    <label class="news-check">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $news->is_featured ?? false))>
                        <span>Bài viết nổi bật</span>
                    </label>
                </section>

                <section class="news-panel">
                    <div class="news-panel-head">
                        <h2>Ảnh bìa</h2>
                        <span>Media</span>
                    </div>

                    <div class="news-cover-preview">
                        @if (($news ?? null)?->thumbnailUrl())
                            <img id="news-cover-preview" src="{{ $news->thumbnailUrl() }}" alt="{{ $news->thumbnail_alt ?: $news->title }}">
                        @else
                            <div id="news-cover-placeholder">Lux Auto</div>
                            <img id="news-cover-preview" src="" alt="" hidden>
                        @endif
                    </div>

                    <label class="news-field">
                        <span>Ảnh bìa</span>
                        <input id="news-cover-input" type="file" name="thumbnail" accept="image/jpeg,image/png,image/webp">
                        @error('thumbnail') <small>{{ $message }}</small> @enderror
                    </label>

                    <label class="news-field">
                        <span>Alt ảnh</span>
                        <input type="text" name="thumbnail_alt" value="{{ old('thumbnail_alt', $news->thumbnail_alt ?? '') }}" maxlength="255">
                    </label>
                </section>

                <div class="news-save-bar">
                    <button class="news-submit" type="submit">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo bài viết' }}</button>
                    @if ($isEdit)
                        <a href="{{ route('admin.news.show', $news) }}">Xem chi tiết</a>
                    @endif
                </div>
            </aside>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (() => {
        const editor = document.getElementById('news-editor');
        const contentInput = document.getElementById('news-content-input');
        const form = document.getElementById('news-form');

        if (editor && contentInput && form) {
            const syncContent = () => {
                contentInput.value = editor.innerHTML.trim();
            };

            document.querySelectorAll('[data-command]').forEach((button) => {
                button.addEventListener('click', () => {
                    const command = button.dataset.command;
                    let value = button.dataset.value || null;

                    if (command === 'createLink') {
                        value = window.prompt('URL');
                        if (!value) {
                            return;
                        }
                    }

                    editor.focus();
                    document.execCommand(command, false, value);
                    syncContent();
                });
            });

            editor.addEventListener('input', syncContent);
            form.addEventListener('submit', syncContent);
        }

        const coverInput = document.getElementById('news-cover-input');
        const coverPreview = document.getElementById('news-cover-preview');
        const coverPlaceholder = document.getElementById('news-cover-placeholder');

        coverInput?.addEventListener('change', () => {
            const file = coverInput.files?.[0];
            if (!file || !coverPreview) {
                return;
            }

            coverPreview.src = URL.createObjectURL(file);
            coverPreview.hidden = false;
            coverPlaceholder?.setAttribute('hidden', 'hidden');
        });
    })();
</script>
@endpush
