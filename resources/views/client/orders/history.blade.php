@extends('layouts.site')
@section('title', 'Lịch sử giao dịch')

@section('content')
<style>
    .history-wrap {
        max-width: 1000px;
        margin: 3rem auto;
        padding: 0 1.5rem;
    }
    .page-title {
        color: var(--accent);
        font-size: 2rem;
        margin-bottom: 2rem;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        text-align: center;
    }

    .history-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        overflow: hidden;
        margin-bottom: 1.5rem;
        transition: transform 0.2s;
    }
    .history-card:hover {
        border-color: var(--accent-dim);
    }

    /* Header của mỗi đơn hàng */
    .h-card-header {
        background: rgba(255, 255, 255, 0.03);
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .h-code {
        font-weight: bold;
        color: var(--text);
        font-size: 1.1rem;
    }
    .h-date {
        color: var(--muted);
        font-size: 0.9rem;
    }

    /* Chi tiết bên trong */
    .h-card-body {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }
    .h-car-img {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid var(--border);
    }
    .h-car-info {
        flex: 1;
    }
    .h-car-name {
        font-size: 1.25rem;
        color: var(--text);
        margin: 0 0 0.5rem;
        font-weight: bold;
    }
    .h-price {
        color: var(--accent);
        font-weight: bold;
        font-size: 1.1rem;
    }

    /* Huy hiệu trạng thái */
    .badge {
        padding: 0.4rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: bold;
        display: inline-block;
    }
    .badge-0 { background: rgba(234, 179, 8, 0.1); color: #facc15; border: 1px solid rgba(250, 204, 21, 0.2); }
    .badge-1 { background: rgba(59, 130, 246, 0.1); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.2); }
    .badge-2 { background: rgba(16, 185, 129, 0.1); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.2); }
    .badge-3 { background: rgba(239, 68, 68, 0.1); color: #f87171; border: 1px solid rgba(248, 113, 113, 0.2); }

    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: var(--surface);
        border: 1px dashed var(--border);
        border-radius: 12px;
    }
</style>

<div class="history-wrap">
    <h1 class="page-title">Lịch sử giao dịch</h1>

    @if($orders->isEmpty())
        <div class="empty-state">
            <h3 style="color: var(--text); margin-top:0;">Bạn chưa có giao dịch nào!</h3>
            <p style="color: var(--muted); margin-bottom: 2rem;">Hãy khám phá các dòng xe sang trọng tại Lux Auto và trải nghiệm dịch vụ đẳng cấp của chúng tôi.</p>
            <a href="{{ route('cars.index') }}" style="background: var(--accent); color: #000; padding: 0.8rem 2rem; border-radius: 50px; text-decoration: none; font-weight: bold;">Khám phá xe ngay</a>
        </div>
    @else
        @foreach($orders as $order)
            <div class="history-card">
                <div class="h-card-header">
                    <div>
                        <span class="h-code">Mã đơn: #{{ $order->order_id }}</span>
                        <span style="margin: 0 10px; color: var(--border);">|</span>
                        <span class="h-date">Ngày đặt: {{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : 'N/A' }}</span>
                    </div>
                    <div>
                        @if($order->status == 0) <span class="badge badge-0">⏳ Đang chờ xác nhận</span>
                        @elseif($order->status == 1) <span class="badge badge-1">💸 Đã đặt cọc thành công</span>
                        @elseif($order->status == 2) <span class="badge badge-2">✅ Giao dịch hoàn tất</span>
                        @elseif($order->status == 3) <span class="badge badge-3">❌ Đã hủy</span>
                        @endif
                    </div>
                </div>

                @foreach($order->details as $detail)
                <div class="h-card-body">
                    @if($detail->car && $detail->car->image)
                        <img src="{{ asset('storage/' . $detail->car->image) }}" alt="Car" class="h-car-img">
                    @else
                        <div style="width: 120px; height: 80px; background: #0a0d12; border-radius: 6px; display:flex; align-items:center; justify-content:center; color: var(--muted); font-size: 0.8rem;">NO IMAGE</div>
                    @endif

                    <div class="h-car-info">
                        <h4 class="h-car-name">
                            <a href="{{ $detail->car ? route('cars.show_public', $detail->car->car_id) : '#' }}" style="color: inherit; text-decoration: none;">
                                {{ $detail->car ? $detail->car->name : 'Xe đã bị gỡ khỏi hệ thống' }}
                            </a>
                        </h4>
                        <div class="h-price">Giá xe: {{ number_format($detail->price, 0, ',', '.') }} đ</div>
                        <div style="font-size: 0.85rem; color: var(--muted); margin-top: 5px;">Số tiền cần cọc: 20.000.000 đ</div>
                    </div>
                </div>
                @endforeach
            </div>
        @endforeach

        @if ($orders->hasPages())
            <div style="margin-top: 2rem; display: flex; justify-content: center;">
                {{ $orders->links('pagination.lux') }}
            </div>
        @endif
    @endif
</div>
@endsection
