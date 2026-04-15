<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class CarReviewReportController extends Controller
{
    public function show(Car $car): JsonResponse
    {
        $stats = Review::query()
            ->where('car_id', $car->car_id)
            ->whereNull('deleted_at')
            ->selectRaw('COUNT(*) as review_count, AVG(rating) as average_rating')
            ->first();

        $distribution = Review::query()
            ->where('car_id', $car->car_id)
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

        $recent = Review::query()
            ->where('car_id', $car->car_id)
            ->whereNull('deleted_at')
            ->with(['user:user_id,name,email'])
            ->orderByDesc('review_id')
            ->limit(10)
            ->get()
            ->map(fn (Review $r) => [
                'review_id' => $r->review_id,
                'rating' => $r->rating,
                'comment' => $r->comment,
                'created_at' => $r->created_at?->toIso8601String(),
                'user' => $r->user ? [
                    'user_id' => $r->user->user_id,
                    'name' => $r->user->name,
                    'email' => $r->user->email,
                ] : null,
            ]);

        return response()->json([
            'car' => [
                'car_id' => $car->car_id,
                'name' => $car->name,
            ],
            'summary' => [
                'review_count' => (int) ($stats?->review_count ?? 0),
                'average_rating' => round((float) ($stats?->average_rating ?? 0), 2),
                'rating_distribution' => $distribution,
            ],
            'recent_reviews' => $recent,
        ]);
    }
}
