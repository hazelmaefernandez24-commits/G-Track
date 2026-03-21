<?php

namespace App\Http\Controllers;

use App\Models\GoingHomeModel;
use App\Models\Schedule;
use App\Models\StudentDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GoingHomeLogController extends Controller
{
    public function logoutForm()
    {
        $loginLog = false;
        $logoutLog = true;
        return view('user-student.goingHomeLog', ['loginLog' => $loginLog, 'logoutLog' => $logoutLog]);
    }

    public function loginForm()
    {
        $loginLog = true;
        $logoutLog = false;
        return view('user-student.goingHomeLog', ['loginLog' => $loginLog, 'logoutLog' => $logoutLog]);
    }

    public function logTimeOut(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
        ], [
            'student_id.exists' => 'The entered Student ID does not exist in our records.',
        ]);

        $studentId = $request->student_id;

        $student = StudentDetail::where('student_id', $studentId)->first();
        if (!$student) {
            return redirect()->route('goinghomeLogForms.show')->with('error', 'Student not found.');
        }

        $schedule = Schedule::getGoingHomeSchedule($studentId);
        if(!$schedule) {
            return redirect()->route('goinghomeLogForms.show')->with('error', 'No Going Home schedule found.');
        }

        $logoutRecord = GoingHomeModel::getStudentLogRecord($studentId);
        if ($logoutRecord && !$logoutRecord->time_in) {
            return redirect()->route('goinghomeLogForms.show')
                ->with('error', 'You have already logged out this ' . Carbon::parse($logoutRecord->time_out)->format('h:i A') . '  today.');
        }

        if ($schedule) {
            $currentTime = Carbon::now();
            $scheduledTimeOut = Carbon::parse($schedule->time_out);

            if ($currentTime->lt($scheduledTimeOut)) {
                return redirect()->route('goinghomeLogForms.show')->with('error', 'Not time yet!');
            } else {
                $remark = 'On Time';
            }

            $data = [
                'student_id' => $studentId,
                'schedule_name' => $schedule->schedule_name,
                'date_time_out' => now()->toDateString(),
                'time_out' => $currentTime,
                'time_out_remarks' => $remark,
                'created_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'created_at' => now(),
                'updated_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'updated_at' => now(),
            ];

            GoingHomeModel::saveData($data);

            return redirect()->route('goinghomeLogForms.show')->with('success', 'Logged out successfully! You are ' . $remark . '.');
        }

        return redirect()->route('goinghomeLogForms.show')->with('error', 'No Going Home schedule found.');
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
            return redirect()->route('goinghomeLogForms.show')->with('error', 'Student not found.');
        }

        $schedule = Schedule::getGoingHomeSchedule($studentId);
        if(!$schedule) {
            return redirect()->route('goinghomeLogForms.show')->with('error', 'No Going Home schedule found.');
        }

        $logoutRecord = GoingHomeModel::getStudentLogRecord($studentId, $schedule->schedule_name);

        if (!$logoutRecord) {
            return redirect()->route('goinghomeLogForms.show')
                ->with('error', 'You did not log out today.');
        }

        if ($logoutRecord && $logoutRecord->time_in) {
            return redirect()->route('goinghomeLogForms.show')
                ->with('error', 'You have already logged in this ' . Carbon::parse($logoutRecord->time_in)->format('h:i A') . '  today.');
        }

        if ($schedule) {
            $currentDate = Carbon::now()->toDateString();
            $currentTime = Carbon::now();
            $date_time_in = Carbon::parse($schedule->date_time_in)->toDateString();
            $scheduledTimeIn = Carbon::parse($schedule->time_in);
            $graceTime = $schedule->login_grace_period;

            if ($currentDate > $date_time_in &&
                $currentTime->gt($scheduledTimeIn->copy()->addMinutes($graceTime))) {
                $remark = 'Late';
            } else {
                $remark = 'On Time';
            }

            $data = [
                'student_id' => $studentId,
                'date_time_out' => $logoutRecord->date_time_out,
                'date_time_in' => now()->toDateString(),
                'time_in' => $currentTime,
                'time_in_remarks' => $remark,
                'updated_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'updated_at' => now(),
            ];

            GoingHomeModel::saveData($data);

            return redirect()->route('goinghomeLogForms.show')->with('success', 'Data saved successfully! You are ' . $remark . '.');
        }

        return redirect()->route('goinghomeLogForms.show')->with('error', 'No Going Home schedule found.');
    }
}
