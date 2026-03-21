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

        /* Grace period input styling */
        .grace-period-input:not(:disabled) {
            background: linear-gradient(135deg, #fff 0%, #fef3e2 100%);
            border-color: #f97316;
        }

        .grace-period-input:not(:disabled):focus {
            border-color: #ea580c;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
        }

        .grace-period-input:disabled {
            background: #f9fafb;
            color: #6b7280;
            cursor: not-allowed;
            border-color: #d1d5db;
        }

        /* Grace period saved state */
        .grace-period-input:disabled.saved {
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            border-color: #22c55e;
        }

        /* Button hover effects */
        #editGracePeriodBtn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none !important;
        }

        #editGracePeriodBtn:disabled:hover {
            background: white !important;
            border-color: #fed7aa !important;
            color: #ea580c !important;
            transform: none !important;
            box-shadow: none !important;
        }
    </style>
    <div class="min-h-screen p-6">
        {{-- Expanded form container with better spacing --}}
        <div class="relative p-12 mx-auto space-y-10 bg-white border border-gray-100 shadow-lg max-w-8xl rounded-xl">

            {{-- Enhanced back button with proper context --}}
            <a href="{{ route('monitor.schedule.choose', ['type' => $data['type']]) }}"
                class="absolute flex items-center gap-2 px-4 py-2 font-semibold text-blue-600 transition-all duration-200 rounded-lg shadow-sm bg-blue-50 top-6 left-6 hover:text-blue-700 hover:bg-blue-100">
                <i data-feather="arrow-left" class="w-4 h-4"></i>
                <span>Back</span>
            </a>

            {{-- Enhanced title section --}}
            <div class="py-8 text-center">
                <h1 class="mb-4 text-4xl font-bold text-gray-800">
                    @if ($data['type'] === 'GoingOut')
                        Set Leisure Schedule
                    @elseif ($data['type'] === 'Academic')
                        Set Academic Regular Schedule
                    @elseif ($data['type'] === 'Irregular')
                        Set Academic Irregular Schedule
                    @endif
                </h1>
                <div class="mb-2 text-xl text-gray-600">
                    @if ($data['type'] === 'GoingOut')
                        for <span class="px-3 py-1 font-semibold text-orange-600 rounded-full bg-orange-50">{{ $data['gender'] == 'M' ? 'Male Students' : 'Female Students' }}</span>
                    @elseif ($data['type'] === 'Academic')
                        for <span class="px-3 py-1 font-semibold text-orange-600 rounded-full bg-orange-50">Class {{ $data['batch'] }} {{ $data['group'] }}</span>
                    @elseif ($data['type'] === 'Irregular')
                        @php
                            $student = App\Models\StudentDetail::where('student_id', $data['student_id'])->first();
                        @endphp
                        for <span class="px-3 py-1 font-semibold text-orange-600 rounded-full bg-orange-50">{{ $student->user->user_fname }} {{ $student->user->user_lname }}</span>
                    @endif
                </div>
                <div class="w-24 h-1 mx-auto rounded-full bg-gradient-to-r from-orange-400 to-orange-600"></div>
            </div>
            {{-- {{ dd($data) }} --}}


            @php
                $days = $data['type'] === 'GoingOut'
                    ? ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']
                    : ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            @endphp

            {{-- {{ dd($data) }} --}}
            @if (request()->has('batch') || request()->has('group') || request()->has('gender') || request()->has('student_id'))
                {{-- Enhanced form with better styling --}}
                <form method="POST" action="{{ route('monitor.schedule.store') }}" id="scheduleForm" class="space-y-8">
                    @csrf

                    {{-- Form configuration section --}}
                    <div class="p-6 border border-gray-200 bg-gray-50 rounded-xl">
                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        @if (request()->has('batch') && request()->has('group'))
                            <input type="hidden" name="batch" value="{{ request('batch') }}">
                            <input type="hidden" name="group" value="{{ request('group') }}">
                        @elseif (request()->has('gender'))
                            <input type="hidden" name="gender" value="{{ request('gender') }}">
                        @elseif (request()->has('student_id'))
                            <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                        @endif
                            <input type="hidden" name="type" value="{{ request('type') }}">

                            {{-- Schedule validity section --}}
                            <div class="space-y-2 {{ $data['type'] === 'GoingOut' ? 'hidden' : '' }}">
                                <label for="semester_id" class="block mb-2 text-sm font-semibold text-gray-700">Semester</label>
                                <select name="semester_id" id="semester_id" onchange="handleSemesterChange()"
                                    class="w-full p-3 transition-all duration-200 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                                    <option value="">Choose a Semester</option>
                                    <option value="1" {{ (request('semester_id') ?? old('semester_id', optional($currentSchedule->first())->semester_id ?? $batchSchedule->semester_id ?? '')) == '1' ? 'selected' : '' }}>
                                        First Semester
                                    </option>
                                    <option value="2" {{ (request('semester_id') ?? old('semester_id', optional($currentSchedule->first())->semester_id ?? $batchSchedule->semester_id ?? '')) == '2' ? 'selected' : '' }}>
                                        Second Semester
                                    </option>
                                    <option value="3" {{ (request('semester_id') ?? old('semester_id', optional($currentSchedule->first())->semester_id ?? $batchSchedule->semester_id ?? '')) == '3' ? 'selected' : '' }}>
                                        Summer
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label for="start_date" class="block mb-2 text-sm font-semibold text-gray-700">Start Date</label>
                                <input type="date"
                                    name="start_date"
                                    value="{{ old('start_date', optional($currentSchedule->first())->start_date ? \Carbon\Carbon::parse($currentSchedule->first()->start_date)->format('Y-m-d') : '') }}"
                                    class="w-full p-3 transition-all duration-200 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror

                                <label for="end_date" class="block mb-2 text-sm font-semibold text-gray-700">End Date</label>
                                <input type="date"
                                    name="end_date"
                                    value="{{ old('end_date', optional($currentSchedule->first())->end_date ? \Carbon\Carbon::parse($currentSchedule->first()->end_date)->format('Y-m-d') : '') }}"
                                    class="w-full p-3 transition-all duration-200 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Grace Period section (Academic and Irregular schedules only) --}}
                            @if ($data['type'] === 'Academic' || $data['type'] === 'Irregular')
                                <div class="col-span-1 space-y-4 lg:col-span-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="flex items-center gap-2 text-lg font-semibold text-gray-800">
                                            <i data-feather="clock" class="w-5 h-5 text-orange-500"></i>
                                            Grace Period Setting
                                        </h4>
                                        <div class="flex items-center gap-2">
                                            <button type="button" id="editGracePeriodBtn" onclick="toggleGracePeriodEdit()"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-orange-600 transition-all duration-200 transform bg-white border-2 border-orange-200 rounded-lg shadow-md hover:text-orange-700 hover:bg-orange-50 hover:border-orange-300 hover:shadow-lg hover:scale-105">
                                                <i data-feather="edit-2" class="w-4 h-4"></i>
                                                <span class="font-medium">Set Grace Period</span>
                                            </button>
                                            <button type="button" id="saveGracePeriodBtn" onclick="saveGracePeriod()" style="display: none;"
                                                class="flex items-center gap-2 px-4 py-2 text-sm text-green-500 transition-all duration-200 transform bg-white border-2 border-gray-200 rounded-lg shadow-md hover:text-green-600 hover:bg-orange-50 hover:border-orange-300 hover:shadow-lg hover:scale-105">
                                                <i data-feather="save" class="w-4 h-4"></i>
                                                <span class="font-medium">Save Changes</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                        {{-- Log Out Grace Period --}}
                                        <div class="relative space-y-2">
                                            <label for="grace_period_logout_minutes" class="block mb-2 text-sm font-semibold text-gray-700">
                                                <i data-feather="log-out" class="inline w-4 h-4 mr-1 text-red-500"></i>
                                                Get Out Grace Period (Minutes)
                                            </label>
                                            <div class="relative w-1/2">
                                                <input type="number" name="grace_period_logout_minutes" id="grace_period_logout_minutes"
                                                    value="{{ old('grace_period_logout_minutes', $currentSchedule && $currentSchedule->isNotEmpty() ? $currentSchedule->first()->grace_period_logout_minutes : (isset($batchSchedule) && $batchSchedule ? $batchSchedule->grace_period_logout_minutes : '')) }}"
                                                    min="0" max="60" step="1" placeholder="0"
                                                    class="w-full p-3 pr-10 transition-all duration-200 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 grace-period-input"
                                                    {{ $currentSchedule && $currentSchedule->isNotEmpty() ? 'disabled' : '' }}>
                                                <div id="logoutCheckIcon" class="absolute hidden transform -translate-y-1/2 right-2 top-1/2">
                                                    <i data-feather="check-circle" class="w-4 h-4 text-green-500"></i>
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                <i data-feather="info" class="inline w-3 h-3 mr-1"></i>
                                                Time buffer for log out timing (leave empty for no grace period)
                                                @if (!($currentSchedule && $currentSchedule->isNotEmpty()) && isset($batchSchedule) && $batchSchedule && $batchSchedule->grace_period_logout_minutes)
                                                    <br><span class="font-medium text-orange-600">
                                                        <i data-feather="arrow-down" class="inline w-3 h-3 mr-1"></i>
                                                        Inherited from batch {{ $data['batch'] }} {{ $data['group'] }} schedule
                                                    </span>
                                                @endif
                                            </p>
                                        </div>

                                        {{-- Log In Grace Period --}}
                                        <div class="relative space-y-2">
                                            <label for="grace_period_login_minutes" class="block mb-2 text-sm font-semibold text-gray-700">
                                                <i data-feather="log-in" class="inline w-4 h-4 mr-1 text-green-500"></i>
                                                Get In Grace Period (Minutes)
                                            </label>
                                            <div class="relative w-1/2">
                                                <input type="number" name="grace_period_login_minutes" id="grace_period_login_minutes"
                                                    value="{{ old('grace_period_login_minutes', $currentSchedule && $currentSchedule->isNotEmpty() ? $currentSchedule->first()->grace_period_login_minutes : (isset($batchSchedule) && $batchSchedule ? $batchSchedule->grace_period_login_minutes : '')) }}"
                                                    min="0" max="60" step="1" placeholder="0"
                                                    class="w-full p-3 pr-10 transition-all duration-200 bg-white border-2 border-gray-300 rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 grace-period-input"
                                                    {{ $currentSchedule && $currentSchedule->isNotEmpty() ? 'disabled' : '' }}>
                                                <div id="loginCheckIcon" class="absolute hidden transform -translate-y-1/2 right-2 top-1/2">
                                                    <i data-feather="check-circle" class="w-4 h-4"></i>
                                                </div>
                                            </div>
                                            <p class="mt-1 text-xs text-gray-500">
                                                <i data-feather="info" class="inline w-3 h-3 mr-1"></i>
                                                Time buffer for log in timing (leave empty for no grace period)
                                                @if (!($currentSchedule && $currentSchedule->isNotEmpty()) && isset($batchSchedule) && $batchSchedule && $batchSchedule->grace_period_login_minutes)
                                                    <br><span class="font-medium text-orange-600">
                                                        <i data-feather="arrow-down" class="inline w-3 h-3 mr-1"></i>
                                                        Inherited from batch {{ $data['batch'] }} {{ $data['group'] }} schedule
                                                    </span>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    {{-- Grace Period Inheritance Info --}}
                                    @if (!($currentSchedule && $currentSchedule->isNotEmpty()) && isset($batchSchedule) && $batchSchedule)
                                        <div class="p-4 mt-4 border border-blue-200 rounded-lg bg-blue-50">
                                            <div class="flex items-start gap-3">
                                                <i data-feather="info" class="w-5 h-5 text-blue-500 mt-0.5"></i>
                                                <div>
                                                    <h5 class="mb-1 text-sm font-semibold text-blue-800">Grace Period Inheritance</h5>
                                                    <p class="text-xs text-blue-700">
                                                        Grace periods are automatically inherited from the batch {{ $data['batch'] }} {{ $data['group'] }} schedule.
                                                        You can modify these values or leave them empty for no grace period.
                                                    </p>
                                                    @if ($batchSchedule->grace_period_logout_minutes || $batchSchedule->grace_period_login_minutes)
                                                        <div class="mt-2 text-xs text-blue-600">
                                                            <strong>Current batch grace periods:</strong>
                                                            @if ($batchSchedule->grace_period_logout_minutes)
                                                                Get Out: {{ $batchSchedule->grace_period_logout_minutes }} minutes
                                                            @endif
                                                            @if ($batchSchedule->grace_period_logout_minutes && $batchSchedule->grace_period_login_minutes)
                                                                •
                                                            @endif
                                                            @if ($batchSchedule->grace_period_login_minutes)
                                                                Get In: {{ $batchSchedule->grace_period_login_minutes }} minutes
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Grace Period Status Display --}}
                                    <div id="gracePeriodStatus" class="hidden p-3 mt-3 rounded-lg">
                                        <p class="text-sm font-medium"></p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Schedule management section --}}
                    <div class="p-6 space-y-6 bg-white border border-gray-200 rounded-xl">
                        <div class="flex items-center justify-between">
                            <h3 class="flex items-center gap-2 text-xl font-semibold text-gray-800">
                                <i data-feather="clock" class="w-5 h-5 text-orange-500"></i>
                                @if ($data['type'] === 'GoingOut')
                                    Leisure Schedules
                                @elseif ($data['type'] === 'Irregular')
                                    Academic Irregular Student's Schedules
                                @elseif ($data['type'] === 'Academic')
                                    Academic Regular Student's Schedules
                                @endif
                            </h3>

                            <button type="button" onclick="openQuickSetup()"
                                class="flex items-center gap-2 px-4 py-2 text-sm text-white transition-all duration-200 transform rounded-lg shadow-md bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 hover:shadow-lg hover:scale-105">
                                <i data-feather="zap" class="w-4 h-4"></i>
                                <span class="font-medium">Quick Setup</span>
                            </button>
                        </div>

                        <!-- Quick Setup Modal -->
                        <div id="quickSetupModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                            <div class="flex items-end justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                                <div class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" aria-hidden="true"></div>
                                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                <div class="inline-block px-4 pt-5 pb-4 overflow-hidden text-left align-bottom transition-all transform bg-white rounded-lg shadow-xl sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                                    <div class="absolute top-0 right-0 pt-4 pr-4">
                                        <button type="button" onclick="closeQuickSetup()" class="text-gray-400 hover:text-gray-500">
                                            <i data-feather="x" class="w-6 h-6"></i>
                                        </button>
                                    </div>
                                    <div class="mt-3 text-center sm:mt-0 sm:text-left">
                                        <h3 class="text-lg font-medium leading-6 text-gray-900" id="modal-title">Quick Schedule Setup</h3>
                                        <div class="mt-4">
                                            <div class="space-y-4">
                                                <div>
                                                    <div class="flex items-center justify-between mb-2">
                                                        <div class="block text-sm font-medium text-gray-700">Select Days</div>
                                                        <div class="inline-flex items-center">
                                                            <input type="checkbox" id="selectAllDays" onchange="toggleAllDays()" class="w-4 h-4 text-orange-600 border-gray-300 rounded focus:ring-orange-500">
                                                            <label for="selectAllDays" class="ml-2 text-sm font-medium text-orange-600">Select All</label>
                                                        </div>
                                                    </div>
                                                    <div class="mt-2 space-y-2">
                                                        @foreach ($days as $day)
                                                            <div class="inline-flex items-center mr-4">
                                                                <input type="checkbox" id="{{ $day }}" name="quick_days[]" value="{{ strtolower($day) }}" class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 day-checkbox" onchange="updateSelectAllState()">
                                                                <label for="{{ $day }}" class="ml-2 text-sm text-gray-700">{{ $day }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                                <div>
                                                    <label for="quick_time_out" class="block text-sm font-medium text-gray-700">Log Out Time</label>
                                                    <input type="time" id="quick_time_out" class="w-full p-2 mt-1 bg-gray-100 border-2 border-gray-300 rounded focus:outline-none focus:border-orange-500">
                                                </div>
                                                <div>
                                                    <label for="quick_time_in" class="block text-sm font-medium text-gray-700">Log In Time</label>
                                                    <input type="time" id="quick_time_in" class="w-full p-2 mt-1 bg-gray-100 border-2 border-gray-300 rounded focus:outline-none focus:border-orange-500">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                        <button type="button" onclick="applyQuickSetup()" class="inline-flex justify-center w-full px-4 py-2 text-base font-medium text-white bg-blue-600 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                            Apply Schedule
                                        </button>
                                        <button type="button" onclick="closeQuickSetup()" class="inline-flex justify-center w-full px-4 py-2 mt-3 text-base font-medium text-gray-700 bg-white border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:w-auto sm:text-sm">
                                            Cancel
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Current schedule display --}}
                        @if ($currentSchedule && $currentSchedule->isNotEmpty())
                            <div class="p-8 mb-8 border-2 border-blue-200 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100">
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="p-2 bg-blue-500 rounded-lg">
                                            <i data-feather="calendar-check" class="w-5 h-5 text-white"></i>
                                        </div>
                                        <div>
                                            <h4 class="text-lg font-semibold text-blue-800">Current Active Schedule</h4>
                                            <p class="text-sm text-blue-600">Click edit to modify existing schedule</p>
                                        </div>
                                    </div>
                                    <button type="button" onclick="toggleEditAll()"
                                        class="flex items-center gap-2 px-6 py-3 text-sm text-orange-600 transition-all duration-200 transform bg-white border-2 border-orange-200 rounded-lg shadow-md hover:text-orange-700 hover:bg-orange-50 hover:border-orange-300 hover:shadow-lg hover:scale-105">
                                        <i data-feather="edit-2" class="w-4 h-4"></i>
                                        <span class="font-medium">Edit Schedule</span>
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                                    @foreach ($currentSchedule as $schedule)
                                        <div class="p-6 transition-all duration-200 bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
                                            <div class="flex items-center gap-3 mb-4">
                                                <div class="p-2 bg-blue-100 rounded-lg">
                                                    <i data-feather="calendar" class="w-4 h-4 text-blue-600"></i>
                                                </div>
                                                <h5 class="font-semibold text-gray-800">{{ $schedule->day_of_week }}</h5>
                                            </div>

                                            <div class="space-y-4">
                                                {{-- Check-Out Time --}}
                                                <div class="space-y-2">
                                                    <label for="time_out_{{ strtolower($schedule->day_of_week) }}" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                                        <i data-feather="log-out" class="w-4 h-4 text-red-500"></i>
                                                        Get Out Time
                                                    </label>
                                                    <input type="time"
                                                        name="schedule[{{ strtolower($schedule->day_of_week) }}][time_out]"
                                                        id="time_out_{{ strtolower($schedule->day_of_week) }}"
                                                        value="{{ old('schedule.' . strtolower($schedule->day_of_week) . '.time_out', $schedule->getFormattedTimeOutAttribute()) }}"
                                                        class="w-full p-3 border-2 {{ $errors->has('schedule.' . strtolower($schedule->day_of_week) . '.time_out') ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-gray-50' }} rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                                        disabled
                                                        required>
                                                    @error('schedule.' . strtolower($schedule->day_of_week) . '.time_out')
                                                        <div class="flex items-center gap-1 mt-1 text-xs text-red-500">
                                                            <i data-feather="alert-circle" class="w-3 h-3"></i>
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>

                                                {{-- Check-In Time --}}
                                                <div class="space-y-2">
                                                    <label for="time_in_{{ strtolower($schedule->day_of_week) }}" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                                        <i data-feather="log-in" class="w-4 h-4 text-green-500"></i>
                                                        Get In Time
                                                    </label>
                                                    <input type="time"
                                                        name="schedule[{{ strtolower($schedule->day_of_week) }}][time_in]"
                                                        id="time_in_{{ strtolower($schedule->day_of_week) }}"
                                                        value="{{ old('schedule.' . strtolower($schedule->day_of_week) . '.time_in', $schedule->getFormattedTimeInAttribute()) }}"
                                                        class="w-full p-3 border-2 {{ $errors->has('schedule.' . strtolower($schedule->day_of_week) . '.time_in') ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-gray-50' }} rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                                        disabled
                                                        required>
                                                    @error('schedule.' . strtolower($schedule->day_of_week) . '.time_in')
                                                        <div class="flex items-center gap-1 mt-1 text-xs text-red-500">
                                                            <i data-feather="alert-circle" class="w-3 h-3"></i>
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        {{-- New schedule creation section --}}
                        @php $showHeaders = false; @endphp
                        @foreach ($days as $day)
                            @if (!$currentSchedule || !$currentSchedule->where('day_of_week', $day)->first())
                                @if (!$showHeaders)
                                    <div class="mb-6">
                                        <h4 class="flex items-center gap-2 mb-4 text-lg font-semibold text-gray-800">
                                            <i data-feather="plus-circle" class="w-5 h-5 text-green-500"></i>
                                            Create New Schedule
                                        </h4>
                                        <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                                    @php $showHeaders = true; @endphp
                                @endif

                                <div class="p-6 transition-all duration-200 bg-white border border-gray-100 shadow-md rounded-xl hover:shadow-lg">
                                    <div class="flex items-center gap-3 mb-4">
                                        <div class="p-2 bg-green-100 rounded-lg">
                                            <i data-feather="calendar" class="w-4 h-4 text-green-600"></i>
                                        </div>
                                        <h5 class="font-semibold text-gray-800">{{ $day }}</h5>
                                    </div>

                                    <div class="space-y-4">
                                        {{-- Check-Out Time --}}
                                        <div class="space-y-2">
                                            <label for="{{ $day }}_timeout" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                                <i data-feather="log-out" class="w-4 h-4 text-red-500"></i>
                                                Get Out Time
                                            </label>
                                            <input type="time" id="{{ $day }}_timeout" name="schedule[{{ strtolower($day) }}][time_out]"
                                                value="{{ old('schedule.' . strtolower($day) . '.time_out') }}"
                                                class="w-full p-3 border-2 {{ $errors->has('schedule.' . strtolower($day) . '.time_out') ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-gray-50' }} rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                                required>
                                            @error('schedule.' . strtolower($day) . '.time_out')
                                                <div class="flex items-center gap-1 mt-1 text-xs text-red-500">
                                                    <i data-feather="alert-circle" class="w-3 h-3"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>

                                        {{-- Check-In Time --}}
                                        <div class="space-y-2">
                                            <label for="{{ $day }}_timein" class="flex items-center gap-2 text-sm font-medium text-gray-700">
                                                <i data-feather="log-in" class="w-4 h-4 text-green-500"></i>
                                                Get In Time
                                            </label>
                                            <input type="time" id="{{ $day }}_timein" name="schedule[{{ strtolower($day) }}][time_in]"
                                                value="{{ old('schedule.' . strtolower($day) . '.time_in') }}"
                                                class="w-full p-3 border-2 {{ $errors->has('schedule.' . strtolower($day) . '.time_in') ? 'border-red-500 bg-red-50' : 'border-gray-300 bg-gray-50' }} rounded-lg focus:outline-none focus:border-orange-500 focus:ring-2 focus:ring-orange-200 transition-all duration-200"
                                                required>
                                            @error('schedule.' . strtolower($day) . '.time_in')
                                                <div class="flex items-center gap-1 mt-1 text-xs text-red-500">
                                                    <i data-feather="alert-circle" class="w-3 h-3"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        @if ($showHeaders)
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Form submission section --}}
                    <div class="p-6 border border-gray-200 bg-gray-50 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                @if (session('success'))
                                    <div class="flex items-center gap-2 px-4 py-2 bg-green-100 border border-green-200 rounded-lg">
                                        <i data-feather="check-circle" class="w-4 h-4 text-green-600"></i>
                                        <p class="font-medium text-green-700">{{ session('success') }}</p>
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="flex items-center gap-2 px-4 py-2 bg-red-100 border border-red-200 rounded-lg">
                                        <i data-feather="alert-circle" class="w-4 h-4 text-red-600"></i>
                                        <p class="font-medium text-red-700">{{ session('error') }}</p>
                                    </div>
                                @endif
                            </div>

                            <button type="submit"
                                class="flex items-center gap-2 px-8 py-3 font-bold text-white transition-all duration-200 transform rounded-lg shadow-md bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 hover:shadow-lg hover:scale-105">
                                <i data-feather="save" class="w-4 h-4"></i>
                                <span>{{ $currentSchedule && $currentSchedule->isNotEmpty() ? 'Update Schedule' : 'Save Schedule' }}</span>
                            </button>
                        </div>
                    </div>
                    </div>
                </form>

                {{-- Delete schedule section --}}
                @if ($currentSchedule && $currentSchedule->isNotEmpty())
                    <div class="mt-6">
                        <div class="p-6 border border-red-200 bg-red-50 rounded-xl">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="p-2 bg-red-100 rounded-lg">
                                        <i data-feather="alert-triangle" class="w-5 h-5 text-red-600"></i>
                                    </div>
                                    <div>

                                        <p class="text-sm text-red-600">Permanently delete this schedule</p>
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('monitor.schedule.delete') }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="type" value="{{ request('type') }}">
                                    @if (request()->has('batch') && request()->has('group'))
                                        <input type="hidden" name="batch" value="{{ request('batch') }}">
                                        <input type="hidden" name="group" value="{{ request('group') }}">
                                    @elseif (request()->has('gender'))
                                        <input type="hidden" name="gender" value="{{ request('gender') }}">
                                    @elseif (request()->has('student_id'))
                                        <input type="hidden" name="student_id" value="{{ request('student_id') }}">
                                    @endif
                                    <button type="submit"
                                        onclick="return confirm('⚠️ Are you sure you want to permanently delete this schedule?\n\nThis action cannot be undone and will remove all schedule data.')"
                                        class="flex items-center gap-2 px-6 py-3 font-medium text-red-600 transition-all duration-200 transform bg-white border-2 border-red-200 rounded-lg hover:bg-red-600 hover:text-white hover:border-red-600 hover:shadow-lg hover:scale-105">
                                        <i data-feather="trash-2" class="w-4 h-4"></i>
                                        <span>Delete Schedule</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <script>
        let isEditing = false;

        // Handle semester dropdown change
        function handleSemesterChange() {
            const semesterSelect = document.getElementById('semester_id');
            const selectedSemester = semesterSelect.value;

            // Get current URL parameters
            const urlParams = new URLSearchParams(window.location.search);

            // Update or add semester_id parameter
            if (selectedSemester) {
                urlParams.set('semester_id', selectedSemester);
            } else {
                urlParams.delete('semester_id');
            }

            // Reload page with updated parameters
            window.location.href = window.location.pathname + '?' + urlParams.toString();
        }

        function toggleEditAll() {
            const timeInputs = document.querySelectorAll('input[type="time"], input[type="text"]');
            const editButton = document.querySelector('button[onclick="toggleEditAll()"]');

            isEditing = !isEditing;
            timeInputs.forEach(input => {
                input.disabled = !isEditing;
            });

            if (isEditing) {
                editButton.innerHTML =
                    '<i data-feather="save" class="w-4 h-4"></i> <span class="font-medium">Done Editing</span>';
                editButton.classList.remove('text-orange-500', 'hover:text-orange-600');
                editButton.classList.add('text-green-500', 'hover:text-green-600');
            } else {
                editButton.innerHTML =
                    '<i data-feather="edit-2" class="w-4 h-4"></i> <span class="font-medium">Edit Schedule</span>';
                editButton.classList.remove('text-green-500', 'hover:text-green-600');
                editButton.classList.add('text-orange-500', 'hover:text-orange-600');
            }
            feather.replace();
        }

        function toggleDateInput(value) {
            const dateContainer = document.getElementById('dateInputContainer');
            const dateInput = document.getElementById('valid_until');

            if (value === 'set_date') {
                dateContainer.classList.remove('hidden');
                dateInput.setAttribute('required', 'required');
            } else {
                dateContainer.classList.add('hidden');
                dateInput.removeAttribute('required');
                dateInput.value = '';
            }
        }

        // Initialize on page load
        document.addEventListener("DOMContentLoaded", function() {
            const validUntilOption = document.getElementById('valid_until_option');
            if (validUntilOption) {
                toggleDateInput(validUntilOption.value);
            }
            feather.replace();
        });

        // Add form submission handler (guarded)
        (function() {
            const form = document.getElementById('scheduleForm');
            if (!form) return;

            form.addEventListener('submit', function(e) {
                if (isEditing) {
                    e.preventDefault();
                    alert('Please click "Done Editing" before updating the schedule.');
                    return false;
                }

                // Enable all form inputs before submission (scoped to the form)
                const inputs = form.querySelectorAll('input[type="time"], input[type="text"], input[type="number"], select, textarea');
                inputs.forEach(input => {
                    input.disabled = false;
                });
            });
        })();

        function openQuickSetup() {
            document.getElementById('quickSetupModal').classList.remove('hidden');
        }

        function closeQuickSetup() {
            document.getElementById('quickSetupModal').classList.add('hidden');
            // Reset form when closing
            document.getElementById('selectAllDays').checked = false;
            document.querySelectorAll('.day-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            document.getElementById('quick_time_out').value = '';
            document.getElementById('quick_time_in').value = '';
        }

        function toggleAllDays() {
            const selectAllCheckbox = document.getElementById('selectAllDays');
            const dayCheckboxes = document.querySelectorAll('.day-checkbox');

            dayCheckboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function updateSelectAllState() {
            const selectAllCheckbox = document.getElementById('selectAllDays');
            const dayCheckboxes = document.querySelectorAll('.day-checkbox');
            const checkedDays = document.querySelectorAll('.day-checkbox:checked');

            // If all days are checked, check the "Select All" checkbox
            if (checkedDays.length === dayCheckboxes.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            }
            // If some days are checked, show indeterminate state
            else if (checkedDays.length > 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
            // If no days are checked, uncheck "Select All"
            else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            }
        }

        function applyQuickSetup() {
            const selectedDays = Array.from(document.querySelectorAll('input[name="quick_days[]"]:checked')).map(cb => cb.value);
            const timeOut = document.getElementById('quick_time_out').value;
            const timeIn = document.getElementById('quick_time_in').value;

            if (selectedDays.length === 0) {
                alert('Please select at least one day');
                return;
            }

            if (!timeOut || !timeIn) {
                alert('Please set both check-out and check-in times');
                return;
            }

            selectedDays.forEach(day => {
                const timeOutInput = document.querySelector(`input[name="schedule[${day}][time_out]"]`);
                const timeInInput = document.querySelector(`input[name="schedule[${day}][time_in]"]`);

                if (timeOutInput) timeOutInput.value = timeOut;
                if (timeInInput) timeInInput.value = timeIn;
            });

            closeQuickSetup();
        }

        let isEditingGracePeriod = false;

        // Toggle grace period edit mode
        function toggleGracePeriodEdit() {
            const logoutInput = document.getElementById('grace_period_logout_minutes');
            const loginInput = document.getElementById('grace_period_login_minutes');
            const editBtn = document.getElementById('editGracePeriodBtn');
            const saveBtn = document.getElementById('saveGracePeriodBtn');
            const logoutCheck = document.getElementById('logoutCheckIcon');
            const loginCheck = document.getElementById('loginCheckIcon');

            @if (!($currentSchedule && $currentSchedule->isNotEmpty()))
                // For new schedules, grace periods are part of the main form submission
                // Just show/hide the save button for visual feedback
                if (editBtn.style.display !== 'none') {
                    editBtn.style.display = 'none';
                    saveBtn.style.display = 'flex';
                    logoutInput.focus();
                } else {
                    editBtn.style.display = 'flex';
                    saveBtn.style.display = 'none';
                }
                return;
            @endif

            isEditingGracePeriod = !isEditingGracePeriod;

            if (isEditingGracePeriod) {
                // Enable editing
                logoutInput.disabled = false;
                loginInput.disabled = false;
                editBtn.style.display = 'none';
                saveBtn.style.display = 'flex';

                // Hide check icons during editing
                logoutCheck.classList.add('hidden');
                loginCheck.classList.add('hidden');

                // Focus on first input
                logoutInput.focus();
            } else {
                // Disable editing
                logoutInput.disabled = true;
                loginInput.disabled = true;
                editBtn.style.display = 'flex';
                saveBtn.style.display = 'none';
            }
        }

        // Save grace period changes
        function saveGracePeriod() {
            @if (!($currentSchedule && $currentSchedule->isNotEmpty()))
                // For new schedules, grace periods will be saved with the main form
                // Just provide visual feedback
                const statusDiv = document.getElementById('gracePeriodStatus');
                const statusText = statusDiv.querySelector('p');

                statusDiv.className = 'p-3 mt-3 rounded-lg bg-blue-50 border border-blue-200';
                statusText.innerHTML = '<i data-feather="info" class="inline w-4 h-4 mr-1"></i>Grace periods will be saved when you submit the schedule.';
                statusDiv.classList.remove('hidden');
                feather.replace();

                toggleGracePeriodEdit();

                setTimeout(() => {
                    statusDiv.classList.add('hidden');
                }, 3000);

                return;
            @endif

            @if ($currentSchedule && $currentSchedule->isNotEmpty())
                const logoutInput = document.getElementById('grace_period_logout_minutes');
                const loginInput = document.getElementById('grace_period_login_minutes');
                const statusDiv = document.getElementById('gracePeriodStatus');
                const statusText = statusDiv.querySelector('p');
                const logoutCheck = document.getElementById('logoutCheckIcon');
                const loginCheck = document.getElementById('loginCheckIcon');

                // Show loading status
                statusDiv.className = 'p-3 mt-3 rounded-lg bg-yellow-50 border border-yellow-200';
                statusText.textContent = 'Saving grace period settings...';
                statusDiv.classList.remove('hidden');

                // Prepare data
                const formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                formData.append('_method', 'PATCH');
                formData.append('type', '{{ $data["type"] }}');
                @if (request()->has('batch') && request()->has('group'))
                    formData.append('batch', '{{ request("batch") }}');
                    formData.append('group', '{{ request("group") }}');
                @elseif (request()->has('student_id'))
                    formData.append('student_id', '{{ request("student_id") }}');
                @elseif (request()->has('gender'))
                    formData.append('gender', '{{ request("gender") }}');
                @endif

                formData.append('grace_period_logout_minutes', logoutInput.value || '');
                formData.append('grace_period_login_minutes', loginInput.value || '');

                // Send AJAX request
                fetch('{{ route("monitor.schedule.update-grace-period") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Show success status
                        statusDiv.className = 'p-3 mt-3 rounded-lg bg-green-50 border border-green-200';
                        statusText.innerHTML = '<i data-feather="check-circle" class="inline w-4 h-4 mr-1"></i>Grace period settings saved successfully!';
                        feather.replace();

                        // Show check icons
                        logoutCheck.classList.remove('hidden');
                        loginCheck.classList.remove('hidden');
                        feather.replace();

                        // Exit edit mode
                        toggleGracePeriodEdit();

                        // Hide status after 3 seconds
                        setTimeout(() => {
                            statusDiv.classList.add('hidden');
                        }, 3000);
                    } else {
                        // Show error status
                        statusDiv.className = 'p-3 mt-3 rounded-lg bg-red-50 border border-red-200';
                        statusText.innerHTML = '<i data-feather="alert-circle" class="inline w-4 h-4 mr-1"></i>' + (data.message || 'Failed to save grace period');
                        feather.replace();
                    }
                })
                .catch(error => {
                    console.error('Error saving grace period:', error);
                    statusDiv.className = 'p-3 mt-3 rounded-lg bg-red-50 border border-red-200';
                    statusText.innerHTML = '<i data-feather="alert-circle" class="inline w-4 h-4 mr-1"></i>Network error occurred';
                    feather.replace();
                });
            @endif
        }

        // Initialize grace period display on page load
        document.addEventListener("DOMContentLoaded", function() {
            @if ($currentSchedule && $currentSchedule->isNotEmpty())
                const logoutInput = document.getElementById('grace_period_logout_minutes');
                const loginInput = document.getElementById('grace_period_login_minutes');
                const logoutCheck = document.getElementById('logoutCheckIcon');
                const loginCheck = document.getElementById('loginCheckIcon');

                // Show check icons if values are set
                if (logoutInput.value) {
                    logoutCheck.classList.remove('hidden');
                }
                if (loginInput.value) {
                    loginCheck.classList.remove('hidden');
                }

                feather.replace();
            @endif
        });
    </script>
</x-monitorLayout>
