<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;


class AuthController extends Controller
{
   
    public function showLoginForm()
    {
        return view('login'); 
    }

    
    public function login(Request $request)
{
    $credentials = $request->only('staff_id', 'password');

    if (Auth::guard('admin')->attempt($credentials)) {
        return redirect('/dashboard');
    }

    return back()->withErrors([
        'staff_id' => 'Invalid Staff ID or password',
    ]);
}
    public function showResetPasswordForm()
    {
        return view('reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'staff_id' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $admin = Admin::where('staff_id', $request->staff_id)
            ->where('email', $request->email)
            ->first();

        if (! $admin) {
            return back()
                ->withErrors(['email' => 'The Staff ID and email do not match an account.'])
                ->withInput($request->only('staff_id', 'email'));
        }

        $admin->password = Hash::make($request->password);
        $admin->save();

        return redirect()->route('login')->with(
            'status',
            'Your password was reset successfully. Please log in with your new password.'
        );
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

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
        $credentials = $request->only('staff_id', 'password');

        if (Auth::guard('admin')->attempt($credentials)) {
            $user = Auth::guard('admin')->user();
            return response()->json([
                'message' => 'Login successful',
                'user' => $user,
                'role' => 'admin'
            ]);
        }
        return response()->json(['message' => 'Invalid admin Staff ID or password'], 401);
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