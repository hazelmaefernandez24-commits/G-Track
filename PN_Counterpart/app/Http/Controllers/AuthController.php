<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function dashboard(Request $request)
    {

        // 1️⃣ Grab the token coming in from ?token=.....
        $token = $request->query('token');

        // If the link was hit with no token at all, bail out early
        if (empty($token)) {
            // dd("token empty");
            return redirect(env('MAIN_SYSTEM_URL') . '/login')->with('error', 'Token missing');
        }

        // 2️⃣ Keep it in session for later API calls if you need it
        session(['api_token' => $token]);

        // 3️⃣ Ask the main system who this user is
        $response = Http::withToken($token)
            ->get(env('MAIN_SYSTEM_URL') . '/api/user');

        if (! $response->successful()) {
            // dd("token invalid");
            // Bad / expired / forged token — back to login
            return redirect(env('MAIN_SYSTEM_URL') . '/login')->with('error', 'Invalid token');
        }

        // 4️⃣ Parse the JSON we just got
        $userData = $response->json();

        // ✅ Check role
        if (isset($userData['user_role'])){
            if ($userData['user_role'] === 'student') {
                session(['user' => $userData]);
                return redirect()->route('student.studentDashboard');
            }elseif ($userData['user_role'] === 'finance') {
                session(['user' => $userData]);
                // dd($userData);
                return redirect()->route('finance.financeDashboard');
            }elseif ($userData['user_role'] === 'cashier') {
                session(['user' => $userData]);
                return redirect()->route('cashier.dashboard');
            }else{
                return redirect()->back()->withErrors(['error' => 'Access denied'], 403);
            }
        }
    }


    public function logout(Request $request)
    {
        // Erase session data
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(env('MAIN_SYSTEM_URL'). '/');
    }
}