@extends('layouts.admin')
@section('title', 'Quản lý đơn hàng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-orders-index.css')
    @endif
@endpush

@section('content')
@php
    $filters = $filters ?? [];
@endphp

<div class="wrap">
    <div class="orders-header">
        <h1 class="page-title">Quản lý đơn hàng</h1>
        <div class="orders-actions">
            @can('orders.create')
                <a href="{{ route('admin.orders.create') }}" class="btn-action btn-primary">Tạo đơn</a>
            @endcan
            <a href="{{ route('admin.orders.export', request()->query()) }}" class="btn-action btn-secondary">Export Excel</a>
        </div>
    </div>

    @if(session('success'))
        <div id="success-alert" class="flash-alert">
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="closeAlert()" aria-label="Đóng">&times;</button>
        </div>

        <script>
            function closeAlert() {
                const alertBox = document.getElementById('success-alert');
                if (alertBox) {
                    alertBox.classList.add('hide');
                    setTimeout(() => alertBox.remove(), 500);
                }
            }

            setTimeout(() => closeAlert(), 2500);
        </script>
    @endif

    @if($errors->any())
        <div class="error-alert">
            <div>
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            <button type="button" class="btn-close-alert is-error" onclick="this.parentElement.remove()" aria-label="Đóng">&times;</button>
        </div>
    @endif

    <form method="GET" action="{{ route('admin.orders.index') }}" class="filter-panel">
        <div class="filter-grid">
            <div class="filter-field filter-field-search">
                <label for="order-q">Tìm kiếm</label>
                <input id="order-q" type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Mã đơn, khách hàng, email">
            </div>

            <div class="filter-field">
                <label for="order-status">Trạng thái</label>
                <select id="order-status" name="status">
                    <option value="">Tất cả</option>
                    @foreach($statusOptions as $value => $label)
                        <option value="{{ $value }}" @selected((string)($filters['status'] ?? '') === (string)$value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-field">
                <label for="date-from">Từ ngày</label>
                <input id="date-from" type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
            </div>

            <div class="filter-field">
                <label for="date-to">Đến ngày</label>
                <input id="date-to" type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
            </div>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn-filter">Lọc</button>
            <a href="{{ route('admin.orders.index') }}" class="btn-reset">Xóa lọc</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã đơn</th>
                    <th>Khách hàng</th>
                    <th>Xe đặt mua</th>
                    <th>Tổng tiền</th>
                    <th>Tiền cọc</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                    <th>Thao tác</th>
                    <th>Cập nhật</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td class="order-code">{{ $order->display_code }}</td>

                        <td>
                            <div class="customer-name">{{ $order->user->name ?? 'Khách ẩn danh' }}</div>
                            <div class="customer-email">{{ $order->user->email ?? '' }}</div>
                        </td>

                        <td>
                            @forelse($order->details as $detail)
                                <div class="car-line">
                                    <span>{{ $detail->car->name ?? 'Xe đã bị xóa' }}</span>
                                    <small>x{{ $detail->quantity }}</small>
                                </div>
                            @empty
                                <span class="muted">Không có xe</span>
                            @endforelse
                        </td>

                        <td class="money-cell">{{ number_format((float) $order->total_price, 0, ',', '.') }} đ</td>
                        <td>{{ number_format((float) ($order->deposit_amount ?? 0), 0, ',', '.') }} đ</td>

                        <td>
                            <span class="badge {{ $order->status_badge_class }}">{{ $order->status_label }}</span>
                        </td>

                        <td class="date-cell">{{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : 'N/A' }}</td>

                        <td>
                            <a href="{{ route('admin.orders.show', $order->order_id) }}" class="btn-detail">Xem chi tiết</a>
                        </td>

                        <td>
                            @can('orders.edit')
                                <form action="{{ route('admin.orders.updateStatus', $order->order_id) }}" method="POST" class="status-form">
                                    @csrf
                                    <select name="status" class="status-select" aria-label="Trạng thái đơn hàng {{ $order->display_code }}">
                                        @foreach($statusOptions as $value => $label)
                                            <option value="{{ $value }}" @selected((string)$order->status === (string)$value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn-update">Lưu</button>
                                </form>
                            @else
                                <span class="muted">Không có quyền</span>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="empty-cell" colspan="9">Chưa có đơn hàng nào phù hợp.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="pagination-wrap">
            {{ $orders->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection
