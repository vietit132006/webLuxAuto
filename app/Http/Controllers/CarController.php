<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Models\ReviewVote;
use App\Services\ReviewEligibilityService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CarController extends Controller
{
    public function index(Request $request)
    {
        $brands = Brand::query()
            ->active()
            ->whereHas('carModels.cars', function (Builder $query): void {
                $query->availableForSale();
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $query = Car::query()
            ->with(['brand', 'carModel.brand'])
            ->withActiveBrand();

        $query->when($request->keyword, function ($q, $keyword) {
            return $q->where('name', 'like', '%' . $keyword . '%');
        });

        $query->when($request->brand_id, function ($q, $brand_id) {
            return $q->whereHas('carModel.brand', function ($brandQuery) use ($brand_id) {
                $brandQuery->active()
                    ->where('brand_id', $brand_id);
            });
        });

        $query->when($request->has('status') && $request->status != '', function ($q) use ($request) {
            return $q->where('status', $request->status);
        });

        $query->when($request->min_price, function ($q, $min_price) {
            return $q->where('price', '>=', $min_price);
        });

        $query->when($request->max_price, function ($q, $max_price) {
            return $q->where('price', '<=', $max_price);
        });

        $cars = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        return view('client.index', compact('cars', 'brands'));
    }

    public function show(Request $request, Car $car, ReviewEligibilityService $eligibilityService): View
    {
        $car->load(['brand', 'carModel.brand', 'images']);
        abort_unless((bool) $car->carModel?->brand?->is_active, 404);

        $reviewFilter = in_array($request->query('review_filter'), ['latest', 'highest', 'lowest', 'with_images', 'purchase'], true)
            ? (string) $request->query('review_filter')
            : 'latest';

        $approvedReviewsBase = Review::query()
            ->where('car_id', $car->car_id)
            ->where('status', Review::STATUS_APPROVED);

        $reviewDistribution = (clone $approvedReviewsBase)
            ->selectRaw('rating, COUNT(*) as cnt')
            ->groupBy('rating')
            ->pluck('cnt', 'rating');

        $reviewsQuery = Review::query()
            ->with(['user:user_id,name', 'images'])
            ->where('car_id', $car->car_id)
            ->where('status', Review::STATUS_APPROVED)
            ->when($reviewFilter === 'with_images', fn (Builder $query) => $query->whereHas('images'))
            ->when($reviewFilter === 'purchase', fn (Builder $query) => $query->where('verified_type', Review::VERIFIED_PURCHASE));

        match ($reviewFilter) {
            'highest' => $reviewsQuery->orderByDesc('rating')->orderByDesc('created_at'),
            'lowest' => $reviewsQuery->orderBy('rating')->orderByDesc('created_at'),
            default => $reviewsQuery->orderByDesc('created_at'),
        };

        $reviews = $reviewsQuery
            ->paginate(8)
            ->withQueryString();

        $reviewCount = (int) ($car->reviews_count ?? (clone $approvedReviewsBase)->count());
        $avgRating = $reviewCount > 0
            ? (float) ($car->avg_rating ?? ((clone $approvedReviewsBase)->avg('rating') ?? 0))
            : 0.0;

        $userReview = null;
        $canReview = false;
        $reviewEligibility = [
            'can_review' => false,
            'verified_type' => Review::VERIFIED_NONE,
            'order_id' => null,
            'ticket_id' => null,
            'service_record_id' => null,
        ];

        if (auth()->check()) {
            $userReview = Review::query()
                ->where('car_id', $car->car_id)
                ->where('user_id', auth()->id())
                ->with('images')
                ->first();

            if (auth()->user()?->role === 'customer') {
                $reviewEligibility = $eligibilityService->resolve($car, auth()->user());
                $canReview = (bool) $reviewEligibility['can_review'];
            }
        }

        return view('client.show', [
            'car' => $car,
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'reviewCount' => $reviewCount,
            'reviewDistribution' => $reviewDistribution,
            'reviewFilter' => $reviewFilter,
            'userReview' => $userReview,
            'canReview' => $canReview,
            'reviewEligibility' => $reviewEligibility,
        ]);
    }

    public function storeReview(Request $request, Car $car, ReviewEligibilityService $eligibilityService): RedirectResponse
    {
        $user = $request->user();
        if ($user->role !== 'customer') {
            return back()
                ->withInput()
                ->withErrors(['review' => 'Chỉ tài khoản khách hàng mới có thể gửi đánh giá sản phẩm.']);
        }

        $reviewEligibility = $eligibilityService->resolve($car, $user);

        if (!$reviewEligibility['can_review']) {
            return back()
                ->withInput()
                ->withErrors(['review' => 'Bạn cần mua xe, đặt cọc, hoàn thành lái thử hoặc đã dùng dịch vụ liên quan trước khi gửi đánh giá.']);
        }

        $data = $request->validate([
            'title' => 'nullable|string|max:150',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:3000',
            'images' => 'nullable|array|max:6',
            'images.*' => 'file|mimes:jpg,jpeg,png,webp|max:3072',
            'truthful' => 'accepted',
        ]);

        if ((int) $data['rating'] <= 2 && mb_strlen(trim((string) ($data['comment'] ?? ''))) < 20) {
            return back()
                ->withInput()
                ->withErrors(['comment' => 'Vui lòng mô tả rõ lý do khi đánh giá 1-2 sao.']);
        }

        $review = DB::transaction(function () use ($request, $user, $car, $data, $reviewEligibility): Review {
            $review = Review::query()
                ->where('user_id', $user->user_id)
                ->where('car_id', $car->car_id)
                ->first();

            if (!$review) {
                $review = new Review([
                    'user_id' => $user->user_id,
                    'car_id' => $car->car_id,
                ]);
            }

            $review->forceFill([
                'title' => $data['title'] ?? null,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
                'status' => Review::STATUS_PENDING,
                'verified_type' => $reviewEligibility['verified_type'],
                'order_id' => $reviewEligibility['order_id'],
                'ticket_id' => $reviewEligibility['ticket_id'],
                'service_record_id' => $reviewEligibility['service_record_id'],
                'approved_by' => null,
                'approved_at' => null,
                'rejected_by' => null,
                'rejected_at' => null,
                'rejected_reason' => null,
                'is_featured' => false,
            ])->save();

            if ($request->hasFile('images')) {
                $review->load('images');

                foreach ($review->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                    $image->delete();
                }

                foreach ($request->file('images', []) as $index => $file) {
                    $review->images()->create([
                        'image_path' => $file->store('review-images/' . $review->review_id, 'public'),
                        'sort_order' => $index,
                    ]);
                }
            }

            return $review;
        });

        app(\App\Services\AdminNotificationService::class)->createOnce(
            'reviews',
            (int) $review->rating <= 2 ? 'review_low_rating' : 'review_pending',
            (int) $review->rating <= 2 ? 'Danh gia 1-2 sao can xu ly' : 'Danh gia moi cho duyet',
            'Review #' . $review->review_id . ' dang cho kiem duyet, rating ' . $review->rating . '/5.',
            route('admin.reviews.index', ['status' => Review::STATUS_PENDING], false),
            ['review_id' => $review->review_id, 'rating' => $review->rating],
            (int) $review->rating <= 2
                ? \App\Models\AdminNotification::PRIORITY_HIGH
                : \App\Models\AdminNotification::PRIORITY_NORMAL
        );

        return redirect()
            ->route('cars.show_public', $car->car_id)
            ->withFragment('danh-gia')
            ->with('review_success', 'Đánh giá của bạn đã được gửi và đang chờ duyệt.');
    }

    public function voteReview(Request $request, Car $car, Review $review): RedirectResponse
    {
        abort_unless((int) $review->car_id === (int) $car->car_id && $review->canBeShown(), 404);

        if ((int) $review->user_id === (int) $request->user()->user_id) {
            return back()
                ->withFragment('danh-gia')
                ->withErrors(['review' => 'Bạn không thể tự đánh dấu hữu ích cho đánh giá của mình.']);
        }

        $vote = ReviewVote::firstOrCreate(
            [
                'review_id' => $review->review_id,
                'user_id' => $request->user()->user_id,
            ],
            [
                'type' => ReviewVote::TYPE_HELPFUL,
            ]
        );

        if (!$vote->wasRecentlyCreated) {
            return back()
                ->withFragment('danh-gia')
                ->with('review_success', 'Bạn đã đánh dấu hữu ích cho đánh giá này.');
        }

        $review->forceFill([
            'helpful_count' => $review->votes()->where('type', ReviewVote::TYPE_HELPFUL)->count(),
        ])->save();

        return back()
            ->withFragment('danh-gia')
            ->with('review_success', 'Cảm ơn bạn đã đánh dấu đánh giá hữu ích.');
    }

    public function reportReview(Request $request, Car $car, Review $review): RedirectResponse
    {
        abort_unless((int) $review->car_id === (int) $car->car_id && $review->canBeShown(), 404);

        if ((int) $review->user_id === (int) $request->user()->user_id) {
            return back()
                ->withFragment('danh-gia')
                ->withErrors(['review' => 'Bạn không thể báo cáo đánh giá của mình.']);
        }

        $data = $request->validate([
            'reason' => 'required|string|max:120',
            'note' => 'nullable|string|max:1000',
        ]);

        $report = ReviewReport::firstOrCreate(
            [
                'review_id' => $review->review_id,
                'user_id' => $request->user()->user_id,
            ],
            [
                'reason' => $data['reason'],
                'note' => $data['note'] ?? null,
            ]
        );

        if (!$report->wasRecentlyCreated) {
            return back()
                ->withFragment('danh-gia')
                ->with('review_success', 'Bạn đã báo cáo đánh giá này trước đó.');
        }

        $review->forceFill([
            'status' => Review::STATUS_REPORTED,
            'report_count' => $review->reports()->count(),
            'is_featured' => false,
        ])->save();

        app(\App\Services\AdminNotificationService::class)->createOnce(
            'reviews',
            'review_reported',
            'Review bi bao cao',
            'Review #' . $review->review_id . ' vua bi khach bao cao can kiem tra.',
            route('admin.reviews.index', ['status' => Review::STATUS_REPORTED], false),
            ['review_id' => $review->review_id, 'report_id' => $report->id],
            \App\Models\AdminNotification::PRIORITY_HIGH
        );

        return back()
            ->withFragment('danh-gia')
            ->with('review_success', 'Cảm ơn bạn. Đánh giá đã được gửi cho quản trị viên kiểm tra.');
    }
}
