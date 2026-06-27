@extends('layouts.admin')

@section('title', 'Khuyến mãi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-cars-promotions.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    <h1 class="page-title">Nội dung khuyến mãi</h1>
    <p class="hint">Nội dung hiển thị cho khách tại trang « Khuyến mãi ». Hỗ trợ xuống dòng thông thường.</p>

    @if(session('success'))
        <div class="flash-alert">✅ {{ session('success') }}</div>
    @endif

    <div class="panel">
        <form method="post" action="{{ route('admin.promotions.update') }}">
            @csrf
            @method('PUT')
            <label class="admin-cars-promotions-inline-1" for="content">Nội dung</label>
            <textarea name="content" id="content" placeholder="VD: Giảm 2% khi đặt cọc trong tuần này...">{{ old('content', $content) }}</textarea>
            <button type="submit" class="btn-save">Lưu</button>
        </form>
    </div>

    <a href="{{ route('promotions.index') }}" class="preview-link" target="_blank">Xem trước trang khách hàng →</a>
</div>
@endsection