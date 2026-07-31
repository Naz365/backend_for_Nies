<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/debug-test', function () {
    try {
        $count = \App\Models\Project::count();
        return response()->json([
            'status' => 'success',
            'project_count' => $count,
            'db_connection' => config('database.default'),
            'db_path' => config('database.connections.sqlite.database')
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'error_message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => substr($e->getTraceAsString(), 0, 1000)
        ], 200);
    }
});

Route::get('/admin/deploy', function () {
    \App\Services\DeployWebhookService::triggerDeploy();
    return back()->with('status', 'Static site build webhook triggered successfully!');
})->name('admin.deploy');
