<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PublicNewsController;
use App\Http\Controllers\Api\PublicCategoryController;
use App\Http\Controllers\Api\Admin\CategoryController;
use App\Http\Controllers\Api\Admin\CacheNewsDebugController;
use App\Http\Controllers\Api\Admin\NewsController;

// Auth
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
Route::get('/user', [AuthController::class, 'user'])->middleware('auth:sanctum');

// Public
Route::get('/categories', PublicCategoryController::class);
Route::get('/news', [PublicNewsController::class, 'index']);
Route::get('/news/{slug}', [PublicNewsController::class, 'show']);

// Admin (protected)
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
    Route::apiResource('categories', CategoryController::class);
    Route::get('/debug/cache/news', [CacheNewsDebugController::class, 'index']);
    Route::apiResource('news', NewsController::class);
});
