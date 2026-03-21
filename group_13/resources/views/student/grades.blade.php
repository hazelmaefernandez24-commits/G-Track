@extends('layouts.student_layout')

@section('content')
<style>
/* Pure CSS Grade Status - Training Design */

/* Reset and Base */
* {
    box-sizing: border-box;
}

/* Main Container */
.student-grades-container {
    width: 100%;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: linear-gradient(135deg, #f0fbff 0%, #ffffff 50%, #f8feff 100%);
    min-height: calc(100vh - 80px);
    font-family: 'Poppins', sans-serif;
    position: relative;
}

.student-grades-container::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #22bbea 0%, #ff9933 50%, #22bbea 100%);
    z-index: 1;
    border-radius: 0 0 2px 2px;
}

/* Header */
.grades-page-header {
    margin-bottom: 30px;
    padding: 25px 0;
    border-bottom: 3px solid transparent;
    background: linear-gradient(135deg, rgba(34, 187, 234, 0.1) 0%, rgba(255, 153, 51, 0.1) 100%);
    border-radius: 12px;
    position: relative;
    margin-top: 10px;
}

.grades-page-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80%;
    height: 3px;
    background: linear-gradient(90deg, #22bbea 0%, #ff9933 100%);
    border-radius: 2px;
}

.grades-page-title {
    font-size: 28px;
    color: #333;
    margin: 0;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 15px;
}

.grades-page-title i {
    color: #22bbea;
    font-size: 26px;
}

/* Cards */
.grades-card {
    background: linear-gradient(135deg, #ffffff 0%, #f8feff 100%);
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(34, 187, 234, 0.1);
    margin-bottom: 30px;
    overflow: hidden;
    border: 1px solid rgba(34, 187, 234, 0.2);
    transition: all 0.3s ease;
}

.grades-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(34, 187, 234, 0.15);
    border-color: rgba(34, 187, 234, 0.3);
}

.grades-card-header {
    background: linear-gradient(135deg, #22bbea 0%, #1a9bc8 100%);
    color: white;
    padding: 20px 25px;
    font-weight: 600;
    font-size: 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-bottom: none;
    position: relative;
}

.grades-card-header::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 3px;
    background: linear-gradient(90deg, #ff9933 0%, #22bbea 100%);
}

.grades-card-header i {
    font-size: 16px;
    opacity: 0.9;
}

.grades-card-body {
    padding: 30px 25px;
    background: linear-gradient(135deg, #ffffff 0%, #f8feff 100%);
}

/* Filter Form */
.grades-filter-form {
    margin: 0;
    width: 100%;
}

.grades-filter-row {
    display: flex;
    gap: 25px;
    align-items: flex-end;
    flex-wrap: wrap;
    margin-bottom: 0;
}

.grades-filter-group {
    flex: 1;
    min-width: 220px;
    display: flex;
    flex-direction: column;
}

.grades-filter-label {
    display: block;
    margin-bottom: 10px;
    color: #495057;
    font-weight: 600;
    font-size: 15px;
    line-height: 1.4;
}

.grades-filter-select {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid rgba(34, 187, 234, 0.3);
    border-radius: 8px;
    font-size: 15px;
    background: linear-gradient(135deg, #ffffff 0%, #f8feff 100%);
    color: #495057;
    transition: all 0.3s ease;
    font-family: inherit;
    line-height: 1.5;
    appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2322bbea' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
    background-position: right 12px center;
    background-repeat: no-repeat;
    background-size: 16px;
    padding-right: 40px;
    box-shadow: 0 2px 4px rgba(34, 187, 234, 0.1);
}

.grades-filter-select:focus {
    border-color: #22bbea;
    outline: none;
    box-shadow: 0 0 0 4px rgba(34, 187, 234, 0.15), 0 4px 8px rgba(34, 187, 234, 0.1);
    background: linear-gradient(135deg, #ffffff 0%, #f0fbff 100%);
    transform: translateY(-1px);
}

.grades-filter-select:hover {
    border-color: #22bbea;
    box-shadow: 0 4px 8px rgba(34, 187, 234, 0.15);
    transform: translateY(-1px);
}

.grades-filter-actions {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    flex-wrap: wrap;
}

/* Buttons */
.grades-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border: 2px solid transparent;
    border-radius: 6px;
    text-decoration: none;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    min-width: 120px;
    line-height: 1.4;
    text-transform: none;
}

.grades-btn:focus {
    outline: none;
    box-shadow: 0 0 0 4px rgba(34, 187, 234, 0.2);
}

.grades-btn-primary {
    background: linear-gradient(135deg, #22bbea 0%, #1a9bc8 100%);
    border-color: #22bbea;
    color: white;
    position: relative;
    overflow: hidden;
}

.grades-btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.grades-btn-primary:hover::before {
    left: 100%;
}

.grades-btn-primary:hover {
    background: linear-gradient(135deg, #1a9bc8 0%, #22bbea 100%);
    border-color: #1a9bc8;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(34, 187, 234, 0.4);
}

.grades-btn-secondary {
    background: linear-gradient(135deg, #ff9933 0%, #e6851a 100%);
    border-color: #ff9933;
    color: white;
    position: relative;
    overflow: hidden;
}

.grades-btn-secondary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.grades-btn-secondary:hover::before {
    left: 100%;
}

.grades-btn-secondary:hover {
    background: linear-gradient(135deg, #e6851a 0%, #ff9933 100%);
    border-color: #e6851a;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(255, 153, 51, 0.4);
}


/* Table Styles */
.grades-table-wrapper {
    overflow-x: auto;
    border-radius: 12px;
    border: 2px solid rgba(34, 187, 234, 0.2);
    background: linear-gradient(135deg, #ffffff 0%, #f8feff 100%);
    margin-top: 20px;
    box-shadow: 0 4px 15px rgba(34, 187, 234, 0.1);
    transition: all 0.3s ease;
}

.grades-table-wrapper:hover {
    border-color: rgba(34, 187, 234, 0.3);
    box-shadow: 0 6px 20px rgba(34, 187, 234, 0.15);
}

.grades-data-table {
    width: 100%;
    border-collapse: collapse;
    background: white;
    font-size: 15px;
    margin: 0;
    min-width: 700px;
}

.grades-data-table thead {
    background: #22bbea;
}

.grades-data-table th {
    background-color: #22bbea;
    color: white;
    padding: 18px 20px;
    text-align: left;
    font-weight: 700;
    font-size: 14px;
    border: none;
    white-space: nowrap;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-bottom: 3px solid #1e9bc4;
}

.grades-data-table td {
    padding: 18px 20px;
    border-bottom: 1px solid #e9ecef;
    color: #495057;
    vertical-align: middle;
    line-height: 1.5;
}

.grades-data-table tbody tr {
    transition: all 0.3s ease;
}

.grades-data-table tbody tr:hover {
    background-color: #f8f9fa;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.grades-data-table tbody tr:last-child td {
    border-bottom: none;
}

/* Table Cell Styles */
.grades-subject-code {
    font-weight: 800;
    color: #2c3e50;
    font-family: 'Courier New', 'Monaco', monospace;
    font-size: 14px;
    letter-spacing: 0.5px;
    background: #f8f9fa;
    padding: 8px 12px;
    border-radius: 4px;
    display: inline-block;
}

.grades-subject-name {
    color: #495057;
    font-weight: 600;
    max-width: 280px;
    line-height: 1.4;
}

.grades-term-info,
.grades-year-info {
    color: #6c757d;
    font-weight: 500;
    font-size: 14px;
}

.grades-grade-display {
    font-weight: 800;
    font-size: 18px;
    text-align: center;
    padding: 10px 16px;
    border-radius: 6px;
    min-width: 70px;
    display: inline-block;
    border: 2px solid transparent;
    line-height: 1.2;
}

.grades-grade-excellent {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.grades-grade-good {
    color: #0c5460;
    background-color: #d1ecf1;
    border-color: #bee5eb;
}

.grades-grade-fair {
    color: #856404;
    background-color: #fff3cd;
    border-color: #ffeaa7;
}

.grades-grade-poor {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

/* Status Badges */
.grades-status-badge {
    display: inline-flex;
    align-items: center;
    padding: 8px 14px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.grades-badge-approved,
.grades-badge-passed {
    background-color: #d4edda;
    color: #155724;
    border-color: #c3e6cb;
}

.grades-badge-pending {
    background-color: #fff3cd;
    color: #856404;
    border-color: #ffeaa7;
}

.grades-badge-rejected,
.grades-badge-failed {
    background-color: #f8d7da;
    color: #721c24;
    border-color: #f5c6cb;
}

.grades-badge-incomplete {
    background-color: #d1ecf1;
    color: #0c5460;
    border-color: #bee5eb;
}

/* Empty State */
.grades-no-data {
    text-align: center;
    padding: 60px 30px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 12px;
    color: #6c757d;
    margin: 30px 0;
    border: 2px dashed #ced4da;
}

.grades-no-data i {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.6;
    color: #22bbea;
    display: block;
}

.grades-no-data h4 {
    margin-bottom: 12px;
    color: #495057;
    font-weight: 700;
    font-size: 24px;
}

.grades-no-data p {
    margin: 0;
    font-size: 16px;
    color: #6c757d;
    font-weight: 500;
}

/* Pagination */
.grades-pagination-container {
    margin: 30px 0;
    display: flex;
    justify-content: center;
}

.grades-pagination-container .pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.grades-pagination-container .pagination li {
    margin: 0;
}

.grades-pagination-container .pagination li a,
.grades-pagination-container .pagination li span {
    display: block;
    padding: 12px 16px;
    text-decoration: none;
    color: #495057;
    background: white;
    border: 1px solid #dee2e6;
    border-right: none;
    transition: all 0.2s ease;
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}

.grades-pagination-container .pagination li:last-child a,
.grades-pagination-container .pagination li:last-child span {
    border-right: 1px solid #dee2e6;
}

.grades-pagination-container .pagination li a:hover {
    background: #f8f9fa;
    color: #22bbea;
}

.grades-pagination-container .pagination li.active span {
    background: #22bbea;
    color: white;
    border-color: #22bbea;
}

.grades-pagination-container .pagination li.disabled span {
    color: #6c757d;
    background: #f8f9fa;
    cursor: not-allowed;
}

/* Per-table Pagination */
.grades-table-pagination {
    margin-top: 20px;
    padding: 15px 0;
    border-top: 1px solid #e9ecef;
}

.pagination-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
}

.pagination-controls {
    display: flex;
    align-items: center;
    gap: 10px;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination-btn-enabled {
    background: #22bbea;
    color: white;
    border: 1px solid #22bbea;
}

.pagination-btn-enabled:hover {
    background: #1a9bc8;
    border-color: #1a9bc8;
    color: white;
    text-decoration: none;
}

.pagination-btn-disabled {
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
    cursor: not-allowed;
}

.pagination-pages {
    display: flex;
    align-items: center;
    gap: 5px;
}

.pagination-page {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    text-decoration: none;
    color: #495057;
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 14px;
    font-family: 'Poppins', sans-serif;
    font-weight: 500;
    transition: all 0.2s ease;
}

.pagination-page:hover {
    background: #f8f9fa;
    color: #22bbea;
    border-color: #22bbea;
    text-decoration: none;
}

.pagination-page-active {
    background: #22bbea;
    color: white;
    border-color: #22bbea;
    cursor: default;
}

.pagination-page-active:hover {
    background: #22bbea;
    color: white;
    border-color: #22bbea;
}

/* Responsive Design */
@media (max-width: 992px) {
    .student-grades-container {
        padding: 15px;
    }

    .grades-filter-row {
        gap: 20px;
    }

    .grades-filter-group {
        min-width: 200px;
    }
}

@media (max-width: 768px) {
    .student-grades-container {
        padding: 12px;
    }

    .grades-page-header {
        margin-bottom: 25px;
        padding-bottom: 15px;
    }

    .grades-page-title {
        font-size: 24px;
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .grades-card-header {
        padding: 15px 20px;
        font-size: 16px;
    }

    .grades-card-body {
        padding: 20px;
    }

    .grades-filter-row {
        flex-direction: column;
        gap: 20px;
        align-items: stretch;
    }

    .grades-filter-group {
        min-width: 100%;
        width: 100%;
    }

    .grades-filter-select {
        padding: 14px 16px;
        font-size: 16px;
        padding-right: 42px;
    }

    .grades-filter-actions {
        flex-direction: column;
        width: 100%;
        gap: 12px;
    }

    .grades-btn {
        width: 100%;
        justify-content: center;
        padding: 14px 20px;
        font-size: 16px;
    }



    .grades-data-table th,
    .grades-data-table td {
        padding: 12px 10px;
        font-size: 14px;
    }

    .grades-subject-code {
        font-size: 12px;
        padding: 6px 10px;
    }

    .grades-subject-name {
        max-width: 180px;
        font-size: 14px;
    }

    .grades-grade-display {
        font-size: 16px;
        padding: 8px 12px;
        min-width: 60px;
    }

    .grades-term-info,
    .grades-year-info {
        font-size: 13px;
    }
}

@media (max-width: 576px) {
    .student-grades-container {
        padding: 10px;
    }

    .grades-page-title {
        font-size: 20px;
    }

    .grades-card-header {
        padding: 12px 15px;
        font-size: 14px;
    }

    .grades-card-body {
        padding: 15px;
    }

    .grades-filter-select {
        padding: 12px 14px;
        font-size: 16px;
        padding-right: 38px;
    }

    .grades-btn {
        padding: 12px 18px;
        font-size: 15px;
    }



    .grades-data-table th,
    .grades-data-table td {
        padding: 10px 8px;
        font-size: 13px;
    }

    .grades-subject-code {
        font-size: 11px;
        padding: 4px 8px;
    }

    .grades-subject-name {
        max-width: 140px;
        font-size: 13px;
    }

    .grades-grade-display {
        font-size: 14px;
        padding: 6px 10px;
        min-width: 50px;
    }

    .grades-status-badge {
        font-size: 11px;
        padding: 6px 10px;
    }

    .grades-no-data {
        padding: 40px 20px;
    }

    .grades-no-data i {
        font-size: 48px;
    }

    .grades-no-data h4 {
        font-size: 20px;
    }

    .grades-no-data p {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    .student-grades-container {
        padding: 8px;
    }

    .grades-page-title {
        font-size: 18px;
    }



    .grades-data-table th,
    .grades-data-table td {
        padding: 8px 6px;
        font-size: 12px;
    }

    .grades-grade-display {
        font-size: 13px;
        padding: 5px 8px;
        min-width: 45px;
    }

    /* Per-table pagination responsive */
    .pagination-wrapper {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .pagination-controls {
        flex-wrap: wrap;
        justify-content: center;
    }

    .pagination-btn {
        padding: 6px 10px;
        font-size: 13px;
    }

    .pagination-page {
        width: 32px;
        height: 32px;
        font-size: 13px;
    }

    .pagination-info {
        font-size: 13px;
    }
}
</style>

<div class="student-grades-container">
    <!-- Page Header -->
    <div class="grades-page-header">
        <h1 class="grades-page-title">
            <i class="fas fa-chart-line"></i>
            My Grade Status
        </h1>

        @if(request()->has('submission_id'))
            <div style="margin-top: 15px;">
                <a href="{{ route('student.grade-submissions.list') }}" class="grades-btn grades-btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    Back to Grade Submissions
                </a>
            </div>
        @endif
    </div>

    <!-- Filter Card -->
    <div class="grades-card">
        <div class="grades-card-header">
            <i class="fas fa-filter"></i>
            Filter Submissions
        </div>
        <div class="grades-card-body">
            <form action="{{ route('student.grades') }}" method="GET" class="grades-filter-form">
                <div class="grades-filter-row">
                    <div class="grades-filter-group">
                        <label for="filter_key" class="grades-filter-label">Submission</label>
                        <select name="filter_key" id="filter_key" class="grades-filter-select">
                            <option value="">All Submissions</option>
                            @foreach($filterOptions as $option)
                                <option value="{{ $option }}" {{ request('filter_key') == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="grades-filter-actions">
                        <button type="submit" class="grades-btn grades-btn-primary">
                            <i class="fas fa-search"></i>
                            Apply Filter
                        </button>
                        @if(request()->has('filter_key'))
                            <a href="{{ route('student.grades') }}" class="grades-btn grades-btn-secondary">
                                <i class="fas fa-times"></i>
                                Clear Filter
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Grade Submissions by Submission -->
    @if(isset($gradeSubmissions) && $gradeSubmissions->count() > 0)
        @foreach($gradeSubmissions as $submission)
            @if($submission->subjects->count() > 0)
                <div class="grades-card">
                    <div class="grades-card-header">
                        <i class="fas fa-table"></i>
                        Semester: {{ $submission->semester }} | Term: {{ ucfirst($submission->term) }} | Year: {{ $submission->academic_year }}
                    </div>
                    <div class="grades-card-body">
                        <div class="grades-table-wrapper">
                            <table class="grades-data-table">
                                <thead>
                                    <tr>
                                        <th>Subject Code</th>
                                        <th>Subject Name</th>
                                        <th>Grade</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($submission->subjects as $subject)
                                        @if($subject->student_submission)
                                            @php
                                                $grade = $subject->student_submission->grade;
                                                $status = strtolower($subject->student_submission->status ?? 'pending');
                                                $gradeDisplay = is_numeric($grade) ? number_format($grade, 2) : ($grade ?? 'N/A');
                                                $subjectCode = $subject->offer_code ?? $subject->code ?? '';
                                                $subjectName = $subject->name ?? 'Unnamed Subject';

                                                // Get school's grading system
                                                $passingMin = $studentSchool->passing_grade_min ?? 1.0;
                                                $passingMax = $studentSchool->passing_grade_max ?? 3.0;

                                                // Determine grade class based on school's grading system
                                                $gradeClass = 'grades-grade-poor';
                                                if (is_numeric($grade)) {
                                                    $gradeValue = floatval($grade);
                                                    $range = $passingMax - $passingMin;

                                                    if ($gradeValue >= $passingMin && $gradeValue <= $passingMax) {
                                                        // Within passing range - determine excellence level
                                                        $excellentThreshold = $passingMin + ($range * 0.8); // Top 20%
                                                        $goodThreshold = $passingMin + ($range * 0.5); // Top 50%

                                                        if ($gradeValue >= $excellentThreshold) {
                                                            $gradeClass = 'grades-grade-excellent';
                                                        } elseif ($gradeValue >= $goodThreshold) {
                                                            $gradeClass = 'grades-grade-good';
                                                        } else {
                                                            $gradeClass = 'grades-grade-fair';
                                                        }
                                                    } else {
                                                        // Below passing grade
                                                        $gradeClass = 'grades-grade-poor';
                                                    }
                                                }

                                                // Determine status badge class
                                                $badgeClass = 'grades-badge-' . str_replace(' ', '-', $status);
                                            @endphp
                                            <tr>
                                                <td><span class="grades-subject-code">{{ $subjectCode }}</span></td>
                                                <td class="grades-subject-name">{{ $subjectName }}</td>
                                                <td>
                                                    <span class="grades-grade-display {{ $gradeClass }}">{{ $gradeDisplay }}</span>
                                                </td>
                                                <td>
                                                    <span class="grades-status-badge {{ $badgeClass }}">
                                                        {{ ucfirst($status) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <!-- Per-table Pagination -->
                        <div class="grades-table-pagination">
                            @php
                                $pagination = $submission->pagination;
                                $currentPage = $pagination->current_page;
                                $lastPage = $pagination->last_page;
                                $hasPrevious = $pagination->has_previous_pages;
                                $hasNext = $pagination->has_more_pages;

                                // Build query parameters for this submission's pagination
                                $queryParams = request()->query();
                                $baseUrl = request()->url();
                            @endphp

                            <div class="pagination-wrapper">
                                <div class="pagination-info">
                                    Showing {{ ($currentPage - 1) * 10 + 1 }} to {{ min($currentPage * 10, $pagination->total) }} of {{ $pagination->total }} subjects
                                </div>

                                <div class="pagination-controls">
                                    <!-- Previous Button -->
                                    @if($hasPrevious)
                                        @php
                                            $prevParams = $queryParams;
                                            $prevParams['submission_' . $submission->id . '_page'] = $currentPage - 1;
                                        @endphp
                                        <a href="{{ $baseUrl }}?{{ http_build_query($prevParams) }}" class="pagination-btn pagination-btn-enabled">
                                            <i class="fas fa-chevron-left"></i>
                                            Previous
                                        </a>
                                    @else
                                        <span class="pagination-btn pagination-btn-disabled">
                                            <i class="fas fa-chevron-left"></i>
                                            Previous
                                        </span>
                                    @endif

                                    <!-- Page Numbers -->
                                    <div class="pagination-pages">
                                        @for($page = 1; $page <= $lastPage; $page++)
                                            @if($page == $currentPage)
                                                <span class="pagination-page pagination-page-active">{{ $page }}</span>
                                            @else
                                                @php
                                                    $pageParams = $queryParams;
                                                    $pageParams['submission_' . $submission->id . '_page'] = $page;
                                                @endphp
                                                <a href="{{ $baseUrl }}?{{ http_build_query($pageParams) }}" class="pagination-page">{{ $page }}</a>
                                            @endif
                                        @endfor
                                    </div>

                                    <!-- Next Button -->
                                    @if($hasNext)
                                        @php
                                            $nextParams = $queryParams;
                                            $nextParams['submission_' . $submission->id . '_page'] = $currentPage + 1;
                                        @endphp
                                        <a href="{{ $baseUrl }}?{{ http_build_query($nextParams) }}" class="pagination-btn pagination-btn-enabled">
                                            Next
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    @else
                                        <span class="pagination-btn pagination-btn-disabled">
                                            Next
                                            <i class="fas fa-chevron-right"></i>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach

        <!-- Pagination -->
        <div class="grades-pagination-container">
            {{ $gradeSubmissions->appends(request()->query())->links() }}
        </div>
    @else
        <div class="grades-no-data">
            <i class="fas fa-table"></i>
            <h4>No Grade Data Available</h4>
            <p>You don't have any submitted grades to display yet.</p>
        </div>
    @endif
</div>
@endsection


