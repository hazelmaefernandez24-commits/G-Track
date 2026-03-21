<x-studentLayout>
    {{-- Full Width Container Override --}}
    <div class="full-width-dashboard">
        {{-- Top Bar (Title, Date & Logout) --}}
        {{-- <div class="relative z-20 flex items-center justify-between w-full px-8 py-4 bg-white shadow-sm">
            {{-- Left: Title --}}
            {{-- <div class="flex items-center gap-4">
                <h1 class="text-2xl font-bold text-gray-800">Monitor Portal</h1>
                <span class="px-3 py-1 text-sm font-medium text-orange-600 bg-orange-100 rounded-full">
                    Log Management
                </span>
            </div> --}}

           

        {{-- Main Content (Full Width without Sidebar) --}}
        <main class="w-full min-h-[calc(100vh-5rem)] p-6 bg-gray-50">
            {{ $slot }}
        </main>
    </div>

    {{-- Full Width Dashboard Styles --}}
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

        /* Override parent layout constraints for full width dashboard */
        .full-width-dashboard {
            width: 100vw;
            max-width: none;
            margin-left: calc(-50vw + 50%);
            margin-right: calc(-50vw + 50%);
            position: relative;
        }

        /* Enhanced table styling for log pages */
        .log-table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .log-table th {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            font-size: 0.75rem;
        }

        .log-table tr:hover {
            background-color: #f9fafb;
            transform: translateY(-1px);
            transition: all 0.2s ease;
        }

        /* Enhanced form styling */
        .consideration-form select,
        .consideration-form textarea,
        .consideration-form input {
            border-radius: 6px;
            border: 1px solid #d1d5db;
            transition: all 0.2s ease;
        }

        .consideration-form select:focus,
        .consideration-form textarea:focus,
        .consideration-form input:focus {
            border-color: #f97316;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.1);
            outline: none;
        }

        /* Enhanced button styling */
        .btn-primary {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border: none;
            border-radius: 6px;
            padding: 8px 16px;
            color: white;
            font-weight: 500;
            transition: all 0.2s ease;
            box-shadow: 0 2px 4px rgba(249, 115, 22, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(249, 115, 22, 0.3);
        }

        /* Status badges */
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .status-excused {
            background-color: #dcfce7;
            color: #166534;
        }

        .status-not-excused {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-absent {
            background-color: #f3f4f6;
            color: #374151;
        }

        .status-late {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .status-on-time {
            background-color: #dbeafe;
            color: #1e40af;
        }
    </style>

    {{-- Initialize Feather Icons --}}
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Feather Icons
            feather.replace();
        });
    </script>
</x-studentLayout>
