<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        Model::preventLazyLoading(! app()->isProduction());

        if (config('campus.force_https')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('registration', fn (Request $request): Limit => Limit::perMinute(5)->by('register:'.$request->ip()));
        RateLimiter::for('profession-search', fn (Request $request): Limit => Limit::perMinute(30)->by('profession:'.$request->ip()));
        RateLimiter::for('game-write', fn (Request $request): array => [
            Limit::perMinute(60)->by('game-user:'.($request->user()?->id ?? $request->ip())),
            Limit::perMinute(120)->by('game-ip:'.$request->ip()),
        ]);

        Vite::prefetch(concurrency: 3);
    }
}
