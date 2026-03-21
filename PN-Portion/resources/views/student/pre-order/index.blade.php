@extends('layouts.app')

@section('title', 'Pre-Select Meals')

@section('content')
<div class="container-fluid">
    <!-- Enhanced Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h3 class="mb-1 fw-bold">
                            <i class="bi bi-clipboard-check me-2"></i>Kitchen Menu Polls
                        </h3>
                        <p class="mb-0 opacity-75">Respond to kitchen polls to help plan meal preparation</p>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kitchen Menu Polls Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-warning">
               
                <div class="card-body">
                   
                    <div id="kitchenPollsContainer">
                        <div class="text-center py-3">
                            <div class="spinner-border text-warning" role="status">
                                <span class="visually-hidden">Loading polls...</span>
                            </div>
                            <p class="mt-2 text-muted">Loading kitchen polls...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    

<!-- Kitchen Poll Response Modal -->
<div class="modal fade" id="kitchenPollModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Respond to Kitchen Poll</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="pollDetails"></div>
                <form id="kitchenPollForm">
                    <input type="hidden" id="pollId" name="poll_id">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Will you eat this meal?</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="will_eat" value="1" id="willEatYes">
                                <label class="form-check-label text-success" for="willEatYes">
                                    <i class="bi bi-check-circle"></i> Yes, I will eat
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="will_eat" value="0" id="willEatNo">
                                <label class="form-check-label text-danger" for="willEatNo">
                                    <i class="bi bi-x-circle"></i> No, I won't eat
                                </label>
                            </div>
                        </div>
                    </div>

                  
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitKitchenPollResponse()">
                    <i class="bi bi-send"></i> Submit Response
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form').forEach(function(form) {
        if (form.action && form.action.includes('/pre-order')) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
                }
                const formData = new FormData(form);
                fetch(form.action, {
                    method: form.method || 'POST',
                    body: formData,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pre-order submitted successfully!');
                        form.reset();
                        location.reload();
                    } else {
                        alert(data.message || 'Failed to submit pre-order');
                    }
                })
                .catch(error => {
                    alert('An error occurred while submitting pre-order');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = 'Submit';
                    }
                });
            });
        }
    });

    // Load kitchen polls on page load
    loadKitchenPolls();

    // Auto-refresh kitchen polls every 30 seconds
    setInterval(loadKitchenPolls, 30000);
});

// Kitchen Polls Functions
let currentKitchenPolls = [];

// SIMPLE MODAL FUNCTIONS - NO BOOTSTRAP DEPENDENCY
function showModalSimple(modalId) {
    const modalElement = document.getElementById(modalId);
    if (!modalElement) return;

    // Clean up any existing stuff
    document.querySelectorAll('.modal-backdrop').forEach(el => el.remove());
    document.body.style.overflow = 'hidden';

    // Show modal manually
    modalElement.style.cssText = `
        display: block !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        z-index: 999999 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
        pointer-events: auto !important;
    `;

    modalElement.classList.add('show');

    // Style the dialog
    const modalDialog = modalElement.querySelector('.modal-dialog');
    if (modalDialog) {
        modalDialog.style.cssText = `
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            z-index: 1000000 !important;
            pointer-events: auto !important;
            margin: 0 !important;
        `;
    }

    // Ensure content is clickable
    const modalContent = modalElement.querySelector('.modal-content');
    if (modalContent) {
        modalContent.style.cssText = `
            pointer-events: auto !important;
            z-index: 1000001 !important;
            background: white !important;
            border-radius: 12px !important;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3) !important;
        `;
    }

    // Make all inputs clickable
    modalElement.querySelectorAll('input, textarea, button, select').forEach(el => {
        el.style.pointerEvents = 'auto';
    });

    // Close on backdrop click
    modalElement.onclick = function(e) {
        if (e.target === modalElement) {
            hideModalSimple(modalId);
        }
    };

    // Close button functionality
    modalElement.querySelectorAll('[data-bs-dismiss="modal"], .btn-close').forEach(btn => {
        btn.onclick = function() {
            hideModalSimple(modalId);
        };
    });
}

function hideModalSimple(modalId) {
    const modalElement = document.getElementById(modalId);
    if (modalElement) {
        modalElement.style.display = 'none';
        modalElement.classList.remove('show');
        document.body.style.overflow = '';
    }
}

// Enhanced refresh function with visual feedback
function refreshKitchenPolls() {
    const refreshBtn = document.getElementById('refreshPollsBtn');
    const refreshIcon = document.getElementById('refreshIcon');

    // Mark as manual refresh for success feedback
    window.isManualRefresh = true;

    // Show loading state
    refreshBtn.disabled = true;
    refreshIcon.className = 'bi bi-arrow-clockwise spin';
    refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise spin"></i> Refreshing...';

    // Call the main load function
    loadKitchenPolls().finally(() => {
        // Reset button state with success animation
        setTimeout(() => {
            refreshBtn.disabled = false;
            refreshIcon.className = 'bi bi-arrow-clockwise';
            refreshBtn.innerHTML = '<i class="bi bi-arrow-clockwise"></i> Refresh';

            // Add success pulse animation
            refreshBtn.classList.add('refresh-success');
            setTimeout(() => {
                refreshBtn.classList.remove('refresh-success');
            }, 500);
        }, 500); // Small delay to show the refresh completed
    });
}

function loadKitchenPolls() {
    console.log('🔄 Loading kitchen polls...');

    return fetch('/student/polls/kitchen', {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => {
        console.log('🔧 Response status:', response.status, response.statusText);

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }

        // Check if response is JSON
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            console.error('❌ Response is not JSON, content-type:', contentType);
            throw new Error('Server returned non-JSON response (likely an error page)');
        }

        return response.json();
    })
    .then(data => {
        console.log('📦 Kitchen polls data:', data);

        if (data.success) {
            currentKitchenPolls = data.polls || [];
            displayKitchenPolls(currentKitchenPolls);

            // Update last refresh time
            updateLastRefreshTime();

            // Show success feedback for manual refresh
            if (window.isManualRefresh) {
                showToast('Polls refreshed successfully!', 'success');
                window.isManualRefresh = false;
            }
        } else {
            console.error('❌ API returned error:', data);
            showKitchenPollsError('Failed to load polls: ' + (data.message || 'Unknown error'));

            // Show debug info if available
            if (data.debug) {
                console.error('🔍 Debug info:', data.debug);
            }
        }
    })
    .catch(error => {
        console.error('💥 Error loading kitchen polls:', error);
        showKitchenPollsError('Error loading polls: ' + error.message);
    });
}

function displayKitchenPolls(polls) {
    const container = document.getElementById('kitchenPollsContainer');
    if (!polls || polls.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="bi bi-inbox display-6 text-muted"></i>
                <h6 class="mt-3 text-muted">No Active Kitchen Polls</h6>
                <p class="text-muted">There are no menu polls available at the moment.</p>
            </div>
        `;
        return;
    }

    // Split polls into pending, finished, expired
    const now = new Date();
    const pending = [];
    const finished = [];
    const expired = [];
    polls.forEach(poll => {
        const deadline = new Date(poll.deadline);
        // Check backend status first, then deadline
        if (poll.status === 'expired' || poll.status === 'finished' || deadline < now) {
            expired.push(poll);
        } else if (poll.has_responded) {
            finished.push(poll);
        } else {
            pending.push(poll);
        }
    });

    let html = '';
    // Pending Polls
    html += `<h5 class="mb-3 text-warning"><i class="bi bi-clock-history me-2"></i>Pending Polls</h5>`;
    html += renderPollSection(pending, 'No pending polls!');
    // Finished Polling
    html += `<h5 class="mb-3 text-success mt-4"><i class="bi bi-check2-circle me-2"></i>Finished Polling</h5>`;
    html += renderPollSection(finished, 'No finished polls!');
    // Expired Polls
    html += `<h5 class="mb-3 text-danger mt-4"><i class="bi bi-x-octagon me-2"></i>Expired Polls</h5>`;
    html += renderPollSection(expired, 'No expired polls!');

    container.innerHTML = html;
}

function renderPollSection(polls, emptyMsg) {
    if (!polls || polls.length === 0) {
        return `<div class='text-center text-muted mb-4'><i class='bi bi-inbox'></i> ${emptyMsg}</div>`;
    }
    let html = '<div class="row">';
    polls.forEach(poll => {
        const deadlineFormatted = formatKitchenPollDeadline(poll.deadline);
        const statusBadge = getKitchenPollStatusBadge(poll);
        html += `
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card h-100 ${poll.has_responded ? 'border-success' : 'border-warning'}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="card-title text-primary">${poll.meal_name}</h6>
                            ${statusBadge}
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="bi bi-calendar"></i> ${formatKitchenPollDate(poll.poll_date)}
                            <span class="badge bg-secondary ms-1">${poll.meal_type}</span>
                        </p>
                        <p class="text-warning small mb-3">
                            <i class="bi bi-clock"></i> Deadline: ${deadlineFormatted}
                        </p>
                        ${getKitchenPollActionButton(poll)}
                    </div>
                </div>
            </div>
        `;
    });
    html += '</div>';
    return html;
}

function getKitchenPollStatusBadge(poll) {
    if (poll.has_responded) {
        const response = poll.response ? 'Will Eat' : 'Won\'t Eat';
        const badgeClass = poll.response ? 'bg-success' : 'bg-danger';
        return `<span class="badge ${badgeClass}"><i class="bi bi-check-circle"></i> ${response}</span>`;
    } else {
        return '<span class="badge bg-warning"><i class="bi bi-clock"></i> Pending</span>';
    }
}

function getKitchenPollActionButton(poll) {
    if (poll.has_responded) {
        return `
            <button class="btn btn-sm btn-outline-primary w-100" onclick="openKitchenPollModal(${poll.id})">
                <i class="bi bi-pencil"></i> Change Response
            </button>
        `;
    } else {
        return `
            <button class="btn btn-sm btn-primary w-100" onclick="openKitchenPollModal(${poll.id})">
                <i class="bi bi-reply"></i> Respond Now
            </button>
        `;
    }
}

function openKitchenPollModal(pollId) {
    const poll = currentKitchenPolls.find(p => p.id === pollId);
    if (!poll) return;

    // Set poll details
    document.getElementById('pollDetails').innerHTML = `
        <div class="alert alert-info">
            <h6><i class="bi bi-info-circle"></i> ${poll.meal_name}</h6>
            <p class="mb-1"><strong>Date:</strong> ${formatKitchenPollDate(poll.poll_date)} (${poll.meal_type})</p>
            <p class="mb-0"><strong>Deadline:</strong> ${formatKitchenPollDeadline(poll.deadline)}</p>
        </div>
    `;

    // Set form values
    document.getElementById('pollId').value = poll.id;

    // Pre-fill if already responded
    if (poll.has_responded) {
        document.getElementById(poll.response ? 'willEatYes' : 'willEatNo').checked = true;
    } else {
        // Clear form
        document.querySelectorAll('input[name="will_eat"]').forEach(input => input.checked = false);
    }

    // Show modal using simple modal function
    showModalSimple('kitchenPollModal');
}

function submitKitchenPollResponse() {
    const form = document.getElementById('kitchenPollForm');
    const formData = new FormData(form);

    const pollId = formData.get('poll_id');
    const willEat = formData.get('will_eat');

    if (!willEat) {
        alert('Please select whether you will eat this meal.');
        return;
    }

    console.log('🔧 Submitting kitchen poll response:', { pollId, willEat });

    fetch(`/student/polls/${pollId}/respond`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            will_eat: willEat === '1'
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('✅ Kitchen poll response submitted:', data);

        if (data.success) {
            // Close modal using simple modal function
            hideModalSimple('kitchenPollModal');

            // Show success message
            alert('Response submitted successfully!');

            // Reload polls
            loadKitchenPolls();
        } else {
            alert(data.message || 'Failed to submit response');
        }
    })
    .catch(error => {
        console.error('💥 Error submitting kitchen poll response:', error);
        alert('Error submitting response: ' + error.message);
    });
}

// Helper functions for kitchen polls
function formatKitchenPollDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatKitchenPollDeadline(deadline) {
    if (!deadline) return 'Not set';

    // If already in 12-hour format, return as-is
    if (deadline.includes('AM') || deadline.includes('PM')) {
        return deadline;
    }

    // Handle different deadline formats
    let timeString = deadline;

    if (deadline.includes(' ')) {
        // Full datetime format: "2025-01-16 21:00:00"
        const [datePart, timePart] = deadline.split(' ');
        timeString = timePart.substring(0, 5); // Get HH:MM
    } else if (deadline.includes(':')) {
        // Time only format: "21:00" or "21:00:00"
        timeString = deadline.substring(0, 5); // Get HH:MM
    }

    // Convert to 12-hour format
    const [hour, minute] = timeString.split(':');
    const h = parseInt(hour);
    const period = h >= 12 ? 'PM' : 'AM';
    const displayHour = h === 0 ? 12 : (h > 12 ? h - 12 : h);

    return `${displayHour}:${minute} ${period}`;
}

function showKitchenPollsError(message) {
    document.getElementById('kitchenPollsContainer').innerHTML = `
        <div class="alert alert-danger">
            <i class="bi bi-exclamation-triangle"></i> ${message}
        </div>
    `;
}

// Toast notification function
function showToast(message, type = 'info') {
    // Remove existing toast if any
    const existingToast = document.getElementById('refreshToast');
    if (existingToast) {
        existingToast.remove();
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.id = 'refreshToast';
    toast.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'primary'} border-0`;
    toast.setAttribute('role', 'alert');
    toast.setAttribute('aria-live', 'assertive');
    toast.setAttribute('aria-atomic', 'true');
    toast.style.position = 'fixed';
    toast.style.top = '20px';
    toast.style.right = '20px';
    toast.style.zIndex = '9999';

    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                <i class="bi bi-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    // Add to page
    document.body.appendChild(toast);

    // Initialize and show toast
    const bsToast = new bootstrap.Toast(toast, {
        autohide: true,
        delay: 3000
    });
    bsToast.show();

    // Remove from DOM after hiding
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}

// Update last refresh time display
function updateLastRefreshTime() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-US', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: true
    });

    const lastRefreshElement = document.getElementById('lastRefreshTime');
    if (lastRefreshElement) {
        lastRefreshElement.textContent = `Last updated: ${timeString}`;
    }
}

function updateDateTime() {
    const now = new Date();
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    const dateString = now.toLocaleDateString('en-US', dateOptions);
    const timeString = now.toLocaleTimeString('en-US', timeOptions);
    const currentDateTimeElement = document.getElementById('currentDateTime');
    if (currentDateTimeElement) {
        currentDateTimeElement.textContent = `${dateString} ${timeString}`;
    }
}
updateDateTime();
setInterval(updateDateTime, 1000);
</script>
@endsection

@push('styles')
<style>
    .table th, .table td {
        vertical-align: middle;
    }

    .card {
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        margin-bottom: 1.5rem;
    }

    /* Spinning animation for refresh button */
    .spin {
        animation: spin 1s linear infinite;
    }

    /* ULTIMATE MODAL FIXES - HIGHEST PRIORITY */
    .modal {
        z-index: 999999 !important;
        position: fixed !important;
    }

    .modal-backdrop {
        z-index: 999998 !important;
        background-color: rgba(0, 0, 0, 0.5) !important;
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        pointer-events: auto !important;
    }

    .modal.show {
        z-index: 999999 !important;
        display: block !important;
    }

    .modal-dialog {
        z-index: 1000000 !important;
        position: relative !important;
        pointer-events: auto !important;
    }

    .modal-content {
        z-index: 1000001 !important;
        position: relative !important;
        pointer-events: auto !important;
    }

    /* Ensure modal is clickable */
    #kitchenPollModal {
        z-index: 999999 !important;
        pointer-events: auto !important;
    }

    #kitchenPollModal .modal-dialog {
        pointer-events: auto !important;
    }

    #kitchenPollModal .modal-content {
        pointer-events: auto !important;
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Enhanced button states */
    #refreshPollsBtn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    /* Success feedback animation */
    .refresh-success {
        animation: pulse 0.5s ease-in-out;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .date-time-block { text-align: center; }
    .date-line { font-size: 1.15rem; font-weight: 500; }
    .time-line { font-size: 1rem; font-family: 'SFMono-Regular', 'Consolas', 'Liberation Mono', monospace; }
</style>
@endpush
