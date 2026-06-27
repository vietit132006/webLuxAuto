<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('performed_by_user_id')->nullable();
            $table->unsignedBigInteger('target_user_id');
            $table->string('old_role')->nullable();
            $table->string('new_role')->nullable();
            $table->timestamps();

            $table->foreign('performed_by_user_id')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('target_user_id')
                ->references('user_id')
                ->on('users')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_audit_logs');
    }
};
