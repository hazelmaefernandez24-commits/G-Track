@extends('layouts.finance')

@section('title', 'Payments')
@section('page-title', 'Payment Management')

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
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.08);
    }
    .card-header-modern {
        background: linear-gradient(90deg, var(--primary));
        color: #fff;
        padding: 1rem 1.5rem;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }
    .card-header-modern h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.1rem;
    }
    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        align-items: flex-end;
        margin-bottom: 1.5rem;
    }
    .filter-group {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
    }
    .filter-group input,
    .filter-group select {
        padding: 0.5rem;
        border: 1px solid var(--border);
        border-radius: 0.5rem;
        width: 200px;
    }
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
        overflow-x: auto;
        border-radius: 0 0 0.75rem 0.75rem;
    }
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: 0.9rem;
        min-width: 700px;
    }
    .table-modern thead th {
        position: sticky;
        top: 0;
        background: var(--card-bg);
        color: var(--primary);
        font-weight: 600;
        padding: 0.75rem;
        text-transform: uppercase;
        border-bottom: 2px solid var(--border);
        z-index: 2;
    }
    .table-modern tbody td {
        padding: 0.75rem;
        border-bottom: 1px solid var(--border);
    }
    .table-modern tbody tr:nth-child(even) {
        background: var(--bg-light);
    }
    .table-modern tbody tr:hover {
        background: var(--secondary);
        color: #fff;
    }
    .btn-sm {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }
    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary) !important;
        box-shadow: 0 0 0 0.2rem rgba(0,95,153,0.25) !important;
    }
    .modal-content {
        border-radius: 0.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    @media (max-width: 576px) {
        .filters {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .filter-group input,
        .filter-group select {
            width: 100%;
        }
        .table-responsive {
            max-height: none;
        }
        .table-modern {
            min-width: 600px;
        }

        /* Matrix View Toggle Styles */
        .form-check-input:checked {
            background-color: rgba(255, 255, 255, 0.8);
            border-color: rgba(255, 255, 255, 0.8);
        }

        .form-check-label {
            font-size: 0.9rem;
            font-weight: 500;
        }

        /* Custom sizing for matrix view toggle */
        #matrixViewToggle {
            width: 16px;
            height: 16px;
            margin-top: 2px;
            cursor: pointer;
            border: 2px solid rgba(255, 255, 255, 0.8);
            border-radius: 3px;
            background-color: transparent;
            transition: all 0.2s ease;
        }

        #matrixViewToggle:checked {
            background-color: rgba(255, 255, 255, 0.9);
            border-color: rgba(255, 255, 255, 0.9);
        }

        #matrixViewToggle:hover {
            border-color: rgba(255, 255, 255, 1);
            transform: scale(1.05);
        }

        #matrixViewToggle + .form-check-label {
            font-size: 0.85rem;
            line-height: 1.2;
            margin-left: 6px;
            cursor: pointer;
            user-select: none;
        }

        /* Matrix view toggle container styling */
        .form-check {
            display: flex;
            align-items: center;
            margin: 0;
            padding: 0;
        }

        .form-check .form-check-input {
            flex-shrink: 0;
        }

        .form-check .form-check-label {
            flex: 1;
            margin-bottom: 0;
        }

        .payment-matrix-cell {
            cursor: pointer;
            text-align: center;
            vertical-align: middle;
        }

        .student-matrix-row:hover {
            background-color: #f8f9fa;
            cursor: pointer;
        }

        #paymentMatrixTable th {
            font-size: 0.9rem;
        }

        #paymentMatrixTable td {
            vertical-align: middle;
        }

        .matrix-student-cell {
            position: sticky;
            left: 0;
            background: white;
            z-index: 5;
            border-right: 2px solid #dee2e6;
        }

        /* Matrix table improvements */
        #paymentMatrixTable {
            border-collapse: separate;
            border-spacing: 0;
        }

        #paymentMatrixTable td {
            border: 1px solid #dee2e6;
            text-align: center;
            vertical-align: middle;
            padding: 8px 4px;
            min-width: 60px;
        }

        #paymentMatrixTable .payment-matrix-cell {
            transition: all 0.2s ease;
        }

        #paymentMatrixTable .payment-matrix-cell:hover {
            background-color: white !important;
            transform: scale(1.1);
            z-index: 10;
            position: relative;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
    }
</style>
@endpush

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">

<div class="py-4 container-fluid">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('payment_added'))
    @php
        $sessionUser = session('user');
        $localUser = $sessionUser ? \App\Models\User::where('user_email', $sessionUser['user_email'])->first() : null;
        $userRole = $localUser ? $localUser->user_role : null;
    @endphp
    <script>
        Swal.fire({
            title: '{{ $userRole === 'cashier' ? 'Waiting for Finance Approval' : 'Success!' }}',
            text: '{{ $userRole === 'cashier' ? 'This payment is now waiting for finance approval.' : 'Payment has been added successfully.' }}',
            icon: '{{ $userRole === 'cashier' ? 'info' : 'success' }}',
            confirmButtonColor: '{{ $userRole === 'cashier' ? '#0d6efd' : '#28a745' }}'
        });
    </script>
    @endif

    <!-- Filters -->
    <div class="filters">
        <div class="filter-group">
            <label for="searchInput">Search</label>
            <input type="text"
                   id="searchInput"
                   placeholder="Name or Student ID"
                   onkeyup="handleFilterChange()">
        </div>
        <form method="GET" action="{{ route('finance.financePayments') }}" class="filter-group" style="margin-bottom: 0;">
            <label for="batch_year">Class Batch</label>
            <select name="batch_year" id="batch_year" class="form-select" onchange="handleBatchFilterChange(this)">
                <option value="">All Class Batches</option>
                @foreach($batches as $batch)
                    <option value="{{ $batch->batch_year }}" {{ request('batch_year') == $batch->batch_year ? 'selected' : '' }}>
                        {{ $batch->batch_year }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <!-- Payment Table -->
    <div class="card card-modern">
        <div class="card-header-modern d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <h5 class="mb-0 me-3">Student Payments</h5>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="matrixViewToggle">
                    <label class="form-check-label text-white" for="matrixViewToggle">
                        <i class="fas fa-table me-1"></i>Matrix View
                    </label>
                </div>
            </div>
        </div>

        <!-- List View (Default) -->
        <div id="listViewContent">
            <div class="table-responsive">
            <table class="mb-0 table-modern" id="paymentsTable">
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Class Batch</th>
                        <th>Total Paid</th>
                        <th>Remaining Balance</th>
                        <th>Payables</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students->sortBy('last_name') as $student)
                        <tr>
                            <td data-label="Student ID">{{ $student->student_id }}</td>
                            <td data-label="Name">{{ $student->last_name }}, {{ $student->first_name }}</td>
                            <td data-label="Batch Year">{{ $student->batch }}</td>
                            <td data-label="Total Paid">₱{{ number_format($student->total_paid,2) }}</td>
                            <td data-label="Remaining">₱{{ number_format($student->remaining_balance,2) }}</td>
                            <td data-label="Due">₱{{ number_format($student->batchInfo->total_due ?? 0, 2) }}</td>
                            <td data-label="Actions">
                                <button class="btn btn-sm btn-primary me-1"
                                        data-bs-toggle="modal"
                                        data-bs-target="#paymentModal"
                                        onclick="loadAddPaymentForm('{{ $student->student_id }}', '{{ $student->last_name }}, {{ $student->first_name }}')"
                                        @if(($student->remaining_balance ?? 0) <= 0) disabled title="Fully Paid: Cannot add more payments" @endif>
                                    <i class="fas fa-plus"></i>
                                </button>
                                <a href="{{ route('finance.getPaymentHistory',['studentId'=>$student->student_id]) }}"
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-history"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

        <!-- Matrix View (Hidden by default) -->
        <div id="matrixViewContent" style="display: none;">
            <div class="card-body p-0">





                <!-- Matrix Table -->
                <div class="table-responsive" style="max-height: 60vh;">
                    <table class="table table-bordered table-hover mb-0" id="paymentMatrixTable">
                        <thead>
                            <tr style="height: 50px;">
                                <th class="text-center align-middle" style="min-width: 200px; height: 50px; position: sticky; left: 0; background: #32abe3; z-index: 10; color: white; vertical-align: middle;">
                                    <i class="fas fa-user me-1"></i>Student
                                </th>
                                <th class="text-center align-middle" style="min-width: 80px; height: 50px; position: sticky; left: 200px; background: #32abe3; z-index: 10; color: white; vertical-align: middle;">Batch</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month1">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month2">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month3">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month4">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month5">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month6">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month7">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month8">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month9">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month10">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month11">-</th>
                                <th class="text-center align-middle" style="min-width: 90px; height: 50px; background: #32abe3; color: white; vertical-align: middle;" id="month12">-</th>
                                <th class="text-center align-middle" style="min-width: 100px; height: 50px; background: #32abe3; color: white; vertical-align: middle;">Total Paid</th>
                                <th class="text-center align-middle" style="min-width: 100px; height: 50px; position: sticky; right: 100px; background: #32abe3; z-index: 10; color: white; vertical-align: middle;">Payable</th>
                                <th class="text-center align-middle" style="min-width: 140px; height: 50px; position: sticky; right: 0; background: #32abe3; z-index: 10; color: white; vertical-align: middle;">Status</th>
                            </tr>
                        </thead>
                        <tbody id="matrixTableBody">
                            <!-- Matrix data will be loaded here via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Year Pagination Footer -->
                <div class="d-flex justify-content-center align-items-center mt-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
                    <div class="d-flex align-items-center">
                        <button type="button" class="btn btn-outline-primary btn-sm me-2" id="prevYearBtn" onclick="previousYear()">
                            <i class="fas fa-chevron-left"></i>
                        </button>
                        <span class="mx-3 fw-bold" style="color: #2c3e50;">
                            <i class="fas fa-calendar-alt me-2" style="color: #32abe3;"></i>
                            <span id="currentYearDisplay">2024</span>
                        </span>
                        <button type="button" class="btn btn-outline-primary btn-sm ms-2" id="nextYearBtn" onclick="nextYear()">
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="addPaymentForm" method="POST" action="{{ route('finance.addPayment') }}" onsubmit="return handleAddPaymentForm(event)">
                        @csrf
                        <input type="hidden" id="student_id" name="student_id">
                        <input type="hidden" id="student_name" name="student_name">
                        <div class="mb-3">
                            <label for="amount" class="form-label fw-semibold">Amount <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="amount" name="amount"
                                   step="0.01" min="1" required placeholder="Enter payment amount">
                            <div class="invalid-feedback">
                                Please enter a valid amount greater than 0.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="payment_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date"
                                   name="payment_date" value="{{ now()->toDateString() }}" required>
                            <div class="invalid-feedback">
                                Please select a payment date.
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="payment_mode" class="form-label fw-semibold">Mode <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_mode" name="payment_mode" required>
                                <option value="">Select Payment Mode</option>
                                {{-- All payment methods (standard and dynamic) --}}
                                @foreach($paymentMethods as $method)
                                    @php
                                        $displayName = $method->name;
                                        $methodType = isset($method->is_standard) && $method->is_standard ? 'standard' : 'dynamic';

                                        // Add account number suffix for dynamic methods with account numbers
                                        if ($method->account_number && $methodType === 'dynamic') {
                                            $lastDigits = substr($method->account_number, -3);
                                            $displayName .= " (***{$lastDigits})";
                                        }
                                    @endphp
                                    <option value="{{ $method->name }}"
                                            data-method-type="{{ $methodType }}"
                                            @if($methodType === 'dynamic') data-method-id="{{ $method->id }}" @endif>
                                        {{ $displayName }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">
                                Please select a payment mode.
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" onclick="showConfirmModal()">Add Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirm Add Payment Modal -->
    <div class="modal fade" id="confirmAddPaymentModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="border-radius: 0.5rem;">
          <div class="modal-header" style="background: #f8f9fa; color: #212529; border-top-left-radius: 0.5rem; border-top-right-radius: 0.5rem;">
            <h5 class="modal-title fw-semibold">Confirm Add Payment</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body" style="padding: 2rem 1.5rem;">
            <p class="mb-3" style="font-size: 1.05rem;">Please review the payment details before proceeding:</p>
            <div class="mb-3">
              <label class="form-label fw-semibold">Student ID</label>
              <div class="form-control bg-light" id="reviewStudentId"></div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Student Name</label>
              <div class="form-control bg-light" id="reviewStudentName"></div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Amount</label>
              <div class="form-control bg-light">₱<span id="reviewAmount"></span></div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Date</label>
              <div class="form-control bg-light" id="reviewDate"></div>
            </div>
            <div class="mb-3">
              <label class="form-label fw-semibold">Mode</label>
              <div class="form-control bg-light" id="reviewMode"></div>
            </div>
            <div class="mb-2 alert alert-secondary">
              Are you sure you want to add this payment? Please double-check the details.
            </div>
          </div>
          <div class="modal-footer" style="border-bottom-right-radius: 0.5rem; border-bottom-left-radius: 0.5rem;">
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" onclick="submitAddPaymentForm()">Yes, Add Payment</button>
          </div>
        </div>
      </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function loadAddPaymentForm(id, name) {
        document.getElementById('student_id').value = id;
        document.getElementById('student_name').value = name;
    }

    function filterByBatch() {
        const batchYear = document.getElementById('batchFilter').value;
        const url = new URL(window.location.href);
        batchYear
            ? url.searchParams.set('batch_year', batchYear)
            : url.searchParams.delete('batch_year');
        window.location.href = url;
    }
    function filterTable() {
        const query = document.getElementById('searchInput').value.toLowerCase();
        document.querySelectorAll('#paymentsTable tbody tr').forEach(row => {
            const idCell   = row.querySelector('td[data-label="Student ID"]').innerText.toLowerCase();
            const nameCell = row.querySelector('td[data-label="Name"]').innerText.toLowerCase();
            row.style.display = (idCell.includes(query) || nameCell.includes(query)) ? '' : 'none';
        });
    }
    function showConfirmModal() {
        // Get form values
        const studentId = document.getElementById('student_id').value;
        const studentName = document.getElementById('student_name').value;
        const amount = document.getElementById('amount').value;
        const paymentDate = document.getElementById('payment_date').value;
        const paymentMode = document.getElementById('payment_mode').value;

        // Validation flag
        let isValid = true;

        // Validate required fields
        if (!studentId || studentId.trim() === '') {
            isValid = false;
        }

        if (!amount || amount.trim() === '') {
            document.getElementById('amount').classList.add('is-invalid');
            isValid = false;
        } else if (parseFloat(amount) <= 0) {
            document.getElementById('amount').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('amount').classList.remove('is-invalid');
        }

        if (!paymentDate || paymentDate.trim() === '') {
            document.getElementById('payment_date').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('payment_date').classList.remove('is-invalid');
        }

        if (!paymentMode || paymentMode.trim() === '') {
            document.getElementById('payment_mode').classList.add('is-invalid');
            isValid = false;
        } else {
            document.getElementById('payment_mode').classList.remove('is-invalid');
        }

        // If there are validation errors, stop here
        if (!isValid) {
            return;
        }

        // All validation passed, populate review modal
        document.getElementById('reviewStudentId').innerText = studentId;
        document.getElementById('reviewStudentName').innerText = studentName;
        document.getElementById('reviewAmount').innerText = parseFloat(amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
        document.getElementById('reviewDate').innerText = new Date(paymentDate).toLocaleDateString('en-US');
        document.getElementById('reviewMode').innerText = paymentMode.charAt(0).toUpperCase() + paymentMode.slice(1);

        const confirmModal = new bootstrap.Modal(document.getElementById('confirmAddPaymentModal'));
        confirmModal.show();
    }
    function submitAddPaymentForm() {
        document.getElementById('addPaymentForm').submit();
    }

    function handleAddPaymentForm(e) {
        e.preventDefault(); // Prevent default submit (even on Enter)
        showConfirmModal();
        return false; // Prevent form from submitting
    }

    // Add real-time validation feedback
    function setupValidationListeners() {
        // Clear validation errors when user starts typing/selecting
        document.getElementById('amount').addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });

        document.getElementById('payment_date').addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });

        document.getElementById('payment_mode').addEventListener('change', function() {
            this.classList.remove('is-invalid');
        });
    }

    // Handle batch management form submission
    document.addEventListener('DOMContentLoaded', function() {
        // Setup validation listeners
        setupValidationListeners();

        // Restore matrix view preference from localStorage
        restoreMatrixViewPreference();

        const manageBatchForm = document.getElementById('manageBatchForm');
        const saveBatchBtn = document.getElementById('saveBatchBtn');

        if (manageBatchForm) {
            manageBatchForm.addEventListener('submit', function(e) {
                // Show loading state
                const btnText = saveBatchBtn.querySelector('.btn-text');
                const spinner = saveBatchBtn.querySelector('.spinner-border');

                btnText.textContent = 'Updating...';
                spinner.classList.remove('d-none');
                saveBatchBtn.disabled = true;

                // Form will submit normally and redirect
            });
        }
    });

    // Function to save matrix view preference to localStorage
    function saveMatrixViewPreference(isMatrixView) {
        localStorage.setItem('financeMatrixViewEnabled', isMatrixView);
        console.log('Matrix view preference saved:', isMatrixView);
    }

    // Function to clear matrix view preference from localStorage
    function clearMatrixViewPreference() {
        localStorage.removeItem('financeMatrixViewEnabled');
        console.log('Matrix view preference cleared');
    }

    // Function to restore matrix view preference from localStorage
    function restoreMatrixViewPreference() {
        const matrixToggle = document.getElementById('matrixViewToggle');
        const listView = document.getElementById('listViewContent');
        const matrixView = document.getElementById('matrixViewContent');

        if (!matrixToggle || !listView || !matrixView) {
            console.log('Matrix view elements not found, skipping preference restoration');
            return;
        }

        try {
            const savedPreference = localStorage.getItem('financeMatrixViewEnabled');
            console.log('Saved matrix view preference:', savedPreference);

            if (savedPreference === 'true') {
                // User previously enabled matrix view, restore it
                console.log('Restoring matrix view preference...');
                matrixToggle.checked = true;

                // Switch to matrix view
                listView.style.display = 'none';
                matrixView.style.display = 'block';

                // Load matrix data
                const tbody = document.getElementById('matrixTableBody');
                if (tbody) {
                    tbody.innerHTML = '<tr><td colspan="15" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading matrix data...</td></tr>';

                    setTimeout(() => {
                        if (typeof loadMatrixData === 'function') {
                            loadMatrixData();
                        } else {
                            console.error('loadMatrixData function not found');
                            tbody.innerHTML = '<tr><td colspan="15" class="text-center py-4 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error: Matrix data loading function not available</td></tr>';
                        }
                    }, 100);
                }
            } else {
                // User previously disabled matrix view or no preference saved, show list view
                console.log('Showing list view (default or user preference)');
                matrixToggle.checked = false;
                listView.style.display = 'block';
                matrixView.style.display = 'none';
            }
        } catch (error) {
            console.error('Error restoring matrix view preference:', error);
            // Fallback to list view on error
            matrixToggle.checked = false;
            listView.style.display = 'block';
            matrixView.style.display = 'none';
        }
    }

    // Function to reset matrix view preference (for debugging/testing)
    function resetMatrixViewPreference() {
        clearMatrixViewPreference();
        console.log('Matrix view preference reset. Refreshing page to apply changes...');
        setTimeout(() => {
            window.location.reload();
        }, 500);
    }

    // Make reset function globally available for debugging
    window.resetMatrixViewPreference = resetMatrixViewPreference;

    // Save preference when page is unloaded
    window.addEventListener('beforeunload', function() {
        const matrixToggle = document.getElementById('matrixViewToggle');
        if (matrixToggle) {
            saveMatrixViewPreference(matrixToggle.checked);
        }
    });

    // Handle page visibility changes (when user switches tabs or minimizes browser)
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            // Page is hidden, save current preference
            const matrixToggle = document.getElementById('matrixViewToggle');
            if (matrixToggle) {
                saveMatrixViewPreference(matrixToggle.checked);
            }
        }
    });

    // Log current matrix view preference for debugging
    console.log('Matrix view preference system initialized');
    console.log('Current localStorage value:', localStorage.getItem('financeMatrixViewEnabled'));

    // ==========================================
    // MATRIX VIEW FUNCTIONALITY
    // ==========================================

    // Matrix data from controller
    const matrixData = @json($matrixData ?? []);
    const matrixMonthlyFee = @json($matrixMonthlyFee ?? 500);
    console.log('Matrix Data Loaded:', matrixData.length, 'students');
    console.log('Matrix Monthly Fee:', matrixMonthlyFee);

    // If no matrix data, create from existing table data
    let actualMatrixData = matrixData;
    if (matrixData.length === 0) {
        console.log('No matrix data from controller, creating from table...');
        console.log('Regular table rows found:', document.querySelectorAll('#paymentsTable tbody tr').length);
        actualMatrixData = createMatrixFromTable();
    }

    // Test if matrix elements exist
    console.log('Matrix toggle element:', document.getElementById('matrixViewToggle'));
    console.log('Matrix table body element:', document.getElementById('matrixTableBody'));

    // Create matrix data from existing table if controller data is empty
    function createMatrixFromTable() {
        const tableRows = document.querySelectorAll('#paymentsTable tbody tr');
        const matrixData = [];

        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length >= 6) {
                const studentName = cells[1].textContent.trim();
                const nameParts = studentName.split(' ');
                const firstName = nameParts[0] || '';
                const lastName = nameParts.slice(1).join(' ') || '';
                const studentId = cells[0].textContent.trim();
                const batch = cells[2].textContent.trim();
                const totalPaid = parseFloat(cells[3].textContent.replace(/[₱,]/g, '')) || 0;

                // Create basic matrix data structure
                const studentData = {
                    student: {
                        student_id: studentId,
                        first_name: firstName,
                        last_name: lastName,
                        batch: batch,
                        total_paid: totalPaid,
                        remaining_balance: parseFloat(cells[4].textContent.replace(/[₱,]/g, '')) || 0
                    },
                    monthly_data: {}
                };

                // Initialize all months
                const currentMonth = new Date().getMonth() + 1;
                for (let month = 1; month <= 12; month++) {
                    studentData.monthly_data[month] = {
                        total: 0,
                        payments: [],
                        is_paid: false,
                        is_current_month: (month === currentMonth)
                    };
                }

                // Apply enhanced payment logic - show partial payments in actual payment months
                // Note: This is simplified fallback logic when no backend matrix data is available
                if (totalPaid > 0) {
                    const monthlyFee = matrixMonthlyFee; // Use dynamic monthly fee from settings

                    // For fallback, assume payment was made in current month (simplified)
                    const currentMonth = new Date().getMonth() + 1;

                    if (totalPaid >= monthlyFee) {
                        // Handle advance payments - mark months sequentially
                        const monthsCovered = Math.floor(totalPaid / monthlyFee);
                        for (let i = 0; i < monthsCovered; i++) {
                            const targetMonth = currentMonth + i;
                            if (targetMonth >= 1 && targetMonth <= 12) {
                                studentData.monthly_data[targetMonth].is_paid = true;
                                studentData.monthly_data[targetMonth].total = monthlyFee;
                                studentData.monthly_data[targetMonth].status = 'paid';
                            }
                        }

                        // Handle remaining partial amount
                        const remainingAmount = totalPaid % monthlyFee;
                        if (remainingAmount > 0) {
                            const nextMonth = currentMonth + monthsCovered;
                            if (nextMonth >= 1 && nextMonth <= 12) {
                                studentData.monthly_data[nextMonth].is_paid = false;
                                studentData.monthly_data[nextMonth].total = remainingAmount;
                                studentData.monthly_data[nextMonth].remaining_balance = monthlyFee - remainingAmount;
                                studentData.monthly_data[nextMonth].status = 'partial';
                            }
                        }
                    } else {
                        // Partial payment in current month
                        studentData.monthly_data[currentMonth].is_paid = false;
                        studentData.monthly_data[currentMonth].total = totalPaid;
                        studentData.monthly_data[currentMonth].remaining_balance = monthlyFee - totalPaid;
                        studentData.monthly_data[currentMonth].status = 'partial';
                    }
                }

                matrixData.push(studentData);
            }
        });

        console.log('Created matrix data from table:', matrixData.length, 'students');
        return matrixData;
    }

    // Matrix view toggle functionality
    document.getElementById('matrixViewToggle').addEventListener('change', function() {
        console.log('Matrix toggle clicked, checked:', this.checked);

        const listView = document.getElementById('listViewContent');
        const matrixView = document.getElementById('matrixViewContent');

        console.log('List view element:', listView);
        console.log('Matrix view element:', matrixView);

        if (this.checked) {
            // Switch to matrix view
            console.log('Switching to matrix view...');
            listView.style.display = 'none';
            matrixView.style.display = 'block';

            // Save preference to localStorage
            saveMatrixViewPreference(true);

            // Add a simple test row first
            const tbody = document.getElementById('matrixTableBody');
            tbody.innerHTML = '<tr><td colspan="15" class="text-center py-4"><i class="fas fa-spinner fa-spin me-2"></i>Loading matrix data...</td></tr>';

            setTimeout(() => {
                loadMatrixData();
            }, 100);
        } else {
            // Switch to list view
            console.log('Switching to list view...');
            listView.style.display = 'block';
            matrixView.style.display = 'none';

            // Save preference to localStorage
            saveMatrixViewPreference(false);
        }
    });

    // Update dynamic legend with current period
    function updateMatrixLegend() {
        const now = new Date();
        const currentMonth = now.toLocaleString('default', { month: 'long' });
        const currentYear = now.getFullYear();

        const legendElement = document.getElementById('matrixCurrentPeriod');
        if (legendElement) {
            legendElement.innerHTML = `Current Period: <strong>${currentMonth} ${currentYear}</strong>`;
        }
    }



    // Global variables for pagination (12-month pages)
    let availableYears = [];
    let currentYearIndex = 0;
    let globalMonthRange = [];
    let filteredMatrixData = [];
    const pageSize = 12; // months per page

    // Build a consolidated, sorted month range from filtered data
    function buildGlobalMonthRange(data) {
        const map = new Map(); // key: yyyy-mm, value: {month, year, display}
        data.forEach(d => {
            const range = Array.isArray(d.month_range) ? d.month_range : [];
            range.forEach(m => {
                if (!m || typeof m.month === 'undefined' || typeof m.year === 'undefined') return;
                const key = `${m.year}-${String(m.month).padStart(2,'0')}`;
                if (!map.has(key)) {
                    map.set(key, { month: m.month, year: m.year, display: m.display || new Date(m.year, m.month-1).toLocaleString('default',{month:'short', year:'numeric'}) });
                }
            });
        });
        const arr = Array.from(map.values()).sort((a,b)=> (a.year*100 + a.month) - (b.year*100 + b.month));
        return arr;
    }

    // Generate page indices based on globalMonthRange
    function generateYearPagination() {
        availableYears = [];
        const totalMonths = globalMonthRange.length;
        const pageCount = Math.max(1, Math.ceil(totalMonths / pageSize));
        for (let p = 0; p < pageCount; p++) availableYears.push(p);

        // Choose page containing current month if present; else last page
        const now = new Date();
        const cm = now.getMonth() + 1;
        const cy = now.getFullYear();
        let todayIdx = -1;
        for (let i = 0; i < totalMonths; i++) {
            const m = globalMonthRange[i];
            if (m.month === cm && m.year === cy) { todayIdx = i; break; }
        }
        currentYearIndex = todayIdx >= 0 ? Math.floor(todayIdx / pageSize) : Math.max(0, pageCount - 1);

        if (availableYears.length > 0) {
            showYearData(availableYears[currentYearIndex]);
        }
    }

    // Show data for specific page (12-month slice)
    function showYearData(pageIndex) {
        // Update current page index
        currentYearIndex = availableYears.indexOf(pageIndex);
        if (currentYearIndex === -1) currentYearIndex = 0;

        // Update range display (start – end)
        const dispEl = document.getElementById('currentYearDisplay');
        if (dispEl) {
            const startIdx = pageIndex * pageSize;
            const startEntry = globalMonthRange[startIdx];
            const endEntry = globalMonthRange[Math.min(globalMonthRange.length - 1, startIdx + pageSize - 1)];
            if (startEntry && endEntry) {
                const y1 = startEntry.year;
                const y2 = endEntry.year;
                dispEl.textContent = (y1 === y2) ? `${y1}` : `${y1} – ${y2}`;
            } else {
                dispEl.textContent = '-';
            }
        }

        // Update navigation buttons
        updateNavigationButtons();

        // Update month headers for selected page
        updateMonthHeadersForYear(pageIndex);

        // Load matrix data for selected page
        loadMatrixDataForYear(pageIndex);
    }

    // Update navigation buttons state
    function updateNavigationButtons() {
        const prevBtn = document.getElementById('prevYearBtn');
        const nextBtn = document.getElementById('nextYearBtn');

        if (prevBtn) {
            // Disable previous button if we're at the first year
            prevBtn.disabled = currentYearIndex <= 0;
            prevBtn.style.opacity = currentYearIndex <= 0 ? '0.5' : '1';
            prevBtn.style.cursor = currentYearIndex <= 0 ? 'not-allowed' : 'pointer';
        }

        if (nextBtn) {
            // Disable next button if we're at the last year
            nextBtn.disabled = currentYearIndex >= availableYears.length - 1;
            nextBtn.style.opacity = currentYearIndex >= availableYears.length - 1 ? '0.5' : '1';
            nextBtn.style.cursor = currentYearIndex >= availableYears.length - 1 ? 'not-allowed' : 'pointer';
        }
    }

    // Navigate to previous year
    function previousYear() {
        if (currentYearIndex > 0) {
            currentYearIndex--;
            showYearData(availableYears[currentYearIndex]);
        }
    }

    // Navigate to next year
    function nextYear() {
        if (currentYearIndex < availableYears.length - 1) {
            currentYearIndex++;
            showYearData(availableYears[currentYearIndex]);
        }
    }

    // Update month headers for specific page based on globalMonthRange
    function updateMonthHeadersForYear(pageIndex) {
        const startIdx = pageIndex * pageSize;
        for (let i = 0; i < pageSize; i++) {
            const headerElement = document.getElementById(`month${i + 1}`);
            if (!headerElement) continue;
            const entry = globalMonthRange[startIdx + i];
            headerElement.textContent = entry ? entry.display : '-';
        }
    }

    // Update month headers based on payment start month (for backward compatibility)
    function updateMonthHeaders(startMonth, startYear) {
        const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        // Calculate the 12 months starting from the payment start month
        for (let i = 0; i < 12; i++) {
            const monthIndex = (startMonth - 1 + i) % 12;
            const year = startYear + Math.floor((startMonth - 1 + i) / 12);
            const headerElement = document.getElementById(`month${i + 1}`);
            if (headerElement) {
                headerElement.textContent = `${monthNames[monthIndex]} ${year}`;
            }
        }
    }

    // Load matrix data into table
    function loadMatrixData() {
        console.log('Loading matrix data...');
        const tbody = document.getElementById('matrixTableBody');
        tbody.innerHTML = '';

        // Update legend with current period
        updateMatrixLegend();

        // Build filtered dataset based on search and batch filters first
        const searchInput = document.getElementById('searchInput');
        const batchFilterEl = document.getElementById('batch_year');
        const searchValue = searchInput ? searchInput.value.toLowerCase() : '';
        const batchFilter = batchFilterEl ? batchFilterEl.value : '';

        filteredMatrixData = actualMatrixData.filter(data => {
            const studentName = (data.student.display_name || `${data.student.last_name || ''}, ${data.student.first_name || ''}`).toLowerCase();
            const studentId = data.student.student_id.toString().toLowerCase();
            const matchesSearch = !searchValue || studentName.includes(searchValue) || studentId.includes(searchValue);
            const matchesBatch = !batchFilter || data.student.batch.toString() === batchFilter.toString();
            return matchesSearch && matchesBatch;
        });

        // Build global month range from filtered data
        globalMonthRange = buildGlobalMonthRange(filteredMatrixData);
        if (globalMonthRange.length === 0) {
            tbody.innerHTML = '<tr><td colspan="17" class="text-center text-muted py-4"><i class="fas fa-info-circle me-2"></i>No students found matching the current filters.</td></tr>';
            return;
        }

        // Generate pagination pages and show current page
        generateYearPagination();
    }

    // Load matrix data for specific page
    function loadMatrixDataForYear(pageIndex) {
        console.log('Loading matrix data for page:', pageIndex);
        const tbody = document.getElementById('matrixTableBody');
        tbody.innerHTML = '';

        // Update legend with current period
        updateMatrixLegend();



        console.log('Matrix data available (filtered):', filteredMatrixData.length);
        if (filteredMatrixData.length === 0) {
            console.warn('No matrix data available!');
            tbody.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-4"><i class="fas fa-exclamation-triangle me-2"></i>No students found.</td></tr>';
            return;
        }

        // Get current filters (with null checks)
        const filteredData = filteredMatrixData;
        console.log('Filtered matrix data:', filteredData.length, 'students');

        // Create matrix rows
        filteredData.forEach(data => {
            const row = document.createElement('tr');
            row.className = 'student-matrix-row';
            row.dataset.studentId = data.student.student_id;

            // Student name cell (sticky)
            const studentCell = document.createElement('td');
            studentCell.className = 'matrix-student-cell text-center align-middle';
            studentCell.style.position = 'sticky';
            studentCell.style.left = '0';
            studentCell.style.backgroundColor = 'white';
            studentCell.style.zIndex = '5';
            studentCell.style.border = '1px solid #dee2e6';
            studentCell.style.minWidth = '200px';
            studentCell.style.height = '60px';
            studentCell.style.verticalAlign = 'middle';
            studentCell.innerHTML = `
                <div class="d-flex flex-column align-items-center justify-content-center h-100">
                    <div class="fw-semibold">${data.student.display_name || data.student.last_name + ', ' + data.student.first_name}</div>
                    <small class="text-muted">ID: ${data.student.student_id}</small>
                </div>
            `;
            row.appendChild(studentCell);

            // Batch cell (sticky)
            const batchCell = document.createElement('td');
            batchCell.className = 'text-center align-middle';
            batchCell.style.position = 'sticky';
            batchCell.style.left = '200px';
            batchCell.style.backgroundColor = 'white';
            batchCell.style.zIndex = '5';
            batchCell.style.border = '1px solid #dee2e6';
            batchCell.style.minWidth = '80px';
            batchCell.style.height = '60px';
            batchCell.style.verticalAlign = 'middle';
            batchCell.innerHTML = `<span class="badge bg-primary">${data.student.batch}</span>`;
            row.appendChild(batchCell);



            // Generate 12 month columns for the selected page
            const startIdx = pageIndex * pageSize;
            for (let i = 0; i < pageSize; i++) {
                const monthCell = document.createElement('td');
                monthCell.className = 'payment-matrix-cell text-center align-middle';
                monthCell.style.minWidth = '90px';
                monthCell.style.height = '60px';
                monthCell.style.backgroundColor = 'white';
                monthCell.style.border = '1px solid #dee2e6';
                monthCell.style.verticalAlign = 'middle';

                // Use the consolidated global month range for header alignment
                const entry = globalMonthRange[startIdx + i];
                if (!entry) {
                    monthCell.innerHTML = `<span style="color: transparent;">-</span>`;
                    row.appendChild(monthCell);
                    continue;
                }
                const actualMonth = entry.month;
                const actualYear = entry.year;

                // Check if this month should be active based on payment start date
                const startMonth = data.student.payment_start_month || 1;
                const startYear = data.student.payment_start_year || new Date().getFullYear();

                // Check if this month is after the payment start date
                const isAfterStartDate = (actualYear > startYear) ||
                                       (actualYear === startYear && actualMonth >= startMonth);

                // Calculate if this month should be active
                const currentDate = new Date();
                const currentMonth = currentDate.getMonth() + 1;
                const currentYear = currentDate.getFullYear();

                // Check if this month is in the past
                const isPastMonth = (actualYear < currentYear) ||
                                  (actualYear === currentYear && actualMonth < currentMonth);

                // Check if this is the current month
                const isCurrentMonth = (actualYear === currentYear && actualMonth === currentMonth);

                // Check payment status for this month
                const monthKey = actualMonth + '_' + actualYear;
                const monthData = data.monthly_data[monthKey];

                if (!isAfterStartDate) {
                    // Before payment start date - show blank
                    monthCell.innerHTML = `<span style="color: transparent;">-</span>`;
                } else if (monthData && monthData.is_paid) {
                    // Paid months - Green check
                    monthCell.innerHTML = `
                        <i class="fas fa-check text-success" title="Paid: ₱${monthData.total.toLocaleString()}"></i>
                    `;
                } else if (monthData && monthData.status === 'partial') {
                    // Partial payment
                    monthCell.innerHTML = `
                        <div class="text-warning fw-bold d-flex flex-column align-items-center" title="Partial Payment: ₱${monthData.total.toLocaleString()} paid, ₱${monthData.remaining_balance.toLocaleString()} remaining" style="font-size: 0.75rem; line-height: 1.2;">
                            <span style="font-size: 0.65rem; opacity: 0.8;">needs</span>
                            <span style="font-size: 0.8rem; font-weight: 700;">₱${monthData.remaining_balance.toLocaleString()}</span>
                        </div>
                    `;
                } else if (isCurrentMonth) {
                    // Current month - Leave blank (no X)
                    monthCell.innerHTML = `<span style="color: transparent;">-</span>`;
                } else if (isPastMonth) {
                    // Past months - Leave blank (no X)
                    monthCell.innerHTML = `<span style="color: transparent;">-</span>`;
                } else {
                    // Future months - blank
                    monthCell.innerHTML = `<span style="color: transparent;">-</span>`;
                }

                row.appendChild(monthCell);
            }

            // Total Paid cell (non-sticky)
            const totalCell = document.createElement('td');
            totalCell.className = 'text-center fw-bold align-middle';
            totalCell.style.minWidth = '100px';
            totalCell.style.height = '60px';
            totalCell.style.verticalAlign = 'middle';
            totalCell.style.border = '1px solid #dee2e6';
            totalCell.innerHTML = `₱${data.student.total_paid.toLocaleString()}`;
            row.appendChild(totalCell);

            // Payable Amount cell (sticky right: 100px)
            const payableCell = document.createElement('td');
            payableCell.className = 'text-center fw-bold align-middle';
            payableCell.style.position = 'sticky';
            payableCell.style.right = '100px';
            payableCell.style.backgroundColor = 'white';
            payableCell.style.zIndex = '5';
            payableCell.style.border = '1px solid #dee2e6';
            payableCell.style.minWidth = '100px';
            payableCell.style.height = '60px';
            payableCell.style.verticalAlign = 'middle';
            const payableAmount = data.student.total_due || 0;
            payableCell.innerHTML = `₱${payableAmount.toLocaleString()}`;
            row.appendChild(payableCell);

            // Status cell (sticky right: 0)
            const statusCell = document.createElement('td');
            statusCell.className = 'text-center fw-bold align-middle';
            statusCell.style.position = 'sticky';
            statusCell.style.right = '0';
            statusCell.style.backgroundColor = 'white';
            statusCell.style.zIndex = '5';
            statusCell.style.border = '1px solid #dee2e6';
            statusCell.style.minWidth = '140px';
            statusCell.style.height = '60px';
            statusCell.style.verticalAlign = 'middle';
            const totalDueForStatus = (data.student.total_due || 0);
            const totalPaidForStatus = (data.student.total_paid || 0);
            const remainingForStatus = Math.max(0, totalDueForStatus - totalPaidForStatus);
            if (remainingForStatus <= 0 && totalDueForStatus > 0) {
                // Fully paid: plain text in green
                statusCell.innerHTML = '<span class="text-success">FULLY PAID</span>';
            } else {
                // Not fully paid: plain text, no yellow highlight
                statusCell.innerHTML = `₱${remainingForStatus.toLocaleString()} remaining`;
            }
            row.appendChild(statusCell);

            tbody.appendChild(row);
        });

        // Show message if no data
        if (filteredData.length === 0) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 17; // Student + Batch + 12 months + Total Paid + Payable + Status
            cell.className = 'text-center text-muted py-4';
            cell.innerHTML = '<i class="fas fa-info-circle me-2"></i>No students found matching the current filters.';
            row.appendChild(cell);
            tbody.appendChild(row);
        }
    }

    // Apply filters to matrix when search or batch filter changes (with null checks)
    const searchInputEl = document.getElementById('searchInput');
    const batchFilterEl = document.getElementById('batch_year'); // Use the actual batch filter ID

    if (searchInputEl) {
        searchInputEl.addEventListener('input', function() {
            if (document.getElementById('matrixViewToggle').checked) {
                loadMatrixData();
            }
        });
    }

    if (batchFilterEl) {
        batchFilterEl.addEventListener('change', function() {
            if (document.getElementById('matrixViewToggle').checked) {
                loadMatrixData();
            }
        });
    }

    // ==========================================
    // UNIFIED FILTER HANDLING
    // ==========================================

    // Handle search filter changes
    function handleFilterChange() {
        const matrixToggle = document.getElementById('matrixViewToggle');

        if (matrixToggle && matrixToggle.checked) {
            // If in matrix view, update matrix table
            loadMatrixData();
        } else {
            // If in list view, use original filter function
            if (typeof filterTable === 'function') {
                filterTable();
            }
        }
    }

    // Handle batch filter changes
    function handleBatchFilterChange(selectElement) {
        const matrixToggle = document.getElementById('matrixViewToggle');

        if (matrixToggle && matrixToggle.checked) {
            // If in matrix view, update matrix table only
            loadMatrixData();
        } else {
            // If in list view, submit form to reload with new batch filter
            selectElement.form.submit();
        }
    }

    // Override the original filterTable function to work with matrix view
    const originalFilterTable = window.filterTable;
    window.filterTable = function() {
        const matrixToggle = document.getElementById('matrixViewToggle');

        if (matrixToggle && matrixToggle.checked) {
            // If in matrix view, don't use original filter, use matrix filter instead
            loadMatrixData();
        } else if (originalFilterTable) {
            // If in list view, use original filter function
            originalFilterTable();
        }
    };

    

    // Auto-open Manage Batch modal when navigated with hash from Settings
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash === '#manage-batches') {
            setTimeout(() => {
                const modalEl = document.getElementById('manageBatchModal');
                if (modalEl) {
                    try {
                        const modal = (bootstrap.Modal.getOrCreateInstance)
                            ? bootstrap.Modal.getOrCreateInstance(modalEl)
                            : new bootstrap.Modal(modalEl);
                        modal.show();
                        // Clean the hash to avoid re-opening on refresh
                        history.replaceState(null, '', window.location.pathname + window.location.search);
                    } catch (e) {
                        // Fallback simple show if Bootstrap instance fails
                        modalEl.classList.add('show');
                        modalEl.style.display = 'block';
                    }
                }
            }, 200); // Small delay to ensure DOM and Bootstrap are ready
        }
    });

</script>



@endsection