<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Force HTTPS scheme for generated URLs when requested by environment
        // or explicitly via the FORCE_HTTPS env variable. This helps avoid
        // Mixed Content errors when the app is served over HTTPS but some
        // generated links were accidentally prefixed with http://
        $forceHttpsEnv = filter_var(env('FORCE_HTTPS', false), FILTER_VALIDATE_BOOLEAN);

        if ($this->app->environment('production') || $forceHttpsEnv) {
            URL::forceScheme('https');
        }
    }
}
