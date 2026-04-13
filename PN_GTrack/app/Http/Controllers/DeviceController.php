<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
class DeviceController extends Controller
{
 public function update(Request $request)
{
    
    \App\Models\Student::updateOrCreate(
        ['student_id' => $request->student_id],
        [
            'battery_level'  => $request->battery_level,
            'signal_status'  => $request->signal, // Changed to match your migration name
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
        if ($student->updated_at->addMinutes(5)->isPast()) {
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

    $broadcastCount = \DB::table('notifications')->where('type', 'broadcast')->where('read', false)->count();
    $sosCount       = \DB::table('notifications')->where('type', 'sos')->where('status', '!=', 'resolved')->count();

    // Latest overall system update
    $latestUpdate = \App\Models\Student::max('updated_at');
    $latestTime   = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null;
    $latestDate   = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null;

    return view('dashboard', compact(
        'students',
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
public function apiStats()
{
    $students = \App\Models\Student::all();

    // Auto-mark students offline if no heartbeat in last 5 minutes
    // Use updated_at (real timestamp) for accurate time comparison
    foreach ($students->where('status', true) as $student) {
        if ($student->updated_at->addMinutes(5)->isPast()) {
            $student->status = false;
            $student->save();
        }
    }

    // Reload after possible updates
    $students = \App\Models\Student::all();

    $onlineCount   = $students->where('status', true)->count();
    $offlineCount  = $students->where('status', false)->count();
    $broadcastCount = \DB::table('notifications')->where('type', 'broadcast')->where('read', false)->count();
    $sosCount       = \DB::table('notifications')->where('type', 'sos')->where('status', '!=', 'resolved')->count();
    $sosStudents    = $students->where('sos_status', 'help')->pluck('student_id');

    // Use updated_at (real timestamp) for accurate latest update calculation
    $latestUpdate   = \App\Models\Student::max('updated_at');
    $latestTime     = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('h:i A') : null;
    $latestDate     = $latestUpdate ? \Carbon\Carbon::parse($latestUpdate)->format('M d, Y') : null;

    return response()->json([
        'onlineCount'    => $onlineCount,
        'offlineCount'   => $offlineCount,
        'broadcastCount' => $broadcastCount,
        'sosCount'       => $sosCount,
        'sosStudents'    => $sosStudents,
        'latestTime'     => $latestTime,
        'latestDate'     => $latestDate,
        'students'       => $students->map(function ($s) {
            return [
                'student_id'     => $s->student_id,
                'name'           => $s->name,
                'class'          => $s->class,
                'gender'         => $s->gender,
                'status'         => $s->status,
                'battery_level'  => $s->battery_level,
                'signal_status'  => $s->signal_status,
                'sos_status'     => $s->sos_status,
                'last_update'    => $s->last_update,
                'contact'        => $s->contact,
            ];
        }),
    ]);
}


}
