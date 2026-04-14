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
    // 1. Xem lịch sử đơn hàng của khách
    public function history()
    {
        // Lấy danh sách đơn hàng của người đang đăng nhập
        $orders = Order::with('details.car')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.orders.history', compact('orders'));
    }

    // 2. Xử lý đặt cọc và đẩy sang cổng VNPay
    public function processDeposit(Request $request, $car_id)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->withErrors(['Lỗi' => 'Vui lòng đăng nhập để đặt cọc xe.']);
        }

        $car = Car::findOrFail($car_id);
        $deposit_amount = 20000000; // 20 triệu

        try {
            DB::beginTransaction();

            // 1. Tạo đơn hàng
            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $car->price,
                'status' => 0
            ]);

            // 2. Chi tiết đơn
            OrderDetail::create([
                'order_id' => $order->order_id,
                'car_id' => $car->car_id,
                'quantity' => 1,
                'price' => $car->price
            ]);

            DB::commit();

            // =========================
            // VNPAY CONFIG
            // =========================
            $vnp_TmnCode = env('VNP_TMN_CODE');
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_Url = env('VNP_URL');
            $vnp_Returnurl = env('VNP_RETURN_URL');

            $vnp_TxnRef = $order->order_id;
            $vnp_OrderInfo = "Thanh toan dat coc xe " . $car->name;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $deposit_amount * 100;
            $vnp_Locale = 'vn';
            $vnp_IpAddr = $request->ip(); // ✅ FIX

            $inputData = [
                "vnp_Version" => "2.1.0",
                "vnp_TmnCode" => $vnp_TmnCode,
                "vnp_Amount" => $vnp_Amount,
                "vnp_Command" => "pay",
                "vnp_CreateDate" => date('YmdHis'),
                "vnp_CurrCode" => "VND",
                "vnp_IpAddr" => $vnp_IpAddr,
                "vnp_Locale" => $vnp_Locale,
                "vnp_OrderInfo" => $vnp_OrderInfo,
                "vnp_OrderType" => $vnp_OrderType,
                "vnp_ReturnUrl" => $vnp_Returnurl,
                "vnp_TxnRef" => $vnp_TxnRef,
            ];

            ksort($inputData);

            $query = http_build_query($inputData);
            $hashdata = urldecode($query);

            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

            $paymentUrl = $vnp_Url . "?" . $query . '&vnp_SecureHash=' . $vnpSecureHash;

            // 👉 Redirect sang VNPAY
            return redirect($paymentUrl);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors([
                'Lỗi hệ thống' => 'Không thể tạo đơn: ' . $e->getMessage()
            ]);
        }
    }

    // 3. Hứng kết quả trả về từ VNPay
    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = array();
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == "vnp_") {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = "";
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
            } else {
                $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
                $i = 1;
            }
        }

        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        // Kiểm tra mã bảo mật xem có đúng VNPay gửi không
        if ($secureHash == $vnp_SecureHash) {
            $order_id = $request->vnp_TxnRef;
            $order = Order::find($order_id);

            if ($request->vnp_ResponseCode == '00') {
                // MÃ 00 LÀ THÀNH CÔNG -> Đổi trạng thái đơn hàng thành 1 (Đã cọc)
                if ($order && $order->status == 0) {
                    $order->update(['status' => 1]);
                }
                return redirect()->route('order.history')->with('success', 'Tuyệt vời! Bạn đã thanh toán cọc thành công chiếc xe.');
            } else {
                // THANH TOÁN THẤT BẠI / HỦY -> Đổi thành 3 (Hủy)
                if ($order && $order->status == 0) {
                    $order->update(['status' => 3]);
                }
                return redirect()->route('order.history')->withErrors(['Lỗi' => 'Giao dịch bị hủy hoặc thanh toán thất bại.']);
            }
        } else {
            return redirect()->route('order.history')->withErrors(['Lỗi' => 'Chữ ký số không hợp lệ, phát hiện nghi vấn giả mạo!']);
        }
    }
}
