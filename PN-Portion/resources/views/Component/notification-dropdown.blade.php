<!-- Feature-Based Notification System -->
<style>
.feature-notification-dot {
    width: 8px;
    height: 8px;
    background: #dc3545;
    border-radius: 50%;
    display: inline-block;
    margin-left: 8px;
    /* REMOVED: animation: pulse 2s infinite; */
    /* REMOVED: box-shadow: 0 0 3px rgba(220, 53, 69, 0.3); */
    border: 1px solid white;
    position: relative;
}

.feature-notification-dot.new {
    width: 8px;
    height: 8px;
    background: #dc3545;
    /* REMOVED: animation: newNotificationPulse 1.5s infinite; */
    /* REMOVED: box-shadow: 0 0 4px rgba(255, 153, 51, 0.4); */
}

.feature-notification-dot.urgent {
    background: #dc3545;
    /* REMOVED: animation: urgentPulse 1s infinite; */
    /* REMOVED: box-shadow: 0 0 5px rgba(220, 53, 69, 0.5); */
}

/* REMOVED: All pulse animations disabled
@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

@keyframes newNotificationPulse {
    0% { opacity: 1; }
    50% { opacity: 0.8; }
    100% { opacity: 1; }
}

@keyframes urgentPulse {
    0% { opacity: 1; }
    50% { opacity: 0.6; }
    100% { opacity: 1; }
}
*/

.nav-link {
    position: relative;
}

.notification-popup {
    position: fixed;
    top: 90px; /* Below the header (70px height + 20px margin) */
    right: 20px;
    background: #ffffff;
    border: 2px solid #22bbea;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 12px 40px rgba(0,0,0,0.15);
    z-index: 1040; /* Higher than header but below modals */
    max-width: 380px;
    min-width: 320px;
    display: none;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(34, 187, 234, 0.3);
}

/* REMOVED: No special popup highlighting
.notification-popup.new {
    border: 2px solid #ff9933;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}

.notification-popup.urgent {
    border: 2px solid #dc3545;
    box-shadow: 0 8px 24px rgba(0,0,0,0.15);
}
*/

.notification-popup.show {
    display: block;
    animation: slideInBounce 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes slideInBounce {
    0% {
        transform: translateX(100%) scale(0.8);
        opacity: 0;
    }
    60% {
        transform: translateX(-10px) scale(1.05);
        opacity: 0.9;
    }
    100% {
        transform: translateX(0) scale(1);
        opacity: 1;
    }
}



.notification-popup .close-btn {
    position: absolute;
    top: 8px;
    right: 12px;
    background: rgba(255, 255, 255, 0.9);
    border: 1px solid #ddd;
    border-radius: 50%;
    width: 28px;
    height: 28px;
    font-size: 16px;
    cursor: pointer;
    color: #666;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    z-index: 10;
}

.notification-popup .close-btn:hover {
    background: #ff4757;
    color: white;
    border-color: #ff4757;
    transform: scale(1.1);
}

/* Enhanced notification content styling */
.notification-popup .notification-content {
    padding-right: 35px; /* Space for close button */
}

.notification-popup .notification-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
    font-size: 16px;
}

.notification-popup .notification-message {
    color: #666;
    line-height: 1.4;
    margin-bottom: 10px;
    font-size: 14px;
}

.notification-popup .notification-time {
    color: #999;
    font-size: 12px;
    font-style: italic;
}

.notification-popup .notification-icon {
    margin-right: 12px;
    font-size: 24px;
    color: #22bbea;
}

/* Enhanced notification backdrop */
.notification-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.02); /* Very subtle */
    z-index: 1039; /* Just below notification popup */
    display: none;
    transition: opacity 0.3s ease;
}

.notification-backdrop.show {
    display: block;
    animation: fadeInBackdrop 0.3s ease-out;
}

@keyframes fadeInBackdrop {
    from { opacity: 0; }
    to { opacity: 1; }
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .notification-popup {
        top: 80px;
        left: 10px;
        right: 10px;
        max-width: none;
        min-width: auto;
    }

    .notification-popup .notification-title {
        font-size: 15px;
    }

    .notification-popup .notification-message {
        font-size: 13px;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .notification-popup {
        border: 3px solid #000;
        box-shadow: 0 8px 32px rgba(0,0,0,0.5);
    }

    .notification-popup .notification-title {
        color: #000;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .notification-popup.show {
        animation: none;
    }

    .notification-popup.show::before {
        animation: none;
    }

    .notification-popup .close-btn:hover {
        transform: none;
    }
}

/* REMOVED: New Notification Item Highlighting - All highlighting disabled
.notification-item-new {
    background: #fff3cd !important;
    border-left: 4px solid #ff9933 !important;
    box-shadow: 0 1px 4px rgba(255, 153, 51, 0.2) !important;
    position: relative;
}

.notification-item-new::before {
    content: '🆕 NEW';
    position: absolute;
    top: 8px;
    right: 8px;
    background: #ff9933;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: bold;
    z-index: 10;
    animation: newBadgePulse 2s infinite;
}

.notification-item-urgent {
    background: #f8d7da !important;
    border-left: 4px solid #dc3545 !important;
    box-shadow: 0 1px 4px rgba(220, 53, 69, 0.2) !important;
    position: relative;
}

.notification-item-urgent::before {
    content: '🚨 URGENT';
    position: absolute;
    top: 8px;
    right: 8px;
    background: #dc3545;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: bold;
    z-index: 10;
    animation: urgentBadgePulse 1s infinite;
}
*/

/* REMOVED: All badge pulse and fade animations disabled
@keyframes newBadgePulse {
    0% { opacity: 1; }
    50% { opacity: 0.8; }
    100% { opacity: 1; }
}

@keyframes urgentBadgePulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}

.notification-item-new.fade-highlight {
    animation: fadeToNormal 2s ease-out forwards;
}

.notification-item-urgent.fade-highlight {
    animation: fadeToNormal 2s ease-out forwards;
}

@keyframes fadeToNormal {
    0% {
        background: #fff3cd;
        border-left-color: #ff9933;
        box-shadow: 0 1px 4px rgba(255, 153, 51, 0.2);
    }
    100% {
        background: transparent;
        border-left-color: transparent;
        box-shadow: none;
    }
}
*/
</style>

<!-- Hidden notification backdrop -->
<div id="notificationBackdrop" class="notification-backdrop" onclick="closeNotificationPopup()"></div>

<!-- Hidden notification popup -->
<div id="notificationPopup" class="notification-popup">
    <button class="close-btn" onclick="closeNotificationPopup()" title="Close notification">&times;</button>
    <div id="notificationContent"></div>
</div>




<script>
// Notification System - Singleton Pattern to Prevent Duplicates
(function() {
    'use strict';

    // Check if notification system is already loaded
    if (window.NotificationSystem) {
        console.log('🔄 Notification system already loaded, skipping...');
        return;
    }

    console.log('🚀 Initializing notification system...');

    // Create namespace
    window.NotificationSystem = {
        initialized: false,

        // Feature mapping for different user roles
        FEATURE_NOTIFICATION_MAP: {
            'cook': {
                'cook.inventory': ['inventory_report', 'low_stock'],
                'cook.feedback': ['feedback_submitted'],
                'cook.post-assessment': ['post_meal_report'],
                'cook.pre-orders': ['poll_response']
            },
            'kitchen': {
                'kitchen.daily-menu': ['menu_update'],
                'kitchen.inventory': ['inventory_approved', 'low_stock'],
                'kitchen.feedback': ['feedback_submitted'],
                'kitchen.pre-orders': ['poll_response']
            },
            'student': {
                'student.menu': ['menu_update'],
                'student.pre-order': ['poll_created'],
                'student.feedback': ['system_update']
            }
        },

        // Initialize the system - HIGHLIGHTS DISABLED
        init: function() {
            if (this.initialized) {
                console.log('🔄 Notification system already initialized');
                return;
            }

            // Keep basic notifications but disable highlighting
            this.loadFeatureNotifications();
            // DISABLED: this.highlightNewNotificationItems();

            // Auto-refresh every 30 seconds (but no highlighting)
            setInterval(() => {
                this.loadFeatureNotifications();
                // DISABLED: this.highlightNewNotificationItems();
            }, 30000);

            this.initialized = true;
            console.log('✅ Notification system initialized - HIGHLIGHTS DISABLED');
        },

        // Load feature notifications
        loadFeatureNotifications: function() {
            console.log(`🔄 Loading feature notifications...`);

            fetch('/notifications/feature-status')
                .then(response => {
                    console.log(`📡 Feature status response:`, response.status);
                    return response.json();
                })
                .then(data => {
                    console.log(`📊 Feature status data:`, data);

                    if (data.success) {
                        this.updateFeatureNotifications(data.features, data.new_notifications || []);

                        // Show popup for new notifications
                        if (data.new_notifications && data.new_notifications.length > 0) {
                            console.log(`🔔 Showing popup for ${data.new_notifications.length} new notifications`);
                            this.showNotificationPopup(data.new_notifications);
                        }
                    } else {
                        console.error('❌ Feature status request failed:', data);
                    }
                })
                .catch(error => {
                    console.error('❌ Error loading feature notifications:', error);
                });
        },

        // Update feature notifications with enhanced highlighting
        updateFeatureNotifications: function(features, newNotifications = []) {
            console.log(`🔔 Updating feature notifications:`, features);
            console.log(`🆕 New notifications:`, newNotifications);

            // Remove all existing notification dots
            const existingDots = document.querySelectorAll('.feature-notification-dot');
            console.log(`🗑️ Removing ${existingDots.length} existing dots`);
            existingDots.forEach(dot => dot.remove());

            // Analyze new notifications to determine urgency and newness
            const routeAnalysis = {};
            newNotifications.forEach(notification => {
                const createdAt = new Date(notification.created_at);
                const now = new Date();
                const ageInMinutes = (now - createdAt) / (1000 * 60);

                // Find which route this notification belongs to
                const userRole = this.getCurrentUserRole();
                const featureMap = this.FEATURE_NOTIFICATION_MAP[userRole] || {};

                for (const [route, types] of Object.entries(featureMap)) {
                    if (types.includes(notification.type)) {
                        if (!routeAnalysis[route]) {
                            routeAnalysis[route] = { isNew: false, isUrgent: false, count: 0 };
                        }

                        routeAnalysis[route].count++;

                        // Mark as new if created within last 2 minutes
                        if (ageInMinutes <= 2) {
                            routeAnalysis[route].isNew = true;
                        }

                        // Mark as urgent based on notification type or age
                        if (notification.type.includes('urgent') ||
                            notification.type === 'poll_response' ||
                            notification.type === 'inventory_report' ||
                            ageInMinutes <= 0.5) { // Very recent notifications
                            routeAnalysis[route].isUrgent = true;
                        }
                        break;
                    }
                }
            });

            // Add notification dots to features that have unread notifications
            Object.keys(features).forEach(route => {
                console.log(`📊 Checking route: ${route}, count: ${features[route]}`);
                if (features[route] > 0) {
                    const analysis = routeAnalysis[route] || { isNew: false, isUrgent: false };
                    console.log(`➕ Adding dot for route: ${route}`, analysis);
                    this.addNotificationDot(route, analysis.isNew, analysis.isUrgent);
                }
            });
        },

        // Add notification dot with enhanced highlighting
        addNotificationDot: function(route, isNew = false, isUrgent = false) {
            // Find the nav link for this route using data-feature attribute
            const navLink = document.querySelector(`a[data-feature="${route}"]`);

            console.log(`🔍 Looking for notification dot target:`, {
                route: route,
                navLink: navLink,
                selector: `a[data-feature="${route}"]`,
                isNew: isNew,
                isUrgent: isUrgent
            });

            if (navLink) {
                // Remove existing dot if present
                const existingDot = navLink.querySelector('.feature-notification-dot');
                if (existingDot) {
                    existingDot.remove();
                }

                // Create new dot with basic styling (no highlighting effects)
                const dot = document.createElement('span');
                let dotClass = 'feature-notification-dot';
                let title = 'New notifications';

                // REMOVED: No special styling for urgent/new - all dots look the same
                // if (isUrgent) {
                //     dotClass += ' urgent';
                //     title = 'Urgent notifications!';
                // } else if (isNew) {
                //     dotClass += ' new';
                //     title = 'New notifications just arrived!';
                // }

                dot.className = dotClass;
                dot.title = title;
                navLink.appendChild(dot);

                console.log(`✅ Added notification dot for route: ${route} (highlighting disabled)`);

                // Add click handler to mark as read when feature is accessed
                const self = this;
                navLink.addEventListener('click', function() {
                    self.markFeatureAsRead(route);
                });

                // REMOVED: No highlighting effects or fading
                // All dots remain static without animations
            } else {
                console.log(`❌ Could not add dot for route: ${route}`, {
                    navLinkExists: !!navLink
                });
            }
        },

        // Mark feature as read
        markFeatureAsRead: function(route) {
            console.log(`🔄 Marking feature as read: ${route}`);

            fetch('/notifications/mark-feature-read', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ route: route })
            })
            .then(response => response.json())
            .then(data => {
                console.log(`📝 Mark as read response:`, data);

                if (data.success) {
                    // Remove notification dot using data-feature attribute
                    const navLink = document.querySelector(`a[data-feature="${route}"]`);
                    if (navLink) {
                        const dot = navLink.querySelector('.feature-notification-dot');
                        if (dot) {
                            dot.remove();
                            console.log(`✅ Removed notification dot for: ${route}`);
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Error marking feature as read:', error);
            });
        },

        // Get current user role from URL or meta tag
        getCurrentUserRole: function() {
            // Try to get from meta tag first
            const metaRole = document.querySelector('meta[name="user-role"]');
            if (metaRole) {
                return metaRole.getAttribute('content');
            }

            // Fallback: detect from URL
            const path = window.location.pathname;
            if (path.includes('/cook/')) return 'cook';
            if (path.includes('/kitchen/')) return 'kitchen';
            if (path.includes('/student/')) return 'student';

            // Default fallback
            return 'student';
        },

        // Show notification popup with enhanced styling
        showNotificationPopup: function(notifications) {
            const popup = document.getElementById('notificationPopup');
            const content = document.getElementById('notificationContent');

            console.log(`🔔 Showing notification popup for ${notifications.length} notifications`);

            // REMOVED: Urgency level detection - no special highlighting
            // const hasUrgent = notifications.some(n =>
            //     n.type.includes('urgent') ||
            //     n.type === 'poll_response' ||
            //     n.type === 'inventory_report'
            // );

            // const isVeryNew = notifications.some(n => {
            //     const ageInMinutes = (new Date() - new Date(n.created_at)) / (1000 * 60);
            //     return ageInMinutes <= 0.5;
            // });

            // Apply basic popup styling (no urgency highlighting)
            popup.className = 'notification-popup show';
            // REMOVED: No special urgent or new styling
            // if (hasUrgent) {
            //     popup.classList.add('urgent');
            // } else if (isVeryNew) {
            //     popup.classList.add('new');
            // }

            if (notifications.length === 1) {
                const notification = notifications[0];
                // REMOVED: No urgency badges or special highlighting

                content.innerHTML = `
                    <div class="notification-content d-flex align-items-start">
                        <i class="bi ${this.getNotificationIcon(notification.type)} notification-icon"
                           style="color: #22bbea; font-size: 24px;"></i>
                        <div class="flex-grow-1">
                            <div class="notification-title">
                                ${notification.title}
                            </div>
                            <div class="notification-message">${notification.message}</div>
                            <div class="notification-time">${this.formatTimeAgo(notification.created_at)}</div>
                        </div>
                    </div>
                `;
            } else {
                // REMOVED: No urgency icons or special highlighting
                const notificationText = 'New Notifications';

                content.innerHTML = `
                    <div class="notification-content d-flex align-items-start">
                        <i class="bi bi-bell-fill notification-icon"
                           style="color: #22bbea; font-size: 28px;"></i>
                        <div class="flex-grow-1">
                            <div class="notification-title">
                                📢 ${notificationText}
                            </div>
                            <div class="notification-message">You have ${notifications.length} new notifications waiting for you!</div>
                            <div class="notification-time">Just now</div>
                        </div>
                    </div>
                `;
            }

            // Add sound effect (optional)
            try {
                // Create a subtle notification sound
                const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                const oscillator = audioContext.createOscillator();
                const gainNode = audioContext.createGain();

                oscillator.connect(gainNode);
                gainNode.connect(audioContext.destination);

                oscillator.frequency.setValueAtTime(800, audioContext.currentTime);
                oscillator.frequency.setValueAtTime(600, audioContext.currentTime + 0.1);

                gainNode.gain.setValueAtTime(0.1, audioContext.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.2);

                oscillator.start(audioContext.currentTime);
                oscillator.stop(audioContext.currentTime + 0.2);
            } catch (e) {
                // Ignore audio errors
                console.log('Audio notification not available');
            }

            // Show backdrop and popup
            const backdrop = document.getElementById('notificationBackdrop');
            backdrop.classList.add('show');
            popup.classList.add('show');

            console.log(`✅ Notification popup displayed with backdrop`);

            // Auto-hide after 6 seconds (increased from 5)
            const self = this;
            setTimeout(() => {
                self.closeNotificationPopup();
            }, 6000);
        },

        // Highlight new notification items - DISABLED
        highlightNewNotificationItems: function() {
            console.log('🚫 Item highlighting disabled - no visual highlights will be applied');

            // DISABLED: All highlighting functionality removed
            // Items will appear normally without any special highlighting
            return;

            /* DISABLED CODE:
            // Get current timestamp for comparison
            const now = new Date();
            const twoMinutesAgo = new Date(now.getTime() - 2 * 60 * 1000);
            const thirtySecondsAgo = new Date(now.getTime() - 30 * 1000);

            // Define selectors for different notification item types
            const itemSelectors = [
                // Feedback items
                '[data-feedback-created]',
                '[data-created-at]',
                '[data-timestamp]',
                // Poll items
                '[data-poll-created]',
                // Inventory items
                '[data-inventory-created]',
                // Post-assessment items
                '[data-assessment-created]',
                // Generic notification items
                '.notification-item',
                '.feedback-item',
                '.poll-item',
                '.assessment-item'
            ];
            */

            // DISABLED: All highlighting code removed
            */
        },

        // Close notification popup
        closeNotificationPopup: function() {
            const popup = document.getElementById('notificationPopup');
            const backdrop = document.getElementById('notificationBackdrop');

            console.log(`🔄 Closing notification popup`);

            // Add closing animation
            popup.style.animation = 'slideOutBounce 0.3s ease-in forwards';
            backdrop.style.animation = 'fadeOutBackdrop 0.3s ease-out forwards';

            setTimeout(() => {
                popup.classList.remove('show');
                backdrop.classList.remove('show');
                popup.style.animation = '';
                backdrop.style.animation = '';
                console.log(`✅ Notification popup closed`);
            }, 300);
        },

        // Inject additional styles
        injectStyles: function() {
            if (window.notificationStylesInjected) return;

            const additionalStyles = `
            @keyframes slideOutBounce {
                0% { transform: translateX(0) scale(1); opacity: 1; }
                100% { transform: translateX(100%) scale(0.8); opacity: 0; }
            }

            @keyframes fadeOutBackdrop {
                from { opacity: 1; }
                to { opacity: 0; }
            }
            `;

            // Inject additional styles
            const styleSheet = document.createElement('style');
            styleSheet.textContent = additionalStyles;
            document.head.appendChild(styleSheet);

            // Mark as injected to prevent duplicates
            window.notificationStylesInjected = true;
        },

        // Get notification icon
        getNotificationIcon: function(type) {
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
        },

        // Format time ago
        formatTimeAgo: function(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffInSeconds = Math.floor((now - date) / 1000);

            if (diffInSeconds < 60) return 'Just now';
            if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)}m ago`;
            if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)}h ago`;

            return date.toLocaleDateString();
        }
    };

    // Inject styles and initialize
    window.NotificationSystem.injectStyles();

    // Initialize when DOM is ready
    document.addEventListener('DOMContentLoaded', function() {
        window.NotificationSystem.init();
    });

    // Create global functions for backward compatibility
    window.initializeFeatureNotifications = function() { window.NotificationSystem.init(); };
    window.loadFeatureNotifications = function() { window.NotificationSystem.loadFeatureNotifications(); };
    window.updateFeatureNotifications = function(features) { window.NotificationSystem.updateFeatureNotifications(features); };
    window.addNotificationDot = function(route) { window.NotificationSystem.addNotificationDot(route); };
    window.markFeatureAsRead = function(route) { window.NotificationSystem.markFeatureAsRead(route); };
    window.showNotificationPopup = function(notifications) { window.NotificationSystem.showNotificationPopup(notifications); };
    window.closeNotificationPopup = function() { window.NotificationSystem.closeNotificationPopup(); };
    window.getNotificationIcon = function(type) { return window.NotificationSystem.getNotificationIcon(type); };
    window.formatTimeAgo = function(dateString) { return window.NotificationSystem.formatTimeAgo(dateString); };

})();

</script>
