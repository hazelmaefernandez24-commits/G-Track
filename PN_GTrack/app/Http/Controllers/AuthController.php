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
        $request->validate([
            'student_id' => 'required'
        ]);

        // Assuming mobile app users are students logging in with their student_id (e.g. PN-123)
        $student = \App\Models\Student::where('student_id', $request->student_id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found in the system'], 404);
        }

        return response()->json([
            'message' => 'Login successful',
            'student' => $student
        ]);
    }

    public function apiLogout(Request $request)
    {
        // For tokenless authentication, logging out is just handled on the mobile device by removing user data context.
        return response()->json(['message' => 'Logged out successfully']);
    }
}