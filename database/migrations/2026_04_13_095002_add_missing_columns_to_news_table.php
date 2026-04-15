<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news', function (Blueprint $table) {
            // Thêm các cột còn thiếu (nếu chưa có)
            if (!Schema::hasColumn('news', 'slug')) {
                $table->string('slug')->nullable()->after('title');
            }
            if (!Schema::hasColumn('news', 'summary')) {
                $table->text('summary')->nullable()->after('slug');
            }
            if (!Schema::hasColumn('news', 'image')) {
                $table->string('image')->nullable()->after('content');
            }
            if (!Schema::hasColumn('news', 'status')) {
                $table->tinyInteger('status')->default(1)->after('image');
            }
        });
    }

    public function down(): void
    {
        Schema::table('news', function (Blueprint $table) {
            $table->dropColumn(['slug', 'summary', 'image', 'status']);
        });
    }
};
