<x-educator-layout>
    <div class="p-6 space-y-6">

        <div class="mb-6">
            <form action="{{ route('leisure.report') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="flex-1 lg:max-w-xs">
                    <label for="monthFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Month</label>
                    <input type="month" id="monthFilter" name="month" value="{{ request('month') }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-orange-500">
                </div>

                <div class="flex-1 lg:max-w-xs">
                    <label for="batchFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Batch</label>
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
                    <label for="groupFilter" class="block mb-1 text-sm font-medium text-gray-700">Filter by Group</label>
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
                        <thead class="text-xs font-semibold tracking-wider uppercase bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-black">Name</th>
                                <th class="px-6 py-3 text-black">Batch</th>
                                <th class="px-6 py-3 text-black">Group</th>
                                <th class="px-6 py-3 text-black">Late</th>
                                <th class="px-6 py-3 text-black">Early</th>
                                <th class="px-6 py-3 text-black">Absent</th>
                                <th class="px-6 py-3 text-black">Total</th>
                                <th class="px-6 py-3 text-black">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($reports as $report)
                                <tr onclick="openStudentModal(this)"
                                    data-log='@json($report->goingOut ? (is_array($report->goingOut) ? $report->goingOut : [$report->goingOut]) : [])'
                                    class="cursor-pointer hover:bg-orange-50 transition-colors">
                                    <td class="px-6 py-3">{{ $report->user->user_fname . ' ' . $report->user->user_lname ?? 'N/A' }}</td>
                                    <td class="px-6 py-3">{{ $report->batch ?? 'N/A' }}</td>
                                    <td class="px-6 py-3">{{ $report->group ?? 'N/A' }}</td>
                                    <td class="px-6 py-3 text-blue-600 font-semibold">{{ $report->total_late ?? 0 }}</td>
                                    <td class="px-6 py-3 text-yellow-600 font-semibold">{{ $report->total_early ?? 0 }}</td>
                                    <td class="px-6 py-3 text-red-600 font-semibold">{{ $report->total_absent ?? 0 }}</td>
                                    <td class="px-6 py-3 text-red-600 font-semibold">{{ $report->total_violations ?? 0 }}</td>
                                    <td class="px-6 py-3">
                                        @php
                                            $status = 'Good';
                                            if ($report->total_violations > 0 && $report->total_violations <= 3) {
                                                $status = 'Warning';
                                            } elseif ($report->total_violations > 3) {
                                                $status = 'Needs Attention';
                                            }
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full
                                            {{ $status === 'Good'
                                                ? 'bg-green-100 text-green-700'
                                                : ($status === 'Warning'
                                                    ? 'bg-red-100 text-red-700'
                                                    : 'bg-yellow-100 text-yellow-700') }}">
                                            {{ $status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                        No records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                {{ $reports->appends(request()->query())->links() }}
            </div>
        </div>

        <div id="studentModal" class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 hidden z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-3xl p-6 relative">
                <button onclick="closeStudentModal()" class="absolute top-2 right-2 text-gray-500 hover:text-gray-700 text-xl">&times;</button>
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Student Report Details</h2>

                <div id="studentInfo" class="mb-4 text-sm text-gray-700"></div>

                <table class="w-full min-w-full text-sm text-left text-gray-700 border border-gray-200">
                    <thead class="text-xs font-semibold tracking-wider uppercase bg-gray-100">
                        <tr>
                            <th class="px-6 py-3 text-black">Date</th>
                            <th class="px-6 py-3 text-black">Out Time</th>
                            <th class="px-6 py-3 text-black">Out Remark</th>
                            <th class="px-6 py-3 text-black">In Time</th>
                            <th class="px-6 py-3 text-black">In Remark</th>
                        </tr>
                    </thead>
                    <tbody id="studentModalTableBody" class="divide-y divide-gray-200">
                        <tr>
                            <td colspan="5" class="px-6 py-4 text-center text-gray-500">Select a student to view details.</td>
                        </tr>
                    </tbody>
                </table>

                <div class="mt-6 text-right">
                    <button onclick="closeStudentModal()" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    function openStudentModal(row) {
        const modal = document.getElementById('studentModal');
        const modalBody = document.getElementById('studentModalTableBody');
        const studentInfo = document.getElementById('studentInfo');

        let parsed = JSON.parse(row.getAttribute('data-log') || '[]');
        const logs = Array.isArray(parsed[0]) ? parsed[0] : Array.isArray(parsed) ? parsed : [parsed];

        modal.classList.remove('hidden');

        const name = row.querySelector('td:nth-child(1)').textContent.trim();
        const batch = row.querySelector('td:nth-child(2)').textContent.trim();
        const group = row.querySelector('td:nth-child(3)').textContent.trim();

        studentInfo.innerHTML = `
            <p><strong>Name:</strong> ${name}</p>
            <p><strong>Batch:</strong> ${batch}</p>
            <p><strong>Group:</strong> ${group}</p>
        `;

        if (!logs.length) {
            modalBody.innerHTML = `
                <tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No records found.</td></tr>
            `;
            return;
        }

        const formatTime = (time) => {
            if (!time) return 'N/A';
            const date = new Date(`1970-01-01T${time}`);
            return isNaN(date) ? time : date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        };

        modalBody.innerHTML = logs.map(log => `
            <tr>
                <td class="px-6 py-3">${log.going_out_date ?? 'N/A'}</td>
                <td class="px-6 py-3">${formatTime(log.time_out)}</td>
                <td class="px-6 py-3">${log.time_out_remark ?? 'N/A'}</td>
                <td class="px-6 py-3">${formatTime(log.time_in)}</td>
                <td class="px-6 py-3">${log.time_in_remark ?? 'N/A'}</td>
            </tr>
        `).join('');
    }

    function closeStudentModal() {
        document.getElementById('studentModal').classList.add('hidden');
    }
    </script>
</x-educator-layout>
