<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\NewsTag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewsSeeder extends Seeder
{
    public function run(): void
    {
        $authorId = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->orderBy('user_id')
            ->value('user_id') ?? User::query()->orderBy('user_id')->value('user_id');

        $brand = Brand::query()->orderBy('brand_id')->first();
        $model = CarModel::query()->orderBy('id')->first();
        $car = Car::query()->orderBy('car_id')->first();

        $articles = [
            [
                'category' => 'Tin showroom',
                'title' => 'LUXAUTO mở lịch tư vấn xe sang cuối tuần',
                'summary' => 'Khách hàng có thể đặt lịch xem xe, lái thử và nhận tư vấn tài chính tại showroom trong khung giờ cuối tuần.',
                'tags' => ['showroom', 'tư vấn', 'lái thử'],
                'thumbnail' => 'https://images.unsplash.com/photo-1562141961-b5d5827a0b8a?w=1200&q=80',
                'is_featured' => true,
                'cta_type' => News::CTA_CONTACT,
            ],
            [
                'category' => 'Tư vấn mua xe',
                'title' => '5 tiêu chí chọn sedan hạng sang đã qua sử dụng',
                'summary' => 'Lịch sử bảo dưỡng, độ nguyên bản, chi phí sở hữu và khả năng thanh khoản là các yếu tố nên kiểm tra trước khi xuống tiền.',
                'tags' => ['sedan', 'xe lướt', 'tư vấn mua xe'],
                'thumbnail' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?w=1200&q=80',
                'is_featured' => true,
                'cta_type' => News::CTA_QUOTE,
            ],
            [
                'category' => 'Đánh giá xe',
                'title' => 'Đánh giá nhanh SUV hạng sang cho gia đình đô thị',
                'summary' => 'SUV hạng sang phù hợp gia đình cần cân bằng giữa độ êm, hàng ghế sau, công nghệ an toàn và chi phí bảo dưỡng.',
                'tags' => ['SUV', 'đánh giá xe', 'gia đình'],
                'thumbnail' => 'https://images.unsplash.com/photo-1603386329225-868f9b1ee6c9?w=1200&q=80',
                'is_featured' => true,
                'cta_type' => News::CTA_TEST_DRIVE,
            ],
            [
                'category' => 'Kinh nghiệm sử dụng xe',
                'title' => 'Cách chăm sóc nội thất da để xe luôn giữ giá',
                'summary' => 'Vệ sinh định kỳ, dưỡng da đúng loại và tránh nắng gắt giúp khoang cabin giữ độ mới lâu hơn.',
                'tags' => ['nội thất da', 'chăm sóc xe', 'giữ giá'],
                'thumbnail' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?w=1200&q=80',
                'is_featured' => false,
                'cta_type' => News::CTA_CONTACT,
            ],
            [
                'category' => 'Bảng giá xe',
                'title' => 'Các khoản phí cần tính khi lăn bánh xe sang',
                'summary' => 'Ngoài giá bán, khách hàng cần dự trù lệ phí trước bạ, đăng ký biển số, bảo hiểm và chi phí kiểm định.',
                'tags' => ['giá lăn bánh', 'chi phí', 'bảng giá'],
                'thumbnail' => 'https://images.unsplash.com/photo-1511919884226-fd3cad34687c?w=1200&q=80',
                'is_featured' => false,
                'cta_type' => News::CTA_QUOTE,
            ],
            [
                'category' => 'Bảo dưỡng - bảo hành',
                'title' => 'Mốc bảo dưỡng quan trọng sau khi nhận xe',
                'summary' => 'Kiểm tra dầu, phanh, lốp, hệ thống điện và lịch bảo dưỡng chính hãng giúp xe vận hành ổn định.',
                'tags' => ['bảo dưỡng', 'bảo hành', 'hậu mãi'],
                'thumbnail' => 'https://images.unsplash.com/photo-1486262715619-67b85e0b08d3?w=1200&q=80',
                'is_featured' => false,
                'cta_type' => News::CTA_CONTACT,
            ],
            [
                'category' => 'So sánh xe',
                'title' => 'So sánh sedan Đức và SUV sang cho nhu cầu công việc',
                'summary' => 'Sedan cho cảm giác lái và hình ảnh doanh nhân, trong khi SUV linh hoạt hơn cho gia đình và các chuyến xa.',
                'tags' => ['so sánh xe', 'sedan', 'SUV'],
                'thumbnail' => 'https://images.unsplash.com/photo-1492144534655-ae79c964c9d7?w=1200&q=80',
                'is_featured' => true,
                'cta_type' => News::CTA_CAR_DETAIL,
            ],
            [
                'category' => 'Khuyến mãi',
                'title' => 'Ưu đãi kiểm tra xe miễn phí trước khi đặt cọc',
                'summary' => 'LUXAUTO hỗ trợ kiểm tra hồ sơ xe, tình trạng kỹ thuật và tư vấn phương án tài chính trước khi khách hàng đặt cọc.',
                'tags' => ['ưu đãi', 'kiểm tra xe', 'đặt cọc'],
                'thumbnail' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?w=1200&q=80',
                'is_featured' => false,
                'cta_type' => News::CTA_CONTACT,
            ],
            [
                'category' => 'Tư vấn mua xe',
                'title' => 'Khi nào nên chọn xe mới, khi nào nên chọn xe lướt',
                'summary' => 'Xe mới phù hợp khách hàng ưu tiên bảo hành dài, còn xe lướt hấp dẫn khi hồ sơ minh bạch và giá tốt.',
                'tags' => ['xe mới', 'xe lướt', 'tư vấn'],
                'thumbnail' => 'https://images.unsplash.com/photo-1549924231-f129b911e442?w=1200&q=80',
                'is_featured' => false,
                'cta_type' => News::CTA_QUOTE,
            ],
            [
                'category' => 'Đánh giá xe',
                'title' => 'Trang bị an toàn nên có trên xe sang hiện đại',
                'summary' => 'Camera 360, cảnh báo điểm mù, ga tự động thích ứng và hỗ trợ giữ làn giúp việc lái xe an tâm hơn.',
                'tags' => ['an toàn', 'công nghệ', 'đánh giá xe'],
                'thumbnail' => 'https://images.unsplash.com/photo-1542362567-b07e54358753?w=1200&q=80',
                'is_featured' => false,
                'cta_type' => News::CTA_TEST_DRIVE,
            ],
        ];

        foreach ($articles as $index => $article) {
            $category = NewsCategory::where('name', $article['category'])->first();
            $slug = Str::slug($article['title']);
            $content = $this->contentFor($article['title']);

            $news = News::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $category?->id,
                    'author_id' => $authorId,
                    'title' => $article['title'],
                    'summary' => $article['summary'],
                    'content' => $content,
                    'thumbnail' => $article['thumbnail'],
                    'thumbnail_alt' => $article['title'],
                    'status' => News::STATUS_PUBLISHED,
                    'is_featured' => $article['is_featured'],
                    'published_at' => now()->subDays(12 - $index),
                    'views_count' => 120 + ($index * 37),
                    'reading_time' => $this->readingTime($content),
                    'seo_title' => $article['title'] . ' | LUXAUTO',
                    'seo_description' => $article['summary'],
                    'seo_keywords' => implode(', ', $article['tags']),
                    'related_brand_id' => $brand?->brand_id,
                    'related_model_id' => $model?->id,
                    'related_car_id' => $car?->car_id,
                    'cta_type' => $article['cta_type'],
                    'cta_label' => News::ctaTypes()[$article['cta_type']] ?? 'Liên hệ tư vấn',
                ]
            );

            $tagIds = collect($article['tags'])
                ->map(function (string $name): int {
                    return NewsTag::firstOrCreate(
                        ['slug' => Str::slug($name)],
                        ['name' => $name]
                    )->id;
                })
                ->all();

            $news->tags()->sync($tagIds);
        }
    }

    private function contentFor(string $title): string
    {
        return <<<HTML
<p><strong>{$title}</strong> là một trong những chủ đề được khách hàng LUXAUTO quan tâm khi tìm hiểu xe sang đã qua kiểm định.</p>
<h2>Điểm cần kiểm tra trước khi quyết định</h2>
<p>Khách hàng nên bắt đầu từ hồ sơ pháp lý, lịch sử bảo dưỡng, tình trạng vận hành và chi phí sở hữu thực tế. Một chiếc xe phù hợp không chỉ đẹp ở ngoại thất mà còn phải minh bạch về nguồn gốc, vận hành ổn định và có phương án hậu mãi rõ ràng.</p>
<blockquote>Đội ngũ tư vấn LUXAUTO luôn ưu tiên minh bạch thông tin xe để khách hàng dễ so sánh và ra quyết định.</blockquote>
<h3>Gợi ý từ showroom</h3>
<ul>
    <li>Đặt lịch xem xe trực tiếp để kiểm tra nội thất, thân vỏ và cảm giác lái.</li>
    <li>Yêu cầu tư vấn chi phí lăn bánh, bảo hiểm và phương án tài chính.</li>
    <li>So sánh ít nhất hai lựa chọn cùng phân khúc trước khi đặt cọc.</li>
</ul>
<p>Nếu cần thêm thông tin, khách hàng có thể liên hệ LUXAUTO để được chuẩn bị sẵn hồ sơ xe và lịch lái thử phù hợp.</p>
HTML;
    }

    private function readingTime(string $content): int
    {
        preg_match_all('/[\p{L}\p{N}]+/u', strip_tags($content), $matches);

        return max(1, (int) ceil(count($matches[0] ?? []) / 220));
    }
}
