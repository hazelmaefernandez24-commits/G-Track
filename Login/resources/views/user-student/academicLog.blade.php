<x-studentLayout>
        <div class="w-full max-w-2xl p-8 bg-white shadow-xl rounded-2xl">

            <div class="flex items-center justify-center mb-6 text-orange-600">
                <!-- Icon -->
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 mr-2 sm:h-7 sm:w-7 md:h-8 md:w-8" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6l-8 4 8 4 8-4-8-4z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 10v6a2 2 0 002 2h12a2 2 0 002-2v-6" />
                </svg>

                <!-- Title -->
                <h1 class="text-xl font-bold text-orange-600 sm:text-xl md:text-2xl lg:text-3xl">
                    Academic Log
                </h1>
            </div>

            <a href="{{ route('academicLogForms.show') }}"
                class="inline-flex items-center mb-6 text-blue-700 hover:underline">
                <i data-feather="arrow-left" class="w-5 h-5 mr-2"></i>
            </a>
            @if ($logoutLog && !$loginLog)
                <form action="{{ route('academic.logout') }}" method="POST">
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
                            class="py-2 font-semibold text-orange-500 transition-all duration-300 bg-orange-200 border-2 border-orange-300 rounded-md shadow-md hover:bg-orange-500 hover:text-white px-7 hover:scale-105">
                            Get Out
                        </button>
                    </div>
                </form>
            @elseif ($loginLog && !$logoutLog)
                <form action="{{ route('academic.login') }}" method="POST">
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
                            class="py-2 font-semibold text-orange-500 transition-all duration-300 bg-orange-200 border-2 border-orange-300 rounded-md shadow-md hover:bg-orange-500 hover:text-white px-7 hover:scale-105">
                            Get In
                        </button>
                    </div>
                </form>
            @endif
            <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    feather.replace();
                });
            </script>
</x-studentLayout>
