@extends('layouts.admin')

@php
    $isEdit = isset($brand);
    $isActive = (string) old('is_active', $isEdit ? (int) $brand->is_active : 1);
@endphp

@section('title', $isEdit ? 'Sửa hãng xe' : 'Thêm hãng xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-brands-form.css')
    @endif
@endpush

@section('content')
<div class="brands-form-page">
    <div class="brands-form-head">
        <div>
            <p class="brands-eyebrow">Danh mục xe</p>
            <h1>{{ $isEdit ? 'Sửa hãng xe' : 'Thêm hãng xe' }}</h1>
        </div>
        <a class="brands-back-link" href="{{ route('admin.brands.index') }}">
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Quay lại
        </a>
    </div>

    <form class="brands-form" method="POST" action="{{ $isEdit ? route('admin.brands.update', $brand->brand_id) : route('admin.brands.store') }}" enctype="multipart/form-data">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        @if ($errors->any())
            <div class="brands-alert brands-alert-error" role="alert">
                <strong>Chưa thể lưu hãng xe.</strong>
                <span>Vui lòng kiểm tra các trường được đánh dấu.</span>
            </div>
        @endif

        @if (session('error'))
            <div class="brands-alert brands-alert-error" role="alert">
                <strong>Chưa thể lưu hãng xe.</strong>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <div class="brands-form-grid">
            <section class="brands-form-section brands-form-section-main" aria-labelledby="brand-main-info">
                <div class="brands-section-head">
                    <h2 id="brand-main-info">Thông tin hãng</h2>
                </div>

                <div class="brands-field">
                    <label for="name">Tên hãng xe <span>*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name', $brand->name ?? '') }}" required maxlength="255" autocomplete="off">
                    @error('name')
                        <p class="brands-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="brands-field">
                    <label for="country">Quốc gia</label>
                    <input id="country" type="text" name="country" value="{{ old('country', $brand->country ?? '') }}" maxlength="100" autocomplete="off">
                    @error('country')
                        <p class="brands-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="brands-field">
                    <label for="slug">Slug</label>
                    <input id="slug" type="text" name="slug" value="{{ old('slug', $brand->slug ?? '') }}" maxlength="255" autocomplete="off">
                    @error('slug')
                        <p class="brands-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="brands-field">
                    <label for="description">Mô tả</label>
                    <textarea id="description" name="description" rows="6">{{ old('description', $brand->description ?? '') }}</textarea>
                    @error('description')
                        <p class="brands-field-error">{{ $message }}</p>
                    @enderror
                </div>
            </section>

            <aside class="brands-form-section brands-form-section-side" aria-labelledby="brand-publish-info">
                <div class="brands-section-head">
                    <h2 id="brand-publish-info">Hiển thị</h2>
                </div>

                <div class="brands-logo-box" data-logo-cropper data-initial-logo="{{ $isEdit && $brand->logo_url ? $brand->logo_url : '' }}">
                    <input type="hidden" name="logo_cropped_data" data-logo-cropped>

                    <div class="brands-logo-cropper">
                        <canvas class="brands-logo-canvas" data-logo-canvas width="512" height="512" aria-label="Khung cắt logo"></canvas>
                        <div class="brands-logo-placeholder" data-logo-placeholder @if($isEdit && $brand->logo_url) hidden @endif>
                            <svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 8.25h16.5M5.25 8.25l1.2 10.05a1.5 1.5 0 0 0 1.49 1.32h8.12a1.5 1.5 0 0 0 1.49-1.32l1.2-10.05M8.25 8.25V6A3.75 3.75 0 0 1 12 2.25 3.75 3.75 0 0 1 15.75 6v2.25" />
                            </svg>
                        </div>
                    </div>

                    <div class="brands-crop-controls">
                        <label class="brands-crop-zoom" for="logo_zoom">
                            <span>Thu phóng</span>
                            <input id="logo_zoom" type="range" min="0" max="100" value="0" data-logo-zoom>
                        </label>
                        <button class="brands-crop-btn" type="button" data-logo-fit>Vừa khung</button>
                    </div>

                    <div class="brands-field brands-field-file">
                        <label for="logo">Logo hãng</label>
                        <input id="logo" type="file" name="logo" accept=".jpg,.jpeg,.png,.webp,.svg,image/jpeg,image/png,image/webp,image/svg+xml">
                        @error('logo')
                            <p class="brands-field-error">{{ $message }}</p>
                        @enderror
                        @error('logo_cropped_data')
                            <p class="brands-field-error">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <input type="hidden" name="is_active" value="0">
                <label class="brands-switch">
                    <input type="checkbox" name="is_active" value="1" @checked($isActive === '1')>
                    <span class="brands-switch-ui" aria-hidden="true"></span>
                    <span>
                        <strong>Trạng thái hiển thị</strong>
                        <em>{{ $isActive === '1' ? 'Đang hiển thị' : 'Đang ẩn' }}</em>
                    </span>
                </label>
                @error('is_active')
                    <p class="brands-field-error">{{ $message }}</p>
                @enderror

                <div class="brands-field">
                    <label for="sort_order">Thứ tự hiển thị</label>
                    <input id="sort_order" type="number" name="sort_order" value="{{ old('sort_order', $brand->sort_order ?? 0) }}" step="1">
                    @error('sort_order')
                        <p class="brands-field-error">{{ $message }}</p>
                    @enderror
                </div>
            </aside>
        </div>

        <section class="brands-form-section brands-seo-section" aria-labelledby="brand-seo-info">
            <div class="brands-section-head">
                <h2 id="brand-seo-info">SEO</h2>
            </div>

            <div class="brands-two-cols">
                <div class="brands-field">
                    <label for="seo_title">SEO title</label>
                    <input id="seo_title" type="text" name="seo_title" value="{{ old('seo_title', $brand->seo_title ?? '') }}" maxlength="255">
                    @error('seo_title')
                        <p class="brands-field-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="brands-field">
                    <label for="seo_description">SEO description</label>
                    <textarea id="seo_description" name="seo_description" rows="3" maxlength="500">{{ old('seo_description', $brand->seo_description ?? '') }}</textarea>
                    @error('seo_description')
                        <p class="brands-field-error">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </section>

        <div class="brands-form-actions">
            <button class="brands-primary-btn" type="submit">
                <svg width="17" height="17" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75 10.5 18.75 19.5 5.25" />
                </svg>
                Lưu hãng xe
            </button>
            <a class="brands-secondary-btn" href="{{ route('admin.brands.index') }}">Hủy</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var outputSize = 512;
    var background = '#f8fafc';

    function clampState(state) {
        var drawnWidth = state.image.width * state.scale;
        var drawnHeight = state.image.height * state.scale;

        if (drawnWidth <= outputSize) {
            state.x = (outputSize - drawnWidth) / 2;
        } else {
            state.x = Math.min(0, Math.max(outputSize - drawnWidth, state.x));
        }

        if (drawnHeight <= outputSize) {
            state.y = (outputSize - drawnHeight) / 2;
        } else {
            state.y = Math.min(0, Math.max(outputSize - drawnHeight, state.y));
        }
    }

    function draw(cropper) {
        var context = cropper.canvas.getContext('2d');
        context.clearRect(0, 0, outputSize, outputSize);
        context.fillStyle = background;
        context.fillRect(0, 0, outputSize, outputSize);

        if (!cropper.state.image) {
            return;
        }

        clampState(cropper.state);
        context.imageSmoothingQuality = 'high';
        context.drawImage(
            cropper.state.image,
            cropper.state.x,
            cropper.state.y,
            cropper.state.image.width * cropper.state.scale,
            cropper.state.image.height * cropper.state.scale
        );
    }

    function setZoomValue(cropper) {
        if (!cropper.state.image) {
            cropper.zoom.value = 0;
            cropper.zoom.disabled = true;
            return;
        }

        cropper.zoom.disabled = false;
        var range = cropper.state.maxScale - cropper.state.minScale;
        cropper.zoom.value = range <= 0 ? 0 : Math.round(((cropper.state.scale - cropper.state.minScale) / range) * 100);
    }

    function fitImage(cropper, markDirty) {
        if (!cropper.state.image) {
            return;
        }

        var image = cropper.state.image;
        cropper.state.minScale = Math.min(outputSize / image.width, outputSize / image.height);
        cropper.state.maxScale = Math.max(outputSize / image.width, outputSize / image.height) * 4;
        cropper.state.scale = cropper.state.minScale;
        cropper.state.x = (outputSize - image.width * cropper.state.scale) / 2;
        cropper.state.y = (outputSize - image.height * cropper.state.scale) / 2;
        cropper.state.dirty = Boolean(markDirty);
        setZoomValue(cropper);
        draw(cropper);
    }

    function loadImage(cropper, src, markDirty) {
        if (!src) {
            cropper.placeholder.hidden = false;
            cropper.state.image = null;
            cropper.hidden.value = '';
            draw(cropper);
            return;
        }

        var image = new Image();
        image.onload = function () {
            cropper.placeholder.hidden = true;
            cropper.state.image = image;
            fitImage(cropper, markDirty);
        };
        image.onerror = function () {
            cropper.placeholder.hidden = false;
            cropper.state.image = null;
            cropper.hidden.value = '';
            draw(cropper);
        };
        image.src = src;
    }

    function exportCrop(cropper) {
        if (!cropper.state.image) {
            cropper.hidden.value = '';
            return;
        }

        draw(cropper);

        try {
            cropper.hidden.value = cropper.canvas.toDataURL('image/png');
        } catch (error) {
            cropper.hidden.value = '';
        }
    }

    document.querySelectorAll('[data-logo-cropper]').forEach(function (root) {
        var form = root.closest('form');
        var fileInput = root.querySelector('input[type="file"]');
        var cropper = {
            root: root,
            canvas: root.querySelector('[data-logo-canvas]'),
            placeholder: root.querySelector('[data-logo-placeholder]'),
            hidden: root.querySelector('[data-logo-cropped]'),
            zoom: root.querySelector('[data-logo-zoom]'),
            fitButton: root.querySelector('[data-logo-fit]'),
            state: {
                image: null,
                scale: 1,
                minScale: 1,
                maxScale: 4,
                x: 0,
                y: 0,
                dragging: false,
                startX: 0,
                startY: 0,
                originX: 0,
                originY: 0,
                dirty: false
            }
        };

        draw(cropper);
        loadImage(cropper, root.dataset.initialLogo, false);

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                var file = fileInput.files && fileInput.files[0];
                if (!file) {
                    loadImage(cropper, root.dataset.initialLogo, false);
                    return;
                }

                var reader = new FileReader();
                reader.onload = function (event) {
                    loadImage(cropper, event.target.result, true);
                };
                reader.readAsDataURL(file);
            });
        }

        cropper.zoom.addEventListener('input', function () {
            if (!cropper.state.image) {
                return;
            }

            var oldScale = cropper.state.scale;
            var centerSourceX = (outputSize / 2 - cropper.state.x) / oldScale;
            var centerSourceY = (outputSize / 2 - cropper.state.y) / oldScale;
            var value = Number(cropper.zoom.value) / 100;

            cropper.state.scale = cropper.state.minScale + (cropper.state.maxScale - cropper.state.minScale) * value;
            cropper.state.x = outputSize / 2 - centerSourceX * cropper.state.scale;
            cropper.state.y = outputSize / 2 - centerSourceY * cropper.state.scale;
            cropper.state.dirty = true;
            draw(cropper);
        });

        cropper.fitButton.addEventListener('click', function () {
            fitImage(cropper, true);
        });

        cropper.canvas.addEventListener('pointerdown', function (event) {
            if (!cropper.state.image) {
                return;
            }

            cropper.canvas.setPointerCapture(event.pointerId);
            cropper.state.dragging = true;
            cropper.state.startX = event.clientX;
            cropper.state.startY = event.clientY;
            cropper.state.originX = cropper.state.x;
            cropper.state.originY = cropper.state.y;
        });

        cropper.canvas.addEventListener('pointermove', function (event) {
            if (!cropper.state.dragging) {
                return;
            }

            var rect = cropper.canvas.getBoundingClientRect();
            cropper.state.x = cropper.state.originX + ((event.clientX - cropper.state.startX) / rect.width) * outputSize;
            cropper.state.y = cropper.state.originY + ((event.clientY - cropper.state.startY) / rect.height) * outputSize;
            cropper.state.dirty = true;
            draw(cropper);
        });

        cropper.canvas.addEventListener('pointerup', function (event) {
            cropper.state.dragging = false;
            cropper.canvas.releasePointerCapture(event.pointerId);
        });

        cropper.canvas.addEventListener('pointercancel', function () {
            cropper.state.dragging = false;
        });

        if (form) {
            form.addEventListener('submit', function () {
                if (cropper.state.image && (cropper.state.dirty || (fileInput && fileInput.files.length))) {
                    exportCrop(cropper);
                }
            });
        }
    });
})();
</script>
@endpush
