@extends('layouts.admin')
@section('title', 'Quản lý Đơn hàng')

@section('content')
<style>
    .page-title {
        margin: 0 0 1.5rem;
        font-size: 1.75rem;
        font-weight: 700;
        color: var(--text);
    }

    /* --- CSS CHO BẢNG ADMIN --- */
    .table-responsive {
        overflow-x: auto;
        border-radius: 12px;
        border: 1px solid var(--border);
        background: var(--surface);
    }
    .admin-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }
    .admin-table th, .admin-table td {
        padding: 1rem;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
    }
    .admin-table th {
        background: rgba(255, 255, 255, 0.02);
        color: var(--muted);
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .admin-table tr:hover {
        background: rgba(255, 255, 255, 0.02);
    }

    /* --- HUY HIỆU TRẠNG THÁI (BADGES) --- */
    .badge {
        padding: 0.35rem 0.75rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: bold;
        display: inline-block;
    }
    .badge-0 { background: rgba(234, 179, 8, 0.1); color: #facc15; } /* Chờ thanh toán - Vàng */
    .badge-1 { background: rgba(59, 130, 246, 0.1); color: #60a5fa; } /* Đã cọc - Xanh dương */
    .badge-2 { background: rgba(16, 185, 129, 0.1); color: #34d399; } /* Hoàn tất - Xanh lá */
    .badge-3 { background: rgba(239, 68, 68, 0.1); color: #f87171; }  /* Đã hủy - Đỏ */

    /* Form cập nhật trạng thái */
    .status-form {
        display: flex;
        gap: 5px;
    }
    .status-select {
        background: #0a0d12;
        color: var(--text);
        border: 1px solid var(--border);
        padding: 0.4rem;
        border-radius: 6px;
        font-size: 0.85rem;
    }
    .btn-update {
        background: var(--accent);
        color: #000;
        border: none;
        padding: 0.4rem 0.8rem;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        font-size: 0.85rem;
    }
    .btn-update:hover { background: #e4d08a; }
</style>

<div class="wrap">
    <h1 class="page-title">Quản lý Giao dịch & Đơn hàng</h1>

    {{-- @if(session('success'))
        <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: bold;">
            ✅ {{ session('success') }}
        </div>
    @endif --}}
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
                transition: opacity 0.5s ease, transform 0.5s ease;
            }
            .flash-alert.hide {
                opacity: 0;
                transform: translateY(-10px);
                pointer-events: none;
            }
            .btn-close-alert {
                background: none;
                border: none;
                color: #065f46;
                font-size: 1.5rem;
                line-height: 1;
                cursor: pointer;
                padding: 0 0 0 1rem;
                transition: transform 0.2s;
            }
            .btn-close-alert:hover {
                transform: scale(1.2);
                color: #047857;
            }
        </style>

        <div id="success-alert" class="flash-alert">
            <span>✅ {{ session('success') }}</span>
            <button type="button" class="btn-close-alert" onclick="closeAlert()" aria-label="Đóng">&times;</button>
        </div>

        <script>
            function closeAlert() {
                const alertBox = document.getElementById('success-alert');
                if (alertBox) {
                    alertBox.classList.add('hide'); // Thêm class ẩn để chạy hiệu ứng mờ dần
                    setTimeout(() => {
                        alertBox.remove(); // Xóa hẳn thẻ HTML khỏi trang sau khi mờ xong
                    }, 500);
                }
            }

            // Tự động gọi hàm đóng sau 2 giây (2000 ms)
            setTimeout(() => {
                closeAlert();
            }, 2000);
        </script>
    @endif  

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách hàng</th>
                    <th>Xe đặt mua</th>
                    <th>Giá trị xe</th>
                    <th>Trạng thái</th>
                    <th>Ngày đặt</th>
                    <th width="200">Cập nhật trạng thái</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $order)
                <tr>
                    <td style="font-weight: bold; color: var(--accent);">#{{ $order->order_id }}</td>

                    <td>
                        <div style="font-weight: bold; color: var(--text);">{{ $order->user->name ?? 'Khách ẩn danh' }}</div>
                        <div style="font-size: 0.85rem; color: var(--muted);">{{ $order->user->email ?? '' }}</div>
                    </td>

                    <td>
                        @foreach($order->details as $detail)
                            <div style="font-weight: 600;">{{ $detail->car->name ?? 'Xe đã bị xóa' }}</div>
                        @endforeach
                    </td>

                    <td style="color: #34d399; font-weight: bold;">
                        {{ number_format($order->total_price, 0, ',', '.') }} đ
                    </td>

                    <td>
                        @if($order->status == 0) <span class="badge badge-0">⏳ Chờ xử lý</span>
                        @elseif($order->status == 1) <span class="badge badge-1">💸 Đã cọc</span>
                        @elseif($order->status == 2) <span class="badge badge-2">✅ Giao xe (Hoàn tất)</span>
                        @elseif($order->status == 3) <span class="badge badge-3">❌ Đã hủy</span>
                        @endif
                    </td>

                    <td style="font-size: 0.9rem; color: var(--muted);">
                        {{ $order->created_at ? $order->created_at->format('H:i - d/m/Y') : 'N/A' }}
                    </td>

                    <td>
                        <form action="{{ route('admin.orders.updateStatus', $order->order_id) }}" method="POST" class="status-form">
                            @csrf
                            <select name="status" class="status-select">
                                <option value="0" {{ $order->status == 0 ? 'selected' : '' }}>Chờ xử lý</option>
                                <option value="1" {{ $order->status == 1 ? 'selected' : '' }}>Đã cọc</option>
                                <option value="2" {{ $order->status == 2 ? 'selected' : '' }}>Hoàn tất</option>
                                <option value="3" {{ $order->status == 3 ? 'selected' : '' }}>Hủy đơn</option>
                            </select>
                            <button type="submit" class="btn-update">Lưu</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; padding: 3rem; color: var(--muted);">
                        Chưa có đơn hàng nào trong hệ thống.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div style="margin-top: 2rem; display: flex; justify-content: center;">
            {{ $orders->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection
