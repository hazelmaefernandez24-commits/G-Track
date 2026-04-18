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

        // Normalize class name (e.g., 'Class 2026' -> '2026')
        $dbClass = $class;
        if (str_starts_with($class, 'Class ')) {
            $dbClass = str_replace('Class ', '', $class);
        }

        $query = \App\Models\Notification::with(['replies', 'student']);

        // --- FILTER BY TYPE (TAB) ---
        if ($tab === 'sos') {
            $query->whereIn('type', ['sos', 'blackout']);
        } elseif ($tab === 'broadcast') {
            $query->where('type', 'broadcast');
        } else {
            // STUDENT MESSAGES: Fetch general conversations only
            // Exclude SOS/Blackout as they belong in the 'Emergency Alerts' tab
            $query->whereNotIn('type', ['sos', 'blackout'])
                  ->where(function($q) {
                      $q->where('type', 'student_message')
                        ->orWhere('sender_type', 'student');
                  })->whereNull('parent_id');
        }

        // --- FILTER BY CLASS ---
        if ($class !== 'all') {
            $query->where(function ($q) use ($dbClass) {
                $q->where('class', $dbClass)
                  ->orWhere('class', 'all') // Include global broadcasts
                  ->orWhereHas('student', function ($sq) use ($dbClass) {
                      $sq->where('class', $dbClass);
                  });
            });
        }

        $notifications = $query->orderBy('created_at', 'desc')->get();

        // --- STATS LOGIC ---
        $students = \App\Models\Student::all();
        $latestUpdate   = \App\Models\Student::max('updated_at');
        $latestTime     = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null;
        $latestDate     = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null;

        $stats = [
            'unread' => \App\Models\Notification::where('read', false)->count(),
            'sos' => \App\Models\Notification::where('type', 'sos')->where('status', '!=', 'resolved')->count(),
            'broadcast' => \App\Models\Notification::where('type', 'broadcast')->count(),
            'onlineCount' => $students->where('status', true)->count(),
            'offlineCount' => $students->where('status', false)->count(),
            'blackout' => \App\Models\Notification::where('type', 'blackout')->count(),
            'latestTime' => $latestTime,
            'latestDate' => $latestDate
        ];

        return view('notifications', [
            'notifications' => $notifications,
            'stats' => $stats,
            'tab' => $tab,
            'subtab' => $subtab,
            'class' => $class,
            'dbClass' => $dbClass
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'message' => 'required',
        ]);

        $target = $request->target;
        $type = 'broadcast';
        $studentClass = 'all';
        $studentId = null;

        // Determine if target is a class or a specific student (future proofing)
        if ($target === 'all') {
            $studentClass = 'all';
        } elseif (in_array($target, ['2026', '2027', '2028'])) {
            $studentClass = $target;
        } else {
            // Assume it might be a student_id if it's not a known class
            $studentId = $target;
            $student = \App\Models\Student::where('student_id', $target)->orWhere('id', $target)->first();
            if ($student) {
                $studentClass = $student->class;
            }
        }

        DB::table('notifications')->insert([
            'student_id' => $studentId,
            'class' => $studentClass, 
            'type' => $type,
            'sender_type' => 'admin',
            'message' => $request->message,
            'read' => false,
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Broadcast notification sent successfully!');
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
            'sender_type' => 'admin',
            'parent_id' => $id,
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

    public function acknowledge($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);
        $notification->update(['read' => true]);

        return redirect()->back()->with('success', 'Alert acknowledged.');
    }

    public function resolve($id)
    {
        $notification = \App\Models\Notification::find($id);
        if ($notification) {
            $notification->update([
                'status' => 'resolved',
                'read' => true
            ]);

            // If it's a student SOS, mark the student as safe
            if ($notification->student_id) {
                $student = \App\Models\Student::where('student_id', $notification->student_id)
                    ->orWhere('id', $notification->student_id)
                    ->first();
                if ($student) {
                    $student->sos_status = 'safe';
                    $student->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Alert marked as Resolved (Safe).');
    }

    public function read($id)
    {
        $notification = \App\Models\Notification::findOrFail($id);
        $notification->update(['read' => true]);

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    // --- MOBILE API METHODS ---
    public function apiGet($student_id)
    {
        $student = \App\Models\Student::where('id', $student_id)
            ->orWhere('student_id', $student_id)
            ->first();
        
        // If student is not found, return an empty list or error
        if (!$student) {
            return response()->json([
                'success' => false,
                'message' => 'Student not found',
                'notifications' => []
            ], 404);
        }

        $notifications = DB::table('notifications')
            ->where(function($q) use ($student) {
                // 1. Global broadcasts
                $q->where('class', 'all')
                // 2. Class-specific broadcasts
                  ->orWhere('class', $student->class)
                // 3. Direct messages to this student ID
                  ->orWhere('student_id', $student->student_id)
                  ->orWhere('student_id', $student->id);
            })
            // Only show broadcasts or messages, hide system/admin alerts like blackout
            ->where('type', '!=', 'blackout')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'student' => [
                'name' => $student->name,
                'class' => $student->class
            ],
            'notifications' => $notifications
        ]);
    }

    public function apiSend(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'target' => 'required|in:sos,blackout,broadcast,student_message',
            'message' => 'required',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'battery_level' => 'nullable|integer|between:0,100',
            'signal' => 'nullable|string',
            'location' => 'nullable|string',
            'media' => 'nullable|file|mimes:mp4,mov,avi,mp3,wav,jpg,png,jpeg|max:20480', // 20MB max
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:20480',
            'audio' => 'nullable|file|mimes:mp3,wav|max:20480',
        ]);

        $student = \App\Models\Student::where('student_id', $request->student_id)
            ->orWhere('id', $request->student_id)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Invalid Student ID'], 404);
        }

        $type = $request->target;

        // Auto-categorization for Blackout alerts
        $lowBatteryKeywords = ['low on battery', 'battery is low', 'running low on battery', 'main gate'];
        $messageLower = strtolower($request->message);
        foreach ($lowBatteryKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                $type = 'blackout';
                break;
            }
        }

        $mediaUrl = $request->hasFile('media') ? asset('storage/' . $request->file('media')->store('alerts', 'public')) : null;
        $videoUrl = $request->hasFile('video') ? asset('storage/' . $request->file('video')->store('alerts', 'public')) : null;
        $audioUrl = $request->hasFile('audio') ? asset('storage/' . $request->file('audio')->store('alerts', 'public')) : null;

        $id = DB::table('notifications')->insertGetId([
            'student_id' => $student->id, // Use numeric ID for the relationship
            'class' => $student->class, 
            'type' => $type,
            'sender_type' => 'student',
            'message' => $request->message,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'battery_level' => $request->battery_level,
            'signal_status' => $request->signal,
            'location' => $request->location,
            'media_url' => $mediaUrl,
            'video_url' => $videoUrl,
            'audio_url' => $audioUrl,
            'read' => false,
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // If it's SOS or Blackout, update the Student's real-time map status
        if ($type === 'sos' || $type === 'blackout') {
            if ($request->latitude) $student->latitude = $request->latitude;
            if ($request->longitude) $student->longitude = $request->longitude;
            if ($request->battery_level) $student->battery_level = $request->battery_level;
            if ($request->signal) $student->signal_status = $request->signal;
            if ($type === 'sos') $student->sos_status = 'help';
            
            $student->last_update = now()->format('M d, Y h:i A');
            $student->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Emergency alert received by Admin!',
            'notification_id' => $id
        ]);
    }
}
