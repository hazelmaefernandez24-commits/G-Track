@extends('layouts.educator')

@section('title', 'Violation Appeals Management')

@section('content')
<div class="container-fluid">
    <!-- Appeals Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-warning text-dark">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0">
                    <i class="fas fa-list me-2"></i>Pending Appeals
                    <span class="badge bg-danger ms-2" id="pendingBadge">1</span>
                </h5>
                <div class="d-flex gap-2">
                    <select id="statusFilter" class="form-select form-select-sm" style="width: auto;">
                        <option value="pending" selected>Pending Appeals</option>
                        <option value="all">All Appeals</option>
                        <option value="approved">Approved Appeals</option>
                        <option value="denied">Denied Appeals</option>
                    </select>
                    <button type="button" class="btn btn-sm btn-outline-dark" onclick="loadAppeals()">
                        <i class="fas fa-refresh"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div id="appealsContainer">
                <div class="text-center py-4">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading appeals...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Appeal Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1" aria-labelledby="reviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reviewModalLabel">Review Appeal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="reviewForm">
                <div class="modal-body">
                    <input type="hidden" id="appealId" name="appeal_id">
                    
                    <div class="mb-3">
                        <label class="form-label"><strong>Student:</strong></label>
                        <p id="studentName" class="text-muted"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Violation:</strong></label>
                        <p id="violationName" class="text-muted"></p>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><strong>Student's Reason:</strong></label>
                        <p id="studentReason" class="text-muted"></p>
                    </div>

                    <div class="mb-3" id="evidenceSection" style="display: none;">
                        <label class="form-label"><strong>Additional Evidence:</strong></label>
                        <p id="additionalEvidence" class="text-muted"></p>
                    </div>

                    <div class="mb-3">
                        <label for="decision" class="form-label">
                            <strong>Decision <span class="text-danger">*</span></strong>
                        </label>
                        <select class="form-select" id="decision" name="decision" required>
                            <option value="">Select Decision</option>
                            <option value="approved">Approve Appeal</option>
                            <option value="denied">Deny Appeal</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="adminResponse" class="form-label">
                            <strong>Admin Response <span class="text-danger">*</span></strong>
                        </label>
                        <textarea 
                            class="form-control" 
                            id="adminResponse" 
                            name="admin_response" 
                            rows="4" 
                            placeholder="Provide a detailed response explaining your decision..."
                            required
                            maxlength="1000"
                        ></textarea>
                        <div class="form-text">
                            <span id="responseCharCount">0</span>/1000 characters
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Decision</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Load appeals on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAppeals();
    loadStats();
});

// Load appeal statistics
function loadStats() {
    fetch('{{ route("educator.appeals.stats") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const pendingBadge = document.getElementById('pendingBadge');
                if (pendingBadge) {
                    pendingBadge.textContent = data.stats.pending;
                }
            }
        })
        .catch(error => console.error('Error loading stats:', error));
}

// Load appeals based on filter
function loadAppeals() {
    const status = document.getElementById('statusFilter').value;
    const container = document.getElementById('appealsContainer');
    
    container.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2">Loading appeals...</p>
        </div>
    `;
    
    fetch(`{{ route("educator.appeals") }}?status=${status}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayAppeals(data.appeals.data);
            } else {
                container.innerHTML = '<div class="alert alert-danger">Error loading appeals</div>';
            }
        })
        .catch(error => {
            console.error('Error loading appeals:', error);
            container.innerHTML = '<div class="alert alert-danger">Error loading appeals</div>';
        });
}

// Display appeals in the container
function displayAppeals(appeals) {
    const container = document.getElementById('appealsContainer');

    if (appeals.length === 0) {
        container.innerHTML = `
            <div class="text-center py-4">
                <i class="fas fa-clipboard-check fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Appeals Found</h5>
                <p class="text-muted">No appeals match the current filter.</p>
            </div>
        `;
        return;
    }

    let html = `
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Violation</th>
                        <th>Appeal Date</th>
                        <th>Reason</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
    `;

    appeals.forEach(appeal => {
        const statusBadge = getStatusBadge(appeal.status);
        const actionButtons = appeal.status === 'pending' ?
            `<div class="btn-group-vertical btn-group-sm">
                <button class="btn btn-success btn-sm mb-1" onclick="reviewAppeal(${appeal.id}, 'approved')">
                    <i class="fas fa-check me-1"></i>Approve
                </button>
                <button class="btn btn-danger btn-sm" onclick="reviewAppeal(${appeal.id}, 'denied')">
                    <i class="fas fa-times me-1"></i>Deny
                </button>
            </div>` :
            `<span class="text-muted small">Reviewed by<br>${appeal.reviewer?.user_fname || 'Unknown'}</span>`;

        const appealDate = new Date(appeal.appeal_date);
        const formattedDate = appealDate.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
        const timeAgo = getTimeAgo(appealDate);

        html += `
            <tr>
                <td>
                    <div>
                        <strong>${appeal.student?.user_fname || 'N/A'} ${appeal.student?.user_lname || ''}</strong>
                        <br>
                        <small class="text-muted">${appeal.student_id || 'N/A'}</small>
                    </div>
                </td>
                <td>
                    <div>
                        <strong>${appeal.violation?.violation_type?.violation_name || 'N/A'}</strong>
                        <br>
                        <small class="text-muted">${appeal.violation?.violation_date ? new Date(appeal.violation.violation_date).toLocaleDateString() : 'N/A'}</small>
                    </div>
                </td>
                <td>
                    <span class="badge bg-info">${formattedDate}</span>
                    <br>
                    <small class="text-muted">${timeAgo}</small>
                </td>
                <td style="max-width: 200px;">
                    <p class="mb-0 text-truncate" title="${appeal.student_reason}">
                        ${appeal.student_reason.substring(0, 50)}${appeal.student_reason.length > 50 ? '...' : ''}
                    </p>
                    ${appeal.student_reason.length > 50 ?
                        `<small><a href="#" class="text-primary" onclick="showFullReason('${appeal.student_reason.replace(/'/g, "\\'")}')">Read more...</a></small>` :
                        ''
                    }
                </td>
                <td>
                    ${actionButtons}
                </td>
            </tr>
        `;
    });

    html += `
                </tbody>
            </table>
        </div>
    `;

    container.innerHTML = html;
}

// Get status badge HTML
function getStatusBadge(status) {
    const badges = {
        'pending': '<span class="badge bg-warning text-dark">Pending</span>',
        'approved': '<span class="badge bg-success">Approved</span>',
        'denied': '<span class="badge bg-danger">Denied</span>'
    };
    return badges[status] || '<span class="badge bg-secondary">Unknown</span>';
}

// Get time ago string
function getTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    return `${Math.floor(diffInSeconds / 2592000)} months ago`;
}

// Show full reason in modal
function showFullReason(reason) {
    alert(reason); // Simple alert for now, can be enhanced with a modal
}

// Quick review function for approve/deny buttons
function reviewAppeal(appealId, decision) {
    const action = decision === 'approved' ? 'approve' : 'deny';
    const message = `Are you sure you want to ${action} this appeal?`;

    if (!confirm(message)) {
        return;
    }

    // For approvals, no reason is required
    let adminResponse = '';
    if (decision === 'approved') {
        adminResponse = 'Appeal approved by educator.';
    } else {
        // Only require reason for denials
        adminResponse = prompt(`Please provide a reason for denying this appeal:`);

        if (!adminResponse || adminResponse.trim().length < 10) {
            alert('Please provide a detailed reason for denial (at least 10 characters).');
            return;
        }
    }

    // Submit the review
    fetch(`/educator/appeals/${appealId}/review`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            decision: decision,
            admin_response: adminResponse
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showSuccessToast(`Appeal ${decision} successfully!`);

            // Immediately update the UI
            updateAppealRowStatus(appealId, decision);

            // Reload data to ensure consistency
            setTimeout(() => {
                loadAppeals();
                loadStats();
            }, 500);
        } else {
            alert('Error: ' + (data.message || 'Failed to review appeal'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while reviewing the appeal.');
    });
}

// Open review modal
function openReviewModal(appealId, studentName, violationName, studentReason, additionalEvidence) {
    document.getElementById('appealId').value = appealId;
    document.getElementById('studentName').textContent = studentName;
    document.getElementById('violationName').textContent = violationName;
    document.getElementById('studentReason').textContent = studentReason;
    
    if (additionalEvidence && additionalEvidence !== 'null' && additionalEvidence.trim() !== '') {
        document.getElementById('additionalEvidence').textContent = additionalEvidence;
        document.getElementById('evidenceSection').style.display = 'block';
    } else {
        document.getElementById('evidenceSection').style.display = 'none';
    }
    
    document.getElementById('decision').value = '';
    document.getElementById('adminResponse').value = '';
    updateResponseCharCount();
    
    const modal = new bootstrap.Modal(document.getElementById('reviewModal'));
    modal.show();
}

// Character count for admin response
document.getElementById('adminResponse').addEventListener('input', updateResponseCharCount);
function updateResponseCharCount() {
    const textarea = document.getElementById('adminResponse');
    const charCount = document.getElementById('responseCharCount');
    charCount.textContent = textarea.value.length;
    
    if (textarea.value.length > 900) {
        charCount.style.color = 'red';
    } else if (textarea.value.length > 800) {
        charCount.style.color = 'orange';
    } else {
        charCount.style.color = 'inherit';
    }
}

// Handle review form submission
document.getElementById('reviewForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const appealId = document.getElementById('appealId').value;
    const decision = document.getElementById('decision').value;
    const adminResponse = document.getElementById('adminResponse').value;
    
    if (!decision || !adminResponse.trim()) {
        alert('Please provide both a decision and response.');
        return;
    }
    
    if (adminResponse.length < 10) {
        alert('Please provide a more detailed response (at least 10 characters).');
        return;
    }
    
    // Submit the review
    fetch(`/educator/appeals/${appealId}/review`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({
            decision: decision,
            admin_response: adminResponse
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Appeal reviewed successfully!');
            bootstrap.Modal.getInstance(document.getElementById('reviewModal')).hide();
            loadAppeals();
            loadStats();
        } else {
            alert('Error: ' + (data.message || 'Failed to review appeal'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while reviewing the appeal.');
    });
});

// Filter change handler
document.getElementById('statusFilter').addEventListener('change', function() {
    const status = this.value;
    const headerTitle = document.querySelector('.card-header h5');
    const badge = document.getElementById('pendingBadge');

    // Update header text based on filter
    switch(status) {
        case 'pending':
            headerTitle.innerHTML = '<i class="fas fa-list me-2"></i>Pending Appeals <span class="badge bg-danger ms-2" id="pendingBadge">1</span>';
            break;
        case 'approved':
            headerTitle.innerHTML = '<i class="fas fa-list me-2"></i>Approved Appeals';
            break;
        case 'denied':
            headerTitle.innerHTML = '<i class="fas fa-list me-2"></i>Denied Appeals';
            break;
        default:
            headerTitle.innerHTML = '<i class="fas fa-list me-2"></i>All Appeals';
    }

    loadAppeals();
});

// Function to immediately update appeal row status in the UI
function updateAppealRowStatus(appealId, decision) {
    const rows = document.querySelectorAll('#appealsTableBody tr');
    rows.forEach(row => {
        const actionCell = row.querySelector('td:last-child');
        if (actionCell && actionCell.innerHTML.includes(`reviewAppeal(${appealId},`)) {
            // Update status badge
            const statusCell = row.cells[3]; // Status column
            const statusBadge = decision === 'approved'
                ? '<span class="badge bg-success">Approved</span>'
                : '<span class="badge bg-danger">Denied</span>';
            statusCell.innerHTML = statusBadge;

            // Update action buttons
            actionCell.innerHTML = '<span class="text-muted small">Just reviewed</span>';

            // Add visual feedback
            row.style.backgroundColor = decision === 'approved' ? '#d4edda' : '#f8d7da';
            setTimeout(() => {
                row.style.backgroundColor = '';
            }, 3000);
        }
    });
}

// Function to show success toast notification
function showSuccessToast(message) {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        toastContainer.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.style.cssText = `
        background: #28a745;
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        display: flex;
        align-items: center;
        min-width: 300px;
        animation: slideInRight 0.3s ease-out;
    `;

    toast.innerHTML = `
        <i class="fas fa-check-circle me-2"></i>
        <span>${message}</span>
    `;

    // Add CSS animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes slideOutRight {
            from { transform: translateX(0); opacity: 1; }
            to { transform: translateX(100%); opacity: 0; }
        }
    `;
    document.head.appendChild(style);

    // Add toast to container
    toastContainer.appendChild(toast);

    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.style.animation = 'slideOutRight 0.3s ease-in';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 4000);
}

</script>
@endsection
