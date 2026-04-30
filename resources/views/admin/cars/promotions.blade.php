@extends('layouts.admin')

@section('title', 'Khuyến mãi')

@section('content')
<style>
    .page-title { font-size: 1.5rem; font-weight: 700; margin: 0 0 1rem; }
    .panel { background: var(--surface); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; max-width: 800px; }
    textarea { width: 100%; min-height: 220px; padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border); background: #0a0d12; color: var(--text); font-family: inherit; line-height: 1.5; }
    .btn-save { margin-top: 1rem; padding: 0.6rem 1.2rem; border-radius: 8px; border: none; background: var(--accent); color: #0c0f14; font-weight: 700; cursor: pointer; }
    .hint { color: var(--muted); font-size: 0.9rem; margin-bottom: 1rem; }
    .flash-alert { background: #d1fae5; color: #065f46; padding: 0.85rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-weight: 600; }
    .preview-link { display: inline-block; margin-top: 0.75rem; color: var(--accent); font-weight: 600; }
</style>

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
            <label for="content" style="font-weight: 600; display: block; margin-bottom: 0.5rem;">Nội dung</label>
            <textarea name="content" id="content" placeholder="VD: Giảm 2% khi đặt cọc trong tuần này...">{{ old('content', $content) }}</textarea>
            <button type="submit" class="btn-save">Lưu</button>
        </form>
    </div>

    <a href="{{ route('promotions.index') }}" class="preview-link" target="_blank">Xem trước trang khách hàng →</a>
</div>
@endsection
