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
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|min:6',
            'role' => 'required|in:education,main',
        ]);

        Admin::create([
            'staff_id' => $request->staff_id,
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
            'email' => 'required|email|unique:admins,email,' . $id,
            'role' => 'required|in:education,main',
        ]);

        $admin->update($request->only(['staff_id', 'email', 'role']));

        if ($request->filled('password')) {
            $admin->update(['password' => Hash::make($request->password)]);
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
