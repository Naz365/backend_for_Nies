<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/status-check');
});

Route::get('/status-check', function () {
    try {
        return response()->json([
            'status' => 'online',
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database' => \App\Models\Project::count() . ' projects',
            'session_driver' => config('session.driver'),
            'cache_driver' => config('cache.default')
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

Route::get('/admin/deploy', function () {
    \App\Services\DeployWebhookService::triggerDeploy();
    return back()->with('status', 'Static site build webhook triggered successfully!');
})->name('admin.deploy');
