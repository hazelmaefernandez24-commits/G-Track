@extends('layouts.educator')

@section('title', 'Student Violation Analytics')

@section('css')
    <link rel="stylesheet" href="{{ asset('css/educator/behavior.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
@endsection

@section('content')
    <div class="container-fluid px-1">


        <!-- Student Behavior Modal -->
      
        <h2 class="mb-4">Student Violation Analytics</h2>

        <!-- Stat Cards Section -->
        <div class="row mb-4">
            <!-- Total Students Card -->
            <div class="col-md-4">
                <a href="{{ route('educator.students') }}" class="stat-box-link btn btn-link p-0 border-0 w-100 text-start" id="total-students-btn">
                    <div class="stat-box">
                        <div class="stat-icon primary">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Total Students</h6>
                            <h2 class="total-students">{{ $totalStudents }}</h2>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Pending Appeals Card -->
            <div class="col-md-4">
                <a href="{{ route('educator.appeals.page') }}" class="stat-box-link btn btn-link p-0 border-0 w-100 text-start" id="pending-appeals-btn">
                    <div class="stat-box">
                        <div class="stat-icon danger">
                            <i class="fas fa-gavel"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Pending Appeals</h6>
                            <h2 class="pending-appeals-count">{{ count($pendingAppeals) }}</h2>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Violation Report Card -->
            <div class="col-md-4">
                <a href="{{ route('educator.violation-report') }}" class="stat-box-link btn btn-link p-0 border-0 w-100 text-start" id="report-btn">
                    <div class="stat-box">
                        <div class="stat-icon warning">
                            <i class="fas fa-file-alt"></i>
                        </div>
                        <div class="stat-content">
                            <h6>Violation Report</h6>
                            <h2 class="report-count" id="report-count">0</h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Behavior Status Overview Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Violation Overview</h5>
                    <div class="d-flex align-items-center">
                        <div class="text-white last-updated small me-3">
                            <i class="fas fa-clock me-1"></i> Last updated: {{ date('M d, Y H:i:s') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Chart Controls in a Card -->
                <div class="card mb-4 shadow-sm">
                    <div class="card-body p-3 mt-3">
                        <h6 class="mb-4">Chart Controls</h6>
                        
                        <!-- Time Period Controls -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3">
                                <label class="form-label mb-0"><i class="fas fa-calendar-alt me-1 text-primary"></i> Time Period:</label>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center">
                                    <div class="me-2" style="width: 100px;">
                                        <input type="number" id="yearSelect" class="form-control form-control-sm" value="{{ date('Y') }}" min="2020" max="{{ date('Y') + 5 }}">
                                        <small class="text-muted d-none">Enter any year</small>
                                    </div>
                                    <div style="width: 150px;">
                                        <select id="monthSelect" class="form-select form-select-sm">
                                            <option value="all" selected>All Months</option>
                                            <option value="0">January</option>
                                            <option value="1">February</option>
                                            <option value="2">March</option>
                                            <option value="3">April</option>
                                            <option value="4">May</option>
                                            <option value="5">June</option>
                                            <option value="6">July</option>
                                            <option value="7">August</option>
                                            <option value="8">September</option>
                                            <option value="9">October</option>
                                            <option value="10">November</option>
                                            <option value="11">December</option>
                                        </select>
                                    </div>
                                    
                                </div>
                            </div>
                        </div>
                        
                        <!-- Batch Filter Controls -->
                        <div class="row mb-3 align-items-center">
                            <div class="col-md-3">
                                <label for="behaviorBatchSelect" class="form-label mb-0"><i class="fas fa-users me-1 text-primary"></i> Class Filter:</label>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex align-items-center">
                                    <div class="input-group">
                                        <span class="input-group-text bg-primary text-white">
                                            <i class="fas fa-filter"></i>
                                        </span>
                                        <select class="form-select" id="behaviorBatchSelect" style="min-width: 250px;">
                                            <option value="all" selected>Loading classes...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Y-Axis Scale Filter -->
                        <div class="row align-items-center">
                            <div class="col-md-3">
                                <label class="form-label mb-0"><i class="fas fa-chart-line me-1 text-primary"></i> Y-Axis Scale:</label>
                            </div>
                            <div class="col-md-9">
                                <div class="d-flex">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Y-axis scale buttons">
                                        <button type="button" class="btn btn-outline-primary y-scale-filter active" data-scale="auto">Auto</button>
                                        <button type="button" class="btn btn-outline-primary y-scale-filter" data-scale="10">0-10</button>
                                        <button type="button" class="btn btn-outline-primary y-scale-filter" data-scale="20">0-20</button>
                                        <button type="button" class="btn btn-outline-primary y-scale-filter" data-scale="50">0-50</button>
                                        <button type="button" class="btn btn-outline-primary y-scale-filter" data-scale="100">0-100</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main chart section starts here -->

                <!-- Main Chart -->
                <div class="chart-container position-relative" style="height: 400px;">
                    <canvas id="behaviorChart"></canvas>
                    <div id="chartLoading" class="loading-indicator" style="display: none;">
                        <div class="spinner"></div>
                        <div>Loading chart data...</div>
                    </div>
                </div>

                <!-- Chart Legend with Enhanced Information -->
                <div class="d-flex justify-content-center mt-4">
                    <div class="d-flex align-items-center me-4">
                        <div class="legend-dot" style="background-color: rgba(78, 115, 223, 0.8);"></div>
                        <span class="ms-1 fw-bold">Male</span>
                    </div>
                    <div class="d-flex align-items-center">
                        <div class="legend-dot" style="background-color: rgba(231, 74, 59, 0.8);"></div>
                        <span class="ms-1 fw-bold">Female</span>
                    </div>
                </div>


            </div>
        </div>

        <!-- Students List Modal -->
        <div class="modal fade" id="studentsListModal" tabindex="-1" aria-labelledby="studentsListModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="studentsListModalLabel">Students List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <!-- Filter Controls -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="batchSelect" class="form-label">Filter by Class:</label>
                                <select class="form-select" id="batchSelect">
                                    <option value="all">All Classes</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="studentSearch" class="form-label">Search Students:</label>
                                <input type="text" class="form-control" id="studentSearch" placeholder="Search by name or ID...">
                            </div>
                        </div>

                        <!-- Students Table -->
                        <div class="table-responsive">
                            <table class="table table-striped" id="studentsTable">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Student ID</th>
                                        <th>Class</th>
                                        <th>Gender</th>
                                    </tr>
                                </thead>
                                <tbody id="studentsList">
                                    <!-- Students will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Student Behavior Modal -->
        <div class="modal fade" id="studentBehaviorModal" tabindex="-1" aria-labelledby="studentBehaviorModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="studentBehaviorModalLabel">Student Behavior Chart</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <h6 id="studentBehaviorName">Student Name</h6>
                            <p class="text-muted" id="studentBehaviorId">Student ID: </p>
                        </div>

                        <!-- Time Period Buttons -->
                        <div class="btn-group mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary" id="studentBtn3Months">3 Months</button>
                            <button type="button" class="btn btn-outline-primary active" id="studentBtn6Months">6 Months</button>
                            <button type="button" class="btn btn-outline-primary" id="studentBtn12Months">12 Months</button>
                        </div>

                        <!-- Chart Container -->
                        <div class="chart-container position-relative" style="height: 300px;">
                            <canvas id="studentBehaviorChart"></canvas>
                            <div id="student-chart-loading" class="loading-indicator" style="display: none;">
                                <div class="spinner"></div>
                                <div>Loading student data...</div>
                            </div>
                            <div id="student-chart-error" class="text-center" style="display: none;">
                                <p class="text-danger">Error loading student data</p>
                                <button class="btn btn-primary btn-sm" id="student-retry-button">Retry</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


@endsection

@push('styles')
    <style>
        .loading-indicator {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            z-index: 10;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .chart-container {
            position: relative;
        }

        #behaviorChart {
            position: relative;
            z-index: 1;
        }

        /* Appeals Box Styling */
        .appeals-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: none;
        }

        .appeals-table td {
            vertical-align: middle;
        }

        .appeals-table .btn-group-vertical .btn {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }

        .appeals-table .student-info strong {
            color: #495057;
        }

        .appeals-table .violation-info strong {
            color: #6c757d;
        }

        .appeals-reason {
            max-width: 250px;
            word-wrap: break-word;
        }

        .appeals-reason .text-truncate {
            line-height: 1.4;
        }

        .appeals-actions {
            min-width: 120px;
        }

        .card-header.bg-warning {
            border-bottom: 2px solid #ffc107;
        }

        .badge.bg-danger {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Stat box styling for danger icon */
        .stat-icon.danger {
            background-color: #dc3545;
            color: white;
        }

        .stat-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            height: 100%;
            display: flex;
            align-items: center;
        }

        .stat-box:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            font-size: 24px;
        }

        .stat-icon.primary {
            background-color: #007bff;
            color: white;
        }

        .stat-icon.warning {
            background-color: #ffc107;
            color: #212529;
        }

        .stat-content h6 {
            margin: 0;
            font-size: 14px;
            color: #6c757d;
            font-weight: 600;
        }

        .stat-content h2 {
            margin: 5px 0 0 0;
            font-size: 32px;
            font-weight: bold;
            color: #495057;
        }


    </style>
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>



    
    <script>
    // Batch Filter for Students Modal
    document.addEventListener('DOMContentLoaded', function() {
        loadAvailableBatchesForStudentsModal();

        const batchSelect = document.getElementById('batchSelect');
        const studentSearch = document.getElementById('studentSearch');

        if (batchSelect) {
            batchSelect.addEventListener('change', filterStudentList);
        }
        if (studentSearch) {
            studentSearch.addEventListener('input', filterStudentList);
        }
        function loadAvailableBatchesForStudentsModal() {
            fetch('/educator/available-batches')
                .then(response => response.json())
                .then(data => {
                    const batchSelect = document.getElementById('batchSelect');
                    batchSelect.innerHTML = '';
                    if (data.success) {
                        data.batches.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch.value;
                            option.textContent = batch.label;
                            batchSelect.appendChild(option);
                        });
                    } else {
                        batchSelect.innerHTML = '<option value="all">All Classes</option>';
                    }
                })
                .catch(() => {
                    const batchSelect = document.getElementById('batchSelect');
                    batchSelect.innerHTML = '<option value="all">All Classes</option>';
                });
        }
        function filterStudentList() {
            const batchSelect = document.getElementById('batchSelect');
            const studentSearch = document.getElementById('studentSearch');
            const studentsTable = document.getElementById('studentsTable');

            if (!batchSelect || !studentSearch || !studentsTable) {
                console.warn('Filter elements not found');
                return;
            }

            const batch = batchSelect.value;
            const searchText = studentSearch.value.toLowerCase();
            const rows = studentsTable.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const studentId = row.children[1]?.textContent || '';
                const matchBatch = (batch === 'all') || studentId.startsWith(batch);
                const matchSearch = row.textContent.toLowerCase().includes(searchText);
                row.style.display = (matchBatch && matchSearch) ? '' : 'none';
            });
        }
    });
    </script>

    <!-- Pass student counts and violation data to JavaScript -->
    <script>
        // Set global variables for student counts that will be used by behavior-charts.js
        window.totalStudents = {{ App\Models\User::where('user_role', 'student')->count() }};
        window.maleStudents = {{ App\Models\User::where('user_role', 'student')->where('gender', 'M')->count() }};
        window.femaleStudents = {{ App\Models\User::where('user_role', 'student')->where('gender', 'F')->count() }};
        
        // Get violation counts by month for male and female students
        window.maleViolationsByMonth = {
            @php
                // Get current year
                $currentYear = date('Y');
                
                // Get all months
                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                
                // Count violations by month for male students
                $maleViolationsByMonth = [];
                foreach ($months as $index => $month) {
                    $monthNum = $index + 1;
                    $startDate = "$currentYear-$monthNum-01";
                    $endDate = date('Y-m-t', strtotime($startDate));
                    
                    $count = App\Models\Violation::join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->where('pnph_users.gender', 'M')
                        ->where('violations.action_taken', true)
                        ->whereDate('violations.created_at', '>=', $startDate)
                        ->whereDate('violations.created_at', '<=', $endDate)
                        ->distinct('violations.student_id')
                        ->count('violations.student_id');
                    
                    $maleViolationsByMonth[$month] = $count;
                }
            @endphp
            
            @foreach($maleViolationsByMonth as $month => $count)
                '{{ $month }}': {{ $count }},
            @endforeach
        };
        
        // Get violation counts by month for female students
        window.femaleViolationsByMonth = {
            @php
                // Count violations by month for female students
                $femaleViolationsByMonth = [];
                foreach ($months as $index => $month) {
                    $monthNum = $index + 1;
                    $startDate = "$currentYear-$monthNum-01";
                    $endDate = date('Y-m-t', strtotime($startDate));
                    
                    $count = App\Models\Violation::join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->where('pnph_users.gender', 'F')
                        ->where('violations.action_taken', true)
                        ->whereDate('violations.created_at', '>=', $startDate)
                        ->whereDate('violations.created_at', '<=', $endDate)
                        ->distinct('violations.student_id')
                        ->count('violations.student_id');
                    
                    $femaleViolationsByMonth[$month] = $count;
                }
            @endphp
            
            @foreach($femaleViolationsByMonth as $month => $count)
                '{{ $month }}': {{ $count }},
            @endforeach
        };
        
        // Get violation counts by week for male and female students
        window.maleViolationsByWeek = {
            @php
                // Get current month
                $currentMonth = date('n') - 1; // 0-indexed for JavaScript
                $monthName = date('F');
                
                // Calculate the number of weeks in the month
                $firstDay = new DateTime("$currentYear-" . ($currentMonth + 1) . "-01");
                $lastDay = new DateTime("$currentYear-" . ($currentMonth + 1) . "-" . date('t', strtotime("$currentYear-" . ($currentMonth + 1) . "-01")));
                $numWeeks = ceil($lastDay->format('j') / 7);
                
                // Count violations by week for male students
                $maleViolationsByWeek = [];
                for ($week = 1; $week <= $numWeeks; $week++) {
                    $weekStart = ($week - 1) * 7 + 1;
                    $weekEnd = min($week * 7, $lastDay->format('j'));
                    
                    $startDate = "$currentYear-" . ($currentMonth + 1) . "-$weekStart";
                    $endDate = "$currentYear-" . ($currentMonth + 1) . "-$weekEnd";
                    
                    $count = App\Models\Violation::join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->where('pnph_users.gender', 'M')
                        ->where('violations.action_taken', true)
                        ->whereDate('violations.created_at', '>=', $startDate)
                        ->whereDate('violations.created_at', '<=', $endDate)
                        ->distinct('violations.student_id')
                        ->count('violations.student_id');
                    
                    $maleViolationsByWeek["$monthName-Week $week"] = $count;
                }
            @endphp
            
            @foreach($maleViolationsByWeek as $week => $count)
                '{{ $week }}': {{ $count }},
            @endforeach
        };
        
        // Get violation counts by week for female students
        window.femaleViolationsByWeek = {
            @php
                // Count violations by week for female students
                $femaleViolationsByWeek = [];
                for ($week = 1; $week <= $numWeeks; $week++) {
                    $weekStart = ($week - 1) * 7 + 1;
                    $weekEnd = min($week * 7, $lastDay->format('j'));
                    
                    $startDate = "$currentYear-" . ($currentMonth + 1) . "-$weekStart";
                    $endDate = "$currentYear-" . ($currentMonth + 1) . "-$weekEnd";
                    
                    $count = App\Models\Violation::join('student_details', 'violations.student_id', '=', 'student_details.student_id')
                        ->join('pnph_users', 'student_details.user_id', '=', 'pnph_users.user_id')
                        ->where('pnph_users.gender', 'F')
                        ->where('violations.action_taken', true)
                        ->whereDate('violations.created_at', '>=', $startDate)
                        ->whereDate('violations.created_at', '<=', $endDate)
                        ->distinct('violations.student_id')
                        ->count('violations.student_id');
                    
                    $femaleViolationsByWeek["$monthName-Week $week"] = $count;
                }
            @endphp
            
            @foreach($femaleViolationsByWeek as $week => $count)
                '{{ $week }}': {{ $count }},
            @endforeach
        };
    </script>
    
    <script src="{{ asset('js/behavior-charts.js') }}"></script>

    <!-- Add Bootstrap JS if not already included -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Load available batches for behavior page dropdown
        function loadAvailableBatchesForBehavior() {
            const batchSelect = document.getElementById('behaviorBatchSelect');
            if (!batchSelect) {
                console.error('Batch select element not found!');
                return;
            }
            // Always show loading state initially
            batchSelect.innerHTML = '<option value="">Loading classes...</option>';

            // Get CSRF token
            const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            fetch('/educator/available-batches', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token || ''
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            throw new Error(`HTTP error! status: ${response.status}, text: ${text}`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.batches && data.batches.length > 0) {
                        batchSelect.innerHTML = '';
                        data.batches.forEach(batch => {
                            const option = document.createElement('option');
                            option.value = batch.value;
                            option.textContent = batch.label;
                            if (batch.value === 'all') {
                                option.selected = true;
                            }
                            batchSelect.appendChild(option);
                        });
                    } else {
                        // If API returns no batches, show fallback
                        loadFallbackBatches();
                    }
                })
                .catch(error => {
                    // On API/network error, show fallback
                    loadFallbackBatches();
                });
        }

        // Fallback function to load default batches
        function loadFallbackBatches() {
            const batchSelect = document.getElementById('behaviorBatchSelect');
            if (batchSelect) {
                batchSelect.innerHTML = `
                    <option value="all" selected>All Classes</option>
                    <option value="2024">2024 Batch</option>
                    <option value="2025">2025 Batch</option>
                    <option value="2026">2026 Batch</option>
                    <option value="2027">2027 Batch</option>
                `;
            }
        }

    

        // Force chart creation immediately


        // Initialize the behavior chart directly when the page loads
        document.addEventListener('DOMContentLoaded', function() {

            // Load available batches for behavior page dropdown
            loadAvailableBatchesForBehavior();

            // Update report count
            updateReportCount();

            // Handle batch filter dropdown change
            const behaviorBatchSelect = document.getElementById('behaviorBatchSelect');
            if (behaviorBatchSelect) {
                behaviorBatchSelect.addEventListener('change', function() {
                    const batch = this.value;
                    console.log('Behavior page batch filter changed to:', batch);
                    // Call the existing batch filtering function
                    if (typeof window.filterDataByBatch === 'function') {
                        window.filterDataByBatch(batch);
                    }
                });
            }

            // Initialize the chart using the function from behavior-charts.js
            console.log('Attempting to initialize behavior chart...');
            console.log('initBehaviorChart function available:', typeof window.initBehaviorChart);
            console.log('Canvas element exists:', document.getElementById('behaviorChart') !== null);

            if (typeof window.initBehaviorChart === 'function') {
                console.log('Calling initBehaviorChart...');
                window.initBehaviorChart();
            } else {
                console.error('initBehaviorChart function not found. Make sure behavior-charts.js is loaded correctly.');

                // Try to initialize chart manually as fallback
                console.log('Attempting manual chart initialization...');
                const canvas = document.getElementById('behaviorChart');
                if (canvas && typeof Chart !== 'undefined') {
                    console.log('Chart.js is available, creating basic chart...');

                    // Create a basic chart with empty data as fallback
const ctx = canvas.getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: [],
        datasets: []
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            title: {
                display: true,
                text: 'Student Violations by Month',
                font: { size: 16, weight: 'bold' }
            },
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true },
            x: { title: { display: true, text: 'Month' } }
        }
    }
});

                    // Hide loading indicator
                    const loadingElement = document.getElementById('chartLoading');
                    if (loadingElement) {
                        loadingElement.style.display = 'none';
                    }

                    console.log('Basic chart created successfully');
                } else {
                    console.error('Canvas or Chart.js not available for manual initialization');
                }
            }

            // Initialize additional features
            initializeAdditionalFeatures();

            // Initialize year and month dropdowns
            const yearSelect = document.getElementById('yearSelect');
            const monthSelect = document.getElementById('monthSelect');

            // Add event listeners to dropdowns
            if (yearSelect) {
                yearSelect.addEventListener('change', function() {
                    console.log('Year changed to:', this.value);
                    if (typeof window.updateChartByPeriod === 'function') {
                        window.updateChartByPeriod();
                    } else {
                        console.log('updateChartByPeriod not available, updating chart title');
                        if (window.behaviorChart) {
                            window.behaviorChart.options.plugins.title.text = `Student Violations by Month (${this.value})`;
                            window.behaviorChart.update();
                        }
                    }
                });
            }

            if (monthSelect) {
                monthSelect.addEventListener('change', function() {
                    console.log('Month changed to:', this.value);
                    if (typeof window.updateChartByPeriod === 'function') {
                        window.updateChartByPeriod();
                    } else {
                        console.log('updateChartByPeriod not available, month filtering not implemented yet');
                    }
                });
            }

            // Initialize refresh button
            const refreshButton = document.getElementById('refresh-behavior');
            if (refreshButton) {
                refreshButton.addEventListener('click', function() {
                    console.log('Refresh button clicked');
                    if (typeof window.updateChartByPeriod === 'function') {
                        window.updateChartByPeriod();
                    } else {
                        console.log('updateChartByPeriod not available, refreshing with API call');
                        // Make API call to refresh data
                        const yearSelect = document.getElementById('yearSelect');
                        const monthSelect = document.getElementById('monthSelect');
                        const batchSelect = document.getElementById('behaviorBatchSelect');

                        const year = yearSelect ? yearSelect.value : new Date().getFullYear();
                        const month = monthSelect ? monthSelect.value : 'all';
                        const batch = batchSelect ? batchSelect.value : 'all';

                        // Show loading
                        this.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Refreshing...';
                        this.disabled = true;

                        // Build API URL
                        let apiUrl = `/educator/behavior/data?year=${year}&batch=${batch}`;
                        if (month !== 'all') {
                            apiUrl += `&month=${month}`;
                        }

                        // Fetch new data
                        fetch(apiUrl, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            console.log('Refreshed data:', data);
                            if (window.behaviorChart && data.success) {
                                // Update chart data
                                window.behaviorChart.data.datasets[0].data = data.men || [];
                                window.behaviorChart.data.datasets[1].data = data.women || [];
                                window.behaviorChart.data.labels = data.labels || [];
                                window.behaviorChart.update();
                            }
                        })
                        .catch(error => {
                            console.error('Error refreshing data:', error);
                        })
                        .finally(() => {
                            // Reset button
                            this.innerHTML = '<i class="fas fa-sync-alt me-1"></i> Refresh Data';
                            this.disabled = false;
                        });
                    }
                });
            }
        });
        // Additional initialization that was in a separate DOMContentLoaded
        function initializeAdditionalFeatures() {
            // Check if we should open the student list modal from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('openStudentList') === '1') {
                // Remove the parameter from URL
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);

                // Open the student list modal
                setTimeout(function() {
                    const studentsListModal = new bootstrap.Modal(document.getElementById('studentsListModal'));
                    studentsListModal.show();
                }, 100);
            }
            const studentSearch = document.getElementById('studentSearch');
            if (studentSearch) {
                studentSearch.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const studentRows = document.querySelectorAll('#studentsList tr');

                    studentRows.forEach(row => {
                        const name = row.cells[0].textContent.toLowerCase();
                        const studentId = row.cells[1].textContent.toLowerCase();
                        const searchText = name + ' ' + studentId;

                        if (searchText.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Add event listeners to student name links
            document.querySelectorAll('.student-name-link').forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Get student ID and name
                    const studentId = this.getAttribute('data-student-id');
                    const studentName = this.textContent.trim();

                    // Show the modal
                    const modal = document.getElementById('studentBehaviorModal');
                    const bsModal = new bootstrap.Modal(modal);
                    bsModal.show();

                    // Update student info
                    document.getElementById('studentBehaviorName').textContent = studentName;
                    document.getElementById('studentBehaviorId').textContent = 'Student ID: ' + studentId;

                    // Show loading indicator
                    document.getElementById('student-chart-loading').style.display = 'flex';

                    // Create a simple chart with default data
                    window.createStudentChart(studentId, 6);
                });
            });

            // Add event listeners to filter buttons
            const btn3Months = document.getElementById('studentBtn3Months');
            const btn6Months = document.getElementById('studentBtn6Months');
            const btn12Months = document.getElementById('studentBtn12Months');
            const retryButton = document.getElementById('student-retry-button');
            const studentBehaviorId = document.getElementById('studentBehaviorId');

            if (btn3Months && btn6Months && btn12Months && studentBehaviorId) {
                btn3Months.addEventListener('click', function() {
                    const studentId = studentBehaviorId.textContent.replace('Student ID: ', '');
                    btn3Months.classList.add('active');
                    btn6Months.classList.remove('active');
                    btn12Months.classList.remove('active');
                    window.createStudentChart(studentId, 3);
                });

                btn6Months.addEventListener('click', function() {
                    const studentId = studentBehaviorId.textContent.replace('Student ID: ', '');
                    btn3Months.classList.remove('active');
                    btn6Months.classList.add('active');
                    btn12Months.classList.remove('active');
                    window.createStudentChart(studentId, 6);
                });

                btn12Months.addEventListener('click', function() {
                    const studentId = studentBehaviorId.textContent.replace('Student ID: ', '');
                    btn3Months.classList.remove('active');
                    btn6Months.classList.remove('active');
                    btn12Months.classList.add('active');
                    window.createStudentChart(studentId, 12);
                });
            }

            // Add event listener to retry button
            if (retryButton && studentBehaviorId) {
                retryButton.addEventListener('click', function() {
                    const studentId = studentBehaviorId.textContent.replace('Student ID: ', '');
                    let months = 6;
                    if (btn3Months && btn3Months.classList.contains('active')) months = 3;
                    if (btn12Months && btn12Months.classList.contains('active')) months = 12;
                    window.createStudentChart(studentId, months);
                });
            }
        }

        // Function to create student behavior chart
        window.createStudentChart = function(studentId, months) {
            const canvas = document.getElementById('studentBehaviorChart');
            const loadingElement = document.getElementById('student-chart-loading');
            const errorElement = document.getElementById('student-chart-error');

            if (!canvas) {
                console.error('Student chart canvas not found');
                return;
            }

            // Show loading
            if (loadingElement) loadingElement.style.display = 'flex';
            if (errorElement) errorElement.style.display = 'none';

            // Destroy existing chart if it exists
            if (window.studentChart) {
                window.studentChart.destroy();
            }

            // Create basic chart with sample data
            const ctx = canvas.getContext('2d');
            const labels = [];
            const data = [];

            // Generate sample labels based on months
            for (let i = months - 1; i >= 0; i--) {
                const date = new Date();
                date.setMonth(date.getMonth() - i);
                labels.push(date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' }));
                data.push(Math.floor(Math.random() * 5)); // Sample data
            }

            window.studentChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Violations',
                        data: data,
                        borderColor: 'rgba(78, 115, 223, 1)',
                        backgroundColor: 'rgba(78, 115, 223, 0.1)',
                        borderWidth: 2,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        title: {
                            display: true,
                            text: `Student Violations - Last ${months} Months`
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1
                            }
                        }
                    }
                }
            });

            // Hide loading
            if (loadingElement) loadingElement.style.display = 'none';
        };



        function updateReportCount() {
            // Update the report count in the main card
            fetch('/educator/violation-report-data?year=all&month=all&batch=all')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('report-count').textContent = data.total || 0;
                    }
                })
                .catch(error => {
                    console.error('Error updating report count:', error);
                });
        }

        // Function to show full appeal reason in a modal
        function showFullReason(reason) {
            // Create a simple modal to show the full reason
            const modal = document.createElement('div');
            modal.className = 'modal fade';
            modal.innerHTML = `
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Appeal Reason</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p>${reason}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
            const bsModal = new bootstrap.Modal(modal);
            bsModal.show();

            // Remove modal from DOM when hidden
            modal.addEventListener('hidden.bs.modal', function() {
                document.body.removeChild(modal);
            });
        }

        // Function to review an appeal (approve or deny)
        function reviewAppeal(appealId, decision) {
            // Show confirmation dialog
            const action = decision === 'approved' ? 'approve' : 'deny';
            const confirmMessage = `Are you sure you want to ${action} this appeal?`;

            if (!confirm(confirmMessage)) {
                return;
            }

            // For approvals, no reason is required
            let adminResponse = '';
            if (decision === 'approved') {
                adminResponse = 'Appeal approved by educator.';
            } else {
                // Only require reason for denials
                adminResponse = prompt(`Please provide a reason for denying this appeal:`);

                if (!adminResponse || adminResponse.trim().length < 10) {
                    alert('Please provide a detailed reason for denial (at least 10 characters).');
                    return;
                }
            }

            // Show loading state
            const buttons = document.querySelectorAll(`button[onclick*="${appealId}"]`);
            buttons.forEach(btn => {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            });

            // Make API request
            fetch(`/appeals/${appealId}/review`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    decision: decision,
                    admin_response: adminResponse.trim()
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message with toast
                    showSuccessToast(`Appeal ${decision} successfully!`);

                    // Reload the page to update the appeals list after a short delay
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Show error message
                    alert(data.message || 'An error occurred while processing the appeal.');

                    // Reset button states
                    buttons.forEach(btn => {
                        btn.disabled = false;
                        if (btn.textContent.includes('Approve')) {
                            btn.innerHTML = '<i class="fas fa-check me-1"></i>Approve';
                        } else {
                            btn.innerHTML = '<i class="fas fa-times me-1"></i>Deny';
                        }
                    });
                }
            })
            .catch(error => {
                console.error('Error reviewing appeal:', error);
                alert('An error occurred while processing the appeal. Please try again.');

                // Reset button states
                buttons.forEach(btn => {
                    btn.disabled = false;
                    if (btn.textContent.includes('Approve')) {
                        btn.innerHTML = '<i class="fas fa-check me-1"></i>Approve';
                    } else {
                        btn.innerHTML = '<i class="fas fa-times me-1"></i>Deny';
                    }
                });
            });
        }

        // Function to show success toast notification
        function showSuccessToast(message) {
            // Create toast container if it doesn't exist
            let toastContainer = document.getElementById('toast-container');
            if (!toastContainer) {
                toastContainer = document.createElement('div');
                toastContainer.id = 'toast-container';
                toastContainer.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    z-index: 9999;
                `;
                document.body.appendChild(toastContainer);
            }

            // Create toast element
            const toast = document.createElement('div');
            toast.style.cssText = `
                background: #28a745;
                color: white;
                padding: 15px 20px;
                border-radius: 8px;
                margin-bottom: 10px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.15);
                display: flex;
                align-items: center;
                min-width: 300px;
                animation: slideInRight 0.3s ease-out;
            `;

            toast.innerHTML = `
                <i class="fas fa-check-circle me-2"></i>
                <span>${message}</span>
            `;

            // Add CSS animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes slideInRight {
                    from { transform: translateX(100%); opacity: 0; }
                    to { transform: translateX(0); opacity: 1; }
                }
                @keyframes slideOutRight {
                    from { transform: translateX(0); opacity: 1; }
                    to { transform: translateX(100%); opacity: 0; }
                }
            `;
            document.head.appendChild(style);

            // Add toast to container
            toastContainer.appendChild(toast);

            // Auto remove after 4 seconds
            setTimeout(() => {
                toast.style.animation = 'slideOutRight 0.3s ease-in';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 300);
            }, 4000);
        }
    </script>
@endpush