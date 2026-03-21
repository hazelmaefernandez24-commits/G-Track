<x-studentLayout>
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
    </style>

    <div class="container px-4 py-6 mx-auto font-sans sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex flex-col items-center justify-between mb-6 sm:flex-row">
            <div class="flex items-center justify-between w-full mb-4 sm:w-auto sm:justify-start sm:mb-0">
                <div class="flex flex-col items-start justify-start mb-8 sm:flex-row">
                    <a href="/logify"
                        class="inline-flex items-center gap-1 bg-orange-200 text-orange-600 px-3 py-1.5 rounded-lg shadow-sm border border-orange-200 transition-all duration-300 group mb-4 sm:mb-0
                       hover:bg-orange-600 hover:text-white hover:border-orange-600">
                        <i data-feather="arrow-left" class="w-4 h-4 transition-transform group-hover:-translate-x-1"></i>
                        <span class="text-sm font-medium">Back</span>
                    </a>
                </div>
                <a href="{{ route('visitor.create') }}"
                    class="inline-flex items-center justify-center px-3 py-1.5 text-white transition bg-orange-600 rounded-md shadow hover:bg-orange-600 sm:hidden">
                    <i data-feather="user-plus" class="w-4 h-4"></i>
                </a>
            </div>
            <h1 class="mb-4 text-2xl font-bold text-center text-orange-700 sm:text-3xl sm:text-left sm:mb-0">Visitor Log Dashboard</h1>
            <a href="{{ route('visitor.create') }}"
                class="items-center justify-center hidden px-3 py-1.5 text-orange-600 transition bg-orange-200 rounded-md shadow sm:inline-flex hover:bg-orange-600 hover:text-white">
                <i data-feather="user-plus" class="w-5 h-5"></i>
                <span class="ml-2">Add New Visitor</span>
            </a>
        </div>


        <!-- Visitor Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow">
            <table class="min-w-full text-sm text-left divide-y divide-gray-200">
                <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                    <tr>
                        <th class="px-4 py-3">Visitors Pass</th>
                        <th class="px-4 py-3">Visitors Name</th>
                        <th class="px-4 py-3">ID Type</th>
                        <th class="px-4 py-3">ID number</th>
                        <th class="px-4 py-3">Relationship</th>
                        <th class="px-4 py-3">Purpose</th>
                        <th class="px-4 py-3">Date of Visit</th>
                        <th class="px-4 py-3">Get In Time</th>
                        <th class="px-4 py-3">Get Out Time</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    @foreach ($visitors as $visitor)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-4">{{ $visitor->visitor_pass }}</td>
                            <td class="px-4 py-4">{{ $visitor->visitor_name }}</td>
                            <td class="px-4 py-4">{{ $visitor->valid_id }}</td>
                            <td class="px-4 py-4">{{ $visitor->id_number }}</td>
                            <td class="px-4 py-4">{{ $visitor->relationship }}</td>
                            <td class="px-4 py-4">{{ $visitor->purpose }}</td>
                            <td class="px-4 py-4">{{ $visitor->visit_date }}</td>
                            <td class="px-4 py-4">{{ $visitor->formatted_time_in }}</td>
                            <td class="px-4 py-4">
                                @if ($visitor->time_out)
                                    {{ $visitor->formatted_time_out }}
                                @else
                                    <form method="POST"
                                        action="{{ route('visitor.logOut', ['id' => $visitor->id]) }}">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center justify-center px-3 py-1.5 text-sm text-blue-600 transition bg-blue-200 rounded-md shadow hover:bg-blue-600 hover:text-white">
                                            <i data-feather="log-out"></i>
                                            <span class="hidden ml-2 sm:inline">Get Out</span>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="flex justify-end mt-6">
{{ $visitors->links('custom-pagination') }}
</div>

    </div>
</x-studentLayout>
