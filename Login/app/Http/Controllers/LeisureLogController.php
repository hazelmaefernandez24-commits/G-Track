<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentDetail;
use App\Models\Going_out;
use App\Models\LeisureSchedule;
use App\Models\Schedule;
use App\Models\AcademicLog;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class LeisureLogController extends Controller
{
    public function showLogForms()
    {
        $goingout = true;
        $academic = false;
        $intern = false;
        $goinghome = false;

        // Get current session information for the authenticated user if available
        $currentSessions = null;
        $canStartNewSession = true;

        return view('user-student.logForms', [
            'academic' => $academic,
            'goingout' => $goingout,
            'intern' => $intern,
            'goinghome' => $goinghome,
            'currentSessions' => $currentSessions,
            'canStartNewSession' => $canStartNewSession
        ]);
    }

    public function logoutForm()
    {
        $loginLog = false;
        $logoutLog = true;
        return view('user-student.goingOutLog', ['loginLog' => $loginLog, 'logoutLog' => $logoutLog]);
    }

    public function loginForm()
    {
        $loginLog = true;
        $logoutLog = false;
        return view('user-student.goingOutLog', ['loginLog' => $loginLog, 'logoutLog' => $logoutLog]);
    }

    public function logTimeOut(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
            'destination' => 'required|string',
            'purpose' => 'required|string',
        ], [
            'student_id.exists' => 'The entered Student ID does not exist in our records.',
        ]);

        $studentId = $request->student_id;

        $student = StudentDetail::where('student_id', $studentId)->first();
        if (!$student) {
            return redirect()->route('goingOutLogForms.show')->with('error', 'Student not found.');
        }

        // Check if student can start a new session (must complete previous session first)
        $activeSession = Going_out::getCurrentActiveSession($studentId, now()->toDateString());

        if ($activeSession && $activeSession->time_out && !$activeSession->time_in) {
            return redirect()->route('goingOutLogForms.show')
                ->with('error', 'You have already logged out this ' . Carbon::parse($activeSession->time_out)->format('h:i A') . '  today.');
        }

        $schedule = Schedule::getIrregLeisureSchedule($studentId);

        if (!$schedule) {
            $schedule = Schedule::getRegLeisureSchedule($student->user->gender);
        }

        if ($schedule) {
            $currentTime = Carbon::now();
            $scheduledTimeOut = Carbon::parse($schedule->time_out);
            $scheduledTimeIn = Carbon::parse($schedule->time_in);
            $graceTime = $schedule->grace_period_logout_minutes;

            if ($currentTime->gt($scheduledTimeIn)) {
                return redirect()->route('goingOutLogForms.show')
                    ->with('error', 'Time out period has ended! Please contact your educator for assistance.');
            }

            // Handle null grace period for unique leisure schedules
            if ($graceTime === null) {
                // No grace period - must be exact time or later
                if ($currentTime->lt($scheduledTimeOut)) {
                    return redirect()->route('goingOutLogForms.show')->with('error', 'Not time yet!');
                } else {
                    $remark = 'On Time';
                }
            } else {
                // Has grace period - can log out early
                if ($currentTime->lt($scheduledTimeOut->copy()->subMinutes($graceTime))) {
                    return redirect()->route('goingOutLogForms.show')->with('error', 'Not time yet!');
                } else {
                    $remark = 'On Time';
                }
            }

            // Handle session creation properly
            $todayDate = now()->toDateString();
            $createdBy = $student->user->user_fname . ' ' . $student->user->user_lname;

            if ($activeSession && !$activeSession->time_out) {
                // Update existing active session
                $activeSession->update([
                    'destination' => $request->destination,
                    'purpose' => $request->purpose,
                    'time_out' => $currentTime,
                    'time_out_remark' => $remark,
                    'updated_by' => $createdBy,
                    'updated_at' => now()
                ]);
            } else {
                // Create new session
                $sessionNumber = Going_out::getNextSessionNumber($studentId, $todayDate);

                Going_out::create([
                    'student_id' => $studentId,
                    'going_out_date' => $todayDate,
                    'session_number' => $sessionNumber,
                    'session_status' => 'active',
                    'destination' => $request->destination,
                    'purpose' => $request->purpose,
                    'time_out' => $currentTime,
                    'time_out_remark' => $remark,
                    'created_by' => $createdBy,
                    'created_at' => now(),
                    'updated_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                    'updated_at' => now(),
                ]);
            }

            return redirect()->route('goingOutLogForms.show')->with('success', 'Logged out successfully! You are ' . $remark . '.');
        }

        return redirect()->route('goingOutLogForms.show')->with('error', 'No Leisure schedule found.');
    }

    public function logTimeIn(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
        ], [
            'student_id.exists' => 'The entered Student ID does not exist in our records.',
        ]);

        $studentId = $request->student_id;

        $student = StudentDetail::where('student_id', $studentId)->first();
        if (!$student) {
            return redirect()->route('goingOutLogForms.show')->with('error', 'Student not found.');
        }

        // Get the current active session
        $activeSession = Going_out::getCurrentActiveSession($studentId, now()->toDateString());

        if (!$activeSession) {
            return redirect()->route('goingOutLogForms.show')
                ->with('error', 'You have not logged out yet or no active session found.');
        }

        if (!$activeSession->time_out) {
            return redirect()->route('goingOutLogForms.show')
                ->with('error', 'You have not logged out for this session.');
        }

        if ($activeSession->time_in) {
            return redirect()->route('goingOutLogForms.show')
                ->with('error', 'You have already logged in this ' . $activeSession->time_in->format('H:i A') . '  today.');
        }

        $schedule = Schedule::getIrregLeisureSchedule($studentId);

        if (!$schedule) {
            $schedule = Schedule::getRegLeisureSchedule($student->user->gender);
        }

        if ($schedule) {
            $currentTime = Carbon::now();
            $scheduledTimeIn = Carbon::parse($schedule->time_in);
            $graceTime = $schedule->grace_period_login_minutes;

            // Handle null grace period for unique leisure schedules
            if ($graceTime === null) {
                // No grace period - must be exact time or earlier
                if ($currentTime->gt($scheduledTimeIn)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            } else {
                // Has grace period - can log in late
                if ($currentTime->gt($scheduledTimeIn->copy()->addMinutes($graceTime))) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            }

            // Update the active session with login information
            $activeSession->update([
                'time_in' => $currentTime,
                'time_in_remark' => $remark,
                'session_status' => 'completed',
                'updated_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'updated_at' => now()
            ]);

            return redirect()->route('goingOutLogForms.show')->with('success', 'Data saved successfully! You are ' . $remark . '.');
        }

        return redirect()->route('goingOutLogForms.show')->with('error', 'No Leisure schedule found.');
    }
}
