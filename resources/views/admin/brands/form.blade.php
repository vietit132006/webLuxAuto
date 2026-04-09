@extends('layouts.admin')
@section('title', isset($brand) ? 'Sửa hãng xe' : 'Thêm hãng xe')

@section('content')
<div class="wrap" style="max-width: 500px; background: var(--surface); padding: 2rem; border-radius: 12px; border: 1px solid var(--border);">
    <h1 style="margin-top: 0; margin-bottom: 1.5rem;">{{ isset($brand) ? 'Sửa Hãng Xe: ' . $brand->name : 'Thêm Hãng Xe Mới' }}</h1>

    <form method="POST" action="{{ isset($brand) ? route('admin.brands.update', $brand->brand_id) : route('admin.brands.store') }}">
        @csrf
        @if(isset($brand)) @method('PUT') @endif

        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text);">Tên hãng xe (*)</label>
            <input type="text" name="name" value="{{ old('name', $brand->name ?? '') }}" required style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
        </div>

        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem; color: var(--text);">Quốc gia (Tùy chọn)</label>
            <input type="text" name="country" value="{{ old('country', $brand->country ?? '') }}" placeholder="Ví dụ: Đức, Nhật Bản..." style="width: 100%; padding: 0.8rem; border-radius: 6px; background: #0c0f14; border: 1px solid var(--border); color: #fff;">
        </div>

        <div>
            <button type="submit" style="background: var(--accent); color: #000; padding: 0.8rem 1.5rem; border: none; border-radius: 6px; font-weight: bold; cursor: pointer;">Lưu Thông Tin</button>
            <a href="{{ route('admin.brands.index') }}" style="color: var(--muted); margin-left: 15px;">Hủy</a>
        </div>
    </form>
</div>
@endsection
