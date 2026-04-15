<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Car;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        // Create a local demo image so UI (asset('storage/...')) always works.
        $demoImagePath = 'images/demo-car.svg';
        if (! Storage::disk('public')->exists($demoImagePath)) {
            $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="675" viewBox="0 0 1200 675">
  <defs>
    <linearGradient id="bg" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#0b1220"/>
      <stop offset="1" stop-color="#111827"/>
    </linearGradient>
  </defs>
  <rect width="1200" height="675" fill="url(#bg)"/>
  <g fill="none" stroke="#e5e7eb" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" opacity="0.9">
    <path d="M210 430c35-80 85-130 155-150 70-20 270-20 340 0 70 20 120 70 155 150"/>
    <path d="M210 430h780"/>
    <path d="M330 430c10 60 50 95 110 95s100-35 110-95"/>
    <path d="M650 430c10 60 50 95 110 95s100-35 110-95"/>
    <path d="M430 280h340"/>
  </g>
  <g fill="#e5e7eb" opacity="0.85">
    <circle cx="440" cy="520" r="58"/>
    <circle cx="760" cy="520" r="58"/>
  </g>
  <text x="60" y="110" font-family="ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto" font-size="48" fill="#e5e7eb" opacity="0.9">Demo Car Image</text>
  <text x="60" y="165" font-family="ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto" font-size="24" fill="#9ca3af">Used for seeded cars</text>
</svg>
SVG;

            Storage::disk('public')->put($demoImagePath, $svg);
        }

        $brands = [
            ['name' => 'Mercedes-Benz', 'country' => 'Germany'],
            ['name' => 'BMW', 'country' => 'Germany'],
            ['name' => 'Toyota', 'country' => 'Japan'],
            ['name' => 'Honda', 'country' => 'Japan'],
            ['name' => 'VinFast', 'country' => 'Vietnam'],
            ['name' => 'Ford', 'country' => 'USA'],
        ];

        $models = [
            'Sedan', 'SUV', 'Coupe', 'Hatchback', 'Crossover', 'Pickup',
        ];

        $colors = [
            'Black', 'White', 'Silver', 'Gray', 'Blue', 'Red',
        ];

        // Seed 15 cars (adjustable 10-20 as requested)
        $rows = [];
        for ($i = 1; $i <= 15; $i++) {
            $brand = $brands[($i - 1) % count($brands)];
            $name = $brand['name'].' '.$models[($i - 1) % count($models)].' '.$i;
            $rows[] = [
                'brand' => $brand['name'],
                'name' => $name,
                'year' => 2018 + ($i % 8),
                'price' => 350000000 + ($i * 75000000),
                'color' => $colors[($i - 1) % count($colors)],
                'description' => 'Xe demo du lieu (seed).',
                'image' => $demoImagePath,
                'stock' => ($i % 7) + 1,
                'mileage_km' => 5000 * $i,
                'fuel_type' => ($i % 3 === 0) ? 'Electric' : (($i % 2 === 0) ? 'Diesel' : 'Gasoline'),
                'transmission' => ($i % 2 === 0) ? 'Automatic' : 'Manual',
                'is_featured' => ($i % 5 === 0),
            ];
        }

        foreach ($rows as $row) {

            // 🔥 Tạo hoặc lấy brand
            $brand = Brand::firstOrCreate(
                ['name' => $row['brand']],
                ['country' => 'Unknown']
            );

            // ❌ bỏ brand khỏi car
            unset($row['brand']);

            // 🔥 gán khóa ngoại
            $row['brand_id'] = $brand->brand_id;

            // Avoid duplicating on reruns (same brand_id + name treated as unique for seed)
            Car::updateOrCreate(
                [
                    'brand_id' => $row['brand_id'],
                    'name' => $row['name'],
                ],
                $row
            );
        }
    }
}
