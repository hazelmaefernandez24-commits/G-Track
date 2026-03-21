<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <title>{{ $title ?? 'Student Dashboard' }}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    @yield('styles')

    <style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
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
        font-family: 'Poppins', sans-serif !important;
        background-color: #f8f9fa;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        overflow-x: hidden; /* Prevent horizontal scroll */
        -webkit-text-size-adjust: 100%; /* Prevent text scaling on iOS */
        -webkit-tap-highlight-color: rgba(34, 187, 234, 0.3); /* Better tap highlight */
        touch-action: manipulation; /* Improve touch responsiveness */
    }

    /* Header */
    .top-bar {
        background-color: #22bbea !important;
        padding: 0 30px !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        height: 80px !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 1000 !important;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1) !important;
    }

    /* Mobile Menu Toggle */
    .mobile-menu-toggle {
        display: none; /* Hidden by default */
        background: none;
        border: none;
        color: white;
        font-size: 22px;
        cursor: pointer;
        padding: 10px;
        border-radius: 8px;
        transition: background-color 0.3s ease;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0.3);
        min-width: 45px;
        min-height: 45px;
        align-items: center;
        justify-content: center;
        position: relative;
        z-index: 1001;
    }

    .mobile-menu-toggle:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }

    .mobile-menu-toggle:active {
        background-color: rgba(255, 255, 255, 0.2);
        transform: scale(0.95);
    }

    /* Logo styles to match training layout */
    .top-bar .PN-logo {
        height: 40px !important;
        width: auto !important;
        max-width: 180px !important;
        object-fit: contain !important;
        margin: 0 !important;
        display: block !important;
        flex-shrink: 0 !important;
        transition: all 0.3s ease !important;
    }

    /* Main Layout */
    .main-wrapper {
        display: flex;
        padding-top: 80px; /* Height of top-bar */
        min-height: 100vh;
        width: 100%;
        position: relative;
    }

    /* Sidebar */
    .sidebar {
        background-color: #ffffff;
        width: 260px;
        position: fixed;
        top: 80px; /* Adjusted to connect with header */
        left: 0;
        bottom: 0;
        overflow-y: auto;
        box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        z-index: 950; /* Higher than overlay */
        padding: 0;
        margin: 0;
        transition: transform 0.3s ease;
        pointer-events: auto;
    }

    /* Sidebar overlay for mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 800 !important; /* Much lower than sidebar */
        opacity: 0;
        transition: opacity 0.3s ease;
        pointer-events: none;
    }

    .sidebar-overlay.active {
        opacity: 1;
        pointer-events: auto;
        z-index: 800 !important; /* Much lower than sidebar */
        background-color: rgba(0, 0, 0, 0.5) !important;
    }

    /* Ensure sidebar content is always above overlay */
    .sidebar.active {
        z-index: 950 !important;
        background-color: #ffffff !important;
        box-shadow: 2px 0 10px rgba(0,0,0,0.3) !important;
    }

    .sidebar.active .menu {
        z-index: 1000 !important;
        background-color: #ffffff !important;
    }

    .sidebar.active .menu li {
        background-color: transparent !important;
    }

    .sidebar.active .menu li a {
        z-index: 1000 !important;
        background-color: transparent !important;
        color: #333 !important;
    }

    /* Menu Styling */
    .menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu li:first-child {
        margin-top: 10px;
    }
    
    .menu li {
        margin-bottom: 5px;
    }

    .menu li a {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        color: #333;
        text-decoration: none;
        font-size: 15px;
        transition: all 0.2s ease;
        border-radius: 8px;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(34, 187, 234, 0.3);
        min-height: 48px; /* Ensure minimum touch target size */
        position: relative;
        z-index: 1000; /* High z-index for clickability */
        pointer-events: auto;
        cursor: pointer;
    }

    .menu-icon {
        width: 20px;
        height: 20px;
        margin-right: 12px;
        object-fit: contain;
    }

    .menu li a:hover {
        background-color: #f1f5f9;
        color: #22bbea;
    }

    .menu li.active a {
        background-color: #e3f2fd;
        color: #22bbea;
        font-weight: 500;
    }

    /* Desktop menu - hidden on mobile */
    .menu {
        display: block;
        list-style: none;
        padding: 20px 0;
        margin: 0;
    }

    /* Mobile navigation menu - hidden on desktop */
    .mobile-nav-menu {
        display: none;
    }
    
    .menu li.active .menu-icon {
        /* Remove the blue filter to keep the original image */
        filter: none;
    }

    .menu li img {
        width: 24px;
        height: 24px;
    }
    
    /* Sidebar Logo */
    .sidebar-logo {
        padding: 20px 0;
        text-align: center;
        border-bottom: 1px solid #e2e8f0;
        margin-bottom: 20px;
    }
    
    .dashboard-logo {
        max-width: 80%;
        height: auto;
        max-height: 80px;
        object-fit: contain;
    }

    .menu li:hover {
        background-color: #f1f5f9;
    }

    .menu li.active {
        background-color: #f1f5f9;
    }

    /* Main Content */
    .content {
        flex: 1;
        margin-left: 260px; /* Same as sidebar width */
        padding: 20px;
        min-height: calc(100vh - 80px);
        background-color: #f8f9fa;
        width: calc(100% - 260px);
        transition: margin-left 0.3s ease, width 0.3s ease;
        position: relative;
        z-index: 1;
        touch-action: manipulation;
        pointer-events: auto;
    }

    /* User Info */
    .user-info {
        margin-left: auto;
        color: #fff;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-info span {
        color: #fff;
    }

    .user-name,
    .user-role-text {
        color: black;
    }

    .logged-in-text,
    .role-separator {
        color:rgb(37, 37, 37);
    }

    .user-role {
        background-color: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 13px;
    }

    /* Logout Button */
    .logout-btn {
        background: none;
        border: none;
        color: #fff;
        cursor: pointer;
        padding: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 45px;
        min-height: 45px;
        border-radius: 50%;
        transition: all 0.2s ease;
        touch-action: manipulation;
        -webkit-tap-highlight-color: rgba(255, 255, 255, 0.3);
        position: relative;
        z-index: 1;
    }

    .logout-btn:hover {
        background-color: rgba(255, 255, 255, 0.2);
        color: #ff9933;
    }

    /* Responsive Design */

    /* Large screens (1200px and up) */
    @media (min-width: 1200px) {
        .content {
            padding: 20px;
        }
    }

    /* Medium screens (992px to 1199px) */
    @media (max-width: 1199px) {
        .sidebar {
            width: 240px;
        }

        .content {
            margin-left: 240px;
            width: calc(100% - 240px);
        }
    }

    /* Small screens (768px to 991px) */
    @media (max-width: 991px) {
        .sidebar {
            width: 220px;
        }

        .content {
            margin-left: 220px;
            width: calc(100% - 220px);
            padding: 20px;
        }

        .user-info {
            font-size: 14px;
        }

        .logged-in-text,
        .role-separator {
            display: none; /* Hide "Logged in as:" and "| Role:" text on smaller screens */
        }

        .user-name {
            display: none; /* Hide user name on smaller screens */
        }

        .user-role {
            display: inline-block;
        }
    }

    /* Tablet screens (768px and below) */
    @media (max-width: 768px) {
        .top-bar {
            padding: 0 15px !important;
        }

        .mobile-menu-toggle {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* COMPLETELY RESET SIDEBAR FOR MOBILE */
        .sidebar {
            position: fixed !important;
            top: 80px !important;
            left: -100% !important;
            width: 280px !important;
            height: calc(100vh - 80px) !important;
            background: white !important;
            background-color: white !important;
            z-index: 10000 !important; /* Higher than overlay */
            transition: left 0.3s ease !important;
            overflow-y: auto !important;
            box-shadow: 2px 0 10px rgba(0,0,0,0.3) !important;
        }

        .sidebar.active {
            left: 0 !important;
            background: white !important;
            background-color: white !important;
            z-index: 10000 !important;
        }

        /* OVERLAY BEHIND SIDEBAR - FIXED */
        .sidebar-overlay {
            display: block !important;
            position: fixed !important;
            top: 0 !important;
            left: 280px !important; /* Start after sidebar width */
            width: calc(100% - 280px) !important; /* Only cover remaining area */
            height: 100% !important;
            background-color: rgba(0, 0, 0, 0.5) !important;
            z-index: 100 !important; /* Much lower z-index */
            opacity: 0 !important;
            pointer-events: none !important;
            transition: opacity 0.3s ease !important;
        }

        .sidebar-overlay.active {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        .content {
            margin-left: 0;
            width: 100%;
            padding: 20px 15px;
            position: relative;
            z-index: 1;
            touch-action: manipulation;
            pointer-events: auto;
        }

        /* Ensure all content elements are clickable on mobile */
        .content * {
            pointer-events: auto;
            touch-action: manipulation;
        }

        /* Fix common interactive elements */
        .content button,
        .content .btn,
        .content a,
        .content input,
        .content select,
        .content textarea,
        .content [onclick],
        .content [role="button"],
        .content .card,
        .content .clickable {
            pointer-events: auto !important;
            touch-action: manipulation !important;
            cursor: pointer !important;
            position: relative;
            z-index: 2;
        }

        .user-info {
            gap: 10px;
        }

        .logged-in-text,
        .role-separator,
        .user-name {
            display: none; /* Hide "Logged in as:", "| Role:" text and user name on mobile */
        }

        .user-role {
            font-size: 12px;
            padding: 3px 8px;
        }

        /* RESET MENU FOR MOBILE */
        .sidebar .menu {
            background: white !important;
            background-color: white !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .sidebar .menu li {
            background: transparent !important;
            background-color: transparent !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .sidebar .menu li a {
            display: flex !important;
            align-items: center !important;
            padding: 18px 20px !important;
            font-size: 16px !important;
            min-height: 56px !important;
            color: #333 !important;
            background: transparent !important;
            background-color: transparent !important;
            text-decoration: none !important;
            border: none !important;
            outline: none !important;
            touch-action: manipulation !important;
            -webkit-tap-highlight-color: rgba(34, 187, 234, 0.3) !important;
            pointer-events: auto !important;
            cursor: pointer !important;
            position: relative !important;
            z-index: 10000 !important;
        }

        .sidebar .menu li a:hover {
            background-color: #f1f5f9 !important;
            color: #22bbea !important;
        }

        .sidebar .menu li a:active {
            background-color: #e3f2fd !important;
            transform: scale(0.98) !important;
        }

        .sidebar .menu li.active a {
            background-color: #e3f2fd !important;
            color: #22bbea !important;
        }

        /* Hide desktop menu on mobile */
        .menu {
            display: none !important;
        }

        /* Show mobile navigation menu on mobile */
        .mobile-nav-menu {
            display: block !important;
        }

        .menu-icon {
            width: 28px;
            height: 28px;
            margin-right: 15px;
        }
    }

    /* Mobile screens (576px and below) */
    @media (max-width: 576px) {
        .top-bar {
            height: 70px !important;
            padding: 0 15px !important;
        }

        .mobile-menu-toggle {
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .main-wrapper {
            padding-top: 70px;
        }

        .sidebar {
            top: 70px;
            width: 100%;
            max-width: 300px;
        }

        .content {
            padding: 15px 10px;
            position: relative;
            z-index: 1;
            touch-action: manipulation;
            pointer-events: auto;
        }

        /* Additional mobile content fixes */
        .content button,
        .content .btn,
        .content a,
        .content input,
        .content select,
        .content textarea {
            min-height: 44px;
            min-width: 44px;
            touch-action: manipulation;
            pointer-events: auto;
            cursor: pointer;
        }

        .PN-logo {
            height: 35px !important;
            max-width: 160px !important;
        }

        .user-info {
            font-size: 12px;
        }

        .user-role {
            font-size: 11px;
            padding: 2px 6px;
        }

        .logout-btn {
            width: 32px;
            height: 32px;
        }

        .logout-btn svg {
            width: 20px;
            height: 20px;
        }
    }

    /* Extra small screens (480px and below) */
    @media (max-width: 480px) {
        .top-bar {
            height: 60px !important;
        }

        .main-wrapper {
            padding-top: 60px;
        }

        .sidebar {
            top: 60px;
        }

        .content {
            padding: 10px 8px;
        }

        .PN-logo {
            height: 30px !important;
            max-width: 140px !important;
        }

        .logged-in-text,
        .role-separator,
        .user-name,
        .user-role-text {
            display: none; /* Hide all user info text on very small screens */
        }

        .menu li a {
            padding: 12px 15px;
            font-size: 15px;
        }

        .menu-icon {
            width: 24px;
            height: 24px;
            margin-right: 12px;
        }
    }

    /* Landscape orientation adjustments for mobile */
    @media (max-height: 500px) and (orientation: landscape) {
        .sidebar {
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        .menu li a {
            padding: 10px 20px;
        }
    }

    /* Touch-friendly improvements */
    @media (hover: none) and (pointer: coarse) {
        .menu li a {
            padding: 18px 20px;
            min-height: 50px;
            display: flex;
            align-items: center;
        }

        .logout-btn {
            min-width: 44px;
            min-height: 44px;
        }

        .mobile-menu-toggle {
            min-width: 44px;
            min-height: 44px;
        }

        /* Ensure all buttons and links are touch-friendly */
        button, .btn, a, input[type="submit"], input[type="button"] {
            min-height: 48px;
            min-width: 48px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: rgba(34, 187, 234, 0.3);
            position: relative;
        }

        /* Improve tap targets for small elements */
        .status-badge, .grade, .subject-code {
            padding: 8px 12px;
            min-height: 32px;
        }
    }

    /* Global mobile improvements */
    @media (max-width: 768px) {
        /* Ensure all clickable elements are touch-friendly */
        button, .btn, a, input[type="submit"], input[type="button"],
        .card, .status-card, .subject-card, .submission-card,
        .table td, .table th, .list-item, .menu-item,
        [onclick], [role="button"], .clickable {
            touch-action: manipulation !important;
            -webkit-tap-highlight-color: rgba(34, 187, 234, 0.3) !important;
            cursor: pointer !important;
            position: relative;
            pointer-events: auto !important;
            z-index: 2;
        }

        /* Fix for tables and lists */
        .table, .list, .grid {
            touch-action: manipulation;
            pointer-events: auto;
        }

        /* Fix for dashboard cards and widgets */
        .dashboard-card, .widget, .stat-card, .info-card {
            touch-action: manipulation;
            pointer-events: auto;
            cursor: pointer;
        }

        /* Prevent text selection on buttons and cards */
        button, .btn, .card, .status-card, .subject-card {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }

        /* Improve button spacing and sizing */
        .btn, button {
            padding: 14px 20px;
            min-height: 48px;
            min-width: 48px;
            font-size: 16px;
            border-radius: 8px;
            touch-action: manipulation;
            -webkit-tap-highlight-color: rgba(34, 187, 234, 0.3);
            cursor: pointer;
            border: none;
            outline: none;
            position: relative;
        }

        /* Improve form elements */
        input, select, textarea {
            font-size: 16px; /* Prevent zoom on iOS */
            padding: 14px;
            min-height: 48px;
            border-radius: 8px;
            border: 1px solid #ddd;
            touch-action: manipulation;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        /* Add active states for better mobile feedback */
        button:active, .btn:active, a:active {
            transform: scale(0.98);
            opacity: 0.8;
        }

        /* Improve mobile menu button visibility */
        .mobile-menu-toggle:active {
            background-color: rgba(255, 255, 255, 0.2);
            transform: scale(0.95);
        }

        /* Better logout button feedback */
        .logout-btn:active {
            background-color: rgba(255, 255, 255, 0.2);
            transform: scale(0.95);
        }

        /* Menu link active states */
        .menu li a:active {
            background-color: #e3f2fd;
            transform: scale(0.98);
        }
    }

    /* Add focus styles for accessibility */
    button:focus, .btn:focus, a:focus {
        outline: 2px solid #22bbea;
        outline-offset: 2px;
    }

    /* Remove focus outline on touch devices */
    @media (hover: none) and (pointer: coarse) {
        button:focus, .btn:focus, a:focus {
            outline: none;
        }
    }

    /* Force mobile menu toggle to be clickable */
    @media (max-width: 768px) {
        .mobile-menu-toggle {
            pointer-events: auto !important;
            cursor: pointer !important;
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            user-select: none !important;
            -webkit-touch-callout: none !important;
        }

        /* Ensure all interactive elements are properly clickable */
        button, .btn, a, input[type="submit"], input[type="button"] {
            pointer-events: auto !important;
            cursor: pointer !important;
        }

        /* Prevent any overlays from blocking content when sidebar is closed */
        .sidebar-overlay:not(.active) {
            pointer-events: none !important;
            display: none !important;
        }

        /* Ensure content area is always clickable when sidebar is closed */
        .content {
            pointer-events: auto !important;
            position: relative;
            z-index: 1;
        }

        /* Fix for any potential blocking elements */
        .main-wrapper {
            position: relative;
            z-index: 1;
        }

        /* Ensure sidebar menu is always clickable on mobile */
        .sidebar .menu {
            pointer-events: auto;
            z-index: 1000;
            position: relative;
        }

        .sidebar .menu li {
            pointer-events: auto;
            z-index: 1000;
            position: relative;
        }

        .sidebar .menu li a {
            pointer-events: auto !important;
            z-index: 1000 !important;
            position: relative !important;
            display: flex !important;
            align-items: center !important;
            touch-action: manipulation !important;
            -webkit-tap-highlight-color: rgba(34, 187, 234, 0.3) !important;
        }

        /* Force sidebar to be above everything when active */
        .sidebar.active {
            z-index: 950 !important;
            pointer-events: auto !important;
            background-color: #ffffff !important;
        }

        .sidebar.active .menu {
            background-color: #ffffff !important;
            pointer-events: auto !important;
            z-index: 1000 !important;
        }

        .sidebar.active .menu li {
            background-color: transparent !important;
            pointer-events: auto !important;
            z-index: 1000 !important;
        }

        .sidebar.active .menu li a {
            background-color: transparent !important;
            color: #333 !important;
            pointer-events: auto !important;
            z-index: 1000 !important;
        }

        /* Override any conflicting styles */
        @media (max-width: 768px) {
            .sidebar {
                background-color: #ffffff !important;
            }

            .sidebar.active {
                background-color: #ffffff !important;
            }

            .sidebar .menu {
                background-color: #ffffff !important;
            }

            .sidebar .menu li {
                background-color: transparent !important;
            }

            .sidebar .menu li a {
                background-color: transparent !important;
                color: #333 !important;
            }

            .sidebar .menu li a:hover {
                background-color: #f1f5f9 !important;
                color: #22bbea !important;
            }

            .sidebar .menu li.active a {
                background-color: #e3f2fd !important;
                color: #22bbea !important;
            }
        }

        /* Emergency override for mobile sidebar background */
        .sidebar.active {
            background: #ffffff !important;
            background-color: #ffffff !important;
        }

        .sidebar.active::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ffffff !important;
            z-index: -1;
        }
    }

    /* EMERGENCY MOBILE SIDEBAR FIX - HIGHEST PRIORITY */
    @media (max-width: 768px) {
        .sidebar {
            background: white !important;
            background-color: white !important;
        }

        .sidebar.active {
            background: white !important;
            background-color: white !important;
            left: 0 !important;
            z-index: 9999 !important;
        }

        .sidebar * {
            background-color: transparent !important;
        }

        .mobile-nav-menu {
            background: white !important;
            background-color: white !important;
        }

        .mobile-nav-menu button {
            background: white !important;
            background-color: white !important;
            color: #333 !important;
        }

        .mobile-nav-menu button:hover,
        .mobile-nav-menu button:active {
            background: #f1f5f9 !important;
            background-color: #f1f5f9 !important;
        }

        /* NUCLEAR OPTION - FORCE WHITE SIDEBAR */
        .sidebar.active {
            background: white !important;
            background-color: white !important;
            background-image: none !important;
        }

        .sidebar.active * {
            background-color: transparent !important;
        }

        .sidebar.active .mobile-nav-menu {
            background: white !important;
            background-color: white !important;
        }

        .sidebar.active .mobile-nav-menu button {
            background: white !important;
            background-color: white !important;
            color: #333 !important;
            pointer-events: auto !important;
            cursor: pointer !important;
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
        <button class="mobile-menu-toggle" id="mobile-menu-toggle" type="button">
            <i class="fas fa-bars"></i>
        </button>
        <img class="PN-logo" src="{{ asset('images/PN-logo.png') }}" alt="PN Logo">

        <!-- Debug Info -->
        @php
            $user = Auth::user();
            $role = strtolower($user->user_role ?? 'none');
        @endphp
        <!-- Debug: User Role: {{ $role }} -->
        <!-- Debug: Is Student: {{ $role === 'student' ? 'Yes' : 'No' }} -->
        <!-- Debug: Route: {{ request()->path() }} -->
        
        @auth
            @php
                $user = Auth::user();
            @endphp

            <div class="user-info">
                <span class="logged-in-text">Logged in as:</span>
                <span class="user-name">
                    {{ $user->user_fname }} {{ $user->user_mInitial }} {{ $user->user_lname }} {{ $user->suffix }}
                </span>
                <span class="role-separator">| Role:</span>
                <span class="user-role-text">
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

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div class="main-wrapper">
        <aside class="sidebar">
            @auth
                @php $role = strtolower(Auth::user()->user_role ?? ''); @endphp
                @if($role === 'student')
                    <!-- Desktop Navigation Menu -->
                    <ul class="menu">
                        <li class="{{ request()->routeIs('student.dashboard') ? 'active' : '' }}">
                            <a href="{{ route('student.dashboard') }}">
                                <img src="{{ asset('images/Dashboard.png') }}" alt="Dashboard" class="menu-icon"> Dashboard
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('student.grades') ? 'active' : '' }}">
                            <a href="{{ route('student.grades') }}">
                                <img src="{{ asset('images/Dashboard.png') }}" alt="Grade Status" class="menu-icon"> Grade Status
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('student.grade-submissions.*') ? 'active' : '' }}">
                            <a href="{{ route('student.grade-submissions.list') }}">
                                <img src="{{ asset('images/mu.png') }}" alt="Grade Submissions" class="menu-icon"> Grade Submissions
                            </a>
                        </li>
                        <li class="{{ request()->routeIs('student.calendar.*') ? 'active' : '' }}">
                            <a href="{{ route('student.calendar.index') }}">
                                <img src="{{ asset('images/calendar.png') }}" alt="Calendar" class="menu-icon"> Calendar
                            </a>
                        </li>
                    </ul>

                    <!-- Mobile Navigation Menu -->
                    <div class="mobile-nav-menu" style="background: white; padding: 0; margin: 0;">

                        <!-- Dashboard -->
                        <div style="border-bottom: 1px solid #eee; margin: 0; padding: 0;" class="{{ request()->routeIs('student.dashboard') ? 'mobile-nav-active' : '' }}">
                            <button onclick="closeMobileMenuAndNavigate('{{ route('student.dashboard') }}')"
                                    style="width: 100%; background: {{ request()->routeIs('student.dashboard') ? '#e3f2fd' : 'white' }}; color: {{ request()->routeIs('student.dashboard') ? '#22bbea' : '#333' }}; padding: 18px 20px; border: none;
                                           text-align: left; cursor: pointer; display: flex; align-items: center;
                                           min-height: 56px; touch-action: manipulation; font-size: 16px; font-weight: {{ request()->routeIs('student.dashboard') ? '500' : 'normal' }};">
                                <img src="{{ asset('images/Dashboard.png') }}" alt="Dashboard" style="width: 24px; height: 24px; margin-right: 12px;">
                                <span>Dashboard</span>
                            </button>
                        </div>

                        <!-- Grade Status -->
                        <div style="border-bottom: 1px solid #eee; margin: 0; padding: 0;" class="{{ request()->routeIs('student.grades') ? 'mobile-nav-active' : '' }}">
                            <button onclick="closeMobileMenuAndNavigate('{{ route('student.grades') }}')"
                                    style="width: 100%; background: {{ request()->routeIs('student.grades') ? '#e3f2fd' : 'white' }}; color: {{ request()->routeIs('student.grades') ? '#22bbea' : '#333' }}; padding: 18px 20px; border: none;
                                           text-align: left; cursor: pointer; display: flex; align-items: center;
                                           min-height: 56px; touch-action: manipulation; font-size: 16px; font-weight: {{ request()->routeIs('student.grades') ? '500' : 'normal' }};">
                                <img src="{{ asset('images/Dashboard.png') }}" alt="Grade Status" style="width: 24px; height: 24px; margin-right: 12px;">
                                <span>Grade Status</span>
                            </button>
                        </div>

                        <!-- Grade Submissions -->
                        <div style="border-bottom: 1px solid #eee; margin: 0; padding: 0;" class="{{ request()->routeIs('student.grade-submissions.*') ? 'mobile-nav-active' : '' }}">
                            <button onclick="closeMobileMenuAndNavigate('{{ route('student.grade-submissions.list') }}')"
                                    style="width: 100%; background: {{ request()->routeIs('student.grade-submissions.*') ? '#e3f2fd' : 'white' }}; color: {{ request()->routeIs('student.grade-submissions.*') ? '#22bbea' : '#333' }}; padding: 18px 20px; border: none;
                                           text-align: left; cursor: pointer; display: flex; align-items: center;
                                           min-height: 56px; touch-action: manipulation; font-size: 16px; font-weight: {{ request()->routeIs('student.grade-submissions.*') ? '500' : 'normal' }};">
                                <img src="{{ asset('images/mu.png') }}" alt="Grade Submissions" style="width: 24px; height: 24px; margin-right: 12px;">
                                <span>Grade Submissions</span>
                            </button>
                        </div>

                        <!-- Calendar -->
                        <div style="border-bottom: 1px solid #eee; margin: 0; padding: 0;" class="{{ request()->routeIs('student.calendar.*') ? 'mobile-nav-active' : '' }}">
                            <button onclick="closeMobileMenuAndNavigate('{{ route('student.calendar.index') }}')"
                                    style="width: 100%; background: {{ request()->routeIs('student.calendar.*') ? '#e3f2fd' : 'white' }}; color: {{ request()->routeIs('student.calendar.*') ? '#22bbea' : '#333' }}; padding: 18px 20px; border: none;
                                           text-align: left; cursor: pointer; display: flex; align-items: center;
                                           min-height: 56px; touch-action: manipulation; font-size: 16px; font-weight: {{ request()->routeIs('student.calendar.*') ? '500' : 'normal' }};">
                                <img src="{{ asset('images/calendar.png') }}" alt="Calendar" style="width: 24px; height: 24px; margin-right: 12px;">
                                <span>Calendar</span>
                            </button>
                        </div>

                    </div>
                @endif
            @endauth
        </aside>

        <main class="content">
            @yield('content')
        </main>
    </div>

    <script>
    function confirmLogout() {
        if (confirm("Are you sure you want to log out?")) {
            document.getElementById('logout-form').submit();
        }
    }

    // Mobile navigation function
    function closeMobileMenuAndNavigate(url) {
        // Close mobile menu
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        if (sidebar) {
            sidebar.classList.remove('active');
        }
        if (sidebarOverlay) {
            sidebarOverlay.classList.remove('active');
        }
        document.body.style.overflow = '';

        // Navigate after short delay
        setTimeout(() => {
            window.location.href = url;
        }, 100);
    }



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

        // Show loader on navigation links (exclude anchors, javascript links, and external links)
        document.querySelectorAll('a:not([href^="#"]):not([href^="javascript:"]):not([target="_blank"]):not([href^="mailto:"]):not([href^="tel:"])').forEach(link => {
            link.addEventListener('click', function(e) {
                // Don't show loader if page hasn't fully loaded yet
                if (!pageFullyLoaded) {
                    return;
                }

                // Don't show loader for dropdown toggles or other non-navigation clicks
                if (!this.getAttribute('onclick') || this.getAttribute('href') !== '#') {
                    // Check if this is a link that might show a confirmation dialog
                    const href = this.getAttribute('href');
                    const onclick = this.getAttribute('onclick');

                    // If it has confirm() in onclick, delay showing loader
                    if (onclick && onclick.includes('confirm(')) {
                        // Don't show loader immediately, let the confirm dialog handle it
                        return;
                    }

                    showLoader();
                }
            });
        });

        // Show loader on button clicks that might navigate (excluding submit buttons)
        document.querySelectorAll('.btn[href], button[onclick*="location"], button[onclick*="window.location"]').forEach(button => {
            button.addEventListener('click', function(e) {
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

        // Hide loader when user navigates back
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

    // Mobile menu functionality
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuToggle = document.getElementById('mobile-menu-toggle');
        const sidebar = document.querySelector('.sidebar');
        const sidebarOverlay = document.getElementById('sidebar-overlay');

        // Toggle mobile menu
        if (mobileMenuToggle) {
            console.log('Mobile menu toggle found:', mobileMenuToggle);

            function toggleMenu() {
                // Simple toggle with forced styling
                const isActive = sidebar.classList.contains('active');

                if (isActive) {
                    // Close menu
                    sidebar.classList.remove('active');
                    sidebarOverlay.classList.remove('active');
                    document.body.style.overflow = '';
                } else {
                    // Open menu
                    sidebar.classList.add('active');
                    sidebarOverlay.classList.add('active');
                    document.body.style.overflow = 'hidden';

                    // Force white background immediately with highest priority
                    sidebar.style.setProperty('background', 'white', 'important');
                    sidebar.style.setProperty('background-color', 'white', 'important');
                    sidebar.style.setProperty('z-index', '10000', 'important');

                    // Force mobile nav menu styling
                    const mobileNavMenu = sidebar.querySelector('.mobile-nav-menu');
                    if (mobileNavMenu) {
                        mobileNavMenu.style.setProperty('background', 'white', 'important');
                        mobileNavMenu.style.setProperty('background-color', 'white', 'important');

                        // Force all buttons to be clickable
                        const mobileNavButtons = mobileNavMenu.querySelectorAll('button');

                        mobileNavButtons.forEach((button, index) => {
                            // Check if this button's parent has the mobile-nav-active class
                            const isActive = button.parentElement.classList.contains('mobile-nav-active');

                            // Force button styling with highest priority, respecting active state
                            const bgColor = isActive ? '#e3f2fd' : 'white';
                            const textColor = isActive ? '#22bbea' : '#333';
                            const fontWeight = isActive ? '500' : 'normal';

                            button.style.setProperty('background', bgColor, 'important');
                            button.style.setProperty('background-color', bgColor, 'important');
                            button.style.setProperty('color', textColor, 'important');
                            button.style.setProperty('font-weight', fontWeight, 'important');
                            button.style.setProperty('pointer-events', 'auto', 'important');
                            button.style.setProperty('z-index', '10001', 'important');
                            button.style.setProperty('cursor', 'pointer', 'important');
                            button.style.setProperty('touch-action', 'manipulation', 'important');
                            button.style.setProperty('position', 'relative', 'important');

                            // Add visual feedback
                            button.addEventListener('mousedown', function() {
                                this.style.setProperty('background-color', '#e3f2fd', 'important');
                            });

                            button.addEventListener('mouseup', function() {
                                const isActive = this.parentElement.classList.contains('mobile-nav-active');
                                const bgColor = isActive ? '#e3f2fd' : 'white';
                                this.style.setProperty('background-color', bgColor, 'important');
                            });

                            button.addEventListener('touchstart', function() {
                                this.style.setProperty('background-color', '#e3f2fd', 'important');
                            });

                            button.addEventListener('touchend', function() {
                                setTimeout(() => {
                                    const isActive = this.parentElement.classList.contains('mobile-nav-active');
                                    const bgColor = isActive ? '#e3f2fd' : 'white';
                                    this.style.setProperty('background-color', bgColor, 'important');
                                }, 200);
                            });
                        });
                    }
                }

            }

            mobileMenuToggle.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleMenu();
            });

            // For touch devices
            mobileMenuToggle.addEventListener('touchstart', function(e) {
                e.preventDefault();
                e.stopPropagation();
            });

            mobileMenuToggle.addEventListener('touchend', function(e) {
                e.preventDefault();
                e.stopPropagation();
                toggleMenu();
            });
        } else {
            console.error('Mobile menu toggle not found!');
        }

        // Close menu when overlay is clicked
        if (sidebarOverlay) {
            function closeMenu() {
                console.log('Closing mobile menu');
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }

            sidebarOverlay.addEventListener('click', closeMenu);
            sidebarOverlay.addEventListener('touchend', function(e) {
                e.preventDefault();
                closeMenu();
            });
        }

        // Simple mobile navigation setup
        console.log('Mobile navigation setup complete');

        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        // Debug profile link click
        const profileLink = document.getElementById('profile-link');
        if (profileLink) {
            console.log('Profile link found:', profileLink.href);
            profileLink.addEventListener('click', function(e) {
                console.log('Profile link clicked');
                console.log('Href:', this.href);
                // e.preventDefault(); // Uncomment to prevent navigation for testing
            });
        } else {
            console.error('Profile link not found!');
        }

        // Prevent zoom on double tap for iOS - but only for non-interactive elements
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function (event) {
            const now = (new Date()).getTime();
            const target = event.target;

            // Don't prevent default for interactive elements
            if (target.tagName === 'BUTTON' ||
                target.tagName === 'A' ||
                target.tagName === 'INPUT' ||
                target.tagName === 'SELECT' ||
                target.tagName === 'TEXTAREA' ||
                target.closest('button') ||
                target.closest('a') ||
                target.closest('.btn') ||
                target.closest('[role="button"]') ||
                target.closest('[onclick]') ||
                target.closest('.menu') ||
                target.closest('.mobile-menu-toggle') ||
                target.closest('.logout-btn')) {
                lastTouchEnd = now;
                return;
            }

            // Only prevent double-tap zoom on non-interactive elements
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);

        // Add debugging for touch events
        console.log('Mobile responsiveness improvements loaded');
        console.log('Screen width:', window.innerWidth);
        console.log('User agent:', navigator.userAgent);
        console.log('Mobile menu toggle element:', document.getElementById('mobile-menu-toggle'));
        console.log('Sidebar element:', document.querySelector('.sidebar'));
        console.log('Sidebar overlay element:', document.getElementById('sidebar-overlay'));

        // Debug sidebar menu links
        const debugMenuLinks = document.querySelectorAll('.menu li a');
        console.log('Menu links found:', debugMenuLinks.length);
        debugMenuLinks.forEach((link, index) => {
            const styles = window.getComputedStyle(link);
            console.log(`Menu link ${index}:`, {
                href: link.href,
                pointerEvents: styles.pointerEvents,
                zIndex: styles.zIndex,
                position: styles.position,
                display: styles.display
            });
        });

        // Test if elements are properly positioned and clickable
        const toggle = document.getElementById('mobile-menu-toggle');
        if (toggle) {
            const rect = toggle.getBoundingClientRect();
            console.log('Mobile toggle position:', {
                top: rect.top,
                left: rect.left,
                width: rect.width,
                height: rect.height,
                visible: rect.width > 0 && rect.height > 0
            });
        }

        // Add debugging for content clicks on mobile
        if (window.innerWidth <= 768) {
            document.addEventListener('click', function(e) {
                console.log('Click detected on mobile:', {
                    target: e.target.tagName,
                    className: e.target.className,
                    id: e.target.id,
                    pointerEvents: window.getComputedStyle(e.target).pointerEvents,
                    zIndex: window.getComputedStyle(e.target).zIndex,
                    position: window.getComputedStyle(e.target).position
                });
            });

            // Add touch debugging
            document.addEventListener('touchstart', function(e) {
                console.log('Touch detected on mobile:', {
                    target: e.target.tagName,
                    className: e.target.className,
                    touches: e.touches.length
                });
            });
        }
    });
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html> 