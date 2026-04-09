<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'/>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>Notifications Dashboard</title>
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
        .topbar{background:#2563eb;
            height:72px;
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:0 22px;
            box-shadow:0 1px 2px rgba(15,23,42,.1);
            border-bottom:1px solid var(--line);
        }
        .top-left{
            display:flex;
            align-items:center;
            gap:10px;
        }
        .top-left a{
            color:#fff;
            text-decoration:none;
            font-weight:600;
            display:flex;
            align-items:center;
            gap:8px;
            font-size:14px;
        }
        .brand{
            display:flex;
            align-items:center;
            gap:10px;
        }

        .brand-icon{
            width:34px;
            height:34px;
            border-radius:10px;
            background:transparent;
            display:flex;
            align-items:center;
            justify-content:center;
            overflow:hidden;
        }

        .brand-icon img{
            width:100%;
            height:100%;
            object-fit:contain;
            display:block;
        }

        .brand-text {
            font-size: 19px;
            font-weight: 800;
            margin: 0;
            color: #fff;
        }

        .subtitle {
            color: #fff;
            font-size: 13px;
            margin: 2px 0 0;
        }

        .container {
            max-width: 100vw;
            width: 100%;
            margin: 0;
            padding: 10px 18px 20px;
        }

        .body-wrap {
            min-width: 100vw;
        }

        .section {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 14px;
            margin-bottom: 14px;
        }

        .section-padding {
            padding: 16px 18px;
        }

        .section-title {
            margin: 0;
            font-size: 21px;
            font-weight: 700;
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

        .overview-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 12px;
            margin-top: 12px;
        }

        @media (max-width: 940px) {
            .overview-grid {
                grid-template-columns: 1fr;
            }
        }

        .stat-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 15px;
        }

        .stat-subtitle {
            font-size: 13px;
            color: var(--muted);
            margin: 0 0 6px;
            font-weight: 650;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 900;
            margin: 0;
            color: #0f172a;
        }

        .stat-detail {
            font-size: 13px;
            color: #94a3b8;
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
    <header class='topbar'>
        <div class='top-left'>
            <a href='/dashboard'>← Back</a>
            <div class='brand'>
                <div class='brand-icon' aria-hidden='true'>
                    <img src="{{ asset('images/gtrack.png') }}" alt="G!Track logo" />
                </div>
                <div>
                    <div class='brand-text'>Notifications Dashboard</div>
                    <div class='subtitle'>Messages, SOS Alerts & System Status</div>
                </div>
            </div>
        </div>
        <a class='logout' href='/logout'>Logout</a>
    </header>

    <main class='container'>
        @if(session('success'))
            <div style='margin-bottom:12px;padding:10px 14px;border:1px solid #34d399;background:#d1fae5;color:#065f46;border-radius:10px; font-weight:600;'>
                {{ session('success') }}
            </div>
        @endif

        <div class='section'>
            <div class='section-padding'>
                <h2 class='section-title'>Notifications Overview</h2>
                <div class='overview-grid'>
                    <div class='stat-card'>
                        <p class='stat-subtitle'>Total Messages</p>
                        <p class='stat-value'>{{ $stats['total'] }}</p>
                        <p class='stat-detail'>All student notifications</p>
                    </div>
                    <div class='stat-card'>
                        <p class='stat-subtitle'>Unread Messages</p>
                        <p class='stat-value' style='color:var(--blue);'>{{ $stats['unread'] }}</p>
                        <p class='stat-detail'>Requires attention</p>
                    </div>
                    <div class='stat-card'>
                        <p class='stat-subtitle'>Emergency Alerts</p>
                        <p class='stat-value' style='color:var(--red);'>{{ $stats['sos'] }}</p>
                        <p class='stat-detail'>Pending alerts</p>
                    </div>
                    <div class='stat-card'>
                        <p class='stat-subtitle'>Broadcast Messages</p>
                        <p class='stat-value' style='color:var(--yellow);'>{{ $stats['total'] - $stats['sos'] }}</p>
                        <p class='stat-detail'>Urgent messages</p>
                    </div>
                </div>
            </div>
            <div class='filter-block'>
                <span class='filter-label'>Filter by Class</span>
                <div class='select-wrap'>
                    <span style='color:#475569;font-size:15px;'>:</span>
                    <select id='class-filter' onchange="location.href='?class=' + encodeURIComponent(this.value) + '&tab={{ $tab }}'">
                        <option value='all' {{ $class === 'all' ? 'selected' : '' }}>All Classes</option>
                        <option value='Class 2026' {{ $class === 'Class 2026' ? 'selected' : '' }}>Class 2026</option>
                        <option value='Class 2027' {{ $class === 'Class 2027' ? 'selected' : '' }}>Class 2027</option>
                        <option value='Class 2028' {{ $class === 'Class 2028' ? 'selected' : '' }}>Class 2028</option>
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
                                            SOS Alert 
                                            @if($notification->status === 'resolved')
                                                <span class='badge-pill' style='background:#f1f5f9;color:#64748b;border-color:#e2e8f0;'>I am Safe (Resolved)</span>
                                            @else
                                                <span class='badge-pill' style='background:#fee2e2;color:#991b1b;border-color:#fecaca;'>Active Help Needed</span>
                                            @endif
                                        </p>
                                        <span class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                                    </div>
                                    <p class='message-body' style="font-weight: 600; color: {{ $notification->status === 'resolved' ? 'var(--muted)' : '#b91c1c' }};">{{ $notification->message }}</p>
                                    
                                    @if($notification->media_url)
                                        <div style="margin-top: 12px; border-radius: 8px; overflow: hidden; border: 1px solid var(--line);">
                                            @if(Str::endsWith($notification->media_url, ['.mp3', '.wav']))
                                                <audio controls style="width: 100%;"><source src="{{ $notification->media_url }}" type="audio/mpeg"></audio>
                                            @else
                                                <video controls style="width: 100%; display: block;"><source src="{{ $notification->media_url }}" type="video/mp4"></video>
                                            @endif
                                        </div>
                                    @endif

                                    <div class='message-meta' style='margin-top:12px; padding: 10px; background: #f8fafc; border-radius: 8px;'>
                                        <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 20px;">
                                            <div style="flex: 1;">
                                                <div style="font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Telemetry</div>
                                                <div style="font-size: 12px; margin-top: 4px;">
                                                    🔋 {{ $notification->battery_level ?? 'N/A' }}% | 📶 {{ $notification->signal_status ?? 'N/A' }}
                                                </div>
                                            </div>
                                            <div style="flex: 1 text-align: right;">
                                                <div style="font-size: 10px; font-weight: 700; color: var(--muted); text-transform: uppercase;">Location</div>
                                                <div style="font-size: 12px; margin-top: 4px; color: var(--blue);">
                                                    @if($notification->latitude)
                                                        <a href="https://www.google.com/maps?q={{ $notification->latitude }},{{ $notification->longitude }}" target="_blank" style="text-decoration: none; color: inherit;">
                                                            📍 {{ number_format($notification->latitude, 5) }}, {{ number_format($notification->longitude, 5) }} ↗
                                                        </a>
                                                    @else
                                                        {{ $notification->location ?? 'N/A' }}
                                                    @endif
                                                </div>
                                            </div>
                                            @if($notification->status !== 'resolved')
                                                <div class='message-actions' style="margin:0;">
                                                    <form method='POST' action='/notifications/{{ $notification->id }}/acknowledge' style='display:inline;' >
                                                        @csrf
                                                        <button class='action-btn ack-btn' style="font-size:11px; padding:6px 12px;" type='submit'>Resolved</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
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
                                            Blackout Alert
                                            <span class='badge-pill' style='background:#dbeafe;color:#1e40af;border-color:#bfdbfe;'>System Offline</span>
                                        </p>
                                        <span class='message-meta'>{{ \Carbon\Carbon::parse($notification->created_at)->format('n/j/Y, h:i A') }}</span>
                                    </div>
                                    <p class='message-body'>{{ $notification->message }}</p>
                                    <div class='message-meta' style='margin-top:12px; background: #f1f5f9; padding: 10px; border-radius: 8px; display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px;'>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b;">Battery</div>
                                            <div style="font-weight: 700; color: {{ $notification->battery_level < 20 ? 'var(--red)' : '#0f172a' }};">
                                                {{ $notification->battery_level ?? 'N/A' }}{{ $notification->battery_level ? '%' : '' }}
                                            </div>
                                        </div>
                                        <div>
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b;">Signal</div>
                                            <div style="font-weight: 700; color: #0f172a;">{{ $notification->signal_status ?? 'N/A' }}</div>
                                        </div>
                                        <div style="grid-column: span 2;">
                                            <div style="font-size: 10px; text-transform: uppercase; font-weight: 700; color: #64748b;">Current Location</div>
                                            <div style="font-weight: 700; color: #0f172a; font-size: 11px;">
                                                @if($notification->latitude)
                                                    <a href="https://www.google.com/maps?q={{ $notification->latitude }},{{ $notification->longitude }}" target="_blank" style="text-decoration: none; color: inherit;">
                                                        📍 {{ $notification->latitude }}, {{ $notification->longitude }}
                                                    </a>
                                                @else
                                                    {{ $notification->location ?? 'N/A' }}
                                                @endif
                                            </div>
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
                            <div class='message-item' style="{{ $notification->type === 'admin_reply' ? 'margin-left: 30px; border-left: 4px solid var(--blue); background: #f0f7ff;' : 'border-left: 4px solid #cbd5e1;' }}">
                                <div class='message-head'>
                                    <p class='message-title'>
                                        {{ $notification->type === 'admin_reply' ? 'Administrator Response' : 'Student Message' }}
                                        @if($notification->type === 'admin_reply')
                                            <span class='badge-pill' style='background:#dbeafe;color:#1e40af;'>Outbound Reply</span>
                                        @else
                                            <span class='badge-pill' style='background:#f1f5f9;color:#475569;'>Inbound Message</span>
                                        @endif
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
                                    
                                    @if($notification->type === 'student_message' && $notification->status !== 'replied')
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
                        @empty
                            <div class='message-item' style='background:#fff;border-color:#cbd5e1;text-align:center;padding:30px;color:var(--muted);'>No messages or replies found.</div>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>
    </main>

</body>
</html>