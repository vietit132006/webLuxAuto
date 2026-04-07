@extends('layouts.site')

@section('title', 'Thêm xe')

@section('content')
<style>
/* ... (Giữ nguyên phần CSS của bạn) ... */
.form-wrap {
    max-width: 700px;
    margin: 0 auto;
}
.form-group {
    margin-bottom: 1rem;
}
.form-group input,
.form-group textarea,
.form-group select {
    width: 100%;
    padding: 0.6rem 0.9rem;
    border-radius: 8px;
    border: 1px solid var(--border);
    background: var(--surface);
    color: var(--text);
}
.form-group input:focus,
.form-group textarea:focus,
.form-group select:focus {
    outline: none;
    border-color: var(--accent-dim);
    box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15);
}
.form-actions {
    display: flex;
    gap: 10px;
}
.btn-submit {
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    border: none;
    background: var(--accent);
    color: #0c0f14;
    font-weight: 600;
    cursor: pointer;
}
.btn-back {
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    border: 1px solid var(--border);
    text-decoration: none;
    color: var(--text);
}
.error {
    color: red;
    font-size: 0.85rem;
}
/* CSS bổ sung cho input file nhìn đẹp hơn một chút */
input[type="file"] {
    padding: 0.4rem;
    background: #fff;
}
</style>

<div class="wrap form-wrap">
    <h1 class="page-title">Thêm xe</h1>

    <form method="POST" action="{{ route('cars.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <select name="brand_id" class="form-control" required>
    <option value="">-- Chọn hãng xe --</option>
    @foreach($brands as $brand)
        <option value="{{ $brand->brand_id }}">{{ $brand->name }}</option>
    @endforeach
</select>
            @error('brand_id') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <input name="name" placeholder="Dòng xe" value="{{ old('name') }}">
        </div>

        <div class="form-group">
            <input name="year" placeholder="Năm sản xuất" value="{{ old('year') }}">
        </div>

        <div class="form-group">
            <input name="price" placeholder="Giá" value="{{ old('price') }}">
        </div>

        <div class="form-group">
            <input name="mileage_km" placeholder="Số km đã đi" value="{{ old('mileage_km') }}">
        </div>

        <div class="form-group">
            <input name="fuel_type" placeholder="Loại nhiên liệu" value="{{ old('fuel_type') }}">
        </div>

        <div class="form-group">
            <input name="transmission" placeholder="Hộp số" value="{{ old('transmission') }}">
        </div>

        <div class="form-group">
            <input name="color" placeholder="Màu xe" value="{{ old('color') }}">
        </div>

        <div class="form-group">
            <textarea name="description" placeholder="Mô tả">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <input type="file" name="image" accept="image/*">
            @error('image') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}> Xe nổi bật
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Thêm</button>
            <a href="{{ route('cars.index') }}" class="btn-back">Quay lại</a>
        </div>
    </form>
</div>
@endsection
