<x-studentLayout>
    {{-- <script src="https://unpkg.com/feather-icons"></script> --}}
    {{-- Full Width Container Override --}}
    <div class="full-width-dashboard">
        {{-- Top Bar (Title, Date & Logout) --}}
        <div class="relative z-20 flex items-center justify-between w-full px-8 py-4 bg-white shadow-sm">


            {{-- Right: Logout --}}
            <div class="flex items-center gap-6 text-sm font-medium text-gray-600 sm:text-base">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                </form>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex h-[calc(100vh-5rem)] overflow-hidden w-full">

            {{-- Vertical Navigation --}}
            <aside id="sidebar" class="transition-all duration-300 ease-in-out bg-white border-r border-gray-200 w-80">
                {{-- Navigation Toggle Button --}}
                <div class="flex items-center justify-end px-4 py-4 border-b border-gray-200">
                    <button id="sidebar-toggle"
                            class="p-2 text-gray-600 transition-all duration-200 rounded-lg hover:text-blue-600 hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                            title="Toggle Navigation">
                        <svg class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                        </svg>
                    </button>
                </div>

                {{-- Navigation Links --}}
                <nav class="mt-2 space-y-1 text-sm">
                    {{-- Dashboard Link --}}
                    <a href="{{ route('monitor.dashboard') }}" id="dashboard-nav-link"
                    class="relative flex items-center gap-3 px-5 py-3 text-blue-600 transition-all duration-200 nav-item hover:bg-blue-50 hover:text-blue-700 group"
                    title="Dashboard">
                        <img src="{{ asset('assets/dashboard.png') }}" alt="Dashboard"
                            class="flex-shrink-0 w-5 h-5 transition-all duration-200 group-hover:scale-110"
                            style="filter: hue-rotate(25deg) saturate(1.5) brightness(1.1);">
                        <span class="text-base font-semibold transition-opacity duration-200 nav-text">Dashboard</span>
                        <hr class="nav-hr absolute bottom-0 left-0 w-full h-0.5 bg-blue-500 transform scale-x-0 transition-transform duration-200 group-hover:scale-x-100">
                    </a>

                    {{-- Set Schedules Dropdown --}}
                    <div class="relative nav-dropdown-container">
                        <button type="button" id="schedules-dropdown-btn"
                                class="relative flex items-center justify-between w-full px-5 py-3 text-blue-600 transition-all duration-200 nav-item hover:bg-blue-50 hover:text-blue-700 group"
                                title="Set Schedules">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('assets/set-schedule.png') }}" alt="Set Schedules"
                                    class="flex-shrink-0 w-5 h-5 transition-all duration-200 group-hover:scale-110"
                                    style="filter: hue-rotate(25deg) saturate(1.5) brightness(1.1);">
                                <span class="text-base font-semibold transition-opacity duration-200 nav-text">Set Schedules</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200 nav-dropdown-icon" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="schedules-dropdown"
                            class="absolute left-0 right-0 z-50 mt-1 overflow-hidden bg-white border border-gray-200 rounded-md shadow-md nav-dropdown-menu">
                            @foreach([
                                ['route'=>'monitor.schedule.choose','param'=>'Academic','icon'=>'academic.png','label'=>'Academic Schedules'],
                                ['route'=>'monitor.schedule.choose','param'=>'GoingOut','icon'=>'going out.png','label'=>'Leisure Schedules'],
                                ['route'=>'monitor.schedule.choose','param'=>'Irregular','icon'=>'academic irreg.png','label'=>'Academic Irregular Schedules'],
                                ['route'=>'monitor.individual-goingout.students','param'=>null,'icon'=>'individual going out.png','label'=>'Going Home Schedules'],
                                ['route'=>'monitor.unique-leisure.students','param'=>null,'icon'=>'running.png','label'=>'Unique Leisure Schedules'],
                                ['route'=>'monitor.calendar.students','param'=>null,'icon'=>'calendar.png','label'=>'Calendar'],
                            ] as $item)
                                <a href="{{ isset($item['param']) ? route($item['route'], ['type'=>$item['param']]) : route($item['route']) }}"
                                class="relative flex items-center gap-3 px-5 py-2.5 text-blue-600 transition-all duration-200 border-b border-gray-100 nav-dropdown-item hover:bg-blue-50 hover:text-blue-700 group last:border-b-0"
                                onclick="handleDropdownItemClick(event, this, 'schedules-dropdown', 'schedules-dropdown-btn')">
                                    <img src="{{ asset('assets/'.$item['icon']) }}" alt="{{ $item['label'] }}"
                                        class="w-4 h-4 transition-transform duration-200 group-hover:scale-110"
                                        style="filter:hue-rotate(25deg) saturate(1.5) brightness(1.1);">
                                    <span class="text-sm font-medium">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>

                    {{-- Log Entries Dropdown --}}
                    <div class="relative nav-dropdown-container">
                        <button type="button" id="logs-dropdown-btn"
                                class="relative flex items-center justify-between w-full px-5 py-3 text-blue-600 transition-all duration-200 nav-item hover:bg-blue-50 hover:text-blue-700 group"
                                title="Log Entries">
                            <div class="flex items-center gap-3">
                                <img src="{{ asset('assets/log entry.png') }}" alt="Log Entries"
                                    class="flex-shrink-0 w-5 h-5 transition-all duration-200 group-hover:scale-110"
                                    style="filter: hue-rotate(25deg) saturate(1.5) brightness(1.1);">
                                <span class="text-base font-semibold transition-opacity duration-200 nav-text">Log Entries</span>
                            </div>
                            <svg class="w-4 h-4 transition-transform duration-200 nav-dropdown-icon" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div id="logs-dropdown"
                            class="absolute left-0 right-0 z-50 mt-1 overflow-hidden bg-white border border-gray-200 rounded-md shadow-md nav-dropdown-menu">
                            @foreach([
                                ['route'=>'monitor.academic.logs','icon'=>'academic.png','label'=>'Academic Logs'],
                                ['route'=>'monitor.goingout.logs','icon'=>'going out.png','label'=>'Leisure Logs'],
                                ['route'=>'monitor.visitor.logs','icon'=>'visitor.png','label'=>'Visitor Logs'],
                                ['route'=>'monitor.intern.logs','icon'=>'intern_logs.png','label'=>'Intern Logs'],
                                ['route'=>'monitor.goinghome.logs','icon'=>'going_home.png','label'=>'Going Home Logs'],
                            ] as $item)
                                <a href="{{ route($item['route']) }}"
                                class="relative flex items-center gap-3 px-5 py-2.5 text-blue-600 transition-all duration-200 border-b border-gray-100 nav-dropdown-item hover:bg-blue-50 hover:text-blue-700 group last:border-b-0"
                                onclick="handleDropdownItemClick(event, this, 'logs-dropdown', 'logs-dropdown-btn')">
                                    <img src="{{ asset('assets/'.$item['icon']) }}" alt="{{ $item['label'] }}"
                                        class="w-4 h-4 transition-transform duration-200 group-hover:scale-110"
                                        style="filter:hue-rotate(25deg) saturate(1.5) brightness(1.1);">
                                    <span class="text-sm font-medium">{{ $item['label'] }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </nav>
            </aside>

        <main class="flex-1 p-6 overflow-y-auto bg-gray-50">
            {{ $slot }}
        </main>
        </div>
    </div>

    {{-- Full Width Dashboard Styles --}}
    <style>
        /* Icon-Only Mode Styles - WIDTH CHANGES TO HALF */
        #sidebar {
            overflow: hidden;
            transition: width 0.3s ease-in-out;
            /* Original width: w-80 = 320px */
        }

        /* Icon-only mode: Width becomes half (160px) */
        #sidebar.icon-only {
            width: 160px !important;
        }

        /* Hide text and dropdown icons in icon-only mode */
        #sidebar.icon-only .nav-text,
        #sidebar.icon-only .nav-dropdown-icon {
            display: none !important;
        }

        /* Center icons when in icon-only mode */
        #sidebar.icon-only .nav-item {
            justify-content: center !important;
            align-items: center !important;
            padding: 1.5rem 0 !important;
            display: flex !important;
            flex-direction: column !important;
        }

        #sidebar.icon-only .nav-item > div:first-child {
            justify-content: center !important;
            align-items: center !important;
            display: flex !important;
            width: 100% !important;
        }

        /* Ensure icons are perfectly centered */
        #sidebar.icon-only .nav-icon {
            margin: 0 auto !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }

        /* Make icons BIGGER and more prominent in icon-only mode */
        #sidebar.icon-only .nav-icon {
            width: 48px !important;
            height: 48px !important;
            padding: 12px !important;
            border-radius: 12px !important;
            background: #f1f5f9 !important;
            border: 2px solid #e2e8f0 !important;
            transition: all 0.3s ease !important;
        }

        /* Hide dropdown menus when in icon-only mode */
        #sidebar.icon-only .nav-dropdown-menu {
            display: none !important;
        }

        /* Toggle button styling for icon-only mode */
        #sidebar.icon-only #sidebar-toggle {
            background: #f97316 !important;
            color: white !important;
            border-radius: 12px !important;
            width: 48px !important;
            height: 48px !important;
            margin: 8px auto !important;
        }

        #sidebar.icon-only #sidebar-toggle:hover {
            background: #ea580c !important;
            transform: scale(1.1) !important;
        }

        /* Make active navigation icon MORE prominent in icon-only mode */
        #sidebar.icon-only .nav-active .nav-icon,
        #sidebar.icon-only .nav-dropdown-item.nav-active .nav-icon,
        #sidebar.icon-only .dropdown-active .nav-icon {
            color: #ffffff !important;
            background: #3b82f6 !important;
            border: 3px solid #2563eb !important;
            box-shadow: 0 4px 16px rgba(59, 130, 246, 0.4) !important;
            width: 56px !important;
            height: 56px !important;
            padding: 16px !important;
        }

        /* Icon hover effects in icon-only mode - BIGGER on hover */
        #sidebar.icon-only .nav-icon:hover {
            background: #e2e8f0 !important;
            transform: scale(1.15) !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
        }

        /* Keep toggle button on the right in icon-only mode */
        #sidebar.icon-only .flex.items-center.justify-end {
            justify-content: flex-end !important;
            padding: 1rem !important;
        }

        /* Override any conflicting navigation item styles in icon-only mode */
        #sidebar.icon-only .nav-item {
            text-align: center !important;
        }

        #sidebar.icon-only .nav-item * {
            margin-left: auto !important;
            margin-right: auto !important;
        }

        /* Tooltip styles for collapsed state */
        #sidebar.collapsed .nav-item {
            position: relative;
        }

        #sidebar.collapsed .nav-item:hover::after {
            content: attr(title);
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.9);
            color: white;
            padding: 8px 12px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            white-space: nowrap;
            z-index: 1000;
            margin-left: 10px;
            opacity: 0;
            animation: tooltipFadeIn 0.2s ease-out forwards;
        }

        #sidebar.collapsed .nav-item:hover::before {
            content: '';
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            border: 6px solid transparent;
            border-right-color: rgba(0, 0, 0, 0.9);
            z-index: 1000;
            margin-left: 4px;
            opacity: 0;
            animation: tooltipFadeIn 0.2s ease-out forwards;
        }

        @keyframes tooltipFadeIn {
            from {
                opacity: 0;
                transform: translateY(-50%) translateX(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(-50%) translateX(0);
            }
        }

        /* Small light gray scrollbars for all elements */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f8f9fa;
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
            transition: background 0.2s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9ca3af;
        }

        ::-webkit-scrollbar-corner {
            background: #f8f9fa;
        }

        /* Firefox scrollbar styling */
        * {
            scrollbar-width: thin;
            scrollbar-color: #d1d5db #f8f9fa;
        }

        /* Override parent layout constraints for full width dashboard */
        .full-width-dashboard {
            width: 100vw;
            max-width: none;
            margin-left: calc(-50vw + 50%);
            margin-right: calc(-50vw + 50%);
            position: relative;
        }

        /* Professional Navigation Styling */
        .nav-item {
            position: relative;
            margin: 0;
            border: none !important;
            border-radius: 0 !important;
            background: transparent !important;
            box-shadow: none !important;
            width: 100%;
            margin-left: 0;
            margin-right: 0;
        }

        .nav-item:hover {
            background: #eff6ff !important;
            transform: none !important;
            box-shadow: none !important;
        }

        .nav-item .nav-hr {
            border: none;
            background: #3b82f6;
            height: 2px;
            margin: 0;
        }

        .nav-item:hover .nav-hr {
            transform: scaleX(1);
        }

        /* Active navigation state */
        .nav-active {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            border: none !important;
            box-shadow: none !important;
            transform: none !important;
        }

        .nav-active .font-semibold,
        .nav-active .font-medium {
            color: #1d4ed8 !important;
            font-weight: 600 !important;
        }

        .nav-active svg {
            color: #1d4ed8 !important;
        }

        .nav-active .nav-hr {
            transform: scaleX(1) !important;
            background: #1d4ed8 !important;
        }

        /* Navigation Dropdown Styles */
        .nav-dropdown-container {
            position: relative;
        }

        .nav-dropdown-menu {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.3s ease;
            max-height: 60vh;        /* allow taller dropdowns */
            overflow-y: auto;       /* enable scrolling when items exceed height */
            -webkit-overflow-scrolling: touch;
            pointer-events: none;
        }

        .nav-dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            max-height: 60vh;       /* matching visible state */
            pointer-events: auto;
        }

        .nav-dropdown-icon {
            transition: transform 0.3s ease;
        }

        .nav-dropdown-icon.rotated {
            transform: rotate(180deg);
        }

        /* Navigation dropdown item hover effects */
        .nav-dropdown-menu a:hover {
            transform: none;
        }

        /* Active dropdown item styling */
        .nav-dropdown-item.nav-active {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            box-shadow: none !important;
        }

        .nav-dropdown-item.nav-active .font-medium,
        .nav-dropdown-item.nav-active svg {
            color: #1d4ed8 !important;
        }

        .nav-dropdown-item.nav-active .nav-hr {
            transform: scaleX(1) !important;
            background: #1d4ed8 !important;
        }

        /* Keep dropdown open when item is active */
        .nav-dropdown-container.keep-open .nav-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
            max-height: 300px;
            pointer-events: auto;
        }

        .nav-dropdown-container.keep-open .nav-dropdown-icon {
            transform: rotate(180deg);
        }

        /* Ensure manual close overrides keep-open */
        .nav-dropdown-menu:not(.show) {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            max-height: 0;
            pointer-events: none;
        }

        /* Remove active styling from dropdown button when item is selected */
        .dropdown-active {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            border: none !important;
            box-shadow: none !important;
        }

        .dropdown-active .font-semibold,
        .dropdown-active svg {
            color: #1d4ed8 !important;
        }

        .dropdown-active .nav-hr {
            transform: scaleX(1) !important;
            background: #1d4ed8 !important;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeSidebar();
            initializeDropdowns();
            setActiveNavigation();
        });

        function initializeSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebar-toggle');

            if (!sidebar || !toggleBtn) {
                console.error('Sidebar elements not found');
                return;
            }

            // Load saved sidebar state from localStorage
            const isIconOnly = localStorage.getItem('monitor-sidebar-icon-only') === 'true';
            if (isIconOnly) {
                sidebar.classList.add('icon-only');
                updateToggleButtonState(true);
            } else {
                updateToggleButtonState(false);
            }

            // Toggle sidebar on button click
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                sidebar.classList.toggle('icon-only');
                const iconOnly = sidebar.classList.contains('icon-only');

                // Save state to localStorage
                localStorage.setItem('monitor-sidebar-icon-only', iconOnly);

                // Update toggle button appearance
                updateToggleButtonState(iconOnly);

                // Close any open dropdowns when switching to icon-only
                if (iconOnly) {
                    const dropdowns = document.querySelectorAll('.nav-dropdown-menu');
                    const buttons = document.querySelectorAll('[id$="-dropdown-btn"]');

                    dropdowns.forEach(dropdown => dropdown.classList.remove('show'));
                    buttons.forEach(button => {
                        const icon = button.querySelector('.nav-dropdown-icon');
                        if (icon) icon.classList.remove('rotated');
                    });
                }

                console.log('Sidebar toggled:', iconOnly ? 'icon-only' : 'full-text');
            });
        }

        function updateToggleButtonState(iconOnly) {
            const toggleBtn = document.getElementById('sidebar-toggle');
            const toggleIcon = toggleBtn.querySelector('svg');

            if (iconOnly) {
                // When icon-only, show text icon to indicate "show text"
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>';
                toggleBtn.title = 'Show Text Labels';
            } else {
                // When full-text, show grid icon to indicate "show icons only"
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>';
                toggleBtn.title = 'Show Icons Only';
            }
        }

        function initializeDropdowns() {
            // Initialize dropdown functionality with namespace to avoid conflicts
            const schedulesBtn = document.getElementById('schedules-dropdown-btn');
            const schedulesDropdown = document.getElementById('schedules-dropdown');
            const logsBtn = document.getElementById('logs-dropdown-btn');
            const logsDropdown = document.getElementById('logs-dropdown');

            // Check if elements exist
            if (!schedulesBtn || !schedulesDropdown || !logsBtn || !logsDropdown) {
                console.error('Navigation dropdown elements not found');
                return;
            }

            // Toggle schedules dropdown with isolated event handling
            schedulesBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                console.log('Navigation schedules dropdown clicked');
                toggleNavDropdown(schedulesDropdown, schedulesBtn);
                closeNavDropdown(logsDropdown, logsBtn);
            });

            // Toggle logs dropdown with isolated event handling
            logsBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                console.log('Navigation logs dropdown clicked');
                toggleNavDropdown(logsDropdown, logsBtn);
                closeNavDropdown(schedulesDropdown, schedulesBtn);
            });

            // Close navigation dropdowns when clicking outside (but only in sidebar area)
            const sidebar = document.querySelector('aside');
            if (sidebar) {
                sidebar.addEventListener('click', function(e) {
                    // Only handle clicks within the sidebar
                    if (!schedulesBtn.contains(e.target) && !schedulesDropdown.contains(e.target) &&
                        !logsBtn.contains(e.target) && !logsDropdown.contains(e.target)) {
                        closeNavDropdown(schedulesDropdown, schedulesBtn);
                        closeNavDropdown(logsDropdown, logsBtn);
                    }
                });
            }

            // Also close when clicking in main content area
            const mainContent = document.querySelector('main');
            if (mainContent) {
                mainContent.addEventListener('click', function(e) {
                    closeNavDropdown(schedulesDropdown, schedulesBtn);
                    closeNavDropdown(logsDropdown, logsBtn);
                });
            }
        }

        function toggleNavDropdown(dropdown, button) {
            const icon = button.querySelector('.nav-dropdown-icon');
            const container = button.closest('.nav-dropdown-container');

            // Check if dropdown is visible (either through 'show' class or 'keep-open' class)
            const isVisible = dropdown.classList.contains('show') || container.classList.contains('keep-open');

            if (isVisible) {
                console.log('Closing navigation dropdown');
                closeNavDropdown(dropdown, button);
                // Also remove keep-open class to allow normal toggle behavior
                container.classList.remove('keep-open');
            } else {
                console.log('Opening navigation dropdown');
                openNavDropdown(dropdown, button);
            }
        }

        function openNavDropdown(dropdown, button) {
            const icon = button.querySelector('.nav-dropdown-icon');
            dropdown.classList.add('show');
            if (icon) {
                icon.classList.add('rotated');
            }
            console.log('Navigation dropdown opened, classes:', dropdown.className);
        }

        function closeNavDropdown(dropdown, button) {
            const icon = button.querySelector('.nav-dropdown-icon');
            dropdown.classList.remove('show');
            if (icon) {
                icon.classList.remove('rotated');
            }
            console.log('Navigation dropdown closed, classes:', dropdown.className);
        }

        function handleDropdownItemClick(event, clickedItem, dropdownId, buttonId) {
            // Don't prevent default - let the navigation happen
            // Don't close the dropdown - keep it open

            console.log('Dropdown item clicked:', clickedItem.id);

            // Keep the dropdown open by adding a class to the container
            const container = clickedItem.closest('.nav-dropdown-container');
            if (container) {
                container.classList.add('keep-open');
            }

            // Remove active class from dropdown button since item is now active
            const button = document.getElementById(buttonId);
            if (button) {
                button.classList.remove('dropdown-active');
            }
        }

        function setActiveNavigation() {
            const currentPath = window.location.pathname;
            const currentUrl = window.location.href;

            // Enhanced navigation mapping with comprehensive path detection
            const navLinks = {
                'dashboard-nav-link': ['/monitor/dashboard'],
                'academic-nav-link': [
                    '/monitor/schedule/choose/Academic',
                    '/monitor/schedule/Academic',
                    '/monitor/schedule?type=Academic',
                    'type=Academic'
                ],
                'goingout-nav-link': [
                    '/monitor/schedule/choose/GoingOut',
                    '/monitor/schedule/GoingOut',
                    '/monitor/schedule?type=GoingOut',
                    'type=GoingOut'
                ],
                'irregular-nav-link': [
                    '/monitor/schedule/choose/Irregular',
                    '/monitor/schedule/Irregular',
                    '/monitor/irregular-schedule',
                    '/monitor/schedule?type=Irregular',
                    'type=Irregular'
                ],
                'individual-goingout-nav': [
                    '/monitor/individual-goingout/students',
                    '/monitor/individual-goingout'
                ],
                'unique-leisure-nav': [
                    '/monitor/unique-leisure/students',
                    '/monitor/unique-leisure'
                ],
                'calendar-nav': [
                    '/monitor/calendar/students',
                    '/monitor/calendar'
                ],

                'academic-logs-nav-link': [
                    '/monitor/academic/logs',
                    '/monitor/academic-logs'
                ],
                'goingout-logs-nav-link': [
                    '/monitor/goingout/logs',
                    '/monitor/goingout-logs'
                ],
                'visitor-logs-nav-link': [
                    '/monitor/visitor/logs',
                    '/monitor/visitor-logs'
                ]
            };

            // Remove active class from all links and dropdown buttons
            Object.keys(navLinks).forEach(linkId => {
                const link = document.getElementById(linkId);
                if (link) {
                    link.classList.remove('nav-active');
                }
            });

            // Remove active class from dropdown buttons
            document.getElementById('schedules-dropdown-btn')?.classList.remove('dropdown-active');
            document.getElementById('logs-dropdown-btn')?.classList.remove('dropdown-active');

            // Remove keep-open class from all dropdown containers
            document.querySelectorAll('.nav-dropdown-container').forEach(container => {
                container.classList.remove('keep-open');
            });

            // Check for active links and set dropdown states
            let schedulesActive = false;
            let logsActive = false;

            Object.entries(navLinks).forEach(([linkId, paths]) => {
                const isActive = paths.some(path => {
                    return currentPath.includes(path) || currentUrl.includes(path);
                });

                if (isActive) {
                    const link = document.getElementById(linkId);
                    if (link) {
                        link.classList.add('nav-active');

                        // Check if this link belongs to schedules or logs dropdown
                        if (['academic-nav-link', 'goingout-nav-link', 'irregular-nav-link', 'individual-goingout-nav', 'unique-leisure-nav', 'calendar-nav'].includes(linkId)) {
                            schedulesActive = true;
                            // Keep schedules dropdown open and don't activate button
                            const schedulesContainer = document.getElementById('schedules-dropdown-btn')?.closest('.nav-dropdown-container');
                            if (schedulesContainer) {
                                schedulesContainer.classList.add('keep-open');
                            }
                        } else if (['academic-logs-nav-link', 'goingout-logs-nav-link', 'visitor-logs-nav-link'].includes(linkId)) {
                            logsActive = true;
                            // Keep logs dropdown open and don't activate button
                            const logsContainer = document.getElementById('logs-dropdown-btn')?.closest('.nav-dropdown-container');
                            if (logsContainer) {
                                logsContainer.classList.add('keep-open');
                            }
                        }
                    }
                }
            });
        }
    </script>
</x-studentLayout>
