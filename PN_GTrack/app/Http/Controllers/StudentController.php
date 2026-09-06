<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentAuth;
use App\Models\Student;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    public function apiLogin(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'password' => 'required'
        ]);

        $studentAuth = StudentAuth::where('student_id', $request->student_id)
            ->first();

        // Verify StudentAuth exists and password is correct
        if (!$studentAuth || !Hash::check($request->password, $studentAuth->password)) {
            return response()->json(['message' => 'Invalid student ID or password'], 401);
        }

        // Mark student as online upon login
        $student = Student::where('student_id', $request->student_id)->first();
        if ($student) {
            $student->status = true;
            $student->last_update = now()->format('M d, Y h:i A');
            $student->save();
        }

        // Return student details for the mobile application
        return response()->json([
            'message' => 'Login successful',
            'student' => $student,
            'role' => 'student'
        ]);
    }

    /**
     * Called by mobile app every ~30 seconds while open.
     * Keeps the student marked as online and updates device info.
     */
    public function heartbeat(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
        ]);

        $student = Student::where('student_id', $request->student_id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->status       = true;
        $student->last_update  = now()->format('M d, Y h:i A');
        $student->signal_status = $request->signal ?? $student->signal_status;
        $battery = $request->input('battery_level', $request->input('battery'));
        $student->battery_level = isset($battery) ? $battery : $student->battery_level;
        $student->touch(); // Force updated_at timestamp to refresh even if data is same
        $student->save();

        return response()->json(['message' => 'Heartbeat received', 'student_id' => $student->student_id]);
    }

    /**
     * Called by mobile app when student presses / cancels SOS.
     */
    public function sendSOS(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'sos_status' => 'required|in:safe,help',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'battery_level' => 'nullable|integer|between:0,100',
            'battery' => 'nullable|integer|between:0,100',
            'signal' => 'nullable|string',
        ]);

        $student = Student::where('student_id', $request->student_id)
            ->orWhere('id', $request->student_id)
            ->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->sos_status  = $request->sos_status;
        $student->last_update = now()->format('M d, Y h:i A');
        if ($request->latitude)  $student->latitude  = $request->latitude;
        if ($request->longitude) $student->longitude = $request->longitude;
        $battery = $request->input('battery_level', $request->input('battery'));
        if (isset($battery))   $student->battery_level = $battery;
        if ($request->signal)   $student->signal_status = $request->signal;
        $student->status = true;
        $student->save();

        if ($request->sos_status === 'help') {
            $activeAlert = \App\Models\Notification::where('student_id', $student->id)
                ->where('type', 'sos')
                ->where('status', '!=', 'resolved')
                ->first();

            if (!$activeAlert) {
                \App\Models\Notification::create([
                    'type' => 'sos',
                    'sender_type' => 'student',
                    'message' => $student->name . ' (' . $student->student_id . ') sent an SOS alert.',
                    'student_id' => $student->id,
                    'class' => $student->class,
                    'latitude' => $student->latitude,
                    'longitude' => $student->longitude,
                    'battery_level' => $student->battery_level,
                    'signal_status' => $student->signal_status,
                    'read' => false,
                    'status' => 'pending',
                ]);
            }
        } else {
            \App\Models\Notification::where('student_id', $student->id)
                ->where('type', 'sos')
                ->where('status', '!=', 'resolved')
                ->update(['status' => 'resolved', 'read' => true]);
        }

        return response()->json([
            'message'    => 'SOS status updated',
            'sos_status' => $request->sos_status,
            'battery_level' => $student->battery_level,
        ]);
    }

    /**
     * Called by mobile app on logout or when app is closed.
     */
    public function goOffline(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
        ]);

        $student = Student::where('student_id', $request->student_id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->status = false;
        $student->save();

        return response()->json(['message' => 'Student marked as offline']);
    }

    /**
     * Upload or update student profile picture.
     * Called by mobile app when a student changes their profile picture.
     */
    public function uploadProfilePicture(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'profile_picture' => 'required|file|mimes:jpg,jpeg,png,webp|max:5120', // 5MB max
        ]);

        $student = Student::where('student_id', $request->student_id)
            ->orWhere('id', $request->student_id)
            ->first();

        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Student not found'], 404);
        }

        // Delete old profile picture if it exists
        if ($student->profile_picture) {
            // Support both old full-URL format and new relative-path format
            $oldPath = $student->profile_picture;
            // Strip any leading domain/URL to get relative path
            if (str_starts_with($oldPath, 'http')) {
                // Extract just the path after /storage/
                $parsed = parse_url($oldPath, PHP_URL_PATH); // e.g. /storage/profile_pictures/xxx.jpg
                $oldPath = ltrim(str_replace('/storage/', '', $parsed), '/');
            }
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($oldPath)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($oldPath);
            }
        }

        try {
            $path = $request->file('profile_picture')->store('profile_pictures', 'public');
            // Store only the relative path so the URL works regardless of host/port
            $student->profile_picture = $path;
            $student->save();

            return response()->json([
                'success' => true,
                'message' => 'Profile picture updated successfully',
                'profile_picture_url' => asset('storage/' . $path)
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to upload profile picture: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload profile picture: ' . $e->getMessage()
            ], 500);
        }
    }
}
