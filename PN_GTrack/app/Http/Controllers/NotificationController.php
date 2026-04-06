<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        // Change default tab to 'student' to match your Blade logic
        $tab = $request->query('tab', 'student');
        $class = $request->query('class', 'all');

        $query = DB::table('notifications');

        // --- FILTER BY TYPE (TAB) ---
        if ($tab === 'sos') {
            $query->where('type', 'sos');
        } elseif ($tab === 'broadcast') {
            $query->where('type', 'broadcast');
        } else {
            // STUDENT MESSAGES: Show messages that are NOT broadcasts and NOT SOS
            // This ensures your "Student Message" tab stays clean.
            $query->whereNotIn('type', ['broadcast', 'sos']);
        }

        // --- FILTER BY CLASS ---
        if ($class !== 'all') {
            $query->where(function ($q) use ($class) {
                $q->where('class', $class)
                  ->orWhere('class', 'all'); 
            });
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        // --- STATS LOGIC ---
        $stats = [
            'total' => DB::table('notifications')->count(),
            'unread' => DB::table('notifications')->where('read', false)->count(),
            'sos' => DB::table('notifications')->where('type', 'sos')->count(),
        ];

        return view('notifications', [
            'notifications' => $notifications,
            'stats' => $stats,
            'tab' => $tab,
            'class' => $class
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'message' => 'required',
        ]);

        // Logic to determine type
        $type = $request->target === 'sos' ? 'sos' : 'broadcast';

        DB::table('notifications')->insert([
            'student_id' => null,
            'class' => $request->class ?? 'all', 
            'type' => $type,
            'message' => $request->message,
            'read' => false,
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Notification sent successfully!');
    }

    // --- MOBILE API METHODS ---
    public function apiGet($student_id)
    {
        // The mobile app will pass the student's ID, fetch their specific notifications
        $student = \App\Models\Student::where('id', $student_id)->orWhere('student_id', $student_id)->first();
        
        $query = DB::table('notifications')->orderBy('created_at', 'desc');

        if ($student) {
            $query->where(function($q) use ($student) {
                $q->where('class', 'all')
                  ->orWhere('class', $student->class)
                  ->orWhere('student_id', $student->id);
            });
        }

        $notifications = $query->get();

        return response()->json([
            'success' => true,
            'notifications' => $notifications
        ]);
    }

    public function apiSend(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'message' => 'required',
        ]);

        $type = $request->target === 'sos' ? 'sos' : 'broadcast';

        $id = DB::table('notifications')->insertGetId([
            'student_id' => $request->student_id ?? null,
            'class' => $request->class ?? 'all', 
            'type' => $type,
            'message' => $request->message,
            'read' => false,
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully!',
            'notification_id' => $id
        ]);
    }
}