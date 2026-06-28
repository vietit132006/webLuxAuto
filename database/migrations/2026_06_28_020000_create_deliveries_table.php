<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders', 'order_id')
                ->cascadeOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->foreignId('car_id')
                ->nullable()
                ->constrained('cars', 'car_id')
                ->nullOnDelete();
            $table->dateTime('expected_delivery_date')->nullable();
            $table->dateTime('actual_delivery_date')->nullable();
            $table->string('delivery_location')->nullable();
            $table->foreignId('delivery_staff_id')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->text('note')->nullable();
            $table->json('checklist_data')->nullable();
            $table->timestamp('stock_deducted_at')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['car_id', 'status']);
            $table->index(['delivery_staff_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
