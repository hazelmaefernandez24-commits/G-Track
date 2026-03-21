@extends('layouts.nav')

@section('content')
<div class="page-container">
    <div class="header-section">
        <h1 style="font-weight: 300">📊 Subject Intervention Analytics</h1>
        <hr>
        <p class="text-muted">View and analyze subjects that need intervention based on student grades. Select a school, class, and submission to view the report.</p>
    </div>
    
    <div class="filter-card">
        <div class="filter-card-header">
            <h5>
                <i class="bi bi-funnel me-2"></i>
                Filter Subject Intervention
            </h5>
        </div>
        <div class="filter-card-body">
            <div class="filter-inline-container">
                <div class="filter-group">
                    <label for="schoolSelect">School</label>
                    <select id="schoolSelect">
                        <option value="">Select School</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="classSelect">Class</label>
                    <select id="classSelect" disabled>
                        <option value="">Select Class</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="submissionSelect">Submission</label>
                    <select id="submissionSelect" disabled>
                        <option value="">Select Submission</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div id="interventionTableContainer">
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-graph-up" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0">Select a school, class, and submission to view intervention report</p>
                </div>
            </div>
        </div>
    </div>
</div>

<br>

<script>
// Prevent form submission on Enter key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        return false;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Prevent form submission
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            return false;
        });
    }

    // Load schools
    fetch('/training/analytics/schools')
        .then(res => res.json())
        .then(data => {
            const schoolSelect = document.getElementById('schoolSelect');
            data.forEach(school => {
                const opt = document.createElement('option');
                opt.value = school.id;
                opt.textContent = school.name;
                schoolSelect.appendChild(opt);
            });
        });

    // School change handler
    document.getElementById('schoolSelect').addEventListener('change', function() {
        const schoolId = this.value;
        const classSelect = document.getElementById('classSelect');
        const submissionSelect = document.getElementById('submissionSelect');
        
        classSelect.innerHTML = '<option value="">Select Class</option>';
        submissionSelect.innerHTML = '<option value="">Select Submission</option>';
        classSelect.disabled = true;
        submissionSelect.disabled = true;
        
        const container = document.getElementById('interventionTableContainer');
        container.innerHTML = `
            <div class="d-flex justify-content-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading classes...</span>
            </div>`;
        
        if (schoolId) {
            fetch(`/training/analytics/classes/${schoolId}`)
                .then(res => res.json())
                .then(data => {
                    if (data.length === 0) {
                        classSelect.innerHTML = '<option value="">No classes found</option>';
                        container.innerHTML = `
                            <div class="text-center p-5">
                                <i class="bi bi-collection" style="font-size: 3rem; color: #6c757d; opacity: 0.7;"></i>
                                <p class="mt-3 mb-0 instruction-text">No classes found for this school.</p>
                            </div>`;
                    } else {
                        data.forEach(cls => {
                            const opt = document.createElement('option');
                            opt.value = cls.id;
                            opt.textContent = cls.name;
                            classSelect.appendChild(opt);
                        });
                        classSelect.disabled = false;
                        
                        container.innerHTML = `
                            <div class="text-center p-5 text-muted">
                                <i class="bi-graph-up" style="font-size: 2.5rem; opacity: 0.5;"></i>
                                <p class="mt-3 mb-0 instruction-text">Select a class and submission to view intervention report</p>
                            </div>`;
                    }
                })
                .catch(error => {
                    console.error('Error loading classes:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi-exclamation-triangle-fill me-2"></i>
                            Failed to load classes. Please try again.
                        </div>`;
                });
        } else {
            container.innerHTML = `
                <div class="text-center p-5 text-muted">
                    <i class="bi-graph-up" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0 instruction-text">Select a school, class, and submission to view intervention report</p>
                </div>`;
        }
    });

    // Class change handler
    document.getElementById('classSelect').addEventListener('change', function() {
        const schoolId = document.getElementById('schoolSelect').value;
        const classId = this.value;
        const submissionSelect = document.getElementById('submissionSelect');
        
        submissionSelect.innerHTML = '<option value="">Select Submission</option>';
        submissionSelect.disabled = true;
        
        if (schoolId && classId) {
            const container = document.getElementById('interventionTableContainer');
            container.innerHTML = `
                <div class="d-flex justify-content-center p-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading submissions...</span>
                </div>`;
                
            fetch(`/training/analytics/class-submissions/${schoolId}/${classId}`)
                .then(res => res.json())
                .then(data => {
                    submissionSelect.innerHTML = '<option value="">Select Submission</option>';
                    
                    if (data.length === 0) {
                        submissionSelect.innerHTML = '<option value="">No submissions found</option>';
                        container.innerHTML = `
                            <div class="text-center p-5">
                                <i class="bi-collection" style="font-size: 3rem; color: #6c757d; opacity: 0.7;"></i>
                                <p class="mt-3 mb-0 instruction-text">No submissions found for this class.</p>
                            </div>`;
                        return;
                    }
                    
                    // Sort submissions: approved first, then by created_at desc
                    const sortedData = [...data].sort((a, b) => {
                        if (a.status === 'approved' && b.status !== 'approved') return -1;
                        if (a.status !== 'approved' && b.status === 'approved') return 1;
                        return 0;
                    });
                    
                    sortedData.forEach(sub => {
                        if (sub && sub.id && sub.label) {
                            const opt = document.createElement('option');
                            opt.value = sub.id;
                            opt.textContent = sub.label;
                            submissionSelect.appendChild(opt);
                        }
                    });
                    
                    submissionSelect.disabled = false;
                    
                    container.innerHTML = `
                        <div class="text-center p-5 text-muted">
                            <i class="bi-graph-up" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            <p class="mt-3 mb-0 instruction-text">Select a submission to view intervention report</p>
                        </div>`;
                })
                .catch(error => {
                    console.error('Error loading submissions:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi-exclamation-triangle-fill me-2"></i>
                            Failed to load submissions. Please try again.
                        </div>`;
                });
        }
    });
    
    // Submission change handler
    document.getElementById('submissionSelect').addEventListener('change', function() {
        const schoolId = document.getElementById('schoolSelect').value;
        const classId = document.getElementById('classSelect').value;
        const submissionId = this.value;
        
        if (schoolId && classId && submissionId) {
            fetchInterventionData(schoolId, classId, submissionId);
        }
    });
    
    // Function to fetch and render intervention data
    function fetchInterventionData(schoolId, classId, submissionId) {
        const container = document.getElementById('interventionTableContainer');
        container.innerHTML = `
            <div class="d-flex justify-content-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading intervention data...</span>
            </div>`;
            
        fetch(`/training/analytics/subject-intervention-data?school_id=${schoolId}&class_id=${classId}&submission_id=${submissionId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    container.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi-exclamation-triangle-fill me-2"></i>
                            ${data.error}
                        </div>`;
                    return;
                }
                
                if (!data.subjects || data.subjects.length === 0) {
                    container.innerHTML = `
                        <div class="text-center p-5">
                            <i class="bi-collection" style="font-size: 3rem; color: #6c757d; opacity: 0.5;"></i>
                            <p class="mt-3 mb-0">No subject data found for this submission.</p>
                        </div>`;
                    return;
                }
                
                // Render the intervention table
                renderInterventionTable(data);
            })
            .catch(error => {
                console.error('Error loading intervention data:', error);
                container.innerHTML = `
                    <div class="alert alert-danger m-3">
                        <i class="bi-exclamation-triangle-fill me-2"></i>
                        Failed to load intervention data. Please try again.
                    </div>`;
            });
    }
    
    // Function to render the intervention table
    function renderInterventionTable(data) {
        const container = document.getElementById('interventionTableContainer');
        
        // Create header with school, class, and submission info
        let headerHtml = `
            <div class="card-header bg-light p-3">
                <div class="d-flex flex-column align-items-center justify-content-center w-100">
                    <div style="font-size: 1.5rem; font-weight: 600; line-height: 1.2; text-align: center;">
                        ${data.school.name} - ${data.class_name}
                    </div>
                    <div class="mt-2" style="font-size: 1rem; color: #6c757d; white-space: nowrap; text-align: center;">
                        ${data.submission.semester ? `Semester: ${data.submission.semester}` : ''}
                        ${data.submission.term ? ` | Term: ${data.submission.term}` : ''}
                        ${data.submission.academic_year ? ` | Academic Year: ${data.submission.academic_year}` : ''}
                    </div>
                </div>
            </div>`;
            
        // Create table
        let tableHtml = `
            <div class="table-responsive">
                <div class="table-container">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Subject</th>
                                <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Passed</th>
                                <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Failed</th>
                                <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">INC</th>
                                <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">DR</th>
                                <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">NC</th>
                                <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>`;
        
        // Add rows for each subject
        data.subjects.forEach(subject => {
            // Determine row class based on remarks
            let rowClass = '';
            if (subject.remarks === 'Need Intervention') {
                rowClass = 'table-danger';
            } else if (subject.remarks === 'Pending') {
                rowClass = 'table-warning';
            } else if (subject.remarks === 'No Grades Submitted') {
                rowClass = 'table-secondary';
            } else if (subject.remarks === 'No Approved Grades') {
                rowClass = 'table-warning';
            } else {
                rowClass = 'table-success';
            }
            
            tableHtml += `
                <tr class="${rowClass}">
                    <td>${subject.subject}</td>
                    <td class="text-center">${subject.passed}</td>
                    <td class="text-center">${subject.failed}</td>
                    <td class="text-center">${subject.inc}</td>
                    <td class="text-center">${subject.dr}</td>
                    <td class="text-center">${subject.nc}</td>
                    <td>
                        <span class="badge ${getRemarksBadgeClass(subject.remarks)}">
                            ${subject.remarks}
                        </span>
                    </td>
                </tr>`;
        });
        
        // Close table
        tableHtml += `
                        </tbody>
                    </table>
                </div>
            </div>`;
            
        container.innerHTML = headerHtml + tableHtml;
    }
    
    // Helper function to get badge class based on remarks
    function getRemarksBadgeClass(remarks) {
        switch (remarks) {
            case 'No Intervention Needed':
                return 'intervention-badge-success';
            case 'No Need Intervention':
                return 'intervention-badge-success';
            case 'Pending':
                return 'intervention-badge-warning';
            case 'Need Intervention':
                return 'intervention-badge-danger';
            case 'No Submission Recorded':
                return 'intervention-badge-info';
            case 'No Grades Submitted':
                return 'intervention-badge-info';
            case 'No Approved Grades':
                return 'intervention-badge-warning';
            default:
                return 'intervention-badge-secondary';
        }
    }
});
</script>

<style>
/* Page Container */
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

/* Filter Card Styling */
.filter-card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
    margin-bottom: 1.5rem;
}

.filter-card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 15px 20px;
    border-radius: 8px 8px 0 0;
}

.filter-card-header h5 {
    margin: 0;
    font-weight: 500;
    color: #495057;
}

.filter-card-body {
    padding: 20px;
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
    border-color: #22bbea;
    box-shadow: 0 0 0 0.2rem rgba(34, 187, 234, 0.25);
    outline: none;
}

.filter-group select:disabled {
    background-color: #f8f9fa;
    color: #6c757d;
    cursor: not-allowed;
}

@media (max-width: 768px) {
    .filter-inline-container {
        flex-direction: column;
        gap: 15px;
    }

    .filter-group {
        min-width: 100%;
    }
}

.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: none;
}

.card-body {
    padding: 20px;
}

.table-container {
    max-width: 95%;
    margin: 0 auto;
    padding: 0 15px;
}

.table {
    border: 1px solid #dee2e6;
    width: 100%;
    font-size: 1rem;
    margin: 20px auto;
}

.table th, .table td {
    border: 1px solid #dee2e6;
    vertical-align: middle;
    padding: 12px 15px;
    text-align: center;
}

.table thead th {
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    text-transform: uppercase;
    font-size: 0.85rem;
    letter-spacing: 0.5px;
    background-color: #f8f9fa;
    text-align: center;
}

.table tbody tr:not(:last-child) {
    border-bottom: 1px solid #dee2e6;
}

.table tbody td {
    font-size: 0.95rem;
}

.badge {
    padding: 0.5em 0.8em;
    font-size: 0.8em;
    font-weight: 600;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    min-width: 120px;
    display: inline-block;
    text-align: center;
}

.bg-success {
    background-color: #28a745 !important;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

.bg-danger {
    background-color: #dc3545 !important;
}

.bg-primary {
    background-color: #007bff !important;
}

.bg-secondary {
    background-color: #6c757d !important;
}

.table > :not(caption) > * > * {
    padding: 1rem 1rem;
}

.card-header {
    padding: 1rem 1.25rem;
    margin-bottom: 0;
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
}

.card-footer {
    padding: 0.75rem 1.25rem;
    background-color: #f8f9fa;
    border-top: 1px solid rgba(0, 0, 0, 0.125);
}

.instruction-text {
    font-size: 1.1rem;
    color: #495057;
    font-weight: 500;
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
    line-height: 1.6;
}

.text-muted .instruction-text {
    color: #6c757d;
}

.bi-graph-up {
    color: #6c757d;
    opacity: 0.7;
    transition: all 0.3s ease;
}

.text-center:hover .bi-graph-up {
    transform: scale(1.1);
    opacity: 0.9;
}

/* Custom Intervention Badge Styles */
.intervention-badge-success {
    background-color: #28a745 !important;
    color: #ffffff !important;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
    text-align: center;
    min-width: 120px;
    border: none;
}

.intervention-badge-danger {
    background-color: #dc3545 !important;
    color: #ffffff !important;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
    text-align: center;
    min-width: 120px;
    border: none;
}

.intervention-badge-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
    text-align: center;
    min-width: 120px;
    border: none;
}

.intervention-badge-info {
    background-color: #17a2b8 !important;
    color: #ffffff !important;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
    text-align: center;
    min-width: 120px;
    border: none;
}

.intervention-badge-secondary {
    background-color: #6c757d !important;
    color: #ffffff !important;
    padding: 6px 12px;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.85rem;
    display: inline-block;
    text-align: center;
    min-width: 120px;
    border: none;
}

/* Mobile responsive styles for intervention badges */
@media (max-width: 768px) {
    .intervention-badge-success,
    .intervention-badge-danger,
    .intervention-badge-warning,
    .intervention-badge-info,
    .intervention-badge-secondary {
        font-size: 0.75rem;
        padding: 4px 8px;
        min-width: 100px;
    }
}
</style>
@endsection