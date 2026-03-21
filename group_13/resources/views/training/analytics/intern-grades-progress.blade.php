@extends('layouts.nav')

@section('content')
<div class="content-wrapper">
    <div class="analytics-container">
    <div class="dashboard-header">
        <h1>Internship Grades Analytics</h1>
        <div class="filters">
            <div class="filter-group">
                <label for="classFilter">Filter by Class:</label>
                <select id="classFilter" class="form-control styled-select">
                    <option value="">All Classes</option>
                    @foreach($allClasses as $class)
                        <option value="{{ $class->class_id }}">{{ $class->class_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="filter-group">
                <label for="companyFilter">Filter by Company:</label>
                <select id="companyFilter" class="form-control styled-select">
                    <option value="">All Companies</option>
                    @php
                        $allCompanies = collect($allClassCompanies)->flatten()->unique()->sort();
                    @endphp
                    @foreach($allCompanies as $company)
                        <option value="{{ $company }}">{{ $company }}</option>
                    @endforeach
                </select>
            </div>

        </div>
    </div>
    <hr>

    <!-- Class Pagination -->
    @if($classPagination->has_pages)
        <div class="class-pagination-container">
            <div class="class-pagination-info">
                <small class="text-muted">
                    Showing class {{ $classPagination->from }} to {{ $classPagination->to }} of {{ $classPagination->total }} classes
                </small>
            </div>
            <div class="class-pagination-links">
                @if($classPagination->on_first_page)
                    <span class="pagination-btn disabled">
                        <i class="fas fa-chevron-left"></i> Previous Class
                    </span>
                @else
                    @php
                        $prevPage = $classPagination->current_page - 1;
                        $currentUrl = request()->fullUrlWithQuery(['class_page' => $prevPage]);
                    @endphp
                    <a href="{{ $currentUrl }}" class="pagination-btn">
                        <i class="fas fa-chevron-left"></i> Previous Class
                    </a>
                @endif

                <span class="page-info">
                    Class {{ $classPagination->current_page }} of {{ $classPagination->last_page }}
                </span>

                @if($classPagination->has_more_pages)
                    @php
                        $nextPage = $classPagination->current_page + 1;
                        $currentUrl = request()->fullUrlWithQuery(['class_page' => $nextPage]);
                    @endphp
                    <a href="{{ $currentUrl }}" class="pagination-btn">
                        Next Class <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="pagination-btn disabled">
                        Next Class <i class="fas fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
    @endif

    @foreach($paginatedClasses as $class)
    <div class="chart-section" id="chart-section-{{ $class->class_id }}">
        <div class="chart-header">
            <h3>{{ $class->class_name }}</h3>
        </div>

        <!-- Charts for current submission only -->
        <div class="submission-charts" id="submission-charts-{{ $class->class_id }}">
            @foreach($currentSubmissions as $submissionNumber)
            <div class="submission-chart" id="submission-{{ $submissionNumber }}-{{ $class->class_id }}">
                <h4>
                    {{ $submissionNumber }} Submission
                    <span class="submission-company" id="company-label-{{ $submissionNumber }}-{{ $class->class_id }}">(All Companies)</span>
                </h4>
                <div class="chart-container">
                    <canvas id="chart-{{ $submissionNumber }}-{{ $class->class_id }}"></canvas>
                </div>
                <div class="no-data-message" id="no-data-{{ $submissionNumber }}-{{ $class->class_id }}" style="display: none;">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3>No Data Available</h3>
                    <p>There is no internship grades data available for this {{ $submissionNumber }} submission with the current class and company filters.</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach

    <!-- Submission Pagination -->
    @if($submissionPagination->has_pages)
        <div class="submission-pagination-container">
            <div class="submission-pagination-info">
                <small class="text-muted">
                    Showing submission {{ $submissionPagination->from }} to {{ $submissionPagination->to }} of {{ $submissionPagination->total }} submissions
                </small>
            </div>
            <div class="submission-pagination-links">
                @if($submissionPagination->on_first_page)
                    <span class="pagination-btn disabled">
                        <i class="fas fa-chevron-left"></i> Previous Submission
                    </span>
                @else
                    @php
                        $prevPage = $submissionPagination->current_page - 1;
                        $currentUrl = request()->fullUrlWithQuery(['submission_page' => $prevPage]);
                    @endphp
                    <a href="{{ $currentUrl }}" class="pagination-btn">
                        <i class="fas fa-chevron-left"></i> Previous Submission
                    </a>
                @endif

                <span class="page-info">
                    Submission {{ $submissionPagination->current_page }} of {{ $submissionPagination->last_page }}
                </span>

                @if($submissionPagination->has_more_pages)
                    @php
                        $nextPage = $submissionPagination->current_page + 1;
                        $currentUrl = request()->fullUrlWithQuery(['submission_page' => $nextPage]);
                    @endphp
                    <a href="{{ $currentUrl }}" class="pagination-btn">
                        Next Submission <i class="fas fa-chevron-right"></i>
                    </a>
                @else
                    <span class="pagination-btn disabled">
                        Next Submission <i class="fas fa-chevron-right"></i>
                    </span>
                @endif
            </div>
        </div>
    @endif


    </div>
</div>

<style>
.content-wrapper {
    margin-top: 70px;
    margin-left: 270px; /* Account for sidebar width + extra space */
    padding: 20px;
    min-height: 100vh;
}

.analytics-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.dashboard-header {
    margin-bottom: 20px;
    margin-top: 20px;
}

.dashboard-header h1 {
    color: #2c3e50;
    font-size: 1.8rem;
    margin-bottom: 20px;
}

.filters {
    display: flex;
    gap: 20px;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
}

.filter-group label {
    font-weight: 500;
    color: #555;
    white-space: nowrap;
}

.chart-section {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    width: 100%;
    margin-bottom: 30px;
    box-sizing: border-box;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.chart-header h3 {
    color: #2c3e50;
    font-size: 1.4rem;
    font-weight: 600;
    margin: 0;
}

.submission-charts {
    display: grid;
    gap: 20px;
    margin-top: 20px;
    width: 100%;
    box-sizing: border-box;
}

.submission-chart {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    width: 100%;
    box-sizing: border-box;
}

.submission-chart h4 {
    color: #495057;
    font-size: 1.1rem;
    margin-bottom: 15px;
    text-align: center;
}

.submission-company {
    color: #6c757d;
    font-size: 0.85em;
    font-weight: 400;
    margin-left: 8px;
}

.chart-container {
    position: relative;
    height: 300px;
    width: 100%;
    box-sizing: border-box;
}

.styled-select {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    background-color: white;
    min-width: 200px;
    max-width: 100%;
}

.styled-select:disabled {
    background-color: #f3f4f6;
    cursor: not-allowed;
}

.no-data-message {
    display: none;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    text-align: center;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin-top: 20px;
}

.no-data-message svg {
    width: 48px;
    height: 48px;
    color: #6c757d;
    margin-bottom: 16px;
}

.no-data-message h3 {
    color: #495057;
    font-size: 1.2rem;
    margin-bottom: 8px;
}

.no-data-message p {
    color: #6c757d;
    margin: 0;
}

/* Add responsive styles */
@media (max-width: 768px) {
    .chart-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .styled-select {
        width: 100%;
    }

    .submission-charts {
        grid-template-columns: 1fr !important;
    }
}

/* Pagination Styles */
.class-pagination-container,
.submission-pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    background-color: #f8f9fa;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin: 20px 0;
}

.class-pagination-info,
.submission-pagination-info {
    color: #6c757d;
    font-size: 0.875rem;
}

.class-pagination-links,
.submission-pagination-links {
    display: flex;
    align-items: center;
    gap: 15px;
}

.pagination-btn {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 8px 16px;
    background-color: #007bff;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    font-size: 0.875rem;
    font-weight: 500;
    transition: background-color 0.3s ease;
    border: none;
    cursor: pointer;
}

.pagination-btn:hover {
    background-color: #0056b3;
    color: white;
    text-decoration: none;
}

.pagination-btn.disabled {
    background-color: #6c757d;
    color: #adb5bd;
    cursor: not-allowed;
}

.pagination-btn.disabled:hover {
    background-color: #6c757d;
    color: #adb5bd;
}

.page-info {
    font-weight: 600;
    color: #495057;
    font-size: 0.875rem;
    padding: 0 10px;
}

@media (max-width: 768px) {
    .class-pagination-container,
    .submission-pagination-container {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .class-pagination-links,
    .submission-pagination-links {
        justify-content: center;
    }
}
</style>

@push('scripts')
<!-- Include Chart.js from CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>

<script>
    // Add CSRF token to all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Store all charts
    const charts = {};

    // Initialize charts for each class and submission number
    @foreach($paginatedClasses as $class)
    @foreach($currentSubmissions as $submissionNumber)
        const ctx{{ $class->class_id }}{{ $submissionNumber }} = document.getElementById(`chart-{{ $submissionNumber }}-{{ $class->class_id }}`);
        if (ctx{{ $class->class_id }}{{ $submissionNumber }}) {
            const ctx = ctx{{ $class->class_id }}{{ $submissionNumber }}.getContext('2d');
            charts[`{{ $submissionNumber }}-{{ $class->class_id }}`] = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['ICT Learning', '21st Century Skills', 'Expected Outputs'],
                datasets: [
                    {
                        label: '1 - Fully Achieved',
                        data: [0, 0, 0],
                        backgroundColor: '#10B981',
                        borderColor: '#10B981',
                        borderWidth: 1
                    },
                    {
                        label: '2 - Partially Achieved',
                        data: [0, 0, 0],
                        backgroundColor: '#F59E0B',
                        borderColor: '#F59E0B',
                        borderWidth: 1
                    },
                    {
                        label: '3 - Barely Achieved',
                        data: [0, 0, 0],
                        backgroundColor: '#F97316',
                        borderColor: '#F97316',
                        borderWidth: 1
                    },
                    {
                        label: '4 - No Achievement',
                        data: [0, 0, 0],
                        backgroundColor: '#EF4444',
                        borderColor: '#EF4444',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 70,
                        title: {
                            display: true,
                            text: 'Number of Students'
                        },
                        ticks: {
                            precision: 0,
                            stepSize: 5
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Competencies'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: `Internship Grades Distribution by Competency - {{ $submissionNumber }} Submission`
                    }
                }
            }
            });
        }
    @endforeach
    @endforeach

    // Function to update company dropdown based on selected class
    function updateCompanyDropdown(selectedClassId) {
        const companyFilter = document.getElementById('companyFilter');
        const classCompanies = @json($allClassCompanies);

        console.log('Updating company dropdown for class:', selectedClassId);
        console.log('Available class companies:', classCompanies);

        // Clear current options except "All Companies"
        companyFilter.innerHTML = '<option value="">All Companies</option>';

        if (selectedClassId && classCompanies[selectedClassId]) {
            const classCompaniesArray = classCompanies[selectedClassId];

            console.log('Companies for selected class:', classCompaniesArray);

            if (classCompaniesArray.length > 0) {
                // Enable dropdown and add companies for the selected class
                companyFilter.disabled = false;
                classCompaniesArray.forEach(company => {
                    const option = document.createElement('option');
                    option.value = company;
                    option.textContent = company;
                    companyFilter.appendChild(option);
                });
            } else {
                // Disable dropdown if no companies for this class
                companyFilter.disabled = true;
                companyFilter.innerHTML = '<option value="">No companies available</option>';
            }

        } else {
            // If no class selected, show all companies from all classes
            const allCompanies = new Set();
            let hasAnyCompanies = false;

            Object.values(classCompanies).forEach(companies => {
                if (companies.length > 0) {
                    hasAnyCompanies = true;
                    companies.forEach(company => allCompanies.add(company));
                }
            });

            if (hasAnyCompanies) {
                companyFilter.disabled = false;
                Array.from(allCompanies).sort().forEach(company => {
                    const option = document.createElement('option');
                    option.value = company;
                    option.textContent = company;
                    companyFilter.appendChild(option);
                });
            } else {
                companyFilter.disabled = true;
                companyFilter.innerHTML = '<option value="">No companies available</option>';
            }
        }
    }



    // Function to update company labels
    function updateCompanyLabels(company) {
        const companyText = company || 'All Companies';
        document.querySelectorAll('[id^="company-label-"]').forEach(label => {
            label.textContent = `(${companyText})`;
        });
    }

    // Function to update a specific chart
    function updateChart(classId, company, submissionNumber) {
        const chartContainer = document.querySelector(`#submission-${submissionNumber}-${classId} .chart-container`);
        const noDataMessage = document.getElementById(`no-data-${submissionNumber}-${classId}`);
        const submissionChart = document.getElementById(`submission-${submissionNumber}-${classId}`);

        // Build query parameters
        const params = new URLSearchParams();
        if (company) params.append('company', company);
        params.append('class_id', classId);
        params.append('submission_number', submissionNumber);
        
        // Make an AJAX call to get updated data
        fetch(`{{ route('training.intern-grades-analytics.data') }}?${params.toString()}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Received data for class', classId, 'with company', company, 'and submission', submissionNumber, ':', data);
            
            if (charts[`${submissionNumber}-${classId}`] && data.classChartData[classId]) {
                const chartData = data.classChartData[classId].chart_data;
                
                // Update the chart data
                charts[`${submissionNumber}-${classId}`].data.labels = chartData.labels;
                charts[`${submissionNumber}-${classId}`].data.datasets = chartData.datasets;
                charts[`${submissionNumber}-${classId}`].update();
                
                // Show/hide based on hasData flag
                if (chartData.hasData) {
                    chartContainer.style.display = 'block';
                    noDataMessage.style.display = 'none';
                    submissionChart.style.display = 'block';
                } else {
                    chartContainer.style.display = 'none';
                    noDataMessage.style.display = 'flex';
                    submissionChart.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Error updating chart:', error);
            // Show no data message on error
            chartContainer.style.display = 'none';
            noDataMessage.style.display = 'flex';
            submissionChart.style.display = 'block';
        });
    }

    // Function to update submission charts layout
    function updateSubmissionChartsLayout(classId) {
        const submissionCharts = document.getElementById(`submission-charts-${classId}`);
        const visibleCharts = Array.from(submissionCharts.querySelectorAll('.submission-chart'))
            .filter(chart => chart.style.display !== 'none');

        // Always use block display for charts
        submissionCharts.style.display = 'block';

        // Update grid columns based on number of visible charts
        if (visibleCharts.length === 1) {
            submissionCharts.style.gridTemplateColumns = '1fr';
        } else if (visibleCharts.length === 2) {
            submissionCharts.style.gridTemplateColumns = 'repeat(2, 1fr)';
        } else if (visibleCharts.length === 3) {
            submissionCharts.style.gridTemplateColumns = 'repeat(3, 1fr)';
        } else if (visibleCharts.length === 4) {
            submissionCharts.style.gridTemplateColumns = 'repeat(2, 1fr)';
        }
    }

    // Add event listener to class filter
    document.getElementById('classFilter').addEventListener('change', function() {
        const selectedClassId = this.value;
        const company = document.getElementById('companyFilter').value;

        console.log('Class filter changed to:', selectedClassId);

        // Update company dropdown based on selected class
        updateCompanyDropdown(selectedClassId);

        // If a specific class is selected, navigate to that class's page
        if (selectedClassId) {
            // Find which page contains this class
            const allClasses = @json($allClasses->pluck('class_id'));
            const classIndex = allClasses.indexOf(selectedClassId);

            if (classIndex !== -1) {
                const targetPage = classIndex + 1; // Pages are 1-indexed
                const currentUrl = new URL(window.location);
                currentUrl.searchParams.set('class_page', targetPage);

                // Preserve current filter values
                const companyValue = document.getElementById('companyFilter').value;

                if (companyValue) {
                    currentUrl.searchParams.set('company', companyValue);
                }

                console.log('Navigating to page', targetPage, 'for class', selectedClassId);
                window.location.href = currentUrl.toString();
                return;
            }
        }

        // If "All Classes" is selected, show all sections on current page
        const sections = document.querySelectorAll('.chart-section');

        sections.forEach(section => {
            const sectionId = section.getAttribute('id');
            const sectionClassId = sectionId.replace('chart-section-', '');

            if (!selectedClassId || sectionClassId === selectedClassId) {
                section.style.display = 'block';
                // Update current submission charts for this class
                const currentSubmissions = @json($currentSubmissions);
                currentSubmissions.forEach(submissionNumber => {
                    updateChart(sectionClassId, company, submissionNumber);
                });
                // Ensure the submission charts container is visible
                const submissionCharts = document.getElementById(`submission-charts-${sectionClassId}`);
                if (submissionCharts) {
                    submissionCharts.style.display = 'block';
                }
                updateSubmissionChartsLayout(sectionClassId);
            } else {
                section.style.display = 'none';
            }
        });
    });

    // Add event listener to company filter
    document.getElementById('companyFilter').addEventListener('change', function() {
        const company = this.value;
        const selectedClassId = document.getElementById('classFilter').value;

        console.log('Company filter changed to:', company);

        // Update company labels
        updateCompanyLabels(company);

        // Update charts for all visible classes
        document.querySelectorAll('.chart-section').forEach(section => {
            const sectionId = section.getAttribute('id');
            const sectionClassId = sectionId.replace('chart-section-', '');

            if (!selectedClassId || sectionClassId === selectedClassId) {
                const currentSubmissions = @json($currentSubmissions);
                currentSubmissions.forEach(submissionNumber => {
                    console.log('Updating chart for company filter - Class:', sectionClassId, 'Company:', company, 'Submission:', submissionNumber);
                    updateChart(sectionClassId, company, submissionNumber);
                });
                updateSubmissionChartsLayout(sectionClassId);
            }
        });
    });



    // Set the class filter to the current class being displayed (for pagination)
    const currentClassId = @json($paginatedClasses->first()->class_id ?? null);
    if (currentClassId) {
        const classFilter = document.getElementById('classFilter');
        classFilter.value = currentClassId;

        // Update company dropdown for the current class
        updateCompanyDropdown(currentClassId);
    }

    // Restore filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);
    const companyParam = urlParams.get('company');

    if (companyParam) {
        const companyFilter = document.getElementById('companyFilter');
        companyFilter.value = companyParam;
        updateCompanyLabels(companyParam);
    }

    // Initial load - update all charts and layouts with current filter values
    document.querySelectorAll('.chart-section').forEach(section => {
        const sectionId = section.getAttribute('id');
        const sectionClassId = sectionId.replace('chart-section-', '');
        const company = document.getElementById('companyFilter').value;
        const currentSubmissions = @json($currentSubmissions);

        currentSubmissions.forEach(submissionNumber => {
            updateChart(sectionClassId, company, submissionNumber);
        });

        // Ensure the submission charts container is visible
        const submissionCharts = document.getElementById(`submission-charts-${sectionClassId}`);
        if (submissionCharts) {
            submissionCharts.style.display = 'block';
        }
        updateSubmissionChartsLayout(sectionClassId);
    });

    // Initial state check for dropdowns
    const classCompanies = @json($allClassCompanies);
    const companyFilter = document.getElementById('companyFilter');

    // Check if there are any companies at all
    const hasAnyCompanies = Object.values(classCompanies).some(companies => companies.length > 0);
    if (!hasAnyCompanies) {
        companyFilter.disabled = true;
        companyFilter.innerHTML = '<option value="">No companies available</option>';
    }
</script>


@endpush 