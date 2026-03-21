<x-studentLayout>

    {{-- Full Width Container Override --}}
    <div class="full-width-dashboard">
        {{-- Top Bar (Title, Date & Logout) --}}
        <div class="relative z-20 flex items-center justify-between w-full px-8 py-4 bg-white shadow-sm">


            {{-- Right: Date and Logout --}}
            <div class="flex items-center gap-6 text-sm font-medium text-gray-600 sm:text-base">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                </form>
            </div>
        </div>

        {{-- Main Content --}}
        <div class="flex h-[calc(100vh-5rem)] overflow-hidden w-full">

        {{-- Vertical Navigation --}}
        <aside id="sidebar" class="bg-white border-r border-gray-200 w-72 transition-all duration-300 ease-in-out">
            {{-- Navigation Toggle Button --}}
            <div class="flex items-center justify-end px-4 py-3 border-b border-gray-200">
                <button id="sidebar-toggle"
                        class="p-2 text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                        title="Toggle Navigation">
                    <svg class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
            </div>

            {{-- Navigation Links --}}
            <nav class="mt-4 px-2">
                <a href="{{ route('educator.dashboard') }}" id="dashboard-nav-link"
                   class="nav-item flex items-center justify-between px-6 py-5 text-blue-600 transition-all duration-300 hover:bg-blue-50 hover:text-blue-700 group relative"
                   title="Dashboard">
                    <div class="flex items-center gap-4">
                        <svg class="nav-icon w-5 h-5 transition-all duration-300 group-hover:scale-110 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"/>
                        </svg>
                        <span class="nav-text text-sm transition-opacity duration-300">Dashboard</span>
                    </div>
                    <hr class="nav-hr absolute bottom-0 left-0 w-full h-0.5 bg-blue-500 transform scale-x-0 transition-transform duration-300 group-hover:scale-x-100">
                </a>

                <a href="{{ route('academic.monitor') }}" id="academic-nav-link"
                   class="nav-item flex items-center justify-between px-6 py-5 text-blue-600 transition-all duration-300 hover:bg-blue-50 hover:text-blue-700 group relative"
                   title="Academic Logs">
                    <div class="flex items-center gap-4">
                        <svg class="nav-icon w-5 h-5 transition-all duration-300 group-hover:scale-110 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="nav-text text-sm transition-opacity duration-300">Academic Logs</span>
                    </div>
                    <div class="nav-badges flex items-center gap-2 transition-opacity duration-300">
                        <div id="academic-timeout-badge"
                             class="notification-badge items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-full shadow-sm border border-orange-400/20"
                             title="Students with Time Out"
                             style="display: none;">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span>0</span>
                        </div>
                        <div id="academic-timein-badge"
                             class="notification-badge items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-full shadow-sm border border-blue-400/20"
                             title="Students with Time In"
                             style="display: none;">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>0</span>
                        </div>
                    </div>
                    <hr class="nav-hr absolute bottom-0 left-0 w-full h-0.5 bg-blue-500 transform scale-x-0 transition-transform duration-300 group-hover:scale-x-100">
                </a>

                <a href="{{ route('goingout.monitor') }}" id="goingout-nav-link"
                   class="nav-item flex items-center justify-between px-6 py-5 text-blue-600 transition-all duration-300 hover:bg-blue-50 hover:text-blue-700 group relative"
                   title="Going Out Logs">
                    <div class="flex items-center gap-4">
                        <svg class="nav-icon w-5 h-5 transition-all duration-300 group-hover:scale-110 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        <span class="nav-text text-sm transition-opacity duration-300">Going Out Logs</span>
                    </div>
                    <div class="nav-badges flex items-center gap-2 transition-opacity duration-300">
                        <div id="goingout-timeout-badge"
                             class="notification-badge items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-full shadow-sm border border-orange-400/20"
                             title="Students with Time Out"
                             style="display: none;">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span>0</span>
                        </div>
                        <div id="goingout-timein-badge"
                             class="notification-badge items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-full shadow-sm border border-blue-400/20"
                             title="Students with Time In"
                             style="display: none;">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>0</span>
                        </div>
                    </div>
                    <hr class="nav-hr absolute bottom-0 left-0 w-full h-0.5 bg-blue-500 transform scale-x-0 transition-transform duration-300 group-hover:scale-x-100">
                </a>

                <a href="{{ route('visitor.monitor') }}" id="visitor-nav-link"
                   class="nav-item flex items-center justify-between px-6 py-5 text-blue-600 transition-all duration-300 hover:bg-blue-50 hover:text-blue-700 group relative"
                   title="Visitors Log">
                    <div class="flex items-center gap-4">
                        <svg class="nav-icon w-5 h-5 transition-all duration-300 group-hover:scale-110 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span class="nav-text text-sm transition-opacity duration-300">Visitors Log</span>
                    </div>
                    <div class="nav-badges flex items-center gap-2 transition-opacity duration-300">
                        <div id="visitor-timeout-badge"
                             class="notification-badge items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-gradient-to-r from-orange-500 to-orange-600 rounded-full shadow-sm border border-orange-400/20"
                             title="Visitors with Time Out"
                             style="display: none;">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                            </svg>
                            <span>0</span>
                        </div>
                        <div id="visitor-timein-badge"
                             class="notification-badge items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-gradient-to-r from-blue-500 to-blue-600 rounded-full shadow-sm border border-blue-400/20"
                             title="Visitors with Time In"
                             style="display: none;">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span>0</span>
                        </div>
                    </div>
                    <hr class="nav-hr absolute bottom-0 left-0 w-full h-0.5 bg-blue-500 transform scale-x-0 transition-transform duration-300 group-hover:scale-x-100">
                </a>

                <a href="{{ route('educator.late-analytics') }}" id="late-nav-link"
                   class="nav-item flex items-center justify-between px-6 py-5 text-blue-600 transition-all duration-300 hover:bg-blue-50 hover:text-blue-700 group relative"
                   title="Reports">
                    <div class="flex items-center gap-4">
                        <svg class="nav-icon w-5 h-5 transition-all duration-300 group-hover:scale-110 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="nav-text text-sm transition-opacity duration-300">Reports</span>
                    </div>
                    <div class="nav-badges flex items-center gap-2 transition-opacity duration-300">
                        <div id="late-badge"
                             class="notification-badge items-center gap-1 px-2.5 py-1 text-xs font-semibold text-white bg-gradient-to-r from-red-500 to-red-600 rounded-full shadow-sm border border-red-400/20"
                             title="Late Students"
                             style="display: none;">
                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            <span>0</span>
                        </div>
                    </div>
                    <hr class="nav-hr absolute bottom-0 left-0 w-full h-0.5 bg-blue-500 transform scale-x-0 transition-transform duration-300 group-hover:scale-x-100">
                </a>
            </nav>
        </aside>

        {{-- Main Content Area --}}
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
            position: relative;
            transition: width 0.3s ease-in-out;
            /* Original width: w-80 = 320px */
        }



        /* Icon-only mode: Width becomes half (160px) */
        #sidebar.icon-only {
            width: 160px !important;
        }

        /* Hide text and badges in icon-only mode */
        #sidebar.icon-only .nav-text,
        #sidebar.icon-only .nav-badges {
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
        #sidebar.icon-only .nav-active .nav-icon {
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

        /* Force hidden badges to stay hidden */
        .notification-badge.force-hidden {
            display: none !important;
            visibility: hidden !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* Tooltip styles for icon-only mode */
        #sidebar.icon-only .nav-item {
            position: relative;
        }

        #sidebar.icon-only .nav-item:hover::after {
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

        #sidebar.icon-only .nav-item:hover::before {
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

        /* Professional badge styles */
        .notification-badge {
            display: flex;
            transition: all 0.3s ease;
            backdrop-filter: blur(4px);
            animation: badgeAppear 0.3s ease-out;
        }

        .notification-badge:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        @keyframes badgeAppear {
            0% {
                opacity: 0;
                transform: scale(0.8);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }

        .notification-badge svg {
            transition: transform 0.2s ease;
        }

        .notification-badge:hover svg {
            transform: rotate(5deg);
        }

        /* Enhanced gradient backgrounds */
        #academic-timeout-badge, #goingout-timeout-badge {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 50%, #dc2626 100%);
            box-shadow: 0 2px 8px rgba(249, 115, 22, 0.3);
        }


        #academic-timein-badge, #goingout-timein-badge, #visitor-timein-badge {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
        }

        #late-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 50%, #b91c1c 100%);
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }

        /* Active navigation state */
        .nav-active {
            background: #eff6ff !important;
            color: #1d4ed8 !important;
            border: none !important;
            box-shadow: none !important;
            transform: none !important;
        }

        .nav-active .font-semibold {
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
    </style>

    {{-- Notification JavaScript --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize collapsible sidebar
            initializeSidebar();

            // Initialize notification system
            updateNotifications();
            setInterval(updateNotifications, 10000); // Update every 10 seconds

            // Add click handlers for navigation links
            setupNavigationClickHandlers();

            // Set active navigation state
            setActiveNavigation();
        });

        function cleanupOldResetStates() {
            console.log('🧹 Cleaning up old reset states...');

            const today = new Date().toDateString();
            const types = ['academic', 'goingout', 'visitor', 'late'];

            types.forEach(type => {
                const resetKey = `educator-${type}-notifications-reset`;
                const timestampKey = `${resetKey}-timestamp`;
                const resetTimestamp = localStorage.getItem(timestampKey);

                if (resetTimestamp) {
                    const resetDate = new Date(parseInt(resetTimestamp)).toDateString();

                    if (resetDate !== today) {
                        console.log(`🗑️ Removing old reset state for ${type} (was reset on ${resetDate})`);
                        localStorage.removeItem(resetKey);
                        localStorage.removeItem(timestampKey);
                    }
                }
            });

            console.log('✅ Old reset states cleaned up');
        }

        function checkAndResetBadgesForCurrentPage() {
            const currentPath = window.location.pathname;
            const currentUrl = window.location.href;

            console.log('🔍 Checking current page for badge reset:', currentPath);

            // Define page patterns and their corresponding badge types
            const pagePatterns = {
                'academic': ['/educator/academic-monitor', '/educator/academic', 'academic-monitor'],
                'goingout': ['/educator/goingout-monitor', '/educator/goingout', 'goingout-monitor'],
                'visitor': ['/educator/visitor-monitor', '/educator/visitor', 'visitor-monitor'],
                'late': ['/educator/late-analytics', '/educator/analytics', 'late-analytics']
            };

            // Check if current page matches any pattern and reset corresponding badges
            Object.entries(pagePatterns).forEach(([type, patterns]) => {
                const isOnPage = patterns.some(pattern => {
                    return currentPath.includes(pattern) || currentUrl.includes(pattern);
                });

                if (isOnPage) {
                    console.log(`🎯 User is on ${type} page - resetting badges automatically`);
                    resetBadgesForPageType(type);
                }
            });
        }

        function resetBadgesForPageType(type) {
            console.log('🔄 Automatically resetting badges for page type:', type);

            // Store reset state immediately to prevent badges from reappearing
            const resetKey = `educator-${type}-notifications-reset`;
            localStorage.setItem(resetKey, 'true');
            localStorage.setItem(`${resetKey}-timestamp`, Date.now().toString());
            console.log('💾 Stored reset state for:', type);

            if (type === 'late') {
                // Reset late badge
                const lateBadge = document.getElementById('late-badge');
                if (lateBadge) {
                    console.log('👻 Resetting late badge');
                    animateAndHideBadge(lateBadge);
                }
            } else {
                // Reset timeout and timein badges for the specific type
                const timeoutBadge = document.getElementById(`${type}-timeout-badge`);
                const timeinBadge = document.getElementById(`${type}-timein-badge`);

                if (timeoutBadge) {
                    console.log(`👻 Resetting ${type} timeout badge`);
                    animateAndHideBadge(timeoutBadge);
                }

                if (timeinBadge) {
                    console.log(`👻 Resetting ${type} timein badge`);
                    animateAndHideBadge(timeinBadge);
                }
            }

            // Mark as viewed on server side
            markNotificationsAsViewed(type);
        }

        function animateAndHideBadge(badge) {
            // Add smooth fade out animation
            badge.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            badge.style.opacity = '0';
            badge.style.transform = 'scale(0.8)';

            // Hide the badge and reset count after animation
            setTimeout(() => {
                forceHideBadge(badge);
                console.log('✅ Badge automatically reset and hidden:', badge.id);
            }, 500);
        }

        function forceHideBadge(badge) {
            // Force hide with multiple CSS properties to ensure it stays hidden
            badge.style.display = 'none';
            badge.style.visibility = 'hidden';
            badge.style.opacity = '0';
            badge.classList.add('force-hidden');

            const countSpan = badge.querySelector('span');
            if (countSpan) {
                countSpan.textContent = '0';
            }

            console.log('🔒 Badge force hidden with multiple CSS properties:', badge.id);
        }

        // Test function for debugging
        function testCollapse() {
            console.log('🧪 TEST: Manual icon-only toggle triggered');
            const sidebar = document.getElementById('sidebar');
            if (sidebar) {
                sidebar.classList.toggle('icon-only');
                const iconOnly = sidebar.classList.contains('icon-only');
                console.log('🧪 TEST: Sidebar manually toggled to:', iconOnly ? 'icon-only' : 'full-text');
                console.log('🧪 TEST: Current classes:', sidebar.className);
                updateToggleButtonState(iconOnly);
            } else {
                console.error('🧪 TEST: Sidebar not found!');
            }
        }

        function initializeSidebar() {
            console.log('🔧 Initializing sidebar...');

            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebar-toggle');

            console.log('🔍 Sidebar element:', sidebar);
            console.log('🔍 Toggle button element:', toggleBtn);

            if (!sidebar || !toggleBtn) {
                console.error('❌ Sidebar elements not found!');
                console.log('Available elements with IDs:', document.querySelectorAll('[id]'));
                return;
            }

            console.log('✅ Sidebar elements found successfully');

            // Load saved sidebar state from localStorage
            const isIconOnly = localStorage.getItem('educator-sidebar-icon-only') === 'true';
            console.log('💾 Saved icon-only state:', isIconOnly);

            if (isIconOnly) {
                sidebar.classList.add('icon-only');
                updateToggleButtonState(true);
                console.log('🔄 Applied icon-only state from localStorage');
            } else {
                updateToggleButtonState(false);
                console.log('🔄 Applied full text state');
            }

            // Toggle sidebar on button click
            toggleBtn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                console.log('🖱️ Toggle button clicked!');

                sidebar.classList.toggle('icon-only');
                const iconOnly = sidebar.classList.contains('icon-only');

                console.log('🔄 Sidebar state changed to:', iconOnly ? 'icon-only' : 'full-text');
                console.log('📋 Current sidebar classes:', sidebar.className);

                // Save state to localStorage
                localStorage.setItem('educator-sidebar-icon-only', iconOnly);

                // Update toggle button appearance
                updateToggleButtonState(iconOnly);

                console.log('✅ Sidebar toggled successfully:', iconOnly ? 'icon-only' : 'full-text');
            });

            console.log('✅ Sidebar initialization complete');
        }

        function updateToggleButtonState(iconOnly) {
            console.log('🎨 Updating toggle button state, icon-only:', iconOnly);

            const toggleBtn = document.getElementById('sidebar-toggle');
            const toggleIcon = toggleBtn.querySelector('svg');

            console.log('🔍 Toggle button:', toggleBtn);
            console.log('🔍 Toggle icon:', toggleIcon);

            if (iconOnly) {
                console.log('🔄 Setting icon-only state (orange button, text icon)');
                // When icon-only, show text icon to indicate "show text"
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>';
                toggleBtn.title = 'Show Text Labels';
                console.log('✅ Applied icon-only button styles');
            } else {
                console.log('🔄 Setting full-text state (normal button, icon-only icon)');
                // When full-text, show grid icon to indicate "show icons only"
                toggleIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>';
                toggleBtn.title = 'Show Icons Only';
                console.log('✅ Applied full-text button styles');
            }

            console.log('✅ Toggle button state updated successfully');
        }

        function initializeNotifications() {
            console.log('🚀 Starting notification system initialization...');

            // Load notification counts
            updateNotifications();

            // Set up auto-refresh every 15 seconds
            setInterval(updateNotifications, 15000);

            console.log('✅ Notification system initialized - auto-refresh every 15 seconds');
        }

        function enforceResetStates() {
            console.log('🔒 Enforcing reset states for all badge types...');

            const today = new Date().toDateString();
            const types = ['academic', 'goingout', 'visitor', 'late'];

            types.forEach(type => {
                const resetKey = `educator-${type}-notifications-reset`;
                const isReset = localStorage.getItem(resetKey);
                const resetTimestamp = localStorage.getItem(`${resetKey}-timestamp`);
                const resetDate = resetTimestamp ? new Date(parseInt(resetTimestamp)).toDateString() : null;
                const isResetToday = resetDate === today;

                if (isReset === 'true' && isResetToday) {
                    console.log(`🔒 Enforcing hidden state for ${type} badges`);

                    if (type === 'late') {
                        const lateBadge = document.getElementById('late-badge');
                        if (lateBadge) forceHideBadge(lateBadge);
                    } else {
                        const timeoutBadge = document.getElementById(`${type}-timeout-badge`);
                        const timeinBadge = document.getElementById(`${type}-timein-badge`);
                        if (timeoutBadge) forceHideBadge(timeoutBadge);
                        if (timeinBadge) forceHideBadge(timeinBadge);
                    }
                }
            });

            console.log('✅ Reset states enforced');
        }

        function updateNotifications() {
            console.log('🔄 Updating notification badges...');
            fetch('{{ route("educator.notifications.counts") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log('📊 Notification data received:', data);

                        updateNotificationBadges('academic', data.academic);
                        updateNotificationBadges('goingout', data.goingout);
                        updateNotificationBadges('visitor', data.visitor);
                        updateLateNotificationBadge(data.late);

                        console.log('✅ All notification badges updated');
                    }
                })
                .catch(error => {
                    console.error('❌ Error fetching notifications:', error);
                });
        }

        function checkForNewNotifications(newData) {
            // Store previous counts to detect new notifications
            const previousCounts = JSON.parse(localStorage.getItem('educator-previous-notification-counts') || '{}');

            console.log('🔍 Checking for new notifications:', {
                previous: previousCounts,
                current: newData
            });

            // Check each notification type for increases
            const types = ['academic', 'goingout', 'visitor'];
            types.forEach(type => {
                const prevTimeout = previousCounts[type]?.timeout || 0;
                const prevTimein = previousCounts[type]?.timein || 0;
                const newTimeout = newData[type]?.timeout || 0;
                const newTimein = newData[type]?.timein || 0;

                if (newTimeout > prevTimeout) {
                    console.log(`⚡ NEW ${type} timeout notification! ${prevTimeout} → ${newTimeout}`);
                    showNewNotificationAlert(type, 'timeout', newTimeout - prevTimeout);
                }

                if (newTimein > prevTimein) {
                    console.log(`⚡ NEW ${type} timein notification! ${prevTimein} → ${newTimein}`);
                    showNewNotificationAlert(type, 'timein', newTimein - prevTimein);
                }
            });

            // Check late notifications
            const prevLate = previousCounts.late?.count || 0;
            const newLate = newData.late?.count || 0;

            if (newLate > prevLate) {
                console.log(`⚡ NEW late notification! ${prevLate} → ${newLate}`);
                showNewNotificationAlert('late', 'late', newLate - prevLate);
            }

            // Store current counts for next comparison
            localStorage.setItem('educator-previous-notification-counts', JSON.stringify(newData));
        }

        function showNewNotificationAlert(type, subtype, count) {
            console.log(`🔔 Showing new notification alert: ${type} ${subtype} (+${count})`);

            // Create a temporary notification popup
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-pulse';
            notification.innerHTML = `
                <div class="flex items-center gap-2">
                    <span class="text-lg">🔔</span>
                    <span>New ${type} ${subtype} activity (+${count})</span>
                </div>
            `;

            document.body.appendChild(notification);

            // Remove notification after 3 seconds
            setTimeout(() => {
                notification.remove();
            }, 3000);
        }

        function updateNotificationBadges(type, counts) {
            console.log(`🔄 Updating ${type} badges:`, counts);

            // Check if badges for this type have been reset/viewed
            const resetKey = `educator-${type}-notifications-reset`;
            const isReset = localStorage.getItem(resetKey);
            const resetTimestamp = localStorage.getItem(`${resetKey}-timestamp`);

            console.log(`🔍 Reset state check for ${type}:`, {
                resetKey,
                isReset,
                resetTimestamp,
                localStorage: localStorage.getItem(resetKey)
            });

            // Check if reset happened today (reset at midnight)
            const today = new Date().toDateString();
            const resetDate = resetTimestamp ? new Date(parseInt(resetTimestamp)).toDateString() : null;
            const isResetToday = resetDate === today;

            console.log(`📅 Date check for ${type}:`, {
                today,
                resetDate,
                isResetToday,
                shouldHide: isReset === 'true' && isResetToday
            });

            if (isReset === 'true' && isResetToday) {
                console.log(`� ${type} badges were reset today - checking for new activities since reset`);

                // Get the reset timestamp to compare with new activities
                const resetTime = parseInt(resetTimestamp);
                const resetCountsKey = `educator-${type}-reset-counts`;
                const resetCounts = JSON.parse(localStorage.getItem(resetCountsKey) || '{"timeout": 0, "timein": 0}');

                console.log(`📊 Reset counts for ${type}:`, resetCounts);
                console.log(`📊 Current counts for ${type}:`, counts);

                // Only show badges if there are NEW activities since the reset
                const newTimeoutCount = Math.max(0, counts.timeout - resetCounts.timeout);
                const newTimeinCount = Math.max(0, counts.timein - resetCounts.timein);

                console.log(`🆕 New activities since reset for ${type}:`, {
                    timeout: newTimeoutCount,
                    timein: newTimeinCount
                });

                // Update badges with only the new counts
                const timeoutBadge = document.getElementById(type + '-timeout-badge');
                const timeinBadge = document.getElementById(type + '-timein-badge');

                if (timeoutBadge) {
                    const countSpan = timeoutBadge.querySelector('span');
                    if (newTimeoutCount > 0) {
                        console.log(`✅ Showing ${type} timeout badge with NEW count:`, newTimeoutCount);
                        countSpan.textContent = newTimeoutCount;
                        timeoutBadge.style.display = 'flex';
                        timeoutBadge.classList.remove('force-hidden');
                    } else {
                        forceHideBadge(timeoutBadge);
                    }
                }

                if (timeinBadge) {
                    const countSpan = timeinBadge.querySelector('span');
                    if (newTimeinCount > 0) {
                        console.log(`✅ Showing ${type} timein badge with NEW count:`, newTimeinCount);
                        countSpan.textContent = newTimeinCount;
                        timeinBadge.style.display = 'flex';
                        timeinBadge.classList.remove('force-hidden');
                    } else {
                        forceHideBadge(timeinBadge);
                    }
                }

                return;
            }

            // Update timeout badge (orange)
            const timeoutBadge = document.getElementById(type + '-timeout-badge');
            console.log(`🔍 Found ${type} timeout badge:`, timeoutBadge);
            if (timeoutBadge) {
                const countSpan = timeoutBadge.querySelector('span');
                if (counts.timeout > 0) {
                    console.log(`✅ Showing ${type} timeout badge with count:`, counts.timeout);
                    countSpan.textContent = counts.timeout;
                    timeoutBadge.style.display = 'flex';
                } else {
                    console.log(`❌ Hiding ${type} timeout badge (count: 0)`);
                    timeoutBadge.style.display = 'none';
                }
            } else {
                console.error(`❌ Could not find ${type} timeout badge element`);
            }

            // Update timein badge (blue)
            const timeinBadge = document.getElementById(type + '-timein-badge');
            console.log(`🔍 Found ${type} timein badge:`, timeinBadge);
            if (timeinBadge) {
                const countSpan = timeinBadge.querySelector('span');
                if (counts.timein > 0) {
                    console.log(`✅ Showing ${type} timein badge with count:`, counts.timein);
                    countSpan.textContent = counts.timein;
                    timeinBadge.style.display = 'flex';
                } else {
                    console.log(`❌ Hiding ${type} timein badge (count: 0)`);
                    timeinBadge.style.display = 'none';
                }
            } else {
                console.error(`❌ Could not find ${type} timein badge element`);
            }
        }

        function updateLateNotificationBadge(lateCounts) {
            console.log('🔄 Updating late badge:', lateCounts);

            // Check if late badge has been reset/viewed
            const resetKey = 'educator-late-notifications-reset';
            const isReset = localStorage.getItem(resetKey);
            const resetTimestamp = localStorage.getItem(`${resetKey}-timestamp`);

            console.log('🔍 Reset state check for late badge:', {
                resetKey,
                isReset,
                resetTimestamp,
                localStorage: localStorage.getItem(resetKey)
            });

            // Check if reset happened today (reset at midnight)
            const today = new Date().toDateString();
            const resetDate = resetTimestamp ? new Date(parseInt(resetTimestamp)).toDateString() : null;
            const isResetToday = resetDate === today;

            console.log('📅 Date check for late badge:', {
                today,
                resetDate,
                isResetToday,
                shouldHide: isReset === 'true' && isResetToday
            });

            if (isReset === 'true' && isResetToday) {
                console.log('🚫 Late badge was reset today - FORCING it to stay hidden');
                const lateBadge = document.getElementById('late-badge');
                if (lateBadge) {
                    forceHideBadge(lateBadge);
                    console.log('✅ Force hidden late badge');
                }
                return;
            }

            const lateBadge = document.getElementById('late-badge');
            console.log('🔍 Found late badge:', lateBadge);
            if (lateBadge) {
                const countSpan = lateBadge.querySelector('span');
                if (lateCounts.count > 0) {
                    console.log('✅ Showing late badge with count:', lateCounts.count);
                    countSpan.textContent = lateCounts.count;
                    lateBadge.style.display = 'flex';
                } else {
                    console.log('❌ Hiding late badge (count: 0)');
                    lateBadge.style.display = 'none';
                }
            } else {
                console.error('❌ Could not find late badge element');
            }
        }

        function setupNavigationClickHandlers() {
            console.log('🔗 Setting up navigation click handlers with automatic badge reset...');

            // Academic logs click handler
            document.getElementById('academic-nav-link').addEventListener('click', function(e) {
                console.log('🖱️ Academic navigation clicked - will reset badges on page visit');
                markNotificationsAsViewed('academic');
            });

            // Going out logs click handler
            document.getElementById('goingout-nav-link').addEventListener('click', function(e) {
                console.log('🖱️ Going out navigation clicked - will reset badges on page visit');
                markNotificationsAsViewed('goingout');
            });

            // Visitor logs click handler
            document.getElementById('visitor-nav-link').addEventListener('click', function(e) {
                console.log('🖱️ Visitor navigation clicked - will reset badges on page visit');
                markNotificationsAsViewed('visitor');
            });

            // Late analytics click handler
            document.getElementById('late-nav-link').addEventListener('click', function(e) {
                console.log('🖱️ Late analytics navigation clicked - will reset badges on page visit');
                markNotificationsAsViewed('late');
            });

            console.log('✅ Navigation click handlers set up successfully');
        }



        function markNotificationsAsViewed(type) {
            console.log('🔄 Marking notifications as viewed for type:', type);

            let url;
            if (type === 'academic') {
                url = '{{ route("educator.notifications.academic.mark-viewed") }}';
            } else if (type === 'goingout') {
                url = '{{ route("educator.notifications.goingout.mark-viewed") }}';
            } else if (type === 'visitor') {
                url = '{{ route("educator.notifications.visitor.mark-viewed") }}';
            } else if (type === 'late') {
                url = '{{ route("educator.notifications.late.mark-viewed") }}';
            }

            // Immediately hide badges for better user experience
            if (type === 'late') {
                updateLateNotificationBadge({ count: 0 });
            } else {
                updateNotificationBadges(type, { timeout: 0, timein: 0 });
            }

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('✅ Successfully marked notifications as viewed for:', type);

                    // Store reset state in localStorage for persistence
                    const resetKey = `educator-${type}-notifications-reset`;
                    localStorage.setItem(resetKey, 'true');
                    localStorage.setItem(`${resetKey}-timestamp`, Date.now().toString());
                } else {
                    console.error('❌ Failed to mark notifications as viewed:', data);
                }
            })
            .catch(error => {
                console.error('❌ Error marking notifications as viewed:', error);
                // Even if server request fails, keep badges hidden for better UX
            });
        }

        function setActiveNavigation() {
            const currentPath = window.location.pathname;
            const currentUrl = window.location.href;

            // Enhanced navigation mapping for educator pages
            const navLinks = {
                'dashboard-nav-link': ['/educator/dashboard'],
                'academic-nav-link': [
                    '/educator/academic-monitor',
                    '/educator/academic',
                    'academic-monitor'
                ],
                'goingout-nav-link': [
                    '/educator/goingout-monitor',
                    '/educator/goingout',
                    'goingout-monitor'
                ],
                'visitor-nav-link': [
                    '/educator/visitor-monitor',
                    '/educator/visitor',
                    'visitor-monitor'
                ],
                'late-nav-link': [
                    '/educator/late-analytics',
                    '/educator/analytics',
                    'late-analytics'
                ]
            };

            // Remove active class from all links
            Object.keys(navLinks).forEach(linkId => {
                const link = document.getElementById(linkId);
                if (link) {
                    link.classList.remove('nav-active');
                }
            });

            // Add active class to current page link
            Object.entries(navLinks).forEach(([linkId, paths]) => {
                const isActive = paths.some(path => {
                    // Check both URL path and query parameters
                    return currentPath.includes(path) || currentUrl.includes(path);
                });

                if (isActive) {
                    const link = document.getElementById(linkId);
                    if (link) {
                        link.classList.add('nav-active');
                    }
                }
            });
        }
    </script>

</x-studentLayout>

