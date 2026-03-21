<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class InternLogController extends Controller
{
    public function logTimeOut(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('academic.logout.form');
        }

        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
        ], [
            'student_id.exists' => 'The entered Student ID does not exist in our records.',
        ]);

        $student = StudentDetail::where('student_id', $request->student_id)->first();
        if (!$student) {
            return redirect()->route('academicLogForms.show')->with('error', 'Student not found.');
        }

        $today = Carbon::now()->format('l');

        Schedule::clearBootedModels();

        $scheduleResult = $this->getCurrentActiveSchedule($student, $today);
        $schedule = $scheduleResult['schedule'];
        $scheduleType = $scheduleResult['type'];

        // Check if schedule exists and validate time restrictions
        if (!$schedule) {
            return redirect()->route('academicLogForms.show')
                ->with('error', 'No schedule set for today! Please contact your educator for assistance.');
        }

        // Check if it's too late to logout (after scheduled end time - NO GRACE PERIOD)
        $scheduleEndTime = Carbon::parse($schedule->getRawTimeIn()); // When academic period ends
        $currentTime = Carbon::parse(now());

        if ($currentTime > $scheduleEndTime) {
            return redirect()->route('academicLogForms.show')
                ->with('error', 'Time out period has ended! Please contact your educator for assistance.');
        }

        $academic = Academic::where([
            ['student_id', $request->student_id],
            ['academic_date', now()->toDateString()]
        ])->first();

        if (!$academic) {
            $academic = Academic::create([
                'student_id' => $request->student_id,
                'academic_date' => now()->toDateString(),
            ]);
        }

        if ($academic->monitor_logged_out) {
            return redirect()->route('academicLogForms.show')->with('error', 'Monitor has already logged you out. You cannot log out again.');
        }

        if ($academic->time_out) {
            return redirect()->route('academicLogForms.show')->with('error', 'You already logged out for this date.');
        }

        // Calculate remark based on schedule (if exists) with dynamic grace period
        $currentTime = Carbon::parse(now());
        $createdBy = $student->user->user_fname . ' ' . $student->user->user_lname;

        $remark = 'On Time'; // Default if no schedule
        if ($schedule) {
            $scheduleStartTime = Carbon::parse($schedule->getRawTimeOut());
            $gracePeriodMinutes = $schedule->grace_period_logout_minutes;

            // Dynamic grace period logic
            if ($gracePeriodMinutes !== null) {
                $graceStartTime = $scheduleStartTime->copy()->subMinutes($gracePeriodMinutes);

                if ($currentTime->lessThan($graceStartTime)) {
                    $remark = 'Early';
                } elseif ($currentTime->greaterThan($scheduleStartTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            } else {
                // No grace period - exact timing
                if ($currentTime->lessThan($scheduleStartTime)) {
                    $remark = 'Early';
                } elseif ($currentTime->greaterThan($scheduleStartTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time'; // Exact time
                }
            }
        }

        Academic::updateOrCreate(
            [
                'student_id' => $request->student_id,
                'academic_date' => now()->toDateString(),
            ],
            [

                'time_out' => now(),
                'time_out_remark' => $remark,
                'created_by' => $createdBy,
                'created_at' => now()
            ]
        );

        // Create notification for this activity
        $isLate = ($remark === 'Late');
        NotificationHistory::createNotification(
            $request->student_id,
            $student->batch,
            'time_out',
            'academic',
            $isLate,
            $remark  // Pass the actual timing status (Early/On Time/Late)
        );

        $log_remark = $academic->where('student_id', $request->student_id)
            ->whereDate('academic_date', now()->toDateString())
            ->value('time_out_remark'); // Get single value instead of collection

        return redirect()->route('academicLogForms.show')->with('success', 'Time-out logged successfully. You are ' . $log_remark . '.');
    }

    /**
     * Store a new academic log (login).
     */
    public function logTimeIn(Request $request)
    {
        if ($request->isMethod('get')) {
            return redirect()->route('academic.login.form');
        }

        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
        ], [
            'student_id.exists' => 'The entered Student ID does not exist in our records.',
        ]);

        $student = StudentDetail::where('student_id', $request->student_id)->first();
        if (!$student) {
            return redirect()->route('academicLogForms.show')->with('error', 'Student not found.');
        }

        $today = Carbon::now()->format('l');

        Schedule::clearBootedModels();

        $scheduleResult = $this->getCurrentActiveSchedule($student, $today);
        $schedule = $scheduleResult['schedule'];
        $scheduleType = $scheduleResult['type'];

        if (!$schedule) {
            Log::warning('Student attempted academic login with no schedule', [
                'student_id' => $student->student_id,
                'batch' => $student->batch,
                'group' => $student->group,
                'day' => $today,
            ]);
            return redirect()->route('academicLogForms.show')
                ->with('error', 'No schedule set for today! Please contact your educator for assistance.');
        }

        $academic = Academic::where('student_id', $request->student_id)
            ->whereDate('academic_date', now()->toDateString())
            ->first();

        // Create academic log entry if it doesn't exist for today
        if ($academic == null) {
            $academic = Academic::create([
                'student_id' => $request->student_id,
                'academic_date' => now()->toDateString(),
            ]);
        }

        if (!$academic->time_out) {
            return redirect()->route('academicLogForms.show')->with('error', 'You have not log out for this date.');
        }

        // Check if monitor already logged in this student (priority check)
        if ($academic->monitor_logged_in) {
            return redirect()->route('academicLogForms.show')->with('error', 'Monitor has already logged you in. You cannot log in again.');
        }

        if ($academic->time_in) {
            return redirect()->route('academicLogForms.show')->with('error', 'You already log in for this date.');
        }

        // Calculate remark based on schedule with dynamic grace period
        $currentTime = Carbon::parse(now());
        $updatedBy = $student->user->user_fname . ' ' . $student->user->user_lname;

        $remark = 'On Time'; // Default
        if ($schedule) {
            $scheduleEndTime = Carbon::parse($schedule->getRawTimeIn()); // When students should return

            // Get login grace period from schedule (no default - null means no grace period)
            $gracePeriodMinutes = $schedule->grace_period_login_minutes;

            // Log the schedule being used for debugging
            Log::info('Academic login remark calculation', [
                'student_id' => $student->student_id,
                'schedule_type' => $scheduleType,
                'schedule_id' => $schedule->schedule_id,
                'current_time' => $currentTime->format('H:i:s'),
                'schedule_time_in' => $schedule->getRawTimeIn(),
                'grace_period_minutes' => $gracePeriodMinutes,
                'schedule_student_id' => $schedule->student_id,
                'schedule_batch' => $schedule->batch,
                'schedule_group' => $schedule->pn_group
            ]);

            // Dynamic grace period logic
            if ($gracePeriodMinutes !== null) {
                $graceEndTime = $scheduleEndTime->copy()->addMinutes($gracePeriodMinutes);

                if ($currentTime->lessThan($scheduleEndTime)) {
                    $remark = 'Early';
                } elseif ($currentTime->greaterThan($graceEndTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time'; // Within grace period
                }

                Log::info('Academic login with grace period', [
                    'student_id' => $student->student_id,
                    'schedule_end_time' => $scheduleEndTime->format('H:i:s'),
                    'grace_end_time' => $graceEndTime->format('H:i:s'),
                    'current_time' => $currentTime->format('H:i:s'),
                    'remark' => $remark
                ]);
            } else {
                // No grace period - exact timing
                if ($currentTime->lessThan($scheduleEndTime)) {
                    $remark = 'Early';
                } elseif ($currentTime->greaterThan($scheduleEndTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time'; // Exact time
                }

                Log::info('Academic login without grace period', [
                    'student_id' => $student->student_id,
                    'schedule_end_time' => $scheduleEndTime->format('H:i:s'),
                    'current_time' => $currentTime->format('H:i:s'),
                    'remark' => $remark
                ]);
            }
        }

        $academic->update([
            'time_in' => now(),
            'time_in_remark' => $remark,
            'updated_by' => $updatedBy,
            'updated_at' => now()
        ]);

        // Create notification for this activity
        $isLate = ($remark === 'Late');
        NotificationHistory::createNotification(
            $request->student_id,
            $student->batch,
            'time_in',
            'academic',
            $isLate,
            $remark  // Pass the actual timing status (Early/On Time/Late)
        );

        $lateRemark = $academic->where('student_id', $request->student_id)
            ->whereDate('academic_date', now()->toDateString())
            ->value('time_in_remark'); // Get single value instead of collection

        return redirect()->route('academicLogForms.show')
            ->with('success', 'Time-in logged successfully. You are ' . $lateRemark . '.');
    }
}
