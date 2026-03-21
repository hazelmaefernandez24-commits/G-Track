<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Student Task - PN Systems</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --header-height: 96px;
            --sidebar-width: 300px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            min-height: 100vh;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background: linear-gradient(135deg, #22BBEA 0%, #1a9bcf 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            height: var(--header-height);
            display: flex;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .container-fluid {
            padding: 0;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 36px 40px;
            background: linear-gradient(180deg, #eef2ff 0%, #f8fafc 45%, #fdfdfd 100%);
            min-height: calc(100vh - var(--header-height));
        }

        .schedule-card {
            background: #ffffff;
            border-radius: 28px;
            padding: 28px;
            box-shadow: 0 30px 70px rgba(15,23,42,0.08);
            border: 1px solid #e2e8f0;
            margin: 0 auto 30px auto;
            max-width: 1180px;
        }

        .schedule-card-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 22px;
        }

        .schedule-card-top h3 {
            margin: 0;
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
        }

        .schedule-count {
            font-size: 0.9rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        .subarea-pill {
            padding: 10px 18px;
            border-radius: 999px;
            background: #ecfeff;
            color: #0e7490;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid #bae6fd;
            white-space: nowrap;
        }

        .schedule-meta-grid {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 18px;
        }

        .meta-card {
            border-radius: 20px;
            padding: 18px 20px;
            border: 1px solid rgba(148,163,184,0.25);
            background: #f8fafc;
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 120px;
        }

        .meta-card.highlight {
            background: linear-gradient(135deg, rgba(34,187,234,0.15), rgba(99,102,241,0.12));
            border: 1px solid rgba(99,102,241,0.3);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.6);
        }

        .meta-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: #0f172a;
            box-shadow: 0 10px 30px rgba(15,23,42,0.15);
        }

        .meta-title {
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: #64748b;
        }

        .meta-value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
        }

        .meta-support {
            font-size: 0.85rem;
            color: #94a3b8;
        }

        .filter-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(15,23,42,0.05);
        }

        .filter-card input[type="date"] {
            border-radius: 12px;
            border: 1px solid #cbd5f5;
            padding: 10px 12px;
            font-size: 0.95rem;
            color: #0f172a;
        }

        .filter-card input[type="date"]:focus {
            outline: none;
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99,102,241,0.2);
        }

        .filter-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 12px;
        }

        .apply-filter-btn {
            background: linear-gradient(135deg, #2563eb, #9333ea);
            color: #fff;
            border: none;
            padding: 10px 16px;
            border-radius: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 12px 25px rgba(79,70,229,0.25);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .apply-filter-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 15px 32px rgba(79,70,229,0.35);
        }

        .clear-filter-btn {
            background: transparent;
            border: none;
            color: #475569;
            font-weight: 600;
            padding: 0 6px;
            text-decoration: underline;
        }

        .filter-hint {
            font-size: 0.8rem;
            color: #94a3b8;
        }

        .schedule-table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .schedule-table thead {
            background: #f8fafc;
            color: #475569;
        }

        .schedule-table thead th {
            font-size: 0.8rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            padding: 14px 16px;
        }

        .schedule-table tbody td {
            padding: 16px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }

        .table-shell {
            margin-top: 22px;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 40px rgba(15,23,42,0.06);
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border-radius: 999px;
            padding: 6px 14px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .status-pill.incomplete {
            background: #fff7ed;
            color: #c2410c;
        }

        .status-pill.incomplete .dot { background: #fb923c; }

        .status-pill.completed {
            background: #dcfce7;
            color: #15803d;
        }

        .status-pill.completed .dot { background: #22c55e; }

        
        /* Modal Styles */
        .task-modal {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 25px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 25px 50px rgba(31, 38, 135, 0.4);
        }
        
        .modal-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 25px 25px 0 0;
            padding: 25px;
            border: none;
        }
        
        .modal-body {
            padding: 30px;
        }
        
        .modal-footer {
            border: none;
            padding: 20px 30px;
        }
        
        .btn-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 12px 25px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .status-note {
            font-size: 0.75rem;
            color: #94a3b8;
            margin-top: 4px;
            display: block;
        }
        
        .task-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-top: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            font-size: 0.9rem;
        }
        
        .detail-item i {
            margin-right: 8px;
            color: #22BBEA;
        }
        
        .no-tasks {
            text-align: center;
            padding: 60px 20px;
            color: white;
        }
        
        .no-tasks i {
            font-size: 4rem;
            opacity: 0.7;
            margin-bottom: 20px;
        }
        
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px;
            color: white;
        }
        
        .day-selector {
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid rgba(255,255,255,0.2);
        }
        
        .day-btn {
            width: 55px;
            height: 55px;
            border-radius: 50%;
            border: 2px solid #e3f2fd;
            background: linear-gradient(145deg, #ffffff 0%, #f8f9fa 100%);
            color: #666;
            font-weight: 700;
            transition: all 0.4s ease;
            margin: 0 8px;
            position: relative;
            overflow: hidden;
        }
        
        .day-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .day-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .day-btn:hover:not(.active) {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.2);
            border-color: #667eea;
            color: #667eea;
        }

        /* Notification animations */
        @keyframes slideIn {
            from {
                transform: translateX(400px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(400px);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="container-fluid">
            <div class="d-flex align-items-center">
                <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo" style="height: 50px; margin-left: 20px;">
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <div class="row">
            @include('partials.sidebar')
            
            <main class="main-content">
                

                <!-- Tasks Container -->
                <div id="tasksContainer">
                    <!-- Loading state -->
                    <div class="loading-spinner" id="loadingSpinner">
                        <div class="text-center">
                            <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-3 mb-0">Loading your tasks...</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Task Detail Modal -->
    <div class="modal fade" id="taskDetailModal" tabindex="-1" aria-labelledby="taskDetailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content task-modal">
                <div class="modal-header">
                    <h5 class="modal-title" id="taskDetailModalLabel">
                        <i class="bi bi-list-task me-2"></i>Task Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalTaskContent">
                        <!-- Task details will be loaded here -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modern" data-bs-dismiss="modal">
                        <i class="bi bi-check-circle me-2"></i>Got it!
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        const DEFAULT_TASK_DATE = '{{ date("Y-m-d") }}';
        let cachedSchedules = [];
        let currentDateFilter = '';
        let dateFilterDelegatesAttached = false;

        document.addEventListener('DOMContentLoaded', function() {
            initializeDaySelection();
            ensureDateFilterDelegates();
            loadMyTasks('wednesday'); // Load default day
        });

        function initializeDaySelection() {
            const dayButtons = document.querySelectorAll('.day-btn');
            const dayLabel = document.getElementById('selectedDayLabel');
            
            dayButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    dayButtons.forEach(btn => {
                        btn.classList.remove('active');
                    });
                    
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Update day label
                    const dayMap = {
                        'monday': 'Monday',
                        'tuesday': 'Tuesday', 
                        'wednesday': 'Wednesday',
                        'thursday': 'Thursday',
                        'friday': 'Friday',
                        'saturday': 'Saturday',
                        'sunday': 'Sunday'
                    };
                    
                    const selectedDay = this.getAttribute('data-day');
                    if (dayLabel) {
                        dayLabel.textContent = dayMap[selectedDay];
                    }
                    
                    // Load tasks for selected day
                    loadMyTasks(selectedDay);
                    
                    console.log('Day selected:', selectedDay);
                });
            });

        }

        function getStatusMeta(status) {
            const normalized = (status || 'pending').toLowerCase();
            if (['completed', 'complete', 'done'].includes(normalized)) {
                return { label: 'Completed', className: 'completed' };
            }
            // Treat any non-completed status as incomplete
            return { label: 'Incomplete', className: 'incomplete' };
        }

        function ensureDateFilterDelegates() {
            if (dateFilterDelegatesAttached) return;

            const tasksContainer = document.getElementById('tasksContainer');
            if (!tasksContainer) return;

            tasksContainer.addEventListener('click', function(event) {
                const button = event.target.closest('[data-role="applyDateFilter"]');
                if (button) {
                    const card = button.closest('[data-batch]');
                    const dateInput = card?.querySelector('[data-role="taskDate"]');
                    handleApplyDateFilter(dateInput?.value?.trim());
                    return;
                }

                const clearBtn = event.target.closest('[data-role="clearDateFilter"]');
                if (clearBtn) {
                    currentDateFilter = '';
                    const card = clearBtn.closest('[data-batch]');
                    const dateInput = card?.querySelector('[data-role="taskDate"]');
                    if (dateInput) {
                        const defaultDate = dateInput.getAttribute('min') || DEFAULT_TASK_DATE;
                        dateInput.value = defaultDate;
                    }
                    displayGeneratedSchedules(cachedSchedules, {
                        filterDate: '',
                        dateInputValue: dateInput?.value || DEFAULT_TASK_DATE
                    });
                }
            });

            tasksContainer.addEventListener('keyup', function(event) {
                if (event.key !== 'Enter') return;
                if (!event.target.matches('[data-role="taskDate"]')) return;
                handleApplyDateFilter(event.target.value?.trim());
            });

            dateFilterDelegatesAttached = true;
        }

        function handleApplyDateFilter(selectedDate) {
            if (!cachedSchedules.length) {
                return;
            }

            currentDateFilter = selectedDate || '';

            displayGeneratedSchedules(cachedSchedules, {
                filterDate: currentDateFilter,
                dateInputValue: selectedDate || DEFAULT_TASK_DATE
            });
        }

        async function loadMyTasks(day) {
            const tasksContainer = document.getElementById('tasksContainer');
            const loadingSpinner = document.getElementById('loadingSpinner');
            const selectedDate = currentDateFilter || DEFAULT_TASK_DATE;
            
            // Show loading
            tasksContainer.innerHTML = `
                <div class="loading-spinner">
                    <div class="text-center">
                        <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 mb-0">Loading your tasks for ${day}...</p>
                    </div>
                </div>
            `;

            try {
                // First, try to fetch generated schedules from admin
                let generatedSchedules = [];
                try {
                    const scheduleResponse = await fetch(`/api/student-generated-schedules`, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        }
                    });

                    if (scheduleResponse.ok) {
                        const scheduleData = await scheduleResponse.json();
                        console.log('API Response:', scheduleData);
                        if (scheduleData.success && scheduleData.schedules) {
                            generatedSchedules = scheduleData.schedules;
                            console.log('Loaded generated schedules:', generatedSchedules);
                            console.log('Schedules count:', generatedSchedules.length);
                        }
                    }
                } catch (scheduleError) {
                    console.log('No generated schedules available yet');
                }

                // If we have generated schedules, display them; otherwise show empty state (no legacy fallback)
                if (generatedSchedules.length > 0) {
                    cachedSchedules = generatedSchedules;
                    displayGeneratedSchedules(generatedSchedules, { 
                        filterDate: currentDateFilter,
                        dateInputValue: currentDateFilter || selectedDate
                    });
                    return;
                } else {
                    cachedSchedules = [];
                    showNoGeneratedSchedule();
                    return;
                }
                
            } catch (error) {
                console.error('Error loading my tasks:', error);
                displayError('Error loading your tasks: ' + error.message);
            }
        }

        // Empty state for when no generated schedules exist yet
        function showNoGeneratedSchedule() {
            const tasksContainer = document.getElementById('tasksContainer');
            tasksContainer.innerHTML = `
                <div class="text-center py-5">
                    <div style="font-size: 3rem; color: #a0aec0; margin-bottom: 1rem;">
                        <i class="bi bi-calendar2-week"></i>
                    </div>
                    <h4 style="color: #4a5568; margin-bottom: 0.5rem;">No Generated Schedule Yet</h4>
                    <p style="color: #718096;">Your admin hasn't published a schedule for you yet. Please check back later.</p>
                </div>
            `;
        }

        function displayGeneratedSchedules(schedules, options = {}) {
            const tasksContainer = document.getElementById('tasksContainer');
            const {
                filterDate = currentDateFilter,
                dateInputValue = currentDateFilter || DEFAULT_TASK_DATE
            } = options;
            const normalizedFilterDate = filterDate?.trim() || '';
            
            // DEBUG: Log what we received
            console.log('displayGeneratedSchedules called with:', {
                schedules: schedules,
                schedulesCount: schedules ? schedules.length : 0,
                selectedDate: normalizedFilterDate
            });
            
            if (!schedules || schedules.length === 0) {
                console.log('No schedules found, showing empty state');
                showNoGeneratedSchedule();
                return;
            }

            // Deduplicate schedules by date/title/description/category to avoid repeats
            const deduped = [];
            const seen = new Set();
            schedules.forEach(schedule => {
                const key = [
                    schedule.schedule_date,
                    schedule.task_title,
                    schedule.task_description,
                    schedule.category_name
                ].join('|');
                if (!seen.has(key)) {
                    seen.add(key);
                    deduped.push(schedule);
                }
            });

            // Group schedules by batch (e.g., 2025, 2026)
            const schedulesByBatch = {};
            deduped.forEach(schedule => {
                const batch = schedule.batch || 'Unknown';
                if (!schedulesByBatch[batch]) {
                    schedulesByBatch[batch] = [];
                }
                schedulesByBatch[batch].push(schedule);
            });

            let html = '';

            Object.keys(schedulesByBatch).sort().forEach(batch => {
                const batchSchedules = schedulesByBatch[batch];

                // Sort by date
                batchSchedules.sort((a, b) => new Date(a.schedule_date) - new Date(b.schedule_date));

                // Group by date so we can create a rowspan like in the admin schedule tables
                const byDate = {};
                batchSchedules.forEach(s => {
                    const d = s.schedule_date;
                    if (!byDate[d]) byDate[d] = [];
                    byDate[d].push(s);
                });

                const subAreas = [...new Set(batchSchedules.map(s => s.category_name).filter(Boolean))];
                const subAreaLabel = subAreas.length ? `Sub Area${subAreas.length > 1 ? 's' : ''}: ${subAreas.join(', ')}` : 'Sub Area: General Task';

                const sortedDates = Object.keys(byDate).sort((a, b) => new Date(a) - new Date(b));
                const scheduleStartDate = sortedDates[0] || '';
                const scheduleEndDate = sortedDates[sortedDates.length - 1] || '';
                const scheduleRangeLabel = scheduleStartDate
                    ? formatDateRange(scheduleStartDate, scheduleEndDate)
                    : 'No schedule published yet';
                const filterValue = dateInputValue || scheduleStartDate;

                html += `
                    <div class="schedule-card" data-batch="${batch}">
                        <div class="schedule-card-top">
                            <div>
                                <h3>Class ${batch} Schedule</h3>
                            </div>
                            <span class="subarea-pill">${subAreaLabel}</span>
                        </div>
                        <div class="schedule-meta-grid">
                            <div class="meta-card highlight">
                                <div class="meta-icon">
                                    <i class="bi bi-calendar-week"></i>
                                </div>
                                <div>
                                    <div class="meta-title">Generated Schedule</div>
                                    <div class="meta-value">${scheduleRangeLabel}</div>
                                    <div class="meta-support">${scheduleStartDate ? `Covering ${sortedDates.length} day${sortedDates.length === 1 ? '' : 's'}` : 'Waiting for educator to publish schedule'}</div>
                                </div>
                            </div>
                            <div class="meta-card filter-card">
                                <div class="d-flex align-items-center justify-content-between">
                                    <span class="meta-title">Filter by Date</span>
                                    ${scheduleStartDate ? `<span class="meta-support">${formatDateRangeShort(scheduleStartDate, scheduleEndDate)}</span>` : ''}
                                </div>
                                <input 
                                    type="date"
                                    class="form-control"
                                    data-role="taskDate"
                                    value="${filterValue || ''}"
                                    ${scheduleStartDate ? `min="${scheduleStartDate}"` : ''}
                                    ${scheduleEndDate ? `max="${scheduleEndDate}"` : ''}
                                >
                                <div class="filter-actions">
                                    <button type="button" class="apply-filter-btn" data-role="applyDateFilter">
                                        <i class="bi bi-funnel"></i>
                                        Apply Filter
                                    </button>
                                    ${normalizedFilterDate ? `<button type="button" class="clear-filter-btn" data-role="clearDateFilter">Clear</button>` : ''}
                                </div>
                                <span class="filter-hint">${scheduleStartDate ? 'Pick a date within the generated schedule period.' : 'Schedule range unavailable yet.'}</span>
                                <span class="filter-hint status-note">Status is updated by inspectors in the evaluation board</span>
                            </div>
                        </div>
                        <div class="table-shell">
                            <div class="table-responsive">
                                <table class="table schedule-table">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Task Title</th>
                                        <th>Description</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                `;

                const filteredDates = normalizedFilterDate ? sortedDates.filter(date => date === normalizedFilterDate) : sortedDates;
                let renderedRows = 0;

                filteredDates.forEach(dateStr => {
                    const rows = byDate[dateStr];
                    const dateObj = new Date(dateStr);
                    const displayDate = dateObj.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    rows.forEach((row, index) => {
                        html += '<tr>';
                        if (index === 0) {
                            html += `<td rowspan="${rows.length}">${displayDate}</td>`;
                        }
                        html += `<td>${row.task_title || 'Task'}</td>`;
                        html += `<td>${row.task_description || 'Task assigned from generated schedule'}</td>`;
                        html += `<td>
                            <span class="status-pill ${getStatusMeta(row.task_status).className}">
                                <span class="dot"></span>${getStatusMeta(row.task_status).label}
                            </span>
                        </td>`;
                        html += '</tr>';
                        renderedRows++;
                    });
                });

                if (!renderedRows) {
                    const friendlyDate = formatDateForDisplay(normalizedFilterDate);
                    html += `
                        <tr>
                            <td colspan="4" class="text-center py-4">
                                No tasks scheduled for ${friendlyDate || 'the selected date'}.
                            </td>
                        </tr>
                    `;
                }

                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>
                `;
            });
            tasksContainer.innerHTML = html;
        }

        function formatDateForDisplay(dateStr) {
            if (!dateStr) return '';
            const dateObj = new Date(dateStr);
            if (Number.isNaN(dateObj.getTime())) {
                return dateStr;
            }
            return dateObj.toLocaleDateString('en-US', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        function formatDateRange(startDate, endDate) {
            if (!startDate && !endDate) {
                return 'No schedule yet';
            }

            const prettyStart = formatDateForDisplay(startDate)?.replace(/^[A-Za-z]+,\s*/, '') || 'TBD';
            const prettyEnd = formatDateForDisplay(endDate)?.replace(/^[A-Za-z]+,\s*/, '') || prettyStart;

            if (startDate === endDate) {
                return `${prettyStart}`;
            }

            return `${prettyStart} to ${prettyEnd}`;
        }

        function formatDateRangeShort(startDate, endDate) {
            if (!startDate && !endDate) return '';
            const shortFormat = dateStr => {
                if (!dateStr) return '';
                const dateObj = new Date(dateStr);
                if (Number.isNaN(dateObj.getTime())) return dateStr;
                return dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
            };

            const startLabel = shortFormat(startDate);
            const endLabel = shortFormat(endDate);
            if (!startLabel && !endLabel) return '';
            if (startLabel === endLabel || !endLabel) return startLabel;
            return `${startLabel} – ${endLabel}`;
        }

        function displayMyTasks(tasks) {
            const tasksContainer = document.getElementById('tasksContainer');
            
            if (!tasks || tasks.length === 0) {
                tasksContainer.innerHTML = `
                    <div class="text-center py-5">
                        <div style="font-size: 4rem; color: #cbd5e0; margin-bottom: 1rem;">
                            <i class="bi bi-calendar-x"></i>
                        </div>
                        <h4 style="color: #4a5568; margin-bottom: 1rem;">No Tasks Assigned</h4>
                        <p style="color: #718096;">You don't have any tasks assigned for this day.</p>
                        <small style="color: #a0aec0;">Check with your coordinator or admin for task assignments.</small>
                    </div>
                `;
                return;
            }

            let tasksHTML = '<div class="task-list">';
            tasks.forEach((task, index) => {
                const icons = ['🏢', '🍳', '🧹', '📋', '🔧', '📊'];
                const taskIcon = icons[index % icons.length];
                
                tasksHTML += `
                    <div class="task-card" onclick="showTaskDetails(${JSON.stringify(task).replace(/"/g, '&quot;')})">
                        <div class="task-content">
                            <div class="task-icon">${taskIcon}</div>
                            <div class="task-info">
                                <div class="task-title">${task.category_name || 'General Task'}</div>
                                <div class="task-subtitle">${task.area_name || 'General Area'}</div>
                                <div class="task-description">${task.task_description || 'Complete assigned duties for this area'}</div>
                            </div>
                            <div class="task-meta">
                                <div class="task-time">
                                    <i class="bi bi-clock me-1"></i>
                                    ${task.start_time || '08:00'} - ${task.end_time || '17:00'}
                                </div>
                                <div class="task-location">
                                    <i class="bi bi-geo-alt"></i>
                                    ${task.location || task.category_name || 'General Area'}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            tasksHTML += '</div>';
            
            tasksContainer.innerHTML = tasksHTML;
        }

        function showTaskDetails(task) {
            const modalContent = document.getElementById('modalTaskContent');
            modalContent.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Task Information</h6>
                        <div class="mb-3">
                            <strong>Category:</strong> ${task.category_name || 'General Task'}
                        </div>
                        <div class="mb-3">
                            <strong>Area:</strong> ${task.area_name || 'General Area'}
                        </div>
                        <div class="mb-3">
                            <strong>Location:</strong> ${task.location || 'Not specified'}
                        </div>
                        <div class="mb-3">
                            <strong>Status:</strong> 
                            <span class="badge bg-success">${task.status || 'Assigned'}</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted mb-2">Schedule</h6>
                        <div class="mb-3">
                            <strong>Time:</strong> ${task.start_time || '08:00'} - ${task.end_time || '17:00'}
                        </div>
                        <div class="mb-3">
                            <strong>Date:</strong> ${task.assigned_date || 'Today'}
                        </div>
                        <div class="mb-3">
                            <strong>Team:</strong> ${task.team_members || 'Individual task'}
                        </div>
                    </div>
                </div>
                <div class="mt-4">
                    <h6 class="text-muted mb-2">Task Description</h6>
                    <p class="mb-0">${task.task_description || 'Complete assigned duties for this area'}</p>
                </div>
                ${task.notes ? `
                    <div class="mt-4">
                        <h6 class="text-muted mb-2">Additional Notes</h6>
                        <p class="mb-0">${task.notes}</p>
                    </div>
                ` : ''}
            `;
            
            const modal = new bootstrap.Modal(document.getElementById('taskDetailModal'));
            modal.show();
        }

        function displayError(message) {
            const tasksContainer = document.getElementById('tasksContainer');
            tasksContainer.innerHTML = `
                <div class="text-center py-5">
                    <div style="font-size: 4rem; color: #f56565; margin-bottom: 1rem;">
                        <i class="bi bi-exclamation-triangle"></i>
                    </div>
                    <h4 style="color: #e53e3e; margin-bottom: 1rem;">Error Loading Tasks</h4>
                    <p style="color: #718096;">${message}</p>
                    <button class="btn btn-modern mt-3" onclick="location.reload()">
                        <i class="bi bi-arrow-clockwise me-2"></i>Try Again
                    </button>
                </div>
            `;
        }
    </script>
</body>
</html>
