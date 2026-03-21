<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Task History - Student Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <style>
        body{
            font-family: 'Poppins', sans-serif;
            margin: 0;
            background: #f6f8fa;
            padding-top: 60px; /* Space for fixed header */
        }
        header {
            font-family: 'Poppins', sans-serif;
            background-color: #22BBEA;
            color: white;
            padding: 15px 20px;
            display: flex;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1100;
            height: 60px;
            box-sizing: border-box;
        }
        .logo {
            font-family: 'Poppins', sans-serif;
            margin-left: 0;
        }
        .logo img {
            width: 240px;
            height: auto;
            margin-left: 0;
        }
        .header-right {
            font-family: 'Poppins', sans-serif;
            flex: 1;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            font-size: 18px;
            font-weight: 500;
        }
        .container-fluid {
            font-family: 'Poppins', sans-serif;
            display: flex;
            min-height: calc(100vh - 60px);
            min-width: 1200px;
        }
        /* Student Sidebar Styles */
        .sidebar {
            width: 300px;
            background: #ffffff;
            color: #374151;
            padding: 0;
            position: fixed;
            top: 60px;
            left: 0;
            height: calc(100vh - 60px);
            overflow-y: auto;
            z-index: 90;
            border-right: 3px solid #22BBEA;
        }

        .sidebar .nav-link {
            color: #374151;
            font-weight: 400;
            font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            font-size: 15px;
        }

        .sidebar .nav-link img { 
            vertical-align: middle; 
            width:26px; 
            height:26px; 
            margin-right:14px; 
        }

        .sidebar .nav-link:hover {
            background: #f1f5f9;
            color: #111827;
        }

        .sidebar .nav-link.active,
        .sidebar .nav-link.active:hover {
            background: #e0f2fe;
            color: #0f172a;
            font-weight: 400;
        }

        .content {
            margin-left: 300px;
            flex: 1;
            padding: 30px;
            background: #f6f8fa;
        }
        /* --- Restore table/content design --- */
        .main-content-wrapper {
            font-family: 'Poppins', sans-serif;
            padding: 40px 30px 30px 30px;
            max-width: 1400px;
            margin: 0 auto;
        }
        .main-content-wrapper h1 {
            font-family: 'Poppins', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 28px;
        }
        .main-content-wrapper form {
            font-family: 'Poppins', sans-serif;
            margin-bottom: 32px;
        }
        .main-content-wrapper .flex {
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .main-content-wrapper .flex-1 {
            font-family: 'Poppins', sans-serif;
            flex: 1;
        }
        .main-content-wrapper input[type="text"] {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 100%;
        }
        .main-content-wrapper select {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            padding: 10px 14px;
            border-radius: 6px;
            border: none;
            min-width: 120px;
            background-color: #22BBEA;
            color: white;
        }
        .main-content-wrapper button,
        .main-content-wrapper a#clearButton {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            margin-left: 2px;
        }
        .main-content-wrapper button#searchButton {
            font-family: 'Poppins', sans-serif;
            background-color: #119416;
            color: white;
        }
        .main-content-wrapper a#clearButton {
            font-family: 'Poppins', sans-serif;
            background-color: #e3342f;
            color: white;
            text-decoration: none;
        }
        .main-content-wrapper .bg-blue-50 {
            font-family: 'Poppins', sans-serif;
            background: #eaf6fb;
        }
        .main-content-wrapper .border-blue-200 {
            font-family: 'Poppins', sans-serif;
            border-color: #b6e0f7;
        }
        .main-content-wrapper .rounded {
            font-family: 'Poppins', sans-serif;
            border-radius: 10px;
        }
        .main-content-wrapper .mb-6 {
            font-family: 'Poppins', sans-serif;
            margin-bottom: 1.5rem;
        }
        .main-content-wrapper .p-4 {
            font-family: 'Poppins', sans-serif;
            padding: 1.25rem;
        }
        .main-content-wrapper .text-blue-800 {
            font-family: 'Poppins', sans-serif;
            color: #155e75;
        }
        .main-content-wrapper .text-blue-700 {
            font-family: 'Poppins', sans-serif;
            color: #1d4e89;
        }
        .main-content-wrapper .overflow-x-auto {
            font-family: 'Poppins', sans-serif;
            overflow-x: auto;
        }
        .main-content-wrapper table {
            font-family: 'Poppins', sans-serif;
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(34,187,234,0.07);
        }
        .main-content-wrapper th, .main-content-wrapper td {
            font-family: 'Poppins', sans-serif;
            border: 1px solid #e2e8f0;
            padding: 12px 10px;
        }
        .main-content-wrapper th {
            font-family: 'Poppins', sans-serif;
            background:rgb(74, 114, 247);
            color: white;
            font-weight: 600;
            text-align: center;
        }
        .main-content-wrapper tr.bg-white:hover {
            font-family: 'Poppins', sans-serif;
            background: #f1f5f9;
        }
        .main-content-wrapper .w-6 {
            font-family: 'Poppins', sans-serif;
            width: 24px;
        }
        .main-content-wrapper .h-6 {
            font-family: 'Poppins', sans-serif;
            height: 24px;
        }
        .main-content-wrapper .bg-green-500 {
            font-family: 'Poppins', sans-serif;
            background: #08a821;
        }
        .main-content-wrapper .bg-red-500 {
            font-family: 'Poppins', sans-serif;
            background: #e61515;
        }
        .main-content-wrapper .rounded-full {
            font-family: 'Poppins', sans-serif;
            border-radius: 9999px;
        }
        .main-content-wrapper .flex.items-center.justify-center.mx-auto {
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: auto;
            margin-right: auto;
        }
        .main-content-wrapper .text-white {
            font-family: 'Poppins', sans-serif;
            color: #fff;
        }
        .main-content-wrapper .text-gray-400 {
            font-family: 'Poppins', sans-serif;
            color: #9ca3af;
        }
        .main-content-wrapper .text-gray-800 {
            font-family: 'Poppins', sans-serif;
            color: #222;
        }
        .main-content-wrapper .text-gray-700 {
            font-family: 'Poppins', sans-serif;
            color: #444;
        }
        .main-content-wrapper .text-gray-50 {
            font-family: 'Poppins', sans-serif;
            color: #f9fafb;
        }
        .main-content-wrapper .bg-red-50 {
            background: #fef2f2;
        }
        .main-content-wrapper .border-red-200 {
            border-color: #fecaca;
        }
        .main-content-wrapper .text-red-800 {
            color: #991b1b;
        }
        .main-content-wrapper .text-red-700 {
            color: #b91c1c;
        }
        .main-content-wrapper .bg-yellow-50 {
            background: #fffbeb;
        }
        .main-content-wrapper .border-yellow-200 {
            border-color: #fde68a;
        }
        .main-content-wrapper .text-yellow-800 {
            color: #92400e;
        }
        .main-content-wrapper .text-yellow-700 {
            color: #a16207;
        }
        .main-content-wrapper .text-center {
            font-family: 'Poppins', sans-serif;
            text-align: center;
        }
        .main-content-wrapper .py-8 {
            font-family: 'Poppins', sans-serif;
            padding-top: 2rem;
            padding-bottom: 2rem;
        }

        /* Task History Form Styling - Match Admin Design */
        .main-content-wrapper input[type="text"] {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            padding: 10px 14px;
            border-radius: 6px;
            border: 1px solid #ccc;
            width: 100%;
        }
        .main-content-wrapper select {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            padding: 10px 14px;
            border-radius: 6px;
            border: none;
            min-width: 120px;
            background-color: #22BBEA;
            color: white;
        }
        .main-content-wrapper button,
        .main-content-wrapper a#clearButton {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            padding: 10px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            margin-left: 2px;
        }
        .main-content-wrapper button#searchButton {
            font-family: 'Poppins', sans-serif;
            background-color: #119416;
            color: white;
        }
        .main-content-wrapper a#clearButton {
            font-family: 'Poppins', sans-serif;
            background-color: #e3342f;
            color: white;
            text-decoration: none;
        }
        .main-content-wrapper .bg-blue-50 {
            font-family: 'Poppins', sans-serif;
            background: #eaf6fb;
        }
        .main-content-wrapper .border-blue-200 {
            font-family: 'Poppins', sans-serif;
            border-color: #b6e0f7;
        }
        .main-content-wrapper .rounded {
            font-family: 'Poppins', sans-serif;
            border-radius: 10px;
        }
    </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
    </div>
    <div class="header-right">
      <!-- Example: You can add user info or logout here if needed -->
    </div>
  </header>
  <div class="container-fluid">
    <nav class="sidebar">
      <div style="padding: 45px 10px;">
        <ul class="nav flex-column" style="list-style: none; padding: 0; margin: 0;">
          <li class="nav-item" style="margin: 8px 0;">
            <a href="{{ route('mainstudentdash') }}" class="nav-link {{ Request::routeIs('mainstudentdash') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
              <img src="{{ asset('images/dashboard.png') }}" alt="Dashboard" style="width:22px;height:22px;margin-right:12px;">Dashboard
            </a>
          </li>
          <li class="nav-item" style="margin: 8px 0;">
            <a href="{{ route('roomtask') }}" class="nav-link {{ Request::routeIs('roomtask') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
              <img src="{{ asset('images/assign.png') }}" alt="Room Tasks" style="width:22px;height:22px;margin-right:12px;">Room Tasks
            </a>
          </li>
          <li class="nav-item" style="margin: 8px 0;">
            <a href="{{ route('generalTask') }}" class="nav-link {{ Request::routeIs('generalTask') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease;">
              <img src="{{ asset('images/assign.png') }}" alt="General Tasks" style="width:22px;height:22px;margin-right:12px;">General Tasks
            </a>
          </li>
          <li class="nav-item" style="margin: 8px 0;">
            <a href="{{ route('admin.task.history') }}" class="nav-link {{ Request::routeIs('admin.task.history') || Request::routeIs('StudentsDashboard.task.history') ? 'active' : '' }}" style="display: flex; align-items: center; padding: 12px 20px; text-decoration: none; color: #374151; border-radius: 8px; margin: 0 15px; transition: all 0.3s ease; {{ Request::routeIs('admin.task.history') || Request::routeIs('StudentsDashboard.task.history') ? 'background: #e0f2fe;' : '' }}">
              <img src="{{ asset('images/history.png') }}" class="sidebar-icon">Room Checklist History
            </a>
          </li>
        </ul>
      </div>
    </nav>
    <div class="content">
      <div class="main-content-wrapper" style="padding: 32px 10px 24px 10px; max-width: 1400px;">
        <!-- Move info box here, above the flex row -->
         <div style="flex: 1; margin-left: 0;">
            <!-- Search/filter form -->
            <form method="GET" action="{{ route('task.history') }}" class="mb-6">
              <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 12px; width: 100%;">
                <div class="relative flex-1" style="flex: 1; display: flex; align-items: center; position: relative;">
                  @if(isset($rooms) && is_iterable($rooms) && count($rooms) > 0)
                    <select id="roomSelect" name="room" class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" style="padding-left: 2.2rem; min-width: 180px; height: 36px;">
                      <option value="">Select Room</option>
                      @foreach($rooms as $r)
                        <option value="{{ $r->room_number }}" {{ (string)($room ?? '') === (string)$r->room_number ? 'selected' : '' }}>{{ $r->room_number }}</option>
                      @endforeach
                    </select>
                  @else
                    <input id="roomSearchInput" name="room" type="text" value="{{ $room ?? '' }}" class="w-full py-2 pl-10 pr-4 border border-gray-300 rounded focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent" placeholder="Enter Room Number" style="padding-left: 2.2rem; min-width: 180px; height: 26px;"/>
                  @endif
                  <span style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none;">
                    <!-- Flaticon magnifying glass SVG icon -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22BBEA" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                  </span>
                </div>
                <select id="monthSelect" name="month" style="height: 44px;">
                  @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ (isset($month) && $month == $i) ? 'selected' : '' }}>
                      {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                    </option>
                  @endfor
                </select>
                <select id="yearSelect" name="year" style="height: 44px;">
                  @for($i = 2023; $i <= 2040; $i++)
                    <option value="{{ $i }}" {{ (isset($year) && $year == $i) ? 'selected' : '' }}>{{ $i }}</option>
                  @endfor
                </select>
                <select id="weekSelect" name="week" style="height: 44px;">
                  @for($i = 1; $i <= 5; $i++)
                    <option value="{{ $i }}" {{ (isset($week) && $week == $i) ? 'selected' : '' }}>Week {{ $i }}</option>
                  @endfor
                </select>
                <button type="submit" id="searchButton" style="height: 44px; display: flex; align-items: center;">Search</button>
                <a href="{{ route('task.history') }}" id="clearButton" style="height: 25px; display: flex; align-items: center;">Clear</a>
              </div>
            </form>
        <!-- Info message -->
        @php
          $noHistory = empty($studentNames);
        @endphp
        @if(!$noHistory)
        <div class="p-4 mb-6 border border-blue-200 rounded bg-blue-50" id="historyInfoBox" style="margin-left:0; margin-right:0;">
          <h2 class="mb-2 text-xl font-semibold text-blue-800">
            Task History for Room {{ $room ?? '?' }} –
            Week {{ $week ?? '?' }} of
            {{ isset($month) ? (is_numeric($month) ? date('F', mktime(0,0,0,$month,1)) : ucfirst($month)) : '?' }}
            {{ $year ?? '?' }}
          </h2>
          <p class="text-blue-700">
            Below is the checklist history for Room {{ $room ?? '?' }} covering all assigned tasks during
            <b>Week {{ $week ?? '?' }} of
            {{ isset($month) ? (is_numeric($month) ? date('F', mktime(0,0,0,$month,1)) : ucfirst($month)) : '?' }}
            {{ $year ?? '?' }}</b>.
            This section reflects all submitted records for the selected week.
          </p>
        </div>
        @endif
        <div style="display: flex; flex-direction: row; gap: 0; align-items: flex-start;">
          @if(!empty($studentNames))
          <div style="min-width: 200px; max-width: 240px; height: 63vh;; background: #f8f9fa; border: 2px solid #22BBEA; border-radius: 12px; padding: 18px 20px 18px 20px; box-shadow: 0 2px 8px rgba(34,187,234,0.07); margin-right: 8px; align-self: flex-start; display: flex; flex-direction: column; justify-content: flex-start; margin-top: 3px;">
            <div style="font-size: 1.15em; font-weight: 700; color:rgb(0, 0, 0); margin-bottom: 20px;">Room Occupants for Room {{ $room ?? '?' }}</div>
            <div style="display: flex; flex-direction: column; gap: 8px;">
              @foreach($studentNames as $name)
                <div style="font-size: 1.05em; color: #222; background: #eaf6fb; border-radius: 6px; padding: 6px 14px;">
                  {{ $name }}
                </div>
              @endforeach
            </div>
          </div>
          @endif

            <!-- Task History Table -->
            <div id="taskHistoryTable" class="overflow-x-auto" style="max-width: 1100px;">
              <table style="width:100%; min-width: 900px; font-size: 1.05em;">
                <thead>
                  <tr>
                    <th style="width: 120px; font-size:1em; text-align:center;"></th>
                    @php
                      // Dynamically get all unique assigned areas for the week from the matrix, in the order you want
                      $desiredOrder = [
                        'Trash Bin', 'Bathroom', 'Comfort Room', 'Table', 'Floor', 'Mirror and Sink', 'Beds', 'Storage and Organization'
                      ];
                      $allAreas = [];
                      if (isset($dayMap) && isset($studentNames) && isset($matrix)) {
                        foreach ($desiredOrder as $area) {
                          foreach ($dayMap as $day) {
                            foreach ($studentNames as $student) {
                              $cells = $matrix[$student][$day] ?? [];
                              foreach ($cells as $cell) {
                                $cellArea = trim($cell['area'] ?? '');
                                if (strcasecmp($cellArea, $area) === 0 && !in_array($area, $allAreas, true)) {
                                  $allAreas[] = $area;
                                }
                              }
                            }
                          }
                        }
                        // Add any extra areas not in desiredOrder
                        foreach ($dayMap as $day) {
                          foreach ($studentNames as $student) {
                            $cells = $matrix[$student][$day] ?? [];
                            foreach ($cells as $cell) {
                              $cellArea = trim($cell['area'] ?? '');
                              if ($cellArea && !in_array($cellArea, $allAreas, true)) {
                                $allAreas[] = $cellArea;
                              }
                            }
                          }
                        }
                      }
                    @endphp
                    @foreach($allAreas as $area)
                      <th style="padding:7px 4px; font-size:1em;">{{ $area }}</th>
                    @endforeach
                  </tr>
                </thead>
                <tbody>
                  @if(empty($studentNames))
                    <tr>
                      <td colspan="{{ 1 + count($allAreas) }}" class="py-8 text-center text-red-400" style="width: 100%;  font-size:1.1em; margin-left:0; margin-right:0;">
                        <div style="color:#e3342f; font-weight:600; margin-bottom:8px; width:100%;">
                          The checklist for this week has not yet been completed.
                        </div>
                        <div style="color:#444; width:100%;">
                          If you want to view the incomplete history you can view it in the <b>Room Checklist</b>.
                        </div>
                      </td>
                    </tr>
                  @else
                    @php
                      $areaDescriptions = [
                        'Floor' => 'Floor is swept and mopped, no dust or spills.',
                        'Comfort Room' => 'CR is clean, no foul smell or trash.',
                        'Bathroom' => 'Bathroom is clean, no stains or trash.',
                        'Mirror and Sink' => 'Mirror is clean and clear. Sink brushed properly.',
                        'Trash Bin' => 'No trash overflowing. Waste segregation observed.',
                        'Table' => 'Shared table is organized, no plates or cups.',
                        'Beds' => 'Rooms do not emit any smell. Beds are neatly made.',
                        'Storage and Organization' => 'Clothes are folded and stored properly.'
                      ];
                    @endphp
                    {{-- Description row (only once, under thead) --}}
                    <tr>
                      <td style="background:#f3f8fd; color:#1d4e89; font-size:0.97em; font-weight:600; text-align:center; padding:6px 4px;">Description</td>
                      @foreach($allAreas as $areaName)
                        <td style="background:#f3f8fd; color:#444; font-size:0.97em; text-align:left; padding:6px 4px;">
                          {{ $areaDescriptions[$areaName] ?? '' }}
                        </td>
                      @endforeach
                    </tr>
                    {{-- Inserted row after description --}}
                    <tr>
                      <td style="background:rgb(34, 81, 234); color:white; font-size:0.97em; font-weight:600; text-align:center; padding:7px 4px;">Day</td>
                      @foreach($allAreas as $areaName)
                        <td style="background:#f9fafb;"></td>
                      @endforeach
                    </tr>
                    @foreach($dayMap as $day)
                      <tr>
                        <td style="text-align: center; vertical-align: middle; padding:6px 4px;">{{ $day }}</td>
                        @foreach($allAreas as $areaName)
                          @php
                            $status = null;
                            // For 'Beds' and 'Storage and Organization', get status from any student
                            if (in_array($areaName, ['Beds', 'Storage and Organization'])) {
                              foreach ($studentNames as $student) {
                                $cells = $matrix[$student][$day] ?? [];
                                foreach ($cells as $cell) {
                                  if ($cell['area'] === $areaName) {
                                    $status = $cell['status'] ?? null;
                                    break 2;
                                  }
                                }
                              }
                            } else {
                              foreach ($studentNames as $student) {
                                $cells = $matrix[$student][$day] ?? [];
                                foreach ($cells as $cell) {
                                  if ($cell['area'] === $areaName) {
                                    $status = $cell['status'] ?? null;
                                    break 2;
                                  }
                                }
                              }
                            }
                            if ($status === null || $status === '') {
                              $status = 'checked';
                            }
                          @endphp
                          <td style="height: 36px; min-width: 60px; max-width: 90px; text-align: center; vertical-align: middle; padding:6px 4px;">
                            {{-- Only show status icon, NO NAME --}}
                            @if($status === 'checked')
                              <div style="width:23px;height:23px;margin:auto;background:#08a821;border-radius:3px;display:flex;align-items:center;justify-content:center;">
                                <i class="ri-check-line" style="color:#fff;font-size:0.95em;"></i>
                              </div>
                            @elseif($status === 'wrong')
                              <div style="width:23px;height:23px;margin:auto;background:#e61515;border-radius:3px;display:flex;align-items:center;justify-content:center;">
                                <i class="ri-close-line" style="color:#fff;font-size:0.95em;"></i>
                              </div>
                            @endif
                          </td>
                        @endforeach
                      </tr>
                    @endforeach
                  @endif
            </tbody>
          </table>
        </div>

        <!-- Task History - Remarks Section -->
        @if(!empty($studentNames))
        <div class="mt-6" style="max-width: 1400px; background-color:rgb(238, 215, 215); border-left: 5px solid rgb(219, 4, 4); padding: 22px 32px 18px 32px; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); margin-bottom: 0px; margin-left: 0px; margin-right: 0;">
          <h2 style="margin-top: 0; margin-bottom: 18px; font-size: 1.25rem; font-weight: 700; color:rgb(0, 2, 8); letter-spacing:0.5px;">
            📋 Task History – Remarks
          </h2>

          @php
            $overallWrong = 0;
            foreach ($dayMap as $day) {
              foreach ($allAreas as $areaName) {
                $status = null;
                // For 'Beds' and 'Storage and Organization', get status from any student
                if (in_array($areaName, ['Beds', 'Storage and Organization'])) {
                  foreach ($studentNames as $student) {
                    $cells = $matrix[$student][$day] ?? [];
                    foreach ($cells as $cell) {
                      if ($cell['area'] === $areaName) {
                        $status = $cell['status'] ?? null;
                        break 2;
                      }
                    }
                  }
                } else {
                  foreach ($studentNames as $student) {
                    $cells = $matrix[$student][$day] ?? [];
                    foreach ($cells as $cell) {
                      if ($cell['area'] === $areaName) {
                        $status = $cell['status'] ?? null;
                        break 2;
                      }
                    }
                  }
                }
                if ($status === 'wrong') {
                  $overallWrong++;
                }
              }
            }

            // Determine overall remark based on wrong marks
            if ($overallWrong <= 3) {
              $overallRemark = '<span style="color:#119416;font-weight:600;"><img src="' . asset('images/check-mark.png') . '" alt="Satisfactory" style="width:24px;height:24px;vertical-align:-5px;margin-right:3px;"> Satisfactory</span>';
            } elseif ($overallWrong <= 6) {
              $overallRemark = '<span style="color:#e6a700;font-weight:600;"><img src="' . asset('images/warning.png') . '" alt="Needs Improvement" style="width:24px;height:24px;vertical-align:-5px;margin-right:3px;"> Needs Improvement</span>';
            } else {
              $overallRemark = '<span style="color:#e3342f;font-weight:600;"><img src="' . asset('images/no.png') . '" alt="Unsatisfactory" style="width:24px;height:24px;vertical-align:-5px;margin-right:3px;"> For Consequence</span>';
            }
          @endphp

          <div style="margin-bottom: 18px; display:flex; align-items:center; gap:18px;">
            <span style="font-size:1.1em;font-weight:600;color:#222;">
              Total Wrong Marks: <span style="color:#007bff;">{{ $overallWrong }}</span>
            </span>
            <span style="font-size:1.1em;">{!! $overallRemark !!}</span>
          </div>

          <div style="margin-bottom: 14px;">
            <div style="display:flex;align-items:center;gap:28px;">
              <span style="font-size:1.1em;">
                <img src="{{ asset('images/check-mark.png') }}" alt="Satisfactory" style="width:28px;height:28px;vertical-align:-6px;margin-right:4px;">
                0–3 wrong marks <b>= Satisfactory</b>
              </span>
              <span style="font-size:1.1em;">
                <img src="{{ asset('images/warning.png') }}" alt="Needs Improvement" style="width:28px;height:28px;vertical-align:-6px;margin-right:4px;">
                4–6 wrong marks <b>= Needs Improvement</b>
              </span>
              <span style="font-size:1.1em;">
                <img src="{{ asset('images/no.png') }}" alt="For Consequence" style="width:28px;height:28px;vertical-align:-6px;margin-right:4px;">
                7+ wrong marks <b>= For Consequence</b>
              </span>
            </div>
          </div>

          <div style="background-color:rgba(255,255,255,0.7); padding:16px; border-radius:8px; margin-top:16px;">
            <p style="margin:0; font-size:0.95em; color:#444; line-height:1.5;">
              <b>Note:</b> If students receive <b>7 or more wrong marks repeatedly</b>, they may be given a consequence <b>depending on the nature of the infraction</b>.
            </p>
          </div>
        </div>
        @endif
         </div>
      </div>
    </div>
  </div>
</body>
</html>
