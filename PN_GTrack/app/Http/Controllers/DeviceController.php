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
    $students = \DB::table('students')
        ->join('locations', 'students.id', '=', 'locations.student_id')
        ->select(
            'students.id',
            'students.student_id',
            'students.name',
            'students.class',
            'students.gender',
            'students.contact',
            'students.battery_level',
            'students.signal_status',
            'students.status',
            'locations.latitude',
            'locations.longitude',
            'locations.sos_status',
            'locations.recorded_at as last_update'
        )
        ->orderBy('locations.recorded_at', 'desc')
        ->get();

    // Mark offline if last_update older than 10 minutes
    foreach ($students as $student) {
        if ($student->last_update && Carbon::parse($student->last_update)->lt(Carbon::now()->subMinutes(10))) {
            $student->status = false;
        }
    }

    // Counts
    $onlineCount   = $students->where('status', true)->count();
    $offlineCount  = $students->where('status', false)->count();


    $broadcastCount = \DB::table('notifications')->where('type', 'broadcast')->count();
    $sosCount       = \DB::table('notifications')->where('type', 'sos')->count();

    $latestUpdate = $students->max('last_update');
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


}
