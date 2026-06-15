<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hadStockQuantity = Schema::hasColumn('cars', 'stock_quantity');
        $hadStock = Schema::hasColumn('cars', 'stock');

        Schema::table('cars', function (Blueprint $table) use ($hadStockQuantity, $hadStock) {
            if (!$hadStockQuantity) {
                $table->unsignedInteger('stock_quantity')->default(1);
            }

            if (!$hadStock) {
                $table->unsignedInteger('stock')->default(1);
            }
        });

        if (!$hadStockQuantity && Schema::hasColumn('cars', 'stock')) {
            DB::table('cars')->update([
                'stock_quantity' => DB::raw('COALESCE(stock, 1)'),
            ]);
        }

        if (!$hadStock && Schema::hasColumn('cars', 'stock_quantity')) {
            DB::table('cars')->update([
                'stock' => DB::raw('COALESCE(stock_quantity, 1)'),
            ]);
        }
    }

    public function down(): void
    {
        $columns = array_values(array_filter([
            'stock_quantity',
        ], fn ($column) => Schema::hasColumn('cars', $column)));

        if ($columns !== []) {
            Schema::table('cars', function (Blueprint $table) use ($columns) {
                $table->dropColumn($columns);
            });
        }
    }
};
