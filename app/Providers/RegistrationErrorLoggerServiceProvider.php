<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\RegistrationErrorLogger;

class RegistrationErrorLoggerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RegistrationErrorLogger::class, function ($app) {
            return new RegistrationErrorLogger();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
