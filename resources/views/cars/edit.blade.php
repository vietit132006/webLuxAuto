@extends('layouts.site')

@section('title', 'Sửa xe')

@section('content')
<div class="wrap form-wrap">
    <h1 class="page-title">Sửa xe</h1>

    <form method="POST" action="{{ route('cars.update', $car->car_id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label>Hãng xe</label>
            <select name="brand_id" required style="width: 100%; padding: 0.5rem; border-radius: 6px; border: 1px solid #ccc;">
                <option value="">-- Chọn hãng xe --</option>

                @foreach($brands as $brand)
                    <option value="{{ $brand->brand_id }}"
                        {{ old('brand_id', $car->brand_id) == $brand->brand_id ? 'selected' : '' }}>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Tên xe</label>
            <input name="name" value="{{ old('name', $car->name) }}" required>
        </div>

        <div class="form-group">
            <label>Năm sản xuất</label>
            <input type="number" name="year" value="{{ old('year', $car->year) }}" required>
        </div>

        <div class="form-group">
            <label>Giá (VNĐ)</label>
            <input type="number" name="price" value="{{ old('price', $car->price) }}" required>
        </div>

        <div class="form-group">
            <label>Màu sắc</label>
            <input name="color" value="{{ old('color', $car->color) }}">
        </div>

        <div class="form-group">
            <label>Số lượng trong kho (Stock)</label>
            <input type="number" name="stock" value="{{ old('stock', $car->stock) }}">
        </div>

        <div class="form-group">
            <label>Mô tả chi tiết</label>
            <textarea name="description" rows="4" style="width: 100%;">{{ old('description', $car->description) }}</textarea>
        </div>

        <div class="form-group">
            <label>Tên file ảnh hoặc Link ảnh</label>
            <input name="image" value="{{ old('image', $car->image) }}">
        </div>

        <div class="form-actions" style="margin-top: 1.5rem;">
            <button type="submit" class="btn-submit">Cập nhật</button>
            <a href="{{ route('cars.index') }}" class="btn-back" style="margin-left: 10px;">Quay lại</a>
        </div>
    </form>
</div>
@endsection
