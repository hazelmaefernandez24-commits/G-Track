<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use App\Models\InternGrade;
use App\Models\School;
use App\Models\ClassModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InternGradeController extends Controller
{
    public function index(Request $request)
    {
        // Get current page for class pagination
        $classPage = $request->get('class_page', 1);
        $classesPerPage = 1; // 1 class per page as requested

        // Build base query
        $query = InternGrade::with(['intern', 'school', 'classModel', 'intern.studentDetail'])
            ->select('intern_grades.*')
            ->leftJoin('pnph_users', 'intern_grades.intern_id', '=', 'pnph_users.user_id')
            ->leftJoin('schools', 'intern_grades.school_id', '=', 'schools.school_id')
            ->leftJoin('classes', 'intern_grades.class_id', '=', 'classes.class_id');

        // Apply filters - Class is now the main filter
        if ($request->filled('class_filter')) {
            $query->where('intern_grades.class_id', $request->class_filter);
        }

        if ($request->filled('submission_filter')) {
            $query->where('intern_grades.submission_number', $request->submission_filter);
        }

        if ($request->filled('company_filter')) {
            $query->where('intern_grades.company_name', 'LIKE', '%' . $request->company_filter . '%');
        }

        $internGrades = $query->get();

        // Get filter options based on current filters
        $filterOptions = $this->getFilterOptions($request);

        // Group grades by class and submission
        $groupedGrades = $internGrades->groupBy(['class_id', 'submission_number']);

        // Apply class pagination
        $allClassIds = $groupedGrades->keys();
        $totalClasses = $allClassIds->count();
        $classOffset = ($classPage - 1) * $classesPerPage;
        $currentClassIds = $allClassIds->skip($classOffset)->take($classesPerPage);

        // Filter grouped grades to only include current page classes
        $paginatedGroupedGrades = $groupedGrades->only($currentClassIds->toArray());

        // Create class pagination info
        $classPagination = (object)[
            'current_page' => $classPage,
            'last_page' => max(1, ceil($totalClasses / $classesPerPage)), // Ensure at least 1 page
            'per_page' => $classesPerPage,
            'total' => $totalClasses,
            'from' => $totalClasses > 0 ? $classOffset + 1 : 0,
            'to' => min($classOffset + $classesPerPage, $totalClasses),
            'has_pages' => true, // Always show pagination
            'on_first_page' => $classPage == 1,
            'has_more_pages' => $classPage < ceil(max(1, $totalClasses) / $classesPerPage)
        ];

        return view('training.intern.index', compact(
            'paginatedGroupedGrades',
            'filterOptions',
            'classPagination'
        ))->with('title', 'Intern Grades');
    }

    private function getFilterOptions(Request $request)
    {
        // Get all classes for the class dropdown (not filtered by school)
        $allClassesQuery = InternGrade::with(['school', 'classModel']);
        $allClasses = $allClassesQuery->get();

        // Get filtered data for submission and company options based on class filter
        $filteredQuery = InternGrade::with(['school', 'classModel']);

        if ($request->filled('class_filter')) {
            $filteredQuery->where('class_id', $request->class_filter);
        }

        $filteredGrades = $filteredQuery->get();

        return [
            'classes' => $allClasses->groupBy('class_id')->map(function($classGrades) {
                $first = $classGrades->first();
                return [
                    'class_id' => $first->class_id,
                    'class_name' => $first->classModel->class_name ?? 'N/A',
                    'school_name' => $first->school->name ?? 'N/A'
                ];
            })->values(),
            'submissions' => $filteredGrades->pluck('submission_number')->unique()->sort()->values(),
            'companies' => $filteredGrades->pluck('company_name')->unique()->sort()->values()
        ];
    }

    public function create()
    {
        $schools = School::all();
        return view('training.intern.create', compact('schools'))->with('title', 'Create Intern Grade');
    }

    public function store(Request $request)
    {
        try {
            // Validate the request
            $validated = $request->validate([
                'school_id' => 'required|exists:schools,school_id',
                'class_id' => 'required|exists:classes,class_id',
                'intern_id' => 'required|exists:pnph_users,user_id',
                'company_name' => 'required|string',
                'submission_date' => 'required|date',
                'submission_number' => 'required|in:1st,2nd,3rd,4th',
                'grades' => 'required|array',
                'grades.ict_learning_competency' => 'required|integer|min:1|max:4',
                'grades.twenty_first_century_skills' => 'required|integer|min:1|max:4',
                'grades.expected_outputs_deliverables' => 'required|integer|min:1|max:4',
                'remarks' => 'nullable|string|max:500'
            ]);

            // Log the incoming request data
            \Log::info('Creating intern grade with data:', [
                'request_data' => $request->all(),
                'validated_data' => $validated
            ]);

            // Check if a grade already exists for this intern in this class and submission number
            $existingGrade = InternGrade::where('intern_id', $validated['intern_id'])
                ->where('class_id', $validated['class_id'])
                ->where('submission_number', $validated['submission_number'])
                ->first();

            if ($existingGrade) {
                \Log::warning('Grade already exists for intern', [
                    'intern_id' => $validated['intern_id'],
                    'class_id' => $validated['class_id'],
                    'submission_number' => $validated['submission_number']
                ]);
                return redirect()->route('training.intern-grades.index')
                    ->with('error', 'A grade already exists for this intern in this class for the selected submission number.');
            }

            DB::beginTransaction();

            try {
                // Create the intern grade
                $internGrade = new InternGrade();
                $internGrade->intern_id = $validated['intern_id'];
                $internGrade->school_id = $validated['school_id'];
                $internGrade->class_id = $validated['class_id'];
                $internGrade->company_name = $validated['company_name'];
                $internGrade->submission_date = $validated['submission_date'];
                $internGrade->submission_number = $validated['submission_number'];
                // Store individual grades as an array (Laravel will cast to JSON)
                $internGrade->grades = $validated['grades'];
                $internGrade->remarks = $validated['remarks'] ?? null;
                $internGrade->created_by = auth()->id();
                $internGrade->updated_by = auth()->id();

                // Calculate final grade BEFORE saving
                $internGrade->final_grade = $internGrade->calculateFinalGradeFromJson();

                // Log final_grade before and after rounding
                \Log::info('Final grade before rounding:', ['type' => gettype($internGrade->final_grade), 'value' => $internGrade->final_grade]);
                $roundedFinalGrade = round($internGrade->final_grade);
                \Log::info('Final grade after rounding:', ['type' => gettype($roundedFinalGrade), 'value' => $roundedFinalGrade]);

                // Determine the status based on the final grade
                $internGrade->status = match((int) $roundedFinalGrade) {
                    1 => 'Fully Achieved',
                    2 => 'Partially Achieved',
                    3 => 'Barely Achieved',
                    4 => 'No Achievement',
                    default => null,
                };

                // Log the determined status
                \Log::info('Determined intern grade status:', ['status' => $internGrade->status]);

                // Log the grade calculation
                \Log::info('Calculated final grade:', [
                    'grades' => [
                        'ict' => $validated['grades']['ict_learning_competency'],
                        'skills' => $validated['grades']['twenty_first_century_skills'],
                        'outputs' => $validated['grades']['expected_outputs_deliverables']
                    ],
                    'final_grade' => $internGrade->final_grade
                ]);

                // Save after calculating final grade
                $internGrade->save();

                $internGrade->refresh();

                DB::commit();

                \Log::info('Successfully created intern grade', [
                    'intern_grade_id' => $internGrade->id,
                    'intern_id' => $internGrade->intern_id,
                    'class_id' => $internGrade->class_id
                ]);

                // Redirect to index instead of returning JSON
                return redirect()->route('training.intern-grades.index')->with('success', 'Intern grade submitted successfully.');

            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Database error while creating intern grade: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e;
            }

        } catch (\Exception $e) {
            \Log::error('Error creating intern grade: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);

            return redirect()->route('training.intern-grades.index')->with('error', 'Failed to submit intern grade. Please try again.');
        }
    }

    public function edit(InternGrade $internGrade)
    {
        \Log::info('InternGradeController@edit: InternGrade ID', ['id' => $internGrade->id]);
        $internGrade->load(['school', 'classModel']);
        \Log::info('InternGradeController@edit: InternGrade after loading relationships', ['internGrade' => $internGrade->toArray()]);
        \Log::info('InternGradeController@edit: InternGrade class relationship', ['class' => $internGrade->class]);
        $schools = School::all();
        return view('training.intern.edit', compact('internGrade', 'schools'))->with('title', 'Edit Intern Grade');
    }

    public function update(Request $request, InternGrade $internGrade)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'grades.ict_learning_competency' => 'required|integer|min:1|max:4',
            'grades.twenty_first_century_skills' => 'required|integer|min:1|max:4',
            'grades.expected_outputs_deliverables' => 'required|integer|min:1|max:4',
            'remarks' => 'nullable|string|max:500'
        ]);

        \Log::info('InternGradeController@update: Validated Data', ['validated' => $validated]);

        try {
            DB::beginTransaction();

            // Assign the validated grades array to the grades JSON column
            $internGrade->grades = $validated['grades'];

            // Update company name if provided
            if (isset($validated['company_name'])) {
                $internGrade->company_name = $validated['company_name'];
            }

            $internGrade->remarks = $validated['remarks'] ?? null;
            $internGrade->updated_by = Auth::id();

            // Recalculate final grade
            $internGrade->final_grade = $internGrade->calculateFinalGradeFromJson();
            // Determine the status based on the recalculated final grade
            $roundedFinalGrade = round($internGrade->final_grade);
            $internGrade->status = match((int) $roundedFinalGrade) {
                1 => 'Fully Achieved',
                2 => 'Partially Achieved',
                3 => 'Barely Achieved',
                4 => 'No Achievement',
                default => null,
            };
            $internGrade->save();

            $internGrade->refresh();

            DB::commit();

            return redirect()
                ->route('training.intern-grades.index')
                ->with('success', 'Intern grade has been updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating intern grade: ' . $e->getMessage());
            return back()->with('error', 'Failed to update intern grade. Please try again.');
        }
    }

    public function destroy(InternGrade $internGrade)
    {
        try {
            $internGrade->delete();
            return redirect()
                ->route('training.intern-grades.index')
                ->with('success', 'Intern grade has been deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting intern grade: ' . $e->getMessage());
            return back()->with('error', 'Failed to delete intern grade. Please try again.');
        }
    }

    public function getInternsBySchoolAndClass(Request $request, $schoolId)
    {
        try {
            Log::info('Fetching students for school and class', [
                'school_id' => $schoolId,
                'class_id' => $request->class_id,
                'request_data' => $request->all()
            ]);

            // First, verify the school exists
            $school = \App\Models\School::find($schoolId);
            if (!$school) {
                Log::error('School not found', ['school_id' => $schoolId]);
                return response()->json(['error' => 'School not found'], 404);
            }

            // Then, verify the class exists if class_id is provided
            if ($request->has('class_id')) {
                $class = \App\Models\ClassModel::where('class_id', $request->class_id)
                    ->where('school_id', $schoolId)
                    ->first();
                if (!$class) {
                    Log::error('Class not found or does not belong to school', [
                        'class_id' => $request->class_id,
                        'school_id' => $schoolId
                    ]);
                    return response()->json(['error' => 'Class not found'], 404);
                }
            }

            // Build the query using joins instead of whereHas for better performance
            $query = \App\Models\PNUser::select('pnph_users.user_id', 'pnph_users.user_fname', 'pnph_users.user_lname')
                ->join('class_student', 'pnph_users.user_id', '=', 'class_student.user_id')
                ->join('classes', function($join) use ($schoolId, $request) {
                    $join->on('classes.id', '=', 'class_student.class_id')
                        ->where('classes.school_id', '=', $schoolId);
                    if ($request->has('class_id')) {
                        $join->where('classes.class_id', '=', $request->class_id);
                    }
                })
                ->where('pnph_users.user_role', 'student')
                ->where('pnph_users.status', 'active')
                ->orderBy('pnph_users.user_lname')
                ->orderBy('pnph_users.user_fname')
                ->distinct();

            Log::info('Query details', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);

            $students = $query->get();

            Log::info('Query results', [
                'student_count' => $students->count(),
                'students' => $students->toArray()
            ]);

            if ($students->isEmpty()) {
                Log::warning('No students found for the given criteria', [
                    'school_id' => $schoolId,
                    'class_id' => $request->class_id
                ]);
            }

            return response()->json($students);
        } catch (\Exception $e) {
            Log::error('Error fetching students: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString(),
                'school_id' => $schoolId,
                'class_id' => $request->class_id
            ]);
            return response()->json(['error' => 'Failed to fetch students'], 500);
        }
    }

    public function progress()
    {
        // Fetch all intern grades with relevant fields
        $grades = \App\Models\InternGrade::select(
                'final_grade',
                'ict_learning_competency',
                'twenty_first_century_skills',
                'expected_outputs_deliverables'
            )
            ->whereNotNull('final_grade') // Only consider graded interns
            ->get();

        // Initialize data structure
        $data = [
            'ICT Learning Competency' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            '21st Century Skills' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
            'Expected Outputs/Deliverables' => [1 => 0, 2 => 0, 3 => 0, 4 => 0],
        ];

        // Map final grade to status key (1->1, 2->2, 3->3, 4->4)
        foreach ($grades as $grade) {
            $roundedFinalGrade = round($grade->final_grade);

            if ($roundedFinalGrade >= 1 && $roundedFinalGrade <= 4) {
                 // Increment counts for each subject based on the intern's final grade status
                 // We are checking if the subject grade itself is not null (meaning it was graded)
                 // and associating it with the final overall status.
                if ($grade->ict_learning_competency !== null) {
                    $data['ICT Learning Competency'][$roundedFinalGrade]++;
                }
                if ($grade->twenty_first_century_skills !== null) {
                    $data['21st Century Skills'][$roundedFinalGrade]++;
                }
                if ($grade->expected_outputs_deliverables !== null) {
                    $data['Expected Outputs/Deliverables'][$roundedFinalGrade]++;
                }
            }
        }

        // Prepare data for Chart.js
        $chartData = [
            'labels' => array_keys($data), // Subjects on X-axis
            'datasets' => [
                [
                    'label' => 'Fully Achieved (Grade 1)',
                    'data' => [ $data['ICT Learning Competency'][1], $data['21st Century Skills'][1], $data['Expected Outputs/Deliverables'][1] ],
                    'backgroundColor' => '#22bbea', // Color for Grade 1
                    'borderColor' => 'rgb(80, 80, 80)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Partially Achieved (Grade 2)',
                    'data' => [ $data['ICT Learning Competency'][2], $data['21st Century Skills'][2], $data['Expected Outputs/Deliverables'][2] ],
                    'backgroundColor' => 'rgb(0, 157, 34)', // Color for Grade 2
                    'borderColor' => 'rgb(80, 80, 80)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Barely Achieved (Grade 3)',
                    'data' => [ $data['ICT Learning Competency'][3], $data['21st Century Skills'][3], $data['Expected Outputs/Deliverables'][3] ],
                    'backgroundColor' => '#ff9933', // Color for Grade 3
                    'borderColor' => 'rgb(81, 81, 81)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'No Achievement (Grade 4)',
                    'data' => [ $data['ICT Learning Competency'][4], $data['21st Century Skills'][4], $data['Expected Outputs/Deliverables'][4] ],
                    'backgroundColor' => 'rgb(204, 1, 1)',  // Color for Grade 4
                    'borderColor' => 'rgb(80, 80, 80)',
                    'borderWidth' => 1
                ]
            ]
        ];

        // Pass data to the view
        return view('training.analytics.intern-grades-progress', compact('chartData'));
    }
} 