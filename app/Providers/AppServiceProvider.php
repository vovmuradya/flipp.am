<?php

namespace App\Providers;

use App\Models\Listing;         // <-- Добавьте эту строку
use App\Policies\ListingPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Config;

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
        // Configure database connection for Railway environment
        if (getenv('RAILWAY_ENVIRONMENT')) {
            Config::set('database.connections.mysql.host', getenv('MYSQLHOST') ?: Config::get('database.connections.mysql.host'));
            Config::set('database.connections.mysql.port', getenv('MYSQLPORT') ?: Config::get('database.connections.mysql.port'));
            Config::set('database.connections.mysql.database', getenv('MYSQLDATABASE') ?: Config::get('database.connections.mysql.database'));
            Config::set('database.connections.mysql.username', getenv('MYSQLUSER') ?: Config::get('database.connections.mysql.username'));
            Config::set('database.connections.mysql.password', getenv('MYSQLPASSWORD') ?: Config::get('database.connections.mysql.password'));

            Config::set('database.default', 'mysql');
        }
    }
}
