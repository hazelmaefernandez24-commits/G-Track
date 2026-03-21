<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordReset
{
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated via session (since this subsystem uses session-based auth)
        $user = session('user');
        if ($user && isset($user['password_reset_required']) && !$user['password_reset_required']) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
