<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AdminManagementController extends Controller
{
    public function index()
    {
        $admins = Admin::all();
        return view('admin.admins.index', compact('admins'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => 'required|unique:admins,staff_id',
            'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => 'required|email:rfc|unique:admins,email',
            'password' => 'required|min:6|confirmed',
            'role' => 'required|in:education,main',
        ], [
            'first_name.regex' => 'First name must contain letters only.',
            'middle_initial.regex' => 'Middle initial must contain letters only.',
            'last_name.regex' => 'Last name must contain letters only.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        Admin::create([
            'staff_id' => $request->staff_id,
            'first_name' => $request->first_name,
            'middle_initial' => $request->middle_initial,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        return redirect()->back()->with('success', 'Admin added successfully.');
    }

    public function update(Request $request, $id)
    {
        $admin = Admin::findOrFail($id);

        $request->validate([
            'staff_id' => 'required|unique:admins,staff_id,' . $id,
            'first_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'middle_initial' => ['nullable', 'regex:/^[A-Za-z]+$/u', 'max:1'],
            'last_name' => ['required', 'regex:/^[A-Za-z .\'-]+$/u'],
            'email' => 'required|email:rfc|unique:admins,email,' . $id,
            'role' => 'required|in:education,main',
            'current_password' => 'nullable|required_with:new_password|min:6',
            'new_password' => 'nullable|min:6|confirmed',
        ], [
            'first_name.regex' => 'First name must contain letters only.',
            'middle_initial.regex' => 'Middle initial must contain letters only.',
            'last_name.regex' => 'Last name must contain letters only.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        if ($request->filled('new_password')) {
            if (! $request->filled('current_password')) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is required to change password.'])->withInput();
            }

            if (! Hash::check($request->current_password, $admin->password)) {
                return redirect()->back()->withErrors(['current_password' => 'Current password is incorrect.'])->withInput();
            }
        }

        $admin->update($request->only(['staff_id', 'first_name', 'middle_initial', 'last_name', 'email', 'role']));

        if ($request->filled('new_password')) {
            $admin->update(['password' => Hash::make($request->new_password)]);
        }

        return redirect()->back()->with('success', 'Admin updated successfully.');
    }

    public function destroy($id)
    {
        $admin = Admin::findOrFail($id);
        
        // Prevent deleting yourself
        if ($admin->id === Auth::guard('admin')->id()) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $admin->delete();

        return redirect()->back()->with('success', 'Admin deleted successfully.');
    }
}
