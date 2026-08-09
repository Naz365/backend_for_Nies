<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/health-check', function () {
    return response()->json([
        'status' => 'online',
        'service' => 'N.I. Engineering Platform Backend',
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
    ]);
});

