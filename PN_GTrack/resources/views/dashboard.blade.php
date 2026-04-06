<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'GTrack') }} - Dashboard</title>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
        <style>
            :root{
                --bg:#f6f7fb;
                --text:#0f172a;
                --muted:#6b7280;
                --card-border:#e5e7eb;
                --shadow: 0 1px 2px rgba(0,0,0,.04);
                --blue:#4f46e5;
                --online:#22c55e;
                --offline:#ef4444;
                --online-sub:#3b82f6;
                --offline-sub:#f43f5e;
            }
            *{box-sizing:border-box;}
            body{
                margin:0;
                background:var(--bg);
                color:var(--text);
                font-family: ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, Arial, "Noto Sans", "Liberation Sans", sans-serif;
            }
            .topbar{
                height:64px;
                background:#2563eb;
                border-bottom:1px solid rgba(0,0,0,.06);
                box-shadow: var(--shadow);
                display:flex;
                align-items:center;
                justify-content:space-between;
                padding:0 20px;
            }
            .brand{
                display:flex;
                align-items:center;
                gap:12px;
                min-width:220px;
            }
            .brand-badge{
                width:34px;height:34px;border-radius:10px;
                background:transparent;
                display:flex;align-items:center;justify-content:center;
            }
            .brand-title{
                font-size:14px;
                font-weight:700;
                letter-spacing:.2px;
                color:#fff;
            }
            .brand-sub{
                font-size:20px;
                color:#fff;
                margin-top:2px;
            }
            .brand-text{
                display:flex;
                flex-direction:column;
                line-height:1.1;
            }
            .actions{
                display:flex;
                align-items:center;
                gap:12px;
            }
            .icon-btn{
                width:36px;height:36px;border-radius:12px;
                border:1px solid rgba(255,255,255,.2);
                background:rgba(255,255,255,.1);
                display:flex;align-items:center;justify-content:center;
                position:relative;
                color:#fff;
            }
            .badge{
                position:absolute;
                top:-6px; right:-6px;
                min-width:18px;height:18px;
                padding:0 5px;
                border-radius:999px;
                background:#ef4444;
                color:#fff;
                font-size:11px;
                font-weight:700;
                display:flex;align-items:center;justify-content:center;
                border:2px solid #fff;
            }
            .logout{
                display:flex;
                align-items:center;
                gap:8px;
                padding:10px 14px;
                border-radius:12px;
                border:1px solid rgba(255,255,255,.3);
                background:rgba(255,255,255,.1);
                color:#fff;
                text-decoration:none;
                font-weight:600;
                font-size:13px;
            }
            .container{
                max-width:1180px;
                margin:0 auto;
                padding:24px 20px;
            }
            .page-title h1{
                margin:0;
                font-size:26px;
                font-weight:800;
                letter-spacing:.1px;
            }
            .page-title p{
                margin:6px 0 18px 0;
                color: #6d28d9;
                font-weight:500;
            }
            .cards{
                display:grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap:18px;
                margin-top:14px;
            }
            @media (max-width: 900px){
                .cards{grid-template-columns: 1fr; }
                .brand{min-width:auto;}
            }
            .card{
                background:#fff;
                border:1px solid rgba(0,0,0,.08);
                border-radius:16px;
                padding:18px 18px 16px 18px;
                position:relative;
                overflow:hidden;
                box-shadow: var(--shadow);
                min-height:150px;
            }
            .card-head{
                display:flex;
                align-items:flex-start;
                justify-content:space-between;
                gap:12px;
                margin-bottom:12px;
            }
            .card-title{
                font-size:14px;
                font-weight:800;
            }
            .status-dot{
                width:34px;height:34px;
                border-radius:12px;
                display:flex;align-items:center;justify-content:center;
                border:1px solid rgba(0,0,0,.06);
                background:#fff;
            }
            .stat-number{
                font-size:28px;
                font-weight:900;
                margin-top:6px;
            }
            .stat-sub{
                margin-top:6px;
                font-size:13px;
                color: #667085;
            }
            .stat-sub strong{font-weight:800;}
            .latest{
                margin-top:6px;
            }
            .latest-time{
                font-size:16px;
                font-weight:800;
                margin-top:6px;
            }
            .latest-date{
                font-size:13px;
                color: var(--muted);
                margin-top:3px;
            }
            .latest-icon{
                width:34px;height:34px;
                border-radius:12px;
                display:flex;align-items:center;justify-content:center;
                border:1px solid rgba(0,0,0,.06);
                background:#fff;
            }
            .card-icon-online{color: var(--online);}
            .card-icon-offline{color: var(--offline);}


            /* New Student Activity Table Styles */
.activity-section {
    margin-top: 24px;
    background: #fff;
    border: 1px solid rgba(0,0,0,.08);
    border-radius: 16px;
    padding: 20px;
    box-shadow: var(--shadow);
    overflow: hidden;
}
.table-container {
    width: 100%;
    overflow-x: auto;
    margin-top: 16px;
}
.activity-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    text-align: left;
}
.activity-table th {
    background: #f8fafc;
    padding: 12px;
    border-bottom: 2px solid #e5e7eb;
    font-weight: 700;
    color: var(--muted);
    text-transform: uppercase;
    font-size: 11px;
}
.activity-table td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
    vertical-align: middle;
}
.activity-table tr:hover {
    background: #fdfdfd;
}
.status-pill {
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 700;
    font-size: 11px;
}
.status-online { background: #dcfce7; color: #166534; }
.status-offline { background: #fee2e2; color: #991b1b; }
.battery-text { font-weight: 600; color: #374151; }

        </style>
    </head>
    <body>
        <header class="topbar">
            <div class="brand">
                <div class="brand-badge" aria-hidden="true">
                    <img src="{{ asset('images/gtrack.png') }}" alt="G!Track logo" style="width:1500%;height:150%;object-fit:contain;" />
                </div>
                <div class="brand-text">
                    
                    <div class="brand-sub">Admin Dashboard</div>
                </div>
            </div>

            <div class="actions">
                <a href="/notifications?tab=broadcast&class=all" class="icon-btn" aria-label="Broadcast Notifications">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2Z" fill="#0f172a"/>
        <path d="M18 16v-5c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2h16l-2-2Z" fill="#0f172a"/>
    </svg>
   <span class="badge">{{ $broadcastCount }}</span>
</a>
                <a href="/notifications?tab=sos&class=all" class="icon-btn" aria-label="SOS Alerts">
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
        <path d="M12 2L2 20h20L12 2Z" fill="#dc2626"/>
        <text x="12" y="17" text-anchor="middle" font-size="10" fill="#fff" font-weight="bold">!</text>
    </svg>
    <span class="badge">{{ $sosCount }}</span>
</a>
               <form action="{{ route('logout') }}" method="POST" style="display:inline;">
    @csrf
    <button type="submit" class="logout" style="background:none;border:none;cursor:pointer;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <path d="M10 17v-2H3v-6h7V7l5 5-5 5Z" fill="#0f172a"/>
            <path d="M14 3h7v18h-7v-2h5V5h-5V3Z" fill="#0f172a" opacity=".6"/>
        </svg>
        Logout
    </button>
</form>

            </div>
        </header>

        <main class="container">
            <div class="page-title">
                <h1>System Status Monitoring</h1>
                <p>Real-time overview of student tracking system</p>
            </div>

            <section class="cards">
               
                <article class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Online Students</div>
                            <div class="stat-number" style="color: var(--online);">{{ $onlineCount }}</div>
                            <div class="stat-sub" style="color: var(--online-sub);">
                                Currently online
                            </div>
                        </div>
                        <div class="status-dot card-icon-online" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3Z" fill="currentColor"/>
                                <path d="M8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Z" fill="currentColor" opacity=".9"/>
                                <path d="M8 13c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Z" fill="currentColor" opacity=".35"/>
                                <path d="M16 13c-1.14 0-3.2.36-4.64 1.06.94.74 1.64 1.7 1.64 2.44V19h9v-2.5c0-2.33-4.67-3.5-6-3.5Z" fill="currentColor" opacity=".25"/>
                            </svg>
                        </div>
                    </div>
                </article>

               
                <article class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Offline Students</div>
                            <div class="stat-number" style="color: var(--offline);">{{ $offlineCount }}</div>
                            <div class="stat-sub" style="color: var(--offline-sub);">
                                Currently offline
                            </div>
                        </div>
                        <div class="status-dot card-icon-offline" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M16 11c1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3 1.34 3 3 3Z" fill="currentColor"/>
                                <path d="M8 11c1.66 0 3-1.34 3-3S9.66 5 8 5 5 6.34 5 8s1.34 3 3 3Z" fill="currentColor" opacity=".9"/>
                                <path d="M8 13c-2.33 0-7 1.17-7 3.5V19h14v-2.5C15 14.17 10.33 13 8 13Z" fill="currentColor" opacity=".35"/>
                                <path d="M16 13c-1.14 0-3.2.36-4.64 1.06.94.74 1.64 1.7 1.64 2.44V19h9v-2.5c0-2.33-4.67-3.5-6-3.5Z" fill="currentColor" opacity=".25"/>
                            </svg>
                        </div>
                    </div>
                </article>

               
                <article class="card">
                    <div class="card-head">
                        <div>
                            <div class="card-title">Latest Update</div>
                            <div class="latest">
                                <div class="latest-time">{{ $latestTime }}</div>
                                <div class="latest-date">{{ $latestDate }}</div>
                            </div>
                        </div>
                        <div class="latest-icon" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Z" stroke="#6b7280" stroke-width="2" opacity=".9"/>
                                <path d="M12 6v6l4 2" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                    </div>
                </article>
            </section>

            
            <div style="display: flex; gap: 16px; margin-top: 24px;">
                <!-- Map Section (65%) -->
                <div style="flex: 0 0 65%; display: flex; flex-direction: column;">
                    <!-- Map Header with Class Filter -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h2 style="font-size: 18px; margin: 0;">Real-Time Location Tracking</h2>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <label for="class-filter" style="font-weight:600; font-size:14px;">Class:</label>
                            <select id="class-filter" style="padding:6px 10px;border:1px solid #d1d5db;border-radius:8px; font-size:13px;">
                                <option>All Classes</option>
                                <option>2026</option>
                                <option>2027</option>
                                <option>2028</option>
                            </select>
                        </div>
                    </div>
                    <div id="map" style="height: 500px; border-radius: 12px; box-shadow: 0 1px 2px rgba(0,0,0,.06);"></div>
                    <!-- Legend -->
                    <div style="margin-top: 12px; font-size: 13px; display: flex; gap: 16px; align-items: center;">
                        <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #3b82f6; border: 2px solid #1e40af;"></span> Male</span>
                        <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444; border: 2px solid #b91c1e;"></span> Female</span>
                    </div>
                </div>

                <!-- Notifications Section (35%) -->
                <div style="flex: 0 0 35%;">
                    <div style="background: #fff; border: 1px solid rgba(0,0,0,.08); border-radius: 16px; padding: 20px; box-shadow: 0 1px 2px rgba(0,0,0,.04);">
                        <!-- Notifications Header -->
                        <div style="margin-bottom: 16px;">
                            <h3 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800;">Notifications Center</h3>
                            <p style="margin: 0; font-size: 13px; color: #667085;">View all messages, SOS alerts & system status</p>
                        </div>

                        <!-- Notification Badges -->
                        <div style="display: flex; gap: 12px; margin-bottom: 20px;">
                            <div style="background: #FEE2E2; border: 1px solid #FECACA; border-radius: 8px; padding: 8px 12px; text-align: center; flex: 1;">
                                <div style="font-size: 20px; font-weight: 900; color: #DC2626;">
    {{ $broadcastCount }}
</div>
                                <div style="font-size: 11px; color: #991B1B; font-weight: 600;">New</div>
                            </div>
                            <div style="background: #DBEAFE; border: 1px solid #BFDBFE; border-radius: 8px; padding: 8px 12px; text-align: center; flex: 1;">
                                <div style="font-size: 20px; font-weight: 900; color: #2563EB;">
    {{ $sosCount }}
</div>
                                <div style="font-size: 11px; color: #1E40AF; font-weight: 600;">SOS</div>
                            </div>
                        </div>

                        <!-- Open Notifications Button -->
                        <button onclick="window.location.href='/notifications'" style="width: 100%; background: #2563EB; color: #fff; border: none; border-radius: 8px; padding: 12px 16px; font-size: 14px; font-weight: 600; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563EB'">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2Z" fill="currentColor"/>
                                <path d="M18 16v-5c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2h16l-2-2Z" fill="currentColor" opacity=".9"/>
                            </svg>
                            Open Notifications Dashboard
                        </button>
                    </div>

                    <!-- Send Notification Container -->
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #E5E7EB;">
                        <h3 style="margin: 0 0 6px 0; font-size: 16px; font-weight: 800;">Send Notification</h3>
                        <p style="margin: 0 0 16px 0; font-size: 13px; color: #667085;">Send emergency announcements</p>

                        @if(session('success'))
    <div style="color: green; margin-bottom: 10px;">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="/notifications/send" style="display: flex; flex-direction: column; gap: 12px;">
    @csrf

    <!-- Target Audience -->
    <div>
        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">
            Target Audience
        </label>
        <select name="target" required style="width: 100%; padding: 8px; border: 1px solid #d1d5db; border-radius: 6px;">
            <option value="all">All Students</option>
            <option value="2026">Class 2026</option>
            <option value="2027">Class 2027</option>
            <option value="2028">Class 2028</option>
            <option value="sos">SOS Alerts Only</option>
        </select>
    </div>

    <!-- Message -->
    <div>
        <label style="display: block; font-size: 12px; font-weight: 700; margin-bottom: 4px;">
            Message
        </label>
        <textarea name="message" required placeholder="Type your emergency announcement here..."
            style="width: 100%; padding: 10px; border: 1px solid #d1d5db; border-radius: 6px; min-height: 80px;"></textarea>
    </div>

    <!-- Send Button -->
    <button type="submit"
        style="width: 100%; background: #2563EB; color: #fff; border: none; border-radius: 6px; padding: 10px; font-weight: 600; cursor: pointer;">
        Send Notification
    </button>
</form>
                    </div>
                </div>
            </div>

        </main>
<section class="activity-section">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
        <div>
            <h3 style="margin: 0; font-size: 18px; font-weight: 800;">Student Activity Log</h3>
            <p style="margin: 4px 0 0 0; font-size: 13px; color: var(--muted);">
                Detailed status of all registered student devices
            </p>
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
                </tr>
            </thead>
          <tbody>
    @foreach($students ?? [] as $student)
    <tr>
        <td style="font-weight: 700;">{{ $student->student_id }}</td> <!-- STU20206009 -->
        <td>{{ $student->name }}</td>
        <td>
            <span style="background: #eff6ff; color: #1e40af; padding: 2px 6px; border-radius: 4px;">
                {{ $student->class }}
            </span>
        </td>
        <td>{{ $student->gender }}</td>
        <td>
            @if($student->sos_status === 'SOS')
                <span class="status-pill status-offline">● SOS ALERT</span>
            @else
                @if($student->status)
                    <span class="status-pill status-online">● ONLINE</span>
                @else
                    <span class="status-pill status-offline">● OFFLINE</span>
                @endif
            @endif
        </td>
        <td class="battery-text">
            <div style="display:flex; align-items:center; gap:4px;">
                <div style="width: 20px; height: 10px; border: 1px solid #9ca3af; border-radius: 2px; position: relative;">
                    <div style="width: {{ $student->battery_level }}%; height: 100%; background: {{ $student->battery_level < 20 ? '#ef4444' : '#22c55e' }};"></div>
                </div>
                {{ $student->battery_level }}%
            </div>
        </td>
        <td>
            <span title="{{ $student->signal_status }}">
                @if($student->signal_status == 'Strong') 📶 @else ⚠️ @endif
                {{ $student->signal_status }}
            </span>
        </td>
        <td style="color: var(--muted);">{{ $student->last_update }}</td>
        <td>
            <a href="tel:{{ $student->contact }}" style="color: var(--blue); text-decoration: none; font-weight: 600;">
                {{ $student->contact }}
            </a>
        </td>
    </tr>
    @endforeach

    @if(count($students ?? []) == 0)
    <tr>
        <td colspan="9" style="text-align:center; padding: 20px; color: var(--muted);">
            No student data available.
        </td>
    </tr>
    @endif
</tbody>


        </table>
    </div>
</section>

        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
        <script>
            let map;
            let markers = [];
            let selectedClass = 'All Classes';

            function getColorByGender(gender) {
                if (!gender) return '#6b7280';
                return gender.toString().toLowerCase() === 'female' ? '#ef4444' : '#3b82f6';
            }

            function showPopup(location) {
                const student = location.student || {};
                const recorded = location.recorded_at ? new Date(location.recorded_at).toLocaleString() : 'Unknown';
                const sos = location.sos_status || student.sos_status || 'safe';

                const sosLabel = sos === 'help' ? '<span style="color:#ef4444;font-weight:700;">I Need Help</span>' : '<span style="color:#22c55e;font-weight:700;">I Am Safe</span>';

                return `
                    <div style="font-size:13px;line-height:1.3;min-width:220px;">
                        <strong>${student.name || 'Unknown'}</strong><br>
                        Gender: ${student.gender || 'Unknown'}<br>
                        Class: ${student.class || 'Unknown'}<br>
                        Email: ${student.email || 'N/A'}<br>
                        Latitude: ${location.latitude}<br>
                        Longitude: ${location.longitude}<br>
                        Latest: ${recorded}<br>
                        Status: ${sosLabel}<br>
                        
                    </div>
                `;
            }

            function updateSOS(studentId, sosStatus) {
                fetch('/api/location/sos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ student_id: studentId, sos_status: sosStatus })
                })
                .then(res => {
                    if (!res.ok) throw new Error('SOS update failed ' + res.status);
                    return res.json();
                })
                .then(() => {
                    loadLocations();
                })
                .catch(err => console.error('Error setting SOS status:', err));
            }

            function clearMarkers() {
                markers.forEach(marker => marker.remove());
                markers = [];
            }

            function loadLocations() {
                const url = selectedClass === 'All Classes' ? '/api/location/all' : `/api/location/all?class=${selectedClass}`;
                fetch(url)
                    .then(res => {
                        if (!res.ok) throw new Error(`HTTP ${res.status}`);
                        return res.json();
                    })
                    .then(data => {
                        clearMarkers();
                        if (!Array.isArray(data) || data.length === 0) return;

                        const bounds = [];
                        data.forEach(loc => {
                            const lat = parseFloat(loc.latitude);
                            const lng = parseFloat(loc.longitude);
                            if (Number.isNaN(lat) || Number.isNaN(lng)) return;

                            const marker = L.circleMarker([lat, lng], {
                                radius: 8,
                                fillColor: getColorByGender(loc.student?.gender),
                                color: '#1f2937',
                                weight: 1,
                                fillOpacity: 0.9,
                            }).addTo(map);

                            marker.bindPopup(showPopup(loc));
                            markers.push(marker);
                            bounds.push([lat, lng]);
                        });

                        if (bounds.length) {
                            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
                        }
                    })
                    .catch(err => console.error('Error loading location data:', err));
            }

            function initMap() {
                map = L.map('map').setView([10.3157, 123.8854], 13);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                    maxZoom: 19,
                }).addTo(map);

                document.getElementById('class-filter').addEventListener('change', function () {
                    selectedClass = this.value;
                    loadLocations();
                });

                loadLocations();
                setInterval(loadLocations, 900000); 

                setTimeout(() => map.invalidateSize(), 350);
            }

            window.updateSOS = updateSOS;
            window.addEventListener('DOMContentLoaded', initMap);
        </script>
    </body>
</html>