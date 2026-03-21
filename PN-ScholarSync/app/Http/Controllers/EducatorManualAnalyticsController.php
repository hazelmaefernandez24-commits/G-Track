<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EducatorManualAnalyticsController extends Controller
{
    public function index()
    {
        // Data for the chart will be added later
        return view('educator.manual-analytics');
    }

    public function getCategoryStudentCounts(Request $request)
    {
        $categories = \App\Models\OffenseCategory::all();
        $result = [
            'categories' => [],
            'counts_all' => [],
            'counts_male' => [],
            'counts_female' => []
        ];

        $time = $request->input('time', 'all');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $class = $request->input('class', 'all');

        foreach ($categories as $category) {
            $violationTypeIds = $category->violationTypes->pluck('id');

            // Base query joined to student_details and pnph_users for filters
            $baseQuery = \App\Models\Violation::whereIn('violations.violation_type_id', $violationTypeIds)
                ->join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id');

            // Time range filter
            if ($time === 'this_month') {
                $baseQuery->whereMonth('violations.violation_date', now()->month)
                          ->whereYear('violations.violation_date', now()->year);
            } elseif ($time === 'this_year') {
                $baseQuery->whereYear('violations.violation_date', now()->year);
            } elseif ($time === 'last_30_days') {
                $baseQuery->where('violations.violation_date', '>=', now()->subDays(30));
            } elseif ($time === 'custom' && $startDate && $endDate) {
                $baseQuery->whereBetween('violations.violation_date', [$startDate, $endDate]);
            }

            // Class filter (batch)
            if ($class !== 'all') {
                $baseQuery->where('student_details.batch', $class);
            }

            // Count ALL
            $allCount = (clone $baseQuery)
                ->distinct('violations.student_id')
                ->count('violations.student_id');

            // Count MALE - accept M or violations.gender 'male'
            $maleCount = (clone $baseQuery)
                ->where(function($q) {
                    $q->where('pnph_users.gender', 'M')
                      ->orWhereRaw("LOWER(COALESCE(violations.gender, '')) = 'male'");
                })
                ->distinct('violations.student_id')
                ->count('violations.student_id');

            // Count FEMALE - accept F or violations.gender 'female'
            $femaleCount = (clone $baseQuery)
                ->where(function($q) {
                    $q->where('pnph_users.gender', 'F')
                      ->orWhereRaw("LOWER(COALESCE(violations.gender, '')) = 'female'");
                })
                ->distinct('violations.student_id')
                ->count('violations.student_id');

            $result['categories'][] = $category->category_name;
            $result['counts_all'][] = $allCount;
            $result['counts_male'][] = $maleCount;
            $result['counts_female'][] = $femaleCount;
        }
        return response()->json($result);
    }
}

