<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Car;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Car>
 */
class CarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'brand_id' => Brand::factory(),
            'model' => strtoupper($this->faker->bothify('??-###')),
            'year' => $this->faker->numberBetween(2018, 2025),
            'price' => $this->faker->numberBetween(300000000, 2000000000),
            'mileage_km' => $this->faker->numberBetween(0, 100000),
            'fuel_type' => $this->faker->randomElement(['Gasoline', 'Diesel', 'Electric']),
            'transmission' => $this->faker->randomElement(['Manual', 'Automatic']),
            'color' => $this->faker->safeColorName,
            'description' => $this->faker->sentence(12),
            'image_url' => $this->faker->imageUrl(640, 480, 'car', true),
            'is_featured' => $this->faker->boolean(),
        ];
    }
}
