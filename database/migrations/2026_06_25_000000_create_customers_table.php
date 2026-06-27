<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id');
            $table->string('customer_code', 50)->unique();
            $table->string('full_name');
            $table->string('phone', 30);
            $table->string('email')->nullable()->unique();
            $table->string('gender', 20)->nullable();
            $table->date('birthday')->nullable();
            $table->text('address')->nullable();
            $table->string('province', 120)->nullable();
            $table->string('occupation', 120)->nullable();
            $table->string('source', 40)->nullable();
            $table->string('interested_car')->nullable();
            $table->string('status', 30)->default('new')->index();
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();

            $table->index('phone');
            $table->index('full_name');
            $table->index(['source', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
