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

Route::get('/test-db', function () {
    try {
        $dbPath = config('database.connections.sqlite.database');
        if (!file_exists($dbPath)) {
            return response()->json(['status' => 'missing_file', 'db_path' => $dbPath]);
        }
        $pdo = new \PDO('sqlite:' . $dbPath);
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        
        $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table';")->fetchAll(\PDO::FETCH_COLUMN);
        
        return response()->json([
            'status' => 'success',
            'database_path' => $dbPath,
            'tables' => $tables
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
});

Route::get('/test-projects', function () {
    try {
        $data = \App\Models\Project::all();
        return response()->json(['status' => 'success', 'data' => $data]);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
    }
});

Route::prefix('v1')->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/blog', [BlogPostController::class, 'index']);
    Route::get('/settings', [SiteSettingController::class, 'index']);
    Route::post('/contact', [ContactController::class, 'store']);
});
