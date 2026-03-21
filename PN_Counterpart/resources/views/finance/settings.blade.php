@extends('layouts.finance')

@section('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@section('title', 'General Settings')
@section('page-title', 'General Settings')

@push('styles')
<style>
    :root {
        --primary:    #FF9933;
        --secondary:  #32abe3;
        --bg-light:   #f5f6fa;
        --card-bg:    #ffffff;
        --text-dark:  #2c3e50;
        --text-muted: #6c757d;
        --border:     #e1e4e8;
        --accent:     #FF9933;
    }
    body, .container-fluid {
        font-family: 'Inter', sans-serif;
        background: var(--bg-light);
        color: var(--text-dark);
    }
    .breadcrumb a {
        color: var(--primary);
        text-decoration: none;
    }
    .breadcrumb-item + .breadcrumb-item::before {
        color: var(--text-muted);
    }
    .card-modern {
        background: var(--card-bg);
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .card-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .card-header-modern {
        background: linear-gradient(90deg, var(--primary) 0%, #e67e22 100%);
        color: #fff;
        padding: 1.5rem;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
        border-bottom: none;
    }
    .card-header-modern h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.25rem;
    }
    .card-body-modern {
        padding: 2rem;
    }
    .settings-section {
        background: var(--card-bg);
        border: 1px solid var(--border);
        border-radius: 0.75rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        transition: all 0.2s ease;
    }
    .settings-section:hover {
        border-color: var(--secondary);
        box-shadow: 0 4px 12px rgba(50, 171, 227, 0.1);
    }
    .section-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--bg-light);
    }
    .section-icon {
        width: 48px;
        height: 48px;
        background: linear-gradient(135deg, var(--secondary) 0%, #1e88e5 100%);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: white;
        font-size: 1.25rem;
    }
    .section-title {
        font-size: 1.25rem;
        font-weight: 600;
        color: var(--text-dark);
        margin: 0;
    }
    .section-description {
        color: var(--text-muted);
        font-size: 0.9rem;
        margin: 0;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-label {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    .form-control {
        border: 2px solid var(--border);
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background: var(--card-bg);
    }
    .form-control:focus {
        border-color: var(--secondary);
        box-shadow: 0 0 0 0.2rem rgba(50, 171, 227, 0.25);
        background: var(--card-bg);
    }
    .input-group-text {
        background: var(--bg-light);
        border: 2px solid var(--border);
        border-left: none;
        color: var(--text-muted);
        font-weight: 500;
    }
    .form-check-input:checked {
        background-color: var(--secondary);
        border-color: var(--secondary);
    }
    .form-check-input:focus {
        border-color: var(--secondary);
        box-shadow: 0 0 0 0.25rem rgba(50, 171, 227, 0.25);
    }
    .btn-primary {
        background: linear-gradient(135deg, var(--primary) 0%, #e67e22 100%);
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 153, 51, 0.3);
        background: linear-gradient(135deg, #e67e22 0%, var(--primary) 100%);
    }
    .btn-secondary {
        background: var(--secondary);
        border: none;
        border-radius: 0.5rem;
        padding: 0.75rem 2rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-secondary:hover {
        background: #1e88e5;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(50, 171, 227, 0.3);
    }
    .btn-outline-warning {
        border: 2px solid #ff8c00;
        color: #ff8c00;
        border-radius: 0.5rem;
        padding: 0.5rem 1rem;
        font-weight: 600;
        transition: all 0.2s ease;
    }
    .btn-outline-warning:hover {
        background: #ff8c00;
        color: white;
        transform: translateY(-1px);
    }
    .small-text {
        color: var(--text-muted);
        font-size: 0.85rem;
        margin-top: 0.25rem;
    }
    .switch-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 1rem;
        background: var(--bg-light);
        border-radius: 0.5rem;
        border: 1px solid var(--border);
        /* Ensure exact uniform box height for switches and the Sender Name box on larger screens */
        height: 76px;
    }
    .switch-info {
        flex: 1;
    }
    .switch-title {
        font-weight: 600;
        color: var(--text-dark);
        margin-bottom: 0.25rem;
    }
    .switch-description {
        color: var(--text-muted);
        font-size: 0.85rem;
    }
    .loading-spinner {
        display: none;
        text-align: center;
        padding: 2rem;
    }
    .loading-spinner .spinner-border {
        color: var(--secondary);
    }
    .alert-success {
        background: rgba(40, 167, 69, 0.1);
        border: 1px solid rgba(40, 167, 69, 0.2);
        color: #155724;
        border-radius: 0.5rem;
    }
    .alert-danger {
        background: rgba(220, 53, 69, 0.1);
        border: 1px solid rgba(220, 53, 69, 0.2);
        color: #721c24;
        border-radius: 0.5rem;
    }
    /* Compact input widths for settings page */
    #general-settings-inline .form-control {
        max-width: 280px;
        width: 100%;
    }
    #general-settings-inline .input-group {
        max-width: 320px;
    }
    #general-settings-inline .input-group .form-control {
        max-width: 240px;
    }
    #general-settings-inline .switch-container {
        max-width: 460px;
    }
    /* Slightly wider switches for Notification Methods */
    #notification-methods .switch-container {
        max-width: 420px;
    }
    /* Make the Payment Reminders master toggle almost full width of its card */
    #payment-reminders-section .switch-container {
        width: 95%;
        max-width: none;
    }
    /* Keep inputs full-width on small screens */
    @media (max-width: 576px) {
        #general-settings-inline .form-control,
        #general-settings-inline .input-group,
        #general-settings-inline .switch-container {
            max-width: 100%;
        }
        #payment-reminders-section .switch-container {
            width: 100%;
        }
        /* Allow switch boxes to collapse naturally on small screens */
        .switch-container {
            height: auto;
        }
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">

    <!-- Loading Spinner -->
    <div id="settings-loading" class="loading-spinner">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Loading settings...</p>
    </div>

    <!-- Settings Form -->
    <div id="settings-form" style="display: none;">
        <!-- Page Header -->
        <div class="card-modern mb-4">
            <div class="card-header-modern">
                <div class="d-flex align-items-center">
                    <i class="fas fa-cogs me-3" style="font-size: 1.5rem;"></i>
                    <div>
                        <h5 class="mb-1">General Settings</h5>
                        <p class="mb-0 opacity-75" style="font-size: 0.9rem;">Configure system preferences and general settings</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Settings Sections -->
        <form id="generalSettingsForm">
            <div id="general-settings-inline" class="row g-3 align-items-stretch">
            <!-- Payment Reminders Settings -->
            <div class="col-12 col-lg-6 d-flex">
                <div class="settings-section h-100 w-100" id="payment-reminders-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <div>
                        <h6 class="section-title">Payment Reminders Settings</h6>
                        <p class="section-description">Configure automated payment reminders and monthly reminder schedule</p>
                    </div>
                </div>
                
                <div class="row g-3">
                    <!-- Enable Payment Reminders -->
                    <div class="col-12">
                        <div class="form-group">
                            <div class="switch-container">
                                <div class="switch-info">
                                    <div class="switch-title">Enable Payment Reminders</div>
                                    <div class="switch-description">Automatically enable reminders for new students and send payment reminders on a specific day each month</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="payment_reminder_enabled" style="transform: scale(1.2);">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reminder After -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_reminder_first_after_months" class="form-label">Reminder After</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="payment_reminder_first_after_months" min="1" max="12" placeholder="Loading...">
                                <span class="input-group-text">months</span>
                            </div>
                            <div class="small-text">Send first reminder after this many <strong>consecutive</strong> months of no payment</div>
                        </div>
                    </div>
                    
                    <!-- Reminder Day and Time -->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="monthly_reminder_day" class="form-label">Reminder Day of Month</label>
                            <input type="number" class="form-control" id="monthly_reminder_day" min="1" max="31" placeholder="Loading...">
                            <div class="small-text">Day of the month to send reminders (1-31)</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="monthly_reminder_time" class="form-label">Reminder Time</label>
                            <input type="time" class="form-control" id="monthly_reminder_time" placeholder="Loading...">
                            <div class="small-text">Time of day to send monthly reminders</div>
                        </div>
                    </div>
                    
                    <!-- Maximum Reminders -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="payment_reminder_max_reminders" class="form-label">Maximum Reminders</label>
                            <input type="number" class="form-control" id="payment_reminder_max_reminders" min="1" max="10" placeholder="Loading...">
                            <div class="small-text">Maximum number of reminders to send per student</div>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Notification Methods Settings -->
            <div class="col-12 col-lg-6 d-flex">
                <div class="settings-section h-100 w-100" id="notification-methods">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div>
                        <h6 class="section-title">Notification Methods</h6>
                        <p class="section-description">Choose how to send notifications to students</p>
                    </div>
                </div>
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="switch-container">
                                <div class="switch-info">
                                    <div class="switch-title">Email Notifications</div>
                                    <div class="switch-description">Send payment reminders via email to students</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notification_method_email" style="transform: scale(1.2);">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="switch-container">
                                <div class="switch-info">
                                    <div class="switch-title">Dashboard Notifications</div>
                                    <div class="switch-description">Show notifications as modal popup in student dashboard</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notification_method_dashboard" style="transform: scale(1.2);">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="switch-container">
                                <div class="switch-info">
                                    <div class="switch-title">Student Account Notifications</div>
                                    <div class="switch-description">Send notifications to student account notifications page</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="notification_method_student_account" style="transform: scale(1.2);">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <div class="switch-container">
                                <div class="switch-info">
                                    <div class="switch-title">Sender Name</div>
                                    <div class="switch-description small-text">Name shown as sender in notifications and emails</div>
                                </div>
                                <div style="flex: 0 0 55%;">
                                    <input type="text" class="form-control" id="notification_sender_name" placeholder="Loading...">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            </div>
            </div>
            <!-- Batch Management & Matrix Settings -->
            <div class="settings-section">
                <div class="section-header">
                    <div class="section-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h6 class="section-title">Batch Management & Payment Settings</h6>
                        <p class="section-description">Configure batch-specific payment amounts, start dates, and matrix settings</p>
                    </div>
                </div>
                
                <!-- Batch Settings and Matrix Settings Side by Side -->
                <div class="row mb-4">
                    <!-- Batch-Specific Settings -->
                    <div class="col-md-6">
                        <div class="p-4" style="border: 1px solid var(--border); border-radius: 0.5rem; background: var(--card-bg);">
                            <h6 class="fw-bold mb-3" style="color: var(--text-dark);">
                                <i class="fas fa-layer-group me-2" style="color: var(--secondary);"></i>Batch-Specific Settings
                            </h6>
                            <p class="text-muted mb-3">Configure payment settings for a specific batch. Select a batch to set its total amount due, start payment month, and monthly default value.</p>
                            
                            <!-- Batch Selection -->
                            <div class="form-group mb-3">
                                <label for="selected_batch" class="form-label fw-semibold">Select Batch</label>
                                <select class="form-control" id="selected_batch">
                                    <option value="">Choose a batch...</option>
                                </select>
                                <div class="small-text">Select batch to configure its payment settings</div>
                            </div>
                        </div>
                    </div>

                    <!-- Enable Advance Payments -->
                    <div class="col-md-6">
                        <div class="p-4" style="border: 1px solid var(--border); border-radius: 0.5rem; background: var(--card-bg);">
                            <h6 class="fw-bold mb-3" style="color: var(--text-dark);">
                                <i class="fas fa-toggle-on me-2" style="color: var(--secondary);"></i>Matrix Settings
                            </h6>
                            <div class="form-group">
                                <div class="switch-container">
                                    <div class="switch-info">
                                        <div class="switch-title">Enable Advance Payments</div>
                                        <div class="switch-description">Allow payments to cover multiple months (e.g., ₱1000 = 2 months)</div>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="matrix_advance_payment_enabled" style="transform: scale(1.2);">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


                <!-- Batch Settings Form (Hidden initially) -->
                <div id="batch-settings-form" style="display: none;">
                    <div class="p-4" style="border: 1px solid var(--border); border-radius: 0.5rem; background: var(--card-bg);">
                        <h6 class="fw-bold mb-3" style="color: var(--text-dark);">
                            <i class="fas fa-cog me-2" style="color: var(--secondary);"></i>Settings for Batch <span id="selected-batch-year"></span>
                        </h6>
                        
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="batch_total_amount" class="form-label fw-semibold">Total Payable Amount</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="batch_total_amount" min="0" step="100" placeholder="Leave empty to keep current value">
                                    </div>
                                    <div class="small-text">Total amount this batch needs to pay</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="batch_start_month" class="form-label fw-semibold">Payment Start Month</label>
                                    <select class="form-control" id="batch_start_month">
                                        <option value="">Keep current value</option>
                                        <option value="1">January</option>
                                        <option value="2">February</option>
                                        <option value="3">March</option>
                                        <option value="4">April</option>
                                        <option value="5">May</option>
                                        <option value="6">June</option>
                                        <option value="7">July</option>
                                        <option value="8">August</option>
                                        <option value="9">September</option>
                                        <option value="10">October</option>
                                        <option value="11">November</option>
                                        <option value="12">December</option>
                                    </select>
                                    <div class="small-text">Month when payments start for this batch</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="batch_start_year" class="form-label fw-semibold">Payment Start Year</label>
                                    <input type="number" class="form-control" id="batch_start_year" min="1900" placeholder="Leave empty to keep current value">
                                    <div class="small-text">Year when payments start for this batch</div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="batch_monthly_default" class="form-label fw-semibold">Monthly Default Value</label>
                                    <div class="input-group">
                                        <span class="input-group-text">₱</span>
                                        <input type="number" class="form-control" id="batch_monthly_default" min="100" max="10000" step="50" placeholder="Leave empty to keep current value">
                                    </div>
                                    <div class="small-text">Monthly amount for matrix calculations for this specific batch (₱500 = 1 month)</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-primary" id="saveBatchSettingsBtn">
                                <i class="fas fa-save me-2"></i>Save Batch Settings
                            </button>
                            <button type="button" class="btn btn-outline-secondary ms-2" id="clearBatchSettingsBtn">
                                <i class="fas fa-times me-2"></i>Clear Settings
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="d-flex gap-3 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Settings
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="loadAllSettings()">
                    <i class="fas fa-refresh me-2"></i>Reset to Saved
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Load settings on page load
    loadAllSettings();
    
    // Form submission
    document.getElementById('generalSettingsForm').addEventListener('submit', function(e) {
        e.preventDefault();
        saveAllSettings();
    });
    
    // Add click event listener directly to the Save Settings button
    document.querySelector('#generalSettingsForm button[type="submit"]').addEventListener('click', function(e) {
        e.preventDefault();
        saveAllSettings();
    });
    
    
    // Load available batches for dropdown
    loadAvailableBatches();
    
    // Batch selection change handler
    document.getElementById('selected_batch').addEventListener('change', function() {
        const selectedBatch = this.value;
        if (selectedBatch) {
            showBatchSettingsForm(selectedBatch);
            loadBatchSpecificSettings(selectedBatch);
        } else {
            hideBatchSettingsForm();
        }
    });
    
    // Save batch settings button
    document.getElementById('saveBatchSettingsBtn').addEventListener('click', function() {
        saveBatchSpecificSettings();
    });
    
    // Clear batch settings button
    document.getElementById('clearBatchSettingsBtn').addEventListener('click', function() {
        clearBatchSpecificSettings();
    });
});

// Show loading spinner
function showSettingsLoading() {
    document.getElementById('settings-loading').style.display = 'block';
    document.getElementById('settings-form').style.display = 'none';
}

// Hide loading spinner
function hideSettingsLoading() {
    document.getElementById('settings-loading').style.display = 'none';
    document.getElementById('settings-form').style.display = 'block';
}

// Load all settings from server
function loadAllSettings() {
    showSettingsLoading();
    
    fetch('/finance/settings/load')
        .then(response => {
            console.log('Response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Settings data received:', data);
            hideSettingsLoading();
            if (data.success !== false) {
                populateSettingsForm(data);
            } else {
                throw new Error(data.message || 'Failed to load settings');
            }
        })
        .catch(error => {
            hideSettingsLoading();
            console.error('Error loading settings:', error);
            console.error('Full error details:', error);
            Swal.fire({
                icon: 'error',
                title: 'Loading Error',
                text: 'Failed to load settings. Check console for details.',
                confirmButtonColor: 'var(--secondary)'
            });
        });
}

// Save all settings to server
function saveAllSettings() {
    console.log('saveAllSettings() called');
    const settings = collectAllSettings();
    console.log('Settings to save:', settings);
    
    // Validate required fields
    if (!validateSettings(settings)) {
        console.log('Validation failed, not saving');
        return;
    }
    
    console.log('Validation passed, proceeding to save');
    showSettingsLoading();

    const csrfToken = document.querySelector('meta[name="csrf-token"]');
    if (!csrfToken) {
        console.error('CSRF token not found');
        Swal.fire({
            icon: 'error',
            title: 'Security Error',
            text: 'CSRF token not found. Please refresh the page.',
            confirmButtonColor: 'var(--secondary)'
        });
        hideSettingsLoading();
        return;
    }
    
    console.log('Making fetch request to /finance/settings/save');
    
    fetch('/finance/settings/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken.getAttribute('content')
        },
        body: JSON.stringify(settings)
    })
    .then(response => {
        console.log('Fetch response status:', response.status);
        if (!response.ok) {
            console.error('Response not OK:', response.status, response.statusText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Fetch response data:', data);
        hideSettingsLoading();
        if (data.success) {
            console.log('Settings saved successfully');
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: 'Settings saved successfully!',
                confirmButtonColor: 'var(--secondary)'
            });
        } else {
            console.error('Save failed:', data.message);
            throw new Error(data.message || 'Failed to save settings');
        }
    })
    .catch(error => {
        hideSettingsLoading();
        console.error('Error saving settings:', error);
        
        let errorMessage = 'Failed to save settings';
        if (error.message.includes('422')) {
            errorMessage = 'Please check your input values and try again';
        } else if (error.message.includes('validation')) {
            errorMessage = 'Some fields contain invalid values';
        }
        
        Swal.fire({
            icon: 'error',
            title: 'Save Error',
            text: errorMessage,
            confirmButtonColor: 'var(--secondary)'
        });
    });
}

// Collect all settings from form
function collectAllSettings() {
    const settings = {};
    
    // Payment Reminders
    settings.payment_reminder_first_after_months = document.getElementById('payment_reminder_first_after_months').value;
    settings.payment_reminder_max_reminders = document.getElementById('payment_reminder_max_reminders').value;
    settings.payment_reminder_enabled = document.getElementById('payment_reminder_enabled').checked;

    // Notification Methods
    settings.notification_method_email = document.getElementById('notification_method_email').checked;
    settings.notification_method_dashboard = document.getElementById('notification_method_dashboard').checked;
    settings.notification_method_student_account = document.getElementById('notification_method_student_account').checked;
    settings.notification_method_sms = document.getElementById('notification_method_sms').checked;
    settings.notification_sender_name = document.getElementById('notification_sender_name').value;

    // Monthly Reminders
    settings.monthly_reminder_day = document.getElementById('monthly_reminder_day').value;
    settings.monthly_reminder_time = document.getElementById('monthly_reminder_time').value;

    // Matrix Settings
    settings.matrix_advance_payment_enabled = document.getElementById('matrix_advance_payment_enabled').checked;
    
    // Batch Settings (simplified - no batch settings collected for now)
    settings.batch_settings = {};
    
    // Debug log to see what we're collecting
    console.log('Collected settings:', settings);
    
    return settings;
}

// Populate form with loaded settings
function populateSettingsForm(data) {
    const settings = data.settings || data;

    try {
        // Payment Reminders
        if (settings.payment_reminders) {
            safeSetValue('payment_reminder_first_after_months', settings.payment_reminders.first_after_months || 2);
            safeSetValue('payment_reminder_max_reminders', settings.payment_reminders.max_reminders || 5);
            safeSetChecked('payment_reminder_enabled', settings.payment_reminders.auto_enabled !== false);
        }

        // Notification Methods
        if (settings.notification_methods) {
            safeSetChecked('notification_method_email', settings.notification_methods.email !== false);
            safeSetChecked('notification_method_dashboard', settings.notification_methods.dashboard !== false);
            safeSetChecked('notification_method_student_account', settings.notification_methods.student_account !== false);
            safeSetChecked('notification_method_sms', settings.notification_methods.sms || false);
            safeSetValue('notification_sender_name', settings.notification_methods.sender_name || 'Finance Department');
        }

        // Monthly Reminders
        if (settings.monthly_reminders) {
            safeSetValue('monthly_reminder_day', settings.monthly_reminders.day || 1);
            safeSetValue('monthly_reminder_time', settings.monthly_reminders.time || '08:00');
        }

        // Matrix Settings
        if (settings.matrix_settings) {
            safeSetChecked('matrix_advance_payment_enabled', settings.matrix_settings.advance_payment_enabled !== false);
        }

        // General Settings - Removed as no longer needed
    } catch (error) {
        console.error('Error populating settings form:', error);
    }
}

// Helper functions for safe DOM manipulation
function safeSetValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.value = value;
    } else {
        console.warn(`Element not found: ${elementId}`);
    }
}

function safeSetChecked(elementId, checked) {
    const element = document.getElementById(elementId);
    if (element) {
        element.checked = checked;
    } else {
        console.warn(`Element not found: ${elementId}`);
    }
}

// Validate settings before saving
function validateSettings(settings) {
    const errors = [];

    // Validate payment reminder settings
    if (settings.payment_reminder_first_after_months && (settings.payment_reminder_first_after_months < 1 || settings.payment_reminder_first_after_months > 12)) {
        errors.push('First reminder must be between 1-12 months');
    }

    if (settings.payment_reminder_max_reminders && (settings.payment_reminder_max_reminders < 1 || settings.payment_reminder_max_reminders > 10)) {
        errors.push('Maximum reminders must be between 1-10');
    }

    if (settings.monthly_reminder_day && (settings.monthly_reminder_day < 1 || settings.monthly_reminder_day > 31)) {
        errors.push('Reminder day must be between 1-31');
    }

    if (errors.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Validation Error',
            html: errors.join('<br>'),
            confirmButtonColor: 'var(--secondary)'
        });
        return false;
    }

    return true;
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Load available batches for dropdown
function loadAvailableBatches() {
    console.log('Loading available batches...');
    fetch('/finance/batches/available')
        .then(response => {
            console.log('Batch response status:', response.status);
            return response.json();
        })
        .then(data => {
            console.log('Batch data received:', data);
            populateBatchDropdown(data.batches || []);
        })
        .catch(error => {
            console.error('Error loading available batches:', error);
            // Add fallback batches if API fails
            const currentYear = new Date().getFullYear();
            const fallbackBatches = [
                { batch_year: currentYear - 1 },
                { batch_year: currentYear },
                { batch_year: currentYear + 1 }
            ];
            populateBatchDropdown(fallbackBatches);
        });
}

// Populate batch dropdown
function populateBatchDropdown(batches) {
    console.log('Populating dropdown with batches:', batches);
    const dropdown = document.getElementById('selected_batch');
    dropdown.innerHTML = '<option value="">Choose a batch...</option>';
    
    if (!batches || batches.length === 0) {
        console.log('No batches available, adding fallback options');
        const currentYear = new Date().getFullYear();
        batches = [
            { batch_year: currentYear - 1 },
            { batch_year: currentYear },
            { batch_year: currentYear + 1 }
        ];
    }
    
    batches.forEach(batch => {
        const option = document.createElement('option');
        option.value = batch.batch_year;
        option.textContent = `Batch ${batch.batch_year}`;
        dropdown.appendChild(option);
        console.log('Added batch option:', batch.batch_year);
    });
    
    console.log('Dropdown populated with', batches.length, 'batches');
}

// Show batch settings form
function showBatchSettingsForm(batchYear) {
    document.getElementById('batch-settings-form').style.display = 'block';
    document.getElementById('selected-batch-year').textContent = batchYear;
}

// Hide batch settings form
function hideBatchSettingsForm() {
    document.getElementById('batch-settings-form').style.display = 'none';
    clearBatchForm();
}

// Load batch-specific settings
function loadBatchSpecificSettings(batchYear) {
    fetch(`/finance/batches/${batchYear}/settings`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.settings) {
                populateBatchForm(data.settings);
            } else {
                // No existing settings, use defaults
                clearBatchForm();
            }
        })
        .catch(error => {
            console.error('Error loading batch settings:', error);
            clearBatchForm();
        });
}

// Populate batch form with settings
function populateBatchForm(settings) {
    document.getElementById('batch_total_amount').value = settings.total_amount || '';
    document.getElementById('batch_start_month').value = settings.start_month || 1;
    document.getElementById('batch_start_year').value = settings.start_year || new Date().getFullYear();
    document.getElementById('batch_monthly_default').value = settings.monthly_default || 500;
}

// Clear batch form
function clearBatchForm() {
    document.getElementById('batch_total_amount').value = '';
    document.getElementById('batch_start_month').value = '';
    document.getElementById('batch_start_year').value = '';
    document.getElementById('batch_monthly_default').value = '';
}

// Save batch-specific settings
function saveBatchSpecificSettings() {
    const selectedBatch = document.getElementById('selected_batch').value;
    if (!selectedBatch) {
        Swal.fire('Error', 'Please select a batch first', 'error');
        return;
    }
    
    // Only include fields that have values (allow individual field updates)
    const settings = {
        batch_year: selectedBatch
    };
    
    const totalAmount = document.getElementById('batch_total_amount').value;
    if (totalAmount && totalAmount.trim() !== '') {
        settings.total_amount = parseFloat(totalAmount);
    }
    
    const startMonth = document.getElementById('batch_start_month').value;
    if (startMonth && startMonth.trim() !== '') {
        settings.start_month = parseInt(startMonth);
    }
    
    const startYear = document.getElementById('batch_start_year').value;
    if (startYear && startYear.trim() !== '') {
        settings.start_year = parseInt(startYear);
    }
    
    const monthlyDefault = document.getElementById('batch_monthly_default').value;
    if (monthlyDefault && monthlyDefault.trim() !== '') {
        settings.monthly_default = parseFloat(monthlyDefault);
    }
    
    fetch(`/finance/batches/${selectedBatch}/settings/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire('Success', 'Batch settings saved successfully!', 'success');
        } else {
            Swal.fire('Error', data.message || 'Failed to save batch settings', 'error');
        }
    })
    .catch(error => {
        console.error('Error saving batch settings:', error);
        Swal.fire('Error', 'Failed to save batch settings', 'error');
    });
}

// Clear batch-specific settings
function clearBatchSpecificSettings() {
    const selectedBatch = document.getElementById('selected_batch').value;
    if (!selectedBatch) {
        return;
    }
    
    Swal.fire({
        title: 'Clear Settings?',
        text: `Are you sure you want to clear all settings for Batch ${selectedBatch}?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: 'var(--secondary)',
        confirmButtonText: 'Yes, clear them!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/finance/batches/${selectedBatch}/settings/clear`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    clearBatchForm();
                    Swal.fire('Cleared', 'Batch settings have been cleared', 'success');
                } else {
                    Swal.fire('Error', data.message || 'Failed to clear batch settings', 'error');
                }
            })
            .catch(error => {
                console.error('Error clearing batch settings:', error);
                Swal.fire('Error', 'Failed to clear batch settings', 'error');
            });
        }
    });
}
</script>
@endpush
