<x-monitorLayout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="p-8 bg-white shadow-md rounded-2xl">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-orange-700">GOING HOME LOGS MONITOR</h1>
            <div class="flex items-center space-x-4">
                <div onclick="openVisitorManualEntryModal()"
                    class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-sm cursor-pointer hover:bg-green-700">
                    <i data-feather="edit" class="w-4 h-4 mr-2"></i> Manual Entry
                </div>
                <a href="{{ route('monitor.dashboard') }}" class="inline-flex items-center text-sm font-medium text-blue-600 hover:underline">
                    <i data-feather="arrow-left" class="w-5 h-5 mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        <!-- Filters -->
        <div class="mb-6">
            <form action="{{ route('monitor.goinghome.logs') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 lg:max-w-xs">
                    <label for="dateTimeOut" class="block mb-1 text-sm font-medium text-gray-700">Search Name</label>
                    <input type="text" name="fullname" placeholder="Search by First Name, or Last Name..."
                        class="w-full px-3 py-2 pr-8 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>
                {{-- Date Out --}}
                <div class="flex-1 lg:max-w-xs">
                    <label for="dateTimeOut" class="block mb-1 text-sm font-medium text-gray-700">Filter by Date Out</label>
                    <input type="date" id="dateTimeOut" name="date_time_out"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                {{-- Date In --}}
                <div class="flex-1 lg:max-w-xs">
                    <label for="dateTimeIn" class="block mb-1 text-sm font-medium text-gray-700">Filter by Date In</label>
                    <input type="date" id="dateTimeIn" name="date_time_in"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                {{-- Batch Filter --}}
                <div class="flex-1 lg:max-w-xs">
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

                {{-- Group Filter --}}
                <div class="flex-1 lg:max-w-xs">
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

                {{-- Type Filter --}}
                <div class="flex-1 lg:max-w-xs">
                    <label for="typeFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Schedule Type</label>
                    <select id="typeFilter" name="type"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">All Types</option>
                        @isset($types)
                            @foreach($types as $scheduleType)
                                <option value="{{ $scheduleType }}" {{ request('type') == $scheduleType ? 'selected' : '' }}>
                                    {{ $scheduleType }}
                                </option>
                            @endforeach
                        @endisset
                    </select>
                </div>

                {{-- Status Filter --}}
                <div class="flex-1 lg:max-w-xs">
                    <label for="statusFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Status</label>
                    <select id="statusFilter" name="status"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="">All Students</option>
                        <option value="not_logged" {{ request('status') == 'not_logged' ? 'selected' : '' }}>Not Logged</option>
                        <option value="not_log_out" {{ request('status') == 'not_log_out' ? 'selected' : '' }}>Not Yet Got Out</option>
                        <option value="not_log_in" {{ request('status') == 'not_log_in' ? 'selected' : '' }}>Not Yet Got In</option>
                        <option value="late" {{ request('status') == 'late' ? 'selected' : '' }}>Late Students</option>
                    </select>
                </div>

                {{-- Submit Button --}}
                <div class="flex-1 lg:max-w-xs">
                    <label class="block mb-1 text-sm font-medium text-gray-700">&nbsp;</label>
                    <button type="submit"
                            class="w-full px-4 py-2 text-sm font-semibold text-white bg-orange-500 rounded-md hover:bg-orange-600">
                        Apply Filters
                    </button>
                </div>
            </form>
        </div>
        <!-- Table -->
        <div class="relative w-full overflow-hidden border border-gray-200 rounded-lg">
            <div class="w-full overflow-x-auto table-container">
                <div class="overflow-y-auto max-h-[calc(100vh-200px)] table-container">
                    <table class="w-full min-w-full text-sm text-left text-gray-700">
                        <thead class="sticky top-0 z-10 text-xs font-semibold tracking-wider uppercase bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-black">Student ID</th>
                                <th class="px-6 py-3 text-black">Name</th>
                                <th class="px-6 py-3 text-black">Batch</th>
                                <th class="px-6 py-3 text-black">Group</th>
                                <th class="px-6 py-3 text-black">Out Date</th>
                                <th class="px-6 py-3 text-black">Out Time</th>
                                <th class="px-6 py-3 text-black">Remark</th>
                                <th class="px-6 py-3 text-black">Consideration</th>
                                <th class="px-6 py-3 text-black">In Date</th>
                                <th class="px-6 py-3 text-black">In Time</th>
                                <th class="px-6 py-3 text-black">Remark</th>
                                <th class="px-6 py-3 text-black">Consideration</th>
                                <th class="px-6 py-3 text-black">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700">
                            @forelse($logs as $log)
                                @php
                                    // Normalize values
                                    $batch = data_get($log, 'batch') ?? 'N/A';
                                    $group = data_get($log, 'group') ?? 'N/A';
                                    $studentId = data_get($log, 'student_id') ?? '—';

                                    // User info
                                    $user = data_get($log, 'user', []);
                                    $fname = data_get($user, 'user_fname') ?? 'N/A';
                                    $lname = data_get($user, 'user_lname') ?? '';

                                    // Time out / in raw
                                    $going_home_log = data_get($log, 'going_home_log');
                                    // dd($going_home_log);
                                    $timeOutRaw = data_get($going_home_log, 'date_time_out');
                                    $timeInRaw = data_get($going_home_log, 'date_time_in');

                                    // Formatted values
                                    $displayDateOut = $timeOutRaw ? \Carbon\Carbon::parse($timeOutRaw)->format('M j, Y') : '—';
                                    $displayDateIn  = $timeInRaw ? \Carbon\Carbon::parse($timeInRaw)->format('M j, Y') : '—';

                                    $formattedTimeOut = data_get($going_home_log, 'time_out') ?? ($timeOutRaw ? \Carbon\Carbon::parse($timeOutRaw)->format('g:i A') : '—');
                                    $formattedTimeIn  = data_get($going_home_log, 'time_in') ?? ($timeInRaw ? \Carbon\Carbon::parse($timeInRaw)->format('g:i A') : '—');
                                // dd($formattedTimeOut);

                                    // Remarks
                                    $timeOutRemark = data_get($going_home_log, 'time_out_remarks') ?? '—';
                                    $timeInRemark  = data_get($going_home_log, 'time_in_remarks') ?? '—';

                                    // Considerations
                                    $timeOutConsideration = data_get($going_home_log, 'time_out_consideration');
                                    $timeOutReason = data_get($going_home_log, 'time_out_reason');
                                    $timeOutMonitor = data_get($going_home_log, 'time_out_monitor_name') ?? 'Monitor';

                                    $timeInConsideration = data_get($going_home_log, 'time_in_consideration');
                                    $timeInReason = data_get($going_home_log, 'time_in_reason');
                                    $timeInMonitor = data_get($going_home_log, 'time_in_monitor_name') ?? 'Monitor';

                                    // Manual entry + approval
                                    $isManual = data_get($going_home_log, 'is_manual_entry') ?? false;
                                    $approvalStatus = data_get($going_home_log, 'approval_status');
                                @endphp

                                <tr class="border-b border-gray-200 hover:bg-gray-50">
                                    {{-- Student ID --}}
                                    <td class="px-6 py-4 font-medium">{{ $studentId }}</td>

                                    {{-- Student Name --}}
                                    <td class="px-6 py-4">{{ $fname }} {{ $lname }}</td>

                                    {{-- Batch --}}
                                    <td class="px-6 py-4">{{ $batch }}</td>

                                    {{-- Group --}}
                                    <td class="px-6 py-4">{{ $group }}</td>

                                    {{-- Date Out --}}
                                    <td class="px-6 py-4">{{ $displayDateOut }}</td>

                                    {{-- Time Out --}}
                                    <td class="px-6 py-4">{{ $formattedTimeOut }}</td>

                                    {{-- Time Out Remark --}}
                                    <td class="px-6 py-4">
                                        <span class="{{ $timeOutRemark === 'Late' ? 'text-red-600 font-bold' : ($timeOutRemark === 'On Time' ? 'text-blue-600 font-bold' : ($timeOutRemark === 'Early' ? 'text-green-600 font-bold' : '')) }}">
                                            {{ $timeOutRemark }}
                                        </span>
                                    </td>

                                    {{-- Time Out Consideration --}}
                                    <td class="px-6 py-4">
                                        @if($timeOutConsideration)
                                            <div class="p-3 border rounded-lg bg-gray-50">
                                                <div class="font-semibold text-sm {{ $timeOutConsideration === 'Excused' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $timeOutConsideration }}
                                                </div>
                                                @if($timeOutReason)
                                                    <div class="mt-1 text-xs italic text-gray-600">{{ $timeOutReason }}</div>
                                                @endif
                                                <div class="mt-1 text-xs font-medium text-blue-600">Set by: {{ $timeOutMonitor }}</div>
                                            </div>
                                        @elseif($timeOutRemark === 'On Time')
                                            <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                                <div class="text-sm font-semibold text-blue-600">No Consideration Needed</div>
                                            </div>
                                        @elseif($timeOutRemark === '—')
                                            <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">No remarks</div>
                                        @else
                                            <button type="button"
                                                onclick="openConsiderationModal('{{ $log->going_home_log->id }}', 'time_out')"
                                                class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md shadow hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-400">
                                                Add Consideration (Time Out)
                                            </button>
                                        @endif
                                    </td>

                                    {{-- Date In --}}
                                    <td class="px-6 py-4">{{ $displayDateIn }}</td>

                                    {{-- Time In --}}
                                    <td class="px-6 py-4">{{ $formattedTimeIn }}</td>

                                    {{-- Time In Remark --}}
                                    <td class="px-6 py-4">
                                        <span class="{{ $timeInRemark === 'Late' ? 'text-red-600 font-bold' : ($timeInRemark === 'On Time' ? 'text-blue-600 font-bold' : ($timeInRemark === 'Early' ? 'text-green-600 font-bold' : '')) }}">
                                            {{ $timeInRemark }}
                                        </span>
                                    </td>

                                    {{-- Time In Consideration --}}
                                    <td class="px-6 py-4">
                                        @if($timeInConsideration)
                                            <div class="p-3 border rounded-lg bg-gray-50">
                                                <div class="font-semibold text-sm {{ $timeInConsideration === 'Excused' ? 'text-green-600' : 'text-red-600' }}">
                                                    {{ $timeInConsideration }}
                                                </div>
                                                @if($timeInReason)
                                                    <div class="mt-1 text-xs italic text-gray-600">{{ $timeInReason }}</div>
                                                @endif
                                                <div class="mt-1 text-xs font-medium text-blue-600">Set by: {{ $timeInMonitor }}</div>
                                            </div>
                                        @elseif($timeInRemark === 'On Time')
                                            <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">
                                                <div class="text-sm font-semibold text-blue-600">No Consideration Needed</div>
                                            </div>
                                        @elseif($timeInRemark === '—')
                                            <div class="p-3 border border-blue-200 rounded-lg bg-blue-50">No remarks</div>
                                        @else
                                            <button type="button"
                                                onclick="openConsiderationModal('{{ $log->going_home_log->id }}', 'time_in')"
                                                class="px-4 py-2 text-sm font-medium text-white bg-orange-500 rounded-md shadow hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-orange-400">
                                                Add Consideration (Time In)
                                            </button>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td class="px-6 py-4">
                                        @if($isManual)
                                            @if($approvalStatus === 'pending')
                                                <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">Pending</span>
                                            @elseif($approvalStatus === 'approved')
                                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Approved</span>
                                            @elseif($approvalStatus === 'rejected')
                                                <span class="px-2 py-1 text-xs font-medium text-red-800 bg-green-100 rounded-full">Rejected</span>
                                            @endif
                                        @else
                                            @if($timeOutRaw && $timeInRaw)
                                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">Complete</span>
                                            @elseif($timeOutRaw)
                                                <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">Got Out</span>
                                            @else
                                                <span class="px-2 py-1 text-xs font-medium text-gray-800 bg-gray-100 rounded-full">Not Logged</span>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="px-6 py-6 text-center text-gray-500">
                                        <i data-feather="inbox" class="w-8 h-8 mx-auto mb-2"></i>
                                        <div>No logs for selected date.</div>
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
    </div>

        <div id="ManualEntryModal"
            class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-600 bg-opacity-50">
            <div class="w-11/12 max-w-2xl p-8 bg-white border border-gray-200 shadow-2xl rounded-3xl animate-fadeIn">
                <div class="flex flex-col items-center justify-center mb-8 text-center">
                    <h3 class="text-3xl font-extrabold text-orange-700">Going Home Manual Entry</h3>
                </div>

                <form id="manualEntryForm"
                    action="{{ route('monitor.goingHomeLog.submit') }}"
                    method="POST"
                    class="space-y-6">
                    @csrf

                    <div>
                        <label for="student_id" class="block mb-3 font-semibold text-gray-700">
                            Student ID <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="student_id" id="student_id" required
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                            placeholder="Enter student ID">
                    </div>

                    <div>
                        <label for="schedule_name" class="block mb-3 font-semibold text-gray-700">
                            Schedule Type <span class="text-red-500">*</span>
                        </label>
                        <select name="schedule_name" id="schedule_name" required
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                            <option value="">Select schedule...</option>
                            @foreach($types as $scheduleType)
                                <option value="{{ $scheduleType }}"
                                    {{ request('type') == $scheduleType ? 'selected' : '' }}>
                                    {{ $scheduleType }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="entryType" class="block mb-3 font-semibold text-gray-700">
                            Entry Type <span class="text-red-500">*</span>
                        </label>
                        <select id="entryType" name="entry_type" required
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                            <option value="">Select entry type...</option>
                            <option value="time_out">Got Out Only</option>
                            <option value="time_in">Got In Only</option>
                            <option value="both">Both Get In & Get Out</option>
                        </select>
                    </div>

                    <div id="timeOutField" class="grid hidden grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block mb-3 font-semibold text-gray-700">Date Get Out</label>
                            <input type="date" name="date_time_out" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block mb-3 font-semibold text-gray-700">Time Out</label>
                            <input type="time" id="timeOut" name="time_out"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                    </div>

                    <div id="timeInField" class="grid hidden grid-cols-1 gap-6 md:grid-cols-2">
                        <div>
                            <label class="block mb-3 font-semibold text-gray-700">Date Get In</label>
                            <input type="date" name="date_time_in" value="{{ $selectedDate ?? now()->format('Y-m-d') }}" max="{{ now()->format('Y-m-d') }}"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                        <div>
                            <label class="block mb-3 font-semibold text-gray-700">Time In</label>
                            <input type="time" id="timeIn" name="time_in"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label for="reason" class="block mb-3 font-semibold text-gray-700">
                            Reason for Manual Entry <span class="text-red-500">*</span>
                        </label>
                        <textarea id="reason" name="reason" rows="4" required
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                            placeholder="Explain why this manual entry is necessary..."></textarea>
                    </div>

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

                <form id="considerationForm" action="{{ route('logs.consideration.goinghome') }}" method="POST">
                    @csrf
                    <input type="hidden" id="logIdInput" name="log_id">
                    <input type="hidden" id="typeInput" name="type">

                    <label class="block mb-2 text-sm font-medium text-gray-700">Select Status</label>
                    <select name="choice"
                        class="w-full px-3 py-2 mb-4 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500"
                        required>
                        <option value="">Select...</option>
                        <option value="Excused" class="text-green-600">✓ Excused</option>
                        <option value="Not Excused" class="text-red-600">✗ Not Excused</option>
                    </select>

                    <label class="block mb-2 text-sm font-medium text-gray-700">Reason</label>
                    <textarea name="reason"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md resize-none focus:outline-none focus:ring-2 focus:ring-orange-500"
                        rows="3"
                        placeholder="Enter reason for this consideration..."
                        maxlength="200"
                        required></textarea>
                    <div class="mt-1 text-xs text-gray-500">Maximum 200 characters</div>

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

    <div id="messageModal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black bg-opacity-50">
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

    @if(session('success'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                openMessageModal('success', "{{ session('success') }}");
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                openMessageModal('error', "{{ session('error') }}");
            });
        </script>
    @endif
    <script>
        function openConsiderationModal(logId, type) {
            const modal = document.getElementById("considerationModal");
            const logInput = document.getElementById("logIdInput");
            const typeInput = document.getElementById("typeInput");
            const title = document.getElementById("modalTitle");

            if (modal) {
                modal.classList.remove("hidden");
                logInput.value = logId;
                typeInput.value = type;
                title.textContent = `Add Consideration (${type.replace("_", " ").toUpperCase()})`;
            }
        }

        function closeConsiderationModal() {
            const modal = document.getElementById("considerationModal");
            if (modal) {
                modal.classList.add("hidden");
            }
        }

        function openMessageModal(type, message) {
            const modal = document.getElementById('messageModal');
            const header = document.getElementById('messageModalHeader');
            const title = document.getElementById('messageModalTitle');
            const body = document.getElementById('messageModalBody');

            // Reset header classes
            header.className = "px-4 py-3 text-white rounded-t-lg";

            if (type === 'success') {
                header.classList.add('bg-green-600');
                title.textContent = "Success";
            } else if (type === 'error') {
                header.classList.add('bg-red-600');
                title.textContent = "Error";
            }

            body.textContent = message;
            modal.classList.remove('hidden'); // Show modal
        }

        function closeMessageModal() {
            const modal = document.getElementById('messageModal');
            modal.classList.add('hidden'); // Hide modal
        }
        document.addEventListener("DOMContentLoaded", function () {
            const modal = document.getElementById("ManualEntryModal");
            const entryTypeSelect = document.getElementById("entryType");
            const timeOutField = document.getElementById("timeOutField");
            const timeInField = document.getElementById("timeInField");
            const form = document.getElementById("manualEntryForm");
            const submitBtn = document.getElementById("submitBtn");

            window.openVisitorManualEntryModal = function () {
                modal.classList.remove("hidden");
            };

            window.closeVisitorManualEntryModal = function () {
                modal.classList.add("hidden");
                form.reset();
                timeOutField.classList.add("hidden");
                timeInField.classList.add("hidden");
            };

            entryTypeSelect.addEventListener("change", function () {
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

            form.addEventListener("submit", function () {
                submitBtn.disabled = true;
                submitBtn.textContent = "Submitting...";
            });
        });
    </script>
</x-monitorLayout>
