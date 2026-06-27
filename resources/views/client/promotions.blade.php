@extends('layouts.site')

@section('title', 'Khuyến mãi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-promotions.css')
    @endif
@endpush


@push('styles')
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