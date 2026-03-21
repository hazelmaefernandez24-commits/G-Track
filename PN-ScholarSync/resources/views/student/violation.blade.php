@extends('layouts.student')

@section('title', 'Student Violation')

@section('css')
<link rel="stylesheet" href="{{ asset('css/student/student-violation.css') }}">
<style>
    /* Basic Styling */
    .no-violations {
        text-align: center;
        padding: 2rem;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin: 1rem 0;
        color: #6c757d;
    }
    
    /* Student Header */
    .student-header {
        background: linear-gradient(135deg, #3a7bd5, #00d2ff);
        padding: 15px 0;
        margin-bottom: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        color: white;
    }
    
    .header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .student-info {
        display: flex;
        align-items: center;
    }
    
    .student-name {
        font-size: 1.5rem;
        font-weight: 600;
        margin: 0;
    }
    
    .student-id, .real-time-display {
        background: rgba(255, 255, 255, 0.2);
        padding: 4px 12px;
        border-radius: 20px;
        font-weight: 500;
        margin-left: 15px;
    }
    
    .real-time-display {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    /* Page Content */
    .page-content {
        margin-top: 20px;
    }
</style>
@endsection

@section('content')

<style>
/* Enhanced Modal Animations */
.modal.fade .modal-dialog {
    transform: scale(0.8) translateY(-50px);
    transition: all 0.3s ease-out;
}

.modal.show .modal-dialog {
    transform: scale(1) translateY(0);
}

/* Custom Modal Styling */
#appealConfirmModal .modal-content {
    border-radius: 1rem;
    overflow: hidden;
}

#appealConfirmModal .modal-header {
    padding: 1.5rem 2rem;
}

#appealConfirmModal .modal-body {
    padding: 2rem;
}

#appealConfirmModal .modal-footer {
    padding: 1.5rem 2rem;
    border-radius: 0 0 1rem 1rem;
}

/* Hover effects for buttons */
#appealConfirmModal .btn {
    transition: all 0.2s ease;
    border-radius: 0.5rem;
}

#appealConfirmModal .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

/* Icon animations */
#appealConfirmModal .fas {
    transition: transform 0.2s ease;
}

#appealConfirmModal .card:hover .fas {
    transform: scale(1.1);
}

/* Gradient background animation */
#appealConfirmModal .modal-header {
    background-size: 200% 200%;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

/* Enhanced shadow effects */
#appealConfirmModal .modal-content {
    box-shadow: 0 20px 60px rgba(0,0,0,0.2);
}

/* Process step cards hover effect */
#appealConfirmModal .col-md-6:hover .bg-opacity-10 {
    transform: scale(1.05);
    transition: transform 0.2s ease;
}

/* Modal entrance and exit animations */
@keyframes slideInDown {
    from {
        transform: translate3d(0, -100%, 0);
        opacity: 0;
    }
    to {
        transform: translate3d(0, 0, 0);
        opacity: 1;
    }
}

@keyframes slideOutUp {
    from {
        transform: translate3d(0, 0, 0);
        opacity: 1;
    }
    to {
        transform: translate3d(0, -100%, 0);
        opacity: 0;
    }
}

/* Button loading state */
#proceedAppealBtn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Smooth transitions for all interactive elements */
#appealConfirmModal * {
    transition: all 0.2s ease;
}

/* Form styling enhancements */
#appealConfirmModal .form-control {
    border-radius: 0.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

#appealConfirmModal .form-control:focus {
    border-color: #ffc107;
    box-shadow: 0 0 0 0.2rem rgba(255, 193, 7, 0.25);
    transform: translateY(-1px);
}

#appealConfirmModal .form-label {
    color: #495057;
    margin-bottom: 0.75rem;
}

/* Character counter styling */
#quickCharCount, #quickEvidenceCharCount {
    font-weight: 600;
    transition: color 0.3s ease;
}

/* Form validation styling */
.form-control.is-invalid {
    border-color: #dc3545;
    animation: shake 0.5s ease-in-out;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}
</style>
@php
    // Safety check: ensure violations variable exists
    if (!isset($violations)) {
        $violations = collect();
    }
@endphp

<!-- Student Header (Educator Style) -->
<div class="d-flex justify-content-between align-items-center mb-4" style="background: linear-gradient(135deg, #3a7bd5, #00d2ff); padding: 18px 32px; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.10); color: #fff;">
    <div style="display: flex; flex-direction: column;">
        <span class="fw-bold" style="font-size: 1.5rem;">{{ auth()->user()->name }}</span>
        @php
            $currentUser = auth()->user();
            $studentDetails = $currentUser->studentDetails;
            $studentId = $studentDetails ? $studentDetails->student_id : null;
        @endphp
        <span style="font-size: 1rem; opacity: 0.9;">ID: {{ $studentId ?? 'N/A' }}</span>
        @php
            // Define penalty hierarchy (higher value = more severe)
            $penaltyPriority = [
                'Exp' => 5,    // Expulsion
                'T'   => 4,    // Termination of Contract
                'Pro' => 3,    // Probation (long code)
                'P'   => 3,    // Probation (short code)
                'WW'  => 2,    // Written Warning
                'W'   => 1,    // Warning
                'VW'  => 0,    // Verbal Warning (long)
                'V'   => 0,    // Verbal Warning (short)
            ];

            // Human-readable labels for each penalty code
            $penaltyLabels = [
                'Exp' => 'Expulsion',
                'T'   => 'Termination of Contract',
                'Pro' => 'Probation',
                'P'   => 'Probation',
                'WW'  => 'Written Warning',
                'W'   => 'Written Warning',
                'VW'  => 'Verbal Warning',
                'V'   => 'Verbal Warning',
            ];

            // Color mapping for quick visual cues
            $penaltyColors = [
                'Exp' => '#e74c3c',   // red
                'T'   => '#c0392b',   // dark red
                'Pro' => '#e67e22',   // orange
                'P'   => '#e67e22',   // orange
                'WW'  => '#f1c40f',   // yellow
                'W'   => '#f1c40f',   // yellow
                'VW'  => '#3498db',   // blue
                'V'   => '#3498db',   // blue
            ];
            // This will be recalculated below based on active violations only
        @endphp
        @php
            // Determine overall student status based on violations
            // Only count violations that are not resolved by approved appeals
            $activeViolations = $violations->filter(function($violation) {
                // Include active violations and violations with denied appeals
                return in_array($violation->status, ['active', 'appealed', 'appeal_denied']);
            });

            // Check for violations resolved by approved appeals
            $resolvedByAppeals = $violations->filter(function($violation) {
                return $violation->status === 'resolved' &&
                       method_exists($violation, 'isResolvedByAppeal') &&
                       $violation->isResolvedByAppeal();
            });

            $hasActiveViolations = $activeViolations->count() > 0;
            $hasPendingAppeals = $violations->where('status', 'appealed')->count() > 0;
            $hasResolvedByAppeals = $resolvedByAppeals->count() > 0;

            // Calculate max penalty only from active violations (not resolved by appeals)
            $maxPenalty = null;
            foreach ($activeViolations as $violation) {
                if (!$maxPenalty || ($penaltyPriority[$violation->penalty] ?? -1) > ($penaltyPriority[$maxPenalty] ?? -1)) {
                    $maxPenalty = $violation->penalty;
                }
            }

            if ($hasActiveViolations) {
                $overallStatus = $maxPenalty ? ($penaltyLabels[$maxPenalty] ?? $maxPenalty) : 'Active Violations';
                $statusColor = 'rgba(220, 53, 69, 0.8)'; // Red for active violations
            } elseif ($hasPendingAppeals) {
                $overallStatus = 'Appeals Under Review';
                $statusColor = 'rgba(255, 193, 7, 0.8)'; // Yellow for pending appeals
            } elseif ($hasResolvedByAppeals && !$hasActiveViolations) {
                $overallStatus = 'Good Standing';
                $statusColor = 'rgba(40, 167, 69, 0.8)'; // Green for good standing
            } else {
                $overallStatus = 'Good Standing';
                $statusColor = 'rgba(40, 167, 69, 0.8)'; // Green for good standing
            }
        @endphp
        <span style="font-size: 1rem; margin-top: 2px; background: {{ $statusColor }}; padding: 4px 12px; border-radius: 20px; color: #fff; font-weight: 600;">
            Status: {{ $overallStatus }}
        </span>
    </div>
    <div class="real-time-display" style="font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-clock"></i> <span id="current-time"></span>
    </div>
</div>

<div class="container">
    <div class="page-content">
        <!-- Page Header -->
        <div class="mb-3">
        <h2>My Violations</h2>
    </div>
        

        
        <!-- Violations List -->
        @if($violations->count() == 0)
            <div class="no-violations">
                <p>No violations found.</p>
            </div>
        @else
            @foreach($violations as $violation)
            <div class="violation-card" id="violation-{{ $violation->id }}" onclick="this.classList.toggle('open')" data-violation-id="{{ $violation->id }}">
                <div class="violation-main">
                    <div class="title">{{ $violation->violation_name }}</div>
                    <div class="date">{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</div>
                    <span class="severity {{ strtolower($violation->severity) }}">{{ $violation->severity }}</span>
                    @if($violation->status === 'active')
                        <span class="badge bg-danger" style="margin-left: 10px;">
                            Active
                        </span>
                    @elseif($violation->status === 'resolved')
                        <span class="badge bg-success" style="margin-left: 10px;">
                            Resolved
                        </span>
                    @elseif($violation->status === 'appealed')
                        <span class="badge bg-info" style="margin-left: 10px;">
                            Under Appeal
                        </span>
                    @elseif($violation->status === 'appeal_approved')
                        <span class="badge bg-success" style="margin-left: 10px;">
                            Appeal Approved
                        </span>
                    @elseif($violation->status === 'appeal_denied')
                        <span class="badge bg-danger" style="margin-left: 10px;">
                            Appeal Denied
                        </span>
                    @else
                        <span class="badge bg-secondary" style="margin-left: 10px;">
                            {{ ucfirst($violation->status) }}
                        </span>
                    @endif

                    <!-- Additional Appeal Info (only show if different from violation status) -->
                    @if($violation->hasBeenAppealed() && $violation->getAppealStatus() === 'pending' && $violation->status !== 'appealed')
                        <span class="badge bg-warning text-dark" style="margin-left: 5px;">
                            <i class="fas fa-clock"></i> Appeal Pending
                        </span>
                    @endif
                    <div class="violation-details">
                        <p>Category: {{ $violation->category_name }}</p>
                        @php
                            $penaltyText = match($violation->penalty) {
                                'W' => 'Written Warning',
                                'V' => 'Verbal Warning',
                                'VW' => 'Verbal Warning',
                                'WW' => 'Written Warning',
                                'P' => 'Probation',
                                'Pro' => 'Probation',
                                'T' => 'Termination of Contract',
                                default => $violation->penalty
                            };
                        @endphp
                        <p>Penalty: {{ $penaltyText }}</p>
                        @if(!empty($violation->consequence) && $violation->consequence !== 'N/A')
                            <p>Consequence: {{ $violation->consequence }}</p>
                        @endif

                        <!-- Date Information -->
                        <p class="text-muted"><small>Date Recorded: {{ \Carbon\Carbon::parse($violation->created_at)->format('M d, Y') }}</small></p>
                        @if($violation->status === 'resolved' && $violation->resolved_date)
                            <p class="text-muted"><small>Date Resolved: {{ \Carbon\Carbon::parse($violation->resolved_date)->format('M d, Y') }}</small></p>
                        @endif

                        <!-- Staff Information -->
                        @if($violation->recorded_by_name && $violation->recorded_by_name !== 'N/A')
                            <p class="text-muted"><small>Recorded By: {{ $violation->recorded_by_name }}</small></p>
                        @endif
                        @if($violation->prepared_by_name && $violation->prepared_by_name !== 'N/A')
                            <p class="text-muted"><small>Prepared By: {{ $violation->prepared_by_name }}</small></p>
                        @endif

                        <!-- Appeal Section -->
                        @if($violation->canBeAppealed())
                            <div class="mt-3 pt-3 border-top">
                                <button type="button" class="btn btn-warning btn-sm"
                                        data-violation-id="{{ $violation->id }}"
                                        data-violation-name="{{ $violation->violation_name }}"
                                        onclick="confirmAppeal(this.dataset.violationId, this.dataset.violationName)">
                                    <i class="fas fa-gavel"></i> Appeal This Violation
                                </button>
                                <p class="text-muted mt-2">
                                    <small><i class="fas fa-info-circle"></i> You can appeal this violation if you believe it was issued incorrectly.</small>
                                    @if($violation->recorded_by_name && $violation->recorded_by_name !== 'N/A')
                                        <br><small><i class="fas fa-bell"></i> The educator who recorded this violation ({{ $violation->recorded_by_name }}) will be notified of your appeal.</small>
                                    @endif
                                </p>
                            </div>
                        @elseif($violation->hasBeenAppealed() && $violation->latestAppeal)
                            <div class="mt-3 pt-3 border-top">
                                <h6 class="text-primary"><i class="fas fa-gavel"></i> Appeal Information</h6>
                                <p><strong>Appeal Status:</strong>
                                    @if($violation->getAppealStatus() === 'pending')
                                        <span class="text-warning"><i class="fas fa-clock"></i> Pending Review</span>
                                    @elseif($violation->getAppealStatus() === 'approved')
                                        <span class="text-success"><i class="fas fa-check-circle"></i> Approved</span>
                                    @elseif($violation->getAppealStatus() === 'denied')
                                        <span class="text-danger"><i class="fas fa-times-circle"></i> Denied</span>
                                    @endif
                                </p>
                                <p><strong>Violation Status:</strong>
                                    @php
                                        $appealStatus = method_exists($violation, 'getAppealStatus') ? $violation->getAppealStatus() : null;
                                    @endphp
                                    @if($violation->status === 'appeal_approved' || ($violation->status === 'resolved' && $appealStatus === 'approved'))
                                        <span class="text-success"><i class="fas fa-check"></i> No longer active (Appeal approved)</span>
                                    @elseif($violation->status === 'appeal_denied' || ($violation->status === 'active' && $appealStatus === 'denied'))
                                        <span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Remains active (Appeal denied)</span>
                                    @elseif($violation->status === 'appealed' || $appealStatus === 'pending')
                                        <span class="text-info"><i class="fas fa-hourglass-half"></i> Under review</span>
                                    @else
                                        <span class="text-muted">{{ ucfirst($violation->status ?? 'unknown') }}</span>
                                    @endif
                                </p>
                                @if($violation->latestAppeal)
                                    <p><strong>Appeal Date:</strong> {{ \Carbon\Carbon::parse($violation->latestAppeal->appeal_date)->format('M d, Y g:i A') }}</p>
                                    <p><strong>Your Reason:</strong> {{ $violation->latestAppeal->student_reason }}</p>

                                    @if($violation->latestAppeal->admin_response)
                                        <p><strong>Admin Response:</strong> {{ $violation->latestAppeal->admin_response }}</p>
                                    @endif

                                    @if($violation->latestAppeal->admin_decision_date)
                                        <p class="text-muted"><small>Decision Date: {{ \Carbon\Carbon::parse($violation->latestAppeal->admin_decision_date)->format('M d, Y g:i A') }}</small></p>
                                    @endif
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

<!-- Appeal Confirmation Modal -->
<div class="modal fade" id="appealConfirmModal" tabindex="-1" aria-labelledby="appealConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="appealConfirmModalLabel">
                    <i class="fas fa-gavel me-2"></i>Appeal Violation
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <strong>Violation:</strong> <span id="confirmViolationName"></span>
                </div>

                <div class="mb-3">
                    <label for="quickStudentReason" class="form-label">
                        Why should this violation be reconsidered? <span class="text-danger">*</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="quickStudentReason"
                        rows="4"
                        placeholder="Provide a detailed explanation..."
                        maxlength="1000"
                        required></textarea>
                    <small class="text-muted">
                        Minimum 50 characters required. <span id="quickCharCount">0</span>/1000
                    </small>
                </div>

                <div class="mb-3">
                    <label for="quickAdditionalEvidence" class="form-label">
                        Additional Evidence <span class="text-muted">(Optional)</span>
                    </label>
                    <textarea
                        class="form-control"
                        id="quickAdditionalEvidence"
                        rows="2"
                        placeholder="Any additional information..."
                        maxlength="500"></textarea>
                    <small class="text-muted"><span id="quickEvidenceCharCount">0</span>/500</small>
                </div>

                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Note:</strong> You can only appeal each violation once. The educator will be notified.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="submitAppealBtn" onclick="handleSubmitAppeal()">
                    <i class="fas fa-paper-plane me-2"></i>Submit Appeal
                </button>
            </div>
        </div>
    </div>
</div>



<!-- Clock JavaScript -->
<script>
function updateClock() {
    const now = new Date();
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric',
                     hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true };
    document.getElementById('current-time').textContent = now.toLocaleDateString('en-US', options);
}

updateClock();
setInterval(updateClock, 1000);

// Appeal Modal Functions
let currentViolationId = null;
let currentViolationName = null;

function confirmAppeal(violationId, violationName) {
    console.log('confirmAppeal called with:', violationId, violationName);

    // Store the violation details for later use
    currentViolationId = violationId;
    currentViolationName = violationName;

    console.log('Current violation ID set to:', currentViolationId);

    // Check if modal element exists
    const modalElement = document.getElementById('appealConfirmModal');
    if (!modalElement) {
        console.error('Appeal confirmation modal not found!');
        alert('Error: Modal not found. Please refresh the page and try again.');
        return;
    }

    console.log('Modal element found');

    // Update the confirmation modal with violation details
    const violationNameElement = document.getElementById('confirmViolationName');
    if (violationNameElement) {
        violationNameElement.textContent = violationName;
        console.log('Violation name updated in modal');
    }

    // Clear and reset form fields
    const reasonField = document.getElementById('quickStudentReason');
    const evidenceField = document.getElementById('quickAdditionalEvidence');
    if (reasonField) {
        reasonField.value = '';
        reasonField.classList.remove('is-invalid');
        console.log('Reason field cleared');
    }
    if (evidenceField) {
        evidenceField.value = '';
        console.log('Evidence field cleared');
    }
    updateQuickCharCount();
    updateQuickEvidenceCharCount();

    // Ensure submit button is enabled and ready
    const submitBtn = document.getElementById('submitAppealBtn');
    if (submitBtn) {
        submitBtn.disabled = false;
        submitBtn.style.pointerEvents = 'auto';
        submitBtn.style.cursor = 'pointer';
        submitBtn.innerHTML = '<i class="fas fa-paper-plane me-2"></i>Submit Appeal';
        console.log('Submit button reset and enabled');
    } else {
        console.error('Submit button not found in modal!');
    }

    // Show the confirmation modal
    try {
        // Ensure Bootstrap is loaded
        if (typeof bootstrap === 'undefined') {
            console.error('Bootstrap is not loaded!');
            alert('Error: Bootstrap not loaded. Please refresh the page.');
            return;
        }

        const confirmModal = new bootstrap.Modal(modalElement, {
            backdrop: 'static',
            keyboard: true
        });

        // Add event listener for when modal is shown
        modalElement.addEventListener('shown.bs.modal', function() {
            const modalContent = modalElement.querySelector('.modal-content');
            if (modalContent) {
                modalContent.style.animation = 'slideInDown 0.4s ease-out';
            }

            // Focus on the reason field
            setTimeout(() => {
                if (reasonField) {
                    reasonField.focus();
                }
            }, 500);
        }, { once: true });

        confirmModal.show();

    } catch (error) {
        console.error('Error showing modal:', error);
        alert('Error showing confirmation dialog: ' + error.message);
    }
}

function openAppealModal(violationId, violationName) {
    document.getElementById('violationId').value = violationId;
    document.getElementById('violationName').textContent = violationName;
    document.getElementById('studentReason').value = '';
    document.getElementById('additionalEvidence').value = '';
    updateCharCount();
    updateEvidenceCharCount();

    const modal = new bootstrap.Modal(document.getElementById('appealModal'));
    modal.show();
}

// Character count for reason textarea
document.getElementById('studentReason').addEventListener('input', updateCharCount);
function updateCharCount() {
    const textarea = document.getElementById('studentReason');
    const charCount = document.getElementById('charCount');
    charCount.textContent = textarea.value.length;

    if (textarea.value.length > 900) {
        charCount.style.color = 'red';
    } else if (textarea.value.length > 800) {
        charCount.style.color = 'orange';
    } else {
        charCount.style.color = 'inherit';
    }
}

// Character count for evidence textarea
document.getElementById('additionalEvidence').addEventListener('input', updateEvidenceCharCount);
function updateEvidenceCharCount() {
    const textarea = document.getElementById('additionalEvidence');
    const charCount = document.getElementById('evidenceCharCount');
    charCount.textContent = textarea.value.length;

    if (textarea.value.length > 450) {
        charCount.style.color = 'red';
    } else if (textarea.value.length > 400) {
        charCount.style.color = 'orange';
    } else {
        charCount.style.color = 'inherit';
    }
}

// Character counting functions for quick appeal form
function updateQuickCharCount() {
    const textarea = document.getElementById('quickStudentReason');
    const counter = document.getElementById('quickCharCount');
    if (textarea && counter) {
        counter.textContent = textarea.value.length;

        // Update color based on length
        if (textarea.value.length < 50) {
            counter.style.color = '#dc3545'; // Red
        } else if (textarea.value.length < 100) {
            counter.style.color = '#ffc107'; // Yellow
        } else {
            counter.style.color = '#198754'; // Green
        }
    }
}

function updateQuickEvidenceCharCount() {
    const textarea = document.getElementById('quickAdditionalEvidence');
    const counter = document.getElementById('quickEvidenceCharCount');
    if (textarea && counter) {
        counter.textContent = textarea.value.length;
    }
}

// Handle submit appeal button click
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for character counting
    const reasonField = document.getElementById('quickStudentReason');
    const evidenceField = document.getElementById('quickAdditionalEvidence');

    if (reasonField) {
        reasonField.addEventListener('input', updateQuickCharCount);
    }

    if (evidenceField) {
        evidenceField.addEventListener('input', updateQuickEvidenceCharCount);
    }

    // Handle submit button
    const submitBtn = document.getElementById('submitAppealBtn');
    console.log('Submit button found:', !!submitBtn);

    if (submitBtn) {
        // Ensure button is enabled and clickable
        submitBtn.disabled = false;
        submitBtn.style.pointerEvents = 'auto';
        submitBtn.style.cursor = 'pointer';

        console.log('Adding click event listener to submit button');

        submitBtn.addEventListener('click', function(e) {
            console.log('Submit button clicked!');
            e.preventDefault();
            e.stopPropagation();

            const reason = document.getElementById('quickStudentReason').value.trim();
            const evidence = document.getElementById('quickAdditionalEvidence').value.trim();

            console.log('Reason length:', reason.length);
            console.log('Evidence length:', evidence.length);

            // Validate input
            if (reason.length < 50) {
                alert('Please provide a more detailed reason for your appeal (at least 50 characters).');
                document.getElementById('quickStudentReason').focus();
                return;
            }

            // Add loading state to button
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
            this.disabled = true;

            console.log('Submitting appeal for violation:', currentViolationId);

            // Submit the appeal
            submitQuickAppeal(currentViolationId, reason, evidence, originalText, this);
        });

        console.log('Event listener added successfully');
    } else {
        console.error('Submit button not found!');
    }
});

// Fallback function for submit button onclick
function handleSubmitAppeal() {
    console.log('handleSubmitAppeal called (fallback)');

    const reason = document.getElementById('quickStudentReason').value.trim();
    const evidence = document.getElementById('quickAdditionalEvidence').value.trim();

    console.log('Reason length:', reason.length);

    // Validate input
    if (reason.length < 50) {
        alert('Please provide a more detailed reason for your appeal (at least 50 characters).');
        document.getElementById('quickStudentReason').focus();
        return;
    }

    if (!currentViolationId) {
        alert('Error: No violation selected. Please try again.');
        return;
    }

    // Add loading state to button
    const submitBtn = document.getElementById('submitAppealBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Submitting...';
    submitBtn.disabled = true;

    console.log('Submitting appeal for violation:', currentViolationId);

    // Submit the appeal
    submitQuickAppeal(currentViolationId, reason, evidence, originalText, submitBtn);
}

// Function to submit appeal directly from confirmation modal
function submitQuickAppeal(violationId, reason, evidence, originalButtonText, buttonElement) {
    console.log('Submitting appeal for violation:', violationId);
    console.log('Reason:', reason);
    console.log('Evidence:', evidence);

    // Create form data
    const formData = new FormData();
    const csrfToken = document.querySelector('meta[name="csrf-token"]');

    if (!csrfToken) {
        console.error('CSRF token not found!');
        alert('Error: CSRF token not found. Please refresh the page.');
        buttonElement.innerHTML = originalButtonText;
        buttonElement.disabled = false;
        return;
    }

    formData.append('_token', csrfToken.getAttribute('content'));
    formData.append('violation_id', violationId);
    formData.append('student_reason', reason);
    formData.append('additional_evidence', evidence);

    console.log('Sending request to:', '{{ route("student.appeal.submit") }}');

    // Submit via fetch
    fetch('{{ route("student.appeal.submit") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response ok:', response.ok);

        if (response.ok) {
            return response.json();
        }

        // Try to get error details
        return response.json().then(errorData => {
            console.error('Server error response:', errorData);
            throw new Error(errorData.message || `Server error: ${response.status}`);
        }).catch(() => {
            // If JSON parsing fails, try text
            return response.text().then(text => {
                console.error('Server response (text):', text);
                throw new Error(`Server error: ${response.status} - ${text}`);
            });
        });
    })
    .then(data => {
        console.log('Success response:', data);

        if (data.success) {
            // Reset button to success state
            if (buttonElement) {
                buttonElement.innerHTML = '<i class="fas fa-check me-2"></i>Appeal Submitted!';
                buttonElement.classList.remove('btn-warning');
                buttonElement.classList.add('btn-success');
            }

            // Close the modal - try multiple methods
            const modalElement = document.getElementById('appealConfirmModal');
            if (modalElement) {
                // Method 1: Try to get existing modal instance
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                } else {
                    // Method 2: Create new instance and hide
                    const newModal = new bootstrap.Modal(modalElement);
                    newModal.hide();
                }

                // Method 3: Force hide with direct style manipulation
                setTimeout(() => {
                    modalElement.style.display = 'none';
                    modalElement.classList.remove('show');
                    document.body.classList.remove('modal-open');

                    // Remove backdrop if it exists
                    const backdrop = document.querySelector('.modal-backdrop');
                    if (backdrop) {
                        backdrop.remove();
                    }
                }, 500);
            }

            // Show success toast notification instead of alert
            showSuccessToast('Your appeal has been submitted successfully! You will be notified when it is reviewed.');

            // Reload the page to show the updated status
            setTimeout(() => {
                window.location.reload();
            }, 2000);
        } else {
            throw new Error(data.message || 'Unexpected response from server');
        }
    })
    .catch(error => {
        console.error('Error submitting appeal:', error);
        alert('An error occurred while submitting your appeal: ' + error.message);

        // Reset button state
        if (buttonElement) {
            buttonElement.innerHTML = originalButtonText;
            buttonElement.disabled = false;
            buttonElement.style.pointerEvents = 'auto';
            buttonElement.style.cursor = 'pointer';
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

// Violation highlighting functionality
function highlightViolation() {
    // Get the highlight parameter from URL
    const urlParams = new URLSearchParams(window.location.search);
    const highlightId = urlParams.get('highlight');

    if (highlightId) {
        // Find the violation card to highlight
        const violationCard = document.getElementById('violation-' + highlightId);

        if (violationCard) {
            // Add highlighting class
            violationCard.classList.add('highlighted-violation');

            // Scroll to the violation card
            setTimeout(() => {
                violationCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                // Auto-expand the violation card to show details
                violationCard.classList.add('open');

                // Remove highlight after 5 seconds
                setTimeout(() => {
                    violationCard.classList.remove('highlighted-violation');
                    // Clean up URL parameter
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }, 5000);
            }, 500);
        }
    }
}

// Call highlight function when page loads
document.addEventListener('DOMContentLoaded', highlightViolation);

</script>

<style>
/* Violation highlighting styles */
.violation-card.highlighted-violation {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%) !important;
    border: 2px solid #f39c12 !important;
    box-shadow: 0 0 20px rgba(243, 156, 18, 0.3) !important;
    transform: scale(1.02);
    transition: all 0.3s ease;
    position: relative;
    z-index: 10;
}

.violation-card.highlighted-violation::before {
    content: "📍 New Violation";
    position: absolute;
    top: -10px;
    right: 15px;
    background: #f39c12;
    color: white;
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(243, 156, 18, 0.4);
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Enhanced notification badge animation */
.highlighted-violation .severity {
    animation: glow 2s ease-in-out infinite alternate;
}

@keyframes glow {
    from { box-shadow: 0 0 5px rgba(243, 156, 18, 0.5); }
    to { box-shadow: 0 0 15px rgba(243, 156, 18, 0.8); }
}
</style>

@endsection
