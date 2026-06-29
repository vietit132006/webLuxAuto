@extends('layouts.admin')

@section('title', $promotion->exists ? 'Sửa khuyến mãi' : 'Tạo khuyến mãi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-promotions.css')
    @endif
@endpush

@section('content')
@php
    $isEdit = $promotion->exists;
    $targetAll = (bool) old('target_all', $isEdit
        ? $promotion->targets->contains(fn ($target) => $target->target_type === \App\Models\PromotionTarget::TYPE_ALL)
        : true);
    $selectedBrandIds = collect(old('brand_ids', $promotion->targets->where('target_type', \App\Models\PromotionTarget::TYPE_BRAND)->pluck('target_id')->all()))
        ->map(fn ($id) => (string) $id);
    $selectedModelIds = collect(old('model_ids', $promotion->targets->where('target_type', \App\Models\PromotionTarget::TYPE_MODEL)->pluck('target_id')->all()))
        ->map(fn ($id) => (string) $id);
    $selectedCarIds = collect(old('car_ids', $promotion->targets->where('target_type', \App\Models\PromotionTarget::TYPE_CAR)->pluck('target_id')->all()))
        ->map(fn ($id) => (string) $id);
    $dateInput = fn ($value) => $value ? $value->format('Y-m-d\TH:i') : '';
    $amount = fn ($field) => old($field, $promotion->{$field} !== null ? (float) $promotion->{$field} : null);
@endphp

<div class="admin-promotions-page is-form">
    <div class="admin-promotions-head">
        <div>
            <h1>{{ $isEdit ? 'Sửa khuyến mãi' : 'Tạo khuyến mãi' }}</h1>
            <p>Marketing / {{ $isEdit ? $promotion->promotion_code : 'Chiến dịch mới' }}</p>
        </div>

        <div class="promotion-head-actions">
            <a class="promotion-secondary" href="{{ route('admin.promotions') }}">Danh sách</a>
            @if($isEdit && $promotion->is_public && $promotion->isActive())
                <a class="promotion-secondary" href="{{ route('promotions.show', $promotion->slug) }}" target="_blank" rel="noopener">Xem frontend</a>
            @endif
        </div>
    </div>

    @if($errors->any())
        <div class="promotion-alert is-error">{{ $errors->first() }}</div>
    @endif

    @if(session('success'))
        <div class="promotion-alert is-success">{{ session('success') }}</div>
    @endif

    <form class="promotion-form" method="post" action="{{ $isEdit ? route('admin.promotions.update', $promotion) : route('admin.promotions.store') }}" enctype="multipart/form-data" data-promotion-form>
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <section class="promotion-form-section">
            <div class="promotion-section-title">
                <h2>Thông tin chính</h2>
                <span>{{ $promotion->promotion_code ?: 'Tự sinh mã KM' }}</span>
            </div>

            <div class="promotion-form-grid">
                <div class="promotion-form-field is-wide">
                    <label for="title">Tên chương trình</label>
                    <input id="title" name="title" type="text" value="{{ old('title', $promotion->title) }}" maxlength="255" required data-promotion-title>
                </div>

                <div class="promotion-form-field">
                    <label for="promotion_code">Mã khuyến mãi</label>
                    <input id="promotion_code" name="promotion_code" type="text" value="{{ old('promotion_code', $promotion->promotion_code) }}" maxlength="20" placeholder="KM000001">
                </div>

                <div class="promotion-form-field">
                    <label for="slug">Slug</label>
                    <input id="slug" name="slug" type="text" value="{{ old('slug', $promotion->slug) }}" maxlength="255" data-promotion-slug>
                </div>

                <div class="promotion-form-field is-wide">
                    <label for="short_description">Mô tả ngắn</label>
                    <textarea id="short_description" name="short_description" rows="3" maxlength="1000">{{ old('short_description', $promotion->short_description) }}</textarea>
                </div>

                <div class="promotion-form-field is-wide">
                    <label for="content">Nội dung chi tiết</label>
                    <textarea id="content" name="content" rows="8">{{ old('content', $promotion->content) }}</textarea>
                </div>

                <div class="promotion-form-field">
                    <label for="banner_image">Banner</label>
                    <input id="banner_image" name="banner_image" type="file" accept=".jpg,.jpeg,.png,.webp" data-banner-input>
                    <span class="promotion-field-hint">JPG, PNG, WEBP tối đa 5MB.</span>
                </div>

                <div class="promotion-form-field">
                    <label for="banner_alt">Alt banner</label>
                    <input id="banner_alt" name="banner_alt" type="text" value="{{ old('banner_alt', $promotion->banner_alt) }}" maxlength="255">
                </div>

                <div class="promotion-banner-preview">
                    @if($promotion->bannerUrl())
                        <img src="{{ $promotion->bannerUrl() }}" alt="{{ $promotion->banner_alt ?: $promotion->title }}" data-banner-preview>
                    @else
                        <div data-banner-preview>Chưa có banner</div>
                    @endif
                </div>
            </div>
        </section>

        <section class="promotion-form-section">
            <div class="promotion-section-title">
                <h2>Loại ưu đãi</h2>
                <span>{{ $promotion->discountLabel() }}</span>
            </div>

            <div class="promotion-form-grid">
                <div class="promotion-form-field">
                    <label for="promotion_type">Loại khuyến mãi</label>
                    <select id="promotion_type" name="promotion_type" required>
                        @foreach($promotionTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('promotion_type', $promotion->promotion_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="promotion-form-field">
                    <label for="discount_type">Kiểu giảm giá</label>
                    <select id="discount_type" name="discount_type">
                        @foreach($discountTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('discount_type', $promotion->discount_type ?: \App\Models\Promotion::DISCOUNT_NONE) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="promotion-form-field">
                    <label for="discount_value">Giá trị giảm</label>
                    <input id="discount_value" name="discount_value" type="number" min="0" step="0.01" value="{{ $amount('discount_value') }}">
                </div>

                <div class="promotion-form-field">
                    <label for="max_discount_value">Giảm tối đa</label>
                    <input id="max_discount_value" name="max_discount_value" type="number" min="0" step="1000" value="{{ $amount('max_discount_value') }}">
                </div>

                <div class="promotion-form-field is-wide">
                    <label for="gift_description">Quà tặng / mô tả ưu đãi</label>
                    <textarea id="gift_description" name="gift_description" rows="3" maxlength="2000">{{ old('gift_description', $promotion->gift_description) }}</textarea>
                </div>

                <div class="promotion-form-field is-wide">
                    <label for="terms">Điều khoản áp dụng</label>
                    <textarea id="terms" name="terms" rows="4" maxlength="5000">{{ old('terms', $promotion->terms) }}</textarea>
                </div>
            </div>
        </section>

        <section class="promotion-form-section">
            <div class="promotion-section-title">
                <h2>Thời gian và trạng thái</h2>
                <span>{{ $promotion->statusLabel() }}</span>
            </div>

            <div class="promotion-form-grid">
                <div class="promotion-form-field">
                    <label for="start_at">Ngày bắt đầu</label>
                    <input id="start_at" name="start_at" type="datetime-local" value="{{ old('start_at', $dateInput($promotion->start_at)) }}">
                </div>

                <div class="promotion-form-field">
                    <label for="end_at">Ngày kết thúc</label>
                    <input id="end_at" name="end_at" type="datetime-local" value="{{ old('end_at', $dateInput($promotion->end_at)) }}">
                </div>

                <div class="promotion-form-field">
                    <label for="status">Trạng thái</label>
                    <select id="status" name="status" required>
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', $promotion->status) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="promotion-form-field">
                    <label for="priority">Độ ưu tiên</label>
                    <input id="priority" name="priority" type="number" min="-999" max="999" step="1" value="{{ old('priority', $promotion->priority ?? 0) }}">
                </div>

                <div class="promotion-form-field">
                    <label for="usage_limit">Giới hạn lượt dùng</label>
                    <input id="usage_limit" name="usage_limit" type="number" min="1" step="1" value="{{ old('usage_limit', $promotion->usage_limit) }}">
                </div>

                <div class="promotion-toggle-grid">
                    <label class="promotion-toggle">
                        <input type="checkbox" name="is_public" value="1" @checked(old('is_public', $promotion->is_public ?? true))>
                        <span>Hiển thị công khai</span>
                    </label>
                    <label class="promotion-toggle">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $promotion->is_featured))>
                        <span>Nổi bật</span>
                    </label>
                    <label class="promotion-toggle">
                        <input type="checkbox" name="auto_apply" value="1" @checked(old('auto_apply', $promotion->auto_apply))>
                        <span>Tự động gợi ý áp dụng</span>
                    </label>
                </div>
            </div>
        </section>

        <section class="promotion-form-section">
            <div class="promotion-section-title">
                <h2>Đối tượng áp dụng</h2>
                <span>{{ $promotion->targetSummary() }}</span>
            </div>

            <input type="hidden" name="target_all" value="0">
            <label class="promotion-toggle promotion-target-all">
                <input type="checkbox" name="target_all" value="1" @checked($targetAll) data-target-all>
                <span>Áp dụng toàn bộ xe</span>
            </label>

            <div class="promotion-target-grid" data-target-custom>
                <div class="promotion-form-field">
                    <label for="brand_ids">Hãng xe</label>
                    <select id="brand_ids" name="brand_ids[]" multiple size="7">
                        @foreach($brands as $brand)
                            <option value="{{ $brand->brand_id }}" @selected($selectedBrandIds->contains((string) $brand->brand_id))>{{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="promotion-form-field">
                    <label for="model_ids">Model xe</label>
                    <select id="model_ids" name="model_ids[]" multiple size="7">
                        @foreach($carModels as $model)
                            <option value="{{ $model->id }}" @selected($selectedModelIds->contains((string) $model->id))>
                                {{ $model->brand?->name }} {{ $model->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="promotion-form-field">
                    <label for="car_ids">Xe cụ thể</label>
                    <select id="car_ids" name="car_ids[]" multiple size="7">
                        @foreach($cars as $car)
                            <option value="{{ $car->car_id }}" @selected($selectedCarIds->contains((string) $car->car_id))>
                                {{ $car->title }}{{ $car->vin ? ' - VIN ' . $car->vin : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </section>

        <section class="promotion-form-section">
            <div class="promotion-section-title">
                <h2>SEO</h2>
                <span>Frontend / khuyến mãi</span>
            </div>

            <div class="promotion-form-grid">
                <div class="promotion-form-field">
                    <label for="seo_title">SEO title</label>
                    <input id="seo_title" name="seo_title" type="text" value="{{ old('seo_title', $promotion->seo_title) }}" maxlength="255">
                </div>

                <div class="promotion-form-field is-wide">
                    <label for="seo_description">SEO description</label>
                    <textarea id="seo_description" name="seo_description" rows="3" maxlength="1000">{{ old('seo_description', $promotion->seo_description) }}</textarea>
                </div>
            </div>
        </section>

        <div class="promotion-form-actions">
            <button class="promotion-primary" type="submit">{{ $isEdit ? 'Lưu thay đổi' : 'Tạo khuyến mãi' }}</button>
            <a class="promotion-secondary" href="{{ route('admin.promotions') }}">Hủy</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.querySelector('[data-promotion-form]');

    if (!form) {
        return;
    }

    const titleInput = form.querySelector('[data-promotion-title]');
    const slugInput = form.querySelector('[data-promotion-slug]');
    const targetAll = form.querySelector('[data-target-all]');
    const targetCustom = form.querySelector('[data-target-custom]');
    const bannerInput = form.querySelector('[data-banner-input]');
    const bannerPreview = form.querySelector('[data-banner-preview]');

    const slugify = (value) => value
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/đ/g, 'd')
        .replace(/Đ/g, 'D')
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', () => {
            if (slugInput.value.trim() === '') {
                slugInput.value = slugify(titleInput.value);
            }
        });
    }

    const syncTargets = () => {
        if (!targetAll || !targetCustom) {
            return;
        }

        targetCustom.classList.toggle('is-disabled', targetAll.checked);
        targetCustom.querySelectorAll('select').forEach((select) => {
            select.disabled = targetAll.checked;
        });
    };

    targetAll?.addEventListener('change', syncTargets);
    syncTargets();

    bannerInput?.addEventListener('change', () => {
        const file = bannerInput.files && bannerInput.files[0];

        if (!file || !bannerPreview) {
            return;
        }

        const url = URL.createObjectURL(file);

        if (bannerPreview.tagName === 'IMG') {
            bannerPreview.src = url;
        } else {
            const img = document.createElement('img');
            img.src = url;
            img.alt = 'Preview banner';
            img.dataset.bannerPreview = '';
            bannerPreview.replaceWith(img);
        }
    });
})();
</script>
@endpush
