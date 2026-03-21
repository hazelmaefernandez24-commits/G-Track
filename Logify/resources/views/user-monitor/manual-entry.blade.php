<x-monitorLogLayout>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Manual Entry Modal --}}
    <div id="manualEntryModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="relative w-11/12 max-w-4xl p-6 bg-white border shadow-2xl rounded-xl">

            <!-- Header -->
            <div class="flex items-center justify-between pb-3 border-b">
                <h3 class="text-xl font-semibold text-gray-800" id="modalTitle">Manual Entry</h3>
                <button type="button" onclick="navigateBack()"
                    class="p-1 text-gray-400 rounded hover:bg-gray-100 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="manualEntryForm" class="mt-4 space-y-5">
                <!-- Error -->
                <div id="formError" class="hidden p-3 text-sm text-center text-red-700 border border-red-200 rounded-md bg-red-50"></div>

                <!-- Student ID -->
                <div>
                    <label for="modalStudentId" class="block mb-1 text-sm font-medium text-gray-700">
                        Student ID <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="modalStudentId" name="student_id" required
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="Enter Student ID (e.g., 2022-00001-TG-0)">
                </div>

                <!-- Session -->
                <div id="sessionSelection" class="hidden">
                    <label for="sessionNumber" class="block mb-1 text-sm font-medium text-gray-700">
                        Session <span class="text-red-500">*</span>
                    </label>
                    <select id="sessionNumber" name="session_number"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">Select Session</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Choose which session to edit, or select "New Session".</p>
                </div>

                <!-- Date -->
                <div>
                    <label for="modalDate" class="block mb-1 text-sm font-medium text-gray-700">Date</label>
                    <input type="date" id="modalDate" name="date" value="{{ $selectedDate }}"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    <input type="hidden" id="modalLogType" name="log_type">
                </div>

                <!-- Entry Type -->
                <div>
                    <label for="entryType" class="block mb-1 text-sm font-medium text-gray-700">Entry Type</label>
                    <select id="entryType" name="entry_type" required
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                        <option value="">Select entry type...</option>
                        <option value="time_out">Time Out Only</option>
                        <option value="time_in">Time In Only</option>
                        <option value="both">Both Time Out & Time In</option>
                    </select>
                </div>

                <!-- Time Fields -->
                <div id="timeFields" class="hidden space-y-4">
                    <div id="timeOutField" class="hidden">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Time Out</label>
                        <input type="time" id="timeOut" name="time_out"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    </div>

                    <div id="timeInField" class="hidden">
                        <label class="block mb-1 text-sm font-medium text-gray-700">Time In</label>
                        <input type="time" id="timeIn" name="time_in"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <!-- Going Out -->
                <div id="goingOutFields" class="hidden space-y-4">
                    <div>
                        <label for="destination" class="block mb-1 text-sm font-medium text-gray-700">Destination</label>
                        <input type="text" id="destination" name="destination" maxlength="255"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    </div>
                    <div>
                        <label for="purpose" class="block mb-1 text-sm font-medium text-gray-700">Purpose</label>
                        <input type="text" id="purpose" name="purpose" maxlength="255"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500">
                    </div>
                </div>

                <!-- Reason -->
                <div>
                    <label for="reason" class="block mb-1 text-sm font-medium text-gray-700">
                        Reason for Manual Entry <span class="text-red-500">*</span>
                    </label>
                    <textarea id="reason" name="reason" required maxlength="500" rows="4"
                        class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500"
                        placeholder="Explain why manual entry is needed..."></textarea>
                    <p class="mt-1 text-xs text-gray-500">Maximum 500 characters</p>
                </div>

                <input type="hidden" name="type" value="{{$type}}">

                <!-- Footer Buttons -->
                <div class="flex justify-end pt-5 space-x-3 border-t">
                    <button type="button" onclick="navigateBack()"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200">
                        Cancel
                    </button>
                    <button type="submit" id="submitBtn"
                        class="px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700">
                        Submit for Approval
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentLogs = [];
        let currentType = '{{ $type }}';
        let currentDate = '{{ $selectedDate }}';
        const backUrl = '{{ $type === 'going_out' ? route('monitor.goingout.logs', ['date' => $selectedDate]) : route('monitor.academic.logs', ['date' => $selectedDate]) }}';

        document.addEventListener('DOMContentLoaded', function() {
            // Initialize feather icons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Auto-open the Manual Entry modal for faster workflow
            // If navigated with a student_id param, preselect and prefill
            const urlParams = new URLSearchParams(window.location.search);
            const preselectStudentId = urlParams.get('student_id') || '';
            const preselectSessionNumber = urlParams.get('session_number') || '';
            openManualEntryModal(preselectStudentId, '');
            if (preselectStudentId) {
                loadStudentData(preselectStudentId, preselectSessionNumber);
            }

            // Event listeners
            // Keep button available if ever unhidden
            const loadLogsBtn = document.getElementById('loadLogsBtn');
            if (loadLogsBtn) loadLogsBtn.addEventListener('click', loadStudentLogs);

            const typeFilterEl = document.getElementById('typeFilter');
            if (typeFilterEl) {
                typeFilterEl.addEventListener('change', function() {
                    currentType = this.value;
                    const modal = document.getElementById('manualEntryModal');
                    if (!modal.classList.contains('hidden')) {
                        const modalLogType = document.getElementById('modalLogType');
                        if (modalLogType) modalLogType.value = currentType;
                        const goingOutFields = document.getElementById('goingOutFields');
                        const sessionSelection = document.getElementById('sessionSelection');
                        if (currentType === 'going_out') {
                            if (goingOutFields) goingOutFields.classList.remove('hidden');
                            if (sessionSelection) sessionSelection.classList.remove('hidden');
                        } else {
                            if (goingOutFields) goingOutFields.classList.add('hidden');
                            if (sessionSelection) sessionSelection.classList.add('hidden');
                        }
                    }
                });
            }

            const modalStudentIdEl = document.getElementById('modalStudentId');
            if (modalStudentIdEl) {
                modalStudentIdEl.addEventListener('input', function() {
                    const studentId = this.value.trim();
                    if (studentId.length >= 5) loadStudentData(studentId);
                });
            }

            const sessionNumberEl = document.getElementById('sessionNumber');
            if (sessionNumberEl) {
                sessionNumberEl.addEventListener('change', function() {
                    const studentId = document.getElementById('modalStudentId')?.value.trim();
                    if (studentId && this.value) loadStudentData(studentId, this.value);
                });
            }

            const dateFilterEl = document.getElementById('dateFilter');
            if (dateFilterEl) {
                dateFilterEl.addEventListener('change', function() {
                    currentDate = this.value;
                    const modal = document.getElementById('manualEntryModal');
                    if (!modal.classList.contains('hidden')) {
                        const modalDateEl = document.getElementById('modalDate');
                        if (modalDateEl) modalDateEl.value = currentDate;
                    }
                });
            }

            const entryTypeEl = document.getElementById('entryType');
            if (entryTypeEl) entryTypeEl.addEventListener('change', handleEntryTypeChange);

            const manualEntryFormEl = document.getElementById('manualEntryForm');
            if (manualEntryFormEl) manualEntryFormEl.addEventListener('submit', submitManualEntry);
        });

        function openManualEntryModal(studentId, studentName) {
            // Reset form first to avoid clearing values we set below
            document.getElementById('manualEntryForm').reset();

            // Set core values
            document.getElementById('modalStudentId').value = studentId || '';
            document.getElementById('modalLogType').value = currentType;
            document.getElementById('modalDate').value = currentDate;

            // Title handling
            const title = studentName ? `Manual Entry - ${studentName}` : 'Manual Entry';
            document.getElementById('modalTitle').textContent = title;

            // Show/hide going out fields and session selection based on type
            const goingOutFields = document.getElementById('goingOutFields');
            const sessionSelection = document.getElementById('sessionSelection');
            if (currentType === 'going_out') {
                goingOutFields.classList.remove('hidden');
                sessionSelection.classList.remove('hidden');
            } else {
                goingOutFields.classList.add('hidden');
                sessionSelection.classList.add('hidden');
            }

            // Ensure time fields are hidden until entry type is chosen (will be toggled if we prefill)
            document.getElementById('timeFields').classList.add('hidden');
            document.getElementById('timeOutField').classList.add('hidden');
            document.getElementById('timeInField').classList.add('hidden');

            // Load student data if studentId is provided
            if (studentId) {
                loadStudentData(studentId);
            }

            // Finally open the modal
            document.getElementById('manualEntryModal').classList.remove('hidden');
        }

        // Helper to open the modal without a pre-selected student
        function openManualEntryBlank() {
            openManualEntryModal('', '');
        }

        function closeManualEntryModal() {
            document.getElementById('manualEntryModal').classList.add('hidden');
        }

        function navigateBack() {
        if (window.history.length > 1) {
                window.history.back();
            } else {
                window.location.href = "/";
            }
        }

        function handleEntryTypeChange() {
            const entryType = document.getElementById('entryType').value;
            const timeFields = document.getElementById('timeFields');
            const timeOutField = document.getElementById('timeOutField');
            const timeInField = document.getElementById('timeInField');

            if (entryType) {
                timeFields.classList.remove('hidden');

                if (entryType === 'time_out' || entryType === 'both') {
                    timeOutField.classList.remove('hidden');
                    document.getElementById('timeOut').required = true;
                } else {
                    timeOutField.classList.add('hidden');
                    document.getElementById('timeOut').required = false;
                }

                if (entryType === 'time_in' || entryType === 'both') {
                    timeInField.classList.remove('hidden');
                    document.getElementById('timeIn').required = true;
                } else {
                    timeInField.classList.add('hidden');
                    document.getElementById('timeIn').required = false;
                }
            } else {
                timeFields.classList.add('hidden');
                timeOutField.classList.add('hidden');
                timeInField.classList.add('hidden');
            }
        }

        // Load student data and populate session dropdown
        async function loadStudentData(studentId, selectedSession = '') {
            try {
                if (!studentId) return;

                const params = new URLSearchParams({
                    student_id: studentId,
                    log_type: currentType,
                    date: currentDate
                });

                const resp = await fetch(`/monitor/manual-entry/find-existing?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!resp.ok) return;
                const data = await resp.json();

                if (data.success && currentType === 'going_out') {
                    // Populate session dropdown
                    const sessionSelect = document.getElementById('sessionNumber');
                    sessionSelect.innerHTML = '<option value="">Select Session</option>';

                    if (data.sessions && data.sessions.length > 0) {
                        data.sessions.forEach(session => {
                            const option = document.createElement('option');
                            option.value = session.session_number;
                            option.textContent = `Session ${session.session_number}`;
                            if (session.time_out) option.textContent += ` (Out: ${session.time_out.slice(0,5)})`;
                            if (session.time_in) option.textContent += ` (In: ${session.time_in.slice(0,5)})`;
                            sessionSelect.appendChild(option);
                        });
                    }

                    // Add option for new session
                    const newSessionOption = document.createElement('option');
                    const nextSessionNumber = data.sessions && data.sessions.length > 0
                        ? Math.max(...data.sessions.map(s => s.session_number)) + 1
                        : 1;
                    newSessionOption.value = nextSessionNumber;
                    newSessionOption.textContent = `New Session ${nextSessionNumber}`;
                    sessionSelect.appendChild(newSessionOption);

                    // Select the specified session or the latest one
                    if (selectedSession) {
                        sessionSelect.value = selectedSession;
                    } else if (data.sessions && data.sessions.length > 0) {
                        sessionSelect.value = data.sessions[0].session_number;
                    } else {
                        sessionSelect.value = nextSessionNumber;
                    }

                    // Load data for the selected session
                    prefillExistingIfAny(studentId, sessionSelect.value);
                } else {
                    // For academic logs, just prefill existing data
                    prefillExistingIfAny(studentId);
                }
            } catch (e) {
                console.warn('Load student data failed', e);
            }
        }

        // Prefill helper: check if a record exists for student/date/type and populate fields accordingly
        async function prefillExistingIfAny(studentId, sessionNumber = '') {
            try {
                if (!studentId) return;
                const params = new URLSearchParams({
                    student_id: studentId,
                    log_type: currentType,
                    date: currentDate,
                    session_number: sessionNumber || ''
                });
                const resp = await fetch(`/monitor/manual-entry/find-existing?${params.toString()}`, {
                    headers: { 'Accept': 'application/json' }
                });
                if (!resp.ok) return; // silently ignore
                const data = await resp.json();
                if (!data.success || !data.exists) return;

                const { time_out, time_in, destination, purpose, session_number } = data.data || {};

                const entryTypeEl = document.getElementById('entryType');
                const timeFields = document.getElementById('timeFields');
                const timeOutField = document.getElementById('timeOutField');
                const timeInField = document.getElementById('timeInField');

                let entryType = '';
                if (time_out && time_in) entryType = 'both';
                else if (time_out) entryType = 'time_out';
                else if (time_in) entryType = 'time_in';

                if (entryType) {
                    entryTypeEl.value = entryType;
                    // reveal fields according to entry type
                    timeFields.classList.remove('hidden');
                    if (entryType === 'time_out' || entryType === 'both') {
                        timeOutField.classList.remove('hidden');
                        if (time_out) document.getElementById('timeOut').value = String(time_out).slice(0,5);
                        document.getElementById('timeOut').required = true;
                    }
                    if (entryType === 'time_in' || entryType === 'both') {
                        timeInField.classList.remove('hidden');
                        if (time_in) document.getElementById('timeIn').value = String(time_in).slice(0,5);
                        document.getElementById('timeIn').required = true;
                    }
                }

                if (currentType === 'going_out') {
                    if (destination) document.getElementById('destination').value = destination;
                    if (purpose) document.getElementById('purpose').value = purpose;
                }
            } catch (e) {
                console.warn('Prefill lookup failed', e);
            }
        }

        function submitManualEntry(event) {
            event.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const originalText = submitBtn.textContent;

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            const formData = new FormData(event.target);

            // clear previous error
            const errorBox = document.getElementById('formError');
            errorBox.classList.add('hidden');
            errorBox.textContent = '';

            fetch('/monitor/manual-entry/submit', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    // Ensure Laravel treats this as an AJAX request and returns JSON (e.g., 422 for validation)
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(async (response) => {
                // Try to parse JSON regardless of status to extract validation errors/messages
                const data = await response.json().catch(() => null);
                if (!response.ok) {
                    const message = (data && (data.error || data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : ''))) || `HTTP error! Status: ${response.status}`;
                    // show inline error
                    errorBox.textContent = message;
                    errorBox.classList.remove('hidden');
                    throw new Error(message);
                }
                return data;
            })
            .then(data => {
                if (data.success) {
                    // Optional inline success could be shown; keep alert for now
                    alert('Manual entry submitted successfully and is pending educator approval.');
                    // Redirect back to the corresponding monitor page (Academic or Going Out)
                    window.location.href = backUrl;
                } else {
                    errorBox.textContent = data.message || 'Failed to submit manual entry.';
                    errorBox.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error submitting manual entry:', error);
                // error already shown inline when possible
            })
            .finally(() => {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            });
        }
    </script>
</x-monitorLogLayout>
