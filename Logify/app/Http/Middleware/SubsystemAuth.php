<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Session;

class SubsystemAuth
{
    public function handle($request, Closure $next)
    {
        // dd(Session::has('api_token'));
        // Block if token missing
        if (! Session::has('api_token')) {
            return redirect('/login')->with('error', 'Please log in through the Main System');
        }

        return $next($request);
    }
}
