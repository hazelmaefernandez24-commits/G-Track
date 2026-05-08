<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>G!Track - @yield('title', 'Admin Dashboard')</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    @stack('styles')

    <style>
        :root {
            --sidebar-bg: #0d47e6ff; /* Deep premium blue */
            --sidebar-hover: #6180d0ff;
            --sidebar-active: #5c8bf1ff; /* Bright blue for active item */
            --bg-color: #F8FAFC;
            --text-main: #0F172A;
            --text-muted: #64748B;
            --border-color: #E2E8F0;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            
            --online: #22C55E;
            --offline: #EF4444;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: 260px;
            background-color: var(--sidebar-bg);
            color: #FFFFFF;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            transition: all 0.3s ease;
            position: fixed;
            height: 100vh;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .brand-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background-color: transparent;
        }

        .brand-icon img {
            width: 130%;
            height: 130%;
            object-fit: contain;
            display: block;
        }

        .brand-text h2 {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.2;
        }

        .brand-text span {
            font-size: 12px;
            color: #b6b7bbff;
            font-weight: 500;
        }

        .sidebar-nav {
            padding: 24px 16px;
            flex: 1;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #CBD5E1;
            text-decoration: none;
            border-radius: 10px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
        }

        .nav-item:hover {
            background-color: var(--sidebar-hover);
            color: #FFFFFF;
        }

        .nav-item.active {
            background-color: var(--sidebar-active);
            color: #FFFFFF;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
        }

        .nav-item i {
            width: 20px;
            height: 20px;
        }

        .sidebar-footer {
            padding: 20px 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        /* --- MAIN CONTENT --- */
        .main-wrapper {
            flex: 1;
            margin-left: 260px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* Top Header (Inside Main Wrapper) */
        .top-header {
            height: 80px;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #FFFFFF;
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .page-title h1 {
            font-size: 22px;
            font-weight: 700;
            color: var(--text-main);
        }

        .page-title p {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 4px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-btn {
            position: relative;
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: all 0.2s;
        }

        .notification-btn:hover {
            background-color: #F1F5F9;
            color: var(--text-main);
        }

        .notification-badge {
            position: absolute;
            top: 2px;
            right: 2px;
            background-color: var(--offline);
            color: white;
            font-size: 10px;
            font-weight: 700;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #FFFFFF;
        }

        /* Content Area */
        .content-area {
            padding: 32px 40px;
            flex: 1;
        }

        /* Global Card Styles */
        .card {
            background: #FFFFFF;
            border: 1px solid var(--border-color);
            border-radius: 16px;
            padding: 24px;
            box-shadow: var(--card-shadow);
        }

        /* SOS Banner */
        #sos-banner {
            display: none;
            background: #DC2626;
            color: #FFFFFF;
            padding: 12px 24px;
            font-size: 14px;
            font-weight: 600;
            text-align: center;
            animation: sosPulse 1s infinite;
        }
        @keyframes sosPulse { 
            0%, 100% { background: #DC2626; } 
            50% { background: #B91C1C; } 
        }

    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="brand-icon">
                <img src="{{ asset('images/gtrack.png') }}" alt="G!Track logo">
            </div>
            <div class="brand-text">
            
                <h2>G!Track</h2>
                <span>ADMIN SYSTEM</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="/dashboard" class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
                <i data-lucide="layout-dashboard"></i>
                Dashboard
            </a>
            <a href="/tracking" class="nav-item {{ request()->is('tracking') ? 'active' : '' }}">
                <i data-lucide="map-pin"></i>
                Real-Time Tracking
            </a>
            <a href="/activity" class="nav-item {{ request()->is('activity') ? 'active' : '' }}">
                <i data-lucide="activity"></i>
                Student Activity
            </a>
            <a href="/notifications" class="nav-item {{ request()->is('notifications') ? 'active' : '' }}">
                <i data-lucide="bell"></i>
                Notifications
            </a>

            @if(Auth::guard('admin')->check() && Auth::guard('admin')->user()->role === 'main')
            <div style="margin: 16px 16px 8px; font-size: 11px; font-weight: 800; color: #b6b7bbff; text-transform: uppercase; letter-spacing: 0.5px;">System Management</div>
            <a href="/admin/students" class="nav-item {{ request()->is('admin/students*') ? 'active' : '' }}">
                <i data-lucide="users-round"></i>
                Manage Students
            </a>
            <a href="/admin/admins" class="nav-item {{ request()->is('admin/admins*') ? 'active' : '' }}">
                <i data-lucide="shield"></i>
                Manage Admins
            </a>
            @endif
        </nav>

        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-item" style="width: 100%; border: none; background: transparent; cursor: pointer; text-align: left;">
                    <i data-lucide="log-out"></i>
                    Logout
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-wrapper">
        <div id="sos-banner">
            🚨 SOS ALERT — A student is in danger! Check the system immediately.
        </div>
        
        <header class="top-header">
            <div class="page-title">
                <h1>@yield('title', 'Overview')</h1>
                <p>@yield('subtitle', 'System status and analytics overview')</p>
            </div>
            
            <div class="header-actions">
                <a href="/notifications" class="notification-btn">
                    <i data-lucide="bell"></i>
                    @if(isset($sosCount) && $sosCount > 0)
                        <span class="notification-badge">{{ $sosCount }}</span>
                    @endif
                </a>
            </div>
        </header>

        <div class="content-area">
            @yield('content')
        </div>
    </main>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Global polling for SOS
        function pollGlobalStats() {
            fetch('/api/dashboard/stats')
                .then(res => res.json())
                .then(data => {
                    const sosBanner = document.getElementById('sos-banner');
                    if (sosBanner) {
                        sosBanner.style.display = (data.sosStudents && data.sosStudents.length > 0) ? 'block' : 'none';
                    }
                    
                    const badge = document.querySelector('.notification-badge');
                    if (data.sosCount > 0) {
                        if (badge) {
                            badge.textContent = data.sosCount;
                        } else {
                            const btn = document.querySelector('.notification-btn');
                            if(btn) {
                                btn.innerHTML += `<span class="notification-badge">${data.sosCount}</span>`;
                            }
                        }
                    } else if (badge) {
                        badge.remove();
                    }
                })
                .catch(err => console.error('Global poll error:', err));
        }

        // Only poll if we are authenticated/on a valid page
        setInterval(pollGlobalStats, 10000);
        pollGlobalStats();
    </script>
    
    @stack('scripts')
</body>
</html>
