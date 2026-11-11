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
            'student_id' => 'required',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'sos_status' => 'nullable|in:safe,help',
        ]);

        $student = Student::where('id', $validated['student_id'])
            ->orWhere('student_id', $validated['student_id'])
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $location = Location::create([
            'student_id' => $student->id,
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'recorded_at' => now(),
        ]);

        $student->recordStatus('online', $validated['sos_status'] ?? null);
        if (($validated['sos_status'] ?? null) === 'safe') {
            \DB::table('emergency_alerts')
                ->where('student_id', $student->id)
                ->where('status', '!=', 'resolved')
                ->update(['status' => 'resolved', 'resolved_at' => now(), 'updated_at' => now()]);
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
            'student_id' => 'required',
            'sos_status' => 'required|in:safe,help',
            'latitude'   => 'nullable|numeric',
            'longitude'  => 'nullable|numeric',
        ]);

        $student = Student::where('id', $request->student_id)
            ->orWhere('student_id', $request->student_id)
            ->firstOrFail();
        if ($request->filled('latitude') && $request->filled('longitude')) {
            $student->locations()->create([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'recorded_at' => now(),
            ]);
        }
        $student->recordStatus('online', $request->sos_status);

        // SOS notification creation (with video) is handled by SosAlertController.
        if ($request->sos_status === 'safe') {
            // Mark as resolved if student is safe
            \DB::table('emergency_alerts')->where('student_id', $student->id)
                ->where('status', '!=', 'resolved')
                ->update(['status' => 'resolved', 'resolved_at' => now(), 'updated_at' => now()]);
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
                'sos_status' => $student->sos_status,
                'student' => [
                    'student_id' => $student->student_id,
                    'name' => $student->name ?? 'Unknown',
                    'gender' => $student->gender ?? 'male',
                    'class' => $student->class ?? 'N/A',
                    'email' => $student->email ?? 'N/A',
                    'phone' => $student->phone ?? null,
                    'sos_status' => $student->sos_status,
                ]
            ];
        })->filter()->values();

        return response()->json($locations);
    }
}
