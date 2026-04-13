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

        // Logic to determine type
        $type = $request->target;
        if (!in_array($type, ['sos', 'blackout', 'broadcast'])) {
            $type = 'broadcast';
        }

        // Determine class automatically if student_id is provided
        $studentClass = $request->class ?? 'all';
        if ($request->student_id && $studentClass === 'all') {
            $targetStudent = \App\Models\Student::where('id', $request->student_id)
                ->orWhere('student_id', $request->student_id)
                ->first();
            if ($targetStudent) {
                $studentClass = $targetStudent->class;
            }
        }

        DB::table('notifications')->insert([
            'student_id' => $request->student_id ?? null,
            'class' => $studentClass, 
            'type' => $type,
            'sender_type' => 'admin',
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
        $student = \App\Models\Student::where('id', $student_id)->orWhere('student_id', $student_id)->first();
        
        $query = DB::table('notifications')->orderBy('created_at', 'desc');

        if ($student) {
            $query->where(function($q) use ($student) {
                $q->where('class', 'all')
                  ->orWhere('class', $student->class)
                  ->orWhere('student_id', $student->id);
            });
        }

        // Admin-only: Hide blackout alerts from students
        $query->where('type', '!=', 'blackout');

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
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:20480',
            'audio' => 'nullable|file|mimes:mp3,wav|max:20480',
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

        $videoUrl = null;
        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('alerts', 'public');
            $videoUrl = asset('storage/' . $path);
        }

        $audioUrl = null;
        if ($request->hasFile('audio')) {
            $path = $request->file('audio')->store('alerts', 'public');
            $audioUrl = asset('storage/' . $path);
        }

        // Auto-categorization: If message mentions low battery or gate emergency, set type to blackout
        $lowBatteryKeywords = ['low on battery', 'battery is low', 'running low on battery', 'main gate'];
        $messageLower = strtolower($request->message);
        foreach ($lowBatteryKeywords as $keyword) {
            if (str_contains($messageLower, $keyword)) {
                $type = 'blackout';
                break;
            }
        }

        // Determine class automatically if student_id is provided
        $studentClass = $request->class ?? 'all';
        if ($request->student_id && $studentClass === 'all') {
            $targetStudent = \App\Models\Student::where('id', $request->student_id)
                ->orWhere('student_id', $request->student_id)
                ->first();
            if ($targetStudent) {
                $studentClass = $targetStudent->class;
            }
        }

        $id = DB::table('notifications')->insertGetId([
            'student_id' => $request->student_id ?? null,
            'class' => $studentClass, 
            'type' => $type,
            'sender_type' => ($type === 'student_message' || $type === 'sos' || $type === 'blackout') ? 'student' : 'system',
            'message' => $request->message,
            'latitude' => $request->latitude ?? null,
            'longitude' => $request->longitude ?? null,
            'battery_level' => $request->battery_level ?? null,
            'signal_status' => $request->signal ?? null,
            'location' => $request->location ?? null,
            'media_url' => $mediaUrl,
            'video_url' => $videoUrl,
            'audio_url' => $audioUrl,
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
