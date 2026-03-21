<x-educator-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- DateTime (TOP RIGHT OUTSIDE FORM) --}}


    <div class="px-4 py-4">
        <div class="p-8 bg-white border border-gray-200 shadow-xl rounded-1xl">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-orange-700">ACADEMIC LOGS MONITOR</h1>
                <div class="flex items-center space-x-4">
                <a href="{{ route('educator.dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-blue-600 hover:underline">
                    <i data-feather="arrow-left" class="w-5 h-5 mr-1"></i> Back to Dashboard
                </a>
            </div>
            </div>

            {{-- Search and Filters --}}
            <div class="mb-6">
                <form action="{{ route('monitor.academic.logs') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 lg:max-w-xs">
                        <label for="dateFilter" class="block mb-1 text-sm font-medium text-gray-700">Search Name</label>
                        <input type="text" name="fullname" placeholder="Search by First Name, or Last Name..."
                            class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="flex-1 lg:max-w-xs">
                        <label for="dateFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Date</label>
                        <input type="date" id="dateFilter" name="date" value="{{ $selectedDate }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="flex-1 lg:max-w-xs">
                        <label for="batchFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by
                            Batch</label>
                        <select name="batch"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">All Batches</option>
                            @foreach ($batches as $batch)
                                <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>
                                    {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 lg:max-w-xs">
                        <label for="groupFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by
                            Group</label>
                        <select name="group"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">All Groups</option>
                            @foreach ($groups as $group)
                                <option value="{{ $group }}" {{ request('group') == $group ? 'selected' : '' }}>
                                    {{ $group }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 lg:max-w-xs">
                        <label for="statusFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by
                            Status</label>
                        <select id="statusFilter" name="status"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">All Students</option>
                            <option value="not_logged" {{ request('status') == 'not_logged' ? 'selected' : '' }}>Not Logged
                            </option>
                            <option value="not_log_out" {{ request('status') == 'not_log_out' ? 'selected' : '' }}>Not Yet
                                Got In</option>
                            <option value="not_log_in" {{ request('status') == 'not_log_in' ? 'selected' : '' }}>Not Yet
                                Got In</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late Students
                            </option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent Students
                            </option>
                        </select>
                    </div>
                    <div class="flex items-center">
                        <button type="submit"
                            class="px-5 py-2 text-sm font-semibold text-white bg-orange-500 rounded-md hover:bg-orange-600 focus:ring-2 focus:ring-orange-500">
                            Apply Filter
                        </button>
                    </div>
                </form>
            </div>

            {{-- Table --}}
            <div class="relative w-full overflow-hidden border border-gray-200 rounded-lg">
                <div class="w-full overflow-x-auto table-container">
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] table-container">
                        <table class="w-full min-w-full text-sm text-left text-gray-700">
                            <thead class="sticky top-0 z-10 text-xs font-semibold tracking-wider uppercase bg-gray-100">
                                <tr>
                                    <th class="px-6 py-3 text-black">Student ID</th>
                                    <th class="px-6 py-3 text-black">Student Name</th>
                                    <th class="px-6 py-3 text-black">Batch</th>
                                    <th class="px-6 py-3 text-black">Group</th>
                                    <th class="px-6 py-3 text-black">Date</th>
                                    <th class="px-6 py-3 text-black">Out Time</th>
                                    <th class="px-6 py-3 text-black">Remarks</th>
                                    <th class="px-6 py-3 text-black">Consideration</th>
                                    <th class="px-6 py-3 text-black">In Time</th>
                                    <th class="px-6 py-3 text-black">Remarks</th>
                                    <th class="px-6 py-3 text-black">Consideration</th>
                                    <th class="px-6 py-3 text-black">Status</tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach ($academicLogs as $index => $log)
                                    @php
                                        $rowClass = 'hover:bg-gray-50';
                                    @endphp
                                    <tr class="{{ $rowClass }}"
                                        data-batch="{{ $log->studentDetail->batch }}"
                                        data-group="{{ $log->studentDetail->group }}"
                                        data-student-id="{{ $log->studentDetail->student_id }}"
                                        data-student-fname="{{ strtolower($log->studentDetail->user->user_fname) }}"
                                        data-student-lname="{{ strtolower($log->studentDetail->user->user_lname) }}"
                                        data-student-fullname="{{ strtolower($log->studentDetail->user->user_fname . ' ' . $log->studentDetail->user->user_lname) }}">
                                        <td class="px-6 py-4">{{ $log->studentDetail->student_id }}</td>
                                        <td class="px-6 py-4">{{ $log->studentDetail->user->user_fname }}
                                            {{ $log->studentDetail->user->user_lname }}</td>
                                        <td class="px-6 py-4">{{ $log->studentDetail->batch }}</td>
                                        <td class="px-6 py-4">{{ $log->studentDetail->group }}</td>
                                        <td class="px-6 py-4">
                                            {{ \Carbon\Carbon::parse($log->academic_date)->format('F j, Y') }}</td>
                                        <td class="px-6 py-4">{{ $log->formatted_time_out }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="{{ $log->time_out_remark === 'Late' ? 'text-red-600 font-bold' : ($log->time_out_remark === 'On Time' ? 'text-blue-600 font-bold' : ($log->time_out_remark === 'Early' ? 'text-green-600 font-bold' : '')) }}">
                                                {{ $log->time_out_remark ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{-- Log Out Consideration - Educator can validate absent considerations --}}
                                            @if($log->time_out_consideration)
                                                <div class="p-3 border rounded-lg bg-gray-50"
                                                     data-monitor-name="{{ $log->time_out_monitor_name ?: 'None' }}">
                                                    <div class="font-semibold text-sm {{ $log->time_out_consideration === 'Excused' ? 'text-green-600' : ($log->time_out_consideration === 'Absent' ? 'text-gray-600' : 'text-red-600') }}">
                                                        {{ $log->time_out_consideration }}
                                                    </div>
                                                    @if($log->time_out_reason)
                                                        <div class="mt-1 text-xs italic text-gray-600">{{ $log->time_out_reason }}</div>
                                                    @endif
                                                    <div class="mt-1 text-xs font-medium text-blue-600">Set by {{ $log->time_out_monitor_name ?: 'Unknown Monitor' }}</div>
                                                </div>
                                            @else
                                                <span class="italic text-gray-500">No consideration set</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">{{ $log->formatted_time_in }}</td>
                                        <td class="px-6 py-4">
                                            <span
                                                class="{{ $log->time_in_remark === 'Late' ? 'text-red-600 font-bold' : ($log->time_in_remark === 'On Time' ? 'text-blue-600 font-bold' : ($log->time_in_remark === 'Early' ? 'text-green-600 font-bold' : '')) }}">
                                                {{ $log->time_in_remark ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            {{-- Log In Consideration - Educator can validate absent considerations --}}
                                            @if($log->educator_consideration)
                                                <div class="p-3 border rounded-lg bg-gray-50"
                                                     data-monitor-name="{{ $log->time_in_monitor_name ?: 'None' }}">
                                                    <div class="font-semibold text-sm {{ $log->educator_consideration === 'Excused' ? 'text-green-600' : ($log->educator_consideration === 'Absent' ? 'text-gray-600' : 'text-red-600') }}">
                                                        {{ $log->educator_consideration }}
                                                    </div>
                                                    @if($log->time_in_reason)
                                                        <div class="mt-1 text-xs italic text-gray-600">{{ $log->time_in_reason }}</div>
                                                    @endif
                                                    <div class="mt-1 text-xs font-medium text-blue-600">Set by {{ $log->time_in_monitor_name ?: 'Monitor' }}</div>
                                                </div>
                                            @else
                                                <span class="italic text-gray-500">No consideration set</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($log->is_manual_entry)
                                                @if($log->approval_status === 'pending')
                                                    <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                                                        Pending
                                                    </span>
                                                @elseif($log->approval_status === 'approved')
                                                    <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                        Approved
                                                    </span>
                                                @elseif($log->approval_status === 'rejected')
                                                    <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
                                                        Rejected
                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                                        Manual Entry
                                                    </span>
                                                @endif
                                            @else
                                                @if($log->time_out && $log->time_in)
                                                    <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                        Complete
                                                    </span>
                                                @elseif($log->time_out)
                                                    <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                                        Got Out

                                                    </span>
                                                @else
                                                    <span class="px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">
                                                        Not Logged
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="flex justify-end mt-6">
                        {{ $academicLogs->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Custom CSS for Full Width --}}
    <style>
        /* Override parent layout constraints for full width */
        .full-width-container {
            width: 100vw;
            max-width: none;
            margin-left: calc(-50vw + 50%);
            margin-right: calc(-50vw + 50%);
        }

        /* Ensure table cells don't wrap unnecessarily */
        .table-cell-nowrap {
            white-space: nowrap;
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

        /* Latest Activity Color Coding */
        .bg-orange-150 {
            background-color: #fed7aa;
        }
        .hover\:bg-orange-150:hover {
            background-color: #fdba74;
        }
        .bg-gray-250 {
            background-color: #e5e7eb;
        }
        .hover\:bg-gray-250:hover {
            background-color: #d1d5db;
        }

        /* Search functionality styles */
        .search-hidden {
            display: none !important;
        }

        /* Filter functionality styles */
        .filter-hidden {
            display: none !important;
        }

        /* Enhanced Dropdown Icon Styles */
        .relative button {
            border: 1px solid #fed7aa;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        .relative button:hover {
            border-color: #fb923c;
            box-shadow: 0 2px 4px 0 rgba(251, 146, 60, 0.1);
            transform: translateY(-1px);
        }

        .relative button:active {
            transform: translateY(0);
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
        }

        /* Dropdown animation */
        [id$="Filter"] {
            transition: all 0.2s ease-in-out;
            transform-origin: top;
        }

        /* Filter option hover effects */
        [onclick*="selectFilter"]:hover {
            background-color: #fff7ed !important;
            color: #ea580c !important;
        }

        /* Active filter indication */
        .filter-active {
            background-color: #fed7aa;
            color: #9a3412;
        }
    </style>

    {{-- Feather Icons --}}
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather Icons
            feather.replace();

            // Filter and search functionality
            const batchFilter = document.getElementById('batchFilter');
            const groupFilter = document.getElementById('groupFilter');
            const statusFilter = document.getElementById('statusFilter');
            const studentSearch = document.getElementById('studentSearch');
            const clearSearch = document.getElementById('clearSearch');
            const searchResults = document.getElementById('searchResults');
            const searchCount = document.getElementById('searchCount');
            const tableRows = document.querySelectorAll('tbody tr');

            // Set initial values from URL parameters
            const urlParams = new URLSearchParams(window.location.search);
            const batchParam = urlParams.get('batch');
            const groupParam = urlParams.get('group');

            if (batchParam) {
                batchFilter.value = batchParam;
                updateGroupFilter(batchParam);
            }
            if (groupParam) {
                groupFilter.value = groupParam;
            }

            // Disable group filter initially if no batch is selected
            if (!batchParam) {
                groupFilter.disabled = true;
            }

            // Store active filters
            const activeFilters = {
                'logout-time': 'all',
                'logout-remarks': 'all',
                'login-time': 'all',
                'login-remarks': 'all',
                'status': 'all'
            };

            // Dropdown filter functionality
            window.toggleDropdown = function(dropdownId, event) {
                if (event) {
                    event.stopPropagation();
                }

                const dropdown = document.getElementById(dropdownId);
                if (!dropdown) return;

                const allDropdowns = document.querySelectorAll('[id$="Filter"][class*="absolute"]:not(#statusFilter)');

                // Close all other dropdowns
                allDropdowns.forEach(d => {
                    if (d.id !== dropdownId) {
                        d.classList.add('hidden');
                    }
                });

                // Toggle current dropdown
                dropdown.classList.toggle('hidden');
            };

            // Close dropdowns when clicking outside
            document.addEventListener('click', function(event) {
                // Don't close if clicking anywhere inside a dropdown
                if (event.target.closest('[id$="Filter"][class*="absolute"]')) {
                    return;
                }

                // Don't close if clicking on the dropdown button or its SVG
                if (event.target.closest('button[onclick*="toggleDropdown"]') ||
                    event.target.closest('svg') && event.target.closest('button[onclick*="toggleDropdown"]')) {
                    return;
                }

                // Close all filter dropdowns
                const allDropdowns = document.querySelectorAll('[id$="Filter"][class*="absolute"]:not(#statusFilter)');
                allDropdowns.forEach(d => {
                    if (d && !d.classList.contains('hidden')) {
                        d.classList.add('hidden');
                    }
                });
            });

            // Selection filter function
            window.selectFilter = function(filterType, value) {
                activeFilters[filterType] = value;
                applyColumnFilters();

                // Close the dropdown after selection
                const dropdown = document.querySelector(`[id$="Filter"][class*="absolute"]:not(.hidden)`);
                if (dropdown) {
                    dropdown.classList.add('hidden');
                }
            };

            // Update filter summary display
            function updateFilterSummary(filteredStudents) {
                const summaryDiv = document.getElementById('filterSummary');
                const contentDiv = document.getElementById('filterSummaryContent');

                let summaryContent = '';
                let hasActiveFilters = false;

                // Check for logout time filters
                if (activeFilters['logout-time'] !== 'all') {
                    hasActiveFilters = true;
                    const filterType = activeFilters['logout-time'];
                    const students = filteredStudents[filterType] || [];

                    if (filterType === 'logged_out') {
                        summaryContent += `<div class="mb-2"><strong>Students who have logged out (${students.length}):</strong></div>`;
                    } else if (filterType === 'not_logged_out') {
                        summaryContent += `<div class="mb-2"><strong>Students who have NOT logged out yet (${students.length}):</strong></div>`;
                    }

                    if (students.length > 0) {
                        summaryContent += `<div class="p-2 overflow-y-auto text-xs bg-white border rounded max-h-32">`;
                        summaryContent += students.map(student => `<span class="inline-block px-2 py-1 mb-1 mr-1 text-xs text-blue-800 bg-blue-100 rounded">${student}</span>`).join('');
                        summaryContent += `</div>`;
                    } else {
                        summaryContent += `<div class="text-xs italic text-gray-500">No students match this filter</div>`;
                    }
                }

                // Check for login time filters
                if (activeFilters['login-time'] !== 'all') {
                    hasActiveFilters = true;
                    const filterType = activeFilters['login-time'];
                    const students = filteredStudents[filterType] || [];

                    if (summaryContent) summaryContent += '<div class="mt-3"></div>';

                    if (filterType === 'logged_in') {
                        summaryContent += `<div class="mb-2"><strong>Students who have logged in (${students.length}):</strong></div>`;
                    } else if (filterType === 'not_logged_in') {
                        summaryContent += `<div class="mb-2"><strong>Students who have NOT logged in yet (${students.length}):</strong></div>`;
                    }

                    if (students.length > 0) {
                        summaryContent += `<div class="p-2 overflow-y-auto text-xs bg-white border rounded max-h-32">`;
                        summaryContent += students.map(student => `<span class="inline-block px-2 py-1 mb-1 mr-1 text-xs text-green-800 bg-green-100 rounded">${student}</span>`).join('');
                        summaryContent += `</div>`;
                    } else {
                        summaryContent += `<div class="text-xs italic text-gray-500">No students match this filter</div>`;
                    }
                }

                // Show or hide the summary
                if (hasActiveFilters && summaryContent) {
                    contentDiv.innerHTML = summaryContent;
                    summaryDiv.classList.remove('hidden');
                } else {
                    summaryDiv.classList.add('hidden');
                }
            }

            // Clear all filters
            function clearAllFilters() {
                // Reset all active filters
                activeFilters = {
                    'logout-time': 'all',
                    'logout-remarks': 'all',
                    'login-time': 'all',
                    'login-remarks': 'all',
                    'status': 'all'
                };

                // Update filter button texts
                document.getElementById('logOutTimeFilterText').textContent = 'Show All';
                document.getElementById('logOutRemarksFilterText').textContent = 'Show All';
                document.getElementById('logInTimeFilterText').textContent = 'Show All';
                document.getElementById('logInRemarksFilterText').textContent = 'Show All';
                document.getElementById('statusFilterText').textContent = 'Show All';

                // Hide summary
                document.getElementById('filterSummary').classList.add('hidden');

                // Reapply filters (which will show all rows)
                applyColumnFilters();
            }

            // Search functionality
            function performSearch() {
                const searchTerm = studentSearch.value.toLowerCase().trim();

                if (searchTerm === '') {
                    clearSearch.classList.add('hidden');
                    searchResults.classList.add('hidden');

                    // Reset all rows to visible when search is empty
                    tableRows.forEach(row => {
                        row.classList.remove('search-hidden');
                        row.style.display = '';
                    });

                    applyFilters();
                    return;
                }

                clearSearch.classList.remove('hidden');
                searchResults.classList.remove('hidden');

                let matchCount = 0;
                let firstMatch = null;

                tableRows.forEach(row => {
                    const studentId = row.getAttribute('data-student-id') || '';
                    const firstName = row.getAttribute('data-student-fname') || '';
                    const lastName = row.getAttribute('data-student-lname') || '';
                    const fullName = row.getAttribute('data-student-fullname') || '';

                    const isMatch = studentId.toLowerCase().includes(searchTerm) ||
                                   firstName.includes(searchTerm) ||
                                   lastName.includes(searchTerm) ||
                                   fullName.includes(searchTerm);

                    if (isMatch) {
                        matchCount++;
                        if (!firstMatch) firstMatch = row;
                        row.classList.remove('search-hidden');
                    } else {
                        row.classList.add('search-hidden');
                    }
                });

                searchCount.textContent = matchCount;

                // Scroll to first match
                if (firstMatch) {
                    firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }

                // Apply filters after search
                applyFilters();
            }

            function clearSearchFunction() {
                studentSearch.value = '';
                clearSearch.classList.add('hidden');
                searchResults.classList.add('hidden');

                // Remove search-hidden class and reset all rows to visible
                tableRows.forEach(row => {
                    row.classList.remove('search-hidden');
                    // Reset row display to empty string to show all rows
                    row.style.display = '';
                });

                // Apply filters only for batch/group, not search
                applyFilters();
            }

            function updateGroupFilter(selectedBatch) {
                // Clear and disable group filter if no batch is selected
                if (!selectedBatch) {
                    groupFilter.value = '';
                    groupFilter.disabled = true;
                    return;
                }

                // Enable group filter
                groupFilter.disabled = false;

                // Get unique groups for the selected batch
                const availableGroups = new Set();
                tableRows.forEach(row => {
                    if (row.getAttribute('data-batch') === selectedBatch) {
                        availableGroups.add(row.getAttribute('data-group'));
                    }
                });

                // Update group filter options
                groupFilter.innerHTML = '<option value="">All Groups</option>';
                Array.from(availableGroups).sort().forEach(group => {
                    const option = document.createElement('option');
                    option.value = group;
                    option.textContent = group;
                    if (group === groupParam) {
                        option.selected = true;
                    }
                    groupFilter.appendChild(option);
                });
            }

            // Apply column filters
            function applyColumnFilters() {
                // Collect filtered students for summary
                const filteredStudents = {
                    'logged_out': [],
                    'not_logged_out': [],
                    'logged_in': [],
                    'not_logged_in': []
                };

                // Apply filters to table rows
                tableRows.forEach(row => {
                    let showRow = true;
                    const studentName = row.cells[1] ? row.cells[1].textContent.trim() : '';
                    const studentId = row.cells[0] ? row.cells[0].textContent.trim() : '';

                    // Check logout time filter
                    if (activeFilters['logout-time'] !== 'all') {
                        const logoutTimeCell = row.cells[5]; // Log Out Time column (correct index)
                        const logoutTimeText = logoutTimeCell ? logoutTimeCell.textContent.trim() : '';
                        // Check if student has logged out (not showing '—' which means no time_out)
                        const hasLogout = logoutTimeText && logoutTimeText !== '—' && logoutTimeText !== '';
                        const filter = activeFilters['logout-time'];
                        const matchesFilter = (filter === 'logged_out' && hasLogout) ||
                                            (filter === 'not_logged_out' && !hasLogout);

                        // Collect students for summary
                        if (filter === 'logged_out' && hasLogout) {
                            filteredStudents.logged_out.push(`${studentName} (${studentId})`);
                        } else if (filter === 'not_logged_out' && !hasLogout) {
                            filteredStudents.not_logged_out.push(`${studentName} (${studentId})`);
                        }

                        if (!matchesFilter) showRow = false;
                    }

                    // Check logout remarks filter
                    if (activeFilters['logout-remarks'] !== 'all' && showRow) {
                        const logoutRemarksCell = row.cells[6]; // Log Out Remarks column (correct index)
                        const remarksText = logoutRemarksCell ? logoutRemarksCell.textContent.trim() : '';
                        const filter = activeFilters['logout-remarks'];
                        if (!remarksText.includes(filter)) showRow = false;
                    }

                    // Check login time filter
                    if (activeFilters['login-time'] !== 'all' && showRow) {
                        const loginTimeCell = row.cells[8]; // Log In Time column (correct index)
                        const loginTimeText = loginTimeCell ? loginTimeCell.textContent.trim() : '';
                        // Check if student has logged in (not showing '—' which means no time_in)
                        const hasLogin = loginTimeText && loginTimeText !== '—' && loginTimeText !== '';
                        const filter = activeFilters['login-time'];

                        // Collect students for summary
                        if (filter === 'logged_in' && hasLogin) {
                            filteredStudents.logged_in.push(`${studentName} (${studentId})`);
                        } else if (filter === 'not_logged_in' && !hasLogin) {
                            filteredStudents.not_logged_in.push(`${studentName} (${studentId})`);
                        }

                        const matchesFilter = (filter === 'logged_in' && hasLogin) ||
                                            (filter === 'not_logged_in' && !hasLogin);
                        if (!matchesFilter) showRow = false;
                    }

                    // Check login remarks filter
                    if (activeFilters['login-remarks'] !== 'all' && showRow) {
                        const loginRemarksCell = row.cells[9]; // Log In Remarks column (correct index)
                        const remarksText = loginRemarksCell ? loginRemarksCell.textContent.trim() : '';
                        const filter = activeFilters['login-remarks'];
                        if (!remarksText.includes(filter)) showRow = false;
                    }

                    // Check status filter
                    if (activeFilters['status'] !== 'all' && showRow) {
                        const statusCell = row.cells[12]; // Status column
                        const statusText = statusCell ? statusCell.textContent.trim().toLowerCase() : '';
                        const filter = activeFilters['status'];
                        let matchesFilter = false;

                        switch(filter) {
                            case 'pending': matchesFilter = statusText.includes('pending'); break;
                            case 'approved': matchesFilter = statusText.includes('approved'); break;
                            case 'rejected': matchesFilter = statusText.includes('rejected'); break;
                        }
                        if (!matchesFilter) showRow = false;
                    }

                    // Show/hide row
                    if (showRow) {
                        row.style.display = '';
                        row.classList.remove('filter-hidden');
                    } else {
                        row.style.display = 'none';
                        row.classList.add('filter-hidden');
                    }
                });

                // Update filter summary
                updateFilterSummary(filteredStudents);

                // Update existing filters to work with column filters
                applyFilters();
            }

            function applyFilters() {
                const selectedBatch = batchFilter.value;
                const selectedGroup = groupFilter.value;
                const selectedStatus = statusFilter.value;
                const hasSearchTerm = studentSearch.value.trim() !== '';

                // Update URL with current filters
                const url = new URL(window.location.href);
                if (selectedBatch) {
                    url.searchParams.set('batch', selectedBatch);
                } else {
                    url.searchParams.delete('batch');
                }
                if (selectedGroup) {
                    url.searchParams.set('group', selectedGroup);
                } else {
                    url.searchParams.delete('group');
                }
                window.history.replaceState({}, '', url);

                // Filter and reapply color coding
                let visibleRows = [];
                tableRows.forEach(row => {
                    const rowBatch = row.getAttribute('data-batch');
                    const rowGroup = row.getAttribute('data-group');

                    let shouldShow = true;

                    if (selectedBatch) {
                        shouldShow = rowBatch === selectedBatch;
                        if (selectedGroup) {
                            shouldShow = shouldShow && (rowGroup === selectedGroup);
                        }
                    }

                    // Apply status filter
                    if (selectedStatus && shouldShow) {
                        shouldShow = shouldShow && checkStudentStatus(row, selectedStatus);
                    }

                    // Only apply search filter if there's an active search term
                    if (hasSearchTerm) {
                        shouldShow = shouldShow && !row.classList.contains('search-hidden');
                    }

                    // Check if row is hidden by column filters
                    if (row.classList.contains('filter-hidden')) {
                        shouldShow = false;
                    }

                    row.style.display = shouldShow ? '' : 'none';

                    if (shouldShow) {
                        visibleRows.push(row);
                    }
                });

                // First, remove all color classes from ALL rows (visible and hidden)
                tableRows.forEach(row => {
                    row.className = row.className.replace(/bg-orange-100|hover:bg-orange-150|border-l-4|border-orange-500|bg-gray-200|hover:bg-gray-250|border-gray-500|bg-yellow-800|bg-opacity-20|hover:bg-yellow-800|hover:bg-opacity-30|border-yellow-800/g, '').trim();

                    // Add base classes
                    if (!row.className.includes('hover:bg-gray-50')) {
                        row.className += ' hover:bg-gray-50';
                    }
                });

                // Filter visible rows that have actual activity (time_out or time_in)
                let activeVisibleRows = visibleRows.filter(row => {
                    const timeOutCell = row.cells[5]; // Log Out Time column
                    const timeInCell = row.cells[8]; // Log In Time column

                    // Check for actual time values using timestamp pattern detection
                    const timeOutText = timeOutCell ? timeOutCell.textContent.trim() : '';
                    const timeInText = timeInCell ? timeInCell.textContent.trim() : '';

                    // Use regex to detect actual timestamp patterns (e.g., "2:30 PM", "14:30", "02:30:45")
                    const timePattern = /\d{1,2}:\d{2}(\s*(AM|PM|am|pm))?/;

                    // Only consider it as activity if it contains actual timestamp
                    const hasTimeOut = timeOutText &&
                                      timeOutText !== '—' &&
                                      timeOutText !== '' &&
                                      timePattern.test(timeOutText);

                    const hasTimeIn = timeInText &&
                                     timeInText !== '—' &&
                                     timeInText !== '' &&
                                     timePattern.test(timeInText);

                    return hasTimeOut || hasTimeIn;
                });

                // Apply color coding ONLY to active visible rows
                activeVisibleRows.forEach((row, activeIndex) => {
                    if (activeIndex === 0) {
                        row.className += ' bg-orange-100 hover:bg-orange-100 border-l-4 border-orange-500'; // 1st latest - Orange
                    } else if (activeIndex === 1 && activeVisibleRows.length >= 2) {
                        row.className += ' bg-gray-200 hover:bg-gray-200 border-l-4 border-gray-500'; // 2nd latest - Silver
                    } else if (activeIndex === 2 && activeVisibleRows.length >= 3) {
                        row.className += ' bg-gray-100 hover:bg-gray-100 border-l-4 border-gray-500'; // 3rd latest - Brown
                    }
                });
            }

            // Search event listeners
            studentSearch.addEventListener('input', performSearch);
            clearSearch.addEventListener('click', clearSearchFunction);

            // When batch changes, update group filter and apply filters
            batchFilter.addEventListener('change', function() {
                const selectedBatch = this.value;
                updateGroupFilter(selectedBatch);
                applyFilters();
            });

            // When group changes, apply filters
            groupFilter.addEventListener('change', applyFilters);

            // When status changes, apply filters
            statusFilter.addEventListener('change', applyFilters);

            // Function to check student status for filtering
            function checkStudentStatus(row, statusFilter) {
                const timeOutCell = row.cells[5]; // Log Out Time column
                const timeInCell = row.cells[8]; // Log In Time column
                const timeOutRemarkCell = row.cells[6]; // Log Out Remarks column
                const timeInRemarkCell = row.cells[9]; // Log In Remarks column
                const timeOutConsiderationCell = row.cells[7]; // Log Out Consideration column
                const timeInConsiderationCell = row.cells[10]; // Log In Consideration column

                const timeOutText = timeOutCell ? timeOutCell.textContent.trim() : '';
                const timeInText = timeInCell ? timeInCell.textContent.trim() : '';
                const timeOutRemark = timeOutRemarkCell ? timeOutRemarkCell.textContent.trim() : '';
                const timeInRemark = timeInRemarkCell ? timeInRemarkCell.textContent.trim() : '';
                const timeOutConsideration = timeOutConsiderationCell ? timeOutConsiderationCell.textContent.trim() : '';
                const timeInConsideration = timeInConsiderationCell ? timeInConsiderationCell.textContent.trim() : '';

                // Use regex to detect actual timestamp patterns
                const timePattern = /\d{1,2}:\d{2}(\s*(AM|PM|am|pm))?/;
                const hasTimeOut = timeOutText && timePattern.test(timeOutText) && timeOutText !== '—';
                const hasTimeIn = timeInText && timePattern.test(timeInText) && timeInText !== '—';

                switch (statusFilter) {
                    case 'not-logged-out':
                        // Students who haven't logged out yet (no time_out)
                        return !hasTimeOut;

                    case 'not-logged-in':
                        // Students who haven't logged in yet (no time_in)
                        return !hasTimeIn;

                    case 'late':
                        // Students who are late (either log out or log in)
                        return (timeOutRemark === 'Late') || (timeInRemark === 'Late');

                    case 'absent':
                        // Students who are marked as absent
                        return timeOutConsideration.includes('Absent') || timeInConsideration.includes('Absent');

                    default:
                        return true;
                }
            }

            // Apply initial filters (this will also apply initial colors)
            applyFilters();
        });

        function validateConsideration(event, hasTimeIn, timeInRemark) {
            const form = event.target;
            const errorDiv = form.nextElementSibling;
            const select = form.querySelector('select');
            const selectedValue = select.value;

            if (!hasTimeIn) {
                event.preventDefault();
                errorDiv.textContent = "Cannot set consideration: Student must get in first";

                errorDiv.classList.remove('hidden');
                return false;
            }

            if (timeInRemark !== 'Late') {
                event.preventDefault();
                errorDiv.textContent = "Cannot set consideration: Student must be late to set any consideration";
                errorDiv.classList.remove('hidden');
                return false;
            }

            errorDiv.classList.add('hidden');
            return true;
        }

        function handleTimeOutConsiderationSubmit(event, logId, hasTimeOut, timeOutRemark) {
            event.preventDefault();
            console.log('Time Out Form submission started', { logId, hasTimeOut, timeOutRemark });

            // Validate first
            if (!validateTimeOutConsideration(event, hasTimeOut, timeOutRemark)) {
                console.log('Time Out Validation failed');
                return false;
            }

            const form = event.target;
            const formData = new FormData(form);
            const errorDiv = document.getElementById(`timeout-error-message-${logId}`);
            const successDiv = document.getElementById(`timeout-success-message-${logId}`);
            const submitButton = form.querySelector('button[type="submit"]');
            const select = form.querySelector('select');

            // Disable the form while submitting
            submitButton.disabled = true;
            select.disabled = true;

            // Clear previous messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            // Get CSRF token from meta tag
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    successDiv.textContent = data.message;
                    successDiv.classList.remove('hidden');
                    submitButton.disabled = true;
                    select.disabled = true;
                    select.value = formData.get('educator_consideration');
                } else {
                    errorDiv.textContent = data.message || 'An error occurred while updating the consideration.';
                    errorDiv.classList.remove('hidden');
                    submitButton.disabled = false;
                    select.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.textContent = 'An error occurred while updating the consideration.';
                errorDiv.classList.remove('hidden');
                submitButton.disabled = false;
                select.disabled = false;
            });

            return false;
        }

        function handleTimeInConsiderationSubmit(event, logId, hasTimeIn, timeInRemark) {
            event.preventDefault();
            console.log('Time In Form submission started', { logId, hasTimeIn, timeInRemark });

            // Validate first
            if (!validateConsideration(event, hasTimeIn, timeInRemark)) {
                console.log('Time In Validation failed');
                return false;
            }

            const form = event.target;
            const formData = new FormData(form);
            const errorDiv = document.getElementById(`timein-error-message-${logId}`);
            const successDiv = document.getElementById(`timein-success-message-${logId}`);
            const submitButton = form.querySelector('button[type="submit"]');
            const select = form.querySelector('select');

            // Disable the form while submitting
            submitButton.disabled = true;
            select.disabled = true;

            // Clear previous messages
            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            // Get CSRF token from meta tag
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    successDiv.textContent = data.message;
                    successDiv.classList.remove('hidden');
                    submitButton.disabled = true;
                    select.disabled = true;
                    select.value = formData.get('educator_consideration');
                } else {
                    errorDiv.textContent = data.message || 'An error occurred while updating the consideration.';
                    errorDiv.classList.remove('hidden');
                    submitButton.disabled = false;
                    select.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.textContent = 'An error occurred while updating the consideration.';
                errorDiv.classList.remove('hidden');
                submitButton.disabled = false;
                select.disabled = false;
            });

            return false;
        }

        function validateTimeOutConsideration(event, hasTimeOut, timeOutRemark) {
            const form = event.target;
            const errorDiv = form.nextElementSibling;
            const select = form.querySelector('select');

            if (!hasTimeOut) {
                event.preventDefault();
                errorDiv.textContent = "Cannot set consideration: Student must get out first";

                errorDiv.classList.remove('hidden');
                return false;
            }

            if (timeOutRemark !== 'Late') {
                event.preventDefault();
                errorDiv.textContent = "Cannot set consideration: Student must be late to set any consideration";
                errorDiv.classList.remove('hidden');
                return false;
            }

            errorDiv.classList.add('hidden');
            return true;
        }

        // Validate absent consideration - Educator function
        function validateAbsentEducator(event, logId, considerationType, validation) {
            console.log('validateAbsentEducator called with:', { logId, considerationType, validation });

            const formData = new FormData();
            formData.append('validation', validation);
            formData.append('consideration_type', considerationType);
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            fetch('{{ route('academic.absent-validation', ':id') }}'.replace(':id', logId), {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Find the validation buttons container
                    const buttonContainer = document.getElementById(`validation-buttons-${logId}-${considerationType}`);
                    if (!buttonContainer) {
                        console.error('Button container not found:', `validation-buttons-${logId}-${considerationType}`);
                        alert('Error: Could not find validation buttons container');
                        return;
                    }

                    // Update the display to show validation status
                    const validationText = validation === 'valid' ? 'Valid Absence' : 'Invalid Absence';
                    const validationColor = validation === 'valid' ? 'text-green-600' : 'text-red-600';
                    const educatorName = data.educator_name || 'Current Educator';

                    buttonContainer.outerHTML = `
                        <div class="mt-2">
                            <div class="text-xs font-bold ${validationColor}">
                                ${validationText}
                            </div>
                            <div class="mt-1 text-xs font-medium text-purple-600">
                                Validated by ${educatorName}
                            </div>
                        </div>
                    `;

                    // Auto-sync: Update the other validation if it was synced by the server
                    if (data.auto_synced) {
                        const otherType = considerationType === 'time_out' ? 'time_in' : 'time_out';
                        const otherButtonContainer = document.getElementById(`validation-buttons-${logId}-${otherType}`);

                        if (otherButtonContainer) {
                            otherButtonContainer.outerHTML = `
                                <div class="mt-2">
                                    <div class="text-xs font-bold ${validationColor}">
                                        ${validationText} (Auto-synced)
                                    </div>
                                    <div class="mt-1 text-xs font-medium text-purple-600">
                                        Validated by ${educatorName}
                                    </div>
                                </div>
                            `;
                        }
                    }

                    // Show success message from server
                    alert(data.message || 'Absent validation updated successfully!');
                } else {
                    alert('Failed to update validation: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while updating the validation.');
            });
        }
    </script>
</x-educator-layout>
