<?php

namespace App\Services;

use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReviewRatingService
{
    public function refreshForCar(int $carId): void
    {
        if (
            $carId <= 0
            || !Schema::hasTable('cars')
            || !Schema::hasColumn('cars', 'avg_rating')
            || !Schema::hasColumn('cars', 'reviews_count')
        ) {
            return;
        }

        $stats = Review::query()
            ->where('car_id', $carId)
            ->where('status', Review::STATUS_APPROVED)
            ->selectRaw('ROUND(AVG(rating), 2) as avg_rating, COUNT(*) as reviews_count')
            ->first();

        DB::table('cars')
            ->where('car_id', $carId)
            ->update([
                'avg_rating' => $stats && (int) $stats->reviews_count > 0 ? $stats->avg_rating : null,
                'reviews_count' => (int) ($stats->reviews_count ?? 0),
            ]);
    }

    public function refreshAll(): void
    {
        if (
            !Schema::hasTable('cars')
            || !Schema::hasColumn('cars', 'avg_rating')
            || !Schema::hasColumn('cars', 'reviews_count')
        ) {
            return;
        }

        DB::table('cars')->update([
            'avg_rating' => null,
            'reviews_count' => 0,
        ]);

        Review::query()
            ->where('status', Review::STATUS_APPROVED)
            ->selectRaw('car_id, ROUND(AVG(rating), 2) as avg_rating, COUNT(*) as reviews_count')
            ->groupBy('car_id')
            ->orderBy('car_id')
            ->chunk(200, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('cars')
                        ->where('car_id', $row->car_id)
                        ->update([
                            'avg_rating' => $row->avg_rating,
                            'reviews_count' => (int) $row->reviews_count,
                        ]);
                }
            });
    }
}
