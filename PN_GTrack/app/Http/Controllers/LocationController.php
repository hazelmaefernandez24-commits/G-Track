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
            if (!empty($validated['sos_status'])) {
                $student->sos_status = $validated['sos_status'];
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
        $request->validate([
            'student_id' => 'required|exists:students,id',
            'sos_status' => 'required|in:safe,help',
        ]);

        $student = Student::findOrFail($request->student_id);
        $student->sos_status = $request->sos_status;
        $student->save();

        // Optionally create an event location marker with same lat/lng if last location exists
        $lastLocation = Location::where('student_id', $student->id)->latest('recorded_at')->first();
        if ($lastLocation) {
            $lastLocation->sos_status = $request->sos_status;
            $lastLocation->save();
        }

        return response()->json(['message' => 'SOS status updated successfully']);
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
