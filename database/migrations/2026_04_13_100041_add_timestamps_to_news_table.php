<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Kiểm tra xem nếu chưa có cột created_at thì mới thêm vào
            if (!Schema::hasColumn('news', 'created_at')) {
                $table->timestamp('created_at')->nullable();
            }
            // Kiểm tra xem nếu chưa có cột updated_at thì mới thêm vào
            if (!Schema::hasColumn('news', 'updated_at')) {
                $table->timestamp('updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
