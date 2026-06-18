@extends('layouts.site')

@section('title', 'Khuyến mãi')

@push('styles')
<style>
    .promo-page {
        margin-top: -2rem;
    }

    .promo-wrap {
        width: min(1180px, calc(100% - 2rem));
        margin: 0 auto;
    }

    .promo-hero {
        min-height: clamp(340px, 46vh, 520px);
        display: flex;
        align-items: flex-end;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
        background:
            linear-gradient(90deg, rgba(12, 15, 20, 0.96) 0%, rgba(12, 15, 20, 0.78) 45%, rgba(12, 15, 20, 0.24) 100%),
            url("https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=1800&q=85") center / cover no-repeat;
    }

    .promo-hero__content {
        width: min(1180px, calc(100% - 2rem));
        margin: 0 auto;
        padding: 3rem 0 2.4rem;
    }

    .promo-kicker {
        display: inline-flex;
        align-items: center;
        gap: 0.55rem;
        margin-bottom: 0.85rem;
        color: var(--accent);
        font-size: 0.76rem;
        font-weight: 850;
        letter-spacing: 0.12em;
        text-transform: uppercase;
    }

    .promo-kicker::before {
        content: "";
        width: 34px;
        height: 1px;
        background: currentColor;
    }

    .promo-title {
        max-width: 760px;
        margin: 0;
        color: #fff;
        font-size: clamp(2rem, 4.5vw, 4rem);
        font-weight: 950;
        line-height: 1;
    }

    .promo-copy {
        max-width: 640px;
        margin: 1rem 0 0;
        color: #c4ccda;
        line-height: 1.75;
    }

    .promo-main {
        padding-top: 1.25rem;
    }

    .promo-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 320px;
        gap: 1rem;
        align-items: start;
    }

    .promo-panel,
    .promo-side {
        border: 1px solid var(--border);
        border-radius: 12px;
        background: linear-gradient(180deg, #151b24, #0d1118);
    }

    .promo-panel {
        min-width: 0;
        padding: 1.25rem;
    }

    .promo-panel__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    }

    .promo-panel__head h2 {
        margin: 0;
        color: var(--text);
        font-size: clamp(1.25rem, 2vw, 1.65rem);
        font-weight: 950;
    }

    .promo-date {
        color: var(--muted);
        font-size: 0.88rem;
        white-space: nowrap;
    }

    .promo-box {
        color: #dbe3ef;
        line-height: 1.78;
        white-space: pre-wrap;
        overflow-wrap: anywhere;
    }

    .promo-empty {
        display: grid;
        min-height: 260px;
        place-items: center;
        padding: 2rem;
        border: 1px dashed var(--border);
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.025);
        color: var(--muted);
        text-align: center;
    }

    .promo-empty svg {
        width: 48px;
        height: 48px;
        margin-bottom: 0.85rem;
        color: var(--accent);
    }

    .promo-empty strong {
        display: block;
        margin-bottom: 0.4rem;
        color: var(--text);
        font-size: 1.12rem;
    }

    .promo-side {
        position: sticky;
        top: 92px;
        padding: 1rem;
    }

    .promo-side h2 {
        margin: 0 0 0.55rem;
        color: var(--text);
        font-size: 1.08rem;
        font-weight: 950;
    }

    .promo-side p {
        margin: 0;
        color: var(--muted);
        line-height: 1.65;
        font-size: 0.92rem;
    }

    .promo-actions {
        display: grid;
        gap: 0.65rem;
        margin-top: 1rem;
    }

    .promo-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        min-height: 44px;
        padding: 0.7rem 1rem;
        border-radius: 8px;
        border: 1px solid transparent;
        font-size: 0.92rem;
        font-weight: 850;
        text-decoration: none;
        text-align: center;
        transition: color 0.2s ease, background 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .promo-btn svg {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    .promo-btn--primary {
        background: linear-gradient(135deg, var(--accent), #ead990);
        color: #0c0f14;
        box-shadow: 0 16px 34px -24px rgba(201, 169, 98, 0.95);
    }

    .promo-btn--primary:hover {
        color: #0c0f14;
        box-shadow: 0 18px 40px -24px rgba(201, 169, 98, 1);
    }

    .promo-btn--ghost {
        border-color: rgba(255, 255, 255, 0.1);
        background: rgba(255, 255, 255, 0.04);
        color: var(--text);
    }

    .promo-btn--ghost:hover {
        border-color: rgba(201, 169, 98, 0.45);
        background: rgba(201, 169, 98, 0.1);
        color: var(--accent);
    }

    .promo-note {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid rgba(255, 255, 255, 0.07);
        color: var(--muted);
        font-size: 0.84rem;
        line-height: 1.55;
    }

    .promo-btn:focus-visible {
        outline: 2px solid rgba(201, 169, 98, 0.9);
        outline-offset: 3px;
    }

    @media (prefers-reduced-motion: reduce) {
        .promo-btn {
            transition: none;
        }
    }

    @media (max-width: 900px) {
        .promo-layout {
            grid-template-columns: 1fr;
        }

        .promo-side {
            position: static;
        }
    }

    @media (max-width: 640px) {
        .promo-wrap,
        .promo-hero__content {
            width: min(100% - 1.5rem, 1180px);
        }

        .promo-hero {
            min-height: 420px;
            background:
                linear-gradient(180deg, rgba(12, 15, 20, 0.88) 0%, rgba(12, 15, 20, 0.74) 48%, rgba(12, 15, 20, 0.96) 100%),
                url("https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=1200&q=85") center / cover no-repeat;
        }

        .promo-panel__head {
            display: grid;
            align-items: start;
        }

        .promo-date {
            white-space: normal;
        }
    }
</style>
@endpush

@section('content')
@php
    $hasPromotion = trim((string) $content) !== '';
@endphp

<div class="promo-page">
    <section class="promo-hero">
        <div class="promo-hero__content">
            <div class="promo-kicker">Ưu đãi Lux Auto</div>
            <h1 class="promo-title">Khuyến mãi & ưu đãi</h1>
            <p class="promo-copy">
                Cập nhật các chương trình hỗ trợ đặt cọc, tư vấn và ưu đãi theo từng thời điểm tại showroom.
            </p>
        </div>
    </section>

    <main class="promo-wrap promo-main">
        <div class="promo-layout">
            <section class="promo-panel">
                <div class="promo-panel__head">
                    <h2>Chương trình hiện tại</h2>
                    <div class="promo-date">Cập nhật {{ now()->format('d/m/Y') }}</div>
                </div>

                @if($hasPromotion)
                    <div class="promo-box">{{ $content }}</div>
                @else
                    <div class="promo-empty">
                        <div>
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3.75h4.864c.89 0 1.705.497 2.112 1.289l.706 1.373 1.526.22a2.375 2.375 0 0 1 1.317 4.05l-1.103 1.075.261 1.52a2.375 2.375 0 0 1-3.447 2.504l-1.365-.718-1.365.718a2.375 2.375 0 0 1-3.447-2.504l.261-1.52-1.103-1.075a2.375 2.375 0 0 1 1.317-4.05l1.526-.22.706-1.373A2.375 2.375 0 0 1 9.568 3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 20.25h4.5" />
                            </svg>
                            <strong>Chưa có chương trình khuyến mãi</strong>
                            <div>Quay lại sau hoặc liên hệ tư vấn để nhận thông tin mới nhất.</div>
                        </div>
                    </div>
                @endif
            </section>

            <aside class="promo-side">
                <h2>Tìm xe phù hợp</h2>
                <p>Chọn xe đang bán, so sánh thông số và chuyển sang chi tiết khi bạn muốn đặt cọc hoặc đặt lịch lái thử.</p>

                <div class="promo-actions">
                    <a href="{{ route('cars.index') }}" class="promo-btn promo-btn--primary">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5 6 8.25A2.25 2.25 0 0 1 8.068 6.9h7.864A2.25 2.25 0 0 1 18 8.25l2.25 5.25M5.25 13.5h13.5m-12 0v3.75m10.5-3.75v3.75" />
                        </svg>
                        Xem danh sách xe
                    </a>
                    <a href="{{ route('compare.index') }}" class="promo-btn promo-btn--ghost">
                        <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 3.75v16.5m9-16.5v16.5M3.75 8.25h16.5M3.75 15.75h16.5" />
                        </svg>
                        So sánh xe
                    </a>
                </div>

                <div class="promo-note">
                    Ưu đãi có thể thay đổi theo tình trạng xe, thời điểm đặt cọc và xác nhận trực tiếp từ showroom.
                </div>
            </aside>
        </div>
    </main>
</div>
@endsection
