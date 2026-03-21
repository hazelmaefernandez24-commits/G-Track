<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>General Task Assignment Dashboard</title>
  <!-- External CSS -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

  <style>
    /* ===== BASE STYLES ===== */
    body {
      background: #eef2f7;
      font-family: "Poppins", sans-serif;
      margin: 0;
      padding-top: 60px;
      overflow-x: hidden;
    }

    /* Header layout */
    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 60px;
      background: linear-gradient(135deg, #22BBEA 0%, #1e90ff 100%);
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 20px;
      z-index: 1000;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .logo img {
      height: 40px;
    }

    .header-logout-btn {
      background: rgba(255,255,255,0.2);
      border: none;
      color: white;
      padding: 8px 12px;
      border-radius: 6px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .header-logout-btn:hover {
      background: rgba(255,255,255,0.3);
    }

    /* Sidebar */
    .sidebar {
      position: fixed;
      left: 0;
      top: 60px;
      width: 250px;
      height: calc(100vh - 60px);
      background: white;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      padding: 20px 0;
      z-index: 999;
    }

    .sidebar .nav-link {
      color: #333;
      padding: 12px 20px;
      display: flex;
      align-items: center;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
      background: #f8f9fa;
      color: #007bff;
    }

    .sidebar-icon {
      width: 20px;
      height: 20px;
      margin-right: 10px;
    }

    /* Main content */
    .main-content {
      margin-left: 250px;
      padding: 20px;
      min-height: calc(100vh - 60px);
    }

    /* Back link */
    .back-link {
      display: inline-flex;
      align-items: center;
      color: #007bff;
      text-decoration: none;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .back-link:hover {
      color: #0056b3;
    }

    .back-link svg {
      margin-right: 8px;
    }

    /* Enhanced header section */
    .enhanced-header-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 15px;
      padding: 30px;
      margin-bottom: 30px;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .enhanced-page-title {
      font-size: 2rem;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .enhanced-page-subtitle {
      font-size: 1.1rem;
      opacity: 0.9;
      margin: 0;
    }

    .header-decoration {
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255,255,255,0.1);
      border-radius: 50%;
    }

    /* No assignments */
    .no-assignments {
      text-align: center;
      padding: 60px 20px;
      color: #666;
    }

    /* Task assignment cards */
    .task-assignment-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
      height: 100%;
      min-height: 280px;
    }

    .task-assignment-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    .category-label {
      font-size: 1.1rem;
      font-weight: 600;
      color: #333;
      margin-bottom: 15px;
      text-align: center;
    }

    .badge-male {
      background: #007bff;
      color: white;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 0.8rem;
    }

    .badge-female {
      background: #e91e63;
      color: white;
      padding: 5px 10px;
      border-radius: 15px;
      font-size: 0.8rem;
    }

    .coordinator-section {
      margin: 15px 0;
      font-size: 0.9rem;
    }

    .description-section {
      margin: 15px 0;
      font-size: 0.85rem;
      color: #666;
      font-style: italic;
    }

    /* Card color variations */
    .kitchen-operations-card {
      border-left: 5px solid #ff6b6b;
    }

    .kitchen-dishwashing-card {
      border-left: 5px solid #4ecdc4;
    }

    .kitchen-dining-card {
      border-left: 5px solid #45b7d1;
    }

    .offices-card {
      border-left: 5px solid #96ceb4;
    }

    .conference-card {
      border-left: 5px solid #feca57;
    }

    .ground-floor-card {
      border-left: 5px solid #ff9ff3;
    }

    .rooftop-waste-card {
      border-left: 5px solid #54a0ff;
    }

    .rooftop-laundry-card {
      border-left: 5px solid #5f27cd;
    }

    /* Alert styles */
    .alert {
      border-radius: 10px;
      border: none;
      padding: 15px 20px;
    }

    .alert-info {
      background: #e3f2fd;
      color: #1565c0;
    }

    /* Button styles */
    .btn-sm {
      padding: 5px 12px;
      font-size: 0.8rem;
      border-radius: 5px;
    }

    .btn-outline-primary {
      border-color: #007bff;
      color: #007bff;
    }

    .btn-outline-primary:hover {
      background: #007bff;
      color: white;
    }

    .btn-primary {
      background: #007bff;
      border-color: #007bff;
    }

    .btn-primary:hover {
      background: #0056b3;
      border-color: #0056b3;
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
      <img src="{{ asset('images/pnlogo-header.png') }}" alt="PN Logo">
    </div>
    <div class="header-right">
      <!-- Logout Button -->
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
    <nav class="sidebar">
      <ul class="nav flex-column">
        <li class="nav-item">
          <a href="{{ route('mainstudentdash') }}" class="nav-link">
            <img src="{{ asset('images/dashboard.png')}}" class="sidebar-icon">Dashboard
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('student.general.task') }}" class="nav-link active">
            <img src="{{ asset('images/assign.png')}}" class="sidebar-icon">General Tasks
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin.task.history') }}" class="nav-link {{ Request::routeIs('admin.task.history') || Request::routeIs('StudentsDashboard.task.history') ? 'active' : '' }}">
            <img src="{{ asset('images/history.png')}}" class="sidebar-icon">Room Checklist History
          </a>
        </li>
      </ul>
    </nav>

    <div class="content main-content">
      <a href="{{ route('mainstudentdash') }}" class="back-link">
        <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
          <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
        </svg>
        Back to Home
      </a>

      <!-- Enhanced Header Section -->
      <div class="enhanced-header-section">
        <div class="header-content">
          <div class="header-text">
            <h1 class="enhanced-page-title">General Task Assignments</h1>
            <p class="enhanced-page-subtitle">View your current task assignments and coordinators</p>
          </div>
        </div>
        <div class="header-decoration"></div>
      </div>

      @if(session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      @if(empty($assignmentDetails) || count($assignmentDetails) == 0)
        <div class="no-assignments">
          <div style="font-size: 3rem; margin-bottom: 16px;">📋</div>
          <h3>No Current Assignments</h3>
          <p>There are no active task assignments at the moment. Please check back later or contact your administrator.</p>
        </div>
      @else
        <!-- Assignment Summary -->
        @if(config('app.debug') && count($assignmentDetails) > 0)
        <div class="alert alert-info mb-4">
          <h6><i class="bi bi-info-circle me-2"></i>Assignment Summary</h6>
          <p><strong>Active Task Categories:</strong> {{ count($assignmentDetails) }}</p>
          <p><strong>Total Assigned Students:</strong> {{ collect($assignmentDetails)->sum('total_members') }}</p>
        </div>
        @endif

        <!-- Dynamic Task Assignment Cards -->
        <div class="row g-3">
          @foreach($assignmentDetails as $categoryName => $assignment)
            @php
              // Skip if no members assigned
              if($assignment['total_members'] == 0) continue;
              
              // Get member data directly from assignment details (already processed by controller)
              $members2025 = $assignment['members_2025'] ?? [];
              $members2026 = $assignment['members_2026'] ?? [];
              $coordinators2025 = $assignment['coordinators_2025'] ?? [];
              $coordinators2026 = $assignment['coordinators_2026'] ?? [];
              
              // Count genders - be more flexible with gender matching
              $boys = 0; $girls = 0;
              foreach($members2025 as $member) {
                $gender = strtolower(trim($member['gender'] ?? ''));
                if(in_array($gender, ['male', 'm'])) $boys++;
                elseif(in_array($gender, ['female', 'f'])) $girls++;
              }
              foreach($members2026 as $member) {
                $gender = strtolower(trim($member['gender'] ?? ''));
                if(in_array($gender, ['male', 'm'])) $boys++;
                elseif(in_array($gender, ['female', 'f'])) $girls++;
              }
              
              // Get coordinator names
              $coor2025 = !empty($coordinators2025) ? $coordinators2025[0] : null;
              $coor2026 = !empty($coordinators2026) ? $coordinators2026[0] : null;
              
              // Assignment dates (we'll use placeholder dates for now)
              $startDate = null;
              $endDate = null;
              
              // Determine card color class based on category name
              $cardClass = 'kitchen-dining-card'; // default
              $categoryLower = strtolower($categoryName);
              
              if (str_contains($categoryLower, 'kitchen')) {
                if (str_contains($categoryLower, 'dishwashing')) {
                  $cardClass = 'kitchen-dishwashing-card';
                } else if (str_contains($categoryLower, 'dining')) {
                  $cardClass = 'kitchen-dining-card';
                } else {
                  $cardClass = 'kitchen-operations-card';
                }
              } elseif (str_contains($categoryLower, 'office')) {
                $cardClass = 'offices-card';
              } elseif (str_contains($categoryLower, 'conference')) {
                $cardClass = 'conference-card';
              } elseif (str_contains($categoryLower, 'ground')) {
                $cardClass = 'ground-floor-card';
              } elseif (str_contains($categoryLower, 'waste') || str_contains($categoryLower, 'rooftop')) {
                $cardClass = 'rooftop-waste-card';
              } elseif (str_contains($categoryLower, 'laundry')) {
                $cardClass = 'rooftop-laundry-card';
              }
            @endphp

            <div class="col-lg-4 col-md-6">
              <div class="task-assignment-card {{ $cardClass }}">
                <!-- Card Title -->
                <div class="category-label">
                  {{ $categoryName ?: ($assignment['category'] ?? 'Unknown Category') }}
                </div>
                
                <!-- Gender Count Badges -->
                <div class="d-flex justify-content-center gap-2 mb-3">
                  <div class="badge-male">
                    <strong>Male: {{ $boys }}</strong>
                  </div>
                  <div class="badge-female">
                    <strong>Female: {{ $girls }}</strong>
                  </div>
                </div>
                
                
                <!-- Assignment Period -->
                <div class="text-center mb-3" style="font-size: 0.9rem; color: #555;">
                  <strong>Valid: {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('F j, Y') : '—' }} - {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('F j, Y') : '—' }}</strong>
                </div>
                
                <!-- Coordinators Section -->
                <div class="coordinator-section">
                  <div>
                    <strong>C2025 Coordinator:</strong> 
                    @if($coor2025)
                      <span style="color: #0066cc; font-weight: 600;">{{ $coor2025 }}</span>
                    @else
                      <span style="color: #999; font-style: italic;">No coordinator assigned</span>
                    @endif
                  </div>
                  <div>
                    <strong>C2026 Coordinator:</strong> 
                    @if($coor2026)
                      <span style="color: #0066cc; font-weight: 600;">{{ $coor2026 }}</span>
                    @else
                      <span style="color: #999; font-style: italic;">No coordinator assigned</span>
                    @endif
                  </div>
                </div>
                
                <!-- Description -->
                <div class="description-section">
                  {{ $assignment['description'] ?? 'No description provided.' }}
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex justify-content-center gap-2 mt-3">
                  @php
                    $displayCategoryName = $categoryName ?: ($assignment['category'] ?? 'Unknown Category');
                    $assignmentData = [
                      'category' => $displayCategoryName,
                      'start_date' => $startDate,
                      'end_date' => $endDate,
                      'members_2025' => $members2025,
                      'members_2026' => $members2026,
                      'coordinators_2025' => $coordinators2025,
                      'coordinators_2026' => $coordinators2026
                    ];
                  @endphp
                  <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#readOnlyTaskModal" onclick="showReadOnlyTask('{{ $displayCategoryName }}', @json($assignmentData))">View Members</button>
                  <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#readOnlyTaskModal" onclick="showReadOnlyTask('{{ $displayCategoryName }}', @json($assignmentData))">View Task</button>
                </div>
              </div>
            </div>
          @endforeach
        </div>

        @if(collect($assignmentDetails)->filter(function($assignment) { return $assignment['total_members'] > 0; })->count() == 0)
          <div class="no-assignments">
            <div style="font-size: 3rem; margin-bottom: 16px;">📋</div>
            <h3>No Active Assignments</h3>
            <p>All categories are available but none have assigned members. Please contact your administrator.</p>
          </div>
        @endif

      @endif
    </div>
  </div>

  <!-- Read-Only Task Modal -->
  <div class="modal fade" id="readOnlyTaskModal" tabindex="-1" aria-labelledby="readOnlyTaskModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="readOnlyTaskModalLabel">Task Details</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="readOnlyTaskContent">
            <!-- Content will be populated by JavaScript -->
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Function to show read-only task details
    function showReadOnlyTask(categoryName, assignmentData) {
      console.log('Modal data:', assignmentData);
      console.log('2025 members:', assignmentData.members_2025);
      console.log('2026 members:', assignmentData.members_2026);
      
      // Set modal title
      document.getElementById('readOnlyTaskModalLabel').textContent = 'Members for ' + categoryName;
      
      // Create the member display table
      let content = `
        <div class="row">
          <div class="col-md-6">
            <div class="batch-section">
              <h6 class="batch-header text-center p-2 mb-0" style="background-color: #2c3e50; color: white;">2025</h6>
              <div class="batch-content">`;
      
      // Add 2025 members
      if (assignmentData.members_2025 && assignmentData.members_2025.length > 0) {
        assignmentData.members_2025.forEach((member, index) => {
          console.log(`2025 Member ${index}:`, member);
          const isCoordinator = member.is_coordinator;
          const bgColor = isCoordinator ? '#f39c12' : '#ecf0f1';
          const textColor = isCoordinator ? 'white' : '#2c3e50';
          
          // Try different possible name fields
          const memberName = member.name || member.student_name || member.full_name || `Member ${index + 1}`;
          
          content += `
            <div class="member-row p-2 border-bottom" style="background-color: ${bgColor}; color: ${textColor};">
              ${memberName}
            </div>`;
        });
      } else {
        content += '<div class="p-2 text-muted text-center">No members assigned</div>';
      }
      
      content += `
              </div>
            </div>
          </div>
          <div class="col-md-6">
            <div class="batch-section">
              <h6 class="batch-header text-center p-2 mb-0" style="background-color: #2c3e50; color: white;">2026</h6>
              <div class="batch-content">`;
      
      // Add 2026 members
      if (assignmentData.members_2026 && assignmentData.members_2026.length > 0) {
        assignmentData.members_2026.forEach((member, index) => {
          console.log(`2026 Member ${index}:`, member);
          const isCoordinator = member.is_coordinator;
          const bgColor = isCoordinator ? '#f39c12' : '#ecf0f1';
          const textColor = isCoordinator ? 'white' : '#2c3e50';
          
          // Try different possible name fields
          const memberName = member.name || member.student_name || member.full_name || `Member ${index + 1}`;
          
          content += `
            <div class="member-row p-2 border-bottom" style="background-color: ${bgColor}; color: ${textColor};">
              ${memberName}
            </div>`;
        });
      } else {
        content += '<div class="p-2 text-muted text-center">No members assigned</div>';
      }
      
      content += `
              </div>
            </div>
          </div>
        </div>`;
      
      // Set the content
      document.getElementById('readOnlyTaskContent').innerHTML = content;
    }
  </script>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
