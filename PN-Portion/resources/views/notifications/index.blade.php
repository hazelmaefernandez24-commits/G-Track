    @extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h3 class="mb-0"><i class="bi bi-bell me-2"></i>Notifications</h3>
                        <p class="mb-0 text-muted">Stay updated with all system activities</p>
                    </div>
                    <div>
                        <button class="btn btn-outline-primary me-2" id="markAllReadBtn">
                            <i class="bi bi-check-all me-1"></i>Mark All Read
                        </button>
                        <button class="btn btn-outline-secondary" id="refreshBtn">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <select class="form-select" id="typeFilter">
                                <option value="">All Types</option>
                                <option value="menu_update">Menu Updates</option>
                                <option value="poll_created">Poll Created</option>
                                <option value="poll_response">Poll Responses</option>
                                <option value="inventory_report">Inventory Reports</option>
                                <option value="feedback_submitted">Feedback</option>
                                <option value="system_update">System Updates</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select class="form-select" id="statusFilter">
                                <option value="">All Notifications</option>
                                <option value="unread">Unread Only</option>
                                <option value="read">Read Only</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-primary" onclick="filterNotifications()">
                                    <i class="bi bi-funnel me-1"></i>Apply Filters
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                                    <i class="bi bi-x-circle me-1"></i>Clear
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body p-0">
                    <!-- Loading State -->
                    <div class="text-center p-5" id="loadingState">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2 text-muted">Loading notifications...</p>
                    </div>

                    <!-- Notifications Container -->
                    <div id="notificationsContainer" style="display: none;">
                        <!-- Notifications will be loaded here -->
                    </div>

                    <!-- Empty State -->
                    <div class="text-center p-5" id="emptyState" style="display: none;">
                        <i class="bi bi-bell-slash fs-1 text-muted mb-3"></i>
                        <h4 class="text-muted">No notifications found</h4>
                        <p class="text-muted">You're all caught up! New notifications will appear here.</p>
                    </div>

                    <!-- Error State -->
                    <div class="text-center p-5" id="errorState" style="display: none;">
                        <i class="bi bi-exclamation-triangle fs-1 text-danger mb-3"></i>
                        <h4 class="text-danger">Failed to load notifications</h4>
                        <p class="text-muted">Please try refreshing the page.</p>
                        <button class="btn btn-primary" onclick="loadNotifications()">
                            <i class="bi bi-arrow-clockwise me-1"></i>Try Again
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12">
            <nav id="paginationContainer" style="display: none;">
                <!-- Pagination will be loaded here -->
            </nav>
        </div>
    </div>
</div>

<style>
.notification-item {
    padding: 20px;
    border-bottom: 1px solid #f8f9fa;
    transition: background-color 0.2s;
    cursor: pointer;
}

.notification-item:hover {
    background-color: #f8f9fa;
}

.notification-item.unread {
    background-color: #e3f2fd;
    border-left: 4px solid #2196f3;
}

.notification-item:last-child {
    border-bottom: none;
}

.notification-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 16px;
    flex-shrink: 0;
}

.notification-content {
    flex-grow: 1;
}

.notification-title {
    font-weight: 600;
    font-size: 1rem;
    margin-bottom: 8px;
}

.notification-message {
    color: #6c757d;
    margin-bottom: 8px;
    line-height: 1.4;
}

.notification-meta {
    display: flex;
    justify-content: between;
    align-items: center;
    font-size: 0.875rem;
    color: #adb5bd;
}

.notification-actions {
    display: flex;
    gap: 8px;
}

/* Notification type colors */
.notification-icon.menu_update { background-color: #e8f5e8; color: #28a745; }
.notification-icon.poll_created { background-color: #fff3cd; color: #ffc107; }
.notification-icon.poll_response { background-color: #d4edda; color: #28a745; }
.notification-icon.inventory_report { background-color: #f8d7da; color: #dc3545; }
.notification-icon.inventory_approved { background-color: #d1ecf1; color: #17a2b8; }
.notification-icon.low_stock { background-color: #f8d7da; color: #dc3545; }
.notification-icon.feedback_submitted { background-color: #e2e3e5; color: #6c757d; }
.notification-icon.post_meal_report { background-color: #d4edda; color: #28a745; }
.notification-icon.system_update { background-color: #cce5ff; color: #007bff; }
.notification-icon.deadline_reminder { background-color: #fff3cd; color: #ffc107; }
.notification-icon.default { background-color: #e9ecef; color: #6c757d; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();

    // Event listeners
    document.getElementById('markAllReadBtn').addEventListener('click', markAllAsRead);
    document.getElementById('refreshBtn').addEventListener('click', () => loadNotifications());
});

let currentPage = 1;
let currentFilters = {};

function loadNotifications(page = 1) {
    currentPage = page;
    
    showLoadingState();
    
    const params = new URLSearchParams({
        page: page,
        ...currentFilters
    });
    
    fetch(`/notifications?${params}`, {
        headers: {
            'Accept': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayNotifications(data.notifications, data.pagination);
        } else {
            showErrorState();
        }
    })
    .catch(error => {
        console.error('Error loading notifications:', error);
        showErrorState();
    });
}

function displayNotifications(notifications, pagination) {
    const container = document.getElementById('notificationsContainer');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    const errorState = document.getElementById('errorState');
    
    // Hide all states
    loadingState.style.display = 'none';
    emptyState.style.display = 'none';
    errorState.style.display = 'none';
    
    if (notifications.length === 0) {
        emptyState.style.display = 'block';
        container.style.display = 'none';
        return;
    }
    
    // Show notifications
    container.style.display = 'block';
    container.innerHTML = notifications.map(notification => createNotificationHTML(notification)).join('');
    
    // Show pagination if needed
    if (pagination.last_page > 1) {
        displayPagination(pagination);
    }
}

function createNotificationHTML(notification) {
    const iconClass = getNotificationIcon(notification.type);
    const timeAgo = formatTimeAgo(notification.created_at);

    return `
        <div class="notification-item ${notification.unread ? 'unread' : ''}" onclick="handleNotificationClick(${notification.id}, '${notification.action_url || ''}')">
            <div class="d-flex">
                <div class="notification-icon ${notification.type}">
                    <i class="bi ${iconClass} fs-4"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${notification.title}</div>
                    <div class="notification-message">${notification.message}</div>
                    <div class="notification-meta">
                        <span>${timeAgo}</span>
                        <div class="notification-actions">
                            ${notification.unread ? '<span class="badge bg-primary">New</span>' : ''}
                            <button class="btn btn-sm btn-outline-danger" onclick="event.stopPropagation(); deleteNotification(${notification.id})">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function getNotificationIcon(type) {
    const icons = {
        'menu_update': 'bi-journal-text',
        'poll_created': 'bi-clipboard-check',
        'poll_response': 'bi-person-check',
        'inventory_report': 'bi-box-seam',
        'inventory_approved': 'bi-check-circle',
        'low_stock': 'bi-exclamation-triangle',
        'feedback_submitted': 'bi-chat-square-text',
        'post_meal_report': 'bi-clipboard-data',
        'system_update': 'bi-gear',
        'deadline_reminder': 'bi-clock',
        'default': 'bi-bell'
    };
    
    return icons[type] || icons['default'];
}

function formatTimeAgo(dateString) {
    const date = new Date(dateString);
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    
    return date.toLocaleDateString();
}

function handleNotificationClick(notificationId, actionUrl) {
    // Mark as read
    markNotificationAsRead(notificationId);

    // Navigate if action URL exists
    if (actionUrl) {
        window.location.href = actionUrl;
    }
}

function markNotificationAsRead(notificationId) {
    fetch(`/notifications/${notificationId}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Refresh current page
            loadNotifications(currentPage);
        }
    })
    .catch(error => {
        console.error('Error marking notification as read:', error);
    });
}

function markAllAsRead() {
    fetch('/notifications/mark-all-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('All notifications marked as read', 'success');
            loadNotifications(currentPage);
        }
    })
    .catch(error => {
        console.error('Error marking all notifications as read:', error);
    });
}

function deleteNotification(notificationId) {
    if (confirm('Are you sure you want to delete this notification?')) {
        fetch(`/notifications/${notificationId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Notification deleted', 'success');
                loadNotifications(currentPage);
            }
        })
        .catch(error => {
            console.error('Error deleting notification:', error);
        });
    }
}

function filterNotifications() {
    const typeFilter = document.getElementById('typeFilter').value;
    const statusFilter = document.getElementById('statusFilter').value;
    
    currentFilters = {};
    if (typeFilter) currentFilters.type = typeFilter;
    if (statusFilter) currentFilters.unread = statusFilter === 'unread' ? 'true' : 'false';
    
    loadNotifications(1);
}

function clearFilters() {
    document.getElementById('typeFilter').value = '';
    document.getElementById('statusFilter').value = '';
    currentFilters = {};
    loadNotifications(1);
}

function showLoadingState() {
    document.getElementById('loadingState').style.display = 'block';
    document.getElementById('notificationsContainer').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('errorState').style.display = 'none';
}

function showErrorState() {
    document.getElementById('loadingState').style.display = 'none';
    document.getElementById('notificationsContainer').style.display = 'none';
    document.getElementById('emptyState').style.display = 'none';
    document.getElementById('errorState').style.display = 'block';
}

function displayPagination(pagination) {
    const container = document.getElementById('paginationContainer');
    container.style.display = 'block';
    
    let paginationHTML = '<ul class="pagination justify-content-center">';
    
    // Previous button
    if (pagination.current_page > 1) {
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadNotifications(${pagination.current_page - 1})">Previous</a></li>`;
    }
    
    // Page numbers
    for (let i = 1; i <= pagination.last_page; i++) {
        if (i === pagination.current_page) {
            paginationHTML += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else {
            paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadNotifications(${i})">${i}</a></li>`;
        }
    }
    
    // Next button
    if (pagination.current_page < pagination.last_page) {
        paginationHTML += `<li class="page-item"><a class="page-link" href="#" onclick="loadNotifications(${pagination.current_page + 1})">Next</a></li>`;
    }
    
    paginationHTML += '</ul>';
    container.innerHTML = paginationHTML;
}

function showToast(message, type = 'info') {
    // Create toast if it doesn't exist
    let toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toastContainer';
        toastContainer.className = 'toast-container position-fixed top-0 end-0 p-3';
        toastContainer.style.zIndex = '1055';
        document.body.appendChild(toastContainer);
    }
    
    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : type === 'error' ? 'danger' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;
    
    toastContainer.appendChild(toast);
    
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}
</script>
@endsection
