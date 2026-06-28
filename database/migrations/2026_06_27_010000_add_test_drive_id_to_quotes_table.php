<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('quotes', 'test_drive_id')) {
            return;
        }

        Schema::table('quotes', function (Blueprint $table) {
            $table->foreignId('test_drive_id')
                ->nullable()
                ->after('user_id')
                ->constrained('support_tickets', 'ticket_id')
                ->nullOnDelete();

            $table->index(['test_drive_id', 'status']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasColumn('quotes', 'test_drive_id')) {
            return;
        }

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropIndex(['test_drive_id', 'status']);
            $table->dropConstrainedForeignId('test_drive_id');
        });
    }
};
