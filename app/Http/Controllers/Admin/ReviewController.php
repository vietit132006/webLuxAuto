<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ReviewsExport;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Review;
use App\Models\ReviewReport;
use App\Support\ReviewQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $filters = ReviewQuery::cleanFilters($request);
        $canModerate = $request->user()->can('reviews.moderate');

        $reviewsQuery = Review::query()
            ->with(['user', 'car.carModel.brand', 'repliedBy'])
            ->withCount('images')
            ->select('reviews.*');

        ReviewQuery::applyFilters($reviewsQuery, $filters);
        $this->scopeForViewer($reviewsQuery, $canModerate);
        ReviewQuery::applySorting($reviewsQuery, $filters['sort']);

        $statsQuery = Review::query();
        $this->scopeForViewer($statsQuery, $canModerate);

        $statusCounts = (clone $statsQuery)
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        return view('admin.reviews.index', [
            'filters' => $filters,
            'reviews' => $reviewsQuery->paginate(25)->withQueryString(),
            'stats' => [
                'total' => (int) (clone $statsQuery)->count(),
                'pending' => (int) ($statusCounts[Review::STATUS_PENDING] ?? 0),
                'approved' => (int) ($statusCounts[Review::STATUS_APPROVED] ?? 0),
                'rejected' => (int) ($statusCounts[Review::STATUS_REJECTED] ?? 0),
                'hidden' => (int) ($statusCounts[Review::STATUS_HIDDEN] ?? 0),
                'reported' => (int) ($statusCounts[Review::STATUS_REPORTED] ?? 0),
                'low_rating' => (int) (clone $statsQuery)->where('rating', '<=', 2)->count(),
                'avg_rating' => (float) ((clone $statsQuery)->where('status', Review::STATUS_APPROVED)->avg('rating') ?? 0),
            ],
            'statusOptions' => Review::statusOptions(),
            'verifiedTypeOptions' => Review::verifiedTypeOptions(),
            ...$this->filterOptions(),
        ]);
    }

    public function show(Request $request, Review $review): View
    {
        if (!$request->user()->can('reviews.moderate')) {
            abort_unless($review->status === Review::STATUS_APPROVED, 403);
        }

        $review->load([
            'user',
            'car.carModel.brand',
            'order',
            'ticket',
            'serviceRecord',
            'images',
            'reports.user',
            'reports.handledBy',
            'approvedBy',
            'rejectedBy',
            'repliedBy',
        ])->loadCount(['images', 'votes', 'reports']);

        return view('admin.reviews.show', compact('review'));
    }

    public function approve(Request $request, Review $review): RedirectResponse
    {
        DB::transaction(function () use ($request, $review): void {
            $review->forceFill([
                'status' => Review::STATUS_APPROVED,
                'approved_by' => $request->user()->user_id,
                'approved_at' => now(),
                'rejected_by' => null,
                'rejected_at' => null,
                'rejected_reason' => null,
            ])->save();

            $review->reports()
                ->where('status', ReviewReport::STATUS_PENDING)
                ->update([
                    'status' => ReviewReport::STATUS_HANDLED,
                    'handled_by' => $request->user()->user_id,
                    'handled_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        return back()->with('success', 'Đã duyệt đánh giá.');
    }

    public function reject(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'rejected_reason' => 'required|string|max:1000',
        ]);

        $review->forceFill([
            'status' => Review::STATUS_REJECTED,
            'approved_by' => null,
            'approved_at' => null,
            'rejected_by' => $request->user()->user_id,
            'rejected_at' => now(),
            'rejected_reason' => $data['rejected_reason'],
            'is_featured' => false,
        ])->save();

        return back()->with('success', 'Đã từ chối đánh giá.');
    }

    public function hide(Request $request, Review $review): RedirectResponse
    {
        $review->forceFill([
            'status' => Review::STATUS_HIDDEN,
            'is_featured' => false,
            'rejected_reason' => $request->input('rejected_reason') ?: $review->rejected_reason,
        ])->save();

        return back()->with('success', 'Đã ẩn đánh giá khỏi frontend.');
    }

    public function reply(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'reply_content' => 'required|string|max:3000',
        ]);

        $review->forceFill([
            'reply_content' => $data['reply_content'],
            'replied_by' => $request->user()->user_id,
            'replied_at' => now(),
        ])->save();

        return back()->with('success', 'Đã lưu phản hồi từ showroom.');
    }

    public function toggleFeatured(Review $review): RedirectResponse
    {
        if (!$review->isApproved()) {
            return back()->withErrors(['review' => 'Chỉ đánh dấu nổi bật cho đánh giá đã duyệt.']);
        }

        $review->forceFill([
            'is_featured' => !$review->is_featured,
        ])->save();

        return back()->with('success', 'Đã cập nhật trạng thái nổi bật.');
    }

    public function destroy(Review $review): RedirectResponse
    {
        $review->delete();

        return redirect()
            ->route('admin.reviews.index')
            ->with('success', 'Đã xóa đánh giá.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filters = ReviewQuery::cleanFilters($request);
        $approvedOnly = !$request->user()->can('reviews.moderate');

        return Excel::download(
            new ReviewsExport($filters, $approvedOnly),
            'luxauto-reviews-' . now()->format('Ymd-His') . '.xlsx'
        );
    }

    private function scopeForViewer(Builder $query, bool $canModerate): void
    {
        if (!$canModerate) {
            $query->where('status', Review::STATUS_APPROVED);
        }
    }

    private function filterOptions(): array
    {
        return [
            'brands' => Brand::query()->orderBy('name')->get(['brand_id', 'name']),
            'models' => CarModel::query()->with('brand')->orderBy('name')->get(['id', 'brand_id', 'name']),
            'cars' => Car::query()
                ->with('carModel.brand')
                ->orderBy('name')
                ->limit(500)
                ->get(['car_id', 'car_model_id', 'name', 'vin', 'internal_code']),
        ];
    }
}
