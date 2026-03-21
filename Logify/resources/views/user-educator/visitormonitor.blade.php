<x-educator-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="px-4 py-6">
        <div class="p-8 bg-white border border-gray-200 shadow-xl rounded-1xl">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <h1 class="flex items-center space-x-2 text-2xl font-bold text-orange-700">
                    <span>VISITOR MONITORING</span>
                </h1>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('educator.dashboard') }}"
                        class="inline-flex items-center text-sm font-medium text-blue-600 hover:underline">
                        <i data-feather="arrow-left" class="w-5 h-5 mr-1"></i> Back to Dashboard
                    </a>
                </div>
            </div>

            {{-- Search Bar (Same as Monitor Side) --}}
            <div class="mb-6">
                <form action="{{ route('visitor.monitor') }}" method="GET" class="flex items-center space-x-2">
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

            {{-- Success Message --}}
            @if (session('success'))
                <div
                    class="px-4 py-3 mb-6 text-sm text-green-800 bg-green-100 border border-green-300 rounded-md shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Table --}}
            <div class="relative w-full overflow-hidden border border-gray-200 rounded-lg">
                <div class="w-full overflow-x-auto table-container">
                    <div class="overflow-y-auto max-h-[calc(100vh-200px)] table-container">
                        <table class="w-full min-w-full text-sm text-left text-gray-700">
                    <thead class="text-xs font-semibold tracking-wider uppercase bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-black">Date of Visit</th>
                            <th class="px-3 py-6 text-black">Visitors Pass</th>
                            <th class="px-6 py-3 text-black">Visitors Name</th>
                            <th class="px-6 py-3 text-black">ID Type</th>
                            <th class="px-6 py-3 text-black">ID Number</th>
                            <th class="px-6 py-3 text-black">Relationship</th>
                            <th class="px-6 py-3 text-black">Purpose</th>
                            <th class="px-6 py-3 text-black">In Time</th>
                            <th class="px-4 py-3 text-black">Out Time</th>
                            <th class="px-4 py-3 text-black">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse($visitors as $visitor)
                            <tr class="hover:bg-gray-50"
                                data-visitor-name="{{ strtolower($visitor->visitor_name ?? '') }}"
                                data-id-number="{{ strtolower($visitor->id_number ?? '') }}"
                                data-purpose="{{ strtolower($visitor->purpose ?? '') }}">
                                <td class="px-6 py-4">{{ $visitor->formatted_date }}</td>
                                <td class="px-6 py-4">{{ $visitor->visitor_pass }}</td>
                                <td class="px-6 py-4">{{ $visitor->visitor_name }}</td>
                                <td class="px-6 py-4">{{ $visitor->valid_id }}</td>
                                <td class="px-6 py-4">{{ $visitor->id_number }}</td>
                                <td class="px-6 py-4">{{ $visitor->relationship }}</td>
                                <td class="px-6 py-4">{{ $visitor->purpose }}</td>
                                <td class="px-6 py-4">{{ $visitor->formatted_time_in }}</td>
                                <td class="px-6 py-4">
                                    @if ($visitor->time_out)
                                        {{ $visitor->formatted_time_out }}
                                    @else
                                        <span class="font-medium text-red-500">Not Yet Got Out</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if($visitor->is_manual_entry)
                                        @if($visitor->approval_status === 'pending')
                                            <span class="px-2 py-1 text-xs font-medium text-yellow-800 bg-yellow-100 rounded-full">
                                                Pending Approval
                                            </span>
                                        @elseif($visitor->approval_status === 'approved')
                                            <span class="px-2 py-1 text-xs font-medium text-green-800 bg-green-100 rounded-full">
                                                Approved
                                            </span>
                                        @elseif($visitor->approval_status === 'rejected')
                                            <span class="px-2 py-1 text-xs font-medium text-red-800 bg-red-100 rounded-full">
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
                                    <td colspan="10" class="px-6 py-8 text-center text-gray-400">
                                        No visitor get in/out records found for this date
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        </table>
                    </div>

                    <!-- Pagination Links -->
                    <div class="flex justify-end mt-6">
                        {{ $visitors->links() }}
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
        </style>

        {{-- Feather Icons + Real-time Clock --}}
        <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                feather.replace();

                // Search functionality
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

                        // Reset all rows to visible when search is empty
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

                    // Scroll to first match
                    if (firstMatch) {
                        firstMatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                }

                function clearSearchFunction() {
                    visitorSearch.value = '';
                    clearSearch.classList.add('hidden');
                    searchResults.classList.add('hidden');

                    // Reset all rows to visible
                    tableRows.forEach(row => {
                        row.style.display = '';
                    });
                }

                // Search event listeners
                visitorSearch.addEventListener('input', performSearch);
                clearSearch.addEventListener('click', clearSearchFunction);

                function updateDateTime() {
                    const now = new Date();
                    const options = {
                        weekday: 'long',
                        year: 'numeric',
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true
                    };
                    document.addEventListener('DOMContentLoaded', function() {
                        document.getElementById('datetime').textContent = now.toLocaleString('en-US', options);
                        const now = new Date(); // Assuming now and options are defined elsewhere
                        const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', hour: 'numeric', minute: 'numeric', second: 'numeric' };
                        const datetimeElement = document.getElementById('datetime');

                        // Check if the element exists before trying to set its textContent
                        if (datetimeElement) {
                            datetimeElement.textContent = now.toLocaleString('en-US', options);
                        } else {
                            console.error("Element with ID 'datetime' not found.");
                        }
                    });
                }

                updateDateTime();
                setInterval(updateDateTime, 1000);
            });
        </script>

    </x-educator-layout>
