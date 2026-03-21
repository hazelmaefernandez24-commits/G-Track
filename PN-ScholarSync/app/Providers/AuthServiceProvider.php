<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('access-admin', function ($user) {
            return $user->user_role === 'admin';
        });

        Gate::define('access-educator', function ($user) {
            return $user->user_role === 'educator';
        });

        Gate::define('access-student', function ($user) {
            return $user->user_role === 'student';
        });

        // Add more specific permissions as needed
        Gate::define('manage-users', function ($user) {
            return $user->user_role === 'admin';
        });

        Gate::define('manage-courses', function ($user) {
            return in_array($user->user_role, ['admin', 'educator']);
        });
    }
}
