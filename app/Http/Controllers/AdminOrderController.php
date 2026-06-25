<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use App\Models\Car;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Setting;
use App\Models\User;
use App\Services\StockMovementService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

class AdminOrderController extends Controller
{
    public function __construct(private readonly StockMovementService $stockMovementService)
    {
    }

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $orders = $this->ordersQuery($filters)
            ->paginate(15)
            ->withQueryString();
        $statusOptions = Order::statusOptions();

        return view('admin.orders.index', compact('orders', 'statusOptions', 'filters'));
    }

    public function create()
    {
        $users = User::query()
            ->where('role', 'customer')
            ->orderBy('name')
            ->get(['user_id', 'name', 'email']);

        $cars = Car::query()
            ->orderBy('name')
            ->get(['car_id', 'name', 'price', 'stock', 'stock_quantity']);

        return view('admin.orders.create', [
            'cars' => $cars,
            'defaultDepositAmount' => $this->defaultDepositAmount(),
            'depositMethodOptions' => Order::depositMethodOptions(),
            'statusOptions' => Order::statusOptions(),
            'users' => $users,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,user_id',
            'car_id' => 'required|integer|exists:cars,car_id',
            'status' => 'required|integer|in:0,1,2,3',
            'deposit_amount' => 'nullable|numeric|min:0',
            'deposit_date' => 'nullable|date',
            'deposit_method' => ['nullable', Rule::in(array_keys(Order::depositMethodOptions()))],
            'deposit_reference' => 'nullable|string|max:255',
            'deposit_note' => 'nullable|string|max:1000',
            'note' => 'nullable|string|max:1000',
        ]);

        try {
            $order = DB::transaction(function () use ($request, $validated) {
                $status = (int) $validated['status'];
                if ($status === Order::STATUS_DEPOSITED) {
                    $validated = array_merge($validated, $this->validatedRequiredDeposit($request));
                }

                $depositAmount = (float) ($validated['deposit_amount'] ?? $this->defaultDepositAmount());
                $depositDate = $validated['deposit_date'] ?? null;

                if ($status === Order::STATUS_COMPLETED && !$depositDate) {
                    $depositDate = now();
                }

                $car = Car::query()
                    ->lockForUpdate()
                    ->findOrFail($validated['car_id']);

                $order = Order::create([
                    'user_id' => $validated['user_id'],
                    'total_price' => $car->price,
                    'deposit_amount' => $depositAmount,
                    'deposit_date' => $depositDate ? Carbon::parse($depositDate) : null,
                    'deposit_method' => $validated['deposit_method'] ?? null,
                    'deposit_reference' => $validated['deposit_reference'] ?? null,
                    'deposit_note' => $validated['deposit_note'] ?? null,
                    'deposit_confirmed_by' => $status === Order::STATUS_DEPOSITED
                        ? $request->user()?->getKey()
                        : null,
                    'status' => $status,
                ]);

                OrderDetail::create([
                    'order_id' => $order->order_id,
                    'car_id' => $car->car_id,
                    'quantity' => 1,
                    'price' => $car->price,
                ]);

                $order->load(['details.car', 'user']);
                $this->recordStatusHistory($order, null, $status, $request, 'Tạo đơn hàng từ trang quản trị.');
                $this->stockMovementService->recordOrderStatusChange($order, null, $status, $request);

                return $order;
            });
        } catch (InvalidArgumentException $e) {
            return back()->withInput()->withErrors(['stock' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.orders.show', $order->order_id)
            ->with('success', 'Đã tạo đơn hàng ' . $order->display_code . ' thành công!');
    }

    public function show($id)
    {
        $order = Order::with(['user', 'details.car', 'statusHistories.user', 'depositConfirmer'])
            ->findOrFail($id);

        return view('admin.orders.show', [
            'order' => $order,
            'depositMethodOptions' => Order::depositMethodOptions(),
            'statusOptions' => Order::statusOptions(),
        ]);
    }

    public function updateDeposit(Request $request, $id)
    {
        $validated = $request->validate([
            'deposit_amount' => 'required|numeric|gt:0',
            'deposit_date' => 'required|date',
            'deposit_method' => ['required', Rule::in(array_keys(Order::depositMethodOptions()))],
            'deposit_reference' => 'nullable|string|max:255',
            'deposit_note' => 'nullable|string|max:1000',
        ]);

        $order = Order::query()->findOrFail($id);
        $order->update($this->depositPayload($validated, $request));

        return back()->with('success', 'Đã cập nhật thông tin đặt cọc thành công!');
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $filename = 'orders_' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new OrdersExport($filters), $filename);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|integer|in:0,1,2,3',
        ]);

        try {
            DB::transaction(function () use ($id, $request, $validated) {
                $order = Order::with(['details.car', 'user'])
                    ->lockForUpdate()
                    ->findOrFail($id);

                $statusBefore = $order->status;
                $statusAfter = (int) $validated['status'];

                if ((string) $statusBefore === (string) $statusAfter) {
                    return;
                }

                $updates = ['status' => $statusAfter];

                if ($statusAfter === Order::STATUS_DEPOSITED) {
                    if (!$this->hasCompleteDepositInfo($order)) {
                        $updates = array_merge(
                            $updates,
                            $this->depositPayload($this->validatedRequiredDeposit($request), $request)
                        );
                    } elseif (!$order->deposit_confirmed_by) {
                        $updates['deposit_confirmed_by'] = $request->user()?->getKey();
                    }
                } elseif ($statusAfter === Order::STATUS_COMPLETED) {
                    if ((float) ($order->deposit_amount ?? 0) <= 0) {
                        $updates['deposit_amount'] = $this->defaultDepositAmount();
                    }

                    if (!$order->deposit_date) {
                        $updates['deposit_date'] = now();
                    }
                }

                $order->update($updates);
                $order->refresh();

                $this->recordStatusHistory(
                    $order,
                    $statusBefore,
                    $statusAfter,
                    $request,
                    null,
                    false
                );

                $this->stockMovementService->recordOrderStatusChange(
                    $order,
                    $statusBefore,
                    $statusAfter,
                    $request
                );
            });
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['stock' => $e->getMessage()]);
        }

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng thành công!');
    }

    private function validatedRequiredDeposit(Request $request): array
    {
        return $request->validate([
            'deposit_amount' => 'required|numeric|gt:0',
            'deposit_date' => 'required|date',
            'deposit_method' => ['required', Rule::in(array_keys(Order::depositMethodOptions()))],
            'deposit_reference' => 'nullable|string|max:255',
            'deposit_note' => 'required|string|max:1000',
        ], [], [
            'deposit_amount' => 'tiền cọc',
            'deposit_date' => 'ngày cọc',
            'deposit_method' => 'phương thức thanh toán',
            'deposit_reference' => 'mã giao dịch',
            'deposit_note' => 'ghi chú đặt cọc',
        ]);
    }

    private function depositPayload(array $validated, Request $request): array
    {
        return [
            'deposit_amount' => (float) $validated['deposit_amount'],
            'deposit_date' => Carbon::parse($validated['deposit_date']),
            'deposit_method' => $validated['deposit_method'],
            'deposit_reference' => $validated['deposit_reference'] ?? null,
            'deposit_note' => $validated['deposit_note'] ?? null,
            'deposit_confirmed_by' => $request->user()?->getKey(),
        ];
    }

    private function hasCompleteDepositInfo(Order $order): bool
    {
        return (float) ($order->deposit_amount ?? 0) > 0
            && $order->deposit_date
            && filled($order->deposit_method)
            && filled($order->deposit_note);
    }

    private function validatedFilters(Request $request): array
    {
        $filters = $request->validate([
            'q' => 'nullable|string|max:255',
            'status' => 'nullable|integer|in:0,1,2,3',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $filters['q'] = trim((string) ($filters['q'] ?? ''));

        if (($filters['status'] ?? '') !== '') {
            $filters['status'] = (int) $filters['status'];
        }

        return $filters;
    }

    private function ordersQuery(array $filters): Builder
    {
        return Order::query()
            ->with(['user', 'details.car'])
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('order_code', 'like', "%{$search}%")
                        ->orWhere('order_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                });
            })
            ->when(($filters['status'] ?? '') !== '', function (Builder $query) use ($filters): void {
                $query->whereIn('status', $this->statusFilterValues((int) $filters['status']));
            })
            ->when($filters['date_from'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($filters['date_to'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '<=', Carbon::parse($date)->endOfDay());
            })
            ->orderByDesc('created_at')
            ->orderByDesc('order_id');
    }

    private function statusFilterValues(int $status): array
    {
        return match ($status) {
            Order::STATUS_PENDING => [0, '0', 'pending'],
            Order::STATUS_DEPOSITED => [1, '1', 'deposit', 'deposited'],
            Order::STATUS_COMPLETED => [2, '2', 'complete', 'completed', 'done'],
            Order::STATUS_CANCELLED => [3, '3', 'cancel', 'canceled', 'cancelled'],
            default => [$status, (string) $status],
        };
    }

    private function recordStatusHistory(
        Order $order,
        mixed $oldStatus,
        mixed $newStatus,
        Request $request,
        ?string $fallbackNote = null,
        bool $useRequestNote = true
    ): void {
        $order->statusHistories()->create([
            'old_status' => $oldStatus === null ? null : (string) $oldStatus,
            'new_status' => (string) $newStatus,
            'user_id' => $request->user()?->getKey(),
            'note' => $this->automaticStatusNote($order, $newStatus)
                ?: (($useRequestNote ? $request->input('note') : null) ?: $fallbackNote),
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
