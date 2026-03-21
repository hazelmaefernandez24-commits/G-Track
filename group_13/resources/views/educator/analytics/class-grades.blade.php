@extends('layouts.educator_layout')

@section('content')
<!-- Add Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Add Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<!-- Add jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<div class="page-container">
    <div class="header-section">
        <h1 style="font-weight: 300">📊Class Grades</h1>
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

<style>
.page-container {
    padding: 20px;
    max-width: 100%; /* Ensure it takes full available width */
}

.header-section {
    margin-bottom: 2rem;
}

.header-section h1 {
    margin-bottom: 0.5rem;
    color: #333;
}

.header-section hr {
    margin: 1rem 0;
    border-color: #e9ecef;
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
    border: none;
    border-radius: 10px;
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.card-body {
    padding: 1.5rem;
}

.table-responsive {
    overflow-x: auto;
    width: 100%; /* Ensure table responsive container takes full width */
}

.table {
    margin-bottom: 0;
    width: 100%; /* Ensure table takes full width of its container */
}

.table th {
    background-color: #22bbea !important;
    color: white !important;
    border-color: #22bbea !important;
    font-weight: 500;
    padding: 1rem;
    text-align: center;
}

.table td {
    padding: 1rem;
    text-align: center;
    vertical-align: middle;
}

.table-success {
    background-color: #d4edda !important;
}

.table-danger {
    background-color: #f8d7da !important;
}

.table-warning {
    background-color: #fff3cd !important;
}

.table-info {
    background-color: #d1ecf1 !important;
}

.grade-summary {
    background-color: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    padding: 1rem;
}

.grade-summary p {
    margin-bottom: 0.5rem;
    color: #495057;
}

.grade-summary strong {
    color: #212529;
}

.loading-spinner {
    width: 2rem;
    height: 2rem;
    border: 0.25rem solid #22bbea;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border 0.75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}

.instruction-text {
    color: #6c757d;
    font-size: 1rem;
}

.alert {
    border-radius: 5px;
    padding: 1rem;
    margin: 1rem;
}

.alert-danger {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

.alert i {
    margin-right: 0.5rem;
}
</style>

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
    fetch('/educator/analytics/schools')
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
                fetch(`/educator/analytics/classes/${schoolId}`)
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
                    fetch(`/educator/analytics/class-submissions/${schoolId}/${classId}`)
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
            
            fetch(`/educator/analytics/class-grades-data?school_id=${schoolId}&class_id=${classId}&submission_id=${submissionId}`)
                .then(async res => {
                    if (!res.ok) {
                        throw new Error(`HTTP error! status: ${res.status}`);
                    }
                    return res.json();
                })
                .then(data => {
                    console.log('Grade data:', data);
                    if (!data || !data.grades || data.grades.length === 0) {
                        container.innerHTML = `
                            <div class="text-center p-5">
                                <i class="bi bi-exclamation-circle" style="font-size: 3rem;"></i>
                                <p class="mt-3 mb-0 instruction-text">No grades found for the selected submission.</p>
                            </div>`;
                        return;
                    }

                    // Identify all unique subjects to create dynamic columns
                    const allSubjects = new Set();
                    data.grades.forEach(student => {
                        student.subjects.forEach(subject => {
                            allSubjects.add(subject.subject_name);
                        });
                    });
                    const sortedSubjects = Array.from(allSubjects).sort();

                    let tableHtml = `
                        <div class="table-responsive">
                            <div class="grade-summary card-body">
                                <p><strong>Submission:</strong> ${data.submission.term} ${data.submission.semester} (${data.submission.academic_year})</p>
                                <p><strong>School:</strong> ${data.school.name}</p>
                                <p><strong>Class:</strong> ${data.class_name}</p>
                                <p><strong>Passing Grade:</strong> ${data.school.passing_grade_min}% - ${data.school.passing_grade_max}%</p>
                            </div>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Student ID</th>
                                        <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Full Name</th>
                                        <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Gender</th>`;

                    sortedSubjects.forEach(subjectName => {
                        tableHtml += `<th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">${subjectName}</th>`;
                    });

                    tableHtml += `
                                        <!-- <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Average</th> -->
                                        <th class="text-center" style="background-color: #22BBEA !important; color: white; border-color: #22BBEA !important;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>`;

                    data.grades.forEach(student => {
                        tableHtml += `<tr>
                                      <td class="text-center">${student.student_id}</td>
                                        <td class="text-center">${student.student_name}</td>
                                        <td class="text-center">${student.gender}</td>`;

                        sortedSubjects.forEach(subjectName => {
                            const subject = student.subjects.find(s => s.subject_name === subjectName);
                            let gradeContent = '';
                            if (subject) {
                                let gradeColorClass = '';
                                if (subject.remarks === 'Passed') {
                                    gradeColorClass = 'text-success';
                                } else if (subject.remarks === 'Rejected') {
                                    gradeColorClass = 'text-danger fw-bold';
                                } else if (subject.remarks === 'Failed' || subject.remarks === 'Need Intervention') {
                                    gradeColorClass = 'text-danger';
                                } else if (subject.remarks === 'Incomplete Submission') {
                                    gradeColorClass = 'text-info';
                                } else if (subject.remarks === 'Pending' || subject.remarks === 'Not yet Approved') {
                                    gradeColorClass = 'text-warning';
                                }

                                gradeContent = `
                                    <span class="d-inline-flex align-items-center">
                                        ${subject.grade}
                                        <i class="bi bi-circle-fill ms-1 ${gradeColorClass}" style="font-size: 0.5em;"></i>
                                    </span>`;
                            } else {
                                gradeContent = `N/A`;
                            }
                            tableHtml += `<td class="text-center">${gradeContent}</td>`;
                        });

                        let studentRemarksClass = '';
                        if (student.overall_status === 'Passed') {
                            studentRemarksClass = 'table-success';
                        } else if (student.overall_status === 'Rejected') {
                            studentRemarksClass = 'table-danger fw-bold';
                        } else if (student.overall_status === 'Failed' || student.overall_status === 'Need Intervention') {
                            studentRemarksClass = 'table-danger';
                        } else if (student.overall_status === 'Conditional') {
                            studentRemarksClass = 'table-warning fw-bold'; // Orange and bold for conditional
                        } else if (student.overall_status === 'Pending' || student.overall_status === 'Not yet Approved') {
                            studentRemarksClass = 'table-warning';
                        } else if (student.overall_status === 'Incomplete Submission') {
                            studentRemarksClass = 'table-info';
                        }

                        tableHtml += `
                                        <!-- <td class="text-center">${student.average_grade.toFixed(2)}</td> -->
                                        <td class="text-center ${studentRemarksClass}">${student.overall_status}</td>
                                    </tr>`;
                    });

                    tableHtml += `
                                </tbody>
                            </table>
                        </div>`;

                    container.innerHTML = tableHtml;
                })
                .catch(error => {
                    console.error('Error loading grades:', error);
                    container.innerHTML = `
                        <div class="alert alert-danger m-3">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            Failed to load grade data. Please try again.
                        </div>`;
                });
    }
});
</script>

<!-- Add Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection 