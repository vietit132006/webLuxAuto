<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('cars', function (Blueprint $table) {
            // Kiểm tra và thêm cột status
            if (!Schema::hasColumn('cars', 'status')) {
                $table->tinyInteger('status')->default(1)->comment('1: Mới 100%, 0: Xe lướt');
            }

            // Kiểm tra và thêm cột year
            if (!Schema::hasColumn('cars', 'year')) {
                $table->integer('year')->nullable()->comment('Đời xe');
            }

            // Kiểm tra và thêm cột color
            if (!Schema::hasColumn('cars', 'color')) {
                $table->string('color', 50)->nullable()->comment('Màu sắc');
            }

            // Kiểm tra và thêm cột fuel
            if (!Schema::hasColumn('cars', 'fuel')) {
                $table->string('fuel', 50)->nullable()->comment('Loại nhiên liệu');
            }
        });
    }

    public function down()
    {
        Schema::table('cars', function (Blueprint $table) {
            if (Schema::hasColumn('cars', 'status')) $table->dropColumn('status');
            if (Schema::hasColumn('cars', 'year')) $table->dropColumn('year');
            if (Schema::hasColumn('cars', 'color')) $table->dropColumn('color');
            if (Schema::hasColumn('cars', 'fuel')) $table->dropColumn('fuel');
        });
    }
};
