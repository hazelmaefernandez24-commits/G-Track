@extends('layouts.app')

@section('title', '')
@section('subtitle', '')

@push('styles')
<style>
    .activity-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
    }

    .activity-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px;
        flex-wrap: wrap;
        margin-bottom: 24px;
    }

    .activity-title {
        min-width: 0;
    }

    .activity-title h2 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
        min-width: 0;
    }

    .activity-title p {
        font-size: 13px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .controls-group {
        display: flex;
        align-items: center;
        gap: 16px;
        flex-wrap: wrap;
    }

    .student-count-badge {
        white-space: nowrap;
    }

    .filter-select {
        padding: 8px 36px 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 13px;
        color: var(--text-main);
        background-color: #FFFFFF;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'%3E%3C/path%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 14px;
        outline: none;
        cursor: pointer;
        min-width: 140px;
    }

    .student-count-badge {
        padding: 8px 12px;
        background: #F8FAFC;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        color: var(--text-main);
    }

    .activity-pagination {
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 16px;
        color: var(--text-muted);
        font-size: 13px;
    }

    .pagination-button {
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #FFFFFF;
        color: var(--text-main);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .pagination-button:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    /* Table Styles */
    .table-container {
        overflow-x: auto;
    }

    .activity-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
    }

    .activity-table th {
        padding: 12px 16px;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted);
        border-bottom: 2px solid var(--border-color);
        white-space: nowrap;
    }

    .activity-table td {
        padding: 16px;
        font-size: 13px;
        color: var(--text-main);
        border-bottom: 1px solid #F1F5F9;
        vertical-align: middle;
    }

    .activity-table tr:hover {
        background-color: #F8FAFC;
    }

    .status-indicator {
        display: flex;
        align-items: center;
        gap: 6px;
        font-weight: 500;
    }

    .dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }

    .dot.online { background-color: var(--online); }
    .dot.offline { background-color: #94A3B8; }
    .dot.sos { background-color: var(--offline); animation: pulse 1s infinite; }

    .class-badge {
        background: #EFF6FF;
        color: var(--sidebar-active);
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
    }

    @keyframes pulse {
        0% { transform: scale(1); opacity: 1; }
        50% { transform: scale(1.5); opacity: 0.5; }
        100% { transform: scale(1); opacity: 1; }
    }

    .btn {
        padding: 6px 12px;
        border-radius: 6px;
        border: 2px solid transparent;
        font-weight: 600;
        color: #f5f2f0ff;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-size: 11px;
        display: inline-block;
        text-align: center;
    }
    .btn-primary { 
        background: var(--sidebar-bg); 
        
        background-color: #FF9933;


    }
    .btn-primary:hover {
        background-color: #d17314ff;

    }
</style>
@endpush

@section('content')

<div class="activity-card">
    <div class="activity-header">
        <div class="activity-title">
            <h2>
                <i data-lucide="activity" style="width: 20px; height: 20px;"></i>
                Student List
            </h2>
            <p>Real-time student connection status and details</p>
        </div>
        
        <div class="controls-group">
            <input
                id="activity-search"
                class="filter-select"
                type="search"
                placeholder="Search students"
                aria-label="Search students by name or student ID"
                style="min-width: 190px;"
            >
            <button
                id="activity-sort-last-name"
                class="filter-select"
                type="button"
                aria-label="Sort by last name A-Z"
                aria-pressed="false"
            >
                Sort by Last Name: A-Z
            </button>
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="filter" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
                <select id="activity-class-filter" class="filter-select">
                    <option value="all">All Classes</option>
                    @foreach($classes as $c)
                        <option value="{{ $c->name }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="student-count-badge" id="student-count-display">
                0 Students
            </div>
        </div>
    </div>

    <div class="table-container">
        <table class="activity-table">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Last Name</th>
                    <th>First Name</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Last Sync</th>
                    <th>Contact</th>
                    <th>Timeline</th>
                </tr>
            </thead>
            <tbody id="student-table-body">
                <!-- Data populated by JS -->
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted);">Loading student data...</td>
                </tr>
            </tbody>
        </table>
    </div>
    <nav class="activity-pagination" aria-label="Student list pagination">
        <span id="activity-pagination-status" aria-live="polite">Showing 0 students</span>
        <button id="activity-previous-page" class="pagination-button" type="button" hidden>Previous</button>
        <button id="activity-next-page" class="pagination-button" type="button" hidden>Next</button>
    </nav>
</div>

@endsection

@push('scripts')
<script>
    let currentFilter = 'all';
    let currentSearch = '';
    let sortLastNameAscending = true;
    let activityStudents = [];
    let currentPage = 1;
    const studentsPerPage = 10;

    document.getElementById('activity-class-filter').addEventListener('change', function(e) {
        currentFilter = e.target.value;
        currentPage = 1;
        renderActivityTable();
    });

    document.getElementById('activity-search').addEventListener('input', function(e) {
        currentSearch = e.target.value.trim().toLocaleLowerCase();
        currentPage = 1;
        renderActivityTable();
    });

    document.getElementById('activity-sort-last-name').addEventListener('click', function() {
        sortLastNameAscending = !sortLastNameAscending;
        this.textContent = `Sort by Last Name: ${sortLastNameAscending ? 'A-Z' : 'Z-A'}`;
        this.setAttribute('aria-label', `Sort by last name ${sortLastNameAscending ? 'A-Z' : 'Z-A'}`);
        this.setAttribute('aria-pressed', String(!sortLastNameAscending));
        currentPage = 1;
        renderActivityTable();
    });

    document.getElementById('activity-previous-page').addEventListener('click', function() {
        if (currentPage > 1) {
            currentPage--;
            renderActivityTable();
        }
    });

    document.getElementById('activity-next-page').addEventListener('click', function() {
        currentPage++;
        renderActivityTable();
    });

    function buildStatusPill(student) {
        if (student.sos_status === 'help') {
            return `<div class="status-indicator" style="color: #DC2626;"><div class="dot sos"></div> SOS ALERT</div>`;
        } else if (student.status) {
            return `<div class="status-indicator" style="color: #16A34A;"><div class="dot online"></div> Online</div>`;
        } else {
            return `<div class="status-indicator" style="color: #64748B;"><div class="dot offline"></div> Offline</div>`;
        }
    }

    function formatLastSync(lastSyncAt, isOnline) {
        if (isOnline) return 'Active now';
        if (!lastSyncAt) return '—';

        const lastSyncTime = new Date(lastSyncAt).getTime();
        if (Number.isNaN(lastSyncTime)) return '—';

        const elapsedMilliseconds = Math.max(0, Date.now() - lastSyncTime);
        const totalMinutes = Math.floor(elapsedMilliseconds / 60000);
        if (totalMinutes < 60) {
            return `${totalMinutes} minute${totalMinutes === 1 ? '' : 's'} ago`;
        }

        const totalHours = Math.floor(totalMinutes / 60);
        if (totalHours < 24) {
            return `${totalHours} hour${totalHours === 1 ? '' : 's'} ago`;
        }

        const totalDays = Math.floor(totalHours / 24);
        return `${totalDays} day${totalDays === 1 ? '' : 's'} ago`;
    }

    function renderActivityTable() {
        const tbody = document.getElementById('student-table-body');
        const paginationStatus = document.getElementById('activity-pagination-status');
        const previousButton = document.getElementById('activity-previous-page');
        const nextButton = document.getElementById('activity-next-page');

        if (activityStudents.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:32px;color:#64748B;">No student data available.</td></tr>';
            document.getElementById('student-count-display').textContent = '0 Students';
            paginationStatus.textContent = 'Showing 0 students';
            previousButton.hidden = true;
            nextButton.hidden = true;
            currentPage = 1;
            return;
        }

        let filteredStudents = activityStudents.filter(student => {
            const matchesClass = currentFilter === 'all'
                || (student.class && student.class.toString() === currentFilter);
            const searchableText = [
                student.first_name,
                student.last_name,
                student.student_id,
            ].filter(Boolean).join(' ').toLocaleLowerCase();

            return matchesClass && searchableText.includes(currentSearch);
        });

        filteredStudents.sort((a, b) => {
            const lastNameOrder = (a.last_name || '').localeCompare(b.last_name || '', undefined, { sensitivity: 'base' });
            const firstNameOrder = (a.first_name || '').localeCompare(b.first_name || '', undefined, { sensitivity: 'base' });
            const order = lastNameOrder || firstNameOrder;
            return sortLastNameAscending ? order : -order;
        });

        document.getElementById('student-count-display').textContent = `${filteredStudents.length} Students`;

        if (filteredStudents.length === 0) {
            tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;padding:32px;color:#64748B;">No students match the selected filters.</td></tr>';
            paginationStatus.textContent = 'Showing 0 students';
            previousButton.hidden = true;
            nextButton.hidden = true;
            currentPage = 1;
            return;
        }

        const pageCount = Math.ceil(filteredStudents.length / studentsPerPage);
        currentPage = Math.min(currentPage, pageCount);
        const firstStudentIndex = (currentPage - 1) * studentsPerPage;
        const pageStudents = filteredStudents.slice(firstStudentIndex, firstStudentIndex + studentsPerPage);
        const lastStudentIndex = firstStudentIndex + pageStudents.length;

        paginationStatus.textContent = `Showing ${firstStudentIndex + 1}-${lastStudentIndex} of ${filteredStudents.length} students`;
        previousButton.hidden = currentPage === 1;
        nextButton.hidden = currentPage === pageCount;

        tbody.innerHTML = pageStudents.map(s => `
                    <tr>
                        <td style="font-weight:600; color:var(--text-main);">${s.student_id || '—'}</td>
                        <td style="font-weight:500;">${s.last_name || '—'}</td>
                        <td style="font-weight:500;">${s.first_name || '—'}</td>
                        <td><span class="class-badge">${s.class || '—'}</span></td>
                        <td>${s.gender || '—'}</td>
                        <td>${buildStatusPill(s)}</td>
                        <td style="color:var(--text-muted); font-size:12px;">${formatLastSync(s.last_sync_at, s.status)}</td>
                        <td>
                            ${s.contact ? `<a href="tel:${s.contact}" style="color:var(--sidebar-active);text-decoration:none;font-weight:500;">${s.contact}</a>` : '<span style="color:#94A3B8;">—</span>'}
                        </td>
                        <td>
                            <a href="/students/${s.id}/history" class="btn btn-primary">View Activity</a>
                        </td>
                    </tr>
                `).join('');
    }

    function pollActivityData() {
        fetch('/api/dashboard/stats')
            .then(res => res.json())
            .then(data => {
                activityStudents = data.students || [];
                renderActivityTable();
            })
            .catch(err => console.error('Activity poll error:', err));
    }

    // Initial fetch and set interval
    pollActivityData();
    setInterval(pollActivityData, 10000);
</script>
@endpush
