<x-studentLayout>
        <div class="w-full max-w-2xl p-8 bg-white shadow-xl rounded-2xl">
            <div class="flex items-center justify-center mb-8 text-orange-600">
                <!-- Navigation Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 19l9 2-7-18-2 7-7 2 9 7z" />
                </svg>
                <h1 class="text-xl font-bold text-orange-600 sm:text-2xl md:text-3xl lg:text-3xl">
                    Going Home Log
                </h1>


            </div>
            <a href="{{ route('internLogForms.show') }}" class="inline-flex items-center mb-6 text-blue-700 hover:underline">
                <i data-feather="arrow-left" class="w-5 h-5 mr-2"></i>
            </a>

            @if ($logoutLog && !$loginLog)
                <form action="{{ route('goinghome.logout') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label for="student_id" class="block mb-2 font-medium text-gray-700">Student ID</label>
                        <input type="text" id="student_id" name="student_id" value="{{ old('student_id') }}"
                            class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none {{ $errors->has('student_id') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500' }}"">
                        @error('student_id')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="text-center">
                        <button type="submit"
                            class="py-2 font-semibold text-orange-500 transition-all duration-300 bg-orange-200 border-2 border-orange-300 rounded-md shadow-md px-7 hover:bg-orange-500 hover:text-white hover:scale-105">
                            Get Out
                        </button>
                    </div>
                </form>
            @elseif ($loginLog && !$logoutLog)
                <form action="{{ route('goinghome.login') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label for="student_id" class="block mb-2 font-medium text-gray-700">Student ID</label>
                        <input type="text" id="student_id" name="student_id" value="{{ old('student_id') }}"
                            class="w-full px-3 py-2 border border-gray-300 bg-gray-100 rounded-md shadow-sm focus:ring-2 focus:ring-orange-400 focus:outline-none {{ $errors->has('student_id') ? 'border-red-500 focus:ring-red-500 focus:border-red-500' : 'border-gray-300 focus:ring-orange-500 focus:border-orange-500' }}"">
                        @error('student_id')
                            <p class="mt-2 text-sm text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="text-center">
                        <button type="submit"
                            class="py-2 font-semibold text-orange-500 transition-all duration-300 bg-orange-200 border-2 border-orange-300 rounded-md shadow-md px-7 hover:bg-orange-500 hover:text-white hover:scale-105">
                            Get In
                        </button>
                    </div>
                </form>
            @else
                <div class="text-center text-gray-700">
                    <p class="text-lg font-semibold">You have already completed your logs for today.</p>
                    <p class="mt-2">Time Out: {{ $logged->time_out }}</p>
                    <p class="mt-2">Time In: {{ $logged->time_in }}</p>
                    <p class="mt-2">Destination: {{ $logged->destination }}</p>
                    <p class="mt-2">Purpose: {{ $logged->purpose }}</p>
                </div>
            @endif
        </div>
</x-studentLayout>
