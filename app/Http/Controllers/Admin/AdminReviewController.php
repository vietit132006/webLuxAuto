<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class AdminReviewController extends Controller
{
    public function index(): JsonResponse
    {
        $reviews = Review::query()
            ->with(['user:user_id,name,email', 'car:car_id,name'])
            ->orderByDesc('review_id')
            ->paginate(30);

        return response()->json($reviews);
    }

    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return response()->json(['message' => 'Review removed']);
    }
}
