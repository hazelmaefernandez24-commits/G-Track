<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Student Room Tasking</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-bold-rounded/css/uicons-bold-rounded.css">
  <link rel="stylesheet" href="https://cdn-uicons.flaticon.com/2.6.0/uicons-regular-rounded/css/uicons-regular-rounded.css">
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
    }

    .page-header {
      background: linear-gradient(135deg, #ff9000 0%, #ff7b00 100%);
      color: white;
      padding: 30px;
      border-radius: 15px;
      margin-bottom: 30px;
      text-align: center;
    }

    .page-header h1 {
      margin: 0;
      font-size: 2.5rem;
      font-weight: 600;
    }

    .page-header p {
      margin: 10px 0 0 0;
      font-size: 1.1rem;
      opacity: 0.9;
    }

    .cards-container {
      display: flex;
      gap: 30px;
      margin-bottom: 30px;
    }

    .card {
      background: white;
      border-radius: 15px;
      padding: 30px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
      flex: 1;
    }

    .card:hover {
      transform: translateY(-5px);
    }

    .card h2 {
      color: #2c3e50;
      margin-bottom: 15px;
      font-size: 1.8rem;
    }

    .card p {
      color: #7f8c8d;
      margin-bottom: 20px;
      line-height: 1.6;
    }

    .student-badge {
      margin-top: 10px;
    }

    .student-badge .badge {
      background:  #4facfe;
      color: white;
      padding: 8px 16px;
      border-radius: 20px;
      font-size: 0.9rem;
      font-weight: 600;
      display: inline-block;
    }

    .card-button {
      background: #4facfe;
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      text-decoration: none;
      display: inline-block;
    }

    .card-button:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
      color: white;
    }

    .floor-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 20px;
    }

    .floor-btn {
      background:rgb(255, 102, 13);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 20px;
      cursor: pointer;
      transition: all 0.3s ease;
      font-weight: 500;
      box-shadow: 0 3px 10px rgba(79, 172, 254, 0.3);
    }

    .floor-btn:hover {
      background:rgb(255, 110, 13);
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
    }

    .hidden {
      display: none !important;
    }

    .rooms-container {
      margin-top: 30px;
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
    }

    .room-box {
      background: white;
      border-radius: 15px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      transition: transform 0.3s ease;
    }

    .room-box:hover {
      transform: translateY(-3px);
    }

    .room-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
    }

    .room-number {
      font-size: 1.5rem;
      font-weight: 600;
      color: #2c3e50;
    }

    .room-status {
      padding: 5px 12px;
      border-radius: 15px;
      font-size: 0.8rem;
      font-weight: 600;
    }

    .status-completed {
      background: #d4edda;
      color: #155724;
    }

    .status-pending {
      background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
      color: #1565c0;
      border: 1px solid #4facfe;
    }

    .status-not-started {
      background: #f8d7da;
      color: #721c24;
    }

    .student-list {
      margin: 15px 0;
    }

    .student-item {
      background: #f8f9fa;
      padding: 8px 12px;
      margin: 5px 0;
      border-radius: 8px;
      font-size: 0.9rem;
    }

    .room-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: 15px;
    }

    .continue-btn {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
      padding: 10px 20px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      transition: all 0.3s ease;
      box-shadow: 0 3px 10px rgba(79, 172, 254, 0.3);
      display: inline-flex;
      align-items: center;
      gap: 5px;
    }

    .continue-btn:hover {
      background: linear-gradient(135deg, #00f2fe 0%, #4facfe 100%);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
      text-decoration: none;
    }

    .student-readonly-note {
      background: #e3f2fd;
      color: #1565c0;
      padding: 8px 12px;
      border-radius: 8px;
      font-size: 0.85rem;
      text-align: center;
      margin-top: 10px;
    }

    .room-content {
      margin: 15px 0;
    }

    .room-content h4 {
      display: flex;
      align-items: center;
      margin-bottom: 10px;
      font-size: 1rem;
      color: #2c3e50;
    }

    .room-content .icon {
      width: 16px;
      height: 16px;
      margin-right: 8px;
    }

    .occupants-list {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .occupants-list li {
      background: #f8f9fa;
      padding: 6px 10px;
      margin: 3px 0;
      border-radius: 6px;
      font-size: 0.9rem;
      color: #495057;
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
      <ul class="nav flex-column">
        <li class="nav-item">
          <a href="{{ route('mainstudentdash') }}" class="nav-link">
            <img src="{{ asset('images/dashboard.png')}}" class="sidebar-icon">Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('student.general.task') }}" class="nav-link">
            <img src="{{ asset('images/assign.png')}}" class="sidebar-icon">General Tasks
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('task.history') }}" class="nav-link active">
            <img src="{{ asset('images/history.png')}}" class="sidebar-icon">Room Task History
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('reports.index') }}" class="nav-link">
            <img src="{{ asset('images/complaint.png')}}" class="sidebar-icon">Reports
          </a>
        </li>
      </ul>
    </nav>

    <!-- Main Content -->
    <div class="content">
      <div class="page-header">
        <h1><i class="fi fi-br-home"></i> Student Room Task History</h1>
        <p>View your room assignments, checklists, and task history. You can view but not edit assignments.</p>
        <div class="student-badge">
          <span class="badge"><i class="fi fi-rr-graduation-cap"></i> Student View</span>
        </div>
      </div>

      <div class="cards-container">
        <div class="card" style="flex: 1;">
          <h2><i class="fi fi-br-clipboard-list"></i> View Room Assignments</h2>
          <p>Browse through different floors to view your room assignments, task checklists, and completion history. Select a floor below to see available rooms.</p>
          <button id="toggleFloorsBtn" class="card-button"><i class="fi fi-br-building"></i> Select Floor (2nd - 7th floors)</button>
          <div id="floorButtons" class="floor-buttons hidden">
            <button class="floor-btn" data-floor="2"><i class="fi fi-br-building"></i> 2nd Floor</button>
            <button class="floor-btn" data-floor="3"><i class="fi fi-br-building"></i> 3rd Floor</button>
            <button class="floor-btn" data-floor="4"><i class="fi fi-br-building"></i> 4th Floor</button>
            <button class="floor-btn" data-floor="5"><i class="fi fi-br-building"></i> 5th Floor</button>
            <button class="floor-btn" data-floor="6"><i class="fi fi-br-building"></i> 6th Floor</button>
            <button class="floor-btn" data-floor="7"><i class="fi fi-br-building"></i> 7th Floor</button>
          </div>
        </div>
      </div>
      <div id="roomsContainer" class="rooms-container hidden"></div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Get the floor data from the server (same as admin dashboard)
    const floorData = @json($floorData ?? []);

    // Floor and room functionality (same as admin but read-only)
    document.getElementById('toggleFloorsBtn').addEventListener('click', function() {
      const floorButtons = document.getElementById('floorButtons');
      floorButtons.classList.toggle('hidden');
      
      if (floorButtons.classList.contains('hidden')) {
        this.textContent = '🏢 Select Floor (2nd - 7th floors)';
        document.getElementById('roomsContainer').classList.add('hidden');
      } else {
        this.textContent = '🔼 Hide floors';
      }
    });

    // Floor button click handlers
    document.querySelectorAll('.floor-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        const floor = this.dataset.floor;
        loadRoomsForFloor(floor);
      });
    });

    function loadRoomsForFloor(floor) {
      const roomsContainer = document.getElementById('roomsContainer');
      roomsContainer.innerHTML = '';
      roomsContainer.classList.remove('hidden');

      // Check if floor data exists
      if (floorData[floor]) {
        let roomsWithStudents = 0;

        // Only show rooms that have students assigned
        floorData[floor].rooms.forEach(roomNumber => {
          // Check if this room has students
          if (floorData[floor].students[roomNumber] && floorData[floor].students[roomNumber].length > 0) {
            const roomBox = createRoomBox(roomNumber, floor);
            roomsContainer.appendChild(roomBox);
            roomsWithStudents++;
          }
        });

        // Show message if no rooms have students
        if (roomsWithStudents === 0) {
          roomsContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: #666;">No rooms with assigned students on this floor.</div>';
        }
      } else {
        roomsContainer.innerHTML = '<div style="text-align: center; padding: 20px; color: #666;">No data available for this floor.</div>';
      }
    }

    function createRoomBox(roomNumber, floor) {
      const roomBox = document.createElement('div');
      roomBox.className = 'room-box';
      roomBox.setAttribute('data-room-number', roomNumber);

      // Get actual student data from the floor data (same as admin dashboard)
      const students = floorData[floor].students[roomNumber] || [];

      roomBox.innerHTML = `
        <div class="room-header">
          <h3><i class="fi fi-br-home"></i> Room ${roomNumber}</h3>
          <div class="room-status status-pending"><i class="fi fi-rr-graduation-cap"></i> Student View</div>
        </div>
        <div class="room-content">
          <h4>
            <i class="fi fi-br-users-alt"></i> Occupants (${students.length})
          </h4>
          <ul class="occupants-list">
            ${students.map(student => `<li><i class="fi fi-rr-user"></i> ${student}</li>`).join('')}
          </ul>
        </div>
        <div class="student-readonly-note">
          <i class="fi fi-rr-eye"></i> Student View: You can view task assignments and history but cannot edit
        </div>
        <div class="room-footer">
          <div></div>
          <a href="/roomtask/${roomNumber}?student_view=1" class="continue-btn">
            <i class="fi fi-br-clipboard-list"></i> View Task Checklist
          </a>
        </div>
      `;
      return roomBox;
    }
  </script>
</body>
</html>
