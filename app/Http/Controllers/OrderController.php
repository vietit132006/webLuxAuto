<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Setting;
use App\Services\StockReservationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderController extends Controller
{
    public function __construct(private readonly StockReservationService $stockReservationService)
    {
    }

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
        if (!Auth::check()) {
            return redirect()->route('login')
                ->withErrors(['Lỗi' => 'Vui lòng đăng nhập để đặt cọc xe.']);
        }

        $car = Car::findOrFail($car_id);

        if (!$car->isAvailableForSale() || $car->availableStock() <= 0) {
            return back()->withErrors([
                'stock' => 'Xe hiện không còn tồn kho để đặt cọc.',
            ]);
        }

        $depositAmount = $this->defaultDepositAmount();

        try {
            DB::beginTransaction();

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_price' => $car->price,
                'deposit_amount' => $depositAmount,
                'deposit_date' => null,
                'status' => Order::STATUS_PENDING,
            ]);

            OrderDetail::create([
                'order_id' => $order->order_id,
                'car_id' => $car->car_id,
                'quantity' => 1,
                'price' => $car->price,
            ]);

            $this->recordStatusHistory($order, null, Order::STATUS_PENDING, $request, 'Tạo đơn đặt cọc trực tuyến.');

            DB::commit();

            $vnp_TmnCode = env('VNP_TMN_CODE');
            $vnp_HashSecret = env('VNP_HASH_SECRET');
            $vnp_Url = env('VNP_URL');
            $vnp_Returnurl = env('VNP_RETURN_URL');

            $vnp_TxnRef = $order->order_id;
            $vnp_OrderInfo = 'Thanh toan dat coc xe ' . $car->name;
            $vnp_OrderType = 'billpayment';
            $vnp_Amount = $depositAmount * 100;
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
            $paymentUrl = $vnp_Url . '?' . $query . '&vnp_SecureHash=' . $vnpSecureHash;

            return redirect($paymentUrl);
        } catch (\Exception $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            return back()->withErrors([
                'Lỗi hệ thống' => 'Không thể tạo đơn: ' . $e->getMessage(),
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
                $hashData .= '&' . urlencode($key) . '=' . urlencode($value);
            } else {
                $hashData .= urlencode($key) . '=' . urlencode($value);
                $i = 1;
            }
        }

        $vnp_HashSecret = env('VNP_HASH_SECRET');
        $secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

        if ($secureHash != $vnp_SecureHash) {
            return redirect()->route('order.history')
                ->withErrors(['Lỗi' => 'Chữ ký số không hợp lệ, phát hiện nghi vấn giả mạo!']);
        }

        $order = Order::find($request->vnp_TxnRef);

        if ($request->vnp_ResponseCode == '00') {
            if ($order && (string) $order->status === (string) Order::STATUS_PENDING) {
                try {
                    DB::transaction(function () use ($order, $request) {
                        $statusBefore = $order->status;
                        $updates = [
                            'status' => Order::STATUS_DEPOSITED,
                            'deposit_date' => $order->deposit_date ?: now(),
                            'deposit_method' => $order->deposit_method ?: Order::DEPOSIT_METHOD_CARD,
                            'deposit_reference' => $order->deposit_reference
                                ?: ($request->vnp_TransactionNo ?? $request->vnp_BankTranNo ?? $request->vnp_TxnRef),
                            'deposit_note' => $order->deposit_note ?: 'Thanh toán cọc VNPay thành công.',
                        ];

                        if ((float) ($order->deposit_amount ?? 0) <= 0) {
                            $updates['deposit_amount'] = $this->defaultDepositAmount();
                        }

                        $order->update($updates);
                        $this->recordStatusHistory(
                            $order,
                            $statusBefore,
                            Order::STATUS_DEPOSITED,
                            $request,
                            'Thanh toán cọc VNPay thành công.'
                        );

                        $this->stockReservationService->reserveForOrder($order, $request->user());
                    });
                } catch (InvalidArgumentException $e) {
                    return redirect()->route('order.history')->withErrors(['stock' => $e->getMessage()]);
                }
            }

            return redirect()->route('order.history')
                ->with('success', 'Tuyệt vời! Bạn đã thanh toán cọc thành công chiếc xe.');
        }

        if ($order && (string) $order->status === (string) Order::STATUS_PENDING) {
            DB::transaction(function () use ($order, $request) {
                $statusBefore = $order->status;
                $order->update(['status' => Order::STATUS_CANCELLED]);
                $this->recordStatusHistory(
                    $order,
                    $statusBefore,
                    Order::STATUS_CANCELLED,
                    $request,
                    'Giao dịch VNPay thất bại hoặc bị hủy.'
                );
            });
        }

        return redirect()->route('order.history')
            ->withErrors(['Lỗi' => 'Giao dịch bị hủy hoặc thanh toán thất bại.']);
    }

    private function recordStatusHistory(
        Order $order,
        mixed $oldStatus,
        mixed $newStatus,
        Request $request,
        ?string $note = null
    ): void {
        $order->statusHistories()->create([
            'old_status' => $oldStatus === null ? null : (string) $oldStatus,
            'new_status' => (string) $newStatus,
            'user_id' => $request->user()?->getKey() ?? $order->user_id,
            'note' => $this->automaticStatusNote($order, $newStatus) ?: $note,
        ]);
    }

    private function automaticStatusNote(Order $order, mixed $newStatus): ?string
    {
        if (Order::normalizeStatus($newStatus) !== Order::STATUS_DEPOSITED) {
            return null;
        }

        return 'Khách đặt cọc ' . number_format((float) ($order->deposit_amount ?? 0), 0, ',', '.') . ' VNĐ';
    }

    private function defaultDepositAmount(): float
    {
        return (float) (Setting::query()
            ->where('group', 'car')
            ->where('key', 'default_deposit_amount')
            ->value('value') ?: 20000000);
    }
}
