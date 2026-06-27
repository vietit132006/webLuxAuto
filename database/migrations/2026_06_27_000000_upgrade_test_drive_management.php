<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->date('appointment_date')->nullable()->after('status');
            $table->time('appointment_time')->nullable()->after('appointment_date');
            $table->string('showroom')->nullable()->after('appointment_time');
            $table->string('sales_person')->nullable()->after('showroom');

            $table->index(['ticket_type', 'status'], 'support_tickets_test_drive_status_idx');
            $table->index(['ticket_type', 'appointment_date'], 'support_tickets_test_drive_appointment_idx');
            $table->index(['ticket_type', 'sales_person'], 'support_tickets_test_drive_sales_person_idx');
        });

        Schema::create('test_drive_status_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('old_status', 50)->nullable();
            $table->string('new_status', 50);
            $table->unsignedBigInteger('changed_by')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ticket_id')
                ->references('ticket_id')
                ->on('support_tickets')
                ->cascadeOnDelete();

            $table->foreign('changed_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['ticket_id', 'created_at'], 'test_drive_status_histories_ticket_created_idx');
            $table->index(['changed_by', 'created_at'], 'test_drive_status_histories_user_created_idx');
        });

        Schema::create('test_drive_notes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->text('note');
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ticket_id')
                ->references('ticket_id')
                ->on('support_tickets')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['ticket_id', 'created_at'], 'test_drive_notes_ticket_created_idx');
        });

        Schema::create('test_drive_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ticket_id')
                ->references('ticket_id')
                ->on('support_tickets')
                ->cascadeOnDelete();

            $table->foreign('uploaded_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['ticket_id', 'created_at'], 'test_drive_files_ticket_created_idx');
        });

        Schema::create('test_drive_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ticket_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('action', 80);
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->text('description')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ticket_id')
                ->references('ticket_id')
                ->on('support_tickets')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->index(['ticket_id', 'created_at'], 'test_drive_activity_logs_ticket_created_idx');
            $table->index(['user_id', 'created_at'], 'test_drive_activity_logs_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_drive_activity_logs');
        Schema::dropIfExists('test_drive_files');
        Schema::dropIfExists('test_drive_notes');
        Schema::dropIfExists('test_drive_status_histories');

        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropIndex('support_tickets_test_drive_sales_person_idx');
            $table->dropIndex('support_tickets_test_drive_appointment_idx');
            $table->dropIndex('support_tickets_test_drive_status_idx');
            $table->dropColumn([
                'appointment_date',
                'appointment_time',
                'showroom',
                'sales_person',
            ]);
        });
    }
};
