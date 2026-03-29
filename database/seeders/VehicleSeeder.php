<?php

namespace Database\Seeders;

use App\Models\Vehicle;
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
                'price' => 1_850_000_000,
                'mileage_km' => 28_500,
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
                'price' => 2_120_000_000,
                'mileage_km' => 35_200,
                'fuel_type' => 'Xăng',
                'transmission' => 'Tự động Steptronic',
                'color' => 'Trắng Alpine',
                'description' => 'Nội thất da Vernasca, HUD, hệ thống an toàn Driving Assistant.',
                'image_url' => 'https://images.unsplash.com/photo-1555215695-3004980ad54e?w=800&q=80',
                'is_featured' => true,
            ],
            [
                'brand' => 'Audi',
                'model' => 'A6 45 TFSI',
                'year' => 2023,
                'price' => 2_450_000_000,
                'mileage_km' => 12_000,
                'fuel_type' => 'Xăng',
                'transmission' => 'S tronic',
                'color' => 'Xám Daytona',
                'description' => 'Mới 95%, còn bảo hành, matrix LED, digital cockpit.',
                'image_url' => 'https://images.unsplash.com/photo-1606664515524-ed2f786a0bd6?w=800&q=80',
                'is_featured' => true,
            ],
            [
                'brand' => 'Lexus',
                'model' => 'ES 250',
                'year' => 2020,
                'price' => 1_620_000_000,
                'mileage_km' => 48_000,
                'fuel_type' => 'Xăng',
                'transmission' => 'CVT',
                'color' => 'Bạc Sonic',
                'description' => 'Im lặng đặc trưng Lexus, ghế chỉnh điện, cốp điện.',
                'image_url' => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?w=800&q=80',
                'is_featured' => false,
            ],
            [
                'brand' => 'Porsche',
                'model' => 'Cayenne S',
                'year' => 2022,
                'price' => 4_890_000_000,
                'mileage_km' => 19_800,
                'fuel_type' => 'Xăng',
                'transmission' => 'PDK',
                'color' => 'Đỏ Carmine',
                'description' => 'Sport Chrono, khung thể thao, âm thanh BOSE.',
                'image_url' => 'https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?w=800&q=80',
                'is_featured' => true,
            ],
            [
                'brand' => 'VinFast',
                'model' => 'VF 8',
                'year' => 2024,
                'price' => 1_090_000_000,
                'mileage_km' => 8_500,
                'fuel_type' => 'Điện',
                'transmission' => '1 cấp',
                'color' => 'Đỏ Crimson',
                'description' => 'Pin thuê theo tháng, ADAS đầy đủ, màn hình kép.',
                'image_url' => 'https://images.unsplash.com/photo-1593941707882-a5bba14938c7?w=800&q=80',
                'is_featured' => false,
            ],
        ];

        foreach ($rows as $row) {
            Vehicle::create($row);
        }
    }
}
