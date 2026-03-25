<?php

use App\Http\Controllers\Api\Admin\CacheNewsDebugController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\ContactController;
use App\Http\Controllers\Api\Admin\NewsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\PublicContactController;
use App\Http\Controllers\Api\PublicNewsController;
use Illuminate\Support\Facades\Route;

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
});
