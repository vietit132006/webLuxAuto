<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id('quote_id');
            $table->string('quote_code', 20)->unique();
            $table->foreignId('customer_id')->nullable()->constrained('customers', 'customer_id')->nullOnDelete();
            $table->foreignId('car_id')->nullable()->constrained('cars', 'car_id')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->decimal('vehicle_price', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('registration_fee', 15, 2)->default(0);
            $table->decimal('plate_fee', 15, 2)->default(0);
            $table->decimal('insurance_fee', 15, 2)->default(0);
            $table->decimal('other_fee', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2)->default(0);
            $table->string('status', 30)->default('draft')->index();
            $table->text('note')->nullable();
            $table->date('expired_at')->nullable()->index();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['car_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
