<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InternLogModel;
use App\Models\GoingHomeModel;
use App\Models\StudentDetail;
use Illuminate\Support\Facades\Auth;
use App\Models\Going_out;
use App\Models\InternshipSchedule;
use App\Models\Schedule;
use App\Models\NotificationView;
use App\Models\NotificationHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class GoingOutLogController extends Controller
{
    public function logTimeOut(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
            'destination' => 'required|string',
            'purpose' => 'required|string',
        ]);

        $student = StudentDetail::where('student_id', $request->student_id)->first();

        if (!$student) {
            return redirect()->route('goingOutLogForms.show')->with('error', 'Student not found.');
        }

        $today = now()->format('l');

        Schedule::clearBootedModels();

        // First, check for unique leisure schedule (individual leisure schedule)
        $schedule = Schedule::where([
                ['student_id', $student->student_id],
                ['day_of_week', $today],
                ['schedule_type', 'unique_leisure']
            ])->where(function ($query) {
                $query->whereNotNull('valid_until')
                      ->whereDate('valid_until', '>=', Carbon::today());
            })
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$schedule) {
            $schedule = Schedule::where([
                    ['batch', $student->batch],
                    ['day_of_week', $today],
                    ['schedule_type', 'going_out'],
                    ['is_batch_schedule', true]
                ])->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('start_date')
                          ->whereNotNull('end_date')
                          ->where('start_date', '<=', Carbon::today())
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
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$schedule) {
            $schedule = Schedule::where([
                    ['gender', $student->user->gender],
                    ['day_of_week', $today],
                    ['student_id', null],
                    ['schedule_type', 'going_out']
                ])->where(function ($query) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', Carbon::today());
                })
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$schedule) {
            return redirect()->route('goingOutLogForms.show')
                ->with('error', 'No schedule set for today! Please contact your educator for assistance.');
        }

        $scheduleStartTime = Carbon::parse($schedule->getRawTimeOut());
        $scheduleEndTime = Carbon::parse($schedule->getRawTimeIn());
        $currentTime = Carbon::parse(now());

        if ($currentTime < $scheduleStartTime) {
            return redirect()->route('goingOutLogForms.show')
                ->with('error', 'Going out period has not started yet! Going out starts at ' . $scheduleStartTime->format('g:i A') . '.');
        }

        if ($currentTime > $scheduleEndTime) {
            return redirect()->route('goingOutLogForms.show')
                ->with('error', 'Going out period has ended! Going out ended at ' . $scheduleEndTime->format('g:i A') . '.');
        }

        $isSunday = now()->isSunday();
        $todayDate = now()->toDateString();

        if ($isSunday) {
            $goingOut = Going_out::where('student_id', $request->student_id)
                ->whereDate('going_out_date', $todayDate)
                ->first();

            if (!$goingOut) {
                $goingOut = Going_out::create([
                    'student_id' => $request->student_id,
                    'going_out_date' => $todayDate,
                    'session_number' => 1,
                    'session_status' => 'active',
                ]);
            }

            if ($goingOut->monitor_logged_out) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'Monitor has already logged you out. You cannot log out again.');
            }

            if ($goingOut->time_out) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'You already log out for this date.');
            }
        } else {
            $activeSession = Going_out::getCurrentActiveSession($request->student_id, $todayDate);

            if ($activeSession && $activeSession->time_out) {
                if ($activeSession->monitor_logged_out) {
                    return redirect()->route('goingOutLogForms.show')->with('error', 'Monitor has already logged you out. You cannot log out again.');
                }
                return redirect()->route('goingOutLogForms.show')->with('error', 'You already logged out for this session. Please log in first to start a new session.');
            }
        }

        $createdBy = $student->user->user_fname . ' ' . $student->user->user_lname;

        $remark = 'On Time';

        if ($isSunday) {
            Going_out::updateOrCreate(
                [
                    'student_id' => $request->student_id,
                    'going_out_date' => $todayDate,
                ],
                [
                    'session_number' => 1,
                    'session_status' => 'active',
                    'destination' => $request->destination,
                    'purpose' => $request->purpose,
                    'time_out' => now(),
                    'time_out_remark' => $remark,
                    'created_by' => $createdBy,
                    'created_at' => now()
                ]
            );

            $isLate = ($remark === 'Late');
            NotificationHistory::createNotification(
                $request->student_id,
                $student->batch,
                'time_out',
                'going_out',
                $isLate,
                $remark
            );
        } else {
            $activeSession = Going_out::getCurrentActiveSession($request->student_id, $todayDate);

            if ($activeSession && !$activeSession->time_out) {
                $activeSession->update([
                    'destination' => $request->destination,
                    'purpose' => $request->purpose,
                    'time_out' => now(),
                    'time_out_remark' => $remark,
                    'updated_by' => $createdBy,
                    'updated_at' => now()
                ]);

                $isLate = ($remark === 'Late');
                NotificationHistory::createNotification(
                    $request->student_id,
                    $student->batch,
                    'time_out',
                    'going_out',
                    $isLate,
                    $remark
                );
            } else {
                $sessionNumber = Going_out::getNextSessionNumber($request->student_id, $todayDate);

                Going_out::create([
                    'student_id' => $request->student_id,
                    'going_out_date' => $todayDate,
                    'session_number' => $sessionNumber,
                    'session_status' => 'active',
                    'destination' => $request->destination,
                    'purpose' => $request->purpose,
                    'time_out' => now(),
                    'time_out_remark' => $remark,
                    'created_by' => $createdBy,
                    'created_at' => now()
                ]);

                $isLate = ($remark === 'Late');
                NotificationHistory::createNotification(
                    $request->student_id,
                    $student->batch,
                    'time_out',
                    'going_out',
                    $isLate,
                    $remark
                );
            }
        }

        $latestGoingOut = Going_out::where('student_id', $request->student_id)
            ->whereDate('going_out_date', $todayDate)
            ->orderBy('session_number', 'desc')
            ->first();

        $log_remark = $latestGoingOut ? $latestGoingOut->time_out_remark : $remark;

        return redirect()->route('goingOutLogForms.show')->with('success', 'Time-out recorded successfully. You are ' . $log_remark . '.');
    }

    /**
     * Store a new going-out log (login).
     */
    public function logTimeIn(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
        ]);

        $student = StudentDetail::where('student_id', $request->student_id)->first();

        if (!$student) {
            return redirect()->route('goingOutLogForms.show')->with('error', 'Student not found.');
        }

        $today = Carbon::now()->format('l');

        Schedule::clearBootedModels();

        // First, check for unique leisure schedule (individual leisure schedule)
        $schedule = Schedule::where([
                ['student_id', $student->student_id],
                ['day_of_week', $today],
                ['schedule_type', 'unique_leisure']
            ])->where(function ($query) {
                $query->whereNotNull('valid_until')
                      ->whereDate('valid_until', '>=', Carbon::today());
            })
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc')
            ->first();

        if (!$schedule) {
            $schedule = Schedule::where([
                    ['batch', $student->batch],
                    ['day_of_week', $today],
                    ['schedule_type', 'going_out'],
                    ['is_batch_schedule', true]
                ])->where(function ($query) {
                    $query->where(function ($q) {
                        $q->whereNotNull('start_date')
                          ->whereNotNull('end_date')
                          ->where('start_date', '<=', Carbon::today())
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
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$schedule) {
            $schedule = Schedule::where([
                    ['gender', $student->user->gender],
                    ['day_of_week', $today],
                    ['student_id', null],
                    ['schedule_type', 'going_out']
                ])->where(function ($query) {
                    $query->whereNull('valid_until')
                        ->orWhere('valid_until', '>=', Carbon::today());
                })
                ->orderBy('updated_at', 'desc')
                ->orderBy('created_at', 'desc')
                ->first();
        }

        if (!$schedule) {
            return redirect()->route('goingOutLogForms.show')
                ->with('error', 'No schedule set for today! Please contact your educator for assistance.');
        }

        $todayDate = now()->toDateString();
        $isSunday = now()->isSunday();

        if ($isSunday) {
            $goingOut = Going_out::where('student_id', $request->student_id)
                ->whereDate('going_out_date', $todayDate)
                ->first();

            if (!$goingOut) {
                $goingOut = Going_out::create([
                    'student_id' => $request->student_id,
                    'going_out_date' => $todayDate,
                    'session_number' => 1,
                    'session_status' => 'active',
                ]);
            }

            if (!$goingOut->time_out) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'You have not log out for this date.');
            }

            if ($goingOut->monitor_logged_in) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'Monitor has already logged you in. You cannot log in again.');
            }

            if ($goingOut->time_in) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'You already log in for this date.');
            }
        } else {
            $goingOut = Going_out::getCurrentActiveSession($request->student_id, $todayDate);

            if (!$goingOut) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'You have not logged out yet or no active session found.');
            }

            if (!$goingOut->time_out) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'You have not log out for this session.');
            }

            if ($goingOut->monitor_logged_in) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'Monitor has already logged you in. You cannot log in again.');
            }

            if ($goingOut->time_in) {
                return redirect()->route('goingOutLogForms.show')->with('error', 'You already log in for this session.');
            }
        }
        if ($goingOut) {
            $scheduleEndTime = Carbon::parse($schedule->getRawTimeIn());
            $updatedBy = $student->user->user_fname . ' ' . $student->user->user_lname;
            $currentTime = Carbon::parse(now());

            $remark = 'On Time';
            if ($schedule) {
                if ($currentTime->greaterThan($scheduleEndTime)) {
                    $remark = 'Late';
                } else {
                    $remark = 'On Time';
                }
            }

            $goingOut->update([
                'time_in' => now(),
                'time_in_remark' => $remark,
                'session_status' => 'completed',
                'updated_by' => $updatedBy,
                'updated_at' => now()
            ]);

            $isLate = ($remark === 'Late');
            NotificationHistory::createNotification(
                $request->student_id,
                $student->batch,
                'time_in',
                'going_out',
                $isLate,
                $remark
            );
        }

        $log_remark = $goingOut->time_in_remark ?: $remark;

        return redirect()->route('goingOutLogForms.show')->with('success', 'Time-in logged successfully. You are ' . $log_remark .'.');
    }

    /**
     * Monitor past going-out logs with filtering capabilities.
     */
    public function pastLogs(Request $request)
    {
        $query = Going_out::with(['studentDetail'])
            ->orderBy('going_out_date', 'desc')
            ->orderBy('time_out', 'desc');

        if ($request->has('month') && !empty($request->month)) {
            $month = date('m', strtotime($request->month));
            $year = date('Y', strtotime($request->month));
            $query->whereMonth('going_out_date', $month)
                ->whereYear('going_out_date', $year);
        }

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('going_out_date', $request->date);
        }

        if ($request->has('session') && !empty($request->session)) {
            $query->where('session_number', $request->session);
        }

        $goingOutLogs = $query->paginate(20)->appends($request->query());

        if ($goingOutLogs->isEmpty() && $request->has('date') && !empty($request->date)) {
            $allStudents = StudentDetail::all();
            $selectedDate = Carbon::parse($request->date);
            $isSunday = $selectedDate->isSunday();

            foreach ($allStudents as $student) {
                if ($isSunday) {
                    Going_out::firstOrCreate([
                        'student_id' => $student->student_id,
                        'going_out_date' => $request->date,
                        'session_number' => 1,
                    ], [
                        'session_status' => 'active',
                        'time_out' => null,
                        'time_out_remark' => 'No Time Out',
                        'time_in' => null,
                        'time_in_remark' => 'No Time In',
                    ]);
                } else {
                    Going_out::firstOrCreate([
                        'student_id' => $student->student_id,
                        'going_out_date' => $request->date,
                        'session_number' => 1,
                    ], [
                        'session_status' => 'active',
                        'time_out' => null,
                        'time_out_remark' => 'No Time Out',
                        'time_in' => null,
                        'time_in_remark' => 'No Time In',
                    ]);
                }
            }

            $goingOutLogs = Going_out::with('studentDetail')
                ->whereDate('going_out_date', $request->date)
                ->paginate(20)
                ->appends($request->query());
        }

        $batches = StudentDetail::distinct()->pluck('batch')->sort();
        $groups = StudentDetail::distinct()->pluck('group')->sort();

        // Get available sessions for the selected date
        $sessions = collect();
        if ($request->date) {
            $sessions = Going_out::whereDate('going_out_date', $request->date)
                ->distinct()
                ->pluck('session_number')
                ->sort()
                ->values();
        }

        return view('user-educator.goingoutmonitor', [
            'todayLogs' => $goingOutLogs,
            'isPastLogs' => true,
            'selectedMonth' => $request->month ?? '',
            'selectedDate' => $request->date ?? '',
            'batches' => $batches,
            'groups' => $groups,
            'sessions' => $sessions,
        ]);
    }

    /**
     * Update the educator's consideration for a going out log.
     */
    public function updateConsideration(Request $request, $id)
    {
        try {
            $request->validate([
                'educator_consideration' => 'required|in:Excused,Not Excused',
                'consideration_type' => 'required|in:time_in,time_out'
            ]);

            $goingOut = Going_out::findOrFail($id);
            $considerationType = $request->consideration_type;

            if ($considerationType === 'time_out') {
                if (!$goingOut->time_out) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set consideration: Student must log time out first'
                    ]);
                }

                if (!in_array($goingOut->time_out_remark, ['Late', 'Automatic'])) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set consideration: Student must be late to set any consideration'
                    ]);
                }

                $updateField = 'time_out_consideration';
                $logMessage = 'Updated going out time out consideration';
            } else {
                if (!$goingOut->time_in) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set consideration: Student must log time in first'
                    ]);
                }

                if ($goingOut->time_in_remark !== 'Late') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Cannot set consideration: Student must be late to set any consideration'
                    ]);
                }

                $updateField = 'educator_consideration';
                $logMessage = 'Updated going out time in consideration';
            }

            DB::beginTransaction();

            try {
                $goingOut->update([
                    $updateField => $request->educator_consideration
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
}
