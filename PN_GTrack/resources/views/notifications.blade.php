<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset='UTF-8'/>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>G!Track - Notifications Dashboard</title>
    <style>
        /* ---- your existing styles remain unchanged ---- */
        :root { 
            --bg: #f8fafc; 
            --card: #ffffff; 
            --line: #e5e7eb; 
            --text:#0f172a; 
            --muted:#64748b; 
            --blue:#2563eb; 
            --red:#dc2626; 
            --yellow:#f59e0b; 
        }
        *{box-sizing:border-box;}
        body{
            margin:0; 
            font-family:ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,Noto Sans,Liberation Sans,sans-serif; 
            background:var(--bg); color:var(--text);
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
            color: #fff;
            text-decoration: none;
        }

       .brand-badge{
                width:34px;height:34px;border-radius:10px;
                background:transparent;
                display:flex;align-items:center;justify-content:center;
            }

        

        .brand-text {
            display: flex;
            flex-direction: column;
        }

        .brand-name {
            font-size: 19px;
            font-weight: 800;
            line-height:1;
        }

        .brand-sub {
            font-size: 11px;
            font-weight: 400;
            opacity: 0.8;
            margin-top: 2px;
        }

        .actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .icon-btn {
            position: relative;
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            width: 34px;
            height: 34px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            transition: all 0.2s;
        }

        .icon-btn:hover { background: rgba(255,255,255,0.2); }

        .logout {
            background: none;
            border: none;
            color: #fff;
            font-weight: 700;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 10px;
            transition: all 0.2s;
        }
        .logout:hover { background: rgba(255,255,255,0.1); }

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
            margin-bottom: 24px;
        }
        .container {
            max-width: 1300px;
            margin: 0 auto;
            padding: 20px 24px 40px 24px;
        }

        .card{
            background:#fff;
            border:1px solid rgba(0,0,0,.08);
            border-radius:16px;
            padding:18px 18px 16px 18px;
            position:relative;
            overflow:hidden;
            box-shadow: 0 1px 2px rgba(0,0,0,.04);
            min-height:150px;
        }
        .card-head{
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            gap:122x;
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
            font-weight: 500;
        }
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
            color: #64748b;
            margin-top:3px;
        }
        .latest-icon{
            width:34px;height:34px;
            border-radius:12px;
            display:flex;align-items:center;justify-content:center;
            border:1px solid rgba(0,0,0,.06);
            background:#fff;
        }

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        .feature-row {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .filter-block {
            display: flex;
            justify-content: flex-end;
            gap: 12px;
            align-items: center;
            padding: 16px 18px;
            border-top: 1px solid var(--line);
            border-bottom: 1px solid var(--line);
            background: #f8fafc;
            border-radius: 0 0 12px 12px;
        }

        .filter-label {
            font-weight: 700;
            color: #0f172a;
            font-size: 14px;
        }

        .select-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        select {
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 9px 11px;
            font-size: 14px;
            background: #fff;
            color: #0f172a;
        }

        .tabs {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            border: 1px solid var(--line);
            border-radius: 9px;
            overflow: hidden;
            background: #f1f5f9;
        }

        .tab {
            padding: 10px 12px;
            text-align: center;
            font-weight: 700;
            font-size: 14px;
            color: #475569;
            text-decoration: none;
            cursor: pointer;
            border-right: 1px solid var(--line);
        }

        .tab:last-child {
            border-right: none;
        }

        .tab.active {
            background: #fff;
            color: var(--text);
        }

        /* Panels */
        .card-panel {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            margin-top: 8px;
            padding: 14px;
        }

        .card-panel-grid {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            align-items: center;
        }

        .card-title {
            margin: 0;
            font-size: 16px;
            font-weight: 700;
        }

        .card-sub {
            margin: 2px 0 0;
            color: #94a3b8;
            font-size: 13px;
        }

        /* Badges */
        .badge-pill {
            padding: 3px 9px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #1d4ed8;
        }

        .badge-right {
            background: #fff;
            border: 1px solid var(--line);
            color: #0f172a;
            padding: 5px 10px;
            border-radius: 999px;
            font-weight: 700;
            font-size: 12px;
        }

        /* Messages */
        .messages {
            margin-top: 10px;
        }

        .message-item {
            background: #f8fafc;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: 12px;
            margin-bottom: 10px;
        }

        .message-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .message-title {
            margin: 0;
            font-weight: 700;
            font-size: 14px;
        }

        .message-meta {
            color: #64748b;
            font-size: 12px;
        }

        .message-body {
            margin: 7px 0 0;
            color: #334155;
            font-size: 13px;
        }
        .message-actions {
            margin-top: 10px;
        }
        .action-btn {
            border: none;
            border-radius: 8px;
            color: #fff;
            font-weight: 700;
            padding: 6px 12px;
            cursor: pointer;
        }
        .ack-btn { background:#059669; }
        .read-btn { background:#2563eb; }

        /* Table Structure */
        .table-container {
            width: 100%;
            overflow-x: auto;
            margin-top: 10px;
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

        /* Sub Tabs */
        .sub-tabs {
            display: flex;
            gap: 1px;
            background: #e2e8f0;
            border: 1px solid var(--line);
            border-radius: 8px;
            padding: 3px;
            margin-bottom: 16px;
            width: 100%;
        }
        .sub-tab {
            flex: 1;
            text-align: center;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            color: #64748b;
            transition: all 0.2s;
        }
        .sub-tab:hover {
            color: var(--blue);
        }
        .sub-tab.active {
            background: #fff;
            color: var(--blue);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <header class="topbar">
        <a href="/dashboard" class="brand">
            <div class="brand-badge">
                <img src="{{ asset('images/gtrack.png') }}" alt="logo" style="width:1500%;height:150%;object-fit:contain;" />
            </div>
            <div class="brand-text">
                <div class="brand-name">Admin Dashboard</div>
                <div class="brand-sub">Communications Center</div>
            </div>
        </a>

        <div class="actions">
            <a href="/dashboard" class="logout">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                    <polyline points="9 22 9 12 15 12 15 22" />
                </svg>
                Main Dashboard
            </a>

            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="logout">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    Logout
                </button>
            </form>
        </div>
    </header>

    <main class="container">
        @if(session('success'))
            <div style='margin-bottom:12px;padding:10px 14px;border:1px solid #34d399;background:#d1fae5;color:#065f46;border-radius:10px; font-weight:600;'>
                {{ session('success') }}
            </div>
        @endif

        <div class="page-title">
            <h1>System Status Monitoring</h1>
            <p>Real-time overview of student tracking system</p>
        </div>

        <section class="cards">
            <article class="card">
                <div class="card-head">
                    <div>
                        <div class="card-title">Online Students</div>
                        <div class="stat-number" id="online-count" style="color: #22c55e;">{{ $stats['onlineCount'] }}</div>
                        <div class="stat-sub" style="color: #3b82f6;">
                            Currently online
                        </div>
                    </div>
                    <div class="status-dot" style="color: #22c55e;">
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
                        <div class="stat-number" id="offline-count" style="color: #ef4444;">{{ $stats['offlineCount'] }}</div>
                        <div class="stat-sub" style="color: #f43f5e;">
                            Currently offline
                        </div>
                    </div>
                    <div class="status-dot" style="color: #ef4444;">
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
                            <div class="latest-time" id="latest-time">{{ $stats['latestTime'] }}</div>
                            <div class="latest-date" id="latest-date">{{ $stats['latestDate'] }}</div>
                        </div>
                    </div>
                    <div class="latest-icon">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20Z" stroke="#6b7280" stroke-width="2" opacity=".9"/>
                            <path d="M12 6v6l4 2" stroke="#6b7280" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        <div class="page-title" style="margin-top: 32px;">
            <h1>Notifications Overview</h1>
            <p>Summary of system communications and alerts</p>
        </div>

        <section class="cards">
            <!-- Unread Messages -->
            <article class="card">
                <div class="card-head">
                    <div>
                        <div class="card-title">Unread Messages</div>
                        <div class="stat-number" style="color: var(--blue);">{{ $stats['unread'] }}</div>
                        <div class="stat-sub">
                            Requires attention
                        </div>
                    </div>
                    <div class="status-dot" style="color: var(--blue);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2Zm0 4-8 5-8-5V6l8 5 8-5v2Z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
            </article>

            <!-- Emergency Alerts -->
            <article class="card">
                <div class="card-head">
                    <div>
                        <div class="card-title">Emergency Alerts</div>
                        <div class="stat-number" style="color: var(--red);">{{ $stats['sos'] }}</div>
                        <div class="stat-sub">
                            Pending SOS alerts
                        </div>
                    </div>
                    <div class="status-dot" style="color: var(--red);">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M1 21h22L12 2 1 21Zm12-3h-2v-2h2v2Zm0-4h-2v-4h2v4Z" fill="currentColor"/>
                        </svg>
                    </div>
                </div>
            </article>

            <!-- Broadcast History -->
            <article class="card">
                <div class="card-head">
                    <div>
                        <div class="card-title">Broadcast History</div>
                        <div class="stat-number" style="color: #f59e0b;">{{ $stats['broadcast'] }}</div>
                        <div class="stat-sub">
                            Announcements sent
                        </div>
                    </div>
                    <div class="status-dot" style="color: #f59e0b;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2Zm0 14H5.17L4 17.17V4h16v12Z" fill="currentColor"/>
                            <path d="M7 9h10v2H7z" fill="currentColor" opacity=".3"/>
                        </svg>
                    </div>
                </div>
            </article>
        </section>

        <div class='section' style="margin-top: 24px;">
            <div class='filter-block' style="border-radius: 12px; border: 1px solid var(--line);">
                <span class='filter-label'>Filter by Class</span>
                <div class='select-wrap'>
                    <span style='color:#475569;font-size:15px;'>:</span>
                    <select id='class-filter' onchange="location.href='?class=' + encodeURIComponent(this.value) + '&tab={{ $tab }}'">
                        <option value='all' {{ $class === 'all' ? 'selected' : '' }}>All Classes</option>
                        <option value='2026' {{ $dbClass === '2026' ? 'selected' : '' }}>Class 2026</option>
                        <option value='2027' {{ $dbClass === '2027' ? 'selected' : '' }}>Class 2027</option>
                        <option value='2028' {{ $dbClass === '2028' ? 'selected' : '' }}>Class 2028</option>
                    </select>
                </div>
            </div>
        </div>

        <div class='section'>
            <div class='tabs'>
                <a class='tab {{ $tab === "student" ? "active" : "" }}' href='?class={{ urlencode($class) }}&tab=student'>Student Messages</a>
                <a class='tab {{ $tab === "sos" ? "active" : "" }}' href='?class={{ urlencode($class) }}&tab=sos'>Emergency Alerts</a>
                <a class='tab {{ $tab === "broadcast" ? "active" : "" }}' href='?class={{ urlencode($class) }}&tab=broadcast'>Broadcast Notifications</a>
            </div>

            <div class='card-panel'>
                <div class='card-panel-grid'>
                    <div>
                        <h3 class='card-title'>{{ $class === 'all' ? 'All Classes' : $class }}</h3>
                        <p class='card-sub'>{{ $notifications->count() }} {{ $notifications->count() === 1 ? 'message' : 'messages' }}</p>
                    </div>
                    <span class='badge-right'>{{ $stats['unread'] }} Unread</span>
                </div>

                @if($tab === 'broadcast')
                    <div class='broadcast-info'>
                        <strong>Broadcast Notifications History<br></strong>
                        <p>All notifications sent to students.</p>
                    </div>
                @endif

                <div class='messages'>
                    @if($tab === 'sos')
                        <div class="sub-tabs">
                            <a href="?class={{ urlencode($class) }}&tab=sos&subtab=sos" class="sub-tab {{ $subtab === 'sos' ? 'active' : '' }}">
                                SOS Alerts
                            </a>
                            <a href="?class={{ urlencode($class) }}&tab=sos&subtab=blackout" class="sub-tab {{ $subtab === 'blackout' ? 'active' : '' }}">
                                Blackout Alerts
                            </a>
                        </div>

                        @if($subtab === 'sos')
                            @forelse($notifications->where('type', 'sos') as $notification)
                                <div class='message-item' style="{{ $notification->status === 'resolved' ? 'opacity: 0.7; border-left: 4px solid var(--muted);' : 'border-left: 4px solid var(--red);' }}">
                                    <div class='message-head'>
                                        <p class='message-title'>
                                            SOS Alert: {{ $notification->student->name ?? 'Unknown Student' }} ({{ $notification->student->student_id ?? 'N/A' }})
                                            @if($notification->status === 'resolved')
                                                <span class='badge-pill' style='background:#f1f5f9;color:#64748b;border-color:#e2e8f0;'>I am Safe (Resolved)</span>
                                            @else
                                                <span class='badge-pill' style='background:#fee2e2;color:#991b1b;border-color:#fecaca;'>Active Help Needed</span>
                                            @endif
                                        </p>
                                        <span class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                                    </div>
                                    {{-- Message Body removed as per request --}}
                                    
                                    @if($notification->media_url || $notification->video_url || $notification->audio_url)
                                        <div style="margin-top: 12px; display: flex; flex-direction: column; gap: 8px;">
                                            {{-- Legacy media_url support --}}
                                            @if($notification->media_url)
                                                <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--line);">
                                                    @if(Str::endsWith($notification->media_url, ['.mp3', '.wav']))
                                                        <audio controls style="width: 100%;"><source src="{{ $notification->media_url }}" type="audio/mpeg"></audio>
                                                    @else
                                                        <video controls style="width: 100%; display: block;"><source src="{{ $notification->media_url }}" type="video/mp4"></video>
                                                    @endif
                                                </div>
                                            @endif

                                            {{-- New video_url support --}}
                                            @if($notification->video_url)
                                                <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--line);">
                                                    <video controls style="width: 100%; display: block;"><source src="{{ $notification->video_url }}" type="video/mp4"></video>
                                                </div>
                                            @endif

                                            {{-- New audio_url support --}}
                                            @if($notification->audio_url)
                                                <div style="border-radius: 8px; overflow: hidden; border: 1px solid var(--line);">
                                                    <audio controls style="width: 100%;"><source src="{{ $notification->audio_url }}" type="audio/mpeg"></audio>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class='message-meta' style='margin-top:12px; background: #f8fafc; padding: 12px; border-radius: 10px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; border: 1px solid rgba(0,0,0,0.05);'>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Battery Status</div>
                                            <div style="font-weight: 700; color: {{ $notification->battery_level < 20 ? '#b91c1c' : '#0f172a' }}; font-size: 14px; margin-top: 2px;">
                                                🔋 {{ $notification->battery_level ?? 'N/A' }}{{ $notification->battery_level ? '%' : '' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Signal Strength</div>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 14px; margin-top: 2px;">📶 {{ $notification->signal_status ?? 'N/A' }}</div>
                                        </div>
                                        <div style="grid-column: span 2;">
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Current Coordinates</div>
                                            <div style="font-weight: 700; color: var(--blue); font-size: 13px; margin-top: 2px;">
                                                @if($notification->latitude)
                                                    <a href="/dashboard?student_id={{ $notification->student->student_id ?? $notification->student_id }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 4px;">
                                                        📍 {{ number_format($notification->latitude, 5) }}, {{ number_format($notification->longitude, 5) }} 
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                                                    </a>
                                                @else
                                                    {{ $notification->location ?? 'Location Unavailable' }}
                                                @endif
                                            </div>
                                        </div>

                                        @if($notification->status !== 'resolved')
                                            <div style="grid-column: span 4; margin-top: 8px; padding-top: 12px; border-top: 1px dashed rgba(0,0,0,0.1); display: flex; justify-content: flex-end; gap: 10px;">
                                                {{-- Acknowledged (Mark as Seen) --}}
                                                @if(!$notification->read)
                                                    <form method='POST' action='/notifications/{{ $notification->id }}/acknowledge' style='display:inline;'>
                                                        @csrf
                                                        <button class='action-btn' style="font-size:11px; padding:8px 16px; border-radius: 8px; background: #3b82f6; color: #fff; border: none; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px;" type='submit'>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                            Acknowledged
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Mark as Resolved (Safe) --}}
                                                <form method='POST' action='/notifications/{{ $notification->id }}/resolve' style='display:inline;'>
                                                    @csrf
                                                    <button class='action-btn ack-btn' style="font-size:11px; padding:8px 16px; border-radius: 8px; font-weight: 700; display: flex; align-items: center; gap: 4px;" type='submit'>
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                        Mark as Resolved
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class='message-item' style='background:#f8fafc;border-style:dashed;text-align:center;color:var(--muted);'>No current SOS alerts.</div>
                            @endforelse
                        @else
                            @forelse($notifications->where('type', 'blackout') as $notification)
                                <div class='message-item' style="border-left: 4px solid var(--blue);">
                                    <div class='message-head'>
                                        <p class='message-title'>
                                            Blackout Alert: {{ $notification->student->name ?? 'Unknown Student' }}
                                            <span class='badge-pill' style='background:#dbeafe;color:#1e40af;border-color:#bfdbfe;'>System Offline</span>
                                        </p>
                                        <span class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                                    </div>
                                    {{-- Message Body removed as per request --}}
                                    <div class='message-meta' style='margin-top:12px; background: #f1f5f9; padding: 12px; border-radius: 10px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; border: 1px solid rgba(0,0,0,0.05);'>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Battery Status</div>
                                            <div style="font-weight: 700; color: {{ $notification->battery_level < 20 ? 'var(--red)' : '#0f172a' }}; font-size: 14px; margin-top: 2px;">
                                                🔋 {{ $notification->battery_level ?? 'N/A' }}{{ $notification->battery_level ? '%' : '' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Signal Strength</div>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 14px; margin-top: 2px;">📶 {{ $notification->signal_status ?? 'N/A' }}</div>
                                        </div>
                                        <div style="grid-column: span 2;">
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Current Coordinates</div>
                                            <div style="font-weight: 700; color: var(--blue); font-size: 13px; margin-top: 2px;">
                                                @if($notification->latitude)
                                                    <a href="/dashboard?student_id={{ $notification->student->student_id ?? $notification->student_id }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 4px;">
                                                        📍 {{ number_format($notification->latitude, 5) }}, {{ number_format($notification->longitude, 5) }} 
                                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                                                    </a>
                                                @else
                                                    {{ $notification->location ?? 'Location Unavailable' }}
                                                @endif
                                            </div>
                                        </div>

                                        @if($notification->status !== 'resolved')
                                            <div style="grid-column: span 4; margin-top: 8px; padding-top: 12px; border-top: 1px dashed rgba(0,0,0,0.1); display: flex; justify-content: flex-end; gap: 10px;">
                                                {{-- Acknowledged (Mark as Seen) --}}
                                                @if(!$notification->read)
                                                    <form method='POST' action='/notifications/{{ $notification->id }}/acknowledge' style='display:inline;'>
                                                        @csrf
                                                        <button class='action-btn' style="font-size:11px; padding:8px 16px; border-radius: 8px; background: #3b82f6; color: #fff; border: none; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px;" type='submit'>
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                                            Acknowledged
                                                        </button>
                                                    </form>
                                                @endif

                                                {{-- Mark as Resolved (Safe) --}}
                                                <form method='POST' action='/notifications/{{ $notification->id }}/resolve' style='display:inline;'>
                                                    @csrf
                                                    <button class='action-btn ack-btn' style="font-size:11px; padding:8px 16px; border-radius: 8px; font-weight: 700; display: flex; align-items: center; gap: 4px;" type='submit'>
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                                        Mark as Resolved
                                                    </button>
                                                </form>
                                            </div>
                                        @endif
                                    </div>
                                    </div>
                                </div>
                            @empty
                                <div class='message-item' style='background:#f8fafc;border-style:dashed;text-align:center;color:var(--muted);'>No blackout events.</div>
                            @endforelse
                        @endif
                    @elseif($tab === 'broadcast')
                        @forelse($notifications as $notification)
                            <div class='message-item' style="border-left: 4px solid var(--yellow);">
                                <div class='message-head'>
                                    <p class='message-title'>
                                        Broadcast Notification
                                        <span class='badge-pill' style='background:#fef3c7;color:#92400e;border-color:#fde68a;'>Outbound</span>
                                    </p>
                                    <span class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                                </div>
                                <p class='message-body'>{{ $notification->message }}</p>
                                <div class='message-meta' style='margin-top:8px;'>
                                    @if($notification->class && $notification->class !== 'all') Class: {{ $notification->class }} | @endif
                                    <span class='badge-pill' style="font-size: 10px;">Sent to All</span>
                                </div>
                            </div>
                        @empty
                            <div class='message-item' style='background:#fff;border-color:#cbd5e1;text-align:center;padding:30px;color:var(--muted);'>No broadcast history.</div>
                        @endforelse
                    @else
                        @forelse($notifications as $notification)
                            {{-- Parent Message (from Student) --}}
                            <div class='message-item' style="border-left: 4px solid #cbd5e1;">
                                <div class='message-head'>
                                    <p class='message-title'>
                                        {{ $notification->student->name ?? 'Unknown Student' }} ({{ $notification->student->student_id ?? 'N/A' }})
                                        <span class='badge-pill' style='background:#f1f5f9;color:#475569;'>Inbound Message</span>
                                    </p>
                                    <span class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                                </div>

                                <p class='message-body'>{{ $notification->message }}</p>

                                <div class='message-meta' style='margin-top:10px; display: flex; justify-content: space-between; align-items: center;'>
                                    <span>
                                        @if($notification->class && $notification->class !== 'all') Class: {{ $notification->class }} | @endif
                                        <strong class='badge-pill' id='status-{{ $notification->id}}' style="font-size: 10px;">
                                           {{ ucfirst($notification->status ?? ($notification->read ? 'Read' : 'Pending')) }}
                                        </strong>
                                    </span>
                                    
                                    @if($notification->status !== 'replied')
                                        <div class="reply-wrap">
                                            <button onclick="document.getElementById('reply-form-{{ $notification->id }}').style.display='block'; this.style.display='none';" class="action-btn read-btn" style="font-size: 11px; padding: 4px 10px;">Reply to Student</button>
                                            <form id="reply-form-{{ $notification->id }}" method="POST" action="/notifications/{{ $notification->id }}/reply" style="display:none; width: 100%; margin-top: 10px;">
                                                @csrf
                                                <textarea name="message" required style="width: 100%; border: 1px solid var(--line); border-radius: 8px; padding: 8px; font-size: 13px;" placeholder="Type your response..."></textarea>
                                                <div style="display: flex; gap: 8px; margin-top: 6px;">
                                                    <button type="submit" class="action-btn read-btn" style="font-size: 11px;">Send Reply</button>
                                                    <button type="button" onclick="this.parentElement.parentElement.style.display='none'; this.parentElement.parentElement.previousElementSibling.style.display='block';" class="action-btn" style="background:var(--muted); font-size: 11px;">Cancel</button>
                                                </div>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Threaded Replies (from Admin) --}}
                            @foreach($notification->replies as $reply)
                                <div class='message-item' style="margin-left: 30px; border-left: 4px solid var(--blue); background: #f0f7ff;">
                                    <div class='message-head'>
                                        <p class='message-title'>
                                            Administrator Response
                                            <span class='badge-pill' style='background:#dbeafe;color:#1e40af;'>Outbound Reply</span>
                                        </p>
                                        <span class='message-meta'>{{ \Carbon\Carbon::parse($reply->created_at)->format('n/j/Y, h:i A') }}</span>
                                    </div>
                                    <p class='message-body'>{{ $reply->message }}</p>
                                    <div class='message-meta' style='margin-top:8px;'>
                                        <span class='badge-pill' style="font-size: 10px; background: #e0f2fe;">Sent from Panel</span>
                                    </div>
                                </div>
                            @endforeach
                        @empty
                            <div class='message-item' style='background:#fff;border-color:#cbd5e1;text-align:center;padding:30px;color:var(--muted);'>No messages or replies found.</div>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </main>


    <script>
    function pollDashboardStats() {
        fetch('/api/dashboard/stats')
            .then(res => res.json())
            .then(data => {
                const onlineEl  = document.getElementById('online-count');
                const offlineEl = document.getElementById('offline-count');
                const timeEl    = document.getElementById('latest-time');
                const dateEl    = document.getElementById('latest-date');
                if (onlineEl)  onlineEl.textContent  = data.onlineCount;
                if (offlineEl) offlineEl.textContent = data.offlineCount;
                if (timeEl)    timeEl.textContent    = data.latestTime ?? '—';
                if (dateEl)    dateEl.textContent    = data.latestDate ?? '—';
            })
            .catch(err => console.error('Dashboard poll error:', err));
    }

    document.addEventListener('DOMContentLoaded', function () {
        pollDashboardStats();
        setInterval(pollDashboardStats, 10000); // Sync every 10s
    });
    </script>
</body>
</html>