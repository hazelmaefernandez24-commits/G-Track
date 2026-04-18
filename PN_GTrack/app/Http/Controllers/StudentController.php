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
        $student->battery_level = $request->battery_level ?? $student->battery_level;
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
        ]);

        $student = Student::where('student_id', $request->student_id)->first();

        if (!$student) {
            return response()->json(['message' => 'Student not found'], 404);
        }

        $student->sos_status  = $request->sos_status;
        $student->last_update = now()->format('M d, Y h:i A');
        if ($request->latitude)  $student->latitude  = $request->latitude;
        if ($request->longitude) $student->longitude = $request->longitude;
        if ($request->battery)   $student->battery_level = $request->battery;
        if ($request->signal)    $student->signal_status = $request->signal;
        $student->save();

        // Also log in notifications table
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
            // "I am Safe" transition - Resolve existing SOS alerts
            \App\Models\Notification::where('student_id', $student->id)
                ->where('type', 'sos')
                ->where('status', '!=', 'resolved')
                ->update(['status' => 'resolved', 'read' => true]);
        }

        return response()->json([
            'message'    => 'SOS status updated',
            'sos_status' => $request->sos_status
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
}
