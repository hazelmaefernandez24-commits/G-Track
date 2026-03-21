<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Only allow student, finance, and cashier roles
        if (!in_array($role, ['student', 'finance', 'cashier'])) {
            abort(403, 'Unauthorized role.');
        }

        // Check if user is authenticated via session (since this subsystem uses session-based auth)
        $user = session('user');
        if ($user && isset($user['user_role']) && $user['user_role'] === $role) {
            return $next($request);
        }

        abort(403, 'Unauthorized action.');
    }
}
