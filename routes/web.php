<?php

use App\Http\Controllers\AdminController; // Đã thêm dòng này
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CarController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\AdminOrderController;

// ==========================================
// 1. NHÓM CHƯA ĐĂNG NHẬP (Khách)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // QUÊN MẬT KHẨU
    Route::get('/forgot-password', [ResetPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])->name('password.email');

    // ĐẶT LẠI MẬT KHẨU (Gắn token)
    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
});

// ==========================================
// 2. NHÓM ĐÃ ĐĂNG NHẬP (Thành viên & Admin)
// ==========================================
Route::middleware('auth')->group(function () {
    Route::get('/thanh-toan/vnpay-return', [\App\Http\Controllers\OrderController::class, 'vnpayReturn'])->name('vnpay.return');
    // Trang chủ
    Route::get('/', HomeController::class)->name('home');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    // 1. ROUTE CHO KHÁCH HÀNG
    Route::get('/tin-tuc', [NewsController::class, 'index'])->name('news.index');
    Route::get('/tin-tuc/{slug}', [NewsController::class, 'show'])->name('news.show');

    // THÊM MỚI: QUẢN LÝ HỒ SƠ CÁ NHÂN
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Route xử lý đặt cọc
    Route::post('/dat-coc/{car_id}', [OrderController::class, 'processDeposit'])->name('order.deposit');

    //Route xem lịch sử
    Route::get('/lich-su-giao-dich', [OrderController::class, 'history'])->name('order.history');
    // ------------------------------------------
    // KHU VỰC KHÁCH HÀNG (Dành cho người mua)
    // ------------------------------------------
    Route::get('/xe', [CarController::class, 'index'])->name('cars.index');
    Route::get('/xe/{car}', [CarController::class, 'show'])->name('cars.show_public');

    // ------------------------------------------
    // KHU VỰC QUẢN TRỊ VIÊN (Chỉ Admin & Staff)
    // - Tự động thêm tiền tố /admin vào URL
    // - Tự động thêm tiền tố admin. vào tên Route
    // ------------------------------------------
    Route::middleware('role:admin,staff')->prefix('admin')->name('admin.')->group(function () {

        // Bảng điều khiển -> URL: /admin/dashboard
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // Quản lý xe (Tất cả trỏ về AdminController)
        Route::get('/xe', [AdminController::class, 'index'])->name('cars.index');
        Route::get('/xe/create', [AdminController::class, 'create'])->name('cars.create');
        Route::post('/xe', [AdminController::class, 'store'])->name('cars.store');

        // Xem chi tiết xe góc nhìn Admin -> URL: /admin/xe/{id}
        Route::get('/xe/{car}', [AdminController::class, 'show'])->name('cars.show');

        // Sửa xe
        Route::get('/xe/{id}/edit', [AdminController::class, 'edit'])->name('cars.edit'); // Chuẩn hóa URL
        Route::put('/xe/{id}', [AdminController::class, 'update'])->name('cars.update');

        // Xóa xe
        Route::delete('/xe/{id}', [AdminController::class, 'destroy'])->name('cars.destroy');


        // Quản lý brands (Dành cho Admin)
        // THÊM MỚI: QUẢN LÝ HÃNG XE (BRANDS)
        Route::get('/brands', [BrandController::class, 'index'])->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])->name('brands.store');
        Route::get('/brands/{id}/edit', [BrandController::class, 'edit'])->name('brands.edit');
        Route::put('/brands/{id}', [BrandController::class, 'update'])->name('brands.update');
        Route::delete('/brands/{id}', [BrandController::class, 'destroy'])->name('brands.destroy');

        // THÊM MỚI: QUẢN LÝ NGƯỜI DÙNG
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // THÊM MỚI: QUẢN LÝ TIN TỨC (NEWS)
        Route::get('/news', [AdminNewsController::class, 'index'])->name('news.index');
        Route::get('/news/create', [AdminNewsController::class, 'create'])->name('news.create');
        Route::post('/news', [AdminNewsController::class, 'store'])->name('news.store');
        Route::get('/news/{id}/detail', [AdminNewsController::class, 'show'])->name('news.show');
        Route::get('/news/{id}/edit', [AdminNewsController::class, 'edit'])->name('news.edit');
        Route::put('/news/{id}', [AdminNewsController::class, 'update'])->name('news.update');
        Route::delete('/news/{id}', [AdminNewsController::class, 'destroy'])->name('news.destroy');

        // QUẢN LÝ ĐƠN HÀNG
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    });
});
