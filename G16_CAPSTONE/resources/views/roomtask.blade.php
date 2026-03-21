<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Room Checklist - Tasking Hub System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="{{ asset('css/roomtask.css') }}" />
  <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  
  <style>
    /* Lighter centered card style (matches the first image) */
    .rt-modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.06); display: none; align-items: center; justify-content: center; z-index: 4000; padding: 24px; }
    .rt-modal-overlay.show { display:flex; }
    .rt-modal { background:#fff; border-radius:8px; width:100%; max-width:520px; box-shadow:0 6px 20px rgba(7,12,14,0.06); border:1px solid #e9ecef; overflow:hidden; display:flex; flex-direction:column; }
    .rt-modal-header { padding:14px 18px; border-bottom: none; }
    .rt-modal-header h2 { margin:0; font-size:1.15rem; font-weight:700; color:#222; }
    .rt-modal-form { padding:14px 18px; display:flex; flex-direction:column; gap:12px; }
    .rt-form-row { display:flex; flex-direction:column; gap:6px; }
    .rt-form-row label { font-weight:600; font-size:0.95rem; color:#333; }
    .rt-form-row input[type="text"], .rt-form-row textarea { padding:10px 12px; border-radius:6px; border:1px solid #d8dbe0; font-size:0.96rem; resize:vertical; background:#fff; }
    .rt-modal-footer { display:flex; gap:10px; justify-content:flex-end; padding:12px 18px; border-top:none; align-items:center; }
    /* Force modal buttons to consistent size and alignment, use !important to defeat global overrides */
    .rt-modal-footer .rt-modal-btn {
      min-width:120px !important;
      height:40px !important;
      padding:8px 14px !important;
      border-radius:6px !important;
      font-weight:600 !important;
      display:inline-flex !important;
      align-items:center !important;
      justify-content:center !important;
      cursor:pointer !important;
      border:none !important;
      box-sizing:border-box !important;
      vertical-align:middle !important;
      line-height:1 !important;
    }
  #taskModal .modal-card {
    max-width: 430px;
    border-radius: 14px;
    border: 1px solid #e4e8f1;
    box-shadow: 0 20px 40px rgba(15,23,42,0.18);
    overflow: hidden;
  }
  #taskModal .modal-header {
    padding: 16px 20px;
    border-bottom: 1px solid #eef1f7;
  }
  #taskModal .modal-header h5,
  #taskModal .modal-header span {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
  }
  #taskModal .modal-body {
    padding: 18px 20px 8px;
  }
  #taskModal .modal-body label {
    font-weight: 600;
    color: #1f2937;
    font-size: 0.95rem;
  }
  #taskModal #modalStatus {
    border-radius: 10px;
    padding: 12px 14px;
    border: 1px solid #d0d7e6;
    font-weight: 600;
    color: #0f172a;
    background: #f8fafc;
  }
  #taskModal .status-helper-text {
    font-size: 0.85rem;
    color: #6b7280;
    margin-top: 6px;
  }
  #taskModal .modal-footer {
    border-top: 1px solid #eef1f7;
    padding: 14px 20px;
  }

    .rt-btn-primary, .rt-btn-secondary {
  min-width: 120px;           /* Same min width */
  height: 40px;               /* Same height */
  padding: 8px 16px;          /* Consistent padding */
  border-radius: 6px;
  display: inline-flex;       /* align content */
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #fff !important;
  cursor: pointer;
  position: static !important; /* remove any relative/absolute positioning */
  top: auto !important;        /* reset any vertical shift */
  margin-left: 8px;            /* space between buttons */
  box-sizing: border-box;
  vertical-align: middle;
}
.rt-btn-secondary {
  min-width: 120px;           /* Same min width */
  height: 40px;               /* Same height */
  padding: 8px 16px;          /* Consistent padding */
  border-radius: 6px;
  display: inline-flex;       /* align content */
  align-items: center;
  justify-content: center;
  font-weight: 600;
  color: #fff !important;
  cursor: pointer;
  position: static !important; /* remove any relative/absolute positioning */
  top: 10px;        /* reset any vertical shift */
  margin-left: 8px;            /* space between buttons */
  box-sizing: border-box;
  vertical-align: middle;
}
    .rt-btn-primary { background:#28a745 !important; color:#fff !important; }
    .rt-btn-primary:hover { background:#218838 !important; }
  /* rely on footer gap for spacing; do not apply extra left margin */
  .rt-btn-secondary { background:#6c757d !important; color:#fff !important; }
    .rt-btn-secondary:hover { background:#5b6469 !important; }
    @media (max-width:420px) { .rt-modal{max-width:98%;} .rt-modal-btn{min-width:100px; height:36px;} }
  </style><style>
  /* Lighter centered card style (matches the first image) */
  .rt-modal-overlay { 
    position: fixed; 
    inset: 0; 
    background: rgba(0,0,0,0.06); 
    display: none; 
    align-items: center; 
    justify-content: center; 
    z-index: 4000; 
    padding: 24px; 
  }
  .rt-modal-overlay.show { 
    display: flex; 
  }
  .rt-modal { 
    background: #fff; 
    border-radius: 8px; 
    width: 100%; 
    max-width: 520px; 
    box-shadow: 0 6px 20px rgba(7,12,14,0.06); 
    border: 1px solid #e9ecef; 
    overflow: hidden; 
    display: flex; 
    flex-direction: column; 
  }
  .rt-modal-header { 
    padding: 14px 18px; 
    border-bottom: none; 
  }
  .rt-modal-header h2 { 
    margin: 0; 
    font-size: 1.15rem; 
    font-weight: 700; 
    color: #222; 
  }
  .rt-modal-form { 
    padding: 14px 18px; 
    display: flex; 
    flex-direction: column; 
    gap: 12px; 
  }
  .rt-form-row { 
    display: flex; 
    flex-direction: column; 
    gap: 6px; 
  }
  .rt-form-row label { 
    font-weight: 600; 
    font-size: 0.95rem; 
    color: #333; 
  }
  .rt-form-row input[type="text"], 
  .rt-form-row textarea { 
    padding: 10px 12px; 
    border-radius: 6px; 
    border: 1px solid #d8dbe0; 
    font-size: 0.96rem; 
    resize: vertical; 
    background: #fff; 
  }
  .rt-modal-footer { 
    display: flex; 
    gap: 10px; 
    justify-content: flex-end; 
    padding: 12px 18px; 
    border-top: none; 
    align-items: center; 
  }
  /* Force modal buttons to consistent size and alignment, use !important to defeat global overrides */
  .rt-modal-footer .rt-modal-btn {
    min-width: 120px !important;
    height: 40px !important;
    padding: 8px 16px !important;
    border-radius: 6px !important;
    font-weight: 600 !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    cursor: pointer !important;
    border: none !important;
    box-sizing: border-box !important;
    vertical-align: middle !important;
    line-height: 1 !important;
    position: static !important;
    top: auto !important;
    color: #fff !important;
  }

  .rt-btn-primary {
    background: #28a745 !important;
  }
  .rt-btn-primary:hover {
    background: #218838 !important;
  }
  
  /* rely on footer gap for spacing; do not apply extra left margin */
  .rt-btn-secondary {
    background: #6c757d !important;
  }
  .rt-btn-secondary:hover {
    background: #5b6469 !important;
  }

  @media (max-width: 420px) { 
    .rt-modal { max-width: 98%; } 
    .rt-modal-btn { min-width: 100px; height: 36px; padding: 6px 12px !important; }
  }

    #simpleLoaderOverlayRT { position: fixed; top:0; left:0; right:0; bottom:0; background: rgba(0,0,0,0.35); display:none; align-items:center; justify-content:center; z-index:20000; }
    #simpleLoaderBoxRT { background:white; padding:16px 20px; border-radius:8px; min-width:280px; text-align:center; box-shadow:0 8px 24px rgba(0,0,0,0.2); }
    #simpleLoaderBoxRT .spinner-dot { width:26px; height:26px; border-radius:50%; border:4px solid rgba(0,0,0,0.12); border-top-color:#007bff; margin:6px auto 8px auto; animation: loader-rotate 0.9s linear infinite; }
    #simpleLoaderBoxRT .loader-message { font-size:14px; color:#333; }
  </style>

  <div id="simpleLoaderOverlayRT" aria-hidden="true">
    <div id="simpleLoaderBoxRT" role="status">
      <div class="spinner-dot" aria-hidden="true"></div>
      <div class="loader-message">Please wait...</div>
    </div>
  </div>

  <script>
    function showLoaderRT(message = 'Please wait...') {
      try { const overlay = document.getElementById('simpleLoaderOverlayRT'); const box = document.getElementById('simpleLoaderBoxRT'); if (!overlay || !box) return; box.querySelector('.loader-message').textContent = message; overlay.style.display = 'flex'; overlay.setAttribute('aria-hidden','false'); } catch(e) { console.warn('showLoaderRT failed', e); }
    }
    function hideLoaderRT() { try { const overlay = document.getElementById('simpleLoaderOverlayRT'); if (overlay) { overlay.style.display = 'none'; overlay.setAttribute('aria-hidden','true'); } } catch(e) {} }
    function showResultRT(type = 'success', message = '', timeout = 2200) { try { const overlay = document.getElementById('simpleLoaderOverlayRT'); const box = document.getElementById('simpleLoaderBoxRT'); if (!overlay || !box) return; box.querySelector('.loader-message').textContent = message || (type==='success'?'Done':'Error'); overlay.style.display = 'flex'; overlay.setAttribute('aria-hidden','false'); if (type==='error') box.style.borderLeft = '4px solid #dc3545'; else box.style.borderLeft = '4px solid #28a745'; setTimeout(()=>{ try { overlay.style.display='none'; overlay.setAttribute('aria-hidden','true'); box.style.borderLeft=''; } catch(e){} }, timeout); } catch(e) { console.warn('showResultRT failed', e); } }
  </script>

  <style>
    /* Clean, responsive layout rules for header, sidebar, content, tables and cards */
    /* Theme variables for table header colors - edit these two variables to change header BG and text color */
    :root {
      --rt-table-head-bg: #1b68ddff; /* default header background (cyan) */
      --rt-table-head-text: #ffffff; /* default header text color (white) */
    }

    /* Apply header colors to relevant tables. Use !important to ensure these styles take precedence over inline/bootstrap rules. */
    .assigned-tasks-panel table thead th,
    #taskTable thead th,
    .rotation-table thead th,
    .table.table-bordered thead th {
      background: var(--rt-table-head-bg) !important;
      color: var(--rt-table-head-text) !important;
      text-align: center;
      vertical-align: middle;
      font-weight: 700;
    }
    .assigned-tasks-panel table td { white-space: normal; word-break: break-word; vertical-align: middle; padding: 10px 8px; text-align: center; }
    .assigned-tasks-panel table th:first-child, .assigned-tasks-panel table td:first-child { text-align: left; }
  .assigned-tasks-panel .table { background: white; }
  /* Add space between occupant name tabs and the "My Assigned Tasks" card */
  .assigned-tasks-panel { margin-top: 18px; }

  /* Overall layout: sidebar + content using flexbox so content fills available left space */
  .main-container { display: flex; align-items: flex-start; gap: 8px; }
  /* Use the shared CSS variable so sidebar width matches other views and content sits close */
  .main-container > .sidebar-container { width: var(--sidebar-width, 340px); flex: 0 0 var(--sidebar-width, 340px); }
    .main-container > .content-container { flex: 1 1 auto; }

    /* Use full available width for major content blocks so the table occupies left/right space */
    .page-header, .room-details-card, .time-selection-wrapper, .checklist-wrapper, .assigned-tasks-panel, .feedback-section, #adminScheduleResults {
      width: 100%;
      max-width: none;
      margin-left: 0;
      margin-right: 0;
      box-sizing: border-box;
      padding-left: 8px;
      padding-right: 8px;
    }

    /* Make checklist and rotation tables stretch to the full content width and scroll internally when needed */
    .checklist-wrapper { width: 100%; overflow-x: auto; display: block; }
    .checklist-wrapper .table, #taskTable, .rotation-table { width: 100%; max-width: none; margin: 0; table-layout: fixed; border-collapse: collapse; }

    /* Column width adjustments: keep Task Description wide, reduce Task Area and Status widths */
    /* Assuming columns: 1 - Assigned To, 2 - Task Area, 3 - Task Description, 4 - Status, 5 - Actions */
    #taskTable th, #taskTable td { overflow-wrap: break-word; word-break: break-word; }
    #taskTable th:nth-child(1), #taskTable td:nth-child(1) { width: 12%; min-width: 120px; }
    #taskTable th:nth-child(2), #taskTable td:nth-child(2) { width: 12%; min-width: 90px; }
    #taskTable th:nth-child(3), #taskTable td:nth-child(3) { width: 56%; min-width: 320px; }
    #taskTable th:nth-child(4), #taskTable td:nth-child(4) { width: 10%; min-width: 80px; }
    #taskTable th:nth-child(5), #taskTable td:nth-child(5) { width: 10%; min-width: 80px; }

    /* Ensure description column content wraps nicely and shows ellipsis on overflow in very narrow views */
    #taskTable td:nth-child(3) {
      white-space: normal;
      max-width: 100%;
    }
    @media (max-width: 900px) {
      /* On smaller screens, relax fixed widths so table can scroll horizontally */
      #taskTable th:nth-child(1), #taskTable td:nth-child(1),
      #taskTable th:nth-child(2), #taskTable td:nth-child(2),
      #taskTable th:nth-child(3), #taskTable td:nth-child(3),
      #taskTable th:nth-child(4), #taskTable td:nth-child(4),
      #taskTable th:nth-child(5), #taskTable td:nth-child(5) {
        width: auto; min-width: 120px;
      }
      #taskTable td:nth-child(3) { max-width: 420px; }
    }

    /* Ensure readable wrapping and stable box-sizing */
    .rotation-table th, .rotation-table td, #taskTable th, #taskTable td { box-sizing: border-box; white-space: normal !important; overflow-wrap: break-word; word-break: break-word; vertical-align: middle; }

    /* Keep action column compact for edit icon */
    #taskTable td.actions-col, #taskTable th.actions-col { width: 54px; max-width: 54px; text-align: center; }
    .actions-col .action-buttons {
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .actions-col .edit-task-btn {
      width: 38px;
      height: 38px;
      border-radius: 8px;
      border: 1px solid rgba(30,144,255,0.35);
      background: rgba(30,144,255,0.08);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      transition: all 0.2s ease;
    }
    .actions-col .edit-task-btn:hover {
      background: rgba(30,144,255,0.18);
      border-color: rgba(30,144,255,0.7);
      transform: translateY(-1px);
    }
    .actions-col .edit-task-btn i {
      font-size: 1.15rem;
      color: #1e90ff;
    }
    .action-buttons .btn { white-space: nowrap; }
    .action-buttons .btn { white-space: nowrap; }

  /* Status column: center controls and adjust width for consistent layout */
  /* Reduced gap so the check and wrong buttons sit closer together */
  .status-buttons { display: flex; gap: 6px; justify-content: center; align-items: center; }

    /* Target both wrapped buttons (admin view) and standalone buttons (completed state).
       Ensure they are centered in the cell and have a stable size. */
    #taskTable td > .status-btn,
    #taskTable td > button.status-btn,
    #taskTable .status-buttons .status-btn,
    .status-btn {
      display: flex;
      justify-content: center;
      align-items: center;
      min-width: 60px;
      max-width: 100px;
      width: 76px;
      height: 36px;
      padding: 6px 6px;
      border-radius: 6px;
      box-sizing: border-box;
      font-weight: 600;
      margin: 0 auto;
    }

    /* Read-only status text (student view) should match the sizing and be centered */
    .student-readonly-status {
      display: block;
      min-width: 56px;
      max-width: 120px;
      width: 76px;
      text-align: center;
      padding: 6px 8px;
      border-radius: 6px;
      background: transparent;
      box-sizing: border-box;
      margin: 0 auto;
    }

    /* Ensure vertical centering and central alignment inside cells */
    #taskTable td, #taskTable th { vertical-align: middle; }
    #taskTable td > .status-buttons, #taskTable td > .status-btn, #taskTable td .student-readonly-status { margin: 0 auto; text-align: center; }

    /* Highlight current logged-in student's name */
    .my-name-highlight {
      background: #108510ff; /* soft yellow */
      padding: 2px 6px;
      border-radius: 4px;
      font-weight: 600;
      color: white;
      display: inline-block;
    }

    /* Status column width: target the 4th column (Status) so it keeps a consistent width
       without affecting other columns. Adjust for responsive screens in the media query below. */
    #taskTable th:nth-child(4),
    #taskTable td:nth-child(4) {
      width: px;
      min-width: 100px;
      max-width: 500px;
      padding-left: 6px;
      padding-right: 6px;
      text-align: center;
      box-sizing: border-box;
      white-space: nowrap;
    }

    /* Small spacing adjustments for visual balance */
    .rotation-table-container, .checklist-wrapper { padding-bottom: 8px; }

    /* Student static view: keep controls hidden but content centered and scrollable */
    body.static-view .assigned-tasks-panel, body.static-view .checklist-wrapper, body.static-view .rotation-table-container { max-width: calc(100% - 28px); padding-left: 12px; padding-right: 12px; }
    body.static-view .checklist-wrapper { background: #fff; border-radius: 10px; padding: 18px; box-shadow: 0 1px 6px rgba(0,0,0,0.06); }

    /* Responsive: hide sidebar and allow full-width content on smaller screens */
    @media (max-width: 992px) {
      .main-container { gap: 12px; }
      .main-container > .sidebar-container { display: none; }
      .main-container > .content-container { flex: 1 1 100%; }
      .page-header, .room-details-card, .time-selection-wrapper, .checklist-wrapper, .assigned-tasks-panel, .feedback-section { padding-left: 8px; padding-right: 8px; }
      #taskTable td, #taskTable th { font-size: 0.95rem; }
    }
  </style>
</head>
<body @if(isset($isStudentView) && $isStudentView) class="static-view" @endif>
  <button class="sidebar-toggle" onclick="toggleSidebar()">
    <i class="fas fa-bars"></i>
  </button>

    <div class="content-container">
    

  <!-- Top header (dashboard style) -->
  <header>
    <div class="logo">
      <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
    </div>
    
  </header>

    <div class="main-container">
    @include('partials.sidebar')

    <!-- Main content -->
    <div class="content-container">
      <!-- Page header -->
      <div class="page-header">
        <h1 class="page-title">Room Task Assignments & Checklist</h1>
      </div>

          @php
            // Determine current user role. Prefer an explicit $user_role passed from controller,
            // otherwise fall back to the authenticated user's role if available.
            $userRole = $user_role ?? (auth()->check() ? (auth()->user()->user_role ?? null) : null);
            $isInspector = ($userRole === 'inspector');
            $isEducator = ($userRole === 'educator');
          @endphp

      @if($selectedRoom)
        <!-- Top area: admin info cards (hidden for student view) -->
        @if(!isset($isStudentView) || !$isStudentView)
        <!-- Room details card for admin view -->
       
        @endif

        <div class="room-details-card">
          <div class="room-title-section">
                  <img src="{{ asset($selectedRoom == '302' ? 'images/flaticon-room.svg' : 'images/flaticon-generic.svg') }}" alt="room" class="room-icon" />
                  <h2>Student occupants in Room: {{ $selectedRoom }}</h2>
          </div>
                @php $occupants_inline = $roomStudents[$selectedRoom] ?? []; @endphp
                <div class="occupant-meta mt-2" style="display:flex;gap:16px;align-items:center;flex-wrap:wrap">
                  <div class="nicknames" style="display:flex;gap:8px;flex-wrap:wrap">
                    @forelse($occupants_inline as $occ_name)
                      {{-- nickname placeholder: if you have nickname data, replace $occ_name with it --}}
                      <span class="occupant-pill" style="background:#f1f5f9;padding:6px 10px;border-radius:6px;font-size:0.95rem;color:#1f2937">{{ $occ_name }}</span>
                    @empty
                      <div class="text-muted">No occupants found for this room.</div>
                    @endforelse
                  </div>
                  <div class="selected-date ms-auto" style="font-size:0.95rem;color:#6b7280">
                    <strong>Date:</strong> <span id="selectedDateDisplayInline">{{ 
                      // prefer a user-selected date if available, otherwise today's date
                      isset($selectedDate) ? $selectedDate : date('F j, Y')
                    }}</span>
                  </div>
                </div>
          {{-- Student occupants badge list removed as redundant; roommates card below shows occupants --}}
          {{-- Student occupants badge list removed as redundant; roommates card below shows occupants --}}

          <!-- Student-only dashboard widgets: roommates + assigned tasks -->
          @if(isset($isStudentView) && $isStudentView)
          {{-- My Roommates card removed per request --}}

          <div class="assigned-tasks-panel p-3 mb-4" style="background:#eaf9ef;border-radius:8px;">
            <h2 class="mb-2">Weekly Room Task Assignments</h2>
            <p><strong>Generated Schedule for Room Tasking</strong></p>
            <hr style="border-top: 1px solid #ccc;">
      @php
        // Determine the selected date (prefer explicit selection, fallback to today)
        try {
          $selectedDateCarbon = isset($selectedDate) ? \Carbon\Carbon::parse($selectedDate) : \Carbon\Carbon::today();
        } catch (\Throwable $e) {
          $selectedDateCarbon = \Carbon\Carbon::today();
        }
        $selectedDateIso = $selectedDateCarbon->format('Y-m-d');
        $selectedDayName = $selectedDateCarbon->format('l');

        // Build the initial candidate list for the selected date (strict date match)
        $todayTasks = [];
        if (isset($myTasks) && is_array($myTasks)) {
          // When myTasks are provided, prefer entries that either have an explicit date
          // matching the selected date, or that specify the selected day name.
          foreach ($myTasks as $mt) {
            $mtDate = is_array($mt) ? ($mt['date'] ?? null) : ($mt->date ?? null);
            if ($mtDate) {
              try { $mtDateNorm = \Carbon\Carbon::parse($mtDate)->format('Y-m-d'); } catch (\Throwable $e) { $mtDateNorm = $mtDate; }
              if ($mtDateNorm === $selectedDateIso) $todayTasks[] = $mt;
            }
          }
        } else {
          // tasksByDay can be grouped-by-room or a flat list for the day name; start with day-name source
          $daySource = $tasksByDay[$selectedDayName]['tasks'] ?? [];
          if (is_array($daySource) && isset($daySource[$selectedRoom])) {
            // grouped-by-room shape
            $candidateTasks = $daySource[$selectedRoom];
          } else {
            // flat list shape (controller may have filtered to the room already)
            $candidateTasks = is_array($daySource) ? $daySource : [];
          }

          // From the candidate tasks, include only those that explicitly match the selected date.
          foreach ($candidateTasks as $t) {
            $tDate = is_array($t) ? ($t['date'] ?? null) : ($t->date ?? null);
            if ($tDate) {
              try { $tDateNorm = \Carbon\Carbon::parse($tDate)->format('Y-m-d'); } catch (\Throwable $e) { $tDateNorm = $tDate; }
              if ($tDateNorm === $selectedDateIso) $todayTasks[] = $t;
            }
          }
        }

        // Deduplicate tasks (in case both date and day matched or sources overlapped)
        $todayTasks = array_values(array_unique($todayTasks, SORT_REGULAR));

        // Build week dates (Sunday - Saturday) based on today
        // Build the week window using the selected date as reference
        $carbonToday = $selectedDateCarbon;
        // Start on Sunday
        $startOfWeek = $carbonToday->copy()->startOfWeek(\Carbon\Carbon::SUNDAY);
        $weekDates = [];
        foreach (range(0,6) as $i) {
          $d = $startOfWeek->copy()->addDays($i);
          $weekDates[] = $d;
        }

        // Collect areas used across the week for this room
        $areas = [];
        foreach ($weekDates as $d) {
          $dayName = $d->format('l');
          $dayTasksRaw = $tasksByDay[$dayName]['tasks'] ?? [];
          if (is_array($dayTasksRaw) && isset($dayTasksRaw[$selectedRoom])) {
            $dayTasks = $dayTasksRaw[$selectedRoom];
          } else {
            $dayTasks = is_array($dayTasksRaw) ? $dayTasksRaw : [];
          }
          foreach ($dayTasks as $t) {
            $a = is_array($t) ? ($t['area'] ?? $t['name'] ?? '') : ($t->area ?? $t->name ?? '');
            if ($a && !in_array($a, $areas)) $areas[] = $a;
          }
        }
        // Keep discovered areas in the original discovered order. Do NOT fall back
        // to a hardcoded canonical list — that would re-introduce sample columns
        // such as FLOOR/HALLWAY when no templates have been applied via the
        // Manage Room Tasks UI. If no areas are discovered, leave $areas empty.
        $areas = array_values(array_filter($areas, function($a) { return trim((string)$a) !== ''; }));
        // Only show columns (areas) which have at least one assigned student for the selected room
        $visibleAreas = [];
        $hasGeneratedSchedule = false;
        foreach ($areas as $area) {
          $hasAssigned = false;
          foreach ($weekDates as $d) {
            $dayName = $d->format('l');
            $dayTasksRaw = $tasksByDay[$dayName]['tasks'] ?? [];
            if (is_array($dayTasksRaw) && isset($dayTasksRaw[$selectedRoom])) {
              $dayTasks = $dayTasksRaw[$selectedRoom];
            } else {
              $dayTasks = is_array($dayTasksRaw) ? $dayTasksRaw : [];
            }
            foreach ($dayTasks as $t) {
              $tArea = is_array($t) ? ($t['area'] ?? $t['name'] ?? '') : ($t->area ?? $t->name ?? '');
              if (trim(strtolower($tArea)) === trim(strtolower($area))) {
                $assignedName = is_array($t) ? ($t['student_name'] ?? $t['student'] ?? $t['name'] ?? '') : ($t->student_name ?? $t->student ?? $t->name ?? '');
                if (trim((string)$assignedName) !== '') {
                  $hasAssigned = true;
                  $hasGeneratedSchedule = true;
                  break 2; // area has an assignment; include it and stop searching
                }
              }
            }
          }
          if ($hasAssigned) $visibleAreas[] = $area;
        }
        // If no areas had assignments, leave visibleAreas empty so only Date/Day columns show

        $hasGeneratedSchedule = false;
        foreach ($weekDates as $d) {
          $dayName = $d->format('l');
          $dayTasksRaw = $tasksByDay[$dayName]['tasks'] ?? [];
          if (is_array($dayTasksRaw) && isset($dayTasksRaw[$selectedRoom])) {
            $roomDayTasks = $dayTasksRaw[$selectedRoom];
          } else {
            $roomDayTasks = is_array($dayTasksRaw) ? $dayTasksRaw : [];
          }

          if (!empty($roomDayTasks)) {
            $hasGeneratedSchedule = true;
            break;
          }
        }
      @endphp

            @if($hasGeneratedSchedule)
              <div class="tasks-list">
                @if(count($todayTasks))
                  @foreach($todayTasks as $task)
                    @php
                      // Support both array and object task shapes (admin data may be Eloquent models)
                      $taskArea = is_array($task) ? ($task['area'] ?? $task['name'] ?? '') : ($task->area ?? $task->name ?? '');
                      $taskDesc = is_array($task) ? ($task['desc'] ?? '') : ($task->desc ?? '');
                      $taskStatus = is_array($task) ? ($task['status'] ?? 'pending') : ($task->status ?? 'pending');
                    @endphp
                    <div class="task-row d-flex justify-content-between align-items-start bg-white p-3 mb-2 rounded border">
                      <div>
                        <div class="fw-600">{{ $taskArea }}</div>
                        <div class="small text-muted">{{ $taskDesc }}</div>
                      </div>
                      {{-- Student view: status is validated via the checklist; hide status here to avoid duplication --}}
                    </div>
                  @endforeach
                @endif
              </div>

              {{-- Weekly rotation table: Sunday - Saturday showing assigned student per area for the selected room --}}
              <div class="mt-3">
                <h6 id="rotationDateRange" class="mb-2">Week: {{ $weekDates[0]->format('M j') }} - {{ $weekDates[6]->format('M j, Y') }}</h6>
                <div class="rotation-table-container">
                  <table id="weeklyRotationTable" class="table table-bordered table-sm rotation-table" style="background:white;">
                    <thead>
                      <tr>
                        <th style="min-width:110px;">Date</th>
                        <th>Day</th>
                        @php
                          // Determine header areas dynamically from discovered visible areas.
                          // Do NOT fall back to a hardcoded canonical list. If no visible areas are
                          // discovered, render only Date/Day columns so admins/students don't see
                          // hardcoded sample areas that could overlap with managed templates.
                          $headerAreas = $visibleAreas;
                        @endphp
                        @foreach($headerAreas as $area)
                          <th>{{ $area }}</th>
                        @endforeach
                      </tr>
                    </thead>
                    <tbody>
                      @foreach($weekDates as $d)
                        @php
                          $dayName = $d->format('l');
                          $dateISO = $d->format('Y-m-d');
                          $dayTasksRaw = $tasksByDay[$dayName]['tasks'] ?? [];
                          if (is_array($dayTasksRaw) && isset($dayTasksRaw[$selectedRoom])) {
                            $dayTasks = $dayTasksRaw[$selectedRoom];
                          } else {
                            $dayTasks = is_array($dayTasksRaw) ? $dayTasksRaw : [];
                          }
                        @endphp
                        <tr>
                          <td>{{ $dateISO }}</td>
                          <td>{{ $dayName }}</td>
                          @foreach($headerAreas as $area)
                            @php
                              // Collect all assigned student names for this area on this day
                              $assignedNames = [];
                              foreach ($dayTasks as $t) {
                                $tArea = is_array($t) ? ($t['area'] ?? $t['name'] ?? '') : ($t->area ?? $t->name ?? '');
                                if (trim(strtolower($tArea)) === trim(strtolower($area))) {
                                  $name = is_array($t) ? ($t['student_name'] ?? $t['student'] ?? $t['name'] ?? '') : ($t->student_name ?? $t->student ?? $t->name ?? '');
                                  if (trim((string)$name) !== '') $assignedNames[] = trim((string)$name);
                                }
                              }
                              // If none found, show placeholder to indicate no assignment
                              $assignedCell = count($assignedNames) ? implode(', ', array_unique($assignedNames)) : 'NONE ASSIGNED';
                            @endphp
                            <td>{{ $assignedCell }}</td>
                          @endforeach
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @else
              <div class="text-center bg-white border rounded p-4 my-3">
                <div class="mb-2" style="font-size: 2.5rem; color: #94a3b8;">
                  <i class="fa-solid fa-calendar-xmark"></i>
                </div>
                <h5 class="mb-2">Room tasking schedule not available</h5>
                <p class="text-muted mb-0">Educators haven't generated a room tasking schedule for this room yet. Please check again later.</p>
              </div>
            @endif

            {{-- NOTE: Inspectors should NOT see the schedule management card above, but they still should see the rotation table and feedback below. The schedule management card is hidden earlier when $isInspector is true. --}}

            <script>
              // After the server-rendered weekly rotation table is built, attempt to fill any "NONE ASSIGNED"
              // placeholders using a saved/generated schedule_map. This ensures student view shows assignments
              // produced by admin's generated schedule even if server-side tasks were empty.
              (function(){
                try {
                  // Do not show or apply generated rotation schedules to inspectors
                  if (typeof window.isInspector !== 'undefined' && window.isInspector) return;
                  const roomKey = (typeof window.selectedRoom !== 'undefined' && window.selectedRoom) ? window.selectedRoom : '{{ $selectedRoom }}';
                  // Prefer in-memory studentSchedules, then _rotationScheduleSaved, then localStorage
                  let saved = null;
                  try { if (window.studentSchedules && window.studentSchedules[roomKey]) saved = window.studentSchedules[roomKey]; } catch(e){}
                  try { if (!saved && window._rotationScheduleSaved && window._rotationScheduleSaved[roomKey]) saved = window._rotationScheduleSaved[roomKey]; } catch(e){}
                  try { if (!saved) { const raw = localStorage.getItem('rotationSchedule_' + roomKey); if (raw) saved = JSON.parse(raw); } } catch(e){}
                  if (!saved || !saved.map) return; // nothing to apply

                  const map = saved.map || {};
                  const table = document.getElementById('weeklyRotationTable');
                  if (!table) return;

                  // Derive areas order from saved.map if possible (use task_area). This avoids hardcoding header columns.
                  const mapKeys = Object.keys(map).sort();
                  let derivedAreas = [];
                  for (let k of mapKeys) {
                    const assigns = map[k] || [];
                    if (Array.isArray(assigns) && assigns.length > 0) {
                      // Prefer explicit task_area field when present
                      derivedAreas = assigns.map(a => (a && (a.task_area || a.area || a.taskArea)) ? String(a.task_area || a.area || a.taskArea).trim() : '').filter(x => x);
                      if (derivedAreas.length) break;
                    } else if (assigns && typeof assigns === 'object') {
                      // Object keyed by area: use keys
                      derivedAreas = Object.keys(assigns).map(k2 => String(k2).trim()).filter(x => x);
                      if (derivedAreas.length) break;
                    }
                  }

                  // Fallback: read existing header areas if no derivedAreas found
                  const thead = table.querySelector('thead tr');
                  const headerAreas = thead ? Array.from(thead.querySelectorAll('th')).slice(2).map(h => h.textContent.trim()) : [];
                  const areas = derivedAreas.length ? derivedAreas : headerAreas;

                  // If derivedAreas differ from current header, rebuild the table header so students get admin-generated areas (no hardcoding)
                  if (derivedAreas.length && JSON.stringify(derivedAreas) !== JSON.stringify(headerAreas)) {
                    const newHead = ['<th style="min-width:110px;">Date</th>', '<th>Day</th>'].concat(derivedAreas.map(a => `<th>${a}</th>`)).join('');
                    const theadEl = table.querySelector('thead');
                    if (theadEl) theadEl.innerHTML = `<tr>${newHead}</tr>`;
                  }

                  const tbody = table.querySelector('tbody');
                  if (!tbody) return;
                  const rows = Array.from(tbody.querySelectorAll('tr'));

                  // parse saved start/end if present for filtering (inclusive)
                  // Prefer explicit keys from saved.map when available (these represent exact generated dates)
                  const mapKeysAll = map && typeof map === 'object' ? Object.keys(map).filter(k => k).sort() : [];
                  const parsedStart = (mapKeysAll && mapKeysAll.length) ? new Date(mapKeysAll[0] + 'T00:00:00') : (saved.start_date ? (new Date(saved.start_date + 'T00:00:00')) : null);
                  const parsedEnd = (mapKeysAll && mapKeysAll.length) ? new Date(mapKeysAll[mapKeysAll.length - 1] + 'T00:00:00') : (saved.end_date ? (new Date(saved.end_date + 'T00:00:00')) : null);

                  // Ensure the weekly table contains rows for every key in the saved map. If a date row is missing
                  // (for example the end date), insert it at the correct chronological position before filling.
                  try {
                    if (mapKeysAll && mapKeysAll.length) {
                      // build a map of existing rows by ISO date
                      const existingRows = {};
                      Array.from(tbody.querySelectorAll('tr')).forEach(r => {
                        const c = r.children[0];
                        if (c) existingRows[(c.textContent || '').trim()] = r;
                      });

                      for (let k of mapKeysAll) {
                        if (existingRows[k]) continue; // row exists
                        // create a new row for date k
                        const d = new Date(k + 'T00:00:00');
                        const dayName = d.toLocaleDateString(undefined, { weekday: 'long' });
                        const newRow = document.createElement('tr');
                        newRow.innerHTML = `<td style="white-space:nowrap;">${k}</td><td>${dayName}</td>` + areas.map(() => '<td>NONE ASSIGNED</td>').join('');

                        // insert in chronological order: find the first row with date > k and insert before it
                        let inserted = false;
                        const rowsNow = Array.from(tbody.querySelectorAll('tr'));
                        for (let i = 0; i < rowsNow.length; i++) {
                          const rowIso = (rowsNow[i].children[0] && rowsNow[i].children[0].textContent) ? rowsNow[i].children[0].textContent.trim() : null;
                          if (!rowIso) continue;
                          try {
                            const rowDate = new Date(rowIso + 'T00:00:00');
                            const thisDate = new Date(k + 'T00:00:00');
                            if (rowDate > thisDate) { rowsNow[i].parentNode.insertBefore(newRow, rowsNow[i]); inserted = true; break; }
                          } catch (e) { continue; }
                        }
                        if (!inserted) tbody.appendChild(newRow);
                        existingRows[k] = newRow;
                      }
                    }
                  } catch (e) { console.warn('insertMissingDateRows failed', e); }

                  // Re-evaluate rows after any inserted rows so newly added date rows get processed too
                  const allRows = Array.from(tbody.querySelectorAll('tr'));
                  allRows.forEach(row => {
                    const dateCell = row.children[0];
                    if (!dateCell) return;
                    const iso = dateCell.textContent.trim();
                    if (!iso) return;

                    // If admin provided a start/end range, skip rows outside that window
                    try {
                      const rowDate = new Date(iso + 'T00:00:00');
                      if (parsedStart && rowDate < parsedStart) { row.remove(); return; }
                      if (parsedEnd && rowDate > parsedEnd) { row.remove(); return; }
                    } catch (err) {
                      // if date parsing fails, continue without removing
                    }

                    const assigns = map[iso] || [];

                    // Helper to extract names from assignment entry
                    const extractNames = (assignment) => {
                      const names = [];
                      if (!assignment) return names;
                      if (Array.isArray(assignment)) {
                        assignment.forEach(a => { if (!a) return; if (typeof a === 'string') names.push(a.trim()); else if (a.student_name || a.name) names.push((a.student_name || a.name).trim()); });
                      } else if (typeof assignment === 'object') {
                        if (Array.isArray(assignment.assignments)) assignment.assignments.forEach(a => { if (a && (a.student_name || a.name)) names.push((a.student_name || a.name).trim()); });
                        else if (assignment.student_name) names.push(String(assignment.student_name).trim());
                        else if (assignment.name) names.push(String(assignment.name).trim());
                        else Object.values(assignment).forEach(v => { if (typeof v === 'string' && v.trim() !== '') names.push(v.trim()); });
                      } else if (typeof assignment === 'string') {
                        if (assignment.trim() !== '') names.push(assignment.trim());
                      }
                      return Array.from(new Set(names.filter(n => n)));
                    };

                    // Ensure there are enough cells for new areas; create if missing
                    for (let ci = 0; ci < areas.length; ci++) {
                      let cell = row.children[ci + 2];
                      if (!cell) {
                        cell = document.createElement('td');
                        row.appendChild(cell);
                      }
                      // assignment may be array or object keyed by area or index
                      let assignment = undefined;
                      if (Array.isArray(assigns)) {
                        assignment = assigns[ci] || assigns[areas[ci]] || assigns[String(ci)] || assigns[String(areas[ci])];
                      } else if (assigns && typeof assigns === 'object') {
                        // Try direct key match first
                        assignment = assigns[areas[ci]] || assigns[String(ci)] || assigns[Object.keys(assigns)[ci]];
                        // If not found, try case-insensitive key matching (robust to capitalization differences)
                        if (!assignment) {
                          const areaKey = areas[ci] ? String(areas[ci]).trim().toLowerCase() : null;
                          if (areaKey) {
                            for (const k2 of Object.keys(assigns)) {
                              if (String(k2).trim().toLowerCase() === areaKey) { assignment = assigns[k2]; break; }
                            }
                          }
                        }
                      }

                      const names = extractNames(assignment);
                      if (names.length > 0) {
                        cell.innerHTML = `<span class="generated-assignee-tag">${names.join(', ')}</span>`;
                      } else {
                        cell.textContent = 'NONE ASSIGNED';
                      }
                    }

                    // After filling, count how many areas have assigned students (exclude placeholders)
                    const expected = 2 + areas.length;
                    let assignedCount = 0;
                    for (let ci = 2; ci < expected; ci++) {
                      const c = row.children[ci];
                      if (!c) continue;
                      const txt = (c.textContent || '').trim();
                      if (txt && txt !== 'NONE ASSIGNED' && txt !== '-') assignedCount++;
                    }

                    // Only remove rows that have zero assigned students AND are not part of the saved map
                    // This preserves educator-generated dates (e.g., Oct 19) even if they temporarily have no assignments
                    try {
                      if (assignedCount === 0) {
                        const inMap = Array.isArray(mapKeysAll) && mapKeysAll.indexOf(iso) !== -1;
                        if (!inMap) {
                          row.remove();
                          return;
                        }
                        // otherwise keep the row and leave 'NONE ASSIGNED' visible
                      }
                    } catch (e) {
                      // On any error, don't remove the row to avoid accidental data loss in the UI
                    }

                    // If there are extra cells beyond areas length, trim them
                    const totalCells = row.children.length;
                    for (let ci = totalCells - 1; ci >= expected; ci--) {
                      row.removeChild(row.children[ci]);
                    }
                  });
                } catch (e) { console.warn('populate weekly rotation from saved schedule failed', e); }
              })();
            </script>

            <script>
              // Replace the server-rendered week header with the admin-provided
              // schedule start/end when a generated schedule exists for the room.
              (function(){
                try {
                  // Do not override week header for inspector users
                  if (typeof window.isInspector !== 'undefined' && window.isInspector) return;
                  const el = document.getElementById('rotationDateRange');
                  if (!el) return;
                  const roomKey = (typeof window.selectedRoom !== 'undefined' && window.selectedRoom) ? window.selectedRoom : '{{ $selectedRoom }}';

                  // Prefer in-memory studentSchedules, then _rotationScheduleSaved, then localStorage
                  let saved = null;
                  try { if (window.studentSchedules && window.studentSchedules[roomKey]) saved = window.studentSchedules[roomKey]; } catch(e){}
                  try { if (!saved && window._rotationScheduleSaved && window._rotationScheduleSaved[roomKey]) saved = window._rotationScheduleSaved[roomKey]; } catch(e){}
                  try { if (!saved) { const raw = localStorage.getItem('rotationSchedule_' + roomKey); if (raw) saved = JSON.parse(raw); } } catch(e){}
                  if (!saved) return;

                  // Prefer explicit map keys for exact generated schedule boundaries when available
                  let start = saved.start_date || null;
                  let end = saved.end_date || null;
                  try {
                    const mapKeys = saved.map && typeof saved.map === 'object' ? Object.keys(saved.map).filter(k => k) : [];
                    if (mapKeys.length > 0) {
                      mapKeys.sort();
                      start = mapKeys[0];
                      end = mapKeys[mapKeys.length - 1];
                    }
                  } catch (e) { /* ignore map parsing errors */ }
                  if (!start && !end) return;

                  const fmtLong = function(iso) {
                    if (!iso) return '';
                    try {
                      const d = new Date(iso + 'T00:00:00');
                      return d.toLocaleDateString('en-US', { month: 'long', day: 'numeric' });
                    } catch (e) { return iso; }
                  };

                  const fmtYear = function(iso) {
                    if (!iso) return '';
                    try { const d = new Date(iso + 'T00:00:00'); return d.getFullYear(); } catch (e) { return ''; }
                  };

                  if (start && end) {
                    const startFmt = fmtLong(start);
                    const endFmt = fmtLong(end);
                    const year = fmtYear(end) || fmtYear(start) || '';
                    el.textContent = `From ${startFmt} to ${endFmt}${year ? ', ' + year : ''}`;
                  } else if (start) {
                    const s = fmtLong(start);
                    const y = fmtYear(start);
                    el.textContent = `From ${s}${y ? ', ' + y : ''}`;
                  } else if (end) {
                    const e = fmtLong(end);
                    const y = fmtYear(end);
                    el.textContent = `Until ${e}${y ? ', ' + y : ''}`;
                  }
                } catch (e) { console.warn('rotationDateRange override failed', e); }
              })();
            </script>

            @if(!isset($isStudentView) || !$isStudentView)
            <div class="mt-3 d-flex gap-2">
              <a href="{{ route('student.room.tasking') }}" class="btn btn-primary">View My Room Checklist</a>
              <a href="{{ route('task.history') }}" class="btn btn-dark">View Other Room Checklists</a>
            </div>
            @endif
          </div>
          @endif
  <script>
    function toggleSidebar() {
      const el = document.getElementById('sidebar');
      if (!el) return;
      el.classList.toggle('open');
    }
  </script>
  <script>
    // Capture-phase handler: immediately update the row and send the update request
    (function() {
      if (window._taskModalCaptureAdded) return;
      window._taskModalCaptureAdded = true;
      document.addEventListener('click', async function(e) {
        const btn = e.target.closest ? e.target.closest('#taskModalSubmit') : null;
        if (!btn) return;
        const modalEl = document.getElementById('taskModal');
        const mode = modalEl?.dataset?.mode || (document.getElementById('modalTaskId')?.value ? 'edit' : 'add');
        // Only handle edit here to update existing rows
        if (mode !== 'edit') return;
        // Capture phase: prevent other handlers from swallowing the event
        try { e.preventDefault(); e.stopPropagation(); } catch (err) {}

        const id = document.getElementById('modalTaskId')?.value;
        const statusSelect = document.getElementById('modalStatus');
        if (!id || !statusSelect) {
          alert('Task information missing. Please refresh the page.');
          return;
        }

        const selectedStatusRaw = (statusSelect.value || '').trim().toLowerCase();
        const mappedStatus = selectedStatusRaw === 'checked'
          ? 'checked'
          : (selectedStatusRaw === 'wrong' ? 'wrong' : 'pending');

        const currentDay = document.getElementById('currentDay')?.textContent?.trim() || '';
        const currentWeek = document.getElementById('weekSelect')?.value || '';
        const currentMonth = document.getElementById('monthSelect')?.value || '';
        const currentYear = document.getElementById('yearSelect')?.value || '';
        const dayKey = `${currentYear}-${currentMonth}-${currentWeek}-${currentDay}`;

        const originalText = btn.textContent;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';

        try {
          const res = await fetch('/update-task-status', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
              taskId: id,
              status: mappedStatus,
              day: currentDay,
              week: currentWeek,
              month: currentMonth,
              year: currentYear,
              dayKey: dayKey
            })
          });

          const data = await res.json();
          if (!data || !data.success) {
            throw new Error(data && data.message ? data.message : 'Server rejected update');
          }

          if (typeof showNotification === 'function') showNotification('Task updated successfully', 'success');
          if (typeof loadTasksForCurrentDay === 'function') {
            await loadTasksForCurrentDay();
          }
        } catch (err) {
          console.error('Update request failed:', err);
          alert('Update failed: ' + (err.message || err));
        } finally {
          btn.disabled = false;
          btn.textContent = originalText;
          statusSelect.value = '';
          try {
            const modalOverlay = document.getElementById('taskModal');
            if (modalOverlay) {
              modalOverlay.classList.remove('show');
              modalOverlay.setAttribute('aria-hidden', 'true');
            }
          } catch (closeErr) { console.warn(closeErr); }
        }
      }, true);
    })();
  </script>


          <div class="time-selection-wrapper">
            <div class="time-controls">
              <select id="monthSelect" class="time-select">
                <option value="">Select Month</option>
                @for($i = 1; $i <= 12; $i++)
                  <option value="{{ $i }}">{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                @endfor
              </select>
              <select id="yearSelect" class="time-select">
                <option value="">Select Year</option>
                @for($i = 2025; $i <= 2040; $i++)
                  <option value="{{ $i }}">{{ $i }}</option>
                @endfor
              </select>
              <select id="weekSelect" class="time-select">
                <option value="">Select Week</option>
              </select>
              <button id="showCalendarBtn" type="button" class="select-date-btn">Select Date</button>
            </div>

            <!-- Calendar Date Picker (hidden until user clicks Select Date) -->
            <div class="calendar-container" id="calendarPanel" style="display:none;">
              <div class="calendar-header" style="display:flex;align-items:center;gap:8px;">
                        <button type="button" id="prevMonthBtn" class="calendar-nav-btn">‹</button>
                        <span id="calendarMonthYear" style="font-weight: 600; color: #374151; flex:1;"></span>
                        <div style="display:flex;gap:8px;align-items:center;">
                          <button type="button" id="toggleCalendarBtn" class="calendar-nav-btn" title="Show/Hide calendar">▾</button>
                          <button type="button" id="nextMonthBtn" class="calendar-nav-btn">›</button>
                        </div>
                      </div>
              <div class="calendar-grid">
                <div class="calendar-weekdays">
                  <div>Sun</div>
                  <div>Mon</div>
                  <div>Tue</div>
                  <div>Wed</div>
                  <div>Thu</div>
                  <div>Fri</div>
                  <div>Sat</div>
                </div>
                <div id="calendarDays" class="calendar-days"></div>
              </div>
              <div class="selected-date-display">
                <strong>Selected Date:</strong> <span id="selectedDateDisplay">{{ date('F j, Y') }}</span>
              </div>
            </div>
            <script>
              (function(){
                const btn = document.getElementById('showCalendarBtn');
                const panel = document.getElementById('calendarPanel');
                if (!btn || !panel) return;
                btn.addEventListener('click', function(){
                  if (panel.style.display === 'none' || panel.style.display === '') {
                    panel.style.display = 'block';
                    // scroll into view if needed
                    panel.scrollIntoView({behavior: 'smooth', block: 'nearest'});
                  } else {
                    panel.style.display = 'none';
                  }
                });

                // close calendar when clicking outside
                document.addEventListener('click', function(e){
                  if (!panel.contains(e.target) && e.target !== btn) {
                    panel.style.display = 'none';
                  }
                });
              })();
            </script>
          </div>
        </div>

        <!-- Day navigation and checklist -->
        <div class="content-main-row" style="margin-top: 10px;">
          <div style="display:flex; justify-content:center;">
            <div class="day-nav">
              <button id="prevDayButton">&laquo;</button>
              <span id="currentDay">{{ $currentDay }}</span>
              <button id="nextDayButton">&raquo;</button>
            </div>
          </div>

          <div class="checklist-wrapper">
<table class="table table-bordered align-middle" id="taskTable">
              <thead class="table-white">
                <tr>
                  <th>Assigned To</th>
                  <th>Task Area</th>
                  <th>Task Description</th>
                  <th>Status</th>
                  {{-- Actions column: visible for educator/inspector (non-student) views --}}
                  @if(!isset($isStudentView) || !$isStudentView)
                    <th class="actions-col">Actions</th>
                  @endif
                </tr>
              </thead>
              <tbody id="taskTableBody">
                @php
                  $dayData = $tasksByDay[$currentDay] ?? null;
                  $isCompleted = $dayData['isCompleted'] ?? false;
                  $roomTasks = $dayData && isset($dayData['tasks'][$selectedRoom]) ? $dayData['tasks'][$selectedRoom] : [];
                @endphp
                @if($roomTasks && count($roomTasks))
                  @php
                    // If the day is already completed, only show rows that have an explicit
                    // checked or wrong display_status. Rows that would render as '-' are
                    // placeholders and should be hidden after completion per UX request.
                    $visibleTasks = $roomTasks;
                    if ($isCompleted) {
                      $visibleTasks = array_values(array_filter($roomTasks, function($t) {
                        $ds = is_array($t) ? ($t['display_status'] ?? '') : ($t->display_status ?? '');
                        return in_array($ds, ['checked', 'wrong'], true);
                      }));
                    }
                    // Deduplicate by id if present else by area
                    $deduped = [];
                    $seenKeys = [];
                    foreach ($visibleTasks as $vt) {
                      $key = '';
                      if (is_array($vt)) $key = isset($vt['id']) && $vt['id'] !== '' ? 'id_'.$vt['id'] : ('area_'.strtolower(trim($vt['area'] ?? '')));
                      else $key = property_exists($vt, 'id') && $vt->id ? 'id_'.$vt->id : ('area_'.strtolower(trim($vt->area ?? '')));
                      if (!in_array($key, $seenKeys, true)) { $seenKeys[] = $key; $deduped[] = $vt; }
                      if (count($deduped) >= 7) break; // enforce max 7 rows
                    }
                    $visibleTasks = $deduped;
                  @endphp

                  @if(count($visibleTasks) === 0)
                    <tr>
                      <td colspan="{{ (isset($isStudentView) && $isStudentView) ? 4 : 5 }}" style="text-align:center;">No completed tasks to display for this room on {{ $currentDay }}.</td>
                    </tr>
                  @else
                    @foreach($visibleTasks as $task)
                      @php
                        $taskId = is_array($task) ? ($task['id'] ?? '') : ($task->id ?? '');
                        $taskName = is_array($task) ? ($task['name'] ?? 'N/A') : ($task->name ?? 'N/A');
                        $taskArea = is_array($task) ? ($task['area'] ?? 'N/A') : ($task->area ?? 'N/A');
                        $taskDesc = is_array($task) ? ($task['desc'] ?? 'N/A') : ($task->desc ?? 'N/A');
                        $taskStatus = is_array($task) ? ($task['status'] ?? 'pending') : ($task->status ?? 'pending');
                        $displayStatus = is_array($task) ? ($task['display_status'] ?? '') : ($task->display_status ?? '');
                      @endphp
                      <tr data-task-id="{{ $taskId }}">
                        @php
                          // Aggregate assigned student names for this task id/area on the current day
                          $assignedList = [];
                          // Search the day's raw room tasks for matching id or area
                          foreach (($roomTasks ?? []) as $rt) {
                            $rtId = is_array($rt) ? ($rt['id'] ?? '') : ($rt->id ?? '');
                            $rtArea = is_array($rt) ? ($rt['area'] ?? '') : ($rt->area ?? '');
                            $name = is_array($rt) ? ($rt['student_name'] ?? $rt['student'] ?? $rt['name'] ?? '') : ($rt->student_name ?? $rt->student ?? $rt->name ?? '');
                            if ($rtId && $taskId && (string)$rtId === (string)$taskId) {
                              if (trim((string)$name) !== '') $assignedList[] = trim((string)$name);
                            } elseif ($rtArea && $taskArea && trim(strtolower($rtArea)) === trim(strtolower($taskArea))) {
                              if (trim((string)$name) !== '') $assignedList[] = trim((string)$name);
                            }
                          }
                          $assignedNames = count($assignedList) ? implode(', ', array_unique($assignedList)) : ($taskName ?: 'NONE ASSIGNED');
                        @endphp
                        <td>{{ $assignedNames }}</td>
                        <td>{{ $taskArea }}</td>
                        <td>{{ $taskDesc }}</td>
                        <td style="text-align:center;">
                          @if($isCompleted)
                            @if($displayStatus === 'checked')
                              <button class="status-btn check-btn active" style="opacity: 1; background-color: #08a821; color: white; border-color: #08a821;" disabled>
                                <i class="fi fi-rr-check"></i>
                              </button>
                            @elseif($displayStatus === 'wrong')
                              <button class="status-btn wrong-btn active" style="opacity: 1; background-color: #e61515; color: white; border-color: #e61515;" disabled>
                                <i class="fi fi-rr-cross"></i>
                              </button>
                            @endif
                          @else
                            @if(isset($isStudentView) && $isStudentView)
                              {{-- Map legacy 'not yet' status to a student-friendly 'Pending' label --}}
                              <span class="student-readonly-status">{{ (strtolower($taskStatus) === 'not yet') ? 'Pending' : ucfirst($taskStatus) }}</span>
                            @else
                              <div class="status-buttons">
                                <button class="status-btn check-btn" onclick="updateStatus(this, '{{ $taskId }}', 'checked')">
                                    <i class="fas fa-check"></i>
                                  </button>
                                  <button class="status-btn wrong-btn" onclick="updateStatus(this, '{{ $taskId }}', 'wrong')">
                                    <i class="fas fa-times"></i>
                                  </button>
                              </div>
                            @endif
                          @endif
                        </td>
                        @if(!isset($isStudentView) || !$isStudentView)
                          <td class="actions-col">
                            <div class="action-buttons">
                              <button type="button" class="btn btn-sm btn-link p-0 edit-task-btn" onclick="openEditTaskRow(this);" title="Update Status">
                                <i class="fas fa-edit text-primary"></i>
                              </button>
                            </div>
                          </td>
                        @endif
                      </tr>
                    @endforeach
                  @endif
                @else
                  {{-- Use dynamic colspan so the placeholder row matches admin (5 cols) or student view (4 cols) --}}
                  <tr>
                    <td colspan="{{ (isset($isStudentView) && $isStudentView) ? 4 : 5 }}" style="text-align:center;">No tasks assigned for this room on {{ $currentDay }}.</td>
                  </tr>
                @endif
              </tbody>
            </table>

              @if(!isset($isStudentView) || !$isStudentView)
                <div class="task-buttons">
                  {{-- Educators/admins can add tasks; inspectors cannot. Both can mark day completed. --}}
               
                  <button id="markAllCompleted" class="mark-all-btn" onclick="markAllCompleted()">Mark as Completed</button>
                </div>
              @endif

            @if((!isset($isStudentView) || !$isStudentView) && !$isInspector)
              {{-- Admin/Educator view: schedule management card (hidden from inspectors) --}}
              <div class="card p-3 mb-4">
                <div class="d-flex align-items-end gap-2 flex-wrap">
                  <div>
                    <label class="form-label">Schedule Mode</label>
                    <select id="scheduleMode" class="form-select">
                      <option value="auto" selected>Automatic (use rotation)</option>
                      <option value="manual">Manual (assign students)</option>
                    </select>
                  </div>
                  <div>
                    <label class="form-label">Start Date</label>
                    <input type="date" id="scheduleStart" class="form-control">
                  </div>
                  <div>
                    <label class="form-label">End Date</label>
                    <input type="date" id="scheduleEnd" class="form-control">
                  </div>
                  <div>
                    <label class="form-label">Rotation Frequency</label>
                    <select id="rotationFrequency" class="form-select">
                      <option value="daily" selected>Daily</option>
                      <option value="weekly">Weekly</option>
                      <option value="monthly">Monthly</option>
                    </select>
                  </div>
                  <div class="ms-auto">
                    <button class="btn btn-primary" onclick="applySchedule()">
                      <i class="fi fi-rr-calendar"></i> Apply Schedule
                    </button>
                  </div>
                </div>
                  <small class="text-muted">Manual mode assigns selected students to the current day only. Auto mode copies the base template into the selected date or range.</small>
                </div>

                <!-- Container where the Generated Rotation Schedule will be rendered (admin) -->
                @if(!(isset($isInspector) && $isInspector))
                  <div id="adminScheduleResults" style="margin-top:12px;"></div>
                @endif
            @endif

            <!-- Legacy Task Form (kept for compatibility) -->
            <div id="taskFormContainer" class="hidden" style="background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin: 20px 0;">
              <h3 id="formTitle">Add Task</h3>
              <form id="taskForm" onsubmit="saveTask(event)">
                <input type="hidden" id="taskId" name="taskId" value="" />
                <div id="taskNameContainer" style="margin-bottom: 15px;">
                  <!-- Assigned To removed from add form UI. Rotation will assign students automatically. -->
                  <input type="hidden" id="taskName" name="taskName" value="" />
                </div>

                <div style="margin-bottom: 15px;">
                  <label for="taskArea" style="display: block; margin-bottom: 5px; font-weight: 500;">Area:</label>
                  <input type="text" id="taskArea" name="taskArea" placeholder="Enter area" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;" />
                </div>

                <div style="margin-bottom: 15px;">
                  <label for="taskDesc" style="display: block; margin-bottom: 5px; font-weight: 500;">Description:</label>
                  <textarea id="taskDesc" name="taskDesc" placeholder="Enter description" rows="4" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; resize: vertical;"></textarea>
                </div>

                <div class="form-buttons">
                  <button type="submit" class="btn task-form-btn btn-primary">Save Task</button>
                  <button type="button" class="btn task-form-btn btn-secondary" onclick="closeForm()">Cancel</button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="checklist-wrapper">
          <div class="feedback-section">
            <h3>Task Feedback</h3>
            @if(!isset($isStudentView) || !$isStudentView)
              <!-- Non-student view: Show feedback form (educators and inspectors can submit feedback) -->
              <form method="POST" action="{{ route('submit.feedback') }}" enctype="multipart/form-data" id="feedbackForm">
                @csrf
                <input type="hidden" name="room_number" value="{{ $selectedRoom }}">
                <input type="hidden" name="day" id="feedbackDay" value="{{ $currentDay }}">
                <input type="hidden" name="week" id="feedbackWeek" value="">
                <input type="hidden" name="month" id="feedbackMonth" value="">
                <input type="hidden" name="year" id="feedbackYear" value="">
                <textarea name="feedback" rows="4" placeholder="Write your feedback here..." required></textarea>
                <div class="custom-file-input" style="margin-top:10px;">
                  <label for="feedback_file">Choose Files (Maximum 3 Photos)</label>
                  <input type="file" id="feedback_file" name="feedback_files[]" multiple accept="image/*" onchange="validateFiles(this)" />
                  <div class="file-info">
                    <span class="file-name" id="fileName">No files chosen</span>
                    <div id="fileError" class="error-message" style="color: red; display: none;">Maximum 3 photos allowed.</div>
                  </div>
                </div>
                <button type="submit" id="submitFeedback">Submit Feedback</button>
              </form>
            @else
            
            @endif
            <!-- Feedback display container -->
            <div id="submittedFeedbacks">
              @if($feedbacks && count($feedbacks) > 0)
                @foreach($feedbacks as $feedback)
                  <div class="feedback-card" data-feedback-id="{{ $feedback->id }}" data-photo-paths="{{ json_encode(json_decode($feedback->photo_paths ?? '[]', true)) }}">
                    @if((!isset($isStudentView) || !$isStudentView) && !$isInspector)
                      <!-- Edit/Delete controls: only visible to educators/admins (not inspectors) -->
                      <div style="position:absolute;top:14px;right:18px;z-index:2;">
                        <button class="btn btn-md btn-primary edit-feedback-btn" data-id="{{ $feedback->id }}" style="margin-right:6px;">Edit Feedback</button>
                        <button class="btn btn-md btn-danger delete-feedback-btn" data-id="{{ $feedback->id }}">Delete Feedback</button>
                      </div>
                    @endif

                    <div style="margin-bottom:10px;">
                      <strong>Comment:</strong>
                      <div style="background:whitesmoke;border-radius:8px;padding:10px 14px;margin-top:4px;font-size:15px;">
                        {{ $feedback->feedback }}
                      </div>
                    </div>

                    <div style="font-size:13px;color:#666;margin-bottom:10px;">
                      Posted • {{ $feedback->created_at ? $feedback->created_at->timezone('Asia/Manila')->format('F j, Y · h:i A') : '' }}
                    </div>


<!-- Add/Edit Task Modal removed from feedback loop; a single global modal will be inserted below -->

                    @if($feedback->photo_paths)
                      @php
                        $photoPaths = json_decode($feedback->photo_paths, true) ?? [];
                      @endphp
                      @if(count($photoPaths) > 0)
                        <div style="display:flex;gap:14px;margin-bottom:10px;flex-wrap:wrap;">
                          @foreach($photoPaths as $photo)
                            <a href="/storage/{{ $photo }}" target="_blank" style="display:inline-block;">
                              <img src="/storage/{{ $photo }}" style="width:120px;height:90px;object-fit:cover;border-radius:8px;border:1px solid #dbe4f3;" />
                            </a>
                          @endforeach
                        </div>
                        <div style="font-size:13px;color:#888;margin-bottom:2px;">
                          {{ count($photoPaths) }} Photo{{ count($photoPaths) > 1 ? 's' : '' }}
                        </div>
                      @endif
                    @endif
                  </div>
                @endforeach
              @else
                <!-- Show "No feedback" message when there are no feedbacks on page load -->
                <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; text-align: center; color: #6c757d; margin: 10px 0;">
                  <i class="fi fi-rr-comment-slash" style="font-size: 1.5rem; margin-bottom: 8px; display: block;"></i>
                  <strong>No feedback available</strong>
                  <p style="margin: 5px 0 0 0; font-size: 0.9rem;">No feedback has been submitted for this day yet.</p>
                </div>
              @endif
            </div>
          </div>
        </div>
      @else
        <div class="room-selection">
          <h2>Please select a room from the dashboard to view its tasks.</h2>
          <a href="{{ route('dashboard') }}" class="btn-back">Back to Dashboard</a>
        </div>
      @endif
    </div>
  </div>

  <!-- Pass PHP variables to JavaScript -->
  <!-- Global Add/Edit Task Modal (single instance) -->
  <div id="taskModal" class="modal-overlay" aria-hidden="true">
    <div class="modal-card">
      <div class="modal-header">
        <span id="taskModalTitle">Add New Task</span>
        <button class="btn btn-outline task-modal-close" type="button" aria-label="Close">✕</button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="modalTaskId">
        <div class="mb-2">
          <label class="form-label">Status</label>
          <select id="modalStatus" class="form-select">
            <option value="checked">Completed task</option>
            <option value="wrong">Incomplete task</option>
          </select>
          <small class="status-helper-text">Choose whether the task is Completed or Incomplete. Select Cancel to keep the current status.</small>
        </div>
        <div style="display:none;" aria-hidden="true">
          <input type="hidden" id="modalArea" value="">
          <textarea id="modalDesc" rows="1" maxlength="500"></textarea>
          <span id="descCount">0</span>
          <input type="hidden" id="modalDate" value="">
        </div>
      </div>
    <div class="modal-footer">
  <button class="btn btn-outline task-modal-close" type="button">Cancel</button>
  <button id="taskModalSubmit" type="button" class="btn btn-primary">Update Status</button>
    </div>
    </div>
  </div>
  <script>
    // Ensure a safe handleTaskSubmit exists early so clicks on the modal submit
    // won't throw 'not defined' while the real implementation is still loading.
    if (typeof window.handleTaskSubmit !== 'function') {
      // Create a polling fallback that waits briefly for the real handler to be defined
      const fallback = async function(mode) {
        console.log('Fallback handleTaskSubmit invoked, mode=', mode, '- will wait for real handler');
        const modalEl = document.getElementById('taskModal');
        if (modalEl) modalEl.dataset.mode = mode || (modalEl.dataset.mode || 'add');

        const start = Date.now();
        const timeout = 800; // ms
        // If later the page replaces window.handleTaskSubmit, the comparison below will detect it
        while (Date.now() - start < timeout) {
          // If handleTaskSubmit was replaced with a different function, call it
          if (window.handleTaskSubmit && window.handleTaskSubmit !== fallback) {
            try {
              await window.handleTaskSubmit(mode);
            } catch (e) {
              console.error('Real handleTaskSubmit failed after fallback detected it:', e);
            }
            return;
          }
          // small delay
          // eslint-disable-next-line no-await-in-loop
          await new Promise(r => setTimeout(r, 50));
        }

        console.warn('Fallback timed out waiting for real handleTaskSubmit; no-op to avoid recursion');
      };

      window.handleTaskSubmit = fallback;
    }

    // Global function definitions for task management
    window.openEditTaskRow = function(btn) {
      console.log('Edit button clicked', btn);
      const row = btn.closest('tr');
      if (!row) {
        console.error('No row found');
        return;
      }

      const taskId = row.getAttribute('data-task-id');
      if (!taskId) {
        alert('Task ID not found');
        return;
      }

      // Extract task data from the row
      const task = {
        id: taskId,
        name: row.children[0]?.textContent?.trim() || '',
        area: row.children[1]?.textContent?.trim() || '',
        desc: row.children[2]?.textContent?.trim() || '',
        status: row.querySelector('.check-btn.active') ? 'completed' :
                (row.querySelector('.wrong-btn.active') ? 'wrong' : 'pending')
      };

      console.log('Opening edit modal for task:', task);

      // Check if modal exists
      const modal = document.getElementById('taskModal');
      if (!modal) {
        alert('Edit modal not found. Please refresh the page.');
        return;
      }

  // Open the edit modal with task data. Prefer the global helper if present,
  // otherwise fall back to an inline implementation so Edit works reliably.
  try {
    console.log('window.openEditTaskModal typeof:', typeof window.openEditTaskModal, 'value:', window.openEditTaskModal);
    if (typeof window.openEditTaskModal === 'function') {
      window.openEditTaskModal(task);
      return;
    }

  // Inline fallback: populate modal fields and show it
    console.warn('window.openEditTaskModal not found — using inline fallback to open modal');
    // Populate modal title and id
    const titleEl = document.getElementById('taskModalTitle');
    if (titleEl) titleEl.textContent = 'Update Task Status';
    const modalIdEl = document.getElementById('modalTaskId');
    if (modalIdEl) modalIdEl.value = task.id || '';

    // Modal no longer allows editing assigned student. If a modalStudent element exists,
    // set it safely but do not rely on it.
    const modalStudent = document.getElementById('modalStudent');
    if (modalStudent) { try { modalStudent.value = task.name || ''; } catch (e) { /* ignore */ } }

    // Other fields
    const modalArea = document.getElementById('modalArea'); if (modalArea) modalArea.value = task.area || '';
    const modalDesc = document.getElementById('modalDesc'); if (modalDesc) modalDesc.value = task.desc || '';
    const descCount = document.getElementById('descCount'); if (descCount) descCount.textContent = (task.desc || '').length;
    const modalDate = document.getElementById('modalDate'); if (modalDate) modalDate.value = task.date || new Date().toISOString().split('T')[0];

    // Populate modal status select if present. Map legacy/computed values to modal options.
    try {
      const modalStatus = document.getElementById('modalStatus');
      if (modalStatus) {
        // Normalize incoming task.status values to our modal options
        const s = (task.status || '').toString().toLowerCase();
        if (s === 'checked' || s === 'completed' || s === 'done') modalStatus.value = 'checked';
        else if (s === 'wrong' || s === 'incorrect' || s === 'failed') modalStatus.value = 'wrong';
        else modalStatus.value = '';
      }
    } catch (e) { /* ignore */ }

    // Submit handler and show modal (manage aria-hidden and focus)
    const submitBtn = document.getElementById('taskModalSubmit');
    if (submitBtn) submitBtn.textContent = 'Update Task';
    const modalEl = document.getElementById('taskModal');
    if (modalEl) {
      modalEl.classList.add('show');
      modalEl.setAttribute('aria-hidden', 'false');
      // focus first interactive element inside modal
      const firstFocusable = modalEl.querySelector('select, input, button, textarea');
      if (firstFocusable) {
        try { firstFocusable.focus(); } catch (e) { /* ignore focus errors */ }
      }
    }
  if (modalEl) modalEl.dataset.mode = 'edit';
  } catch (e) {
    console.error('Fallback openEditTaskModal failed:', e);
    alert('Failed to open edit modal. See console for details.');
  }
    };

    window.deleteTaskRow = function(btn) {
      console.log('Delete button clicked', btn);
      const row = btn.closest('tr');
      if (!row) {
        console.error('No row found');
        return;
      }

      const taskId = row.getAttribute('data-task-id');
      if (!taskId) {
        alert('Task ID not found');
        return;
      }

      // Show confirmation dialog with task details
      const taskName = row.children[0]?.textContent?.trim() || 'Unknown';
      const taskArea = row.children[1]?.textContent?.trim() || 'Unknown';

      if (!confirm(`Are you sure you want to delete this task?\n\nStudent: ${taskName}\nArea: ${taskArea}\n\nThis action cannot be undone.`)) {
        return;
      }

      // Show loading state (global overlay + per-button fallback)
      btn.disabled = true;
      const originalText = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Deleting...';
      try { showLoaderRT('Deleting task...'); } catch(e) {}

      fetch(`{{ route('room.management.task.delete', ['id' => ':id']) }}`.replace(':id', taskId), {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Remove row with animation
          row.style.transition = 'opacity 0.3s ease';
          row.style.opacity = '0';
          setTimeout(() => {
            row.remove();
            if (typeof loadTasksForCurrentDay === 'function') {
              loadTasksForCurrentDay();
            }
          }, 300);

          // Show success message
          try { hideLoaderRT(); showResultRT('success', 'Task deleted successfully', 1800); } catch(e) { showNotification('Task deleted successfully', 'success'); }
        } else {
          throw new Error(data.message || 'Failed to delete task');
        }
      })
      .catch(error => {
        console.error('Delete error:', error);
        try { hideLoaderRT(); showResultRT('error', 'Delete failed: ' + (error.message || ''), 4000); } catch(e) { alert('Delete failed: ' + error.message); }

        // Reset button state
        btn.disabled = false;
        btn.innerHTML = originalText;
      });
    };

    // Global notification function
    window.showNotification = function(message, type = 'info') {
      // Remove any existing notifications
      const existingNotifications = document.querySelectorAll('.notification');
      existingNotifications.forEach(n => n.remove());

      // Create notification element
      const notification = document.createElement('div');
      notification.className = `notification ${type}`;
      notification.innerHTML = `
        <div style="display: flex; align-items: center; justify-content: space-between;">
          <span>${message}</span>
          <button onclick="this.parentElement.parentElement.remove()" style="background: none; border: none; color: white; font-size: 18px; cursor: pointer; margin-left: 10px;">&times;</button>
        </div>
      `;

      document.body.appendChild(notification);

      // Auto remove after 4 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.style.animation = 'slideOut 0.3s ease';
          setTimeout(() => notification.remove(), 300);
        }
      }, 4000);
    };

    // Test function to verify everything is working
    window.testFunctions = function() {
      console.log('Testing functions...');

      if (typeof openEditTaskRow === 'function') {
        console.log('✓ openEditTaskRow is available');
      } else {
        console.error('✗ openEditTaskRow is NOT available');
      }

      if (typeof deleteTaskRow === 'function') {
        console.log('✓ deleteTaskRow is available');
      } else {
        console.error('✗ deleteTaskRow is NOT available');
      }

      if (typeof showNotification === 'function') {
        console.log('✓ showNotification is available');
        showNotification('Test notification - functions are working!', 'success');
      } else {
        console.error('✗ showNotification is NOT available');
      }

      // Test if modal exists
      const modal = document.getElementById('taskModal');
      if (modal) {
        console.log('✓ Task modal found');
      } else {
        console.error('✗ Task modal NOT found');
      }

      // Test if buttons exist
      const editBtns = document.querySelectorAll('.edit-task-btn');
      const deleteBtns = document.querySelectorAll('.delete-task-btn');
      console.log(`Found ${editBtns.length} edit buttons and ${deleteBtns.length} delete buttons`);

      alert('Function t est completed. Check console for details.');
    };
  </script>
  <script>

  // Use week order: (PHP provides days) — week calculations below use ISO-style weeks (Monday to Sunday)
  const days = @json($daysOfWeek);
    let currentDayIndex = {{ array_search($currentDay, $daysOfWeek) }};
    const currentDayElement = document.getElementById("currentDay");
    const taskTableBody = document.getElementById("taskTableBody");
    const markAllCompletedBtn = document.getElementById("markAllCompleted");
    let weekDayCompletionStatus = @json($weekDayCompletionStatus ?? []);
    let currentWeek = document.getElementById('weekSelect').value;
    let currentMonth = document.getElementById('monthSelect').value;
    let currentYear = document.getElementById('yearSelect').value;
    let editTaskIndex = null;
  // Normalize selectedRoom to an empty string when null and ensure it's a string type
  window.selectedRoom = (function(){ const r = @json($selectedRoom); return r === null ? '' : String(r); })();
    const isStudentView = @json($isStudentView ?? false);

  function getWeekDates(year, month, weekNumber) {
    // ISO-style weeks: Monday to Sunday.
    // Compute the start (Monday) and end (Sunday) for the given week number
    // within the calendar month view. Week 1 is the first ISO-week that
    // contains the 1st of the month (backfill to the previous Monday if needed).

    // First day of the month
    const firstDayOfMonth = new Date(year, month - 1, 1);

    // Determine the ISO weekday for the first day (0 = Monday, 6 = Sunday for our mapping)
    // JS getDay(): 0 = Sunday, 1 = Monday ... 6 = Saturday
    const jsDow = firstDayOfMonth.getDay();
    // Map JS dow to ISO index where Monday=0..Sunday=6
    const isoIndex = (jsDow === 0) ? 6 : (jsDow - 1);

    // Start date for week 1: go back to the Monday on or before the 1st
    const startOfWeek1 = new Date(firstDayOfMonth);
    startOfWeek1.setDate(firstDayOfMonth.getDate() - isoIndex);

    // For requested weekNumber, add (weekNumber - 1) * 7 days to startOfWeek1
    const startDate = new Date(startOfWeek1);
    startDate.setDate(startOfWeek1.getDate() + (weekNumber - 1) * 7);

    // End date is Sunday (6 days after Monday)
    const endDate = new Date(startDate);
    endDate.setDate(startDate.getDate() + 6);

    return { startDate, endDate };
  }

    function updateWeeks() {
        const monthSelect = document.getElementById('monthSelect');
        const yearSelect = document.getElementById('yearSelect');
        const weekSelect = document.getElementById('weekSelect');

        const month = parseInt(monthSelect.value);
        const year = parseInt(yearSelect.value);

        if (month && year) {
            const firstDay = new Date(year, month - 1, 1);
            const lastDay = new Date(year, month, 0);
            const daysInMonth = lastDay.getDate();

            // For ISO-style weeks (Monday-Sunday), find the Monday on or before the 1st
            const jsDow = firstDay.getDay(); // 0=Sun,1=Mon..6=Sat
            const isoIndex = (jsDow === 0) ? 6 : (jsDow - 1); // Monday=0..Sunday=6
            const startOfWeek1 = new Date(firstDay);
            startOfWeek1.setDate(firstDay.getDate() - isoIndex);

            // Total span (in days) from startOfWeek1 to last day of month inclusive
            const msPerDay = 24 * 60 * 60 * 1000;
            const spanDays = Math.floor((lastDay - startOfWeek1) / msPerDay) + 1;
            const weeksInMonth = Math.ceil(spanDays / 7);

            // Clear and add default option
            weekSelect.innerHTML = '<option value="">Select Week</option>';

            // Show weeks based on actual calendar weeks
            for (let i = 1; i <= weeksInMonth; i++) {
                const { startDate, endDate } = getWeekDates(year, month, i);

                // Format dates
                const startDateStr = startDate.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });
                const endDateStr = endDate.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric'
                });

                const option = document.createElement('option');
                option.value = i;
                option.textContent = `Week ${i} (${startDateStr} - ${endDateStr})`;
                weekSelect.appendChild(option);
            }
        } else {
            weekSelect.innerHTML = '<option value="">Select Week</option>';
        }
    }

    // Add this helper function before DOMContentLoaded
function checkWeekCompletionAndAlert(room, week, month, year) {
    // This function checks if all 7 days are completed for the selected room/week/month/year
    // and shows a popup if not completed.
    fetch('/api/check-week-completion', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            room: room,
            week: week,
            month: month,
            year: year
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data && data.completed === false) {
            alert("Checklist for this room is not yet completed for the week.");
        }
    });
}

    // Add event listeners when the DOM is loaded
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM loaded, initializing task management...');

      // Test if functions are available
      if (typeof openEditTaskRow === 'function') {
        console.log('openEditTaskRow function is available');
      } else {
        console.error('openEditTaskRow function is NOT available');
      }

      if (typeof deleteTaskRow === 'function') {
        console.log('deleteTaskRow function is available');
      } else {
        console.error('deleteTaskRow function is NOT available');
      }

      // Add click handlers for the task buttons (only if not student view)
      if (!isStudentView) {
        const addTaskBtn = document.getElementById('addTaskButton');
        const editTaskBtn = document.getElementById('editTaskButton');

        if (addTaskBtn) {
          addTaskBtn.addEventListener('click', function() {
            openForm('add');
          });
        }

        if (editTaskBtn) {
          editTaskBtn.addEventListener('click', function() {
            openForm('edit');
          });
        }
      }

      // Get dropdown elements
      const monthSelect = document.getElementById('monthSelect');
      const yearSelect = document.getElementById('yearSelect');
      const weekSelect = document.getElementById('weekSelect');

      if (monthSelect && yearSelect && weekSelect) {
        // Add change event listeners
        monthSelect.addEventListener('change', function() {
          updateWeeks();
          loadTasksForCurrentDay();
          // clear static date mode when using month/week/year selections
          try { document.body.classList.remove('static-view'); } catch(e) {}
          // Check completion for the new selection
          const room = window.selectedRoom;
          const week = weekSelect.value;
          const month = monthSelect.value;
          const year = yearSelect.value;
          if (room && week && month && year) {
              checkWeekCompletionAndAlert(room, week, month, year);
          }
        });

        yearSelect.addEventListener('change', function() {
          updateWeeks();
          loadTasksForCurrentDay();
          try { document.body.classList.remove('static-view'); } catch(e) {}
          const room = window.selectedRoom;
          const week = weekSelect.value;
          const month = monthSelect.value;
          const year = yearSelect.value;
          if (room && week && month && year) {
              checkWeekCompletionAndAlert(room, week, month, year);
          }
        });

        weekSelect.addEventListener('change', function() {
          loadTasksForCurrentDay();
          onFeedbackSelectionChange(); // Also update feedback
          try { document.body.classList.remove('static-view'); } catch(e) {}
          const room = window.selectedRoom;
          const week = weekSelect.value;
          const month = monthSelect.value;
          const year = yearSelect.value;
          if (room && week && month && year) {
              checkWeekCompletionAndAlert(room, week, month, year);
          }
        });

        // Set current month and year to today
        const today = new Date();
        monthSelect.value = today.getMonth() + 1;
        yearSelect.value = today.getFullYear();

        // Update weeks and set default week to the week that contains today
        updateWeeks();

        // After weeks populate, pick the option whose start/end range includes today
        setTimeout(() => {
          if (weekSelect.options.length > 1) {
            let matched = null;
            const selMonth = parseInt(monthSelect.value, 10);
            const selYear = parseInt(yearSelect.value, 10);
            for (let i = 1; i < weekSelect.options.length; i++) {
              const wk = parseInt(weekSelect.options[i].value, 10);
              try {
                const { startDate, endDate } = getWeekDates(selYear, selMonth, wk);
                const sd = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                const ed = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
                const now = new Date(today.getFullYear(), today.getMonth(), today.getDate());
                if (now >= sd && now <= ed) { matched = wk; break; }
              } catch (e) {
                // ignore and continue
              }
            }
            if (matched === null) {
              // fallback to first week option
              matched = parseInt(weekSelect.options[1].value, 10) || 1;
            }
            weekSelect.value = String(matched);
            // Trigger change event to load tasks and feedback
            loadTasksForCurrentDay();
            onFeedbackSelectionChange();
          }
        }, 300);
      }

      // Initialize feedback
      syncFeedbackFormFields();
      loadFeedbacks();
      // Highlight current user's name in any pre-rendered tables
      try { highlightMyName(); } catch (e) { /* ignore if function not yet defined */ }
    });

    // Highlight occurrences of the current user's name inside assignment tables.
    // container optional: if provided, only search inside that element; otherwise search common tables.
    function highlightMyName(container) {
      try {
        const me = (window.currentUserFullName || '').toString().trim();
        if (!me) return;
        const esc = s => s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const re = new RegExp('(' + esc(me) + ')', 'gi');

        // selectors to search: task table assigned-to cells, weekly rotation table cells, generated schedule table
        const root = container || document;
        const selectors = [
          '.occupant-meta .occupant-pill',
          '#taskTable tbody tr td:first-child',
          '#weeklyRotationTable tbody tr td',
          '#generatedRotationTable tbody tr td',
          '#generatedScheduleCard table tbody tr td'
        ];

        selectors.forEach(sel => {
          const nodes = Array.from(root.querySelectorAll ? root.querySelectorAll(sel) : []);
          nodes.forEach(n => {
            try {
              // Avoid re-highlighting if already contains highlight span
              if (n.querySelector && n.querySelector('.my-name-highlight')) return;
              const html = n.innerHTML || '';
              // Only replace plain text occurrences (case-insensitive)
              const newHtml = html.replace(re, '<span class="my-name-highlight">$1</span>');
              if (newHtml !== html) n.innerHTML = newHtml;
            } catch (e) { /* ignore per-node errors */ }
          });
        });
      } catch (e) { console.warn('highlightMyName failed', e); }
    }

    // Duplicate event listeners removed - now handled in main DOMContentLoaded

    // Unified function to change day by offset and reload data, always using Sunday-Saturday order
    function changeDayByOffset(offset) {
      // Always use the days array for navigation order (Sunday-Saturday)
      currentDayIndex = (currentDayIndex + offset + days.length) % days.length;
      currentDayElement.textContent = days[currentDayIndex];

      // Debug: Log the navigation to verify correct order
      console.log('Day navigation:', {
        offset: offset,
        newIndex: currentDayIndex,
        newDay: days[currentDayIndex],
        daysArray: days
      });

      loadTasksForCurrentDay();
      onFeedbackSelectionChange();
    }

    // Attach navigation listeners only once, always using days[] order
    document.getElementById('prevDayButton').onclick = function() {
      changeDayByOffset(-1);
    };
    document.getElementById('nextDayButton').onclick = function() {
      changeDayByOffset(1);
    };

    // If user changes the day manually (e.g., by code), keep currentDayIndex in sync
    function setDayByName(dayName) {
      const idx = days.indexOf(dayName);
      if (idx !== -1) {
        currentDayIndex = idx;
        currentDayElement.textContent = days[currentDayIndex];
        loadTasksForCurrentDay();
        onFeedbackSelectionChange();
      }
    }

    function openForm(mode) {
      currentMode = mode;
      const formContainer = document.getElementById('taskFormContainer');
      const title = document.getElementById('formTitle');
      const taskNameContainer = document.getElementById('taskNameContainer');
      const form = document.getElementById('taskForm');

      if (!formContainer || !title || !taskNameContainer || !form) {
        console.error('Required form elements not found');
        return;
      }

      if (mode === 'add') {
        title.textContent = 'Add Task';
        // Admin should not assign students when creating a task. Tasks are assigned
        // automatically by the rotation schedule. Provide a read-only note instead
        // and keep a hidden taskName input for compatibility.
        taskNameContainer.innerHTML = `
          <!-- Assigned To is handled by rotation schedule; keep a hidden taskName for compatibility -->
          <input type="hidden" id="taskName" name="taskName" value="" />
          <input type="hidden" id="taskId" name="taskId" value="" />
        `;
        document.getElementById('taskArea').value = '';
        document.getElementById('taskDesc').value = '';
        form.reset();
      } else if (mode === 'edit') {
        title.textContent = 'Edit Task';
        const currentDay = document.getElementById('currentDay').textContent;
        const room = '{{ $selectedRoom }}';

        // Get tasks from the table if possible, fallback to tasksByDay
        let tasks = [];
        const rows = document.querySelectorAll('#taskTableBody tr[data-task-id]');
        if (rows.length > 0) {
          rows.forEach((row, idx) => {
            if (row.querySelector('td') && row.querySelectorAll('td').length >= 3) {
              tasks.push({
                name: row.children[0].textContent.trim(),
                area: row.children[1].textContent.trim(),
                desc: row.children[2].textContent.trim(),
                id: row.getAttribute('data-task-id') || idx
              });
            }
          });
        }
        if (tasks.length === 0 && tasksByDay[currentDay] && tasksByDay[currentDay][room]) {
          tasks = tasksByDay[currentDay][room];
        }

        taskNameContainer.innerHTML = `
          <label for="taskNameDropdown">Select Task to Edit:</label>
          <select id="taskNameDropdown" name="taskNameDropdown" required>
            ${tasks.map((task, index) => `
              <option value="${index}" data-task-id="${task.id}">${task.name} - ${task.area}</option>
            `).join('')}
          </select>
          <label for="taskName">Change Name To:</label>
          <input type="text" id="taskName" name="taskName" placeholder="Enter new name" required />
          <input type="hidden" id="taskId" name="taskId" value="" />
        `;

        // Remove previous status display if present
        const prevStatus = document.getElementById('editStatusDisplay');
        if (prevStatus) prevStatus.remove();

        // Load the first task by default
        loadSelectedTask(0);

        // Add change event to dropdown
        const nameDropdown = document.getElementById('taskNameDropdown');
        if (nameDropdown) {
          nameDropdown.addEventListener('change', function() {
            loadSelectedTask(this.value);
          });
        }
      }

      formContainer.classList.remove('hidden');
    }

    function loadSelectedTask(index) {
  const currentDay = document.getElementById('currentDay').textContent;
  const room = '{{ $selectedRoom }}';

  // Try to get tasks from the table if possible, fallback to tasksByDay
  let tasks = [];
  const rows = document.querySelectorAll('#taskTableBody tr[data-task-id]');
  if (rows.length > 0) {
    rows.forEach((row, idx) => {
      if (row.querySelector('td') && row.querySelectorAll('td').length >= 3) {
        tasks.push({
          name: row.children[0].textContent.trim(),
          area: row.children[1].textContent.trim(),
          desc: row.children[2].textContent.trim(),
          id: row.getAttribute('data-task-id') || idx
        });
      }
    });
  }
  if (tasks.length === 0 && tasksByDay[currentDay] && tasksByDay[currentDay][room]) {
    tasks = tasksByDay[currentDay][room];
  }

  const task = tasks[index];
  if (task) {
    document.getElementById('taskName').value = task.name;
    document.getElementById('taskArea').value = task.area;
    document.getElementById('taskDesc').value = task.desc;
    document.getElementById('taskId').value = task.id || '';
    editTaskIndex = index;

    // Remove status display if present (for edit form, status is not shown)
    const prevStatus = document.getElementById('editStatusDisplay');
    if (prevStatus) prevStatus.remove();
  }
    }

    function closeForm() {
      const formContainer = document.getElementById('taskFormContainer');
      const form = document.getElementById('taskForm');
      if (formContainer && form) {
        formContainer.classList.add('hidden');
        form.reset();
      }
    }

    function saveTask(event) {
      event.preventDefault();

      const currentDay = document.getElementById('currentDay').textContent;
      const room = '{{ $selectedRoom }}';
  // Admin no longer provides Assigned To; keep name empty and let rotation assign later
  const name = '';
  const area = document.getElementById('taskArea').value;
  const desc = document.getElementById('taskDesc').value;
      const taskId = document.getElementById('taskId') ? document.getElementById('taskId').value : null;

      // Validate inputs (Assigned To not required here)
      if (!area || !desc) {
        alert('Please fill in all fields');
        return;
      }
      if (!room) { alert('Select a room first by navigating to /roomtask/{room}.'); return; }

  // Use RoomManagementController endpoints which expect keys: room_number, area, description, day, assigned_to
      const payload = {
        room_number: room,
        area: area,
        description: desc,
        day: currentDay,
        assigned_to: '', // assignments are handled by rotation schedule
        // Ensure the task is applied globally to all dates/weeks/months/years
        apply_to_all: true
      };

      // Choose route based on mode
      let url = '{{ route('room.management.task.create') }}';
      let opts = {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify(payload)
      };

      if (currentMode === 'edit') {
        // If editing, call PUT to the update route with the task id
        const id = taskId || document.getElementById('taskId')?.value;
        url = `{{ route('room.management.task.update', ['id' => ':id']) }}`.replace(':id', id || '');
        opts.method = 'PUT';
        // include assigned_to when editing if a name was provided (compat)
        const newName = document.getElementById('taskName') ? document.getElementById('taskName').value : '';
        if (newName) opts.body = JSON.stringify(Object.assign({}, payload, { assigned_to: newName }));
        else opts.body = JSON.stringify(payload);
      }

  try { showLoaderRT(currentMode === 'add' ? 'Adding task...' : 'Updating task...'); } catch(e) {}

  fetch(url, opts)
      .then(async response => {
        // Robust parsing: prefer JSON, but if server returned HTML (error page), extract text and strip tags
        if (response.ok) {
          try {
            return await response.json();
          } catch (e) {
            // response body not JSON; try to read text and normalize
            const txt = await response.text();
            const stripped = txt.replace(/<[^>]*>/g, '\\n').trim();
            return { success: false, message: stripped || 'Server returned an invalid response' };
          }
        } else {
          // Try JSON error message first
          try {
            const d = await response.json();
            const msg = (d && d.message) ? d.message : JSON.stringify(d);
            throw new Error(msg);
          } catch (e) {
            // Fallback to plain text (possibly HTML) - strip tags for readability
            try {
              const txt = await response.text();
              const stripped = txt.replace(/<[^>]*>/g, '\\n').trim();
              throw new Error(stripped || ('HTTP error ' + response.status));
            } catch (e2) {
              throw new Error('HTTP error ' + response.status);
            }
          }
        }
      })
      .then (data => {
        if (data.success) {
          // Only update tasksByDay for add mode (optional, but not needed for edit)
          if (currentMode === 'add') {
            // When adding a task via the inline form, mirror it into every day
            // so it appears on all checklists. Use the global `days` array if
            // available, otherwise fall back to server-rendered days list.
            const allDays = (typeof days !== 'undefined') ? days : @json($daysOfWeek);
            (allDays || []).forEach(d => {
              if (!tasksByDay[d]) tasksByDay[d] = {};
              if (!tasksByDay[d][room]) tasksByDay[d][room] = [];
              tasksByDay[d][room].push({
                name: '', // new tasks are unassigned until rotation assigns students
                area: area,
                desc: desc,
                status: 'pending'
              });
            });
          }
          // For edit, do not update tasksByDay directly (let loadTasksForCurrentDay handle it)
          loadTasksForCurrentDay();
          closeForm();
          try { hideLoaderRT(); showResultRT('success', 'Task saved successfully!', 2200); } catch(e) { alert('Task saved successfully!'); }
        } else {
          throw new Error(data.message || 'Failed to save task');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        try { hideLoaderRT(); showResultRT('error', 'Error saving task: ' + (error.message || ''), 4000); } catch(e) { alert('An error occurred while saving the task: ' + error.message); }
      })
    }

    function updateStatus(button, taskId, status) {
        // Prevent status updates in student view
        if (isStudentView) {
            alert('You are in student view mode. You cannot modify task statuses.');
            return;
        }

        const currentDay = document.getElementById('currentDay').textContent;
        const currentWeek = document.getElementById('weekSelect').value;
        const currentMonth = document.getElementById('monthSelect').value;
        const currentYear = document.getElementById('yearSelect').value;
        const room = window.selectedRoom;

        // Create a unique key for this specific day
        const dayKey = `${currentYear}-${currentMonth}-${currentWeek}-${currentDay}`;

        // Check if the day is already completed
        if (weekDayCompletionStatus[dayKey]) {
            return false;
        }

        // Toggle active class for the clicked button
        const isActive = button.classList.contains('active');

        // Get the parent row and both buttons
        const row = button.closest('tr');
        const checkBtn = row.querySelector('.check-btn');
        const wrongBtn = row.querySelector('.wrong-btn');

        // Remove active class from both buttons
        checkBtn.classList.remove('active');
        wrongBtn.classList.remove('active');

        // If the button wasn't active before, make it active now
        // If it was active, we're toggling it off
        let newStatus = 'pending';
        if (!isActive) {
            button.classList.add('active');
            newStatus = status;
        }

        console.log(`Updating task ${taskId} status to: ${newStatus}`);

        // Send the status update to the server
    fetch('/update-task-status', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
            body: JSON.stringify({
                taskId: taskId,
                status: newStatus,
                day: currentDay,
                week: currentWeek,
                month: currentMonth,
                year: currentYear,
                dayKey: dayKey
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Task status updated successfully:', data);

                // Check if all tasks have a status and update the "Mark All Completed" button
                const allTasks = document.querySelectorAll('#taskTableBody tr[data-task-id]');
                const allTasksHaveStatus = Array.from(allTasks).every(row => {
                    const checkActive = row.querySelector('.check-btn.active');
                    const wrongActive = row.querySelector('.wrong-btn.active');
                    const needsFixActive = row.querySelector('.needs-fix-btn.active');
                    return checkActive || wrongActive || needsFixActive;
                });

                const markAllBtn = document.getElementById('markAllCompleted');
                if (markAllBtn) {
                    markAllBtn.disabled = !allTasksHaveStatus;
                    markAllBtn.style.opacity = allTasksHaveStatus ? '1' : '0.5';
                    markAllBtn.style.cursor = allTasksHaveStatus ? 'pointer' : 'not-allowed';
                }
            } else {
                console.error('Failed to update task status:', data.message);
                // Revert UI changes if the server update failed
                loadTasksForCurrentDay();
            }
        })
        .catch(error => {
            console.error('Error updating task status:', error);
            // Revert UI changes if there was an error
            loadTasksForCurrentDay();
        });
    }

    function editTask(taskId) {
        // Here you can implement the edit functionality
        // For example, show a modal with the task details
        alert('Edit functionality will be implemented here');
    }

    function loadTasksForCurrentDay() {
        const currentDay = document.getElementById('currentDay').textContent;
        const room = window.selectedRoom;
        currentWeek = document.getElementById('weekSelect').value;
        currentMonth = document.getElementById('monthSelect').value;
        currentYear = document.getElementById('yearSelect').value;

        if (!currentWeek || !currentMonth || !currentYear) {
            taskTableBody.innerHTML = `<tr><td colspan="${isStudentView ? 4 : 5}" style="text-align:center;">Please select week, month, and year.</td></tr>`;
            return;
        }

        // Create a unique key for this specific day
        const dayKey = `${currentYear}-${currentMonth}-${currentWeek}-${currentDay}`;

        // Show loading indicator
        taskTableBody.innerHTML = `<tr><td colspan="${isStudentView ? 4 : 5}" style="text-align:center;">Loading tasks...</td></tr>`;

        // Fetch the current task statuses from the server and return the promise to caller
        return fetch('/get-task-statuses', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
      body: JSON.stringify({
        day: currentDay,
        room: String(room || ''),
        week: currentWeek,
        month: currentMonth,
        year: currentYear
          })
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Task data received:', data); // Debug log
            // Store last response so external handlers can inspect
            window._lastTaskStatusResponse = data;

            if (data.success) {
                    const tasks = data.tasks;
                    const isCompleted = data.completed;

                    // If the server returned a schedule_map for this room, persist it locally so renderTasks can prefer scheduled assignees
                    try {
                      if (data.schedule_map) {
                        window.studentSchedules = window.studentSchedules || {};
                        window.studentSchedules[room] = window.studentSchedules[room] || {};
                        window.studentSchedules[room].map = data.schedule_map;
                        saveStudentSchedules && saveStudentSchedules();
                      }
                    } catch(e) { console.warn('Failed to apply server schedule_map locally', e); }

                    // Update the completion status cache
                    weekDayCompletionStatus[dayKey] = isCompleted;

                // If server returned tasks, merge them with any client-side global tasks
                // (tasksByDay) so tasks added via the UI with apply_to_all show up on
                // every day. If server returned no tasks, fall back to tasksByDay
                // for the current day/room.
                try {
                  let renderList = Array.isArray(tasks) ? tasks.slice() : [];
                  const localList = (tasksByDay && tasksByDay[currentDay] && tasksByDay[currentDay][room]) ? tasksByDay[currentDay][room] : [];

                  if ((!renderList || renderList.length === 0) && localList.length > 0) {
                    // No server tasks: use local schedule-created tasks
                    renderList = localList.slice();
                  } else if (renderList.length > 0 && localList.length > 0) {
                    // Merge without duplicating by normalized key (area + desc) so that
                    // server-created tasks (with id) and client-mirrored tasks (no id)
                    // that represent the same logical task do not appear twice.
                    const makeKey = (t) => {
                      try {
                        const area = (t && (t.area || t.Area || t.task_area)) ? String(t.area || t.Area || t.task_area).trim().toLowerCase() : '';
                        const desc = (t && (t.desc || t.description || t.task_description)) ? String(t.desc || t.description || t.task_description).trim().toLowerCase() : '';
                        return area + '|' + desc;
                      } catch (e) { return '' }
                    };

                    const seen = new Set();
                    const merged = [];
                    renderList.forEach(t => {
                      try {
                        const key = makeKey(t);
                        if (!seen.has(key)) { seen.add(key); merged.push(t); }
                      } catch (e) { /* ignore malformed task entries */ }
                    });
                    localList.forEach(t => {
                      try {
                        const key = makeKey(t);
                        if (!seen.has(key)) { seen.add(key); merged.push(t); }
                      } catch (e) { /* ignore */ }
                    });
                    renderList = merged;
                  }

                  if (renderList && renderList.length > 0) {
                    console.log('Rendering tasks (merged):', renderList); // Debug log
                    renderTasks(renderList, isCompleted, dayKey);
                  } else {
                    taskTableBody.innerHTML = `<tr><td colspan="${isStudentView ? 4 : 5}" style="text-align:center;">No tasks assigned for this room on ${currentDay}.</td></tr>`;
                  }
                } catch (e) {
                  console.warn('Failed to merge server and local tasks:', e);
                  taskTableBody.innerHTML = `<tr><td colspan="${isStudentView ? 4 : 5}" style="text-align:center;">No tasks assigned for this room on ${currentDay}.</td></tr>`;
                }
                return data;
            } else {
                throw new Error(data.message || 'Failed to load tasks');
            }
        })
        .catch(error => {
            console.error('Error loading tasks:', error);
            taskTableBody.innerHTML = `<tr><td colspan="${isStudentView ? 4 : 5}" style="text-align:center;">Error loading tasks: ${error.message}</td></tr>`;
            // Normalize to a rejected promise so callers can handle
            return Promise.reject(error);
        });
    }

    // Helper function to render tasks with their statuses
    function renderTasks(tasks, isDayCompleted, dayKey) {
        try {
      // Deduplicate tasks by normalized key (area + desc) or id (preferred) to avoid repeated rows
      const uniqueTasks = [];
      const seen = new Set();
      const makeKey = (t) => {
        try {
          const area = (t && (t.area || t.Area || t.task_area)) ? String(t.area || t.Area || t.task_area).trim().toLowerCase() : '';
          const desc = (t && (t.desc || t.description || t.task_description)) ? String(t.desc || t.description || t.task_description).trim().toLowerCase() : '';
          return area + '|' + desc;
        } catch (e) { return '' }
      };
      (tasks || []).forEach(t => {
        try {
          const key = makeKey(t);
          if (!seen.has(key)) {
            seen.add(key);
            uniqueTasks.push(t);
          }
        } catch (e) { /* ignore malformed task entries */ }
      });
      // Trim to at most 7 rows (there should be 7 students/tasks per day)
      if (uniqueTasks.length > 7) uniqueTasks.splice(7);

      taskTableBody.innerHTML = uniqueTasks.map(task => {
            const taskId = (task && typeof task === 'object' && 'id' in task) ? (task.id ?? '') : '';
            const taskName = (task && typeof task === 'object' && 'name' in task) ? (task.name ?? 'N/A') : 'N/A';
            const taskArea = (task && typeof task === 'object' && 'area' in task) ? (task.area ?? 'N/A') : 'N/A';
            const taskDesc = (task && typeof task === 'object' && 'desc' in task) ? (task.desc ?? 'N/A') : 'N/A';
            const taskStatus = (task && typeof task === 'object' && 'status' in task) ? (task.status ?? '') : '';

            const _s = (taskStatus || '').toString().toLowerCase();
            const isChecked = ['checked', 'completed', 'done', 'complete'].includes(_s);
            const isWrong = ['wrong', 'in progress', 'not yet', 'incorrect', 'failed'].includes(_s);

      let statusCell = '';
            if (isDayCompleted) {
        if (isChecked) {
                    statusCell = `<button class="status-btn check-btn active" style="opacity: 1; background-color: #08a821; color: white; border-color: #08a821;" disabled>
                                <i class="fas fa-check"></i>
                              </button>`;
                } else if (isWrong) {
                    statusCell = `<button class="status-btn wrong-btn active" style="opacity: 1; background-color: #e61515; color: white; border-color: #e61515;" disabled>
                                <i class="fas fa-times"></i>
                              </button>`;
        } else {
          // If day is completed but this task has no checked/wrong status,
          // treat it as a placeholder and exclude it from the rendered rows by
          // returning an empty string later. We represent it here with a
          // special marker.
          statusCell = `__PLACEHOLDER__`;
        }
            } else {
                if (isStudentView) {
                    // Student view: Show read-only status. Normalize legacy values like 'not yet' to 'Pending'
                    try {
                      let disp = (taskStatus || 'pending') + '';
                      const low = disp.trim().toLowerCase();
                      if (low === 'not yet' || low === 'pending') {
                        disp = 'Pending';
                      } else {
                        disp = disp.charAt(0).toUpperCase() + disp.slice(1);
                      }
                      statusCell = `<span class="student-readonly-status">${disp}</span>`;
                    } catch (e) {
                      statusCell = `<span class="student-readonly-status">Pending</span>`;
                    }
                } else {
                    // Admin view: Show interactive buttons
          statusCell = `<div class="status-buttons">
                <button class="status-btn check-btn" onclick="updateStatus(this, '${taskId}', 'checked')">
                  <i class="fas fa-check"></i>
                </button>
                <button class="status-btn wrong-btn" onclick="updateStatus(this, '${taskId}', 'wrong')">
                  <i class="fas fa-times"></i>
                </button>
                </div>`;
                }
            }

            const actionsCell = isStudentView ? '' : `
                        <td class="actions-col">
                            <div class="action-buttons">
                              <button type="button" class="btn btn-sm btn-link p-0 edit-task-btn" onclick="openEditTaskRow(this)" title="Update Status">
                                <i class="fas fa-edit text-primary"></i>
                              </button>
                            </div>
                        </td>`;

  // Determine assigned student: prefer server-provided task name (persisted) first,
  // then rotation schedule assignment if available for this date. Do NOT overwrite
  // a persisted name with the 'NONE ASSIGNED' placeholder.
  let assignedDisplay = taskName || '';
      try {
        const selDay = document.getElementById('currentDay')?.textContent?.trim() || '';
        const roomKey = window.selectedRoom || '{{ $selectedRoom }}';
        // Compute an ISO date for the currently selected day from the UI (preferred).
        // Fall back to explicit window._selectedDateISO (which may be set by schedule generation)
        let dateIso = null;
        try {
          const wk = document.getElementById('weekSelect')?.value;
          const mo = parseInt(document.getElementById('monthSelect')?.value || '0', 10);
          const yr = parseInt(document.getElementById('yearSelect')?.value || '0', 10);
          if (wk && mo && yr && selDay && typeof getDayNumberForSelection === 'function') {
            const dayNum = getDayNumberForSelection(yr, mo, wk, selDay);
            if (dayNum) {
              const mm = String(mo).padStart(2, '0');
              const dd = String(dayNum).padStart(2, '0');
              dateIso = `${yr}-${mm}-${dd}`;
            }
          }
        } catch(e) { /* ignore */ }
        // If UI-derived date isn't available, use the globally-selected generated date
        if (!dateIso) dateIso = window._selectedDateISO || null;

  // Determine saved/generated schedule metadata (prefer in-memory, then rehydrate from _rotationScheduleSaved or localStorage)
        let savedSchedule = null;
        try {
          if (window.studentSchedules && window.studentSchedules[roomKey]) savedSchedule = window.studentSchedules[roomKey];
          else if (window._rotationScheduleSaved && window._rotationScheduleSaved[roomKey]) savedSchedule = window._rotationScheduleSaved[roomKey];
          else {
            const raw = localStorage.getItem('rotationSchedule_' + roomKey);
            if (raw) {
              try { savedSchedule = JSON.parse(raw); } catch(e) { savedSchedule = null; }
            }
          }
        } catch(e) { savedSchedule = null; }

        if (savedSchedule && (savedSchedule.map || savedSchedule.map === null)) {
          const map = savedSchedule.map || {};
          const start = savedSchedule.start_date || null;
          const end = savedSchedule.end_date || null;

          // Helper: is dateIso within [start, end] (inclusive). If start is missing, treat as not-in-range here
          const isWithinRange = (dateIsoVal, startVal, endVal) => {
            if (!dateIsoVal) return false;
            if (!startVal) return false; // require explicit start for schedule applicability
            try {
              const d = dateIsoVal;
              if (d < startVal) return false;
              if (endVal && d > endVal) return false;
              return true;
            } catch (e) { return false; }
          };

          // if dateIso is null/undefined, default to today's date ISO so the overview (today) can use the schedule
          if (!dateIso) {
            const now = new Date();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const dd = String(now.getDate()).padStart(2, '0');
            const yy = now.getFullYear();
            dateIso = `${yy}-${mm}-${dd}`;
          }

          if (dateIso && isWithinRange(dateIso, start, end)) {
            // If date is inside the saved schedule date window, try to find assignment
            const baseRows = Array.from(document.querySelectorAll('#taskTableBody tr'));
            let foundIdx = -1;
            for (let ri = 0; ri < baseRows.length; ri++) {
              const a = baseRows[ri].children[1] ? baseRows[ri].children[1].textContent.trim() : '';
              if (a && a.toLowerCase() === String(taskArea).toLowerCase()) { foundIdx = ri; break; }
            }
            if (foundIdx !== -1 || (map && map[dateIso])) {
              const assignsRaw = map[dateIso] || [];
              let asg = null;

              // If assigns is an array, try to find a matching entry by area name first
              if (Array.isArray(assignsRaw)) {
                asg = assignsRaw.find(a => {
                  if (!a) return false;
                  const aArea = (a.task_area || a.area || a.taskArea || a.name || a.task_area_name || '') + '';
                  return aArea.trim().toLowerCase() === String(taskArea).trim().toLowerCase();
                }) || assignsRaw[foundIdx] || assignsRaw[0] || null;
              } else if (assignsRaw && typeof assignsRaw === 'object') {
                // If assigns is an object keyed by area names, try to resolve by key
                const keyExact = assignsRaw[taskArea] || assignsRaw[String(taskArea).toLowerCase()];
                if (keyExact) asg = keyExact;
                else {
                  // Try to find any value whose key or nested area matches
                  for (const k of Object.keys(assignsRaw)) {
                    if (String(k).trim().toLowerCase() === String(taskArea).trim().toLowerCase()) { asg = assignsRaw[k]; break; }
                    const v = assignsRaw[k];
                    if (v && typeof v === 'object') {
                      const vArea = (v.task_area || v.area || '') + '';
                      if (vArea && String(vArea).trim().toLowerCase() === String(taskArea).trim().toLowerCase()) { asg = v; break; }
                    }
                  }
                }
                // Fallback to first value
                if (!asg) {
                  const vals = Object.values(assignsRaw);
                  if (vals.length) asg = vals[0];
                }
              }

              // Normalize asg to extract student name(s)
              let names = [];
              if (Array.isArray(asg)) {
                asg.forEach(item => {
                  if (!item) return;
                  if (typeof item === 'string') names.push(item.trim());
                  else if (item.student_name || item.name) names.push((item.student_name || item.name).trim());
                });
              } else if (asg && typeof asg === 'object') {
                if (Array.isArray(asg.assignments)) asg.assignments.forEach(a => { if (a && (a.student_name || a.name)) names.push((a.student_name || a.name).trim()); });
                else if (asg.student_name) names.push(String(asg.student_name).trim());
                else if (asg.name) names.push(String(asg.name).trim());
                else Object.values(asg).forEach(v => { if (typeof v === 'string' && v.trim() !== '') names.push(v.trim()); });
              } else if (typeof asg === 'string' && asg.trim() !== '') {
                names.push(asg.trim());
              }

              names = Array.from(new Set(names.filter(n => n)));
              if (names.length > 0) assignedDisplay = names.join(', ');
            }
          } else {
            // Date is outside saved schedule window -> show NONE ASSIGNED placeholder
            assignedDisplay = 'NONE ASSIGNED';
          }
        } else {
          // No schedule stored for this exact room/date: only show placeholder if the
          // server-side taskName is empty (i.e., not persisted). If taskName exists,
          // keep it (persistence).
          if (!assignedDisplay || String(assignedDisplay).trim() === '') {
            assignedDisplay = 'NONE ASSIGNED';
          }
        }
      } catch (e) { /* ignore schedule lookup errors */ }

      // If we marked this row as a placeholder for a completed day, skip it
      if (statusCell === '__PLACEHOLDER__') return '';

      return `
        <tr data-task-id="${taskId}">
          <td>${assignedDisplay}</td>
          <td>${taskArea}</td>
          <td>${taskDesc}</td>
          <td style="text-align:center;">${statusCell}</td>
          ${actionsCell}
        </tr>
      `;
            }).join('');

      // If the mapping produced no visible rows (e.g., all were placeholders),
      // show a centered message consistent with server-side Blade behavior.
      if (!taskTableBody.innerHTML || taskTableBody.innerHTML.trim() === '') {
        taskTableBody.innerHTML = `<tr><td colspan="${isStudentView ? 4 : 5}" style="text-align:center;">No completed tasks to display for this room on ${document.getElementById('currentDay')?.textContent || ''}.</td></tr>`;
      }

      // Update the "Mark All Completed" button
      updateMarkAllCompletedButton(isDayCompleted, tasks);
        } catch (error) {
            console.error('Error rendering tasks:', error);
            taskTableBody.innerHTML = `<tr><td colspan="${isStudentView ? 4 : 5}" style="text-align:center;">Error rendering tasks: ${error.message}</td></tr>`;
        }
    }

    // Helper function to update the "Mark All Completed" button
    function updateMarkAllCompletedButton(isDayCompleted, tasks) {
        const markAllBtn = document.getElementById('markAllCompleted');
        if (!markAllBtn) return; // Skip if button doesn't exist (student view)

        if (isDayCompleted) {
            markAllBtn.disabled = true;
            markAllBtn.style.opacity = '0.5';
            markAllBtn.style.cursor = 'not-allowed';
        } else {
      const allTasksHaveStatus = tasks.every(task => {
        const status = (task.status || 'pending').toString().toLowerCase();
        // treat both legacy and canonical values as valid
        return ['checked','completed','done','complete','wrong','in progress','not yet'].includes(status);
      });

            markAllBtn.disabled = !allTasksHaveStatus;
            markAllBtn.style.opacity = allTasksHaveStatus ? '1' : '0.5';
            markAllBtn.style.cursor = allTasksHaveStatus ? 'pointer' : 'not-allowed';
        }
    }

    function markAllCompleted() {
        const currentDay = document.getElementById('currentDay').textContent;
        const room = window.selectedRoom;
        const currentWeek = document.getElementById('weekSelect').value;
        const currentMonth = document.getElementById('monthSelect').value;
        const currentYear = document.getElementById('yearSelect').value;

        // Validate that week, month, and year are selected
        if (!currentWeek || !currentMonth || !currentYear) {
            alert('Please select week, month, and year before marking the day as completed.');
            return;
        }

        // Create a unique key for this specific day
        const dayKey = `${currentYear}-${currentMonth}-${currentWeek}-${currentDay}`;

        // Check if all tasks have a status
        const taskRows = document.querySelectorAll('#taskTableBody tr[data-task-id]');
        const allTasksHaveStatus = Array.from(taskRows).every(row => {
            const checkActive = row.querySelector('.check-btn.active');
            const wrongActive = row.querySelector('.wrong-btn.active');
            return checkActive || wrongActive;
        });

        if (!allTasksHaveStatus) {
            alert('Please set a status (checked or wrong) for all tasks before marking the day as completed.');
            return;
        }

        // Show confirmation dialog
        const confirmed = confirm('Are you sure you want to mark all tasks for this day as completed?');
        if (!confirmed) {
            return;
  // Modal helpers
    function openTaskScheduler(){ window.location.href='{{ route('task.scheduler') }}'; }

  window.openAddTaskModal = function(){
      if (isStudentView) return;

      // Reset form
      document.getElementById('taskModalTitle').textContent = 'Add New Task';
  document.getElementById('modalTaskId').value = '';
  // Clear any legacy student select value if present (we no longer use a student dropdown)
  // modalStudent input removed — nothing to clear
      document.getElementById('modalArea').value = '';
      document.getElementById('modalDesc').value = '';
      document.getElementById('descCount').textContent = '0';

      // Set today's date
      const today = new Date();
      document.getElementById('modalDate').value = today.toISOString().split('T')[0];

  // Reset modal status (if present)
  try { const ms = document.getElementById('modalStatus'); if (ms) ms.value = ''; } catch(e) {}

      document.getElementById('taskModalSubmit').textContent = 'Add Task';
      const modalEl = document.getElementById('taskModal');
      if (modalEl) {
        modalEl.classList.add('show');
        modalEl.setAttribute('aria-hidden', 'false');
        const firstFocusable = modalEl.querySelector('select, input, button, textarea');
        if (firstFocusable) { try { firstFocusable.focus(); } catch(e) {} }
      }

  // Mark modal mode as add; the global click listener will handle submission
  if (modalEl) modalEl.dataset.mode = 'add';
    }

  window.openEditTaskModal = function(task){
      if (isStudentView) return;

      console.log('Opening edit modal for task:', task);

      document.getElementById('taskModalTitle').textContent = 'Edit Task';
      document.getElementById('modalTaskId').value = task.id || '';

      // Populate dropdown for students
      // Modal no longer allows selecting students manually; assignment is done by schedule.
      // Ensure any modal student element, if present, is cleared for edit as well.
      // modalStudent input removed — assigned student cannot be edited here

      // Populate other fields
      document.getElementById('modalArea').value = task.area || '';
      document.getElementById('modalDesc').value = task.desc || '';
      document.getElementById('descCount').textContent = (task.desc || '').length;
      document.getElementById('modalDate').value = task.date || new Date().toISOString().split('T')[0];

      // Status not editable in modal; keep status changes in checklist UI

      // If modalStatus exists (we added it), populate it from task.status
      try {
        const modalStatus = document.getElementById('modalStatus');
        if (modalStatus) {
          const s = (task.status || '').toString().toLowerCase();
          if (s === 'checked' || s === 'completed' || s === 'done') modalStatus.value = 'checked';
          else if (s === 'wrong' || s === 'incorrect' || s === 'failed') modalStatus.value = 'wrong';
          else modalStatus.value = '';
        }
      } catch (e) { /* ignore */ }

      document.getElementById('taskModalSubmit').textContent = 'Update Task';
      document.getElementById('taskModal').classList.add('show');

  // Mark modal mode as edit; the global click listener will handle submission
  if (document.getElementById('taskModal')) document.getElementById('taskModal').dataset.mode = 'edit';
    }

    window.closeTaskModal = function(){
        const modalEl = document.getElementById('taskModal');
        if (modalEl) {
          modalEl.classList.remove('show');
          modalEl.setAttribute('aria-hidden', 'true');
        }
    }

    // Unified function to handle both add and edit task submissions
  window.handleTaskSubmit = async function(mode) {
      const submitBtn = document.getElementById('taskModalSubmit');
      const originalText = submitBtn.textContent;

      try {
        // Validate required fields. Admin does not provide student assignments here;
        // tasks are created/updated unassigned and rotation schedule assigns students.
        const areaValue = document.getElementById('modalArea').value.trim();
        const descValue = document.getElementById('modalDesc').value.trim();

        if (!areaValue || !descValue) {
          alert('Please fill in all required fields');
          return;
        }

        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = `<i class="fas fa-spinner fa-spin"></i> ${mode === 'add' ? 'Adding' : 'Updating'}...`;

        // Read modal status if present
        const modalStatusValue = (document.getElementById('modalStatus') && document.getElementById('modalStatus').value) ? document.getElementById('modalStatus').value : '';

        const payload = {
          room_number: window.selectedRoom,
          // Do not set assigned_to on admin-created tasks; leave empty so rotation assigns later
          assigned_to: '',
          area: areaValue,
          description: descValue,
          day: document.getElementById('currentDay').textContent.trim(),
          // Include status if user set it in modal (otherwise omit so server preserves current behavior)
          // (we only attach status when editing on purpose)
          // status will be conditionally attached below for edit mode
          date: document.getElementById('modalDate').value,
          // Apply this task globally across all dates. The backend migration is handled elsewhere.
          apply_to_all: true
        };

        if (modalStatusValue) {
          // Map client-side friendly values to RoomManagementController expected values
          // 'checked' -> 'completed', 'wrong' -> 'in progress'
          const mapped = (modalStatusValue === 'checked') ? 'completed' : (modalStatusValue === 'wrong' ? 'in progress' : (modalStatusValue === 'needs_fix' ? 'needs_fix' : modalStatusValue));
          payload.status = mapped;
        }

        let response;
        if (mode === 'edit') {
          const id = document.getElementById('modalTaskId').value;
          response = await fetch(`{{ route('room.management.task.update', ['id'=>':id']) }}`.replace(':id', id), {
            method: 'PUT',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
          });
        } else {
          response = await fetch(`{{ route('room.management.task.create') }}`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
          });
        }

        const data = await response.json();

        if (!data.success) {
          throw new Error(data.message || `Failed to ${mode} task`);
        }

        // Success
        showNotification(`Task ${mode === 'add' ? 'added' : 'updated'} successfully!`, 'success');
        closeTaskModal();
        // If this was an add, mirror the task into every day client-side so
        // the checklist immediately shows it across all dates with no assignee.
        if (mode === 'add') {
          try {
            const allDays = (typeof days !== 'undefined') ? days : @json($daysOfWeek);
            const room = window.selectedRoom;
            (allDays || []).forEach(d => {
              if (!tasksByDay[d]) tasksByDay[d] = {};
              if (!tasksByDay[d][room]) tasksByDay[d][room] = [];
              tasksByDay[d][room].push({
                name: '', // 'NONE ASSIGNED' will be displayed when rendering if empty
                area: areaValue,
                desc: descValue,
                status: 'pending'
              });
            });
          } catch(e) { console.warn('Failed to mirror modal-added task client-side', e); }
        }
        loadTasksForCurrentDay();

      } catch (error) {
        console.error(`${mode} error:`, error);
        alert(`Error ${mode === 'add' ? 'adding' : 'updating'} task: ` + error.message);
      } finally {
        // Reset button state
        submitBtn.disabled = false;
        submitBtn.textContent = originalText;
      }
    }

    // Count description chars
    const descEl = document.getElementById('modalDesc');
    const descCountEl = document.getElementById('descCount');
    if (descEl) descEl.addEventListener('input', ()=>{ descCountEl.textContent = descEl.value.length; });

    // Hook submit button -> delegate to the single handleTaskSubmit function to avoid duplicate logic
    const submitBtn = document.getElementById('taskModalSubmit');
    if (submitBtn) {
      submitBtn.addEventListener('click', async () => {
        const modalEl = document.getElementById('taskModal');
        const mode = modalEl?.dataset?.mode || (document.getElementById('modalTaskId').value ? 'edit' : 'add');
        console.log('Modal submit clicked, delegating to handleTaskSubmit, mode=', mode);
        try {
          await window.handleTaskSubmit(mode);
        } catch (e) {
          console.error('handleTaskSubmit failed:', e);
          alert('Save failed: ' + (e.message || e));
        }
      });
      // Remove any leftover onclick property
      submitBtn.onclick = null;
    }

    // Wire the inline buttons to open the modal instead of old inline form
    function openInlineEdit(taskId) {
      if (!taskId) return;
      const row = document.querySelector(`tr[data-task-id="${taskId}"]`);
      if (!row) return;
      const task = {
        id: taskId,
        name: row.children[0]?.textContent?.trim(),
        area: row.children[1]?.textContent?.trim(),
        desc: row.children[2]?.textContent?.trim(),
        status: row.querySelector('.check-btn.active') ? 'completed' : (row.querySelector('.wrong-btn.active') ? 'pending' : 'pending')
      };
  window.openEditTaskModal(task);
    }





    // Note: Function definitions moved to global scope above

    // Note: showNotification function moved to global scope above

        }

        // Get all tasks for the current day with their current status
        const tasks = Array.from(taskRows).map(row => {
            const taskId = row.getAttribute('data-task-id');
            const checkBtn = row.querySelector('.check-btn');
            const wrongBtn = row.querySelector('.wrong-btn');

            let status = 'pending';
            if (checkBtn && checkBtn.classList.contains('active')) {
                status = 'checked';
            } else if (wrongBtn && wrongBtn.classList.contains('active')) {
                status = 'wrong';
            }

            console.log(`Task ${taskId} will be marked as: ${status}`);

            return {
        id: taskId,
        status: status,
        // Read the assigned student name from the first cell if available so the server can persist it
        assigned_name: (row.children[0] ? row.children[0].textContent.trim() : '')
            };
        });

        // Debug: Log the data being sent
        console.log('Sending mark day complete request:', {
            day: currentDay,
            room: room,
            week: currentWeek,
            month: currentMonth,
            year: currentYear,
            dayKey: dayKey,
            tasks: tasks
        });

    // Send the request to mark the day as completed
  try { showLoaderRT('Marking day as completed...'); } catch(e) {}
  fetch('/mark-day-complete', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
      },
            body: JSON.stringify({
                day: currentDay,
                room: room,
                week: currentWeek,
                month: currentMonth,
                year: currentYear,
                dayKey: dayKey,
                tasks: tasks
            })
        })

  .then(async response => {
            console.log('Response status:', response.status);
            // Try to parse JSON, but if server returned HTML (error page), strip tags and return as message
            if (response.ok) {
              try {
                return await response.json();
              } catch (e) {
                const txt = await response.text();
                const stripped = txt.replace(/<[^>]*>/g, '\\n').trim();
                return { success: false, message: stripped || 'Server returned invalid response' };
              }
            } else {
              try {
                const d = await response.json();
                throw new Error(d.message || JSON.stringify(d));
              } catch (e) {
                const txt = await response.text();
                const stripped = txt.replace(/<[^>]*>/g, '\\n').trim();
                throw new Error(stripped || ('HTTP error ' + response.status));
              }
            }
        })
        .then(data => {
            console.log('Response data:', data);
            if (data.success) {
                console.log('Day marked as completed successfully:', data);

                // Update the UI to reflect the completed status
                weekDayCompletionStatus[dayKey] = true;

                // Reload the tasks to show the updated status
                loadTasksForCurrentDay();
                try { hideLoaderRT(); showResultRT('success', 'Day marked as completed successfully!', 2500); } catch(e) { alert('Day marked as completed successfully!'); }
              } else {
                console.error('Server returned error:', data);
                throw new Error(data.message || 'Failed to mark day as completed');
              }
            })
            .catch(error => {
              console.error('Error marking day as completed:', error);
              try { hideLoaderRT(); showResultRT('error', 'Error: ' + (error.message || ''), 4000); } catch(e) { alert('Error: ' + error.message); }
            });
    }


    function validateFiles(input) {
  const maxFiles = 3;
  const files = input.files;
  const errorDiv = document.getElementById('fileError');
  const submitButton = document.getElementById('submitFeedback');
  const fileNameSpan = document.getElementById('fileName');

  // Display file names and thumbnails
  if (files.length > 0) {
    const fileNames = Array.from(files).map(file => file.name);
    // Show file names
    fileNameSpan.textContent = 'Selected files: ' + fileNames.join(', ');

    // Remove any previous thumbnails
    let thumbContainer = document.getElementById('selectedFileThumbs');
    if (thumbContainer) thumbContainer.remove();

    // Create a container for thumbnails
    thumbContainer = document.createElement('div');
    thumbContainer.id = 'selectedFileThumbs';
    thumbContainer.style.display = 'flex';
    thumbContainer.style.gap = '8px';
    thumbContainer.style.marginTop = '6px';

    Array.from(files).forEach(file => {
      if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
          const img = document.createElement('img');
          img.src = e.target.result;
          img.style.width = '48px';
          img.style.height = '36px';
          img.style.objectFit = 'cover';
          img.style.borderRadius = '4px';
          img.style.border = '1px solid #dbe4f3';
          thumbContainer.appendChild(img);
        };
        reader.readAsDataURL(file);
      }
    });

    // Insert thumbnails after fileNameSpan
    fileNameSpan.parentNode.appendChild(thumbContainer);
  } else {
    fileNameSpan.textContent = 'No files chosen';
    const thumbContainer = document.getElementById('selectedFileThumbs');
    if (thumbContainer) thumbContainer.remove();
  }

  // Validate maximum files
  if (files.length > maxFiles) {
    errorDiv.textContent = 'Maximum 3 photos allowed.';
    errorDiv.style.display = 'block';
    if (submitButton) submitButton.disabled = true;
    return false;
  }

  // Validate file types
  const validTypes = ['image/jpeg', 'image/png', 'image/jpg'];
  const invalidFiles = Array.from(files).filter(file => !validTypes.includes(file.type));

  if (invalidFiles.length > 0) {
    errorDiv.textContent = 'Please select only image files (JPG, PNG)';
    errorDiv.style.display = 'block';
    if (submitButton) submitButton.disabled = true;
    return false;
  }

  errorDiv.style.display = 'none';
  if (submitButton) submitButton.disabled = false;
  return true;
}






    function disableStatusSelects() {
      const statusSelects = document.querySelectorAll('.status-select');
      statusSelects.forEach(select => {
        select.disabled = true;
      });

      // Disable buttons (only if they exist - admin view)
      const markAllBtn = document.getElementById('markAllCompleted');
      const markDayBtn = document.getElementById('markDayComplete');

      if (markAllBtn) {
        markAllBtn.disabled = true;
        markAllBtn.style.opacity = '0.5';
      }
      if (markDayBtn) {
        markDayBtn.disabled = true;
        markDayBtn.style.opacity = '0.5';
      }
    }

    window.tasksByDay = @json($tasksByDay);
    window.daysOfWeek = @json($daysOfWeek);
    window.currentDayIndex = {{ array_search($currentDay, $daysOfWeek) }};
    window.dayCompletionStatus = @json($dayCompletionStatus ?? []);
    window.selectedRoom = @json($selectedRoom);
  // Expose role flags to client-side so JS-rendered rows can respect inspector/admin restrictions
  window.isInspector = @json($isInspector ?? false);
  window.isEducator = @json($isEducator ?? false);
  // Current logged-in user's full name (used to highlight their name in tables)
  window.currentUserFullName = @json(auth()->check() ? trim((auth()->user()->user_fname ?? '') . ' ' . (auth()->user()->user_lname ?? '')) : '');

    // --- FEEDBACK SECTION LOGIC ---

    // Helper: get current feedback selection values
    function getFeedbackSelection() {
      // Get the current selection values
      const week = document.getElementById('weekSelect').value;
      const month = document.getElementById('monthSelect').value;
      const year = document.getElementById('yearSelect').value;
      const dayName = document.getElementById('currentDay').textContent;
      return {
        room_number: window.selectedRoom,
        day: dayName, // Send day name instead of day number
        week: week,
        month: month,
        year: year
      };
    }

    // Compute the day number (1-31) for the selected week/month/year and day name
    function getDayNumberForSelection(year, month, week, dayName) {
      if (!year || !month || !week || !dayName) return '';
      // Find the first day of the month
      const firstOfMonth = new Date(year, month - 1, 1);
      // Determine week start using ISO-style weeks (Monday as start)
      let firstWeekday = new Date(firstOfMonth);
      while (firstWeekday.getDay() !== 1) { // 1 = Monday
        firstWeekday.setDate(firstWeekday.getDate() - 1);
      }
      // Calculate the start (Monday) of the requested week
      const weekStart = new Date(firstWeekday);
      weekStart.setDate(weekStart.getDate() + (week - 1) * 7);
      // Map day name to offset with Monday=0 .. Sunday=6
      const dayOffsets = { "Monday": 0, "Tuesday": 1, "Wednesday": 2, "Thursday": 3, "Friday": 4, "Saturday": 5, "Sunday": 6 };
      if (!(dayName in dayOffsets)) return '';
      const date = new Date(weekStart);
      date.setDate(date.getDate() + dayOffsets[dayName]);
      // Always return the day number, even if outside selected month
      return date.getDate();
    }

    // Set hidden feedback form fields to match current selection
    function syncFeedbackFormFields() {
      const sel = getFeedbackSelection();
      try {
        const fd = document.getElementById('feedbackDay'); if (fd) fd.value = sel.day || '';
        const fw = document.getElementById('feedbackWeek'); if (fw) fw.value = sel.week || '';
        const fm = document.getElementById('feedbackMonth'); if (fm) fm.value = sel.month || '';
        const fy = document.getElementById('feedbackYear'); if (fy) fy.value = sel.year || '';
      } catch (e) {
        // Defensive: if elements are not present (student/admin view differences), do nothing
        console.warn('syncFeedbackFormFields skipped: form elements missing', e);
      }
    }

  // Apply schedule based on date inputs (student side)
  // Moved to top-level so it's always defined even if loadFeedbacks throws earlier
  window.applySchedule = function() {
      try {
        console.log('applySchedule: start');
        const modeEl = document.getElementById('scheduleMode');
        const freqEl = document.getElementById('rotationFrequency');
        const startEl = document.getElementById('scheduleStart');
        const endEl = document.getElementById('scheduleEnd');
        const mode = modeEl ? modeEl.value : 'auto';
        const frequency = freqEl ? freqEl.value : 'daily';
        const start = startEl ? startEl.value : '';
        const end = endEl ? endEl.value : '';
        const room = (typeof window.selectedRoom !== 'undefined' && window.selectedRoom) ? window.selectedRoom : '{{ $selectedRoom }}';

        if (!room) { alert('Select a room first.'); return; }
        if (!start) { alert('Select a start date.'); return; }
        if (end && new Date(end) < new Date(start)) { alert('End date cannot be before start date.'); return; }

        // Build dates array according to frequency
        const s = new Date(start);
        const e = end ? new Date(end) : null;
        const dates = [];
        let cur = new Date(s.getFullYear(), s.getMonth(), s.getDate());
        dates.push(new Date(cur));
        if (e) {
          while (true) {
            if (frequency === 'daily') cur.setDate(cur.getDate() + 1);
            else if (frequency === 'weekly') cur.setDate(cur.getDate() + 7);
            else if (frequency === 'monthly') cur.setMonth(cur.getMonth() + 1);
            else cur.setDate(cur.getDate() + 1);
            if (cur > e) break;
            dates.push(new Date(cur));
          }
        }

        // Collect students: prefer the visible occupant list in the DOM (so the UI order is respected),
        // otherwise fallback to the server-provided list embedded at render time.
        let students = [];
        try {
          const pills = document.querySelectorAll('.occupant-meta .occupant-pill');
          if (pills && pills.length) {
            students = Array.from(pills).map(p => (p.textContent || p.innerText || '').trim()).filter(Boolean);
          }
        } catch (e) { console.warn('read occupant pills failed', e); }
        if (!students || students.length === 0) {
          students = @json($roomStudents[$selectedRoom] ?? []);
        }
        function fetchSync(url) {
          try {
            var req = new XMLHttpRequest();
            req.open('GET', url, false);
            req.setRequestHeader('Accept', 'application/json');
            req.send(null);
            if (req.status >= 200 && req.status < 300) {
              return JSON.parse(req.responseText);
            }
          } catch (e) {
            console.warn('fetchSync failed', e);
          }
          return null;
        }
        let baseTasks = [];
        try {
          const firstDayName = (dates && dates.length) ? new Date(dates[0].getFullYear(), dates[0].getMonth(), dates[0].getDate()).toLocaleDateString(undefined, { weekday: 'long' }) : '';
          const url = '/tasks/base-templates?room=' + encodeURIComponent(room) + (firstDayName ? '&day=' + encodeURIComponent(firstDayName) : '');
          const resp = fetchSync(url);
          if (resp && resp.success && Array.isArray(resp.tasks) && resp.tasks.length) {
            baseTasks = resp.tasks.map((t, idx) => ({
              id: t.id || ('t' + idx),
              area: (t.area || t.name || '').toString().trim() || ('Task ' + (idx+1))
            }));
          }
        } catch(e) { console.warn('Failed to load base templates', e); }
        if (!baseTasks || baseTasks.length === 0) {
          const tasksRows = Array.from(document.querySelectorAll('#taskTableBody tr'));
          baseTasks = tasksRows.map((tr, idx) => ({
            id: tr.getAttribute('data-task-id') || ('t' + idx),
            area: (tr.children[1] ? tr.children[1].textContent.trim() : '') || (`Task ${idx+1}`)
          }));
        }

        // Helper to format a Date as local yyyy-mm-dd (avoid timezone shifts)
        function formatDateISO(d) {
          const year = d.getFullYear();
          const month = (d.getMonth() + 1).toString().padStart(2, '0');
          const day = d.getDate().toString().padStart(2, '0');
          return `${year}-${month}-${day}`;
        }

        // Build schedule_map: isoDate => { areaName: student_name, ... }
        // Use unique area names for the header (prevent duplicate headers), but preserve order.
        const schedule_map = {};
        // Derive canonical unique areas from baseTasks (preserve first occurrence order)
        const baseAreas = [];
        const seenAreas = {};
        baseTasks.forEach(t => {
          const a = (t.area || '').toString().trim();
          const key = a.toLowerCase();
          if (!seenAreas[key] && a !== '') { seenAreas[key] = true; baseAreas.push(a); }
        });
        // If no areas discovered, fallback to a single generic column
        if (!baseAreas.length) baseAreas.push('Task');

        dates.forEach((d, dateIndex) => {
          const iso = formatDateISO(d);
          schedule_map[iso] = {};
          baseAreas.forEach((area, ti) => {
            const sidx = (students && students.length > 0) ? ((dateIndex + ti) % students.length) : -1;
            const student_name = sidx >= 0 ? students[sidx] : '';
            schedule_map[iso][area] = student_name;
          });
        });

        // Persist locally so student can reload/view the schedule
        try {
          window.studentSchedules = window.studentSchedules || {};
          window.studentSchedules[room] = { mode, frequency, start_date: start, end_date: end || null, map: schedule_map };
          saveStudentSchedules && saveStudentSchedules();
        } catch(e) { console.warn('Failed to persist schedule locally', e); }

        // Render schedule table: Date | Day | one column per task area
        try {
          const container = document.getElementById('adminScheduleResults');
          // Only render the compact admin schedule here when not in student view
          if (container && !isStudentView) {
              const areas = baseAreas.slice();
              // Determine which area columns actually have at least one assigned student across the generated dates
              const visibleAreas = areas.filter(a => {
                for (let di = 0; di < dates.length; di++) {
                  const isoCheck = formatDateISO(dates[di]);
                  const assignsCheck = schedule_map[isoCheck] || {};
                  const assignment = assignsCheck[a];
                  if (assignment && String(assignment).trim() !== '') return true;
                }
                return false;
              });

              const theadCols = ['<th style="width:120px">Date</th>', '<th style="width:120px">Day</th>']
                .concat(visibleAreas.map(a => `<th>${a}</th>`)).join('');

              const rows = dates.map(d => {
                const iso = formatDateISO(d);
                const dayName = new Date(d.getFullYear(), d.getMonth(), d.getDate()).toLocaleDateString(undefined, { weekday: 'long' });
                const assigns = schedule_map[iso] || {};
                const cols = visibleAreas.map(a => {
                  const name = assigns[a] || '';
                  return `<td>${name || '-'}</td>`;
                }).join('');
                return `<tr><td style="white-space:nowrap;">${iso}</td><td>${dayName}</td>${cols}</tr>`;
              }).join('');
              // Render generated schedule into a dedicated placeholder so it doesn't replace the persisted preview
              // Show duration line using start/end variables (end may be null)
              const durationText = formatDuration(start, end);
              let genHtml = `<div class="card" style="padding:12px;border-radius:8px;"><div style="font-weight:600;margin-bottom:6px;color:#0d6efd;">Generated Rotation Schedule</div>${durationText ? `<div style="color:#6c757d;margin-bottom:8px;">${durationText}</div>` : ''}<div style="overflow:auto;"><table class="table table-sm table-bordered"><thead><tr>${theadCols}</tr></thead><tbody>${rows}</tbody></table></div></div>`;
              let genContainer = document.getElementById('generatedScheduleCard');
              if (!genContainer && !isStudentView) {
                // create a styled placeholder only for admin/non-student views
                container.insertAdjacentHTML('beforeend', '<div id="generatedScheduleCard" style="margin-top:10px;"></div>');
                genContainer = document.getElementById('generatedScheduleCard');
              }
              // if running in student view and placeholder is missing, avoid creating it (we show the larger weekly schedule elsewhere)
              if (genContainer) genContainer.innerHTML = genHtml;
              genContainer.innerHTML = genHtml;
              // Persist generated schedule to localStorage so it persists across page reloads
              try {
                const saved = { mode, frequency, start_date: start, end_date: end || null, map: schedule_map };
                localStorage.setItem('rotationSchedule_' + room, JSON.stringify(saved));
                // mark a global flag so we can keep Apply disabled when active
                window._rotationScheduleSaved = window._rotationScheduleSaved || {};
                window._rotationScheduleSaved[room] = saved;
              } catch(e) { console.warn('Failed to save generated schedule locally', e); }
            }
  } catch(e) { console.warn('Failed to render admin schedule results', e); }

  // Set scheduledDatesIso for calendar highlighting and select first generated date
  window.scheduledDatesIso = dates.map(d => formatDateISO(d));
        if (window.scheduledDatesIso && window.scheduledDatesIso.length > 0) window._selectedDateISO = window.scheduledDatesIso[0];

  // Send full schedule_map to server so admin views can reflect assignments for selected dates
        const payload = { mode, frequency, start_date: start, end_date: end || null, room, schedule_map };
        const applyBtn = document.querySelector('button[onclick="applySchedule()"]');
        if (applyBtn) applyBtn.disabled = true;
        // Prepare headers safely (meta token may be absent in some contexts)
        const headers = { 'Content-Type': 'application/json' };
        try {
          const meta = document.querySelector('meta[name="csrf-token"]');
          if (meta && meta.content) headers['X-CSRF-TOKEN'] = meta.content;
        } catch(e) { /* ignore */ }

        fetch('{{ route('tasks.schedule') }}', {
          method: 'POST',
          headers,
          body: JSON.stringify(payload)
        }).then(async response => {
          let data = null;
          try { data = await response.json(); } catch (err) { /* ignore non-JSON */ }
          if (!response.ok) {
            const msg = (data && data.message) ? data.message : ('HTTP ' + response.status);
            throw new Error(msg);
          }
          return data;
        }).then(data => {
          console.log('applySchedule: server response', data);
          const successMsg = (data && typeof data === 'object' && data.message) ? data.message : 'Schedule applied';
          try { alert(successMsg); } catch(e) { console.log('alert suppressed', successMsg); }
          // Highlight calendar days
          try {
            const cells = document.querySelectorAll('#calendarDays .calendar-day');
            cells.forEach(c => {
              const iso = c.dataset.iso || c.getAttribute('data-iso') || c.getAttribute('data-date');
              if (iso && window.scheduledDatesIso && window.scheduledDatesIso.indexOf(iso) !== -1) c.classList.add('calendar-day-scheduled');
              else if (iso) c.classList.remove('calendar-day-scheduled');
            });
          } catch(e) {}

          // Auto-click selected date if visible
          try { if (window._selectedDateISO) { const el = document.querySelector(`#calendarDays .calendar-day[data-iso="${window._selectedDateISO}"]`); if (el) el.click(); } } catch(e) {}

          if (typeof loadTasksForCurrentDay === 'function') loadTasksForCurrentDay();

          // Overlay generated assignments into the main task table so "Assigned To" updates immediately
          try {
            // Determine the authoritative schedule map: prefer payload variable, then server response, then saved map
            let authoritativeMap = null;
            try {
              // payload is in outer scope for this function
              authoritativeMap = (typeof payload !== 'undefined' && payload && payload.schedule_map) ? payload.schedule_map : null;
            } catch(e) { /* ignore */ }
            if (!authoritativeMap && data && data.schedule && data.schedule.schedule_map) {
              authoritativeMap = data.schedule.schedule_map;
            }
            if (!authoritativeMap) {
              try {
                const saved = localStorage.getItem('rotationSchedule_' + (payload && payload.room ? payload.room : '{{ $selectedRoom }}'));
                if (saved) authoritativeMap = JSON.parse(saved).map || JSON.parse(saved).schedule_map || JSON.parse(saved).map;
              } catch(e) { /* ignore parse errors */ }
            }

            if (authoritativeMap && typeof authoritativeMap === 'string') {
              try { authoritativeMap = JSON.parse(authoritativeMap); } catch(e) { /* ignore */ }
            }

              // Choose the ISO date to apply from calendar selection or from the generated map keys.
              let isoToUse = (window._selectedDateISO) ? window._selectedDateISO : null;
              if (!isoToUse && authoritativeMap) {
                const keys = Object.keys(authoritativeMap || {});
                isoToUse = keys.length ? keys[0] : null;
              }

              // If server returned authoritative persisted rows, prefer those for DOM updates.
              let serverAssigns = null;
              try {
                if (data && Array.isArray(data.applied_tasks) && data.applied_tasks.length) {
                  serverAssigns = {};
                  data.applied_tasks.forEach(r => {
                    try {
                      const dateIso = (r.date_iso || r.date || '').toString();
                      const areaName = (r.area || '').toString().trim();
                      const nameVal = (r.name || r.student_name || '').toString().trim();
                      if (!dateIso || !areaName) return;
                      serverAssigns[dateIso] = serverAssigns[dateIso] || {};
                      serverAssigns[dateIso][areaName.toLowerCase()] = nameVal;
                    } catch (ee) { /* ignore per-row */ }
                  });
                }
              } catch (e) { /* ignore */ }

              // Determine assignments for the chosen date (may be null if date outside generated range)
              let assigns = null;
              if (serverAssigns && isoToUse && serverAssigns[isoToUse]) {
                assigns = serverAssigns[isoToUse];
              } else if (authoritativeMap && isoToUse && authoritativeMap[isoToUse]) {
                // Normalize authoritativeMap entries to lowercase keys for matching
                assigns = {};
                const raw = authoritativeMap[isoToUse];
                if (Array.isArray(raw)) {
                  // array-of-objects or array-of-strings: try to map by area using derivedAreas positions
                  raw.forEach((it, idx) => {
                    if (!it) return;
                    if (typeof it === 'string') {
                      assigns['task ' + (idx+1)] = it;
                    } else if (it.task_area || it.area) {
                      assigns[(it.task_area || it.area).toString().trim().toLowerCase()] = (it.student_name || it.name || '').toString().trim();
                    }
                  });
                } else if (typeof raw === 'object') {
                  for (const k of Object.keys(raw)) assigns[k.toString().trim().toLowerCase()] = raw[k];
                }
              }

              const rows = Array.from(document.querySelectorAll('#taskTableBody tr'));
              rows.forEach(tr => {
                try {
                  const areaCell = tr.children[1];
                  const nameCell = tr.children[0];
                  if (!areaCell || !nameCell) return;
                  const areaText = (areaCell.textContent || '').trim().toLowerCase();

                  // If we have an assignments object for this date, attempt to match by area key (case-insensitive).
                  let matchedName = '';
                  if (assigns) {
                    for (const k of Object.keys(assigns || {})) {
                      if (k && k.toString().trim().toLowerCase() === areaText) { matchedName = assigns[k]; break; }
                    }
                  }

                  // Per spec: only overwrite when server/schedule provides a non-empty name.
                  if (matchedName && String(matchedName).trim() !== '') {
                    nameCell.textContent = matchedName;
                  } else {
                    // Preserve any server-persisted name. If the cell is empty or already
                    // a placeholder, leave or set the placeholder; otherwise keep existing value.
                    const existing = (nameCell.textContent || '').trim();
                    if (!existing || existing === '' || existing === 'NONE ASSIGNED' || existing === '-') {
                      nameCell.textContent = existing || 'NONE ASSIGNED';
                    }
                  }
                } catch(e) { /* ignore per-row errors */ }
              });
          } catch(e) { console.warn('Failed to overlay generated assignments into task table', e); }
        }).catch(err => {
          console.error('applySchedule server error', err);
          const errMsg = (err && typeof err === 'object' && err.message) ? err.message : (err ? String(err) : 'Unknown error');
          try { alert('Failed to apply schedule on server: ' + errMsg); } catch(e) { console.error('alert failed', e); }
        }).finally(() => {
          // Re-enable the Apply button after the request finishes unless a saved generated schedule is active
          try {
            const roomKey = (typeof window.selectedRoom !== 'undefined' && window.selectedRoom) ? window.selectedRoom : '{{ $selectedRoom }}';
            const saved = (window._rotationScheduleSaved && window._rotationScheduleSaved[roomKey]) ? window._rotationScheduleSaved[roomKey] : null;
            if (applyBtn) {
              if (isScheduleActive(saved)) {
                applyBtn.disabled = true;
                applyBtn.title = 'Apply disabled because a saved rotation schedule is active. Clear saved schedule to re-enable.';
              } else {
                applyBtn.disabled = false;
              }
            }
          } catch(e) {
            if (applyBtn) applyBtn.disabled = false;
          }
        });
      } catch (e) {
        console.error('applySchedule unexpected error', e);
        alert('Schedule failed: ' + e.message);
      }
    }

    // Helper: format Date as yyyy-mm-dd (global) and check if saved schedule is active
    if (typeof formatDateISO !== 'function') {
      function formatDateISO(d) {
        const year = d.getFullYear();
        const month = (d.getMonth() + 1).toString().padStart(2, '0');
        const day = d.getDate().toString().padStart(2, '0');
        return `${year}-${month}-${day}`;
      }
    }

    // Helper: smart duration formatting for display
    // - If both dates are in the same year: "Month D to Month D, YYYY" (e.g., "October 13 to October 14, 2025")
    // - If dates span different years: "Month D, YYYY to Month D, YYYY" (e.g., "October 13, 2024 to October 25, 2025")
    function formatDuration(startIso, endIso) {
      if (!startIso && !endIso) return '';
      try {
        if (!endIso) {
          const s = new Date(startIso + 'T00:00:00');
          return 'From ' + s.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        }
        const s = new Date(startIso + 'T00:00:00');
        const e = new Date(endIso + 'T00:00:00');
        if (s.getFullYear() === e.getFullYear()) {
          const sStr = s.toLocaleDateString('en-US', { month: 'long', day: 'numeric' });
          const eStr = e.toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' });
          return 'From ' + sStr + ' to ' + eStr;
        }
        const sFull = s.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        const eFull = e.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
        return 'From ' + sFull + ' to ' + eFull;
      } catch (e) {
        return (startIso && endIso) ? ('From ' + startIso + ' To ' + endIso) : (startIso ? ('From ' + startIso) : '');
      }
    }

    function isScheduleActive(saved) {
      if (!saved) return false;
      const today = formatDateISO(new Date());
      if (!saved.end_date) return true; // open-ended = active
      try {
        return saved.end_date >= today;
      } catch (e) {
        return false;
      }
    }

    // Persist studentSchedules -> localStorage per-room; used by multiple places
    function saveStudentSchedules() {
      try {
        if (!window.studentSchedules || typeof window.studentSchedules !== 'object') return;
        window._rotationScheduleSaved = window._rotationScheduleSaved || {};
        Object.keys(window.studentSchedules).forEach(roomKey => {
          try {
            const saved = window.studentSchedules[roomKey];
            if (!saved) return;
            // store per-room so other code can read rotationSchedule_<room>
            localStorage.setItem('rotationSchedule_' + roomKey, JSON.stringify(saved));
            // mirror into _rotationScheduleSaved for quick access
            window._rotationScheduleSaved[roomKey] = saved;
          } catch (e) { /* ignore per-room errors */ }
        });
      } catch (e) { console.warn('saveStudentSchedules failed', e); }
    }

    // Ensure generated placeholder exists and restore saved generated schedule from localStorage
    try {
      // Do not create generated schedule placeholders for inspector users
      if (typeof window.isInspector !== 'undefined' && window.isInspector) throw new Error('Inspector view: skip generated schedule placeholder');
      // Choose a sensible container where the generated schedule can be shown for both admin and student views.
      const adminResults = document.getElementById('adminScheduleResults');
      const fallbackContainer = document.querySelector('.checklist-wrapper') || document.querySelector('.content-container');
      const containerRoot = adminResults || fallbackContainer;
      if (containerRoot && (adminResults || !isStudentView)) {
        if (!document.getElementById('generatedScheduleCard')) containerRoot.insertAdjacentHTML('beforeend', '<div id="generatedScheduleCard" style="margin-top:10px;"></div>');
        // Try to restore saved generated schedule
        try {
          const room = (typeof window.selectedRoom !== 'undefined' && window.selectedRoom) ? window.selectedRoom : '{{ $selectedRoom }}';
          const raw = localStorage.getItem('rotationSchedule_' + room);
          if (raw) {
            const saved = JSON.parse(raw);
            try {
              // If saved schedule has an end_date in the past, treat it as expired and do not render.
              const todayIso = (new Date()).toISOString().split('T')[0];
              const endDate = saved && saved.end_date ? saved.end_date : null;
              const isExpired = endDate && (endDate < todayIso);
              if (!isExpired) {
                try { renderGeneratedScheduleFromSaved(room, saved); } catch(e) { console.warn('Failed to render saved generated schedule', e); }
              } else {
                // Hide placeholder for expired schedule (do not remove stored copy).
                try {
                  const genEl = document.getElementById('generatedScheduleCard');
                  if (genEl) genEl.innerHTML = '';
                  // Ensure calendar highlights are cleared for expired schedules
                  window.scheduledDatesIso = [];
                  window._selectedDateISO = null;
                  // Make sure Apply button is enabled so user can create a new schedule
                  const applyBtn = document.querySelector('button[onclick="applySchedule()"]');
                  if (applyBtn) { applyBtn.disabled = false; applyBtn.title = ''; }
                } catch(e) { /* ignore UI cleanup errors */ }
              }
            } catch(e) { console.warn('Failed to render saved generated schedule', e); }
            // When restoring a saved generated schedule, ensure calendar highlights and selected date
            // reflect the exact generated dates. Prefer saved.map keys if present, otherwise saved.start_date/end_date.
            try {
              const map = saved.map || {};
              const keys = Object.keys(map).filter(k => k).sort();
              if (keys.length) {
                window.scheduledDatesIso = keys.slice();
                window._selectedDateISO = window.scheduledDatesIso[0];
              } else {
                const s = saved.start_date ? saved.start_date : null;
                const e = saved.end_date ? saved.end_date : null;
                if (s) {
                  // Build an array with the date range if end exists; otherwise use start only
                  if (e) {
                    // create inclusive list between s and e (ISO strings)
                    const arr = [];
                    const sd = new Date(s + 'T00:00:00');
                    const ed = new Date(e + 'T00:00:00');
                    for (let d = new Date(sd); d <= ed; d.setDate(d.getDate() + 1)) { arr.push(d.toISOString().split('T')[0]); }
                    window.scheduledDatesIso = arr;
                    window._selectedDateISO = arr.length ? arr[0] : s;
                  } else {
                    window.scheduledDatesIso = [s];
                    window._selectedDateISO = s;
                  }
                }
              }
            } catch (e) { /* ignore scheduling restore errors */ }
            // Disable Apply button if saved schedule is active
            try {
              const applyBtn = document.querySelector('button[onclick="applySchedule()"]');
              if (applyBtn && isScheduleActive(saved)) {
                applyBtn.disabled = true;
                applyBtn.title = 'Apply disabled because a saved rotation schedule is active. Clear saved schedule to re-enable.';
              } else if (applyBtn) {
                // Ensure tooltip/title cleared when not active
                applyBtn.title = '';
                applyBtn.disabled = false;
              }
            } catch(e) { /* ignore */ }
          }
        } catch(e) { /* ignore localStorage errors */ }
      }
    } catch(e) { console.warn('Failed to ensure generated schedule placeholder', e); }

    // Helper to render generated schedule from a saved object {mode, frequency, start_date, end_date, map}
    function renderGeneratedScheduleFromSaved(room, saved) {
      try {
        // Prefer the admin schedule results container when present; otherwise use the generatedScheduleCard placeholder we created earlier
        let container = document.getElementById('generatedScheduleCard');
        if (!container) {
          const adminResults = document.getElementById('adminScheduleResults');
          const fallbackContainer = document.querySelector('.checklist-wrapper') || document.querySelector('.content-container');
          if (adminResults) {
            adminResults.insertAdjacentHTML('beforeend', '<div id="generatedScheduleCard" style="margin-top:10px;"></div>');
            container = document.getElementById('generatedScheduleCard');
          } else if (fallbackContainer && !isStudentView && !(typeof window.isInspector !== 'undefined' && window.isInspector)) {
            // Only insert into fallback container for non-student views to avoid duplicate small card
            fallbackContainer.insertAdjacentHTML('beforeend', '<div id="generatedScheduleCard" style="margin-top:10px;"></div>');
            container = document.getElementById('generatedScheduleCard');
          }
        }
        if (!container) return;
        // Robustly derive areas from the saved map, supporting multiple shapes:
        // - map[iso] = { areaName: studentName, ... }
        // - map[iso] = [ { task_area: 'CR', student_name: 'Bob' }, ... ]
        // - map[iso] = [ 'Bob', 'Alice', ... ] (index-based)
        // Fallback to areas discovered in the task table when map doesn't provide them.
        const map = saved.map || {};
        const keys = Object.keys(map).sort();

        // Try to derive canonical area order from the saved map (prefer first non-empty date)
        let derivedAreas = [];
        for (let ki = 0; ki < keys.length; ki++) {
          const assigns = map[keys[ki]];
          if (!assigns) continue;
          if (Array.isArray(assigns) && assigns.length) {
            // If array of objects with task_area/area fields
            const candidate = assigns.map((it) => {
              if (!it) return '';
              if (typeof it === 'string') return '';
              return (it.task_area || it.area || it.name || '').toString().trim();
            }).filter(Boolean);
            if (candidate.length) { derivedAreas = candidate; break; }
            // If array of strings, treat them as names with implicit Task N headers
            if (assigns.every(a => typeof a === 'string')) {
              // create generic Task 1..N headers
              derivedAreas = assigns.map((_, idx) => 'Task ' + (idx + 1));
              break;
            }
          } else if (assigns && typeof assigns === 'object') {
            // Object keyed by area names
            const keysAreas = Object.keys(assigns).map(k => k.toString().trim()).filter(Boolean);
            if (keysAreas.length) { derivedAreas = keysAreas; break; }
          }
        }

        // Fallback: derive areas from the visible task table (server-provided order)
        if (!derivedAreas.length) {
          const tasksRows = Array.from(document.querySelectorAll('#taskTableBody tr'));
          derivedAreas = tasksRows.map((tr, idx) => (tr.children[1] ? tr.children[1].textContent.trim() : (`Task ${idx+1}`)));
        }

        // Now compute which areas actually have at least one non-empty assignment across dates
        const visibleAreas = derivedAreas.filter(area => {
          if (!area) return false;
          const aLower = area.toString().trim().toLowerCase();
          for (let ki = 0; ki < keys.length; ki++) {
            const assigns = map[keys[ki]];
            if (!assigns) continue;
            if (Array.isArray(assigns)) {
              // Try to find matching entry by area name or fallback to index
              const byName = assigns.find(it => it && ( (it.task_area && it.task_area.toString().trim().toLowerCase() === aLower) || (it.area && it.area.toString().trim().toLowerCase() === aLower) || (it.name && it.name.toString().trim().toLowerCase() === aLower) ));
              if (byName) {
                const nameVal = (byName.student_name || byName.name || '').toString().trim();
                if (nameVal) return true;
              }
              // else try index match
              const idx = derivedAreas.indexOf(area);
              const asg = assigns[idx];
              if (asg) {
                if (typeof asg === 'string' && asg.trim() !== '') return true;
                if (asg.student_name && String(asg.student_name).trim() !== '') return true;
              }
            } else if (assigns && typeof assigns === 'object') {
              // direct keyed object
              for (const k of Object.keys(assigns)) {
                if (String(k).trim().toLowerCase() === aLower) {
                  const v = assigns[k];
                  if (!v) continue;
                  if (typeof v === 'string' && v.trim() !== '') return true;
                  if (v.student_name && String(v.student_name).trim() !== '') return true;
                }
              }
            }
          }
          return false;
        });

        const theadCols = ['<th style="width:120px">Date</th>', '<th style="width:120px">Day</th>']
          .concat(visibleAreas.map(a => `<th>${a}</th>`)).join('');

        const rows = keys.map(k => {
          const d = new Date(k + 'T00:00:00');
          const dayName = d.toLocaleDateString(undefined, { weekday: 'long' });
          const assigns = map[k] || {};
          const cols = visibleAreas.map(a => {
            const aKey = a.toString().trim();
            let nameOut = '';
            if (Array.isArray(assigns)) {
              // try to find by area name first
              const byName = assigns.find(it => it && ( (it.task_area && it.task_area.toString().trim().toLowerCase() === aKey.toLowerCase()) || (it.area && it.area.toString().trim().toLowerCase() === aKey.toLowerCase()) || (it.name && it.name.toString().trim().toLowerCase() === aKey.toLowerCase()) ));
              if (byName) nameOut = (byName.student_name || byName.name || '').toString().trim();
              // fallback to index
              if (!nameOut) {
                const idx = derivedAreas.indexOf(a);
                const cand = assigns[idx];
                if (cand) {
                  if (typeof cand === 'string') nameOut = cand.trim();
                  else nameOut = (cand.student_name || cand.name || '').toString().trim();
                }
              }
            } else if (assigns && typeof assigns === 'object') {
              // try exact key match (case-insensitive)
              for (const k2 of Object.keys(assigns)) {
                if (String(k2).trim().toLowerCase() === aKey.toLowerCase()) {
                  const v = assigns[k2];
                  if (!v) { nameOut = ''; break; }
                  if (typeof v === 'string') nameOut = v.trim();
                  else nameOut = (v.student_name || v.name || '').toString().trim();
                  break;
                }
              }
            }
            return `<td>${nameOut || '-'}</td>`;
          }).join('');
          return `<tr><td style="white-space:nowrap;">${k}</td><td>${dayName}</td>${cols}</tr>`;
        }).join('');
  // Use saved.start_date/saved.end_date to display duration
  const durStart = saved && saved.start_date ? saved.start_date : '';
  const durEnd = saved && saved.end_date ? saved.end_date : '';
  const durationTextSaved = formatDuration(durStart, durEnd);
  const genHtml = `<div class="card" style="padding:12px;border-radius:8px;"><div style="font-weight:600;margin-bottom:6px;color:#0d6efd;">Generated Rotation Schedule</div>${durationTextSaved ? `<div style="color:#6c757d;margin-bottom:8px;">${durationTextSaved}</div>` : ''}<div style="overflow:auto;"><table id="generatedRotationTable" class="table table-sm table-bordered"><thead><tr>${theadCols}</tr></thead><tbody>${rows}</tbody></table></div></div>`;
        // Add a small toolbar with an option to re-apply / clear a saved schedule for this room
        // Only show admin controls (reapply/clear) when the admin placeholder exists (i.e., educator/admin view)
        const adminControlsHtml = (document.getElementById('adminScheduleResults')) ? `
            <div style="margin-top:8px;">
              <button type="button" class="btn btn-sm btn-secondary" onclick="clearSavedSchedule('${room.replace(/'/g, "\\'")}')">Clear Saved Schedule</button>
            </div>` : '';

        container.innerHTML = genHtml + adminControlsHtml;
        // Highlight current user's name occurrences inside the generated schedule
        try { highlightMyName(container); } catch (e) { /* ignore */ }
      } catch(e) { console.warn('renderGeneratedScheduleFromSaved error', e); }
    }

    

    // Clear saved generated schedule for a room (client-side) and update UI without reload
    function clearSavedSchedule(room) {
      try {
        if (!room) room = (window.selectedRoom || '{{ $selectedRoom }}');
        if (!confirm('Clear the saved generated schedule for room ' + room + '? This cannot be undone.')) return;

        // Remove localStorage entry
        try { localStorage.removeItem('rotationSchedule_' + room); } catch(e) { console.warn('Failed to remove localStorage item', e); }

        // Clear in-memory mirrors
        try {
          if (window._rotationScheduleSaved) delete window._rotationScheduleSaved[room];
          if (window.studentSchedules && window.studentSchedules[room]) delete window.studentSchedules[room];
        } catch(e) { /* ignore */ }

        // Re-enable Apply button
        try {
          const applyBtn = document.querySelector('button[onclick="applySchedule()"]');
          if (applyBtn) {
            applyBtn.disabled = false;
            applyBtn.title = '';
            applyBtn.style.opacity = '1';
            applyBtn.style.cursor = 'pointer';
          }
        } catch(e) { /* ignore */ }

        // Remove generated schedule card from DOM and re-render placeholder
        try {
          const gen = document.getElementById('generatedScheduleCard');
          if (gen) {
            gen.innerHTML = '';
          }
          alert('Saved generated schedule cleared for this room.');
        } catch(e) { console.warn('clearSavedSchedule UI update failed', e); }

      } catch(e) {
        console.error('clearSavedSchedule unexpected', e);
        alert('Failed to clear saved schedule: ' + (e && e.message ? e.message : e));
      }
    }

    // Fetch and display feedbacks for current selection
    function loadFeedbacks() {
      const sel = getFeedbackSelection();
      const feedbackForm = document.getElementById('feedbackForm');
      const feedbacksContainer = document.getElementById('submittedFeedbacks');



      if (!sel.room_number || !sel.day || !sel.week || !sel.month || !sel.year) {
        feedbacksContainer.innerHTML = '';
        if (feedbackForm && !isStudentView) {
          feedbackForm.style.display = '';
        }
        return;
      }

      const studentViewParam = isStudentView ? '&student_view=1' : '';
      fetch(`/feedbacks?room_number=${encodeURIComponent(sel.room_number)}&day=${encodeURIComponent(sel.day)}&week=${encodeURIComponent(sel.week)}&month=${encodeURIComponent(sel.month)}&year=${encodeURIComponent(sel.year)}${studentViewParam}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(res => res.text())
      .then(html => {
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = html;
        const hasFeedback = tempDiv.querySelector('.feedback-card') !== null;

        if (hasFeedback) {
          // If student view, remove admin controls from the feedback cards
          if (isStudentView) {
            const editBtns = tempDiv.querySelectorAll('.edit-feedback-btn');
            const deleteBtns = tempDiv.querySelectorAll('.delete-feedback-btn');
            editBtns.forEach(btn => btn.remove());
            deleteBtns.forEach(btn => btn.remove());

            // Remove the admin controls container
            const adminControls = tempDiv.querySelectorAll('div[style*="position:absolute"]');
            adminControls.forEach(control => control.remove());
          }

          feedbacksContainer.innerHTML = tempDiv.innerHTML;

          // Hide feedback form only in admin view when feedback exists
          // In student view, form should always be hidden
          if (feedbackForm) {
            if (isStudentView) {
              feedbackForm.style.display = 'none';
            } else {
              // Admin view: hide form when feedback exists, show when no feedback
              feedbackForm.style.display = 'none';
            }
          }
        } else {
          // Show "No feedback" message when there are no feedbacks
          feedbacksContainer.innerHTML = `
            <div style="background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; text-align: center; color: #6c757d; margin: 10px 0;">
              <i class="fi fi-rr-comment-slash" style="font-size: 1.5rem; margin-bottom: 8px; display: block;"></i>
              <strong>No feedback available</strong>
              <p style="margin: 5px 0 0 0; font-size: 0.9rem;">No feedback has been submitted for this day yet.</p>
            </div>
          `;

          // Show feedback form only in admin view when no feedback exists
          if (feedbackForm) {
            if (isStudentView) {
              feedbackForm.style.display = 'none';
            } else {
              feedbackForm.style.display = '';
            }
          }
        }
      })
      .catch(() => {
        feedbacksContainer.innerHTML = '<div style="color:red;">Failed to load feedbacks.</div>';
        if (feedbackForm && !isStudentView) {
          feedbackForm.style.display = '';
        }
      });
    }

    // On feedback form submit, reload feedbacks after successful submission
    const feedbackForm = document.getElementById('feedbackForm');
    if (feedbackForm) {
      feedbackForm.addEventListener('submit', function(e) {
      // Always sync hidden fields first!
      syncFeedbackFormFields();

      const fileInput = document.getElementById('feedback_file');
      if (!validateFiles(fileInput)) {
        e.preventDefault();
        return;
      }
      // Validate required hidden fields
      const day = document.getElementById('feedbackDay').value;
      const week = document.getElementById('feedbackWeek').value;
      const month = document.getElementById('feedbackMonth').value;
      const year = document.getElementById('feedbackYear').value;
      if (!day || !week || !month || !year) {
        e.preventDefault();
        alert('Please select week, month, and year before submitting feedback.');
        return;
      }
      e.preventDefault(); // Prevent default form submission

      const form = this;
      const formData = new FormData(form);

      // Debug: Log form data
      console.log('Submitting feedback with data:');
      for (let [key, value] of formData.entries()) {
        console.log(key, value);
      }

      // Disable submit button to prevent double submit
      const submitBtn = document.getElementById('submitFeedback');
      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = 'Submitting...';
      }

      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(res => {
        // If validation fails, try to parse error and show message
        if (!res.ok) {
          return res.json().then(err => { throw err; });
        }
        return res.json ? res.json() : res.text();
      })
      .then(data => {
        loadFeedbacks();
        form.reset();
        document.getElementById('fileName').textContent = 'No files chosen';
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Feedback';
        }
      })
      .catch(err => {
        console.error('Feedback submission error:', err);
        let msg = 'Failed to submit feedback.';
        if (err && err.message) {
          msg += '\nError: ' + err.message;
        } else if (err && err.errors) {
          msg += '\nValidation errors:\n' + Object.values(err.errors).map(arr => arr.join(', ')).join('\n');
        } else if (typeof err === 'string') {
          msg += '\nError: ' + err;
        }
        alert(msg);
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Submit Feedback';
        }
      });
      });
    }

    // On day/week/month/year change, sync feedback form fields and reload feedbacks
    function onFeedbackSelectionChange() {
      syncFeedbackFormFields();
      loadFeedbacks();
    }

    // Feedback event listeners now handled in main DOMContentLoaded


    // Also update feedback form fields when day changes via code
    function setDayByName(dayName) {
      // Find the index for the requested day and update currentDay
      const idx = days.indexOf(dayName);
      if (idx !== -1) {
        currentDayIndex = idx;
        // Update DOM day display
        if (currentDayElement) currentDayElement.textContent = days[currentDayIndex];

        // Ensure week/month/year selects are in sync; load tasks and feedback
        // If week/month/year are not set, try to keep existing selections
        const weekSelect = document.getElementById('weekSelect');
        const monthSelect = document.getElementById('monthSelect');
        const yearSelect = document.getElementById('yearSelect');

        // If month/year selects exist and do not match selectedDate, try to align them
        try {
          if (typeof selectedDate !== 'undefined' && selectedDate) {
            if (monthSelect) monthSelect.value = selectedDate.getMonth() + 1;
            if (yearSelect) yearSelect.value = selectedDate.getFullYear();
            updateWeeks();
            // pick matching week option by checking getWeekDates for each week
            if (weekSelect) {
              for (let i = 1; i < weekSelect.options.length; i++) {
                const w = parseInt(weekSelect.options[i].value);
                const { startDate, endDate } = getWeekDates(selectedDate.getFullYear(), selectedDate.getMonth() + 1, w);
                const sd = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
                const ed = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
                if (selectedDate >= sd && selectedDate <= ed) { weekSelect.value = w; break; }
              }
            }
          }
        } catch (e) { /* ignore */ }

        // Trigger data refresh
        loadTasksForCurrentDay();
        syncFeedbackFormFields();
        loadFeedbacks();      }
    }

    // Feedback initialization now handled in main DOMContentLoaded

    // Feedback edit/delete handlers
    document.addEventListener('click', function(e) {
      // Edit Feedback
      if (e.target.classList.contains('edit-feedback-btn')) {
        e.preventDefault();
        const card = e.target.closest('.feedback-card');
        const feedbackId = e.target.getAttribute('data-id');
        const commentDiv = card.querySelector('div[style*="background:whitesmoke"]');
        const oldComment = commentDiv ? commentDiv.textContent.trim() : '';
        const photoPaths = card.getAttribute('data-photo-paths') ? JSON.parse(card.getAttribute('data-photo-paths')) : [];
        // Improved edit form layout
        let formHtml = `
          <form class="edit-feedback-form" enctype="multipart/form-data" data-id="${feedbackId}" style="margin-bottom:10px; background:#f4f8fc; border-radius:10px; padding:18px 18px 10px 18px; box-shadow:0 1px 4px #e5e9f2;">
            <div style="margin-bottom:12px;">
              <textarea name="feedback" rows="4" required style="width:100%;margin-bottom:10px;border-radius:6px;border:1px solid #b6c2d2;padding:8px;resize:vertical;">${oldComment.replace(/"/g, '&quot;')}</textarea>
            </div>
            <div style="margin-bottom:12px;">
              <label style="font-weight:500;display:block;margin-bottom:4px;">Current Photos:</label>
              <div style="display:flex;gap:10px;flex-wrap:wrap;">
                ${photoPaths.map(img => `
                  <div style="position:relative;display:inline-block;">
                    <img src="/storage/${img}" style="width:80px;height:60px;object-fit:cover;border-radius:6px;border:1px solid #dbe4f3;">
                    <button type="button" class="remove-photo-btn" data-path="${img}" style="position:absolute;top:2px;right:2px;background:#e22a1d;color:#fff;border:none;border-radius:50%;width:20px;height:20px;line-height:18px;font-size:14px;cursor:pointer;">&times;</button>
                  </div>
                `).join('')}
              </div>
            </div>
            <div style="margin-bottom:18px;">
              <label style="font-weight:500;">Add More Photos (max 3):</label>
              <input type="file" name="feedback_files[]" multiple accept="image/*" style="margin-left:8px;" />
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;">
              <button type="submit" class="btn btn-success btn-sm" style="padding:6px 22px;font-size:15px;border-radius:6px;display:flex;align-items:center;min-width:100px;">
                <i class="fa fa-save" style="margin-right:6px;"></i>Save
              </button>
              <button type="button" class="btn btn-secondary btn-sm cancel-edit-feedback" style="padding:6px 22px;font-size:15px;border-radius:6px;display:flex;align-items:center;min-width:80px;">
                <i class="fa fa-times" style="margin-right:3px;"></i>Cancel
              </button>
            </div>
          </form>
        `;
        card.querySelector('div[style*="background:whitesmoke"]').parentNode.insertAdjacentHTML('beforebegin', formHtml);
        card.querySelector('div[style*="background:whitesmoke"]').style.display = 'none';
        e.target.style.display = 'none';
        card.querySelector('.delete-feedback-btn').style.display = 'none';
      }

      // Delete Feedback
      if (e.target.classList.contains('delete-feedback-btn')) {
        e.preventDefault();
        if (!confirm('Are you sure you want to delete this feedback?')) return;
        const feedbackId = e.target.getAttribute('data-id');
        fetch('/feedback/delete', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ id: feedbackId })
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            loadFeedbacks();
          } else {
            alert('Failed to delete feedback.');
          }
        });
      }

      // Remove photo in edit form
      if (e.target.classList.contains('remove-photo-btn')) {
        e.preventDefault();
        e.target.parentNode.remove();
      }

      // Cancel edit
      if (e.target.classList.contains('cancel-edit-feedback')) {
        e.preventDefault();
        loadFeedbacks();
      }
    });

    // Handle edit feedback form submit (AJAX)
    document.addEventListener('submit', function(e) {
      if (e.target.classList.contains('edit-feedback-form')) {
        e.preventDefault();
        const form = e.target;
        const feedbackId = form.getAttribute('data-id');
        const formData = new FormData(form);
        formData.append('id', feedbackId);
        // Collect removed photos
        const removedPhotos = [];
        form.querySelectorAll('.remove-photo-btn').forEach(btn => {
          removedPhotos.push(btn.getAttribute('data-path'));
        });
        removedPhotos.forEach(path => formData.append('remove_photos[]', path));
        fetch('/feedback/edit', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          },
          body: formData
        })
        .then(res => res.json())
        .then(data => {
          if (data.success) {
            loadFeedbacks();
          } else {
            alert('Failed to update feedback.');
          }
        });
      }
    });


   /* ===== Calendar: render and selection logic ===== */
    (function() {
      // state
      let calendarYear = new Date().getFullYear();
      let calendarMonth = new Date().getMonth(); // 0-based
      let selectedDate = new Date();

      function formatDateDisplay(d) {
        const opts = { year: 'numeric', month: 'long', day: 'numeric' };
        return d.toLocaleDateString('en-US', opts);
      }

      function renderCalendar(year, month) {
        calendarYear = year;
        calendarMonth = month;
        const monthYearEl = document.getElementById('calendarMonthYear');
        const daysContainer = document.getElementById('calendarDays');
        if (!monthYearEl || !daysContainer) return;

        monthYearEl.textContent = new Date(year, month, 1).toLocaleString('en-US', { month: 'long', year: 'numeric' });
        daysContainer.innerHTML = '';

        const firstOfMonth = new Date(year, month, 1);
        const startDate = new Date(firstOfMonth);
        // start from the previous Sunday to fill the grid
        startDate.setDate(firstOfMonth.getDate() - firstOfMonth.getDay());

        // render 6 weeks (42 cells) for consistent layout
        for (let i = 0; i < 42; i++) {
          const d = new Date(startDate);
          d.setDate(startDate.getDate() + i);
          const cell = document.createElement('div');
          cell.className = 'calendar-day';
          if (d.getMonth() !== month) cell.classList.add('calendar-day-other');
          if (d.toDateString() === (new Date()).toDateString()) cell.classList.add('calendar-day-today');
          if (selectedDate && d.toDateString() === selectedDate.toDateString()) cell.classList.add('calendar-day-selected');
          cell.textContent = d.getDate();
          const iso = d.toISOString().split('T')[0];
          cell.dataset.iso = iso;
          // Highlight scheduled dates if any (window.scheduledDatesIso is set by applySchedule)
          try {
            if (window && Array.isArray(window.scheduledDatesIso) && window.scheduledDatesIso.indexOf(iso) !== -1) {
              cell.classList.add('calendar-day-scheduled');
            }
          } catch(e) { /* ignore */ }
          cell.addEventListener('click', () => onDateClick(d));
          daysContainer.appendChild(cell);
        }
      }

      function onDateClick(d) {
        selectedDate = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        // Update display
        const selDisplay = document.getElementById('selectedDateDisplay');
        if (selDisplay) selDisplay.textContent = formatDateDisplay(selectedDate);

        // Sync month/year selects and recompute weeks
        const monthSelect = document.getElementById('monthSelect');
        const yearSelect = document.getElementById('yearSelect');
        const weekSelect = document.getElementById('weekSelect');
        if (monthSelect) monthSelect.value = selectedDate.getMonth() + 1;
        if (yearSelect) yearSelect.value = selectedDate.getFullYear();

        // Force weeks update so week options match the month/year
        updateWeeks();

        // pick matching week option by checking getWeekDates for each week
        if (weekSelect) {
          let matched = '';
          for (let i = 1; i < weekSelect.options.length; i++) {
            const w = parseInt(weekSelect.options[i].value);
            const { startDate, endDate } = getWeekDates(selectedDate.getFullYear(), selectedDate.getMonth() + 1, w);
            // normalize dates (strip time)
            const sd = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
            const ed = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
            if (selectedDate >= sd && selectedDate <= ed) {
              matched = w;
              break;
            }
          }
          if (matched) weekSelect.value = matched;
        }

        // Set current day name and load tasks/feedbacks
        const dayName = selectedDate.toLocaleDateString('en-US', { weekday: 'long' });
        setDayByName(dayName); // this will call loadTasksForCurrentDay via its implementation
        syncFeedbackFormFields();
        loadTasksForCurrentDay();
        loadFeedbacks();

  // Enter static/read-only mode when a specific calendar date is chosen (only for student view)
  try { if (typeof isStudentView !== 'undefined' && isStudentView) document.body.classList.add('static-view'); } catch(e) { /* ignore */ }

        // re-render to update selected highlight
        renderCalendar(calendarYear, calendarMonth);
      }

      // prev/next month
      document.addEventListener('DOMContentLoaded', function() {
        const prevBtn = document.getElementById('prevMonthBtn');
        const nextBtn = document.getElementById('nextMonthBtn');
        if (prevBtn) prevBtn.addEventListener('click', function() {
          if (calendarMonth === 0) { calendarMonth = 11; calendarYear--; } else calendarMonth--;
          renderCalendar(calendarYear, calendarMonth);
        });
        if (nextBtn) nextBtn.addEventListener('click', function() {
          if (calendarMonth === 11) { calendarMonth = 0; calendarYear++; } else calendarMonth++;
          renderCalendar(calendarYear, calendarMonth);
        });

        // initialize selected date from server display if available, else today
        const selDisplayEl = document.getElementById('selectedDateDisplay');
        if (selDisplayEl && selDisplayEl.textContent) {
          // try parse displayed text (server printed date on blade)
          const parsed = new Date(selDisplayEl.textContent);
          if (!isNaN(parsed)) selectedDate = new Date(parsed.getFullYear(), parsed.getMonth(), parsed.getDate());
        } else {
          selectedDate = new Date();
        }
        // ensure selects align with selectedDate
        const mSelect = document.getElementById('monthSelect');
        const ySelect = document.getElementById('yearSelect');
        if (mSelect) mSelect.value = selectedDate.getMonth() + 1;
        if (ySelect) ySelect.value = selectedDate.getFullYear();
        // update weeks and try set week
        updateWeeks();
        // set week if possible (reuse onDateClick logic partially)
        const weekSelect = document.getElementById('weekSelect');
        if (weekSelect) {
          for (let i = 1; i < weekSelect.options.length; i++) {
            const w = parseInt(weekSelect.options[i].value);
            const { startDate, endDate } = getWeekDates(selectedDate.getFullYear(), selectedDate.getMonth() + 1, w);
            const sd = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
            const ed = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
            if (selectedDate >= sd && selectedDate <= ed) { weekSelect.value = w; break; }
          }
        }

        // show selected date string
        const selDisplay = document.getElementById('selectedDateDisplay');
        if (selDisplay) selDisplay.textContent = formatDateDisplay(selectedDate);

        // set calendar view to selected date month
  renderCalendar(selectedDate.getFullYear(), selectedDate.getMonth());

        // Calendar toggle button
        const toggleCalendarBtn = document.getElementById('toggleCalendarBtn');
        const calendarContainer = document.querySelector('.calendar-container');
        const storageKey = 'roomtask.calendarCollapsed';
        // Initialize state from sessionStorage
        try {
          const collapsed = sessionStorage.getItem(storageKey) === '1';
          if (collapsed && calendarContainer) calendarContainer.classList.add('collapsed');
          if (toggleCalendarBtn) toggleCalendarBtn.textContent = collapsed ? '▸' : '▾';
        } catch(e) { /* ignore storage errors */ }

        if (toggleCalendarBtn) {
          toggleCalendarBtn.addEventListener('click', function() {
            if (!calendarContainer) return;
            const isCollapsed = calendarContainer.classList.toggle('collapsed');
            toggleCalendarBtn.textContent = isCollapsed ? '▸' : '▾';
            try { sessionStorage.setItem(storageKey, isCollapsed ? '1' : '0'); } catch(e){}
          });
        }
      });
    })();


  </script>
  <script>
    // Delegated safety listener: ensure clicking the modal submit will invoke handleTaskSubmit
    (function() {
      if (window._taskModalDelegationAdded) return;
      window._taskModalDelegationAdded = true;
      document.addEventListener('click', function(e) {
        const btn = e.target.closest ? e.target.closest('#taskModalSubmit') : null;
        if (!btn) return;
        e.preventDefault();
        const modalEl = document.getElementById('taskModal');
        const mode = modalEl?.dataset?.mode || (document.getElementById('modalTaskId')?.value ? 'edit' : 'add');
        console.log('Safety delegated submit click, mode=', mode);
        if (typeof window.handleTaskSubmit === 'function') {
          // fire it async and ignore errors here (they're logged inside)
          window.handleTaskSubmit(mode).catch(err => console.error('handleTaskSubmit error:', err));
        } else {
          // If not defined yet, try a short retry after 50ms
          setTimeout(() => {
            if (typeof window.handleTaskSubmit === 'function') window.handleTaskSubmit(mode).catch(err => console.error('handleTaskSubmit error:', err));
            else console.warn('handleTaskSubmit still not defined');
          }, 50);
        }
      });
    })();
  </script>
  <script>
    // Delegated safety for modal close buttons (X and Cancel).
    (function(){
      if (window._taskModalCloseDelegationAdded) return;
      window._taskModalCloseDelegationAdded = true;
      document.addEventListener('click', function(e){
        const btn = e.target.closest ? e.target.closest('.task-modal-close') : null;
        if (!btn) return;
        e.preventDefault();
        // If closeTaskModal exists, call it. Otherwise wait a short time for it to appear.
        if (typeof window.closeTaskModal === 'function') {
          try { window.closeTaskModal(); } catch(err){ console.error('closeTaskModal threw:', err); }
          return;
        }
        // Small retry in case scripts load slightly later
        setTimeout(() => {
          if (typeof window.closeTaskModal === 'function') {
            try { window.closeTaskModal(); } catch(err) { console.error('closeTaskModal threw on retry:', err); }
          } else {
            // Fallback: hide modal directly
            const modal = document.getElementById('taskModal');
            if (modal) { modal.classList.remove('show'); modal.setAttribute('aria-hidden','true'); }
          }
        }, 50);
      });
    })();
  </script>
  <script>
    // Admin-only: synchronize horizontal alignment of rotation table with checklist header
    (function(){
      try {
        // don't run in student/static view
        var isStatic = (document.body && document.body.classList && document.body.classList.contains('static-view'));
        if (typeof isStudentView !== 'undefined' && isStudentView) isStatic = true;
        if (isStatic) return;

        function syncAlignment(){
          try {
            var checklist = document.querySelector('.checklist-wrapper');
            var rot = document.querySelector('.rotation-table-container');
            var assigned = document.querySelector('.assigned-tasks-panel');
            var content = document.querySelector('.content-container');
            if (!checklist || !rot) return;

            var contentLeft = content ? content.getBoundingClientRect().left : 0;
            var left = checklist.getBoundingClientRect().left - contentLeft;
            if (left < 0) left = 0;

            // Apply margin to rotation container so its left edge lines up with checklist
            rot.style.marginLeft = left + 'px';
            rot.style.marginRight = '0';

            // Ensure rotation table fills remaining width but never becomes negative
            var rotTable = rot.querySelector('.rotation-table');
            if (rotTable) {
              // Use calc to keep responsive behavior
              rotTable.style.width = 'calc(100% - ' + left + 'px)';
              rotTable.style.minWidth = '300px';
            }

            if (assigned) assigned.style.marginLeft = left + 'px';
          } catch (e) { console.warn('syncAlignment inner failed', e); }
        }

        // Run after DOM ready; also watch for layout changes
        document.addEventListener('DOMContentLoaded', function(){
          syncAlignment();
          // run a couple times to catch late renders
          setTimeout(syncAlignment, 120);
          setTimeout(syncAlignment, 600);
        });

        // Resize handler (debounced)
        var resizeTimer = null;
        window.addEventListener('resize', function(){ clearTimeout(resizeTimer); resizeTimer = setTimeout(syncAlignment, 80); });

        // MutationObserver: if rotation table or checklist content changes, re-sync
        var obsTarget = document.querySelector('.content-container') || document.body;
        if (obsTarget && window.MutationObserver) {
          var mo = new MutationObserver(function(m){ syncAlignment(); });
          mo.observe(obsTarget, { childList: true, subtree: true, attributes: false });
        }
      } catch (e) { console.warn('Admin alignment helper failed to initialize', e); }
    })();
  </script>
  <!-- Add Task Modal HTML -->
  <div id="rt-modal-overlay" class="rt-modal-overlay" aria-hidden="true">
    <div id="rt-modal" class="rt-modal" role="dialog" aria-modal="true" aria-labelledby="rt-modal-title" tabindex="-1">
      <form id="addTaskForm" class="rt-modal-form" novalidate>
        <h2 id="rt-modal-title" style="margin:0 0 6px 0; font-size:1.15rem; font-weight:700; color:#222;">Add Task</h2>
        <div class="rt-form-row">
          <label for="rt-area">Area</label>
          <input id="rt-area" name="area" type="text" required placeholder="Enter area" />
        </div>
        <div class="rt-form-row">
          <label for="rt-desc">Description</label>
          <textarea id="rt-desc" name="description" rows="5" required placeholder="Enter description"></textarea>
        </div>
        <div class="rt-modal-footer">
          <button type="submit" class="rt-modal-btn rt-btn-primary">Save Task</button>
          <button type="button" id="rt-cancel-btn" class="rt-modal-btn rt-btn-secondary">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Add Task Modal JS -->
  <script>
  (function(){
    const overlay = document.getElementById('rt-modal-overlay');
    const modal = document.getElementById('rt-modal');
    const openBtn = document.getElementById('addTaskButton');
    const cancelBtn = document.getElementById('rt-cancel-btn');
    const form = document.getElementById('addTaskForm');
    const firstInput = document.getElementById('rt-area');

    function openModal(){ overlay.classList.add('show'); overlay.setAttribute('aria-hidden','false'); document.body.style.overflow='hidden'; setTimeout(()=>{ if(modal) modal.focus(); if(firstInput) firstInput.focus(); },10); }
    function closeModal(){ overlay.classList.remove('show'); overlay.setAttribute('aria-hidden','true'); document.body.style.overflow=''; form.reset(); }

    if(openBtn) openBtn.addEventListener('click', function(e){ e.preventDefault(); openModal(); });
    if(cancelBtn) cancelBtn.addEventListener('click', function(e){ e.preventDefault(); closeModal(); });

    overlay.addEventListener('click', function(e){ if(e.target===overlay) closeModal(); });
    modal.addEventListener('click', function(e){ e.stopPropagation(); });
    document.addEventListener('keydown', function(e){ if(e.key==='Escape' && overlay.classList.contains('show')) closeModal(); });

    form.addEventListener('submit', function(e){ e.preventDefault(); const area = document.getElementById('rt-area').value.trim(); const desc = document.getElementById('rt-desc').value.trim(); if(!area||!desc){ alert('Please complete both Area and Description.'); return; }
      // Populate legacy fields if present and call existing saveTask
      const legacyArea = document.getElementById('taskArea'); const legacyDesc = document.getElementById('taskDesc'); if(legacyArea) legacyArea.value = area; if(legacyDesc) legacyDesc.value = desc;
      if(typeof window.saveTask === 'function'){ try{ window.saveTask(new Event('submit',{cancelable:true})); }catch(err){ console.warn('saveTask call failed',err); closeModal(); } }else{ console.log('Add Task:',{area,desc}); closeModal(); }
    });
  })();
  </script>
  <style>
    /* Ensure legacy inline form is hidden so only modal is used */
    #taskFormContainer { display: none !important; }
  </style>
  <script>
    // Keep this room task page synchronized with dashboard assignments.
    (function(){
      const selectedRoom = (typeof window.selectedRoom !== 'undefined' && window.selectedRoom) ? String(window.selectedRoom) : '{{ $selectedRoom ?? '' }}';

      function renderOccupantsList(students) {
        try {
          const nicknamesDiv = document.querySelector('.occupant-meta .nicknames');
          if (!nicknamesDiv) return;
          nicknamesDiv.innerHTML = '';
          if (!students || !Array.isArray(students) || students.length === 0) {
            const msg = document.createElement('div');
            msg.className = 'text-muted';
            msg.textContent = 'No occupants found for this room.';
            nicknamesDiv.appendChild(msg);
            return;
          }
          students.forEach(name => {
            const span = document.createElement('span');
            span.className = 'occupant-pill';
            span.style.cssText = 'background:#f1f5f9;padding:6px 10px;border-radius:6px;font-size:0.95rem;color:#1f2937;margin-right:6px;';
            span.textContent = name;
            try {
              const me = (window.currentUserFullName || '').toString().trim();
              if (me && name && name.toString().trim().toLowerCase() === me.toLowerCase()) {
                // Add my-name-highlight class so current user is visually emphasized in the occupants card
                span.classList.add('my-name-highlight');
                span.style.background = '';
                span.style.color = '';
              }
            } catch (e) { /* ignore highlight errors */ }
            nicknamesDiv.appendChild(span);
          });
        } catch (e) { console.warn('renderOccupantsList error', e); }
      }

      async function fetchRoomDetailsAndUpdate(room) {
        if (!room) return;
        try {
          // Prefer a same-origin request and accept JSON
          const res = await fetch(`/api/room/details/${encodeURIComponent(room)}`, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
          if (!res.ok) return;
          const data = await res.json();
          if (data && data.success) {
            const students = (data.room && Array.isArray(data.room.students)) ? data.room.students.map(s => (typeof s === 'string' ? s : (s.name || s.student_name || ''))) : [];
            renderOccupantsList(students);
            try { localStorage.setItem('roomtask_last_update_' + room, JSON.stringify({ ts: Date.now(), students })); } catch(e){}
          }
        } catch (e) { console.warn('fetchRoomDetailsAndUpdate failed', e); }
      }

      function handleIncomingRoomUpdate(payload) {
        try {
          if (!payload) return;
          // Dashboard may broadcast a wrapper with roomData or updatedRooms
          if (payload.roomData && payload.roomData[selectedRoom]) {
            renderOccupantsList(payload.roomData[selectedRoom] || []);
            return;
          }
          if (Array.isArray(payload.updatedRooms) && payload.updatedRooms.indexOf(selectedRoom) !== -1) {
            fetchRoomDetailsAndUpdate(selectedRoom);
            return;
          }
          // If payload is a simple mapping
          if (payload[selectedRoom]) {
            renderOccupantsList(payload[selectedRoom]);
            return;
          }
        } catch (e) { console.warn('handleIncomingRoomUpdate error', e); }
      }

      // BroadcastChannel listener (dashboard posts to 'room_updates')
      try {
        if (window.BroadcastChannel) {
          const ch = new BroadcastChannel('room_updates');
          ch.addEventListener('message', ev => { try { handleIncomingRoomUpdate(ev.data); } catch(e){} });
        }
      } catch (e) { console.warn('BroadcastChannel init failed', e); }

      // localStorage cross-tab listener
      window.addEventListener('storage', function(e) {
        try {
          if (!e) return;
          if (e.key === 'roomAssignmentsUpdate' || e.key === 'roomAssignmentsUpdated') {
            let parsed = null;
            try { parsed = e.newValue ? JSON.parse(e.newValue) : null; } catch(err) { parsed = e.newValue; }
            handleIncomingRoomUpdate(parsed);
          }
        } catch (err) { console.warn('storage event handler failed', err); }
      });

      // postMessage (same-origin) listener
      window.addEventListener('message', function(ev) {
        try {
          if (!ev || ev.origin !== window.location.origin) return;
          const d = ev.data || {};
          if (d && (d.type === 'room_assignments_updated' || d.type === 'ROOM_ASSIGNMENTS_UPDATED')) {
            handleIncomingRoomUpdate(d);
          }
        } catch (e) { /* ignore */ }
      });

      // Initial hydration: prefer authoritative dashboard mapping, then room details, then cached localStorage
      (function(){
        async function initOccupants() {
          if (!selectedRoom) return;
          try {
            // Try dashboard mapping first (returns room_assignments map)
            try {
              const mapRes = await fetch('/api/dashboard/room-data', { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' });
              if (mapRes && mapRes.ok) {
                const mapData = await mapRes.json();
                const names = mapData && mapData.room_assignments && mapData.room_assignments[selectedRoom] ? mapData.room_assignments[selectedRoom] : null;
                if (Array.isArray(names)) {
                  renderOccupantsList(names);
                  try { localStorage.setItem('roomtask_last_update_' + selectedRoom, JSON.stringify({ ts: Date.now(), students: names })); } catch(e){}
                  return; // done
                }
              }
            } catch (e) {
              // ignore and fallback
              console.warn('dashboard mapping fetch failed', e);
            }

            // Fallback: fetch detailed room endpoint
            await fetchRoomDetailsAndUpdate(selectedRoom);
          } catch (e) {
            // Last resort: use cached localStorage if available
            try {
              const raw = localStorage.getItem('roomtask_last_update_' + selectedRoom);
              if (raw) {
                const parsed = JSON.parse(raw);
                if (parsed && Array.isArray(parsed.students)) {
                  renderOccupantsList(parsed.students);
                  return;
                }
              }
            } catch (e2) {
              console.warn('localStorage fallback failed', e2);
            }
            // If everything fails, try fetching room details once more
            try { await fetchRoomDetailsAndUpdate(selectedRoom); } catch(_){}
          }
        }
        initOccupants();
      })();
    })();
  </script>
<script>
  // Deduplicate names inside the Generated Rotation Schedule table cells.
  // Some client-side rendering paths (student view) were producing duplicate
  // name elements inside the same table cell. This script removes duplicate
  // child elements that contain the same trimmed text content and watches
  // for dynamic insertions so it runs when the generated schedule is added.
  (function(){
    function dedupeGeneratedSchedule(){
      try {
        const tbl = document.getElementById('generatedRotationTable');
        if (!tbl) return;
        const tds = tbl.querySelectorAll('tbody td');
        tds.forEach(td => {
          // Gather seen texts in this cell
          const seen = new Set();
          // Iterate over a static list of child elements to avoid live-collection issues
          const children = Array.from(td.querySelectorAll('*'));
          children.forEach(el => {
            const txt = (el.textContent || '').trim();
            if (!txt) return;
            if (seen.has(txt)) {
              // Remove duplicate element
              el.remove();
            } else {
              seen.add(txt);
            }
          });
          // Also handle the case where the cell has plain text nodes (no child elements)
          if (td.childElementCount === 0) {
            // Normalize whitespace inside the td
            td.textContent = td.textContent.replace(/\s+/g, ' ').trim();
          }
        });
      } catch (e) {
        // Non-fatal: log in console for debugging
        console.warn('dedupeGeneratedSchedule failed', e);
      }
    }

    // Run on initial load
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', dedupeGeneratedSchedule);
    } else {
      dedupeGeneratedSchedule();
    }

    // Observe DOM for any insertions of the generated schedule table and re-run dedupe
    const observer = new MutationObserver(mutations => {
      for (const m of mutations) {
        for (const node of Array.from(m.addedNodes)) {
          if (node && node.nodeType === 1) {
            if (node.id === 'generatedRotationTable' || node.querySelector && node.querySelector('#generatedRotationTable')) {
              dedupeGeneratedSchedule();
              return;
            }
          }
        }
      }
    });
    observer.observe(document.body, { childList: true, subtree: true });
  })();
</script>
</body>
</html>
</html>
