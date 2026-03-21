@extends('layouts.nav')

@section('content')
<div class="monitor-container">
    <div class="monitor-card">
        <div class="card-header-custom">
            <h2>Grade Submission Monitor</h2>
            @if(isset($schoolPagination) && $schoolPagination->has_pages)
                <p class="school-pagination-info">
                    Showing school {{ $schoolPagination->from }} of {{ $schoolPagination->total }} schools
                </p>
            @endif
        </div>

        <div class="card-body-custom">
            <!-- Enhanced Success and Error Messages -->
            @if(session('success'))
                <div class="alert-custom alert-success-custom">
                    <div class="alert-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Success!</strong>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-custom alert-error-custom">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Error!</strong>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert-custom alert-warning-custom">
                    <div class="alert-icon">
                        <i class="fas fa-exclamation-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Warning!</strong>
                        <p>{{ session('warning') }}</p>
                    </div>
                </div>
            @endif

            @if(isset($message))
                <div class="alert-custom alert-info-custom">
                    <div class="alert-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <div class="alert-content">
                        <strong>Information</strong>
                        <p>{{ $message }}</p>
                    </div>
                </div>
            @endif

            {{-- Filtering Form --}}
            <div class="filter-section">
                 <h3>Filter Submissions</h3>
                 <form action="{{ route('training.grade-submissions.index') }}" method="GET" class="filter-form-custom">
                    <div class="filter-dropdowns-container">
                        <div class="form-group-custom filter-group">
                            <label for="school_id" class="visually-hidden">School</label>
                            <select name="school_id" id="school_id" class="form-control-custom" onchange="updateClassDropdown()">
                                <option value="">All Schools</option>
                                @foreach($allSchools as $school)
                                    <option value="{{ $school->school_id }}" {{ request('school_id') == $school->school_id ? 'selected' : '' }}>
                                        {{ $school->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group-custom filter-group">
                            <label for="class_id" class="visually-hidden">Class</label>
                            <select name="class_id" id="class_id" class="form-control-custom" onchange="clearSubmissionFilterAndSubmit()">
                                <option value="">All Classes</option>
                                @if(request('school_id') && isset($allClassesBySchool[request('school_id')]))
                                    @foreach($allClassesBySchool[request('school_id')] as $class)
                                        <option value="{{ $class->class_id }}" {{ request('class_id') == $class->class_id ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>

                        <div class="form-group-custom filter-group">
                            <label for="filter_key" class="visually-hidden">Semester Term Academic Year</label>
                            <select name="filter_key" id="filter_key" class="form-control-custom">
                                <option value="">All Submissions</option>
                                @foreach ($filterOptions as $option)
                                    <option value="{{ $option['value'] }}" {{ request('filter_key') == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['display'] }}
                                        @if(!request('school_id') && !request('class_id'))
                                            - {{ $option['school_name'] }} ({{ $option['class_name'] }})
                                        @elseif(!request('class_id'))
                                            - {{ $option['class_name'] }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="filter-buttons">
                            <button type="submit" class="btn-custom btn-primary-custom" title="Apply Filters">
                                <i class="fas fa-filter"></i> <span class="btn-text">Filter</span>
                            </button>
                            <button type="button" onclick="location.href='{{ route('training.grade-submissions.index') }}'" class="btn-custom btn-secondary-custom" title="Reset Filters">
                                <i class="fas fa-undo"></i> <span class="btn-text">Reset</span>
                            </button>
                        </div>
                    </div>
                 </form>
            </div>
        </div>
    </div>

    @foreach($schools as $school)
        @php $schoolSubmissions = $submissionsBySchool[$school->school_id] ?? collect(); @endphp
        @if($schoolSubmissions->isNotEmpty())
            <div class="school-container">
                <div class="school-header">
                    <h3>{{ $school->name }}</h3>
                </div>
                <div class="school-content">
                    @foreach($schoolSubmissions as $gradeSubmission)
                        @php
                            // Use paginated students from controller
                            $students = $gradeSubmission->students_paginated;
                            $pagination = $gradeSubmission->students_pagination;
                            // Fetch subjects for this submission
                            $subjects = \DB::table('grade_submission_subject')
                                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                                ->select('subjects.*')
                                ->distinct()
                                ->get();
                            // Fetch grades for this submission
                            $rawGrades = \DB::table('grade_submission_subject')
                                ->join('subjects', 'grade_submission_subject.subject_id', '=', 'subjects.id')
                                ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                                ->select(
                                    'grade_submission_subject.user_id',
                                    'grade_submission_subject.subject_id',
                                    'grade_submission_subject.grade',
                                    'grade_submission_subject.status',
                                    'subjects.name as subject_name'
                                )
                                ->get();
                            $grades = [];
                            foreach ($rawGrades as $grade) {
                                if (!isset($grades[$grade->user_id])) {
                                    $grades[$grade->user_id] = [];
                                }
                                $grades[$grade->user_id][$grade->subject_id] = (object)[
                                    'grade' => $grade->grade,
                                    'status' => $grade->status,
                                    'subject_name' => $grade->subject_name
                                ];
                            }
                        @endphp
                        <div class="submission-section">
                            <div class="submission-header">
                                <div class="submission-title">
                                    <h4>{{ $gradeSubmission->semester }} {{ $gradeSubmission->term }} {{ $gradeSubmission->academic_year }}</h4>
                                    <div class="class-label">
                                        <i class="fas fa-users"></i>
                                        <span>Class: {{ $gradeSubmission->classModel->class_name ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive-custom">
                                <table class="grade-monitor-table">
                                    <thead>
                                        <tr>
                                            <th class="text-center-custom" style="width: 80px">Student ID</th>
                                            <th style="width: 160px">Name</th>
                                            @foreach($subjects as $subject)
                                                <th class="text-center-custom" style="min-width: 80px">{{ $subject->name }}</th>
                                            @endforeach
                                            <th class="text-center-custom" style="width: 100px">Proof</th>
                                            <th class="text-center-custom" style="width: 100px">Status</th>
                                            <th class="text-center-custom" style="width: 180px">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($students as $student)
                                            <tr data-submission-id="{{ $gradeSubmission->id }}" data-student-id="{{ $student->user_id }}">
                                                <td class="text-center-custom small-text">{{ $student->student_id }}</td>
                                                <td class="small-text">{{ $student->name }}</td>
                                                @foreach($subjects as $subject)
                                                    <td class="text-center-custom">
                                                        @php
                                                            $grade = $grades[$student->user_id][$subject->id] ?? null;
                                                            $gradeValue = $grade ? $grade->grade : null;
                                                        @endphp
                                                        
                                                        @if($gradeValue !== null)
                                                            <div class="grade-value small-text">
                                                                @if(in_array(strtoupper($gradeValue), ['INC', 'NC', 'DR']))
                                                                    {{ strtoupper($gradeValue) }}
                                                                @else
                                                                    {{ number_format((float)$gradeValue, 1) }}
                                                                @endif
                                                            </div>
                                                        @else
                                                            <span class="text-muted-custom small-text">Not submitted</span>
                                                        @endif
                                                    </td>
                                                @endforeach
                                                <td class="text-center-custom">
                                                    @php
                                                        $proof = \App\Models\GradeSubmissionProof::where('grade_submission_id', $gradeSubmission->id)
                                                            ->where('user_id', $student->user_id)
                                                            ->first();
                                                    @endphp
                                                    @if($proof)
                                                        <a href="{{ route('training.grade-submissions.view-proof', ['gradeSubmission' => $gradeSubmission->id, 'student' => $student->user_id]) }}"
                                                           class="btn-custom btn-primary-custom"
                                                           title="View Proof">
                                                            <i class="fas fa-eye"></i> <span class="btn-text">View</span>
                                                        </a>
                                                    @else
                                                        <span class="text-muted-custom small-text">No proof</span>
                                                    @endif
                                                 </td>
                                                 <td class="text-center-custom">
                                                     @php
                                                         // Check if student has uploaded all grades
                                                         $hasAllGrades = true;
                                                         foreach($subjects as $subject) {
                                                             $grade = $grades[$student->user_id][$subject->id] ?? null;
                                                             $gradeValue = $grade ? $grade->grade : null;
                                                             if ($gradeValue === null) {
                                                                 $hasAllGrades = false;
                                                                 break;
                                                             }
                                                         }
                                                         
                                                         // Only check for approval status if all grades are uploaded
                                                         if ($hasAllGrades) {
                                                             $status = DB::table('grade_submission_subject')
                                                                 ->where('grade_submission_subject.grade_submission_id', $gradeSubmission->id)
                                                                 ->where('grade_submission_subject.user_id', $student->user_id)
                                                                 ->value('status') ?? 'pending';
                                                         } else {
                                                             $status = 'pending';
                                                         }
                                                         
                                                         // Check if proof exists
                                                         $proof = \App\Models\GradeSubmissionProof::where('grade_submission_id', $gradeSubmission->id)
                                                             ->where('user_id', $student->user_id)
                                                             ->first();
                                                     @endphp
                                                     <span class="status-badge {{ $status === 'approved' ? 'approved' : ($status === 'rejected' ? 'rejected' : 'pending') }}">
                                                         {{ $hasAllGrades ? ucfirst($status) : 'Pending Grades' }}
                                                     </span>
                                                 </td>
                                                 <td class="text-center-custom">
                                                     <div class="action-buttons">
                                                         @php
                                                             $hasIncGrade = false;
                                                             foreach($subjects as $subject) {
                                                                 $grade = $grades[$student->user_id][$subject->id] ?? null;
                                                                 $gradeValue = $grade ? $grade->grade : null;
                                                                 if(strtoupper($gradeValue) === 'INC') {
                                                                     $hasIncGrade = true;
                                                                     break;
                                                                 }
                                                             }
                                                         @endphp
                                                         
                                                         @if($proof && $proof->status === 'pending')
                                                             <div class="action-group">
                                                                 <form method="POST" action="{{ route('training.grade-submissions.update-proof-status', ['gradeSubmission' => $gradeSubmission->id, 'student' => $student->user_id]) }}" class="d-inline">
                                                                     @csrf
                                                                     <input type="hidden" name="status" value="approved">
                                                                     <button type="submit" class="action-button btn-success-custom" title="Approve">
                                                                         <i class="fas fa-check"></i> <span class="btn-text">Approve</span>
                                                                     </button>
                                                                 </form>
                                                                 <form method="POST" action="{{ route('training.grade-submissions.update-proof-status', ['gradeSubmission' => $gradeSubmission->id, 'student' => $student->user_id]) }}" class="d-inline">
                                                                     @csrf
                                                                     <input type="hidden" name="status" value="rejected">
                                                                     <button type="submit" class="action-button btn-danger-custom" title="Reject">
                                                                         <i class="fas fa-times"></i> <span class="btn-text">Reject</span>
                                                                     </button>
                                                                 </form>
                                                             </div>
                                                         @else
                                                             @if($hasIncGrade)
                                                                 <form method="POST" action="{{ route('training.grade-submissions.update-proof-status', ['gradeSubmission' => $gradeSubmission->id, 'student' => $student->user_id]) }}" class="d-inline">
                                                                     @csrf
                                                                     <input type="hidden" name="status" value="pending">
                                                                      <button type="submit" class="action-button btn-warning-custom" title="Edit Status">
                                                                          <i class="fas fa-edit"></i> <span class="btn-text">Edit</span>
                                                                      </button>
                                                                 </form>
                                                             @else
                                                                 <span class="text-muted-custom small-text">
                                                                     Status is final and cannot be changed
                                                                 </span>
                                                             @endif
                                                         @endif
                                                     </div>
                                                 </td>
                                             </tr>
                                         @endforeach
                                     </tbody>
                                </table>
                            </div>

                            <!-- Student Pagination for this submission -->
                            @if($pagination->has_pages)
                                <div class="submission-pagination-container">
                                    <div class="submission-pagination-info">
                                        <small class="text-muted">
                                            Showing {{ $pagination->from }} to {{ $pagination->to }} of {{ $pagination->total }} students
                                        </small>
                                    </div>
                                    <div class="submission-pagination-links">
                                        @if($pagination->on_first_page)
                                            <span class="pagination-btn disabled">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </span>
                                        @else
                                            @php
                                                $prevPage = $pagination->current_page - 1;
                                                $currentUrl = request()->fullUrlWithQuery(['submission_' . $gradeSubmission->id . '_page' => $prevPage]);
                                            @endphp
                                            <a href="{{ $currentUrl }}" class="pagination-btn">
                                                <i class="fas fa-chevron-left"></i> Previous
                                            </a>
                                        @endif

                                        <span class="page-info-small">
                                            Page {{ $pagination->current_page }} of {{ $pagination->last_page }}
                                        </span>

                                        @if($pagination->has_more_pages)
                                            @php
                                                $nextPage = $pagination->current_page + 1;
                                                $currentUrl = request()->fullUrlWithQuery(['submission_' . $gradeSubmission->id . '_page' => $nextPage]);
                                            @endphp
                                            <a href="{{ $currentUrl }}" class="pagination-btn">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </a>
                                        @else
                                            <span class="pagination-btn disabled">
                                                Next <i class="fas fa-chevron-right"></i>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    <!-- School Pagination -->
    @if(isset($schoolPagination) && $schoolPagination->has_pages)
    <div class="school-pagination-container">
        <div class="school-pagination-info">
            <span class="pagination-text">
                <i class="fas fa-school"></i>
                Showing school {{ $schoolPagination->from }} of {{ $schoolPagination->total }} schools
            </span>
        </div>
        <div class="school-pagination-buttons">
            @if ($schoolPagination->on_first_page)
                <span class="pagination-button disabled">
                    <i class="fas fa-chevron-left"></i> Previous School
                </span>
            @else
                @php
                    $prevPage = $schoolPagination->current_page - 1;
                    $currentUrl = request()->fullUrlWithQuery(['school_page' => $prevPage]);
                @endphp
                <a href="{{ $currentUrl }}" class="pagination-button">
                    <i class="fas fa-chevron-left"></i> Previous School
                </a>
            @endif

            <div class="page-info">
                <span class="current-school">
                    School {{ $schoolPagination->current_page }} of {{ $schoolPagination->last_page }}
                </span>
                @if($schools->isNotEmpty())
                    <span class="school-name">
                        ({{ $schools->first()->name }})
                    </span>
                @endif
            </div>

            @if ($schoolPagination->has_more_pages)
                @php
                    $nextPage = $schoolPagination->current_page + 1;
                    $currentUrl = request()->fullUrlWithQuery(['school_page' => $nextPage]);
                @endphp
                <a href="{{ $currentUrl }}" class="pagination-button">
                    Next School <i class="fas fa-chevron-right"></i>
                </a>
            @else
                <span class="pagination-button disabled">
                    Next School <i class="fas fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </div>
    @endif
</div>

<style>
    :root {
        --primary-color: #22bbea;
        --secondary-color: #ff9933;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
        --info-color: #17a2b8;
        --light-bg: #f8f9fa;
        --dark-text: #343a40;
        --border-color: #dee2e6;
        --card-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        --error-color: #dc3545;
    }

    body {
        font-family: 'Arial', sans-serif; /* Using a common sans-serif font */
        line-height: 1.6;
        margin: 0;
        padding: 0;
        background-color: var(--light-bg);
        color: var(--dark-text);
    }

    .monitor-container {
        max-width: 1200px; /* Wider container for the monitor table */
        margin: 20px auto;
        padding: 0 15px;
    }

    .monitor-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        overflow: hidden;
        margin-bottom: 20px;
    }

    .card-header-custom {
        background-color: var(--primary-color);
        color: #fff;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header-custom h2 {
        margin: 0 0 5px 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .school-pagination-info {
        margin: 0;
        font-size: 0.9rem;
        opacity: 0.9;
        color: var(--info-color);
    }

    .school-pagination-info i {
        margin-right: 5px;
    }

    .card-body-custom {
        padding: 20px;
    }

    .alert-custom {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 16px 20px;
        margin-bottom: 20px;
        border-radius: 8px;
        border-left: 4px solid;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        animation: slideInDown 0.3s ease-out;
    }

    .alert-success-custom {
        background-color: #d4edda;
        border-left-color: #28a745;
        color: #155724;
    }

    .alert-error-custom {
        background-color: #f8d7da;
        border-left-color: #dc3545;
        color: #721c24;
    }

    .alert-warning-custom {
        background-color: #fff3cd;
        border-left-color: #ffc107;
        color: #856404;
    }

    .alert-info-custom {
        background-color: #d1ecf1;
        border-left-color: #17a2b8;
        color: #0c5460;
    }

    .alert-icon {
        flex-shrink: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-top: 2px;
    }

    .alert-success-custom .alert-icon {
        color: #28a745;
    }

    .alert-error-custom .alert-icon {
        color: #dc3545;
    }

    .alert-warning-custom .alert-icon {
        color: #ffc107;
    }

    .alert-info-custom .alert-icon {
        color: #17a2b8;
    }

    .alert-icon i {
        font-size: 18px;
    }

    .alert-content {
        flex: 1;
        line-height: 1.5;
    }

    .alert-content strong {
        display: block;
        margin-bottom: 4px;
        font-weight: 600;
    }

    .alert-content p {
        margin: 0;
        font-size: 14px;
    }

    @keyframes slideInDown {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    /* Auto-hide success messages */
    .alert-success-custom {
        position: relative;
    }

    .alert-success-custom::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        height: 3px;
        background: #28a745;
        animation: progressBar 5s linear forwards;
    }

    @keyframes progressBar {
        from {
            width: 100%;
        }
        to {
            width: 0%;
        }
    }

    .filter-section {
        margin-bottom: 20px;
        padding: 15px;
        background-color: var(--light-bg);
        border-radius: 5px;
    }
     .filter-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        font-size: 1.25rem;
        color: var(--dark-text);
     }

    .filter-dropdowns-container {
        display: flex;
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }

    @media (min-width: 768px) {
        .filter-dropdowns-container {
            flex-direction: row;
            align-items: flex-end;
            gap: 15px;
            flex-wrap: wrap;
        }
    }

    .filter-form-custom {
        display: block;
    }

    .form-group-custom.filter-group {
        margin-bottom: 0; /* Remove margin from form group in flex container */
        flex: 1; /* Allow the select to grow equally */
        min-width: 200px; /* Reduced minimum width */
        max-width: 300px; /* Reduced max width to prevent overflow */
    }

    @media (min-width: 768px) {
        .form-group-custom.filter-group {
            flex: 1 1 220px; /* Reduced flexible basis */
            max-width: 320px; /* Reduced max width on desktop */
        }
    }

     .form-control-custom {
        width: 100%; /* Make select fill its container */
        padding: 10px 14px; /* Increased padding for better spacing */
        border: 1px solid var(--border-color);
        border-radius: 5px;
        font-size: 1rem; /* Good readable font size */
        box-sizing: border-box;
        white-space: nowrap; /* Prevent text wrapping */
        overflow: hidden; /* Hide overflow */
        text-overflow: ellipsis; /* Show ellipsis for long text */
        min-height: 44px; /* Consistent height for all dropdowns */
     }
     .form-control-custom:focus {
         border-color: var(--primary-color);
         outline: none;
         box-shadow: 0 0 5px rgba(0, 123, 255, 0.25);
     }

     /* Dropdown option styling for better text visibility */
     .form-control-custom option {
         padding: 8px 12px;
         font-size: 0.95rem;
         white-space: nowrap;
         overflow: hidden;
         text-overflow: ellipsis;
     }

    .visually-hidden {
        position: absolute;
        width: 1px;
        height: 1px;
        margin: -1px;
        padding: 0;
        overflow: hidden;
        clip: rect(0, 0, 0, 0);
        border: 0;
    }

    .action-buttons-container {
        display: flex;
        gap: 10px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        padding: 8px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 8px;
    }

    .btn-custom {
        padding: 6px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        min-width: 85px;
        height: 30px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn-custom i {
        font-size: 13px;
        flex-shrink: 0;
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        color: white;
    }

    .btn-secondary-custom {
        background-color: var(--secondary-color);
        color: white;
    }

    .btn-success-custom {
        background-color: var(--success-color);
        color: white;
    }

    .btn-danger-custom {
        background-color: #dc3545;
        color: white;
    }

    .btn-warning-custom {
        background-color: #ffc107;
        color: #000;
    }

    .filter-buttons {
        display: flex;
        gap: 10px;
        margin-top: 0;
        justify-content: flex-start;
        align-items: center;
        flex-shrink: 0; /* Prevent buttons from shrinking */
        min-width: 180px; /* Reduced minimum width */
        flex-wrap: wrap; /* Allow wrapping on very small screens */
    }

    @media (max-width: 767px) {
        .filter-buttons {
            margin-top: 15px;
            justify-content: center;
            width: 100%;
            gap: 8px;
        }
    }

    @media (max-width: 480px) {
        .filter-buttons {
            flex-direction: column;
            gap: 8px;
            align-items: stretch;
        }
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        max-width: 180px;
        margin: 0 auto;
    }

    .action-group {
        display: flex;
        gap: 4px;
        align-items: center;
        flex-wrap: wrap;
        justify-content: center;
        max-width: 180px;
    }

    /* Hover effects for table rows */
    tr[data-submission-id] {
        transition: background-color 0.2s ease;
    }

    tr[data-submission-id]:hover {
        background-color: rgba(34, 187, 234, 0.05);
    }

    tr[data-submission-id]:hover .btn-custom {
        opacity: 1;
        transform: translateY(0);
    }

    /* Pagination Styles */
    .pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px;
        margin-top: 20px;
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .pagination-info {
        color: #6c757d;
        font-size: 0.9rem;
    }

    .pagination-buttons {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pagination-button {
        padding: 8px 16px;
        border-radius: 6px;
        background: white;
        border: 1px solid #ddd;
        color: #333;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .pagination-button:hover:not(.disabled) {
        background: #f5f5f5;
        border-color: #ccc;
    }

    .pagination-button.disabled {
        color: #aaa;
        cursor: not-allowed;
    }

    .page-info {
        margin: 0 10px;
        font-size: 0.9rem;
        color: #666;
    }

    @media (max-width: 768px) {
        .pagination-container {
            flex-direction: column;
            gap: 15px;
            align-items: flex-start;
        }
    }

    .btn-custom:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-custom:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(34, 187, 234, 0.3);
    }

    .btn-custom:active {
        transform: translateY(0);
        box-shadow: 0 0 1px rgba(0,0,0,0.1);
    }

    .btn-custom.disabled {
        opacity: 0.6;
        cursor: not-allowed;
        box-shadow: none;
    }

    /* Action button hover effects */
    .action-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }

    .action-button:focus {
        outline: none;
        box-shadow: 0 0 0 1px rgba(34, 187, 234, 0.3);
    }

    .action-button:active {
        transform: translateY(0);
        box-shadow: 0 0 1px rgba(0,0,0,0.1);
    }

    /* Ensure icons in action buttons are properly sized */
    .action-button i {
        font-size: 11px;
        flex-shrink: 0;
    }

    .inc-badge {
        background: #ff4444;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 11px;
        margin-left: 4px;
    }

    /* Reduce padding for action buttons in table cells */
    .action-button {
        padding: 4px 8px;
        min-width: 85px;
        height: 28px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .table-responsive-custom {
        width: 100%;
        overflow-x: auto; /* Add horizontal scroll on small screens */
        margin-top: 20px; /* Space above the table */
        -webkit-overflow-scrolling: touch; /* Smooth scrolling on iOS */
    }

    .grade-monitor-table {
        width: 100%;
        border-collapse: collapse;
        border: 1px solid var(--border-color);
    }

    .grade-monitor-table th,
    .grade-monitor-table td {
        padding: 10px;
        border: 1px solid var(--border-color);
        text-align: left;
        vertical-align: middle;
        font-size: 0.9rem;
    }

    .grade-monitor-table th {
        background-color: var(--light-bg);
        font-weight: 600;
        text-align: center; /* Center table headers */
    }

    .grade-monitor-table td {
         text-align: center; /* Center table cells by default */
    }

     .grade-monitor-table tbody tr:nth-child(even) {
        background-color: #f9f9f9; /* Zebra striping */
     }

    .grade-monitor-table tbody tr:hover {
        background-color: #e9e9e9;
    }

    .text-center-custom {
        text-align: center;
    }

    .small-text {
        font-size: 0.85rem;
    }

    .grade-value {
        font-weight: 500;
    }

     .text-muted-custom {
        color: #6c757d;
     }

    .debug-section {
        margin-top: 30px;
        padding-top: 20px;
        border-top: 1px solid var(--border-color);
    }
     .debug-section h3 {
        font-size: 1.25rem;
        margin-top: 0;
        margin-bottom: 10px;
        color: var(--dark-text);
     }

    .debug-pre {
        background-color: #e9ecef;
        padding: 15px;
        border-radius: 5px;
        overflow-x: auto; /* Add scroll for long debug output */
        font-size: 0.85rem;
        color: #333;
    }

    .school-container {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: var(--card-shadow);
        margin-bottom: 30px;
        overflow: hidden;
    }

    .school-header {
        background-color: var(--primary-color);
        color: #fff;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .school-header h3 {
        margin: 0;
        font-size: 1.3rem;
        font-weight: 600;
    }

    .school-content {
        padding: 20px;
    }

    .submission-section {
        margin-bottom: 30px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        overflow: hidden;
        background-color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .submission-section:last-child {
        margin-bottom: 0;
    }

    .submission-header {
        background-color: var(--light-bg);
        padding: 15px 20px;
        border-bottom: 1px solid var(--border-color);
        margin-bottom: 0;
    }

    .submission-title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
    }

    .submission-header h4 {
        margin: 0;
        color: var(--dark-text);
        font-size: 1.1rem;
        font-weight: 600;
    }

    .class-label {
        display: flex;
        align-items: center;
        gap: 6px;
        background: var(--primary-color);
        color: white;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 500;
        box-shadow: 0 2px 4px rgba(34, 187, 234, 0.2);
    }

    .class-label i {
        font-size: 0.8rem;
    }

    @media (max-width: 768px) {
        .submission-title {
            flex-direction: column;
            align-items: flex-start;
        }

        .class-label {
            align-self: flex-end;
        }
    }

    /* Submission-specific pagination styles */
    .submission-pagination-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px 20px;
        margin-top: 15px;
        background-color: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }

    .submission-pagination-info {
        color: #6c757d;
        font-size: 14px;
        font-weight: 500;
    }

    .submission-pagination-links {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .pagination-btn {
        padding: 6px 12px;
        border-radius: 4px;
        background: white;
        border: 1px solid #dee2e6;
        color: #22bbea;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 4px;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .pagination-btn:hover:not(.disabled) {
        background: #f8f9fa;
        border-color: #22bbea;
        color: #1a9bc7;
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(34, 187, 234, 0.2);
    }

    .pagination-btn.disabled {
        color: #6c757d;
        background: #f8f9fa;
        cursor: not-allowed;
        opacity: 0.6;
    }

    .page-info-small {
        margin: 0 8px;
        font-size: 13px;
        color: #495057;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .submission-pagination-container {
            flex-direction: column;
            gap: 10px;
            text-align: center;
        }

        .submission-pagination-info {
            order: 2;
        }

        .submission-pagination-links {
            order: 1;
            justify-content: center;
        }
    }

    /* School Pagination Styles */
    .school-pagination-container {
        margin-top: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 2px solid var(--primary-color);
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(34, 187, 234, 0.1);
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 15px;
    }

    .school-pagination-info {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .pagination-text {
        color: var(--dark-text);
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .pagination-text i {
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    .school-pagination-buttons {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .current-school {
        font-weight: 600;
        color: var(--primary-color);
    }

    .school-name {
        color: var(--dark-text);
        font-style: italic;
        margin-left: 5px;
        font-size: 0.9rem;
    }

    .page-info {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        text-align: center;
    }

    /* Responsive button text hiding */
    @media (max-width: 1024px) {
        .btn-text {
            display: none;
        }

        .btn-custom {
            min-width: 40px;
            padding: 6px 8px;
        }

        .action-button {
            min-width: 36px;
            padding: 4px 6px;
        }

        .action-buttons {
            max-width: 80px;
        }

        .action-group {
            max-width: 80px;
        }
    }

    @media (max-width: 768px) {
        .school-pagination-container {
            flex-direction: column;
            text-align: center;
        }

        .school-pagination-buttons {
            width: 100%;
            justify-content: center;
        }

        .grade-monitor-table th,
        .grade-monitor-table td {
            padding: 6px;
            font-size: 0.8rem;
        }

        .action-buttons {
            max-width: 120px;
        }

        .action-group {
            max-width: 120px;
        }
    }

    @media (max-width: 480px) {
        .grade-monitor-table th,
        .grade-monitor-table td {
            padding: 4px;
            font-size: 0.75rem;
        }

        .action-button {
            min-width: 32px;
            height: 26px;
            font-size: 10px;
            padding: 3px 4px;
        }

        .btn-custom {
            min-width: 36px;
            height: 28px;
            font-size: 11px;
            padding: 4px 6px;
        }

        .action-buttons {
            max-width: 70px;
            gap: 2px;
        }

        .action-group {
            max-width: 70px;
            gap: 2px;
        }
    }
</style>
@endsection

<script>
// All classes data for dynamic filtering
const allClassesBySchool = @json($allClassesBySchool ?? []);

function updateClassDropdown(autoSubmit = true) {
    const schoolSelect = document.getElementById('school_id');
    const classSelect = document.getElementById('class_id');
    const submissionSelect = document.getElementById('filter_key');
    const selectedSchoolId = schoolSelect.value;

    // Clear current class options
    classSelect.innerHTML = '<option value="">All Classes</option>';

    if (selectedSchoolId && allClassesBySchool[selectedSchoolId]) {
        // Add classes for selected school
        allClassesBySchool[selectedSchoolId].forEach(function(classItem) {
            const option = document.createElement('option');
            option.value = classItem.class_id;
            option.textContent = classItem.class_name;

            // Maintain selected class if it belongs to the selected school
            if (classItem.class_id === '{{ request("class_id") }}') {
                option.selected = true;
            }

            classSelect.appendChild(option);
        });
    }

    // Clear submission filter when school/class changes
    if (autoSubmit) {
        submissionSelect.value = '';
    }

    // Submit form to apply school filter only if requested
    if (autoSubmit) {
        schoolSelect.form.submit();
    }
}

function clearSubmissionFilterAndSubmit() {
    const submissionSelect = document.getElementById('filter_key');
    const classSelect = document.getElementById('class_id');

    // Clear submission filter when class changes
    submissionSelect.value = '';

    // Submit form
    classSelect.form.submit();
}

function updateSubmissionFilter() {
    const submissionSelect = document.getElementById('filter_key');

    // Clear submission filter and submit form
    submissionSelect.value = '';
    submissionSelect.form.submit();
}

document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success messages after 5 seconds
    const successAlerts = document.querySelectorAll('.alert-success-custom');
    successAlerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s ease-out';
            alert.style.opacity = '0';
            setTimeout(function() {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Add click to dismiss functionality for all alerts
    const allAlerts = document.querySelectorAll('.alert-custom');
    allAlerts.forEach(function(alert) {
        alert.style.cursor = 'pointer';
        alert.title = 'Click to dismiss';
        alert.addEventListener('click', function() {
            this.style.transition = 'opacity 0.3s ease-out';
            this.style.opacity = '0';
            setTimeout(() => {
                this.remove();
            }, 300);
        });
    });

    // Initialize class dropdown on page load (without auto-submit)
    updateClassDropdown(false);
});
</script>