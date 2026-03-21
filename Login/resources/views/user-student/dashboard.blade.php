<x-studentLayout>
    <div class="container px-4 py-8 mx-auto font-sans sm:px-6 lg:px-8">
        {{-- Top Navigation --}}
        <div class="flex flex-col items-start justify-start mb-8 sm:flex-row">
            <a href="/logify"
                class="inline-flex items-center gap-1 bg-orange-200 text-orange-600 px-3 py-1.5 rounded-lg shadow-sm border border-orange-200 transition-all duration-300 group mb-4 sm:mb-0
                       hover:bg-orange-600 hover:text-white hover:border-orange-600">
                <i data-feather="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                <span class="text-sm font-medium">Back</span>
            </a>
        </div>


        {{-- Welcome Header with Animation --}}
        <div class="relative mb-12 text-center">
            <h1 class="mb-4 text-4xl font-bold text-gray-800">
                Welcome to <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 to-orange-600">Logify</span>
            </h1>
            <p class="max-w-2xl mx-auto mb-4 text-base font-medium text-gray-600">
                Manage your academic and off campus going out with ease
            </p>
            <div class="w-32 h-1 mx-auto rounded-full bg-gradient-to-r from-orange-500 to-orange-400"></div>
        </div>

        {{-- Action Cards Container with Background Pattern --}}
        <div class="relative max-w-6xl mx-auto">
            <div class="absolute inset-0 bg-[radial-gradient(#e5e7eb_1px,transparent_1px)] [background-size:16px_16px] -z-10"></div>
            <div class="grid max-w-3xl grid-cols-1 gap-4 mx-auto md:grid-cols-2">
                {{-- Academic Log Card --}}
                <a href="{{ route('academicLogForms.show') }}"
                    class="relative transition-all duration-300 bg-blue-300 border-2 border-blue-500 rounded-lg shadow-md group hover:bg-blue-500">
                    <div class="flex flex-col items-center p-4 text-center">
                        <div class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-blue-100 rounded-full group-hover:bg-white">
                            <i data-feather="book-open" class="w-6 h-6 text-blue-600 group-hover:text-blue-500"></i>
                        </div>
                        <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Academic Log</h3>
                        <div class="flex items-center text-sm font-medium text-gray-600 group-hover:text-white">
                            <span>Access Academic Log</span>
                            <i data-feather="arrow-right" class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1"></i>
                        </div>
                    </div>
                </a>

                {{-- Going Out Log Card --}}
                <a href="{{ route('goingOutLogForms.show') }}"
                    class="relative transition-all duration-300 bg-blue-300 border-2 border-blue-500 rounded-lg shadow-md group hover:bg-blue-500">
                    <div class="flex flex-col items-center p-4 text-center">
                        <div class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-blue-100 rounded-full group-hover:bg-white">
                            <i data-feather="log-out" class="w-6 h-6 text-blue-600 group-hover:text-blue-500"></i>
                        </div>
                        <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Leisure Log</h3>
                        <div class="flex items-center text-sm font-medium text-gray-600 group-hover:text-white">
                            <span>Access Leisure Log</span>
                            <i data-feather="arrow-right" class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1"></i>
                        </div>
                    </div>
                </a>

                <a href="{{ route('internLogForms.show') }}"
                    class="relative transition-all duration-300 bg-orange-300 border-2 border-orange-500 rounded-lg shadow-md group hover:bg-orange-500">
                    <div class="flex flex-col items-center p-4 text-center">
                        <div class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-orange-100 rounded-full group-hover:bg-white">
                            <i data-feather="log-out" class="w-6 h-6 text-orange-600 group-hover:text-orange-500"></i>
                        </div>
                        <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Intern Log</h3>
                        <div class="flex items-center text-sm font-medium text-gray-600 group-hover:text-white">
                            <span>Access Intern Log</span>
                            <i data-feather="arrow-right" class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1"></i>
                        </div>
                    </div>
                </a>

                <a href="{{ route('goinghomeLogForms.show') }}"
                    class="relative transition-all duration-300 bg-orange-300 border-2 border-orange-500 rounded-lg shadow-md group hover:bg-orange-500">
                    <div class="flex flex-col items-center p-4 text-center">
                        <div class="flex items-center justify-center w-12 h-12 mb-3 transition-colors duration-300 bg-orange-100 rounded-full group-hover:bg-white">
                            <i data-feather="log-out" class="w-6 h-6 text-orange-600 group-hover:text-orange-500"></i>
                        </div>
                        <h3 class="mb-1 text-lg font-semibold text-gray-800 group-hover:text-white">Going Home Log</h3>
                        <div class="flex items-center text-sm font-medium text-gray-600 group-hover:text-white">
                            <span>Access Going Home Log</span>
                            <i data-feather="arrow-right" class="w-4 h-4 ml-2 transition-transform group-hover:translate-x-1"></i>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Feather Icons Script --}}
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            feather.replace();
        });
    </script>
</x-studentLayout>
