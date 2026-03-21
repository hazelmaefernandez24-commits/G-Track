<?php

namespace App\Http\Controllers;

use App\Models\PNUser;
use App\Models\StudentDetail;
use App\Models\Schedule;
use App\Models\NotificationView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\Academic;
use App\Models\AcademicSchedule;
use App\Models\EventSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AcademicLogController extends Controller
{
    public function logoutForm()
    {
        $loginLog = false;
        $logoutLog = true;
        return view('user-student.academicLog', ['loginLog' => $loginLog, 'logoutLog' => $logoutLog]);
    }

    public function loginForm()
    {
        $loginLog = true;
        $logoutLog = false;
        return view('user-student.academicLog', ['loginLog' => $loginLog, 'logoutLog' => $logoutLog]);
    }

    public function logTimeOut(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
        ], [
            'student_id.exists' => 'The entered Student ID does not exist in our records.',
        ]);

        $student = StudentDetail::get_student($request->student_id);
        $date = Carbon::now()->toDateString();

        $logoutRecord = Academic::getStudentLogRecord( $request->student_id, $date);
        if ($logoutRecord && $logoutRecord->time_out) {
            return redirect()->route('academicLogForms.show')->with('error', 'You have already logged out this ' . Carbon::parse($logoutRecord->time_out)->format('h:i A') . '  today.');
        }

        $isSunday = now()->isSunday();
        if($isSunday){
            return redirect()->route('academicLogForms.show')->with('error', 'No Academic Schedue for Sunday');
        }
        $today = now()->toDateString();
        $schedule = EventSchedule::get_schedule($today);
        if(!$schedule){
            $schedule = Schedule::get_academic_schedule_by_id($request->student_id, $today);
            if (!$schedule) {
                $schedule = Schedule::get_academic_schedule($student->batch, $student->group, $today);
            }
        }

        if (!$schedule) {
            return redirect()->back()->with('error', 'No schedule found for this student on the selected date.');
        }

        if ($schedule) {
            $currentTime = Carbon::now();
            $scheduledTimeOut = Carbon::parse($schedule->time_out);
            $scheduledTimeIn = Carbon::parse($schedule->time_in);
            $graceTime = $schedule->grace_period_logout_minutes; // Fixed column name

            if ($currentTime->gt($scheduledTimeIn)) {
                return redirect()->route('academicLogForms.show')
                    ->with('error', 'Time out period has ended! Please contact your educator for assistance.');
            }

            if ($graceTime === null) {
                if ($currentTime->lt($scheduledTimeOut)) {
                    $remark = 'Early';
                } elseif ($currentTime->gt($scheduledTimeOut)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            } else {
                $graceStartTime = $scheduledTimeOut->copy()->subMinutes($graceTime);
                if ($currentTime->lt($graceStartTime)) {
                    $remark = 'Early';
                } elseif ($currentTime->gt($scheduledTimeOut)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            }

            $data = [
                'semester_id' => $schedule->semester_id ?? 1,
                'student_id' =>  $request->student_id,
                'date' => now()->toDateString(),
                'time_out' => $currentTime,
                'time_out_remark' => $remark,
                'created_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'created_at' => now(),
                'updated_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'updated_at' => now(),
            ];

            Academic::saveData($data);

            return redirect()->route('academicLogForms.show')->with('success', 'Data saved successfully! You are ' . $remark . '.');
        }

        return redirect()->route('academicLogForms.show')->with('error', 'No academic schedule found for this student.');
    }

    public function logTimeIn(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
        ], [
            'student_id.exists' => 'The entered Student ID does not exist in our records.',
        ]);

        $studentId = $request->student_id;

        $date = Carbon::now()->toDateString();

        $logoutRecord = Academic::getStudentLogRecord($studentId, $date);

        if (!$logoutRecord) {
            return redirect()->route('academicLogForms.show')
                ->with('error', 'You did not log out today.');
        }

        if ($logoutRecord->time_in) {
            return redirect()->route('academicLogForms.show')
                ->with('error', 'You have already log in this ' . Carbon::parse($logoutRecord->time_out)->format('h:i A') . '  today.');
        }

        $isSunday = now()->isSunday();
        if($isSunday){
            return redirect()->back()->with('error', 'No Academic Schedue for Sunday');
        }
        $student = StudentDetail::get_student($request->student_id);
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
        }
        $today = now();
        $schedule = EventSchedule::get_schedule($today);
        if(!$schedule){
            $schedule = Schedule::get_academic_schedule_by_id($request->student_id, $today);
            if (!$schedule) {
                $schedule = Schedule::get_academic_schedule($student->batch, $student->group, $today);
            }
        }

        if ($schedule) {
            $currentTime = Carbon::now();
            $scheduledTimeIn = Carbon::parse($schedule->time_in);
            $graceTime = $schedule->grace_period_login_minutes;

            if ($graceTime === null) {
                // No grace period - exact timing
                if ($currentTime->lt($scheduledTimeIn)) {
                    $remark = 'Early';
                } elseif ($currentTime->gt($scheduledTimeIn)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            } else {
                // Has grace period
                $graceEndTime = $scheduledTimeIn->copy()->addMinutes($graceTime);
                if ($currentTime->lt($scheduledTimeIn)) {
                    $remark = 'Early';
                } elseif ($currentTime->gt($graceEndTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            }

            $data = [
                'student_id' => $studentId,
                'date' => now()->toDateString(),
                'time_in' => $currentTime,
                'time_in_remark' => $remark,
                'updated_by' => $student->user->user_fname . ' ' . $student->user->user_lname,
                'updated_at' => now(),
            ];

            Academic::saveData($data);

            return redirect()->route('academicLogForms.show')->with('success', 'Data saved successfully! You are ' . $remark . '.');
        }

        // No schedule found
        return redirect()->route('academicLogForms.show')->with('error', 'No academic schedule found for this student.');
    }

    /**
     * Get the current active semester for a student
     * This method tries to determine the current semester from active schedules
     */
    private function getCurrentActiveSemester($student)
    {
        $today = Carbon::now()->format('l');
        $currentDate = Carbon::now()->toDateString();

        // First, try to find an active irregular schedule for this student
        $activeSchedule = Schedule::where('student_id', $student->student_id)
            ->where('day_of_week', $today)
            ->where('schedule_type', 'academic')
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate);
            })
            ->where('is_deleted', false)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($activeSchedule && $activeSchedule->semester_id) {
            return $activeSchedule->semester_id;
        }

        // If no irregular schedule, try to find an active batch schedule
        $batchSchedule = Schedule::where('batch', $student->batch)
            ->where('pn_group', $student->group)
            ->where('day_of_week', $today)
            ->where('schedule_type', 'academic')
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('start_date')
                    ->orWhere('start_date', '<=', $currentDate);
            })
            ->where(function ($query) use ($currentDate) {
                $query->whereNull('end_date')
                    ->orWhere('end_date', '>=', $currentDate);
            })
            ->where('is_deleted', false)
            ->orderBy('updated_at', 'desc')
            ->first();

        if ($batchSchedule && $batchSchedule->semester_id) {
            return $batchSchedule->semester_id;
        }

        // If no active schedule found, return null to get schedules from any semester
        return null;
    }
}
