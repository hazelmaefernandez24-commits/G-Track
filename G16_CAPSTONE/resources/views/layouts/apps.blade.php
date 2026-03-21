<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>General Task Assignment</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
  <style>
    body {
      background: #eef2f7;
      font-family: "Poppins", sans-serif;
      margin: 0;
      padding: 0;
    }

    /* Header Styles */
    .main-header {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      color: white;
      padding: 20px 0;
      text-align: center;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .header-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 20px;
      text-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    /* Navigation Button Styles */
    .nav-buttons {
      display: flex;
      justify-content: center;
      gap: 15px;
      flex-wrap: wrap;
    }

    .nav-btn {
      background: rgba(255, 255, 255, 0.2);
      color: white;
      border: 2px solid rgba(255, 255, 255, 0.3);
      padding: 12px 24px;
      border-radius: 25px;
      text-decoration: none;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.3s ease;
      backdrop-filter: blur(10px);
    }

    .nav-btn:hover {
      background: rgba(255, 255, 255, 0.3);
      border-color: rgba(255, 255, 255, 0.5);
      color: white;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .nav-btn.active {
      background: rgba(255, 255, 255, 0.9);
      color: #4facfe;
      border-color: white;
      font-weight: 700;
    }

    /* Content Container */
    .content-container {
      padding: 30px 20px;
      max-width: 1200px;
      margin: 0 auto;
    }

    /* Dashboard specific styles */
    .dashboard-title {
      font-weight: 700;
      color: #333;
      text-align: center;
      margin-bottom: 30px;
    }

    /* Table styles */
    .table {
      background: white;
      border-radius: 10px;
      overflow: hidden;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .table th {
      background: #f8f9fa;
      font-weight: 600;
      color: #333;
      border: none;
      padding: 15px;
    }

    .table td {
      padding: 15px;
      border: none;
      border-bottom: 1px solid #eee;
    }

    /* Form styles */
    .form-control, .form-select {
      border-radius: 8px;
      border: 2px solid #e9ecef;
      padding: 12px 15px;
      font-size: 1rem;
    }

    .form-control:focus, .form-select:focus {
      border-color: #4facfe;
      box-shadow: 0 0 0 0.2rem rgba(79, 172, 254, 0.25);
    }

    .btn-primary {
      background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
      border: none;
      border-radius: 8px;
      padding: 12px 24px;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
    }

    /* Card styles */
    .card {
      border: none;
      border-radius: 15px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
      transition: all 0.3s ease;
    }

    .card:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    /* Responsive design */
    @media (max-width: 768px) {
      .header-title {
        font-size: 2rem;
      }

      .nav-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
      }

      .content-container {
        padding: 20px 15px;
      }
    }
  </style>
</head>
<body>
  <!-- Header -->
  <header class="main-header">
    <div class="container">
      <h1 class="header-title">General Task Assignment</h1>

      <!-- Navigation Buttons -->
      <div class="nav-buttons d-flex justify-content-center gap-3">
        <a href="{{ route('dashboard') }}" class="btn nav-btn {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          Dashboard
        </a>
        <a href="{{ url('/students') }}" class="btn nav-btn {{ request()->is('students*') ? 'active' : '' }}">
          Students
        </a>
        <a href="{{ url('/categories') }}" class="btn nav-btn {{ request()->is('categories*') ? 'active' : '' }}">
          Categories
        </a>
        <a href="{{ url('/assignments') }}" class="btn nav-btn {{ request()->is('assignments*') ? 'active' : '' }}">
          Assignments
        </a>
      </div>
    </div>
  </header>

  <!-- Content -->
  <div class="content-container">
    @yield('content')
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>