<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Number;
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
        // Fallback macro for Number::format when intl extension is not enabled in PHP environment
        Number::macro('format', function ($number, $precision = null, $maxPrecision = null, $locale = null) {
            if (extension_loaded('intl') && class_exists(\NumberFormatter::class)) {
                $formatter = new \NumberFormatter($locale ?? 'en', \NumberFormatter::DECIMAL);
                if ($precision !== null) {
                    $formatter->setAttribute(\NumberFormatter::MIN_FRACTION_DIGITS, $precision);
                    $formatter->setAttribute(\NumberFormatter::MAX_FRACTION_DIGITS, $maxPrecision ?? $precision);
                }
                return $formatter->format($number);
            }

            $decimals = $precision ?? 0;
            return number_format((float) $number, $decimals);
        });

        // Register ContentObserver to trigger GitHub Actions deploy webhook on save/delete
        Project::observe(ContentObserver::class);
        Product::observe(ContentObserver::class);
        BlogPost::observe(ContentObserver::class);
        Customer::observe(ContentObserver::class);
        SiteSetting::observe(ContentObserver::class);
    }
}
