<?php

namespace Database\Seeders;

use App\Models\NewsCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Tin showroom',
            'Tư vấn mua xe',
            'Đánh giá xe',
            'Kinh nghiệm sử dụng xe',
            'Bảng giá xe',
            'Bảo dưỡng - bảo hành',
            'So sánh xe',
            'Khuyến mãi',
        ];

        foreach ($categories as $index => $name) {
            NewsCategory::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => 'Chuyên mục ' . mb_strtolower($name) . ' dành cho khách hàng LUXAUTO.',
                    'is_active' => true,
                    'sort_order' => ($index + 1) * 10,
                    'seo_title' => $name . ' | LUXAUTO',
                    'seo_description' => 'Tin bài ' . mb_strtolower($name) . ' được biên tập bởi đội ngũ LUXAUTO.',
                ]
            );
        }
    }
}
