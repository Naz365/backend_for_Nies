<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/admin-login-test', function () {
    try {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        $authUrl = $panel ? $panel->getLoginUrl() : 'no_panel';
        return response()->json([
            'status' => 'success',
            'panel_id' => $panel ? $panel->getId() : null,
            'login_url' => $authUrl,
            'user_count' => \App\Models\User::count()
        ]);
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
