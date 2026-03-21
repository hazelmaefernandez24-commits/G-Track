<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Dashboard' }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Removed dashboard2.css to prevent style conflicts -->
    <style>
        :root {
            --sidebar-width: 250px;
            --topbar-height: 80px;
            --content-padding: 20px;
            --primary-color: #22bbea;
            --sidebar-bg: #ffffff;
            --content-bg: #f8f9fa;
            --text-color: #333333;
            --hover-bg: #e3f2fd;
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
            background-color: var(--content-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--text-color);
        }

        .top-bar {
            height: var(--topbar-height);
            background: var(--primary-color);
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 30px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .PN-logo {
            height: 50px;
            width: auto;
            object-fit: contain;
        }

        .user-info {
            color: white;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 16px;
        }

        .user-info span {
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .logout-btn {
            background: none;
            border: none;
            color: inherit;
            cursor: pointer;
            padding: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logout-btn:hover {
            color: #ff9933;
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
            width: var(--sidebar-width);
            background: var(--sidebar-bg);
            position: fixed;
            top: var(--topbar-height);
            left: 0;
            bottom: 0;
            overflow-y: auto;
            border-right: 2px solid var(--primary-color);
            z-index: 100;
        }

        .menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .menu li {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.3s;
            color: var(--text-color);
        }

        .menu li:hover {
            background-color: var(--hover-bg);
        }

        .menu li.active {
            background-color: rgba(34, 187, 234, 0.1);
            border-left: 4px solid var(--primary-color);
            padding-left: 16px;
        }

        .menu li a {
            text-decoration: none;
            color: inherit;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .menu li img {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            opacity: 0.8;
        }

        .content {
            flex: 1;
            padding: var(--content-padding);
            margin-left: var(--sidebar-width);
            background-color: var(--content-bg);
            min-height: calc(100vh - var(--topbar-height));
        }

        /* Success and Error Message Styles */
        .alert {
            padding: 15px 20px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideInDown 0.3s ease-out;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .alert-error,
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }

        .alert-warning {
            color: #856404;
            background-color: #fff3cd;
            border-color: #ffeaa7;
        }

        .alert-info {
            color: #0c5460;
            background-color: #d1ecf1;
            border-color: #bee5eb;
        }

        .alert .alert-icon {
            font-size: 18px;
            flex-shrink: 0;
        }

        .alert-success .alert-icon {
            color: #28a745;
        }

        .alert-error .alert-icon,
        .alert-danger .alert-icon {
            color: #dc3545;
        }

        .alert-warning .alert-icon {
            color: #ffc107;
        }

        .alert-info .alert-icon {
            color: #17a2b8;
        }

        .alert-close {
            position: absolute;
            top: 10px;
            right: 15px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            opacity: 0.7;
            transition: opacity 0.2s;
        }

        .alert-close:hover {
            opacity: 1;
        }

        .alert ul {
            margin: 0;
            padding-left: 20px;
        }

        .alert li {
            margin-bottom: 5px;
        }

        @keyframes slideInDown {
            from {
                transform: translateY(-20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Auto-hide animation */
        .alert.fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateY(0);
            }
            to {
                opacity: 0;
                transform: translateY(-20px);
            }
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

        @auth
            @php
                $user = Auth::user();
                $currentRoute = request()->route()->getName();
            @endphp

            <div class="user-info">
                Logged in as: 
                <span>
                    {{ $user->user_fname }} {{ $user->user_mInitial }} {{ $user->user_lname }} {{ $user->suffix }}
                </span> 
                | Role: 
                <span>
                    {{ ucfirst($user->user_role) }}
                </span>

                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="button" class="logout-btn" onclick="confirmLogout()">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        @endauth
    </div>

    <div class="layout-container">
        <aside class="sidebar">
            <ul class="menu">
                <li class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <a href="{{ route('admin.dashboard') }}">
                        <img src="{{ asset('images/Dashboard.png') }}" alt="Dashboard"> Dashboard
                    </a>
                </li>
                <li class="{{ request()->routeIs('admin.pnph_users.*') ? 'active' : '' }}">
                    <a href="{{ route('admin.pnph_users.index') }}">
                        <img src="{{ asset('images/mu.png') }}" alt="Manage Users"> Manage Users
                    </a>
                </li>
            </ul>
        </aside>

        <main class="content">
            <!-- Success and Error Messages -->
            @if(session('success'))
                <div class="alert alert-success" id="success-alert">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="alert-close" onclick="closeAlert('success-alert')">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-error" id="error-alert">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="alert-close" onclick="closeAlert('error-alert')">&times;</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning" id="warning-alert">
                    <i class="fas fa-exclamation-triangle alert-icon"></i>
                    <span>{{ session('warning') }}</span>
                    <button type="button" class="alert-close" onclick="closeAlert('warning-alert')">&times;</button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info" id="info-alert">
                    <i class="fas fa-info-circle alert-icon"></i>
                    <span>{{ session('info') }}</span>
                    <button type="button" class="alert-close" onclick="closeAlert('info-alert')">&times;</button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger" id="validation-errors-alert">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <div>
                        <strong>Please fix the following errors:</strong>
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" class="alert-close" onclick="closeAlert('validation-errors-alert')">&times;</button>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @yield('scripts')
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

        function confirmLogout() {
            if (confirm('Are you sure you want to logout?')) {
                document.getElementById('logout-form').submit();
            }
        }

        // Alert management functions
        function closeAlert(alertId) {
            const alert = document.getElementById(alertId);
            if (alert) {
                alert.classList.add('fade-out');
                setTimeout(() => {
                    alert.remove();
                }, 500);
            }
        }

        // Auto-hide success messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const successAlert = document.getElementById('success-alert');
            if (successAlert) {
                setTimeout(() => {
                    closeAlert('success-alert');
                }, 5000);
            }

            const infoAlert = document.getElementById('info-alert');
            if (infoAlert) {
                setTimeout(() => {
                    closeAlert('info-alert');
                }, 5000);
            }
        });

        // Enhanced form validation with better error handling
        function validateAndSubmit(formId, confirmMessage) {
            const form = document.getElementById(formId);
            if (!form) return false;

            // Clear any existing validation errors
            clearValidationErrors();

            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }

            // Show confirmation if message provided
            if (confirmMessage && !confirm(confirmMessage)) {
                return false;
            }

            // Show loader and submit
            showLoader();
            form.submit();
            return true;
        }

        function clearValidationErrors() {
            const errorElements = document.querySelectorAll('.validation-error');
            errorElements.forEach(element => element.remove());
        }

        // Show success message dynamically
        function showSuccessMessage(message) {
            const existingAlert = document.getElementById('dynamic-success-alert');
            if (existingAlert) {
                existingAlert.remove();
            }

            const alertHtml = `
                <div class="alert alert-success" id="dynamic-success-alert">
                    <i class="fas fa-check-circle alert-icon"></i>
                    <span>${message}</span>
                    <button type="button" class="alert-close" onclick="closeAlert('dynamic-success-alert')">&times;</button>
                </div>
            `;

            const content = document.querySelector('.content');
            content.insertAdjacentHTML('afterbegin', alertHtml);

            setTimeout(() => {
                closeAlert('dynamic-success-alert');
            }, 5000);
        }

        // Show error message dynamically
        function showErrorMessage(message) {
            const existingAlert = document.getElementById('dynamic-error-alert');
            if (existingAlert) {
                existingAlert.remove();
            }

            const alertHtml = `
                <div class="alert alert-error" id="dynamic-error-alert">
                    <i class="fas fa-exclamation-circle alert-icon"></i>
                    <span>${message}</span>
                    <button type="button" class="alert-close" onclick="closeAlert('dynamic-error-alert')">&times;</button>
                </div>
            `;

            const content = document.querySelector('.content');
            content.insertAdjacentHTML('afterbegin', alertHtml);
        }
    </script>
</body>
</html>
