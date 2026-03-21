<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthControllerpast extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); // Render the login.blade.php view
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = [
            'user_id' => $request->user_id,
            'password' => $request->password,
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors(['user_id' => 'Your account is not active.'])->withInput();
            }

            // Store user info in session if needed
            session(['user_fullname' => $user->fname . ' ' . $user->lname]);

            if (in_array($user->role, ['educator', 'inspector'])) {
                return redirect()->route('dashboard');
            } elseif ($user->role === 'student') {
                return redirect()->route('homepage');
            } else {
                Auth::logout();
                return back()->withErrors(['user_id' => 'Unauthorized role.'])->withInput();
            }
        }

        // If login fails
        return back()->withErrors(['user_id' => 'Invalid credentials'])->withInput();
    }
}
