<?php

namespace ChrisRhymes\LinkChecker;

use Illuminate\Support\ServiceProvider as SupportServiceProvider;

class ServiceProvider extends SupportServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/link-checker.php' => config_path('link-checker.php'),
        ]);
    }

    public function register()
    {

    }
}