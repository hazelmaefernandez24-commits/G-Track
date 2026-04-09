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
        $subtab = $request->query('subtab', 'sos');
        $class = $request->query('class', 'all');

        $query = DB::table('notifications');

        // --- FILTER BY TYPE (TAB) ---
        if ($tab === 'sos') {
            $query->whereIn('type', ['sos', 'blackout']);
        } elseif ($tab === 'broadcast') {
            $query->where('type', 'broadcast');
        } else {
            // STUDENT MESSAGES: Show messages from students and admin replies
            $query->whereIn('type', ['student_message', 'admin_reply']);
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
            'sos' => DB::table('notifications')->where('type', 'sos')->where('status', '!=', 'resolved')->count(),
            'blackout' => DB::table('notifications')->where('type', 'blackout')->count(),
        ];

        return view('notifications', [
            'notifications' => $notifications,
            'stats' => $stats,
            'tab' => $tab,
            'subtab' => $subtab,
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
        $type = $request->target;
        if (!in_array($type, ['sos', 'blackout', 'broadcast'])) {
            $type = 'broadcast';
        }

        DB::table('notifications')->insert([
            'student_id' => $request->student_id ?? null,
            'class' => $request->class ?? 'all', 
            'type' => $type,
            'message' => $request->message,
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'battery_level' => $request->battery_level ?? null,
            'signal_status' => $request->signal ?? null,
            'location' => $request->location ?? null,
            'read' => false,
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Notification sent successfully!');
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'message' => 'required',
        ]);

        $parent = DB::table('notifications')->where('id', $id)->first();
        if (!$parent) {
            return redirect()->back()->with('error', 'Message not found.');
        }

        DB::table('notifications')->insert([
            'student_id' => $parent->student_id,
            'class' => $parent->class,
            'type' => 'admin_reply',
            'message' => $request->message,
            'read' => false,
            'status' => 'replied',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Mark original as read/replied
        DB::table('notifications')->where('id', $id)->update(['status' => 'replied', 'read' => true]);

        return redirect()->back()->with('success', 'Reply sent to student!');
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
            'media' => 'nullable|file|mimes:mp4,mov,avi,mp3,wav|max:20480', // 20MB max
        ]);

        $type = $request->target;
        if (!in_array($type, ['sos', 'blackout', 'broadcast', 'student_message'])) {
            $type = 'broadcast';
        }

        $mediaUrl = null;
        if ($request->hasFile('media')) {
            $path = $request->file('media')->store('alerts', 'public');
            $mediaUrl = asset('storage/' . $path);
        }

        $id = DB::table('notifications')->insertGetId([
            'student_id' => $request->student_id ?? null,
            'class' => $request->class ?? 'all', 
            'type' => $type,
            'message' => $request->message,
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'battery_level' => $request->battery_level ?? null,
            'signal_status' => $request->signal ?? null,
            'location' => $request->location ?? null,
            'media_url' => $mediaUrl,
            'read' => false,
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // If it's SOS or Blackout, also update the Student's main status and coordinates
        if ($type === 'sos' || $type === 'blackout') {
            $student = \App\Models\Student::where('student_id', $request->student_id)->orWhere('id', $request->student_id)->first();
            if ($student) {
                if ($request->latitude) $student->latitude = $request->latitude;
                if ($request->longitude) $student->longitude = $request->longitude;
                if ($request->battery_level) $student->battery_level = $request->battery_level;
                if ($request->signal) $student->signal_status = $request->signal;
                if ($type === 'sos') $student->sos_status = 'help';
                $student->last_update = now()->format('M d, Y h:i A');
                $student->save();
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification sent successfully!',
            'notification_id' => $id,
            'media_url' => $mediaUrl
        ]);
    }
}