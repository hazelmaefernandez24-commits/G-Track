/**
 * GLOBAL ERROR PREVENTION SYSTEM
 * Prevents and handles JavaScript errors across all user types
 */

(function() {
    'use strict';
    
    // Prevent multiple initializations
    if (window.ErrorPreventionInitialized) {
        return;
    }
    window.ErrorPreventionInitialized = true;

    console.log('🛡️ Initializing Global Error Prevention System...');

    // Global error handler
    window.addEventListener('error', function(event) {
        console.error('🚨 Global JavaScript Error:', {
            message: event.message,
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno,
            error: event.error
        });
        
        // Send error to server for logging (temporarily disabled to prevent error loops)
        // if (typeof fetch !== 'undefined') {
        //     fetch('/api/log-error', {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        //         },
        //         body: JSON.stringify({
        //             type: 'javascript_error',
        //             message: event.message,
        //             filename: event.filename,
        //             line: event.lineno,
        //             column: event.colno,
        //             stack: event.error?.stack,
        //             url: window.location.href,
        //             user_agent: navigator.userAgent
        //         })
        //     }).catch(err => console.warn('Failed to log error to server:', err));
        // }
    });

    // Global unhandled promise rejection handler
    window.addEventListener('unhandledrejection', function(event) {
        console.error('🚨 Unhandled Promise Rejection:', event.reason);
        
        // Send to server (temporarily disabled to prevent error loops)
        // if (typeof fetch !== 'undefined') {
        //     fetch('/api/log-error', {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        //         },
        //         body: JSON.stringify({
        //             type: 'promise_rejection',
        //             message: event.reason?.message || 'Unhandled promise rejection',
        //             stack: event.reason?.stack,
        //             url: window.location.href
        //         })
        //     }).catch(err => console.warn('Failed to log promise rejection to server:', err));
        // }
    });

    // Safe API call wrapper
    window.safeApiCall = function(url, options = {}) {
        const defaultOptions = {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json'
            }
        };

        const mergedOptions = {
            ...defaultOptions,
            ...options,
            headers: { ...defaultOptions.headers, ...options.headers }
        };

        return fetch(url, mergedOptions)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success === false) {
                    throw new Error(data.message || 'API call failed');
                }
                return data;
            })
            .catch(error => {
                console.error('🚨 API Call Failed:', {
                    url: url,
                    error: error.message,
                    options: mergedOptions
                });
                throw error;
            });
    };

    // Safe DOM manipulation
    window.safeQuerySelector = function(selector) {
        try {
            return document.querySelector(selector);
        } catch (error) {
            console.warn('🚨 Invalid selector:', selector, error);
            return null;
        }
    };

    window.safeQuerySelectorAll = function(selector) {
        try {
            return document.querySelectorAll(selector);
        } catch (error) {
            console.warn('🚨 Invalid selector:', selector, error);
            return [];
        }
    };

    // Safe event listener
    window.safeAddEventListener = function(element, event, handler) {
        if (!element) {
            console.warn('🚨 Cannot add event listener: element is null');
            return;
        }
        
        if (typeof handler !== 'function') {
            console.warn('🚨 Cannot add event listener: handler is not a function');
            return;
        }

        try {
            element.addEventListener(event, function(e) {
                try {
                    handler(e);
                } catch (error) {
                    console.error('🚨 Event handler error:', {
                        event: event,
                        error: error.message,
                        stack: error.stack
                    });
                }
            });
        } catch (error) {
            console.error('🚨 Failed to add event listener:', error);
        }
    };

    // Safe modal operations
    window.safeShowModal = function(modalId) {
        const modal = safeQuerySelector('#' + modalId);
        if (!modal) {
            console.warn('🚨 Modal not found:', modalId);
            return false;
        }

        try {
            // Use the simple modal approach we implemented
            if (typeof showModalSimple === 'function') {
                showModalSimple(modalId);
                return true;
            }
            
            // Fallback to Bootstrap if available
            if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                const bsModal = new bootstrap.Modal(modal);
                bsModal.show();
                return true;
            }
            
            console.warn('🚨 No modal system available');
            return false;
            
        } catch (error) {
            console.error('🚨 Failed to show modal:', modalId, error);
            return false;
        }
    };

    // Safe form submission
    window.safeSubmitForm = function(form, onSuccess, onError) {
        if (!form) {
            console.warn('🚨 Cannot submit: form is null');
            return;
        }

        const formData = new FormData(form);
        const url = form.action || window.location.href;
        const method = form.method || 'POST';

        safeApiCall(url, {
            method: method,
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            }
        })
        .then(data => {
            if (typeof onSuccess === 'function') {
                onSuccess(data);
            }
        })
        .catch(error => {
            if (typeof onError === 'function') {
                onError(error);
            } else {
                console.error('🚨 Form submission failed:', error);
            }
        });
    };

    // Safe local storage operations
    window.safeLocalStorage = {
        get: function(key) {
            try {
                return localStorage.getItem(key);
            } catch (error) {
                console.warn('🚨 LocalStorage get failed:', key, error);
                return null;
            }
        },
        set: function(key, value) {
            try {
                localStorage.setItem(key, value);
                return true;
            } catch (error) {
                console.warn('🚨 LocalStorage set failed:', key, error);
                return false;
            }
        },
        remove: function(key) {
            try {
                localStorage.removeItem(key);
                return true;
            } catch (error) {
                console.warn('🚨 LocalStorage remove failed:', key, error);
                return false;
            }
        }
    };

    // System health check function
    window.checkSystemHealth = function() {
        console.log('🔍 Running client-side system health check...');
        
        const checks = {
            jquery: typeof $ !== 'undefined',
            bootstrap: typeof bootstrap !== 'undefined',
            csrf_token: !!document.querySelector('meta[name="csrf-token"]'),
            local_storage: typeof Storage !== 'undefined',
            fetch_api: typeof fetch !== 'undefined'
        };

        console.log('📊 System Health Results:', checks);
        return checks;
    };

    // Auto-run health check on load
    document.addEventListener('DOMContentLoaded', function() {
        checkSystemHealth();
    });

    // Make functions available globally
    window.ErrorPrevention = {
        safeApiCall: window.safeApiCall,
        safeQuerySelector: window.safeQuerySelector,
        safeQuerySelectorAll: window.safeQuerySelectorAll,
        safeAddEventListener: window.safeAddEventListener,
        safeShowModal: window.safeShowModal,
        safeSubmitForm: window.safeSubmitForm,
        safeLocalStorage: window.safeLocalStorage,
        checkSystemHealth: window.checkSystemHealth
    };

    console.log('✅ Global Error Prevention System initialized successfully');
    console.log('💡 Available functions: window.ErrorPrevention.*');

})();
