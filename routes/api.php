<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\ContactController;

Route::prefix('v1')->group(function () {
    // Read-only API endpoints for static site generation & public frontend
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/blog', [BlogPostController::class, 'index']);
    Route::get('/settings', [SiteSettingController::class, 'index']);

    // Rate-limited contact submission endpoint (5 submissions per minute per IP)
    Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1');
});
