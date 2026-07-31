<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use App\Models\Project;
use App\Models\Product;
use App\Models\BlogPost;
use App\Models\Customer;
use App\Models\SiteSetting;
use App\Observers\ContentObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production' || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        // Register ContentObserver to trigger GitHub Actions deploy webhook on save/delete
        Project::observe(ContentObserver::class);
        Product::observe(ContentObserver::class);
        BlogPost::observe(ContentObserver::class);
        Customer::observe(ContentObserver::class);
        SiteSetting::observe(ContentObserver::class);
    }
}
