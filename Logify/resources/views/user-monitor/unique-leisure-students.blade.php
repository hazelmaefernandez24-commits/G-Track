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
                        <h1 class="text-2xl font-bold text-blue-600">Unique Leisure Schedule</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <a href="{{ route('monitor.unique-leisure.students') }}"
                            class="flex items-center gap-2 px-4 py-2 text-orange-600 transition-all duration-200 border border-orange-200 rounded-lg bg-orange-50 hover:bg-orange-100 hover:border-orange-300">
                            <i data-feather="home" class="w-4 h-4"></i>
                            <span class="font-medium">Unique Leisure Schedule</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        @if (session('success'))
            <div class="w-full px-6 py-2">
                <div class="mx-auto max-w-7xl">
                    <div class="relative px-4 py-3 text-green-700 bg-green-100 border border-green-400 rounded"
                        role="alert">
                        <span class="block sm:inline">{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="w-full px-6 py-2">
                <div class="mx-auto max-w-7xl">
                    <div class="relative px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded"
                        role="alert">
                        <span class="block sm:inline">{{ session('error') }}</span>
                    </div>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="w-full px-6 py-2">
                <div class="mx-auto max-w-7xl">
                    <div class="relative px-4 py-3 text-red-700 bg-red-100 border border-red-400 rounded"
                        role="alert">
                        <div class="flex items-center mb-2">
                            <i data-feather="alert-circle" class="w-4 h-4 mr-2"></i>
                            <strong class="font-bold">Please fix the validation errors and try again:</strong>
                        </div>
                        <ul class="space-y-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

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
                            <input type="text" id="search-input"
                                placeholder="Search by Student ID, First Name, or Last Name"
                                class="w-full px-4 py-3 transition-all duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Batch Filter -->
                        <div class="lg:w-48">
                            <label for="batch-filter" class="block mb-2 text-sm font-medium text-gray-700">
                                <i data-feather="users" class="inline w-4 h-4 mr-1"></i>
                                Batch
                            </label>
                            <select id="batch-filter"
                                class="w-full px-4 py-3 transition-all duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Batches</option>
                                @foreach ($batches as $batch)
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
                                class="w-full px-4 py-3 transition-all duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Groups</option>
                                @foreach ($groups as $group)
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
                                class="w-full px-4 py-3 transition-all duration-200 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
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
                            <i data-feather="users" class="inline w-5 h-5 mr-2 text-blue-500"></i>
                            Select Students for Unique Leisure Schedule
                        </h2>
                        <div class="flex items-center gap-4">
                            <span class="text-sm text-gray-600">
                                Total: <span class="font-semibold">{{ $students->count() }}</span> students
                            </span>
                            <span class="text-sm text-gray-600">
                                Selected: <span id="selected-count" class="font-semibold text-blue-600">0</span>
                            </span>
                        </div>
                    </div>

                    @if ($students->count() > 0)
                        <form id="schedule-form" method="POST" action="{{ route('monitor.unique-leisure.set-schedule') }}">
                            @csrf

                            <!-- Selection Controls -->
                            <div class="p-4 mb-4 border border-gray-200 rounded-lg bg-gray-50">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" id="select-all"
                                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                            <span class="text-sm font-medium text-gray-700">Select All</span>
                                        </label>
                                    </div>
                                    <button type="button" id="proceed-btn" disabled
                                        class="px-6 py-2 font-medium text-white transition-all duration-200 bg-blue-600 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                        <i data-feather="calendar" class="inline w-4 h-4 mr-1"></i>
                                        Set Unique Leisure Schedule
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
                                            @foreach ($students as $student)
                                                @php
                                                    $studentSchedules = $existingSchedules
                                                        ? $existingSchedules->get($student->student_id, collect())
                                                        : collect();
                                                    $hasSchedule = $studentSchedules->isNotEmpty();
                                                @endphp
                                                <tr class="transition-colors duration-150 hover:bg-gray-50"
                                                    data-has-schedule="{{ $hasSchedule ? 'true' : 'false' }}">
                                                    <td class="px-6 py-4 font-medium text-gray-900">
                                                        {{ $student->student_id }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        {{ $student->user->user_fname }}
                                                        {{ $student->user->user_lname }}
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                                            {{ $student->batch }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4">
                                                        <span
                                                            class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                            {{ $student->group }}
                                                        </span>
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        @if ($hasSchedule)
                                                            @php
                                                                $uniqueSchedules = $studentSchedules->unique(
                                                                    'day_of_week',
                                                                );
                                                            @endphp
                                                            <div class="flex flex-wrap justify-center gap-1">
                                                                @foreach ($uniqueSchedules as $schedule)
                                                                    <button type="button"
                                                                        class="px-2 py-1 text-xs font-medium text-blue-800 transition-colors bg-blue-100 rounded-full cursor-pointer schedule-day-btn hover:bg-blue-200"
                                                                        data-student-id="{{ $student->student_id }}"
                                                                        data-day="{{ $schedule->day_of_week }}"
                                                                        data-time-out="{{ $schedule->time_out }}"
                                                                        data-time-in="{{ $schedule->time_in }}"
                                                                        data-valid-until="{{ $schedule->valid_until }}"
                                                                        title="Click to view schedule details">
                                                                        {{ substr($schedule->day_of_week, 0, 3) }}
                                                                    </button>
                                                                @endforeach
                                                            </div>
                                                        @else
                                                            <span
                                                                class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">
                                                                No Schedule
                                                            </span>
                                                        @endif
                                                    </td>
                                                    <td class="px-6 py-4 text-center">
                                                        <label class="inline-flex items-center cursor-pointer">
                                                            <input type="checkbox" name="student_ids[]"
                                                                value="{{ $student->student_id }}"
                                                                class="w-4 h-4 text-blue-600 border-gray-300 rounded student-checkbox focus:ring-blue-500">
                                                        </label>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Schedule Setting Section -->
                            <div id="schedule-section" class="hidden p-6 bg-white border border-green-200 rounded-lg">
                                <div class="mb-4">
                                    <h4 class="text-lg font-semibold text-green-800">
                                        <i data-feather="clock" class="inline w-5 h-5 mr-2"></i>
                                        Set Schedule Times
                                    </h4>
                                </div>

                                <p class="mb-6 text-gray-600">
                                    Define the schedule times for the selected days and chosen students.
                                </p>

                                <!-- Schedule Name -->
                                <div class="mb-6">
                                    <label class="block mb-2 text-sm font-medium text-gray-700">
                                        <i data-feather="tag" class="inline w-4 h-4 mr-1"></i>
                                        Schedule Name
                                    </label>
                                    <input type="text" name="schedule_name" id="individualScheduleName"
                                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                </div>

                                <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                                    <!-- Start (Time Out) -->
                                    <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                                        <h5 class="mb-3 font-semibold text-yellow-800">
                                            <i data-feather="log-out" class="inline w-4 h-4 mr-1"></i>
                                            Start (Time Out)
                                        </h5>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block mb-1 text-sm font-medium text-gray-700">Date Start</label>
                                                <input type="date" name="start_date" id="individualStartDate"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                            </div>
                                            <div>
                                                <label class="block mb-1 text-sm font-medium text-gray-700">Time Out</label>
                                                <input type="time" name="time_out" id="individualTimeOut"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- End (Time In) -->
                                    <div class="p-4 border border-green-200 rounded-lg bg-green-50">
                                        <h5 class="mb-3 font-semibold text-green-800">
                                            <i data-feather="log-in" class="inline w-4 h-4 mr-1"></i>
                                            End (Time In)
                                        </h5>
                                        <div class="space-y-3">
                                            <div>
                                                <label class="block mb-1 text-sm font-medium text-gray-700">Date End</label>
                                                <input type="date" name="end_date" id="individualEndDate"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                            </div>
                                            <div>
                                                <label class="block mb-1 text-sm font-medium text-gray-700">Time In</label>
                                                <input type="time" name="time_in" id="individualTimeIn"
                                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Summary -->
                                <div id="scheduleSummary" class="hidden p-4 mb-6 border border-gray-200 rounded-lg bg-gray-50">
                                    <h6 class="mb-2 font-medium text-gray-800">
                                        <i data-feather="info" class="inline w-4 h-4 mr-1"></i>
                                        Schedule Summary
                                    </h6>
                                    <div id="scheduleSummaryContent" class="text-sm text-gray-600">
                                        <!-- Summary will show here -->
                                    </div>
                                </div>

                                <!-- Submit -->
                                <div class="flex justify-end">
                                    <button type="submit" id="submitSchedule" disabled
                                            class="px-8 py-3 font-medium text-white transition-all duration-200 bg-green-600 rounded-lg hover:bg-green-700 focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:bg-gray-300 disabled:cursor-not-allowed">
                                        <i data-feather="save" class="inline w-4 h-4 mr-2"></i>
                                        Set Unique Leisure Schedule
                                    </button>
                                </div>

                                <input type="hidden" name="individual_schedule_type" id="scheduleTypeInput" value="">
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

        <!-- Schedule Details Modal -->
        <div id="schedule-modal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="w-full max-w-md bg-white rounded-lg shadow-xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-800">
                                <i data-feather="calendar" class="inline w-5 h-5 mr-2 text-blue-500"></i>
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

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const scheduleForm = document.getElementById('schedule-form');
                if (!scheduleForm) return; // nothing to do when no form / no students

                // Scope all lookups to the form to avoid global nulls
                const selectAllCheckbox = scheduleForm.querySelector('#select-all');
                const studentCheckboxes = scheduleForm.querySelectorAll('.student-checkbox');
                const selectedCountSpan = scheduleForm.querySelector('#selected-count');
                const proceedBtn = scheduleForm.querySelector('#proceed-btn');
                const scheduleSection = scheduleForm.querySelector('#schedule-section');
                const submitScheduleBtn = scheduleForm.querySelector('#submitSchedule');
                const studentRows = scheduleForm.querySelectorAll('tbody tr');

                // Modal elements (modal exists outside form)
                const scheduleModal = document.getElementById('schedule-modal');
                const closeModalBtn = document.getElementById('close-modal');
                const modalContent = document.getElementById('modal-content');

                function updateSelectionState() {
                    const allSelectedCount = scheduleForm.querySelectorAll('.student-checkbox:checked').length;
                    const visibleRows = Array.from(studentRows).filter(row => row.style.display !== 'none');
                    const visibleSelectedCount = visibleRows.filter(row => {
                        const checkbox = row.querySelector('.student-checkbox');
                        return checkbox && checkbox.checked;
                    }).length;

                    const hiddenSelectedCount = allSelectedCount - visibleSelectedCount;
                    if (selectedCountSpan) {
                        if (hiddenSelectedCount > 0) {
                            selectedCountSpan.innerHTML =
                                `${allSelectedCount} <span class="text-xs text-blue-600">(${hiddenSelectedCount} hidden)</span>`;
                        } else {
                            selectedCountSpan.textContent = allSelectedCount;
                        }
                    }
                    if (proceedBtn) proceedBtn.disabled = allSelectedCount === 0;

                    if (selectAllCheckbox) {
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
                }

                function filterStudents() {
                    const searchInput = document.getElementById('search-input');
                    const batchFilter = document.getElementById('batch-filter');
                    const groupFilter = document.getElementById('group-filter');
                    const scheduleFilter = document.getElementById('schedule-filter');
                    const searchTerm = searchInput ? searchInput.value.toLowerCase().trim() : '';
                    const selectedBatch = batchFilter ? batchFilter.value : '';
                    const selectedGroup = groupFilter ? groupFilter.value : '';
                    const selectedScheduleStatus = scheduleFilter ? scheduleFilter.value : '';
                    let visibleCount = 0;

                    studentRows.forEach(row => {
                        const studentId = row.querySelector('td:nth-child(1)').textContent.toLowerCase();
                        const studentName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                        const studentBatch = row.querySelector('td:nth-child(3) span').textContent;
                        const studentGroup = row.querySelector('td:nth-child(4) span').textContent;
                        const hasSchedule = row.getAttribute('data-has-schedule') === 'true';

                        const matchesSearch = !searchTerm || studentId.includes(searchTerm) || studentName.includes(searchTerm);
                        const matchesBatch = !selectedBatch || studentBatch === selectedBatch;
                        const matchesGroup = !selectedGroup || studentGroup === selectedGroup;
                        const matchesScheduleStatus = !selectedScheduleStatus ||
                            (selectedScheduleStatus === 'with-schedule' && hasSchedule) ||
                            (selectedScheduleStatus === 'without-schedule' && !hasSchedule);

                        if (matchesSearch && matchesBatch && matchesGroup && matchesScheduleStatus) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    const totalCountElement = document.querySelector('.flex.items-center.gap-4 .text-sm.text-gray-600');
                    if (totalCountElement) {
                        const totalStudents = studentRows.length;
                        if (visibleCount === totalStudents) {
                            totalCountElement.innerHTML = `Total: <span class="font-semibold">${visibleCount}</span> students`;
                        } else {
                            totalCountElement.innerHTML = `Showing: <span class="font-semibold">${visibleCount}</span> of <span class="font-semibold">${totalStudents}</span> students`;
                        }
                    }

                    updateSelectionState();
                }

                // Safe event binding helpers
                if (selectAllCheckbox) {
                    selectAllCheckbox.addEventListener('change', function() {
                        const isChecked = this.checked;
                        studentRows.forEach(row => {
                            if (row.style.display !== 'none') {
                                const checkbox = row.querySelector('.student-checkbox');
                                if (checkbox) checkbox.checked = isChecked;
                            }
                        });
                        updateSelectionState();
                    });
                }

                if (studentCheckboxes && studentCheckboxes.length) {
                    studentCheckboxes.forEach(checkbox => checkbox.addEventListener('change', updateSelectionState));
                }

                const searchInputEl = document.getElementById('search-input');
                if (searchInputEl) searchInputEl.addEventListener('input', filterStudents);
                const batchFilterEl = document.getElementById('batch-filter');
                if (batchFilterEl) batchFilterEl.addEventListener('change', filterStudents);
                const groupFilterEl = document.getElementById('group-filter');
                if (groupFilterEl) groupFilterEl.addEventListener('change', filterStudents);
                const scheduleFilterEl = document.getElementById('schedule-filter');
                if (scheduleFilterEl) scheduleFilterEl.addEventListener('change', filterStudents);

                if (proceedBtn) {
                    proceedBtn.addEventListener('click', function() {
                        if (scheduleSection) {
                            scheduleSection.classList.remove('hidden');
                            scheduleSection.scrollIntoView({ behavior: 'smooth' });
                        }
                        toggleSubmitState();
                    });
                }

                function toggleSubmitState() {
                    const anyStudent = scheduleForm.querySelectorAll('.student-checkbox:checked').length > 0;
                    const dateVal = scheduleForm.querySelector('#individualStartDate') ? scheduleForm.querySelector('#individualStartDate').value : '';
                    const outVal = scheduleForm.querySelector('#individualTimeOut') ? scheduleForm.querySelector('#individualTimeOut').value : '';
                    const inVal = scheduleForm.querySelector('#individualTimeIn') ? scheduleForm.querySelector('#individualTimeIn').value : '';
                    if (submitScheduleBtn) submitScheduleBtn.disabled = !(anyStudent && dateVal && outVal && inVal);
                }

                ['individualStartDate', 'individualTimeOut', 'individualTimeIn'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.addEventListener('input', toggleSubmitState);
                });
                if (studentCheckboxes && studentCheckboxes.length) studentCheckboxes.forEach(cb => cb.addEventListener('change', toggleSubmitState));

                // modal click handlers
                if (closeModalBtn) closeModalBtn.addEventListener('click', () => scheduleModal.classList.add('hidden'));
                if (scheduleModal) scheduleModal.addEventListener('click', function(e) { if (e.target === scheduleModal) scheduleModal.classList.add('hidden'); });

                // schedule details show
                document.addEventListener('click', function(e) {
                    if (e.target.classList && e.target.classList.contains('schedule-day-btn')) {
                        const studentId = e.target.getAttribute('data-student-id');
                        const day = e.target.getAttribute('data-day');
                        const timeOut = e.target.getAttribute('data-time-out');
                        const timeIn = e.target.getAttribute('data-time-in');
                        const validUntil = e.target.getAttribute('data-valid-until');
                        showScheduleModal(studentId, day, timeOut, timeIn, validUntil);
                    }
                });

                function showScheduleModal(studentId, day, timeOut, timeIn, validUntil) {
                    const inputEl = document.querySelector(`tr input[value="${studentId}"]`);
                    if (!inputEl) return;
                    const studentRow = inputEl.closest('tr');
                    const studentName = studentRow ? studentRow.querySelector('td:nth-child(2)').textContent.trim() : '';

                    modalContent.innerHTML = `
                        <div class="space-y-3">
                            <div class="p-3 rounded-lg bg-gray-50">
                                <h4 class="mb-1 font-medium text-gray-800">Student Information</h4>
                                <p class="text-sm text-gray-600">ID: <span class="font-medium">${studentId}</span></p>
                                <p class="text-sm text-gray-600">Name: <span class="font-medium">${studentName}</span></p>
                            </div>
                            <div class="p-3 rounded-lg bg-blue-50">
                                <h4 class="mb-2 font-medium text-gray-800">
                                    <i data-feather="calendar" class="inline w-4 h-4 mr-1 text-blue-500"></i>
                                    ${day} Schedule
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
                                ${validUntil ? `<div class="pt-2 mt-2 border-t border-blue-200"><p class="text-xs text-gray-600"><i data-feather="clock" class="inline w-3 h-3 mr-1"></i> Valid until: <span class="font-medium">${validUntil}</span></p></div>` : ''}
                            </div>
                        </div>
                    `;

                    if (scheduleModal) scheduleModal.classList.remove('hidden');
                    if (typeof feather !== 'undefined') feather.replace();
                }

                // Initialize
                updateSelectionState();
                toggleSubmitState();

                // Submit handler (scoped and guarded)
                scheduleForm.addEventListener('submit', function(e) {
                    const selectedIds = Array.from(scheduleForm.querySelectorAll('.student-checkbox:checked')).map(cb => cb.value);
                    if (selectedIds.length === 0) {
                        e.preventDefault();
                        alert('Please select at least one student.');
                        return;
                    }

                    const startDate = scheduleForm.querySelector('#individualStartDate')?.value;
                    const endDate = scheduleForm.querySelector('#individualStartEnd')?.value;
                    const outVal = scheduleForm.querySelector('#individualTimeOut')?.value;
                    const inVal = scheduleForm.querySelector('#individualTimeIn')?.value;
                    if (!startDate || !outVal || !inVal) {
                        e.preventDefault();
                        alert('Please provide start date and both time inputs.');
                        return;
                    }
                    if (endDate && endDate < startDate) {
                        e.preventDefault();
                        alert('End date cannot be earlier than start date.');
                        return;
                    }
                    // do not append hidden inputs; checkboxes will be submitted
                });
            });
         </script>
</x-monitorLayout>
