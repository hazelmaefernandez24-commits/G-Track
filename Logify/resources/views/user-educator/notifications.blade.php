<x-educator-layout>
<div class="min-h-screen bg-gray-50">
    {{-- Header --}}
    <div class="bg-white border-b border-gray-200 shadow-sm">
        <div class="px-6 mx-auto max-w-8xl sm:px-8 lg:px-10">
            <div class="flex items-center justify-between py-6">
                <div class="flex items-center gap-4">
                    <svg class="w-8 h-8 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                    </svg>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
                        <p class="text-sm text-gray-600">Real-time student activity updates</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Notifications Container --}}
    <div class="px-6 py-10 mx-auto max-w-8xl sm:px-8 lg:px-10">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
            {{-- Loading State --}}
            <div id="loading-state" class="flex items-center justify-center py-12">
                <div class="flex items-center gap-3">
                    <div class="w-6 h-6 border-b-2 border-blue-600 rounded-full animate-spin"></div>
                    <span class="text-gray-600">Loading notifications...</span>
                </div>
            </div>

            {{-- Empty State --}}
            <div id="empty-state" class="hidden py-12 text-center">
                <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z"/>
                </svg>
                <h3 class="mb-2 text-lg font-medium text-gray-900">No notifications yet</h3>
                <p class="text-gray-500">Student activity notifications will appear here in real-time.</p>
            </div>

            {{-- Notifications List --}}
            <div id="notifications-list" class="hidden divide-y divide-gray-200">
                <!-- Notifications will be populated here -->
            </div>
        </div>

        {{-- Load More Button --}}
        <div id="load-more-container" class="hidden mt-6 text-center">
            <button id="load-more-btn"
                    class="px-6 py-3 text-gray-700 transition-colors duration-200 bg-gray-100 rounded-lg hover:bg-gray-200">
                Load More Notifications
            </button>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentOffset = 0;
    const limit = 20;
    let isLoading = false;
    let hasMoreNotifications = true;

    // Initialize the page
    loadNotifications();

    // Set up real-time updates every 10 seconds
    setInterval(loadNotifications, 10000);

    // Mark all as read button
    document.getElementById('mark-all-read-btn').addEventListener('click', markAllAsRead);

    // Load more button
    document.getElementById('load-more-btn').addEventListener('click', loadMoreNotifications);

    function loadNotifications(reset = false) {
        if (isLoading) return;

        if (reset) {
            currentOffset = 0;
            hasMoreNotifications = true;
        }

        isLoading = true;

        fetch(`{{ route('educator.notifications.history') }}?limit=${limit}&offset=${currentOffset}`)
            .then(response => response.json())
            .then(data => {
                console.log('📡 API Response:', data); // Debug log
                if (data.success) {
                    console.log('📊 Notifications received:', data.notifications.length); // Debug log
                    displayNotifications(data.notifications, reset);
                    updateNotificationCount(data.total);

                    // Update pagination
                    currentOffset += data.notifications.length;
                    hasMoreNotifications = data.notifications.length === limit;

                    // Show/hide load more button
                    const loadMoreContainer = document.getElementById('load-more-container');
                    if (hasMoreNotifications && data.notifications.length > 0) {
                        loadMoreContainer.classList.remove('hidden');
                    } else {
                        loadMoreContainer.classList.add('hidden');
                    }
                } else {
                    console.error('❌ Error loading notifications:', data.error);
                }
            })
            .catch(error => {
                console.error('❌ Error fetching notifications:', error);
                console.error('❌ Full error details:', error.message, error.stack);
            })
            .finally(() => {
                isLoading = false;
                document.getElementById('loading-state').classList.add('hidden');
            });
    }

    function loadMoreNotifications() {
        loadNotifications(false);
    }

    function displayNotifications(notifications, reset = false) {
        console.log('🎨 Displaying notifications:', notifications.length, 'reset:', reset); // Debug log
        const notificationsList = document.getElementById('notifications-list');
        const emptyState = document.getElementById('empty-state');

        if (reset) {
            notificationsList.innerHTML = '';
        }

        if (notifications.length === 0 && reset) {
            console.log('📭 No notifications to display - showing empty state'); // Debug log
            notificationsList.classList.add('hidden');
            emptyState.classList.remove('hidden');
            return;
        }

        console.log('📋 Showing notifications list'); // Debug log
        notificationsList.classList.remove('hidden');
        emptyState.classList.add('hidden');

        notifications.forEach((notification, index) => {
            console.log(`📝 Creating notification ${index + 1}:`, notification); // Debug log
            const notificationElement = createNotificationElement(notification);
            notificationsList.appendChild(notificationElement);
        });
    }

    function createNotificationElement(notification) {
        const div = document.createElement('div');
        div.className = 'p-6 hover:bg-gray-50 transition-colors duration-200';

        const actionText = notification.action_type === 'time_in' ? 'logged in' : 'logged out';
        const actionColor = notification.action_type === 'time_in' ? 'text-blue-600' : 'text-orange-600';
        const logTypeText = notification.log_type === 'academic' ? 'Academic' :
                    notification.log_type === 'going_out' ? 'Leisure Out' :
                    notification.log_type === 'going_home' ? 'Going Home' :
                    notification.log_type === 'intern' ? 'Internship' :
                    'Visitor';


        // Create timing status indicator based on timing_status
        let timingIndicator = '';
        let timingText = '';
        if (notification.timing_status) {
            switch (notification.timing_status) {
                case 'Late':
                    timingIndicator = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">Late</span>';
                    timingText = ' and is marked as <span class="font-medium text-red-600">Late</span>';
                    break;
                case 'Early':
                    timingIndicator = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">Early</span>';
                    timingText = ' and is marked as <span class="font-medium text-green-600">Early</span>';
                    break;
                case 'On Time':
                    timingIndicator = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 ml-2">On Time</span>';
                    timingText = ' and is marked as <span class="font-medium text-blue-600">On Time</span>';
                    break;
                case 'nontime':
                    timingIndicator = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 ml-2">No Time</span>';
                    timingText = ' and is marked as <span class="font-medium text-gray-600">No Time</span>';
                    break;
                case 'Manual Entry':
                    timingIndicator = '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 ml-2">Manual Entry</span>';
                    timingText = ' and is marked as <span class="font-medium text-purple-600">Manual Entry</span>';
                    break;
            }
        }

        div.innerHTML = `
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="flex items-center justify-center w-10 h-10 bg-blue-100 rounded-full">
                        <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <p class="text-sm font-medium text-gray-900">
                                ${notification.student_name}
                            </p>
                            ${notification.batch ? `<span class="text-xs text-gray-500">Batch ${notification.batch}</span>` : ''}
                        </div>
                        <div class="text-xs text-gray-500">
                        ${notification.date_formatted}  ${notification.time_formatted}
                        </div>
                    </div>
                    <p class="mt-1 text-sm text-gray-600">
                        <span class="${actionColor} font-medium">${actionText}</span>
                        for ${logTypeText}${timingText}
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                    </p>
                </div>
            </div>
        `;

        return div;
    }

    function updateNotificationCount(total) {
        const countElement = document.getElementById('notification-count');
        countElement.textContent = `${total} total notifications`;
    }

    function markAllAsRead() {
        fetch('{{ route("educator.notifications.mark-all-read") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('All notifications marked as read');
                // Optionally refresh the notifications
                loadNotifications(true);
            } else {
                console.error('Error marking notifications as read:', data.error);
            }
        })
        .catch(error => {
            console.error('Error marking notifications as read:', error);
        });
    }
});
</script>
</x-educator-layout>
