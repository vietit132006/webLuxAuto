<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('orders', 'quote_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('quote_id')
                ->nullable()
                ->after('order_code')
                ->constrained('quotes', 'quote_id')
                ->nullOnDelete();

            $table->unique('quote_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('orders', 'quote_id')) {
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique(['quote_id']);
            $table->dropConstrainedForeignId('quote_id');
        });
    }
};
