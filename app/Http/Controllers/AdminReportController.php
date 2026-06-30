<?php

namespace App\Http\Controllers;

use App\Exports\CustomerReportExport;
use App\Exports\DeliveryReportExport;
use App\Exports\InventoryReportExport;
use App\Exports\ReservationReportExport;
use App\Exports\ReviewsExport;
use App\Exports\SalesReportExport;
use App\Exports\ServiceReportExport;
use App\Exports\StaffReportExport;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\InventoryLog;
use App\Models\LiveLead;
use App\Models\LiveSession;
use App\Models\Order;
use App\Models\Quote;
use App\Models\Review;
use App\Models\ServiceAppointment;
use App\Models\ServiceRecord;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Warranty;
use App\Services\StockMovementService;
use App\Support\AfterSalesQuery;
use App\Support\AdminReportQuery;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class AdminReportController extends Controller
{
    public function __construct(private readonly StockMovementService $stockMovementService)
    {
    }

    public function sales(Request $request): View
    {
        $filters = $this->salesFilters($request);
        $ordersQuery = Order::query()
            ->with(['user', 'quote.user', 'depositConfirmer', 'details.car.carModel.brand', 'delivery'])
            ->select('orders.*');

        AdminReportQuery::applyOrderFilters($ordersQuery, $filters);

        $orders = $ordersQuery
            ->orderByDesc('created_at')
            ->orderByDesc('order_id')
            ->paginate(20)
            ->withQueryString();

        $orderStatsQuery = Order::query();
        AdminReportQuery::applyOrderFilters($orderStatsQuery, $filters);

        $completedRevenueQuery = AdminReportQuery::completedRevenueQuery($filters);
        $totalRevenue = (float) (clone $completedRevenueQuery)->sum('orders.total_price');
        $deliveredOrders = (int) (clone $completedRevenueQuery)->distinct('orders.order_id')->count('orders.order_id');
        $totalRemaining = (float) ((clone $completedRevenueQuery)
            ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(orders.total_price, 0) - COALESCE(orders.deposit_amount, 0) > 0 THEN COALESCE(orders.total_price, 0) - COALESCE(orders.deposit_amount, 0) ELSE 0 END), 0) as aggregate')
            ->value('aggregate') ?? 0);

        $statusCounts = (clone $orderStatsQuery)
            ->selectRaw('orders.status, COUNT(*) as aggregate')
            ->groupBy('orders.status')
            ->pluck('aggregate', 'orders.status');

        $cancelledOrders = (int) collect(AdminReportQuery::orderStatusValues(Order::STATUS_CANCELLED))
            ->sum(fn ($status): int => (int) ($statusCounts[$status] ?? 0));

        $depositedOrders = (int) collect(AdminReportQuery::orderStatusValues(Order::STATUS_DEPOSITED))
            ->sum(fn ($status): int => (int) ($statusCounts[$status] ?? 0));

        $totalDeposit = (float) (clone $orderStatsQuery)
            ->whereNotIn('orders.status', AdminReportQuery::orderStatusValues(Order::STATUS_CANCELLED))
            ->sum('orders.deposit_amount');

        $stats = [
            'total_revenue' => $totalRevenue,
            'total_orders' => (int) (clone $orderStatsQuery)->count(),
            'delivered_orders' => $deliveredOrders,
            'deposited_orders' => $depositedOrders,
            'cancelled_orders' => $cancelledOrders,
            'total_deposit' => $totalDeposit,
            'total_remaining' => $totalRemaining,
        ];

        return view('admin.reports.sales', [
            'filters' => $filters,
            'orders' => $orders,
            'stats' => $stats,
            'monthlyChart' => $this->salesMonthlyChart($filters),
            'brandChart' => $this->salesGroupChart($filters, 'brand'),
            'modelChart' => $this->salesGroupChart($filters, 'model'),
            'staffChart' => $this->salesStaffChart($filters),
            ...$this->vehicleAndStaffOptions(),
            'orderStatusOptions' => Order::statusOptions(),
        ]);
    }

    public function exportSales(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new SalesReportExport($this->salesFilters($request)),
            'luxauto-sales-report-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function inventory(Request $request): View
    {
        $filters = $this->inventoryFilters($request);
        $carsQuery = Car::query()->with(['carModel.brand']);
        AdminReportQuery::applyInventoryFilters($carsQuery, $filters);

        $cars = (clone $carsQuery)
            ->orderByRaw('(COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0)) asc')
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        $statsRow = (clone $carsQuery)
            ->selectRaw('COALESCE(SUM(COALESCE(stock_quantity, stock, 0)), 0) as physical_stock')
            ->selectRaw('COALESCE(SUM(COALESCE(reserved_quantity, 0)), 0) as reserved_stock')
            ->selectRaw('COALESCE(SUM(CASE WHEN COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0) > 0 THEN COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0) ELSE 0 END), 0) as available_stock')
            ->selectRaw('COALESCE(SUM(COALESCE(stock_quantity, stock, 0) * COALESCE(sale_price, list_price, price, 0)), 0) as inventory_value')
            ->first();

        $stats = [
            'physical_stock' => (int) ($statsRow?->physical_stock ?? 0),
            'reserved_stock' => (int) ($statsRow?->reserved_stock ?? 0),
            'available_stock' => (int) ($statsRow?->available_stock ?? 0),
            'out_of_stock' => (int) (clone $carsQuery)->whereRaw('COALESCE(stock_quantity, stock, 0) <= 0')->count(),
            'fully_reserved' => (int) (clone $carsQuery)
                ->whereRaw('COALESCE(stock_quantity, stock, 0) > 0')
                ->whereRaw('(COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0)) <= 0')
                ->count(),
            'inventory_value' => (float) ($statsRow?->inventory_value ?? 0),
        ];

        return view('admin.reports.inventory', [
            'filters' => $filters,
            'cars' => $cars,
            'stats' => $stats,
            'stockByBrandChart' => $this->inventoryBrandChart($filters),
            'oldStockCars' => (clone $carsQuery)
                ->whereNotNull('stock_in_date')
                ->where('stock_in_date', '<=', now()->subDays(90)->toDateString())
                ->orderBy('stock_in_date')
                ->take(8)
                ->get(),
            'lowStockCars' => (clone $carsQuery)
                ->whereRaw('(COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0)) BETWEEN 1 AND 2')
                ->orderByRaw('(COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0)) asc')
                ->orderBy('name')
                ->take(8)
                ->get(),
            ...$this->vehicleAndStaffOptions(),
            'stockStateOptions' => $this->stockStateOptions(),
            'carStatusOptions' => $this->carStatusOptions(),
        ]);
    }

    public function exportInventory(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new InventoryReportExport($this->inventoryFilters($request)),
            'luxauto-inventory-report-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function reservations(Request $request): View
    {
        $filters = $this->reservationFilters($request);
        $reservationsQuery = StockReservation::query()
            ->with(['order.user', 'car.carModel.brand', 'user', 'reservedBy']);
        AdminReportQuery::applyReservationFilters($reservationsQuery, $filters);

        $reservations = $reservationsQuery
            ->orderByDesc('reserved_at')
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $statFilters = $filters;
        $statFilters['status'] = '';
        $statQuery = StockReservation::query();
        AdminReportQuery::applyReservationFilters($statQuery, $statFilters);
        $statusCounts = (clone $statQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.reports.reservations', [
            'filters' => $filters,
            'reservations' => $reservations,
            'stats' => [
                'active' => (int) ($statusCounts[StockReservation::STATUS_ACTIVE] ?? 0),
                'completed' => (int) ($statusCounts[StockReservation::STATUS_COMPLETED] ?? 0),
                'cancelled' => (int) ($statusCounts[StockReservation::STATUS_CANCELLED] ?? 0),
                'released' => (int) ($statusCounts[StockReservation::STATUS_RELEASED] ?? 0),
                'expired' => (int) ($statusCounts[StockReservation::STATUS_EXPIRED] ?? 0),
            ],
            'reservationStatusOptions' => $this->reservationStatusOptions(),
            'carsForFilter' => $this->carsForFilter(),
            'customerUsers' => $this->customerUsersForFilter(),
        ]);
    }

    public function exportReservations(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new ReservationReportExport($this->reservationFilters($request)),
            'luxauto-reservation-report-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function deliveries(Request $request): View
    {
        $filters = $this->deliveryFilters($request);
        $deliveriesQuery = Delivery::query()
            ->with(['order.user', 'car.carModel.brand', 'deliveryStaff']);
        AdminReportQuery::applyDeliveryFilters($deliveriesQuery, $filters);

        $deliveries = $deliveriesQuery
            ->orderByRaw('CASE WHEN actual_delivery_date IS NULL THEN 1 ELSE 0 END')
            ->orderByDesc('actual_delivery_date')
            ->orderByDesc('expected_delivery_date')
            ->paginate(25)
            ->withQueryString();

        $statFilters = $filters;
        $statFilters['status'] = '';
        $statQuery = Delivery::query();
        AdminReportQuery::applyDeliveryFilters($statQuery, $statFilters);
        $statusCounts = (clone $statQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');
        $totalDeliveries = (int) (clone $statQuery)->count();
        $deliveredCount = (int) ($statusCounts[Delivery::STATUS_DELIVERED] ?? 0);

        return view('admin.reports.deliveries', [
            'filters' => $filters,
            'deliveries' => $deliveries,
            'stats' => [
                'total' => $totalDeliveries,
                'pending' => (int) ($statusCounts[Delivery::STATUS_PENDING] ?? 0),
                'preparing' => (int) ($statusCounts[Delivery::STATUS_PREPARING] ?? 0),
                'ready' => (int) ($statusCounts[Delivery::STATUS_READY] ?? 0),
                'delivered' => $deliveredCount,
                'cancelled' => (int) ($statusCounts[Delivery::STATUS_CANCELLED] ?? 0),
                'success_rate' => $this->safeRate($deliveredCount, $totalDeliveries),
            ],
            'deliveryStatusChart' => $this->deliveryStatusChart($filters),
            'deliveryStatusOptions' => Delivery::statusOptions(),
            'staff' => $this->staffOptions(),
        ]);
    }

    public function exportDeliveries(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new DeliveryReportExport($this->deliveryFilters($request)),
            'luxauto-delivery-report-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function services(Request $request): View
    {
        $filters = AfterSalesQuery::cleanRecordFilters($request->query());
        $recordsQuery = ServiceRecord::query()
            ->with(['serviceAppointment', 'warranty', 'user', 'car.carModel.brand', 'handledBy']);
        AfterSalesQuery::applyRecordFilters($recordsQuery, $filters);

        $records = $recordsQuery
            ->orderByDesc('service_date')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $statsQuery = ServiceRecord::query();
        AfterSalesQuery::applyRecordFilters($statsQuery, $filters);

        $appointmentsQuery = ServiceAppointment::query();
        if ($filters['service_type']) {
            $appointmentsQuery->where('service_type', $filters['service_type']);
        }
        if ($filters['handled_by']) {
            $appointmentsQuery->where('assigned_staff_id', $filters['handled_by']);
        }
        if ($filters['date_from']) {
            $appointmentsQuery->where('appointment_date', '>=', $filters['date_from']);
        }
        if ($filters['date_to']) {
            $appointmentsQuery->where('appointment_date', '<=', $filters['date_to']);
        }

        $topCarsQuery = ServiceRecord::query()
            ->join('cars', 'cars.car_id', '=', 'service_records.car_id')
            ->selectRaw('cars.car_id, cars.name, COUNT(*) as services_count, COALESCE(SUM(service_records.total_cost), 0) as total_cost')
            ->groupBy('cars.car_id', 'cars.name')
            ->orderByDesc('services_count')
            ->orderByDesc('total_cost')
            ->take(8);
        AfterSalesQuery::applyRecordFilters($topCarsQuery, $filters);

        return view('admin.reports.services', [
            'filters' => $filters,
            'records' => $records,
            'serviceTypeOptions' => ServiceAppointment::serviceTypeOptions(),
            'staff' => $this->staffOptions(),
            'stats' => [
                'appointments' => (clone $appointmentsQuery)->count(),
                'completed_appointments' => (clone $appointmentsQuery)->where('status', ServiceAppointment::STATUS_COMPLETED)->count(),
                'cancelled_appointments' => (clone $appointmentsQuery)->where('status', ServiceAppointment::STATUS_CANCELLED)->count(),
                'records' => (clone $statsQuery)->count(),
                'service_cost' => (float) (clone $statsQuery)->sum('total_cost'),
                'next_due' => ServiceRecord::query()->nextServiceWithin(30)->count(),
                'expiring_warranties' => Warranty::query()->expiringWithin(30)->count(),
            ],
            'statusOptions' => ServiceRecord::statusOptions(),
            'topCars' => $topCarsQuery->get(),
        ]);
    }

    public function exportServices(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new ServiceReportExport(AfterSalesQuery::cleanRecordFilters($request->query())),
            'luxauto-service-report-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function customers(Request $request): View
    {
        $filters = $this->customerFilters($request);
        $customersQuery = Customer::query()
            ->with(['creator'])
            ->withCount('quotes');
        AdminReportQuery::applyCustomerFilters($customersQuery, $filters);

        $customers = $customersQuery
            ->orderByDesc('created_at')
            ->orderByDesc('customer_id')
            ->paginate(25)
            ->withQueryString();

        $statsQuery = Customer::query();
        AdminReportQuery::applyCustomerFilters($statsQuery, $filters);

        return view('admin.reports.customers', [
            'filters' => $filters,
            'customers' => $customers,
            'stats' => [
                'new' => (int) (clone $statsQuery)->where('status', Customer::STATUS_NEW)->count(),
                'quoted' => (int) (clone $statsQuery)
                    ->where(function (Builder $query): void {
                        $query->where('status', Customer::STATUS_QUOTED)
                            ->orWhereHas('quotes');
                    })
                    ->count(),
                'test_drive' => (int) (clone $statsQuery)->where('status', Customer::STATUS_TEST_DRIVE)->count(),
                'deposit' => (int) (clone $statsQuery)->where('status', Customer::STATUS_DEPOSIT)->count(),
                'purchased' => (int) (clone $statsQuery)->where('status', Customer::STATUS_PURCHASED)->count(),
            ],
            'sourceChart' => $this->customerSourceChart($filters),
            'sourceOptions' => Customer::sourceOptions(),
            'customerStatusOptions' => Customer::STATUSES,
        ]);
    }

    public function exportCustomers(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new CustomerReportExport($this->customerFilters($request)),
            'luxauto-customer-report-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function staff(Request $request): View
    {
        $filters = $this->staffFilters($request);
        $rows = AdminReportQuery::staffRows($filters);

        return view('admin.reports.staff', [
            'filters' => $filters,
            'rows' => $rows,
            'stats' => [
                'customers' => (int) $rows->sum('customers_count'),
                'quotes' => (int) $rows->sum('quotes_count'),
                'accepted_quotes' => (int) $rows->sum('accepted_quotes_count'),
                'orders' => (int) $rows->sum('orders_count'),
                'delivered' => (int) $rows->sum('delivered_count'),
                'revenue' => (float) $rows->sum('revenue'),
                'closing_rate' => $this->safeRate((int) $rows->sum('orders_count'), (int) $rows->sum('quotes_count')),
            ],
            'staffRevenueChart' => [
                'labels' => $rows->sortByDesc('revenue')->take(8)->pluck('user.name')->values(),
                'data' => $rows->sortByDesc('revenue')->take(8)->pluck('revenue')->map(fn ($value): float => (float) $value)->values(),
            ],
            'staff' => $this->staffOptions(),
        ]);
    }

    public function exportStaff(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new StaffReportExport($this->staffFilters($request)),
            'luxauto-staff-report-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    public function conversion(Request $request): View
    {
        $filters = $this->dateFilters($request);
        $dateRange = AdminReportQuery::dateRange($filters);

        $testDriveQuery = Ticket::query()->where('ticket_type', Ticket::TYPE_TEST_DRIVE);
        $quoteQuery = Quote::query();
        $orderQuery = Order::query()
            ->whereNotIn('status', AdminReportQuery::orderStatusValues(Order::STATUS_CANCELLED));

        if ($dateRange) {
            $testDriveQuery->whereBetween('created_at', $dateRange);
            $quoteQuery->whereBetween('created_at', $dateRange);
            $orderQuery->whereBetween('created_at', $dateRange);
        }

        $testDriveTotal = (int) $testDriveQuery->count();
        $quoteTotal = (int) $quoteQuery->count();
        $orderTotal = (int) $orderQuery->count();
        $deliveredTotal = (int) AdminReportQuery::completedRevenueQuery($filters)
            ->distinct('orders.order_id')
            ->count('orders.order_id');

        $funnel = [
            [
                'label' => 'Lái thử',
                'value' => $testDriveTotal,
                'rate' => 100.0,
            ],
            [
                'label' => 'Báo giá',
                'value' => $quoteTotal,
                'rate' => $this->safeRate($quoteTotal, $testDriveTotal),
            ],
            [
                'label' => 'Đơn hàng',
                'value' => $orderTotal,
                'rate' => $this->safeRate($orderTotal, $quoteTotal),
            ],
            [
                'label' => 'Giao xe',
                'value' => $deliveredTotal,
                'rate' => $this->safeRate($deliveredTotal, $orderTotal),
            ],
        ];

        return view('admin.reports.conversion', [
            'filters' => $filters,
            'stats' => [
                'test_drives' => $testDriveTotal,
                'quotes' => $quoteTotal,
                'orders' => $orderTotal,
                'delivered' => $deliveredTotal,
                'test_drive_to_quote' => $this->safeRate($quoteTotal, $testDriveTotal),
                'quote_to_order' => $this->safeRate($orderTotal, $quoteTotal),
                'order_to_delivery' => $this->safeRate($deliveredTotal, $orderTotal),
            ],
            'funnel' => $funnel,
            'funnelChart' => [
                'labels' => collect($funnel)->pluck('label')->values(),
                'data' => collect($funnel)->pluck('value')->values(),
                'rates' => collect($funnel)->pluck('rate')->values(),
            ],
            'quoteStatusChart' => $this->quoteStatusChart($filters),
        ]);
    }

    public function live(Request $request): View
    {
        $filters = [
            ...$this->dateFilters($request),
            'live_session_id' => $this->intFilter($request, 'live_session_id'),
            'car_id' => $this->intFilter($request, 'car_id'),
            'assigned_to' => $this->intFilter($request, 'assigned_to'),
        ];

        $leadsQuery = LiveLead::query()
            ->with(['liveSession', 'car.carModel.brand', 'assignedTo', 'quote', 'testDrive']);

        $this->applyLiveLeadFilters($leadsQuery, $filters);

        $leads = (clone $leadsQuery)
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $quoteQuery = Quote::query()->where(function (Builder $query): void {
            $query->whereNotNull('live_session_id')
                ->orWhereNotNull('live_lead_id');
        });
        $orderQuery = Order::query()
            ->join('quotes', 'quotes.quote_id', '=', 'orders.quote_id')
            ->where(function (Builder $query): void {
                $query->whereNotNull('quotes.live_session_id')
                    ->orWhereNotNull('quotes.live_lead_id');
            });

        if ($filters['live_session_id']) {
            $quoteQuery->where('live_session_id', $filters['live_session_id']);
            $orderQuery->where('quotes.live_session_id', $filters['live_session_id']);
        }

        if ($filters['car_id']) {
            $quoteQuery->where('car_id', $filters['car_id']);
            $orderQuery->join('order_details', 'order_details.order_id', '=', 'orders.order_id')
                ->where('order_details.car_id', $filters['car_id']);
        }

        if ($dateRange = AdminReportQuery::dateRange($filters)) {
            $quoteQuery->whereBetween('created_at', $dateRange);
            $orderQuery->whereBetween('orders.created_at', $dateRange);
        }

        $leadStatsQuery = clone $leadsQuery;
        $totalLeads = (int) (clone $leadStatsQuery)->count();
        $convertedLeads = (int) (clone $leadStatsQuery)->where('status', LiveLead::STATUS_CONVERTED)->count();

        $topCars = (clone $leadsQuery)
            ->whereNotNull('live_leads.car_id')
            ->join('cars', 'cars.car_id', '=', 'live_leads.car_id')
            ->leftJoin('car_models', 'car_models.id', '=', 'cars.car_model_id')
            ->leftJoin('brands', 'brands.brand_id', '=', 'car_models.brand_id')
            ->selectRaw('cars.car_id, cars.name, car_models.name as model_name, brands.name as brand_name, COUNT(*) as leads_count')
            ->groupBy('cars.car_id', 'cars.name', 'car_models.name', 'brands.name')
            ->orderByDesc('leads_count')
            ->take(8)
            ->get();

        $topSessions = (clone $leadsQuery)
            ->join('live_sessions', 'live_sessions.id', '=', 'live_leads.live_session_id')
            ->selectRaw('live_sessions.id, live_sessions.live_code, live_sessions.title, COUNT(*) as leads_count')
            ->groupBy('live_sessions.id', 'live_sessions.live_code', 'live_sessions.title')
            ->orderByDesc('leads_count')
            ->take(8)
            ->get();

        return view('admin.reports.live', [
            'filters' => $filters,
            'leads' => $leads,
            'stats' => [
                'sessions' => LiveSession::count(),
                'completed_sessions' => LiveSession::whereIn('status', [LiveSession::STATUS_LIVE, LiveSession::STATUS_ENDED])->count(),
                'leads' => $totalLeads,
                'quote_requests' => (int) (clone $leadStatsQuery)->where('lead_type', LiveLead::TYPE_QUOTE_REQUEST)->count(),
                'test_drive_requests' => (int) (clone $leadStatsQuery)->where('lead_type', LiveLead::TYPE_TEST_DRIVE_REQUEST)->count(),
                'deposit_interests' => (int) (clone $leadStatsQuery)->where('lead_type', LiveLead::TYPE_DEPOSIT_INTEREST)->count(),
                'converted_leads' => $convertedLeads,
                'conversion_rate' => $this->safeRate($convertedLeads, $totalLeads),
                'quotes_from_live' => (int) $quoteQuery->count(),
                'orders_from_live' => (int) $orderQuery->distinct('orders.order_id')->count('orders.order_id'),
            ],
            'topCars' => $topCars,
            'topSessions' => $topSessions,
            'sessions' => LiveSession::query()->orderByDesc('starts_at')->orderByDesc('created_at')->get(['id', 'live_code', 'title']),
            'cars' => $this->carsForFilter(),
            'staff' => $this->staffOptions(),
            'leadStatusOptions' => LiveLead::STATUSES,
            'leadTypeOptions' => LiveLead::TYPES,
        ]);
    }

    public function inventoryCheck(): View
    {
        $cars = Car::with('brand')->orderBy('name')->get();
        $logs = InventoryLog::with('car.brand')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();

        return view('admin.reports.inventory_check', compact('cars', 'logs'));
    }

    public function storeInventoryLog(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'car_id' => 'required|exists:cars,car_id',
            'change_qty' => 'required|integer|not_in:0',
            'note' => 'nullable|string|max:500',
        ]);

        try {
            DB::transaction(function () use ($data, $request) {
                $car = Car::lockForUpdate()->findOrFail($data['car_id']);
                $oldStock = (int) ($car->stock_quantity ?? $car->stock ?? 0);
                $newStock = $oldStock + $data['change_qty'];
                if ($newStock < 0) {
                    throw new \InvalidArgumentException('Tồn kho sau điều chỉnh không được âm.');
                }
                if ($newStock < $car->reservedStock()) {
                    throw new \InvalidArgumentException('Không thể điều chỉnh tồn vật lý thấp hơn số lượng xe đang được giữ.');
                }
                $car->update([
                    'stock' => $newStock,
                    'stock_quantity' => $newStock,
                ]);
                InventoryLog::create([
                    'car_id' => $car->car_id,
                    'change_qty' => $data['change_qty'],
                    'note' => $data['note'] ?? null,
                ]);

                $this->stockMovementService->recordMovement(
                    $car,
                    $oldStock,
                    (int) $data['change_qty'],
                    $newStock,
                    StockMovement::ACTION_INVENTORY_CHECK,
                    'Kiểm tra và điều chỉnh tồn kho.',
                    $data['note'] ?? null,
                    null,
                    $request
                );
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.reports.inventory_check')->withErrors(['car_id' => $e->getMessage()]);
        }

        return redirect()->route('admin.reports.inventory_check')->with('success', 'Đã ghi nhận kiểm kho và cập nhật tồn.');
    }

    public function reviews(Request $request): View
    {
        $filters = \App\Support\ReviewQuery::cleanFilters($request);

        $reviewsQuery = Review::query()
            ->with(['user', 'car.carModel.brand'])
            ->withCount('images');
        \App\Support\ReviewQuery::applyFilters($reviewsQuery, $filters);

        $statsQuery = Review::query();
        \App\Support\ReviewQuery::applyFilters($statsQuery, $filters);

        $approvedQuery = (clone $statsQuery)->where('status', Review::STATUS_APPROVED);
        $approvedCount = (int) (clone $approvedQuery)->count();
        $positiveCount = (int) (clone $approvedQuery)->where('rating', '>=', 4)->count();
        $statusCounts = (clone $statsQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $stats = [
            'avg_rating' => (float) ((clone $approvedQuery)->avg('rating') ?? 0),
            'total' => (int) (clone $statsQuery)->count(),
            'pending' => (int) ($statusCounts[Review::STATUS_PENDING] ?? 0),
            'approved' => $approvedCount,
            'rejected' => (int) ($statusCounts[Review::STATUS_REJECTED] ?? 0),
            'low_rating' => (int) (clone $statsQuery)->where('rating', '<=', 2)->count(),
            'positive_rate' => $this->safeRate($positiveCount, $approvedCount),
        ];

        $distribution = (clone $statsQuery)
            ->selectRaw('rating, COUNT(*) as cnt')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('cnt', 'rating');

        $reviews = $reviewsQuery
            ->orderByDesc('created_at')
            ->orderByDesc('review_id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.reports.reviews', [
            'filters' => $filters,
            'reviews' => $reviews,
            'stats' => $stats,
            'distribution' => $distribution,
            'monthlyChart' => $this->reviewsMonthlyChart($filters),
            'brandChart' => $this->reviewsBrandChart($filters),
            'topHighCars' => $this->reviewsTopCars($filters, 'high'),
            'topLowCars' => $this->reviewsTopCars($filters, 'low'),
            'badReviews' => (clone $statsQuery)
                ->with(['user', 'car'])
                ->where(function (Builder $query): void {
                    $query->where('rating', '<=', 2)
                        ->orWhere('status', Review::STATUS_REPORTED);
                })
                ->orderByDesc('created_at')
                ->take(8)
                ->get(),
            'statusOptions' => Review::statusOptions(),
            'verifiedTypeOptions' => Review::verifiedTypeOptions(),
            ...$this->vehicleAndStaffOptions(),
            'cars' => $this->carsForFilter(),
        ]);
    }

    public function exportReviews(Request $request): BinaryFileResponse
    {
        return Excel::download(
            new ReviewsExport(\App\Support\ReviewQuery::cleanFilters($request)),
            'luxauto-review-report-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    private function salesMonthlyChart(array $filters): array
    {
        [$months, $windowFrom, $windowTo] = $this->monthWindow($filters['date_to'] ?? null);
        $chartFilters = $this->filtersWithoutDates($filters);
        $dateExpression = 'COALESCE(deliveries.actual_delivery_date, orders.created_at)';
        $monthExpression = AdminReportQuery::monthKeyExpression($dateExpression);

        $rows = AdminReportQuery::completedRevenueQuery($chartFilters)
            ->whereBetween(DB::raw($dateExpression), [$windowFrom, $windowTo])
            ->selectRaw($monthExpression . ' as month_key')
            ->selectRaw('COALESCE(SUM(orders.total_price), 0) as revenue')
            ->selectRaw('COUNT(DISTINCT orders.order_id) as orders_count')
            ->groupBy('month_key')
            ->get()
            ->keyBy('month_key');

        return [
            'labels' => $months->pluck('label')->values(),
            'revenue' => $months->map(fn (array $month): float => (float) ($rows[$month['key']]->revenue ?? 0))->values(),
            'orders' => $months->map(fn (array $month): int => (int) ($rows[$month['key']]->orders_count ?? 0))->values(),
        ];
    }

    private function salesGroupChart(array $filters, string $group): array
    {
        $query = AdminReportQuery::completedRevenueQuery($filters)
            ->join('order_details', 'order_details.order_id', '=', 'orders.order_id')
            ->join('cars', 'cars.car_id', '=', 'order_details.car_id')
            ->join('car_models', 'car_models.id', '=', 'cars.car_model_id');

        if ($group === 'brand') {
            $query->join('brands', 'brands.brand_id', '=', 'car_models.brand_id')
                ->selectRaw('brands.name as label');
        } else {
            $query->selectRaw('car_models.name as label');
        }

        $rows = $query
            ->selectRaw('COALESCE(SUM(order_details.price * order_details.quantity), 0) as revenue')
            ->selectRaw('COALESCE(SUM(order_details.quantity), 0) as quantity')
            ->groupBy('label')
            ->orderByDesc('revenue')
            ->take(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->values(),
            'revenue' => $rows->pluck('revenue')->map(fn ($value): float => (float) $value)->values(),
            'quantity' => $rows->pluck('quantity')->map(fn ($value): int => (int) $value)->values(),
        ];
    }

    private function salesStaffChart(array $filters): array
    {
        $rows = AdminReportQuery::completedRevenueQuery($filters)
            ->leftJoin('quotes', 'quotes.quote_id', '=', 'orders.quote_id')
            ->leftJoin('users', 'users.user_id', '=', DB::raw('COALESCE(quotes.user_id, orders.deposit_confirmed_by)'))
            ->whereNotNull('users.user_id')
            ->selectRaw('users.name as label')
            ->selectRaw('COALESCE(SUM(orders.total_price), 0) as revenue')
            ->groupBy('users.user_id', 'users.name')
            ->orderByDesc('revenue')
            ->take(8)
            ->get();

        return [
            'labels' => $rows->pluck('label')->values(),
            'revenue' => $rows->pluck('revenue')->map(fn ($value): float => (float) $value)->values(),
        ];
    }

    private function inventoryBrandChart(array $filters): array
    {
        $query = Car::query()
            ->join('car_models', 'car_models.id', '=', 'cars.car_model_id')
            ->join('brands', 'brands.brand_id', '=', 'car_models.brand_id')
            ->selectRaw('brands.name as label')
            ->selectRaw('COALESCE(SUM(COALESCE(cars.stock_quantity, cars.stock, 0)), 0) as physical_stock')
            ->selectRaw('COALESCE(SUM(COALESCE(cars.reserved_quantity, 0)), 0) as reserved_stock')
            ->groupBy('brands.brand_id', 'brands.name')
            ->orderByDesc('physical_stock')
            ->take(10);

        AdminReportQuery::applyInventoryFilters($query, $filters);

        $rows = $query->get();

        return [
            'labels' => $rows->pluck('label')->values(),
            'physical' => $rows->pluck('physical_stock')->map(fn ($value): int => (int) $value)->values(),
            'reserved' => $rows->pluck('reserved_stock')->map(fn ($value): int => (int) $value)->values(),
        ];
    }

    private function deliveryStatusChart(array $filters): array
    {
        $query = Delivery::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status');
        AdminReportQuery::applyDeliveryFilters($query, $filters);

        $rows = $query->pluck('aggregate', 'status');

        return [
            'labels' => array_values(Delivery::statusOptions()),
            'data' => collect(array_keys(Delivery::statusOptions()))
                ->map(fn (string $status): int => (int) ($rows[$status] ?? 0))
                ->values(),
        ];
    }

    private function quoteStatusChart(array $filters): array
    {
        $query = Quote::query()
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status');

        if ($dateRange = AdminReportQuery::dateRange($filters)) {
            $query->whereBetween('created_at', $dateRange);
        }

        $rows = $query->pluck('aggregate', 'status');

        return [
            'labels' => array_values(Quote::STATUSES),
            'data' => collect(array_keys(Quote::STATUSES))
                ->map(fn (string $status): int => (int) ($rows[$status] ?? 0))
                ->values(),
            ];
    }

    private function reviewsMonthlyChart(array $filters): array
    {
        [$months, $windowFrom, $windowTo] = $this->monthWindow($filters['date_to'] ?? null);
        $chartFilters = $this->filtersWithoutDates($filters);
        $monthExpression = AdminReportQuery::monthKeyExpression('reviews.created_at');

        $query = Review::query()
            ->leftJoin('cars', 'cars.car_id', '=', 'reviews.car_id')
            ->leftJoin('car_models', 'car_models.id', '=', 'cars.car_model_id')
            ->leftJoin('brands', 'brands.brand_id', '=', 'car_models.brand_id')
            ->whereBetween('reviews.created_at', [$windowFrom, $windowTo])
            ->selectRaw($monthExpression . ' as month_key')
            ->selectRaw('COUNT(DISTINCT reviews.review_id) as reviews_count')
            ->selectRaw('ROUND(AVG(reviews.rating), 2) as avg_rating')
            ->groupBy('month_key');

        $this->applyReviewJoinedFilters($query, $chartFilters);

        $rows = $query->get()->keyBy('month_key');

        return [
            'labels' => $months->pluck('label')->values(),
            'counts' => $months->map(fn (array $month): int => (int) ($rows[$month['key']]->reviews_count ?? 0))->values(),
            'avg' => $months->map(fn (array $month): float => (float) ($rows[$month['key']]->avg_rating ?? 0))->values(),
        ];
    }

    private function reviewsBrandChart(array $filters): array
    {
        $query = Review::query()
            ->join('cars', 'cars.car_id', '=', 'reviews.car_id')
            ->join('car_models', 'car_models.id', '=', 'cars.car_model_id')
            ->join('brands', 'brands.brand_id', '=', 'car_models.brand_id')
            ->where('reviews.status', Review::STATUS_APPROVED)
            ->selectRaw('brands.name as label')
            ->selectRaw('COUNT(*) as reviews_count')
            ->selectRaw('ROUND(AVG(reviews.rating), 2) as avg_rating')
            ->groupBy('brands.brand_id', 'brands.name')
            ->orderByDesc('reviews_count')
            ->take(8);

        $this->applyReviewJoinedFilters($query, $filters);

        $rows = $query->get();

        return [
            'labels' => $rows->pluck('label')->values(),
            'counts' => $rows->pluck('reviews_count')->map(fn ($value): int => (int) $value)->values(),
            'avg' => $rows->pluck('avg_rating')->map(fn ($value): float => (float) $value)->values(),
        ];
    }

    private function reviewsTopCars(array $filters, string $mode)
    {
        $query = Review::query()
            ->join('cars', 'cars.car_id', '=', 'reviews.car_id')
            ->leftJoin('car_models', 'car_models.id', '=', 'cars.car_model_id')
            ->leftJoin('brands', 'brands.brand_id', '=', 'car_models.brand_id')
            ->where('reviews.status', Review::STATUS_APPROVED)
            ->selectRaw('cars.car_id, cars.name, brands.name as brand_name, car_models.name as model_name')
            ->selectRaw('COUNT(*) as reviews_count')
            ->selectRaw('ROUND(AVG(reviews.rating), 2) as avg_rating')
            ->groupBy('cars.car_id', 'cars.name', 'brands.name', 'car_models.name')
            ->havingRaw('COUNT(*) > 0')
            ->take(8);

        $this->applyReviewJoinedFilters($query, $filters);

        if ($mode === 'low') {
            $query->orderBy('avg_rating')->orderByDesc('reviews_count');
        } else {
            $query->orderByDesc('avg_rating')->orderByDesc('reviews_count');
        }

        return $query->get();
    }

    private function applyReviewJoinedFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['q'] ?? '', function (Builder $query, string $search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('reviews.title', 'like', "%{$search}%")
                        ->orWhere('reviews.comment', 'like', "%{$search}%")
                        ->orWhere('cars.name', 'like', "%{$search}%");
                });
            })
            ->when($filters['car_id'] ?? null, fn (Builder $query, int $carId) => $query->where('reviews.car_id', $carId))
            ->when($filters['brand_id'] ?? null, fn (Builder $query, int $brandId) => $query->where('brands.brand_id', $brandId))
            ->when($filters['model_id'] ?? null, fn (Builder $query, int $modelId) => $query->where('cars.car_model_id', $modelId))
            ->when($filters['rating'] ?? null, fn (Builder $query, int $rating) => $query->where('reviews.rating', $rating))
            ->when(($filters['status'] ?? '') !== '', fn (Builder $query) => $query->where('reviews.status', $filters['status']))
            ->when(($filters['verified_type'] ?? '') !== '', fn (Builder $query) => $query->where('reviews.verified_type', $filters['verified_type']))
            ->when($filters['date_from'] ?? '', function (Builder $query, string $date): void {
                $query->where('reviews.created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($filters['date_to'] ?? '', function (Builder $query, string $date): void {
                $query->where('reviews.created_at', '<=', Carbon::parse($date)->endOfDay());
            });

        if (($filters['has_images'] ?? '') === '1') {
            $query->whereExists(function ($subQuery): void {
                $subQuery->selectRaw('1')
                    ->from('review_images')
                    ->whereColumn('review_images.review_id', 'reviews.review_id');
            });
        }

        if (($filters['has_images'] ?? '') === '0') {
            $query->whereNotExists(function ($subQuery): void {
                $subQuery->selectRaw('1')
                    ->from('review_images')
                    ->whereColumn('review_images.review_id', 'reviews.review_id');
            });
        }
    }

    private function applyLiveLeadFilters(Builder $query, array $filters): void
    {
        $query
            ->when($filters['live_session_id'] ?? null, fn (Builder $query, int $sessionId): Builder => $query->where('live_session_id', $sessionId))
            ->when($filters['car_id'] ?? null, fn (Builder $query, int $carId): Builder => $query->where('live_leads.car_id', $carId))
            ->when($filters['assigned_to'] ?? null, fn (Builder $query, int $userId): Builder => $query->where('assigned_to', $userId))
            ->when($filters['date_from'] ?? '', function (Builder $query, string $date): void {
                $query->where('live_leads.created_at', '>=', Carbon::parse($date)->startOfDay());
            })
            ->when($filters['date_to'] ?? '', function (Builder $query, string $date): void {
                $query->where('live_leads.created_at', '<=', Carbon::parse($date)->endOfDay());
            });
    }

    private function customerSourceChart(array $filters): array
    {
        $query = Customer::query()
            ->selectRaw("COALESCE(NULLIF(source, ''), 'Khác') as label, COUNT(*) as aggregate")
            ->groupBy('label')
            ->orderByDesc('aggregate');
        AdminReportQuery::applyCustomerFilters($query, $filters);

        $rows = $query->get();

        return [
            'labels' => $rows->pluck('label')->values(),
            'data' => $rows->pluck('aggregate')->map(fn ($value): int => (int) $value)->values(),
        ];
    }

    private function monthWindow(?string $endingDate): array
    {
        $windowTo = $endingDate ? Carbon::parse($endingDate)->endOfMonth() : now()->endOfMonth();
        $windowFrom = $windowTo->copy()->subMonthsNoOverflow(11)->startOfMonth();

        $months = collect(range(0, 11))->map(function (int $offset) use ($windowFrom): array {
            $month = $windowFrom->copy()->addMonthsNoOverflow($offset);

            return [
                'key' => $month->format('Y-m'),
                'label' => $month->format('m/Y'),
            ];
        });

        return [$months, $windowFrom, $windowTo];
    }

    private function vehicleAndStaffOptions(): array
    {
        return [
            'brands' => Brand::query()->orderBy('name')->get(['brand_id', 'name']),
            'models' => CarModel::query()->with('brand')->orderBy('name')->get(['id', 'brand_id', 'name']),
            'staff' => $this->staffOptions(),
        ];
    }

    private function staffOptions()
    {
        return User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->orderBy('name')
            ->get(['user_id', 'name', 'email']);
    }

    private function carsForFilter()
    {
        return Car::query()
            ->with('carModel.brand')
            ->orderBy('name')
            ->get(['car_id', 'car_model_id', 'name', 'vin', 'internal_code']);
    }

    private function customerUsersForFilter()
    {
        return User::query()
            ->where('role', 'customer')
            ->orderBy('name')
            ->limit(300)
            ->get(['user_id', 'name', 'email', 'phone']);
    }

    private function carStatusOptions()
    {
        return Car::query()
            ->whereNotNull('status')
            ->select('status')
            ->distinct()
            ->orderBy('status')
            ->pluck('status', 'status');
    }

    private function stockStateOptions(): array
    {
        return [
            'out_of_stock' => 'Hết hàng',
            'fully_reserved' => 'Đã giữ hết',
            'available' => 'Còn có thể bán',
        ];
    }

    private function reservationStatusOptions(): array
    {
        return [
            StockReservation::STATUS_ACTIVE => 'Đang giữ',
            StockReservation::STATUS_COMPLETED => 'Hoàn tất',
            StockReservation::STATUS_CANCELLED => 'Đã hủy',
            StockReservation::STATUS_RELEASED => 'Đã giải phóng',
            StockReservation::STATUS_EXPIRED => 'Hết hạn',
        ];
    }

    private function salesFilters(Request $request): array
    {
        $filters = $this->dateFilters($request);
        $status = Order::normalizeStatus($request->input('status'));

        return [
            ...$filters,
            'brand_id' => $this->intFilter($request, 'brand_id'),
            'model_id' => $this->intFilter($request, 'model_id'),
            'user_id' => $this->intFilter($request, 'user_id'),
            'status' => $status === null ? '' : (string) $status,
        ];
    }

    private function inventoryFilters(Request $request): array
    {
        return [
            'brand_id' => $this->intFilter($request, 'brand_id'),
            'model_id' => $this->intFilter($request, 'model_id'),
            'status' => trim((string) $request->input('status', '')),
            'stock_state' => in_array($request->input('stock_state'), array_keys($this->stockStateOptions()), true)
                ? (string) $request->input('stock_state')
                : '',
        ];
    }

    private function reservationFilters(Request $request): array
    {
        $status = (string) $request->input('status', '');

        return [
            ...$this->dateFilters($request),
            'status' => in_array($status, StockReservation::STATUSES, true) ? $status : '',
            'car_id' => $this->intFilter($request, 'car_id'),
            'user_id' => $this->intFilter($request, 'user_id'),
        ];
    }

    private function deliveryFilters(Request $request): array
    {
        $status = (string) $request->input('status', '');

        return [
            ...$this->dateFilters($request),
            'status' => in_array($status, Delivery::STATUSES, true) ? $status : '',
            'delivery_staff_id' => $this->intFilter($request, 'delivery_staff_id'),
        ];
    }

    private function customerFilters(Request $request): array
    {
        $status = (string) $request->input('status', '');
        $source = (string) $request->input('source', '');
        $sources = array_keys(Customer::sourceOptions());

        return [
            ...$this->dateFilters($request),
            'status' => array_key_exists($status, Customer::STATUSES) ? $status : '',
            'source' => in_array($source, $sources, true) ? $source : '',
        ];
    }

    private function staffFilters(Request $request): array
    {
        return [
            ...$this->dateFilters($request),
            'user_id' => $this->intFilter($request, 'user_id'),
        ];
    }

    private function dateFilters(Request $request): array
    {
        return [
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
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable) {
            return '';
        }
    }

    private function intFilter(Request $request, string $key): ?int
    {
        $value = $request->input($key);

        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private function filtersWithoutDates(array $filters): array
    {
        $filters['date_from'] = '';
        $filters['date_to'] = '';

        return $filters;
    }

    private function safeRate(int|float $numerator, int|float $denominator): float
    {
        return $denominator > 0 ? round(((float) $numerator / (float) $denominator) * 100, 1) : 0.0;
    }
}
