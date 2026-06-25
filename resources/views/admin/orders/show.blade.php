@extends('layouts.admin')
@section('title', 'Chi tiết đơn hàng ' . $order->display_code)

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-orders-show.css')
    @endif
@endpush

@section('content')
<div class="wrap">
    @if(session('success'))
        <div class="flash-alert">
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
        </div>
    @endif

    @if($errors->any())
        <div class="error-alert">
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            <button type="button" class="btn-close-alert" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
        </div>
    @endif

    <div class="order-header">
        <div>
            <a href="{{ route('admin.orders.index') }}" class="back-link">Quay lại danh sách</a>
            <h1 class="order-id">{{ $order->display_code }}</h1>
            <div class="order-date">Ngày tạo: {{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : 'N/A' }}</div>
        </div>
        <span class="status-badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
    </div>

    <div class="order-grid">
        <div class="main-content">
            <section class="panel">
                <h2 class="panel-title">Danh sách xe</h2>

                <div class="car-list">
                    @forelse($order->details as $detail)
                        <div class="car-item">
                            @if($detail->car && $detail->car->image)
                                <img src="{{ asset('storage/' . $detail->car->image) }}" class="car-img" alt="{{ $detail->car->name }}">
                            @else
                                <div class="car-img-placeholder">NO IMAGE</div>
                            @endif

                            <div class="car-info">
                                <div class="car-name">{{ $detail->car->name ?? 'Xe đã bị xóa' }}</div>
                                <div class="car-meta">Số lượng: {{ $detail->quantity }}</div>
                            </div>

                            <div class="car-price">{{ number_format((float) $detail->price, 0, ',', '.') }} đ</div>
                        </div>
                    @empty
                        <div class="empty-state">Đơn hàng chưa có xe.</div>
                    @endforelse
                </div>
            </section>

            <section class="panel">
                <h2 class="panel-title">Lịch sử trạng thái</h2>

                <div class="history-table-wrap">
                    <table class="history-table">
                        <thead>
                            <tr>
                                <th>Thời gian</th>
                                <th>Trạng thái cũ</th>
                                <th>Trạng thái mới</th>
                                <th>Người cập nhật</th>
                                <th>Ghi chú</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($order->statusHistories as $history)
                                <tr>
                                    <td>{{ $history->created_at ? $history->created_at->format('H:i - d/m/Y') : 'N/A' }}</td>
                                    <td>{{ \App\Models\Order::labelForStatus($history->old_status) }}</td>
                                    <td>
                                        <span class="badge badge-{{ \App\Models\Order::normalizeStatus($history->new_status) ?? 'unknown' }}">
                                            {{ \App\Models\Order::labelForStatus($history->new_status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="history-user">{{ $history->user->name ?? 'Hệ thống' }}</div>
                                        <div class="history-email">{{ $history->user->email ?? '' }}</div>
                                    </td>
                                    <td>{{ $history->note ?? 'N/A' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="empty-cell">Chưa có lịch sử trạng thái.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="sidebar-content">
            <section class="panel">
                <h2 class="panel-title">Khách hàng</h2>
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
            </section>

            <section class="panel">
                <h2 class="panel-title">Thanh toán</h2>
                <div class="summary-row">
                    <span>Tổng tiền</span>
                    <strong>{{ number_format((float) $order->total_price, 0, ',', '.') }} đ</strong>
                </div>
                <div class="summary-row">
                    <span>Tiền cọc</span>
                    <strong>{{ number_format((float) ($order->deposit_amount ?? 0), 0, ',', '.') }} đ</strong>
                </div>
                <div class="summary-row">
                    <span>Ngày cọc</span>
                    <strong>{{ $order->deposit_date ? $order->deposit_date->format('H:i - d/m/Y') : 'N/A' }}</strong>
                </div>
                <div class="summary-total">
                    <span>Còn lại</span>
                    <strong>{{ number_format(max(0, (float) $order->total_price - (float) ($order->deposit_amount ?? 0)), 0, ',', '.') }} đ</strong>
                </div>
            </section>

            @can('orders.edit')
                <section class="panel">
                    <h2 class="panel-title">Cập nhật trạng thái</h2>
                    <form action="{{ route('admin.orders.updateStatus', $order->order_id) }}" method="POST" class="status-update-form">
                        @csrf
                        <label for="status" class="form-label">Trạng thái</label>
                        <select id="status" name="status" class="form-control">
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string)$order->status === (string)$value)>{{ $label }}</option>
                            @endforeach
                        </select>

                        <button type="submit" class="btn-submit">Lưu trạng thái</button>
                    </form>
                </section>
            @endcan
        </div>
    </div>
</div>
@endsection
