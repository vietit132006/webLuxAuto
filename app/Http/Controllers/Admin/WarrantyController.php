<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WarrantiesExport;
use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\ServiceAppointment;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Warranty;
use App\Support\AfterSalesQuery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class WarrantyController extends Controller
{
    public function index(Request $request)
    {
        $filters = AfterSalesQuery::cleanWarrantyFilters($request->query());
        $query = Warranty::query()
            ->with(['user', 'car.carModel.brand', 'order', 'delivery'])
            ->withCount(['serviceAppointments', 'serviceRecords']);
        AfterSalesQuery::applyWarrantyFilters($query, $filters);

        $warranties = $query
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statsQuery = Warranty::query();
        AfterSalesQuery::applyWarrantyFilters($statsQuery, array_merge($filters, ['status' => '', 'expiring' => '']));

        return view('admin.warranties.index', [
            'filters' => $filters,
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'active' => (clone $statsQuery)->where('status', Warranty::STATUS_ACTIVE)->count(),
                'expired' => (clone $statsQuery)->where('status', Warranty::STATUS_EXPIRED)->count(),
                'void' => (clone $statsQuery)->where('status', Warranty::STATUS_VOID)->count(),
                'expiring' => (clone $statsQuery)->expiringWithin(30)->count(),
            ],
            'statusOptions' => Warranty::statusOptions(),
            'warranties' => $warranties,
        ]);
    }

    public function create(Request $request)
    {
        $warranty = new Warranty([
            'order_id' => $request->integer('order_id') ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'car_id' => $request->integer('car_id') ?: null,
            'start_date' => now(),
            'end_date' => now()->addMonthsNoOverflow(36),
            'warranty_months' => 36,
            'status' => Warranty::STATUS_ACTIVE,
        ]);

        if ($request->integer('order_id')) {
            $order = Order::query()
                ->with(['delivery', 'details.car'])
                ->find($request->integer('order_id'));

            if ($order) {
                $warranty->user_id = $warranty->user_id ?: $order->user_id;
                $warranty->car_id = $warranty->car_id ?: ($order->delivery?->car_id ?: $order->details->first()?->car_id);
            }
        }

        return view('admin.warranties.form', $this->formData($warranty, 'create'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedWarranty($request);

        try {
            $warranty = DB::transaction(fn () => $this->persistWarranty(new Warranty(), $validated));
        } catch (ValidationException $e) {
            throw $e;
        }

        return redirect()
            ->route('admin.warranties.show', $warranty)
            ->with('success', 'Đã tạo hồ sơ bảo hành ' . $warranty->warranty_code . '.');
    }

    public function show(Warranty $warranty)
    {
        $warranty->load([
            'order.user',
            'delivery.deliveryStaff',
            'user',
            'car.carModel.brand',
            'serviceAppointments.assignedStaff',
            'serviceAppointments.files.uploadedBy',
            'serviceRecords.serviceAppointment',
            'serviceRecords.handledBy',
            'serviceRecords.files.uploadedBy',
        ]);

        return view('admin.warranties.show', [
            'appointmentStatusOptions' => ServiceAppointment::statusOptions(),
            'serviceTypeOptions' => ServiceAppointment::serviceTypeOptions(),
            'warranty' => $warranty,
        ]);
    }

    public function edit(Warranty $warranty)
    {
        return view('admin.warranties.form', $this->formData($warranty, 'edit'));
    }

    public function update(Request $request, Warranty $warranty)
    {
        $validated = $this->validatedWarranty($request, $warranty);

        DB::transaction(fn () => $this->persistWarranty($warranty, $validated));

        return redirect()
            ->route('admin.warranties.show', $warranty)
            ->with('success', 'Đã cập nhật hồ sơ bảo hành.');
    }

    public function destroy(Warranty $warranty)
    {
        $warranty->delete();

        return redirect()
            ->route('admin.warranties.index')
            ->with('success', 'Đã xóa hồ sơ bảo hành.');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new WarrantiesExport(AfterSalesQuery::cleanWarrantyFilters($request->query())),
            'luxauto-warranties-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    private function validatedWarranty(Request $request, ?Warranty $warranty = null): array
    {
        return $request->validate([
            'order_id' => [
                'required',
                'integer',
                'exists:orders,order_id',
                Rule::unique('warranties', 'order_id')->ignore($warranty?->id),
            ],
            'delivery_id' => 'nullable|integer|exists:deliveries,id',
            'user_id' => 'nullable|integer|exists:users,user_id',
            'car_id' => 'nullable|integer|exists:cars,car_id',
            'vin' => 'nullable|string|max:255',
            'license_plate' => 'nullable|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'warranty_months' => 'required|integer|min:1|max:120',
            'mileage_limit' => 'nullable|integer|min:0|max:2000000',
            'status' => ['required', Rule::in(Warranty::STATUSES)],
            'note' => 'nullable|string|max:2000',
        ], [], [
            'order_id' => 'đơn hàng',
            'start_date' => 'ngày bắt đầu',
            'end_date' => 'ngày kết thúc',
            'warranty_months' => 'số tháng bảo hành',
            'status' => 'trạng thái',
        ]);
    }

    private function persistWarranty(Warranty $warranty, array $validated): Warranty
    {
        $order = Order::query()
            ->with(['delivery', 'details.car'])
            ->lockForUpdate()
            ->findOrFail($validated['order_id']);

        $delivery = !empty($validated['delivery_id'])
            ? Delivery::query()->lockForUpdate()->find($validated['delivery_id'])
            : $order->delivery;

        if ($delivery && (int) $delivery->order_id !== (int) $order->order_id) {
            throw ValidationException::withMessages([
                'delivery_id' => 'Lịch giao xe không thuộc đơn hàng đã chọn.',
            ]);
        }

        $car = !empty($validated['car_id'])
            ? Car::query()->find($validated['car_id'])
            : ($delivery?->car ?: $order->details->first()?->car);

        $startDate = Carbon::parse($validated['start_date']);
        $warrantyMonths = (int) $validated['warranty_months'];
        $endDate = $startDate->copy()->addMonthsNoOverflow($warrantyMonths);

        $warranty->fill([
            'order_id' => $order->order_id,
            'delivery_id' => $delivery?->id,
            'user_id' => $validated['user_id'] ?: $order->user_id,
            'car_id' => $car?->car_id,
            'vin' => $validated['vin'] ?: $car?->vin,
            'license_plate' => $validated['license_plate'] ?: $car?->license_plate,
            'start_date' => $startDate->toDateString(),
            'end_date' => $endDate->toDateString(),
            'warranty_months' => $warrantyMonths,
            'mileage_limit' => $validated['mileage_limit'] ?? null,
            'status' => $validated['status'],
            'note' => $validated['note'] ?? null,
        ]);
        $warranty->save();

        return $warranty;
    }

    private function formData(Warranty $warranty, string $mode): array
    {
        return [
            'cars' => Car::query()
                ->with('carModel.brand')
                ->orderBy('name')
                ->get(['car_id', 'car_model_id', 'name', 'vin', 'license_plate']),
            'mode' => $mode,
            'orders' => $this->ordersForForm($warranty),
            'statusOptions' => Warranty::statusOptions(),
            'users' => User::query()
                ->where('role', 'customer')
                ->orWhere('user_id', $warranty->user_id)
                ->orderBy('name')
                ->get(['user_id', 'name', 'email', 'phone']),
            'warranty' => $warranty,
        ];
    }

    private function ordersForForm(Warranty $warranty)
    {
        return Order::query()
            ->with(['user', 'delivery', 'details.car'])
            ->where(function (Builder $query) use ($warranty): void {
                $query->whereDoesntHave('warranty');

                if ($warranty->exists) {
                    $query->orWhere('order_id', $warranty->order_id);
                }
            })
            ->where(function (Builder $query): void {
                $query->whereHas('delivery', fn (Builder $deliveryQuery) => $deliveryQuery->where('status', Delivery::STATUS_DELIVERED))
                    ->orWhereIn('status', [Order::STATUS_COMPLETED, (string) Order::STATUS_COMPLETED, 'completed', 'complete']);
            })
            ->orderByDesc('created_at')
            ->limit(500)
            ->get();
    }
}
