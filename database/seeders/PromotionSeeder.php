<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Car;
use App\Models\CarModel;
use App\Models\Promotion;
use App\Models\PromotionTarget;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PromotionSeeder extends Seeder
{
    public function run(): void
    {
        $creatorId = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->orderBy('user_id')
            ->value('user_id');

        $brand = Brand::query()->orderBy('brand_id')->first();
        $model = CarModel::query()->orderBy('id')->first();
        $car = Car::query()->orderBy('car_id')->first();

        $rows = [
            [
                'promotion_code' => 'KM000001',
                'title' => 'Giảm 50 triệu cho xe điện',
                'slug' => 'giam-50-trieu-cho-xe-dien',
                'short_description' => 'Ưu đãi tiền mặt cho khách đặt cọc các dòng xe điện trong tháng.',
                'content' => 'Khách hàng đặt cọc xe điện tại Lux Auto được giảm trực tiếp 50 triệu đồng vào báo giá.',
                'banner_image' => 'https://images.unsplash.com/photo-1617704548623-340376564e68?auto=format&fit=crop&w=1400&q=85',
                'promotion_type' => Promotion::TYPE_CASH_DISCOUNT,
                'discount_type' => Promotion::DISCOUNT_FIXED,
                'discount_value' => 50000000,
                'gift_description' => null,
                'terms' => 'Áp dụng cho xe còn tồn khả dụng. Không quy đổi thành tiền mặt ngoài báo giá.',
                'start_at' => Carbon::now()->subDays(5),
                'end_at' => Carbon::now()->addDays(40),
                'is_featured' => true,
                'auto_apply' => true,
                'target' => ['type' => PromotionTarget::TYPE_ALL, 'id' => null],
            ],
            [
                'promotion_code' => 'KM000002',
                'title' => 'Tặng bảo hiểm thân vỏ năm đầu',
                'slug' => 'tang-bao-hiem-than-vo-nam-dau',
                'short_description' => 'Tặng gói bảo hiểm thân vỏ năm đầu cho xe sang đã qua kiểm định.',
                'content' => 'Lux Auto hỗ trợ bảo hiểm thân vỏ năm đầu cho khách hoàn tất đặt cọc trong thời gian chương trình.',
                'banner_image' => 'https://images.unsplash.com/photo-1503736334956-4c8f8e92946d?auto=format&fit=crop&w=1400&q=85',
                'promotion_type' => Promotion::TYPE_INSURANCE_SUPPORT,
                'discount_type' => Promotion::DISCOUNT_NONE,
                'discount_value' => null,
                'gift_description' => 'Gói bảo hiểm thân vỏ năm đầu theo chính sách đối tác bảo hiểm.',
                'terms' => 'Giá trị bảo hiểm phụ thuộc hồ sơ xe và xác nhận từ đối tác bảo hiểm.',
                'start_at' => Carbon::now()->subDays(2),
                'end_at' => Carbon::now()->addDays(30),
                'is_featured' => true,
                'auto_apply' => false,
                'target' => ['type' => PromotionTarget::TYPE_ALL, 'id' => null],
            ],
            [
                'promotion_code' => 'KM000003',
                'title' => 'Hỗ trợ 50% lệ phí trước bạ',
                'slug' => 'ho-tro-50-phan-tram-le-phi-truoc-ba',
                'short_description' => 'Hỗ trợ một phần chi phí lăn bánh cho khách mua xe trong kỳ ưu đãi.',
                'content' => 'Chương trình hỗ trợ 50% lệ phí trước bạ, tối đa 80 triệu đồng, khi khách hoàn tất báo giá và đặt cọc.',
                'banner_image' => 'https://images.unsplash.com/photo-1493238792000-8113da705763?auto=format&fit=crop&w=1400&q=85',
                'promotion_type' => Promotion::TYPE_REGISTRATION_FEE_SUPPORT,
                'discount_type' => Promotion::DISCOUNT_PERCENT,
                'discount_value' => 3,
                'max_discount_value' => 80000000,
                'gift_description' => null,
                'terms' => 'Khoản hỗ trợ được thể hiện trên báo giá và tùy thuộc hồ sơ đăng ký thực tế.',
                'start_at' => Carbon::now()->subDay(),
                'end_at' => Carbon::now()->addDays(21),
                'is_featured' => true,
                'auto_apply' => true,
                'target' => $brand
                    ? ['type' => PromotionTarget::TYPE_BRAND, 'id' => $brand->brand_id]
                    : ['type' => PromotionTarget::TYPE_ALL, 'id' => null],
            ],
            [
                'promotion_code' => 'KM000004',
                'title' => 'Tặng gói bảo dưỡng 2 năm',
                'slug' => 'tang-goi-bao-duong-2-nam',
                'short_description' => 'Gói bảo dưỡng định kỳ 2 năm tại hệ thống đối tác dịch vụ.',
                'content' => 'Khách nhận xe trong thời gian khuyến mãi được tặng gói bảo dưỡng định kỳ 2 năm.',
                'banner_image' => 'https://images.unsplash.com/photo-1487754180451-c456f719a1fc?auto=format&fit=crop&w=1400&q=85',
                'promotion_type' => Promotion::TYPE_MAINTENANCE_PACKAGE,
                'discount_type' => Promotion::DISCOUNT_NONE,
                'discount_value' => null,
                'gift_description' => 'Gói bảo dưỡng 2 năm hoặc 20.000 km, tùy điều kiện nào đến trước.',
                'terms' => 'Không áp dụng đồng thời với chương trình hậu mãi riêng của từng xe nếu có.',
                'start_at' => Carbon::now(),
                'end_at' => Carbon::now()->addDays(60),
                'is_featured' => false,
                'auto_apply' => false,
                'target' => $model
                    ? ['type' => PromotionTarget::TYPE_MODEL, 'id' => $model->id]
                    : ['type' => PromotionTarget::TYPE_ALL, 'id' => null],
            ],
            [
                'promotion_code' => 'KM000005',
                'title' => 'Ưu đãi trả góp lãi suất thấp',
                'slug' => 'uu-dai-tra-gop-lai-suat-thap',
                'short_description' => 'Hỗ trợ hồ sơ vay mua xe với gói lãi suất ưu đãi từ đối tác ngân hàng.',
                'content' => 'Lux Auto hỗ trợ kết nối ngân hàng và tư vấn phương án tài chính phù hợp cho khách mua xe.',
                'banner_image' => 'https://images.unsplash.com/photo-1533473359331-0135ef1b58bf?auto=format&fit=crop&w=1400&q=85',
                'promotion_type' => Promotion::TYPE_INSTALLMENT_SUPPORT,
                'discount_type' => Promotion::DISCOUNT_NONE,
                'discount_value' => null,
                'gift_description' => 'Tư vấn hồ sơ vay và gói lãi suất ưu đãi theo từng ngân hàng.',
                'terms' => 'Phụ thuộc điều kiện phê duyệt của ngân hàng.',
                'start_at' => Carbon::now()->subDays(3),
                'end_at' => Carbon::now()->addDays(45),
                'is_featured' => false,
                'auto_apply' => false,
                'target' => ['type' => PromotionTarget::TYPE_ALL, 'id' => null],
            ],
            [
                'promotion_code' => 'KM000006',
                'title' => 'Tặng phụ kiện cao cấp',
                'slug' => 'tang-phu-kien-cao-cap',
                'short_description' => 'Tặng bộ phụ kiện nội thất và camera hành trình cho xe được chọn.',
                'content' => 'Bộ phụ kiện cao cấp bao gồm thảm sàn, phim cách nhiệt và camera hành trình theo cấu hình xe.',
                'banner_image' => 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?auto=format&fit=crop&w=1400&q=85',
                'promotion_type' => Promotion::TYPE_GIFT,
                'discount_type' => Promotion::DISCOUNT_NONE,
                'discount_value' => null,
                'gift_description' => 'Bộ phụ kiện cao cấp theo xe.',
                'terms' => 'Số lượng quà tặng có hạn và cần xác nhận với tư vấn viên.',
                'start_at' => Carbon::now()->subDays(7),
                'end_at' => Carbon::now()->addDays(18),
                'is_featured' => false,
                'auto_apply' => false,
                'target' => $car
                    ? ['type' => PromotionTarget::TYPE_CAR, 'id' => $car->car_id]
                    : ['type' => PromotionTarget::TYPE_ALL, 'id' => null],
            ],
        ];

        foreach ($rows as $row) {
            $target = $row['target'];
            unset($row['target']);

            $promotion = Promotion::updateOrCreate(
                ['promotion_code' => $row['promotion_code']],
                array_merge($row, [
                    'status' => Promotion::STATUS_ACTIVE,
                    'is_public' => true,
                    'priority' => $row['is_featured'] ? 10 : 0,
                    'created_by' => $creatorId,
                    'banner_alt' => $row['title'],
                ])
            );

            $promotion->targets()->delete();
            $promotion->targets()->create([
                'target_type' => $target['type'],
                'target_id' => $target['id'],
            ]);
        }
    }
}
