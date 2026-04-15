<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CarController extends Controller
{
    // Hiển thị danh sách xe cho khách hàng (Trang chủ)
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('q', ''));
        $query = Car::query()->orderByDesc('year')->orderByDesc('car_id');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('brand_id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%"); // Đã sửa 'model' thành 'name' cho khớp DB của bạn
            });
        }

        $cars = $query->paginate(9)->withQueryString();

        return view('client.index', [
            'cars' => $cars,
            'search' => $search,
        ]);
    }

    // Hiển thị trang chi tiết xe dành cho Khách hàng
    public function show(Car $car): View
    {
        $reviewsQuery = Review::query()
            ->where('car_id', $car->car_id)
            ->whereNull('deleted_at')
            ->with(['user:user_id,name'])
            ->orderByDesc('review_id');

        $reviews = $reviewsQuery->paginate(8);
        $reviewCount = (clone $reviewsQuery)->count();
        $avgRating = (clone $reviewsQuery)->avg('rating');

        $myReview = null;
        if (Auth::check()) {
            $myReview = Review::withTrashed()
                ->where('car_id', $car->car_id)
                ->where('user_id', Auth::id())
                ->first();
        }

        return view('client.show', [
            'car' => $car,
            'reviews' => $reviews,
            'reviewCount' => $reviewCount,
            'avgRating' => $avgRating ? round((float) $avgRating, 2) : 0,
            'myReview' => $myReview,
        ]);
    }
}
