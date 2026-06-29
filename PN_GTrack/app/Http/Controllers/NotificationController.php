<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class NotificationController extends Controller
{
    private function replyColumnName(): string
    {
        return 'reply_to_id';
    }

    private function canSendMessages(): bool
    {
        return Auth::guard('admin')->check()
            && in_array(Auth::guard('admin')->user()->role, ['education', 'main'], true);
    }

    private function currentAdminId(): ?int
    {
        $user = Auth::guard('admin')->user();

        return $user ? (int) $user->getKey() : null;
    }

    private function scopeToCurrentAdmin($query)
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return $query;
        }

        if ($user->role === 'main') {
            // Main admins only see conversations assigned to them and broadcasts/alerts.
            return $query->where(function ($q) use ($user) {
                $q->where('admin_id', $user->getKey())
                  ->orWhereIn('type', ['broadcast', 'sos', 'blackout']);
            });
        }

        return $query->where('admin_id', $user->getKey());  // Education admins see only their assigned messages
    }

    private function currentAdminName(): string
    {
        $user = Auth::guard('admin')->user();

        if (!$user) {
            return 'Education Staff';
        }

        return trim(implode(' ', array_filter([
            $user->first_name,
            $user->middle_initial,
            $user->last_name,
        ])));
    }

    private function currentSenderLabel(): string
    {
        $user = Auth::guard('admin')->user();
        $roleLabel = $user && isset($user->role) && $user->role === 'education'
            ? 'Education'
            : 'Admin';
        $name = $this->currentAdminName();

        return $name ? $roleLabel . ' - ' . $name : $roleLabel;
    }

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

        if ($tab === 'student') {
            $query = $this->scopeToCurrentAdmin($query);
        }

        // --- FILTER BY TYPE (TAB) ---
        if ($tab === 'sos') {
            $query->whereIn('type', ['sos', 'blackout']);
        } elseif ($tab === 'broadcast') {
            $query->where('type', 'broadcast');
        } else {
            // STUDENT MESSAGES: Modern Messenger-style grouping
            $query->whereNotIn('type', ['sos', 'blackout'])
                  ->where(function($q) {
                      $q->whereIn('type', ['student_message', 'admin_reply'])
                        ->orWhereIn('sender_type', ['student', 'admin']);
                  });
        }

        // --- FILTER BY CLASS ---
        if ($class !== 'all') {
            $query->where(function ($q) use ($dbClass, $tab) {
                $q->where('class', $dbClass)
                  ->orWhereHas('student', function ($sq) use ($dbClass) {
                      $sq->where('class', $dbClass);
                  });

                if ($tab !== 'student') {
                    $q->orWhere('class', 'all'); // Include global broadcasts only outside student message list
                }
            });
        }

        // Get all relevant notifications
        // Use descending order directly and reset keys for proper collection handling
        $allNotifications = $query->orderBy('created_at', 'desc')->get();

        if ($tab === 'student') {
            // Group the messages by student for Messenger layout
            // Only group items that have a valid student_id
            $notifications = $allNotifications->whereNotNull('student_id')->groupBy('student_id');
        } else {
            // For SOS and Broadcast, keep the list sorted by newest first
            $notifications = $allNotifications->values();
        }

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

        $threadStudentIds = $allNotifications
            ->whereNotNull('student_id')
            ->pluck('student_id')
            ->filter()
            ->unique()
            ->values();

        $sidebarStudents = $threadStudentIds->isNotEmpty()
            ? \App\Models\Student::whereIn('id', $threadStudentIds)->orderBy('name', 'asc')->get()
            : collect();

        $user = Auth::guard('admin')->user();
        $unreadCountsQuery = \App\Models\Notification::selectRaw('student_id, count(*) as unread_count')
            ->where('sender_type', 'student')
            ->where('read', false)
            ->whereNotNull('student_id');

        if ($user && in_array($user->role, ['education', 'main'], true)) {
            $unreadCountsQuery->where('admin_id', $user->getKey());
        }

        $unreadCounts = $unreadCountsQuery
            ->groupBy('student_id')
            ->pluck('unread_count', 'student_id');

        return view('notifications', [
            'notifications' => $notifications,
            'sidebarStudents' => $sidebarStudents,
            'stats' => $stats,
            'tab' => $tab,
            'subtab' => $subtab,
            'class' => $class,
            'dbClass' => $dbClass,
            'canMessage' => $this->canSendMessages(),
            'unreadCounts' => $unreadCounts,
            'currentAdminId' => $user ? $user->getKey() : null,
            'currentAdminRole' => $user ? $user->role : null,
        ]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'subject' => 'required|max:255',
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
            'sender_name' => $this->currentSenderLabel(),
            'subject' => $request->subject,
            'message' => $request->message,
            'read' => true, // Admin-sent broadcasts are "read" by default for the admin
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'Broadcast notification sent successfully!');
    }

    public function reply(Request $request, $id)
    {
        if (!$this->canSendMessages()) {
            return redirect()->back()->with('error', 'Only education staff can send student messages.');
        }

        $request->validate([
            'message' => 'required',
        ]);

        $parent = DB::table('notifications')->where('id', $id)->first();
        if (!$parent) {
            return redirect()->back()->with('error', 'Message not found.');
        }

        $replyColumn = $this->replyColumnName();

        DB::table('notifications')->insert([
            'student_id' => $parent->student_id,
            'admin_id' => $this->currentAdminId(),
            'class' => $parent->class,
            'type' => 'admin_reply',
            'sender_type' => 'admin',
            'sender_name' => $this->currentAdminName(),
            $replyColumn => $id,
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

    public function deleteAllSosArchives()
    {
        \App\Models\Notification::where('type', 'sos')
            ->where('status', 'resolved')
            ->delete();

        return redirect()->back()->with('success', 'All SOS archives deleted successfully.');
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

        // Apply access control based on user role
        $user = Auth::guard('admin')->user();
        
        // Main admins should not be able to view student conversations via API
        if ($user && $user->role === 'main') {
            return response()->json([
                'success' => false,
                'message' => 'Access denied',
                'notifications' => []
            ], 403);
        }

        $notificationsQuery = DB::table('notifications')
            ->where(function($q) use ($student) {
                // 1. Global broadcasts
                $q->where('class', 'all')
                // 2. Class-specific broadcasts
                  ->orWhere('class', $student->class)
                // 3. Direct messages to this student reference
                  ->orWhere('student_id', $student->id);
            })
            // Only show broadcasts or messages, hide system/admin alerts like blackout
            ->where('type', '!=', 'blackout');

        // Apply role-based filtering
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if ($user->role === 'education') {
                // Education staff only see their own conversations
                $notificationsQuery->where('admin_id', Auth::guard('admin')->id());
            }
        }

        $notifications = $notificationsQuery
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($notif) {
                // Backward-compatibility: keep both names available for old and new schemas
                // $notif->parent_id = $notif->reply_to_id ?? $notif->parent_id ?? null; // No longer needed
                return $notif;
            });

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
            'battery' => 'nullable|integer|between:0,100',
            'signal' => 'nullable|string',
            'location' => 'nullable|string',
            'media' => 'nullable|file|mimes:mp4,mov,avi,jpg,png,jpeg|max:25600', // 25MB max
            'video' => 'nullable|file|mimes:mp4,mov,avi|max:25600', // 25MB max
        ]);

        if ($request->target === 'sos' && !$request->hasFile('video')) {
            return response()->json([
                'success' => false,
                'message' => 'SOS alerts require a video feed before they are accepted.'
            ], 422);
        }

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

        try {
            $mediaUrl = $request->hasFile('media') ? asset('storage/' . $request->file('media')->store('recordings/media', 'public')) : null;
            $videoUrl = $request->hasFile('video') ? asset('storage/' . $request->file('video')->store('recordings/videos', 'public')) : null;
        } catch (\Exception $e) {
            \Log::error('Failed to upload media/video files: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload media files: ' . $e->getMessage()
            ], 500);
        }

        $recipientAdminId = $request->input('admin_id', $request->input('recipient_admin_id', $request->input('target_admin_id', $request->input('to_admin_id'))));

        try {
            $id = DB::table('notifications')->insertGetId([
                'student_id' => $student->id, // Use numeric ID for the relationship
                'admin_id' => $recipientAdminId,
                'class' => $student->class, 
                'type' => $type,
                'sender_type' => 'student',
                'message' => $request->message,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'battery_level' => $request->input('battery_level', $request->input('battery')),
                'signal_status' => $request->signal,
                'location' => $request->location,
                'media_url' => $mediaUrl,
                'video_url' => $videoUrl,
                'audio_url' => null,
                'read' => false,
                'status' => 'pending', 
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to create notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification: ' . $e->getMessage()
            ], 500);
        }

        // If it's SOS or Blackout, update the Student's real-time map status
        if ($type === 'sos' || $type === 'blackout') {
            try {
                if ($request->latitude) $student->latitude = $request->latitude;
                if ($request->longitude) $student->longitude = $request->longitude;
                $battery = $request->input('battery_level', $request->input('battery'));
                if (isset($battery)) $student->battery_level = $battery;
                if ($request->signal) $student->signal_status = $request->signal;
                if ($type === 'sos') $student->sos_status = 'help';
                
                $student->last_update = now()->format('M d, Y h:i A');
                $student->save();
            } catch (\Exception $e) {
                \Log::error('Failed to update student status: ' . $e->getMessage());
                return response()->json([
                    'success' => false,
                    'message' => 'Alert received but failed to update student status: ' . $e->getMessage()
                ], 500);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Emergency alert received by Admin!',
            'notification_id' => $id
        ]);
    }

    // --- MESSENGER UI AJAX METHODS ---
    public function getMessagesJson($student_id)
    {
        $student = \App\Models\Student::where('id', $student_id)
            ->orWhere('student_id', $student_id)
            ->first();

        if (!$student) {
            return response()->json(['messages' => []]);
        }

        // Apply access control based on user role
        $user = Auth::guard('admin')->user();

        // Fetch all messages related to this student
        $markSeenQuery = DB::table('notifications')
            ->where('student_id', $student->id)
            ->where('sender_type', 'student')
            ->where('read', false);

        if ($user && in_array($user->role, ['education', 'main'], true)) {
            $markSeenQuery->where('admin_id', $user->getKey());
        }

        $markSeenQuery->update(['read' => true]);

        $messagesQuery = DB::table('notifications')
            ->where('student_id', $student->id);

        if ($user && in_array($user->role, ['education', 'main'], true)) {
            // Only show messages for the admin currently logged in.
            $messagesQuery->where('admin_id', $user->getKey());
        }

        $messages = $messagesQuery
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function($msg) {
                // Backward-compatibility: keep both names available for old and new schemas
                // $msg->parent_id = $msg->reply_to_id ?? $msg->parent_id ?? null; // No longer needed
                return $msg;
            });

        return response()->json(['messages' => $messages]);
    }

    public function sendMessageAjax(Request $request, $student_id)
    {
        if (!$this->canSendMessages()) {
            return response()->json(['success' => false, 'message' => 'Only education staff can send student messages.'], 403);
        }

        $message = trim((string) $request->input('message', ''));

        if ($message === '') {
            return response()->json(['success' => false, 'message' => 'Message cannot be empty.'], 422);
        }

        $student = \App\Models\Student::where('id', $student_id)
            ->orWhere('student_id', $student_id)
            ->first();

        if (!$student) {
            return response()->json(['success' => false], 404);
        }

        // Find the latest message from the student to set as the reply_to_id parent
        $latestStudentMessage = DB::table('notifications')
            ->where('student_id', $student->id)
            ->where('sender_type', 'student')
            ->orderBy('created_at', 'desc')
            ->first();

        $replyColumn = $this->replyColumnName();

        $id = DB::table('notifications')->insertGetId([
            'student_id'  => $student->id,
            'admin_id'    => $this->currentAdminId(),
            'class'       => $student->class,
            'type'        => 'admin_reply',
            'sender_type' => 'admin',
            'sender_name' => $this->currentAdminName(),
            $replyColumn  => $latestStudentMessage ? $latestStudentMessage->id : null,
            'message'     => $message,
            'read'        => false,
            'status'      => 'replied',
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        // Mark any unread student messages in this conversation as seen by the admin
        $readMarkQuery = DB::table('notifications')
            ->where('student_id', $student->id)
            ->where('sender_type', 'student')
            ->where('read', false);

        // Only education staff can mark messages as read
        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if ($user->role === 'education') {
                $readMarkQuery->where('admin_id', Auth::guard('admin')->id());
            }
        }

        $readMarkQuery->update(['read' => true]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function deleteConversation(Request $request, $student_id)
    {
        if (!$this->canSendMessages()) {
            return response()->json(['success' => false, 'message' => 'Only education staff can delete student messages.'], 403);
        }

        $student = \App\Models\Student::where('id', $student_id)
            ->orWhere('student_id', $student_id)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found.'], 404);
        }

        $query = DB::table('notifications')
            ->where('student_id', $student->id)
            ->whereNotIn('type', ['sos', 'blackout', 'broadcast'])
            ->where(function($q) {
                $q->whereIn('type', ['student_message', 'admin_reply'])
                  ->orWhereIn('sender_type', ['student', 'admin']);
            });

        if (Auth::guard('admin')->check()) {
            $user = Auth::guard('admin')->user();
            if (in_array($user->role, ['education', 'main'], true)) {
                $query->where('admin_id', $user->getKey());
            }
        }

        $query->delete();

        return response()->json(['success' => true]);
    }

    public function allStudentsJson(Request $request)
    {
        $class = $request->query('class');
        $query = \App\Models\Student::query();
        if ($class && $class !== 'all') {
            $query->where('class', $class);
        }
        $students = $query->orderBy('name', 'asc')->get(['id', 'name', 'student_id', 'class', 'status']);
        return response()->json(['students' => $students]);
    }

    /**
     * GET /api/admins
     * Returns list of all available admins for messaging/notifications
     */
    public function getAdmins()
    {
        $admins = \App\Models\Admin::select('id', 'staff_id', 'first_name', 'middle_initial', 'last_name', 'email', 'role')
            ->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
            ->get()
            ->map(function($admin) {
                return [
                    'id' => $admin->id,
                    'name' => trim($admin->first_name . ' ' . $admin->last_name),
                    'email' => $admin->email,
                    'role' => $admin->role,
                    'staff_id' => $admin->staff_id,
                ];
            });

        return response()->json([
            'success' => true,
            'admins' => $admins
        ]);
    }

    // --- ADMIN MOBILE API METHODS ---

    public function apiAdminBroadcast(Request $request)
    {
        $request->validate([
            'target' => 'required',
            'subject' => 'required|max:255',
            'message' => 'required',
        ]);

        $target = $request->target;
        $studentClass = 'all';
        $studentId = null;

        if ($target === 'all') {
            $studentClass = 'all';
        } elseif (in_array($target, ['2026', '2027', '2028'])) {
            $studentClass = $target;
        } else {
            $studentId = $target;
            $student = \App\Models\Student::where('student_id', $target)->orWhere('id', $target)->first();
            if ($student) {
                $studentClass = $student->class;
            }
        }

        DB::table('notifications')->insert([
            'student_id' => $studentId,
            'class' => $studentClass, 
            'type' => 'broadcast',
            'sender_type' => 'admin',
            'sender_name' => $request->input('admin_name', 'Admin'),
            'subject' => $request->subject,
            'message' => $request->message,
            'read' => true,
            'status' => 'pending', 
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Broadcast sent successfully']);
    }

    public function apiAdminSendMessage(Request $request, $student_id)
    {
        $request->validate([
            'message' => 'required',
        ]);

        $student = \App\Models\Student::where('id', $student_id)
            ->orWhere('student_id', $student_id)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        $latestStudentMessage = DB::table('notifications')
            ->where('student_id', $student->id)
            ->where('sender_type', 'student')
            ->orderBy('created_at', 'desc')
            ->first();

        $id = DB::table('notifications')->insertGetId([
            'student_id'  => $student->id,
            'admin_id'    => $request->input('admin_id'), // Send admin_id from mobile
            'class'       => $student->class,
            'type'        => 'admin_reply',
            'sender_type' => 'admin',
            'sender_name' => $request->input('admin_name', 'Admin'), // Send admin name from mobile
            'reply_to_id' => $latestStudentMessage ? $latestStudentMessage->id : null,
            'message'     => $request->message,
            'read'        => false,
            'status'      => 'replied',
            'created_at'  => now(),
            'updated_at'  => now()
        ]);

        return response()->json(['success' => true, 'message' => 'Message sent successfully', 'id' => $id]);
    }
    
    public function apiAdminResolve($id)
    {
        $notification = \App\Models\Notification::find($id);
        if ($notification) {
            $notification->update([
                'status' => 'resolved',
                'read' => true
            ]);

            if ($notification->student_id) {
                $student = \App\Models\Student::where('student_id', $notification->student_id)
                    ->orWhere('id', $notification->student_id)
                    ->first();
                if ($student) {
                    $student->sos_status = 'safe';
                    $student->save();
                }
            }
            return response()->json(['success' => true, 'message' => 'Alert marked as Resolved (Safe).']);
        }
        return response()->json(['success' => false, 'message' => 'Notification not found'], 404);
    }
}
