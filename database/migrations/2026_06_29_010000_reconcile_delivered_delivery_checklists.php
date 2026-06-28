<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const CHECKLIST_KEYS = [
        'exterior_checked',
        'interior_checked',
        'keys_handed_over',
        'documents_handed_over',
        'usage_guided',
        'insurance_handed_over',
        'handover_minutes_signed',
    ];

    public function up(): void
    {
        if (
            !Schema::hasTable('deliveries')
            || !Schema::hasColumn('deliveries', 'checklist_data')
        ) {
            return;
        }

        DB::table('deliveries')
            ->where('status', 'delivered')
            ->orderBy('id')
            ->chunkById(100, function ($deliveries): void {
                foreach ($deliveries as $delivery) {
                    $checklist = json_decode((string) ($delivery->checklist_data ?? '[]'), true);
                    $checklist = is_array($checklist) ? $checklist : [];
                    $wasComplete = true;

                    foreach (self::CHECKLIST_KEYS as $key) {
                        if (empty($checklist[$key])) {
                            $wasComplete = false;
                            $checklist[$key] = true;
                        }
                    }

                    if ($wasComplete) {
                        continue;
                    }

                    $syncNote = '[He thong] Dong bo checklist day du vi ban ghi da duoc xac nhan giao xe truoc khi siet nghiep vu.';
                    $note = trim((string) ($delivery->note ?? ''));

                    if (!str_contains($note, $syncNote)) {
                        $note = trim($note . PHP_EOL . $syncNote);
                    }

                    DB::table('deliveries')
                        ->where('id', $delivery->id)
                        ->update([
                            'checklist_data' => json_encode($checklist, JSON_UNESCAPED_UNICODE),
                            'note' => $note,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    public function down(): void
    {
        // Legacy checklist state cannot be reconstructed safely.
    }
};
