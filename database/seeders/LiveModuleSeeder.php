<?php

namespace Database\Seeders;

use App\Models\Car;
use App\Models\LiveLead;
use App\Models\LiveSession;
use App\Models\Promotion;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LiveModuleSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            DB::table('live_leads')->delete();
            DB::table('live_session_cars')->delete();
            DB::table('live_sessions')->delete();

            $host = User::query()
                ->whereIn('role', ['admin', 'staff'])
                ->orderBy('user_id')
                ->first();

            $cars = Car::query()
                ->orderByDesc('created_at')
                ->take(5)
                ->get();

            if ($cars->isEmpty()) {
                return;
            }

            $promotion = Promotion::query()->effective()->orderedForDisplay()->first();

            $active = LiveSession::create([
                'title' => 'Lux Auto Live - Xe sang san showroom',
                'description' => 'Phien live demo gioi thieu xe dang co san va uu dai rieng trong live.',
                'platform' => 'youtube',
                'video_id' => 'dQw4w9WgXcQ',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'status' => LiveSession::STATUS_LIVE,
                'starts_at' => now()->subMinutes(20),
                'host_user_id' => $host?->getKey(),
                'is_active' => true,
                'is_public' => true,
                'replay_enabled' => true,
                'views_count' => 128,
                'peak_viewers' => 34,
                'cta_label' => 'Nhan bao gia live',
                'created_by' => $host?->getKey(),
            ]);

            foreach ($cars->take(3)->values() as $index => $car) {
                $active->sessionCars()->create([
                    'car_id' => $car->car_id,
                    'promotion_id' => $promotion?->id,
                    'display_order' => $index + 1,
                    'live_price' => $car->sale_price ?: $car->price,
                    'live_note' => $index === 0 ? 'Xe dang focus trong phien live.' : null,
                    'is_focus' => $index === 0,
                    'is_active' => true,
                    'pinned_at' => now()->subMinutes(15 - $index),
                ]);
            }

            foreach ($cars->take(2)->values() as $index => $car) {
                LiveLead::create([
                    'live_session_id' => $active->id,
                    'car_id' => $car->car_id,
                    'customer_name' => 'Khach live demo ' . ($index + 1),
                    'phone' => '09000000' . ($index + 1),
                    'email' => 'live-demo-' . ($index + 1) . '@luxauto.local',
                    'lead_type' => $index === 0 ? LiveLead::TYPE_QUOTE_REQUEST : LiveLead::TYPE_TEST_DRIVE_REQUEST,
                    'message' => 'Lead mau tu livestream.',
                    'status' => LiveLead::STATUS_NEW,
                    'assigned_to' => $host?->getKey(),
                ]);
            }

            $scheduled = LiveSession::create([
                'title' => 'Lux Auto Live - SUV va sedan moi',
                'description' => 'Phien live sap toi gioi thieu cac mau xe moi ve showroom.',
                'platform' => 'youtube',
                'video_id' => 'dQw4w9WgXcQ',
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                'status' => LiveSession::STATUS_SCHEDULED,
                'starts_at' => now()->addDay()->setTime(20, 0),
                'host_user_id' => $host?->getKey(),
                'is_active' => false,
                'is_public' => true,
                'replay_enabled' => false,
                'created_by' => $host?->getKey(),
            ]);

            foreach ($cars->skip(2)->take(3)->values() as $index => $car) {
                $scheduled->sessionCars()->create([
                    'car_id' => $car->car_id,
                    'promotion_id' => $promotion?->id,
                    'display_order' => $index + 1,
                    'live_price' => null,
                    'live_note' => 'Du kien len song trong phien sap toi.',
                    'is_focus' => $index === 0,
                    'is_active' => true,
                    'pinned_at' => now(),
                ]);
            }
        });
    }
}
