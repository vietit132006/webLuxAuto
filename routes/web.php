<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// 1. NHÓM CHƯA ĐĂNG NHẬP (Khách)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// 2. NHÓM ĐÃ ĐĂNG NHẬP (Thành viên)
Route::middleware('auth')->group(function () {

    // Trang chủ
    Route::get('/', HomeController::class)->name('home');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // ==========================================
    // KHU VỰC KHÁCH HÀNG (Giao diện dạng Thẻ/Card)
    // ==========================================
    Route::get('/xe', [CarController::class, 'index'])->name('cars.index');
    Route::get('/xe/{car}', [CarController::class, 'show'])->name('cars.show');

    // ==========================================
    // KHU VỰC QUẢN TRỊ VIÊN (Giao diện dạng Bảng)
    // Đường dẫn sẽ tự động thêm chữ /admin/ phía trước
    // ==========================================
    Route::middleware('role:admin,staff')->prefix('admin')->group(function () {

        // Danh sách xe dành riêng cho Admin
        Route::get('/xe', [CarController::class, 'adminIndex'])->name('admin.cars.index');

        // Thêm xe mới
        Route::get('/xe/create', [CarController::class, 'create'])->name('cars.create');
        Route::post('/xe', [CarController::class, 'store'])->name('cars.store');

        // Sửa xe
        Route::get('/xe/edit/{id}', [CarController::class, 'edit'])->name('cars.edit');
        Route::put('/xe/{id}', [CarController::class, 'update'])->name('cars.update');

        // Xóa xe (Mới được bổ sung)
        Route::delete('/xe/{id}', [CarController::class, 'destroy'])->name('cars.destroy');
    });
});
