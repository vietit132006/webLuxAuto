<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    // 1. Hiển thị danh sách đơn hàng
    public function index(Request $request)
    {
        // Lấy danh sách đơn hàng, kèm theo thông tin Khách hàng (user) và Chi tiết xe (details.car)
        $orders = Order::with(['user', 'details.car'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.orders.index', compact('orders'));
    }

    // 2. Cập nhật trạng thái đơn hàng
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|integer|in:0,1,2,3'
        ]);

        $order = Order::findOrFail($id);
        $order->update(['status' => $request->status]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng #' . $id . ' thành công!');
    }
}
