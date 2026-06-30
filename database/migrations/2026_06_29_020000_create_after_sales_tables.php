<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warranties', function (Blueprint $table) {
            $table->id();
            $table->string('warranty_code', 30)->unique();
            $table->foreignId('order_id')
                ->constrained('orders', 'order_id')
                ->cascadeOnDelete();
            $table->foreignId('delivery_id')
                ->nullable()
                ->constrained('deliveries')
                ->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->foreignId('car_id')
                ->nullable()
                ->constrained('cars', 'car_id')
                ->nullOnDelete();
            $table->string('vin')->nullable();
            $table->string('license_plate')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('warranty_months')->default(36);
            $table->unsignedInteger('mileage_limit')->nullable();
            $table->string('status', 20)->default('active')->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique('order_id');
            $table->index(['car_id', 'status']);
            $table->index(['user_id', 'status']);
            $table->index(['end_date', 'status']);
        });

        Schema::create('service_appointments', function (Blueprint $table) {
            $table->id();
            $table->string('appointment_code', 30)->unique();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->foreignId('car_id')
                ->nullable()
                ->constrained('cars', 'car_id')
                ->nullOnDelete();
            $table->foreignId('warranty_id')
                ->nullable()
                ->constrained('warranties')
                ->nullOnDelete();
            $table->string('service_type', 30)->index();
            $table->date('appointment_date');
            $table->time('appointment_time')->nullable();
            $table->string('service_location')->nullable();
            $table->foreignId('assigned_staff_id')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->string('status', 30)->default('pending')->index();
            $table->text('customer_note')->nullable();
            $table->text('internal_note')->nullable();
            $table->timestamps();

            $table->index(['appointment_date', 'status']);
            $table->index(['assigned_staff_id', 'status']);
        });

        Schema::create('service_records', function (Blueprint $table) {
            $table->id();
            $table->string('record_code', 30)->unique();
            $table->foreignId('service_appointment_id')
                ->nullable()
                ->constrained('service_appointments')
                ->nullOnDelete();
            $table->foreignId('warranty_id')
                ->nullable()
                ->constrained('warranties')
                ->nullOnDelete();
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->foreignId('car_id')
                ->nullable()
                ->constrained('cars', 'car_id')
                ->nullOnDelete();
            $table->string('service_type', 30)->index();
            $table->date('service_date');
            $table->unsignedInteger('mileage')->nullable();
            $table->text('problem_description')->nullable();
            $table->text('work_performed')->nullable();
            $table->text('parts_replaced')->nullable();
            $table->decimal('labor_cost', 14, 2)->default(0);
            $table->decimal('parts_cost', 14, 2)->default(0);
            $table->decimal('total_cost', 14, 2)->default(0);
            $table->date('next_service_date')->nullable();
            $table->unsignedInteger('next_service_mileage')->nullable();
            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->string('status', 30)->default('completed')->index();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['service_date', 'status']);
            $table->index(['next_service_date', 'status']);
            $table->index(['handled_by', 'status']);
        });

        Schema::create('service_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_record_id')
                ->nullable()
                ->constrained('service_records')
                ->cascadeOnDelete();
            $table->foreignId('service_appointment_id')
                ->nullable()
                ->constrained('service_appointments')
                ->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['service_record_id', 'created_at']);
            $table->index(['service_appointment_id', 'created_at']);
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_files');
        Schema::dropIfExists('service_records');
        Schema::dropIfExists('service_appointments');
        Schema::dropIfExists('warranties');
    }
};
