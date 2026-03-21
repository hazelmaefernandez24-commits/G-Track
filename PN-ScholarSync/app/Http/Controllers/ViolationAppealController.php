<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\ViolationAppeal;
use App\Models\Violation;
use App\Models\StudentDetails;
use App\Models\User;
use App\Models\Notification;
use App\Http\Controllers\NotificationController;

class ViolationAppealController extends Controller
{
    /**
     * Submit a new appeal for a violation
     */
    public function submitAppeal(Request $request)
    {
        try {
            // Validate the request
            $validator = Validator::make($request->all(), [
                'violation_id' => 'required|exists:violations,id',
                'student_reason' => 'required|string|min:50|max:1000',
                'additional_evidence' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Please correct the errors and try again.',
                        'errors' => $validator->errors()
                    ], 422);
                }

                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput()
                    ->with('error', 'Please correct the errors and try again.');
            }

            $user = Auth::user();
            $studentDetails = $user->studentDetails;

            if (!$studentDetails) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Student information not found. Please contact administration.'
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'Student information not found. Please contact administration.');
            }

            // Get the violation and verify it belongs to this student
            $violation = Violation::where('id', $request->violation_id)
                ->where('student_id', $studentDetails->student_id)
                ->first();

            if (!$violation) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Violation not found or you do not have permission to appeal it.'
                    ], 404);
                }

                return redirect()->back()
                    ->with('error', 'Violation not found or you do not have permission to appeal it.');
            }

            // Check if violation can be appealed
            if (!$violation->canBeAppealed()) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'This violation cannot be appealed. It may have already been appealed or is not in an active status.'
                    ], 400);
                }

                return redirect()->back()
                    ->with('error', 'This violation cannot be appealed. It may have already been appealed or is not in an active status.');
            }

            // Create the appeal
            $appeal = ViolationAppeal::create([
                'violation_id' => $violation->id,
                'student_id' => $studentDetails->student_id,
                'student_reason' => $request->student_reason,
                'additional_evidence' => $request->additional_evidence,
                'status' => ViolationAppeal::STATUS_PENDING,
                'appeal_date' => now()
            ]);

            // Update violation status to indicate it has been appealed
            $violation->update(['status' => 'appealed']);

            // Create notification for student
            NotificationController::createNotification(
                $user->user_id,
                'Appeal Submitted Successfully',
                "Your appeal for the violation '{$violation->violationType->violation_name}' has been submitted and is pending review by administrators.",
                'info',
                $appeal->id
            );

            // Create specific notification for the educator who recorded the violation
            if ($violation->recorded_by) {
                $recordingEducator = User::where('user_id', $violation->recorded_by)->first();
                if ($recordingEducator) {
                    NotificationController::createNotification(
                        $recordingEducator->user_id,
                        'Student Appeals Your Violation Record',
                        "Student {$user->user_fname} {$user->user_lname} has appealed the violation '{$violation->violationType->violation_name}' that you recorded on " . date('M j, Y', strtotime($violation->violation_date)) . ". Reason: " . substr($request->student_reason, 0, 100) . (strlen($request->student_reason) > 100 ? '...' : '') . " Please review this appeal.",
                        'warning',
                        $appeal->id
                    );
                }
            }

            // Create notifications for other admins and educators (excluding the recording educator to avoid duplicate)
            $adminUsers = User::whereIn('user_role', ['admin', 'educator'])
                ->where('user_id', '!=', $violation->recorded_by ?? '')
                ->get();

            foreach ($adminUsers as $admin) {
                NotificationController::createNotification(
                    $admin->user_id,
                    'New Violation Appeal Submitted',
                    "Student {$user->user_fname} {$user->user_lname} has submitted an appeal for a violation recorded by " . ($recordingEducator->user_fname ?? 'Unknown') . " " . ($recordingEducator->user_lname ?? 'Educator') . ". Please review the appeal in the admin panel.",
                    'info',
                    $appeal->id
                );
            }

            Log::info('Violation appeal submitted successfully', [
                'appeal_id' => $appeal->id,
                'violation_id' => $violation->id,
                'student_id' => $studentDetails->student_id,
                'user_id' => $user->user_id
            ]);

            // Check if this is an AJAX request
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Your appeal has been submitted successfully. You will be notified when it is reviewed.',
                    'appeal_id' => $appeal->id
                ]);
            }

            return redirect()->back()
                ->with('success', 'Your appeal has been submitted successfully. You will be notified when it is reviewed.');

        } catch (\Exception $e) {
            Log::error('Error submitting violation appeal: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'violation_id' => $request->violation_id ?? null,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while submitting your appeal. Please try again later.'
                ], 500);
            }

            return redirect()->back()
                ->with('error', 'An error occurred while submitting your appeal. Please try again later.');
        }
    }

    /**
     * Get appeals for admin management (for admin/educator interface)
     */
    public function getAppealsForAdmin(Request $request)
    {
        try {
            $status = $request->get('status', 'all');
            $perPage = $request->get('per_page', 10);

            $query = ViolationAppeal::with([
                'violation',
                'violation.violationType',
                'violation.violationType.offenseCategory',
                'student',
                'studentDetails',
                'reviewer'
            ])->orderBy('appeal_date', 'desc');

            if ($status !== 'all') {
                $query->where('status', $status);
            }

            $appeals = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'appeals' => $appeals
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching appeals for admin: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appeals'
            ], 500);
        }
    }

    /**
     * Review an appeal (approve or deny)
     */
    public function reviewAppeal(Request $request, $appealId)
    {
        try {
            $validator = Validator::make($request->all(), [
                'decision' => 'required|in:approved,denied',
                'admin_response' => 'nullable|string|max:1000'
            ]);

            // Additional validation: require admin_response for denials only
            if ($request->decision === 'denied' && (!$request->admin_response || strlen(trim($request->admin_response)) < 10)) {
                return response()->json([
                    'success' => false,
                    'message' => 'A detailed reason is required when denying an appeal (at least 10 characters)'
                ], 400);
            }

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data',
                    'errors' => $validator->errors()
                ], 400);
            }

            $user = Auth::user();

            // Check if user has permission to review appeals
            if (!in_array($user->user_role, ['admin', 'educator'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to review appeals'
                ], 403);
            }

            $appeal = ViolationAppeal::with(['violation', 'violation.violationType', 'student'])
                ->findOrFail($appealId);

            if ($appeal->status !== ViolationAppeal::STATUS_PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'This appeal has already been reviewed'
                ], 400);
            }

            // Set default admin response for approvals if none provided
            $adminResponse = $request->admin_response;
            if ($request->decision === 'approved' && empty(trim($adminResponse))) {
                $adminResponse = 'Appeal approved by educator.';
            }

            // Update the appeal
            $appeal->update([
                'status' => $request->decision,
                'admin_response' => $adminResponse,
                'admin_decision_date' => now(),
                'reviewed_by' => $user->user_id
            ]);

            // Update violation status based on decision
            if ($request->decision === 'approved') {
                // When appeal is approved, automatically resolve the violation and consequence
                $appeal->violation->resolveViolationAndConsequence();
                // Additionally, set penalty to 'NONE' per business rule
                $appeal->violation->penalty = 'NONE';
                $appeal->violation->save();
            } else {
                $appeal->violation->update(['status' => 'appeal_denied']);
            }

            // Notify the student (best-effort)
            try {
                $student = $appeal->student;
                if ($student) {
                    $vioName = $appeal->violation?->violationType?->violation_name ?? 'Violation';
                    $title = $request->decision === 'approved' ? 'Appeal Approved - Violation & Consequence Resolved' : 'Appeal Denied';
                    $message = $request->decision === 'approved'
                        ? "Your appeal for the violation '{$vioName}' has been approved. The violation and its consequence have been automatically resolved and are no longer active on your record."
                        : "Your appeal for the violation '{$vioName}' has been denied. The violation remains on your record.";

                    NotificationController::createNotification(
                        $student->user_id,
                        $title,
                        $message . " Admin response: " . $adminResponse,
                        $request->decision === 'approved' ? 'success' : 'warning',
                        $appeal->id
                    );
                }
            } catch (\Throwable $ntfEx) {
                Log::warning('Failed to notify student about appeal decision', [
                    'appeal_id' => $appeal->id,
                    'error' => $ntfEx->getMessage()
                ]);
            }

            // Notify the educator who recorded the violation about the decision (best-effort)
            try {
                if ($appeal->violation?->recorded_by && $appeal->violation->recorded_by !== $user->user_id) {
                    $recordingEducator = User::where('user_id', $appeal->violation->recorded_by)->first();
                    if ($recordingEducator) {
                        $vioName = $appeal->violation?->violationType?->violation_name ?? 'Violation';
                        $student = $appeal->student;
                        $studentName = $student ? ($student->user_fname . ' ' . $student->user_lname) : 'Student';
                        $educatorTitle = $request->decision === 'approved' ? 'Violation Appeal Approved - Auto-Resolved' : 'Violation Appeal Denied';
                        $educatorMessage = $request->decision === 'approved'
                            ? "The appeal for the violation '{$vioName}' that you recorded for {$studentName} has been approved. The violation and its consequence have been automatically resolved and are no longer active."
                            : "The appeal for the violation '{$vioName}' that you recorded for {$studentName} has been denied. The violation remains active.";

                        NotificationController::createNotification(
                            $recordingEducator->user_id,
                            $educatorTitle,
                            $educatorMessage . " Decision made by: {$user->user_fname} {$user->user_lname}",
                            $request->decision === 'approved' ? 'info' : 'success',
                            $appeal->id
                        );
                    }
                }
            } catch (\Throwable $ntfEx) {
                Log::warning('Failed to notify educator about appeal decision', [
                    'appeal_id' => $appeal->id,
                    'error' => $ntfEx->getMessage()
                ]);
            }

            Log::info('Appeal reviewed successfully', [
                'appeal_id' => $appeal->id,
                'decision' => $request->decision,
                'reviewed_by' => $user->user_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appeal reviewed successfully',
                'appeal' => $appeal->fresh(['violation', 'violation.violationType', 'student', 'reviewer'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error reviewing appeal: ' . $e->getMessage(), [
                'appeal_id' => $appealId,
                'user_id' => Auth::id(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while reviewing the appeal'
            ], 500);
        }
    }

    /**
     * Get appeal statistics for dashboard
     */
    public function getAppealStats()
    {
        try {
            $stats = [
                'total' => ViolationAppeal::count(),
                'pending' => ViolationAppeal::where('status', ViolationAppeal::STATUS_PENDING)->count(),
                'approved' => ViolationAppeal::where('status', ViolationAppeal::STATUS_APPROVED)->count(),
                'denied' => ViolationAppeal::where('status', ViolationAppeal::STATUS_DENIED)->count(),
                'recent' => ViolationAppeal::where('appeal_date', '>=', now()->subDays(7))->count()
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching appeal stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch appeal statistics'
            ], 500);
        }
    }
}
