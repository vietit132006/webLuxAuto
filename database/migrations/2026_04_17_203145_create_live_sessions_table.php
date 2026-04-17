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
        Schema::create('live_sessions', function (Blueprint $table) {
            $table->id();

            // Lưu ID của luồng Live (ví dụ ID YouTube hoặc Facebook)
            $table->string('video_id')->nullable();

            // Trạng thái live: 1 là Đang phát, 0 là Đã tắt
            $table->boolean('is_active')->default(0);

            // Lưu danh sách ID các xe ghim trên live dưới dạng mảng JSON [1, 5, 8]
            $table->json('featured_car_ids')->nullable();

            // Tùy chọn: Thêm tiêu đề cho phiên Live để hiển thị trên web
            $table->string('title')->nullable();

            // Tùy chọn: Lời kêu gọi hành động (VD: "Chốt cọc ngay để nhận voucher 50tr!")
            $table->text('description')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('live_sessions');
    }
};
