<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleSwitchController extends Controller
{

    public function switchMode(Request $request)
    {
        $mode = $request->input('switch_mode');

        if ($mode === 'monitor') {
            session(['user_mode' => 'monitor']);
            return redirect()->route('monitor.dashboard');
        }

        session(['user_mode' => 'educator']);
        return redirect()->route('educator.dashboard');
    }

    /**
     * Get current user mode
     */
    public function getCurrentMode()
    {
        $user = session('user');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User session not found'
            ], 401);
        }

        // Support both array-based and object-based session payloads
        $role = null;
        $fname = '';
        $lname = '';

        if (is_array($user)) {
            $role = $user['user_role'] ?? null;
            $fname = $user['user_fname'] ?? '';
            $lname = $user['user_lname'] ?? '';
        } else {
            $role = $user->user_role ?? null;
            $fname = $user->user_fname ?? '';
            $lname = $user->user_lname ?? '';
        }

        if ($role !== 'educator') {
            return response()->json([
                'success' => false,
                'message' => 'User does not have mode switching capability'
            ], 403);
        }

        // Default to educator mode if no mode is set in session
        $currentMode = session('user_mode', 'educator');

        return response()->json([
            'success' => true,
            'mode'    => $currentMode,
            'user'    => [
                'name' => trim($fname . ' ' . $lname),
                'role' => $role
            ]
        ]);
    }


    /**
     * Helper method to check if user is in monitor mode
     */
    public static function isInMonitorMode()
    {
        $user = Auth::user();

        if (!$user || $user->user_role !== 'educator') {
            return false;
        }

        return session('user_mode') === 'monitor';
    }

    /**
     * Helper method to get current mode display name
     */
    public static function getCurrentModeDisplay()
    {
        // Check session-based authentication first
        $sessionUser = session('user');

        if ($sessionUser && isset($sessionUser['user_role'])) {
            if ($sessionUser['user_role'] !== 'educator') {
                return ucfirst($sessionUser['user_role']);
            }

            $mode = session('user_mode', 'educator');
            return ucfirst($mode);
        }

        // Fallback to Auth::user() for compatibility
        $user = Auth::user();

        if (!$user || $user->user_role !== 'educator') {
            return ucfirst($user->user_role ?? 'guest');
        }

        $mode = session('user_mode', 'educator');
        return ucfirst($mode);
    }
}
