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

        /* --- REVERTED TAB UI --- */
        .tabs {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            border: 1px solid var(--line);
            border-radius: 9px;
            overflow: hidden;
            background: #f1f5f9;
            margin-top: 10px;
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

        /* --- RESTORED LEGACY STYLES --- */
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
        .sub-tab:hover { color: var(--blue); }
        .sub-tab.active {
            background: #fff;
            color: var(--blue);
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        /* --- MESSENGER-STYLE CHAT UI --- */
        .chat-container {
            margin-bottom: 30px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
        }

        .chat-header {
            background: #f8fafc;
            padding: 12px 20px;
            border-bottom: 1px solid var(--line);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .chat-scroll-area {
            max-height: 500px;
            overflow-y: auto;
            padding: 24px;
            background: #fff;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .chat-bubble {
            max-width: 75%;
            padding: 10px 14px;
            font-size: 14px;
            line-height: 1.5;
            position: relative;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
        }

        .bubble-left {
            align-self: flex-start;
            background: #f1f5f9;
            color: #1e293b;
            border-radius: 4px 18px 18px 18px;
        }

        .bubble-right {
            align-self: flex-end;
            background: #f97316; /* Orange */
            color: #fff;
            border-radius: 18px 18px 4px 18px;
        }

        .chat-timestamp {
            font-size: 10px;
            margin-top: 4px;
            opacity: 0.7;
            display: block;
            text-align: inherit;
        }

        .chat-input-area {
            background: #f8fafc;
            padding: 16px 20px;
            border-top: 1px solid var(--line);
        }

        .chat-input-row {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: 4px 4px 4px 18px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
        }

        .chat-input-row input {
            flex: 1;
            border: none;
            outline: none;
            padding: 8px 0;
            font-size: 14px;
            background: transparent;
        }

        .chat-send-btn {
            background: #6366f1;
            color: #fff;
            border: none;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        .chat-send-btn:hover { background: #4f46e5; }
        /* --- MESSENGER UI INTEGRATION --- */
        .messenger-wrapper {
            display: flex;
            height: 650px;
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            overflow: hidden;
            margin-top: 10px;
        }

        /* LEFT PANEL — Student List */
        .student-panel {
            width: 320px;
            background: #fff;
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
        }
        .panel-header {
            padding: 14px 16px 10px;
            border-bottom: 1px solid var(--line);
        }
        .panel-title { font-size: 16px; font-weight: 800; margin-bottom: 10px; }
        
        .search-box {
            display: flex; align-items: center;
            background: #f1f5f9; border-radius: 20px;
            padding: 8px 14px; gap: 8px;
            margin-bottom: 10px;
        }
        .search-box input {
            background: none; border: none; outline: none;
            font-size: 13px; color: var(--text); width: 100%;
        }

        .class-tabs {
            display: flex; gap: 4px;
            background: #f1f5f9; border: 1px solid var(--line);
            border-radius: 8px; padding: 3px;
        }
        .class-tab {
            flex: 1; text-align: center; padding: 5px 4px;
            border-radius: 6px; font-size: 11px; font-weight: 700;
            color: var(--muted); cursor: pointer; transition: all 0.15s;
        }
        .class-tab.active { background: #fff; color: var(--blue); box-shadow: 0 1px 3px rgba(0,0,0,0.07); }

        .student-list { flex: 1; overflow-y: auto; }
        .alpha-heading {
            padding: 6px 16px 4px;
            font-size: 10px; font-weight: 900; letter-spacing: 1px;
            text-transform: uppercase; color: var(--blue);
            background: #eff6ff; border-bottom: 1px solid #dbeafe;
        }
        .student-row {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 16px; cursor: pointer;
            border-bottom: 1px solid #f8fafc; transition: background 0.15s;
        }
        .student-row:hover { background: #f8fafc; }
        .student-row.active { background: #eff6ff; border-left: 3px solid var(--blue); }

        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: #dbeafe; color: #1d4ed8;
            font-weight: 800; font-size: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; position: relative;
        }
        .avatar.female { background: #fce7f3; color: #be185d; }
        .unread-dot {
            position: absolute; top: 0; right: 0;
            width: 10px; height: 10px;
            background: var(--blue); border-radius: 50%;
            border: 2px solid #fff;
        }
        .student-name { font-weight: 700; font-size: 13px; }
        .student-sub { font-size: 11px; color: var(--muted); }

        /* RIGHT PANEL — Chat */
        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: #f8fafc;
            overflow: hidden;
        }
        .chat-empty {
            flex: 1; display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            color: var(--muted); text-align: center; gap: 10px;
        }
        .chat-header {
            background: #fff; border-bottom: 1px solid var(--line);
            padding: 10px 20px;
            display: flex; align-items: center; gap: 12px;
            flex-shrink: 0;
        }
        .chat-header-name { font-weight: 800; font-size: 14px; }
        .chat-header-sub { font-size: 11px; color: var(--muted); }

        .chat-messages {
            flex: 1; overflow-y: auto;
            padding: 20px;
            display: flex; flex-direction: column; gap: 6px;
        }
        .date-sep { text-align: center; margin: 15px 0 10px; }
        .date-sep span {
            background: #e2e8f0; color: #64748b;
            font-size: 10px; font-weight: 800;
            padding: 4px 12px; border-radius: 999px;
            text-transform: uppercase;
        }

        .bubble-wrap { display: flex; gap: 8px; margin: 4px 0; align-items: flex-end; }
        .bubble-wrap.admin { flex-direction: row-reverse; }
        .bubble-content { display: flex; flex-direction: column; max-width: 75%; }
        .bubble-wrap.admin .bubble-content { align-items: flex-end; }
        
        .bubble {
            padding: 8px 14px; border-radius: 18px;
            font-size: 14px; line-height: 1.4;
        }
        .bubble.student { background: #fff; color: var(--text); border: 1px solid #e2e8f0; border-bottom-left-radius: 4px; }
        .bubble.admin { background: var(--blue); color: #fff; border-bottom-right-radius: 4px; }
        .bubble.sos-alert { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .bubble-time { font-size: 10px; margin-top: 4px; color: var(--muted); }

        .chat-input-area {
            background: #fff; border-top: 1px solid var(--line);
            padding: 12px 20px; display: flex; align-items: flex-end; gap: 10px;
        }
        .input-wrap {
            flex: 1; background: #f1f5f9; border: 1px solid var(--line);
            border-radius: 20px; padding: 8px 16px;
        }
        .input-wrap textarea {
            width: 100%; background: none; border: none; outline: none;
            font-size: 14px; resize: none; line-height: 1.4;
            max-height: 100px; font-family: inherit;
        }
        .send-btn {
            width: 40px; height: 40px; border-radius: 50%;
            background: var(--blue); color: #fff; border: none;
            cursor: pointer; display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .send-btn:disabled { background: #cbd5e1; }
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
                    <div style="background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px; margin-bottom: 24px;">
                        <h3 style="margin: 0 0 4px 0; font-size: 16px; font-weight: 800;">Send New Broadcast</h3>
                        <p style="margin: 0 0 16px 0; font-size: 13px; color: var(--muted);">Direct one-way announcement to students</p>

                        <form method="POST" action="/notifications/send" style="display: grid; grid-template-columns: 1fr 2fr auto; gap: 16px; align-items: flex-end;">
                            @csrf
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 6px;">Target Audience</label>
                                <select name="target" required style="width: 100%; padding: 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; background: #f8fafc;">
                                    <option value="all">All Students</option>
                                    <option value="2026">Class 2026</option>
                                    <option value="2027">Class 2027</option>
                                    <option value="2028">Class 2028</option>
                                </select>
                            </div>
                            <div>
                                <label style="display: block; font-size: 11px; font-weight: 800; color: var(--muted); text-transform: uppercase; margin-bottom: 6px;">Message Content</label>
                                <input type="text" name="message" required placeholder="Type your announcement here..." style="width: 100%; padding: 10px; border: 1px solid var(--line); border-radius: 8px; font-size: 13px; background: #f8fafc;">
                            </div>
                            <button type="submit" style="background: var(--blue); color: #fff; border: none; padding: 10px 24px; border-radius: 8px; font-weight: 700; font-size: 13px; cursor: pointer; transition: all 0.2s;">
                                Send Announcement
                            </button>
                        </form>
                    </div>

                    <div class='broadcast-info'>
                        <strong>Broadcast Notifications History</strong>
                        <p>Detailed log of all outbound school-wide announcements.</p>
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

                                    @php
                                        // Prefer current student telemetry over static notification data
                                        $currentBattery = $notification->student->battery_level ?? $notification->battery_level;
                                        $currentSignal = $notification->student->signal_status ?? $notification->signal_status;
                                        $currentLat = $notification->student->latitude ?? $notification->latitude;
                                        $currentLng = $notification->student->longitude ?? $notification->longitude;
                                    @endphp

                                    <div class='message-meta' style='margin-top:12px; background: #f8fafc; padding: 12px; border-radius: 10px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; border: 1px solid rgba(0,0,0,0.05);'>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Live Battery</div>
                                            <div style="font-weight: 700; color: {{ $currentBattery < 20 ? '#b91c1c' : '#0f172a' }}; font-size: 14px; margin-top: 2px;">
                                                🔋 {{ $currentBattery ?? 'N/A' }}{{ $currentBattery ? '%' : '' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Live Signal</div>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 14px; margin-top: 2px;">📶 {{ $currentSignal ?? 'N/A' }}</div>
                                        </div>
                                        <div style="grid-column: span 2;">
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Last Known Location</div>
                                            <div style="font-weight: 700; color: var(--blue); font-size: 13px; margin-top: 2px;">
                                                @if($currentLat)
                                                    <a href="/dashboard?student_id={{ $notification->student->student_id ?? $notification->student_id }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 4px;">
                                                        📍 {{ number_format($currentLat, 5) }}, {{ number_format($currentLng, 5) }} 
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
                                    @php
                                        // Prefer current student telemetry over static notification data
                                        $currentBattery = $notification->student->battery_level ?? $notification->battery_level;
                                        $currentSignal = $notification->student->signal_status ?? $notification->signal_status;
                                        $currentLat = $notification->student->latitude ?? $notification->latitude;
                                        $currentLng = $notification->student->longitude ?? $notification->longitude;
                                    @endphp

                                    <div class='message-meta' style='margin-top:12px; background: #f1f5f9; padding: 12px; border-radius: 10px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; border: 1px solid rgba(0,0,0,0.05);'>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Live Battery</div>
                                            <div style="font-weight: 700; color: {{ $currentBattery < 20 ? 'var(--red)' : '#0f172a' }}; font-size: 14px; margin-top: 2px;">
                                                🔋 {{ $currentBattery ?? 'N/A' }}{{ $currentBattery ? '%' : '' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Live Signal</div>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 14px; margin-top: 2px;">📶 {{ $currentSignal ?? 'N/A' }}</div>
                                        </div>
                                        <div style="grid-column: span 2;">
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 800; color: #64748b; letter-spacing: 0.5px;">Last Known Location</div>
                                            <div style="font-weight: 700; color: var(--blue); font-size: 13px; margin-top: 2px;">
                                                @if($currentLat)
                                                    <a href="/dashboard?student_id={{ $notification->student->student_id ?? $notification->student_id }}" style="text-decoration: none; color: inherit; display: flex; align-items: center; gap: 4px;">
                                                        📍 {{ number_format($currentLat, 5) }}, {{ number_format($currentLng, 5) }} 
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
                        {{-- MESSENGER-STYLE STUDENT CHAT --}}
                        <div class="messenger-wrapper">
                            {{-- Sidebar --}}
                            <aside class="student-panel">
                                <div class="panel-header">
                                    <div class="panel-title">Student Chats</div>
                                    <div class="search-box">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                        <input type="text" id="student-search" placeholder="Search students..." autocomplete="off">
                                    </div>
                                    <div class="class-tabs">
                                        <div class="class-tab active" data-class="all">All</div>
                                        <div class="class-tab" data-class="2026">2026</div>
                                        <div class="class-tab" data-class="2027">2027</div>
                                        <div class="class-tab" data-class="2028">2028</div>
                                    </div>
                                </div>
                                <div class="student-list" id="student-list-container">
                                    @php $currentLetter = ''; @endphp
                                    @forelse($sidebarStudents as $student)
                                        @php
                                            $letter = strtoupper(substr($student->name, 0, 1));
                                            $isFemale = strtolower($student->gender ?? '') === 'female';
                                            $unread = \App\Models\Notification::where(function($q) use ($student) {
                                                    $q->where('student_id', $student->id)
                                                      ->orWhere('student_id', $student->student_id);
                                                })
                                                ->where('sender_type', 'student')
                                                ->where('read', false)
                                                ->count();
                                        @endphp
                                        @if($letter !== $currentLetter)
                                            @php $currentLetter = $letter; @endphp
                                            <div class="alpha-heading alpha-row">{{ $letter }}</div>
                                        @endif
                                        <div class="student-row" 
                                             data-id="{{ $student->id }}" 
                                             data-name="{{ strtolower($student->name) }}" 
                                             data-class="{{ $student->class }}"
                                             data-display-name="{{ $student->name }}"
                                             data-student-id="{{ $student->student_id }}"
                                             data-gender="{{ strtolower($student->gender ?? '') }}">
                                            <div class="avatar {{ $isFemale ? 'female' : '' }}">
                                                {{ strtoupper(substr($student->name, 0, 1)) }}
                                                @if($unread > 0)<div class="unread-dot"></div>@endif
                                            </div>
                                            <div class="student-info">
                                                <div class="student-name">{{ $student->name }}</div>
                                                <div class="student-sub">{{ $student->student_id }} · {{ $student->class }}</div>
                                            </div>
                                        </div>
                                    @empty
                                        <div style="padding: 40px 20px; text-align: center; color: var(--muted); font-size: 13px;">No students found.</div>
                                    @endforelse
                                </div>
                            </aside>

                            {{-- Chat Panel --}}
                            <section class="chat-panel">
                                <div class="chat-empty" id="chat-empty">
                                    <div style="background: #eff6ff; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--blue)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    </div>
                                    <p style="font-weight: 800; color: #0f172a; font-size: 16px;">Select a student</p>
                                    <p style="font-size: 13px; color: var(--muted);">Click on a student to see message history</p>
                                </div>

                                <div class="chat-header" id="chat-header" style="display:none;">
                                    <div class="avatar" id="chat-header-avatar"></div>
                                    <div class="student-info">
                                        <div class="chat-header-name" id="chat-header-name">Student Name</div>
                                        <div class="chat-header-sub" id="chat-header-sub">ID · Class</div>
                                    </div>
                                </div>

                                <div class="chat-messages" id="chat-messages" style="display:none;"></div>

                                <div class="chat-input-area" id="chat-input-area" style="display:none;">
                                    <div class="input-wrap">
                                        <textarea id="msg-input" rows="1" placeholder="Type a message..." oninput="this.style.height='auto';this.style.height=this.scrollHeight+'px'"></textarea>
                                    </div>
                                    <button class="send-btn" id="send-btn" title="Send message">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                    </button>
                                </div>
                            </section>
                        </div>
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

        // Global click listener for student rows (delegation)
        document.addEventListener('click', function(e) {
            const row = e.target.closest('.student-row');
            if (row) {
                try {
                    handleStudentSelection(row);
                } catch (err) {
                    console.error('Selection error:', err);
                }
            }
        });
    });

    // --- MESSENGER LOGIC ---
    let activeStudentId = null;
    let activeStudentName = '';
    let activeIsFemale = false;
    let msgPollTimer = null;

    // Search
    document.getElementById('student-search')?.addEventListener('input', function() {
        filterStudentList();
    });

    // Class tabs
    document.querySelectorAll('.class-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.class-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
            filterStudentList();
        });
    });

    function filterStudentList() {
        const q = document.getElementById('student-search').value.toLowerCase();
        const cls = document.querySelector('.class-tab.active')?.dataset.class || 'all';
        
        document.querySelectorAll('.student-row').forEach(row => {
            const nameMatch = row.dataset.name.includes(q);
            const classMatch = cls === 'all' || row.dataset.class === cls;
            row.style.display = (nameMatch && classMatch) ? 'flex' : 'none';
        });

        // Hide alpha headings if no visible students in that group
        document.querySelectorAll('.alpha-row').forEach(heading => {
            let hasVisible = false;
            let next = heading.nextElementSibling;
            while (next && !next.classList.contains('alpha-row')) {
                if (next.style.display !== 'none') { hasVisible = true; break; }
                next = next.nextElementSibling;
            }
            heading.style.display = hasVisible ? 'block' : 'none';
        });
    }

    // Renamed to handleStudentSelection for better clarity with the delegation
    function handleStudentSelection(row) {
        const id = row.getAttribute('data-id');
        const name = row.getAttribute('data-display-name') || 'Student';
        const studentId = row.getAttribute('data-student-id') || 'N/A';
        const cls = row.getAttribute('data-class') || 'N/A';
        const gender = row.getAttribute('data-gender') || '';

        // UI Updates
        document.querySelectorAll('.student-row').forEach(r => r.classList.remove('active'));
        row.classList.add('active');

        const dot = row.querySelector('.unread-dot');
        if (dot) dot.remove();

        activeStudentId = id;
        activeStudentName = name;
        activeIsFemale = (gender === 'female');

        // Header Updates
        const avatarEl = document.getElementById('chat-header-avatar');
        if (avatarEl) {
            avatarEl.textContent = name.charAt(0).toUpperCase();
            avatarEl.className = 'avatar' + (activeIsFemale ? ' female' : '');
        }
        
        const nameEl = document.getElementById('chat-header-name');
        if (nameEl) nameEl.textContent = name;

        const subEl = document.getElementById('chat-header-sub');
        if (subEl) subEl.textContent = studentId + ' · Class ' + cls;

        // Panel Visibility
        const emptyState = document.getElementById('chat-empty');
        if (emptyState) emptyState.style.display = 'none';

        const headerPanel = document.getElementById('chat-header');
        if (headerPanel) headerPanel.style.display = 'flex';

        const msgsPanel = document.getElementById('chat-messages');
        if (msgsPanel) {
            msgsPanel.style.display = 'flex';
            msgsPanel.innerHTML = '<div style="text-align:center;padding:40px;color:#64748b;font-size:13px;">Loading conversation...</div>';
        }

        const inputPanel = document.getElementById('chat-input-area');
        if (inputPanel) inputPanel.style.display = 'flex';

        loadMessages(true);

        clearInterval(msgPollTimer);
        msgPollTimer = setInterval(() => loadMessages(false), 4000);
    }

    // Helper for onclick-style calls if still in HTML
    function openChat(row) {
        handleStudentSelection(row);
    }

    async function loadMessages(forceScroll) {
        if (!activeStudentId) return;
        try {
            const res = await fetch(`/messages/${activeStudentId}/json`);
            if (!res.ok) throw new Error('Server error ' + res.status);
            const data = await res.json();
            renderMessages(data.messages, forceScroll);
        } catch (e) { 
            console.error('Poll error:', e);
            const container = document.getElementById('chat-messages');
            if (container) container.innerHTML = '<div style="color:red;padding:20px;text-align:center;">Error loading history: ' + e.message + '</div>';
        }
    }

    function renderMessages(messages, forceScroll) {
        try {
            const container = document.getElementById('chat-messages');
            if (!container) return;
            const wasAtBottom = container.scrollHeight - container.scrollTop - container.clientHeight < 80;
            
            let html = '';
            let prevDate = '';

            if (!messages || !Array.isArray(messages) || messages.length === 0) {
                html = '<div style="text-align:center;color:#64748b;font-size:13px;margin-top:40px;">No messages yet. Say hello! 👋</div>';
            } else {
                messages.forEach(msg => {
                    const d = new Date(msg.created_at);
                    const dateStr = d.toLocaleDateString('en-US', { month:'short', day:'numeric', year:'numeric' });
                    const timeStr = d.toLocaleTimeString('en-US', { hour:'2-digit', minute:'2-digit' });
                    const isAdmin = msg.sender_type === 'admin';
                    const isEmergency = msg.type === 'sos' || msg.type === 'blackout';

                    if (dateStr !== prevDate) {
                        html += `<div class="date-sep"><span>${dateStr}</span></div>`;
                        prevDate = dateStr;
                    }

                    const bubClass = isAdmin ? 'admin' : 'student';
                    const sosClass = isEmergency ? ' sos-alert' : '';
                    const prefix = isEmergency ? '<b>🚨 ALERT:</b> ' : '';

                    html += `
                        <div class="bubble-wrap ${bubClass}">
                            <div class="bubble-content">
                                <div class="bubble ${bubClass}${sosClass}">${prefix}${escHtml(msg.message)}</div>
                                <div class="bubble-time">${timeStr}</div>
                            </div>
                        </div>`;
                });
            }

            container.innerHTML = html;
            if (forceScroll || wasAtBottom) {
                container.scrollTop = container.scrollHeight;
            }
        } catch (renderErr) {
            console.error('Render error:', renderErr);
            const container = document.getElementById('chat-messages');
            if (container) container.innerHTML = '<div style="color:red;padding:20px;text-align:center;">Rendering error: ' + renderErr.message + '</div>';
        }
    }

    async function sendMessage() {
        const input = document.getElementById('msg-input');
        const text = input.value.trim();
        if (!text || !activeStudentId) return;

        const btn = document.getElementById('send-btn');
        btn.disabled = true;

        const fd = new FormData();
        fd.append('message', text);
        fd.append('_token', '{{ csrf_token() }}');

        try {
            await fetch(`/messages/new/${activeStudentId}`, { method: 'POST', body: fd });
            input.value = '';
            input.style.height = 'auto';
            await loadMessages(true);
        } catch (e) { console.error('Send error:', e); }
        finally { btn.disabled = false; input.focus(); }
    }

    function escHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    document.getElementById('send-btn')?.addEventListener('click', sendMessage);
    document.getElementById('msg-input')?.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); }
    });
    </script>
</body>
</html>