<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $customers = Customer::query()
            ->with('creator')
            ->withCount('interactions')
            ->withMax('interactions', 'created_at')
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($inner) use ($search) {
                    $inner->where('customer_code', 'like', "%{$search}%")
                        ->orWhere('full_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('province', 'like', "%{$search}%")
                        ->orWhere('interested_car', 'like', "%{$search}%");
                });
            })
            ->when($filters['source'] !== '', fn ($query) => $query->where('source', $filters['source']))
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $statusCounts = Customer::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalCustomers = Customer::count();
        $sourceOptions = Customer::sourceOptions();
        $statusOptions = Customer::STATUSES;

        return view('admin.customers.index', compact(
            'customers',
            'filters',
            'sourceOptions',
            'statusOptions',
            'statusCounts',
            'totalCustomers'
        ));
    }

    public function create(): View
    {
        $customer = new Customer(['status' => Customer::STATUS_NEW]);
        $sourceOptions = Customer::sourceOptions();
        $statusOptions = Customer::STATUSES;
        $genderOptions = Customer::GENDERS;

        return view('admin.customers.form', compact('customer', 'sourceOptions', 'statusOptions', 'genderOptions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['customer_code'] = $data['customer_code'] ?: $this->generateCustomerCode();
        $data['created_by'] = Auth::id();

        $customer = Customer::create($data);

        app(\App\Services\AdminNotificationService::class)->createOnce(
            'customers',
            'customer_created',
            'Khach hang moi ' . $customer->customer_code,
            'Ho so khach hang moi vua duoc tao trong CRM.',
            route('admin.customers.show', $customer, false),
            ['customer_id' => $customer->customer_id],
            \App\Models\AdminNotification::PRIORITY_NORMAL,
            $request->user()
        );

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Đã thêm khách hàng mới.');
    }

    public function show(Customer $customer): View
    {
        $customer->load('creator');

        $interactions = $customer->interactions()
            ->with('creator')
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('admin.customers.show', compact('customer', 'interactions'));
    }

    public function edit(Customer $customer): View
    {
        $sourceOptions = Customer::sourceOptions();
        $statusOptions = Customer::STATUSES;
        $genderOptions = Customer::GENDERS;

        return view('admin.customers.form', compact('customer', 'sourceOptions', 'statusOptions', 'genderOptions'));
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $data = $this->validatedData($request, $customer);
        $data['customer_code'] = $data['customer_code'] ?: $customer->customer_code;

        $customer->update($data);

        return redirect()
            ->route('admin.customers.show', $customer)
            ->with('success', 'Đã cập nhật khách hàng.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Đã xóa khách hàng.');
    }

    public function storeInteraction(Request $request, Customer $customer): RedirectResponse
    {
        $request->merge([
            'note' => trim((string) $request->input('note')),
        ]);

        $data = $request->validate(
            [
                'note' => ['required', 'string', 'max:2000'],
            ],
            [],
            [
                'note' => 'ghi chú chăm sóc',
            ]
        );

        $customer->interactions()->create([
            'note' => trim($data['note']),
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Đã thêm ghi chú chăm sóc.');
    }

    private function filters(Request $request): array
    {
        $source = (string) $request->input('source', '');
        $status = (string) $request->input('status', '');

        return [
            'q' => trim((string) $request->input('q', '')),
            'source' => in_array($source, Customer::SOURCES, true) ? $source : '',
            'status' => array_key_exists($status, Customer::STATUSES) ? $status : '',
        ];
    }

    private function validatedData(Request $request, ?Customer $customer = null): array
    {
        $this->trimInput($request);

        return $request->validate(
            [
                'customer_code' => [
                    'nullable',
                    'string',
                    'max:50',
                    Rule::unique('customers', 'customer_code')->ignore($customer?->customer_id, 'customer_id'),
                ],
                'full_name' => ['required', 'string', 'max:255'],
                'phone' => ['required', 'string', 'max:30'],
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                    Rule::unique('customers', 'email')->ignore($customer?->customer_id, 'customer_id'),
                ],
                'gender' => ['nullable', Rule::in(array_keys(Customer::GENDERS))],
                'birthday' => ['nullable', 'date', 'before_or_equal:today'],
                'address' => ['nullable', 'string', 'max:1000'],
                'province' => ['nullable', 'string', 'max:120'],
                'occupation' => ['nullable', 'string', 'max:120'],
                'source' => ['nullable', Rule::in(Customer::SOURCES)],
                'interested_car' => ['nullable', 'string', 'max:255'],
                'status' => ['required', Rule::in(array_keys(Customer::STATUSES))],
                'note' => ['nullable', 'string', 'max:2000'],
            ],
            [],
            [
                'customer_code' => 'mã khách hàng',
                'full_name' => 'họ tên',
                'phone' => 'số điện thoại',
                'email' => 'email',
                'gender' => 'giới tính',
                'birthday' => 'ngày sinh',
                'address' => 'địa chỉ',
                'province' => 'tỉnh/thành',
                'occupation' => 'nghề nghiệp',
                'source' => 'nguồn khách',
                'interested_car' => 'xe quan tâm',
                'status' => 'trạng thái',
                'note' => 'ghi chú',
            ]
        );
    }

    private function trimInput(Request $request): void
    {
        $fields = [
            'customer_code',
            'full_name',
            'phone',
            'email',
            'gender',
            'address',
            'province',
            'occupation',
            'source',
            'interested_car',
            'note',
        ];

        $normalized = [];

        foreach ($fields as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $value = trim((string) $request->input($field));
            $normalized[$field] = $value === '' ? null : $value;
        }

        $request->merge($normalized);
    }

    private function generateCustomerCode(): string
    {
        do {
            $code = 'KH' . now()->format('ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }
}
