@extends('layouts.site')

@section('title', 'Khuyến mãi')

@section('content')
<style>
    .promo-hero { margin-bottom: 2rem; }
    .promo-hero h1 { font-size: clamp(1.5rem, 3vw, 2rem); margin: 0 0 0.5rem; }
    .promo-hero p { color: var(--muted); margin: 0; }
    .promo-box {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 2rem;
        line-height: 1.75;
        white-space: pre-wrap;
    }
    .promo-empty { color: var(--muted); text-align: center; padding: 3rem; border: 1px dashed var(--border); border-radius: 12px; }
</style>

<div class="wrap">
    <div class="promo-hero">
        <h1>Khuyến mãi &amp; ưu đãi</h1>
        <p>Cập nhật theo từng thời điểm — liên hệ hotline để được tư vấn chi tiết.</p>
    </div>

    @if(trim((string) $content) === '')
        <div class="promo-empty">Hiện chưa có chương trình khuyến mãi. Quay lại sau hoặc gọi tư vấn.</div>
    @else
        <div class="promo-box">{{ $content }}</div>
    @endif

    <div style="margin-top: 2rem; display: flex; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route('cars.index') }}" style="display: inline-flex; align-items: center; padding: 0.65rem 1.1rem; border-radius: 8px; background: var(--accent); color: #0c0f14; font-weight: 700; text-decoration: none;">Xem danh sách xe</a>
        <a href="{{ route('compare.index') }}" style="display: inline-flex; align-items: center; padding: 0.65rem 1.1rem; border-radius: 8px; border: 1px solid var(--border); color: var(--text); font-weight: 600; text-decoration: none;">So sánh xe</a>
    </div>
</div>
@endsection
