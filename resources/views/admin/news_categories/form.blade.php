@extends('layouts.admin')

@php
    $isEdit = $category->exists;
@endphp

@section('title', $isEdit ? 'Sửa chuyên mục tin' : 'Thêm chuyên mục tin')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-news-categories.css')
    @endif
@endpush

@section('content')
<div class="news-cat-page is-form">
    <div class="news-cat-head">
        <div>
            <p class="news-cat-kicker">CMS Tin tức</p>
            <h1>{{ $isEdit ? 'Sửa chuyên mục' : 'Thêm chuyên mục' }}</h1>
        </div>
        <a class="news-cat-btn is-secondary" href="{{ route('admin.news-categories.index') }}">Quay lại</a>
    </div>

    @if ($errors->any())
        <div class="news-cat-alert is-danger">{{ $errors->first() }}</div>
    @endif

    <form class="news-cat-form" method="post" action="{{ $isEdit ? route('admin.news-categories.update', $category) : route('admin.news-categories.store') }}">
        @csrf
        @if ($isEdit)
            @method('PUT')
        @endif

        <section class="news-cat-panel">
            <div class="news-cat-panel-head">
                <h2>Thông tin chuyên mục</h2>
                <span>Category</span>
            </div>

            <div class="news-cat-two-col">
                <label class="news-cat-field">
                    <span>Tên chuyên mục <b>*</b></span>
                    <input type="text" name="name" value="{{ old('name', $category->name) }}" required maxlength="255">
                    @error('name') <small>{{ $message }}</small> @enderror
                </label>

                <label class="news-cat-field">
                    <span>Slug</span>
                    <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" maxlength="255" placeholder="tu-dong-sinh-neu-de-trong">
                    @error('slug') <small>{{ $message }}</small> @enderror
                </label>
            </div>

            <label class="news-cat-field">
                <span>Mô tả</span>
                <textarea name="description" rows="4" maxlength="2000">{{ old('description', $category->description) }}</textarea>
            </label>

            <div class="news-cat-two-col">
                <label class="news-cat-field">
                    <span>Thứ tự</span>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0" step="1">
                </label>

                <label class="news-cat-check">
                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))>
                    <span>Hiển thị chuyên mục</span>
                </label>
            </div>
        </section>

        <section class="news-cat-panel">
            <div class="news-cat-panel-head">
                <h2>SEO</h2>
                <span>Search</span>
            </div>

            <label class="news-cat-field">
                <span>SEO title</span>
                <input type="text" name="seo_title" value="{{ old('seo_title', $category->seo_title) }}" maxlength="255">
            </label>

            <label class="news-cat-field">
                <span>SEO description</span>
                <textarea name="seo_description" rows="3" maxlength="500">{{ old('seo_description', $category->seo_description) }}</textarea>
            </label>
        </section>

        <div class="news-cat-save">
            <button class="news-cat-btn is-primary" type="submit">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo chuyên mục' }}</button>
        </div>
    </form>
</div>
@endsection
