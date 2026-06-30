<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Customer;
use App\Models\LiveLead;
use App\Models\LiveSession;
use App\Models\LiveSessionCar;
use App\Models\Promotion;
use App\Models\Quote;
use App\Models\Ticket;
use App\Models\User;
use App\Support\YouTubeVideo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AdminLiveController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $sessions = LiveSession::query()
            ->with(['host'])
            ->withCount([
                'sessionCars as pinned_cars_count',
                'leads',
                'leads as quote_requests_count' => fn (Builder $query): Builder => $query->where('lead_type', LiveLead::TYPE_QUOTE_REQUEST),
                'leads as test_drive_requests_count' => fn (Builder $query): Builder => $query->where('lead_type', LiveLead::TYPE_TEST_DRIVE_REQUEST),
            ])
            ->when($filters['q'] !== '', function (Builder $query) use ($filters): void {
                $search = $filters['q'];

                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('live_code', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('video_id', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] !== '', fn (Builder $query): Builder => $query->where('status', $filters['status']))
            ->when($filters['platform'] !== '', fn (Builder $query): Builder => $query->where('platform', $filters['platform']))
            ->when($filters['date_from'] !== '', fn (Builder $query): Builder => $query->where('starts_at', '>=', $filters['date_from'] . ' 00:00:00'))
            ->when($filters['date_to'] !== '', fn (Builder $query): Builder => $query->where('starts_at', '<=', $filters['date_to'] . ' 23:59:59'))
            ->orderByRaw("FIELD(status, 'live', 'scheduled', 'draft', 'ended', 'cancelled')")
            ->orderByRaw('starts_at IS NULL')
            ->orderByDesc('starts_at')
            ->orderByDesc('created_at')
            ->paginate(12)
            ->withQueryString();

        $statusCounts = LiveSession::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.live.index', [
            'sessions' => $sessions,
            'filters' => $filters,
            'statusOptions' => LiveSession::statusOptions(),
            'platformOptions' => LiveSession::platformOptions(),
            'stats' => [
                'total' => LiveSession::count(),
                'live' => (int) ($statusCounts[LiveSession::STATUS_LIVE] ?? 0),
                'scheduled' => (int) ($statusCounts[LiveSession::STATUS_SCHEDULED] ?? 0),
                'ended' => (int) ($statusCounts[LiveSession::STATUS_ENDED] ?? 0),
                'new_leads' => LiveLead::where('status', LiveLead::STATUS_NEW)->count(),
            ],
        ]);
    }

    public function create(): View
    {
        $session = new LiveSession([
            'platform' => 'youtube',
            'status' => LiveSession::STATUS_DRAFT,
            'is_public' => true,
            'replay_enabled' => false,
            'is_active' => false,
        ]);

        return view('admin.live.form', $this->formData($session));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedSessionData($request);
        $carRows = $this->validatedCarRows($request);

        $session = DB::transaction(function () use ($data, $carRows): LiveSession {
            if ($data['is_active']) {
                $this->deactivateOtherLiveSessions();
            }

            $session = LiveSession::create([
                ...$data,
                'created_by' => Auth::id(),
            ]);

            $this->syncSessionCars($session, $carRows);

            return $session;
        });

        return redirect()
            ->route('admin.live.show', $session)
            ->with('success', 'Da tao phien livestream ' . $session->live_code . '.');
    }

    public function show(LiveSession $liveSession): View
    {
        $liveSession->load([
            'host',
            'creator',
            'sessionCars.car.modelInfo.brand',
            'sessionCars.promotion',
            'leads.car.modelInfo.brand',
            'leads.user',
            'leads.assignedTo',
            'leads.quote',
            'leads.testDrive',
        ]);

        $leadCounts = $liveSession->leads
            ->groupBy('lead_type')
            ->map->count();

        return view('admin.live.show', [
            'session' => $liveSession,
            'leadCounts' => $leadCounts,
            'statusOptions' => LiveSession::statusOptions(),
            'leadStatusOptions' => LiveLead::STATUSES,
            'users' => $this->staffUsers(),
        ]);
    }

    public function edit(LiveSession $liveSession): View
    {
        $liveSession->load('sessionCars');

        return view('admin.live.form', $this->formData($liveSession));
    }

    public function update(Request $request, LiveSession $liveSession): RedirectResponse
    {
        $data = $this->validatedSessionData($request, $liveSession);
        $carRows = $this->validatedCarRows($request);

        DB::transaction(function () use ($liveSession, $data, $carRows): void {
            if ($data['is_active']) {
                $this->deactivateOtherLiveSessions($liveSession);
            }

            $liveSession->update($data);
            $this->syncSessionCars($liveSession, $carRows);
        });

        return redirect()
            ->route('admin.live.show', $liveSession)
            ->with('success', 'Da cap nhat phien livestream.');
    }

    public function destroy(LiveSession $liveSession): RedirectResponse
    {
        if ($liveSession->leads()->exists()) {
            return back()->withErrors(['live' => 'Khong the xoa phien live da phat sinh lead.']);
        }

        $liveSession->delete();

        return redirect()
            ->route('admin.live.index')
            ->with('success', 'Da xoa phien livestream.');
    }

    public function start(LiveSession $liveSession): RedirectResponse
    {
        if (!YouTubeVideo::isValidId($liveSession->video_id)) {
            return back()->withErrors(['video_id' => 'Can cau hinh YouTube video ID hop le truoc khi bat live.']);
        }

        DB::transaction(function () use ($liveSession): void {
            $this->deactivateOtherLiveSessions($liveSession);

            $liveSession->forceFill([
                'status' => LiveSession::STATUS_LIVE,
                'is_active' => true,
                'starts_at' => $liveSession->starts_at ?: now(),
            ])->save();
        });

        return back()->with('success', 'Da bat phat song livestream.');
    }

    public function stop(LiveSession $liveSession): RedirectResponse
    {
        $nextStatus = $liveSession->starts_at && $liveSession->starts_at->isFuture()
            ? LiveSession::STATUS_SCHEDULED
            : LiveSession::STATUS_DRAFT;

        $liveSession->forceFill([
            'status' => $nextStatus,
            'is_active' => false,
        ])->save();

        return back()->with('success', 'Da tam tat livestream.');
    }

    public function end(LiveSession $liveSession): RedirectResponse
    {
        $liveSession->forceFill([
            'status' => LiveSession::STATUS_ENDED,
            'is_active' => false,
            'ends_at' => $liveSession->ends_at ?: now(),
        ])->save();

        return back()->with('success', 'Da ket thuc phien livestream.');
    }

    public function lead(LiveLead $lead): View
    {
        $lead->load([
            'liveSession',
            'car.modelInfo.brand',
            'user',
            'assignedTo',
            'quote',
            'testDrive',
        ]);

        return view('admin.live.lead', [
            'lead' => $lead,
            'leadStatusOptions' => LiveLead::STATUSES,
            'leadTypeOptions' => LiveLead::TYPES,
            'users' => $this->staffUsers(),
        ]);
    }

    public function updateLead(Request $request, LiveLead $lead): RedirectResponse
    {
        $data = $request->validate([
            'customer_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:255'],
            'status' => ['required', Rule::in(array_keys(LiveLead::STATUSES))],
            'assigned_to' => [
                'nullable',
                'integer',
                Rule::exists('users', 'user_id')->where(fn ($query) => $query->whereIn('role', ['admin', 'staff'])),
            ],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $data['assigned_to'] = $data['assigned_to'] ?: null;
        $lead->update($data);

        return back()->with('success', 'Da cap nhat live lead.');
    }

    public function createQuoteFromLead(LiveLead $lead): RedirectResponse
    {
        $lead->load(['liveSession', 'car.modelInfo.brand', 'quote']);

        if ($lead->quote) {
            return redirect()
                ->route('admin.quotes.show', $lead->quote)
                ->with('success', 'Lead nay da co bao gia, da mo bao gia hien co.');
        }

        if (!$lead->car) {
            return back()->withErrors(['car_id' => 'Lead chua gan xe, khong the tao bao gia.']);
        }

        $quote = DB::transaction(function () use ($lead): Quote {
            $customer = $this->findOrCreateCustomerFromLead($lead);
            $sessionCar = LiveSessionCar::query()
                ->where('live_session_id', $lead->live_session_id)
                ->where('car_id', $lead->car_id)
                ->first();

            $vehiclePrice = (float) ($sessionCar?->live_price ?: ($lead->car->sale_price ?: $lead->car->price ?: 0));

            $quote = Quote::create([
                'customer_id' => $customer->customer_id,
                'car_id' => $lead->car_id,
                'user_id' => $lead->assigned_to ?: Auth::id(),
                'live_session_id' => $lead->live_session_id,
                'live_lead_id' => $lead->id,
                'vehicle_price' => $vehiclePrice,
                'discount_amount' => 0,
                'registration_fee' => (float) ($lead->car->registration_fee ?? 0),
                'plate_fee' => (float) ($lead->car->license_plate_fee ?? 0),
                'insurance_fee' => (float) ($lead->car->insurance_fee ?? 0),
                'other_fee' => (float) ($lead->car->other_fees ?? 0),
                'status' => Quote::STATUS_DRAFT,
                'note' => trim('Tao tu live lead #' . $lead->id . '. ' . (string) $lead->message),
                'expired_at' => now()->addDays(7)->toDateString(),
            ]);

            $lead->forceFill([
                'status' => LiveLead::STATUS_CONVERTED,
                'assigned_to' => $lead->assigned_to ?: Auth::id(),
            ])->save();

            return $quote;
        });

        app(\App\Services\AdminNotificationService::class)->createOnce(
            'quotes',
            'quote_created',
            'Bao gia moi ' . $quote->quote_code,
            'Bao gia duoc tao tu live lead #' . $lead->id . '.',
            route('admin.quotes.show', $quote, false),
            ['quote_id' => $quote->quote_id, 'live_lead_id' => $lead->id],
            \App\Models\AdminNotification::PRIORITY_NORMAL,
            request()->user()
        );

        return redirect()
            ->route('admin.quotes.show', $quote)
            ->with('success', 'Da tao bao gia nhap tu live lead.');
    }

    public function createTestDriveFromLead(LiveLead $lead): RedirectResponse
    {
        $lead->load(['liveSession', 'car.modelInfo.brand', 'testDrive']);

        if ($lead->testDrive) {
            return redirect()
                ->route('admin.test_drives.show', $lead->testDrive->ticket_id)
                ->with('success', 'Lead nay da co lich lai thu, da mo lich hien co.');
        }

        if (!$lead->car) {
            return back()->withErrors(['car_id' => 'Lead chua gan xe, khong the tao lich lai thu.']);
        }

        $booking = DB::transaction(function () use ($lead): Ticket {
            $user = $this->findOrCreateUserFromLead($lead);

            $booking = Ticket::create([
                'user_id' => $user->user_id,
                'ticket_type' => Ticket::TYPE_TEST_DRIVE,
                'car_id' => $lead->car_id,
                'live_session_id' => $lead->live_session_id,
                'live_lead_id' => $lead->id,
                'subject' => 'Dang ky lai thu tu livestream ' . ($lead->liveSession?->live_code ?: '#' . $lead->live_session_id),
                'message' => $lead->message ?: 'Khach de lai yeu cau lai thu trong livestream.',
                'status' => Ticket::STATUS_PENDING,
                'sales_person' => $lead->assignedTo?->name,
            ]);

            $lead->forceFill([
                'status' => LiveLead::STATUS_CONVERTED,
                'assigned_to' => $lead->assigned_to ?: Auth::id(),
            ])->save();

            return $booking;
        });

        app(\App\Services\AdminNotificationService::class)->createOnce(
            'test_drives',
            'test_drive_created',
            'Lich lai thu moi ' . $booking->display_code,
            'Lich lai thu duoc tao tu live lead #' . $lead->id . '.',
            route('admin.test_drives.show', $booking->ticket_id, false),
            ['ticket_id' => $booking->ticket_id, 'live_lead_id' => $lead->id],
            \App\Models\AdminNotification::PRIORITY_HIGH,
            request()->user()
        );

        return redirect()
            ->route('admin.test_drives.show', $booking->ticket_id)
            ->with('success', 'Da tao lich lai thu tu live lead.');
    }

    private function validatedSessionData(Request $request, ?LiveSession $session = null): array
    {
        $this->normalizeSessionInput($request);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('live_sessions', 'slug')->ignore($session?->id),
            ],
            'description' => ['nullable', 'string', 'max:3000'],
            'platform' => ['required', Rule::in(array_keys(LiveSession::PLATFORMS))],
            'video_input' => ['nullable', 'string', 'max:500'],
            'thumbnail' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(array_keys(LiveSession::STATUSES))],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'host_user_id' => [
                'nullable',
                'integer',
                Rule::exists('users', 'user_id')->where(fn ($query) => $query->whereIn('role', ['admin', 'staff'])),
            ],
            'cta_label' => ['nullable', 'string', 'max:80'],
            'cta_url' => ['nullable', 'url', 'max:500'],
        ], [], [
            'title' => 'tieu de live',
            'video_input' => 'link hoac ID YouTube',
            'starts_at' => 'thoi gian bat dau',
            'ends_at' => 'thoi gian ket thuc',
        ]);

        $videoInput = $data['video_input'] ?? null;
        $videoId = $data['platform'] === 'youtube' ? YouTubeVideo::extractId($videoInput) : null;
        $willBeLive = $request->boolean('is_active') || $data['status'] === LiveSession::STATUS_LIVE;

        if ($data['platform'] === 'youtube' && $videoInput && !$videoId) {
            throw ValidationException::withMessages([
                'video_input' => 'Link hoac Video ID YouTube khong hop le. Video ID thuong co 11 ky tu.',
            ]);
        }

        if ($willBeLive && !$videoId) {
            throw ValidationException::withMessages([
                'video_input' => 'Can Video ID YouTube hop le truoc khi bat live.',
            ]);
        }

        $status = $data['status'];
        $isActive = $request->boolean('is_active') || $status === LiveSession::STATUS_LIVE;

        if ($isActive) {
            $status = LiveSession::STATUS_LIVE;
        }

        return [
            'title' => $data['title'],
            'slug' => $data['slug'] ?: LiveSession::uniqueSlug($data['title'], $session?->id),
            'description' => $data['description'] ?? null,
            'platform' => $data['platform'],
            'video_id' => $videoId,
            'video_url' => $videoInput,
            'thumbnail' => $data['thumbnail'] ?? null,
            'status' => $status,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
            'host_user_id' => $data['host_user_id'] ?: null,
            'is_active' => $isActive,
            'is_public' => $request->boolean('is_public'),
            'replay_enabled' => $request->boolean('replay_enabled'),
            'cta_label' => $data['cta_label'] ?? null,
            'cta_url' => $data['cta_url'] ?? null,
        ];
    }

    private function validatedCarRows(Request $request): array
    {
        $request->validate([
            'cars' => ['nullable', 'array'],
            'cars.*.car_id' => ['required', 'integer', Rule::exists('cars', 'car_id')],
            'cars.*.enabled' => ['nullable', 'boolean'],
            'cars.*.display_order' => ['nullable', 'integer', 'min:0', 'max:999999'],
            'cars.*.promotion_id' => ['nullable', 'integer', Rule::exists('promotions', 'id')],
            'cars.*.live_price' => ['nullable', 'numeric', 'min:0', 'max:999999999999.99'],
            'cars.*.live_note' => ['nullable', 'string', 'max:1000'],
            'cars.*.is_focus' => ['nullable', 'boolean'],
            'cars.*.is_active' => ['nullable', 'boolean'],
        ]);

        return collect($request->input('cars', []))
            ->filter(fn (array $row): bool => !empty($row['enabled']))
            ->map(function (array $row): array {
                return [
                    'car_id' => (int) $row['car_id'],
                    'promotion_id' => !empty($row['promotion_id']) ? (int) $row['promotion_id'] : null,
                    'display_order' => max(0, (int) ($row['display_order'] ?? 0)),
                    'live_price' => ($row['live_price'] ?? '') !== '' ? (float) $row['live_price'] : null,
                    'live_note' => trim((string) ($row['live_note'] ?? '')) ?: null,
                    'is_focus' => !empty($row['is_focus']),
                    'is_active' => array_key_exists('is_active', $row) ? !empty($row['is_active']) : true,
                ];
            })
            ->sortBy('display_order')
            ->values()
            ->all();
    }

    private function syncSessionCars(LiveSession $session, array $carRows): void
    {
        $carIds = collect($carRows)->pluck('car_id')->all();

        $session->sessionCars()
            ->when($carIds !== [], fn (Builder $query): Builder => $query->whereNotIn('car_id', $carIds))
            ->when($carIds === [], fn (Builder $query): Builder => $query)
            ->delete();

        foreach ($carRows as $row) {
            $sessionCar = $session->sessionCars()->firstOrNew(['car_id' => $row['car_id']]);

            $sessionCar->fill([
                'promotion_id' => $row['promotion_id'],
                'display_order' => $row['display_order'],
                'live_price' => $row['live_price'],
                'live_note' => $row['live_note'],
                'is_focus' => $row['is_focus'],
                'is_active' => $row['is_active'],
                'pinned_at' => $sessionCar->pinned_at ?: now(),
            ])->save();
        }

        $session->forceFill(['featured_car_ids' => $carIds])->save();
    }

    private function formData(LiveSession $session): array
    {
        $selectedCars = $session->exists
            ? $session->sessionCars->keyBy('car_id')
            : collect();

        return [
            'session' => $session,
            'statusOptions' => LiveSession::statusOptions(),
            'platformOptions' => LiveSession::platformOptions(),
            'users' => $this->staffUsers(),
            'promotions' => Promotion::query()
                ->effective()
                ->orderedForDisplay()
                ->get(['id', 'promotion_code', 'title', 'promotion_type', 'discount_type', 'discount_value']),
            'cars' => Car::query()
                ->with('carModel.brand')
                ->orderByDesc('created_at')
                ->get([
                    'car_id',
                    'car_model_id',
                    'name',
                    'vin',
                    'internal_code',
                    'image',
                    'price',
                    'sale_price',
                    'stock',
                    'stock_quantity',
                    'reserved_quantity',
                    'status',
                ]),
            'selectedCars' => $selectedCars,
        ];
    }

    private function deactivateOtherLiveSessions(?LiveSession $except = null): void
    {
        LiveSession::query()
            ->where('is_active', true)
            ->when($except, fn (Builder $query): Builder => $query->whereKeyNot($except->getKey()))
            ->update([
                'is_active' => false,
                'status' => LiveSession::STATUS_ENDED,
                'ends_at' => now(),
            ]);
    }

    private function filters(Request $request): array
    {
        $status = (string) $request->input('status', '');
        $platform = (string) $request->input('platform', '');

        return [
            'q' => trim((string) $request->input('q', '')),
            'status' => array_key_exists($status, LiveSession::STATUSES) ? $status : '',
            'platform' => array_key_exists($platform, LiveSession::PLATFORMS) ? $platform : '',
            'date_from' => $this->dateFilter($request, 'date_from'),
            'date_to' => $this->dateFilter($request, 'date_to'),
        ];
    }

    private function dateFilter(Request $request, string $key): string
    {
        $value = trim((string) $request->input($key, ''));

        if ($value === '') {
            return '';
        }

        try {
            return \Carbon\Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    private function normalizeSessionInput(Request $request): void
    {
        $normalized = [];

        foreach ([
            'title',
            'slug',
            'description',
            'video_input',
            'thumbnail',
            'starts_at',
            'ends_at',
            'host_user_id',
            'cta_label',
            'cta_url',
        ] as $field) {
            if (!$request->has($field)) {
                continue;
            }

            $value = trim((string) $request->input($field));
            $normalized[$field] = $value === '' ? null : $value;
        }

        $request->merge($normalized);
    }

    private function staffUsers()
    {
        return User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->where('status', true)
            ->orderBy('name')
            ->get(['user_id', 'name', 'email', 'role']);
    }

    private function findOrCreateCustomerFromLead(LiveLead $lead): Customer
    {
        $name = trim((string) ($lead->customer_name ?: $lead->user?->name));
        $phone = trim((string) ($lead->phone ?: $lead->user?->phone));
        $email = trim((string) ($lead->email ?: $lead->user?->email));

        if ($phone === '') {
            throw ValidationException::withMessages([
                'phone' => 'Lead can co so dien thoai truoc khi tao bao gia.',
            ]);
        }

        $customer = Customer::query()
            ->where(function (Builder $query) use ($phone, $email): void {
                $query->where('phone', $phone);

                if ($email !== '') {
                    $query->orWhere('email', $email);
                }
            })
            ->orderByDesc('updated_at')
            ->first();

        if ($customer) {
            return $customer;
        }

        return Customer::create([
            'customer_code' => $this->generateCustomerCode(),
            'full_name' => $name ?: 'Khach livestream #' . $lead->id,
            'phone' => $phone,
            'email' => $email !== '' ? $email : null,
            'source' => 'Livestream',
            'interested_car' => $lead->car?->title,
            'status' => Customer::STATUS_QUOTED,
            'note' => 'Tao tu live lead #' . $lead->id . '.',
            'created_by' => Auth::id(),
        ]);
    }

    private function findOrCreateUserFromLead(LiveLead $lead): User
    {
        if ($lead->user) {
            return $lead->user;
        }

        $email = trim((string) $lead->email);
        $phone = trim((string) $lead->phone);

        $query = User::query();

        if ($email !== '') {
            $existing = (clone $query)->where('email', $email)->first();

            if ($existing) {
                return $existing;
            }
        }

        if ($phone !== '') {
            $existing = User::query()->where('phone', $phone)->first();

            if ($existing) {
                return $existing;
            }
        }

        if ($email === '') {
            $email = 'livelead-' . $lead->id . '@luxauto.local';
        }

        return User::create([
            'name' => $lead->customer_name ?: 'Khach livestream #' . $lead->id,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : null,
            'password' => Hash::make(Str::random(16)),
            'role' => 'customer',
            'status' => true,
        ]);
    }

    private function generateCustomerCode(): string
    {
        do {
            $code = 'KH' . now()->format('ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Customer::where('customer_code', $code)->exists());

        return $code;
    }
}
