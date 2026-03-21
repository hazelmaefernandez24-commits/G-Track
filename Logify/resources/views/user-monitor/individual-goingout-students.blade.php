<x-monitorLayout>
    <div class="w-full min-h-screen bg-gray-50">
        <!-- Header Section -->
        <div class="w-full bg-white border-b border-gray-200">
            <div class="w-full px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <a href="{{ route('monitor.dashboard') }}"
                           class="flex items-center gap-2 px-4 py-2 text-blue-600 transition-all duration-200 border border-blue-200 rounded-lg bg-blue-50 hover:bg-blue-100 hover:border-blue-300">
                            <i data-feather="arrow-left" class="w-4 h-4"></i>
                            <span class="font-medium">Back to Dashboard</span>
                        </a>
                        <div class="w-px h-8 bg-gray-300"></div>
                        <h1 class="text-2xl font-bold text-orange-600">Going Home Schedule</h1>
                    </div>
                    <div class="flex items-center gap-4">

                    </div>
                    <div class="flex items-center gap-4">
                        <!-- Navigation removed - now accessible via sidebar -->
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="w-full px-6 py-6">
            <!-- Search and Filter Section -->
            <div class="mb-6 bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex flex-col gap-4 lg:flex-row">
                        <!-- Search Input -->
                        <div class="flex-1">
                            <label for="search" class="block mb-2 text-sm font-medium text-gray-700">
                                <i data-feather="search" class="inline w-4 h-4 mr-1"></i>
                                Search by ID or Name
                            </label>
                            <input type="text"
                                   id="search-input"
                                   placeholder="Search by Student ID, First Name, or Last Name"
                                   class="w-full px-4 py-3 transition-all duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        </div>

                        <!-- Batch Filter -->
                        <div class="lg:w-48">
                            <label for="batch-filter" class="block mb-2 text-sm font-medium text-gray-700">
                                <i data-feather="users" class="inline w-4 h-4 mr-1"></i>
                                Batch
                            </label>
                            <select id="batch-filter"
                                    class="w-full px-4 py-3 transition-all duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">All Batches</option>
                                @foreach($batches as $batch)
                                    <option value="{{ $batch }}">{{ $batch }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Group Filter -->
                        <div class="lg:w-48">
                            <label for="group-filter" class="block mb-2 text-sm font-medium text-gray-700">
                                <i data-feather="tag" class="inline w-4 h-4 mr-1"></i>
                                Group
                            </label>
                            <select id="group-filter"
                                    class="w-full px-4 py-3 transition-all duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">All Groups</option>
                                @foreach($groups as $group)
                                    <option value="{{ $group }}">{{ $group }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Schedule Status Filter -->
                        <div class="lg:w-48">
                            <label for="schedule-filter" class="block mb-2 text-sm font-medium text-gray-700">
                                <i data-feather="calendar" class="inline w-4 h-4 mr-1"></i>
                                Schedule Status
                            </label>
                            <select id="schedule-filter"
                                    class="w-full px-4 py-3 transition-all duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                <option value="">All Students</option>
                                <option value="with-schedule">With Schedule</option>
                                <option value="without-schedule">Without Schedule</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Selection Section -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-semibold text-gray-800">
                            <i data-feather="users" class="inline w-5 h-5 mr-2 text-orange-500"></i>
                            Select Students for Going Home Schedule
                        </h2>
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-600">
                                Total: <span class="font-semibold">{{ $students->count() }}</span> students
                            </span>
                            <span class="text-sm text-gray-600">
                                Selected: <span id="selected-count" class="font-semibold text-orange-600">0</span>
                            </span>
                        </div>
                    </div>

                    @if($students->count() > 0)
                        <form id="schedule-form" method="POST" action="{{ route('monitor.batch-goingout.create') }}">
                            @csrf
                            <!-- Selection Controls -->
                            <div class="p-4 mb-4 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" id="select-all" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                            <span class="text-sm font-medium text-gray-700">Select All</span>
                                        </label>
                                    </div>
                                    <button type="button"
                                            id="proceed-btn"
                                            disabled
                                            class="px-6 py-2 font-medium text-white transition-all duration-200 bg-orange-600 rounded-lg hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                        <i data-feather="home" class="inline w-4 h-4 mr-1"></i>
                                        Set Going Home Schedule
                                    </button>
                                </div>
                            </div>

                            <!-- Students Table -->
                            <div class="overflow-hidden border border-gray-200 rounded-lg">
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm text-left text-gray-700">
                                        <thead class="text-xs font-semibold tracking-wider uppercase bg-gray-100">
                                            <tr>
                                                <th class="px-6 py-3 text-black">Student ID</th>
                                                <th class="px-6 py-3 text-black">Name</th>
                                                <th class="px-6 py-3 text-black">Batch</th>
                                                <th class="px-6 py-3 text-black">Group</th>
                                                <th class="px-6 py-3 text-center text-black">Current Schedule</th>
                                                <th class="px-6 py-3 text-center text-black">Select</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200">
                                            @foreach($students as $student)
                                                @php
                                                    $studentSchedules = $existingSchedules->get($student->student_id, collect());
                                                    $hasSchedule = $studentSchedules->isNotEmpty();
                                                @endphp
                                                <tr class="transition-colors duration-150 hover:bg-gray-50" data-has-schedule="{{ $hasSchedule ? 'true' : 'false' }}">
                                                    <td class="px-6 py-4 font-medium text-gray-900">
                                                        {{ $student->student_id }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        {{ $student->user->user_fname }} {{ $student->user->user_lname }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                                            {{ $student->batch }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                            {{ $student->group }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        @if($hasSchedule)
                                                            <div class="flex flex-wrap justify-center gap-1">
                                                                @foreach($studentSchedules as $schedule)
                                                                    <button type="button"
                                                                        class="px-2 py-1 text-xs font-medium text-orange-800 transition-colors bg-orange-100 rounded-full cursor-pointer schedule-day-btn hover:bg-orange-200"
                                                                        data-student-id="{{ $student->student_id }}"
                                                                        data-date-start="{{ \Carbon\Carbon::parse($schedule->start_date)->format('M d, Y') ?? '' }}"
                                                                        data-date-end="{{ \Carbon\Carbon::parse($schedule->end_date)->format('M d, Y') ?? '' }}"
                                                                        data-time-out="{{ \Carbon\Carbon::parse($schedule->time_out)->format('h:i A') ?? '' }}"
                                                                        data-time-in="{{ \Carbon\Carbon::parse($schedule->time_in)->format('h:i A') ?? '' }}"
                                                                        title="Click to view schedule details"
                                                                        >
                                                                        Has Schedule
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">
                                                                No Schedule
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="checkbox"
                                                                   name="student_ids[]"
                                                                   value="{{ $student->student_id }}"
                                                                   class="w-4 h-4 text-orange-600 border-gray-300 rounded student-checkbox focus:ring-orange-500">
                                                        </label>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Going Home Schedule Section (Initially Hidden) -->
                            <div id="schedule-section" class="hidden p-6 mt-8 border border-orange-200 rounded-lg bg-orange-50">
                                <h3 class="mb-4 text-lg font-semibold text-gray-800">
                                    <i data-feather="home" class="inline w-5 h-5 mr-2 text-orange-500"></i>
                                    Set Going Home Schedule
                                </h3>
                                <p class="mb-6 text-gray-600">Set up a going home period where students get out on the departure date and get in on the return date.</p>



                                <!-- Going Home Period Form -->
                                <div id="goingHomeStep" class="p-6 bg-white border border-orange-200 rounded-lg">
                                    <div class="mb-4">
                                        <h4 class="text-lg font-semibold text-orange-800">
                                            <i data-feather="home" class="inline w-5 h-5 mr-2"></i>
                                            Going Home Period Details
                                        </h4>
                                    </div>

                                    <p class="mb-6 text-gray-600">Set up a going home period where students get out on the departure date and get in on the return date.</p>

                                    <div class="mb-6">
                                        <label class="block mb-2 text-sm font-medium text-gray-700">
                                            <i data-feather="tag" class="inline w-4 h-4 mr-1"></i>
                                            Schedule Name
                                        </label>
                                        <input type="text" name="schedule_name" id="individualScheduleName"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                                    </div>

                                    <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                                        <!-- Log Out (Start Date) -->
                                        <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                                            <h5 class="mb-3 font-semibold text-red-800">
                                                <i data-feather="log-out" class="inline w-4 h-4 mr-1"></i>
                                                Get Out (Departure)
                                            </h5>
                                            <div class="space-y-3">
                                                <div>
                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Date</label>
                                                    <input type="date" name="start_date" id="individualStartDate"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                                </div>
                                                <div>
                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Time</label>
                                                    <input type="time" name="time_out" id="individualTimeOut"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500">
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Log In (End Date) -->
                                        <div class="p-4 border border-blue-200 rounded-lg bg-blue-50">
                                            <h5 class="mb-3 font-semibold text-blue-800">
                                                <i data-feather="log-in" class="inline w-4 h-4 mr-1"></i>
                                                Get In (Return)
                                            </h5>
                                            <div class="space-y-3">
                                                <div>
                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Date</label>
                                                    <input type="date" name="end_date" id="individualEndDate"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                                <div>
                                                    <label class="block mb-1 text-sm font-medium text-gray-700">Time</label>
                                                    <input type="time" name="time_in" id="individualTimeIn"
                                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Period Summary -->
                                    <div id="periodSummary" class="hidden p-4 mb-6 border border-gray-200 rounded-lg bg-gray-50">
                                        <h6 class="mb-2 font-medium text-gray-800">
                                            <i data-feather="info" class="inline w-4 h-4 mr-1"></i>
                                            Period Summary
                                        </h6>
                                        <div id="periodSummaryContent" class="text-sm text-gray-600">
                                            <!-- Summary will be shown here -->
                                        </div>
                                    </div>

                                    <div class="flex justify-end">
                                        <button type="button" id="submitGoingHomeSchedule" disabled
                                                class="px-8 py-3 font-medium text-white transition-all duration-200 bg-orange-600 rounded-lg hover:bg-orange-700 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                            <i data-feather="save" class="inline w-4 h-4 mr-2"></i>
                                            Set Going Home Period
                                        </button>
                                    </div>

                                    <!-- Hidden input to indicate schedule type -->
                                    <input type="hidden" name="individual_schedule_type" id="scheduleTypeInput" value="">
                                </div>
                            </div>
                        </form>
                    @else
                        <div class="py-12 text-center">
                            <div class="mb-4 text-gray-400">
                                <i data-feather="users" class="w-16 h-16 mx-auto"></i>
                            </div>
                            <h3 class="mb-2 text-lg font-medium text-gray-900">No students found</h3>
                            <p class="text-gray-600">Try adjusting your search criteria or filters.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>


    </div>

    <!-- Schedule Details Modal -->
    <div id="schedule-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="w-full max-w-md bg-white rounded-lg shadow-xl">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">
                            <i data-feather="calendar" class="inline w-5 h-5 mr-2 text-orange-500"></i>
                            Schedule Details
                        </h3>
                        <button type="button" id="close-modal" class="text-gray-400 hover:text-gray-600">
                            <i data-feather="x" class="w-5 h-5"></i>
                        </button>
                    </div>

                    <div id="modal-content" class="space-y-4">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- All Batch Schedules Modal -->
    <div id="all-batch-schedules-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="text-xl font-semibold text-gray-800">
                            <i data-feather="calendar" class="inline w-6 h-6 mr-2 text-orange-500"></i>
                            All Batch Going Home Schedules
                        </h3>
                        <button type="button" id="close-batch-schedules-modal" class="text-gray-400 hover:text-gray-600">
                            <i data-feather="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                    <p class="mt-2 text-sm text-gray-600">View all upcoming and active batch schedules</p>
                </div>

                <div class="p-6 overflow-y-auto max-h-[70vh]">
                    <div id="all-batch-schedules-content">
                        <!-- Content will be populated by JavaScript -->
                        <div class="py-8 text-center text-gray-500">
                            <i data-feather="loader" class="w-8 h-8 mx-auto mb-2 animate-spin"></i>
                            <p>Loading batch schedules...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="messageModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg">
            <div id="messageModalHeader"
                class="px-4 py-3 text-white rounded-t-lg">
                <h3 class="text-lg font-semibold" id="messageModalTitle"></h3>
            </div>
            <div class="px-4 py-5">
                <p id="messageModalBody" class="text-gray-700"></p>
            </div>
            <div class="flex justify-end px-4 py-3 border-t">
                <button type="button" onclick="closeMessageModal()"
                        class="px-4 py-2 text-sm font-medium text-white bg-gray-600 rounded-lg hover:bg-gray-700 focus:outline-none">
                    Close
                </button>
            </div>
        </div>
    </div>

    <style>
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded fired');
            const selectAllCheckbox = document.getElementById('select-all');
            const studentCheckboxes = document.querySelectorAll('.student-checkbox');
            const selectedCountSpan = document.getElementById('selected-count');
            const proceedBtn = document.getElementById('proceed-btn');

            // Search and filter elements
            const searchInput = document.getElementById('search-input');
            const batchFilter = document.getElementById('batch-filter');
            const groupFilter = document.getElementById('group-filter');
            const scheduleFilter = document.getElementById('schedule-filter');
            const studentRows = document.querySelectorAll('tbody tr');

            // Modal elements
            const scheduleModal = document.getElementById('schedule-modal');
            const closeModalBtn = document.getElementById('close-modal');
            const modalContent = document.getElementById('modal-content');

            // Update selected count and proceed button state
            function updateSelectionState() {
                // Count all selected students (both visible and hidden)
                const allSelectedCount = document.querySelectorAll('.student-checkbox:checked').length;

                // Count only visible selected students for select-all logic
                const visibleRows = Array.from(studentRows).filter(row => row.style.display !== 'none');
                const visibleSelectedCount = visibleRows.filter(row => {
                    const checkbox = row.querySelector('.student-checkbox');
                    return checkbox && checkbox.checked;
                }).length;

                // Update selected count display (show total selected, including hidden)
                const hiddenSelectedCount = allSelectedCount - visibleSelectedCount;
                if (hiddenSelectedCount > 0) {
                    selectedCountSpan.innerHTML = `${allSelectedCount} <span class="text-xs text-orange-600">(${hiddenSelectedCount} hidden)</span>`;
                } else {
                    selectedCountSpan.textContent = allSelectedCount;
                }
                proceedBtn.disabled = allSelectedCount === 0;

                // Update select all checkbox state based on visible students only
                const visibleCheckboxes = visibleRows.map(row => row.querySelector('.student-checkbox')).filter(cb => cb);

                if (visibleSelectedCount === 0) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = false;
                } else if (visibleSelectedCount === visibleCheckboxes.length && visibleCheckboxes.length > 0) {
                    selectAllCheckbox.indeterminate = false;
                    selectAllCheckbox.checked = true;
                } else {
                    selectAllCheckbox.indeterminate = true;
                }
            }

            // Update going home submit button state
            function updateGoingHomeSubmitState() {
                const submitGoingHomeBtn = document.getElementById('submitGoingHomeSchedule');
                if (submitGoingHomeBtn) {
                    const startDate = document.getElementById('individualStartDate').value;
                    const endDate = document.getElementById('individualEndDate').value;
                    const scheduleName = document.getElementById('individualScheduleName').value.trim();
                    const timeOut = document.getElementById('individualTimeOut').value;
                    const timeIn = document.getElementById('individualTimeIn').value;

                    submitGoingHomeBtn.disabled = !startDate || !endDate || !scheduleName || !timeOut || !timeIn;
                }
            }

            // Live search and filter functionality
            function filterStudents() {
                const searchTerm = searchInput.value.toLowerCase().trim();
                const selectedBatch = batchFilter.value;
                const selectedGroup = groupFilter.value;
                const selectedScheduleStatus = scheduleFilter.value;
                let visibleCount = 0;
                let visibleSelectedCount = 0;

                studentRows.forEach(row => {
                    const studentId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                    const studentName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const studentBatchElement = row.querySelector('td:nth-child(3) span');
                    const studentGroupElement = row.querySelector('td:nth-child(4) span');
                    const studentBatch = studentBatchElement ? studentBatchElement.textContent.trim() : '';
                    const studentGroup = studentGroupElement ? studentGroupElement.textContent.trim() : '';
                    const hasSchedule = row.getAttribute('data-has-schedule') === 'true';
                    const checkbox = row.querySelector('.student-checkbox');

                    // Check search criteria
                    const matchesSearch = searchTerm === '' ||
                                        studentId.includes(searchTerm) ||
                                        studentName.includes(searchTerm);

                    // Check batch filter
                    const matchesBatch = selectedBatch === '' || studentBatch === selectedBatch;

                    // Check group filter
                    const matchesGroup = selectedGroup === '' || studentGroup === selectedGroup;

                    // Check schedule status filter
                    const matchesScheduleStatus = selectedScheduleStatus === '' ||
                                                (selectedScheduleStatus === 'with-schedule' && hasSchedule) ||
                                                (selectedScheduleStatus === 'without-schedule' && !hasSchedule);

                    // Show/hide row based on all criteria
                    if (matchesSearch && matchesBatch && matchesGroup && matchesScheduleStatus) {
                        row.style.display = '';
                        visibleCount++;

                        // Count visible selected students
                        if (checkbox && checkbox.checked) {
                            visibleSelectedCount++;
                        }
                    } else {
                        row.style.display = 'none';
                        // DO NOT uncheck hidden students - preserve their selection
                        // This allows users to maintain their selections across filters
                    }
                });

                // Update total count display (showing visible students)
                const totalCountElement = document.querySelector('.flex.items-center.gap-4 .text-sm.text-gray-600');
                if (totalCountElement) {
                    const totalStudents = studentRows.length;
                    if (visibleCount === totalStudents) {
                        totalCountElement.innerHTML = `Total: <span class="font-semibold">${visibleCount}</span> students`;
                    } else {
                        totalCountElement.innerHTML = `Showing: <span class="font-semibold">${visibleCount}</span> of <span class="font-semibold">${totalStudents}</span> students`;
                    }
                }

                // Update selection state after filtering
                updateSelectionState();
            }



            // Select all functionality (only affects visible students)
            selectAllCheckbox.addEventListener('change', function() {
                const isChecked = this.checked;

                // Only affect visible students
                studentRows.forEach(row => {
                    if (row.style.display !== 'none') {
                        const checkbox = row.querySelector('.student-checkbox');
                        if (checkbox) {
                            checkbox.checked = isChecked;
                        }
                    }
                });

                updateSelectionState();
            });

            // Individual student checkbox change
            studentCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectionState);
            });



            // Live search and filter event listeners
            searchInput.addEventListener('input', filterStudents);
            batchFilter.addEventListener('change', function() {
                console.log('Batch filter changed to:', batchFilter.value);
                filterStudents();
            });
            groupFilter.addEventListener('change', function() {
                console.log('Group filter changed to:', groupFilter.value);
                filterStudents();
            });
            scheduleFilter.addEventListener('change', function() {
                console.log('Schedule filter changed to:', scheduleFilter.value);
                filterStudents();
            });

            // Going home form field event listeners
            document.addEventListener('input', function(e) {
                if (e.target.matches('#individualStartDate, #individualEndDate, #individualScheduleName, #individualTimeOut, #individualTimeIn')) {
                    updateGoingHomeSubmitState();
                }
            });

            // Schedule day button click handlers
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('schedule-day-btn')) {
                    const studentId = e.target.getAttribute('data-student-id');
                    const timeOut = e.target.getAttribute('data-time-out');
                    const timeIn = e.target.getAttribute('data-time-in');
                    const dateStart = e.target.getAttribute('data-date-start');
                    const dateEnd = e.target.getAttribute('data-date-end');

                    showScheduleModal(studentId, dateStart, dateEnd, timeOut, timeIn);
                }
            });

            // Modal close handlers
            closeModalBtn.addEventListener('click', function() {
                scheduleModal.classList.add('hidden');
            });

            scheduleModal.addEventListener('click', function(e) {
                if (e.target === scheduleModal) {
                    scheduleModal.classList.add('hidden');
                }
            });

            // Function to show schedule modal
            function showScheduleModal(studentId, dateStart, dateEnd, timeOut, timeIn) {
                const studentRow = document.querySelector(`tr input[value="${studentId}"]`).closest('tr');
                const studentName = studentRow.querySelector('td:nth-child(2)').textContent.trim();

                modalContent.innerHTML = `
                    <div class="space-y-3">
                        <div class="p-3 rounded-lg bg-gray-50">
                            <h4 class="mb-1 font-medium text-gray-800">Student Information</h4>
                            <p class="text-sm text-gray-600">ID: <span class="font-medium">${studentId}</span></p>
                            <p class="text-sm text-gray-600">Name: <span class="font-medium">${studentName}</span></p>
                        </div>

                        <div class="p-3 rounded-lg bg-orange-50">
                            <h4 class="mb-2 font-medium text-gray-800">
                                <i data-feather="calendar" class="inline w-4 h-4 mr-1 text-orange-500"></i>
                                ${dateStart} - ${dateEnd} Schedule
                            </h4>
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <p class="mb-1 text-xs text-gray-600">
                                        <i data-feather="log-out" class="inline w-3 h-3 mr-1 text-red-500"></i>
                                        Log Out Time
                                    </p>
                                    <p class="text-sm font-medium text-gray-800">${timeOut}</p>
                                </div>
                                <div>
                                    <p class="mb-1 text-xs text-gray-600">
                                        <i data-feather="log-in" class="inline w-3 h-3 mr-1 text-green-500"></i>
                                        Log In Time
                                    </p>
                                    <p class="text-sm font-medium text-gray-800">${timeIn}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                scheduleModal.classList.remove('hidden');

                // Re-initialize feather icons
                if (typeof feather !== 'undefined') {
                    feather.replace();
                }
            }

            // Proceed directly to going home schedule
            proceedBtn.addEventListener('click', function() {
                // Show going home schedule section directly
                document.getElementById('schedule-section').classList.remove('hidden');
                document.getElementById('goingHomeStep').classList.remove('hidden');
                document.getElementById('scheduleTypeInput').value = 'date_range';
                document.getElementById('schedule-section').scrollIntoView({ behavior: 'smooth' });
            });






            // Going Home Schedule Submit Handler
            document.getElementById('submitGoingHomeSchedule').addEventListener('click', function() {
                // Submit the form
                document.getElementById('schedule-form').submit();
            });

            // Initialize
            updateSelectionState();
            // updateDaySelectionState();

            // Batch Schedule Functionality
            const toggleBatchScheduleBtn = document.getElementById('toggleBatchSchedule');
            const batchScheduleSection = document.getElementById('batchScheduleSection');
            const cancelBatchScheduleBtn = document.getElementById('cancelBatchSchedule');
            const batchScheduleForm = document.getElementById('batchScheduleForm');

            // Cancel batch schedule
            if (cancelBatchScheduleBtn && batchScheduleSection && batchScheduleForm) {
                cancelBatchScheduleBtn.addEventListener('click', function() {
                    batchScheduleSection.classList.add('hidden');
                    batchScheduleForm.reset();
                });
            }

            // Handle batch schedule form submission using event delegation
            document.addEventListener('submit', function(e) {
                if (e.target && e.target.id === 'batchScheduleForm') {
                    e.preventDefault();
                    console.log('Batch schedule form submitted via event delegation');

                    const form = e.target;
                    const formData = new FormData(form);

                    // Show loading state
                    const submitBtn = form.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i data-feather="loader" class="inline w-4 h-4 mr-2 animate-spin"></i>Creating...';
                    submitBtn.disabled = true;

                    // Submit the form
                    fetch('{{ route("monitor.batch-goingout.create") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Batch schedule response:', data);
                        if (data.success) {
                            // Show success message
                            console.log('Showing success message...');
                            if (typeof showDynamicMessage === 'function') {
                                showDynamicMessage('success', 'Batch schedule created successfully!', 'check-circle');
                            } else {
                                console.info('showDynamicMessage not defined — batch schedule created');
                            }

                            // Reset form and hide section
                            form.reset();
                            const batchSection = document.getElementById('batchScheduleSection');
                            if (batchSection) {
                                batchSection.classList.add('hidden');
                            }

                            // Reload batch schedules to show the new one
                            if (typeof loadBatchSchedules === 'function') loadBatchSchedules();
                            if (typeof loadMainBatchSchedules === 'function') loadMainBatchSchedules();

                            // Scroll to top to show success message
                            window.scrollTo({ top: 0, behavior: 'smooth' });
                        } else {
                            if (typeof showDynamicMessage === 'function') {
                                showDynamicMessage('error', 'Error: ' + (data.message || 'Failed to create batch schedule'), 'alert-circle');
                            } else {
                                alert('Error: ' + (data.message || 'Failed to create batch schedule'));
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        if (typeof showDynamicMessage === 'function') {
                            showDynamicMessage('error', 'An error occurred while creating the batch schedule.', 'alert-circle');
                        } else {
                            alert('An error occurred while creating the batch schedule.');
                        }
                    })
                    .finally(() => {
                        // Restore button state
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                        feather.replace();
                    });
                }
            });

            // Going Home Period Input Handlers
            const individualStartDateInput = document.getElementById('individualStartDate');
            const individualEndDateInput = document.getElementById('individualEndDate');

            // Going home period date validation and period summary update
            if (individualStartDateInput) {
                individualStartDateInput.addEventListener('change', function() {
                    if (individualEndDateInput) {
                        individualEndDateInput.min = this.value;
                        if (individualEndDateInput.value && individualEndDateInput.value < this.value) {
                            individualEndDateInput.value = this.value;
                        }
                    }
                    updatePeriodSummary();
                    // updateDaySelectionState();
                });
            }

            if (individualEndDateInput) {
                individualEndDateInput.addEventListener('change', function() {
                    if (individualStartDateInput.value && this.value < individualStartDateInput.value) {
                        alert('End date cannot be earlier than start date.');
                        this.value = individualStartDateInput.value;
                    }
                    updatePeriodSummary();
                    // updateDaySelectionState();
                });
            }

            // Time input listeners
            const timeOutInput = document.getElementById('individualTimeOut');
            const timeInInput = document.getElementById('individualTimeIn');
            const scheduleNameInput = document.getElementById('individualScheduleName');

            if (timeOutInput) {
                timeOutInput.addEventListener('change', function() {
                    updatePeriodSummary();
                    // updateDaySelectionState();
                });
            }

            if (timeInInput) {
                timeInInput.addEventListener('change', function() {
                    updatePeriodSummary();
                    // updateDaySelectionState();
                });
            }

            if (scheduleNameInput) {
                scheduleNameInput.addEventListener('input', function() {
                    // updateDaySelectionState();
                });
            }

            // Function to update period summary
            function updatePeriodSummary() {
                const startDate = individualStartDateInput.value;
                const endDate = individualEndDateInput.value;
                const timeOut = document.getElementById('individualTimeOut').value;
                const timeIn = document.getElementById('individualTimeIn').value;
                const summary = document.getElementById('periodSummary');
                const summaryContent = document.getElementById('periodSummaryContent');

                if (startDate && endDate && timeOut && timeIn) {
                    const start = new Date(startDate);
                    const end = new Date(endDate);
                    const totalDays = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) + 1;

                    const startFormatted = start.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    const endFormatted = end.toLocaleDateString('en-US', {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'long',
                        day: 'numeric'
                    });

                    summaryContent.innerHTML = `
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div class="p-3 bg-red-100 border border-red-200 rounded-lg">
                                <h6 class="mb-1 font-medium text-red-800">
                                    <i data-feather="log-out" class="inline w-3 h-3 mr-1"></i>
                                    Departure
                                </h6>
                                <p class="text-sm text-red-700">${startFormatted}</p>
                                <p class="text-sm font-medium text-red-800">at ${timeOut}</p>
                            </div>
                            <div class="p-3 bg-blue-100 border border-blue-200 rounded-lg">
                                <h6 class="mb-1 font-medium text-blue-800">
                                    <i data-feather="log-in" class="inline w-3 h-3 mr-1"></i>
                                    Return
                                </h6>
                                <p class="text-sm text-blue-700">${endFormatted}</p>
                                <p class="text-sm font-medium text-blue-800">at ${timeIn}</p>
                            </div>
                        </div>
                        <div class="p-2 mt-3 text-center bg-gray-100 rounded">
                            <p class="text-sm text-gray-700">
                                <strong>Duration:</strong> ${totalDays} day${totalDays > 1 ? 's' : ''}
                                ${totalDays > 1 ? `(${totalDays - 1} day${totalDays > 2 ? 's' : ''} away from dormitory)` : ''}
                            </p>
                        </div>
                    `;

                    summary.classList.remove('hidden');

                    // Re-initialize feather icons
                    feather.replace();
                } else {
                    summary.classList.add('hidden');
                }
            }

            // loadBatchSchedules();
            // loadIndividualDateRangeSchedules();

            @if(session('success'))
                setTimeout(() => {
                    loadIndividualDateRangeSchedules();
                }, 1000);
            @endif
            // Load all batch schedules for modal
            function loadAllBatchSchedulesModal() {
                const container = document.getElementById('all-batch-schedules-content');

                // Show loading state
                container.innerHTML = `
                    <div class="py-8 text-center text-gray-500">
                        <i data-feather="loader" class="w-8 h-8 mx-auto mb-2 animate-spin"></i>
                        <p>Loading batch schedules...</p>
                    </div>
                `;
                feather.replace();

                fetch('{{ route("monitor.batch-goingout.list") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.data.length > 0) {
                            // Group schedules by status
                            const activeSchedules = data.data.filter(s => s.is_active);
                            const expiredSchedules = data.data.filter(s => !s.is_active);

                            let html = '';

                            // Active schedules section
                            if (activeSchedules.length > 0) {
                                html += `
                                    <div class="mb-8">
                                        <h4 class="flex items-center mb-4 text-lg font-semibold text-green-600">
                                            <i data-feather="check-circle" class="w-5 h-5 mr-2"></i>
                                            Active Schedules (${activeSchedules.length})
                                        </h4>
                                        <div class="space-y-4">
                                            ${activeSchedules.map(schedule => createScheduleCard(schedule, true)).join('')}
                                        </div>
                                    </div>
                                `;
                            }

                            // Expired schedules section
                            if (expiredSchedules.length > 0) {
                                html += `
                                    <div class="mb-4">
                                        <h4 class="flex items-center mb-4 text-lg font-semibold text-gray-600">
                                            <i data-feather="clock" class="w-5 h-5 mr-2"></i>
                                            Past Schedules (${expiredSchedules.length})
                                        </h4>
                                        <div class="space-y-4">
                                            ${expiredSchedules.map(schedule => createScheduleCard(schedule, false)).join('')}
                                        </div>
                                    </div>
                                `;
                            }

                            container.innerHTML = html;
                        } else {
                            container.innerHTML = `
                                <div class="py-12 text-center text-gray-500">
                                    <i data-feather="calendar" class="w-12 h-12 mx-auto mb-4"></i>
                                    <h4 class="mb-2 text-lg font-medium text-gray-900">No Batch Schedules Found</h4>
                                    <p class="text-gray-600">No batch going home schedules have been created yet.</p>
                                    <p class="mt-2 text-sm text-gray-500">Use the "Batch Schedule" button to create your first schedule.</p>
                                </div>
                            `;
                        }
                        feather.replace();
                    })
                    .catch(error => {
                        console.error('Error loading all batch schedules:', error);
                        container.innerHTML = `
                            <div class="py-12 text-center text-red-500">
                                <i data-feather="alert-circle" class="w-12 h-12 mx-auto mb-4"></i>
                                <h4 class="mb-2 text-lg font-medium text-red-700">Error Loading Schedules</h4>
                                <p class="text-red-600">Failed to load batch schedules. Please try again.</p>
                            </div>
                        `;
                        feather.replace();
                    });
            }

            // Helper function to create schedule card HTML
            function createScheduleCard(schedule, isActive) {
                const statusClass = isActive ? 'bg-green-50 border-green-200' : 'bg-gray-50 border-gray-300';
                const statusBadge = isActive ?
                    '<span class="inline-flex items-center px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Active</span>' :
                    '<span class="inline-flex items-center px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">Expired</span>';

                return `
                    <div class="p-4 border rounded-lg ${statusClass}">
                        <div class="flex items-start justify-between mb-3">
                            <div>
                                <h5 class="font-semibold text-gray-800">${schedule.schedule_name}</h5>
                                <p class="text-sm text-gray-600">Batch ${schedule.batch} • ${schedule.student_count || 0} students</p>
                            </div>
                            <div class="text-right">
                                ${statusBadge}
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2">
                            <div>
                                <span class="font-medium text-gray-700">Date Range:</span>
                                <p class="text-gray-600">
                                    ${schedule.start_date && schedule.end_date ?
                                        `${schedule.start_date} - ${schedule.end_date}` :
                                        'Permanent'
                                    }
                                </p>
                            </div>

                            <div>
                                <span class="font-medium text-gray-700">Time:</span>
                                <p class="text-gray-600">
                                    Out: ${schedule.time_out || 'Not set'} | In: ${schedule.time_in || 'Not set'}
                                </p>
                            </div>
                        </div>

                        <div class="pt-3 mt-3 text-xs text-gray-500 border-t border-gray-200">
                            Created: ${schedule.created_at || 'Unknown'}
                        </div>
                    </div>
                `;
            }

            // Initialize feather icons
            feather.replace();
        });
        
        @if(session('success'))
            document.addEventListener("DOMContentLoaded", function() {
                openMessageModal('success', "{{ session('success') }}");
            });
        @elseif(session('error'))
            document.addEventListener("DOMContentLoaded", function() {
                openMessageModal('error', "{{ session('error') }}");
            });
        @endif
    </script>
</x-monitorLayout>
