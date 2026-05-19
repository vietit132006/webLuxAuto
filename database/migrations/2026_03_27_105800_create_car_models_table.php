<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Đây là nơi bạn định nghĩa cấu trúc bảng
     */
    public function up(): void
    {
        Schema::create('car_models', function (Blueprint $table) {
            $table->id();

            // Sửa dòng này: Chỉ định rõ cột tham chiếu là 'brand_id' thay vì 'id' mặc định
            $table->foreignId('brand_id')
                ->constrained('brands', 'brand_id') // <--- Quan trọng: thêm 'brand_id' vào đây
                ->onDelete('cascade');

            $table->string('name');
            $table->string('engine')->nullable();
            $table->string('fuel_type')->nullable();
            $table->string('transmission')->nullable();
            $table->string('body_type')->nullable();
            $table->string('drive_type')->nullable();
            $table->integer('seats')->nullable();
            $table->integer('doors')->nullable();
            $table->string('origin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     * Dùng để xóa bảng nếu bạn muốn quay lại trạng thái cũ
     */
    public function down(): void
    {
        Schema::dropIfExists('car_models');
    }
};
