<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
class DeviceController extends Controller
{
 public function update(Request $request)
{
    $request->validate(['student_id' => 'required']);
    $student = \App\Models\Student::where('student_id', $request->student_id)->first();

    if (!$student) {
        return response()->json(['message' => 'Student not found'], 404);
    }

    $student->recordStatus('online');

    return response()->json(['message' => 'Student status updated']);
}



public function index()
{
    $students = \App\Models\Student::with(['latestStatus', 'studentClass', 'locations'])->get();
    $onlineStudents = $students->where('status', true);

    foreach ($onlineStudents as $student) {
        if ($student->last_update && $student->last_update->copy()->addMinutes(15)->isPast()) {
            $student->recordStatus('offline');
        }
    }

    $students = \App\Models\Student::with(['latestStatus', 'studentClass', 'locations'])->get()->map(function ($student) {
        $lastLocation = $student->locations->sortByDesc('recorded_at')->first();
        
        $student->latitude = $lastLocation->latitude ?? null;
        $student->longitude = $lastLocation->longitude ?? null;
        $student->last_update_loc = $lastLocation->recorded_at ?? $student->last_update;
        return $student;
    });

    // Counts
    $onlineCount   = $students->where('status', true)->count();
    $offlineCount  = $students->where('status', false)->count();

    $broadcastCount = DB::table('broadcast_recipients')->whereNull('read_at')->count();
    $sosCount = DB::table('emergency_alerts')->where('status', '!=', 'resolved')->count();

    $latestUpdate = \App\Models\StudentStatus::max('recorded_at');
    $latestTime   = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null;
    $latestDate   = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null;
    $classes      = \App\Models\BatchClass::orderBy('year')->get();
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
    $students = \App\Models\Student::with(['latestStatus', 'studentClass'])->get();

    foreach ($students->where('status', true) as $student) {
        if ($student->last_update && $student->last_update->copy()->addMinutes(15)->isPast()) {
            $student->recordStatus('offline');
        }
    }

    $students = \App\Models\Student::with(['latestStatus', 'studentClass'])->get();

    $onlineCount   = $students->where('status', true)->count();
    $offlineCount  = $students->where('status', false)->count();
    $broadcastCount = DB::table('broadcast_recipients')->whereNull('read_at')->count();
    $sosCount = DB::table('emergency_alerts')->where('status', '!=', 'resolved')->count();
    $unreadSosCount = DB::table('emergency_alerts')
        ->where('status', '!=', 'resolved')
        ->whereNull('read_at')
        ->count();
    $sosStudents    = $students->where('sos_status', 'help')->pluck('student_id');
    $currentSosAlerts = $this->currentSosAlerts();

    $latestUpdate   = \App\Models\StudentStatus::max('recorded_at');
    $latestTime     = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null;
    $latestDate     = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null;

    $adminId = $request->query('admin_id');
    $adminRole = $request->query('role');
    
    $studentMessagesQuery = DB::table('messages')
        ->join('conversations', 'conversations.id', '=', 'messages.conversation_id')
        ->where('messages.sender_type', 'student')
        ->whereNull('messages.read_at');

    if ($adminId && in_array($adminRole, ['education', 'main'], true)) {
        $studentMessagesQuery->where('conversations.admin_id', $adminId);
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
    return DB::table('emergency_alerts')
        ->join('students', 'students.id', '=', 'emergency_alerts.student_id')
        ->join('student_classes', 'student_classes.id', '=', 'students.student_class_id')
        ->where('emergency_alerts.status', '!=', 'resolved')
        ->orderByDesc('emergency_alerts.created_at')
        ->get([
            'students.first_name',
            'students.last_name',
            'student_classes.year as class',
        ])
        ->map(fn ($alert) => [
            'last_name' => $alert->last_name,
            'first_name' => $alert->first_name,
            'class' => (string) $alert->class,
            'alert' => 'SOS ALERT',
        ]);
}

public function tracking()
{
    $classes = \App\Models\BatchClass::orderBy('year')->get();
    $sosCount = DB::table('emergency_alerts')->where('status', '!=', 'resolved')->count();
    return view('tracking', compact('sosCount', 'classes'));
}

public function activity()
{
    $classes = \App\Models\BatchClass::orderBy('year')->get();
    $sosCount = DB::table('emergency_alerts')->where('status', '!=', 'resolved')->count();
    return view('activity', compact('sosCount', 'classes'));
}

}
