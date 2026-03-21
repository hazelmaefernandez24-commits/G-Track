@extends('layouts.student_layout')

@section('content')
<div class="dashboard-container">
    <!-- Subject List -->
    <div class="subject-list-container">
        @if(isset($subjectsWithGrades) && $subjectsWithGrades->count() > 0)
            <div class="subject-list">
                @foreach($subjectsWithGrades as $subject)
                    <div class="subject-card status-{{ strtolower($subject->status) }}">
                        <div class="subject-code">{{ $subject->subject_code }}</div>
                        <div class="subject-name">{{ $subject->subject_name }}</div>
                        <div class="subject-grade">
                            <span class="grade">{{ $subject->grade ?? 'N/A' }}</span>
                            <span class="status-badge">{{ strtoupper($subject->status) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <h2>Dashboard</h2>

    <!-- Filter Section -->
    <div class="filter-card">
        <div class="filter-card-header">
            <h5>
                <i class="fas fa-filter"></i>
                Filter Submissions
            </h5>
        </div>
        <div class="filter-card-body">
            <form action="{{ route('student.dashboard') }}" method="GET" class="filter-form">
                <div class="filter-inline-container">
                    <div class="filter-group">
                        <label for="filter_key">Submission</label>
                        <select name="filter_key" id="filter_key" class="filter-select">
                            <option value="">All Submissions</option>
                            @foreach($filterOptions as $option)
                                <option value="{{ $option }}" {{ request('filter_key') == $option ? 'selected' : '' }}>{{ $option }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="filter-buttons">
                    <button type="submit" class="btn btn-filter">
                        <i class="fas fa-search"></i>
                        Apply Filter
                    </button>
                    @if(request()->has('filter_key'))
                        <a href="{{ route('student.dashboard') }}" class="btn btn-clear">
                            <i class="fas fa-times"></i>
                            Clear Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="status-cards-container">
        <div class="status-card pass">
            <div class="status-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="status-details">
                <h3>Pass</h3>
                <p>{{ $statusCounts['pass'] ?? 0 }} Subjects</p>
            </div>
        </div>

        <div class="status-card fail">
            <div class="status-icon">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="status-details">
                <h3>Fail</h3>
                <p>{{ $statusCounts['fail'] ?? 0 }} Subjects</p>
            </div>
        </div>

        <div class="status-card inc">
            <div class="status-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="status-details">
                <h3>INC</h3>
                <p>{{ $statusCounts['inc'] ?? 0 }} Subjects</p>
            </div>
        </div>

        <div class="status-card nc">
            <div class="status-icon">
                <i class="fas fa-minus-circle"></i>
            </div>
            <div class="status-details">
                <h3>NC</h3>
                <p>{{ $statusCounts['nc'] ?? 0 }} Subjects</p>
            </div>
        </div>

        <div class="status-card dr">
            <div class="status-icon">
                <i class="fas fa-ban"></i>
            </div>
            <div class="status-details">
                <h3>DR</h3>
                <p>{{ $statusCounts['dr'] ?? 0 }} Subjects</p>
            </div>
        </div>
    </div>
    
    <div class="submissions-section">
    
    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($gradeSubmissions->isEmpty())
        <div class="no-submissions">
            <p>No grade submissions found.</p>
        </div>
    @else
        <div class="submissions-grid">
            @foreach($gradeSubmissions as $submission)
                <div class="submission-card">
                    <div class="card-header">
                        <h3>{{ $submission->term ?? 'N/A' }}</h3>
                    </div>
                    <div class="card-content">
                        <div class="info-row">
                            <span class="label">Semester:</span>
                            <span class="value">{{ $submission->semester ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">Academic Year:</span>
                            <span class="value">{{ $submission->academic_year ?? 'N/A' }}</span>
                        </div>

                        @php
                            // Get the student's pivot data
                            $studentPivot = $submission->students->where('pivot.user_id', Auth::id())->first();
                            $pivotStatus = $studentPivot ? ($studentPivot->pivot->status ?? 'pending') : 'pending';
                            
                            // Get the latest proof status
                            $proof = $submission->proofs->where('user_id', Auth::id())->sortByDesc('created_at')->first();
                            $proofStatus = $proof ? $proof->status : null;
                            
                            // Determine the overall status to display
                            $overallStatus = $pivotStatus; // Default to pivot status
                            
                            // If there's a proof with a more specific status, use that
                            if ($proofStatus && in_array($proofStatus, ['approved', 'rejected'])) {
                                $overallStatus = $proofStatus;
                            } elseif ($proofStatus === 'pending' && $pivotStatus === 'submitted') {
                                $overallStatus = 'pending_review';
                            }
                        @endphp
                        
                        <div class="info-row">
                            <span class="label">Status:</span>
                            <span class="status {{ $overallStatus }}">
                                @if($overallStatus === 'pending_review')
                                    Pending Review
                                @else
                                    {{ ucfirst($overallStatus) }}
                                @endif
                            </span>
                        </div>
                        
                        <div class="info-row">
                            <span class="label">Submitted:</span>
                            <span class="date">{{ $submission->created_at ? $submission->created_at->format('M d, Y') : 'N/A' }}</span>
                        </div>

                        {{-- Add the button based on submission status --}}
                        <div class="card-actions">
                            @php
                                // Check if the student has any grades submitted for this submission
                                $hasGrades = $submission->students->contains('pivot.user_id', Auth::id()) && 
                                          $submission->students->where('pivot.user_id', Auth::id())->first()->pivot->grade !== null;
                                
                                // Check if there's a proof submitted
                                $hasProof = $proof !== null;
                            @endphp
                            
                            @if(!$hasGrades && !$hasProof)
                                {{-- New submission - no grades or proof yet --}}
                                <a href="{{ route('student.submit-grades.show', $submission->id) }}" class="btn-submit-grades">Submit Grades</a>
                            @elseif($overallStatus === 'rejected' || $pivotStatus === 'rejected' || $proofStatus === 'rejected')
                                <a href="{{ route('student.submit-grades.show', $submission->id) }}" class="btn-submit-grades">Resubmit Grades</a>
                            @elseif(in_array($overallStatus, ['submitted', 'pending_review', 'pending']))
                                <a href="{{ route('student.view-submission', $submission->id) }}" class="btn-view-submission">View Submission</a>
                            @elseif($overallStatus === 'approved')
                                <a href="{{ route('student.view-submission', $submission->id) }}" class="btn-view-submission">View Approved Submission</a>
                            @else
                                <a href="{{ route('student.submit-grades.show', $submission->id) }}" class="btn-submit-grades">Submit Grades</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    </div>
</div>

<style>
/* Dashboard Header */
.dashboard-header {
    display: flex;
    align-items: center;
    margin-bottom: 20px;
    gap: 15px;
}

.grade-status-logo {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.grade-status-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

.dashboard-header h2 {
    margin: 0;
    font-size: 1.8rem;
    color: #2c3e50;
}

/* Section Header */
.section-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin: 25px 0 20px;
}

.section-logo {
    width: 36px;
    height: 36px;
    object-fit: contain;
}

.section-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.8rem;
}

/* Status Cards Styles */
.status-cards-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 15px;
    margin: 20px 0 30px;
    padding-bottom: 10px;
}

/* Status Card Styles */
.status-card {
    background: #fff;
    border-radius: 10px;
    padding: 15px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border-left: 5px solid;
    min-width: 0; /* Prevent flex item from overflowing */
}

.status-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Subject List Styles */
.subject-list-container {
    margin: 30px 0;
}

.subject-list {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 15px;
    margin-top: 15px;
}

.subject-card {
    background: #fff;
    border-radius: 8px;
    padding: 15px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border-left: 4px solid #ddd;
    transition: all 0.3s ease;
}

.subject-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
}

.subject-code {
    font-weight: 600;
    color: #444;
    margin-bottom: 5px;
    font-size: 0.9em;
}

.subject-name {
    font-size: 1.1em;
    color: #333;
    margin-bottom: 10px;
}

.subject-grade {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-top: 8px;
    border-top: 1px solid #eee;
}

.grade {
    font-weight: 600;
    font-size: 1.1em;
}

.status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 0.8em;
    font-weight: 600;
    text-transform: uppercase;
}

/* Status Colors */
.status-pass, .status-PASS {
    border-left-color: #28a745;
}

.status-fail, .status-FAIL {
    border-left-color: #dc3545;
}

.status-inc, .status-INC {
    border-left-color: #ffc107;
}

.status-nc, .status-NC {
    border-left-color: #6c757d;
}

.status-dr, .status-DR {
    border-left-color: #343a40;
}

/* Status Badge Colors */
.status-badge {
    background-color: #e9ecef;
    color: #495057;
}

.status-pass .status-badge, 
.status-PASS .status-badge {
    background-color: #d4edda;
    color: #155724;
}

.status-fail .status-badge,
.status-FAIL .status-badge {
    background-color: #f8d7da;
    color: #721c24;
}

.status-inc .status-badge,
.status-INC .status-badge {
    background-color: #fff3cd;
    color: #856404;
}

.status-nc .status-badge,
.status-NC .status-badge {
    background-color: #e2e3e5;
    color: #383d41;
}

.status-dr .status-badge,
.status-DR .status-badge {
    background-color: #d6d8db;
    color: #1b1e21;
}

.no-subjects {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
    color: #6c757d;
}

.status-icon {
    font-size: 30px;
    margin-right: 15px;
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.status-details h3 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #333;
}

.status-details p {
    margin: 5px 0 0;
    font-size: 14px;
    color: #666;
}

/* Card Colors */
.status-card.pass {
    border-color: #28a745;
}

.status-card.pass .status-icon {
    background-color: #28a745;
}

.status-card.fail {
    border-color: #dc3545;
}

.status-card.fail .status-icon {
    background-color: #dc3545;
}

.status-card.inc {
    border-color: #ffc107;
}

.status-card.inc .status-icon {
    background-color: #ffc107;
}

.status-card.nc {
    border-color: #6c757d;
}

.status-card.nc .status-icon {
    background-color: #6c757d;
}

.status-card.dr {
    border-color: #6f42c1;
}

.status-card.dr .status-icon {
    background-color: #6f42c1;
}

/* Responsive adjustments */
@media (max-width: 1200px) {
    .status-cards-container {
        grid-template-columns: repeat(5, 180px);
    }
}

@media (max-width: 992px) {
    .status-cards-container {
        grid-template-columns: repeat(5, 160px);
    }
}

@media (max-width: 768px) {
    .status-cards-container {
        grid-template-columns: repeat(5, 140px);
    }
}

/* For very small screens, allow horizontal scrolling */
@media (max-width: 576px) {
    .status-cards-container {
        grid-template-columns: repeat(5, 130px);
    }
    
    .status-card {
        min-width: 120px;
        padding: 15px 10px;
    }
    
    .status-icon {
        width: 40px;
        height: 40px;
        font-size: 24px;
        margin-right: 10px;
    }
    
    .status-details h3 {
        font-size: 14px;
    }
    
    .status-details p {
        font-size: 12px;
    }
}

.dashboard-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
}

h1 {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
}

/* Filter Card Styles */
.filter-card {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 30px;
    border: 1px solid #e9ecef;
    overflow: hidden;
}

.filter-card-header {
    background: #22bbea;
    padding: 15px 20px;
    border-bottom: 1px solid #e9ecef;
}

.filter-card-header h5 {
    margin: 0;
    color: #fff;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-card-header i {
    font-size: 14px;
}

.filter-card-body {
    padding: 20px;
}

.filter-form {
    margin: 0;
}

.filter-inline-container {
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
    margin-bottom: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 180px;
    flex: 1;
}

.filter-group label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 6px;
    font-size: 14px;
}

.filter-select {
    padding: 10px 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    background-color: #fff;
    font-size: 14px;
    color: #495057;
    transition: all 0.3s ease;
    cursor: pointer;
}

.filter-select:focus {
    border-color: #22bbea;
    box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.1);
    outline: none;
}

.filter-select:hover {
    border-color: #22bbea;
}

.filter-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 120px;
    justify-content: center;
}

.btn-filter {
    background: #22bbea;
    color: #fff;
    box-shadow: 0 2px 4px rgba(34, 187, 234, 0.3);
}

.btn-filter:hover {
    background: linear-gradient(135deg, #1e9bc4 0%, #1a87a8 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(34, 187, 234, 0.4);
}

.btn-clear {
    background: #6c757d;
    color: #fff;
    box-shadow: 0 2px 4px rgba(108, 117, 125, 0.3);
}

.btn-clear:hover {
    background: #5a6268;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.4);
}

.alert {
    padding: 15px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: .25rem;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.submissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.submission-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.card-header {
    background: #f8f9fa;
    padding: 15px;
    border-bottom: 1px solid #eee;
}

.card-header h3 {
    margin: 0;
    color: #333;
    font-size: 18px;
}

.card-content {
    padding: 15px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.info-row:last-child {
    margin-bottom: 0;
}

.label {
    color: #666;
    font-weight: 500;
}

.value,
.date {
    color: #333;
}

.status {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 500;
}

.status.pending {
    background: #fff3cd;
    color: #856404;
}

.status.approved {
    background: #d4edda;
    color: #155724;
}

.status.rejected {
    background: #f8d7da;
    color: #721c24;
}

.status.submitted {
     background-color: #cce5ff;
     color: #004085;
}

.no-submissions {
    text-align: center;
    padding: 40px;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.no-submissions p {
    color: #6c757d;
    font-size: 1.1em;
    margin: 0;
}

@media (max-width: 768px) {
    .submissions-grid {
        grid-template-columns: 1fr;
    }
}

/* Styles for the subjects list (removed as subjects are not listed directly on the card anymore) */
/*
.card-content h4 {
    margin-top: 15px;
    margin-bottom: 5px;
    color: #555;
    font-size: 16px;
}

.card-content ul {
    list-style: disc inside;
    padding-left: 0;
    margin-bottom: 10px;
}

.card-content ul li {
    margin-bottom: 3px;
    color: #666;
}
*/

.card-actions {
    margin-top: auto; /* Push actions to the bottom */
    padding-top: 15px; /* Add some space above the button */
    border-top: 1px solid #eee; /* Optional: Add a separator */
    text-align: right; /* Align button to the right */
}

.btn-submit-grades,
.btn-view-submission {
    display: inline-block;
    color: white;
    padding: 8px 16px;
    border-radius: 5px;
    text-decoration: none; /* Remove underline */
    font-size: 14px;
    transition: background-color 0.3s ease;
}

.btn-submit-grades {
    background-color: #007bff; /* Primary blue color */
}

.btn-view-submission {
    background-color: #6c757d; /* Secondary gray color */
}

.btn-submit-grades:hover {
    background-color: #0056b3;
}

.btn-view-submission:hover {
    background-color: #5a6268;
}

/* Responsive Design Improvements */

/* Large screens (1200px and up) */
@media (min-width: 1200px) {
    .dashboard-container {
        padding: 0;
        max-width: 1400px;
    }

    .status-cards-container {
        grid-template-columns: repeat(5, 1fr);
        gap: 20px;
    }

    .subject-list {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }

    .submissions-grid {
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    }
}

/* Medium screens (992px to 1199px) */
@media (max-width: 1199px) {
    .dashboard-container {
        padding: 0;
    }

    .status-cards-container {
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
    }
}

/* Small screens (768px to 991px) */
@media (max-width: 991px) {
    .dashboard-container {
        padding: 0;
    }

    .status-cards-container {
        grid-template-columns: repeat(5, 1fr);
        gap: 12px;
    }

    .status-card {
        padding: 12px;
    }

    .status-icon {
        width: 40px;
        height: 40px;
        font-size: 20px;
        margin-right: 10px;
    }

    .status-details h3 {
        font-size: 14px;
    }

    .status-details p {
        font-size: 12px;
    }
}

/* Tablet screens (768px and below) */
@media (max-width: 768px) {
    .dashboard-container {
        padding: 0;
    }

    /* Make status cards scroll horizontally on mobile */
    .status-cards-container {
        display: flex;
        overflow-x: auto;
        padding-bottom: 10px;
        gap: 10px;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none; /* Firefox */
        -ms-overflow-style: none; /* IE and Edge */
    }

    .status-cards-container::-webkit-scrollbar {
        display: none; /* Chrome, Safari, Opera */
    }

    .status-card {
        flex: 0 0 140px;
        min-width: 140px;
        margin-right: 0;
    }

    /* Make subject list single column on mobile */
    .subject-list {
        grid-template-columns: 1fr;
        gap: 12px;
    }

    /* Improve filter form for mobile */
    .filter-card-body {
        padding: 15px;
    }

    .filter-inline-container {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }

    .filter-group {
        min-width: 100%;
    }

    .filter-select {
        padding: 12px;
        font-size: 16px; /* Prevent zoom on iOS */
    }

    .filter-buttons {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        padding: 12px;
        font-size: 16px;
    }

    /* Make submissions grid single column on mobile */
    .submissions-grid {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    /* Adjust card content for mobile */
    .submission-card {
        margin-bottom: 0;
    }

    .card-header h3 {
        font-size: 1.1rem;
    }

    .info-row {
        font-size: 0.9rem;
        flex-direction: column;
        align-items: flex-start;
        gap: 2px;
    }

    .info-row .label {
        font-weight: 600;
        color: #555;
    }

    .info-row .value,
    .info-row .date,
    .info-row .status {
        margin-left: 0;
    }

    /* Make buttons full width on mobile */
    .btn-submit-grades,
    .btn-view-submission {
        width: 100%;
        margin-top: 10px;
        padding: 12px;
        font-size: 16px;
        text-align: center;
    }
}

/* Mobile screens (576px and below) */
@media (max-width: 576px) {
    .dashboard-container {
        padding: 0;
    }

    .status-card {
        flex: 0 0 130px;
        min-width: 130px;
        padding: 10px;
    }

    .status-icon {
        width: 35px;
        height: 35px;
        font-size: 18px;
        margin-right: 8px;
    }

    .status-details h3 {
        font-size: 13px;
    }

    .status-details p {
        font-size: 11px;
    }

    .subject-card {
        padding: 12px;
    }

    .subject-code {
        font-size: 0.85em;
    }

    .subject-name {
        font-size: 0.9em;
    }

    .status-badge {
        font-size: 0.7em;
        padding: 2px 6px;
    }

    .card-header {
        padding: 12px;
    }

    .card-content {
        padding: 12px;
    }

    .card-header h3 {
        font-size: 1rem;
    }

    .info-row {
        font-size: 0.85rem;
        margin-bottom: 8px;
    }

    .btn-submit-grades,
    .btn-view-submission {
        padding: 10px;
        font-size: 14px;
    }
}

/* Extra small screens (480px and below) */
@media (max-width: 480px) {
    .dashboard-container {
        padding: 0;
    }

    h1, h2 {
        font-size: 1.3rem;
        margin-bottom: 15px;
    }

    .status-card {
        flex: 0 0 120px;
        min-width: 120px;
        padding: 8px;
    }

    .status-icon {
        width: 30px;
        height: 30px;
        font-size: 16px;
        margin-right: 6px;
    }

    .status-details h3 {
        font-size: 12px;
    }

    .status-details p {
        font-size: 10px;
    }

    .subject-card {
        padding: 10px;
    }

    .subject-code {
        font-size: 0.8em;
    }

    .subject-name {
        font-size: 0.85em;
    }

    .status-badge {
        font-size: 0.65em;
        padding: 1px 4px;
    }

    .card-header {
        padding: 10px;
    }

    .card-content {
        padding: 10px;
    }

    .card-header h3 {
        font-size: 0.95rem;
    }

    .info-row {
        font-size: 0.8rem;
        margin-bottom: 6px;
    }

    .btn-submit-grades,
    .btn-view-submission {
        padding: 8px;
        font-size: 13px;
    }

    /* Improve form elements for very small screens */
    .filter-card-header {
        padding: 12px 15px;
    }

    .filter-card-header h5 {
        font-size: 14px;
    }

    .filter-card-body {
        padding: 12px;
    }

    .filter-select {
        padding: 10px;
        font-size: 14px;
    }

    .btn {
        padding: 10px;
        font-size: 14px;
    }
}

/* Landscape orientation for mobile devices */
@media (max-height: 500px) and (orientation: landscape) {
    .dashboard-container {
        padding: 0;
    }

    .status-cards-container {
        margin: 10px 0 15px;
    }

    .submissions-section {
        margin-top: 15px;
    }
}

/* High DPI displays */
@media (-webkit-min-device-pixel-ratio: 2), (min-resolution: 192dpi) {
    .PN-logo {
        image-rendering: -webkit-optimize-contrast;
        image-rendering: crisp-edges;
    }
}
</style>
@endsection