<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StudentTaskStatus;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StudentTaskStatusController extends Controller
{
    /**
     * Update task status for a student
     */
    public function updateStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'task_category' => 'required|string',
                'status' => 'required|in:not_started,in_progress,completed',
                'notes' => 'nullable|string|max:500',
                'assignment_id' => 'nullable|string'
            ]);

            $studentId = Auth::user()->user_id;
            
            // Get or create task status record
            $taskStatus = StudentTaskStatus::getOrCreate(
                $studentId,
                $validated['task_category'],
                $validated['assignment_id'] ?? null
            );
            
            // Update status with notes
            $taskStatus->updateStatus(
                $validated['status'],
                $validated['notes'] ?? null
            );

            Log::info('Task status updated', [
                'student_id' => $studentId,
                'task_category' => $validated['task_category'],
                'new_status' => $validated['status'],
                'notes' => $validated['notes'] ?? null
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Task status updated successfully',
                'status' => $taskStatus->status,
                'status_text' => $taskStatus->getStatusText(),
                'badge_class' => $taskStatus->getStatusBadgeClass(),
                'started_at' => $taskStatus->started_at?->format('M d, Y H:i'),
                'completed_at' => $taskStatus->completed_at?->format('M d, Y H:i'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating task status', [
                'error' => $e->getMessage(),
                'student_id' => Auth::user()->user_id ?? null,
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update task status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get task status for a student
     */
    public function getStatus(Request $request)
    {
        try {
            $taskCategory = $request->get('task_category');
            $studentId = Auth::user()->user_id;

            if (!$taskCategory) {
                return response()->json([
                    'success' => false,
                    'message' => 'Task category is required'
                ], 400);
            }

            $taskStatus = StudentTaskStatus::where('student_id', $studentId)
                ->where('task_category', $taskCategory)
                ->first();

            if (!$taskStatus) {
                return response()->json([
                    'success' => true,
                    'status' => 'not_started',
                    'status_text' => 'Not Started',
                    'badge_class' => 'badge bg-secondary',
                    'notes' => null,
                    'started_at' => null,
                    'completed_at' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'status' => $taskStatus->status,
                'status_text' => $taskStatus->getStatusText(),
                'badge_class' => $taskStatus->getStatusBadgeClass(),
                'notes' => $taskStatus->notes,
                'started_at' => $taskStatus->started_at?->format('M d, Y H:i'),
                'completed_at' => $taskStatus->completed_at?->format('M d, Y H:i'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting task status', [
                'error' => $e->getMessage(),
                'student_id' => Auth::user()->user_id ?? null,
                'task_category' => $taskCategory ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get task status'
            ], 500);
        }
    }

    /**
     * Get all task statuses for current student
     */
    public function getAllStatuses()
    {
        try {
            $studentId = Auth::user()->user_id;
            
            $taskStatuses = StudentTaskStatus::where('student_id', $studentId)
                ->get()
                ->keyBy('task_category');

            $formattedStatuses = [];
            foreach ($taskStatuses as $category => $status) {
                $formattedStatuses[$category] = [
                    'status' => $status->status,
                    'status_text' => $status->getStatusText(),
                    'badge_class' => $status->getStatusBadgeClass(),
                    'notes' => $status->notes,
                    'started_at' => $status->started_at?->format('M d, Y H:i'),
                    'completed_at' => $status->completed_at?->format('M d, Y H:i'),
                ];
            }

            return response()->json([
                'success' => true,
                'statuses' => $formattedStatuses
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting all task statuses', [
                'error' => $e->getMessage(),
                'student_id' => Auth::user()->user_id ?? null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get task statuses'
            ], 500);
        }
    }
}
