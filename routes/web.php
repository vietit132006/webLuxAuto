<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

// 1. NHÓM CHƯA ĐĂNG NHẬP (Khách)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// 2. NHÓM ĐÃ ĐĂNG NHẬP (Thành viên)
Route::middleware('auth')->group(function () {
    // Trang chủ
    Route::get('/', HomeController::class)->name('home');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // Danh sách xe
    Route::get('/xe', [CarController::class, 'index'])->name('cars.index');

    // Thêm xe (Phải đặt TRƯỚC route chi tiết)
    Route::get('/xe/create', [CarController::class, 'create'])->name('cars.create');
    Route::post('/xe', [CarController::class, 'store'])->name('cars.store');

    // Sửa xe
    Route::get('/xe/edit/{id}', [CarController::class, 'edit'])->name('cars.edit');
    Route::put('/xe/{id}', [CarController::class, 'update'])->name('cars.update');

    // Xem chi tiết xe (Phải đặt DƯỚI CÙNG trong nhóm /xe để không nuốt các chữ như 'create', 'edit')
    Route::get('/xe/{car}', [CarController::class, 'show'])->name('cars.show');
});
