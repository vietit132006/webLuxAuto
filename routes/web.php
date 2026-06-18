<?php

use App\Http\Controllers\AdminController; // Đã thêm dòng này
use App\Http\Controllers\AdminLiveController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\Admin\CarController as AdminCarController;
use App\Http\Controllers\Admin\CarExcelController;
use App\Http\Controllers\Admin\TestDriveController;
use App\Http\Controllers\AccountSwitchController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CarController as ClientCarController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CarModelController;

Route::get('/livestream', [LiveController::class, 'index'])->name('livestream');

Route::post('/saved-login-accounts/login', [AccountSwitchController::class, 'loginWithSavedAccount'])
    ->name('saved-login-accounts.login');
Route::delete('/saved-login-accounts', [AccountSwitchController::class, 'destroySavedAccount'])
    ->name('saved-login-accounts.destroy');


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
    Route::get('/thanh-toan/vnpay-return', [OrderController::class, 'vnpayReturn'])->name('vnpay.return');
    // Trang chủ
    Route::get('/', HomeController::class)->name('home');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/saved-login-accounts', [AccountSwitchController::class, 'storeSavedAccount'])
        ->name('saved-login-accounts.store');
    Route::post('/account-switch', [AccountSwitchController::class, 'switchTo'])->name('account-switch.switch');
    Route::post('/account-switch/restore', [AccountSwitchController::class, 'restore'])->name('account-switch.restore');

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
    Route::get('/xe', [ClientCarController::class, 'index'])->name('cars.index');
    Route::get('/xe/{car}', [ClientCarController::class, 'show'])->name('cars.show_public');
    Route::post('/xe/{car}/danh-gia', [ClientCarController::class, 'storeReview'])->name('cars.reviews.store');

    Route::get('/khuyen-mai', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/so-sanh-xe', [CompareController::class, 'index'])->name('compare.index');

    // 1. DÀNH CHO KHÁCH HÀNG (Đặt trong nhóm middleware 'auth')
    Route::get('/ho-tro', [TicketController::class, 'history'])->name('ticket.history'); // Lịch sử hỗ trợ
    Route::get('/ho-tro/tao-moi', [TicketController::class, 'create'])->name('ticket.create'); // Form tạo ticket
    Route::post('/ho-tro', [TicketController::class, 'store'])->name('ticket.store'); // Xử lý lưu ticket

    // ------------------------------------------
    // KHU VỰC QUẢN TRỊ VIÊN (Chỉ Admin & Staff)
    // - Tự động thêm tiền tố /admin vào URL
    // - Tự động thêm tiền tố admin. vào tên Route
    // ------------------------------------------
    Route::middleware('role:admin,staff')->prefix('admin')->name('admin.')->group(function () {

        // Bảng điều khiển vẫn giữ AdminController
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

        // SỬA TẠI ĐÂY: Trỏ về CarController
        Route::get('/xe', [AdminCarController::class, 'index'])->name('cars.index');
        Route::get('/xe/create', [AdminCarController::class, 'create'])->name('cars.create');
        Route::post('/xe', [AdminCarController::class, 'store'])->name('cars.store');
        Route::get('/cars/export', [CarExcelController::class, 'export'])->name('cars.export');
        Route::get('/cars/inventory/export', [CarExcelController::class, 'exportInventory'])->name('cars.inventory.export');
        Route::get('/cars/import-template', [CarExcelController::class, 'template'])->name('cars.import.template');
        Route::post('/cars/import', [CarExcelController::class, 'import'])->name('cars.import');
        Route::get('/car-models/{id}/specs', [AdminCarController::class, 'getModelSpecs'])->name('cars.modelSpecs');

        // QUẢN LÝ MODEL XE
        Route::resource('/car-models', CarModelController::class);

        // Xem chi tiết xe
        Route::get('/xe/{car}', [AdminCarController::class, 'show'])->name('cars.show');

        // Sửa xe
        Route::get('/xe/{id}/edit', [AdminCarController::class, 'edit'])->name('cars.edit');
        Route::put('/xe/{id}', [AdminCarController::class, 'update'])->name('cars.update');

        // Xóa xe
        Route::delete('/xe/{id}', [AdminCarController::class, 'destroy'])->name('cars.destroy');


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

        // BÁO CÁO & KHUYẾN MÃI
        Route::get('/reports/sales', [AdminReportController::class, 'sales'])->name('reports.sales');
        Route::get('/reports/inventory', [AdminReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('/reports/inventory-check', [AdminReportController::class, 'inventoryCheck'])->name('reports.inventory_check');
        Route::post('/reports/inventory-log', [AdminReportController::class, 'storeInventoryLog'])->name('reports.inventory_log');
        Route::get('/reports/customers', [AdminReportController::class, 'customers'])->name('reports.customers');
        Route::get('/reports/reviews', [AdminReportController::class, 'reviews'])->name('reports.reviews');
        Route::get('/promotions', [AdminReportController::class, 'promotions'])->name('promotions');
        Route::put('/promotions', [AdminReportController::class, 'updatePromotions'])->name('promotions.update');

        // QUẢN LÝ LIVESTREAM
        Route::get('/live', [AdminLiveController::class, 'index'])->name('live.index');
        Route::post('/live/update', [AdminLiveController::class, 'update'])->name('live.update');

        // 2. DÀNH CHO ADMIN (Đặt trong nhóm middleware 'role:admin,staff' có prefix 'admin')
        Route::get('/tickets', [AdminTicketController::class, 'index'])->name('tickets.index'); // Quản lý ticket
        Route::post('/tickets/{id}/reply', [AdminTicketController::class, 'reply'])->name('tickets.reply'); // Admin trả lời

        // TEST DRIVE BOOKINGS (đặt lịch lái thử)
        Route::get('/test-drives', [TestDriveController::class, 'index'])->name('test_drives.index');
        Route::get('/test-drives/{id}', [TestDriveController::class, 'show'])->name('test_drives.show');
        Route::post('/test-drives/{id}/status', [TestDriveController::class, 'updateStatus'])->name('test_drives.updateStatus');
    });
});
