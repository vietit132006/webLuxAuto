<?php

use App\Http\Controllers\AccountSwitchController;
use App\Http\Controllers\Admin\CarController as AdminCarController;
use App\Http\Controllers\Admin\CarExcelController;
use App\Http\Controllers\Admin\CarModelController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\QuoteController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\StockMovementController;
use App\Http\Controllers\Admin\TestDriveController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminLiveController;
use App\Http\Controllers\AdminNewsController;
use App\Http\Controllers\AdminOrderController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminTicketController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\CarController as ClientCarController;
use App\Http\Controllers\CompareController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LiveController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\PublicQuoteController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/livestream', [LiveController::class, 'index'])->name('livestream');
Route::get('/bao-gia/{quote}/{token}', [PublicQuoteController::class, 'show'])->name('quotes.public.show');
Route::get('/bao-gia/{quote}/{token}/pdf', [PublicQuoteController::class, 'pdf'])->name('quotes.public.pdf');
Route::post('/bao-gia/{quote}/{token}/phan-hoi', [PublicQuoteController::class, 'respond'])->name('quotes.public.respond');

Route::post('/saved-login-accounts/login', [AccountSwitchController::class, 'loginWithSavedAccount'])
    ->name('saved-login-accounts.login');
Route::delete('/saved-login-accounts', [AccountSwitchController::class, 'destroySavedAccount'])
    ->name('saved-login-accounts.destroy');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [ResetPasswordController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [ResetPasswordController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [ResetPasswordController::class, 'resetPassword'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/thanh-toan/vnpay-return', [OrderController::class, 'vnpayReturn'])->name('vnpay.return');
    Route::get('/', HomeController::class)->name('home');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');

    Route::post('/saved-login-accounts', [AccountSwitchController::class, 'storeSavedAccount'])
        ->name('saved-login-accounts.store');
    Route::post('/account-switch', [AccountSwitchController::class, 'switchTo'])->name('account-switch.switch');
    Route::post('/account-switch/restore', [AccountSwitchController::class, 'restore'])->name('account-switch.restore');

    Route::get('/tin-tuc', [NewsController::class, 'index'])->name('news.index');
    Route::get('/tin-tuc/{slug}', [NewsController::class, 'show'])->name('news.show');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    Route::post('/dat-coc/{car_id}', [OrderController::class, 'processDeposit'])->name('order.deposit');
    Route::get('/lich-su-giao-dich', [OrderController::class, 'history'])->name('order.history');

    Route::get('/xe', [ClientCarController::class, 'index'])->name('cars.index');
    Route::get('/xe/{car}', [ClientCarController::class, 'show'])->name('cars.show_public');
    Route::post('/xe/{car}/danh-gia', [ClientCarController::class, 'storeReview'])->name('cars.reviews.store');

    Route::get('/khuyen-mai', [PromotionController::class, 'index'])->name('promotions.index');
    Route::get('/so-sanh-xe', [CompareController::class, 'index'])->name('compare.index');

    Route::get('/ho-tro', [TicketController::class, 'history'])->name('ticket.history');
    Route::get('/ho-tro/tao-moi', [TicketController::class, 'create'])->name('ticket.create');
    Route::post('/ho-tro', [TicketController::class, 'store'])->name('ticket.store');

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard'])
            ->middleware('permission:dashboard.view')
            ->name('dashboard');

        Route::get('/xe', [AdminCarController::class, 'index'])
            ->middleware('permission:cars.view')
            ->name('cars.index');
        Route::get('/xe/create', [AdminCarController::class, 'create'])
            ->middleware('permission:cars.create')
            ->name('cars.create');
        Route::post('/xe', [AdminCarController::class, 'store'])
            ->middleware('permission:cars.create')
            ->name('cars.store');
        Route::get('/cars/export', [CarExcelController::class, 'export'])
            ->middleware('permission:cars.view')
            ->name('cars.export');
        Route::get('/cars/inventory/export', [CarExcelController::class, 'exportInventory'])
            ->middleware('permission:inventory.view')
            ->name('cars.inventory.export');
        Route::get('/cars/import-template', [CarExcelController::class, 'template'])
            ->middleware('permission:cars.create')
            ->name('cars.import.template');
        Route::post('/cars/import', [CarExcelController::class, 'import'])
            ->middleware('permission:cars.create')
            ->name('cars.import');
        Route::get('/car-models/{id}/specs', [AdminCarController::class, 'getModelSpecs'])
            ->middleware('permission:cars.create|cars.edit')
            ->name('cars.modelSpecs');
        Route::get('/xe/{car}', [AdminCarController::class, 'show'])
            ->middleware('permission:cars.view')
            ->name('cars.show');
        Route::get('/xe/{id}/edit', [AdminCarController::class, 'edit'])
            ->middleware('permission:cars.edit')
            ->name('cars.edit');
        Route::put('/xe/{id}', [AdminCarController::class, 'update'])
            ->middleware('permission:cars.edit')
            ->name('cars.update');
        Route::delete('/xe/{id}', [AdminCarController::class, 'destroy'])
            ->middleware('permission:cars.delete')
            ->name('cars.destroy');

        Route::get('/car-models', [CarModelController::class, 'index'])
            ->middleware('permission:cars.view')
            ->name('car-models.index');
        Route::get('/car-models/create', [CarModelController::class, 'create'])
            ->middleware('permission:cars.create')
            ->name('car-models.create');
        Route::post('/car-models', [CarModelController::class, 'store'])
            ->middleware('permission:cars.create')
            ->name('car-models.store');
        Route::get('/car-models/{car_model}', [CarModelController::class, 'show'])
            ->middleware('permission:cars.view')
            ->name('car-models.show');
        Route::get('/car-models/{car_model}/edit', [CarModelController::class, 'edit'])
            ->middleware('permission:cars.edit')
            ->name('car-models.edit');
        Route::put('/car-models/{car_model}', [CarModelController::class, 'update'])
            ->middleware('permission:cars.edit')
            ->name('car-models.update');
        Route::delete('/car-models/{car_model}', [CarModelController::class, 'destroy'])
            ->middleware('permission:cars.delete')
            ->name('car-models.destroy');

        Route::get('/brands', [BrandController::class, 'index'])
            ->middleware('permission:cars.view')
            ->name('brands.index');
        Route::get('/brands/create', [BrandController::class, 'create'])
            ->middleware('permission:cars.create')
            ->name('brands.create');
        Route::post('/brands', [BrandController::class, 'store'])
            ->middleware('permission:cars.create')
            ->name('brands.store');
        Route::get('/brands/{id}/edit', [BrandController::class, 'edit'])
            ->middleware('permission:cars.edit')
            ->name('brands.edit');
        Route::put('/brands/{id}', [BrandController::class, 'update'])
            ->middleware('permission:cars.edit')
            ->name('brands.update');
        Route::patch('/brands/{id}/toggle-status', [BrandController::class, 'toggleStatus'])
            ->middleware('permission:cars.edit')
            ->name('brands.toggle-status');
        Route::delete('/brands/{id}', [BrandController::class, 'destroy'])
            ->middleware('permission:cars.delete')
            ->name('brands.destroy');

        Route::get('/users', [UserController::class, 'index'])
            ->middleware('permission:users.view')
            ->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])
            ->middleware('permission:users.create')
            ->name('users.create');
        Route::post('/users', [UserController::class, 'store'])
            ->middleware('permission:users.create')
            ->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])
            ->middleware('permission:users.edit')
            ->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])
            ->middleware('permission:users.edit')
            ->name('users.update');
        Route::patch('/users/{id}/status', [UserController::class, 'toggleStatus'])
            ->middleware('permission:users.edit')
            ->name('users.toggle-status');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])
            ->middleware('permission:users.delete')
            ->name('users.destroy');

        Route::get('/roles', [RoleController::class, 'index'])
            ->middleware('permission:roles.view')
            ->name('roles.index');
        Route::get('/roles/create', [RoleController::class, 'create'])
            ->middleware('permission:roles.create')
            ->name('roles.create');
        Route::post('/roles', [RoleController::class, 'store'])
            ->middleware('permission:roles.create')
            ->name('roles.store');
        Route::get('/roles/{role}/edit', [RoleController::class, 'edit'])
            ->middleware('permission:roles.edit')
            ->name('roles.edit');
        Route::put('/roles/{role}', [RoleController::class, 'update'])
            ->middleware('permission:roles.edit')
            ->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])
            ->middleware('permission:roles.delete')
            ->name('roles.destroy');

        Route::get('/news', [AdminNewsController::class, 'index'])
            ->middleware('permission:news.view')
            ->name('news.index');
        Route::get('/news/create', [AdminNewsController::class, 'create'])
            ->middleware('permission:news.create')
            ->name('news.create');
        Route::post('/news', [AdminNewsController::class, 'store'])
            ->middleware('permission:news.create')
            ->name('news.store');
        Route::get('/news/{id}/detail', [AdminNewsController::class, 'show'])
            ->middleware('permission:news.view')
            ->name('news.show');
        Route::get('/news/{id}/edit', [AdminNewsController::class, 'edit'])
            ->middleware('permission:news.edit')
            ->name('news.edit');
        Route::put('/news/{id}', [AdminNewsController::class, 'update'])
            ->middleware('permission:news.edit')
            ->name('news.update');
        Route::delete('/news/{id}', [AdminNewsController::class, 'destroy'])
            ->middleware('permission:news.delete')
            ->name('news.destroy');

        Route::get('/orders', [AdminOrderController::class, 'index'])
            ->middleware('permission:orders.view')
            ->name('orders.index');
        Route::get('/orders/create', [AdminOrderController::class, 'create'])
            ->middleware('permission:orders.create')
            ->name('orders.create');
        Route::post('/orders', [AdminOrderController::class, 'store'])
            ->middleware('permission:orders.create')
            ->name('orders.store');
        Route::get('/orders/export', [AdminOrderController::class, 'export'])
            ->middleware('permission:orders.view')
            ->name('orders.export');
        Route::get('/orders/{id}', [AdminOrderController::class, 'show'])
            ->middleware('permission:orders.view')
            ->name('orders.show');
        Route::patch('/orders/{id}/deposit', [AdminOrderController::class, 'updateDeposit'])
            ->middleware('permission:orders.edit')
            ->name('orders.updateDeposit');
        Route::post('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])
            ->middleware('permission:orders.edit')
            ->name('orders.updateStatus');
        Route::patch('/orders/{order}/delivery', [AdminOrderController::class, 'updateDelivery'])
            ->middleware('permission:orders.edit|inventory.adjust')
            ->name('orders.updateDelivery');
        Route::post('/orders/{order}/delivery/files', [AdminOrderController::class, 'uploadDeliveryFiles'])
            ->middleware('permission:orders.edit')
            ->name('orders.deliveryFiles.store');
        Route::get('/delivery-files/{deliveryFile}/view', [AdminOrderController::class, 'viewDeliveryFile'])
            ->middleware('permission:orders.view')
            ->name('orders.deliveryFiles.view');
        Route::get('/delivery-files/{deliveryFile}/download', [AdminOrderController::class, 'downloadDeliveryFile'])
            ->middleware('permission:orders.view')
            ->name('orders.deliveryFiles.download');
        Route::delete('/delivery-files/{deliveryFile}', [AdminOrderController::class, 'deleteDeliveryFile'])
            ->middleware('permission:orders.edit')
            ->name('orders.deliveryFiles.destroy');

        Route::get('/customers', [CustomerController::class, 'index'])
            ->middleware('permission:customers.view')
            ->name('customers.index');
        Route::get('/customers/create', [CustomerController::class, 'create'])
            ->middleware('permission:customers.create')
            ->name('customers.create');
        Route::post('/customers', [CustomerController::class, 'store'])
            ->middleware('permission:customers.create')
            ->name('customers.store');
        Route::get('/customers/{customer}', [CustomerController::class, 'show'])
            ->middleware('permission:customers.view')
            ->name('customers.show');
        Route::get('/customers/{customer}/edit', [CustomerController::class, 'edit'])
            ->middleware('permission:customers.edit')
            ->name('customers.edit');
        Route::put('/customers/{customer}', [CustomerController::class, 'update'])
            ->middleware('permission:customers.edit')
            ->name('customers.update');
        Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])
            ->middleware('permission:customers.delete')
            ->name('customers.destroy');
        Route::post('/customers/{customer}/interactions', [CustomerController::class, 'storeInteraction'])
            ->middleware('permission:customers.edit')
            ->name('customers.interactions.store');

        Route::get('/quotes', [QuoteController::class, 'index'])
            ->middleware('permission:quotes.view')
            ->name('quotes.index');
        Route::get('/quotes/create', [QuoteController::class, 'create'])
            ->middleware('permission:quotes.create')
            ->name('quotes.create');
        Route::post('/quotes', [QuoteController::class, 'store'])
            ->middleware('permission:quotes.create')
            ->name('quotes.store');
        Route::get('/quotes/{quote}/pdf', [QuoteController::class, 'pdf'])
            ->middleware('permission:quotes.view')
            ->name('quotes.pdf');
        Route::post('/quotes/{quote}/send', [QuoteController::class, 'send'])
            ->middleware('permission:quotes.edit')
            ->name('quotes.send');
        Route::post('/quotes/{quote}/create-order', [QuoteController::class, 'createOrderFromQuote'])
            ->middleware('permission:orders.create')
            ->name('quotes.createOrder');
        Route::get('/quotes/{quote}', [QuoteController::class, 'show'])
            ->middleware('permission:quotes.view')
            ->name('quotes.show');
        Route::get('/quotes/{quote}/edit', [QuoteController::class, 'edit'])
            ->middleware('permission:quotes.edit')
            ->name('quotes.edit');
        Route::put('/quotes/{quote}', [QuoteController::class, 'update'])
            ->middleware('permission:quotes.edit')
            ->name('quotes.update');
        Route::delete('/quotes/{quote}', [QuoteController::class, 'destroy'])
            ->middleware('permission:quotes.delete')
            ->name('quotes.destroy');

        Route::get('/reports/sales', [AdminReportController::class, 'sales'])
            ->middleware('permission:reports.view')
            ->name('reports.sales');
        Route::get('/reports/inventory', [AdminReportController::class, 'inventory'])
            ->middleware('permission:inventory.view')
            ->name('reports.inventory');
        Route::get('/reports/inventory-check', [AdminReportController::class, 'inventoryCheck'])
            ->middleware('permission:inventory.adjust')
            ->name('reports.inventory_check');
        Route::post('/reports/inventory-log', [AdminReportController::class, 'storeInventoryLog'])
            ->middleware('permission:inventory.adjust')
            ->name('reports.inventory_log');
        Route::get('/reports/customers', [AdminReportController::class, 'customers'])
            ->middleware('permission:customers.view')
            ->name('reports.customers');
        Route::get('/reports/reviews', [AdminReportController::class, 'reviews'])
            ->middleware('permission:reviews.view')
            ->name('reports.reviews');
        Route::get('/promotions', [AdminReportController::class, 'promotions'])
            ->middleware('permission:promotions.view')
            ->name('promotions');
        Route::put('/promotions', [AdminReportController::class, 'updatePromotions'])
            ->middleware('permission:promotions.edit')
            ->name('promotions.update');

        Route::get('/live', [AdminLiveController::class, 'index'])
            ->middleware('permission:live.view')
            ->name('live.index');
        Route::post('/live/update', [AdminLiveController::class, 'update'])
            ->middleware('permission:live.edit')
            ->name('live.update');

        Route::get('/tickets', [AdminTicketController::class, 'index'])
            ->middleware('permission:tickets.view')
            ->name('tickets.index');
        Route::post('/tickets/{id}/reply', [AdminTicketController::class, 'reply'])
            ->middleware('permission:tickets.reply')
            ->name('tickets.reply');

        Route::get('/test-drives', [TestDriveController::class, 'index'])
            ->middleware('permission:test_drives.view')
            ->name('test_drives.index');
        Route::get('/test-drives/export', [TestDriveController::class, 'export'])
            ->middleware('permission:test_drives.export')
            ->name('test_drives.export');
        Route::get('/test-drives/{id}/quotes/create', [QuoteController::class, 'createFromTestDrive'])
            ->middleware(['permission:test_drives.view', 'permission:quotes.create'])
            ->name('test_drives.quotes.create');
        Route::get('/test-drives/{id}', [TestDriveController::class, 'show'])
            ->middleware('permission:test_drives.view')
            ->name('test_drives.show');
        Route::put('/test-drives/{id}/appointment', [TestDriveController::class, 'updateAppointment'])
            ->middleware('permission:test_drives.edit')
            ->name('test_drives.updateAppointment');
        Route::post('/test-drives/{id}/status', [TestDriveController::class, 'updateStatus'])
            ->middleware('permission:test_drives.edit')
            ->name('test_drives.updateStatus');
        Route::post('/test-drives/{id}/notes', [TestDriveController::class, 'storeNote'])
            ->middleware('permission:test_drives.edit')
            ->name('test_drives.notes.store');
        Route::post('/test-drives/{id}/files', [TestDriveController::class, 'storeFiles'])
            ->middleware('permission:test_drives.edit')
            ->name('test_drives.files.store');
        Route::get('/test-drives/{id}/files/{file}/view', [TestDriveController::class, 'viewFile'])
            ->middleware('permission:test_drives.view')
            ->name('test_drives.files.view');
        Route::get('/test-drives/{id}/files/{file}/download', [TestDriveController::class, 'downloadFile'])
            ->middleware('permission:test_drives.view')
            ->name('test_drives.files.download');
        Route::delete('/test-drives/{id}/files/{file}', [TestDriveController::class, 'destroyFile'])
            ->middleware('permission:test_drives.delete')
            ->name('test_drives.files.destroy');

        Route::get('/stock-movements', [StockMovementController::class, 'index'])
            ->middleware('permission:inventory.history')
            ->name('stock-movements.index');
        Route::get('/stock-movements/export', [StockMovementController::class, 'export'])
            ->middleware('permission:inventory.history')
            ->name('stock-movements.export');
    });
});
