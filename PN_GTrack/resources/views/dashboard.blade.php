<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'GTrack') }} - Dashboard</title>
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
                <div class="icon-btn" aria-label="Notifications">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2Z" fill="#0f172a"/>
                        <path d="M18 16v-5c0-3.07-1.63-5.64-4.5-6.32V4a1.5 1.5 0 0 0-3 0v.68C7.63 5.36 6 7.92 6 11v5l-2 2h16l-2-2Z" fill="#0f172a" opacity=".9"/>
                    </svg>
                    <span class="badge">3</span>
                </div>
                <div class="icon-btn" aria-label="Settings">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19.14 12.94c.04-.31.06-.63.06-.94s-.02-.63-.06-.94l2.03-1.58a.5.5 0 0 0 .12-.64l-1.92-3.32a.5.5 0 0 0-.6-.22l-2.39.96a7.06 7.06 0 0 0-1.63-.94l-.36-2.54A.5.5 0 0 0 13.5 1h-3a.5.5 0 0 0-.49.42l-.36 2.54c-.58.23-1.12.54-1.63.94l-2.39-.96a.5.5 0 0 0-.6.22L2.11 7.46a.5.5 0 0 0 .12.64l2.03 1.58c-.04.31-.06.63-.06.94s.02.63.06.94L2.23 14.52a.5.5 0 0 0-.12.64l1.92 3.32c.13.22.39.3.6.22l2.39-.96c.5.4 1.05.71 1.63.94l.36 2.54c.04.24.25.42.49.42h3c.24 0 .45-.18.49-.42l.36-2.54c.58-.23 1.12-.54 1.63-.94l2.39.96c.21.08.47 0 .6-.22l1.92-3.32a.5.5 0 0 0-.12-.64l-2.03-1.58ZM12 15.5A3.5 3.5 0 1 1 12 8.5a3.5 3.5 0 0 1 0 7Z" fill="#0f172a" opacity=".85"/>
                    </svg>
                </div>
                <a class="logout" href="{{ url('/') }}">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M10 17v-2H3v-6h7V7l5 5-5 5Z" fill="#0f172a"/>
                        <path d="M14 3h7v18h-7v-2h5V5h-5V3Z" fill="#0f172a" opacity=".6"/>
                    </svg>
                    Logout
                </a>
            </div>
        </header>

        <main class="container">
            <div class="page-title">
                <h1>System Status Monitoring</h1>
                <p>Real-time overview of student tracking system</p>
            </div>

            <section class="cards">
                <!-- Online Students -->
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

                <!-- Offline Students -->
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

                <!-- Latest Update -->
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
        </main>
    </body>
</html>

