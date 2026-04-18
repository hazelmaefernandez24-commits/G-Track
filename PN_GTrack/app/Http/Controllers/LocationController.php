<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Location;
use App\Models\Student;

class LocationController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'student_id' => 'required|integer|exists:students,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'sos_status' => 'nullable|in:safe,help',
        ]);

        $location = Location::create([
            'student_id' => $validated['student_id'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'recorded_at' => now(),
            'sos_status' => $validated['sos_status'] ?? 'safe',
        ]);

        $student = Student::find($validated['student_id']);
        if ($student) {
            $student->status = true; // Mark as online when sending GPS
            $student->last_update = now()->format('M d, Y h:i A');
            
            // Handle SOS status changes
            if (!empty($validated['sos_status'])) {
                $oldStatus = $student->sos_status;
                $student->sos_status = $validated['sos_status'];

                // If moving to SOS 'help', ensure a notification exists
                if ($validated['sos_status'] === 'help') {
                    // Check if there's already an active (unresolved) SOS alert to avoid duplication
                    $activeAlert = \App\Models\Notification::where('student_id', $student->id)
                        ->where('type', 'sos')
                        ->where('status', '!=', 'resolved')
                        ->first();

                    if (!$activeAlert) {
                        \App\Models\Notification::create([
                            'type'          => 'sos',
                            'sender_type'   => 'student',
                            'message'       => $student->name . ' (' . $student->student_id . ') triggered an SOS alert via Location Update!',
                            'student_id'    => $student->id,
                            'class'         => $student->class,
                            'latitude'      => $validated['latitude'],
                            'longitude'     => $validated['longitude'],
                            'read'          => false,
                            'status'        => 'pending',
                        ]);
                    }
                } elseif ($validated['sos_status'] === 'safe' && $oldStatus === 'help') {
                    // If moving from help to safe, resolve any active alerts
                    \App\Models\Notification::where('student_id', $student->id)
                        ->where('type', 'sos')
                        ->where('status', '!=', 'resolved')
                        ->update(['status' => 'resolved', 'read' => true]);
                }
            }
            $student->save();
        }

        return response()->json([
            'message' => 'Location updated successfully',
            'location' => $location,
        ]);
    }

    public function setSOS(Request $request)
    {
        // Now supporting more fields from mobile to make the SOS alert rich
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'sos_status' => 'required|in:safe,help',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
            'battery'    => 'nullable|integer',
            'signal'     => 'nullable|string',
        ]);

        $student = Student::findOrFail($request->student_id);
        $student->sos_status = $request->sos_status;
        
        // Update live telemetry if provided
        if ($request->latitude)  $student->latitude  = $request->latitude;
        if ($request->longitude) $student->longitude = $request->longitude;
        if ($request->battery)   $student->battery_level = $request->battery;
        if ($request->signal)    $student->signal_status = $request->signal;
        
        $student->last_update = now()->format('M d, Y h:i A');
        $student->save();

        // Create the notification record so it appears in the Admin Dashboard
        if ($request->sos_status === 'help') {
            \App\Models\Notification::create([
                'type'          => 'sos',
                'sender_type'   => 'student',
                'message'       => $student->name . ' (' . $student->student_id . ') sent an SOS alert!',
                'student_id'    => $student->id, // Use numeric ID for the relationship
                'class'         => $student->class,
                'latitude'      => $request->latitude,
                'longitude'     => $request->longitude,
                'battery_level' => $request->battery,
                'signal_status' => $request->signal,
                'read'          => false,
                'status'        => 'pending',
            ]);
        } else {
            // Mark as resolved if student is safe
            \App\Models\Notification::where('student_id', $student->id)
                ->where('type', 'sos')
                ->where('status', '!=', 'resolved')
                ->update(['status' => 'resolved', 'read' => true]);
        }

        // Optionally update historical location entry
        $lastLocation = Location::where('student_id', $student->id)->latest('recorded_at')->first();
        if ($lastLocation) {
            $lastLocation->sos_status = $request->sos_status;
            $lastLocation->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'SOS status updated and Admin notified.',
            'sos_status' => $request->sos_status
        ]);
    }

    public function getAll(Request $request)
    {
        $classFilter = $request->query('class', 'All Classes');
        
        // Normalize class filter if it starts with 'Class '
        if ($classFilter !== 'All Classes' && str_starts_with($classFilter, 'Class ')) {
            $classFilter = str_replace('Class ', '', $classFilter);
        }

        $query = Student::with(['locations' => function ($q) {
            $q->orderBy('recorded_at', 'desc');
        }]);

        if ($classFilter !== 'All Classes') {
            $query->where('class', $classFilter);
        }

        $students = $query->get();

        $locations = $students->map(function ($student) {
            $latest = $student->locations->first();
            if (!$latest) return null;

            return [
                'id' => $latest->id,
                'student_id' => $student->id,
                'latitude' => $latest->latitude,
                'longitude' => $latest->longitude,
                'recorded_at' => $latest->recorded_at,
                // CRITICAL: Always use the Student's CURRENT SOS status, not historical log data
                'sos_status' => $student->sos_status ?? 'safe', 
                'student' => [
                    'student_id' => $student->student_id,
                    'name' => $student->name ?? 'Unknown',
                    'gender' => $student->gender ?? 'male',
                    'class' => $student->class ?? '2026',
                    'email' => $student->email ?? 'N/A',
                    'phone' => $student->phone ?? null,
                    'sos_status' => $student->sos_status ?? 'safe',
                ]
            ];
        })->filter()->values();

        return response()->json($locations);
    }
}
