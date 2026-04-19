<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\OrderDetail;
use App\Models\Review;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class CarController extends Controller
{
    // Hiển thị danh sách xe cho khách hàng (Trang chủ)
    public function index(\Illuminate\Http\Request $request)
    {
        // 1. Lấy danh sách các Hãng xe để đổ ra Sidebar lọc
        $brands = \App\Models\Brand::all();

        // 2. Bắt đầu câu truy vấn (Query Builder)
        $query = \App\Models\Car::query();

        // Lọc theo Tên xe (Từ khóa)
        $query->when($request->keyword, function ($q, $keyword) {
            return $q->where('name', 'like', '%' . $keyword . '%');
        });

        // Lọc theo Hãng xe
        $query->when($request->brand_id, function ($q, $brand_id) {
            return $q->where('brand_id', $brand_id);
        });

        // Lọc theo Trạng thái (Ví dụ: 1 = Mới 100%, 0 = Xe lướt)
        $query->when($request->has('status') && $request->status != '', function ($q) use ($request) {
            return $q->where('status', $request->status);
        });

        // Lọc theo Khoảng giá (Từ Min đến Max)
        $query->when($request->min_price, function ($q, $min_price) {
            return $q->where('price', '>=', $min_price);
        });
        $query->when($request->max_price, function ($q, $max_price) {
            return $q->where('price', '<=', $max_price);
        });

        // 3. Thực thi truy vấn, sắp xếp xe mới nhất lên đầu và phân trang
        // LƯU Ý: Phải có withQueryString() để khi khách bấm sang Trang 2, bộ lọc không bị mất!
        $cars = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('client.index', compact('cars', 'brands'));
    }

    // Hiển thị trang chi tiết xe dành cho Khách hàng
    public function show(Car $car): View
    {
        $car->load('brand');

        $reviews = Review::query()
            ->with('user:user_id,name')
            ->where('car_id', $car->car_id)
            ->orderByDesc('created_at')
            ->paginate(8)
            ->withQueryString();

        $avgRating = (float) (Review::where('car_id', $car->car_id)->avg('rating') ?? 0);
        $reviewCount = Review::where('car_id', $car->car_id)->count();

        $userReview = null;
        $canReview = false;
        if (auth()->check()) {
            $userReview = Review::where('car_id', $car->car_id)
                ->where('user_id', auth()->id())
                ->first();

            $hasDepositOrBuy = OrderDetail::query()
                ->where('car_id', $car->car_id)
                ->whereHas('order', function ($query) {
                    $query->where('user_id', auth()->id())
                        ->whereIn('status', [1, 2]);
                })
                ->exists();

            $hasTestDrive = false;
            if (Schema::hasColumn('support_tickets', 'ticket_type') && Schema::hasColumn('support_tickets', 'car_id')) {
                $hasTestDrive = Ticket::query()
                    ->where('user_id', auth()->id())
                    ->where('ticket_type', 'test_drive')
                    ->where('car_id', $car->car_id)
                    ->exists();
            }

            $canReview = $hasDepositOrBuy || $hasTestDrive;
        }

        return view('client.show', [
            'car' => $car,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'reviewCount' => $reviewCount,
            'userReview' => $userReview,
            'canReview' => $canReview,
        ]);
    }

    public function storeReview(Request $request, Car $car): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'customer') {
            return back()
                ->withInput()
                ->withErrors(['review' => 'Chỉ tài khoản khách hàng mới có thể gửi đánh giá sản phẩm.']);
        }

        $canReview = OrderDetail::query()
            ->where('car_id', $car->car_id)
            ->whereHas('order', function ($query) use ($user) {
                $query->where('user_id', $user->user_id)
                    ->whereIn('status', [1, 2]);
            })
            ->exists();

        $hasTestDrive = false;
        if (Schema::hasColumn('support_tickets', 'ticket_type') && Schema::hasColumn('support_tickets', 'car_id')) {
            $hasTestDrive = Ticket::query()
                ->where('user_id', $user->user_id)
                ->where('ticket_type', 'test_drive')
                ->where('car_id', $car->car_id)
                ->exists();
        }

        if (! $canReview && ! $hasTestDrive) {
            return back()
                ->withInput()
                ->withErrors(['review' => 'Bạn cần đặt lịch lái thử hoặc đặt cọc xe này trước khi gửi đánh giá.']);
        }

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
        ]);

        Review::updateOrCreate(
            [
                'user_id' => $user->user_id,
                'car_id' => $car->car_id,
            ],
            [
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]
        );

        return redirect()
            ->route('cars.show_public', $car->car_id)
            ->withFragment('danh-gia')
            ->with('review_success', 'Đã lưu đánh giá của bạn. Cảm ơn bạn!');
    }
}
