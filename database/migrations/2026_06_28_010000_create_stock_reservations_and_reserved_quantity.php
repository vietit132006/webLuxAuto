<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('cars', 'reserved_quantity')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->unsignedInteger('reserved_quantity')
                    ->default(0)
                    ->after(Schema::hasColumn('cars', 'stock_quantity') ? 'stock_quantity' : 'stock');
            });

            DB::table('cars')->whereNull('reserved_quantity')->update(['reserved_quantity' => 0]);
        }

        if (!Schema::hasTable('stock_reservations')) {
            Schema::create('stock_reservations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('car_id')
                    ->constrained('cars', 'car_id')
                    ->cascadeOnDelete();
                $table->foreignId('order_id')
                    ->nullable()
                    ->constrained('orders', 'order_id')
                    ->nullOnDelete();
                $table->foreignId('quote_id')
                    ->nullable()
                    ->constrained('quotes', 'quote_id')
                    ->nullOnDelete();
                $table->foreignId('user_id')
                    ->nullable()
                    ->constrained('users', 'user_id')
                    ->nullOnDelete();
                $table->unsignedInteger('quantity')->default(1);
                $table->string('status', 30)->default('active')->index();
                $table->foreignId('reserved_by')
                    ->nullable()
                    ->constrained('users', 'user_id')
                    ->nullOnDelete();
                $table->timestamp('reserved_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamp('released_at')->nullable();
                $table->string('release_reason', 500)->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['car_id', 'status']);
                $table->index(['order_id', 'status']);
                $table->index(['quote_id', 'status']);
                $table->index(['reserved_by', 'status']);
            });
        }

        if (Schema::hasTable('stock_movements')) {
            Schema::table('stock_movements', function (Blueprint $table) {
                if (!Schema::hasColumn('stock_movements', 'reserved_before')) {
                    $table->integer('reserved_before')->nullable()->after('quantity_after');
                }

                if (!Schema::hasColumn('stock_movements', 'reserved_change')) {
                    $table->integer('reserved_change')->nullable()->after('reserved_before');
                }

                if (!Schema::hasColumn('stock_movements', 'reserved_after')) {
                    $table->integer('reserved_after')->nullable()->after('reserved_change');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_reservations');

        if (Schema::hasTable('stock_movements')) {
            $stockMovementColumns = array_values(array_filter([
                'reserved_before',
                'reserved_change',
                'reserved_after',
            ], fn (string $column) => Schema::hasColumn('stock_movements', $column)));

            if ($stockMovementColumns !== []) {
                Schema::table('stock_movements', function (Blueprint $table) use ($stockMovementColumns) {
                    $table->dropColumn($stockMovementColumns);
                });
            }
        }

        if (Schema::hasColumn('cars', 'reserved_quantity')) {
            Schema::table('cars', function (Blueprint $table) {
                $table->dropColumn('reserved_quantity');
            });
        }
    }
};
