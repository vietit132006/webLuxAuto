<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CarPromotionController extends Controller
{
    public function index(Request $request, Car $car): JsonResponse
    {
        $onlyActive = $request->boolean('active', true);

        $query = Promotion::query()
            ->where('car_id', $car->car_id)
            ->orderByDesc('promotion_id');

        if ($onlyActive) {
            $query->activeNow();
        }

        $promotions = $query->paginate(10);

        $car->load(['promotions' => fn ($q) => $q->activeNow()->orderByDesc('promotion_id')]);

        return response()->json([
            'car' => [
                'car_id' => $car->car_id,
                'name' => $car->name,
                'list_price' => (string) $car->price,
                'discounted_price' => $car->discounted_price,
                'final_price_float' => $car->final_price_float,
            ],
            'promotions' => $promotions,
        ]);
    }
}
