<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delivery_id')
                ->constrained('deliveries')
                ->cascadeOnDelete();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('uploaded_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->timestamps();

            $table->index(['delivery_id', 'created_at']);
            $table->index('uploaded_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_files');
    }
};
