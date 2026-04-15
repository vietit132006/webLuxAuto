@extends('layouts.admin')

@section('title', 'Thêm xe')

@section('content')
<style>
.form-wrap { max-width: 700px; margin: 0 auto; background: var(--surface); padding: 2rem; border-radius: 12px; border: 1px solid var(--border); }
.form-group { margin-bottom: 1.25rem; }
.form-group label { display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text); }
.form-group input:not([type="file"]), .form-group textarea, .form-group select { width: 100%; padding: 0.6rem 0.9rem; border-radius: 8px; border: 1px solid var(--border); background: var(--surface); color: var(--text); }
.form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: var(--accent-dim); box-shadow: 0 0 0 3px rgba(201, 169, 98, 0.15); }
.btn-submit { padding: 0.6rem 1.2rem; border-radius: 8px; border: none; background: var(--accent); color: #0c0f14; font-weight: 600; cursor: pointer; }
.btn-back { padding: 0.6rem 1.2rem; border-radius: 8px; border: 1px solid var(--border); text-decoration: none; color: var(--text); margin-left: 10px; }
.error { color: red; font-size: 0.85rem; margin-top: 0.3rem;}
input[type="file"] { padding: 0.4rem; background: #fff; width: 100%; border-radius: 8px;}
</style>

<div class="wrap form-wrap">
    <h1 class="page-title" style="margin-bottom: 1.5rem;">Thêm xe mới</h1>

    <form method="POST" action="{{ route('admin.cars.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="form-group">
            <label>Hãng xe</label>
            <select name="brand_id" required>
                <option value="">-- Chọn hãng xe --</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->brand_id }}" {{ old('brand_id') == $brand->brand_id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
            @error('brand_id') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group">
            <label>Tên xe / Dòng xe</label>
            <input name="name" value="{{ old('name') }}" required>
        </div>

        <div class="form-group">
            <label>Năm sản xuất</label>
            <input type="number" name="year" value="{{ old('year') }}" required>
        </div>

        <div class="form-group">
            <label>Giá bán (VNĐ)</label>
            <input type="number" name="price" value="{{ old('price') }}" required>
        </div>

        <div class="form-group">
            <label>Màu sắc</label>
            <input name="color" value="{{ old('color') }}">
        </div>

        <div class="form-group">
            <label>Số lượng trong kho (Stock)</label>
            <input type="number" name="stock" value="{{ old('stock', 1) }}" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px;">
            <div class="form-group">
                <label>Số km đã đi</label>
                <input type="number" name="mileage_km" value="{{ old('mileage_km') }}">
            </div>
            <div class="form-group">
                <label>Nhiên liệu</label>
                <input name="fuel_type" value="{{ old('fuel_type') }}" placeholder="VD: Xăng">
            </div>
            <div class="form-group">
                <label>Hộp số</label>
                <input name="transmission" value="{{ old('transmission') }}" placeholder="VD: Tự động">
            </div>
        </div>

        <div class="form-group">
            <label>Mô tả chi tiết</label>
            <textarea name="description" rows="4">{{ old('description') }}</textarea>
        </div>

        <div class="form-group">
            <label>Hình ảnh xe</label>
            <input type="file" name="image" accept="image/*">
            @error('image') <div class="error">{{ $message }}</div> @enderror
        </div>

        <div class="form-group" style="background: rgba(201, 169, 98, 0.1); padding: 10px; border-radius: 8px;">
            <label style="margin: 0; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }} style="width: auto;">
                Đánh dấu là "Xe Nổi Bật" (Sẽ hiển thị ra trang chủ)
            </label>
        </div>

        <div class="form-actions" style="margin-top: 2rem;">
            <button type="submit" class="btn-submit">Lưu thông tin</button>
            <a href="{{ route('admin.cars.index') }}" class="btn-back">Hủy & Quay lại</a>
        </div>
    </form>
</div>
@endsection
