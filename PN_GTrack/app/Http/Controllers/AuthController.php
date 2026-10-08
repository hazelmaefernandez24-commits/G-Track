<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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

    public function updateProfile(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->role === 'education', 403);

        $validated = Validator::make($request->all(), [
            'first_name' => ['required', 'string', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'string', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'string', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => ['required', 'email:rfc', 'unique:admins,email,'.Auth::guard('admin')->id()],
        ], [
            'first_name.regex' => 'First name must contain letters only.',
            'middle_initial.regex' => 'Middle initial must contain letters only.',
            'last_name.regex' => 'Last name must contain letters only.',
            'email.email' => 'Please enter a valid email address.',
        ])->validateWithBag('profile');

        Auth::guard('admin')->user()->update($validated);

        return back()->with('profile_update_status', 'Your profile has been updated successfully.');
    }

    public function changePassword(Request $request)
    {
        abort_unless(Auth::guard('admin')->user()->role === 'education', 403);

        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'confirmed', 'different:current_password'],
        ]);

        $admin = Auth::guard('admin')->user();

        if (! Hash::check($validated['current_password'], $admin->password)) {
            return back()
                ->withErrors(['current_password' => 'The current password is incorrect.'])
                ->withInput();
        }

        $admin->password = Hash::make($validated['new_password']);
        $admin->password_changed_at = now();
        $admin->save();

        return back()->with(
            'password_change_status',
            'Your password has been changed successfully.'
        );
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
        $admin->password_changed_at = now();
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