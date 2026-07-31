<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/test-render-login', function () {
    try {
        $view = view('filament-panels::pages.auth.login')->render();
        return response($view);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'exception_class' => get_class($e),
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 1500)
        ]);
    }
});

Route::get('/admin/deploy', function () {
    \App\Services\DeployWebhookService::triggerDeploy();
    return back()->with('status', 'Static site build webhook triggered successfully!');
})->name('admin.deploy');
