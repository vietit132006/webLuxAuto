<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('promotion_code', 20)->unique();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('short_description')->nullable();
            $table->longText('content')->nullable();
            $table->string('banner_image')->nullable();
            $table->string('banner_alt')->nullable();
            $table->string('promotion_type', 50)->index();
            $table->string('discount_type', 20)->nullable()->index();
            $table->decimal('discount_value', 15, 2)->nullable();
            $table->decimal('max_discount_value', 15, 2)->nullable();
            $table->text('gift_description')->nullable();
            $table->text('terms')->nullable();
            $table->timestamp('start_at')->nullable()->index();
            $table->timestamp('end_at')->nullable()->index();
            $table->string('status', 30)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->boolean('is_public')->default(true)->index();
            $table->boolean('auto_apply')->default(false)->index();
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->integer('priority')->default(0)->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users', 'user_id')
                ->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'is_public', 'start_at', 'end_at']);
            $table->index(['promotion_type', 'status']);
        });

        Schema::create('promotion_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promotion_id')
                ->constrained('promotions')
                ->cascadeOnDelete();
            $table->string('target_type', 20)->index();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->timestamps();

            $table->unique(['promotion_id', 'target_type', 'target_id'], 'promotion_targets_unique');
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('quote_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')
                ->constrained('quotes', 'quote_id')
                ->cascadeOnDelete();
            $table->foreignId('promotion_id')
                ->constrained('promotions')
                ->cascadeOnDelete();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->text('gift_note')->nullable();
            $table->timestamps();

            $table->unique(['quote_id', 'promotion_id']);
            $table->index(['promotion_id', 'created_at']);
        });

        Schema::create('order_promotions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')
                ->constrained('orders', 'order_id')
                ->cascadeOnDelete();
            $table->foreignId('promotion_id')
                ->constrained('promotions')
                ->cascadeOnDelete();
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->text('gift_note')->nullable();
            $table->timestamps();

            $table->unique(['order_id', 'promotion_id']);
            $table->index(['promotion_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_promotions');
        Schema::dropIfExists('quote_promotions');
        Schema::dropIfExists('promotion_targets');
        Schema::dropIfExists('promotions');
    }
};
