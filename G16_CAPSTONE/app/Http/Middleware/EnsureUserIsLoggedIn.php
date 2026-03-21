<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsLoggedIn
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('userid')) { // Match the session key set in AuthController
            return redirect()->route('auth.login');
        }
        return $next($request);
    }
}
