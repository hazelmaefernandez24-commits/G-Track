@extends('layouts.nav')

@section('content')
<style>
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.header-section h1 {
    font-weight: 300;
    color: #333;
    margin-bottom: 10px;
}

.header-section hr {
    border: none;
    height: 1px;
    background-color: #ddd;
    margin-bottom: 15px;
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
}

.card-body {
    padding: 20px;
}

.table {
    margin-bottom: 0;
}

.table th {
    background-color: #22bbea;
    border-top: none;
    font-weight: 600;
    color: #fff;
}

.table-hover tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.badge {
    font-size: 0.875em;
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.form-select, .form-control {
    border-radius: 6px;
    border: 1px solid #ced4da;
}

.form-select:focus, .form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.alert {
    border-radius: 6px;
    border: none;
}

.text-muted {
    color: #6c757d !important;
}

/* Intervention Details Styling */
.intervention-details {
    font-style: italic;
    line-height: 1.4;
    max-width: 250px;
    word-wrap: break-word;
    display: block;
    margin-top: 2px;
}

.text-info {
    color: #22bbea !important;
    font-weight: 600;
}

/* Filter Section Styling */
.filter-inline-container {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: end;
    margin-bottom: 20px;
}

.filter-group {
    display: flex;
    flex-direction: column;
    min-width: 200px;
    flex: 1;
}

.filter-group label {
    margin-bottom: 5px;
    font-weight: 500;
    color: #495057;
    font-size: 14px;
}

.filter-group select {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 6px;
    background-color: #fff;
    font-size: 14px;
}

.filter-group select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    outline: none;
}

.filter-buttons {
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid #e9ecef;
}

.filter-buttons .btn {
    margin-right: 12px;
    margin-bottom: 8px;
    min-width: 120px;
    padding: 8px 16px;
}

.filter-buttons .btn:last-child {
    margin-right: 0;
}

/* Pagination Styling */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
    font-weight: 500;
    flex-shrink: 0;
}

.pagination-wrapper {
    display: flex;
    justify-content: flex-end;
    flex-grow: 1;
}

.pagination-wrapper .pagination {
    margin: 0;
    display: flex;
    flex-direction: row;
    list-style: none;
    padding: 0;
}

.pagination-wrapper .page-item {
    display: inline-block;
    margin: 0 1px;
}

.pagination-wrapper .page-link {
    color: #22bbea;
    border-color: #dee2e6;
    padding: 8px 12px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 6px;
    border: 1px solid #dee2e6;
    text-decoration: none;
    display: inline-block;
    line-height: 1.25;
    transition: all 0.3s ease;
}

.pagination-wrapper .page-link:hover {
    color: #1a9bc7;
    background-color: #f8f9fa;
    border-color: #22bbea;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(34, 187, 234, 0.2);
}

.pagination-wrapper .page-item.active .page-link {
    background-color: #22bbea;
    border-color: #22bbea;
    color: white;
    font-weight: 600;
    box-shadow: 0 2px 4px rgba(34, 187, 234, 0.3);
}

.pagination-wrapper .page-item.disabled .page-link {
    color: #6c757d;
    background-color: #fff;
    border-color: #dee2e6;
    cursor: not-allowed;
}

.pagination-wrapper .page-item.disabled .page-link:hover {
    transform: none;
    box-shadow: none;
}

@media (max-width: 768px) {
    .filter-inline-container {
        flex-direction: column;
        gap: 15px;
    }

    .filter-group {
        min-width: 100%;
    }

    .pagination-container {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .pagination-info {
        order: 2;
        text-align: center;
    }

    .pagination-wrapper {
        order: 1;
        justify-content: center;
    }
}
</style>

<div class="page-container">
    <div class="header-section">
        <h1 style="font-weight: 300">🎯 Intervention Overview</h1>
        <hr>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter Section -->
    <div class="card shadow-sm mb-4">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-funnel me-2"></i>
                Filter Interventions
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('training.intervention.index') }}" id="filterForm">
                <div class="filter-inline-container">
                    <div class="filter-group">
                        <label for="school_id">School</label>
                        <select id="school_id" name="school_id">
                            <option value="">All Schools</option>
                            @foreach($schools as $school)
                                <option value="{{ $school->school_id }}"
                                    {{ request('school_id') == $school->school_id ? 'selected' : '' }}>
                                    {{ $school->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="class_id">Class</label>
                        <select id="class_id" name="class_id" disabled>
                            <option value="">Select School First</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->class_id }}"
                                    {{ request('class_id') == $class->class_id ? 'selected' : '' }}>
                                    {{ $class->class_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="submission_id">Submission</label>
                        <select id="submission_id" name="submission_id" disabled>
                            <option value="">Select Class First</option>
                            @foreach($submissions as $submission)
                                <option value="{{ $submission['id'] }}"
                                    {{ request('submission_id') == $submission['id'] ? 'selected' : '' }}>
                                    {{ $submission['display_name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="">All Status</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="done" {{ request('status') == 'done' ? 'selected' : '' }}>Done</option>
                        </select>
                    </div>
                </div>

                <div class="filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('training.intervention.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle me-2"></i>Clear Filters
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="window.location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Refresh
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Interventions Table -->
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="bi bi-table me-2"></i>
                Intervention Status Overview
                <span class="badge bg-light text-dark ms-2">{{ $interventions->total() }} total interventions</span>
                <!-- <span class="badge bg-info text-white ms-1">View Only</span> -->
            </h5>
        </div>
        <div class="card-body p-0">
            @if($interventions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="text-center">No. of Students</th>
                                <th>Subject</th>
                                <th>Submission</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Date</th>
                                <th>Educator Assigned</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($interventions as $intervention)
                                <tr class="{{ $intervention->status === 'done' ? 'table-success' : 'table-warning' }}">
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6">{{ $intervention->student_count }}</span>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $intervention->subject->name ?? 'N/A' }}</strong>
                                            @if($intervention->school)
                                                <br><small class="text-muted">{{ $intervention->school->name }}</small>
                                            @endif
                                            @if($intervention->classModel)
                                                <br><small class="text-muted">Class: {{ $intervention->classModel->class_name }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($intervention->gradeSubmission)
                                            <div>
                                                <strong>{{ $intervention->gradeSubmission->semester }} {{ $intervention->gradeSubmission->term }}</strong>
                                                <br><small class="text-muted">{{ $intervention->gradeSubmission->academic_year }}</small>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($intervention->status === 'done')
                                            <span class="badge bg-success">Done</span>
                                        @else
                                            <span class="badge bg-warning text-dark">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($intervention->intervention_date)
                                            {{ $intervention->intervention_date->format('M d, Y') }}
                                        @else
                                            <span class="text-muted">Not Set</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($intervention->educatorAssigned)
                                            <div>
                                                <strong>{{ $intervention->educatorAssigned->user_fname }} {{ $intervention->educatorAssigned->user_lname }}</strong>
                                                <br><small class="text-muted">{{ $intervention->educatorAssigned->user_email }}</small>
                                                @if($intervention->remarks && trim($intervention->remarks) !== '')
                                                    <br><small class="text-info"><strong>Intervention Details:</strong></small>
                                                    <br><small class="text-muted intervention-details">{{ Str::limit($intervention->remarks, 100) }}</small>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">Not Assigned</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 3rem; opacity: 0.5;"></i>
                    <h5 class="mt-3">No Interventions Found</h5>
                    <p class="mb-0">
                        @if(request()->hasAny(['school_id', 'class_id', 'subject_id', 'status']))
                            No interventions match your current filters. Try adjusting your search criteria.
                        @else
                            Student haven't finalized their grades yet.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Pagination Section -->
    @if($interventions->count() > 0)
        <div class="card shadow-sm mt-3">
            <div class="card-body">
                <div class="pagination-container">
                    <div class="pagination-info">
                        <small class="text-muted">
                            Showing {{ $interventions->firstItem() ?? 1 }} to {{ $interventions->lastItem() ?? $interventions->count() }} of {{ $interventions->total() }} interventions
                        </small>
                    </div>
                    <div class="pagination-wrapper">
                        @if($interventions->hasPages())
                            {{ $interventions->links('custom-pagination') }}
                        @else
                            <!-- Show pagination even for single page -->
                            <nav aria-label="Pagination Navigation">
                                <ul class="pagination">
                                    <li class="page-item disabled"><span class="page-link">Previous</span></li>
                                    <li class="page-item active"><span class="page-link">1</span></li>
                                    <li class="page-item disabled"><span class="page-link">Next</span></li>
                                </ul>
                            </nav>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Summary Cards -->
    @if($interventions->count() > 0)
        <!-- <div class="row mt-4">
            <div class="col-md-3">
                <div class="card bg-warning text-dark">
                    <div class="card-body text-center">
                        <h3 class="mb-1">{{ $interventions->where('status', 'pending')->count() }}</h3>
                        <p class="mb-0">Pending</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-1">{{ $interventions->where('status', 'done')->count() }}</h3>
                        <p class="mb-0">Completed</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-info text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-1">{{ $interventions->sum('student_count') }}</h3>
                        <p class="mb-0">Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body text-center">
                        <h3 class="mb-1">{{ $interventions->pluck('subject_id')->unique()->count() }}</h3>
                        <p class="mb-0">Subjects</p>
                    </div>
                </div>
            </div>
        </div> -->
    @endif
</div>

<br><br>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const schoolSelect = document.getElementById('school_id');
    const classSelect = document.getElementById('class_id');
    const submissionSelect = document.getElementById('submission_id');

    // Initialize form state
    initializeForm();

    // School change handler
    schoolSelect.addEventListener('change', function() {
        const schoolId = this.value;

        // Reset class and submission dropdowns
        classSelect.innerHTML = '<option value="">Loading classes...</option>';
        classSelect.disabled = true;
        submissionSelect.innerHTML = '<option value="">Select Class First</option>';
        submissionSelect.disabled = true;

        if (schoolId) {
            // Fetch classes for selected school
            fetch(`/training/intervention/classes/${schoolId}`)
                .then(response => response.json())
                .then(classes => {
                    classSelect.innerHTML = '<option value="">All Classes</option>';
                    classes.forEach(classItem => {
                        const option = document.createElement('option');
                        option.value = classItem.class_id;
                        option.textContent = classItem.class_name;
                        if (classItem.class_id === '{{ request("class_id") }}') {
                            option.selected = true;
                        }
                        classSelect.appendChild(option);
                    });
                    classSelect.disabled = false;

                    // Trigger class change if there's a selected class
                    if (classSelect.value) {
                        classSelect.dispatchEvent(new Event('change'));
                    }
                })
                .catch(error => {
                    console.error('Error fetching classes:', error);
                    classSelect.innerHTML = '<option value="">Error loading classes</option>';
                });
        } else {
            classSelect.innerHTML = '<option value="">Select School First</option>';
            classSelect.disabled = true;
        }
    });

    // Class change handler
    classSelect.addEventListener('change', function() {
        const schoolId = schoolSelect.value;
        const classId = this.value;

        // Reset submission dropdown
        submissionSelect.innerHTML = '<option value="">Loading submissions...</option>';
        submissionSelect.disabled = true;

        if (schoolId && classId) {
            // Fetch submissions for selected school and class
            fetch(`/training/intervention/submissions?school_id=${schoolId}&class_id=${classId}`)
                .then(response => response.json())
                .then(submissions => {
                    submissionSelect.innerHTML = '<option value="">All Submissions</option>';
                    submissions.forEach(submission => {
                        const option = document.createElement('option');
                        option.value = submission.id;
                        option.textContent = submission.display_name;
                        if (submission.id == '{{ request("submission_id") }}') {
                            option.selected = true;
                        }
                        submissionSelect.appendChild(option);
                    });
                    submissionSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error fetching submissions:', error);
                    submissionSelect.innerHTML = '<option value="">Error loading submissions</option>';
                });
        } else {
            submissionSelect.innerHTML = '<option value="">Select Class First</option>';
            submissionSelect.disabled = true;
        }
    });

    function initializeForm() {
        // Enable class dropdown if school is selected
        if (schoolSelect.value) {
            classSelect.disabled = false;
            schoolSelect.dispatchEvent(new Event('change'));
        }
    }
});

// Add loading states for form submissions
document.getElementById('filterForm').addEventListener('submit', function() {
    const submitButton = this.querySelector('button[type="submit"]');
    const originalText = submitButton.innerHTML;
    submitButton.innerHTML = '<i class="bi bi-spinner-border spinner-border-sm me-1"></i> Filtering...';
    submitButton.disabled = true;
});
</script>
@endsection
