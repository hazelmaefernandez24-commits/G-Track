<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Staff Dashboard')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="{{ asset('css/student/student.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/4e45d9ad8d.js" crossorigin="anonymous"></script>
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
    </style>

    @yield('css')
</head>
<body>

    <!-- Topbar -->
    <div class="nav-topbar">
        <img src="https://www.passerellesnumeriques.org/wp-content/uploads/2024/05/PN-Logo-English-White-Baseline.png.webp" alt="">
        <div class="topbar-right">
            <div class="notification-container">
                <button class="notification-btn" id="studentNotificationBtn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-badge" id="studentNotificationBadge" style="display: none;">0</span>
                </button>
                <div class="notification-dropdown" id="studentNotificationDropdown">
                    <div class="notification-header">
                        <h6>Notifications</h6>
                        <button class="mark-all-read" id="studentMarkAllRead">Mark all as read</button>
                    </div>
                    <div class="notification-list" id="studentNotificationList">
                        <div class="notification-loading" style="text-align: center; padding: 20px;">
                            <i class="fas fa-spinner fa-spin"></i> Loading notifications...
                        </div>
                    </div>
                    <div class="notification-footer">
                        <a href="{{ route('student.notifications.page') }}" class="view-all-notifications">View all notifications</a>
                    </div>
                </div>
            </div>
            <span class="user-role">Student</span>
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

    <div class="dashboard-wrapper d-flex">
        <!-- Sidebar -->
        <div class="nav-sidebar">
            <ul class="list-unstyled">
                <li class="p-3 {{ request()->routeIs('student.violation') ? 'active' : ''}}"><a href="{{ route('student.violation') }}" class="text-decoration-none"><img src="{{ asset('images/warning (1).png') }}" alt=""> My Violations</a></li>
                {{-- Link to removed student.behavior page intentionally disabled --}}
                <li class="p-3 {{ request()->routeIs('student.manual') ? 'active' : ''}}"><a href="{{ route('student.manual') }}" class="text-decoration-none"><img src="{{ asset('images/manual.png') }}" alt=""> Student Code of Conduct</a></li>
            </ul>
        </div>
    </div>
    <div class="main-content">
            @yield('content')
        </div>
    
    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Student Notification JavaScript -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const studentNotificationBtn = document.getElementById('studentNotificationBtn');
        const studentNotificationDropdown = document.getElementById('studentNotificationDropdown');
        const studentNotificationBadge = document.getElementById('studentNotificationBadge');
        const studentMarkAllReadBtn = document.getElementById('studentMarkAllRead');
        const studentNotificationList = document.getElementById('studentNotificationList');

        // Load notifications function
        function loadNotifications() {
            fetch('{{ route("student.notifications") }}')
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
            if (!studentNotificationList) return;

            if (notifications.length === 0) {
                studentNotificationList.innerHTML = `
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
                    <div class="notification-item ${isUnread ? 'unread' : ''}"
                         data-id="${notification.id}"
                         data-related-id="${notification.related_id || ''}"
                         data-type="${notification.type}"
                         style="cursor: pointer;">
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

            studentNotificationList.innerHTML = notificationsHtml;
            attachNotificationClickHandlers();
        }

        // Show error message
        function showNotificationError() {
            if (studentNotificationList) {
                studentNotificationList.innerHTML = `
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
                    const relatedId = this.dataset.relatedId;
                    const notificationType = this.dataset.type;

                    // Mark as read if unread
                    if (this.classList.contains('unread')) {
                        markNotificationAsRead(notificationId, this);
                    }

                    // Handle navigation based on notification type and related content
                    if (relatedId && (notificationType === 'warning' || notificationType === 'success')) {
                        // For violation-related notifications, navigate to violation page with highlight
                        const violationUrl = '{{ route("student.violation") }}' + '?highlight=' + relatedId;
                        window.location.href = violationUrl;
                    }

                    // Close the notification dropdown
                    if (studentNotificationDropdown) {
                        studentNotificationDropdown.style.display = 'none';
                    }
                });
            });
        }

        // Mark notification as read
        function markNotificationAsRead(notificationId, element) {
            fetch(`{{ route("student.notifications.mark-read", ":id") }}`.replace(':id', notificationId), {
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
            fetch('{{ route("student.notifications.mark-all-read") }}', {
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
            fetch('{{ route("student.notifications.unread-count") }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success && studentNotificationBadge) {
                        const count = data.count;
                        studentNotificationBadge.textContent = count;
                        studentNotificationBadge.style.display = count > 0 ? 'flex' : 'none';
                    }
                })
                .catch(error => {
                    console.error('Error updating notification badge:', error);
                });
        }

        if (studentNotificationBtn && studentNotificationDropdown) {
            // Toggle notification dropdown and load notifications
            studentNotificationBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                const isVisible = studentNotificationDropdown.style.display === 'block';
                if (isVisible) {
                    studentNotificationDropdown.style.display = 'none';
                } else {
                    studentNotificationDropdown.style.display = 'block';
                    loadNotifications();
                }
            });

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!studentNotificationBtn.contains(e.target) && !studentNotificationDropdown.contains(e.target)) {
                    studentNotificationDropdown.style.display = 'none';
                }
            });

            // Mark all notifications as read
            if (studentMarkAllReadBtn) {
                studentMarkAllReadBtn.addEventListener('click', function() {
                    markAllNotificationsAsRead();
                });
            }
        }

        // Update notification badge on page load
        updateNotificationBadge();
    });
    </script>

    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js"></script>

    @stack('scripts')
</body>
</html>