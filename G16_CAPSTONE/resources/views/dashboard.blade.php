<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PN Tasking Hub</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@flaticon/flaticon-uicons/css/all/all.css">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Snapshot counts wiring -->
  <meta name="room-tasks-count" content="{{ $roomTasksCount ?? 0 }}">
  <!-- Lightweight loader (used instead of SweetAlert) -->
  <style>
    /* Simple centered loader overlay */
    #simpleLoaderOverlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0,0,0,0.35);
      display: none;
      align-items: center;
      justify-content: center;
      z-index: 20000;
    }
    
    /* ===== PN-SCHOLARYNC LOGOUT ICON STYLES ===== */
    .logout-container {
      display: flex;
      align-items: center;
      margin-right: 20px;
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

    /* Header layout - move logout to right side */
    header {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      height: 95px;
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
      height: 55px;
      width: auto;
    }

    /* Logout container - positioned on right */
    .logout-container {
      display: flex;
      align-items: center;
      margin-left: auto;
      margin-right: 0;
    }

    /* Body padding for fixed header */
    body {
      padding-top: 60px;
      margin: 0;
      font-family: 'Poppins', sans-serif;
    }

    #simpleLoaderBox {
      background: white;
      padding: 18px 22px;
      border-radius: 8px;
      min-width: 320px;
      max-width: 90%;
      box-shadow: 0 10px 30px rgba(0,0,0,0.2);
      text-align: center;
      font-family: 'Poppins', sans-serif;
    }
    #simpleLoaderBox .loader-icon { font-size: 28px; display:block; margin-bottom:8px; }
    #simpleLoaderBox .loader-message { font-size: 15px; color: #333; margin-bottom: 6px; }
    #simpleLoaderBox.success { border-left: 4px solid #28a745; }
    #simpleLoaderBox.error { border-left: 4px solid #dc3545; }
    /* small spinner animation */
    @keyframes loader-rotate { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    .spinner-dot {
      width: 28px; height: 28px; border-radius: 50%; border: 4px solid rgba(0,0,0,0.12); border-top-color: #007bff; margin: 6px auto 10px auto; animation: loader-rotate 0.9s linear infinite;
    }
  </style>

  <!-- Loader overlay element -->
  <div id="simpleLoaderOverlay" aria-hidden="true">
    <div id="simpleLoaderBox" role="status">
      <div class="spinner-dot" aria-hidden="true"></div>
      <div class="loader-message">Please wait...</div>
    </div>
  </div>

  <script>

    function showResult(type = 'success', message = '', timeout = 2500) {
      try {
        const overlay = document.getElementById('simpleLoaderOverlay');
        const box = document.getElementById('simpleLoaderBox');
        if (!overlay || !box) return;
        box.classList.remove('success','error');
        if (type === 'success') box.classList.add('success');
        if (type === 'error') box.classList.add('error');
        box.querySelector('.loader-message').textContent = message || (type === 'success' ? 'Done' : 'Error');
        // Stop spinner by hiding it visually (keep it for animation consistency)
        overlay.style.display = 'flex';
        overlay.setAttribute('aria-hidden', 'false');
        // Hide after timeout
        setTimeout(() => {
          try { overlay.style.display = 'none'; overlay.setAttribute('aria-hidden','true'); } catch(e) {}
        }, timeout);
      } catch(e) { console.warn('showResult failed', e); }
    }

    function hideLoader() {
      try { const overlay = document.getElementById('simpleLoaderOverlay'); if (overlay) { overlay.style.display = 'none'; overlay.setAttribute('aria-hidden','true'); } } catch(e) {}
    }
  </script>
  <style>
    /* Room Edit Form Styles - Matching Room Management */
    .form-control {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #ced4da;
      border-radius: 4px;
      font-size: 14px;
      transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .form-control:focus {
      border-color: #80bdff;
      outline: none;
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .form-label {
      margin-bottom: 5px;
      font-weight: 500;
      color: #495057;
      font-size: 14px;
    }

    .form-text {
      margin-top: 3px;
      font-size: 12px;
    }

    .text-muted {
      color: #6c757d !important;
    }

    .row {
      margin-left: 0;
      margin-right: 0;
    }

    .mb-3 {
      margin-bottom: 1rem !important;
    }

    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .modal-footer {
      display: flex;
      align-items: center;
      justify-content: flex-end;
      gap: 10px;
    }

    .btn {
      padding: 8px 16px;
      border: 1px solid transparent;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
      font-size: 14px;
      font-weight: 400;
      text-align: center;
      vertical-align: middle;
      transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }

    .btn-primary {
      color: #fff;
      background-color: #007bff;
      border-color: #007bff;
    }

    .btn-primary:hover {
      background-color: #0056b3;
      border-color: #004085;
    }

    .btn-secondary {
      color: #fff;
      background-color: #6c757d;
      border-color: #6  c757d;
    }

    .btn-sec  ondary:hover {
      background-color: #545b62;
      border-color: #4e555b;
    }

    .btn-sm {
      padding: 4px 8px;
      font-size: 12px;
    } 

    .btn-outline-primary {  
      color: #007bff;
      border-color: #007bff;
      background-color: transparent;
    }

    .btn-outline-primary:hover {
      background-color: #007bff;
      color: white;
    }

    .btn-outline-danger {
      color: #dc3545;
      border-color: #dc3545;
      background-color: transparent;
    }

    .btn-outline-danger:hover {
      background-color: #dc3545;
      color: white;
    }

    .badge {
      display: inline-block;
      padding: 4px 8px;
      font-size: 12px;
      font-weight: bold;
      border-radius: 12px;
      margin-left: 8px;
    }

    .badge-blue {
      background-color: #007bff;
      color: white;
    }

    .badge-pink {
      background-color: #e91e63;
      color: white;
    }

    .badge-secondary {
      background-color: #6c757d;
      color: white;
    }

    .student-item {
      transition: background-color 0.2s;
    }

    .student-item:hover {
      background-color: #f8f9fa;
    }

    /* Responsive Design for Room Cards */
    @media (max-width: 768px) {
      .room-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
      }

      .room-title {
        width: 100%;
      }

      .room-status {
        justify-content: space-between;
        width: 100%;
      }

      .room-actions {
        align-self: flex-end;
      }

      .room-footer {
        flex-direction: column;
        align-items: stretch;
        gap: 15px;
      }

      .footer-actions {
        justify-content: center;
      }

      .footer-link {
        justify-content: center;
      }

      .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
      }

      .occupant-count {
        align-self: flex-start;
      }

      .btn {
        padding: 10px 16px;
        font-size: 0.9rem;
      }
    }


    @media (max-width: 480px) {
      .room-card {
        margin-bottom: 15px;
      }

      .room-header {
        padding: 12px 16px;
      }

      .room-body {
        padding: 16px;
      }

      .room-footer {
        padding: 12px 16px;
      }

      .room-title h3 {
        font-size: 1.1rem;
      }

      .footer-actions {
        flex-direction: column;
        width: 100%;
      }

      .btn {
        width: 100%;
        justify-content: center;
      }
    }

    /* Original responsive styles */
    @media (max-width: 768px) {
      .stats-container {
        flex-direction: column !important;
        align-items: center !important;
      }

      .management-cards {
        flex-direction: column !important;
      }

      .section-header {
        flex-direction: column !important;
        gap: 20px !important;
        text-align: center !important;
      }

      .action-buttons-group {
        flex-direction: column !important;
        width: 100% !important;
      }

      .action-btn {
        width: 100% !important;
      }

      .floor-buttons {
        justify-content: center !important;
      }
    }

    @media (max-width: 480px) {
      .welcome-header h1 {
        font-size: 2rem !important;
      }

      .stat-card {
        min-width: 150px !important;
        padding: 20px !important;
      }

      .stat-number {
        font-size: 2.5rem !important;
      }

      .management-card {
        padding: 20px !important;
      }
    }

    .current-stats {
      font-family: 'Courier New', monospace;
      font-size: 0.9rem;
      line-height: 1.6;
    }

    #roomCapacity {
      font-size: 1.1rem;
      padding: 10px;
      border: 2px solid #dee2e6;
      border-radius: 5px;
      transition: border-color 0.3s ease;
    }

    #roomCapacity:focus {
      border-color: #007bff;
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    /* Enhanced hover effects */
    .floor-buttons {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
      margin-top: 15px;
    }

    .floor-buttons.hidden {
      display: none !important;
    }

    /* Rooms container visibility helper - ensures rooms are hidden/shown when toggled */
    .rooms-container {
      transition: opacity 0.25s ease;
      opacity: 1;
      margin-top: 20px;
    }

    .rooms-container.hidden {
      display: none !important;
    }

    .rooms-wrapper {
      display: grid;
      grid-template-columns: repeat(4, minmax(0, 1fr));
      gap: 18px;
    }

    @media (max-width: 1200px) {
      .rooms-wrapper { grid-template-columns: repeat(3, minmax(0, 1fr)); }
    }
    @media (max-width: 900px) {
      .rooms-wrapper { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 600px) {
      .rooms-wrapper { grid-template-columns: 1fr; }
    }

    /* Room card layout (manage-room style) */
    .room-card { display:flex; flex-direction:column; }
    .room-card .room-header {
      display:flex; align-items:center; justify-content:space-between;
      padding:12px 16px; border-bottom:1px solid #e9ecef;
    }
    .room-card .room-title { margin:0; font-size:1rem; font-weight:700; color:#1f2937; }
    .room-card .room-status { font-size:0.75rem; padding:4px 8px; border-radius:9999px; background:#eafaf0; color:#198754; font-weight:600; }
    .room-card .room-body { padding:12px 16px; }
    .room-card .occ-row { display:flex; justify-content:space-between; align-items:center; margin:6px 0 8px; font-weight:700; }
    .room-card .occ-bar { height:6px; background:#f1f5f9; border-radius:6px; overflow:hidden; }
    .room-card .occ-bar > span { display:block; height:100%; background:#f59e0b; width:0%; }
    .room-card .section-label { color:#6b7280; font-size:0.85rem; margin-top:10px; margin-bottom:6px; }
    .room-card .occupants { border:1px solid #e9ecef; border-radius:8px; padding:8px; background:#fafafa; max-height:140px; overflow:auto; }
    .room-card .occupants > div { padding:4px 6px; border-bottom:1px solid #eee; }
    .room-card .occupants > div:last-child { border-bottom:none; }

    /* Prevent any animations or transitions that cause room cards to shake when updating */
    .room-card, .room-card * {
      transition: none !important;
      animation: none !important;
    }

    /* Room card footer actions - auto width buttons */
    .room-card .room-footer {
      padding: 12px 16px;
      border-top: 1px solid #e9ecef;
      display: flex;
      gap: 8px;
    }
    .room-card .room-footer .btn {
      display: inline-flex;
      justify-content: center;
      align-items: center;
      padding: 10px 12px;
      border-radius: 8px;
      font-weight: 600;
      font-size: 0.9rem;
    }
    .room-card .btn-capacity { background: #22bbea; color: #fff; border: none; }
    .room-card .btn-checklist { background: #0d6efd; color: #fff; border: none; }

    /* Active floor button visual state */
    .floor-btn.active {
      background: #0d6efd;
      color: #fff;
      border: none;
      box-shadow: 0 4px 10px rgba(13,110,253,0.15);
    }

    /* Enhanced Student Management Styles */
    .student-item {
      transition: all 0.2s ease;
    }

    .student-item:hover {
      background: #f0f0f0 !important;
      border-color: #007bff !important;
      transform: translateY(-1px);
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .badge-blue {
      background-color: #007bff;
      color: white;
    }

    .badge-pink {
      background-color: #e91e63;
      color: white;
    }

    .badge-secondary {
      background-color: #6c757d;
      color: white;
    }

    .suggestion-item:hover {
      background-color: #f8f9fa !important;
    }

    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
      animation: fadeIn 0.3s ease;
    }

    .modal-content {
      background-color: #fefefe;
      margin: 5% auto;
      padding: 20px;
      border: none;
      border-radius: 12px;
      width: 80%;
      max-width: 600px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.3);
      animation: slideIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    @keyframes slideIn {
      from { transform: translateY(-50px); opacity: 0; }
      to { transform: translateY(0); opacity: 1; }
    }

    .close {
      color: #aaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
      cursor: pointer;
      transition: color 0.2s ease;
    }

    .close:hover,
    .close:focus {
      color: #000;
      text-decoration: none;
    }

    .btn-success {
      background-color: #28a745;
      border-color: #28a745;
      color: white;
    }

    .btn-success:hover {
      background-color: #218838;
      border-color: #1e7e34;
    }

    .btn-danger {
      background-color: #dc3545;
      border-color: #dc3545;
      color: white;
    }

    .btn-danger:hover {
      background-color: #c82333;
      border-color: #bd2130;
    }

    .alert {
      animation: slideInRight 0.3s ease;
    }

    @keyframes slideInRight {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }

    @keyframes spin {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }

    /* Loading states */
    .btn:disabled {
      opacity: 0.6;
      cursor: not-allowed;
    }

    /* Form validation styles */
    .form-control.error {
      border-color: #dc3545;
      box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
    }

    .form-control.success {
      border-color: #28a745;
      box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
    }

    /* Animation for loading spinner */
    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }



    /* Badge styles */
    .badge-blue {
      background-color: #2196f3;
      color: white;
    }

    .badge-pink {
      background-color: #e91e63;
      color: white;
    }

    .badge-secondary {
      background-color: #6c757d;
      color: white;
    }
  </style>
  <style>
    .hero-wrap { width:100%; max-width:1200px; margin:0 auto 20px auto; padding:0 16px; box-sizing:border-box; }
    .welcomeCard { width:100%; margin:0 auto 18px auto; }
    .page-header { display:flex; flex-direction:column; gap:6px; margin:16px 0 8px 0; }
    .page-header h1 { margin:0; font-size:1.6rem; font-weight:700; color:#1f2937; }
    .page-subtitle { color:#6b7280; font-size:0.95rem; }
    .tabs-wrap { display:flex; gap:10px; align-items:center; }
    .nav.nav-tabs.clean-pills .nav-link { border:none; background:#f3f4f6; color:#374151; border-radius:9999px; padding:8px 14px; font-weight:500; }
    .nav.nav-tabs.clean-pills .nav-link.active { background:#0d6efd; color:#fff; }
    .toolbar-row { display:flex; justify-content:space-between; align-items:center; gap:12px; margin:12px 0 6px 0; flex-wrap:wrap; }
    .toolbar-row .search { flex:1 1 280px; display:flex; align-items:center; gap:8px; background:#fff; border:1px solid #e5e7eb; border-radius:10px; padding:8px 12px; }
    .toolbar-row .search input { border:none; outline:none; width:100%; font-size:0.95rem; }
    .toolbar-row .filter-btn { background:#0d6efd; color:#fff; border:none; border-radius:10px; padding:8px 14px; display:inline-flex; align-items:center; gap:8px; font-weight:600; }
    .section-card.card-clean { border:1px solid #e5e7eb; background:#fff; border-radius:14px; box-shadow:0 4px 10px rgba(0,0,0,0.04); padding:20px; margin:0 auto; max-width:100%; overflow:hidden; }
    .gt-summary-grid {
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      gap:16px;
      margin-top:18px;
    }
    .gt-summary-card { border-radius:18px; padding:18px; color:#fff; position:relative; overflow:hidden; min-height:150px; box-shadow:0 10px 25px rgba(0,0,0,0.08); }
    .gt-summary-card .gt-icon { position:absolute; top:14px; right:14px; font-size:2.2rem; opacity:0.2; }
    .gt-summary-card .gt-value { font-size:2rem; font-weight:700; margin-bottom:6px; }
    .gt-summary-card .gt-label { font-weight:600; font-size:0.95rem; letter-spacing:0.2px; }
    .gt-summary-card .gt-subtext { font-size:0.8rem; opacity:0.85; margin-top:12px; display:flex; align-items:center; gap:6px; }
    .gt-status-wrap { margin-top:28px; }
    .gt-status-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:14px; }
    .gt-status-card { background:#f9fafb; border-radius:14px; padding:16px; border:1px solid #eef2ff; box-shadow:0 4px 10px rgba(59,130,246,0.08); }
    .gt-status-card .gt-status-label { font-weight:600; font-size:0.9rem; margin-bottom:6px; display:flex; align-items:center; gap:8px; }
    .gt-status-card .gt-status-value { font-size:1.8rem; font-weight:700; color:#111827; }
    .gt-status-card .gt-progress { margin-top:10px; height:6px; border-radius:999px; background:#e5e7eb; overflow:hidden; }
    .gt-status-card .gt-progress span { display:block; height:100%; border-radius:999px; }
    .gt-category-section { margin-top:35px; }
    .gt-category-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:18px; margin-top:18px; }
    .gt-category-card { border:1px solid #e5e7eb; border-radius:16px; padding:18px; box-shadow:0 8px 20px rgba(17,24,39,0.05); background:#fff; display:flex; flex-direction:column; gap:10px; }
    .gt-category-card .gt-category-header { display:flex; justify-content:space-between; align-items:flex-start; }
    .gt-category-card .gt-category-title { font-weight:600; color:#111827; font-size:1rem; }
    .gt-status-pill { border-radius:999px; padding:4px 10px; font-size:0.75rem; font-weight:600; color:#fff; display:inline-flex; align-items:center; gap:6px; }
    .gt-category-card .gt-meta-row { display:flex; gap:14px; font-size:0.85rem; color:#4b5563; }
    .gt-category-card .gt-meta-row span { display:flex; align-items:center; gap:6px; font-weight:500; }
    .gt-category-card .gt-meta-row span i {
      width:26px;
      height:26px;
      border-radius:8px;
      display:inline-flex;
      align-items:center;
      justify-content:center;
      font-size:0.85rem;
      background:#eef2ff;
      color:#312e81;
    }
    .gt-category-card .gt-meta-row span .fi-rr-users {
      background:rgba(16,185,129,0.15);
      color:#0f766e;
    }
    .gt-category-card .gt-meta-row span .fi-rr-star {
      background:rgba(251,191,36,0.2);
      color:#b45309;
    }
    .gt-date-row {
      font-size:0.8rem;
      color:#94a3b8;
      display:flex;
      gap:12px;
    }
    .gt-date-row span {
      display:flex;
      align-items:center;
      gap:6px;
    }
    .gt-date-row span i {
      color:#2563eb;
      font-size:0.85rem;
    }
    .gt-category-card .gt-progress-shell { height:6px; background:#e5e7eb; border-radius:999px; overflow:hidden; margin-top:4px; }
    .gt-category-card .gt-progress-shell span { display:block; height:100%; border-radius:999px; }
    .gt-category-empty { text-align:center; padding:30px; border:2px dashed #cbd5f5; border-radius:14px; font-size:0.95rem; color:#4b5563; background:#f8fbff; }
    .room-stats-grid {
      display:grid;
      grid-template-columns:repeat(3,minmax(0,1fr));
      gap:14px;
      margin-top:16px;
      width:100%;
      box-sizing:border-box;
    }
    .room-stat-card {
      border-radius:18px;
      padding:18px;
      color:#fff;
      position:relative;
      overflow:hidden;
      min-height:150px;
      box-shadow:0 10px 25px rgba(0,0,0,0.08);
    }
    .room-stat-card .stat-icon {
      position:absolute;
      top:14px;
      right:14px;
      font-size:2.2rem;
      opacity:0.2;
    }
    .room-stat-subtext {
      font-size:0.8rem;
      opacity:0.9;
      margin-top:10px;
      display:flex;
      align-items:center;
      gap:6px;
    }
    .room-stat-subtext i {
      font-size:0.9rem;
    }
    @media (max-width: 992px) {
      .gt-summary-grid {
        grid-template-columns:repeat(2,minmax(0,1fr));
      }
      .room-stats-grid {
        grid-template-columns:repeat(2,minmax(0,1fr));
      }
    }

    @media (max-width: 768px) {
      .gt-summary-grid {
        grid-template-columns:minmax(0,1fr);
      }
      .gt-summary-card { min-height: unset; }
      .room-stats-grid {
        grid-template-columns:minmax(0,1fr);
      }
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
    @include('partials.sidebar')
  </div>
  <div class="main-content">
  <div class="hero-wrap">
      <div class="welcomeCard" id="welcomeCard">
      <h2>
        Good day, {{ auth()->user()->user_fname ?? 'User' }} {{ auth()->user()->user_lname ?? '' }}!
      </h2>
      <p>Welcome to the PN Tasking Hub System.</p>
      <p>Please use the buttons below to access and manage the Room Tasking and General Tasking modules.</p>
  </div>
  <div class="page-header">
    <div class="tabs-wrap" style="display: flex; gap: 4px; margin: 0 0 20px 0; max-width: 500px;">
      <div class="nav-item" style="display: inline-block;">
        <a class="nav-link" href="#" id="tab-general" style="
          display: block;
          padding: 10px 20px;
          background: #f3f4f6;
          color: #4b5563;
          border-radius: 20px;
          text-decoration: none;
          font-weight: 500;
          width: 100%;
          border: 1px solid #e5e7eb;
          transition: all 0.2s ease;
        ">General Tasking</a>
      </div>
      <div class="nav-item" style="display: inline-block;">
        <a class="nav-link active" href="#" id="tab-room" style="
          display: block;
          padding: 10px 20px;
          background: #22bbea;
          color: white;
          border-radius: 20px;
          text-decoration: none;
          font-weight: 500;
           width: 100%;
          border: 1px solid #e5e7eb;
          transition: all 0.2s ease;
        ">Room Tasking</a>
      </div>
    </div>
  </div>

  <!-- Bulk Add Unassigned Students Modal -->
  <div id="bulkAddModal" class="modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:10001; padding:30px; overflow-y:auto;">
    <div class="modal-content" style="max-width:900px; width:100%; margin:40px auto;">
      <span class="close" onclick="closeBulkAddModal()">&times;</span>
      <div class="modal-header" style="border-bottom:1px solid #dee2e6; padding-bottom:12px; margin-bottom:12px;">
        <h3 style="margin:0; display:flex; align-items:center; gap:10px;">
          <i class="fi fi-rr-list-plus" style="color:#1e88e5;"></i>
          Bulk Add Unassigned Students
        </h3>
        <div id="bulkOccupantBadge" style="font-size:0.85rem; font-weight:600; color:#1e293b; background:#e0f2ff; border-radius:999px; padding:6px 14px;">
          Loading…
        </div>
      </div>
      <div class="modal-body" id="bulkAddBody">
        <div style="margin-bottom:12px; color:#6c757d;">Select one or more unassigned students that match this room’s occupant type.</div>
        <div id="bulkStudentsList" style="max-height:400px; overflow-y:auto; border:1px solid #e9ecef; padding:12px; border-radius:8px; background:white;"></div>
        <div id="bulkAddProgress" style="margin-top:12px; display:none; color:#0f5132; background:#d1e7dd; padding:8px; border-radius:6px;"></div>
      </div>
      <div class="modal-footer" style="border-top:1px solid #dee2e6; padding-top:12px; margin-top:12px; display:flex; justify-content:flex-end; gap:10px;">
        <button type="button" class="btn btn-secondary" onclick="closeBulkAddModal()">Cancel</button>
        <button type="button" class="btn btn-primary" id="bulkAddSubmitBtn" onclick="processBulkAddSelections()">Add Selected</button>
      </div>
    </div>
  </div>
  
  <!-- General Content (General Tasking) -->
  <div id="generalContent">
  <!-- Welcome Header -->
  <div id="generalStatsSection" class="section-card card-clean" style="margin-top:10px;">
    <div class="section-header" style="display:flex; justify-content: space-between; align-items:center;">
      <h3 class="section-title" style="margin:0; display:flex; align-items:center; gap:8px;">
        <i class="fi fi-rr-grid" style="color:#22bbea;"></i>
        General Tasking Overview
      </h3>
    </div>
    @php
      $overview = $generalTaskOverview ?? [];
      $counts = $overview['counts'] ?? [];
      $statuses = $counts['statuses'] ?? [];
      $totalCategories = $counts['total_categories'] ?? 0;
      $totalAssignments = $counts['total_assignments'] ?? 0;
      $totalStudentsGeneral = $counts['total_students'] ?? 0;
      $totalCoordinatorsGeneral = $counts['total_coordinators'] ?? 0;
      $overdueTasks = $counts['overdue'] ?? 0;
      $statusOrder = ['pending','completed'];
      $statusMeta = [
        'pending' => ['label' => 'Incomplete', 'color' => '#d13925ff', 'bg' => '#b62e0cff', 'icon' => 'fi fi-rr-time-add', 'progress' => 20],
        'completed' => ['label' => 'Completed', 'color' => '#16a34a', 'bg' => '#04860bff', 'icon' => 'fi fi-rr-check-circle', 'progress' => 100],
      ];
      $summaryCards = [
        ['label' => 'Incomplete Tasks', 'value' => $statuses['pending'] ?? 0, 'gradient' => $statusMeta['pending']['bg'], 'icon' => $statusMeta['pending']['icon'], 'subtext' => 'Not yet started'],
        ['label' => 'Completed Tasks', 'value' => $statuses['completed'] ?? 0, 'gradient' => $statusMeta['completed']['bg'], 'icon' => $statusMeta['completed']['icon'], 'subtext' => 'Marked as done'],
        ['label' => 'Assigned Students', 'value' => $totalStudentsGeneral, 'gradient' => 'rgba(12, 75, 156, 1)', 'icon' => 'fi fi-rr-users-alt', 'subtext' =>'All assigned students'],
      ];
      $categoryCards = collect($overview['categories'] ?? [])->sortBy('name')->values();
      $displayStatusTotals = array_intersect_key($statuses, array_flip($statusOrder));
      $totalStatusCount = max(array_sum($displayStatusTotals), 1);
    @endphp

    <div class="gt-summary-grid">
      @foreach($summaryCards as $card)
        <div class="gt-summary-card" style="background: {{ $card['gradient'] }};">
          <div class="gt-icon"><i class="{{ $card['icon'] }}"></i></div>
          <div class="gt-value">{{ $card['value'] }}</div>
          <div class="gt-label">{{ $card['label'] }}</div>
          <div class="gt-subtext">
            <i class="fi fi-rr-info"></i>
            <span>{{ $card['subtext'] }}</span>
          </div>
        </div>
      @endforeach
    </div>

    <div class="gt-status-wrap">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
        <h4 style="margin:0; font-size:1rem; font-weight:600; color:#111827;">Tasks by Status</h4>
        <span style="font-size:0.85rem; color:#6b7280;">{{ $totalAssignments }} total assignments</span>
      </div>
      <div class="gt-status-grid">
        @foreach($statusOrder as $statusKey)
          @php
            $meta = $statusMeta[$statusKey];
            $count = $statuses[$statusKey] ?? 0;
            $percent = $totalStatusCount ? round(($count / $totalStatusCount) * 100) : 0;
          @endphp
          <div class="gt-status-card" onclick="window.filterStudentsByStatus('{{ $statusKey }}')" style="cursor: pointer; transition: all 0.3s ease; user-select: none;" onmouseover="this.style.boxShadow='0 8px 16px rgba(0,0,0,0.15)'; this.style.transform='translateY(-2px)';" onmouseout="this.style.boxShadow=''; this.style.transform='translateY(0)';">
            <div class="gt-status-label" style="color: {{ $meta['color'] }};">
              <i class="{{ $meta['icon'] }}"></i>
              {{ $meta['label'] }}
            </div>
            <div class="gt-status-value">{{ $count }}</div>
            <div class="text-muted" style="font-size:0.8rem;">{{ $percent }}% of all assignments</div>
            <div class="gt-progress">
              <span style="width: {{ $percent }}%; background: {{ $meta['color'] }};"></span>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    <!-- Filtered Students by Status Modal -->
    <div id="filteredStudentsModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; padding:30px; overflow-y:auto;">
      <div style="background:#fff; border-radius:22px; max-width:1500px; width:calc(100% - 40px); margin:0 auto; box-shadow:0 30px 80px rgba(15,23,42,0.18); border:1px solid #e5e7eb;">
        <div style="padding:26px 32px; border-bottom:1px solid #e5e7eb; display:flex; justify-content:space-between; align-items:center;">
          <div>
            <h3 id="filteredStudentsTitle" style="margin:0; font-size:1.45rem; font-weight:700; color:#0f172a;">Tasks by Status</h3>
          </div>
          <button type="button" onclick="window.closeFilteredStudentsModal()" style="border:none; background:#f1f5f9; width:44px; height:44px; border-radius:50%; font-size:1.4rem; color:#475569; cursor:pointer;">×</button>
        </div>
        <div style="padding:20px 32px; border-bottom:1px solid #e5e7eb; display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end;">
          <div style="flex:1; min-width:220px;">
            <label style="font-size:0.82rem; font-weight:600; color:#475569;">Filter by Category</label>
            <select id="filteredStudentsCategory" class="form-select" style="border-radius:12px; border:1px solid #d1d5db;">
              <option value="all">All Categories</option>
            </select>
          </div>
          <div style="display:flex; gap:8px; align-items:flex-end;">
            <div>
              <label style="font-size:0.82rem; font-weight:600; color:#475569;">Filter by Date</label>
              <input type="date" id="filteredStudentsDateFilter" class="form-control" style="border-radius:12px;">
            </div>
            <button type="button" class="btn btn-secondary" style="height:40px;" onclick="window.clearFilteredStudentsFilter()">Clear</button>
          </div>
        </div>
        <div style="padding:0 32px 32px 32px;">
          <div id="filteredStudentsContent" style="padding:24px 0;">
            <div class="empty-state">Select a status card to view students.</div>
          </div>
        </div>
      </div>
    </div>

    <div class="gt-category-section">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <h4 style="margin:0; font-size:1rem; font-weight:600; color:#111827;">Category Insights</h4>
        <span style="font-size:0.85rem; color:#6b7280;">{{ $totalCategories }} Active {{ $totalCategories === 1 ? 'Category' : 'Categories' }}</span>
      </div>
      @if($categoryCards->isEmpty())
        <div class="gt-category-empty">
          <i class="fi fi-rr-folder-open" style="margin-right:6px;"></i>
          No active category assignments yet. Once you add assignments in General Tasking, they will appear here.
        </div>
      @else
        <div class="gt-category-grid">
          @foreach($categoryCards as $category)
            @php
              $statusKey = $category['status_key'] ?? 'pending';
              $meta = $statusMeta[$statusKey] ?? $statusMeta['pending'];
              $progressWidth = $meta['progress'];
              $startDate = $category['start_date'] ? \Carbon\Carbon::parse($category['start_date'])->format('M d, Y') : '—';
              $endDate = $category['end_date'] ? \Carbon\Carbon::parse($category['end_date'])->format('M d, Y') : '—';
            @endphp
            <div class="gt-category-card">
              <div class="gt-category-header">
                <div>
                  <div class="gt-category-title">{{ $category['name'] }}</div>
                  <p style="margin:4px 0 0 0; font-size:0.85rem; color:#6b7280;">{{ $category['description'] ?? 'No description available.' }}</p>
                </div>
                @if($statusKey !== 'in_progress')
                <span class="gt-status-pill" style="background: {{ $meta['color'] }};">
                  <i class="{{ $meta['icon'] }}"></i>
                  {{ $meta['label'] }}
                </span>
                @endif
              </div>
              <div class="gt-meta-row">
                <span><i class="fi fi-rr-users"></i>{{ $category['members'] }} members</span>
                <span><i class="fi fi-rr-star"></i>{{ $category['coordinators'] }} coordinator(s)</span>
              </div>
                     <div class="gt-date-row">
                <span><i class="fi fi-rr-calendar"></i>Start: {{ $startDate }}</span>
                <span><i class="fi fi-rr-calendar-clock"></i>Target: {{ $endDate }}</span>
              </div>
              <div class="gt-progress-shell">
                <span style="width: {{ $progressWidth }}%; background: {{ $meta['color'] }};"></span>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

  </div>
  </div>
  <!-- Room Tasking Content -->
  <div id="roomContent">
  <!-- Dashboard layout: left panel (floors) + center panel (stats + rooms) -->
  <div class="dashboard-layout" id="dashboardLayout" style="margin:0 auto; display:flex; flex-direction:column; gap:18px; align-items:stretch; padding:0 16px; box-sizing:border-box; max-width:1200px; width:100%;">
      <aside class="left-panel" id="leftPanel" aria-label="Floor navigation" style="float:left; width:260px; box-sizing:border-box; padding-right:12px;">
        <!-- Floor navigation will be moved here on load -->
      </aside>
      <section class="center-panel" id="centerPanel" style="width:100%; max-width:100%; margin:0 auto;">
        <div id="statsArea" style="width:100%; max-width:1200px; margin:0 auto; display:flex; flex-direction:column; gap:18px;"><!-- Stats will be moved here --></div>
        <div id="roomsArea" style="margin-top:18px; width:100%;"><!-- Rooms will be moved here --></div>
      </section>
    </div>

    <script>
      document.addEventListener('DOMContentLoaded', function() {

        const toggleBtn = document.getElementById('toggleRoomOverviewBtn');
        const section = document.getElementById('roomOverviewSection');
        const statsArea = document.getElementById('statsArea');
        const totalTasksEl = document.getElementById('statTotalRoomTasks');
        const studentsEl = document.getElementById('statStudentsOccupying');
        const totalRoomsEl = document.getElementById('statTotalRooms');
        const goToFloorsBtn = document.getElementById('goToFloorsBtn');
        const floorButtonsTop = document.getElementById('floorButtonsTop');
        const floorsTopAnchor = document.getElementById('floorsTopAnchor');
        const roomsContainer = document.getElementById('roomsContainer');
        // Use [] in PHP null coalesce to avoid Blade parse errors, then fallback to {} in JS
        const floorsData = (@json($floorsAndRooms ?? [])) || {};
        const roomStudentsData = (@json($roomStudents ?? [])) || {};

        function ordinal(n){
          n = parseInt(n,10)||0; const v=n%100; if(v>=11&&v<=13) return n+'th';
          const s=n%10; return n+(s==1?'st':s==2?'nd':s==3?'rd':'th');
        }

        function computeSnapshotMetrics(){
          let roomsCount = 0;
          let occupantsCount = 0;
          // Prefer authoritative backend-provided student list per room
          if (roomStudentsData && typeof roomStudentsData === 'object' && Object.keys(roomStudentsData).length > 0) {
            try {
              Object.values(roomStudentsData).forEach(list => {
                if (Array.isArray(list)) occupantsCount += list.length;
              });
            } catch(e) {}
          }
          if (floorsData && typeof floorsData === 'object') {
            Object.values(floorsData).forEach(rooms => {
              if (Array.isArray(rooms)) {
                roomsCount += rooms.length;
                // If we didn't get roomStudentsData, allow fallback where floorsData holds detailed room objects
                if (occupantsCount === 0) {
                  rooms.forEach(r => {
                    const occArr = (r && (r.assignments ?? r.students)) ? (r.assignments ?? r.students) : [];
                    const occCount = Array.isArray(occArr) ? occArr.length : (r?.assignments_count ?? 0);
                    occupantsCount += (occCount || 0);
                  });
                }
              }
            });
          }
          // Update DOM if elements exist
          if (totalRoomsEl) totalRoomsEl.textContent = roomsCount.toString();
          if (studentsEl) studentsEl.textContent = occupantsCount.toString();
          // Optional: allow backend to inject an accurate room tasks count via meta
          const metaTasks = document.querySelector('meta[name="room-tasks-count"]');
          if (metaTasks && totalTasksEl) {
            const v = parseInt(metaTasks.getAttribute('content') || '0', 10);
            if (!isNaN(v) && v >= 0) totalTasksEl.textContent = String(v);
          }

          // Fallback: if meta not available or still zero, try client-side storage used by Manage Room Tasks
          try {
            if (totalTasksEl && (totalTasksEl.textContent === '' || totalTasksEl.textContent === '0')) {
              const raw = localStorage.getItem('roomTasks_v1');
              if (raw) {
                const list = JSON.parse(raw);
                if (Array.isArray(list)) {
                  totalTasksEl.textContent = String(list.length);
                }
              }
            }
          } catch(e) { /* ignore parse errors */ }
        }

        function renderRoomsForFloor(floor){
          if(!roomsContainer) return;
          const rooms = (floorsData && floorsData[floor]) ? floorsData[floor] : [];
          roomsContainer.innerHTML = '';

          if(Array.isArray(rooms) && rooms.length){
              const wrapper = document.createElement('div');
              wrapper.className = 'rooms-wrapper';
              wrapper.style.display = 'grid';
              wrapper.style.gridTemplateColumns = 'repeat(auto-fill, minmax(280px, 1fr))';
              wrapper.style.gap = '16px';
              wrapper.style.width = '100%';

            // Ensure rooms is a proper array
            const roomsList = Array.isArray(rooms) ? rooms : Object.values(rooms || {});
            
            // Sort rooms by room number
            roomsList.sort((a, b) => {
              const numA = parseInt((a.room_number || a.number || a.id || '0').toString().replace(/\D/g, '')) || 0;
              const numB = parseInt((b.room_number || b.number || b.id || '0').toString().replace(/\D/g, '')) || 0;
              return numA - numB;
            });

            roomsList.forEach(r => {
              if (!r) return;
              
              // Safely get and format room number
              let number = '';
              try {
                if (r.room_number !== undefined && r.room_number !== null) {
                  number = String(r.room_number);
                } else if (r.number !== undefined && r.number !== null) {
                  number = String(r.number);
                } else if (r.id !== undefined && r.id !== null) {
                  number = String(r.id);
                }
                
                // Clean and validate the room number
                number = number.replace(/[^\w\s-]/g, '').trim();
                if (!number || number === 'null' || number === 'undefined') {
                  console.warn('Invalid room number:', r);
                  return;  // Skip if no valid room number
                }
              } catch (e) {
                console.error('Error processing room number:', e, r);
                return;  // Skip if there's an error
              }

              // Safely get occupants count
              let occ = 0;
              if (Array.isArray(r.assignments)) {
                occ = r.assignments.length;
              } else if (Array.isArray(r.students)) {
                occ = r.students.length;
              } else if (typeof r.assignments_count === 'number') {
                occ = r.assignments_count;
              }
              
              const cap = typeof r.capacity === 'number' ? r.capacity : 6;
              const occupantType = (r.occupant_type || 'both').toLowerCase();
              const occupantMeta = (() => {
                if (occupantType === 'female') return { text: 'Female Only', bg: '#fde5f3', color: '#9d174d' };
                if (occupantType === 'male') return { text: 'Male Only', bg: '#dbeafe', color: '#1d4ed8' };
                return { text: 'All Genders', bg: '#ecfccb', color: '#3f6212' };
              })();
              
              // Safely process assignments/students
              const assignmentsRaw = Array.isArray(r.assignments) ? r.assignments : 
                                   (Array.isArray(r.students) ? r.students : []);
              
              const filteredPeople = assignmentsRaw
                .filter(Boolean) // Remove any null/undefined entries
                .map(p => ({
                  name: (p.student_name || p.name || '').toString().trim(),
                  gender: (p.student_gender || p.gender || '').toLowerCase()
                }))
                .filter(p => {
                  if (!p.name) return false;
                  if (occupantType === 'female') return p.gender === 'f' || p.gender === 'female';
                  if (occupantType === 'male') return p.gender === 'm' || p.gender === 'male';
                  return true;
                });
              
              const pct = cap > 0 ? Math.max(0, Math.min(100, Math.round((occ / cap) * 100))) : 0;
              const occupied = occ > 0;

              // Create card element with proper data attributes
              const card = document.createElement('div');
              card.className = 'room-card';
              card.style.cssText = 'background:#fff; border:1px solid #e9ecef; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.03); transition:all 0.2s ease; display:flex; flex-direction:column; height:100%;';
              
              // Add hover effect
              card.onmouseenter = () => card.style.transform = 'translateY(-2px)';
              card.onmouseleave = () => card.style.transform = 'translateY(0)';
              
              // Ensure we have a valid number to display
              let displayNumber = 'Room';
              if (number && number !== 'null' && number !== 'undefined') {
                displayNumber = `Room ${number}`;
              }
              
              card.innerHTML = `
                <div class="room-header" style="display:flex; justify-content:space-between; align-items:center; padding:12px 16px; border-bottom:1px solid #f0f0f0;">
                  <h3 class="room-title" style="margin:0; font-size:1.1rem; font-weight:700; color:#1f2937; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                    ${displayNumber}
                  </h3>
                  <span class="room-status" style="font-size:0.7rem; padding:4px 10px; border-radius:9999px; background:${occupied ? '#eafaf0' : '#f3f4f6'}; color:${occupied ? '#198754' : '#6b7280'}; font-weight:600; border:1px solid ${occupied ? '#c3e6cb' : '#e5e7eb'}; white-space:nowrap;">
                    <i class="fi ${occupied ? 'fi-rr-user' : 'fi-rr-home'}" style="margin-right:4px; vertical-align:middle;"></i>
                    ${occupied ? 'Occupied' : 'Vacant'}
                  </span>
                </div>
                <div class="room-body" style="padding:16px; flex:1; display:flex; flex-direction:column;">
                  <div style="margin-bottom:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                      <span style="font-size:0.8rem; color:#6b7280; font-weight:500;">
                        <i class="fi fi-rr-users" style="margin-right:6px;"></i>Occupancy
                      </span>
                      <span style="font-weight:700; color:#111827; font-size:0.95rem;">
                        ${occ}<span style="color:#9ca3af; font-weight:500;">/${cap}</span>
                      </span>
                    </div>
                    <div style="height:6px; background:#f1f5f9; border-radius:6px; overflow:hidden; box-shadow:inset 0 1px 2px rgba(0,0,0,0.05);">
                      <span style="display:block; height:100%; background:${pct >= 90 ? '#ef4444' : pct >= 70 ? '#f59e0b' : '#10b981'}; width:${pct}%; transition:all 0.3s ease;"></span>
                    </div>
                  </div>
                  
                  ${occupantType !== 'both' ? `
                  <div style="margin-bottom:12px;">
                    <span style="display:inline-flex; align-items:center; padding:4px 10px; border-radius:999px; font-weight:600; font-size:0.75rem; background:${occupantMeta.bg}; color:${occupantMeta.color}; border:1px solid ${occupantType === 'male' ? 'rgba(29, 78, 216, 0.2)' : 'rgba(217, 70, 239, 0.2)'};">
                      <i class="fi ${occupantType === 'male' ? 'fi-rr-male' : 'fi-rr-female'}" style="margin-right:6px; font-size:0.8rem;"></i>
                      ${occupantMeta.text}
                    </span>
                  </div>` : ''}
                  
                  <div style="flex:1; display:flex; flex-direction:column;">
                    <div style="font-size:0.8rem; color:#6b7280; margin-bottom:6px; font-weight:500;">
                      <i class="fi fi-rr-user" style="margin-right:6px;"></i>Occupants
                    </div>
                    <div style="background:#f8f9fa; border:1px solid #f0f0f0; border-radius:8px; padding:8px; flex:1; min-height:60px; max-height:120px; overflow-y:auto;">
                      ${filteredPeople.length > 0 ? 
                        filteredPeople.map(p => `
                          <div style="padding:6px 8px; border-bottom:1px solid #f0f0f0; display:flex; align-items:center;">
                            <i class="fi ${p.gender === 'm' || p.gender === 'male' ? 'fi-rr-male' : 'fi-rr-female'}" 
                               style="margin-right:8px; color:${p.gender === 'm' || p.gender === 'male' ? '#3b82f6' : '#ec4899'}; font-size:0.9rem;"></i>
                            <span style="font-size:0.9rem; color:#374151; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                              ${p.name.replace(/[^\w\s-]/g, '')}
                            </span>
                          </div>
                        `).join('') 
                        : `
                        <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; height:100%; color:#9ca3af; padding:10px 0;">
                          <i class="fi fi-rr-user" style="font-size:1.2rem; margin-bottom:4px; opacity:0.7;"></i>
                          <span style="font-size:0.85rem;">No occupants</span>
                        </div>
                      `}
                    </div>
                  </div>
                </div>
                <div class="room-footer" style="padding:0 16px 16px 16px; display:flex; gap:10px;">
                  <button type="button" 
                          class="btn btn-capacity" 
                          onclick="openIndividualCapacityModal('${number.replace(/'/g, "\\'")}')" 
                          style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:8px 12px; font-size:0.85rem; border-radius:6px; font-weight:500; background:#f3f4f6; color:#374151; border:1px solid #e5e7eb; transition:all 0.2s ease; cursor:pointer; white-space:nowrap;">
                    <i class="fi fi-rr-edit" style="font-size:0.8rem;"></i> Edit
                  </button>
                  <button type="button" 
                          class="btn btn-checklist" 
                          onclick="window.location.href='/roomtask/${number.replace(/'/g, "\\'")}'" 
                          style="flex:1; display:inline-flex; align-items:center; justify-content:center; gap:6px; padding:8px 12px; font-size:0.85rem; border-radius:6px; font-weight:500; background:#22bbea; color:white; border:none; transition:all 0.2s ease; cursor:pointer; white-space:nowrap;">
                    <i class="fi fi-rr-clipboard-list" style="font-size:0.8rem;"></i> Checklist
                  </button>
                </div>`;

              wrapper.appendChild(card);
            });

            roomsContainer.appendChild(wrapper);
          } else {
            roomsContainer.innerHTML = `<div style='padding:12px; color:#6c757d;'>No rooms found for ${ordinal(floor)} floor.</div>`;
          }

          roomsContainer.classList.remove('hidden');
        }

        function buildFloorButtonsTop(){
          if (!floorButtonsTop) return;
          const floors = Object.keys(floorsData || {}).sort((a,b)=>Number(a)-Number(b));
          const suffix = n=>{ n=Number(n)||0; const v=n%100; if(v>=11&&v<=13) return 'th'; const s=n%10; return s==1?'st':s==2?'nd':s==3?'rd':'th'; };
          floorButtonsTop.innerHTML = floors.map(f=>`
            <button class="floor-btn" data-floor="${f}">
              <i class="fi fi-rr-floor" style="margin-right: 6px;"></i>
              ${f}${suffix(f)} Floor
            </button>
          `).join('');
          floorButtonsTop.querySelectorAll('.floor-btn').forEach(btn=>{
            btn.addEventListener('click', function(){
              const floor = this.getAttribute('data-floor');
              floorButtonsTop.querySelectorAll('.floor-btn').forEach(b=>b.classList.toggle('active', b.getAttribute('data-floor')===floor));
              renderRoomsForFloor(floor);
            });
          });
        }
        buildFloorButtonsTop();

        if (toggleBtn && section) {
          toggleBtn.addEventListener('click', function() {
            const isVisible = section.style.display !== 'none';
            section.style.display = isVisible ? 'none' : 'block';
            if (!isVisible) {
              // Populate snapshot metrics from floorsData (and optional meta for tasks)
              computeSnapshotMetrics();
              // Smooth scroll into view
              section.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
          });
        }

        // Make the View Floors and Rooms button open the in-dashboard floor/rooms UI
        if (goToFloorsBtn && floorButtonsTop) {
          goToFloorsBtn.addEventListener('click', function() {
            try {
              // Reveal top floor buttons area
              floorButtonsTop.classList.remove('hidden');
              floorButtonsTop.style.display='flex';
              // Scroll into view
              (floorsTopAnchor || floorButtonsTop).scrollIntoView({ behavior: 'smooth', block: 'start' });
              // Auto-click the first floor button to render rooms if none are shown yet
              const firstBtn = floorButtonsTop.querySelector('.floor-btn');
              if (firstBtn) {
                firstBtn.click();
                // After loading rooms, ensure rooms container is visible and scrolled
                if (roomsContainer) {
                  roomsContainer.classList.remove('hidden');
                  setTimeout(() => roomsContainer.scrollIntoView({ behavior: 'smooth', block: 'start' }), 300);
                }
              }
            } catch (e) { console.warn('goToFloorsBtn handler error', e); }
          });
        }

        function injectRoomOverview(){
          if (section && statsArea && !statsArea.contains(section)) {
            section.style.marginTop = '0';
            section.style.width = '100%';
            section.style.maxWidth = '100%';
            statsArea.insertBefore(section, statsArea.firstChild);
          }
        }

        function openFloorsFromHash(){
          if (location.hash === '#floors' && floorButtonsTop) {
            try {
              floorButtonsTop.classList.remove('hidden');
              floorButtonsTop.style.display='flex';
              const firstBtn = floorButtonsTop.querySelector('.floor-btn');
              if (firstBtn) {
                firstBtn.click();
              }
              if (roomsContainer) {
                roomsContainer.classList.remove('hidden');
                setTimeout(() => (floorsTopAnchor || floorButtonsTop).scrollIntoView({ behavior: 'smooth', block: 'start' }), 200);
              }
            } catch(e) { console.warn('hash open error', e); }
          }
        }
  // Initial load
  injectRoomOverview();
  computeSnapshotMetrics();
  // Ensure the occupying-students stat is accurate using the canonical in-memory model
  try { computeAndRenderTotalAssignedStudents(); } catch(e) { /* function may be defined later; ignore */ }
        openFloorsFromHash();
        // Handle future hash changes
        window.addEventListener('hashchange', openFloorsFromHash);

        // Tab switching: General Tasking / Room Tasking
        const tabGeneral = document.getElementById('tab-general');
        const tabRoom = document.getElementById('tab-room');
        const generalSection = document.getElementById('generalContent');
        const roomSection = document.getElementById('roomContent');

        function updateGeneralStats(){
          try {
            const catEl = document.getElementById('genTotalCategories');
            const asgEl = document.getElementById('genActiveAssignments');
            const stuEl = document.getElementById('genTotalStudents');
            if (!catEl || !asgEl || !stuEl) return;
            fetch('/api/current-assignments')
              .then(r=>r.json())
              .then(data=>{
                if (!data || typeof data !== 'object') return;
                const categories = Object.keys(data);
                const activeAssignments = categories.length;
                let totalStudents = 0;
                const mainAreaSet = new Set();
                categories.forEach(k=>{
                  const item = data[k] || {};
                  if (typeof item.total_members === 'number') {
                    totalStudents += item.total_members;
                  } else {
                    const m25 = Array.isArray(item.members_2025) ? item.members_2025.length : 0;
                    const m26 = Array.isArray(item.members_2026) ? item.members_2026.length : 0;
                    const m27 = Array.isArray(item.members_2027) ? item.members_2027.length : 0;
                    totalStudents += (m25 + m26 + m27);
                  }
                  // Heuristic main-area grouping: use first word token of category name
                  try {
                    const base = String(k).trim();
                    if (base.length) {
                      const token = base.split(/\s+/)[0].toLowerCase();
                      if (token) mainAreaSet.add(token);
                    }
                  } catch(e) {}
                });
                catEl.textContent = String(categories.length);
                asgEl.textContent = String(activeAssignments);
                stuEl.textContent = String(totalStudents);
                const mainEl = document.getElementById('genMainAreas');
                if (mainEl) mainEl.textContent = String(mainAreaSet.size);
              })
              .catch(()=>{});
          } catch (e) {}
        }

        function showGeneral(){
          if (tabGeneral && tabRoom) { tabGeneral.classList.add('active'); tabRoom.classList.remove('active'); }
          if (generalSection) generalSection.style.display = 'block';
          if (roomSection) roomSection.style.display = 'none';
          updateGeneralStats();
        }
        function showRoom(){
          if (tabGeneral && tabRoom) { tabRoom.classList.add('active'); tabGeneral.classList.remove('active'); }
          if (generalSection) generalSection.style.display = 'none';
          if (roomSection) roomSection.style.display = 'block';
        }

        if (tabGeneral) tabGeneral.addEventListener('click', (e)=>{ e.preventDefault(); showGeneral(); });
        if (tabRoom) tabRoom.addEventListener('click', (e)=>{ e.preventDefault(); showRoom(); });

        // Default to Room Tasking view on load (matches current active pill)
        showRoom();
        // Preload general stats in background
        updateGeneralStats();
      });
    </script>

    <!-- Management Cards removed as requested -->
    <div id="floors" style="height: 1px; visibility: hidden;"></div>

    <!-- Snapshot Stat Cards -->
    <div id="roomOverviewSection" class="section-card card-clean" style="display:block; margin-top: 0; width:100%; box-sizing:border-box;">
      <div class="section-header" style="display:flex; justify-content: space-between; align-items:center;">
        <h3 class="section-title" style="margin:0; display:flex; align-items:center; gap:8px;">
          <i class="fi fi-rr-list" style="color:#22bbea;"></i>
         Room & Task Assignments Overview
        </h3>
        <a id="goToFloorsBtn" href="#floors" class="btn btn-primary">View Floors and Rooms</a>
      </div>
      <div class="room-stats-grid">
        <div class="room-stat-card" style="background:#1d4ed8;">
          <div class="stat-icon"><i class="fi fi-rr-list-check"></i></div>
          <div class="stat-number" id="statTotalRoomTasks" style="font-size:2rem; font-weight:700; margin-bottom:6px;">25</div>
          <div class="stat-label" style="font-size:0.95rem; opacity:0.9;">Room Task Assignments</div>
          <div class="room-stat-subtext"><i class="fi fi-rr-time-forward"></i><span>Currently applied task templates</span></div>
        </div>
        <div class="room-stat-card" style="background:#15803d;">
          <div class="stat-icon"><i class="fi fi-rr-users"></i></div>
          @php
              $totalStudents = 0;
              if (!empty($roomStudents) && is_array($roomStudents)) {
                  foreach ($roomStudents as $room => $students) {
                      if (is_array($students)) {
                          $totalStudents += count($students);
                      }
                  }
              }
          @endphp
          <div class="stat-number" id="statStudentsOccupying" style="font-size:2rem; font-weight:700; margin-bottom:6px;">{{ $totalStudents }}</div>
          <div class="stat-label" style="font-size:0.95rem; opacity:0.9;">Total Student Occupancy</div>
          <div class="room-stat-subtext"><i class="fi fi-rr-users-alt"></i><span>Students currently assigned</span></div>
        </div>
        <div class="room-stat-card" style="background:#f97316;">
          <div class="stat-icon"><i class="fi fi-rr-door-closed"></i></div>
          <div class="stat-number" id="statTotalRooms" style="font-size:2rem; font-weight:700; margin-bottom:6px;">15</div>
          <div class="stat-label" style="font-size:0.95rem; opacity:0.9;">Total Rooms</div>
          <div class="room-stat-subtext"><i class="fi fi-rr-home"></i><span>Rooms monitored this cycle</span></div>
        </div>
      </div>

    @php
      $occupancy = $occupancySummary ?? [];
      $occupancyRatings = collect($occupancy['ratings'] ?? []);
      $totalOccupancyRooms = $occupancy['total_rooms'] ?? ($totalTemplateRooms ?? 0);
      $totalOccupancyStudents = $occupancy['total_students'] ?? ($totalStudents ?? 0);
    @endphp
    @if($occupancyRatings->isNotEmpty())
      <div class="occupancy-summary-card">
        <div class="occupancy-summary-header">
          <div>
            <h3>Occupancy Snapshot</h3>
            <p>Quick view of rooms grouped by capacity rating.</p>
          </div>
          <div class="occupancy-summary-meta">
            <span><i class="fi fi-rr-home"></i>{{ $totalOccupancyRooms }} rooms</span>
            <span><i class="fi fi-rr-users"></i>{{ $totalOccupancyStudents }} students</span>
          </div>
        </div>
        <div class="occupancy-ratings-row">
          @foreach($occupancyRatings as $rating)
            @php
              $accent = $rating['accent'] ?? '#22bbea';
              $count = (int) ($rating['count'] ?? 0);
              $percent = (int) ($rating['percent'] ?? 0);
              $bgColor = match($accent) {
                '#dc2626' => '#fee2e2',
                '#f59e0b' => '#fef3c7',
                '#10b981' => '#dcfce7',
                default => '#e0f2fe'
              };
              $borderColor = match($accent) {
                '#dc2626' => '#fecaca',
                '#f59e0b' => '#fcd34d',
                '#10b981' => '#86efac',
                default => '#7dd3fc'
              };
            @endphp
            <div class="occupancy-rating-card" style="background: {{ $bgColor }}; border: 2px solid {{ $borderColor }}; border-radius: 9px; padding: 10px; flex: 1; min-width: 170px;">
              <!-- Header with Icon and Title -->
              <div style="display: flex; align-items: flex-start; gap: 8px; margin-bottom: 8px;">
                <div style="width: 28px; height: 28px; background: {{ $accent }}; border-radius: 5px; display: flex; align-items: center; justify-content: center; color: white; font-size: 0.9rem; flex-shrink: 0;">
                  @if($accent === '#dc2626')
                    ⊘
                  @elseif($accent === '#f59e0b')
                    ◐
                  @else
                    ◯
                  @endif
                </div>
                <div style="flex: 1;">
                  <h4 style="margin: 0; font-weight: 700; color: #1f2937; font-size: 0.85rem; line-height: 1.05;">{{ $rating['title'] ?? '—' }}</h4>
                  <p style="margin: 1px 0 0 0; font-size: 0.7rem; color: #6b7280; line-height: 1.05;">{{ $rating['subtitle'] ?? '' }}</p>
                </div>
              </div>

              <!-- Big Number Display -->
              <div style="margin-bottom: 8px;">
                <div style="font-size: 1.6rem; font-weight: 700; color: {{ $accent }}; line-height: 1; margin-bottom: 1px;">{{ $count }}</div>
                <div style="font-size: 0.7rem; color: #6b7280; line-height: 1.1;">of {{ $totalOccupancyRooms }} rooms</div>
                <div style="font-size: 0.65rem; color: #6b7280; line-height: 1.1;">{{ $rating['description'] ?? '' }}</div>
              </div>

              <!-- Status Row -->
              <div style="background: white; padding: 6px 8px; border-radius: 5px; margin-bottom: 7px; display: flex; justify-content: space-between; align-items: center; gap: 4px;">
                <span style="font-size: 0.65rem; font-weight: 600; color: #4b5563;">{{ $rating['status_label'] ?? 'Capacity Status' }}</span>
                <span style="font-size: 0.75rem; font-weight: 700; color: {{ $accent }};">{{ $rating['status_value'] ?? '—' }}</span>
              </div>

              <!-- Progress Bar -->
              <div style="margin-bottom: 7px;">
                <div style="height: 4px; background: rgba(0,0,0,0.1); border-radius: 2px; overflow: hidden;">
                  <div style="height: 100%; background: {{ $accent }}; width: {{ min(100, max(0, $percent)) }}%; transition: width 0.3s ease;"></div>
                </div>
              </div>

              <!-- Percentage at Bottom -->
              <div style="text-align: right;">
                <div style="font-size: 0.95rem; font-weight: 700; color: {{ $accent }}; line-height: 1;">{{ $percent }}%</div>
                <div style="font-size: 0.65rem; color: #6b7280; line-height: 1.05;">of rooms</div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    @endif

    <div class="task-template-section section-card card-clean" style="margin-top:18px;">
      @php
        $templateOverview = $taskTemplateOverview ?? [];
        $totalTemplates = $templateOverview['total_templates'] ?? 0;
        $totalTemplateRooms = $templateOverview['total_rooms'] ?? 0;
        $templatesList = collect($templateOverview['templates'] ?? []);
        $maxTasksCount = max(1, (int) $templatesList->max('tasks_count'));
        $templateIconPalette = [
          'tasks' => '#2563eb',
          'coverage' => '#10b981',
          'remaining' => '#f97316',
        ];
      @endphp
      <div class="task-template-header">
        <h3 style="margin:0; display:flex; align-items:center; gap:10px; font-size:1.1rem;">
          <i class="fi fi-rr-layout-fluid" style="color:#4f46e5;"></i>
          Task Templates Overview
        </h3>
        <div class="task-template-metrics">
          <span><i class="fi fi-rr-list" style="color:#2563eb;"></i>{{ $totalTemplates }} templates</span>
          <span><i class="fi fi-rr-building" style="color:#10b981;"></i>{{ $totalTemplateRooms }} rooms tracked</span>
        </div>
      </div>
      @if($templatesList->isEmpty())
        <div class="task-template-empty">
          <i class="fi fi-rr-info"></i>
          No task templates have been applied yet. Create templates in Manage Room Tasks to see them here.
        </div>
      @else
        <div class="task-template-list">
          @foreach($templatesList as $index => $template)
            @php
              $progressPercent = (int) ($template['coverage_percent'] ?? 0);
              $roomsApplied = (int) ($template['rooms_applied'] ?? 0);
              $tasksCount = (int) ($template['tasks_count'] ?? 0);
              $tasksPercent = $maxTasksCount > 0 ? max(8, round(($tasksCount / $maxTasksCount) * 100)) : 0;
              $roomsRemaining = max(0, $totalTemplateRooms - $roomsApplied);
              $remainingPercent = $totalTemplateRooms > 0 ? 100 - $progressPercent : 0;
            @endphp
            <div class="task-template-card">
              <div class="template-row">
                <div class="template-info">
                  <div class="task-template-index">{{ $index + 1 }}</div>
                  <div class="task-template-meta">
                    <h4>{{ strtoupper($template['name'] ?? 'Untitled Task') }}</h4>
                    <p>{{ $template['description'] ?? 'No description available.' }}</p>
                  </div>
                </div>
                <div class="task-template-usage">
                  <span class="usage-title">Applied to {{ $roomsApplied }} {{ \Illuminate\Support\Str::plural('room', $roomsApplied) }}</span>
                  <strong>{{ $progressPercent }}% coverage</strong>
                </div>
              </div>
              <div class="template-progress-grid">
                <div class="template-progress-card">
                  <div class="template-progress-label"><i class="fi fi-rr-clipboard-check" style="color: {{ $templateIconPalette['tasks'] }};"></i> Tasks generated</div>
                  <div class="task-progress-bar">
                    <div style="width: {{ min(100, $tasksPercent) }}%;"></div>
                  </div>
                  <div class="template-progress-meta">{{ $tasksCount }} {{ \Illuminate\Support\Str::plural('task', $tasksCount) }}</div>
                </div>
                <div class="template-progress-card">
                  <div class="template-progress-label"><i class="fi fi-rr-building" style="color: {{ $templateIconPalette['coverage'] }};"></i> Room coverage</div>
                  <div class="task-progress-bar">
                    <div style="width: {{ $progressPercent }}%;"></div>
                  </div>
                  <div class="template-progress-meta">{{ $progressPercent }}% of {{ $totalTemplateRooms }} rooms</div>
                </div>
                <div class="template-progress-card">
                  <div class="template-progress-label"><i class="fi fi-rr-home-heart" style="color: {{ $templateIconPalette['remaining'] }};"></i> Rooms remaining</div>
                  <div class="task-progress-bar warning">
                    <div style="width: {{ $remainingPercent }}%;"></div>
                  </div>
                  <div class="template-progress-meta">{{ $roomsRemaining }} {{ \Illuminate\Support\Str::plural('room', $roomsRemaining) }} to cover</div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      @endif
    </div>

    {{-- Quick Actions Section (disabled)
    <div class="main-cards" style="margin-top: 30px;">
      @if((auth()->user()->user_role ?? '') !== 'inspector')
      <div class="card" style="min-width: 1150px;">
        <h2>
          <i class="fi fi-rr-settings" style="margin-right: 8px; color: #22bbea;"></i>
          Quick Actions
        </h2>
        <p>Manage room capacity and access room management tools</p>
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 20px;">
          <button id="capacitySettingsBtn" style="
            background: #28a745;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            justify-content: center;
            min-width: 120px;
          " onmouseover="this.style.background='#218838'" onmouseout="this.style.background='#28a745'">
            <i class="fi fi-rr-settings" style="font-size: 1rem;"></i>
            Set Capacity for All Rooms
          </button>
          <button onclick="openAddFloorModal()" style="
            background: #28a745;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            justify-content: center;
            min-width: 120px;
          " onmouseover="this.style.background='#218838'" onmouseout="this.style.background='#28a745'">
            <i class="fi fi-rr-plus" style="font-size: 1rem;"></i>
            Add Floor
          </button>
          <button onclick="openNewRoomModal()" style="
            background: #0d6efd;
            border: none;
            padding: 6px 12px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            justify-content: center;
            min-width: 120px;
          " onmouseover="this.style.background='#0b5ed7'" onmouseout="this.style.background='#0d6efd'">
            <i class="fi fi-rr-plus" style="font-size: 1rem;"></i>
            Add Room
          </button>
          <button onclick="openDeleteFloorModal()" style="
            background: #dc3545;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
            justify-content: center;
            min-width: 120px;
          " onmouseover="this.style.background='#c82333'" onmouseout="this.style.background='#dc3545'">
            <i class="fi fi-rr-trash" style="font-size: 1rem;"></i>
            Remove floor
          </button>
        </div>
  </div>
  @endif
    </div>
    --}}
    <!-- Floors & Rooms Section (Top Controls above room cards) -->
    <div id="floorsTopAnchor"></div>
    <div class="floor-navigation" style="margin: 20px 0 15px 0; padding: 0 10px;">
      <h3 style="margin: 0 0 12px 0; color: #2d3748; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <i class="fi fi-rr-layers" style="color: #4a5568;"></i>
        Floor Navigation
      </h3>
      <div id="floorButtonsTop" class="floor-buttons" style="display: flex; gap: 8px; flex-wrap: wrap;"></div>
    </div>
    <!-- Rooms Container -->
  <div id="roomsContainer" class="rooms-container hidden"></div>
  </div> <!-- end roomContent -->
  </div>
</div>

  <!-- Student Modal -->
  <div id="studentModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2 id="modalTitle">Add Student</h2>
      <form id="studentForm">
        <input type="hidden" id="roomNumber" name="roomNumber">
        <input type="hidden" id="actionType" name="actionType" value="add">
        <div id="editStudentSelect" class="form-group" style="display: none;">
          <label for="existingStudent">Select Student:</label>
          <select id="existingStudent" name="existingStudent" class="form-control">
            <option value="">-- Select a student --</option>
          </select>
        </div>
        <div id="addStudentSelect" class="form-group" style="display: none;">
          <label for="validStudentSelect">Select Student from  PNPh Management System:</label>
          <select id="validStudentSelect" name="validStudentSelect" class="form-control">
            <option value="">-- Loading students... --</option>
          </select>
          <small style="color: #6c757d;">Only students from the database can be assigned</small>
        </div>
        <div class="form-group" id="manualStudentInput">
          <label for="studentName">Student Name:</label>
          <input type="text" id="studentName" name="studentName" required>
          <small style="color: #6c757d;">Or select from dropdown above</small>
        </div>
        <div class="form-actions">
          <button type="submit" class="submit-btn">Save</button>
          <button type="button" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- New Room Modal -->
  <div id="newRoomModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Add New Room</h2>
      <form id="newRoomForm">
        <div class="form-group">
          <label for="newRoomNumber">Room Number:</label>
          <input type="number" id="newRoomNumber" name="room_number" required min="200" max="50000">
          <small style="color: #6c757d;">Enter a specific room number (e.g. 206). Must use the floor prefix (e.g. 2xx for 2nd floor).</small>
        </div>
        <div class="form-actions">
          <button type="submit" class="submit-btn">Add Room</button>
          <button type="button" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Add Floor Modal -->
  <div id="addFloorModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2><i class="fi fi-rr-plus" style="margin-right:8px;color:#2196f3;"></i> Add New Floor</h2>
      <div style="background: #e3f2fd; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #2196f3;">
        <h4 style="margin: 0 0 8px 0; color: #1565c0;"><i class="fi fi-rr-notes" style="margin-right:6px;"></i> Floor Creation Guidelines</h4>
        <p style="margin: 0; font-size: 0.9rem; color: #1976d2;">
          • You may add any floor 2 and above<br>
          • New floors will be created with the specified number of rooms<br>
          • Room numbers will follow the pattern: [Floor][01-05] (e.g., 201, 202, 203... for 2nd floor)
        </p>
      </div>
      <form id="addFloorForm">
        <div class="form-group">
          <label for="newFloorNumber">Floor Number:</label>
          <input type="number" id="newFloorNumber" name="floor_number" required min="2" max="99">
          <small style="color: #6c757d;">Enter a floor number (2 or above)</small>
        </div>
        <div class="form-group">
          <label for="numRooms">Number of Rooms to Create:</label>
          <input type="number" id="numRooms" name="num_rooms" placeholder="e.g. 5" min="1" max="200">
        </div>
        <div class="form-actions">
          <button type="submit" class="submit-btn"><i class="fi fi-rr-plus" style="margin-right:6px;"></i> Create Floor</button>
          <button type="button" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Delete Floor Modal -->
  <div id="deleteFloorModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2><i class="fi fi-rr-trash" style="margin-right:8px;color:#dc3545;"></i> Delete Floor</h2>
      <div style="background: #ffebee; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #f44336;">
        
      </div>
      <form id="deleteFloorForm">
        <div class="form-group">
          <label for="floorToDelete">Select Floor to Remove:</label>
          <select id="floorToDelete" name="floor_to_delete" required>
            <option value="">-- Select a floor --</option>
          </select>
          <small style="color: #6c757d;">You may delete any floor 2 and above</small>
        </div>
        <div class="form-actions">
          <button type="submit" class="submit-btn" style="background: #dc3545;"><i class="fi fi-rr-trash" style="margin-right:6px;"></i> Delete Floor</button>
          <button type="button" class="cancel-btn">Cancel</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Room Capacity Settings Modal -->
  <div id="capacityModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>
        <i class="fi fi-rr-settings"></i>
        Room Capacity Settings
      </h2>
      <!-- capacity-info card removed per request -->
      <form id="capacityForm">
        <div class="form-group">
          <label for="roomCapacity">Students per Room:</label>
          <input type="number" id="roomCapacity" name="capacity" min="1" max="20" value="6" required>
          <small style="color: #6c757d;">Default: 6 students per room (Range: 1-20)</small>
        </div>
        <!-- current-stats removed per request (was showing Loading...) -->
      </form>
      <div class="form-actions">
        <button type="button" class="submit-btn" onclick="applyCapacitySettings()">
          <i style="margin-right:6px;"></i>
          Save
        </button>
        <button type="button" class="cancel-btn">Cancel</button>
      </div>
    </div>
  </div>

  <!-- Individual Room Edit Modal -->
  <div id="individualCapacityModal" class="modal">
    <div class="modal-content" style="max-width: 800px; width: 90%;">
      <span class="close">&times;</span>
      <div class="modal-header" style="border-bottom: 1px solid #dee2e; padding-bottom: 15px; margin-bottom: 20px;">
        <h2 style="margin: 0; display: flex; align-items: center; gap: 10px;">
          <i class="fi fi-rr-edit"></i>
          Edit Room - <span id="individualRoomNumber">Room XXX</span>
        </h2>
      </div>

      <div class="modal-body">
        <form id="individualCapacityForm">
          <input type="hidden" id="targetRoomNumber" name="roomNumber">
          <input type="hidden" id="individualRoomOccupantType" name="occupant_type" value="both">
          <!-- Enhanced Capacity and Assignment Controls -->
          <!-- Hidden form elements for JS compatibility -->
          <div style="display:none;">
            <!-- Capacity Controls -->
            <input type="hidden" id="individualRoomCapacity" name="capacity" value="6">
            <input type="checkbox" id="enableAutoAssignment" checked>
            
            <!-- Gender Capacities -->
            <input type="hidden" id="individualMaleCapacity" name="male_capacity" value="">
            <input type="hidden" id="individualFemaleCapacity" name="female_capacity" value="">
            <input type="checkbox" id="autoAssignMale" name="auto_assign_male">
            <input type="checkbox" id="autoAssignFemale" name="auto_assign_female">
            
            <!-- Batch Capacities -->
            <input type="hidden" id="male2025Capacity" name="male_capacity_2025" value="">
            <input type="hidden" id="female2025Capacity" name="female_capacity_2025" value="">
            <input type="hidden" id="male2026Capacity" name="male_capacity_2026" value="">
            <input type="hidden" id="female2026Capacity" name="female_capacity_2026" value="">
            <input type="hidden" id="individualAssignedBatch" name="assigned_batch" value="">
            <input type="checkbox" id="autoAssignMale2025">
            <input type="checkbox" id="autoAssignFemale2025">
            <input type="checkbox" id="autoAssignMale2026">
            <input type="checkbox" id="autoAssignFemale2026">
          </div>

          <!--  Section -->
          <div class="student-management-section" id="studentManagementSection" style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-top: 15px; border: 1px solid #e9ecef;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e9ecef; gap: 12px; flex-wrap: wrap;">
              <div>
                <h5 style="margin: 0; color: #2c3e50; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                  <i class="fi fi-rr-users-alt" style="color: #3498db; font-size: 1.2rem;"></i>
                  Student Management
                </h5>
                <p style="margin: 4px 0 0 0; color: #6c757d; font-size: 0.9rem;">
                  View, add, edit, or remove students assigned to this room
                </p>
              </div>
              <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <button type="button" class="btn btn-outline-primary" onclick="openBulkAddModal()" style="display:flex; align-items:center; gap:8px; padding:10px 14px; font-weight:500;">
                  <i class="fi fi-rr-list-plus"></i>
                  <span>Bulk Add Unassigned</span>
                </button>
                <button type="button" class="btn btn-success" onclick="addNewStudent()" style="display: flex; align-items: center; gap: 8px; padding: 10px 16px; font-weight: 500;">
                  <i class="fi fi-rr-user-add"></i>
                  <span>Add Student</span>
                </button>
              </div>
            </div>
            <div id="currentStudentsList">
              <div style="text-align: center; padding: 20px; color: #6c757d;">
                <i class="fi fi-rr-spinner" style="animation: spin 1s linear infinite; font-size: 1.5rem; margin-bottom: 10px; display: block;"></i>
                Loading current students...
              </div>
            </div>
          </div>

          <!-- Impact Analysis -->
          <div class="capacity-impact" id="capacityImpact" style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; display: none;">
            <strong><i class="fi fi-rr-exclamation"></i> Impact Analysis:</strong>
            <div id="impactContent"></div>
          </div>
        </form>
      </div>

      <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 20px;">
        <button type="button" class="btn btn-secondary" onclick="closeIndividualCapacityModal()">Cancel</button>
        <button type="button" class="btn btn-primary submit-btn" onclick="applyIndividualCapacity()">Save Changes</button>
      </div>
    </div>
  </div>

  <script>
    // Global student management functions (accessible to onclick handlers)
    function openEditStudentModal(currentName, index) {
      showStudentEditModal(currentName, 'edit');
    }

    function confirmRemoveStudent(studentName, index) {
      showConfirmationModal(
        'Remove Student',
        `Are you sure you want to remove "${studentName}" from this room?`,
        'Remove',
        'btn-danger',
        () => removeStudentFromRoom(studentName)
      );
    }

    function addNewStudent() {
      showStudentEditModal('', 'add');
    }

    // Enhanced student edit modal
    function showStudentEditModal(currentName, mode) {
      const isEdit = mode === 'edit';
      const title = isEdit ? 'Edit Student Assignment' : 'Add New Student';
      const buttonText = isEdit ? 'Update Assignment' : 'Add Student';
      const buttonClass = isEdit ? 'btn-primary' : 'btn-success';

      const modalHtml = `
        <div id="studentEditModal" class="modal" style="display: block; z-index: 10000;">
          <div class="modal-content" style="max-width: 550px; width: 90%;">
            <span class="close" onclick="closeStudentEditModal()">&times;</span>
            <div class="modal-header" style="border-bottom: 1px solid #dee2e6; padding-bottom: 15px; margin-bottom: 20px;">
              <h3 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fi fi-rr-${isEdit ? 'edit' : 'user-add'}"></i>
                ${title}
              </h3>
            </div>
            <div class="modal-body">
              <div style="margin-bottom: 20px; position: relative;">
                <label for="studentNameInput" class="form-label" style="display: block; margin-bottom: 8px; font-weight: 500;">
                  <i class="fi fi-rr-user" style="margin-right: 6px;"></i>
                  Student Name
                </label>
                <div style="position: relative;">
                  <input type="text" id="studentNameInput" class="form-control"
                         value="${currentName}" placeholder="Start typing student name..."
                         style="width: 100%; padding: 12px 40px 12px 12px; border: 1px solid #ddd; border-radius: 6px; font-size: 1rem; transition: border-color 0.3s;">
                  <div id="inputLoadingSpinner" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); display: none;">
                    <i class="fi fi-rr-spinner" style="animation: spin 1s linear infinite; color: #007bff;"></i>
                  </div>
                </div>
                <div id="validationMessage" style="margin-top: 6px; font-size: 0.875rem; display: none;"></div>
                <small class="form-text text-muted" style="margin-top: 4px; display: block;">
                  <i class="fi fi-rr-info" style="margin-right: 4px;"></i>
                  Only students from the  PNPh Management System can be assigned. Type at least 2 characters to see suggestions.
                </small>
              </div>
              <div id="studentSuggestions" style="display: none; border: 1px solid #ddd; border-radius: 6px; max-height: 250px; overflow-y: auto; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);"></div>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 20px;">
              <button type="button" class="btn btn-secondary" onclick="closeStudentEditModal()">Cancel</button>
              <button type="button" id="submitStudentBtn" class="btn ${buttonClass}" onclick="processStudentEdit('${currentName}', '${mode}')" disabled>${buttonText}</button>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHtml);

      // Setup enhanced autocomplete functionality
      setupEnhancedStudentAutocomplete(mode);

      // Focus on input and select text if editing
      const input = document.getElementById('studentNameInput');
      input.focus();
      if (isEdit && currentName) {
        input.select();
      }
    }

    async function validateAndUpdateStudentName(oldName, newName) {
      try {
        console.log('validateAndUpdateStudentName called:', { oldName, newName });
        const roomNumber = document.getElementById('targetRoomNumber').value;
        const response = await fetch('/api/room/edit-student', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            room: roomNumber,
            old_name: oldName,
            new_name: newName
          })
        });

        // Handle different HTTP status codes
        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));

          if (response.status === 422) {
            const error = new Error(errorData.message || 'Validation failed');
            error.suggestions = errorData.suggestions;
            error.errorType = errorData.error_type;
            throw error;
          } else if (response.status === 404) {
            const error = new Error(errorData.message || 'Student not found');
            error.suggestions = errorData.suggestions;
            error.errorType = errorData.error_type;
            throw error;
          } else if (response.status === 500) {
            throw new Error('Server error. Please try again later.');
          } else {
            throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
          }
        }

        const data = await response.json();
        if (!data.success) {
          const error = new Error(data.message || 'Failed to update student');
          error.suggestions = data.suggestions;
          error.errorType = data.error_type;
          throw error;
        }

        // Show success message
        showAlert('success', data.message || 'Student updated successfully');

        // Update dashboard immediately with returned data
        if (data.updated_room_data) {
          updateDashboardWithRoomData(data.updated_room_data);
        } else {
          // Fallback to API call if no data returned
          await syncDashboardAfterStudentChange(roomNumber);
        }

        // Refresh the modal to show updated student list with delay
        setTimeout(() => {
          openIndividualCapacityModal(roomNumber);
        }, 1000);

      } catch (error) {
        console.error('Error updating student name:', error);

        // Enhanced error handling with suggestions
        let errorMessage = getUserFriendlyErrorMessage(error.message);

        // Add suggestions if available
        if (error.suggestions && error.suggestions.length > 0) {
          errorMessage += '\n\nSuggestions:\n• ' + error.suggestions.join('\n• ');
        }

        showAlert('error', errorMessage);
      }
    }

    // This function is replaced by the enhanced version below at line 1673

    // Enhanced student management functions
    function closeStudentEditModal() {
      const modal = document.getElementById('studentEditModal');
      if (modal) {
        modal.remove();
      }
    }

    function showConfirmationModal(title, message, buttonText, buttonClass, onConfirm) {
      const modalHtml = `
        <div id="confirmationModal" class="modal" style="display: block; z-index: 10001;">
          <div class="modal-content" style="max-width: 400px; width: 90%;">
            <div class="modal-header" style="border-bottom: 1px solid #dee2e6; padding-bottom: 15px; margin-bottom: 20px;">
              <h4 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                <i class="fi fi-rr-exclamation-triangle" style="color: #dc3545;"></i>
                ${title}
              </h4>
            </div>
            <div class="modal-body">
              <p style="margin: 0; color: #666;">${message}</p>
            </div>
            <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 20px;">
              <button type="button" class="btn btn-secondary" onclick="closeConfirmationModal()">Cancel</button>
              <button type="button" class="btn ${buttonClass}" onclick="confirmAction()">${buttonText}</button>
            </div>
          </div>
        </div>
      `;

      document.body.insertAdjacentHTML('beforeend', modalHtml);

      window.confirmAction = function() {
        onConfirm();
        closeConfirmationModal();
      };
    }

    function closeConfirmationModal() {
      const modal = document.getElementById('confirmationModal');
      if (modal) {
        modal.remove();
      }
      delete window.confirmAction;
    }

    async function setupEnhancedStudentAutocomplete(mode) {
      const input = document.getElementById('studentNameInput');
      const suggestionsDiv = document.getElementById('studentSuggestions');
      const validationMessage = document.getElementById('validationMessage');
      const loadingSpinner = document.getElementById('inputLoadingSpinner');
      const submitBtn = document.getElementById('submitStudentBtn');
      const roomNumber = document.getElementById('targetRoomNumber').value;

      let debounceTimer;
      let currentQuery = '';
      let selectedStudentValid = false;

      // Enable submit button if we have a valid initial value
      if (mode === 'edit' && input.value.trim()) {
        validateStudentName(input.value.trim());
      }

      input.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        currentQuery = query;
        selectedStudentValid = false;
        submitBtn.disabled = true;

        // Clear previous validation
        hideValidationMessage();

        // Update input styling
        input.style.borderColor = '#ddd';

        if (query.length === 0) {
          suggestionsDiv.style.display = 'none';
          loadingSpinner.style.display = 'none';
          return;
        }

        if (query.length < 2) {
          suggestionsDiv.style.display = 'none';
          loadingSpinner.style.display = 'none';
          showValidationMessage('Please enter at least 2 characters', 'info');
          return;
        }

        // Show loading spinner
        loadingSpinner.style.display = 'block';

        debounceTimer = setTimeout(async () => {
          await searchAndDisplayStudents(query);
        }, 300);
      });

      // Handle keyboard navigation
      input.addEventListener('keydown', function(e) {
        const suggestions = suggestionsDiv.querySelectorAll('.suggestion-item');
        const activeSuggestion = suggestionsDiv.querySelector('.suggestion-item.active');

        if (e.key === 'ArrowDown') {
          e.preventDefault();
          if (activeSuggestion) {
            activeSuggestion.classList.remove('active');
            const next = activeSuggestion.nextElementSibling;
            if (next) {
              next.classList.add('active');
            } else {
              suggestions[0]?.classList.add('active');
            }
          } else {
            suggestions[0]?.classList.add('active');
          }
        } else if (e.key === 'ArrowUp') {
          e.preventDefault();
          if (activeSuggestion) {
            activeSuggestion.classList.remove('active');
            const prev = activeSuggestion.previousElementSibling;
            if (prev) {
              prev.classList.add('active');
            } else {
              suggestions[suggestions.length - 1]?.classList.add('active');
            }
          } else {
            suggestions[suggestions.length - 1]?.classList.add('active');
          }
        } else if (e.key === 'Enter') {
          e.preventDefault();
          if (activeSuggestion) {
            const studentName = activeSuggestion.dataset.studentName;
            selectStudent(studentName);
          }
        } else if (e.key === 'Escape') {
          suggestionsDiv.style.display = 'none';
        }
      });

      // Hide suggestions when clicking outside
      document.addEventListener('click', function(e) {
        if (!input.contains(e.target) && !suggestionsDiv.contains(e.target)) {
          suggestionsDiv.style.display = 'none';
        }
      });

      async function searchAndDisplayStudents(query) {
        try {
          const response = await fetch(`/api/students/valid-list?search=${encodeURIComponent(query)}&room_number=${roomNumber}&limit=8`);
          const data = await response.json();

          loadingSpinner.style.display = 'none';

          if (data.success) {
            if (data.students.length > 0) {
              displayEnhancedStudentSuggestions(data.students);
              hideValidationMessage();
            } else {
              suggestionsDiv.style.display = 'none';
              showValidationMessage(`No students found matching "${query}". Please check the spelling or contact an administrator.`, 'warning');
            }
          } else {
            suggestionsDiv.style.display = 'none';
            showValidationMessage(data.message || 'Error searching for students', 'error');
          }
        } catch (error) {
          console.error('Error fetching student suggestions:', error);
          loadingSpinner.style.display = 'none';
          suggestionsDiv.style.display = 'none';
          showValidationMessage('Network error. Please check your connection and try again.', 'error');
        }
      }

      async function validateStudentName(studentName) {
        if (!studentName) return;

        try {
          const response = await fetch(`/api/students/valid-list?search=${encodeURIComponent(studentName)}&limit=1`);
          const data = await response.json();

          if (data.success && data.students.length > 0) {
            const exactMatch = data.students.find(s => s.name.toLowerCase() === studentName.toLowerCase());
            if (exactMatch) {
              selectedStudentValid = true;
              submitBtn.disabled = false;
              input.style.borderColor = '#28a745';
              showValidationMessage(`Valid student: ${exactMatch.display_name}`, 'success');
            } else {
              showValidationMessage('Student name does not exactly match any registered student', 'warning');
            }
          } else {
            showValidationMessage('Student not found in the  PNPh Management System', 'error');
          }
        } catch (error) {
          console.error('Error validating student:', error);
        }
      }

      function showValidationMessage(message, type) {
        const colors = {
          success: '#28a745',
          error: '#dc3545',
          warning: '#ffc107',
          info: '#17a2b8'
        };

        validationMessage.textContent = message;
        validationMessage.style.color = colors[type] || '#666';
        validationMessage.style.display = 'block';
      }

      function hideValidationMessage() {
        validationMessage.style.display = 'none';
      }

      // Expose functions for use by other parts of the modal
      window.selectStudent = function(studentName) {
        input.value = studentName;
        suggestionsDiv.style.display = 'none';
        selectedStudentValid = true;
        submitBtn.disabled = false;
        input.style.borderColor = '#28a745';
        validateStudentName(studentName);
      };
    }

    function displayEnhancedStudentSuggestions(students) {
      const suggestionsDiv = document.getElementById('studentSuggestions');
      let suggestionsHtml = '';

      if (students.length === 0) {
        suggestionsHtml = `
          <div style="padding: 15px; text-align: center; color: #666;">
            <i class="fi fi-rr-search" style="font-size: 1.2rem; margin-bottom: 8px; display: block;"></i>
            No students found matching your search
          </div>
        `;
      } else {
        students.forEach((student, index) => {
          const genderIcon = student.gender === 'M' ? 'fi-rr-mars' : 'fi-rr-venus';
          const genderColor = student.gender === 'M' ? '#007bff' : '#e91e63';
          const genderBadge = `<span style="background: ${genderColor}; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.75rem;">${student.gender}</span>`;
          const batchBadge = student.batch ? `<span style="background: #6c757d; color: white; padding: 2px 6px; border-radius: 10px; font-size: 0.75rem;">Batch ${student.batch}</span>` : '';

          suggestionsHtml += `
            <div class="suggestion-item" data-student-name="${student.name}" onclick="selectStudent('${student.name}')"
                 style="padding: 12px; border-bottom: 1px solid #eee; cursor: pointer; display: flex; align-items: center; gap: 10px; transition: all 0.2s; position: relative;">
              <div style="width: 36px; height: 36px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.9rem;">
                ${student.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase()}
              </div>
              <div style="flex: 1; min-width: 0;">
                <div style="font-weight: 500; color: #333; margin-bottom: 2px;">${student.name}</div>
                <div style="display: flex; gap: 6px; align-items: center;">
                  ${genderBadge}
                  ${batchBadge}
                </div>
              </div>
              <i class="fi fi-rr-angle-right" style="color: #ccc; font-size: 0.9rem;"></i>
            </div>
          `;
        });
      }

      suggestionsDiv.innerHTML = suggestionsHtml;
      suggestionsDiv.style.display = 'block';

      // Add enhanced hover and keyboard navigation effects
      suggestionsDiv.querySelectorAll('.suggestion-item').forEach((item, index) => {
        item.addEventListener('mouseenter', function() {
          // Remove active class from all items
          suggestionsDiv.querySelectorAll('.suggestion-item').forEach(i => i.classList.remove('active'));
          // Add active class to current item
          this.classList.add('active');
          this.style.backgroundColor = '#f8f9fa';
          this.style.transform = 'translateX(4px)';
        });

        item.addEventListener('mouseleave', function() {
          this.classList.remove('active');
          this.style.backgroundColor = 'white';
          this.style.transform = 'translateX(0)';
        });
      });
    }

    /* ------------------------------
     * Bulk add helpers
     * ------------------------------ */
    function getBulkOccupantCopy(type) {
      const normalized = (type || 'both').toLowerCase();
      if (normalized === 'male') return { text: 'Male occupants only', bg: '#dbeafe', color: '#1d4ed8' };
      if (normalized === 'female') return { text: 'Female occupants only', bg: '#fde5f3', color: '#9d174d' };
      return { text: 'Room accepts all students', bg: '#e0f2ff', color: '#0f172a' };
    }

    function renderBulkStudentsList(students) {
      const listDiv = document.getElementById('bulkStudentsList');
      if (!listDiv) return;
      if (!students || students.length === 0) {
        listDiv.innerHTML = '<div style="padding:14px; color:#6c757d;">No students available.</div>';
        return;
      }

      const cards = students.map(student => {
        const initials = student.name.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
        const genderBadge = student.gender === 'M'
          ? '<span style="background:#bfdbfe; color:#1d4ed8; padding:2px 8px; border-radius:999px; font-size:0.75rem;">Male</span>'
          : '<span style="background:#fbcfe8; color:#9d174d; padding:2px 8px; border-radius:999px; font-size:0.75rem;">Female</span>';
        const batchBadge = student.batch
          ? `<span style="background:#e2e8f0; color:#0f172a; padding:2px 8px; border-radius:999px; font-size:0.75rem;">Batch ${student.batch}</span>`
          : '';

        return `
          <label style="display:flex; gap:12px; align-items:center; padding:12px; border:1px solid #e2e8f0; border-radius:10px; background:#fff; cursor:pointer; transition:all .2s;">
            <input type="checkbox" class="bulk-student-checkbox" value="${student.name}" style="margin:0;">
            <div style="width:40px; height:40px; border-radius:12px; background:linear-gradient(135deg,#22bbea,#1e90ff); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:600;">
              ${initials}
            </div>
            <div style="flex:1;">
              <div style="font-weight:600; color:#0f172a; margin-bottom:4px;">${student.name}</div>
              <div style="display:flex; gap:6px; flex-wrap:wrap;">
                ${genderBadge}
                ${batchBadge}
              </div>
            </div>
          </label>
        `;
      }).join('');

      listDiv.innerHTML = `
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(260px,1fr)); gap:12px;">
          ${cards}
        </div>
      `;
    }

    async function openBulkAddModal() {
      const modal = document.getElementById('bulkAddModal');
      if (!modal) return;

      const roomNumberField = document.getElementById('targetRoomNumber');
      const roomNumber = roomNumberField ? roomNumberField.value : '';
      if (!roomNumber) {
        showEnhancedAlert('error', 'Open a room first before bulk adding students.');
        return;
      }

      const occupantTypeInput = document.getElementById('individualRoomOccupantType');
      const occupantType = (occupantTypeInput ? occupantTypeInput.value : 'both') || 'both';
      const badge = document.getElementById('bulkOccupantBadge');
      const badgeCopy = getBulkOccupantCopy(occupantType);
      if (badge) {
        badge.textContent = badgeCopy.text;
        badge.style.background = badgeCopy.bg;
        badge.style.color = badgeCopy.color;
      }

      const listDiv = document.getElementById('bulkStudentsList');
      const progressDiv = document.getElementById('bulkAddProgress');
      if (progressDiv) progressDiv.style.display = 'none';
      if (listDiv) {
        listDiv.innerHTML = '<div style="padding:20px; text-align:center; color:#6c757d;"><i class="fi fi-rr-spinner" style="animation:spin 1s linear infinite;"></i> Loading unassigned students...</div>';
      }
      modal.style.display = 'block';

      try {
        const query = new URLSearchParams({
          unassigned_only: '1',
          occupant_type: occupantType,
          limit: '200'
        });
        const response = await fetch(`/api/students/valid-list?${query.toString()}`);
        const data = await response.json();
        if (data.success && data.students && data.students.length > 0) {
          renderBulkStudentsList(data.students);
        } else if (listDiv) {
          listDiv.innerHTML = '<div style="padding:14px; color:#6c757d; background:#f8fafc; border:1px dashed #cbd5f5; border-radius:8px; text-align:center;">No unassigned students found for this occupant type.</div>';
        }
      } catch (error) {
        console.error('Error loading unassigned students', error);
        if (listDiv) {
          listDiv.innerHTML = '<div style="padding:12px; color:#c92a2a; background:#ffe5e5; border-radius:8px;">Failed to load students. Please try again.</div>';
        }
      }
    }

    function closeBulkAddModal() {
      const modal = document.getElementById('bulkAddModal');
      if (!modal) return;
      modal.style.display = 'none';
      const listDiv = document.getElementById('bulkStudentsList');
      if (listDiv) listDiv.innerHTML = '';
      const progressDiv = document.getElementById('bulkAddProgress');
      if (progressDiv) progressDiv.style.display = 'none';
    }

    async function processBulkAddSelections() {
      const checkboxes = Array.from(document.querySelectorAll('.bulk-student-checkbox')).filter(function(cb) { return cb.checked; });
      if (checkboxes.length === 0) {
        showEnhancedAlert('info', 'No students selected.');
        return;
      }

      const roomNumberField = document.getElementById('targetRoomNumber');
      const roomNumber = roomNumberField ? roomNumberField.value : '';
      if (!roomNumber) {
        showEnhancedAlert('error', 'Unable to detect the current room.');
        return;
      }

      const progressDiv = document.getElementById('bulkAddProgress');
      const submitBtn = document.getElementById('bulkAddSubmitBtn');
      if (submitBtn) submitBtn.disabled = true;
      if (progressDiv) {
        progressDiv.style.display = 'block';
        progressDiv.textContent = `Adding ${checkboxes.length} student(s)...`;
      }

      const added = [];
      const failed = [];

      for (let i = 0; i < checkboxes.length; i++) {
        const name = checkboxes[i].value;
        try {
          const res = await addStudentToRoom(name);
          if (res && res.success) {
            added.push(name);
          } else {
            failed.push({ name, message: res && res.message ? res.message : 'Failed' });
          }
        } catch (error) {
          failed.push({ name, message: error.enhancedMessage || error.message || 'Error' });
        }
        if (progressDiv) {
          progressDiv.textContent = `Processed ${i + 1}/${checkboxes.length} — Added: ${added.length}, Failed: ${failed.length}`;
        }
      }

      if (submitBtn) submitBtn.disabled = false;

      if (added.length > 0) {
        showEnhancedAlert('success', `${added.length} student(s) added successfully to Room ${roomNumber}.`);
      }
      if (failed.length > 0) {
        const failureDetails = failed.slice(0, 3).map(item => `• ${item.name}: ${item.message}`).join('<br>');
        showEnhancedAlert('warning', `${failed.length} student(s) failed to add.<br>${failureDetails}`);
      }

      setTimeout(() => {
        openIndividualCapacityModal(roomNumber);
      }, 600);

      closeBulkAddModal();
    }

    window.openBulkAddModal = openBulkAddModal;
    window.closeBulkAddModal = closeBulkAddModal;
    window.processBulkAddSelections = processBulkAddSelections;

    async function processStudentEdit(originalName, mode) {
      const studentName = document.getElementById('studentNameInput').value.trim();
      const roomNumber = document.getElementById('targetRoomNumber').value;
      const submitButton = document.getElementById('submitStudentBtn');
      const validationMessage = document.getElementById('validationMessage');

      // Enhanced client-side validation
      const validationResult = validateStudentInput(studentName, originalName, mode);
      if (!validationResult.valid) {
        showEnhancedAlert('error', validationResult.message);
        return;
      }

      // Show enhanced loading state
      const originalButtonText = submitButton.textContent;
      submitButton.disabled = true;
      submitButton.innerHTML = '<i class="fi fi-rr-spinner" style="animation: spin 1s linear infinite; margin-right: 6px;"></i>Processing...';

      // Show processing message
      validationMessage.textContent = `${mode === 'add' ? 'Adding' : 'Updating'} student assignment...`;
      validationMessage.style.color = '#17a2b8';
      validationMessage.style.display = 'block';

      try {
        let response;
        if (mode === 'add') {
          response = await addStudentToRoom(studentName);
        } else {
          response = await editStudentInRoom(originalName, studentName);
        }

        closeStudentEditModal();

        if (response && response.success) {
          showEnhancedAlert('success', response.message || `Student ${mode === 'add' ? 'added' : 'updated'} successfully`);

          // Update dashboard immediately with returned data
          if (response.updated_room_data) {
            updateDashboardWithRoomData(response.updated_room_data);
          }

          // Show additional info if student was moved from other rooms
          if (response.previous_rooms && response.previous_rooms.length > 0) {
            setTimeout(() => {
              showEnhancedAlert('info', `Student was also removed from room(s): ${response.previous_rooms.join(', ')}`);
            }, 2000);
          }

          // Add a delay to ensure  PNPh Management System is updated before refreshing modal
          setTimeout(() => {
            openIndividualCapacityModal(roomNumber); // Refresh the modal
          }, 1000);
        }
      } catch (error) {
        console.error(`Error ${mode === 'add' ? 'adding' : 'editing'} student:`, error);

        // Parse error response for better user feedback
        let errorMessage = `Failed to ${mode === 'add' ? 'add' : 'update'} student. Please try again.`;
        if (error.response) {
          try {
            const errorData = await error.response.json();
            errorMessage = errorData.message || errorMessage;

            // Show suggestions if available
            if (errorData.suggestions && errorData.suggestions.length > 0) {
              errorMessage += ` Did you mean: ${errorData.suggestions.slice(0, 3).join(', ')}?`;
            }
          } catch (parseError) {
            console.error('Error parsing error response:', parseError);
          }
        }

        showEnhancedAlert('error', errorMessage);

        // Restore button state
        submitButton.disabled = false;
        submitButton.innerHTML = originalButtonText;
        validationMessage.style.display = 'none';
      }
    }

    // Client-side validation for student input
    function validateStudentInput(studentName, originalName, mode) {
      // Check if name is empty
      if (!studentName) {
        return {
          valid: false,
          message: 'Please enter a student name'
        };
      }

      // Check minimum length
      if (studentName.length < 2) {
        return {
          valid: false,
          message: 'Student name must be at least 2 characters long'
        };
      }

      // Check maximum length
      if (studentName.length > 100) {
        return {
          valid: false,
          message: 'Student name is too long (maximum 100 characters)'
        };
      }

      // Check for valid characters (letters, spaces, hyphens, apostrophes)
      const namePattern = /^[a-zA-Z\s\-'\.]+$/;
      if (!namePattern.test(studentName)) {
        return {
          valid: false,
          message: 'Student name can only contain letters, spaces, hyphens, apostrophes, and periods'
        };
      }

      // Check if name is the same as original (for edit mode)
      if (mode === 'edit' && studentName === originalName) {
        return {
          valid: false,
          message: 'Please enter a different name or cancel the edit'
        };
      }

      // Check for excessive spaces
      if (studentName.includes('  ')) {
        return {
          valid: false,
          message: 'Student name cannot contain multiple consecutive spaces'
        };
      }

      return {
        valid: true,
        message: 'Valid input'
      };
    }

    async function addStudentToRoom(studentName, retryCount = 0) {
      const roomNumber = document.getElementById('targetRoomNumber').value;
      console.log('addStudentToRoom called:', { studentName, roomNumber, retryCount });

      try {
        const response = await fetch('/api/room/add-student', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            room: roomNumber,
            name: studentName
          })
        });

        // Handle different HTTP status codes with enhanced error information
        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));

          // If server rejected due to full capacity, attempt to increase capacity dynamically then retry once
          if (response.status === 422 && errorData.message && /full capacity|at full capacity/i.test(errorData.message)) {
            console.warn('Server reports room full. Attempting to expand capacity dynamically before retrying add-student.');

            // Only attempt capacity bump once per add call to avoid loops
            if (retryCount >= 1) {
              const error = new Error(errorData.message || 'Validation failed');
              error.suggestions = errorData.suggestions;
              error.errorType = errorData.error_type;
              throw error;
            }

            try {
              // Determine current students count from DOM or floorData as fallback
              let currentCount = 0;
              try {
                const roomCard = document.querySelector(`.room-box[data-room-number="${roomNumber}"]`);
                if (roomCard) {
                  const list = roomCard.querySelectorAll('.occupants-list li');
                  // filter out placeholder 'No students assigned'
                  currentCount = Array.from(list).map(li => li.textContent.trim()).filter(t => t && !/no students assigned/i.test(t) && !/loading/i.test(t)).length;
                }
              } catch (e) { console.warn('Unable to read currentCount from DOM', e); }

              // If we couldn't read DOM count, try floorData
              if (!currentCount && typeof floorData !== 'undefined' && floorData) {
                for (const f in floorData) {
                  if (floorData[f].students && floorData[f].students[roomNumber]) {
                    currentCount = floorData[f].students[roomNumber].length;
                    break;
                  }
                }
              }

              // Compute desired capacity: ensure at least one slot for new student
              const desiredCapacity = Math.max((currentCount || 0) + 1, 6);

              // Call API to set individual room capacity
              const setResp = await fetch('/api/room/set-individual-capacity', {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                  'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                  'Accept': 'application/json'
                },
                body: JSON.stringify({ room_number: roomNumber, capacity: desiredCapacity })
              });

              if (!setResp.ok) {
                console.error('Failed to update room capacity before retrying add-student', setResp.status);
                const errData = await setResp.json().catch(() => ({}));
                const error = new Error(errData.message || `Failed to set capacity (status ${setResp.status})`);
                throw error;
              }

              const setData = await setResp.json().catch(() => ({}));
              if (!setData.success) {
                const error = new Error(setData.message || 'Failed to update room capacity');
                throw error;
              }

              // Update UI: set data-capacity and occupancy display for room card(s)
              try {
                document.querySelectorAll(`.room-box[data-room-number="${roomNumber}"]`).forEach(card => {
                  card.setAttribute('data-capacity', String(desiredCapacity));
                  const occ = card.querySelector('.occupancy');
                  if (occ) {
                    const currentCount = card.querySelectorAll('.occupants-list li').length;
                    occ.textContent = `${currentCount}/${desiredCapacity}`;
                  }
                });
              } catch (e) { console.warn('UI update after capacity set failed', e); }

              // Retry adding the student once
              return addStudentToRoom(studentName, retryCount + 1);
            } catch (bumpError) {
              console.error('Capacity bump failed', bumpError);
              const error = new Error(errorData.message || 'Validation failed');
              error.suggestions = errorData.suggestions;
              error.errorType = errorData.error_type;
              throw error;
            }
          } else if (response.status === 404) {
            const error = new Error(errorData.message || 'Student not found');
            error.suggestions = errorData.suggestions;
            error.errorType = errorData.error_type;
            throw error;
          } else if (response.status === 500) {
            throw new Error('Server error. Please try again later.');
          } else {
            throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
          }
        }

        const data = await response.json();
        if (!data.success) {
          const error = new Error(data.message || 'Failed to add student');
          error.suggestions = data.suggestions;
          error.errorType = data.error_type;
          throw error;
        }

        // Update dashboard immediately with returned data
        if (data.updated_room_data) {
          updateDashboardWithRoomData(data.updated_room_data);
        } else {
          // Fallback to API call if no data returned
          await syncDashboardAfterStudentChange(roomNumber);
        }
        return data;

      } catch (error) {
        console.error('Error adding student:', error);

        // Retry logic for network errors
        if (retryCount < 2 && (error.name === 'TypeError' || error.message.includes('fetch'))) {
          console.log(`Retrying add student operation (attempt ${retryCount + 1})`);
          await new Promise(resolve => setTimeout(resolve, 1000)); // Wait 1 second
          return addStudentToRoom(studentName, retryCount + 1);
        }

        // Enhanced error handling with suggestions
        let errorMessage = getUserFriendlyErrorMessage(error.message);

        // Add suggestions if available
        if (error.suggestions && error.suggestions.length > 0) {
          errorMessage += ` Did you mean: ${error.suggestions.slice(0, 3).join(', ')}?`;
        }

        // Use appropriate alert type based on error type
        const alertType = error.errorType === 'not_found' ? 'warning' : 'error';
        showEnhancedAlert(alertType, errorMessage);

        // Store error details for potential retry
        error.enhancedMessage = errorMessage;
        throw error;
      }
    }

    async function editStudentInRoom(oldName, newName, retryCount = 0) {
      const roomNumber = document.getElementById('targetRoomNumber').value;

      try {
        const response = await fetch('/api/room/edit-student', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            room: roomNumber,
            old_name: oldName,
            new_name: newName
          })
        });

        // Handle different HTTP status codes with enhanced error information
        if (!response.ok) {
          const errorData = await response.json().catch(() => ({}));

          if (response.status === 422) {
            const error = new Error(errorData.message || 'Validation failed');
            error.suggestions = errorData.suggestions;
            error.errorType = errorData.error_type;
            throw error;
          } else if (response.status === 404) {
            const error = new Error(errorData.message || 'Student not found');
            error.suggestions = errorData.suggestions;
            error.errorType = errorData.error_type;
            throw error;
          } else if (response.status === 500) {
            throw new Error('Server error. Please try again later.');
          } else {
            throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
          }
        }

        const data = await response.json();
        if (!data.success) {
          const error = new Error(data.message || 'Failed to update student');
          error.suggestions = data.suggestions;
          error.errorType = data.error_type;
          throw error;
        }

        // Update dashboard immediately with returned data
        if (data.updated_room_data) {
          updateDashboardWithRoomData(data.updated_room_data);
        } else {
          // Fallback to API call if no data returned
          await syncDashboardAfterStudentChange(roomNumber);
        }
        return data;

      } catch (error) {
        console.error('Error editing student:', error);

        // Retry logic for network errors
        if (retryCount < 2 && (error.name === 'TypeError' || error.message.includes('fetch'))) {
          console.log(`Retrying edit student operation (attempt ${retryCount + 1})`);
          await new Promise(resolve => setTimeout(resolve, 1000));
          return editStudentInRoom(oldName, newName, retryCount + 1);
        }

        // Enhanced error handling with suggestions
        let errorMessage = getUserFriendlyErrorMessage(error.message);

        // Add suggestions if available
        if (error.suggestions && error.suggestions.length > 0) {
          errorMessage += ` Did you mean: ${error.suggestions.slice(0, 3).join(', ')}?`;
        }

        // Use appropriate alert type based on error type
        const alertType = error.errorType === 'not_found' ? 'warning' : 'error';
        showEnhancedAlert(alertType, errorMessage);

        // Store error details for potential retry
        error.enhancedMessage = errorMessage;
        throw error;
      }
    }

    // Convert technical error messages to user-friendly ones
    function getUserFriendlyErrorMessage(errorMessage) {
      const errorMap = {
        'Student not found in the system': 'This student is not registered in our  PNPh Management System. Please check the name spelling or contact an administrator.',
        'Student not found in the PNPh Management System': 'This student is not registered in the  PNPh Management System. Please verify the name or contact an administrator to add the student.',
        'Room is at full capacity': 'This room is already full. Please try a different room or increase the room capacity first.',
        'Gender mismatch': 'This room is assigned to students of a different gender. Please choose a room that matches the student\'s gender.',
        'Student is already assigned to this room': 'This student is already assigned to this room.',
        'Room not found': 'The specified room could not be found. Please refresh the page and try again.',
        'Student assignment not found': 'The student assignment could not be found. The student may have already been removed.',
        'Validation failed': 'The information provided is not valid. Please check your input and try again.',
        'Server error': 'A server error occurred. Please try again in a few moments.',
        'Failed to add student': 'Unable to add the student. Please check your internet connection and try again.',
        'Failed to update student': 'Unable to update the student assignment. Please try again.',
        'Failed to remove student': 'Unable to remove the student. Please try again.',
        'Student name cannot be empty': 'Please enter a student name.',
        'Student name must be at least 2 characters': 'Student name must be at least 2 characters long.',
        'Student name cannot exceed 100 characters': 'Student name is too long. Please use a shorter name.',
        'Student name can only contain letters': 'Student name can only contain letters, spaces, hyphens, apostrophes, and periods.',
        'Network error': 'Unable to connect to the server. Please check your internet connection and try again.',
        'Timeout error': 'The request took too long to complete. Please try again.',
        'Permission denied': 'You do not have permission to perform this action.',
        'Session expired': 'Your session has expired. Please refresh the page and log in again.',
        'CSRF token mismatch': 'Security token expired. Please refresh the page and try again.',
        'Too many requests': 'Too many requests. Please wait a moment and try again.',
        'Access denied': 'Access denied. You may not have permission to perform this action.',
        'Resource not found': 'The requested resource was not found. Please refresh the page and try again.',
        'Internal server error': 'A server error occurred. Please try again in a few moments or contact support if the problem persists.'
      };

      // Check for exact matches first
      if (errorMap[errorMessage]) {
        return errorMap[errorMessage];
      }

      // Check for partial matches
      for (const [key, value] of Object.entries(errorMap)) {
        if (errorMessage.toLowerCase().includes(key.toLowerCase())) {
          return value;
        }
      }

      // Handle specific error patterns
      if (errorMessage.includes('CSRF') || errorMessage.includes('csrf')) {
        return 'Security token expired. Please refresh the page and try again.';
      }

      if (errorMessage.includes('429')) {
        return 'Too many requests. Please wait a moment and try again.';
      }

      if (errorMessage.includes('403')) {
        return 'Access denied. You may not have permission to perform this action.';
      }

      if (errorMessage.includes('404')) {
        return 'The requested resource was not found. Please refresh the page and try again.';
      }

      if (errorMessage.includes('500') || errorMessage.includes('Internal Server Error')) {
        return 'A server error occurred. Please try again in a few moments or contact support if the problem persists.';
      }

      if (errorMessage.includes('timeout') || errorMessage.includes('Timeout')) {
        return 'The request took too long to complete. Please check your connection and try again.';
      }

      if (errorMessage.includes('network') || errorMessage.includes('Network') || errorMessage.includes('fetch')) {
        return 'Network error. Please check your internet connection and try again.';
      }

      // Default fallback message with helpful guidance
      return errorMessage || 'An unexpected error occurred. Please try refreshing the page or contact support if the problem continues.';
    }

    async function removeStudentFromRoom(studentName, retryCount = 0) {
      try {
        console.log('removeStudentFromRoom called:', { studentName, retryCount });
        const roomNumber = document.getElementById('targetRoomNumber').value;
        const response = await fetch('/api/room/delete-student', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            room: roomNumber,
            name: studentName
          })
        });

        if (!response.ok) {
          if (response.status === 422) {
            const errorData = await response.json();
            throw new Error(errorData.message || 'Validation failed');
          } else if (response.status === 500) {
            throw new Error('Server error. Please try again later.');
          } else {
            throw new Error(`HTTP error ${response.status}: ${response.statusText}`);
          }
        }

        const data = await response.json();
        if (!data.success) {
          throw new Error(data.message || 'Failed to remove student');
        }

        showAlert('success', data.message || 'Student removed successfully');

        // Update dashboard immediately with returned data
        if (data.updated_room_data) {
          updateDashboardWithRoomData(data.updated_room_data);
        } else {
          // Fallback to API call if no data returned
          await syncDashboardAfterStudentChange(roomNumber);
        }

        // Add a delay to ensure  PNPh Management System is updated before refreshing modal
        setTimeout(() => {
          openIndividualCapacityModal(roomNumber); // Refresh the modal
        }, 1000);

      } catch (error) {
        console.error('Error removing student:', error);

        // Retry logic for network errors
        if (retryCount < 2 && (error.name === 'TypeError' || error.message.includes('fetch'))) {
          console.log(`Retrying remove student operation (attempt ${retryCount + 1})`);
          await new Promise(resolve => setTimeout(resolve, 1000));
          return removeStudentFromRoom(studentName, retryCount + 1);
        }

        const userMessage = getUserFriendlyErrorMessage(error.message);
        showAlert('error', userMessage);
      }
    }

    // Alert system for better user feedback
    function showAlert(type, message) {
      const alertHtml = `
        <div id="alertNotification" class="alert alert-${type}" style="
          position: fixed;
          top: 20px;
          right: 20px;
          z-index: 10002;
          min-width: 300px;
          padding: 15px;
          border-radius: 8px;
          box-shadow: 0 4px 12px rgba(0,0,0,0.15);
          background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
          border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
          color: ${type === 'success' ? '#155724' : '#721c24'};
          display: flex;
          align-items: center;
          gap: 10px;
        ">
          <i class="fi fi-rr-${type === 'success' ? 'check-circle' : 'exclamation-triangle'}" style="font-size: 1.2rem;"></i>
          <span style="flex: 1;">${message}</span>
          <button onclick="closeAlert()" style="background: none; border: none; font-size: 1.2rem; cursor: pointer; color: inherit;">&times;</button>
        </div>
      `;

      // Remove existing alert
      const existingAlert = document.getElementById('alertNotification');
      if (existingAlert) {
        existingAlert.remove();
      }

      document.body.insertAdjacentHTML('beforeend', alertHtml);

      // Auto-remove after 5 seconds
      setTimeout(() => {
        closeAlert();
      }, 5000);
    }

    function closeAlert() {
      const alert = document.getElementById('alertNotification');
      if (alert) {
        alert.remove();
      }
    }

    // Enhanced alert system with better styling and animations
    function showEnhancedAlert(type, message, duration = 5000) {
      const alertColors = {
        success: { bg: '#d4edda', border: '#c3e6cb', text: '#155724', icon: 'check-circle' },
        error: { bg: '#f8d7da', border: '#f5c6cb', text: '#721c24', icon: 'exclamation-triangle' },
        warning: { bg: '#fff3cd', border: '#ffeaa7', text: '#856404', icon: 'exclamation' },
        info: { bg: '#d1ecf1', border: '#bee5eb', text: '#0c5460', icon: 'info-circle' }
      };

      const colors = alertColors[type] || alertColors.info;

      const alertHtml = `
        <div id="enhancedAlertNotification" class="enhanced-alert" style="
          position: fixed;
          top: 20px;
          right: 20px;
          z-index: 10002;
          min-width: 350px;
          max-width: 500px;
          padding: 16px 20px;
          border-radius: 10px;
          box-shadow: 0 6px 20px rgba(0,0,0,0.15);
          background: ${colors.bg};
          border: 1px solid ${colors.border};
          color: ${colors.text};
          display: flex;
          align-items: flex-start;
          gap: 12px;
          transform: translateX(100%);
          transition: transform 0.3s ease-out;
          font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        ">
          <i class="fi fi-rr-${colors.icon}" style="font-size: 1.3rem; margin-top: 2px; flex-shrink: 0;"></i>
          <div style="flex: 1; line-height: 1.4;">
            <div style="font-weight: 500; margin-bottom: 2px;">${type.charAt(0).toUpperCase() + type.slice(1)}</div>
            <div style="font-size: 0.95rem; opacity: 0.9;">${message}</div>
          </div>
          <button onclick="closeEnhancedAlert()" style="
            background: none;
            border: none;
            font-size: 1.4rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            padding: 0;
            margin-top: 2px;
            flex-shrink: 0;
            transition: opacity 0.2s;
          " onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">&times;</button>
        </div>
      `;

      // Remove existing enhanced alert
      const existingAlert = document.getElementById('enhancedAlertNotification');
      if (existingAlert) {
        existingAlert.remove();
      }

      document.body.insertAdjacentHTML('beforeend', alertHtml);

      // Animate in
      const alertElement = document.getElementById('enhancedAlertNotification');
      setTimeout(() => {
        alertElement.style.transform = 'translateX(0)';
      }, 10);

      // Auto-remove after specified duration
      setTimeout(() => {
        closeEnhancedAlert();
      }, duration);
    }

    function closeEnhancedAlert() {
      const alert = document.getElementById('enhancedAlertNotification');
      if (alert) {
        alert.style.transform = 'translateX(100%)';
        setTimeout(() => {
          alert.remove();
        }, 300);
      }
    }

    // Enhanced dashboard update with real-time synchronization
      function updateDashboardWithRoomData(roomData) {
        try {
          // Snapshot previous global state BEFORE mutating
          const previousGlobal = window.roomStudents ? JSON.parse(JSON.stringify(window.roomStudents)) : {};

        // Track which rooms were updated for visual feedback
        const updatedRooms = [];

        // Merge incoming roomData into local floorData so per-room edits persist
        try {
          updateFloorDataWithNewAssignments(roomData);
        } catch (e) { console.warn('updateFloorDataWithNewAssignments failed', e); }

        // Update all room cards with new data and enhanced visual feedback
          Object.keys(roomData).forEach(roomNumber => {
            const previousStudents = previousGlobal?.[roomNumber] || [];
          const newStudents = roomData[roomNumber] || [];

          // Check if there were actual changes
          const hasChanges = JSON.stringify(previousStudents) !== JSON.stringify(newStudents);

          if (hasChanges) {
            updatedRooms.push(roomNumber);
            updateSpecificRoomCardWithAnimation(roomNumber, newStudents, previousStudents);
          }
        });

          // Now mutate the global cache after UI updates
          window.roomStudents = {
            ...(previousGlobal || {}),
            ...roomData
          };

          // Recompute and render the total assigned students snapshot
          try { computeAndRenderTotalAssignedStudents(); } catch (e) { console.warn('computeAndRenderTotalAssignedStudents failed', e); }

          // Also ensure floorData reflects these changes for UI rebuilds
          try {
            updateFloorDataWithNewAssignments(roomData);
          } catch (e) { /* already attempted above */ }

          // Trigger enhanced cross-tab communication
        const updateEvent = {
          type: 'room_assignments_updated',
          timestamp: Date.now(),
          updatedRooms: updatedRooms,
          roomData: roomData
        };

        localStorage.setItem('roomAssignmentsUpdate', JSON.stringify(updateEvent));

        // Broadcast to other tabs using BroadcastChannel if available
        if (window.BroadcastChannel) {
          const channel = new BroadcastChannel('room_updates');
          channel.postMessage(updateEvent);
        }

        // Show subtle notification for multiple room updates
        if (updatedRooms.length > 1) {
          showEnhancedAlert('info', `Updated ${updatedRooms.length} rooms: ${updatedRooms.join(', ')}`, 3000);
        }

        console.log('Dashboard updated with real-time data:', {
          updatedRooms,
          totalRooms: Object.keys(roomData).length
        });
      } catch (error) {
        console.error('Error updating dashboard with room data:', error);
        showEnhancedAlert('error', 'Failed to update dashboard. Please refresh the page.', 5000);
      }
    }

    // Real-time dashboard synchronization (fallback method)
    async function syncDashboardAfterStudentChange(roomNumber) {
      try {
        // Get updated room assignments from the server
        const response = await fetch('/api/dashboard/room-data', {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
          }
        });

        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        if (data.success && data.roomStudents) {
          // Update the specific room card on the dashboard
          updateSpecificRoomCard(roomNumber, data.roomStudents[roomNumber] || []);

          // Update global room data for consistency
          if (window.roomStudents) {
            window.roomStudents[roomNumber] = data.roomStudents[roomNumber] || [];
          }

          // Trigger localStorage update for cross-tab communication
          localStorage.setItem('roomAssignmentsUpdated', Date.now().toString());

          console.log(`Dashboard synchronized for room ${roomNumber}`);
        }
      } catch (error) {
        console.error('Error synchronizing dashboard:', error);
        // Don't show error to user as this is background sync
      }
    }

    // Enhanced room card update with animations and change detection
    function updateSpecificRoomCardWithAnimation(roomNumber, newStudents, previousStudents = []) {
      const roomCards = document.querySelectorAll(`.room-box[data-room-number="${roomNumber}"]`);

      roomCards.forEach(roomCard => {
        // Determine the type of change
        const changeType = getChangeType(previousStudents, newStudents);

        // Update the room card content
        updateRoomCardStudents(roomCard, newStudents);

        // Apply appropriate animation based on change type
        applyChangeAnimation(roomCard, changeType);

        // Update room status indicators
        updateRoomStatusIndicators(roomCard, newStudents);
      });
    }

    // Backward compatibility function
    function updateSpecificRoomCard(roomNumber, students) {
      updateSpecificRoomCardWithAnimation(roomNumber, students, []);
    }

    // Determine the type of change that occurred
    function getChangeType(previousStudents, newStudents) {
      if (previousStudents.length === 0 && newStudents.length > 0) {
        return 'added';
      } else if (previousStudents.length > 0 && newStudents.length === 0) {
        return 'removed_all';
      } else if (previousStudents.length > newStudents.length) {
        return 'removed';
      } else if (previousStudents.length < newStudents.length) {
        return 'added';
      } else {
        return 'modified';
      }
    }

    // Apply appropriate animation based on change type (DISABLED - No animations)
    function applyChangeAnimation(roomCard, changeType) {
      // Remove any existing animation classes
      roomCard.classList.remove('room-updated', 'room-added', 'room-removed', 'room-modified', 'room-pulse');

      // Clear any existing animation styles
      roomCard.style.transform = '';
      roomCard.style.boxShadow = '';
      roomCard.style.borderColor = '';
      roomCard.style.transition = '';

      // No animations applied - room cards remain static
    }

    // Update room status indicators (occupancy, capacity warnings, etc.)
    function updateRoomStatusIndicators(roomCard, students) {
      // Update occupancy badge
      const occupancyBadge = roomCard.querySelector('.occupancy-badge');
      if (occupancyBadge) {
        const capacity = parseInt(roomCard.dataset.capacity) || 6;
        const occupancy = students.length;
        const percentage = Math.round((occupancy / capacity) * 100);

        occupancyBadge.textContent = `${occupancy}/${capacity}`;

        // Update badge color based on occupancy
        occupancyBadge.className = 'occupancy-badge';
        if (percentage >= 100) {
          occupancyBadge.classList.add('badge-danger');
        } else if (percentage >= 80) {
          occupancyBadge.classList.add('badge-warning');
        } else {
          occupancyBadge.classList.add('badge-success');
        }
      }

      // Update room status icon
      const statusIcon = roomCard.querySelector('.room-status-icon');
      if (statusIcon) {
        if (students.length === 0) {
          statusIcon.className = 'fi fi-rr-house-blank room-status-icon';
          statusIcon.style.color = '#6c757d';
        } else {
          statusIcon.className = 'fi fi-rr-house-user room-status-icon';
          statusIcon.style.color = '#28a745';
        }
      }
    }

    // Enhanced room card update function with better occupancy display
    function updateRoomCardStudents(roomCard, students) {
      const occupantsList = roomCard.querySelector('.occupants-list');
      if (occupantsList && Array.isArray(students)) {
        // Clear existing students
        occupantsList.innerHTML = '';

        if (students.length === 0) {
          const li = document.createElement('li');
          li.style.color = '#666';
          li.style.fontStyle = 'italic';
          li.textContent = 'No students assigned';
          occupantsList.appendChild(li);
        } else {
          // Add new students
          students.forEach(student => {
            const li = document.createElement('li');
            li.textContent = student;
            li.style.padding = '2px 0';
            li.style.borderBottom = '1px solid #eee';
            occupantsList.appendChild(li);
          });
        }

        // Update occupancy count with dynamic capacity (display expands if students exceed configured capacity)
        const occupancySpan = roomCard.querySelector('.occupancy');
        if (occupancySpan) {
          // Read configured capacity from data-capacity attribute (fallback to 6)
          const configuredAttr = parseInt(roomCard.getAttribute('data-capacity'));
          const configuredCapacity = Number.isFinite(configuredAttr) ? configuredAttr : 6;
          const displayCapacity = Math.max(configuredCapacity, students.length);

          occupancySpan.textContent = `${students.length}/${displayCapacity}`;

          // Update occupancy color based on percentage of displayCapacity
          const percentage = (students.length / displayCapacity) * 100;
          if (percentage >= 100) {
            occupancySpan.style.color = '#dc3545'; // Red for full
          } else if (percentage >= 80) {
            occupancySpan.style.color = '#ffc107'; // Yellow for nearly full
          } else {
            occupancySpan.style.color = '#28a745'; // Green for available
          }
        }

        // Update student count in header if it exists
        const studentCountElement = roomCard.querySelector('.student-count');
        if (studentCountElement) {
          studentCountElement.textContent = students.length;
        }

        console.log(`Updated room card for room ${roomCard.getAttribute('data-room-number')} with ${students.length} students`);
      }
    }



    document.addEventListener('DOMContentLoaded', function() {
      const toggleFloorsBtn = document.getElementById('toggleFloorsBtn');
      const floorButtons = document.getElementById('floorButtons');
      const roomsContainer = document.getElementById('roomsContainer');
      const studentModal = document.getElementById('studentModal');
      const newRoomModal = document.getElementById('newRoomModal');

      // Initialize real-time synchronization
      initializeRealTimeSync();

      const addFloorModal = document.getElementById('addFloorModal');
      const deleteFloorModal = document.getElementById('deleteFloorModal');
      const capacityModal = document.getElementById('capacityModal');

      // Toggle floor buttons dropdown
      if (toggleFloorsBtn && floorButtons) {
        toggleFloorsBtn.addEventListener('click', function() {
          const isHidden = floorButtons.style.display === 'none' || floorButtons.style.display === '';
          if (isHidden) {
            floorButtons.style.display = 'grid';
            toggleFloorsBtn.textContent = 'Hide floors';
          } else {
            floorButtons.style.display = 'none';
            toggleFloorsBtn.textContent = 'Show floors';
            // Hide rooms container when floors are hidden
            if (roomsContainer) {
              roomsContainer.classList.add('hidden');
            }
          }
        });
      }

      // Setup capacity settings button
      const capacitySettingsBtn = document.getElementById('capacitySettingsBtn');
      if (capacitySettingsBtn) {
        capacitySettingsBtn.addEventListener('click', function() {
          openCapacityModal();
        });
      }

      // Room Capacity Settings Functions
      function openCapacityModal() {
        if (capacityModal) {
          // Load current capacity and stats
          loadCapacityStats();
          capacityModal.style.display = 'block';
        }
      }

  // Student view detection
  const isStudentView = @json($isStudentView ?? false);
  // Inspector role detection (educators have full access, inspectors have limited access)
  const isInspector = @json((auth()->user()->user_role ?? '') === 'inspector');

      // Floor data with student assignments - dynamically loaded from  PNPh Management System
      const roomStudents = @json($roomStudents ?? []);
      const floorsAndRooms = @json($floorsAndRooms ?? []);

      // Dynamically construct floorData from DB-backed data
      const floorData = {};
      Object.keys(floorsAndRooms).forEach(floor => {
        const rooms = floorsAndRooms[floor].map(r => r.toString());
        const students = {};
        rooms.forEach(roomNum => {
          students[roomNum] = roomStudents[roomNum] || [];
        });
        floorData[floor] = { rooms, students };
      });

      // Initialize global cache of room assignments so periodic syncs have a baseline
      window.roomStudents = window.roomStudents || (typeof roomStudents !== 'undefined' ? roomStudents : {});

      // Helper to show room cards for a floor (reusable for static and dynamic floor buttons)
      function showFloor(floor) {
        console.log('showFloor called with', floor);
        const floorKey = String(floor);

        // Try to locate floor data using string key or original key
        const data = floorData[floorKey] || floorData[floor];
        if (!data) {
          console.warn('No floor data for', floor, 'available keys:', Object.keys(floorData));
          return;
        }

        // mark active button
        document.querySelectorAll('.floor-btn').forEach(b => b.classList.remove('active'));
        const btn = document.querySelector(`.floor-btn[data-floor="${floorKey}"]`) || document.querySelector(`.floor-btn[data-floor="${floor}"]`);
        if (btn) btn.classList.add('active');

        roomsContainer.classList.remove('hidden');
        roomsContainer.innerHTML = '';

        const roomsWrapper = document.createElement('div');
        roomsWrapper.className = 'rooms-wrapper';
        roomsWrapper.style.display = 'grid';
        roomsWrapper.style.gridTemplateColumns = 'repeat(auto-fill, minmax(300px, 1fr))';
        roomsWrapper.style.gap = '20px';

        // Show all rooms for the floor
        let roomsWithStudents = 0;
        data.rooms.forEach(roomNumber => {
          const roomBox = createRoomBox(roomNumber, floorKey);
          roomsWrapper.appendChild(roomBox);
          if (data.students[roomNumber] && data.students[roomNumber].length > 0) {
            roomsWithStudents++;
          }
        });

        if (roomsWithStudents === 0) {
          const noRoomsMessage = document.createElement('div');
          noRoomsMessage.style.cssText = `
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            color: #6c757d;
            font-size: 1.1rem;
            margin: 20px 0;
          `;
          noRoomsMessage.innerHTML = `
            <i class="fas fa-home" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
            No rooms with assigned students on Floor ${floor}
          `;
          roomsWrapper.appendChild(noRoomsMessage);
        }

        roomsContainer.appendChild(roomsWrapper);
        setupRoomEventListeners();
      }

      // Handle floor button clicks - use the shared showFloor helper
      const floorBtns = document.querySelectorAll('.floor-btn');
      console.log('Found floor buttons:', floorBtns.length);
      floorBtns.forEach(btn => {
        btn.addEventListener('click', function() {
          const floor = this.dataset.floor;
          console.log('Floor button clicked:', floor);
          showFloor(floor);
        });
      });

      function createRoomBox(roomNumber, floor) {
        const roomBox = document.createElement('div');
        roomBox.className = 'room-box'; // Keep original class for compatibility
        roomBox.setAttribute('data-room-number', roomNumber);

        // Different content for student view vs admin view
        if (isStudentView) {
          // Get students for this room
          const roomStudents = floorData[floor].students[roomNumber] || [];
          const studentCount = roomStudents.length;

          roomBox.innerHTML = `
            <h3>Room ${roomNumber}</h3>
            <div class="student-occupants">
              <h4>
                Student Occupants (${studentCount})
                <div style="background: #e3f2fd; color: #1565c0; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem; display:flex; align-items:center; gap:6px;">
                  <i class="fi fi-rr-eye" style="font-size:0.9rem;"></i>
                  <span>View Only</span>
                </div>
              </h4>
              <ul class="occupants-list">
                ${roomStudents.length > 0
                  ? roomStudents.map(student => `<li>${student}</li>`).join('')
                  : '<li style="color: #666; font-style: italic;">No students assigned</li>'
                }
              </ul>
            </div>
            <div class="room-footer">
              <div style="background: #e3f2fd; color: #1565c0; padding: 8px 12px; border-radius: 6px; font-size: 0.85rem; text-align: center;">
                Student View: Cannot edit assignments
              </div>
              <a href="/roomtask/${roomNumber}?student_view=1" class="continue-btn">View Checklist</a>
            </div>
          `;
        } else {
          // Get students for this room
          const roomStudents = floorData[floor].students[roomNumber] || [];
          const studentCount = roomStudents.length;
          // Default capacity until backend sync updates it
          const initialCapacity = 6;
          // Store configured capacity on the element so other code can read it
          roomBox.setAttribute('data-capacity', initialCapacity);
          // Display capacity should expand if current students exceed configured capacity
          const displayCapacity = Math.max(initialCapacity, studentCount);

          roomBox.innerHTML = `
            <h3>Room ${roomNumber}</h3>
            <div class="student-occupants">
                <h4>
                Student Occupants (<span class="occupancy">${studentCount}/${displayCapacity}</span>)
              </h4>
              <ul class="occupants-list">
                ${roomStudents.length > 0
                  ? roomStudents.map(student => `<li>${student}</li>`).join('')
                  : '<li style="color: #666; font-style: italic;">No students assigned</li>'
                }
              </ul>
            </div>
              <div class="room-footer">
                ${isInspector ? `
                  <button class="edit-capacity-btn" disabled style="background: #6c757d; color: #fff; border: none; padding: 8px 12px; border-radius: 4px; cursor: not-allowed; opacity: 0.7;">
                    <i class="fi fi-rr-settings"></i> Edit Capacity
                  </button>
                ` : `
                  <button class="edit-capacity-btn" style="background: #17a2b8; color: white; border: none; padding: 8px 12px; border-radius: 4px; cursor: pointer;">
                    <i class="fi fi-rr-settings"></i> Edit Capacity
                  </button>
                `}
                <a href="/roomtask/${roomNumber}" class="continue-btn">Checklist</a>
              </div>
          `;
        }
        return roomBox;
      }

      function setupRoomEventListeners() {
        // Edit Room Capacity button
        document.querySelectorAll('.edit-capacity-btn').forEach(btn => {
          btn.addEventListener('click', function(e) {
            e.preventDefault();
            const roomBox = this.closest('.room-box');
            const roomNumber = roomBox.getAttribute('data-room-number');
            openIndividualCapacityModal(roomNumber);
          });
        });

        // Room management is now handled in the dedicated room management system
      }

      function openModal(roomNumber, action) {
        const modal = document.getElementById('studentModal');
        const modalTitle = document.getElementById('modalTitle');
        const roomNumberInput = document.getElementById('roomNumber');
        const actionTypeInput = document.getElementById('actionType');
        const studentNameInput = document.getElementById('studentName');
        const existingStudentSelect = document.getElementById('existingStudent');
        const editStudentSelectDiv = document.getElementById('editStudentSelect');
        const addStudentSelectDiv = document.getElementById('addStudentSelect');
        const manualStudentInput = document.getElementById('manualStudentInput');

        modalTitle.textContent = action === 'add' ? 'Add Student' :
                               action === 'edit' ? 'Edit Student' : 'Delete Student';
        actionTypeInput.value = action;
        roomNumberInput.value = roomNumber;

        if (action === 'add') {
          editStudentSelectDiv.style.display = 'none';
          addStudentSelectDiv.style.display = 'block';
          manualStudentInput.style.display = 'block';
          studentNameInput.required = true;
          loadValidStudentsForAdd();
        } else {
          editStudentSelectDiv.style.display = 'block';
          addStudentSelectDiv.style.display = 'none';
          populateStudentSelect(roomNumber);
          if (action === 'edit') {
            manualStudentInput.style.display = 'block';
            studentNameInput.required = true;
          } else {
            manualStudentInput.style.display = 'none';
            studentNameInput.required = false;
          }
        }

        modal.style.display = 'block';
      }

      // Load valid students from Login  PNPh Management System for adding
      async function loadValidStudentsForAdd() {
        const validStudentSelect = document.getElementById('validStudentSelect');
        const studentNameInput = document.getElementById('studentName');

        try {
          validStudentSelect.innerHTML = '<option value="">-- Loading students... --</option>';

          const response = await fetch('/api/students/valid-list', {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': csrfToken
            }
          });

          const data = await response.json();

          if (data.success) {
            validStudentSelect.innerHTML = '<option value="">-- Select a student --</option>';

            data.students.forEach(student => {
              const option = document.createElement('option');
              option.value = student.name;
              option.textContent = `${student.name} (${student.gender === 'M' ? 'Male' : 'Female'})`;
              option.dataset.gender = student.gender;
              validStudentSelect.appendChild(option);
            });

            // Add event listener for dropdown selection
            validStudentSelect.addEventListener('change', function() {
              if (this.value) {
                studentNameInput.value = this.value;
              }
            });

          } else {
            validStudentSelect.innerHTML = '<option value="">-- Error loading students --</option>';
            console.error('Failed to load students:', data.message);
          }

        } catch (error) {
          console.error('Error loading valid students:', error);
          validStudentSelect.innerHTML = '<option value="">-- Error loading students --</option>';
        }
      }

      function openNewRoomModal() {
        newRoomModal.style.display = 'block';
      }

      function openAddFloorModal() {
        addFloorModal.style.display = 'block';
      }

      function openDeleteFloorModal() {
        // Populate the floor dropdown with floors 2–7 and any additional floors present in floorData
        const floorSelect = document.getElementById('floorToDelete');
        floorSelect.innerHTML = '<option value="">Select a floor</option>';

        // Always include floors 2–7
        function getOrdinal(n) {
          const s = ["th", "st", "nd", "rd"], v = n % 100;
          return n + (s[(v - 20) % 10] || s[v] || s[0]);
        }

        for (let i = 2; i <= 7; i++) {
          if (floorData[i]) {
            const option = document.createElement('option');
            option.value = i;
            option.textContent = `${getOrdinal(i)} floor`;
            floorSelect.appendChild(option);
          }
        }

        // Include any additional floors (8+ or dynamically added)
        Object.keys(floorData).forEach(floor => {
          const num = parseInt(floor);
          if (num >= 8) {
            const option = document.createElement('option');
            option.value = floor;
            option.textContent = `${getOrdinal(num)} floor`;
            floorSelect.appendChild(option);
          }
        });

        deleteFloorModal.style.display = 'block';
      }

  // Expose to global scope for inline onclick handlers (Quick Actions)
  // Assign immediately so inline onclick attributes can find these functions
  window.openAddFloorModal = openAddFloorModal;
  // Expose opening the single-room modal as well so the Quick Actions button works
  window.openNewRoomModal = openNewRoomModal;
  window.openDeleteFloorModal = openDeleteFloorModal;

      function closeModal(modal) {
        modal.style.display = 'none';
        if (modal === studentModal) {
          document.getElementById('studentForm').reset();
        } else if (modal === addFloorModal) {
          document.getElementById('addFloorForm').reset();
        } else if (modal === deleteFloorModal) {
          document.getElementById('deleteFloorForm').reset();
        } else if (modal === capacityModal) {
          document.getElementById('capacityForm').reset();
        } else {
          document.getElementById('newRoomForm').reset();
        }
      }



      // Close button handlers
      document.querySelectorAll('.close, .cancel-btn').forEach(btn => {
        btn.addEventListener('click', function() {
          const modal = this.closest('.modal');
          closeModal(modal);
        });
      });

      // Close modal when clicking outside
      window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
          closeModal(event.target);
        }
      });



      function updateFloorDataWithNewAssignments(roomStudents) {
        // Merge incoming roomStudents into floorData without clearing rooms that
        // are not included in the payload. This avoids wiping student lists when
        // the server returns a partial set of rooms.
        if (!roomStudents || typeof roomStudents !== 'object') return;

        // For each room in the incoming payload, find which floor contains it
        // and update only that room's student list. Do not clear other rooms.
        Object.keys(roomStudents).forEach(roomNum => {
          // Find the floor that contains this room
          for (const floorKey of Object.keys(floorData)) {
            const rooms = floorData[floorKey].rooms || [];
            // Normalize to strings to avoid mismatches between numeric and string room ids
            const roomStrings = rooms.map(r => String(r));
            if (roomStrings.includes(String(roomNum))) {
              // Ensure students array exists and update it
              floorData[floorKey].students = floorData[floorKey].students || {};
              floorData[floorKey].students[roomNum] = roomStudents[roomNum] || [];
              break; // stop searching floors once updated
            }
          }
        });
        // After merging, ensure the dashboard snapshot stat is refreshed
        try { computeAndRenderTotalAssignedStudents(); } catch (e) { /* noop if function not available yet */ }
      }

      // Compute total assigned students across all rooms and update the stat DOM
      function computeAndRenderTotalAssignedStudents() {
        try {
          let total = 0;

          // Prefer authoritative in-memory floorData when available
          if (typeof floorData !== 'undefined' && floorData && Object.keys(floorData).length > 0) {
            Object.values(floorData).forEach(floor => {
              if (floor && floor.students && typeof floor.students === 'object') {
                Object.values(floor.students).forEach(arr => {
                  if (Array.isArray(arr)) total += arr.length;
                });
              }
            });
          } else if (window.roomStudents && typeof window.roomStudents === 'object') {
            // Fallback to flat room->students mapping
            Object.values(window.roomStudents).forEach(arr => {
              if (Array.isArray(arr)) total += arr.length;
            });
          }

          const el = document.getElementById('statStudentsOccupying');
          if (el) el.textContent = String(total);
          return total;
        } catch (err) {
          console.warn('computeAndRenderTotalAssignedStudents error', err);
          return 0;
        }
      }

      function populateStudentSelect(roomNumber) {
        const select = document.getElementById('existingStudent');
        select.innerHTML = '<option value="">-- Select a student --</option>';

        const roomBox = document.querySelector(`[data-room-number="${roomNumber}"]`);
        if (roomBox) {
          const students = Array.from(roomBox.querySelectorAll('.occupants-list li'))
            .map(li => li.textContent.trim());

          students.forEach(student => {
            const option = document.createElement('option');
            option.value = student;
            option.textContent = student;
            select.appendChild(option);
          });
        }
      }

      // Form submission handlers
      document.getElementById('studentForm').addEventListener('submit', handleStudentFormSubmit);
      document.getElementById('newRoomForm').addEventListener('submit', handleNewRoomFormSubmit);
      document.getElementById('addFloorForm').addEventListener('submit', handleAddFloorFormSubmit);
      document.getElementById('deleteFloorForm').addEventListener('submit', handleDeleteFloorFormSubmit);

      function handleStudentFormSubmit(event) {
        event.preventDefault();
        const form = event.target;
        const roomNumber = form.roomNumber.value;
        const action = form.actionType.value;
        const roomBox = document.querySelector(`[data-room-number="${roomNumber}"]`);
        const occupantsList = roomBox.querySelector('.occupants-list');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        if (action === 'add') {
          const studentName = form.studentName.value;
          if (!studentName) {
            alert('Please enter a student name.');
            return;
          }
          fetch('/api/room/add-student', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              room: roomNumber,
              name: studentName
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data && data.success) {
              const li = document.createElement('li');
              li.textContent = studentName;
              occupantsList.appendChild(li);

              // Build updated students array from DOM to keep local state consistent
              const updatedStudents = Array.from(occupantsList.querySelectorAll('li')).map(li => li.textContent.trim()).filter(s => s && !/no students assigned/i.test(s) && !/loading/i.test(s));

              // Update dashboard local cache and UI for this specific room so changes persist across syncs/reloads
              try {
                updateDashboardWithRoomData({ [roomNumber]: updatedStudents });
              } catch (e) {
                console.warn('Failed to update dashboard with new students for room', roomNumber, e);
              }

              closeModal(studentModal);
            } else {
              alert((data && data.message) ? data.message : 'Failed to add student.');
            }
          })
          .catch(err => {
            alert('Failed to add student. Please try again.');
          });
        } else if (action === 'edit') {
          const oldName = form.existingStudent.value;
          const newName = form.studentName.value;

          if (!oldName || !newName) {
            alert('Please select a student and enter a new name.');
            return;
          }
          fetch('/api/room/edit-student', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              room: roomNumber,
              old_name: oldName,
              new_name: newName
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data && data.success) {
              const studentToUpdate = Array.from(occupantsList.children).find(
                li => li.textContent.trim() === oldName
              );
              if (studentToUpdate) {
                studentToUpdate.textContent = newName;
                  // Rebuild students array and update local dashboard state
                  const updatedStudents = Array.from(occupantsList.querySelectorAll('li')).map(li => li.textContent.trim()).filter(s => s && !/no students assigned/i.test(s) && !/loading/i.test(s));
                  try { updateDashboardWithRoomData({ [roomNumber]: updatedStudents }); } catch(e) { console.warn('Failed to update dashboard after edit for room', roomNumber, e); }
              }
              closeModal(studentModal);
            } else {
              alert((data && data.message) ? data.message : 'Failed to edit student.');
            }
          })
          .catch(() => {
            alert('Failed to edit student. Please try again.');
          });
        } else if (action === 'delete') {
          const studentName = form.existingStudent.value;
          if (!studentName) {
            alert('Please select a student to delete.');
            return;
          }
          if (!confirm(`Are you sure you want to delete ${studentName}?`)) {
            return;
          }
          fetch('/api/room/delete-student', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              room: roomNumber,
              name: studentName
            })
          })
          .then(res => res.json())
          .then(data => {
            if (data && data.success) {
              const studentToRemove = Array.from(occupantsList.children).find(
                li => li.textContent.trim() === studentName
              );
              if (studentToRemove) {
                studentToRemove.remove();
                  // Rebuild students array and update local dashboard state
                  const updatedStudents = Array.from(occupantsList.querySelectorAll('li')).map(li => li.textContent.trim()).filter(s => s && !/no students assigned/i.test(s) && !/loading/i.test(s));
                  try { updateDashboardWithRoomData({ [roomNumber]: updatedStudents }); } catch(e) { console.warn('Failed to update dashboard after delete for room', roomNumber, e); }
              }
              closeModal(studentModal);
            } else {
              alert((data && data.message) ? data.message : 'Failed to delete student.');
            }
          })
          .catch(() => {
            alert('Failed to delete student. Please try again.');
          });
        }
      }

  async function handleNewRoomFormSubmit(event) {
        event.preventDefault();
        const roomNumber = document.getElementById('newRoomNumber').value;
        const floor = Math.floor(roomNumber / 100);

        if (!roomNumber) {
          alert('Please enter a room number.');
          return;
        }

        const existingRoom = document.querySelector(`[data-room-number="${roomNumber}"]`);
        if (existingRoom) {
          alert('This room already exists.');
          return;
        }

        // Persist the new room on server via room-management endpoint
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        try {
          showLoader('Creating room...');
        } catch(e) {}

        try {
          const res = await fetch('/room-management/rooms', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin',
            body: JSON.stringify({ room_number: roomNumber, capacity: 6, status: 'active' })
          });

          const text = await res.text().catch(() => '');
          let json = {};
          try { json = text ? JSON.parse(text) : {}; } catch(e) { json = { __raw: text }; }

          if (!res.ok) {
            let msg = json && json.message ? json.message : `Failed to create room (HTTP ${res.status})`;
            try { hideLoader(); } catch(e) {}
            alert(msg);
            return;
          }

          // Success: merge into local floorData and refresh UI
          if (!floorData[floor]) {
            floorData[floor] = { rooms: [], students: {} };
          }
          if (!floorData[floor].rooms.includes(roomNumber)) {
            floorData[floor].rooms.push(String(roomNumber));
            floorData[floor].students[String(roomNumber)] = [];
          }

          // Ensure a floor button exists
          const existingBtn = document.querySelector(`[data-floor="${floor}"]`);
          if (!existingBtn && parseInt(floor, 10) >= 2) {
            addFloorButton(floor);
          }

          // Trigger click on the floor button to refresh the display
          const floorBtn = document.querySelector(`[data-floor="${floor}"]`);
          if (floorBtn) floorBtn.click();

          try { hideLoader(); } catch(e) {}
          try { showResult('success', `Created room ${roomNumber}`, 2500); } catch(e) { alert(`Created room ${roomNumber}`); }

        } catch (error) {
          console.error('Error creating room:', error);
          try { hideLoader(); } catch(e) {}
          alert('Failed to create room. Please try again.');
        } finally {
          closeModal(newRoomModal);
        }
      }

      async function handleAddFloorFormSubmit(event) {
        event.preventDefault();
        // Show loading indicator
        if (window.Swal) {
          Swal.fire({ title: 'Creating rooms...', html: 'Please wait while rooms are being created', allowOutsideClick: false, didOpen: () => { Swal.showLoading(); } });
        }
        const form = event.target;
        const floorNumber = parseInt(form.newFloorNumber.value, 10);
        const rawRooms = (form.studentRooms && form.studentRooms.value) ? form.studentRooms.value.trim() : '';
        const numRooms = (form.numRooms && form.numRooms.value) ? parseInt(form.numRooms.value, 10) : null;

        if (!floorNumber || (!rawRooms && !numRooms)) {
          alert('Please fill in the floor number and either provide a number of rooms or explicit room numbers.');
          return;
        }

        // Basic floor validation (allow floors 2+)
        if (floorNumber < 2 || floorNumber > 99) {
          alert('Please provide a valid floor number (2-99).');
          return;
        }

        let parsedRooms = [];
        const invalid = [];

        if (rawRooms) {
          // Parse explicit room numbers from comma/space separated input
          const tokens = rawRooms.split(/[,\s]+/).map(t => t.trim()).filter(Boolean);
          tokens.forEach(tok => {
            if (/^[0-9]{3,6}$/.test(tok)) {
              parsedRooms.push(tok);
            } else if (/^[0-9]+$/.test(tok)) {
              parsedRooms.push(tok);
            } else {
              invalid.push(tok);
            }
          });

          if (invalid.length > 0) {
            alert('Invalid room numbers detected: ' + invalid.join(', '));
            return;
          }
        } else if (numRooms && Number.isInteger(numRooms) && numRooms > 0) {
          // Auto-generate sequential room numbers for the floor: floor*100 + i (1..numRooms)
          for (let i = 1; i <= numRooms; i++) {
            const rn = String((floorNumber * 100) + i);
            parsedRooms.push(rn);
          }
        }

        // Ensure rooms match the floor prefix when possible (e.g., floor 5 -> 5xx)
        const floorPrefix = String(floorNumber);
        const filteredRooms = parsedRooms.filter(rn => rn.startsWith(floorPrefix));
        if (filteredRooms.length === 0) {
          if (!confirm('None of the provided room numbers appear to match the selected floor. Continue with the provided room numbers?')) {
            return;
          }
        }

        // Deduplicate and filter already-existing rooms
        const deduped = [...new Set(parsedRooms)];
        const roomsToCreate = deduped.filter(rn => {
          for (const f in floorData) {
            if (floorData[f].rooms && floorData[f].rooms.includes(rn)) return false;
          }
          return true;
        });

        if (roomsToCreate.length === 0) {
          alert('No new rooms to create (all provided rooms already exist).');
          closeModal(addFloorModal);
          return;
        }

        // Create rooms via room-management endpoint (persistently)
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const createResults = [];
        // Show lightweight loader while creating rooms
        try { showLoader('Creating rooms...'); } catch(e){}

        for (const rn of roomsToCreate) {
          try {
            const res = await fetch('/room-management/rooms', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
              },
              credentials: 'same-origin',
              body: JSON.stringify({ room_number: rn, capacity: 6, status: 'active' })
            });

            const text = await res.text().catch(() => '');
            let json = null;
            try { json = text ? JSON.parse(text) : {}; } catch(e) { json = { __raw: text }; }

            createResults.push({ room: rn, ok: res.ok, status: res.status, body: json });
          } catch (err) {
            createResults.push({ room: rn, ok: false, error: err.toString() });
          }
        }

        const failures = createResults.filter(r => !r.ok);
        const successes = createResults.filter(r => r.ok).map(r => r.room);

        // Merge successes into local floorData
        if (!floorData[floorNumber]) {
          floorData[floorNumber] = { rooms: [], students: {} };
        }
        successes.forEach(rn => {
          if (!floorData[floorNumber].rooms.includes(rn)) {
            floorData[floorNumber].rooms.push(rn);
            floorData[floorNumber].students[rn] = [];
          }
        });

        // Ensure a button exists for this floor
        const existingBtn = document.querySelector(`[data-floor="${floorNumber}"]`);
        if (!existingBtn && parseInt(floorNumber, 10) >= 2) {
          addFloorButton(floorNumber);
        }

        // Build a friendly result message including validation/server messages
        const failureDetails = failures.map(f => {
          // Network/exception
          if (f.error) return `${f.room} — ${f.error}`;

          // If server returned structured JSON message, prefer it
          if (f.body) {
            if (f.body.message) return `${f.room} — ${f.body.message}`;
            if (f.body.errors) {
              // Flatten validation errors
              try {
                const errs = Object.values(f.body.errors).flat().join('; ');
                if (errs) return `${f.room} — ${errs}`;
              } catch (e) {}
            }
            if (f.body.__raw) return `${f.room} — ${String(f.body.__raw).substring(0,200)}`;
          }

          // Fallback to HTTP status
          if (f.status) return `${f.room} — HTTP ${f.status}`;
          return `${f.room} — Unknown error`;
        });

  // Close modal and inform user with a friendly message
  closeModal(addFloorModal);
        let messageParts = [];
  if (successes.length > 0) messageParts.push(`<i class="fi fi-rr-check" style="margin-right:6px;color:#28a745;"></i> Created rooms: ${successes.join(', ')}`);
  if (failureDetails.length > 0) messageParts.push(`<i class="fi fi-rr-exclamation" style="margin-right:6px;color:#ffc107;"></i> Failed to create: ${failureDetails.join(' | ')}`);
        messageParts.push('Refreshing dashboard...');

        const finalMessage = messageParts.join('\n\n');
        // Close loader and show result
        try { hideLoader(); } catch(e){}
        if (failureDetails.length > 0) {
          try { showResult('error', `Failed to create: ${failureDetails.join(' | ')}`, 7000); } catch(e) { if (typeof showEnhancedAlert === 'function') showEnhancedAlert('error', finalMessage, 8000); else alert(finalMessage); }
        } else {
          try { showResult('success', `Created rooms: ${successes.join(', ')}`, 3500); } catch(e) { if (typeof showEnhancedAlert === 'function') showEnhancedAlert('success', finalMessage, 4000); else alert(finalMessage); }
        }

        // Re-sync with authoritative backend state
        try { await syncWithRoomManagement(); } catch(e) { console.warn('Sync failed after creating rooms', e); }
      }

  async function handleDeleteFloorFormSubmit(event) {
        event.preventDefault();
        // show lightweight loader
        try { showLoader('Deleting floor...'); } catch(e) {}
        const form = event.target;
        const floorToDelete = form.floorToDelete.value;

        if (!floorToDelete) {
          alert('Please select a floor to delete.');
          return;
        }

        // Validate floor number (only floors 2 and above can be deleted)
        // Floor 1 is reserved and cannot be deleted via the dashboard UI
        if (parseInt(floorToDelete) < 2) {
          alert('Only floors 2 and above can be deleted. Floor 1 is reserved.');
          return;
        }

        // Confirm deletion
        const roomCount = floorData[floorToDelete] ? floorData[floorToDelete].rooms.length : 0;
        const studentCount = floorData[floorToDelete] ?
          Object.values(floorData[floorToDelete].students).reduce((total, students) => total + students.length, 0) : 0;

        if (!confirm(`Are you sure you want to delete Floor ${floorToDelete}?\n\nThis will remove:\n• ${roomCount} rooms\n• ${studentCount} student assignments\n\nThis action cannot be undone!`)) {
          return;
        }
        // If there are no rooms on this floor locally, just remove UI and attempt a sync
        const roomsOnFloor = (floorData[floorToDelete] && Array.isArray(floorData[floorToDelete].rooms)) ? floorData[floorToDelete].rooms.slice() : [];

        if (roomsOnFloor.length === 0) {
          // Remove from floorData and UI and then sync
          delete floorData[floorToDelete];
          const floorBtnEmpty = document.querySelector(`[data-floor="${floorToDelete}"]`);
          if (floorBtnEmpty) floorBtnEmpty.remove();
          const currentFloorTitleEmpty = document.querySelector('h3');
          if (currentFloorTitleEmpty && currentFloorTitleEmpty.textContent.includes(`Floor ${floorToDelete}`)) {
            roomsContainer.innerHTML = '';
            roomsContainer.classList.add('hidden');
          }
          closeModal(deleteFloorModal);
          showEnhancedAlert('success', `<i class="fi fi-rr-trash" style="margin-right:6px;color:#6c757d;"></i> Floor ${floorToDelete} has been removed from the dashboard (no rooms present).`, 4000);
          // Refresh from backend to ensure consistency
          try { await syncWithRoomManagement(); } catch(e) { console.warn('Sync failed after empty-floor delete', e); }
          return;
        }

        // Ask server to delete each room persistently. We run requests in parallel and wait for them.
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        const deletePromises = roomsOnFloor.map(roomNumber => {
          const url = `/api/dashboard/delete-room/${encodeURIComponent(roomNumber)}`;
          return fetch(url, {
            method: 'DELETE',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin'
          }).then(async res => {
            const text = await res.text().catch(() => '');
            let json = null;
            try { json = text ? JSON.parse(text) : {}; } catch(e) { json = { __raw: text }; }
            return { room: roomNumber, ok: res.ok, status: res.status, body: json };
          }).catch(err => ({ room: roomNumber, ok: false, error: err }));
        });

        // Wait for deletion results
        const results = await Promise.all(deletePromises);

        const failed = results.filter(r => !r.ok);
        if (failed.length > 0) {
          // Build an error message showing which rooms failed
          const failedRooms = failed.map(f => `${f.room} (${f.status || 'network'})`).join(', ');
          console.error('Failed to delete rooms on server:', failed);
          closeModal(deleteFloorModal);
          try { hideLoader(); } catch(e) {}
          try { showResult('error', `Failed to delete some rooms on Floor ${floorToDelete}: ${failedRooms}. Please check server logs or try again.`, 7000); }
          catch(e) { if (typeof showEnhancedAlert === 'function') showEnhancedAlert('error', `Failed to delete some rooms on Floor ${floorToDelete}: ${failedRooms}. Please check server logs or try again.`, 8000); else alert(`Failed to delete some rooms on Floor ${floorToDelete}: ${failedRooms}.`); }
          // Refresh from backend to restore authoritative view
          try { await syncWithRoomManagement(); } catch(e) { console.warn('Sync failed after partial-delete', e); }
          return;
        }

        // All deletions succeeded on server -> remove floor locally and update UI
        delete floorData[floorToDelete];
        const floorBtn = document.querySelector(`[data-floor="${floorToDelete}"]`);
        if (floorBtn) {
          floorBtn.remove();
        }

        // Clear rooms container if this floor was currently displayed
        const currentFloorTitle = document.querySelector('h3');
        if (currentFloorTitle && currentFloorTitle.textContent.includes(`Floor ${floorToDelete}`)) {
          roomsContainer.innerHTML = '';
          roomsContainer.classList.add('hidden');
        }

  // Close modal and show success message
  closeModal(deleteFloorModal);
  try { hideLoader(); } catch(e) {}
  try { showResult('success', `Floor ${floorToDelete} and its rooms have been deleted permanently.`, 3500); }
  catch(e) { if (typeof showEnhancedAlert === 'function') showEnhancedAlert('success', `<i class="fi fi-rr-trash" style="margin-right:6px;color:#6c757d;"></i> Floor ${floorToDelete} and its rooms have been deleted permanently.`, 5000); else alert(`Floor ${floorToDelete} deleted.`); }

        // Re-sync with backend to ensure the UI reflects the persistent state
        try {
          await syncWithRoomManagement();
        } catch (e) {
          console.warn('Error syncing after floor delete:', e);
        }
      }

      function getOrdinalSuffix(num) {
        const j = num % 10;
        const k = num % 100;
        if (j == 1 && k != 11) return 'st';
        if (j == 2 && k != 12) return 'nd';
        if (j == 3 && k != 13) return 'rd';
        return 'th';
      }

      // Room Management Synchronization
      async function syncWithRoomManagement() {
        try {
          const response = await fetch('/api/dashboard/room-data');
          const data = await response.json();

          if (data.success) {
            // Update floorData with latest room information
            const updatedFloorData = data.floor_data || {};

            // Merge new data with existing floorData without wiping student lists
            Object.keys(updatedFloorData).forEach(floor => {
              const incoming = updatedFloorData[floor] || {};
              if (floorData[floor]) {
                // Replace the rooms array (structure changes are authoritative)
                if (Array.isArray(incoming.rooms)) {
                  floorData[floor].rooms = incoming.rooms;
                }

                // Ensure a students object exists locally
                floorData[floor].students = floorData[floor].students || {};

                // Merge incoming students per-room: only overwrite rooms provided by server
                if (incoming.students && typeof incoming.students === 'object') {
                  Object.keys(incoming.students).forEach(rn => {
                    floorData[floor].students[String(rn)] = incoming.students[rn] || [];
                  });
                }
              } else {
                // Floor not present locally — adopt incoming structure safely
                floorData[floor] = incoming;
                floorData[floor].students = floorData[floor].students || {};

                // Add floor button if it doesn't exist
                const existingBtn = document.querySelector(`[data-floor="${floor}"]`);
                if (!existingBtn && parseInt(floor) >= 2) {
                  addFloorButton(floor);
                }
              }
            });

            // Refresh current view if rooms are displayed
            if (!roomsContainer.classList.contains('hidden')) {
              const activeFloorBtn = document.querySelector('.floor-btn.active');
              if (activeFloorBtn) {
                activeFloorBtn.click();
              }
            }

            // Also refresh any room cards that are currently visible on the main dashboard
            refreshVisibleRoomCards();

            console.log('Dashboard synced with room management system');

            // Recompute assigned students snapshot after sync
            try { computeAndRenderTotalAssignedStudents(); } catch (e) { console.warn('computeAndRenderTotalAssignedStudents failed after sync', e); }

            // Show sync status in the UI (disabled)
            // showSyncStatus('Successfully synced with dashboard data');
          }
        } catch (error) {
          console.error('Error syncing with room management:', error);
        }
      }

      // Function to refresh visible room cards with updated student data
      function refreshVisibleRoomCards() {
        // Find all room boxes currently displayed
        const roomBoxes = document.querySelectorAll('.room-box[data-room-number]');
        console.log(`Found ${roomBoxes.length} room boxes to refresh`);

        roomBoxes.forEach(roomBox => {
          const roomNumber = roomBox.getAttribute('data-room-number');

          // Find which floor this room belongs to
          let roomFloor = null;
          Object.keys(floorData).forEach(floor => {
            if (floorData[floor].students[roomNumber] !== undefined) {
              roomFloor = floor;
            }
          });

          if (roomFloor) {
            // Get updated student data for this room
            const roomStudents = floorData[roomFloor].students[roomNumber] || [];
            const studentCount = roomStudents.length;
            console.log(`Updating room ${roomNumber} with ${studentCount} students:`, roomStudents);

            // Update the student occupants section
            const studentOccupantsDiv = roomBox.querySelector('.student-occupants');
            if (studentOccupantsDiv) {
              const studentsList = studentOccupantsDiv.querySelector('ul');
              if (studentsList) {
                // Update the count in the header
                const header = studentOccupantsDiv.querySelector('h4');
                if (header) {
                  // Determine stored/configured capacity for this room (fallback to 6)
                  const storedCapacity = parseInt(roomBox.getAttribute('data-capacity')) || 6;
                  const displayCapacity = Math.max(storedCapacity, studentCount);
                  const headerText = header.innerHTML;
                  // Replace existing count/capacity text with updated studentCount/displayCapacity
                  const updatedHeaderText = headerText.replace(/Student Occupants \([^)]*\)/, `Student Occupants (${studentCount}/${displayCapacity})`);
                  header.innerHTML = updatedHeaderText;
                }

                // Update the students list
                studentsList.innerHTML = '';
                if (roomStudents.length > 0) {
                  roomStudents.forEach(student => {
                    const li = document.createElement('li');
                    li.textContent = student;
                    studentsList.appendChild(li);
                  });
                } else {
                  const li = document.createElement('li');
                  li.textContent = 'No students assigned';
                  li.style.fontStyle = 'italic';
                  li.style.color = '#666';
                  studentsList.appendChild(li);
                }
              }
            }
          }
        });

        console.log('Refreshed visible room cards with updated student data');
      }

      function showSyncStatus(message, type = 'success') {
        // Create or update sync status indicator
        let syncIndicator = document.getElementById('syncIndicator');
        if (!syncIndicator) {
          syncIndicator = document.createElement('div');
          syncIndicator.id = 'syncIndicator';
          document.body.appendChild(syncIndicator);
        }

        // Determine styling based on message type
        let backgroundColor = '#4CAF50'; // Default green
        let displayTime = 3000; // Default 3 seconds
        let fontSize = '14px';
        let padding = '10px 15px';

        if (message.includes('reassigned') || message.includes('capacity')) {
          backgroundColor = '#2196F3'; // Blue for capacity changes
          displayTime = 5000; // Show longer for important updates
          fontSize = '15px';
          padding = '12px 18px';
        }

        syncIndicator.style.cssText = `
          position: fixed;
          top: 20px;
          right: 20px;
          background: ${backgroundColor};
          color: white;
          padding: ${padding};
          border-radius: 8px;
          font-size: ${fontSize};
          font-weight: 500;
          z-index: 1000;
          box-shadow: 0 4px 12px rgba(0,0,0,0.3);
          transition: all 0.3s ease;
          max-width: 400px;
          word-wrap: break-word;
        `;

        syncIndicator.textContent = message + ' - ' + new Date().toLocaleTimeString();
        syncIndicator.style.opacity = '1';
        syncIndicator.style.transform = 'translateY(0)';

        // Hide after specified time
        setTimeout(() => {
          syncIndicator.style.opacity = '0';
          syncIndicator.style.transform = 'translateY(-10px)';
        }, displayTime);
      }

      function addFloorButton(floor) {
        const floorButtons = document.getElementById('floorButtons');
        const newFloorBtn = document.createElement('button');
        newFloorBtn.className = 'floor-btn';
        newFloorBtn.setAttribute('data-floor', floor);
        newFloorBtn.textContent = `${floor}${getOrdinalSuffix(floor)} Floor`;

        // Add click event listener using shared helper
        newFloorBtn.addEventListener('click', function() {
          const floorNum = this.dataset.floor;
          showFloor(floorNum);
        });

        floorButtons.appendChild(newFloorBtn);
      }

      // Set up periodic sync with room management system
      setInterval(syncWithRoomManagement, 30000); // Sync every 30 seconds

      // Initial sync on page load
      setTimeout(syncWithRoomManagement, 2000); // Sync after 2 seconds

      // Listen for room assignment updates from room management page
      window.addEventListener('message', function(event) {
        if (event.origin !== window.location.origin) return;

        if (event.data.type === 'ROOM_ASSIGNMENTS_UPDATED') {
          console.log('Received room assignments update notification');
          // Trigger immediate sync
          syncWithRoomManagement();
        }
      });

      // Listen for localStorage changes (for cross-tab communication)
      window.addEventListener('storage', function(event) {
        if (event.key === 'roomAssignmentsUpdated' && event.newValue) {
          console.log('Room assignments updated in another tab, syncing...');
          // Trigger immediate sync
          syncWithRoomManagement();
        }
      });



      async function loadCapacityStats() {
        try {
          console.log('Loading capacity stats...');

          const response = await fetch('/api/room/statistics', {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
          });

          console.log('Response status:', response.status);

          if (!response.ok) {
            const errorText = await response.text();
            console.error('Response error:', errorText);
            throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
          }

          const data = await response.json();
          console.log('Response data:', data);

          if (data.success) {
            document.getElementById('roomCapacity').value = data.capacity;
            updateDetailedStatsDisplay(data);
            console.log('Statistics loaded successfully');
          } else {
            throw new Error(data.message || 'Failed to load statistics');
          }
        } catch (error) {
          console.error('Error loading capacity stats:', error);
          document.getElementById('statsContent').innerHTML = '<span style="color:#dc3545;display:flex;align-items:center;gap:8px;"><i class="fi fi-rr-exclamation"></i> Error loading statistics: ' + error.message + '</span>';
        }
      }

      function updateDetailedStatsDisplay(data) {
        const validation = data.validation;
        const studentCounts = data.student_counts;
        const capacity = data.capacity;

        const totalStudents = (studentCounts.M || 0) + (studentCounts.F || 0);
        const maleStudents = studentCounts.M || 0;
        const femaleStudents = studentCounts.F || 0;

        const estimatedRooms = Math.ceil(totalStudents / capacity);
        const validationIcon = validation.valid ? '<i class="fi fi-rr-check"></i>' : '<i class="fi fi-rr-exclamation"></i>';
        const validationColor = validation.valid ? '#28a745' : '#dc3545';

        document.getElementById('statsContent').innerHTML = `
          <div style="display:grid; gap:8px;">
            <div style="display:flex; align-items:center; gap:8px; color:#495057;">
              <i class="fi fi-rr-users"></i><strong>Demographics</strong>
              <span style="margin-left:auto; color:#6c757d;">Total: ${totalStudents} | M: ${maleStudents} | F: ${femaleStudents}</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px; color:#495057;">
              <i class="fi fi-rr-home"></i><strong>Assignment</strong>
              <span style="margin-left:auto; color:#6c757d;">Per Room: ${capacity} | Rooms: ${estimatedRooms} (M: ${validation.statistics.male_rooms || 0} • F: ${validation.statistics.female_rooms || 0})</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px; color:${validationColor};">
              ${validationIcon}
              <strong>Gender Separation</strong>
              <span style="margin-left:auto;">${validation.message}</span>
            </div>
          </div>
        `;
      }

      async function applyCapacitySettings() {
        const capacityInput = document.getElementById('roomCapacity');
        const capacity = parseInt(capacityInput.value, 10);

        if (isNaN(capacity) || capacity < 1 || capacity > 20) {
          showEnhancedAlert('warning', 'Please enter a valid capacity between 1 and 20 students.');
          return;
        }

        // Show lightweight loader for global reassignment (also keep button spinner)
        try { showLoader('Reassigning students...'); } catch(e) { console.warn('showLoader failed', e); }

        // Button loading state
        const submitBtn = document.querySelector('#capacityModal .submit-btn');
        const originalHTML = submitBtn ? submitBtn.innerHTML : null;
        if (submitBtn) {
          submitBtn.innerHTML = '<i class="fi fi-rr-spinner" style="animation: spin 1s linear infinite; margin-right:6px;"></i>Reassigning Students...';
          submitBtn.disabled = true;
        }

        try {
          const response = await fetch('/api/room/reassign-students', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Accept': 'application/json',
              'X-Requested-With': 'XMLHttpRequest',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ capacity: capacity })
          });

          // Always attempt to read the response body (text first to preserve raw server messages)
          const rawText = await response.text();
          let data = {};
          try {
            data = rawText ? JSON.parse(rawText) : {};
          } catch (e) {
            data = { success: false, __raw: rawText };
          }

          if (!response.ok) {
            // Log server response for debugging
            console.error('Server responded with error for applyCapacitySettings:', response.status, rawText);
            const serverMsg = data && data.message ? data.message : rawText || `HTTP ${response.status}`;
            try { hideLoader(); showResult('error', `Error reassigning students: ${serverMsg}`, 5000); } catch(e) { showEnhancedAlert('error', `Error reassigning students: ${serverMsg}`); }
            return;
          }

          if (data.success) {
            // Close modal safely
            const capacityModalEl = document.getElementById('capacityModal');
            if (typeof closeModal === 'function') closeModal(capacityModalEl);

            // Show success
            const msgParts = [];
            msgParts.push('Students successfully reassigned!');
            if (data.execution_time_ms) msgParts.push(`Completed in ${data.execution_time_ms}ms`);
            msgParts.push(`New capacity: ${capacity} students per room`);
            try { hideLoader(); showResult('success', msgParts.join(' • '), 5000); } catch(e) { showEnhancedAlert('success', msgParts.join(' • '), 7000); }

            // Immediately update dashboard with returned data if provided
            if (data.roomStudents) {
              console.log('Updating dashboard with fresh room assignment data...');
              console.log('Room students data:', data.roomStudents);
              updateDashboardWithNewAssignments(data.roomStudents);

              // Trigger server-side task sync (ensures task/checklist cards reflect new assignments)
              try {
                (async () => {
                  console.log('Requesting server to sync tasks with new assignments...');
                  const syncResp = await fetch('/api/room/sync-tasks', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json',
                      'X-Requested-With': 'XMLHttpRequest',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ triggered_by: 'capacity_change', capacity: capacity })
                  });

                  if (!syncResp.ok) {
                    const txt = await syncResp.text().catch(() => '');
                    console.warn('Task sync returned non-OK:', syncResp.status, txt);
                  } else {
                    const syncData = await syncResp.json().catch(() => ({}));
                    console.log('Task sync completed:', syncData);
                  }

                  // After task sync, refresh dashboard data so task cards update
                  try {
                    await syncWithRoomManagement();
                  } catch (e) {
                    console.warn('Error during post-sync dashboard refresh:', e);
                  }

                  // Broadcast to other tabs to update their UI
                  try {
                    localStorage.setItem('roomAssignmentsUpdated', JSON.stringify({ ts: Date.now(), capacity }));
                  } catch (e) {
                    console.warn('Unable to broadcast roomAssignmentsUpdated via localStorage', e);
                  }
                })();
              } catch (err) {
                console.error('Error initiating task sync:', err);
              }
            } else {
              console.log('No room students data returned, triggering sync instead');
              // Ensure tasks and dashboard are synchronized even if no detailed assignment returned
              (async () => {
                try {
                  await fetch('/api/room/sync-tasks', {
                    method: 'POST',
                    headers: {
                      'Content-Type': 'application/json',
                      'Accept': 'application/json',
                      'X-Requested-With': 'XMLHttpRequest',
                      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ triggered_by: 'capacity_change', capacity: capacity })
                  }).catch(e => console.warn('Task sync failed:', e));
                } catch (e) {
                  console.warn('Error during fallback task sync:', e);
                }

                try {
                  await syncWithRoomManagement();
                } catch (e) {
                  console.warn('Error refreshing dashboard after fallback sync:', e);
                }

                try {
                  localStorage.setItem('roomAssignmentsUpdated', JSON.stringify({ ts: Date.now(), capacity }));
                } catch (e) {
                  console.warn('Unable to broadcast roomAssignmentsUpdated via localStorage', e);
                }
              })();
            }
          } else {
            const message = data.message || data.__raw || 'Unknown error occurred';
            try { hideLoader(); showResult('error', `Error: ${message}`, 5000); } catch(e) { showEnhancedAlert('error', `Error: ${message}`); }
          }
        } catch (error) {
          console.error('Error reassigning students (network or unexpected):', error);
          try { hideLoader(); showResult('error', 'Error reassigning students. Please try again. ' + (error.message || ''), 5000); } catch(e) { showEnhancedAlert('error', 'Error reassigning students. Please try again. ' + (error.message || '')); }
        } finally {
          try { hideLoader(); } catch(e) {}
          // Reset button
          const submitBtnFinal = document.querySelector('#capacityModal .submit-btn');
          if (submitBtnFinal) {
            submitBtnFinal.innerHTML = originalHTML || '<i class="fi fi-rr-settings" style="margin-right:6px;"></i>Set Capacity for All Rooms';
            submitBtnFinal.disabled = false;
          }
        }
      }

      // Function to immediately update dashboard with new room assignments
      function updateDashboardWithNewAssignments(roomStudents) {
        console.log('Updating dashboard with new room assignments (delegating to merge-update):', roomStudents);

        // Delegate to the unified updater which merges per-room changes, updates floorData,
        // refreshes visible cards, and broadcasts cross-tab updates.
        try {
          updateDashboardWithRoomData(roomStudents);
        } catch (e) {
          console.warn('updateDashboardWithRoomData failed, falling back to direct updateFloorDataWithNewAssignments', e);
          updateFloorDataWithNewAssignments(roomStudents);
          refreshVisibleRoomCards();
        }

        // Force refresh the main dashboard view if no specific floor is selected
        if (roomsContainer.classList.contains('hidden')) {
          console.log('Refreshing main dashboard view');
          // Trigger a refresh of the main dashboard cards
          const dashboardCards = document.querySelectorAll('.room-box[data-room-number]');
          dashboardCards.forEach(card => {
            const roomNumber = card.getAttribute('data-room-number');
            if (roomStudents[roomNumber]) {
              updateRoomCardStudents(card, roomStudents[roomNumber]);
            }
          });
        }

        // If a floor is currently displayed, refresh it immediately
        if (!roomsContainer.classList.contains('hidden')) {
          const activeFloorBtn = document.querySelector('.floor-btn.active');
          if (activeFloorBtn) {
            const floor = activeFloorBtn.dataset.floor;
            console.log(`Refreshing currently displayed floor ${floor}`);

            // Clear and rebuild the rooms display
            const roomsWrapper = roomsContainer.querySelector('.rooms-wrapper');
            if (roomsWrapper) {
              roomsWrapper.innerHTML = '';

              // Rebuild room boxes with updated data
              floorData[floor].rooms.forEach(roomNumber => {
                const roomBox = createRoomBox(roomNumber, floor);
                roomsWrapper.appendChild(roomBox);
              });

              // Re-setup event listeners for the new room boxes
              setupRoomEventListeners();
            }
          }
        }

        // Show visual feedback that the update was successful (disabled)
        // showSyncStatus('Dashboard updated with new room assignments');

        console.log('Dashboard successfully updated with new room assignments');
      }

      // Enhanced function to update room card students display with capacity
      function updateRoomCardStudents(roomCard, students, capacity = null) {
        const occupantsList = roomCard.querySelector('.occupants-list');
        const roomNumber = roomCard.getAttribute('data-room-number');

        if (occupantsList && Array.isArray(students)) {
          // Clear existing students
          occupantsList.innerHTML = '';

          // Add new students
          students.forEach(student => {
            const li = document.createElement('li');
            li.textContent = student;
            occupantsList.appendChild(li);
          });

          // Update occupancy count with dynamic capacity (display expands if students exceed configured capacity)
          const occupancySpan = roomCard.querySelector('.occupancy');
          if (occupancySpan) {
            const configuredCapacity = capacity || parseInt(roomCard.getAttribute('data-capacity')) || 6;
            const displayCapacity = Math.max(configuredCapacity, students.length);
            occupancySpan.textContent = `${students.length}/${displayCapacity}`;
          }

          // Update capacity display elements if they exist
          const capacityElements = roomCard.querySelectorAll('.capacity-display');
          capacityElements.forEach(element => {
            if (capacity) {
              element.textContent = capacity;
            }
          });

          console.log(`Updated room card for room ${roomNumber} with ${students.length} students (capacity: ${capacity || 'default'})`);
        }
      }

      // Helper function to get current room data
      function getCurrentRoomData(roomNumber) {
        // Try to get from floor data if available
        if (typeof floorData !== 'undefined' && floorData) {
          for (const floor in floorData) {
            if (floorData[floor].students && floorData[floor].students[roomNumber]) {
              return {
                students: floorData[floor].students[roomNumber],
                capacity: 6 // Default capacity
              };
            }
          }
        }
        return null;
      }

      // Individual Room Capacity Modal Functions
      async function openIndividualCapacityModal(roomNumber) {
        const modal = document.getElementById('individualCapacityModal');
        const roomNumberSpan = document.getElementById('individualRoomNumber');
        const targetRoomInput = document.getElementById('targetRoomNumber');
        const studentsListDiv = document.getElementById('currentStudentsList');
        const impactDiv = document.getElementById('capacityImpact');

        // Set room number
        roomNumberSpan.textContent = `Room ${roomNumber}`;
        targetRoomInput.value = roomNumber;

        // Show loading state
        studentsListDiv.innerHTML = `
          <div style="text-align: center; padding: 20px; color: #6c757d;">
            <i class="fi fi-rr-spinner" style="animation: spin 1s linear infinite; font-size: 1.5rem; margin-bottom: 10px; display: block;"></i>
            Loading current students...
          </div>`;

        try {
          // Get room details from API (includes persistent capacity)
          const response = await fetch(`/api/room/details/${roomNumber}`, {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          });

          if (response.ok) {
            const data = await response.json();
            if (data.success && data.room) {
              populateRoomForm(data.room);
            } else {
              console.error('Failed to load room details:', data.message);
              populateRoomFormFallback(roomNumber);
            }
          } else {
            throw new Error(`HTTP error! status: ${response.status}`);
          }
        } catch (error) {
          console.error('Error loading room details:', error);
          populateRoomFormFallback(roomNumber);
        }

        // Hide impact analysis initially
        impactDiv.style.display = 'none';

        // Add comprehensive event listeners for capacity changes
        setupCapacityEventListeners(roomNumber);

        // Initialize auto-assignment features
        initializeAutoAssignmentFeatures();

        modal.style.display = 'block';
      }

      // Populate form with room data from API
      function populateRoomForm(room) {
        // Basic capacity fields
        document.getElementById('individualRoomCapacity').value = room.capacity || 6;
        document.getElementById('individualMaleCapacity').value = room.male_capacity || '';
        document.getElementById('individualFemaleCapacity').value = room.female_capacity || '';

        // Persist occupant type for downstream bulk-add filtering
        const occupantTypeField = document.getElementById('individualRoomOccupantType');
        if (occupantTypeField) {
          occupantTypeField.value = room.occupant_type || 'both';
        }

        // Batch-specific capacity fields
        document.getElementById('male2025Capacity').value = room.male_capacity_2025 || '';
        document.getElementById('female2025Capacity').value = room.female_capacity_2025 || '';
        document.getElementById('male2026Capacity').value = room.male_capacity_2026 || '';
        document.getElementById('female2026Capacity').value = room.female_capacity_2026 || '';

        // Assigned batch
        document.getElementById('individualAssignedBatch').value = room.assigned_batch || '';

        // Display current students
        displayCurrentStudents(room.students || []);
      }

      // Fallback method for populating form
      function populateRoomFormFallback(roomNumber) {
        const currentRoomData = getCurrentRoomData(roomNumber);
        const occupantTypeField = document.getElementById('individualRoomOccupantType');
        if (occupantTypeField) {
          occupantTypeField.value = 'both';
        }
        if (currentRoomData) {
          document.getElementById('individualRoomCapacity').value = currentRoomData.capacity || 6;

          // Display students without detailed info
          const studentsSimple = currentRoomData.students.map(name => ({ name, gender: null, batch: null }));
          displayCurrentStudents(studentsSimple);
        }
      }

      // Display current students with enhanced edit capabilities
      function displayCurrentStudents(students) {
        const studentsListDiv = document.getElementById('currentStudentsList');
        const roomNumber = document.getElementById('targetRoomNumber').value;

        if (students.length === 0) {
          studentsListDiv.innerHTML = `
            <div style="text-align: center; padding: 20px; color: #666; font-style: italic; border: 2px dashed #ddd; border-radius: 8px;">
              <i class="fi fi-rr-users" style="font-size: 2rem; margin-bottom: 10px; display: block; color: #ccc;"></i>
              <strong>No students assigned to Room ${roomNumber}</strong>
              <p style="margin: 8px 0 0 0; font-size: 0.9rem;">Click "Add Student" to assign students to this room</p>
            </div>`;
          return;
        }

        // Add header with room info and student count
        let studentsHtml = `
          <div style="background: #e3f2fd; padding: 12px; border-radius: 6px; margin-bottom: 15px; border-left: 4px solid #2196f3;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <div>
                <strong style="color: #1565c0; font-size: 1.1rem;">
                  <i class="fi fi-rr-home" style="margin-right: 6px;"></i>
                  Room ${roomNumber} - Current Students
                </strong>
                <p style="margin: 4px 0 0 0; color: #1976d2; font-size: 0.9rem;">
                  ${students.length} student${students.length !== 1 ? 's' : ''} currently assigned
                </p>
              </div>
              <div style="background: white; padding: 6px 12px; border-radius: 20px; border: 2px solid #2196f3;">
                <strong style="color: #1565c0; font-size: 1.1rem;">${students.length}</strong>
              </div>
            </div>
          </div>
          <div class="students-list" style="max-height: 300px; overflow-y: auto;">`;

        students.forEach((student, index) => {
          const genderBadge = student.gender ?
            `<span class="badge ${student.gender === 'M' ? 'badge-blue' : 'badge-pink'}" style="font-size: 0.75rem; padding: 2px 6px; border-radius: 12px; margin-left: 8px;">
              <i class="fi fi-rr-${student.gender === 'M' ? 'mars' : 'venus'}" style="margin-right: 2px;"></i>
              ${student.gender === 'M' ? 'Male' : 'Female'}
            </span>` : '';
          const batchBadge = student.batch ?
            `<span class="badge badge-secondary" style="font-size: 0.75rem; padding: 2px 6px; border-radius: 12px; margin-left: 4px;">
              <i class="fi fi-rr-graduation-cap" style="margin-right: 2px;"></i>
              Batch ${student.batch}
            </span>` : '';

          studentsHtml += `
            <div class="student-item" style="display: flex; justify-content: space-between; align-items: center; padding: 15px; border: 2px solid #e3f2fd; border-radius: 10px; margin-bottom: 10px; background: white; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
              <div style="flex: 1;">
                <div style="display: flex; align-items: center; margin-bottom: 4px;">
                  <div style="background: #2196f3; color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-right: 12px; font-weight: bold;">
                    ${index + 1}
                  </div>
                  <div>
                    <strong style="color: #1565c0; font-size: 1.1rem; display: block;">${student.name}</strong>
                    <div style="display: flex; align-items: center; gap: 6px; margin-top: 2px;">
                      ${genderBadge}
                      ${batchBadge}
                    </div>
                  </div>
                </div>
              </div>
              <div style="display: flex; gap: 8px;">
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="openEditStudentModal('${student.name}', ${index})"
                        style="padding: 8px 12px; border-radius: 6px; display: flex; align-items: center; gap: 4px; font-weight: 500;"
                        title="Edit student assignment">
                  <i class="fi fi-rr-edit" style="font-size: 0.9rem;"></i>
                  <span style="font-size: 0.85rem;">Edit</span>
                </button>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmRemoveStudent('${student.name}', ${index})"
                        style="padding: 8px 12px; border-radius: 6px; display: flex; align-items: center; gap: 4px; font-weight: 500;"
                        title="Remove student from room">
                  <i class="fi fi-rr-trash" style="font-size: 0.9rem;"></i>
                  <span style="font-size: 0.85rem;">Remove</span>
                </button>
              </div>
            </div>
          `;
        });
        studentsHtml += '</div>';

        studentsListDiv.innerHTML = studentsHtml;
      }

      // Setup comprehensive event listeners for all capacity and assignment changes
      function setupCapacityEventListeners(roomNumber) {
        const capacityInput = document.getElementById('individualRoomCapacity');
        const maleCapacityInput = document.getElementById('individualMaleCapacity');
        const femaleCapacityInput = document.getElementById('individualFemaleCapacity');
        const male2025Input = document.getElementById('male2025Capacity');
        const female2025Input = document.getElementById('female2025Capacity');
        const male2026Input = document.getElementById('male2026Capacity');
        const female2026Input = document.getElementById('female2026Capacity');
        const assignedBatchSelect = document.getElementById('individualAssignedBatch');

        // Remove existing listeners
        [capacityInput, maleCapacityInput, femaleCapacityInput, male2025Input, female2025Input, male2026Input, female2026Input, assignedBatchSelect].forEach(input => {
          if (input && input.changeHandler) {
            input.removeEventListener('input', input.changeHandler);
            input.removeEventListener('change', input.changeHandler);
          }
        });

        // Dynamic capacity validation and auto-adjustment
        function validateAndAdjustCapacities() {
          const totalCapacity = parseInt(capacityInput.value) || 0;
          const maleCapacity = parseInt(maleCapacityInput.value) || 0;
          const femaleCapacity = parseInt(femaleCapacityInput.value) || 0;

          // Auto-adjust gender capacities if they exceed total
          if (maleCapacity + femaleCapacity > totalCapacity) {
            if (maleCapacity > 0 && femaleCapacity > 0) {
              // Proportionally adjust both
              const ratio = totalCapacity / (maleCapacity + femaleCapacity);
              maleCapacityInput.value = Math.floor(maleCapacity * ratio);
              femaleCapacityInput.value = totalCapacity - Math.floor(maleCapacity * ratio);
            }
          }

          // Update batch-specific capacities validation
          validateBatchCapacities();

          // Analyze impact
          analyzeCapacityImpact(roomNumber, totalCapacity);
        }

        function validateBatchCapacities() {
          const totalCapacity = parseInt(capacityInput.value) || 0;
          const male2025 = parseInt(male2025Input.value) || 0;
          const female2025 = parseInt(female2025Input.value) || 0;
          const male2026 = parseInt(male2026Input.value) || 0;
          const female2026 = parseInt(female2026Input.value) || 0;

          const totalBatchCapacity = male2025 + female2025 + male2026 + female2026;

          if (totalBatchCapacity > totalCapacity) {
            // Show warning but don't auto-adjust batch capacities
            showCapacityWarning(`Batch-specific capacities (${totalBatchCapacity}) exceed total capacity (${totalCapacity})`);
          } else {
            hideCapacityWarning();
          }
        }

        // Add event listeners with dynamic validation
        capacityInput.changeHandler = validateAndAdjustCapacities;
        maleCapacityInput.changeHandler = validateAndAdjustCapacities;
        femaleCapacityInput.changeHandler = validateAndAdjustCapacities;
        male2025Input.changeHandler = validateBatchCapacities;
        female2025Input.changeHandler = validateBatchCapacities;
        male2026Input.changeHandler = validateBatchCapacities;
        female2026Input.changeHandler = validateBatchCapacities;

        // Batch assignment change handler
        if (assignedBatchSelect) {
          assignedBatchSelect.changeHandler = function() {
            handleBatchAssignmentChange(roomNumber, this.value);
          };
          assignedBatchSelect.addEventListener('change', assignedBatchSelect.changeHandler);
        }

        // Attach all listeners
        capacityInput.addEventListener('input', capacityInput.changeHandler);
        maleCapacityInput.addEventListener('input', maleCapacityInput.changeHandler);
        femaleCapacityInput.addEventListener('input', femaleCapacityInput.changeHandler);
        male2025Input.addEventListener('input', male2025Input.changeHandler);
        female2025Input.addEventListener('input', female2025Input.changeHandler);
        male2026Input.addEventListener('input', male2026Input.changeHandler);
        female2026Input.addEventListener('input', female2026Input.changeHandler);

        // Setup auto-assignment checkbox listeners
        setupAutoAssignmentListeners(roomNumber);
      }

      // Setup auto-assignment checkbox event listeners
      function setupAutoAssignmentListeners(roomNumber) {
        const autoAssignCheckboxes = [
          'autoAssignMale', 'autoAssignFemale',
          'autoAssignMale2025', 'autoAssignFemale2025',
          'autoAssignMale2026', 'autoAssignFemale2026'
        ];

        autoAssignCheckboxes.forEach(checkboxId => {
          const checkbox = document.getElementById(checkboxId);
          if (checkbox) {
            checkbox.addEventListener('change', function() {
              handleAutoAssignmentChange(checkboxId, this.checked, roomNumber);
            });
          }
        });

        // Setup main auto-assignment toggle
        const enableAutoAssignment = document.getElementById('enableAutoAssignment');
        if (enableAutoAssignment) {
          enableAutoAssignment.addEventListener('change', function() {
            toggleAutoAssignmentFeatures(this.checked);
          });
        }
      }

      // Enhanced gender-based assignment logic
      function handleAutoAssignmentChange(checkboxId, isChecked, roomNumber) {
        const enableAutoAssignment = document.getElementById('enableAutoAssignment').checked;

        if (isChecked && !enableAutoAssignment) {
          // Auto-enable the main toggle if individual checkbox is checked
          document.getElementById('enableAutoAssignment').checked = true;
          toggleAutoAssignmentFeatures(true);
        }

        // Enhanced feedback with gender-based assignment validation
        if (isChecked) {
          const capacityType = checkboxId.replace('autoAssign', '').toLowerCase();

          // Check for gender conflicts in current assignments
          validateGenderCompatibility(roomNumber, capacityType);

          showAutoAssignmentFeedback(`Auto-assignment enabled for ${capacityType} students from  PNPh Management System`);
        }
      }

      // Validate gender compatibility for room assignments
      async function validateGenderCompatibility(roomNumber, capacityType) {
        try {
          // Get current room data to check existing student genders
          const currentRoomData = getCurrentRoomData(roomNumber);

          if (currentRoomData && currentRoomData.students && currentRoomData.students.length > 0) {
            const existingGenders = [...new Set(currentRoomData.students.map(s => s.gender).filter(g => g))];

            // Determine the gender being assigned
            let targetGender = null;
            if (capacityType.includes('male')) {
              targetGender = 'M';
            } else if (capacityType.includes('female')) {
              targetGender = 'F';
            }

            // Check for gender conflicts
            if (targetGender && existingGenders.length > 0 && !existingGenders.includes(targetGender)) {
              const existingGenderText = existingGenders.includes('M') ? 'male' : 'female';
              const targetGenderText = targetGender === 'M' ? 'male' : 'female';

              showCapacityWarning(
                `<i class="fi fi-rr-exclamation-triangle" style="margin-right:6px;color:#ffc107;"></i> Gender Conflict: This room currently has ${existingGenderText} students. ` +
                `Adding ${targetGenderText} students would violate the gender-based room policy. ` +
                `Consider reassigning existing students or choosing a different room.`
              );

              return false;
            }
          }

          return true;
        } catch (error) {
          console.error('Error validating gender compatibility:', error);
          return true; // Allow assignment if validation fails
        }
      }

      // Enhanced auto-assignment with gender-based logic
      async function performGenderBasedAutoAssignment(roomNumber, assignmentConfig) {
        try {
          showAutoAssignmentFeedback('<i class="fi fi-rr-sync" style="margin-right:6px;color:#17a2b8;"></i> Performing gender-based auto-assignment from  PNPh Management System...');

          const response = await fetch('/api/room/auto-assign-students', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
              'Accept': 'application/json'
            },
            body: JSON.stringify({
              room_number: roomNumber,
              assignment_config: assignmentConfig,
              use_login_database: true,
              enforce_gender_policy: true,
              data_source: {
                users_table: 'pnph_users',
                details_table: 'student_details',
                gender_field: 'gender',
                batch_field: 'batch',
                name_fields: ['user_fname', 'user_lname'],
                role_filter: 'student'
              }
            })
          });

          const data = await response.json();

          if (data.success) {
            showAutoAssignmentFeedback(`<i class="fi fi-rr-check" style="margin-right:6px;color:#28a745;"></i> ${data.message || 'Students assigned successfully with gender-based policy'}`);

            // Update the dashboard with new assignments
            if (data.updated_room_data) {
              updateDashboardWithRoomData(data.updated_room_data);
            }

            // Refresh the modal to show updated student list
            setTimeout(() => {
              openIndividualCapacityModal(roomNumber);
            }, 1000);

            return true;
          } else {
            showAutoAssignmentFeedback(`<i class="fi fi-rr-cross" style="margin-right:6px;color:#dc3545;"></i> ${data.message || 'Failed to assign students'}`);
            return false;
          }
        } catch (error) {
          console.error('Error performing auto-assignment:', error);
          showAutoAssignmentFeedback('<i class="fi fi-rr-cross" style="margin-right:6px;color:#dc3545;"></i> Network error during auto-assignment. Please try again.');
          return false;
        }
      }

      // Enhanced confirmation dialog with gender-based messaging
      function showConfirmationDialog(title, message, confirmText = 'Confirm', cancelText = 'Cancel') {
        return new Promise((resolve) => {
          const modalHtml = `
            <div id="genderConfirmationModal" class="modal" style="display: block; z-index: 10002;">
              <div class="modal-content" style="max-width: 500px; width: 90%;">
                <div class="modal-header" style="border-bottom: 1px solid #dee2e6; padding-bottom: 15px; margin-bottom: 20px;">
                  <h4 style="margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fi fi-rr-exclamation-triangle" style="color: #ffc107;"></i>
                    ${title}
                  </h4>
                </div>
                <div class="modal-body">
                  <p style="margin: 0; color: #666; line-height: 1.5;">${message}</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #dee2e6; padding-top: 15px; margin-top: 20px;">
                  <button type="button" class="btn btn-secondary" onclick="resolveGenderConfirmation(false)">${cancelText}</button>
                  <button type="button" class="btn btn-warning" onclick="resolveGenderConfirmation(true)">${confirmText}</button>
                </div>
              </div>
            </div>
          `;

          document.body.insertAdjacentHTML('beforeend', modalHtml);

          window.resolveGenderConfirmation = function(result) {
            const modal = document.getElementById('genderConfirmationModal');
            if (modal) {
              modal.remove();
            }
            delete window.resolveGenderConfirmation;
            resolve(result);
          };
        });
      }

      // Enhanced alert system with gender-based styling
      function showAlert(type, message, duration = 5000) {
        const alertId = 'genderAlert_' + Date.now();
        const alertColors = {
          success: { bg: '#d4edda', border: '#c3e6cb', text: '#155724', icon: 'fi-rr-check' },
          error: { bg: '#f8d7da', border: '#f5c6cb', text: '#721c24', icon: 'fi-rr-cross' },
          warning: { bg: '#fff3cd', border: '#ffeaa7', text: '#856404', icon: 'fi-rr-exclamation' },
          info: { bg: '#d1ecf1', border: '#bee5eb', text: '#0c5460', icon: 'fi-rr-info' }
        };

        const colors = alertColors[type] || alertColors.info;

        const alertHtml = `
          <div id="${alertId}" class="alert alert-${type}" style="
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10003;
            max-width: 400px;
            background: ${colors.bg};
            border: 1px solid ${colors.border};
            border-left: 4px solid ${colors.border};
            color: ${colors.text};
            padding: 15px 20px;
            border-radius: 6px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideInRight 0.3s ease;
            font-size: 0.9rem;
            line-height: 1.4;
          ">
            <div style="display: flex; align-items: flex-start; gap: 10px;">
              <i class="fi ${colors.icon}" style="font-size: 1.1rem; margin-top: 2px;"></i>
              <div style="flex: 1;">
                <strong style="display: block; margin-bottom: 4px;">
                  ${type.charAt(0).toUpperCase() + type.slice(1)}
                </strong>
                ${message}
              </div>
              <button onclick="document.getElementById('${alertId}').remove()" style="
                background: none;
                border: none;
                color: ${colors.text};
                cursor: pointer;
                font-size: 1.2rem;
                padding: 0;
                margin-left: 10px;
              ">&times;</button>
            </div>
          </div>
        `;

        document.body.insertAdjacentHTML('beforeend', alertHtml);

        // Auto-remove after duration
        setTimeout(() => {
          const alert = document.getElementById(alertId);
          if (alert) {
            alert.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => alert.remove(), 300);
          }
        }, duration);
      }

      // Toggle auto-assignment features
      function toggleAutoAssignmentFeatures(enabled) {
        const autoCheckboxes = document.querySelectorAll('[id^="autoAssign"]:not(#enableAutoAssignment)');
        autoCheckboxes.forEach(checkbox => {
          checkbox.disabled = !enabled;
          if (!enabled) {
            checkbox.checked = false;
          }
        });

        showAutoAssignmentFeedback(enabled ?
          'Auto-assignment features enabled - students will be sourced from  PNPh Management System (pnph_users + student_details)' :
          'Auto-assignment features disabled'
        );
      }

      // Show auto-assignment feedback
      function showAutoAssignmentFeedback(message) {
        // Create or update feedback div
        let feedbackDiv = document.getElementById('autoAssignmentFeedback');
        if (!feedbackDiv) {
          feedbackDiv = document.createElement('div');
          feedbackDiv.id = 'autoAssignmentFeedback';
          feedbackDiv.style.cssText = `
            background: #d4edda; color: #155724; padding: 8px 12px; border-radius: 4px;
            margin: 10px 0; font-size: 0.9rem; border-left: 3px solid #28a745;
            display: none;
          `;

          const syncOptions = document.querySelector('.sync-options');
          if (syncOptions) {
            syncOptions.appendChild(feedbackDiv);
          }
        }

        feedbackDiv.textContent = message;
        feedbackDiv.style.display = 'block';

        // Auto-hide after 3 seconds
        setTimeout(() => {
          feedbackDiv.style.display = 'none';
        }, 3000);
      }

      // Initialize auto-assignment features when modal opens
      function initializeAutoAssignmentFeatures() {
        // Set default states
        const enableAutoAssignment = document.getElementById('enableAutoAssignment');

        if (enableAutoAssignment) {
          enableAutoAssignment.checked = true;
        }

        // Initialize auto-assignment checkboxes as enabled
        toggleAutoAssignmentFeatures(true);

        // Show initial guidance
        showAutoAssignmentFeedback('Auto-assignment enabled. Students will be sourced from  PNPh Management System (pnph_users + student_details).');
      }









      function closeIndividualCapacityModal() {
        const modal = document.getElementById('individualCapacityModal');
        const impactDiv = document.getElementById('capacityImpact');
        modal.style.display = 'none';
        impactDiv.style.display = 'none';
      }

      function getCurrentRoomData(roomNumber) {
        // Find the room data from the current floor data
        for (const floor in floorData) {
          if (floorData[floor].students[roomNumber]) {
            const students = floorData[floor].students[roomNumber] || [];
            return {
              occupancy: students.length,
              capacity: 6, // Default capacity, will be updated from backend
              students: students
            };
          }
        }
        return { occupancy: 0, capacity: 6, students: [] };
      }

      function analyzeCapacityImpact(roomNumber, newCapacity) {
        const currentData = getCurrentRoomData(roomNumber);
        const impactDiv = document.getElementById('capacityImpact');
        const impactContent = document.getElementById('impactContent');

        if (newCapacity === currentData.capacity) {
          impactDiv.style.display = 'none';
          return;
        }

        let impactMessage = '';
        let impactColor = '#ffc107'; // Warning yellow

        if (newCapacity > currentData.capacity) {
          // Increasing capacity
          const additionalSlots = newCapacity - currentData.capacity;
          impactMessage = `
            <div style="color: #28a745;">
              <i class="fi fi-rr-check" style="margin-right:6px;color:#28a745;"></i> Capacity will increase by ${additionalSlots} slot(s)<br>
              <i class="fi fi-rr-check" style="margin-right:6px;color:#28a745;"></i> Room can accommodate ${additionalSlots} more student(s)<br>
              <i class="fi fi-rr-check" style="margin-right:6px;color:#28a745;"></i> No students will be displaced
            </div>
          `;
          impactColor = '#d4edda';
        } else if (newCapacity >= currentData.occupancy) {
          // Decreasing capacity but still fits current students
          const reducedSlots = currentData.capacity - newCapacity;
          impactMessage = `
            <div style="color: #856404;">
              <i class="fi fi-rr-exclamation-triangle" style="margin-right:6px;color:#856404;"></i> Capacity will decrease by ${reducedSlots} slot(s)<br>
              <i class="fi fi-rr-check" style="margin-right:6px;color:#856404;"></i> Current students (${currentData.occupancy}) will remain in room<br>
              <i class="fi fi-rr-exclamation-triangle" style="margin-right:6px;color:#856404;"></i> ${newCapacity - currentData.occupancy} available slot(s) after change
            </div>
          `;
        } else {
          // Decreasing capacity below current occupancy
          const studentsToRelocate = currentData.occupancy - newCapacity;
          impactMessage = `
            <div style="color: #721c24;">
              <i class="fi fi-rr-exclamation-triangle" style="margin-right:6px;color:#721c24;"></i> Capacity will decrease below current occupancy<br>
              <i class="fi fi-rr-exclamation-triangle" style="margin-right:6px;color:#721c24;"></i> ${studentsToRelocate} student(s) will need to be relocated<br>
              <i class="fi fi-rr-exclamation-triangle" style="margin-right:6px;color:#721c24;"></i> Students will be automatically reassigned to other rooms
            </div>
          `;
          impactColor = '#f8d7da';
        }

        impactContent.innerHTML = impactMessage;
        impactDiv.style.backgroundColor = impactColor;
        impactDiv.style.display = 'block';
      }

      // Show capacity warning
      function showCapacityWarning(message) {
        let warningDiv = document.getElementById('capacityWarning');
        if (!warningDiv) {
          warningDiv = document.createElement('div');
          warningDiv.id = 'capacityWarning';
          warningDiv.style.cssText = 'background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0; border-left: 4px solid #dc3545;';
          document.getElementById('individualCapacityForm').appendChild(warningDiv);
        }
        warningDiv.innerHTML = `<strong><i class="fi fi-rr-exclamation-triangle"></i> Warning:</strong> ${message}`;
        warningDiv.style.display = 'block';
      }

      // Hide capacity warning
      function hideCapacityWarning() {
        const warningDiv = document.getElementById('capacityWarning');
        if (warningDiv) {
          warningDiv.style.display = 'none';
        }
      }

      // Handle batch assignment changes with automatic student reassignment
      function handleBatchAssignmentChange(roomNumber, newBatch) {
        console.log(`Batch assignment changed for room ${roomNumber} to ${newBatch}`);

        // Get current students in the room
        const currentRoomData = getCurrentRoomData(roomNumber);
        if (!currentRoomData || !currentRoomData.students) return;

        // Check if any current students don't match the new batch
        if (currentRoomData.students.length > 0 && newBatch !== '') {
          showCapacityWarning(`Changing batch assignment may require reassigning students who don't match the new batch requirement.`);
        } else {
          hideCapacityWarning();
        }
      }

      async function applyIndividualCapacity() {
        const roomNumber = document.getElementById('targetRoomNumber').value;
        const newCapacity = parseInt(document.getElementById('individualRoomCapacity').value);
        const submitBtn = document.querySelector('#individualCapacityModal .submit-btn');
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        if (!roomNumber || !newCapacity || newCapacity < 1 || newCapacity > 20) {
          alert('Please enter a valid capacity (1-20)');
          return;
        }

        // Collect comprehensive form data for dynamic updates
        const formData = {
          room_number: roomNumber,
          capacity: newCapacity
        };

        // Add gender capacity controls
        const maleCapacityValue = document.getElementById('individualMaleCapacity').value.trim();
        if (maleCapacityValue !== '') {
          formData.male_capacity = parseInt(maleCapacityValue);
        }

        const femaleCapacityValue = document.getElementById('individualFemaleCapacity').value.trim();
        if (femaleCapacityValue !== '') {
          formData.female_capacity = parseInt(femaleCapacityValue);
        }

        // Add batch-specific capacity controls
        const male2025Value = document.getElementById('male2025Capacity').value.trim();
        if (male2025Value !== '') {
          formData.male_capacity_2025 = parseInt(male2025Value);
        }

        const female2025Value = document.getElementById('female2025Capacity').value.trim();
        if (female2025Value !== '') {
          formData.female_capacity_2025 = parseInt(female2025Value);
        }

        const male2026Value = document.getElementById('male2026Capacity').value.trim();
        if (male2026Value !== '') {
          formData.male_capacity_2026 = parseInt(male2026Value);
        }

        const female2026Value = document.getElementById('female2026Capacity').value.trim();
        if (female2026Value !== '') {
          formData.female_capacity_2026 = parseInt(female2026Value);
        }

        // Add assigned batch
        const assignedBatchValue = document.getElementById('individualAssignedBatch').value;
        if (assignedBatchValue !== '') {
          formData.assigned_batch = assignedBatchValue;
        }

        // Add auto-update flag for other rooms
        formData.auto_update_other_rooms = true;

        // Add cross-room synchronization settings (UI toggle may be removed)
        const enableCrossRoomSyncEl = document.getElementById('enableCrossRoomSync');
        // Default to true when control is absent so capacity increases can borrow from other rooms
        const enableCrossRoomSync = enableCrossRoomSyncEl ? enableCrossRoomSyncEl.checked : true;
        const enableAutoAssignmentEl = document.getElementById('enableAutoAssignment');
        const enableAutoAssignment = enableAutoAssignmentEl ? enableAutoAssignmentEl.checked : true;
        formData.enable_cross_room_sync = enableCrossRoomSync;
        formData.enable_auto_assignment = enableAutoAssignment;

        // Add auto-assignment flags for each capacity type
        const autoAssignMale = document.getElementById('autoAssignMale').checked;
        const autoAssignFemale = document.getElementById('autoAssignFemale').checked;
        const autoAssignMale2025 = document.getElementById('autoAssignMale2025').checked;
        const autoAssignFemale2025 = document.getElementById('autoAssignFemale2025').checked;
        const autoAssignMale2026 = document.getElementById('autoAssignMale2026').checked;
        const autoAssignFemale2026 = document.getElementById('autoAssignFemale2026').checked;

        // Build auto-assignment configuration
        formData.auto_assignment_config = {
          male_general: autoAssignMale,
          female_general: autoAssignFemale,
          male_2025: autoAssignMale2025,
          female_2025: autoAssignFemale2025,
          male_2026: autoAssignMale2026,
          female_2026: autoAssignFemale2026
        };

        // If auto-assignment is enabled and specific checkboxes are checked,
        // automatically populate students from Login database (pnph_users + student_details)
        if (enableAutoAssignment) {
          formData.auto_populate_students = true;
          formData.use_login_database = true; // Flag to use Login folder database

          // Collect capacity requirements for auto-assignment from Login database
          const autoAssignmentRequirements = {};

          if (autoAssignMale && formData.male_capacity) {
            autoAssignmentRequirements.male_general = formData.male_capacity;
          }
          if (autoAssignFemale && formData.female_capacity) {
            autoAssignmentRequirements.female_general = formData.female_capacity;
          }
          if (autoAssignMale2025 && formData.male_capacity_2025) {
            autoAssignmentRequirements.male_2025 = formData.male_capacity_2025;
          }
          if (autoAssignFemale2025 && formData.female_capacity_2025) {
            autoAssignmentRequirements.female_2025 = formData.female_capacity_2025;
          }
          if (autoAssignMale2026 && formData.male_capacity_2026) {
            autoAssignmentRequirements.male_2026 = formData.male_capacity_2026;
          }
          if (autoAssignFemale2026 && formData.female_capacity_2026) {
            autoAssignmentRequirements.female_2026 = formData.female_capacity_2026;
          }

          formData.auto_assignment_requirements = autoAssignmentRequirements;

          // Add database source specification
          formData.student_data_source = {
            users_table: 'pnph_users',
            details_table: 'student_details',
            gender_field: 'gender', // M/F format
            batch_field: 'batch',   // 2025, 2026 format
            name_fields: ['user_fname', 'user_lname'], // First and last name
            role_filter: 'student'  // Only get users with student role
          };
        }

        // Enhanced capacity and gender validation
        const maleCapacity = parseInt(formData.male_capacity) || 0;
        const femaleCapacity = parseInt(formData.female_capacity) || 0;

        // Validate total capacity constraints
        if (maleCapacity + femaleCapacity > newCapacity) {
          showAlert('error', 'Male + Female capacity cannot exceed total capacity');
          return;
        }

        // Validate gender-based assignment policy
        if (maleCapacity > 0 && femaleCapacity > 0) {
          const confirmMixed = await showConfirmationDialog(
            'Gender Policy Warning',
            'You have specified both male and female capacities for this room. ' +
            'This violates the gender-based room assignment policy where each room should contain only students of the same gender. ' +
            'Do you want to continue anyway?',
            'Continue',
            'Cancel'
          );

          if (!confirmMixed) {
            return;
          }
        }

        // Validate batch-specific capacities
        const male2025 = parseInt(formData.male_capacity_2025) || 0;
        const female2025 = parseInt(formData.female_capacity_2025) || 0;
        const male2026 = parseInt(formData.male_capacity_2026) || 0;
        const female2026 = parseInt(formData.female_capacity_2026) || 0;

        const totalBatchCapacity = male2025 + female2025 + male2026 + female2026;
        if (totalBatchCapacity > newCapacity) {
          showAlert('error', `Batch-specific capacities (${totalBatchCapacity}) cannot exceed total capacity (${newCapacity})`);
          return;
        }

        // Check for existing students and gender compatibility
        const currentRoomData = getCurrentRoomData(roomNumber);
        if (currentRoomData && currentRoomData.students && currentRoomData.students.length > 0) {
          const existingGenders = [...new Set(currentRoomData.students.map(s => s.gender).filter(g => g))];

          if (existingGenders.length > 0) {
            const existingGender = existingGenders[0];
            const conflictingCapacity = (existingGender === 'M' && femaleCapacity > 0) ||
                                      (existingGender === 'F' && maleCapacity > 0);

            if (conflictingCapacity) {
              const existingGenderText = existingGender === 'M' ? 'male' : 'female';
              const conflictingGenderText = existingGender === 'M' ? 'female' : 'male';

              const confirmReassign = await showConfirmationDialog(
                'Gender Conflict Detected',
                `This room currently has ${existingGenderText} students, but you've specified capacity for ${conflictingGenderText} students. ` +
                'This would require reassigning existing students to maintain gender-based room policy. Continue?',
                'Reassign Students',
                'Cancel'
              );

              if (!confirmReassign) {
                return;
              }

              formData.force_gender_reassignment = true;
            }
          }
        }

  // Disable button and show loading
  submitBtn.innerHTML = '<i class="fi fi-rr-sync" style="margin-right:6px; animation: spin 1s linear infinite;"></i> Saving Changes...';
        submitBtn.disabled = true;

        try {
          console.log('Making request to update room', formData);

          const response = await fetch('/api/room/set-individual-capacity', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': csrfToken,
              'Accept': 'application/json'
            },
            body: JSON.stringify(formData)
          });

          console.log('Response status:', response.status);
          console.log('Response headers:', response.headers);

          if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
          }

          const data = await response.json();
          console.log('Response data:', data);

          if (data.success) {
            // Close modal
            closeIndividualCapacityModal();

            // Show enhanced success message with auto-assignment details
            let successMessage = `Room ${roomNumber} updated successfully!\n`;
            if (data.changes_summary) {
              successMessage += `\nChanges made:\n${data.changes_summary}`;
            }
            if (data.students_reassigned && data.students_reassigned > 0) {
              successMessage += `\n${data.students_reassigned} student(s) were automatically reassigned.`;
            }

            // Add auto-assignment feedback from Login database
            if (data.auto_assigned_students) {
              const autoAssigned = data.auto_assigned_students;
              successMessage += `\n\nAuto-Assignment Results (from  PNPh Management System):`;
              if (autoAssigned.male_general > 0) successMessage += `\n• ${autoAssigned.male_general} male students assigned from pnph_users`;
              if (autoAssigned.female_general > 0) successMessage += `\n• ${autoAssigned.female_general} female students assigned from pnph_users`;
              if (autoAssigned.male_2025 > 0) successMessage += `\n• ${autoAssigned.male_2025} male batch 2025 students assigned`;
              if (autoAssigned.female_2025 > 0) successMessage += `\n• ${autoAssigned.female_2025} female batch 2025 students assigned`;
              if (autoAssigned.male_2026 > 0) successMessage += `\n• ${autoAssigned.male_2026} male batch 2026 students assigned`;
              if (autoAssigned.female_2026 > 0) successMessage += `\n• ${autoAssigned.female_2026} female batch 2026 students assigned`;
            }

            // Show assigned student names if available
            if (data.assigned_student_names && data.assigned_student_names.length > 0) {
              successMessage += `\n\nAssigned Students:`;
              data.assigned_student_names.forEach(student => {
                successMessage += `\n• ${student.name} (${student.gender}, Batch ${student.batch})`;
              });
            }

            // Add cross-room synchronization feedback
            if (data.cross_room_updates && data.cross_room_updates.length > 0) {
              successMessage += `\n\nCross-Room Updates:`;
              data.cross_room_updates.forEach(update => {
                successMessage += `\n• Room ${update.room_number}: capacity adjusted to ${update.new_capacity}`;
              });
            }

            // Show success alert with auto-dismiss
            showSuccessAlert(successMessage);

            // ENHANCED IMMEDIATE ROOM CARD UPDATES - Real-time synchronization
            console.log('Starting enhanced real-time room card updates with response data:', data);

            // 1. Update all rooms provided by backend map for instant UI sync
            if (data.updated_room_data) {
              updateDashboardWithRoomData(data.updated_room_data);
            }

            // 2. Update the specific room that was edited (capacity and list)
            await updateSpecificRoomCardWithGenderStyling(roomNumber, data);

            // 3. Update affected rooms (accept both array and object map)
            if (data.affected_rooms_list || data.affected_rooms) {
              await updateAffectedRoomsWithAnimation(data.affected_rooms_list || data.affected_rooms);
            }

            // 3. Perform dynamic room adjustment across all floors
            if (enableCrossRoomSync) {
              await performDynamicRoomAdjustment(data);
            }

            // 4. Force refresh all room assignments from backend with loading indicators
            setTimeout(async () => {
              await refreshAllRoomCardsWithLoadingIndicators();
              // Ensure the edited card capacity reflects new value immediately
              document.querySelectorAll(`.room-box[data-room-number="${roomNumber}"]`).forEach(card => {
                card.setAttribute('data-capacity', String(newCapacity));
                const occ = card.querySelector('.occupancy');
                if (occ) {
                  const currentCount = (data.updated_room_data && data.updated_room_data[roomNumber]) ? data.updated_room_data[roomNumber].length : (card.querySelectorAll('.occupants-list li').length);
                  occ.textContent = `${currentCount}/${newCapacity}`;
                }
              });
              console.log('All room card updates completed with real-time sync');
            }, 500);

            // Update global capacity settings if they changed
            if (data.global_capacity_updated) {
              updateGlobalCapacityDisplay(data.new_global_capacity);
            }

          } else {
            alert('Error: ' + (data.message || 'Failed to update room capacity'));
          }
        } catch (error) {
          console.error('Error updating individual room capacity:', error);
          alert('Error updating room capacity. Please try again.');
        } finally {
          // Reset button
          submitBtn.textContent = 'Save Changes';
          submitBtn.disabled = false;
        }
      }

      function refreshCurrentFloorView() {
        // Refresh the currently displayed floor if any
        const activeFloorBtn = document.querySelector('.floor-btn.active');
        if (activeFloorBtn) {
          const floor = activeFloorBtn.dataset.floor;
          // Trigger a refresh of the floor data
          activeFloorBtn.click();
        }
      }

      // Make functions globally accessible
      // Initialize real-time synchronization system
      function initializeRealTimeSync() {
        // Listen for localStorage changes (cross-tab communication)
        window.addEventListener('storage', function(e) {
          if (e.key === 'roomAssignmentsUpdate' && e.newValue) {
            try {
              const updateEvent = JSON.parse(e.newValue);
              if (updateEvent.type === 'room_assignments_updated') {
                console.log('Received cross-tab room update:', updateEvent);

                // Update dashboard with new data
                if (updateEvent.roomData) {
                  updateDashboardWithRoomData(updateEvent.roomData);
                }

                // Show notification about the update
                if (updateEvent.updatedRooms.length > 0) {
                  showEnhancedAlert('info',
                    `Room assignments updated in another tab: ${updateEvent.updatedRooms.join(', ')}`,
                    3000
                  );
                }
              }
            } catch (error) {
              console.error('Error processing cross-tab update:', error);
            }
          }
        });

        // Listen for BroadcastChannel messages (modern browsers)
        if (window.BroadcastChannel) {
          const channel = new BroadcastChannel('room_updates');
          channel.addEventListener('message', function(event) {
            const updateEvent = event.data;
            if (updateEvent.type === 'room_assignments_updated') {
              console.log('Received broadcast room update:', updateEvent);

              // Update dashboard with new data
              if (updateEvent.roomData) {
                updateDashboardWithRoomData(updateEvent.roomData);
              }
            }
          });
        }

        // Periodic sync to ensure data consistency (every 30 seconds)
        setInterval(async function() {
          try {
            await performPeriodicSync();
          } catch (error) {
            console.error('Periodic sync failed:', error);
          }
        }, 30000);

        // Visibility change listener to sync when tab becomes active
        document.addEventListener('visibilitychange', function() {
          if (!document.hidden) {
            setTimeout(async () => {
              try {
                await performPeriodicSync();
              } catch (error) {
                console.error('Visibility sync failed:', error);
              }
            }, 1000);
          }
        });

        console.log('Real-time synchronization initialized');
      }

      // Perform periodic synchronization
      async function performPeriodicSync() {
        try {
          const response = await fetch('/api/dashboard/room-data', {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          });

          if (response.ok) {
            const data = await response.json();
            if (data.success && data.roomStudents) {
                // Merge server roomStudents into local cache to avoid wiping local per-room edits.
                // We prefer authoritative server values for rooms included in the payload,
                // but keep any local-only rooms untouched.
                const merged = Object.assign({}, window.roomStudents || {});
                Object.keys(data.roomStudents).forEach(rn => {
                  merged[rn] = data.roomStudents[rn] || [];
                });

                const hasChanges = JSON.stringify(window.roomStudents || {}) !== JSON.stringify(merged);
                if (hasChanges) {
                  console.log('Periodic sync detected changes, updating dashboard');
                  // Use the regular merge/update function so UI, floorData and cross-tab
                  // broadcasts are handled consistently.
                  updateDashboardWithRoomData(data.roomStudents);

                  // Update global room data to the merged result
                  window.roomStudents = merged;
                }
              }
          }
        } catch (error) {
          // Silent fail for periodic sync
          console.warn('Periodic sync failed:', error);
        }
      }

      // Enhanced notification system for real-time updates
      function showRealTimeUpdateNotification(type, roomNumber, studentName, action) {
        const messages = {
          added: `Student "${studentName}" added to room ${roomNumber}`,
          removed: `Student "${studentName}" removed from room ${roomNumber}`,
          moved: `Student "${studentName}" moved to room ${roomNumber}`,
          updated: `Student assignment updated in room ${roomNumber}`
        };

        const message = messages[action] || `Room ${roomNumber} updated`;
        showEnhancedAlert(type, message, 4000);
      }

      // Add CSS for room styling (animations removed)
      const style = document.createElement('style');
      style.textContent = `
        .occupancy-badge {
          display: inline-block;
          padding: 2px 8px;
          border-radius: 12px;
          font-size: 0.75rem;
          font-weight: 500;
          color: white;
        }

        .badge-success { background-color: #28a745; }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-danger { background-color: #dc3545; }

        /* Prevent visual shaking/movement of room cards when JS updates inline styles.
           This forces no transforms, transitions or animations on room cards and their
           children so adding/editing students won't cause cards to jump. */
        .room-box, .room-box * {
          transition: none !important;
          animation: none !important;
          transform: none !important;
        }
      `;
      document.head.appendChild(style);

      // Enhanced success alert with auto-dismiss
      function showSuccessAlert(message) {
        // Create or update success alert
        let alertDiv = document.getElementById('successAlert');
        if (!alertDiv) {
          alertDiv = document.createElement('div');
          alertDiv.id = 'successAlert';
          alertDiv.style.cssText = `
            position: fixed; top: 20px; right: 20px; z-index: 10000;
            background: #d4edda; color: #155724; padding: 15px 20px;
            border-radius: 8px; border-left: 4px solid #28a745;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            max-width: 400px; font-family: 'Poppins', sans-serif;
          `;
          document.body.appendChild(alertDiv);
        }

        alertDiv.innerHTML = `
          <div style="display: flex; align-items: flex-start; gap: 10px;">
            <i class="fi fi-rr-check-circle" style="color: #28a745; font-size: 1.2rem; margin-top: 2px;"></i>
            <div style="flex: 1;">
              <strong>Success!</strong><br>
              <span style="font-size: 0.9rem; white-space: pre-line;">${message}</span>
            </div>
          </div>
        `;

        alertDiv.style.display = 'block';

        // Auto-dismiss after 5 seconds
        setTimeout(() => {
          if (alertDiv) {
            alertDiv.style.opacity = '0';
            alertDiv.style.transform = 'translateX(100%)';
            setTimeout(() => alertDiv.remove(), 300);
          }
        }, 5000);
      }

      // Update multiple room cards simultaneously
      function updateMultipleRoomCards(affectedRooms) {
        if (!affectedRooms || typeof affectedRooms !== 'object') return;

        Object.keys(affectedRooms).forEach(roomNumber => {
          const roomData = affectedRooms[roomNumber];
          const roomCards = document.querySelectorAll(`[data-room-number="${roomNumber}"]`);

          roomCards.forEach(roomCard => {
            // Update students with capacity information
            if (roomData.students) {
              updateRoomCardStudents(roomCard, roomData.students, roomData.capacity);
            }

            // Update capacity display if available
            if (roomData.capacity) {
              const capacityElements = roomCard.querySelectorAll('.capacity-display');
              capacityElements.forEach(el => {
                el.textContent = `Capacity: ${roomData.capacity}`;
              });

              // Also update occupancy display with new capacity (display expands if students exceed configured capacity)
              const occupancySpan = roomCard.querySelector('.occupancy');
              if (occupancySpan && roomData.students) {
                const displayCapacity = Math.max(roomData.capacity, roomData.students.length);
                occupancySpan.textContent = `${roomData.students.length}/${displayCapacity}`;
              }
            }

            // Add visual feedback for updated rooms
            roomCard.style.transition = 'all 0.3s ease';
            roomCard.style.borderColor = '#28a745';
            roomCard.style.boxShadow = '0 4px 12px rgba(40, 167, 69, 0.3)';

            setTimeout(() => {
              roomCard.style.borderColor = '';
              roomCard.style.boxShadow = '';
            }, 2000);
          });
        });
      }



      // IMMEDIATE UPDATE FUNCTIONS FOR PROPER ROOM CARD SYNC

      // Update specific room card immediately with new data
      async function updateSpecificRoomCardImmediately(roomNumber, responseData) {
        console.log(`Immediately updating room ${roomNumber} card with data:`, responseData);

        const roomCards = document.querySelectorAll(`[data-room-number="${roomNumber}"]`);
        if (roomCards.length === 0) {
          console.log(`No room cards found for room ${roomNumber}`);
          return;
        }

        // Extract data from response
        const newCapacity = responseData.new_capacity || responseData.capacity;
        const newStudents = responseData.room_assignments ? responseData.room_assignments[roomNumber] : [];

        console.log(`Room ${roomNumber} - New capacity: ${newCapacity}, Students:`, newStudents);

        roomCards.forEach(roomCard => {
          // Update students list
          const occupantsList = roomCard.querySelector('.occupants-list');
          if (occupantsList) {
            occupantsList.innerHTML = '';
            if (Array.isArray(newStudents)) {
              newStudents.forEach(student => {
                const li = document.createElement('li');
                li.textContent = student;
                occupantsList.appendChild(li);
              });
            }
          }

          // Update the room header to show new capacity
          // Update room header and occupancy display using displayCapacity = max(configured, current students)
          const studentCount = Array.isArray(newStudents) ? newStudents.length : 0;
          const configuredCapacity = newCapacity || parseInt(roomCard.getAttribute('data-capacity')) || 6;
          const displayCapacity = Math.max(configuredCapacity, studentCount);

          // Update the header
          const occupantsHeader = roomCard.querySelector('.student-occupants h4');
          if (occupantsHeader) {
            occupantsHeader.textContent = `Student Occupants (${studentCount}/${displayCapacity})`;
            console.log(`Updated header for room ${roomNumber} to show ${studentCount}/${displayCapacity}`);
          }

          // Update occupancy span
          const occupancySpan = roomCard.querySelector('.occupancy');
          if (occupancySpan) {
            occupancySpan.textContent = `${studentCount}/${displayCapacity}`;
          }

          // Update capacity displays
          const capacityElements = roomCard.querySelectorAll('.capacity-display, .room-capacity');
          capacityElements.forEach(el => {
            if (newCapacity) {
              el.textContent = newCapacity;
            }
          });

          // Visual feedback with green highlight
          roomCard.style.transition = 'all 0.5s ease';
          roomCard.style.backgroundColor = '#d4edda';
          roomCard.style.borderColor = '#28a745';
          roomCard.style.transform = 'scale(1.02)';

          setTimeout(() => {
            roomCard.style.backgroundColor = '';
            roomCard.style.borderColor = '';
            roomCard.style.transform = 'scale(1)';
          }, 2000);
        });

        console.log(`Successfully updated room ${roomNumber} cards with capacity ${newCapacity}`);
      }

      // Update affected rooms immediately
      async function updateAffectedRoomsImmediately(affectedRooms) {
        console.log('Immediately updating affected rooms:', affectedRooms);

        Object.keys(affectedRooms).forEach(roomNumber => {
          const roomData = affectedRooms[roomNumber];
          const roomCards = document.querySelectorAll(`[data-room-number="${roomNumber}"]`);

          roomCards.forEach(roomCard => {
            // Update students if provided
            if (roomData.students) {
              const occupantsList = roomCard.querySelector('.occupants-list');
              if (occupantsList) {
                occupantsList.innerHTML = '';
                roomData.students.forEach(student => {
                  const li = document.createElement('li');
                  li.textContent = student;
                  occupantsList.appendChild(li);
                });
              }
            }

            // Update capacity and occupancy
            if (roomData.capacity) {
              const studentCount = roomData.students ? roomData.students.length : 0;
              const displayCapacity = Math.max(roomData.capacity, studentCount);

              const occupancySpan = roomCard.querySelector('.occupancy');
              if (occupancySpan) {
                occupancySpan.textContent = `${studentCount}/${displayCapacity}`;
              }

              const capacityElements = roomCard.querySelectorAll('.capacity-display, .room-capacity');
              capacityElements.forEach(el => {
                // Keep stored/configured capacity intact, but show displayCapacity when appropriate
                el.textContent = displayCapacity;
              });
            }

            // Visual feedback for affected rooms
            roomCard.style.transition = 'all 0.3s ease';
            roomCard.style.borderColor = '#ffc107';
            setTimeout(() => {
              roomCard.style.borderColor = '';
            }, 2000);
          });
        });
      }

      // Refresh all room cards from backend data
      async function refreshAllRoomCardsFromBackend() {
        try {
          console.log('Refreshing all room cards from backend...');

          const response = await fetch('/api/dashboard/room-data', {
            method: 'GET',
            headers: {
              'Accept': 'application/json',
              'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
          });

          if (response.ok) {
            const data = await response.json();
            console.log('Backend room data:', data);

            // Update all room cards with fresh backend data
            const allRoomCards = document.querySelectorAll('[data-room-number]');
            allRoomCards.forEach(roomCard => {
              const roomNumber = roomCard.getAttribute('data-room-number');

              // Get room data from response
              const roomStudents = data.room_assignments ? data.room_assignments[roomNumber] : [];
              const roomCapacity = data.room_capacities ? data.room_capacities[roomNumber] : 6;

              console.log(`Refreshing room ${roomNumber} - Students: ${roomStudents.length}, Capacity: ${roomCapacity}`);

              // Update occupants list
              const occupantsList = roomCard.querySelector('.occupants-list');
              if (occupantsList) {
                occupantsList.innerHTML = '';
                if (Array.isArray(roomStudents)) {
                  roomStudents.forEach(student => {
                    const li = document.createElement('li');
                    li.textContent = student;
                    occupantsList.appendChild(li);
                  });
                }
              }

              // Update the "Student Occupants" header and occupancy display using displayCapacity
              const occupantsHeader = roomCard.querySelector('.student-occupants h4');
              const studentCount = Array.isArray(roomStudents) ? roomStudents.length : 0;
              const displayCapacity = Math.max(roomCapacity, studentCount);
              if (occupantsHeader) {
                occupantsHeader.textContent = `Student Occupants (${studentCount}/${displayCapacity})`;
              }

              // Update occupancy display
              const occupancySpan = roomCard.querySelector('.occupancy');
              if (occupancySpan) {
                occupancySpan.textContent = `${studentCount}/${displayCapacity}`;
              }

              // Update capacity displays
              const capacityElements = roomCard.querySelectorAll('.capacity-display, .room-capacity');
              capacityElements.forEach(el => {
                el.textContent = roomCapacity;
              });
            });

            console.log('All room cards refreshed from backend');
          }
        } catch (error) {
          console.error('Error refreshing room cards from backend:', error);
        }
      }

      // Update global capacity display
      function updateGlobalCapacityDisplay(newCapacity) {
        const capacityDisplays = document.querySelectorAll('.global-capacity-display');
        capacityDisplays.forEach(display => {
          display.textContent = `${newCapacity} students per room`;
        });

        // Update the main capacity input if visible
        const mainCapacityInput = document.getElementById('roomCapacity');
        if (mainCapacityInput) {
          mainCapacityInput.value = newCapacity;
        }
      }

      // Enhanced Dynamic Room Adjustment Functions

      // Update specific room card with gender-based styling and animations
      async function updateSpecificRoomCardWithGenderStyling(roomNumber, responseData) {
        try {
          const roomCard = document.querySelector(`[data-room-number="${roomNumber}"]`);
          if (!roomCard) return;

          // Add loading animation
          roomCard.style.transition = 'all 0.3s ease';
          roomCard.style.transform = 'scale(0.98)';
          roomCard.style.opacity = '0.7';

          // Update room data
          if (responseData.updated_room_data) {
            const roomData = responseData.updated_room_data;

            // Update occupancy with gender-based styling and dynamic capacity
            const occupancySpan = roomCard.querySelector('.occupancy');
            if (occupancySpan && roomData.students) {
              const studentCount = roomData.students.length;
              const configuredCap = roomData.capacity || 6;
              const displayCapacity = Math.max(configuredCap, studentCount);
              occupancySpan.textContent = `${studentCount}/${displayCapacity}`;

              // Add gender-based color coding
              const genders = [...new Set(roomData.students.map(s => s.gender).filter(g => g))];
              if (genders.length === 1) {
                const genderColor = genders[0] === 'M' ? '#007bff' : '#e91e63';
                occupancySpan.style.color = genderColor;
                occupancySpan.style.fontWeight = 'bold';
              }
            }

            // Update student list with gender badges
            const studentsList = roomCard.querySelector('.students-list, .room-students');
            if (studentsList && roomData.students) {
              updateStudentListWithGenderBadges(studentsList, roomData.students);
            }
          }

          // Animate back to normal
          setTimeout(() => {
            roomCard.style.transform = 'scale(1)';
            roomCard.style.opacity = '1';
            roomCard.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
          }, 300);

          // Remove animation styles after completion
          setTimeout(() => {
            roomCard.style.transition = '';
            roomCard.style.boxShadow = '';
          }, 600);

        } catch (error) {
          console.error('Error updating specific room card:', error);
        }
      }

      // Update affected rooms with smooth animations
      async function updateAffectedRoomsWithAnimation(affectedRooms) {
        try {
          for (const roomUpdate of affectedRooms) {
            const roomCard = document.querySelector(`[data-room-number="${roomUpdate.room_number}"]`);
            if (roomCard) {
              // Pulse animation to indicate change
              roomCard.style.animation = 'pulse 0.6s ease-in-out';

              // Update capacity and occupancy (display expands if current occupancy exceeds new capacity)
              const occupancySpan = roomCard.querySelector('.occupancy');
              if (occupancySpan) {
                const displayCapacity = Math.max(roomUpdate.new_capacity, roomUpdate.current_occupancy);
                occupancySpan.textContent = `${roomUpdate.current_occupancy}/${displayCapacity}`;
              }

              // Update capacity displays
              const capacityElements = roomCard.querySelectorAll('.capacity-display, .room-capacity');
              capacityElements.forEach(el => {
                el.textContent = roomUpdate.new_capacity;
              });

              // Remove animation after completion
              setTimeout(() => {
                roomCard.style.animation = '';
              }, 600);
            }
          }
        } catch (error) {
          console.error('Error updating affected rooms:', error);
        }
      }

      // Perform dynamic room adjustment across all floors
      async function performDynamicRoomAdjustment(responseData) {
        try {
          if (!responseData.cross_room_adjustments) return;

          showAlert('info', '<i class="fi fi-rr-sync" style="margin-right:6px;color:#17a2b8;"></i> Performing dynamic room adjustments across all floors...', 3000);

          // Process each floor's adjustments
          for (const floorAdjustment of responseData.cross_room_adjustments) {
            const floorRooms = document.querySelectorAll(`[data-room-number^="${floorAdjustment.floor}"]`);

            floorRooms.forEach(roomCard => {
              // Add subtle animation to show synchronization
              roomCard.style.borderLeft = '4px solid #28a745';
              roomCard.style.transition = 'border-left 0.3s ease';

              setTimeout(() => {
                roomCard.style.borderLeft = '';
                roomCard.style.transition = '';
              }, 2000);
            });
          }

          // Show completion message
          setTimeout(() => {
            showAlert('success', `<i class="fi fi-rr-check" style="margin-right:6px;color:#28a745;"></i> Dynamic room adjustments completed successfully!`, 3000);
          }, 1000);

        } catch (error) {
          console.error('Error performing dynamic room adjustment:', error);
          showAlert('error', `<i class="fi fi-rr-cross" style="margin-right:6px;color:#dc3545;"></i> Error during dynamic room adjustment. Please refresh the page.`, 5000);
        }
      }

      // Refresh all room cards with loading indicators
      async function refreshAllRoomCardsWithLoadingIndicators() {
        try {
          // Show loading indicators on all room cards
          const allRoomCards = document.querySelectorAll('[data-room-number]');
          allRoomCards.forEach(card => {
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'room-loading-indicator';
            loadingIndicator.innerHTML = '<i class="fi fi-rr-spinner" style="animation: spin 1s linear infinite;"></i> Syncing...';
            loadingIndicator.style.cssText = `
              position: absolute;
              top: 10px;
              right: 10px;
              background: rgba(0,123,255,0.9);
              color: white;
              padding: 4px 8px;
              border-radius: 12px;
              font-size: 0.75rem;
              z-index: 10;
            `;
            card.style.position = 'relative';
            card.appendChild(loadingIndicator);
          });

          // Fetch updated room data
          const response = await fetch('/api/room/get-all-assignments');
          const data = await response.json();

          if (data.success && data.room_assignments) {
            // Update each room card with new data
            Object.entries(data.room_assignments).forEach(([roomNumber, roomData]) => {
              const roomCard = document.querySelector(`[data-room-number="${roomNumber}"]`);
              if (roomCard) {
                updateRoomCardWithCompleteData(roomCard, roomNumber, roomData);
              }
            });
          }

          // Remove loading indicators
          setTimeout(() => {
            document.querySelectorAll('.room-loading-indicator').forEach(indicator => {
              indicator.remove();
            });
          }, 500);

        } catch (error) {
          console.error('Error refreshing room cards with loading indicators:', error);
          // Remove loading indicators on error
          document.querySelectorAll('.room-loading-indicator').forEach(indicator => {
            indicator.remove();
          });
        }
      }

      // Update student list with gender badges and batch information
      function updateStudentListWithGenderBadges(studentsList, students) {
        if (!studentsList || !students) return;

        let studentsHtml = '';
        students.forEach((student, index) => {
          const genderBadge = student.gender ?
            `<span class="badge ${student.gender === 'M' ? 'badge-blue' : 'badge-pink'}" style="font-size: 0.7rem; margin-left: 6px;">
              <i class="fi fi-rr-${student.gender === 'M' ? 'mars' : 'venus'}"></i>
              ${student.gender === 'M' ? 'M' : 'F'}
            </span>` : '';

          const batchBadge = student.batch ?
            `<span class="badge badge-secondary" style="font-size: 0.7rem; margin-left: 4px;">
              <i class="fi fi-rr-graduation-cap"></i>
              ${student.batch}
            </span>` : '';

          studentsHtml += `
            <div class="student-item" style="padding: 8px; border-bottom: 1px solid #eee; display: flex; align-items: center; justify-content: space-between;">
              <div style="display: flex; align-items: center; gap: 8px;">
                <span style="font-weight: 500;">${student.name || student}</span>
                ${genderBadge}
                ${batchBadge}
              </div>
              <div class="student-actions" style="display: flex; gap: 4px;">
                <button class="btn btn-sm btn-outline-primary" onclick="openEditStudentModal('${student.name || student}', ${index})" title="Edit Student">
                  <i class="fi fi-rr-edit"></i>
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="confirmRemoveStudent('${student.name || student}', ${index})" title="Remove Student">
                  <i class="fi fi-rr-trash"></i>
                </button>
              </div>
            </div>
          `;
        });

        studentsList.innerHTML = studentsHtml;
      }

      // Update room card with complete data including gender and batch information
      function updateRoomCardWithCompleteData(roomCard, roomNumber, roomData) {
        try {
          // Update occupancy
          const occupancySpan = roomCard.querySelector('.occupancy');
          if (occupancySpan) {
            const studentCount = Array.isArray(roomData.students) ? roomData.students.length : 0;
            const configuredCapacity = roomData.capacity || 6;
            const displayCapacity = Math.max(configuredCapacity, studentCount);
            occupancySpan.textContent = `${studentCount}/${displayCapacity}`;
          }

          // Update student list
          const studentsList = roomCard.querySelector('.students-list, .room-students');
          if (studentsList) {
            updateStudentListWithGenderBadges(studentsList, roomData.students || []);
          }

          // Update room header with gender indication
          const roomHeader = roomCard.querySelector('.room-header h3, .room-title h3');
          if (roomHeader && roomData.students && roomData.students.length > 0) {
            const genders = [...new Set(roomData.students.map(s => s.gender).filter(g => g))];
            if (genders.length === 1) {
              const genderIcon = genders[0] === 'M' ? 'fi-rr-mars' : 'fi-rr-venus';
              const genderColor = genders[0] === 'M' ? '#007bff' : '#e91e63';
              roomHeader.innerHTML = `Room ${roomNumber} <i class="fi ${genderIcon}" style="color: ${genderColor}; margin-left: 8px;"></i>`;
            }
          }

        } catch (error) {
          console.error('Error updating room card with complete data:', error);
        }
      }

      window.openCapacityModal = openCapacityModal;
      window.applyCapacitySettings = applyCapacitySettings;
      window.updateDashboardWithNewAssignments = updateDashboardWithNewAssignments;
      window.openIndividualCapacityModal = openIndividualCapacityModal;
      window.closeIndividualCapacityModal = closeIndividualCapacityModal;
      window.applyIndividualCapacity = applyIndividualCapacity;

      // ===== GENERAL TASK STATUS MODAL (Educator view) =====
      (function(){
        const modal = document.getElementById('filteredStudentsModal');
        const modalTitle = document.getElementById('filteredStudentsTitle');
        const modalSubtitle = document.getElementById('filteredStudentsSubtitle');
        const modalCategory = document.getElementById('filteredStudentsCategory');
        const modalDate = document.getElementById('filteredStudentsDateFilter');
        const modalContent = document.getElementById('filteredStudentsContent');
        const statusDetailsEndpoint = @json(route('generalTask.inspection.status-details'));
        const modalState = { status: null, data: [] };

        function renderStudents(students) {
          if (!students.length) {
            modalContent.innerHTML = '<div class="empty-state">No students match the selected filters.</div>';
            return;
          }

          const columnTemplate = '280px 0.95fr 0.95fr 1.35fr 1.05fr 0.9fr 0.85fr';
          const headerRow = `
            <div style="display:grid; grid-template-columns:${columnTemplate}; gap:22px; padding:12px 8px; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.1em; color:#94a3b8; font-weight:600;">
              <div>Assigned Student</div>
              <div>Main Area</div>
              <div>Sub Area</div>
              <div>Assigned Task</div>
              <div>Progress</div>
              <div>Status</div>
              <div>Date</div>
            </div>`;

          const rows = students.map((student, index) => {
            const avatarColors = ['#2050b8ff','#0aa844ff','#b81b1bff','#a77d22ff','#6230b9ff','#1a867dff'];
            const avatarColor = avatarColors[index % avatarColors.length];

            return `
              <div style="display:grid; grid-template-columns:${columnTemplate}; gap:22px; padding:18px 20px; border-bottom:1px solid #e5e7eb; align-items:center; border-radius:18px; background:#fff;">
                <div style="display:flex; align-items:center; gap:12px;">
                  <div style="width:48px; height:48px; border-radius:16px; background:${avatarColor}; display:flex; align-items:center; justify-content:center; font-weight:600; color:#fff;">
                    ${student.initials}
                  </div>
                  <div>
                    <div style="font-weight:600; color:#0f172a; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                      <span>${student.student_name}</span>
                    </div>
                    <div style="font-size:0.8rem; color:#94a3b8;">Batch ${student.batch ?? '—'}</div>
                  </div>
                </div>
                <div style="font-weight:600; color:#0f172a;">${student.main_area ?? 'General Task'}</div>
                <div style="font-weight:600; color:#0f172a;">${student.sub_area ?? '—'}</div>
                <div>
                  <div style="font-weight:600; color:#0f172a;">${student.task_title}</div>
                  <div style="font-size:0.8rem; color:#94a3b8;">${student.task_description ?? ''}</div>
                </div>
                <div style="display:flex; flex-direction:column; gap:6px;">
                  <div style="height:8px; background:#f1f5f9; border-radius:999px; overflow:hidden;">
                    <div style="height:8px; width:${student.progress}%; background:${student.status_color};"></div>
                  </div>
                  <div style="font-weight:600; color:${student.status_color}; font-size:0.8rem;">${student.progress}%</div>
                </div>
                <div style="display:flex; align-items:center;">
                  <span style="display:inline-flex; align-items:center; gap:6px; padding:6px 14px; border-radius:999px; font-size:0.8rem; font-weight:600; background:${student.status_bg}; color:${student.status_color};">${student.status_label}</span>
                </div>
                <div style="text-align:right; font-size:0.9rem; color:#475569;">
                  ${student.schedule_date_formatted ?? ''}
                </div>
              </div>`;
          }).join('');

          modalContent.innerHTML = `
            <div style="margin-top:12px;">
              ${headerRow}
              ${rows}
            </div>`;
        }

        function populateCategories(categories, selected) {
          const current = selected || 'all';
          modalCategory.innerHTML = '<option value="all">All Categories</option>';
          (categories || []).forEach(category => {
            const option = document.createElement('option');
            option.value = category.value;
            option.textContent = category.label;
            modalCategory.appendChild(option);
          });
          modalCategory.value = current;
        }

        function buildParams(status) {
          const params = new URLSearchParams({
            status,
            date_range: 'all',
            category: 'all',
            search: ''
          });

          if (modalCategory.value && modalCategory.value !== 'all') {
            params.set('modal_category', modalCategory.value);
          }

          if (modalDate.value) {
            params.set('date_filter', modalDate.value);
          }

          return params.toString();
        }

        async function fetchStatusDetails(status) {
          modalContent.innerHTML = '<div class="empty-state">Fetching students...</div>';
          try {
            const response = await fetch(`${statusDetailsEndpoint}?${buildParams(status)}`, {
              headers: {
                'X-Requested-With': 'XMLHttpRequest'
              }
            });

            if (!response.ok) {
              throw new Error('Failed to fetch students');
            }

            const data = await response.json();
            if (!data.success) {
              throw new Error(data.message || 'Unable to fetch data');
            }

            modalState.data = data.students || [];
            modalTitle.textContent = `${data.meta.label} Tasks`;
            if (modalSubtitle) {
              modalSubtitle.textContent = `${data.total} student${data.total === 1 ? '' : 's'} with ${data.meta.label.toLowerCase()} tasks`;
            }
            populateCategories(data.categories, modalCategory.value);
            renderStudents(modalState.data);
          } catch (error) {
            console.error('Error fetching status details:', error);
            modalContent.innerHTML = '<div class="empty-state">Unable to load students. Please try again.</div>';
          }
        }

        window.filterStudentsByStatus = function(status) {
          modalState.status = status;
          modalCategory.value = 'all';
          modalDate.value = '';
          modal.style.display = 'block';
          fetchStatusDetails(status);
        };

        function closeStatusModal() {
          modal.style.display = 'none';
          modalState.status = null;
        }

        function clearStatusFilters() {
          modalCategory.value = 'all';
          modalDate.value = '';
          if (modalState.status) {
            fetchStatusDetails(modalState.status);
          }
        }

        modalCategory.addEventListener('change', () => {
          if (modalState.status) {
            fetchStatusDetails(modalState.status);
          }
        });

        modalDate.addEventListener('change', () => {
          if (modalState.status) {
            fetchStatusDetails(modalState.status);
          }
        });

        window.addEventListener('click', (event) => {
          if (event.target === modal) {
            closeStatusModal();
          }
        });

        window.closeFilteredStudentsModal = closeStatusModal;
        window.clearFilteredStudentsFilter = clearStatusFilters;
      })();

      window.closeFilteredStudentsModal = function() {
        document.getElementById('filteredStudentsModal').style.display = 'none';
      };

      // Close modal when clicking outside
      document.addEventListener('click', function(event) {
        const modal = document.getElementById('filteredStudentsModal');
        if (event.target === modal) {
          modal.style.display = 'none';
        }
      });
    });
  </script>
</body>
</html>     