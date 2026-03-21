<?php

namespace App\Http\Controllers\Training;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InternGrade;
use App\Models\School;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InternGradesAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // Get pagination parameters
        $classPage = $request->get('class_page', 1);
        $submissionPage = $request->get('submission_page', 1);
        $classesPerPage = 1; // 1 class per page
        $submissionsPerPage = 1; // 1 submission per page

        // Get all classes
        $allClasses = \App\Models\ClassModel::orderBy('class_name')->get();
        $totalClasses = $allClasses->count();

        // Apply class pagination
        $classOffset = ($classPage - 1) * $classesPerPage;
        $paginatedClasses = $allClasses->skip($classOffset)->take($classesPerPage);

        // Create class pagination info
        $classPagination = (object)[
            'current_page' => $classPage,
            'last_page' => max(1, ceil($totalClasses / $classesPerPage)),
            'per_page' => $classesPerPage,
            'total' => $totalClasses,
            'from' => $totalClasses > 0 ? $classOffset + 1 : 0,
            'to' => min($classOffset + $classesPerPage, $totalClasses),
            'has_pages' => true, // Always show pagination
            'on_first_page' => $classPage == 1,
            'has_more_pages' => $classPage < ceil(max(1, $totalClasses) / $classesPerPage)
        ];

        // Get available submissions for current class
        $availableSubmissions = ['1st', '2nd', '3rd', '4th'];
        $totalSubmissions = count($availableSubmissions);

        // Apply submission pagination
        $submissionOffset = ($submissionPage - 1) * $submissionsPerPage;
        $currentSubmissions = array_slice($availableSubmissions, $submissionOffset, $submissionsPerPage);

        // Create submission pagination info
        $submissionPagination = (object)[
            'current_page' => $submissionPage,
            'last_page' => max(1, ceil($totalSubmissions / $submissionsPerPage)),
            'per_page' => $submissionsPerPage,
            'total' => $totalSubmissions,
            'from' => $totalSubmissions > 0 ? $submissionOffset + 1 : 0,
            'to' => min($submissionOffset + $submissionsPerPage, $totalSubmissions),
            'has_pages' => true, // Always show pagination
            'on_first_page' => $submissionPage == 1,
            'has_more_pages' => $submissionPage < ceil(max(1, $totalSubmissions) / $submissionsPerPage)
        ];

        // Get companies for ALL classes (for filtering dropdown)
        $allClassCompanies = [];
        foreach ($allClasses as $class) {
            $allClassCompanies[$class->class_id] = InternGrade::select('company_name')
                ->where('class_id', $class->class_id)
                ->whereNotNull('company_name')
                ->distinct()
                ->orderBy('company_name')
                ->pluck('company_name');
        }

        // Get submission dates for each submission number
        $submissionDates = [];
        foreach ($currentSubmissions as $submissionNumber) {
            // Get the most recent submission date for this submission number across all classes
            $submissionDate = InternGrade::where('submission_number', $submissionNumber)
                ->whereNotNull('submission_date')
                ->orderBy('submission_date', 'desc')
                ->value('submission_date');

            $submissionDates[$submissionNumber] = $submissionDate ? \Carbon\Carbon::parse($submissionDate)->format('M d, Y') : null;
        }

        // Get chart data for paginated classes
        $classChartData = [];
        foreach ($paginatedClasses as $class) {
            $classChartData[$class->class_id] = [
                'class_name' => $class->class_name,
                'companies' => InternGrade::where('class_id', $class->class_id)
                    ->whereNotNull('company_name')
                    ->distinct()
                    ->pluck('company_name')
                    ->toArray()
            ];
        }

        return view('training.analytics.intern-grades-progress', compact(
            'allClassCompanies',
            'allClasses',
            'paginatedClasses',
            'classChartData',
            'classPagination',
            'submissionPagination',
            'currentSubmissions',
            'submissionDates'
        ))->with('title', 'Internship Grades Progress Analytics');
    }

    public function getAnalyticsData(Request $request)
    {
        $company = $request->input('company');
        $classId = $request->input('class_id');
        $submissionNumber = $request->input('submission_number');

        \Log::info('Training Intern Grades Analytics Data Request:', [
            'company' => $company,
            'class_id' => $classId,
            'submission_number' => $submissionNumber
        ]);

        // Get chart data for each class
        $classChartData = [];
        $classes = \App\Models\ClassModel::orderBy('class_name')->get();
        
        foreach ($classes as $class) {
            // If class_id is provided, only get data for that class
            if ($classId && $class->class_id != $classId) {
                continue;
            }

            $classChartData[$class->class_id] = [
                'class_name' => $class->class_name,
                'chart_data' => $this->getDistributionChartData($company, $class->class_id)
            ];
        }

        return response()->json([
            'classChartData' => $classChartData
        ]);
    }

    public function checkSubmissions(Request $request)
    {
        $classId = $request->input('class_id');

        if (!$classId) {
            // Check if there are any submissions across all classes
            $hasSubmissions = InternGrade::whereNotNull('submission_number')->exists();
        } else {
            // Check if the specific class has any intern grades with submission numbers
            $hasSubmissions = InternGrade::where('class_id', $classId)
                ->whereNotNull('submission_number')
                ->exists();
        }

        \Log::info('Training Check Submissions:', [
            'class_id' => $classId,
            'hasSubmissions' => $hasSubmissions
        ]);

        return response()->json(['hasSubmissions' => $hasSubmissions]);
    }

    private function getSummaryData($company = null)
    {
        $query = InternGrade::query();
        
        if ($company) {
            $query->where('company_name', $company);
        }

        return [
            'fully_achieved' => (clone $query)->where('status', 'Fully Achieved')->count(),
            'partially_achieved' => (clone $query)->where('status', 'Partially Achieved')->count(),
            'no_achievement' => (clone $query)->where('status', 'No Achievement')->count()
        ];
    }

    private function getDistributionChartData($company = null, $classId = null)
    {
        $query = InternGrade::query();
        
        if ($company) {
            $query->where('company_name', $company);
        }

        if ($classId) {
            $query->where('class_id', $classId);
        }

        // Add submission number filter
        if (request()->has('submission_number')) {
            $submissionNumber = request('submission_number');
            $query->where('submission_number', $submissionNumber);
        }

        // Define the competencies exactly as they appear in the database
        $competencies = [
            'ict_learning_competency' => 'ICT Learning',
            'twenty_first_century_skills' => '21st Century Skills',
            'expected_outputs_deliverables' => 'Expected Outputs'
        ];

        // Initialize datasets for each grade (1-4)
        $datasets = [
            [
                'label' => '1 - Fully Achieved',
                'data' => [],
                'backgroundColor' => '#10B981', // Green
                'borderColor' => '#10B981',
                'borderWidth' => 1
            ],
            [
                'label' => '2 - Partially Achieved',
                'data' => [],
                'backgroundColor' => '#F59E0B', // Yellow
                'borderColor' => '#F59E0B',
                'borderWidth' => 1
            ],
            [
                'label' => '3 - Barely Achieved',
                'data' => [],
                'backgroundColor' => '#F97316', // Orange
                'borderColor' => '#F97316',
                'borderWidth' => 1
            ],
            [
                'label' => '4 - No Achievement',
                'data' => [],
                'backgroundColor' => '#EF4444', // Red
                'borderColor' => '#EF4444',
                'borderWidth' => 1
            ]
        ];

        // Get all records first and process in PHP for better debugging
        $allRecords = (clone $query)->get();

        \Log::info('Training Processing records', [
            'total_records' => $allRecords->count(),
            'sample_grades' => $allRecords->take(2)->pluck('grades')->toArray()
        ]);

        // For each competency, count students in each grade
        foreach ($competencies as $competencyKey => $competencyLabel) {
            // Count students for each grade (1-4) for this competency
            for ($grade = 1; $grade <= 4; $grade++) {
                $count = 0;

                foreach ($allRecords as $record) {
                    $grades = $record->grades;
                    if (is_array($grades) && isset($grades[$competencyKey])) {
                        $competencyGrade = $grades[$competencyKey];
                        if (is_numeric($competencyGrade) && (int)$competencyGrade === $grade) {
                            $count++;
                        }
                    }
                }

                $datasets[$grade - 1]['data'][] = $count;

                \Log::info("Training Grade count for {$competencyLabel} grade {$grade}: {$count}");
            }
        }

        // Check if we have any data
        $hasData = false;
        foreach ($datasets as $dataset) {
            if (array_sum($dataset['data']) > 0) {
                $hasData = true;
                break;
            }
        }

        // For debugging
        \Log::info('Chart Data', [
            'company' => $company,
            'classId' => $classId,
            'submission_number' => request('submission_number'),
            'datasets' => $datasets,
            'hasData' => $hasData,
            'query' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        return [
            'labels' => array_values($competencies),
            'datasets' => $datasets,
            'hasData' => $hasData
        ];
    }

    private function getTrendChartData($company = null, $classId = null)
    {
        $query = InternGrade::query();
        
        if ($company) {
            $query->where('company_name', $company);
        }

        // Add class filter
        if ($classId) {
            $query->where('class_id', $classId);
        }

        $monthlyAverages = $query->select(
            DB::raw('DATE_FORMAT(created_at, "%Y-%m") as month'),
            DB::raw('AVG(final_grade) as average_grade')
        )
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        return [
            'labels' => $monthlyAverages->pluck('month'),
            'datasets' => [
                [
                    'label' => 'Average Grade',
                    'data' => $monthlyAverages->pluck('average_grade'),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                    'tension' => 0.4
                ]
            ]
        ];
    }

    private function getCompetencyChartData($company = null, $classId = null)
    {
        $query = InternGrade::query();
        
        if ($company) {
            $query->where('company_name', $company);
        }

        // Add class filter
        if ($classId) {
            $query->where('class_id', $classId);
        }

        $competencyAverages = $query->select(
            DB::raw('AVG(JSON_EXTRACT(grades, "$.ict_learning_competency")) as ict_learning'),
            DB::raw('AVG(JSON_EXTRACT(grades, "$.twenty_first_century_skills")) as century_skills'),
            DB::raw('AVG(JSON_EXTRACT(grades, "$.expected_outputs_deliverables")) as outputs')
        )->first();

        return [
            'labels' => ['ICT Learning', '21st Century Skills', 'Expected Outputs'],
            'datasets' => [
                [
                    'label' => 'Average Competency Score',
                    'data' => [
                        $competencyAverages->ict_learning,
                        $competencyAverages->century_skills,
                        $competencyAverages->outputs
                    ],
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => '#3B82F6',
                    'pointBackgroundColor' => '#3B82F6',
                    'pointBorderColor' => '#fff',
                    'pointHoverBackgroundColor' => '#fff',
                    'pointHoverBorderColor' => '#3B82F6'
                ]
            ]
        ];
    }

    private function getSchoolComparisonData($company = null, $classId = null)
    {
        $query = InternGrade::query()
            ->join('pnph_users', 'intern_grades.intern_id', '=', 'pnph_users.user_id')
            ->join('class_student', 'pnph_users.user_id', '=', 'class_student.user_id')
            ->join('classes', 'class_student.class_id', '=', 'classes.id')
            ->join('schools', 'classes.school_id', '=', 'schools.school_id')
            ->select('schools.name', DB::raw('AVG(intern_grades.final_grade) as average_grade'))
            ->groupBy('schools.school_id', 'schools.name');

        if ($company) {
            $query->where('intern_grades.company_name', $company);
        }

        // Add class filter
        if ($classId) {
            $query->where('class_id', $classId);
        }

        $schoolAverages = $query->get();

        return [
            'labels' => $schoolAverages->pluck('name'),
            'datasets' => [
                [
                    'label' => 'Average Grade',
                    'data' => $schoolAverages->pluck('average_grade'),
                    'backgroundColor' => '#3B82F6',
                    'borderColor' => '#2563EB',
                    'borderWidth' => 1
                ]
            ]
        ];
    }
} 