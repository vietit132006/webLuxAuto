<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_code', 20)->nullable()->unique()->after('order_id');
            $table->decimal('deposit_amount', 12, 2)->default(0)->after('total_price');
            $table->timestamp('deposit_date')->nullable()->after('deposit_amount');
        });

        DB::table('orders')
            ->select('order_id')
            ->orderBy('order_id')
            ->get()
            ->each(function ($order): void {
                DB::table('orders')
                    ->where('order_id', $order->order_id)
                    ->whereNull('order_code')
                    ->update([
                        'order_code' => sprintf('DH%06d', (int) $order->order_id),
                    ]);
            });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropUnique('orders_order_code_unique');
            $table->dropColumn(['order_code', 'deposit_amount', 'deposit_date']);
        });
    }
};
