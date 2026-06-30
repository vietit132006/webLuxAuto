<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('live_sessions')
            || !Schema::hasTable('live_session_cars')
            || !Schema::hasColumn('live_sessions', 'featured_car_ids')
        ) {
            return;
        }

        DB::table('live_sessions')
            ->whereNotNull('featured_car_ids')
            ->orderBy('id')
            ->chunkById(100, function ($sessions): void {
                foreach ($sessions as $session) {
                    $carIds = json_decode((string) $session->featured_car_ids, true);

                    if (!is_array($carIds) || $carIds === []) {
                        continue;
                    }

                    foreach (array_values(array_unique(array_filter($carIds))) as $index => $carId) {
                        if (!DB::table('cars')->where('car_id', $carId)->exists()) {
                            continue;
                        }

                        DB::table('live_session_cars')->updateOrInsert(
                            [
                                'live_session_id' => $session->id,
                                'car_id' => (int) $carId,
                            ],
                            [
                                'display_order' => $index + 1,
                                'is_focus' => $index === 0,
                                'is_active' => true,
                                'pinned_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                    }
                }
            });
    }

    public function down(): void
    {
        // Keep migrated car pins. The JSON column is retained for compatibility only.
    }
};
