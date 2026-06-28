<?php

namespace App\Http\Controllers;

use App\Exports\OrdersExport;
use App\Models\Car;
use App\Models\Delivery;
use App\Models\DeliveryFile;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Setting;
use App\Models\StockReservation;
use App\Models\User;
use App\Services\StockReservationService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;

class AdminOrderController extends Controller
{
    public function __construct(private readonly StockReservationService $stockReservationService)
    {
    }

    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $orders = $this->ordersQuery($filters)
            ->paginate(15)
            ->withQueryString();
        $statusOptions = Order::statusOptions();
        $depositFilterOptions = $this->depositFilterOptions();
        $sortOptions = $this->sortOptions();
        $orderStats = $this->orderStats($filters);

        return view('admin.orders.index', compact(
            'depositFilterOptions',
            'filters',
            'orderStats',
            'orders',
            'sortOptions',
            'statusOptions'
        ));
    }

    public function create()
    {
        $users = User::query()
            ->where('role', 'customer')
            ->orderBy('name')
            ->get(['user_id', 'name', 'email']);

        $cars = Car::query()
            ->orderBy('name')
            ->get(['car_id', 'name', 'price', 'stock', 'stock_quantity', 'reserved_quantity']);

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
                $this->syncStockReservationForStatus($order, null, $status, $request);

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
        $order = Order::with([
            'user',
            'details.car',
            'statusHistories.user',
            'depositConfirmer',
            'quote',
            'delivery.deliveryStaff',
            'delivery.files.uploadedBy',
        ])
            ->findOrFail($id);

        $delivery = $this->ensureDeliveryForOrder($order);
        $order->setRelation('delivery', $delivery);

        return view('admin.orders.show', [
            'canManageDelivery' => $this->canManageDelivery(request()->user()),
            'delivery' => $delivery,
            'deliveryChecklistOptions' => Delivery::checklistOptions(),
            'deliveryStaffOptions' => $this->deliveryStaffOptions($delivery),
            'deliveryStatusOptions' => Delivery::statusOptions(),
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

        $order = Order::query()
            ->with('delivery')
            ->findOrFail($id);

        if ($this->orderHasDeliveredDelivery($order)) {
            return back()->withErrors([
                'deposit' => 'Xe đã được giao, không thể chỉnh sửa thông tin cọc. Nếu cần điều chỉnh, vui lòng xử lý bằng nghiệp vụ đối soát riêng.',
            ]);
        }

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
                $order = Order::with(['details.car', 'user', 'delivery'])
                    ->lockForUpdate()
                    ->findOrFail($id);

                $statusBefore = $order->status;
                $statusAfter = (int) $validated['status'];

                if ((string) $statusBefore === (string) $statusAfter) {
                    return;
                }

                if ($this->orderHasDeliveredDelivery($order)) {
                    throw ValidationException::withMessages([
                        'status' => 'Xe đã được giao, không thể đổi trạng thái đơn hàng. Nếu cần hoàn kho hoặc hủy sau giao, vui lòng dùng quy trình điều chỉnh/đối soát riêng.',
                    ]);
                }

                if (
                    $statusAfter === Order::STATUS_COMPLETED
                    && !$this->orderHasDeliveredDelivery($order)
                ) {
                    throw ValidationException::withMessages([
                        'status' => 'Vui lòng xác nhận giao xe trước khi hoàn tất đơn hàng.',
                    ]);
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

                $this->syncStockReservationForStatus(
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

    public function updateDelivery(Request $request, Order $order)
    {
        $validated = $request->validate([
            'expected_delivery_date' => 'nullable|date',
            'actual_delivery_date' => 'nullable|date',
            'delivery_location' => 'nullable|string|max:255',
            'delivery_staff_id' => 'nullable|integer|exists:users,user_id',
            'status' => ['required', Rule::in(Delivery::STATUSES)],
            'note' => 'nullable|string|max:2000',
            'checklist_data' => 'nullable|array',
        ]);

        try {
            DB::transaction(function () use ($order, $request, $validated): void {
                $lockedOrder = Order::query()
                    ->with(['details.car', 'user', 'delivery'])
                    ->lockForUpdate()
                    ->findOrFail($order->getKey());

                $delivery = $this->lockedDeliveryForOrder($lockedOrder);
                $payload = $this->deliveryPayload($validated, $request);
                $statusAfter = $payload['status'];

                if (
                    $delivery->status === Delivery::STATUS_DELIVERED
                    && $statusAfter !== Delivery::STATUS_DELIVERED
                ) {
                    throw new InvalidArgumentException(
                        'Xe đã được giao, không thể đổi trạng thái giao xe. Nếu cần hoàn kho, vui lòng dùng chức năng điều chỉnh tồn kho.'
                    );
                }

                $shouldDeductStock = $statusAfter === Delivery::STATUS_DELIVERED
                    && !$delivery->stock_deducted_at;

                if ($shouldDeductStock) {
                    $activeReservation = StockReservation::query()
                        ->where('order_id', $lockedOrder->order_id)
                        ->where('status', StockReservation::STATUS_ACTIVE)
                        ->lockForUpdate()
                        ->first(['id']);

                    if ($activeReservation) {
                        $this->stockReservationService->completeForOrder($lockedOrder, $request->user());
                        $payload['stock_deducted_at'] = now();
                    } else {
                        $completedReservation = StockReservation::query()
                            ->where('order_id', $lockedOrder->order_id)
                            ->where('status', StockReservation::STATUS_COMPLETED)
                            ->lockForUpdate()
                            ->orderByDesc('completed_at')
                            ->first(['completed_at']);

                        if (!$completedReservation) {
                        throw new InvalidArgumentException('Đơn hàng chưa giữ xe, không thể giao xe.');
                        }

                        $payload['stock_deducted_at'] = $completedReservation->completed_at ?: now();
                    }

                    if (!$payload['actual_delivery_date']) {
                        $payload['actual_delivery_date'] = now();
                    }
                } elseif ($delivery->stock_deducted_at) {
                    $payload['stock_deducted_at'] = $delivery->stock_deducted_at;
                    $payload['actual_delivery_date'] = $payload['actual_delivery_date']
                        ?: $delivery->actual_delivery_date;
                }

                $delivery->forceFill($payload)->save();

                if ($shouldDeductStock) {
                    $statusBefore = $lockedOrder->status;

                    if (Order::normalizeStatus($statusBefore) !== Order::STATUS_COMPLETED) {
                        $lockedOrder->forceFill([
                            'status' => Order::STATUS_COMPLETED,
                        ])->save();

                        $lockedOrder->refresh();

                        $this->recordStatusHistory(
                            $lockedOrder,
                            $statusBefore,
                            Order::STATUS_COMPLETED,
                            $request,
                            'Hoàn tất đơn hàng sau khi giao xe.',
                            false
                        );
                    }
                }
            });
        } catch (InvalidArgumentException $e) {
            return back()
                ->withInput()
                ->withErrors(['delivery_status' => $e->getMessage()]);
        }

        return back()->with('success', 'Đã cập nhật thông tin giao xe thành công!');
    }

    public function uploadDeliveryFiles(Request $request, Order $order)
    {
        $request->validate([
            'delivery_files' => 'required|array|max:10',
            'delivery_files.*' => 'required|file|mimes:pdf,jpg,jpeg,png,webp|max:5120',
        ]);

        $delivery = $this->ensureDeliveryForOrder($order);

        foreach ($request->file('delivery_files', []) as $file) {
            $path = $file->store('delivery-files/' . $delivery->id, 'public');

            $delivery->files()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'uploaded_by' => $request->user()?->getKey(),
            ]);
        }

        return back()->with('success', 'Đã tải lên tài liệu giao xe thành công!');
    }

    public function downloadDeliveryFile(DeliveryFile $deliveryFile)
    {
        abort_unless($deliveryFile->delivery, 404);
        abort_unless(Storage::disk('public')->exists($deliveryFile->file_path), 404);

        return Storage::disk('public')->download(
            $deliveryFile->file_path,
            $deliveryFile->file_name
        );
    }

    public function viewDeliveryFile(DeliveryFile $deliveryFile)
    {
        abort_unless($deliveryFile->delivery, 404);
        abort_unless(Storage::disk('public')->exists($deliveryFile->file_path), 404);

        return Storage::disk('public')->response(
            $deliveryFile->file_path,
            $deliveryFile->file_name
        );
    }

    public function deleteDeliveryFile(DeliveryFile $deliveryFile)
    {
        Storage::disk('public')->delete($deliveryFile->file_path);
        $deliveryFile->delete();

        return back()->with('success', 'Đã xóa tài liệu giao xe.');
    }

    private function syncStockReservationForStatus(
        Order $order,
        mixed $statusBefore,
        mixed $statusAfter,
        Request $request
    ): void {
        $before = Order::normalizeStatus($statusBefore);
        $after = Order::normalizeStatus($statusAfter);
        $actor = $request->user();

        if ($after === Order::STATUS_DEPOSITED) {
            $this->stockReservationService->reserveForOrder($order, $actor);

            return;
        }

        if ($after === Order::STATUS_COMPLETED) {
            return;
        }

        if ($after === Order::STATUS_CANCELLED) {
            $this->stockReservationService->releaseForOrder(
                $order,
                'Hủy đơn hàng ' . $order->display_code . '.',
                $actor
            );

            return;
        }

        if ($before === Order::STATUS_DEPOSITED && $after === Order::STATUS_PENDING) {
            $this->stockReservationService->releaseForOrder(
                $order,
                'Đơn hàng ' . $order->display_code . ' chuyển về trạng thái chờ xử lý.',
                $actor
            );
        }
    }

    private function lockedDeliveryForOrder(Order $order): Delivery
    {
        $delivery = Delivery::query()
            ->where('order_id', $order->order_id)
            ->lockForUpdate()
            ->first();

        if ($delivery) {
            return $delivery;
        }

        return Delivery::create($this->newDeliveryAttributes($order));
    }

    private function ensureDeliveryForOrder(Order $order): Delivery
    {
        $order->loadMissing(['details', 'delivery.deliveryStaff', 'delivery.files.uploadedBy']);

        if ($order->delivery) {
            return $order->delivery;
        }

        $delivery = Delivery::query()->firstOrCreate(
            ['order_id' => $order->order_id],
            $this->newDeliveryAttributes($order)
        );

        return $delivery->load(['deliveryStaff', 'files.uploadedBy']);
    }

    private function newDeliveryAttributes(Order $order): array
    {
        $order->loadMissing('details');

        return [
            'order_id' => $order->order_id,
            'user_id' => $order->user_id,
            'car_id' => $order->details->first()?->car_id,
            'status' => Delivery::STATUS_PENDING,
        ];
    }

    private function deliveryPayload(array $validated, Request $request): array
    {
        $expectedDate = $validated['expected_delivery_date'] ?? null;
        $actualDate = $validated['actual_delivery_date'] ?? null;

        return [
            'expected_delivery_date' => $expectedDate ? Carbon::parse($expectedDate) : null,
            'actual_delivery_date' => $actualDate ? Carbon::parse($actualDate) : null,
            'delivery_location' => $validated['delivery_location'] ?? null,
            'delivery_staff_id' => $validated['delivery_staff_id'] ?? null,
            'status' => $validated['status'],
            'note' => $validated['note'] ?? null,
            'checklist_data' => $this->normalizeDeliveryChecklist($request->input('checklist_data', [])),
        ];
    }

    private function normalizeDeliveryChecklist(array $input): array
    {
        $selected = [];

        foreach ($input as $key => $value) {
            if (is_string($key) && array_key_exists($key, Delivery::CHECKLIST_OPTIONS)) {
                $selected[$key] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                continue;
            }

            if (is_string($value) && array_key_exists($value, Delivery::CHECKLIST_OPTIONS)) {
                $selected[$value] = true;
            }
        }

        return collect(Delivery::CHECKLIST_OPTIONS)
            ->keys()
            ->mapWithKeys(fn (string $key): array => [$key => (bool) ($selected[$key] ?? false)])
            ->all();
    }

    private function deliveryStaffOptions(Delivery $delivery)
    {
        return User::query()
            ->where(function (Builder $query) use ($delivery): void {
                $query->whereIn('role', ['admin', 'staff']);

                if ($delivery->delivery_staff_id) {
                    $query->orWhere('user_id', $delivery->delivery_staff_id);
                }
            })
            ->orderBy('name')
            ->get(['user_id', 'name', 'email']);
    }

    private function canManageDelivery(?User $user): bool
    {
        if (!$user) {
            return false;
        }

        try {
            return $user->can('orders.edit') || $user->can('inventory.adjust');
        } catch (\Throwable) {
            return in_array($user->role, ['admin', 'staff'], true);
        }
    }

    private function orderHasDeliveredDelivery(Order $order): bool
    {
        $order->loadMissing('delivery');

        return $order->delivery
            && $order->delivery->status === Delivery::STATUS_DELIVERED
            && $order->delivery->stock_deducted_at;
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
            'deposit_filter' => ['nullable', Rule::in(array_keys($this->depositFilterOptions()))],
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'price_from' => 'nullable|numeric|min:0',
            'price_to' => 'nullable|numeric|min:0',
            'sort' => ['nullable', Rule::in(array_keys($this->sortOptions()))],
        ]);

        if (
            ($filters['price_from'] ?? '') !== ''
            && ($filters['price_to'] ?? '') !== ''
            && (float) $filters['price_from'] > (float) $filters['price_to']
        ) {
            throw ValidationException::withMessages([
                'price_to' => 'Giá đến phải lớn hơn hoặc bằng Giá từ.',
            ]);
        }

        $filters['q'] = trim((string) ($filters['q'] ?? ''));
        $filters['deposit_filter'] = $filters['deposit_filter'] ?? '';
        $filters['sort'] = $filters['sort'] ?? 'latest';

        if (($filters['status'] ?? '') !== '') {
            $filters['status'] = (int) $filters['status'];
        }

        return $filters;
    }

    private function ordersQuery(array $filters): Builder
    {
        $query = Order::query()
            ->with(['user', 'details.car', 'delivery'])
            ->select('orders.*');

        $this->applyOrderFilters($query, $filters);
        $this->applyOrderSorting($query, $filters['sort'] ?? 'latest');

        return $query;
    }

    private function orderStats(array $filters): array
    {
        $query = Order::query();
        $this->applyOrderFilters($query, $filters);

        return [
            'total_orders' => (clone $query)->count(),
            'total_value' => (float) (clone $query)->sum('total_price'),
            'pending' => (clone $query)->whereIn('status', $this->statusFilterValues(Order::STATUS_PENDING))->count(),
            'deposited' => (clone $query)->whereIn('status', $this->statusFilterValues(Order::STATUS_DEPOSITED))->count(),
            'completed' => (clone $query)->whereIn('status', $this->statusFilterValues(Order::STATUS_COMPLETED))->count(),
            'cancelled' => (clone $query)->whereIn('status', $this->statusFilterValues(Order::STATUS_CANCELLED))->count(),
        ];
    }

    private function applyOrderFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? null, function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('order_code', 'like', "%{$search}%")
                        ->orWhere('order_id', 'like', "%{$search}%")
                        ->orWhereHas('user', function (Builder $userQuery) use ($search): void {
                            $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%");
                        })
                        ->orWhereHas('details.car', function (Builder $carQuery) use ($search): void {
                            $carQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when(($filters['status'] ?? '') !== '', function (Builder $query) use ($filters): void {
                $query->whereIn('status', $this->statusFilterValues((int) $filters['status']));
            })
            ->when(($filters['deposit_filter'] ?? '') === 'with_deposit', function (Builder $query): void {
                $query->where('deposit_amount', '>', 0);
            })
            ->when(($filters['deposit_filter'] ?? '') === 'without_deposit', function (Builder $query): void {
                $query->where(function (Builder $inner): void {
                    $inner->whereNull('deposit_amount')
                        ->orWhere('deposit_amount', '<=', 0);
                });
            })
            ->when($filters['date_from'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($filters['date_to'] ?? null, function (Builder $query, string $date): void {
                $query->where('created_at', '<=', Carbon::parse($date)->endOfDay());
            });

        if (($filters['price_from'] ?? '') !== '') {
            $query->where('total_price', '>=', (float) $filters['price_from']);
        }

        if (($filters['price_to'] ?? '') !== '') {
            $query->where('total_price', '<=', (float) $filters['price_to']);
        }
    }

    private function applyOrderSorting(Builder $query, string $sort): void
    {
        match ($sort) {
            'oldest' => $query->orderBy('created_at')->orderBy('order_id'),
            'total_desc' => $query->orderByDesc('total_price')->orderByDesc('created_at')->orderByDesc('order_id'),
            'total_asc' => $query->orderBy('total_price')->orderByDesc('created_at')->orderByDesc('order_id'),
            'deposit_desc' => $query->orderByDesc('deposit_amount')->orderByDesc('created_at')->orderByDesc('order_id'),
            default => $query->orderByDesc('created_at')->orderByDesc('order_id'),
        };
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

    private function depositFilterOptions(): array
    {
        return [
            'with_deposit' => 'Có tiền cọc',
            'without_deposit' => 'Chưa cọc',
        ];
    }

    private function sortOptions(): array
    {
        return [
            'latest' => 'Mới nhất',
            'oldest' => 'Cũ nhất',
            'total_desc' => 'Giá trị cao nhất',
            'total_asc' => 'Giá trị thấp nhất',
            'deposit_desc' => 'Tiền cọc cao nhất',
        ];
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
