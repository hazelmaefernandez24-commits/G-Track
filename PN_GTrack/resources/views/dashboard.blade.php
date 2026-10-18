@extends('layouts.app')

@section('title', 'Overview')
@section('subtitle', 'System status and analytics overview')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        box-shadow: var(--card-shadow);
    }

    .stat-content h3 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .stat-subtitle {
        font-size: 13px;
        color: var(--text-muted);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .online-icon { color: var(--online); background: #DCFCE7; }
    .offline-icon { color: var(--offline); background: #FEE2E2; }
    .update-icon { color: var(--text-muted); background: #F1F5F9; }

    /* System Overview Cards */
    .system-overview {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
    }

    .system-header {
        margin-bottom: 24px;
    }

    .system-header h2 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
    }

    .system-header p {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .quick-access-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .qa-card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        gap: 16px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .qa-card:hover {
        border-color: var(--sidebar-active);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        transform: translateY(-2px);
    }

    .qa-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .qa-blue { background: #EFF6FF; color: var(--sidebar-active); }
    .qa-green { background: #DCFCE7; color: var(--online); }
    .qa-purple { background: #F3E8FF; color: #9333EA; }
    .qa-orange { background: #FFEDD5; color: #EA580C; }

    .qa-content h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .qa-content p {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.4;
    }

    .active-students-card {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        margin-top: 0;
    }

    .dashboard-lists {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        align-items: start;
        gap: 24px;
        margin-top: 32px;
    }

    .main-wrapper:has(.active-students-card) {
        min-width: 0;
    }

    .stats-grid {
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }

    .stat-card {
        min-width: 0;
    }

    .active-students-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        margin-bottom: 20px;
    }

    .active-students-header h2 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
    }

    .active-students-count {
        color: var(--text-muted);
        font-size: 13px;
        margin-top: 4px;
    }

    .active-students-filters {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(120px, 35%);
        justify-content: end;
        gap: 12px;
        margin-bottom: 16px;
    }

    .active-students-search,
    .active-students-batch {
        min-height: 40px;
        padding: 8px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #FFFFFF;
        color: var(--text-main);
        font-size: 14px;
        outline: none;
    }

    .active-students-search {
        width: 100%;
        min-width: 0;
    }

    .active-students-batch {
        width: 100%;
        min-width: 0;
    }

    .current-sos-batch {
        width: auto;
        min-width: 140px;
        max-width: 180px;
        flex: 0 0 auto;
    }

    .active-students-search:focus,
    .active-students-batch:focus {
        border-color: var(--primary-dark);
        box-shadow: 0 0 0 2px rgba(0, 157, 225, 0.14);
    }

    .active-students-table-wrap {
        overflow-x: auto;
    }

    .active-students-table {
        width: 100%;
        min-width: 0;
        border-collapse: collapse;
        text-align: left;
        table-layout: fixed;
    }

    .active-students-table th:nth-child(1),
    .active-students-table td:nth-child(1),
    .active-students-table th:nth-child(2),
    .active-students-table td:nth-child(2) {
        width: 25%;
    }

    .active-students-table th:nth-child(3),
    .active-students-table td:nth-child(3) {
        width: 25%;
    }

    .active-students-table th:nth-child(4),
    .active-students-table td:nth-child(4) {
        width: 25%;
    }

    .active-students-table th,
    .active-students-table td {
        padding: 14px 4px;
        border-bottom: 1px solid var(--border-color);
        font-size: 14px;
        overflow-wrap: anywhere;
    }

    .active-students-table th {
        color: var(--text-muted);
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.02em;
        white-space: nowrap;
    }

    .active-students-table tbody tr:last-child td {
        border-bottom: 0;
    }

    .active-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        color: var(--online);
        font-weight: 600;
    }

    .active-status::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: currentColor;
    }

    .active-students-empty {
        color: var(--text-muted);
        text-align: center;
    }

    .sos-alert-status {
        color: #DC2626;
        font-weight: 700;
    }

    @media (max-width: 640px) {
        .dashboard-lists {
            grid-template-columns: minmax(0, 1fr);
        }

        .active-students-filters {
            grid-template-columns: minmax(0, 1fr);
        }

        .active-students-header {
            align-items: stretch;
            flex-direction: column;
        }

        .active-students-search {
            width: 100%;
        }

        .current-sos-batch {
            width: 100%;
            max-width: none;
        }

        .active-students-table th,
        .active-students-table td {
            padding: 12px 6px;
        }
    }
</style>
@endpush

@section('content')

<!-- Top Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-content">
            <h3>Online Students</h3>
            <div class="stat-value" style="color: var(--online);" id="online-count">{{ $onlineCount ?? 0 }}</div>
            <div class="stat-subtitle">Currently online</div>
        </div>
        <div class="stat-icon online-icon">
            <i data-lucide="user-check"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <h3>Offline Students</h3>
            <div class="stat-value" style="color: var(--offline);" id="offline-count">{{ $offlineCount ?? 0 }}</div>
            <div class="stat-subtitle">Currently offline</div>
        </div>
        <div class="stat-icon offline-icon">
            <i data-lucide="user-minus"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <h3>Last Update</h3>
            <div class="stat-value" style="font-size: 24px; margin-top: 6px;" id="latest-time">{{ $latestTime ?? '--:--' }}</div>
            <div class="stat-subtitle" id="latest-date">{{ $latestDate ?? '' }}</div>
        </div>
        <div class="stat-icon update-icon">
            <i data-lucide="clock"></i>
        </div>
    </div>
</div>

<div class="dashboard-lists">
<section class="card active-students-card" aria-labelledby="active-students-title">
    <div class="active-students-header">
        <div>
            <h2 id="active-students-title">Student List Active</h2>
            <p class="active-students-count"><span id="active-students-count">{{ $activeStudents->count() }}</span> students online</p>
        </div>
    </div>

    <div class="active-students-filters">
        <input
            id="active-students-search"
            class="active-students-search"
            type="search"
            placeholder="Search students"
            aria-label="Search active students"
        >
        <select id="active-students-batch" class="active-students-batch" aria-label="Filter active students by batch">
            <option value="All Classes">All Classes</option>
            @foreach($classes as $class)
                <option value="{{ $class->name }}">{{ $class->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="active-students-table-wrap">
        <table class="active-students-table">
            <thead>
                <tr>
                    <th scope="col">First Name</th>
                    <th scope="col">Last Name</th>
                    <th scope="col">Status</th>
                    <th scope="col">Batches</th>
                </tr>
            </thead>
            <tbody id="active-students-rows">
                @forelse($activeStudents as $student)
                    <tr>
                        <td>{{ $student->first_name ?: \Illuminate\Support\Str::before($student->name, ' ') }}</td>
                        <td>{{ $student->last_name ?: (\Illuminate\Support\Str::contains($student->name, ' ') ? \Illuminate\Support\Str::afterLast($student->name, ' ') : '') }}</td>
                        <td><span class="active-status">On</span></td>
                        <td>{{ $student->class }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="active-students-empty" colspan="4">No students are currently online.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<section class="card active-students-card current-sos-card" aria-labelledby="current-sos-alerts-title">
    <div class="active-students-header">
        <div>
            <h2 id="current-sos-alerts-title">Current SOS Alerts</h2>
            <p class="active-students-count">
                <span id="current-sos-alert-count">{{ $currentSosAlerts->count() }}</span>
                <span id="current-sos-alert-label">student SOS alerts</span>
            </p>
        </div>
        <select id="current-sos-alert-batch" class="active-students-batch current-sos-batch" aria-label="Filter SOS alerts by batch">
            <option value="All Classes">All Classes</option>
            @foreach($classes as $class)
                <option value="{{ $class->name }}">{{ $class->name }}</option>
            @endforeach
        </select>
    </div>

    <div class="active-students-table-wrap">
        <table class="active-students-table">
            <thead>
                <tr>
                    <th scope="col">Last Name</th>
                    <th scope="col">First Name</th>
                    <th scope="col">Status</th>
                </tr>
            </thead>
            <tbody id="current-sos-alert-rows">
                @forelse($currentSosAlerts as $alert)
                    <tr>
                        <td>{{ $alert['last_name'] }}</td>
                        <td>{{ $alert['first_name'] }}</td>
                        <td class="sos-alert-status">{{ $alert['alert'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="active-students-empty" colspan="3">NO SOS ALERT</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
</div>

@endsection

@push('scripts')
<script>
    const activeStudentsRows = document.getElementById('active-students-rows');
    const activeStudentsCount = document.getElementById('active-students-count');
    const activeStudentsSearch = document.getElementById('active-students-search');
    const activeStudentsBatch = document.getElementById('active-students-batch');
    let dashboardStudents = @json($activeStudentsData);
    const currentSosAlertRows = document.getElementById('current-sos-alert-rows');
    const currentSosAlertCount = document.getElementById('current-sos-alert-count');
    const currentSosAlertLabel = document.getElementById('current-sos-alert-label');
    const currentSosAlertBatch = document.getElementById('current-sos-alert-batch');
    let dashboardSosAlerts = @json($currentSosAlerts);

    function renderCurrentSosAlerts() {
        currentSosAlertRows.replaceChildren();
        const selectedBatch = currentSosAlertBatch.value;
        const visibleAlerts = dashboardSosAlerts.filter(alert =>
            selectedBatch === 'All Classes' || alert.class === selectedBatch
        );
        currentSosAlertCount.textContent = visibleAlerts.length;
        currentSosAlertLabel.textContent = `student SOS alert${visibleAlerts.length === 1 ? '' : 's'}`;

        if (!visibleAlerts.length) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 3;
            cell.className = 'active-students-empty';
            cell.textContent = 'NO SOS ALERT';
            row.appendChild(cell);
            currentSosAlertRows.appendChild(row);
            return;
        }

        visibleAlerts.forEach(alert => {
            const row = document.createElement('tr');
            [alert.last_name, alert.first_name].forEach(value => {
                const cell = document.createElement('td');
                cell.textContent = value || '';
                row.appendChild(cell);
            });

            const detailsCell = document.createElement('td');
            detailsCell.className = 'sos-alert-status';
            detailsCell.textContent = alert.alert || 'SOS ALERT';
            row.appendChild(detailsCell);
            currentSosAlertRows.appendChild(row);
        });
    }

    function renderActiveStudents() {
        const search = activeStudentsSearch.value.trim().toLocaleLowerCase();
        const batch = activeStudentsBatch.value;
        const activeStudents = dashboardStudents.filter(student => {
            if (student.status !== true) return false;

            const parts = (student.name || '').trim().split(/\s+/);
            const firstName = student.first_name || parts[0] || '';
            const lastName = student.last_name || (parts.length > 1 ? parts[parts.length - 1] : '');
            const matchesSearch = `${firstName} ${lastName}`.toLocaleLowerCase().includes(search);
            const matchesBatch = batch === 'All Classes' || student.class === batch;

            return matchesSearch && matchesBatch;
        });

        activeStudentsRows.replaceChildren();
        activeStudentsCount.textContent = activeStudents.length;

        if (activeStudents.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 4;
            cell.className = 'active-students-empty';
            cell.textContent = dashboardStudents.some(student => student.status === true)
                ? 'No active students match your filters.'
                : 'No students are currently online.';
            row.appendChild(cell);
            activeStudentsRows.appendChild(row);
            return;
        }

        activeStudents.forEach(student => {
            const parts = (student.name || '').trim().split(/\s+/);
            const values = [
                student.first_name || parts[0] || '',
                student.last_name || (parts.length > 1 ? parts[parts.length - 1] : ''),
            ];
            const row = document.createElement('tr');

            values.forEach(value => {
                const cell = document.createElement('td');
                cell.textContent = value;
                row.appendChild(cell);
            });

            const statusCell = document.createElement('td');
            const status = document.createElement('span');
            status.className = 'active-status';
            status.textContent = 'On';
            statusCell.appendChild(status);
            row.appendChild(statusCell);

            const batchCell = document.createElement('td');
            batchCell.textContent = student.class || '';
            row.appendChild(batchCell);

            activeStudentsRows.appendChild(row);
        });
    }

    activeStudentsSearch.addEventListener('input', renderActiveStudents);
    activeStudentsBatch.addEventListener('change', renderActiveStudents);
    currentSosAlertBatch.addEventListener('change', renderCurrentSosAlerts);

    // Poll just for the dashboard stats
    function updateDashboardStats() {
        fetch('/api/dashboard/stats')
            .then(res => res.json())
            .then(data => {
                const onlineEl  = document.getElementById('online-count');
                const offlineEl = document.getElementById('offline-count');
                const timeEl    = document.getElementById('latest-time');
                const dateEl    = document.getElementById('latest-date');
                
                if (onlineEl)  onlineEl.textContent  = data.onlineCount;
                if (offlineEl) offlineEl.textContent = data.offlineCount;
                if (timeEl)    timeEl.textContent    = data.latestTime ?? '--:--';
                if (dateEl)    dateEl.textContent    = data.latestDate ?? '';
                if (Array.isArray(data.students)) {
                    dashboardStudents = data.students;
                    renderActiveStudents();
                }
                if (Array.isArray(data.currentSosAlerts)) {
                    dashboardSosAlerts = data.currentSosAlerts;
                    renderCurrentSosAlerts();
                }
            })
            .catch(err => console.error('Dashboard stats update failed:', err));
    }

    renderActiveStudents();
    renderCurrentSosAlerts();
    setInterval(updateDashboardStats, 10000);
</script>
@endpush