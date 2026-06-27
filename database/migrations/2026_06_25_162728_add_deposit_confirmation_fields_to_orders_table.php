<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('deposit_method', 30)->nullable()->after('deposit_date');
            $table->string('deposit_reference')->nullable()->after('deposit_method');
            $table->text('deposit_note')->nullable()->after('deposit_reference');
            $table->unsignedBigInteger('deposit_confirmed_by')->nullable()->after('deposit_note');

            $table->foreign('deposit_confirmed_by')
                ->references('user_id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['deposit_confirmed_by']);
            $table->dropColumn([
                'deposit_method',
                'deposit_reference',
                'deposit_note',
                'deposit_confirmed_by',
            ]);
        });
    }
};
