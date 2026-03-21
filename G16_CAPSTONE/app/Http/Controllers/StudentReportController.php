<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Assignment;
use App\Models\Category;
use App\Models\Student;

class StudentReportController extends Controller
{
    public function index()
    {
        // Load reports from JSON file
        $reportsFile = storage_path('app/reports_data.json');
        $reports = [];
        if (file_exists($reportsFile)) {
            $reports = json_decode(file_get_contents($reportsFile), true) ?? [];
        }
        // Convert to collection for compatibility with Blade
        $reports = collect($reports);
        
        // Get all sub-areas (categories with parent_id) for the task category dropdown
        $taskCategories = Category::whereNotNull('parent_id')
            ->orderBy('name')
            ->get();
            
        return view('reports.index', compact('reports', 'taskCategories'));
    }

    public function getAssignmentData()
    {
        try {
            // Get current assignments with their members and categories
            $assignments = Assignment::where('status', 'current')
                ->with(['category', 'assignmentMembers.student'])
                ->get();

            $assignmentData = [];

            foreach ($assignments as $assignment) {
                $categoryName = strtolower($assignment->category->name);

                // Map category names to match your dropdown values
                $taskKey = null;

                if (str_contains($categoryName, 'kitchen')) {
                    $taskKey = 'kitchen';
                } elseif (str_contains($categoryName, 'dining')) {
                    $taskKey = 'dining';
                } elseif (str_contains($categoryName, 'dishwashing')) {
                    $taskKey = 'dishwashing';
                } elseif (str_contains($categoryName, 'ground floor')) {
                    $taskKey = 'groundfloor';
                } elseif (str_contains($categoryName, 'office') || str_contains($categoryName, 'conference')) {
                    $taskKey = 'offices';
                } elseif (str_contains($categoryName, 'garbage') || str_contains($categoryName, 'rug') || str_contains($categoryName, 'rooftop')) {
                    $taskKey = 'garbage';
                }

                if ($taskKey) {
                    $assignmentData[$taskKey] = $assignment->assignmentMembers
                        ->map(function ($member) {
                            return $member->student->name;
                        })
                        ->toArray();
                }
            }

            return response()->json([
                'success' => true,
                'assignments' => $assignmentData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching assignment data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function submitReport(Request $request)
    {
        try {
            // Validate the request
            $request->validate([
                'batch' => 'required|string',
                'task' => 'required|string',
                'performance_data' => 'required|array',
                'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120' // 5MB max
            ]);

            // Create unique report ID
            $reportId = 'report_' . time() . '_' . Str::random(8);
            
            // Create report directory
            $reportDir = 'reports/' . $reportId;
            Storage::disk('public')->makeDirectory($reportDir);

            // Handle image uploads
            $uploadedImages = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $filename = $index . '_' . time() . '.' . $image->getClientOriginalExtension();
                    $path = $image->storeAs($reportDir, $filename, 'public');
                    
                    $uploadedImages[] = [
                        'filename' => $filename,
                        'path' => $path,
                        'url' => Storage::url($path),
                        'size' => $image->getSize(),
                        'original_name' => $image->getClientOriginalName()
                    ];
                }
            }

            // Prepare report data
            $reportData = [
                'id' => $reportId,
                'batch' => $request->batch,
                'task' => $request->task,
                'performance_data' => $request->performance_data,
                'images' => $uploadedImages,
                'submitted_by' => $request->submitted_by ?? 'Anonymous',
                'submitted_at' => now()->toISOString(),
                'created_at' => now()->format('Y-m-d H:i:s')
            ];

            // Save report data to JSON file
            $reportsFile = storage_path('app/reports_data.json');
            $existingReports = [];
            
            if (file_exists($reportsFile)) {
                $existingReports = json_decode(file_get_contents($reportsFile), true) ?? [];
            }
            
            $existingReports[] = $reportData;
            file_put_contents($reportsFile, json_encode($existingReports, JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'Report submitted successfully!',
                'report_id' => $reportId,
                'images_count' => count($uploadedImages)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error submitting report: ' . $e->getMessage()
            ], 500);
        }
    }

    public function history()
    {
        // Load reports from JSON file
        $reportsFile = storage_path('app/reports_data.json');
        $reports = [];
        
        if (file_exists($reportsFile)) {
            $reports = json_decode(file_get_contents($reportsFile), true) ?? [];
        }
        
        // Sort by newest first
        usort($reports, function($a, $b) {
            return strtotime($b['submitted_at']) - strtotime($a['submitted_at']);
        });

        return view('reports.history', compact('reports'));
    }

    public function adminView()
    {
        // Load all reports for admin view
        $reportsFile = storage_path('app/reports_data.json');
        $reports = [];
        
        if (file_exists($reportsFile)) {
            $reports = json_decode(file_get_contents($reportsFile), true) ?? [];
        }

        // Group by task and batch for better organization
        $groupedReports = [];
        foreach ($reports as $report) {
            $key = $report['task'] . '_' . $report['batch'];
            if (!isset($groupedReports[$key])) {
                $groupedReports[$key] = [
                    'task' => $report['task'],
                    'batch' => $report['batch'],
                    'reports' => []
                ];
            }
            $groupedReports[$key]['reports'][] = $report;
        }

        return view('reports.admin', compact('reports', 'groupedReports'));
    }

    public function deleteReport($reportId)
    {
        try {
            // Load existing reports
            $reportsFile = storage_path('app/reports_data.json');
            $reports = [];
            
            if (file_exists($reportsFile)) {
                $reports = json_decode(file_get_contents($reportsFile), true) ?? [];
            }

            // Find and remove the report
            $reports = array_filter($reports, function($report) use ($reportId) {
                if ($report['id'] === $reportId) {
                    // Delete associated images
                    Storage::disk('public')->deleteDirectory('reports/' . $reportId);
                    return false;
                }
                return true;
            });

            // Save updated reports
            file_put_contents($reportsFile, json_encode(array_values($reports), JSON_PRETTY_PRINT));

            return response()->json([
                'success' => true,
                'message' => 'Report deleted successfully!'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting report: ' . $e->getMessage()
            ], 500);
        }
    }
}