<x-monitorLayout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="p-8 bg-white shadow-md rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-orange-700">ACADEMIC LOGS MONITOR</h1>
            <div class="flex items-center space-x-4">
                <div onclick="openVisitorManualEntryModal()"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-sm cursor-pointer hover:bg-green-700">
                    <i data-feather="edit" class="w-4 h-4 mr-2"></i> Manual Entry
                </div>
                <a href="{{ route('monitor.dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-blue-600 hover:underline">
                    <i data-feather="arrow-left" class="w-5 h-5 mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <div class="mb-6">
            <form action="{{ route('monitor.academic.logs') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 lg:max-w-xs">
                    <label for="dateFilter" class="block mb-1 text-sm font-medium text-gray-700">Search Name</label>
                    <input type="text" name="fullname" placeholder="Search by First Name, or Last Name..."
                        class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                <div class="flex-1 lg:max-w-xs">
                    <label for="dateFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Date</label>
                    <input type="date" id="dateFilter" name="date" value="{{ $selectedDate }}" max="{{ now()->format('Y-m-d') }}"
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

        <div class="relative w-full overflow-hidden border border-gray-200 rounded-lg">
            <div class="w-full overflow-x-auto table-container">
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] table-container">
                    <table class="w-full min-w-full text-sm text-left text-gray-700">
                        <thead class="sticky top-0 z-10 text-xs font-semibold tracking-wider uppercase bg-gray-100">
                            <tr>
                                <th class="px-5 py-2 text-black">Student ID</th>
                                <th class="px-5 py-2 text-black">Name</th>
                                <th class="px-5 py-2 text-black">Batch</th>
                                <th class="px-5 py-2 text-black">Group</th>
                                <th class="px-5 py-2 text-black">Date</th>
                                <th class="px-5 py-2 text-black">Out Time</th>
                                <th class="px-5 py-2 text-black">Remark</th>
                                <th class="px-5 py-2 text-black 2">Consideration</th>
                                <th class="px-5 py-2 text-black">In Time</th>
                                <th class="px-5 py-2 text-black">Remark</th>
                                <th class="px-5 py-2 text-black">Consideration</th>
                                <th class="px-5 py-2 text-black">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700">
                            @forelse($logs as $index => $log)
                                @php
                                    $rowClass = 'border-b border-gray-200 hover:bg-gray-50';
                                @endphp
                                <tr class="{{ $rowClass }}"
                                    data-batch="{{ $log->batch ?? '' }}"
                                    data-group="{{ $log->group ?? '' }}"
                                    data-student-id="{{ $log->student_id }}"
                                    data-student-fname="{{ strtolower($log->user->user_fname ?? '') }}"
                                    data-student-lname="{{ strtolower($log->user->user_lname ?? '') }}"
                                    data-student-fullname="{{ strtolower(($log->user->user_fname ?? '') . ' ' . ($log->user->user_lname ?? '')) }}">

                                    <td class="px-6 py-4">{{ $log->student_id }}</td>

                                    <td class="px-6 py-4">
                                        {{ $log->user->user_fname ?? 'N/A' }} {{ $log->user->user_lname ?? '' }}
                                    </td>

                                    <td class="px-6 py-4">{{ $log->batch ?? 'N/A' }}</td>
                                    <td class="px-6 py-4">{{ $log->group ?? 'N/A' }}</td>

                                    {{-- Academic Date --}}
                                    <td class="px-6 py-4">
                                        @if(optional($log->academic)->academic_date)
                                            {{ \Carbon\Carbon::parse(optional($log->academic)->academic_date)->format('M j, Y') }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Time Out --}}
                                    <td class="px-6 py-4">
                                        @if(optional($log->academic)->time_out)
                                            <span>{{ optional($log->academic)->formatted_time_out }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">
                                                —
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Time Out Remark --}}
                                    <td class="px-6 py-4">
                                        <span class="{{ optional($log->academic)->time_out_remark === 'Late' ? 'text-red-600 font-bold' :
                                                    (optional($log->academic)->time_out_remark === 'On Time' ? 'text-blue-600 font-bold' :
                                                    (optional($log->academic)->time_out_remark === 'Early' ? 'text-green-600 font-bold' :
                                                    (optional($log->academic)->time_out_remark === 'Absent' ? 'text-red-600 font-bold' : 'text-yellow-600 font-bold'))) }}">
                                            @if(optional($log->academic)->time_out_remark)
                                                {{ optional($log->academic)->time_out_remark }}
                                            @else
                                                @if(optional($log->academic)->approval_status === 'pending')
                                                    Pending
                                                @else
                                                    <button type="button" data-student="{{ $log->student_id, $selectedDate }}" class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded-md shadow absent-btn hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-red-400">
                                                        Absent?
                                                    </button>
                                                @endif
                                            @endif
                                        </span>
                                    </td>

                                    {{-- Time Out Consideration --}}
                                    <td class="px-6 py-4">
                                        @if(optional($log->academic)->time_out_consideration)
                                            <div class="p-3 border rounded-lg bg-gray-50">
                                                <div class="font-semibold text-sm {{ optional($log->academic)->time_out_consideration === 'Excused' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ optional($log->academic)->time_out_consideration }}
                                                </div>
                                                @if(optional($log->academic)->time_out_reason)
                                                    <div class="mt-1 text-xs italic text-gray-600">{{ optional($log->academic)->time_out_reason }}</div>
                                                @endif
                                                <div class="mt-1 text-xs font-medium text-blue-600">
                                                    Set by: {{ optional($log->academic)->time_out_monitor_name ?: 'Monitor' }}
                                                </div>
                                            </div>
                                        @else
                                            @if(optional($log->academic)->time_out_remark === 'On Time')
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                                    <div class="text-sm font-semibold text-blue-600">No Consideration Needed</div>
                                                </div>
                                            @elseif(!optional($log->academic)->time_out_remark)
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">No remarks</div>
                                            @else
                                                <button type="button" onclick="openConsiderationModal({{ optional($log->academic)->id ?? 'null' }}, 'time_out', '{{ optional($log->academic)->time_out_remark }}')"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md shadow hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-400">
                                                    Add Consideration {{ optional($log->academic)->time_out_remark === 'Absent' ? '' : '(Time In)' }}
                                                </button>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Time In --}}
                                    <td class="px-6 py-4">
                                        @if(optional($log->academic)->time_in)
                                            <span>{{ optional($log->academic)->formatted_time_in }}</span>
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium text-gray-600 bg-gray-100 rounded-full">
                                                —
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Time In Remark --}}
                                    <td class="px-6 py-4">
                                        <span class="{{ optional($log->academic)->time_in_remark === 'Late' ? 'text-red-600 font-bold' :
                                                    (optional($log->academic)->time_in_remark === 'On Time' ? 'text-blue-600 font-bold' :
                                                    (optional($log->academic)->time_in_remark === 'Early' ? 'text-green-600 font-bold' : 'text-red-600 font-bold')) }}">
                                            @if(optional($log->academic)->time_in_remark)
                                                {{ optional($log->academic)->time_in_remark }}
                                            @else
                                                —
                                            @endif
                                        </span>
                                    </td>

                                    {{-- Time In Consideration --}}
                                    <td class="px-6 py-4">
                                        @if(optional($log->academic)->educator_consideration)
                                            <div class="p-3 border rounded-lg bg-gray-50">
                                                <div class="font-semibold text-sm {{ optional($log->academic)->educator_consideration === 'Excused' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ optional($log->academic)->educator_consideration }}
                                                </div>
                                                @if(optional($log->academic)->time_in_reason)
                                                    <div class="mt-1 text-xs italic text-gray-600">{{ optional($log->academic)->time_in_reason }}</div>
                                                @endif
                                                <div class="mt-1 text-xs font-medium text-blue-600">
                                                    Set by: {{ optional($log->academic)->time_in_monitor_name ?: 'Monitor' }}
                                                </div>
                                            </div>
                                        @else
                                            @if(optional($log->academic)->time_in_remark === 'On Time')
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                                    <div class="text-sm font-semibold text-blue-600">No Consideration Needed</div>
                                                </div>
                                            @elseif(optional($log->academic)->time_in_remark === 'Absent')
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">—</div>
                                            @elseif(!optional($log->academic)->time_in_remark)
                                                <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">No remarks</div>
                                            @else
                                                <button type="button" onclick="openConsiderationModal({{ optional($log->academic)->id ?? 'null' }}, 'time_in')"
                                                    class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md shadow hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-400">
                                                    Add Consideration (Time In)
                                                </button>
                                            @endif
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4">
                                        @if(optional($log->academic)->is_manual_entry)
                                            @if(optional($log->academic)->approval_status === 'pending')
                                                <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                                                    Pending Approval
                                                </span>
                                            @elseif(optional($log->academic)->approval_status === 'approved')
                                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                    Approved
                                                </span>
                                            @elseif(optional($log->academic)->approval_status === 'rejected')
                                                <span class="px-2 py-1 text-xs font-medium text-red-800 bg-green-100 rounded-full">
                                                    Rejected
                                                </span>
                                            @endif
                                        @else
                                            @if(optional($log->academic)->time_out && optional($log->academic)->time_in)
                                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                    Complete
                                                </span>
                                            @elseif(optional($log->academic)->time_out)
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
                            @empty
                                <tr>
                                    <td colspan="12" class="px-6 py-8 text-center text-gray-500">
                                        <i data-feather="inbox" class="w-8 h-8 mx-auto mb-2"></i>
                                        <div>No academic logs found for the selected criteria.</div>
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

    <div id="ManualEntryModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-600 bg-opacity-50">
        <div class="w-11/12 max-w-2xl p-8 bg-white border border-gray-200 shadow-2xl rounded-3xl animate-fadeIn">
            <div class="flex flex-col items-center justify-center mb-8 text-center">
                <h3 class="text-3xl font-extrabold text-orange-700">Academic Manual Entry</h3>
            </div>
            <form id="manualEntryForm" action="{{ route('monitor.academicLog.submit') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label for="student_id"
                        class="block mb-3 font-semibold text-gray-700"> Student ID <span class="text-red-500">*</span>
                    </label> <input type="text" id="student_id" name="student_id"
                        class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                        placeholder="Enter student ID">
                </div>
                <div>
                    <label for="date" class="block mb-3 font-semibold text-gray-700"> Date <span class="text-red-500">*</span></label>
                    <input type="date" id="date" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                        name="date" class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                </div>
                <div>
                    <label for="entryType" class="block mb-3 font-semibold text-gray-700"> Entry Type <span class="text-red-500">*</span></label>
                    <select id="entryType" name="entry_type" required class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        <option value="" disabled selected>Select entry type...</option>
                        <option value="time_out">Get Out Only</option>
                        <option value="time_in">Get In Only</option>
                        <option value="both">Both Get In & Out</option>
                    </select>
                </div>
                <div id="timeFields" class="grid grid-cols-1 gap-4">
                    <div id="timeOutField" class="hidden"> <label for="timeOut"
                            class="block mb-3 font-semibold text-gray-700"> Get Out Time <span class="text-red-500">*</span></label>
                            <input type="time" id="timeOut" name="time_out" class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    </div>
                    <div id="timeInField" class="hidden">
                        <label for="timeIn" class="block mb-3 font-semibold text-gray-700"> Get In Time <span class="text-red-500">*</span></label>
                        <input type="time" id="timeIn" name="time_in" class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label for="reason" class="block mb-3 font-semibold text-gray-700"> Reason for Manual Entry<span class="text-red-500">*</span> </label>
                    <textarea id="reason" name="reason" rows="4"
                        class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                        placeholder="Explain why this manual entry is necessary..."></textarea>
                </div>
                <div class="pt-10 space-x-4 text-center">
                    <button type="button"
                        onclick="closeVisitorManualEntryModal()"
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
            <form id="considerationForm" action="{{ route('monitor.academic.consideration') }}" method="POST">
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
                    rows="3" placeholder="Enter reason for this consideration..." maxlength="200" required></textarea>
                <div class="mt-1 text-xs text-gray-500">Maximum 200 characters</div>

                {{-- Actions --}}
                <div class="flex justify-end mt-4 space-x-2">
                    <button type="button" onclick="closeConsiderationModal()"
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

    <div id="messageModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md bg-white rounded-lg shadow-lg">
            <div id="messageModalHeader" class="px-4 py-3 text-white rounded-t-lg">
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

    @if (session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                openMessageModal('success', "{{ session('success') }}");
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                openMessageModal('error', "{{ session('error') }}");
            });
        </script>
    @endif

    <div id="absentModal"
        class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">

        <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-lg font-semibold">Mark as Absent</h3>
                <button onclick="closeAbsentModal()" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>

            <form id="absentForm" method="POST" action="{{ route('monitor.markAbsent') }}">
                @csrf
                <input type="hidden" id="absentLogId" name="student_id">

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
                    <button type="button" onclick="closeAbsentModal()"
                        class="px-4 py-2 text-gray-800 bg-gray-300 rounded hover:bg-gray-400">Cancel</button>
                    <button type="submit"
                        class="px-4 py-2 text-white bg-red-600 rounded hover:bg-red-700">Mark Absent</button>
                </div>
            </form>
        </div>
    </div>

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

        // Close consideration modal
        function closeConsiderationModal() {
            const modal = document.getElementById("considerationModal");
            if (modal) modal.classList.add("hidden");
        }

        function openAbsentModal(log_id) {
            const modal = document.getElementById('absentModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('absentLogId').value = log_id;
            document.body.style.overflow = 'hidden'; // prevent background scroll
        }

        function closeAbsentModal() {
            const modal = document.getElementById('absentModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = ''; // restore scrolling
        }

        function openMessageModal(type, message) {
            const modal = document.getElementById('messageModal');
            const header = document.getElementById('messageModalHeader');
            const title = document.getElementById('messageModalTitle');
            const body = document.getElementById('messageModalBody');

            header.className = "px-4 py-3 text-white rounded-t-lg";

            if (type === 'success') {
                header.classList.add('bg-green-600');
                title.textContent = "Success";
            } else if (type === 'error') {
                header.classList.add('bg-red-600');
                title.textContent = "Error";
            }

            body.textContent = message;
            modal.classList.remove('hidden');
        }

        function closeMessageModal() {
            const modal = document.getElementById('messageModal');
            modal.classList.add('hidden');
        }

        document.addEventListener("DOMContentLoaded", function() {
            const modal = document.getElementById("ManualEntryModal");
            const entryTypeSelect = document.getElementById("entryType");
            const timeOutField = document.getElementById("timeOutField");
            const timeInField = document.getElementById("timeInField");
            const form = document.getElementById("manualEntryForm");
            const submitBtn = document.getElementById("submitBtn");

            window.openVisitorManualEntryModal = function() {
                modal.classList.remove("hidden");
            };

            window.closeVisitorManualEntryModal = function() {
                modal.classList.add("hidden");
                form.reset();
                timeOutField.classList.add("hidden");
                timeInField.classList.add("hidden");
            };

            entryTypeSelect.addEventListener("change", function() {
                const value = this.value;

                timeOutField.classList.add("hidden");
                timeInField.classList.add("hidden");

                if (value === "time_out") {
                    timeOutField.classList.remove("hidden");
                } else if (value === "time_in") {
                    timeInField.classList.remove("hidden");
                } else if (value === "both") {
                    timeOutField.classList.remove("hidden");
                    timeInField.classList.remove("hidden");
                }
            });

            form.addEventListener("submit", function() {
                submitBtn.disabled = true;
                submitBtn.textContent = "Submitting...";
            });
        });

        // Delegated handler for Absent? buttons (works for dynamic / paginated rows)
        document.addEventListener('click', function(e) {
            const btn = e.target.closest && e.target.closest('.absent-btn');
            if (!btn) return;
            const studentId = btn.getAttribute('data-student') || btn.dataset.student;
            if (!studentId) return;
            openAbsentModal(studentId);
        });
    </script>

    <style>
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

        .search-hidden {
            display: none !important;
        }

        .filter-hidden {
            display: none !important;
        }

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

        [id$="Filter"] {
            transition: all 0.2s ease-in-out;
            transform-origin: top;
        }

        [onclick*="selectFilter"]:hover {
            background-color: #fff7ed !important;
            color: #ea580c !important;
        }

        .filter-active {
            background-color: #fed7aa;
            color: #9a3412;
        }
    </style>
</x-monitorLayout>
