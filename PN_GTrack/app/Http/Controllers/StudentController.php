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

        $student = Student::where('student_id', $request->student_id)->first();
        if ($student) {
            $student->recordStatus('online');
        }

        // Return student details for the mobile application
        return response()->json([
            'message' => 'Login successful',
            'student' => $student,
            'role' => 'student'
        ]);
    }

    public function apiResetPassword(Request $request)
    {
        $request->merge([
            'new_password' => $request->input('new_password') ?? $request->input('password'),
        ]);

        $validated = $request->validate([
            'student_id' => ['required', 'string'],
            'current_password' => ['required', 'string'],
            'new_password' => ['required', 'string', 'min:6', 'different:current_password'],
        ]);

        $student = Student::where('student_id', $validated['student_id'])
            ->orWhere('id', $validated['student_id'])
            ->first();
        $studentAuth = $student
            ? StudentAuth::where('student_id', $student->student_id)->first()
            : null;

        if (! $studentAuth) {
            return response()->json([
                'success' => false,
                'message' => 'Student account not found.',
            ], 404);
        }

        if (! Hash::check($validated['current_password'], $studentAuth->password)) {
            return response()->json([
                'success' => false,
                'message' => 'The current password is incorrect.',
            ], 401);
        }

        $studentAuth->password = Hash::make($validated['new_password']);
        $studentAuth->save();

        return response()->json([
            'success' => true,
            'message' => 'Password reset successfully.',
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

        $student->recordStatus('online');

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

        if ($request->filled('latitude') && $request->filled('longitude')) {
            $student->locations()->create([
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'recorded_at' => now(),
            ]);
        }
        $student->recordStatus('online', $request->sos_status);

        if ($request->sos_status === 'safe') {
            \DB::table('emergency_alerts')->where('student_id', $student->id)
                ->where('status', '!=', 'resolved')
                ->update(['status' => 'resolved', 'resolved_at' => now()]);
        }
        // SOS notification creation (with video) is handled by SosAlertController.

        return response()->json([
            'message'    => 'SOS status updated',
            'sos_status' => $request->sos_status,
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

        $student->recordStatus('offline');

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
