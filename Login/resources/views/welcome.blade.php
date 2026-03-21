<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Welcome PN System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0% {
                transform: translateY(0px);
            }

            50% {
                transform: translateY(-20px);
            }

            100% {
                transform: translateY(0px);
            }
        }
    </style>
</head>

<body class="relative flex items-center justify-center min-h-screen px-4 overflow-hidden font-sans bg-gradient-to-br from-blue-50 via-indigo-100 to-purple-200">
    <!-- Background decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute bg-blue-200 rounded-full -top-24 -right-24 w-96 h-96 opacity-20 blur-3xl"></div>
        <div class="absolute bg-purple-200 rounded-full -bottom-24 -left-24 w-96 h-96 opacity-20 blur-3xl"></div>
    </div>

    <div class="relative z-10 w-full max-w-4xl overflow-hidden transition-all duration-500 border rounded-lg shadow-2xl bg-white/95 backdrop-blur-xl border-white/60 hover:shadow-blue-200/50">

        <!-- Header inside the card -->
        <header class="flex items-center justify-between px-8 py-5 text-white bg-blue-600 rounded-t-lg shadow-md">
            <div class="flex items-center gap-4">
                <img src="{{ asset('images/pnlogo.png') }}" alt="PN Logo" class="object-contain h-14 md:h-16" />

            </div>
            @if (Route::has('login'))
            <nav class="flex items-center justify-end gap-4">
                @if(!empty($token))
                    <a
                        href="{{ route('login.process', ['token' => $token]) }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-4 text-lg font-medium text-white transition bg-blue-600 shadow-lg hover:bg-blue-700 rounded-xl">
                        Dashboard
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center justify-center gap-2 px-6 py-4 text-lg font-medium text-white transition bg-blue-600 shadow-lg hover:bg-blue-700 rounded-xl">
                        Log in
                    </a>
                @endif
            </nav>
            @endif
        </header>

        <!-- Main content -->
        <div class="relative p-10 space-y-12 text-center md:p-16">
            <!-- Decorative elements -->
            <div class="absolute top-0 left-0 w-32 h-32 rounded-full bg-gradient-to-br from-orange-100 to-transparent opacity-40 blur-2xl"></div>
            <div class="absolute bottom-0 right-0 w-32 h-32 rounded-full bg-gradient-to-br from-blue-100 to-transparent opacity-40 blur-2xl"></div>
            <div>
                <h1 class="text-4xl font-bold text-transparent md:text-5xl bg-gradient-to-r from-orange-600 to-orange-500 bg-clip-text animate-float">Welcome to PN System</h1>
                <p class="mt-4 text-lg font-light text-gray-600 md:text-xl">Choose your action below</p>
            </div>

            <div class="flex flex-col justify-center gap-6 md:flex-row">
                <a href="{{ route('visitor.dashboard.show') }}"
                    class="flex flex-col items-center justify-center gap-4 px-8 py-6 text-lg font-medium text-orange-600 transition-all duration-300 transform bg-blue-200 border-2 shadow-lg group backdrop-blur-sm border-blue-600/20 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-blue-200 rounded-xl hover:-translate-y-1">
                    <i data-lucide="users" class="w-12 h-12 text-orange-600 transition-transform duration-300 group-hover:text-white group-hover:scale-110"></i>
                    <span>Visitor Log</span>
                </a>
                <a href="{{ route('student.dashboard') }}"
                    class="flex flex-col items-center justify-center gap-4 px-8 py-6 text-lg font-medium text-orange-600 transition-all duration-300 transform bg-blue-200 border-2 shadow-lg group backdrop-blur-sm border-blue-600/20 hover:border-blue-600 hover:bg-blue-600 hover:text-white hover:shadow-blue-200 rounded-xl hover:-translate-y-1">
                    <i data-lucide="graduation-cap" class="w-12 h-12 text-orange-600 transition-transform duration-300 group-hover:text-white group-hover:scale-110"></i>
                    <span>Student Log</span>
                </a>
            </div>
        </div>

        <!-- Footer with two lines -->
        <footer class="mt-10 mb-4 text-xs text-center text-gray-500 sm:text-sm">
            <p class="italic">"Empowering Logify for a Better PN Experience"</p>
            <p class="mt-1">© 2025 Logify. All rights reserved.</p>
        </footer>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>

</html>
