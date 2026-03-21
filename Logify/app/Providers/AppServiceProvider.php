<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Policies\UserPolicy;
use App\Models\PNUser;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;

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
        //dd('hell');
        Gate::define('isAdmin', function ($user) {
            return $user->user_role === 'admin';
        });

        Gate::define('isEducator', function ($user) {
            return $user->user_role === 'educator';
        });

        // Educators automatically have monitor capabilities (merged roles)
        Gate::define('isMonitor', function ($user) {
            return $user->user_role === 'educator';
        });

        Gate::define('isStudent', function ($user) {
            return $user->user_role === 'student';
        });

        // Register Blade components
        Blade::component('components.studentLayout', 'studentLayout');

        // Use custom pagination view
        \Illuminate\Pagination\Paginator::defaultView('custom-pagination');
        \Illuminate\Pagination\Paginator::defaultSimpleView('custom-pagination');
    }
}

