<x-monitorLayout>
    <!-- Real-time Notification Container -->
    <div id="notification-container" class="fixed z-50 max-w-sm space-y-3 top-4 right-4">
        <!-- Notification cards will be dynamically inserted here -->
    </div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    feather.replace();

    // Show schedule modals if any
    const modalIds = ['academicScheduleModal', 'irregularScheduleModal'];
    modalIds.forEach(modalId => {
        const modal = document.getElementById(modalId);
        if (modal) modal.classList.remove('hidden');
    });

    // Load overview cards and render only the default (academic) chart
    updateTodayOverview();
    // ensure only the default chart is visible on load
    showChart(currentChart);

    // Then start the refresh cycle — refresh only overview + currently visible chart
    function refreshVisibleChart() {
        updateTodayOverview();
        switch (currentChart) {
            case 'academic': updateAcademicTimeInOutChart(); break;
            case 'goingOut': updateGoingOutTimeInOutChart(); break;
            case 'intern': updateInternTimeInOutChart(); break;
            case 'goingHome': updateGoingHomeTimeInOutChart(); break;
            default: /* visitor or none */ break;
        }
    }
    refreshVisibleChart();
    setInterval(refreshVisibleChart, 15000);

    // Initialize real-time notifications
    initializeRealtimeNotifications();
});

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none'; // Ensures it's removed from view
    }
}

// Real-time notification system (synchronized with navigation badges)
let notificationQueue = [];
let isProcessingQueue = false;
let isFirstLoad = true;
let shownNotifications = new Set(); // Track shown notifications to prevent duplicates
let lastAcademicViewed = null;
let lastGoingOutViewed = null;

function initializeRealtimeNotifications() {
    // Check for new activities every 3 seconds (same as navigation badges)
    setInterval(checkForNewActivities, 3000);

    // Clean up old notification tracking every 5 minutes to prevent memory buildup
    setInterval(() => {
        if (shownNotifications.size > 100) {
            console.log('🧹 Cleaning up old notification tracking');
            shownNotifications.clear();
        }
    }, 5 * 60 * 1000);

    console.log('🔔 Real-time notification system initialized (synced with navigation badges)');
}

function checkForNewActivities() {
    const currentTime = new Date().toISOString();

    // console.log('🔍 Checking for new activities...');
    fetch('/educator/recent-activities')
        .then(response => response.json())
        .then(data => {
            // console.log('📡 Recent activities response:', data);
            if (data.success) {
                if (data.activities.length > 0) {
                    // On first load, don't show notifications for existing activities
                    if (isFirstLoad) {
                        isFirstLoad = false;
                    } else {
                            const activityKey = `${activity.type}_${activity.student_id}_${activity.action}_${activity.timestamp}`;
                            if (shownNotifications.has(activityKey)) {
                                return false;
                            }
                            shownNotifications.add(activityKey);
                            return true;

                        if (newActivities.length > 0) {
                            newActivities.forEach(activity => {
                                notificationQueue.push(activity);
                            });

                            // Process queue if not already processing
                            if (!isProcessingQueue) {
                                processNotificationQueue();
                            }
                        // } else {
                        //     console.log('❌ No new activities to show (all filtered out)');
                        // }
                        }
                    }
                } else {
                    // Mark first load as complete even if no activities
                    if (isFirstLoad) {
                        isFirstLoad = false;
                        console.log('✅ First load complete, ready for new notifications');
                    }
                }

                // Update last check time to current time
                lastNotificationCheck = currentTime;

            }
        })
        .catch(error => {
            console.error('❌ Error checking for new activities:', error);
        });
}

function processNotificationQueue() {
    if (notificationQueue.length === 0) {
        isProcessingQueue = false;
        return;
    }

    isProcessingQueue = true;
    const activity = notificationQueue.shift();

    showNotificationCard(activity);

    // Process next notification after 1 second delay
    setTimeout(() => {
        processNotificationQueue();
    }, 1000);
}

function showNotificationCard(activity) {
    const container = document.getElementById('notification-container');
    if (!container) return;

    // Create notification card
    const card = document.createElement('div');
    card.className = 'notification-card transform translate-x-full opacity-0 transition-all duration-300 ease-out';

    // Determine card style based on action and late status
    let bgColor, icon, actionText, typeText, displayName, batchInfo;

    // Check if student is late (red color for late students)
    if (activity.is_late) {
        bgColor = 'bg-gradient-to-r from-red-500 to-red-600';
        icon = '⚠️';
        actionText = 'Late Time In';
        typeText = activity.type === 'academic' ? 'Academic' : (activity.type === 'visitor' ? 'Visitor' : 'Going Out');
    } else {
        // Standard colors: Blue for time in, Orange for time out
        if (activity.action === 'time_in') {
            bgColor = 'bg-gradient-to-r from-blue-500 to-blue-600';
            icon = '🔵';
            actionText = 'Time In';
        } else {
            bgColor = 'bg-gradient-to-r from-orange-500 to-orange-600';
            icon = '🟠';
            actionText = 'Time Out';
        }

        if (activity.type === 'visitor') {
            typeText = 'Visitor';
        } else {
            typeText = activity.type === 'academic' ? 'Academic' : 'Going Out';
        }
    }

    // Set display name and batch info based on activity type
    if (activity.type === 'visitor') {
        displayName = activity.visitor_name;
        batchInfo = 'Visitor Log';
    } else {
        displayName = activity.student_name;
        batchInfo = `Batch ${activity.batch} • ${typeText}`;
    }

    card.innerHTML = `
        <div class="${bgColor} text-white p-4 rounded-lg shadow-lg border border-white/20 backdrop-blur-sm">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                    <div class="text-2xl">${icon}</div>
                    <div>
                        <div class="text-sm font-semibold">${displayName}</div>
                        <div class="text-xs opacity-90">${batchInfo}</div>
                        <div class="text-xs opacity-75">${actionText} at ${activity.time}</div>
                    </div>
                </div>
                <button onclick="removeNotificationCard(this)" class="text-lg leading-none text-white/70 hover:text-white">&times;</button>
            </div>
        </div>
    `;

    container.appendChild(card);

    // Animate in
    setTimeout(() => {
        card.classList.remove('translate-x-full', 'opacity-0');
        card.classList.add('translate-x-0', 'opacity-100');
    }, 100);

    // Auto-remove after 5 seconds
    setTimeout(() => {
        removeNotificationCard(card);
    }, 5000);
}

function removeNotificationCard(element) {
    const card = element.closest ? element.closest('.notification-card') : element;
    if (card) {
        card.classList.add('translate-x-full', 'opacity-0');
        setTimeout(() => {
            if (card.parentNode) {
                card.parentNode.removeChild(card);
            }
        }, 300);
    }
}
</script>

            {{-- Welcome Message --}}
            <div class="mb-10 text-center">
                <p class="text-xl font-bold text-blue-800 sm:text-3xl">
                    Welcome, Monitor!
                    <br class="hidden sm:block" />
                    <span class="block mt-2 text-sm font-medium text-gray-600">Ready to monitor today's student activity?</span>
                    <span class="block w-24 h-1 mx-auto mt-3 bg-blue-500 rounded-full"></span>
                </p>
            </div>

            {{-- Quick Stats Container --}}
            <div class="p-10 mb-8 bg-white shadow-md rounded-2xl">
                <div class="relative perspective-1000">
                    {{-- Flip Container --}}
                    <div id="overviewFlipContainer" class="relative w-full transition-transform duration-500 transform-style-3d">
                        {{-- Academic Overview (Front) --}}
                        <div id="academicOverview" class="w-full backface-hidden">
                            <h3 class="mb-8 text-2xl font-semibold text-gray-800">
                                <span id="overviewTitle">Today's Logs Overview</span>
                            </h3>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6">
                                <!-- Total Students -->
                                <div class="relative p-4 border-2 border-indigo-500 text-white transition shadow bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-md hover:scale-105 min-h-[150px]">
                                    <div class="flex items-center h-full">
                                    <i data-feather="users" class="w-6 h-6 text-indigo-200"></i>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-indigo-100 opacity-90">Total Students</p>
                                        <p class="text-xl font-bold text-white" id="totalStudents">0</p>
                                    </div>
                                    </div>
                                </div>

                                <!-- Completed Academic Log -->
                                <div class="relative p-4 border-2 border-green-500 text-white transition shadow bg-gradient-to-r from-green-500 to-green-600 rounded-md hover:scale-105 min-h-[150px]">
                                    <div class="flex items-center h-full">
                                    <i data-feather="book-open" class="w-6 h-6 text-green-200"></i>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-green-100 opacity-90">Completed Academic Log</p>
                                        <p class="text-xl font-bold text-white" id="completedAcademic">0</p>
                                    </div>
                                    </div>
                                </div>

                                <!-- Completed Leisure Log -->
                                <div class="relative p-4 border-2 border-orange-500 text-white transition shadow bg-gradient-to-r from-orange-500 to-orange-600 rounded-md hover:scale-105 min-h-[150px]">
                                    <div class="flex items-center h-full">
                                    <i data-feather="log-out" class="w-6 h-6 text-orange-200"></i>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-orange-100 opacity-90">Completed Leisure Log</p>
                                        <p class="text-xl font-bold text-white" id="completedLeisure">0</p>
                                    </div>
                                    </div>
                                </div>

                                <!-- Completed Intern Log -->
                                <div class="relative p-4 border-2 border-purple-500 text-white transition shadow bg-gradient-to-r from-purple-500 to-purple-600 rounded-md hover:scale-105 min-h-[150px]">
                                    <div class="flex items-center h-full">
                                    <i data-feather="briefcase" class="w-6 h-6 text-purple-200"></i>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-purple-100 opacity-90">Completed Intern Log</p>
                                        <p class="text-xl font-bold text-white" id="completedIntern">0</p>
                                    </div>
                                    </div>
                                </div>

                                <!-- Completed Going Home -->
                                <div class="relative p-4 border-2 border-pink-500 text-white transition shadow bg-gradient-to-r from-pink-500 to-pink-600 rounded-md hover:scale-105 min-h-[150px]">
                                    <div class="flex items-center h-full">
                                    <i data-feather="home" class="w-6 h-6 text-pink-200"></i>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-pink-100 opacity-90">Completed Going Home</p>
                                        <p class="text-xl font-bold text-white" id="completedGoingHome">0</p>
                                    </div>
                                    </div>
                                </div>

                                <!-- Total Visitors -->
                                <div class="relative p-4 border-2 border-yellow-500 text-white transition shadow bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-md hover:scale-105 min-h-[150px]">
                                    <div class="flex items-center h-full">
                                    <i data-feather="user-check" class="w-6 h-6 text-yellow-200"></i>
                                    <div class="ml-3">
                                        <p class="text-sm font-medium text-yellow-100 opacity-90">Total Visitors</p>
                                        <p class="text-xl font-bold text-white" id="totalVisitors">0</p>
                                    </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Chart Filter Buttons (only show for Academic overview) --}}
            <div id="chartFilterButtons" class="flex justify-center mb-6">
                <div class="inline-flex p-1 bg-gray-100 rounded-lg">
                    <button id="btnAcademic" type="button" class="px-6 py-2 text-sm font-medium text-white transition-all duration-200 bg-blue-600 rounded-md shadow-sm chart-toggle" data-target="academic">
                        Academic Graph
                    </button>
                    <button id="btnGoingOut" type="button" class="px-6 py-2 text-sm font-medium text-gray-600 transition-all duration-200 rounded-md chart-toggle hover:text-gray-800" data-target="goingOut">
                        Leisure Graph
                    </button>
                    <button id="btnIntern" type="button" class="px-6 py-2 text-sm font-medium text-gray-600 transition-all duration-200 rounded-md chart-toggle hover:text-gray-800" data-target="intern">
                        Intern Graph
                    </button>
                    <button id="btnGoingHome" type="button" class="px-6 py-2 text-sm font-medium text-gray-600 transition-all duration-200 rounded-md chart-toggle hover:text-gray-800" data-target="goingHome">
                        Going Home Graph
                    </button>
                    <button id="btnVisitor" type="button" class="px-6 py-2 text-sm font-medium text-gray-600 transition-all duration-200 rounded-md chart-toggle hover:text-gray-800" data-target="visitor">
                        Visitor Graph
                    </button>
                </div>
            </div>

            <div class="mb-8">
                <div id="academicChartContainer" class="p-6 bg-white shadow-md rounded-2xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Academic Logs</h3>
                    <div class="h-80">
                        <canvas id="academicTimeInOutChart"></canvas>
                    </div>
                </div>

                <!-- Academic monthly line graph (per-chart) -->
                <div id="academicLineContainer" class="hidden p-6 mt-4 bg-white shadow-md rounded-2xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Academic — Monthly Absence Trend</h3>
                    <div class="h-56">
                        <canvas id="academicLineChart"></canvas>
                    </div>
                </div>

                 <div id="monthlyLineGraphContainer" class="hidden p-6 bg-white shadow-md rounded-2xl">
                     <h3 class="mb-4 text-lg font-semibold text-gray-800">Absences by Batch (Monthly Overview)</h3>
                     <div class="h-80">
                         <canvas id="academicAbsentChart"></canvas>
                     </div>
                 </div>

                 <div id="goingOutChartContainer" class="p-6 bg-white shadow-md rounded-2xl">
                     <h3 class="mb-4 text-lg font-semibold text-gray-800">Leisure Logs</h3>
                     <div class="h-80">
                         <canvas id="goingOutTimeInOutChart"></canvas>
                     </div>
                 </div>

                <!-- Going Out monthly line graph -->
                <div id="goingOutLineContainer" class="hidden p-6 mt-4 bg-white shadow-md rounded-2xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Leisure — Monthly Absence Trend</h3>
                    <div class="h-56">
                        <canvas id="goingOutLineChart"></canvas>
                    </div>
                </div>

                 <div id="internChartContainer" class="p-6 bg-white shadow-md rounded-2xl">
                     <h3 class="mb-4 text-lg font-semibold text-gray-800">Intern Logs</h3>
                     <div class="h-80">
                         <canvas id="internTimeInOutChart"></canvas>
                     </div>
                 </div>

                <!-- Intern monthly line graph -->
                <div id="internLineContainer" class="hidden p-6 mt-4 bg-white shadow-md rounded-2xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Intern — Monthly Absence Trend</h3>
                    <div class="h-56">
                        <canvas id="internLineChart"></canvas>
                    </div>
                </div>

                 <div id="goingHomeChartContainer" class="p-6 bg-white shadow-md rounded-2xl">
                     <h3 class="mb-4 text-lg font-semibold text-gray-800">Going Home Logs</h3>
                     <div class="h-80">
                         <canvas id="goingHomeTimeInOutChart"></canvas>
                     </div>
                 </div>

                <!-- Going Home monthly line graph -->
                <div id="goingHomeLineContainer" class="hidden p-6 mt-4 bg-white shadow-md rounded-2xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Going Home — Monthly Absence Trend</h3>
                    <div class="h-56">
                        <canvas id="goingHomeLineChart"></canvas>
                    </div>
                </div>

                <div id="visitorChartContainer" class="hidden p-6 bg-white shadow-md rounded-2xl">
                   <h3 class="mb-4 text-lg font-semibold text-gray-800">Visitor Logs</h3>
                    <div class="h-80">
                        <canvas id="visitorTimeInOutChart"></canvas>
                    </div>
                </div>

                <!-- Visitor daily line graph -->
                <div id="visitorLineContainer" class="hidden p-6 mt-4 bg-white shadow-md rounded-2xl">
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Visitor — Daily In/Out Trend</h3>
                    <div class="h-56">
                        <canvas id="visitorLineChart"></canvas>
                    </div>
                </div>
            </div>
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.3.0/dist/chart.umd.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/patternomaly@1.3.2/dist/patternomaly.min.js"></script>
    <style>
        .perspective-1000 {
            perspective: 1000px;
        }
        .transform-style-3d {
            transform-style: preserve-3d;
        }
        .backface-hidden {
            backface-visibility: hidden;
        }
        .rotate-y-180 {
            transform: rotateY(180deg);
        }
        .flip-button {
            transition: all 0.3s ease;
        }
        .flip-button:hover {
            transform: scale(1.05);
        }
        .flip-button:active {
            transform: scale(0.95);
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

        /* Smooth scrolling behavior */
        html {
            scroll-behavior: smooth;
        }

        /* Real-time notification card styles */
        .notification-card {
            min-width: 300px;
            max-width: 350px;
            z-index: 1000;
        }

        .notification-card:hover {
            transform: scale(1.02) !important;
        }

        /* Notification container positioning */
        #notification-container {
            pointer-events: none;
        }

        #notification-container .notification-card {
            pointer-events: auto;
        }

        /* Animation for notification cards */
        @keyframes slideInRight {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOutRight {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        .notification-enter {
            animation: slideInRight 0.3s ease-out;
        }

        .notification-exit {
            animation: slideOutRight 0.3s ease-in;
        }
    </style>
    <script>
        let academicTimeInOutChart = null;
        let goingOutTimeInOutChart = null;
        let internTimeInOutChart = null;
        let goingHomeTimeInOutChart = null;
        // line chart instances (kept per canvas)
        let academicLineChart = null;
        let goingOutLineChart = null;
        let internLineChart = null;
        let goingHomeLineChart = null;
         // track which chart is currently visible
         let currentChart = 'academic';

        // Function to create pattern colors
        function createPatterns(data, color) {
            return data.map(() => pattern.draw('diagonal', color));
        }

        function createDots(data, color) {
            return data.map(() => pattern.draw('dot', color));
        }

        // Function to show specific chart
        function showChart(chartType) {
            // map chartType to container ids and update functions
            const mapping = {
                academic: { container: 'academicChartContainer', lineContainer: 'academicLineContainer', updateFn: updateAcademicTimeInOutChart, lineFn: updateAcademicLineGraph, btnId: 'btnAcademic' },
                goingOut: { container: 'goingOutChartContainer', lineContainer: 'goingOutLineContainer', updateFn: updateGoingOutTimeInOutChart, lineFn: updateGoingOutLineGraph, btnId: 'btnGoingOut' },
                intern: { container: 'internChartContainer', lineContainer: 'internLineContainer', updateFn: updateInternTimeInOutChart, lineFn: updateInternLineGraph, btnId: 'btnIntern' },
                goingHome: { container: 'goingHomeChartContainer', lineContainer: 'goingHomeLineContainer', updateFn: updateGoingHomeTimeInOutChart, lineFn: updateGoingHomeLineGraph, btnId: 'btnGoingHome' },
                visitor: { container: 'visitorChartContainer', lineContainer: 'visitorLineContainer', updateFn: updateVisitorTimeInOutChart, lineFn: updateVisitorLineGraph, btnId: 'btnVisitor' }
            };

            // Hide all containers first
            Object.values(mapping).forEach(m => {
                const el = document.getElementById(m.container);
                if (el) el.classList.add('hidden');
                if (m.lineContainer) {
                    const lineEl = document.getElementById(m.lineContainer);
                    if (lineEl) lineEl.classList.add('hidden');
                }
            });

            // Reset button styles
            document.querySelectorAll('.chart-toggle').forEach(btn => {
                btn.classList.remove('bg-blue-600', 'text-white', 'shadow-sm');
                btn.classList.add('text-gray-600');
            });

            // Show selected
            const selected = mapping[chartType];
            if (selected) {
                const containerEl = document.getElementById(selected.container);
                if (containerEl) containerEl.classList.remove('hidden');
                const btnEl = document.getElementById(selected.btnId);
                if (btnEl) {
                    btnEl.classList.add('bg-blue-600', 'text-white', 'shadow-sm');
                    btnEl.classList.remove('text-gray-600');
                }
                // remember current visible chart and update only it + its monthly line graph
                currentChart = chartType;
                if (typeof selected.updateFn === 'function') selected.updateFn();
                if (selected.lineContainer) {
                    const lineEl = document.getElementById(selected.lineContainer);
                    if (lineEl) lineEl.classList.remove('hidden');
                }
                if (typeof selected.lineFn === 'function') selected.lineFn();
             } else {
                 console.warn('Unknown chart type:', chartType);
             }
         }

        // wire chart-toggle buttons (delegated)
        document.addEventListener('click', function (e) {
            const btn = e.target.closest && e.target.closest('.chart-toggle');
            if (!btn) return;
            const target = btn.getAttribute('data-target');
            if (target) showChart(target);
        });

        // Function to update Today's Overview cards (Academic)
        function updateTodayOverview() {
            fetch('/educator/logs-data')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.message);
                        return;
                    }

                    // Fill in the cards
                    document.getElementById('totalStudents').textContent = data.total_students ?? '0';
                    document.getElementById('completedAcademic').textContent = data.academic ?? '0';
                    document.getElementById('completedLeisure').textContent = data.going_out ?? '0';
                    document.getElementById('completedIntern').textContent = data.intern ?? '0';
                    document.getElementById('completedGoingHome').textContent = data.going_home ?? '0';
                    document.getElementById('totalVisitors').textContent = data.visitor ?? '0';
                })
                .catch(error => {
                    console.error('Error fetching overview data:', error);
                });
        }

        // Function to update Going Out Overview
        function updateGoingOutOverview() {
            // Check if it's Sunday - Going Out still operates on Sundays
            // No special Sunday handling needed for Going Out Overview

            // Fetch going out log in/out data
            fetch('/educator/goingout-loginout-data')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Error:', data.message);
                        return;
                    }

                    // Update Log In card with breakdown (total, on time, and late)
                    document.getElementById('goingOutLogInTotal').textContent = data.logIn?.total || 0;
                    document.getElementById('goingOutLogInOnTime').textContent = data.logIn?.onTime || 0;
                    document.getElementById('goingOutLogInLate').textContent = data.logIn?.late || 0;

                    // Update Log Out card (total and on time)
                    document.getElementById('goingOutLogOutTotal').textContent = data.logOut?.total || 0;
                    document.getElementById('goingOutLogOutOnTime').textContent = data.logOut?.onTime || 0;
                })
                .catch(error => {
                    console.error('Error fetching leisure get in/out data:', error);
                    // Show error state in the UI (for existing cards)
                    document.getElementById('goingOutLogInTotal').textContent = '—';
                    document.getElementById('goingOutLogInOnTime').textContent = '—';
                    document.getElementById('goingOutLogInLate').textContent = '—';
                    document.getElementById('goingOutLogOutTotal').textContent = '—';
                    document.getElementById('goingOutLogOutOnTime').textContent = '—';
                });

            // Fetch total students data
            fetch('/educator/student-data')
                .then(response => response.json())
                .then(data => {
                    console.log('Student data response:', data); // Debug log
                    if (!Array.isArray(data) || data.length === 0) {
                        document.getElementById('goingOutTotalStudents').textContent = '—';
                        return;
                    }
                    const studentCounts = data.map(item => item.total_students);
                    const totalStudents = studentCounts.reduce((sum, count) => sum + count, 0);
                    document.getElementById('goingOutTotalStudents').textContent = totalStudents;
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('goingOutTotalStudents').textContent = '—';
                });
        }

        // Function to update Academic Time In/Out Chart
        function updateAcademicTimeInOutChart() {
            const chartContainer = document.getElementById('academicChartContainer').querySelector('.h-80');
            const canvas = document.getElementById('academicTimeInOutChart');

            if (!chartContainer || !canvas) {
                console.error('Chart container or canvas not found');
                return;
            }

            fetch('/educator/time-inout-by-batch?type=academic')
                .then(response => response.json())
                .then(timeData => {
                    if (timeData.error) {
                        throw new Error(timeData.message || 'Error fetching data');
                    }

                    if (academicTimeInOutChart instanceof Chart) {
                        academicTimeInOutChart.destroy();
                    }

                    if (!timeData || timeData.length === 0) {
                        chartContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No academic time in/out data available for today</div>';
                        return;
                    }

                    const ctx = canvas.getContext('2d');
                    academicTimeInOutChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: timeData.map(item => `Class ${item.batch}`),
                            datasets: [
                                {
                                    label: 'Get Out',
                                    data: timeData.map(item => item.time_out_count),
                                    backgroundColor: '#f59e0b', // Amber
                                    borderColor: '#b45309',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Get In',
                                    data: timeData.map(item => item.time_in_count),
                                    backgroundColor: '#3b82f6', // Blue
                                    borderColor: '#1d4ed8',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Ontime',
                                    data: timeData.map(item => item.time_in_ontime_count),
                                    backgroundColor: '#10b981', // Emerald
                                    borderColor: '#065f46',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Ontime',
                                    data: timeData.map(item => item.time_out_ontime_count),
                                    backgroundColor: '#8b5cf6', // Violet
                                    borderColor: '#5b21b6',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Late',
                                    data: timeData.map(item => item.time_in_late_count),
                                    backgroundColor: '#ef4444', // Red
                                    borderColor: '#991b1b',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Late',
                                    data: timeData.map(item => item.time_out_late_count),
                                    backgroundColor: '#f97316', // Orange
                                    borderColor: '#c2410c',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Early',
                                    data: timeData.map(item => item.time_in_early_count),
                                    backgroundColor: '#14b8a6', // Teal
                                    borderColor: '#0f766e',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Early',
                                    data: timeData.map(item => item.time_out_early_count),
                                    backgroundColor: '#eab308', // Yellow
                                    borderColor: '#a16207',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Absent',
                                    data: timeData.map(item => item.absent_count),
                                    backgroundColor: '#14b8a6', // Teal
                                    borderColor: '#0f766e',
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                title: {
                                    display: true,
                                    text: 'Academic Get In/Out & Late Students by Batch'
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading academic chart:', error);
                    chartContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full">
                            <p class="mb-2 font-semibold text-red-500">Error loading academic time in/out data</p>
                            <p class="text-sm text-gray-500">${error.message}</p>
                        </div>
                    `;
                });
        }

        function updateGoingOutTimeInOutChart() {
            const chartContainer = document.getElementById('goingOutChartContainer').querySelector('.h-80');
            const canvas = document.getElementById('goingOutTimeInOutChart');

            if (!chartContainer || !canvas) {
                console.error('Leisure chart container or canvas not found');
                return;
            }

            fetch('/educator/time-inout-by-batch?type=going_out')
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        throw new Error(data.message || 'Error fetching data');
                    }

                    if (goingOutTimeInOutChart instanceof Chart) {
                        goingOutTimeInOutChart.destroy();
                    }

                    if (!data || Object.keys(data).length === 0) {
                        chartContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No leisure time in/out data available for today</div>';
                        return;
                    }

                    const ctx = canvas.getContext('2d');
                    goingOutTimeInOutChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Male', 'Female'],
                            datasets: [
                                {
                                    label: 'Get Out',
                                    data: [data.Male.time_out_count, data.Female.time_out_count],
                                    backgroundColor: '#f97316', // Orange
                                    borderColor: '#ea580c',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Get In',
                                    data: [data.Male.time_in_count, data.Female.time_in_count],
                                    backgroundColor: '#3b82f6', // Blue
                                    borderColor: '#2563eb',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Ontime',
                                    data: [data.Male.time_out_ontime_count, data.Female.time_out_ontime_count],
                                    backgroundColor: '#22c55e', // Green
                                    borderColor: '#15803d',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Ontime',
                                    data: [data.Male.time_in_ontime_count, data.Female.time_in_ontime_count],
                                    backgroundColor: '#06b6d4', // Cyan
                                    borderColor: '#0e7490',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Late',
                                    data: [data.Male.time_out_late_count, data.Female.time_out_late_count],
                                    backgroundColor: '#ef4444', // Red
                                    borderColor: '#991b1b',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Late',
                                    data: [data.Male.time_in_late_count, data.Female.time_in_late_count],
                                    backgroundColor: '#a855f7', // Purple
                                    borderColor: '#6b21a8',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Early',
                                    data: [data.Male.time_out_early_count, data.Female.time_out_early_count],
                                    backgroundColor: '#eab308', // Yellow
                                    borderColor: '#a16207',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Early',
                                    data: [data.Male.time_in_early_count, data.Female.time_in_early_count],
                                    backgroundColor: '#14b8a6', // Teal
                                    borderColor: '#0f766e',
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                title: {
                                    display: true,
                                    text: 'Leisure Time In/Out & Late Students by Gender'
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading leisure chart:', error);
                    chartContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full">
                            <p class="mb-2 font-semibold text-red-500">Error loading leisure time in/out data</p>
                            <p class="text-sm text-gray-500">${error.message}</p>
                        </div>
                    `;
                });
        }

        function updateInternTimeInOutChart() {
            const chartContainer = document.getElementById('internChartContainer').querySelector('.h-80');
            const canvas = document.getElementById('internTimeInOutChart');

            if (!chartContainer || !canvas) {
                console.error('Chart container or canvas not found');
                return;
            }

            fetch('/educator/time-inout-by-batch?type=intern')
                .then(response => response.json())
                .then(timeData => {
                    if (timeData.error) {
                        throw new Error(timeData.message || 'Error fetching data');
                    }

                    if (internTimeInOutChart instanceof Chart) {
                        internTimeInOutChart.destroy();
                    }

                    if (!timeData || timeData.length === 0) {
                        // Just show message instead of removing canvas
                        chartContainer.innerHTML = `
                            <div class="flex items-center justify-center h-full text-gray-500">
                                No intern time in/out data available for today
                            </div>
                        `;
                        return;
                    }

                    const ctx = canvas.getContext('2d');
                    internTimeInOutChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: timeData.map(item => item.batch || 'N/A'),
                            datasets: [
                                {
                                    label: 'Get Out',
                                    data: timeData.map(item => item.time_out_count),
                                    backgroundColor: '#f59e0b',
                                    borderColor: '#b45309',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Get In',
                                    data: timeData.map(item => item.time_in_count),
                                    backgroundColor: '#3b82f6',
                                    borderColor: '#1d4ed8',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Ontime',
                                    data: timeData.map(item => item.time_in_ontime_count),
                                    backgroundColor: '#10b981',
                                    borderColor: '#065f46',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Ontime',
                                    data: timeData.map(item => item.time_out_ontime_count),
                                    backgroundColor: '#8b5cf6',
                                    borderColor: '#5b21b6',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Late',
                                    data: timeData.map(item => item.time_in_late_count),
                                    backgroundColor: '#ef4444',
                                    borderColor: '#991b1b',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Late',
                                    data: timeData.map(item => item.time_out_late_count),
                                    backgroundColor: '#f97316',
                                    borderColor: '#c2410c',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Early',
                                    data: timeData.map(item => item.time_in_early_count),
                                    backgroundColor: '#14b8a6',
                                    borderColor: '#0f766e',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Early',
                                    data: timeData.map(item => item.time_out_early_count),
                                    backgroundColor: '#eab308',
                                    borderColor: '#a16207',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Absent',
                                    data: timeData.map(item => item.absent_count),
                                    backgroundColor: '#14b8a6', // Teal
                                    borderColor: '#0f766e',
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            },
                            plugins: {
                                legend: { position: 'top' },
                                title: {
                                    display: true,
                                    text: 'Intern Get In/Out & Status by Batch'
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading intern chart:', error);

                    chartContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full">
                            <p class="mb-2 font-semibold text-red-500">Error loading intern time in/out data</p>
                            <p class="text-sm text-gray-500">${error.message}</p>
                        </div>
                    `;
                });
        }

        function updateGoingHomeTimeInOutChart() {
            const chartContainer = document.getElementById('goingHomeChartContainer').querySelector('.h-80');
            const canvas = document.getElementById('goingHomeTimeInOutChart');

            if (!chartContainer || !canvas) {
                console.error('Going Home chart container or canvas not found');
                return;
            }

            fetch('/educator/time-inout-by-batch?type=going_home')
                .then(response => response.json())
                .then(timeData => {
                    if (timeData.error) {
                        throw new Error(timeData.message || 'Error fetching data');
                    }

                    if (goingHomeTimeInOutChart instanceof Chart) {
                        goingHomeTimeInOutChart.destroy();
                    }

                    if (!timeData || timeData.length === 0) {
                        chartContainer.innerHTML = `
                            <div class="flex items-center justify-center h-full text-gray-500">
                                No going home time in/out data available for today
                            </div>
                        `;
                        return;
                    }

                    const ctx = canvas.getContext('2d');
                    goingHomeTimeInOutChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: timeData.map(item => item.batch || 'N/A'),
                            datasets: [
                                {
                                    label: 'Get Out',
                                    data: timeData.map(item => item.time_out_count),
                                    backgroundColor: '#f59e0b',
                                    borderColor: '#b45309',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Get In',
                                    data: timeData.map(item => item.time_in_count),
                                    backgroundColor: '#3b82f6',
                                    borderColor: '#1d4ed8',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Ontime',
                                    data: timeData.map(item => item.time_in_ontime_count),
                                    backgroundColor: '#10b981',
                                    borderColor: '#065f46',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Ontime',
                                    data: timeData.map(item => item.time_out_ontime_count),
                                    backgroundColor: '#8b5cf6',
                                    borderColor: '#5b21b6',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Late',
                                    data: timeData.map(item => item.time_in_late_count),
                                    backgroundColor: '#ef4444',
                                    borderColor: '#991b1b',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Late',
                                    data: timeData.map(item => item.time_out_late_count),
                                    backgroundColor: '#f97316',
                                    borderColor: '#c2410c',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time In Early',
                                    data: timeData.map(item => item.time_in_early_count),
                                    backgroundColor: '#14b8a6',
                                    borderColor: '#0f766e',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out Early',
                                    data: timeData.map(item => item.time_out_early_count),
                                    backgroundColor: '#eab308',
                                    borderColor: '#a16207',
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { stepSize: 1 }
                                }
                            },
                            plugins: {
                                legend: { position: 'top' },
                                title: {
                                    display: true,
                                    text: 'Going Home Get In/Out & Status by Batch'
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading going home chart:', error);

                    chartContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full">
                            <p class="mb-2 font-semibold text-red-500">Error loading going home time in/out data</p>
                            <p class="text-sm text-gray-500">${error.message}</p>
                        </div>
                    `;
                });
        }

        function updateVisitorTimeInOutChart() {
            const chartContainer = document.getElementById('visitorChartContainer').querySelector('.h-80');
            const canvas = document.getElementById('visitorTimeInOutChart');

            if (!chartContainer || !canvas) {
                console.error('Visitor chart container or canvas not found');
                return;
            }

            fetch('/educator/time-inout-by-batch?type=visitor')
                .then(response => response.json())
                .then(timeData => {
                    if (timeData.error) {
                        throw new Error(timeData.message || 'Error fetching visitor data');
                    }

                    if (visitorTimeInOutChart instanceof Chart) {
                        visitorTimeInOutChart.destroy();
                    }

                    if (!timeData || Object.keys(timeData).length === 0) {
                        chartContainer.innerHTML = '<div class="flex items-center justify-center h-full text-gray-500">No visitor time in/out data available for today</div>';
                        return;
                    }

                    const ctx = canvas.getContext('2d');
                    visitorTimeInOutChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: ['Visitors'],
                            datasets: [
                                {
                                    label: 'Time In',
                                    data: [timeData.time_in_count || 0],
                                    backgroundColor: '#3b82f6', // Blue
                                    borderColor: '#1d4ed8',
                                    borderWidth: 2
                                },
                                {
                                    label: 'Time Out',
                                    data: [timeData.time_out_count || 0],
                                    backgroundColor: '#f59e0b', // Amber
                                    borderColor: '#b45309',
                                    borderWidth: 2
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1
                                    }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top'
                                },
                                title: {
                                    display: true,
                                    text: 'Visitor Time In/Out Summary (Today)'
                                }
                            }
                        }
                    });
                })
                .catch(error => {
                    console.error('Error loading visitor chart:', error);
                    chartContainer.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full">
                            <p class="mb-2 font-semibold text-red-500">Error loading visitor time in/out data</p>
                            <p class="text-sm text-gray-500">${error.message}</p>
                        </div>
                    `;
                });
        }

        // Generic monthly line chart builder
        function updateDailyLineChart(type, canvasId, title) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) {
                console.warn(`⚠️ Canvas with ID "${canvasId}" not found.`);
                return;
            }

            const parent = canvas.closest('.h-56') || canvas.parentElement;

            fetch(`/educator/absent-students-by-batch?type=${encodeURIComponent(type)}`)
                .then(response => response.json())
                .then(dailyData => {
                    if (dailyData.error) {
                        throw new Error(dailyData.message || 'Error fetching chart data.');
                    }

                    if (!Array.isArray(dailyData) || dailyData.length === 0) {
                        parent.innerHTML = `
                            <div class="flex items-center justify-center h-full text-gray-500">
                                <div class="text-center">
                                    <i data-feather="calendar" class="w-10 h-10 mx-auto mb-3 text-blue-500"></i>
                                    <p class="text-sm">No daily data available</p>
                                </div>
                            </div>`;
                        feather.replace();
                        return;
                    }

                    if (canvas._chartInstance instanceof Chart) {
                        canvas._chartInstance.destroy();
                    }

                    // Labels (days)
                    const labels = dailyData.map(d => d.day_name || d.day);

                    let totals;
                    let datasets;

                    if (type === 'visitor') {
                        // 👉 Visitor version: only time in/out
                        totals = {
                            in_count: dailyData.map(d => d.in_count || 0),
                            out_count: dailyData.map(d => d.out_count || 0)
                        };

                        datasets = [
                            {
                                label: 'Visitors In',
                                data: totals.in_count,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#1d4ed8',
                                borderWidth: 2
                            },
                            {
                                label: 'Visitors Out',
                                data: totals.out_count,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#059669',
                                borderWidth: 2
                            }
                        ];
                    } else {
                        // 👉 Student version: keep your full original structure
                        totals = {
                            absent: dailyData.map(d => d.batches.reduce((sum, b) => sum + (b.absent_count || 0), 0)),
                            late: dailyData.map(d => d.batches.reduce((sum, b) => sum + (b.late_count || 0), 0)),
                            early: dailyData.map(d => d.batches.reduce((sum, b) => sum + (b.early_count || 0), 0)),
                            ontime: dailyData.map(d => d.batches.reduce((sum, b) => sum + (b.ontime_count || 0), 0)),
                            out_late: dailyData.map(d => d.batches.reduce((sum, b) => sum + (b.out_late_count || 0), 0)),
                            out_early: dailyData.map(d => d.batches.reduce((sum, b) => sum + (b.out_early_count || 0), 0)),
                            out_ontime: dailyData.map(d => d.batches.reduce((sum, b) => sum + (b.out_ontime_count || 0), 0))
                        };

                        datasets = [
                            {
                                label: 'Absent (In)',
                                data: totals.absent,
                                borderColor: '#dc2626',
                                backgroundColor: 'rgba(220,38,38,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#b91c1c',
                                borderWidth: 2
                            },
                            {
                                label: 'Late (In)',
                                data: totals.late,
                                borderColor: '#f97316',
                                backgroundColor: 'rgba(249,115,22,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#ea580c',
                                borderWidth: 2
                            },
                            {
                                label: 'Early (In)',
                                data: totals.early,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#059669',
                                borderWidth: 2
                            },
                            {
                                label: 'On Time (In)',
                                data: totals.ontime,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59,130,246,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#1d4ed8',
                                borderWidth: 2
                            },
                            {
                                label: 'Late (Out)',
                                data: totals.out_late,
                                borderColor: '#a855f7',
                                backgroundColor: 'rgba(168,85,247,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#7e22ce',
                                borderWidth: 2
                            },
                            {
                                label: 'Early (Out)',
                                data: totals.out_early,
                                borderColor: '#14b8a6',
                                backgroundColor: 'rgba(20,184,166,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#0d9488',
                                borderWidth: 2
                            },
                            {
                                label: 'On Time (Out)',
                                data: totals.out_ontime,
                                borderColor: '#facc15',
                                backgroundColor: 'rgba(250,204,21,0.15)',
                                fill: true,
                                tension: 0.4,
                                pointRadius: 5,
                                pointBackgroundColor: '#ca8a04',
                                borderWidth: 2
                            }
                        ];
                    }

                    const ctx = canvas.getContext('2d');
                    const chart = new Chart(ctx, {
                        type: 'line',
                        data: { labels, datasets },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            interaction: { mode: 'index', intersect: false },
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        stepSize: 1,
                                        color: '#6B7280',
                                        font: { size: 12, family: "'Inter', sans-serif" }
                                    },
                                    grid: { color: 'rgba(0,0,0,0.05)', drawBorder: false },
                                    title: {
                                        display: true,
                                        text: type === 'visitor' ? 'Number of Visitors' : 'Number of Students',
                                        color: '#374151',
                                        font: { size: 12, weight: '600' },
                                        padding: { top: 10, bottom: 10 }
                                    }
                                },
                                x: {
                                    ticks: {
                                        color: '#6B7280',
                                        font: { size: 12, family: "'Inter', sans-serif" }
                                    },
                                    grid: { display: false }
                                }
                            },
                            plugins: {
                                legend: {
                                    position: 'top',
                                    labels: {
                                        color: '#374151',
                                        padding: 15,
                                        usePointStyle: true,
                                        pointStyle: 'circle',
                                        font: { size: 12, weight: '500', family: "'Inter', sans-serif" }
                                    }
                                },
                                title: {
                                    display: true,
                                    text: title,
                                    color: '#111827',
                                    font: { size: 14, weight: 'bold', family: "'Inter', sans-serif" },
                                    padding: { top: 10, bottom: 10 }
                                },
                                tooltip: {
                                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                                    titleFont: { size: 13, weight: '600' },
                                    bodyFont: { size: 12 },
                                    padding: 12,
                                    cornerRadius: 8,
                                    callbacks: {
                                        label: function(ctx) {
                                            const count = ctx.raw;
                                            const label = ctx.dataset.label;
                                            return `${label}: ${count} ${type === 'visitor' ? 'visitor' : 'student'}${count !== 1 ? 's' : ''}`;
                                        }
                                    }
                                }
                            },
                            animation: { duration: 800, easing: 'easeInOutQuart' }
                        }
                    });

                    canvas._chartInstance = chart;
                })
                .catch(err => {
                    console.error('❌ Daily line chart error:', err);
                    parent.innerHTML = `
                        <div class="flex flex-col items-center justify-center h-full text-center text-gray-500">
                            <i data-feather="alert-triangle" class="w-8 h-8 mb-2 text-red-500"></i>
                            <p class="text-sm font-medium text-red-500">Error loading data</p>
                            <p class="text-xs text-gray-400">${err.message}</p>
                        </div>`;
                    feather.replace();
                });
        }

        // thin wrappers
        function updateAcademicLineGraph() {
            updateDailyLineChart('academic','academicLineChart','Academic Daily Attendance');
        }

        function updateGoingOutLineGraph() {
            updateDailyLineChart('going_out','goingOutLineChart','Leisure Daily Attendance');
        }

        function updateInternLineGraph() {
            updateDailyLineChart('intern','internLineChart','Intern Daily Attendance');
        }

        function updateGoingHomeLineGraph() {
            updateDailyLineChart('going_home','goingHomeLineChart','Going Home Daily Attendance');
        }

        function updateVisitorLineGraph() {
            // Ensure visitor line container + canvas exist; create if missing
            let lineContainer = document.getElementById('visitorLineContainer');
            if (!lineContainer) {
                const anchor = document.getElementById('goingHomeLineContainer') || document.querySelector('.mb-8') || document.body;
                lineContainer = document.createElement('div');
                lineContainer.id = 'visitorLineContainer';
                lineContainer.className = 'hidden p-6 mt-4 bg-white shadow-md rounded-2xl';
                lineContainer.innerHTML = `
                    <h3 class="mb-4 text-lg font-semibold text-gray-800">Visitor — Daily In/Out</h3>
                    <div class="h-56">
                        <canvas id="visitorLineChart"></canvas>
                    </div>
                `;
                // append after anchor's last child
                anchor.appendChild(lineContainer);
            } else {
                // if canvas missing, create it
                if (!document.getElementById('visitorLineChart')) {
                    lineContainer.innerHTML = `
                        <h3 class="mb-4 text-lg font-semibold text-gray-800">Visitor — Daily In/Out</h3>
                        <div class="h-56">
                            <canvas id="visitorLineChart"></canvas>
                        </div>
                    `;
                }
            }

            // Call generic daily line updater
            updateDailyLineChart('visitor', 'visitorLineChart', 'Visitor Daily In/Out');
        }
    </script>
    @endpush



</x-monitorLayout>
