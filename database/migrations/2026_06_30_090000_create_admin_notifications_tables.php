<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('admin_notifications')) {
            Schema::create('admin_notifications', function (Blueprint $table): void {
                $table->id();
                $table->string('type', 120)->index();
                $table->string('module', 60)->index();
                $table->string('title');
                $table->text('message')->nullable();
                $table->string('action_url', 1000)->nullable();
                $table->string('priority', 20)->default('normal')->index();
                $table->json('data')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('created_by')
                    ->references('user_id')
                    ->on('users')
                    ->nullOnDelete();
                $table->index(['module', 'priority', 'created_at']);
            });
        }

        if (!Schema::hasTable('admin_notification_reads')) {
            Schema::create('admin_notification_reads', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('notification_id')
                    ->constrained('admin_notifications')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('user_id');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();

                $table->foreign('user_id')
                    ->references('user_id')
                    ->on('users')
                    ->cascadeOnDelete();
                $table->unique(['notification_id', 'user_id'], 'admin_notification_reads_unique');
                $table->index(['user_id', 'read_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_notification_reads');
        Schema::dropIfExists('admin_notifications');
    }
};
