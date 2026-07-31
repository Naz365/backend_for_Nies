<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::get('/status-check', function () {
    return response()->json([
        'status' => 'online',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
        'database' => \App\Models\Project::count() . ' projects',
        'session_driver' => config('session.driver'),
        'cache_driver' => config('cache.default')
    ]);
});

Route::get('/admin/login', function () {
    return response('<!DOCTYPE html><html><head><title>N.I. Engineering Services CMS</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><style>body{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;background:#0A192F;color:white;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:20px;box-sizing:border-box}.card{background:#1E293B;padding:2.5rem;border-radius:12px;width:100%;max-width:400px;box-shadow:0 20px 25px -5px rgba(0,0,0,0.5);border:1px solid #334155}.logo{text-align:center;margin-bottom:1.5rem}.logo img{height:60px;width:auto}.title{font-size:1.25rem;font-weight:700;text-align:center;color:#E53E3E;margin-bottom:1.5rem;text-transform:uppercase;letter-spacing:0.05em}label{font-size:0.875rem;font-weight:600;color:#94A3B8;display:block;margin-bottom:0.5rem}input{width:100%;padding:12px;margin-bottom:1.25rem;border-radius:6px;border:1px solid #334155;background:#0F172A;color:white;font-size:1rem;box-sizing:border-box;outline:none}input:focus{border-color:#E53E3E}button{width:100%;padding:14px;background:#E53E3E;color:white;border:none;border-radius:6px;font-size:1rem;font-weight:700;cursor:pointer;text-transform:uppercase;letter-spacing:0.05em;transition:background 0.2s}button:hover{background:#C53030}.info{font-size:0.75rem;color:#94A3B8;text-align:center;margin-top:1.5rem;border-t:1px solid #334155;pt:1rem}</style></head><body><div class="card"><div class="logo"><img src="https://niengineeringbd.com/wp-content/uploads/2017/11/ni_logo-1.png" alt="N.I. Logo"></div><div class="title">Private CMS Portal</div><form method="POST" action="/admin/login-action"><label>Email Address</label><input type="email" name="email" value="admin@niengineeringbd.com" required><label>Password</label><input type="password" name="password" value="password123" required><button type="submit">Sign In to Dashboard</button></form><div class="info"><p>Pre-seeded Admin Credentials:<br><strong>admin@niengineeringbd.com</strong> / <strong>password123</strong></p></div></div></body></html>');
});

Route::post('/admin/login-action', function (Request $request) {
    $credentials = $request->only('email', 'password');
    if (auth()->attempt($credentials)) {
        return redirect('/admin/dashboard');
    }
    return back()->with('error', 'Invalid admin credentials');
});

Route::get('/admin/dashboard', function () {
    if (!auth()->check()) {
        return redirect('/admin/login');
    }
    $projects = \App\Models\Project::count();
    $products = \App\Models\Product::count();
    $posts = \App\Models\BlogPost::count();
    $submissions = \App\Models\ContactSubmission::count();
    
    return response('<!DOCTYPE html><html><head><title>N.I. CMS Dashboard</title><meta name="viewport" content="width=device-width, initial-scale=1.0"><style>body{font-family:sans-serif;background:#0A192F;color:white;margin:0;padding:2rem}.header{display:flex;justify-content:space-between;align-items:center;padding-bottom:1rem;border-b:2px solid #E53E3E}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:1.5rem;margin-top:2rem}.card{background:#1E293B;padding:1.5rem;border-radius:8px;border:1px solid #334155}.num{font-size:2.5rem;font-weight:bold;color:#E53E3E}.btn{display:inline-block;padding:10px 20px;background:#E53E3E;color:white;text-decoration:none;border-radius:4px;font-weight:bold;margin-top:1rem}</style></head><body><div class="header"><h1>N.I. Engineering CMS Admin Dashboard</h1><a href="/admin/logout" class="btn">Logout</a></div><div class="grid"><div class="card"><h3>Published Projects</h3><div class="num">'.$projects.'</div></div><div class="card"><h3>Products & Solutions</h3><div class="num">'.$products.'</div></div><div class="card"><h3>Blog Articles</h3><div class="num">'.$posts.'</div></div><div class="card"><h3>Contact Inquiries</h3><div class="num">'.$submissions.'</div></div></div><div style="margin-top:2rem"><a href="/admin/deploy" class="btn" style="background:#2563EB">Trigger Static Site Re-Deploy</a></div></body></html>');
});

Route::get('/admin/logout', function () {
    auth()->logout();
    return redirect('/admin/login');
});

Route::get('/admin/deploy', function () {
    \App\Services\DeployWebhookService::triggerDeploy();
    return back()->with('status', 'Static site build webhook triggered successfully!');
})->name('admin.deploy');
