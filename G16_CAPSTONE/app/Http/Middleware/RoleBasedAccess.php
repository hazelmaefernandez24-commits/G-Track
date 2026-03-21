<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleBasedAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $allowedRoles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$allowedRoles)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $userRole = $user->user_role;

        // Check if user's role is in the allowed roles
        if (!in_array($userRole, $allowedRoles)) {
            // Redirect based on user's actual role
            if ($userRole === 'student') {
                return redirect()->route('mainstudentdash')->with('error', 'Access denied. Students can only access the student dashboard.');
            } elseif (in_array($userRole, ['educator', 'inspector'])) {
                return redirect()->route('dashboard')->with('error', 'Access denied. Admin users can only access the admin dashboard.');
            } else {
                // Unknown role, logout and redirect to login
                Auth::logout();
                return redirect()->route('login')->with('error', 'Invalid user role.');
            }
        }

        return $next($request);
    }
}
