@extends('layouts.admin')
@section('title', 'Quản lý Đơn hàng')

@push('styles')
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite('resources/css/admin-orders-index.css')
    @endif
@endpush


@section('content')

<div class="wrap">
    <h1 class="page-title">Quản lý Giao dịch & Đơn hàng</h1>

    {{-- @if(session('success'))
        <div class="admin-orders-index-inline-9">
            ✅ {{ session('success') }}
        </div>
    @endif --}}
    @if(session('success'))

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
                    <td class="admin-orders-index-inline-8">#{{ $order->order_id }}</td>

                    <td>
                        <div class="admin-orders-index-inline-7">{{ $order->user->name ?? 'Khách ẩn danh' }}</div>
                        <div class="admin-orders-index-inline-6">{{ $order->user->email ?? '' }}</div>
                    </td>

                    <td>
                        @foreach($order->details as $detail)
                            <div class="admin-orders-index-inline-5">{{ $detail->car->name ?? 'Xe đã bị xóa' }}</div>
                        @endforeach
                    </td>

                    <td class="admin-orders-index-inline-4">
                        {{ number_format($order->total_price, 0, ',', '.') }} đ
                    </td>

                    <td>
                        @if($order->status == 0) <span class="badge badge-0">⏳ Chờ xử lý</span>
                        @elseif($order->status == 1) <span class="badge badge-1">💸 Đã cọc</span>
                        @elseif($order->status == 2) <span class="badge badge-2">✅ Giao xe (Hoàn tất)</span>
                        @elseif($order->status == 3) <span class="badge badge-3">❌ Đã hủy</span>
                        @endif
                    </td>

                    <td class="admin-orders-index-inline-3">
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
                    <td class="admin-orders-index-inline-2" colspan="7">
                        Chưa có đơn hàng nào trong hệ thống.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($orders->hasPages())
        <div class="admin-orders-index-inline-1">
            {{ $orders->links('pagination.lux') }}
        </div>
    @endif
</div>
@endsection