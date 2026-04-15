<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;

class ReviewController extends Controller
{
    public function store(StoreReviewRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $userId = (int) $request->user()->user_id;
        $carId = (int) $data['car_id'];

        $review = Review::withTrashed()->firstOrNew([
            'user_id' => $userId,
            'car_id' => $carId,
        ]);
        $review->rating = $data['rating'];
        $review->comment = $data['comment'] ?? null;
        if ($review->trashed()) {
            $review->restore();
        } else {
            $review->save();
        }

        return redirect()
            ->route('cars.show_public', ['car' => $carId])
            ->with('success', 'Da luu danh gia.');
    }
}
