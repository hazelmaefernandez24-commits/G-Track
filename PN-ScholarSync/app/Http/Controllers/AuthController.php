<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    public function dashboard(Request $request)
    {
        // 1️⃣ Grab the token coming in from ?token=.....
        $token = $request->query('token');

        // If the link was hit with no token at all, bail out early
        if (empty($token)) {
            // dd("token empty");
            return redirect(env('MAIN_SYSTEM_URL') . '/')->with('error', 'Token missing');
        }

        // 2️⃣ Keep it in session for later API calls if you need it
        session(['api_token' => $token]);

        // 3️⃣ Ask the main system who this user is
        $mainSystemUrl = env('MAIN_SYSTEM_URL');
        $fullUrl = $mainSystemUrl . '/api/user';

        // Debug logging
        \Log::info('AuthController debug', [
            'main_system_url' => $mainSystemUrl,
            'full_url' => $fullUrl,
            'token_length' => strlen($token)
        ]);

        $response = Http::withToken($token)
            ->get($fullUrl);

        if (! $response->successful()) {
            // dd("token invalid");
            // Bad / expired / forged token — back to login
            return redirect(env('MAIN_SYSTEM_URL') . '/')->with('error', 'Invalid token');
        }

        // 4️⃣ Parse the JSON we just got
        $userData = $response->json();
        $user = User::where('user_id', $userData['user_id'])->first();
        // 5️⃣ Make sure they’re actually an educator
        if (isset($userData['user_role'])){
            if ($userData['user_role'] === 'educator') {
                // dd("educator");
                session(['user' => $userData]);
                Auth::login($user);
                return redirect()->route('educator.dashboard');
            }elseif ($userData['user_role'] === 'student') {
                session(['user' => $userData]);
                Auth::login($user);
                return redirect()->route('student.dashboard');
            }else{
                return redirect()->to(
                    env('MAIN_SYSTEM_URL') . "/main-menu?error=Access+denied&token={$token}"
                );
            }
        }
    }


    public function logout(Request $request)
    {
        $tokenString = session('api_token');          // the plain text token
        PersonalAccessToken::findToken($tokenString)?->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to(env('MAIN_SYSTEM_URL') . '/');
    }
}
