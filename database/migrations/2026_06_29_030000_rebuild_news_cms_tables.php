<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('news_tag');
        Schema::dropIfExists('news_tags');
        Schema::dropIfExists('news');
        Schema::dropIfExists('news_categories');
        Schema::enableForeignKeyConstraints();

        Schema::create('news_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('news', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained('news_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('thumbnail')->nullable();
            $table->string('thumbnail_alt')->nullable();
            $table->string('status', 32)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedSmallInteger('reading_time')->nullable();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->string('seo_keywords')->nullable();
            $table->string('canonical_url')->nullable();
            $table->foreignId('related_brand_id')->nullable()->constrained('brands', 'brand_id')->nullOnDelete();
            $table->foreignId('related_model_id')->nullable()->constrained('car_models')->nullOnDelete();
            $table->foreignId('related_car_id')->nullable()->constrained('cars', 'car_id')->nullOnDelete();
            $table->string('cta_type', 32)->nullable();
            $table->string('cta_label')->nullable();
            $table->string('cta_url')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['status', 'scheduled_at']);
            $table->index(['category_id', 'status']);
            $table->index(['author_id', 'status']);
            $table->index(['is_featured', 'status']);
        });

        Schema::create('news_tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('news_tag', function (Blueprint $table): void {
            $table->foreignId('news_id')->constrained('news')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('news_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->primary(['news_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('news_tag');
        Schema::dropIfExists('news_tags');
        Schema::dropIfExists('news');
        Schema::dropIfExists('news_categories');
        Schema::enableForeignKeyConstraints();

        Schema::create('news', function (Blueprint $table): void {
            $table->id('news_id');
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content');
            $table->string('image')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }
};
