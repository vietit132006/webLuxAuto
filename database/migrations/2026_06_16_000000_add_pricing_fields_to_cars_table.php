<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            if (!Schema::hasColumn('cars', 'list_price')) {
                $table->unsignedBigInteger('list_price')->nullable()->after('price');
            }

            if (!Schema::hasColumn('cars', 'sale_price')) {
                $table->unsignedBigInteger('sale_price')->nullable()->after('list_price');
            }

            if (!Schema::hasColumn('cars', 'registration_fee')) {
                $table->unsignedBigInteger('registration_fee')->default(0)->after('sale_price');
            }

            if (!Schema::hasColumn('cars', 'license_plate_fee')) {
                $table->unsignedBigInteger('license_plate_fee')->default(0)->after('registration_fee');
            }

            if (!Schema::hasColumn('cars', 'inspection_fee')) {
                $table->unsignedBigInteger('inspection_fee')->default(0)->after('license_plate_fee');
            }

            if (!Schema::hasColumn('cars', 'insurance_fee')) {
                $table->unsignedBigInteger('insurance_fee')->default(0)->after('inspection_fee');
            }

            if (!Schema::hasColumn('cars', 'other_fees')) {
                $table->unsignedBigInteger('other_fees')->default(0)->after('insurance_fee');
            }

            if (!Schema::hasColumn('cars', 'registration_area')) {
                $table->string('registration_area', 100)->nullable()->after('estimated_rolling_price');
            }
        });

        DB::table('cars')
            ->whereNull('list_price')
            ->update(['list_price' => DB::raw('price')]);

        if (Schema::hasColumn('cars', 'estimated_rolling_price')) {
            DB::table('cars')
                ->whereNull('estimated_rolling_price')
                ->update(['estimated_rolling_price' => DB::raw('price')]);
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            'registration_area',
            'other_fees',
            'insurance_fee',
            'inspection_fee',
            'license_plate_fee',
            'registration_fee',
            'sale_price',
            'list_price',
        ], fn ($column) => Schema::hasColumn('cars', $column)));

        if ($columns !== []) {
            Schema::table('cars', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
