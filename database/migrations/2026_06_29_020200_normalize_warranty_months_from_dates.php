<?php

use App\Models\Warranty;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('warranties')) {
            return;
        }

        DB::table('warranties')
            ->whereNotNull('start_date')
            ->whereNotNull('end_date')
            ->orderBy('id')
            ->chunkById(100, function ($warranties): void {
                foreach ($warranties as $warranty) {
                    $months = Warranty::monthsBetweenDates($warranty->start_date, $warranty->end_date);

                    if ((int) $warranty->warranty_months === $months) {
                        continue;
                    }

                    DB::table('warranties')
                        ->where('id', $warranty->id)
                        ->update([
                            'warranty_months' => $months,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        //
    }
};
