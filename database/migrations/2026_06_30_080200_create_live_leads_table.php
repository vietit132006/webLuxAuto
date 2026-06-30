<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_leads')) {
            return;
        }

        Schema::create('live_leads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('car_id')->nullable()->constrained('cars', 'car_id')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('lead_type', 40);
            $table->text('message')->nullable();
            $table->string('status', 30)->default('new');
            $table->foreignId('assigned_to')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();

            $table->index(['live_session_id', 'status'], 'live_leads_session_status_index');
            $table->index(['car_id', 'lead_type'], 'live_leads_car_type_index');
            $table->index(['assigned_to', 'status'], 'live_leads_assignee_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_leads');
    }
};
