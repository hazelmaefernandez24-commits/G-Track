@php
    $user = session('user');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Student Dashboard')</title>
  <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('assets/css/all.min.css') }}">
  <link href="{{ asset('assets/css/poppins-font.css') }}" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/payment-history.css') }}">

  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="{{ asset('assets/css/sweetalert2.min.css') }}">
  <script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>

 <style>
    :root {
      --primary-skyblue: #22BBEA;
      --sidebar-bg: #ffffff;
      --sidebar-hover: #e2f4fb;
      --text-primary: #343a40;
      --content-bg: #f1f6fb;
    }
    * { box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--content-bg);
      margin: 0; padding: 0;
      overflow-x: hidden;
    }
    .wrapper { display: flex; min-height: 100vh; }
    .sidebar {
      width: 240px;
      background: var(--sidebar-bg);
      border-right: 1px solid #dee2e6;
      transform: translateX(-240px);
      transition: transform .3s ease;
      position: fixed; top: 0; left: 0; bottom: 0; z-index: 1000;
    }
    .sidebar.show { transform: translateX(0); }
    .sidebar .logo { height: 50px; margin: 1rem auto; display: block; }
    .sidebar .nav-link {
      color: var(--text-primary);
      padding: 12px 20px; margin: 4px 8px;
      border-radius: 8px;
      transition: background .3s ease, color .3s ease;
      display: flex; align-items: center;
    }
    .sidebar .nav-link i { margin-right: 12px; }
    .sidebar .nav-link:hover,
    .sidebar .nav-link.active { background: var(--sidebar-hover); color: var(--primary-skyblue); }
    .main-content { flex-grow: 1; margin-left: 0; transition: margin-left .3s ease; }
    .main-content.shift { margin-left: 240px; }
    .header {
      background: var(--primary-skyblue);
      display: flex; align-items: center;
      padding: 0.75rem 1rem;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      position: relative; z-index: 900;
    }
    #sidebarToggle { background: none; border: none; color: #fff; font-size: 1.5rem; padding: 4px; }
    .title {
      flex: 1;
      color: #fff;
      font-weight: 700;
      text-align: center;
      margin: 0;
    }
    .header-profile {
      flex: 0;
      margin-left: auto;
    }
    .header-profile .dropdown-toggle {
      display: inline-flex;
      align-items: center;
      white-space: nowrap;
      color: #fff;
      font-weight: 500;
      padding: 4px 8px;
      background: transparent;
      border: none;
    }
    .header-profile .dropdown-toggle:focus { outline: none; box-shadow: none; }
    .content { padding: 24px; }
    .content-section {
      background: #fff;
      border-radius: 12px;
      padding: 24px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.05);
      margin-bottom: 20px;
    }
    #overlay {
      position: fixed; top: 0; left: 0; right: 0; bottom: 0;
      background: rgba(0,0,0,0.4);
      display: none; z-index: 950;
    }
    #overlay.show { display: block; }
    /* Mobile-specific styles */
    @media (max-width: 768px) {
        .sidebar {
            width: 200px;
        }
        .main-content.shift {
            margin-left: 200px;
        }
        .header {
            padding: 0.5rem;
            flex-wrap: wrap;
        }
        .title {
            font-size: 1rem;
            margin: 0.25rem 0;
        }
        .header-profile .dropdown-toggle {
            font-size: 0.8rem;
            padding: 2px 6px;
        }
        .content {
            padding: 16px;
        }
        .content-section {
            padding: 16px;
        }
        #sidebarToggle {
            font-size: 1.2rem;
        }
    }
    @media (max-width: 576px) {
        .sidebar .nav-link {
            font-size: 0.85rem;
            padding: 8px 16px;
        }
        .sidebar .logo {
            height: 40px;
            margin: 0.75rem auto;
        }
        .header-profile .dropdown-menu {
            min-width: 120px;
        }
    }
    .notification-badge {
        position: absolute;
          top: -8px;
        right: -8px;
        background-color:  #ff9933;
        color: white;
        border-radius: 50%;
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
        min-width: 18px;
        height: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .badge {
        font-size: 0.75rem;
        padding: 0.25em 0.4em;
        border-radius: 50%;
        color: white;
    }
    .bg-danger {
        background-color:  #ff9933;
    }
    .position-absolute {
        position: absolute;
    }
    .position-relative {
        position: relative;
    }
    .translate-middle {
        transform: translate(-50%, -50%);
    }
    .top-0 {
        top: 0;
    }
    .start-100 {
        left: 100%;
    }
</style>
</head>
<body>
<div class="wrapper">
  <nav id="sidebar" class="sidebar">
    <img src="{{ asset('photos/pnlogo.png') }}" alt="Logo" class="logo">
    <ul class="mt-3 nav flex-column">
      <li class="nav-item">
        <a href="{{ route('student.studentDashboard') }}" class="nav-link @if(request()->routeIs('student.studentDashboard')) active @endif">
          <i class="fas fa-tachometer-alt"></i>Dashboard</a>
      </li>
      <li class="nav-item">
        <a href="{{ route('student.studentPayments') }}" class="nav-link @if(request()->routeIs('student.studentPayments')) active @endif">
          <i class="fas fa-credit-card"></i>Payment History</a>
      </li>
      <li class="nav-item">
        <a href="{{ route('payment-methods.show') }}" class="nav-link @if(request()->routeIs('payment-methods.show')) active @endif">
          <i class="fas fa-money-bill"></i> Payment Options
        </a>
      </li>
      <li class="nav-item">
        <a href="{{ route('student.paymentForm') }}" class="nav-link @if(request()->routeIs('student.paymentForm.blade')) active @endif">
          <i class="fas fa-upload"></i> Upload Payment</a>
      </li>
      <li class="nav-item">
        <a href="{{ route('student.notifications') }}" class="nav-link @if(request()->routeIs('student.notifications')) active @endif">
          <i class="fas fa-bell position-relative">
            @php
              $sessionUser = session('user');
              $unreadCount = 0;
              if ($sessionUser) {
                  $unreadCount = \App\Models\CustomNotification::where('user_id', $sessionUser['user_id'])
                      ->where('is_read', 0)
                      ->count();
              }
            @endphp
            @if($unreadCount > 0)
              <span id="sidebarNotificationBadge" class="notification-badge">{{ $unreadCount }}</span>
            @else
              <span id="sidebarNotificationBadge" class="notification-badge" style="display: none;"></span>
            @endif
          </i> Notifications
        </a>
      </li>
    </ul>
  </nav>

  <div id="mainContent" class="main-content">
    <header class="header">
      <button id="sidebarToggle"><i id="sidebarToggleIcon" class="fas fa-bars"></i></button>
      <h1 class="mb-0 title h5">@yield('page-title', 'Student Dashboard')</h1>
      <div class="header-profile dropdown">
        <button class="dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">

          @if(isset($user))
            {{ $user['user_fname'] }} {{ $user['user_lname'] }}
          @else
            Student
          @endif
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
          <li><a class="dropdown-item" href="{{ route('student.profile') }}">Profile</a></li>
          <li><hr class="dropdown-divider"></li>
          <li>
            <form method="POST" action="{{ route('logout') }}">
              @csrf
              <button type="submit" class="dropdown-item w-100 text-start">Logout</button>
            </form>
          </li>
        </ul>
      </div>
    </header>

    <main class="content">
      <div class="content-section">
        @yield('content')
      </div>
    </main>
  </div>
</div>

<div id="overlay"></div>
<script src="{{ asset('assets/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script>
  const sidebar = document.getElementById('sidebar');
  const mainContent = document.getElementById('mainContent');
  const overlay = document.getElementById('overlay');
  const toggleBtn = document.getElementById('sidebarToggle');
  const toggleIcon = document.getElementById('sidebarToggleIcon');

  function toggleSidebar() {
    const isOpen = sidebar.classList.toggle('show');
    overlay.classList.toggle('show', isOpen);
    mainContent.classList.toggle('shift', isOpen);
    toggleIcon.classList.toggle('fa-times', isOpen);
    toggleIcon.classList.toggle('fa-bars', !isOpen);
  }

  toggleBtn.addEventListener('click', toggleSidebar);
  overlay.addEventListener('click', toggleSidebar);

  document.addEventListener('DOMContentLoaded', () => {
    const sidebarNotificationBadge = document.getElementById('sidebarNotificationBadge'); // Badge inside the bell icon

    const fetchUnreadNotifications = () => {
        fetch('/notifications/unread-count')
            .then(response => response.json())
            .then(data => {
                if (data.unread_count > 0) {
                    sidebarNotificationBadge.style.display = 'inline-block';
                    sidebarNotificationBadge.textContent = data.unread_count;
                } else {
                    sidebarNotificationBadge.style.display = 'none';
                }
            })
            .catch(error => console.error('Error fetching notifications:', error));
    };

    // Fetch notifications on page load
    fetchUnreadNotifications();

    // Optionally, poll for new notifications every 30 seconds
    setInterval(fetchUnreadNotifications, 30000);
  });
</script>
@yield('scripts')
@stack('scripts')
@stack('styles')
</body>
</html>
