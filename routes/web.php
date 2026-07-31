<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/debug-login', function () {
    try {
        $panel = \Filament\Facades\Filament::getCurrentPanel();
        return response()->json([
            'status' => 'panel_resolved',
            'panel_id' => $panel ? $panel->getId() : 'null',
            'user_count' => \App\Models\User::count()
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 1500)
        ], 200);
    }
});

Route::get('/admin/deploy', function () {
    \App\Services\DeployWebhookService::triggerDeploy();
    return back()->with('status', 'Static site build webhook triggered successfully!');
})->name('admin.deploy');
