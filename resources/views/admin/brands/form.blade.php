@extends('layouts.admin')
@section('title', isset($brand) ? 'Sửa hãng xe' : 'Thêm hãng xe')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-brands-form.css')
    @endif
@endpush


@section('content')
<div class="wrap admin-brands-form-inline-8">
    <h1 class="admin-brands-form-inline-7">{{ isset($brand) ? 'Sửa Hãng Xe: ' . $brand->name : 'Thêm Hãng Xe Mới' }}</h1>

    <form method="POST" action="{{ isset($brand) ? route('admin.brands.update', $brand->brand_id) : route('admin.brands.store') }}">
        @csrf
        @if(isset($brand)) @method('PUT') @endif

        <div class="admin-brands-form-inline-6">
            <label class="admin-brands-form-inline-4">Tên hãng xe (*)</label>
            <input class="admin-brands-form-inline-3" type="text" name="name" value="{{ old('name', $brand->name ?? '') }}" required>
        </div>

        <div class="admin-brands-form-inline-5">
            <label class="admin-brands-form-inline-4">Quốc gia (Tùy chọn)</label>
            <input class="admin-brands-form-inline-3" type="text" name="country" value="{{ old('country', $brand->country ?? '') }}" placeholder="Ví dụ: Đức, Nhật Bản...">
        </div>

        <div>
            <button class="admin-brands-form-inline-2" type="submit">Lưu Thông Tin</button>
            <a class="admin-brands-form-inline-1" href="{{ route('admin.brands.index') }}">Hủy</a>
        </div>
    </form>
</div>
@endsection