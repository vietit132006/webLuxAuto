<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            if (!Schema::hasColumn('brands', 'logo')) {
                $table->string('logo')->nullable()->after('country');
            }

            if (!Schema::hasColumn('brands', 'slug')) {
                $table->string('slug')->nullable()->unique()->after('logo');
            }

            if (!Schema::hasColumn('brands', 'description')) {
                $table->text('description')->nullable()->after('slug');
            }

            if (!Schema::hasColumn('brands', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }

            if (!Schema::hasColumn('brands', 'sort_order')) {
                $table->integer('sort_order')->default(0)->after('is_active');
            }

            if (!Schema::hasColumn('brands', 'seo_title')) {
                $table->string('seo_title')->nullable()->after('sort_order');
            }

            if (!Schema::hasColumn('brands', 'seo_description')) {
                $table->text('seo_description')->nullable()->after('seo_title');
            }
        });
    }

    public function down(): void
    {
        Schema::table('brands', function (Blueprint $table) {
            if (Schema::hasColumn('brands', 'slug')) {
                $table->dropUnique('brands_slug_unique');
            }

            $columns = array_values(array_filter([
                Schema::hasColumn('brands', 'logo') ? 'logo' : null,
                Schema::hasColumn('brands', 'slug') ? 'slug' : null,
                Schema::hasColumn('brands', 'description') ? 'description' : null,
                Schema::hasColumn('brands', 'is_active') ? 'is_active' : null,
                Schema::hasColumn('brands', 'sort_order') ? 'sort_order' : null,
                Schema::hasColumn('brands', 'seo_title') ? 'seo_title' : null,
                Schema::hasColumn('brands', 'seo_description') ? 'seo_description' : null,
            ]));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
