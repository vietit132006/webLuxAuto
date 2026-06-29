<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <title>{{ $quote->quote_code }}</title>
    <style>
        @page {
            margin: 30px 36px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #111827;
            font-size: 12px;
            line-height: 1.55;
        }

        h1,
        h2,
        p {
            margin: 0;
        }

        .header {
            border-bottom: 2px solid #c9a962;
            padding-bottom: 18px;
            margin-bottom: 22px;
        }

        .brand {
            color: #111827;
            font-size: 24px;
            font-weight: 800;
            letter-spacing: 1px;
        }

        .brand span {
            color: #a9822d;
        }

        .quote-code {
            margin-top: 8px;
            color: #374151;
            font-size: 14px;
            font-weight: 700;
        }

        .meta {
            margin-top: 6px;
            color: #4b5563;
        }

        .grid {
            width: 100%;
            margin-bottom: 18px;
        }

        .grid td {
            width: 50%;
            vertical-align: top;
            padding-right: 18px;
        }

        .box {
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 14px;
        }

        .box h2 {
            margin-bottom: 8px;
            color: #111827;
            font-size: 13px;
            text-transform: uppercase;
        }

        .muted {
            color: #6b7280;
        }

        table.pricing {
            width: 100%;
            border-collapse: collapse;
            margin-top: 18px;
        }

        table.pricing th,
        table.pricing td {
            border-bottom: 1px solid #e5e7eb;
            padding: 10px 8px;
            text-align: left;
        }

        table.pricing th {
            background: #111827;
            color: #ffffff;
            font-size: 11px;
            text-transform: uppercase;
        }

        table.pricing td:last-child,
        table.pricing th:last-child {
            text-align: right;
        }

        .total-row td {
            border-bottom: 0;
            background: #f8fafc;
            color: #111827;
            font-size: 14px;
            font-weight: 800;
        }

        .note {
            margin-top: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            white-space: pre-line;
        }

        .footer {
            margin-top: 28px;
            color: #6b7280;
            font-size: 11px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">LUX <span>AUTO</span></div>
        <div class="quote-code">Báo giá {{ $quote->quote_code }}</div>
        <p class="meta">Ngày lập: {{ $quote->created_at?->format('d/m/Y H:i') }} | Trạng thái: {{ $quote->statusLabel() }}</p>
    </div>

    <table class="grid">
        <tr>
            <td>
                <div class="box">
                    <h2>Khách hàng</h2>
                    <p><strong>{{ $quote->customer?->full_name ?? 'Khách đã xóa' }}</strong></p>
                    <p class="muted">Mã KH: {{ $quote->customer?->customer_code ?? '---' }}</p>
                    <p class="muted">SĐT: {{ $quote->customer?->phone ?? '---' }}</p>
                    <p class="muted">Email: {{ $quote->customer?->email ?? '---' }}</p>
                </div>
            </td>
            <td>
                <div class="box">
                    <h2>Xe</h2>
                    <p><strong>{{ $quote->car?->title ?? 'Xe đã xóa' }}</strong></p>
                    <p class="muted">VIN: {{ $quote->car?->vin ?? '---' }}</p>
                    <p class="muted">Biển số: {{ $quote->car?->license_plate ?? '---' }}</p>
                    <p class="muted">Hết hạn: {{ $quote->expired_at?->format('d/m/Y') ?: '---' }}</p>
                </div>
            </td>
        </tr>
    </table>

    <table class="pricing">
        <thead>
            <tr>
                <th>Hạng mục</th>
                <th>Số tiền</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Giá xe</td>
                <td>{{ $quote->money('vehicle_price') }}</td>
            </tr>
            <tr>
                <td>Giảm giá</td>
                <td>-{{ $quote->money('discount_amount') }}</td>
            </tr>
            <tr>
                <td>Phí đăng ký</td>
                <td>{{ $quote->money('registration_fee') }}</td>
            </tr>
            <tr>
                <td>Phí biển số</td>
                <td>{{ $quote->money('plate_fee') }}</td>
            </tr>
            <tr>
                <td>Phí bảo hiểm</td>
                <td>{{ $quote->money('insurance_fee') }}</td>
            </tr>
            <tr>
                <td>Phí khác</td>
                <td>{{ $quote->money('other_fee') }}</td>
            </tr>
            <tr class="total-row">
                <td>Tổng thanh toán</td>
                <td>{{ $quote->money('total_price') }}</td>
            </tr>
        </tbody>
    </table>

    @if($quote->quotePromotions->isNotEmpty())
        <div class="note">
            <strong>Khuyến mãi đã áp dụng:</strong>
            @foreach($quote->quotePromotions as $quotePromotion)
                <br>
                {{ $quotePromotion->promotion?->promotion_code ?? 'KM đã xóa' }} - {{ $quotePromotion->promotion?->title ?? 'Khuyến mãi đã xóa' }}:
                giảm {{ number_format((float) $quotePromotion->discount_amount, 0, ',', '.') }} đ
                @if($quotePromotion->gift_note)
                    | {{ $quotePromotion->gift_note }}
                @endif
            @endforeach
        </div>
    @endif

    @if($quote->note)
        <div class="note">
            <strong>Ghi chú:</strong>
            <br>
            {{ $quote->note }}
        </div>
    @endif

    <div class="footer">
        Báo giá được lập bởi {{ $quote->user->name ?? 'LUXAUTO' }}. Giá trị báo giá phụ thuộc trạng thái xe, phí thực tế và thời hạn hiệu lực.
    </div>
</body>
</html>
