<x-studentLayout>
    <div class="container px-4 py-8 mx-auto font-sans sm:px-6 lg:px-8">
        {{-- Header Section with Back Button --}}
        <div class="flex flex-col items-start justify-between mb-8 sm:flex-row">
            <a href="/student/dashboard"
                class="inline-flex items-center justify-center px-3 py-1.5 text-orange-600 transition-all duration-300 bg-orange-200 border border-orange-200 rounded-lg shadow-sm hover:bg-orange-600 hover:text-white hover:border-orange-600">
                <i data-feather="arrow-left" class="w-4 h-4 mr-1"></i>
                <span class="text-sm font-medium">Back to Dashboard</span>
            </a>
        </div>


        {{-- Title Section --}}
        <div class="mb-12 text-center">
            <h1 class="mb-4 text-3xl font-bold text-gray-800 sm:text-4xl">
                @if ($academic)
                    <span class="text-orange-600">Academic Log</span>
                @elseif($goingout)
                    <span class="text-orange-600">Leisure Log</span>
                @elseif($intern)
                    <span class="text-orange-600">Intern Log</span>
                @elseif($goinghome)
                    <span class="text-orange-600">Going Home Log</span>
                @endif
            </h1>
            <p class="text-lg text-gray-600">Select your Get In / Get Out action below</p>
            <div class="w-24 h-1 mx-auto mt-4 bg-orange-500 rounded-full"></div>
        </div>

        {{-- Logging Options Container --}}
        <div class="max-w-4xl mx-auto">
            <div class="grid max-w-3xl grid-cols-1 gap-4 mx-auto md:grid-cols-2">
                @if ($academic)
                    {{-- Academic Log Out --}}
                    <a href="{{ route('academic.logout.form') }}"
                        class="relative transition-all duration-300 bg-blue-200 border-2 border-blue-500 rounded-lg shadow-md group hover:bg-blue-500">
                        <div class="flex flex-col items-center p-4 text-center">
                            <div
                                class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-blue-100 rounded-full group-hover:bg-white">
                                <i data-feather="book-open" class="w-6 h-6 text-blue-600 group-hover:text-blue-500"></i>
                            </div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Get Out</h3>
                            <p class="text-sm text-gray-600 group-hover:text-white">Record your departure when leaving for
                                School.</p>
                        </div>
                    </a>

                    {{-- Academic Log In --}}
                    <a href="{{ route('academic.login.form') }}"
                        class="relative transition-all duration-300 bg-orange-200 border-2 border-orange-500 rounded-lg shadow-md group hover:bg-orange-500">
                        <div class="flex flex-col items-center p-4 text-center">
                            <div
                                class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-orange-100 rounded-full group-hover:bg-white">
                                <i data-feather="log-in"
                                    class="w-6 h-6 text-orange-600 group-hover:text-orange-500"></i>
                            </div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Get In</h3>
                            <p class="text-sm text-gray-600 group-hover:text-white">Record your arrival back at the Center.
                            </p>
                        </div>
                    </a>
                @elseif ($goingout)
                    {{-- Session Status Information --}}
                    @if(isset($currentSessions))
                        <div class="col-span-1 md:col-span-2 mb-4">
                            <div class="p-4 bg-blue-50 border border-blue-200 rounded-lg">
                                <h4 class="text-sm font-semibold text-blue-800 mb-2">Today's Sessions</h4>
                                <div class="text-xs text-blue-600">
                                    Multiple sessions are allowed. Complete your current session before starting a new one.
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Going Out Log Out --}}
                    <a href="{{ route('goingout.logout.form') }}"
                        class="relative transition-all duration-300 bg-blue-200 border-2 border-blue-500 rounded-lg shadow-md group hover:bg-blue-500">
                        <div class="flex flex-col items-center p-4 text-center">
                            <div
                                class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-blue-100 rounded-full group-hover:bg-white">
                                <i data-feather="log-out" class="w-6 h-6 text-blue-600 group-hover:text-blue-500"></i>
                            </div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Get Out</h3>
                            <p class="text-sm text-gray-600 group-hover:text-white">Start a new session or get out for leisure activities.</p>
                        </div>
                    </a>

                    {{-- Going Out Log In --}}
                    <a href="{{ route('goingout.login.form') }}"
                        class="relative transition-all duration-300 bg-orange-200 border-2 border-orange-500 rounded-lg shadow-md group hover:bg-orange-500">
                        <div class="flex flex-col items-center p-4 text-center">
                            <div
                                class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-orange-100 rounded-full group-hover:bg-white">
                                <i data-feather="log-in"
                                    class="w-6 h-6 text-orange-600 group-hover:text-orange-500"></i>
                            </div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Get In</h3>
                            <p class="text-sm text-gray-600 group-hover:text-white">Complete your current session by recording your return.</p>
                        </div>
                    </a>
                @elseif ($intern)
                    <a href="{{ route('intern.logout.form') }}"
                        class="relative transition-all duration-300 bg-blue-200 border-2 border-blue-500 rounded-lg shadow-md group hover:bg-blue-500">
                        <div class="flex flex-col items-center p-4 text-center">
                            <div
                                class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-blue-100 rounded-full group-hover:bg-white">
                                <i data-feather="log-out" class="w-6 h-6 text-blue-600 group-hover:text-blue-500"></i>
                            </div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Get Out</h3>
                            <p class="text-sm text-gray-600 group-hover:text-white">Record your departure when leaving for
                                your intended destination.</p>
                        </div>
                    </a>

                    <a href="{{ route('intern.login.form') }}"
                        class="relative transition-all duration-300 bg-orange-200 border-2 border-orange-500 rounded-lg shadow-md group hover:bg-orange-500">
                        <div class="flex flex-col items-center p-4 text-center">
                            <div
                                class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-orange-100 rounded-full group-hover:bg-white">
                                <i data-feather="log-in"
                                    class="w-6 h-6 text-orange-600 group-hover:text-orange-500"></i>
                            </div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Get In</h3>
                            <p class="text-sm text-gray-600 group-hover:text-white">Record your return after going out.</p>
                        </div>
                    </a>
                @elseif ($goinghome)
                    <a href="{{ route('goinghome.logout.form') }}"
                        class="relative transition-all duration-300 bg-blue-200 border-2 border-blue-500 rounded-lg shadow-md group hover:bg-blue-500">
                        <div class="flex flex-col items-center p-4 text-center">
                            <div
                                class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-blue-100 rounded-full group-hover:bg-white">
                                <i data-feather="log-out" class="w-6 h-6 text-blue-600 group-hover:text-blue-500"></i>
                            </div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Get Out</h3>
                            <p class="text-sm text-gray-600 group-hover:text-white">Record your departure when leaving for
                                your intended destination.</p>
                        </div>
                    </a>

                    <a href="{{ route('goinghome.login.form') }}"
                        class="relative transition-all duration-300 bg-orange-200 border-2 border-orange-500 rounded-lg shadow-md group hover:bg-orange-500">
                        <div class="flex flex-col items-center p-4 text-center">
                            <div
                                class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-orange-100 rounded-full group-hover:bg-white">
                                <i data-feather="log-in"
                                    class="w-6 h-6 text-orange-600 group-hover:text-orange-500"></i>
                            </div>
                            <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Get In</h3>
                            <p class="text-sm text-gray-600 group-hover:text-white">Record your return after going out.</p>
                        </div>
                    </a>
                @endif
            </div>
        </div>
        <div class="flex items-center justify-center mt-6 p-15">
            @if (session('success'))
                <div class="flex items-center justify-between p-4 mb-6 text-green-800 bg-green-100 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 12l2 2l4-4"></path>
                            <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9s4.03-9 9-9s9 4.03 9 9z"></path>
                        </svg>
                        {{ session('success') }}
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-green-500 hover:text-green-700">
                        ✖
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="flex items-center justify-between p-4 mb-6 text-red-800 bg-red-100 rounded-lg">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 9v2m0 4h.01"></path>
                            <path d="M21 12c0 4.97-4.03 9-9 9s-9-4.03-9-9s4.03-9 9-9s9 4.03 9 9z"></path>
                        </svg>
                        {{ session('error') }}
                    </div>
                    <button onclick="this.parentElement.remove()" class="text-red-500 hover:text-red-700">
                        ✖
                    </button>
                </div>
            @endif
        </div>
    </div>
</x-studentLayout>
