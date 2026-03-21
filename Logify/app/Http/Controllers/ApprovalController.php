<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\ManualEntryLog;
use App\Models\Academic;
use App\Models\Going_out;
use App\Models\GoingHomeModel;
use App\Models\InternLogModel;
use App\Models\InternshipSchedule;
use App\Models\Visitor;
use App\Models\NotificationHistory;
use App\Models\Schedule;
use App\Models\StudentDetail;
use App\Models\VisitorLog;
use Carbon\Carbon;

class ApprovalController extends Controller
{
    /**
     * Show the approval dashboard for educators
     */
    public function index(Request $request)
    {
        $type = $request->get('type', 'all'); // all, academic, going_out, visitor
        $status = $request->get('status', 'pending'); // pending, approved, rejected, all

        // Get student manual entries
        $studentQuery = ManualEntryLog::with(['studentDetail.user'])
            ->orderBy('created_at', 'desc');

        if ($type !== 'all' && $type !== 'visitor') {
            $studentQuery->where('log_type', $type);
        }

        if ($status !== 'all') {
            $studentQuery->where('status', $status);
        }

        // Get visitor manual entries
        $visitorQuery = Visitor::where('is_manual_entry', true)
            ->orderBy('manual_entry_timestamp', 'desc');

        if ($status !== 'all') {
            $visitorQuery->where('approval_status', $status);
        }

        // Combine results based on type filter
        if ($type === 'visitor') {
            $manualEntries = collect();
            $visitorEntries = $visitorQuery->paginate(20);
        } elseif ($type === 'all') {
            $manualEntries = $studentQuery->paginate(10);
            $visitorEntries = $visitorQuery->paginate(10);
        } else {
            $manualEntries = $studentQuery->paginate(20);
            $visitorEntries = collect();
        }

        // Get counts for badges
        $studentPendingCount = ManualEntryLog::where('status', 'pending')->count();
        $studentApprovedCount = ManualEntryLog::where('status', 'approved')->count();
        $studentRejectedCount = ManualEntryLog::where('status', 'rejected')->count();

        $visitorPendingCount = Visitor::where('is_manual_entry', true)->where('approval_status', 'pending')->count();
        $visitorApprovedCount = Visitor::where('is_manual_entry', true)->where('approval_status', 'approved')->count();
        $visitorRejectedCount = Visitor::where('is_manual_entry', true)->where('approval_status', 'rejected')->count();

        $pendingCount = $studentPendingCount + $visitorPendingCount;
        $approvedCount = $studentApprovedCount + $visitorApprovedCount;
        $rejectedCount = $studentRejectedCount + $visitorRejectedCount;

        return view('user-educator.approvals', compact(
            'manualEntries',
            'visitorEntries',
            'type',
            'status',
            'pendingCount',
            'approvedCount',
            'rejectedCount'
        ));
    }

    /**
     * Get manual entry details for review
     */
    public function getEntryDetails($id)
    {
        try {
            $entry = ManualEntryLog::with(['studentDetail.user'])->findOrFail($id);

            return response()->json([
                'success'   => true,
                'entry'     => $entry,
                'student'   => $entry->studentDetail,
                'user'      => $entry->studentDetail->user ?? null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch entry details',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Approve a manual entry
     */
    public function approve(Request $request)
    {
        $request->validate([
            'notes'    => 'nullable|string|max:1000',
            'entry_id' => 'required|exists:manual_entry_logs,id',
            'log_type' => 'required|in:academic,going_out,going_home,intern,visitor'
        ]);

        try {
            DB::beginTransaction();

            $entry = ManualEntryLog::findOrFail($request->entry_id);
            $educatorName = session('user.user_fname') . ' ' . session('user.user_lname');

            if ($request->log_type === 'academic') {
                try{
                    Academic::updateOrCreate(
                        [
                            'student_id'    => $entry->student_id,
                            'academic_date' => $entry->manual_data['academic_date'],
                        ],
                        $entry->manual_data
                    );
                } catch (\Exception $e) {
                    DB::rollBack();
                    redirect()->back()->with('error', 'Failed to approve manual entry: ' . $e->getMessage());
                }
            }elseif ($request->log_type === 'going_out') {
                $student_log = Going_out::get_student_by_date($entry->student_id, $entry->manual_data['going_out_date']);

                Going_out::updateOrCreate(
                    [
                        'student_id'     => $entry->student_id,
                        'going_out_date' => $entry->manual_data['going_out_date'],
                        'session_number' => $student_log->session_number
                    ],
                    $entry->manual_data
                );
            }elseif ($request->log_type === 'intern') {
                InternLogModel::updateOrCreate(
                    [
                        'student_id'    => $entry->student_id,
                        'date' => $entry->manual_data['date'],
                    ],
                    $entry->manual_data
                );
            }elseif ($request->log_type === 'going_home') {
                GoingHomeModel::updateOrCreate(
                    [
                        'student_id'    => $entry->student_id,
                        'schedule_name' => $entry->manual_data['schedule_name'],
                    ],
                    $entry->manual_data
                );
            }elseif ($request->log_type === 'visitor') {
                try {
                    Visitor::updateOrCreate(
                        [
                            'visitor_pass' => $entry->manual_data['visitor_pass'],
                            'visitor_name' => $entry->manual_data['visitor_name'],
                            'visit_date'   => date('Y-m-d', strtotime($entry->manual_data['visit_date'])),
                        ],
                        $entry->manual_data
                    );
                } catch (\Exception $e) {
                    return redirect()->back()->with('error','Failed to save visitor log');
                }
            }
            $entry->update([
                'status' => 'approved',
                'approved_by' => $educatorName,
                'approved_at' => now(),
                'approval_notes' => $request->notes
            ]);

            $this->createNotificationsForApprovedEntry($entry);
            DB::commit();
            return redirect()->back()->with('success','Manual entry approved successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to approve manual entry: ' . $e->getMessage());
        }
    }

    /**
     * Reject a manual entry
     */
    public function reject(Request $request)
    {
        $request->validate([
            'notes'    => 'nullable|string|max:1000',
            'entry_id' => 'required|exists:manual_entry_logs,id',
            'log_type' => 'required|in:academic,going_out,going_home,intern,visitor'
        ]);

        try {
            DB::beginTransaction();

            $entry = ManualEntryLog::findOrFail($request->entry_id);
            $educatorName = session('user.user_fname') . ' ' . session('user.user_lname');

            if ($request->log_type === 'academic') {
                $manual_log = Academic::where('student_id', $entry->student_id)
                    ->where('academic_date', $entry->manual_data['academic_date'])
                    ->firstOrFail();

                $manual_log->update([
                    'approval_status' => 'rejected',
                ]);
            }elseif ($request->log_type === 'going_out') {
                $manual_log = Going_out::where('student_id', $entry->student_id)
                    ->where('going_out_date', $entry->manual_data['going_out_date'])
                    ->firstOrFail();

                $manual_log->update([
                    'approval_status' => 'rejected',
                ]);
            }elseif ($request->log_type === 'going_home') {
                $manual_log = GoingHomeModel::where('student_id', $entry->student_id)
                    ->where('schedule_name', $entry->manual_data['schedule_name'])
                    ->firstOrFail();

                $manual_log->update([
                    'approval_status' => 'rejected',
                ]);
            }elseif ($request->log_type === 'intern') {
                $manual_log = InternLogModel::where('student_id', $entry->student_id)
                    ->where('date', $entry->manual_data['date'])
                    ->firstOrFail();

                $manual_log->update([
                    'approval_status' => 'rejected',
                ]);
            }

            $entry->update([
                'status' => 'rejected',
                'approved_by' => $educatorName,
                'approved_at' => now(),
                'approval_notes' => $request->notes
            ]);
            DB::commit();
            return redirect()->back()->with('success', 'Manual entry rejected successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to reject manual entry');
        }
    }

    /**
     * Bulk approve multiple entries
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'entry_ids' => 'required|array',
            'entry_ids.*' => 'exists:manual_entry_logs,id',
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $educatorName = Auth::user()->user_fname . ' ' . Auth::user()->user_lname;
            $approvedCount = 0;

            foreach ($request->entry_ids as $entryId) {
                $entry = ManualEntryLog::find($entryId);
                if ($entry && $entry->status === 'pending') {
                    $entry->update([
                        'status' => 'approved',
                        'approved_by' => $educatorName,
                        'approved_at' => now(),
                        'approval_notes' => $request->notes
                    ]);

                    $logRecord = $entry->getLogRecord();
                    if ($logRecord) {
                        $logRecord->update([
                            'approval_status' => 'approved',
                            'approved_by' => $educatorName,
                            'approved_at' => now(),
                            'approval_notes' => $request->notes
                        ]);
                    }

                    $this->createNotificationsForApprovedEntry($entry);

                    $approvedCount++;
                }
            }

            DB::commit();

            Log::info('Bulk approval completed', [
                'approved_count' => $approvedCount,
                'approved_by' => $educatorName
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully approved {$approvedCount} manual entries"
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve entries'
            ], 500);
        }
    }

    /**
     * Approve a visitor manual entry
     */
    public function approveVisitor(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        try {
            $visitor = Visitor::findOrFail($id);

            // Check if this is actually a manual entry
            if (!$visitor->is_manual_entry || $visitor->approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This visitor entry is not pending manual entry approval.'
                ], 400);
            }

            $educatorName = Auth::user()->user_fname . ' ' . Auth::user()->user_lname;

            // Update approval status
            $visitor->update([
                'approval_status' => 'approved',
                'approved_by' => $educatorName,
                'approved_at' => now(),
                'approval_notes' => $request->notes,
                'updated_by' => $educatorName,
                'updated_at' => now()
            ]);

            // Now create notifications since the entry is approved
            if ($visitor->time_in) {
                NotificationHistory::createNotification(
                    $visitor->visitor_name,
                    null,
                    'time_in',
                    'visitor',
                    false,
                    'On Time'
                );
            }

            if ($visitor->time_out) {
                NotificationHistory::createNotification(
                    $visitor->visitor_name,
                    null,
                    'time_out',
                    'visitor',
                    false,
                    'On Time'
                );
            }

            Log::info('Visitor manual entry approved', [
                'visitor_id' => $id,
                'visitor_name' => $visitor->visitor_name,
                'approved_by' => $educatorName,
                'manual_entry_type' => $visitor->manual_entry_type
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visitor manual entry approved successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to approve visitor manual entry', [
                'error' => $e->getMessage(),
                'visitor_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve visitor manual entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject a visitor manual entry
     */
    public function rejectVisitor(Request $request, $id)
    {
        $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        try {
            $visitor = Visitor::findOrFail($id);

            // Check if this is actually a manual entry
            if (!$visitor->is_manual_entry || $visitor->approval_status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'This visitor entry is not pending manual entry approval.'
                ], 400);
            }

            $educatorName = Auth::user()->user_fname . ' ' . Auth::user()->user_lname;

            // Update approval status
            $visitor->update([
                'approval_status' => 'rejected',
                'approved_by' => $educatorName,
                'approved_at' => now(),
                'approval_notes' => $request->notes,
                'updated_by' => $educatorName,
                'updated_at' => now()
            ]);

            Log::info('Visitor manual entry rejected', [
                'visitor_id' => $id,
                'visitor_name' => $visitor->visitor_name,
                'rejected_by' => $educatorName,
                'manual_entry_type' => $visitor->manual_entry_type,
                'reason' => $request->notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visitor manual entry rejected successfully.'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to reject visitor manual entry', [
                'error' => $e->getMessage(),
                'visitor_id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject visitor manual entry: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create notifications for approved manual entries
     */
    private function createNotificationsForApprovedEntry(ManualEntryLog $entry)
    {
        try {
            // Get student details for batch information
            $student = StudentDetail::where('student_id', $entry->student_id)->first();
            if (!$student) {
                Log::warning('Student not found for notification creation', [
                    'student_id' => $entry->student_id,
                    'entry_id' => $entry->id
                ]);
                return;
            }

            // Create notifications based on entry type
            if (in_array($entry->entry_type, ['time_out', 'both'])) {
                NotificationHistory::createNotification(
                    $entry->student_id,
                    $student->batch,
                    'time_out',
                    $entry->log_type,
                    false, // Manual entries are not marked as late in notifications
                    'Manual Entry' // Use 'Manual Entry' as timing status
                );
            }

            if (in_array($entry->entry_type, ['time_in', 'both'])) {
                NotificationHistory::createNotification(
                    $entry->student_id,
                    $student->batch,
                    'time_in',
                    $entry->log_type,
                    false, // Manual entries are not marked as late in notifications
                    'Manual Entry' // Use 'Manual Entry' as timing status
                );
            }

            Log::info('Notifications created for approved manual entry', [
                'entry_id' => $entry->id,
                'student_id' => $entry->student_id,
                'entry_type' => $entry->entry_type,
                'log_type' => $entry->log_type
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating notifications for approved manual entry', [
                'entry_id' => $entry->id,
                'student_id' => $entry->student_id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
