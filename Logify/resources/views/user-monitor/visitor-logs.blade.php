<x-monitorLayout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="p-8 bg-white shadow-md rounded-2xl">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-orange-700">VISITOR LOGS MONITOR</h1>
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

        {{-- Search Bar Only --}}
        <div class="mb-6">
            <form action="{{ route('monitor.visitor.logs') }}" method="GET" class="flex items-center space-x-2">
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

                {{-- Submit Button --}}
                <div class="flex-1 lg:max-w-xs">
                    <label class="block mb-1 text-sm font-medium text-gray-700">&nbsp;</label>
                    <button type="submit"
                            class="w-full px-4 py-2 text-sm font-semibold text-white bg-orange-500 rounded-md hover:bg-orange-600">
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
                            <thead class="text-xs font-semibold tracking-wider uppercase bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-black">Visitor Pass</th>
                                <th class="px-6 py-3 text-black">Visitor Name</th>
                                <th class="px-6 py-3 text-black">Valid ID</th>
                                <th class="px-6 py-3 text-black">ID Number</th>
                                <th class="px-6 py-3 text-black">Relationship</th>
                                <th class="px-6 py-3 text-black">Purpose</th>
                                <th class="px-6 py-3 text-black">Date</th>
                                <th class="px-6 py-3 text-black">Time In</th>
                                <th class="px-6 py-3 text-black">Time Out</th>
                                <th class="px-6 py-3 text-black">Status</th>
                            </tr>
                        </thead>
                        <tbody class="text-sm text-gray-700">
                            @forelse($logs as $log)
                            @php
                            // dd($log);
                            @endphp
                                <tr class="border-b border-gray-200 hover:bg-gray-50"
                                    data-visitor-name="{{ strtolower($log->visitor_name ?? '') }}"
                                    data-id-number="{{ strtolower($log->id_number ?? '') }}"
                                    data-purpose="{{ strtolower($log->purpose ?? '') }}"
                                    data-date="{{ $log->visit_date ?? '' }}"
                                    data-status="{{ $log->time_out ? 'completed' : 'active' }}">
                                    <td class="px-6 py-4 font-medium">{{ $log->visitor_pass ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $log->visitor_name ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $log->valid_id ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $log->id_number ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $log->relationship ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ $log->purpose ?? '—' }}</td>
                                    <td class="px-6 py-4">{{ \Carbon\Carbon::parse($log->visit_date)->format('M j, Y') }}</td>
                                    <td class="px-6 py-4">{{ $log->formatted_time_in }}</td>
                                    <td class="px-6 py-4">{{ $log->formatted_time_out }}</td>
                                    <td class="px-6 py-4">
                                        @if($log->is_manual_entry)
                                            @if($log->approval_status === 'pending')
                                                <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                                                    Pending Approval
                                                </span>
                                            @elseif($log->approval_status === 'approved')
                                                <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                    Approved
                                                </span>
                                            @elseif($log->approval_status === 'rejected')
                                                <span class="px-2 py-1 text-xs font-medium text-red-800 bg-green-100 rounded-full">
                                                    Rejected
                                                </span>
                                            @endif
                                        @else
                                            <span class="px-2 py-1 text-xs font-medium text-blue-800 bg-blue-100 rounded-full">
                                                Regular Entry
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="px-6 py-8 text-center text-gray-500">
                                        <i data-feather="inbox" class="w-8 h-8 mx-auto mb-2"></i>
                                        <div>No visitor logs found for the selected criteria.</div>
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

        {{-- Visitor Manual Entry Modal (restyled to match Leisure) --}}
        <div id="visitorManualEntryModal"
            class="fixed inset-0 z-50 flex items-center justify-center hidden bg-gray-600 bg-opacity-50">

    <div class="w-11/12 max-w-2xl max-h-[90vh] overflow-y-auto p-8 bg-white border border-gray-200 shadow-2xl rounded-3xl animate-fadeIn">

                <!-- Header -->
                <div class="flex flex-col items-center justify-center mb-8 text-center">
                    <h3 class="text-3xl font-extrabold text-orange-700">Visitor Manual Entry</h3>
                </div>

                <!-- Form -->
                <form id="visitorManualEntryForm" class="space-y-6">

                    <!-- Entry Type -->
                    <div>
                        <label class="block mb-3 font-semibold text-gray-700">Entry Type <span class="text-red-500">*</span></label>
                        <select id="entryType" name="entry_type" required
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                            <option value="">Select entry type...</option>
                            <option value="time_in">Got In Only</option>
                            <option value="time_out">Got Out Only</option>
                            <option value="both">Both Get In & Out</option>
                        </select>
                    </div>

                    <!-- Visitor Info -->
                    <div id="visitorName">
                        <label class="block mb-3 font-semibold text-gray-700">Select Visitor</label>
                        <input id="visitorId" name="visitor_name"
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                            placeholder="Type Visitor Name...">

                        <label class="block mt-6 mb-3 font-semibold text-gray-700">Visitor Pass</label>
                        <input type="text" id="visitorPass" name="visitor_pass"
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">

                        <label class="block mt-6 mb-3 font-semibold text-gray-700">Date</label>
                        <input type="date" id="modalDate" name="date" value="{{ $selectedDate }}" max="{{ now()->format('Y-m-d') }}"
                            class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                    </div>

                    <!-- Existing Visitor Section -->
                    <div id="existingVisitorSection" class="hidden space-y-6">

                        <div>
                            <label class="block mb-3 font-semibold text-gray-700">Valid ID</label>
                            <select id="validId" name="valid_id"
                                    class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                                <option value="">Select ID type...</option>
                                <option value="Driver's License">Driver's License</option>
                                <option value="Passport">Passport</option>
                                <option value="National ID">National ID</option>
                                <option value="SSS ID">SSS ID</option>
                                <option value="PhilHealth ID">PhilHealth ID</option>
                                <option value="Voter's ID">Voter's ID</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div id="otherIdSection" class="hidden">
                            <label class="block mb-3 font-semibold text-gray-700">Specify Other ID Type</label>
                            <input type="text" id="otherIdType" name="other_id_type"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                placeholder="Enter ID type">
                        </div>

                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                            <div>
                                <label class="block mb-3 font-semibold text-gray-700">ID Number</label>
                                <input type="text" id="idNumber" name="id_number"
                                    class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                    placeholder="Enter ID number">
                            </div>
                            <div>
                                <label class="block mb-3 font-semibold text-gray-700">Relationship</label>
                                <input type="text" id="relationship" name="relationship"
                                    class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                    placeholder="e.g., Parent, Guardian, Relative">
                            </div>
                        </div>

                        <div>
                            <label class="block mb-3 font-semibold text-gray-700">Purpose of Visit</label>
                            <input type="text" id="purpose" name="purpose"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none"
                                placeholder="Enter purpose of visit">
                        </div>
                    </div>

                    <!-- Time Fields -->
                    <div id="timeFields" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div id="timeInField" class="hidden">
                            <label class="block mb-3 font-semibold text-gray-700">Time In</label>
                            <input type="time" id="timeIn" name="time_in"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                        <div id="timeOutField" class="hidden">
                            <label class="block mb-3 font-semibold text-gray-700">Time Out</label>
                            <input type="time" id="timeOut" name="time_out"
                                class="w-full px-4 py-3 bg-gray-100 border border-gray-300 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none">
                        </div>
                    </div>

                    <!-- Reason -->
                    <div>
                        <label class="block mb-3 font-semibold text-gray-700">Reason for Manual Entry <span class="text-red-500">*</span></label>
                        <textarea id="reason" name="reason" rows="3" required
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


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initializeVisitorManualEntryModal();
            const visitorSearch = document.getElementById('visitorSearch');
            const clearSearch = document.getElementById('clearSearch');
            const searchResults = document.getElementById('searchResults');
            const searchCount = document.getElementById('searchCount');
            const tableRows = document.querySelectorAll('tbody tr[data-visitor-name]');

            function performSearch() {
                const searchTerm = visitorSearch.value.toLowerCase().trim();

                if (searchTerm === '') {
                    clearSearch.classList.add('hidden');
                    searchResults.classList.add('hidden');

                    tableRows.forEach(row => {
                        row.style.display = '';
                    });
                    return;
                }

                clearSearch.classList.remove('hidden');
                searchResults.classList.remove('hidden');

                let matchCount = 0;
                let firstMatch = null;

                tableRows.forEach(row => {
                    const visitorName = row.getAttribute('data-visitor-name') || '';
                    const idNumber = row.getAttribute('data-id-number') || '';
                    const purpose = row.getAttribute('data-purpose') || '';

                    const isMatch = visitorName.includes(searchTerm) ||
                                   idNumber.includes(searchTerm) ||
                                   purpose.includes(searchTerm);

                    if (isMatch) {
                        matchCount++;
                        if (!firstMatch) firstMatch = row;
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                searchCount.textContent = matchCount;

                if (firstMatch) {
                    firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }

            function clearSearchFunction() {
                visitorSearch.value = '';
                clearSearch.classList.add('hidden');
                searchResults.classList.add('hidden');

                tableRows.forEach(row => {
                    row.style.display = '';
                });
            }

            visitorSearch.addEventListener('input', performSearch);
            clearSearch.addEventListener('click', clearSearchFunction);
        });

        function handleConsiderationSubmit(event, logId) {
            event.preventDefault();

            const form = event.target;
            const formData = new FormData(form);
            const errorDiv = document.getElementById(`visitor-error-${logId}`);
            const successDiv = document.getElementById(`visitor-success-${logId}`);

            errorDiv.classList.add('hidden');
            successDiv.classList.add('hidden');

            const submitButton = form.querySelector('button[type="submit"]');
            const select = form.querySelector('select');
            const textarea = form.querySelector('textarea');

            submitButton.disabled = true;
            select.disabled = true;
            textarea.disabled = true;

            fetch('{{ route('monitor.visitor.consideration', ':id') }}'.replace(':id', logId), {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    successDiv.textContent = data.message;
                    successDiv.classList.remove('hidden');

                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    errorDiv.textContent = data.message || 'An error occurred';
                    errorDiv.classList.remove('hidden');

                    submitButton.disabled = false;
                    select.disabled = false;
                    textarea.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                errorDiv.textContent = 'An error occurred while updating consideration';
                errorDiv.classList.remove('hidden');

                submitButton.disabled = false;
                select.disabled = false;
                textarea.disabled = false;
            });

            return false;
        }

        function openVisitorManualEntryModal() {
            document.getElementById('visitorManualEntryModal').classList.remove('hidden');
            document.getElementById('modalDate').value = '{{ $selectedDate }}';
            resetVisitorManualEntryForm();
        }

        function closeVisitorManualEntryModal() {
            document.getElementById('visitorManualEntryModal').classList.add('hidden');
            resetVisitorManualEntryForm();
        }

        function resetVisitorManualEntryForm() {
            document.getElementById('visitorManualEntryForm').reset();
            document.getElementById('existingVisitorSection').classList.add('hidden');
            document.getElementById('visitorName').classList.add('hidden');
            document.getElementById('otherIdSection').classList.add('hidden');
            document.getElementById('timeInField').classList.add('hidden');
            document.getElementById('timeOutField').classList.add('hidden');

            clearRequiredAttributes();
        }

        function clearRequiredAttributes() {
            const fields = ['visitorId', 'visitorName', 'validId', 'visitorPass', 'idNumber', 'relationship', 'purpose', 'timeIn', 'timeOut'];
            fields.forEach(fieldId => {
                const field = document.getElementById(fieldId);
                if (field) {
                    field.removeAttribute('required');
                }
            });
        }

        function initializeVisitorManualEntryModal() {
            document.getElementById('entryType').addEventListener('change', function() {
                const entryType = this.value;
                const existingSection = document.getElementById('existingVisitorSection');
                const visitorName = document.getElementById('visitorName');
                const timeInField = document.getElementById('timeInField');
                const timeOutField = document.getElementById('timeOutField');

                clearRequiredAttributes();

                existingSection.classList.add('hidden');
                visitorName.classList.add('hidden');
                timeInField.classList.add('hidden');
                timeOutField.classList.add('hidden');

                if (entryType) {
                    visitorName.classList.remove('hidden');
                    document.getElementById('visitorId').setAttribute('required', 'required');

                    if (entryType === 'time_in' || entryType === 'both') {
                        existingSection.classList.remove('hidden');
                        timeInField.classList.remove('hidden');
                        document.getElementById('timeIn').setAttribute('required', 'required');
                    }
                    if (entryType === 'time_out' || entryType === 'both') {
                        timeOutField.classList.remove('hidden');
                        document.getElementById('timeOut').setAttribute('required', 'required');
                    }
                }
            });

            document.getElementById('validId').addEventListener('change', function() {
                const otherSection = document.getElementById('otherIdSection');
                const otherIdType = document.getElementById('otherIdType');

                if (this.value === 'Other') {
                    otherSection.classList.remove('hidden');
                    otherIdType.setAttribute('required', 'required');
                } else {
                    otherSection.classList.add('hidden');
                    otherIdType.removeAttribute('required');
                }
            });
        }

        function submitVisitorManualEntry() {
            const submitBtn = document.getElementById('submitBtn');
            const originalHTML = submitBtn.innerHTML;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            const form = document.getElementById('visitorManualEntryForm');
            const formData = new FormData(form);

            fetch('{{ route('monitor.visitor.submit') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(async response => {
                if (!response.ok) {
                    const text = await response.text();
                    throw new Error(`HTTP ${response.status}: ${text}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeVisitorManualEntryModal();
                    location.reload();
                } else {
                    console.error('Server error:', data);
                    alert(data.message || 'Failed to submit visitor manual entry.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while submitting the manual entry.');
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            });
        }

        // Attach to form submit
        document.getElementById('visitorManualEntryForm').addEventListener('submit', function(e) {
            e.preventDefault();
            submitVisitorManualEntry();
        });
    </script>
</x-monitorLayout>
