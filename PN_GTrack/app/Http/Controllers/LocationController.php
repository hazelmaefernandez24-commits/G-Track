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

        if (!empty($validated['sos_status'])) {
            $student = Student::find($validated['student_id']);
            $student->sos_status = $validated['sos_status'];
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
        
        $query = Location::with('student')
            ->orderBy('recorded_at', 'desc')
            ->latest();

        if ($classFilter !== 'All Classes') {
            $query->whereHas('student', function ($q) use ($classFilter) {
                $q->where('class', $classFilter);
            });
        }

        $locations = $query->get()->map(function ($location) {
            return [
                'id' => $location->id,
                'student_id' => $location->student_id,
                'latitude' => $location->latitude,
                'longitude' => $location->longitude,
                'recorded_at' => $location->recorded_at,
                'sos_status' => $location->sos_status ?? ($location->student->sos_status ?? 'safe'),
                'student' => [
                    'name' => $location->student->name ?? 'Unknown',
                    'gender' => $location->student->gender ?? 'male',
                    'class' => $location->student->class ?? '2026',
                    'email' => $location->student->email ?? 'N/A',
                    'phone' => $location->student->phone ?? null,
                    'sos_status' => $location->student->sos_status ?? 'safe',
                ]
            ];
        });

        return response()->json($locations);
    }
}
