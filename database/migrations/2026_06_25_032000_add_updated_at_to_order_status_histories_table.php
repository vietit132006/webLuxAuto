<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('order_status_histories') || Schema::hasColumn('order_status_histories', 'updated_at')) {
            return;
        }

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->timestamp('updated_at')->nullable()->after('created_at');
        });

        DB::table('order_status_histories')
            ->whereNull('updated_at')
            ->update(['updated_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        if (!Schema::hasTable('order_status_histories') || !Schema::hasColumn('order_status_histories', 'updated_at')) {
            return;
        }

        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->dropColumn('updated_at');
        });
    }
};
