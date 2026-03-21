<?php

namespace App\Http\Controllers;

use App\Models\InternLogModel;
use App\Models\InternshipSchedule;
use App\Models\StudentDetail;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InternLogsController extends Controller
{
    public function logoutForm()
    {
        $loginLog = false;
        $logoutLog = true;
        return view('user-student.internLog', ['loginLog' => $loginLog, 'logoutLog' => $logoutLog]);
    }

    public function loginForm()
    {
        $loginLog = true;
        $logoutLog = false;
        return view('user-student.internLog', ['loginLog' => $loginLog, 'logoutLog' => $logoutLog]);
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
            return redirect()->route('internLogForms.show')->with('error', 'Student not found.');
        }

        $logoutRecord = InternLogModel::getStudentLogRecord($studentId);

        if ($logoutRecord && !$logoutRecord->time_in) {
            return redirect()->route('internLogForms.show')
                ->with('error', 'You have already logged out this ' . Carbon::parse($logoutRecord->time_out)->format('h:i A') . '  today.');
        }

        $schedule = InternshipSchedule::getInternSchedule($studentId);

        if(!$schedule){
            return redirect()->route('internLogForms.show')
                ->with('error', 'No internship schedule set.');
        }
        $currentTime = Carbon::now();

        if($schedule->time_in <= $currentTime){
            return redirect()->route('internLogForms.show')
                ->with('error', 'The time in is ended.');
        }

        $today = now()->format('l');
        $days = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true);

        if (!in_array($today, $days)) {
            return redirect()->route('internLogForms.show')->with('error', 'No duty today.');
        }

        if ($schedule) {
            $scheduledTimeOut = Carbon::parse($schedule->time_out);

            $remark = $currentTime
                ? ($currentTime->lt($scheduledTimeOut) ? 'Early'
                : ($currentTime->gt($scheduledTimeOut) ? 'Late' : 'On Time'))
                : null;

            $data = [
                'student_id' => $studentId,
                'date' => now()->toDateString(),
                'time_out' => $currentTime,
                'time_out_remark' => $remark,
                'created_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'created_at' => now(),
            ];

            InternLogModel::saveData($data);

            return redirect()->route('internLogForms.show')->with('success', 'Logged out successfully! You are ' . $remark . '.');
        }

        return redirect()->route('internLogForms.show')->with('error', 'No Leisure schedule found.');
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
            return redirect()->route('internLogForms.show')->with('error', 'Student not found.');
        }

        $logoutRecord = InternLogModel::getStudentLogRecord($studentId);

        if (!$logoutRecord) {
            return redirect()->route('internLogForms.show')
                ->with('error', 'You did not log out today.');
        }

        if ($logoutRecord && $logoutRecord->time_in) {
            return redirect()->route('internLogForms.show')
                ->with('error', 'You have already logged in this ' . Carbon::parse($logoutRecord->time_in)->format('h:i A') . '  today.');
        }

        $schedule = InternshipSchedule::getInternSchedule($studentId);

        if(!$schedule){
            return redirect()->route('internLogForms.show')
                ->with('error', 'No internship schedule set.');
        }

        $today = now()->format('l');
        $days = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true);

        if (!in_array($today, $days)) {
            return redirect()->route('internLogForms.show')->with('error', 'No duty today.');
        }

        if ($schedule) {
            $currentTime = Carbon::now();
            $scheduledTimeIn = Carbon::parse($schedule->time_in);
            // $graceTime = $schedule->login_grace_period;

            $remark = $currentTime
                ? ($currentTime->gt($scheduledTimeIn) ? 'Late'
                : ($currentTime->lt($scheduledTimeIn) ? 'Early' : 'On Time'))
                : null;

            $data = [
                'student_id' => $studentId,
                'date' => now()->toDateString(),
                'time_in' => $currentTime,
                'time_in_remark' => $remark,
                'updated_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'updated_at' => now(),
            ];

            InternLogModel::saveData($data);

            return redirect()->route('internLogForms.show')->with('success', 'Data saved successfully! You are ' . $remark . '.');
        }

        return redirect()->route('internLogForms.show')->with('error', 'No Leisure schedule found.');
    }
}
