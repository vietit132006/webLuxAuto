<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->string('ticket_type')->default('support')->after('user_id');
            $table->unsignedBigInteger('car_id')->nullable()->after('ticket_type');

            $table->index(['user_id', 'ticket_type']);
            $table->index(['car_id']);
        });
    }

    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex(['support_tickets_user_id_ticket_type_index']);
            $table->dropIndex(['support_tickets_car_id_index']);
            $table->dropColumn(['ticket_type', 'car_id']);
        });
    }
};

