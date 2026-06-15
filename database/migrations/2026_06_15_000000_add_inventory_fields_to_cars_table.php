<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('internal_code', 50)->nullable()->unique()->after('license_plate');
            $table->unsignedBigInteger('estimated_rolling_price')->nullable()->after('price');
            $table->date('stock_in_date')->nullable()->after('owner_count');
            $table->date('on_road_date')->nullable()->after('stock_in_date');
            $table->string('vehicle_condition', 30)
                ->default('new')
                ->after('on_road_date')
                ->comment('new: Moi, used: Cu, display: Trung bay, test_drive: Lai thu');
            $table->string('current_location')->nullable()->after('vehicle_condition');
        });
    }

    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->dropUnique('cars_internal_code_unique');
            $table->dropColumn([
                'internal_code',
                'estimated_rolling_price',
                'stock_in_date',
                'on_road_date',
                'vehicle_condition',
                'current_location',
            ]);
        });
    }
};
