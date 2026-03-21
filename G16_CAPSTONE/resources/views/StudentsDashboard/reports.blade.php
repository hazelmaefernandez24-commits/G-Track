
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Report Validation</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: #f6f8fa;
            min-height: 100vh;
            padding-top: 80px; /* Space for fixed header */
        }

        /* Fixed Header */
       header {
        font-family: 'Poppins', sans-serif;
        background-color: #22BBEA;
        color: white;
      padding: 20px;
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
          
    .header-right {
      font-family: 'Poppins', sans-serif;
      flex: 1;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      font-size: 18px;
      font-weight: 500;
      gap: 15px;
    }

        /* Logout button styling (PN-ScholarSync style) */
        .logout-btn-pn {
            background: none;
            border: none;
            color: white;
            padding: 8px;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn-pn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: scale(1.1);
        }

        .logout-btn-pn svg {
            width: 24px;
            height: 24px;
            stroke: white;
        }

        .logout-btn-pn:hover svg {
            stroke: #ffffff;
        }

        .container-fluid {
            display: flex;
            min-height: calc(100vh - 80px);
            min-width: 1500px;
        }

        /* Student Sidebar Styles */
        .sidebar {
            width: 300px;
            background: #ffffff;
            color: #374151;
            padding: 0;
            position: fixed;
            top: 80px;
            left: 0;
            height: calc(100vh - 80px);
            overflow-y: auto;
            z-index: 90;
            border-right: 3px solid #22BBEA;
        }

        .sidebar .nav-link {
            color: #374151;
            font-weight: 400;
            font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 15px;
        }

        .sidebar .nav-link img { 
            vertical-align: middle; 
            width:26px; 
            height:26px; 
            margin-right:14px; 
        }

        .sidebar .nav-link:hover {
            background: #f1f5f9;
            color: #111827;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link.active:hover {
            background: #e0f2fe;
            color: #0f172a;
            font-weight: 400;
        }

        .content {
            font-family: 'Poppins', sans-serif;
            padding: 30px 20px;
            background: #f6f8fa;
            overflow-y: auto;
            width: 100%;
        }

        .page-header {
            background: linear-gradient(135deg, #ff9000 0%, #ff7b00 100%);
            color: #fff;
            border-radius: 4px;
            padding: 6px 10px;
            margin-bottom: 8px;
            box-shadow: 0 1px 3px rgba(255, 144, 0, 0.1);
            max-width: 400px;
            width: fit-content;
        }

        .page-title {
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 1px;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .page-description {
            font-size: 0.65rem;
            margin: 0;
            opacity: 0.8;
        }



        .form-section {
            margin-bottom: 10px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 3px;
            display: block;
            font-size: 0.75rem;
        }

        .form-select, .form-input, .form-textarea {
            width: 100%;
            padding: 6px 10px;
            border: 1px solid #e1e5e9;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.75rem;
            transition: border-color 0.3s ease;
        }

        .form-select[multiple] {
            min-height: 80px;
            padding: 4px;
        }

        .form-select[multiple] option {
            padding: 4px 8px;
            margin: 1px 0;
            border-radius: 2px;
        }

        .form-select[multiple] option:checked {
            background: #22BBEA;
            color: white;
        }

        .form-select:disabled {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .form-select:focus, .form-input:focus, .form-textarea:focus {
            outline: none;
            border-color: #22BBEA;
            box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.1);
        }

        .form-textarea {
            resize: vertical;
            min-height: 50px;
        }

        .checkbox-group {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 6px;
            margin-top: 6px;
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 4px 6px;
            background: #f8f9fa;
            border-radius: 3px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            font-size: 0.7rem;
        }

        .checkbox-item:hover {
            background: #e3f2fd;
            border-color: #22BBEA;
        }

        .checkbox-item input[type="checkbox"] {
            margin-right: 8px;
            transform: scale(1.1);
        }

        /* Reports Sections */
        .reports-sections {
            display: flex;
            flex-direction: column;
            gap: 30px;
            max-width: 1200px;
            width: 100%;
            margin: 0;
        }


        /* Student Report Section */
        .student-report-section {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        .section-header {
            background: #f8f9fa;
            padding: 20px;
            border-bottom: 1px solid #e9ecef;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin: 0;
            display: flex;
            align-items: center;
        }

        .task-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .task-selector > div {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .task-select {
            padding: 8px 12px;
            border: 1px solid #e1e5e9;
            border-radius: 4px;
            font-size: 0.9rem;
            background: #fff;
            min-width: 200px;
        }

        .student-report-table {
            padding: 20px;
            min-height: 200px;
        }

        .no-task-selected {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }

        .no-task-icon {
            font-size: 3rem;
            margin-bottom: 15px;
        }

        .no-task-text {
            font-size: 1rem;
            font-weight: 500;
        }

        /* Weekly Report Table Styles */
        .weekly-report-container {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.06);
            padding: 20px;
            margin-bottom: 20px;
        }

        .batch-selector {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .batch-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }

        .batch-select {
            padding: 8px 12px;
            border: 1px solid #e1e5e9;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.8rem;
            background: #fff;
            cursor: pointer;
        }

        .week-info {
            margin-left: auto;
            font-size: 0.8rem;
            color: #666;
            font-weight: 500;
        }

        .performance-table-container {
            overflow-x: auto;
            margin-bottom: 20px;
        }

        .performance-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8rem;
            background: #fff;
        }

        .performance-table th {
            background: #4a5568;
            color: #fff;
            padding: 12px 8px;
            text-align: center;
            font-weight: 600;
            border: 1px solid #ddd;
        }

        .student-name-header {
            background: #22BBEA !important;
            text-align: left !important;
            min-width: 200px;
            max-width: 200px;
        }

        .day-header {
            min-width: 80px;
            width: 80px;
        }

        .performance-table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: center;
            vertical-align: middle;
        }

        .student-name-cell {
            text-align: left !important;
            font-weight: 500;
            background: #f8f9fa;
            min-width: 200px;
            max-width: 200px;
        }

        .day-cell {
            position: relative;
            width: 80px;
            height: 50px;
        }

        .status-buttons {
            display: flex;
            gap: 4px;
            justify-content: center;
            align-items: center;
        }

        .status-btn {
            width: 28px;
            height: 28px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9rem;
            font-weight: bold;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .check-btn {
            background: #e8f5e8;
            color: #28a745;
            border: 1px solid #28a745;
        }

        .check-btn:hover {
            background: #28a745;
            color: #fff;
        }

        .check-btn.active {
            background: #28a745;
            color: #fff;
        }

        .wrong-btn {
            background: #e3f2fd;
            color: #007bff;
            border: 1px solid #007bff;
        }

        .wrong-btn:hover {
            background: #007bff;
            color: white;
        }

        .wrong-btn.active {
            background: #007bff;
            color: white;
        }

        .no-data {
            text-align: center;
            color: #666;
            font-style: italic;
            padding: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            align-items: flex-start;
            margin-top: 20px;
        }

        .action-buttons button {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .save-btn {
            background: #28a745;
            color: #fff;
        }

        .save-btn:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .upload-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .upload-btn {
            background: #17a2b8;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background: #138496;
            transform: translateY(-1px);
        }

        .uploaded-images {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            max-width: 300px;
            justify-content: center;
        }

        .image-preview {
            position: relative;
            width: 60px;
            height: 60px;
            border-radius: 6px;
            overflow: hidden;
            border: 2px solid #e9ecef;
            background: #f8f9fa;
        }

        .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-remove {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            font-size: 0.7rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .image-remove:hover {
            background: #c82333;
        }

        .image-count {
            font-size: 0.7rem;
            color: #666;
            margin-top: 5px;
        }



        .submit-btn {
            background: #28a745;
            color: #fff;
            border: none;
            padding: 6px 16px;
            border-radius: 4px;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            max-width: 500px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e9ecef;
        }

        .modal-header h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #333;
        }

        .close-btn {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: #666;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .close-btn:hover {
            color: #333;
        }

        .modal-body {
            padding: 0;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container-fluid {
                flex-direction: column;
                min-width: auto;
            }

            .sidebar {
                width: 100%;
                padding: 10px 0;
            }

            .content {
                padding: 15px;
            }
            
            .main-content {
                margin-left: 0 !important;
                justify-content: center !important;
            }

            .reports-sections {
                max-width: 100%;
                width: 100%;
            }

            .section-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .task-selector {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                width: 100%;
            }

            .task-selector > div {
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }

            .task-select {
                width: 100%;
                min-width: auto;
            }
        }



        @media (max-width: 768px) {
            .performance-table-container {
                font-size: 0.7rem;
            }

            .status-btn {
                width: 24px;
                height: 24px;
                font-size: 0.8rem;
            }

            .day-header {
                min-width: 60px;
                width: 60px;
            }
        }

        /* Admin Validation Styles - Clean Modern Design */
        .admin-validation-section {
            background: white;
            border-radius: 12px;
            padding: 0;
            margin-bottom: 24px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid #e5e7eb;
            width: 100%;
            max-width: 1200px;
            overflow: hidden;
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
            border-radius: 0 0 12px 12px;
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

        .submitted-reports-container {
            padding: 20px 0;
        }

        .main-content {
            margin-left: 270px;
            margin-right: 40px;
            margin-top: 20px;
            padding: 20px;
            min-height: calc(100vh - 120px);
            background: #f4f4f4;
            max-width: calc(100vw - 330px);
            position: relative;
            z-index: 1;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
        </div>
    </header>

    @include('partials.sidebar')

    <div class="main-content">
        @php
            $isAdmin = auth()->check() && in_array(auth()->user()->user_role, ['admin', 'educator', 'inspector']);
        @endphp

        @if($isAdmin)
        <!-- ADMIN VALIDATION INTERFACE -->
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
        @else
        <!-- STUDENT REPORT INTERFACE -->
        <div class="reports-sections">
            <!-- Student Report Section -->
            <div class="report-section">
                <div class="section-header">
                    <h3 class="section-title">📝 Student Report</h3>
                </div>
                <div class="task-selector">
                    <div>
                        <label for="taskSelect" style="font-weight: 600; color: #333;">Select Task Category:</label>
                        <select id="taskSelect" class="task-select" onchange="loadStudentReport()">
                            <option value="">-- Select a task --</option>
                            @foreach($taskCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="reportDate" style="font-weight: 600; color: #333;">Report Date:</label>
                        <input type="date" id="reportDate" class="task-select" onchange="loadStudentReport()">
                    </div>
                </div>
                <div id="studentReportTable" class="performance-table-container">
                    <div class="no-task-selected">
                        <div class="no-task-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;color:#666;display:block;margin:0 auto;" aria-hidden="true" focusable="false">
                                <rect x="7" y="3" width="10" height="2" rx="1" fill="currentColor" />
                                <rect x="6" y="5" width="12" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" />
                                <path d="M8 9h8M8 13h8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" fill="none" />
                            </svg>
                        </div>
                        <div class="no-task-text">Select a task category to view student performance report</div>
                    </div>
                </div>
            </div>

            <!-- Previous Reports Section -->
            <div class="report-section">
                <div class="section-header">
                    <h3 class="section-title">📋 Previous Reports</h3>
                    <div style="display: flex; gap: 10px;">
                        <input type="date" id="viewDate" class="task-select" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px;">
                        <button onclick="loadPreviousReports()" style="background: #17a2b8; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; cursor: pointer;">View Reports</button>
                        <button onclick="loadTodaysReports()" style="background: #28a745; color: white; border: none; padding: 8px 16px; border-radius: 4px; font-weight: 600; cursor: pointer;">Today's Reports</button>
                    </div>
                </div>
                <div id="previousReportsTable" class="performance-table-container">
                    <div class="no-task-selected">
                        <div class="no-task-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;color:#666;display:block;margin:0 auto;" aria-hidden="true" focusable="false">
                                <rect x="3" y="5" width="18" height="16" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" />
                                <path d="M16 3v4M8 3v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" fill="none" />
                                <path d="M7 11h10M7 15h10" stroke="currentColor" stroke-width="1.2" stroke-linecap="round" fill="none" />
                            </svg>
                        </div>
                        <div class="no-task-text">Select a date to view previous reports</div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <script>
        // Detect if user is admin based on page content
        const isAdmin = {{ $isAdmin ? 'true' : 'false' }};
        
        // Load submitted reports when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            
            if (isAdmin) {
                console.log('✅ Admin validation page loaded successfully');
                console.log('🔧 Page fixed - CSS and structure restored');
                loadSubmittedReports();
                
                // Set today's date as default in date filter
                const dateFilter = document.getElementById('dateFilter');
                if (dateFilter) {
                    dateFilter.value = today;
                }
            } else {
                console.log('✅ Student report page loaded successfully');
                // Set today's date as default in report date
                const reportDate = document.getElementById('reportDate');
                if (reportDate) {
                    reportDate.value = today;
                }
            }
            
            // Test if CSRF token is available
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            console.log('CSRF Token available:', !!csrfToken);
            
            // Test if all elements are properly loaded
            console.log('📊 Admin table body found:', !!document.getElementById('reportsTableBody'));
            console.log('🎯 Student report table found:', !!document.getElementById('studentReportTable'));
        });

        // Admin Validation Functions
        async function loadSubmittedReports() {
            try {
                const response = await fetch('/api/task-submissions', {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
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
                            <td colspan="7" style="text-align: center; padding: 20px; color: #007bff;">
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
            
            // Check if there are any pending reports
            const hasPendingReports = reports.some(report => report.status === 'pending');
            
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
                
                let rowContent = `
                    <td><span class="student-id">${studentId}</span></td>
                    <td><span class="student-name">${studentName}</span></td>
                    <td><span class="task-badge">${taskCategoryDisplay}</span></td>
                    <td>${report.submittedBy}</td>
                    <td>${formattedDate}</td>
                    <td><span class="status-badge status-${report.status}">${report.status.charAt(0).toUpperCase() + report.status.slice(1)}</span></td>
                `;
                
                // Only add Admin Validation column if there are pending reports
                if (hasPendingReports) {
                    rowContent += `
                        <td>
                            ${report.status === 'pending' ? `
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

        // ========== STUDENT-SIDE FUNCTIONS ==========
        function loadStudentReport() {
            const selectedTask = document.getElementById('taskSelect').value;
            const tableContainer = document.getElementById('studentReportTable');

            console.log('Selected task:', selectedTask);
            console.log('Table container:', tableContainer);

            if (!selectedTask) {
                tableContainer.innerHTML = `
                    <div class="no-task-selected">
                        <div class="no-task-icon">
                            <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;color:#666;display:block;margin:0 auto;" aria-hidden="true" focusable="false">
                                <rect x="7" y="3" width="10" height="2" rx="1" fill="currentColor" />
                                <rect x="6" y="5" width="12" height="14" rx="2" fill="none" stroke="currentColor" stroke-width="1.6" />
                                <path d="M8 9h8M8 13h8" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" fill="none" />
                            </svg>
                        </div>
                        <div class="no-task-text">Select a task category to view student performance report</div>
                    </div>
                `;
                return;
            }

            // Fetch real students from database for the selected category
            const apiUrl = `/api/get-assigned-students/${selectedTask}`;
            console.log('Calling API:', apiUrl);
            fetch(apiUrl, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    console.log('API Response data:', data);
                    if (data.success) {
                        displayStudentReportTable(data.students, selectedTask);
                    } else {
                        console.error('Error fetching students:', data.message);
                        tableContainer.innerHTML = `
                            <div class="no-task-selected">
                                <div class="no-task-text">Error loading students for ${selectedTask}: ${data.message}</div>
                            </div>
                        `;
                    }
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                    tableContainer.innerHTML = `
                        <div class="no-task-selected">
                            <div class="no-task-text">Error loading students: ${error.message}</div>
                        </div>
                    `;
                });
        }

        function displayStudentReportTable(students, selectedTask) {
            const tableContainer = document.getElementById('studentReportTable');
            
            if (students.length === 0) {
                tableContainer.innerHTML = `
                    <div class="no-task-selected">
                        <div class="no-task-text">No students assigned to ${selectedTask} tasks yet</div>
                    </div>
                `;
                return;
            }

            tableContainer.innerHTML = `
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.9rem;">
                        <thead>
                            <tr style="background: #f8f9fa;">
                                <th rowspan="2" style="border: 1px solid #e9ecef; padding: 12px; text-align: center; font-weight: 600;">Name of Student</th>
                                <th colspan="7" style="border: 1px solid #e9ecef; padding: 12px; text-align: center; font-weight: 600; background: #e8f5e8;">Accomplish</th>
                                <th colspan="7" style="border: 1px solid #e9ecef; padding: 12px; text-align: center; font-weight: 600; background: #ffebee;">Non-Accomplish</th>
                            </tr>
                            <tr style="background: #f8f9fa;">
                                ${['M','T','W','TH','F','S','SU'].map(day => `<th style="border: 1px solid #e9ecef; padding: 8px; text-align: center; font-size: 0.8rem;">${day}</th>`).join('')}
                                ${['M','T','W','TH','F','S','SU'].map(day => `<th style="border: 1px solid #e9ecef; padding: 8px; text-align: center; font-size: 0.8rem;">${day}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>
                            ${students.map((student, idx) => `
                                <tr>
                                    <td style="border: 1px solid #e9ecef; padding: 10px; font-weight: 500;">${student.name}</td>
                                    ${['M', 'T', 'W', 'TH', 'F', 'S', 'SU'].map(day => `
                                        <td style="border: 1px solid #e9ecef; padding: 8px; text-align: center;">
                                            <input type="radio" name="${student.id}_${day}" value="accomplish" style="transform: scale(1.2);">
                                        </td>
                                    `).join('')}
                                    ${['M', 'T', 'W', 'TH', 'F', 'S', 'SU'].map(day => `
                                        <td style="border: 1px solid #e9ecef; padding: 8px; text-align: center;">
                                            <input type="radio" name="${student.id}_${day}" value="non-accomplish" style="transform: scale(1.2);">
                                        </td>
                                    `).join('')}
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 20px; text-align: center; display: flex; gap: 15px; justify-content: center;">
                    <button onclick="saveDraftReport()" style="background: #6c757d; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                        💾 Save Draft
                    </button>
                    <button onclick="submitStudentReport()" style="background: #22BBEA; color: white; border: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; cursor: pointer;">
                        📤 Submit Report
                    </button>
                </div>
            `;
        }

        function saveDraftReport() {
            const selectedTask = document.getElementById('taskSelect').value;
            const selectedDate = document.getElementById('reportDate').value;
            
            if (!selectedTask) {
                alert('Please select a task category first');
                return;
            }
            
            if (!selectedDate) {
                alert('Please select a report date first');
                return;
            }

            // Collect all radio button data
            const reportData = {};
            const radios = document.querySelectorAll('input[type="radio"]:checked');

            radios.forEach(radio => {
                const [studentId, day] = radio.name.split('_');
                if (!reportData[studentId]) {
                    reportData[studentId] = {};
                }
                reportData[studentId][day] = radio.value;
            });

            if (Object.keys(reportData).length === 0) {
                alert('Please mark at least one student\'s performance before saving draft');
                return;
            }

            // Save draft to database
            const draftData = {
                category: selectedTask,
                report_data: reportData,
                report_date: selectedDate,
                submitted_by: '{{ auth()->user() ? auth()->user()->user_fname . " " . auth()->user()->user_lname : "System User" }}',
                status: 'draft', // Draft status - won't appear in admin validation
                _token: '{{ csrf_token() }}'
            };

            fetch('/api/save-draft-report', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(draftData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Draft saved successfully! You can continue editing later.');
                    // Don't reset form - keep the data for continued editing
                } else {
                    alert('❌ Error saving draft: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Save draft error:', error);
                alert('Error saving draft. Please try again.');
            });
        }

        function submitStudentReport() {
            const selectedTask = document.getElementById('taskSelect').value;
            const selectedDate = document.getElementById('reportDate').value;
            
            if (!selectedTask) {
                alert('Please select a task category first');
                return;
            }
            
            if (!selectedDate) {
                alert('Please select a report date first');
                return;
            }

            // Collect all radio button data
            const reportData = {};
            const radios = document.querySelectorAll('input[type="radio"]:checked');

            radios.forEach(radio => {
                const [studentId, day] = radio.name.split('_');
                if (!reportData[studentId]) {
                    reportData[studentId] = {};
                }
                reportData[studentId][day] = radio.value;
            });

            if (Object.keys(reportData).length === 0) {
                alert('Please mark at least one student\'s performance before submitting');
                return;
            }

            // Submit to database
            const submitData = {
                category: selectedTask,
                report_data: reportData,
                report_date: selectedDate,
                submitted_by: '{{ auth()->user() ? auth()->user()->user_fname . " " . auth()->user()->user_lname : "System User" }}',
                _token: '{{ csrf_token() }}'
            };

            fetch('/api/submit-student-report', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(submitData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Student report submitted successfully!');
                    // Reset form
                    document.getElementById('taskSelect').value = '';
                    document.getElementById('studentReportTable').innerHTML = `
                        <div class="no-task-selected">
                            <div class="no-task-text">Select a task category to view student performance report</div>
                        </div>
                    `;
                } else {
                    alert('❌ Error submitting report: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Submit error:', error);
                alert('Error submitting report. Please try again.');
            });
        }

        function loadDraftReports() {
            const selectedDate = document.getElementById('viewDate').value;
            const tableContainer = document.getElementById('previousReportsTable');
            
            if (!selectedDate) {
                alert('Please select a date to view drafts');
                return;
            }
            
            // Fetch draft reports from backend
            fetch(`/api/get-drafts-by-date/${selectedDate}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.drafts && data.drafts.length > 0) {
                    let draftsHtml = `
                        <h3 style="color: #6c757d; margin-bottom: 15px;">📝 Draft Reports (${data.drafts.length})</h3>
                        <div style="display: grid; gap: 15px;">
                    `;
                    
                    data.drafts.forEach(draft => {
                        draftsHtml += `
                            <div style="border: 1px solid #6c757d; border-left: 4px solid #6c757d; padding: 15px; border-radius: 8px; background: #f8f9fa;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                    <div>
                                        <strong style="color: #6c757d;">${draft.category}</strong> - 
                                        <small style="color: #6c757d;">${draft.report_date}</small>
                                    </div>
                                    <div>
                                        <span style="background: #6c757d; color: white; padding: 2px 8px; border-radius: 12px; font-size: 12px;">
                                            DRAFT
                                        </span>
                                    </div>
                                </div>
                                <div style="color: #6c757d; margin-bottom: 10px;">
                                    <small>By: ${draft.submitted_by} | Created: ${draft.created_at}</small>
                                </div>
                                <div style="display: flex; gap: 10px;">
                                    <button onclick="loadDraftForEditing('${draft.draft_id}')" style="background: #28a745; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                        ✏️ Edit Draft
                                    </button>
                                    <button onclick="submitDraftFromStorage('${draft.draft_id}')" style="background: #22BBEA; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                        📤 Submit Draft
                                    </button>
                                    <button onclick="deleteDraft('${draft.draft_id}')" style="background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 4px; font-size: 12px; cursor: pointer;">
                                        🗑️ Delete
                                    </button>
                                </div>
                            </div>
                        `;
                    });
                    
                    draftsHtml += '</div>';
                    tableContainer.innerHTML = draftsHtml;
                } else {
                    tableContainer.innerHTML = `
                        <div style="text-align: center; padding: 40px; color: #6c757d;">
                            <div style="font-size: 48px; margin-bottom: 15px;">📝</div>
                            <div style="font-size: 18px; margin-bottom: 10px;">No draft reports found</div>
                            <div style="font-size: 14px;">Draft reports saved for ${selectedDate} will appear here</div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Load drafts error:', error);
                tableContainer.innerHTML = `
                    <div style="text-align: center; padding: 40px; color: #dc3545;">
                        <div style="font-size: 48px; margin-bottom: 15px;">❌</div>
                        <div style="font-size: 18px;">Error loading drafts</div>
                        <div style="font-size: 14px;">Please try again later</div>
                    </div>
                `;
            });
        }

        function loadDraftForEditing(draftId) {
            // Load draft data and populate the form
            fetch(`/api/get-draft-by-id/${draftId}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.draft) {
                    const draft = data.draft;
                    
                    // Set the task category and date
                    document.getElementById('taskSelect').value = draft.category;
                    document.getElementById('reportDate').value = draft.report_date;
                    
                    // Load students and populate the draft data
                    loadAssignedStudents(draft.category, () => {
                        // After students are loaded, populate the draft data
                        setTimeout(() => {
                            Object.keys(draft.report_data).forEach(studentId => {
                                const studentData = draft.report_data[studentId];
                                Object.keys(studentData).forEach(day => {
                                    const radio = document.querySelector(`input[name="${studentId}_${day}"][value="${studentData[day]}"]`);
                                    if (radio) {
                                        radio.checked = true;
                                    }
                                });
                            });
                            
                            alert('✅ Draft loaded! You can continue editing and submit when ready.');
                        }, 1000);
                    });
                } else {
                    alert('❌ Error loading draft: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Load draft error:', error);
                alert('Error loading draft. Please try again.');
            });
        }

        function submitDraftFromStorage(draftId) {
            if (confirm('Are you sure you want to submit this draft? It will be sent to admin for validation.')) {
                // Load draft and submit it as a regular report
                fetch(`/api/get-draft-by-id/${draftId}`, {
                    method: 'GET',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.draft) {
                        const draft = data.draft;
                        
                        // Submit as regular report
                        const submitData = {
                            category: draft.category,
                            report_data: draft.report_data,
                            report_date: draft.report_date,
                            submitted_by: draft.submitted_by,
                            _token: '{{ csrf_token() }}'
                        };

                        fetch('/api/submit-student-report', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(submitData)
                        })
                        .then(response => response.json())
                        .then(result => {
                            if (result.success) {
                                alert('✅ Draft submitted successfully!');
                                // Delete the draft after submission
                                deleteDraft(draftId);
                                // Refresh drafts list
                                loadDraftReports();
                            } else {
                                alert('❌ Error submitting draft: ' + result.message);
                            }
                        })
                        .catch(error => {
                            console.error('Submit draft error:', error);
                            alert('Error submitting draft. Please try again.');
                        });
                    } else {
                        alert('❌ Error loading draft for submission: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Load draft for submission error:', error);
                    alert('Error loading draft for submission. Please try again.');
                });
            }
        }

        function deleteDraft(draftId) {
            if (confirm('Are you sure you want to delete this draft?')) {
                fetch(`/api/delete-draft/${draftId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('✅ Draft deleted successfully!');
                        loadDraftReports(); // Refresh drafts list
                    } else {
                        alert('❌ Error deleting draft: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Delete draft error:', error);
                    alert('Error deleting draft. Please try again.');
                });
            }
        }

        function loadPreviousReports() {
            const selectedDate = document.getElementById('viewDate').value;
            const tableContainer = document.getElementById('previousReportsTable');
            
            if (!selectedDate) {
                alert('Please select a date to view reports');
                return;
            }
            
            // Fetch previous reports from backend
            fetch(`/api/get-reports-by-date/${selectedDate}`, {
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.reports && data.reports.length > 0) {
                    displayPreviousReports(data.reports, selectedDate);
                } else {
                    tableContainer.innerHTML = `
                        <div class="no-task-selected">
                            <div class="no-task-text">No reports found for ${selectedDate}</div>
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error loading previous reports:', error);
                tableContainer.innerHTML = `
                    <div class="no-task-selected">
                        <div class="no-task-text">Error loading reports for ${selectedDate}</div>
                    </div>
                `;
            });
        }

        function displayPreviousReports(reports, selectedDate) {
            const tableContainer = document.getElementById('previousReportsTable');
            
            let reportsHtml = `
                <div style="padding: 20px;">
                    <h3 style="margin-bottom: 20px; color: #333;">Reports for ${selectedDate}</h3>
                    <div style="display: grid; gap: 15px;">
            `;
            
            reports.forEach(report => {
                reportsHtml += `
                    <div style="border: 1px solid #e9ecef; border-radius: 8px; padding: 15px; background: #f8f9fa;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                            <h4 style="margin: 0; color: #22BBEA;">Report ID: ${report.report_id}</h4>
                            <span style="background: ${report.status === 'pending' ? '#ffc107' : '#28a745'}; color: white; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">${report.status}</span>
                        </div>
                        <p><strong>Category:</strong> ${report.category}</p>
                        <p><strong>Submitted at:</strong> ${report.submitted_at}</p>
                    </div>
                `;
            });
            
            reportsHtml += `
                    </div>
                </div>
            `;
            
            tableContainer.innerHTML = reportsHtml;
        }

        function loadTodaysReports() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('viewDate').value = today;
            loadPreviousReports();
        }
    </script>
</body>
</html>
