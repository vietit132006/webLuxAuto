@extends('layouts.admin')

@section('title', 'Báo cáo khuyến mãi')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-promotions.css')
    @endif
@endpush

@section('content')
@php
    $money = fn ($value) => number_format((float) $value, 0, ',', '.') . ' đ';
@endphp

<div class="admin-promotions-page">
    <div class="admin-promotions-head">
        <div>
            <h1>Báo cáo khuyến mãi</h1>
            <p>Báo cáo / Marketing / Khuyến mãi</p>
        </div>

        <div class="promotion-head-actions">
            <a class="promotion-secondary" href="{{ route('admin.promotions') }}">Danh sách khuyến mãi</a>
        </div>
    </div>

    <section class="promotion-stats-grid is-report">
        <div class="promotion-stat">
            <span>Tổng khuyến mãi</span>
            <strong>{{ number_format($stats['total']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Đang hoạt động</span>
            <strong>{{ number_format($stats['active']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Sắp diễn ra</span>
            <strong>{{ number_format($stats['scheduled']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Đã hết hạn</span>
            <strong>{{ number_format($stats['expired']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Đơn có khuyến mãi</span>
            <strong>{{ number_format($stats['orders_with_promotions']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Doanh thu từ đơn có KM</span>
            <strong>{{ $money($stats['revenue_with_promotions']) }}</strong>
        </div>
        <div class="promotion-stat">
            <span>Tổng tiền giảm</span>
            <strong>{{ $money($stats['total_discount']) }}</strong>
        </div>
    </section>

    <div class="promotion-report-grid">
        <section class="promotion-report-panel">
            <div class="promotion-section-title">
                <h2>Khuyến mãi được dùng nhiều nhất</h2>
                <span>Top {{ $topPromotions->count() }}</span>
            </div>

            <div class="promotion-report-list">
                @forelse($topPromotions as $promotion)
                    <div class="promotion-report-item">
                        <div>
                            <strong>{{ $promotion->promotion_code }} - {{ $promotion->title }}</strong>
                            <span>{{ $promotion->typeLabel() }} · {{ $promotion->discountLabel() }}</span>
                        </div>
                        <div class="promotion-report-numbers">
                            <strong>{{ number_format($promotion->usage_count) }}</strong>
                            <span>{{ number_format($promotion->quote_promotions_count + $promotion->order_promotions_count) }} liên kết</span>
                        </div>
                    </div>
                @empty
                    <div class="promotion-empty is-panel">Chưa có dữ liệu sử dụng khuyến mãi.</div>
                @endforelse
            </div>
        </section>

        <section class="promotion-report-panel">
            <div class="promotion-section-title">
                <h2>Báo giá gần đây có khuyến mãi</h2>
                <span>{{ $recentQuotePromotions->count() }} bản ghi</span>
            </div>

            <div class="promotion-report-list">
                @forelse($recentQuotePromotions as $quotePromotion)
                    <div class="promotion-report-item">
                        <div>
                            <strong>{{ $quotePromotion->quote?->quote_code ?? 'Báo giá đã xóa' }}</strong>
                            <span>
                                {{ $quotePromotion->promotion?->promotion_code ?? 'KM đã xóa' }}
                                · {{ $quotePromotion->quote?->customer?->full_name ?? 'Khách hàng' }}
                            </span>
                        </div>
                        <div class="promotion-report-numbers">
                            <strong>{{ $money($quotePromotion->discount_amount) }}</strong>
                            <span>{{ $quotePromotion->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="promotion-empty is-panel">Chưa có báo giá áp dụng khuyến mãi.</div>
                @endforelse
            </div>
        </section>

        <section class="promotion-report-panel">
            <div class="promotion-section-title">
                <h2>Đơn hàng gần đây có khuyến mãi</h2>
                <span>{{ $recentOrderPromotions->count() }} bản ghi</span>
            </div>

            <div class="promotion-report-list">
                @forelse($recentOrderPromotions as $orderPromotion)
                    <div class="promotion-report-item">
                        <div>
                            <strong>{{ $orderPromotion->order?->display_code ?? 'Đơn đã xóa' }}</strong>
                            <span>
                                {{ $orderPromotion->promotion?->promotion_code ?? 'KM đã xóa' }}
                                · {{ $orderPromotion->order?->user?->name ?? 'Khách hàng' }}
                            </span>
                        </div>
                        <div class="promotion-report-numbers">
                            <strong>{{ $money($orderPromotion->discount_amount) }}</strong>
                            <span>{{ $orderPromotion->created_at?->format('d/m/Y H:i') }}</span>
                        </div>
                    </div>
                @empty
                    <div class="promotion-empty is-panel">Chưa có đơn hàng áp dụng khuyến mãi.</div>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
