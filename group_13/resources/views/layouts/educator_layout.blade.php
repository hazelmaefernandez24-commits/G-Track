<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('css/nav.css') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    @yield('styles')
    <style>
    :root {
        --sidebar-width: 250px;
        --topbar-height: 80px;
        --content-padding: 20px;
    }

    /* Theme Loader */
    .loader {
        width: 50px;
        aspect-ratio: 1;
        border-radius: 50%;
        border: 8px solid #22bbea;
        border-right-color: #ff9933;
        animation: l2 1s infinite linear;
    }
    @keyframes l2 {
        to { transform: rotate(1turn); }
    }

    /* Loader Overlay */
    .loader-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(255, 255, 255, 0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 9999;
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .loader-overlay.hidden {
        opacity: 0;
        pointer-events: none;
    }
    
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif !important;
    }

    /* Preserve icon fonts */
    .fas, .far, .fal, .fab, .fa,
    [class*="fa-"],
    .material-icons,
    .glyphicon {
        font-family: "Font Awesome 6 Free", "Font Awesome 6 Pro", "Font Awesome 5 Free", "Font Awesome 5 Pro", "Material Icons", "Glyphicons Halflings" !important;
    }

    /* Preserve SVG icons */
    svg {
        font-family: inherit !important;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif !important;
        background-color: #f1f5f9;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .logout-btn {
        background: none;
        border: none;
        color: inherit;
        cursor: pointer;
    }

    .logout-btn:hover {
        color: #ff9933;
    }

    .top-bar {
        background-color: #22bbea;
        padding: 0 20px;
        display: flex;
        align-items: center;
        height: 80px;
        flex-shrink: 0;
    }

    .PN-logo {
        height: 40px;
    }

    .layout-container {
        display: flex;
        flex: 1;
        min-height: calc(100vh - var(--topbar-height));
        margin-top: var(--topbar-height);
        position: relative;
        width: 100%;
    }

    .sidebar {
        background-color: #ffffff;
        width: var(--sidebar-width);
        padding: 20px 0;
        position: fixed;
        top: var(--topbar-height);
        left: 0;
        bottom: 0;
        overflow-y: auto;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        z-index: 100;
    }

    .menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu li {
        padding: 12px 20px;
        display: flex;
        flex-direction: column;
        cursor: pointer;
        transition: all 0.3s ease;
        border-radius: 8px;
        margin: 0 10px;
    }

    .menu li:hover {
        background-color: #f1f5f9;
    }

    .menu li.active {
        background-color: #e3f2fd;
        border-left: 4px solid #22bbea;
        padding-left: 16px;
    }

    .menu li a {
        display: flex;
        align-items: center;
        gap: 10px;
        color: #333333;
        text-decoration: none;
        width: 100%;
        font-size: 15px;
        transition: color 0.3s ease;
    }

    .menu li.active a {
        color: #22bbea;
        font-weight: 600;
    }

    .dropdown > a {
        display: flex;
        align-items: center;
        width: 100%;
    }

    .dropdown.active > a {
        color: #22bbea;
        font-weight: 600;
    }

    .dropdown-content {
        display: none;
        padding: 5px 0;
        margin-top: 8px;
    }

    .dropdown.active .dropdown-content {
        display: block;
    }

    .dropdown-content a {
        padding: 8px 0 8px 34px;
        color: #333333;
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s ease;
        border-radius: 6px;
        margin: 2px 10px;
    }

    .dropdown-content a img {
        width: 20px;
        height: 20px;
    }

    .dropdown-content a:hover {
        background-color: #f1f5f9;
        color: #22bbea;
    }

    .dropdown-content a.active {
        background-color: #e3f2fd;
        color: #22bbea;
        font-weight: 600;
        border-left: 3px solid #22bbea;
        padding-left: 31px;
    }

    .dropdown > a::after {
        content: '▼';
        font-size: 10px;
        margin-left: auto;
    }

    .dropdown.active > a::after {
        content: '▲';
    }

    .menu li img {
        width: 24px;
        height: 24px;
    }

    .content {
        flex-grow: 1;
        padding: var(--content-padding);
        overflow-y: auto;
        background-color: #f8f9fa;
        margin-left: var(--sidebar-width);
    }
</style>

</head>
<body>
    <!-- Theme Loader -->
    <div class="loader-overlay" id="pageLoader">
        <div class="loader"></div>
    </div>
    <div class="top-bar">
        <img class="PN-logo" src="{{ asset('images/PN-logo.png') }}" alt="PN Logo">

        {{-- Add Logged in as info and Logout --}}
        @auth
            @php
                $user = Auth::user();
            @endphp

            <div class="user-info" style="color: #333; font-weight: 500; display: flex; align-items: center; gap: 15px;">
                Logged in as: 
                <span style="color: white;">
                    {{ $user->user_fname }} {{ $user->user_mInitial }} {{ $user->user_lname }} {{ $user->suffix }}
                </span> 
                | Role: 
                <span style="color: white;">
                    {{ ucfirst($user->user_role) }}
                </span>

                <form action="{{ route('logout') }}" method="POST" id="logout-form" style="display: inline;">
                    @csrf
                    <button type="button" class="logout-btn" onclick="confirmLogout()">
                        <svg class="w-6 h-6 text-gray-800 dark:text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
                        </svg>
                    </button>
                </form>
            </div>
        @endauth
    </div>


    <div class="layout-container">
        <aside class="sidebar">
            <ul class="menu">

                <li class="{{ request()->routeIs('educator.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('educator.dashboard') }}">
                        <img src="{{ asset('images/Dashboard.png') }}" alt="Dashboard"> Dashboard
                    </a>
                </li>
                <li class="{{ request()->routeIs('educator.students.index') || request()->routeIs('educator.students.*') ? 'active' : '' }}">
                    <a href="{{ route('educator.students.index') }}">
                        <img src="{{ asset('images/mu.png') }}" alt="Students Info"> Students Info
                    </a>
                </li>

               
                <li class="dropdown {{ request()->routeIs('educator.analytics.*') ? 'active' : '' }}" id="educatorAnalyticsDropdown">
                    <a href="#" onclick="toggleDropdown(event)">
                        <img src="{{ asset('images/analytics.png') }}" alt="Analytics"> Analytics
                    </a>
                    <div class="dropdown-content">
                        <a href="{{ route('educator.analytics.class-grades') }}" class="{{ request()->routeIs('educator.analytics.class-grades') ? 'active' : '' }}">
                            <img src="{{ asset('images/class grades.png') }}" alt="Class Grades"> Class Grades
                        </a>
                        <a href="{{ route('educator.analytics.subject-progress') }}" class="{{ request()->routeIs('educator.analytics.subject-progress') ? 'active' : '' }}">
                            <img src="{{ asset('images/subject progress.png') }}" alt="Subject Progress"> Subject Progress
                        </a>
                        <a href="{{ route('educator.analytics.subject-intervention') }}" class="{{ request()->routeIs('educator.analytics.subject-intervention') ? 'active' : '' }}">
                            <img src="{{ asset('images/subject intervention.png') }}" alt="Subject Intervention"> Subject Intervention
                        </a>
                        <a href="{{ route('educator.analytics.class-progress') }}" class="{{ request()->routeIs('educator.analytics.class-progress') ? 'active' : '' }}">
                            <img src="{{ asset('images/analytics.png') }}" alt="Class Progress"> Class Progress
                        </a>
                        <a href="{{ route('educator.analytics.intern-grades-progress') }}" class="{{ request()->routeIs('educator.analytics.intern-grades-progress') ? 'active' : '' }}">
                            <img src="{{ asset('images/internship grades.png') }}" alt="Internship Grades Progress"> Internship Grades Progress
                        </a>
                    </div>
                </li>
                <li class="{{ request()->routeIs('educator.intervention') ? 'active' : '' }}">
                    <a href="{{ route('educator.intervention') }}">
                        <img src="{{ asset('images/is.png') }}" alt="Intervention Status"> Intervention Status
                    </a>
                </li>
                <li class="{{ request()->routeIs('educator.calendar.*') ? 'active' : '' }}">
                    <a href="{{ route('educator.calendar.index') }}">
                        <img src="{{ asset('images/calendar.png') }}" alt="Calendar"> Calendar
                    </a>
                </li>

            </ul>
        </aside>

        <main class="content">
            @yield('content')
        </main>
    </div>

    <script>
    // Track page load state
    let pageFullyLoaded = false;

    // Hide loader when page is loaded
    window.addEventListener('load', function() {
        pageFullyLoaded = true;
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.classList.add('hidden');
            setTimeout(() => {
                loader.style.display = 'none';
            }, 300);
        }
    });

    // Show loader on user interactions
    document.addEventListener('DOMContentLoaded', function() {
        // Form submissions - loader disabled for forms
        // document.querySelectorAll('form').forEach(form => {
        //     form.addEventListener('submit', function(e) {
        //         // Loader disabled for form submissions
        //     });
        // });

        // Show loader on navigation links
        document.querySelectorAll('a:not([href^="#"]):not([href^="javascript:"]):not([target="_blank"]):not([href^="mailto:"]):not([href^="tel:"])').forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't show loader if page hasn't fully loaded yet
                if (!pageFullyLoaded) {
                    return;
                }

                if (!this.getAttribute('onclick') || this.getAttribute('href') !== '#') {
                    const onclick = this.getAttribute('onclick');

                    // If it has confirm() in onclick, don't show loader immediately
                    if (onclick && onclick.includes('confirm(')) {
                        return;
                    }

                    showLoader();
                }
            });
        });

        // Show loader on button clicks that might navigate (excluding submit buttons)
        document.querySelectorAll('.btn[href], button[onclick*="location"], button[onclick*="window.location"]').forEach(button => {
            button.addEventListener('click', function() {
                // Don't show loader if page hasn't fully loaded yet
                if (!pageFullyLoaded) {
                    return;
                }

                const onclick = this.getAttribute('onclick');

                // If it has confirm() in onclick, don't show loader immediately
                if (onclick && onclick.includes('confirm(')) {
                    return;
                }

                showLoader();
            });
        });

        // Hide loader on browser back/forward navigation
        window.addEventListener('pageshow', function(event) {
            if (event.persisted) {
                hideLoader();
            }
        });

        window.addEventListener('popstate', function() {
            hideLoader();
        });
    });

    function showLoader() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.style.display = 'flex';
            loader.classList.remove('hidden');
        }
    }

    function hideLoader() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.classList.add('hidden');
            setTimeout(() => {
                loader.style.display = 'none';
            }, 300);
        }
    }

    // Enhanced confirm function that handles loader properly
    window.confirmWithLoader = function(message, callback) {
        if (confirm(message)) {
            showLoader();
            if (callback) callback();
            return true;
        }
        return false;
    };

    function toggleDropdown(event) {
        event.preventDefault();
        const dropdown = event.target.closest('.dropdown');
        if (dropdown) {
            dropdown.classList.toggle('active');
        }
    }

    // Auto-open dropdown if any child is active
    document.addEventListener('DOMContentLoaded', function() {
        const activeDropdown = document.querySelector('.dropdown.active');
        if (activeDropdown) {
            activeDropdown.classList.add('active');
        }
    });

    
     function confirmLogout() {
            if (confirm("Are you sure you want to log out?")) {
                document.getElementById('logout-form').submit();
            }
        }
    
</script>

@yield('scripts')
@stack('scripts')

   
</body>
</html>
