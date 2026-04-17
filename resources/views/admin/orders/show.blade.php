@extends('layouts.admin')
@section('title', 'Chi tiết đơn hàng #' . $order->order_id)

@section('content')
<style>
    .order-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }
    .order-id {
        font-size: 1.8rem;
        font-weight: 800;
        color: var(--accent);
    }
    .order-date {
        color: var(--muted);
        font-size: 0.95rem;
    }

    .order-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2rem;
    }

    .panel {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .panel-title {
        font-size: 1.2rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        border-bottom: 1px solid var(--border);
        padding-bottom: 0.8rem;
    }

    .info-group {
        margin-bottom: 1.2rem;
    }
    .info-label {
        font-size: 0.85rem;
        color: var(--muted);
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.3rem;
    }
    .info-value {
        font-weight: 600;
        color: var(--text);
    }

    .car-item {
        display: flex;
        gap: 1.5rem;
        align-items: center;
        padding: 1rem;
        background: rgba(255, 255, 255, 0.02);
        border-radius: 10px;
    }
    .car-img {
        width: 150px;
        height: 100px;
        object-fit: cover;
        border-radius: 8px;
    }
    .car-info { flex: 1; }
    .car-name { font-size: 1.2rem; font-weight: 700; color: var(--text); }
    .car-price { color: var(--accent); font-weight: 700; font-size: 1.1rem; }

    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: bold;
        font-size: 0.9rem;
    }
    .badge-0 { background: rgba(234, 179, 8, 0.1); color: #facc15; }
    .badge-1 { background: rgba(59, 130, 246, 0.1); color: #60a5fa; }
    .badge-2 { background: rgba(16, 185, 129, 0.1); color: #34d399; }
    .badge-3 { background: rgba(239, 68, 68, 0.1); color: #f87171; }

    .action-btns {
        display: flex;
        gap: 1rem;
        margin-top: 1rem;
    }
    .btn {
        padding: 0.6rem 1.2rem;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        border: none;
        transition: 0.2s;
    }
    .btn-confirm { background: #34d399; color: #000; }
    .btn-cancel { background: #f87171; color: #fff; }
    .btn-back { background: var(--border); color: var(--text); }
</style>

<div class="wrap">
    @if(session('success'))
        <style>
            .flash-alert {
                background-color: #d1fae5;
                color: #065f46;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                border: 1px solid #34d399;
                font-weight: 600;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .btn-close-alert {
                background: none;
                border: none;
                color: inherit;
                font-size: 1.5rem;
                line-height: 1;
                cursor: pointer;
                padding: 0 0 0 1rem;
            }
        </style>
        <div id="success-alert" class="flash-alert">
            <span>✅ {{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if($errors->has('status'))
        <style>
            .error-alert {
                background-color: #fee2e2;
                color: #991b1b;
                padding: 1rem 1.5rem;
                border-radius: 8px;
                margin-bottom: 1.5rem;
                border: 1px solid #f87171;
                font-weight: 600;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
        </style>
        <div class="error-alert">
            <span>❌ {{ $errors->first('status') }}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    <div class="order-header">
        <div>
            <div class="order-id">Đơn hàng #{{ $order->order_id }}</div>
            <div class="order-date">Đặt lúc: {{ $order->created_at->format('H:i - d/m/Y') }}</div>
        </div>
        <div>
            @if($order->status == 0) <span class="status-badge badge-0">⏳ Chờ xử lý</span>
            @elseif($order->status == 1) <span class="status-badge badge-1">💸 Đã cọc</span>
            @elseif($order->status == 2) <span class="status-badge badge-2">✅ Hoàn tất</span>
            @elseif($order->status == 3) <span class="status-badge badge-3">❌ Đã hủy</span>
            @endif
        </div>
    </div>

    <div class="order-grid">
        <div class="main-content">
            <div class="panel">
                <h3 class="panel-title">Sản phẩm trong đơn hàng</h3>
                @foreach($order->details as $detail)
                    <div class="car-item">
                        @if($detail->car && $detail->car->image)
                            <img src="{{ asset('storage/' . $detail->car->image) }}" class="car-img">
                        @else
                            <div style="width: 150px; height: 100px; background: #0a0d12; border-radius: 8px;"></div>
                        @endif
                        <div class="car-info">
                            <div class="car-name">{{ $detail->car->name ?? 'Xe đã bị xóa' }}</div>
                            <div class="car-price">{{ number_format($detail->price, 0, ',', '.') }} đ</div>
                            <div style="color: var(--muted); font-size: 0.9rem; margin-top: 5px;">Số lượng: {{ $detail->quantity }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <h3 class="panel-title">Thao tác nhanh</h3>
                <div class="action-btns">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-back">← Quay lại danh sách</a>
                    
                    @if($order->status != 2 && $order->status != 3)
                        <form action="{{ route('admin.orders.updateStatus', $order->order_id) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="status" value="2">
                            <button type="submit" class="btn btn-confirm">Xác nhận hoàn tất</button>
                        </form>

                        <form action="{{ route('admin.orders.updateStatus', $order->order_id) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="status" value="3">
                            <button type="submit" class="btn btn-cancel">Hủy đơn hàng</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="sidebar-content">
            <div class="panel">
                <h3 class="panel-title">Thông tin khách hàng</h3>
                <div class="info-group">
                    <div class="info-label">Họ tên</div>
                    <div class="info-value">{{ $order->user->name ?? 'Khách ẩn danh' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $order->user->email ?? 'N/A' }}</div>
                </div>
                <div class="info-group">
                    <div class="info-label">Số điện thoại</div>
                    <div class="info-value">{{ $order->user->phone ?? 'N/A' }}</div>
                </div>
            </div>

            <div class="panel">
                <h3 class="panel-title">Tổng kết thanh toán</h3>
                <div style="display: flex; justify-content: space-between; margin-bottom: 0.8rem;">
                    <span style="color: var(--muted);">Giá trị xe:</span>
                    <span style="font-weight: 600;">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
                </div>
                <div style="display: flex; justify-content: space-between; margin-bottom: 1.5rem;">
                    <span style="color: var(--muted);">Số tiền đã cọc:</span>
                    <span style="font-weight: 600; color: #34d399;">{{ $order->status >= 1 ? '20.000.000' : '0' }} đ</span>
                </div>
                <div style="border-top: 1px solid var(--border); padding-top: 1rem; display: flex; justify-content: space-between;">
                    <span style="font-weight: 700; font-size: 1.1rem;">Tổng cộng:</span>
                    <span style="font-weight: 800; font-size: 1.3rem; color: var(--accent);">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
