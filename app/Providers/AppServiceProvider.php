<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiter;

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
        $rateLimiter = app(RateLimiter::class);
        $rateLimiter->for('api', function ($rateLimiter) {
            $rateLimiter->hit('api-ip:' . request()->ip(), 60);
        });
    }
}
