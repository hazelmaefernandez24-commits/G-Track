<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RequirePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard('admin')->user();

        if ($admin?->must_change_password
            && ! $request->routeIs('account.first-login-password.show', 'account.first-login-password.update')) {
            return redirect()->route('account.first-login-password.show');
        }

        return $next($request);
    }
}
