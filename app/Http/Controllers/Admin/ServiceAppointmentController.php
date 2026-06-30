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
use Illuminate\Validation\Rule;

class ServiceAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $filters = AfterSalesQuery::cleanAppointmentFilters($request->query());
        $query = ServiceAppointment::query()
            ->with(['user', 'car.carModel.brand', 'warranty', 'assignedStaff'])
            ->withCount('serviceRecords');
        AfterSalesQuery::applyAppointmentFilters($query, $filters);

        $appointments = $query
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(15)
            ->withQueryString();

        $statsQuery = ServiceAppointment::query();
        AfterSalesQuery::applyAppointmentFilters($statsQuery, array_merge($filters, ['status' => '']));

        return view('admin.service_appointments.index', [
            'appointments' => $appointments,
            'filters' => $filters,
            'reminders' => [
                'today' => ServiceAppointment::query()->pendingOrConfirmedToday()->count(),
                'upcoming' => ServiceAppointment::query()
                    ->whereIn('status', [ServiceAppointment::STATUS_PENDING, ServiceAppointment::STATUS_CONFIRMED])
                    ->whereBetween('appointment_date', [now()->addDay()->toDateString(), now()->addDays(30)->toDateString()])
                    ->count(),
                'next_services' => ServiceRecord::query()->nextServiceWithin(30)->count(),
                'expiring_warranties' => Warranty::query()->expiringWithin(30)->count(),
            ],
            'serviceTypeOptions' => ServiceAppointment::serviceTypeOptions(),
            'staff' => $this->staffOptions(),
            'stats' => [
                'total' => (clone $statsQuery)->count(),
                'pending' => (clone $statsQuery)->where('status', ServiceAppointment::STATUS_PENDING)->count(),
                'confirmed' => (clone $statsQuery)->where('status', ServiceAppointment::STATUS_CONFIRMED)->count(),
                'completed' => (clone $statsQuery)->where('status', ServiceAppointment::STATUS_COMPLETED)->count(),
                'cancelled' => (clone $statsQuery)->where('status', ServiceAppointment::STATUS_CANCELLED)->count(),
            ],
            'statusOptions' => ServiceAppointment::statusOptions(),
        ]);
    }

    public function create(Request $request)
    {
        $appointment = new ServiceAppointment([
            'warranty_id' => $request->integer('warranty_id') ?: null,
            'user_id' => $request->integer('user_id') ?: null,
            'car_id' => $request->integer('car_id') ?: null,
            'service_type' => $request->input('service_type', ServiceAppointment::TYPE_MAINTENANCE),
            'appointment_date' => now()->toDateString(),
            'status' => ServiceAppointment::STATUS_PENDING,
        ]);

        if ($appointment->warranty_id) {
            $warranty = Warranty::query()->find($appointment->warranty_id);
            $appointment->user_id = $appointment->user_id ?: $warranty?->user_id;
            $appointment->car_id = $appointment->car_id ?: $warranty?->car_id;
        }

        return view('admin.service_appointments.form', $this->formData($appointment, 'create'));
    }

    public function store(Request $request)
    {
        $appointment = ServiceAppointment::create($this->appointmentPayload($this->validatedAppointment($request)));

        app(\App\Services\AdminNotificationService::class)->createOnce(
            'services',
            $appointment->appointment_date?->isToday() ? 'service_today' : 'service_created',
            $appointment->appointment_date?->isToday()
                ? 'Lich dich vu hom nay'
                : 'Lich dich vu moi ' . $appointment->appointment_code,
            'Lich dich vu can CSKH theo doi.',
            route('admin.service-appointments.show', $appointment, false),
            ['service_appointment_id' => $appointment->id, 'appointment_date' => $appointment->appointment_date?->toDateString()],
            $appointment->appointment_date?->isToday()
                ? \App\Models\AdminNotification::PRIORITY_HIGH
                : \App\Models\AdminNotification::PRIORITY_NORMAL,
            $request->user(),
            $appointment->appointment_date?->isToday() ? 20 : null
        );

        return redirect()
            ->route('admin.service-appointments.show', $appointment)
            ->with('success', 'Đã tạo lịch dịch vụ ' . $appointment->appointment_code . '.');
    }

    public function show(ServiceAppointment $serviceAppointment)
    {
        $serviceAppointment->load([
            'user',
            'car.carModel.brand',
            'warranty',
            'assignedStaff',
            'serviceRecords.handledBy',
            'files.uploadedBy',
        ]);

        return view('admin.service_appointments.show', [
            'appointment' => $serviceAppointment,
        ]);
    }

    public function edit(ServiceAppointment $serviceAppointment)
    {
        return view('admin.service_appointments.form', $this->formData($serviceAppointment, 'edit'));
    }

    public function update(Request $request, ServiceAppointment $serviceAppointment)
    {
        $serviceAppointment->update($this->appointmentPayload($this->validatedAppointment($request)));

        return redirect()
            ->route('admin.service-appointments.show', $serviceAppointment)
            ->with('success', 'Đã cập nhật lịch dịch vụ.');
    }

    public function destroy(ServiceAppointment $serviceAppointment)
    {
        $serviceAppointment->delete();

        return redirect()
            ->route('admin.service-appointments.index')
            ->with('success', 'Đã xóa lịch dịch vụ.');
    }

    private function validatedAppointment(Request $request): array
    {
        return $request->validate([
            'user_id' => 'nullable|integer|exists:users,user_id',
            'car_id' => 'nullable|integer|exists:cars,car_id',
            'warranty_id' => 'nullable|integer|exists:warranties,id',
            'service_type' => ['required', Rule::in(ServiceAppointment::SERVICE_TYPES)],
            'appointment_date' => 'required|date',
            'appointment_time' => 'nullable|date_format:H:i',
            'service_location' => 'nullable|string|max:255',
            'assigned_staff_id' => 'nullable|integer|exists:users,user_id',
            'status' => ['required', Rule::in(ServiceAppointment::STATUSES)],
            'customer_note' => 'nullable|string|max:2000',
            'internal_note' => 'nullable|string|max:2000',
        ]);
    }

    private function appointmentPayload(array $validated): array
    {
        if (!empty($validated['warranty_id'])) {
            $warranty = Warranty::query()->find($validated['warranty_id']);
            $validated['user_id'] = $validated['user_id'] ?: $warranty?->user_id;
            $validated['car_id'] = $validated['car_id'] ?: $warranty?->car_id;
        }

        return $validated;
    }

    private function formData(ServiceAppointment $appointment, string $mode): array
    {
        return [
            'appointment' => $appointment,
            'cars' => Car::query()
                ->with('carModel.brand')
                ->orderBy('name')
                ->get(['car_id', 'car_model_id', 'name', 'vin', 'license_plate']),
            'mode' => $mode,
            'serviceTypeOptions' => ServiceAppointment::serviceTypeOptions(),
            'staff' => $this->staffOptions(),
            'statusOptions' => ServiceAppointment::statusOptions(),
            'users' => User::query()
                ->where('role', 'customer')
                ->orWhere('user_id', $appointment->user_id)
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
