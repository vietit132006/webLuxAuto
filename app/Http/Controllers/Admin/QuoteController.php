<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Quote;
use App\Models\Ticket;
use App\Models\User;
use App\Services\QuotePdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
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
            'user_id' => Auth::id(),
        ]);

        return view('admin.quotes.form', $this->formData($quote));
    }

    public function createFromTestDrive(int $id): View|RedirectResponse
    {
        $booking = $this->testDriveForQuote($id);

        abort_unless($booking->status === Ticket::STATUS_COMPLETED, 403);

        $existingQuote = $booking->quotes->first();

        if ($existingQuote) {
            return redirect()
                ->route('admin.quotes.show', $existingQuote)
                ->with('success', 'Lịch lái thử này đã có báo giá, đã mở báo giá hiện có.');
        }

        $customer = $this->customerForTestDrive($booking);
        $salesUserId = $this->userIdForSalesPerson($booking->sales_person);

        $quote = new Quote([
            'status' => Quote::STATUS_DRAFT,
            'customer_id' => $customer?->customer_id,
            'car_id' => $booking->car_id,
            'user_id' => $salesUserId ?: Auth::id(),
            'test_drive_id' => $booking->ticket_id,
            'discount_amount' => 0,
            'registration_fee' => 0,
            'plate_fee' => 0,
            'insurance_fee' => 0,
            'other_fee' => 0,
        ]);

        $prefillWarning = $customer
            ? null
            : 'Chưa tìm thấy khách hàng CRM trùng email/SĐT với lịch lái thử. Vui lòng chọn khách hàng trước khi lưu báo giá.';

        return view('admin.quotes.form', $this->formData($quote, $booking, $prefillWarning));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        $data['user_id'] = $data['user_id'] ?? Auth::id();

        if (!empty($data['test_drive_id'])) {
            $existingQuote = Quote::query()
                ->where('test_drive_id', $data['test_drive_id'])
                ->orderByDesc('created_at')
                ->first();

            if ($existingQuote) {
                return redirect()
                    ->route('admin.quotes.show', $existingQuote)
                    ->with('success', 'Lịch lái thử này đã có báo giá, đã mở báo giá hiện có.');
            }
        }

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
        $quote->load(['customer', 'car.modelInfo.brand', 'user', 'testDrive', 'order']);

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

    public function createOrderFromQuote(Request $request, Quote $quote): RedirectResponse
    {
        [$order, $created, $quoteCode, $createdCustomerUser] = DB::transaction(function () use ($quote, $request): array {
            $lockedQuote = Quote::query()
                ->with(['customer', 'car'])
                ->whereKey($quote->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedQuote->status !== Quote::STATUS_ACCEPTED) {
                throw ValidationException::withMessages([
                    'quote' => 'Chỉ có thể tạo đơn khi khách đồng ý báo giá.',
                ]);
            }

            $existingOrder = Order::query()
                ->where('quote_id', $lockedQuote->quote_id)
                ->first();

            if ($existingOrder) {
                return [$existingOrder, false, $lockedQuote->quote_code, false];
            }

            if (!$lockedQuote->customer) {
                throw ValidationException::withMessages([
                    'customer' => 'Báo giá chưa có khách hàng hợp lệ để tạo đơn hàng.',
                ]);
            }

            if (!$lockedQuote->car) {
                throw ValidationException::withMessages([
                    'car' => 'Báo giá chưa có xe hợp lệ để tạo đơn hàng.',
                ]);
            }

            $lockedCar = Car::query()
                ->whereKey($lockedQuote->car_id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($lockedCar->availableStock() <= 0) {
                throw ValidationException::withMessages([
                    'stock' => 'Xe trong báo giá hiện không còn tồn khả dụng để tạo đơn hàng.',
                ]);
            }

            [$customerUser, $createdCustomerUser] = $this->findOrCreateCustomerUserForQuote($lockedQuote);

            $order = Order::create([
                'quote_id' => $lockedQuote->quote_id,
                'user_id' => $customerUser->user_id,
                'total_price' => $lockedQuote->total_price,
                'deposit_amount' => 0,
                'status' => Order::STATUS_PENDING,
            ]);

            OrderDetail::create([
                'order_id' => $order->order_id,
                'car_id' => $lockedCar->car_id,
                'quantity' => 1,
                'price' => $lockedQuote->total_price,
            ]);

            $order->statusHistories()->create([
                'old_status' => null,
                'new_status' => (string) Order::STATUS_PENDING,
                'user_id' => $request->user()?->getKey(),
                'note' => 'Tạo đơn hàng từ báo giá ' . $lockedQuote->quote_code . '.',
            ]);

            return [$order, true, $lockedQuote->quote_code, $createdCustomerUser];
        });

        if (!$created) {
            return redirect()
                ->route('admin.orders.show', $order->order_id)
                ->with('success', 'Báo giá ' . $quoteCode . ' đã có đơn hàng ' . $order->display_code . '.');
        }

        if ($createdCustomerUser) {
            return redirect()
                ->route('admin.quotes.show', $quote)
                ->with('success', 'Đã tự động tạo tài khoản khách hàng và tạo đơn hàng thành công.');
        }

        return redirect()
            ->route('admin.orders.show', $order->order_id)
            ->with('success', 'Đã tạo đơn hàng ' . $order->display_code . ' từ báo giá ' . $quoteCode . ' thành công.');
    }

    public function pdf(Quote $quote, QuotePdfService $quotePdf): Response
    {
        return $quotePdf->download($quote);
    }

    private function formData(Quote $quote, ?Ticket $sourceTestDrive = null, ?string $prefillWarning = null): array
    {
        $sourceTestDrive ??= $quote->testDrive;

        return [
            'quote' => $quote,
            'customers' => $this->customersForSelect(),
            'cars' => $this->carsForSelect(),
            'users' => $this->usersForSelect($quote->user_id ? (int) $quote->user_id : null),
            'statusOptions' => Quote::STATUSES,
            'sourceTestDrive' => $sourceTestDrive,
            'prefillWarning' => $prefillWarning,
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
                'user_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('users', 'user_id')->where(function ($query) {
                        $query->whereIn('role', ['admin', 'staff']);
                    }),
                ],
                'test_drive_id' => [
                    'nullable',
                    'integer',
                    Rule::exists('support_tickets', 'ticket_id')->where(function ($query) {
                        $query->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
                            ->where('status', Ticket::STATUS_COMPLETED);
                    }),
                ],
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
                'user_id' => 'nhân viên phụ trách',
                'test_drive_id' => 'lịch lái thử',
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

        foreach (['user_id', 'test_drive_id'] as $field) {
            if ($request->input($field) === '') {
                $normalized[$field] = null;
            }
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

    private function usersForSelect(?int $selectedUserId = null)
    {
        return User::query()
            ->where(function ($query) use ($selectedUserId): void {
                $query->where(function ($activeUsers): void {
                    $activeUsers->whereIn('role', ['admin', 'staff'])
                        ->where('status', true);
                });

                if ($selectedUserId) {
                    $query->orWhere('user_id', $selectedUserId);
                }
            })
            ->orderBy('name')
            ->get(['user_id', 'name', 'email', 'role']);
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
                'stock',
                'stock_quantity',
                'reserved_quantity',
                'registration_fee',
                'license_plate_fee',
                'insurance_fee',
                'other_fees',
            ]);
    }

    private function testDriveForQuote(int $id): Ticket
    {
        return Ticket::query()
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->with(['user', 'car.modelInfo.brand', 'quotes'])
            ->findOrFail($id);
    }

    private function customerForTestDrive(Ticket $booking): ?Customer
    {
        $email = trim((string) $booking->user?->email);
        $phone = trim((string) $booking->user?->phone);

        if ($email === '' && $phone === '') {
            return null;
        }

        $query = Customer::query()
            ->where(function ($customerQuery) use ($email, $phone): void {
                if ($email !== '') {
                    $customerQuery->where('email', $email);
                }

                if ($phone !== '') {
                    if ($email === '') {
                        $customerQuery->where('phone', $phone);
                    } else {
                        $customerQuery->orWhere('phone', $phone);
                    }
                }
            });

        if ($email !== '') {
            $query->orderByRaw('CASE WHEN email = ? THEN 0 ELSE 1 END', [$email]);
        }

        if ($phone !== '') {
            $query->orderByRaw('CASE WHEN phone = ? THEN 0 ELSE 1 END', [$phone]);
        }

        return $query
            ->orderByDesc('updated_at')
            ->first();
    }

    private function findOrCreateCustomerUserForQuote(Quote $quote): array
    {
        $email = trim((string) $quote->customer?->email);
        $phone = trim((string) $quote->customer?->phone);

        if ($email !== '') {
            $user = User::query()
                ->where('email', $email)
                ->first();

            if ($user) {
                return [$user, false];
            }
        }

        if ($phone !== '') {
            $user = User::query()
                ->where('phone', $phone)
                ->first();

            if ($user) {
                return [$user, false];
            }
        }

        if ($email === '') {
            $email = $this->generatedCustomerEmail($quote);
        }

        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user) {
            return [$user, false];
        }

        return [
            User::create([
                'name' => $quote->customer?->full_name ?: 'Khách hàng ' . $quote->quote_code,
                'email' => $email,
                'phone' => $phone !== '' ? $phone : null,
                'password' => Hash::make(Str::random(12)),
                'role' => 'customer',
                'status' => true,
            ]),
            true,
        ];
    }

    private function generatedCustomerEmail(Quote $quote): string
    {
        $customerId = $quote->customer?->customer_id;
        $suffix = $customerId ? 'customer-' . $customerId : 'quote-' . $quote->quote_id;

        return $suffix . '@luxauto.local';
    }

    private function userIdForSalesPerson(?string $salesPerson): ?int
    {
        $salesPerson = trim((string) $salesPerson);

        if ($salesPerson === '') {
            return null;
        }

        return User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->where('status', true)
            ->where('name', $salesPerson)
            ->value('user_id');
    }
}
