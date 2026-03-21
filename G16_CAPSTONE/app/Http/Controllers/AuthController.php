<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1️⃣ Grab the token coming in from ?token=.....
        $token = $request->query('token');

        // If the link was hit with no token at all, show dashboard directly to avoid redirect loop
        if (empty($token)) {
            // For development: show dashboard directly, but log that no token was provided
            \Log::info('Tasking Hub accessed without token from: ' . $request->getClientIp());
            
            // For development: redirect to general task page directly
            return redirect()->route('generalTask');
        }

        // 2️⃣ Keep it in session for later API calls if you need it
        session(['api_token' => $token]);
        \Log::info('Tasking Hub received token: ' . substr($token, 0, 10) . '...');

        // 3️⃣ Ask the main system who this user is
        try {
            $response = Http::timeout(10)->withToken($token)
                ->get('http://localhost:8000/api/user');
        } catch (\Exception $e) {
            \Log::error('Failed to connect to main system: ' . $e->getMessage());
            return response('<h1>Connection Error</h1><p>Could not connect to main login system at localhost:8000</p><p>Please make sure the Login system is running.</p>');
        }

        if (! $response->successful()) {
            // dd("token invalid");
            // Bad / expired / forged token — back to login
            return redirect(env('MAIN_SYSTEM_URL') . '/')->with('error', 'Invalid token');
        }

        // 4️⃣ Parse the JSON we just got
        $userData = $response->json();
        $user = User::where('user_id', $userData['user_id'])->first();
        // 5️⃣ Make sure they're actually an educator
        if (isset($userData['user_role'])){
            if ($userData['user_role'] === 'educator') {
                Auth::login($user);
                return redirect()->route('dashboard');
            }elseif ($userData['user_role'] === 'student') {
                Auth::login($user);
                return redirect()->route('mainstudentdash');
            }elseif ($userData['user_role'] === 'coordinator') {
                Auth::login($user);
                return redirect()->route('mainstudentdash'); // Coordinators use student dashboard
            }elseif ($userData['user_role'] === 'inspector') {
                Auth::login($user);
                return redirect()->route('dashboard');
            }else{
                return redirect()->to(
                    env('MAIN_SYSTEM_URL') . "/main-menu?error=Access+denied&token={$token}"
                );
            }
        }
    }


    public function logout(Request $request)
    {
        $tokenString = session('api_token');

        if ($tokenString) {
            PersonalAccessToken::findToken($tokenString)?->delete();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(env('MAIN_SYSTEM_URL') . '/');
    }
}
