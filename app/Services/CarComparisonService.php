<?php

namespace App\Services;

use App\Models\Car;
use Illuminate\Support\Collection;

class CarComparisonService
{
    /**
     * @param  array<int>  $carIds
     * @return Collection<int, array<string, mixed>>
     */
    public function compare(array $carIds): Collection
    {
        if ($carIds === []) {
            return collect();
        }

        $idsSql = implode(',', array_map('intval', $carIds));

        $cars = Car::query()
            ->with(['brand', 'promotions' => fn ($q) => $q->activeNow()->orderByDesc('promotion_id')])
            ->whereIn('car_id', $carIds)
            ->orderByRaw('FIELD(car_id, '.$idsSql.')')
            ->get();

        return $cars->map(fn (Car $car) => $this->serializeCar($car));
    }

    /**
     * @return array<string, mixed>
     */
    protected function serializeCar(Car $car): array
    {
        $promo = $car->relationLoaded('promotions')
            ? $car->promotions->first()
            : $car->activePromotion();

        return [
            'car_id' => $car->car_id,
            'name' => $car->name,
            'brand' => $car->brand?->name,
            'price' => (string) $car->price,
            'discounted_price' => $car->discounted_price,
            'final_price_float' => $car->final_price_float,
            'year' => $car->year,
            'color' => $car->color,
            'stock' => $car->stock,
            'mileage_km' => $car->mileage_km,
            'fuel_type' => $car->fuel_type,
            'transmission' => $car->transmission,
            'is_featured' => (bool) $car->is_featured,
            'description' => $car->description,
            'specifications' => [
                'mileage_km' => $car->mileage_km,
                'fuel_type' => $car->fuel_type,
                'transmission' => $car->transmission,
                'year' => $car->year,
                'color' => $car->color,
            ],
            'features' => [
                'is_featured' => (bool) $car->is_featured,
                'description_excerpt' => $car->description ? mb_substr(strip_tags($car->description), 0, 280) : null,
            ],
            'active_promotion' => $promo ? [
                'title' => $promo->title,
                'discount_type' => $promo->discount_type,
                'discount_value' => (string) $promo->discount_value,
            ] : null,
        ];
    }
}
