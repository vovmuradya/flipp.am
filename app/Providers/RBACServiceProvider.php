<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\RBACService;

class RBACServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(RBACService::class, function ($app) {
            return new RBACService();
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
