<?php

namespace ChrisRhymes\LinkChecker;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/link-checker.php' => config_path('link-checker.php'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/link-checker.php', 'link-checker');

        RateLimiter::for('link-checker', function ($job) {
            return Limit::perMinute(config('link-checker.rate_limit', 5))
                ->by(parse_url($job->link->url, PHP_URL_HOST));
        });
    }

    public function register()
    {
    }
}
