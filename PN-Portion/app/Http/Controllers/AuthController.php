<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    public function dashboard(Request $request)
    {
        $token = $request->query('token');
        if (empty($token)) {
            return redirect(env('MAIN_SYSTEM_URL') . '/')->with('error', 'Token missing');
        }
        // session(['api_token' => $token]);
        $response = Http::withToken($token)
            ->get(env('MAIN_SYSTEM_URL') . '/api/user');

        if (! $response->successful()) {
            return redirect(env('MAIN_SYSTEM_URL') . '/')->with('error', 'Invalid token');
        }

        $userData = User::where('user_email', $response['user_email'])->first();

        if (isset($userData['user_role'])){
            if ($userData['user_role'] === 'cook') {
                Auth::login($userData);
                return redirect()->route('cook.dashboard');
            }elseif ($userData['user_role'] === 'student'){
                Auth::login($userData);
                return redirect()->route('student.dashboard');
            }elseif ($userData['user_role'] === 'kitchen'){
                Auth::login($userData);
                return redirect()->route('kitchen.dashboard');
            }else{
                    return redirect()->to(
                    env('MAIN_SYSTEM_URL') . "/main-menu?error=Access+denied&token={$token}"
                );
            }
        }
        // session(['user' => $userData]);
        return redirect()->route('educator.dashboard');
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
