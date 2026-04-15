<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminStorePromotionRequest;
use App\Http\Requests\AdminUpdatePromotionRequest;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;

class AdminPromotionController extends Controller
{
    public function index(): JsonResponse
    {
        $items = Promotion::query()
            ->with(['car' => fn ($q) => $q->select('car_id', 'name')])
            ->orderByDesc('promotion_id')
            ->get();

        return response()->json(['data' => $items]);
    }

    public function store(AdminStorePromotionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $promotion = Promotion::query()->create($data);

        return response()->json(['data' => $promotion], 201);
    }

    public function show(Promotion $promotion): JsonResponse
    {
        $promotion->load(['car']);

        return response()->json(['data' => $promotion]);
    }

    public function update(AdminUpdatePromotionRequest $request, Promotion $promotion): JsonResponse
    {
        $data = $request->validated();
        if ($request->exists('is_active')) {
            $data['is_active'] = $request->boolean('is_active');
        }
        $promotion->update($data);

        return response()->json(['data' => $promotion->fresh()]);
    }

    public function destroy(Promotion $promotion): JsonResponse
    {
        $promotion->delete();

        return response()->json(['message' => 'Deleted']);
    }
}
