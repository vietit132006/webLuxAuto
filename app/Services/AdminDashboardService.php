<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Quote;
use App\Models\StockMovement;
use App\Models\StockReservation;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class AdminDashboardService
{
    public function build(Request $request, ?Authenticatable $user): array
    {
        $permissions = $this->permissions($user);
        $range = $this->resolveRange($request);
        $stockExpressions = $this->stockExpressions();

        $inventory = $permissions['inventory']
            ? $this->inventoryStats($stockExpressions)
            : null;

        $customerStats = $permissions['customers']
            ? $this->customerStats($range)
            : null;

        $testDriveStats = $permissions['test_drives']
            ? $this->testDriveStats($range)
            : null;

        $quoteStats = $permissions['quotes']
            ? $this->quoteStats($range)
            : null;

        $orderStats = $permissions['orders']
            ? $this->orderStats($range)
            : null;

        $deliveryStats = $permissions['orders']
            ? $this->deliveryStats($range)
            : null;

        $revenueStats = $permissions['reports']
            ? $this->revenueStats($range)
            : null;

        return [
            'dashboardRange' => $range,
            'dashboardRangeOptions' => $this->rangeOptions(),
            'dashboardStats' => $this->statCards(
                $range,
                $inventory,
                $customerStats,
                $testDriveStats,
                $quoteStats,
                $orderStats,
                $deliveryStats,
                $revenueStats
            ),
            'dashboardCharts' => $this->charts($range, $permissions),
            'recentCars' => $permissions['cars'] ? $this->recentCars() : collect(),
            'recentStockMovements' => $permissions['inventory_history'] ? $this->recentStockMovements($range) : collect(),
            'recentOrders' => $permissions['orders'] ? $this->recentOrders($range) : collect(),
            'upcomingDeliveries' => $permissions['orders'] ? $this->upcomingDeliveries($range) : collect(),
            'topSellingCars' => $permissions['orders'] ? $this->topSellingCars($range) : collect(),
            'topInterestedCars' => ($permissions['quotes'] || $permissions['test_drives']) ? $this->topInterestedCars($range, $permissions) : collect(),
            'dashboardMeta' => [
                'physical_stock_column' => $stockExpressions['physical_column'],
                'reserved_stock_column' => $stockExpressions['reserved_column'],
            ],
            'canViewCars' => $permissions['cars'],
            'canViewInventory' => $permissions['inventory'],
            'canViewInventoryHistory' => $permissions['inventory_history'],
            'canViewReports' => $permissions['reports'],
            'canViewCustomers' => $permissions['customers'],
            'canViewQuotes' => $permissions['quotes'],
            'canViewOrders' => $permissions['orders'],
            'canViewTestDrives' => $permissions['test_drives'],
        ];
    }

    private function permissions(?Authenticatable $user): array
    {
        return [
            'cars' => $user?->can('cars.view') ?? false,
            'inventory' => $user?->can('inventory.view') ?? false,
            'inventory_history' => $user?->can('inventory.history') ?? false,
            'reports' => $user?->can('reports.view') ?? false,
            'customers' => $user?->can('customers.view') ?? false,
            'quotes' => $user?->can('quotes.view') ?? false,
            'orders' => $user?->can('orders.view') ?? false,
            'test_drives' => $user?->can('test_drives.view') ?? false,
        ];
    }

    private function resolveRange(Request $request): array
    {
        $now = now();
        $rangeKey = (string) $request->query('range', 'month');
        $fromInput = $request->query('from');
        $toInput = $request->query('to');

        if ($fromInput || $toInput) {
            $rangeKey = 'custom';
            $from = ($this->parseDate($fromInput) ?: $now->copy()->startOfMonth())->startOfDay();
            $to = ($this->parseDate($toInput) ?: $now->copy())->endOfDay();
        } else {
            [$from, $to] = match ($rangeKey) {
                'today' => [$now->copy()->startOfDay(), $now->copy()->endOfDay()],
                '7' => [$now->copy()->subDays(6)->startOfDay(), $now->copy()->endOfDay()],
                '30' => [$now->copy()->subDays(29)->startOfDay(), $now->copy()->endOfDay()],
                'year' => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
                default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth()],
            };

            if (!in_array($rangeKey, ['today', '7', '30', 'month', 'year'], true)) {
                $rangeKey = 'month';
            }
        }

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        return [
            'key' => $rangeKey,
            'from' => $from,
            'to' => $to,
            'from_input' => $from->toDateString(),
            'to_input' => $to->toDateString(),
            'label' => $from->isSameDay($to)
                ? $from->format('d/m/Y')
                : $from->format('d/m/Y') . ' - ' . $to->format('d/m/Y'),
        ];
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (Throwable) {
            return null;
        }
    }

    private function rangeOptions(): array
    {
        return [
            'today' => 'Hôm nay',
            '7' => '7 ngày',
            '30' => '30 ngày',
            'month' => 'Tháng này',
            'year' => 'Năm nay',
            'custom' => 'Tùy chọn',
        ];
    }

    private function stockExpressions(): array
    {
        $physicalColumn = Schema::hasColumn('cars', 'stock_quantity')
            ? 'stock_quantity'
            : (Schema::hasColumn('cars', 'stock') ? 'stock' : null);
        $reservedColumn = Schema::hasColumn('cars', 'reserved_quantity') ? 'reserved_quantity' : null;

        return [
            'physical' => $physicalColumn ? 'COALESCE(' . $physicalColumn . ', 0)' : '0',
            'reserved' => $reservedColumn ? 'COALESCE(' . $reservedColumn . ', 0)' : '0',
            'physical_column' => $physicalColumn,
            'reserved_column' => $reservedColumn,
        ];
    }

    private function inventoryStats(array $stockExpressions): array
    {
        $physicalExpression = $stockExpressions['physical'];
        $reservedExpression = $stockExpressions['reserved'];
        $availableExpression = '(' . $physicalExpression . ' - ' . $reservedExpression . ')';

        $physical = (int) (Car::query()
            ->selectRaw('COALESCE(SUM(' . $physicalExpression . '), 0) as aggregate')
            ->value('aggregate') ?? 0);

        $reserved = (int) (Car::query()
            ->selectRaw('COALESCE(SUM(' . $reservedExpression . '), 0) as aggregate')
            ->value('aggregate') ?? 0);

        $available = (int) (Car::query()
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN ' . $availableExpression . ' > 0 THEN ' . $availableExpression . ' ELSE 0 END), 0) as aggregate'
            )
            ->value('aggregate') ?? 0);

        $activeReservations = (int) StockReservation::query()
            ->where('status', StockReservation::STATUS_ACTIVE)
            ->sum('quantity');

        return compact('physical', 'reserved', 'available', 'activeReservations');
    }

    private function customerStats(array $range): array
    {
        return [
            'new' => Customer::query()
                ->whereBetween('created_at', [$range['from'], $range['to']])
                ->count(),
        ];
    }

    private function testDriveStats(array $range): array
    {
        $periodQuery = $this->testDrivePeriodQuery($range);
        $completed = (clone $periodQuery)->where('status', Ticket::STATUS_COMPLETED)->count();
        $total = (clone $periodQuery)->count();

        return [
            'total' => $total,
            'today_or_period' => $total,
            'completed' => $completed,
        ];
    }

    private function quoteStats(array $range): array
    {
        $periodQuery = Quote::query()
            ->whereBetween('created_at', [$range['from'], $range['to']]);
        $total = (clone $periodQuery)->count();
        $accepted = (clone $periodQuery)->where('status', Quote::STATUS_ACCEPTED)->count();

        return [
            'pending' => (clone $periodQuery)
                ->whereIn('status', [Quote::STATUS_DRAFT, Quote::STATUS_SENT])
                ->count(),
            'total' => $total,
            'accepted' => $accepted,
            'conversion_rate' => $total > 0 ? round(($accepted / $total) * 100, 1) : 0.0,
        ];
    }

    private function orderStats(array $range): array
    {
        return [
            'deposited' => Order::query()
                ->whereIn('status', $this->orderStatusValues(Order::STATUS_DEPOSITED))
                ->whereBetween('created_at', [$range['from'], $range['to']])
                ->count(),
        ];
    }

    private function deliveryStats(array $range): array
    {
        return [
            'waiting' => Delivery::query()
                ->whereIn('status', [Delivery::STATUS_PENDING, Delivery::STATUS_PREPARING, Delivery::STATUS_READY])
                ->where(function (Builder $query) use ($range): void {
                    $query->whereBetween('expected_delivery_date', [$range['from'], $range['to']])
                        ->orWhere(function (Builder $fallback) use ($range): void {
                            $fallback->whereNull('expected_delivery_date')
                                ->whereBetween('created_at', [$range['from'], $range['to']]);
                        });
                })
                ->count(),
            'delivered' => Delivery::query()
                ->where('status', Delivery::STATUS_DELIVERED)
                ->where(function (Builder $query) use ($range): void {
                    $query->whereBetween('actual_delivery_date', [$range['from'], $range['to']])
                        ->orWhere(function (Builder $fallback) use ($range): void {
                            $fallback->whereNull('actual_delivery_date')
                                ->whereBetween('updated_at', [$range['from'], $range['to']]);
                        });
                })
                ->count(),
        ];
    }

    private function revenueStats(array $range): array
    {
        $query = $this->completedRevenueQuery();
        $this->applyCompletedRevenueDateRange($query, $range);

        return [
            'total' => (float) ($query->sum('orders.total_price') ?? 0),
        ];
    }

    private function statCards(
        array $range,
        ?array $inventory,
        ?array $customerStats,
        ?array $testDriveStats,
        ?array $quoteStats,
        ?array $orderStats,
        ?array $deliveryStats,
        ?array $revenueStats
    ): array {
        $cards = [];

        if ($inventory) {
            $cards[] = [
                'key' => 'physical_stock',
                'label' => 'Tổng xe tồn kho',
                'value' => number_format($inventory['physical']),
                'meta' => 'Tồn kho vật lý toàn showroom',
                'icon' => 'fa-boxes-stacked',
                'tone' => 'gold',
            ];
            $cards[] = [
                'key' => 'available_stock',
                'label' => 'Xe có thể bán',
                'value' => number_format($inventory['available']),
                'meta' => 'Tồn vật lý trừ xe đã giữ chỗ',
                'icon' => 'fa-car-side',
                'tone' => 'green',
            ];
            $cards[] = [
                'key' => 'reserved_stock',
                'label' => 'Xe đã giữ chỗ',
                'value' => number_format($inventory['reserved']),
                'meta' => 'Giữ chỗ active: ' . number_format($inventory['activeReservations']),
                'icon' => 'fa-lock',
                'tone' => 'violet',
            ];
        }

        if ($revenueStats) {
            $cards[] = [
                'key' => 'revenue',
                'label' => 'Doanh thu',
                'value' => $this->money($revenueStats['total']),
                'meta' => 'Đơn hoàn tất/giao xe trong ' . $range['label'],
                'icon' => 'fa-chart-line',
                'tone' => 'emerald',
            ];
        }

        if ($customerStats) {
            $cards[] = [
                'key' => 'new_customers',
                'label' => 'Khách hàng mới',
                'value' => number_format($customerStats['new']),
                'meta' => 'Tạo trong ' . $range['label'],
                'icon' => 'fa-user-plus',
                'tone' => 'blue',
            ];
        }

        if ($testDriveStats) {
            $cards[] = [
                'key' => 'test_drives',
                'label' => 'Lịch lái thử',
                'value' => number_format($testDriveStats['today_or_period']),
                'meta' => 'Theo appointment_date trong ' . $range['label'],
                'icon' => 'fa-calendar-check',
                'tone' => 'sky',
            ];
        }

        if ($quoteStats) {
            $cards[] = [
                'key' => 'pending_quotes',
                'label' => 'Báo giá đang chờ',
                'value' => number_format($quoteStats['pending']),
                'meta' => 'Nháp hoặc đã gửi, chưa chấp nhận/từ chối',
                'icon' => 'fa-file-invoice-dollar',
                'tone' => 'amber',
            ];
        }

        if ($orderStats) {
            $cards[] = [
                'key' => 'deposited_orders',
                'label' => 'Đơn hàng đã cọc',
                'value' => number_format($orderStats['deposited']),
                'meta' => 'Status đã cọc trong ' . $range['label'],
                'icon' => 'fa-receipt',
                'tone' => 'blue',
            ];
        }

        if ($deliveryStats) {
            $cards[] = [
                'key' => 'waiting_deliveries',
                'label' => 'Đơn chờ giao',
                'value' => number_format($deliveryStats['waiting']),
                'meta' => 'Pending, preparing hoặc ready',
                'icon' => 'fa-truck-fast',
                'tone' => 'orange',
            ];
            $cards[] = [
                'key' => 'delivered_cars',
                'label' => 'Xe đã giao',
                'value' => number_format($deliveryStats['delivered']),
                'meta' => 'Delivered trong ' . $range['label'],
                'icon' => 'fa-circle-check',
                'tone' => 'green',
            ];
        }

        if ($quoteStats) {
            $cards[] = [
                'key' => 'conversion',
                'label' => 'Tỷ lệ chuyển đổi',
                'value' => number_format($quoteStats['conversion_rate'], 1) . '%',
                'meta' => number_format($quoteStats['accepted']) . '/' . number_format($quoteStats['total']) . ' báo giá accepted',
                'icon' => 'fa-percent',
                'tone' => 'gold',
            ];
        }

        return $cards;
    }

    private function charts(array $range, array $permissions): array
    {
        $charts = [
            'revenue' => null,
            'orders' => null,
            'quoteStatuses' => null,
            'testDriveStatuses' => null,
        ];

        if ($permissions['reports']) {
            $charts['revenue'] = $this->revenueByMonthChart($range);
        }

        if ($permissions['orders']) {
            $charts['orders'] = $this->ordersByMonthChart($range);
        }

        if ($permissions['quotes']) {
            $charts['quoteStatuses'] = $this->quoteStatusChart($range);
        }

        if ($permissions['test_drives']) {
            $charts['testDriveStatuses'] = $this->testDriveStatusChart($range);
        }

        return $charts;
    }

    private function revenueByMonthChart(array $range): array
    {
        [$months, $windowFrom, $windowTo] = $this->monthWindow($range['to']);
        $dateExpression = 'COALESCE(deliveries.actual_delivery_date, orders.created_at)';
        $monthExpression = $this->monthKeyExpression($dateExpression);

        $rows = $this->completedRevenueQuery()
            ->whereBetween(DB::raw($dateExpression), [$windowFrom, $windowTo])
            ->selectRaw($monthExpression . ' as month_key, COALESCE(SUM(orders.total_price), 0) as aggregate')
            ->groupBy('month_key')
            ->pluck('aggregate', 'month_key');

        return [
            'labels' => $months->pluck('label')->values(),
            'data' => $months->map(fn (array $month): float => round((float) ($rows[$month['key']] ?? 0), 0))->values(),
        ];
    }

    private function ordersByMonthChart(array $range): array
    {
        [$months, $windowFrom, $windowTo] = $this->monthWindow($range['to']);
        $monthExpression = $this->monthKeyExpression('created_at');

        $rows = Order::query()
            ->whereBetween('created_at', [$windowFrom, $windowTo])
            ->whereNotIn('status', $this->orderStatusValues(Order::STATUS_CANCELLED))
            ->selectRaw($monthExpression . ' as month_key, COUNT(*) as aggregate')
            ->groupBy('month_key')
            ->pluck('aggregate', 'month_key');

        return [
            'labels' => $months->pluck('label')->values(),
            'data' => $months->map(fn (array $month): int => (int) ($rows[$month['key']] ?? 0))->values(),
        ];
    }

    private function quoteStatusChart(array $range): array
    {
        $rows = Quote::query()
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'labels' => array_values(Quote::STATUSES),
            'data' => collect(array_keys(Quote::STATUSES))
                ->map(fn (string $status): int => (int) ($rows[$status] ?? 0))
                ->values(),
        ];
    }

    private function testDriveStatusChart(array $range): array
    {
        $rows = $this->testDrivePeriodQuery($range)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return [
            'labels' => array_values(Ticket::TEST_DRIVE_STATUS_LABELS),
            'data' => collect(array_keys(Ticket::TEST_DRIVE_STATUS_LABELS))
                ->map(fn (string $status): int => (int) ($rows[$status] ?? 0))
                ->values(),
        ];
    }

    private function recentCars(): Collection
    {
        return Car::query()
            ->with(['modelInfo.brand'])
            ->orderByDesc('updated_at')
            ->orderByDesc('car_id')
            ->take(6)
            ->get();
    }

    private function recentStockMovements(array $range): Collection
    {
        return StockMovement::query()
            ->with(['car.modelInfo.brand', 'user'])
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->take(10)
            ->get();
    }

    private function recentOrders(array $range): Collection
    {
        return Order::query()
            ->with(['user', 'delivery', 'details.car.modelInfo.brand'])
            ->whereBetween('created_at', [$range['from'], $range['to']])
            ->orderByDesc('created_at')
            ->orderByDesc('order_id')
            ->take(8)
            ->get();
    }

    private function upcomingDeliveries(array $range): Collection
    {
        $from = max(now()->startOfDay()->timestamp, $range['from']->copy()->startOfDay()->timestamp);
        $fromDate = Carbon::createFromTimestamp($from)->startOfDay();

        return Delivery::query()
            ->with(['order.user', 'car.modelInfo.brand', 'deliveryStaff'])
            ->whereIn('status', [Delivery::STATUS_PENDING, Delivery::STATUS_PREPARING, Delivery::STATUS_READY])
            ->where(function (Builder $query) use ($range, $fromDate): void {
                $query->whereBetween('expected_delivery_date', [$fromDate, $range['to']])
                    ->orWhere(function (Builder $fallback) use ($range): void {
                        $fallback->whereNull('expected_delivery_date')
                            ->whereBetween('created_at', [$range['from'], $range['to']]);
                    });
            })
            ->orderByRaw('CASE WHEN expected_delivery_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expected_delivery_date')
            ->orderByDesc('created_at')
            ->take(8)
            ->get();
    }

    private function topSellingCars(array $range): Collection
    {
        $query = DB::table('order_details')
            ->join('orders', 'orders.order_id', '=', 'order_details.order_id')
            ->leftJoin('deliveries', 'deliveries.order_id', '=', 'orders.order_id')
            ->join('cars', 'cars.car_id', '=', 'order_details.car_id')
            ->whereIn('orders.status', $this->orderStatusValues(Order::STATUS_COMPLETED))
            ->where(function ($query): void {
                $query->whereNull('deliveries.id')
                    ->orWhere('deliveries.status', Delivery::STATUS_DELIVERED);
            });

        $this->applyCompletedRevenueDateRange($query, $range);

        return $query
            ->selectRaw('cars.car_id, cars.name, SUM(order_details.quantity) as sold_quantity, SUM(order_details.quantity * order_details.price) as sold_amount')
            ->groupBy('cars.car_id', 'cars.name')
            ->orderByDesc('sold_quantity')
            ->orderByDesc('sold_amount')
            ->take(5)
            ->get();
    }

    private function topInterestedCars(array $range, array $permissions): Collection
    {
        $quoteCounts = collect();
        $testDriveCounts = collect();

        if ($permissions['quotes']) {
            $quoteCounts = Quote::query()
                ->whereBetween('created_at', [$range['from'], $range['to']])
                ->whereNotNull('car_id')
                ->selectRaw('car_id, COUNT(*) as aggregate')
                ->groupBy('car_id')
                ->pluck('aggregate', 'car_id');
        }

        if ($permissions['test_drives']) {
            $testDriveCounts = $this->testDrivePeriodQuery($range)
                ->whereNotNull('car_id')
                ->selectRaw('car_id, COUNT(*) as aggregate')
                ->groupBy('car_id')
                ->pluck('aggregate', 'car_id');
        }

        $carIds = $quoteCounts->keys()
            ->merge($testDriveCounts->keys())
            ->unique()
            ->filter()
            ->values();

        if ($carIds->isEmpty()) {
            return collect();
        }

        $cars = Car::query()
            ->with(['modelInfo.brand'])
            ->whereIn('car_id', $carIds)
            ->get()
            ->keyBy('car_id');

        return $carIds
            ->map(function ($carId) use ($cars, $quoteCounts, $testDriveCounts): array {
                $quoteTotal = (int) ($quoteCounts[$carId] ?? 0);
                $testDriveTotal = (int) ($testDriveCounts[$carId] ?? 0);
                $car = $cars[$carId] ?? null;

                return [
                    'car_id' => $carId,
                    'name' => $car ? $this->carDisplayName($car) : 'Xe #' . $carId,
                    'quote_count' => $quoteTotal,
                    'test_drive_count' => $testDriveTotal,
                    'total' => $quoteTotal + $testDriveTotal,
                ];
            })
            ->sortByDesc('total')
            ->take(5)
            ->values();
    }

    private function completedRevenueQuery()
    {
        return Order::query()
            ->leftJoin('deliveries', 'deliveries.order_id', '=', 'orders.order_id')
            ->whereIn('orders.status', $this->orderStatusValues(Order::STATUS_COMPLETED))
            ->where(function ($query): void {
                $query->whereNull('deliveries.id')
                    ->orWhere('deliveries.status', Delivery::STATUS_DELIVERED);
            });
    }

    private function applyCompletedRevenueDateRange($query, array $range): void
    {
        $query->where(function ($query) use ($range): void {
            $query->whereBetween('deliveries.actual_delivery_date', [$range['from'], $range['to']])
                ->orWhere(function ($fallback) use ($range): void {
                    $fallback->whereNull('deliveries.actual_delivery_date')
                        ->whereBetween('orders.created_at', [$range['from'], $range['to']]);
                });
        });
    }

    private function testDrivePeriodQuery(array $range): Builder
    {
        return Ticket::query()
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->where(function (Builder $query) use ($range): void {
                $query->whereBetween('appointment_date', [$range['from']->toDateString(), $range['to']->toDateString()])
                    ->orWhere(function (Builder $fallback) use ($range): void {
                        $fallback->whereNull('appointment_date')
                            ->whereBetween('created_at', [$range['from'], $range['to']]);
                    });
            });
    }

    private function monthWindow(Carbon $endingAt): array
    {
        $windowTo = $endingAt->copy()->endOfMonth();
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

    private function monthKeyExpression(string $dateExpression): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$dateExpression})"
            : "DATE_FORMAT({$dateExpression}, '%Y-%m')";
    }

    private function orderStatusValues(int $status): array
    {
        return match ($status) {
            Order::STATUS_PENDING => [0, '0', 'pending'],
            Order::STATUS_DEPOSITED => [1, '1', 'deposit', 'deposited'],
            Order::STATUS_COMPLETED => [2, '2', 'complete', 'completed', 'done'],
            Order::STATUS_CANCELLED => [3, '3', 'cancel', 'canceled', 'cancelled'],
            default => [$status, (string) $status],
        };
    }

    private function carDisplayName(Car $car): string
    {
        $brand = $car->modelInfo?->brand?->name;
        $model = $car->modelInfo?->name;

        return trim(collect([$brand, $model, $car->name])->filter()->join(' ')) ?: ('Xe #' . $car->getKey());
    }

    private function money(float $value): string
    {
        return number_format($value, 0, ',', '.') . ' đ';
    }
}
