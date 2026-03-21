<x-monitorLayout>
    <div class="p-6 mb-6 bg-white shadow-md rounded-xl">
        <h2 class="mb-4 text-xl font-bold text-orange-700">Advanced Schedules</h2>
        <div id="visitorCalendar"></div>
    </div>

    <!-- Modal -->
    <div id="eventModal" class="fixed inset-0 z-50 items-center justify-center hidden bg-black bg-opacity-50">
        <div class="w-full max-w-md p-6 bg-white shadow-lg rounded-xl">
            <form action="{{ route('monitor.calendar.set-schedule') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="event_id" id="eventId">
                <h3 id="modalTitle" class="mb-2 text-lg font-bold text-orange-700"></h3>
                <p id="modalStartDate" class="mb-4 text-sm text-gray-600"></p>
                <p id="modalEndDate" class="mb-4 text-sm text-gray-600"></p>
                <p id="modalDescription" class="mb-4 text-gray-800"></p>
                <div>
                    <label for="time_out" class="block mb-1 text-sm font-medium text-gray-700">Time Out</label>
                    <input type="time" name="time_out" id="time_out"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 focus:outline-none">
                </div>
                <div>
                    <label for="time_in" class="block mb-1 text-sm font-medium text-gray-700">Time In</label>
                    <input type="time" name="time_in" id="time_in"
                           class="w-full px-3 py-2 border rounded-lg focus:ring-2 focus:ring-orange-400 focus:outline-none">
                </div>
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" id="closeModal"
                        class="px-4 py-2 text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300">
                        Close
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-white bg-orange-500 rounded-lg hover:bg-orange-600">
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

    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

    <script>
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

        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('visitorCalendar');
            const modal = document.getElementById('eventModal');
            const closeModalBtn = document.getElementById('closeModal');

            const modalTitle = document.getElementById('modalTitle');
            const modalStartDate = document.getElementById('modalStartDate');
            const modalEndDate = document.getElementById('modalEndDate');
            const modalDescription = document.getElementById('modalDescription');
            const eventIdInput = document.getElementById('eventId');

            // Events from backend (already include time_in, time_out, color)
            const events = @json($events);

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                aspectRatio: 1.5,
                headerToolbar: {
                    left: 'prev',
                    center: 'title',
                    right: 'today next'
                },
                events: events,
                displayEventTime: false,
                dayMaxEvents: true,
                navLinks: true,
                selectable: true,

                eventClick: function(info) {
                    info.jsEvent.preventDefault();

                    modalTitle.textContent = info.event.title;
                    modalStartDate.textContent = "Start Date: " + info.event.start.toISOString().split('T')[0];
                    modalEndDate.textContent = "End Date: " + (info.event.end
                        ? info.event.end.toISOString().split('T')[0]
                        : info.event.start.toISOString().split('T')[0]);
                    modalDescription.textContent = info.event.extendedProps.description || 'No additional details.';

                    eventIdInput.value = info.event.id;

                    // ✅ prefill schedule times if already set
                    document.getElementById('time_in').value = info.event.extendedProps.time_in || '';
                    document.getElementById('time_out').value = info.event.extendedProps.time_out || '';

                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                }
            });

            calendar.render();

            closeModalBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }
            });
        });
    </script>
</x-monitorLayout>
