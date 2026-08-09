<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\ContactController;

Route::get('/ping', function () {
    return response()->json(['status' => 'ok', 'timestamp' => now()->toIso8601String()]);
});

Route::get('/v1/health', function () {
    return response()->json([
        'status' => 'healthy',
        'timestamp' => now()->toIso8601String(),
        'service' => 'N.I. Engineering API'
    ]);
});

// Explicit v1 API endpoints
Route::get('/v1/projects', [ProjectController::class, 'index']);
Route::get('/v1/products', [ProductController::class, 'index']);
Route::get('/v1/blog', [BlogPostController::class, 'index']);
Route::get('/v1/settings', [SiteSettingController::class, 'index']);
Route::post('/v1/contact', [ContactController::class, 'store']);
