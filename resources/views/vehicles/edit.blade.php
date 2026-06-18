@extends('layouts.site')

@section('title', 'Sửa: '.$vehicle->title)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/vehicles-edit.css')
    @endif
@endpush


@section('content')

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
                    <label class="vehicles-edit-inline-1" for="is_featured">Xe nổi bật (hiện trên trang chủ)</label>
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