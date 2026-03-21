<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks - PN Systems</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .main-container {
            padding: 20px;
            max-width: 1400px;
            margin: 0 auto;
        }
        
        .header-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 25px;
            margin-bottom: 25px;
        }
        
        .task-modal {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            padding: 0;
            border: none;
        }
        
        .modal-header-custom {
            background: linear-gradient(135deg, #22BBEA 0%, #1a9bcf 100%);
            color: white;
            border-bottom: none;
            border-radius: 15px 15px 0 0;
            padding: 25px;
        }
        
        .day-btn {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            font-weight: bold;
            margin: 0 5px;
            transition: all 0.3s ease;
        }
        
        .day-btn.active {
            background: #22BBEA !important;
            border-color: #22BBEA !important;
            color: white !important;
        }
        
        .task-table {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.1);
        }
        
        .task-table thead {
            background: #22BBEA;
            color: white;
        }
        
        .task-table th {
            border: none;
            padding: 15px;
            font-weight: 600;
            text-align: center;
            vertical-align: middle;
        }
        
        .task-table td {
            border: 1px solid #e9ecef;
            padding: 12px 15px;
            text-align: center;
            vertical-align: middle;
        }
        
        .task-table tbody tr:hover {
            background-color: #f8f9fa;
        }
        
        .btn-custom {
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .schedule-controls {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            padding: 25px;
            margin: 20px 0;
        }
        
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            border: 2px solid #e9ecef;
            padding: 10px 15px;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #22BBEA;
            box-shadow: 0 0 0 0.2rem rgba(34, 187, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="main-container">
        <!-- Header -->
        <div class="header-card">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <h2 class="mb-1" style="color: #333; font-weight: bold;">
                        <i class="bi bi-list-task me-3" style="color: #22BBEA;"></i>
                        Task Management System
                    </h2>
                    <p class="text-muted mb-0">Assign and manage tasks for students efficiently</p>
                </div>
                <div class="d-flex gap-3">
                    <button class="btn btn-outline-primary btn-custom" onclick="goBack()">
                        <i class="bi bi-arrow-left me-2"></i>Back to General Tasks
                    </button>
                    <button class="btn btn-primary btn-custom" onclick="openTaskModal()">
                        <i class="bi bi-plus-circle me-2"></i>Manage Tasks
                    </button>
                </div>
            </div>
        </div>

        <!-- Task Management Modal Content (Always Visible for Testing) -->
        <div class="task-modal">
            <!-- Modal Header -->
            <div class="modal-header-custom d-flex align-items-center justify-content-between">
                <h5 class="mb-0">Manage Tasks</h5>
                <span id="categoryName" class="badge bg-light text-dark">General</span>
            </div>

            <!-- Modal Body -->
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <button id="btnOpenAddTask" type="button" class="btn btn-primary btn-custom">
                            <i class="bi bi-plus-circle me-1"></i> Add Task
                        </button>
                    </div>
                </div>

                <!-- Inline Add Task Form (hidden by default) -->
                <div id="addTaskInlineForm" class="card p-3 mb-3" style="max-width:720px; display:none;">
                    <div class="mb-2">
                        <label class="form-label mb-1">Task Description</label>
                        <textarea id="newTaskDescriptionInput" class="form-control" rows="2" placeholder="Enter task description"></textarea>
                    </div>
                    <div class="d-flex justify-content-end gap-2 mt-2">
                        <button type="button" class="btn btn-secondary" id="btnCancelTaskInline">Cancel</button>
                        <button type="button" class="btn btn-primary" id="btnSaveTaskInline">Add Task</button>
                    </div>
                </div>

                <!-- Task Table -->
                <div class="task-table table-responsive">
                    <table class="table table-bordered mb-0" id="taskTable">
                        <thead class="text-white" style="background:#22BBEA;">
                            <tr>
                                <th style="width:18%;">Assigned To</th>
                                <th style="width:18%;">Task Area</th>
                                <th style="width:52%;">Task Description</th>
                                <th style="width:12%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="taskTableBody">
                            <tr class="text-muted">
                                <td colspan="4" class="text-center py-4">
                                    <i class="bi bi-info-circle me-2"></i>No tasks yet. Click "Add Task" to create one.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

                <!-- Inline handlers below handle adding tasks; removed separate add-task modal to keep interface compact -->
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Day selection functionality
        document.addEventListener('DOMContentLoaded', function() {
            initializeDaySelection();
            console.log('Manage Tasks page loaded successfully!');
        });

        function initializeDaySelection() {
            const dayButtons = document.querySelectorAll('.day-btn');
            const dayLabel = document.getElementById('selectedDayLabel');
            
            dayButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    dayButtons.forEach(btn => {
                        btn.classList.remove('btn-info', 'active');
                        btn.classList.add('btn-outline-info');
                    });
                    
                    // Add active class to clicked button
                    this.classList.remove('btn-outline-info');
                    this.classList.add('btn-info', 'active');
                    
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
                    dayLabel.textContent = dayMap[selectedDay];
                    
                    // Load tasks for selected day
                    loadTasksForDay(selectedDay);
                    
                    console.log('Day selected:', selectedDay);
                });
            });
        }

        function loadTasksForDay(day) {
            console.log('Loading tasks for:', day);
            // Here you would typically fetch tasks from your API
            // For now, we'll just show a message
            alert('Loading tasks for ' + day + '...');
        }

        function editTask(button) {
            const row = button.closest('tr');
            const currentAssigned = row.cells[0].textContent.trim();
            const currentArea = row.cells[1].textContent.trim();
            const currentDesc = row.cells[2].textContent.trim();

            const newAssigned = prompt('Assigned To (enter name or leave as None Assigned):', currentAssigned) || 'None Assigned';
            const newDesc = prompt('Task Description (leave blank for no default):', currentDesc) || '';

            row.cells[0].textContent = newAssigned;
            // Keep Task Area in sync with modal category
            const modalCategory = document.getElementById('categoryName')?.textContent?.trim();
            if (modalCategory) row.cells[1].textContent = modalCategory;
            row.cells[2].textContent = newDesc;

            alert('Task updated');
            console.log('Task edited for row:', { assigned: newAssigned, area: row.cells[1].textContent, desc: newDesc });
        }

        function deleteTask(button) {
            const row = button.closest('tr');
            const studentName = row.cells[0].textContent;

            if (confirm('Are you sure you want to delete the task for ' + studentName + '?')) {
                row.remove();
                // If table becomes empty, show placeholder row again
                const tbody = document.getElementById('taskTableBody');
                if (tbody && tbody.children.length === 0) {
                    const placeholder = document.createElement('tr');
                    placeholder.innerHTML = `
                        <td colspan="4" class="text-center text-muted py-4">
                            <i class="bi bi-info-circle" style="font-size: 1.5rem; opacity: 0.6;"></i>
                            <p class="mt-2 mb-0">No tasks for this category yet. Click <strong>Add Task</strong> to create one.</p>
                        </td>
                    `;
                    tbody.appendChild(placeholder);
                }

                alert('Task deleted for ' + studentName);
                console.log('Task deleted for:', studentName);
            }
        }

        function addNewTask() {
            // Open a small input form (prompt) to enter the task description
            const taskDesc = prompt('Enter the Task Description for this category:');
            if (taskDesc === null) {
                // User cancelled
                return;
            }

            const tbody = document.getElementById('taskTableBody');
            if (!tbody) return;

            // Remove placeholder row if present
            if (tbody.children.length === 1 && tbody.children[0].querySelector('.bi-info-circle')) {
                tbody.innerHTML = '';
            }

            const currentCategoryName = document.getElementById('categoryName')?.textContent?.trim() || 'General';

            const tr = document.createElement('tr');
            tr.style.transition = 'background-color 0.3s';
            tr.innerHTML = `
                <td style="padding: 12px 15px; text-align: center;">None Assigned</td>
                <td style="padding: 12px 15px; text-align: center;">${currentCategoryName}</td>
                <td style="padding: 12px 15px; text-align: center;">${escapeHtml(taskDesc)}</td>
                <td style="padding: 12px 15px; text-align: center;">
                    <button class="btn btn-sm btn-primary me-1" onclick="editTask(this)">
                        <i class="bi bi-pencil"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteTask(this)">
                        <i class="bi bi-trash"></i> Remove
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            alert('New task added');
            console.log('Added new task for category:', currentCategoryName, 'desc:', taskDesc);
        }

        // Simple HTML escape to avoid injecting markup via prompt
        function escapeHtml(unsafe) {
            return String(unsafe)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function markDayComplete() {
            const selectedDay = document.getElementById('selectedDayLabel').textContent;
            alert('Day marked as completed: ' + selectedDay);
            console.log('Mark day complete clicked for:', selectedDay);
        }

        function applySchedule() {
            const scheduleMode = document.getElementById('scheduleModeSelect').value;
            const startDate = document.getElementById('startDateInput').value;
            const endDate = document.getElementById('endDateInput').value;
            const rotationFreq = document.getElementById('rotationFrequencySelect').value;
            
            alert('Schedule Applied!\n' +
                  'Mode: ' + scheduleMode + '\n' +
                  'Start: ' + startDate + '\n' +
                  'End: ' + endDate + '\n' +
                  'Frequency: ' + rotationFreq);
            
            console.log('Apply schedule clicked:', {
                mode: scheduleMode,
                startDate: startDate,
                endDate: endDate,
                frequency: rotationFreq
            });
        }

        function openTaskModal() {
            alert('Task modal is already open! This is the manage tasks interface.');
        }

        function goBack() {
            if (confirm('Are you sure you want to go back to General Tasks?')) {
                // In a real application, you would navigate back
                window.history.back();
            }
        }

        // Month and week selection
        document.getElementById('monthSelect').addEventListener('change', function() {
            console.log('Month changed to:', this.value);
        });

        document.getElementById('weekSelect').addEventListener('change', function() {
            console.log('Week changed to:', this.value);
        });

        document.getElementById('dateInput').addEventListener('change', function() {
            console.log('Date changed to:', this.value);
        });

        // Inline Add Task form handlers (keeps form inside modal; avoids overlapping stacked modals)
        const btnOpenAddTask = document.getElementById('btnOpenAddTask');
        const addTaskInlineForm = document.getElementById('addTaskInlineForm');
        const btnSaveTaskInline = document.getElementById('btnSaveTaskInline');
        const btnCancelTaskInline = document.getElementById('btnCancelTaskInline');

        if (btnOpenAddTask && addTaskInlineForm) {
            btnOpenAddTask.addEventListener('click', function() {
                // toggle inline form
                if (addTaskInlineForm.style.display === 'none' || addTaskInlineForm.style.display === '') {
                    addTaskInlineForm.style.display = 'block';
                    // focus textarea
                    const ta = document.getElementById('newTaskDescriptionInput');
                    if (ta) ta.focus();
                } else {
                    addTaskInlineForm.style.display = 'none';
                }
            });
        }

        if (btnCancelTaskInline && addTaskInlineForm) {
            btnCancelTaskInline.addEventListener('click', function() {
                // hide and clear
                addTaskInlineForm.style.display = 'none';
                const ta = document.getElementById('newTaskDescriptionInput'); if (ta) ta.value = '';
            });
        }

        if (btnSaveTaskInline) {
            btnSaveTaskInline.addEventListener('click', function() {
                const descEl = document.getElementById('newTaskDescriptionInput');
                const desc = descEl ? descEl.value.trim() : '';
                if (!desc) { alert('Please enter a task description'); if(descEl) descEl.focus(); return; }

                const tbody = document.getElementById('taskTableBody');
                if (!tbody) return;

                // Remove placeholder if present (placeholder uses a bi-info-circle icon cell)
                if (tbody.children.length === 1 && tbody.children[0].querySelector('.bi-info-circle')) {
                    tbody.innerHTML = '';
                }

                const currentCategoryName = document.getElementById('categoryName')?.textContent?.trim() || 'General';

                const tr = document.createElement('tr');
                tr.style.transition = 'background-color 0.25s ease';
                tr.innerHTML = `
                    <td style="padding: 12px 15px; text-align: center;">${escapeHtml('None Assigned')}</td>
                    <td style="padding: 12px 15px; text-align: center;">${escapeHtml(currentCategoryName)}</td>
                    <td style="padding: 12px 15px; text-align: center;">${escapeHtml(desc)}</td>
                    <td style="padding: 12px 15px; text-align: center;"></td>
                    <td style="padding: 12px 15px; text-align: center;">
                        <button class="btn btn-sm btn-primary me-1" onclick="editTask(this)">
                            <i class="bi bi-pencil"></i> Edit
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteTask(this)">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </td>
                `;
                tbody.appendChild(tr);

                // hide inline form and clear
                addTaskInlineForm.style.display = 'none';
                if (descEl) descEl.value = '';

                // small visual cue
                tr.style.backgroundColor = '#e9f7ef';
                setTimeout(()=> tr.style.backgroundColor = '', 500);

                alert('Task added');
            });
        }
    </script>
</body>
</html>
