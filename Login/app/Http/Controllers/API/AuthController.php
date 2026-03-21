<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\PNUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Laravel\Sanctum\PersonalAccessToken;


class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! PNUser::where('user_id', $value)->exists()) {
                        $fail('User not found.');
                    }
                },
            ],

            'password' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    $user = PNUser::where('user_id', $request->input('user_id'))->first();

                    if (! $user || ! Hash::check($value, $user->user_password)) {
                        $fail('Incorrect password.');
                    }
                },
            ],
        ]);

        $user = PNUser::where('user_id', $request->user_id)->first();

        $token = $user->createToken('subsystem-token')->plainTextToken;
        if ($user->is_temp_password) {
            return redirect()->route('change-password', ['token' => $token]);
        }

        if (! $user) {
            return redirect()->back()->withErrors([
                'user_id' => 'User not found.',
            ])->withInput();
        }

        if (! Hash::check($request->password, $user->user_password)) {
            return redirect()->back()->withErrors([
                'password' => 'Wrong password!',
            ])->withInput();
        }

        return redirect()->route('main-menu', ['token' => $token, 'user_role' => $user->user_role]);
    }

    public function logout(Request $request)
    {
        $tokenString = $request->input('token');

        if ($tokenString) {
            PersonalAccessToken::findToken($tokenString)?->delete();
        }

        return redirect('/');
    }

    public function verifyForgotPassword(Request $request)
    {
        $request->validate([
            'user_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    if (! PNUser::where('user_id', $value)->exists()) {
                        $fail('User not found.');
                    }
                },
            ],
            'email' => [
                'required',
                'email',
                function ($attribute, $value, $fail) use ($request) {
                    $user = PNUser::where('user_id', $request->input('user_id'))->first();
                    if (!$user || $user->user_email !== $value) {
                        $fail('Email does not match this account.');
                    }
                },
            ],
        ]);

        $user = PNUser::where('user_id', $request->user_id)
            ->where('user_email', $request->email)
            ->first();

        if (!$user) {
            return back()->withErrors(['error' => 'User ID or Email does not match our records.']);
        }

        session(['success' => true, 'user_id' => $user->user_id]);

        return redirect()->route('reset-password')->with('success', 'You can now change your password.');
    }

    public function showResetPasswordForm()
    {
        return view('reset-password');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|confirmed|min:8',
        ]);

        $user = PNUser::where('user_id', session('user_id'))->first();

        if (!$user) {
            return redirect()->route('forgot-password')->withErrors(['error' => 'User not found.']);
        }

        $user->update([
            'user_password' => Hash::make($request->new_password),
            'is_temp_password' => false,
        ]);

        session()->forget(['success', 'user_id']);

        return redirect()->route('login')->with('success', 'Your password has been updated successfully. Please log in.');
    }

    public function showChangePasswordForm(Request $request)
    {
        $token = $request->query('token');

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            abort(401, 'Unauthorized');
        }

        $user = $accessToken->tokenable;

        if (!$token) {
            return redirect()->route('login')->with('error', 'Token missing');
        }
        return view('change-password', compact('token'));
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'current_password' => 'required',
            'new_password' => 'required|confirmed|min:8',
        ]);

        $token = $request->input('token');
        $accessToken = PersonalAccessToken::findToken($token);
        if (!$accessToken) {
            return redirect()->route('login')->with('error', 'Unauthorized');
        }

        $user = $accessToken->tokenable;

        if (!Hash::check($request->current_password, $user->user_password)) {
            return back()->withErrors(['current_password' => 'The current password is incorrect']);
        }

        $user->update([
            'user_password' => Hash::make($request->new_password),
            'is_temp_password' => false,
        ]);

        return redirect()->route('main-menu', ['token' => $token]);
    }
}
