<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\CalendarModel;
use App\Models\EventSchedule;
use Illuminate\Http\Request;
use App\Models\Schedule;
use App\Models\StudentDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\IrregularSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class MonitorScheduleController extends Controller
{
    /**
     * Get the effective schedule for a student, prioritizing irregular schedule if it exists
     *
     * @param string $student_id
     * @param string $batch
     * @param string $group
     * @return \Illuminate\Database\/Eloquent\Collection
     */
    private function getStudentSchedule($student_id, $batch, $group)
    {
        // First check if student has an academic irregular schedule
        $irregularSchedule = Schedule::where('student_id', $student_id)
            ->where('schedule_type', 'academic') // Only look for academic irregular schedules
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', Carbon::today());
            })
            ->orderBy('day_of_week')
            ->get();

        // If irregular schedule exists, return it
        if ($irregularSchedule->isNotEmpty()) {
            return $irregularSchedule;
        }
        // If no irregular schedule, return the academic batch schedule
        return Schedule::where('batch', $batch)
            ->where('pn_group', $group)
            ->where('schedule_type', 'academic') // Only look for academic batch schedules
            ->where(function ($query) {
                $query->whereNull('valid_until')
                    ->orWhereDate('valid_until', '>=', Carbon::today());
            })
            ->orderBy('day_of_week')
            ->get();
    }
    public function getGroups(Request $request)
    {
        try {
            $batch = $request->input('batch');

            $groups = DB::table('student_details')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->where('student_details.batch', $batch)
                ->where('pnph_users.status', 'active')
                ->select('student_details.group')
                ->distinct()
                ->orderBy('student_details.group')
                ->get();

            return response()->json($groups);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to fetch groups',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function show(Request $request)
    {
        try {
            $data = $request->only([
                'type',
                'batch',
                'group',
                'student_id',
                'gender',
                'semester_id', // Add semester_id to the request data
            ]);
            $currentSchedule = null;
            $expiredSchedule = null;
            $warningMessage = null;

            $batches = DB::table('student_details')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->where('pnph_users.status', 'active')
                ->select('student_details.batch')
                ->distinct()
                ->orderBy('student_details.batch', 'desc')
                ->get();

            if ($data['type'] == 'Academic') {
                // Build base query for academic schedules
                $baseQuery = Schedule::where('batch', $data['batch'])
                    ->where('pn_group', $data['group'])
                    ->where('schedule_type', 'academic');

                // Add semester filter if provided
                if (!empty($data['semester_id'])) {
                    $baseQuery->where('semester_id', $data['semester_id']);
                }

                // Check for expired schedule first (end_date < today)
                $expiredSchedule = (clone $baseQuery)
                    ->where('end_date', '<', Carbon::today())
                    ->orderBy('end_date', 'desc')
                    ->first();

                // Get current active schedule (start_date <= today AND end_date >= today)
                $currentSchedule = (clone $baseQuery)
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->orderBy('day_of_week')
                    ->get();

                if ($currentSchedule->isNotEmpty()) {
                    $endDate = $currentSchedule->first()->end_date;
                }

                // Warning if schedule expires within 3 days
                if (!empty($endDate) && Carbon::parse($endDate)->diffInDays(Carbon::today()) <= 3) {
                    $warningMessage = 'Schedule will expire on ' . Carbon::parse($endDate)->format('M d, Y') . '. Please update the schedule.';
                }
            }
            elseif ($data['type'] == 'Irregular') {
                // Build base query for irregular academic schedules
                $baseQuery = Schedule::where('student_id', $data['student_id'])
                    ->where('schedule_type', 'academic');

                // Add semester filter if provided
                if (!empty($data['semester_id'])) {
                    $baseQuery->where('semester_id', $data['semester_id']);
                }

                $expiredSchedule = (clone $baseQuery)
                    ->where('end_date', '<', Carbon::today())
                    ->orderBy('end_date', 'desc')
                    ->first();

                $currentSchedule = (clone $baseQuery)
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->orderBy('day_of_week')
                    ->get();

                if ($currentSchedule->isNotEmpty()) {
                    $endDate = $currentSchedule->first()->end_date;
                }

                if (!empty($endDate) && Carbon::parse($endDate)->diffInDays(Carbon::today()) <= 3) {
                    $warningMessage = 'Schedule will expire on ' . Carbon::parse($endDate)->format('M d, Y') . '. Please update the schedule.';
                }
            }
            elseif ($data['type'] == 'GoingOut') {
                $expiredSchedule = Schedule::where('gender', $data['gender'])
                    ->where('schedule_type', 'going_out')
                    ->where('end_date', '<', Carbon::today())
                    ->orderBy('end_date', 'desc')
                    ->first();

                $currentSchedule = Schedule::where('gender', $data['gender'])
                    ->where('schedule_type', 'going_out')
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->orderBy('day_of_week')
                    ->get();

                if ($currentSchedule->isNotEmpty()) {
                    $endDate = $currentSchedule->first()->end_date;
                }

                if (!empty($endDate) && Carbon::parse($endDate)->diffInDays(Carbon::today()) <= 3) {
                    $warningMessage = 'Schedule will expire on ' . Carbon::parse($endDate)->format('M d, Y') . '. Please update the schedule.';
                }
            }
            return view('user-monitor.setSched', compact('data', 'batches', 'currentSchedule', 'expiredSchedule', 'warningMessage'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load schedule form: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'student_id'         => 'nullable|string',
                'gender'             => 'nullable|string',
                'semester_id'        => 'nullable|in:1,2,3',
                'type'               => 'required|in:Academic,Irregular,GoingOut',
                'batch'              => 'nullable|string',
                'group'              => 'nullable|in:PN1,PN2',
                'start_date' => 'required|date',
                'end_date'   => 'required|date|after_or_equal:start_date',
                'grace_period_logout_minutes' => 'nullable|integer|min:0|max:60',
                'grace_period_login_minutes' => 'nullable|integer|min:0|max:60',
                'schedule'           => 'required|array',
                'schedule.*.time_out' => 'required|date_format:H:i',
                'schedule.*.time_in' => [
                    'required',
                    'date_format:H:i',
                    function ($attribute, $value, $fail) use ($request) {
                        $parts = explode('.', $attribute);
                        $day = $parts[1];
                        $timeOut = $request->input("schedule.$day.time_out");

                        if ($timeOut === $value) {
                            $fail("Time in cannot be the same as time out for this schedule");
                        } elseif (strtotime($timeOut) >= strtotime($value)) {
                            $fail("Time in must be after time out for this schedule");
                        }
                    },
                ],
            ], [
                'schedule.*.time_in.after' => 'Time in must be after time out',
                'end_date.after_or_equal' => 'Valid until date must be today or a future date',
                'group.required' => 'Please select a group (PN1 or PN2)',
                'group.in' => 'Please select either PN1 or PN2',
                'grace_period_logout_minutes.integer' => 'Log out grace period must be a valid number',
                'grace_period_logout_minutes.min' => 'Log out grace period cannot be negative',
                'grace_period_logout_minutes.max' => 'Log out grace period cannot exceed 60 minutes',
                'grace_period_login_minutes.integer' => 'Log in grace period must be a valid number',
                'grace_period_login_minutes.min' => 'Log in grace period cannot be negative',
                'grace_period_login_minutes.max' => 'Log in grace period cannot exceed 60 minutes'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->validated();

            // Resolve creator ID once and guard against missing authentication
            $creatorId = (Auth::user()->user_id ?? Auth::id() ?? session('user.user_id'));
            if (empty($creatorId)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'You must be logged in to perform this action. Please log in again.');
            }

            if (isset($data['student_id'])) {
                $existingIregStud = Schedule::where('student_id', $data['student_id'])
                    ->where('schedule_type', 'academic')
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($existingIregStud) {
                    // Get student details
                    $student = \App\Models\StudentDetail::where('student_id', $data['student_id'])->first();

                    // Get batch schedule (template)
                    $batchSchedule = null;
                    if ($student) {
                        $batchSchedule = Schedule::where('batch', $student->batch)
                            ->where('pn_group', $student->group)
                            ->where('schedule_type', 'academic')
                            ->whereDate('start_date', '<=', Carbon::today())
                            ->whereDate('end_date', '>=', Carbon::today())
                            ->first();
                    }

                    foreach ($data['schedule'] as $dayKey => $times) {
                        $updateData = [
                            'time_in'    => $times['time_in'],
                            'time_out'   => $times['time_out'],
                            'start_date' => $data['start_date'],
                            'end_date'   => $data['end_date'],
                            'updated_at' => now(),
                        ];

                        if ($data['type'] === 'Academic' || $data['type'] === 'Irregular') {
                            $updateData['grace_period_logout_minutes'] =
                                $data['grace_period_logout_minutes'] ?? ($batchSchedule->grace_period_logout_minutes ?? null);

                            $updateData['grace_period_login_minutes'] =
                                $data['grace_period_login_minutes'] ?? ($batchSchedule->grace_period_login_minutes ?? null);
                        }

                        Schedule::where('student_id', $data['student_id'])
                            ->where('schedule_type', 'academic')
                            ->where('day_of_week', ucfirst($dayKey))
                            ->whereDate('start_date', '<=', Carbon::today())
                            ->whereDate('end_date', '>=', Carbon::today())
                            ->update($updateData);
                    }

                    if (function_exists('opcache_reset')) {
                        opcache_reset();
                    }

                    if (config('cache.default') !== 'array') {
                        Cache::flush();
                    }

                    $verifySchedule = Schedule::where('student_id', $data['student_id'])
                        ->where('schedule_type', 'academic')
                        ->whereDate('start_date', '<=', Carbon::today())
                        ->whereDate('end_date', '>=', Carbon::today())
                        ->orderBy('updated_at', 'desc')
                        ->get();

                    return redirect()
                        ->route('monitor.schedule', [
                            'student_id' => $data['student_id'],
                            'type'       => $data['type'],
                        ])
                        ->with('success', 'Schedule has been updated successfully! Changes are now active.');
                } else {
                    // If no existing schedule, create new
                    $student = \App\Models\StudentDetail::where('student_id', $data['student_id'])->first();

                    $batchSchedule = null;
                    if ($student) {
                        $batchSchedule = Schedule::where('batch', $student->batch)
                            ->where('pn_group', $student->group)
                            ->where('schedule_type', 'academic')
                            ->whereDate('start_date', '<=', Carbon::today())
                            ->whereDate('end_date', '>=', Carbon::today())
                            ->first();
                    }

                    foreach ($data['schedule'] as $dayKey => $times) {
                        $createData = [
                            'student_id'   => $data['student_id'],
                            'day_of_week'  => ucfirst($dayKey),
                            'schedule_type'=> 'academic',
                            'time_in'      => $times['time_in'],
                            'time_out'     => $times['time_out'],
                            'start_date'   => $data['start_date'],
                            'end_date'     => $data['end_date'],
                            'created_by'   => (Auth::user()->user_id ?? session('user.user_id')),
                        ];

                        if ($data['type'] === 'Academic' || $data['type'] === 'Irregular') {
                            $createData['grace_period_logout_minutes'] =
                                $data['grace_period_logout_minutes'] ?? ($batchSchedule->grace_period_logout_minutes ?? null);

                            $createData['grace_period_login_minutes'] =
                                $data['grace_period_login_minutes'] ?? ($batchSchedule->grace_period_login_minutes ?? null);
                        }

                        Schedule::create($createData);
                    }

                    return redirect()
                        ->route('monitor.schedule', [
                            'student_id' => $data['student_id'],
                            'type'       => $data['type'],
                        ])
                        ->with('success', 'Schedule has been saved successfully!');
                }
            }

            // 3) Process each day's schedule
            if (isset($data['group']) && isset($data['batch'])) {
                $existingAcadBatch = Schedule::where('batch', $data['batch'])
                    ->where('pn_group', $data['group'])
                    ->where('schedule_type', 'academic')
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->first();

                if ($existingAcadBatch) {
                    foreach ($data['schedule'] as $dayKey => $times) {
                        $updateData = [
                            'time_in'    => $times['time_in'],
                            'time_out'   => $times['time_out'],
                            'start_date' => $data['start_date'],
                            'end_date'   => $data['end_date'],
                            'updated_at' => now(), // Force timestamp update
                        ];

                        // Preserve existing grace periods for Academic and GoingOut if not provided
                        if ($data['type'] === 'Academic' || $data['type'] === 'GoingOut') {
                            if (array_key_exists('grace_period_logout_minutes', $data)) {
                                $updateData['grace_period_logout_minutes'] = $data['grace_period_logout_minutes'] ?: null;
                            }
                            if (array_key_exists('grace_period_login_minutes', $data)) {
                                $updateData['grace_period_login_minutes'] = $data['grace_period_login_minutes'] ?: null;
                            }
                        }

                        Schedule::where('batch', $data['batch'])
                            ->where('pn_group', $data['group'])
                            ->where('day_of_week', ucfirst($dayKey))
                            ->whereDate('start_date', '<=', Carbon::today())
                            ->whereDate('end_date', '>=', Carbon::today())
                            ->update($updateData);
                    }

                    // Clear caches
                    if (function_exists('opcache_reset')) {
                        opcache_reset();
                    }
                    if (config('cache.default') !== 'array') {
                        Cache::flush();
                    }

                    // Verify updates
                    $verifySchedule = Schedule::where('batch', $data['batch'])
                        ->where('pn_group', $data['group'])
                        ->whereDate('start_date', '<=', Carbon::today())
                        ->whereDate('end_date', '>=', Carbon::today())
                        ->orderBy('updated_at', 'desc')
                        ->get();

                    return redirect()
                        ->route('monitor.schedule', [
                            'batch' => $data['batch'],
                            'group' => $data['group'],
                            'type'  => $data['type'],
                        ])
                        ->with('success', 'Schedule has been updated successfully! Changes are now active.');
                } else {
                    foreach ($data['schedule'] as $dayKey => $times) {
                        $createData = [
                            'batch'        => $data['batch'],
                            'semester_id'  => $data['semester_id'],
                            'pn_group'     => $data['group'],
                            'day_of_week'  => ucfirst($dayKey),
                            'schedule_type'=> 'academic',
                            'time_in'      => $times['time_in'],
                            'time_out'     => $times['time_out'],
                            'start_date'   => $data['start_date'],
                            'end_date'     => $data['end_date'],
                            'created_by'   => (Auth::user()->user_id ?? session('user.user_id')),
                        ];

                        if ($data['type'] === 'Academic') {
                            $createData['grace_period_logout_minutes'] = $data['grace_period_logout_minutes'] ?? null;
                            $createData['grace_period_login_minutes']  = $data['grace_period_login_minutes'] ?? null;
                        }

                        Schedule::create($createData);
                    }

                    return redirect()
                        ->route('monitor.schedule', [
                            'batch' => $data['batch'],
                            'group' => $data['group'],
                            'type'  => $data['type'],
                        ])
                        ->with('success', 'Schedule has been saved successfully!');
                }
            }

            if (isset($data['gender'])) {
                $existingGoingOut = Schedule::where('gender', $data['gender'])
                    ->where('schedule_type', 'going_out')
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->first();

                if ($existingGoingOut) {
                    foreach ($data['schedule'] as $dayKey => $times) {
                        $updateData = [
                            'time_in'    => $times['time_in'],
                            'time_out'   => $times['time_out'],
                            'start_date' => $data['start_date'],
                            'end_date'   => $data['end_date'],
                            'updated_at' => now(),
                        ];

                        // Add grace periods only for Academic (not for Going Out)
                        if ($data['type'] === 'Academic') {
                            if (array_key_exists('grace_period_logout_minutes', $data)) {
                                $updateData['grace_period_logout_minutes'] = $data['grace_period_logout_minutes'] ?: null;
                            }
                            if (array_key_exists('grace_period_login_minutes', $data)) {
                                $updateData['grace_period_login_minutes'] = $data['grace_period_login_minutes'] ?: null;
                            }
                        }

                        Schedule::where('gender', $data['gender'])
                            ->where('day_of_week', ucfirst($dayKey))
                            ->whereDate('start_date', '<=', Carbon::today())
                            ->whereDate('end_date', '>=', Carbon::today())
                            ->update($updateData);
                    }

                    if (function_exists('opcache_reset')) {
                        opcache_reset();
                    }
                    if (config('cache.default') !== 'array') {
                        Cache::flush();
                    }

                    return redirect()
                        ->route('monitor.schedule', [
                            'gender' => $data['gender'],
                            'type'   => $data['type'],
                        ])
                        ->with('success', 'Schedule has been updated successfully!');
                } else {
                    foreach ($data['schedule'] as $dayKey => $times) {
                        $createData = [
                            'gender'        => $data['gender'],
                            'day_of_week'   => ucfirst($dayKey),
                            'schedule_type' => 'going_out',
                            'time_in'       => $times['time_in'],
                            'time_out'      => $times['time_out'],
                            'start_date'    => $data['start_date'],
                            'end_date'      => $data['end_date'],
                            'created_by'    => (Auth::user()->user_id ?? session('user.user_id')),
                        ];

                        if ($data['type'] === 'Academic') {
                            $createData['grace_period_logout_minutes'] = $data['grace_period_logout_minutes'] ?? null;
                            $createData['grace_period_login_minutes']  = $data['grace_period_login_minutes'] ?? null;
                        }

                        Schedule::create($createData);
                    }

                    return redirect()
                        ->route('monitor.schedule', [
                            'gender' => $data['gender'],
                            'type'   => $data['type'],
                        ])
                        ->with('success', 'Schedule has been saved successfully!');
                }
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to update schedule: ' . $e->getMessage());
        }
    }


    public function showIrregularSchedule($student_id)
    {
        try {
            // Get the student details with their name
            $student = DB::table('student_details')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->where('student_details.student_id', $student_id)
                ->select(
                    'student_details.*',
                    'pnph_users.user_fname as first_name',
                    'pnph_users.user_lname as last_name'
                )
                ->first();

            if (!$student) {
                return redirect()
                    ->route('monitor.irregular-schedule.select')
                    ->with('error', 'Student not found');
            }

            // Get current active schedule for the student
            $currentSchedule = Schedule::where('student_id', $student_id)
                ->where('schedule_type', 'academic')
                ->whereDate('start_date', '<=', Carbon::today())
                ->whereDate('end_date', '>=', Carbon::today())
                ->orderBy('day_of_week')
                ->get();

            // Get expired schedule if it exists
            $expiredSchedule = Schedule::where('student_id', $student_id)
                ->where('schedule_type', 'academic')
                ->where('end_date', '<', Carbon::today())
                ->orderBy('end_date', 'desc')
                ->first();

            // Get the end_date value if it exists
            $endDate = $currentSchedule->isNotEmpty() ? $currentSchedule->first()->end_date : null;

            // Prepare warning message if schedule is expiring soon
            $warningMessage = null;
            if ($endDate && Carbon::parse($endDate)->diffInDays(Carbon::today()) <= 3) {
                $warningMessage = 'Schedule will expire on ' . Carbon::parse($endDate)->format('M d, Y') . '. Please update the schedule.';
            }

            // Get batch schedule to show inherited grace periods for new irregular schedules
            $batchSchedule = null;
            if ($currentSchedule->isEmpty()) {
                $batchSchedule = Schedule::where('batch', $student->batch)
                    ->where('pn_group', $student->group)
                    ->where('schedule_type', 'academic')
                    ->whereDate('start_date', '<=', Carbon::today())
                    ->whereDate('end_date', '>=', Carbon::today())
                    ->first();
            }

            // Prepare data array for the view
            $data = [
                'type'        => 'Irregular',
                'student_id'  => $student_id,
                'student_name'=> $student->first_name . ' ' . $student->last_name,
                'batch'       => $student->batch,
                'group'       => $student->group
            ];

            return view('user-monitor.setSched', compact(
                'data',
                'currentSchedule',
                'expiredSchedule',
                'endDate',
                'warningMessage',
                'batchSchedule'
            ));

        } catch (\Exception $e) {
            return redirect()
                ->route('monitor.irregular-schedule.select')
                ->with('error', 'Failed to load student schedule: ' . $e->getMessage());
        }
    }

    public function selectStudent()
    {
        try {
            $students = DB::table('student_details')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->select(
                    'student_details.student_id',
                    'pnph_users.user_fname as first_name',
                    'pnph_users.user_lname as last_name',
                    'student_details.batch',
                    'student_details.group as pn_group'
                )
                ->where('pnph_users.user_role', 'student')
                ->where('pnph_users.status', 'active')
                ->orderBy('student_details.batch')
                ->orderBy('student_details.group')
                ->orderBy('pnph_users.user_lname')
                ->get();

            // Get all student_ids that already have an academic irregular schedule
            $studentsWithSchedule = DB::table('schedules')
                ->whereNotNull('student_id')
                ->where('schedule_type', 'academic') // Only count academic irregular schedules
                ->select('student_id')
                ->distinct()
                ->pluck('student_id')
                ->toArray();

            return view('user-monitor.select-student', compact('students', 'studentsWithSchedule'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load students: ' . $e->getMessage());
        }
    }


    public function dashboard()
    {
        // Get unique active batches from student_details
        $batches = DB::table('student_details')
            ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
            ->where('pnph_users.status', 'active')
            ->select('student_details.batch')
            ->distinct()
            ->orderBy('student_details.batch', 'desc')
            ->get();

        return view('user-monitor.dashboard', compact('batches'));
    }

    public function delete(Request $request)
    {
        try {
            $type = $request->input('type');
            $query = Schedule::query();

            if ($type === 'Academic') {
                $query->where('batch', $request->input('batch'))
                      ->where('pn_group', $request->input('group'));
            } elseif ($type === 'Irregular') {
                $query->where('student_id', $request->input('student_id'))
                      ->where('schedule_type', 'academic'); // Only delete academic irregular schedules
            } elseif ($type === 'GoingOut') {
                $query->where('gender', $request->input('gender'));
            }

            $deleted = $query->delete();

            if ($deleted) {

                return redirect()->back()->with('success', 'Schedule deleted successfully');
            }

            return redirect()->back()->with('error', 'No schedule found to delete');
        } catch (\Exception $e) {

            return redirect()->back()->with('error', 'Failed to delete schedule: ' . $e->getMessage());
        }
    }

    public function updateGracePeriod(Request $request)
    {
        try {
            // Validate the request - only allow Academic schedules
            $validator = validator($request->all(), [
                'type' => 'required|in:Academic,Irregular',
                'batch' => 'nullable|string',
                'group' => 'nullable|in:PN1,PN2',
                'student_id' => 'nullable|string',
                'gender' => 'nullable|string',
                'grace_period_logout_minutes' => 'nullable|integer|min:0|max:60',
                'grace_period_login_minutes' => 'nullable|integer|min:0|max:60',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input: ' . $validator->errors()->first()
                ], 400);
            }

            $data = $validator->validated();

            // Prevent grace period updates for GoingOut schedules
            if ($data['type'] === 'GoingOut') {
                return response()->json([
                    'success' => false,
                    'message' => 'Grace period settings are not available for Going Out schedules.'
                ], 400);
            }

            // Determine which schedules to update based on type
            $query = Schedule::query();

            if ($data['type'] === 'Academic' && isset($data['batch']) && isset($data['group'])) {
                $query->where('batch', $data['batch'])
                      ->where('pn_group', $data['group']);
            } elseif ($data['type'] === 'Irregular' && isset($data['student_id'])) {
                $query->where('student_id', $data['student_id']);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid schedule parameters'
                ], 400);
            }

            // Only update active schedules
            $query->where(function ($q) {
                $q->whereNull('valid_until')
                  ->orWhereDate('valid_until', '>=', Carbon::today());
            });

            // Prepare update data
            $updateData = [];
            $updateData['grace_period_logout_minutes'] = $data['grace_period_logout_minutes'] ?: null;
            $updateData['grace_period_login_minutes'] = $data['grace_period_login_minutes'] ?: null;
            $updateData['updated_at'] = now();

            // Update the schedules
            $updated = $query->update($updateData);

            if ($updated > 0) {
                $logoutValue = $data['grace_period_logout_minutes'];
                $loginValue = $data['grace_period_login_minutes'];

                $messages = [];
                if ($logoutValue) {
                    $messages[] = "Log out: {$logoutValue} minutes";
                } else {
                    $messages[] = "Log out: exact timing";
                }

                if ($loginValue) {
                    $messages[] = "Log in: {$loginValue} minutes";
                } else {
                    $messages[] = "Log in: exact timing";
                }

                $message = "Grace periods updated - " . implode(', ', $messages);

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'schedules_updated' => $updated
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedules found to update'
                ], 404);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update grace period: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show students for individual going-out schedule selection
     */
    public function showIndividualGoingOutStudents(Request $request)
    {
        try {
            $students = StudentDetail::with('user')->orderBy('student_id')->get();
            $batches = StudentDetail::distinct()->pluck('batch')->sort();
            $groups = StudentDetail::distinct()->pluck('group')->sort();

            // Only show going home schedules (date range schedules for going home)
            $existingSchedules = Schedule::where('schedule_type', 'going_home')
                ->whereNotNull('student_id')
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('start_date')
                          ->whereNotNull('end_date')
                          ->whereDate('end_date', '>=', Carbon::today());
                    });
                })
                ->get()
                ->groupBy('student_id');

            return view('user-monitor.individual-goingout-students', compact('students', 'batches', 'groups', 'existingSchedules'));

        } catch (\Exception $e) {
            return redirect()->route('monitor.dashboard')
                ->with('error', 'Failed to load students page: ' . $e->getMessage());
        }
    }

    /**
     * Set individual going-out schedule for selected students
     */
    public function setIndividualGoingOutSchedule(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'required|exists:student_details,student_id',
                'selected_days' => 'required_if:individual_schedule_type,single_day|array|min:1',
                'selected_days.*' => 'required|in:monday,tuesday,wednesday,thursday,friday,saturday,sunday',
                'schedule' => 'required_if:individual_schedule_type,single_day|array',
                'schedule.*.time_out' => 'required|date_format:H:i',
                'schedule.*.time_in' => 'required|date_format:H:i|after:schedule.*.time_out',
                'individual_schedule_type' => 'required|in:single_day,date_range',
                'individual_schedule_name' => 'required_if:individual_schedule_type,date_range|string|max:255',
                'individual_start_date' => 'required_if:individual_schedule_type,date_range|date|after_or_equal:today',
                'individual_end_date' => 'required_if:individual_schedule_type,date_range|date|after_or_equal:individual_start_date',
                'individual_time_out' => 'required_if:individual_schedule_type,date_range|date_format:H:i',
                'individual_time_in' => 'required_if:individual_schedule_type,date_range|date_format:H:i',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please fix the validation errors and try again.');
            }

            $data = $validator->validated();

            // Resolve creator for this individual-schedule action
            $creatorId = (Auth::user()->user_id ?? Auth::id() ?? session('user.user_id'));
            if (empty($creatorId)) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'You must be logged in to set schedules. Please log in again.');
            }

            // Prevent duplicates for single-day schedules: do a pre-validation pass
            if ($data['individual_schedule_type'] === 'single_day') {
                $conflicts = [];
                $selectedDays = $data['selected_days'] ?? [];
                foreach ($data['student_ids'] as $studentId) {
                    foreach ($selectedDays as $selectedDay) {
                        $dayOfWeek = ucfirst($selectedDay);
                        $exists = Schedule::where([
                            ['student_id', $studentId],
                            ['day_of_week', $dayOfWeek],
                            ['schedule_type', 'going_home']
                        ])->where(function ($query) {
                            $query->whereNotNull('start_date')
                                  ->whereNotNull('end_date')
                                  ->whereDate('end_date', '>=', Carbon::today());
                        })->exists();

                        if ($exists) {
                            $student = StudentDetail::with('user')->where('student_id', $studentId)->first();
                            $studentName = $student ? ($student->user->user_fname . ' ' . $student->user->user_lname) : $studentId;
                            $conflicts[] = "Student {$studentName} already has a schedule for {$dayOfWeek}.";
                        }
                    }
                }

                if (!empty($conflicts)) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors($conflicts)
                        ->with('error', 'Some selected days already have existing schedules.');
                }
            }

            DB::beginTransaction();

            try {
                $createdSchedules = 0;
                $updatedSchedules = 0;

                if ($data['individual_schedule_type'] === 'date_range') {
                    $startDate = Carbon::parse($data['individual_start_date']);
                    $endDate = Carbon::parse($data['individual_end_date']);

                    foreach ($data['student_ids'] as $studentId) {
                        $logOutDay = strtolower($startDate->format('l'));
                        $this->createDateRangeSchedule($studentId, $data, $logOutDay, 'log_out', $startDate, $endDate, $creatorId);
                        $createdSchedules++;

                        // Create log in schedule for end date (only if different from start date)
                        if (!$startDate->isSameDay($endDate)) {
                            $logInDay = strtolower($endDate->format('l'));
                            $this->createDateRangeSchedule($studentId, $data, $logInDay, 'log_in', $startDate, $endDate, $creatorId);
                            $createdSchedules++;
                        }
                    }
                } else {
                    // For single day schedules, use the selected days
                    $daysToProcess = $data['selected_days'] ?? [];

                    foreach ($data['student_ids'] as $studentId) {
                        // Process all determined days
                        foreach ($daysToProcess as $selectedDay) {
                            if (!isset($data['schedule'][$selectedDay])) {
                                continue; // Skip if no schedule data for this day
                            }

                            $times = $data['schedule'][$selectedDay];
                            $dayOfWeek = ucfirst($selectedDay);

                            // Mapping day names to Carbon constants
                            $dayMapping = [
                                'monday'    => Carbon::MONDAY,
                                'tuesday'   => Carbon::TUESDAY,
                                'wednesday' => Carbon::WEDNESDAY,
                                'thursday'  => Carbon::THURSDAY,
                                'friday'    => Carbon::FRIDAY,
                                'saturday'  => Carbon::SATURDAY,
                                'sunday'    => Carbon::SUNDAY
                            ];

                            $targetDayNumber = $dayMapping[strtolower($selectedDay)];

                            // Determine start_date and end_date for a one-day schedule
                            $today = Carbon::today();
                            if ($today->dayOfWeek === $targetDayNumber) {
                                // If today matches, schedule is only for today
                                $scheduleStart = $today->copy()->startOfDay();
                                $scheduleEnd   = $today->copy()->endOfDay();
                            } else {
                                // Otherwise, find the next occurrence of that day
                                $nextDay       = $today->copy()->next($targetDayNumber);
                                $scheduleStart = $nextDay->copy()->startOfDay();
                                $scheduleEnd   = $nextDay->copy()->endOfDay();
                            }

                            // Check if an individual going-out schedule already exists for this student and day
                            // Consider both current and upcoming schedules (end_date >= today)
                            $existingSchedule = Schedule::where([
                                ['student_id', $studentId],
                                ['day_of_week', $dayOfWeek],
                                ['schedule_type', 'going_home']
                            ])->where(function ($query) {
                                $query->where(function ($q) {
                                    // Schedules with a date range (includes single-day schedules as start=end)
                                    $q->whereNotNull('start_date')
                                      ->whereNotNull('end_date')
                                      ->whereDate('end_date', '>=', Carbon::today());
                                });
                            })->first();

                            $scheduleData = [
                                'student_id'   => $studentId,
                                'gender'       => null,
                                'batch'        => null,
                                'pn_group'     => null,
                                'day_of_week'  => $dayOfWeek,
                                'schedule_type'=> 'going_home',
                                'time_in'      => $times['time_in'],
                                'time_out'     => $times['time_out'],
                                'grace_period_logout_minutes' => null,
                                'grace_period_login_minutes'  => null,
                                'created_at'   => now(),
                                'created_by'   => $creatorId,
                                'is_batch_schedule' => false
                            ];

                            if ($data['individual_schedule_type'] === 'date_range') {
                                // Multi-day range schedule
                                $scheduleData['schedule_name'] = $data['individual_schedule_name'];
                                $scheduleData['start_date']    = $data['individual_start_date'];
                                $scheduleData['end_date']      = $data['individual_end_date'];
                            } else {
                                // Single-day schedule (next occurrence of chosen day)
                                $scheduleData['schedule_name'] = null;
                                $scheduleData['start_date']    = $scheduleStart;
                                $scheduleData['end_date']      = $scheduleEnd;
                            }

                            if ($existingSchedule) {
                                // Should not happen due to pre-validation. If it does, stop and report.
                                DB::rollBack();
                                return redirect()->back()
                                    ->withInput()
                                    ->withErrors(["A schedule for {$dayOfWeek} already exists for student {$studentId}."])
                                    ->with('error', 'Duplicate day detected. No changes were saved.');
                            } else {
                                Schedule::create($scheduleData);
                                $createdSchedules++;
                            }
                        }
                    }
                }

                DB::commit();

                $studentCount = count($data['student_ids']);

                $students = StudentDetail::whereIn('student_id', $data['student_ids'])
                    ->with('user')
                    ->get();

                $studentNames = $students->map(function ($student) {
                    return $student->user->user_fname . ' ' . $student->user->user_lname;
                })->toArray();

                $studentNamesString = '';
                if ($studentCount === 1) {
                    $studentNamesString = $studentNames[0];
                } elseif ($studentCount === 2) {
                    $studentNamesString = implode(' and ', $studentNames);
                } else {
                    $lastStudent = array_pop($studentNames);
                    $studentNamesString = implode(', ', $studentNames) . ', and ' . $lastStudent;
                }

                if ($data['individual_schedule_type'] === 'date_range') {
                    $message = "Going home period '{$data['individual_schedule_name']}' has been successfully set for {$studentNamesString}.";
                } else {
                    $dayNames = implode(', ', array_map('ucfirst', $data['selected_days']));
                    $message = "Individual going-out schedule has been successfully set for {$studentNamesString} on {$dayNames}.";
                }

                $logData = [
                    'student_ids' => $data['student_ids'],
                    'schedule_type' => $data['individual_schedule_type'],
                    'student_count' => $studentCount,
                    'created_schedules' => $createdSchedules,
                    'updated_schedules' => $updatedSchedules,
                    'user_id' => $creatorId
                ];

                if ($data['individual_schedule_type'] === 'date_range') {
                    $logData['schedule_name'] = $data['individual_schedule_name'];
                    $logData['start_date'] = $data['individual_start_date'];
                    $logData['end_date'] = $data['individual_end_date'];
                    $logData['time_out'] = $data['individual_time_out'];
                    $logData['time_in'] = $data['individual_time_in'];
                } else {
                    $logData['selected_days'] = $data['selected_days'];
                    $logData['day_count'] = count($data['selected_days']);
                }

                return redirect()->route('monitor.individual-goingout.students')
                    ->with('success', $message);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Failed to set schedule: ' . $e->getMessage());
        }
    }

    /**
     * Helper method to create date range schedule
     */
    private function createDateRangeSchedule($studentId, $data, $dayOfWeek, $scheduleAction, $startDate, $endDate, $creatorId)
    {
        // Determine time based on action
        $timeOut = $scheduleAction === 'log_out' ? $data['individual_time_out'] : null;
        $timeIn = $scheduleAction === 'log_in' ? $data['individual_time_in'] : null;

        // For going home periods, we create schedules that allow logging on specific dates only
        $scheduleData = [
            'student_id' => $studentId,
            'gender' => null,
            'batch' => null,
            'pn_group' => null,
            'day_of_week' => ucfirst($dayOfWeek),
            'schedule_type' => 'going_home',
            'time_in' => $timeIn,
            'time_out' => $timeOut,
            'schedule_name' => $data['individual_schedule_name'],
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'is_batch_schedule' => false,
            'valid_until' => null, // Date range schedules don't use valid_until
            'grace_period_logout_minutes' => null,
            'grace_period_login_minutes' => null,
            'created_by' => $creatorId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        Schedule::create($scheduleData);
    }

    /**
     * Create batch going-out schedule
     */
    public function createBatchGoingOutSchedule(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'student_ids'   => 'required|array',
                'student_ids.*' => 'exists:student_details,student_id', // optional but good
                'schedule_name' => 'required|string|max:255',
                'start_date'    => 'required|date|after_or_equal:today',
                'end_date'      => 'required|date|after_or_equal:start_date',
                'time_out'      => 'required|date_format:H:i',
                'time_in'       => 'required|date_format:H:i'
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withInput()
                    ->with('success', false)
                    ->with('message', 'Validation failed: ' . $validator->errors()->first());
            }

            $data = $validator->validated();
            $startDate = Carbon::parse($data['start_date']);
            $endDate = Carbon::parse($data['end_date']);

            $conflictingSchedules = Schedule::where('schedule_type', 'going_home')
                ->where('is_batch_schedule', true)
                ->where('schedule_name', $data['schedule_name'])
                ->exists();

            if ($conflictingSchedules) {
                return redirect()->back()
                    ->withInput()
                    ->with('success', false)
                    ->with('message', 'A batch schedule with this name already exists for this batch.');
            }

            $schedules = [];

            foreach ($data['student_ids'] as $student_id) {
                $exist = Schedule::where('student_id', $student_id)
                    ->where('schedule_type', 'going_home')
                    ->where('is_batch_schedule', true)
                    ->where('schedule_name', $data['schedule_name'])
                    ->where(function ($query) use ($startDate, $endDate) {
                        $query->whereNotNull('start_date')
                            ->whereNotNull('end_date');
                    })
                    ->exists(); 

                if ($exist) {
                    continue;
                }

                $schedules[] = [
                    'student_id'        => $student_id,
                    'schedule_type'     => 'going_home',
                    'schedule_name'     => $data['schedule_name'],
                    'start_date'        => $data['start_date'],
                    'end_date'          => $data['end_date'],
                    'is_batch_schedule' => true,
                    'time_out'          => $data['time_out'],
                    'time_in'           => $data['time_in'],
                    'created_at'        => now(),
                    'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                ];
            }

            Schedule::insert($schedules);


            $totalDays = $startDate->diffInDays($endDate) + 1;
            $daysAway = $totalDays > 1 ? $totalDays - 1 : 0;

            $durationText = $totalDays === 1 ?
                "1 day" :
                "{$totalDays} days" . ($daysAway > 0 ? " ({$daysAway} day" . ($daysAway > 1 ? "s" : "") . " away from dormitory)" : "");
            return redirect()->back()
                ->with('success', true)
                ->with('message', "Going home schedule '{$data['schedule_name']}' has been successfully set. Duration: {$durationText}.");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('success', false)
                ->with('message', 'Failed to create batch schedule: ' . $e->getMessage());
        }
    }

    /**
     * Get existing batch going-out schedules
     */
    public function getBatchGoingOutSchedules()
    {
        try {
            $batchSchedules = Schedule::where('schedule_type', 'going_out')
                ->where('is_batch_schedule', true)
                ->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('start_date')
                          ->whereNotNull('end_date')
                          ->where('end_date', '>=', Carbon::today());
                    })->orWhere(function ($q) {
                        $q->whereNull('start_date')
                          ->whereNull('end_date')
                          ->where(function ($query) {
                              $query->whereNull('valid_until')
                                  ->orWhere('valid_until', '>=', Carbon::today());
                          });
                    });
                })
                ->orderBy('batch')
                ->orderBy('start_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(['batch', 'schedule_name']);

            $formattedSchedules = [];
            foreach ($batchSchedules as $batch => $scheduleGroups) {
                foreach ($scheduleGroups as $scheduleName => $schedules) {
                    $firstSchedule = $schedules->first();
                    $days = $schedules->pluck('day_of_week')->sort()->values()->toArray();

                    $formattedSchedules[] = [
                        'batch' => $batch,
                        'schedule_name' => $scheduleName,
                        'start_date' => $firstSchedule->start_date ? $firstSchedule->start_date->format('M d, Y') : null,
                        'end_date' => $firstSchedule->end_date ? $firstSchedule->end_date->format('M d, Y') : null,
                        'time_out' => $firstSchedule->formatted_time_out,
                        'time_in' => $firstSchedule->formatted_time_in,
                        'days' => $days,
                        'days_count' => count($days),
                        'is_active' => $firstSchedule->end_date ? $firstSchedule->end_date->gte(Carbon::today()) : true,
                        'created_at' => $firstSchedule->created_at->format('M d, Y g:i A')
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $formattedSchedules
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch batch schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get existing individual date range going-out schedules
     */
    public function getIndividualDateRangeSchedules()
    {
        try {
            $individualSchedules = Schedule::where('schedule_type', 'going_out')
                ->whereNotNull('student_id')
                ->whereNotNull('start_date')
                ->whereNotNull('end_date')
                ->where('end_date', '>=', Carbon::today())
                ->with(['student.user'])
                ->orderBy('end_date', 'desc')
                ->orderBy('start_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->get()
                ->groupBy(['student_id', 'schedule_name']);

            $formattedSchedules = [];
            foreach ($individualSchedules as $studentId => $scheduleGroups) {
                foreach ($scheduleGroups as $scheduleName => $schedules) {
                    $firstSchedule = $schedules->first();
                    $student = $firstSchedule->student;
                    $days = $schedules->pluck('day_of_week')->sort()->values()->toArray();

                    $formattedSchedules[] = [
                        'student_id' => $studentId,
                        'student_name' => $student->user->first_name . ' ' . $student->user->last_name,
                        'batch' => $student->batch,
                        'schedule_name' => $scheduleName,
                        'start_date' => $firstSchedule->start_date->format('M d, Y'),
                        'end_date' => $firstSchedule->end_date->format('M d, Y'),
                        'time_out' => $firstSchedule->formatted_time_out,
                        'time_in' => $firstSchedule->formatted_time_in,
                        'days' => $days,
                        'days_count' => count($days),
                        'is_active' => $firstSchedule->end_date->gte(Carbon::today()),
                        'is_current' => $firstSchedule->start_date->lte(Carbon::today()) && $firstSchedule->end_date->gte(Carbon::today()),
                        'created_at' => $firstSchedule->created_at->format('M d, Y g:i A')
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'data' => $formattedSchedules
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch individual date range schedules: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show students for unique leisure schedule selection (individual leisure schedules)
     */
    public function showUniqueLeisureStudents(Request $request)
    {
        try {
            $students = StudentDetail::with('user')->orderBy('student_id')->get();
            $batches = StudentDetail::distinct()->pluck('batch')->sort();
            $groups = StudentDetail::distinct()->pluck('group')->sort();

            $existingSchedules = Schedule::where('schedule_type', 'unique_leisure')
                ->whereNotNull('student_id')
                ->whereNull('start_date')
                ->whereNull('end_date')
                ->get()
                ->groupBy('student_id');
            return view('user-monitor.unique-leisure-students', compact('students', 'batches', 'groups', 'existingSchedules'));

        } catch (\Exception $e) {
            return redirect()->route('monitor.dashboard')
                ->with('error', 'Failed to load students: ' . $e->getMessage());
        }
    }

    public function showCalendar(Request $request)
    {
        try {
            $events = CalendarModel::get_all_events();
            return view('user-monitor.calendar', compact('events'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to load students: ' . $e->getMessage());
        }
    }

    public function setCalendarSchedule(Request $request)
    {
        try {
            $request->validate([
                "event_id" => "required|exists:calendar_events,id",
                "time_out" => "required",
                "time_in"  => "required|after:time_out",
            ]);

            EventSchedule::updateOrCreate([
                'calendar_events_id' => $request->event_id
            ],
            [
                'time_in'            => $request->time_in,
                'time_out'           => $request->time_out,
                'created_by'         => session('user.user_fname') . session('user.user_lname'),
                'created_at'         => now(),
                'updated_by'         => session('user.user_fname') . session('user.user_lname'),
                'updated_at'         => now(),
                'is_deleted'         => false,
            ]);

            return redirect()->back()->with('success', 'Schedule saved successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to create schedule: ' . $e->getMessage());
        }
    }

    public function setUniqueLeisureSchedule(Request $request)
    {
        try {
            $validator = validator($request->all(), [
                'student_ids'   => 'required|array|min:1',
                'student_ids.*' => 'required|exists:student_details,student_id',
                'schedule_name' => 'required|string|max:255',
                'start_date'    => 'required|date',
                'end_date'      => 'required|date|after_or_equal:start_date',
                'time_out'      => 'required|date_format:H:i',
                'time_in'       => 'required|date_format:H:i|after:time_out',
            ]);

            if ($validator->fails()) {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->validated();

            $schedules = [];

            foreach ($data['student_ids'] as $student_id) {
                $schedules[] = [
                    'student_id'       => $student_id,
                    'schedule_type'    => 'going_out',
                    'schedule_name'    => $data['schedule_name'],
                    'start_date'       => $data['start_date'],
                    'end_date'         => $data['end_date'],
                    'is_batch_schedule'=> true,
                    'time_out'         => $data['time_out'],
                    'time_in'          => $data['time_in'],
                    'created_at'       => now(),
                    'created_by'       => session('user.user_fname') . ' ' . session('user.user_lname'),
                ];
            }

            Schedule::insert($schedules);

            return redirect()->route('monitor.unique-leisure.students')
                ->with('success', 'Unique leisure schedules have been set successfully for ' . count($data['student_ids']) . ' student(s).');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Failed to set unique leisure schedules: ' . $e->getMessage())
                ->withInput();
        }
    }
}
