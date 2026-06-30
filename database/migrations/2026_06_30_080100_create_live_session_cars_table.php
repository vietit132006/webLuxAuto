<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('live_session_cars')) {
            return;
        }

        Schema::create('live_session_cars', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('live_session_id')->constrained('live_sessions')->cascadeOnDelete();
            $table->foreignId('car_id')->constrained('cars', 'car_id')->cascadeOnDelete();
            $table->foreignId('promotion_id')->nullable()->constrained('promotions')->nullOnDelete();
            $table->unsignedInteger('display_order')->default(0);
            $table->decimal('live_price', 15, 2)->nullable();
            $table->text('live_note')->nullable();
            $table->boolean('is_focus')->default(false);
            $table->boolean('is_active')->default(true);
            $table->dateTime('pinned_at')->nullable();
            $table->timestamps();

            $table->unique(['live_session_id', 'car_id'], 'live_session_cars_session_car_unique');
            $table->index(['live_session_id', 'display_order'], 'live_session_cars_session_order_index');
            $table->index(['car_id', 'is_active'], 'live_session_cars_car_active_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('live_session_cars');
    }
};
