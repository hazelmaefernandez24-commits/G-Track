@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2 class="mb-0" style="font-weight: 700; color: #1a202c;">
                <i class="bi bi-clipboard-check me-2"></i>Task Assignments Overview
            </h2>
            <p class="text-muted mt-2">156 active assignments across 8 main areas</p>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" class="form-control border-start-0" id="searchAssignments" placeholder="Search assignments...">
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="assignmentsTable">
                        <thead style="background: #f8f9fa; border-bottom: 2px solid #e2e8f0;">
                            <tr>
                                <th style="padding: 16px; font-weight: 600; color: #4a5568;">Student</th>
                                <th style="padding: 16px; font-weight: 600; color: #4a5568;">Main Area</th>
                                <th style="padding: 16px; font-weight: 600; color: #4a5568;">Sub Area</th>
                                <th style="padding: 16px; font-weight: 600; color: #4a5568;">Tasks</th>
                                <th style="padding: 16px; font-weight: 600; color: #4a5568;">Progress</th>
                                <th style="padding: 16px; font-weight: 600; color: #4a5568;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="assignmentsTableBody">
                            <!-- Will be populated by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .student-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .progress-bar-custom {
        height: 6px;
        border-radius: 3px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 3px;
        transition: width 0.3s ease;
    }

    .status-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-active {
        background: #d1fae5;
        color: #065f46;
    }

    .status-in-progress {
        background: #fef3c7;
        color: #92400e;
    }

    .status-completed {
        background: #dbeafe;
        color: #0c4a6e;
    }

    .status-pending {
        background: #fee2e2;
        color: #7f1d1d;
    }

    table tbody tr {
        border-bottom: 1px solid #e2e8f0;
        transition: background-color 0.2s ease;
    }

    table tbody tr:hover {
        background-color: #f8f9fa;
    }

    table tbody tr:last-child {
        border-bottom: none;
    }
</style>

<script>
    // Sample data - in production, this would come from the API
    const assignmentsData = [
        {
            student: 'Emma Rodriguez',
            studentCode: 'STD245',
            mainArea: 'Kitchen',
            subArea: 'Dishwashing',
            tasks: '8/12',
            progress: 67,
            status: 'In Progress',
            statusType: 'in-progress',
            avatar: 'ER'
        },
        {
            student: 'Michael Kim',
            studentCode: 'STD301',
            mainArea: 'Maintenance',
            subArea: 'Electrical',
            tasks: '5/8',
            progress: 63,
            status: 'In Progress',
            statusType: 'in-progress',
            avatar: 'MK'
        },
        {
            student: 'Sarah Johnson',
            studentCode: 'STD156',
            mainArea: 'Kitchen',
            subArea: 'Preparation',
            tasks: '12/12',
            progress: 100,
            status: 'Completed',
            statusType: 'completed',
            avatar: 'SJ'
        },
        {
            student: 'James Wilson',
            studentCode: 'STD289',
            mainArea: 'Grounds',
            subArea: 'Landscaping',
            tasks: '3/10',
            progress: 30,
            status: 'Pending',
            statusType: 'pending',
            avatar: 'JW'
        },
        {
            student: 'Lisa Chen',
            studentCode: 'STD412',
            mainArea: 'Dining',
            subArea: 'Service',
            tasks: '10/12',
            progress: 83,
            status: 'In Progress',
            statusType: 'in-progress',
            avatar: 'LC'
        }
    ];

    function getAvatarColor(index) {
        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
        return colors[index % colors.length];
    }

    function getStatusClass(statusType) {
        const statusMap = {
            'in-progress': 'status-in-progress',
            'completed': 'status-completed',
            'pending': 'status-pending',
            'active': 'status-active'
        };
        return statusMap[statusType] || 'status-pending';
    }

    function renderAssignments(data) {
        const tableBody = document.getElementById('assignmentsTableBody');
        tableBody.innerHTML = '';

        data.forEach((assignment, index) => {
            const row = document.createElement('tr');
            const avatarColor = getAvatarColor(index);
            const statusClass = getStatusClass(assignment.statusType);

            row.innerHTML = `
                <td style="padding: 16px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div class="student-avatar" style="background: ${avatarColor};">
                            ${assignment.avatar}
                        </div>
                        <div>
                            <div style="font-weight: 600; color: #1a202c;">${assignment.student}</div>
                            <div style="font-size: 0.85rem; color: #718096;">${assignment.studentCode}</div>
                        </div>
                    </div>
                </td>
                <td style="padding: 16px; color: #4a5568;">${assignment.mainArea}</td>
                <td style="padding: 16px; color: #4a5568;">${assignment.subArea}</td>
                <td style="padding: 16px; color: #4a5568;">${assignment.tasks}</td>
                <td style="padding: 16px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <div class="progress-bar-custom" style="flex: 1; min-width: 80px;">
                            <div class="progress-fill" style="width: ${assignment.progress}%; background: ${assignment.progress === 100 ? '#10b981' : assignment.progress >= 50 ? '#f59e0b' : '#ef4444'};"></div>
                        </div>
                        <span style="font-size: 0.85rem; color: #718096; min-width: 35px;">${assignment.progress}%</span>
                    </div>
                </td>
                <td style="padding: 16px;">
                    <span class="status-badge ${statusClass}">${assignment.status}</span>
                </td>
            `;

            tableBody.appendChild(row);
        });
    }

    function filterAssignments() {
        const searchTerm = document.getElementById('searchAssignments').value.toLowerCase();
        const filtered = assignmentsData.filter(assignment => 
            assignment.student.toLowerCase().includes(searchTerm) ||
            assignment.mainArea.toLowerCase().includes(searchTerm) ||
            assignment.subArea.toLowerCase().includes(searchTerm) ||
            assignment.studentCode.toLowerCase().includes(searchTerm)
        );
        renderAssignments(filtered);
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        renderAssignments(assignmentsData);

        // Add search functionality
        document.getElementById('searchAssignments').addEventListener('keyup', filterAssignments);
    });

    // Fetch real data from API
    function fetchTaskAssignments() {
        fetch('/api/task-assignments-overview', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success && data.assignments) {
                    renderAssignments(data.assignments);
                    // Update the total count
                    document.querySelector('.text-muted').textContent = `${data.total} active task submissions`;
                }
            })
            .catch(error => console.error('Error fetching assignments:', error));
    }

    // Fetch real data on page load
    window.addEventListener('load', function() {
        fetchTaskAssignments();
        // Refresh every 30 seconds
        setInterval(fetchTaskAssignments, 30000);
    });
</script>
@endsection
