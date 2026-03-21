<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
    <title>Logify</title>

    <style>
        body {
            font-family: 'Inter', sans-serif;
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
    </style>
</head>

<body class="flex flex-col min-h-screen text-gray-800">
    <!-- Header Section -->
    <header class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-4 py-4 bg-blue-500 shadow-md">
        <a href="{{ env('MAIN_SYSTEM_URL') . '/main-menu' }}">
            <img src="{{ asset('assets/PN-Logo.png') }}" alt="PN Logo" class="object-contain h-10" />
        </a>
        @if (session('user'))
            <div class="flex items-center ml-4 space-x-2 text-sm font-semibold text-white">
                <img src="{{ asset('assets/user.png') }}" alt="Profile" class="object-cover w-8 h-8 rounded-full" />
                <span class="">{{ session('user.user_fname') }} {{ session('user.user_lname') }}</span>

                <!-- User Dropdown -->
                <div class="relative ml-2" id="userDropdown">
                    <button type="button"
                        class="flex items-center justify-center p-1 transition bg-white rounded-full group hover:bg-orange-600"
                        onclick="toggleDropdown()" title="User Menu">
                        <i data-feather="chevron-down"
                            class="w-3 h-3 text-blue-600 transition group-hover:text-white"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div id="dropdownMenu"
                        class="absolute right-0 z-50 hidden mt-2 bg-white border border-gray-200 rounded-md shadow-lg w-36">
                        <div class="py-1">
                            <!-- User Info -->
                            <div class="px-4 py-2 border-b border-gray-100">
                                <p class="text-xs font-medium text-gray-900">{{ session('user.user_fname') }}
                                    {{ session('user.user_lname') }}</p>
                                <p class="text-xs text-gray-500">
                                    @if(session('user.user_role') === 'educator')
                                        {{ ucfirst(session('user_mode', 'educator')) }}
                                    @elseif(session('user.user_role'))
                                        {{ ucfirst(session('user.user_role')) }}
                                    @else
                                        Educator
                                    @endif
                                </p>
                            </div>

                            <!-- Mode Switch (Only for Educators) -->
                            @if (session('user.user_role') === 'educator')
                                @php
                                    $switch = session('user_mode', 'educator'); // default to educator
                                @endphp
                                <form action="{{ route('role.switch') }}" method="POST">
                                    @csrf

                                    @if ($switch === 'educator')
                                        <input type="hidden" name="switch_mode" value="monitor">
                                        <button id="mode-switch-btn"
                                            class="flex items-center w-full px-4 py-2 text-xs text-left text-gray-700 transition-colors duration-200 hover:bg-gray-50 hover:text-gray-900">
                                            <i data-feather="repeat" class="w-4 h-4 mr-2"></i>
                                            <span id="mode-switch-text">Switch to Monitor</span>
                                        </button>
                                    @elseif($switch === 'monitor')
                                        <input type="hidden" name="switch_mode" value="educator">
                                        <button id="mode-switch-btn"
                                            class="flex items-center w-full px-4 py-2 text-xs text-left text-gray-700 transition-colors duration-200 hover:bg-gray-50 hover:text-gray-900">
                                            <i data-feather="repeat" class="w-4 h-4 mr-2"></i>
                                            <span id="mode-switch-text">Switch to Educator</span>
                                        </button>
                                    @endif
                                </form>
                            @endif
                            <!-- Logout Button -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                    class="flex items-center w-full px-4 py-2 text-xs text-left text-gray-700 transition-colors duration-200 hover:bg-red-50 hover:text-red-600">
                                    <i data-feather="log-out" class="w-4 h-4 mr-2"></i>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </header>

    <!-- Main Content Slot -->
    <main class="flex-grow px-2 py-1 mt-20">
        <div class="flex items-center justify-end mb-1">
            <div class="flex flex-row w-full gap-1 sm:w-auto sm:gap-1 sm:p-0">
                <!-- Day Box -->
                <div
                    class="flex items-center w-32 gap-2 px-2 py-1 text-gray-800 bg-white border border-gray-300 rounded-md shadow-sm">
                    <i data-feather="calendar" class="flex-shrink-0 w-4 h-4 text-orange-500"></i>
                    <span id="day" class="text-xs font-medium tracking-wide truncate"></span>
                </div>

                <!-- Date Box -->
                <div
                    class="flex items-center w-40 gap-2 px-2 py-1 text-gray-800 bg-white border border-gray-300 rounded-md shadow-sm">
                    <i data-feather="calendar" class="flex-shrink-0 w-4 h-4 text-orange-500"></i>
                    <span id="date" class="text-xs font-medium tracking-wide truncate"></span>
                </div>

                <!-- Time Box -->
                <div
                    class="flex items-center gap-2 px-2 py-1 text-gray-800 bg-white border border-gray-300 rounded-md shadow-sm w-36">
                    <i data-feather="clock" class="flex-shrink-0 w-4 h-4 text-orange-500"></i>
                    <span id="time" class="text-xs font-medium tracking-wide"></span>
                </div>
            </div>
        </div>
        <div class="flex flex-col items-center justify-center">
            {{ $slot }}
        </div>
    </main>


    <footer class="mt-10 mb-4 text-xs text-center text-gray-500 sm:text-sm">
        <p class="italic">"Empowering Logify for a Better PN Experience"</p>
        <p class="mt-1"> 2025 Logify. All rights reserved.</p>
    </footer>

</body>

<script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        feather.replace();

        function updateDateTime() {
            const now = new Date();

            // Update Day
            const dayOptions = {
                weekday: 'long'
            };
            document.getElementById('day').textContent = now.toLocaleString('en-US', dayOptions);

            // Update Date
            const dateOptions = {
                month: 'long',
                day: 'numeric',
                year: 'numeric'
            };
            document.getElementById('date').textContent = now.toLocaleString('en-US', dateOptions);

            // Update Time
            const timeOptions = {
                hour: 'numeric',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            };
            document.getElementById('time').textContent = now.toLocaleString('en-US', timeOptions);
        }

        updateDateTime();
        setInterval(updateDateTime, 1000);

        // Initialize mode switch if user is educator
        @if (session('user') && session('user.user_role') === 'educator')
            initializeModeSwitch();
        @endif
    });

    // Dropdown functionality
    function toggleDropdown() {
        const dropdownMenu = document.getElementById('dropdownMenu');
        dropdownMenu.classList.toggle('hidden');
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const userDropdown = document.getElementById('userDropdown');
        const dropdownMenu = document.getElementById('dropdownMenu');

        if (userDropdown && !userDropdown.contains(event.target)) {
            dropdownMenu.classList.add('hidden');
        }
    });

    // Close dropdown when pressing Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            const dropdownMenu = document.getElementById('dropdownMenu');
            dropdownMenu.classList.add('hidden');
        }
    });

    @if (session('user') && session('user.user_role') === 'educator')
        // Mode switching functionality
        function initializeModeSwitch() {
            // Get current mode from server and update UI
            fetch('{{ route('role.current-mode') }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        updateModeDisplay(data.mode);
                    }
                })
                .catch(error => {
                    console.error('Error fetching current mode:', error);
                });
        }

        function updateModeDisplay(currentMode) {
            const switchText = document.getElementById('mode-switch-text');
            if (switchText) {
                if (currentMode === 'educator') {
                    switchText.textContent = 'Switch to Monitor';
                } else {
                    switchText.textContent = 'Switch to Educator';
                }
            }
        }
    @endif
</script>

@stack('scripts')

</html>
