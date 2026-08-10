<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\BlogPostController;
use App\Http\Controllers\Api\SiteSettingController;
use App\Http\Controllers\Api\ContactController;

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientLogoController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\QuoteRequestController;
use App\Http\Controllers\Api\ServiceRequestController;

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

// Catalog Read-Only Endpoints (Rate Limited 60 req/min)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/v1/categories', [CategoryController::class, 'index']);
    Route::get('/v1/categories/{slug}', [CategoryController::class, 'show']);
    Route::get('/v1/products', [ProductController::class, 'index']);
    Route::get('/v1/products/{slug}', [ProductController::class, 'show']);
    Route::get('/v1/client-logos', [ClientLogoController::class, 'index']);
    Route::get('/v1/projects', [ProjectController::class, 'index']);
    Route::get('/v1/blog', [BlogPostController::class, 'index']);
    Route::get('/v1/settings', [SiteSettingController::class, 'index']);
    Route::get('/v1/orders/{order_number}', [OrderController::class, 'show']);
    Route::get('/v1/service-requests/{request_number}', [ServiceRequestController::class, 'show']);
});

// Shopping Cart API endpoints (Rate Limited 60 req/min)
Route::middleware('throttle:60,1')->group(function () {
    Route::get('/v1/cart', [CartController::class, 'show']);
    Route::post('/v1/cart/items', [CartController::class, 'addItem']);
    Route::match(['put', 'patch'], '/v1/cart/items/{id}', [CartController::class, 'updateItem']);
    Route::delete('/v1/cart/items/{id}', [CartController::class, 'removeItem']);
    Route::delete('/v1/cart', [CartController::class, 'clearCart']);
});

// Public Mutation Endpoints (Rate Limited 15 req/min to protect against spam & bots)
Route::middleware('throttle:15,1')->group(function () {
    Route::post('/v1/orders', [OrderController::class, 'store']);
    Route::post('/v1/quote-requests', [QuoteRequestController::class, 'store']);
    Route::post('/v1/service-requests', [ServiceRequestController::class, 'store']);
    Route::post('/v1/contact', [ContactController::class, 'store']);
});
