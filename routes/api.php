<?php

use App\Http\Controllers\Api\Admin\CacheNewsDebugController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ContactController;
use App\Http\Controllers\Api\Admin\FeedConfigController as AdminFeedConfigController;
use App\Http\Controllers\Api\Admin\MarketInstrumentController;
use App\Http\Controllers\Api\Admin\NewsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\FeedConfigController;
use App\Http\Controllers\Api\MarketIngestController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\PublicContactController;
use App\Http\Controllers\Api\PublicNewsController;
use Illuminate\Support\Facades\Route;

// Market data (autenticado via bearer token do MT5)
Route::get('/market/health', [MarketIngestController::class, 'health']);
Route::get('/market/quotes', [MarketIngestController::class, 'quotes']);
Route::post('/market/snapshot', [MarketIngestController::class, 'snapshot'])
    ->middleware('throttle:120,1');
Route::get('/feed/config', [FeedConfigController::class, 'show']);
Route::get('/feed/status', [FeedConfigController::class, 'status']);

// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Public
Route::get('/categories', PublicCategoryController::class);
Route::get('/news', [PublicNewsController::class, 'index']);
Route::get('/news/{slug}', [PublicNewsController::class, 'show']);
Route::post('/contacts', [PublicContactController::class, 'store']);

// Admin (protected)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::get('/debug/cache/news', [CacheNewsDebugController::class, 'index']);
    Route::apiResource('news', NewsController::class);
    Route::apiResource('contacts', ContactController::class)->only(['index']);
    Route::apiResource('market-instruments', MarketInstrumentController::class);

    Route::get('/feed-configs', [AdminFeedConfigController::class, 'index']);
    Route::put('/feed-configs/{feedId}/toggle', [AdminFeedConfigController::class, 'toggle']);
});
