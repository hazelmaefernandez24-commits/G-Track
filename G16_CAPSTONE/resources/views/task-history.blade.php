<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room Task History - Tasking Hub System</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.6.0/remixicon.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons/css/all/all.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
   <style>
      body{
        font-family: 'Poppins', sans-serif;
        margin: 0;
        background: #f6f8fa;
        /* make room for fixed header; computed at runtime */
        padding-top: 0;
      }
      header {
        font-family: 'Poppins', sans-serif;
        background-color: #22BBEA;
        color: white;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1100; /* ensure header is above sidebar/content */
        box-sizing: border-box;
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
        }
    .container-fluid {
      font-family: 'Poppins', sans-serif;
      display: flex;
      min-height: 100vh;
      min-width: 1200px;
    }
  .sidebar {
      font-family: 'Poppins', sans-serif;
      border-right: 3px solid #22BBEA;
      background: #f8f9fa;
      width: 240px;
      padding-top: 10px;
      padding-bottom: 30px;
      /* fix sidebar to viewport and allow internal scroll */
      position: fixed;
      top: 0; /* will be adjusted at runtime to sit below header */
      left: 0;
      bottom: 0;
      overflow-y: auto;
        }
        .sidebar ul {
            font-family: 'Poppins', sans-serif;
            list-style: none;
            padding-left: 0;
            margin: 0;
        }
        .sidebar ul li {
            font-family: 'Poppins', sans-serif;
            margin: 15px 0;
            border-radius: 5px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .sidebar ul li a {
            font-family: 'Poppins', sans-serif;
            text-decoration: none;
            color: black;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 15px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        .sidebar ul li:hover {
            font-family: 'Poppins', sans-serif;
            background: #fa5408;
            color: white;
`            max-width: 100%;
        }
        .sidebar ul li:hover a {
            font-family: 'Poppins', sans-serif;
            color: white;
        }
        .sidebar-icon {
            width: 30px;
            height: 30px;
            margin-right: 8px;
            vertical-align: middle;
        }
        .content {
            font-family: 'Poppins', sans-serif;
            flex: 1;
            padding: 0;
            margin: 0;
            background: #f6f8fa;
            /* leave space for fixed sidebar */
            margin-left: 270px;
        }
        /* --- Restore table/content design --- */
        .main-content-wrapper {
            font-family: 'Poppins', sans-serif;
            background: #f6f8fa;
            padding: 20px 40px;
            border-bottom: 1px solid #ddd;
        }
        .content-header h1 {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #333;
        }
        .content-body {
            font-family: 'Poppins', sans-serif;
            padding: 20px 40px;
        }
        .search-form {
            font-family: 'Poppins', sans-serif;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .search-form select, .search-form input {
            font-family: 'Poppins', sans-serif;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .search-form button {
            font-family: 'Poppins', sans-serif;
            background: #22BBEA;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
        }
        .search-form button:hover {
            font-family: 'Poppins', sans-serif;
            background: #1a9ac9;
        }
        table {
            font-family: 'Poppins', sans-serif;
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: #fff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        th, td {
            font-family: 'Poppins', sans-serif;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            font-family: 'Poppins', sans-serif;
            background: #22BBEA;
            color: white;
            font-weight: 600;
        }
        tr:hover {
            font-family: 'Poppins', sans-serif;
            background: #f5f5f5;
        }
        .status-check {
            font-family: 'Poppins', sans-serif;
            color: #28a745;
        }
        .status-wrong {
            font-family: 'Poppins', sans-serif;
            color: #dc3545;
        }
        .filter-group {
            font-family: 'Poppins', sans-serif;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .filter-group label {
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            min-width: 80px;
        }
        #clearButton {
            font-family: 'Poppins', sans-serif;
            background: #f8f9fa;
            border: 1px solid #ddd;
            color: #333;
            padding: 8px 16px;
            border-radius: 4px;
            text-decoration: none;
            margin-left: 10px;
        }
        #clearButton:hover {
            font-family: 'Poppins', sans-serif;
            background: #e9ecef;
        }
        .filter-type-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            margin-right: 10px;
        }
  </style>
</head>
<body>
  <header>
    <div class="logo">
        <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
    </div>
</header>
<div class="container-fluid">
  <div class="row">
    @include('partials.sidebar')
  </div>
    <div class="main-content content">
      <div class="content-body">
        <div class="search-form">
            <form action="{{ Route::has('admin.task.history') ? route('admin.task.history') : url('/task-history') }}" method="GET" style="width: 100%;">
              <div style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                <div class="filter-group">
                  <label for="room">Room:</label>
                  <select name="room" id="room">
                    <option value="">Select Room</option>
                      @foreach($rooms as $r)
                        <option value="{{ $r->room_number }}" {{ (request('room', $room ?? '') == $r->room_number) ? 'selected' : '' }}>{{ $r->room_number }}</option>
                      @endforeach
                  </select>
                </div>
                <div class="filter-group">
                  <label for="filter_type">&nbsp;</label>
                  <!-- remove filter select; keep layout spacing -->
                </div>
                <div class="filter-group">
                  <label for="month">Start Date:</label>
                  <input type="date" name="start_date" id="start_date" value="{{ request('start_date', '') }}" />
                </div>
                <div class="filter-group">
                  <label for="year">End Date:</label>
                  <input type="date" name="end_date" id="end_date" value="{{ request('end_date', '') }}" />
                </div>
                <div class="filter-group">
                  <label for="week">&nbsp;</label>
                  <button type="submit" id="searchButton" style="height: 44px; display: flex; align-items: center;">Search</button>
                  <a href="{{ Route::has('admin.task.history') ? route('admin.task.history') : url('/task-history') }}" id="clearButton" style="height: 25px; display: flex; align-items: center;">Clear</a>
                </div>
              </div>
            </form>
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded" id="historyInfoBox" style="margin-left:0; margin-right:0;">
          <h2 class="text-xl font-semibold text-blue-800 mb-2">
            Room Checklist History
          </h2>
          @php
            $start_date = request('start_date');
            $end_date = request('end_date');
          @endphp
         <p class="text-blue-700">
      The table below shows the history of all completed room checklists submitted
      @if(!empty($startDateStr) || !empty($endDateStr))
        <b>from {{ $startDateStr ?? ($start_date ?: 'Start') }} to {{ $endDateStr ?? ($end_date ?: 'Now') }}</b>.
      @elseif(!empty($start_date) || !empty($end_date))
        <b>from {{ $start_date ?: 'Start' }} to {{ $end_date ?: 'Now' }}</b>.
      @endif
      This section reflects all submitted records within the selected date range.
    </p>
        </div>
        <div style="display: flex; flex-direction: row; gap: 0; align-items: flex-start;">
          <div style="flex: 1; margin-left: 0; width:100%;">
            <!-- Task History Table -->
            <div id="taskHistoryTable" class="overflow-x-auto" style="max-width: 1500px; width:100%;">
              @if(!empty($taskHistories) && is_array($taskHistories))
                @foreach($taskHistories as $dateKey => $dayHistories)
                  <h3 style="margin-top:18px; margin-bottom:6px;">{{ \Carbon\Carbon::parse($dateKey)->format('F j, Y') }}</h3>
                  <table style="width:100%; min-width: 1300px; font-size: 1.05em; margin-bottom:18px;">
                    <thead>
                      <tr>
                        <th style="width: 180px; font-size:1em; text-align:center;">Assigned To</th>
                        <th style="width: 140px; font-size:1em; text-align:center;">Task Area</th>
                        <th style="font-size:1em; text-align:center; width: 200px">Task Description</th>
                        <th style="width: 80px; font-size:1em; text-align:center;">Status</th>
                        <th style="width: 120px; font-size:1em; text-align:center;">Date</th>
                      </tr>
                    </thead>
                    @php
                      // Filter out summary rows that have no task_id and no assigned/task fields.
                      $visibleHistories = [];
                      foreach ($dayHistories as $h) {
                        $hasTaskData = !(empty($h->task_id) && empty($h->assigned_to) && empty($h->task_area) && empty($h->task_description));
                        if ($hasTaskData) {
                          $visibleHistories[] = $h;
                        }
                      }

                      // Compute the wrong count and remark for this day based on visible entries
                      $dayWrong = 0;
                      foreach ($visibleHistories as $h) {
                        $s = $h->status ?? optional($h->roomTask)->status ?? null;
                        if ($s === 'wrong') {
                          $dayWrong++;
                        }
                      }
                      if ($dayWrong <= 3) {
                        $dayRemarkText = 'Satisfactory';
                        $dayRemarkColor = '#119416';
                      } elseif ($dayWrong <= 6) {
                        $dayRemarkText = 'Needs Improvement';
                        $dayRemarkColor = '#e6a700';
                      } else {
                        $dayRemarkText = 'For Consequence';
                        $dayRemarkColor = '#e3342f';
                      }
                    @endphp

                    <tbody>
                      @if(count($visibleHistories) > 0)
                        @foreach($visibleHistories as $history)
                          @php
                            $assigned = $history->assigned_to ?? optional($history->roomTask)->name ?? '';
                            $area = $history->task_area ?? optional($history->roomTask)->area ?? '';
                            $desc = $history->task_description ?? optional($history->roomTask)->desc ?? '';
                            $status = $history->status ?? optional($history->roomTask)->status ?? '';
                          @endphp
                          <tr>
                            <td style="text-align:center;">{{ $assigned }}</td>
                            <td style="text-align:center;">{{ $area }}</td>
                            <td style="text-align:left;">{{ $desc }}</td>
                            <td style="text-align:center;">
                              @if(in_array($status, ['check','checked','completed']))
                                <div style="width:23px;height:23px;margin:auto;background:#119416;border-radius:3px;display:flex;align-items:center;justify-content:center;">
                                  <i class="ri-check-line" style="color:#fff;font-size:0.95em;"></i>
                                </div>
                              @elseif($status === 'wrong')
                                <div style="width:23px;height:23px;margin:auto;background:#e61515;border-radius:3px;display:flex;align-items:center;justify-content:center;">
                                  <i class="ri-close-line" style="color:#fff;font-size:0.95em;"></i>
                                </div>
                              @endif
                            </td>
              @php
                // Prefer to display the grouped dateKey so every row in the day's table shows the same calendar date.
                try {
                  $displayDate = \Carbon\Carbon::parse($dateKey)->format('M d, Y');
                } catch (\Exception $e) {
                  if (!empty($history->day)) {
                    try {
                      $displayDate = \Carbon\Carbon::parse($history->day)->format('M d, Y');
                    } catch (\Exception $ex) {
                      $displayDate = optional($history->created_at)->format('M d, Y');
                    }
                  } else {
                    $displayDate = optional($history->created_at)->format('M d, Y');
                  }
                }
              @endphp
              <td style="text-align:center;">{{ $displayDate }}</td>
                          </tr>
                        @endforeach
                      @else
                        <tr>
                          <td colspan="5" style="text-align:center;">No history records for this date.</td>
                        </tr>
                      @endif
                    </tbody>
                    </tbody>

                    <!-- Remarks row placed after the last history row -->
                    <tfoot>
                      <tr>
                        <td colspan="5" style="background:#fff; font-weight:700; padding:12px 15px;">
                          <span style="color:#e3342f; font-weight:800; text-transform:uppercase; margin-right:12px;">REMARKS:</span>
                          <span style="color:{{ $dayRemarkColor }}; font-weight:800; text-transform:uppercase;">{{ strtoupper($dayRemarkText) }}</span>
                          <span style="margin-left:12px; color:darkred; font-weight:600;">({{ $dayWrong }} wrong)</span>
                        </td>
                      </tr>
                    </tfoot>

                  </table>
                @endforeach
              @else
                <p>No history records found.</p>
              @endif
            </div>

            <!-- Remarks / Legend Section -->
            <div class="mt-6" style="max-width: 1400px; background-color:rgb(238, 215, 215); border-left: 5px solid rgb(219, 4, 4); padding: 22px 32px 18px 32px; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); margin-bottom: 0px; margin-left: 0px; margin: right 0;">
              <h2 style="margin-top: 0; margin-bottom: 18px; font-size: 1.25rem; font-weight: 700; color:rgb(0, 2, 8); letter-spacing:0.5px;">
                Task History – Remarks
              </h2>
              @if(!empty($studentNames))
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
                        // For other areas, find the student assigned for this area on this day
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
                    Total Wrong Marks: <span style="color:darkred;">{{ $overallWrong }}</span>
                  </span>
                  <span style="font-size:1.1em;">{!! $overallRemark !!}</span>
                </div>
              @endif
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
                    <img src="{{ asset('images/no.png') }}" alt="For Consequence" style="width:26px;height:26px;vertical-align:-6px;margin-right:4px;">
                    7+ wrong marks <b>= For Consequence</b>
                  </span>
                </div>
              </div>
              <div style="background:rgb(245, 107, 107);border-radius:6px;padding:20px 14px;font-size:0.98em;color:black;">
                <b>Note:</b> If students receive <b>7 or more wrong marks repeatedly</b>, they may be given a consequence<b> depending on the nature of the infraction.</b> 
              </div>
            </div> 

            {{-- Feedbacks for this week --}}
            @if(!empty($feedbacksByDay)) 
            <div class="feedbacks-wrapper" style="max-width: 1400px; background-color:rgb(183, 222, 235); border-left: 5px solid rgb(43, 174, 250); padding: 22px 32px 18px 32px; border-radius: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.05); margin-bottom: 0px; margin-left: 0px; margin-right: 0;">
                <h2 style="margin-top: 0; margin-bottom: 18px; font-size: 1.25rem; font-weight: 700; color:rgb(0, 2, 8); letter-spacing:0.5px;">
                    📝 Feedbacks for this week
                </h2>
                @foreach($dayMap as $day)
                    @if(isset($feedbacksByDay[$day]) && count($feedbacksByDay[$day]))
                        <div class="feedback-history-day" style="margin-bottom: 24px;">
                            <h4 style="margin-bottom: 8px;">{{ $day }}</h4>
                            @foreach($feedbacksByDay[$day] as $feedback)
                                <div class="feedback-history-card" style="border:1px solid #ddd; border-radius:8px; margin-bottom:12px; padding:12px; background: #fff;">
                                    <div>
                                        <strong>Feedback:</strong>
                                        <div style="background:whitesmoke; border-radius:6px; padding:8px 12px; margin:6px 0;">
                                            {{ $feedback->feedback }}
                                        </div>
                                    </div>
                                    @if($feedback->photo_paths)
                                        <div style="margin-top:8px;">
                                            <strong>Photos:</strong>
                                            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                                                @foreach(json_decode($feedback->photo_paths, true) as $img)
                                                    <img src="{{ asset('storage/' . $img) }}" style="width:100px; height:75px; object-fit:cover; border-radius:6px; border:1px solid #dbe4f3;">
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <div style="font-size:12px; color:#888; margin-top:6px;">
                                        Uploaded: 
                                        {{ \Carbon\Carbon::parse($feedback->created_at)->timezone('Asia/Manila')->format('F j, Y · h:i A') }}
                                        <br>
                                        Day: <b>{{ $feedback->day }}</b> | Year: <b>{{ $feedback->year }}</b>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Logout removed - only available in dashboard -->
</body>
<script>
  (function() {
    function adjustLayoutForHeader() {
      try {
        var header = document.querySelector('header');
        var body = document.body;
        var sidebar = document.querySelector('.sidebar');
        if (!header) return;
        var h = header.getBoundingClientRect().height;
        // Apply padding to body so content starts below header
        body.style.paddingTop = h + 'px';
        // Position sidebar below header
        if (sidebar) {
          sidebar.style.top = h + 'px';
        }
      } catch (e) {
        console.error('adjustLayoutForHeader error', e);
      }
    }

    window.addEventListener('DOMContentLoaded', adjustLayoutForHeader);
    window.addEventListener('load', adjustLayoutForHeader);
    window.addEventListener('resize', function() {
      // debounce
      clearTimeout(window.__adjustHeaderTimer);
      window.__adjustHeaderTimer = setTimeout(adjustLayoutForHeader, 100);
    });
  })();
</script>
</html>