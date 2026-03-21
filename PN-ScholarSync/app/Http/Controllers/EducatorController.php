<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\OffenseCategory;
use App\Models\SeverityMaxCount;
use App\Traits\ViolationCountingTrait;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Exception;
use App\Events\ManualUpdated;

/**
 * EducatorController
 * Handles all educator-related functionality including violation management,
 * student tracking, and dashboard statistics.
 */
class EducatorController extends Controller
{
    use ViolationCountingTrait;

    /**
     * Show a student's profile by student_id
     */
    public function showStudentProfile($student_id)
    {
        // First try to find by student_id in student_details table
        $studentDetails = \App\Models\StudentDetails::where('student_id', $student_id)->first();
        if (!$studentDetails) {
            abort(404, 'Student not found');
        }

    

    

        $student = $studentDetails->user;
        if (!$student) {
            abort(404, 'Student user not found');
        }

        $violations = \App\Models\Violation::where('student_id', $student_id)
            ->with(['violationType', 'latestAppeal'])
            ->orderByDesc('violation_date')
            ->get();

        // Calculate student status based on violations that should count for penalty
        $activeViolations = $violations->filter(function($violation) {
            return $violation->shouldCountForPenalty();
        });

        $studentStatus = 'Good Standing';
        if ($activeViolations->isNotEmpty()) {
            $penaltyPriority = ['Exp', 'T', 'Pro', 'P', 'WW', 'W', 'VW', 'V'];
            foreach ($penaltyPriority as $code) {
                if ($activeViolations->contains('penalty', $code)) {
                    $studentStatus = match($code) {
                        'Exp', 'T' => 'Termination of Contract',
                        'Pro', 'P' => 'Probationary of Contract',
                        'WW', 'W' => 'Written Warning',
                        'VW', 'V' => 'Verbal Warning',
                    };
                    break;
                }
            }
        }

        return view('educator.student_violation_history', compact('student', 'violations', 'studentDetails', 'studentStatus'));
    }

    // =============================================
    // DASHBOARD METHODS
    // =============================================

    /**
     * Show all students page
     */
    public function studentsPage()
    {
        $students = \App\Models\User::where('user_role', 'student')->with('studentDetails')->get();
        // Extract unique batches from student IDs (first 4 characters)
        $batches = \App\Models\User::where('user_role', 'student')
            ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
            ->selectRaw('LEFT(student_details.student_id, 4) as batch_year')
            ->distinct()
            ->orderBy('batch_year', 'desc')
            ->get()
            ->map(function($batch) {
                return (object) [
                    'id' => $batch->batch_year,
                    'name' => 'Class ' . $batch->batch_year
                ];
            });
        return view('educator.students', compact('students', 'batches'));
    }

    /**
     * Show all active violation cases
     */
    public function activeViolations(Request $request)
    {
        $query = \App\Models\Violation::with(['student', 'violationType'])
            ->where('status', 'active');

        // Search by student name (first/last) - no 'name' column exists
        if ($request->filled('name')) {
            $name = $request->name;
            $query->whereHas('student', function ($q) use ($name) {
                $q->where('user_fname', 'like', '%' . $name . '%')
                  ->orWhere('user_lname', 'like', '%' . $name . '%');
            });
        }
        // Filter by batch
        if ($request->filled('batch') && $request->batch !== 'all') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_id', 'like', $request->batch . '%');
            });
        }
        $violations = $query->orderByDesc('violation_date')->get();
        // Get available batches for filter dropdown
        $batches = \App\Models\User::where('user_role', 'student')
            ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
            ->selectRaw('LEFT(student_details.student_id, 4) as batch')
            ->distinct()
            ->pluck('batch');
        return view('educator.activeViolations', [
            'violations' => $violations,
            'batches' => $batches,
            'currentBatch' => $request->batch ?? 'all',
            'currentName' => $request->name ?? ''
        ]);
    }

    /**
     * Show all resolved violation cases
     */
    public function resolvedViolations(Request $request)
    {
        $query = \App\Models\Violation::with(['student', 'violationType'])
            ->where('status', 'resolved');

        // Search by student name (first/last) - no 'name' column exists
        if ($request->filled('name')) {
            $name = $request->name;
            $query->whereHas('student', function ($q) use ($name) {
                $q->where('user_fname', 'like', '%' . $name . '%')
                  ->orWhere('user_lname', 'like', '%' . $name . '%');
            });
        }
        // Filter by batch
        if ($request->filled('batch') && $request->batch !== 'all') {
            $query->whereHas('student', function ($q) use ($request) {
                $q->where('student_id', 'like', $request->batch . '%');
            });
        }
        $violations = $query->orderByDesc('violation_date')->get();
        // Get available batches for filter dropdown
        $batches = \App\Models\User::where('user_role', 'student')
            ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
            ->selectRaw('LEFT(student_details.student_id, 4) as batch')
            ->distinct()
            ->pluck('batch');
        return view('educator.resolvedViolations', [
            'violations' => $violations,
            'batches' => $batches,
            'currentBatch' => $request->batch ?? 'all',
            'currentName' => $request->name ?? ''
        ]);
    }
    
    /**
     * Get students count by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentsByBatch(Request $request)
    {
        $batch = $request->query('batch', 'all');

        try {
            if ($batch === 'all') {
                $count = User::where('user_role', 'student')->count();
            } else {
                // Filter based on the student_id prefix (e.g., 2025% for Class 2025)
                $count = User::where('user_role', 'student')
                    ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                    ->where('student_details.student_id', 'like', $batch . '%')
                    ->count();
            }

            return response()->json([
                'success' => true,
                'count' => $count,
                'batch' => $batch
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getStudentsByBatch: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching students by batch: ' . $e->getMessage(),
                'count' => 0
            ]);
        }
    }
    
    /**
     * Get violations count by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationsCount(Request $request)
    {
        $batch = $request->query('batch', 'all');
        return response()->json($this->getViolationCountResponse($batch, 'active'));
    }

    /**
     * Get total violations count by batch (all statuses)
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalViolationsCount(Request $request)
    {
        $batch = $request->query('batch', 'all');
        return response()->json($this->getViolationCountResponse($batch));
    }

    /**
     * Get resolved violations count by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getResolvedViolationsCount(Request $request)
    {
        $batch = $request->query('batch', 'all');
        return response()->json($this->getViolationCountResponse($batch, 'resolved'));
    }
    
    /**
     * Get available batches dynamically from the database
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableBatches()
    {
        try {
            // Collect possible class years from multiple sources
            $years = collect();

            // 1) Explicit batch column if present on student_details
            if (\Schema::hasColumn('student_details', 'batch')) {
                $explicit = \DB::table('student_details')
                    ->whereNotNull('batch')
                    ->pluck('batch')
                    ->filter(fn($b) => preg_match('/^\d{4}$/', (string)$b));
                $years = $years->merge($explicit);
            }

            // 2) Derive from student_details.student_id prefix
            $studentIdYears = \DB::table('student_details')
                ->whereNotNull('student_id')
                ->pluck('student_id')
                ->map(function ($sid) {
                    if (preg_match('/^(\d{4})/', (string)$sid, $m)) { return $m[1]; }
                    return null;
                })
                ->filter();
            $years = $years->merge($studentIdYears);

            // 3) Derive from violations.student_id prefix as fallback
            $violationYears = \DB::table('violations')
                ->whereNotNull('student_id')
                ->pluck('student_id')
                ->map(function ($sid) {
                    if (preg_match('/^(\d{4})/', (string)$sid, $m)) { return $m[1]; }
                    return null;
                })
                ->filter();
            $years = $years->merge($violationYears);

            // Normalize: unique and sort descending
            $years = $years->unique()->sortDesc()->values();

            // Build response array (no counts to keep it fast)
            $batches = $years->map(fn($y) => [ 'value' => (string)$y, 'label' => 'Class ' . (string)$y ]);

            // Always include the "All" option first
            $allBatches = collect([ [ 'value' => 'all', 'label' => 'All Classes' ] ])->concat($batches);

            return response()->json([
                'success' => true,
                'batches' => $allBatches
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in getAvailableBatches: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching available batches: ' . $e->getMessage(),
                'batches' => [ [ 'value' => 'all', 'label' => 'All Classes' ] ]
            ]);
        }
    }

    /**
     * Get students compliance status (violators and non-violators) by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentComplianceByBatch(Request $request)
    {
        try {
            $batch = $request->query('batch', 'all');
            
            // Get all students using proper table joins
            $studentsQuery = DB::table('pnph_users')
                ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                ->where('pnph_users.user_role', 'student');

            // Apply batch filter
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 2025% for Class 2025)
                $studentsQuery->where('student_details.student_id', 'like', $batch . '%');
            }
            
            // Get non-compliant students (with violations)
            $nonCompliantQuery = clone $studentsQuery;
            $nonCompliant = $nonCompliantQuery
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('violations')
                          ->whereRaw('violations.student_id = student_details.student_id')
                          ->where('violations.status', 'active');
                })
                ->select('pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.student_id', DB::raw('(SELECT COUNT(*) FROM violations WHERE violations.student_id = student_details.student_id AND violations.status = "active") as violations_count'))
                ->orderBy('violations_count', 'desc')
                ->limit(10)
                ->get();
            
            // Get compliant students (without violations)
            $compliantQuery = clone $studentsQuery;
            $compliant = $compliantQuery
                ->whereNotExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('violations')
                          ->whereRaw('violations.student_id = student_details.student_id')
                          ->where('violations.status', 'active');
                })
                ->select('pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.student_id')
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'nonCompliant' => $nonCompliant,
                'compliant' => $compliant,
                'batch' => $batch
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching student compliance data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student compliance data: ' . $e->getMessage(),
                'nonCompliant' => [],
                'compliant' => []
            ], 500);
        }
    }

    /**
     * Display the educator dashboard
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function dashboard(Request $request)
    {
        try {
            // Get total students - temporarily bypass role check
            $totalStudents = User::where('user_role', 'student')->count();

            // Get total violations
            $totalViolations = Violation::count();

            // Get top violators (students with most violations)
            try {
                // First, check if the violations table exists
                if (!Schema::hasTable('violations')) {
                    throw new \Exception('Violations table does not exist');
                }

                // Get all violations
                $allViolations = DB::table('violations')->get();

                // Log the total violations found
                \Log::info('Total violations found: ' . $allViolations->count());

                // Group violations by student_id and count them
                $violationsPerStudent = $allViolations->groupBy('student_id')
                    ->map(function ($group) {
                        return count($group);
                    })
                    ->sortDesc()
                    ->take(5);

                // Log the violations per student
                \Log::info('Violations per student: ' . json_encode($violationsPerStudent));

                // Get all student IDs with violations
                $studentIds = $violationsPerStudent->keys()->toArray();

                // Get all users with these student IDs
                $users = DB::table('pnph_users')
                    ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                    ->whereIn('student_details.student_id', $studentIds)
                    ->select('pnph_users.*', 'student_details.student_id')
                    ->get();

                // Log the users found
                \Log::info('Users found: ' . $users->count());

                // Create a lookup array for quick access to user data
                $userLookup = [];
                foreach ($users as $user) {
                    $userLookup[$user->student_id] = $user;
                }

                // Create the final top violators collection
                $topViolators = collect();
                foreach ($violationsPerStudent as $studentId => $count) {
                    $user = $userLookup[$studentId] ?? null;

                    if ($user) {
                        $fullName = trim($user->user_fname . ' ' . $user->user_lname);
                        \Log::info("Adding violator: {$fullName} with {$count} violations");
                        $topViolators->push((object) [
                            'id' => $user->user_id,
                            'name' => $fullName,
                            'student_id' => $studentId,
                            'violations_count' => $count
                        ]);
                    } else {
                        \Log::warning("No user found for student_id: {$studentId}");
                        // Add a placeholder for missing users
                        $topViolators->push((object) [
                            'id' => 0,
                            'name' => "Student {$studentId}",
                            'student_id' => $studentId,
                            'violations_count' => $count
                        ]);
                    }
                }

                // Log the final top violators collection
                \Log::info('Final top violators: ' . json_encode($topViolators));

                // If no violators found, log this information
                if ($topViolators->isEmpty()) {
                    \Log::info('No violators found in the database');
                    // Keep it as an empty collection, don't convert to array
                    // This ensures consistent type handling in the view
                }

                // Log for debugging
                \Log::info('Top violators query result: ' . $topViolators->count() . ' records found with violations');
                foreach ($topViolators as $violator) {
                    \Log::info("Violator: {$violator->name} ({$violator->student_id}) - {$violator->violations_count} violations");
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching top violators: ' . $e->getMessage());
                // Initialize with an empty collection on error to be consistent
                $topViolators = collect();
            }

            // Get recent violations - safely handling potential missing tables
            try {
                // Check if violation_types table exists
                $hasViolationTypesTable = Schema::hasTable('violation_types');

                if ($hasViolationTypesTable) {
                    $recentViolations = Violation::with(['student'])
                        ->select(
                            'violations.*',
                            'pnph_users.user_fname as student_name'
                        )
                        ->join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->orderBy('violations.created_at', 'desc')
                        ->take(5)
                        ->get();
                } else {
                    // Simplified query without violation_types
                    $recentViolations = Violation::select(
                            'violations.*',
                            'pnph_users.user_fname as student_name'
                        )
                        ->join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->orderBy('violations.created_at', 'desc')
                        ->take(5)
                        ->get();
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching recent violations: ' . $e->getMessage());
                $recentViolations = collect([]);
            }

            // Get violations by month
            $violationsByMonth = DB::table('violations')
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('COUNT(*) as count'))
                ->whereYear('created_at', date('Y'))
                ->groupBy('month')
                ->orderBy('month')
                ->get();

            // Get violations by type - safely handling potential missing tables
            try {
                // Check if violation_types table exists
                $hasViolationTypesTable = Schema::hasTable('violation_types');

                if ($hasViolationTypesTable) {
                    $violationsByType = DB::table('violations')
                        ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                        ->select('violation_types.violation_name as name', DB::raw('COUNT(violations.id) as count'))
                        ->groupBy('violation_types.id', 'violation_types.violation_name')
                        ->orderBy('count', 'desc')
                        ->take(5)
                        ->get();
                } else {
                    // Group by violation_type_id instead if table doesn't exist
                    $violationsByType = DB::table('violations')
                        ->select('violation_type_id as name', DB::raw('COUNT(id) as count'))
                        ->groupBy('violation_type_id')
                        ->orderBy('count', 'desc')
                        ->take(5)
                        ->get();
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching violations by type: ' . $e->getMessage());
                $violationsByType = collect([]);
            }

            // Get count of students with violations
            $violatorCount = User::where('user_role', 'student')
                ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('violations')
                          ->whereRaw('violations.student_id = student_details.student_id');
                })
                ->count();

            $nonViolatorCount = $totalStudents - $violatorCount;

            // Get the period parameter from the URL or default to 'month'
            $period = $request->input('period', 'month');

            // Get violation statistics based on the period
            try {
                $violationStats = $this->getViolationStatsByPeriod($period);
            } catch (Exception $e) {
                Log::error('Error getting violation stats: ' . $e->getMessage());
                $violationStats = [];
            }

            // Get available batches dynamically
            $availableBatches = DB::table('pnph_users')
                ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                ->where('pnph_users.user_role', 'student')
                ->selectRaw('LEFT(student_details.student_id, 4) as batch_year')
                ->distinct()
                ->orderBy('batch_year')
                ->pluck('batch_year')
                ->filter()
                ->values();

            // Get notification count for current user
            $unreadCount = 0;
            if (Auth::check()) {
                try {
                    $unreadCount = Notification::where('user_id', Auth::id())
                        ->where('is_read', false)
                        ->count();
                } catch (\Exception $e) {
                    $unreadCount = 0;
                }
            }

            return view('educator.dashboard', [
                'topViolators' => $topViolators,
                'totalViolations' => $totalViolations,
                'totalStudents' => $totalStudents,
                'recentViolations' => $recentViolations,
                'violationsByMonth' => $violationsByMonth,
                'violationsByType' => $violationsByType,
                'violatorCount' => $violatorCount,
                'nonViolatorCount' => $nonViolatorCount,
                'violationStats' => $violationStats,
                'availableBatches' => $availableBatches,
                'unreadCount' => $unreadCount
            ]);
        } catch (Exception $e) {
            Log::error('Error in dashboard: ' . $e->getMessage());

            // Get notification count even in error case
            $unreadCount = 0;
            if (Auth::check()) {
                try {
                    $unreadCount = Notification::where('user_id', Auth::id())
                        ->where('is_read', false)
                        ->count();
                } catch (\Exception $e) {
                    $unreadCount = 0;
                }
            }

            return view('educator.dashboard', [
                'topViolators' => collect(),
                'totalViolations' => 0,
                'totalStudents' => 0,
                'recentViolations' => [],
                'violationsByMonth' => [],
                'violationsByType' => [],
                'violatorCount' => 0,
                'nonViolatorCount' => 0,
                'violationStats' => [],
                'availableBatches' => collect(),
                'unreadCount' => $unreadCount,
                'error' => 'Unable to load dashboard data'
            ]);
        }
    }

    // Dashboard helper methods

    /**
     * Get violation statistics by period
     *
     * @param string $period The period to get statistics for (month, last_month, last_3_months)
     * @return \Illuminate\Support\Collection Violation statistics
     */
    private function getViolationStatsByPeriod($period = 'month')
    {
        Log::info('Violation stats query', ['period' => $period]);

        // Determine date range based on period
        $dateRange = $this->getDateRangeForPeriod($period);

        try {
            // Check if violation_types table exists
            if (Schema::hasTable('violation_types')) {
                // Join with violation_types table to get names (exclude approved appeals)
                $stats = DB::table('violations')
                    ->select(
                        'violations.violation_type_id',
                        'violation_types.violation_name as violation_name',
                        DB::raw('COUNT(*) as count')
                    )
                    ->leftJoin('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                    ->where('violations.status', '!=', 'appeal_approved') // Exclude approved appeals
                    ->whereBetween('violations.violation_date', [$dateRange['start'], $dateRange['end']])
                    ->groupBy('violations.violation_type_id', 'violation_types.violation_name')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get();

                // If no data found, return empty collection
                if ($stats->isEmpty()) {
                    Log::info('No violation stats found for period: ' . $period);
                    return collect([]);
                }

                // Ensure each item has a violation_name
                $stats = $stats->map(function($item) {
                    if (empty($item->violation_name)) {
                        $item->violation_name = 'Type ' . $item->violation_type_id;
                    }
                    return $item;
                });
            } else {
                // Group by violation_type_id only if violation_types table doesn't exist (exclude approved appeals)
                $stats = DB::table('violations')
                    ->select('violation_type_id', DB::raw('COUNT(*) as count'))
                    ->where('status', '!=', 'appeal_approved') // Exclude approved appeals
                    ->whereBetween('violation_date', [$dateRange['start'], $dateRange['end']])
                    ->groupBy('violation_type_id')
                    ->orderBy('count', 'desc')
                    ->limit(5)
                    ->get()
                    ->map(function($item) {
                        $item->violation_name = 'Type ' . $item->violation_type_id;
                        return $item;
                    });
            }

            Log::info('Violation stats query results: ' . $stats->count() . ' records');
            return $stats;

        } catch (\Exception $e) {
            Log::error('Error in violation stats query: ' . $e->getMessage());
            // Return empty collection on error
            return collect([]);
        }
    }

    /**
     * Get date range for a given period
     *
     * @param string $period The period (month, last_month, last_3_months)
     * @return array Start and end dates
     */
    private function getDateRangeForPeriod($period)
    {
        $now = Carbon::now();

        switch ($period) {
            case 'last_month':
                $start = $now->copy()->subMonth()->startOfMonth()->format('Y-m-d');
                $end = $now->copy()->subMonth()->endOfMonth()->format('Y-m-d');
                break;

            case 'last_3_months':
                $start = $now->copy()->subMonths(3)->startOfMonth()->format('Y-m-d');
                $end = $now->copy()->endOfMonth()->format('Y-m-d');
                break;

            case 'month':
            default:
                $start = $now->copy()->startOfMonth()->format('Y-m-d');
                $end = $now->copy()->endOfMonth()->format('Y-m-d');
                break;
        }

        return ['start' => $start, 'end' => $end];
    }

    // =============================================
    // VIOLATION MANAGEMENT METHODS
    // =============================================

    /**
     * View a specific violation
     * @param int $id The violation ID
     * @return \Illuminate\View\View
     */
    public function viewViolation($id)
    {
        try {
            $violation = Violation::with(['student', 'violationType', 'violationType.offenseCategory'])
                ->findOrFail($id);

            return view('educator.viewViolation', [
                'violation' => $violation
            ]);
        } catch (Exception $e) {
            Log::error('Error viewing violation: ' . $e->getMessage());
            return redirect()->route('educator.violation')
                ->with('error', 'Unable to find the requested violation record.');
        }
    }

    /**
     * View a G16 invalid violation by submission_id.
     */
    public function viewInvalidViolation($submission_id)
    {
        try {
            // 1) Try from local invalid_violations table first
            $iv = \DB::table('invalid_violations')
                ->where('task_submission_id', $submission_id)
                ->first();

            if ($iv) {
                // Hydrate a lightweight stdClass compatible with viewViolation fields
                $violation = (object) [
                    'id' => null,
                    'student_id' => $iv->student_id,
                    'student' => $this->getUserByStudentId($iv->student_id),
                    'violation_date' => $iv->violation_date,
                    'violationType' => (object) [
                        'violation_name' => 'Invalid task submission',
                        'offenseCategory' => (object) ['category_name' => $iv->incident_place ?? 'Center Tasking']
                    ],
                    // Provide root-level offenseCategory to match viewViolation.blade expectations
                    'offenseCategory' => (object) ['category_name' => $iv->incident_place ?? 'Center Tasking'],
                    // Provide offense field expected by the blade (not applicable to invalids)
                    'offense' => null,
                    'severity' => $iv->severity ?? 'Low',
                    'penalty' => $iv->penalty ?? 'VW',
                    'consequence' => $iv->consequence,
                    'consequence_duration_value' => null,
                    'consequence_duration_unit' => null,
                    'consequence_status' => $iv->consequence_status ?? 'active',
                    'incident_datetime' => $iv->incident_datetime,
                    'incident_place' => $iv->incident_place,
                    'incident_details' => $iv->incident_details,
                    'prepared_by' => $iv->prepared_by ?? 'G16 Bridge',
                    'status' => $iv->status ?? 'active',
                ];

                return view('educator.viewViolation', [ 'violation' => $violation ]);
            }

            // 2) Fallback: fetch directly from G16_CAPSTONE by submission id
            $g16 = \DB::connection('g16_capstone')
                ->table('task_submissions as ts')
                ->join('pnph_users as u', 'ts.user_id', '=', 'u.user_id')
                ->leftJoin('student_details as sd', 'ts.user_id', '=', 'sd.user_id')
                ->where('ts.id', $submission_id)
                ->select(
                    'ts.id as submission_id',
                    'ts.task_category',
                    'ts.description',
                    'ts.validated_at',
                    'ts.admin_notes',
                    'u.user_fname',
                    'u.user_lname',
                    'u.gender',
                    'sd.student_id'
                )->first();

            if (!$g16) {
                return redirect()->route('educator.violation')
                    ->with('error', 'Invalid submission not found.');
            }

            $student = $this->getUserByStudentId($g16->student_id);
            $violation = (object) [
                'id' => null,
                'student_id' => $g16->student_id,
                'student' => $student,
                'violation_date' => $g16->validated_at ?: now(),
                'violationType' => (object) [
                    'violation_name' => 'Invalid task submission',
                    'offenseCategory' => (object) ['category_name' => $g16->task_category ?? 'Center Tasking']
                ],
                // Provide root-level offenseCategory to match viewViolation.blade expectations
                'offenseCategory' => (object) ['category_name' => $g16->task_category ?? 'Center Tasking'],
                // Provide offense field expected by the blade (not applicable to invalids)
                'offense' => null,
                'severity' => 'Low',
                'penalty' => 'VW',
                'consequence' => 'Invalid task submission for ' . ($g16->task_category ?? 'task'),
                'consequence_duration_value' => null,
                'consequence_duration_unit' => null,
                'consequence_status' => 'active',
                'incident_datetime' => $g16->validated_at ?: now(),
                'incident_place' => $g16->task_category,
                'incident_details' => $g16->admin_notes ?: $g16->description,
                'prepared_by' => 'G16 Bridge',
                'status' => 'active',
            ];

            return view('educator.viewViolation', [ 'violation' => $violation ]);

        } catch (\Exception $e) {
            \Log::error('Error viewing invalid violation: ' . $e->getMessage());
            return redirect()->route('educator.violation')
                ->with('error', 'Unable to open the invalid violation record.');
        }
    }

    /**
     * View an x_status-based violation detail page
     */
    public function viewXStatusViolation($id)
    {
        try {
            // Normalize id (accept 'x_status_12' or '12')
            $rawId = (string)$id;
            if (!ctype_digit($rawId)) {
                $digits = preg_replace('/\D+/', '', $rawId);
                $id = $digits !== '' ? (int)$digits : 0;
            } else {
                $id = (int)$rawId;
            }

            if ($id <= 0) {
                \Log::warning('viewXStatusViolation: invalid id parameter', ['input' => $rawId]);
                return redirect()->route('educator.violation')
                    ->with('error', 'Record not found for the selected x_status entry.');
            }
            // Get base x_status row (same table used by the list)
            $row = \DB::table('x_status')->where('id', $id)->first();

            // Fallback: try the same joined query shape used by the list if base fetch failed
            if (!$row) {
                $joined = \DB::table('x_status as xs')
                    ->leftJoin('student_details as sd', function($j){
                        $j->on('sd.student_id', '=', 'xs.student_id');
                    })
                    ->leftJoin('pnph_users as u', function($j){
                        $j->on('u.user_id', '=', 'sd.user_id')
                          ->orOn('u.user_id', '=', 'xs.user_id');
                    })
                    ->where('xs.id', $id)
                    ->select('xs.*', \DB::raw('COALESCE(sd.student_id, xs.student_id) as student_id'), 'u.user_fname', 'u.user_lname', 'u.gender')
                    ->first();
                if ($joined) {
                    // Use the joined row as our base object
                    $row = (object) [
                        'id' => $joined->id,
                        'student_id' => $joined->student_id ?? null,
                        'user_id' => null, // may be null, we will still try to enrich below
                        'task_area' => $joined->task_area ?? null,
                        'task_description' => $joined->task_description ?? null,
                        'task_status' => $joined->task_status ?? null,
                        'day' => $joined->day ?? null,
                        'created_at' => $joined->created_at ?? null,
                        'assigned_to' => $joined->assigned_to ?? null,
                    ];
                }
            }

            // If still missing, render a minimal placeholder page instead of error redirect
            if (!$row) {
                $violation = (object) [
                    'id' => null,
                    'student_id' => 'N/A',
                    'student' => (object) [ 'user_fname' => '', 'user_lname' => '', 'gender' => '' ],
                    'violation_date' => now(),
                    'violationType' => (object) [ 'violation_name' => 'Room Rules Violation', 'offenseCategory' => (object)['category_name' => 'Room Rules'] ],
                    'offenseCategory' => (object) ['category_name' => 'Room Rules'],
                    'offense' => 'Room Rules Non-Compliance',
                    'severity' => 'Low',
                    'penalty' => 'WW',
                    'consequence' => 'N/A',
                    'incident_datetime' => null,
                    'incident_place' => 'Center',
                    'incident_details' => null,
                    'prepared_by' => 'Task History Bridge',
                    'status' => 'active',
                    'consequence_status' => 'pending',
                ];
                \Log::warning('viewXStatusViolation: x_status not found, rendering placeholder', ['id' => $id]);
                return view('educator.viewViolation', [ 'violation' => $violation ]);
            }
            // Enrich student and user defensively (no joins to avoid SQL edge cases)
            $studentCode = $row->student_id ?? null;
            $sd = null;
            if ($studentCode) {
                $sd = \DB::table('student_details')->where('student_id', $studentCode)->first();
            }
            $user = null;
            $userId = ($sd && isset($sd->user_id)) ? $sd->user_id : ($row->user_id ?? null);
            if ($userId) {
                $user = \DB::table('pnph_users')->where('user_id', $userId)->first();
            }

            $rule = \App\Http\Controllers\ViolationController::mapRoomRuleFromXStatus($row);
            $studentIdOut = $studentCode ?: ($sd ? ($sd->student_id ?? null) : null) ?: 'N/A';
            $userFname = $user ? ($user->user_fname ?? '') : '';
            $userLname = $user ? ($user->user_lname ?? '') : '';
            $userGender = $user ? ($user->gender ?? '') : '';
            $preparedByOut = trim(($userFname.' '.$userLname)) ?: ($row->assigned_to ?? 'Task History Bridge');
            $violation = (object) [
                'id' => null,
                'student_id' => $studentIdOut,
                'student' => (object) [
                    'user_fname' => $userFname,
                    'user_lname' => $userLname,
                    'gender' => $userGender,
                ],
                'violation_date' => (!empty($row->day) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$row->day)) ? $row->day : ($row->created_at ?: now()),
                'violationType' => (object) [
                    'violation_name' => $rule['name'],
                    'offenseCategory' => (object) ['category_name' => 'Room Rules']
                ],
                'offenseCategory' => (object) ['category_name' => 'Room Rules'],
                'offense' => 'Room Rules Non-Compliance',
                'severity' => $rule['severity'],
                'penalty' => 'WW',
                'consequence' => 'N/A',
                'incident_datetime' => $row->created_at,
                'incident_place' => $row->task_area ?? 'Center',
                'incident_details' => trim(implode(' | ', array_filter([
                    $row->task_area ? ('Area: '.$row->task_area) : null,
                    $row->task_status ? ('Status: '.$row->task_status) : null,
                    $row->assigned_to ? ('Assigned To: '.$row->assigned_to) : null,
                ]))) ?: null,
                'prepared_by' => 'N/A',
                'status' => 'pending',
                'consequence_status' => 'pending',
                'is_x_status' => true,
            ];

            return view('educator.viewViolation', [ 'violation' => $violation ]);
        } catch (\Exception $e) {
            \Log::error('Error viewing x_status violation', [
                'error' => $e->getMessage(),
                'id' => $id ?? null
            ]);
            // Fail-safe: render a minimal placeholder instead of redirecting with an error
            $violation = (object) [
                'id' => null,
                'student_id' => 'N/A',
                'student' => (object) [ 'user_fname' => '', 'user_lname' => '', 'gender' => '' ],
                'violation_date' => now(),
                'violationType' => (object) [ 'violation_name' => 'Room Rules Violation', 'offenseCategory' => (object)['category_name' => 'Room Rules'] ],
                'offenseCategory' => (object) ['category_name' => 'Room Rules'],
                'offense' => 'Room Rules Non-Compliance',
                'severity' => 'Low',
                'penalty' => 'WW',
                'consequence' => 'N/A',
                'incident_datetime' => null,
                'incident_place' => 'Center',
                'incident_details' => null,
                'prepared_by' => 'N/A',
                'status' => 'pending',
                'consequence_status' => 'pending',
                'is_x_status' => true,
            ];
            return view('educator.viewViolation', [ 'violation' => $violation ]);
        }
    }

    /**
     * Helper: try to get user model minimal fields by student_id
     */
    private function getUserByStudentId($studentId)
    {
        if (!$studentId) return null;
        try {
            $sd = \App\Models\StudentDetails::where('student_id', $studentId)->with('user')->first();
            return $sd?->user;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Show the form for creating a new violation type
     */
    public function showViolationTypeForm()
    {
        $categories = OffenseCategory::all();
        return view('educator.newViolation', [
            'categories' => $categories
        ]);
    }

    /**
     * Create a new violation type
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createViolationType(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string',
                'category_id' => 'required|exists:offense_categories,id',
                'penalty' => 'required|string|max:50',
                'severity' => 'required|string|exists:severities,severity_name'
            ]);

            $violationType = new ViolationType();
            $violationType->name = $validated['name'];
            $violationType->description = $validated['description'];
            $violationType->offense_category_id = $validated['category_id'];
            $violationType->penalty = $validated['penalty'];
            $violationType->severity = $validated['severity'];
            $violationType->save();

            return response()->json([
                'success' => true,
                'message' => 'Violation type created successfully',
                'data' => $violationType
            ]);
        } catch (Exception $e) {
            Log::error('Error creating violation type: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create violation type: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Filter and display students by penalty type
     *
     * @param string $penalty The penalty code (VW, WW, Pro, T)
     * @return \Illuminate\View\View
     */
    public function studentsByPenalty(Request $request, $penalty)
    {
        try {
            // Validate the penalty type - updated to use new penalty codes
            $validPenalties = ['VW', 'WW', 'Pro', 'T'];
            if (!in_array($penalty, $validPenalties)) {
                return redirect()->route('educator.violation')
                    ->with('error', 'Invalid penalty type specified.');
            }

            $batch = $request->query('batch', 'all');
            // Use the exact penalty code since we've standardized them
            $penaltyValues = [$penalty];
            
            $violationsQuery = Violation::with(['student', 'violationType'])
                ->whereIn('penalty', $penaltyValues)
                // Only show violations that count toward current penalty cards
                ->where('action_taken', true)
                // Exclude violations that have been appeal-approved
                ->where(function($q){
                    $q->where('status', '!=', 'appeal_approved')
                      ->orWhereNull('status');
                })
                ->whereNotExists(function($sub){
                    $sub->select(\DB::raw(1))
                        ->from('violation_appeals')
                        ->whereColumn('violation_appeals.violation_id', 'violations.id')
                        ->where('violation_appeals.status', 'approved');
                })
                // Extra safety: exclude explicit NONE penalties
                ->where(function($q){
                    $q->whereNull('penalty')->orWhere('penalty', '!=', 'NONE');
                });
            if ($batch !== 'all') {
                $violationsQuery->whereHas('student', function($query) use ($batch) {
                    $query->where('student_id', 'like', $batch . '%');
                });
            }
            $violations = $violationsQuery->orderBy('violation_date', 'desc')->get();

            return view('educator.studentsByPenalty', compact('violations', 'penalty', 'batch'));

        } catch (\Exception $e) {
            Log::error('Error in studentsByPenalty: ' . $e->getMessage());
            return redirect()->route('educator.dashboard')
                ->with('error', 'Unable to load students by penalty.');
        }
    }

    /**
     * Display the behavior monitoring page
     */
    public function behavior()
    {
        // Get total students count from the database
        $totalStudents = User::where('user_role', 'student')->count();

        // Get count of active violation cases
        // Count all violations regardless of status (active, resolved, etc.)
        $activeViolationCases = \App\Models\Violation::where('status', 'active')->count();

        // Get pending appeals that need review
        $pendingAppeals = \App\Models\ViolationAppeal::with([
            'violation',
            'violation.violationType',
            'studentDetails',
            'student'
        ])
        ->where('status', \App\Models\ViolationAppeal::STATUS_PENDING)
        ->orderBy('appeal_date', 'desc')
        ->get();

        return view('educator.behavior', [
            'totalStudents' => $totalStudents,
            'activeViolationCases' => $activeViolationCases,
            'pendingAppeals' => $pendingAppeals
        ]);
    }

    /**
     * Display the violation report page
     */
    public function violationReportPage()
    {
        return view('educator.violation-report');
    }

    /**
     * Get violation report data
     */
    public function getViolationReportData(Request $request)
    {
        try {
            // Get filter parameters
            $year = $request->input('year', date('Y'));
            $month = $request->input('month', 'all');
            $batch = $request->input('batch', 'all');

            // Base query for violations with student and violation type information
            $query = \App\Models\Violation::with(['violationType', 'studentDetails.user'])
                ->join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->leftJoin('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->select(
                    'violations.*',
                    'pnph_users.user_fname',
                    'pnph_users.user_lname',
                    'student_details.student_id as student_number',
                    'violation_types.violation_name'
                );

            // Apply year filter
            if ($year && $year !== 'all') {
                $query->whereYear('violations.violation_date', $year);
            }

            // Apply month filter
            if ($month && $month !== 'all') {
                $query->whereMonth('violations.violation_date', intval($month) + 1);
            }

            // Apply batch filter
            if ($batch && $batch !== 'all') {
                $query->where('student_details.student_id', 'like', $batch . '%');
            }

            // Get the violations ordered by date (newest first)
            $violations = $query->orderBy('violations.violation_date', 'desc')
                               ->orderBy('violations.created_at', 'desc')
                               ->get();

            // Format the data for the report
            $reportData = $violations->map(function ($violation) {
                return [
                    'id' => $violation->id,
                    'date' => $violation->violation_date ? \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') : 'N/A',
                    'student' => trim(($violation->user_fname ?? '') . ' ' . ($violation->user_lname ?? '')),
                    'student_id' => $violation->student_number ?? 'N/A',
                    'violation' => $violation->violation_name ?? 'N/A',
                    'severity' => $violation->severity ?? 'N/A',
                    'penalty' => $this->formatPenalty($violation->penalty),
                    'action_taken' => $violation->action_taken ? 'Yes' : 'No',
                    'remarks' => $violation->action_taken ? 'Not Excused' : 'Excused',
                    'status' => ucfirst($violation->status ?? 'active'),
                    'prepared_by' => $violation->prepared_by ?? 'N/A'
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $reportData,
                'total' => $reportData->count(),
                'filters' => [
                    'year' => $year,
                    'month' => $month,
                    'batch' => $batch
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching violation report: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update action taken status for a violation
     */
    public function updateActionTaken(Request $request)
    {
        try {
            $request->validate([
                'violation_id' => 'required|integer',
                'action_taken' => 'required|boolean'
            ]);

            $violation = \App\Models\Violation::find($request->violation_id);

            if (!$violation) {
                return response()->json([
                    'success' => false,
                    'message' => 'Violation not found'
                ], 404);
            }

            // Store original values for logging
            $originalActionTaken = $violation->action_taken;
            $originalPenalty = $violation->penalty;
            $originalStatus = $violation->status;

            // Debug logging
            \Log::info('Updating violation action_taken', [
                'violation_id' => $request->violation_id,
                'old_action_taken' => $originalActionTaken,
                'new_action_taken' => $request->action_taken,
                'old_penalty' => $originalPenalty,
                'old_status' => $originalStatus
            ]);

            // Update action_taken
            $violation->action_taken = $request->action_taken;

            // If action_taken is set to false (No), update penalty and status
            if (!$request->action_taken) {
                // Store original penalty in consequence field as backup (if not already stored)
                if ($violation->penalty && !str_contains($violation->consequence, '[ORIGINAL_PENALTY:')) {
                    $violation->consequence = $violation->consequence . ' [ORIGINAL_PENALTY:' . $violation->penalty . ']';
                }
                // Set penalty to 'NONE' when no action taken, per business rule
                $violation->penalty = 'NONE';

                // Set consequence status to resolved since no action is taken
                $violation->consequence_status = 'resolved';
                $violation->consequence_start_date = null;
                $violation->consequence_end_date = null;

                // Status will be automatically set to 'resolved' by the model's boot method
            } else {
                // If action_taken is set back to true, try to restore original penalty
                if ((is_null($violation->penalty) || $violation->penalty === 'NONE') && str_contains($violation->consequence, '[ORIGINAL_PENALTY:')) {
                    // Extract original penalty from consequence
                    preg_match('/\[ORIGINAL_PENALTY:([^\]]+)\]/', $violation->consequence, $matches);
                    if (isset($matches[1])) {
                        $violation->penalty = $matches[1];
                        // Remove the backup from consequence
                        $violation->consequence = trim(preg_replace('/\s*\[ORIGINAL_PENALTY:[^\]]+\]/', '', $violation->consequence));
                    }
                }

                // Set consequence status based on whether there's a duration
                if ($violation->consequence_duration_value && $violation->consequence_duration_unit) {
                    $violation->consequence_status = 'active';
                    $violation->consequence_start_date = now();
                    $violation->consequence_end_date = $violation->calculateConsequenceEndDate();
                } else {
                    $violation->consequence_status = 'active';
                }

                // Status will remain as is or be set to active by default
                if ($violation->status === 'resolved') {
                    $violation->status = 'active';
                }
            }

            $saved = $violation->save();

            // Get updated values
            $updatedViolation = $violation->fresh();

            // Debug logging
            \Log::info('Violation update result', [
                'saved' => $saved,
                'current_action_taken' => $updatedViolation->action_taken,
                'current_penalty' => $updatedViolation->penalty,
                'current_status' => $updatedViolation->status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Action taken status updated successfully',
                'data' => [
                    'penalty' => $updatedViolation->penalty,
                    'status' => $updatedViolation->status,
                    'action_taken' => $updatedViolation->action_taken,
                    'consequence_status' => $updatedViolation->consequence_status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating action taken: ' . $e->getMessage()
            ], 500);
        }
    }



    /**
     * Format penalty code to readable text
     */
    private function formatPenalty($penalty)
    {
        // Get penalty display name from database configuration
        $penaltyConfig = \App\Models\PenaltyConfiguration::where('penalty_code', $penalty)->first();
        return $penaltyConfig ? $penaltyConfig->display_name : ($penalty ?? 'N/A');
    }

    /**
     * Display the behavior page with student statistics
     */
    public function behaviorStats()
    {
        $stats = $this->getStudentStats();
        return view('educator.behaviorStats', $stats);
    }

    /**
     * API endpoint to get behavior data for the chart
     */
    public function getBehaviorData(Request $request)
    {
        $monthsToShow = $request->input('months', 6);
        return $this->getBehaviorDataByGender($monthsToShow);
    }

    /**
     * Get severity level based on penalty
     * @param string $penalty The penalty code
     * @return string The severity level
     */
    protected function getSeverityFromPenalty($penalty)
    {
        switch ($penalty) {
            case 'W':
                return 'low';
            case 'VW':
                return 'medium';
            case 'WW':
                return 'high';
            case 'Pro':
            case 'Exp':
                return 'very high';
            default:
                return 'medium';
        }
    }

    /**
     * Get penalty code from penalty name
     * @param string $penalty The penalty name
     * @return string The penalty code
     */
    protected function getPenaltyCode($penalty)
    {
        switch (strtolower($penalty)) {
            case 'warning':
                return 'W';
            case 'verbal warning':
                return 'VW';
            case 'written warning':
                return 'WW';
            case 'probation':
                return 'Pro';
            case 'expulsion':
                return 'Exp';
            default:
                return $penalty;
        }
    }

    /**
     * Get violation statistics for the dashboard
     *
     * @param string $period The period to get statistics for (month, last_month, last_3_months, year)
     * @return array
     */
    protected function getViolationStats($period = 'month')
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Define date ranges based on period
        switch ($period) {
            case 'month':
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
                $periodLabel = 'This Month';
                break;

            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $startDate = Carbon::createFromDate($lastMonth->year, $lastMonth->month, 1)->startOfDay();
                $endDate = Carbon::createFromDate($lastMonth->year, $lastMonth->month, 1)->endOfMonth()->endOfDay();
                $periodLabel = 'Last Month';
                break;

            case 'last_3_months':
                $startDate = $now->copy()->subMonths(3)->startOfMonth()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                $periodLabel = 'Last 3 Months';
                break;

            case 'year':
                $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();
                $periodLabel = 'This Year';
                break;

            default:
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
                $periodLabel = 'This Month';
        }

        // Get total violations for this period
        $totalViolations = Violation::where('status', 'active')
            ->whereBetween('violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();

        // Get violations by type for this period (exclude approved appeals)
        $violationsByType = DB::table('violations')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->select('violation_types.violation_name', DB::raw('COUNT(*) as count'))
            ->where('violations.status', 'active')
            ->where('violations.status', '!=', 'appeal_approved') // Exclude approved appeals
            ->whereBetween('violations.violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('violation_types.id', 'violation_types.violation_name')
            ->orderBy('count', 'desc')
            ->take(5)
            ->get();

        // Get violations by severity for this period (exclude approved appeals)
        $violationsBySeverity = DB::table('violations')
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
            ->select('severities.severity_name as severity', DB::raw('COUNT(*) as count'))
            ->where('violations.status', 'active')
            ->where('violations.status', '!=', 'appeal_approved') // Exclude approved appeals
            ->whereBetween('violations.violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->groupBy('severities.severity_name')
            ->orderBy('count', 'desc')
            ->get();

        return [
            'totalViolations' => $totalViolations,
            'violationsByType' => $violationsByType,
            'violationsBySeverity' => $violationsBySeverity,
            'periodLabel' => $periodLabel
        ];
    }

    /**
     * Get student statistics for behavior monitoring
     */
    protected function getStudentStats()
    {
        return [
            'totalStudents' => User::where('user_role', 'student')->count(),
            'studentsNeedingAttention' => User::where('user_role', 'student')
                ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
                ->whereExists(function($query) {
                    $query->select(DB::raw(1))
                          ->from('violations')
                          ->whereRaw('violations.student_id = student_details.student_id')
                          ->havingRaw('COUNT(violations.id) > 2');
                })
                ->count()
        ];
    }

    /**
     * View student behavior page
     *
     * @param string $studentId
     * @return \Illuminate\View\View
     */
    public function viewStudentBehavior($studentId)
    {
        // Find the student
        $student = User::where('student_id', $studentId)->first();
        if (!$student) {
            return redirect()->route('educator.behavior')->with('error', 'Student not found');
        }

        // Get violation count
        $violationCount = \App\Models\Violation::where('student_id', $studentId)->count();

        return view('educator.student-behavior', [
            'student' => $student,
            'violationCount' => $violationCount
        ]);
    }

    /**
     * Get behavior data for a specific student
     *
     * @param Request $request
     * @param string $studentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentBehaviorData(Request $request, $studentId)
    {
        try {
            // Get the period (3, 6 or 12 months)
            $months = $request->input('months', 6);

            // Validate that months is one of the allowed values
            if (!in_array($months, [3, 6, 12])) {
                $months = 6; // Default to 6 months if invalid
            }

            // Find the student
            $student = User::where('student_id', $studentId)->first();
            if (!$student) {
                $student = User::where('id', $studentId)->first();
            }

            if (!$student) {
                throw new \Exception('Student not found');
            }

            // Use student_id for violation lookup, fallback to user id
            $lookupId = $student->student_id ?? $student->id;

            // Generate month labels and initialize scores
            $labels = [];
            $scoreData = [];

            // Generate last X months
            $startDate = now()->subMonths($months)->startOfMonth();
            $currentDate = clone $startDate;



            // Generate labels and default scores (all start at 100% level)
            while ($currentDate <= now()) {
                $labels[] = $currentDate->format('M Y');
                $scoreData[] = 100; // All months start at 100%
                $currentDate->addMonth();
            }

            \Illuminate\Support\Facades\Log::info('Processing student behavior chart', [
                'student_id' => $studentId,
                'lookup_id' => $lookupId,
                'student_name' => $student->name,
                'violations_count' => $violations->count(),
                'months' => $months,
                'current_score' => $currentScore
            ]);







            // Get the last violation date if any
            $lastViolationDate = null;
            if ($violations->count() > 0) {
                $lastViolation = $violations->sortByDesc('violation_date')->first();
                $lastViolationDate = $lastViolation ? \Carbon\Carbon::parse($lastViolation->violation_date)->format('M d, Y') : null;
            }

            \Illuminate\Support\Facades\Log::info('Final behavior chart data', [
                'student' => $student->name,
                'violations' => $violations->count(),
                'current_score' => $currentScore,
                'labels' => $labels,
                'scores' => $scoreData
            ]);

            // Return the data
            return response()->json([
                'labels' => $labels,
                'scoreData' => $scoreData,
                'currentScore' => $currentScore,
                'yAxisMax' => 100,
                'yAxisStep' => 10,
                'violationsCount' => $violations->count(),
                'studentName' => $student->name,
                'studentId' => $student->student_id ?? $student->id,
                'lastViolationDate' => $lastViolationDate
            ]);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in getStudentBehaviorData', [
                'student_id' => $studentId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Return a valid response even on error
            $defaultLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
            $defaultData = [100, 100, 100, 100, 100, 100];

            return response()->json([
                'error' => 'Could not load behavior data: ' . $e->getMessage(),
                'labels' => $defaultLabels,
                'scoreData' => $defaultData,
                'yAxisMax' => 100,
                'yAxisStep' => 10,
                'violationsCount' => 0,
                'studentName' => 'Unknown Student',
                'studentId' => $studentId
            ]);
        }
    }

    /**
     * Convert penalty code to a human-readable name
     *
     * @param string $penalty The penalty code
     * @return string The full name of the penalty
     */
    protected function getPenaltyName($penalty)
    {
        switch ($penalty) {
            case 'W':
                return 'Warning';
            case 'VW':
                return 'Verbal Warning';
            case 'WW':
                return 'Written Warning';
            case 'Pro':
                return 'Probation';
            case 'T':
                return 'Termination of Contract';
            default:
                return 'Unknown Penalty';
        }
    }

    /**
     * Get behavior data by gender for the chart
     */
    protected function getBehaviorDataByGender($monthsToShow = 6)
    {
        // Initialize arrays and variables
        $labels = [];
        $menData = [];
        $womenData = [];
        $currentDate = now();
        $allMonths = [];
        $totalViolations = 0;
        
        try {
            // Generate months array based on requested period
            if ($monthsToShow == 12) {
                // Use all calendar months of the current year
                for ($month = 1; $month <= 12; $month++) {
                    $monthDate = Carbon::createFromDate($currentDate->year, $month, 1);
                    $allMonths[] = (object)[
                        'year' => $currentDate->year,
                        'month' => $month,
                        'date' => $monthDate,
                        'name' => $monthDate->format('F')
                    ];
                }
            } else {
                // Use relative months from current date
                $tempDate = now()->subMonths($monthsToShow - 1)->startOfMonth();
                for ($i = 0; $i < $monthsToShow; $i++) {
                    $allMonths[] = (object)[
                        'year' => $tempDate->year,
                        'month' => $tempDate->month,
                        'date' => $tempDate->copy(),
                        'name' => $tempDate->format('F')
                    ];
                    $tempDate->addMonth();
                }
            }

            // Process each month
            foreach ($allMonths as $monthData) {
                $monthNum = $monthData->month;
                $yearNum = $monthData->year;
                $labels[] = $monthData->name;

                // Get all violations from the database
                $allViolations = DB::table('violations')
                    
                    ->get();

                // Special handling for problematic months (February, April, June)
                if (in_array($monthData->name, ['February', 'April', 'June'])) {
                    \Log::info("Processing {$monthData->name} {$yearNum} - Total violations: {$allViolations->count()}");

                    // FORCE detection of violations for these months by directly querying the database
                    $forcedViolations = DB::table('violations')
                        
                        ->get();

                    // Log all violations for debugging
                    foreach ($forcedViolations as $violation) {
                        \Log::info("Checking violation ID: {$violation->id}, Date: {$violation->violation_date}, Sex: {$violation->sex}");

                        // Check if the date string contains the month name in any form
                        $violationDate = strtolower($violation->violation_date);
                        $monthLower = strtolower($monthData->name);
                        $monthShort = strtolower(substr($monthData->name, 0, 3)); // feb, jun, apr

                        if (strpos($violationDate, $monthLower) !== false ||
                            strpos($violationDate, $monthShort) !== false ||
                            strpos($violationDate, "{$monthNum}/") !== false ||
                            strpos($violationDate, "-{$monthNum}-") !== false) {

                            \Log::info("Found {$monthData->name} violation: {$violation->id}");
                            // Add this violation to our collection if it's not already there
                            $allViolations->push($violation);
                        }
                    }
                }

                // Filter men violations for this month with comprehensive date checking
                $menViolations = $allViolations->filter(function($violation) use ($monthData, $monthNum, $yearNum) {
                    // Check if this is a male/man violation
                    $isMale = in_array(strtolower($violation->gender), ['male', 'm', 'boy', 'man', 'men']);
                    if (!$isMale) {
                        return false;
                    }

                    // Get the violation date
                    $violationDate = $violation->violation_date;

                    // Check for month name in the date string
                    if (stripos($violationDate, $monthData->name) !== false) {
                        return true;
                    }

                    // Check for standard date format (YYYY-MM-DD)
                    try {
                        $date = new \DateTime($violationDate);
                        $dateMonth = (int)$date->format('n');
                        $dateYear = (int)$date->format('Y');

                        if ($dateMonth === $monthNum && $dateYear === $yearNum) {
                            return true;
                        }
                    } catch (\Exception $e) {
                        // Date parsing failed, continue with other checks
                    }

                    // Check for numeric formats (MM/DD/YYYY, MM-DD-YYYY)
                    $paddedMonth = str_pad($monthNum, 2, '0', STR_PAD_LEFT);
                    if (strpos($violationDate, "{$paddedMonth}/") !== false ||
                        strpos($violationDate, "{$paddedMonth}-") !== false) {
                        return true;
                    }

                    return false;
                });

                // Filter women violations for this month with comprehensive date checking
                $womenViolations = $allViolations->filter(function($violation) use ($monthData, $monthNum, $yearNum) {
                    // Check if this is a female/woman violation
                    $isFemale = in_array(strtolower($violation->gender), ['female', 'f', 'girl', 'woman', 'women']);
                    if (!$isFemale) {
                        return false;
                    }

                    // Get the violation date
                    $violationDate = $violation->violation_date;

                    // Check for month name in the date string
                    if (stripos($violationDate, $monthData->name) !== false) {
                        return true;
                    }

                    // Check for standard date format (YYYY-MM-DD)
                    try {
                        $date = new \DateTime($violationDate);
                        $dateMonth = (int)$date->format('n');
                        $dateYear = (int)$date->format('Y');

                        if ($dateMonth === $monthNum && $dateYear === $yearNum) {
                            return true;
                        }
                    } catch (\Exception $e) {
                        // Date parsing failed, continue with other checks
                    }

                    // Check for numeric formats (MM/DD/YYYY, MM-DD-YYYY)
                    $paddedMonth = str_pad($monthNum, 2, '0', STR_PAD_LEFT);
                    if (strpos($violationDate, "{$paddedMonth}/") !== false ||
                        strpos($violationDate, "{$paddedMonth}-") !== false) {
                        return true;
                    }

                    return false;
                });

                // Count the violations for this month
                $menCount = $menViolations->count();
                $womenCount = $womenViolations->count();
                
                // Log the counts for debugging
                \Log::info("Month {$monthData->name}: Men violations: {$menCount}, Women violations: {$womenCount}");
                
                // Add the counts to our data arrays
                $menData[] = $menCount;
                $womenData[] = $womenCount;
                
                // Add to total violations count
                $totalViolations += $menCount + $womenCount;
            }
            
            \Log::info("Total violations processed: {$totalViolations}");
            \Log::info("Men data: " . json_encode($menData));
            \Log::info("Women data: " . json_encode($womenData));

           
           
            return [
                'labels' => $labels,
                'men' => $menData,
                'women' => $womenData,
                'lastUpdated' => now()->format('Y-m-d H:i:s')
            ];
        } 
        catch (\Exception $e) {
            \Log::error('Error in getBehaviorDataByGender: ' . $e->getMessage());
            return [
                'error' => true,
                'message' => 'Failed to load behavior data: ' . $e->getMessage(),
                'labels' => [],
                'men' => [],
                'women' => []
            ];
        }
    }

    /**
     * Show the form for editing the student manual
     */
    public function editManual()
    {
        // Get all offense categories with their violation types
        $categories = OffenseCategory::with(['violationTypes' => function($query) {
            $query->with('severityRelation');
        }])->get();

        // Get penalty options for JavaScript
        $penaltyOptions = \App\Models\PenaltyConfiguration::getForDropdown();

        return view('educator.editManual', compact('categories', 'penaltyOptions'));
    }

    /**
     * Update the student manual
     */
    public function updateManual(Request $request)
    {
        // Log the incoming request data
        Log::info('Manual update request received', ['data' => $request->all()]);
        
        DB::beginTransaction();
        try {
            $submittedCategoryIds = collect($request->input('categories', []))->pluck('id')->filter()->values();
            $existingCategoryIds = OffenseCategory::pluck('id');

            // Delete categories not in the submitted data
            OffenseCategory::whereIn('id', $existingCategoryIds->diff($submittedCategoryIds))->delete();

            // Update existing categories and their violations, and add new violations
            if ($request->has('categories')) {
                foreach ($request->categories as $categoryData) {
                    if (isset($categoryData['id'])) {
                        // Existing category
                        $category = OffenseCategory::find($categoryData['id']);
                        if ($category) {
                            $category->category_name = $categoryData['category_name'];
                            $category->save();

                            $submittedViolationIds = collect($categoryData['violationTypes'] ?? [])->pluck('id')->filter()->values();
                            $existingViolationIds = $category->violationTypes()->pluck('id');

                            // Delete violations not in the submitted data for this category
                            $category->violationTypes()->whereIn('id', $existingViolationIds->diff($submittedViolationIds))->delete();

                            if (isset($categoryData['violationTypes'])) {
                                foreach ($categoryData['violationTypes'] as $violationData) {
                                    if (isset($violationData['id']) && !empty($violationData['id'])) {
                                        // Existing violation - preserve severity_id, only update violation_name
                                        $violation = ViolationType::find($violationData['id']);
                                        if ($violation) {
                                            $violation->violation_name = $violationData['violation_name'];
                                            $violation->save();
                                        }
                                    } else if (!empty($violationData['violation_name'])) {
                                        // New violation for existing category
                                        $newViolation = new ViolationType();
                                        $newViolation->offense_category_id = $category->id;
                                        $newViolation->violation_name = $violationData['violation_name'];
                                        $newViolation->severity_id = $violationData['severity_id'] ?? 2; // Default to Medium (ID: 2)
                                        $newViolation->default_penalty = 'VW'; // Default penalty
                                        $newViolation->save();
                                    }
                                }
                            }
                        }
                    } else {
                        // New category
                        if (!empty($categoryData['category_name'])) {
                            $newCategory = new OffenseCategory();
                            $newCategory->category_name = $categoryData['category_name'];
                            $newCategory->save();

                            // Add violations to new category
                            if (isset($categoryData['violationTypes'])) {
                                foreach ($categoryData['violationTypes'] as $violationData) {
                                    if (!empty($violationData['violation_name'])) {
                                        $newViolation = new ViolationType();
                                        $newViolation->offense_category_id = $newCategory->id;
                                        $newViolation->violation_name = $violationData['violation_name'];
                                        $newViolation->severity_id = $violationData['severity_id'] ?? 2; // Default to Medium (ID: 2)
                                        $newViolation->default_penalty = 'VW'; // Default penalty
                                        $newViolation->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }
            
            // Clear any cache
            if (method_exists(\Cache::class, 'forget')) {
                \Cache::forget('student_manual_categories');
            }
            
            DB::commit();
            Log::info('Manual updated successfully');

            // Fire event to notify all students about manual update
            event(new ManualUpdated('manual_update', [
                'updated_at' => now(),
                'updated_by' => auth()->user()->user_fname . ' ' . auth()->user()->user_lname
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Manual updated successfully.',
                'redirect_url' => route('educator.manual')
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating manual: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to update manual: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handles the deletion of a violation type.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteViolationType(Request $request)
    {
        $violationTypeId = $request->input('violation_type_id');

        try {
            $violationType = ViolationType::findOrFail($violationTypeId);
            $violationName = $violationType->violation_name;
            $violationType->delete();

            if (method_exists(\Cache::class, 'forget')) {
                \Cache::forget('student_manual_categories');
            }

            // Fire event to notify all students about violation type deletion
            event(new ManualUpdated('category_change', [
                'action' => 'deleted',
                'category_name' => "violation type '{$violationName}'",
                'updated_by' => auth()->user()->user_fname . ' ' . auth()->user()->user_lname
            ]));

            return response()->json(['success' => true, 'message' => 'Violation deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deleting violation type: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete violation: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Handles the deletion of an offense category.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteOffenseCategory(Request $request)
    {
        $categoryId = $request->input('category_id');

        try {
            $category = OffenseCategory::findOrFail($categoryId);
            $categoryName = $category->category_name;
            $category->delete();

            if (method_exists(\Cache::class, 'forget')) {
                \Cache::forget('student_manual_categories');
            }

            // Fire event to notify all students about category deletion
            event(new ManualUpdated('category_change', [
                'action' => 'deleted',
                'category_name' => $categoryName,
                'updated_by' => auth()->user()->user_fname . ' ' . auth()->user()->user_lname
            ]));

            return response()->json(['success' => true, 'message' => 'Category deleted successfully.']);
        } catch (\Exception $e) {
            Log::error('Error deleting category: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to delete category: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get severity max counts for the manual page
     */
    public function getSeverityMaxCounts()
    {
        try {
            $maxCounts = SeverityMaxCount::orderBySeverity()->get();

            // If no records exist, create default ones
            if ($maxCounts->isEmpty()) {
                $this->createDefaultSeverityMaxCounts();
                $maxCounts = SeverityMaxCount::orderBySeverity()->get();
            }

            return response()->json([
                'success' => true,
                'data' => $maxCounts
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting severity max counts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to load severity max counts: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update severity max counts
     */
    public function updateSeverityMaxCounts(Request $request)
    {
        try {
            $request->validate([
                'severity_configs' => 'required|array',
                'severity_configs.*.id' => 'required|integer',
                'severity_configs.*.max_count' => 'required|integer|min:1|max:10',
                'severity_configs.*.base_penalty' => 'required|string|in:VW,WW,Pro,T',
                'severity_configs.*.escalated_penalty' => 'required|string|in:VW,WW,Pro,T',
                'severity_configs.*.description' => 'nullable|string|max:500'
            ]);

            DB::beginTransaction();

            foreach ($request->severity_configs as $config) {
                SeverityMaxCount::where('id', $config['id'])->update([
                    'max_count' => $config['max_count'],
                    'base_penalty' => $config['base_penalty'],
                    'escalated_penalty' => $config['escalated_penalty'],
                    'description' => $config['description'] ?? ''
                ]);
            }

            DB::commit();

            // Fire event to notify all students about severity configuration update
            event(new ManualUpdated('severity_config_update', [
                'updated_at' => now(),
                'updated_by' => auth()->user()->user_fname . ' ' . auth()->user()->user_lname,
                'configs_updated' => count($request->severity_configs)
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Severity configurations updated successfully.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating severity max counts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update severity configurations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create default severity max counts
     */
    private function createDefaultSeverityMaxCounts()
    {
        // Run the seeder to create default configurations
        Artisan::call('db:seed', ['--class' => 'SeverityMaxCountSeeder']);
    }


}






