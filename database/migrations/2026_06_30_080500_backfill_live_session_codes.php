<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('live_sessions') || !Schema::hasColumn('live_sessions', 'live_code')) {
            return;
        }

        $lastCode = DB::table('live_sessions')
            ->whereNotNull('live_code')
            ->where('live_code', 'like', 'LIVE%')
            ->orderByDesc('id')
            ->value('live_code');

        $nextNumber = 1;

        if ($lastCode && preg_match('/^LIVE(\d+)$/', $lastCode, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
        }

        DB::table('live_sessions')
            ->whereNull('live_code')
            ->orderBy('id')
            ->chunkById(100, function ($sessions) use (&$nextNumber): void {
                foreach ($sessions as $session) {
                    DB::table('live_sessions')
                        ->where('id', $session->id)
                        ->update([
                            'live_code' => 'LIVE' . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT),
                        ]);

                    $nextNumber++;
                }
            });
    }

    public function down(): void
    {
        // Keep generated live codes. They are now part of the livestream identity.
    }
};
