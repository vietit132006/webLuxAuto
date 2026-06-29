@extends('layouts.admin')

@section('title', $quote->quote_code)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-quotes.css')
    @endif
@endpush

@section('content')
@php
    $quoteAvailableStock = $quote->car?->saleableStock() ?? 0;
    $quotePhysicalStock = $quote->car?->physicalStock() ?? 0;
    $canCreateOrderFromQuote = $quote->status === \App\Models\Quote::STATUS_ACCEPTED
        && $quote->car
        && $quoteAvailableStock > 0;
@endphp
<div class="admin-quotes-page">
    <div class="admin-quotes-head">
        <div>
            <h1>{{ $quote->quote_code }}</h1>
            <p>Bán hàng / Báo giá / {{ $quote->customer?->full_name ?? 'Khách đã xóa' }}</p>
        </div>

        <div class="quote-head-actions">
            <a class="admin-quotes-secondary" href="{{ route('admin.quotes.index') }}">Danh sách</a>
            <a class="admin-quotes-secondary" href="{{ route('admin.quotes.pdf', $quote) }}">Xuất PDF</a>
            @can('quotes.edit')
                <form class="quote-send-form" action="{{ route('admin.quotes.send', $quote) }}" method="post">
                    @csrf
                    <button class="admin-quotes-secondary" type="submit">Gửi cho khách</button>
                </form>
                <a class="admin-quotes-primary" href="{{ route('admin.quotes.edit', $quote) }}">Sửa báo giá</a>
            @endcan
            @if($quote->order)
                @can('orders.view')
                    <a class="admin-quotes-primary" href="{{ route('admin.orders.show', $quote->order->order_id) }}">Xem đơn hàng</a>
                @endcan
            @else
                @can('orders.create')
                    @if($canCreateOrderFromQuote)
                        <form class="quote-send-form" action="{{ route('admin.quotes.createOrder', $quote) }}" method="post">
                            @csrf
                            <button class="admin-quotes-primary" type="submit">Tạo đơn hàng</button>
                        </form>
                    @else
                        <div class="quote-disabled-action">
                            <button class="admin-quotes-disabled" type="button" disabled>Tạo đơn hàng</button>
                            <span>
                                @if($quote->status !== \App\Models\Quote::STATUS_ACCEPTED)
                                    Chỉ có thể tạo đơn khi khách đồng ý báo giá
                                @elseif(!$quote->car || $quoteAvailableStock <= 0)
                                    Xe trong báo giá hiện không còn tồn khả dụng để tạo đơn hàng.
                                @endif
                            </span>
                        </div>
                    @endif
                @endcan
            @endif
        </div>
    </div>

    @if(session('success'))
        <div class="admin-quotes-alert is-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="admin-quotes-alert is-error">{{ $errors->first() }}</div>
    @endif

    @if(session('quote_public_url'))
        <div class="admin-quotes-alert is-success">
            Link khách hàng: <a href="{{ session('quote_public_url') }}" target="_blank" rel="noopener">{{ session('quote_public_url') }}</a>
        </div>
    @endif

    <section class="quote-share-panel">
        <div>
            <h2>Link khách hàng</h2>
            <p>Gửi link này cho khách để xem báo giá, tải PDF và phản hồi chấp nhận hoặc từ chối.</p>
        </div>

        @if($quote->publicUrl())
            <div class="quote-share-url">
                <input type="text" value="{{ $quote->publicUrl() }}" readonly>
                <a class="admin-quotes-secondary" href="{{ $quote->publicUrl() }}" target="_blank" rel="noopener">Mở link</a>
            </div>
        @else
            @can('quotes.edit')
                <form class="quote-send-form" action="{{ route('admin.quotes.send', $quote) }}" method="post">
                    @csrf
                    <button class="admin-quotes-primary" type="submit">Tạo link gửi khách</button>
                </form>
            @else
                <p class="quotes-muted">Chưa có link khách hàng.</p>
            @endcan
        @endif
    </section>

    <section class="quote-detail-panel">
        <div class="quote-profile">
            <div>
                <span class="quotes-code">{{ $quote->quote_code }}</span>
                <h2>{{ $quote->customer?->full_name ?? 'Khách đã xóa' }}</h2>
                <p>{{ $quote->customer?->phone ?? '---' }}{{ $quote->customer?->email ? ' / ' . $quote->customer->email : '' }}</p>
            </div>
            <span class="quotes-status {{ $quote->statusClass() }}">{{ $quote->statusLabel() }}</span>
        </div>

        <dl class="quote-detail-grid">
            <div>
                <dt>Xe báo giá</dt>
                <dd>{{ $quote->car?->title ?? 'Xe đã xóa' }}</dd>
            </div>
            @if($quote->car)
                <div>
                    <dt>Tồn khả dụng</dt>
                    <dd>
                        {{ number_format($quoteAvailableStock, 0, ',', '.') }}
                        @if($quotePhysicalStock <= 0)
                            <span class="quote-stock-warning">Hết hàng</span>
                        @elseif($quoteAvailableStock <= 0)
                            <span class="quote-stock-warning">Đã giữ hết</span>
                        @endif
                    </dd>
                </div>
            @endif
            <div>
                <dt>VIN</dt>
                <dd>{{ $quote->car?->vin ?: '---' }}</dd>
            </div>
            <div>
                <dt>Biển số</dt>
                <dd>{{ $quote->car?->license_plate ?: '---' }}</dd>
            </div>
            <div>
                <dt>Người lập</dt>
                <dd>{{ $quote->user->name ?? 'Hệ thống' }}</dd>
            </div>
            @if($quote->testDrive)
                <div>
                    <dt>Nguồn tạo</dt>
                    <dd>
                        @can('test_drives.view')
                            <a class="quote-source-link" href="{{ route('admin.test_drives.show', $quote->testDrive->ticket_id) }}">
                                Từ lịch lái thử {{ $quote->testDrive->display_code }}
                            </a>
                        @else
                            Từ lịch lái thử {{ $quote->testDrive->display_code }}
                        @endcan
                    </dd>
                </div>
            @endif
            <div>
                <dt>Ngày tạo</dt>
                <dd>{{ $quote->created_at?->format('d/m/Y H:i') }}</dd>
            </div>
            <div>
                <dt>Ngày hết hạn</dt>
                <dd>{{ $quote->expired_at?->format('d/m/Y') ?: '---' }}</dd>
            </div>
            <div>
                <dt>Đã gửi</dt>
                <dd>{{ $quote->sent_at?->format('d/m/Y H:i') ?: '---' }}</dd>
            </div>
            <div>
                <dt>Khách đã xem</dt>
                <dd>{{ $quote->viewed_at?->format('d/m/Y H:i') ?: '---' }}</dd>
            </div>
            <div>
                <dt>Khách phản hồi</dt>
                <dd>{{ $quote->customer_responded_at?->format('d/m/Y H:i') ?: '---' }}</dd>
            </div>
        </dl>
    </section>

    @if($quote->order)
        <section class="quote-source-panel">
            <div>
                <span>Đơn hàng liên quan</span>
                <strong>{{ $quote->order->display_code }}</strong>
                <p>Đơn hàng được tạo từ báo giá {{ $quote->quote_code }}.</p>
            </div>
            @can('orders.view')
                <a class="admin-quotes-secondary" href="{{ route('admin.orders.show', $quote->order->order_id) }}">Xem chi tiết đơn hàng</a>
            @endcan
        </section>
    @endif

    <section class="quote-pricing-panel">
        <div class="quote-section-title">
            <h2>Chi tiết giá</h2>
            <span>{{ $quote->money('total_price') }}</span>
        </div>

        <div class="quote-price-lines">
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
                <span>Tổng thanh toán</span>
                <strong>{{ $quote->money('total_price') }}</strong>
            </div>
        </div>
    </section>

    @if($quote->quotePromotions->isNotEmpty())
        <section class="quote-note-panel quote-promotion-summary">
            <div class="quote-section-title">
                <h2>Khuyến mãi đã áp dụng</h2>
                <span>{{ number_format($quote->promotionDiscountTotal(), 0, ',', '.') }} đ</span>
            </div>

            <div class="quote-promotion-summary-list">
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
        <section class="quote-note-panel">
            <h2>Ghi chú</h2>
            <p>{{ $quote->note }}</p>
        </section>
    @endif

    @if($quote->customer_response_note)
        <section class="quote-note-panel">
            <h2>Ghi chú phản hồi của khách</h2>
            <p>{{ $quote->customer_response_note }}</p>
        </section>
    @endif

    @can('quotes.delete')
        <section class="quote-danger-panel">
            <div>
                <h2>Xóa báo giá</h2>
                <p>Thao tác này chỉ xóa bản ghi báo giá, không xóa khách hàng hoặc xe liên quan.</p>
            </div>
            <form action="{{ route('admin.quotes.destroy', $quote) }}" method="post" onsubmit="return confirm('Xóa báo giá này?');">
                @csrf
                @method('DELETE')
                <button type="submit">Xóa báo giá</button>
            </form>
        </section>
    @endcan
</div>
@endsection
