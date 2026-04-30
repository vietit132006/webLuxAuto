<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('cars', function (Blueprint $table) {
            $table->string('engine')->nullable()->after('year');
            $table->string('interior_color')->nullable()->after('color');
            $table->string('origin')->nullable()->after('transmission');
            $table->string('body_type')->nullable()->after('origin');
            $table->integer('seats')->nullable()->after('body_type');
            $table->integer('doors')->nullable()->after('seats');
            $table->string('drive_type')->nullable()->after('doors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cars', function (Blueprint $table) {
            //
        });
    }
};
