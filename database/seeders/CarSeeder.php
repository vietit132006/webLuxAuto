<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Brand;
use Illuminate\Database\Seeder;

class CarSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'brand' => 'Mercedes-Benz',
                'name' => 'C300 AMG',
                'year' => 2022,
                'price' => 1850000000,
                'color' => 'Đen Obsidian',
                'description' => 'Xe lướt bảo hành chính hãng, full option, camera 360.',
                'image' => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&q=80',
                'stock' => 3,
            ],
            [
                'brand' => 'BMW',
                'name' => '530i M Sport',
                'year' => 2021,
                'price' => 2120000000,
                'color' => 'Trắng Alpine',
                'description' => 'Nội thất da Vernasca, HUD, hệ thống an toàn Driving Assistant.',
                'image' => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800&q=80',
                'stock' => 2,
            ],
        ];

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

            Car::create($row);
        }
    }
}
