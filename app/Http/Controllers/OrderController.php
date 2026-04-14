<?php
namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function processDeposit(Request $request, $car_id)
    {
        // Yêu cầu đăng nhập mới được đặt cọc
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['Lỗi' => 'Vui lòng đăng nhập để đặt cọc xe.']);
        }

        $car = Car::findOrFail($car_id);

        try {
            DB::beginTransaction();

            // 1. Tạo Đơn hàng tổng (Bảng orders)
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $car->price, // Tạm lưu giá xe lúc đặt
                'status' => 0 // 0 = Chờ thanh toán cọc
            ]);

            // 2. Tạo Chi tiết đơn hàng (Bảng order_details)
            OrderDetail::create([
                'order_id' => $order->order_id, // Lấy ID của đơn hàng vừa tạo ở trên
                'car_id' => $car->car_id,
                'quantity' => 1,
                'price' => $car->price
            ]);

            DB::commit();

            // Tạm thời trả về trang danh sách xe và báo thành công
            // (Sau này chúng ta sẽ chuyển hướng sang cổng VNPay ở đây)
            return redirect()->route('cars.index')->with('success', 'Tạo đơn đặt cọc thành công! Chờ nhân viên liên hệ.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['Lỗi hệ thống' => 'Không thể tạo đơn hàng lúc này: ' . $e->getMessage()]);
        }
    }
}
