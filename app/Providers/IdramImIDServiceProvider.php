<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\IdramImIDService;

class IdramImIDServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(IdramImIDService::class, function ($app) {
            return new IdramImIDService();
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
