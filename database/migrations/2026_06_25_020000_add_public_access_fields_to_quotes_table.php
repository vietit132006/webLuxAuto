<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('public_token', 80)->nullable()->unique()->after('quote_code');
            $table->timestamp('sent_at')->nullable()->after('expired_at');
            $table->timestamp('viewed_at')->nullable()->after('sent_at');
            $table->timestamp('customer_responded_at')->nullable()->after('viewed_at');
            $table->text('customer_response_note')->nullable()->after('customer_responded_at');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn([
                'public_token',
                'sent_at',
                'viewed_at',
                'customer_responded_at',
                'customer_response_note',
            ]);
        });
    }
};
