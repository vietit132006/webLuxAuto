<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\InventoryLog;
use App\Models\Order;
use App\Models\Review;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminReportController extends Controller
{
    public function sales(Request $request): View
    {
        $month = $request->input('month', now()->format('Y-m'));

        $totalCompleted = (float) Order::where('status', 2)->sum('total_price');
        $totalDeposited = (float) Order::where('status', 1)->sum('total_price');

        $byStatus = Order::query()
            ->selectRaw('status, COUNT(*) as cnt, COALESCE(SUM(total_price), 0) as sum_price')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        $monthlyRows = Order::query()
            ->whereYear('created_at', (int) substr($month, 0, 4))
            ->whereMonth('created_at', (int) substr($month, 5, 2))
            ->orderBy('created_at', 'desc')
            ->with(['user', 'details.car'])
            ->paginate(20)
            ->withQueryString();

        $chartMonths = collect(range(5, 0))->map(function ($i) {
            return now()->subMonths($i)->format('Y-m');
        });

        $chartData = $chartMonths->map(function ($ym) {
            [$y, $m] = explode('-', $ym);

            return [
                'label' => $ym,
                'revenue' => (float) Order::where('status', 2)
                    ->whereYear('created_at', (int) $y)
                    ->whereMonth('created_at', (int) $m)
                    ->sum('total_price'),
                'orders' => (int) Order::whereYear('created_at', (int) $y)
                    ->whereMonth('created_at', (int) $m)
                    ->count(),
            ];
        });

        return view('admin.reports.sales', compact(
            'totalCompleted',
            'totalDeposited',
            'byStatus',
            'month',
            'monthlyRows',
            'chartData'
        ));
    }

    public function inventory(): View
    {
        $cars = Car::with('brand')
            ->orderBy('stock', 'asc')
            ->orderBy('name')
            ->paginate(25);

        $totalUnits = (int) Car::sum('stock');
        $lowStock = Car::where('stock', '>', 0)->where('stock', '<=', 2)->count();
        $outOfStock = Car::where('stock', '<=', 0)->count();

        return view('admin.reports.inventory', compact('cars', 'totalUnits', 'lowStock', 'outOfStock'));
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
            DB::transaction(function () use ($data) {
                $car = Car::lockForUpdate()->findOrFail($data['car_id']);
                $newStock = $car->stock + $data['change_qty'];
                if ($newStock < 0) {
                    throw new \InvalidArgumentException('Tồn kho sau điều chỉnh không được âm.');
                }
                $car->update(['stock' => $newStock]);
                InventoryLog::create([
                    'car_id' => $car->car_id,
                    'change_qty' => $data['change_qty'],
                    'note' => $data['note'] ?? null,
                ]);
            });
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('admin.reports.inventory_check')->withErrors(['car_id' => $e->getMessage()]);
        }

        return redirect()->route('admin.reports.inventory_check')->with('success', 'Đã ghi nhận kiểm kho và cập nhật tồn.');
    }

    public function customers(): View
    {
        $customers = User::where('role', 'customer')
            ->withCount('orders')
            ->withSum('orders', 'total_price')
            ->orderByDesc('orders_count')
            ->paginate(25);

        $totalCustomers = User::where('role', 'customer')->count();
        $activeBuyers = User::where('role', 'customer')->has('orders')->count();

        return view('admin.reports.customers', compact('customers', 'totalCustomers', 'activeBuyers'));
    }

    public function reviews(): View
    {
        $avgRating = Review::avg('rating');
        $totalReviews = Review::count();

        $reviews = Review::with(['user', 'car.brand'])
            ->orderBy('created_at', 'desc')
            ->paginate(25);

        $distribution = Review::query()
            ->selectRaw('rating, COUNT(*) as cnt')
            ->groupBy('rating')
            ->orderBy('rating')
            ->pluck('cnt', 'rating');

        return view('admin.reports.reviews', compact('reviews', 'avgRating', 'totalReviews', 'distribution'));
    }

    public function promotions(): View
    {
        $setting = Setting::firstOrCreate(
            ['key' => 'promotions_content'],
            ['value' => '', 'group' => 'marketing']
        );

        return view('admin.promotions', ['content' => $setting->value ?? '']);
    }

    public function updatePromotions(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'content' => 'nullable|string|max:20000',
        ]);

        Setting::updateOrCreate(
            ['key' => 'promotions_content'],
            ['value' => $data['content'] ?? '', 'group' => 'marketing']
        );

        return redirect()->route('admin.promotions')->with('success', 'Đã lưu nội dung khuyến mãi.');
    }
}
