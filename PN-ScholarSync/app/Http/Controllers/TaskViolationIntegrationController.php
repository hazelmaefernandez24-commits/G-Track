<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TaskViolationIntegrationService;
use Illuminate\Support\Facades\Log;

class TaskViolationIntegrationController extends Controller
{
    protected $integrationService;

    public function __construct(TaskViolationIntegrationService $integrationService)
    {
        $this->integrationService = $integrationService;
    }

    /**
     * Display the integration dashboard
     */
    public function index()
    {
        try {
            // Get invalid submissions with student names
            $invalidSubmissions = $this->integrationService->getInvalidSubmissionsWithStudentNames();
            
            // Get existing violations that were created from task submissions
            $existingViolations = \App\Models\Violation::whereNotNull('task_submission_id')
                ->with(['violationType.offenseCategory'])
                ->orderBy('created_at', 'desc')
                ->get();

            return view('educator.task-violation-integration', [
                'invalidSubmissions' => $invalidSubmissions,
                'existingViolations' => $existingViolations,
                'totalInvalid' => $invalidSubmissions->count(),
                'totalSynced' => $existingViolations->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error in task violation integration index: ' . $e->getMessage());
            return view('educator.task-violation-integration', [
                'invalidSubmissions' => collect(),
                'existingViolations' => collect(),
                'totalInvalid' => 0,
                'totalSynced' => 0,
                'error' => 'Unable to load integration data: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Sync invalid task submissions to violations
     */
    public function sync(Request $request)
    {
        try {
            $result = $this->integrationService->syncInvalidTaskSubmissions();

            if ($result['success']) {
                $message = "Successfully synced {$result['synced_count']} out of {$result['total_found']} invalid submissions to violations.";
                
                if (!empty($result['errors'])) {
                    $message .= " " . count($result['errors']) . " errors occurred.";
                }

                return redirect()->route('educator.task-violation-integration')
                    ->with('success', $message);
            } else {
                return redirect()->route('educator.task-violation-integration')
                    ->with('error', 'Sync failed: ' . $result['error']);
            }

        } catch (\Exception $e) {
            Log::error('Error in sync task violations: ' . $e->getMessage());
            return redirect()->route('educator.task-violation-integration')
                ->with('error', 'Sync failed: ' . $e->getMessage());
        }
    }

    /**
     * Get invalid submissions data for AJAX
     */
    public function getInvalidSubmissions(Request $request)
    {
        try {
            $invalidSubmissions = $this->integrationService->getInvalidSubmissionsWithStudentNames();
            
            return response()->json([
                'success' => true,
                'data' => $invalidSubmissions,
                'total' => $invalidSubmissions->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting invalid submissions: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Preview what violations would be created
     */
    public function preview(Request $request)
    {
        try {
            $invalidSubmissions = $this->integrationService->getInvalidSubmissionsWithStudentNames();
            
            $preview = $invalidSubmissions->map(function($submission) {
                return [
                    'student_name' => $submission['student_name'],
                    'student_id' => $submission['student_id'],
                    'task_category' => $submission['task_category'],
                    'violation_type' => "Non-compliance with {$submission['task_category']} task assignment",
                    'severity' => 'Low',
                    'penalty' => 'VW (Verbal Warning)',
                    'consequence' => "Student must complete additional {$submission['task_category']} tasks and demonstrate proper task completion procedures.",
                    'validated_at' => $submission['validated_at']
                ];
            });

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'total' => $preview->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating preview: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
