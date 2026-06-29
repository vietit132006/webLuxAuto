@extends('layouts.site')

@section('title', 'Báo giá ' . $quote->quote_code)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/client-quotes.css')
    @endif
@endpush

@section('content')
@php
    $isExpired = $quote->isDateExpired() || $quote->status === \App\Models\Quote::STATUS_EXPIRED;
@endphp

<div class="client-quote-page">
    <section class="client-quote-hero">
        <div>
            <span class="client-quote-kicker">Lux Auto quotation</span>
            <h1>Báo giá {{ $quote->quote_code }}</h1>
            <p>{{ $quote->car?->title ?? 'Xe đã chọn' }}</p>
        </div>

        <div class="client-quote-total">
            <span>Tổng báo giá</span>
            <strong>{{ $quote->money('total_price') }}</strong>
        </div>
    </section>

    @if(session('success'))
        <div class="client-quote-alert is-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="client-quote-alert is-error">{{ session('error') }}</div>
    @endif

    @if(isset($errors) && $errors->any())
        <div class="client-quote-alert is-error">{{ $errors->first() }}</div>
    @endif

    <section class="client-quote-status-panel">
        <div>
            <span class="client-quote-status {{ $quote->statusClass() }}">{{ $quote->statusLabel() }}</span>
            <p>
                Ngày lập: {{ $quote->created_at?->format('d/m/Y H:i') }}
                @if($quote->expired_at)
                    <span>Hiệu lực đến: {{ $quote->expired_at->format('d/m/Y') }}</span>
                @endif
            </p>
        </div>

        <a class="client-quote-secondary" href="{{ $quote->publicPdfUrl() }}">Tải PDF</a>
    </section>

    <div class="client-quote-grid">
        <section class="client-quote-panel">
            <h2>Thông tin khách hàng</h2>
            <dl class="client-quote-info">
                <div>
                    <dt>Họ tên</dt>
                    <dd>{{ $quote->customer?->full_name ?? 'Khách hàng' }}</dd>
                </div>
                <div>
                    <dt>Số điện thoại</dt>
                    <dd>{{ $quote->customer?->phone ?? '---' }}</dd>
                </div>
                <div>
                    <dt>Email</dt>
                    <dd>{{ $quote->customer?->email ?? '---' }}</dd>
                </div>
                <div>
                    <dt>Nhân viên tư vấn</dt>
                    <dd>{{ $quote->user->name ?? 'Lux Auto' }}</dd>
                </div>
            </dl>
        </section>

        <section class="client-quote-panel">
            <h2>Thông tin xe</h2>
            <dl class="client-quote-info">
                <div>
                    <dt>Mẫu xe</dt>
                    <dd>{{ $quote->car?->title ?? 'Xe đã chọn' }}</dd>
                </div>
                <div>
                    <dt>VIN</dt>
                    <dd>{{ $quote->car?->vin ?: '---' }}</dd>
                </div>
                <div>
                    <dt>Biển số</dt>
                    <dd>{{ $quote->car?->license_plate ?: '---' }}</dd>
                </div>
                <div>
                    <dt>Màu sắc</dt>
                    <dd>{{ $quote->car?->color ?: '---' }}</dd>
                </div>
            </dl>
        </section>
    </div>

    <section class="client-quote-panel">
        <div class="client-quote-section-title">
            <h2>Chi tiết chi phí</h2>
            <span>{{ $quote->money('total_price') }}</span>
        </div>

        <div class="client-quote-price-lines">
            <div>
                <span>Giá xe</span>
                <strong>{{ $quote->money('vehicle_price') }}</strong>
            </div>
            <div>
                <span>Giảm giá</span>
                <strong>-{{ $quote->money('discount_amount') }}</strong>
            </div>
            <div>
                <span>Phí đăng ký</span>
                <strong>{{ $quote->money('registration_fee') }}</strong>
            </div>
            <div>
                <span>Phí biển số</span>
                <strong>{{ $quote->money('plate_fee') }}</strong>
            </div>
            <div>
                <span>Phí bảo hiểm</span>
                <strong>{{ $quote->money('insurance_fee') }}</strong>
            </div>
            <div>
                <span>Phí khác</span>
                <strong>{{ $quote->money('other_fee') }}</strong>
            </div>
            <div class="is-total">
                <span>Tổng thanh toán dự kiến</span>
                <strong>{{ $quote->money('total_price') }}</strong>
            </div>
        </div>
    </section>

    @if($quote->quotePromotions->isNotEmpty())
        <section class="client-quote-panel">
            <div class="client-quote-section-title">
                <h2>Ưu đãi đã áp dụng</h2>
                <span>{{ number_format($quote->promotionDiscountTotal(), 0, ',', '.') }} đ</span>
            </div>

            <div class="client-quote-promotions">
                @foreach($quote->quotePromotions as $quotePromotion)
                    <div>
                        <strong>{{ $quotePromotion->promotion?->promotion_code ?? 'KM đã xóa' }} - {{ $quotePromotion->promotion?->title ?? 'Khuyến mãi đã xóa' }}</strong>
                        <span>Giảm {{ number_format((float) $quotePromotion->discount_amount, 0, ',', '.') }} đ</span>
                        @if($quotePromotion->gift_note)
                            <small>{{ $quotePromotion->gift_note }}</small>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    @if($quote->note)
        <section class="client-quote-panel">
            <h2>Ghi chú từ Lux Auto</h2>
            <p class="client-quote-note">{{ $quote->note }}</p>
        </section>
    @endif

    <section class="client-quote-panel">
        <div class="client-quote-section-title">
            <h2>Phản hồi báo giá</h2>
            @if($quote->customer_responded_at)
                <span>{{ $quote->customer_responded_at->format('d/m/Y H:i') }}</span>
            @endif
        </div>

        @if($quote->status === \App\Models\Quote::STATUS_ACCEPTED)
            <div class="client-quote-result is-accepted">
                <strong>Quý khách đã chấp nhận báo giá.</strong>
                @if($quote->customer_response_note)
                    <p>{{ $quote->customer_response_note }}</p>
                @endif
            </div>
        @elseif($quote->status === \App\Models\Quote::STATUS_REJECTED)
            <div class="client-quote-result is-rejected">
                <strong>Quý khách đã từ chối báo giá.</strong>
                @if($quote->customer_response_note)
                    <p>{{ $quote->customer_response_note }}</p>
                @endif
            </div>
        @elseif($isExpired)
            <div class="client-quote-result is-expired">
                <strong>Báo giá đã hết hạn.</strong>
                <p>Vui lòng liên hệ Lux Auto để được cập nhật báo giá mới nhất.</p>
            </div>
        @else
            <form class="client-quote-response-form" method="post" action="{{ route('quotes.public.respond', ['quote' => $quote->quote_code, 'token' => $quote->public_token]) }}">
                @csrf
                <label for="customer_response_note">Ghi chú cho tư vấn viên</label>
                <textarea id="customer_response_note" name="customer_response_note" rows="4" placeholder="Nhập yêu cầu thêm, thời gian liên hệ lại hoặc lý do từ chối nếu có">{{ old('customer_response_note') }}</textarea>

                <div class="client-quote-response-actions">
                    <button class="client-quote-primary" type="submit" name="response" value="{{ \App\Models\Quote::STATUS_ACCEPTED }}">
                        Chấp nhận báo giá
                    </button>
                    <button class="client-quote-danger" type="submit" name="response" value="{{ \App\Models\Quote::STATUS_REJECTED }}">
                        Từ chối báo giá
                    </button>
                </div>
            </form>
        @endif
    </section>
</div>
@endsection
