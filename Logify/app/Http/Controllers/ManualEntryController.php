<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\StudentDetail;
use App\Models\Academic;
use App\Models\EventSchedule;
use App\Models\Going_out;
use App\Models\GoingHomeModel;
use App\Models\InternLogModel;
use App\Models\InternshipSchedule;
use App\Models\ManualEntryLog;
use App\Models\Schedule;
use App\Models\Visitor;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;

class ManualEntryController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'academic');

        $students = StudentDetail::with('user')
            ->orderBy('student_id')
            ->get();

        $batches = StudentDetail::distinct()->pluck('batch')->filter()->sort()->values();
        $groups = StudentDetail::distinct()->pluck('group')->filter()->sort()->values();

        return view('user-monitor.manual-entry', compact('students', 'batches', 'groups', 'type', 'selectedDate'));
    }

    public function getStudentLogs(Request $request)
    {
        $request->validate([
            'type' => 'required|in:academic,going_out',
            'date' => 'required|date',
            'student_id' => 'nullable|string',
            'batch' => 'nullable|string',
            'group' => 'nullable|string'
        ]);

        $type = $request->type;
        $date = $request->date;

        try {
            $query = StudentDetail::query();

            if ($request->student_id) {
                $query->where('student_id', $request->student_id);
            }

            if ($request->batch) {
                $query->where('batch', $request->batch);
            }

            if ($request->group) {
                $query->where('group', $request->group);
            }

            $logs = StudentDetail::with('user')
                ->orderBy('student_id')
                ->get();

            return response()->json([
                'success' => true,
                'logs' => $logs,
                'type' => $type,
                'date' => $date
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch student logs'
            ], 500);
        }
    }

    public function findExistingLog(Request $request)
    {
        $request->validate([
            'student_id' => 'required|string|exists:student_details,student_id',
            'log_type' => 'required|in:academic,going_out',
            'date' => 'required|date',
            'session_number' => 'nullable|integer|min:1',
        ]);

        try {
            $studentId = $request->student_id;
            $date = $request->date;
            $type = $request->log_type;

            if ($type === 'academic') {
                $record = Academic::where('student_id', $studentId)
                    ->whereDate('academic_date', $date)
                    ->first();

                return response()->json([
                    'success' => true,
                    'exists' => (bool) $record,
                    'data' => $record ? [
                        'time_out' => $record->time_out,
                        'time_in' => $record->time_in,
                        'is_manual_entry' => (bool) $record->is_manual_entry,
                        'approval_status' => $record->approval_status,
                        'destination' => null,
                        'purpose' => null,
                    ] : null,
                    'sessions' => [],
                ]);
            } elseif($type === 'going_out') {
                $sessions = Going_out::where('student_id', $studentId)
                    ->whereDate('going_out_date', $date)
                    ->orderBy('session_number', 'desc')
                    ->get(['id','session_number','time_out','time_in','destination','purpose']);

                if ($request->filled('session_number')) {
                    $record = $sessions->firstWhere('session_number', (int) $request->session_number);
                } else {
                    $record = $sessions->first();
                }

                return response()->json([
                    'success' => true,
                    'exists' => (bool) $record,
                    'data' => $record ? [
                        'time_out' => $record->time_out,
                        'time_in' => $record->time_in,
                        'is_manual_entry' => (bool) ($record->is_manual_entry ?? false),
                        'approval_status' => $record->approval_status ?? null,
                        'destination' => $record->destination,
                        'purpose' => $record->purpose,
                        'session_number' => $record->session_number,
                    ] : null,
                    'sessions' => $sessions->map(function($s){
                        return [
                            'session_number' => $s->session_number,
                            'time_out' => $s->time_out,
                            'time_in' => $s->time_in,
                        ];
                    })->values(),
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to lookup existing record.',
            ], 500);
        }
    }

    public function submitVisitorManualEntry(Request $request)
    {
        $request->validate([
            'entry_type'   => 'required|in:time_in,time_out,both',
            'date'         => 'required|date',
            'visitor_name' => 'required|string|max:255|regex:/^[A-Za-z\s]+$/',
            'valid_id'     => 'required_if:entry_type,time_in,entry_type,both|nullable|string|max:255',
            'other_id_type'=> 'nullable|string|max:255',
            'visitor_pass' => 'required_if:entry_type,time_in,entry_type,both|nullable|string|max:50',
            'id_number'    => 'required_if:entry_type,time_in,entry_type,both|nullable|string|max:255',
            'relationship' => 'required_if:entry_type,time_in,entry_type,both|nullable|string|max:100',
            'purpose'      => 'required_if:entry_type,time_in,entry_type,both|nullable|string|max:255',
            'time_in'      => 'nullable|date_format:H:i',
            'time_out'     => 'nullable|date_format:H:i',
            'reason'       => 'required|string|max:500',
        ]);

        try {
            DB::beginTransaction();
            $monitorName = session('user_fname') . ' ' . session('user_lname');
            if($request->entry_type === 'time_out'){
                $visitor = Visitor::where('visitor_pass', $request->visitor_pass)
                            ->where('visit_date', $request->date)
                            ->where('visitor_name', $request->visitor_name)
                            ->whereNull('time_out')
                            ->first();
                if(!$visitor) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to submit visitor manual entry: Visitor not found'
                    ], 500);
                }
            }

            $validId = $request->valid_id === 'Other' ? $request->other_id_type : $request->valid_id;

            $manualData = [];
            if($request->entry_type === 'time_in'){
                $manualData = [
                    'guard_id'  => session('user.user_id'),
                    'visit_date' => date('Y-m-d', strtotime($request->date)),
                    'visitor_name' => $request->visitor_name,
                    'visitor_pass' => $request->visitor_pass,
                    'valid_id'               => $validId,
                    'id_number'              => $request->id_number,
                    'relationship'           => $request->relationship,
                    'purpose'                => $request->purpose,
                    'time_in'                => date('H:i:s', strtotime($request->time_in)),
                    'is_manual_entry'        => true,
                    'manual_entry_type'      => $request->entry_type,
                    'manual_entry_reason'    => $request->reason,
                    'manual_entry_timestamp' => Carbon::parse(now())->format('Y-m-d H:i:s'),
                    'approval_status'        => 'approved',
                    'created_by'             => session('user.user_fname') . session('user.user_lname'),
                    'created_at'             => Carbon::parse(now())->format('Y-m-d H:i:s'),
                    'updated_by'   => session('user.user_fname') . session('user.user_lname'),
                    'updated_at'   => Carbon::parse(now())->format('Y-m-d H:i:s')
                ];
            }elseif($request->entry_type === 'time_out'){
                $manualData = [
                    'visit_date' => $request->date,
                    'visitor_name' => $request->visitor_name,
                    'visitor_pass' => $request->visitor_pass,
                    'time_out'     => date('H:i:s', strtotime($request->time_out)),
                    'approval_status'=> 'approved',
                    'updated_by'   => session('user.user_fname') . session('user.user_lname'),
                    'updated_at'   => Carbon::parse(now())->format('Y-m-d H:i:s')
                ];
            }else{
                $manualData = [
                    'guard_id'  => session('user.user_id'),
                    'visit_date' => date('Y-m-d', strtotime($request->date)),
                    'visitor_name' => $request->visitor_name,
                    'visitor_pass' => $request->visitor_pass,
                    'valid_id'               => $validId,
                    'id_number'              => $request->id_number,
                    'relationship'           => $request->relationship,
                    'purpose'                => $request->purpose,
                    'time_in'                => date('H:i:s', strtotime($request->time_in)),
                    'time_out'               => date('H:i:s', strtotime($request->time_out)),
                    'is_manual_entry'        => true,
                    'manual_entry_type'      => $request->entry_type,
                    'manual_entry_reason'    => $request->reason,
                    'manual_entry_timestamp' => Carbon::parse(now())->format('Y-m-d H:i:s'),
                    'approval_status'        => 'approved',
                    'created_by'             => session('user.user_fname') . session('user.user_lname'),
                    'created_at'             => Carbon::parse(now())->format('Y-m-d H:i:s'),
                    'updated_by'   => session('user.user_fname') . session('user.user_lname'),
                    'updated_at'   => Carbon::parse(now())->format('Y-m-d H:i:s')
                ];
            }

            ManualEntryLog::create([
                    'student_id'   => $request->student_id,
                    'log_type'     => 'visitor',
                    'entry_type'   => $request->entry_type,
                    'reason'       => $request->reason,
                    'monitor_name' => $monitorName,
                    'manual_data'  => $manualData,
                    'status'       => 'pending',
                    'monitor_name' => session('user.user_fname') . session('user.user_lname'),
                    'created_at'   => now()
                ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Visitor manual entry submitted successfully! Pending educator approval.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit visitor manual entry: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submitInternManualEntry(Request $request)
    {
        try {
            $request->validate([
                'log_type' => 'required',
                'entry_type' => 'required',
                'student_id' => 'required|exists:student_details,student_id',
                'date'       => 'required|date',
                'time_out'    => 'nullable|date_format:H:i',
                'time_in'   => 'nullable|date_format:H:i|after:time_out',
                'reason'     => 'nullable|string|max:255',
            ]);

            $schedule = InternshipSchedule::getInternSchedule($request->student_id, $request->date);

            if (!$schedule) {
                return redirect()->back()->with('error', 'No schedule found for this student on the selected date.');
            }

            $logs = InternLogModel::getStudentLogRecord($request->student_id, $request->date);
            if($logs && $logs->time_out && $logs->time_in){
                return redirect()->back()->with('error', 'This student already completed the logs.');
            }

            if($logs && $logs->time_out){
                if($request->entry_type === 'time_out'){
                    return redirect()->back()->with('error', 'This student already time out.');
                }
            }

            if($logs && $logs->time_in){
                if($request->entry_type === 'time_in'){
                    return redirect()->back()->with('error', 'This student already time in.');
                }
            }

            $today = Carbon::parse($request->date)->format('l');
            $days = is_array($schedule->days) ? $schedule->days : json_decode($schedule->days, true);

            if (!in_array($today, $days)) {
                return back()->with('error', 'No duty today');
            }

            $scheduledTimeIn  = Carbon::parse($schedule->time_in);
            $scheduledTimeOut = Carbon::parse($schedule->time_out);

            $timeIn  = !empty($request->time_in)  ? Carbon::parse($request->time_in)  : null;
            $timeOut = !empty($request->time_out) ? Carbon::parse($request->time_out) : null;

            $logout_remark = $timeOut
                ? ($timeOut->lt($scheduledTimeOut) ? 'Early'
                : ($timeOut->gt($scheduledTimeOut) ? 'Late' : 'On Time'))
                : null;

            $login_remark = $timeIn
                ? ($timeIn->gt($scheduledTimeIn) ? 'Late'
                : ($timeIn->lt($scheduledTimeIn) ? 'Early' : 'On Time'))
                : null;

            if($request->entry_type === 'time_out'){
                $data = [
                    'manual_entry_type' => $request->entry_type,
                    'student_id'        => $request->student_id,
                    'date'              => $request->date,
                    'time_out'          => $request->time_out,
                    'time_out_remark'   => $logout_remark,
                    'reason'            => $request->reason,
                    'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'created_at'        => now(),
                    'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'updated_at'        => now(),
                    'approval_status'   => 'approved',
                ];
            }elseif($request->entry_type === 'time_in'){
                $data = [
                    'manual_entry_type' => $request->entry_type,
                    'student_id'        => $request->student_id,
                    'date'              => $request->date,
                    'time_in'           => $request->time_in,
                    'time_in_remark'    => $login_remark,
                    'reason'            => $request->reason,
                    'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'updated_at'        => now(),
                    'approval_status'  => 'approved',
                ];
            }else{
                $data = [
                    'manual_entry_type' => $request->entry_type,
                    'student_id'        => $request->student_id,
                    'date'              => $request->date,
                    'time_out'          => $request->time_out,
                    'time_out_remark'   => $logout_remark,
                    'reason'            => $request->reason,
                    'time_in'           => $request->time_in,
                    'time_in_remark'    => $login_remark,
                    'reason'            => $request->reason,
                    'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'created_at'        => now(),
                    'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'updated_at'        => now(),
                    'approval_status'   => 'approved',
                ];
            }
            DB::beginTransaction();

            InternLogModel::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'date'       => $request->date,
                ],
                [
                    'is_manual_entry' => 1,
                    'approval_status'  => 'pending',
                    'updated_at'        => now()->format('Y-m-d H:i:s'),
                ]
            );
            DB::commit();

            ManualEntryLog::create([
                'student_id'   => $request->student_id,
                'log_type' => $request->log_type,
                'entry_type'     => $request->entry_type,
                'log_id'       => null,
                'reason'       => $request->reason,
                'monitor_name' => session('user.user_fname') . ' ' . session('user.user_lname'),
                'manual_data'  => $data,
                'status'       => 'pending'
            ]);
            DB::commit();

            return redirect()->back()->with('success', 'Manual entry submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit manual entry: ' . $e->getMessage());
        }
    }

    public function submitAcademicManualEntry(Request $request)
    {
        $request->validate([
            'entry_type' => 'required',
            'student_id' => 'required|exists:student_details,student_id',
            'date'       => 'required|date',
            'time_out'   => 'nullable|date_format:H:i',
            'time_in'    => 'nullable|date_format:H:i|after_or_equal:time_out',
            'reason'     => 'nullable|string|max:255',
        ]);

        if (in_array($request->entry_type, ['time_out', 'both']) && !$request->filled('time_out')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Time Out is required for the selected entry type.'
            ]);
        }

        if (in_array($request->entry_type, ['time_in', 'both']) && !$request->filled('time_in')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Time In is required for the selected entry type.'
            ]);
        }

        if ($request->entry_type === 'both') {
            $out = Carbon::createFromFormat('Y-m-d H:i', $request->date.' '.$request->time_out);
            $in = Carbon::createFromFormat('Y-m-d H:i', $request->date.' '.$request->time_in);
            if ($out->gt($in)) {
                return redirect()->back()->with([
                    'success' => false,
                    'message' => 'Time Out must be before or equal to Time In when selecting Both.'
                ]);
            }
        }

        $isSunday = Carbon::parse($request['date'])->isSunday();
        if($isSunday){
            return redirect()->back()->with('error', 'No Academic Schedue for Sunday');
        }

        $logs = Academic::getStudentLogRecord($request->student_id, $request->date);
        if($logs && $logs->time_out && $logs->time_in){
            return redirect()->back()->with('error', 'This student already completed the logs.');
        }

        if($logs && $logs->time_out){
            if($request->entry_type === 'time_out'){
                return redirect()->back()->with('error', 'This student already time out.');
            }
        }

        if($logs && $logs->time_in){
            if($request->entry_type === 'time_in'){
                return redirect()->back()->with('error', 'This student already time in.');
            }
        }

        $student = StudentDetail::get_student($request->student_id);
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
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

        $time_out = $request->time_out ? Carbon::parse($request->time_out) : null;
        $time_in  = $request->time_in  ? Carbon::parse($request->time_in)  : null;

        $scheduledTimeOut = $schedule?->time_out ? Carbon::parse($schedule->time_out) : null;
        $scheduledTimeIn  = $schedule?->time_in  ? Carbon::parse($schedule->time_in)  : null;

        // dd($schedule->toArray());
        $logout_remark = $time_out && $scheduledTimeOut
            ? ($time_out->lt($scheduledTimeOut->copy()->subMinutes(60)) ? 'Early'
                : ($time_out->gt($scheduledTimeOut) ? 'Late' : 'On Time'))
            : null;

        $login_remark = $time_in && $scheduledTimeIn
            ? ($time_in->lt($scheduledTimeIn) ? 'Early'
                : ($time_in->gt($scheduledTimeIn->copy()->addMinutes(60)) ? 'Late' : 'On Time'))
            : null;


        if($request->entry_type === 'time_out') {
            $data = [
                'manual_entry_type' => $request->entry_type,
                'student_id'        => $request->student_id,
                'academic_date'     => $request->date,
                'time_out'          => $request->time_out,
                'time_out_remark'   => $logout_remark,
                'reason'            => $request->reason,
                'approval_status'   => 'approved',
                'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'created_at'        => now()->format('Y-m-d H:i:s'),
                'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'updated_at'        => now()->format('Y-m-d H:i:s'),
            ];
        }elseif($request->entry_type === 'time_in') {
            $data = [
                'manual_entry_type' => $request->entry_type,
                'student_id'        => $request->student_id,
                'academic_date'     => $request->date,
                'time_in'           => $request->time_in,
                'time_in_remark'    => $login_remark,
                'reason'            => $request->reason,
                'approval_status'   => 'approved',
                'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'updated_at'        => now()->format('Y-m-d H:i:s'),
            ];
        }else{
            $data = [
                'manual_entry_type' => $request->entry_type,
                'student_id'        => $request->student_id,
                'academic_date'     => $request->date,
                'time_out'          => $request->time_out,
                'time_out_remark'   => $logout_remark,
                'time_in'           => $request->time_in,
                'time_in_remark'    => $login_remark,
                'reason'            => $request->reason,
                'approval_status'   => 'approved',
                'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'created_at'        => now()->format('Y-m-d H:i:s'),
                'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'updated_at'        => now()->format('Y-m-d H:i:s'),
            ];
        }

        DB::beginTransaction();
        try {
            Academic::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'academic_date' => $request->date
                ],
                [
                    'is_manual_entry' => 1,
                    'approval_status' => 'pending',
                    'updated_at'        => now()->format('Y-m-d H:i:s'),
                ]
            );

            DB::commit();

            ManualEntryLog::create([
                'student_id'   => $request->student_id,
                'log_type'     => 'academic',
                'entry_type'   => $request->entry_type,
                'log_id'       => null,
                'reason'       => $request->reason,
                'monitor_name' => session('user.user_fname') . ' ' . session('user.user_lname'),
                'manual_data'  => $data,
                'status'       => 'pending'
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Manual entry submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit manual entry: ' . $e->getMessage());
        }
    }

    public function submitLeisureManualEntry(Request $request)
    {
        try {
            $request->validate([
                'entry_type' => 'required',
                'student_id' => 'required|exists:student_details,student_id',
                'date'       => 'required|date',
                'time_out'   => 'nullable|date_format:H:i',
                'time_in'    => 'nullable|date_format:H:i|after_or_equal:time_out',
                'destination' => 'required|string|max:255',
                'purpose'    => 'required|string|max:255',
                'reason'     => 'nullable|string|max:255',
            ]);

            if (in_array($request->entry_type, ['time_out', 'both']) && !$request->filled('time_out')) {
                return redirect()->back()->with([
                    'success' => false,
                    'message' => 'Time Out is required for the selected entry type.'
                ]);
            }

            if (in_array($request->entry_type, ['time_in', 'both']) && !$request->filled('time_in')) {
                return redirect()->back()->with([
                    'success' => false,
                    'message' => 'Time In is required for the selected entry type.'
                ]);
            }
            if ($request->entry_type === 'both') {
                $out = Carbon::createFromFormat('Y-m-d H:i', $request->date.' '.$request->time_out);
                $in = Carbon::createFromFormat('Y-m-d H:i', $request->date.' '.$request->time_in);
                if ($out->gt($in)) {
                    return redirect()->back()->with([
                        'success' => false,
                        'message' => 'Time Out must be before or equal to Time In when selecting Both.'
                    ]);
                }
            }

            $student = StudentDetail::get_student($request->student_id);
            if (!$student) {
                return redirect()->back()->with('error', 'Student not found.');
            }

            $logs = Going_out::getStudentLogRecord($request->student_id, $request->date);
            if($logs && $logs->time_out && !$logs->time_out){
                if($request->entry_type === 'time_out'){
                    return redirect()->back()->with('error', 'This student already time out.');
                }
            }
            $scheduleDate = Carbon::parse($request->date);
            $schedule = Schedule::get_goingout_schedule_by_id($request->student_id, $scheduleDate);
            if (!$schedule) {
                $schedule = Schedule::get_goingout_schedule($student->user['gender'], $scheduleDate);
            }

            if (!$schedule) {
                return redirect()->back()->with('error', 'No schedule found for this student on the selected date.');
            }

            $scheduledTimeOut = Carbon::parse($schedule->time_out);
            $scheduledTimeIn  = Carbon::parse($schedule->time_in);

            $timeOut = !empty($request->time_out) ? Carbon::parse($request->time_out) : null;
            $timeIn  = !empty($request->time_in)  ? Carbon::parse($request->time_in)  : null;

            $logout_remark = $timeOut
                ? ($timeOut->lt($scheduledTimeOut) ? 'Early'
                : ($timeOut->gt($scheduledTimeOut) ? 'Late' : 'On Time'))
                : null;

            $login_remark = $timeIn
                ? ($timeIn->lt($scheduledTimeIn) ? 'Early'
                : ($timeIn->gt($scheduledTimeIn) ? 'Late' : 'On Time'))
                : null;

            $student_log = Going_out::get_student_by_date($request->student_id, $request->date);

            if($request->entry_type === 'time_out'){
                $data = [
                    'manual_entry_type' => $request->entry_type,
                    'student_id'        => $request->student_id,
                    'session_number'    => optional($student_log)->session_number ? $student_log->session_number + 1 : 1,
                    'going_out_date'    => $request->date,
                    'time_out'          => $request->time_out,
                    'time_out_remark'   => $logout_remark,
                    'destination'       => $request->destination,
                    'purpose'           => $request->purpose,
                    'reason'            => $request->reason,
                    'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'created_at'        => now()->format('Y-m-d H:i:s'),
                    'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'updated_at'        => now()->format('Y-m-d H:i:s'),
                    'approval_status'   => 'approved',
                ];
            }elseif($request->entry_type === 'time_in'){
                $data = [
                    'manual_entry_type' => $request->entry_type,
                    'student_id'        => $request->student_id,
                    'going_out_date'     => $request->date,
                    'time_in'           => $request->time_in,
                    'time_in_remark'   => $login_remark,
                    'destination'       => $request->destination,
                    'purpose'           => $request->purpose,
                    'reason'            => $request->reason,
                    'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'updated_at'        => now()->format('Y-m-d H:i:s'),
                    'approval_status'   => 'approved',
                ];
            }else{
                $data = [
                    'manual_entry_type' => $request->entry_type,
                    'student_id'        => $request->student_id,
                    'session_number' => optional($student_log)->session_number ? $student_log->session_number + 1 : 1,
                    'going_out_date'     => $request->date,
                    'time_out'          => $request->time_out,
                    'time_out_remark'   => $logout_remark,
                    'time_in'           => $request->time_in,
                    'time_in_remark'   => $login_remark,
                    'destination'       => $request->destination,
                    'purpose'           => $request->purpose,
                    'reason'            => $request->reason,
                    'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'created_at'        => now()->format('Y-m-d H:i:s'),
                    'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                    'updated_at'        => now()->format('Y-m-d H:i:s'),
                    'approval_status'   => 'approved',
                ];
            }

            DB::beginTransaction();

            Going_out::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'going_out_date' => $request->date,
                    'session_number' => $student_log->session_number ? $student_log->session_number + 1 : 1
                ],
                [
                    'is_manual_entry' => 1,
                    'approval_status' => 'pending',
                    'updated_at'        => now()->format('Y-m-d H:i:s'),
                ]
            );

            DB::commit();

            ManualEntryLog::create([
                'student_id'   => $request->student_id,
                'log_type'     => 'going_out',
                'entry_type'   => $request->entry_type,
                'log_id'       => null,
                'reason'       => $request->reason,
                'monitor_name' => session('user.user_fname') . ' ' . session('user.user_lname'),
                'manual_data'  => $data,
                'status'       => 'pending',
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Manual entry submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit manual entry: ' . $e->getMessage());
        }
    }

    public function submitGoingHomeManualEntry(Request $request)
    {
        $request->validate([
            'schedule_name' => 'required',
            'entry_type'    => 'required',
            'student_id'    => 'required|exists:student_details,student_id',
            'date_time_out' => 'nullable|date',
            'time_out'      => 'nullable|date_format:H:i',
            'date_time_in'  => 'nullable|date',
            'time_in'       => 'nullable|date_format:H:i',
            'reason'        => 'nullable|string|max:255',
        ]);

        if (in_array($request->entry_type, ['time_out', 'both']) && !$request->filled('time_out')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Time Out is required for the selected entry type.'
            ]);
        }

        if (in_array($request->entry_type, ['time_in', 'both']) && !$request->filled('time_in')) {
            return redirect()->back()->with([
                'success' => false,
                'message' => 'Time In is required for the selected entry type.'
            ]);
        }

        $student = StudentDetail::get_student($request->student_id);
        if (!$student) {
            return redirect()->back()->with('error', 'Student not found.');
        }

        $schedule = Schedule::get_goinghome_schedule_by_id($request->student_id);
        if (!$schedule) {
            return redirect()->back()->with('error', 'No schedule found for this student on the selected date.');
        }

        $logs = GoingHomeModel::getStudentLogRecord($request->student_id, $schedule);
        if($logs && $logs->time_out && $logs->time_in){
            return redirect()->back()->with('error', 'This student already completed the logs.');
        }

        if($logs && $logs->time_out){
            if($request->entry_type === 'time_out'){
                return redirect()->back()->with('error', 'This student already time out.');
            }
        }

        if($logs && $logs->time_in){
            if($request->entry_type === 'time_in'){
                return redirect()->back()->with('error', 'This student already time in.');
            }
        }

        $timeIn  = !empty($request->time_in)  ? Carbon::parse($request->time_in)  : null;
        $timeOut = !empty($request->time_out) ? Carbon::parse($request->time_out) : null;
        $start_date = $schedule->start_date->toDateString();
        $end_date = $schedule->end_date->toDateString();
        // dd($timeIn, $timeOut, $start_date, $end_date);
        $logout_remark = null;
        if ($timeOut) {
            if ($timeOut->lt($schedule->time_out) && $request->date_time_out <= $start_date) {
                $logout_remark = 'Early';
            } else {
                $logout_remark = 'On Time';
            }
        }
        // dd($logout_remark);
        $login_remark = null;

        if ($timeIn) {
            if ($timeIn->gt($schedule->time_in) && $request->date_time_in >= $end_date) {
                $login_remark = 'Late';
            } else {
                $login_remark = 'On Time';
            }
        }
        // dd($logout_remark);
        if($request->entry_type === 'time_out'){
            $data = [
                'schedule_name'      => $request->schedule_name,
                'manual_entry_type' => $request->entry_type,
                'student_id'        => $request->student_id,
                'date_time_out'     => $request->date_time_out,
                'time_out'          => $request->time_out,
                'time_out_remarks'   => $logout_remark,
                'reason'            => $request->reason,
                'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'created_at'        => now()->format('Y-m-d H:i:s'),
                'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'created_at'        => now()->format('Y-m-d H:i:s'),
                'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'updated_at'        => now()->format('Y-m-d H:i:s'),
                'approval_status'   => 'approved',
            ];
        }elseif($request->entry_type === 'time_in'){
            $data = [
                'schedule_name'      => $request->schedule_name,
                'manual_entry_type' => $request->entry_type,
                'student_id'        => $request->student_id,
                'date_time_in'      => $request->date_time_in,
                'time_in'           => $request->time_in,
                'time_in_remarks'    => $login_remark,
                'reason'            => $request->reason,
                'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'updated_at'        => now()->format('Y-m-d H:i:s'),
                'approval_status'   => 'approved',
            ];
        }else{
            $data = [
                'schedule_name'      => $request->schedule_name,
                'manual_entry_type' => $request->entry_type,
                'student_id'        => $request->student_id,
                'date_time_out'     => $request->date_time_out,
                'time_out'          => $request->time_out,
                'time_out_remarks'   => $logout_remark,
                'date_time_in'      => $request->date_time_in,
                'time_in'           => $request->time_in,
                'time_in_remarks'    => $login_remark,
                'reason'            => $request->reason,
                'created_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'created_at'        => now()->format('Y-m-d H:i:s'),
                'updated_by'        => session('user.user_fname') . ' ' . session('user.user_lname'),
                'updated_at'        => now()->format('Y-m-d H:i:s'),
                'approval_status'   => 'approved',
            ];
        }

        DB::beginTransaction();
        try {
            GoingHomeModel::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'schedule_name' => $request->schedule_name
                ],
                [
                    'is_manual_entry' => 1,
                    'approval_status' => 'pending',
                    'updated_at'        => now()->format('Y-m-d H:i:s'),
                ]
            );

            ManualEntryLog::create([
                'student_id'   => $request->student_id,
                'log_type'     => 'going_home',
                'entry_type'   => $request->entry_type,
                'log_id'       => null,
                'reason'       => $request->reason,
                'monitor_name' => session('user.user_fname') . ' ' . session('user.user_lname'),
                'manual_data'  => $data,
                'status'       => 'pending'
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Manual entry submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to submit manual entry: ' . $e->getMessage());
        }
    }
}
