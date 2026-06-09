@extends('layouts.app')

@section('title', 'Student Activity')
@section('subtitle', 'Real-time student connection status')

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
        margin-bottom: 24px;
    }

    .activity-title h2 {
        font-size: 16px;
        font-weight: 700;
        color: var(--text-main);
        display: flex;
        align-items: center;
        gap: 8px;
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
        color: #FF9933;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        font-size: 11px;
        display: inline-block;
        text-align: center;
    }
    .btn-primary { 
        background: var(--sidebar-bg); 
        
        background-color: #22bbea;


    }
    .btn-primary:hover {
        background: var(--sidebar-hover);

    }
</style>
@endpush

@section('content')

<div class="activity-card">
    <div class="activity-header">
        <div class="activity-title">
            <h2>
                <i data-lucide="activity" style="width: 20px; height: 20px;"></i>
                Student Activity Table
            </h2>
            <p>Real-time student connection status and details</p>
        </div>
        
        <div class="controls-group">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="filter" style="width: 16px; height: 16px; color: var(--text-muted);"></i>
                <select id="activity-class-filter" class="filter-select">
                    <option value="all">All Classes</option>
                    <option value="2026">2026</option>
                    <option value="2027">2027</option>
                    <option value="2028">2028</option>
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
                    <th>Name</th>
                    <th>Class</th>
                    <th>Gender</th>
                    <th>Status</th>
                    <th>Battery</th>
                    <th>Signal</th>
                    <th>Last Update</th>
                    <th>Contact</th>
                    <th>Timeline</th>
                </tr>
            </thead>
            <tbody id="student-table-body">
                <!-- Data populated by JS -->
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted);">Loading student data...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let currentFilter = 'all';

    document.getElementById('activity-class-filter').addEventListener('change', function(e) {
        currentFilter = e.target.value;
        pollActivityData();
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

    function buildBatteryCell(level) {
        if (level === null || level === undefined) return '<span style="color: #94A3B8;">N/A</span>';
        const color = level < 20 ? '#EF4444' : '#22C55E';
        return `
            <div style="display:flex;align-items:center;gap:6px;">
                <div style="width:24px;height:12px;border:1px solid #CBD5E1;border-radius:2px;position:relative;padding:1px;">
                    <div style="width:${level}%;height:100%;background:${color};border-radius:1px;"></div>
                </div>
                <span style="font-weight:600;font-size:12px;">${level}%</span>
            </div>
        `;
    }

    function buildSignalIcon(signal) {
        if (!signal) return '<span style="color: #94A3B8;">—</span>';
        
        const sig = signal.toLowerCase();
        if (sig.includes('excellent') || sig.includes('strong') || sig.includes('good')) {
            return `<span style="color: #16A34A;">📶 ${signal}</span>`;
        } else if (sig.includes('fair')) {
            return `<span style="color: #F59E0B;">📶 ${signal}</span>`;
        } else {
            return `<span style="color: #EF4444;">⚠️ ${signal}</span>`;
        }
    }

    function pollActivityData() {
        fetch('/api/dashboard/stats')
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('student-table-body');
                
                if (!data.students || data.students.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:32px;color:#64748B;">No student data available.</td></tr>';
                    document.getElementById('student-count-display').textContent = '0 Students';
                    return;
                }

                // Apply filter
                let filteredStudents = data.students;
                if (currentFilter !== 'all') {
                    filteredStudents = filteredStudents.filter(s => s.class && s.class.toString() === currentFilter);
                }

                document.getElementById('student-count-display').textContent = `${filteredStudents.length} Students`;

                if (filteredStudents.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="10" style="text-align:center;padding:32px;color:#64748B;">No students found for this class.</td></tr>';
                    return;
                }

                tbody.innerHTML = filteredStudents.map(s => `
                    <tr>
                        <td style="font-weight:600; color:var(--text-main);">${s.student_id || '—'}</td>
                        <td style="font-weight:500;">${s.name || '—'}</td>
                        <td><span class="class-badge">${s.class || '—'}</span></td>
                        <td>${s.gender || '—'}</td>
                        <td>${buildStatusPill(s)}</td>
                        <td>${buildBatteryCell(s.battery_level)}</td>
                        <td>${buildSignalIcon(s.signal_status)}</td>
                        <td style="color:var(--text-muted); font-size:12px;">${s.last_update || '—'}</td>
                        <td>
                            ${s.contact ? `<a href="tel:${s.contact}" style="color:var(--sidebar-active);text-decoration:none;font-weight:500;">${s.contact}</a>` : '<span style="color:#94A3B8;">—</span>'}
                        </td>
                        <td>
                            <a href="/students/${s.id}/history" class="btn btn-primary">View History</a>
                        </td>
                    </tr>
                `).join('');
            })
            .catch(err => console.error('Activity poll error:', err));
    }

    // Initial fetch and set interval
    pollActivityData();
    setInterval(pollActivityData, 10000);
</script>
@endpush
