<?php

namespace App\Providers;

use App\Models\Listing;         // <-- Убедитесь, что эта строка есть
use App\Policies\ListingPolicy; // <-- Убедитесь, что эта строка есть
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        Listing::class => ListingPolicy::class, // <-- ВОТ ПРАВИЛЬНОЕ МЕСТО
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
