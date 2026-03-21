<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - Student Report Validation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons/css/all/all.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f4f4;
            color: #333;
            padding-top: 80px; /* Add padding to push content below fixed header */
        }

                    header {
            font-family: 'Poppins', sans-serif;
            background-color: #22BBEA;
            color: white;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1100; /* ensure header is above sidebar/content */
            box-sizing: border-box;
            min-height: var(--header-height);
            }

            .logo {
            font-family: 'Poppins', sans-serif;
            margin-left: 0;
            }
            .logo img {
            /* constrain logo height so header stays compact */
            height: 56px;
            width: auto;
            margin-left: 0;
            display: block;
            }

        /* Sidebar - Exact Match Dashboard */
        .sidebar {
            width: 250px;
            background: #f8f9fa;
            color: black;
            padding: 20px;
            position: fixed;
            top: 130px;
            left: 0;
            height: calc(100vh - 130px);
            overflow-y: auto;
            z-index: 100;
            border-right: 3px solid #22BBEA;
        }

        .sidebar-icon {
            width: 30px;
            height: 30px;
            margin-right: 8px;
            vertical-align: middle;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
            margin: 0;
        }

        .sidebar ul li {
            margin: 15px 0;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .sidebar ul li a {
            text-decoration: none;
            color: black;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 18px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .sidebar ul li:hover {
            background: #fa5408;
            color: white;
            max-width: 100%;
            max-height: 100%;
        }

        .sidebar ul li:hover a {
            color: white;
        }

        .sidebar ul li.active {
            background: #fa5408;
            color: white;
        }

        .sidebar ul li.active a {
            color: white;
        }

        /* Main Content - Fixed for proper table display */
        .main-content {
            margin-left: 270px; /* Account for sidebar width + padding */
            margin-right: 40px; /* Add right margin to prevent full width */
            margin-top: 20px; /* Reduced since body now has padding-top */
            padding: 20px;
            min-height: calc(100vh - 120px);
            background: #f4f4f4;
            max-width: calc(100vw - 330px); /* Limit maximum width */
            position: relative;
            z-index: 1; /* Ensure content is above any background elements */
        }

        /* Page Title Styling */
        .page-title {
            background: linear-gradient(135deg, #22BBEA 0%, #1a9bc7 100%);
            color: white;
            padding: 20px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 4px 16px rgba(34, 187, 234, 0.2);
        }

        .page-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: white;
        }

        .page-title p {
            font-size: 0.95rem;
            margin: 8px 0 0 0;
            line-height: 1.4;
            color: rgba(255, 255, 255, 0.9);
        }



        /* Admin Validation Styles - Clean Modern Design */
        .admin-validation-section {
            background: white;
            border-radius: 12px;
            padding: 0;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb; /* Use consistent gray border instead of red */
            width: 100%;
            max-width: 1200px; /* Limit table width for better readability */
            overflow: hidden; /* Prevent content overflow */
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0;
            padding: 20px 24px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
            border-radius: 12px 12px 0 0;
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: #374151;
            display: flex;
            align-items: center;
            margin: 0;
        }

        .filter-controls {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .filter-select {
            padding: 8px 16px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 14px;
            background: white;
            color: #374151;
            font-weight: 500;
            min-width: 140px;
        }

        .reports-table-container {
            overflow-x: auto;
            margin-top: 0;
            padding: 0 24px 24px 24px;
            background: white;
            border-radius: 0 0 12px 12px; /* Round bottom corners */
            position: relative;
        }

        .validation-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            background: white;
        }

        .validation-table th {
            background: #f9fafb;
            padding: 16px 24px;
            text-align: left;
            font-weight: 600;
            border-bottom: 1px solid #e5e7eb;
            color: #374151;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .validation-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }

        .validation-table tr:hover {
            background: #f9fafb;
        }

        .report-id {
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Roboto Mono', monospace;
            background: #f3f4f6;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #6b7280;
        }

        .task-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .task-kitchen { background: #fef3c7; color: #92400e; }
        .task-dining { background: #d1fae5; color: #065f46; }
        .task-dishwashing { background: #dbeafe; color: #1e40af; }
        .task-offices { background: #fce7f3; color: #be185d; }
        .task-garbage { background: #f3f4f6; color: #374151; }
        .task-groundfloor { background: #e0f2fe; color: #0369a1; }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        /* Student task statuses (visible to admin) */
        .status-not_started {
            background: #f3f4f6;
            color: #6b7280;
        }
        .status-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }
        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-valid {
            background: #d1fae5;
            color: #065f46;
        }

        .status-invalid {
            background: #fee2e2;
            color: #dc2626;
        }

        .validation-buttons {
            display: flex;
            gap: 8px;
        }

        .validation-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .valid-btn {
            background: #10b981;
            color: white;
        }

        .valid-btn:hover {
            background: #059669;
        }

        .invalid-btn {
            background: #ef4444;
            color: white;
        }

        .invalid-btn:hover {
            background: #dc2626;
        }

        .validation-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .validation-status {
            text-align: center;
            padding: 8px;
        }

        .validation-status .status-badge {
            display: inline-block;
            border-radius: 6px;
            font-size: 12px;
        }

        /* Remove any red borders or outlines that might cause visual issues */
        * {
            outline: none !important;
        }
        
        /* Ensure no elements have red borders */
        .admin-validation-section,
        .reports-table-container,
        .validation-table,
        .main-content {
            border-color: #e5e7eb !important;
        }
        
        /* Fix any potential z-index issues */
        .sidebar {
            z-index: 1000;
        }
        
        .main-content {
            z-index: 1;
        }

        /* Responsive Design */
        @media (max-width: 1200px) {
            .main-content {
                margin-left: 270px;
                margin-right: 20px;
                padding: 15px;
                max-width: calc(100vw - 310px);
            }
            
            .admin-validation-section {
                max-width: 100%;
            }
            
            .filter-controls {
                flex-wrap: wrap;
                gap: 8px;
            }
        }
        
        @media (max-width: 768px) {
            body {
                padding-top: 70px; /* Slightly less padding for mobile */
            }
            
            .main-content {
                margin-left: 0;
                margin-top: 10px; /* Reduced since body has padding */
                padding: 15px;
                margin-right: 15px;
            }
            
            .sidebar {
                transform: translateX(-100%);
            }
            
            .validation-table th,
            .validation-table td {
                padding: 12px 8px;
                font-size: 12px;
            }

            .validation-buttons {
                flex-direction: column;
                gap: 4px;
            }

            .validation-btn {
                padding: 6px 12px;
                font-size: 11px;
            }
            
            .reports-table-container {
                padding: 0 12px 12px 12px;
            }
            
            .section-header {
                padding: 15px;
                flex-direction: column;
                gap: 15px;
                align-items: stretch;
            }
            
            .filter-controls {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
        </div>
        
        <!-- Logout removed - only available in dashboard -->
    </header>

    <!-- Include consistent admin sidebar -->
    @include('partials.sidebar')

    <!-- Main Content Area -->
    <div class="main-content">

        <!-- Admin Validation Section -->
            <div class="admin-validation-section">
                <div class="section-header">
                    <h2 class="section-title">
                        📊 Reports Management
                    </h2>
                    <div class="filter-controls" style="display: flex; gap: 15px; align-items: center;">
                        <div>
                            <label for="dateFilter" style="font-size: 0.9rem; font-weight: 600; color: #333; margin-right: 8px;">Filter by Date:</label>
                            <input type="date" id="dateFilter" class="filter-select" style="padding: 8px 12px; border: 1px solid #e1e5e9; border-radius: 4px; font-size: 0.9rem;" onchange="filterReportsByDate()">
                        </div>
                        <div>
                            <label for="statusFilter" style="font-size: 0.9rem; font-weight: 600; color: #333; margin-right: 8px;">Status:</label>
                            <select id="statusFilter" class="filter-select" onchange="filterReports()">
                                <option value="">All Reports</option>
                                <option value="not_started">Not Started</option>
                                <option value="in_progress">In Progress</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending Validation</option>
                                <option value="valid">Valid Reports</option>
                                <option value="invalid">Invalid Reports</option>
                            </select>
                        </div>
                        <div>
                            <button onclick="clearFilters()" style="background: #6c757d; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; cursor: pointer;">Clear Filters</button>
                        </div>
                    </div>
                </div>

                <div class="submitted-reports-container" id="submittedReportsContainer">
                    <div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 6px; border-left: 4px solid #22BBEA;">
                        <span id="reportsSummary" style="font-weight: 600; color: #333;">Loading student reports...</span>
                    </div>
                    <div class="reports-table-container">
                        <table class="validation-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Student Name</th>
                                    <th>Task Category</th>
                                    <th>Reported By</th>
                                    <th>Date Submitted</th>
                                    <th>Status</th>
                                    <th>Admin Validation</th>
                                </tr>
                            </thead>
                            <tbody id="reportsTableBody">
                                <!-- Reports will be populated here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
    </div>

    <script>
        // Load submitted reports when page loads
        document.addEventListener('DOMContentLoaded', function() {
            console.log('✅ Admin validation page loaded successfully');
            console.log('🔧 Page fixed - CSS and structure restored');
            loadSubmittedReports();
            
            // Set today's date as default in date filter
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('dateFilter').value = today;
            
            // Test if CSRF token is available
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token available:', !!csrfToken);
            
            // Test if all elements are properly loaded
            console.log('📊 Table body found:', !!document.getElementById('reportsTableBody'));
            console.log('🎯 Filter controls found:', !!document.getElementById('statusFilter'));
        });

        // Admin Validation Functions
        async function loadSubmittedReports() {
            try {
                const response = await fetch(`/api/task-submissions?_=${Date.now()}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    cache: 'no-store'
                });
                
                console.log('API Response Status:', response.status);
                console.log('API Response Headers:', response.headers);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('API Error Response:', errorText);
                    throw new Error(`Failed to fetch submissions: ${response.status} - ${errorText.substring(0, 200)}`);
                }
                
                const reports = await response.json();
                console.log('Successfully loaded', reports.length, 'reports from API');
                displaySubmittedReports(reports);
                // Apply initial filter (today's date by default)
                setTimeout(() => filterReports(), 100);
            } catch (error) {
                console.error('Error loading submissions:', error);
                // Show error message to user
                const tableBody = document.getElementById('reportsTableBody');
                if (tableBody) {
                    tableBody.innerHTML = `
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: #dc3545;">
                                ❌ Error loading student reports: ${error.message}<br>
                                <small>Check console for details. Try refreshing the page.</small>
                            </td>
                        </tr>
                    `;
                }
                // Fallback to empty array if API fails
                displaySubmittedReports([]);
            }
        }

        function displaySubmittedReports(reports) {
            console.log('Displaying', reports.length, 'reports');
            const tableBody = document.getElementById('reportsTableBody');
            tableBody.innerHTML = '';
            
            // Store reports for filtering
            allReports = reports;
            
            // Check if there are any reports that need validation (in_progress or pending)
            const hasPendingReports = reports.some(report => report.status === 'in_progress' || report.status === 'pending');
            
            // Show/hide Admin Validation column based on pending reports
            const table = document.querySelector('.validation-table');
            const adminValidationHeader = table.querySelector('th:last-child');
            const adminValidationCells = table.querySelectorAll('td:last-child');
            
            if (hasPendingReports) {
                adminValidationHeader.style.display = '';
            } else {
                adminValidationHeader.style.display = 'none';
            }

            reports.forEach((report, index) => {
                console.log(`Creating row for report ${index + 1}:`, report.id, 'Status:', report.status);
                const row = document.createElement('tr');
                row.setAttribute('data-status', report.status);
                row.setAttribute('data-task', report.task);
                
                // Add date attribute for filtering (YYYY-MM-DD format)
                const reportDate = new Date(report.dateSubmitted);
                const dateForFilter = reportDate.toISOString().split('T')[0];
                row.setAttribute('data-date', dateForFilter);

                const formattedDate = reportDate.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });

                // Use original_id for validation if available, otherwise use the report.id
                const validationId = report.original_id || report.id;
                
                // Use the separate student ID and name from API
                const studentId = report.id; // Student ID code
                const studentName = report.student_name || 'Unknown Student'; // Actual student name
                
                // Create row content based on whether we have pending reports
                // Format task category name properly
                const taskCategoryDisplay = report.task || 'Unknown Category';
                
                // Student-selected task status (normalize and fallback)
                // Prefer API-provided fields; fallback to parsing description
                let studentStatusKey = (report.student_status || '');
                let studentStatusDisplay = (report.student_status_display || '');
                if (!studentStatusKey) {
                    try {
                        const desc = (report.description || '').toString();
                        const m = desc.match(/Status:\s*([^\n]+)/i);
                        if (m && m[1]) {
                            const raw = m[1].trim();
                            const key = raw.toLowerCase().replace(/\s+/g, '_');
                            if (['not_started','in_progress','completed'].includes(key)) {
                                studentStatusKey = key;
                            } else if (key === 'inprogress') {
                                studentStatusKey = 'in_progress';
                            } else if (key === 'done') {
                                studentStatusKey = 'completed';
                            }
                            studentStatusDisplay = studentStatusKey === 'not_started' ? 'Not Started' : 
                                              (studentStatusKey === 'in_progress' ? 'In Progress' : 
                                              (studentStatusKey === 'completed' ? 'Completed' : 
                                              (studentStatusKey ? (studentStatusKey.charAt(0).toUpperCase() + studentStatusKey.slice(1)) : 'Not Started')));
                        }
                    } catch (e) {
                        studentStatusKey = 'not_started';
                        studentStatusDisplay = 'Not Started';
                    }
                }
                if (!studentStatusKey) { studentStatusKey = 'not_started'; }
                if (!studentStatusDisplay) { 
                    studentStatusDisplay = studentStatusKey === 'not_started' ? 'Not Started' : 
                                         (studentStatusKey === 'in_progress' ? 'In Progress' : 
                                         (studentStatusKey === 'completed' ? 'Completed' : 
                                         (studentStatusKey.charAt(0).toUpperCase() + studentStatusKey.slice(1)))); 
                }

                let rowContent = `
                    <td><span class="student-id">${studentId}</span></td>
                    <td><span class="student-name">${studentName}</span></td>
                    <td><span class="task-badge">${taskCategoryDisplay}</span></td>
                    <td>${report.submittedBy}</td>
                    <td>${formattedDate}</td>
                    <td><span class="status-badge status-${studentStatusKey}">${studentStatusDisplay}</span></td>
                `;
                
                // Only add Admin Validation column if there are reports that need validation
                if (hasPendingReports) {
                    rowContent += `
                        <td>
                            ${report.status === 'in_progress' || report.status === 'pending' ? `
                                <div class="validation-buttons">
                                    <button class="validation-btn valid-btn"
                                            onclick="validateReport('${validationId}', 'valid', '${report.id}')">
                                        ✓ Valid
                                    </button>
                                    <button class="validation-btn invalid-btn"
                                            onclick="validateReport('${validationId}', 'invalid', '${report.id}')">
                                        ✗ Invalid
                                    </button>
                                </div>
                            ` : `
                                <!-- Empty cell - no admin validation needed -->
                            `}
                        </td>
                    `;
                }
                
                row.innerHTML = rowContent;

                tableBody.appendChild(row);
            });
        }

        async function validateReport(validationId, status, displayId) {
            console.log('🔧 Validating report:', validationId, 'with status:', status, 'displayId:', displayId);
            
            // Show confirmation dialog with the display ID (student name)
            const confirmMessage = `Are you sure you want to mark report for ${displayId} as ${status.toUpperCase()}?`;
            if (!confirm(confirmMessage)) {
                return;
            }

            try {
                // Use the validation ID directly (should be numeric from original_id)
                const numericId = validationId.toString().replace('RPT', '').replace(/^0+/, '') || validationId;
                const apiUrl = `/api/task-submissions/${numericId}/validate`;
                
                console.log('🌐 API URL:', apiUrl);
                console.log('🔢 Numeric ID:', numericId);
                console.log('📝 Display ID:', displayId);

                // Submit validation to backend
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({
                        status: status,
                        notes: `Validated by admin on ${new Date().toLocaleString()}`
                    })
                });

                console.log('📡 Response status:', response.status);
                
                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('❌ Response error:', errorText);
                    throw new Error(`HTTP ${response.status}: ${errorText.substring(0, 200)}`);
                }

                const data = await response.json();
                console.log('✅ Response data:', data);

                if (data.success) {
                    console.log('🎯 Updating UI for displayId:', displayId, 'to status:', status);
                    
                    // Update the UI using the display ID
                    updateReportStatus(displayId, status);
                    
                    // Show success message
                    alert(`✅ Report for ${displayId} has been marked as ${status.toUpperCase()} successfully!`);
                    
                    // Force immediate UI update
                    const rows = document.querySelectorAll('#reportsTableBody tr');
                    rows.forEach(row => {
                        const reportIdElement = row.querySelector('.report-id');
                        if (reportIdElement && reportIdElement.textContent === displayId) {
                            console.log('🔄 Found matching row, updating status...');
                            
                            // Update status badge
                            const statusBadge = row.querySelector('.status-badge');
                            if (statusBadge) {
                                statusBadge.className = `status-badge status-${status}`;
                                statusBadge.textContent = status.charAt(0).toUpperCase() + status.slice(1);
                                console.log('✅ Status badge updated');
                            }
                            
                            // Remove validation buttons
                            const validationCell = row.cells[5]; // Admin Validation column
                            if (validationCell) {
                                validationCell.innerHTML = `<!-- Validated -->`;
                                console.log('✅ Validation buttons removed');
                            }
                            
                            // Update row data attribute
                            row.setAttribute('data-status', status);
                            console.log('✅ Row data-status updated to:', status);
                        }
                    });
                    
                    // Check if we need to hide the Admin Validation column
                    setTimeout(() => {
                        const remainingPendingReports = document.querySelectorAll('#reportsTableBody tr[data-status="pending"]');
                        console.log('🔍 Remaining pending reports:', remainingPendingReports.length);
                        
                        if (remainingPendingReports.length === 0) {
                            const table = document.querySelector('.validation-table');
                            const adminValidationHeader = table.querySelector('th:last-child');
                            const adminValidationCells = table.querySelectorAll('td:last-child');
                            
                            if (adminValidationHeader) {
                                adminValidationHeader.style.display = 'none';
                                console.log('🔒 Admin validation header hidden');
                            }
                            adminValidationCells.forEach(cell => {
                                cell.style.display = 'none';
                            });
                            console.log('🔒 Admin validation cells hidden');
                        }
                    }, 500);
                    
                } else {
                    alert('❌ Error validating report: ' + (data.message || 'Unknown error'));
                }
            } catch (error) {
                console.error('💥 Validation error:', error);
                alert('❌ Error validating report: ' + error.message + '\n\nPlease check the console for more details and try again.');
            }
        }

        function updateReportStatus(reportId, newStatus) {
            // Find the row with this report ID
            const rows = document.querySelectorAll('#reportsTableBody tr');
            rows.forEach(row => {
                const reportIdElement = row.querySelector('.report-id');
                if (reportIdElement && reportIdElement.textContent === reportId) {
                    // Update status badge in the Status column
                    const statusBadge = row.querySelector('.status-badge');
                    statusBadge.className = `status-badge status-${newStatus}`;
                    statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);

                    // Empty the Admin Validation column - no content needed
                    const validationCell = row.cells[5]; // Admin Validation column
                    validationCell.innerHTML = ``;

                    // Update row data attribute
                    row.setAttribute('data-status', newStatus);
                }
            });
        }

        function filterReports() {
            const statusFilter = document.getElementById('statusFilter').value;
            const dateFilter = document.getElementById('dateFilter').value;
            const rows = document.querySelectorAll('#reportsTableBody tr');
            
            let visibleCount = 0;
            let totalCount = rows.length;

            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                const rowDate = row.getAttribute('data-date');
                
                const statusMatch = !statusFilter || rowStatus === statusFilter;
                const dateMatch = !dateFilter || rowDate === dateFilter;
                
                const shouldShow = statusMatch && dateMatch;
                row.style.display = shouldShow ? '' : 'none';
                
                if (shouldShow) visibleCount++;
            });
            
            // Update summary
            updateReportsSummary(visibleCount, totalCount, dateFilter, statusFilter);
        }
        
        function updateReportsSummary(visible, total, dateFilter, statusFilter) {
            const summaryElement = document.getElementById('reportsSummary');
            let summaryText = `Showing ${visible} of ${total} student reports`;
            
            if (dateFilter || statusFilter) {
                summaryText += ' (filtered';
                if (dateFilter) summaryText += ` by date: ${dateFilter}`;
                if (statusFilter) summaryText += ` by status: ${statusFilter}`;
                summaryText += ')';
            }
            
            summaryElement.textContent = summaryText;
        }

        function filterReportsByDate() {
            filterReports(); // Use the combined filter function
        }

        function clearFilters() {
            document.getElementById('statusFilter').value = '';
            document.getElementById('dateFilter').value = '';
            filterReports();
        }

        // Store all reports for filtering
        let allReports = [];
    </script>
</body>
</html>
