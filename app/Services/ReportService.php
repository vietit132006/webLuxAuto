<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function salesRevenue(string $period): array
    {
        $completed = Order::query()->where('status', 2);

        $now = Carbon::now();

        return match ($period) {
            'daily' => [
                'period' => 'daily',
                'total' => (clone $completed)->whereDate('created_at', $now->toDateString())->sum('total_price'),
                'breakdown' => (clone $completed)
                    ->whereDate('created_at', '>=', $now->copy()->subDays(30))
                    ->selectRaw('DATE(created_at) as day, SUM(total_price) as revenue')
                    ->groupBy('day')
                    ->orderBy('day')
                    ->get()
                    ->map(fn ($r) => ['date' => $r->day, 'revenue' => (float) $r->revenue])
                    ->all(),
            ],
            'monthly' => [
                'period' => 'monthly',
                'total' => (clone $completed)
                    ->whereYear('created_at', $now->year)
                    ->whereMonth('created_at', $now->month)
                    ->sum('total_price'),
                'breakdown' => (clone $completed)
                    ->where('created_at', '>=', $now->copy()->subMonths(12))
                    ->selectRaw('YEAR(created_at) as y, MONTH(created_at) as m, SUM(total_price) as revenue')
                    ->groupBy('y', 'm')
                    ->orderBy('y')
                    ->orderBy('m')
                    ->get()
                    ->map(fn ($r) => [
                        'year' => (int) $r->y,
                        'month' => (int) $r->m,
                        'revenue' => (float) $r->revenue,
                    ])
                    ->all(),
            ],
            'yearly' => [
                'period' => 'yearly',
                'total' => (clone $completed)->whereYear('created_at', $now->year)->sum('total_price'),
                'breakdown' => (clone $completed)
                    ->selectRaw('YEAR(created_at) as y, SUM(total_price) as revenue')
                    ->groupBy('y')
                    ->orderBy('y')
                    ->get()
                    ->map(fn ($r) => ['year' => (int) $r->y, 'revenue' => (float) $r->revenue])
                    ->all(),
            ],
            default => [
                'period' => $period,
                'total' => 0,
                'breakdown' => [],
                'error' => 'period must be daily, monthly, or yearly',
            ],
        };
    }

    /**
     * @return array<int, array{car_id: int, name: string, units_sold: int}>
     */
    public function bestSellingCars(int $limit = 10): array
    {
        $rows = DB::table('order_details')
            ->join('orders', 'order_details.order_id', '=', 'orders.order_id')
            ->join('cars', 'order_details.car_id', '=', 'cars.car_id')
            ->where('orders.status', 2)
            ->whereNull('cars.deleted_at')
            ->groupBy('cars.car_id', 'cars.name')
            ->selectRaw('cars.car_id, cars.name, SUM(order_details.quantity) as units')
            ->orderByDesc('units')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'car_id' => (int) $r->car_id,
            'name' => $r->name,
            'units_sold' => (int) $r->units,
        ])->all();
    }

    /**
     * @return array{low_stock: Collection, out_of_stock: Collection}
     */
    public function inventoryReport(int $lowThreshold = 5): array
    {
        $low = Car::query()
            ->where('stock', '>', 0)
            ->where('stock', '<', $lowThreshold)
            ->orderBy('stock')
            ->get(['car_id', 'name', 'stock', 'price']);

        $out = Car::query()
            ->where('stock', '<=', 0)
            ->orderBy('car_id')
            ->get(['car_id', 'name', 'stock', 'price']);

        return [
            'low_stock' => $low,
            'out_of_stock' => $out,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function customerReport(int $topLimit = 10): array
    {
        $topByOrders = User::query()
            ->where('role', 'customer')
            ->withCount(['orders' => fn ($q) => $q->where('status', 2)])
            ->orderByDesc('orders_count')
            ->limit($topLimit)
            ->get()
            ->map(fn (User $u) => [
                'user_id' => $u->user_id,
                'name' => $u->name,
                'email' => $u->email,
                'completed_orders' => $u->orders_count,
            ])
            ->values()
            ->all();

        $topBySpending = User::query()
            ->where('role', 'customer')
            ->whereHas('orders', fn ($q) => $q->where('status', 2))
            ->withSum(['orders as total_spent' => fn ($q) => $q->where('status', 2)], 'total_price')
            ->orderByDesc('total_spent')
            ->limit($topLimit)
            ->get()
            ->map(fn (User $u) => [
                'user_id' => $u->user_id,
                'name' => $u->name,
                'email' => $u->email,
                'total_spent' => (float) ($u->total_spent ?? 0),
            ])
            ->values()
            ->all();

        $since = Carbon::now()->subDays(30);
        $customerIdsWithOrderBefore = Order::query()
            ->where('created_at', '<', $since)
            ->distinct()
            ->pluck('user_id');

        $recentOrderers = Order::query()
            ->where('created_at', '>=', $since)
            ->distinct()
            ->pluck('user_id');

        $newInPeriod = $recentOrderers->diff($customerIdsWithOrderBefore)->count();
        $returningInPeriod = $recentOrderers->intersect($customerIdsWithOrderBefore)->count();

        return [
            'top_by_completed_orders' => $topByOrders,
            'top_by_spending' => $topBySpending,
            'new_vs_returning_last_30_days' => [
                'new_customers' => $newInPeriod,
                'returning_customers' => $returningInPeriod,
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function topCustomersBySpending(int $limit = 10): array
    {
        $rows = DB::table('orders')
            ->join('users', 'orders.user_id', '=', 'users.user_id')
            ->where('orders.status', 2)
            ->where('users.role', 'customer')
            ->groupBy('users.user_id', 'users.name', 'users.email')
            ->selectRaw('users.user_id, users.name, users.email, SUM(orders.total_price) as total_spent, COUNT(*) as completed_orders, MAX(orders.created_at) as last_order_at')
            ->orderByDesc('total_spent')
            ->limit($limit)
            ->get();

        return $rows->map(fn ($r) => [
            'user_id' => (int) $r->user_id,
            'name' => $r->name,
            'email' => $r->email,
            'total_spent' => (float) $r->total_spent,
            'completed_orders' => (int) $r->completed_orders,
            'last_order_at' => $r->last_order_at,
        ])->all();
    }

    public function newVsReturning(int $days = 30): array
    {
        $days = max(1, min(365, $days));
        $since = Carbon::now()->subDays($days);

        $customerIdsWithOrderBefore = Order::query()
            ->where('created_at', '<', $since)
            ->distinct()
            ->pluck('user_id');

        $recentOrderers = Order::query()
            ->where('created_at', '>=', $since)
            ->distinct()
            ->pluck('user_id');

        return [
            'days' => $days,
            'new_customers' => $recentOrderers->diff($customerIdsWithOrderBefore)->count(),
            'returning_customers' => $recentOrderers->intersect($customerIdsWithOrderBefore)->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function reviewReport(int $mostReviewedLimit = 10): array
    {
        $avgPerCar = Car::query()
            ->whereHas('reviews')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->get()
            ->map(fn (Car $c) => [
                'car_id' => $c->car_id,
                'name' => $c->name,
                'average_rating' => round((float) ($c->reviews_avg_rating ?? 0), 2),
                'review_count' => (int) $c->reviews_count,
            ])
            ->values()
            ->all();

        $mostReviewed = Review::query()
            ->selectRaw('car_id, COUNT(*) as cnt, AVG(rating) as avg_r')
            ->whereNull('deleted_at')
            ->groupBy('car_id')
            ->orderByDesc('cnt')
            ->limit($mostReviewedLimit)
            ->get();

        $carIds = $mostReviewed->pluck('car_id');
        $names = Car::query()->whereIn('car_id', $carIds)->pluck('name', 'car_id');

        $mostReviewed = $mostReviewed->map(fn ($row) => [
            'car_id' => (int) $row->car_id,
            'name' => $names[$row->car_id] ?? '',
            'review_count' => (int) $row->cnt,
            'average_rating' => round((float) $row->avg_r, 2),
        ])->all();

        return [
            'average_rating_per_car' => $avgPerCar,
            'most_reviewed_cars' => $mostReviewed,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function averageRatingPerCar(int $limit = 50): array
    {
        $limit = max(1, min(200, $limit));

        return Car::query()
            ->whereHas('reviews')
            ->withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->orderByDesc('reviews_avg_rating')
            ->limit($limit)
            ->get()
            ->map(fn (Car $c) => [
                'car_id' => $c->car_id,
                'name' => $c->name,
                'average_rating' => round((float) ($c->reviews_avg_rating ?? 0), 2),
                'review_count' => (int) $c->reviews_count,
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function mostReviewedCars(int $limit = 10): array
    {
        $limit = max(1, min(200, $limit));

        $mostReviewed = Review::query()
            ->selectRaw('car_id, COUNT(*) as cnt, AVG(rating) as avg_r')
            ->whereNull('deleted_at')
            ->groupBy('car_id')
            ->orderByDesc('cnt')
            ->limit($limit)
            ->get();

        $carIds = $mostReviewed->pluck('car_id');
        $names = Car::query()->whereIn('car_id', $carIds)->pluck('name', 'car_id');

        return $mostReviewed->map(fn ($row) => [
            'car_id' => (int) $row->car_id,
            'name' => $names[$row->car_id] ?? '',
            'review_count' => (int) $row->cnt,
            'average_rating' => round((float) $row->avg_r, 2),
        ])->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function reviewReportForCar(int $carId): array
    {
        $stats = Review::query()
            ->where('car_id', $carId)
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as review_count, AVG(rating) as average_rating')
            ->first();

        $distribution = Review::query()
            ->where('car_id', $carId)
            ->whereNull('deleted_at')
            ->selectRaw('rating, COUNT(*) as cnt')
            ->groupBy('rating')
            ->orderBy('rating')
            ->get()
            ->mapWithKeys(fn ($r) => [(int) $r->rating => (int) $r->cnt])
            ->all();

        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = $distribution[$i] ?? 0;
        }
        ksort($distribution);

        return [
            'car_id' => $carId,
            'review_count' => (int) ($stats?->review_count ?? 0),
            'average_rating' => round((float) ($stats?->average_rating ?? 0), 2),
            'rating_distribution' => $distribution,
        ];
    }
}
