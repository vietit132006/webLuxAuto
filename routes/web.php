<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VehicleController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/xe', [VehicleController::class, 'index'])->name('vehicles.index');
    Route::get('/xe/{vehicle}', [VehicleController::class, 'show'])->name('vehicles.show');
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});
