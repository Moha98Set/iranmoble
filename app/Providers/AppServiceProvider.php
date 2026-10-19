<?php

namespace App\Providers;

use BezhanSalleh\FilamentShield\Facades\FilamentShield;
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
        /*
         * Spatie's Role model lives in the package, so Laravel's convention
         * based policy discovery never finds RolePolicy. Without this the
         * role matrix would be reachable by anyone who can open the panel.
         */
        FilamentShield::enforcePolicies();
    }
}
