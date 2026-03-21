<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Staff Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/educator/educator.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .user-role {
            color: white;
            font-weight: 500;
            margin-right: 15px;
            background-color: rgba(255, 255, 255, 0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        .topbar-right {
            display: flex;
            align-items: center;
        }
        /* Toast Notification Styles */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            padding: 1rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: slideIn 0.3s ease-out;
            min-width: 300px;
            max-width: 400px;
        }

        .toast.success {
            border-left: 4px solid #28a745;
        }

        .toast.error {
            border-left: 4px solid #dc3545;
            background-color: #fff5f5;
        }

        .toast.info {
            border-left: 4px solid #17a2b8;
        }

        .toast.warning {
            border-left: 4px solid #ffc107;
        }

        .toast i {
            font-size: 1.25rem;
        }

        .toast.success i {
            color: #28a745;
        }

        .toast.error i {
            color: #dc3545;
        }

        .toast.info i {
            color: #17a2b8;
        }

        .toast.warning i {
            color: #ffc107;
        }

        .toast-message {
            flex: 1;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .toast.success .toast-message {
            color: #1e7e34;
        }

        .toast.error .toast-message {
            color: #dc3545;
        }

        .toast.info .toast-message {
            color: #17a2b8;
        }

        .toast.warning .toast-message {
            color: #856404;
        }

        .toast-close {
            background: none;
            border: none;
            cursor: pointer;
            padding: 0.25rem;
            font-size: 1rem;
            line-height: 1;
            opacity: 0.7;
        }

        .toast-close:hover {
            opacity: 1;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes fadeOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
    </style>
    @yield('css')
</head>
<body>
    <!-- Add toast container at the top of the body -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Include toast.js -->
    <script src="{{ asset('js/toast.js') }}"></script>

    <!-- Topbar -->
    <div class="nav-topbar">
        <img src="https://www.passerellesnumeriques.org/wp-content/uploads/2024/05/PN-Logo-English-White-Baseline.png.webp" alt="">
        <div class="topbar-right">
            <div style="position: relative; display: inline-block; margin-right: 20px;">
                <button id="notificationBtn"
                        style="background: none; border: none; color: white; font-size: 20px; cursor: pointer; position: relative; padding: 8px; border-radius: 50%; transition: background-color 0.3s ease; z-index: 2000;">
                    <i class="fas fa-bell"></i>
                    @if(($unreadCount ?? 0) > 0)
                    <span id="notificationBadge" style="position: absolute; top: 0; right: 0; background-color: #ff4757; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; font-weight: bold; display: flex; align-items: center; justify-content: center; border: 2px solid #55A6F3;">{{ $unreadCount ?? 0 }}</span>
                    @else
                    <span id="notificationBadge" style="display:none; position: absolute; top: 0; right: 0; background-color: #ff4757; color: white; border-radius: 50%; width: 18px; height: 18px; font-size: 11px; font-weight: bold; align-items: center; justify-content: center; border: 2px solid #55A6F3;">0</span>
                    @endif
                </button>
                <div id="notificationDropdown" style="position: absolute; top: 100%; right: 0; background: white; border-radius: 8px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15); width: 350px; max-height: 400px; overflow-y: auto; z-index: 1500; display: none; margin-top: 10px;">
                    <div style="padding: 15px 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                        <h6 style="margin: 0; color: #333; font-weight: 600;">Notifications</h6>
                        <button id="markAllRead" style="background: none; border: none; color: #55A6F3; cursor: pointer; font-size: 12px;">Mark all as read</button>
                    </div>
                    <div id="notificationList" style="max-height: 300px; overflow-y: auto;">
                        <div style="text-align: center; padding: 20px;">
                            <i class="fas fa-spinner fa-spin"></i> Loading notifications...
                        </div>
                    </div>
                    <div style="padding: 12px 20px; border-top: 1px solid #eee; text-align: center;">
                        <a href="{{ route('educator.notifications.page') }}" style="color: #55A6F3; text-decoration: none; font-size: 13px; font-weight: 500;">View all notifications</a>
                    </div>
                </div>
            </div>
            <span class="user-role">Educator</span>
            <form action="{{ route('logout') }}" method="post" style="display:inline">
                @csrf
                <button type="submit">
                    <svg class="w-6 h-6 text-gray-800" width="24" height="24" fill="none" viewBox="0 0 24 24">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M20 12H8m12 0-4 4m4-4-4-4M9 4H7a3 3 0 0 0-3 3v10a3 3 0 0 0 3 3h2"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>

        <!-- Sidebar -->
    <div class="nav-sidebar">
        <ul class="list-unstyled mb-0">
                <li class="p-3 {{ request()->routeIs('educator.dashboard') ? 'active' : ''}}"><a href="{{ route('educator.dashboard') }}" class="text-decoration-none"><img src="{{asset('images/dashboard.png')}}" alt=""> Dashboard</a></li>
                <li class="p-3 {{ request()->routeIs('educator.violation') ? 'active' : ''}}"><a href="{{ route('educator.violation') }}" class="text-decoration-none"><img src="{{ asset('images/warning (1).png') }}" alt=""> Violations</a></li>
                <li class="p-3 {{ request()->routeIs('educator.behavior') ? 'active' : '' }}"><a href="{{ route('educator.behavior') }}" class="text-decoration-none"><img src="{{ asset('images/online-report.png') }}" alt=""> Violation Analytics</a></li>

                <li class="p-3 {{ request()->routeIs('educator.manual') || request()->routeIs('educator.manual.analytics') ? 'active' : ''}}" style="position: relative;">
                    <a href="{{ route('educator.manual') }}" class="text-decoration-none">
                        <img src="{{ asset('images/manual.png') }}" alt=""> Student Code of Conduct
                    </a>
                </li>
                <li class="p-3 {{ request()->routeIs('educator.manual.analytics') ? 'active' : ''}}">
                    <a href="{{ route('educator.manual.analytics') }}" class="text-decoration-none">
                        <img src="{{ asset('images/market-research.png') }}" alt=""> Category Insights
                    </a>
                </li>
                <!-- <div class="dropdown-container">
                    <a href="page2.html">General Behavior</a>
                    <a href="page3.html">Schedules</a>
                    <a href="page4.html">Room Rules</a>
                    <a href="page5.html">Dress Code</a>
                    <a href="page6.html">Equipment</a>
                    <a href="page7.html">Center Tasking</a>
                </div> -->
            </ul>
        </div>
        
    <!-- Main Content -->
    <div class="main-content">
        @yield('content')
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Global function for onclick fallback (kept for safety, used by legacy code if any)
        function toggleNotifications(event) {
            console.log('toggleNotifications called via onclick');
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }

            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationList = document.getElementById('notificationList');

            if (notificationDropdown) {
                // Check current display state
                const isCurrentlyVisible = notificationDropdown.style.display === 'block';

                if (isCurrentlyVisible) {
                    // Hide dropdown
                    notificationDropdown.style.display = 'none';
                    console.log('Hiding dropdown');
                } else {
                    // Show dropdown
                    notificationDropdown.style.display = 'block';
                    console.log('Showing dropdown');

                    if (notificationList) {
                        // Show loading
                        notificationList.innerHTML = '<div style="text-align: center; padding: 20px;"><i class="fas fa-spinner fa-spin"></i> Loading notifications...</div>';

                        // Load notifications
                        fetch('{{ route("educator.notifications") }}')
                            .then(response => response.json())
                            .then(data => {
                                console.log('Notifications loaded:', data);
                                if (data.success) {
                                    if (data.notifications && data.notifications.data && data.notifications.data.length > 0) {
                                        displayNotificationsSimple(data.notifications.data);
                                    } else {
                                        notificationList.innerHTML = '<div style="text-align: center; padding: 20px; color: #666;"><i class="fas fa-bell-slash"></i> No notifications</div>';
                                    }
                                } else {
                                    notificationList.innerHTML = '<div style="text-align: center; padding: 20px; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> ' + (data.message || 'Failed to load notifications') + '</div>';
                                }
                            })
                            .catch(error => {
                                console.error('Error loading notifications:', error);
                                notificationList.innerHTML = '<div style="text-align: center; padding: 20px; color: #dc3545;"><i class="fas fa-exclamation-triangle"></i> Failed to load notifications</div>';
                            });
                    }
                }
            } else {
                console.log('Notification dropdown element not found');
            }
        }

        // Simple display function for global toggleNotifications
        function displayNotificationsSimple(notifications) {
            const notificationList = document.getElementById('notificationList');
            if (!notificationList) return;

            if (notifications.length === 0) {
                notificationList.innerHTML = '<div style="text-align: center; padding: 20px; color: #666;"><i class="fas fa-bell-slash"></i> No notifications</div>';
                return;
            }

            let html = '';
            notifications.forEach(notification => {
                const isUnread = !notification.is_read;
                const timeAgo = new Date(notification.created_at).toLocaleDateString();

                html += `
                    <div class="notification-item ${isUnread ? 'unread' : ''}" style="padding: 15px 20px; border-bottom: 1px solid #f5f5f5; cursor: pointer;">
                        <div style="display: flex; align-items: flex-start;">
                            <div style="margin-right: 12px; font-size: 16px; width: 20px; text-align: center;">
                                <i class="fas fa-exclamation-triangle" style="color: #ff4757;"></i>
                            </div>
                            <div style="flex: 1;">
                                <p style="margin: 0 0 4px 0; font-weight: 600; color: #333; font-size: 14px;">${notification.title}</p>
                                <p style="margin: 0 0 4px 0; color: #666; font-size: 13px;">${notification.message}</p>
                                <span style="color: #999; font-size: 11px;">${timeAgo}</span>
                            </div>
                        </div>
                    </div>
                `;
            });

            notificationList.innerHTML = html;
        }

        document.addEventListener("DOMContentLoaded", function () {
            // Notification functionality will be handled in DOMContentLoaded event below

            // Expose markAllAsRead globally to support any legacy inline calls
            window.markAllAsRead = function() {
                fetch('{{ route("educator.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.notification-item.unread').forEach(item => item.classList.remove('unread'));
                        // Update badge
                        fetch('{{ route("educator.notifications.unread-count") }}')
                          .then(r => r.json())
                          .then(cnt => {
                              const badge = document.getElementById('notificationBadge');
                              if (badge) {
                                  const c = cnt.success ? cnt.count : 0;
                                  badge.textContent = c;
                                  badge.style.display = c > 0 ? '' : 'none';
                              }
                          });
                    }
                })
                .catch(error => {
                    console.error('Error marking all as read:', error);
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(event) {
                const notificationDropdown = document.getElementById('notificationDropdown');
                const notificationBtn = event.target.closest('button[onclick*="toggleNotifications"]');

                if (notificationDropdown && !notificationBtn && !notificationDropdown.contains(event.target)) {
                    notificationDropdown.style.display = 'none';
                }
            });

        });

    </script>
    
    <!-- Bootstrap JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Chart.js and plugins -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.0.0"></script>
    <script src="https://cdn.jsdelivr.net/npm/hammerjs@2.0.8"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-zoom@1.2.1/dist/chartjs-plugin-zoom.min.js"></script>
    <script>
        // Register Chart.js plugins globally
        if (typeof Chart !== 'undefined') {
            Chart.register(ChartDataLabels);
        }

        // Educator Notification System
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM Content Loaded - Initializing notifications');

            const notificationBtn = document.getElementById('notificationBtn');
            const notificationDropdown = document.getElementById('notificationDropdown');
            const notificationBadge = document.getElementById('notificationBadge');
            const markAllReadBtn = document.getElementById('markAllRead');
            const notificationList = document.getElementById('notificationList');

            console.log('Notification elements:', {
                btn: !!notificationBtn,
                dropdown: !!notificationDropdown,
                badge: !!notificationBadge,
                markAllBtn: !!markAllReadBtn,
                list: !!notificationList
            });

            // Load notifications function
            function loadNotifications() {
                fetch('{{ route("educator.notifications") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            displayNotifications(data.notifications.data);
                        } else {
                            showNotificationError();
                        }
                    })
                    .catch(error => {
                        console.error('Error loading notifications:', error);
                        showNotificationError();
                    });
            }

            // Display notifications function
            function displayNotifications(notifications) {
                if (!notificationList) return;

                if (notifications.length === 0) {
                    notificationList.innerHTML = `
                        <div class="notification-empty" style="text-align: center; padding: 20px; color: #666;">
                            <i class="fas fa-bell-slash"></i>
                            <p>No notifications</p>
                        </div>
                    `;
                    return;
                }

                let notificationsHtml = '';
                notifications.forEach(notification => {
                    const isUnread = !notification.is_read;
                    const timeAgo = formatTimeAgo(notification.created_at);
                    const iconClass = getNotificationIcon(notification.type);

                    notificationsHtml += `
                        <div class="notification-item ${isUnread ? 'unread' : ''}" data-id="${notification.id}">
                            <div class="notification-icon">
                                <i class="${iconClass}"></i>
                            </div>
                            <div class="notification-content">
                                <p class="notification-title">${notification.title}</p>
                                <p class="notification-text">${notification.message}</p>
                                <span class="notification-time">${timeAgo}</span>
                            </div>
                        </div>
                    `;
                });

                notificationList.innerHTML = notificationsHtml;
                attachNotificationClickHandlers();
            }

            // Show error message
            function showNotificationError() {
                if (notificationList) {
                    notificationList.innerHTML = `
                        <div class="notification-error" style="text-align: center; padding: 20px; color: #dc3545;">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Failed to load notifications</p>
                        </div>
                    `;
                }
            }

            // Get notification icon based on type
            function getNotificationIcon(type) {
                switch(type) {
                    case 'warning': return 'fas fa-exclamation-circle text-warning';
                    case 'danger': return 'fas fa-exclamation-triangle text-danger';
                    case 'success': return 'fas fa-check-circle text-success';
                    case 'info':
                    default: return 'fas fa-info-circle text-info';
                }
            }

            // Format time ago
            function formatTimeAgo(dateString) {
                const date = new Date(dateString);
                const now = new Date();
                const diffInSeconds = Math.floor((now - date) / 1000);

                if (diffInSeconds < 60) return 'Just now';
                if (diffInSeconds < 3600) return Math.floor(diffInSeconds / 60) + ' minutes ago';
                if (diffInSeconds < 86400) return Math.floor(diffInSeconds / 3600) + ' hours ago';
                return Math.floor(diffInSeconds / 86400) + ' days ago';
            }

            // Attach click handlers to notification items
            function attachNotificationClickHandlers() {
                document.querySelectorAll('.notification-item').forEach(item => {
                    item.addEventListener('click', function() {
                        const notificationId = this.dataset.id;
                        if (this.classList.contains('unread')) {
                            markNotificationAsRead(notificationId, this);
                        }
                    });
                });
            }

            // Mark notification as read
            function markNotificationAsRead(notificationId, element) {
                fetch(`{{ route("educator.notifications.mark-read", ":id") }}`.replace(':id', notificationId), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        element.classList.remove('unread');
                        updateNotificationBadge();
                    }
                })
                .catch(error => {
                    console.error('Error marking notification as read:', error);
                });
            }

            // Mark all notifications as read
            function markAllNotificationsAsRead() {
                fetch('{{ route("educator.notifications.mark-all-read") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.querySelectorAll('.notification-item.unread').forEach(item => {
                            item.classList.remove('unread');
                        });
                        updateNotificationBadge();
                    }
                })
                .catch(error => {
                    console.error('Error marking all notifications as read:', error);
                });
            }

            // Update notification badge
            function updateNotificationBadge() {
                fetch('{{ route("educator.notifications.unread-count") }}')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && notificationBadge) {
                            const count = data.count;
                            notificationBadge.textContent = count;
                            notificationBadge.style.display = count > 0 ? '' : 'none';
                        }
                    })
                    .catch(error => {
                        console.error('Error updating notification badge:', error);
                    });
            }

            if (notificationBtn && notificationDropdown) {
                console.log('Notification elements found, adding event listeners');

                // Add a simple test to ensure the button is clickable
                notificationBtn.style.pointerEvents = 'auto';
                notificationBtn.style.cursor = 'pointer';

                // Toggle notification dropdown and load notifications
                notificationBtn.addEventListener('click', function(e) {
                    console.log('Notification button clicked!');
                    e.preventDefault();
                    e.stopPropagation();
                    const isVisible = notificationDropdown.style.display === 'block';
                    if (isVisible) {
                        notificationDropdown.style.display = 'none';
                    } else {
                        notificationDropdown.style.display = 'block';
                        console.log('Loading notifications...');
                        loadNotifications();
                    }
                });

                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!notificationBtn.contains(e.target) && !notificationDropdown.contains(e.target)) {
                        notificationDropdown.style.display = 'none';
                    }
                });

                // Mark all notifications as read
                if (markAllReadBtn) {
                    markAllReadBtn.addEventListener('click', function() {
                        markAllNotificationsAsRead();
                    });
                }
            }
        });

    </script>
    @stack('scripts')
    @yield('scripts')
    
</body>
</html>