<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cars')) {
            return;
        }

        $hasStockQuantity = Schema::hasColumn('cars', 'stock_quantity');
        $hasStock = Schema::hasColumn('cars', 'stock');
        $hasReservedQuantity = Schema::hasColumn('cars', 'reserved_quantity');

        if (!$hasStockQuantity && !$hasStock) {
            return;
        }

        DB::table('cars')
            ->where('status', 3)
            ->where(function ($query) use ($hasStockQuantity, $hasStock, $hasReservedQuantity): void {
                if ($hasStockQuantity && $hasStock) {
                    $query->whereRaw('COALESCE(stock_quantity, stock, 0) > 0');
                } elseif ($hasStockQuantity) {
                    $query->where('stock_quantity', '>', 0);
                } else {
                    $query->where('stock', '>', 0);
                }

                if ($hasReservedQuantity) {
                    $query->orWhere('reserved_quantity', '>', 0);
                }
            })
            ->orderBy('car_id')
            ->chunkById(100, function ($cars) use ($hasStockQuantity, $hasStock, $hasReservedQuantity): void {
                foreach ($cars as $car) {
                    $physicalBefore = (int) (
                        $hasStockQuantity
                            ? ($car->stock_quantity ?? ($hasStock ? $car->stock : 0))
                            : ($car->stock ?? 0)
                    );
                    $reservedBefore = $hasReservedQuantity ? (int) ($car->reserved_quantity ?? 0) : null;

                    $updates = [];

                    if ($hasStockQuantity) {
                        $updates['stock_quantity'] = 0;
                    }

                    if ($hasStock) {
                        $updates['stock'] = 0;
                    }

                    if ($hasReservedQuantity) {
                        $updates['reserved_quantity'] = 0;
                    }

                    DB::table('cars')
                        ->where('car_id', $car->car_id)
                        ->update($updates);

                    $this->recordStockMovement(
                        (int) $car->car_id,
                        $physicalBefore,
                        $reservedBefore
                    );
                }
            }, 'car_id');
    }

    public function down(): void
    {
        // Data reconciliation is intentionally not reversible.
    }

    private function recordStockMovement(int $carId, int $physicalBefore, ?int $reservedBefore): void
    {
        if (!Schema::hasTable('stock_movements')) {
            return;
        }

        $now = now();
        $movement = [
            'car_id' => $carId,
            'user_id' => null,
            'action_type' => 'delivery',
            'quantity_before' => $physicalBefore,
            'quantity_change' => -$physicalBefore,
            'quantity_after' => 0,
            'reason' => 'Đồng bộ tồn vật lý về 0 cho xe đã bán/giao xe.',
            'note' => 'Tự động đối soát dữ liệu sau khi siết nghiệp vụ giao xe.',
            'ip_address' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];

        if (Schema::hasColumn('stock_movements', 'reserved_before')) {
            $movement['reserved_before'] = $reservedBefore;
        }

        if (Schema::hasColumn('stock_movements', 'reserved_change')) {
            $movement['reserved_change'] = $reservedBefore === null ? null : -$reservedBefore;
        }

        if (Schema::hasColumn('stock_movements', 'reserved_after')) {
            $movement['reserved_after'] = $reservedBefore === null ? null : 0;
        }

        DB::table('stock_movements')->insert($movement);
    }
};
