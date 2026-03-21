@extends('layouts.nav')

@section('content')
<div class="page-container">
    <div class="header-section">
        <h1 style= "font-weight: 300">📊Class Grades</h1>
        <hr>
        <p class="text-muted">View and analyze class grades. Select a school, class, and submission to view the grade report.</p>
    </div>
    
    <div class="filter-card">
        <div class="filter-card-header">
            <h5>
                <i class="bi bi-funnel me-2"></i>
                Filter Class Grades
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
            <div id="gradesTableContainer" class="w-100" style="min-height: 300px; display: flex; flex-direction: column;">
                <div class="text-center text-muted w-100">
                    <div style="width: 100%; max-width: 600px; margin: 0 auto; padding: 2rem;">
                        <i class="bi bi-graph-up" style="font-size: 3rem; color: #6c757d; opacity: 0.7; display: block; margin: 0 auto 1rem;"></i>
                        <p class="instruction-text" style="margin: 0; padding: 0 1rem;">Select a school, class, and submission to view grade report</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
    console.log('Loading schools...');
    fetch('/training/analytics/schools')
        .then(res => {
            console.log('Schools response status:', res.status);
            return res.json();
        })
        .then(data => {
            console.log('Schools data:', data);
            const schoolSelect = document.getElementById('schoolSelect');
            data.forEach(school => {
                const opt = document.createElement('option');
                opt.value = school.id;
                opt.textContent = school.name;
                schoolSelect.appendChild(opt);
            });
        });

    document.getElementById('schoolSelect').addEventListener('change', function() {
        const schoolId = this.value;
        const classSelect = document.getElementById('classSelect');
        const submissionSelect = document.getElementById('submissionSelect');
        
        classSelect.innerHTML = '<option value="">Select Class</option>';
        submissionSelect.innerHTML = '<option value="">Select Submission</option>';
        classSelect.disabled = true;
        submissionSelect.disabled = true;
        
        const container = document.getElementById('gradesTableContainer');
        container.innerHTML = `
            <div class="d-flex justify-content-center p-5">
                <div class="loading-spinner"></div>
                <span class="ms-2">Loading classes...</span>
            </div>`;
        
        if (schoolId) {
                console.log(`Loading classes for school ${schoolId}...`);
                fetch(`/training/analytics/classes/${schoolId}`)
                    .then(res => {
                        console.log('Classes response status:', res.status);
                        return res.json();
                    })
                    .then(data => {
                        console.log('Classes data:', data);
                    if (data.length === 0) {
                        classSelect.innerHTML = '<option value="">No classes found</option>';
                        container.innerHTML = `
                            <div class="text-center p-5">
                                <i class="bi bi-collection" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0 instruction-text">No classes found for this school.</p>
                            </div>`;
                    } else {
                        data.forEach(cls => {
                            const opt = document.createElement('option');
                            opt.value = cls.id;
                            opt.textContent = cls.name;
                            classSelect.appendChild(opt);
                        });
                        container.innerHTML = `
                            <div class="text-center p-5 text-muted">
                                <i class="bi bi-graph-up" style="font-size: 2.5rem;"></i>
                                <p class="mt-3 mb-0 instruction-text">Select a class and submission to view grade report</p>
                            </div>`;
                    }
                    classSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading classes:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Failed to load classes. Please try again.
                        </div>`;
                });
        } else {
            container.innerHTML = `
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-graph-up" style="font-size: 2.5rem;"></i>
                    <p class="mt-3 mb-0 instruction-text">Select a school, class, and submission to view grade report</p>
                </div>`;
        }
    });

    document.getElementById('classSelect').addEventListener('change', function() {
        const schoolId = document.getElementById('schoolSelect').value;
        const classId = this.value;
        const submissionSelect = document.getElementById('submissionSelect');
        submissionSelect.innerHTML = '<option value="">Select Submission</option>';
        submissionSelect.disabled = true;
        if (schoolId && classId) {
                    console.log(`Loading submissions for school ${schoolId}, class ${classId}...`);
                    submissionSelect.innerHTML = '<option value="">Loading submissions...</option>';
                    fetch(`/training/analytics/class-submissions/${schoolId}/${classId}`)
                        .then(res => {
                            if (!res.ok) {
                                throw new Error(`HTTP error! status: ${res.status}`);
                            }
                            return res.json();
                        })
                        .then(data => {
                            console.log('Raw submissions data from server:', JSON.stringify(data, null, 2));
                            submissionSelect.innerHTML = '<option value="">Select Submission</option>';
                            
                            if (!data || data.length === 0) {
                                const opt = document.createElement('option');
                                opt.value = '';
                                opt.textContent = 'No submissions found';
                                submissionSelect.appendChild(opt);
                                return;
                            }
                            
                            // Log each submission's status
                            data.forEach((sub, index) => {
                                console.log(`Submission ${index + 1}:`, {
                                    id: sub.id,
                                    label: sub.label,
                                    status: sub.status,
                                    has_incomplete_grades: sub.has_incomplete_grades,
                                    rawObject: sub
                                });
                            });
                            
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
                                    
                                    // Extract just the Semester, Term, and Year from the label
                                    // Format is typically: "[Status] - Semester Term Academic Year"
                                    const labelParts = sub.label.split(' - ');
                                    // If we have a status prefix (e.g., "[Approved]"), remove it
                                    const cleanLabel = labelParts.length > 1 ? labelParts.slice(1).join(' - ') : sub.label;
                                    opt.textContent = cleanLabel;
                                    
                                    console.log('Processing submission:', {
                                        id: sub.id,
                                        status: sub.status,
                                        label: sub.label,
                                        statusType: typeof sub.status,
                                        statusLower: sub.status ? sub.status.toLowerCase() : 'undefined'
                                    });
                                    
                                    // Always enable the dropdown option
                                    opt.disabled = false;
                                    
                                    submissionSelect.appendChild(opt);
                                }
                            });
                            submissionSelect.disabled = false;
                });
        }
        document.getElementById('gradesTableContainer').innerHTML = '';
    });

    document.getElementById('submissionSelect').addEventListener('change', fetchGrades);

    function fetchGrades() {
        const schoolId = document.getElementById('schoolSelect').value;
        const classId = document.getElementById('classSelect').value;
        const submissionId = document.getElementById('submissionSelect').value;
        
        if (!schoolId || !classId || !submissionId) {
            return;
        }
            
            // Show loading state
            const container = document.getElementById('gradesTableContainer');
            container.innerHTML = `
                <div class="d-flex justify-content-center p-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span class="ms-2">Loading grade data...</span>
                </div>`;
            
            fetch(`/training/analytics/class-grades-data?school_id=${schoolId}&class_id=${classId}&submission_id=${submissionId}`)
                .then(async res => {
                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await res.text();
                    console.error('Non-JSON response:', text);
                    throw new Error('Server returned non-JSON response. Please try again.');
                }
                    
                    if (!res.ok) {
                    const errorData = await res.json();
                    throw new Error(errorData.message || `HTTP error! status: ${res.status}`);
                }
                
                return res.json();
            })
            .then(data => {
                if (!data) {
                    throw new Error('No data received from server');
                }
                
                if (data.error) {
                    throw new Error(data.error);
                }
                
                console.log('Grades data received:', data);
                    renderGradesTable(data);
                })
                .catch(error => {
                    console.error('Error fetching grades:', error);
                    container.innerHTML = `
                    <div class="alert alert-danger m-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            ${error.message || 'Error loading grade data. Please try again.'}
                        </div>`;
                });
    }

    function renderGradesTable(data) {
        const container = document.getElementById('gradesTableContainer');
        
        if (!data || !data.students || !data.students.length) {
            container.innerHTML = `
                <div class="text-center p-5">
                    <i class="bi bi-exclamation-circle" style="font-size: 3rem; color: #6c757d; opacity: 0.8;"></i>
                    <h5 class="mt-3">No Grade Data Available</h5>
                    <p class="text-muted">No approved grade data found for this submission.</p>
                    <p class="small text-muted mt-2">Please ensure at least one student has approved grades for this submission.</p>
                </div>`;
            return;
        }

        // Process data
        let students = data.students;
        let subjects = data.subjects || [];
        
        // Get school and class information
        const schoolName = data.school_name || (data.school ? data.school.name : 'N/A');
        const className = data.class_name || (data.class ? data.class.name : 'N/A');
        
        // Get submission data
        const submission = data.submission || {};
        const semester = submission.semester || data.semester || 'N/A';
        const term = submission.term || data.term || 'N/A';
        const academicYear = submission.academic_year || data.academic_year || 'N/A';
        
        // Pagination settings
        const itemsPerPage = 10;
        let currentPage = 1;
        const totalPages = Math.ceil(students.length / itemsPerPage);

        function renderTable(page) {
            const start = (page - 1) * itemsPerPage;
            const end = start + itemsPerPage;
            const pageStudents = students.slice(start, end);
            
            let tableHtml = `
                <div class="card mb-4 border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div style="width: 100%; text-align: center;">
                            <div style="display: inline-block; text-align: center; width: 100%;">
                                <h4 class="mb-3" style="font-size: 1.5rem; font-weight: 500; line-height: 1.3; color: #2c3e50;">
                                    ${schoolName || 'N/A'} - ${className || 'N/A'}
                                </h4>
                                <div class="text-muted" style="font-size: 1rem; letter-spacing: 0.3px; line-height: 1.5;">
                                    <span><strong>Semester:</strong> ${semester || 'N/A'}</span>
                                    <span class="mx-2">|</span>
                                    <span><strong>Term:</strong> ${term || 'N/A'}</span>
                                    <span class="mx-2">|</span>
                                    <span><strong>Academic Year:</strong> ${academicYear || 'N/A'}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive" style="width: 100%; max-width: 100%; overflow-x: auto;">
                <table class="table table-hover align-middle grades-table" style="width: 100%; min-width: 1200px;">
                    <thead>
                        <tr>
                                <th style="background-color: #22BBEA; color: white; border-color: #22BBEA; width: 120px;">Student ID</th>
                                <th style="background-color: #22BBEA; color: white; border-color: #22BBEA; width: 200px;">Full Name</th>`;
            
            // Add subject headers - only once
            subjects.forEach(subject => {
                tableHtml += `<th style="background-color: #22BBEA; color: white; border-color: #22BBEA; width: calc((100% - 520px) / ${subjects.length});">${subject}</th>`;
            });
            
            tableHtml += `
                                <!-- <th style="background-color: #22BBEA; color: white; border-color: #22BBEA; width: 100px;">Average</th> -->
                                <th style="background-color: #22BBEA; color: white; border-color: #22BBEA; width: 100px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>`;

            // Add student rows for current page
            pageStudents.forEach(student => {
                tableHtml += `
                <tr>
                    <td>${student.student_id || ''}</td>
                    <td>${student.full_name || ''}</td>`;

                // Add grades for each subject
                if (student.grades && student.grades.length > 0) {
            student.grades.forEach(gradeData => {
                const grade = typeof gradeData === 'object' ? gradeData.grade : gradeData;
                const status = typeof gradeData === 'object' ? (gradeData.status || 'pending') : 'approved';
                
                let displayGrade = grade || '-';
                let gradeClass = '';
                let statusBadge = '';
                
                // Add status badge based on status
                const statusLower = status.toLowerCase().trim();
                if (statusLower === 'pending' || statusLower === 'pending_approval' || statusLower === 'submitted') {
                    statusBadge = ' <span class="badge bg-warning text-dark" title="Pending Approval"><i class="bi bi-hourglass"></i></span>';
                } else if (statusLower === 'rejected') {
                    statusBadge = ' <span class="badge bg-danger" title="Rejected"><i class="bi bi-x-circle"></i></span>';
                    gradeClass = 'text-decoration-line-through text-muted'; // Strikethrough and muted for rejected
                } else if (statusLower === 'approved' || statusLower === 'approve') {
                    statusBadge = ' <span class="badge bg-success" title="Approved"><i class="bi bi-check-circle"></i></span>';
                }
                
                // Style based on grade value (only for approved grades)
                if (statusLower === 'approved') {
                    if (displayGrade === 'INC') {
                        gradeClass = 'grade-inc'; // Apply custom class for approved INC
                    } else if (displayGrade === 'NC' || displayGrade === 'DR') {
                        gradeClass = 'grade-dr-nc'; // Apply custom class for approved NC/DR
                    } else if (displayGrade !== '-' && !isNaN(displayGrade)) {
                        const numericGrade = parseFloat(displayGrade);
                        // Check if lower grades are better or higher grades are better
                        const isLowerBetter = parseFloat(data.school.passing_grade_min) < parseFloat(data.school.passing_grade_max);
                        let isFailing = false;
                        if(isLowerBetter) {
                             isFailing = numericGrade > parseFloat(data.school.passing_grade_max);
                        } else {
                             isFailing = numericGrade < parseFloat(data.school.passing_grade_min);
                        }

                        if (isFailing) {
                            gradeClass = 'text-danger fw-bold'; // Use Bootstrap for numeric failing
                        } else {
                             gradeClass = 'text-success fw-bold'; // Use Bootstrap for numeric passing
                        }
                    }
                } else if (statusLower === 'pending' || statusLower === 'pending_approval' || statusLower === 'submitted') {
                    // Style for pending grades
                     gradeClass = 'text-muted'; // Muted color for pending grades
                }
                // Rejected grades are handled above with strikethrough and muted color

                
                // Add the grade cell with status and appropriate class
                tableHtml += `<td class="${gradeClass}">
                    <div class="d-flex flex-column">
                        <span>${displayGrade}</span>
                        ${statusBadge}
                    </div>
                </td>`;
            });
                } else {
                    // If no grades, add empty cells for each subject
                    subjects.forEach(() => {
                        tableHtml += `<td>-</td>`;
                    });
                }

                // Add average and status
                let statusText = student.status || 'No Approved Grades';
                let statusClass = 'text-secondary'; // Default class
                
                // Determine status class based on the status text
                if (statusText.includes('Passed')) {
                    statusClass = 'text-success'; // Green
                } else if (statusText.includes('Rejected')) {
                    statusClass = 'text-danger fw-bold'; // Red and bold for rejected
                } else if (statusText.includes('Failed')) {
                    statusClass = 'text-danger'; // Red
                } else if (statusText.includes('Conditional')) {
                    statusClass = 'text-warning fw-bold'; // Orange and bold for conditional
                } else if (statusText.includes('Pending') || statusText.includes('Not yet Approved')) {
                    statusClass = 'text-warning'; // Orange
                } else if (statusText.includes('No Grades Submitted') || statusText.includes('No Calculable Average')) {
                     statusClass = 'text-muted'; // Muted color for informational statuses
                }
                
                // Format average with 2 decimal places if it's a number
                let averageDisplay = '-';
                if (student.average !== null && !isNaN(student.average)) {
                    averageDisplay = parseFloat(student.average).toFixed(2);
                } else if (student.average === 0) {
                    averageDisplay = '0.00';
                }
                
                // Add a note if there are pending grades
                let statusNote = '';
                if (student.grades && student.grades.some(g => {
                    const status = (g?.status || '').toLowerCase().trim();
                    return status === 'pending' || status === 'pending_approval' || status === 'submitted';
                })) {
                    statusNote = ' <span class="badge bg-warning text-dark">Pending Grades</span>';
                }
                
                tableHtml += `
                    <!-- <td class="text-center fw-bold">${averageDisplay}</td> -->
                    <td class="text-center fw-bold ${statusClass}">
                        ${statusText}
                        ${statusNote}
                    </td>
                </tr>`;
            });

            tableHtml += `
                    </tbody>
                </table>
            </div>`;
        
            // Add pagination controls below the table
            let paginationHtml = `
                <div class="d-flex flex-column align-items-center mt-3" style="width: 100%;">
                    <div class="text-muted mb-2">
                        Showing ${start + 1} to ${Math.min(end, students.length)} of ${students.length} students
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination mb-0">
                            <li class="page-item ${page === 1 ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page - 1}" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>`;

            // Add page numbers
            for (let i = 1; i <= totalPages; i++) {
                paginationHtml += `
                    <li class="page-item ${i === page ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
            }

            paginationHtml += `
                            <li class="page-item ${page === totalPages ? 'disabled' : ''}">
                                <a class="page-link" href="#" data-page="${page + 1}" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        </ul>
                    </nav>
                </div>`;

            container.innerHTML = `
                <div class="table-container-wrapper">${tableHtml}</div>
                <div class="pagination-container-wrapper">${paginationHtml}</div>
            `;

            // Add event listeners for pagination
            document.querySelectorAll('.page-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const newPage = parseInt(e.target.closest('.page-link').dataset.page);
                    if (newPage >= 1 && newPage <= totalPages) {
                        currentPage = newPage;
                        renderTable(currentPage);
                    }
                });
            });
        }

        // Initial render
        renderTable(currentPage);
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

.grades-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.9rem;
    table-layout: fixed;
}
.grades-table th, .grades-table td {
    border: 1px solid #e0e0e0;
    padding: 0.75rem;
    text-align: center;
    vertical-align: middle;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.grades-table th {
    background-color: #f8f9fa;
    font-weight: 600;
    color: #495057;
    text-transform: uppercase;
    font-size: 0.8rem;
    letter-spacing: 0.5px;
}
.grades-table tbody tr:hover {
    background-color: rgba(0, 0, 0, 0.01);
}
.grades-table td {
    color: #212529;
}
.status-passed {
    color: #198754 !important;
    font-weight: 600;
}
.status-failed {
    color: #dc3545 !important;
    font-weight: 600;
}
.status-pending {
    color: #ffc107 !important;
    font-weight: 600;
}

/* Custom styles for individual grades */
.grades-table td.grade-inc {
    color: #ffc107 !important; /* Orange */
    font-weight: bold;
}

.grades-table td.grade-dr-nc {
    color: #dc3545 !important; /* Red */
    font-weight: bold;
}

/* Ensure Bootstrap text colors have higher specificity if needed */
.grades-table td.text-success {
    color: #198754 !important;
}

.grades-table td.text-danger {
     color: #dc3545 !important;
}

.grades-table td.text-warning {
     color: #ffc107 !important;
}

.grades-table td.text-muted {
     color: #6c757d !important;
}

.loading-spinner {
    display: inline-block;
    width: 2rem;
    height: 2rem;
    border: 0.25rem solid rgba(13, 110, 253, 0.3);
    border-radius: 50%;
    border-top-color: #0d6efd;
    animation: spin 1s ease-in-out infinite;
}
@keyframes spin {
    to { transform: rotate(360deg); }
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

.bi-graph-up, .bi-collection {
    color: #6c757d;
    opacity: 0.7;
    transition: all 0.3s ease;
    margin: 0 auto;
    display: block;
}

.text-center:hover .bi-graph-up,
.text-center:hover .bi-collection {
    transform: scale(1.1);
    opacity: 0.9;
}

.pagination {
    margin-bottom: 0;
    justify-content: center;
}
.pagination .page-item {
    display: inline-block; /* Make list items display horizontally */
}
.pagination .page-link {
    color: #22BBEA;
    border-color: #dee2e6;
    padding: 0.5rem 0.75rem;
}
.pagination .page-item.active .page-link {
    background-color: #22BBEA;
    border-color: #22BBEA;
    color: white;
}
.pagination .page-link:hover {
    color: #1a9bc7;
    background-color: #e9ecef;
    border-color: #dee2e6;
}
.pagination .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: #fff;
    border-color: #dee2e6;
}
#gradesTableContainer {
    min-height: 300px;
    display: flex;
    flex-direction: column;
    align-items: center; /* Keep align-items: center to center the block children */
}

#gradesTableContainer > div {
    width: 100%; /* Ensure direct children take full width */
}

.table-container-wrapper,
.pagination-container-wrapper {
    width: 100%;
    display: block; /* Ensure these are block elements */
}

.table-responsive {
    display: block; /* Explicitly make table-responsive a block element */
    width: 100%;
}
</style>
@endsection
