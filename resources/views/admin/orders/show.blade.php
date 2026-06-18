@extends('layouts.admin')
@section('title', 'Chi tiết đơn hàng #' . $order->order_id)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-orders-show.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    @if(session('success'))
        <div id="success-alert" class="flash-alert">
            <span>✅ {{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()">&times;</button>
        </div>
    @endif

    @if($errors->has('status'))
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
                            <div class="admin-orders-show-inline-11"></div>
                        @endif
                        <div class="car-info">
                            <div class="car-name">{{ $detail->car->name ?? 'Xe đã bị xóa' }}</div>
                            <div class="car-price">{{ number_format($detail->price, 0, ',', '.') }} đ</div>
                            <div class="admin-orders-show-inline-10">Số lượng: {{ $detail->quantity }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="panel">
                <h3 class="panel-title">Thao tác nhanh</h3>
                <div class="action-btns">
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-back">← Quay lại danh sách</a>
                    
                    @if($order->status != 2 && $order->status != 3)
                        <form class="admin-orders-show-inline-9" action="{{ route('admin.orders.updateStatus', $order->order_id) }}" method="POST">
                            @csrf
                            <input type="hidden" name="status" value="2">
                            <button type="submit" class="btn btn-confirm">Xác nhận hoàn tất</button>
                        </form>

                        <form class="admin-orders-show-inline-9" action="{{ route('admin.orders.updateStatus', $order->order_id) }}" method="POST">
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
                <div class="admin-orders-show-inline-8">
                    <span class="admin-orders-show-inline-5">Giá trị xe:</span>
                    <span class="admin-orders-show-inline-7">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
                </div>
                <div class="admin-orders-show-inline-6">
                    <span class="admin-orders-show-inline-5">Số tiền đã cọc:</span>
                    <span class="admin-orders-show-inline-4">{{ $order->status >= 1 ? '20.000.000' : '0' }} đ</span>
                </div>
                <div class="admin-orders-show-inline-3">
                    <span class="admin-orders-show-inline-2">Tổng cộng:</span>
                    <span class="admin-orders-show-inline-1">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection