@extends('layouts.educator_layout')

@section('content')

<div class="page-container">
    <div class="header-section">
        <h1 style= "font-weight: 300">📊 Class Progress</h1>
        <hr>
        <p class="text-muted">View the progress distribution of students in a class for a specific submission.</p>
    </div>

    <div class="filter-card">
        <div class="filter-card-header">
            <h5>
                <i class="bi bi-funnel me-2"></i>
                Filter Class Progress
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

    <!-- Header information section moved above the graph -->
    <div id="headerInfo" class="text-center mb-4" style="display: none;">
        <h4 class="mb-2" id="schoolClassInfo"></h4>
        <p class="text-muted mb-0" id="submissionInfo"></p>
    </div>

    <!-- Progress Display -->
    <div class="card shadow-sm">
        <div class="card-body">
            <div id="progressChartContainer" class="w-100" style="min-height: 600px; display: flex; justify-content: center; align-items: center; flex-direction: column;">
                <div class="text-center text-muted w-100">
                    <div style="width: 100%; max-width: 800px; margin: 0 auto; padding: 2rem;">
                        <i class="bi bi-pie-chart" style="font-size: 3rem; color: #6c757d; opacity: 0.7; display: block; margin: 0 auto 1rem;"></i>
                        <p class="instruction-text" style="margin: 0; padding: 0 1rem;">Select a school, class, and submission to view class progress chart</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const schoolSelect = document.getElementById('schoolSelect');
    const classSelect = document.getElementById('classSelect');
    const submissionSelect = document.getElementById('submissionSelect');
    const chartContainer = document.getElementById('progressChartContainer');

    let myChart = null; // To hold the Chart.js instance
    let selectedSchoolName = ''; // Store the selected school name

    // Function to initialize the chart area with placeholder/loading state
    function resetChartArea(message = 'Select a school, class, and submission to view class progress chart', showSpinner = false) {
        if (myChart) {
            myChart.destroy(); // Destroy existing chart instance
            myChart = null;
        }
        chartContainer.innerHTML = `
            <div class="text-center text-muted w-100 p-5">
                ${showSpinner ? '<div class="spinner-border text-primary mb-3" role="status"><span class="visually-hidden">Loading...</span></div>' : '<i class="bi bi-pie-chart" style="font-size: 3rem; color: #6c757d; opacity: 0.7; display: block; margin: 0 auto 1rem;"></i>'}
                <p class="instruction-text" style="margin: 0; padding: 0 1rem;">${message}</p>
            </div>
        `;
        chartContainer.style.minHeight = '400px';
        chartContainer.style.display = 'flex';
        chartContainer.style.flexDirection = 'column';
        chartContainer.style.justifyContent = 'center';
        chartContainer.style.alignItems = 'center';
    }

    // Initial reset
    resetChartArea();

    // Fetch schools
    fetch('/educator/analytics/schools')
        .then(res => res.json())
        .then(schools => {
            schools.forEach(school => {
                const opt = document.createElement('option');
                opt.value = school.id;
                opt.textContent = school.name;
                schoolSelect.appendChild(opt);
            });
        });

    // Handle school selection change
    schoolSelect.addEventListener('change', function() {
        const schoolId = this.value;
        selectedSchoolName = this.options[this.selectedIndex].text; // Store the selected school name
        classSelect.innerHTML = '<option value="">Select Class</option>';
        submissionSelect.innerHTML = '<option value="">Select Submission</option>';
        classSelect.disabled = true;
        submissionSelect.disabled = true;
        resetChartArea('Loading classes...', true);
        document.getElementById('headerInfo').style.display = 'none';

        if (schoolId) {
            fetch(`/educator/analytics/classes/${schoolId}`)
                .then(res => res.json())
                .then(classes => {
                    if (classes.length === 0) {
                        classSelect.innerHTML = '<option value="">No classes found</option>';
                        resetChartArea('No classes found for this school.');
                    } else {
                        classes.forEach(cls => {
                            const opt = document.createElement('option');
                            opt.value = cls.id;
                            opt.textContent = cls.name;
                            classSelect.appendChild(opt);
                        });
                        resetChartArea('Select a class and submission to view class progress chart');
                    }
                    classSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading classes:', error);
                    resetChartArea('Failed to load classes. Please try again.');
                });
        } else {
            resetChartArea();
        }
    });

    // Handle class selection change
    classSelect.addEventListener('change', function() {
        const schoolId = schoolSelect.value;
        const classId = this.value;
        submissionSelect.innerHTML = '<option value="">Select Submission</option>';
        submissionSelect.disabled = true;
        resetChartArea('Loading submissions...', true);

        if (schoolId && classId) {
            fetch(`/educator/analytics/class-submissions/${schoolId}/${classId}`)
                .then(res => res.json())
                .then(submissions => {
                    if (!submissions || submissions.length === 0) {
                        submissionSelect.innerHTML = '<option value="">No submissions found</option>';
                         resetChartArea('No submissions found for this class.');
                    } else {
                         // Sort submissions: approved first, then by created_at desc
                            const sortedData = [...submissions].sort((a, b) => {
                                if (a.status === 'approved' && b.status !== 'approved') return -1;
                                if (a.status !== 'approved' && b.status === 'approved') return 1;
                                return 0;
                            });

                        sortedData.forEach(sub => {
                             if (sub && sub.id && sub.label) {
                                    const opt = document.createElement('option');
                                    opt.value = sub.id;
                                    // Extract just the Semester, Term, and Year from the label
                                    const labelParts = sub.label.split(' | ');
                                    opt.textContent = labelParts.join(' | '); // Use the formatted label from backend

                                    opt.disabled = false; // Always enable for selection
                                    submissionSelect.appendChild(opt);
                                }
                        });
                         resetChartArea('Select a submission to view class progress chart');
                    }
                    submissionSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading submissions:', error);
                    resetChartArea('Failed to load submissions. Please try again.');
                });
        } else {
             resetChartArea('Select a class and submission to view class progress chart');
        }
    });

    // Handle submission selection change and fetch data
    submissionSelect.addEventListener('change', function() {
        const schoolId = schoolSelect.value;
        const classId = classSelect.value;
        const submissionId = this.value;

        if (schoolId && classId && submissionId) {
            fetchProgressData(schoolId, classId, submissionId);
        } else {
            resetChartArea('Select a submission to view class progress chart');
        }
    });

    // Function to update header information
    function updateHeaderInfo(data) {
        const headerInfo = document.getElementById('headerInfo');
        const schoolClassInfo = document.getElementById('schoolClassInfo');
        const submissionInfo = document.getElementById('submissionInfo');

        if (data && data.class_name) {
            // Use school name from data if available, otherwise fall back to selectedSchoolName
            const schoolName = (data.school && data.school.name) ? data.school.name : selectedSchoolName;
            schoolClassInfo.textContent = `${schoolName} - ${data.class_name}`;

            // Handle both submission_details and submission structures
            const submission = data.submission_details || data.submission;
            if (submission) {
                submissionInfo.textContent = `Semester: ${submission.semester} | Term: ${submission.term} | Academic Year: ${submission.academic_year}`;
            }
            headerInfo.style.display = 'block';
        } else {
            headerInfo.style.display = 'none';
        }
    }

    // Modify the fetchProgressData function to update header info
    function fetchProgressData(schoolId, classId, submissionId) {
        resetChartArea('Loading class progress data...', true);

        fetch(`/educator/analytics/class-progress-data?school_id=${schoolId}&class_id=${classId}&submission_id=${submissionId}`)
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
                if (data.error) {
                    // Check if we have submission info even with error
                    if (data.submission && data.school && data.class_name) {
                        updateHeaderInfo(data);
                        resetChartArea(`Error: ${data.error}`);
                    } else {
                        resetChartArea(`Error: ${data.error}`);
                        updateHeaderInfo(null);
                    }
                } else if (data.submission_status === 'not_found') {
                    resetChartArea('Submission not found.');
                    updateHeaderInfo(null);
                } else if (data.total_students === 0) {
                    // Check if we have submission info even with no students
                    if (data.submission && data.school && data.class_name) {
                        updateHeaderInfo(data);
                        resetChartArea('No students found for this class and submission.');
                    } else {
                        resetChartArea('No students found for this class and submission.');
                        updateHeaderInfo(null);
                    }
                } else {
                    updateHeaderInfo(data);
                    renderProgressChart(data);
                }
            })
            .catch(error => {
                console.error('Error fetching progress data:', error);
                resetChartArea(`Error fetching data: ${error.message}`);
                updateHeaderInfo(null);
            });
    }

    // Function to render the pie chart
    function renderProgressChart(data) {
        chartContainer.innerHTML = ''; // Clear previous content
        chartContainer.style.minHeight = '600px'; // Increased height
        chartContainer.style.display = 'block';

        const canvas = document.createElement('canvas');
        canvas.style.width = '100%';
        canvas.style.height = '500px'; // Set explicit height for canvas
        chartContainer.appendChild(canvas);

        const ctx = canvas.getContext('2d');

        // Define colors for the chart slices
        const backgroundColors = ['#198754', '#dc3545', '#fd7e14', '#ffc107', '#6c757d']; // Green, Red, Orange (Conditional), Yellow (Pending), Muted
        const borderColors = ['#ffffff', '#ffffff', '#ffffff', '#ffffff', '#ffffff'];

        myChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: data.labels.map((label, index) => {
                    const count = data.counts[label];
                    const percentage = data.data[index];
                    return `${label}: ${count} (${percentage}%)`;
                }),
                datasets: [{
                    data: data.data,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: {
                                size: 14 // Increased font size for legend
                            }
                        }
                    },
                    title: {
                        display: true,
                        text: 'Class Progress',
                        font: {
                            size: 16 // Increased font size for title
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label;
                            }
                        }
                    }
                },
                layout: {
                    padding: 30 // Increased padding around the chart
                }
            }
        });
    }

    // Basic styles for instruction text and spinner (can be moved to CSS file)
    const style = document.createElement('style');
    style.innerHTML = `
        .instruction-text {
            font-size: 1.1rem;
            color: #495057;
            font-weight: 500;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            line-height: 1.6;
        }
        .bi-pie-chart {
             color: #6c757d;
             opacity: 0.7;
             transition: all 0.3s ease;
             margin: 0 auto;
             display: block;
        }
        .text-center:hover .bi-pie-chart {
            transform: scale(1.1);
            opacity: 0.9;
        }
         .spinner-border {
            display: inline-block;
            width: 2rem;
            height: 2rem;
            vertical-align: -0.125em;
            border: 0.25em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            -webkit-animation: .75s linear infinite spinner-border;
            animation: .75s linear infinite spinner-border;
        }
        @keyframes spinner-border {
            to { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);

});
</script>

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

.header-section {
    margin-bottom: 30px;
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

.card-header {
    background: #22bbea;
    color: white;
    padding: 16px 24px;
    border-radius: 8px 8px 0 0;
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 0.5px;
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

/* Chart and Summary Styling */
.chart-container {
    position: relative;
    height: 400px;
    padding: 20px;
}

.summary-stats {
    padding: 20px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    font-weight: 500;
    color: #495057;
}

.stat-value {
    font-weight: 600;
    font-size: 1.1rem;
}

@media (max-width: 768px) {
    .chart-container {
        height: 300px;
        padding: 10px;
    }

    .summary-stats {
        padding: 15px;
        margin-top: 20px;
    }
}
</style>
@endsection
