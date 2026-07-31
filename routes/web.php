<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/simple-web-test', function () {
    try {
        return response()->json([
            'status' => 'ok',
            'session_id' => session()->getId(),
            'app_key_exists' => !empty(config('app.key'))
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ]);
    }
});

Route::get('/admin/deploy', function () {
    \App\Services\DeployWebhookService::triggerDeploy();
    return back()->with('status', 'Static site build webhook triggered successfully!');
})->name('admin.deploy');
