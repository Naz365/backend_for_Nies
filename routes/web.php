<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/admin/deploy', function () {
    \App\Services\DeployWebhookService::triggerDeploy();
    return back()->with('status', 'Static site build webhook triggered successfully!');
})->name('admin.deploy');
