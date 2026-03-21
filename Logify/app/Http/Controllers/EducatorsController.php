<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Models\Academic;
use App\Models\Going_out;
use App\Models\GoingHomeModel;
use App\Models\InternLogModel;
use App\Models\InternshipSchedule;
use App\Models\NotificationView;
use App\Models\PNUser;
use App\Models\Schedule;
use App\Models\StudentDetail;
use App\Models\StudentDetails;
use App\Models\Visitor;

class EducatorsController extends Controller
{
    public function show()
    {
        // Academic schedules (batch + group, active within start/end date)
        $academicSchedule = Schedule::whereNotNull('batch')
            ->whereNotNull('pn_group')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        // Going out schedules (gender-based, active within start/end date)
        $goingOutSchedule = Schedule::whereNotNull('gender')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        // Irregular schedules (student-specific, active within start/end date)
        $irregularSchedule = Schedule::whereNotNull('student_id')
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->get();

        return view('user-educator.dashboard', compact(
            'academicSchedule',
            'goingOutSchedule',
            'irregularSchedule'
        ));
    }

    public function getTodayAttendance()
    {
        try {
            $today = now()->format('Y-m-d');
            $isSunday = now()->isSunday();

            if ($isSunday) {
                return response()->json([
                    'present' => 0,
                    'onTime' => 0,
                    'late' => 0,
                    'isAcademic' => true
                ]);
            }

            $present = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_out')
                ->count();

            $onTime = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['On Time', 'ontime'])
                ->count();

            $late = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->whereIn('time_in_remark', ['Late', 'late'])
                ->count();

            return response()->json([
                'present' => $present,
                'onTime' => $onTime,
                'late' => $late,
                'isAcademic' => true
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to fetch academic attendance data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getGoingOutAttendance()
    {
        try {
            $today = now()->format('Y-m-d');

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

    public function getLogsData()
    {
        try {
            $today = now()->format('Y-m-d');

            $total_students = StudentDetail::whereHas('user', function ($q) {
                $q->where('status', 'active');
            })
            ->count();

            $academic = Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->count();

            $going_out = Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->count();

            $intern = InternLogModel::whereDate('date', $today)
                ->whereNotNull('time_in')
                ->count();

            $going_home = GoingHomeModel::whereDate('date_time_in', $today)
                ->whereNotNull('time_in')
                ->count();

            $visitor = Visitor::whereDate('visit_date', $today)
                ->whereNotNull('time_out')
                ->count();

            return response()->json([
                'total_students' => $total_students,
                'academic'    => $academic,
                'going_out'   => $going_out,
                'intern'      => $intern,
                'going_home'  => $going_home,
                'visitor'     => $visitor,
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
                $lateStudentsByBatch = DB::table('student_details as s')
                    ->select(
                        's.batch',
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "Late" THEN a.student_id END) as academic_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_in_remark = "Late" THEN g.student_id END) as going_out_late_count')
                    )
                    ->leftJoin('academics as a', function($join) use ($today) {
                        $join->on('s.student_id', '=', 'a.student_id')
                            ->whereDate('a.academic_date', $today)
                            ->whereNotNull('a.time_in')
                            ->where('a.time_in_remark', 'Late');
                    })
                    ->leftJoin('going_outs as g', function($join) use ($today) {
                        $join->on('s.student_id', '=', 'g.student_id')
                            ->whereDate('g.going_out_date', $today)
                            ->whereNotNull('g.time_in')
                            ->where('g.time_in_remark', 'Late');
                    })
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
            return response()->json([
                'error' => 'Failed to fetch student data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAcademicReport(Request $request)
    {
        $month = $request->input('month') ?? now()->format('Y-m');
        $batch = $request->input('batch');
        $group = $request->input('group');

        $reports = StudentDetail::getAcademicReport($month, $batch, $group)->paginate(20);
        $batches = StudentDetail::distinct()->pluck('batch');
        $groups  = StudentDetail::distinct()->pluck('group');

        return view('user-educator.academicReport', compact('reports', 'month', 'batches', 'groups'));
    }

    public function getLeisureReport(Request $request)
    {
        $month = $request->input('month') ?? now()->format('Y-m');
        $batch = $request->input('batch');
        $group = $request->input('group');

        $reports = StudentDetail::getLeisureReport($month, $batch, $group)->paginate(20);
        $batches = StudentDetail::distinct()->pluck('batch');
        $groups  = StudentDetail::distinct()->pluck('group');
        return view('user-educator.leisureReport', compact('reports', 'month', 'batches', 'groups'));
    }

    public function getInternReport(Request $request)
    {
        $month = $request->input('month') ?? now()->format('Y-m');
        $batch = $request->input('batch');
        $group = $request->input('group');

        $reports = StudentDetail::getInternReport($month, $batch, $group)->paginate(20);
        $batches = StudentDetail::distinct()->pluck('batch');
        $groups  = StudentDetail::distinct()->pluck('group');
        return view('user-educator.internReport', compact('reports', 'month', 'batches', 'groups'));
    }

    public function getGoingHomeReport(Request $request)
    {
        $month = $request->input('month') ?? now()->format('Y-m');
        $batch = $request->input('batch');
        $group = $request->input('group');

        $reports = StudentDetail::getGoingHomeReport($month, $batch, $group)->paginate(20);
        $batches = StudentDetail::distinct()->pluck('batch');
        $groups  = StudentDetail::distinct()->pluck('group');
        return view('user-educator.goinghomeReport', compact('reports', 'month', 'batches', 'groups'));
    }

    public function getLateAnalytics(Request $request)
    {
        try {
            $month = $request->input('month', now()->format('m'));
            $year = $request->input('year', now()->format('Y'));
            $batch = $request->input('batch');
            $semester = $request->input('semester');

            $academicQuery = DB::table('academics as a')
                ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                ->select(
                    's.student_id',
                    'u.user_fname as first_name',
                    'u.user_lname as last_name',
                    's.batch',
                    's.group',
                    DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "Late" THEN a.id END) as academic_logout_late_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "Late" AND a.time_out_consideration = "Excused" THEN a.id END) as academic_logout_excused_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "Late" THEN a.id END) as academic_login_late_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "Late" AND a.educator_consideration = "Excused" THEN a.id END) as academic_login_excused_count'),
                    DB::raw('"academic" as late_type')
                )
                ->whereMonth('a.academic_date', $month)
                ->whereYear('a.academic_date', $year)
                ->where(function($query) {
                    $query->where('a.time_out_remark', 'Late')
                        ->orWhere('a.time_in_remark', 'Late');
                })
                ->where('u.status', 'active');

            if (!empty($semester) && is_numeric($semester)) {
                $academicQuery->where('a.semester_id', $semester);
            }

            $goingOutQuery = DB::table('going_outs as g')
                ->join('student_details as s', 'g.student_id', '=', 's.student_id')
                ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                ->select(
                    's.student_id',
                    'u.user_fname as first_name',
                    'u.user_lname as last_name',
                    's.batch',
                    's.group',
                    DB::raw('COUNT(DISTINCT CASE WHEN g.time_out_remark = "Late" THEN g.id END) as goingout_logout_late_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN g.time_out_remark = "Late" AND g.time_out_consideration = "Excused" THEN g.id END) as goingout_logout_excused_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN g.time_in_remark = "Late" THEN g.id END) as goingout_login_late_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN g.time_in_remark = "Late" AND g.educator_consideration = "Excused" THEN g.id END) as goingout_login_excused_count'),
                    DB::raw('"going_out" as late_type')
                )
                ->whereMonth('g.going_out_date', $month)
                ->whereYear('g.going_out_date', $year)
                ->where(function($query) {
                    $query->where('g.time_out_remark', 'Late')
                        ->orWhere('g.time_in_remark', 'Late');
                })
                ->where('u.status', 'active');

            if ($batch) {
                $academicQuery->where('s.batch', $batch);
                $goingOutQuery->where('s.batch', $batch);
            }

            try {
                $academicLateStudents = $academicQuery->groupBy(
                    's.student_id',
                    'u.user_fname',
                    'u.user_lname',
                    's.batch',
                    's.group'
                )->get();
            } catch (\Exception $e) {
                throw $e;
            }

            try {
                $goingOutLateStudents = $goingOutQuery->groupBy(
                    's.student_id',
                    'u.user_fname',
                    'u.user_lname',
                    's.batch',
                    's.group'
                )->get();
            } catch (\Exception $e) {
                throw $e;
            }

            $lateStudents = collect([...$academicLateStudents, ...$goingOutLateStudents])
                ->groupBy('student_id')
                ->map(function ($group) {
                    $first = $group->first();

                    $academic_logout_late = 0;
                    $academic_logout_excused = 0;
                    $academic_login_late = 0;
                    $academic_login_excused = 0;
                    $goingout_logout_late = 0;
                    $goingout_logout_excused = 0;
                    $goingout_login_late = 0;
                    $goingout_login_excused = 0;

                    // Sum up counts from both academic and going out records
                    foreach ($group as $record) {
                        if ($record->late_type === 'academic') {
                            $academic_logout_late += $record->academic_logout_late_count ?? 0;
                            $academic_logout_excused += $record->academic_logout_excused_count ?? 0;
                            $academic_login_late += $record->academic_login_late_count ?? 0;
                            $academic_login_excused += $record->academic_login_excused_count ?? 0;
                        } else {
                            $goingout_logout_late += $record->goingout_logout_late_count ?? 0;
                            $goingout_logout_excused += $record->goingout_logout_excused_count ?? 0;
                            $goingout_login_late += $record->goingout_login_late_count ?? 0;
                            $goingout_login_excused += $record->goingout_login_excused_count ?? 0;
                        }
                    }

                    // Calculate totals
                    $total_logout_late = $academic_logout_late + $goingout_logout_late;
                    $total_login_late = $academic_login_late + $goingout_login_late;
                    $total_logout_excused = $academic_logout_excused + $goingout_logout_excused;
                    $total_login_excused = $academic_login_excused + $goingout_login_excused;
                    $total_late = $total_logout_late + $total_login_late;
                    $total_excused = $total_logout_excused + $total_login_excused;

                    return [
                        'student_id' => $first->student_id,
                        'first_name' => $first->first_name,
                        'last_name' => $first->last_name,
                        'batch' => $first->batch,
                        'group' => $first->group,
                        'logout_late_count' => $total_logout_late,
                        'logout_excused_count' => $total_logout_excused,
                        'login_late_count' => $total_login_late,
                        'login_excused_count' => $total_login_excused,
                        'total_late_count' => $total_late,
                        'total_excused_count' => $total_excused,
                        'late_types' => $group->pluck('late_type')->toArray()
                    ];
                })
                ->values()
                ->sortByDesc('total_late_count');

            $batches = DB::table('student_details')
                ->select('batch')
                ->distinct()
                ->orderBy('batch')
                ->pluck('batch');

            return response()->json([
                'lateStudents' => $lateStudents,
                'batches' => $batches,
                'currentMonth' => $month,
                'currentYear' => $year,
                'selectedBatch' => $batch
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to fetch late analytics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getStudentLateHistory(Request $request)
    {
        try {
            $studentId = $request->input('student_id');
            $month = $request->input('month', now()->format('m'));
            $year = $request->input('year', now()->format('Y'));
            $semester = $request->input('semester');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required'
                ], 400);
            }

            $academicLogoutLateHistory = DB::table('academics as a')
                ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                ->leftJoin('schedules as sch', function($join) {
                    $join->on('s.batch', '=', 'sch.batch')
                         ->on('s.group', '=', 'sch.pn_group')
                         ->where('sch.schedule_type', '=', 'academic')
                         ->where('sch.is_deleted', '=', false)
                         ->whereRaw('DAYNAME(a.academic_date) = sch.day_of_week');
                })
                ->select(
                    'a.academic_date',
                    'a.time_out as actual_time',
                    DB::raw('COALESCE(sch.time_out, "08:00:00") as scheduled_time'), // Default to 8:00 AM if no schedule
                    'a.time_out_consideration as consideration',
                    DB::raw('"academic" as late_type'),
                    DB::raw('"Log Out" as logging_process')
                )
                ->where('s.student_id', $studentId)
                ->whereMonth('a.academic_date', $month)
                ->whereYear('a.academic_date', $year)
                ->where('a.time_out_remark', 'Late');

            // Add semester filter if provided
            if ($semester) {
                $academicLogoutLateHistory->where('a.semester_id', $semester);
            }

            $academicLoginLateHistory = DB::table('academics as a')
                ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                ->leftJoin('schedules as sch', function($join) {
                    $join->on('s.batch', '=', 'sch.batch')
                         ->on('s.group', '=', 'sch.pn_group')
                         ->where('sch.schedule_type', '=', 'academic')
                         ->where('sch.is_deleted', '=', false)
                         ->whereRaw('DAYNAME(a.academic_date) = sch.day_of_week');
                })
                ->select(
                    'a.academic_date',
                    'a.time_in as actual_time',
                    DB::raw('COALESCE(sch.time_in, "20:00:00") as scheduled_time'), // Default to 8:00 PM if no schedule
                    'a.educator_consideration as consideration',
                    DB::raw('"academic" as late_type'),
                    DB::raw('"Log In" as logging_process')
                )
                ->where('s.student_id', $studentId)
                ->whereMonth('a.academic_date', $month)
                ->whereYear('a.academic_date', $year)
                ->where('a.time_in_remark', 'Late');

            // Add semester filter if provided
            if ($semester) {
                $academicLoginLateHistory->where('a.semester_id', $semester);
            }

            // Get going out late history - separate log out and log in records
            $goingOutLogoutLateHistory = DB::table('going_outs as g')
                ->join('student_details as s', 'g.student_id', '=', 's.student_id')
                ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                ->leftJoin('schedules as sch', function($join) {
                    $join->on('s.batch', '=', 'sch.batch')
                         ->on('s.group', '=', 'sch.pn_group')
                         ->where('sch.schedule_type', '=', 'going_out')
                         ->where('sch.is_deleted', '=', false)
                         ->whereRaw('DAYNAME(g.going_out_date) = sch.day_of_week');
                })
                ->select(
                    'g.going_out_date as academic_date',
                    'g.time_out as actual_time',
                    DB::raw('COALESCE(sch.time_out, "17:00:00") as scheduled_time'), // Default to 5:00 PM
                    'g.time_out_consideration as consideration',
                    DB::raw('"going_out" as late_type'),
                    DB::raw('"Log Out" as logging_process')
                )
                ->where('s.student_id', $studentId)
                ->whereMonth('g.going_out_date', $month)
                ->whereYear('g.going_out_date', $year)
                ->where('g.time_out_remark', 'Late');

            $goingOutLoginLateHistory = DB::table('going_outs as g')
                ->join('student_details as s', 'g.student_id', '=', 's.student_id')
                ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                ->leftJoin('schedules as sch', function($join) {
                    $join->on('s.batch', '=', 'sch.batch')
                         ->on('s.group', '=', 'sch.pn_group')
                         ->where('sch.schedule_type', '=', 'going_out')
                         ->where('sch.is_deleted', '=', false)
                         ->whereRaw('DAYNAME(g.going_out_date) = sch.day_of_week');
                })
                ->select(
                    'g.going_out_date as academic_date',
                    'g.time_in as actual_time',
                    DB::raw('COALESCE(sch.time_in, "22:00:00") as scheduled_time'), // Default to 10:00 PM for going out return
                    'g.educator_consideration as consideration',
                    DB::raw('"going_out" as late_type'),
                    DB::raw('"Log In" as logging_process')
                )
                ->where('s.student_id', $studentId)
                ->whereMonth('g.going_out_date', $month)
                ->whereYear('g.going_out_date', $year)
                ->where('g.time_in_remark', 'Late');

            // Combine all late history records
            $lateHistory = collect()
                ->merge($academicLogoutLateHistory->get())
                ->merge($academicLoginLateHistory->get())
                ->merge($goingOutLogoutLateHistory->get())
                ->merge($goingOutLoginLateHistory->get())
                ->sortByDesc('academic_date')
                ->sortByDesc('logging_process')
                ->values(); // Reset array keys

            return response()->json([
                'success' => true,
                'data' => [
                    'lateHistory' => $lateHistory
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student late history: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getLineGraph(Request $request)
    {
        try {
            // Determine which table to use (support multiple log types)
            $type = $request->query('type', 'academic'); // default: academic
            $tableMap = [
                'academic'   => 'academics',
                'going_out'  => 'going_outs',
                'intern'     => 'intern_log',
                'going_home' => 'going_home',
                'visitor'    => 'visitors'
            ];
            $table = $tableMap[$type] ?? 'academics';
            // die($type);
            // Get all distinct batches
            $allBatches = DB::table('student_details')
                ->distinct()
                ->pluck('batch')
                ->sort()
                ->values();

            // Build query based on type (daily instead of monthly)
            if ($type === 'intern') {
                $query = DB::table($table . ' as t')
                    ->join('student_details as s', 't.student_id', '=', 's.student_id')
                    ->select(
                        DB::raw('DATE(t.date) as day'),
                        's.batch',
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Absent" THEN t.student_id END) as absent_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Late" THEN t.student_id END) as late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Early" THEN t.student_id END) as early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "On Time" THEN t.student_id END) as ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "Late" THEN t.student_id END) as out_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "Early" THEN t.student_id END) as out_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "On Time" THEN t.student_id END) as out_ontime_count')
                    )
                    ->whereBetween(DB::raw('t.date'), [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                    ->groupBy(DB::raw('DATE(t.date)'), 's.batch')
                    ->orderBy(DB::raw('DATE(t.date)'))
                    ->orderBy('s.batch');
            } else if ($type === 'going_home') {
                $query = DB::table($table . ' as t')
                    ->join('student_details as s', 't.student_id', '=', 's.student_id')
                    ->select(
                        DB::raw('DATE(t.date_time_in) as day'),
                        's.batch',
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remarks = "Absent" THEN t.student_id END) as absent_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remarks = "Late" THEN t.student_id END) as late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remarks = "Early" THEN t.student_id END) as early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remarks = "On Time" THEN t.student_id END) as ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remarks = "Late" THEN t.student_id END) as out_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remarks = "Early" THEN t.student_id END) as out_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remarks = "On Time" THEN t.student_id END) as out_ontime_count')
                    )
                    ->whereBetween(DB::raw('t.date_time_in'), [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                    ->groupBy(DB::raw('DATE(t.date_time_in)'), 's.batch')
                    ->orderBy(DB::raw('DATE(t.date_time_in)'))
                    ->orderBy('s.batch');
            } else if ($type === 'academic') {
                $query = DB::table($table . ' as t')
                    ->join('student_details as s', 't.student_id', '=', 's.student_id')
                    ->select(
                        DB::raw('DATE(t.academic_date) as day'),
                        's.batch',
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Absent" THEN t.student_id END) as absent_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Late" THEN t.student_id END) as late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Early" THEN t.student_id END) as early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "On Time" THEN t.student_id END) as ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "Late" THEN t.student_id END) as out_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "Early" THEN t.student_id END) as out_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "On Time" THEN t.student_id END) as out_ontime_count')
                    )
                    ->whereBetween(DB::raw('t.academic_date'), [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                    ->groupBy(DB::raw('DATE(t.academic_date)'), 's.batch')
                    ->orderBy(DB::raw('DATE(t.academic_date)'))
                    ->orderBy('s.batch');
            } else if ($type === 'going_out') {
                $query = DB::table($table . ' as t')
                    ->join('student_details as s', 't.student_id', '=', 's.student_id')
                    ->select(
                        DB::raw('DATE(t.going_out_date) as day'),
                        's.batch',
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Absent" THEN t.student_id END) as absent_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Late" THEN t.student_id END) as late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "Early" THEN t.student_id END) as early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_in_remark = "On Time" THEN t.student_id END) as ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "Late" THEN t.student_id END) as out_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "Early" THEN t.student_id END) as out_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN t.time_out_remark = "On Time" THEN t.student_id END) as out_ontime_count')
                    )
                    ->whereBetween(DB::raw('t.going_out_date'), [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                    ->groupBy(DB::raw('DATE(t.going_out_date)'), 's.batch')
                    ->orderBy(DB::raw('DATE(t.going_out_date)'))
                    ->orderBy('s.batch');
            } elseif ($type === 'visitor'){
                $query = DB::table($table . ' as t')
                ->select(
                    DB::raw('DATE(t.visit_date) as day'),
                    DB::raw('COUNT(CASE WHEN t.time_in IS NOT NULL THEN t.id END) as in_count'),
                    DB::raw('COUNT(CASE WHEN t.time_out IS NOT NULL THEN t.id END) as out_count')
                )
                ->whereBetween(DB::raw('t.visit_date'), [now()->subDays(6)->startOfDay(), now()->endOfDay()])
                ->groupBy(DB::raw('DATE(t.visit_date)'))
                ->orderBy(DB::raw('DATE(t.visit_date)'));
            }

            $stats = $query->get();

            if ($type === 'visitor') {
                $completeData = [];
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i)->toDateString();
                    $record = $stats->where('day', $day)->first();

                    $completeData[] = [
                        'day'       => $day,
                        'day_name'  => now()->subDays($i)->format('l'),
                        'in_count'  => $record ? (int)$record->in_count : 0,
                        'out_count' => $record ? (int)$record->out_count : 0,
                    ];
                }

                return response()->json($completeData);
            }else{
                $completeData = [];
                for ($i = 6; $i >= 0; $i--) {
                    $day = now()->subDays($i)->toDateString();

                    $dayData = [
                        'day' => $day,
                        'day_name' => now()->subDays($i)->format('l'),
                        'batches' => []
                    ];

                    foreach ($allBatches as $batch) {
                        $record = $stats->where('day', $day)->where('batch', $batch)->first();

                        $dayData['batches'][] = [
                            'batch'          => $batch,
                            'absent_count'   => $record ? (int)$record->absent_count : 0,
                            'late_count'     => $record ? (int)$record->late_count : 0,
                            'early_count'    => $record ? (int)$record->early_count : 0,
                            'ontime_count'   => $record ? (int)$record->ontime_count : 0,
                            'out_late_count' => $record ? (int)$record->out_late_count : 0,
                            'out_early_count'=> $record ? (int)$record->out_early_count : 0,
                            'out_ontime_count'=> $record ? (int)$record->out_ontime_count : 0,
                        ];
                    }

                    $completeData[] = $dayData;
                }

                return response()->json($completeData);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to fetch daily attendance summary',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getAbsentAnalytics(Request $request)
    {
        try {
            $month = $request->input('month', now()->format('m'));
            $year = $request->input('year', now()->format('Y'));
            $batch = $request->input('batch');

            // Query for absent students from academics table only (excluding valid absences)
            $academicAbsentQuery = DB::table('academics as a')
                ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                ->select(
                    'a.student_id',
                    'u.user_fname as first_name',
                    'u.user_lname as last_name',
                    's.batch',
                    's.group',
                    DB::raw('COUNT(CASE
                        WHEN (a.time_in_remark = "Absent" AND (a.time_in_absent_validation IS NULL OR a.time_in_absent_validation = "not_valid"))
                        OR (a.time_in_remark = "Absent" AND (a.time_out_absent_validation IS NULL OR a.time_out_absent_validation = "not_valid"))
                        THEN 1 END) as academic_absent_count')
                )
                ->whereMonth('a.academic_date', $month)
                ->whereYear('a.academic_date', $year)
                ->where(function($query) {
                    $query->where(function($subQuery) {
                        // Time in absent that is not valid
                        $subQuery->where('a.time_out_remark', 'Absent')
                                ->where(function($validationQuery) {
                                    $validationQuery->whereNull('a.time_in_absent_validation')
                                                  ->orWhere('a.time_in_absent_validation', 'not_valid');
                                });
                    })->orWhere(function($subQuery) {
                        // Time out absent that is not valid
                        $subQuery->where('a.time_in_remark', 'Absent')
                                ->where(function($validationQuery) {
                                    $validationQuery->whereNull('a.time_out_absent_validation')
                                                  ->orWhere('a.time_out_absent_validation', 'not_valid');
                                });
                    });
                })
                ->groupBy('a.student_id', 'u.user_fname', 'u.user_lname', 's.batch', 's.group')
                ->having('academic_absent_count', '>', 0);

            // Apply batch filter if provided
            if ($batch) {
                $academicAbsentQuery->where('s.batch', $batch);
            }

            $absentStudents = $academicAbsentQuery->get();

            // Convert to collection and sort by academic absent count
            $allAbsentStudents = $absentStudents->map(function ($student) {
                return [
                    'student_id' => $student->student_id,
                    'first_name' => $student->first_name,
                    'last_name' => $student->last_name,
                    'batch' => $student->batch,
                    'group' => $student->group,
                    'academic_absent_count' => $student->academic_absent_count
                ];
            })->sortByDesc('academic_absent_count')->values();

            $batches = DB::table('student_details')
                ->select('batch')
                ->distinct()
                ->orderBy('batch')
                ->pluck('batch');

            return response()->json([
                'absentStudents' => $allAbsentStudents,
                'batches' => $batches,
                'filters' => [
                    'month' => $month,
                    'year' => $year,
                    'batch' => $batch
                ]
            ]);

        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to fetch absent analytics data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function getStudentAbsentHistory(Request $request)
    {
        try {
            $studentId = $request->input('student_id');
            $month = $request->input('month', now()->format('m'));
            $year = $request->input('year', now()->format('Y'));
            $semester = $request->input('semester');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required'
                ], 400);
            }

            // Academic absent history (both educator and timeout considerations) with validation
            $academicAbsentHistory = DB::table('academics as a')
                ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                ->select(
                    'a.academic_date',
                    DB::raw('CASE
                        WHEN a.time_in_remark = "Absent" THEN a.time_in_remark
                        WHEN a.time_out_remark = "Absent" THEN a.time_out_remark
                        ELSE "Absent"
                    END as consideration'),
                    DB::raw('"academic" as absent_type'),
                    DB::raw('CASE
                        WHEN a.time_in_remark = "Absent" THEN "Time In"
                        WHEN a.time_out_remark = "Absent" THEN "Time Out"
                        ELSE "Academic"
                    END as logging_process'),
                    DB::raw('CASE
                        WHEN a.time_in_remark = "Absent" THEN a.educator_consideration
                        WHEN a.time_out_remark = "Absent" THEN a.time_out_consideration
                        ELSE NULL
                    END as validation')
                )
                ->where('s.student_id', $studentId)
                ->whereMonth('a.academic_date', $month)
                ->whereYear('a.academic_date', $year)
                ->where(function($query) {
                    $query->where('a.time_in_remark', 'Absent')
                          ->orWhere('a.time_out_remark', 'Absent');
                });

            // Add semester filter if provided
            if ($semester) {
                $academicAbsentHistory->where('a.semester_id', $semester);
            }

            $academicAbsentHistory = $academicAbsentHistory->orderBy('a.academic_date', 'desc')->get();

            $absentHistory = $academicAbsentHistory;

            return response()->json([
                'success' => true,
                'data' => [
                    'absentHistory' => $absentHistory
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student absent history: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTimeInOutByBatch(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');
            $type = $request->input('type', 'academic');

            if ($type === 'going_out') {
                // Get Going Out time in/out data
                $timeData = DB::table('going_outs as g')
                    ->join('student_details as s', 'g.student_id', '=', 's.student_id')
                    ->join('pnph_users as u', 's.user_id', '=', 'u.user_id')
                    ->select(
                        's.batch',
                        'u.gender',
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_out IS NOT NULL THEN g.student_id END) as time_out_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_in IS NOT NULL THEN g.student_id END) as time_in_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_in_remark = "Early" THEN g.student_id END) as time_in_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_out_remark = "Early" THEN g.student_id END) as time_out_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_in_remark = "On time" THEN g.student_id END) as time_in_ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_out_remark = "On time" THEN g.student_id END) as time_out_ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_in_remark = "Late" THEN g.student_id END) as time_in_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN g.time_out_remark = "Late" THEN g.student_id END) as time_out_late_count')
                    )
                    ->whereDate('g.going_out_date', $today)
                    ->groupBy('s.batch', 'u.gender')
                    ->orderBy('s.batch')
                    ->get();

                // Process the data to group by gender
                $processedData = [
                    'Male' => [
                        'time_out_count' => 0,
                        'time_in_count' => 0,
                        'time_out_late_count' => 0,
                        'time_in_late_count' => 0,
                        'time_in_early_count' => 0,
                        'time_out_early_count' => 0,
                        'time_in_ontime_count' => 0,
                        'time_out_ontime_count' => 0,
                        'batches' => []
                    ],
                    'Female' => [
                        'time_out_count' => 0,
                        'time_in_count' => 0,
                        'time_out_late_count' => 0,
                        'time_in_late_count' => 0,
                        'time_in_early_count' => 0,
                        'time_out_early_count' => 0,
                        'time_in_ontime_count' => 0,
                        'time_out_ontime_count' => 0,
                        'batches' => []
                    ]
                ];

                foreach ($timeData as $record) {
                    $gender = $record->gender === 'M' ? 'Male' : 'Female';

                    // Add to total counts
                    $processedData[$gender]['time_out_count'] += $record->time_out_count;
                    $processedData[$gender]['time_in_count'] += $record->time_in_count;
                    $processedData[$gender]['time_in_early_count'] += $record->time_in_early_count;
                    $processedData[$gender]['time_out_early_count'] += $record->time_out_early_count;
                    $processedData[$gender]['time_in_ontime_count'] += $record->time_in_ontime_count;
                    $processedData[$gender]['time_out_ontime_count'] += $record->time_out_ontime_count;
                    $processedData[$gender]['time_in_late_count'] += $record->time_in_late_count;
                    $processedData[$gender]['time_out_late_count'] += $record->time_out_late_count;

                    // Add batch details
                    $processedData[$gender]['batches'][] = [
                        'batch' => $record->batch,
                        'time_out_count' => $record->time_out_count,
                        'time_in_count' => $record->time_in_count,
                        'time_in_early_count' => $record->time_in_early_count,
                        'time_out_early_count' => $record->time_out_early_count,
                        'time_in_ontime_count' => $record->time_in_ontime_count,
                        'time_out_ontime_count' => $record->time_out_ontime_count,
                        'time_in_late_count' => $record->time_in_late_count,
                        'time_out_late_count' => $record->time_out_late_count,
                    ];
                }
// die($timeData);
                return response()->json($processedData);
            } elseif($type === 'academic') {
                // Check if there are any academic records for today
                $totalRecords = DB::table('academics')->whereDate('academic_date', $today)->count();

                // Get Academic time in/out data
                $timeData = DB::table('academics as a')
                    ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                    ->select(
                        's.batch',
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out IS NOT NULL THEN a.student_id END) as time_out_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in IS NOT NULL THEN a.student_id END) as time_in_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "On time" THEN a.student_id END) as time_in_ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "On time" THEN a.student_id END) as time_out_ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "Late" THEN a.student_id END) as time_in_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "Late" THEN a.student_id END) as time_out_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "Early" THEN a.student_id END) as time_in_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "Early" THEN a.student_id END) as time_out_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "Absent" THEN a.student_id END) as absent_count')
                    )
                    ->whereDate('a.academic_date', $today)
                    ->groupBy('s.batch')
                    ->orderBy('s.batch')
                    ->get();
// die($timeData);
                return response()->json($timeData);
            }elseif ($type === 'intern'){
                $timeData = DB::table('intern_log as a')
                    ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                    ->select(
                        's.batch',
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out IS NOT NULL THEN a.student_id END) as time_out_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in IS NOT NULL THEN a.student_id END) as time_in_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "On time" THEN a.student_id END) as time_in_ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "On time" THEN a.student_id END) as time_out_ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "Late" THEN a.student_id END) as time_in_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "Late" THEN a.student_id END) as time_out_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "Early" THEN a.student_id END) as time_in_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remark = "Early" THEN a.student_id END) as time_out_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remark = "Absent" THEN a.student_id END) as absent_count')
                    )
                    ->whereDate('a.date', $today)
                    ->groupBy('s.batch')
                    ->orderBy('s.batch')
                    ->get();
                // die($timeData);
                return response()->json($timeData);
            }elseif ($type === 'going_home'){
                $timeData = DB::table('going_home as a')
                    ->join('student_details as s', 'a.student_id', '=', 's.student_id')
                    ->select(
                        's.batch',
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out IS NOT NULL THEN a.student_id END) as time_out_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in IS NOT NULL THEN a.student_id END) as time_in_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remarks = "On time" THEN a.student_id END) as time_in_ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remarks = "On time" THEN a.student_id END) as time_out_ontime_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remarks = "Late" THEN a.student_id END) as time_in_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remarks = "Late" THEN a.student_id END) as time_out_late_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_in_remarks = "Early" THEN a.student_id END) as time_in_early_count'),
                        DB::raw('COUNT(DISTINCT CASE WHEN a.time_out_remarks = "Early" THEN a.student_id END) as time_out_early_count')
                    )
                    ->whereDate('a.date_time_out', $today)
                    ->orWhereDate('a.date_time_in', $today)
                    ->groupBy('s.batch')
                    ->orderBy('s.batch')
                    ->get();
                // die($timeData);
                return response()->json($timeData);
            }elseif ($type === 'visitor'){
                $timeData = DB::table('visitors as a')
                ->select(
                    DB::raw('COUNT(DISTINCT CASE WHEN a.time_out IS NOT NULL THEN a.id END) as time_out_count'),
                    DB::raw('COUNT(DISTINCT CASE WHEN a.time_in IS NOT NULL THEN a.id END) as time_in_count')
                )
                ->whereDate('a.visit_date', $today)
                ->first();

            return response()->json($timeData);
            }
        } catch (\Exception $e) {

            return response()->json([
                'error' => 'Failed to fetch time in/out data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent student time in/out activities for real-time notifications
     * Uses the same logic as navigation badges to ensure accuracy
     */
    public function getRecentActivities(Request $request)
    {
        try {
            $today = now()->format('Y-m-d');
            $activities = [];

            $academicLastViewed = $goingOutLastViewed = $visitorLastViewed = null;
            if (Schema::hasTable('notification_views') && class_exists(\App\Models\NotificationView::class)) {
                try {
                    $academicLastViewed = \App\Models\NotificationView::getLastViewed('academic');
                    $goingOutLastViewed = \App\Models\NotificationView::getLastViewed('goingout');
                    $visitorLastViewed = \App\Models\NotificationView::getLastViewed('visitor');
                } catch (\Throwable $ex) {
                    $academicLastViewed = $goingOutLastViewed = $visitorLastViewed = null;
                }
            }

            // Get academic activities (same logic as NotificationController)
            $academicTimeOutQuery = \App\Models\Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_out')
                ->with(['studentDetail.user']);

            if ($academicLastViewed) {
                $academicTimeOutQuery->where(function($q) use ($academicLastViewed) {
                    $q->where('updated_at', '>', $academicLastViewed)
                      ->orWhere('created_at', '>', $academicLastViewed);
                });
            }

            $academicTimeInQuery = \App\Models\Academic::whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->with(['studentDetail.user']);

            if ($academicLastViewed) {
                $academicTimeInQuery->where(function($q) use ($academicLastViewed) {
                    $q->where('updated_at', '>', $academicLastViewed)
                      ->orWhere('created_at', '>', $academicLastViewed);
                });
            }

            // Get the activities
            $academicTimeOuts = $academicTimeOutQuery->orderBy('updated_at', 'desc')->get();
            $academicTimeIns = $academicTimeInQuery->orderBy('updated_at', 'desc')->get();

            // Process academic activities - determine most recent action per student
            $academicStudentActions = [];

            // Collect time out activities
            foreach ($academicTimeOuts as $activity) {
                $studentKey = $activity->student_id;
                $timeOutTimestamp = \Carbon\Carbon::parse($activity->time_out);

                if (!isset($academicStudentActions[$studentKey]) ||
                    $timeOutTimestamp->greaterThan(\Carbon\Carbon::parse($academicStudentActions[$studentKey]['timestamp']))) {
                    $academicStudentActions[$studentKey] = [
                        'id' => $activity->id,
                        'student_id' => $activity->student_id,
                        'student_name' => $activity->studentDetail->user->user_fname . ' ' . $activity->studentDetail->user->user_lname,
                        'batch' => $activity->studentDetail->batch,
                        'type' => 'academic',
                        'action' => 'time_out',
                        'time' => $timeOutTimestamp->format('g:i A'),
                        'timestamp' => $activity->updated_at,
                        'action_timestamp' => $timeOutTimestamp
                    ];
                }
            }

            // Collect time in activities and compare with time out
            foreach ($academicTimeIns as $activity) {
                $studentKey = $activity->student_id;
                $timeInTimestamp = \Carbon\Carbon::parse($activity->time_in);

                if (!isset($academicStudentActions[$studentKey]) ||
                    $timeInTimestamp->greaterThan($academicStudentActions[$studentKey]['action_timestamp'])) {
                    $academicStudentActions[$studentKey] = [
                        'id' => $activity->id,
                        'student_id' => $activity->student_id,
                        'student_name' => $activity->studentDetail->user->user_fname . ' ' . $activity->studentDetail->user->user_lname,
                        'batch' => $activity->studentDetail->batch,
                        'type' => 'academic',
                        'action' => 'time_in',
                        'time' => $timeInTimestamp->format('g:i A'),
                        'timestamp' => $activity->updated_at,
                        'action_timestamp' => $timeInTimestamp,
                        'is_late' => $activity->time_in_remark === 'Late'
                    ];
                }
            }

            // Add academic activities to main array
            foreach ($academicStudentActions as $studentAction) {
                unset($studentAction['action_timestamp']); // Remove helper field
                $activities[] = $studentAction;
            }

            // Get going out activities (same logic as NotificationController)
            $goingOutTimeOutQuery = \App\Models\Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_out')
                ->with(['studentDetail.user']);

            if ($goingOutLastViewed) {
                $goingOutTimeOutQuery->where(function($q) use ($goingOutLastViewed) {
                    $q->where('updated_at', '>', $goingOutLastViewed)
                      ->orWhere('created_at', '>', $goingOutLastViewed);
                });
            }

            $goingOutTimeInQuery = \App\Models\Going_out::whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->with(['studentDetail.user']);

            if ($goingOutLastViewed) {
                $goingOutTimeInQuery->where(function($q) use ($goingOutLastViewed) {
                    $q->where('updated_at', '>', $goingOutLastViewed)
                      ->orWhere('created_at', '>', $goingOutLastViewed);
                });
            }

            // Get the activities
            $goingOutTimeOuts = $goingOutTimeOutQuery->orderBy('updated_at', 'desc')->get();
            $goingOutTimeIns = $goingOutTimeInQuery->orderBy('updated_at', 'desc')->get();

            // Process going out activities - determine most recent action per student
            $goingOutStudentActions = [];

            // Collect time out activities
            foreach ($goingOutTimeOuts as $activity) {
                $studentKey = $activity->student_id;
                $timeOutTimestamp = \Carbon\Carbon::parse($activity->time_out);

                if (!isset($goingOutStudentActions[$studentKey]) ||
                    $timeOutTimestamp->greaterThan(\Carbon\Carbon::parse($goingOutStudentActions[$studentKey]['timestamp']))) {
                    $goingOutStudentActions[$studentKey] = [
                        'id' => $activity->id,
                        'student_id' => $activity->student_id,
                        'student_name' => $activity->studentDetail->user->user_fname . ' ' . $activity->studentDetail->user->user_lname,
                        'batch' => $activity->studentDetail->batch,
                        'type' => 'going_out',
                        'action' => 'time_out',
                        'time' => $timeOutTimestamp->format('g:i A'),
                        'timestamp' => $activity->updated_at,
                        'action_timestamp' => $timeOutTimestamp
                    ];
                }
            }

            // Collect time in activities and compare with time out
            foreach ($goingOutTimeIns as $activity) {
                $studentKey = $activity->student_id;
                $timeInTimestamp = \Carbon\Carbon::parse($activity->time_in);

                if (!isset($goingOutStudentActions[$studentKey]) ||
                    $timeInTimestamp->greaterThan($goingOutStudentActions[$studentKey]['action_timestamp'])) {
                    $goingOutStudentActions[$studentKey] = [
                        'id' => $activity->id,
                        'student_id' => $activity->student_id,
                        'student_name' => $activity->studentDetail->user->user_fname . ' ' . $activity->studentDetail->user->user_lname,
                        'batch' => $activity->studentDetail->batch,
                        'type' => 'going_out',
                        'action' => 'time_in',
                        'time' => $timeInTimestamp->format('g:i A'),
                        'timestamp' => $activity->updated_at,
                        'action_timestamp' => $timeInTimestamp,
                        'is_late' => $activity->time_in_remark === 'Late'
                    ];
                }
            }

            // Add going out activities to main array
            foreach ($goingOutStudentActions as $studentAction) {
                unset($studentAction['action_timestamp']); // Remove helper field
                $activities[] = $studentAction;
            }

            // Get visitor activities (same logic as NotificationController)
            $visitorTimeOutQuery = \App\Models\Visitor::whereDate('visit_date', $today)
                ->whereNotNull('time_out');

            if ($visitorLastViewed) {
                $visitorTimeOutQuery->where(function($q) use ($visitorLastViewed) {
                    $q->where('updated_at', '>', $visitorLastViewed)
                      ->orWhere('created_at', '>', $visitorLastViewed);
                });
            }

            $visitorTimeInQuery = \App\Models\Visitor::whereDate('visit_date', $today)
                ->whereNotNull('time_in');

            if ($visitorLastViewed) {
                $visitorTimeInQuery->where(function($q) use ($visitorLastViewed) {
                    $q->where('updated_at', '>', $visitorLastViewed)
                      ->orWhere('created_at', '>', $visitorLastViewed);
                });
            }

            // Get the activities
            $visitorTimeOuts = $visitorTimeOutQuery->orderBy('updated_at', 'desc')->get();
            $visitorTimeIns = $visitorTimeInQuery->orderBy('updated_at', 'desc')->get();

            // Process visitor activities - determine most recent action per visitor
            $visitorActions = [];

            // Collect time out activities
            foreach ($visitorTimeOuts as $activity) {
                $visitorKey = $activity->id;
                $timeOutTimestamp = \Carbon\Carbon::parse($activity->time_out);

                if (!isset($visitorActions[$visitorKey]) ||
                    $timeOutTimestamp->gt(\Carbon\Carbon::parse($visitorActions[$visitorKey]['action_timestamp']))) {

                    $visitorActions[$visitorKey] = [
                        'type' => 'visitor',
                        'visitor_id' => $activity->id,
                        'visitor_name' => $activity->visitor_name,
                        'action' => 'time_out',
                        'time' => $timeOutTimestamp->format('g:i A'),
                        'timestamp' => $timeOutTimestamp->toISOString(),
                        'action_timestamp' => $timeOutTimestamp->toISOString(),
                        'is_late' => false // Visitors don't have late status
                    ];
                }
            }

            // Collect time in activities
            foreach ($visitorTimeIns as $activity) {
                $visitorKey = $activity->id;
                $timeInTimestamp = \Carbon\Carbon::parse($activity->time_in);

                if (!isset($visitorActions[$visitorKey]) ||
                    $timeInTimestamp->gt(\Carbon\Carbon::parse($visitorActions[$visitorKey]['action_timestamp']))) {

                    $visitorActions[$visitorKey] = [
                        'type' => 'visitor',
                        'visitor_id' => $activity->id,
                        'visitor_name' => $activity->visitor_name,
                        'action' => 'time_in',
                        'time' => $timeInTimestamp->format('g:i A'),
                        'timestamp' => $timeInTimestamp->toISOString(),
                        'action_timestamp' => $timeInTimestamp->toISOString(),
                        'is_late' => false // Visitors don't have late status
                    ];
                }
            }

            // Add visitor activities to main array
            foreach ($visitorActions as $visitorAction) {
                unset($visitorAction['action_timestamp']); // Remove helper field
                $activities[] = $visitorAction;
            }

            // Sort activities by timestamp (newest first)
            usort($activities, function($a, $b) {
                return strtotime($b['timestamp']) - strtotime($a['timestamp']);
            });

            // Limit to last 10 activities to prevent overwhelming the UI
            $activities = array_slice($activities, 0, 10);

            return response()->json([
                'success' => true,
                'activities' => $activities,
                'academic_last_viewed' => $academicLastViewed,
                'goingout_last_viewed' => $goingOutLastViewed,
                'visitor_last_viewed' => $visitorLastViewed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Failed to fetch recent activities',
                'message' => $e->getMessage(),
                'activities' => []
            ], 500);
        }
    }

    public function goinghomeMonitor(Request $request)
    {
        $query = GoingHomeModel::get_all_logs($request->batch, $request->group, $request->type, $request->date_time_out, $request->date_time_in, $request->status, $request->fullname);

        $todayLogs = $query->paginate(20);

        $batches = StudentDetail::distinct()->pluck('batch')->sort();
        $groups  = StudentDetail::distinct()->pluck('group')->sort();

        return view('user-educator.goinghomemonitor', [
            'todayLogs'    => $todayLogs,
            'batches'      => $batches,
            'groups'       => $groups,
            'date_time_out' => $request->date_time_out,
            'date_time_in' => $request->date_time_in
        ]);
    }

    public function internMonitor(Request $request)
    {
        $selectedDate = $request->date ?? now()->format('Y-m-d');
        $query = InternLogModel::get_all_logs($selectedDate, $request->company, $request->status, $request->fullname);
        $todayLogs = $query->paginate(20);

        return view('user-educator.internmonitor', [
            'todayLogs' => $todayLogs,
            'companies' => InternshipSchedule::distinct()->pluck('company'),
            'sessions' => collect(),
            'selectedDate' => $selectedDate,
        ]);
    }

    public function academicMonitor(Request $request)
    {
        NotificationView::markAsViewed('academic');

        $query = Academic::get_all($request->batch, $request->group, $request->date, $request->status, $request->fullname);

        $academicLogs = $query->paginate();

        return view('user-educator.academicmonitor', [
            'academicLogs' => $academicLogs,
            'batches' => StudentDetail::distinct()->pluck('batch'),
            'groups' => StudentDetail::distinct()->pluck('group'),
            'selectedDate' => $request->date,
        ]);
    }

    public function goingoutMonitor(Request $request)
    {
        NotificationView::markAsViewed('goingout');

        $query = Going_out::get_all($request->gender, $request->date, $request->status, $request->session, $request->fullname);

        $todayLogs = $query->paginate(20);

        return view('user-educator.goingoutmonitor', [
            'todayLogs' => $todayLogs,
            'batches' => StudentDetail::distinct()->pluck('batch'),
            'groups' => StudentDetail::distinct()->pluck('group'),
            'sessions' => Going_out::distinct()->pluck('session_number'),
            'selectedDate' => $request->date,
        ]);
    }

    public function visitorMonitor(Request $request)
    {
        NotificationView::markAsViewed('visitor');

        $query = Visitor::get_all_logs($request->type, $request->date, $request->fullname);

        $visitors = $query->paginate(20);

        return view('user-educator.visitormonitor', [
            'visitors' => $visitors,
            'selectedDate' => $request->date,
        ]);
    }
}
