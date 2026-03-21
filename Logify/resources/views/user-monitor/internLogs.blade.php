<x-monitorLayout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="p-8 bg-white shadow-md rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-orange-700">INTERN LOGS MONITOR</h1>
            <div class="flex items-center space-x-4">
                <button type="button" id="openManualEntryBtn"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-sm hover:bg-green-700">
                    <i data-feather="edit" class="w-4 h-4 mr-2"></i> Manual Entry
                </button>

                <a href="{{ route('monitor.dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-blue-600 hover:underline">
                    <i data-feather="arrow-left" class="w-5 h-5 mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="mb-6">
            <div class="flex flex-col items-end gap-4 lg:flex-row">
                <form action="{{ route('monitor.intern.logs') }}" method="GET" class="flex flex-wrap items-end gap-4">
                    <div class="flex-1 min-w-[150px]">
                        <label for="dateFilter" class="block mb-1 text-sm font-medium text-gray-700">Search Name</label>
                        <input type="text" name="fullname" placeholder="Search by First Name, or Last Name..."
                            class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label for="dateFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Date</label>
                        <input type="date" id="dateFilter" name="date" value="{{ $selectedDate }}" max="{{ now()->format('Y-m-d') }}"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div class="flex-1 min-w-150px]">
                        <label for="batchFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Batch</label>
                        <select id="batchFilter" name="batch"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">All Batches</option>
                            @foreach($batches as $batch)
                                <option value="{{ $batch }}" {{ request('batch') == $batch ? 'selected' : '' }}>
                                    {{ $batch }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label for="groupFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Group</label>
                        <select id="groupFilter" name="group"
                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">All Groups</option>
                            @foreach($groups as $group)
                                <option value="{{ $group }}" {{ request('group') == $group ? 'selected' : '' }}>
                                    {{ $group }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex-1 min-w-[150px]">
                        <label for="statusFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Status</label>
                        <select id="statusFilter" name="status"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">All Students</option>
                            <option value="not_logged" {{ request('status') == 'not_logged' ? 'selected' : '' }}>Not Logged</option>
                            <option value="not_log_out" {{ request('status') == 'not_log_out' ? 'selected' : '' }}>Not Yet Got Out</option>
                            <option value="not_log_in" {{ request('status') == 'not_log_in' ? 'selected' : '' }}>Not Yet Got In</option>
                            <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late Students</option>
                            <option value="absent" {{ request('status') == 'absent' ? 'selected' : '' }}>Absent Students</option>
                        </select>
                    </div>

                    <div class="flex-1 min-w-[150px]">
                        <label for="companyFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Company</label>
                        <select id="companyFilter" name="company"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                            <option value="">All Companies</option>
                            @foreach ($companies as $company)
                                <option value="{{ $company }}" {{ request('company') == $company ? 'selected' : '' }}>
                                    {{ $company }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex items-center">
                        <button type="submit"
                            class="px-5 py-2 text-sm font-semibold text-white bg-orange-500 rounded-md hover:bg-orange-600 focus:ring-2 focus:ring-orange-500">
                            Apply Filter
                        </button>
                    </div>
                </form>
                </form>
            </div>
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
                                <th class="px-6 py-3 text-black">Company</th>
                                <th class="px-6 py-3 text-black">Date</th>
                                <th class="relative px-6 py-3 text-black">Out Time</th>
                                <th class="relative px-6 py-3 text-black">Remark</th>
                                <th class="px-6 py-3 text-black">Consideration</th>
                                <th class="relative px-6 py-3 text-black">In Time</th>
                                <th class="relative px-6 py-3 text-black">Remark</th>
                                <th class="px-6 py-3 text-black">Consideration</th>
                                <th class="relative px-6 py-3 text-black">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700">
                            @forelse($logs as $index => $log)
                                @php
                                    $rowClass = 'border-b border-gray-200 hover:bg-gray-50';

                                    // Normalize Student
                                    $studentId = data_get($log, 'student_id') ?? '';
                                    $studentDetail = data_get($log, 'studentDetail') ?? null;
                                    $user = data_get($log, 'user') ?? null;
                                    $fname = data_get($user, 'user_fname') ?? '';
                                    $lname = data_get($user, 'user_lname') ?? '';
                                    $fullname = trim("$fname $lname");
                                    $batch = data_get($studentDetail, 'batch') ?? '';
                                    $group = data_get($studentDetail, 'group') ?? '';

                                    $intern_log = data_get($log, 'intern_log');
                                    $internship = data_get($intern_log, 'internshipSchedule') ?? null;
                                    $schedule = data_get($log, 'intern_schedule') ?? '—';
                                    $company = data_get($schedule, 'company') ?? '—';

                                    $dateRaw = data_get($intern_log, 'date');
                                    $displayDate = $dateRaw ? \Carbon\Carbon::parse($dateRaw)->format('M j, Y') : '—';

                                    $timeOutRaw = data_get($intern_log, 'time_out');
                                    $timeInRaw = data_get($intern_log, 'time_in');
                                    $formattedTimeOut = $timeOutRaw ? \Carbon\Carbon::parse($timeOutRaw)->format('g:i A') : null;
                                    $formattedTimeIn = $timeInRaw ? \Carbon\Carbon::parse($timeInRaw)->format('g:i A') : null;

                                    $timeOutRemark = data_get($intern_log, 'time_out_remark');
                                    $timeInRemark = data_get($intern_log, 'time_in_remark');

                                    $timeOutConsideration = data_get($intern_log, 'time_out_consideration');
                                    $timeOutReason = data_get($intern_log, 'time_out_reason');
                                    $timeOutMonitor = data_get($intern_log, 'created_by');

                                    $timeInConsideration = data_get($intern_log, 'educator_consideration');
                                    $timeInReason = data_get($intern_log, 'time_in_reason');
                                    $timeInMonitor = data_get($intern_log, 'updated_by');

                                    $isManual = data_get($intern_log, 'is_manual_entry') ?? false;
                                    $manualType = data_get($intern_log, 'manual_entry_type');
                                    $approvalStatus = data_get($intern_log, 'approval_status');
                                    $approvedBy = data_get($intern_log, 'approved_by');
                                @endphp

                                <tr class="{{ $rowClass }}"
                                    data-batch="{{ strtolower($batch) }}"
                                    data-group="{{ strtolower($group) }}"
                                    data-student-id="{{ $studentId }}"
                                    data-student-fname="{{ strtolower($fname) }}"
                                    data-student-lname="{{ strtolower($lname) }}"
                                    data-student-fullname="{{ strtolower($fullname) }}">

                                    <td class="px-6 py-4 font-medium">{{ $studentId }}</td>
                                    <td class="px-6 py-4">{{ $fname ?: 'N/A' }} {{ $lname }}</td>
                                    <td class="px-6 py-4">{{ $company }}</td>
                                    <td class="px-6 py-4">{{ $displayDate }}</td>
                                    <td class="px-6 py-4">
                                        @if ($formattedTimeOut)
                                            <span>{{ $formattedTimeOut }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">
                                                —
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($timeOutRemark)
                                            <span class="{{ $timeOutRemark === 'Late' ? 'text-red-600 font-bold' : ($timeOutRemark === 'On Time' ? 'text-blue-600 font-bold' : ($timeOutRemark === 'Early' ? 'text-green-600 font-bold' : '')) }}">
                                                {{ $timeOutRemark }}
                                            </span>
                                        @else
                                            <button type="button" data-log="{{ $studentId }}" class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md shadow absent-btn hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-400">
                                                    Absent?
                                            </button>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($timeOutConsideration)
                                            <div class="p-3 border rounded-lg bg-gray-50">
                                                <div
                                                    class="font-semibold text-sm {{ $timeOutConsideration === 'Excused' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $timeOutConsideration }}
                                                </div>
                                                @if ($timeOutReason)
                                                    <div class="mt-1 text-xs italic text-gray-600">
                                                        {{ $timeOutReason }}</div>
                                                @endif
                                                <div class="mt-1 text-xs font-medium text-blue-600">
                                                    Set by: {{ $timeOutMonitor ?: 'Monitor' }}
                                                </div>
                                            </div>
                                        @else
                                            @if ($timeOutRemark === 'On Time')
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                                    <div class="text-sm font-semibold text-blue-600">
                                                        No Consideration Needed
                                                    </div>
                                                </div>
                                            @elseif(!$timeOutRemark)
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                                    No remarks
                                                </div>
                                            @else
                                                <button type="button"
                                                    onclick="openConsiderationModal('{{ $intern_log->id }}', 'time_out', '{{ $timeOutRemark }}')"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md shadow hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-400">
                                                    Add Consideration {{ $timeOutRemark === 'absent' ? '(Time In)' : '' }}
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($formattedTimeIn)
                                            <div class="flex flex-col">
                                                <span>{{ $formattedTimeIn }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">—</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($timeInRemark)
                                            <span class="{{ $timeInRemark === 'Late' ? 'text-red-600 font-bold' : ($timeInRemark === 'On Time' ? 'text-blue-600 font-bold' : ($timeInRemark === 'Early' ? 'text-green-600 font-bold' : '')) }}">
                                                {{ $timeInRemark }}
                                            </span>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($timeInConsideration)
                                            <div class="p-3 border rounded-lg bg-gray-50">
                                                <div
                                                    class="font-semibold text-sm {{ $timeInConsideration === 'Excused' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $timeInConsideration }}
                                                </div>
                                                @if ($timeInReason)
                                                    <div class="mt-1 text-xs italic text-gray-600">
                                                        {{ $timeInReason }}</div>
                                                @endif
                                                <div class="mt-1 text-xs font-medium text-blue-600">
                                                    Set by: {{ $timeInMonitor ?: 'Monitor' }}
                                                </div>
                                            </div>
                                        @else
                                            @if ($timeInRemark === 'On Time')
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                                    <div class="text-sm font-semibold text-blue-600">
                                                        No Consideration Needed
                                                    </div>
                                                </div>
                                            @elseif($timeInRemark === 'Absent')
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">—</div>
                                            @elseif(!$timeInRemark)
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                                    No remarks
                                                </div>
                                            @else
                                                <button type="button"
                                                    onclick="openConsiderationModal('{{ $intern_log->id }}', 'time_in')"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md shadow hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-400">
                                                    Add Consideration (Time In)
                                                </button>
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($isManual)
                                            @if ($approvalStatus === 'pending')
                                                <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Pending</span>
                                            @elseif($approvalStatus === 'approved')
                                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Approved</span>
                                            @elseif($approvalStatus === 'rejected')
                                                <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">Rejected</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">Manual Entry</span>
                                            @endif
                                        @else
                                            @if ($formattedTimeOut && $formattedTimeIn)
                                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Complete</span>
                                            @elseif($formattedTimeOut)
                                                <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">Got Out</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">Not Logged</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="px-6 py-8 text-center text-gray-500">
                                        <i data-feather="inbox" class="w-8 h-8 mx-auto mb-2"></i>
                                        <div>No internship logs found for the selected criteria.</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end mt-6">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>

        {{-- Intern Manual Entry Modal (updated styling to match Leisure) --}}
        <div id="ManualEntryModal"
            class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-600 bg-opacity-50">
            <div class="w-11/12 max-w-2xl p-8 bg-white border border-gray-200 shadow-2xl rounded-3xl animate-fadeIn">

                <!-- Header -->
                <div class="flex flex-col items-center justify-center mb-8 text-center">
                    <h3 class="text-3xl font-extrabold text-orange-700">Intern Manual Entry</h3>
                </div>

                <!-- Form -->
                <form id="internManualEntryForm" action="{{ route('monitor.internLog.submit') }}" method="POST" class="space-y-6">
                    @csrf
                    <input type="hidden" value="intern" name="log_type">

                    <!-- Student ID -->
                    <div>
                        <label for="student_id" class="block mb-3 font-semibold text-gray-700">
                            Student ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="student_id" name="student_id"
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                            placeholder="Enter student ID">
                    </div>

                    <!-- Date -->
                    <div>
                        <label for="date" class="block mb-3 font-semibold text-gray-700">
                            Date <span class="text-red-500">*</span>
                        </label>
                        <input type="date" id="date" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    </div>

                    <!-- Entry Type -->
                    <div>
                        <label for="entryType" class="block mb-3 font-semibold text-gray-700">
                            Entry Type <span class="text-red-500">*</span>
                        </label>
                        <select id="entryType" name="entry_type" required
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                            <option value="" disabled selected>Select entry type...</option>
                            <option value="time_out">Get Out Only</option>
                            <option value="time_in">Get In Only</option>
                            <option value="both">Both Get In & Out</option>
                        </select>
                    </div>

                    <!-- Time Fields -->
                    <div id="timeFields" class="grid grid-cols-1 gap-4">
                        <div id="timeOutField" class="hidden">
                            <label for="timeOut" class="block mb-3 font-semibold text-gray-700">
                                Get Out Time <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="timeOut" name="time_out"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                        <div id="timeInField" class="hidden">
                            <label for="timeIn" class="block mb-3 font-semibold text-gray-700">
                                Get In Time <span class="text-red-500">*</span>
                            </label>
                            <input type="time" id="timeIn" name="time_in"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label for="reason" class="block mb-3 font-semibold text-gray-700">
                            Reason for Manual Entry <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reason" name="reason" rows="4"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                placeholder="Explain why this manual entry is necessary..."></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="pt-10 text-center">
                        <button type="button" onclick="closeVisitorManualEntryModal()"
                                class="inline-flex items-center gap-3 px-10 py-3 font-semibold text-gray-700 transition duration-300 ease-in-out bg-gray-200 border-2 border-gray-300 rounded-md shadow-lg hover:bg-gray-300 hover:scale-105 focus:outline-none focus:ring-2 focus:ring-gray-300">
                            Cancel
                        </button>
                        <button type="submit" id="submitBtn"
                                class="inline-flex items-center gap-3 px-10 py-3 font-semibold text-orange-500 transition duration-300 ease-in-out bg-orange-200 border-2 border-orange-300 rounded-md shadow-lg hover:bg-orange-500 hover:text-white hover:scale-105 focus:outline-none focus:ring-2 focus:ring-orange-300">
                            Submit
                        </button>
                    </div>
                </form>
            </div>
        </div>


    <div id="considerationModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
            <h2 id="modalTitle" class="mb-4 text-lg font-semibold text-gray-800">Add Consideration</h2>

            {{-- Form --}}
            <form id="considerationForm" action="{{ route('logs.consideration.intern') }}" method="POST">
                @csrf
                <input type="hidden" id="logIdInput" name="log_id">
                <input type="hidden" id="typeInput" name="type">
                <input type="hidden" id="remarkType" name="remark">

                {{-- Choice --}}
                <label class="block mb-2 text-sm font-medium text-gray-700">Select Status</label>
                <select name="choice"
                    class="w-full px-3 py-2 mb-4 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                    required>
                    <option value="">Select...</option>
                    <option value="Excused" class="text-green-600">✓ Excused</option>
                    <option value="Not Excused" class="text-red-600">✗ Not Excused</option>
                </select>

                {{-- Reason --}}
                <label class="block mb-2 text-sm font-medium text-gray-700">Reason</label>
                <textarea name="reason"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-orange-500"
                    rows="3"
                    placeholder="Enter reason for this consideration..."
                    maxlength="200"
                    required></textarea>
                <div class="mt-1 text-xs text-gray-500">Maximum 200 characters</div>

                {{-- Actions --}}
                <div class="flex justify-end mt-4 space-x-2">
                    <button type="button"
                        onclick="closeConsiderationModal()"
                        class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-200 rounded-md hover:bg-gray-300">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md hover:bg-orange-600">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Modal -->
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

    <div id="absentModal" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold">Mark as Absent</h3>
                <button onclick="closeAbsentModal()" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>

            <form id="absentForm" method="POST" action="{{ route('monitor.markAbsent.intern') }}">
                @csrf
                <input type="hidden" id="absentLogId" name="log_id">

                <div>
                    <label for="date" class="block mb-3 font-semibold text-gray-700">Date <span class="text-red-500">*</span></label>
                    <input type="date" id="date" name="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                        class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                </div>

                <label class="block mb-2 text-sm font-medium text-gray-700">Reason</label>
                <textarea name="reason"
                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-orange-500"
                    rows="3" placeholder="Enter reason for this consideration..." maxlength="200" required></textarea>
                <div class="mt-1 text-xs text-gray-500">Maximum 200 characters</div>

                <div class="mt-4">
                    <p class="text-gray-700">Are you sure you want to mark this student as <strong>Absent</strong>?</p>
                </div>

                <div class="flex justify-end mt-6 space-x-3">
                    <button type="button" onclick="closeAbsentModal()" class="px-4 py-2 text-gray-800 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit" class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">Mark Absent</button>
                </div>
            </form>
        </div>
    </div>

    <style>
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

    <script>
        function openConsiderationModal(logId, type, remark) {
            const modal = document.getElementById("considerationModal");
            const logInput = document.getElementById("logIdInput");
            const typeInput = document.getElementById("typeInput");
            const remarkType = document.getElementById("remarkType");
            const title = document.getElementById("modalTitle");

            if (modal) {
                modal.classList.remove("hidden");
                logInput.value = logId;
                typeInput.value = type;
                remarkType.value = remark;
                title.textContent = `Add Consideration (${type.replace("_", " ").toUpperCase()})`;
            }
        }

        function closeConsiderationModal() {
            const modal = document.getElementById("considerationModal");
            if (modal) {
                modal.classList.add("hidden");
            }
        }

        function openAbsentModal(logId) {
            document.getElementById('absentModal').classList.remove('hidden');
            document.getElementById('absentModal').classList.add('flex');
            document.getElementById('absentLogId').value = logId; // set log_id in form
        }

        function closeAbsentModal() {
            document.getElementById('absentModal').classList.add('hidden');
            document.getElementById('absentModal').classList.remove('flex');
        }

        // Open visitor manual entry modal
        function openVisitorManualEntryModal() {
            const modal = document.getElementById('ManualEntryModal');
            if (!modal) return;
            modal.classList.remove('hidden');
            // reset form fields if desired
            const form = modal.querySelector('form');
            if (form) form.reset();
            // ensure time fields reflect current entryType
            toggleVisitorTimeFields();
        }

        // Close visitor manual entry modal
        function closeVisitorManualEntryModal() {
            const modal = document.getElementById('ManualEntryModal');
            if (!modal) return;
            modal.classList.add('hidden');
        }

        // Toggle time fields inside visitor modal based on entry type select
        function toggleVisitorTimeFields() {
            const entryType = document.getElementById('entryType')?.value || '';
            const timeFields = document.getElementById('timeFields');
            const timeInField = document.getElementById('timeInField');
            const timeOutField = document.getElementById('timeOutField');
            if (!timeFields) return;
            // reset
            timeInField?.classList.add('hidden');
            timeOutField?.classList.add('hidden');
            timeFields.classList.remove('hidden'); // keep container visible (per layout)

            if (entryType === 'time_in') {
                timeInField?.classList.remove('hidden');
            } else if (entryType === 'time_out') {
                timeOutField?.classList.remove('hidden');
            } else if (entryType === 'both') {
                timeInField?.classList.remove('hidden');
                timeOutField?.classList.remove('hidden');
            } else {
                // if no selection, hide container to match initial UX
                timeFields.classList.add('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const openBtn = document.getElementById('openManualEntryBtn');
            if (openBtn) openBtn.addEventListener('click', openVisitorManualEntryModal);

            const entryTypeEl = document.getElementById('entryType');
            if (entryTypeEl) entryTypeEl.addEventListener('change', toggleVisitorTimeFields);

            // close when clicking overlay outside modal content
            const modal = document.getElementById('ManualEntryModal');
            if (modal) {
                modal.addEventListener('click', function (e) {
                    if (e.target === modal) closeVisitorManualEntryModal();
                });
            }
        });

        function openMessageModal(type, message) {
            const modal = document.getElementById('messageModal');
            const header = document.getElementById('messageModalHeader');
            const title = document.getElementById('messageModalTitle');
            const body = document.getElementById('messageModalBody');

            if (type === 'success') {
                header.className = "px-4 py-3 rounded-t-lg bg-green-600 text-white";
                title.innerText = "Success";
            } else {
                header.className = "px-4 py-3 rounded-t-lg bg-red-600 text-white";
                title.innerText = "Error";
            }

            body.innerText = message;

            modal.classList.remove('hidden');
        }

        function closeMessageModal() {
            document.getElementById('messageModal').classList.add('hidden');
        }

        // Auto-open modal if session message exists
        @if(session('success'))
            document.addEventListener("DOMContentLoaded", function() {
                openMessageModal('success', "{{ session('success') }}");
            });
        @elseif(session('error'))
            document.addEventListener("DOMContentLoaded", function() {
                openMessageModal('error', "{{ session('error') }}");
            });
        @endif

        // Delegated handler for Absent? buttons — uses data-log (intern_log id)
        document.addEventListener('click', function (e) {
            const btn = e.target.closest && e.target.closest('.absent-btn');
            if (!btn) return;
            const logId = btn.getAttribute('data-log') || btn.dataset.log;
            if (!logId) return;
            openAbsentModal(logId);
        });
    </script>
</x-monitorLayout>
