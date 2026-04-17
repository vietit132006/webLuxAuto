@extends('layouts.site')

@section('title', 'Sửa: '.$vehicle->title)

@section('content')
<style>
    .form-page h1 { margin: 0 0 1.25rem; font-size: 1.5rem; font-weight: 700; }
    .form-grid {
        display: grid;
        gap: 1rem;
        grid-template-columns: 1fr;
    }
    @media (min-width: 640px) {
        .form-grid { grid-template-columns: 1fr 1fr; }
    }
    .form-grid .full { grid-column: 1 / -1; }
    .field label {
        display: block;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--muted);
        margin-bottom: 0.35rem;
    }
    .field input, .field textarea, .field select {
        width: 100%;
        padding: 0.55rem 0.75rem;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: var(--surface);
        color: var(--text);
        font-size: 1rem;
    }
    .field input:focus, .field textarea:focus, .field select:focus {
        outline: none;
        border-color: var(--accent-dim);
    }
    .field textarea { min-height: 100px; resize: vertical; }
    .field .err { font-size: 0.8125rem; color: #f87171; margin-top: 0.25rem; }
    .check-row { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.25rem; }
    .check-row input { width: auto; }
    .form-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        margin-top: 1.25rem;
        padding-top: 1rem;
        border-top: 1px solid var(--border);
    }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.6rem 1.15rem;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.9375rem;
        cursor: pointer;
        border: none;
        text-decoration: none;
    }
    .btn-primary {
        background: linear-gradient(135deg, var(--accent), var(--accent-dim));
        color: #0c0f14;
    }
    .btn-primary:hover { filter: brightness(1.06); color: #0c0f14; }
    .btn-ghost {
        background: transparent;
        border: 1px solid var(--border);
        color: var(--text);
    }
    .btn-ghost:hover { border-color: var(--accent-dim); color: var(--accent); }
</style>

<div class="wrap form-page">
    <h1>Sửa thông tin xe</h1>

    <form method="post" action="{{ route('vehicles.update', $vehicle) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="field">
                <label for="brand">Hãng xe</label>
                <input id="brand" name="brand" value="{{ old('brand', $vehicle->brand) }}" required>
                @error('brand')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="model">Dòng xe</label>
                <input id="model" name="model" value="{{ old('model', $vehicle->model) }}" required>
                @error('model')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="year">Năm sản xuất</label>
                <input id="year" name="year" type="number" value="{{ old('year', $vehicle->year) }}" required>
                @error('year')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="price">Giá (VNĐ)</label>
                <input id="price" name="price" type="number" value="{{ old('price', $vehicle->price) }}" required min="0">
                @error('price')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="mileage_km">Số km đã đi</label>
                <input id="mileage_km" name="mileage_km" type="number" value="{{ old('mileage_km', $vehicle->mileage_km) }}" min="0">
                @error('mileage_km')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="fuel_type">Nhiên liệu</label>
                <input id="fuel_type" name="fuel_type" value="{{ old('fuel_type', $vehicle->fuel_type) }}" required>
                @error('fuel_type')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="transmission">Hộp số</label>
                <input id="transmission" name="transmission" value="{{ old('transmission', $vehicle->transmission) }}" required>
                @error('transmission')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field">
                <label for="color">Màu</label>
                <input id="color" name="color" value="{{ old('color', $vehicle->color) }}">
                @error('color')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field full">
                <label for="image_url">URL ảnh</label>
                <input id="image_url" name="image_url" type="text" value="{{ old('image_url', $vehicle->image_url) }}" placeholder="https://...">
                @error('image_url')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field full">
                <label for="description">Mô tả</label>
                <textarea id="description" name="description">{{ old('description', $vehicle->description) }}</textarea>
                @error('description')<div class="err">{{ $message }}</div>@enderror
            </div>
            <div class="field full">
                <div class="check-row">
                    <input type="checkbox" id="is_featured" name="is_featured" value="1" @checked(old('is_featured', $vehicle->is_featured))>
                    <label for="is_featured" style="margin:0;">Xe nổi bật (hiện trên trang chủ)</label>
                </div>
                @error('is_featured')<div class="err">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Lưu thay đổi</button>
            <a href="{{ route('vehicles.show', $vehicle) }}" class="btn btn-ghost">Huỷ</a>
        </div>
    </form>
</div>
@endsection
