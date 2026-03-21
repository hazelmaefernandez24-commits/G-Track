<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\PNUser;



class AuthControllerpast extends Controller
{
    // Show the login form
    public function showLoginForm()
    {
        return view('login');
    }



    // Handle login
    public function login(Request $request)
    {
        // Validate the login input
        $request->validate([
            'user_id' => 'required',
            'password' => 'required',
        ]);

        // Find the user by user_id
        $user = PNUser::where('user_id', $request->user_id)->first();

        // Check if the user exists and the password matches
        if (!$user || !Hash::check($request->password, $user->user_password)) {
            return back()->withErrors(['error' => 'Invalid User ID or Password']);
        }


        // Check if the user is active
        if ($user->status !== 'active') {
            return back()->withErrors(['error' => 'Your account has been deactivated. Please contact the administrator.']);
        }


        Auth::login($user);
        session(['user_id' => $user->user_id]);
        // Check if the password is temporary
        if ($user->is_temp_password) {
            return redirect()->route('change-password');
        }

        //Role based
        switch ($user->user_role) {
            case 'Admin':
                return redirect()->route('admin.dashboard');
            case 'Training':
                return redirect()->route('training.dashboard');
            case 'Educator':
                return redirect()->route('educator.dashboard');
            case 'Student':
                return redirect()->route('student.dashboard');
            default:
                Auth::logout();
                return redirect()->route('login')->withErrors(['error' => 'Unauthorized role.']);
        }
    }

    // Handle logout
    public function logout()
    {
        Auth::logout();
        return redirect()->route('main-menu');
    }

    // Show the change password form
    public function showChangePasswordForm()
    {
        return view('change-password');
    }



    // Handle password update
    public function updatePassword(Request $request)
    {
        // Validate the input
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);



        $user = Auth::user();

        // Check if the current password matches
        if (!Hash::check($request->current_password, $user->user_password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect']);
        }

        // Update the password
        $user->update([
            'user_password' => Hash::make($request->new_password),
            'is_temp_password' => false, // Mark the password as no longer temporary
        ]);



        // Redirect based on role
        switch ($user->user_role) {
            case 'Admin':
                return redirect()->route('admin.dashboard')->with('success', 'Password updated successfully.');
            case 'Training':
                return redirect()->route('training.dashboard')->with('success', 'Password updated successfully.');
            case 'Educator':
                return redirect()->route('educator.dashboard')->with('success', 'Password updated successfully.');
            case 'Student':
                return redirect()->route('student.dashboard')->with('success', 'Password updated successfully.');
            default:
                Auth::logout();
                return redirect()->route('login')->withErrors(['error' => 'Unauthorized role.']);
        }

}

    //Handle forgot password

    public function verifyForgotPassword(Request $request)
{
    // Validate the input
    $request->validate([
        'user_id' => 'required',
        'email' => 'required|email',
    ]);

    // Check if the user exists with the given user ID and email
    $user = PNUser::where('user_id', $request->user_id)
                  ->where('user_email', $request->email)
                  ->first();

    if (!$user) {
        // If no match is found, redirect back with an error
        return back()->withErrors(['error' => 'User ID or Email does not match our records.']);
    }

     // Set a persistent session variable to allow access to the change password page
     session(['success' => true, 'user_id' => $user->user_id]);


    // If the user is found, redirect to the change password page
    return redirect()->route('reset-password')->with('success', 'You can now change your password.');
}



//Shows the reset password form
public function showResetPasswordForm()
{

    return view('reset-password'); // new view just for reset
}




// Handle the password reset
public function resetPassword(Request $request)
{
    // Validate the input
    $request->validate([
        'new_password' => 'required|confirmed|min:8',
    ]);

    // Get the user from the session
    $user = PNUser::where('user_id', session('user_id'))->first();

    if (!$user) {
        return redirect()->route('forgot-password')->withErrors(['error' => 'User not found.']);
    }

    // Update the password
    $user->update([
        'user_password' => Hash::make($request->new_password),
        'is_temp_password' => false, // Mark the password as no longer temporary
    ]);

    // Clear the session variables
    session()->forget(['success', 'user_id']);

    // Redirect to the login page with a success message
    return redirect()->route('login')->with('success', 'Your password has been updated successfully. Please log in.');
}


}
