<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @stack('styles')
    <style>
        :root {
            --primary-color: #FF9933;
            --secondary-color: #FF9933;
            --accent-color: #2C3E50;
            --background-color: #f8f9fa;
            --text-color: #333;
            --primary: #2563eb;
            --primary-light: #3b82f6;
            --accent: #f59e0b;
            --accent-light: #fbbf24;
            --dark: #1e293b;
            --light: #f8fafc;
            --glass: rgba(255, 255, 255, 0.2);
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            
            --sidebar: #4169E1;
            /* Shared layout sizes */
            --header-height: 80px; /* default header height used across layouts */
            --sidebar-width: 300px; /* default sidebar width used across layouts */
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--background-color);
            color: var(--text-color);
        }

        .nav-header {
            background:  #22BBEA;
            color: white;
            height: var(--header-height);
            padding: 0 1.5rem; /* horizontal padding only; vertical centering via height */
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .nav-header h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
        }

        .sidebar {
            position: fixed;
            left: 0;
            top: var(--header-height); /* start just below header */
            bottom: 0;
            width: var(--sidebar-width);
            background: #4169E1;
            padding: 1.5rem 1rem 2rem 1rem;
            box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar-nav {
            list-style: none;
        }

        .sidebar-nav li {
            margin-bottom: 0.5rem;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .sidebar-nav a:hover,
        .sidebar-nav a.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 2rem;
        }

        .icon {
            margin-right: 0.75rem;
            font-size: 1.25rem;
        }
    </style>
</head>
<body>
    <header class="nav-header">
        <div class="logo">
            <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo" style="height: 40px; width: auto;">
        </div>
        <div class="header-right" style="display: flex; align-items: center; gap: 15px;">
            <!-- Compact Attendance Status in Header -->
            <div class="header-attendance-status" id="headerAttendanceStatus" style="background: rgba(255, 255, 255, 0.15); border-radius: 25px; padding: 8px 15px; backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2);">
                <div class="attendance-compact" id="attendanceCompact" style="display: flex; align-items: center; gap: 8px; font-size: 14px;">
                    <span class="status-icon-small" id="statusIconSmall" style="font-size: 16px;">
                        <i class="bi bi-clock-history"></i>
                    </span>
                    <span class="status-text-small" id="statusTextSmall" style="font-weight: 500; min-width: 80px;">Checking...</span>
                    <button class="btn btn-sm btn-outline-light ms-2" onclick="refreshAttendance()" id="refreshBtnSmall" title="Refresh Attendance">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>
            <!-- Logout Button -->
            <form action="{{ route('logout') }}" method="post" style="display:inline; margin-left: 15px;">
                @csrf
                <button type="submit" class="header-logout-btn" title="Log Out" style="background: none; border: none; color: white; padding: 8px; border-radius: 6px; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center;">
                    <svg class="w-6 h-6 text-gray-800" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
                    </svg>
                </button>
            </form>
        </div>
    </header>

    @include('partials.sidebar')

    <main class="main-content">
        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Student ID from Laravel
        const studentId = {{ auth()->user()->user_id ?? 'null' }};
        let attendanceStatus = null;

        // Check attendance status on page load
        document.addEventListener('DOMContentLoaded', function() {
            checkAttendanceStatus();
            // Auto-refresh every 5 minutes
            setInterval(checkAttendanceStatus, 300000);
        });

        async function checkAttendanceStatus() {
            if (!studentId) {
                showAttendanceError('Student ID not found');
                return;
            }

            try {
                const response = await fetch(`/api/attendance/student/${studentId}`);
                const data = await response.json();
                
                attendanceStatus = data;
                updateAttendanceDisplay(data);
                
            } catch (error) {
                console.error('Error checking attendance:', error);
                showAttendanceError('Unable to check attendance status');
            }
        }

        function updateAttendanceDisplay(data) {
            // Update compact header status
            const iconSmall = document.getElementById('statusIconSmall');
            const textSmall = document.getElementById('statusTextSmall');

            // Update content based on status
            switch(data.status) {
                case 'present':
                    iconSmall.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #28a745;"></i>';
                    textSmall.textContent = 'Present';
                    break;
                    
                case 'absent':
                    iconSmall.innerHTML = '<i class="bi bi-info-circle-fill" style="color: #ffc107;"></i>';
                    textSmall.textContent = 'Absent (Info Only)';
                    break;
                    
                case 'late':
                    iconSmall.innerHTML = '<i class="bi bi-clock-fill" style="color: #fd7e14;"></i>';
                    textSmall.textContent = 'Late (Info Only)';
                    break;
                    
                case 'excused':
                    iconSmall.innerHTML = '<i class="bi bi-info-circle-fill" style="color: #17a2b8;"></i>';
                    textSmall.textContent = 'Excused (Info Only)';
                    break;
                    
                default:
                    iconSmall.innerHTML = '<i class="bi bi-question-circle-fill" style="color: #6c757d;"></i>';
                    textSmall.textContent = 'Unknown Status';
            }
        }

        function showAttendanceError(message) {
            const iconSmall = document.getElementById('statusIconSmall');
            const textSmall = document.getElementById('statusTextSmall');
            
            iconSmall.innerHTML = '<i class="bi bi-exclamation-triangle-fill" style="color: #dc3545;"></i>';
            textSmall.textContent = 'Error';
        }

        function refreshAttendance() {
            const refreshBtn = document.getElementById('refreshBtnSmall');
            const originalContent = refreshBtn.innerHTML;
            
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i>';
            refreshBtn.disabled = true;
            
            checkAttendanceStatus().finally(() => {
                refreshBtn.innerHTML = originalContent;
                refreshBtn.disabled = false;
            });
        }
    </script>
    
    @stack('scripts')
</body>
</html> 