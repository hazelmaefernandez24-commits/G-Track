<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\Academic;
use App\Models\EventSchedule;
use App\Models\StudentDetail;
use App\Models\Going_out;
use App\Models\GoingHomeModel;
use App\Models\InternLogModel;
use App\Models\Internship;
use App\Models\InternshipSchedule;
use App\Models\ManualEntryLog;
use App\Models\Visitor;
use App\Models\Schedule;
use App\Models\NotificationHistory;
use App\Models\PNUser;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class MonitorController extends Controller
{
    public function getTodayAttendance(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');
            $type = $request->query('type', 'academic');
            if ($type === 'going_out') {
                $present = Going_out::whereDate('going_out_date', $today)
                    ->whereNotNull('time_out')
                    ->count();

                $onTime = Going_out::whereDate('going_out_date', $today)
                    ->whereNotNull('time_in')
                    ->where('time_in_remark', 'On Time')
                    ->count();

                $late = Going_out::whereDate('going_out_date', $today)
                    ->whereNotNull('time_in')
                    ->where('time_in_remark', 'Late')
                    ->count();
            } else {
                $present = Academic::whereDate('academic_date', $today)
                    ->whereNotNull('time_out')
                    ->count();

                $onTime = Academic::whereDate('academic_date', $today)
                    ->whereNotNull('time_in')
                    ->where('time_in_remark', 'On Time')
                    ->count();

                $late = Academic::whereDate('academic_date', $today)
                    ->whereNotNull('time_in')
                    ->where('time_in_remark', 'Late')
                    ->count();
            }

            return response()->json([
                'present' => $present,
                'onTime' => $onTime,
                'late' => $late
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch attendance data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function goinghomeLogs(Request $request)
    {
        try{
            $date_time_out = $request->date_time_out ?? now()->format('Y-m-d');
            $date_time_in = $request->date_time_in ?? now()->format('Y-m-d');
            $logs = StudentDetail::get_all_with_goinghome($request->batch, $request->group, $request->type, $date_time_out, $date_time_in, $request->status, $request->fullname)->paginate(20);
            // dd($logs->toArray());
            $types   = Schedule::get_all_type();
            $batches = StudentDetail::select('batch')->distinct()->pluck('batch')->sort();
            $groups  = StudentDetail::select('group')->distinct()->pluck('group')->sort();
            return view('user-monitor.goinghome-logs', compact('logs', 'batches', 'groups', 'types', 'date_time_out', 'date_time_in'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch student data' . $e->getMessage());
        }
    }

    public function internLogs(Request $request)
    {
        try{
            $selectedDate = $request->date;
            $logs = StudentDetail::get_all_with_intern($selectedDate, $request->status, $request->company, $request->fullname, $request->batch, $request->group)->paginate(20);

            $batches = StudentDetail::distinct()->pluck('batch');
            $groups  = StudentDetail::distinct()->pluck('group');
            $companies = InternshipSchedule::distinct()->pluck('company');
            return view('user-monitor.internLogs', compact('selectedDate', 'logs', 'companies', 'batches', 'groups'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch student data' . $e->getMessage());
        }
    }

    public function getStudentData()
    {
        try {
            $studentData = DB::table('student_details')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->select('student_details.batch', DB::raw('COUNT(*) as total_students'))
                ->where('pnph_users.user_role', 'student')
                ->where('pnph_users.status', 'active')
                ->groupBy('student_details.batch')
                ->orderBy('student_details.batch')
                ->get();

            return response()->json($studentData);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to fetch student data' . $e->getMessage());
        }
    }

    public function getLateStudentsByBatch()
    {
        try {
            $today = now()->format('Y-m-d');
            $isSunday = now()->isSunday();

            if ($isSunday) {
                // Get late students by gender for Sunday
                $maleLate = Going_out::whereDate('going_out_date', $today)
                    ->whereNotNull('time_in')
                    ->where('time_in_remark', 'Late')
                    ->whereHas('studentDetail.user', function($query) {
                        $query->where('gender', 'M');
                    })
                    ->count();

                $femaleLate = Going_out::whereDate('going_out_date', $today)
                    ->whereNotNull('time_in')
                    ->where('time_in_remark', 'Late')
                    ->whereHas('studentDetail.user', function($query) {
                        $query->where('gender', 'F');
                    })
                    ->count();

                return response()->json([
                    'male_late' => $maleLate,
                    'female_late' => $femaleLate
                ]);
            } else {
                // Get late students by batch for weekdays
                $lateStudentsByBatch = DB::table('academics as a')
                    ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                    ->select('s.batch', DB::raw('COUNT(*) as late_count'))
                    ->whereDate('a.academic_date', $today)
                    ->whereNotNull('a.time_in')
                    ->where('a.time_in_remark', 'Late')
                    ->groupBy('s.batch')
                    ->orderBy('s.batch')
                    ->get();

                if ($lateStudentsByBatch->isEmpty()) {
                    return response()->json([]);
                }

                return response()->json($lateStudentsByBatch);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch late students data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getGoingOutAttendance()
    {
        try {
            $today = now()->format('Y-m-d');

            // Get Going Out attendance data
            $present = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_out')
                ->count();

            $onTime = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['On Time', 'ontime'])
                ->count();

            $late = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['Late', 'late'])
                ->count();
            return response()->json([
                'present' => $present,
                'onTime' => $onTime,
                'late' => $late
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch going out attendance data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAcademicLogInOutData()
    {
        try {
            $today = now()->format('Y-m-d');
            $isSunday = now()->isSunday();

            // If it's Sunday, return empty data since there's no academic schedule
            if ($isSunday) {
                return response()->json([
                    'logIn' => [
                        'total' => 0,
                        'onTime' => 0,
                        'late' => 0,
                        'early' => 0
                    ],
                    'logOut' => [
                        'total' => 0,
                        'onTime' => 0,
                        'late' => 0,
                        'early' => 0
                    ]
                ]);
            }

            // Get Academic log in data
            $logInTotal = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->count();

            $logInOnTime = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['On Time', 'ontime'])
                ->count();

            $logInLate = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['Late', 'late'])
                ->count();

            $logInEarly = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['Early', 'early'])
                ->count();

            // Get Academic log out data
            $logOutTotal = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_out')
                ->count();

            $logOutOnTime = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_out')
                ->whereIn('time_out_remark', ['On Time', 'ontime'])
                ->count();

            $logOutLate = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_out')
                ->whereIn('time_out_remark', ['Late', 'late'])
                ->count();

            $logOutEarly = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_out')
                ->whereIn('time_out_remark', ['Early', 'early'])
                ->count();

            return response()->json([
                'logIn' => [
                    'total' => $logInTotal,
                    'onTime' => $logInOnTime,
                    'late' => $logInLate,
                    'early' => $logInEarly
                ],
                'logOut' => [
                    'total' => $logOutTotal,
                    'onTime' => $logOutOnTime,
                    'late' => $logOutLate,
                    'early' => $logOutEarly
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch academic log in/out data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getGoingOutLogInOutData()
    {
        try {
            $today = now()->format('Y-m-d');



            // Get Going Out log in data
            $logInTotal = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->count();

            // Check for both proper case and lowercase versions
            $logInOnTime = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['On Time', 'ontime'])
                ->count();

            $logInLate = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['Late', 'late'])
                ->count();

            $logInEarly = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['Early', 'early'])
                ->count();

            // Get Going Out log out data
            $logOutTotal = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_out')
                ->count();

            $logOutOnTime = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_out')
                ->whereIn('time_out_remark', ['On Time', 'ontime'])
                ->count();

            $logOutLate = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_out')
                ->whereIn('time_out_remark', ['Late', 'late'])
                ->count();

            $logOutEarly = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_out')
                ->whereIn('time_out_remark', ['Early', 'early'])
                ->count();

            return response()->json([
                'logIn' => [
                    'total' => $logInTotal,
                    'onTime' => $logInOnTime,
                    'late' => $logInLate,
                    'early' => $logInEarly
                ],
                'logOut' => [
                    'total' => $logOutTotal,
                    'onTime' => $logOutOnTime,
                    'late' => $logOutLate,
                    'early' => $logOutEarly
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch going out log in/out data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAbsentStudentsByBatch()
    {
        try {
            $currentYear = now()->year;

            // Get all distinct batches
            $allBatches = DB::table('student_details')
                ->distinct()
                ->pluck('batch')
                ->sort()
                ->values();

            // Get absent students data for all months of the current year (excluding valid absences)
            $absentData = DB::table('academics as a')
                ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                ->select(
                    DB::raw('MONTH(a.academic_date) as month'),
                    's.batch',
                    DB::raw('COUNT(CASE
                        WHEN (a.educator_consideration = "Absent" AND (a.time_in_absent_validation IS NULL OR a.time_in_absent_validation = "not_valid"))
                        OR (a.time_out_consideration = "Absent" AND (a.time_out_absent_validation IS NULL OR a.time_out_absent_validation = "not_valid"))
                        THEN 1 END) as absent_count')
                )
                ->whereYear('a.academic_date', $currentYear)
                ->where(function($query) {
                    $query->where(function($subQuery) {
                        // Time in absent that is not valid
                        $subQuery->where('a.educator_consideration', 'Absent')
                                ->where(function($validationQuery) {
                                    $validationQuery->whereNull('a.time_in_absent_validation')
                                                  ->orWhere('a.time_in_absent_validation', 'not_valid');
                                });
                    })->orWhere(function($subQuery) {
                        // Time out absent that is not valid
                        $subQuery->where('a.time_out_consideration', 'Absent')
                                ->where(function($validationQuery) {
                                    $validationQuery->whereNull('a.time_out_absent_validation')
                                                  ->orWhere('a.time_out_absent_validation', 'not_valid');
                                });
                    });
                })
                ->groupBy(DB::raw('MONTH(a.academic_date)'), 's.batch')
                ->having('absent_count', '>', 0)
                ->orderBy(DB::raw('MONTH(a.academic_date)'))
                ->orderBy('s.batch')
                ->get();

            // Create a complete dataset with all months and all batches
            $monthNames = [
                1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
                5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
                9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
            ];

            $completeData = [];

            // Initialize all 12 months
            for ($month = 1; $month <= 12; $month++) {
                $monthData = [
                    'month' => $month,
                    'month_name' => $monthNames[$month],
                    'batches' => []
                ];

                // Initialize all batches for this month
                foreach ($allBatches as $batch) {
                    $existingData = $absentData->where('month', $month)->where('batch', $batch)->first();
                    $monthData['batches'][] = [
                        'batch' => $batch,
                        'absent_count' => $existingData ? $existingData->absent_count : 0
                    ];
                }

                $completeData[] = $monthData;
            }
            return response()->json($completeData);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch absent students data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getTimeInOutByBatch(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');
            $type = $request->query('type', 'academic');

            if ($type === 'going_out') {
                // Get going out time in/out data by gender
                $timeData = DB::table('going_outs as g')
                    ->join('student_details as s', 'g.student_id', '=', 's.student_id')
                    ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                    ->select(
                        'u.gender',
                        DB::raw('COUNT(CASE WHEN g.time_out IS NOT NULL THEN 1 END) as time_out_count'),
                        DB::raw('COUNT(CASE WHEN g.time_in IS NOT NULL THEN 1 END) as time_in_count'),
                        DB::raw('COUNT(CASE WHEN g.time_in_remark = "Late" THEN 1 END) as late_count')
                    )
                    ->whereDate('g.going_out_date', $today)
                    ->groupBy('u.gender')
                    ->get();

                // Format data for chart
                $formattedData = [
                    'Male' => ['time_out_count' => 0, 'time_in_count' => 0, 'late_count' => 0],
                    'Female' => ['time_out_count' => 0, 'time_in_count' => 0, 'late_count' => 0]
                ];

                foreach ($timeData as $data) {
                    $gender = $data->gender === 'M' ? 'Male' : 'Female';
                    $formattedData[$gender] = [
                        'time_out_count' => $data->time_out_count,
                        'time_in_count' => $data->time_in_count,
                        'late_count' => $data->late_count
                    ];
                }

                return response()->json($formattedData);
            } else {
                // Get academic time in/out data by batch
                $timeData = DB::table('academics as a')
                    ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                    ->select(
                        's.batch',
                        DB::raw('COUNT(CASE WHEN a.time_out IS NOT NULL THEN 1 END) as time_out_count'),
                        DB::raw('COUNT(CASE WHEN a.time_in IS NOT NULL THEN 1 END) as time_in_count'),
                        DB::raw('COUNT(CASE WHEN a.time_in_remark = "Late" THEN 1 END) as late_count')
                    )
                    ->whereDate('a.academic_date', $today)
                    ->groupBy('s.batch')
                    ->orderBy('s.batch')
                    ->get();

                return response()->json($timeData);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch time in/out data',
                'message' => $e->getMessage()
            ], 500);

        }
    }

    public function academicLogs(Request $request)
    {
        $selectedDate = $request->date;
        $allLogs = StudentDetail::get_all_with_academic($request->batch, $request->group, $selectedDate, $request->status, $request->fullname);
        $perPage = 20;
        $currentPage = Paginator::resolveCurrentPage('page');
        $offset = ($currentPage - 1) * $perPage;

        $logs = new LengthAwarePaginator(
            $allLogs->slice($offset, $perPage)->values(),
            $allLogs->count(),
            $perPage,
            $currentPage,
            [
                'path'     => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]
        );
        $logs->appends($request->query());
        $batches = StudentDetail::select('batch')->distinct()->pluck('batch')->sort();
        $groups  = StudentDetail::select('group')->distinct()->pluck('group')->sort();

        return view('user-monitor.academic-logs', compact('logs', 'batches', 'groups', 'selectedDate'));
    }

    public function goingoutLogs(Request $request)
    {
        try {
            $selectedDate = $request->date;

            // Get logs with filters
            $allLogs = StudentDetail::get_all_with_goingout($request->gender, $selectedDate, $request->status, $request->fullname,$request->session, $request->batch, $request->group);

            // Manual pagination
            $perPage     = 20;
            $currentPage = Paginator::resolveCurrentPage('page');
            $offset      = ($currentPage - 1) * $perPage;

            $logs = new LengthAwarePaginator(
                $allLogs->slice($offset, $perPage)->values(),
                $allLogs->count(),
                $perPage,
                $currentPage,
                [
                    'path'     => Paginator::resolveCurrentPath(),
                    'pageName' => 'page',
                ]
            );
            $logs->appends($request->query());

            // Dropdown filters
            $batches  = StudentDetail::select('batch')->distinct()->pluck('batch')->sort();
            $groups   = StudentDetail::select('group')->distinct()->pluck('group')->sort();
            $sessions = Going_out::whereDate('going_out_date', $selectedDate)
                ->distinct()
                ->pluck('session_number');

            return view('user-monitor.goingout-logs', compact('logs', 'batches', 'groups', 'selectedDate', 'sessions'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load visitor logs: ' . $e->getMessage());
        }
    }

    public function visitorLogs(Request $request)
    {
        try {
            $selectedDate = $request->date;
            $logs = Visitor::get_all_logs($request->type, $selectedDate, $request->fullname)->paginate('20');
            return view('user-monitor.visitor-logs', compact('logs', 'selectedDate'));
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to load visitor logs: ' . $e->getMessage());
        }
    }

    public function setConsiderationAcademic(Request $request)
    {
        $request->validate([
            'remark' => 'required',
            'log_id' => 'required|exists:academics,id',
            'reason' => 'required|string|max:255',
            'type'   => 'required|in:time_out,time_in',
            'choice' => 'required|in:Excused,Not Excused',
        ]);

        DB::beginTransaction();

        try {
            $updateData = [
                'updated_by' => trim(session('user.user_fname') . ' ' . session('user.user_lname')),
                'updated_at' => now(),
            ];

            if($request->remark === 'Absent'){
                $updateData['time_out_consideration'] = $request->choice;
                $updateData['time_out_reason'] = $request->reason;
                $updateData['educator_consideration'] = $request->choice;
                $updateData['time_in_reason'] = $request->reason;
                $updateData['time_out_monitor_name'] = session('user.user_fname') . session('user.user_lname');
                $updateData['time_in_monitor_name'] = session('user.user_fname') . session('user.user_lname');
            }else{
                if ($request->type === 'time_out') {
                    $updateData['time_out_consideration'] = $request->choice;
                    $updateData['time_out_reason'] = $request->reason;
                    $updateData['time_out_monitor_name'] = session('user.user_fname') . session('user.user_lname');
                } else {
                    $updateData['educator_consideration'] = $request->choice;
                    $updateData['time_in_reason'] = $request->reason;
                    $updateData['time_in_monitor_name'] = session('user.user_fname') . session('user.user_lname');
                }
            }

            Academic::where('id', $request->log_id)->update($updateData);

            DB::commit();

            return redirect()->back()->with('success', 'Consideration saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save consideration: ' . $e->getMessage());
        }
    }

    public function setConsiderationGoingOut(Request $request)
    {
        try {
            $request->validate([
                'log_id' => 'required|exists:going_outs,id',
                'reason' => 'required|string|max:255',
                'type'   => 'required|in:time_out,time_in',
                'choice' => 'required|in:Excused,Not Excused',
            ]);

            DB::beginTransaction();

            $updateData = [
                'updated_by' => trim(session('user.user_fname') . ' ' . session('user.user_lname')),
                'updated_at' => now(),
            ];

            if ($request->type === 'time_out') {
                $updateData['time_out_consideration'] = $request->choice;
                $updateData['time_out_reason'] = $request->reason;
                $updateData['time_out_monitor_name'] = session('user.user_fname') . session('user.user_lname');
            } else {
                $updateData['educator_consideration'] = $request->choice;
                $updateData['time_in_reason'] = $request->reason;
                $updateData['time_in_monitor_name'] = session('user.user_fname') . session('user.user_lname');
            }

            Going_out::where('id', $request->log_id)->update($updateData);

            DB::commit();

            return redirect()->back()->with('success', 'Consideration saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save consideration: ' . $e->getMessage());
        }
    }

    public function setConsiderationIntern(Request $request)
    {
        try {
            $request->validate([
                'remark' => 'required',
                'log_id' => 'required|exists:intern_log,id',
                'reason' => 'required|string|max:255',
                'type'   => 'required|in:time_out,time_in',
                'choice' => 'required|in:Excused,Not Excused',
            ]);
            // dd('hello');
            DB::beginTransaction();

            $updateData = [
                'updated_by' => trim(session('user.user_fname') . ' ' . session('user.user_lname')),
                'updated_at' => now(),
            ];
            if($request->remark === 'Absent'){
                $updateData['time_out_consideration'] = $request->choice;
                $updateData['time_out_reason'] = $request->reason;
                $updateData['educator_consideration'] = $request->choice;
                $updateData['time_in_reason'] = $request->reason;
                $updateData['time_out_monitor_name'] = session('user.user_fname') . session('user.user_lname');
                $updateData['time_in_monitor_name'] = session('user.user_fname') . session('user.user_lname');
            }else{
                if ($request->type === 'time_out') {
                    $updateData['time_out_consideration'] = $request->choice;
                    $updateData['time_out_reason'] = $request->reason;
                    $updateData['time_out_monitor_name'] = session('user.user_fname') . session('user.user_lname');
                } else {
                    $updateData['educator_consideration'] = $request->choice;
                    $updateData['time_in_reason'] = $request->reason;
                    $updateData['time_in_monitor_name'] = session('user.user_fname') . session('user.user_lname');
                }
            }

            InternLogModel::where('id', $request->log_id)->update($updateData);

            DB::commit();

            return redirect()->back()->with('success', 'Consideration saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save consideration: ' . $e->getMessage());
        }
    }

    public function setConsiderationGoingHome(Request $request)
    {
        try {
            $request->validate([
                'log_id' => 'required|exists:going_home,id',
                'reason' => 'required|string|max:255',
                'type'   => 'required|in:time_out,time_in',
                'choice' => 'required|in:Excused,Not Excused',
            ]);

            $updateData = [
                'updated_by' => trim(session('user.user_fname') . ' ' . session('user.user_lname')),
                'updated_at' => now(),
            ];

            if ($request->type === 'time_out') {
                $updateData['time_out_consideration'] = $request->choice;
                $updateData['time_out_reason'] = $request->reason;
                $updateData['time_out_monitor_name'] = session('user.user_fname') . session('user.user_lname');
            } else {
                $updateData['time_in_consideration'] = $request->choice;
                $updateData['time_in_reason'] = $request->reason;
                $updateData['time_in_monitor_name'] = session('user.user_fname') . session('user.user_lname');
            }

            DB::beginTransaction();

            GoingHomeModel::where('id', $request->log_id)->update($updateData);

            DB::commit();
            return redirect()->back()->with('success', 'Consideration saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to save consideration: ' . $e->getMessage());
        }
    }

    public function markAbsentIntern(Request $request)
    {
        try {
            $request->validate([
                    'log_id' => 'required|exists:student_details,student_id',
                    'date'       => 'required|date',
                    'reason'     => 'required|string'
                ]);

            $schedule = InternshipSchedule::getInternSchedule($request->log_id, $request->date);
            if (!$schedule) {
                return redirect()->back()->with('error', 'No schedule found for this student on the selected date.');
            }

            DB::beginTransaction();

            $manualData =[
                    'student_id' => $request->log_id,
                    'date' => $request->date,
                    'time_in_remark' => 'Absent',
                    'time_out_remark' => 'Absent',
                    'updated_by' => session('user.user_fname') . session('user.user_lname'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                    'approval_status' => 'approved',
                ];

            ManualEntryLog::create([
                'student_id'   => $request->log_id,
                'log_type'     => 'intern',
                'entry_type'   => 'absent',
                'reason'       => $request->reason,
                'manual_data'  => $manualData,
                'status'       => 'pending',
                'monitor_name' => session('user.user_fname') . session('user.user_lname'),
                'created_at'   => now()
            ]);

            InternLogModel::updateOrCreate(
                [
                    'student_id' => $request->log_id,
                    'date' => $request->date
                ],
                [
                    'is_manual_entry' => 1,
                    'approval_status' => 'pending'
                ]
            );

            DB::commit();

            return redirect()->back()->with('success', 'Student marked as absent successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to mark absent: ' . $e->getMessage());
        }
    }

    public function markAbsent(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:student_details,student_id',
                'date'       => 'required|date',
                'reason'     => 'required|string'
            ]);

            $isSunday = Carbon::parse($request->date)->isSunday();
            if($isSunday){
                return redirect()->back()->with('error', 'No Academic Schedue for Sunday');
            }
            $student = StudentDetail::get_student($request->student_id);
            if (!$student) {
                return redirect()->back()->with('error', 'Student not found.');
            }
            $logs = Academic::getStudentLogRecord($request->student_id, $request->date);
            if($logs){
                return redirect()->back()->with('error', 'The student already logged this date');
            }
            $schedule = EventSchedule::get_schedule($request->date);
            if(!$schedule){
                $schedule = Schedule::get_academic_schedule_by_id($request->student_id, $request->date);
                if (!$schedule) {
                    $schedule = Schedule::get_academic_schedule($student->batch, $student->group, $request->date);
                }
            }
            if (!$schedule) {
                return redirect()->back()->with('error', 'No schedule found for this student on the selected date.');
            }

            DB::beginTransaction();

            $manualData= [
                    'student_id' => $request->student_id,
                    'academic_date' => date('Y-m-d', strtotime($request->date)),
                    'time_in_remark' => 'Absent',
                    'time_out_remark' => 'Absent',
                    'updated_by' => session('user.user_fname') . session('user.user_lname'),
                    'updated_at' => now()->format('Y-m-d H:i:s'),
                    'approval_status'       => 'approved',
                ];

            ManualEntryLog::create([
                'student_id'   => $request->student_id,
                'log_type'     => 'academic',
                'entry_type'   => 'absent',
                'reason'       => $request->reason,
                'manual_data'  => $manualData,
                'status'       => 'pending',
                'monitor_name' => session('user.user_fname') . session('user.user_lname'),
                'created_at'   => now()
            ]);

            Academic::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'academic_date' => $request->date
                ],
                [
                    'is_manual_entry' => 1,
                    'approval_status' => 'pending'
                ]
            );

            DB::commit();

            return redirect()->back()->with('success', 'Student marked as absent successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to mark absent: ' . $e->getMessage());
        }
    }

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

            $updateField = $considerationType === 'time_out' ? 'time_out_absent_validation' : 'time_in_absent_validation';
            $otherField = $considerationType === 'time_out' ? 'time_in_absent_validation' : 'time_out_absent_validation';

            DB::beginTransaction();

            try {
                // Update the current validation field
                $updateData = [
                    $updateField => $request->validation,
                    'updated_by' => session('user_fname') . ' ' . session('user_lname'),
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
                    'auto_synced' => $autoSynced
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update validation: ' . $e->getMessage()
            ]);
        }
    }

    public function updateVisitorConsideration(Request $request, $id)
    {
        try {
            $request->validate([
                'consideration' => 'required|in:Excused,Not Excused,Absent',
                'reason' => 'required|string|max:500'
            ]);

            $visitor = Visitor::findOrFail($id);

            DB::beginTransaction();

            try {
                $visitor->update([
                    'consideration' => $request->consideration,
                    'reason' => $request->reason,
                    'monitor_id' => session('user_id')
                ]);

                DB::commit();
                return response()->json([
                    'success' => true,
                    'message' => 'Consideration updated successfully.'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update consideration: ' . $e->getMessage()
            ]);
        }
    }

    public function performAcademicLogout(Request $request, $id)
    {
        try {
            $academic = Academic::findOrFail($id);

            // Check if already logged out
            if ($academic->time_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student has already logged out.'
                ]);
            }

            $currentTime = Carbon::parse(now());
            $today = $currentTime->format('l'); // Day name (Monday, Tuesday, etc.)

            // Get schedule for this student using the same logic as student logging
            $student = $academic->studentDetail;
            $scheduleResult = $this->getCurrentActiveSchedule($student, $today);
            $schedule = $scheduleResult['schedule'];
            $scheduleType = $scheduleResult['type'];

            // Check if schedule exists for today
            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedule set for today! Cannot perform logout operation.'
                ]);
            }

            // Check if it's too late to logout (after scheduled end time - NO GRACE PERIOD)
            $scheduleEndTime = \Carbon\Carbon::parse($schedule->time_in); // When academic period ends

            if ($currentTime > $scheduleEndTime) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time out period has ended! Please contact your educator for assistance.'
                ]);
            }

            // Academic logs: Determine remark based on schedule with 15-min grace period
            $scheduleStartTime = Carbon::parse($schedule->time_out); // When students should start logging out

            // 15-minute grace period logic
            $remark = 'On Time';

            $gracePeriodMinutes = $schedule->grace_period_logout_minutes;

            if ($gracePeriodMinutes !== null) {
                if ($currentTime->lessThan($scheduleStartTime->copy()->subMinutes($gracePeriodMinutes))) {
                    $remark = 'Early';
                } elseif ($currentTime->greaterThan($scheduleStartTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            } else {
                if ($currentTime->lessThan($scheduleStartTime)) {
                    $remark = 'Early';
                } elseif ($currentTime->greaterThan($scheduleStartTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            }

            $created_by = session('user_fname') . ' ' . session('user_lname');

            // Update the academic log
            $academic->update([
                'time_out' => $currentTime->format('H:i:s'),
                'time_out_remark' => $remark,
                'created_by' => $created_by,
                'created_at' => now()
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Student logged out successfully.',
                'time_out' => $currentTime->format('g:i A'),
                'remark' => $remark,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log out student: ' . $e->getMessage()
            ]);
        }
    }

    public function performAcademicLogin(Request $request, $id)
    {
        try {
            $academic = Academic::findOrFail($id);

            // Check if not logged out yet
            if (!$academic->time_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student must log out first before logging in.'
                ]);
            }

            // Check if already logged in
            if ($academic->time_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student has already logged in.'
                ]);
            }

            $currentTime = Carbon::parse(now());
            $today = $currentTime->format('l'); // Day name (Monday, Tuesday, etc.)

            // Get schedule for this student using the same logic as student logging
            $student = $academic->studentDetail;
            $scheduleResult = $this->getCurrentActiveSchedule($student, $today);
            $schedule = $scheduleResult['schedule'];
            $scheduleType = $scheduleResult['type'];

            // Check if schedule exists for today
            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedule set for today! Cannot perform login operation.'
                ]);
            }

            // Academic logs: Allow login anytime, determine remark based on schedule with dynamic grace period
            $scheduleEndTime = Carbon::parse($schedule->time_in); // When students should return

            // Get login grace period from schedule (no default - null means no grace period)
            $gracePeriodMinutes = $schedule->grace_period_login_minutes;

            // Dynamic grace period logic
            $remark = 'On Time'; // Default
            if ($gracePeriodMinutes !== null) {
                if ($currentTime->lessThan($scheduleEndTime)) {
                    $remark = 'Early';
                } elseif ($currentTime->greaterThan($scheduleEndTime->copy()->addMinutes($gracePeriodMinutes))) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time'; // Within grace period
                }
            } else {
                // No grace period - exact timing
                if ($currentTime->lessThan($scheduleEndTime)) {
                    $remark = 'Early';
                } elseif ($currentTime->greaterThan($scheduleEndTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time'; // Exact time
                }
            }

            $updated_by = session('user_fname') . ' ' . session('user_lname');

            // Update the academic log
            $academic->update([
                'time_in' => $currentTime->format('H:i:s'),
                'time_in_remark' => $remark,
                'updated_by' => $updated_by,
                'updated_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Student logged in successfully.',
                'time_in' => $currentTime->format('g:i A'),
                'remark' => $remark
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log in student: ' . $e->getMessage()
            ]);
        }
    }

    public function performGoingoutLogout(Request $request, $id)
    {
        try {
            // Validate destination and purpose
            $request->validate([
                'destination' => 'required|string|max:100',
                'purpose' => 'required|string|max:100'
            ]);

            $goingOut = Going_out::findOrFail($id);

            // Check if already logged out
            if ($goingOut->time_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student has already logged out.'
                ]);
            }

            $currentTime = now();
            $today = $currentTime->format('l'); // Day name (Monday, Tuesday, etc.)

            // Get schedule for this student - check individual going-out schedule first
            $student = $goingOut->studentDetail;

            // First check for individual going-out schedule for this specific student
            $schedule = DB::table('schedules')
                ->where('student_id', $student->student_id)
                ->where('day_of_week', $today)
                ->where('schedule_type', 'going_out') // Only look for going-out schedules
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                        ->orWhereDate('valid_until', '>=', now()->toDateString());
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            // If no individual schedule found, fall back to general going-out schedule
            if (!$schedule) {
                $schedule = DB::table('schedules')
                    ->where('gender', $student->user->gender)
                    ->where('day_of_week', $today)
                    ->whereNull('student_id') // General schedules have null student_id
                    ->where('schedule_type', 'going_out') // Only look for going-out schedules
                    ->where(function ($query) {
                        $query->whereNull('valid_until')
                            ->orWhereDate('valid_until', '>=', now()->toDateString());
                    })
                    ->orderBy('updated_at', 'desc')
                    ->first();
            }

            // Check if schedule exists for today
            if (!$schedule) {
                return response()->json([
                    'success' => false,
                    'message' => 'No schedule set for today! Cannot perform logout operation.'
                ]);
            }

            // Apply same time restrictions as student-side
            $scheduleStartTime = \Carbon\Carbon::parse($schedule->time_out);
            $scheduleEndTime = \Carbon\Carbon::parse($schedule->time_in);

            // Check if it's not scheduled time yet
            if ($currentTime->format('H:i:s') < $scheduleStartTime->format('H:i:s')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not scheduled time yet! Going out starts at ' . $scheduleStartTime->format('g:i A') . '.'
                ]);
            }

            // Check if it's too late to logout (after scheduled end time - NO GRACE PERIOD)
            if ($currentTime->format('H:i:s') > $scheduleEndTime->format('H:i:s')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Time out period has ended! Please contact your educator for assistance.'
                ]);
            }

            $scheduleStartTime = \Carbon\Carbon::parse($currentTime->toDateString() . ' ' . $schedule->time_out);
            $scheduleEndTime = \Carbon\Carbon::parse($currentTime->toDateString() . ' ' . $schedule->time_in);

            if ($currentTime->lessThan($scheduleStartTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Going out period has not started yet! Going out starts at ' . $scheduleStartTime->format('g:i A') . '.'
                ]);
            }

            if ($currentTime->greaterThan($scheduleEndTime)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Going out period has ended! Going out ended at ' . $scheduleEndTime->format('g:i A') . '.'
                ]);
            }

            $remark = 'On Time'; // Always "On Time" since only allowed during scheduled period

            $created_by = session('user_fname') . ' ' . session('user_lname');

            $goingOut->update([
                'destination' => $request->destination,
                'purpose' => $request->purpose,
                'time_out' => $currentTime->format('H:i:s'),
                'time_out_remark' => $remark,
                'created_by' => $created_by,
                'created_at' => now()
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Student logged out successfully.',
                'time_out' => $currentTime->format('g:i A'),
                'remark' => $remark
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log out student: ' . $e->getMessage()
            ]);
        }
    }

    public function performGoingoutLogin(Request $request, $id)
    {
        try {
            $goingOut = Going_out::findOrFail($id);

            if (!$goingOut->time_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student must log out first before logging in.'
                ]);
            }

            if ($goingOut->time_in) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student has already logged in.'
                ]);
            }

            $currentTime = now();
            $today = $currentTime->format('l'); // Day name (Monday, Tuesday, etc.)

            $student = $goingOut->studentDetail;

            $schedule = DB::table('schedules')
                ->where('student_id', $student->student_id)
                ->where('day_of_week', $today)
                ->where('schedule_type', 'going_out') // Only look for going-out schedules
                ->where(function ($query) {
                    $query->whereNull('valid_until')
                        ->orWhereDate('valid_until', '>=', now()->toDateString());
                })
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$schedule) {
                $schedule = DB::table('schedules')
                    ->where('gender', $student->user->gender)
                    ->where('day_of_week', $today)
                    ->whereNull('student_id') // General schedules have null student_id
                    ->where('schedule_type', 'going_out') // Only look for going-out schedules
                    ->where(function ($query) {
                        $query->whereNull('valid_until')
                            ->orWhereDate('valid_until', '>=', now()->toDateString());
                    })
                    ->orderBy('updated_at', 'desc')
                    ->first();
            }

            $remark = 'On Time'; // Default
            if ($schedule) {
               $scheduleEndTime = Carbon::parse($schedule->time_in);

                if ($currentTime->greaterThan($scheduleEndTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            }

            $updated_by = session('user_fname') . ' ' . session('user_lname');

            $goingOut->update([
                'time_in' => $currentTime->format('H:i:s'),
                'time_in_remark' => $remark,
                'session_status' => 'completed',
                'updated_by' => $updated_by,
                'updated_at' => now()
            ]);
            return response()->json([
                'success' => true,
                'message' => 'Student logged in successfully.',
                'time_in' => $currentTime->format('g:i A'),
                'remark' => $remark
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log in student: ' . $e->getMessage()
            ]);
        }
    }

    private function getCurrentActiveSchedule($student, $today)
    {
        $irregularSchedule = Schedule::where([
            ['student_id', $student->student_id],
            ['day_of_week', $today],
            ['schedule_type', 'academic']
        ])->where(function ($query) {
            $query->whereNull('valid_until')
                ->orWhere('valid_until', '>=', Carbon::today());
        })
        ->orderBy('updated_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->first();

        if ($irregularSchedule) {
            return ['schedule' => $irregularSchedule, 'type' => 'irregular'];
        }

        $batchSchedule = Schedule::where([
            ['batch', $student->batch],
            ['pn_group', $student->group],
            ['day_of_week', $today],
            ['schedule_type', 'academic']
        ])->where(function ($query) {
            $query->whereNull('valid_until')
                ->orWhere('valid_until', '>=', Carbon::today());
        })
        ->orderBy('updated_at', 'desc')
        ->orderBy('created_at', 'desc')
        ->first();

        if ($batchSchedule) {
            return ['schedule' => $batchSchedule, 'type' => 'batch'];
        }
        return ['schedule' => null, 'type' => 'none'];
    }
}
