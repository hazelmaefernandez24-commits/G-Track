<x-monitorLayout>
    <script src="https://unpkg.com/feather-icons"></script>

    {{-- Global Small Light Gray Scrollbar Styles --}}
    <style>
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

    <div class="min-h-screen">
        <div class="relative w-full h-full bg-white">

            {{-- Header section with back button and title --}}
            <div class="flex items-center justify-between p-6 bg-white border-b border-gray-200">
                <a href="{{ route('monitor.dashboard') }}" class="flex items-center gap-2 px-4 py-2 font-semibold text-blue-600 transition-all duration-200 rounded-lg shadow-sm bg-blue-50 hover:text-blue-700 hover:bg-blue-100">
                    <i data-feather="arrow-left" class="w-4 h-4"></i>
                    <span>Back to Dashboard</span>
                </a>

                <h2 class="text-3xl font-bold text-orange-500">
                    Select Student
                </h2>

                <div class="w-32"></div> {{-- Spacer for centering --}}
            </div>

            {{-- Filter section --}}
            <div class="p-6 border-b border-gray-200 bg-gray-50">
                <div class="flex items-center gap-6">
                    <div class="relative flex-1">
                        <input type="text"
                               id="studentSearch"
                               placeholder="Search by ID or Name"
                               class="w-full p-3 pl-10 transition-all duration-200 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                        <i data-feather="search" class="absolute text-gray-400 transform -translate-y-1/2 left-3 top-1/2"></i>
                    </div>
                    <div class="w-64">
                        <select id="batch_filter" class="w-full p-3 transition-all duration-200 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                            <option value="">Select Class</option>
                            @php
                                $uniqueBatches = $students->pluck('batch')->unique()->sort();
                            @endphp
                            @foreach($uniqueBatches as $batch)
                                <option value="{{ $batch }}">{{ $batch }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="w-64">
                        <select id="group_filter" class="w-full p-3 transition-all duration-200 border-2 border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                            <option value="">Select Group</option>
                            <option value="PN1">PN1</option>
                            <option value="PN2">PN2</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Table section --}}
            <div class="flex-1 overflow-hidden">
                <div class="h-full overflow-auto">
                    <table class="w-full bg-white">
                        <thead class="sticky top-0 z-10 bg-gray-100">
                            <tr>
                                <th class="px-8 py-4 text-sm font-semibold tracking-wider text-left text-gray-700 uppercase border-b-2 border-gray-200">Student ID</th>
                                <th class="px-8 py-4 text-sm font-semibold tracking-wider text-left text-gray-700 uppercase border-b-2 border-gray-200">Name</th>
                                <th class="px-8 py-4 text-sm font-semibold tracking-wider text-left text-gray-700 uppercase border-b-2 border-gray-200">Batch</th>
                                <th class="px-8 py-4 text-sm font-semibold tracking-wider text-left text-gray-700 uppercase border-b-2 border-gray-200">Group</th>
                                <th class="px-8 py-4 text-sm font-semibold tracking-wider text-left text-gray-700 uppercase border-b-2 border-gray-200">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100" id="studentTableBody">
                            <tr id="noStudentsRow">
                                <td colspan="5" class="px-8 py-12 font-medium text-center text-gray-500 bg-gray-50">
                                    No students found matching the selected criteria
                                </td>
                            </tr>
                            @foreach($students as $student)
                                <tr class="transition-colors duration-150 student-row hover:bg-blue-50" data-batch="{{ $student->batch }}" data-group="{{ $student->pn_group }}">
                                    <td class="px-8 py-5 text-sm font-medium text-gray-900 whitespace-nowrap">{{ $student->student_id }}</td>
                                    <td class="px-8 py-5 text-sm text-gray-900 whitespace-nowrap">{{ $student->first_name }} {{ $student->last_name }}</td>
                                    <td class="px-8 py-5 text-sm text-gray-900 whitespace-nowrap">{{ $student->batch }}</td>
                                    <td class="px-8 py-5 text-sm text-gray-900 whitespace-nowrap">{{ $student->pn_group }}</td>
                                    <td class="px-8 py-5 text-sm text-gray-900 whitespace-nowrap">
                                        <a href="{{ route('monitor.schedule', ['type' => 'Irregular', 'student_id' => $student->student_id]) }}"
                                           class="inline-flex items-center gap-2 px-4 py-2 font-semibold transition-all duration-200 rounded-lg {{ in_array($student->student_id, $studentsWithSchedule) ? 'text-orange-600 bg-orange-50 hover:bg-orange-100 border border-orange-200' : 'text-blue-600 bg-blue-50 hover:bg-blue-100 border border-blue-200' }}">
                                            @if(in_array($student->student_id, $studentsWithSchedule))
                                                <i data-feather="edit-2" class="w-4 h-4"></i>
                                                Edit Schedule
                                            @else
                                                <i data-feather="plus-circle" class="w-4 h-4"></i>
                                                Set Schedule
                                            @endif
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('studentSearch');
            const studentRows = document.querySelectorAll('.student-row');
            const batchFilter = document.getElementById('batch_filter');
            const groupFilter = document.getElementById('group_filter');

            // Disable group filter initially
            groupFilter.disabled = true;

            // Show all students initially
            function showAllStudents() {
                studentRows.forEach(row => {
                    if (row.id !== 'noStudentsRow') {
                        row.style.display = '';
                    }
                });
                document.getElementById('noStudentsRow').style.display = 'none';
            }

            // Update group filter options based on selected batch
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
                studentRows.forEach(row => {
                    if (row.getAttribute('data-batch') === selectedBatch) {
                        availableGroups.add(row.getAttribute('data-group'));
                    }
                });

                // Update group filter options
                groupFilter.innerHTML = '<option value="">Select Group</option>';
                Array.from(availableGroups).sort().forEach(group => {
                    const option = document.createElement('option');
                    option.value = group;
                    option.textContent = group;
                    groupFilter.appendChild(option);
                });
            }

            // Filter students based on criteria
            function updateNoStudentsMessage(visibleCount, searchTerm, batch, group) {
                const noStudentsRow = document.getElementById('noStudentsRow');
                const messageCell = noStudentsRow.querySelector('td');

                if (visibleCount === 0) {
                    let message = 'No students found';
                    if (searchTerm) {
                        message += ` matching "${searchTerm}"`;
                    }
                    if (batch) {
                        message += ` in Batch ${batch}`;
                    }
                    if (group) {
                        message += ` Group ${group}`;
                    }
                    messageCell.textContent = message;
                    noStudentsRow.style.display = '';
                } else {
                    noStudentsRow.style.display = 'none';
                }
            }

            function filterStudents() {
                const searchTerm = searchInput.value.toLowerCase();
                const selectedBatch = batchFilter.value;
                const selectedGroup = groupFilter.value;
                let visibleStudents = 0;

                // First, hide the no students message
                document.getElementById('noStudentsRow').style.display = 'none';

                // Filter student rows
                studentRows.forEach(row => {
                    if (row.id === 'noStudentsRow') return; // Skip the message row

                    const studentId = row.querySelector('td:first-child').textContent.toLowerCase();
                    const studentName = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
                    const batch = row.querySelector('td:nth-child(3)').textContent;
                    const group = row.querySelector('td:nth-child(4)').textContent;

                    const matchesSearch = studentId.includes(searchTerm) || studentName.includes(searchTerm);
                    const matchesBatch = !selectedBatch || batch === selectedBatch;
                    const matchesGroup = !selectedGroup || group === selectedGroup;
                    const isVisible = matchesSearch && matchesBatch && matchesGroup;

                    row.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleStudents++;
                });

                // Update the message based on filter criteria
                updateNoStudentsMessage(visibleStudents, searchInput.value, selectedBatch, selectedGroup);
            }

            // Event listeners
            searchInput.addEventListener('input', filterStudents);

            // When batch changes, update group filter and apply filters
            batchFilter.addEventListener('change', function() {
                const selectedBatch = this.value;
                updateGroupFilter(selectedBatch);
                filterStudents();
            });

            // When group changes, apply filters
            groupFilter.addEventListener('change', filterStudents);

            // Show all students initially
            showAllStudents();

            feather.replace();
        });
    </script>
</x-monitorLayout>
