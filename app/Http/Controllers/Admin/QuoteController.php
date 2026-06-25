<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Quote;
use App\Services\QuotePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $quotes = Quote::query()
            ->with(['customer', 'car.modelInfo.brand', 'user'])
            ->when($filters['q'] !== '', function ($query) use ($filters) {
                $search = $filters['q'];

                $query->where(function ($inner) use ($search) {
                    $inner->where('quote_code', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('customer', function ($customerQuery) use ($search) {
                            $customerQuery->where('customer_code', 'like', "%{$search}%")
                                ->orWhere('full_name', 'like', "%{$search}%")
                                ->orWhere('phone', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        })
                        ->orWhereHas('car', function ($carQuery) use ($search) {
                            $carQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('vin', 'like', "%{$search}%")
                                ->orWhere('license_plate', 'like', "%{$search}%")
                                ->orWhere('internal_code', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] !== '', fn ($query) => $query->where('status', $filters['status']))
            ->when($filters['customer_id'], fn ($query) => $query->where('customer_id', $filters['customer_id']))
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $statusCounts = Quote::query()
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalQuotes = Quote::count();
        $customers = $this->customersForSelect();
        $statusOptions = Quote::STATUSES;

        return view('admin.quotes.index', compact(
            'quotes',
            'filters',
            'statusCounts',
            'totalQuotes',
            'customers',
            'statusOptions'
        ));
    }

    public function create(): View
    {
        $quote = new Quote([
            'status' => Quote::STATUS_DRAFT,
            'discount_amount' => 0,
            'registration_fee' => 0,
            'plate_fee' => 0,
            'insurance_fee' => 0,
            'other_fee' => 0,
        ]);

        return view('admin.quotes.form', $this->formData($quote));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['user_id'] = Auth::id();

        $quote = DB::transaction(function () use ($data) {
            $data['quote_code'] = Quote::generateQuoteCode();

            return Quote::create($data);
        });

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'Đã tạo báo giá mới.');
    }

    public function show(Quote $quote): View
    {
        $quote->load(['customer', 'car.modelInfo.brand', 'user']);

        return view('admin.quotes.show', compact('quote'));
    }

    public function edit(Quote $quote): View
    {
        return view('admin.quotes.form', $this->formData($quote));
    }

    public function update(Request $request, Quote $quote): RedirectResponse
    {
        $data = $this->validatedData($request);

        $quote->update($data);

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'Đã cập nhật báo giá.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return redirect()
            ->route('admin.quotes.index')
            ->with('success', 'Đã xóa báo giá.');
    }

    public function send(Quote $quote): RedirectResponse
    {
        $quote->ensurePublicToken();

        $quote->forceFill([
            'status' => $quote->status === Quote::STATUS_ACCEPTED ? Quote::STATUS_ACCEPTED : Quote::STATUS_SENT,
            'sent_at' => $quote->sent_at ?: now(),
        ])->save();

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'Đã tạo link báo giá để gửi cho khách hàng.')
            ->with('quote_public_url', $quote->publicUrl());
    }

    public function pdf(Quote $quote, QuotePdfService $quotePdf): Response
    {
        return $quotePdf->download($quote);
    }

    private function formData(Quote $quote): array
    {
        return [
            'quote' => $quote,
            'customers' => $this->customersForSelect(),
            'cars' => $this->carsForSelect(),
            'statusOptions' => Quote::STATUSES,
        ];
    }

    private function filters(Request $request): array
    {
        $status = (string) $request->input('status', '');
        $customerId = (string) $request->input('customer_id', '');

        return [
            'q' => trim((string) $request->input('q', '')),
            'status' => array_key_exists($status, Quote::STATUSES) ? $status : '',
            'customer_id' => ctype_digit($customerId) ? (int) $customerId : null,
        ];
    }

    private function validatedData(Request $request): array
    {
        $this->trimInput($request);

        return $request->validate(
            [
                'customer_id' => ['required', 'integer', Rule::exists('customers', 'customer_id')],
                'car_id' => ['required', 'integer', Rule::exists('cars', 'car_id')],
                'vehicle_price' => ['required', 'numeric', 'min:0', 'max:999999999999.99'],
                'discount_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
                'registration_fee' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
                'plate_fee' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
                'insurance_fee' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
                'other_fee' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
                'status' => ['required', Rule::in(array_keys(Quote::STATUSES))],
                'note' => ['nullable', 'string', 'max:2000'],
                'expired_at' => ['nullable', 'date'],
            ],
            [],
            [
                'customer_id' => 'khách hàng',
                'car_id' => 'xe',
                'vehicle_price' => 'giá xe',
                'discount_amount' => 'giảm giá',
                'registration_fee' => 'phí đăng ký',
                'plate_fee' => 'phí biển số',
                'insurance_fee' => 'phí bảo hiểm',
                'other_fee' => 'phí khác',
                'status' => 'trạng thái',
                'note' => 'ghi chú',
                'expired_at' => 'ngày hết hạn',
            ]
        );
    }

    private function trimInput(Request $request): void
    {
        $fields = ['note', 'expired_at'];
        $normalized = [];

        foreach ($fields as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $value = trim((string) $request->input($field));
            $normalized[$field] = $value === '' ? null : $value;
        }

        foreach (['discount_amount', 'registration_fee', 'plate_fee', 'insurance_fee', 'other_fee'] as $field) {
            if ($request->input($field) === null || $request->input($field) === '') {
                $normalized[$field] = 0;
            }
        }

        $request->merge($normalized);
    }

    private function customersForSelect()
    {
        return Customer::query()
            ->orderBy('full_name')
            ->get(['customer_id', 'customer_code', 'full_name', 'phone']);
    }

    private function carsForSelect()
    {
        return Car::query()
            ->with('modelInfo.brand')
            ->orderByDesc('created_at')
            ->get([
                'car_id',
                'car_model_id',
                'name',
                'vin',
                'license_plate',
                'price',
                'sale_price',
                'registration_fee',
                'license_plate_fee',
                'insurance_fee',
                'other_fees',
            ]);
    }
}
