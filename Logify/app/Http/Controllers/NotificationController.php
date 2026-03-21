<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Academic;
use App\Models\Going_out;
use App\Models\GoingHomeModel;
use App\Models\InternLogModel;
use App\Models\NotificationView;
use App\Models\Visitor;
use App\Models\NotificationHistory;
use App\Models\StudentDetail;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Get notification counts for academic, going out, visitor logs, and late students
     */
    public function getNotificationCounts()
    {
        try {
            $academicCounts = $this->getAcademicNotificationCounts();
            $goingOutCounts = $this->getGoingOutNotificationCounts();
            $visitorCounts = $this->getVisitorNotificationCounts();
            $goinghomeCounts = $this->getGoingHomeNotificationCounts();
            $interCounts = $this->getInternNotificationCounts();
            $lateCounts = $this->getLateStudentNotificationCounts();

            return response()->json([
                'academic' => $academicCounts,
                'goingout' => $goingOutCounts,
                'visitor' => $visitorCounts,
                'goinghome' => $goinghomeCounts,
                'intern'=> $interCounts,
                'late' => $lateCounts,
                'success' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'academic' => ['timeout' => 0, 'timein' => 0],
                'goingout' => ['timeout' => 0, 'timein' => 0],
                'visitor' => ['timeout' => 0, 'timein' => 0],
                'late' => ['count' => 0],
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get academic logs notification counts (separate for time in and time out)
     */
    private function getAcademicNotificationCounts()
    {
        $lastViewed = NotificationView::getLastViewed('academic');
        $today = now()->format('Y-m-d');

        // Count time out activities
        $timeOutQuery = Academic::whereDate('academic_date', $today)
            ->whereNotNull('time_out');

        if ($lastViewed) {
            $timeOutQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        // Count time in activities
        $timeInQuery = Academic::whereDate('academic_date', $today)
            ->whereNotNull('time_in');

        if ($lastViewed) {
            $timeInQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        return [
            'timeout' => $timeOutQuery->count(),
            'timein' => $timeInQuery->count()
        ];
    }

    /**
     * Get going out logs notification counts (separate for time in and time out)
     */
    private function getGoingOutNotificationCounts()
    {
        $lastViewed = NotificationView::getLastViewed('goingout');
        $today = now()->format('Y-m-d');

        // Count time out activities
        $timeOutQuery = Going_out::whereDate('going_out_date', $today)
            ->whereNotNull('time_out');

        if ($lastViewed) {
            $timeOutQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        // Count time in activities
        $timeInQuery = Going_out::whereDate('going_out_date', $today)
            ->whereNotNull('time_in');

        if ($lastViewed) {
            $timeInQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        return [
            'timeout' => $timeOutQuery->count(),
            'timein' => $timeInQuery->count()
        ];
    }

    /**
     * Get visitor logs notification counts (separate for time in and time out)
     */
    private function getVisitorNotificationCounts()
    {
        $lastViewed = NotificationView::getLastViewed('visitor');
        $today = now()->format('Y-m-d');

        // Count time out activities (visitors who have left)
        $timeOutQuery = Visitor::whereDate('visitor_date', $today)
            ->whereNotNull('time_out');

        if ($lastViewed) {
            $timeOutQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        // Count time in activities (new visitors who arrived)
        $timeInQuery = Visitor::whereDate('visitor_date', $today)
            ->whereNotNull('time_in');

        if ($lastViewed) {
            $timeInQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        return [
            'timeout' => $timeOutQuery->count(),
            'timein' => $timeInQuery->count()
        ];
    }

    private function getGoingHomeNotificationCounts()
    {
        $lastViewed = NotificationView::getLastViewed('going_home');
        $today = now()->format('Y-m-d');

        // Count time out activities (visitors who have left)
        $timeOutQuery = GoingHomeModel::whereDate('date_time_out', $today)
            ->whereNotNull('time_out');

        if ($lastViewed) {
            $timeOutQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        // Count time in activities (new visitors who arrived)
        $timeInQuery = GoingHomeModel::whereDate('date_time_in', $today)
            ->whereNotNull('time_in');

        if ($lastViewed) {
            $timeInQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        return [
            'timeout' => $timeOutQuery->count(),
            'timein' => $timeInQuery->count()
        ];
    }

    private function getInternNotificationCounts()
    {
        $lastViewed = NotificationView::getLastViewed('intern');
        $today = now()->format('Y-m-d');

        // Count time out activities (visitors who have left)
        $timeOutQuery = InternLogModel::whereDate('date', $today)
            ->whereNotNull('time_out');

        if ($lastViewed) {
            $timeOutQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        // Count time in activities (new visitors who arrived)
        $timeInQuery = InternLogModel::whereDate('date', $today)
            ->whereNotNull('time_in');

        if ($lastViewed) {
            $timeInQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        return [
            'timeout' => $timeOutQuery->count(),
            'timein' => $timeInQuery->count()
        ];
    }

    /**
     * Get late student notification counts
     */
    private function getLateStudentNotificationCounts()
    {
        $lastViewed = NotificationView::getLastViewed('late');
        $today = now()->format('Y-m-d');

        // Count late students from academic logs
        $academicLateQuery = Academic::whereDate('academic_date', $today)
            ->where('time_in_remark', 'Late');

        if ($lastViewed) {
            $academicLateQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        // Count late students from going out logs
        $goingOutLateQuery = Going_out::whereDate('going_out_date', $today)
            ->where('time_in_remark', 'Late');

        if ($lastViewed) {
            $goingOutLateQuery->where(function($q) use ($lastViewed) {
                $q->where('updated_at', '>', $lastViewed)
                  ->orWhere('created_at', '>', $lastViewed);
            });
        }

        $totalLateCount = $academicLateQuery->count() + $goingOutLateQuery->count();

        return [
            'count' => $totalLateCount
        ];
    }

    /**
     * Mark academic logs as viewed
     */
    public function markAcademicAsViewed()
    {
        NotificationView::markAsViewed('academic');

        return response()->json([
            'success' => true,
            'message' => 'Academic notifications marked as viewed'
        ]);
    }

    /**
     * Mark going out logs as viewed
     */
    public function markGoingOutAsViewed()
    {
        NotificationView::markAsViewed('goingout');

        return response()->json([
            'success' => true,
            'message' => 'Going out notifications marked as viewed'
        ]);
    }

    /**
     * Mark visitor logs as viewed
     */
    public function markVisitorAsViewed()
    {
        NotificationView::markAsViewed('visitor');

        return response()->json([
            'success' => true,
            'message' => 'Visitor notifications marked as viewed'
        ]);
    }

    /**
     * Mark late student notifications as viewed
     */
    public function markLateAsViewed()
    {
        NotificationView::markAsViewed('late');

        return response()->json([
            'success' => true,
            'message' => 'Late student notifications marked as viewed'
        ]);
    }

    /**
     * Get late students with identification for red dots
     * Shows ALL students with late activity from today (for red dots)
     */
    public function getLateStudentsWithActivity()
    {
        try {
            $today = now()->format('Y-m-d');
            $lateStudents = [];

            // Get ALL late students from academic logs today (not filtered by last viewed)
            $academicLateStudents = Academic::with('studentDetail')
                ->whereDate('academic_date', $today)
                ->where('time_in_remark', 'Late')
                ->get();

            foreach ($academicLateStudents as $student) {
                if ($student->studentDetail) {
                    $lateStudents[] = [
                        'student_id' => $student->student_id,
                        'type' => 'academic',
                        'date' => $student->academic_date,
                        'batch' => $student->studentDetail->batch ?? null,
                        'group' => $student->studentDetail->group ?? null,
                        'time_in' => $student->time_in,
                        'created_at' => $student->created_at ? $student->created_at->format('Y-m-d H:i:s') : null
                    ];
                }
            }

            // Get ALL late students from going out logs today (not filtered by last viewed)
            $goingOutLateStudents = Going_out::with('studentDetail')
                ->whereDate('going_out_date', $today)
                ->where('time_in_remark', 'Late')
                ->get();

            foreach ($goingOutLateStudents as $student) {
                if ($student->studentDetail) {
                    $lateStudents[] = [
                        'student_id' => $student->student_id,
                        'type' => 'going_out',
                        'date' => $student->going_out_date,
                        'batch' => $student->studentDetail->batch ?? null,
                        'group' => $student->studentDetail->group ?? null,
                        'time_in' => $student->time_in,
                        'created_at' => $student->created_at ? $student->created_at->format('Y-m-d H:i:s') : null
                    ];
                }
            }

            // Remove duplicates (same student might be late in both academic and going out)
            $uniqueLateStudents = [];
            $seenStudents = [];

            foreach ($lateStudents as $student) {
                if (!in_array($student['student_id'], $seenStudents)) {
                    $uniqueLateStudents[] = $student;
                    $seenStudents[] = $student['student_id'];
                }
            }

            return response()->json([
                'success' => true,
                'students' => $uniqueLateStudents,
                'total_count' => count($uniqueLateStudents),
                'debug_info' => [
                    'academic_late' => count($academicLateStudents),
                    'going_out_late' => count($goingOutLateStudents),
                    'unique_students' => count($uniqueLateStudents)
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'students' => []
            ]);
        }
    }

    /**
     * Display the notification page
     */
    public function notificationPage()
    {
        // Ensure history is up to date before viewing
        $this->syncRecentActivitiesToHistory();

        // Mark all notifications as read when the page is accessed
        NotificationHistory::markAllAsRead();

        return view('user-educator.notifications');
    }

    /**
     * Get notification history for the notification page
     */
    public function getNotificationHistory(Request $request)
    {
        try {
            // Ensure history is up to date before fetching
            $this->syncRecentActivitiesToHistory();
            $limit = $request->get('limit', 50);
            $offset = $request->get('offset', 0);

            // Get recent notifications from notification_history table
            $notifications = NotificationHistory::with(['studentDetail.user'])
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->offset($offset)
                ->get()
                ->map(function ($notification) {
                    // Get student name - handle both students and visitors
                    $studentName = 'Unknown';
                    if ($notification->log_type === 'visitor') {
                        // For visitors, student_id is actually the visitor name
                        $studentName = $notification->student_id;
                    } else {
                        // For students, get name from relationship
                        if ($notification->studentDetail && $notification->studentDetail->user) {
                            $studentName = $notification->studentDetail->user->user_fname . ' ' . $notification->studentDetail->user->user_lname;
                        }
                    }

                    return [
                        'id' => $notification->id,
                        'student_id' => $notification->student_id,
                        'student_name' => $studentName,
                        'batch' => $notification->batch,
                        'action_type' => $notification->action_type,
                        'log_type' => $notification->log_type,
                        'is_late' => $notification->is_late,
                        'timing_status' => $notification->timing_status,
                        'timestamp' => $notification->created_at->format('Y-m-d H:i:s'),
                        'time_formatted' => $notification->created_at->format('g:i A'),
                        'date_formatted' => $notification->created_at->format('M j, Y'),
                        'is_read' => $notification->is_read
                    ];
                });

            // Mark all notifications as read when fetching
            NotificationHistory::where('is_read', false)->update(['is_read' => true]);
                // die($notifications);
            return response()->json([
                'success' => true,
                'notifications' => $notifications,
                'total' => NotificationHistory::count()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
                'notifications' => []
            ]);
        }
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        try {
            NotificationHistory::where('is_read', false)->update(['is_read' => true]);

            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get unread notification count for the notification badge
     */
    public function getUnreadCount()
    {
        try {
            // Ensure history contains latest external activities
            $this->syncRecentActivitiesToHistory();

            $count = NotificationHistory::where('is_read', false)->count();

            return response()->json([
                'success' => true,
                'count' => $count
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'count' => 0,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Ingest recent activities (even if created outside Logify) into notification_history
     * so the Notifications badge and page reflect student log in/out and visitor events.
     */
    private function syncRecentActivitiesToHistory(): void
    {
        try {
            $today = now()->format('Y-m-d');

            // Use most recent history timestamp to avoid duplicates
            $latestHistoryAt = NotificationHistory::max('created_at');
            $since = $latestHistoryAt ? Carbon::parse($latestHistoryAt)->subMinute() : Carbon::today();
            // die($since);
            /** ---------------- Academic Logs ---------------- */
            $academicTimeouts = Academic::with('studentDetail')
                ->whereDate('academic_date', $today)
                ->whereNotNull('time_out')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();

            foreach ($academicTimeouts as $log) {
                $this->createHistoryOnce(
                    $log->student_id,
                    optional($log->studentDetail)->batch,
                    'time_out',
                    'academic',
                    $log->time_out_remark === 'Late',
                    $log->time_out_remark,
                    Carbon::parse($log->academic_date.' '.$log->time_out)
                );
            }

            $academicTimeins = Academic::with('studentDetail')
                ->whereDate('academic_date', $today)
                ->whereNotNull('time_in')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();

            foreach ($academicTimeins as $log) {
                $this->createHistoryOnce(
                    $log->student_id,
                    optional($log->studentDetail)->batch,
                    'time_in',
                    'academic',
                    $log->time_in_remark === 'Late',
                    $log->time_in_remark,
                    Carbon::parse($log->academic_date.' '.$log->time_in)
                );
            }

            /** ---------------- Going Out Logs ---------------- */
            $goingTimeouts = Going_out::with('studentDetail')
                ->whereDate('going_out_date', $today)
                ->whereNotNull('time_out')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();
            // die($goingTimeouts);
            foreach ($goingTimeouts as $log) {
                $this->createHistoryOnce(
                    $log->student_id,
                    optional($log->studentDetail)->batch,
                    'time_out',
                    'going_out',
                    $log->time_out_remark === 'Late',
                    $log->time_out_remark,
                    Carbon::parse($log->going_out_date.' '.$log->time_out)
                );
            }

            $goingTimeins = Going_out::with('studentDetail')
                ->whereDate('going_out_date', $today)
                ->whereNotNull('time_in')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();

            foreach ($goingTimeins as $log) {
                $this->createHistoryOnce(
                    $log->student_id,
                    optional($log->studentDetail)->batch,
                    'time_in',
                    'going_out',
                    $log->time_in_remark === 'Late',
                    $log->time_in_remark,
                    Carbon::parse($log->going_out_date.' '.$log->time_in)
                );
            }

            /** ---------------- Going Home Logs ---------------- */
            $goingHomeTimeouts = GoingHomeModel::with('studentDetail')
                ->whereDate('date_time_out', $today)
                ->whereNotNull('time_out')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();
            // die($goingHomeTimeouts);
            foreach ($goingHomeTimeouts as $log) {
                $this->createHistoryOnce(
                    $log->student_id,
                    optional($log->studentDetail)->batch,
                    'time_out',
                    'going_home',
                    $log->time_out_remarks === 'Late',
                    $log->time_out_remarks,
                    Carbon::parse($log->date_time_out)
                );
            }

            $goingHomeTimeins = GoingHomeModel::with('studentDetail')
                ->whereDate('date_time_in', $today)
                ->whereNotNull('time_in')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();

            foreach ($goingHomeTimeins as $log) {
                $this->createHistoryOnce(
                    $log->student_id,
                    optional($log->studentDetail)->batch,
                    'time_in',
                    'going_home',
                    $log->time_in_remarks === 'Late',
                    $log->time_in_remarks,
                    Carbon::parse($log->date_time_in)
                );
            }

            /** ---------------- Intern Logs ---------------- */
            $internTimeouts = InternLogModel::with('studentDetail')
                ->whereDate('date', $today)
                ->whereNotNull('time_out')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();

            foreach ($internTimeouts as $log) {
                $this->createHistoryOnce(
                    $log->student_id,
                    optional($log->studentDetail)->batch,
                    'time_out',
                    'intern',
                    $log->time_out_remark === 'Late',
                    $log->time_out_remark,
                    Carbon::parse($log->date.' '.$log->time_out)
                );
            }

            $internTimeins = InternLogModel::with('studentDetail')
                ->whereDate('date', $today)
                ->whereNotNull('time_in')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();

            foreach ($internTimeins as $log) {
                $this->createHistoryOnce(
                    $log->student_id,
                    optional($log->studentDetail)->batch,
                    'time_in',
                    'intern',
                    $log->time_in_remark === 'Late',   // ✅ singular
                    $log->time_in_remark,              // ✅ singular
                    Carbon::parse($log->date.' '.$log->time_in)
                );
            }

            /** ---------------- Visitor Logs ---------------- */
            $visitorTimeouts = Visitor::whereDate('visit_date', $today)
                ->whereNotNull('time_out')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();

            foreach ($visitorTimeouts as $v) {
                $this->createHistoryOnce(
                    $v->visitor_name, // stored in student_id field
                    null,
                    'time_out',
                    'visitor',
                    false,
                    'On Time',
                    Carbon::parse($v->visitor_date.' '.$v->time_out)
                );
            }

            $visitorTimeins = Visitor::whereDate('visit_date', $today)
                ->whereNotNull('time_in')
                ->where(function($q) use ($since) {
                    $q->where('updated_at', '>', $since)
                    ->orWhere('created_at', '>', $since);
                })->get();

            foreach ($visitorTimeins as $v) {
                $this->createHistoryOnce(
                    $v->visitor_name,
                    null,
                    'time_in',
                    'visitor',
                    false,
                    'On Time',
                    Carbon::parse($v->visit_date.' '.$v->time_in)
                );
            }

        } catch (\Exception $e) {
            // Silent fail to avoid breaking UI
        }
    }

    /**
     * Create a NotificationHistory record if an equivalent one doesn't already exist.
     */
    private function createHistoryOnce($studentId, $batch, $actionType, $logType, $isLate, $timingStatus, Carbon $activityAt): void
    {
        $exists = NotificationHistory::where('student_id', $studentId)
            ->where('action_type', $actionType)
            ->where('log_type', $logType)
            ->whereDate('activity_timestamp', $activityAt->toDateString())
            ->exists();

        if (!$exists) {
            NotificationHistory::create([
                'student_id'       => $studentId,
                'batch'            => $batch,
                'action_type'      => $actionType,
                'log_type'         => $logType,
                'is_late'          => (bool) $isLate,
                'timing_status'    => $timingStatus,
                'is_read'          => false,
                'activity_timestamp' => $activityAt,
            ]);
        }
    }
}
