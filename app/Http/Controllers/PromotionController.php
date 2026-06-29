<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $this->filters($request);

        $query = Promotion::query()
            ->with(['targets.brand', 'targets.carModel.brand', 'targets.car.modelInfo.brand'])
            ->publiclyVisible()
            ->when($filters['promotion_type'] !== '', fn (Builder $query) => $query->where('promotion_type', $filters['promotion_type']));

        $this->applyTargetFilters($query, $filters);

        $promotions = $query
            ->orderedForDisplay()
            ->paginate(9)
            ->withQueryString();

        $featuredPromotions = Promotion::query()
            ->with(['targets.brand', 'targets.carModel.brand', 'targets.car.modelInfo.brand'])
            ->publiclyVisible()
            ->where('is_featured', true)
            ->orderedForDisplay()
            ->limit(4)
            ->get();

        $endingSoonPromotions = Promotion::query()
            ->with(['targets'])
            ->publiclyVisible()
            ->whereNotNull('end_at')
            ->whereBetween('end_at', [now(), now()->addDays(21)])
            ->orderBy('end_at')
            ->limit(5)
            ->get();

        return view('client.promotions', [
            'brands' => $this->brandsForSelect(),
            'carModels' => $this->modelsForSelect(),
            'endingSoonPromotions' => $endingSoonPromotions,
            'featuredPromotions' => $featuredPromotions,
            'filters' => $filters,
            'promotionTypes' => Promotion::TYPES,
            'promotions' => $promotions,
        ]);
    }

    public function show(Promotion $promotion): View
    {
        abort_unless($promotion->is_public && $promotion->isActive(), 404);

        $promotion->load(['targets.brand', 'targets.carModel.brand', 'targets.car.modelInfo.brand']);

        $applicableCars = $this->carsForPromotion($promotion)
            ->orderByDesc('is_featured')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        $relatedPromotions = Promotion::query()
            ->with('targets')
            ->publiclyVisible()
            ->where('id', '!=', $promotion->id)
            ->where('promotion_type', $promotion->promotion_type)
            ->orderedForDisplay()
            ->limit(3)
            ->get();

        return view('client.promotions-show', [
            'applicableCars' => $applicableCars,
            'promotion' => $promotion,
            'relatedPromotions' => $relatedPromotions,
        ]);
    }

    private function carsForPromotion(Promotion $promotion): Builder
    {
        $promotion->loadMissing('targets');

        $query = Car::query()
            ->with(['modelInfo.brand', 'brand', 'carModel.brand'])
            ->withActiveBrand();

        if ($promotion->targets->contains(fn (PromotionTarget $target): bool => $target->target_type === PromotionTarget::TYPE_ALL)) {
            return $query;
        }

        $brandIds = $promotion->targets
            ->where('target_type', PromotionTarget::TYPE_BRAND)
            ->pluck('target_id')
            ->filter()
            ->values();
        $modelIds = $promotion->targets
            ->where('target_type', PromotionTarget::TYPE_MODEL)
            ->pluck('target_id')
            ->filter()
            ->values();
        $carIds = $promotion->targets
            ->where('target_type', PromotionTarget::TYPE_CAR)
            ->pluck('target_id')
            ->filter()
            ->values();

        return $query->where(function (Builder $inner) use ($brandIds, $modelIds, $carIds): void {
            if ($brandIds->isNotEmpty()) {
                $inner->orWhereHas('modelInfo', fn (Builder $modelQuery) => $modelQuery->whereIn('brand_id', $brandIds));
            }

            if ($modelIds->isNotEmpty()) {
                $inner->orWhereIn('car_model_id', $modelIds);
            }

            if ($carIds->isNotEmpty()) {
                $inner->orWhereIn('car_id', $carIds);
            }
        });
    }

    private function filters(Request $request): array
    {
        $promotionType = (string) $request->input('promotion_type', '');
        $brandId = (string) $request->input('brand_id', '');
        $modelId = (string) $request->input('model_id', '');

        return [
            'promotion_type' => array_key_exists($promotionType, Promotion::TYPES) ? $promotionType : '',
            'brand_id' => ctype_digit($brandId) ? (int) $brandId : null,
            'model_id' => ctype_digit($modelId) ? (int) $modelId : null,
        ];
    }

    private function applyTargetFilters(Builder $query, array $filters): void
    {
        if ($filters['brand_id']) {
            $brandId = $filters['brand_id'];
            $modelIds = CarModel::query()->where('brand_id', $brandId)->pluck('id');
            $carIds = Car::query()->whereIn('car_model_id', $modelIds)->pluck('car_id');

            $query->whereHas('targets', function (Builder $targetQuery) use ($brandId, $modelIds, $carIds): void {
                $targetQuery->where('target_type', PromotionTarget::TYPE_ALL)
                    ->orWhere(function (Builder $inner) use ($brandId): void {
                        $inner->where('target_type', PromotionTarget::TYPE_BRAND)
                            ->where('target_id', $brandId);
                    })
                    ->orWhere(function (Builder $inner) use ($modelIds): void {
                        $inner->where('target_type', PromotionTarget::TYPE_MODEL)
                            ->whereIn('target_id', $modelIds);
                    })
                    ->orWhere(function (Builder $inner) use ($carIds): void {
                        $inner->where('target_type', PromotionTarget::TYPE_CAR)
                            ->whereIn('target_id', $carIds);
                    });
            });
        }

        if ($filters['model_id']) {
            $modelId = $filters['model_id'];
            $brandId = CarModel::query()->whereKey($modelId)->value('brand_id');
            $carIds = Car::query()->where('car_model_id', $modelId)->pluck('car_id');

            $query->whereHas('targets', function (Builder $targetQuery) use ($modelId, $brandId, $carIds): void {
                $targetQuery->where('target_type', PromotionTarget::TYPE_ALL)
                    ->orWhere(function (Builder $inner) use ($brandId): void {
                        $inner->where('target_type', PromotionTarget::TYPE_BRAND)
                            ->where('target_id', $brandId);
                    })
                    ->orWhere(function (Builder $inner) use ($modelId): void {
                        $inner->where('target_type', PromotionTarget::TYPE_MODEL)
                            ->where('target_id', $modelId);
                    })
                    ->orWhere(function (Builder $inner) use ($carIds): void {
                        $inner->where('target_type', PromotionTarget::TYPE_CAR)
                            ->whereIn('target_id', $carIds);
                    });
            });
        }
    }

    private function brandsForSelect()
    {
        return Brand::query()
            ->active()
            ->orderBy('name')
            ->get(['brand_id', 'name']);
    }

    private function modelsForSelect()
    {
        return CarModel::query()
            ->with('brand')
            ->orderBy('name')
            ->get(['id', 'brand_id', 'name']);
    }
}
