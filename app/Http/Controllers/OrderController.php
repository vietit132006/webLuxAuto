<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function history()
    {
        $orders = Order::with('details.car')
            ->where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('client.orders.history', compact('orders'));
    }

    public function processDeposit(Request $request, $car_id)
    {
        if (! Auth::check()) {
            return redirect()->route('login')
                ->withErrors(['error' => 'Vui long dang nhap de dat coc xe.']);
        }

        $car = Car::findOrFail($car_id);

        if ($car->stock < 1) {
            return back()->withErrors([
                'error' => 'Xe nay hien khong con trong kho.',
            ]);
        }

        $deposit_amount = 20000000;
        $unitPrice = $car->final_price_float;

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $unitPrice,
                'status' => 0,
            ]);

            OrderDetail::create([
                'order_id' => $order->order_id,
                'car_id' => $car->car_id,
                'quantity' => 1,
                'price' => $unitPrice,
            ]);

            DB::commit();

            $vnp_TmnCode = env('VNP_TMN_CODE');
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_Url = env('VNP_URL');
            $vnp_Returnurl = env('VNP_RETURN_URL');

            $vnp_TxnRef = $order->order_id;
            $vnp_OrderInfo = 'Thanh toan dat coc xe '.$car->name;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $deposit_amount * 100;
            $vnp_Locale = 'vn';
            $vnp_IpAddr = $request->ip();

            $inputData = [
                'vnp_Version' => '2.1.0',
                'vnp_TmnCode' => $vnp_TmnCode,
                'vnp_Amount' => $vnp_Amount,
                'vnp_Command' => 'pay',
                'vnp_CreateDate' => date('YmdHis'),
                'vnp_CurrCode' => 'VND',
                'vnp_IpAddr' => $vnp_IpAddr,
                'vnp_Locale' => $vnp_Locale,
                'vnp_OrderInfo' => $vnp_OrderInfo,
                'vnp_OrderType' => $vnp_OrderType,
                'vnp_ReturnUrl' => $vnp_Returnurl,
                'vnp_TxnRef' => $vnp_TxnRef,
            ];

            ksort($inputData);

            $query = http_build_query($inputData);
            $hashdata = urldecode($query);

            $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);

            $paymentUrl = $vnp_Url.'?'.$query.'&vnp_SecureHash='.$vnpSecureHash;

            return redirect($paymentUrl);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->withErrors([
                'error' => 'Khong the tao don: '.$e->getMessage(),
            ]);
        }
    }

    public function vnpayReturn(Request $request)
    {
        $vnp_SecureHash = $request->vnp_SecureHash;
        $inputData = [];
        foreach ($request->all() as $key => $value) {
            if (substr($key, 0, 4) == 'vnp_') {
                $inputData[$key] = $value;
            }
        }

        unset($inputData['vnp_SecureHash']);
        ksort($inputData);
        $i = 0;
        $hashData = '';
        foreach ($inputData as $key => $value) {
            if ($i == 1) {
                $hashData = $hashData.'&'.urlencode($key).'='.urlencode($value);
            } else {
                $hashData = $hashData.urlencode($key).'='.urlencode($value);
                $i = 1;
            }
        }

        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash == $vnp_SecureHash) {
            $order_id = $request->vnp_TxnRef;
            $order = Order::find($order_id);

            if ($request->vnp_ResponseCode == '00') {
                if ($order && $order->status == 0) {
                    $order->update(['status' => 1]);
                }

                return redirect()->route('order.history')->with('success', 'Thanh toan coc thanh cong.');
            } else {
                if ($order && $order->status == 0) {
                    $order->update(['status' => 3]);
                }

                return redirect()->route('order.history')->withErrors(['error' => 'Giao dich bi huy hoac that bai.']);
            }
        } else {
            return redirect()->route('order.history')->withErrors(['error' => 'Chu ky so khong hop le.']);
        }
    }
}
