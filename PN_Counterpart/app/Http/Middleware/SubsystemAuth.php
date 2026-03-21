<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;

class SubsystemAuth
{
    public function handle($request, Closure $next)
    {
        // Block if token missing
        if (! Session::has('api_token')) {
            return redirect('/login')->with('error', 'Please log in through the Main System');
        }
        return $next($request);
    }
}
