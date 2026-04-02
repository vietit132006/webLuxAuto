@extends('layouts.site')

@section('title', 'Sửa xe')

@section('content')
<div class="wrap form-wrap">
    <h1 class="page-title">Sửa xe</h1>

    <form method="POST" action="{{ route('vehicles.update', $vehicle->id) }}">
        @csrf

        <div class="form-group">
            <input name="brand" value="{{ old('brand', $vehicle->brand) }}">
        </div>

        <div class="form-group">
            <input name="model" value="{{ old('model', $vehicle->model) }}">
        </div>

        <div class="form-group">
            <input name="year" value="{{ old('year', $vehicle->year) }}">
        </div>

        <div class="form-group">
            <input name="price" value="{{ old('price', $vehicle->price) }}">
        </div>

        <div class="form-group">
            <input name="mileage_km" value="{{ old('mileage_km', $vehicle->mileage_km) }}">
        </div>

        <div class="form-group">
            <input name="fuel_type" value="{{ old('fuel_type', $vehicle->fuel_type) }}">
        </div>

        <div class="form-group">
            <input name="transmission" value="{{ old('transmission', $vehicle->transmission) }}">
        </div>

        <div class="form-group">
            <input name="color" value="{{ old('color', $vehicle->color) }}">
        </div>

        <div class="form-group">
            <textarea name="description">{{ old('description', $vehicle->description) }}</textarea>
        </div>

        <div class="form-group">
            <input name="image_url" value="{{ old('image_url', $vehicle->image_url) }}">
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="is_featured" value="1"
                    {{ $vehicle->is_featured ? 'checked' : '' }}>
                Xe nổi bật
            </label>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Cập nhật</button>
            <a href="{{ route('vehicles.index') }}" class="btn-back">Quay lại</a>
        </div>
    </form>
</div>
@endsection