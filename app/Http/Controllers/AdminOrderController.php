<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\StockMovementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminOrderController extends Controller
{
    public function __construct(private readonly StockMovementService $stockMovementService)
    {
    }

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

        try {
            DB::transaction(function () use ($id, $request) {
                $order = Order::with(['details.car', 'user'])->lockForUpdate()->findOrFail($id);
                $statusBefore = $order->status;
                $statusAfter = (int) $request->status;

                if ((string) $statusBefore === (string) $statusAfter) {
                    return;
                }

                $order->update(['status' => $statusAfter]);

                $this->stockMovementService->recordOrderStatusChange(
                    $order,
                    $statusBefore,
                    $statusAfter,
                    $request
                );
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withErrors(['stock' => $e->getMessage()]);
        }

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng #' . $id . ' thành công!');
    }
}
