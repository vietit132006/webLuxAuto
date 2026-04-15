<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use Illuminate\Http\JsonResponse;

class InventoryPublicController extends Controller
{
    public function summary(): JsonResponse
    {
        $cars = Car::query()
            ->orderBy('car_id')
            ->get(['car_id', 'name', 'stock', 'price']);

        return response()->json([
            'data' => $cars->map(fn (Car $c) => [
                'car_id' => $c->car_id,
                'name' => $c->name,
                'stock' => $c->stock,
                'price' => (string) $c->price,
            ]),
        ]);
    }
}
