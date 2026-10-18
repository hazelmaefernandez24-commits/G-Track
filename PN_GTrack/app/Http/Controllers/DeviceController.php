<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\SosAlert;
class DeviceController extends Controller
{
 public function update(Request $request)
{
    
    \App\Models\Student::updateOrCreate(
        ['student_id' => $request->student_id],
        [
            'battery_level'  => $request->input('battery_level', $request->input('battery')),
            'signal_status'  => $request->input('signal', $request->input('signal_status')), 
            'status'         => true,             // Use 'status' to match your 'onlineCount' logic
            'last_update'    => now()->format('M d, Y h:i A')
        ]
    );

    return response()->json(['message' => 'Student status updated']);
}



public function index()
{
    // 1. Auto-mark students offline if no heartbeat in last 5 minutes
    $onlineStudents = \App\Models\Student::where('status', true)->get();
    foreach ($onlineStudents as $student) {
        if ($student->updated_at->addMinutes(15)->isPast()) {
            $student->status = false;
            $student->save();
        }
    }

    // 2. Fetch students with their latest location for the table/map
    // Using Eloquent for better consistency
    $students = \App\Models\Student::all()->map(function($student) {
        $lastLocation = $student->locations()->latest('recorded_at')->first();
        
        // Add location details to the student object for the view
        $student->latitude = $lastLocation->latitude ?? null;
        $student->longitude = $lastLocation->longitude ?? null;
        $student->last_update_loc = $lastLocation->recorded_at ?? $student->last_update;
        return $student;
    });

    // Counts
    $onlineCount   = $students->where('status', true)->count();
    $offlineCount  = $students->where('status', false)->count();

    $broadcastCount = \App\Models\BroadcastNotification::where('read', false)->count();
    $sosCount       = \App\Models\SosAlert::where('status', '!=', 'resolved')->count();

    // Latest overall system update
    $latestUpdate = \App\Models\Student::max('updated_at');
    $latestTime   = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null;
    $latestDate   = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null;
    $classes      = \App\Models\BatchClass::orderBy('name')->get(['name']);
    $activeStudents = $students->where('status', true)->values();
    $activeStudentsData = $activeStudents->map(fn ($student) => [
        'first_name' => $student->first_name,
        'last_name' => $student->last_name,
        'name' => $student->name,
        'class' => $student->class,
        'status' => (bool) $student->status,
    ])->values();
    $currentSosAlerts = $this->currentSosAlerts();

    return view('dashboard', compact(
        'students',
        'activeStudents',
        'activeStudentsData',
        'currentSosAlerts',
        'classes',
        'onlineCount',
        'offlineCount',
        'broadcastCount',
        'sosCount',
        'latestTime',
        'latestDate'
    ));
}

/**
 * JSON stats endpoint polled by the dashboard every 10 seconds.
 */
public function apiStats(Request $request)
{
    $students = \App\Models\Student::all();

    // Auto-mark students offline if no heartbeat in last 5 minutes
    // Use updated_at (real timestamp) for accurate time comparison
    foreach ($students->where('status', true) as $student) {
        if ($student->updated_at->addMinutes(15)->isPast()) {
            $student->status = false;
            $student->save();
        }
    }

    // Reload after possible updates
    $students = \App\Models\Student::all();

    $onlineCount   = $students->where('status', true)->count();
    $offlineCount  = $students->where('status', false)->count();
    $broadcastCount = \App\Models\BroadcastNotification::where('read', false)->count();
    $sosCount       = \App\Models\SosAlert::where('status', '!=', 'resolved')->count();
    $unreadSosCount = \App\Models\SosAlert::where('status', '!=', 'resolved')->where('read', false)->count();
    $sosStudents    = $students->where('sos_status', 'help')->pluck('student_id');
    $currentSosAlerts = $this->currentSosAlerts();

    // Use updated_at (real timestamp) for accurate latest update calculation
    $latestUpdate   = \App\Models\Student::max('updated_at');
    $latestTime     = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null;
    $latestDate     = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null;

    $adminId = $request->query('admin_id');
    $adminRole = $request->query('role');
    
    $studentMessagesQuery = \DB::table('notifications')
        ->where('sender_type', 'student')
        ->where('type', 'student_message')
        ->where('read', false);

    if ($adminId && in_array($adminRole, ['education', 'main'], true)) {
        $studentMessagesQuery->where('admin_id', $adminId);
    }
    
    $studentMessagesCount = $studentMessagesQuery->count();
    $totalUnread = $unreadSosCount + $studentMessagesCount;

    return response()->json([
        'totalUnread'    => $totalUnread,
        'onlineCount'    => $onlineCount,
        'offlineCount'   => $offlineCount,
        'broadcastCount' => $broadcastCount,
        'sosCount'       => $sosCount,
        'sosStudents'    => $sosStudents,
        'currentSosAlerts' => $currentSosAlerts,
        'latestTime'     => $latestTime,
        'latestDate'     => $latestDate,
        'students'       => $students->map(function ($s) {
            return [
                'id'             => $s->id,
                'student_id'     => $s->student_id,
                'name'           => $s->name,
                'first_name'     => $s->first_name,
                'last_name'      => $s->last_name,
                'class'          => $s->class,
                'gender'         => $s->gender,
                'status'         => $s->status,
                'battery_level'  => $s->battery_level,
                'signal_status'  => $s->signal_status,
                'sos_status'     => $s->sos_status,
                'last_update'    => $s->last_update,
                'last_sync_at'   => $s->last_update ? Carbon::parse($s->last_update)->toIso8601String() : null,
                'contact'        => $s->contact,
            ];
        }),
    ]);
}

private function currentSosAlerts()
{
    return SosAlert::with('student')
        ->where('status', '!=', 'resolved')
        ->latest('created_at')
        ->get()
        ->filter(fn ($alert) => $alert->student !== null)
        ->map(function ($alert) {
            $student = $alert->student;
            $nameParts = preg_split('/\s+/', trim((string) $student->name), -1, PREG_SPLIT_NO_EMPTY);

            return [
                'last_name' => $student->last_name ?: (count($nameParts) > 1 ? $nameParts[count($nameParts) - 1] : ''),
                'first_name' => $student->first_name ?: ($nameParts[0] ?? ''),
                'class' => $student->class,
                'alert' => 'SOS ALERT',
            ];
        })
        ->values();
}

public function tracking()
{
    $classes = \App\Models\BatchClass::orderBy('name')->get();
    $sosCount = \App\Models\SosAlert::where('status', '!=', 'resolved')->count();
    return view('tracking', compact('sosCount', 'classes'));
}

public function activity()
{
    $classes = \App\Models\BatchClass::orderBy('name')->get();
    $sosCount = \App\Models\SosAlert::where('status', '!=', 'resolved')->count();
    return view('activity', compact('sosCount', 'classes'));
}

}
