<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Dashboard | PN Tasking Hub</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <script src="https://kit.fontawesome.com/4e45d9ad8d.js" crossorigin="anonymous"></script>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #f8f9fa;
      margin: 0;
      padding: 0;
      min-height: 100vh;
    }
        header {
        font-family: 'Poppins', sans-serif;
        background-color: #22BBEA;
        color: white;
      padding: 20px;
        display: flex;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1100; /* ensure header is above sidebar/content */
        box-sizing: border-box;
        min-height: var(--header-height);
      }

      .logo {
        font-family: 'Poppins', sans-serif;
        margin-left: 0;
      }
      .logo img {
        /* constrain logo height so header stays compact */
        height: 56px;
        width: auto;
        margin-left: 0;
        display: block;
      }
          
    .header-right {
      font-family: 'Poppins', sans-serif;
      flex: 1;
      display: flex;
      justify-content: flex-end;
      align-items: center;
      font-size: 18px;
      font-weight: 500;
      gap: 15px;
    }
    
    /* Compact Header Attendance Status */
    .header-attendance-status {
      background: rgba(255, 255, 255, 0.15);
      border-radius: 25px;
      padding: 8px 15px;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    
    .attendance-compact {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
    }
    
    .status-icon-small {
      font-size: 16px;
    }
    
    .status-text-small {
      font-weight: 500;
      min-width: 80px;
    }

    /* Header Logout Button (PN-ScholarSync style) */
    .header-logout-btn {
      background: none;
      border: none;
      color: white;
      padding: 8px;
      border-radius: 6px;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .header-logout-btn:hover {
      background-color: rgba(255, 255, 255, 0.1);
      transform: scale(1.1);
    }

    .header-logout-btn svg {
      width: 24px;
      height: 24px;
      stroke: white;
    }

    .header-logout-btn:hover svg {
      stroke: #ffffff;
    }

    
    .container-fluid {
      font-family: 'Poppins', sans-serif;
      display: flex;
      min-height: calc(100vh - 80px);
      min-width: 1200px;
    }
    .sidebar {
      font-family: 'Poppins', sans-serif;
      border-right: 3px solid #22BBEA;
      background: #f8f9fa;
      width: 250px;
      padding: 20px 0;
    }
    .nav {
      font-family: 'Poppins', sans-serif;
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .nav-item {
      font-family: 'Poppins', sans-serif;
      margin-bottom: 5px;
    }
    .nav-link {
      font-family: 'Poppins', sans-serif;
      display: flex;
      align-items: center;
      padding: 15px 20px;
      color: #333;
      text-decoration: none;
      font-weight: 500;
      transition: all 0.3s ease;
      border-left: 3px solid transparent;
    }
    .nav-link:hover {
      font-family: 'Poppins', sans-serif;
      background: #e3f2fd;
      color: #22BBEA;
      border-left-color: #22BBEA;
    }
    .nav-link.active {
      background: #e3f2fd;
      color: #22BBEA;
      border-left-color: #22BBEA;
      font-weight: 600;
    }
    .sidebar-icon {
      font-family: 'Poppins', sans-serif;
      width: 20px;
      height: 20px;
      margin-right: 12px;
    }
    .content {
      font-family: 'Poppins', sans-serif;
      flex: 1;
      padding: 30px;
      background: #f6f8fa;
      overflow-y: auto;
      max-height: calc(100vh - 80px);
    }
    /* Ensure main content sits below the fixed header to avoid overlap with top cards */
    .main-content {
      /* Use header height CSS variable when available, fallback to 80px */
      padding-top: calc(var(--header-height, 120px) + 12px);
    }
    /* Attendance Status Card */
    .attendance-status-card {
      background: linear-gradient(135deg, #22BBEA 0%, #1e9fd4 100%);
      color: white;
      border-radius: 16px;
      padding: 20px 24px;
      margin: 0 auto 20px auto;
      box-shadow: 0 4px 16px rgba(34, 187, 234, 0.2);
      max-width: 800px;
      width: calc(100% - 40px);
    }
    
    .attendance-status-present {
      background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .attendance-status-absent {
      background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    }
    
    .attendance-status-late {
      background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
    }
    
    .attendance-status-excused {
      background: linear-gradient(135deg, #17a2b8 0%, #6f42c1 100%);
    }
    
    .status-icon {
      font-size: 1.5rem;
      margin-bottom: 8px;
    }
    
    .status-title {
      font-size: 1.3rem;
      font-weight: 700;
      margin-bottom: 4px;
    }
    
    .status-subtitle {
      opacity: 0.9;
      font-size: 0.85rem;
    }

    .welcome-card {
      background: linear-gradient(135deg, #ff9000 0%, #ff7b00 100%);
      color: #fff;
      border-radius: 16px;
      padding: 28px 32px;
      margin: 0 auto 32px auto;
      box-shadow: 0 4px 16px rgba(255, 144, 0, 0.2);
      max-width: 800px;
      width: calc(100% - 40px);
      text-align: left;
    }
    .welcome-title {
      font-size: 1.8rem;
      font-weight: 700;
      margin-bottom: 8px;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .welcome-text {
      font-size: 1rem;
      margin-bottom: 0;
      line-height: 1.5;
    }
    .cards-container {
      display: flex;
      gap: 40px;
      margin-top: 0;
      flex-wrap: wrap;
      justify-content: center;
      width: 100%;
      max-width: 1000px;
      margin-left: auto;
      margin-right: auto;
    }
    .task-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      padding: 28px 24px;
      min-width: 300px;
      max-width: 380px;
      width: 360px;
      display: flex;
      flex-direction: column;
      align-items: center;
      border: 1px solid #e8e8e8;
      transition: all 0.3s ease;
    }
    .task-card:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 20px rgba(0,0,0,0.12);
    }
    .card-title {
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 12px;
      color: #333;
      text-align: center;
    }
    .card-description {
      color: #666;
      font-size: 1rem;
      text-align: center;
      margin-bottom: 20px;
      line-height: 1.5;
    }
    .card-button {
      background: #22b6ef;
      color: #fff;
      font-weight: 600;
      border: none;
      border-radius: 6px;
      padding: 8px 16px;
      font-size: 0.85rem;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
      cursor: pointer;
      max-width: 200px;
      text-align: center;
    }
    .card-button:hover {
      background: #1a8fc1;
      color: #fff;
      text-decoration: none;
      transform: translateY(-1px);
    }
    .info-icon {
      width: 28px;
      height: 28px;
      display: inline-block;
    }
    .roommate-avatar {
      width: 28px;
      height: 28px;
      border-radius: 50%;
      display: inline-block;
      vertical-align: middle;
      background: #e9f7fd;
      padding: 4px;
    }
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0;
        padding: 100px 30px 60px 30px;
        max-width: 100%;
      }
      .sidebar {
        transform: translateX(-100%);
        transition: transform 0.3s ease;
      }
      .sidebar.show {
        transform: translateX(0);
      }
      .cards-container {
        flex-direction: column;
        gap: 16px;
      }
      .task-card {
        max-width: 100%;
        min-width: 0;
        width: 100%;
      }
      .welcome-card {
        width: calc(100% - 20px);
        padding: 24px;
      }
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
    </div>
    <div class="header-right">
      <!-- Compact Attendance Status in Header -->
      <div class="header-attendance-status" id="headerAttendanceStatus">
        <div class="attendance-compact" id="attendanceCompact">
          <span class="status-icon-small" id="statusIconSmall">
            <i class="bi bi-clock-history"></i>
          </span>
          <span class="status-text-small" id="statusTextSmall">Checking...</span>
          <button class="btn btn-sm btn-outline-light ms-2" onclick="refreshAttendance()" id="refreshBtnSmall" title="Refresh Attendance">
            <i class="bi bi-arrow-clockwise"></i>
          </button>
        </div>
      </div>
      <!-- Logout Button (PN-ScholarSync style) -->
      <form action="{{ route('logout') }}" method="post" style="display:inline; margin-left: 15px;">
        @csrf
        <button type="submit" class="header-logout-btn" title="Log Out">
          <svg class="w-6 h-6 text-gray-800" width="24" height="24" fill="none" viewBox="0 0 24 24">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
          </svg>
        </button>
      </form>
    </div>
  </header>
  <div class="container-fluid">
    @include('partials.sidebar')
  <div class="main-content content">
        <div class="welcome-card">
          <div class="welcome-title">Good day, {{ auth()->user()->user_fname ?? 'User' }} {{ auth()->user()->user_lname ?? '' }}!</div>
          <div class="welcome-text">
            Welcome to the PN Tasking Hub System.<br>
            <span id="attendanceMessage">Your attendance status is shown for information only. You can access all task assignments.</span>
          </div>
        </div>
    <div class="cards-container">
          <div class="task-card">
            <div class="card-title">General Assigned Tasks</div>
            <div class="card-description">
            View your current general tasks and schedules.
            </div>
            <button class="card-button" onclick="window.location.href='{{ route('student.general.task') }}'">View Tasks</button>
          </div>

          <div class="task-card">
            <div class="card-title">Room Tasks</div>
            <div class="card-description">
              View your room task assignments, inspection checklist results, and comments if any.
            </div>
            <button class="card-button" onclick="window.location.href='{{ route('student.room.tasking') }}'">View room assignments</button>
          </div>
        </div>
        <!-- Student summary and tasks (if studentTasks provided) -->
        @php
            $st = isset($studentTasks) && (is_array($studentTasks) || $studentTasks instanceof \Illuminate\Support\Collection)
                ? (is_array($studentTasks) ? collect($studentTasks) : $studentTasks)
                : collect();
            $selectedRoom = $st['selectedRoom'] ?? $st->get('selectedRoom') ?? null;
            $roommates = $st['roommates'] ?? $st->get('roommates') ?? [];
            $days = $st['days'] ?? $st->get('days') ?? ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
            $tasksByDay = $st['tasksByDay'] ?? $st->get('tasksByDay') ?? [];
            $today = date('l');
            $todayTasks = $tasksByDay[$today]['tasks'] ?? [];
        @endphp
    </div>
  </div>

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
        let data = null;
        try {
          const response = await fetch(`/api/attendance/student/${studentId}`);
          if (response && response.ok) data = await response.json();
        } catch (e) {
          console.warn('Primary attendance API failed, will try proxy', e);
        }

        // If primary API didn't return expected fields, try proxy
        if (!data || (typeof data.time_in === 'undefined' && typeof data.time_out === 'undefined')) {
          try {
            const proxyRes = await fetch(`/api/attendance/proxy/${studentId}`);
            if (proxyRes && proxyRes.ok) data = await proxyRes.json();
          } catch (pe) {
            console.warn('Proxy attendance API failed', pe);
          }
        }

        if (!data) {
          throw new Error('No attendance data available');
        }

        attendanceStatus = data;
        updateAttendanceDisplay(data);

        // Compute eligibility same as before
        let computedEligibility = true;
        try {
          const hasTimeOut = (data && (data.time_out || data.time_out_remark));
          const hasTimeIn = (data && (data.time_in || data.time_in_remark));
          if (hasTimeOut && !hasTimeIn) {
            computedEligibility = false;
          } else {
            if (typeof data.task_eligible !== 'undefined') computedEligibility = !!data.task_eligible;
          }
        } catch (e) {
          console.warn('Error computing eligibility from attendance data', e);
          if (typeof data.task_eligible !== 'undefined') computedEligibility = !!data.task_eligible;
        }

        console.log('Computed task eligibility based on attendance:', computedEligibility, data);
        updateTaskAccess(computedEligibility);

      } catch (error) {
        console.error('Error checking attendance:', error);
        showAttendanceError('Unable to check attendance status');
      }
    }

    function updateAttendanceDisplay(data) {
      // Update compact header status
      const iconSmall = document.getElementById('statusIconSmall');
      const textSmall = document.getElementById('statusTextSmall');
      const message = document.getElementById('attendanceMessage');

      // Update content based on status
      switch(data.status) {
        case 'present':
          iconSmall.innerHTML = '<i class="bi bi-check-circle-fill" style="color: #28a745;"></i>';
          textSmall.textContent = 'Present';
          message.textContent = 'Your attendance status is shown for information only. You can access all task assignments.';
          break;
          
        case 'absent':
          iconSmall.innerHTML = '<i class="bi bi-info-circle-fill" style="color: #ffc107;"></i>';
          textSmall.textContent = 'Absent (Info Only)';
          message.textContent = 'Your attendance status is shown for information only. You can access all task assignments.';
          break;
          
        case 'late':
          iconSmall.innerHTML = '<i class="bi bi-clock-fill" style="color: #fd7e14;"></i>';
          textSmall.textContent = 'Late (Info Only)';
          message.textContent = 'Your attendance status is shown for information only. You can access all task assignments.';
          break;
          
        case 'excused':
          iconSmall.innerHTML = '<i class="bi bi-info-circle-fill" style="color: #17a2b8;"></i>';
          textSmall.textContent = 'Excused (Info Only)';
          message.textContent = 'Your attendance status is shown for information only. You can access all task assignments.';
          break;
          
        default:
          iconSmall.innerHTML = '<i class="bi bi-question-circle-fill" style="color: #6c757d;"></i>';
          textSmall.textContent = 'Unknown Status';
          message.textContent = 'Please contact your coordinator to verify your attendance.';
      }
    }

    function updateTaskAccess(isTaskEligible) {
      // ATTENDANCE IS NOW INFORMATIONAL ONLY - NO BLOCKING
      // All students can access tasks regardless of attendance status
      
      const taskCards = document.querySelectorAll('.task-card');
      const cardButtons = document.querySelectorAll('.card-button');
      
      // Always enable all task cards and buttons
      taskCards.forEach(card => {
        card.style.opacity = '1';
        card.style.pointerEvents = 'auto';
      });

      cardButtons.forEach(button => {
        button.disabled = false;
        button.style.opacity = '1';
        button.style.cursor = 'pointer';
        // DO NOT remove onclick handlers - keep original functionality
        // button.onclick = null; // REMOVED - this was breaking the buttons
      });
      
      // Attendance status is now just informational - no access restrictions
      console.log('Attendance status:', isTaskEligible ? 'Present' : 'Absent', '(Informational only - no access restrictions)');
    }

    function showAttendanceError(message) {
      const card = document.getElementById('attendanceStatusCard');
      const icon = document.getElementById('statusIcon');
      const title = document.getElementById('statusTitle');
      const subtitle = document.getElementById('statusSubtitle');
      
      card.className = 'attendance-status-card attendance-status-absent';
      icon.innerHTML = '<i class="bi bi-exclamation-triangle-fill"></i>';
      title.textContent = 'Error';
      subtitle.textContent = message;
      
      updateTaskAccess(false);
    }

    function refreshAttendance() {
      const refreshBtn = document.getElementById('refreshBtn');
      refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refreshing...';
      refreshBtn.disabled = true;
      
      checkAttendanceStatus().finally(() => {
        refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh';
        refreshBtn.disabled = false;
      });
    }

  </script>
</body>
</html>