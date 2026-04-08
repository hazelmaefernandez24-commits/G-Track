<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class AuthController extends Controller
{
   
    public function showLoginForm()
    {
        return view('login'); 
    }

    
  public function login(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        return redirect('/dashboard');
    }

    return back()->withErrors([
        'email' => 'Invalid email or password',
    ]);
}
    
    public function logout(Request $request)
    {
        Auth::logout();

        // Invalidate session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Redirect to login page
        return redirect('/login');
    }

    // --- MOBILE API METHODS ---
    public function apiLogin(Request $request)
{
    // 1. If the mobile app says the user is an 'admin'
    if ($request->role === 'admin') {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'role' => 'admin'
            ]);
        }
        return response()->json(['message' => 'Invalid admin email or password'], 401);
    }

    // Student authentication is now handled by StudentController@apiLogin
    // Mobile apps should use the `/api/student/login` endpoint
    return response()->json(['message' => 'Please use the correct endpoint for student login'], 400);
}

    public function apiLogout(Request $request)
    {
        // For tokenless authentication, logging out is just handled on the mobile device by removing user data context.
        return response()->json(['message' => 'Logged out successfully']);
    }
}