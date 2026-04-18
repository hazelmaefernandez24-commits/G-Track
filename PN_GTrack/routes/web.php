<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login'); 
Route::post('/login', [AuthController::class, 'login']);
Route::get('/logout', [AuthController::class, 'logout']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('welcome');
});

use App\Models\Notification;
use Illuminate\Support\Facades\Schema;

Route::get('/dashboard', function () {

  
    $latestLocations = DB::table('locations')
        ->select('student_id', DB::raw('MAX(recorded_at) as last_seen'))
        ->groupBy('student_id')
        ->get();

    $onlineCount = 0;
    $offlineCount = 0;
    $latestUpdate = null;

    foreach ($latestLocations as $loc) {
        $lastSeen = Carbon::parse($loc->last_seen);

        if (!$latestUpdate || $lastSeen->gt($latestUpdate)) {
            $latestUpdate = $lastSeen;
        }

        if ($lastSeen->diffInMinutes(now()) <= 10) {
            $onlineCount++;
        } else {
            $offlineCount++;
        }
    }

    if (!$latestUpdate) {
        $latestTime = 'No updates yet';
        $latestDate = '';
    } else {
        $latestTime = $latestUpdate->format('g:i:s A');
        $latestDate = $latestUpdate->format('n/j/Y');
    }


    $broadcastCount = 0;
$sosCount = 0;

if (Schema::hasTable('notifications')) {

   
    $broadcastCount = DB::table('notifications')
        ->where('read', false)
        ->count();

    // ONLY unread and non-resolved SOS alerts
    $sosCount = DB::table('notifications')
        ->where('type', 'sos')
        ->where('status', '!=', 'resolved')
        ->count();
}

    return view('dashboard', compact(
        'onlineCount',
        'offlineCount',
        'latestTime',
        'latestDate',
        'broadcastCount',
        'sosCount'
    ));
});

Route::get('/notifications', [NotificationController::class, 'index']);

Route::post('/notifications/send', [NotificationController::class, 'send']);

Route::post('/notifications/{id}/acknowledge', [NotificationController::class, 'acknowledge']);
Route::post('/notifications/{id}/resolve', [NotificationController::class, 'resolve']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'read']);
Route::post('/notifications/{id}/reply', [NotificationController::class, 'reply']);

Route::get('/dashboard', [App\Http\Controllers\DeviceController::class, 'index']);

Route::get('/students', function () {
    $students = \App\Models\Student::orderBy('name', 'asc')->get();
    return view('students', compact('students'));
});

// ── Messenger Routes ──

// Sidebar conversation list (no student selected)
Route::get('/messages', function () {
    $students = \App\Models\Student::orderBy('name')->get();

    $conversations = $students->map(function ($student) {
        $lastMessage = DB::table('notifications')
            ->where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_id', $student->student_id);
            })
            ->whereIn('sender_type', ['student', 'admin'])
            ->whereIn('type', ['student_message', 'admin_reply', 'message'])
            ->orWhere(function ($q) use ($student) {
                $q->where('student_id', $student->id)->where('sender_type', 'student');
            })
            ->orderBy('created_at', 'desc')
            ->first();

        // Simpler query
        $lastMessage = DB::table('notifications')
            ->where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_id', $student->student_id);
            })
            ->whereNotIn('type', ['sos', 'blackout', 'broadcast'])
            ->orderBy('created_at', 'desc')
            ->first();

        $unreadCount = DB::table('notifications')
            ->where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_id', $student->student_id);
            })
            ->where('sender_type', 'student')
            ->where('read', false)
            ->whereNotIn('type', ['sos', 'blackout', 'broadcast'])
            ->count();

        return [
            'student'     => $student,
            'lastMessage' => $lastMessage,
            'unreadCount' => $unreadCount,
        ];
    })->filter(fn($c) => $c['lastMessage'] !== null)->values();

    return view('messages', compact('conversations'));
});

// Open a specific student's conversation
Route::get('/messages/{studentId}', function ($studentId) {
    $activeStudent = \App\Models\Student::findOrFail($studentId);

    $students = \App\Models\Student::orderBy('name')->get();
    $conversations = $students->map(function ($student) {
        $lastMessage = DB::table('notifications')
            ->where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_id', $student->student_id);
            })
            ->whereNotIn('type', ['sos', 'blackout', 'broadcast'])
            ->orderBy('created_at', 'desc')
            ->first();

        $unreadCount = DB::table('notifications')
            ->where(function ($q) use ($student) {
                $q->where('student_id', $student->id)
                  ->orWhere('student_id', $student->student_id);
            })
            ->where('sender_type', 'student')
            ->where('read', false)
            ->whereNotIn('type', ['sos', 'blackout', 'broadcast'])
            ->count();

        return ['student' => $student, 'lastMessage' => $lastMessage, 'unreadCount' => $unreadCount];
    })->filter(fn($c) => $c['lastMessage'] !== null)->values();

    // Get all messages for this student (thread - both directions)
    $messages = DB::table('notifications')
        ->where(function ($q) use ($activeStudent) {
            $q->where('student_id', $activeStudent->id)
              ->orWhere('student_id', $activeStudent->student_id);
        })
        ->whereNotIn('type', ['sos', 'blackout', 'broadcast'])
        ->orderBy('created_at', 'asc')
        ->get();

    // Mark student messages as read
    DB::table('notifications')
        ->where(function ($q) use ($activeStudent) {
            $q->where('student_id', $activeStudent->id)
              ->orWhere('student_id', $activeStudent->student_id);
        })
        ->where('sender_type', 'student')
        ->where('read', false)
        ->update(['read' => true]);

    // Root message ID for replies
    $rootMessage = DB::table('notifications')
        ->where(function ($q) use ($activeStudent) {
            $q->where('student_id', $activeStudent->id)
              ->orWhere('student_id', $activeStudent->student_id);
        })
        ->where('sender_type', 'student')
        ->whereNull('parent_id')
        ->orderBy('created_at', 'desc')
        ->first();

    $rootMessageId = $rootMessage?->id;
    $isFemale = strtolower($activeStudent->gender ?? '') === 'female';

    return view('messages', compact('conversations', 'activeStudent', 'messages', 'rootMessageId', 'isFemale'));
});

// JSON endpoint for live polling
Route::get('/messages/{studentId}/json', function ($studentId) {
    $student = \App\Models\Student::findOrFail($studentId);

    $messages = DB::table('notifications')
        ->where(function ($q) use ($student) {
            $q->where('student_id', $student->id)
              ->orWhere('student_id', $student->student_id);
        })
        ->whereNotIn('type', ['sos', 'blackout', 'broadcast'])
        ->orderBy('created_at', 'asc')
        ->get();

    return response()->json(['messages' => $messages]);
});

// Admin sends a reply to existing message thread
Route::post('/messages/reply/{id}', function ($id) {
    $request = request();
    $request->validate(['message' => 'required']);

    $parent = DB::table('notifications')->where('id', $id)->first();
    if (!$parent) abort(404);

    DB::table('notifications')->insert([
        'student_id'  => $parent->student_id,
        'class'       => $parent->class,
        'type'        => 'admin_reply',
        'sender_type' => 'admin',
        'parent_id'   => $id,
        'message'     => $request->message,
        'read'        => false,
        'status'      => 'replied',
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    DB::table('notifications')->where('id', $id)->update(['status' => 'replied', 'read' => true]);

    return response()->json(['success' => true]);
});

// Admin sends a new message to a student (no prior thread)
Route::post('/messages/new/{studentId}', function ($studentId) {
    $request = request();
    $request->validate(['message' => 'required']);

    $student = \App\Models\Student::findOrFail($studentId);

    DB::table('notifications')->insert([
        'student_id'  => $student->id,
        'class'       => $student->class,
        'type'        => 'admin_reply',
        'sender_type' => 'admin',
        'parent_id'   => null,
        'message'     => $request->message,
        'read'        => false,
        'status'      => 'sent',
        'created_at'  => now(),
        'updated_at'  => now(),
    ]);

    return response()->json(['success' => true]);
});


