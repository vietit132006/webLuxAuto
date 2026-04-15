<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;

class PromotionPublicController extends Controller
{
    public function active(): JsonResponse
    {
        $promotions = Promotion::query()
            ->activeNow()
            ->with([
                'car' => fn ($q) => $q->select('car_id', 'name', 'price', 'brand_id')
                    ->with(['promotions' => fn ($pq) => $pq->activeNow()->orderByDesc('promotion_id')]),
            ])
            ->orderByDesc('promotion_id')
            ->get()
            ->map(function (Promotion $p) {
                $car = $p->car;
                $final = $car ? $car->final_price_float : null;

                return [
                    'promotion_id' => $p->promotion_id,
                    'title' => $p->title,
                    'description' => $p->description,
                    'discount_type' => $p->discount_type,
                    'discount_value' => (string) $p->discount_value,
                    'starts_at' => $p->starts_at?->toIso8601String(),
                    'ends_at' => $p->ends_at?->toIso8601String(),
                    'car' => $car ? [
                        'car_id' => $car->car_id,
                        'name' => $car->name,
                        'list_price' => (string) $car->price,
                        'discounted_price' => $car->discounted_price,
                        'final_price_float' => $final,
                    ] : null,
                ];
            });

        return response()->json(['data' => $promotions]);
    }

    public function index(): JsonResponse
    {
        $promotions = Promotion::query()
            ->activeNow()
            ->with([
                'car' => fn ($q) => $q->select('car_id', 'name', 'price', 'brand_id')
                    ->with(['promotions' => fn ($pq) => $pq->activeNow()->orderByDesc('promotion_id')]),
            ])
            ->orderByDesc('promotion_id')
            ->paginate(12);

        $promotions->getCollection()->transform(function (Promotion $p) {
            $car = $p->car;
            $final = $car ? $car->final_price_float : null;

            return [
                'promotion_id' => $p->promotion_id,
                'title' => $p->title,
                'description' => $p->description,
                'discount_type' => $p->discount_type,
                'discount_value' => (string) $p->discount_value,
                'starts_at' => $p->starts_at?->toIso8601String(),
                'ends_at' => $p->ends_at?->toIso8601String(),
                'car' => $car ? [
                    'car_id' => $car->car_id,
                    'name' => $car->name,
                    'list_price' => (string) $car->price,
                    'discounted_price' => $car->discounted_price,
                    'final_price_float' => $final,
                ] : null,
            ];
        });

        return response()->json($promotions);
    }
}
