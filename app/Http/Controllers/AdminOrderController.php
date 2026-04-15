<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUpdateOrderStatusRequest;
use App\Models\Car;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
    public function updateStatus(AdminUpdateOrderStatusRequest $request, $id)
    {
        $order = Order::with('details')->findOrFail($id);
        $old = (int) $order->status;
        $new = (int) $request->validated('status');

        DB::transaction(function () use ($order, $old, $new): void {
            if ($old === 2 && $new !== 2) {
                foreach ($order->details as $detail) {
                    Car::query()->whereKey($detail->car_id)->increment('stock', $detail->quantity);
                }
            }

            if ($new === 2 && $old !== 2) {
                foreach ($order->details as $detail) {
                    $car = Car::query()->lockForUpdate()->whereKey($detail->car_id)->first();
                    if (! $car || $car->stock < $detail->quantity) {
                        throw ValidationException::withMessages([
                            'status' => 'Khong du ton kho cho xe #'.$detail->car_id,
                        ]);
                    }
                }
                foreach ($order->details as $detail) {
                    Car::query()->whereKey($detail->car_id)->decrement('stock', $detail->quantity);
                }
            }

            $order->update(['status' => $new]);
        });

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng #'.$id.' thành công!');
    }
}
