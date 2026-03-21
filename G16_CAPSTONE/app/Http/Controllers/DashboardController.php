<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        // Check if user is authenticated
        if (Auth::check()) {
            $user = Auth::user();
            
            // Redirect based on user role
            switch ($user->user_role) {
                case 'student':
                case 'coordinator':
                    return redirect()->route('mainstudentdash');
                    
                case 'educator':
                case 'inspector':
                    return redirect()->route('generalTask');
                    
                default:
                    return redirect()->to(env('MAIN_SYSTEM_URL') . '/');
            }
        }
        
        // If not authenticated, show a simple dashboard or redirect to login
        return view('welcome');
    }
}
