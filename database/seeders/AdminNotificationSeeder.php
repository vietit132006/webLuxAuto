<?php

namespace Database\Seeders;

use App\Models\AdminNotification;
use App\Models\User;
use App\Services\AdminNotificationService;
use Illuminate\Database\Seeder;

class AdminNotificationSeeder extends Seeder
{
    public function run(): void
    {
        $actor = User::query()
            ->whereIn('role', ['admin', 'staff'])
            ->orderBy('user_id')
            ->first();

        $notifications = app(AdminNotificationService::class);

        $rows = [
            [
                'module' => 'quotes',
                'type' => 'demo_quote_created',
                'title' => 'Bao gia moi can xu ly',
                'message' => 'Khach hang vua duoc tao bao gia demo, can sale kiem tra va gui link.',
                'action_url' => route('admin.quotes.index', [], false),
                'priority' => AdminNotification::PRIORITY_NORMAL,
            ],
            [
                'module' => 'orders',
                'type' => 'demo_order_deposited',
                'title' => 'Don hang da dat coc',
                'message' => 'Mot don hang demo da co thong tin dat coc va can theo doi giu xe.',
                'action_url' => route('admin.orders.index', [], false),
                'priority' => AdminNotification::PRIORITY_HIGH,
            ],
            [
                'module' => 'reviews',
                'type' => 'demo_review_pending',
                'title' => 'Danh gia dang cho duyet',
                'message' => 'Co danh gia moi can bo phan marketing/CSKH kiem duyet.',
                'action_url' => route('admin.reviews.index', [], false),
                'priority' => AdminNotification::PRIORITY_HIGH,
            ],
            [
                'module' => 'live',
                'type' => 'demo_live_lead_created',
                'title' => 'Live lead moi tu livestream',
                'message' => 'Khach de lai nhu cau tu van xe trong livestream demo.',
                'action_url' => route('admin.live.index', [], false),
                'priority' => AdminNotification::PRIORITY_URGENT,
            ],
            [
                'module' => 'services',
                'type' => 'demo_service_today',
                'title' => 'Lich bao duong hom nay',
                'message' => 'Co lich bao duong demo can xac nhan voi khach trong ngay.',
                'action_url' => route('admin.service-appointments.index', [], false),
                'priority' => AdminNotification::PRIORITY_HIGH,
            ],
            [
                'module' => 'inventory',
                'type' => 'demo_inventory_out',
                'title' => 'Xe het hang',
                'message' => 'Ton kho demo can kiem tra vi xe da het hang hoac khong con kha dung.',
                'action_url' => route('admin.reports.inventory', [], false),
                'priority' => AdminNotification::PRIORITY_URGENT,
            ],
        ];

        foreach ($rows as $row) {
            $notifications->createOnce(
                $row['module'],
                $row['type'],
                $row['title'],
                $row['message'],
                $row['action_url'],
                ['demo' => true],
                $row['priority'],
                $actor
            );
        }
    }
}
