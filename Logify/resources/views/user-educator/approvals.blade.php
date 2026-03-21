<x-educator-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="p-8 bg-white shadow-md rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-orange-700">MANUAL ENTRY APPROVALS</h1>
            <div class="flex items-center space-x-4">
                <form method="GET" class="flex items-center space-x-2">
                    <select name="type" class="px-3 py-2 text-sm border border-gray-300 rounded-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="all" {{ $type === 'all' ? 'selected' : '' }}>All Types</option>
                        <option value="academic" {{ $type === 'academic' ? 'selected' : '' }}>Academic</option>
                        <option value="going_out" {{ $type === 'going_out' ? 'selected' : '' }}>Going Out</option>
                        <option value="visitor" {{ $type === 'visitor' ? 'selected' : '' }}>Visitor</option>
                    </select>
                    <select name="status" class="px-3 py-2 text-sm border border-gray-300 rounded-sm focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                    </select>
                    <button type="submit" class="px-4 py-2 text-sm font-semibold text-white bg-orange-500 rounded-sm hover:bg-orange-600">
                        Filter
                    </button>
                </form>

                <a href="{{ route('educator.dashboard') }}"
                    class="inline-flex items-center text-sm font-medium text-blue-600 hover:underline">
                    <i data-feather="arrow-left" class="w-5 h-5 mr-1"></i> Back to Dashboard
                </a>
            </div>
        </div>

        {{-- Status Summary --}}
        <div class="grid grid-cols-1 gap-4 mb-6 md:grid-cols-3">
            <div class="p-4 border border-yellow-200 rounded-lg bg-yellow-50">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-yellow-800">Pending Approval</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $pendingCount }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 border border-green-200 rounded-lg bg-green-50">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-green-800">Approved</p>
                        <p class="text-2xl font-bold text-green-900">{{ $approvedCount }}</p>
                    </div>
                </div>
            </div>

            <div class="p-4 border border-red-200 rounded-lg bg-red-50">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-red-800">Rejected</p>
                        <p class="text-2xl font-bold text-red-900">{{ $rejectedCount }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bulk Actions --}}
        @if($status === 'pending')
        <div class="p-4 mb-4 border border-blue-200 rounded-lg bg-blue-50">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="selectAll" class="mr-3 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                    <label for="selectAll" class="text-sm font-medium text-blue-800">Select All</label>
                </div>
                <div class="flex space-x-2">
                    <button id="bulkApproveBtn" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-md hover:bg-green-700 disabled:opacity-50" disabled>
                        Bulk Approve
                    </button>
                </div>
            </div>
        </div>
        @endif

        {{-- Manual Entries Table --}}
        <div class="relative w-full overflow-hidden border border-gray-200 rounded-lg">
            <div class="w-full overflow-x-auto">
                <table class="w-full min-w-full text-sm text-left text-gray-700">
                    <thead class="text-xs font-semibold tracking-wider uppercase bg-gray-100">
                        <tr>
                            @if($status === 'pending')
                            <th class="px-6 py-3 text-black">
                                <input type="checkbox" class="text-orange-600 border-gray-300 rounded focus:ring-orange-500" disabled>
                            </th>
                            @endif
                            <th class="px-6 py-3 text-black">Student/Visitor</th>
                            <th class="px-6 py-3 text-black">Type</th>
                            <th class="px-6 py-3 text-black">Entry Type</th>
                            <th class="px-6 py-3 text-black">Monitor</th>
                            <th class="px-6 py-3 text-black">Date</th>
                            <th class="px-6 py-3 text-black">Status</th>
                            <th class="px-6 py-3 text-black">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm text-gray-700 divide-y divide-gray-200">
                        @forelse($manualEntries as $entry)
                        <tr class="hover:bg-gray-50">
                            @if($status === 'pending')
                            <td class="px-6 py-4">
                                @if($entry->status === 'pending')
                                <input type="checkbox" class="text-orange-600 border-gray-300 rounded entry-checkbox focus:ring-orange-500"
                                       value="{{ $entry->id }}">
                                @endif
                            </td>
                            @endif
                            <td class="px-6 py-4">
                                <div>
                                    <div class="font-medium">
                                        {{ $entry->studentDetail->user->user_fname ?? $entry->manual_data['visitor_name'] ?? '' }}
                                        {{ $entry->studentDetail->user->user_lname ?? '' }}
                                    </div>
                                    <div class="text-xs text-gray-500">{{ $entry->student_id }}</div>
                                    <div class="text-xs text-gray-500">
                                        {{ optional($entry->studentDetail)->batch ? 'Batch ' . optional($entry->studentDetail)->batch : '' }}
                                        {{ optional($entry->studentDetail)->group ? ', Group ' . optional($entry->studentDetail)->group : '' }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full
                                    {{ $entry->log_type === 'academic'
                                        ? 'bg-blue-100 text-blue-800'
                                        : ($entry->log_type === 'going_out'
                                            ? 'bg-orange-100 text-orange-800'
                                            : ($entry->log_type === 'intern'
                                                ? 'bg-purple-100 text-purple-800'
                                                : ($entry->log_type === 'going_home'
                                                    ? 'bg-pink-100 text-pink-800'
                                                    : 'bg-green-100 text-green-800'))) }}">
                                    {{ $entry->log_type === 'academic'
                                        ? 'Academic'
                                        : ($entry->log_type === 'going_out'
                                            ? 'Leisure'
                                            : ($entry->log_type === 'intern'
                                                ? 'Intern'
                                                : ($entry->log_type === 'going_home'
                                                    ? 'Going Home'
                                                    : 'Visitor'))) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">{{ $entry->formatted_entry_type }}</td>
                            <td class="px-6 py-4">{{ $entry->monitor_name }}</td>
                            <td class="px-6 py-4">{{ $entry->created_at->format('M j, Y g:i A') }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $entry->status_badge_class }}">
                                    {{ $entry->formatted_status }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex space-x-2">
                                    <button onclick="viewEntryDetails({{ $entry->id }})"
                                        class="px-3 py-1 text-xs text-blue-600 bg-blue-100 rounded hover:bg-blue-200">
                                        View Details
                                    </button>
                                    @if($entry->status === 'pending')
                                    <button onclick="openApprovalModal({{ $entry->id }}, '{{ $entry->log_type }}')"
                                        class="px-3 py-1 text-xs text-white bg-green-500 rounded hover:bg-green-600">
                                        Approve
                                    </button>
                                    <button onclick="rejectEntry({{ $entry->id }}, '{{ $entry->log_type }}')"
                                            class="px-3 py-1 text-xs text-white bg-red-500 rounded hover:bg-red-600">
                                        Reject
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ $status === 'pending' ? '8' : '7' }}" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center text-gray-500">
                                    <svg class="w-12 h-12 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="mb-2 text-lg font-medium">No Manual Entries Found</h3>
                                    <p class="text-sm">No manual entries match the current filters.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Student Pagination --}}
        @if($manualEntries->hasPages())
        <div class="flex justify-end mt-6">
            {{ $manualEntries->appends(request()->query())->links() }}
        </div>
        @endif
    </div>

    <div id="detailsModal" class="fixed inset-0 hidden w-full h-full overflow-y-auto bg-gray-600 bg-opacity-50" style="z-index: 50;">
        <div class="relative w-11/12 p-5 mx-auto bg-white border rounded-md shadow-lg top-20 md:w-3/4 lg:w-1/2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Entry Details</h3>
                <button type="button" onclick="closeDetailsModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div id="detailsModalBody" class="space-y-3">
                <!-- Filled dynamically -->
                <p><span class="font-semibold">Visitor Name:</span> <span id="detailVisitorName"></span></p>
                <p><span class="font-semibold">Log Type:</span> <span id="detailLogType"></span></p>
                <p><span class="font-semibold">Date:</span> <span id="detailDate"></span></p>
                <p><span class="font-semibold">Notes:</span> <span id="detailNotes"></span></p>
            </div>

            <div class="flex justify-end mt-4">
                <button type="button" onclick="closeDetailsModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">
                    Close
                </button>
            </div>
        </div>
    </div>

   {{-- Approval Modal --}}
<div id="approvalModal"
    class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-600 bg-opacity-50">

    <!-- Modal Box -->
    <div class="w-11/12 max-w-lg max-h-[90vh] overflow-y-auto p-6 bg-white border border-gray-200 shadow-2xl rounded-2xl animate-fadeIn">

        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-800" id="approvalModalTitle">
                Approve Manual Entry
            </h3>
            <button type="button" onclick="closeApprovalModal()"
                class="text-gray-400 transition hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Form -->
        <form action="{{ route('educator.approvals.approve') }}" method="POST">
            @csrf
            <input type="hidden" id="approvalEntryId" name="entry_id">
            <input type="hidden" id="approvalLogType" name="log_type">

            <!-- Notes -->
            <div class="mb-5">
                <label for="approvalNotes" class="block mb-2 text-sm font-medium text-gray-700">
                    Notes
                </label>
                <textarea id="approvalNotes" name="notes" rows="4"
                    class="w-full px-4 py-2 placeholder-gray-400 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500"
                    placeholder="Add any notes about this approval or rejection..."></textarea>
            </div>

            <!-- Buttons -->
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeApprovalModal()"
                    class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-gray-200 rounded-xl hover:bg-gray-300">
                    Cancel
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-medium text-white transition bg-green-600 shadow-md rounded-xl hover:bg-green-700">
                    Approve
                </button>
            </div>
        </form>
    </div>
</div>

        {{-- Rejection Modal --}}
        <div id="rejectionModal"
            class="fixed inset-0 hidden flex items-center justify-center bg-gray-600 bg-opacity-50 z-[9999]">

            <!-- Modal Box -->
            <div class="w-11/12 max-w-lg max-h-[90vh] overflow-y-auto p-6 bg-white border border-gray-200 shadow-2xl rounded-2xl animate-fadeIn">

                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-xl font-bold text-gray-800" id="rejectionModalTitle">
                        Reject Manual Entry
                    </h3>
                    <button type="button" onclick="closeRejectionModal()"
                        class="text-gray-400 transition hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Form -->
                <form action="{{ route('educator.approvals.reject') }}" method="POST">
                    @csrf
                    <input type="hidden" id="rejectionEntryId" name="entry_id">
                    <input type="hidden" id="rejectionLogType" name="log_type">

                    <!-- Reason -->
                    <div class="mb-5">
                        <label for="rejectionNotes" class="block mb-2 text-sm font-medium text-gray-700">
                            Reason for Rejection
                        </label>
                        <textarea id="rejectionNotes" name="notes" rows="4"
                            class="w-full px-4 py-2 placeholder-gray-400 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-red-500"
                            placeholder="Provide a reason for rejecting this entry..."></textarea>
                    </div>

                    <!-- Buttons -->
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeRejectionModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 transition bg-gray-200 rounded-xl hover:bg-gray-300">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-5 py-2 text-sm font-medium text-white transition bg-red-600 shadow-md rounded-xl hover:bg-red-700">
                            Reject
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

        function viewEntryDetails(entryId) {
            fetch(`/educator/approvals/details/${entryId}`)
                .then(response => {
                    if (!response.ok) throw new Error("Network response was not ok");
                    return response.json();
                })
                .then(data => {
                    const entry = data.entry;
                    const user = data.user;
                    const name = entry.manual_data.visitor_name || (user ? `${user.user_fname} ${user.user_lname}` : '-');
                    document.getElementById('detailVisitorName').textContent = name
                    document.getElementById('detailLogType').textContent = entry.log_type || '-';
                    let date = '-';
                    switch (entry.log_type) {
                        case 'academic':
                        case 'intern':
                            date = entry.manual_data?.academic_date || '-';
                            break;
                        case 'going_out':
                            date = entry.manual_data?.going_out_date || '-';
                            break;
                        case 'going_home':
                            date = entry.manual_data?.date_time_out || '-';
                            break;
                        case 'visitor':
                            date = entry.manual_data?.visit_date || '-';
                            break;
                    }
                    document.getElementById('detailDate').textContent = date;
                    document.getElementById('detailNotes').textContent =
                        entry.reason || entry.notes || '-';
                    document.getElementById('detailsModal').classList.remove('hidden');
                })
                .catch(error => {
                    console.error("Error loading entry details:", error);
                    alert("Something went wrong. Check console.");
                });
        }

        function closeDetailsModal() {
            document.getElementById('detailsModal').classList.add('hidden');
        }


        function openApprovalModal(entryId, logType) {
            const modal = document.getElementById("approvalModal");
            const entryInput = document.getElementById("approvalEntryId");
            const logTypeInput = document.getElementById("approvalLogType");

            // Fill hidden fields
            entryInput.value = entryId;
            logTypeInput.value = logType;

            // Show modal
            modal.classList.remove("hidden");
        }

        function closeApprovalModal() {
            const modal = document.getElementById("approvalModal");
            const entryInput = document.getElementById("approvalEntryId");
            const logTypeInput = document.getElementById("approvalLogType");
            const notes = document.getElementById("approvalNotes");

            modal.classList.add("hidden");

            // Reset fields
            entryInput.value = "";
            logTypeInput.value = "";
            notes.value = "";
        }

        function rejectEntry(entryId, logType) {
            const modal = document.getElementById("rejectionModal");
            const entryInput = document.getElementById("rejectionEntryId");
            const logTypeInput = document.getElementById("rejectionLogType");

            // Fill hidden inputs
            entryInput.value = entryId;
            logTypeInput.value = logType;

            // Show modal
            modal.classList.remove("hidden");
        }

        function closeRejectionModal() {
            const modal = document.getElementById("rejectionModal");
            modal.classList.add("hidden");
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Select all functionality
            const selectAllCheckbox = document.getElementById('selectAll');
            const entryCheckboxes = document.querySelectorAll('.entry-checkbox');
            const bulkApproveBtn = document.getElementById('bulkApproveBtn');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function() {
                    entryCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateBulkButtons();
                });
            }

            entryCheckboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateBulkButtons);
            });

            if (bulkApproveBtn) {
                bulkApproveBtn.addEventListener('click', bulkApprove);
            }

            document.getElementById('approvalForm').addEventListener('submit', submitApproval);
        });

        function updateBulkButtons() {
            const checkedBoxes = document.querySelectorAll('.entry-checkbox:checked');
            const bulkApproveBtn = document.getElementById('bulkApproveBtn');

            if (bulkApproveBtn) {
                bulkApproveBtn.disabled = checkedBoxes.length === 0;
            }
        }

        function closeEntryDetailsModal() {
            document.getElementById('entryDetailsModal').classList.add('hidden');
        }

        function bulkApprove() {
            const checkedBoxes = document.querySelectorAll('.entry-checkbox:checked');
            const entryIds = Array.from(checkedBoxes).map(cb => cb.value);

            if (entryIds.length === 0) {
                alert('Please select entries to approve');
                return;
            }

            if (!confirm(`Are you sure you want to approve ${entryIds.length} entries?`)) {
                return;
            }

            const formData = new FormData();
            entryIds.forEach(id => formData.append('entry_ids[]', id));
            formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            fetch('/educator/approvals/bulk-approve', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert(data.message || 'Failed to approve entries');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred during bulk approval');
            });
        }
    </script>
</x-educator-layout>
