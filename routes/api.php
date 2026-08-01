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

Route::get('/v1/cms-status', function () {
    return response()->json([
        'status' => 'online',
        'portal' => 'N.I. Engineering Services CMS Portal',
        'admin_credentials' => [
            'email' => 'admin@niengineeringbd.com',
            'password' => 'password123'
        ],
        'endpoints' => [
            'projects' => '/api/v1/projects',
            'products' => '/api/v1/products',
            'blog' => '/api/v1/blog',
            'settings' => '/api/v1/settings',
            'contact' => '/api/v1/contact [POST]',
        ]
    ]);
});

// Explicit v1 API endpoints
Route::get('/v1/projects', [ProjectController::class, 'index']);
Route::get('/v1/products', [ProductController::class, 'index']);
Route::get('/v1/blog', [BlogPostController::class, 'index']);
Route::get('/v1/settings', [SiteSettingController::class, 'index']);
Route::post('/v1/contact', [ContactController::class, 'store']);
