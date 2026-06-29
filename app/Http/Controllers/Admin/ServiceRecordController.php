<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\ServiceAppointment;
use App\Models\ServiceRecord;
use App\Models\User;
use App\Models\Warranty;
use App\Support\AfterSalesQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ServiceRecordController extends Controller
{
    public function index(Request $request)
    {
        $filters = AfterSalesQuery::cleanRecordFilters($request->query());
        $query = ServiceRecord::query()
            ->with(['serviceAppointment', 'warranty', 'user', 'car.carModel.brand', 'handledBy']);
        AfterSalesQuery::applyRecordFilters($query, $filters);

        $records = $query
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $statsQuery = ServiceRecord::query();
        AfterSalesQuery::applyRecordFilters($statsQuery, array_merge($filters, ['status' => '']));

        return view('admin.service_records.index', [
            'filters' => $filters,
            'records' => $records,
            'serviceTypeOptions' => ServiceAppointment::serviceTypeOptions(),
            'staff' => $this->staffOptions(),
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'completed' => (clone $statsQuery)->where('status', ServiceRecord::STATUS_COMPLETED)->count(),
                'cancelled' => (clone $statsQuery)->where('status', ServiceRecord::STATUS_CANCELLED)->count(),
                'total_cost' => (float) (clone $statsQuery)->sum('total_cost'),
                'next_due' => ServiceRecord::query()->nextServiceWithin(30)->count(),
            ],
            'statusOptions' => ServiceRecord::statusOptions(),
        ]);
    }

    public function create(Request $request)
    {
        $appointment = null;

        if ($request->integer('appointment_id')) {
            $appointment = ServiceAppointment::query()
                ->with(['serviceRecords', 'warranty', 'user', 'car'])
                ->find($request->integer('appointment_id'));

            if ($appointment && $appointment->serviceRecords->isNotEmpty()) {
                return redirect()
                    ->route('admin.service-records.show', $appointment->serviceRecords->first())
                    ->with('success', 'Lịch hẹn này đã có lịch sử dịch vụ, hệ thống đã mở hồ sơ hiện có.');
            }
        }

        $record = new ServiceRecord([
            'service_appointment_id' => $appointment?->id ?: null,
            'warranty_id' => $appointment?->warranty_id ?: ($request->integer('warranty_id') ?: null),
            'user_id' => $appointment?->user_id ?: ($request->integer('user_id') ?: null),
            'car_id' => $appointment?->car_id ?: ($request->integer('car_id') ?: null),
            'service_type' => $appointment?->service_type ?: ServiceAppointment::TYPE_MAINTENANCE,
            'service_date' => now()->toDateString(),
            'handled_by' => $appointment?->assigned_staff_id ?: $request->user()?->getKey(),
            'status' => ServiceRecord::STATUS_COMPLETED,
        ]);

        if ($record->warranty_id && (!$record->user_id || !$record->car_id)) {
            $warranty = Warranty::query()->find($record->warranty_id);
            $record->user_id = $record->user_id ?: $warranty?->user_id;
            $record->car_id = $record->car_id ?: $warranty?->car_id;
        }

        return view('admin.service_records.form', $this->formData($record, 'create'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatedRecord($request);

        $record = DB::transaction(function () use ($validated) {
            if (!empty($validated['service_appointment_id'])) {
                $existing = ServiceRecord::query()
                    ->where('service_appointment_id', $validated['service_appointment_id'])
                    ->lockForUpdate()
                    ->first();

                if ($existing) {
                    throw ValidationException::withMessages([
                        'service_appointment_id' => 'Lịch hẹn này đã có lịch sử dịch vụ.',
                    ]);
                }
            }

            $record = ServiceRecord::create($this->recordPayload($validated));
            $this->markAppointmentCompleted($record);

            return $record;
        });

        return redirect()
            ->route('admin.service-records.show', $record)
            ->with('success', 'Đã tạo lịch sử dịch vụ ' . $record->record_code . '.');
    }

    public function show(ServiceRecord $serviceRecord)
    {
        $serviceRecord->load([
            'serviceAppointment',
            'warranty',
            'user',
            'car.carModel.brand',
            'handledBy',
            'files.uploadedBy',
        ]);

        return view('admin.service_records.show', [
            'record' => $serviceRecord,
        ]);
    }

    public function edit(ServiceRecord $serviceRecord)
    {
        return view('admin.service_records.form', $this->formData($serviceRecord, 'edit'));
    }

    public function update(Request $request, ServiceRecord $serviceRecord)
    {
        $validated = $this->validatedRecord($request);

        DB::transaction(function () use ($serviceRecord, $validated): void {
            $serviceRecord->update($this->recordPayload($validated));
            $this->markAppointmentCompleted($serviceRecord);
        });

        return redirect()
            ->route('admin.service-records.show', $serviceRecord)
            ->with('success', 'Đã cập nhật lịch sử dịch vụ.');
    }

    public function destroy(ServiceRecord $serviceRecord)
    {
        $serviceRecord->delete();

        return redirect()
            ->route('admin.service-records.index')
            ->with('success', 'Đã xóa lịch sử dịch vụ.');
    }

    private function validatedRecord(Request $request): array
    {
        return $request->validate([
            'service_appointment_id' => 'nullable|integer|exists:service_appointments,id',
            'warranty_id' => 'nullable|integer|exists:warranties,id',
            'user_id' => 'nullable|integer|exists:users,user_id',
            'car_id' => 'nullable|integer|exists:cars,car_id',
            'service_type' => ['required', Rule::in(ServiceAppointment::SERVICE_TYPES)],
            'service_date' => 'required|date',
            'mileage' => 'nullable|integer|min:0|max:2000000',
            'problem_description' => 'nullable|string|max:5000',
            'work_performed' => 'nullable|string|max:5000',
            'parts_replaced' => 'nullable|string|max:5000',
            'labor_cost' => 'nullable|numeric|min:0|max:999999999999',
            'parts_cost' => 'nullable|numeric|min:0|max:999999999999',
            'next_service_date' => 'nullable|date|after_or_equal:service_date',
            'next_service_mileage' => 'nullable|integer|min:0|max:2000000',
            'handled_by' => 'nullable|integer|exists:users,user_id',
            'status' => ['required', Rule::in(ServiceRecord::STATUSES)],
            'note' => 'nullable|string|max:3000',
        ]);
    }

    private function recordPayload(array $validated): array
    {
        if (!empty($validated['service_appointment_id'])) {
            $appointment = ServiceAppointment::query()->find($validated['service_appointment_id']);
            $validated['warranty_id'] = $validated['warranty_id'] ?: $appointment?->warranty_id;
            $validated['user_id'] = $validated['user_id'] ?: $appointment?->user_id;
            $validated['car_id'] = $validated['car_id'] ?: $appointment?->car_id;
            $validated['service_type'] = $validated['service_type'] ?: $appointment?->service_type;
            $validated['handled_by'] = $validated['handled_by'] ?: $appointment?->assigned_staff_id;
        }

        if (!empty($validated['warranty_id'])) {
            $warranty = Warranty::query()->find($validated['warranty_id']);
            $validated['user_id'] = $validated['user_id'] ?: $warranty?->user_id;
            $validated['car_id'] = $validated['car_id'] ?: $warranty?->car_id;
        }

        $validated['labor_cost'] = (float) ($validated['labor_cost'] ?? 0);
        $validated['parts_cost'] = (float) ($validated['parts_cost'] ?? 0);
        $validated['total_cost'] = $validated['labor_cost'] + $validated['parts_cost'];

        return $validated;
    }

    private function markAppointmentCompleted(ServiceRecord $record): void
    {
        if (!$record->service_appointment_id || $record->status !== ServiceRecord::STATUS_COMPLETED) {
            return;
        }

        ServiceAppointment::query()
            ->whereKey($record->service_appointment_id)
            ->update(['status' => ServiceAppointment::STATUS_COMPLETED]);
    }

    private function formData(ServiceRecord $record, string $mode): array
    {
        return [
            'appointments' => ServiceAppointment::query()
                ->with(['user', 'car.carModel.brand', 'warranty'])
                ->orderByDesc('appointment_date')
                ->limit(500)
                ->get(),
            'cars' => Car::query()
                ->with('carModel.brand')
                ->orderBy('name')
                ->get(['car_id', 'car_model_id', 'name', 'vin', 'license_plate']),
            'mode' => $mode,
            'record' => $record,
            'serviceTypeOptions' => ServiceAppointment::serviceTypeOptions(),
            'staff' => $this->staffOptions(),
            'statusOptions' => ServiceRecord::statusOptions(),
            'users' => User::query()
                ->where('role', 'customer')
                ->orWhere('user_id', $record->user_id)
                ->orderBy('name')
                ->get(['user_id', 'name', 'email', 'phone']),
            'warranties' => Warranty::query()
                ->with(['user', 'car.carModel.brand'])
                ->orderByDesc('start_date')
                ->limit(500)
                ->get(),
        ];
    }

    private function staffOptions()
    {
        return User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->orderBy('name')
            ->get(['user_id', 'name', 'email']);
    }
}
