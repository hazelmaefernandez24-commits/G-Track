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
    /* ===== FORCE LEFT ALIGNMENT - HIGHEST PRIORITY ===== */
    html body .container-fluid .content.main-content,
    html body .container-fluid .content.main-content *,
    html body .container-fluid .content,
    html body .container-fluid .content * {
      text-align: left !important;
      margin-left: 0 !important;
    }

    html body .container-fluid .content.main-content .row,
    html body .container-fluid .content .row {
      justify-content: flex-start !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      padding-left: 0 !important;
      padding-right: 0 !important;
    }

    html body .container-fluid .content.main-content .col-lg-4,
    html body .container-fluid .content.main-content .col-md-6,
    html body .container-fluid .content .col-lg-4,
    html body .container-fluid .content .col-md-6 {
      padding-left: 0 !important;
      margin-left: 0 !important;
    }

    html body .container-fluid {
      padding: 0 !important;
      margin: 0 !important;
    }

    html body .container-fluid .content {
      margin-left: 250px !important;
      margin-right: 0 !important;
      width: calc(100% - 250px) !important;
      max-width: none !important;
      padding-left: 20px !important;
      padding-right: 20px !important;
    }

    /* Override any centering classes */
    html body .text-center {
      text-align: left !important;
    }

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
    }

    /* Sidebar styles */
    .sidebar {
      position: fixed;
      top: 60px;
      left: 0;
      width: 250px;
      height: calc(100vh - 60px);
      background: #fff;
      box-shadow: 2px 0 10px rgba(0,0,0,0.1);
      padding: 20px 0;
      overflow-y: auto;
      z-index: 999;
    }

    .sidebar .nav-link {
      display: flex;
      align-items: center;
      padding: 12px 20px;
      color: #666;
      text-decoration: none;
      transition: all 0.3s ease;
    }

    .sidebar .nav-link.active {
      background: #f8f9fa;
      color: #007bff;
      border-right: 3px solid #007bff;
    }

    .sidebar-icon {
      width: 20px;
      height: 20px;
      margin-right: 12px;
    }

    /* Main content */
    .content {
      margin-left: 250px;
      padding: 20px;
      min-height: calc(100vh - 60px);
      width: calc(100% - 250px) !important;
      max-width: none !important;
      display: block !important;
    }

    /* Force left alignment for all content */
    .main-content {
      text-align: left !important;
      max-width: none !important;
      margin: 0 !important;
    }

    .main-content .row {
      justify-content: flex-start !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    .main-content .category-card {
      text-align: left !important;
    }

    /* Override any Bootstrap container centering */
    .container-fluid .content {
      max-width: none !important;
      margin-left: 250px !important;
      margin-right: 0 !important;
    }

    /* Ensure no auto margins on content */
    .content > * {
      margin-left: 0 !important;
      margin-right: auto !important;
    }

    /* Force everything to left align - very aggressive */
    .main-content,
    .main-content *,
    .content,
    .content * {
      text-align: left !important;
    }

    /* Override any Bootstrap centering classes */
    .text-center {
      text-align: left !important;
    }

    /* Force rows to start from left */
    .row {
      justify-content: flex-start !important;
    }

    /* Remove any auto margins that might center content */
    .container,
    .container-fluid,
    .row,
    .col-*,
    [class*="col-"] {
      margin-left: 0 !important;
    }

    /* Very specific targeting for the card structure */
    .content .row.justify-content-start {
      margin: 0 !important;
      padding: 0 !important;
      width: 100% !important;
    }

    .content .row.justify-content-start .col-lg-4,
    .content .row.justify-content-start .col-md-6 {
      padding-left: 0 !important;
      margin-left: 0 !important;
    }

    /* Force the entire content area to not be centered */
    .container-fluid {
      padding: 0 !important;
    }

    .container-fluid .content.main-content {
      margin-left: 250px !important;
      margin-right: 0 !important;
      padding-left: 20px !important;
      padding-right: 20px !important;
      width: calc(100% - 250px) !important;
    }

    .back-link {
      display: inline-flex;
      align-items: center;
      color: #007bff;
      text-decoration: none;
      margin-bottom: 20px;
      font-size: 14px;
    }

    .back-link svg {
      margin-right: 5px;
    }

    /* Enhanced header section */
    .enhanced-header-section {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border-radius: 20px;
      padding: 30px;
      margin-bottom: 30px;
      color: white;
      position: relative;
      overflow: hidden;
    }

    .enhanced-page-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 10px;
    }

    .enhanced-page-subtitle {
      font-size: 1.1rem;
      opacity: 0.9;
      margin: 0;
    }

    /* Task card styles */
    .category-card {
      position: relative;
      border: none;
      border-radius: 20px;
      margin-bottom: 20px;
      transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
      cursor: pointer;
      overflow: hidden;
    }

    .category-card:hover {
      transform: translateY(-12px) scale(1.03);
      box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }

    .category-label {
      font-size: 22px;
      font-weight: 800;
      color: #2c3e50;
      margin-bottom: 8px;
      text-align: center;
      position: relative;
    }

    /* Individual Pastel Colors for Each Category */
    .category-card:nth-child(1) > div {
      background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 50%, #f48fb1 100%);
      border: 2px solid #f8bbd9;
    }

    .category-card:nth-child(2) > div {
      background: linear-gradient(135deg, #e3f2fd 0%, #90caf9 50%, #64b5f6 100%);
      border: 2px solid #90caf9;
    }

    .category-card:nth-child(3) > div {
      background: linear-gradient(135deg, #e8f5e8 0%, #a5d6a7 50%, #81c784 100%);
      border: 2px solid #a5d6a7;
    }

    .category-card:nth-child(4) > div {
      background: linear-gradient(135deg, #fff8e1 0%, #ffcc02 50%, #ffc107 100%);
      border: 2px solid #ffcc02;
    }

    .category-card:nth-child(5) > div {
      background: linear-gradient(135deg, #f3e5f5 0%, #ce93d8 50%, #ba68c8 100%);
      border: 2px solid #ce93d8;
    }

    .category-card:nth-child(6) > div {
      background: linear-gradient(135deg, #fff3e0 0%, #ffab91 50%, #ff8a65 100%);
      border: 2px solid #ffab91;
    }

    /* Badge styles */
    .badge {
      border-radius: 20px;
      padding: 8px 15px;
      font-size: 14px;
      font-weight: 600;
    }

    .badge-primary {
      background: #007bff;
      color: white;
    }

    .badge-success {
      background: #28a745;
      color: white;
    }

    /* Student list styles */
    .student-row {
      padding: 8px 0;
      border-bottom: 1px solid #eee;
    }

    .student-row:last-child {
      border-bottom: none;
    }

    .coordinator-highlight {
      background: rgba(255, 193, 7, 0.2);
      border-radius: 5px;
      padding: 4px 8px;
      font-weight: 600;
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      
      .content {
        margin-left: 0;
      }
      
      .enhanced-page-title {
        font-size: 2rem;
      }
    }

    /* ===== FINAL OVERRIDE - MAXIMUM SPECIFICITY ===== */
    html body div.container-fluid div.content.main-content {
      margin-left: 250px !important;
      margin-right: 0 !important;
      width: calc(100% - 250px) !important;
      max-width: none !important;
      text-align: left !important;
      display: block !important;
    }

    html body div.container-fluid div.content.main-content div.row {
      justify-content: flex-start !important;
      margin: 0 !important;
      padding: 0 !important;
      text-align: left !important;
      display: flex !important;
      flex-wrap: wrap !important;
    }

    html body div.container-fluid div.content.main-content div.row div[class*="col-"] {
      padding-left: 0 !important;
      padding-right: 15px !important;
      margin-left: 0 !important;
      text-align: left !important;
      display: flex !important;
      justify-content: flex-start !important;
    }

    html body div.container-fluid div.content.main-content div.row div[class*="col-"] div.category-card {
      text-align: left !important;
      width: 100% !important;
      margin: 0 !important;
    }

    /* Force all Bootstrap rows to start from left */
    .row.justify-content-start {
      justify-content: flex-start !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    /* Force all columns to align left */
    .col-lg-4, .col-md-6 {
      display: flex !important;
      justify-content: flex-start !important;
      padding-left: 0 !important;
      padding-right: 15px !important;
    }

    /* Remove any centering from Bootstrap */
    .justify-content-center {
      justify-content: flex-start !important;
    }

    .text-center {
      text-align: left !important;
    }

    /* Ensure cards fill their containers properly */
    .category-card {
      width: 100% !important;
      margin: 0 !important;
    }

    /* Override dashboard.css if it exists */
    .main-content,
    .main-content *,
    .content,
    .content *,
    .row,
    .row *,
    [class*="col-"],
    [class*="col-"] * {
      text-align: left !important;
      margin-left: 0 !important;
    }

    /* Force Bootstrap grid to left */
    .justify-content-start {
      justify-content: flex-start !important;
    }

    .text-start {
      text-align: left !important;
    }

    /* Animation for celebration effect */
    @keyframes pulse {
      0% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.1);
      }
      100% {
        transform: scale(1);
      }
    }

    /* ULTIMATE LEFT ALIGNMENT OVERRIDE */
    body * {
      text-align: left !important;
    }

    .container-fluid .content.main-content,
    .container-fluid .content.main-content * {
      text-align: left !important;
      justify-content: flex-start !important;
    }

    /* Ensure no external CSS can center the content */
    body .container-fluid .content {
      display: block !important;
      text-align: left !important;
    }

    body .container-fluid .content .row {
      justify-content: flex-start !important;
      text-align: left !important;
    }

    /* NUCLEAR OPTION - Override everything */
    html, body, div, section, main, article, aside, header, footer, nav, 
    .container, .container-fluid, .row, .col, .col-1, .col-2, .col-3, .col-4, .col-5, .col-6,
    .col-7, .col-8, .col-9, .col-10, .col-11, .col-12, .col-sm, .col-md, .col-lg, .col-xl,
    .col-sm-1, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8,
    .col-sm-9, .col-sm-10, .col-sm-11, .col-sm-12, .col-md-1, .col-md-2, .col-md-3, .col-md-4,
    .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-md-10, .col-md-11, .col-md-12,
    .col-lg-1, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8,
    .col-lg-9, .col-lg-10, .col-lg-11, .col-lg-12, .col-xl-1, .col-xl-2, .col-xl-3, .col-xl-4,
    .col-xl-5, .col-xl-6, .col-xl-7, .col-xl-8, .col-xl-9, .col-xl-10, .col-xl-11, .col-xl-12 {
      text-align: left !important;
      justify-content: flex-start !important;
    }

    /* Force Bootstrap grid system to left */
    .row {
      display: flex !important;
      flex-wrap: wrap !important;
      justify-content: flex-start !important;
      align-items: flex-start !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    /* Force all columns to start from left */
    [class*="col-"] {
      padding-left: 0 !important;
      padding-right: 15px !important;
      margin-left: 0 !important;
      display: block !important;
      text-align: left !important;
    }

    /* Override any dashboard.css centering */
    .main-content {
      text-align: left !important;
      justify-content: flex-start !important;
    }

    /* Kill all centering classes */
    .text-center, .justify-content-center, .align-items-center, .mx-auto, .text-md-center, .text-lg-center {
      text-align: left !important;
      justify-content: flex-start !important;
      align-items: flex-start !important;
      margin-left: 0 !important;
      margin-right: auto !important;
    }

    /* FINAL NUCLEAR OVERRIDE - MAXIMUM SPECIFICITY */
    html body div.container-fluid div.content.main-content div.mb-2.mt-3 div.row.justify-content-start {
      justify-content: flex-start !important;
      text-align: left !important;
      display: flex !important;
      flex-wrap: wrap !important;
      margin-left: 0 !important;
      margin-right: 0 !important;
      align-items: flex-start !important;
    }

    html body div.container-fluid div.content.main-content div.mb-2.mt-3 div.row.justify-content-start div.col-lg-4,
    html body div.container-fluid div.content.main-content div.mb-2.mt-3 div.row.justify-content-start div.col-md-6 {
      padding-left: 0 !important;
      padding-right: 15px !important;
      margin-left: 0 !important;
      text-align: left !important;
      display: block !important;
      float: left !important;
    }

    /* Override any possible dashboard.css rules */
    body[class*=""] div[class*="container"] div[class*="content"] div[class*="row"] {
      justify-content: flex-start !important;
      text-align: left !important;
    }

    /* Force flexbox behavior */
    .row.justify-content-start {
      display: -webkit-box !important;
      display: -ms-flexbox !important;
      display: flex !important;
      -webkit-box-pack: start !important;
      -ms-flex-pack: start !important;
      justify-content: flex-start !important;
      -webkit-box-align: start !important;
      -ms-flex-align: start !important;
      align-items: flex-start !important;
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
          <a href="#" class="nav-link" onclick="openMyTaskAssignments(); return false;">
            <img src="{{ asset('images/assign.png')}}" class="sidebar-icon">My Task Assignments
          </a>
        </li>
        <li class="nav-item">
          <a href="{{ route('admin.task.history') }}" class="nav-link {{ Request::routeIs('admin.task.history') || Request::routeIs('StudentsDashboard.task.history') ? 'active' : '' }}">
            <img src="{{ asset('images/history.png')}}" class="sidebar-icon">Room Checklist History
          </a>
        </li>
      </ul>
    </nav>

    <div class="content main-content" style="text-align: left !important; justify-content: flex-start !important; display: block !important;">
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

      <!-- General Task Checklist Button -->
      <div class="mb-3 text-start">
        <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#generalChecklistModal">
          <i class="bi bi-list-check me-2"></i>General Task Checklist
        </button>
      </div>

      @if(session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      <!-- Dynamic Cards Structure - Same as Admin View -->
      @if(isset($dynamicStructure) && $dynamicStructure->count() > 0)
        @foreach($dynamicStructure as $mainAreaName => $subAreaCategories)
          @if($subAreaCategories->count() > 0)
          <div class="mb-2 mt-3">
            <h3 style="font-weight:600; color:#222; font-size:1.1rem; margin-bottom:12px;">{{ $mainAreaName }}</h3>
            <div class="row justify-content-start" style="justify-content: flex-start !important; text-align: left !important; display: flex !important; flex-wrap: wrap !important; margin-left: 0 !important; margin-right: 0 !important;">
              @foreach($subAreaCategories as $cat)
                  <div class="col-lg-4 col-md-6" style="padding-left: 0 !important; padding-right: 15px !important; margin-left: 0 !important; text-align: left !important; display: block !important;">
                    <div class="category-card text-start p-0 overflow-hidden" style="background:none; border:none; box-shadow:none;">
                      <div style="height:100%; min-height:220px; max-height:260px; border-radius:10px; padding:10px; background:#f8f9fa; box-shadow: 0 0 6px rgba(0,0,0,0.05); position: relative;">
                        <div class="category-label" style="background:none; border:none; margin-bottom:4px; font-size:0.8rem; font-weight:600; color:#333;">
                          {{ $cat->name }}
                        </div>
                        <div class="mb-2">
                          @php
                            // Compute counts and coordinators for this category (current assignments only)
                            $boys = 0; $girls = 0; $coor2025 = null; $coor2026 = null; $coor_any = null; $coor_any_batch = null;
                            foreach($cat->assignments as $assignment){
                              if($assignment->status === 'current'){
                                foreach($assignment->assignmentMembers as $member){
                                  // Prepare sensible fallbacks when the student relation is missing (legacy or partially-migrated rows)
                                  $g = null; $batch = null; $fullName = null;
                                  if ($member->student) {
                                    $g = $member->student->gender ?? null;
                                    $batch = optional($member->student->studentDetail)->batch ?? ($member->student->batch ?? null);
                                    $fullName = trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? ''));
                                  } else {
                                    // Try using direct member fields that may have been stored by the shuffle script
                                    // e.g. student_name, student_code, or student_group16_id
                                    if (!empty($member->student_name)) {
                                      $fullName = $member->student_name;
                                      $g = $member->gender ?? null;
                                      // Try to resolve batch from student_code or other fields
                                      if (!empty($member->student_code)) {
                                        $studentDetail = \App\Models\StudentDetail::where('student_id', $member->student_code)->first();
                                        $batch = $studentDetail ? $studentDetail->batch : null;
                                      }
                                    }
                                  }
                                  
                                  // Count by gender
                                  if (strtolower($g ?? '') === 'male' || strtolower($g ?? '') === 'm') $boys++;
                                  elseif (strtolower($g ?? '') === 'female' || strtolower($g ?? '') === 'f') $girls++;
                                  
                                  // Track coordinators
                                  if ($member->is_coordinator) {
                                    if ($batch == 2025) {
                                      $coor2025 = $fullName;
                                    } elseif ($batch == 2026) {
                                      $coor2026 = $fullName;
                                    } else {
                                      $coor_any = $fullName;
                                      $coor_any_batch = $batch;
                                    }
                                  }
                                }
                              }
                            }
                            
                            $totalMembers = $boys + $girls;
                          @endphp
                          
                          <!-- Member count badges -->
                          <div class="d-flex justify-content-start gap-2 mb-2">
                            <span class="badge badge-primary">{{ $boys }} Boys</span>
                            <span class="badge badge-success">{{ $girls }} Girls</span>
                          </div>
                          
                          <!-- Coordinators -->
                          @if($coor2025 || $coor2026 || $coor_any)
                            <div class="coordinator-section" style="font-size: 0.75rem; color: #666;">
                              <strong>Coordinators:</strong><br>
                              @if($coor2025)
                                <div class="coordinator-highlight">C2025: {{ $coor2025 }}</div>
                              @endif
                              @if($coor2026)
                                <div class="coordinator-highlight">C2026: {{ $coor2026 }}</div>
                              @endif
                              @if($coor_any)
                                <div class="coordinator-highlight">{{ $coor_any_batch ? 'C'.$coor_any_batch : 'Coordinator' }}: {{ $coor_any }}</div>
                              @endif
                            </div>
                          @endif
                          
                          <!-- All members list -->
                          <div class="members-section mt-2" style="font-size: 0.7rem; max-height: 100px; overflow-y: auto;">
                            @foreach($cat->assignments as $assignment)
                              @if($assignment->status === 'current')
                                @foreach($assignment->assignmentMembers as $member)
                                  @php
                                    $fullName = '';
                                    $batch = '';
                                    if ($member->student) {
                                      $fullName = trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? ''));
                                      $batch = optional($member->student->studentDetail)->batch ?? ($member->student->batch ?? '');
                                    } else {
                                      $fullName = $member->student_name ?? 'Unknown';
                                      if (!empty($member->student_code)) {
                                        $studentDetail = \App\Models\StudentDetail::where('student_id', $member->student_code)->first();
                                        $batch = $studentDetail ? $studentDetail->batch : '';
                                      }
                                    }
                                  @endphp
                                  <div class="student-row {{ $member->is_coordinator ? 'coordinator-highlight' : '' }}">
                                    {{ $fullName }} @if($batch)({{ $batch }})@endif
                                    @if($member->is_coordinator)<strong> - Coordinator</strong>@endif
                                  </div>
                                @endforeach
                              @endif
                            @endforeach
                          </div>
                          
                          <!-- Task Checklist Section -->
                          @if($cat->checklist && $cat->checklist->checklist_items && count($cat->checklist->checklist_items) > 0)
                          <div class="checklist-section mt-3" style="border-top: 1px solid #e9ecef; padding-top: 12px;">
                            <div class="d-flex align-items-center mb-2">
                              <i class="bi bi-list-check me-2" style="color: #007bff; font-size: 1rem;"></i>
                              <strong style="color: #333; font-size: 0.8rem;">Task Checklist</strong>
                            </div>
                            <div class="checklist-items" style="font-size: 0.7rem; max-height: 150px; overflow-y: auto;">
                              @foreach($cat->checklist->checklist_items as $index => $item)
                              <div class="checklist-item d-flex align-items-start mb-1" style="padding: 4px 0;">
                                <input type="checkbox" class="form-check-input me-2" style="margin-top: 2px; transform: scale(0.8);" id="checklist_{{ $cat->id }}_{{ $index }}">
                                <label for="checklist_{{ $cat->id }}_{{ $index }}" style="line-height: 1.3; cursor: pointer; color: #555;">
                                  {{ $item }}
                                </label>
                              </div>
                              @endforeach
                            </div>
                          </div>
                          @endif
                        </div>
                      </div>
                    </div>
                  </div>
              @endforeach
            </div>
          </div>
          @endif
        @endforeach
      @else
        <div class="alert alert-info text-center">
          <h4>No Task Assignments Available</h4>
          <p>There are currently no active task assignments. Please check back later or contact your administrator.</p>
        </div>
      @endif
    </div>
  </div>

  <!-- General Task Checklist Modal -->
  <div class="modal fade" id="generalChecklistModal" tabindex="-1" aria-labelledby="generalChecklistModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">
          <h5 class="modal-title" id="generalChecklistModalLabel">
            <i class="bi bi-list-check me-2"></i>General Task Checklist
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          @php
            $allChecklistItems = [];
            foreach($categories as $category) {
              if($category->checklist && $category->checklist->checklist_items) {
                foreach($category->checklist->checklist_items as $item) {
                  if(!in_array($item, $allChecklistItems)) {
                    $allChecklistItems[] = $item;
                  }
                }
              }
            }
          @endphp
          
          @if(count($allChecklistItems) > 0)
            <div class="alert alert-info border-0 mb-3" style="background: #f8f9ff;">
              <i class="bi bi-info-circle me-2"></i>
              <strong>Complete these tasks</strong> as part of your general task assignments.
            </div>
            
            @foreach($allChecklistItems as $index => $item)
            <div class="checklist-item d-flex align-items-start mb-3 p-3" style="background: #f8f9fa; border-radius: 8px; border-left: 4px solid #007bff;">
              <input type="checkbox" class="form-check-input me-3" style="margin-top: 4px; transform: scale(1.2);" id="general_checklist_{{ $index }}">
              <label for="general_checklist_{{ $index }}" style="line-height: 1.4; cursor: pointer; color: #333; font-size: 0.95rem;">
                {{ $item }}
              </label>
            </div>
            @endforeach
          @else
            <div class="text-center py-4">
              <i class="bi bi-list-check" style="font-size: 3rem; color: #ccc;"></i>
              <h5 class="mt-3 text-muted">No Checklist Items Available</h5>
              <p class="text-muted">There are currently no task checklist items configured.</p>
            </div>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- View Assignment Modal -->
  <div class="modal fade" id="viewAssignmentModal" tabindex="-1" aria-labelledby="viewAssignmentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white;">
          <h5 class="modal-title" id="viewAssignmentModalLabel">
            <i class="bi bi-eye me-2"></i>View Assignment
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 20px; background: #f8f9fa;">
          <!-- Assignment Info -->
          <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
            <div class="d-flex align-items-center">
              <i class="bi bi-info-circle me-3" style="font-size: 1.5rem;"></i>
              <div>
                <h6 class="mb-1" style="font-weight: 600;">Assignment Area: <span id="assignmentAreaName"></span></h6>
                <p class="mb-0">View your weekly task assignments and operational tasks for this area.</p>
              </div>
            </div>
          </div>

          <!-- Task Completion Table -->
          <div class="card border-0" style="border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
            <div class="card-header border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 15px 15px 0 0;">
              <div class="d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                  <i class="bi bi-list-check me-3" style="font-size: 1.5rem;"></i>
                  <div>
                    <h5 class="mb-1">TASKS TO COMPLETE</h5>
                    <p class="mb-0" style="opacity: 0.9;">Weekly task completion checklist</p>
                  </div>
                </div>
                <div class="badge bg-light text-dark px-3 py-2" style="font-size: 0.9rem;">
                  <i class="bi bi-calendar-date me-1"></i>
                  <input type="date" class="form-control-sm border-0" style="background: transparent; color: #333;" value="2024-12-23">
                </div>
              </div>
            </div>
            
            <div class="card-body" style="padding: 0;">
              <!-- Task Completion Table -->
              <div class="table-responsive">
                <table class="table table-bordered mb-0" style="border-collapse: collapse;">
                  <thead>
                    <tr style="background: #f8f9fa;">
                      <th style="width: 35%; padding: 15px; font-weight: 600; color: #333; border: 1px solid #dee2e6;">TASKS TO COMPLETE</th>
                      <th style="width: 9.28%; text-align: center; padding: 15px; font-weight: 600; color: #333; border: 1px solid #dee2e6;">MON</th>
                      <th style="width: 9.28%; text-align: center; padding: 15px; font-weight: 600; color: #333; border: 1px solid #dee2e6;">TUE</th>
                      <th style="width: 9.28%; text-align: center; padding: 15px; font-weight: 600; color: #333; border: 1px solid #dee2e6;">WED</th>
                      <th style="width: 9.28%; text-align: center; padding: 15px; font-weight: 600; color: #333; border: 1px solid #dee2e6;">THU</th>
                      <th style="width: 9.28%; text-align: center; padding: 15px; font-weight: 600; color: #333; border: 1px solid #dee2e6;">FRI</th>
                      <th style="width: 9.28%; text-align: center; padding: 15px; font-weight: 600; color: #333; border: 1px solid #dee2e6;">SAT</th>
                      <th style="width: 9.28%; text-align: center; padding: 15px; font-weight: 600; color: #333; border: 1px solid #dee2e6;">SUN</th>
                    </tr>
                  </thead>
                  <tbody id="taskCompletionTableBody">
                    <!-- Task rows will be populated by JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Operational Tasks Section -->
          <div class="card border-0 mt-4" style="border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);" id="operationalTasksCard">
            <div class="card-header border-0" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 20px; border-radius: 15px 15px 0 0;">
              <div class="d-flex align-items-center">
                <i class="bi bi-list-check me-3" style="font-size: 1.5rem;"></i>
                <div>
                  <h5 class="mb-1">Operational Tasks</h5>
                  <p class="mb-0" style="opacity: 0.9;">Daily operational checklist for this area</p>
                </div>
              </div>
            </div>
            <div class="card-body" style="padding: 25px;">
              <div id="operationalTasksList">
                <!-- Operational tasks will be populated by JavaScript -->
              </div>
              <div id="noOperationalTasks" class="text-center py-4" style="display: none;">
                <i class="bi bi-clipboard-check" style="font-size: 3rem; color: #ccc;"></i>
                <h6 class="mt-3 text-muted">No Operational Tasks</h6>
                <p class="text-muted">No specific operational tasks configured for this area.</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="modal-footer border-0" style="padding: 25px; background: white;">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal" style="border-radius: 12px; padding: 12px 25px;">
            <i class="bi bi-x-circle me-2"></i>Close
          </button>
          <button type="button" class="btn btn-success btn-lg" onclick="markAllOperationalCompleted()" style="border-radius: 12px; padding: 12px 25px;">
            <i class="bi bi-check-all me-2"></i>Mark All Tasks Completed
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
    // Function to show assignment details in modal
    function showAssignmentDetails(categoryId, areaName, checklistItems) {
      console.log('showAssignmentDetails called with:', categoryId, areaName, checklistItems);
      
      // Set the area name
      const areaNameElement = document.getElementById('assignmentAreaName');
      if (areaNameElement) {
        areaNameElement.textContent = areaName;
        console.log('Area name set to:', areaName);
      } else {
        console.error('assignmentAreaName element not found');
      }
      
      // Populate task completion table
      populateTaskCompletionTable(categoryId, areaName, checklistItems);
      
      // Populate operational tasks
      populateOperationalTasks(checklistItems);
      
      console.log('Assignment details populated successfully');
    }
    
    // Function to populate task completion table
    function populateTaskCompletionTable(categoryId, areaName, checklistItems) {
      console.log('populateTaskCompletionTable called for:', areaName);
      
      const tableBody = document.getElementById('taskCompletionTableBody');
      if (!tableBody) {
        console.error('taskCompletionTableBody not found');
        return;
      }
      
      // Clear existing content
      tableBody.innerHTML = '';
      
      // Default tasks based on the first image
      const defaultTasks = [
        'Assigned members wake up on time and completed their tasks as scheduled.',
        'The students assigned to cook the rice completed the task properly.',
        'The students assigned to cook the viand completed the task properly.',
        'The students assigned to assist the cook carried out their duties diligently.',
        'Ingredients were prepared ahead of time.',
        'The kitchen was properly cleaned after cooking.',
        'The food was transferred from the kitchen to the center.'
      ];
      
      // Use checklist items if available, otherwise use default tasks
      const tasks = (checklistItems && checklistItems.length > 0) ? checklistItems : defaultTasks;
      
      tasks.forEach((task, index) => {
        const row = document.createElement('tr');
        row.style.cssText = 'border: 1px solid #dee2e6;';
        
        // Task description cell
        const taskCell = document.createElement('td');
        taskCell.style.cssText = 'padding: 15px; border: 1px solid #dee2e6; vertical-align: middle; background: #f8f9fa;';
        taskCell.textContent = task;
        row.appendChild(taskCell);
        
        // Day cells (MON-SUN)
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        days.forEach(day => {
          const dayCell = document.createElement('td');
          dayCell.style.cssText = 'padding: 15px; border: 1px solid #dee2e6; text-align: center; vertical-align: middle; background: #fff;';
          
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'form-check-input';
          checkbox.style.cssText = 'transform: scale(1.2); cursor: pointer;';
          checkbox.id = `task_${index}_${day}`;
          
          dayCell.appendChild(checkbox);
          row.appendChild(dayCell);
        });
        
        tableBody.appendChild(row);
      });
    }
    
    // Function to populate operational tasks
    function populateOperationalTasks(checklistItems) {
      const operationalTasksList = document.getElementById('operationalTasksList');
      const noOperationalTasks = document.getElementById('noOperationalTasks');
      
      // Clear previous content
      operationalTasksList.innerHTML = '';
      
      if (checklistItems && checklistItems.length > 0) {
        noOperationalTasks.style.display = 'none';
        operationalTasksList.style.display = 'block';
        
        checklistItems.forEach((item, index) => {
          const taskDiv = document.createElement('div');
          taskDiv.className = 'operational-task mb-3';
          
          const checkboxId = `operational_task_${index}`;
          
          taskDiv.innerHTML = `
            <div class="card border-0" style="background: #f8f9fa; border-radius: 10px;">
              <div class="card-body d-flex align-items-center" style="padding: 15px;">
                <div class="me-3">
                  <input type="checkbox" class="form-check-input" style="transform: scale(1.2);" id="${checkboxId}">
                </div>
                <div class="flex-grow-1">
                  <label for="${checkboxId}" style="cursor: pointer; margin: 0; font-weight: 500; color: #333;">
                    ${item}
                  </label>
                </div>
                <div class="ms-3">
                  <span class="badge bg-primary">Operational</span>
                </div>
              </div>
            </div>
          `;
          
          operationalTasksList.appendChild(taskDiv);
        });
      } else {
        operationalTasksList.style.display = 'none';
        noOperationalTasks.style.display = 'block';
      }
    }
    
    // Function to mark all operational tasks as completed
    function markAllOperationalCompleted() {
      // Mark all operational tasks
      const operationalCheckboxes = document.querySelectorAll('#operationalTasksList input[type="checkbox"]');
      operationalCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
      });
      
      // Mark all task completion table checkboxes
      const tableCheckboxes = document.querySelectorAll('#taskCompletionTableBody input[type="checkbox"]');
      tableCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
      });
      
      // Show success message
      const alert = document.createElement('div');
      alert.className = 'alert alert-success border-0 fade show mt-3';
      alert.style.cssText = 'border-radius: 12px; padding: 20px;';
      alert.innerHTML = `
        <div class="d-flex align-items-center">
          <div class="me-3">
            <i class="bi bi-check-circle-fill" style="font-size: 1.5rem; color: #28a745;"></i>
          </div>
          <div class="flex-grow-1">
            <h6 class="mb-1" style="font-weight: 600;">All Tasks Completed!</h6>
            <p class="mb-0">Excellent work! You have completed all weekly assignments and operational tasks for this area.</p>
          </div>
          <button type="button" class="btn-close" onclick="this.parentElement.parentElement.remove()"></button>
        </div>
      `;
      
      const operationalTasksList = document.getElementById('operationalTasksList');
      if (operationalTasksList) {
        operationalTasksList.appendChild(alert);
        
        // Auto-dismiss after 5 seconds
        setTimeout(() => {
          if (alert && alert.parentNode) {
            alert.remove();
          }
        }, 5000);
      }
    }

    // Student Task Assignments Functions
    function openMyTaskAssignments() {
      const modal = new bootstrap.Modal(document.getElementById('myTaskAssignmentsModal'));
      modal.show();
      loadMyTaskAssignments();
    }

    async function loadMyTaskAssignments() {
      const studentId = '{{ auth()->user()->user_id ?? "" }}';
      if (!studentId) {
        showTaskAssignmentError('Unable to identify student');
        return;
      }

      try {
        showTaskAssignmentLoading(true);
        
        const response = await fetch(`/task-management/student/${studentId}/tasks`);
        const result = await response.json();
        
        if (result.success && result.assignments) {
          displayTaskAssignments(result.assignments);
        } else {
          showTaskAssignmentError('No task assignments found');
        }
      } catch (error) {
        console.error('Error loading task assignments:', error);
        showTaskAssignmentError('Error loading task assignments');
      } finally {
        showTaskAssignmentLoading(false);
      }
    }

    function displayTaskAssignments(assignments) {
      const container = document.getElementById('taskAssignmentsContainer');
      container.innerHTML = '';

      if (Object.keys(assignments).length === 0) {
        container.innerHTML = `
          <div class="text-center py-5">
            <i class="bi bi-clipboard-x" style="font-size: 3rem; color: #ccc;"></i>
            <h5 class="mt-3 text-muted">No Task Assignments</h5>
            <p class="text-muted">You don't have any task assignments yet.</p>
          </div>
        `;
        return;
      }

      Object.keys(assignments).forEach(categoryName => {
        const categoryAssignments = assignments[categoryName];
        
        const categoryCard = document.createElement('div');
        categoryCard.className = 'card mb-4';
        categoryCard.style.borderRadius = '15px';
        categoryCard.style.border = 'none';
        categoryCard.style.boxShadow = '0 5px 20px rgba(0,0,0,0.1)';
        
        categoryCard.innerHTML = `
          <div class="card-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; border-radius: 15px 15px 0 0;">
            <h5 class="mb-0">
              <i class="bi bi-geo-alt me-2"></i>${categoryName}
            </h5>
          </div>
          <div class="card-body">
            <div id="category-${categoryName.replace(/\s+/g, '-')}-assignments">
              <!-- Assignments will be loaded here -->
            </div>
          </div>
        `;
        
        container.appendChild(categoryCard);
        
        // Display assignments for each date
        const assignmentContainer = categoryCard.querySelector(`#category-${categoryName.replace(/\s+/g, '-')}-assignments`);
        
        Object.keys(categoryAssignments).forEach(date => {
          const dateAssignments = categoryAssignments[date];
          
          const dateSection = document.createElement('div');
          dateSection.className = 'mb-3';
          
          dateSection.innerHTML = `
            <h6 class="fw-bold mb-3">
              <i class="bi bi-calendar-date me-2"></i>${formatAssignmentDate(date)}
            </h6>
            <div class="row">
              ${dateAssignments.map(assignment => `
                <div class="col-md-6 mb-3">
                  <div class="card h-100" style="border-left: 4px solid #28a745;">
                    <div class="card-body">
                      <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="card-title mb-0">${assignment.task_definition.task_name}</h6>
                        <span class="badge bg-${getStatusColor(assignment.status)}">${assignment.status}</span>
                      </div>
                      <p class="card-text text-muted small mb-2">${assignment.task_definition.task_description || 'No description'}</p>
                      <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                          <i class="bi bi-clock me-1"></i>
                          ${assignment.start_time || '08:00'} - ${assignment.end_time || '17:00'}
                        </small>
                        <div>
                          <span class="badge bg-${getDifficultyColor(assignment.task_definition.difficulty_level)}">${assignment.task_definition.difficulty_level}</span>
                          ${assignment.task_definition.estimated_duration ? `<span class="badge bg-info">${assignment.task_definition.estimated_duration} min</span>` : ''}
                        </div>
                      </div>
                      ${assignment.notes ? `<div class="mt-2"><small class="text-muted"><i class="bi bi-sticky me-1"></i>${assignment.notes}</small></div>` : ''}
                    </div>
                  </div>
                </div>
              `).join('')}
            </div>
          `;
          
          assignmentContainer.appendChild(dateSection);
        });
      });
    }

    function showTaskAssignmentLoading(show) {
      const container = document.getElementById('taskAssignmentsContainer');
      if (show) {
        container.innerHTML = `
          <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading your task assignments...</p>
          </div>
        `;
      }
    }

    function showTaskAssignmentError(message) {
      const container = document.getElementById('taskAssignmentsContainer');
      container.innerHTML = `
        <div class="alert alert-warning text-center">
          <i class="bi bi-exclamation-triangle me-2"></i>${message}
        </div>
      `;
    }

    function formatAssignmentDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
      });
    }

    function getStatusColor(status) {
      switch(status) {
        case 'assigned': return 'primary';
        case 'in_progress': return 'warning';
        case 'completed': return 'success';
        case 'not_completed': return 'danger';
        default: return 'secondary';
      }
    }

    function getDifficultyColor(difficulty) {
      switch(difficulty) {
        case 'easy': return 'success';
        case 'medium': return 'warning';
        case 'hard': return 'danger';
        default: return 'secondary';
      }
    }
  </script>

  <!-- My Task Assignments Modal -->
  <div class="modal fade" id="myTaskAssignmentsModal" tabindex="-1" aria-labelledby="myTaskAssignmentsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
      <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <div class="modal-header" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border-bottom: none; border-radius: 15px 15px 0 0;">
          <h5 class="modal-title" id="myTaskAssignmentsModalLabel">
            <i class="bi bi-person-check me-2"></i>My Task Assignments
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 30px; background: #f8f9fa; max-height: 70vh; overflow-y: auto;">
          <!-- Assignment Info -->
          <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
            <div class="d-flex align-items-center">
              <i class="bi bi-info-circle me-3" style="font-size: 1.5rem;"></i>
              <div>
                <h6 class="mb-1" style="font-weight: 600;">Your Task Assignments</h6>
                <p class="mb-0">View all your assigned tasks across different areas. Your name will be highlighted for easy identification.</p>
              </div>
            </div>
          </div>

          <!-- Task Assignments Container -->
          <div id="taskAssignmentsContainer">
            <!-- Task assignments will be loaded here -->
          </div>
        </div>
        
        <div class="modal-footer border-0" style="padding: 25px; background: white; border-radius: 0 0 15px 15px;">
          <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal" style="border-radius: 12px; padding: 12px 25px;">
            <i class="bi bi-x-circle me-2"></i>Close
          </button>
          <button type="button" class="btn btn-primary btn-lg" onclick="loadMyTaskAssignments()" style="border-radius: 12px; padding: 12px 25px;">
            <i class="bi bi-arrow-clockwise me-2"></i>Refresh
          </button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
