<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Order;
use App\Models\Promotion;
use App\Models\Quote;
use Illuminate\Support\Collection;

class PromotionApplicationService
{
    public function applicablePromotionsForCar(Car $car): Collection
    {
        $car->loadMissing('modelInfo.brand');

        return Promotion::query()
            ->with(['targets.brand', 'targets.carModel.brand', 'targets.car.modelInfo.brand'])
            ->effective()
            ->orderedForDisplay()
            ->get()
            ->filter(fn (Promotion $promotion): bool => $promotion->isApplicableToCar($car))
            ->values();
    }

    public function applicationPayloadForCar(int $carId, float $vehiclePrice, array $promotionIds): Collection
    {
        $promotionIds = collect($promotionIds)
            ->filter(fn (mixed $id): bool => is_numeric($id))
            ->map(fn (mixed $id): int => (int) $id)
            ->unique()
            ->values();

        if ($promotionIds->isEmpty()) {
            return collect();
        }

        $car = Car::query()
            ->with('modelInfo.brand')
            ->findOrFail($carId);

        $promotions = Promotion::query()
            ->with(['targets.brand', 'targets.carModel.brand', 'targets.car.modelInfo.brand'])
            ->effective()
            ->whereIn('id', $promotionIds)
            ->orderedForDisplay()
            ->get()
            ->filter(fn (Promotion $promotion): bool => $promotion->isApplicableToCar($car));

        return $promotions
            ->map(fn (Promotion $promotion): array => [
                'promotion_id' => $promotion->id,
                'discount_amount' => $promotion->calculateDiscountAmount($vehiclePrice),
                'gift_note' => $promotion->gift_description,
            ])
            ->values();
    }

    public function syncQuotePromotions(Quote $quote, Collection $payloads): float
    {
        $quote->loadMissing('quotePromotions');

        $beforeIds = $quote->quotePromotions->pluck('promotion_id');
        $payloadsByPromotion = $payloads->keyBy('promotion_id');
        $nextIds = $payloadsByPromotion->keys();

        if ($nextIds->isEmpty()) {
            $quote->quotePromotions()->delete();
        } else {
            $quote->quotePromotions()
                ->whereNotIn('promotion_id', $nextIds->all())
                ->delete();
        }

        foreach ($payloadsByPromotion as $payload) {
            $quote->quotePromotions()->updateOrCreate(
                ['promotion_id' => $payload['promotion_id']],
                [
                    'discount_amount' => $payload['discount_amount'],
                    'gift_note' => $payload['gift_note'],
                ]
            );
        }

        $this->refreshUsageCounts($beforeIds->merge($nextIds)->all());

        return (float) $payloadsByPromotion->sum(fn (array $payload): float => (float) $payload['discount_amount']);
    }

    public function copyQuotePromotionsToOrder(Quote $quote, Order $order): void
    {
        $quote->loadMissing('quotePromotions.promotion');

        if ($quote->quotePromotions->isEmpty()) {
            return;
        }

        $promotionIds = [];

        foreach ($quote->quotePromotions as $quotePromotion) {
            $order->orderPromotions()->updateOrCreate(
                ['promotion_id' => $quotePromotion->promotion_id],
                [
                    'discount_amount' => $quotePromotion->discount_amount,
                    'gift_note' => $quotePromotion->gift_note,
                ]
            );

            $promotionIds[] = $quotePromotion->promotion_id;
        }

        $this->refreshUsageCounts($promotionIds);
    }

    public function refreshUsageCounts(array $promotionIds): void
    {
        $promotionIds = collect($promotionIds)
            ->filter()
            ->unique()
            ->values();

        if ($promotionIds->isEmpty()) {
            return;
        }

        Promotion::withTrashed()
            ->whereIn('id', $promotionIds)
            ->get()
            ->each(fn (Promotion $promotion): mixed => $promotion->refreshUsageCount());
    }
}
