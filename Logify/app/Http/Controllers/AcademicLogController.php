<?php

namespace App\Http\Controllers;

use App\Models\Academic;
use App\Models\StudentDetail;
use App\Models\Schedule;
use App\Models\NotificationView;
use App\Models\NotificationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class AcademicLogController extends Controller
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

    /**
     * Monitor past logs with filtering capabilities.
     */
    public function pastLogs(Request $request)
    {
        $query = Academic::with(['studentDetail'])
            ->orderBy('academic_date', 'desc')
            ->orderBy('time_out', 'desc');

        if ($request->has('month') && !empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year = date('Y', strtotime($request->month));
            $query->whereMonth('academic_date', $month)
                ->whereYear('academic_date', $year);
        }

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('academic_date', $request->date);
        }

        $academicLogs = $query->paginate(20)->appends($request->query());

        if ($academicLogs->isEmpty() && $request->has('date') && !empty($request->date)) {
            Log::info('No logs found for selected date, creating empty logs.', [
                'date' => $request->date,
            ]);
            $allStudents = StudentDetail::all();

            foreach ($allStudents as $student) {
                Academic::firstOrCreate([
                    'student_id' => $student->student_id,
                    'academic_date' => $request->date,
                ], [
                    'time_out' => null,
                    'time_out_remark' => 'No Time Out',
                    'time_in' => null,
                    'time_in_remark' => 'No Time In',
                ]);
            }

            $academicLogs = Academic::with('studentDetail')
                ->whereDate('academic_date', $request->date)
                ->paginate(20)
                ->appends($request->query());
        }

        $batches = StudentDetail::distinct()->pluck('batch')->sort();
        $groups = StudentDetail::distinct()->pluck('group')->sort();

        return view('user-educator.academicmonitor', [
            'academicLogs' => $academicLogs,
            'isPastLogs' => true,
            'selectedMonth' => $request->month ?? '',
            'selectedDate' => $request->date ?? '',
            'batches' => $batches,
            'groups' => $groups,
        ]);
    }



    /**
     * Update educator consideration in an academic log.
     */
    public function updateConsideration(Request $request, $id)
    {
        try {
            $request->validate([
                'educator_consideration' => 'required|in:Excused,Not Excused',
                'consideration_type' => 'required|in:time_in,time_out'
            ]);

            $academic = Academic::findOrFail($id);
            $considerationType = $request->consideration_type;

            if ($considerationType === 'time_out') {
                // Validate time out consideration
                if (!$academic->time_out) {
                    Log::warning('Cannot set time out consideration: Student has not logged time out', [
                        'academic_id' => $id,
                        'student_id' => $academic->student_id
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set consideration: Student must log time out first'
                    ]);
                }

                if ($academic->time_out_remark !== 'Late') {
                    Log::warning('Cannot set time out consideration: Student is not late for time out', [
                        'academic_id' => $id,
                        'student_id' => $academic->student_id,
                        'time_out_remark' => $academic->time_out_remark
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set consideration: Student must be late to set any consideration'
                    ]);
                }

                $updateField = 'time_out_consideration';
                $logMessage = 'Updated academic time out consideration';
            } else {
                // Validate time in consideration (existing logic)
                if (!$academic->time_in) {
                    Log::warning('Cannot set time in consideration: Student has not logged time in', [
                        'academic_id' => $id,
                        'student_id' => $academic->student_id
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set consideration: Student must log time in first'
                    ]);
                }

                if ($academic->time_in_remark !== 'Late') {
                    Log::warning('Cannot set time in consideration: Student is not late for time in', [
                        'academic_id' => $id,
                        'student_id' => $academic->student_id,
                        'time_in_remark' => $academic->time_in_remark
                    ]);
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set consideration: Student must be late to set any consideration'
                    ]);
                }

                $updateField = 'educator_consideration';
                $logMessage = 'Updated academic time in consideration';
            }

            DB::beginTransaction();

            try {
                $academic->update([
                    $updateField => $request->educator_consideration
                ]);

                DB::commit();
                Log::info($logMessage, [
                    'academic_id' => $id,
                    'student_id' => $academic->student_id,
                    'consideration_type' => $considerationType,
                    'consideration' => $request->educator_consideration
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Consideration updated successfully.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            Log::error('Failed to update academic consideration', [
                'error' => $e->getMessage(),
                'academic_id' => $id,
                'consideration_type' => $request->consideration_type ?? 'unknown'
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to update consideration: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get the most current active schedule for a student
     * This method ensures we always get the latest schedule that should be followed
     */
    private function getCurrentActiveSchedule($student, $today)
    {
        // Disable query log temporarily to ensure fresh queries
        DB::disableQueryLog();

        Log::info('Fetching current active schedule', [
            'student_id' => $student->student_id,
            'batch' => $student->batch,
            'group' => $student->group,
            'day' => $today,
            'query_time' => now()->format('Y-m-d H:i:s.u')
        ]);

        // First priority: Check for irregular schedule (student-specific)
        $irregularSchedule = Schedule::where([
            ['student_id', $student->student_id],
            ['day_of_week', $today],
            ['schedule_type', 'academic'] // Only look for academic irregular schedules
        ])->where(function ($query) {
            $query->whereNull('valid_until') // Permanent schedule
                ->orWhere('valid_until', '>=', Carbon::today()); // Or valid today/future
        })
        ->orderBy('updated_at', 'desc') // Most recently updated first
        ->orderBy('created_at', 'desc')  // Then most recently created
        ->first();

        if ($irregularSchedule) {
            Log::info('Using irregular schedule for student', [
                'student_id' => $student->student_id,
                'schedule_id' => $irregularSchedule->schedule_id,
                'time_out' => $irregularSchedule->getRawTimeOut(),
                'time_in' => $irregularSchedule->getRawTimeIn(),
                'updated_at' => $irregularSchedule->updated_at,
                'valid_until' => $irregularSchedule->valid_until,
                'query_time' => now()->format('Y-m-d H:i:s.u')
            ]);

            // Re-enable query log
            DB::enableQueryLog();

            return ['schedule' => $irregularSchedule, 'type' => 'irregular'];
        }

        // Second priority: Check for batch schedule (group-based)
        $batchSchedule = Schedule::where([
            ['batch', $student->batch],
            ['pn_group', $student->group],
            ['day_of_week', $today],
            ['schedule_type', 'academic'] // Only look for academic batch schedules
        ])->where(function ($query) {
            $query->whereNull('valid_until') // Permanent schedule
                ->orWhere('valid_until', '>=', Carbon::today()); // Or valid today/future
        })
        ->orderBy('updated_at', 'desc') // Most recently updated first
        ->orderBy('created_at', 'desc')  // Then most recently created
        ->first();

        if ($batchSchedule) {
            Log::info('Using batch schedule for student', [
                'student_id' => $student->student_id,
                'batch' => $student->batch,
                'group' => $student->group,
                'schedule_id' => $batchSchedule->schedule_id,
                'time_out' => $batchSchedule->getRawTimeOut(),
                'time_in' => $batchSchedule->getRawTimeIn(),
                'updated_at' => $batchSchedule->updated_at,
                'valid_until' => $batchSchedule->valid_until,
                'query_time' => now()->format('Y-m-d H:i:s.u')
            ]);

            // Re-enable query log
            DB::enableQueryLog();

            return ['schedule' => $batchSchedule, 'type' => 'batch'];
        }

        Log::warning('No active schedule found for student', [
            'student_id' => $student->student_id,
            'batch' => $student->batch,
            'group' => $student->group,
            'day' => $today,
            'query_time' => now()->format('Y-m-d H:i:s.u')
        ]);

        // Re-enable query log
        DB::enableQueryLog();

        return ['schedule' => null, 'type' => 'none'];
    }

    /**
     * Debug function to check schedule for a student
     */
    public function debugSchedule(Request $request)
    {
        $studentId = $request->get('student_id');
        if (!$studentId) {
            return response()->json(['error' => 'Please provide student_id parameter']);
        }

        $student = StudentDetail::where('student_id', $studentId)->first();
        if (!$student) {
            return response()->json(['error' => 'Student not found']);
        }

        $today = Carbon::now()->format('l');

        // Check irregular schedule
        $irregularSchedule = Schedule::where([
            ['student_id', $student->student_id],
            ['day_of_week', $today],
            ['schedule_type', 'academic'] // Only look for academic irregular schedules
        ])->where(function ($query) {
            $query->whereNull('valid_until')
                ->orWhereDate('valid_until', '>=', Carbon::today());
        })->first();

        // Check batch schedule
        $batchSchedule = Schedule::where([
            ['batch', $student->batch],
            ['pn_group', $student->group],
            ['day_of_week', $today],
            ['schedule_type', 'academic'] // Only look for academic batch schedules
        ])->where(function ($query) {
            $query->whereNull('valid_until')
                ->orWhereDate('valid_until', '>=', Carbon::today());
        })->first();

        $currentTime = Carbon::parse(now());

        return response()->json([
            'student_info' => [
                'student_id' => $student->student_id,
                'batch' => $student->batch,
                'group' => $student->group,
                'name' => $student->user->name ?? 'N/A'
            ],
            'current_info' => [
                'current_time' => $currentTime->format('Y-m-d H:i:s'),
                'current_day' => $today,
                'today_date' => Carbon::today()->format('Y-m-d')
            ],
            'irregular_schedule' => $irregularSchedule ? [
                'schedule_id' => $irregularSchedule->schedule_id,
                'time_out' => $irregularSchedule->time_out,
                'time_in' => $irregularSchedule->time_in,
                'day_of_week' => $irregularSchedule->day_of_week,
                'valid_until' => $irregularSchedule->valid_until,
                'can_logout_now' => $currentTime <= Carbon::parse($irregularSchedule->time_out)
            ] : null,
            'batch_schedule' => $batchSchedule ? [
                'schedule_id' => $batchSchedule->schedule_id,
                'time_out' => $batchSchedule->time_out,
                'time_in' => $batchSchedule->time_in,
                'day_of_week' => $batchSchedule->day_of_week,
                'batch' => $batchSchedule->batch,
                'group' => $batchSchedule->pn_group,
                'valid_until' => $batchSchedule->valid_until,
                'can_logout_now' => $currentTime <= Carbon::parse($batchSchedule->time_out)
            ] : null,
            'schedule_used' => $irregularSchedule ? 'irregular' : ($batchSchedule ? 'batch' : 'none')
        ]);
    }

    /**
     * Update educator validation for absent considerations.
     */
    public function updateAbsentValidation(Request $request, $id)
    {
        try {
            $request->validate([
                'validation' => 'required|in:valid,not_valid',
                'consideration_type' => 'required|in:time_in,time_out'
            ]);

            $academic = Academic::findOrFail($id);
            $considerationType = $request->consideration_type;

            // Check if the consideration is actually "Absent"
            if ($considerationType === 'time_out' && $academic->time_out_consideration !== 'Absent') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only validate absent considerations.'
                ]);
            }

            if ($considerationType === 'time_in' && $academic->educator_consideration !== 'Absent') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only validate absent considerations.'
                ]);
            }

            // Determine which field to update
            $updateField = $considerationType === 'time_out' ? 'time_out_absent_validation' : 'time_in_absent_validation';
            $otherField = $considerationType === 'time_out' ? 'time_in_absent_validation' : 'time_out_absent_validation';

            DB::beginTransaction();

            try {
                // Get educator name from session
                $user = session('user');
                $educatorName = 'Educator'; // Default fallback

                if ($user) {
                    if (is_array($user)) {
                        $firstName = $user['user_fname'] ?? '';
                        $lastName = $user['user_lname'] ?? '';
                        $educatorName = trim($firstName . ' ' . $lastName);
                    } else {
                        $firstName = $user->user_fname ?? '';
                        $lastName = $user->user_lname ?? '';
                        $educatorName = trim($firstName . ' ' . $lastName);
                    }
                }

                // Additional fallbacks
                if (empty($educatorName) || $educatorName === ' ') {
                    $educatorName = session('user_fname') . ' ' . session('user_lname');
                    $educatorName = trim($educatorName);

                    if (empty($educatorName) || $educatorName === ' ') {
                        $educatorName = 'Educator';
                    }
                }



                // Update the current validation field (preserve monitor names)
                $updateData = [
                    $updateField => $request->validation,
                    'updated_by' => $educatorName,
                    'updated_at' => now()
                ];

                // Auto-sync: If the other consideration is also "Absent", update its validation too
                $otherConsideration = $considerationType === 'time_out' ? $academic->educator_consideration : $academic->time_out_consideration;
                $autoSynced = false;
                if ($otherConsideration === 'Absent') {
                    $updateData[$otherField] = $request->validation;
                    $autoSynced = true;
                }

                $academic->update($updateData);

                DB::commit();
                $message = $autoSynced
                    ? 'Absent validation updated successfully. Both log in and log out validations have been synced.'
                    : 'Absent validation updated successfully.';

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'auto_synced' => $autoSynced,
                    'educator_name' => $educatorName
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to update validation: ' . $e->getMessage()
                ]);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the validation.'
            ]);
        }
    }
}
