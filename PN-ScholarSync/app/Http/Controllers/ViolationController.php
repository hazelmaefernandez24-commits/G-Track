<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Violation;
use App\Models\ViolationType;
use App\Models\SeverityMaxCount;
use App\Models\OffenseCategory;
use App\Models\Severity;
use App\Models\User;
use App\Http\Requests\StoreViolationTypeRequest;
use App\Traits\ViolationCountingTrait;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Exception;
use App\Events\ManualUpdated;

class ViolationController extends Controller
{
    use ViolationCountingTrait;

    /**
     * Get violation statistics by batch
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStatsByBatch(Request $request)
    {
        $batch = $request->query('batch', 'all');
        $period = $request->query('period', 'month');
        
        // Get date range for the period
        $dateRange = $this->getDateRangeForPeriod($period);
        $startDate = $dateRange['start'];
        $endDate = $dateRange['end'];
        
        // Base query for violations within the date range
        $query = Violation::whereBetween('created_at', [$startDate, $endDate]);
        
        // Filter by batch if not 'all'
        if ($batch !== 'all') {
            $query->whereHas('studentDetails', function ($q) use ($batch) {
                $q->where('batch', $batch);
            });
        }

        // Get counts by violation type (exclude approved appeals)
        $violationStats = $query->with('violationType')
            ->where('status', '!=', 'appeal_approved')
            ->get()
            ->groupBy('violation_type_id')
            ->map(function ($violations, $typeId) {
                $type = $violations->first()->violationType;
                return [
                    'type' => $type->violation_name,
                    'count' => $violations->count(),
                    'color' => $type->color ?? '#' . substr(md5($type->violation_name), 0, 6)
                ];
            })
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $violationStats,
            'period' => $period,
            'dateRange' => $dateRange
        ]);
    }

    /**
     * API: Search students for Select2 (id = student_id, text = "Lastname, Firstname (ID)")
     * GET /api/search-students?q=<term>
     */
    public function searchStudents(Request $request)
    {
        $term = trim((string) $request->query('q', ''));

        // Base query joins student_details to get authoritative student_id
        $query = User::query()
            ->join('student_details', 'pnph_users.user_id', '=', 'student_details.user_id')
            ->select('pnph_users.user_fname', 'pnph_users.user_lname', 'student_details.student_id');

        // Filter to student role; support either spatie role() or user_role column
        if (method_exists(User::class, 'role')) {
            try {
                $ids = User::role('student')->pluck('user_id');
                $query->whereIn('pnph_users.user_id', $ids);
            } catch (\Throwable $e) {
                $query->where('pnph_users.user_role', 'student');
            }
        } else {
            $query->where('pnph_users.user_role', 'student');
        }

        if ($term !== '') {
            $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $term) . '%';
            $query->where(function($q) use ($like) {
                $q->where('pnph_users.user_fname', 'like', $like)
                  ->orWhere('pnph_users.user_lname', 'like', $like)
                  ->orWhere('student_details.student_id', 'like', $like);
            });
        }

        $results = $query
            ->orderBy('pnph_users.user_lname')
            ->limit(20)
            ->get()
            ->map(function($row){
                $fname = $row->user_fname ?? '';
                $lname = $row->user_lname ?? '';
                $sid = $row->student_id ?? '';
                return [
                    'id' => $sid,
                    'text' => trim($lname . ', ' . $fname) . ($sid ? ' (' . $sid . ')' : '')
                ];
            });

        return response()->json($results);
    }
    


    /**
     * Display a listing of violations
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        try {
            $batch = $request->query('batch', 'all');
            $severity = $request->query('severity', '');
            $search = $request->query('search', '');
            
            // Get regular violations
            $violationsQuery = Violation::with(['student', 'violationType.offenseCategory'])
                ->orderBy('created_at', 'desc');
            
            // Apply batch filter
            if ($batch !== 'all') {

                // Filter via student_details to be resilient when violations.student_id is missing
                $violationsQuery
                    ->leftJoin('student_details as sd_for_batch', 'violations.student_id', '=', 'sd_for_batch.student_id')
                    ->where(function ($q) use ($batch) {
                        // Match explicit batch column if present
                        $q->orWhere('sd_for_batch.batch', $batch)
                          // Match student_id starting with year on student_details
                          ->orWhere('sd_for_batch.student_id', 'like', $batch . '%')
                          // Also match directly on violations table as fallback
                          ->orWhere('violations.student_id', 'like', $batch . '%');
                    })
                    ->select('violations.*');
            }

            if ($severity !== '') {
                $sevLower = strtolower(trim($severity));
                $violationsQuery
                    ->leftJoin('violation_types as vt_for_sev', 'violations.violation_type_id', '=', 'vt_for_sev.id')
                    ->leftJoin('severities as sev_for_vt', 'vt_for_sev.severity_id', '=', 'sev_for_vt.id')
                    ->whereRaw('LOWER(TRIM(COALESCE(violations.severity, sev_for_vt.severity_name))) = ?', [$sevLower])
                    ->select('violations.*');

            }
            
            // Apply severity filter (handled above via join when provided)
            
            // Apply search filter
            if (!empty($search)) {
                $violationsQuery->where(function($query) use ($search) {
                    $query->whereHas('student', function($q) use ($search) {
                        $q->where('user_fname', 'like', "%{$search}%")
                          ->orWhere('user_lname', 'like', "%{$search}%")
                          ->orWhere('student_id', 'like', "%{$search}%");
                    })
                    ->orWhereHas('violationType', function($q) use ($search) {
                        $q->where('violation_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('violationType.offenseCategory', function($q) use ($search) {
                        $q->where('category_name', 'like', "%{$search}%");
                    });
                });
            }
            
            // Get regular violations
            $regularViolations = $violationsQuery->get();
            
            // Get invalid students and convert them to violation-like objects
            $invalidStudents = $this->getInvalidStudentsAsViolations($batch, $severity, $search);

            // Get x_status rows (synced from G16 task_histories) as violation-like objects
            $xStatusViolations = $this->getXStatusAsViolations($batch, $severity, $search);
            
            // Combine both collections
            $allViolations = $regularViolations
                ->concat($invalidStudents)
                ->concat($xStatusViolations)
                ->sortByDesc('violation_date')
                ->values();
            
            // Manual pagination
            $perPage = 10;
            $currentPage = $request->query('page', 1);
            $offset = ($currentPage - 1) * $perPage;
            
            $paginatedViolations = $allViolations->slice($offset, $perPage);
            $total = $allViolations->count();
            
            $violations = new \Illuminate\Pagination\LengthAwarePaginator(
                $paginatedViolations,
                $total,
                $perPage,
                $currentPage,
                [
                    'path' => $request->url(),
                    'pageName' => 'page',
                ]
            );
            
            $violations->appends([
                'batch' => $batch,
                'severity' => $severity,
                'search' => $search
            ]);

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

            // Compute penalty counts (unique students) from the same filtered dataset (pre-pagination)
            $eligibleForCounts = $allViolations->filter(function ($v) {
                return ($v->action_taken ?? false) && strtolower((string)($v->status ?? '')) !== 'appeal_approved';
            });
            // Count TOTAL violations per penalty (matches rows in table, after filters), not unique students
            $penaltyCounts = [
                'VW'  => $eligibleForCounts->where('penalty', 'VW')->count(),
                'WW'  => $eligibleForCounts->where('penalty', 'WW')->count(),
                'Pro' => $eligibleForCounts->where('penalty', 'Pro')->count(),
                'T'   => $eligibleForCounts->where('penalty', 'T')->count(),
            ];

            return view('educator.violation', [
                'violations' => $violations,
                'batch' => $batch,
                'severity' => $severity,
                'search' => $search,
                'unreadCount' => $unreadCount,
                'penaltyCounts' => $penaltyCounts,
            ]);
        } catch (Exception $e) {
            Log::error('Error fetching violations: ' . $e->getMessage());
            // Create an empty paginated result to avoid hasPages() error
            $emptyViolations = new \Illuminate\Pagination\LengthAwarePaginator(
                collect(), // Empty collection
                0, // Total items
                10, // Items per page
                1, // Current page
                [
                    'path' => request()->url(),
                    'pageName' => 'page',
                ]
            );

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

            return view('educator.violation', [
                'violations' => $emptyViolations,
                'unreadCount' => $unreadCount,
                'penaltyCounts' => ['VW'=>0,'WW'=>0,'Pro'=>0,'T'=>0],
            ])->with('error', 'Unable to load violations. Please try again later.');
        }
    }

    /**
     * Show the form for creating a new violation
     */
    public function create()
    {
        $students = User::where('user_role', 'student')->with('studentDetails')->get();
        $violationTypes = ViolationType::all();
        $offenseCategories = OffenseCategory::all();
        return view('educator.addViolator', compact('students', 'violationTypes', 'offenseCategories'));
    }

    
    /**
     * Store a newly created violation in storage
     */
    public function store(Request $request)
    {
        // --- Backend Validation for Termination Policy ---

        // 1. Check if student already has a termination penalty (exclude approved appeals)
        //    Allow submission but record a warning in logs.
        $studentId = $request->input('student_id');
        $hasTermination = Violation::where('student_id', $studentId)
            ->whereRaw('UPPER(penalty) = ?', ['T'])
            ->whereNotExists(function($sub){
                $sub->select(\DB::raw(1))
                    ->from('violation_appeals')
                    ->whereColumn('violation_appeals.violation_id', 'violations.id')
                    ->where('violation_appeals.status', 'approved');
            })
            ->exists();

        if ($hasTermination) {
            Log::warning('Proceeding to add violation for already-terminated student (store)', ['student_id' => $studentId]);
        }

        // 2. Get violation type and calculate severity
        $violationType = ViolationType::with('severityRelation')->find($request->input('violation_type_id'));
        $severity = $violationType && $violationType->severityRelation ? $violationType->severityRelation->severity_name : 'Low';

        // --- End of Validation ---

        $validated = $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
            'incident_datetime' => 'nullable|date',
            'incident_place' => 'nullable|string|max:255',
            'incident_details' => 'nullable|string',
            'prepared_by' => 'nullable|string|max:255',
            'action_taken' => 'required|boolean',
        ]);
        
        // Get student gender for the record
        $studentDetails = \App\Models\StudentDetails::where('student_id', $request->student_id)->first();

        // Handle case where student might not be found
        if ($studentDetails && $studentDetails->user) {
            $validated['gender'] = $studentDetails->user->gender;
        } else {
            // Default to a placeholder value if student not found
            $validated['gender'] = 'unknown';
            Log::warning('Student not found when creating violation', ['student_id' => $request->student_id]);
        }
        
        // Get severity from the violation type
        if ($violationType && $violationType->severity_id) {
            $severityModel = Severity::find($violationType->severity_id);
            
            if ($severityModel) {
                $validated['severity'] = $severityModel->severity_name;
            } else {
                // Default to a placeholder value if severity not found
                $validated['severity'] = 'Medium';
                Log::warning('Severity not found when creating violation', [
                    'violation_type_id' => $request->violation_type_id,
                    'severity_id' => $violationType->severity_id
                ]);
            }
        } else {
            // Default to a placeholder value if violation type or severity_id not found
            $validated['severity'] = 'Medium';
            Log::warning('Violation type not found or missing severity_id when creating violation', [
                'violation_type_id' => $request->violation_type_id
            ]);
        }

        // Compute offense count for ACTIVE prior violations in the SAME SEVERITY
        // Escalation continues while previous violation(s) in the severity are still active
        $existingOffenses = Violation::where('student_id', $studentId)
            ->where('action_taken', true)
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
            ->where('severities.severity_name', $validated['severity'])
            ->where(function($q){
                $q->where('violations.status', 'active')
                  ->orWhere('violations.consequence_status', 'active');
            })
            ->where(function($q) {
                $q->where('violations.status', '!=', 'appeal_approved')->orWhereNull('violations.status');
            })
            ->whereNotExists(function($sub){
                $sub->select(\DB::raw(1))
                    ->from('violation_appeals')
                    ->whereColumn('violation_appeals.violation_id', 'violations.id')
                    ->where('violation_appeals.status', 'approved');
            })
            ->count();
        $offenseCount = $existingOffenses + 1;
        $offenseString = $offenseCount . (function($n){ $j = $n % 10; $k = $n % 100; if ($j == 1 && $k != 11) return 'st'; if ($j == 2 && $k != 12) return 'nd'; if ($j == 3 && $k != 13) return 'rd'; return 'th'; })($offenseCount);

        // Calculate penalty using active same-severity count
        $calculatedPenalty = $this->computePenaltyByRules($validated['severity'], $existingOffenses);

        // Enforce: penalties never downgrade.
        // 1) If currently on Probation -> next is Termination (business rule)
        // 2) Otherwise, do not allow the new penalty to be lower than the student's highest existing penalty.
        $highestExistingPenalty = $this->getHighestExistingPenalty($studentId);
        if ($highestExistingPenalty === 'Pro') {
            $finalPenalty = 'T';
        } elseif (!empty($highestExistingPenalty)) {
            // Use helper to ensure monotonic non-decreasing penalties
            $finalPenalty = $this->escalateIfRepeat($highestExistingPenalty, $calculatedPenalty);
        } else {
            $finalPenalty = $calculatedPenalty;
        }
        // Global rule: every new violation escalates one level from the decided penalty
        $finalPenalty = $this->escalateOneLevel($finalPenalty ?? ($request->input('penalty') ?? null));
        $validated['penalty'] = $finalPenalty;

        // If consequence is empty, include a short offense note so the offense is visible without DB schema changes
        if (empty($validated['consequence'])) {
            $validated['consequence'] = "Offense: {$offenseString}";
        } else {
            // append offense info for transparency (avoid overwriting user-provided consequence)
            $validated['consequence'] = trim($validated['consequence']) . " (Offense: {$offenseString})";
        }
        
        // Idempotency: skip creating if a very recent identical record already exists (guards double-clicks)
        $recentDuplicate = Violation::where('student_id', $studentId)
            ->where('violation_type_id', $request->input('violation_type_id'))
            ->whereDate('violation_date', Carbon::parse($request->input('violation_date'))->toDateString())
            ->where('created_at', '>=', now()->subSeconds(10))
            ->exists();
        if ($recentDuplicate) {
            Log::warning('Skipping duplicate single violation detected (recent identical record exists)', [
                'student_id' => $studentId,
                'violation_type_id' => $request->input('violation_type_id'),
                'violation_date' => $request->input('violation_date')
            ]);
            return redirect()->route('educator.violation')
                ->with('success', 'Duplicate submission ignored: a similar violation was just recorded.');
        }

        // Create a new violation record
        $violation = new Violation($validated);

        // Set final penalty and default action_taken
        $violation->penalty = $finalPenalty ?? $request->input('penalty');
        $violation->action_taken = $request->input('action_taken', true); // Default to true, allow override from form

        // Handle consequence status based on action_taken
        if (!$violation->action_taken) {
            // If no action is taken, consequence is resolved (no consequence applied)
            $violation->consequence_status = 'resolved';
        } else {
            // Action is taken, handle consequence duration if provided
            // Validate duration similar to group flow
            $allowedUnits = ['hours', 'days', 'weeks', 'months'];
            $durVal = (int) ($request->input('duration_value') ?? 0);
            $durUnit = (string) $request->input('duration_unit');
            if ($durVal > 0 && in_array($durUnit, $allowedUnits, true)) {
                $violation->consequence_duration_value = $durVal;
                $violation->consequence_duration_unit = $durUnit;
                $violation->consequence_status = 'active';
                $violation->consequence_start_date = now();
                $violation->consequence_end_date = $violation->calculateConsequenceEndDate();
            } else {
                // For consequences without duration or invalid duration inputs
                $violation->consequence_duration_value = null;
                $violation->consequence_duration_unit = null;
                $violation->consequence_start_date = null;
                $violation->consequence_end_date = null;
                $violation->consequence_status = 'active';
            }
        }

                $violation->save();
                // Debug: Log after save
                Log::debug('Violation saved', ['violation_id' => $violation->id, 'student_id' => $violation->student_id]);
        
        // Show appropriate success message based on penalty severity
        $penaltyConfig = \App\Models\PenaltyConfiguration::where('penalty_code', $violation->penalty)->first();
        $isTermination = $penaltyConfig && strtolower($penaltyConfig->display_name) === 'termination of contract';

        $message = $isTermination
            ? 'Violation added successfully. Note: This violation results in termination of contract.'
            : 'Violation added successfully.';
        
        return redirect()->route('educator.violation')
            ->with('success', $message);
    }

    /**
     * Show the form for editing the specified violation
     */
    public function edit($id)
    {
        $violation = Violation::with(['student.studentDetails', 'violationType'])->findOrFail($id);
        $offenseCategories = OffenseCategory::all();
        $violationTypes = ViolationType::where('offense_category_id', $violation->violationType->offense_category_id)->get();
        $students = User::role('student')->with('studentDetails')->get(); // <-- Make sure this line is present

        return view('educator.editViolation', compact('violation', 'offenseCategories', 'violationTypes', 'students'));
    }

    /**
     * Update the specified violation in storage
     */
    public function update(Request $request, $id)
    {
        try {
            // Log the incoming request for debugging
            \Log::info('Violation status update request', [
                'id' => $id,
                'request_data' => $request->all(),
                'expects_json' => $request->expectsJson(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept')
            ]);

            // Only validate and update the status field
            $validated = $request->validate([
                'status' => 'required|in:active,resolved',
            ]);

            $violation = Violation::findOrFail($id);
            $oldStatus = $violation->status;
            $violation->status = $validated['status'];
            $violation->save();

            \Log::info('Violation status updated successfully', [
                'id' => $id,
                'old_status' => $oldStatus,
                'new_status' => $violation->status
            ]);

            // Check if this is an AJAX request
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Violation status updated successfully.',
                    'status' => $violation->status
                ]);
            }

            return redirect()->route('educator.violation')
                ->with('success', 'Violation status updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error in violation status update', [
                'id' => $id,
                'errors' => $e->validator->errors()->all()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed: ' . implode(', ', $e->validator->errors()->all())
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            \Log::error('Error in violation status update', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating violation status: ' . $e->getMessage()
                ], 500);
            }
            throw $e;
        }
    }

    /**
     * Get violation types by category
     */
    public function getViolationTypesByCategory($categoryId)
    {
        $violationTypes = ViolationType::where('offense_category_id', $categoryId)
            ->with('severityRelation')
            ->orderBy('violation_name')
            ->get()
            ->unique('violation_name') // Only unique violation names
            ->values() // Re-index the collection
            ->map(function ($type) {
                return [
                    'id' => $type->id,
                    'violation_name' => $type->violation_name,
                    'offense_category_id' => $type->offense_category_id,
                    'default_penalty' => $type->default_penalty,
                    'severity_id' => $type->severity_id,
                    'severity' => $type->severityRelation->severity_name ?? 'Medium'
                ];
            });
        
        return response()->json($violationTypes);
    }

    /**
     * Get form data for the new violation type form
     */
    public function getFormData()
    {
        $categories = OffenseCategory::all();
        $severities = Severity::all();
        return response()->json([
            'categories' => $categories,
            'severities' => $severities
        ]);
    }

    /**
     * Show the form for creating a new violation type
     */
    public function createViolationType()
    {
        $categories = OffenseCategory::all();
        $severities = Severity::all();

        // Create dynamic penalty map based on severity configurations
        $penaltyMap = [];
        foreach ($severities as $severity) {
            $config = SeverityMaxCount::where('severity_name', $severity->severity_name)->first();
            if ($config) {
                $basePenaltyName = \App\Models\PenaltyConfiguration::getDisplayName($config->base_penalty);
                $escalatedPenaltyName = \App\Models\PenaltyConfiguration::getDisplayName($config->escalated_penalty);

                $penalties = [$basePenaltyName];
                if ($config->escalated_penalty !== $config->base_penalty) {
                    $penalties[] = $escalatedPenaltyName;
                }
                $penaltyMap[$severity->severity_name] = $penalties;
            }
        }

        return view('educator.newViolation', compact('categories', 'severities', 'penaltyMap'));
    }

    /**
     * Store a new violation type
     * 
     * @param \App\Http\Requests\StoreViolationTypeRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeViolationType(StoreViolationTypeRequest $request)
    {
        try {
            // Get validated data
            $validated = $request->validated();
            
            // Find or create the offense category
            $offenseCategory = OffenseCategory::firstOrCreate(['category_name' => $validated['category']]);
            
            // Find the severity by name
            $severity = \App\Models\Severity::where('severity_name', $validated['severity'])->first();
            
            if (!$severity) {
                throw new \Exception('Invalid severity level');
            }
            
            // Create the violation type
            $violationType = ViolationType::create([
                'offense_category_id' => $offenseCategory->id,
                'violation_name' => $validated['violation_name'],
                'description' => $validated['offense'] ?? null,
                'default_penalty' => $validated['penalty'] ?? null,
                'severity_id' => $severity->id
            ]);

            // Fire event to notify all students about new violation type
            event(new ManualUpdated('new_violation_type', [
                'violation_name' => $validated['violation_name'],
                'category_name' => $offenseCategory->category_name,
                'severity' => $severity->severity_name,
                'created_by' => auth()->user()->user_fname . ' ' . auth()->user()->user_lname
            ]));

            // Return a nicer success message
            return response()->json([
                'success' => true,
                'message' => '✅ New violation added successfully! The student manual has been updated.',
                'data' => $violationType,
                'redirect' => route('educator.violation')
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
     * Show the form for adding a violator
     */
    public function addViolatorForm()
    {
        $students = User::where('user_role', 'student')->with('studentDetails')->get();
        $offenseCategories = OffenseCategory::all();
        // Get severities from database
        $severities = \App\Models\Severity::orderBy('id')->pluck('severity_name')->toArray();
        $offenses = ['1st', '2nd', '3rd'];
        // Get penalties from database configuration
        $penalties = \App\Models\PenaltyConfiguration::getActive()
            ->map(function($penalty) {
                return [
                    'value' => $penalty->penalty_code,
                    'label' => $penalty->display_name
                ];
            })->toArray();

        // Get penalty code to display name mapping for JavaScript
        $penaltyCodeMap = \App\Models\PenaltyConfiguration::getForDropdown();

        return view('educator.addViolator', compact('students', 'offenseCategories', 'severities', 'offenses', 'penalties', 'penaltyCodeMap'));
    }

    /**
     * Show the form for adding group violators (reuse single form with multi-select mode)
     */
    public function addGroupViolatorForm()
    {
        $students = User::where('user_role', 'student')->with('studentDetails')->get();
        $offenseCategories = OffenseCategory::all();
        $severities = \App\Models\Severity::orderBy('id')->pluck('severity_name')->toArray();
        $offenses = ['1st', '2nd', '3rd'];
        $penalties = \App\Models\PenaltyConfiguration::getActive()
            ->map(function($penalty) {
                return [
                    'value' => $penalty->penalty_code,
                    'label' => $penalty->display_name
                ];
            })->toArray();
        $penaltyCodeMap = \App\Models\PenaltyConfiguration::getForDropdown();

        return view('educator.addViolator', [
            'students' => $students,
            'offenseCategories' => $offenseCategories,
            'severities' => $severities,
            'offenses' => $offenses,
            'penalties' => $penalties,
            'penaltyCodeMap' => $penaltyCodeMap,
            'groupMode' => true,
        ]);
    }

    /**
     * Store a new violator record
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addViolatorSubmit(Request $request)
    {
    Log::info('Add Violator Submit called', ['request_data' => $request->all()]);
    // Debug: Log all request input
    Log::debug('Request input', $request->all());

        try {
            // --- Backend Validation for Termination Policy ---

            // 1. Check for existing termination
            $studentId = $request->input('student_id');
            Log::info('Processing student', ['student_id' => $studentId]);
            $hasTermination = Violation::where('student_id', $studentId)
                ->whereRaw('UPPER(penalty) = ?', ['T'])
                ->whereNotExists(function($sub){
                    $sub->select(\DB::raw(1))
                        ->from('violation_appeals')
                        ->whereColumn('violation_appeals.violation_id', 'violations.id')
                        ->where('violation_appeals.status', 'approved');
                })
                ->exists();
            if ($hasTermination) {
                Log::warning('Proceeding to add violation for already-terminated student (addViolatorSubmit)', ['student_id' => $studentId]);
            }

            // 2. Check if the new violation results in termination
            $violationType = ViolationType::with('severityRelation')->find($request->input('violation_type_id'));
            Log::info('Violation type found', ['violation_type' => $violationType ? $violationType->toArray() : null]);

            if ($violationType && $violationType->severityRelation) {
                $severity = $violationType->severityRelation->severity_name;
                Log::info('Severity determined', ['severity' => $severity]);

                // Get current infraction count for this severity
                $infractionCount = Violation::where('student_id', $studentId)
                    ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                    ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
                    ->where('severities.severity_name', $severity)
                    ->count();

                $nextInfractionNum = $infractionCount + 1;

                // Use database-driven penalty calculation
                $severityConfig = SeverityMaxCount::where('severity_name', $severity)->first();
                if ($severityConfig) {
                    $newPenalty = $nextInfractionNum > $severityConfig->max_count
                        ? $severityConfig->escalated_penalty
                        : $severityConfig->base_penalty;
                } else {
                    $newPenalty = null;
                }

                // If the calculated penalty is termination ('T'), we still allow the record to be saved.
                // Business rules elsewhere (e.g., EducatorController::checkInfractionCount) already prevent
                // adding additional infractions once a termination penalty exists. Retaining the calculated
                // value here ensures the violation is stored correctly without blocking the action.
            }

            // --- End of Validation ---

            // Determine consequence validation rule based on action_taken
            $actionTaken = $request->input('action_taken', true);
            $consequenceRule = $actionTaken ? 'required|string' : 'nullable|string';

            $validated = $request->validate([
                'student_id' => 'required|exists:student_details,student_id',
                'violation_type_id' => 'required|exists:violation_types,id',
                'violation_date' => 'required|date',
                'offense' => 'nullable|string',
                // Penalty is computed server-side; do not require client value
                'penalty' => 'nullable|string',
                'consequence' => $consequenceRule,
                'status' => 'required|in:active,resolved',
                'incident_datetime' => 'nullable|date',
                'incident_place' => 'nullable|string|max:255',
                'incident_details' => 'nullable|string',
                'prepared_by' => 'nullable|string|max:255',
                'action_taken' => 'required|boolean',
            ]);
                // Debug: Log validated data
                Log::debug('Validated data', $validated);
            
            // Start a database transaction for data consistency
            DB::beginTransaction();
            
            // Get student information
            $studentDetails = \App\Models\StudentDetails::where('student_id', $request->student_id)->firstOrFail();
            $student = $studentDetails->user;
            $validated['gender'] = $student->gender ?? 'unknown';
            
            // Get severity from the violation type
            $violationType = ViolationType::with('severityRelation')->findOrFail($request->violation_type_id);
            $validated['severity'] = $violationType->severityRelation->severity_name ?? $request->severity;
            
            // Determine penalty based on SAME-SEVERITY prior ACTIVE violations (exclude no-action and approved appeals)
            $existingSeverityCount = Violation::where('student_id', $validated['student_id'])
                ->where('action_taken', true)
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
                ->where('severities.severity_name', $validated['severity'])
                ->where(function($q){
                    $q->where('violations.status', 'active')
                      ->orWhere('violations.consequence_status', 'active');
                })
                ->where(function($q) {
                    $q->where('violations.status', '!=', 'appeal_approved')
                      ->orWhereNull('violations.status');
                })
                ->whereNotExists(function($sub){
                    $sub->select(\DB::raw(1))
                        ->from('violation_appeals')
                        ->whereColumn('violation_appeals.violation_id', 'violations.id')
                        ->where('violation_appeals.status', 'approved');
                })
                ->count();

            $calculatedPenalty = $this->computePenaltyByRules($validated['severity'], $existingSeverityCount);

            // Enforce: penalties never downgrade (and Probation -> Termination rule)
            $highestExistingPenalty = $this->getHighestExistingPenalty($validated['student_id']);
            if ($highestExistingPenalty === 'Pro') {
                $finalPenalty = 'T';
            } elseif (!empty($highestExistingPenalty)) {
                $finalPenalty = $this->escalateIfRepeat($highestExistingPenalty, $calculatedPenalty);
            } else {
                $finalPenalty = $calculatedPenalty;
            }
            $validated['penalty'] = $finalPenalty;
            
            // Add recorded_by if authenticated
            if (Auth::check()) {
                $validated['recorded_by'] = Auth::id();
            }
            
            // Handle consequence based on action_taken
            if (!$actionTaken) {
                // If no action taken, set a default consequence
                $validated['consequence'] = $validated['consequence'] ?: 'No disciplinary action taken - violation recorded for documentation purposes only.';
            }

            // Create a new violation record (always create a new record)
            $violation = new Violation($validated);

            // Set penalty and action_taken
            $violation->penalty = $finalPenalty ?? $calculatedPenalty; // Always use server-side final penalty
            $violation->action_taken = $actionTaken;

            $violation->save();
            
            // Log the creation
            Log::info('Created new violation record', [
                'id' => $violation->id,
                'student_id' => $validated['student_id'],
                'severity' => $validated['severity'],
                'penalty' => $validated['penalty']
            ]);
            
            // Commit the transaction
            DB::commit();
            
            // Log the successful creation
            Log::info('Violation record created', [
                'id' => $violation->id,
                'student_id' => $validated['student_id'],
                'severity' => $validated['severity'],
                'penalty' => $validated['penalty']
            ]);
            
            return redirect()->route('educator.violation')
                ->with('success', 'Violation record created successfully.');
                
        } catch (ValidationException $e) {
            // Validation errors are automatically handled by Laravel
            return back()->withErrors($e->validator)->withInput();
            
        } catch (ModelNotFoundException $e) {
            // Handle not found errors
            DB::rollBack();
            Log::error('Resource not found when creating violation: ' . $e->getMessage());
            return back()->with('error', 'Student or violation type not found.')
                ->withInput($request->except('password'));
                
        } catch (Exception $e) {
            // Handle any other exceptions
            DB::rollBack();
            Log::error('Error creating violation record: ' . $e->getMessage());
            return back()->with('error', 'An error occurred while creating the violation record. Please try again.')
                ->withInput($request->except('password'));
        }
    }

    /**
     * Store multiple violators in one submission
     */
    public function addGroupViolatorSubmit(Request $request)
    {
    Log::info('Add Group Violator Submit called', ['request_data' => $request->all()]);
    // Debug: Log all request input
    Log::debug('Request input', $request->all());

        // Normalize student_ids when coming from Select2 JSON helper
        if (!$request->has('student_ids') && $request->filled('student_ids_json')) {
            try {
                $decoded = json_decode($request->input('student_ids_json'), true);
                if (is_array($decoded)) {
                    $request->merge(['student_ids' => $decoded]);
                }
            } catch (\Throwable $e) {
                // Ignore; validation will catch if missing
            }
        }

        // Build a server-side consequence value if action_taken is true and no explicit consequence provided
        if ($request->boolean('action_taken') && !$request->filled('consequence')) {
            $cons = (string) $request->input('consequence_select', '');
            $durV = $request->input('duration_value');
            $durU = $request->input('duration_unit');
            if (!empty($cons)) {
                if (!empty($durV) && !empty($durU)) {
                    $request->merge(['consequence' => trim($cons . ' for ' . $durV . ' ' . $durU)]);
                } else {
                    $request->merge(['consequence' => $cons]);
                }
            }
        }

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:student_details,student_id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'consequence' => 'nullable|string',
            'offense' => 'nullable|string',
            'status' => 'required|in:active,resolved',
            'incident_datetime' => 'nullable|date',
            'incident_place' => 'nullable|string|max:255',
            'incident_details' => 'nullable|string',
            'prepared_by' => 'nullable|string|max:255',
            'action_taken' => 'required|boolean',
        ]);
    // Debug: Log validated data
    Log::debug('Validated data', $validated);

        DB::beginTransaction();
        try {
            $violationType = ViolationType::with('severityRelation')->findOrFail($validated['violation_type_id']);
            $severity = $violationType->severityRelation->severity_name ?? 'Low';
            $actionTaken = (bool) $validated['action_taken'];

            $created = 0;
            // Deduplicate posted student IDs to avoid double creation
            $studentIds = array_values(array_unique($validated['student_ids']));
            foreach ($studentIds as $studentId) {
                // Idempotency: skip if a very recent identical record already exists (guards double-clicks)
                $recentDuplicate = Violation::where('student_id', $studentId)
                    ->where('violation_type_id', $validated['violation_type_id'])
                    ->whereDate('violation_date', Carbon::parse($validated['violation_date'])->toDateString())
                    ->where('created_at', '>=', now()->subSeconds(10))
                    ->exists();
                if ($recentDuplicate) {
                    Log::warning('Skipping duplicate group violation detected (recent identical record exists)', [
                        'student_id' => $studentId,
                        'violation_type_id' => $validated['violation_type_id'],
                        'violation_date' => $validated['violation_date']
                    ]);
                    continue;
                }
                // Determine severity and penalty per student
                $calculatedPenalty = $this->determinePenaltyBySeverity($severity, $studentId);

                // Check if student already has a termination penalty (exclude approved appeals)
                $hasTermination = Violation::where('student_id', $studentId)
                    ->whereRaw('UPPER(penalty) = ?', ['T'])
                    ->whereNotExists(function($sub){
                        $sub->select(\DB::raw(1))
                            ->from('violation_appeals')
                            ->whereColumn('violation_appeals.violation_id', 'violations.id')
                            ->where('violation_appeals.status', 'approved');
                    })
                    ->exists();

                if ($hasTermination) {
                    Log::warning('Proceeding to add violation in group for already-terminated student', ['student_id' => $studentId]);
                }
                
                // Get student information
                $studentDetails = \App\Models\StudentDetails::where('student_id', $studentId)->firstOrFail();
                $student = $studentDetails->user;
                $gender = $student->gender ?? 'unknown';

                // Determine offense number for this student scoped to this violation type (do not modify DB schema)
                $existingOffenses = Violation::where('student_id', $studentId)
                    ->where('violation_type_id', $validated['violation_type_id'])
                    ->where(function($query) {
                        $query->where('status', '!=', 'appeal_approved')
                              ->orWhereNull('status');
                    })
                    ->count();
                $offenseCount = $existingOffenses + 1;
                $offenseString = $offenseCount . (function($n){
                    $j = $n % 10; $k = $n % 100;
                    if ($j == 1 && $k != 11) return 'st';
                    if ($j == 2 && $k != 12) return 'nd';
                    if ($j == 3 && $k != 13) return 'rd';
                    return 'th';
                })($offenseCount);

                // Resolve per-student severity via helper (handles missing config consistently)
                $studentSeverity = $this->resolveSeverityForViolationType($validated['violation_type_id']) ?? $severity;

                // === Per-student penalty evaluation ===
                // Compute a final penalty consistent with single submission flow
                // 1) calculate base by severity
                // 2) enforce no downgrade vs highest existing
                // 3) if currently on Probation -> escalate to Termination

                // Highest existing penalty for student
                $highestExistingPenalty = $this->getHighestExistingPenalty($studentId);

                if ($highestExistingPenalty === 'Pro') {
                    $finalPenalty = 'T';
                } elseif (!empty($highestExistingPenalty)) {
                    $finalPenalty = $this->escalateIfRepeat($highestExistingPenalty, $calculatedPenalty);
                } else {
                    $finalPenalty = $calculatedPenalty;
                }

                // Get the student's highest existing penalty (may be null)
                $highestExistingPenalty = $this->getHighestExistingPenalty($studentId);

                // Build a stable ordered penalty map (code => index) using configured sort_order
                $activePenalties = \App\Models\PenaltyConfiguration::getActive()
                    ->sortBy('sort_order')
                    ->pluck('penalty_code')
                    ->values()
                    ->toArray();
                // Normalize keys to uppercase for safe lookup
                $penaltyOrder = [];
                foreach ($activePenalties as $idx => $code) {
                    $penaltyOrder[strtoupper($code)] = (int) $idx;
                }

                // Provide a conservative default mapping if configuration is missing
                $defaultOrder = [
                    'VW' => 0,
                    'WW' => 1,
                    'PRO' => 2,
                    'T' => 3,
                ];

                // Minimal penalty by severity mapping (normalized keys)
                $minPenaltyBySeverity = [
                    'low' => 'VW',
                    'medium' => 'WW',
                    'high' => 'Pro',
                    'very high' => 'T'
                ];

                $sevKey = strtolower($studentSeverity ?? 'low');
                // If student is already terminated, skip (safety check)
                if (!empty($highestExistingPenalty) && $highestExistingPenalty === 'T') {
                    Log::info('Skipping student in group: already terminated (safety)', ['student_id' => $studentId]);
                    $terminatedStudents[] = $studentId;
                    continue;
                }

                // If student is on Probation, escalate to Termination per rule
                if (!empty($highestExistingPenalty) && strtoupper($highestExistingPenalty) === 'PRO') {
                    Log::info('Escalating to Termination because student is on Probation (group)', ['student_id' => $studentId, 'highest_existing_penalty' => $highestExistingPenalty]);
                    $finalPenalty = 'T';
                    // keep calculated values null - decision made by existing status
                    $calculatedPenalty = null;
                    $minPenalty = null;
                } elseif ($sevKey === 'very high') {
                    // Very High severity -> immediate termination
                    Log::info('Very High severity -> immediate Termination', ['student_id' => $studentId, 'violation_type_id' => $validated['violation_type_id']]);
                    $finalPenalty = 'T';
                    $calculatedPenalty = null;
                    $minPenalty = null;
                } else {
                    // Compute prior ACTIVE same-severity count for this student (action taken, not appeal-approved)
                    $existingSeverityCount = Violation::where('student_id', $studentId)
                        ->where('action_taken', true)
                        ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                        ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
                        ->where('severities.severity_name', $studentSeverity)
                        ->where(function($q){
                            $q->where('violations.status', 'active')
                              ->orWhere('violations.consequence_status', 'active');
                        })
                        ->where(function($q) {
                            $q->where('violations.status', '!=', 'appeal_approved')
                              ->orWhereNull('violations.status');
                        })
                        ->whereNotExists(function($sub){
                            $sub->select(\DB::raw(1))
                                ->from('violation_appeals')
                                ->whereColumn('violation_appeals.violation_id', 'violations.id')
                                ->where('violation_appeals.status', 'approved');
                        })
                        ->count();
                    $calculatedPenalty = $this->computePenaltyByRules($studentSeverity, $existingSeverityCount);

                    // Ensure calculated penalty is at least the minimal penalty corresponding to the severity
                    $minPenalty = $minPenaltyBySeverity[$sevKey] ?? 'VW';

                    // Normalize codes for lookup
                    $calculatedCode = strtoupper((string) $calculatedPenalty);
                    $minCode = strtoupper((string) $minPenalty);
                    $existingCode = strtoupper((string) ($highestExistingPenalty ?? ''));

                    $calculatedRank = $penaltyOrder[$calculatedCode] ?? ($defaultOrder[$calculatedCode] ?? null);
                    $minRank = $penaltyOrder[$minCode] ?? ($defaultOrder[$minCode] ?? 0);
                    if ($calculatedRank === null) {
                        // If calculated penalty code not found in config, fallback to min penalty
                        Log::warning('Calculated penalty code not found in configuration, falling back to minimal penalty', ['student_id' => $studentId, 'calculated' => $calculatedPenalty, 'fallback' => $minPenalty]);
                        $calculatedCode = $minCode;
                        $calculatedRank = $minRank;
                        $calculatedPenalty = $minPenalty;
                    }

                    // Never downgrade: if existing highest penalty has greater order index, keep that
                    $existingRank = $penaltyOrder[$existingCode] ?? ($defaultOrder[$existingCode] ?? -1);
                    if ($existingRank > $calculatedRank) {
                        $finalPenalty = $highestExistingPenalty;
                    } else {
                        $finalPenalty = $calculatedPenalty;
                    }
                    // Ensure repeat at same level escalates one step for this student
                    $finalPenalty = $this->escalateIfRepeat($highestExistingPenalty, $finalPenalty);
                }
                Log::info('Final penalty determined for student', ['student_id' => $studentId, 'final_penalty' => $finalPenalty, 'highest_existing_penalty' => $highestExistingPenalty, 'severity' => $studentSeverity, 'offenseCount' => $offenseCount]);

                // Extra debug: log penalty ranks and decision factors to aid troubleshooting
                $penaltyRankDebug = [
                    'calculated_rank' => $calculatedRank,
                    'min_rank' => $minRank,
                    'existing_rank' => $existingRank,
                    'calculated_code' => $calculatedCode,
                    'min_code' => $minCode,
                    'existing_code' => $existingCode,
                ];
                Log::debug('Penalty decision context', ['student_id' => $studentId, 'calculatedPenalty' => $calculatedPenalty ?? null, 'minPenalty' => $minPenalty ?? null, 'highestExistingPenalty' => $highestExistingPenalty, 'ranks' => $penaltyRankDebug]);
                // Global rule: every new violation escalates one level from the decided penalty (group)
                $finalPenalty = $this->escalateOneLevel($finalPenalty);

                // Handle consequence based on action_taken
            $consequence = $validated['consequence'] ?? $request->input('consequence', '');
            if (!$actionTaken && empty($consequence)) {
                $consequence = 'No disciplinary action taken - violation recorded for documentation purposes only.';
            }
            if ($actionTaken && empty($consequence)) {
                $consequence = 'Action taken; consequence not specified.';
            }


                // Only set fields that actually exist on the violations table/schema.
                // Some environments/databases don't have columns like `offense`, `action_taken`,
                // `incident_datetime`, `incident_place`, `incident_details`, or `prepared_by`.
                // Persist only the safe/core attributes to avoid SQL errors.
                // include the student-specific severity and append offense info to consequence if not already present
                if (empty($consequence)) {
                    $consequence = "Offense: {$offenseString}";
                } else {
                    $consequence = trim($consequence) . " (Offense: {$offenseString})";
                }

                $violationData = [
                    'student_id' => $studentId,
                    'violation_type_id' => $validated['violation_type_id'],
                    'violation_date' => $validated['violation_date'],
                    // Use finalPenalty when available; fallback to calculatedPenalty
                    'penalty' => $finalPenalty ?? $calculatedPenalty,
                    'consequence' => $consequence,
                    'status' => $validated['status'],
                    'severity' => $studentSeverity,
                    // Persist incident details for group submissions so they are visible in the view page
                    'incident_datetime' => $validated['incident_datetime'] ?? null,
                    'incident_place' => $validated['incident_place'] ?? null,
                    'incident_details' => $validated['incident_details'] ?? null,
                    'prepared_by' => $validated['prepared_by'] ?? null,
                ];

                // Instantiate model and set lifecycle fields
                $violation = new Violation($violationData);
                $violation->action_taken = $actionTaken;
                if (!$actionTaken) {
                    $violation->consequence_status = 'resolved';
                } else {
                    // Validate and set duration fields safely (DB enum allows: hours, days, weeks, months)
                    $allowedUnits = ['hours', 'days', 'weeks', 'months'];
                    $durVal = (int) ($request->input('duration_value') ?? 0);
                    $durUnit = (string) $request->input('duration_unit');
                    if ($durVal > 0 && in_array($durUnit, $allowedUnits, true)) {
                        $violation->consequence_duration_value = $durVal;
                        $violation->consequence_duration_unit = $durUnit;
                        $violation->consequence_status = 'active';
                        $violation->consequence_start_date = now();
                        // calculate end date only with validated numeric value/unit
                        $violation->consequence_end_date = $violation->calculateConsequenceEndDate();
                    } else {
                        // Clear invalid duration inputs to avoid Carbon errors
                        $violation->consequence_duration_value = null;
                        $violation->consequence_duration_unit = null;
                        $violation->consequence_start_date = null;
                        $violation->consequence_end_date = null;
                        $violation->consequence_status = 'active';
                    }
                }

                if (Auth::check()) {
                    try {
                        $authId = Auth::id();
                        if (!empty($authId) && \App\Models\User::where('user_id', $authId)->exists()) {
                            $violation->recorded_by = $authId;
                        } else {
                            // Leave recorded_by null if user cannot be verified to avoid FK violations
                            \Log::warning('Skipping recorded_by due to missing user in pnph_users', ['auth_id' => $authId]);
                        }
                    } catch (\Throwable $ex) {
                        // Safety: do not block save on lookup errors
                        \Log::warning('Error verifying recorded_by user', ['error' => $ex->getMessage()]);
                    }
                }

                $violation->save();
                $created++;
            }

            DB::commit();
            return redirect()->route('educator.violation')->with('success', "Created {$created} violation record(s) successfully.");
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error creating group violations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'An error occurred while creating the group violations: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing a violation
     */
    public function editViolation($id)
    {
        $violation = Violation::findOrFail($id);
        $students = User::role('student')->get();
        $offenseCategories = OffenseCategory::all();
        $violationTypes = ViolationType::where('offense_category_id', $violation->violationType->offense_category_id)->get();
        
        return view('educator.editViolation', compact('violation', 'students', 'offenseCategories', 'violationTypes'));
    }

    /**
     * Update a violation record
     */
    public function updateViolation(Request $request, $id)
    {
        // --- Backend Validation for Termination Policy ---

        // 1. Check if the edited violation results in termination
        $violationType = ViolationType::with('severityRelation')->find($request->input('violation_type_id'));
        if ($violationType && $violationType->severityRelation) {
            $severity = $violationType->severityRelation->severity_name;
            $newPenalty = $this->determinePenaltyBySeverity($severity, $request->input('student_id'));

            if ($newPenalty === 'T') {
                return back()->withInput()->with('error', 'Update failed: This violation results in a penalty of Termination of Contract.');
            }
        } else {
            $newPenalty = $request->input('penalty');
        }

        // --- End of Validation ---

        $validated = $request->validate([
            'student_id' => 'required|exists:student_details,student_id',
            'violation_type_id' => 'required|exists:violation_types,id',
            'violation_date' => 'required|date',
            'penalty' => 'required|string',
            'consequence' => 'nullable|string',
            'status' => 'required|in:active,resolved',
            'incident_datetime' => 'nullable|date',
            'incident_place' => 'nullable|string|max:255',
            'incident_details' => 'nullable|string',
            'prepared_by' => 'nullable|string|max:255',
        ]);
        
        // Get student gender for the record
        $studentDetails = \App\Models\StudentDetails::where('student_id', $request->student_id)->first();

        // Handle case where student might not be found
        if ($studentDetails && $studentDetails->user) {
            $validated['gender'] = $studentDetails->user->gender;
        } else {
            // Default to a placeholder value if student not found
            $validated['gender'] = 'unknown';
            Log::warning('Student not found when updating violation', ['student_id' => $request->student_id]);
        }
        
        // Get severity from the violation type
        $violationType = ViolationType::find($request->violation_type_id);
        
        // Handle case where violation type might not have a valid severity
        if ($violationType && $violationType->severity_id) {
            $severity = Severity::find($violationType->severity_id);
            
            if ($severity) {
                $validated['severity'] = $severity->severity_name;
            } else {
                // Default to a placeholder value if severity not found
                $validated['severity'] = 'Medium';
                Log::warning('Severity not found when updating violation', [
                    'violation_type_id' => $request->violation_type_id,
                    'severity_id' => $violationType->severity_id
                ]);
            }
        } else {
            // Default to a placeholder value if violation type or severity_id not found
            $validated['severity'] = 'Medium';
            Log::warning('Violation type not found or missing severity_id when updating violation', [
                'violation_type_id' => $request->violation_type_id
            ]);
        }
        
        $violation = Violation::findOrFail($id);

        // Set penalty, severity and default action_taken
        $violation->penalty = $request->input('penalty');
        $violation->severity = $severity ?? $request->input('severity');
        $violation->action_taken = false; // Default to false since field was removed from form

        // Update the rest of the validated fields
        $violation->fill($validated);
        $violation->save();
        
        return redirect()->route('educator.violation')
            ->with('success', 'Violation updated successfully.');
    }

    // Removed legacy determinePenalty() as it is unused and duplicated by determinePenaltyBySeverity()/computePenaltyByRules().

    /**
     * Resolve the severity name for a given violation type ID.
     * Provides a safe fallback to 'Low' if not found or misconfigured.
     *
     * @param int|string|null $violationTypeId
     * @return string Severity name (e.g., Low, Medium, High, Very High)
     */
    private function resolveSeverityForViolationType($violationTypeId)
    {
        try {
            if (empty($violationTypeId)) {
                return 'Low';
            }
            $type = ViolationType::with('severityRelation')->find($violationTypeId);
            if ($type && $type->severityRelation && !empty($type->severityRelation->severity_name)) {
                return $type->severityRelation->severity_name;
            }
        } catch (\Throwable $e) {
            \Log::warning('resolveSeverityForViolationType failed', [
                'violation_type_id' => $violationTypeId,
                'error' => $e->getMessage()
            ]);
        }
        return 'Low';
    }

    /**
     * Escalate a penalty code by one level using PenaltyConfiguration order.
     * Returns the same code if already at maximum.
     */
    private function escalateOneLevel($penaltyCode)
    {
        // Enforce fixed sequence: Verbal Warning -> Written Warning -> Probationary -> Termination
        $sequence = ['VW','WW','PRO','T'];
        $current = strtoupper((string)$penaltyCode);
        $idx = array_search($current, $sequence, true);
        if ($idx === false) { $idx = 0; }
        $nextIdx = min($idx + 1, count($sequence) - 1);
        $next = $sequence[$nextIdx];
        // Return canonical casing for Probationary
        return $next === 'PRO' ? 'Pro' : $next;
    }

/**
 * Determine penalty based on severity and offense count within that severity
 *
 * @param string $severity The severity level (Low, Medium, High, Very High)
 * @param int $studentId The student ID to count previous offenses
 * @return string The penalty code (VW, WW, Pro, T)
 */
private function determinePenaltyBySeverity($severity, $studentId = null)
{
    $severity = strtolower($severity);

    // Count existing violations with the same severity for this student
    // Only count violations where action_taken = true and exclude violations resolved by approved appeals
    $offenseCount = 1; // Default to 1st offense
    if ($studentId) {
        $existingOffenses = Violation::where('student_id', $studentId)
            ->where('action_taken', true) // Only count violations where action was taken
            ->where(function($query) {
                // Exclude violations that were resolved due to approved appeals
                $query->where('status', '!=', 'resolved')
                      ->orWhere(function($subQuery) {
                          // If status is resolved, only exclude if it was due to an approved appeal
                          $subQuery->where('status', 'resolved')
                                   ->whereDoesntHave('appeals', function($appealQuery) {
                                       $appealQuery->where('status', 'approved');
                                   });
                      });
            })
            ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
            ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
            ->where('severities.severity_name', $severity)
            ->count();
        $offenseCount = $existingOffenses + 1;
    }

    // Get severity configuration from database
    $severityConfig = SeverityMaxCount::where('severity_name', ucfirst($severity))->first();

    // If no configuration found, use default configuration
    if (!$severityConfig) {
        Log::warning('No severity configuration found for severity: ' . $severity . ', using defaults');

        // Default severity configurations
        $defaultConfigs = [
            'low' => ['max_count' => 1, 'base_penalty' => 'VW', 'escalated_penalty' => 'WW'],
            'medium' => ['max_count' => 1, 'base_penalty' => 'WW', 'escalated_penalty' => 'Pro'],
            'high' => ['max_count' => 1, 'base_penalty' => 'Pro', 'escalated_penalty' => 'T'],
            'very high' => ['max_count' => 1, 'base_penalty' => 'T', 'escalated_penalty' => 'T'],
        ];

        $config = $defaultConfigs[$severity] ?? $defaultConfigs['low'];
        $maxCount = $config['max_count'];
        $basePenalty = $config['base_penalty'];
        $escalatedPenalty = $config['escalated_penalty'];
    } else {
        $maxCount = $severityConfig->max_count;
        $basePenalty = $severityConfig->base_penalty;
        $escalatedPenalty = $severityConfig->escalated_penalty;
    }

    // Implement cyclic penalty escalation based on offense count
    return $this->calculateEscalatedPenalty($offenseCount, $maxCount, $basePenalty, $escalatedPenalty);
}

/**
 * Calculate escalated penalty based on offense count and maximum count thresholds
 * Implements cyclic escalation: every max_count range gets the next penalty level
 * Example for Low severity (max_count=3): 1-3=VW, 4-6=WW, 7-9=Pro, 10+=T
 *
 * @param int $offenseCount Current offense count for this severity
 * @param int $maxCount Maximum count before escalation for this severity
 * @param string $basePenalty Base penalty for this severity
 * @param string $escalatedPenalty Escalated penalty for this severity
 * @return string The calculated penalty code
 */
private function calculateEscalatedPenalty($offenseCount, $maxCount, $basePenalty, $escalatedPenalty)
{
    // Get all penalty configurations ordered by severity (sort_order)
    // Use query builder instead of getActive() to avoid Collection orderBy error
    $penalties = \App\Models\PenaltyConfiguration::where('is_active', true)
        ->orderBy('sort_order')
        ->pluck('penalty_code')
        ->toArray();

    // Find the index of the base penalty
    $basePenaltyIndex = array_search($basePenalty, $penalties);
    if ($basePenaltyIndex === false) {
        // If base penalty not found, return escalated penalty as fallback
        return $escalatedPenalty;
    }

    // Calculate which penalty range the offense count falls into
    // Range 1: 1 to maxCount (use base penalty)
    // Range 2: (maxCount + 1) to (2 * maxCount) (use next penalty)
    // Range 3: (2 * maxCount + 1) to (3 * maxCount) (use next penalty)
    // etc.

    $penaltyRangeIndex = ceil($offenseCount / $maxCount) - 1;

    // Calculate target penalty index
    $targetPenaltyIndex = $basePenaltyIndex + $penaltyRangeIndex;

    // Ensure we don't exceed the maximum penalty level
    $maxPenaltyIndex = count($penalties) - 1;
    $targetPenaltyIndex = min($targetPenaltyIndex, $maxPenaltyIndex);

    // Return the calculated penalty
    return $penalties[$targetPenaltyIndex];
}

/**
 * Get the highest penalty for a student from their existing violations
 * 
 * @param int $studentId
 * @return string|null
 */
private function getHighestExistingPenalty($studentId)
{
    // Get penalty rankings from database configuration
    $penaltyRanks = \App\Models\PenaltyConfiguration::getActive()
        ->pluck('sort_order', 'penalty_code')
        ->toArray();
    
    // Get all violations for this student that should count towards highest penalty
    $violations = Violation::where('student_id', $studentId)
        ->where('action_taken', true)
        ->where(function($q) {
            $q->where('status', '!=', 'appeal_approved')
              ->orWhereNull('status');
        })
        ->whereNotExists(function($sub){
            $sub->select(\DB::raw(1))
                ->from('violation_appeals')
                ->whereColumn('violation_appeals.violation_id', 'violations.id')
                ->where('violation_appeals.status', 'approved');
        })
        ->where(function($q){
            $q->whereNull('penalty')->orWhere('penalty', '!=', 'NONE');
        })
        ->get();
    
    if ($violations->isEmpty()) {
        return null;
    }
    
    // Find the highest penalty
    $highestRank = 0;
    $highestPenalty = null;
    
    foreach ($violations as $violation) {
        $rank = $penaltyRanks[$violation->penalty] ?? 0;
        if ($rank > $highestRank) {
            $highestRank = $rank;
            $highestPenalty = $violation->penalty;
        }
    }
    
    Log::info('Found highest existing penalty for student', [
        'student_id' => $studentId,
        'highest_penalty' => $highestPenalty,
        'highest_rank' => $highestRank,
        'violation_count' => $violations->count()
    ]);
    
    return $highestPenalty;
}


    /**
     * Compute penalty strictly by rules for a given severity and the number of prior ACTIVE
     * violations in the same severity.
     *
     * @param string $severity               Severity name (e.g., Low, Medium, High, Very High)
     * @param int    $existingSeverityCount  Count of prior active violations for this severity
     * @return string                         Penalty code (e.g., VW, WW, Pro, T)
     */
    private function computePenaltyByRules($severity, $existingSeverityCount)
    {
        $sevKey = strtolower(trim((string)$severity));

        // Determine configs from DB, fallback to conservative defaults
        $config = SeverityMaxCount::where('severity_name', ucfirst($sevKey))->first();
        if (!$config) {
            // Default configuration if DB row is missing
            $defaults = [
                'low' => ['max_count' => 1, 'base_penalty' => 'VW', 'escalated_penalty' => 'WW'],
                'medium' => ['max_count' => 1, 'base_penalty' => 'WW', 'escalated_penalty' => 'Pro'],
                'high' => ['max_count' => 1, 'base_penalty' => 'Pro', 'escalated_penalty' => 'T'],
                'very high' => ['max_count' => 1, 'base_penalty' => 'T', 'escalated_penalty' => 'T'],
            ];
            $chosen = $defaults[$sevKey] ?? $defaults['low'];
            $maxCount = (int) $chosen['max_count'];
            $basePenalty = (string) $chosen['base_penalty'];
            $escalatedPenalty = (string) $chosen['escalated_penalty'];
        } else {
            $maxCount = (int) $config->max_count;
            $basePenalty = (string) $config->base_penalty;
            $escalatedPenalty = (string) $config->escalated_penalty;
        }

        // existingSeverityCount counts prior ACTIVE same-severity violations; next offense is +1
        $offenseCount = (int) $existingSeverityCount + 1;

        return $this->calculateEscalatedPenalty($offenseCount, $maxCount, $basePenalty, $escalatedPenalty);
    }

    /**
     * Ensure penalties never downgrade; if the newly calculated penalty equals the highest existing
     * one, escalate by one step. Uses configured penalty order from PenaltyConfiguration.sort_order.
     *
     * @param string|null $highestExistingPenalty  Highest penalty the student already has
     * @param string|null $newPenalty              Newly calculated penalty
     * @return string|null                         Final non-decreasing penalty
     */
    private function escalateIfRepeat($highestExistingPenalty, $newPenalty)
    {
        // If nothing to compare against, return the new penalty as-is
        if (empty($highestExistingPenalty)) {
            return $newPenalty;
        }

        // Build an ordered list of active penalty codes by sort_order
        $ordered = \App\Models\PenaltyConfiguration::getActive()
            ->sortBy('sort_order')
            ->pluck('penalty_code')
            ->values()
            ->toArray();

        // Provide a conservative fallback ordering if configuration is incomplete
        if (empty($ordered)) {
            $ordered = ['VW', 'WW', 'Pro', 'T'];
        }

        // Normalize to uppercase for indexing while preserving original case for return
        $index = [];
        foreach ($ordered as $i => $code) {
            $index[strtoupper($code)] = $i;
        }

        $existingCode = strtoupper((string) $highestExistingPenalty);
        $newCode = strtoupper((string) $newPenalty);

        // Unknown codes: keep the safer (higher) one if we can determine; otherwise prefer new
        if (!array_key_exists($existingCode, $index) || !array_key_exists($newCode, $index)) {
            return $newPenalty ?? $highestExistingPenalty;
        }

        $existingRank = $index[$existingCode];
        $newRank = $index[$newCode];

        // Never downgrade
        if ($newRank < $existingRank) {
            return $highestExistingPenalty;
        }

        // If same level, escalate one step where possible
        if ($newRank === $existingRank) {
            $nextRank = min($existingRank + 1, count($ordered) - 1);
            return $ordered[$nextRank];
        }

        // Otherwise it's already an escalation
        return $newPenalty;
    }

    /**
     * Display student violations
     */
    public function studentViolations()
    {
        // Get all violations with student information and paginate them
        $violations = Violation::with(['student', 'violationType'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('educator.violation', ['violations' => $violations]);
    }
    
    /**
     * Get students who committed a specific violation
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStudents(Request $request)
    {
        try {
            $violationName = $request->query('violation_name');
            $period = $request->query('period', 'month');
            $batch = $request->query('batch', 'all');

            if (!$violationName) {
                return response()->json([
                    'success' => false,
                    'message' => 'Violation name is required'
                ], 400);
            }

            // Build the base query
            $query = DB::table('violations')
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                ->where('violation_types.violation_name', $violationName)
                ;

            // Apply period filter
            switch ($period) {
                case 'week':
                    $query->where('violations.violation_date', '>=', now()->subWeek());
                    break;
                case 'month':
                    $query->where('violations.violation_date', '>=', now()->subMonth());
                    break;
                case 'last_month':
                    $query->whereBetween('violations.violation_date', [
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    ]);
                    break;
                case 'year':
                    $query->where('violations.violation_date', '>=', now()->subYear());
                    break;
            }

            // Apply batch filter
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 2025% for Class 2025)
                $query->where('users.student_id', 'like', $batch . '%');
            }

            // Get students with violation details
            $students = $query->select(
                'pnph_users.user_fname',
                'pnph_users.user_lname',
                'student_details.student_id',
                'violations.violation_date',
                'violations.penalty'
            )
            ->orderBy('violations.violation_date', 'desc')
            ->get()
            ->map(function($student) {
                return [
                    'name' => ($student->user_fname ?? '') . ' ' . ($student->user_lname ?? ''),
                    'student_id' => $student->student_id,
                    'violation_date' => $student->violation_date ? date('M d, Y', strtotime($student->violation_date)) : 'Unknown Date',
                    'penalty' => $student->penalty ?? 'No penalty assigned'
                ];
            });

            return response()->json([
                'success' => true,
                'students' => $students,
                'count' => $students->count()
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching violation students: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch violation students'
            ], 500);
        }
    }

    /**
     * Get detailed violations for a specific student
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStudentViolations(Request $request)
    {
        try {
            $studentId = $request->query('student_id');

            if (!$studentId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Student ID is required'
                ], 400);
            }

            // Get violations for the specific student with related data
            $violations = Violation::with(['violationType', 'violationType.offenseCategory'])
                ->where('student_id', $studentId)
                
                ->orderBy('violation_date', 'desc')
                ->get()
                ->map(function($violation) {
                    return [
                        'id' => $violation->id,
                        'violation_name' => $violation->violationType->violation_name ?? 'Unknown Violation',
                        'category_name' => $violation->violationType->offenseCategory->category_name ?? 'Unknown Category',
                        'violation_date' => $violation->violation_date ? date('M d, Y', strtotime($violation->violation_date)) : 'Unknown Date',
                        'severity' => $violation->severity ?? 'Unknown',
                        'offense' => $violation->offense ?? 'Unknown',
                        'penalty' => $this->formatPenalty($violation->penalty ?? 'Unknown'),
                        'consequence' => $violation->consequence ?? 'No consequence specified',
                        'status' => $violation->status
                    ];
                });

            return response()->json([
                'success' => true,
                'violations' => $violations,
                'total_count' => $violations->count()
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching student violations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching student violations',
                'violations' => [],
                'total_count' => 0
            ], 500);
        }
    }

    /**
     * Format penalty code to readable text
     *
     * @param string $penalty
     * @return string
     */
    private function formatPenalty($penalty)
    {
        // Get penalty display name from database configuration
        $penaltyConfig = \App\Models\PenaltyConfiguration::where('penalty_code', $penalty)->first();
        return $penaltyConfig ? $penaltyConfig->display_name : $penalty;
    }

    /**
     * Count active violations filtered by batch
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function countViolationsByBatchFilter(Request $request)
    {
        $batch = $request->query('batch', 'all');

        // Log for debugging
        Log::info('Active violation count for batch ' . $batch);

        return response()->json($this->getViolationCountResponse($batch, 'active'));
    }
    
    /**
     * Get violation statistics by time period for the educator dashboard
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getViolationStatsByPeriod(Request $request)
    {
        try {
            // Get and validate the period parameter
            $period = $this->validatePeriod($request->input('period', 'month'));
            $batch = $request->input('batch', 'all');
            
            // Get date range for the selected period
            $dateRange = $this->getDateRangeForPeriod($period);
            $startDate = $dateRange['startDate'];
            $endDate = $dateRange['endDate'];
            $relevantMonths = $dateRange['relevantMonths'] ?? [];
            
            // Log the date range for debugging
            Log::info('Violation stats query', [
                'period' => $period,
                'batch' => $batch,
                'dateRange' => [
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d')
                ]
            ]);
            
            // Get violation statistics directly from the database with optimized query
            $violationStats = $this->getViolationStatsFromDatabase($period, $startDate, $endDate, $relevantMonths, $batch);
            
            // Log the results and return
            Log::info('Violation stats results count: ' . count($violationStats));
            return response()->json($violationStats);
            
        } catch (Exception $e) {
            Log::error('Error in getViolationStatsByPeriod: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'error' => 'An error occurred while retrieving violation statistics.',
                'message' => app()->environment('production') ? null : $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get violation statistics from database
     * 
     * @param string $period
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param array $relevantMonths
     * @return array
     */
    private function getViolationStatsFromDatabase($period, $startDate, $endDate, $relevantMonths, $batch = 'all')
    {
        try {
            // Query to get violation statistics grouped by violation type
            // Only count violations where action was taken and exclude approved appeals
            $query = DB::table('violations')
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->select(
                    'violation_types.id',
                    'violation_types.violation_name',
                    DB::raw('COUNT(violations.id) as count')
                )
                ->where('violations.action_taken', true) // Only count violations where action was taken
                ->where('violations.status', '!=', 'appeal_approved') // Exclude approved appeals
                ->whereBetween('violations.violation_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                
            // Apply batch filter if specified
            if ($batch !== 'all') {
                // Filter based on the student_id prefix (e.g., 2025% for Class 2025)
                $query->where('violations.student_id', 'like', $batch . '%');
            }
            
            $stats = $query->groupBy('violation_types.id', 'violation_types.violation_name')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get();
            
            // Log the results for debugging
            Log::info('Violation stats query results', [
                'count' => count($stats),
                'period' => $period,
                'batch' => $batch,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d')
            ]);
            
            return $stats;
        } catch (\Exception $e) {
            Log::error('Error in getViolationStatsFromDatabase: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate the period parameter
     *
     * @param string $period
     * @return string
     */
    private function validatePeriod($period)
    {
        $validPeriods = ['month', 'last_month', 'last_3_months', 'year'];
        
        if (!in_array($period, $validPeriods)) {
            // Default to 'month' if invalid period provided
            return 'month';
        }
        
        return $period;
    }
    
    /**
     * Get date range for the selected period
     * 
     * @param string $period
     * @return array
     */
    private function getDateRangeForPeriod($period)
    {
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        
        switch ($period) {
            case 'month':
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
                break;
                
            case 'last_month':
                $lastMonth = $now->copy()->subMonth();
                $startDate = Carbon::createFromDate($lastMonth->year, $lastMonth->month, 1)->startOfDay();
                $endDate = Carbon::createFromDate($lastMonth->year, $lastMonth->month, 1)->endOfMonth()->endOfDay();
                break;
                
            case 'last_3_months':
                $startDate = $now->copy()->subMonths(3)->startOfMonth()->startOfDay();
                $endDate = $now->copy()->endOfDay();
                break;
                
            case 'year':
                $startDate = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, 12, 31)->endOfDay();
                break;
                
            default:
                // Default to current month
                $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfDay();
                $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->endOfDay();
        }
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }

    // End of ViolationController class methods
    
    /**
     * Get date range for the 'month' period
     *
     * @param int $currentMonth
     * @param int $currentYear
     * @param array $specificMonths
     * @return array
     */
    private function getMonthPeriodRange($currentMonth, $currentYear, $specificMonths)
    {
        $targetMonth = null;
        $targetYear = null;
        $startDate = null;
        $endDate = null;
        
        // If current month is one of the specific months, use it
        if (in_array($currentMonth, $specificMonths)) {
            $targetMonth = $currentMonth;
            $targetYear = $currentYear;
            $startDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth();
        } else {
            // Find the most recent specific month
            $mostRecentMonth = null;
            foreach ($specificMonths as $month) {
                if ($month < $currentMonth && ($mostRecentMonth === null || $month > $mostRecentMonth)) {
                    $mostRecentMonth = $month;
                }
            }
            
            // If no recent month found, use the last month of the year
            if ($mostRecentMonth === null) {
                $targetMonth = max($specificMonths);
                $targetYear = $currentYear - 1;
            } else {
                $targetMonth = $mostRecentMonth;
                $targetYear = $currentYear;
            }
            
            $startDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->endOfMonth();
        }
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'targetMonth' => $targetMonth,
            'targetYear' => $targetYear
        ];
    }
    
    /**
     * Get date range for the 'last_month' period
     *
     * @param int $currentMonth
     * @param int $currentYear
     * @param array $specificMonths
     * @return array
     */
    private function getLastMonthPeriodRange($currentMonth, $currentYear, $specificMonths)
    {
        $targetMonth = null;
        $targetYear = null;
        
        // Find the previous specific month
        $currentMonthIndex = array_search($currentMonth, $specificMonths);
        
        if ($currentMonthIndex !== false && $currentMonthIndex > 0) {
            // Previous month in the same year
            $targetMonth = $specificMonths[$currentMonthIndex - 1];
            $targetYear = $currentYear;
        } else {
            // Previous month is in the previous year
            $targetMonth = end($specificMonths);
            $targetYear = $currentYear - 1;
        }
        
        $startDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($targetYear, $targetMonth, 1)->endOfMonth();
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'targetMonth' => $targetMonth,
            'targetYear' => $targetYear
        ];
    }
    
    /**
     * Get date range for the 'last_3_months' period
     *
     * @param int $currentMonth
     * @param int $currentYear
     * @param array $specificMonths
     * @return array
     */
    private function getLast3MonthsPeriodRange($currentMonth, $currentYear, $specificMonths)
    {
        $relevantMonths = [];
        
        // Find the current or most recent specific month
        $recentMonth = null;
        $relevantYear = $currentYear;
        
        if (in_array($currentMonth, $specificMonths)) {
            $recentMonth = $currentMonth;
        } else {
            foreach ($specificMonths as $month) {
                if ($month < $currentMonth && ($recentMonth === null || $month > $recentMonth)) {
                    $recentMonth = $month;
                }
            }
            
            if ($recentMonth === null) {
                $recentMonth = max($specificMonths);
                $relevantYear = $currentYear - 1;
            }
        }
        
        // Add the recent month and find the two before it
        $relevantMonths[] = ['month' => $recentMonth, 'year' => $relevantYear];
        
        // Find the two previous specific months
        for ($i = 0; $i < 2; $i++) {
            $currentIndex = array_search($recentMonth, $specificMonths);
            if ($currentIndex > 0) {
                // Previous month in the same year
                $recentMonth = $specificMonths[$currentIndex - 1];
            } else {
                // Previous month is in the previous year
                $recentMonth = end($specificMonths);
                $relevantYear--;
            }
            $relevantMonths[] = ['month' => $recentMonth, 'year' => $relevantYear];
        }
        
        // Set the date range to cover all relevant months
        $earliestMonth = end($relevantMonths);
        $startDate = Carbon::createFromDate($earliestMonth['year'], $earliestMonth['month'], 1)->startOfMonth();
        $latestMonth = reset($relevantMonths);
        $endDate = Carbon::createFromDate($latestMonth['year'], $latestMonth['month'], 1)->endOfMonth();
        
        return [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'relevantMonths' => $relevantMonths
        ];
    }
    
    // Using the studentsByPenalty method from EducatorController instead
    
    /**
     * Check if a student has existing violations with the same severity
     * and return the appropriate offense count
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function checkExistingViolations(Request $request)
    {
        try {
            $studentId = $request->query('student_id');
            $violationTypeId = $request->query('violation_type_id');
            
            if (!$studentId || !$violationTypeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Missing student ID or violation type ID'
                ]);
            }
            
            // Get the severity for the violation type
            $violationType = ViolationType::with('severityRelation')->findOrFail($violationTypeId);
            $severity = $violationType->severityRelation->severity_name ?? null;
            
            if (!$severity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not determine severity for violation type'
                ]);
            }
            
            // Count existing ACTIVE violations in the SAME SEVERITY for this student
            // Rule: escalation continues as long as last violation is still active
            $existingOffenses = Violation::where('student_id', $studentId)
                ->where('action_taken', true)
                // Same severity via join
                ->join('violation_types', 'violations.violation_type_id', '=', 'violation_types.id')
                ->join('severities', 'violation_types.severity_id', '=', 'severities.id')
                ->where('severities.severity_name', $severity)
                // Only count active/unresolved ones
                ->where(function($q){
                    $q->where('violations.status', 'active')
                      ->orWhere('violations.consequence_status', 'active');
                })
                // Exclude legacy and approved appeals
                ->where(function($q){ $q->where('violations.status', '!=', 'appeal_approved')->orWhereNull('violations.status'); })
                ->whereNotExists(function($sub){
                    $sub->select(\DB::raw(1))
                        ->from('violation_appeals')
                        ->whereColumn('violation_appeals.violation_id', 'violations.id')
                        ->where('violation_appeals.status', 'approved');
                })
                ->count();
            $offenseCount = $existingOffenses + 1; // This will be the offense number for the new violation

            // Get severity configuration from database
            $severityConfig = SeverityMaxCount::where('severity_name', $severity)->first();

            // If no configuration found, log error and return default response
            if (!$severityConfig) {
                Log::error('No severity configuration found for severity: ' . $severity);
                return response()->json([
                    'offense_count' => 1,
                    'max_count' => 1,
                    'penalty' => 'VW',
                    'escalated' => false,
                    'base_penalty' => 'VW',
                    'escalated_penalty' => 'WW'
                ]);
            }

            $maxCount = $severityConfig->max_count;
            $basePenalty = $severityConfig->base_penalty;
            $escalatedPenalty = $severityConfig->escalated_penalty;
            $isEscalated = $offenseCount > $maxCount;

            // Calculate the penalty based on severity and offense count
            $calculatedPenalty = $this->determinePenaltyBySeverity($severity, $studentId);

            // Check if the student has any existing violations with a higher penalty
            $highestExistingPenalty = $this->getHighestExistingPenalty($studentId);

            // If already terminated, short-circuit
            $isTerminated = $highestExistingPenalty === 'T';
            if ($isTerminated) {
                return response()->json([
                    'success' => true,
                    'severity' => $severity,
                    'offenseCount' => $offenseCount,
                    'maxCount' => $maxCount,
                    'isEscalated' => $isEscalated,
                    'basePenalty' => $basePenalty,
                    'escalatedPenalty' => $escalatedPenalty,
                    'calculatedPenalty' => 'T',
                    'highestExistingPenalty' => $highestExistingPenalty,
                    'finalPenalty' => 'T',
                    'isTerminated' => true
                ]);
            }

            // If currently on Probation, any subsequent violation escalates to Termination per rule
            if (!empty($highestExistingPenalty) && strtoupper($highestExistingPenalty) === 'PRO') {
                return response()->json([
                    'success' => true,
                    'severity' => $severity,
                    'offenseCount' => $offenseCount,
                    'maxCount' => $maxCount,
                    'isEscalated' => true,
                    'basePenalty' => $basePenalty,
                    'escalatedPenalty' => $escalatedPenalty,
                    'calculatedPenalty' => 'T',
                    'highestExistingPenalty' => $highestExistingPenalty,
                    'finalPenalty' => 'T',
                    'isTerminated' => false
                ]);
            }

            // Compute penalty strictly by rules based on active count in this severity
            $existingSeverityCount = $existingOffenses;
            $calculatedPenalty = $this->computePenaltyByRules($severity, $existingSeverityCount);

            // Final penalty policy for preview:
            // - If currently on Probation ('Pro'), next violation is Termination ('T')
            // - Otherwise, do not allow the preview penalty to be lower than student's highest existing penalty
            if (!empty($highestExistingPenalty) && strtoupper($highestExistingPenalty) === 'PRO') {
                $finalPenalty = 'T';
            } elseif (!empty($highestExistingPenalty)) {
                $finalPenalty = $this->escalateIfRepeat($highestExistingPenalty, $calculatedPenalty);
            } else {
                $finalPenalty = $calculatedPenalty;
            }
            
            // Log the result
            Log::info('Checked existing violations', [
                'student_id' => $studentId,
                'violation_type_id' => $violationTypeId,
                'severity' => $severity,
                'offense_count' => $offenseCount,
                'max_count' => $maxCount,
                'is_escalated' => $isEscalated,
                'base_penalty' => $basePenalty,
                'escalated_penalty' => $escalatedPenalty,
                'calculated_penalty' => $calculatedPenalty,
                'highest_existing_penalty' => $highestExistingPenalty,
                'final_penalty' => $finalPenalty,
                'note' => 'finalPenalty uses calculatedPenalty unless highestExistingPenalty is Pro'
            ]);

            return response()->json([
                'success' => true,
                'severity' => $severity,
                'offenseCount' => $offenseCount,
                'maxCount' => $maxCount,
                'isEscalated' => $isEscalated,
                'basePenalty' => $basePenalty,
                'escalatedPenalty' => $escalatedPenalty,
                'calculatedPenalty' => $calculatedPenalty,
                'highestExistingPenalty' => $highestExistingPenalty,
                'finalPenalty' => $finalPenalty
            ]);
            
        } catch (Exception $e) {
            Log::error('Error checking existing violations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error checking existing violations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the consequence for a violation
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateConsequence(Request $request, $id)
    {
        try {
            $violation = Violation::findOrFail($id);

            // Validate the request
            $validated = $request->validate([
                'consequence_select' => 'nullable|string',
                'consequence' => 'required|string|max:255',
                'duration_value' => 'nullable|integer|min:1|max:365',
                'duration_unit' => 'nullable|in:minutes,hours,days,weeks,months'
            ]);

            // Consequences that require duration
            $consequencesWithDuration = [
                'No cellphone', 'No going out', 'Community Service',
                'Kitchen team', 'No internet access', 'Suspension', 'Detention'
            ];

            $consequence = $validated['consequence'];
            $durationValue = $validated['duration_value'] ? (int)$validated['duration_value'] : null;
            $durationUnit = $validated['duration_unit'] ?? null;

            // If a consequence with duration is selected, combine it with duration
            if (in_array($consequence, $consequencesWithDuration) && $durationValue && $durationUnit) {
                $finalConsequence = "{$consequence} for {$durationValue} {$durationUnit}";
                
                // Set duration fields
                $violation->consequence_duration_value = $durationValue;
                $violation->consequence_duration_unit = $durationUnit;
                
                // Calculate start and end dates
                $violation->consequence_start_date = now();
                
                // Calculate end date manually to avoid Carbon issues
                $startDate = $violation->consequence_start_date;
                switch ($durationUnit) {
                    case 'minutes':
                        $violation->consequence_end_date = $startDate->copy()->addMinutes($durationValue);
                        break;
                    case 'hours':
                        $violation->consequence_end_date = $startDate->copy()->addHours($durationValue);
                        break;
                    case 'days':
                        $violation->consequence_end_date = $startDate->copy()->addDays($durationValue);
                        break;
                    case 'weeks':
                        $violation->consequence_end_date = $startDate->copy()->addWeeks($durationValue);
                        break;
                    case 'months':
                        $violation->consequence_end_date = $startDate->copy()->addMonths($durationValue);
                        break;
                    default:
                        $violation->consequence_end_date = null;
                }
            } else {
                $finalConsequence = $consequence;
                
                // Clear duration fields for consequences that don't need them
                $violation->consequence_duration_value = null;
                $violation->consequence_duration_unit = null;
                $violation->consequence_start_date = null;
                $violation->consequence_end_date = null;
            }

            // Update the violation
            $violation->consequence = $finalConsequence;
            $violation->consequence_status = 'active';
            $violation->save();

            return redirect()->route('educator.view-violation', $id)
                ->with('success', 'Consequence updated successfully!');

        } catch (ModelNotFoundException $e) {
            return redirect()->route('educator.violation')
                ->with('error', 'Violation not found.');
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (Exception $e) {
            Log::error('Error updating consequence: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'An error occurred while updating the consequence. Please try again.');
        }
    }

    /**
     * Get invalid students directly from G16_CAPSTONE and convert them to violation-like objects
     */
    private function getInvalidStudentsAsViolations($batch = 'all', $severity = '', $search = '')
    {
        try {
            // First, try to get invalid students with full join
            $invalidStudents = $this->getInvalidStudentsWithFullData($batch, $search);
            
            // If that fails, try simpler approach
            if ($invalidStudents->isEmpty()) {
                $invalidStudents = $this->getInvalidStudentsSimple($batch, $search);
            }

            // Exclude submissions that were already synced into real violations
            try {
                $existingIds = \DB::table('violations')
                    ->whereNotNull('task_submission_id')
                    ->pluck('task_submission_id')
                    ->toArray();
                if (!empty($existingIds)) {
                    $invalidStudents = $invalidStudents->reject(function($item) use ($existingIds) {
                        return in_array($item->submission_id ?? $item->id ?? null, $existingIds);
                    });
                }
            } catch (\Exception $e) {
                // If check fails, continue without filtering
            }

            // Convert to violation-like objects
            return $invalidStudents->map(function($student) {
                // Create a violation object that matches the structure expected by the view
                $violation = new \stdClass();
                $violation->id = 'g16_invalid_' . $student->submission_id;
                $violation->violation_date = $student->validated_at ?: $student->created_at;
                $violation->severity = 'Low'; // Invalid tasks are typically low severity
                $violation->penalty = 'VW'; // Verbal Warning
                $violation->action_taken = true;
                $violation->status = 'active';
                $violation->consequence_status = 'pending';
                $violation->offense = 'Task Non-Compliance';
                $violation->consequence = "Student failed to properly complete " . ucfirst($student->task_category) . " task assignment.";
                if (isset($student->description) && $student->description) {
                    $violation->consequence .= " Details: " . $student->description;
                }
                $violation->created_at = $student->validated_at ?: $student->created_at;
                $violation->is_invalid_student = true; // Flag to identify these records
                $violation->g16_submission_id = $student->submission_id; // Keep reference to original
                
                // Create student object with actual data from G16_CAPSTONE
                $violation->student = new \stdClass();
                $violation->student->user_fname = $student->user_fname ?? 'Unknown';
                $violation->student->user_lname = $student->user_lname ?? 'Student';
                $violation->student->student_id = $student->student_id ?? $student->user_id ?? 'N/A';
                $violation->student->user_email = $student->user_email ?? '';
                $violation->student->gender = $student->gender ?? '';
                $violation->student->batch = $student->batch ?? '';
                
                // Create violation type object
                $violation->violationType = new \stdClass();
                $violation->violationType->violation_name = "Non-compliance with " . ucfirst($student->task_category) . " task assignment";
                
                // Create offense category object
                $violation->violationType->offenseCategory = new \stdClass();
                $violation->violationType->offenseCategory->category_name = 'Center Tasking';
                
                return $violation;
            });

        } catch (\Exception $e) {
            Log::error('Error getting invalid students from G16_CAPSTONE: ' . $e->getMessage());
            // Return empty collection if G16_CAPSTONE is not accessible
            return collect();
        }
    }

    /**
     * Try to get invalid students with full data (with joins)
     */
    private function getInvalidStudentsWithFullData($batch = 'all', $search = '')
    {
        try {
            $query = DB::connection('g16_capstone')
                ->table('task_submissions as ts')
                ->join('pnph_users as u', 'ts.user_id', '=', 'u.user_id')
                ->leftJoin('student_details as sd', 'ts.user_id', '=', 'sd.user_id')
                ->where('ts.status', 'invalid')
                ->select(
                    'ts.id as submission_id',
                    'ts.user_id',
                    'ts.task_category',
                    'ts.description',
                    'ts.validated_at',
                    'ts.created_at',
                    'u.user_fname',
                    'u.user_lname',
                    'u.user_email',
                    'u.gender',
                    'sd.student_id',
                    'sd.batch'
                )
                ->orderBy('ts.validated_at', 'desc');

            // Apply filters
            if ($batch !== 'all') {
                $query->where('sd.student_id', 'like', $batch . '%');
            }

            if (!empty($search)) {
                $query->where(function($q) use ($search) {
                    $q->where('u.user_fname', 'like', "%{$search}%")
                      ->orWhere('u.user_lname', 'like', "%{$search}%")
                      ->orWhere('sd.student_id', 'like', "%{$search}%")
                      ->orWhere('ts.task_category', 'like', "%{$search}%");
                });
            }

            return $query->get();

        } catch (\Exception $e) {
            Log::warning('Full data query failed, trying simple approach: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Fallback: Get invalid students with simple query (no joins)
     */
    private function getInvalidStudentsSimple($batch = 'all', $search = '')
    {
        try {
            // Get invalid submissions first
            $submissions = DB::connection('g16_capstone')
                ->table('task_submissions')
                ->where('status', 'invalid')
                ->orderBy('validated_at', 'desc')
                ->get();

            // Then get user data for each submission
            $enrichedSubmissions = $submissions->map(function($submission) {
                try {
                    // Get user data
                    $user = DB::connection('g16_capstone')
                        ->table('pnph_users')
                        ->where('user_id', $submission->user_id)
                        ->first();

                    // Try to get student details
                    $studentDetail = null;
                    try {
                        $studentDetail = DB::connection('g16_capstone')
                            ->table('student_details')
                            ->where('user_id', $submission->user_id)
                            ->first();
                    } catch (\Exception $e) {
                        // student_details table might not exist, that's ok
                    }

                    // Combine data
                    $combined = new \stdClass();
                    $combined->submission_id = $submission->id;
                    $combined->user_id = $submission->user_id;
                    $combined->task_category = $submission->task_category;
                    $combined->description = $submission->description ?? '';
                    $combined->validated_at = $submission->validated_at;
                    $combined->created_at = $submission->created_at;
                    
                    if ($user) {
                        $combined->user_fname = $user->user_fname ?? 'Unknown';
                        $combined->user_lname = $user->user_lname ?? 'Student';
                        $combined->user_email = $user->user_email ?? '';
                        $combined->gender = $user->gender ?? '';
                    } else {
                        $combined->user_fname = 'Unknown';
                        $combined->user_lname = 'Student';
                        $combined->user_email = '';
                        $combined->gender = '';
                    }

                    if ($studentDetail) {
                        $combined->student_id = $studentDetail->student_id;
                        $combined->batch = $studentDetail->batch ?? '';
                    } else {
                        $combined->student_id = $submission->user_id; // Use user_id as fallback
                        $combined->batch = '';
                    }

                    return $combined;

                } catch (\Exception $e) {
                    Log::warning('Error enriching submission ' . $submission->id . ': ' . $e->getMessage());
                    return null;
                }
            })->filter(); // Remove null entries

            // Apply filters
            if ($batch !== 'all') {
                $enrichedSubmissions = $enrichedSubmissions->filter(function($item) use ($batch) {
                    return strpos($item->student_id, $batch) === 0;
                });
            }

            if (!empty($search)) {
                $enrichedSubmissions = $enrichedSubmissions->filter(function($item) use ($search) {
                    return stripos($item->user_fname, $search) !== false ||
                           stripos($item->user_lname, $search) !== false ||
                           stripos($item->student_id, $search) !== false ||
                           stripos($item->task_category, $search) !== false;
                });
            }

            return $enrichedSubmissions;

        } catch (\Exception $e) {
            Log::error('Simple query also failed: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Convert x_status rows (synced from G16 task_histories) into violation-like objects
     */
    private function getXStatusAsViolations($batch = 'all', $severity = '', $search = '')
    {
        try {
            // Base query from local x_status, try to enrich with local users/student_details if present
            $query = DB::table('x_status as xs')
                ->leftJoin('student_details as sd', function($j){
                    $j->on('sd.student_id', '=', 'xs.student_id');
                })
                ->leftJoin('pnph_users as u', function($j){
                    // Prefer joining by sd.user_id if available, fall back to xs.user_id
                    $j->on('u.user_id', '=', DB::raw('COALESCE(sd.user_id, xs.user_id)'));
                })
                ->select(
                    'xs.id', 'xs.room_number', 'xs.task_id', 'xs.day', 'xs.week', 'xs.month', 'xs.year',
                    'xs.completed', 'xs.status as task_status', 'xs.assigned_to', 'xs.task_area',
                    'xs.task_description', 'xs.filter_type', 'xs.created_at', 'xs.updated_at',
                    DB::raw('COALESCE(sd.student_id, xs.student_id) as student_id'),
                    'u.user_fname', 'u.user_lname'
                )
                ->orderByDesc('xs.created_at');

            // Filter by batch (student_id prefix) if requested
            if ($batch !== 'all') {
                $query->where(function($q) use ($batch){
                    $q->where('sd.student_id', 'like', $batch.'%')
                      ->orWhere('xs.student_id', 'like', $batch.'%');
                });
            }

            // Search by name, student_id, area, or assigned_to
            if (!empty($search)) {
                $query->where(function($q) use ($search){
                    $q->where('u.user_fname', 'like', "%{$search}%")
                      ->orWhere('u.user_lname', 'like', "%{$search}%")
                      ->orWhere('sd.student_id', 'like', "%{$search}%")
                      ->orWhere('xs.student_id', 'like', "%{$search}%")
                      ->orWhere('xs.task_area', 'like', "%{$search}%")
                      ->orWhere('xs.assigned_to', 'like', "%{$search}%");
                });
            }

            $rows = $query->get();

            // Map to violation-like objects expected by the view
            return $rows->map(function($r){
                $v = new \stdClass();
                $v->id = 'x_status_' . $r->id;
                $v->x_status_id = $r->id;
                // Prefer date string in xs.day if it looks like YYYY-MM-DD; else use created_at
                $v->violation_date = !empty($r->day) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string)$r->day)
                    ? $r->day
                    : ($r->created_at ?: now());
                // Map to a specific Room Rule name and severity
                $rule = self::mapRoomRuleFromXStatus($r);
                $v->severity = $rule['severity'];
                $v->penalty = 'VW';
                $v->action_taken = true;
                $v->status = 'active';
                $v->consequence_status = 'pending';
                $v->offense = 'Room Rules Non-Compliance';
                // Build a readable description from x_status fields
                $details = [];
                if (!empty($r->task_area)) $details[] = 'Area: '.$r->task_area;
                if (!empty($r->task_status)) $details[] = 'Status: '.$r->task_status;
                if (!empty($r->task_description)) $details[] = 'Notes: '.$r->task_description;
                if (!empty($r->assigned_to)) $details[] = 'Assigned to: '.$r->assigned_to;
                $v->consequence = empty($details)
                    ? 'Room Rules violation recorded from task history integration.'
                    : ('Room Rules violation recorded from task history integration. '.implode(' | ', $details));
                $v->created_at = $r->created_at;
                $v->is_x_status = true;

                // Student object
                $v->student = new \stdClass();
                $v->student->user_fname = $r->user_fname ?? '';
                $v->student->user_lname = $r->user_lname ?? '';
                $v->student->student_id = $r->student_id ?? 'N/A';

                // Violation type/category objects for display columns
                $v->violationType = new \stdClass();
                $v->violationType->violation_name = $rule['name'];
                $v->violationType->offenseCategory = new \stdClass();
                $v->violationType->offenseCategory->category_name = 'Room Rules';

                return $v;
            });
        } catch (\Exception $e) {
            Log::error('Error getting x_status rows as violations: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Heuristic mapping from x_status fields to a specific Room Rules violation
     */
    public static function mapRoomRuleFromXStatus($row): array
    {
        $text = strtolower(trim(
            implode(' ', array_filter([
                (string)($row->task_area ?? ''),
                (string)($row->task_description ?? ''),
                (string)($row->assigned_to ?? ''),
                (string)($row->task_status ?? ''),
            ]))
        ));

        // 3.9 Boys entering girls room or vice versa (Medium)
        if (preg_match('/boys?\s+entering\s+girls?|girls?\s+entering\s+boys?|opposite\s+sex\s+room/', $text)) {
            return ['name' => 'Boys entering girls room or vice versa except for emergencies.', 'severity' => 'Medium'];
        }

        // 3.3 Not maintaining hygiene
        if (preg_match('/hygiene|bedsheet|bed\s*sheets?|pillow\s*cases?|wash(ed|ing)/', $text)) {
            return ['name' => 'Not maintaining hygiene (e.g., bedsheets and pillow cases must be washed every two weeks).', 'severity' => 'Low'];
        }

        // 3.5 Not turning off lights/faucet/fan
        if (preg_match('/not\s*turn(ing)?\s*off|lights?|light\s*left\s*on|faucet|tap\s*left\s*open|fan\s*left\s*on|electric\s*fan/', $text)) {
            return ['name' => 'Not turning off lights, faucet and electric fan when not in use.', 'severity' => 'Low'];
        }

        // 3.6 Posting or vandalism
        if (preg_match('/vandal(ism|ize)|posting|graffiti|writ(ing|ten)\s*on\s*wall|stickers?\s*on\s*wall/', $text)) {
            return ['name' => 'Posting or vandalism on the room walls.', 'severity' => 'Low'];
        }

        // 3.7 Sleeping in another room
        if (preg_match('/sleep(ing)?\s+in\s+another\s+room|wrong\s*room|not\s*assigned\s*room/', $text)) {
            return ['name' => 'Sleeping in another room other than the one assigned.', 'severity' => 'Low'];
        }

        // 3.8 Speaking loudly, shouting and playing loud music
        if (preg_match('/loud\s*music|speaking\s*loud|shout(ing)?|noise|noisy/', $text)) {
            return ['name' => 'Speaking loudly, shouting and playing loud music inside the center.', 'severity' => 'Low'];
        }

        // 3.2 Hanging clothes on the windows and balcony
        if (preg_match('/hang(ing)?\s*clothes|clothes\s*on\s*(window|balcony)/', $text)) {
            return ['name' => 'Hanging clothes on the windows and balcony.', 'severity' => 'Low'];
        }

        // 3.1 Bringing food or eating meals inside the room
        if (preg_match('/food\s*inside\s*the\s*room|eat(ing)?\s*inside\s*the\s*room|bringing\s*food\s*in\s*room/', $text)) {
            return ['name' => 'Bringing food or eating meals inside the room.', 'severity' => 'Low'];
        }

        // Default → 3.4 Not maintaining room cleanliness
        return ['name' => 'Not maintaining room cleanliness such as a dirty and smelly bathroom and comfort room, mirror and sink is not properly cleaned, trash or clutter on the floor, overflowing trash in trash bins, unorganized beds, and etc.', 'severity' => 'Low'];
    }

    // End of ViolationController methods
}
