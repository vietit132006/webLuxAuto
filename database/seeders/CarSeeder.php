<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\Brand;
use Illuminate\Database\Seeder;

class VehicleSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            [
                'brand' => 'Mercedes-Benz',
                'model' => 'C300 AMG',
                'year' => 2022,
                'price' => 1850000000,
                'mileage_km' => 28500,
                'fuel_type' => 'Xăng',
                'transmission' => 'Tự động 9 cấp',
                'color' => 'Đen Obsidian',
                'description' => 'Xe lướt bảo hành chính hãng, full option, camera 360.',
                'image_url' => 'https://images.unsplash.com/photo-1618843479313-40f8afb4b4d8?w=800&q=80',
                'is_featured' => true,
            ],
            [
                'brand' => 'BMW',
                'model' => '530i M Sport',
                'year' => 2021,
                'price' => 2120000000,
                'mileage_km' => 35200,
                'fuel_type' => 'Xăng',
                'transmission' => 'Tự động Steptronic',
                'color' => 'Trắng Alpine',
                'description' => 'Nội thất da Vernasca, HUD, hệ thống an toàn Driving Assistant.',
                'image_url' => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800&q=80',
                'is_featured' => true,
            ],
            // thêm các xe khác...
        ];

        foreach ($rows as $row) {

            // 🔥 Tạo hoặc lấy brand
            $brand = Brand::firstOrCreate(
                ['name' => $row['brand']], // điều kiện tìm
                ['country' => 'Unknown']   // nếu chưa có thì tạo
            );

            // ❌ bỏ brand khỏi car
            unset($row['brand']);

            // 🔥 gán FK
            $row['brand_id'] = $brand->brand_id;

            Car::create($row);
        }
    }
}
