<?php

namespace App\Support;

use App\Models\Car;
use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\Quote;
use App\Models\StockReservation;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AdminReportQuery
{
    public static function orderStatusValues(int $status): array
    {
        return match ($status) {
            Order::STATUS_PENDING => [0, '0', 'pending'],
            Order::STATUS_DEPOSITED => [1, '1', 'deposit', 'deposited'],
            Order::STATUS_COMPLETED => [2, '2', 'complete', 'completed', 'done'],
            Order::STATUS_CANCELLED => [3, '3', 'cancel', 'canceled', 'cancelled'],
            default => [$status, (string) $status],
        };
    }

    public static function completedRevenueQuery(array $filters = []): Builder
    {
        $query = Order::query()
            ->leftJoin('deliveries', 'deliveries.order_id', '=', 'orders.order_id')
            ->whereIn('orders.status', self::orderStatusValues(Order::STATUS_COMPLETED))
            ->where(function ($query): void {
                $query->whereNull('deliveries.id')
                    ->orWhere('deliveries.status', Delivery::STATUS_DELIVERED);
            });

        if (($filters['status'] ?? '') !== '') {
            $status = Order::normalizeStatus($filters['status']);

            if ($status !== Order::STATUS_COMPLETED) {
                $query->whereRaw('1 = 0');
            }
        }

        self::applyOrderFilters($query, $filters, useCompletionDate: true, includeStatus: false);

        return $query;
    }

    public static function applyOrderFilters(
        Builder $query,
        array $filters,
        bool $useCompletionDate = false,
        bool $includeStatus = true
    ): void {
        if ($includeStatus && ($filters['status'] ?? '') !== '') {
            $query->whereIn('orders.status', self::orderStatusValues((int) $filters['status']));
        }

        if ($range = self::dateRange($filters)) {
            if ($useCompletionDate) {
                $query->whereBetween(DB::raw('COALESCE(deliveries.actual_delivery_date, orders.created_at)'), $range);
            } else {
                $query->whereBetween('orders.created_at', $range);
            }
        }

        if (!empty($filters['brand_id'])) {
            $query->whereHas('details.car.carModel', function (Builder $modelQuery) use ($filters): void {
                $modelQuery->where('brand_id', (int) $filters['brand_id']);
            });
        }

        if (!empty($filters['model_id'])) {
            $query->whereHas('details.car', function (Builder $carQuery) use ($filters): void {
                $carQuery->where('car_model_id', (int) $filters['model_id']);
            });
        }

        if (!empty($filters['user_id'])) {
            $userId = (int) $filters['user_id'];

            $query->where(function (Builder $staffQuery) use ($userId): void {
                $staffQuery
                    ->whereHas('quote', fn (Builder $quoteQuery) => $quoteQuery->where('user_id', $userId))
                    ->orWhere('orders.deposit_confirmed_by', $userId);
            });
        }
    }

    public static function applyInventoryFilters(Builder $query, array $filters): void
    {
        if (!empty($filters['brand_id'])) {
            $query->whereHas('carModel', function (Builder $modelQuery) use ($filters): void {
                $modelQuery->where('brand_id', (int) $filters['brand_id']);
            });
        }

        if (!empty($filters['model_id'])) {
            $query->where('car_model_id', (int) $filters['model_id']);
        }

        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }

        match ($filters['stock_state'] ?? '') {
            'out_of_stock' => $query->whereRaw('COALESCE(stock_quantity, stock, 0) <= 0'),
            'fully_reserved' => $query
                ->whereRaw('COALESCE(stock_quantity, stock, 0) > 0')
                ->whereRaw('(COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0)) <= 0'),
            'available' => $query->whereRaw('(COALESCE(stock_quantity, stock, 0) - COALESCE(reserved_quantity, 0)) > 0'),
            default => null,
        };
    }

    public static function applyReservationFilters(Builder $query, array $filters): void
    {
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['car_id'])) {
            $query->where('car_id', (int) $filters['car_id']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', (int) $filters['user_id']);
        }

        if ($range = self::dateRange($filters)) {
            $query->whereBetween(DB::raw('COALESCE(reserved_at, created_at)'), $range);
        }
    }

    public static function applyDeliveryFilters(Builder $query, array $filters): void
    {
        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['delivery_staff_id'])) {
            $query->where('delivery_staff_id', (int) $filters['delivery_staff_id']);
        }

        if ($range = self::dateRange($filters)) {
            $query->whereBetween(DB::raw('COALESCE(actual_delivery_date, expected_delivery_date, created_at)'), $range);
        }
    }

    public static function applyCustomerFilters(Builder $query, array $filters): void
    {
        if (($filters['source'] ?? '') !== '') {
            $query->where('source', $filters['source']);
        }

        if (($filters['status'] ?? '') !== '') {
            $query->where('status', $filters['status']);
        }

        if ($range = self::dateRange($filters)) {
            $query->whereBetween('created_at', $range);
        }
    }

    public static function staffRows(array $filters = []): Collection
    {
        $staffQuery = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->orderBy('name')
            ->orderBy('user_id');

        if (!empty($filters['user_id'])) {
            $staffQuery->where('user_id', (int) $filters['user_id']);
        }

        $staff = $staffQuery->get(['user_id', 'name', 'email']);
        $dateRange = self::dateRange($filters);

        $customerCountsQuery = Customer::query()
            ->selectRaw('created_by, COUNT(*) as aggregate')
            ->whereNotNull('created_by')
            ->groupBy('created_by');
        self::applyOptionalDateRange($customerCountsQuery, 'created_at', $dateRange);
        $customerCounts = $customerCountsQuery->pluck('aggregate', 'created_by');

        $quoteCountsQuery = Quote::query()
            ->selectRaw('user_id, COUNT(*) as aggregate')
            ->whereNotNull('user_id')
            ->groupBy('user_id');
        self::applyOptionalDateRange($quoteCountsQuery, 'created_at', $dateRange);
        $quoteCounts = $quoteCountsQuery->pluck('aggregate', 'user_id');

        $acceptedQuoteCountsQuery = Quote::query()
            ->selectRaw('user_id, COUNT(*) as aggregate')
            ->where('status', Quote::STATUS_ACCEPTED)
            ->whereNotNull('user_id')
            ->groupBy('user_id');
        self::applyOptionalDateRange($acceptedQuoteCountsQuery, 'created_at', $dateRange);
        $acceptedQuoteCounts = $acceptedQuoteCountsQuery->pluck('aggregate', 'user_id');

        $testDriveCountsQuery = Ticket::query()
            ->selectRaw('sales_person, COUNT(*) as aggregate')
            ->where('ticket_type', Ticket::TYPE_TEST_DRIVE)
            ->whereNotNull('sales_person')
            ->where('sales_person', '!=', '')
            ->groupBy('sales_person');
        self::applyOptionalDateRange($testDriveCountsQuery, 'created_at', $dateRange);
        $testDriveCounts = $testDriveCountsQuery->pluck('aggregate', 'sales_person');

        $orderCountsQuery = Order::query()
            ->leftJoin('quotes', 'quotes.quote_id', '=', 'orders.quote_id')
            ->selectRaw('COALESCE(quotes.user_id, orders.deposit_confirmed_by) as staff_id, COUNT(DISTINCT orders.order_id) as aggregate')
            ->whereRaw('COALESCE(quotes.user_id, orders.deposit_confirmed_by) IS NOT NULL')
            ->groupBy('staff_id');
        self::applyOptionalDateRange($orderCountsQuery, 'orders.created_at', $dateRange);
        $orderCounts = $orderCountsQuery->pluck('aggregate', 'staff_id');

        $revenueRowsQuery = Order::query()
            ->leftJoin('quotes', 'quotes.quote_id', '=', 'orders.quote_id')
            ->leftJoin('deliveries', 'deliveries.order_id', '=', 'orders.order_id')
            ->selectRaw('COALESCE(quotes.user_id, orders.deposit_confirmed_by) as staff_id')
            ->selectRaw('COUNT(DISTINCT orders.order_id) as delivered_orders')
            ->selectRaw('COALESCE(SUM(orders.total_price), 0) as revenue')
            ->whereIn('orders.status', self::orderStatusValues(Order::STATUS_COMPLETED))
            ->where(function ($query): void {
                $query->whereNull('deliveries.id')
                    ->orWhere('deliveries.status', Delivery::STATUS_DELIVERED);
            })
            ->whereRaw('COALESCE(quotes.user_id, orders.deposit_confirmed_by) IS NOT NULL')
            ->groupBy('staff_id');

        if ($dateRange) {
            $revenueRowsQuery->whereBetween(DB::raw('COALESCE(deliveries.actual_delivery_date, orders.created_at)'), $dateRange);
        }

        $revenueRows = $revenueRowsQuery->get()->keyBy('staff_id');

        return $staff->map(function (User $user) use (
            $customerCounts,
            $quoteCounts,
            $acceptedQuoteCounts,
            $testDriveCounts,
            $orderCounts,
            $revenueRows
        ): array {
            $quoteTotal = (int) ($quoteCounts[$user->user_id] ?? 0);
            $orderTotal = (int) ($orderCounts[$user->user_id] ?? 0);
            $revenueRow = $revenueRows[$user->user_id] ?? null;

            return [
                'user' => $user,
                'customers_count' => (int) ($customerCounts[$user->user_id] ?? 0),
                'test_drives_count' => (int) ($testDriveCounts[$user->name] ?? 0),
                'quotes_count' => $quoteTotal,
                'accepted_quotes_count' => (int) ($acceptedQuoteCounts[$user->user_id] ?? 0),
                'orders_count' => $orderTotal,
                'delivered_count' => (int) ($revenueRow?->delivered_orders ?? 0),
                'revenue' => (float) ($revenueRow?->revenue ?? 0),
                'closing_rate' => $quoteTotal > 0 ? round(($orderTotal / $quoteTotal) * 100, 1) : 0.0,
            ];
        })->values();
    }

    public static function dateRange(array $filters): ?array
    {
        $from = self::parseDate($filters['date_from'] ?? null, true);
        $to = self::parseDate($filters['date_to'] ?? null, false);

        if (!$from && !$to) {
            return null;
        }

        $from ??= Carbon::create(1970, 1, 1)->startOfDay();
        $to ??= now()->endOfDay();

        return [$from, $to];
    }

    public static function monthKeyExpression(string $dateExpression): string
    {
        return DB::getDriverName() === 'sqlite'
            ? "strftime('%Y-%m', {$dateExpression})"
            : "DATE_FORMAT({$dateExpression}, '%Y-%m')";
    }

    private static function parseDate(mixed $value, bool $startOfDay): ?Carbon
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        $date = Carbon::parse($value);

        return $startOfDay ? $date->startOfDay() : $date->endOfDay();
    }

    private static function applyOptionalDateRange(Builder $query, string $column, ?array $dateRange): void
    {
        if ($dateRange) {
            $query->whereBetween($column, $dateRange);
        }
    }
}
