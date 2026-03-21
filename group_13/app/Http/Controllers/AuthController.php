<?php

namespace App\Http\Controllers;

use App\Models\PNUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;
use Illuminate\Support\Facades\Auth;

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
        $response = Http::withToken($token)
            ->get(env('MAIN_SYSTEM_URL') . '/api/user');

        if (! $response->successful()) {
            // dd("token invalid");
            // Bad / expired / forged token — back to login
            return redirect(env('MAIN_SYSTEM_URL') . '/')->with('error', 'Invalid token');
        }

        // 4️⃣ Parse the JSON we just got
        $userData = $response->json();
        $user = PNUser::where('user_id', $userData['user_id'])->first();
        // 5️⃣ Make sure they’re actually an educator
        if (isset($userData['user_role'])){
            if ($userData['user_role'] === 'admin') {
                Auth::login($user);
                session(['user_id' => $user->user_id]);
                // dd($user, 'admin');
                return redirect()->route('admin.dashboard');
            }elseif ($userData['user_role'] === 'educator'){
                Auth::login($user);
                session(['user_id' => $user->user_id]);
                // dd($user, 'educator');
                return redirect()->route('educator.dashboard');
            }elseif ($userData['user_role'] === 'training'){
                Auth::login($user);
                session(['user_id' => $user->user_id]);
                // dd($user, 'training');
                return redirect()->route('training.dashboard');
            }elseif ($userData['user_role'] === 'student'){
                Auth::login($user);
                session(['user_id' => $user->user_id]);
                // dd($user, 'student');
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
