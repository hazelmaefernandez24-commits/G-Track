<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Room Management - PN Tasking Hub System</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link rel="stylesheet" href="{{ asset('css/room-management.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons/css/all/all.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <style>
  body {
  overflow-x: hidden;
     }

  .sidebar { border-right: none !important; }
    .room-management-container {
      /* Reduce top padding so the page title sits closer to the header
         and decrease horizontal padding so content sits closer to the sidebar */
      padding: 32px 12px 48px;
      /* Limit page width so layout matches intended desktop view at 100% zoom */
      max-width: 1400px;
      margin: 0 auto;
    }
    
    .stats-overview {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .stat-card {
      background: linear-gradient(135deg, #22bbea 0%, #1a9bc8 100%);
      color: white;
      padding: 20px;
      border-radius: 12px;
      text-align: center;
      box-shadow: 0 4px 15px rgba(34, 187, 234, 0.3);
    }
    
    .stat-number {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 5px;
    }
    
    .stat-label {
      font-size: 0.9rem;
      opacity: 0.9;
    }
    
    .management-sections {
      display: grid;
      grid-template-rows: 1fr 1fr;
      gap: 30px;
      margin-bottom: 30px;
    }
    
    .section-card {
      background: white;
      border-radius: 12px;
      padding: 25px;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
      border: 1px solid #e9ecef;
    }
    
    .section-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 2px solid #f8f9fa;
    }
    
    .section-title {
      font-size: 1.4rem;
      font-weight: 600;
      color: #333;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    
    .btn-primary-custom {
      background: linear-gradient(135deg, #22bbea 0%, #1a9bc8 100%);
      border: none;
      color: white;
      padding: 10px 20px;
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .btn-primary-custom:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(34, 187, 234, 0.4);
      color: white;
    }
    
    .room-grid {
      display: grid;
      /* Flexible grid: each column is at least 300px and expands to fill space.
         This prevents forced horizontal scroll while keeping a 3-column layout
         on wide screens similar to your screenshot. */
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 24px;
      padding-bottom: 10px;
    }
    
    .room-card-design {
      background: white;
      border: 1px solid #e9ecef;
      border-radius: 12px;
      padding: 28px 20px;
      /* Let the card size be driven by the grid column width */
      width: 100%;
      max-width: none;
      box-sizing: border-box;
      transition: all 0.3s ease;
      margin-bottom: 10px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.08);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
    }
    /* Non-student room styling (e.g., Storage Room) */
    .room-card-design.non-student {
      border-color: #fee2e2;
      box-shadow: 0 6px 20px rgba(220,38,38,0.06);
      background: #fffaf9;
      border-left: 6px solid #fecaca;
    }
    .room-card-design.non-student .room-description-design {
      font-size: 1.05rem;
      color: #b91c1c; /* deeper red */
      font-weight: 700;
      margin: 12px auto 0;
      letter-spacing: 0.2px;
      text-align: center;
      display: block;
    }
    .non-student-badge {
      display: inline-block;
      margin-left: 10px;
      padding: 6px 10px;
      background: #fff1f2;
      color: #991b1b;
      border: 1px solid #fecaca;
      border-radius: 999px;
      font-weight: 700;
      font-size: 0.75rem;
    }
    .room-card-design.non-student .room-number-design { color: #1f2937; }

    .non-student-highlight {
      margin: 18px auto 10px;
      padding: 18px 16px;
      background: #fff5f5;
      border: 1px dashed #fecaca;
      border-radius: 12px;
      text-align: center;
      color: #b91c1c;
      font-weight: 600;
      display: flex;
      flex-direction: column;
      gap: 6px;
      align-items: center;
    }
    .non-student-highlight i {
      font-size: 1.4rem;
      color: #f97316;
    }
    .non-student-highlight span {
      font-size: 1rem;
    }
    .non-student-highlight small {
      font-size: 0.85rem;
      color: #9f1239;
      font-weight: 500;
    }
    

    
    .room-header {
      display: flex;
      justify-content: between;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .room-number {
      font-size: 1.3rem;
      font-weight: 600;
      color: #333;
    }
    
    .room-status {
      padding: 4px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 500;
    }
    
    .status-active {
      background: #d4edda;
      color: #155724;
    }
    
    .status-inactive {
      background: #f8d7da;
      color: #721c24;
    }
    
    /* Vacant: no occupants regardless of room status */
    .status-vacant {
      background: #eef2ff;
      color: #3730a3;
    }
    
    .capacity-bar {
      background: #f8f9fa;
      border-radius: 10px;
      height: 8px;
      margin: 10px 0;
      overflow: hidden;
    }
    
    .capacity-fill {
      height: 100%;
      border-radius: 10px;
      transition: width 0.3s ease;
    }
    
    .capacity-normal {
      background: linear-gradient(90deg, #28a745, #20c997);
    }
    
    .capacity-warning {
      background: linear-gradient(90deg, #ffc107, #fd7e14);
    }
    
    .capacity-full {
      background: linear-gradient(90deg, #dc3545, #c82333);
    }
    
    .occupants-list {
      list-style: none;
      padding: 0;
      margin: 10px 0;
    }
    
    .occupants-list li {
      padding: 5px 0;
      font-size: 0.9rem;
      color: #666;
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .room-actions {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
    
    .btn-sm-custom {
      padding: 6px 12px;
      font-size: 0.8rem;
      border-radius: 6px;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    
    .btn-edit {
      background: #17a2b8;
      color: white;
    }
    
    .btn-edit:hover {
      background: #138496;
      color: white;
    }
    
    .btn-delete {
      background: #dc3545;
      color: white;
    }
    
    .btn-delete:hover {
      background: #c82333;
      color: white;
    }
    
    .btn-tasks {
      background: #6f42c1;
      color: white;
    }
    
    .btn-tasks:hover {
      background: #5a32a3;
      color: white;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
        <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
    </div>
    
    <!-- PN-ScholarSync Logout Icon -->
    <div class="logout-container">
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
      </form>
    </div>
  </header>

  <div class="container-fluid">
    <div class="row">
      <!-- Include consistent admin sidebar -->
      @include('partials.sidebar')
      
  <!-- Main content beside sidebar -->
  <main class="main-content" style="min-height: 90vh; padding: 10px 15px 15px 8px; padding-top: calc(var(--header-height, 60px) + 5px); margin-left: 100px;">
        <div class="container-fluid py-1">
        <div class="room-management-container">
          <!-- Page Header -->
          <div style="margin-bottom: 12px;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
              <div>
                <h1 style="font-size: 1.8rem; font-weight: 600; color: #333; margin: 0 0 10px 0;">
                  Room Management
                </h1>
                <p style="font-size: 1rem; color: #666; margin: 0 0 15px 0; max-width: 800px; line-height: 1.5;">
                  Comprehensive control over room assignments, floor management, capacity settings, and occupancy monitoring.
                </p>
              </div>

            </div>
          </div>

          <!-- Quick Actions (moved from Dashboard) -->
          <div class="section-card" style="margin-bottom: 18px;">
            <div class="section-header" style="border-bottom:none; margin-bottom:10px; padding-bottom:0;">
              <h2 class="section-title">
                <i class="fi fi-rr-door-open" style="color: #22bbea;"></i>
                Quick Actions
              </h2>
              <p style="margin: 0; color: #666; font-size: 0.9rem; max-width: 800px; line-height: 1.5;">
                Quick access to essential room management functions. 
            </div>
            <div style="display:flex; gap:12px; flex-wrap:wrap;">
              <button id="capacitySettingsBtn" class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#capacityModal">
                <i class="fi fi-rr-settings" style="margin-right:6px;"></i> Set Capacity for All Rooms
              </button>
              <button id="openAddFloorBtn" class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#addFloorModal">
                <i class="fi fi-rr-plus" style="margin-right:6px;"></i> Add Floor
              </button>
              <button id="openAddRoomBtn" class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#newRoomModal">
                <i class="fi fi-rr-plus" style="margin-right:6px;"></i> Add Room
              </button>
              <button id="openDeleteFloorBtn" class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#deleteFloorModal">
                <i class="fi fi-rr-trash" style="margin-right:6px;"></i> Remove Floor
              </button>
            </div>
          </div>

          <!-- Rooms Grid -->
          <!-- Floor Filter Dropdown -->
          <div class="section-card mb-4">
            <div class="section-header d-flex justify-content-between align-items-center">
              <button class="btn-primary-custom" onclick="refreshRooms()" title="Sync with dashboard data and refresh">
                  <i class="fi fi-rr-refresh"></i>
                  Sync & Refresh
                </button>
              <div class="d-flex align-items-center gap-2 floor-filter-container" style="min-width: 360px;">
              <select class="form-select" id="floorFilterDropdown" aria-label="Filter by floor">
                  <option value="all" selected>All floors</option>
                  @php
                    $floors = [];
                    foreach ($rooms as $room) {
                        preg_match('/(\d+)/', $room->room_number, $matches);
                        if (!empty($matches[1])) {
                            $roomNumber = $matches[1];
                            $floor = (strlen($roomNumber) >= 4) ? substr($roomNumber, 0, 2) : substr($roomNumber, 0, 1);
                            if (!in_array($floor, $floors)) {
                                $floors[] = $floor;
                            }
                        }
                    }
                    sort($floors, SORT_NUMERIC);
                  @endphp
                  @foreach($floors as $floor)
                    @php
                      $n = intval($floor);
                      $suffix = ($n % 100 >= 11 && $n % 100 <= 13)
                                ? 'th'
                                : (($n % 10 == 1) ? 'st' : (($n % 10 == 2) ? 'nd' : (($n % 10 == 3) ? 'rd' : 'th')));
                    @endphp
                    <option value="{{ $floor }}">{{ $floor }}{{ $suffix }} floor</option>
                  @endforeach
                </select>
                <select class="form-select" id="genderFilterDropdown" aria-label="Filter by occupant gender" style="max-width:160px;">
                  <option value="all" selected>All</option>
                  <option value="male">Male occupants</option>
                  <option value="female">Female occupants</option>
                </select>
                <button class="btn btn-primary" id="applyFloorFilter">Apply</button>
                <button class="btn btn-outline-secondary" id="clearFloorFilter">Clear</button>
              </div>
            </div>
          </div>

          <div class="section-card">
            <div class="section-header">
              <div style="display: flex; gap: 10px;">
              </div>

              
            </div>
            
            <div class="room-grid" id="roomsGrid">
              @foreach($rooms as $room)
                @php
                  $maleCount = $room->assignments->where('student_gender', 'M')->count();
                  $femaleCount = $room->assignments->where('student_gender', 'F')->count();
                  // detect student room once and add a class for non-student rooms
                  $_desc = trim(strtolower($room->description ?? ''));
                  $_isStudentRoom = ($_desc === '' || strpos($_desc, 'student') !== false);
                  $storedOccupantTypeRaw = strtolower($room->occupant_type ?? '');
                  $storedOccupantType = in_array($storedOccupantTypeRaw, ['male', 'female', 'both'])
                    ? $storedOccupantTypeRaw
                    : 'both';
                  if ($storedOccupantType === 'both') {
                    if ($maleCount > 0 && $femaleCount === 0) {
                      $storedOccupantType = 'male';
                    } elseif ($femaleCount > 0 && $maleCount === 0) {
                      $storedOccupantType = 'female';
                    }
                  }
                  $occupantType = $storedOccupantType;
                  $badgeMeta = [
                    'text' => 'All Occupants Allowed',
                    'bg' => '#ecfccb',
                    'color' => '#3f6212'
                  ];
                  if ($occupantType === 'female') {
                    $badgeMeta = ['text' => 'Female Occupants Only', 'bg' => '#fde5f3', 'color' => '#9d174d'];
                  } elseif ($occupantType === 'male') {
                    $badgeMeta = ['text' => 'Male Occupants Only', 'bg' => '#dbeafe', 'color' => '#1d4ed8'];
                  }
                @endphp
                <div class="room-card-design {{ $_isStudentRoom ? '' : 'non-student' }}" data-room-id="{{ $room->id }}" data-male-count="{{ $maleCount }}" data-female-count="{{ $femaleCount }}">
                  <!-- Room Header with Icon and Status -->
                  <div class="room-header-design">
                    <div class="room-title-with-icon">
                      <i class="fi fi-rr-home room-icon-design"></i>
                      <span class="room-number-design">Room {{ $room->room_number }}</span>
                    </div>
                    @php
                      $occupantsCount = $room->assignments->count();
                      // If there are no occupants, show Vacant regardless of stored room status
                      if ($occupantsCount === 0) {
                        $displayClass = 'vacant';
                        $displayText = 'Vacant';
                      } else {
                        $displayClass = ($room->status === 'active') ? 'active' : 'inactive';
                        $displayText = ($room->status === 'active') ? 'Occupied' : 'Vacant';
                      }
                    @endphp
                    <span class="status-pill status-{{ $displayClass }}">{{ $displayText }}</span>
                    @if(isset($_isStudentRoom) && !$_isStudentRoom)
                     
                    @endif
                  </div>

                  <!-- Room Name (only show if a custom name is set different from the default 'Room X') -->
                  @php
                    $defaultName = 'Room ' . $room->room_number;
                    $roomName = trim($room->name ?? '');
                  @endphp
                  @if($roomName && $roomName !== $defaultName)
                    <h3 class="room-name-design">{{ $roomName }}</h3>
                  @endif

                  @php
                    $isStudentRoom = $_isStudentRoom ?? true;
                  @endphp

                  <!-- Room Description -->
                  <p class="room-description-design">{{ $room->description ?: 'Room for students' }}</p>

                 

                  @if($isStudentRoom)
                    <!-- Occupancy Display -->
                    <div class="occupancy-display">
                      <span class="occupancy-text">Occupancy</span>
                      <span class="occupancy-numbers">{{ $room->assignments->count() }}/{{ $room->capacity }}</span>
                    </div>

                    <!-- Progress Bar -->
                    @php
                      $occupancyPercentage = $room->capacity > 0 ? ($room->assignments->count() / $room->capacity) * 100 : 0;
                      $barClass = $occupancyPercentage >= 100 ? 'bar-full' : ($occupancyPercentage >= 80 ? 'bar-warning' : 'bar-normal');
                    @endphp
                    <div class="progress-container">
                      <div class="progress-fill {{ $barClass }}" style="width: {{ min($occupancyPercentage, 100) }}%"></div>
                    </div>
                  @endif

                  <!-- Occupants Section (Expandable for rooms with students) -->
                  @php
                    $filteredAssignments = $room->assignments->filter(function ($assignment) use ($occupantType) {
                      if ($occupantType === 'female') {
                        return strtoupper($assignment->student_gender) === 'F';
                      }
                      if ($occupantType === 'male') {
                        return strtoupper($assignment->student_gender) === 'M';
                      }
                      return true;
                    });
                  @endphp
                  @if($filteredAssignments->count() > 0)
                    <div class="occupants-wrapper">
                      <div class="occupants-toggle" onclick="toggleOccupantsList({{ $room->id }})">
                        @if($occupantType !== 'both')
                          <span class="occupants-label">
                            <span style="display:inline-block; padding:4px 10px; border-radius:999px; font-weight:600; background:{{ $badgeMeta['bg'] }}; color:{{ $badgeMeta['color'] }};">
                              {{ $badgeMeta['text'] }}
                            </span>
                          </span>
                        @endif
                        <i class="fi fi-rr-angle-small-down expand-arrow" id="arrow-{{ $room->id }}"></i>
                      </div>
                      <div class="occupants-dropdown" id="occupants-{{ $room->id }}" style="display: none;">
                        @foreach($filteredAssignments as $assignment)
                          <div class="occupant-row">
                            <i class="fi fi-rr-user student-icon"></i>
                            <span class="student-name">{{ $assignment->student_name }}</span>
                            <span class="student-gender">({{ $assignment->student_gender === 'M' ? 'Male' : 'Female' }})</span>
                            <i class="fi fi-rr-angle-small-up move-up" onclick="moveStudentUp({{ $assignment->id }})"></i>
                            <i class="fi fi-rr-angle-small-down move-down" onclick="moveStudentDown({{ $assignment->id }})"></i>
                          </div>
                        @endforeach

                        <!-- Additional Action Buttons in Expanded View -->
                      
    
                      </div>
                    </div>
                  @endif

                  <!-- Main Action Buttons (Always Visible) -->
                  <div class="main-actions">
                    <!-- Edit opens the edit modal where admin can set Active/Inactive -->
                    <button class="main-btn edit-main" onclick="editRoom({{ $room->id }})">
                      <i class="fi fi-rr-edit"></i>
                      Edit
                    </button>

                    <!-- Delete calls the sync-aware delete routine -->
                    <button class="main-btn delete-main" onclick="deleteRoomWithSync({{ $room->id }}, '{{ $room->room_number }}')">
                      <i class="fi fi-rr-trash"></i>
                      Remove
                    </button>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        </div>
      </main>
    </div>
  </div>

  <!-- Add Room Modal (Enhanced with Dashboard Sync) -->
  <div class="modal fade" id="addRoomModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fi fi-rr-plus"></i>
            Add New Room
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="addRoomForm">
            <div class="mb-3">
              <label for="addRoomNumber" class="form-label">Room Number</label>
              <input type="text" class="form-control" id="addRoomNumber" name="room_number" required
                     placeholder="e.g., 201, 302, 405" pattern="[2-9][0-9]{2}">
              <small class="form-text text-muted">Enter 3-digit room number (floors 2-9)</small>
            </div>

            <!-- Capacity and Gender Distribution Row -->
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="addRoomCapacity" class="form-label">Total Capacity</label>
                <input type="number" class="form-control" id="addRoomCapacity" name="capacity" min="1" max="20" value="6">
              </div>
              <div class="col-md-4">
                <label for="addMaleCapacity" class="form-label">Male Students</label>
                <input type="number" class="form-control" id="addMaleCapacity" name="male_capacity" min="0" max="20" placeholder="0">
              </div>
              <div class="col-md-4">
                <label for="addFemaleCapacity" class="form-label">Female Students</label>
                <input type="number" class="form-control" id="addFemaleCapacity" name="female_capacity" min="0" max="20" placeholder="0">
              </div>
            </div>

            <!-- Batch Assignment -->
            <div class="mb-3">
              <label for="addRoomBatch" class="form-label">Assigned Batch</label>
              <select class="form-control" id="addRoomBatch" name="assigned_batch">
                <option value="">No specific batch</option>
                @if($students && count($students) > 0)
                  @foreach($students as $batch => $batchStudents)
                    <option value="{{ $batch }}">Batch {{ $batch }}</option>
                  @endforeach
                @else
                  <option value="2025">Batch 2025</option>
                  <option value="2026">Batch 2026</option>
                @endif
              </select>
            </div>

            <div class="mb-3">
              <label for="addRoomCategory" class="form-label">Room Category (optional)</label>
              <select class="form-control" id="addRoomCategory">
                <option value="">Select a category (optional)...</option>
                <option value="Conference Room">Conference Room</option>
                <option value="Office">Office</option>
                <option value="Storage Room">Storage Room</option>
                <option value="Utility Room">Utility Room</option>
                <option value="Equipment Room">Equipment Room</option>
                <option value="Study Room">Study Room</option>
                <option value="Meeting Room">Meeting Room</option>
                <option value="Pantry">Pantry</option>
                <option value="Others">Others (specify)</option>
              </select>
              <div class="mt-2" id="addRoomCategoryOtherContainer" style="display:none;">
                <input type="text" class="form-control" id="addRoomCategoryOther" placeholder="Specify other category">
              </div>
              <small class="form-text text-muted">Optional category for non-student rooms; will be saved into the description field.</small>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="addRoomWithSync()">
            <i class="fi fi-rr-plus"></i>
            Add Room
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Create Room Modal -->
  <div class="modal fade" id="createRoomModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fi fi-rr-plus"></i>
            Create New Room
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="createRoomForm">
            <div class="mb-3">
              <label for="roomNumber" class="form-label">Room Number</label>
              <input type="text" class="form-control" id="roomNumber" name="room_number" required>
            </div>

            <!-- Capacity and Gender Distribution Row -->
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="roomCapacity" class="form-label">Total Capacity</label>
                <input type="number" class="form-control" id="roomCapacity" name="capacity" min="1" max="20" required>
              </div>
              <div class="col-md-4">
                <label for="maleCapacity" class="form-label">Male Students</label>
                <input type="number" class="form-control" id="maleCapacity" name="male_capacity" min="0" max="20" placeholder="0">
              </div>
              <div class="col-md-4">
                <label for="femaleCapacity" class="form-label">Female Students</label>
                <input type="number" class="form-control" id="femaleCapacity" name="female_capacity" min="0" max="20" placeholder="0">
              </div>
            </div>

            <!-- Batch Assignment -->
            <div class="mb-3">
              <label for="roomBatch" class="form-label">Assigned Batch</label>
              <select class="form-control" id="roomBatch" name="assigned_batch">
                <option value="">No specific batch</option>
                @if($students && count($students) > 0)
                  @foreach($students as $batch => $batchStudents)
                    <option value="{{ $batch }}">Batch {{ $batch }}</option>
                  @endforeach
                @else
                  <option value="2025">Batch 2025</option>
                  <option value="2026">Batch 2026</option>
                @endif
              </select>
            </div>

            <div class="mb-3">
              <label for="roomStatus" class="form-label">Room Type</label>
              <select class="form-control" id="roomStatus" name="status" required>
                <option value="active">Student Room</option>
                <option value="inactive">Non-Student Room</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="roomCategory" class="form-label">Room Category (for Non-Student Rooms)</label>
              <select class="form-control" id="roomCategory">
                <option value="">Select a category...</option>
                <option value="Conference Room">Conference Room</option>
                <option value="Office">Office</option>
                <option value="Storage Room">Storage Room</option>
                <option value="Utility Room">Utility Room</option>
                <option value="Equipment Room">Equipment Room</option>
                <option value="Study Room">Study Room</option>
                <option value="Meeting Room">Meeting Room</option>
                <option value="Pantry">Pantry</option>
                <option value="Restroom">Restroom</option>
                <option value="Others">Others (specify)</option>
              </select>
              <div class="mt-2" id="roomCategoryOtherContainer" style="display:none;">
                <input type="text" class="form-control" id="roomCategoryOther" placeholder="Specify other category">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="createRoom()">Create Room</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Room Modal -->
  <div class="modal fade" id="editRoomModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fi fi-rr-edit"></i>
            Edit Room
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="editRoomForm">
            <input type="hidden" id="editRoomId" name="room_id">
            <div class="mb-3">
              <label for="editRoomNumber" class="form-label">Room Number</label>
              <input type="text" class="form-control" id="editRoomNumber" name="room_number" disabled>
            </div>

            <!-- Only allow editing Room Type (stored as active/inactive) and Description per request -->
            <div class="mb-3">
              <label for="editRoomStatus" class="form-label">Room Type</label>
              <select class="form-control" id="editRoomStatus" name="status" required>
                <option value="active">Student-Occupied Room</option>
                <option value="inactive">Non-Student Room</option>
              </select>
            </div>
            
            <!-- Occupant Type field - Only show for student rooms -->
            <div class="mb-3" id="editRoomOccupantTypeContainer" style="display: none;">
              <label for="editRoomOccupantType" class="form-label">Occupant Type</label>
              <select class="form-control" id="editRoomOccupantType" name="occupant_type" required>
                <option value="both">Select occupants type</option>
                <option value="male">Male Occupants Only</option>
                <option value="female">Female Occupants Only</option>
              </select>
            </div>
            
            <div class="mb-3" id="editRoomCategoryContainer" style="display: none;">
              <label for="editRoomCategory" class="form-label">Room Category (for Non-Student Rooms)</label>
              <select class="form-control" id="editRoomCategory">
                <option value="">Select a category...</option>
                <option value="Conference Room">Conference Room</option>
                <option value="Office">Office</option>
                <option value="Storage Room">Storage Room</option>
                <option value="Utility Room">Utility Room</option>
                <option value="Equipment Room">Equipment Room</option>
                <option value="Study Room">Study Room</option>
                <option value="Meeting Room">Meeting Room</option>
                <option value="Pantry">Pantry</option>
                <option value="Restroom">Restroom</option>
                <option value="Others">Others (specify)</option>
              </select>
              <div class="mt-2" id="editRoomCategoryOtherContainer" style="display: none;">
                <input type="text" class="form-control" id="editRoomCategoryOther" placeholder="Specify other category">
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="updateRoomBtn">Update Room</button>
        </div>
      </div>
    </div>
  </div>

  <style>
    /* Ensure modals appear above header and fixed sidebar */
    #editRoomModal,.modal { z-index: 20000 !important; }
    .modal-backdrop { z-index: 19990 !important; }
  </style>

  <!-- Student Assignment Modal -->
  <div class="modal fade" id="assignmentModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fi fi-rr-user-add"></i>
            Student Assignment
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h6>Select Room</h6>
              <select class="form-control mb-3" id="assignmentRoom">
                <option value="">Choose a room...</option>
                @foreach($rooms->where('status', 'active') as $room)
                  <option value="{{ $room->room_number }}" data-capacity="{{ $room->capacity }}" data-occupancy="{{ $room->assignments->count() }}">
                    Room {{ $room->room_number }} ({{ $room->assignments->count() }}/{{ $room->capacity }})
                  </option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <h6>Select Student</h6>
              <select class="form-control mb-3" id="assignmentStudent">
                <option value="">Choose a student...</option>
                @foreach($students as $batch => $batchStudents)
                  <optgroup label="Batch {{ $batch }}">
                    @foreach($batchStudents as $student)
                      <option value="{{ $student->user_id }}" data-gender="{{ $student->gender }}">
                        {{ $student->user_fname }} {{ $student->user_lname }} ({{ $student->gender === 'M' ? 'Male' : 'Female' }})
                      </option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
            </div>
          </div>
          <div id="assignmentInfo" class="alert alert-info" style="display: none;"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="assignStudent()" id="assignBtn" disabled>Assign Student</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Task Management Modal -->
  <div class="modal fade" id="taskModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fi fi-rr-list-check"></i>
            Room Task Management
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row mb-3">
            <div class="col-md-8">
              <h6 id="taskRoomTitle">Room Tasks</h6>
            </div>
            <div class="col-md-4 text-end">
              <button class="btn btn-primary btn-sm" onclick="openCreateTaskModal()">
                <i class="fi fi-rr-plus"></i>
                Add Task
              </button>
            </div>
          </div>
          <div id="tasksList">
            <!-- Tasks will be loaded here -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Create Task Modal -->
  <div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="fi fi-rr-plus"></i>
            Create New Task
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <form id="createTaskForm">
            <input type="hidden" id="taskRoomNumber" name="room_number">
            <div class="mb-3">
              <label for="taskArea" class="form-label">Task Area</label>
              <select class="form-control" id="taskArea" name="area" required>
                <option value="">Select area...</option>
                @foreach($taskAreas as $area => $description)
                  <option value="{{ $area }}">{{ $area }}</option>
                @endforeach
              </select>
            </div>
            <div class="mb-3">
              <label for="taskDescription" class="form-label">Description</label>
              <textarea class="form-control" id="taskDescription" name="description" rows="3" required></textarea>
            </div>
            <div class="mb-3">
              <label for="taskDay" class="form-label">Day</label>
              <select class="form-control" id="taskDay" name="day" required>
                <option value="">Select day...</option>
                <option value="Monday">Monday</option>
                <option value="Tuesday">Tuesday</option>
                <option value="Wednesday">Wednesday</option>
                <option value="Thursday">Thursday</option>
                <option value="Friday">Friday</option>
                <option value="Saturday">Saturday</option>
                <option value="Sunday">Sunday</option>
              </select>
            </div>
            <div class="mb-3">
              <label for="taskAssignedTo" class="form-label">Assigned To (Optional)</label>
              <input type="text" class="form-control" id="taskAssignedTo" name="assigned_to" placeholder="Leave empty for 'Everyone'">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" onclick="createTask()">Create Task</button>
        </div>
      </div>
    </div>
  </div>



  <!-- Quick Actions helper modals (dashboard parity) -->
  <!-- New Room Modal (used by Quick Actions) -->
  <div class="modal fade" id="newRoomModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Room</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="newRoomForm">
            <div class="mb-3">
              <label for="newRoomNumber" class="form-label">Room Number</label>
              <input type="number" id="newRoomNumber" name="room_number" class="form-control" required min="200" placeholder="e.g. 201">
              <small class="form-text text-muted">Enter a 3-digit room number (floor prefix required)</small>
            </div>
            <div class="mb-3">
              <label for="newRoomStatus" class="form-label">Room Type</label>
              <select id="newRoomStatus" name="status" class="form-control" required>
                <option value="active">Student-Occupied Room</option>
                <option value="inactive">Non-Student Room</option>
              </select>
            </div>
            <div class="mb-3" id="newRoomOccupantTypeContainer" style="display: none;">
              <label for="newRoomOccupantType" class="form-label">Occupant Type</label>
              <select id="newRoomOccupantType" name="occupant_type" class="form-control" required>
                <option value= "type">Select occupants type</option>
                 <option value="male">Male Occupants</option>
                <option value="female">Female Occupants</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="newRoomForm" class="btn btn-primary">Add Room</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Floor Modal -->
  <div class="modal fade" id="addFloorModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Add New Floor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="addFloorForm">
            <div class="mb-3">
              <label for="newFloorNumber" class="form-label">Floor Number</label>
              <input type="number" id="newFloorNumber" name="floor_number" class="form-control" required min="2" placeholder="e.g. 2">
            </div>
            <div class="mb-3">
              <label for="numRooms" class="form-label">Number of Rooms</label>
              <input type="number" id="numRooms" name="num_rooms" class="form-control" min="1" placeholder="e.g. 5">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="addFloorForm" class="btn btn-primary">Create Floor</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Floor Modal -->
  <div class="modal fade" id="deleteFloorModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Delete Floor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="deleteFloorForm">
            <div class="mb-3">
              <label for="floorToDelete" class="form-label">Select Floor to Delete</label>
              <select id="floorToDelete" name="floor_to_delete" class="form-control">
                <option value="">-- Select a floor --</option>
              </select>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="deleteFloorForm" class="btn btn-danger">Delete Floor</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Capacity Modal -->
  <div class="modal fade" id="capacityModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Room Capacity Settings</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="capacityForm">
            <div class="mb-3">
              <label for="globalRoomCapacity" class="form-label">Students per Room</label>
              <!-- Use a unique id here to avoid colliding with the create-room modal's `roomCapacity` input -->
              <input type="number" id="globalRoomCapacity" name="capacity" class="form-control" min="1" max="20" value="6" required>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" form="capacityForm" class="btn btn-primary">Save</button>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .room-card-design { transition: all 0.2s ease; }
    .room-card-design.hidden { display: none; }
    .floor-filter-container { margin-left: auto; }
    .section-title { margin-bottom: 0 !important; }
    @media (max-width: 768px) {
      .section-header { flex-direction: column; align-items: flex-start !important; gap: 10px; }
      .floor-filter-container { width: 100% !important; margin-left: 0; }
      .floor-filter-container .btn { flex: 1 0 auto; }
    }
  </style>

  <script>
    // Apply/Clear floor filter targeting room cards
    document.addEventListener('DOMContentLoaded', function() {
      const roomCards = document.querySelectorAll('.room-card-design');
      const floorFilter = document.getElementById('floorFilterDropdown');
      const applyBtn = document.getElementById('applyFloorFilter');
      const clearBtn = document.getElementById('clearFloorFilter');

      // Tag each card with its floor
      roomCards.forEach(card => {
        const numberEl = card.querySelector('.room-number-design');
        if (!numberEl) return;
        const text = numberEl.textContent || '';
        const match = text.match(/(\d+)/);
        if (match && match[1]) {
          const num = match[1];
          const floor = num.length >= 4 ? num.substring(0, 2) : num.charAt(0);
          card.setAttribute('data-floor', floor);
        }
      });

      const genderFilter = document.getElementById('genderFilterDropdown');

      function applyFilter() {
        const selected = floorFilter.value;
        const genderSelected = genderFilter ? genderFilter.value : 'all';

        roomCards.forEach(card => {
          // floor match
          let floorMatch = (selected === 'all');
          if (!floorMatch) {
            const f = card.getAttribute('data-floor');
            floorMatch = (f === selected);
          }

          // gender match: assume we show rooms that have at least one occupant of the selected gender
          let genderMatch = true;
          if (genderSelected === 'male') {
            const mc = parseInt(card.dataset.maleCount || '0', 10);
            genderMatch = mc > 0;
          } else if (genderSelected === 'female') {
            const fc = parseInt(card.dataset.femaleCount || '0', 10);
            genderMatch = fc > 0;
          }

          if (floorMatch && genderMatch) {
            card.classList.remove('hidden');
          } else {
            card.classList.add('hidden');
          }
        });
      }

      applyBtn.addEventListener('click', applyFilter);
      clearBtn.addEventListener('click', function() {
        floorFilter.value = 'all';
        if (genderFilter) genderFilter.value = 'all';
        applyFilter();
      });

      // Optional: auto-filter on change
      // floorFilter.addEventListener('change', applyFilter);
    });
  </script>

  <script>
    // Quick Actions wiring (open modals and submit)
    document.addEventListener('DOMContentLoaded', function() {
      const btnCapacity = document.getElementById('capacitySettingsBtn');
      const btnAddFloor = document.getElementById('openAddFloorBtn');
      const btnAddRoom = document.getElementById('openAddRoomBtn');
      const btnDeleteFloor = document.getElementById('openDeleteFloorBtn');

      const capacityModalEl = document.getElementById('capacityModal');
      const addFloorModalEl = document.getElementById('addFloorModal');
      const deleteFloorModalEl = document.getElementById('deleteFloorModal');
      const newRoomModalEl = document.getElementById('newRoomModal');

      const capacityModal = capacityModalEl ? new bootstrap.Modal(capacityModalEl) : null;
      const addFloorModal = addFloorModalEl ? new bootstrap.Modal(addFloorModalEl) : null;
      const deleteFloorModal = deleteFloorModalEl ? new bootstrap.Modal(deleteFloorModalEl) : null;
      const newRoomModal = newRoomModalEl ? new bootstrap.Modal(newRoomModalEl) : null;

      if (btnCapacity && capacityModal) btnCapacity.addEventListener('click', ()=> capacityModal.show());
      if (btnAddFloor && addFloorModal) btnAddFloor.addEventListener('click', ()=> addFloorModal.show());
      if (btnAddRoom && newRoomModal) btnAddRoom.addEventListener('click', ()=> newRoomModal.show());
      if (btnDeleteFloor && deleteFloorModal) btnDeleteFloor.addEventListener('click', ()=> {
        // Populate floors list from existing rooms
        try {
          const select = document.getElementById('floorToDelete');
          if (select) {
            // Clear existing options except the first one
            while (select.options.length > 1) {
              select.remove(1);
            }
            
            const floors = new Set();
            // Get all floor numbers
            document.querySelectorAll('.room-card-design .room-number-design').forEach(el=>{
              const m = (el.textContent||'').match(/(\d+)/);
              if (m && m[1]) {
                const num = m[1];
                const f = num.length >= 4 ? num.substring(0,2) : num.charAt(0);
                floors.add(parseInt(f));
              }
            });
            const arr = Array.from(floors).sort((a,b)=>Number(a)-Number(b));
            // Convert to array, sort, and add to select with ordinal suffixes
            const sortedFloors = Array.from(floors).sort((a,b) => a - b);
            sortedFloors.forEach(f => {
              const option = document.createElement('option');
              option.value = f;
              // Add ordinal suffix
              const j = f % 10, k = f % 100;
              const suffix = (j === 1 && k !== 11) ? 'st' :
                           (j === 2 && k !== 12) ? 'nd' :
                           (j === 3 && k !== 13) ? 'rd' : 'th';
              option.textContent = `${f}${suffix} floor`;
              select.appendChild(option);
            });
          }
        } catch(e){}
        deleteFloorModal.show();
      });

      // Form handlers
      const capacityForm = document.getElementById('capacityForm');
      if (capacityForm) capacityForm.addEventListener('submit', function(e){ e.preventDefault(); applyCapacitySettings(); });

      const addFloorForm = document.getElementById('addFloorForm');
      if (addFloorForm) addFloorForm.addEventListener('submit', function(e){ e.preventDefault(); handleAddFloorFormSubmit(e); });

      const deleteFloorForm = document.getElementById('deleteFloorForm');
      if (deleteFloorForm) deleteFloorForm.addEventListener('submit', function(e){ e.preventDefault(); handleDeleteFloorFormSubmit(e); });

      const newRoomForm = document.getElementById('newRoomForm');
      if (newRoomForm) newRoomForm.addEventListener('submit', function(e){ e.preventDefault(); handleNewRoomFormSubmit(e); });

      // Toggle occupant type visibility in Add Room modal based on Room Type selection
      const newRoomStatusEl = document.getElementById('newRoomStatus');
      const newRoomOccupantTypeContainer = document.getElementById('newRoomOccupantTypeContainer');
      
      function toggleNewRoomOccupantTypeUI() {
        if (!newRoomStatusEl || !newRoomOccupantTypeContainer) return;
        if (newRoomStatusEl.value === 'active') {
          // Show occupant type for student rooms
          newRoomOccupantTypeContainer.style.display = '';
        } else {
          // Hide occupant type for non-student rooms
          newRoomOccupantTypeContainer.style.display = 'none';
          const occupantTypeSelect = document.getElementById('newRoomOccupantType');
          if (occupantTypeSelect) occupantTypeSelect.value = 'both';
        }
      }
      
      if (newRoomStatusEl) {
        newRoomStatusEl.addEventListener('change', toggleNewRoomOccupantTypeUI);
        // Initialize on load
        toggleNewRoomOccupantTypeUI();
      }
    });

    async function applyCapacitySettings(){
      try {
  // Use the unique global capacity input id so we don't pick up the create-room's roomCapacity field
  const capacityInput = document.getElementById('globalRoomCapacity');
  const capacity = Number(capacityInput ? capacityInput.value : 0);
        if (!capacity || capacity < 1) { alert('Enter a valid capacity'); return; }

        const res = await fetch('/api/room/reassign-students', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
          },
          credentials: 'same-origin',
          body: JSON.stringify({ capacity: capacity })
        });

        const text = await res.text().catch(()=>'');
        let json = {};
        try { json = text ? JSON.parse(text) : {}; } catch(e) { json = { __raw: text }; }

        if (!res.ok) {
          const msg = json && json.message ? json.message : `Failed to apply capacity (HTTP ${res.status})`;
          throw new Error(msg);
        }

        // Close modal and refresh
        const capacityModalEl = document.getElementById('capacityModal');
        if (capacityModalEl) {
          try { new bootstrap.Modal(capacityModalEl).hide(); } catch(e){}
        }
        if (typeof refreshRooms === 'function') refreshRooms();
        else location.reload();
      } catch(err){ console.error(err); alert(err.message || 'Error applying capacity'); }
    }
  </script>

  <script>
    // CSRF Token setup
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Modal instances
    let createRoomModal, editRoomModal, assignmentModal, taskModal, addRoomModal;

    document.addEventListener('DOMContentLoaded', function() {
      createRoomModal = new bootstrap.Modal(document.getElementById('createRoomModal'));
      editRoomModal = new bootstrap.Modal(document.getElementById('editRoomModal'));
      assignmentModal = new bootstrap.Modal(document.getElementById('assignmentModal'));
      taskModal = new bootstrap.Modal(document.getElementById('taskModal'));
      addRoomModal = new bootstrap.Modal(document.getElementById('addRoomModal'));

      // Wire Update Room button to updateRoom() to avoid inline onclick overlap
      const updateBtn = document.getElementById('updateRoomBtn');
      if (updateBtn) {
        updateBtn.addEventListener('click', function() {
          updateRoom();
        });
      }

      // Toggle category UI when Room Type changes in Edit modal
      const editStatusEl = document.getElementById('editRoomStatus');
      const editOccupantTypeContainer = document.getElementById('editRoomOccupantTypeContainer');
      const editCatContainer = document.getElementById('editRoomCategoryContainer');
      const editCatSelect = document.getElementById('editRoomCategory');
      const editCatOtherContainer = document.getElementById('editRoomCategoryOtherContainer');
      const editCatOther = document.getElementById('editRoomCategoryOther');

      function toggleEditCategoryUI() {
        if (!editStatusEl) return;
        if (editStatusEl.value === 'inactive') {
          // Show category container, hide occupant type
          if (editCatContainer) editCatContainer.style.display = '';
          if (editOccupantTypeContainer) editOccupantTypeContainer.style.display = 'none';
        } else {
          // Show occupant type, hide category
          if (editCatContainer) editCatContainer.style.display = 'none';
          if (editOccupantTypeContainer) editOccupantTypeContainer.style.display = '';
          if (editCatSelect) editCatSelect.value = '';
          if (editCatOther) editCatOther.value = '';
          if (editCatOtherContainer) editCatOtherContainer.style.display = 'none';
        }
      }

      if (editStatusEl) {
        editStatusEl.addEventListener('change', toggleEditCategoryUI);
        // Initialize toggle on load
        toggleEditCategoryUI();
      }

      if (editCatSelect) {
        editCatSelect.addEventListener('change', function() {
          if (this.value === 'Others') {
            if (editCatOtherContainer) editCatOtherContainer.style.display = '';
          } else {
            if (editCatOtherContainer) editCatOtherContainer.style.display = 'none';
            if (editCatOther) editCatOther.value = '';
          }
        });
      }

      // Wire create modal category behavior (only visible when Room Type = inactive)
      const createStatusEl = document.getElementById('roomStatus');
      const createCatContainer = document.getElementById('roomCategory') ? document.getElementById('roomCategory').parentNode : null;
      const createCatSelect = document.getElementById('roomCategory');
      const createCatOtherContainer = document.getElementById('roomCategoryOtherContainer');
      const createCatOther = document.getElementById('roomCategoryOther');

      function toggleCreateCategoryUI() {
        if (!createStatusEl || !createCatContainer) return;
        if (createStatusEl.value === 'inactive') {
          createCatContainer.style.display = '';
        } else {
          createCatContainer.style.display = 'none';
          if (createCatSelect) createCatSelect.value = '';
          if (createCatOther) createCatOther.value = '';
          if (createCatOtherContainer) createCatOtherContainer.style.display = 'none';
        }
      }

      if (createStatusEl) createStatusEl.addEventListener('change', toggleCreateCategoryUI);
      if (createCatSelect) {
        createCatSelect.addEventListener('change', function() {
          if (this.value === 'Others') {
            if (createCatOtherContainer) createCatOtherContainer.style.display = '';
          } else {
            if (createCatOtherContainer) createCatOtherContainer.style.display = 'none';
            if (createCatOther) createCatOther.value = '';
          }
        });
      }

      // Wire add-room quick form category other toggle
      const addCatSelect = document.getElementById('addRoomCategory');
      const addCatOtherContainer = document.getElementById('addRoomCategoryOtherContainer');
      const addCatOther = document.getElementById('addRoomCategoryOther');
      if (addCatSelect) {
        addCatSelect.addEventListener('change', function() {
          if (this.value === 'Others') {
            if (addCatOtherContainer) addCatOtherContainer.style.display = '';
          } else {
            if (addCatOtherContainer) addCatOtherContainer.style.display = 'none';
            if (addCatOther) addCatOther.value = '';
          }
        });
      }

      // Setup assignment validation
      setupAssignmentValidation();

      // Setup capacity validation
      setupCapacityValidation();



      // Setup test sync button
      const testSyncBtn = document.getElementById('testSyncBtn');
      if (testSyncBtn) {
        testSyncBtn.addEventListener('click', function() {
          testRoomSync();
        });
      }
    });

    function openCreateRoomModal() {
      document.getElementById('createRoomForm').reset();
      createRoomModal.show();
    }

    function createRoom() {
      const form = document.getElementById('createRoomForm');
      const formData = new FormData(form);

      // If creating a Non-Student Room, ensure the selected category (or other) is saved into description
      try {
        const status = document.getElementById('roomStatus') ? document.getElementById('roomStatus').value : 'active';
        let descriptionVal = '';
        if (status === 'inactive') {
          const sel = document.getElementById('roomCategory');
          const other = document.getElementById('roomCategoryOther');
          if (sel && sel.value === 'Others') descriptionVal = other ? other.value.trim() : '';
          else if (sel) descriptionVal = sel.value || '';
        }
        // override/add description in FormData so backend receives the category text
        formData.set('description', descriptionVal);
      } catch (e) {
        // ignore
      }

      fetch('/room-management/rooms', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          createRoomModal.hide();
          showAlert('success', data.message);
          refreshRooms();
        } else {
          showAlert('error', data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while creating the room');
      });
    }

    function editRoom(roomId) {
      // Find room data from the DOM or fetch from server
      fetch(`/room-management/rooms/${roomId}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.room) {
            const room = data.room;
            document.getElementById('editRoomId').value = room.id;
            document.getElementById('editRoomNumber').value = room.room_number;
            const statusEl = document.getElementById('editRoomStatus');
            const occupantTypeEl = document.getElementById('editRoomOccupantType');
            const catContainer = document.getElementById('editRoomCategoryContainer');
            const catSelect = document.getElementById('editRoomCategory');
            const catOther = document.getElementById('editRoomCategoryOther');
            const occupantTypeContainer = document.getElementById('editRoomOccupantTypeContainer');
            const catOtherContainer = document.getElementById('editRoomCategoryOtherContainer');

            statusEl.value = room.status || 'active';

            // Populate occupant type with fallback if stored value missing but assignments indicate a single gender
            if (occupantTypeEl) {
              let occupantValue = room.occupant_type || 'both';
              if (!occupantValue || occupantValue === 'both') {
                if (room.male_assignments_count > 0 && room.female_assignments_count === 0) {
                  occupantValue = 'male';
                } else if (room.female_assignments_count > 0 && room.male_assignments_count === 0) {
                  occupantValue = 'female';
                }
              }
              occupantTypeEl.value = occupantValue;
            }

            // Initialize category UI based on current stored description
            const desc = room.description ? String(room.description).trim() : '';
            // If room is inactive (Non-Student Room) show category
            if (statusEl.value === 'inactive') {
              catContainer.style.display = '';
              if (occupantTypeContainer) occupantTypeContainer.style.display = 'none';
              // If the existing description matches one of the options, select it
              const foundOption = Array.from(catSelect.options).find(o => o.value === desc);
              if (foundOption) {
                catSelect.value = desc;
                catOther.value = '';
                document.getElementById('editRoomCategoryOtherContainer').style.display = 'none';
              } else if (desc && desc.length > 0) {
                // Use 'Others' and populate the other input
                catSelect.value = 'Others';
                catOther.value = desc;
                document.getElementById('editRoomCategoryOtherContainer').style.display = '';
              } else {
                catSelect.value = '';
                catOther.value = '';
                document.getElementById('editRoomCategoryOtherContainer').style.display = 'none';
              }
            } else {
              // Hide category UI for student rooms
              catContainer.style.display = 'none';
              if (occupantTypeContainer) occupantTypeContainer.style.display = '';
              catSelect.value = '';
              catOther.value = '';
              if (catOtherContainer) catOtherContainer.style.display = 'none';
            }

            editRoomModal.show();
          } else {
            showAlert('error', 'Failed to load room data');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('error', 'Failed to load room data');
        });
    }

    function updateRoom() {
      const roomId = document.getElementById('editRoomId').value;
      const statusVal = document.getElementById('editRoomStatus').value;
      // Default description is empty for student rooms
      let descriptionVal = '';
      if (statusVal === 'inactive') {
        const sel = document.getElementById('editRoomCategory');
        const other = document.getElementById('editRoomCategoryOther');
        if (sel && sel.value === 'Others') {
          descriptionVal = other ? other.value.trim() : '';
        } else if (sel) {
          descriptionVal = sel.value || '';
        }
      }

      const payload = {
        status: statusVal,
        description: descriptionVal
      };
      
      // Include occupant_type for student rooms
      if (statusVal === 'active') {
        const occupantTypeEl = document.getElementById('editRoomOccupantType');
        if (occupantTypeEl) {
          payload.occupant_type = occupantTypeEl.value;
        }
      }

      fetch(`/room-management/rooms/${roomId}`, {
        method: 'PUT',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(payload)
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          editRoomModal.hide();
          showAlert('success', data.message || 'Room updated');
          setTimeout(() => refreshRooms(), 600);
        } else {
          showAlert('error', data.message || 'Failed to update room');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while updating the room');
      });
    }

    function deleteRoom(roomId) {
      if (confirm('Are you sure you want to delete this room? This action cannot be undone.')) {
        fetch(`/room-management/rooms/${roomId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', data.message);
            refreshRooms();
          } else {
            showAlert('error', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('error', 'An error occurred while deleting the room');
        });
      }
    }

    function openAssignmentModal() {
      assignmentModal.show();
    }

    function setupAssignmentValidation() {
      const roomSelect = document.getElementById('assignmentRoom');
      const studentSelect = document.getElementById('assignmentStudent');
      const assignBtn = document.getElementById('assignBtn');
      const infoDiv = document.getElementById('assignmentInfo');

      function validateAssignment() {
        const room = roomSelect.value;
        const student = studentSelect.value;

        if (!room || !student) {
          assignBtn.disabled = true;
          infoDiv.style.display = 'none';
          return;
        }

        const roomOption = roomSelect.selectedOptions[0];
        const studentOption = studentSelect.selectedOptions[0];

        const capacity = parseInt(roomOption.dataset.capacity);
        const occupancy = parseInt(roomOption.dataset.occupancy);
        const studentGender = studentOption.dataset.gender;

        let message = '';
        let isValid = true;

        if (occupancy >= capacity) {
          message = 'Room is at full capacity.';
          isValid = false;
        }

        // Add gender validation logic here if needed

        if (isValid) {
          message = `Assigning ${studentOption.text} to Room ${room}. Occupancy will be ${occupancy + 1}/${capacity}.`;
          infoDiv.className = 'alert alert-success';
        } else {
          infoDiv.className = 'alert alert-danger';
        }

        infoDiv.textContent = message;
        infoDiv.style.display = 'block';
        assignBtn.disabled = !isValid;
      }

      roomSelect.addEventListener('change', validateAssignment);
      studentSelect.addEventListener('change', validateAssignment);
    }

    function assignStudent() {
      const roomNumber = document.getElementById('assignmentRoom').value;
      const studentId = document.getElementById('assignmentStudent').value;

      fetch('/room-management/assign-student', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: JSON.stringify({
          room_number: roomNumber,
          student_id: studentId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          assignmentModal.hide();
          showAlert('success', data.message);
          refreshRooms();
        } else {
          showAlert('error', data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while assigning the student');
      });
    }

    function manageTasks(roomNumber) {
      document.getElementById('taskRoomTitle').textContent = `Room ${roomNumber} Tasks`;
      document.getElementById('taskRoomNumber').value = roomNumber;
      // Load tasks for the room
      loadRoomTasks(roomNumber);
      taskModal.show();
    }

    function loadRoomTasks(roomNumber) {
      fetch(`/room-management/rooms/${roomNumber}/tasks`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            displayTasks(data.tasks);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('error', 'Failed to load room tasks');
        });
    }

    function displayTasks(tasks) {
      const tasksList = document.getElementById('tasksList');
      if (tasks.length === 0) {
        tasksList.innerHTML = '<p class="text-muted">No tasks found for this room.</p>';
        return;
      }

  let html = '<div class="table-responsive"><table class="table table-striped"><thead><tr><th>Day</th><th>Area</th><th>Description</th><th>Status</th><th>Actions</th></tr></thead><tbody>';

      tasks.forEach(task => {
        html += `
          <tr>
            <td>${task.day}</td>
            <td>${task.area}</td>
            <td>${task.desc}</td>
            <td><span class="badge bg-${task.status === 'completed' ? 'success' : task.status === 'in progress' ? 'warning' : 'secondary'}">${task.status}</span></td>
            <td>
              <!-- Edit removed per request; only allow delete for tasks in this view -->
              <button class="btn btn-sm btn-outline-danger" onclick="deleteTask(${task.id})">Remove</button>
            </td>
          </tr>
        `;
      });

      html += '</tbody></table></div>';
      tasksList.innerHTML = html;
    }

    function openCreateTaskModal() {
      document.getElementById('createTaskForm').reset();
      const createTaskModal = new bootstrap.Modal(document.getElementById('createTaskModal'));
      createTaskModal.show();
    }

    function createTask() {
      const form = document.getElementById('createTaskForm');
      const formData = new FormData(form);

      fetch('/room-management/tasks', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const createTaskModal = bootstrap.Modal.getInstance(document.getElementById('createTaskModal'));
          createTaskModal.hide();
          showAlert('success', data.message);
          // Reload tasks for the current room
          const roomNumber = document.getElementById('taskRoomNumber').value;
          loadRoomTasks(roomNumber);
        } else {
          showAlert('error', data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'An error occurred while creating the task');
      });
    }

    function editTask(taskId) {
      // Implementation for editing tasks
      showAlert('info', 'Edit task functionality will be implemented');
    }

    function deleteTask(taskId) {
      if (confirm('Are you sure you want to delete this task?')) {
        fetch(`/room-management/tasks/${taskId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        })
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            showAlert('success', data.message);
            // Reload tasks for the current room
            const roomNumber = document.getElementById('taskRoomNumber').value;
            loadRoomTasks(roomNumber);
          } else {
            showAlert('error', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showAlert('error', 'An error occurred while deleting the task');
        });
      }
    }

    async function refreshRooms() {
      try {
        // Show loading state
        const refreshBtn = document.querySelector('button[onclick="refreshRooms()"]');
        const originalText = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i class="fi fi-rr-spinner"></i> Syncing...';
        refreshBtn.disabled = true;

        // Sync with dashboard data first
        const syncResponse = await fetch('/room-management/sync-dashboard', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
          }
        });

        const syncData = await syncResponse.json();

        if (syncData.success) {
          showAlert('success', 'Successfully synced with dashboard data');
          // Wait a moment then reload
          setTimeout(() => {
            location.reload();
          }, 1000);
        } else {
          showAlert('warning', 'Sync completed with some issues, refreshing page...');
          setTimeout(() => {
            location.reload();
          }, 1500);
        }

      } catch (error) {
        console.error('Error syncing with dashboard:', error);
        showAlert('info', 'Refreshing page...');
        setTimeout(() => {
          location.reload();
        }, 1000);
      }
    }

    function toggleOccupantsList(roomId) {
      const occupantsList = document.getElementById(`occupants-${roomId}`);
      const toggleIcon = document.getElementById(`arrow-${roomId}`);

      if (occupantsList.style.display === 'none') {
        occupantsList.style.display = 'block';
        toggleIcon.classList.add('rotated');
      } else {
        occupantsList.style.display = 'none';
        toggleIcon.classList.remove('rotated');
      }
    }

    function moveStudentUp(assignmentId) {
      // Implementation for moving student up in the list
      console.log('Move student up:', assignmentId);
      // You can implement the actual functionality here
    }

    function moveStudentDown(assignmentId) {
      // Implementation for moving student down in the list
      console.log('Move student down:', assignmentId);
      // You can implement the actual functionality here
    }

    function showAlert(type, message) {
      const alertDiv = document.createElement('div');
      alertDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show`;
      alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      `;

      document.querySelector('.room-management-container').insertBefore(alertDiv, document.querySelector('.stats-overview'));

      setTimeout(() => {
        alertDiv.remove();
      }, 5000);
    }

    // Enhanced Room Management Functions
    function openAddRoomModal() {
      document.getElementById('addRoomForm').reset();
      addRoomModal.show();
    }

    async function addRoomWithSync() {
      const form = document.getElementById('addRoomForm');
      const formData = new FormData(form);

      // Validate room number format
      const roomNumber = formData.get('room_number');
      if (!roomNumber || !/^[2-9][0-9]{2}$/.test(roomNumber)) {
        showAlert('error', 'Room number must be 3 digits starting with 2-9 (e.g., 201, 302, 405)');
        return;
      }

      // Validate capacity
      const totalCapacity = parseInt(formData.get('capacity')) || 6;
      const maleCapacity = parseInt(formData.get('male_capacity')) || 0;
      const femaleCapacity = parseInt(formData.get('female_capacity')) || 0;

      if ((maleCapacity + femaleCapacity) > totalCapacity) {
        showAlert('error', 'Male + Female capacity cannot exceed total capacity');
        return;
      }

      try {
        // Ensure category from add form is sent as description
        try {
          const sel = document.getElementById('addRoomCategory');
          const other = document.getElementById('addRoomCategoryOther');
          let desc = '';
          if (sel) {
            if (sel.value === 'Others') desc = other ? other.value.trim() : '';
            else desc = sel.value || '';
            formData.set('description', desc);
          }
        } catch(e) {}
        const response = await fetch('/room-management/add-room-sync', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          },
          body: formData
        });

        const data = await response.json();

        if (data.success) {
          addRoomModal.hide();
          showAlert('success', `Room ${roomNumber} created successfully and synced with dashboard!`);

          // Refresh the room list
          setTimeout(() => {
            refreshRooms();
          }, 1000);
        } else {
          showAlert('error', data.message || 'Failed to create room');
        }
      } catch (error) {
        console.error('Error creating room:', error);
        showAlert('error', 'An error occurred while creating the room');
      }
    }

    async function deleteRoomWithSync(roomId, roomNumber) {
      try {
        // First attempt - check if room has students/tasks
        const response = await fetch(`/room-management/delete-room-sync/${roomId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
          }
        });

        const data = await response.json();

        if (data.success) {
          showAlert('success', `Room ${roomNumber} deleted successfully and synced with dashboard!`);

          // Refresh the room list
          setTimeout(() => {
            refreshRooms();
          }, 1000);
        } else if (data.requires_confirmation) {
          // Room has students/tasks - ask for confirmation
          const confirmMessage = `Room ${roomNumber} contains:\n• ${data.student_count} assigned students\n• ${data.task_count} tasks\n\nThis will permanently delete all room data. Continue?`;

          if (confirm(confirmMessage)) {
            // Force delete with all data
            const forceResponse = await fetch(`/room-management/delete-room-sync/${roomId}?force_delete=true`, {
              method: 'DELETE',
              headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
              }
            });

            const forceData = await forceResponse.json();

            if (forceData.success) {
              showAlert('success', `Room ${roomNumber} and all associated data deleted successfully!`);

              // Refresh the room list
              setTimeout(() => {
                refreshRooms();
              }, 1000);
            } else {
              showAlert('error', forceData.message || 'Failed to delete room');
            }
          }
        } else {
          showAlert('error', data.message || 'Failed to delete room');
        }
      } catch (error) {
        console.error('Error deleting room:', error);
        showAlert('error', 'An error occurred while deleting the room');
      }
    }

    function setupCapacityValidation() {
      // Create capacity validation for both create and edit forms
      function validateCapacity(totalCapacityId, maleCapacityId, femaleCapacityId) {
        const totalCapacity = document.getElementById(totalCapacityId);
        const maleCapacity = document.getElementById(maleCapacityId);
        const femaleCapacity = document.getElementById(femaleCapacityId);

        function validate() {
          const total = parseInt(totalCapacity.value) || 0;
          const male = parseInt(maleCapacity.value) || 0;
          const female = parseInt(femaleCapacity.value) || 0;
          const genderTotal = male + female;

          // Remove previous error styling
          [maleCapacity, femaleCapacity].forEach(input => {
            input.style.borderColor = '';
            input.style.backgroundColor = '';
          });

          if (genderTotal > total) {
            // Add error styling
            [maleCapacity, femaleCapacity].forEach(input => {
              input.style.borderColor = '#dc3545';
              input.style.backgroundColor = '#fff5f5';
            });

            // Show error message
            let errorDiv = document.getElementById('capacity-error-' + totalCapacityId);
            if (!errorDiv) {
              errorDiv = document.createElement('div');
              errorDiv.id = 'capacity-error-' + totalCapacityId;
              errorDiv.style.color = '#dc3545';
              errorDiv.style.fontSize = '0.875rem';
              errorDiv.style.marginTop = '5px';
              femaleCapacity.parentNode.appendChild(errorDiv);
            }
            errorDiv.textContent = `Gender total (${genderTotal}) cannot exceed room capacity (${total})`;
            return false;
          } else {
            // Remove error message
            const errorDiv = document.getElementById('capacity-error-' + totalCapacityId);
            if (errorDiv) {
              errorDiv.remove();
            }
            return true;
          }
        }

        // Add event listeners
        [totalCapacity, maleCapacity, femaleCapacity].forEach(input => {
          input.addEventListener('input', validate);
          input.addEventListener('change', validate);
        });
      }

      // Setup validation for create form
      if (document.getElementById('roomCapacity')) {
        validateCapacity('roomCapacity', 'maleCapacity', 'femaleCapacity');
      }

      // Setup validation for edit form
      if (document.getElementById('editRoomCapacity')) {
        validateCapacity('editRoomCapacity', 'editMaleCapacity', 'editFemaleCapacity');
      }

      // Setup validation for add room form
      if (document.getElementById('addRoomCapacity')) {
        validateCapacity('addRoomCapacity', 'addMaleCapacity', 'addFemaleCapacity');
      }
    }





    // Function to trigger dashboard synchronization
    async function triggerDashboardSync() {
      try {
        console.log('Triggering dashboard synchronization...');

        // Try to communicate with dashboard if it's open in another tab
        if (window.opener && !window.opener.closed) {
          // If this window was opened from dashboard, notify the parent
          window.opener.postMessage({
            type: 'ROOM_ASSIGNMENTS_UPDATED',
            timestamp: new Date().toISOString()
          }, window.location.origin);
        }

        // Use localStorage to communicate between tabs
        localStorage.setItem('roomAssignmentsUpdated', JSON.stringify({
          timestamp: new Date().toISOString(),
          trigger: 'room_management_reassignment'
        }));

        // Remove the flag after a short delay to prevent multiple triggers
        setTimeout(() => {
          localStorage.removeItem('roomAssignmentsUpdated');
        }, 1000);

        console.log('Dashboard sync notification sent');
      } catch (error) {
        console.error('Error triggering dashboard sync:', error);
      }
    }

    // Function to trigger immediate dashboard refresh with updated room data
    async function triggerDashboardRefresh(roomStudents, capacity) {
      try {
        console.log('Triggering immediate dashboard refresh with updated data...');

        // Send detailed update message to dashboard
        const updateData = {
          type: 'ROOM_ASSIGNMENTS_UPDATED_WITH_DATA',
          timestamp: new Date().toISOString(),
          roomStudents: roomStudents,
          capacity: capacity,
          trigger: 'room_management_reassignment'
        };

        // Try to communicate with dashboard if it's open in another tab
        if (window.opener && !window.opener.closed) {
          window.opener.postMessage(updateData, window.location.origin);
        }

        // Use localStorage to communicate between tabs with detailed data
        localStorage.setItem('roomAssignmentsUpdatedWithData', JSON.stringify(updateData));

        // Clear the localStorage item after a short delay to prevent stale data
        setTimeout(() => {
          localStorage.removeItem('roomAssignmentsUpdatedWithData');
        }, 5000);

        console.log('Dashboard refresh triggered with updated room data');
      } catch (error) {
        console.error('Error triggering dashboard refresh:', error);
      }
    }

    // Test sync function
    async function testRoomSync() {
      try {
        console.log('Testing room sync...');

        // First, check current state
        const testResponse = await fetch('/test-room-sync');
        const testData = await testResponse.json();

        console.log('Current state:', testData);

        // Show current state in alert
        alert(`Current State:
Dashboard Room Students: ${Object.keys(testData.dashboard_room_students).length} rooms
Room Assignments in DB: ${testData.room_assignments_count}
Rooms with Assignments: ${testData.rooms_with_assignments}/${testData.rooms_count}

Check console for detailed data.`);

        // Trigger manual sync
        const syncResponse = await fetch('/room-management/sync-dashboard', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Content-Type': 'application/json'
          }
        });

        const syncData = await syncResponse.json();
        console.log('Sync result:', syncData);

        if (syncData.success) {
          showAlert('success', 'Sync completed successfully! Refreshing page...');
          setTimeout(() => {
            window.location.reload();
          }, 2000);
        } else {
          showAlert('error', 'Sync failed: ' + syncData.message);
        }

      } catch (error) {
        console.error('Test sync error:', error);
        showAlert('error', 'Test sync failed: ' + error.message);
      }
    }


  </script>

  <!-- Logout form for sidebar -->
  <script>
    // Helpers used by Quick Actions (small subset of Dashboard helpers)
    function closeModal(modal) {
      try {
        if (!modal) return;
        if (modal instanceof Element) {
          const bs = bootstrap.Modal.getInstance(modal) || new bootstrap.Modal(modal);
          bs.hide();
        }
      } catch (e) { console.warn('closeModal error', e); }
    }

    function showLoader(message = 'Please wait...') {
      try {
        let overlay = document.getElementById('rm-simpleLoaderOverlay');
        if (!overlay) {
          overlay = document.createElement('div');
          overlay.id = 'rm-simpleLoaderOverlay';
          overlay.style.cssText = 'position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.35);display:flex;align-items:center;justify-content:center;z-index:12000;';
          overlay.innerHTML = `<div style="background:white;padding:18px 22px;border-radius:8px;box-shadow:0 6px 18px rgba(0,0,0,0.2);max-width:320px; text-align:center;"><div style="font-size:18px;margin-bottom:8px;">${message}</div><div style="font-size:12px;color:#666;">Processing...</div></div>`;
          document.body.appendChild(overlay);
        } else {
          overlay.style.display = 'flex';
          overlay.querySelector('div').firstChild.textContent = message;
        }
      } catch(e){ console.warn('showLoader failed', e); }
    }

    function hideLoader() {
      try { const overlay = document.getElementById('rm-simpleLoaderOverlay'); if (overlay) overlay.style.display = 'none'; } catch(e){}
    }

    function showResult(type = 'success', message = '', timeout = 2500) {
      try {
        // Map to showAlert for room-management
        if (type === 'success') showAlert('success', message);
        else if (type === 'error' || type === 'danger') showAlert('error', message);
        else if (type === 'warning') showAlert('warning', message);
        else showAlert('info', message);
        if (timeout && timeout > 0) setTimeout(() => {}, timeout);
      } catch(e){ console.warn('showResult failed', e); }
    }

    // Dashboard-equivalent handlers for Quick Actions
    async function handleNewRoomFormSubmit(event) {
      event = event || window.event;
      try {
        if (event && event.preventDefault) event.preventDefault();
        const roomNumber = document.getElementById('newRoomNumber').value;
        const roomStatus = document.getElementById('newRoomStatus').value || 'active';
        const occupantType = document.getElementById('newRoomOccupantType').value || 'both';
        
        if (!roomNumber) { alert('Please enter a room number.'); return; }

        const existingRoom = document.querySelector(`[data-room-number="${roomNumber}"]`);
        if (existingRoom) { alert('This room already exists.'); return; }

        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        try { showLoader('Creating room...'); } catch(e){}

        const payload = { room_number: roomNumber, capacity: 6, status: roomStatus };
        // Only include occupant_type for student rooms
        if (roomStatus === 'active') {
          payload.occupant_type = occupantType;
        }

        const res = await fetch('/room-management/rooms', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrf
          },
          credentials: 'same-origin',
          body: JSON.stringify(payload)
        });

        const text = await res.text().catch(()=>'');
        let json = {};
        try { json = text ? JSON.parse(text) : {}; } catch(e) { json = { __raw: text }; }

        if (!res.ok) {
          const msg = json && json.message ? json.message : `Failed to create room (HTTP ${res.status})`;
          try { hideLoader(); } catch(e){}
          alert(msg);
          return;
        }

        // Refresh via refreshRooms to keep behavior consistent
        try { hideLoader(); } catch(e){}
        closeModal(document.getElementById('newRoomModal'));
        showResult('success', `Created room ${roomNumber}`);
        if (typeof refreshRooms === 'function') refreshRooms(); else location.reload();
      } catch (error) {
        console.error('Error creating room:', error);
        try { hideLoader(); } catch(e){}
        alert('Failed to create room. Please try again.');
      }
    }

    async function handleAddFloorFormSubmit(event) {
      event = event || window.event;
      if (event && event.preventDefault) event.preventDefault();
      try {
        const form = document.getElementById('addFloorForm');
        const floorNumber = parseInt(form.newFloorNumber.value, 10);
        const rawRooms = (form.studentRooms && form.studentRooms.value) ? form.studentRooms.value.trim() : '';
        const numRooms = (form.numRooms && form.numRooms.value) ? parseInt(form.numRooms.value, 10) : null;

        if (!floorNumber || (!rawRooms && !numRooms)) { alert('Please fill in the floor number and either provide a number of rooms or explicit room numbers.'); return; }
        if (floorNumber < 2 || floorNumber > 99) { alert('Please provide a valid floor number (2-99).'); return; }

        let parsedRooms = [];
        const invalid = [];
        if (rawRooms) {
          const tokens = rawRooms.split(/[,\s]+/).map(t => t.trim()).filter(Boolean);
          tokens.forEach(tok => {
            if (/^[0-9]{3,6}$/.test(tok)) parsedRooms.push(tok);
            else if (/^[0-9]+$/.test(tok)) parsedRooms.push(tok);
            else invalid.push(tok);
          });
          if (invalid.length > 0) { alert('Invalid room numbers detected: ' + invalid.join(', ')); return; }
        } else if (numRooms && Number.isInteger(numRooms) && numRooms > 0) {
          for (let i=1;i<=numRooms;i++) parsedRooms.push(String((floorNumber*100)+i));
        }

        const deduped = [...new Set(parsedRooms)];
        const roomsToCreate = deduped.filter(rn => {
          for (const el of document.querySelectorAll('.room-card-design .room-number-design')) {
            const m = (el.textContent||'').match(/(\d+)/);
            if (m && m[1] && String(m[1]) === String(rn)) return false;
          }
          return true;
        });

        if (roomsToCreate.length === 0) { closeModal(document.getElementById('addFloorModal')); alert('No new rooms to create (all provided rooms already exist).'); return; }

        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const createResults = [];
        try { showLoader('Creating rooms...'); } catch(e){}

        for (const rn of roomsToCreate) {
          try {
            const res = await fetch('/room-management/rooms', {
              method: 'POST',
              headers: { 'Content-Type':'application/json','Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf },
              credentials: 'same-origin',
              body: JSON.stringify({ room_number: rn, capacity: 6, status: 'active' })
            });
            const text = await res.text().catch(()=>'');
            let json = {};
            try { json = text ? JSON.parse(text) : {}; } catch(e){ json = { __raw: text }; }
            createResults.push({ room: rn, ok: res.ok, status: res.status, body: json });
          } catch (err) { createResults.push({ room: rn, ok: false, error: String(err) }); }
        }

        const failures = createResults.filter(r => !r.ok);
        const successes = createResults.filter(r => r.ok).map(r => r.room);

        // Close modal and show result
        closeModal(document.getElementById('addFloorModal'));
        try { hideLoader(); } catch(e){}

        if (failures.length > 0) {
          const failureDetails = failures.map(f => {
            if (f.error) return `${f.room} — ${f.error}`;
            if (f.body && f.body.message) return `${f.room} — ${f.body.message}`;
            if (f.status) return `${f.room} — HTTP ${f.status}`;
            return `${f.room} — Unknown error`;
          });
          showResult('error', `Failed to create: ${failureDetails.join(' | ')}`, 7000);
        } else {
          showResult('success', `Created rooms: ${successes.join(', ')}`, 3500);
        }

        // Refresh to reflect new rooms
        if (typeof refreshRooms === 'function') refreshRooms(); else location.reload();
      } catch (err) {
        console.error('Error in handleAddFloorFormSubmit:', err);
        try { hideLoader(); } catch(e){}
        alert('Failed to create rooms.');
      }
    }

    async function handleDeleteFloorFormSubmit(event) {
      event = event || window.event; if (event && event.preventDefault) event.preventDefault();
      try {
        const floorToDelete = document.getElementById('floorToDelete').value;
        if (!floorToDelete) return; 
        
        // Get the display text of the selected option to show in the confirmation
        const floorDisplayText = document.getElementById('floorToDelete').options[document.getElementById('floorToDelete').selectedIndex].text;
        if (!confirm(`Are you sure you want to delete ${floorDisplayText}? This action cannot be undone.`)) return;

        // Gather rooms on floor
        const roomsOnFloor = [];
        document.querySelectorAll('.room-card-design .room-number-design').forEach(el=>{
          const m = (el.textContent||'').match(/(\d+)/);
          if (m && m[1]) {
            const num = m[1];
            const f = num.length >= 4 ? num.substring(0,2) : num.charAt(0);
            if (String(f) === String(floorToDelete)) roomsOnFloor.push(num);
          }
        });

        if (roomsOnFloor.length === 0) {
          closeModal(document.getElementById('deleteFloorModal'));
          showResult('success', `${floorDisplayText} has been removed (no rooms present).`);
          if (typeof refreshRooms === 'function') refreshRooms(); else location.reload();
          return;
        }

        try { showLoader('Deleting floor...'); } catch(e){}
        const csrf = document.querySelector('meta[name="csrf-token"]').content;
        const deletePromises = roomsOnFloor.map(roomNumber => {
          const url = `/api/dashboard/delete-room/${encodeURIComponent(roomNumber)}`;
          return fetch(url, {
            method: 'DELETE',
            headers: { 'Accept':'application/json','X-Requested-With':'XMLHttpRequest','X-CSRF-TOKEN':csrf },
            credentials: 'same-origin'
          }).then(async res => {
            const text = await res.text().catch(()=>'');
            let json = {};
            try { json = text ? JSON.parse(text) : {}; } catch(e){ json = { __raw: text }; }
            return { room: roomNumber, ok: res.ok, status: res.status, body: json };
          }).catch(err => ({ room: roomNumber, ok: false, error: err }));
        });

        const results = await Promise.all(deletePromises);
        const failed = results.filter(r => !r.ok);
        closeModal(document.getElementById('deleteFloorModal'));
        try { hideLoader(); } catch(e){}
        if (failed.length > 0) {
          const failedRooms = failed.map(f => `${f.room} (${f.status || 'network'})`).join(', ');
          showResult('error', `Failed to delete some rooms on ${floorDisplayText}: ${failedRooms}`, 7000);
          if (typeof refreshRooms === 'function') refreshRooms(); else location.reload();
          return;
        }

        showResult('success', `${floorDisplayText} and its rooms have been deleted permanently.`, 3500);
        if (typeof refreshRooms === 'function') refreshRooms(); else location.reload();
      } catch (err) {
        console.error('Error deleting floor:', err);
        try { hideLoader(); } catch(e){}
        alert('Failed to delete floor.');
      }
    }
  </script>

  <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
  </form>
</body>
</html>
