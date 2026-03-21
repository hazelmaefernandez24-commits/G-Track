<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CoordinatorOnly
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // Check if user has coordinator role
        $userRole = Auth::user()->user_role;
        $coordinatorRoles = ['educator', 'inspector', 'coordinator'];
        
        if (!in_array($userRole, $coordinatorRoles)) {
            // For AJAX requests, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied: Only coordinators can access this feature.',
                    'coordinator_required' => true,
                    'redirect_url' => route('mainstudentdash')
                ], 403);
            }
            
            // For regular requests, redirect with error message
            return redirect()->route('mainstudentdash')
                ->with('error', 'Access denied: Only coordinators can access the Tasking Report feature.');
        }

        return $next($request);
    }
}
