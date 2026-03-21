<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use App\Models\PNUser;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();
        $request->session()->regenerate();

        $user = Auth::user();

        if (Gate::allows('isEducator')) {
            // Educators have merged educator/monitor roles - always go to educator dashboard
            // They can switch to monitor mode using the header switch
            Log::info('Educator logged in (with monitor capabilities).', [
                'user_id' => $user->id,
                'user_role' => $user->user_role,
            ]);

            // Clear any existing mode session and default to educator mode
            session(['user_mode' => 'educator']);

            return redirect()->route('educator.dashboard');
        }

        Log::warning('Unauthorized role access attempted.', [
            'user_id' => $user->id,
            'user_role' => $user->user_role,
        ]);
        Auth::logout();
        return redirect()->route('login')->withErrors(['role' => 'Unauthorized role access.']);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();
        Log::info('User logged out.', [
            'user_id' => $user ? $user->id : null,
            'user_role' => $user ? $user->user_role : null,
        ]);

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
