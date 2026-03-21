@extends('layouts.educator_layout')

@section('content')
<div class="page-container">
    <div class="header-section">
        <h1 style="font-weight: 300">📊 Subject Progress Analytics</h1>
        <hr>
        <p class="text-muted">View and analyze student progress across subjects. Select a school, class, and submission to view the report.</p>
    </div>

    <div class="filter-card">
        <div class="filter-card-header">
            <h5>
                <i class="bi bi-funnel me-2"></i>
                Filter Subject Progress
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
        <div id="progressChartContainer">
            <div class="text-center p-5 text-muted">
                <i class="bi bi-bar-chart-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                <p class="mt-3 mb-0">Select a school, class, and submission to view subject progress</p>
            </div>
        </div>
    </div>
</div>

<br>
<br>
<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// Prevent form submission on Enter key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        return false;
    }
});

document.addEventListener('DOMContentLoaded', function() {
    // Chart instance
    let progressChart = null;
    
    // Load schools
    fetch('/educator/analytics/schools')
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
        
        const container = document.getElementById('progressChartContainer');
        container.innerHTML = `
            <div class="d-flex justify-content-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading classes...</span>
            </div>`;
        
        if (!schoolId) {
            container.innerHTML = `
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-bar-chart-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0">Select a school, class, and submission to view subject progress</p>
                </div>`;
            return;
        }
        
        // Load classes for the selected school
        fetch(`/educator/analytics/classes/${schoolId}`)
            .then(res => res.json())
            .then(classes => {
                classSelect.innerHTML = '<option value="">Select Class</option>';
                classes.forEach(cls => {
                    const opt = document.createElement('option');
                    opt.value = cls.id;
                    opt.textContent = cls.name;
                    classSelect.appendChild(opt);
                });
                classSelect.disabled = false;
                
                container.innerHTML = `
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-bar-chart-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        <p class="mt-3 mb-0">Select a class and submission to view subject progress</p>
                    </div>`;
            });
    });
    
    // Class change handler
    document.getElementById('classSelect').addEventListener('change', function() {
        const schoolId = document.getElementById('schoolSelect').value;
        const classId = this.value;
        const submissionSelect = document.getElementById('submissionSelect');
        
        submissionSelect.innerHTML = '<option value="">Select Submission</option>';
        submissionSelect.disabled = true;
        
        const container = document.getElementById('progressChartContainer');
        container.innerHTML = `
            <div class="d-flex justify-content-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading submissions...</span>
            </div>`;
        
        if (!classId) {
            container.innerHTML = `
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-bar-chart-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0">Select a class and submission to view subject progress</p>
                </div>`;
            return;
        }
        
        // Load submissions for the selected school and class
        fetch(`/educator/analytics/class-submissions/${schoolId}/${classId}`)
            .then(res => res.json())
            .then(submissions => {
                submissionSelect.innerHTML = '<option value="">Select Submission</option>';
                
                if (!submissions || submissions.length === 0) {
                    submissionSelect.innerHTML = '<option value="">No submissions found</option>';
                    container.innerHTML = `
                        <div class="text-center p-5 text-muted">
                            <i class="bi bi-bar-chart-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                            <p class="mt-3 mb-0">No submissions found for this class</p>
                        </div>`;
                    return;
                }

                submissions.forEach(submission => {
                    const opt = document.createElement('option');
                    opt.value = submission.id;
                    opt.textContent = submission.label;
                    submissionSelect.appendChild(opt);
                });
                submissionSelect.disabled = false;
                
                container.innerHTML = `
                    <div class="text-center p-5 text-muted">
                        <i class="bi bi-bar-chart-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                        <p class="mt-3 mb-0">Select a submission to view subject progress</p>
                    </div>`;
            })
            .catch(error => {
                console.error('Error loading submissions:', error);
                submissionSelect.innerHTML = '<option value="">Error loading submissions</option>';
                container.innerHTML = `
                    <div class="text-center p-5 text-danger">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2.5rem;"></i>
                        <p class="mt-3 mb-0">Error loading submissions. Please try again.</p>
                    </div>`;
            });
    });
    
    // Submission change handler
    document.getElementById('submissionSelect').addEventListener('change', function() {
        const schoolId = document.getElementById('schoolSelect').value;
        const classId = document.getElementById('classSelect').value;
        const submissionId = this.value;
        
        const container = document.getElementById('progressChartContainer');
        
        if (!submissionId) {
            container.innerHTML = `
                <div class="text-center p-5 text-muted">
                    <i class="bi bi-bar-chart-line" style="font-size: 2.5rem; opacity: 0.5;"></i>
                    <p class="mt-3 mb-0">Select a submission to view subject progress</p>
                </div>`;
            return;
        }
        
        container.innerHTML = `
            <div class="d-flex justify-content-center p-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <span class="ms-2">Loading data...</span>
            </div>`;
        
        // Fetch subject progress data
        fetch(`/educator/analytics/subject-progress-data?school_id=${schoolId}&class_id=${classId}&submission_id=${submissionId}`)
            .then(res => res.json())
            .then(data => {
                if (data.error) {
                    container.innerHTML = `
                        <div class="text-center p-5 text-danger">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2.5rem;"></i>
                            <p class="mt-3 mb-0">${data.error}</p>
                        </div>`;
                    return;
                }
                
                // Process and display the data
                // Check if there's any actual grade data to display (not just subjects)
                const hasData = data.subjects && data.subjects.some(subject =>
                    subject.passed > 0 || subject.failed > 0 || subject.inc > 0 ||
                    subject.dr > 0 || subject.nc > 0
                );

                // Create header with school, class, and submission info
                const headerHtml = `
                    <div class="card">
                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h4 style="color: #22bbea; margin-bottom: 0.5rem;">${data.school.name}</h4>
                                <h5 style="color: #495057; margin-bottom: 0.5rem;">${data.class_name}</h5>
                                <div class="mt-2" style="font-size: 1rem; color: #6c757d; white-space: nowrap; text-align: center; margin-bottom: 1rem;">
                                    ${data.submission.semester ? `Semester: ${data.submission.semester}` : ''}
                                    ${data.submission.term ? ` | Term: ${data.submission.term}` : ''}
                                    ${data.submission.academic_year ? ` | Academic Year: ${data.submission.academic_year}` : ''}
                                </div>
                                ${!hasData ? `
                                <div class="text-center py-2">
                                    <i class="bi bi-exclamation-circle" style="font-size: 2.5rem; color: #6c757d; margin-bottom: 0.5rem;"></i>
                                    <h5 class="mb-1" style="color: #6c757d;">No Approved Grades Available</h5>
                                    <p class="text-muted mb-0">There are no approved grades for the selected submission yet.</p>
                                </div>
                                ` : ''}
                            </div>
                        </div>
                    </div>`;

                // Set the container HTML with header
                container.innerHTML = headerHtml;

                // Only proceed to create chart if there's actual grade data
                if (!hasData) {
                    return;
                }
                
                // Add chart container to existing content
                const chartContainer = document.createElement('div');
                chartContainer.innerHTML = `
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="chart-container" style="position: relative; height: 500px; width: 100%;">
                                <canvas id="subjectProgressChart"></canvas>
                            </div>
                        </div>
                    </div>`;
                container.appendChild(chartContainer);
                
                // Prepare data for chart
                const subjects = data.subjects.map(s => s.subject || 'Unknown Subject');
                const passedData = data.subjects.map(s => parseInt(s.passed) || 0);
                const failedData = data.subjects.map(s => parseInt(s.failed) || 0);
                const incData = data.subjects.map(s => parseInt(s.inc) || 0);
                const drData = data.subjects.map(s => parseInt(s.dr) || 0);
                const ncData = data.subjects.map(s => parseInt(s.nc) || 0);
                
                // Destroy previous chart if it exists
                if (progressChart) {
                    progressChart.destroy();
                }
                
                // Create new chart
                const ctx = document.getElementById('subjectProgressChart').getContext('2d');
                progressChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: subjects,
                        datasets: [
                            {
                                label: 'Passed',
                                data: passedData,
                                backgroundColor: 'rgba(40, 167, 69, 0.7)',
                                borderColor: 'rgba(40, 167, 69, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'Failed',
                                data: failedData,
                                backgroundColor: 'rgba(220, 53, 69, 0.7)',
                                borderColor: 'rgba(220, 53, 69, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'INC',
                                data: incData,
                                backgroundColor: 'rgba(255, 193, 7, 0.7)',
                                borderColor: 'rgba(255, 193, 7, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'DR',
                                data: drData,
                                backgroundColor: 'rgba(23, 162, 184, 0.7)',
                                borderColor: 'rgba(23, 162, 184, 1)',
                                borderWidth: 1
                            },
                            {
                                label: 'NC',
                                data: ncData,
                                backgroundColor: 'rgba(108, 117, 125, 0.7)',
                                borderColor: 'rgba(108, 117, 125, 1)',
                                borderWidth: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            x: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Subjects'
                                }
                            },
                            y: {
                                stacked: true,
                                title: {
                                    display: true,
                                    text: 'Number of Students'
                                },
                                beginAtZero: true,
                                max: 70,
                                ticks: {
                                    stepSize: 5
                                }
                            }
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: 'Subject Progress Distribution',
                                font: {
                                    size: 16
                                }
                            },
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        const label = context.dataset.label || '';
                                        const value = context.raw || 0;
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                        return `${label}: ${value} (${percentage}%)`;
                                    }
                                }
                            }
                        }
                    }
                });
            })
            .catch(error => {
                console.error('Error loading data:', error);
                container.innerHTML = `
                    <div class="text-center p-5 text-danger">
                        <i class="bi bi-exclamation-triangle" style="font-size: 2.5rem;"></i>
                        <p class="mt-3 mb-0">Error loading data. Please try again.</p>
                    </div>`;
            });
    });
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
</style>
@endsection