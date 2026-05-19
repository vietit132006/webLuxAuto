<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dùng CREATE vì chúng ta đang xây dựng lại từ đầu bằng migrate:fresh
        Schema::create('cars', function (Blueprint $table) {
            $table->id('car_id'); // Khóa chính

            // 1. Kết nối với bảng mẫu xe (car_models)
            // Lưu ý: car_models.id phải dùng kiểu bigint unsigned (mặc định của $table->id())
            $table->foreignId('car_model_id')->constrained('car_models', 'id')->onDelete('cascade');

            // 2. Các thông tin định danh duy nhất của xe cũ
            $table->string('vin')->unique(); // Số khung
            $table->string('license_plate')->nullable(); // Biển số
            $table->string('name'); // Tên phiên bản (VD: Carrera S)

            // 3. Thông số biến động theo từng con xe
            $table->bigInteger('price');
            $table->integer('year');
            $table->integer('mileage_km'); // Số Odo
            $table->integer('owner_count')->default(1); // Số đời chủ
            $table->string('color')->nullable();
            $table->string('interior_color')->nullable();
            $table->text('description')->nullable();

            // 4. Media & Trạng thái
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->string('video_file')->nullable();
            $table->boolean('is_featured')->default(false);

            // SỬA LỖI TẠI ĐÂY: tinyInteger (viết hoa chữ I)
            $table->tinyInteger('status')->default(1)->comment('1: Sẵn sàng, 2: Cọc, 3: Đã bán');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
