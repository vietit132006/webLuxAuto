<?php

use App\Http\Controllers\Api\CarComparisonController;
use App\Http\Controllers\Api\CarPromotionController;
use App\Http\Controllers\Api\CarReviewReportController;
use App\Http\Controllers\Api\InventoryPublicController;
use App\Http\Controllers\Api\PromotionPublicController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:120,1')->group(function (): void {
    Route::get('/cars/compare', [CarComparisonController::class, 'index']);
    Route::get('/promotions/active', [PromotionPublicController::class, 'active']);
    Route::get('/promotions', [PromotionPublicController::class, 'index']);
    Route::get('/inventory/summary', [InventoryPublicController::class, 'summary']);

    // Per-car (product) endpoints
    Route::get('/cars/{car}/promotions', [CarPromotionController::class, 'index']);
    Route::get('/cars/{car}/reviews/report', [CarReviewReportController::class, 'show']);
});
