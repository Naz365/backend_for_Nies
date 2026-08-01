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

Route::get('/cms-login', function () {
    return response('<!DOCTYPE html><html><head><title>N.I. Engineering Services CMS</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0A192F;color:white;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box}.card{background:#1E293B;padding:2.5rem;border-radius:12px;width:100%;max-width:400px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.5);border:1px solid #334155}.logo{text-align:center;margin-bottom:1.5rem}.logo img{height:60px;width:auto}.title{font-size:1.25rem;font-weight:700;text-align:center;color:#E53E3E;margin-bottom:1.5rem;text-transform:uppercase;letter-spacing:0.05em}label{font-size:0.875rem;font-weight:600;color:#94A3B8;display:block;margin-bottom:0.5rem}input{width:100%;padding:12px;margin-bottom:1.25rem;border-radius:6px;border:1px solid #334155;background:#0F172A;color:white;font-size:1rem;box-sizing:border-box;outline:none}input:focus{border-color:#E53E3E}button{width:100%;padding:14px;background:#E53E3E;color:white;border:none;border-radius:6px;font-size:1rem;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:0.05em;transition:background 0.2s}button:hover{background:#C53030}.info{font-size:0.75rem;color:#94A3B8;text-align:center;margin-top:1.5rem;border-t:1px solid #334155;pt:1rem}</style></head><body><div class="card"><div class="logo"><img src="https://niengineeringbd.com/wp-content/uploads/2017/11/ni_logo-1.png" alt="N.I. Logo"></div><div class="title">Private CMS Portal</div><form method="POST" action="/api/v1/login-action"><label>Email Address</label><input type="email" name="email" value="admin@niengineeringbd.com" required><label>Password</label><input type="password" name="password" value="password123" required><button type="submit">Sign In to Dashboard</button></form><div class="info"><p>Pre-seeded Admin Credentials:<br><strong>admin@niengineeringbd.com</strong> / <strong>password123</strong></p></div></div></body></html>');
});

// Explicit v1 API endpoints
Route::get('/v1/projects', [ProjectController::class, 'index']);
Route::get('/v1/products', [ProductController::class, 'index']);
Route::get('/v1/blog', [BlogPostController::class, 'index']);
Route::get('/v1/settings', [SiteSettingController::class, 'index']);
Route::post('/v1/contact', [ContactController::class, 'store']);
