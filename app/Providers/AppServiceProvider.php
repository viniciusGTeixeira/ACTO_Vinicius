<?php

/**
 * ACTO Maps - App Service Provider
 * 
 * @license MIT
 * @author Kemersson Vinicius Gonçalves Teixeira
 * @date 10/2025
 */

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Rate limiters
        RateLimiter::for('public_api', function (Request $request) {
            return Limit::perMinute(config('app.rate_limit_public_api', 60))
                ->by($request->ip());
        });

        RateLimiter::for('authenticated_api', function (Request $request) {
            return Limit::perMinute(config('app.rate_limit_authenticated_api', 100))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('admin_api', function (Request $request) {
            return Limit::perMinute(config('app.rate_limit_admin_api', 30))
                ->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(config('app.rate_limit_login', 5))
                ->by($request->ip());
        });
    }
}
