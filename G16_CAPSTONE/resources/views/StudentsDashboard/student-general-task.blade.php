
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
      /* reserve space for the fixed header so content and sidebar are not hidden */
      padding-top: 60px;
      overflow-x: hidden;
    }
    
    /* Prevent any unwanted overlay elements */
    body::before,
    body::after,
    *::before,
    *::after {
      pointer-events: none;
    }
    
    /* Reset any problematic pseudo-elements */
    .main-content::before,
    .main-content::after {
      display: none !important;
    }

    /* Header layout - move logout to right side */
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

    .logo {
      display: flex;
      align-items: center;
    }

    .logo img {
      height: 40px;
      width: auto;
    }

    /* Logout container - positioned on right */
    .logout-container {
      display: flex;
      align-items: center;
      margin-left: auto;
    }

    .logout-btn {
      background: rgba(255, 255, 255, 0.2);
      border: 2px solid rgba(255, 255, 255, 0.3);
      border-radius: 12px;
      padding: 12px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(10px);
      -webkit-backdrop-filter: blur(10px);
    }

    .logout-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      border-color: rgba(255, 255, 255, 0.5);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .logout-btn:active {
      transform: translateY(0);
      box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    }

    .logout-btn svg {
      width: 24px;
      height: 24px;
      transition: transform 0.2s ease;
    }

    .logout-btn:hover svg {
      transform: scale(1.1);
    }

    .row {
      font-family: 'Poppins', sans-serif;
      font-size: 20px;
      font-weight: 450; 
    }




    /* ===== NAVIGATION STYLES ===== */
    .nav-buttons {
      margin-top: 15px;
    }

    .nav-btn {
      background-color: rgba(255,255,255,0.2);
      color: white !important;
      border: 2px solid rgba(255,255,255,0.3);
      padding: 10px 25px;
      border-radius: 25px;
      font-weight: 600;
      text-decoration: none;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
    }

    .nav-btn:hover {
      background-color: rgba(255,255,255,0.3);
      border-color: rgba(255,255,255,0.6);
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
      color: white !important;
    }

    .nav-btn.active {
      background-color: white;
      color: #22BBEA !important;
      border-color: white;
      font-weight: 700;
    }

    .nav-btn.active:hover {
      background-color: #f8f9fa;
      color: #22BBEA !important;
    }


    /* ===== SHARED SIDEBAR STYLES (matches partials.sidebar) ===== */
    .sidebar-container {
      width: 300px !important;
      background: #ffffff !important;
      color: #374151 !important;
      padding: 0 !important;
      position: fixed !important;
      top: 60px !important;
      left: 0 !important;
      height: calc(100vh - 60px) !important;
      overflow-y: auto !important;
      z-index: 90 !important;
      border-right: 3px solid #22BBEA !important;
    }

    .sidebar-container .nav-link {
      color: #374151 !important;
      font-weight: 400 !important;
      font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
      font-size: 15px !important;
    }

    .sidebar-container .nav-link img { 
      vertical-align: middle !important; 
      width:26px !important; 
      height:26px !important; 
      margin-right:14px !important; 
    }

    .sidebar-container .nav-link:hover {
      background: #f1f5f9 !important;
      color: #111827 !important;
    }

    .sidebar-container .nav-link.active,
    .sidebar-container .nav-link.active:hover {
      background: #e0f2fe !important;
      color: #0f172a !important;
      font-weight: 400 !important;
    }

    .sidebar-icon {
      font-family: 'Poppins', sans-serif;
      width: 20px;
      height: 20px;
      margin-right: 8px;
      vertical-align: middle;
    }

    .sidebar h3 {
      margin-top: 0;
    }

    .sidebar ul {
      list-style: none;
      padding-left: 0;
    }

    .sidebar ul li {
      margin: 15px 0;
    }

    .sidebar ul li:first-child {
      margin-top: 20px; /* proper spacing from top */
    }

    .sidebar ul li a {
      font-size: 16px;
      text-decoration: none;
      color: black;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px 16px;
    }

    .sidebar ul li:hover {
      background: #fa5408;
      color: white;
      max-width: 100%;
      border-radius: 5px;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    .sidebar ul li:hover a {
      color: white;
      transition: color 0.3s ease;
    }

    /* ===== CONTENT STYLES ===== */
    .content {
      flex: 1;
      padding: 30px;
      margin-top: 0;
      padding-top: 20px;
      font-display: center;
    }

    /* Main content layout and styling */
    .main-content {
      border: none !important;
      border-bottom: none !important;
      padding: 20px 40px;
      min-height: calc(100vh - 60px);
      margin-left: 250px;
      background: #eef2f7;
      overflow-y: auto;
      width: calc(100% - 250px);
      position: relative;
      z-index: 1;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
      justify-content: flex-start;
    }
    
    /* Wrapper for all content to align left */
    .main-content > * {
      width: 100%;
      max-width: none;
      margin: 0;
      padding-left: 20px;
    }
    
    /* Ensure no overlay elements interfere */
    .main-content * {
      position: relative;
      z-index: 2;
    }

    .container-fluid {
      border: none !important;
      border-bottom: none !important;
      padding: 0;
      margin: 0;
    }

    body {
      border-bottom: none !important;
    }

    /* Force left alignment for all content */
    .justify-content-center {
      justify-content: flex-start !important;
    }
    
    .category-card.text-center,
    .main-content .text-center:not(th):not(.table th):not(.modal-header) {
      text-align: left !important;
    }
    
    .mx-auto {
      margin-left: 0 !important;
      margin-right: 0 !important;
    }

    /* Responsive design for main content */
    @media (max-width: 768px) {
      .main-content {
        margin-left: 0 !important;
        width: 100% !important;
        padding: 15px 20px !important;
      }
      
      .main-content > * {
        max-width: 100% !important;
        padding-left: 10px !important;
      }
    }

    .dashboard-title {
      font-weight: 700;
      color: #333;
    }
    .category-card {
      position: relative;
      border: none;
      border-radius: 20px;
      padding: 25px;
      transition: all 0.4s ease;
      color: #333;
      box-shadow: 0 8px 25px rgba(0,0,0,0.08);
      cursor: pointer;
      user-select: none;
      max-height: 300px;
      overflow-y: auto;
      backdrop-filter: blur(10px);
    }

    /* Individual Pastel Colors for Each Category */
    .category-card:nth-child(1) {
      background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 50%, #f48fb1 100%);
      border: 2px solid #f8bbd9;
    }

    .category-card:nth-child(2) {
      background: linear-gradient(135deg, #e3f2fd 0%, #90caf9 50%, #64b5f6 100%);
      border: 2px solid #90caf9;
    }

    .category-card:nth-child(3) {
      background: linear-gradient(135deg, #e8f5e8 0%, #a5d6a7 50%, #81c784 100%);
      border: 2px solid #a5d6a7;
    }

    .category-card:nth-child(4) {
      background: linear-gradient(135deg, #fff8e1 0%, #ffcc02 50%, #ffc107 100%);
      border: 2px solid #ffcc02;
    }

    .category-card:nth-child(5) {
      background: linear-gradient(135deg, #f3e5f5 0%, #ce93d8 50%, #ba68c8 100%);
      border: 2px solid #ce93d8;
    }

    .category-card:nth-child(6) {
      background: linear-gradient(135deg, #fff3e0 0%, #ffab91 50%, #ff8a65 100%);
      border: 2px solid #ffab91;
    }
    .category-label {
      font-size: 22px;
      font-weight: 800;
      color: #2c3e50;
      margin-bottom: 15px;
      padding-bottom: 8px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: relative;
    }

    .category-label::after {
      content: '';
      position: absolute;
      bottom: 0;
      left: 0;
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, rgba(255,255,255,0.8), rgba(255,255,255,0.4), rgba(255,255,255,0.8));
      border-radius: 2px;
    }
    .task-list {
      list-style-type: none;
      padding-left: 0;
      margin-bottom: 0;
      font-size: 9px;
      max-height: 180px;
      overflow-y: auto;
    }
    .task-list li {
      padding: 3px 0;
      border-bottom: 1px solid #ccc3;
    }
    .btn-custom {
      border-radius: 30px;
      padding: 8px 20px;
      font-weight: 500;
    }
    .table-container {
      background: white;
      padding: 20px;
      border-radius: 15px;
      box-shadow: 0 0 15px rgba(0,0,0,0.05);
    }
    .student-row td {
      vertical-align: middle;
    }
    .category-card:hover {
      transform: translateY(-12px) scale(1.03);
      box-shadow: 0 15px 40px rgba(0,0,0,0.2);
    }

    /* Enhanced hover effects for each category */
    .category-card:nth-child(1):hover {
      box-shadow: 0 15px 40px rgba(244, 143, 177, 0.4);
    }

    .category-card:nth-child(2):hover {
      box-shadow: 0 15px 40px rgba(100, 181, 246, 0.4);
    }

    .category-card:nth-child(3):hover {
      box-shadow: 0 15px 40px rgba(129, 199, 132, 0.4);
    }

    .category-card:nth-child(4):hover {
      box-shadow: 0 15px 40px rgba(255, 204, 2, 0.4);
    }

    .category-card:nth-child(5):hover {
      box-shadow: 0 15px 40px rgba(186, 104, 200, 0.4);
    }

    .category-card:nth-child(6):hover {
      box-shadow: 0 15px 40px rgba(255, 171, 145, 0.4);
    }
    nav.navbar-custom {
      background-color: #0d6efd;
      color: white;
    }
    nav.navbar-custom .navbar-brand,
    nav.navbar-custom .nav-link {
      color: white;
      font-weight: 600;
    }
    nav.navbar-custom .nav-link:hover {
      color: #ffd43b;
    }

    /* Centered Form Modal */
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0,0,0,0.4);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
    .modal-content {
      background: white;
      padding: 25px;
      border-radius: 15px;
      width: 1400px;
      max-width: 95vw;
      margin: 0 auto;
      box-shadow: 0 8px 20px rgba(0,0,0,0.2);
      position: relative;
      left: 50%;
      transform: translateX(-50%);
    }
    .close-btn {
      position: absolute;
      top: 12px;
      right: 15px;
      background: #dc3545;
      border: none;
      color: white;
      font-weight: 700;
      font-size: 18px;
      width: 28px;
      height: 28px;
      border-radius: 50%;
      line-height: 22px;
      cursor: pointer;
      user-select: none;
      transition: background-color 0.3s ease;
    }
    .close-btn:hover {
      background: #a71d2a;
    }

    /* Student Assignment Modal */
    #studentAssignModal {
      display: none;
      align-items: center;
      justify-content: center;
    }
    #studentAssignModal .modal-content {
      width: 1400px !important;
      max-width: 98vw;
      min-height: 420px;
      transition: width 0.3s, min-height 0.3s;
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: stretch;
      font-size: 1.15rem;
    }
    #studentAssignModal .modal-content .fw-bold {
      font-size: 1.3rem;
    }
    #studentAssignModal .modal-content .coordinator-label {
      font-weight: 500;
      margin-bottom: 8px;
      font-size: 1.1rem;
      color: #444;
      text-align: left;
    }
    #studentAssignModal .modal-content .coordinator-name {
      font-weight: 400;
      font-size: 1.15rem;
      margin-left: 3px;
      color: #222;
    }
    #studentAssignModal .modal-content ul.list-group-flush li {
      font-size: 1.08rem;
      padding: 8px 0;
    }

    /* Task Assignment History Modal */
    #historyModal.show {
      display: flex !important;
      align-items: center;
      justify-content: center;
    }
    #historyModal .modal-dialog {
      margin: 1.75rem auto;
      max-width: 95%;
      width: 95%;
    }
    #historyModal .modal-content {
      width: 100%;
      max-width: 2000px;
      position: relative;
      margin: 0 auto;
    }

    /* Add spacing to history modal table cells */
    #historyModal .table-bordered td {
      padding: 15px !important;
    }

    #historyModal .table-sm td {
      padding: 8px 12px !important;
      border: 1px solid #dee2e6 !important;
    }

    #historyModal .table-sm th {
      padding: 10px 12px !important;
      border: 1px solid #dee2e6 !important;
    }

    /* Timeline Styles */
    .timeline-item {
      position: relative;
      padding-left: 30px;
      margin-bottom: 20px;
    }
    .timeline-item .dot {
      position: absolute;
      left: 0;
      top: 5px;
      width: 10px;
      height: 10px;
      border-radius: 50%;
    }
    .dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
    .dot-blue { background: #0d6efd; }
    .dot-pink { background: #e83e8c; }
    .dot-green { background: #28a745; }
    .timeline-item { background: #f8f9fa; border-radius: 10px; padding: 12px 16px; }

    /* Custom container width for maximum space utilization */
    .container-fluid {
      --bs-gutter-x: 1rem;
      --bs-gutter-y: 0;
      max-width: 100vw;
      width: 100%;
      padding-right: calc(var(--bs-gutter-x) * .5);
      padding-left: calc(var(--bs-gutter-x) * .5);
      margin: 0;
    }

    /* Optimize main content spacing */
    main.col-md-10 {
      padding: 15px !important;
      margin: 0;
    }

    /* Optimize row and column spacing */
    .row {
      --bs-gutter-x: 1rem;
      --bs-gutter-y: 1rem;
      margin: 0;
    }

    .col-lg-3, .col-md-4, .col-sm-6 {
      padding-left: 3px;
      padding-right: 3px;
      margin-bottom: 10px;
    }

    /* Reduce unnecessary margins */
    .dashboard-title {
      margin-bottom: 10px !important;
    }

    .text-muted.mb-4 {
      margin-bottom: 20px !important;
    }

    /* Edit Button with Blue Line Styling */
    .btn-edit-blue {
      background: linear-gradient(135deg, #007bff, #0056b3);
      border: 2px solid #007bff;
      color: white;
      padding: 8px 20px;
      border-radius: 6px;
      font-weight: 600;
      font-size: 9px;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .btn-edit-blue:before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
      transition: left 0.5s;
    }

    .btn-edit-blue:hover {
      background: linear-gradient(135deg, #0056b3, #004085);
      border-color: #0056b3;
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }

    .btn-edit-blue:hover:before {
      left: 100%;
    }

    .btn-edit-blue:active {
      transform: translateY(0);
      box-shadow: 0 2px 6px rgba(0, 123, 255, 0.2);
    }

    /* Coordinator highlighting styles for View Members modal */
    .coordinator-highlight-cell {
      background-color: #f5e6a3 !important;
      border: 1px solid #d4c69a !important;
      padding: 8px !important;
    }

    .coordinator-name {
      font-weight: 600 !important;
      color: #856404 !important;
    }

    /* Coordinator highlighting for View History rows */
    .coordinator-highlight-row {
      background-color: #fff3cd !important;
      border: 1px solid #ffeaa7 !important;
      padding: 4px 8px !important;
      border-radius: 4px !important;
      margin-bottom: 2px !important;
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
    }

    /* Make all member names in View History single line */
    .modal-body .row .col div {
      white-space: nowrap !important;
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      line-height: 1.4 !important;
      margin-bottom: 2px !important;
    }

    /* Coordinator highlighting for Edit Members modal */
    .coordinator-highlight-edit {
      background-color: #f5e6a3 !important;
      border: 1px solid #d4c69a !important;
      border-radius: 6px !important;
    }

    .coordinator-highlight-edit:hover {
      border-color: #ff9800 !important;
      box-shadow: 0 2px 8px rgba(255, 193, 7, 0.5) !important;
    }

    /* Coordinator highlighting styles */
    .coordinator-highlight-cell {
      background-color: #f5e6a3 !important;
      border: 1px solid #d4c69a !important;
    }

    .coordinator-name {
      font-weight: 600 !important;
      color: #856404 !important;
    }

    /* Edit Member Styles */
    .member-list-container {
      max-height: 400px;
      overflow-y: auto;
      padding-right: 10px;
    }

    .member-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 15px;
      margin-bottom: 8px;
      background-color: #ffffff;
      border-radius: 6px;
      border: 1px solid #e0e0e0;
      transition: all 0.2s ease;
    }

    .member-row:hover {
      border-color: #007bff;
      box-shadow: 0 1px 4px rgba(0,123,255,0.15);
    }

    .member-info {
      flex: 1;
    }

    .member-name {
      font-weight: 500;
      color: #333;
      margin-bottom: 4px;
      font-size: 0.95em;
    }

    .member-comment {
      font-size: 0.85em;
      color: #666;
      font-style: italic;
      margin-top: 4px;
    }

    .comment-edit-area {
      margin-top: 8px;
    }

    .comment-input {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 0.9em;
    }

    .member-actions {
      display: flex;
      gap: 8px;
      margin-left: 15px;
    }

    .btn-edit-small, .btn-save-small {
      padding: 6px 16px;
      font-size: 0.8em;
      border-radius: 4px;
      border: none;
      cursor: pointer;
      transition: all 0.2s ease;
      font-weight: 500;
      min-width: 50px;
    }

    .btn-edit-small {
      background: #17a2b8;
      color: white;
    }

    .btn-edit-small:hover {
      background: #138496;
    }

    .btn-save-small {
      background: #28a745;
      color: white;
    }

    .btn-save-small:hover {
      background: #1e7e34;
    }

    .btn-save-small:disabled {
      background: #6c757d;
      cursor: not-allowed;
    }

    .comment-input {
      width: 100%;
      padding: 4px 8px;
      border: 1px solid #ddd;
      border-radius: 4px;
      font-size: 11px;
      margin-top: 4px;
    }

    .member-comment {
      font-size: 9px;
      color: #666;
      font-style: italic;
      margin-top: 2px;
    }

    /* ===== PASTEL TASK CARD COLORS ===== */
    :root {
      --pastel-pink: #fce4ec;
      --pastel-blue: #e3f2fd;
      --pastel-green: #e8f5e8;
      --pastel-yellow: #fff8e1;
      --pastel-purple: #f3e5f5;
      --pastel-orange: #fff3e0;
      --pastel-teal: #e0f2f1;
      --pastel-lavender: #f8f4ff;
      --pastel-peach: #ffeee6;
      --pastel-mint: #e8f8f5;
      --pastel-coral: #ffe8e8;
      --pastel-sky: #e6f3ff;
    }

    /* Task Card Pastel Styling - Soft & Easy on Eyes */
    .col-lg-3:nth-child(1) .category-card > div {
      background: linear-gradient(135deg, #fce4ec 0%, #f8bbd9 100%) !important;
      border: 1px solid #f8bbd9 !important;
      box-shadow: 0 4px 15px rgba(248, 187, 217, 0.2) !important;
      transition: all 0.3s ease !important;
    }

    .col-lg-3:nth-child(1) .category-card > div:hover {
      transform: translateY(-3px) !important;
      box-shadow: 0 8px 25px rgba(248, 187, 217, 0.3) !important;
    }

    .col-lg-3:nth-child(1) .category-label {
      color: #ad1457 !important;
    }

    .col-lg-3:nth-child(2) .category-card > div {
      background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%) !important;
      border: 1px solid #bbdefb !important;
      box-shadow: 0 4px 15px rgba(187, 222, 251, 0.2) !important;
      transition: all 0.3s ease !important;
    }

    .col-lg-3:nth-child(2) .category-card > div:hover {
      transform: translateY(-3px) !important;
      box-shadow: 0 8px 25px rgba(187, 222, 251, 0.3) !important;
    }

    .col-lg-3:nth-child(2) .category-label {
      color: #1565c0 !important;
    } 

    .col-lg-3:nth-child(3) .category-card > div {
      background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c9 100%) !important;
      border: 1px solid #c8e6c9 !important;
      box-shadow: 0 4px 15px rgba(200, 230, 201, 0.2) !important;
      transition: all 0.3s ease !important;
    }

    .col-lg-3:nth-child(3) .category-card > div:hover {
      transform: translateY(-3px) !important;
      box-shadow: 0 8px 25px rgba(200, 230, 201, 0.3) !important;
    }

    .col-lg-3:nth-child(3) .category-label {
      color: #2e7d32 !important;
    }

    .col-lg-3:nth-child(4) .category-card > div {
      background: linear-gradient(135deg, #fff8e1 0%, #fff3c4 100%) !important;
      border: 1px solid #fff3c4 !important;
      box-shadow: 0 4px 15px rgba(255, 243, 196, 0.2) !important;
      transition: all 0.3s ease !important;
    }

    .col-lg-3:nth-child(4) .category-card > div:hover {
      transform: translateY(-3px) !important;
      box-shadow: 0 8px 25px rgba(255, 243, 196, 0.3) !important;
    }

    .col-lg-3:nth-child(4) .category-label {
      color: #f57c00 !important;
    }

    .col-lg-3:nth-child(5) .category-card > div {
      background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%) !important;
      border: 1px solid #e1bee7 !important;
      box-shadow: 0 4px 15px rgba(225, 190, 231, 0.2) !important;
      transition: all 0.3s ease !important;
    }

    .col-lg-3:nth-child(5) .category-card > div:hover {
      transform: translateY(-3px) !important;
      box-shadow: 0 8px 25px rgba(225, 190, 231, 0.3) !important;
    }

    .col-lg-3:nth-child(5) .category-label {
      color: #6a1b9a !important;
    }

    .col-lg-3:nth-child(6) .category-card > div {
      background: linear-gradient(135deg, #fff3e0 0%, #ffcc80 100%) !important;
      border: 1px solid #ffcc80 !important;
      box-shadow: 0 4px 15px rgba(255, 204, 128, 0.2) !important;
      transition: all 0.3s ease !important;
    }

    .col-lg-3:nth-child(6) .category-card > div:hover {
      transform: translateY(-3px) !important;
      box-shadow: 0 8px 25px rgba(255, 204, 128, 0.3) !important;
    }

    .col-lg-3:nth-child(6) .category-label {
      color: #ef6c00 !important;
    }

    /* Enhanced badge styling for pastel cards */
    .category-card .badge {
      border-radius: 20px !important;
      padding: 8px 15px !important;
      font-size: 14px !important;
      font-weight: 700 !important;
      margin: 0 5px !important;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1) !important;
    }

    /* Enhanced button styling for pastel cards */
    .category-card .btn {
      border-radius: 25px !important;
      padding: 10px 20px !important;
      font-weight: 600 !important;
      transition: all 0.3s ease !important;
      box-shadow: 0 4px 1    /* Task Checklist Modal Styles - Enhanced Tabular Format */
    .table {
      border-collapse: collapse !important;
      margin: 0 !important;
      background-color: #ffffff !important;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    /* Enhanced table headers */
    .table thead th {
      background-color: transparent !important;
      border: 1px solid #dee2e6 !important;
      font-weight: 600 !important;
      text-align: center !important;
      padding: 12px 8px !important;
      color: #333 !important;
      font-size: 13px !important;
    }

    /* Task Checklist Modal - Remove ONLY header backgrounds, keep category colors */
    #taskChecklistModal .table thead th {
      background-color: transparent !important;
      background: none !important;
      border: 1px solid #dee2e6 !important;
      color: #333 !important;
    }

    /* Override Bootstrap table-bordered default styling for task checklist headers only */
    #taskChecklistModal .table-bordered thead th {
      background-color: transparent !important;
      background: none !important;
      border: 1px solid #dee2e6 !important;
    }

    /* Remove any Bootstrap table header backgrounds but keep category cell colors */
    #taskChecklistModal .table > thead > tr > th {
      background-color: transparent !important;
      background: none !important;
      border: 1px solid #dee2e6 !important;
    }

    /* Keep category cell colors - DO NOT override these */
    #taskChecklistModal .table tbody td[style*="background-color: #90EE90"],
    #taskChecklistModal .table tbody td[style*="background: linear-gradient"] {
      /* Keep original background colors for category cells */
    }

    /* Vertical text rotation for category cells */
    .vertical-text {
      writing-mode: vertical-lr;
      text-orientation: mixed;
      transform: rotate(180deg);
      white-space: nowrap;
    }

    /* Category headers with better styling */
    .table thead th.category-header {
      background-color: transparent !important;
      font-weight: 600 !important;
      font-size: 14px !important;
      color: #333 !important;
    }

    /* Week headers */
    .table thead th.week-header {
      background-color: transparent !important;
      font-weight: 600 !important;
      font-size: 13px !important;
      color: #333 !important;
    }

    /* Day headers */
    .table thead th.day-header {
      background-color: transparent !important;
      font-weight: 600 !important;
      font-size: 11px !important;
      color: #333 !important;
    }

    .table td, .table th {
      border: 1px solid #000 !important;
      padding: 2px !important;
      vertical-align: middle !important;
      line-height: 1.2 !important;
    }

    .status-buttons {
      display: flex !important;
      gap: 2px !important;
      justify-content: center !important;
      align-items: center !important;
    }

    .status-btn {
      width: 30px !important;
      height: 30px !important;
      border: 2px solid #000 !important;
      background: white !important;
      border-radius: 4px !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
      font-size: 12px !important;
      font-weight: bold !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }

    .status-btn:hover {
      background: #e3f2fd !important;
      border-color: #007bff !important;
      box-shadow: 0 0 0 2px rgba(0,123,255,0.25) !important;
    }

    .status-btn.completed {
      background: #28a745 !important;
      color: white !important;
      border-color: #28a745 !important;
    }

    .status-btn.pending {
      background: #ffc107 !important;
      color: #212529 !important;
      border-color: #ffc107 !important;
    }

    .status-btn.not-started {
      background: #dc3545 !important;
      color: white !important;
      border-color: #dc3545 !important;
    }

    /* Task description cells */
    .task-description-cell {
      padding: 12px !important;
      border: 1px solid #333 !important;
      width: 300px !important;
      vertical-align: middle !important;
      background-color: #f8f9fa !important;
      font-size: 16px !important;
      line-height: 1.4 !important;
      color: #000000 !important;
      font-weight: normal !important;
    }

    /* Checkbox cells styling */
    .checkbox-cell {
      background-color: #fafafa !important;
      border: 1px solid #333 !important;
      padding: 6px !important;
      text-align: center !important;
      vertical-align: middle !important;
    }

    /* Single checkbox styling */
    .single-checkbox {
      border: 2px solid #333 !important;
      background-color: #ffffff !important;
      border-radius: 4px !important;
      transition: all 0.2s ease !important;
    }

    .single-checkbox:hover {
      border-color: #007bff !important;
      box-shadow: 0 0 0 2px rgba(0,123,255,0.25) !important;
    }

    /* Remarks cells - Enhanced Tabular Format */
    .remarks-cell {
      padding: 12px !important;
      border: 1px solid #333 !important;
      width: 400px !important;
      vertical-align: middle !important;
      background-color: #fff5f5 !important;
    }

    /* Category cells - Enhanced styling */
    .category-cell {
      border: 2px solid #333 !important;
      font-weight: bold !important;
      text-align: center !important;
      vertical-align: middle !important;
      padding: 12px 8px !important;
      line-height: 1.3 !important;
    }

    /* Remarks input styling */
    .remarks-input {
      width: 100% !important;
      height: 60px !important;
      border: 1px solid #ccc !important;
      border-radius: 3px !important;
      padding: 12px !important;
      font-size: 16px !important;
      background-color: #ffffff !important;
      resize: vertical !important;
      font-family: Arial, sans-serif !important;
    }

    .remarks-input:focus {
      border-color: #007bff !important;
      box-shadow: 0 0 0 2px rgba(0,123,255,0.25) !important;
      outline: none !important;
    }

    /* Table row styling */
    .table tbody tr {
      border-bottom: 1px solid #dee2e6 !important;
    }

    /* Kitchen Assignment Modal Centering */
    #kitchenAssignmentModal .modal-dialog {
      max-width: 900px;
      margin: 1.75rem auto;
    }
    
    #kitchenAssignmentModal .modal-content {
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    #kitchenAssignmentModal .assignment-grid {
      max-width: 100%;
      overflow-x: auto;
    }
    /* Food Preparation task styling for Kitchen modal */
    #kitchenAssignmentModal .task-label.prep {
      background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%) !important;
      color: white !important;
      font-size: 10px !important;
      box-shadow: 0 6px 20px rgba(78, 205, 196, 0.3) !important;
    }

    @media (max-width: 768px) {
      #kitchenAssignmentModal .modal-dialog {
        max-width: 95%;
        margin: 1rem auto;
      }
    }

    /* Enhanced Add Task Area Modal Styles */
    .area-type-card {
      transition: all 0.3s ease !important;
    }
    
    .area-type-card:hover {
      border-color: #007bff !important;
      background-color: #f8f9ff !important;
      transform: translateY(-2px) !important;
      box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15) !important;
    }
    
    .area-type-card input[type="radio"]:checked + label {
      color: #007bff !important;
    }
    
    .area-type-card:has(input[type="radio"]:checked) {
      border-color: #007bff !important;
      background-color: #f8f9ff !important;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1) !important;
    }
    
    #addTaskAreaModal .form-control:focus,
    #addTaskAreaModal .form-select:focus {
      border-color: #007bff !important;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1) !important;
    }
    
    #addTaskAreaModal .btn-primary {
      background: linear-gradient(135deg, #007bff 0%, #0056b3 100%) !important;
      border: none !important;
      transition: all 0.3s ease !important;
    }
    
    #addTaskAreaModal .btn-primary:hover {
      transform: translateY(-2px) !important;
      box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3) !important;
    }
    
    #addTaskAreaModal .btn-primary:disabled {
      background: #6c757d !important;
      transform: none !important;
      box-shadow: none !important;
    }

    /* Enhanced delete button styling */
    .delete-capacity-btn {
      transition: all 0.3s ease !important;
    }
    
    .delete-capacity-btn:hover {
      color: #dc3545 !important;
      transform: scale(1.1) !important;
      text-shadow: 0 0 8px rgba(220, 53, 69, 0.5) !important;
    }
    
    .delete-capacity-btn:disabled {
      opacity: 0.6 !important;
      transform: none !important;
      pointer-events: none !important;
    }

    /* Deletion animation styles */
    .deleting-card {
      animation: deleteAnimation 0.6s ease-in-out forwards !important;
    }
    
    @keyframes deleteAnimation {
      0% {
        opacity: 1;
        transform: scale(1) translateY(0);
      }
      50% {
        opacity: 0.5;
        transform: scale(0.9) translateY(-10px);
      }
      100% {
        opacity: 0;
        transform: scale(0.8) translateY(-20px);
      }
    }
  </style>

    <script>
      // Legacy capacity helper removed to avoid conflicts with the new Edit Task modal and consolidated handlers.
      // Kept intentionally empty to prevent runtime errors from missing DOM elements.
    </script>

  <!-- Inserted bottom dashboard block (dynamic) -->
  @php
    $totalTasks = $assignments->count();
    $activeTasks = $assignments->where('status', 'current')->count();
    $pendingTasks = $assignments->where('status', 'pending')->count();
    $completedTasks = $assignments->where('status', 'completed')->count();
    $assignedStudentIds = [];
    foreach($assignments as $a) {
      if (!empty($a->assignmentMembers) && is_iterable($a->assignmentMembers)) {
        foreach($a->assignmentMembers as $m) {
          if($m->student) $assignedStudentIds[] = $m->student->id ?? ($m->student_id ?? null);
        }
      }
    }
    $assignedStudents = count(array_unique(array_filter($assignedStudentIds)));
  @endphp

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
      <button type="button" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
              class="logout-btn" title="Log Out">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <path d="M17 7L15.59 8.41L18.17 11H8V13H18.17L15.59 15.59L17 17L22 12L17 7Z" fill="white"/>
          <path d="M4 5H12V3H4C2.9 3 2 3.9 2 5V19C2 20.1 2.9 21 4 21H12V19H4V5Z" fill="white"/>
        </svg>
      </button>
    </div>
</header>
<div class="container-fluid">
  <div class="row">
    <!-- Student Sidebar -->
    <nav class="sidebar-container">
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
    <div class="main-content">
          <!-- Flash Messages -->
          @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
              <h2 class="dashboard-title mb-1" style="font-size:1.4rem;">General Task Assignments</h2>
              <p class="text-muted mb-0" style="font-size:0.85rem;">Manage and track all task assignments across categories</p>
              
            </div>
            @if(auth()->check() && in_array(auth()->user()->user_role, ['educator', 'inspector']))
            <div class="d-flex gap-2">
              <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#taskChecklistModal">
                <i class="bi bi-list-task me-2"></i>General Task Checklist
              </button> 
            </div>
            @endif
          </div>

          <!-- Removed legacy Task Categories & Descriptions table per request. Keep only dynamic student/task cards below. -->

          {{-- Dynamic category structure: Main Areas contain Sub Areas (task cards) --}}
          @php
            // Group categories by main area (parent_id = null) and their sub-areas (parent_id != null)
            $mainAreas = $categories->whereNull('parent_id');
            $subAreas = $categories->whereNotNull('parent_id');
            
            // Create dynamic structure
            $dynamicStructure = [];
            foreach($mainAreas as $mainArea) {
              $dynamicStructure[$mainArea->name] = $subAreas->where('parent_id', $mainArea->id);
            }
          @endphp

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
              
              // Check for batch requirements from session overrides
              $overrides = session('auto_shuffle_overrides', []);
              $overrideMales = null; $overrideFemales = null;
              if (isset($overrides[$categoryName])) {
                $info = $overrides[$categoryName];
                if (!empty($info['batch_requirements']) && is_array($info['batch_requirements'])) {
                  $overrideMales = 0; $overrideFemales = 0;
                  foreach ($info['batch_requirements'] as $y => $vals) {
                    $overrideMales += (int)($vals['boys'] ?? 0);
                    $overrideFemales += (int)($vals['girls'] ?? 0);
                  }
                  // If both sums are zero, treat as no override
                  if (($overrideMales + $overrideFemales) === 0) {
                    $overrideMales = null; $overrideFemales = null;
                  }
                }
              }
              
              // Decide what to display: use override sums when present, otherwise actual assigned counts
              $displayMales = $overrideMales !== null ? $overrideMales : $boys;
              $displayFemales = $overrideFemales !== null ? $overrideFemales : $girls;
              
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


            <!-- Container where newly added areas will be appended by JS -->
            <div id="addedAreasContainer"></div>

          <!-- Dynamic Cards Structure - Main Areas with Sub Area Task Cards -->
          @foreach($dynamicStructure as $mainAreaName => $subAreaCategories)
            @if($subAreaCategories->count() > 0)
            <div class="mb-2 mt-3">
              <h3 style="font-weight:600; color:#222; font-size:1.1rem; margin-bottom:12px;">{{ $mainAreaName }}</h3>
              <div class="row">
                @foreach($subAreaCategories as $cat)
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
                      <div class="category-card text-start p-0 overflow-hidden" style="background:none; border:none; box-shadow:none;">
                        @php
                          // Use the EXACT saved color so student view matches the chosen swatch
                          $baseColor = $cat->color_code ?? '#f8f9fa';
                          $hex = ltrim($baseColor, '#');
                          if (strlen($hex) === 3) { $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2]; }
                          $r = intval(substr($hex,0,2), 16);
                          $g = intval(substr($hex,2,2), 16);
                          $b = intval(substr($hex,4,2), 16);
                          $mix = function($c,$p){ return max(0, min(255, (int)round($c + (255 - $c) * $p))); };
                          // Primary stop is the exact color, second stop is slightly lighter for depth
                          $bg1 = $baseColor;
                          $bg2 = sprintf('#%02X%02X%02X', $mix($r, 0.18), $mix($g, 0.18), $mix($b, 0.18));
                          $borderCol = sprintf('#%02X%02X%02X', $mix($r, 0.10), $mix($g, 0.10), $mix($b, 0.10));
                          $cardBgStyle = "background: linear-gradient(135deg, {$bg1} 0%, {$bg2} 100%) !important; border: 1px solid {$borderCol} !important;";
                        @endphp
                        <div style="height:100%; min-height:180px; max-height:220px; border-radius:10px; padding:10px; {{ $cardBgStyle }} box-shadow: 0 0 6px rgba(0,0,0,0.05); position: relative;">
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
                                      }
                                      if (empty($batch) && !empty($member->student_code)) {
                                        // attempt to parse batch from codes like '2025010029C1' -> 2025
                                        if (preg_match('/^(20\d{2})/', $member->student_code, $m)) {
                                          $batch = (int)$m[1];
                                        }
                                      }
                                      // If no explicit gender saved on the member, attempt a heuristic from name suffix or leave null
                                      if (empty($g) && !empty($member->gender)) {
                                        $g = $member->gender;
                                      }
                                    }

                                    // Count genders when available (accept 'M'/'F' or 'Male'/'Female')
                                    if ($g === 'Male' || $g === 'M') { $boys++; }
                                    if ($g === 'Female' || $g === 'F') { $girls++; }

                                    // If coordinator flag is set, record coordinator name using the best-available fullname
                                    if($member->is_coordinator) {
                                      $coName = $fullName ?: ($member->student_code ?? null);
                                      // record per-batch coordinator if batch known
                                      if ($batch == 2025 && !$coor2025) $coor2025 = $coName;
                                      if ($batch == 2026 && !$coor2026) $coor2026 = $coName;
                                      // also capture any coordinator as fallback
                                      if (!$coor_any) { $coor_any = $coName; $coor_any_batch = $batch; }
                                    }
                                  }
                                }
                              }
                            @endphp
                            @php
                              $overrides = session('auto_shuffle_overrides', []);
                              $reqTotal = null;
                              $reqByBatch = null;
                              if (isset($overrides[$cat->name])) {
                                $info = $overrides[$cat->name];
                                if (!empty($info['batch_requirements']) && is_array($info['batch_requirements'])) {
                                  $sum = 0;
                                  foreach ($info['batch_requirements'] as $y => $vals) {
                                    $sum += (int)($vals['boys'] ?? 0) + (int)($vals['girls'] ?? 0);
                                  }
                                  $reqTotal = $sum > 0 ? $sum : null;
                                  $reqByBatch = $info['batch_requirements'];
                                } elseif (!empty($info['max_total'])) {
                                  $reqTotal = (int)$info['max_total'];
                                }
                              }

                              // If overrides exist, compute summed males/females from batch_requirements
                              $overrideMales = null; $overrideFemales = null;
                              if (is_array($reqByBatch)) {
                                $overrideMales = 0; $overrideFemales = 0;
                                foreach ($reqByBatch as $y => $vals) {
                                  $overrideMales += (int)($vals['boys'] ?? 0);
                                  $overrideFemales += (int)($vals['girls'] ?? 0);
                                }
                                // If both sums are zero, treat as no override
                                if (($overrideMales + $overrideFemales) === 0) {
                                  $overrideMales = null; $overrideFemales = null;
                                }
                              }

                              // Decide what to display: use override sums when present, otherwise actual assigned counts
                              $displayMales = $overrideMales !== null ? $overrideMales : $boys;
                              $displayFemales = $overrideFemales !== null ? $overrideFemales : $girls;

                              // Determine override or assignment dates for duration display
                              $overrideInfo = $overrides[$cat->name] ?? null;
                              $displayStart = null; $displayEnd = null;
                              if ($overrideInfo) {
                                if (!empty($overrideInfo['start_date'])) $displayStart = $overrideInfo['start_date'];
                                if (!empty($overrideInfo['end_date'])) $displayEnd = $overrideInfo['end_date'];
                              }
                              if (!$displayStart) $displayStart = optional($cat->assignments->where('status','current')->first())->start_date ?? null;
                              if (!$displayEnd) $displayEnd = optional($cat->assignments->where('status','current')->first())->end_date ?? null;
                            @endphp

                              @if($reqTotal)
                              <span class="badge" style="background:#28a745; color:#fff; font-weight:600; margin-left:8px;">Required students: {{ $reqTotal }}</span>
                            @endif
                            <span class="badge" style="background:#1565c0; color:#fff; font-weight:500; font-size:0.7rem; padding:3px 6px;">Male: {{ $displayMales }}</span>
                            <span class="badge" style="background:#f3f6fb; color:#222; font-weight:500; font-size:0.7rem; padding:3px 6px;">Female: {{ $displayFemales }}</span>

                            @if($displayStart || $displayEnd)
                              <div style="margin-top:5px; font-size:11px; color:#444;">
                                Valid: {{ $displayStart ? \Carbon\Carbon::parse($displayStart)->format('F j, Y') : '—' }} - {{ $displayEnd ? \Carbon\Carbon::parse($displayEnd)->format('F j, Y') : '—' }}
                              </div>
                            @endif
                            @if(auth()->check() && in_array(auth()->user()->user_role, ['educator', 'inspector']))
                            <div style="position: absolute; top: 12px; right: 12px;">
                              <a href="#" class="text-muted me-2 edit-capacity-btn" data-category-id="{{ $cat->id }}" data-category-name="{{ addslashes($cat->name) }}" data-current-boys="{{ $boys }}" data-current-girls="{{ $girls }}" data-current-start="{{ optional($cat->assignments->where('status','current')->first())->start_date ?? '' }}" data-current-end="{{ optional($cat->assignments->where('status','current')->first())->end_date ?? '' }}" title="Edit Capacity"><i class="bi bi-pencil" style="font-size: 1.05rem;"></i></a>
                              <a href="#" class="text-danger delete-assignment-btn delete-capacity-btn" data-category-id="{{ $cat->id }}" data-category-name="{{ addslashes($cat->name) }}" title="Delete Task Area Permanently"><i class="bi bi-trash" style="font-size: 1.05rem;"></i></a>
                            </div>
                            @endif
                          </div>
                          <div class="mb-2">
                            @php
                              // If a per-batch coordinator wasn't set, use coor_any only when its batch matches.
                              if (!$coor2025 && $coor_any && $coor_any_batch == 2025) $coor2025 = $coor_any;
                              if (!$coor2026 && $coor_any && $coor_any_batch == 2026) $coor2026 = $coor_any;
                            @endphp
                            @php
                              // Prefer coordinator names saved in session overrides metadata, then actual assigned coordinators,
                              // then fall back to any assigned member from the same batch, then any member in the assignment.
                              // This ensures the UI shows a specific person rather than 'TBD' when possible.
                              $meta = session('auto_shuffle_overrides', []);
                              $meta = isset($meta[$cat->name]['metadata']) ? $meta[$cat->name]['metadata'] : null;
                              $displayCoor2025 = $meta['coordinator_2025'] ?? $coor2025 ?? null;
                              $displayCoor2026 = $meta['coordinator_2026'] ?? $coor2026 ?? null;

                              // If still empty, try to derive from current assignment members
                              $currentAssign = optional($cat->assignments->where('status','current')->first());
                              if ($currentAssign) {
                                // helper to resolve member display name
                                $resolveMemberName = function($member) {
                                  if ($member->student) {
                                    return trim(($member->student->user_fname ?? '') . ' ' . ($member->student->user_lname ?? ''));
                                  }
                                  if (!empty($member->student_name)) return $member->student_name;
                                  if (!empty($member->student_code)) return $member->student_code;
                                  return null;
                                };

                                if (empty($displayCoor2025)) {
                                  // find first member in batch 2025
                                  if (!empty($currentAssign->assignmentMembers) && is_iterable($currentAssign->assignmentMembers)) {
                                    foreach ($currentAssign->assignmentMembers as $m) {
                                      $mbatch = optional($m->student)->studentDetail ? optional($m->student->studentDetail)->batch : null;
                                      if (empty($mbatch) && !empty($m->student_code) && preg_match('/^(20\d{2})/', $m->student_code, $mm)) {
                                        $mbatch = (int)$mm[1];
                                      }
                                      if ($mbatch == 2025) { $displayCoor2025 = $resolveMemberName($m); break; }
                                    }
                                  }
                                }

                                if (empty($displayCoor2026)) {
                                  // find first member in batch 2026
                                  if (!empty($currentAssign->assignmentMembers) && is_iterable($currentAssign->assignmentMembers)) {
                                    foreach ($currentAssign->assignmentMembers as $m) {
                                      $mbatch = optional($m->student)->studentDetail ? optional($m->student->studentDetail)->batch : null;
                                      if (empty($mbatch) && !empty($m->student_code) && preg_match('/^(20\d{2})/', $m->student_code, $mm)) {
                                        $mbatch = (int)$mm[1];
                                      }
                                      if ($mbatch == 2026) { $displayCoor2026 = $resolveMemberName($m); break; }
                                    }
                                  }
                                }

                                // Final fallback: use any member in the assignment
                                if (empty($displayCoor2025)) {
                                  $first = null;
                                  if (!empty($currentAssign->assignmentMembers) && is_object($currentAssign->assignmentMembers) && method_exists($currentAssign->assignmentMembers, 'first')) {
                                    $first = $currentAssign->assignmentMembers->first();
                                  }
                                  if ($first) $displayCoor2025 = $resolveMemberName($first);
                                }
                                if (empty($displayCoor2026)) {
                                  $first = null;
                                  if (!empty($currentAssign->assignmentMembers) && is_object($currentAssign->assignmentMembers) && method_exists($currentAssign->assignmentMembers, 'first')) {
                                    $first = $currentAssign->assignmentMembers->first();
                                  }
                                  if ($first) $displayCoor2026 = $resolveMemberName($first);
                                }
                              }

                              // If both displays resolved to the same name, try to pick a different person for 2026
                              // Prefer showing a distinct 2026 name (Class 2026) when possible per user request.
                              if ($displayCoor2025 && $displayCoor2026 && $displayCoor2025 === $displayCoor2026) {
                                // attempt to find another 2026 member name
                                if ($currentAssign && !empty($currentAssign->assignmentMembers) && is_iterable($currentAssign->assignmentMembers)) {
                                  foreach ($currentAssign->assignmentMembers as $m) {
                                    $mbatch = optional($m->student)->studentDetail ? optional($m->student->studentDetail)->batch : null;
                                    if (empty($mbatch) && !empty($m->student_code) && preg_match('/^(20\d{2})/', $m->student_code, $mm)) $mbatch = (int)$mm[1];
                                    $name = null;
                                    if ($m->student) $name = trim(($m->student->user_fname ?? '') . ' ' . ($m->student->user_lname ?? ''));
                                    elseif (!empty($m->student_name)) $name = $m->student_name;
                                    elseif (!empty($m->student_code)) $name = $m->student_code;
                                    if ($mbatch == 2026 && $name && $name !== $displayCoor2025) { $displayCoor2026 = $name; break; }
                                  }
                                }
                                // if still same, annotate the second to indicate same person across batches
                                if ($displayCoor2025 === $displayCoor2026) {
                                  $displayCoor2026 = $displayCoor2026 . ' (also C2025)';
                                }
                              }

                              // Final fallback to an explicit placeholder if still empty
                              $displayCoor2025 = $displayCoor2025 ?? '—';
                              $displayCoor2026 = $displayCoor2026 ?? '—';
                            @endphp
                            <div style="font-size:0.75rem;"><b>C2025 Coordinator:</b> {{ $displayCoor2025 }}</div>
                            <div style="font-size:0.75rem;"><b>C2026 Coordinator:</b> {{ $displayCoor2026 }}</div>
                            @php
                              // Determine description: prefer session override description, then category description
                              $displayDescription = null;
                              $overrides = session('auto_shuffle_overrides', []);
                              if (isset($overrides[$cat->name]) && !empty($overrides[$cat->name]['description'])) {
                                $displayDescription = $overrides[$cat->name]['description'];
                              } else {
                                $displayDescription = $cat->description ?? null;
                              }
                            @endphp
                            <div style="margin-top:5px; font-size:11px; color:#555;">
                              <em>{{ $displayDescription ?? 'No description provided.' }}</em>
                            </div>
                          </div>
                          <div class="d-flex justify-content-start gap-2 mt-2">
                            <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#studentAssignModal{{ $cat->id }}" style="font-size:0.7rem; padding:3px 6px;">View Members</button>
                            @if(auth()->check() && in_array(auth()->user()->user_role, ['educator', 'inspector']))
                            <button class="btn btn-primary btn-sm manage-tasks-btn" data-category-id="{{ $cat->id }}" data-category-name="{{ $cat->name }}" data-room-number="{{ $cat->name }}" style="font-size:0.7rem; padding:3px 6px;">Manage Tasks</button>
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

  <script>
    // Define user role for JavaScript access
    const isAdmin = @json(auth()->check() && in_array(auth()->user()->user_role, ['educator', 'inspector']));
    // Check if user is student to hide count numbers
    const isStudent = @json(auth()->check() && in_array(auth()->user()->user_role, ['student']));
    
    // Function to get assignment modal ID based on category name
    function getAssignmentModalId(categoryName) {
      const modalMap = {
        'Kitchen Operations Center': 'kitchenAssignmentModal',
        'Kitchen Dishwashing Station': 'dishwashingAssignmentModal',
        'Kitchen Dining Service Area': 'diningAssignmentModal',
        'Offices Room(s)': 'officeAssignmentModal',
        'Conference Rooms': 'conferenceAssignmentModal',
        'Ground Floor Common Areas': 'groundAssignmentModal',
        'Rooftop Waste Management Center': 'wasteAssignmentModal',
        'Rooftop Laundry Operations': 'laundryAssignmentModal'
      };
      return modalMap[categoryName] || 'kitchenAssignmentModal';
    }
    
    (function(){
      const areasList = document.getElementById('areasList');
      const alertEl = document.getElementById('manageAreasAlert');

      function showAlert(msg, type='success'){
        if (!alertEl) return; // Prevent null reference error
        alertEl.className = 'alert alert-' + type;
        alertEl.textContent = msg;
        alertEl.classList.remove('d-none');
        setTimeout(()=> alertEl.classList.add('d-none'), 3500);
      }

      async function loadAreas(){
        areasList.innerHTML = '<div class="text-muted">Loading...</div>';
        try{
          const res = await fetch('/categories', { headers: { 'Accept': 'application/json' } });
          const text = await res.text();
          // If this route returns HTML view, fallback to server-rendered categories
          let data = null;
          try{ data = JSON.parse(text); }catch(e){}
          let categories = data || window.initialCategories || [];
          // if categories is empty and the server returned HTML, parse via a fallback (not implemented)
          areasList.innerHTML = '';
          categories.forEach(c => {
            const item = document.createElement('div');
            item.className = 'list-group-item d-flex justify-content-between align-items-center';
            item.innerHTML = `
              <div>
                <div class="fw-bold">${escapeHtml(c.name)}</div>
                <div class="text-muted small">${escapeHtml(c.description || '')}</div>
              </div>
              <div>
                <button class="btn btn-sm btn-outline-primary me-2 btn-edit-area" data-id="${c.id}" data-name="${escapeAttr(c.name)}" data-desc="${escapeAttr(c.description || '')}">Edit</button>
                <button class="btn btn-sm btn-outline-danger btn-delete-area" data-id="${c.id}">Delete</button>
              </div>
            `;
            areasList.appendChild(item);
          });
          // wire buttons
          areasList.querySelectorAll('.btn-edit-area').forEach(b => b.addEventListener('click', onEditArea));
          areasList.querySelectorAll('.btn-delete-area').forEach(b => b.addEventListener('click', onDeleteArea));
        }catch(err){
          console.error(err);
          areasList.innerHTML = '<div class="text-danger">Failed to load areas</div>';
        }
      }

      function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>'"`]/g, function(ch){ return {'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;','`':'&#96;'}[ch]; }); }
      function escapeAttr(s){ if(!s) return ''; return String(s).replace(/"/g, '&quot;'); }

      document.getElementById('addAreaBtn').addEventListener('click', async function(){
        const name = document.getElementById('newAreaName').value.trim();
        const desc = document.getElementById('newAreaDesc').value.trim();
        if(!name) { showAlert('Area name required','danger'); return; }
        try{
          const res = await fetch('/categories', {
            method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ name: name, description: desc })
          });
          const j = await res.json();
          if(res.ok && j.success){
            showAlert('Area added');
            document.getElementById('newAreaName').value = '';
            document.getElementById('newAreaDesc').value = '';
            loadAreas();
          } else {
            showAlert(j.message || 'Failed to add','danger');
          }
        }catch(err){ console.error(err); showAlert('Failed to add','danger'); }
      });

      async function onEditArea(e){
        const btn = e.currentTarget;
        const id = btn.dataset.id;
        const name = btn.dataset.name || '';
        const desc = btn.dataset.desc || '';
        // Show inline edit form
        const row = btn.closest('.list-group-item');
        row.innerHTML = `
          <div class="flex-grow-1 d-flex gap-2">
            <input class="form-control edit-area-name" value="${escapeAttr(name)}" />
            <input class="form-control edit-area-desc" value="${escapeAttr(desc)}" />
          </div>
          <div>
            <button class="btn btn-sm btn-success btn-save-edit" data-id="${id}">Save</button>
            <button class="btn btn-sm btn-secondary btn-cancel-edit">Cancel</button>
          </div>
        `;
        row.querySelector('.btn-cancel-edit').addEventListener('click', loadAreas);
        row.querySelector('.btn-save-edit').addEventListener('click', async function(){
          const nid = this.dataset.id;
          const nname = row.querySelector('.edit-area-name').value.trim();
          const ndesc = row.querySelector('.edit-area-desc').value.trim();
          if(!nname){ showAlert('Area name required','danger'); return; }
          try{
            const res = await fetch(`/categories/${nid}`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }, body: JSON.stringify({ name: nname, description: ndesc }) });
            const j = await res.json();
            if(res.ok && j.success){ showAlert('Area updated'); loadAreas(); }
            else showAlert(j.message || 'Failed to update','danger');
          }catch(err){ console.error(err); showAlert('Failed to update','danger'); }
        });
      }

      async function onDeleteArea(e){
        const id = e.currentTarget.dataset.id;
        if(!confirm('Delete this area?')) return;
        try{
          const res = await fetch(`/categories/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' } });
          const j = await res.json();
          if(res.ok && j.success){ showAlert('Area deleted'); loadAreas(); }
          else showAlert(j.message || 'Failed to delete','danger');
        }catch(err){ console.error(err); showAlert('Failed to delete','danger'); }
      }

      // On modal show, load areas from server. We prefer JSON endpoint; if server returns HTML, expect window.initialCategories set server-side.
      document.getElementById('manageAreasModal').addEventListener('show.bs.modal', function(){ loadAreas(); });
    })();
  </script>

  <!-- Student coordinator table removed per user request -->

  </div>
</div>

  <!-- Add Task Area Modal (Enhanced with Area Type Selection) -->
  <div class="modal fade" id="addTaskAreaModal" tabindex="-1" aria-labelledby="addTaskAreaModalLabel">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
      <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
        <div class="modal-header" style="border-bottom: 1px solid #e9ecef; padding: 20px 30px;">
          <h5 class="modal-title" id="addTaskAreaModalLabel" style="font-weight: 600; color: #333; font-size: 1.2rem;">
            <i class="bi bi-plus-circle me-2" style="color: #007bff;"></i>Add New Task Area
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 30px;">
          <div id="addAreaAlert" class="alert d-none" role="alert"></div>
          
          <!-- Workflow Guide -->
          <div class="alert alert-info border-0 mb-4" style="background: linear-gradient(135deg, #e3f2fd 0%, #f8f9ff 100%); border-left: 4px solid #007bff !important;">
            <div class="d-flex align-items-start">
              <i class="bi bi-lightbulb me-3 mt-1" style="color: #007bff; font-size: 1.1rem;"></i>
              <div style="font-size: 0.9rem;">
                <strong style="color: #1976d2;">Quick Guide:</strong><br>
                <span class="text-muted">
                  1️⃣ <strong>Main Area</strong> = Container (e.g., "Kitchen Area")<br>
                  2️⃣ <strong>Sub Area</strong> = Task Card for assignments & auto-shuffle<br>
                  <small style="color: #666;">💡 Create main area first, then add sub-areas under it</small>
                </span>
              </div>
            </div>
          </div>
          
          <!-- Area Type Selection -->
          <div class="mb-4">
            <label class="form-label" style="font-weight: 600; color: #333; margin-bottom: 12px;">
              <i class="bi bi-diagram-3 me-2"></i>Area Type
            </label>
            <div class="row g-3">
              <div class="col-6">
                <div class="form-check area-type-card" style="border: 2px solid #e9ecef; border-radius: 10px; padding: 15px; cursor: pointer; transition: all 0.3s ease;">
                  <input class="form-check-input" type="radio" name="areaType" id="mainAreaType" value="main" checked style="margin-top: 8px;">
                  <label class="form-check-label w-100" for="mainAreaType" style="cursor: pointer;">
                    <div class="d-flex align-items-center">
                      <i class="bi bi-house-door me-2" style="font-size: 1.2rem; color: #007bff;"></i>
                      <div>
                        <div style="font-weight: 600; color: #333;">Main Area</div>
                        <small class="text-muted">Container for sub-areas</small>
                      </div>
                    </div>
                  </label>
                </div>
                
                <!-- Gender Count Badges -->
                <div class="d-flex justify-content-center gap-2 mb-3">
                  <div class="badge-male">
                    <strong>Male: {{ $displayMales }}</strong>
                  </div>
                  <div class="badge-female">
                    <strong>Female: {{ $displayFemales }}</strong>
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
          </div>

          <!-- Parent Area Selection (for sub-areas) -->
          <div id="parentAreaSection" class="mb-4" style="display: none;">
            <label class="form-label" style="font-weight: 500; color: #333; margin-bottom: 8px;">
              <i class="bi bi-arrow-up me-2"></i>Parent Area
            </label>
            <select id="parentAreaSelect" class="form-select" style="border: 1px solid #ddd; border-radius: 8px; padding: 12px 15px; font-size: 0.95rem;">
              <option value="">Select a main area...</option>
            </select>
          </div>
          
          <!-- Area Name -->
          <div class="mb-4">
            <label class="form-label" style="font-weight: 500; color: #333; margin-bottom: 8px;">
              <i class="bi bi-tag me-2"></i><span id="areaNameLabel">Area Name</span>
            </label>
            <input id="addAreaName" class="form-control" placeholder="e.g. Kitchen Operations Center" style="border: 1px solid #ddd; border-radius: 8px; padding: 12px 15px; font-size: 0.95rem;" />
            <div class="form-text">
              <small id="areaNameHint">Enter a descriptive name for your main area</small>
            </div>
          </div>
          
          <!-- Description -->
          <div class="mb-4">
            <label class="form-label" style="font-weight: 500; color: #333; margin-bottom: 8px;">
              <i class="bi bi-card-text me-2"></i>Description (Optional)
            </label>
            <textarea id="addAreaDescription" class="form-control" rows="3" placeholder="Brief description of this area's purpose and responsibilities..." style="border: 1px solid #ddd; border-radius: 8px; padding: 12px 15px; font-size: 0.95rem; resize: vertical;"></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top: 1px solid #e9ecef; padding: 20px 30px; gap: 10px;">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 8px; padding: 10px 20px; font-weight: 500;">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </button>
          <button id="saveNewTaskAreaBtn" type="button" class="btn btn-primary" style="background: #007bff; border: none; border-radius: 8px; padding: 10px 20px; font-weight: 500;">
            <i class="bi bi-check-circle me-2"></i>Create Area
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Student Assignment Modals for each category -->
  @foreach($categories as $category)
  <div class="modal fade" id="studentAssignModal{{ $category->id }}" tabindex="-1" aria-labelledby="studentAssignModalLabel{{ $category->id }}">
    <div class="modal-dialog" style="max-width: 1200px; width:90vw; margin: 1.75rem auto;">
      <div class="modal-content" style="width: 100%; margin: 0 auto; transform: none; left: auto;">
        <div class="modal-header">
          <h5 class="modal-title" id="studentAssignModalLabel{{ $category->id }}" style="font-size: 22px;">Members for {{ $category->name }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <table class="table table-bordered" style="font-size: 18px;">
            <thead>
              <tr>
                @foreach($activeBatches->whereIn('year', [2025, 2026])->values() as $batch)
                  <th style="font-size: 20px; padding: 15px; text-align: center; background-color: #f8f9fa;">{{ $batch->display_name }}</th>
                @endforeach
              </tr>
            </thead>
            <tbody>
@php
  // Organize members by batch for this category
  $membersByBatch = [];
  foreach($activeBatches->whereIn('year', [2025, 2026])->values() as $batch) {
    $membersByBatch[$batch->year] = [];
  }

  // Get current assignment members for this category
  foreach($category->assignments as $assignment) {
    if($assignment->status === 'current') {
      foreach($assignment->assignmentMembers as $member) {
        if ($member->student) {
          $batchYear = $member->student->batch;
          if(isset($membersByBatch[$batchYear])) {
            $membersByBatch[$batchYear][] = $member;
          }
        }
      }
    }
  }

  // Find the maximum number of rows needed
  $maxRows = 0;
  foreach($activeBatches->whereIn('year', [2025, 2026])->values() as $batch) {
    $members = $membersByBatch[$batch->year] ?? [];
    if(count($members) > $maxRows) $maxRows = count($members);
  }
@endphp
@for($i = 0; $i < $maxRows; $i++)
    <tr>
        @foreach($activeBatches->whereIn('year', [2025, 2026])->values() as $batch)
      @php $members = $membersByBatch[$batch->year] ?? []; @endphp
      <td class="{{ isset($members[$i]) && $members[$i]->is_coordinator ? 'coordinator-highlight-cell' : '' }}" style="padding: 15px; font-size: 18px;">
        @if(isset($members[$i]) && $members[$i]->student)
          @php $u = $members[$i]->student; $studentName = trim(($u->user_fname ?? '') . ' ' . ($u->user_lname ?? '')); @endphp
          <span class="{{ $members[$i]->is_coordinator ? 'coordinator-name' : '' }}" style="font-size: 18px;">{{ $studentName }}</span>
          @if($members[$i]->comments)
            <span class="text-muted" style="font-size: 16px;"> ({{ $members[$i]->comments }})</span>
          @endif
        @endif
      </td>
        @endforeach
    </tr>
@endfor
            </tbody>
          </table>

          @if(auth()->check() && in_array(auth()->user()->user_role, ['educator', 'inspector']))
          <!-- Add and Delete Buttons - Above Edit Button -->
          <div class="text-center mt-3 mb-2">
            <button type="button" class="btn btn-success me-2" onclick="openAddMembersModal({{ $category->id }}, '{{ $category->name }}')">
              <i class="bi bi-person-plus me-1"></i>Add
            </button>
            <button type="button" class="btn btn-danger" onclick="openDeleteMembersModal({{ $category->id }}, '{{ $category->name }}')">
              <i class="bi bi-person-dash me-1"></i>Delete
            </button>
          </div>

          <!-- Edit Button - Below Add/Delete buttons -->
          <div class="text-center">
            <button type="button" class="btn btn-edit-blue" onclick="openEditMembersModal({{ $category->id }}, '{{ $category->name }}')">
              <i class="bi bi-pencil-square me-1"></i>Edit Members
            </button>
          </div>
          @endif
        </div>
      </div>
    </div>
  </div>
  @endforeach

  <!-- Manage Tasks Modal (reused for any category) -->
  <div class="modal fade" id="manageTasksModal" tabindex="-1" aria-labelledby="manageTasksModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="manageTasksModalLabel">Manage Tasks for <span id="manageTasksCategoryName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div id="manageTasksAlert" class="alert d-none" role="alert"></div>
          <div class="mb-3 d-flex justify-content-between align-items-center">
            <div>
              <strong>Area:</strong>
              <span id="manageTasksAreaName" class="ms-2">--</span>
            </div>
            <button id="btnOpenAddTask" class="btn btn-primary btn-sm">+ Add Task</button>
          </div>

          <!-- Task list -->
          <div id="manageTasksList" class="list-group">
            <!-- populated by JS -->
          </div>

          <!-- Add/Edit form (hidden by default) -->
          <div id="manageTaskForm" class="card mt-3 d-none">
            <div class="card-body">
              <input type="hidden" id="manageTaskId" value="">
              <div class="mb-2">
                <label class="form-label">Task Area</label>
                <input id="manageTaskArea" class="form-control" placeholder="e.g. Kitchen">
              </div>
              <div class="mb-2">
                <label class="form-label">Description</label>
                <textarea id="manageTaskDescription" class="form-control" rows="3"></textarea>
              </div>
              <!-- Day removed per request (server will default to Monday) -->
              <div class="d-flex justify-content-end gap-2">
                <button id="manageTaskCancel" class="btn btn-secondary btn-sm">Cancel</button>
                <button id="manageTaskSave" class="btn btn-success btn-sm">Save Task</button>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Manage Areas Modal -->
  <div class="modal fade" id="manageAreasModal" tabindex="-1" aria-labelledby="manageAreasModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="manageAreasModalLabel">Manage Areas</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted">Here you can add, edit or delete main task areas (categories).</p>
          <div id="manageAreasContent">
            <!-- placeholder: area list + add/edit controls -->
            <p>No areas loaded yet.</p>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <a href="{{ url('/assignments/create') }}" class="btn btn-primary">+ Add Area</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Members Modal -->
  <div class="modal fade" id="addMembersModal" tabindex="-1" aria-labelledby="addMembersModalLabel">
    <div class="modal-dialog" style="max-width: 1200px; width:90vw;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addMembersModalLabel">Add Members to <span id="addCategoryName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <!-- Add New Student Section -->
          <div class="row mb-4">
            <div class="col-md-6">
              <div class="card border-success">
                <div class="card-header bg-success text-white">
                  <h6 class="mb-0">Add New Student - Batch 2025</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-8">
                      <input type="text" class="form-control" id="newStudent2025Name" placeholder="Enter student name" style="font-size: 16px;">
                    </div>
                    <div class="col">
                      <select class="form-control" id="newStudent2025Gender" style="font-size: 16px;">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm mt-2" onclick="addNewStudentToCategory(2025)">
                    <i class="bi bi-plus-circle"></i> Add to Category
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="card border-success">
                <div class="card-header bg-success text-white">
                  <h6 class="mb-0">Add New Student - Batch 2026</h6>
                </div>
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-8">
                      <input type="text" class="form-control" id="newStudent2026Name" placeholder="Enter student name" style="font-size: 16px;">
                    </div>
                    <div class="col">
                      <select class="form-control" id="newStudent2026Gender" style="font-size: 16px;">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                      </select>
                    </div>
                  </div>
                  <button type="button" class="btn btn-success btn-sm mt-2" onclick="addNewStudentToCategory(2026)">
                    <i class="bi bi-plus-circle"></i> Add to Category
                  </button>
                </div>
              </div>
            </div>
          </div>

          <hr>

          <!-- Existing Students Section -->
          <div class="row">
            <div class="col-md-6">
              <h6 class="fw-bold mb-3 text-primary">Available Students - Batch 2025</h6>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-hover">
                  <tbody id="availableStudents2025">
                    <!-- Available students will be loaded here -->
                  </tbody>
                </table>
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="fw-bold mb-3 text-primary">Available Students - Batch 2026</h6>
              <div class="table-responsive" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-hover">
                  <tbody id="availableStudents2026">
                    <!-- Available students will be loaded here -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <hr>

          <div class="row">
            <div class="col-32">
              <h6 class="fw-bold mb-3 text-primary">Selected Students to Add</h6>
              <div id="selectedStudentsToAdd" class="border rounded p-3" style="min-height: 50px; background-color: #f8f9fa;">
                <p class="text-muted mb-0">Click on students above to select them for adding to this category.</p>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" id="confirmAddMembers">Add Selected Members</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Members Modal -->
  <div class="modal fade" id="deleteMembersModal" tabindex="-1" aria-labelledby="deleteMembersModalLabel">
    <div class="modal-dialog" style="max-width: 1200px; width:90vw;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteMembersModalLabel">Delete Members from <span id="deleteCategoryName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="row">
            <div class="col-md-6">
              <h6 class="fw-bold mb-3 text-warning">Current Members - Batch 2025</h6>
              <p class="small text-muted">Click to select for removal from category only</p>
              <div id="currentMembers2025" style="max-height: 300px; overflow-y: auto;">
                <!-- Current members will be loaded here -->
              </div>
            </div>
            <div class="col-md-6">
              <h6 class="fw-bold mb-3 text-warning">Current Members - Batch 2026</h6>
              <p class="small text-muted">Click to select for removal from category only</p>
              <div id="currentMembers2026" style="max-height: 300px; overflow-y: auto;">
                <!-- Current members will be loaded here -->
              </div>
            </div>
          </div>

          <hr>

          <div class="row">
            <div class="col-32">
              <h6 class="fw-bold mb-3 text-warning">Selected Members to Remove from Category</h6>
              <div id="selectedMembersToDelete" class="border rounded p-3" style="min-height: 55px; background-color: #fff3cd;">
                <p class="text-muted mb-0">Click on members above to select them for removal from this category.</p>
              </div>
            </div>
          </div>

          <hr>

          <!-- Delete from System Section -->
          <div class="row">
            <div class="col-32">
              <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                  <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Permanently Delete Students from System</h6>
                </div>
                <div class="card-body">
                  <p class="text-danger small mb-3">
                    <strong>Warning:</strong> This will permanently delete students from the entire system and all assignments.
                  </p>
                  <div class="row">
                    <div class="col-md-6">
                      <h6 class="text-danger">Batch 2025 Students</h6>
                      <div id="systemDeleteMembers2025" style="max-height: 200px; overflow-y: auto;">
                        <!-- Members for system deletion will be loaded here -->
                      </div>
                    </div>
                    <div class="col-md-6">
                      <h6 class="text-danger">Batch 2026 Students</h6>
                      <div id="systemDeleteMembers2026" style="max-height: 200px; overflow-y: auto;">
                        <!-- Members for system deletion will be loaded here -->
                      </div>
                    </div>
                  </div>
                  <div class="mt-3">
                    <h6 class="text-danger">Selected Students to Delete from System</h6>
                    <div id="selectedStudentsToDeleteFromSystem" class="border rounded p-3" style="min-height: 55px; background-color: #f8d7da;">
                      <p class="text-muted mb-0">Click on students above to select them for permanent deletion.</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-warning" id="confirmDeleteMembers">Remove from Category</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteFromSystem">Delete from System</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Members Modal -->
  <div class="modal fade" id="editMembersModal" tabindex="-1" aria-labelledby="editMembersModalLabel">
    <div class="modal-dialog" style="max-width: 1200px; width:90vw;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editMembersModalLabel">Edit Members for <span id="editCategoryName"></span></h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="padding: 20px;">
          <div class="row">
            <div class="col-md-6">
              <h6 class="fw-bold mb-3 text-primary">Batch 2025</h6>
              <div id="editMembers2025Container" class="member-list-container">
                <!-- Members will be loaded here -->
              </div>
            </div>
              <!-- removed nested Edit Capacity Modal to avoid duplicate IDs; single global modal is defined later -->

            <div class="col-md-6">
              <h6 class="fw-bold mb-3 text-primary">Batch 2026</h6>
              <div id="editMembers2026Container" class="member-list-container">
                <!-- Members will be loaded here -->
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Task Assignment History Modal -->
  <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header" style="background-color: transparent; border-bottom: 1px solid #dee2e6;">
          <h5 class="modal-title" id="historyModalLabel" style="color: #000000;">Task Assignment History</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div style="max-height:80vh; overflow-y:auto;">
            <table class="table table-bordered align-middle">
              <thead style="background-color: transparent;">
                <tr>
                  <th style="width: 80px; background-color: transparent; border: 1px solid #dee2e6; color: #000000;">Category</th>
                  <th style="width: 400px; background-color: transparent; border: 1px solid #dee2e6; color: #000000;">Period & Members</th>
                  <th style="width: 400px; background-color: transparent; border: 1px solid #dee2e6; color: #000000;">Previous Assignment</th>
                  <th style="width: 80px; background-color: transparent; border: 1px solid #dee2e6; color: #000000;">Status</th>
                </tr>
              </thead>
              <tbody>
                @foreach($categories as $category)
                  @php
                    // Get current and previous assignments for this category
                    $catAssignments = $assignmentHistory->where('category_id', $category->id)->sortByDesc('id')->values();
                    $current = $catAssignments->where('status', 'current')->first();
                    $previous = $catAssignments->where('status', 'previous')->sortByDesc('id')->first();
                  @endphp
                  <tr>
                    <td class="fw-bold align-top">{{ $category->name }}</td>
                    <td>
                      @if($current)
                        <div>
                          <span class="fw-semibold text-primary">Period: {{ $current->start_date }} - {{ $current->end_date }}</span>
                        </div>
                        @php
                          $members2025 = $current->assignmentMembers->where('student.batch', 2025)->values();
                          $members2026 = $current->assignmentMembers->where('student.batch', 2026)->values();
                          $maxRows = max($members2025->count(), $members2026->count());
                        @endphp
                        <table class="table table-bordered table-sm mt-2">
                          <thead style="background-color: transparent;">
                            <tr>
                              <th class="fw-bold text-center" style="width: 50%; background-color: transparent; border: 1px solid #dee2e6; color: #000000;">Batch 2025</th>
                              <th class="fw-bold text-center" style="width: 50%; background-color: transparent; border: 1px solid #dee2e6; color: #000000;">Batch 2026</th>
                            </tr>
                          </thead>
                          <tbody>
                            @for($i = 0; $i < $maxRows; $i++)
                              <tr>
                                <td class="{{ isset($members2025[$i]) && $members2025[$i]->is_coordinator ? 'coordinator-highlight-cell' : '' }}">
                                  @if(isset($members2025[$i]))
                                    @php
                                      $m = $members2025[$i];
                                      $u = $m->student ?? null;
                                      $name = $u ? (trim(($u->user_fname ?? '') . ' ' . ($u->user_lname ?? '')) ?? ($u->name ?? '')) : 'Unknown';
                                    @endphp
                                    <span class="{{ $members2025[$i]->is_coordinator ? 'coordinator-name' : '' }}">{{ $name }}</span>
                                    @if($members2025[$i]->comments)
                                      <span class="text-muted small"> ({{ $members2025[$i]->comments }})</span>
                                    @endif
                                  @endif
                                </td>
                                <td class="{{ isset($members2026[$i]) && $members2026[$i]->is_coordinator ? 'coordinator-highlight-cell' : '' }}">
                                  @if(isset($members2026[$i]))
                                    @php
                                      $m2 = $members2026[$i];
                                      $u2 = $m2->student ?? null;
                                      $name2 = $u2 ? (trim(($u2->user_fname ?? '') . ' ' . ($u2->user_lname ?? '')) ?? ($u2->name ?? '')) : 'Unknown';
                                    @endphp
                                    <span class="{{ $members2026[$i]->is_coordinator ? 'coordinator-name' : '' }}">{{ $name2 }}</span>
                                    @if($members2026[$i]->comments)
                                      <span class="text-muted small"> ({{ $members2026[$i]->comments }})</span>
                                    @endif
                                  @endif
                                </td>
                              </tr>
                            @endfor
                          </tbody>
                        </table>
                      @else
                        <span class="text-muted">No assignment</span>
                      @endif
                    </td>
                    <td>
                      @if($previous)
                        <div>
                          <span class="fw-semibold text-primary">Period: {{ $previous->start_date }} - {{ $previous->end_date }}</span>
                        </div>
                        @php
                          $prevMembers2025 = $previous->assignmentMembers->where('student.batch', 2025)->values();
                          $prevMembers2026 = $previous->assignmentMembers->where('student.batch', 2026)->values();
                          $prevMaxRows = max($prevMembers2025->count(), $prevMembers2026->count());
                        @endphp
                        <table class="table table-bordered table-sm mt-2">
                          <thead style="background-color: transparent;">
                            <tr>
                              <th class="fw-bold text-center" style="width: 50%; background-color: transparent; border: 1px solid #dee2e6; color: #000000;">Batch 2025</th>
                              <th class="fw-bold text-center" style="width: 50%; background-color: transparent; border: 1px solid #dee2e6; color: #000000;">Batch 2026</th>
                            </tr>
                          </thead>
                          <tbody>
                            @for($i = 0; $i < $prevMaxRows; $i++)
                              <tr>
                                <td class="{{ isset($prevMembers2025[$i]) && $prevMembers2025[$i]->is_coordinator ? 'coordinator-highlight-cell' : '' }}">
                                  @if(isset($prevMembers2025[$i]))
                                    @php
                                      $pm = $prevMembers2025[$i];
                                      $pu = $pm->student ?? null;
                                      $pname = $pu ? (trim(($pu->user_fname ?? '') . ' ' . ($pu->user_lname ?? '')) ?? ($pu->name ?? '')) : 'Unknown';
                                    @endphp
                                    <span class="{{ $prevMembers2025[$i]->is_coordinator ? 'coordinator-name' : '' }}">{{ $pname }}</span>
                                    @if($prevMembers2025[$i]->comments)
                                      <span class="text-muted small"> ({{ $prevMembers2025[$i]->comments }})</span>
                                    @endif
                                  @endif
                                </td>
                                <td class="{{ isset($prevMembers2026[$i]) && $prevMembers2026[$i]->is_coordinator ? 'coordinator-highlight-cell' : '' }}">
                                  @if(isset($prevMembers2026[$i]))
                                    @php
                                      $pm2 = $prevMembers2026[$i];
                                      $pu2 = $pm2->student ?? null;
                                      $pname2 = $pu2 ? (trim(($pu2->user_fname ?? '') . ' ' . ($pu2->user_lname ?? '')) ?? ($pu2->name ?? '')) : 'Unknown';
                                    @endphp
                                    <span class="{{ $prevMembers2026[$i]->is_coordinator ? 'coordinator-name' : '' }}">{{ $pname2 }}</span>
                                    @if($prevMembers2026[$i]->comments)
                                      <span class="text-muted small"> ({{ $prevMembers2026[$i]->comments }})</span>
                                    @endif
                                  @endif
                                </td>
                              </tr>
                            @endfor
                          </tbody>
                        </table>
                      @else
                        <span class="text-muted">No previous assignment</span>
                      @endif
                    </td>
                    <td class="text-center align-top">
                      @if($loop->first)
                        <span class="badge bg-success">Current</span>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Capacity Modal (Single Global) -->
  <div class="modal fade" id="editCapacityModal" tabindex="-1" aria-labelledby="editCapacityModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editCapacityModalLabel">Edit Task Assignment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="editCapacityForm">
            @csrf
            <input type="hidden" id="edit_category_id" name="category_id" />

            <div class="row gx-3 gy-2 align-items-center">
              <div class="col-32 mb-2">
                <label class="form-label">Task Name</label>
                <input type="text" readonly class="form-control" id="task_name" />
              </div>

              <!-- Horizontal two-column layout: left=2025, right=2026 -->
              <div class="col-md-6">
                <div class="d-flex gap-2">
                  <div class="flex-fill">
                    <label class="form-label small">Batch 2025 - Males Required</label>
                    <input type="number" class="form-control" id="batch2025_boys_required" name="batch2025_boys_required" min="0" />
                  </div>
                  <div class="flex-fill">
                    <label class="form-label small">Batch 2025 - Females Required</label>
                    <input type="number" class="form-control" id="batch2025_girls_required" name="batch2025_girls_required" min="0" />
                  </div>
                </div>
                <!-- removed Batch 2025 Assigned display per request -->
              </div>

              <div class="col-md-6">
                <div class="d-flex gap-2">
                  <div class="flex-fill">
                    <label class="form-label small">Batch 2026 - Males Required</label>
                    <input type="number" class="form-control" id="batch2026_boys_required" name="batch2026_boys_required" min="0" />
                  </div>
                  <div class="flex-fill">
                    <label class="form-label small">Batch 2026 - Females Required</label>
                    <input type="number" class="form-control" id="batch2026_girls_required" name="batch2026_girls_required" min="0" />
                  </div>
                </div>
                <!-- Per request, remove Batch 2026 assigned input to simplify modal -->
              </div>

              <div class="col-md-6">
                <label class="form-label">Start Date</label>
                <input type="date" class="form-control" id="start_date" name="start_date" />
              </div>
              <div class="col-md-6">
                <label class="form-label">End Date</label>
                <input type="date" class="form-control" id="end_date" name="end_date" />
              </div>

              <!-- Description field -->
              <div class="col-32">
                <label class="form-label">Description <small class="text-muted">(optional)</small></label>
                <textarea id="task_description" name="task_description" class="form-control" rows="2" placeholder="Add a short description for this task area"></textarea>
              </div>

              <!-- Coordinators: optional inputs with Auto checkbox -->
              <div class="col-md-6">
                <label class="form-label">Coordinator 2025 <small class="text-muted">(optional)</small></label>
                <div class="input-group">
                  <input type="text" id="coordinator_2025" name="coordinator_2025" class="form-control" placeholder="Enter coordinator name" />
                  <span class="input-group-text">
                    <input class="form-check-input mt-0" type="checkbox" id="auto_assign_coord_2025" aria-label="Auto assign coordinator 2025"> Auto
                  </span>
                </div>
              </div>
              <div class="col-md-6">
                <label class="form-label">Coordinator 2026 <small class="text-muted">(optional)</small></label>
                <div class="input-group">
                  <input type="text" id="coordinator_2026" name="coordinator_2026" class="form-control" placeholder="Enter coordinator name" />
                  <span class="input-group-text">
                    <input class="form-check-input mt-0" type="checkbox" id="auto_assign_coord_2026" aria-label="Auto assign coordinator 2026"> Auto
                  </span>
                </div>
              </div>

            </div>
          </form>
          <div class="mt-3 text-muted small">Note: After saving, Auto-Shuffle will run to apply the new capacities and avoid repeating students from the previous week. If 'Auto' is checked for a coordinator, the system will select a coordinator during shuffle.</div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="saveEditCapacityBtn" class="btn btn-primary">Save Changes</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Task Checklist Modal - Only show for admins -->
  @if(auth()->check() && in_array(auth()->user()->user_role, ['educator', 'inspector']))
  <div class="modal fade" id="taskChecklistModal" tabindex="-1" aria-labelledby="taskChecklistModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px; width: 90vw; margin: 1.75rem auto;">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="taskChecklistModalLabel" style="font-size: 28px; font-weight: bold; color: #000000;">
            <i class="bi bi-list-check me-2"></i>Task Checklist
          </h5>
        </div>
        <div class="modal-body p-0">
          <!-- Navigation Controls -->
          <div class="d-flex justify-content-between align-items-center p-3 border-bottom">
            <button type="button" class="btn btn-outline-primary" onclick="changeTaskPage(-1)" id="prevPageBtn">
              <i class="bi bi-chevron-left"></i> Previous
            </button>
            <span class="fw-bold">Page <span id="currentPageNumber">1</span> of 10</span>
            <button type="button" class="btn btn-outline-primary" onclick="changeTaskPage(1)" id="nextPageBtn">
              Next <i class="bi bi-chevron-right"></i>
            </button>
          </div>

          <!-- Task Content Area -->
          <div id="taskPageContent" class="p-3">
            <!-- Content will be loaded dynamically -->
          </div>
        </div>
      </div>
    </div>

  @endif
  <!-- End Task Checklist Modal admin-only section -->

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    function editCategoryMembers(categoryId, categoryName) {
      // You can customize this function based on your needs
      // For now, it will redirect to an edit page or show an edit modal

      // Option 1: Redirect to edit page
      window.location.href = `/assignments/category/${categoryId}/edit`;

      // Option 2: Show alert (for testing)
      // alert(`Edit members for ${categoryName} (Category ID: ${categoryId})`);

      // Option 3: Open edit modal (you would need to create this modal)
      // $('#editCategoryModal').modal('show');
      // populateEditModal(categoryId, categoryName);
    }

    // Build a consistent student display name from Login fields when available
    function buildStudentDisplayName(studentObj) {
      if (!studentObj) return 'Unknown';
      // If this is a nested member with student, prefer that first
      if (studentObj.student) {
        const u = studentObj.student;
        const n = (((u.user_fname || '') + ' ' + (u.user_lname || '')).trim()) || u.name;
        return n || 'Unknown';
      }
      // If object has user_fname/user_lname (Login-shaped student)
      if (studentObj.user_fname || studentObj.user_lname) {
        const n = (((studentObj.user_fname || '') + ' ' + (studentObj.user_lname || '')).trim()) || studentObj.name;
        return n || 'Unknown';
      }
      // Fallback to legacy name field
      return studentObj.name || 'Unknown';
    }

    // Global variables for add/delete functionality
    let currentCategoryId = null;
    let selectedStudentsToAdd = [];
    let selectedMembersToDelete = [];
    let selectedStudentsToDeleteFromSystem = [];

    // Open Add Members Modal
    function openAddMembersModal(categoryId, categoryName) {
      currentCategoryId = categoryId;
      selectedStudentsToAdd = [];

      // Set category name
      document.getElementById('addCategoryName').textContent = categoryName;

      // Clear selected students display
      document.getElementById('selectedStudentsToAdd').innerHTML = '<p class="text-muted mb-0">Click on students above to select them for adding to this category.</p>';

      // Load available students
      loadAvailableStudents(categoryId);

      // Show modal
      const addModal = new bootstrap.Modal(document.getElementById('addMembersModal'));
      addModal.show();
    }

    // Open Delete Members Modal
    function openDeleteMembersModal(categoryId, categoryName) {
      currentCategoryId = categoryId;
      selectedMembersToDelete = [];
      selectedStudentsToDeleteFromSystem = [];

      // Set category name
      document.getElementById('deleteCategoryName').textContent = categoryName;

      // Clear selected displays
      document.getElementById('selectedMembersToDelete').innerHTML = '<p class="text-muted mb-0">Click on members above to select them for removal from this category.</p>';
      document.getElementById('selectedStudentsToDeleteFromSystem').innerHTML = '<p class="text-muted mb-0">Click on students above to select them for permanent deletion.</p>';

      // Load current members
      loadCurrentMembers(categoryId);
      loadAllStudentsForSystemDeletion();

      // Show modal
      const deleteModal = new bootstrap.Modal(document.getElementById('deleteMembersModal'));
      deleteModal.show();
    }

    // Load available students for adding
    function loadAvailableStudents(categoryId) {
      fetch(`/assignments/category/${categoryId}/available-students`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            populateAvailableStudents(data.students2025, data.students2026);
          } else {
            console.error('Error loading available students:', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }

    // Populate available students in modal
    function populateAvailableStudents(students2025, students2026) {
      const container2025 = document.getElementById('availableStudents2025');
      const container2026 = document.getElementById('availableStudents2026');

      // Clear containers
      container2025.innerHTML = '';
      container2026.innerHTML = '';

      // Populate Batch 2025
      students2025.forEach(student => {
        const row = document.createElement('tr');
        row.className = 'student-row-add';
        row.style.cursor = 'pointer';
        row.dataset.studentId = student.id;
        const sName = buildStudentDisplayName(student);
        row.dataset.studentName = sName;
        row.dataset.studentBatch = student.batch;
        row.innerHTML = `<td class="py-2 px-3">${sName} (${student.gender || ''})</td>`;
        row.onclick = () => selectStudentToAdd(student);
        container2025.appendChild(row);
      });

      // Populate Batch 2026
      students2026.forEach(student => {
        const row = document.createElement('tr');
        row.className = 'student-row-add';
        row.style.cursor = 'pointer';
        row.dataset.studentId = student.id;
        const sName = buildStudentDisplayName(student);
        row.dataset.studentName = sName;
        row.dataset.studentBatch = student.batch;
        row.innerHTML = `<td class="py-2 px-3">${sName} (${student.gender || ''})</td>`;
        row.onclick = () => selectStudentToAdd(student);
        container2026.appendChild(row);
      });
    }

    // Select student to add
    function selectStudentToAdd(student) {
      // Check if already selected
      if (selectedStudentsToAdd.find(s => s.id === student.id)) {
        return;
      }

      selectedStudentsToAdd.push(student);
      updateSelectedStudentsToAddDisplay();

      // Highlight the row
      const rows = document.querySelectorAll(`[data-student-id="${student.id}"]`);
      rows.forEach(row => {
        row.classList.add('table-success');
        row.style.opacity = '0.6';
      });
    }

    // Update selected students to add display
    function updateSelectedStudentsToAddDisplay() {
      const container = document.getElementById('selectedStudentsToAdd');

      if (selectedStudentsToAdd.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">Click on students above to select them for adding to this category.</p>';
        return;
      }

      let html = '';
      selectedStudentsToAdd.forEach(student => {
        const sName = buildStudentDisplayName(student);
        html += `
          <span class="badge bg-primary me-2 mb-2" style="font-size: 9px; padding: 8px 12px;">
            ${sName} (Batch ${student.batch})
            <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 11px;" onclick="removeStudentToAdd(${student.id})"></button>
          </span>
        `;
      });

      container.innerHTML = html;
    }

    // Remove student from add selection
    function removeStudentToAdd(studentId) {
      selectedStudentsToAdd = selectedStudentsToAdd.filter(s => s.id !== studentId);
      updateSelectedStudentsToAddDisplay();

      // Remove highlight from row
      const rows = document.querySelectorAll(`[data-student-id="${studentId}"]`);
      rows.forEach(row => {
        row.classList.remove('table-success');
        row.style.opacity = '1';
      });
    }

    // Add new student directly to category
    function addNewStudentToCategory(batch) {
      const nameInput = document.getElementById(`newStudent${batch}Name`);
      const genderSelect = document.getElementById(`newStudent${batch}Gender`);

      const name = nameInput.value.trim();
      const gender = genderSelect.value;

      if (!name) {
        alert('Please enter a student name');
        nameInput.focus();
        return;
      }

      // Create and add student to system, then assign to category
      fetch('/students/quick-add-to-category', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          name: name,
          gender: gender,
          batch: batch,
          category_id: currentCategoryId
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('success', data.message);
          // Clear input
          nameInput.value = '';
          // Refresh available students
          loadAvailableStudents(currentCategoryId);
        } else {
          alert('Error adding student: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error adding student');
      });
    }

    // Load current members for deleting
    function loadCurrentMembers(categoryId) {
      fetch(`/assignments/category/${categoryId}/current-members`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            populateCurrentMembers(data.members2025, data.members2026);
          } else {
            console.error('Error loading current members:', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }

    // Populate current members in delete modal
    function populateCurrentMembers(members2025, members2026) {
      const container2025 = document.getElementById('currentMembers2025');
      const container2026 = document.getElementById('currentMembers2026');

      // Clear containers
      container2025.innerHTML = '';
      container2026.innerHTML = '';

      // Populate Batch 2025
      members2025.forEach(member => {
        const div = document.createElement('div');
        div.className = 'member-item-delete p-2 border rounded mb-2';
        div.style.cursor = 'pointer';
        div.dataset.memberId = member.id;
        const studentName = buildStudentDisplayName(member);
        div.dataset.memberName = studentName;
        div.innerHTML = `
          <div class="${member.is_coordinator ? 'coordinator-name' : ''}">${studentName}</div>
          ${member.comments ? `<small class="text-muted">(${member.comments})</small>` : ''}
        `;
        div.onclick = () => selectMemberToDelete(member);
        container2025.appendChild(div);
      });

      // Populate Batch 2026
      members2026.forEach(member => {
        const div = document.createElement('div');
        div.className = 'member-item-delete p-2 border rounded mb-2';
        div.style.cursor = 'pointer';
        div.dataset.memberId = member.id;
        const studentName = buildStudentDisplayName(member);
        div.dataset.memberName = studentName;
        div.innerHTML = `
          <div class="${member.is_coordinator ? 'coordinator-name' : ''}">${studentName}</div>
          ${member.comments ? `<small class="text-muted">(${member.comments})</small>` : ''}
        `;
        div.onclick = () => selectMemberToDelete(member);
        container2026.appendChild(div);
      });
    }

    // Select member to delete
    function selectMemberToDelete(member) {
      // Check if already selected
      if (selectedMembersToDelete.find(m => m.id === member.id)) {
        return;
      }

      selectedMembersToDelete.push(member);
      updateSelectedMembersToDeleteDisplay();

      // Highlight the member
      const items = document.querySelectorAll(`[data-member-id="${member.id}"]`);
      items.forEach(item => {
        item.classList.add('bg-danger', 'text-white');
      });
    }

    // Update selected members to delete display
    function updateSelectedMembersToDeleteDisplay() {
      const container = document.getElementById('selectedMembersToDelete');

      if (selectedMembersToDelete.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">Click on members above to select them for removal from this category.</p>';
        return;
      }

      let html = '';
      selectedMembersToDelete.forEach(member => {
        const studentName = buildStudentDisplayName(member);
        html += `
          <span class="badge bg-danger me-2 mb-2" style="font-size: 9px; padding: 8px 12px;">
            ${studentName}
            <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 11px;" onclick="removeMemberToDelete(${member.id})"></button>
          </span>
        `;
      });

      container.innerHTML = html;
    }

    // Remove member from delete selection
    function removeMemberToDelete(memberId) {
      selectedMembersToDelete = selectedMembersToDelete.filter(m => m.id !== memberId);
      updateSelectedMembersToDeleteDisplay();

      // Remove highlight from member
      const items = document.querySelectorAll(`[data-member-id="${memberId}"]`);
      items.forEach(item => {
        item.classList.remove('bg-danger', 'text-white');
      });
    }

    // Load all students for system deletion
    function loadAllStudentsForSystemDeletion() {
      fetch('/students/all-for-deletion')
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            populateStudentsForSystemDeletion(data.students2025, data.students2026);
          } else {
            console.error('Error loading students for deletion:', data.message);
          }
        })
        .catch(error => {
          console.error('Error:', error);
        });
    }

    // Populate students for system deletion
    function populateStudentsForSystemDeletion(students2025, students2026) {
      const container2025 = document.getElementById('systemDeleteMembers2025');
      const container2026 = document.getElementById('systemDeleteMembers2026');

      // Clear containers
      container2025.innerHTML = '';
      container2026.innerHTML = '';

      // Populate Batch 2025
      students2025.forEach(student => {
        const div = document.createElement('div');
        div.className = 'student-item-system-delete p-2 border rounded mb-2';
        div.style.cursor = 'pointer';
        div.dataset.studentId = student.id;
        const sName = buildStudentDisplayName(student);
        div.dataset.studentName = sName;
        div.innerHTML = `
          <div>${sName} (${student.gender || ''})</div>
        `;
        div.onclick = () => selectStudentForSystemDeletion(student);
        container2025.appendChild(div);
      });

      // Populate Batch 2026
      students2026.forEach(student => {
        const div = document.createElement('div');
        div.className = 'student-item-system-delete p-2 border rounded mb-2';
        div.style.cursor = 'pointer';
        div.dataset.studentId = student.id;
        const sName = buildStudentDisplayName(student);
        div.dataset.studentName = sName;
        div.innerHTML = `
          <div>${sName} (${student.gender || ''})</div>
        `;
        div.onclick = () => selectStudentForSystemDeletion(student);
        container2026.appendChild(div);
      });
    }

    // Select student for system deletion
    function selectStudentForSystemDeletion(student) {
      // Check if already selected
      if (selectedStudentsToDeleteFromSystem.find(s => s.id === student.id)) {
        return;
      }

      selectedStudentsToDeleteFromSystem.push(student);
      updateSelectedStudentsForSystemDeletionDisplay();

      // Highlight the student
      const items = document.querySelectorAll(`[data-student-id="${student.id}"]`);
      items.forEach(item => {
        if (item.classList.contains('student-item-system-delete')) {
          item.classList.add('bg-danger', 'text-white');
        }
      });
    }

    // Update selected students for system deletion display
    function updateSelectedStudentsForSystemDeletionDisplay() {
      const container = document.getElementById('selectedStudentsToDeleteFromSystem');

      if (selectedStudentsToDeleteFromSystem.length === 0) {
        container.innerHTML = '<p class="text-muted mb-0">Click on students above to select them for permanent deletion.</p>';
        return;
      }

      let html = '';
      selectedStudentsToDeleteFromSystem.forEach(student => {
        const sName = buildStudentDisplayName(student);
        html += `
          <span class="badge bg-danger me-2 mb-2" style="font-size: 9px; padding: 8px 12px;">
            ${sName}
            <button type="button" class="btn-close btn-close-white ms-2" style="font-size: 11px;" onclick="removeStudentFromSystemDeletion(${student.id})"></button>
          </span>
        `;
      });

      container.innerHTML = html;
    }

    // Remove student from system deletion selection
    function removeStudentFromSystemDeletion(studentId) {
      selectedStudentsToDeleteFromSystem = selectedStudentsToDeleteFromSystem.filter(s => s.id !== studentId);
      updateSelectedStudentsForSystemDeletionDisplay();

      // Remove highlight from student
      const items = document.querySelectorAll(`[data-student-id="${studentId}"]`);
      items.forEach(item => {
        if (item.classList.contains('student-item-system-delete')) {
          item.classList.remove('bg-danger', 'text-white');
        }
      });
    }

    // Open Edit Members Modal - Direct to edit form
    function openEditMembersModal(categoryId, categoryName) {
      // Close any existing modal first
      const existingModal = bootstrap.Modal.getInstance(document.getElementById('studentAssignModal' + categoryId));
      if (existingModal) {
        existingModal.hide();
      }

      // Set category name in modal title
      document.getElementById('editCategoryName').textContent = categoryName;

      // Show loading state
      document.getElementById('editMembers2025Container').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';
      document.getElementById('editMembers2026Container').innerHTML = '<div class="text-center"><div class="spinner-border" role="status"></div></div>';

      // Show edit modal immediately
      const editModal = new bootstrap.Modal(document.getElementById('editMembersModal'));
      editModal.show();

      // Fetch members data and populate edit form directly
      fetch(`/assignments/category/${categoryId}/members`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            populateEditMembers(data.members2025, data.members2026);
          } else {
            showNotification('Error loading members data', 'error');
          }
        })
        .catch(error => {
          console.error('Error:', error);
          showNotification('Error loading members data', 'error');
        });
    }

    // Populate edit members containers
    function populateEditMembers(members2025, members2026) {
      const container2025 = document.getElementById('editMembers2025Container');
      const container2026 = document.getElementById('editMembers2026Container');

      // Clear containers
      container2025.innerHTML = '';
      container2026.innerHTML = '';

      // Populate Batch 2025
      members2025.forEach(member => {
        const memberHtml = createEditMemberRow(member);
        container2025.appendChild(memberHtml);
      });

      // Populate Batch 2026
      members2026.forEach(member => {
        const memberHtml = createEditMemberRow(member);
        container2026.appendChild(memberHtml);
      });
    }

    // Refresh the student assign modal for a category by re-fetching members and updating the modal table
    function refreshStudentAssignModal(categoryId) {
      // Find the modal and its table body
      const modal = document.getElementById('studentAssignModal' + categoryId);
      if (!modal) return;

      fetch(`/assignments/category/${categoryId}/members`, { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(json => {
          if (!json.success) return;

          // Normalize server response to membersByBatch: { <year>: [members...] }
          let membersByBatch = {};

          // Case A: server returns members2025 and members2026 explicitly
          if (json.members2025 || json.members2026) {
            if (json.members2025) membersByBatch[2025] = json.members2025;
            if (json.members2026) membersByBatch[2026] = json.members2026;
          }

          // Case B: server returns a keyed object like membersByBatch or members_by_batch
          if (Object.keys(membersByBatch).length === 0) {
            const candidates = json.membersByBatch || json.members_by_batch || json.members_by_year || null;
            if (candidates) {
              membersByBatch = candidates;
            }
          }

          // Case C: server returns flat members array with student.batch on each member
          if (Object.keys(membersByBatch).length === 0 && Array.isArray(json.members)) {
            json.members.forEach(m => {
              const y = (m.student && (m.student.batch || m.student.year)) || m.batch || m.year || 'unknown';
              if (!membersByBatch[y]) membersByBatch[y] = [];
              membersByBatch[y].push(m);
            });
          }

          // Fallback: ensure every active batch has an array
          (window.activeBatches || []).forEach(b => {
            if (!membersByBatch[b.year]) membersByBatch[b.year] = [];
          });

          // Compute max rows
          let maxRows = 0;
          Object.values(membersByBatch).forEach(arr => { if (Array.isArray(arr) && arr.length > maxRows) maxRows = arr.length; });

          // Build tbody HTML using single-name-per-column layout
          let tbodyHtml = '';
          for (let i = 0; i < maxRows; i++) {
            tbodyHtml += '<tr>';
            (window.activeBatches || []).forEach(b => {
              const members = membersByBatch[b.year] || [];
              const member = members[i];
              if (member) {
                const name = buildStudentDisplayName(member);
                const coordinatorClass = member.is_coordinator ? 'coordinator-name' : '';
                const comments = member.comments ? ` <span class="text-muted">(${member.comments})</span>` : '';
                tbodyHtml += `<td class="${coordinatorClass}" style="padding: 15px; font-size: 18px; vertical-align: middle;">${name}${comments}</td>`;
              } else {
                tbodyHtml += '<td></td>';
              }
            });
            tbodyHtml += '</tr>';
          }

          const tb = modal.querySelector('.modal-body table tbody');
          if (tb) tb.innerHTML = tbodyHtml;
        })
        .catch(err => console.error('Error refreshing assign modal:', err));
    }

    // Create edit member row
    function createEditMemberRow(member) {
      const div = document.createElement('div');
      div.className = `member-row ${member.is_coordinator ? 'coordinator-highlight-edit' : ''}`;
      div.id = `member-row-${member.id}`;

  const studentName = buildStudentDisplayName(member);

      div.innerHTML = `
        <div class="member-info">
          <div class="member-name ${member.is_coordinator ? 'coordinator-name' : ''}">
            ${studentName}
          </div>
          ${member.comments ? `<div class="member-comment" id="comment-display-${member.id}">(${member.comments})</div>` : ''}
          <div class="comment-edit-area" id="comment-edit-${member.id}" style="display: none;">
            <input type="text" class="comment-input" id="comment-input-${member.id}"
                   value="${member.comments || ''}" placeholder="Add comment (e.g., Not available - headache)">
          </div>
        </div>
        <div class="member-actions">
          <button class="btn-edit-small" onclick="editMember(${member.id})" id="edit-btn-${member.id}">Edit</button>
          <button class="btn-save-small" onclick="saveMember(${member.id})" id="save-btn-${member.id}" style="display: none;">Save</button>
        </div>
      `;

      return div;
    }

    // Edit member function
    function editMember(memberId) {
      // Hide comment display and edit button
      const commentDisplay = document.getElementById(`comment-display-${memberId}`);
      const editBtn = document.getElementById(`edit-btn-${memberId}`);
      const saveBtn = document.getElementById(`save-btn-${memberId}`);
      const commentEdit = document.getElementById(`comment-edit-${memberId}`);

      if (commentDisplay) commentDisplay.style.display = 'none';
      editBtn.style.display = 'none';
      saveBtn.style.display = 'inline-block';
      commentEdit.style.display = 'block';

      // Focus on input
      const input = document.getElementById(`comment-input-${memberId}`);
      input.focus();
    }

    // Edit student name
    function editStudentName(studentId) {
      const nameDisplay = document.getElementById(`name-display-${studentId}`);
      const nameEdit = document.getElementById(`name-edit-${studentId}`);
      const editBtn = document.getElementById(`edit-name-btn-${studentId}`);
      const saveBtn = document.getElementById(`save-name-btn-${studentId}`);
      const cancelBtn = document.getElementById(`cancel-name-btn-${studentId}`);

      nameDisplay.classList.add('d-none');
      nameEdit.classList.remove('d-none');
      editBtn.classList.add('d-none');
      saveBtn.classList.remove('d-none');
      cancelBtn.classList.remove('d-none');

      nameEdit.focus();
      nameEdit.select();
    }

    // Save student name
    function saveStudentName(studentId) {
      const nameEdit = document.getElementById(`name-edit-${studentId}`);
      const newName = nameEdit.value.trim();

      if (!newName) {
        alert('Name cannot be empty');
        return;
      }

      fetch(`/students/${studentId}/update-name`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ name: newName })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          const nameDisplay = document.getElementById(`name-display-${studentId}`);
          nameDisplay.textContent = newName;
          cancelEditName(studentId);

          // Show success message
          showNotification('success', data.message);
        } else {
          alert('Error updating name: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error updating student name');
      });
    }

    // Cancel edit name
    function cancelEditName(studentId) {
      const nameDisplay = document.getElementById(`name-display-${studentId}`);
      const nameEdit = document.getElementById(`name-edit-${studentId}`);
      const editBtn = document.getElementById(`edit-name-btn-${studentId}`);
      const saveBtn = document.getElementById(`save-name-btn-${studentId}`);
      const cancelBtn = document.getElementById(`cancel-name-btn-${studentId}`);

      nameDisplay.classList.remove('d-none');
      nameEdit.classList.add('d-none');
      editBtn.classList.remove('d-none');
      saveBtn.classList.add('d-none');
      cancelBtn.classList.add('d-none');

      // Reset input value to original
      nameEdit.value = nameDisplay.textContent;
    }

    // Delete student
    function deleteStudent(studentId, studentName) {
      if (!confirm(`Are you sure you want to delete "${studentName}"? This action cannot be undone and will remove the student from all assignments.`)) {
        return;
      }

      fetch(`/students/${studentId}`, {
        method: 'DELETE',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('success', data.message);
          // Refresh the page to update the view
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          alert('Error deleting student: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error deleting student');
      });
    }

    // Add new student
    function addNewStudent(batch) {
      const nameInput = document.getElementById(`newStudent${batch}Name`);
      const genderSelect = document.getElementById(`newStudent${batch}Gender`);

      const name = nameInput.value.trim();
      const gender = genderSelect.value;

      if (!name) {
        alert('Please enter a student name');
        nameInput.focus();
        return;
      }

      fetch('/students/quick-add', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          name: name,
          gender: gender,
          batch: batch
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('success', data.message);
          nameInput.value = '';
          // Refresh the page to update the view
          setTimeout(() => {
            location.reload();
          }, 1500);
        } else {
          alert('Error adding student: ' + data.message);
        }
      })
      .catch(error => {
        console.error('Error:', error);
        alert('Error adding student');
      });
    }

    // Save member function
    function saveMember(memberId) {
      const input = document.getElementById(`comment-input-${memberId}`);
      const comments = input.value.trim();

      // Show loading state
      const saveBtn = document.getElementById(`save-btn-${memberId}`);
      const originalText = saveBtn.textContent;
      saveBtn.textContent = 'Saving...';
      saveBtn.disabled = true;

      // Get CSRF token
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Create form data
      const formData = new FormData();
      formData.append('member_id', memberId);
      formData.append('comments', comments);
      formData.append('_token', csrfToken);

      // Send AJAX request
      fetch('/assignments/update-member-comment', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrfToken,
          'Accept': 'application/json'
        },
        body: formData
      })
      .then(response => {
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
      })
      .then(data => {
        if (data.success) {
          // Update display
          const commentDisplay = document.getElementById(`comment-display-${memberId}`);
          const editBtn = document.getElementById(`edit-btn-${memberId}`);
          const commentEdit = document.getElementById(`comment-edit-${memberId}`);

          if (comments) {
            if (commentDisplay) {
              commentDisplay.textContent = `(${comments})`;
              commentDisplay.style.display = 'block';
            } else {
              // Create comment display if it doesn't exist
              const memberInfo = document.querySelector(`#member-row-${memberId} .member-info`);
              const newCommentDisplay = document.createElement('div');
              newCommentDisplay.className = 'member-comment';
              newCommentDisplay.id = `comment-display-${memberId}`;
              newCommentDisplay.textContent = `(${comments})`;
              memberInfo.appendChild(newCommentDisplay);
            }
          } else {
            if (commentDisplay) commentDisplay.style.display = 'none';
          }

          // Reset buttons
          editBtn.style.display = 'inline-block';
          saveBtn.style.display = 'none';
          commentEdit.style.display = 'none';

          // Show success message
          showNotification('Comment saved successfully!', 'success');

          // Refresh the page after 1 second to show updated comments in View History
          setTimeout(() => {
            window.location.reload();
          }, 1000);
        } else {
          showNotification(data.message || 'Error saving comment. Please try again.', 'error');
        }
      })
      .catch(error => {
        console.error('Error:', error);
        showNotification('Error saving comment. Please try again.', 'error');
      })
      .finally(() => {
        // Reset button state
        saveBtn.textContent = originalText;
        saveBtn.disabled = false;
      });
    }

    // Simple notification function
    function showNotification(message, type) {
      const notification = document.createElement('div');
      notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      notification.innerHTML = `
        <div class="d-flex align-items-center">
          <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}-fill me-2"></i>
          ${message}
        </div>
      `;
      document.body.appendChild(notification);
      setTimeout(() => notification.remove(), 4000);
    }

    // Show workflow demonstration
    function showWorkflowDemo() {
      const modal = document.createElement('div');
      modal.className = 'modal fade';
      modal.innerHTML = `
        <div class="modal-dialog modal-lg modal-dialog-centered">
          <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
            <div class="modal-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; border-bottom: none; border-radius: 15px 15px 0 0;">
              <h5 class="modal-title">
                <i class="bi bi-book me-2"></i>Task Area Workflow Guide
              </h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
              <div class="row">
                <div class="col-md-6">
                  <div class="card border-primary mb-3">
                    <div class="card-header bg-primary text-white">
                      <i class="bi bi-house-door me-2"></i>Step 1: Create Main Area
                    </div>
                    <div class="card-body">
                      <h6 class="card-title">Main Area = Container</h6>
                      <p class="card-text small">
                        • Acts as a category organizer<br>
                        • Does NOT create task cards<br>
                        • Example: "Kitchen Area", "Office Area"
                      </p>
                      <div class="bg-light p-2 rounded">
                        <strong>Example:</strong><br>
                        <span class="text-primary">Kitchen Area</span> (Main)
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="card border-success mb-3">
                    <div class="card-header bg-success text-white">
                      <i class="bi bi-card-checklist me-2"></i>Step 2: Create Sub Areas
                    </div>
                    <div class="card-body">
                      <h6 class="card-title">Sub Area = Task Card</h6>
                      <p class="card-text small">
                        • Creates actual task cards<br>
                        • Used in auto-shuffle<br>
                        • Students get assigned here
                      </p>
                      <div class="bg-light p-2 rounded">
                        <strong>Examples:</strong><br>
                        <span class="text-success">Kitchen Operations Center</span><br>
                        <span class="text-success">Kitchen Dishwashing Station</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="alert alert-info border-0" style="background: #f8f9ff;">
                <h6><i class="bi bi-lightbulb me-2"></i>Complete Example:</h6>
                <div class="ms-3">
                  <strong>Kitchen Area</strong> (Main Area - Container)<br>
                  ├── Kitchen Operations Center (Sub Area - Task Card)<br>
                  ├── Kitchen Dishwashing Station (Sub Area - Task Card)<br>
                  └── Kitchen Dining Service Area (Sub Area - Task Card)
                </div>
              </div>
              
              <div class="alert alert-warning border-0">
                <i class="bi bi-info-circle me-2"></i>
                <strong>Remember:</strong> Only sub-areas appear as task cards for student assignments and auto-shuffle!
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Got It!</button>
            </div>
          </div>
        </div>
      `;
      
      document.body.appendChild(modal);
      const bsModal = new bootstrap.Modal(modal);
      bsModal.show();
      
      modal.addEventListener('hidden.bs.modal', () => {
        modal.remove();
      });
    }

    // Optional: Add some visual feedback when hovering over edit button
    document.addEventListener('DOMContentLoaded', function() {
      const editButtons = document.querySelectorAll('.btn-edit-blue');
      editButtons.forEach(button => {
        button.addEventListener('mouseenter', function() {
          this.style.transform = 'translateY(-2px)';
        });
        button.addEventListener('mouseleave', function() {
          this.style.transform = 'translateY(0)';
        });
      });
    });

    // Task Checklist Modal Functions
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('status-btn')) {
            const taskId = e.target.dataset.task;
            const week = e.target.dataset.week;
            const day = e.target.dataset.day;
            const status = e.target.dataset.status;

            // Find other button in same cell and deactivate it
            const otherButtons = e.target.parentElement.querySelectorAll('.status-btn');
            otherButtons.forEach(btn => {
                if (btn !== e.target) {
                    btn.classList.remove('active');
                }
            });

            // Toggle current button
            if (e.target.classList.contains('active')) {
                e.target.classList.remove('active');
            } else {
                e.target.classList.add('active');
            }
        }
    });

    // Update week dates function
    function updateWeekDates() {
        const week1Date = document.getElementById('week1_date').value;
        const week2Date = document.getElementById('week2_date').value;

        // You can add AJAX call here to save dates if needed
        console.log('Week 1 Date:', week1Date);
        console.log('Week 2 Date:', week2Date);
    }

    // Mark task function for check/wrong buttons
    function toggleCheckbox(cellId) {
        const container = document.getElementById(`${cellId}_container`);
        const buttons = document.getElementById(`${cellId}_buttons`);
        const status = document.getElementById(`${cellId}_status`);

        // Check if the checkbox is already marked (has a status)
        if (status.innerHTML !== '') {
            // If already marked, clear it
            clearTask(cellId);
        } else {
            // If not marked, show buttons for selection
            if (buttons.style.display === 'none') {
                buttons.style.display = 'flex';
                status.style.display = 'none';
                container.style.backgroundColor = '#f8f9fa';
            } else {
                // If buttons are visible, hide them
                buttons.style.display = 'none';
                status.style.display = 'block';
                container.style.backgroundColor = 'white';
            }
        }
    }

    function markTask(cellId, status) {
        const container = document.getElementById(`${cellId}_container`);
        const buttons = document.getElementById(`${cellId}_buttons`);
        const statusSpan = document.getElementById(`${cellId}_status`);

        // Hide buttons and show status
        buttons.style.display = 'none';
        statusSpan.style.display = 'block';

        // Set the status icon and color
        if (status === 'check') {
            statusSpan.innerHTML = '✓';
            statusSpan.style.color = '#4caf50';
            container.style.backgroundColor = '#e8f5e8';
            container.style.borderColor = '#4caf50';
        } else if (status === 'wrong') {
            statusSpan.innerHTML = '✗';
            statusSpan.style.color = '#007bff';
            container.style.backgroundColor = '#e3f2fd';
            container.style.borderColor = '#007bff';
        }

        // You can add logic here to save the status to database or local storage
    console.log(`Task ${cellId} marked as ${status}`);

    // If this checkbox represents a server-backed task, attempt to persist status
    // We encode server task ids as 'server_task_<id>_...'
    const match = cellId.match(/^server_task_(\d+)/);
    if (match) {
      const taskId = match[1];
      const week = cellId.includes('_week2') ? 'week2' : 'week1';
      // POST status to server endpoint (expects task_id, week, day, status)
      const parts = cellId.split('_');
      // Format: server_task_<id>_<day>_<week>
      const day = parts[parts.length - 2] || '';

      const form = new FormData();
      form.append('task_id', taskId);
      form.append('week', week);
      form.append('day', day);
      form.append('status', status);

      fetch('/task-checklist/update-status', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json'
        },
        body: form
      }).then(r => r.json()).then(j => {
        if (!j.success) console.warn('Could not save task status', j);
      }).catch(e => console.error('Error saving task status', e));
    }
    }

    function clearTask(cellId) {
        const container = document.getElementById(`${cellId}_container`);
        const buttons = document.getElementById(`${cellId}_buttons`);
        const statusSpan = document.getElementById(`${cellId}_status`);

        // Reset to original state
        statusSpan.innerHTML = '';
        statusSpan.style.color = '';
        container.style.backgroundColor = 'white';
        container.style.borderColor = '#dee2e6';
        buttons.style.display = 'none';
        statusSpan.style.display = 'block';

        console.log(`Task ${cellId} cleared`);
    }

    // Task Page Navigation
    let currentTaskPage = 1;
    const totalTaskPages = 10; // You can adjust this based on how many pages you want

    function navigateTaskPage(direction) {
        if (direction === 'next') {
            currentTaskPage = currentTaskPage < totalTaskPages ? currentTaskPage + 1 : 1;
        } else if (direction === 'prev') {
            currentTaskPage = currentTaskPage > 1 ? currentTaskPage - 1 : totalTaskPages;
        }

        console.log('Current Task Page:', currentTaskPage);

        // Update page number display
        document.getElementById('currentPageNumber').textContent = currentTaskPage;

        // Here you can load different content based on currentTaskPage
        loadTaskPageContent(currentTaskPage);
    }

    function loadTaskPageContent(pageNumber) {
        console.log('Loading task page:', pageNumber);

        const taskPageContent = document.getElementById('taskPageContent');

        if (pageNumber === 1) {
            // Page 1: Kitchen & General Cleaning Tasks
            taskPageContent.innerHTML = getPage1Content();
        } else if (pageNumber === 2) {
            // Page 2: Dishwashing & General Cleaning Tasks
            taskPageContent.innerHTML = getPage2Content();
        } else if (pageNumber === 3) {
            // Page 3: Dining & Ground Floor Cleaning
            taskPageContent.innerHTML = getPage3Content();
        } else if (pageNumber === 4) {
            // Page 4: Laundry & Maintenance
            taskPageContent.innerHTML = getPage4Content();
        } else if (pageNumber === 5) {
            // Page 5: Security & Safety
            taskPageContent.innerHTML = getPage5Content();
        } else if (pageNumber === 6) {
            // Page 6: Office & Administrative
            taskPageContent.innerHTML = getPage6Content();
        } else if (pageNumber === 7) {
            // Page 7: Garden & Outdoor
            taskPageContent.innerHTML = getPage7Content();
        } else if (pageNumber === 8) {
            // Page 8: Storage & Inventory
            taskPageContent.innerHTML = getPage8Content();
        } else if (pageNumber === 9) {
            // Page 9: Recreation & Events
            taskPageContent.innerHTML = getPage9Content();
        } else if (pageNumber === 10) {
            // Page 10: Special Tasks & Projects
            taskPageContent.innerHTML = getPage10Content();
        } else {
            // Default to Page 1
            taskPageContent.innerHTML = getPage1Content();
        }

        // Update modal title to show current page
        document.getElementById('taskChecklistModalLabel').textContent = `Task Checklist - Page ${pageNumber}`;
    }

    function getPage1Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: transparent; border: 1px solid #dee2e6; font-size: 9px; font-weight: 600; padding: 6px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: transparent; border: 1px solid #dee2e6; font-size: 9px; font-weight: 600; padding: 4px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: transparent; border: 1px solid #dee2e6; padding: 4px; font-size: 11px; font-weight: 600;">
                    DATE: <input type="date" id="week1_date" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;" onchange="updateWeekDates()">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 35px; border: 1px solid #dee2e6; font-size: 8px; padding: 2px; background-color: transparent; font-weight: 600;">MON</th>
                  <th class="text-center" style="width: 35px; border: 1px solid #dee2e6; font-size: 8px; padding: 2px; background-color: transparent; font-weight: 600;">TUE</th>
                  <th class="text-center" style="width: 35px; border: 1px solid #dee2e6; font-size: 8px; padding: 2px; background-color: transparent; font-weight: 600;">WED</th>
                  <th class="text-center" style="width: 35px; border: 1px solid #dee2e6; font-size: 8px; padding: 2px; background-color: transparent; font-weight: 600;">THU</th>
                  <th class="text-center" style="width: 35px; border: 1px solid #dee2e6; font-size: 8px; padding: 2px; background-color: transparent; font-weight: 600;">FRI</th>
                  <th class="text-center" style="width: 35px; border: 1px solid #dee2e6; font-size: 8px; padding: 2px; background-color: transparent; font-weight: 600;">SAT</th>
                  <th class="text-center" style="width: 35px; border: 1px solid #dee2e6; font-size: 8px; padding: 2px; background-color: transparent; font-weight: 600;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getKitchenRows()}
                ${getGeneralCleaningRows()}
              </tbody>
            </table>
        `;
    }

    function getKitchenRows() {
    // Prefer server-provided tasks if available
    const serverKitchen = (window.serverChecklist && window.serverChecklist['KITCHEN']) ? window.serverChecklist['KITCHEN'] : null;

    const kitchenTasks = serverKitchen ? serverKitchen.map(t => t.description) : [
      'Assigned members wake up on time and completed their tasks as scheduled.',
      'The students assigned to cook the rice completed the task properly.',
      'The students assigned to cook the viand completed the task properly.',
      'The students assigned to assist the cook carried out their duties diligently.',
      'Ingredients were prepared ahead of time.',
      'The kitchen was properly cleaned after cooking.',
      'The food was transferred from the kitchen to the center.',
      'Proper inventory of stocks was maintained and deliveries were handled appropriately.',
      'Water and food supplies were regularly monitored and stored in the proper place.',
      'Receipts, kitchen phones, and keys were safely stored.',
      'Kitchen utensils were properly stored.',
      'The stove was turned off after cooking.',
      'Properly disposed of the garbage.',
      'Properly washed the burner.',
      'Wiped and arranged the chiller.',
      'Cleaned the canal after cooking.',
      'Arranged the freezer.'
    ];

    let rows = '';
    kitchenTasks.forEach((task, index) => {
      const isFirst = index === 0;
      const categoryCell = isFirst ? `<td rowspan="${kitchenTasks.length}" class="text-center category-cell" style="background-color: #4caf50; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 6px 4px; vertical-align: middle;">KITCHEN<br><br>2-3<br>unchecked-<br>for<br>improvement<br>5 or more<br>unchecked-<br>for<br>consequence</td>` : '';

      // If server data exists, use the task id for checkboxes and remarks bindings
      const taskObj = serverKitchen ? serverKitchen[index] : null;
      const taskKey = taskObj ? `server_task_${taskObj.id}` : `kitchen${index + 1}`;

      rows += `
        <tr>
          ${categoryCell}
          <td style="border: 1px solid #dee2e6; padding: 4px; font-size: 18px; background-color: #fafafa; text-align: left; line-height: 1.3; font-weight: normal; color: #000000;">
            ${task}
          </td>
          ${generateCheckboxCells(taskKey)}
          <td style="border: 1px solid #dee2e6; padding: 6px 4px; background-color: #fff8e1; width: 400px;">
            <textarea class="form-control" placeholder="Remarks..." data-task-id="${taskObj ? taskObj.id : ''}" style="font-size: 16px; padding: 6px 4px; border: 1px solid #dee2e6; color: #666; font-weight: normal; height: 50px; resize: vertical;"></textarea>
          </td>
        </tr>
      `;
    });
    return rows;
    }

    function getGeneralCleaningRows() {
        const cleaningTasks = [
            'Cleaned the drainage canals.',
            'Brushed and rinsed the floor of the dishwashing area.',
            'Brushed the sink.',
            'Washed the barrel container.',
            'Cleaned and arranged the storage cabinet.',
            'Wiped the cabinets. (No dusts/stains inside and outside the cabinet)'
        ];

        let rows = '';
        cleaningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${cleaningTasks.length}" class="text-center category-cell" style="background-color: #2196f3; color: white; border: 1px solid #000; font-size: 9px; font-weight: normal; padding: 6px 4px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td style="border: 1px solid #dee2e6; padding: 4px; font-size: 18px; background-color: #fafafa; text-align: left; line-height: 1.3; font-weight: normal; color: #000000;">
                        ${task}
                    </td>
                    ${generateCheckboxCells('cleaning' + (index + 1))}
                    <td style="border: 1px solid #dee2e6; padding: 6px 4px; background-color: #fff8e1; width: 400px;">
                        <textarea class="form-control" placeholder="Remarks..." style="font-size: 16px; padding: 6px 4px; border: 1px solid #dee2e6; color: #666; font-weight: normal; height: 50px; resize: vertical;"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function generateCheckboxCells(taskId, week = 'week1') {
        const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        let cells = '';

        days.forEach(day => {
            const cellId = `${taskId}_${day}_${week}`;
            cells += `
                <td class="text-center" style="border: 1px solid #dee2e6; padding: 2px; background-color: #f8f9fa;">
                    <div id="${cellId}_container" class="checkbox-container" style="width: 50px; height: 35px; border: 2px solid #dee2e6; background-color: white; border-radius: 4px; display: flex; align-items: center; justify-content: center; cursor: pointer; margin: 0 auto;" onclick="toggleCheckbox('${cellId}')">
                        <div id="${cellId}_buttons" style="display: none; gap: 3px;">
                            <button type="button" class="btn btn-success btn-sm" style="font-size: 9px; padding: 3px 4px; background-color: #4caf50; border: 1px solid #4caf50; color: white; font-weight: 700; width: 22px; height: 20px; border-radius: 3px; line-height: 1;" onclick="event.stopPropagation(); markTask('${cellId}', 'check')" title="Check">✓</button>
                            <button type="button" class="btn btn-danger btn-sm" style="font-size: 9px; padding: 3px 4px; background-color: #007bff; border: 1px solid #007bff; color: white; font-weight: 700; width: 22px; height: 20px; border-radius: 3px; line-height: 1;" onclick="event.stopPropagation(); markTask('${cellId}', 'wrong')" title="Wrong">✗</button>
                        </div>
                        <span id="${cellId}_status" style="font-size: 16px; font-weight: bold;"></span>
                    </div>
                </td>
            `;
        });

        return cells;
    }

    function getPage2Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #ffc107; color: white; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p2" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getDishwashingRows()}
                ${getGeneralCleaningRows()}
              </tbody>
            </table>
        `;
    }

    function getDishwashingRows() {
        const dishwashingTasks = [
            'Wash the dishes thoroughly.',
            'Disposed of the leftovers in the proper place.',
            'Cleaned the sink after washing the dishes.',
            'Ensured no plates, glasses, utensils, or other items were left in the sink.',
            'Neatly arranged the plates, glasses, utensils, pots, and pans in their designated places.',
            'Properly stored the basin and pail in their designated areas.',
            'Avoid wasting soap during washing.',
            'Cleaned the dishwashing area.',
            'Ensured staff plates, utensils, and other items were properly cleaned and stored in their designated areas.'
        ];

        let rows = '';
        dishwashingTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${dishwashingTasks.length}" class="text-center category-cell" style="background-color: #ffc107; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 6px 4px; vertical-align: middle;">DISHWASHING<br><br>2-3<br>unchecked-<br>for<br>improvement<br>4 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('dishwashing' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="dishwashing${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getPage3Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #17a2b8; color: white; border: 1px solid #000; font-size: 16px; font-weight: bold; padding: 6px 4px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p3" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getDiningRows()}
                ${getGroundFloorRows()}
              </tbody>
            </table>
        `;
    }

    function getDiningRows() {
        const diningTasks = [
            'Set up the dining area ahead of time.',
            'Distributed the food equally.',
            'Properly wiped the tables after mealtime.',
            'Rang the bell or announce to batchmates that it\'s mealtime.',
            'Swept the dining area.',
            'Arranged and cleaned the dining area after mealtime (chairs, tables, and dishes).',
            'Packed the lunch of batchmates on time.',
            'Gathered all the dishes for washing.'
        ];

        let rows = '';
        diningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${diningTasks.length}" class="text-center category-cell" style="background-color: #17a2b8; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 6px 4px; vertical-align: middle;">DINING<br><br>2<br>unchecked-<br>for<br>improvement<br>3 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('dining' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="dining${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getGroundFloorRows() {
        const groundFloorTasks = [
            'Brushed the tables.',
            'Brush and rinse the floor in the dining area.',
            'Arrange the dining area once the tables and floor are dry.'
        ];

        let rows = '';
        groundFloorTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${groundFloorTasks.length}" class="text-center category-cell" style="background-color: #17a2b8; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('generalcleaning' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="generalcleaning${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    // Page 4: Room 203 - Offices & Conference Rooms
    function getPage4Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 16px; font-weight: bold; padding: 6px 4px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p4" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getOfficesConferenceRoomsRows()}
                ${getGeneralCleaningRows4()}
              </tbody>
            </table>
        `;
    }

    function getOfficesConferenceRoomsRows() {
        const officesTasks = [
            'ROOM 203',
            'Properly cleaned and brushed the toilet, sink, and shower room, including the tiles.',
            'Swept the floor.',
            'Mopped the floor.',
            'Wiped the tables and chairs (dust-free).',
            'Wiped the mirror with a cloth or paper.',
            'Wiped the cabinets (dust-free).',
            'Cleaned and organized the plates, glasses, and spoons.',
            'Ensured the pail in the toilet is full of water.',
            'Cleaned the window.',
            'Cleaned the toilet bowl and tiles with a cleaner.',
            'Got rid of the cobwebs.',
            'Removed stains on the wall.'
        ];

        let rows = '';
        officesTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const isRoomTitle = task === 'ROOM 203';
            const categoryCell = isFirst ? `<td rowspan="${officesTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 6px 4px; vertical-align: middle;">OFFICES &<br>CONFERENCE<br>ROOMS<br><br>2-3<br>unchecked-<br>for<br>improvement<br>4 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            const taskCellStyle = isRoomTitle ? 'background-color: #ff8c00 !important; font-weight: bold !important; color: #000000 !important; text-align: center !important;' : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell" style="${taskCellStyle}">${task}</td>
                    ${generateCheckboxCells('offices' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="offices${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getGeneralCleaningRows4() {
        const generalCleaningTasks = [
            'Cleaned the toilet bowl and tiles with a cleaner.',
            'Got rid of the cobwebs.',
            'Removed stains on the wall.'
        ];

        let rows = '';
        generalCleaningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${generalCleaningTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('generalcleaning4_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="generalcleaning4_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    // Page 5: Room 301 - Offices & Conference Rooms
    function getPage5Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #fd7e14; color: white; border: 1px solid #000; font-size: 16px; font-weight: bold; padding: 6px 4px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p5" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getOfficesConferenceRoomsRows5()}
                ${getGeneralCleaningRows5()}
              </tbody>
            </table>
        `;
    }

    function getOfficesConferenceRoomsRows5() {
        const officesTasks = [
            'ROOM 301',
            'Properly cleaned and brushed the toilet, sink, and shower room, including the tiles.',
            'Swept the floor.',
            'Mopped the floor.',
            'Wiped the tables and chairs (dust-free).',
            'Wiped the mirror with a cloth or paper.',
            'Wiped the cabinets (dust-free).',
            'Cleaned and organized the plates, glasses, and spoons.',
            'Ensured the pail in the toilet is full of water.',
            'Cleaned the window.',
            'Washed the curtain on Saturday.',
            'Hung the curtain on Sunday.'
        ];

        let rows = '';
        officesTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const isRoomTitle = task === 'ROOM 301';
            const categoryCell = isFirst ? `<td rowspan="${officesTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">OFFICES &<br>CONFERENCE<br>ROOMS<br><br>2-3<br>unchecked-<br>for<br>improvement<br>4 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            const taskCellStyle = isRoomTitle ? 'background-color: #ff8c00 !important; font-weight: bold !important; color: #000000 !important; text-align: center !important;' : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell" style="${taskCellStyle}">${task}</td>
                    ${generateCheckboxCells('offices5_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="offices5_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getGeneralCleaningRows5() {
        const generalCleaningTasks = [
            'Cleaned the toilet bowl and tiles with a cleaner.',
            'Got rid of the cobwebs.',
            'Removed stains on the wall.'
        ];

        let rows = '';
        generalCleaningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${generalCleaningTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('generalcleaning5_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="generalcleaning5_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    // Page 6: Room 401 - Offices & Conference Rooms
    function getPage6Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #20c997; color: white; border: 1px solid #000; font-size: 16px; font-weight: bold; padding: 6px 4px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p6" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getOfficesConferenceRoomsRows6()}
                ${getGeneralCleaningRows6()}
              </tbody>
            </table>
        `;
    }

    function getOfficesConferenceRoomsRows6() {
        const officesTasks = [
            'ROOM 401',
            'Properly cleaned and brushed the toilet, sink, and shower room, including the tiles.',
            'Swept the floor.',
            'Mopped the floor.',
            'Wiped the tables and chairs (dust-free).',
            'Wiped the mirror with a cloth or paper.',
            'Wiped the cabinets (dust-free).',
            'Cleaned and organized the plates, glasses, and spoons.',
            'Ensured the pail in the toilet is full of water.',
            'Cleaned the window.'
        ];

        let rows = '';
        officesTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const isRoomTitle = task === 'ROOM 401';
            const categoryCell = isFirst ? `<td rowspan="${officesTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">OFFICES &<br>CONFERENCE<br>ROOMS<br><br>2-3<br>unchecked-<br>for<br>improvement<br>4 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            const taskCellStyle = isRoomTitle ? 'background-color: #ff8c00 !important; font-weight: bold !important; color: #000000 !important; text-align: center !important;' : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell" style="${taskCellStyle}">${task}</td>
                    ${generateCheckboxCells('offices6_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="offices6_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getGeneralCleaningRows6() {
        const generalCleaningTasks = [
            'Cleaned the toilet bowl and tiles with a cleaner.',
            'Got rid of the cobwebs.',
            'Removed stains on the wall.'
        ];

        let rows = '';
        generalCleaningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${generalCleaningTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('generalcleaning6_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="generalcleaning6_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    // Page 7: Room 303 - Offices & Conference Rooms
    function getPage7Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #198754; color: white; border: 1px solid #000; font-size: 16px; font-weight: bold; padding: 6px 4px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p7" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getOfficesConferenceRoomsRows7()}
                ${getGeneralCleaningRows7()}
              </tbody>
            </table>
        `;
    }

    function getOfficesConferenceRoomsRows7() {
        const officesTasks = [
            'ROOM 303',
            'Properly cleaned and brushed the toilet, sink, and shower room, including the tiles.',
            'Swept the floor.',
            'Mopped the floor.',
            'Wiped the tables and chairs (dust-free).',
            'Wiped the mirror with a cloth or paper.',
            'Wiped the cabinets (dust-free).',
            'Cleaned and organized the plates, glasses, and spoons.',
            'Ensured the pail in the toilet is full of water.',
            'Cleaned the window.'
        ];

        let rows = '';
        officesTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const isRoomTitle = task === 'ROOM 303';
            const categoryCell = isFirst ? `<td rowspan="${officesTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">OFFICES &<br>CONFERENCE<br>ROOMS<br><br>2-3<br>unchecked-<br>for<br>improvement<br>4 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            const taskCellStyle = isRoomTitle ? 'background-color: #ff8c00 !important; font-weight: bold !important; color: #000000 !important; text-align: center !important;' : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell" style="${taskCellStyle}">${task}</td>
                    ${generateCheckboxCells('offices7_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="offices7_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getGeneralCleaningRows7() {
        const generalCleaningTasks = [
            'Cleaned the toilet bowl and tiles with a cleaner.',
            'Got rid of the cobwebs.',
            'Removed stains on the wall.'
        ];

        let rows = '';
        generalCleaningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${generalCleaningTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('generalcleaning7_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="generalcleaning7_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    // Page 8: Room 503 - Offices & Conference Rooms
    function getPage8Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #6610f2; color: white; border: 1px solid #000; font-size: 16px; font-weight: bold; padding: 6px 4px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p8" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getOfficesConferenceRoomsRows8()}
                ${getGeneralCleaningRows8()}
              </tbody>
            </table>
        `;
    }

    function getOfficesConferenceRoomsRows8() {
        const officesTasks = [
            'ROOM 503',
            'Properly cleaned and brushed the toilet, sink, and shower room, including the tiles.',
            'Swept the floor.',
            'Mopped the floor.',
            'Wiped the tables and chairs (dust-free).',
            'Wiped the mirror with a cloth or paper.',
            'Wiped the cabinets (dust-free).',
            'Cleaned and organized the electronic devices.',
            'Ensured the pail in the toilet is full of water.',
            'Cleaned the window.'
        ];

        let rows = '';
        officesTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const isRoomTitle = task === 'ROOM 503';
            const categoryCell = isFirst ? `<td rowspan="${officesTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">OFFICES &<br>CONFERENCE<br>ROOMS<br><br>2-3<br>unchecked-<br>for<br>improvement<br>4 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            const taskCellStyle = isRoomTitle ? 'background-color: #ff8c00 !important; font-weight: bold !important; color: #000000 !important; text-align: center !important;' : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell" style="${taskCellStyle}">${task}</td>
                    ${generateCheckboxCells('offices8_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="offices8_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getGeneralCleaningRows8() {
        const generalCleaningTasks = [
            'Cleaned the toilet bowl and tiles with a cleaner.',
            'Got rid of the cobwebs.',
            'Removed stains on the wall.'
        ];

        let rows = '';
        generalCleaningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${generalCleaningTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('generalcleaning8_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="generalcleaning8_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    // Page 9: Garbage Collection
    function getPage9Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #e83e8c; color: white; border: 1px solid #000; font-size: 16px; font-weight: bold; padding: 6px 4px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p9" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getGarbageCollectionRows()}
                ${getGeneralCleaningRows9()}
              </tbody>
            </table>
        `;
    }

    function getGarbageCollectionRows() {
        const garbageTasks = [
            'Collected all the trash from conference rooms, offices (inside the office trash bins), rooms, rooftop, ground floor, and other areas with trash.',
            'Ensured that all rooms and offices had their own trash bins.',
            'Segregated the garbage.',
            'Washed the rugs and sofa covers.',
            'Placed the rugs in their designated areas.',
            'Threw away items placed in the fire exit.',
            'Washed the trash bins.',
            'Arranged the items on the rooftop.'
        ];

        let rows = '';
        garbageTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${garbageTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 6px 4px; vertical-align: middle;">GARBAGE<br>COLLECTORS,<br>RUGS &<br>ROOFTOP<br><br>2<br>unchecked-<br>for<br>improvement<br>3 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('garbage' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="garbage${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getGeneralCleaningRows9() {
        const generalCleaningTasks = [
            'Cleaned and rinsed the floor on the rooftop.',
            'Wiped the rooftop window.',
            'Returned the trash bins to their designated areas by Sunday afternoon.'
        ];

        let rows = '';
        generalCleaningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${generalCleaningTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('generalcleaning9_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="generalcleaning9_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    // Page 10: Ground Floor
    function getPage10Content() {
        return `
            <table class="table table-bordered mb-0" style="font-size: 11px; border: 1px solid #dee2e6; width: 100%; table-layout: fixed;">
              <thead>
                <tr>
                  <th rowspan="2" class="text-center" style="background-color: #6c757d; color: white; border: 1px solid #000; font-size: 16px; font-weight: bold; padding: 6px 4px; vertical-align: middle; width: 80px;">TASK AREAS</th>
                  <th rowspan="2" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; font-size: 11px; font-weight: bold; padding: 8px; vertical-align: middle; width: 450px;">TASKS TO COMPLETE</th>
                  <th colspan="7" class="text-center" style="background-color: #f8f9fa; border: 1px solid #000; padding: 8px 10px; font-size: 13px; font-weight: bold;">
                    DATE: <input type="date" id="week1_date_p10" value="2024-12-23" class="form-control d-inline-block" style="width: 200px; font-size: 18px; padding: 6px 4px; margin-left: 10px; border: 2px solid #007bff; border-radius: 5px;">
                  </th>
                  <th rowspan="2" class="text-center" style="width: 400px; background-color: #000000; color: white; border: 1px solid #000; font-size: 16px; padding: 8px;">REMARKS</th>
                </tr>
                <tr>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">MON</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">TUE</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">WED</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">THU</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">FRI</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SAT</th>
                  <th class="text-center" style="width: 50px; border: 1px solid #000; font-size: 9px; padding: 4px;">SUN</th>
                </tr>
              </thead>
              <tbody>
                ${getGroundFloorTasksRows()}
                ${getGeneralCleaningRows10()}
              </tbody>
            </table>
        `;
    }

    function getGroundFloorTasksRows() {
        const groundFloorTasks = [
            'Wiped the elevator (wall, floor, and buttons).',
            'Swept the ground floor, stairs, CCTV area, and outside the PN Center.',
            'Properly arranged the receiving area and things on the ground floor (tables, water gallons, cabinets, etc.).',
            'Mopped the stairs and CCTV area (tiles).',
            'Thoroughly arranged the CCTV table, electric fan, and bench/chairs.',
            'Wiped the windows.',
            'Properly cleaned and brushed the comfort room.',
            'Ensured that the receiving area is well-maintained and organized.',
            'Wiped the wall outside, ensuring there are no visible stains.'
        ];

        let rows = '';
        groundFloorTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${groundFloorTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">GROUND<br>FLOOR<br><br>2-3<br>unchecked-<br>for<br>improvement<br>4 or more<br>unchecked-<br>for<br>consequence</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('groundfloor10_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="groundfloor10_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

    function getGeneralCleaningRows10() {
        const generalCleaningTasks = [
            'Wiped the cabinets, tables, and chairs.',
            'Brushed and rinsed the floor of the ground floor.',
            'Arrange the ground floor once the floor is dry.'
        ];

        let rows = '';
        generalCleaningTasks.forEach((task, index) => {
            const isFirst = index === 0;
            const categoryCell = isFirst ? `<td rowspan="${generalCleaningTasks.length}" class="text-center category-cell" style="background-color: #28a745; color: white; border: 1px solid #000; font-size: 9px; font-weight: bold; padding: 8px; vertical-align: middle;">General<br>Cleaning</td>` : '';

            rows += `
                <tr>
                    ${categoryCell}
                    <td class="task-cell">${task}</td>
                    ${generateCheckboxCells('generalcleaning10_' + (index + 1))}
                    <td class="remarks-cell">
                        <textarea class="remarks-input" data-task="generalcleaning10_${index + 1}" data-week="1"></textarea>
                    </td>
                </tr>
            `;
        });
        return rows;
    }

  // Initialize with Page 1 content when modal opens and fetch dynamic checklist from server
  document.addEventListener('DOMContentLoaded', function() {
  // serverChecklist will hold tasks grouped by category returned from controller
  window.serverChecklist = {};

  // Active batches metadata (year and display name) exported from server for client-side rendering
  window.activeBatches = {!! json_encode($activeBatches->map(function($b){ return ['year' => $b->year, 'display_name' => $b->display_name]; })->toArray()) !!};

    const taskModal = document.getElementById('taskChecklistModal');
    if (taskModal) {
      // When modal is about to be shown, fetch checklist JSON for the selected week
      taskModal.addEventListener('show.bs.modal', function() {
        console.log('Modal showing, fetching checklist data...');

        // Optional: read a week start date input if present; fallback to today
        const weekInput = document.getElementById('week1_date');
        const weekStart = weekInput ? weekInput.value : null;

        // Build query
        const params = weekStart ? `?week_start_date=${encodeURIComponent(weekStart)}` : '';

        fetch(`/task-checklist/data${params}`, {
          headers: {
            'Accept': 'application/json'
          }
        })
        .then(resp => {
          if (!resp.ok) throw new Error('Failed to load checklist data');
          return resp.json();
        })
        .then(json => {
          if (json.success) {
            // store for rendering
            window.serverChecklist = json.tasks || {};
            window.currentWeekStart = json.week_start || null;

            // Render the first page using server data if available, otherwise fall back to static generator
            loadTaskPageContent(currentTaskPage);
            document.getElementById('currentPageNumber').textContent = currentTaskPage;
          } else {
            console.warn('Checklist data returned no tasks, using static fallback.');
            loadTaskPageContent(currentTaskPage);
            document.getElementById('currentPageNumber').textContent = currentTaskPage;
          }
        })
        .catch(err => {
          console.error('Error fetching checklist data:', err);
          // Fallback to static rendering
          loadTaskPageContent(currentTaskPage);
          document.getElementById('currentPageNumber').textContent = currentTaskPage;
        });
      });

      // When modal has been fully shown, ensure content is visible
      taskModal.addEventListener('shown.bs.modal', function() {
        console.log('Modal shown, content should be loaded');
        // If serverChecklist already loaded, render current page
        loadTaskPageContent(currentTaskPage);
        document.getElementById('currentPageNumber').textContent = currentTaskPage;
      });
    }

        // Add event listeners for confirm buttons
        // Confirm Add Members button
        const confirmAddBtn = document.getElementById('confirmAddMembers');
        if (confirmAddBtn) {
          confirmAddBtn.addEventListener('click', function() {
            if (selectedStudentsToAdd.length === 0) {
              alert('Please select at least one student to add.');
              return;
            }

            const studentIds = selectedStudentsToAdd.map(s => s.id);

            fetch(`/assignments/category/${currentCategoryId}/add-members`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                student_ids: studentIds
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showNotification('success', data.message);
                // Close modal
                bootstrap.Modal.getInstance(document.getElementById('addMembersModal')).hide();

                // If server returned member lists for immediate display, refresh the category modal in-place.
                // Expected server shape (best-effort): data.members2025, data.members2026, etc.
                try {
                  refreshStudentAssignModal(currentCategoryId);
                } catch (e) {
                  // Fallback to full reload if refresh fails
                  setTimeout(() => location.reload(), 1200);
                }
              } else {
                alert('Error adding members: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Error adding members');
            });
          });
        }

    // Auto-save remarks for server-backed tasks when textarea loses focus
    document.body.addEventListener('focusout', function(e) {
      const ta = e.target;
      if (ta && ta.tagName === 'TEXTAREA' && ta.dataset && ta.dataset.taskId) {
        const taskId = ta.dataset.taskId;
        const remarks = ta.value || '';

        const form = new FormData();
        form.append('task_id', taskId);
        form.append('remarks', remarks);

        fetch('/task-checklist/update-remarks', {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
          },
          body: form
        }).then(r => r.json()).then(j => {
          if (!j.success) console.warn('Could not save remarks', j);
        }).catch(err => console.error('Error saving remarks', err));
      }
    });

        // Confirm Delete Members button (remove from category only)
        const confirmDeleteBtn = document.getElementById('confirmDeleteMembers');
        if (confirmDeleteBtn) {
          confirmDeleteBtn.addEventListener('click', function() {
            if (selectedMembersToDelete.length === 0) {
              alert('Please select at least one member to remove from category.');
              return;
            }

            const memberIds = selectedMembersToDelete.map(m => m.id);

            if (!confirm(`Are you sure you want to remove ${selectedMembersToDelete.length} member(s) from this category?`)) {
              return;
            }

            fetch(`/assignments/category/${currentCategoryId}/remove-members`, {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                member_ids: memberIds
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showNotification('success', data.message);
                // Close modal and refresh page
                bootstrap.Modal.getInstance(document.getElementById('deleteMembersModal')).hide();
                setTimeout(() => location.reload(), 1500);
              } else {
                alert('Error removing members: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Error removing members');
            });
          });
        }

        // Confirm Delete from System button
        const confirmDeleteFromSystemBtn = document.getElementById('confirmDeleteFromSystem');
        if (confirmDeleteFromSystemBtn) {
          confirmDeleteFromSystemBtn.addEventListener('click', function() {
            if (selectedStudentsToDeleteFromSystem.length === 0) {
              alert('Please select at least one student to delete from system.');
              return;
            }

            const studentIds = selectedStudentsToDeleteFromSystem.map(s => s.id);
            const studentNames = selectedStudentsToDeleteFromSystem.map(s => buildStudentDisplayName(s)).join(', ');

            if (!confirm(`Are you sure you want to PERMANENTLY DELETE these students from the entire system?\n\n${studentNames}\n\nThis action cannot be undone and will remove them from all assignments.`)) {
              return;
            }

            fetch('/students/delete-multiple', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
              },
              body: JSON.stringify({
                student_ids: studentIds
              })
            })
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showNotification('success', data.message);
                // Close modal and refresh page
                bootstrap.Modal.getInstance(document.getElementById('deleteMembersModal')).hide();
                setTimeout(() => location.reload(), 1500);
              } else {
                alert('Error deleting students: ' + data.message);
              }
            })
            .catch(error => {
              console.error('Error:', error);
              alert('Error deleting students');
            });
          });
        }

        // Force load task content immediately as fallback
        setTimeout(() => {
            const taskPageContent = document.getElementById('taskPageContent');
            if (taskPageContent && taskPageContent.innerHTML.trim() === '') {
                console.log('Fallback: Loading task content...');
                loadTaskPageContent(1);
                document.getElementById('currentPageNumber').textContent = '1';
            }
        }, 500);

        // Also try to load content immediately when page loads
        setTimeout(() => {
            const taskPageContent = document.getElementById('taskPageContent');
            if (taskPageContent) {
                console.log('Immediate load: Loading Page 1 content...');
                try {
                    const page1Content = getPage1Content();
                    console.log('Page 1 content generated:', page1Content.substring(0, 200) + '...');
                    taskPageContent.innerHTML = page1Content;
                    document.getElementById('currentPageNumber').textContent = '1';
                    console.log('Page 1 content loaded successfully!');
                } catch (error) {
                    console.error('Error loading Page 1 content:', error);
                }
            }
        }, 100);

        // Add comprehensive test functions for all pages
        window.testTaskContent = function() {
            console.log('=== TESTING TASK CONTENT GENERATION ===');
            try {
                const page1 = getPage1Content();
                console.log('✅ Page 1 Content Length:', page1.length);
                console.log('✅ Page 1 Preview:', page1.substring(0, 500));

                const kitchenRows = getKitchenRows();
                console.log('✅ Kitchen Rows Length:', kitchenRows.length);

                const cleaningRows = getGeneralCleaningRows();
                console.log('✅ Cleaning Rows Length:', cleaningRows.length);

                const checkboxCells = generateCheckboxCells('test1');
                console.log('✅ Checkbox Cells Length:', checkboxCells.length);

                // Force load into modal
                const taskPageContent = document.getElementById('taskPageContent');
                if (taskPageContent) {
                    taskPageContent.innerHTML = page1;
                    console.log('✅ Content loaded into modal!');
                } else {
                    console.log('❌ taskPageContent element not found');
                }

            } catch (error) {
                console.error('❌ Error in test:', error);
            }
        };

        // Test all pages function
        window.testAllPages = function() {
            console.log('=== TESTING ALL 10 PAGES ===');
            const pages = [
                { num: 1, name: 'Kitchen & General Cleaning', func: getPage1Content },
                { num: 2, name: 'Dishwashing & General Cleaning', func: getPage2Content },
                { num: 3, name: 'Dining & Ground Floor', func: getPage3Content },
                { num: 4, name: 'Room 203 - Offices', func: getPage4Content },
                { num: 5, name: 'Room 301 - Offices', func: getPage5Content },
                { num: 6, name: 'Room 401 - Offices', func: getPage6Content },
                { num: 7, name: 'Room 303 - Offices', func: getPage7Content },
                { num: 8, name: 'Room 503 - Offices', func: getPage8Content },
                { num: 9, name: 'Garbage Collection', func: getPage9Content },
                { num: 10, name: 'Ground Floor', func: getPage10Content }
            ];

            pages.forEach(page => {
                try {
                    const content = page.func();
                    console.log(`✅ Page ${page.num} (${page.name}): ${content.length} characters`);
                    console.log(`   Preview: ${content.substring(0, 100)}...`);
                } catch (error) {
                    console.error(`❌ Page ${page.num} (${page.name}) Error:`, error);
                }
            });

            console.log('=== ALL PAGES TESTED ===');
        };

        // Function to show specific page
        window.showPage = function(pageNum) {
            console.log(`=== SHOWING PAGE ${pageNum} ===`);
            if (pageNum >= 1 && pageNum <= 10) {
                currentTaskPage = pageNum;
                const taskPageContent = document.getElementById('taskPageContent');
                if (taskPageContent) {
                    loadTaskPageContent(pageNum);
                    document.getElementById('currentPageNumber').textContent = pageNum;
                    console.log(`✅ Page ${pageNum} loaded!`);
                } else {
                    console.log('❌ taskPageContent element not found');
                }
            } else {
                console.log('❌ Invalid page number:', pageNum);
            }
        };

        // Quick access functions for each page
        window.showPage1 = () => showPage(1);
        window.showPage2 = () => showPage(2);
        window.showPage3 = () => showPage(3);
        window.showPage4 = () => showPage(4);
        window.showPage5 = () => showPage(5);
        window.showPage6 = () => showPage(6);
        window.showPage7 = () => showPage(7);
        window.showPage8 = () => showPage(8);
        window.showPage9 = () => showPage(9);
        window.showPage10 = () => showPage(10);

        // Function to show page summaries
        window.showPageSummaries = function() {
            console.log('=== TASK CHECKLIST PAGE SUMMARIES ===');
            console.log('📋 Page 1: Kitchen & General Cleaning');
            console.log('   - 17 Kitchen tasks (green category)');
            console.log('   - 6 General cleaning tasks (blue category)');
            console.log('');
            console.log('📋 Page 2: Dishwashing & General Cleaning');
            console.log('   - 9 Dishwashing tasks (yellow category)');
            console.log('   - 6 General cleaning tasks (blue category)');
            console.log('');
            console.log('📋 Page 3: Dining & Ground Floor');
            console.log('   - 8 Dining tasks (teal category)');
            console.log('   - Ground floor cleaning tasks');
            console.log('');
            console.log('📋 Page 4: Room 203 - Offices');
            console.log('   - Office cleaning tasks (green category)');
            console.log('');
            console.log('📋 Page 5: Room 301 - Offices');
            console.log('   - Office cleaning tasks (orange category)');
            console.log('');
            console.log('📋 Page 6: Room 401 - Offices');
            console.log('   - Office cleaning tasks (teal category)');
            console.log('');
            console.log('📋 Page 7: Room 303 - Offices');
            console.log('   - Office cleaning tasks (green category)');
            console.log('');
            console.log('📋 Page 8: Room 503 - Offices');
            console.log('   - Office cleaning tasks (purple category)');
            console.log('');
            console.log('📋 Page 9: Garbage Collection');
            console.log('   - Garbage collection tasks (pink category)');
            console.log('');
            console.log('📋 Page 10: Ground Floor');
            console.log('   - Ground floor maintenance tasks (gray category)');
            console.log('');
            console.log('🎯 Use showPage(1-10) to navigate to any page');
            console.log('🎯 Use testAllPages() to test all page generation');
        };
    });

    // Navigation functions for task checklist
    function changeTaskPage(direction) {
        console.log('changeTaskPage called with direction:', direction);
        console.log('Current page before change:', currentTaskPage);

        const newPage = currentTaskPage + direction;

        if (newPage >= 1 && newPage <= 10) {
            currentTaskPage = newPage;
            console.log('Loading new page:', currentTaskPage);

            try {
                loadTaskPageContent(currentTaskPage);
                document.getElementById('currentPageNumber').textContent = currentTaskPage;
                console.log('✅ Successfully loaded page:', currentTaskPage);
            } catch (error) {
                console.error('❌ Error loading page:', error);
            }
        } else {
            console.log('Page out of range:', newPage);
        }
    }

    // Single checkbox functions
    function showOptions(cellId) {
        const mainBox = document.getElementById(cellId);
        const optionsDiv = document.getElementById(cellId + '_options');

        // Hide main box and show options
        mainBox.style.display = 'none';
        optionsDiv.style.display = 'flex';
        optionsDiv.style.gap = '2px';
        optionsDiv.style.justifyContent = 'center';
        optionsDiv.style.alignItems = 'center';
    }

    function selectOption(cellId, option) {
        const mainBox = document.getElementById(cellId);
        const optionsDiv = document.getElementById(cellId + '_options');

        // Hide options and show main box with selected option
        optionsDiv.style.display = 'none';
        mainBox.style.display = 'flex';

        if (option === 'check') {
            mainBox.style.backgroundColor = '#4caf50';
            mainBox.style.color = 'white';
            mainBox.style.border = '2px solid #4caf50';
            mainBox.innerHTML = '✓';
            mainBox.setAttribute('data-selected', 'check');
        } else if (option === 'wrong') {
            mainBox.style.backgroundColor = '#007bff';
            mainBox.style.color = 'white';
            mainBox.style.border = '2px solid #007bff';
            mainBox.innerHTML = '✗';
            mainBox.setAttribute('data-selected', 'wrong');
        }

        // Add click handler to reset if clicked again
        mainBox.onclick = function() {
            resetBox(cellId);
        };
    }

    function resetBox(cellId) {
        const mainBox = document.getElementById(cellId);

        // Reset to original state - completely empty
        mainBox.style.backgroundColor = 'white';
        mainBox.style.color = 'black';
        mainBox.style.border = '2px solid black';
        mainBox.innerHTML = '';
        mainBox.removeAttribute('data-selected');

        // Restore original click handler
        mainBox.onclick = function() {
            showOptions(cellId);
        };
    }
  </script>

  <script>
    // Enhanced Add Task Area modal logic with area type selection
    (function(){
      const saveBtn = document.getElementById('saveNewTaskAreaBtn');
      const alertEl = document.getElementById('addAreaAlert');
      const container = document.getElementById('addedAreasContainer');
      const mainAreaTypeRadio = document.getElementById('mainAreaType');
      const subAreaTypeRadio = document.getElementById('subAreaType');
      const parentAreaSection = document.getElementById('parentAreaSection');
      const parentAreaSelect = document.getElementById('parentAreaSelect');
      const areaNameLabel = document.getElementById('areaNameLabel');
      const areaNameHint = document.getElementById('areaNameHint');
      const areaNameInput = document.getElementById('addAreaName');
      const areaDescriptionInput = document.getElementById('addAreaDescription');

      // Load main areas for parent selection
      async function loadMainAreas() {
        try {
          const response = await fetch('/api/main-areas', {
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
          });
          
          if (response.ok) {
            const data = await response.json();
            if (data.success && data.main_areas) {
              parentAreaSelect.innerHTML = '<option value="">Select a main area...</option>';
              data.main_areas.forEach(area => {
                const option = document.createElement('option');
                option.value = area.id;
                option.textContent = area.name;
                parentAreaSelect.appendChild(option);
              });
            }
          }
        } catch (error) {
          console.error('Error loading main areas:', error);
        }
      }

      // Handle area type change
      function handleAreaTypeChange() {
        const isSubArea = subAreaTypeRadio.checked;
        
        if (isSubArea) {
          parentAreaSection.style.display = 'block';
          areaNameLabel.textContent = 'Sub Area Name';
          areaNameHint.textContent = 'Enter a name for the sub-area under the selected main area';
          areaNameInput.placeholder = 'e.g. Operations Center, Dishwashing Station';
          loadMainAreas();
        } else {
          parentAreaSection.style.display = 'none';
          areaNameLabel.textContent = 'Main Area Name';
          areaNameHint.textContent = 'Enter a descriptive name for your main area';
          areaNameInput.placeholder = 'e.g. Kitchen Area, Dormitory Office';
        }
      }

      // Initialize event listeners
      if (mainAreaTypeRadio && subAreaTypeRadio) {
        mainAreaTypeRadio.addEventListener('change', handleAreaTypeChange);
        subAreaTypeRadio.addEventListener('change', handleAreaTypeChange);
        
        // Initialize on page load
        handleAreaTypeChange();
      }

      // Reset modal when opened
      document.getElementById('addTaskAreaModal').addEventListener('show.bs.modal', function() {
        // Reset form
        if (mainAreaTypeRadio) mainAreaTypeRadio.checked = true;
        if (subAreaTypeRadio) subAreaTypeRadio.checked = false;
        if (areaNameInput) areaNameInput.value = '';
        if (areaDescriptionInput) areaDescriptionInput.value = '';
        if (parentAreaSelect) parentAreaSelect.value = '';
        
        // Reset UI
        handleAreaTypeChange();
        
        // Hide alert
        if (alertEl) alertEl.classList.add('d-none');
      });

      function showAddAlert(msg, type='success'){
        if(!alertEl) return;
        alertEl.className = 'alert alert-' + type;
        alertEl.textContent = msg;
        alertEl.classList.remove('d-none');
        setTimeout(()=> alertEl.classList.add('d-none'), 4000);
      }

      async function appendAreaToContainer(cat){
        if(!container) return;
        
        // Get the Main Area and Sub Area values from the form
        const mainAreaName = document.getElementById('addMainAreaName').value.trim();
        const subAreaName = document.getElementById('addSubArea').value.trim() || cat.name;
        
        // Create a new Main Area section with proper structure
        const mainAreaDiv = document.createElement('div');
        mainAreaDiv.className = 'mb-2 mt-3';
        mainAreaDiv.style.opacity = '0';
        mainAreaDiv.style.transform = 'translateY(20px)';
        mainAreaDiv.style.transition = 'all 0.5s ease';
        
        // Create the main area header
        const headerHtml = `
          <h4 class="mb-3" style="font-size: 1.2rem; font-weight: 600; color: #333; border-bottom: 2px solid #007bff; padding-bottom: 8px;">
            ${escapeHtml(mainAreaName)}
          </h4>
        `;
        
        // Create the card container
        const cardContainerHtml = `
          <div class="row g-3">
            <div class="col-lg-3 col-md-4 col-sm-6 mb-3">
              <div class="category-card text-start p-0 overflow-hidden" style="background:none; border:none; box-shadow:none;">
                <div style="height:100%; min-height:180px; max-height:220px; border-radius:10px; padding:10px; background:#f8f9fa; box-shadow: 0 0 6px rgba(0,0,0,0.05); position: relative;" class="kitchen-operations-card">
                  <div class="category-label" style="background:none; border:none; margin-bottom:4px; font-size:0.8rem; font-weight:600; color:#333;">
                    ${escapeHtml(subAreaName)}
                  </div>
                  <div class="mb-2">
                    <span class="badge" style="background:#1565c0; color:#fff; font-weight:500; font-size:0.7rem; padding:3px 6px;">Male: 0</span>
                    <span class="badge" style="background:#e91e63; color:#fff; font-weight:500; font-size:0.7rem; padding:3px 6px; margin-left:4px;">Female: 0</span>
                  </div>
                  <div style="font-size:0.7rem; color:#666; margin-bottom:8px;">Valid: Not assigned yet</div>
                  <div style="font-size:0.7rem; color:#666; margin-bottom:8px;">
                    <strong>C2025 Coordinator:</strong> —<br>
                    <strong>C2026 Coordinator:</strong> —
                  </div>
                  <div style="font-size:0.65rem; color:#999; font-style:italic; margin-bottom:12px;">
                    ${escapeHtml(cat.description || 'No description provided.')}
                  </div>
                  <div class="d-flex justify-content-start gap-1">
                    <button class="btn btn-outline-primary btn-sm" onclick="openStudentModal(${cat.id}, '${escapeHtml(subAreaName)}')" style="font-size:0.65rem; padding:4px 8px;">View Members</button>
                    <button class="btn btn-primary btn-sm" onclick="openManageTasksModal(${cat.id}, '${escapeHtml(subAreaName)}')" style="font-size:0.65rem; padding:4px 8px;">Manage Tasks</button>
                  </div>
                  <div class="position-absolute top-0 end-0 p-2">
                    <button class="btn btn-sm btn-outline-secondary edit-capacity-btn me-1" data-category-id="${cat.id}" data-category-name="${escapeHtml(subAreaName)}" title="Edit Assignment" style="font-size:0.6rem; padding:2px 6px;">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger delete-capacity-btn" data-category-id="${cat.id}" data-category-name="${escapeHtml(subAreaName)}" title="Delete Task" style="font-size:0.6rem; padding:2px 6px;">
                      <i class="bi bi-trash"></i>
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        `;
        
        mainAreaDiv.innerHTML = headerHtml + cardContainerHtml;
        
        // Add to container
        container.appendChild(mainAreaDiv);
        
        // Animate the main area in
        setTimeout(() => {
          mainAreaDiv.style.opacity = '1';
          mainAreaDiv.style.transform = 'translateY(0)';
        }, 100);
        
        // Show success notification with main area creation
        setTimeout(() => {
          showAddAlert(`✨ New main area "${mainAreaName}" with task "${subAreaName}" created successfully!`, 'success');
        }, 600);
        
        // Add special handling for dynamically created cards
        const deleteBtn = mainAreaDiv.querySelector('.delete-capacity-btn');
        const editBtn = mainAreaDiv.querySelector('.edit-capacity-btn');
        
        if (deleteBtn) {
          deleteBtn.addEventListener('click', function(e) {
            e.preventDefault();
            const categoryId = this.dataset.categoryId;
            const categoryName = this.dataset.categoryName || cat.name;
            
            if (!confirm(`Delete "${categoryName}"? This will remove the task card.`)) return;
            
            // For temporary/local cards, just remove from DOM
            if (categoryId.startsWith('temp_')) {
              // Animate out the entire main area
              mainAreaDiv.style.transition = 'all 0.5s ease';
              mainAreaDiv.style.opacity = '0';
              mainAreaDiv.style.transform = 'translateY(-20px)';
              
              setTimeout(() => {
                mainAreaDiv.remove();
                showAddAlert(`Main area "${mainAreaName}" removed`, 'info');
              }, 500);
            } else {
              // For real server cards, use the existing server delete logic
              fetch(`/assignments/category/${categoryId}/current`, {
                method: 'DELETE',
                headers: { 
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), 
                  'Accept': 'application/json' 
                }
              })
              .then(r => r.json())
              .then(j => {
                if (j.success) {
                  // Animate out the entire main area
                  mainAreaDiv.style.transition = 'all 0.5s ease';
                  mainAreaDiv.style.opacity = '0';
                  mainAreaDiv.style.transform = 'translateY(-20px)';
                  
                  setTimeout(() => {
                    mainAreaDiv.remove();
                    showAddAlert(`Task card "${categoryName}" deleted`, 'success');
                  }, 500);
                } else {
                  showAddAlert(j.message || 'Failed to delete', 'danger');
                }
              })
              .catch(err => {
                console.error('Error deleting:', err);
                showAddAlert('Error deleting task card', 'danger');
              });
            }
          });
        }
        
        if (editBtn) {
          editBtn.addEventListener('click', function(e) {
            e.preventDefault();
            showAddAlert('Edit functionality will be available after page reload', 'info');
          });
        }
      }

      function escapeHtml(s){ if(!s) return ''; return String(s).replace(/[&<>'"`]/g, function(ch){ return {'&':'&amp;','<':'&lt;','>':'&gt;','\'':'&#39;','"':'&quot;','`':'&#96;'}[ch]; }); }

      if(saveBtn){
        saveBtn.addEventListener('click', async function(){
          // Get form values from enhanced modal
          const areaName = areaNameInput.value.trim();
          const areaDescription = areaDescriptionInput.value.trim();
          const isSubArea = subAreaTypeRadio.checked;
          const parentId = isSubArea ? parentAreaSelect.value : null;
          
          // Validation
          if(!areaName){ 
            showAddAlert('Area name is required','danger'); 
            return; 
          }
          
          if(isSubArea && !parentId){ 
            showAddAlert('Please select a parent area for the sub-area','danger'); 
            return; 
          }
          
          try{
            // Disable save button during request
            saveBtn.disabled = true;
            const originalText = saveBtn.innerHTML;
            saveBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Creating...';
            
            console.log('Attempting to save:', { 
              name: areaName, 
              description: areaDescription,
              area_type: isSubArea ? 'sub' : 'main',
              parent_id: parentId
            });
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            // Use the new task-areas endpoint
            const res = await fetch('/task-areas', {
              method: 'POST', 
              headers: { 
                'Content-Type':'application/json',
                'X-CSRF-TOKEN': csrfToken, 
                'Accept':'application/json' 
              },
              body: JSON.stringify({ 
                name: areaName, 
                description: areaDescription,
                area_type: isSubArea ? 'sub' : 'main',
                parent_id: parentId
              })
            });
            
            console.log('Response status:', res.status);
            console.log('Response headers:', res.headers);
            
            let j;
            try {
              j = await res.json();
              console.log('Response data:', j);
            } catch (parseError) {
              console.error('Failed to parse JSON response:', parseError);
              const textResponse = await res.text();
              console.log('Raw response:', textResponse);
              throw new Error('Invalid JSON response from server');
            }
            
            if(res.ok && j.success){
              // Show success message
              showAddAlert(`✅ ${j.message}`, 'success');
              
              // Clear form and close modal
              areaNameInput.value = '';
              areaDescriptionInput.value = '';
              if (parentAreaSelect) parentAreaSelect.value = '';
              mainAreaTypeRadio.checked = true;
              subAreaTypeRadio.checked = false;
              handleAreaTypeChange();
              
              var modalEl = document.getElementById('addTaskAreaModal');
              var modal = bootstrap.Modal.getInstance(modalEl);
              if(modal) modal.hide();
              
              // Show different notifications based on area type
              setTimeout(() => {
                const notification = document.createElement('div');
                notification.className = 'alert alert-success alert-dismissible fade show';
                notification.style.position = 'fixed';
                notification.style.top = '80px';
                notification.style.right = '20px';
                notification.style.zIndex = '9999';
                notification.style.minWidth = '400px';
                
                if (j.is_main_area) {
                  notification.innerHTML = `
                    <div class="d-flex align-items-center">
                      <i class="bi bi-building me-3" style="font-size: 1.5rem; color: #28a745;"></i>
                      <div>
                        <strong>Main Area Created!</strong><br>
                        <small>"${j.category.name}" is now available as a container. Add sub-areas under it to create task cards.</small>
                      </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  `;
                } else {
                  notification.innerHTML = `
                    <div class="d-flex align-items-center">
                      <i class="bi bi-card-checklist me-3" style="font-size: 1.5rem; color: #28a745;"></i>
                      <div>
                        <strong>Task Card Created!</strong><br>
                        <small>"${j.category.name}" will appear as a task card and can be used in auto-shuffle.</small>
                      </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                  `;
                }
                
                document.body.appendChild(notification);
                
                // Auto remove after 6 seconds
                setTimeout(() => {
                  try { notification.remove(); } catch(e) {}
                }, 6000);
              }, 300);
              
              // Refresh page to show new area
              setTimeout(() => {
                window.location.reload();
              }, 2500);
              
            } else {
              console.error('Save failed:', j);
              const errorMsg = (j && j.message) ? j.message : 
                              (j && j.errors) ? Object.values(j.errors).flat().join('; ') : 
                              'Failed to create task area';
              
              showAddAlert(`❌ ${errorMsg}`, 'danger');
            }
          } catch(err) { 
            console.error('Save error:', err); 
            showAddAlert(`❌ Network error: ${err.message}`, 'danger'); 
          } finally {
            // Restore button state
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Create Area';
          }
        });
      }
    })();
  </script>

  <script>
    // Manage Tasks modal opener
    document.addEventListener('DOMContentLoaded', function() {
      document.querySelectorAll('.manage-tasks-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
          var catId = this.getAttribute('data-category-id');
          var catName = this.getAttribute('data-category-name');
          document.getElementById('manageTasksCategoryName').textContent = catName;
          // Load tasks via AJAX placeholder (implement endpoint as needed)
          var content = document.getElementById('manageTasksContent');
          content.innerHTML = '<p class="text-muted">Loading tasks for ' + catName + '...</p>';
          // Example: fetch(`/areas/'+catId+'/tasks`).then(...)
          // For now show a placeholder list
          setTimeout(function(){
            content.innerHTML = `
              <div class="list-group">
                <div class="list-group-item d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-bold">Prepare vegetables</div>
                    <div class="text-muted small">Chop onions, carrots, and celery for today's soup</div>
                  </div>
                  <div>
                    <button class="btn btn-sm btn-outline-primary me-2">Edit</button>
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </div>
                </div>
                <div class="list-group-item d-flex justify-content-between align-items-start">
                  <div>
                    <div class="fw-bold">Clean cooking equipment</div>
                    <div class="text-muted small">Wash and sanitize all pots, pans, and utensils</div>
                  </div>
                  <div>
                    <button class="btn btn-sm btn-outline-primary me-2">Edit</button>
                    <button class="btn btn-sm btn-outline-danger">Delete</button>
                  </div>
                </div>
              </div>
            `;
          }, 400);
          var modal = new bootstrap.Modal(document.getElementById('manageTasksModal'));
          modal.show();
        });
      });

      // After initial placeholder code, wire real CRUD operations
      const manageTasksState = {
        roomNumber: null,
      };

     // Function to show alert messages
  function showAddAlert(message, type = 'success') {
    const alertDiv = document.getElementById('addTaskAreaAlert');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
      ${message}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    alertDiv.style.display = 'block';
  }

      // When modal shown, load tasks for the selected category/room
      document.getElementById('manageTasksModal').addEventListener('show.bs.modal', function(e){
        const titleSpan = document.getElementById('manageTasksCategoryName');
        const areaNameSpan = document.getElementById('manageTasksAreaName');
        // room number is stored on the button that triggered the modal via data-room-number
        const trigger = document.querySelector('.manage-tasks-btn[data-bs-toggle]') || null;
        // fallback: preserve current state.roomNumber
        if (!manageTasksState.roomNumber && trigger && trigger.dataset.roomNumber) {
          manageTasksState.roomNumber = trigger.dataset.roomNumber;
        }
        // If still no room number, try to parse from title (best-effort)
        // Reset UI
        document.getElementById('manageTasksList').innerHTML = '';
        document.getElementById('manageTaskForm').classList.add('d-none');
      });

      // Public function used by opener handler to set the room and load tasks
      function openManageTasksForRoom(roomNumber, categoryName, areaName){
        manageTasksState.roomNumber = roomNumber;
        document.getElementById('manageTasksCategoryName').textContent = categoryName || roomNumber;
        document.getElementById('manageTasksAreaName').textContent = areaName || '-';
        loadManageTasks(roomNumber);
        const modal = new bootstrap.Modal(document.getElementById('manageTasksModal'));
        modal.show();
      }

      // Replace earlier opener binding to call openManageTasksForRoom
      document.querySelectorAll('.manage-tasks-btn').forEach(function(btn){
        btn.addEventListener('click', function(e){
          const catId = this.getAttribute('data-category-id') || this.getAttribute('data-room-number');
          const catName = this.getAttribute('data-category-name') || this.getAttribute('data-category');
          const areaName = this.getAttribute('data-area-name') || this.getAttribute('data-category-name') || '';
          openManageTasksForRoom(catId, catName, areaName);
        });
      });

      async function loadManageTasks(roomNumber){
        try{
          document.getElementById('manageTasksList').innerHTML = '<div class="text-muted">Loading...</div>';
          // Use general TaskController endpoint to get merged task statuses/templates
          const payload = {
            day: 'Monday', // default day for listing in manage modal
            room: roomNumber,
            week: (new Date()).getWeek ? (new Date()).getWeek() : '1',
            month: String(new Date().getMonth() + 1),
            year: String(new Date().getFullYear())
          };
          const res = await fetch('/get-task-statuses', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify(payload)
          });
          const json = await res.json();
          if(!json.success){
            document.getElementById('manageTasksList').innerHTML = '<div class="text-danger">Failed to load tasks</div>';
            return;
          }
          renderManageTasksList(json.tasks || []);
        }catch(err){
          console.error(err);
          document.getElementById('manageTasksList').innerHTML = '<div class="text-danger">Error loading tasks</div>';
        }
      }

      function renderManageTasksList(tasks){
        const container = document.getElementById('manageTasksList');
        container.innerHTML = '';
        if(!tasks || tasks.length === 0){
          container.innerHTML = '<div class="text-muted">No tasks yet.</div>';
          return;
        }
        tasks.forEach(task => {
          const item = document.createElement('div');
          item.className = 'list-group-item d-flex justify-content-between align-items-start';
          // Removed day suffix display per request — show only area and description
          item.innerHTML = `
            <div>
              <div class="fw-bold">${escapeHtml(task.area || task.area)}</div>
              <div class="text-muted small">${escapeHtml(task.desc || task.description || '')}</div>
            </div>
            <div>
              <button class="btn btn-sm btn-outline-primary me-2 btn-edit-task" data-task-id="${task.id}">Edit</button>
              <button class="btn btn-sm btn-outline-danger btn-delete-task" data-task-id="${task.id}">Delete</button>
            </div>
          `;
          container.appendChild(item);
        });

        // wire edit/delete buttons
        container.querySelectorAll('.btn-edit-task').forEach(b => b.addEventListener('click', onEditTaskClick));
        container.querySelectorAll('.btn-delete-task').forEach(b => b.addEventListener('click', onDeleteTaskClick));
      }

      function escapeHtml(str){
        if(!str) return '';
        return String(str).replace(/[&<>"'`]/g, function(s){
          return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":"&#39;","`":"&#96;"})[s];
        });
      }

      // Open add form
      document.getElementById('btnOpenAddTask').addEventListener('click', function(){
        document.getElementById('manageTaskId').value = '';
        document.getElementById('manageTaskArea').value = '';
        document.getElementById('manageTaskDescription').value = '';
        document.getElementById('manageTaskForm').classList.remove('d-none');
      });

      document.getElementById('manageTaskCancel').addEventListener('click', function(){
        document.getElementById('manageTaskForm').classList.add('d-none');
      });

      // Save (create or update)
      document.getElementById('manageTaskSave').addEventListener('click', async function(){
        const id = document.getElementById('manageTaskId').value;
        const area = document.getElementById('manageTaskArea').value.trim();
        const desc = document.getElementById('manageTaskDescription').value.trim();
  const day = 'Monday'; // default day — Day field removed per request
        if(!area || !desc){
          showManageTasksAlert('Area and description are required','danger');
          return;
        }
        try{
          if(id){
            // update via general save-task endpoint (mode: edit)
            const res = await fetch('/save-task', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
              },
              body: JSON.stringify({ name: 'Everyone', area: area, desc: desc, day: day, room: manageTasksState.roomNumber, mode: 'edit', taskId: id })
            });
            if (res.status === 422) {
              const err = await res.json();
              const msgs = (err.errors) ? Object.values(err.errors).flat().join(' ') : (err.message || 'Validation failed');
              showManageTasksAlert(msgs, 'danger');
            } else {
              const j = await res.json();
              if(j.success){
                showManageTasksAlert('Task updated');
                document.getElementById('manageTaskForm').classList.add('d-none');
                loadManageTasks(manageTasksState.roomNumber);
              } else {
                showManageTasksAlert(j.message || 'Failed to update','danger');
              }
            }
          } else {
            // create via general save-task endpoint (mode: add)
            const res = await fetch('/save-task', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
              },
              body: JSON.stringify({ name: 'Everyone', area: area, desc: desc, day: day, room: manageTasksState.roomNumber, mode: 'add' })
            });
            if (res.status === 422) {
              const err = await res.json();
              const msgs = (err.errors) ? Object.values(err.errors).flat().join(' ') : (err.message || 'Validation failed');
              showManageTasksAlert(msgs, 'danger');
            } else {
              const j = await res.json();
              if(j.success){
                showManageTasksAlert('Task created');
                document.getElementById('manageTaskForm').classList.add('d-none');
                loadManageTasks(manageTasksState.roomNumber);
              } else {
                showManageTasksAlert(j.message || 'Failed to create','danger');
              }
            }
          }
        }catch(err){
          console.error(err);
          showManageTasksAlert('Unexpected error','danger');
        }
      });

      async function onEditTaskClick(e){
        const id = this.dataset.taskId;
        try{
          // Use general get-task-statuses to retrieve current tasks
          const res = await fetch('/get-task-statuses', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' },
            body: JSON.stringify({ day: 'Monday', room: manageTasksState.roomNumber, week: (new Date()).getWeek ? (new Date()).getWeek() : '1', month: String(new Date().getMonth() + 1), year: String(new Date().getFullYear()) })
          });
          const j = await res.json();
          const task = (j.tasks || []).find(t => String(t.id) === String(id));
          if(!task){ showManageTasksAlert('Task not found','danger'); return; }
          document.getElementById('manageTaskId').value = task.id;
          document.getElementById('manageTaskArea').value = task.area || '';
          document.getElementById('manageTaskDescription').value = task.desc || task.description || '';
          // Day removed from form — server will default to Monday if needed
          document.getElementById('manageTaskForm').classList.remove('d-none');
        }catch(err){ console.error(err); showManageTasksAlert('Failed to load task for edit','danger'); }
      }

      async function onDeleteTaskClick(e){
        if(!confirm('Delete this task?')) return;
        const id = this.dataset.taskId;
        try{
          const res = await fetch(`/room-management/tasks/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content, 'Accept': 'application/json' }
          });
          const j = await res.json();
          if(j.success){ showManageTasksAlert('Task deleted'); loadManageTasks(manageTasksState.roomNumber); }
          else showManageTasksAlert(j.message || 'Failed to delete','danger');
        }catch(err){ console.error(err); showManageTasksAlert('Failed to delete','danger'); }
      }

      // Placeholder: load areas list when Manage Areas modal opens
      var manageAreasModalEl = document.getElementById('manageAreasModal');
      if (manageAreasModalEl) {
        manageAreasModalEl.addEventListener('show.bs.modal', function (event) {
          var container = document.getElementById('manageAreasContent');
          container.innerHTML = '<p class="text-muted">Loading areas...</p>';
          setTimeout(function(){
            // Dynamic area list using the new structure
            container.innerHTML = `
              <ul class="list-group">
                @foreach($dynamicStructure as $mainAreaName => $subAreaCategories)
                  <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                      <strong>{{ $mainAreaName }}</strong>
                      <small class="text-muted d-block">{{ $subAreaCategories->count() }} sub-areas</small>
                    </div>
                    <div>
                      <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                      <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </div>
                  </li>
                  @foreach($subAreaCategories as $subArea)
                    <li class="list-group-item ps-4" style="border-left: 3px solid #28a745;">
                      <div class="d-flex justify-content-between align-items-center">
                        <div>
                          <span>{{ $subArea->name }}</span>
                          <small class="text-muted d-block">Sub-area (Task Card)</small>
                        </div>
                        <div>
                          <button class="btn btn-sm btn-outline-primary me-1">Edit</button>
                          <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </div>
                      </div>
                    </li>
                  @endforeach
                @endforeach
              </ul>
            `;
          }, 300);
        });
      }
    });
  </script>

  <script>
    // Consolidated handlers for Edit/Delete Task Assignment modal
    document.addEventListener('DOMContentLoaded', function() {
      const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

      // Open Edit Capacity Modal when pencil icon clicked
      document.querySelectorAll('.edit-capacity-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
          e.preventDefault();
          const categoryId = this.dataset.categoryId;
          const categoryName = this.dataset.categoryName || '';

          // Populate basic fields
          document.getElementById('edit_category_id').value = categoryId;
          document.getElementById('task_name').value = categoryName;

          // Reset coordinator/description fields while loading
          // Ensure Auto checkboxes are unchecked so user can input coordinators by default
          const auto2025 = document.getElementById('auto_assign_coord_2025');
          const auto2026 = document.getElementById('auto_assign_coord_2026');
          if (auto2025) { auto2025.checked = false; }
          if (auto2026) { auto2026.checked = false; }
          const coord25 = document.getElementById('coordinator_2025');
          const coord26 = document.getElementById('coordinator_2026');
          if (coord25) { coord25.removeAttribute('disabled'); coord25.classList.remove('bg-light'); coord25.value = ''; }
          if (coord26) { coord26.removeAttribute('disabled'); coord26.classList.remove('bg-light'); coord26.value = ''; }
          document.getElementById('task_description').value = '';

          // Fetch current members to compute assigned counts and coordinators
          fetch(`/assignments/category/${categoryId}/members`, { headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(json => {
              if (!json) throw new Error('No response');

              // Normalize
              const members2025 = json.members2025 || json.membersByBatch?.['2025'] || (json.members || []).filter(m => (m.student && (m.student.batch == 2025)) || m.batch == 2025) || [];
              const members2026 = json.members2026 || json.membersByBatch?.['2026'] || (json.members || []).filter(m => (m.student && (m.student.batch == 2026)) || m.batch == 2026) || [];

              // We no longer show assigned counts in the modal per request. Populate description/coordinator.

              // Find coordinator names
              const coord2025 = members2025.find(m => m.is_coordinator) || null;
              const coord2026 = members2026.find(m => m.is_coordinator) || null;
              function getName(m) {
                if (!m) return '';
                if (m.student) return ((m.student.user_fname || '') + ' ' + (m.student.user_lname || '')).trim() || m.student.name || '';
                return (m.user_fname || '') + ' ' + (m.user_lname || '') || m.name || '';
              }
              // Populate coordinator inputs (still optional; user may overwrite or check Auto)
              document.getElementById('coordinator_2025').value = getName(coord2025);
              document.getElementById('coordinator_2026').value = getName(coord2026);
              // If server returned a description or saved metadata, populate it
              const meta = json.metadata || {};
              if (meta && typeof meta === 'object') {
                if (meta.coordinator_2025) document.getElementById('coordinator_2025').value = meta.coordinator_2025;
                if (meta.coordinator_2026) document.getElementById('coordinator_2026').value = meta.coordinator_2026;
                if (typeof meta.auto_assign_coord_2025 !== 'undefined') document.getElementById('auto_assign_coord_2025').checked = !!meta.auto_assign_coord_2025;
                if (typeof meta.auto_assign_coord_2026 !== 'undefined') document.getElementById('auto_assign_coord_2026').checked = !!meta.auto_assign_coord_2026;
                if (meta.description) document.getElementById('task_description').value = meta.description;
              } else if (json.description) {
                document.getElementById('task_description').value = json.description || '';
              }
            })
            .catch(err => {
              console.error('Error loading members for modal:', err);
              // On error, clear optional inputs
              document.getElementById('coordinator_2025').value = '';
              document.getElementById('coordinator_2026').value = '';
              document.getElementById('task_description').value = '';
            })
            .finally(() => {
              // Show modal
              const mdl = new bootstrap.Modal(document.getElementById('editCapacityModal'));
              mdl.show();
            });
        });
      });

      // Save changes: persist overrides and then trigger auto-shuffle
      const saveBtn = document.getElementById('saveEditCapacityBtn');
      if (saveBtn) {
        saveBtn.addEventListener('click', async function() {
          const categoryId = document.getElementById('edit_category_id').value;
          const b2025_boys = parseInt(document.getElementById('batch2025_boys_required').value) || 0;
          const b2025_girls = parseInt(document.getElementById('batch2025_girls_required').value) || 0;
          const b2026_boys = parseInt(document.getElementById('batch2026_boys_required').value) || 0;
          const b2026_girls = parseInt(document.getElementById('batch2026_girls_required').value) || 0;
          const startDate = document.getElementById('start_date').value || '';
          const endDate = document.getElementById('end_date').value || '';
          const description = document.getElementById('task_description').value || null;

          // Basic validation
          if (!categoryId) {
            alert('Missing category id');
            return;
          }

          const payload = {
            category_id: categoryId,
            batch_requirements: {
              2025: { boys: b2025_boys, girls: b2025_girls },
              2026: { boys: b2026_boys, girls: b2026_girls }
            },
            start_date: startDate,
            end_date: endDate,
            coordinators: {
              2025: document.getElementById('coordinator_2025').value || null,
              2026: document.getElementById('coordinator_2026').value || null
            },
            auto_assign_coordinator: {
              2025: !!document.getElementById('auto_assign_coord_2025')?.checked,
              2026: !!document.getElementById('auto_assign_coord_2026')?.checked
            },
            description: description
          };

          // Disable button while saving
          saveBtn.disabled = true;
          saveBtn.textContent = 'Saving...';

          try {
            // Save overrides
            const r1 = await fetch('/assignments/update-capacity', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
              },
              body: JSON.stringify(payload)
            });

            if (!r1.ok) {
              let errMsg = `Save failed: ${r1.status} ${r1.statusText}`;
              try { const j = await r1.json(); if (j && j.message) errMsg = j.message; } catch(e){}
              throw new Error(errMsg);
            }

            const j = await r1.json();
            if (!j.success) throw new Error(j.message || 'Failed to save');

            // If there are warnings (e.g., batch mismatch resolution warnings), show a non-blocking toast
            if (j.warnings && Array.isArray(j.warnings) && j.warnings.length > 0) {
              // create a simple alert at top of page so user sees the warning but save is allowed
              const warnText = j.warnings.join('\n');
              const alertEl = document.createElement('div');
              alertEl.className = 'alert alert-warning alert-dismissible fade show';
              alertEl.style.position = 'fixed';
              alertEl.style.top = '10px';
              alertEl.style.right = '10px';
              alertEl.style.zIndex = 2000;
              alertEl.innerHTML = `<strong>Warning:</strong> ${warnText} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>`;
              document.body.appendChild(alertEl);
              setTimeout(()=>{ try{ alertEl.remove(); }catch(e){} }, 8000);
            }

            // Close modal
            bootstrap.Modal.getInstance(document.getElementById('editCapacityModal')).hide();

            // Trigger auto-shuffle
            const r2 = await fetch('/assignments/auto-shuffle', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
              },
              body: JSON.stringify({ overrides: { [j.category_name || categoryId]: { batch_requirements: payload.batch_requirements, start_date: startDate, end_date: endDate } } })
            });

            // If redirected, follow it
            if (r2.redirected) {
              window.location = r2.url;
              return;
            }

            if (!r2.ok) {
              let errMsg = `Auto-shuffle failed: ${r2.status} ${r2.statusText}`;
              try { const jr = await r2.json(); if (jr && jr.message) errMsg = jr.message; } catch(e){}
              // Show server message but still attempt to reload so UI reflects saved overrides
              showNotification(errMsg, 'danger');
              setTimeout(() => location.reload(), 1200);
              return;
            }

            const ct = r2.headers.get('content-type') || '';
            if (ct.indexOf('application/json') !== -1) {
              const res = await r2.json();
              if (res && res.success) {
                showNotification('Auto-shuffle completed', 'success');
                setTimeout(() => location.reload(), 900);
              } else {
                showNotification(res?.message || 'Saved. Auto-shuffle may have failed; check logs.', 'danger');
                setTimeout(() => location.reload(), 1200);
              }
            } else {
              // Non-JSON (likely HTML) — reload the page to reflect changes
              window.location.reload();
            }

          } catch (err) {
            console.error('Error saving capacity or running auto-shuffle:', err);
            // Show a helpful message to user
            const userMsg = (err && err.message) ? err.message : 'Error saving or running auto-shuffle';
            showNotification(userMsg, 'danger');
          } finally {
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Changes';
          }
        });
      }

      // Delete task area/category permanently (delegated)
      document.addEventListener('click', function(e) {
        const el = e.target.closest && e.target.closest('.delete-capacity-btn');
        if (!el) return;
        e.preventDefault();
        const categoryId = el.dataset.categoryId;
        let categoryName = el.dataset.categoryName || '';

        // Try to extract visible category name from nearby DOM if not provided
        if (!categoryName) {
          const parentCard = el.closest('.category-card');
          if (parentCard) {
            const label = parentCard.querySelector('.category-label');
            if (label) categoryName = label.textContent.trim();
          }
        }

        // Enhanced confirmation dialog
        const confirmMessage = `⚠️ PERMANENT DELETION WARNING ⚠️\n\nAre you sure you want to permanently delete "${categoryName || 'this task area'}"?\n\nThis will:\n• Remove the task area completely\n• Delete all assignments for this area\n• Remove all student assignments\n• Cannot be undone\n\nType "DELETE" to confirm:`;
        
        const userInput = prompt(confirmMessage);
        if (userInput !== 'DELETE') {
          showNotification('Deletion cancelled', 'info');
          return;
        }

        // Show loading state
        el.disabled = true;
        el.innerHTML = '<i class="bi bi-hourglass-split"></i>';
        
        // Use the new task-areas delete endpoint
        fetch(`/task-areas/${categoryId}`, {
          method: 'DELETE',
          headers: { 
            'X-CSRF-TOKEN': csrfToken, 
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          }
        })
        .then(r => r.json())
        .then(j => {
          if (j.success) {
            showNotification(`✅ Task area "${categoryName}" permanently deleted`, 'success');
            
            // Remove the task card from the DOM with animation
            const taskCard = el.closest('.col-lg-4') || el.closest('.category-card') || el.closest('[class*="col-"]');
            if (taskCard) {
              // Add deletion animation class
              taskCard.classList.add('deleting-card');
              
              // Remove the card after animation
              setTimeout(() => {
                taskCard.remove();
                
                // Check if section is now empty and hide it
                const section = taskCard.closest('.section-container') || taskCard.closest('[class*="Area"]');
                if (section) {
                  const remainingCards = section.querySelectorAll('.col-lg-4, .category-card, [class*="col-"]');
                  if (remainingCards.length === 0) {
                    section.style.transition = 'all 0.5s ease';
                    section.style.opacity = '0';
                    setTimeout(() => {
                      section.remove();
                      showNotification(`Section cleaned up - no more task areas in this section`, 'info');
                    }, 500);
                  }
                }
              }, 600);
              
              // Reload page after a delay to ensure data consistency
              setTimeout(() => {
                showNotification('Refreshing page to update data...', 'info');
                location.reload();
              }, 2500);
            } else {
              // Fallback: immediate reload if card not found
              setTimeout(() => location.reload(), 1000);
            }
          } else {
            // Handle specific error messages
            const errorMsg = j.message || 'Failed to delete task area';
            showNotification(`❌ ${errorMsg}`, 'danger');
            
            // Restore button state
            el.disabled = false;
            el.innerHTML = '<i class="bi bi-trash"></i>';
          }
        })
        .catch(err => {
          console.error('Error deleting task area:', err);
          showNotification('❌ Network error while deleting task area', 'danger');
          
          // Restore button state
          el.disabled = false;
          el.innerHTML = '<i class="bi bi-trash"></i>';
        });
      });

      // Coordinator auto-assign toggles: when checked, disable manual input
      const autoCoord2025 = document.getElementById('auto_assign_coord_2025');
      const autoCoord2026 = document.getElementById('auto_assign_coord_2026');
      function toggleCoordInput(checkbox, inputId) {
        if (!checkbox) return;
        const input = document.getElementById(inputId);
        if (!input) return;
        // initialize input state based on checkbox current value
        if (checkbox.checked) {
          input.setAttribute('disabled', 'disabled');
          input.classList.add('bg-light');
        } else {
          input.removeAttribute('disabled');
          input.classList.remove('bg-light');
        }
        checkbox.addEventListener('change', function() {
          if (checkbox.checked) {
            input.setAttribute('disabled', 'disabled');
            input.classList.add('bg-light');
          } else {
            input.removeAttribute('disabled');
            input.classList.remove('bg-light');
          }
        });
      }
      toggleCoordInput(autoCoord2025, 'coordinator_2025');
      toggleCoordInput(autoCoord2026, 'coordinator_2026');
    });
  </script>

  <style>
    /* Task Checklist Modal Styles - Enhanced Tabular Format */
    .table {
      border-collapse: collapse !important;
      margin: 0 !important;
      background-color: #ffffff !important;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1) !important;
    }

    /* Enhanced table headers */
    .table thead th {
      background-color: transparent !important;
      border: 1px solid #dee2e6 !important;
      font-weight: 600 !important;
      text-align: center !important;
      padding: 12px 8px !important;
      color: #333 !important;
      font-size: 13px !important;
    }

    /* Task Checklist Modal - Remove ONLY header backgrounds, keep category colors */
    #taskChecklistModal .table thead th {
      background-color: transparent !important;
      background: none !important;
      border: 1px solid #dee2e6 !important;
      color: #333 !important;
    }

    /* Override Bootstrap table-bordered default styling for task checklist headers only */
    #taskChecklistModal .table-bordered thead th {
      background-color: transparent !important;
      background: none !important;
      border: 1px solid #dee2e6 !important;
    }

    /* Remove any Bootstrap table header backgrounds but keep category cell colors */
    #taskChecklistModal .table > thead > tr > th {
      background-color: transparent !important;
      background: none !important;
      border: 1px solid #dee2e6 !important;
    }

    /* Keep category cell colors - DO NOT override these */
    #taskChecklistModal .table tbody td[style*="background-color: #90EE90"],
    #taskChecklistModal .table tbody td[style*="background: linear-gradient"] {
      /* Keep original background colors for category cells */
    }

    /* Vertical text rotation for category cells */
    .vertical-text {
      writing-mode: vertical-lr;
      text-orientation: mixed;
      transform: rotate(180deg);
      white-space: nowrap;
    }

    /* Category headers with better styling */
    .table thead th.category-header {
      background-color: transparent !important;
      font-weight: 600 !important;
      font-size: 14px !important;
      color: #333 !important;
    }

    /* Week headers */
    .table thead th.week-header {
      background-color: transparent !important;
      font-weight: 600 !important;
      font-size: 13px !important;
      color: #333 !important;
    }

    /* Day headers */
    .table thead th.day-header {
      background-color: transparent !important;
      font-weight: 600 !important;
      font-size: 11px !important;
      color: #333 !important;
    }

    .table td, .table th {
      border: 1px solid #000 !important;
      padding: 2px !important;
      vertical-align: middle !important;
      line-height: 1.2 !important;
    }

    .status-buttons {
      display: flex !important;
      gap: 2px !important;
      justify-content: center !important;
      align-items: center !important;
    }

    .status-btn {
      width: 30px !important;
      height: 30px !important;
      border: 2px solid #000 !important;
      background: white !important;
      font-size: 14px !important;
      font-weight: bold !important;
      cursor: pointer !important;
      border-radius: 4px !important;
      padding: 0 !important;
      margin: 2px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      line-height: 1 !important;
      transition: all 0.2s ease !important;
    }

    .check-btn {
      background: white !important;
      color: #28a745 !important;
      border: 2px solid #28a745 !important;
      width: 30px !important;
      height: 30px !important;
      font-size: 14px !important;
      font-weight: bold !important;
    }

    .check-btn:hover {
      background: #e8f5e8 !important;
    }

    .check-btn.active {
      background: #28a745 !important;
      color: white !important;
      border: 2px solid #28a745 !important;
    }

    .wrong-btn {
      background: white !important;
      color: #007bff !important;
      border: 2px solid #007bff !important;
      width: 30px !important;
      height: 30px !important;
      font-size: 14px !important;
      font-weight: bold !important;
    }

    .wrong-btn:hover {
      background: #e3f2fd !important;
    }

    .wrong-btn.active {
      background: #007bff !important;
      color: white !important;
      border: 2px solid #007bff !important;
    }

    .remarks-input {
      border: none !important;
      resize: none !important;
      background: #ffebee !important;
      padding: 4px !important;
      margin: 0 !important;
      outline: none !important;
      width: 100% !important;
      height: 30px !important;
      font-size: 11px !important;
      font-weight: 500 !important;
      line-height: 1.2 !important;
      color: #333 !important;
    }

    .remarks-input:focus {
      box-shadow: none !important;
      border: none !important;
    }

    /* Make Task Checklist Modal Content Larger */
    #taskChecklistModal .modal-content {
      width: 98vw !important;
      max-width: 2000px !important;
      margin: 1rem 1rem 1rem 0.5rem !important;
      height: 95vh !important;
      overflow-x: hidden !important;
      overflow-y: auto !important;
    }

    /* Make Task Page Content Area Wider */
    #taskPageContent {
      width: 100% !important;
      max-width: 100% !important;
      margin: 0 !important;
      padding: 15px 20px !important;
      overflow-x: hidden !important;
    }

    /* Make tables inside task content wider */
    #taskPageContent table {
      width: 100% !important;
      min-width: 100% !important;
      max-width: 100% !important;
      font-size: 12px !important;
      table-layout: fixed !important;
    }

    #taskPageContent .table-bordered {
      border: 1px solid #dee2e6 !important;
    }

    #taskChecklistModal .modal-dialog {
      max-width: 99vw !important;
      width: 99vw !important;
      margin: 0.5rem auto !important;
      height: auto !important;
    }

    /* Improved Modal Table Spacing */
    .modal-body table {
      width: 100% !important;
      border-collapse: collapse !important;
      border-spacing: 0 !important;
      margin: 10px 0 !important;
      max-width: 1700px !important;
      font-size: 12px !important;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
      border: 1px solid #dee2e6 !important;
    }

    .modal-body table th,
    .modal-body table td {
      padding: 8px 6px !important;
      border: 2px solid #000 !important;
      text-align: center !important;
      vertical-align: middle !important;
      font-size: 12px !important;
      line-height: 1.4 !important;
      font-weight: 500 !important;
    }

    .modal-body table th {
      background-color: #2c3e50 !important;
      font-weight: bold !important;
      color: white !important;
      border: 2px solid #000 !important;
      padding: 12px 8px !important;
      text-align: center !important;
      font-size: 13px !important;
      text-transform: uppercase !important;
      letter-spacing: 0.5px !important;
    }

    .modal-body table tbody tr:nth-child(even) {
      background-color: #f8f9fa !important;
    }

    .modal-body table tbody tr:nth-child(odd) {
      background-color: #ffffff !important;
    }

    .modal-body table tbody tr:hover {
      background-color: #e3f2fd !important;
    }

    /* Center the fullscreen modal content */
    .modal-fullscreen .modal-content {
      display: flex !important;
      flex-direction: column !important;
      justify-content: center !important;
      align-items: center !important;
      min-height: 100vh !important;
      margin: 0 auto !important;
      max-width: 95% !important;
      padding: 20px !important;
    }

    .modal-fullscreen .modal-body {
      display: flex !important;
      justify-content: center !important;
      align-items: center !important;
      width: 100% !important;
      overflow: auto !important;
    }

    .modal-fullscreen .modal-header {
      width: 100% !important;
      text-align: center !important;
      justify-content: center !important;
      position: relative !important;
    }

    .modal-fullscreen .modal-header .btn-close {
      position: absolute !important;
      right: 20px !important;
      top: 50% !important;
      transform: translateY(-50%) !important;
    }

    /* Better Modal Centering */
    .modal-dialog {
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      min-height: calc(100vh - 3.5rem) !important;
    }

    /* Specific adjustments for member modals */
    #studentAssignModal .modal-content,
    #addMembersModal .modal-content,
    #deleteMembersModal .modal-content,
    #editMembersModal .modal-content {
      transform: none !important;
      left: auto !important;
      margin: 0 auto !important;
    }

    /* Task description styling - Enhanced Tabular Format */
    .task-cell:not(.category-cell) {
      background-color: #ffffff !important;
      padding: 12px 16px !important;
      border: 1px solid #333 !important;
      font-size: 18px !important;
      font-weight: normal !important;
      line-height: 1.5 !important;
      vertical-align: middle !important;
      width: 350px !important;
      white-space: normal !important;
      text-align: left !important;
      color: #000000 !important;
      border-right: 2px solid #666 !important;
    }

    /* Keep category cells unchanged */
    .task-cell.category-cell {
      /* Preserve original styling for task area column */
    }

    /* Task Checklist Modal Title - Make it bigger and more readable */
    #taskChecklistModalLabel {
      font-size: 32px !important;
      font-weight: normal !important;
      color: #000000 !important;
      text-align: center !important;
      margin: 0 !important;
      padding: 15px 0 !important;
    }

    /* Task Checklist Modal Header */
    #taskChecklistModal .modal-header {
      padding: 15px 20px !important;
      border-bottom: 2px solid #dee2e6 !important;
      background-color: #f8f9fa !important;
    }

    /* Improve text selection/highlighting readability for task descriptions only */
    .task-cell:not(.category-cell)::selection,
    .task-cell:not(.category-cell) *::selection {
      background-color: #000000 !important;
      color: #ffffff !important;
    }

    /* For Firefox */
    .task-cell:not(.category-cell)::-moz-selection,
    .task-cell:not(.category-cell) *::-moz-selection {
      background-color: #000000 !important;
      color: #ffffff !important;
    }

    /* Bold styling for important task items */
    .task-cell.important-task {
      font-weight: bold !important;
      background-color: #f8f9fa !important;
      color: #2c3e50 !important;
    }

    /* Room title styling - Enhanced Orange Highlight */
    .task-cell.room-title,
    .task-cell[style*="background-color: #ff8c00"] {
      background-color: #ff8c00 !important;
      font-weight: bold !important;
      color: #000000 !important;
      text-align: center !important;
      font-size: 18px !important;
      border: 1px solid #333 !important;
      padding: 8px !important;
    }

    /* Day cells - Enhanced Tabular Format */
    .day-cell {
      text-align: center !important;
      padding: 8px 6px !important;
      border: 1px solid #333 !important;
      width: 75px !important;
      height: 45px !important;
      vertical-align: middle !important;
      background-color: #ffffff !important;
      font-weight: normal !important;
    }

    /* Checkbox cells styling */
    .checkbox-cell {
      background-color: #fafafa !important;
      border: 1px solid #333 !important;
      padding: 6px !important;
      text-align: center !important;
      vertical-align: middle !important;
    }

    /* Single checkbox styling */
    .single-checkbox {
      border: 2px solid #333 !important;
      background-color: #ffffff !important;
      border-radius: 4px !important;
      transition: all 0.2s ease !important;
    }

    .single-checkbox:hover {
      border-color: #007bff !important;
      box-shadow: 0 0 0 2px rgba(0,123,255,0.25) !important;
    }

    /* Remarks cells - Enhanced Tabular Format */
    .remarks-cell {
      padding: 12px !important;
      border: 1px solid #333 !important;
      width: 400px !important;
      vertical-align: middle !important;
      background-color: #fff5f5 !important;
    }

    /* Category cells - Enhanced styling */
    .category-cell {
      border: 2px solid #333 !important;
      font-weight: bold !important;
      text-align: center !important;
      vertical-align: middle !important;
      padding: 12px 8px !important;
      line-height: 1.3 !important;
    }

    /* Remarks input styling */
    .remarks-input {
      width: 100% !important;
      height: 60px !important;
      border: 1px solid #ccc !important;
      border-radius: 3px !important;
      padding: 12px !important;
      font-size: 16px !important;
      background-color: #ffffff !important;
      resize: vertical !important;
      font-family: Arial, sans-serif !important;
    }

    .remarks-input:focus {
      border-color: #007bff !important;
      box-shadow: 0 0 0 2px rgba(0,123,255,0.25) !important;
      outline: none !important;
    }

    /* Table row styling */
    .table tbody tr {
      border-bottom: 1px solid #dee2e6 !important;
    }

    .table tbody tr:nth-child(even) {
      background-color: #f8f9fa !important;
    }

    .table tbody tr:hover {
      background-color: #e3f2fd !important;
    }

    /* Improve readability of task description text only - NOT task areas */
    #taskChecklistModal .table tbody td:not(.category-cell) {
      font-size: 16px !important;
      line-height: 1.4 !important;
      color: #000000 !important;
      font-weight: normal !important;
    }

    /* Override any blue text highlighting with better contrast - ONLY for task descriptions */
    #taskChecklistModal .table tbody td:not(.category-cell)[style*="color: blue"],
    #taskChecklistModal .table tbody td:not(.category-cell)[style*="color: #0000ff"],
    #taskChecklistModal .table tbody td:not(.category-cell)[style*="color: #007bff"] {
      color: #000000 !important;
      background-color: #f8f9fa !important;
      font-weight: normal !important;
    }

    /* Keep task area column colors unchanged */
    #taskChecklistModal .table tbody td.category-cell {
      /* Preserve original colors and styling for task areas */
    }

    /* ===== KITCHEN ASSIGNMENT DRAG & DROP STYLES ===== */
    
    /* Student Cards - Enhanced Design like Reference Image */
    .student-card {
      background: #fff;
      border: 2px solid #e8f4fd;
      border-radius: 12px;
      padding: 12px 16px;
      margin-right: 12px;
      margin-bottom: 8px;
      cursor: grab;
      transition: all 0.3s ease;
      box-shadow: 0 3px 10px rgba(0,0,0,0.08);
      min-width: 200px;
      max-width: 250px;
      flex-shrink: 0;
      display: flex;
      align-items: center;
    }
    
    /* Student List Container - Enhanced Horizontal Scrolling */
    .student-list {
      display: flex !important;
      flex-direction: row !important;
      overflow-x: auto !important;
      overflow-y: hidden !important;
      padding: 8px 0 !important;
      gap: 0 !important;
      scroll-behavior: smooth !important;
      -webkit-overflow-scrolling: touch !important;
    }
    
    /* Custom Scrollbar for Student List */
    .student-list::-webkit-scrollbar {
      height: 8px;
    }
    
    .student-list::-webkit-scrollbar-track {
      background: #f1f1f1;
      border-radius: 4px;
    }
    
    .student-list::-webkit-scrollbar-thumb {
      background: #4285f4;
      border-radius: 4px;
    }
    
    .student-list::-webkit-scrollbar-thumb:hover {
      background: #3367d6;
    }
    
    /* Enhanced Card Body for Better Scrolling */
    .card-body {
      position: relative;
    }
    
    /* Scroll Indicators */
    .card-body::before {
      content: '';
      position: absolute;
      top: 0;
      right: 0;
      width: 20px;
      height: 100%;
      background: linear-gradient(to left, rgba(248, 251, 255, 1), rgba(248, 251, 255, 0));
      pointer-events: none;
      z-index: 2;
    }
    
    .card-body::after {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 20px;
      height: 100%;
      background: linear-gradient(to right, rgba(248, 251, 255, 1), rgba(248, 251, 255, 0));
      pointer-events: none;
      z-index: 2;
    }
    
    /* Ensure student cards are properly spaced for scrolling */
    .student-card:last-child {
      margin-right: 20px;
    }
    
    .student-card:first-child {
      margin-left: 20px;
    }

    .student-card:hover {
      border-color: #4285f4;
      box-shadow: 0 6px 20px rgba(66, 133, 244, 0.15);
      transform: translateY(-2px);
      background: #f8fbff;
    }

    .student-card:active {
      cursor: grabbing;
    }

    .student-card.dragging {
      opacity: 0.5;
      transform: rotate(5deg);
    }
    
    /* All Students Special Card - Green like reference */
    .student-card.all-students-option {
      background: linear-gradient(135deg, #4CAF50, #45a049) !important;
      border: 2px solid #4CAF50 !important;
      color: white !important;
      font-weight: 600;
    }
    
    .student-card.all-students-option:hover {
      background: linear-gradient(135deg, #45a049, #3d8b40) !important;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(76, 175, 80, 0.3);
    }

    .student-avatar {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      background: linear-gradient(135deg, #4285f4 0%, #34a853 100%);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      font-size: 14px;
      margin-right: 12px;
      flex-shrink: 0;
      text-transform: uppercase;
    }
    
    .student-info {
      flex: 1;
      min-width: 0;
    }

    .student-name {
      font-weight: 600;
      font-size: 14px;
      color: #2c3e50;
      margin-bottom: 2px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    
    .student-id {
      font-size: 12px;
      color: #7f8c8d;
      font-weight: 500;
    }
    /* Modern Time Headers */
    .time-header {
      background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
      color: white;
      padding: 12px 8px;
      text-align: center;
      font-weight: 700;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
      font-size: 0.85rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      transition: all 0.3s ease;
      border: 2px solid rgba(255,255,255,0.2);
    }

    .time-header:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(79, 70, 229, 0.4);
    }

    .time-header.breakfast {
      background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
      color: #8b4513;
      font-size: 0.9rem;
    }

    .time-header.lunch {
      background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
      color: #2c5282;
    }

    .time-header.dinner {
      background: linear-gradient(135deg, #d299c2 0%, #fef9d7 100%);
      color: #553c9a;
    }

    /* Task Labels */
    .task-label {
      background: #f8f9fa;
      border: 2px solid #dee2e6;
      border-radius: 6px;
      padding: 15px 8px;
      text-align: center;
      font-weight: 600;
      height: 100%;
      width: 100%;
      max-width: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      box-sizing: border-box;
      overflow: hidden;
      word-wrap: break-word;
    }

    .task-label.cook {
      background: linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%);
      border-color: #ff6b6b;
      color: #c92a2a;
    }

    .task-label.prep {
      background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
      border-color: #20c997;
      color: #0f5132;
    }

    .task-label.wash {
      background: linear-gradient(135deg, #d0e7ff 0%, #b3d9ff 100%);
      border-color: #0d6efd;
      color: #0a58ca;
    }

    /* Modern Drop Zones */
    .drop-zone {
      min-height: 180px;
      border: 2px solid #e3f2fd;
      border-radius: 12px;
      padding: 20px;
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
      border-style: solid;
    }

    .drop-zone:hover {
      border-color: #2196f3;
      background: linear-gradient(135deg, #f3f8ff 0%, #e8f4fd 100%);
      box-shadow: 0 4px 16px rgba(33, 150, 243, 0.15);
      transform: translateY(-2px);
    }

    .drop-zone.drag-over {
      border-color: #4caf50;
      background: linear-gradient(135deg, #f1f8e9 0%, #e8f5e8 100%);
      box-shadow: 0 6px 20px rgba(76, 175, 80, 0.2);
    }

    .drop-zone.drag-over {
      border-color: #28a745;
      background: #d4edda;
      border-style: solid;
      box-shadow: inset 0 0 10px rgba(40, 167, 69, 0.3);
    }

    .drop-zone.full {
      border-color: #007bff;
      background: #e3f2fd;
    }

    .drop-zone-header {
      text-align: center;
      color: #6c757d;
      font-size: 11px;
      margin-bottom: 5px;
      border-bottom: 1px solid #dee2e6;
      padding-bottom: 3px;
    }

    .assigned-students {
      min-height: 55px !important;
      max-height: 55px !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
      flex: 1 !important;
    }

    .assigned-student {
      background: #e3f2fd;
      border: 1px solid #2196f3;
      border-radius: 6px;
      padding: 6px 8px !important;
      margin-bottom: 2px !important;
      font-size: 10px !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      animation: slideIn 0.3s ease;
      white-space: nowrap !important;
      overflow: hidden !important;
      max-width: 100% !important;
      height: 24px !important;
      min-height: 24px !important;
      max-height: 24px !important;
    }

    .assigned-student .student-name {
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      white-space: nowrap !important;
      flex: 1 !important;
      margin-right: 5px !important;
      max-width: calc(100% - 25px) !important;
      font-size: 10px !important;
      line-height: 1.2 !important;
    }

    .assigned-student .remove-btn {
      background: #dc3545;
      color: white;
      border: none;
      border-radius: 50%;
      width: 16px !important;
      height: 16px !important;
      font-size: 9px !important;
      cursor: pointer;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      flex-shrink: 0 !important;
    }

    .assigned-student .remove-btn:hover {
      background: #c82333;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    /* Assignment Grid */
    .assignment-grid {
      background: white;
      border-radius: 6px;
    }

    /* FORCE consistent row heights and alignment for ALL assignment modals */
    .assignment-grid .row {
      min-height: 65px !important;
      align-items: stretch !important;
      display: flex !important;
      flex-wrap: nowrap !important;
      overflow: visible !important;
    }

    /* COMPLETELY LOCK DOWN the grid structure */
    .assignment-grid {
      overflow-x: hidden !important;
      width: 100% !important;
    }

    .assignment-grid .row > * {
      flex-shrink: 0 !important;
      overflow: hidden !important;
    }

    /* NUCLEAR OPTION: Override ALL Bootstrap row behavior in assignment modals */
    #kitchenAssignmentModal .assignment-grid .row,
    #officeAssignmentModal .assignment-grid .row,
    #conferenceAssignmentModal .assignment-grid .row,
    #groundAssignmentModal .assignment-grid .row,
    #wasteAssignmentModal .assignment-grid .row,
    #dishwashingAssignmentModal .assignment-grid .row,
    #diningAssignmentModal .assignment-grid .row {
      display: flex !important;
      flex-wrap: nowrap !important;
      margin: 0 !important;
      min-height: 65px !important;
      align-items: stretch !important;
      overflow: visible !important;
    }

    /* Force all columns in assignment modals to stay in line */
    #kitchenAssignmentModal .assignment-grid .row > [class*="col"],
    #officeAssignmentModal .assignment-grid .row > [class*="col"],
    #conferenceAssignmentModal .assignment-grid .row > [class*="col"],
    #groundAssignmentModal .assignment-grid .row > [class*="col"],
    #wasteAssignmentModal .assignment-grid .row > [class*="col"],
    #dishwashingAssignmentModal .assignment-grid .row > [class*="col"],
    #diningAssignmentModal .assignment-grid .row > [class*="col"] {
      flex: 1 !important;
      min-width: 0 !important;
      max-width: none !important;
      flex-shrink: 0 !important;
      overflow: visible !important;
    }

    /* Special handling for col-3 (task label column) */
    #kitchenAssignmentModal .assignment-grid .row > .col-3,
    #officeAssignmentModal .assignment-grid .row > .col-3,
    #conferenceAssignmentModal .assignment-grid .row > .col-3,
    #groundAssignmentModal .assignment-grid .row > .col-3,
    #wasteAssignmentModal .assignment-grid .row > .col-3,
    #dishwashingAssignmentModal .assignment-grid .row > .col-3,
    #diningAssignmentModal .assignment-grid .row > .col-3 {
      flex: 0 0 20% !important;
      max-width: 20% !important;
      min-width: 150px !important;
      overflow: hidden !important;
    }

    .assignment-grid .col {
      display: flex !important;
      flex-direction: column !important;
      flex: 1 !important;
      min-width: 0 !important;
      overflow: hidden !important;
    }

    .assignment-grid .col-3 {
      display: flex !important;
      flex-direction: column !important;
      flex: 0 0 25% !important;
      max-width: 25% !important;
      min-width: 0 !important;
    }

    .assignment-grid .drop-zone {
      flex: 1 !important;
      display: flex !important;
      height: 100% !important;
      overflow: visible !important;
    }

    .assignment-grid .task-label {
      height: 100% !important;
      max-height: 55px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      text-align: center !important;
      overflow: hidden !important;
      word-wrap: break-word !important;
      hyphens: auto !important;
      line-height: 1.1 !important;
      font-size: 12px !important;
      padding: 3px !important;
    }

    /* Specific targeting for task labels that might be causing issues */
    .task-label.cook,
    .task-label.prep,
    .task-label.wash-dishes,
    .task-label.serve-food,
    .task-label.sweep-mop,
    .task-label.clean-glass,
    .task-label.clean-tables,
    .task-label.garbage-collection,
    .task-label.floor-mopping {
      max-height: 55px !important;
      overflow: hidden !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      flex-shrink: 0 !important;
    }

    /* Global styles for ALL assignment modals */
    #officeAssignmentModal .assigned-students,
    #conferenceAssignmentModal .assigned-students,
    #groundAssignmentModal .assigned-students,
    #wasteAssignmentModal .assigned-students,
    #dishwashingAssignmentModal .assigned-students,
    #diningAssignmentModal .assigned-students {
      min-height: 55px !important;
      max-height: 55px !important;
      overflow-y: auto !important;
      overflow-x: hidden !important;
      flex: 1 !important;
    }

    #officeAssignmentModal .assigned-student,
    #conferenceAssignmentModal .assigned-student,
    #groundAssignmentModal .assigned-student,
    #wasteAssignmentModal .assigned-student,
    #dishwashingAssignmentModal .assigned-student,
    #diningAssignmentModal .assigned-student {
      background: #e3f2fd;
      border: 1px solid #2196f3;
      border-radius: 6px;
      padding: 6px 8px !important;
      margin-bottom: 2px !important;
      font-size: 10px !important;
      display: flex !important;
      justify-content: space-between !important;
      align-items: center !important;
      animation: slideIn 0.3s ease;
      white-space: nowrap !important;
      overflow: hidden !important;
      max-width: 100% !important;
      height: 24px !important;
      min-height: 24px !important;
      max-height: 24px !important;
    }

    #officeAssignmentModal .assigned-student .student-name,
    #conferenceAssignmentModal .assigned-student .student-name,
    #groundAssignmentModal .assigned-student .student-name,
    #wasteAssignmentModal .assigned-student .student-name,
    #dishwashingAssignmentModal .assigned-student .student-name,
    #diningAssignmentModal .assigned-student .student-name {
      overflow: hidden !important;
      text-overflow: ellipsis !important;
      white-space: nowrap !important;
      flex: 1 !important;
      margin-right: 5px !important;
      max-width: calc(100% - 25px) !important;
      font-size: 10px !important;
      line-height: 1.2 !important;
    }

    #officeAssignmentModal .assignment-grid .row,
    #conferenceAssignmentModal .assignment-grid .row,
    #groundAssignmentModal .assignment-grid .row,
    #wasteAssignmentModal .assignment-grid .row,
    #dishwashingAssignmentModal .assignment-grid .row,
    #diningAssignmentModal .assignment-grid .row {
      min-height: 65px !important;
      max-height: 65px !important;
      align-items: stretch !important;
      display: flex !important;
      flex-wrap: nowrap !important;
    }

    #officeAssignmentModal .assignment-grid .col,
    #conferenceAssignmentModal .assignment-grid .col,
    #groundAssignmentModal .assignment-grid .col,
    #wasteAssignmentModal .assignment-grid .col,
    #dishwashingAssignmentModal .assignment-grid .col,
    #diningAssignmentModal .assignment-grid .col {
      display: flex;
      flex-direction: column;
    }

    #officeAssignmentModal .assignment-grid .drop-zone,
    #conferenceAssignmentModal .assignment-grid .drop-zone,
    #groundAssignmentModal .assignment-grid .drop-zone,
    #wasteAssignmentModal .assignment-grid .drop-zone,
    #dishwashingAssignmentModal .assignment-grid .drop-zone,
    #diningAssignmentModal .assignment-grid .drop-zone {
      flex: 1;
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    /* Enhanced Kitchen Assignment Card Styles */
    .task-preview-card {
      background: rgba(255,255,255,0.1);
      border-radius: 15px;
      padding: 15px;
      text-align: center;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
      border: 1px solid rgba(255,255,255,0.2);
    }

    .task-preview-card:hover {
      background: rgba(255,255,255,0.2);
      transform: translateY(-5px);
      box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    .kitchen-icons-container {
      display: flex;
      justify-content: center;
      align-items: center;
    }

    @keyframes bounce {
      0%, 20%, 50%, 80%, 100% {
        transform: translateY(0);
      }
      40% {
        transform: translateY(-10px);
      }
      60% {
        transform: translateY(-5px);
      }
    }

    .kitchen-icon {
      transition: all 0.3s ease;
    }

    .kitchen-icon:hover {
      transform: scale(1.2) !important;
      animation-play-state: paused;
    }

    /* Enhanced button hover effects */
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.3) !important;
    }

    /* Glass morphism effect for modal */
    .modal-content {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* Enhanced gradient backgrounds */
    .bg-gradient-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    }

    .bg-gradient-success {
      background: linear-gradient(135deg, #56ab2f 0%, #a8e6cf 100%) !important;
    }

    .bg-gradient-warning {
      background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
      .student-card {
        font-size: 11px;
      }
      
      .drop-zone {
        min-height: 55px;
      }
      
      .time-header {
        font-size: 11px;
        padding: 8px;
      }

      .kitchen-icon {
        font-size: 2rem !important;
        margin: 0 5px !important;
      }

      .task-preview-card {
        margin-bottom: 15px;
      }

      .btn-lg {
        font-size: 1rem;
        padding: 12px 30px !important;
      }
    }
    
    /* Kitchen Assignment Modal Centering */
    #kitchenAssignmentModal .modal-dialog {
      max-width: 900px;
      margin: 1.75rem auto;
    }
    
    #kitchenAssignmentModal .modal-content {
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    #kitchenAssignmentModal .assignment-grid {
      max-width: 100%;
      overflow-x: auto;
    }
    /* Food Preparation task styling for Kitchen modal */
    #kitchenAssignmentModal .task-label.prep {
      background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%) !important;
      color: white !important;
      font-size: 10px !important;
      box-shadow: 0 6px 20px rgba(78, 205, 196, 0.3) !important;
    }

    /* Dishwashing Assignment Modal Sizing */
    #dishwashingAssignmentModal .modal-dialog {
      max-width: 1200px !important;
      margin: 1.75rem auto;
      max-height: calc(100vh - 3.5rem) !important;
    }

    #dishwashingAssignmentModal .modal-content {
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
      height: auto !important;
      max-height: calc(100vh - 3.5rem) !important;
      display: flex !important;
      flex-direction: column !important;
    }
    
    #dishwashingAssignmentModal .modal-body {
      flex: 1 !important;
      overflow-y: auto !important;
      max-height: calc(100vh - 200px) !important;
    }
    
    #dishwashingAssignmentModal .modal-footer {
      flex-shrink: 0 !important;
      border-top: 1px solid #dee2e6 !important;
      background: #f8f9fa !important;
    }
    
    /* Fix All Other Modals - Dining, Office, Conference, Ground, Waste */
    #diningAssignmentModal .modal-dialog,
    #officeAssignmentModal .modal-dialog,
    #conferenceAssignmentModal .modal-dialog,
    #groundAssignmentModal .modal-dialog,
    #wasteAssignmentModal .modal-dialog {
      max-width: 1200px !important;
      margin: 1.75rem auto;
      max-height: calc(100vh - 3.5rem) !important;
    }

    #diningAssignmentModal .modal-content,
    #officeAssignmentModal .modal-content,
    #conferenceAssignmentModal .modal-content,
    #groundAssignmentModal .modal-content,
    #wasteAssignmentModal .modal-content {
      height: auto !important;
      max-height: calc(100vh - 3.5rem) !important;
      display: flex !important;
      flex-direction: column !important;
    }
    
    #diningAssignmentModal .modal-body,
    #officeAssignmentModal .modal-body,
    #conferenceAssignmentModal .modal-body,
    #groundAssignmentModal .modal-body,
    #wasteAssignmentModal .modal-body {
      flex: 1 !important;
      overflow-y: auto !important;
      max-height: calc(100vh - 200px) !important;
    }
    
    #diningAssignmentModal .modal-footer,
    #officeAssignmentModal .modal-footer,
    #conferenceAssignmentModal .modal-footer,
    #groundAssignmentModal .modal-footer,
    #wasteAssignmentModal .modal-footer {
      flex-shrink: 0 !important;
      border-top: 1px solid #dee2e6 !important;
      background: #f8f9fa !important;
    }
    
    /* Force Assignment Grid Visibility for All Modals */
    #diningAssignmentModal .assignment-grid,
    #officeAssignmentModal .assignment-grid,
    #conferenceAssignmentModal .assignment-grid,
    #groundAssignmentModal .assignment-grid,
    #wasteAssignmentModal .assignment-grid,
    #dishwashingAssignmentModal .assignment-grid {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
      height: auto !important;
      overflow: visible !important;
    }
    
    /* Force Modal Body Visibility */
    #diningAssignmentModal .modal-body,
    #officeAssignmentModal .modal-body,
    #conferenceAssignmentModal .modal-body,
    #groundAssignmentModal .modal-body,
    #wasteAssignmentModal .modal-body,
    #dishwashingAssignmentModal .modal-body {
      display: block !important;
      visibility: visible !important;
      opacity: 1 !important;
    }
    
    /* Force All Modal Content to be Visible */
    #diningAssignmentModal *,
    #officeAssignmentModal *,
    #conferenceAssignmentModal *,
    #groundAssignmentModal *,
    #wasteAssignmentModal *,
    #dishwashingAssignmentModal * {
      visibility: visible !important;
    }
    
    /* Hide Student Count Numbers for Students Only */
    @if(auth()->check() && in_array(auth()->user()->user_role, ['student']))
    .drop-zone-header {
      display: none !important;
    }
    @endif
    
    #dishwashingAssignmentModal .assignment-grid {
      max-width: 100%;
      overflow-x: auto;
    }

    /* Increase dishwashing modal row heights */
    #dishwashingAssignmentModal .assignment-grid .row {
      min-height: 200px !important;
    }

    #dishwashingAssignmentModal .drop-zone {
      min-height: 160px !important;
      padding: 20px !important;
    }

    /* Reduce gap between day headers and task rows */
    #dishwashingAssignmentModal .assignment-grid .row.mb-3 {
      margin-bottom: 0.2rem !important;
    }

    #dishwashingAssignmentModal .assignment-grid .row.mb-5 {
      margin-bottom: 0.5rem !important;
    }

    /* Make day headers stick closer to task boxes */
    #dishwashingAssignmentModal .assignment-grid {
      gap: 0.3rem !important;
    }

    /* Modern Dishwashing Modal Styling */
    #dishwashingAssignmentModal .modal-content {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
      border: none !important;
      border-radius: 20px !important;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
    }

    #dishwashingAssignmentModal .modal-header {
      background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
      border-radius: 20px 20px 0 0 !important;
      border: none !important;
      padding: 20px 30px !important;
    }

    #dishwashingAssignmentModal .card {
      background: rgba(255,255,255,0.9) !important;
      border: none !important;
      border-radius: 16px !important;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1) !important;
      backdrop-filter: blur(10px) !important;
    }

    #dishwashingAssignmentModal .card-header {
      background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
      border-radius: 16px 16px 0 0 !important;
      border: none !important;
    }

    #dishwashingAssignmentModal .time-header {
      background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%) !important;
      box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3) !important;
      border: 2px solid rgba(255,255,255,0.3) !important;
    }

    /* Modern Task Labels for Dishwashing Modal */
    #dishwashingAssignmentModal .task-label {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%) !important;
      border: none !important;
      border-radius: 16px !important;
      box-shadow: 0 6px 20px rgba(255, 107, 107, 0.3) !important;
      color: white !important;
      font-weight: 700 !important;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
      border: 2px solid rgba(255,255,255,0.2) !important;
    }

    #dishwashingAssignmentModal .task-label:hover {
      transform: translateY(-3px) scale(1.02) !important;
      box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4) !important;
    }

    #dishwashingAssignmentModal .task-label.prep {
      background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%) !important;
      box-shadow: 0 6px 20px rgba(78, 205, 196, 0.3) !important;
    }

    #dishwashingAssignmentModal .task-label.wash {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3) !important;
    }

    /* Animated Drop Zone Effects */
    #dishwashingAssignmentModal .drop-zone {
      background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%) !important;
      border: 2px solid #e2e8f0 !important;
      position: relative !important;
      overflow: hidden !important;
    }

    #dishwashingAssignmentModal .drop-zone::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
      transition: left 0.5s;
    }

    #dishwashingAssignmentModal .drop-zone:hover::before {
      left: 100%;
    }

    /* Apply Modern Styling to All Other Modals */
    #diningAssignmentModal .modal-content,
    #officeAssignmentModal .modal-content,
    #conferenceAssignmentModal .modal-content,
    #groundAssignmentModal .modal-content,
    #wasteAssignmentModal .modal-content {
      background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%) !important;
      border: none !important;
      border-radius: 20px !important;
      box-shadow: 0 20px 60px rgba(0,0,0,0.15) !important;
    }

    #diningAssignmentModal .modal-header,
    #officeAssignmentModal .modal-header,
    #conferenceAssignmentModal .modal-header,
    #groundAssignmentModal .modal-header,
    #wasteAssignmentModal .modal-header {
      background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%) !important;
      border-radius: 20px 20px 0 0 !important;
      border: none !important;
      padding: 20px 30px !important;
    }

    #diningAssignmentModal .card,
    #officeAssignmentModal .card,
    #conferenceAssignmentModal .card,
    #groundAssignmentModal .card,
    #wasteAssignmentModal .card {
      background: rgba(255,255,255,0.9) !important;
      border: none !important;
      border-radius: 16px !important;
      box-shadow: 0 8px 32px rgba(0,0,0,0.1) !important;
      backdrop-filter: blur(10px) !important;
    }

    #diningAssignmentModal .card-header,
    #officeAssignmentModal .card-header,
    #conferenceAssignmentModal .card-header,
    #groundAssignmentModal .card-header,
    #wasteAssignmentModal .card-header {
      background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%) !important;
      border-radius: 16px 16px 0 0 !important;
      border: none !important;
    }

    #diningAssignmentModal .time-header,
    #officeAssignmentModal .time-header,
    #conferenceAssignmentModal .time-header,
    #groundAssignmentModal .time-header,
    #wasteAssignmentModal .time-header {
      background: linear-gradient(135deg, #8b5cf6 0%, #a855f7 100%) !important;
      box-shadow: 0 4px 16px rgba(139, 92, 246, 0.3) !important;
      border: 2px solid rgba(255,255,255,0.3) !important;
    }

    /* Modern Task Labels for All Other Modals */
    #diningAssignmentModal .task-label,
    #officeAssignmentModal .task-label,
    #conferenceAssignmentModal .task-label,
    #groundAssignmentModal .task-label,
    #wasteAssignmentModal .task-label {
      background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%) !important;
      border: none !important;
      border-radius: 16px !important;
      box-shadow: 0 6px 20px rgba(255, 107, 107, 0.3) !important;
      color: white !important;
      font-weight: 700 !important;
      text-shadow: 0 2px 4px rgba(0,0,0,0.2) !important;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
      border: 2px solid rgba(255,255,255,0.2) !important;
    }

    #diningAssignmentModal .task-label:hover,
    #officeAssignmentModal .task-label:hover,
    #conferenceAssignmentModal .task-label:hover,
    #groundAssignmentModal .task-label:hover,
    #wasteAssignmentModal .task-label:hover {
      transform: translateY(-3px) scale(1.02) !important;
      box-shadow: 0 8px 25px rgba(255, 107, 107, 0.4) !important;
    }

    /* Different colors for different task types */
    #diningAssignmentModal .task-label.prep,
    #officeAssignmentModal .task-label.prep,
    #conferenceAssignmentModal .task-label.prep,
    #groundAssignmentModal .task-label.prep,
    #wasteAssignmentModal .task-label.prep {
      background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%) !important;
      box-shadow: 0 6px 20px rgba(78, 205, 196, 0.3) !important;
    }

    #diningAssignmentModal .task-label.wash,
    #officeAssignmentModal .task-label.wash,
    #conferenceAssignmentModal .task-label.wash,
    #groundAssignmentModal .task-label.wash,
    #wasteAssignmentModal .task-label.wash {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
      box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3) !important;
    }

    /* Animated Drop Zone Effects for All Modals */
    #diningAssignmentModal .drop-zone,
    #officeAssignmentModal .drop-zone,
    #conferenceAssignmentModal .drop-zone,
    #groundAssignmentModal .drop-zone,
    #wasteAssignmentModal .drop-zone {
      background: linear-gradient(135deg, #ffffff 0%, #f1f5f9 100%) !important;
      border: 2px solid #e2e8f0 !important;
      position: relative !important;
      overflow: hidden !important;
    }

    #diningAssignmentModal .drop-zone::before,
    #officeAssignmentModal .drop-zone::before,
    #conferenceAssignmentModal .drop-zone::before,
    #groundAssignmentModal .drop-zone::before,
    #wasteAssignmentModal .drop-zone::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.6), transparent);
      transition: left 0.5s;
    }

    #diningAssignmentModal .drop-zone:hover::before,
    #officeAssignmentModal .drop-zone:hover::before,
    #conferenceAssignmentModal .drop-zone:hover::before,
    #groundAssignmentModal .drop-zone:hover::before,
    #wasteAssignmentModal .drop-zone:hover::before {
      left: 100%;
    }

    /* Apply Same Spacing as Kitchen Modal to All Other Modals */
    #diningAssignmentModal .modal-dialog,
    #officeAssignmentModal .modal-dialog,
    #conferenceAssignmentModal .modal-dialog,
    #groundAssignmentModal .modal-dialog,
    #wasteAssignmentModal .modal-dialog {
      max-width: 1200px !important;
      margin: 1.75rem auto;
    }

    #diningAssignmentModal .modal-content,
    #officeAssignmentModal .modal-content,
    #conferenceAssignmentModal .modal-content,
    #groundAssignmentModal .modal-content,
    #wasteAssignmentModal .modal-content {
      border-radius: 12px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }
    
    #diningAssignmentModal .assignment-grid,
    #officeAssignmentModal .assignment-grid,
    #conferenceAssignmentModal .assignment-grid,
    #groundAssignmentModal .assignment-grid,
    #wasteAssignmentModal .assignment-grid {
      max-width: 100%;
      overflow-x: auto;
    }

    /* Same row heights and spacing for all modals */
    #diningAssignmentModal .assignment-grid .row,
    #officeAssignmentModal .assignment-grid .row,
    #conferenceAssignmentModal .assignment-grid .row,
    #groundAssignmentModal .assignment-grid .row,
    #wasteAssignmentModal .assignment-grid .row {
      min-height: 200px !important;
    }

    #diningAssignmentModal .drop-zone,
    #officeAssignmentModal .drop-zone,
    #conferenceAssignmentModal .drop-zone,
    #groundAssignmentModal .drop-zone,
    #wasteAssignmentModal .drop-zone {
      min-height: 160px !important;
      padding: 20px !important;
    }

    /* Same tight spacing between headers and content */
    #diningAssignmentModal .assignment-grid .row.mb-3,
    #officeAssignmentModal .assignment-grid .row.mb-3,
    #conferenceAssignmentModal .assignment-grid .row.mb-3,
    #groundAssignmentModal .assignment-grid .row.mb-3,
    #wasteAssignmentModal .assignment-grid .row.mb-3 {
      margin-bottom: 0.2rem !important;
    }

    #diningAssignmentModal .assignment-grid .row.mb-5,
    #officeAssignmentModal .assignment-grid .row.mb-5,
    #conferenceAssignmentModal .assignment-grid .row.mb-5,
    #groundAssignmentModal .assignment-grid .row.mb-5,
    #wasteAssignmentModal .assignment-grid .row.mb-5 {
      margin-bottom: 0.5rem !important;
    }

    #diningAssignmentModal .assignment-grid,
    #officeAssignmentModal .assignment-grid,
    #conferenceAssignmentModal .assignment-grid,
    #groundAssignmentModal .assignment-grid,
    #wasteAssignmentModal .assignment-grid {
      gap: 0.3rem !important;
    }
    
    @media (max-width: 768px) {
      #kitchenAssignmentModal .modal-dialog,
      #dishwashingAssignmentModal .modal-dialog,
      #diningAssignmentModal .modal-dialog,
      #officeAssignmentModal .modal-dialog,
      #conferenceAssignmentModal .modal-dialog,
      #groundAssignmentModal .modal-dialog,
      #wasteAssignmentModal .modal-dialog {
        max-width: 95%;
        margin: 1rem auto;
      }
      .assignment-grid {
        font-size: 0.8rem;
      }
      .time-header {
        font-size: 0.65rem;
        padding: 4px 3px;
      }
      .task-label {
        font-size: 9px;
        min-height: 40px;
      }
    }
  </style>

  <!-- Kitchen Assignment Modal - Student View -->
  <div class="modal fade" id="kitchenAssignmentModal" tabindex="-1" aria-labelledby="kitchenAssignmentModalLabel">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 95vw; width: 95vw; margin: 1rem auto;">
      <div class="modal-content">
        <div class="modal-header" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; border-bottom: none;">
          <h5 class="modal-title d-flex align-items-center">
            <i class="bi bi-eye me-2"></i>
            View Assignment
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        
        <div class="modal-body p-3">
          <div class="alert alert-info border-0" style="background: #e3f2fd; color: #1976d2; margin-bottom: 15px;">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Custom Assignment Creator:</strong> View your kitchen team assignments for the week.
          </div>

          <div class="row g-3">
            <!-- Full Width Task Completion Table -->
            <div class="col-12">
              <!-- Header Section -->
              <div class="d-flex justify-content-between align-items-center mb-2" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); color: white; padding: 10px 15px; border-radius: 6px;">
                <h6 class="mb-0"><i class="bi bi-list-check me-2"></i>TASKS TO COMPLETE</h6>
                <div class="d-flex align-items-center">
                  <span class="me-2" style="font-size: 0.8rem;">DATE:</span>
                  <input type="date" class="form-control form-control-sm" style="background: white; border: 1px solid #ddd; font-size: 0.8rem; width: 130px;" value="2024-12-23">
                </div>
              </div>
              
              <!-- Compact Table -->
              <div style="width: 100%; overflow-x: auto;">
                <table class="table table-bordered table-sm mb-0" style="border-collapse: collapse; width: 100%; font-size: 0.75rem;">
                  <thead>
                    <tr style="background: #343a40; color: white; height: 35px;">
                      <th style="width: 25%; padding: 6px; font-weight: 600; border: 1px solid #495057; text-align: center; font-size: 0.75rem;">TASKS TO COMPLETE</th>
                      <th style="width: 10.7%; text-align: center; padding: 4px; font-weight: 600; border: 1px solid #495057; font-size: 0.75rem;">MON</th>
                      <th style="width: 10.7%; text-align: center; padding: 4px; font-weight: 600; border: 1px solid #495057; font-size: 0.75rem;">TUE</th>
                      <th style="width: 10.7%; text-align: center; padding: 4px; font-weight: 600; border: 1px solid #495057; font-size: 0.75rem;">WED</th>
                      <th style="width: 10.7%; text-align: center; padding: 4px; font-weight: 600; border: 1px solid #495057; font-size: 0.75rem;">THU</th>
                      <th style="width: 10.7%; text-align: center; padding: 4px; font-weight: 600; border: 1px solid #495057; font-size: 0.75rem;">FRI</th>
                      <th style="width: 10.7%; text-align: center; padding: 4px; font-weight: 600; border: 1px solid #495057; font-size: 0.75rem;">SAT</th>
                      <th style="width: 10.7%; text-align: center; padding: 4px; font-weight: 600; border: 1px solid #495057; font-size: 0.75rem;">SUN</th>
                    </tr>
                  </thead>
                  <tbody id="taskCompletionTableBody">
                    <!-- Task rows will be populated by JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer bg-light d-flex justify-content-between">
          <button type="button" class="btn btn-outline-warning" onclick="clearKitchenSchedule()">
            <i class="bi bi-arrow-clockwise me-2"></i>Clear Schedule
          </button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Close
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Load kitchen schedule when modal opens
    document.getElementById('kitchenAssignmentModal').addEventListener('shown.bs.modal', function() {
      console.log('Modal shown event triggered');
      loadKitchenAssignment();
    });

    // Also try to populate table when page loads (for testing)
    document.addEventListener('DOMContentLoaded', function() {
      console.log('DOM loaded, testing table population');
      setTimeout(() => {
        // Example: Test with custom tasks from the second image
        const exampleCustomTasks = [
          'Set up dishwashing station properly',
          'Wash dishes, utensils, and cookware thoroughly',
          'Organize clean dishes and utensils in proper storage'
        ];
        
        // Uncomment this line to test with custom tasks:
        // populateTaskCompletionTable(exampleCustomTasks);
        
        // Or use default tasks:
        populateTaskCompletionTable();
      }, 1000);
    });

    function loadKitchenAssignment(categoryId = null, areaName = null, checklistItems = null) {
      console.log('loadKitchenAssignment called with:', { categoryId, areaName, checklistItems });
      loadStudentList();
      loadWeeklyScheduleGrid(categoryId, areaName, checklistItems);
    }

    function loadStudentList() {
      const studentList = document.getElementById('studentList');
      
      // Get assigned students for this specific task - replace with actual data from your backend
      // This should only show students who are assigned to this particular task
      // Students styled like in reference image with blue theme
      const assignedStudents = [
        { name: 'Christian Virtudazo', role: 'Coordinator', color: '#ffd700', textColor: '#333' },
        { name: 'Rosana Jane', role: 'Member', color: '#4285f4', textColor: '#fff' },
        { name: 'Aisa Delos Santos', role: 'Member', color: '#4285f4', textColor: '#fff' },
        { name: 'Jane Grace', role: 'Member', color: '#4285f4', textColor: '#fff' },
        { name: 'Michael Jovita', role: 'Member', color: '#4285f4', textColor: '#fff' },
        { name: 'Bryan Delicano', role: 'Member', color: '#4285f4', textColor: '#fff' }
      ];

      // Update student count
      const studentCountElement = document.getElementById('studentCount');
      if (studentCountElement) {
        studentCountElement.textContent = `Found ${assignedStudents.length} students!`;
      }

      studentList.innerHTML = '';
      
      assignedStudents.forEach(student => {
        const studentCard = document.createElement('div');
        studentCard.className = 'student-card p-1 rounded';
        studentCard.style.cssText = `
          background: ${student.color};
          border: 1px solid ${student.role === 'Coordinator' ? '#e6c200' : '#3367d6'};
          cursor: pointer;
          font-size: 0.7rem;
          text-align: center;
          font-weight: 500;
          transition: all 0.2s ease;
          padding: 6px 8px;
          line-height: 1.3;
          width: 100%;
          display: block;
          margin-bottom: 6px;
          color: ${student.textColor};
          border-radius: 6px;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        `;
        studentCard.innerHTML = `
          <div>${student.name}</div>
          ${student.role === 'Coordinator' ? '<small style="font-size: 0.55rem; color: #666;">Coordinator</small>' : ''}
        `;
        
        // Make student card draggable
        studentCard.draggable = true;
        studentCard.setAttribute('data-student-name', student.name);
        studentCard.setAttribute('data-student-color', student.color);
        
        // Add drag event listeners
        studentCard.addEventListener('dragstart', function(e) {
          e.dataTransfer.setData('text/plain', student.name);
          e.dataTransfer.setData('student-color', student.color);
          this.style.opacity = '0.5';
        });
        
        studentCard.addEventListener('dragend', function(e) {
          this.style.opacity = '1';
        });
        
        // Add hover effect
        studentCard.addEventListener('mouseenter', function() {
          this.style.transform = 'scale(1.05)';
          this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)';
        });
        
        studentCard.addEventListener('mouseleave', function() {
          this.style.transform = 'scale(1)';
          this.style.boxShadow = 'none';
        });
        
        studentList.appendChild(studentCard);
      });
      
      // Add instruction text at the bottom
      const instructionDiv = document.createElement('div');
      instructionDiv.className = 'mt-2 text-center';
      instructionDiv.style.cssText = 'padding-top: 10px; border-top: 1px solid #eee; margin-top: 10px;';
      instructionDiv.innerHTML = '<small class="text-muted" style="font-size: 0.6rem; line-height: 1.2;">Drag students from above to the day cards on the right</small>';
      studentList.appendChild(instructionDiv);
    }

    function loadWeeklyScheduleGrid(categoryId = null, areaName = null, checklistItems = null) {
      // This function now populates the task completion table
      console.log('loadWeeklyScheduleGrid called with:', { categoryId, areaName, checklistItems });
      
      // Add a small delay to ensure DOM is ready
      setTimeout(() => {
        // If checklist items are provided, use them as custom tasks
        if (checklistItems && checklistItems.length > 0) {
          console.log('Using custom checklist items:', checklistItems);
          populateTaskCompletionTable(checklistItems);
        } else {
          // Try to fetch tasks from backend or use defaults
          fetchTasksForArea(categoryId, areaName);
        }
      }, 100);
    }
    
    // Function to fetch tasks for a specific area from backend
    function fetchTasksForArea(categoryId, areaName) {
      console.log('Fetching tasks for area:', areaName);
      
      // TODO: Replace with actual API call to fetch checklist items for this sub area
      // For now, we'll use the default tasks as fallback
      
      // Example API call structure:
      // fetch(`/api/sub-areas/${categoryId}/checklist-items`)
      //   .then(response => response.json())
      //   .then(data => {
      //     if (data.success && data.checklistItems) {
      //       populateTaskCompletionTable(data.checklistItems);
      //     } else {
      //       populateTaskCompletionTable(); // Use defaults
      //     }
      //   })
      //   .catch(error => {
      //     console.error('Error fetching tasks:', error);
      //     populateTaskCompletionTable(); // Use defaults
      //   });
      
      // For now, use default tasks
      populateTaskCompletionTable();
    }
    
    // Function to be called from admin side when viewing a specific sub area
    // This function should be called with the actual checklist items from the database
    function showAssignmentForSubArea(subAreaData) {
      console.log('Showing assignment for sub area:', subAreaData);
      
      // Extract checklist items from the sub area data
      const checklistItems = subAreaData.checklistItems || subAreaData.tasks || [];
      
      // Open the modal and populate with custom tasks
      const modal = new bootstrap.Modal(document.getElementById('kitchenAssignmentModal'));
      modal.show();
      
      // Load the assignment with custom tasks
      setTimeout(() => {
        loadKitchenAssignment(subAreaData.id, subAreaData.name, checklistItems);
      }, 300);
    }
    
    // Example usage from admin side:
    // const subAreaData = {
    //   id: 123,
    //   name: 'Dishwashing Station',
    //   checklistItems: [
    //     'Set up dishwashing station properly',
    //     'Wash dishes, utensils, and cookware thoroughly',
    //     'Organize clean dishes and utensils in proper storage'
    //   ]
    // };
    // showAssignmentForSubArea(subAreaData);
    
    function populateTaskCompletionTable(customTasks = null) {
      console.log('populateTaskCompletionTable called');
      const tableBody = document.getElementById('taskCompletionTableBody');
      if (!tableBody) {
        console.error('taskCompletionTableBody not found');
        return;
      }
      console.log('tableBody found:', tableBody);
      
      // Clear existing content
      tableBody.innerHTML = '';
      
      // Use custom tasks if provided, otherwise use default tasks
      let tasksToUse = customTasks;
      
      if (!tasksToUse || tasksToUse.length === 0) {
        // Default tasks based on the first image (fallback)
        tasksToUse = [
          'Assigned members wake up on time and completed their tasks as scheduled.',
          'The students assigned to cook the rice completed the task properly.',
          'The students assigned to cook the viand completed the task properly.',
          'The students assigned to assist the cook carried out their duties diligently.',
          'Ingredients were prepared ahead of time.',
          'The kitchen was properly cleaned after cooking.',
          'The food was transferred from the kitchen to the center.'
        ];
      }
      
      console.log('Processing tasks:', tasksToUse.length);
      tasksToUse.forEach((task, index) => {
        const row = document.createElement('tr');
        row.style.cssText = 'border: 1px solid #dee2e6; height: 60px;';
        
        // Task description cell
        const taskCell = document.createElement('td');
        taskCell.style.cssText = 'padding: 8px; border: 1px solid #dee2e6; vertical-align: middle; background: #f8f9fa; font-size: 0.8rem; line-height: 1.3; font-weight: 500; width: 25%; text-align: left; color: #333;';
        taskCell.textContent = task;
        row.appendChild(taskCell);
        console.log('Created task cell for:', task);
        
        // Day cells (MON-SUN) - Now with drag and drop zones
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        const dayColors = ['#4285f4', '#34a853', '#fbbc04', '#ea4335', '#9c27b0', '#ff9800', '#795548'];
        
        days.forEach((day, dayIndex) => {
          const dayCell = document.createElement('td');
          dayCell.className = 'drop-zone-cell';
          dayCell.style.cssText = `
            padding: 4px; 
            border: 1px solid #dee2e6; 
            text-align: center; 
            vertical-align: top; 
            background: #fff; 
            min-height: 60px; 
            position: relative;
            cursor: pointer;
            width: 10.7%;
          `;
          
          // Create simple assignment container
          const assignmentContainer = document.createElement('div');
          assignmentContainer.style.cssText = `
            min-height: 50px; 
            padding: 4px; 
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
          `;
          
          // Add checkbox at the top
          const checkbox = document.createElement('input');
          checkbox.type = 'checkbox';
          checkbox.className = 'form-check-input mb-1';
          checkbox.style.cssText = 'transform: scale(1.0); cursor: pointer; margin-bottom: 4px;';
          checkbox.id = `task_${index}_${day}`;
          
          // Create dropdown for student selection
          const studentSelect = document.createElement('select');
          studentSelect.className = 'form-select form-select-sm';
          studentSelect.style.cssText = 'font-size: 0.7rem; width: 100%; margin-bottom: 4px;';
          studentSelect.id = `student_select_${index}_${day}`;
          
          // Add default option
          const defaultOption = document.createElement('option');
          defaultOption.value = '';
          defaultOption.textContent = 'Select Student';
          studentSelect.appendChild(defaultOption);
          
          // Add student options (we'll populate this with actual students)
          const students = ['Christian Virtuoso', 'Rolanda Jane', 'Alex Delios Santos', 'Jane Grace', 'Michael Jovita', 'Bryan Delicano'];
          students.forEach(student => {
            const option = document.createElement('option');
            option.value = student;
            option.textContent = student;
            studentSelect.appendChild(option);
          });
          
          // Container for assigned students display
          const assignedStudents = document.createElement('div');
          assignedStudents.className = 'assigned-students-display';
          assignedStudents.style.cssText = 'width: 100%; text-align: center; font-size: 0.6rem;';
          
          // Add event listener for student selection
          studentSelect.addEventListener('change', function() {
            if (this.value) {
              addStudentToCell(this.value, assignedStudents, dayColors[dayIndex]);
              this.value = ''; // Reset dropdown
            }
          });
          
          assignmentContainer.appendChild(checkbox);
          assignmentContainer.appendChild(studentSelect);
          assignmentContainer.appendChild(assignedStudents);
          
          dayCell.appendChild(assignmentContainer);
          row.appendChild(dayCell);
        });
        
        tableBody.appendChild(row);
        console.log('Added row for task:', task);
      });
      console.log('Finished populating table with', defaultTasks.length, 'tasks');
    }
    
    // Function to add student to cell (simple dropdown approach)
    function addStudentToCell(studentName, container, color) {
      // Check if student is already assigned to this cell
      const existingStudents = container.querySelectorAll('.assigned-student');
      const isAlreadyAssigned = Array.from(existingStudents).some(student => 
        student.textContent.trim().includes(studentName)
      );
      
      if (!isAlreadyAssigned && studentName) {
        // Add student to this cell
        const studentDiv = document.createElement('div');
        studentDiv.className = 'assigned-student';
        studentDiv.style.cssText = `
          background: ${color}30; 
          color: #333; 
          padding: 2px 4px; 
          margin: 1px; 
          border-radius: 3px; 
          font-size: 0.6rem; 
          border: 1px solid ${color}50;
          display: inline-block;
          position: relative;
        `;
        
        studentDiv.innerHTML = `
          ${studentName}
          <button class="btn btn-sm btn-outline-danger ms-1" onclick="removeStudentFromTask(this)" style="font-size: 0.5rem; padding: 0px 2px; line-height: 1;">×</button>
        `;
        
        container.appendChild(studentDiv);
      }
    }
    
    // Function to remove student from task assignment
    function removeStudentFromTask(button) {
      const studentDiv = button.parentElement;
      studentDiv.remove();
    }
    
    // Function to remove student from assignment
    function removeStudent(button) {
      const studentDiv = button.parentElement;
      studentDiv.style.animation = 'fadeOut 0.3s ease';
      setTimeout(() => {
        studentDiv.remove();
        
        // Show "Drag Students Here" message if no students left
        const dropZone = studentDiv.closest('.drop-zone');
        const assignedStudents = dropZone.querySelectorAll('.assigned-student');
        if (assignedStudents.length === 0) {
          const dropMessage = dropZone.querySelector('.drop-message');
          if (dropMessage) {
            dropMessage.style.display = 'block';
            dropMessage.textContent = 'Drag Students Here';
          }
        }
      }, 300);
    }
    
    // Function to clear all schedule
    function clearKitchenSchedule() {
      const assignedStudents = document.querySelectorAll('#weeklyScheduleGrid .assigned-student');
      assignedStudents.forEach(student => {
        student.style.animation = 'fadeOut 0.3s ease';
        setTimeout(() => student.remove(), 300);
      });
      
      // Show all "Drag Students Here" messages
      setTimeout(() => {
        const dropMessages = document.querySelectorAll('#weeklyScheduleGrid .drop-message');
        dropMessages.forEach(message => {
          message.style.display = 'block';
          message.textContent = 'Drag Students Here';
        });
      }, 300);
    }
  </script>

  <script>
    // Additional CSS fixes for modal display
    const style = document.createElement('style');
    style.textContent = `
      /* Ensure all modals display properly */
      .modal {
        display: none !important;
      }
      
      .modal.show {
        display: block !important;
      }
      
      .student-card {
        display: flex !important;
        visibility: visible !important;
      }
      
      /* Kitchen Assignment Modal Specific Styles */
      .student-card {
        transition: all 0.2s ease;
      }
      
      .student-card:hover {
        transform: scale(1.05);
        box-shadow: 0 2px 8px rgba(0,0,0,0.2);
      }
      
      .day-card {
        transition: all 0.2s ease;
      }
      
      .day-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      }
      
      .drop-zone {
        transition: all 0.2s ease;
      }
      
      .assigned-student {
        animation: fadeIn 0.3s ease;
      }
      
      @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
      }
      
      @keyframes fadeOut {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-10px); }
      }
      
      /* Standardize ALL modal headers and footers for consistent height */
                      <div class="col">
                        <div class="drop-zone" data-task="prep-lunch" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-lunch-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-lunch" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-lunch-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-lunch" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-lunch-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Food Preparation - Dinner -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label prep">
                          Food Preparation<br>
                          <small style="font-size: 8px;">Dinner</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="prep-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="prep-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>
                    <!-- Cook - Breakfast -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label cook">
                          Cook<br>
                          <small style="font-size: 8px;">Breakfast</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-breakfast" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-breakfast-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-breakfast" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-breakfast-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-breakfast" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-breakfast-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-breakfast" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-breakfast-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-breakfast" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-breakfast-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-breakfast" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-breakfast-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-breakfast" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-breakfast-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Cook - Lunch -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label cook">
                          Cook<br>
                          <small style="font-size: 8px;">Lunch</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-lunch" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-lunch-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-lunch" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-lunch-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-lunch" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-lunch-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-lunch" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-lunch-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-lunch" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-lunch-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-lunch" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-lunch-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-lunch" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-lunch-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Cook - Dinner -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label cook">
                          Cook<br>
                          <small style="font-size: 8px;">Dinner</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
<hr style="border: 2px solid #ddd; margin: 20px 0;">

          <!-- Available Students (BOTTOM) -->
          <div class="row">
            <div class="col-32">
              <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #4285f4, #34a853); color: white; padding: 16px 20px; border: none;">
                  <h6 class="mb-0" style="font-weight: 600; font-size: 16px;"><i class="bi bi-people me-2"></i>👥 Available Students</h6>
                </div>
                <div class="card-body p-3" style="max-height: 200px; overflow-x: auto; overflow-y: hidden; background: #f8fbff; position: relative;">
                  <div id="modalStudentList" class="student-list d-flex flex-nowrap" style="min-width: max-content; padding: 8px 0; gap: 0;">
                    <!-- All Students Option -->
                    <div class="student-card all-students-option" draggable="true" data-student-id="all" data-student-name="All Students" style="flex: 0 0 auto; width: auto; min-width: 200px;">
                      <div class="d-flex align-items-center">
                        <div class="student-avatar" style="background: #fff; color: #4CAF50; font-weight: bold; border: 2px solid #fff;">
                          ALL
                        </div>
                        <div class="student-info">
                          <div class="student-name" style="color: white; font-weight: 600;">All Students</div>
                          <div class="student-id" style="color: rgba(255,255,255,0.8);">Assign Everyone</div>
                        </div>
                      </div>
                    </div>
                    @foreach($students as $student)
                      <div class="student-card" draggable="true" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}" style="flex: 0 0 auto; width: auto; min-width: 200px;">
                        <div class="d-flex align-items-center">
                          <div class="student-avatar">
                            @php
                              $nameParts = explode(' ', trim($student->name));
                              $initials = '';
                              if (count($nameParts) >= 2) {
                                $initials = substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1);
                              } else {
                                $initials = substr($student->name, 0, 2);
                              }
                            @endphp
                            {{ $initials }}
                          </div>
                          <div class="student-info">
                            <div class="student-name">{{ $student->name }}</div>
                            <div class="student-id">ID: {{ $student->id }}</div>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-success" onclick="saveKitchenAssignments()">
            <i class="bi bi-save me-2"></i>Save Assignments
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Kitchen Assignment Drag & Drop JavaScript -->
  <script>
    // Kitchen Assignment Modal Functionality - Event listeners will be set up after functions are defined

    // Assign all students to a specific task across all time slots (max 4 total)
    function assignAllToTask(taskType) {
      const students = document.querySelectorAll('#modalStudentList .student-card[style*="flex"], #modalStudentList .student-card:not([style])');
      const timeSlots = ['breakfast', 'lunch', 'dinner'];
      
      // Check current total assignments
      const currentTotal = getTotalAssignedStudents();
      const remainingSlots = 4 - currentTotal;
      
      if (remainingSlots <= 0) {
        showNotification('Maximum 4 students already assigned across all tasks', 'warning');
        return;
      }
      
      let studentIndex = 0;
      let totalAssigned = 0;
      
      timeSlots.forEach(time => {
        const dropZone = document.querySelector(`[data-task="${taskType}"][data-time="${time}"]`);
        const maxStudents = parseInt(dropZone.dataset.max);
        const assignedContainer = dropZone.querySelector('.assigned-students');
        
        // Clear existing assignments for this task/time slot
        assignedContainer.innerHTML = '';
        
        // Assign students up to the maximum, but respect global 99-student limit
        for (let i = 0; i < maxStudents && studentIndex < students.length && totalAssigned < remainingSlots; i++) {
          const student = students[studentIndex];
          const studentData = {
            studentId: student.dataset.studentId,
            studentName: student.dataset.studentName
          };
          
          const assignedStudent = createAssignedStudentElement(studentData, taskType, time);
          assignedContainer.appendChild(assignedStudent);
          hideStudentFromAvailableList(studentData.studentId);
          studentIndex++;
          totalAssigned++;
        }
        
        // Update drop zone status
        updateDropZoneStatus(dropZone, assignedContainer.children.length, maxStudents);
      });
      
      showNotification(`${totalAssigned} students assigned to ${taskType} tasks (${99 - currentTotal - totalAssigned} slots remaining)`, 'success');
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Initialize when page loads
      console.log('Kitchen assignment system ready');
    });

    function initializeDragAndDrop() {
      // Add drag event listeners to student cards in modal (including All Students option)
      const studentCards = document.querySelectorAll('#modalStudentList .student-card');
      studentCards.forEach(card => {
        card.addEventListener('dragstart', handleDragStart);
        card.addEventListener('dragend', handleDragEnd);
      });

      // Add drop event listeners to drop zones
      const dropZones = document.querySelectorAll('.drop-zone');
      dropZones.forEach(zone => {
        zone.addEventListener('dragover', handleDragOver);
        zone.addEventListener('dragenter', handleDragEnter);
        zone.addEventListener('dragleave', handleDragLeave);
        zone.addEventListener('drop', handleDrop);
      });
    }

    let draggedElement = null;

    function handleDragStart(e) {
      draggedElement = this;
      this.classList.add('dragging');
      
      // Store student data
      e.dataTransfer.setData('text/plain', JSON.stringify({
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName
      }));
      
      e.dataTransfer.effectAllowed = 'move';
    }

    function handleDragEnd(e) {
      this.classList.remove('dragging');
      draggedElement = null;
    }

    function handleDragOver(e) {
      e.preventDefault();
      e.dataTransfer.dropEffect = 'move';
    }

    function handleDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleDragLeave(e) {
      // Only remove drag-over if we're actually leaving the drop zone
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
      }
    }

    function handleDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      if (!draggedElement) return;
      
      // Get drop zone info
      const task = this.dataset.task;
      const time = this.dataset.time;
      const maxStudents = parseInt(this.dataset.max);
      
      // Get student data
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      
      // Handle "All Students" option
      if (studentData.studentId === 'all') {
        assignAllStudentsToSlot(this, task, time, maxStudents);
        return;
      }
      
      // Check total assignments limit (99 students max)
      const totalAssigned = getTotalAssignedStudents();
      if (totalAssigned >= 99) {
        showNotification('Maximum 99 students can be assigned across all tasks', 'warning');
        return;
      }
      
      // Check if drop zone is full
      const assignedContainer = this.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for ${task} during ${time}`, 'warning');
        return;
      }
      
      // Check if student is already assigned to this slot
      const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentData.studentId}"]`);
      if (existingAssignment) {
        showNotification(`${studentData.studentName} is already assigned to this slot`, 'info');
        return;
      }
      
      // Create assigned student element
      const assignedStudent = createAssignedStudentElement(studentData, task, time);
      assignedContainer.appendChild(assignedStudent);
      
      // Hide student from available list
      hideStudentFromAvailableList(studentData.studentId);
      
      // Update drop zone status
      updateDropZoneStatus(this, currentCount + 1, maxStudents);
      
      showNotification(`${studentData.studentName} assigned to ${task} for ${time}`, 'success');
    }

    function createAssignedStudentElement(studentData, task, time) {
      const div = document.createElement('div');
      div.className = 'assigned-student';
      div.dataset.studentId = studentData.studentId;
      div.dataset.task = task;
      div.dataset.time = time;
      
      div.innerHTML = `
        <span class="student-name">${studentData.studentName}</span>
        <button class="remove-btn" onclick="removeAssignment(this)" title="Remove assignment">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      return div;
    }

    function removeAssignment(button) {
      const assignedStudent = button.closest('.assigned-student');
      const dropZone = assignedStudent.closest('.drop-zone');
      const studentName = assignedStudent.querySelector('.student-name').textContent;
      const studentId = assignedStudent.dataset.studentId;
      
      // Remove the assignment
      assignedStudent.remove();
      
      // Show student back in available list
      showStudentInAvailableList(studentId);
      
      // Update drop zone status
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      const maxStudents = parseInt(dropZone.dataset.max);
      updateDropZoneStatus(dropZone, currentCount, maxStudents);
      
      showNotification(`${studentName} removed from assignment`, 'info');
    }

    function updateDropZoneStatus(dropZone, currentCount, maxStudents) {
      if (currentCount >= maxStudents) {
        dropZone.classList.add('full');
      } else {
        dropZone.classList.remove('full');
      }
      
      const header = dropZone.querySelector('.drop-zone-header small');
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    function showNotification(message, type = 'info') {
      // Create notification element
      const notification = document.createElement('div');
      notification.className = `alert alert-${getBootstrapAlertClass(type)} alert-dismissible fade show position-fixed`;
      notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
      
      notification.innerHTML = `
        ${getNotificationIcon(type)} ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `;
      
      document.body.appendChild(notification);
      
      // Auto remove after 5 seconds
      setTimeout(() => {
        if (notification.parentNode) {
          notification.remove();
        }
      }, 5000);
    }

    function getBootstrapAlertClass(type) {
      const classes = {
        'success': 'success',
        'error': 'danger',
        'warning': 'warning',
        'info': 'info'
      };
      return classes[type] || 'info';
    }

    function getNotificationIcon(type) {
      const icons = {
        'success': '<i class="bi bi-check-circle me-2"></i>',
        'error': '<i class="bi bi-exclamation-triangle me-2"></i>',
        'warning': '<i class="bi bi-exclamation-triangle me-2"></i>',
        'info': '<i class="bi bi-info-circle me-2"></i>'
      };
      return icons[type] || icons['info'];
    }

    // Load existing assignments on page load
    // Function to get only students assigned to specific task and time
    function getTaskSpecificStudents(categoryName, taskType = null, timeSlot = null) {
      const params = new URLSearchParams({
        category_name: categoryName
      });
      
      if (taskType) params.append('task_type', taskType);
      if (timeSlot) params.append('time_slot', timeSlot);
      
      return fetch(`/get-task-assignments?${params}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            console.log(`Found ${data.total_count} students for ${categoryName}`, data.filters_applied);
            return data.assignments;
          } else {
            console.error('Failed to get task assignments:', data.message);
            return [];
          }
        })
        .catch(error => {
          console.error('Error fetching task assignments:', error);
          return [];
        });
    }

    function loadExistingAssignments() {
      fetch('/get-kitchen-assignments')
        .then(response => response.json())
        .then(data => {
          if (data.assignments) {
            data.assignments.forEach(assignment => {
              const dropZone = document.querySelector(`[data-task="${assignment.task}"][data-time="${assignment.time}"]`);
              if (dropZone) {
                const assignedContainer = dropZone.querySelector('.assigned-students');
                const assignedStudent = createAssignedStudentElement({
                  studentId: assignment.student_id,
                  studentName: assignment.student_name
                }, assignment.task, assignment.time);
                
                assignedContainer.appendChild(assignedStudent);
                
                // Update drop zone status
                const currentCount = assignedContainer.children.length;
                const maxStudents = parseInt(dropZone.dataset.max);
                updateDropZoneStatus(dropZone, currentCount, maxStudents);
              }
            });
          }
        })
        .catch(error => {
          console.error('Error loading assignments:', error);
        });
    }

    // Function to load and display only assigned students for each drop zone
    function loadTaskSpecificAssignments() {
      // Get all drop zones in the kitchen modal
      const dropZones = document.querySelectorAll('#kitchenAssignmentModal .drop-zone[data-task][data-time]');
      
      dropZones.forEach(async (dropZone) => {
        const taskType = dropZone.getAttribute('data-task');
        const timeSlot = dropZone.getAttribute('data-time');
        
        try {
          // Get only students assigned to this specific task and time
          const assignedStudents = await getTaskSpecificStudents('Kitchen', taskType, timeSlot);
          
          // Clear the drop zone first
          const assignedContainer = dropZone.querySelector('.assigned-students');
          if (assignedContainer) {
            assignedContainer.innerHTML = '';
          }
          
          // Add only the assigned students
          assignedStudents.forEach(student => {
            if (assignedContainer) {
              const studentElement = createAssignedStudentElement({
                studentId: student.id,
                studentName: student.name
              }, taskType, timeSlot);
              assignedContainer.appendChild(studentElement);
            }
          });
          
          // Update the header count
          updateDropZoneHeader(dropZone);
          
          console.log(`Loaded ${assignedStudents.length} students for ${taskType} on ${timeSlot}`);
          
        } catch (error) {
          console.error(`Error loading assignments for ${taskType} on ${timeSlot}:`, error);
        }
      });
    }

    // Update drop zone header with current count
    function updateDropZoneHeader(dropZone) {
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const header = dropZone.querySelector('.drop-zone-header small');
      const maxStudents = parseInt(dropZone.getAttribute('data-max') || 99);
      const currentCount = assignedContainer ? assignedContainer.children.length : 0;
      
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    // Get total number of assigned students across all tasks
    function getTotalAssignedStudents() {
      const assignedStudents = document.querySelectorAll('.assigned-students .assigned-student');
      return assignedStudents.length;
    }

    // Update the assignment counter display
    function updateAssignmentCounter() {
      const total = getTotalAssignedStudents();
      const counter = document.getElementById('assignmentCounter');
      if (counter) {
        counter.textContent = `${total}/4 Students Assigned`;
        
        // Change color based on count
        counter.className = 'badge ';
        if (total === 0) {
          counter.className += 'bg-light text-dark';
        } else if (total < 4) {
          counter.className += 'bg-success text-white';
        } else {
          counter.className += 'bg-warning text-dark';
        }
      }
    }

    // Hide student from available list when assigned
    function hideStudentFromAvailableList(studentId) {
      const studentCard = document.querySelector(`#modalStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'none';
      }
    }

    // Show student back in available list when unassigned
    function showStudentInAvailableList(studentId) {
      const studentCard = document.querySelector(`#modalStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'flex';
      }
    }

    // Assign all available students to a specific slot
    function assignAllStudentsToSlot(dropZone, task, time, maxStudents) {
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      // Get all available students (not hidden)
      const availableStudents = document.querySelectorAll('#modalStudentList .student-card:not(.all-students-option)');
      const visibleStudents = Array.from(availableStudents).filter(card => 
        card.style.display !== 'none' && card.dataset.studentId !== 'all'
      );
      
      let assignedCount = 0;
      let totalAssigned = getTotalAssignedStudents();
      
      for (const studentCard of visibleStudents) {
        // Check if we've reached the slot limit
        if (currentCount + assignedCount >= maxStudents) {
          break;
        }
        
        // Check if we've reached the global limit
        if (totalAssigned >= 99) {
          break;
        }
        
        const studentId = studentCard.dataset.studentId;
        const studentName = studentCard.dataset.studentName;
        
        // Check if student is already assigned to this slot
        const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentId}"]`);
        if (existingAssignment) {
          continue;
        }
        
        // Create assigned student element
        const assignedStudent = createAssignedStudentElement({
          studentId: studentId,
          studentName: studentName
        }, task, time);
        
        assignedContainer.appendChild(assignedStudent);
        
        // Hide student from available list
        hideStudentFromAvailableList(studentId);
        
        assignedCount++;
        totalAssigned++;
      }
      
      // Update drop zone status
      updateDropZoneStatus(dropZone, currentCount + assignedCount, maxStudents);
      
      if (assignedCount > 0) {
        showNotification(`${assignedCount} students assigned to ${task} for ${time}`, 'success');
      } else {
        showNotification('No available students to assign', 'info');
      }
    }

    // Clear all assignments from drop zones
    function clearAllKitchenAssignments() {
      document.querySelectorAll('.assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      
      // Reset all drop zone counters
      document.querySelectorAll('.drop-zone').forEach(zone => {
        const maxStudents = parseInt(zone.dataset.max);
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `0/${maxStudents} students`;
        }
        zone.classList.remove('full');
      });

      // Show all students back in available list
      document.querySelectorAll('#modalStudentList .student-card').forEach(card => {
        card.style.display = 'flex';
      });
    }

    // Load existing kitchen assignments when modal opens
    function loadExistingKitchenAssignments() {
      fetch('/get-kitchen-assignments')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.assignments) {
            // Clear existing assignments
            document.querySelectorAll('#kitchenAssignmentModal .assigned-students').forEach(container => {
              container.innerHTML = '';
            });
            
            // Load assignments into the grid
            data.assignments.forEach(assignment => {
              const targetContainer = document.getElementById(`${assignment.task_type}-${assignment.time_slot}`);
              if (targetContainer) {
                const studentElement = document.createElement('div');
                studentElement.className = 'assigned-student';
                studentElement.draggable = true;
                studentElement.dataset.studentId = assignment.student_id;
                studentElement.dataset.task = assignment.task_type;
                studentElement.dataset.time = assignment.time_slot;
                studentElement.innerHTML = `
                  <div class="student-name">${assignment.student_name}</div>
                  <button class="btn btn-sm btn-outline-danger remove-student" onclick="removeKitchenStudent(this)">
                    <i class="bi bi-x"></i>
                  </button>
                `;
                targetContainer.appendChild(studentElement);
              }
            });
            
            // Update counters
            updateKitchenCounters();
          }
        })
        .catch(error => {
          console.error('Error loading kitchen assignments:', error);
        });
    }
    
    // Initialize kitchen drag and drop functionality
    function initializeKitchenDragAndDrop() {
      console.log('Initializing Kitchen drag and drop...');
      
      // Wait a bit more for DOM to be ready
      setTimeout(() => {
        // Try multiple selectors to find student cards
        let studentCards = document.querySelectorAll('#modalStudentList .student-card');
        
        if (studentCards.length === 0) {
          studentCards = document.querySelectorAll('#kitchenAssignmentModal .student-card');
        }
        
        if (studentCards.length === 0) {
          studentCards = document.querySelectorAll('.student-card');
        }
        
        console.log(`Found ${studentCards.length} student cards for kitchen drag and drop`);
        
        studentCards.forEach(card => {
          card.addEventListener('dragstart', handleKitchenDragStart);
          card.addEventListener('dragend', handleKitchenDragEnd);
        });

        // Add drop event listeners to drop zones
        const dropZones = document.querySelectorAll('#kitchenAssignmentModal .drop-zone');
        console.log(`Found ${dropZones.length} drop zones for kitchen`);
        
        dropZones.forEach(zone => {
          zone.addEventListener('dragover', handleKitchenDragOver);
          zone.addEventListener('dragenter', handleKitchenDragEnter);
          zone.addEventListener('dragleave', handleKitchenDragLeave);
          zone.addEventListener('drop', handleKitchenDrop);
        });
        
        console.log('Kitchen drag and drop initialized successfully!');
        
        // Force re-initialization function
        window.forceKitchenDragInit = function() {
          console.log('Force re-initializing Kitchen drag and drop...');
          initializeKitchenDragAndDrop();
          console.log('Kitchen drag and drop force re-initialized!');
        };
      }, 100);
    }

    // Kitchen drag and drop handlers
    let kitchenDraggedElement = null;

    function handleKitchenDragStart(e) {
      kitchenDraggedElement = this;
      const studentData = {
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName || this.querySelector('.student-name')?.textContent || this.textContent.trim()
      };
      e.dataTransfer.setData('text/plain', JSON.stringify(studentData));
      this.style.opacity = '0.5';
    }

    function handleKitchenDragEnd(e) {
      this.style.opacity = '1';
      kitchenDraggedElement = null;
    }

    function handleKitchenDragOver(e) {
      e.preventDefault();
    }

    function handleKitchenDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleKitchenDragLeave(e) {
      this.classList.remove('drag-over');
    }

    function handleKitchenDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      const assignedContainer = this.querySelector('.assigned-students');
      const maxStudents = parseInt(this.dataset.max) || 99;
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for this task`, 'warning');
        return;
      }
      
      // Create assigned student element
      const studentElement = document.createElement('div');
      studentElement.className = 'assigned-student';
      studentElement.draggable = true;
      studentElement.dataset.studentId = studentData.studentId;
      studentElement.innerHTML = `
        <div class="student-name">${studentData.studentName}</div>
        <button class="btn btn-sm btn-outline-danger remove-student" onclick="removeKitchenStudent(this)">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      assignedContainer.appendChild(studentElement);
      
      // Hide the student from available list
      if (kitchenDraggedElement) {
        kitchenDraggedElement.style.display = 'none';
      }
      
      // Update counters
      updateKitchenCounters();
    }

    function removeKitchenStudent(button) {
      const studentElement = button.closest('.assigned-student');
      const studentId = studentElement.dataset.studentId;
      
      // Show student back in available list
      const availableStudent = document.querySelector(`#modalStudentList .student-card[data-student-id="${studentId}"]`);
      if (availableStudent) {
        availableStudent.style.display = 'flex';
      }
      
      // Remove from assigned
      studentElement.remove();
      
      // Update counters
      updateKitchenCounters();
    }

    function updateKitchenCounters() {
      document.querySelectorAll('#kitchenAssignmentModal .drop-zone').forEach(zone => {
        const assignedContainer = zone.querySelector('.assigned-students');
        const currentCount = assignedContainer ? assignedContainer.children.length : 0;
        const maxStudents = parseInt(zone.dataset.max) || 99;
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `${currentCount}/${maxStudents} students`;
        }
      });
    }

    // Enhanced Horizontal Scrolling for Available Students
    function initializeHorizontalScrolling() {
      const studentLists = document.querySelectorAll('.student-list');
      
      studentLists.forEach(list => {
        // Add smooth scrolling behavior
        list.style.scrollBehavior = 'smooth';
        
        // Add mouse wheel horizontal scrolling
        list.addEventListener('wheel', function(e) {
          if (e.deltaY !== 0) {
            e.preventDefault();
            this.scrollLeft += e.deltaY;
          }
        });
        
        // Add touch/mouse drag scrolling
        let isDown = false;
        let startX;
        let scrollLeft;
        
        list.addEventListener('mousedown', function(e) {
          if (e.target.closest('.student-card')) return; // Don't interfere with drag
          isDown = true;
          startX = e.pageX - this.offsetLeft;
          scrollLeft = this.scrollLeft;
          this.style.cursor = 'grabbing';
        });
        
        list.addEventListener('mouseleave', function() {
          isDown = false;
          this.style.cursor = 'default';
        });
        
        list.addEventListener('mouseup', function() {
          isDown = false;
          this.style.cursor = 'default';
        });
        
        list.addEventListener('mousemove', function(e) {
          if (!isDown) return;
          e.preventDefault();
          const x = e.pageX - this.offsetLeft;
          const walk = (x - startX) * 2;
          this.scrollLeft = scrollLeft - walk;
        });
      });
    }
    
    // Set up event listeners after all functions are defined
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize horizontal scrolling for all student lists
      initializeHorizontalScrolling();
      
      // Initialize drag and drop when modal opens
      const kitchenModal = document.getElementById('kitchenAssignmentModal');
      if (kitchenModal) {
        kitchenModal.addEventListener('shown.bs.modal', function() {
          console.log('Kitchen modal opened!');
          // Re-initialize scrolling for this modal
          initializeHorizontalScrolling();
          // Add small delay to ensure modal is fully rendered
          setTimeout(() => {
            console.log('Initializing kitchen drag and drop after delay...');
            if (typeof initializeKitchenDragAndDrop === 'function') {
              initializeKitchenDragAndDrop();
            } else {
              console.error('initializeKitchenDragAndDrop function not found!');
            }
            if (typeof loadExistingKitchenAssignments === 'function') {
              loadExistingKitchenAssignments();
            } else {
              console.error('loadExistingKitchenAssignments function not found!');
            }
          }, 500);
        });

        // Clear assignments when modal closes
        kitchenModal.addEventListener('hidden.bs.modal', function() {
          if (typeof clearAllKitchenAssignments === 'function') {
            clearAllKitchenAssignments();
          } else {
            console.error('clearAllKitchenAssignments function not found!');
          }
        });
      }
    });

    // Initialize other modals with basic functionality
    document.addEventListener('DOMContentLoaded', function() {
      console.log('Initializing all assignment modals...');
      
      // FORCE REMOVE ALL aria-hidden attributes from modals
      console.log('Forcefully removing all aria-hidden attributes...');
      document.querySelectorAll('.modal').forEach(modal => {
        if (modal.hasAttribute('aria-hidden')) {
          console.log(`Removing aria-hidden from modal: ${modal.id}`);
          modal.removeAttribute('aria-hidden');
        }
      });
      
      // Check if Bootstrap is available
      if (typeof bootstrap === 'undefined') {
        console.error('Bootstrap is not loaded! Modals will not work.');
        return;
      }
      console.log('Bootstrap is available, initializing modals...');
      // Office Assignment Modal
      const officeModal = document.getElementById('officeAssignmentModal');
      if (officeModal) {
        // Initialize Bootstrap modal explicitly
        const officeBootstrapModal = new bootstrap.Modal(officeModal);
        
        officeModal.addEventListener('shown.bs.modal', function() {
          console.log('Office assignment modal opened!');
          // Force show modal content with enhanced visibility
          const modalBody = officeModal.querySelector('.modal-body');
          const assignmentGrid = officeModal.querySelector('.assignment-grid');
          const allElements = officeModal.querySelectorAll('*');
          
          // Force all elements to be visible
          allElements.forEach(el => {
            el.style.visibility = 'visible';
            el.style.display = el.style.display === 'none' ? 'block' : el.style.display || 'block';
            el.style.opacity = '1';
          });
          
          if (modalBody) {
            modalBody.style.display = 'block';
            modalBody.style.visibility = 'visible';
            modalBody.style.opacity = '1';
          }
          if (assignmentGrid) {
            assignmentGrid.style.display = 'block';
            assignmentGrid.style.visibility = 'visible';
            assignmentGrid.style.opacity = '1';
          }
          console.log('✅ Forced Office modal content to be visible');
        });
        
        // Add click handler for office buttons
        document.querySelectorAll('button[data-bs-target="#officeAssignmentModal"]').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Office button clicked, showing modal...');
            officeBootstrapModal.show();
          });
        });
      }

      // Conference Assignment Modal
      const conferenceModal = document.getElementById('conferenceAssignmentModal');
      if (conferenceModal) {
        const conferenceBootstrapModal = new bootstrap.Modal(conferenceModal);
        
        conferenceModal.addEventListener('shown.bs.modal', function() {
          console.log('Conference assignment modal opened!');
          // Force show modal content with enhanced visibility
          const modalBody = conferenceModal.querySelector('.modal-body');
          const assignmentGrid = conferenceModal.querySelector('.assignment-grid');
          const allElements = conferenceModal.querySelectorAll('*');
          
          // Force all elements to be visible
          allElements.forEach(el => {
            el.style.visibility = 'visible';
            el.style.display = el.style.display === 'none' ? 'block' : el.style.display || 'block';
            el.style.opacity = '1';
          });
          
          if (modalBody) {
            modalBody.style.display = 'block';
            modalBody.style.visibility = 'visible';
            modalBody.style.opacity = '1';
          }
          if (assignmentGrid) {
            assignmentGrid.style.display = 'block';
            assignmentGrid.style.visibility = 'visible';
            assignmentGrid.style.opacity = '1';
          }
          console.log('✅ Forced Conference modal content to be visible');
        });
        
        document.querySelectorAll('button[data-bs-target="#conferenceAssignmentModal"]').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Conference button clicked, showing modal...');
            conferenceBootstrapModal.show();
          });
        });
      }

      // Dining Assignment Modal
      const diningModal = document.getElementById('diningAssignmentModal');
      if (diningModal) {
        const diningBootstrapModal = new bootstrap.Modal(diningModal);
        
        diningModal.addEventListener('shown.bs.modal', function() {
          console.log('Dining assignment modal opened!');
          // Force show modal content with enhanced visibility
          const modalBody = diningModal.querySelector('.modal-body');
          const assignmentGrid = diningModal.querySelector('.assignment-grid');
          const allElements = diningModal.querySelectorAll('*');
          
          // Force all elements to be visible
          allElements.forEach(el => {
            el.style.visibility = 'visible';
            el.style.display = el.style.display === 'none' ? 'block' : el.style.display || 'block';
            el.style.opacity = '1';
          });
          
          if (modalBody) {
            modalBody.style.display = 'block';
            modalBody.style.visibility = 'visible';
            modalBody.style.opacity = '1';
          }
          if (assignmentGrid) {
            assignmentGrid.style.display = 'block';
            assignmentGrid.style.visibility = 'visible';
          }
          console.log('✅ Forced Dining modal content to be visible');
        });
        
        document.querySelectorAll('button[data-bs-target="#diningAssignmentModal"]').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Dining button clicked, showing modal...');
            diningBootstrapModal.show();
          });
        });
      }

      // Dishwashing Assignment Modal
      const dishwashingModal = document.getElementById('dishwashingAssignmentModal');
      if (dishwashingModal) {
        const dishwashingBootstrapModal = new bootstrap.Modal(dishwashingModal);
        
        dishwashingModal.addEventListener('shown.bs.modal', function() {
          console.log('Dishwashing assignment modal opened!');
          // Force show modal content
          const modalBody = dishwashingModal.querySelector('.modal-body');
          const assignmentGrid = dishwashingModal.querySelector('.assignment-grid');
          if (modalBody) {
            modalBody.style.display = 'block';
            modalBody.style.visibility = 'visible';
          }
          if (assignmentGrid) {
            assignmentGrid.style.display = 'block';
            assignmentGrid.style.visibility = 'visible';
          }
          console.log('✅ Forced Dishwashing modal content to be visible');
        });
        
        document.querySelectorAll('button[data-bs-target="#dishwashingAssignmentModal"]').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Dishwashing button clicked, showing modal...');
            dishwashingBootstrapModal.show();
          });
        });
      }

      // Ground Assignment Modal
      const groundModal = document.getElementById('groundAssignmentModal');
      if (groundModal) {
        const groundBootstrapModal = new bootstrap.Modal(groundModal);
        
        groundModal.addEventListener('shown.bs.modal', function() {
          console.log('Ground assignment modal opened!');
          // Force show modal content
          const modalBody = groundModal.querySelector('.modal-body');
          const assignmentGrid = groundModal.querySelector('.assignment-grid');
          if (modalBody) {
            modalBody.style.display = 'block';
            modalBody.style.visibility = 'visible';
          }
          if (assignmentGrid) {
            assignmentGrid.style.display = 'block';
            assignmentGrid.style.visibility = 'visible';
          }
          console.log('✅ Forced Ground modal content to be visible');
        });
        
        document.querySelectorAll('button[data-bs-target="#groundAssignmentModal"]').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Ground button clicked, showing modal...');
            groundBootstrapModal.show();
          });
        });
      }

      // Waste Assignment Modal
      const wasteModal = document.getElementById('wasteAssignmentModal');
      if (wasteModal) {
        const wasteBootstrapModal = new bootstrap.Modal(wasteModal);
        
        wasteModal.addEventListener('shown.bs.modal', function() {
          console.log('Waste assignment modal opened!');
          // Force show modal content
          const modalBody = wasteModal.querySelector('.modal-body');
          const assignmentGrid = wasteModal.querySelector('.assignment-grid');
          if (modalBody) {
            modalBody.style.display = 'block';
            modalBody.style.visibility = 'visible';
          }
          if (assignmentGrid) {
            assignmentGrid.style.display = 'block';
            assignmentGrid.style.visibility = 'visible';
          }
          console.log('✅ Forced Waste modal content to be visible');
        });
        
        document.querySelectorAll('button[data-bs-target="#wasteAssignmentModal"]').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Waste button clicked, showing modal...');
            wasteBootstrapModal.show();
          });
        });
      }
      
      // Debug: Check if all View Assignment buttons exist
      const viewButtons = document.querySelectorAll('button[data-bs-target*="AssignmentModal"]');
      console.log(`Found ${viewButtons.length} View Assignment buttons:`);
      viewButtons.forEach((btn, index) => {
        const target = btn.getAttribute('data-bs-target');
        console.log(`Button ${index + 1}: ${target}`);
        
        // Add click event listener for debugging
        btn.addEventListener('click', function() {
          console.log(`Clicked View Assignment button for: ${target}`);
          const modal = document.querySelector(target);
          if (modal) {
            console.log(`Modal ${target} found, attempting to show...`);
          } else {
            console.error(`Modal ${target} not found!`);
          }
        });
      });
      
      // Test function to manually open modals
      window.testModal = function(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
          const bsModal = new bootstrap.Modal(modal);
          bsModal.show();
          console.log(`Manually opened modal: ${modalId}`);
        } else {
          console.error(`Modal not found: ${modalId}`);
        }
      };
      
      console.log('Modal initialization complete! Use testModal("modalId") to test manually.');
      
      // Test all modals immediately
      console.log('Testing modal availability:');
      const modalIds = ['kitchenAssignmentModal', 'officeAssignmentModal', 'conferenceAssignmentModal', 'diningAssignmentModal', 'dishwashingAssignmentModal', 'groundAssignmentModal', 'wasteAssignmentModal'];
      modalIds.forEach(id => {
        const modal = document.getElementById(id);
        console.log(`${id}: ${modal ? 'FOUND' : 'NOT FOUND'}`);
      });
      
      // Add global test function
      window.testAllModals = function() {
        modalIds.forEach(id => {
          console.log(`Testing ${id}...`);
          testModal(id);
        });
      };
      
      console.log('Use testAllModals() to test all modals at once.');
      
      // Continuously monitor and remove aria-hidden attributes
      const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
          if (mutation.type === 'attributes' && mutation.attributeName === 'aria-hidden') {
            const target = mutation.target;
            if (target.classList.contains('modal') && target.hasAttribute('aria-hidden')) {
              console.log(`Automatically removing aria-hidden from ${target.id}`);
              target.removeAttribute('aria-hidden');
            }
          }
        });
      });
      
      // Start observing all modals for aria-hidden changes
      document.querySelectorAll('.modal').forEach(modal => {
        observer.observe(modal, {
          attributes: true,
          attributeFilter: ['aria-hidden']
        });
      });
      
      console.log('Started monitoring for aria-hidden attributes on all modals.');
    });

    // Add missing functions for other modals
    function clearAllOfficeAssignments() {
      console.log('Clearing all office assignments...');
      document.querySelectorAll('#officeAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      showNotification('All office assignments cleared', 'info');
    }

    function clearAllConferenceAssignments() {
      console.log('Clearing all conference assignments...');
      document.querySelectorAll('#conferenceAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      showNotification('All conference assignments cleared', 'info');
    }

    function clearAllDiningAssignments() {
      console.log('Clearing all dining assignments...');
      document.querySelectorAll('#diningAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      showNotification('All dining assignments cleared', 'info');
    }

    function clearAllGroundAssignments() {
      console.log('Clearing all ground assignments...');
      document.querySelectorAll('#groundAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      showNotification('All ground assignments cleared', 'info');
    }

    // Generic save functions for other modals
    function saveOfficeAssignments() {
      console.log('Saving office assignments...');
      showNotification('Office assignments saved!', 'success');
    }

    function saveConferenceAssignments() {
      console.log('Saving conference assignments...');
      showNotification('Conference assignments saved!', 'success');
    }

    function saveDiningAssignments() {
      console.log('Saving dining assignments...');
      showNotification('Dining assignments saved!', 'success');
    }

    function saveGroundAssignments() {
      console.log('Saving ground assignments...');
      showNotification('Ground assignments saved!', 'success');
    }

    // Emergency function to fix all modal issues
    window.fixAllModals = function() {
      console.log('🔧 EMERGENCY MODAL FIX - Removing all problematic attributes...');
      
      document.querySelectorAll('.modal').forEach(modal => {
        // Remove aria-hidden
        if (modal.hasAttribute('aria-hidden')) {
          modal.removeAttribute('aria-hidden');
          console.log(`✅ Removed aria-hidden from ${modal.id}`);
        }
        
        // Ensure proper Bootstrap classes
        if (!modal.classList.contains('modal')) {
          modal.classList.add('modal');
        }
        if (!modal.classList.contains('fade')) {
          modal.classList.add('fade');
        }
        
        // Reset display style
        modal.style.display = '';
        
        console.log(`✅ Fixed modal: ${modal.id}`);
      });
      
      // Re-initialize Bootstrap modals
      document.querySelectorAll('.modal').forEach(modal => {
        try {
          const bsModal = new bootstrap.Modal(modal);
          console.log(`✅ Re-initialized Bootstrap modal: ${modal.id}`);
        } catch (e) {
          console.error(`❌ Failed to initialize ${modal.id}:`, e);
        }
      });
      
      console.log('🎉 Emergency modal fix complete! Try clicking View Assignment buttons now.');
    };
    
    console.log('💡 If modals still don\'t work, run fixAllModals() in console');
    
    // Add CSS fixes for modal display issues
    const style = document.createElement('style');
    style.textContent = `
      /* Fix modal display issues - standardize ALL modals with consistent height */
      #kitchenAssignmentModal .modal-body,
      #officeAssignmentModal .modal-body,
      #conferenceAssignmentModal .modal-body,
      #diningAssignmentModal .modal-body,
      #dishwashingAssignmentModal .modal-body,
      #groundAssignmentModal .modal-body,
      #wasteAssignmentModal .modal-body,
      #addMembersModal .modal-body,
      #deleteMembersModal .modal-body,
      #editMembersModal .modal-body,
      #taskChecklistModal .modal-body,
      [id^="studentAssignModal"] .modal-body {
        height: calc(85vh - 120px) !important;
        max-height: calc(85vh - 120px) !important;
        min-height: 480px !important;
        overflow-y: auto !important;
        padding: 20px !important;
        font-size: 14px !important;
        flex: 1 !important;
      }
      
      /* Standardize assignment grids for all modals */
      #kitchenAssignmentModal .assignment-grid,
      #officeAssignmentModal .assignment-grid,
      #conferenceAssignmentModal .assignment-grid,
      #diningAssignmentModal .assignment-grid,
      #dishwashingAssignmentModal .assignment-grid,
      #groundAssignmentModal .assignment-grid,
      #wasteAssignmentModal .assignment-grid {
        display: block !important;
        visibility: visible !important;
        width: 100% !important;
        margin: 0 !important;
      }
      
      .drop-zone {
        min-height: 60px !important;
        border: 2px dashed #ddd !important;
        border-radius: 8px !important;
        padding: 10px !important;
        margin: 2px !important;
        background-color: #f8f9fa !important;
      }
      
      .time-header {
        text-align: center !important;
        font-weight: bold !important;
        padding: 8px !important;
        background-color: #e9ecef !important;
        border-radius: 4px !important;
        margin-bottom: 5px !important;
      }
      
      .task-label {
        text-align: center !important;
        font-weight: bold !important;
        padding: 10px !important;
        background-color: #007bff !important;
        color: white !important;
        border-radius: 4px !important;
        min-height: 60px !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
      }
      
      /* Standardize ALL modal sizes to match Kitchen modal */
      #kitchenAssignmentModal .modal-dialog,
      #officeAssignmentModal .modal-dialog,
      #conferenceAssignmentModal .modal-dialog,
      #diningAssignmentModal .modal-dialog,
      #dishwashingAssignmentModal .modal-dialog,
      #groundAssignmentModal .modal-dialog,
      #wasteAssignmentModal .modal-dialog,
      #addMembersModal .modal-dialog,
      #deleteMembersModal .modal-dialog,
      #editMembersModal .modal-dialog,
      #taskChecklistModal .modal-dialog,
      [id^="studentAssignModal"] .modal-dialog {
        max-width: 1200px !important;
        width: 90vw !important;
        margin: 1.75rem auto !important;
      }
      
      /* Ensure ALL modals have consistent content sizing and height */
      #kitchenAssignmentModal .modal-content,
      #officeAssignmentModal .modal-content,
      #conferenceAssignmentModal .modal-content,
      #diningAssignmentModal .modal-content,
      #dishwashingAssignmentModal .modal-content,
      #groundAssignmentModal .modal-content,
      #wasteAssignmentModal .modal-content,
      #addMembersModal .modal-content,
      #deleteMembersModal .modal-content,
      #editMembersModal .modal-content,
      #taskChecklistModal .modal-content,
      [id^="studentAssignModal"] .modal-content {
        width: 100% !important;
        height: 85vh !important;
        max-height: 85vh !important;
        min-height: 600px !important;
        display: flex !important;
        flex-direction: column !important;
      }
      
      /* Ensure student list is visible */
      .student-list {
        display: block !important;
        visibility: visible !important;
      }
      
      .student-card {
        display: flex !important;
        visibility: visible !important;
      }
      
      /* Standardize ALL modal headers and footers for consistent height */
      #kitchenAssignmentModal .modal-header,
      #officeAssignmentModal .modal-header,
      #conferenceAssignmentModal .modal-header,
      #diningAssignmentModal .modal-header,
      #dishwashingAssignmentModal .modal-header,
      #groundAssignmentModal .modal-header,
      #wasteAssignmentModal .modal-header,
      #addMembersModal .modal-header,
      #deleteMembersModal .modal-header,
      #editMembersModal .modal-header,
      #taskChecklistModal .modal-header,
      [id^="studentAssignModal"] .modal-header {
        height: 60px !important;
        min-height: 60px !important;
        padding: 15px 20px !important;
        flex-shrink: 0 !important;
      }
      
      #kitchenAssignmentModal .modal-footer,
      #officeAssignmentModal .modal-footer,
      #conferenceAssignmentModal .modal-footer,
      #diningAssignmentModal .modal-footer,
      #dishwashingAssignmentModal .modal-footer,
      #groundAssignmentModal .modal-footer,
      #wasteAssignmentModal .modal-footer,
      #addMembersModal .modal-footer,
      #deleteMembersModal .modal-footer,
      #editMembersModal .modal-footer,
      #taskChecklistModal .modal-footer,
      [id^="studentAssignModal"] .modal-footer {
        height: 60px !important;
        min-height: 60px !important;
        padding: 15px 20px !important;
        flex-shrink: 0 !important;
      }
    `;
    document.head.appendChild(style);
    console.log('✅ Added CSS fixes for modal display');
  </script>

  <!-- Dishwashing Assignment Modal -->
  <div class="modal fade" id="dishwashingAssignmentModal" tabindex="-1" aria-labelledby="dishwashingAssignmentModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="dishwashingAssignmentModalLabel">
            <i class="bi bi-droplet me-2"></i>View Assignment
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Instructions:</strong> Drag student names from the bottom panel to assign them to dishwashing tasks. 
            You can assign as many students as needed across all tasks.
          </div>

          <!-- Assignment Grid (TOP) -->
          <div class="row mb-4">
            <div class="col-32">
              <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                  <h6 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Dishwashing Task Assignments</h6>
                  <div>
                    <button class="btn btn-sm btn-light" onclick="if(typeof clearAllDishwashingAssignments === 'function') { clearAllDishwashingAssignments(); } else { console.error('clearAllDishwashingAssignments not found'); }">
                      <i class="bi bi-trash me-1"></i>Clear All
                    </button>
                  </div>
                </div>
                <div class="card-body p-5">
                  <div class="assignment-grid">
                    <!-- Day Headers -->
                    <div class="row mb-3">
                      <div class="col-3">
                        <h6 class="text-center font-weight-bold">Task / Day</h6>
                      </div>
                      <div class="col">
                        <div class="time-header monday">
                          Mon
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header tuesday">
                          Tue
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header wednesday">
                          Wed
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header thursday">
                          Thu
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header friday">
                          Fri
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header saturday">
                          Sat
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header sunday">
                          Sun
                        </div>
                      </div>
                    </div>

                    <!-- Task Rows -->
                    <!-- Dishwashing - Breakfast -->
                    <div class="row mb-5">
                      <div class="col-3">
                        <div class="task-label cook">
                          Dishwashing<br>
                          <small style="font-size: 8px;">Breakfast</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-breakfast" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-breakfast-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-breakfast" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-breakfast-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-breakfast" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-breakfast-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-breakfast" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-breakfast-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-breakfast" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-breakfast-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-breakfast" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-breakfast-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-breakfast" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-breakfast-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Dishwashing - Lunch -->
                    <div class="row mb-5">
                      <div class="col-3">
                        <div class="task-label prep">
                          Dishwashing<br>
                          <small style="font-size: 8px;">Lunch</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-lunch" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-lunch-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-lunch" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-lunch-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-lunch" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-lunch-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-lunch" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-lunch-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-lunch" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-lunch-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-lunch" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-lunch-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-lunch" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-lunch-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Dishwashing - Dinner -->
                    <div class="row mb-5">
                      <div class="col-3">
                        <div class="task-label wash">
                          Dishwashing<br>
                          <small style="font-size: 8px;">Dinner</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dishwashing-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dishwashing-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
<hr style="border: 2px solid #ddd; margin: 20px 0;">

          <!-- Available Students (BOTTOM) -->
          <div class="row">
            <div class="col-32">
              <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #4285f4, #34a853); color: white; padding: 16px 20px; border: none;">
                  <h6 class="mb-0" style="font-weight: 600; font-size: 16px;"><i class="bi bi-people me-2"></i>👥 Available Students</h6>
                </div>
                <div class="card-body p-3" style="max-height: 200px; overflow-x: auto; overflow-y: hidden; background: #f8fbff;">
                  <div id="dishwashingStudentList" class="student-list d-flex flex-nowrap gap-2" style="min-width: max-content;">
                    <!-- All Students Option -->
                    <div class="student-card all-students-option" draggable="true" data-student-id="all" data-student-name="All Students" style="flex: 0 0 auto; width: auto; min-width: 150px; border: 3px solid #28a745; background: linear-gradient(135deg, #28a745, #20c997);">
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2" style="background: #fff; color: #28a745; font-weight: bold; border: 2px solid #fff;">
                          ALL
                        </div>
                        <div class="flex-grow-1">
                          <div class="student-name fw-bold text-white" style="font-size: 0.85rem;">All Students</div>
                          <small class="text-white-50">Assign Everyone</small>
                        </div>
                      </div>
                    </div>
                    @foreach($students as $student)
                      <div class="student-card" draggable="true" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}" style="flex: 0 0 auto; width: auto; min-width: 150px;">
                        <div class="d-flex align-items-center">
                          <div class="student-avatar me-2">
                            @php
                              $nameParts = explode(' ', trim($student->name));
                              $initials = '';
                              if (count($nameParts) >= 2) {
                                $initials = substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1);
                              } else {
                                $initials = substr($student->name, 0, 2);
                              }
                            @endphp
                            {{ $initials }}
                          </div>
                          <div class="student-info">
                            <div class="student-name">{{ $student->name }}</div>
                            <small class="text-muted">ID: {{ $student->id }}</small>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-success" onclick="saveDishwashingAssignments()">
            <i class="bi bi-save me-2"></i>Save Assignments
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Dishwashing Assignment Modal Functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize drag and drop when dishwashing modal opens
      const dishwashingModal = document.getElementById('dishwashingAssignmentModal');
      dishwashingModal.addEventListener('shown.bs.modal', function() {
        if (typeof initializeDishwashingDragAndDrop === 'function') {
          initializeDishwashingDragAndDrop();
        } else {
          console.error('initializeDishwashingDragAndDrop function not found!');
        }
        if (typeof loadExistingDishwashingAssignments === 'function') {
          loadExistingDishwashingAssignments();
        } else {
          console.error('loadExistingDishwashingAssignments function not found!');
        }
      });

      // Clear assignments when modal closes
      dishwashingModal.addEventListener('hidden.bs.modal', function() {
        if (typeof clearAllDishwashingAssignments === 'function') {
          clearAllDishwashingAssignments();
        } else {
          console.error('clearAllDishwashingAssignments function not found!');
        }
      });
    });

    function initializeDishwashingDragAndDrop() {
      // Add drag event listeners to student cards in dishwashing modal
      const studentCards = document.querySelectorAll('#dishwashingStudentList .student-card');
      studentCards.forEach(card => {
        card.addEventListener('dragstart', handleDishwashingDragStart);
        card.addEventListener('dragend', handleDishwashingDragEnd);
      });

      // Add drop event listeners to drop zones in dishwashing modal
      const dropZones = document.querySelectorAll('#dishwashingAssignmentModal .drop-zone');
      dropZones.forEach(zone => {
        zone.addEventListener('dragover', handleDishwashingDragOver);
        zone.addEventListener('dragenter', handleDishwashingDragEnter);
        zone.addEventListener('dragleave', handleDishwashingDragLeave);
        zone.addEventListener('drop', handleDishwashingDrop);
      });
    }

    let dishwashingDraggedElement = null;

    function handleDishwashingDragStart(e) {
      dishwashingDraggedElement = this;
      const studentData = {
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName
      };
      e.dataTransfer.setData('text/plain', JSON.stringify(studentData));
      this.style.opacity = '0.5';
    }

    function handleDishwashingDragEnd(e) {
      this.style.opacity = '1';
      dishwashingDraggedElement = null;
    }

    function handleDishwashingDragOver(e) {
      e.preventDefault();
    }

    function handleDishwashingDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleDishwashingDragLeave(e) {
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
      }
    }

    function handleDishwashingDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      if (!dishwashingDraggedElement) return;
      
      // Get drop zone info
      const task = this.dataset.task;
      const time = this.dataset.time;
      const maxStudents = parseInt(this.dataset.max);
      
      // Get student data
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      
      // Handle "All Students" option
      if (studentData.studentId === 'all') {
        assignAllDishwashingStudentsToSlot(this, task, time, maxStudents);
        return;
      }
      
      // Check total assignments limit (99 students max)
      const totalAssigned = getTotalDishwashingAssignedStudents();
      if (totalAssigned >= 99) {
        showNotification('Maximum 99 students can be assigned across all dishwashing tasks', 'warning');
        return;
      }
      
      // Check if drop zone is full
      const assignedContainer = this.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for ${task} during ${time}`, 'warning');
        return;
      }
      
      // Check if student is already assigned to this slot
      const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentData.studentId}"]`);
      if (existingAssignment) {
        showNotification(`${studentData.studentName} is already assigned to this slot`, 'info');
        return;
      }
      
      // Create assigned student element
      const assignedStudent = createDishwashingAssignedStudentElement(studentData, task, time);
      assignedContainer.appendChild(assignedStudent);
      
      // Hide student from available list
      hideDishwashingStudentFromAvailableList(studentData.studentId);
      
      // Update drop zone status
      updateDishwashingDropZoneStatus(this, currentCount + 1, maxStudents);
      
      showNotification(`${studentData.studentName} assigned to ${task} for ${time}`, 'success');
    }

    function createDishwashingAssignedStudentElement(studentData, task, time) {
      const div = document.createElement('div');
      div.className = 'assigned-student';
      div.dataset.studentId = studentData.studentId;
      div.dataset.task = task;
      div.dataset.time = time;
      
      div.innerHTML = `
        <span class="student-name">${studentData.studentName}</span>
        <button class="remove-btn" onclick="removeDishwashingAssignment(this)" title="Remove assignment">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      return div;
    }

    function removeDishwashingAssignment(button) {
      const assignedStudent = button.closest('.assigned-student');
      const dropZone = assignedStudent.closest('.drop-zone');
      const studentName = assignedStudent.querySelector('.student-name').textContent;
      const studentId = assignedStudent.dataset.studentId;
      
      // Remove the assignment
      assignedStudent.remove();
      
      // Show student back in available list
      showDishwashingStudentInAvailableList(studentId);
      
      // Update drop zone status
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      const maxStudents = parseInt(dropZone.dataset.max);
      updateDishwashingDropZoneStatus(dropZone, currentCount, maxStudents);
      
      showNotification(`${studentName} removed from dishwashing assignment`, 'info');
    }

    function updateDishwashingDropZoneStatus(dropZone, currentCount, maxStudents) {
      if (currentCount >= maxStudents) {
        dropZone.classList.add('full');
      } else {
        dropZone.classList.remove('full');
      }
      
      const header = dropZone.querySelector('.drop-zone-header small');
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    function getTotalDishwashingAssignedStudents() {
      const assignedStudents = document.querySelectorAll('#dishwashingAssignmentModal .assigned-students .assigned-student');
      return assignedStudents.length;
    }

    function hideDishwashingStudentFromAvailableList(studentId) {
      const studentCard = document.querySelector(`#dishwashingStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'none';
      }
    }

    function showDishwashingStudentInAvailableList(studentId) {
      const studentCard = document.querySelector(`#dishwashingStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'flex';
      }
    }

    // Assign all available students to a specific dishwashing slot
    function assignAllDishwashingStudentsToSlot(dropZone, task, time, maxStudents) {
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      // Get all available students (not hidden)
      const availableStudents = document.querySelectorAll('#dishwashingStudentList .student-card:not(.all-students-option)');
      const visibleStudents = Array.from(availableStudents).filter(card => 
        card.style.display !== 'none' && card.dataset.studentId !== 'all'
      );
      
      let assignedCount = 0;
      let totalAssigned = getTotalDishwashingAssignedStudents();
      
      for (const studentCard of visibleStudents) {
        // Check if we've reached the slot limit
        if (currentCount + assignedCount >= maxStudents) {
          break;
        }
        
        // Check if we've reached the global limit
        if (totalAssigned >= 99) {
          break;
        }
        
        const studentId = studentCard.dataset.studentId;
        const studentName = studentCard.dataset.studentName;
        
        // Check if student is already assigned to this slot
        const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentId}"]`);
        if (existingAssignment) {
          continue;
        }
        
        // Create assigned student element
        const assignedStudent = createDishwashingAssignedStudentElement({
          studentId: studentId,
          studentName: studentName
        }, task, time);
        
        assignedContainer.appendChild(assignedStudent);
        
        // Hide student from available list
        hideDishwashingStudentFromAvailableList(studentId);
        
        assignedCount++;
        totalAssigned++;
      }
      
      // Update drop zone status
      updateDishwashingDropZoneStatus(dropZone, currentCount + assignedCount, maxStudents);
      
      if (assignedCount > 0) {
        showNotification(`${assignedCount} students assigned to ${task} for ${time}`, 'success');
      } else {
        showNotification('No available students to assign', 'info');
      }
    }

    function clearAllDishwashingAssignments() {
      document.querySelectorAll('#dishwashingAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      
      // Reset all drop zone counters
      document.querySelectorAll('#dishwashingAssignmentModal .drop-zone').forEach(zone => {
        const maxStudents = parseInt(zone.dataset.max);
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `0/${maxStudents} students`;
        }
        zone.classList.remove('full');
      });

      // Show all students back in available list
      document.querySelectorAll('#dishwashingStudentList .student-card').forEach(card => {
        card.style.display = 'flex';
      });
    }

    function loadExistingDishwashingAssignments() {
      fetch('/get-dishwashing-assignments')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.assignments) {
            // Clear existing assignments
            document.querySelectorAll('#dishwashingAssignmentModal .assigned-students').forEach(container => {
              container.innerHTML = '';
            });
            
            // Load assignments into the grid
            data.assignments.forEach(assignment => {
              const targetContainer = document.getElementById(`${assignment.task_type}-${assignment.time_slot}`);
              if (targetContainer) {
                const studentElement = createDishwashingAssignedStudentElement({
                  studentId: assignment.student_id,
                  studentName: assignment.student_name
                }, assignment.task_type, assignment.time_slot);
                targetContainer.appendChild(studentElement);
              }
            });
            
            // Update counters
            updateDishwashingCounters();
          }
        })
        .catch(error => {
          console.error('Error loading dishwashing assignments:', error);
        });
    }
    
    // Update dishwashing assignment counters
    function updateDishwashingCounters() {
      document.querySelectorAll('#dishwashingAssignmentModal .drop-zone').forEach(zone => {
        const assignedContainer = zone.querySelector('.assigned-students');
        const currentCount = assignedContainer ? assignedContainer.children.length : 0;
        const maxStudents = parseInt(zone.dataset.max) || 99;
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `${currentCount}/${maxStudents} students`;
        }
      });
    }

    function saveDishwashingAssignments() {
      // Collect all assignments
      const assignments = [];
      
      document.querySelectorAll('#dishwashingAssignmentModal .assigned-student').forEach(student => {
        assignments.push({
          student_id: student.dataset.studentId,
          task: student.dataset.task,
          time: student.dataset.time,
          student_name: student.querySelector('.student-name').textContent
        });
      });

      if (assignments.length === 0) {
        showNotification('No dishwashing assignments to save', 'info');
        return;
      }

      // Send to server via AJAX
      fetch('/save-dishwashing-assignments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ assignments: assignments })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          // Close modal after successful save
          const modal = bootstrap.Modal.getInstance(document.getElementById('dishwashingAssignmentModal'));
          if (modal) modal.hide();
          // Optionally reload the page to show updated assignments
          setTimeout(() => window.location.reload(), 1500);
        } else {
          showNotification(data.message || 'Error saving assignments', 'error');
        }
      })
      .catch(error => {
        console.error('Error saving dishwashing assignments:', error);
        showNotification('Error saving assignments. Please try again.', 'error');
      });
    }
  </script>

  <!-- Dining Assignment Modal -->
  <div class="modal fade" id="diningAssignmentModal" tabindex="-1" aria-labelledby="diningAssignmentModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
      <div class="modal-content">
        <div class="modal-header bg-success text-white">
          <h5 class="modal-title" id="diningAssignmentModalLabel">
            <i class="bi bi-cup-straw me-2"></i>View Assignment
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Instructions:</strong> Drag student names from the bottom panel to assign them to dining service tasks. 
            You can assign as many students as needed across all tasks.
          </div>

          <!-- Assignment Grid (TOP) -->
          <div class="row mb-4">
            <div class="col-32">
              <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                  <h6 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Dining Service Task Assignments</h6>
                  <div>
                    <button class="btn btn-sm btn-light" onclick="clearAllDiningAssignments()">
                      <i class="bi bi-trash me-1"></i>Clear All
                    </button>
                  </div>
                </div>
                <div class="card-body p-3">
                  <div class="assignment-grid">
                    <!-- Day Headers -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <h6 class="text-center font-weight-bold">Task / Day</h6>
                      </div>
                      <div class="col">
                        <div class="time-header monday">
                          Mon
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header tuesday">
                          Tue
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header wednesday">
                          Wed
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header thursday">
                          Thu
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header friday">
                          Fri
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header saturday">
                          Sat
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header sunday">
                          Sun
                        </div>
                      </div>
                    </div>

                    <!-- Task Rows -->
                    <!-- Dining Service - Breakfast -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label arrange-tables">
                          Dining Service<br>
                          <small style="font-size: 8px;">Breakfast</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-breakfast" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-breakfast-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-breakfast" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-breakfast-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-breakfast" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-breakfast-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-breakfast" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-breakfast-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-breakfast" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-breakfast-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-breakfast" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-breakfast-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-breakfast" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-breakfast-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Dining Service - Lunch -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label arrange-tables">
                          Dining Service<br>
                          <small style="font-size: 8px;">Lunch</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-lunch" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-lunch-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-lunch" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-lunch-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-lunch" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-lunch-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-lunch" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-lunch-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-lunch" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-lunch-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-lunch" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-lunch-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-lunch" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-lunch-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Dining Service - Dinner -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label arrange-tables">
                          Dining Service<br>
                          <small style="font-size: 8px;">Dinner</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="dining-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="dining-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
<hr style="border: 2px solid #ddd; margin: 20px 0;">

          <!-- Available Students (BOTTOM) -->
          <div class="row">
            <div class="col-32">
              <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #4285f4, #34a853); color: white; padding: 16px 20px; border: none;">
                  <h6 class="mb-0" style="font-weight: 600; font-size: 16px;"><i class="bi bi-people me-2"></i>👥 Available Students</h6>
                </div>
                <div class="card-body p-3" style="max-height: 200px; overflow-x: auto; overflow-y: hidden; background: #f8fbff;">
                  <div id="diningStudentList" class="student-list d-flex flex-nowrap gap-2" style="min-width: max-content;">
                    <!-- All Students Option -->
                    <div class="student-card all-students-option" draggable="true" data-student-id="all" data-student-name="All Students" style="flex: 0 0 auto; width: auto; min-width: 150px; border: 3px solid #28a745; background: linear-gradient(135deg, #28a745, #20c997);">
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2" style="background: #fff; color: #28a745; font-weight: bold; border: 2px solid #fff;">
                          ALL
                        </div>
                        <div class="flex-grow-1">
                          <div class="student-name fw-bold text-white" style="font-size: 0.85rem;">All Students</div>
                          <small class="text-white-50">Assign Everyone</small>
                        </div>
                      </div>
                    </div>
                    @foreach($students as $student)
                      <div class="student-card" draggable="true" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}" style="flex: 0 0 auto; width: auto; min-width: 150px;">
                        <div class="d-flex align-items-center">
                          <div class="student-avatar me-2">
                            @php
                              $nameParts = explode(' ', trim($student->name));
                              $initials = '';
                              if (count($nameParts) >= 2) {
                                $initials = substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1);
                              } else {
                                $initials = substr($student->name, 0, 2);
                              }
                            @endphp
                            {{ $initials }}
                          </div>
                          <div class="student-info">
                            <div class="student-name">{{ $student->name }}</div>
                            <small class="text-muted">ID: {{ $student->id }}</small>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-success" onclick="saveDiningAssignments()">
            <i class="bi bi-save me-2"></i>Save Assignments
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Dining Assignment Modal Functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize drag and drop when dining modal opens
      const diningModal = document.getElementById('diningAssignmentModal');
      diningModal.addEventListener('shown.bs.modal', function() {
        console.log('Dining modal opened!');
        // Add small delay to ensure modal is fully rendered
        setTimeout(() => {
          console.log('Initializing dining drag and drop after delay...');
          initializeDiningDragAndDrop();
          loadExistingDiningAssignments();
        }, 500);
      });

      // Clear assignments when modal closes
      diningModal.addEventListener('hidden.bs.modal', function() {
        clearAllDiningAssignments();
      });
    });

    function initializeDiningDragAndDrop() {
      console.log('Initializing Dining drag and drop...');
      
      // Wait a bit more for DOM to be ready
      setTimeout(() => {
        // Try multiple selectors to find student cards
        let studentCards = document.querySelectorAll('#diningStudentList .student-card');
        
        if (studentCards.length === 0) {
          console.log('No cards found with #diningStudentList .student-card, trying alternative selectors...');
          studentCards = document.querySelectorAll('#diningAssignmentModal .student-card');
        }
        
        console.log('Found', studentCards.length, 'student cards for dining modal');
        
        if (studentCards.length === 0) {
          console.error('ERROR: No student cards found! Check if modal is properly loaded.');
          return;
        }
        
        studentCards.forEach((card, index) => {
          // Ensure draggable attribute is set
          card.setAttribute('draggable', 'true');
          card.style.cursor = 'grab';
          
          // Remove existing listeners to avoid duplicates
          card.removeEventListener('dragstart', handleDiningDragStart);
          card.removeEventListener('dragend', handleDiningDragEnd);
          
          // Add new listeners
          card.addEventListener('dragstart', handleDiningDragStart);
          card.addEventListener('dragend', handleDiningDragEnd);
          
          // Add visual feedback
          card.addEventListener('mouseenter', function() {
            this.style.cursor = 'grab';
            this.style.opacity = '0.9';
          });
          
          card.addEventListener('mouseleave', function() {
            this.style.opacity = '1';
          });
          
          console.log('Added drag listeners to dining card', index + 1, ':', card.dataset.studentName || card.querySelector('.student-name')?.textContent || 'Unknown');
        });

        // Add drop event listeners to drop zones in dining modal
        const dropZones = document.querySelectorAll('#diningAssignmentModal .drop-zone');
        console.log('Found', dropZones.length, 'drop zones for dining modal');
        
        dropZones.forEach((zone, index) => {
          // Remove existing listeners to avoid duplicates
          zone.removeEventListener('dragover', handleDiningDragOver);
          zone.removeEventListener('dragenter', handleDiningDragEnter);
          zone.removeEventListener('dragleave', handleDiningDragLeave);
          zone.removeEventListener('drop', handleDiningDrop);
          
          // Add new listeners
          zone.addEventListener('dragover', handleDiningDragOver);
          zone.addEventListener('dragenter', handleDiningDragEnter);
          zone.addEventListener('dragleave', handleDiningDragLeave);
          zone.addEventListener('drop', handleDiningDrop);
          
          console.log('Added drop listeners to dining zone', index + 1);
        });
        
        console.log('Dining drag and drop initialization complete!');
      }, 100);
      
      // Test function to verify drag functionality
      window.testDiningDrag = function() {
        const cards = document.querySelectorAll('#diningStudentList .student-card');
        console.log('Testing Dining drag functionality:');
        console.log('- Found', cards.length, 'student cards');
        cards.forEach((card, i) => {
          console.log(`- Card ${i+1}: draggable=${card.draggable}, id=${card.dataset.studentId}, name=${card.dataset.studentName}`);
        });
        const zones = document.querySelectorAll('#diningAssignmentModal .drop-zone');
        console.log('- Found', zones.length, 'drop zones');
        console.log('Test complete! Try dragging a student now.');
      };
      
      // Force re-initialize function
      window.forceDiningDragInit = function() {
        console.log('Force re-initializing Dining drag and drop...');
        initializeDiningDragAndDrop();
        console.log('Dining drag and drop force re-initialized!');
      };
    }

    let diningDraggedElement = null;

    function handleDiningDragStart(e) {
      diningDraggedElement = this;
      const studentData = {
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName
      };
      e.dataTransfer.setData('text/plain', JSON.stringify(studentData));
      this.style.opacity = '0.5';
    }

    function handleDiningDragEnd(e) {
      this.style.opacity = '1';
      diningDraggedElement = null;
    }

    function handleDiningDragOver(e) {
      e.preventDefault();
    }

    function handleDiningDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleDiningDragLeave(e) {
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
      }
    }

    function handleDiningDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      if (!diningDraggedElement) return;
      
      // Get drop zone info
      const task = this.dataset.task;
      const time = this.dataset.time;
      const maxStudents = parseInt(this.dataset.max);
      
      // Get student data
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      
      // Handle "All Students" option
      if (studentData.studentId === 'all') {
        assignAllDiningStudentsToSlot(this, task, time, maxStudents);
        return;
      }
      
      // Check total assignments limit (99 students max)
      const totalAssigned = getTotalDiningAssignedStudents();
      if (totalAssigned >= 99) {
        showNotification('Maximum 99 students can be assigned across all dining tasks', 'warning');
        return;
      }
      
      // Check if drop zone is full
      const assignedContainer = this.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for ${task} during ${time}`, 'warning');
        return;
      }
      
      // Check if student is already assigned to this slot
      const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentData.studentId}"]`);
      if (existingAssignment) {
        showNotification(`${studentData.studentName} is already assigned to this slot`, 'info');
        return;
      }
      
      // Create assigned student element
      const assignedStudent = createDiningAssignedStudentElement(studentData, task, time);
      assignedContainer.appendChild(assignedStudent);
      
      // Hide student from available list
      hideDiningStudentFromAvailableList(studentData.studentId);
      
      // Update drop zone status
      updateDiningDropZoneStatus(this, currentCount + 1, maxStudents);
      
      showNotification(`${studentData.studentName} assigned to ${task} for ${time}`, 'success');
    }

    function createDiningAssignedStudentElement(studentData, task, time) {
      const div = document.createElement('div');
      div.className = 'assigned-student';
      div.dataset.studentId = studentData.studentId;
      div.dataset.task = task;
      div.dataset.time = time;
      
      div.innerHTML = `
        <span class="student-name">${studentData.studentName}</span>
        <button class="remove-btn" onclick="removeDiningAssignment(this)" title="Remove assignment">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      return div;
    }

    function removeDiningAssignment(button) {
      const assignedStudent = button.closest('.assigned-student');
      const dropZone = assignedStudent.closest('.drop-zone');
      const studentName = assignedStudent.querySelector('.student-name').textContent;
      const studentId = assignedStudent.dataset.studentId;
      
      // Remove the assignment
      assignedStudent.remove();
      
      // Show student back in available list
      showDiningStudentInAvailableList(studentId);
      
      // Update drop zone status
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      const maxStudents = parseInt(dropZone.dataset.max);
      updateDiningDropZoneStatus(dropZone, currentCount, maxStudents);
      
      showNotification(`${studentName} removed from dining assignment`, 'info');
    }

    function updateDiningDropZoneStatus(dropZone, currentCount, maxStudents) {
      if (currentCount >= maxStudents) {
        dropZone.classList.add('full');
      } else {
        dropZone.classList.remove('full');
      }
      
      const header = dropZone.querySelector('.drop-zone-header small');
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    function getTotalDiningAssignedStudents() {
      const assignedStudents = document.querySelectorAll('#diningAssignmentModal .assigned-students .assigned-student');
      return assignedStudents.length;
    }

    function hideDiningStudentFromAvailableList(studentId) {
      const studentCard = document.querySelector(`#diningStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'none';
      }
    }

    function showDiningStudentInAvailableList(studentId) {
      const studentCard = document.querySelector(`#diningStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'flex';
      }
    }

    function clearAllDiningAssignments() {
      document.querySelectorAll('#diningAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      
      // Reset all drop zone counters
      document.querySelectorAll('#diningAssignmentModal .drop-zone').forEach(zone => {
        const maxStudents = parseInt(zone.dataset.max);
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `0/${maxStudents} students`;
        }
        zone.classList.remove('full');
      });

      // Show all students back in available list
      document.querySelectorAll('#diningStudentList .student-card').forEach(card => {
        card.style.display = 'flex';
      });
    }

    function loadExistingDiningAssignments() {
      fetch('/get-dining-assignments')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.assignments) {
            // Clear existing assignments
            document.querySelectorAll('#diningAssignmentModal .assigned-students').forEach(container => {
              container.innerHTML = '';
            });
            
            // Load assignments into the grid
            data.assignments.forEach(assignment => {
              const targetContainer = document.getElementById(`${assignment.task_type}-${assignment.time_slot}`);
              if (targetContainer) {
                const studentElement = createDiningAssignedStudentElement({
                  studentId: assignment.student_id,
                  studentName: assignment.student_name
                }, assignment.task_type, assignment.time_slot);
                targetContainer.appendChild(studentElement);
              }
            });
            
            // Update counters
            updateDiningCounters();
          }
        })
        .catch(error => {
          console.error('Error loading dining assignments:', error);
        });
    }
    
    // Update dining assignment counters
    function updateDiningCounters() {
      document.querySelectorAll('#diningAssignmentModal .drop-zone').forEach(zone => {
        const assignedContainer = zone.querySelector('.assigned-students');
        const currentCount = assignedContainer ? assignedContainer.children.length : 0;
        const maxStudents = parseInt(zone.dataset.max) || 99;
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `${currentCount}/${maxStudents} students`;
        }
      });
    }

    // Duplicate loadExistingKitchenAssignments function removed
    
    // Initialize kitchen drag and drop functionality
    function initializeKitchenDragAndDrop() {
      console.log('Initializing Kitchen drag and drop...');
      
      // Wait a bit more for DOM to be ready
      setTimeout(() => {
        // Try multiple selectors to find student cards
        let studentCards = document.querySelectorAll('#modalStudentList .student-card');
        
        if (studentCards.length === 0) {
          console.log('No cards found with #modalStudentList .student-card, trying alternative selectors...');
          studentCards = document.querySelectorAll('#kitchenAssignmentModal .student-card');
        }
        
        console.log('Found', studentCards.length, 'student cards for kitchen modal');
        
        if (studentCards.length === 0) {
          console.error('ERROR: No student cards found! Check if modal is properly loaded.');
          return;
        }
        
        studentCards.forEach((card, index) => {
          // Ensure draggable attribute is set
          card.setAttribute('draggable', 'true');
          card.style.cursor = 'grab';
          
          // Remove existing listeners to avoid duplicates
          card.removeEventListener('dragstart', handleKitchenDragStart);
          card.removeEventListener('dragend', handleKitchenDragEnd);
          
          // Add new listeners
          card.addEventListener('dragstart', handleKitchenDragStart);
          card.addEventListener('dragend', handleKitchenDragEnd);
          
          // Add visual feedback
          card.addEventListener('mouseenter', function() {
            this.style.cursor = 'grab';
            this.style.opacity = '0.9';
          });
          
          card.addEventListener('mouseleave', function() {
            this.style.opacity = '1';
          });
          
          console.log('Added drag listeners to card', index + 1, ':', card.dataset.studentName || card.querySelector('.student-name')?.textContent || 'Unknown');
        });

        // Add drop event listeners to drop zones in kitchen modal
        const dropZones = document.querySelectorAll('#kitchenAssignmentModal .drop-zone');
        console.log('Found', dropZones.length, 'drop zones for kitchen modal');
        
        dropZones.forEach((zone, index) => {
          // Remove existing listeners to avoid duplicates
          zone.removeEventListener('dragover', handleKitchenDragOver);
          zone.removeEventListener('dragenter', handleKitchenDragEnter);
          zone.removeEventListener('dragleave', handleKitchenDragLeave);
          zone.removeEventListener('drop', handleKitchenDrop);
          
          // Add new listeners
          zone.addEventListener('dragover', handleKitchenDragOver);
          zone.addEventListener('dragenter', handleKitchenDragEnter);
          zone.addEventListener('dragleave', handleKitchenDragLeave);
          zone.addEventListener('drop', handleKitchenDrop);
          
          console.log('Added drop listeners to zone', index + 1);
        });
        
        console.log('Kitchen drag and drop initialization complete!');
      }, 100);
      
      // Test function to verify drag functionality
      window.testKitchenDrag = function() {
        const cards = document.querySelectorAll('#modalStudentList .student-card');
        console.log('Testing Kitchen drag functionality:');
        console.log('- Found', cards.length, 'student cards');
        cards.forEach((card, i) => {
          console.log(`- Card ${i+1}: draggable=${card.draggable}, id=${card.dataset.studentId}, name=${card.dataset.studentName}`);
        });
        const zones = document.querySelectorAll('#kitchenAssignmentModal .drop-zone');
        console.log('- Found', zones.length, 'drop zones');
        console.log('Test complete! Try dragging a student now.');
      };
      
      // Force re-initialize function
      window.forceKitchenDragInit = function() {
        console.log('Force re-initializing Kitchen drag and drop...');
        initializeKitchenDragAndDrop();
        console.log('Kitchen drag and drop force re-initialized!');
      };
    }

    let kitchenDraggedElement = null;

    function handleKitchenDragStart(e) {
      kitchenDraggedElement = this;
      const studentData = {
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName || this.querySelector('.student-name')?.textContent || this.textContent.trim()
      };
      e.dataTransfer.setData('text/plain', JSON.stringify(studentData));
      this.style.opacity = '0.5';
    }

    function handleKitchenDragEnd(e) {
      this.style.opacity = '1';
      kitchenDraggedElement = null;
    }

    function handleKitchenDragOver(e) {
      e.preventDefault();
    }

    function handleKitchenDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleKitchenDragLeave(e) {
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
      }
    }

    function handleKitchenDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      if (!kitchenDraggedElement) return;
      
      // Get drop zone info
      const task = this.dataset.task;
      const time = this.dataset.time;
      const maxStudents = parseInt(this.dataset.max) || 99;
      
      // Get student data
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      
      // Check if drop zone is full
      const assignedContainer = this.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for ${task} during ${time}`, 'warning');
        return;
      }
      
      // Check if student is already assigned to this slot
      const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentData.studentId}"]`);
      if (existingAssignment) {
        showNotification(`${studentData.studentName} is already assigned to this slot`, 'info');
        return;
      }
      
      // Create assigned student element
      const assignedStudent = createKitchenAssignedStudentElement(studentData, task, time);
      assignedContainer.appendChild(assignedStudent);
      
      // Hide student from available list
      hideKitchenStudentFromAvailableList(studentData.studentId);
      
      // Update drop zone status
      updateKitchenDropZoneStatus(this, currentCount + 1, maxStudents);
      
      showNotification(`${studentData.studentName} assigned to ${task} for ${time}`, 'success');
    }

    function createKitchenAssignedStudentElement(studentData, task, time) {
      const div = document.createElement('div');
      div.className = 'assigned-student';
      div.dataset.studentId = studentData.studentId;
      div.dataset.task = task;
      div.dataset.time = time;
      
      div.innerHTML = `
        <span class="student-name">${studentData.studentName}</span>
        <button class="remove-btn" onclick="removeKitchenAssignment(this)" title="Remove assignment">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      return div;
    }

    function removeKitchenAssignment(button) {
      const assignedStudent = button.closest('.assigned-student');
      const dropZone = assignedStudent.closest('.drop-zone');
      const studentName = assignedStudent.querySelector('.student-name').textContent;
      const studentId = assignedStudent.dataset.studentId;
      
      // Remove the assignment
      assignedStudent.remove();
      
      // Show student back in available list
      showKitchenStudentInAvailableList(studentId);
      
      // Update drop zone status
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      const maxStudents = parseInt(dropZone.dataset.max) || 99;
      updateKitchenDropZoneStatus(dropZone, currentCount, maxStudents);
      
      showNotification(`${studentName} removed from kitchen assignment`, 'info');
    }

    function updateKitchenDropZoneStatus(dropZone, currentCount, maxStudents) {
      if (currentCount >= maxStudents) {
        dropZone.classList.add('full');
      } else {
        dropZone.classList.remove('full');
      }
      
      const header = dropZone.querySelector('.drop-zone-header small');
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    function hideKitchenStudentFromAvailableList(studentId) {
      const studentCard = document.querySelector(`#kitchenStudentList .student-card[data-student-id="${studentId}"], #modalStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'none';
      }
    }

    function showKitchenStudentInAvailableList(studentId) {
      const studentCard = document.querySelector(`#kitchenStudentList .student-card[data-student-id="${studentId}"], #modalStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'flex';
      }
    }

    // Clear all kitchen assignments
    function clearAllKitchenAssignments() {
      document.querySelectorAll('#kitchenAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      
      // Reset all drop zone counters
      document.querySelectorAll('#kitchenAssignmentModal .drop-zone').forEach(zone => {
        const maxStudents = parseInt(zone.dataset.max) || 99;
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `0/${maxStudents} students`;
        }
        zone.classList.remove('full');
      });

      // Show all students back in available list
      document.querySelectorAll('#modalStudentList .student-card').forEach(card => {
        card.style.display = 'flex';
      });
    }

    // Update kitchen assignment counters
    function updateKitchenCounters() {
      document.querySelectorAll('#kitchenAssignmentModal .drop-zone').forEach(zone => {
        const assignedContainer = zone.querySelector('.assigned-students');
        const currentCount = assignedContainer ? assignedContainer.children.length : 0;
        const maxStudents = parseInt(zone.dataset.max) || 99;
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `${currentCount}/${maxStudents} students`;
        }
      });
    }
    // Save kitchen assignments
    function saveKitchenAssignments() {
      console.log('Saving kitchen assignments...');
      
      const assignments = [];
      
      // Collect all assigned students from kitchen modal
      document.querySelectorAll('#kitchenAssignmentModal .assigned-students .assigned-student').forEach(student => {
        const studentId = student.dataset.studentId;
        const studentName = student.querySelector('.student-name').textContent;
        const task = student.dataset.task;
        const time = student.dataset.time;
        
        assignments.push({
          student_id: studentId,
          student_name: studentName,
          task_type: task,
          time_slot: time,
          is_coordinator: false
        });
      });
      
      console.log('Kitchen assignments to save:', assignments);
      
      if (assignments.length === 0) {
        showNotification('No assignments to save', 'info');
        return;
      }
      
      // Send to server via AJAX
      fetch('/save-kitchen-assignments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
          assignments: assignments,
          category: 'Kitchen'
        })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification('Kitchen assignments saved successfully!', 'success');
          // Close modal
          const modal = bootstrap.Modal.getInstance(document.getElementById('kitchenAssignmentModal'));
          modal.hide();
        } else {
          showNotification('Error saving assignments: ' + data.message, 'error');
        }
      })
      .catch(error => {
        console.error('Error saving kitchen assignments:', error);
        showNotification('Error saving assignments', 'error');
      });
    }
    
    function saveDiningAssignments() {
      // Collect all assignments
      const assignments = [];
      
      document.querySelectorAll('#diningAssignmentModal .assigned-student').forEach(student => {
        assignments.push({
          student_id: student.dataset.studentId,
          task: student.dataset.task,
          time: student.dataset.time,
          student_name: student.querySelector('.student-name').textContent
        });
      });
      });

      if (assignments.length === 0) {
        showNotification('No dining assignments to save', 'info');
        return;
      }

      // Send to server via AJAX
      fetch('/save-dining-assignments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ assignments: assignments })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          // Close modal after successful save
          const modal = bootstrap.Modal.getInstance(document.getElementById('diningAssignmentModal'));
          if (modal) modal.hide();
          setTimeout(() => window.location.reload(), 1500);
        } else {
          showNotification(data.message || 'Error saving assignments', 'error');
        }
      })
      .catch(error => {
        console.error('Error saving dining assignments:', error);
        showNotification('Error saving assignments. Please try again.', 'error');
      });
      
      // Close modal after successful save
      const modal = bootstrap.Modal.getInstance(document.getElementById('diningAssignmentModal'));
      if (modal) modal.hide();
    }
  </script>

  <!-- Office Assignment Modal -->
  <div class="modal fade" id="officeAssignmentModal" tabindex="-1" aria-labelledby="officeAssignmentModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
      <div class="modal-content">
        <div class="modal-header bg-warning text-dark">
          <h5 class="modal-title" id="officeAssignmentModalLabel">
            <i class="bi bi-building me-2"></i>View Assignment
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Instructions:</strong> Drag student names from the bottom panel to assign them to office cleaning tasks. 
            You can assign as many students as needed across all tasks.
          </div>

          <!-- Assignment Grid (TOP) -->
          <div class="row mb-4">
            <div class="col-32">
              <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                  <h6 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Office Cleaning Task Assignments</h6>
                  <div>
                    <button class="btn btn-sm btn-light" onclick="clearAllOfficeAssignments()">
                      <i class="bi bi-trash me-1"></i>Clear All
                    </button>
                  </div>
                </div>
                <div class="card-body p-3">
                  <div class="assignment-grid">
                    <!-- Day Headers -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <h6 class="text-center font-weight-bold">Task / Day</h6>
                      </div>
                      <div class="col">
                        <div class="time-header monday">
                          Mon
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header tuesday">
                          Tue
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header wednesday">
                          Wed
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header thursday">
                          Thu
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header friday">
                          Fri
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header saturday">
                          Sat
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header sunday">
                          Sun
                        </div>
                      </div>
                    </div>

                    <!-- Task Rows -->
                    <!-- Office Cleaning - Morning -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label sweep-mop">
                          Office Cleaning<br>
                          <small style="font-size: 8px;">Morning</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-morning" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-morning-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-morning" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-morning-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-morning" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-morning-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-morning" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-morning-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-morning" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-morning-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-morning" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-morning-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-morning" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-morning-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Office Cleaning - Afternoon -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label clean-glass">
                          Office Cleaning<br>
                          <small style="font-size: 8px;">Afternoon</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-afternoon" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-afternoon-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-afternoon" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-afternoon-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-afternoon" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-afternoon-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-afternoon" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-afternoon-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-afternoon" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-afternoon-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-afternoon" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-afternoon-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-afternoon" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-afternoon-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Office Cleaning - Evening -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label clean-bathroom">
                          Office Cleaning<br>
                          <small style="font-size: 8px;">Evening</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-evening" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-evening-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-evening" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-evening-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-evening" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-evening-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-evening" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-evening-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-evening" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-evening-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-evening" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-evening-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="office-evening" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="office-evening-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
<hr style="border: 2px solid #ddd; margin: 20px 0;">

          <!-- Available Students (BOTTOM) -->
          <div class="row">
            <div class="col-32">
              <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #4285f4, #34a853); color: white; padding: 16px 20px; border: none;">
                  <h6 class="mb-0" style="font-weight: 600; font-size: 16px;"><i class="bi bi-people me-2"></i>👥 Available Students</h6>
                </div>
                <div class="card-body p-3" style="max-height: 200px; overflow-x: auto; overflow-y: hidden; background: #f8fbff;">
                  <div id="officeStudentList" class="student-list d-flex flex-nowrap gap-2" style="min-width: max-content;">
                    <!-- All Students Option -->
                    <div class="student-card all-students-option" draggable="true" data-student-id="all" data-student-name="All Students" style="flex: 0 0 auto; width: auto; min-width: 150px; border: 3px solid #28a745; background: linear-gradient(135deg, #28a745, #20c997);">
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2" style="background: #fff; color: #28a745; font-weight: bold; border: 2px solid #fff;">
                          ALL
                        </div>
                        <div class="flex-grow-1">
                          <div class="student-name fw-bold text-white" style="font-size: 0.85rem;">All Students</div>
                          <small class="text-white-50">Assign Everyone</small>
                        </div>
                      </div>
                    </div>
                    @foreach($students as $student)
                      <div class="student-card" draggable="true" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}" style="flex: 0 0 auto; width: auto; min-width: 150px;">
                        <div class="d-flex align-items-center">
                          <div class="student-avatar me-2">
                            @php
                              $nameParts = explode(' ', trim($student->name));
                              $initials = '';
                              if (count($nameParts) >= 2) {
                                $initials = substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1);
                              } else {
                                $initials = substr($student->name, 0, 2);
                              }
                            @endphp
                            {{ $initials }}
                          </div>
                          <div class="student-info">
                            <div class="student-name">{{ $student->name }}</div>
                            <small class="text-muted">ID: {{ $student->id }}</small>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-warning" onclick="saveOfficeAssignments()">
            <i class="bi bi-save me-2"></i>Save Assignments
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Conference Assignment Modal -->
  <div class="modal fade" id="conferenceAssignmentModal" tabindex="-1" aria-labelledby="conferenceAssignmentModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
      <div class="modal-content">
        <div class="modal-header bg-info text-white">
          <h5 class="modal-title" id="conferenceAssignmentModalLabel">
            <i class="bi bi-people-fill me-2"></i>View Assignment
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Instructions:</strong> Drag student names from the bottom panel to assign them to conference room cleaning tasks. 
            You can assign as many students as needed across all tasks.
          </div>

          <!-- Assignment Grid (TOP) -->
          <div class="row mb-4">
            <div class="col-32">
              <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                  <h6 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Conference Room Cleaning Task Assignments</h6>
                  <div>
                    <button class="btn btn-sm btn-light" onclick="clearAllConferenceAssignments()">
                      <i class="bi bi-trash me-1"></i>Clear All
                    </button>
                  </div>
                </div>
                <div class="card-body p-3">
                  <div class="assignment-grid">
                    <!-- Day Headers -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <h6 class="text-center font-weight-bold">Task / Day</h6>
                      </div>
                      <div class="col">
                        <div class="time-header monday">
                          Mon
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header tuesday">
                          Tue
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header wednesday">
                          Wed
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header thursday">
                          Thu
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header friday">
                          Fri
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header saturday">
                          Sat
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header sunday">
                          Sun
                        </div>
                      </div>
                    </div>

                    <!-- Task Rows -->
                    <!-- Conference Cleaning - Morning -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label sweep-mop">
                          Conference Cleaning<br>
                          <small style="font-size: 8px;">Morning</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-morning" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-morning-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-morning" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-morning-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-morning" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-morning-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-morning" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-morning-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-morning" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-morning-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-morning" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-morning-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-morning" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-morning-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Conference Cleaning - Afternoon -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label clean-glass">
                          Conference Cleaning<br>
                          <small style="font-size: 8px;">Afternoon</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-afternoon" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-afternoon-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-afternoon" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-afternoon-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-afternoon" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-afternoon-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-afternoon" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-afternoon-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-afternoon" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-afternoon-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-afternoon" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-afternoon-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-afternoon" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-afternoon-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Conference Cleaning - Evening -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label clean-bathroom">
                          Conference Cleaning<br>
                          <small style="font-size: 8px;">Evening</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-evening" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-evening-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-evening" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-evening-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-evening" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-evening-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-evening" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-evening-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-evening" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-evening-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-evening" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-evening-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="conference-evening" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="conference-evening-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
<hr style="border: 2px solid #ddd; margin: 20px 0;">

          <!-- Available Students (BOTTOM) -->
          <div class="row">
            <div class="col-32">
              <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #4285f4, #34a853); color: white; padding: 16px 20px; border: none;">
                  <h6 class="mb-0" style="font-weight: 600; font-size: 16px;"><i class="bi bi-people me-2"></i>👥 Available Students</h6>
                </div>
                <div class="card-body p-3" style="max-height: 200px; overflow-x: auto; overflow-y: hidden; background: #f8fbff;">
                  <div id="conferenceStudentList" class="student-list d-flex flex-nowrap gap-2" style="min-width: max-content;">
                    <!-- All Students Option -->
                    <div class="student-card all-students-option" draggable="true" data-student-id="all" data-student-name="All Students" style="flex: 0 0 auto; width: auto; min-width: 150px; border: 3px solid #28a745; background: linear-gradient(135deg, #28a745, #20c997);">
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2" style="background: #fff; color: #28a745; font-weight: bold; border: 2px solid #fff;">
                          ALL
                        </div>
                        <div class="flex-grow-1">
                          <div class="student-name fw-bold text-white" style="font-size: 0.85rem;">All Students</div>
                          <small class="text-white-50">Assign Everyone</small>
                        </div>
                      </div>
                    </div>
                    @foreach($students as $student)
                      <div class="student-card" draggable="true" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}" style="flex: 0 0 auto; width: auto; min-width: 150px;">
                        <div class="d-flex align-items-center">
                          <div class="student-avatar me-2">
                            @php
                              $nameParts = explode(' ', trim($student->name));
                              $initials = '';
                              if (count($nameParts) >= 2) {
                                $initials = substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1);
                              } else {
                                $initials = substr($student->name, 0, 2);
                              }
                            @endphp
                            {{ $initials }}
                          </div>
                          <div class="student-info">
                            <div class="student-name">{{ $student->name }}</div>
                            <small class="text-muted">ID: {{ $student->id }}</small>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-info" onclick="saveConferenceAssignments()">
            <i class="bi bi-save me-2"></i>Save Assignments
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Office Assignment Modal Functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize drag and drop when office modal opens
      const officeModal = document.getElementById('officeAssignmentModal');
      officeModal.addEventListener('shown.bs.modal', function() {
        initializeOfficeDragAndDrop();
        loadExistingOfficeAssignments();
      });

      // Clear assignments when modal closes
      officeModal.addEventListener('hidden.bs.modal', function() {
        clearAllOfficeAssignments();
      });

      // Initialize drag and drop when conference modal opens
      const conferenceModal = document.getElementById('conferenceAssignmentModal');
      conferenceModal.addEventListener('shown.bs.modal', function() {
        initializeConferenceDragAndDrop();
        loadExistingConferenceAssignments();
      });

      // Clear assignments when modal closes
      conferenceModal.addEventListener('hidden.bs.modal', function() {
        clearAllConferenceAssignments();
      });
    });

    // Office Modal Functions
    function initializeOfficeDragAndDrop() {
      const studentCards = document.querySelectorAll('#officeStudentList .student-card');
      studentCards.forEach(card => {
        card.addEventListener('dragstart', handleOfficeDragStart);
        card.addEventListener('dragend', handleOfficeDragEnd);
      });

      const dropZones = document.querySelectorAll('#officeAssignmentModal .drop-zone');
      dropZones.forEach(zone => {
        zone.addEventListener('dragover', handleOfficeDragOver);
        zone.addEventListener('dragenter', handleOfficeDragEnter);
        zone.addEventListener('dragleave', handleOfficeDragLeave);
        zone.addEventListener('drop', handleOfficeDrop);
      });
    }

    let officeDraggedElement = null;

    function handleOfficeDragStart(e) {
      officeDraggedElement = this;
      const studentData = {
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName
      };
      e.dataTransfer.setData('text/plain', JSON.stringify(studentData));
      this.style.opacity = '0.5';
    }

    function handleOfficeDragEnd(e) {
      this.style.opacity = '1';
      officeDraggedElement = null;
    }

    function handleOfficeDragOver(e) {
      e.preventDefault();
    }

    function handleOfficeDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleOfficeDragLeave(e) {
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
      }
    }

    function handleOfficeDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      if (!officeDraggedElement) return;
      
      const task = this.dataset.task;
      const time = this.dataset.time;
      const maxStudents = parseInt(this.dataset.max);
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      
      // Handle "All Students" option
      if (studentData.studentId === 'all') {
        assignAllOfficeStudentsToSlot(this, task, time, maxStudents);
        return;
      }
      
      const totalAssigned = getTotalOfficeAssignedStudents();
      if (totalAssigned >= 99) {
        showNotification('Maximum 99 students can be assigned across all office tasks', 'warning');
        return;
      }
      
      const assignedContainer = this.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for ${task} during ${time}`, 'warning');
        return;
      }
      
      const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentData.studentId}"]`);
      if (existingAssignment) {
        showNotification(`${studentData.studentName} is already assigned to this slot`, 'info');
        return;
      }
      
      const assignedStudent = createOfficeAssignedStudentElement(studentData, task, time);
      assignedContainer.appendChild(assignedStudent);
      hideOfficeStudentFromAvailableList(studentData.studentId);
      updateOfficeDropZoneStatus(this, currentCount + 1, maxStudents);
      showNotification(`${studentData.studentName} assigned to ${task} for ${time}`, 'success');
    }

    function createOfficeAssignedStudentElement(studentData, task, time) {
      const div = document.createElement('div');
      div.className = 'assigned-student';
      div.dataset.studentId = studentData.studentId;
      div.dataset.task = task;
      div.dataset.time = time;
      
      div.innerHTML = `
        <span class="student-name">${studentData.studentName}</span>
        <button class="remove-btn" onclick="removeOfficeAssignment(this)" title="Remove assignment">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      return div;
    }

    function removeOfficeAssignment(button) {
      const assignedStudent = button.closest('.assigned-student');
      const dropZone = assignedStudent.closest('.drop-zone');
      const studentName = assignedStudent.querySelector('.student-name').textContent;
      const studentId = assignedStudent.dataset.studentId;
      
      assignedStudent.remove();
      showOfficeStudentInAvailableList(studentId);
      
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      const maxStudents = parseInt(dropZone.dataset.max);
      updateOfficeDropZoneStatus(dropZone, currentCount, maxStudents);
      
      showNotification(`${studentName} removed from office assignment`, 'info');
    }

    function updateOfficeDropZoneStatus(dropZone, currentCount, maxStudents) {
      if (currentCount >= maxStudents) {
        dropZone.classList.add('full');
      } else {
        dropZone.classList.remove('full');
      }
      
      const header = dropZone.querySelector('.drop-zone-header small');
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    function getTotalOfficeAssignedStudents() {
      const assignedStudents = document.querySelectorAll('#officeAssignmentModal .assigned-students .assigned-student');
      return assignedStudents.length;
    }

    function hideOfficeStudentFromAvailableList(studentId) {
      const studentCard = document.querySelector(`#officeStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'none';
      }
    }

    function showOfficeStudentInAvailableList(studentId) {
      const studentCard = document.querySelector(`#officeStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'flex';
      }
    }

    // Assign all available students to a specific office slot
    function assignAllOfficeStudentsToSlot(dropZone, task, time, maxStudents) {
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      // Get all available students (not hidden)
      const availableStudents = document.querySelectorAll('#officeStudentList .student-card:not(.all-students-option)');
      const visibleStudents = Array.from(availableStudents).filter(card => 
        card.style.display !== 'none' && card.dataset.studentId !== 'all'
      );
      
      let assignedCount = 0;
      let totalAssigned = getTotalOfficeAssignedStudents();
      
      for (const studentCard of visibleStudents) {
        // Check if we've reached the slot limit
        if (currentCount + assignedCount >= maxStudents) {
          break;
        }
        
        // Check if we've reached the global limit
        if (totalAssigned >= 99) {
          break;
        }
        
        const studentId = studentCard.dataset.studentId;
        const studentName = studentCard.dataset.studentName;
        
        // Check if student is already assigned to this slot
        const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentId}"]`);
        if (existingAssignment) {
          continue;
        }
        
        // Create assigned student element
        const assignedStudent = createOfficeAssignedStudentElement({
          studentId: studentId,
          studentName: studentName
        }, task, time);
        
        assignedContainer.appendChild(assignedStudent);
        
        // Hide student from available list
        hideOfficeStudentFromAvailableList(studentId);
        
        assignedCount++;
        totalAssigned++;
      }
      
      // Update drop zone status
      updateOfficeDropZoneStatus(dropZone, currentCount + assignedCount, maxStudents);
      
      if (assignedCount > 0) {
        showNotification(`${assignedCount} students assigned to ${task} for ${time}`, 'success');
      } else {
        showNotification('No available students to assign', 'info');
      }
    }

    function clearAllOfficeAssignments() {
      document.querySelectorAll('#officeAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      
      document.querySelectorAll('#officeAssignmentModal .drop-zone').forEach(zone => {
        const maxStudents = parseInt(zone.dataset.max);
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `0/${maxStudents} students`;
        }
        zone.classList.remove('full');
      });

      document.querySelectorAll('#officeStudentList .student-card').forEach(card => {
        card.style.display = 'flex';
      });
    }

    function loadExistingOfficeAssignments() {
      fetch('/get-office-assignments')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.assignments) {
            // Clear existing assignments
            document.querySelectorAll('#officeAssignmentModal .assigned-students').forEach(container => {
              container.innerHTML = '';
            });
            
            // Load assignments into the grid
            data.assignments.forEach(assignment => {
              const targetContainer = document.getElementById(`${assignment.task_type}-${assignment.time_slot}`);
              if (targetContainer) {
                const studentElement = createOfficeAssignedStudentElement({
                  studentId: assignment.student_id,
                  studentName: assignment.student_name
                }, assignment.task_type, assignment.time_slot);
                targetContainer.appendChild(studentElement);
              }
            });
            
            // Update counters
            updateOfficeCounters();
          }
        })
        .catch(error => {
          console.error('Error loading office assignments:', error);
        });
    }
    
    // Update office assignment counters
    function updateOfficeCounters() {
      document.querySelectorAll('#officeAssignmentModal .drop-zone').forEach(zone => {
        const assignedContainer = zone.querySelector('.assigned-students');
        const currentCount = assignedContainer ? assignedContainer.children.length : 0;
        const maxStudents = parseInt(zone.dataset.max) || 99;
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `${currentCount}/${maxStudents} students`;
        }
      });
    }

    function saveOfficeAssignments() {
      // Collect all assignments
      const assignments = [];
      
      document.querySelectorAll('#officeAssignmentModal .assigned-student').forEach(student => {
        assignments.push({
          student_id: student.dataset.studentId,
          task: student.dataset.task,
          time: student.dataset.time,
          student_name: student.querySelector('.student-name').textContent
        });
      });

      if (assignments.length === 0) {
        showNotification('No office assignments to save', 'info');
        return;
      }

      // Send to server via AJAX
      fetch('/save-office-assignments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ assignments: assignments })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          const modal = bootstrap.Modal.getInstance(document.getElementById('officeAssignmentModal'));
          if (modal) modal.hide();
          setTimeout(() => window.location.reload(), 1500);
        } else {
          showNotification(data.message || 'Error saving assignments', 'error');
        }
      })
      .catch(error => {
        console.error('Error saving office assignments:', error);
        showNotification('Error saving assignments. Please try again.', 'error');
      });
      
      const modal = bootstrap.Modal.getInstance(document.getElementById('officeAssignmentModal'));
      if (modal) modal.hide();
    }

    // Conference Modal Functions
    function initializeConferenceDragAndDrop() {
      const studentCards = document.querySelectorAll('#conferenceStudentList .student-card');
      studentCards.forEach(card => {
        card.addEventListener('dragstart', handleConferenceDragStart);
        card.addEventListener('dragend', handleConferenceDragEnd);
      });

      const dropZones = document.querySelectorAll('#conferenceAssignmentModal .drop-zone');
      dropZones.forEach(zone => {
        zone.addEventListener('dragover', handleConferenceDragOver);
        zone.addEventListener('dragenter', handleConferenceDragEnter);
        zone.addEventListener('dragleave', handleConferenceDragLeave);
        zone.addEventListener('drop', handleConferenceDrop);
      });
    }

    let conferenceDraggedElement = null;

    function handleConferenceDragStart(e) {
      conferenceDraggedElement = this;
      const studentData = {
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName
      };
      e.dataTransfer.setData('text/plain', JSON.stringify(studentData));
      this.style.opacity = '0.5';
    }

    function handleConferenceDragEnd(e) {
      this.style.opacity = '1';
      conferenceDraggedElement = null;
    }

    function handleConferenceDragOver(e) {
      e.preventDefault();
    }

    function handleConferenceDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleConferenceDragLeave(e) {
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
      }
    }

    function handleConferenceDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      if (!conferenceDraggedElement) return;
      
      const task = this.dataset.task;
      const time = this.dataset.time;
      const maxStudents = parseInt(this.dataset.max);
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      
      // Handle "All Students" option
      if (studentData.studentId === 'all') {
        assignAllConferenceStudentsToSlot(this, task, time, maxStudents);
        return;
      }
      
      const totalAssigned = getTotalConferenceAssignedStudents();
      if (totalAssigned >= 99) {
        showNotification('Maximum 99 students can be assigned across all conference tasks', 'warning');
        return;
      }
      
      const assignedContainer = this.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for ${task} during ${time}`, 'warning');
        return;
      }
      
      const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentData.studentId}"]`);
      if (existingAssignment) {
        showNotification(`${studentData.studentName} is already assigned to this slot`, 'info');
        return;
      }
      
      const assignedStudent = createConferenceAssignedStudentElement(studentData, task, time);
      assignedContainer.appendChild(assignedStudent);
      hideConferenceStudentFromAvailableList(studentData.studentId);
      updateConferenceDropZoneStatus(this, currentCount + 1, maxStudents);
      showNotification(`${studentData.studentName} assigned to ${task} for ${time}`, 'success');
    }

    function createConferenceAssignedStudentElement(studentData, task, time) {
      const div = document.createElement('div');
      div.className = 'assigned-student';
      div.dataset.studentId = studentData.studentId;
      div.dataset.task = task;
      div.dataset.time = time;
      
      div.innerHTML = `
        <span class="student-name">${studentData.studentName}</span>
        <button class="remove-btn" onclick="removeConferenceAssignment(this)" title="Remove assignment">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      return div;
    }

    function removeConferenceAssignment(button) {
      const assignedStudent = button.closest('.assigned-student');
      const dropZone = assignedStudent.closest('.drop-zone');
      const studentName = assignedStudent.querySelector('.student-name').textContent;
      const studentId = assignedStudent.dataset.studentId;
      
      assignedStudent.remove();
      showConferenceStudentInAvailableList(studentId);
      
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      const maxStudents = parseInt(dropZone.dataset.max);
      updateConferenceDropZoneStatus(dropZone, currentCount, maxStudents);
      
      showNotification(`${studentName} removed from conference assignment`, 'info');
    }

    function updateConferenceDropZoneStatus(dropZone, currentCount, maxStudents) {
      if (currentCount >= maxStudents) {
        dropZone.classList.add('full');
      } else {
        dropZone.classList.remove('full');
      }
      
      const header = dropZone.querySelector('.drop-zone-header small');
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    function getTotalConferenceAssignedStudents() {
      const assignedStudents = document.querySelectorAll('#conferenceAssignmentModal .assigned-students .assigned-student');
      return assignedStudents.length;
    }

    function hideConferenceStudentFromAvailableList(studentId) {
      const studentCard = document.querySelector(`#conferenceStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'none';
      }
    }

    function showConferenceStudentInAvailableList(studentId) {
      const studentCard = document.querySelector(`#conferenceStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'flex';
      }
    }

    // Assign all available students to a specific conference slot
    function assignAllConferenceStudentsToSlot(dropZone, task, time, maxStudents) {
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      // Get all available students (not hidden)
      const availableStudents = document.querySelectorAll('#conferenceStudentList .student-card:not(.all-students-option)');
      const visibleStudents = Array.from(availableStudents).filter(card => 
        card.style.display !== 'none' && card.dataset.studentId !== 'all'
      );
      
      let assignedCount = 0;
      let totalAssigned = getTotalConferenceAssignedStudents();
      
      for (const studentCard of visibleStudents) {
        // Check if we've reached the slot limit
        if (currentCount + assignedCount >= maxStudents) {
          break;
        }
        
        // Check if we've reached the global limit
        if (totalAssigned >= 99) {
          break;
        }
        
        const studentId = studentCard.dataset.studentId;
        const studentName = studentCard.dataset.studentName;
        
        // Check if student is already assigned to this slot
        const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentId}"]`);
        if (existingAssignment) {
          continue;
        }
        
        // Create assigned student element
        const assignedStudent = createConferenceAssignedStudentElement({
          studentId: studentId,
          studentName: studentName
        }, task, time);
        
        assignedContainer.appendChild(assignedStudent);
        
        // Hide student from available list
        hideConferenceStudentFromAvailableList(studentId);
        
        assignedCount++;
        totalAssigned++;
      }
      
      // Update drop zone status
      updateConferenceDropZoneStatus(dropZone, currentCount + assignedCount, maxStudents);
      
      if (assignedCount > 0) {
        showNotification(`${assignedCount} students assigned to ${task} for ${time}`, 'success');
      } else {
        showNotification('No available students to assign', 'info');
      }
    }

    function clearAllConferenceAssignments() {
      document.querySelectorAll('#conferenceAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      
      document.querySelectorAll('#conferenceAssignmentModal .drop-zone').forEach(zone => {
        const maxStudents = parseInt(zone.dataset.max);
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `0/${maxStudents} students`;
        }
        zone.classList.remove('full');
      });

      document.querySelectorAll('#conferenceStudentList .student-card').forEach(card => {
        card.style.display = 'flex';
      });
    }

    function loadExistingConferenceAssignments() {
      fetch('/get-conference-assignments')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.assignments) {
            // Clear existing assignments
            document.querySelectorAll('#conferenceAssignmentModal .assigned-students').forEach(container => {
              container.innerHTML = '';
            });
            
            // Load assignments into the grid
            data.assignments.forEach(assignment => {
              const targetContainer = document.getElementById(`${assignment.task_type}-${assignment.time_slot}`);
              if (targetContainer) {
                const studentElement = createConferenceAssignedStudentElement({
                  studentId: assignment.student_id,
                  studentName: assignment.student_name
                }, assignment.task_type, assignment.time_slot);
                targetContainer.appendChild(studentElement);
              }
            });
            
            // Update counters
            updateConferenceCounters();
          }
        })
        .catch(error => {
          console.error('Error loading conference assignments:', error);
        });
    }
    
    // Update conference assignment counters
    function updateConferenceCounters() {
      document.querySelectorAll('#conferenceAssignmentModal .drop-zone').forEach(zone => {
        const assignedContainer = zone.querySelector('.assigned-students');
        const currentCount = assignedContainer ? assignedContainer.children.length : 0;
        const maxStudents = parseInt(zone.dataset.max) || 99;
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `${currentCount}/${maxStudents} students`;
        }
      });
    }

    function saveConferenceAssignments() {
      // Collect all assignments
      const assignments = [];
      
      document.querySelectorAll('#conferenceAssignmentModal .assigned-student').forEach(student => {
        assignments.push({
          student_id: student.dataset.studentId,
          task: student.dataset.task,
          time: student.dataset.time,
          student_name: student.querySelector('.student-name').textContent
        });
      });

      if (assignments.length === 0) {
        showNotification('No conference assignments to save', 'info');
        return;
      }

      // Send to server via AJAX
      fetch('/save-conference-assignments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ assignments: assignments })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          const modal = bootstrap.Modal.getInstance(document.getElementById('conferenceAssignmentModal'));
          if (modal) modal.hide();
          setTimeout(() => window.location.reload(), 1500);
        } else {
          showNotification(data.message || 'Error saving assignments', 'error');
        }
      })
      .catch(error => {
        console.error('Error saving conference assignments:', error);
        showNotification('Error saving assignments. Please try again.', 'error');
      });
      
      const modal = bootstrap.Modal.getInstance(document.getElementById('conferenceAssignmentModal'));
      if (modal) modal.hide();
    }
  </script>

  <!-- Ground Level Assignment Modal -->
  <div class="modal fade" id="groundAssignmentModal" tabindex="-1" aria-labelledby="groundAssignmentModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
      <div class="modal-content">
        <div class="modal-header bg-secondary text-white">
          <h5 class="modal-title" id="groundAssignmentModalLabel">
            <i class="bi bi-layers me-2"></i>View Assignment
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Instructions:</strong> Drag student names from the bottom panel to assign them to ground level operations tasks. 
            You can assign as many students as needed across all tasks.
          </div>

          <!-- Assignment Grid (TOP) -->
          <div class="row mb-4">
            <div class="col-32">
              <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                  <h6 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Ground Level Operations Task Assignments</h6>
                  <div>
                    <button class="btn btn-sm btn-light" onclick="clearAllGroundAssignments()">
                      <i class="bi bi-trash me-1"></i>Clear All
                    </button>
                  </div>
                </div>
                <div class="card-body p-3">
                  <div class="assignment-grid">
                    <!-- Day Headers -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <h6 class="text-center font-weight-bold">Task / Day</h6>
                      </div>
                      <div class="col">
                        <div class="time-header monday">
                          Mon
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header tuesday">
                          Tue
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header wednesday">
                          Wed
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header thursday">
                          Thu
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header friday">
                          Fri
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header saturday">
                          Sat
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header sunday">
                          Sun
                        </div>
                      </div>
                    </div>

                    <!-- Task Rows -->
                    <!-- Ground Operations - Morning -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label sweep-mop">
                          Ground Operations<br>
                          <small style="font-size: 8px;">Morning</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-morning" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-morning-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-morning" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-morning-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-morning" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-morning-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-morning" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-morning-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-morning" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-morning-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-morning" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-morning-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-morning" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-morning-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Ground Operations - Afternoon -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label clean-ground-cr">
                          Ground Operations<br>
                          <small style="font-size: 8px;">Afternoon</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-afternoon" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-afternoon-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-afternoon" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-afternoon-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-afternoon" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-afternoon-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-afternoon" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-afternoon-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-afternoon" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-afternoon-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-afternoon" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-afternoon-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-afternoon" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-afternoon-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Ground Operations - Evening -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label clean-elevator">
                          Ground Operations<br>
                          <small style="font-size: 8px;">Evening</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-evening" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-evening-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-evening" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-evening-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-evening" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-evening-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-evening" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-evening-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-evening" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-evening-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-evening" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-evening-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="ground-evening" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="ground-evening-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
<hr style="border: 2px solid #ddd; margin: 20px 0;">

          <!-- Available Students (BOTTOM) -->
          <div class="row">
            <div class="col-32">
              <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #4285f4, #34a853); color: white; padding: 16px 20px; border: none;">
                  <h6 class="mb-0" style="font-weight: 600; font-size: 16px;"><i class="bi bi-people me-2"></i>👥 Available Students</h6>
                </div>
                <div class="card-body p-3" style="max-height: 200px; overflow-x: auto; overflow-y: hidden; background: #f8fbff;">
                  <div id="groundStudentList" class="student-list d-flex flex-nowrap gap-2" style="min-width: max-content;">
                    <!-- All Students Option -->
                    <div class="student-card all-students-option" draggable="true" data-student-id="all" data-student-name="All Students" style="flex: 0 0 auto; width: auto; min-width: 150px; border: 3px solid #28a745; background: linear-gradient(135deg, #28a745, #20c997);">
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2" style="background: #fff; color: #28a745; font-weight: bold; border: 2px solid #fff;">
                          ALL
                        </div>
                        <div class="flex-grow-1">
                          <div class="student-name fw-bold text-white" style="font-size: 0.85rem;">All Students</div>
                          <small class="text-white-50">Assign Everyone</small>
                        </div>
                      </div>
                    </div>
                    @foreach($students as $student)
                      <div class="student-card" draggable="true" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}" style="flex: 0 0 auto; width: auto; min-width: 150px;">
                        <div class="d-flex align-items-center">
                          <div class="student-avatar me-2">
                            @php
                              $nameParts = explode(' ', trim($student->name));
                              $initials = '';
                              if (count($nameParts) >= 2) {
                                $initials = substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1);
                              } else {
                                $initials = substr($student->name, 0, 2);
                              }
                            @endphp
                            {{ $initials }}
                          </div>
                          <div class="student-info">
                            <div class="student-name">{{ $student->name }}</div>
                            <small class="text-muted">ID: {{ $student->id }}</small>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-secondary" onclick="saveGroundAssignments()">
            <i class="bi bi-save me-2"></i>Save Assignments
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Ground Level Assignment Modal Functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize drag and drop when ground modal opens
      const groundModal = document.getElementById('groundAssignmentModal');
      groundModal.addEventListener('shown.bs.modal', function() {
        initializeGroundDragAndDrop();
        loadExistingGroundAssignments();
      });

      // Clear assignments when modal closes
      groundModal.addEventListener('hidden.bs.modal', function() {
        clearAllGroundAssignments();
      });
    });

    // Ground Modal Functions
    function initializeGroundDragAndDrop() {
      const studentCards = document.querySelectorAll('#groundStudentList .student-card');
      studentCards.forEach(card => {
        card.addEventListener('dragstart', handleGroundDragStart);
        card.addEventListener('dragend', handleGroundDragEnd);
      });

      const dropZones = document.querySelectorAll('#groundAssignmentModal .drop-zone');
      dropZones.forEach(zone => {
        zone.addEventListener('dragover', handleGroundDragOver);
        zone.addEventListener('dragenter', handleGroundDragEnter);
        zone.addEventListener('dragleave', handleGroundDragLeave);
        zone.addEventListener('drop', handleGroundDrop);
      });
    }

    let groundDraggedElement = null;

    function handleGroundDragStart(e) {
      groundDraggedElement = this;
      const studentData = {
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName
      };
      e.dataTransfer.setData('text/plain', JSON.stringify(studentData));
      this.style.opacity = '0.5';
    }

    function handleGroundDragEnd(e) {
      this.style.opacity = '1';
      groundDraggedElement = null;
    }

    function handleGroundDragOver(e) {
      e.preventDefault();
    }

    function handleGroundDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleGroundDragLeave(e) {
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
      }
    }

    function handleGroundDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      if (!groundDraggedElement) return;
      
      const task = this.dataset.task;
      const time = this.dataset.time;
      const maxStudents = parseInt(this.dataset.max);
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      
      // Handle "All Students" option
      if (studentData.studentId === 'all') {
        assignAllGroundStudentsToSlot(this, task, time, maxStudents);
        return;
      }
      
      const totalAssigned = getTotalGroundAssignedStudents();
      if (totalAssigned >= 99) {
        showNotification('Maximum 99 students can be assigned across all ground level tasks', 'warning');
        return;
      }
      
      const assignedContainer = this.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for ${task} during ${time}`, 'warning');
        return;
      }
      
      const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentData.studentId}"]`);
      if (existingAssignment) {
        showNotification(`${studentData.studentName} is already assigned to this slot`, 'info');
        return;
      }
      
      const assignedStudent = createGroundAssignedStudentElement(studentData, task, time);
      assignedContainer.appendChild(assignedStudent);
      hideGroundStudentFromAvailableList(studentData.studentId);
      updateGroundDropZoneStatus(this, currentCount + 1, maxStudents);
      showNotification(`${studentData.studentName} assigned to ${task} for ${time}`, 'success');
    }

    function createGroundAssignedStudentElement(studentData, task, time) {
      const div = document.createElement('div');
      div.className = 'assigned-student';
      div.dataset.studentId = studentData.studentId;
      div.dataset.task = task;
      div.dataset.time = time;
      
      div.innerHTML = `
        <span class="student-name">${studentData.studentName}</span>
        <button class="remove-btn" onclick="removeGroundAssignment(this)" title="Remove assignment">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      return div;
    }

    function removeGroundAssignment(button) {
      const assignedStudent = button.closest('.assigned-student');
      const dropZone = assignedStudent.closest('.drop-zone');
      const studentName = assignedStudent.querySelector('.student-name').textContent;
      const studentId = assignedStudent.dataset.studentId;
      
      assignedStudent.remove();
      showGroundStudentInAvailableList(studentId);
      
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      const maxStudents = parseInt(dropZone.dataset.max);
      updateGroundDropZoneStatus(dropZone, currentCount, maxStudents);
      
      showNotification(`${studentName} removed from ground level assignment`, 'info');
    }

    function updateGroundDropZoneStatus(dropZone, currentCount, maxStudents) {
      if (currentCount >= maxStudents) {
        dropZone.classList.add('full');
      } else {
        dropZone.classList.remove('full');
      }
      
      const header = dropZone.querySelector('.drop-zone-header small');
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    function getTotalGroundAssignedStudents() {
      const assignedStudents = document.querySelectorAll('#groundAssignmentModal .assigned-students .assigned-student');
      return assignedStudents.length;
    }

    function hideGroundStudentFromAvailableList(studentId) {
      const studentCard = document.querySelector(`#groundStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'none';
      }
    }

    function showGroundStudentInAvailableList(studentId) {
      const studentCard = document.querySelector(`#groundStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'flex';
      }
    }

    // Assign all available students to a specific ground slot
    function assignAllGroundStudentsToSlot(dropZone, task, time, maxStudents) {
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      // Get all available students (not hidden)
      const availableStudents = document.querySelectorAll('#groundStudentList .student-card:not(.all-students-option)');
      const visibleStudents = Array.from(availableStudents).filter(card => 
        card.style.display !== 'none' && card.dataset.studentId !== 'all'
      );
      
      let assignedCount = 0;
      let totalAssigned = getTotalGroundAssignedStudents();
      
      for (const studentCard of visibleStudents) {
        // Check if we've reached the slot limit
        if (currentCount + assignedCount >= maxStudents) {
          break;
        }
        
        // Check if we've reached the global limit
        if (totalAssigned >= 99) {
          break;
        }
        
        const studentId = studentCard.dataset.studentId;
        const studentName = studentCard.dataset.studentName;
        
        // Check if student is already assigned to this slot
        const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentId}"]`);
        if (existingAssignment) {
          continue;
        }
        
        // Create assigned student element
        const assignedStudent = createGroundAssignedStudentElement({
          studentId: studentId,
          studentName: studentName
        }, task, time);
        
        assignedContainer.appendChild(assignedStudent);
        
        // Hide student from available list
        hideGroundStudentFromAvailableList(studentId);
        
        assignedCount++;
        totalAssigned++;
      }
      
      // Update drop zone status
      updateGroundDropZoneStatus(dropZone, currentCount + assignedCount, maxStudents);
      
      if (assignedCount > 0) {
        showNotification(`${assignedCount} students assigned to ${task} for ${time}`, 'success');
      } else {
        showNotification('No available students to assign', 'info');
      }
    }

    function clearAllGroundAssignments() {
      document.querySelectorAll('#groundAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      
      document.querySelectorAll('#groundAssignmentModal .drop-zone').forEach(zone => {
        const maxStudents = parseInt(zone.dataset.max);
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `0/${maxStudents} students`;
        }
        zone.classList.remove('full');
      });

      document.querySelectorAll('#groundStudentList .student-card').forEach(card => {
        card.style.display = 'flex';
      });
    }

    function loadExistingGroundAssignments() {
      fetch('/get-ground-assignments')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.assignments) {
            // Clear existing assignments
            document.querySelectorAll('#groundAssignmentModal .assigned-students').forEach(container => {
              container.innerHTML = '';
            });
            
            // Load assignments into the grid
            data.assignments.forEach(assignment => {
              const targetContainer = document.getElementById(`${assignment.task_type}-${assignment.time_slot}`);
              if (targetContainer) {
                const studentElement = createGroundAssignedStudentElement({
                  studentId: assignment.student_id,
                  studentName: assignment.student_name
                }, assignment.task_type, assignment.time_slot);
                targetContainer.appendChild(studentElement);
              }
            });
            
            // Update counters
            updateGroundCounters();
          }
        })
        .catch(error => {
          console.error('Error loading ground assignments:', error);
      document.querySelectorAll('#groundAssignmentModal .assigned-student').forEach(student => {
        assignments.push({
          student_id: student.dataset.studentId,
          task: student.dataset.task,
          time: student.dataset.time,
          student_name: student.querySelector('.student-name').textContent
        });
      });

      if (assignments.length === 0) {
        showNotification('No ground level assignments to save', 'info');
        return;
      }

      // Send to server via AJAX
      fetch('/save-ground-assignments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ assignments: assignments })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          const modal = bootstrap.Modal.getInstance(document.getElementById('groundAssignmentModal'));
          if (modal) modal.hide();
          setTimeout(() => window.location.reload(), 1500);
        } else {
          showNotification(data.message || 'Error saving assignments', 'error');
        }
      })
      .catch(error => {
        console.error('Error saving ground assignments:', error);
        showNotification('Error saving assignments. Please try again.', 'error');
      });
      
      const modal = bootstrap.Modal.getInstance(document.getElementById('groundAssignmentModal'));
      if (modal) modal.hide();
    }
  </script>

  <!-- Waste Management Assignment Modal -->
  <div class="modal fade" id="wasteAssignmentModal" tabindex="-1" aria-labelledby="wasteAssignmentModalLabel">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
      <div class="modal-content">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title" id="wasteAssignmentModalLabel">
            <i class="bi bi-recycle me-2"></i>View Assignment
          </h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Instructions:</strong> Drag student names from the bottom panel to assign them to waste management tasks. 
            You can assign as many students as needed across all tasks.
          </div>

          <!-- Assignment Grid (TOP) -->
          <div class="row mb-4">
            <div class="col-32">
              <div class="card shadow-sm">
                <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
                  <h6 class="mb-0"><i class="bi bi-calendar3 me-2"></i>Waste Management Task Assignments</h6>
                  <div>
                    <button class="btn btn-sm btn-light" onclick="clearAllWasteAssignments()">
                      <i class="bi bi-trash me-1"></i>Clear All
                    </button>
                  </div>
                </div>
                <div class="card-body p-3">
                  <div class="assignment-grid">
                    <!-- Day Headers -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <h6 class="text-center font-weight-bold">Task / Day</h6>
                      </div>
                      <div class="col">
                        <div class="time-header monday">
                          Mon
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header tuesday">
                          Tue
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header wednesday">
                          Wed
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header thursday">
                          Thu
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header friday">
                          Fri
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header saturday">
                          Sat
                        </div>
                      </div>
                      <div class="col">
                        <div class="time-header sunday">
                          Sun
                        </div>
                      </div>
                    </div>

                    <!-- Task Rows -->
                    <!-- Waste Management - Morning -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label collect-garbage">
                          Waste Management<br>
                          <small style="font-size: 8px;">Morning</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-morning" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-morning-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-morning" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-morning-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-morning" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-morning-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-morning" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-morning-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-morning" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-morning-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-morning" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-morning-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-morning" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-morning-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Waste Management - Afternoon -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label separate-garbage">
                          Waste Management<br>
                          <small style="font-size: 8px;">Afternoon</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-afternoon" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-afternoon-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-afternoon" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-afternoon-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-afternoon" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-afternoon-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-afternoon" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-afternoon-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-afternoon" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-afternoon-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-afternoon" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-afternoon-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-afternoon" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-afternoon-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Waste Management - Evening -->
                    <div class="row mb-2">
                      <div class="col-3">
                        <div class="task-label arrange-garbage">
                          Waste Management<br>
                          <small style="font-size: 8px;">Evening</small>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-evening" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-evening-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-evening" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-evening-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-evening" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-evening-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-evening" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-evening-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-evening" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-evening-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-evening" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-evening-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="waste-evening" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="waste-evening-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="monday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-monday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="tuesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-tuesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="wednesday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-wednesday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="thursday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-thursday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="friday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-friday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="saturday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-saturday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                      <div class="col">
                        <div class="drop-zone" data-task="cook-dinner" data-time="sunday" data-max="99">
                          <div class="drop-zone-header">
                            <small>0/99 students</small>
                          </div>
                          <div class="assigned-students" id="cook-dinner-sunday">
                            <!-- Students will be dropped here -->
                          </div>
                        </div>
                      </div>
                    </div>

                  </div>
                </div>
              </div>
            </div>
          </div>
<hr style="border: 2px solid #ddd; margin: 20px 0;">

          <!-- Available Students (BOTTOM) -->
          <div class="row">
            <div class="col-32">
              <div class="card shadow-sm" style="border-radius: 12px; overflow: hidden; border: none;">
                <div class="card-header" style="background: linear-gradient(135deg, #4285f4, #34a853); color: white; padding: 16px 20px; border: none;">
                  <h6 class="mb-0" style="font-weight: 600; font-size: 16px;"><i class="bi bi-people me-2"></i>👥 Available Students</h6>
                </div>
                <div class="card-body p-3" style="max-height: 200px; overflow-x: auto; overflow-y: hidden; background: #f8fbff;">
                  <div id="wasteStudentList" class="student-list d-flex flex-nowrap gap-2" style="min-width: max-content;">
                    <!-- All Students Option -->
                    <div class="student-card all-students-option" draggable="true" data-student-id="all" data-student-name="All Students" style="flex: 0 0 auto; width: auto; min-width: 150px; border: 3px solid #28a745; background: linear-gradient(135deg, #28a745, #20c997);">
                      <div class="d-flex align-items-center">
                        <div class="avatar-circle me-2" style="background: #fff; color: #28a745; font-weight: bold; border: 2px solid #fff;">
                          ALL
                        </div>
                        <div class="flex-grow-1">
                          <div class="student-name fw-bold text-white" style="font-size: 0.85rem;">All Students</div>
                          <small class="text-white-50">Assign Everyone</small>
                        </div>
                      </div>
                    </div>
                    @foreach($students as $student)
                      <div class="student-card" draggable="true" data-student-id="{{ $student->id }}" data-student-name="{{ $student->name }}" style="flex: 0 0 auto; width: auto; min-width: 150px;">
                        <div class="d-flex align-items-center">
                          <div class="student-avatar me-2">
                            @php
                              $nameParts = explode(' ', trim($student->name));
                              $initials = '';
                              if (count($nameParts) >= 2) {
                                $initials = substr($nameParts[0], 0, 1) . substr($nameParts[count($nameParts)-1], 0, 1);
                              } else {
                                $initials = substr($student->name, 0, 2);
                              }
                            @endphp
                            {{ $initials }}
                          </div>
                          <div class="student-info">
                            <div class="student-name">{{ $student->name }}</div>
                            <small class="text-muted">ID: {{ $student->id }}</small>
                          </div>
                        </div>
                      </div>
                    @endforeach
                  </div>
                </div>
              </div>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cancel
          </button>
          <button type="button" class="btn btn-dark" onclick="saveWasteAssignments()">
            <i class="bi bi-save me-2"></i>Save Assignments
          </button>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Waste Management Assignment Modal Functionality
    document.addEventListener('DOMContentLoaded', function() {
      // Initialize drag and drop when waste modal opens
      const wasteModal = document.getElementById('wasteAssignmentModal');
      wasteModal.addEventListener('shown.bs.modal', function() {
        initializeWasteDragAndDrop();
        loadExistingWasteAssignments();
      });

      // Clear assignments when modal closes
      wasteModal.addEventListener('hidden.bs.modal', function() {
        clearAllWasteAssignments();
      });
    });

    // Waste Modal Functions
    function initializeWasteDragAndDrop() {
      const studentCards = document.querySelectorAll('#wasteStudentList .student-card');
      studentCards.forEach(card => {
        card.addEventListener('dragstart', handleWasteDragStart);
        card.addEventListener('dragend', handleWasteDragEnd);
      });

      const dropZones = document.querySelectorAll('#wasteAssignmentModal .drop-zone');
      dropZones.forEach(zone => {
        zone.addEventListener('dragover', handleWasteDragOver);
        zone.addEventListener('dragenter', handleWasteDragEnter);
        zone.addEventListener('dragleave', handleWasteDragLeave);
        zone.addEventListener('drop', handleWasteDrop);
      });
    }

    let wasteDraggedElement = null;

    function handleWasteDragStart(e) {
      wasteDraggedElement = this;
      const studentData = {
        studentId: this.dataset.studentId,
        studentName: this.dataset.studentName
      };
      e.dataTransfer.setData('text/plain', JSON.stringify(studentData));
      this.style.opacity = '0.5';
    }

    function handleWasteDragEnd(e) {
      this.style.opacity = '1';
      wasteDraggedElement = null;
    }

    function handleWasteDragOver(e) {
      e.preventDefault();
    }

    function handleWasteDragEnter(e) {
      e.preventDefault();
      this.classList.add('drag-over');
    }

    function handleWasteDragLeave(e) {
      if (!this.contains(e.relatedTarget)) {
        this.classList.remove('drag-over');
      }
    }

    function handleWasteDrop(e) {
      e.preventDefault();
      this.classList.remove('drag-over');
      
      if (!wasteDraggedElement) return;
      
      const task = this.dataset.task;
      const time = this.dataset.time;
      const maxStudents = parseInt(this.dataset.max);
      const studentData = JSON.parse(e.dataTransfer.getData('text/plain'));
      
      // Handle "All Students" option
      if (studentData.studentId === 'all') {
        assignAllWasteStudentsToSlot(this, task, time, maxStudents);
        return;
      }
      
      const totalAssigned = getTotalWasteAssignedStudents();
      if (totalAssigned >= 99) {
        showNotification('Maximum 99 students can be assigned across all waste management tasks', 'warning');
        return;
      }
      
      const assignedContainer = this.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      if (currentCount >= maxStudents) {
        showNotification(`Maximum ${maxStudents} students allowed for ${task} during ${time}`, 'warning');
        return;
      }
      
      const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentData.studentId}"]`);
      if (existingAssignment) {
        showNotification(`${studentData.studentName} is already assigned to this slot`, 'info');
        return;
      }
      
      const assignedStudent = createWasteAssignedStudentElement(studentData, task, time);
      assignedContainer.appendChild(assignedStudent);
      hideWasteStudentFromAvailableList(studentData.studentId);
      updateWasteDropZoneStatus(this, currentCount + 1, maxStudents);
      showNotification(`${studentData.studentName} assigned to ${task} for ${time}`, 'success');
    }

    function createWasteAssignedStudentElement(studentData, task, time) {
      const div = document.createElement('div');
      div.className = 'assigned-student';
      div.dataset.studentId = studentData.studentId;
      div.dataset.task = task;
      div.dataset.time = time;
      
      div.innerHTML = `
        <span class="student-name">${studentData.studentName}</span>
        <button class="remove-btn" onclick="removeWasteAssignment(this)" title="Remove assignment">
          <i class="bi bi-x"></i>
        </button>
      `;
      
      return div;
    }

    function removeWasteAssignment(button) {
      const assignedStudent = button.closest('.assigned-student');
      const dropZone = assignedStudent.closest('.drop-zone');
      const studentName = assignedStudent.querySelector('.student-name').textContent;
      const studentId = assignedStudent.dataset.studentId;
      
      assignedStudent.remove();
      showWasteStudentInAvailableList(studentId);
      
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      const maxStudents = parseInt(dropZone.dataset.max);
      updateWasteDropZoneStatus(dropZone, currentCount, maxStudents);
      
      showNotification(`${studentName} removed from waste management assignment`, 'info');
    }

    function updateWasteDropZoneStatus(dropZone, currentCount, maxStudents) {
      if (currentCount >= maxStudents) {
        dropZone.classList.add('full');
      } else {
        dropZone.classList.remove('full');
      }
      
      const header = dropZone.querySelector('.drop-zone-header small');
      if (header && !isStudent) {
        header.textContent = `${currentCount}/${maxStudents} students`;
      }
    }

    function getTotalWasteAssignedStudents() {
      const assignedStudents = document.querySelectorAll('#wasteAssignmentModal .assigned-students .assigned-student');
      return assignedStudents.length;
    }

    function hideWasteStudentFromAvailableList(studentId) {
      const studentCard = document.querySelector(`#wasteStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'none';
      }
    }

    function showWasteStudentInAvailableList(studentId) {
      const studentCard = document.querySelector(`#wasteStudentList .student-card[data-student-id="${studentId}"]`);
      if (studentCard) {
        studentCard.style.display = 'flex';
      }
    }

    // Assign all available students to a specific waste slot
    function assignAllWasteStudentsToSlot(dropZone, task, time, maxStudents) {
      const assignedContainer = dropZone.querySelector('.assigned-students');
      const currentCount = assignedContainer.children.length;
      
      // Get all available students (not hidden)
      const availableStudents = document.querySelectorAll('#wasteStudentList .student-card:not(.all-students-option)');
      const visibleStudents = Array.from(availableStudents).filter(card => 
        card.style.display !== 'none' && card.dataset.studentId !== 'all'
      );
      
      let assignedCount = 0;
      let totalAssigned = getTotalWasteAssignedStudents();
      
      for (const studentCard of visibleStudents) {
        // Check if we've reached the slot limit
        if (currentCount + assignedCount >= maxStudents) {
          break;
        }
        
        // Check if we've reached the global limit
        if (totalAssigned >= 99) {
          break;
        }
        
        const studentId = studentCard.dataset.studentId;
        const studentName = studentCard.dataset.studentName;
        
        // Check if student is already assigned to this slot
        const existingAssignment = assignedContainer.querySelector(`[data-student-id="${studentId}"]`);
        if (existingAssignment) {
          continue;
        }
        
        // Create assigned student element
        const assignedStudent = createWasteAssignedStudentElement({
          studentId: studentId,
          studentName: studentName
        }, task, time);
        
        assignedContainer.appendChild(assignedStudent);
        
        // Hide student from available list
        hideWasteStudentFromAvailableList(studentId);
        
        assignedCount++;
        totalAssigned++;
      }
      
      // Update drop zone status
      updateWasteDropZoneStatus(dropZone, currentCount + assignedCount, maxStudents);
      
      if (assignedCount > 0) {
        showNotification(`${assignedCount} students assigned to ${task} for ${time}`, 'success');
      } else {
        showNotification('No available students to assign', 'info');
      }
    }

    function clearAllWasteAssignments() {
      document.querySelectorAll('#wasteAssignmentModal .assigned-students').forEach(container => {
        container.innerHTML = '';
      });
      
      document.querySelectorAll('#wasteAssignmentModal .drop-zone').forEach(zone => {
        const maxStudents = parseInt(zone.dataset.max);
        const header = zone.querySelector('.drop-zone-header small');
        if (header && !isStudent) {
          header.textContent = `0/${maxStudents} students`;
        }
        zone.classList.remove('full');
      });

      document.querySelectorAll('#wasteStudentList .student-card').forEach(card => {
        card.style.display = 'flex';
      });
    }

    function loadExistingWasteAssignments() {
      fetch('/get-waste-assignments')
        .then(response => response.json())
        .then(data => {
          if (data.success && data.assignments) {
            // Clear existing assignments
            document.querySelectorAll('#wasteAssignmentModal .assigned-students').forEach(container => {
              container.innerHTML = '';
            });
            
            // Load assignments into the grid
            data.assignments.forEach(assignment => {
              const targetContainer = document.getElementById(`${assignment.task_type}-${assignment.time_slot}`);
              if (targetContainer) {
                const studentElement = createWasteAssignedStudentElement({
                  studentId: assignment.student_id,
                  studentName: assignment.student_name
                }, assignment.task_type, assignment.time_slot);
                targetContainer.appendChild(studentElement);
              }
            });
            
            // Update counters
            updateWasteCounters();
          }
        })
        .catch(error => {
          console.error('Error loading waste assignments:', error);
        });
    }

    function saveWasteAssignments() {
      // Collect all assignments
      const assignments = [];
      
      document.querySelectorAll('#wasteAssignmentModal .assigned-student').forEach(student => {
        assignments.push({
          student_id: student.dataset.studentId,
          task: student.dataset.task,
          time: student.dataset.time,
          student_name: student.querySelector('.student-name').textContent
        });
      });

      if (assignments.length === 0) {
        showNotification('No waste management assignments to save', 'info');
        return;
      }

      // Send to server via AJAX
      fetch('/save-waste-assignments', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ assignments: assignments })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(data.message, 'success');
          const modal = bootstrap.Modal.getInstance(document.getElementById('wasteAssignmentModal'));
          if (modal) modal.hide();
          setTimeout(() => window.location.reload(), 1500);
        } else {
          showNotification(data.message || 'Error saving assignments', 'error');
        }
      })
      .catch(error => {
        console.error('Error saving waste assignments:', error);
        showNotification('Error saving assignments. Please try again.', 'error');
      });
      
      const modal = bootstrap.Modal.getInstance(document.getElementById('wasteAssignmentModal'));
      if (modal) modal.hide();
    }


  </script>


</body>
</html>
