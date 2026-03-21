@extends('layouts.finance')

@section('title', 'Payments')
@section('page-title', 'Payment Management')

@section('content')
<style>
    /* Custom styles for enhanced UI */
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .table-sticky thead th {
        position: sticky;
        top: 0;
        background: #f8f9fa;
        z-index: 1;
    }
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }
    .card-header {
        background: linear-gradient(90deg, #FF9933, #FFAA55);
        color: white;
    }
    .modal-content {
        border-radius: 0.5rem;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    .form-control:focus, .form-select:focus {
        border-color: #FF9933;
        box-shadow: 0 0 0 0.2rem rgba(255, 153, 51, 0.25);
    }
</style>

<div class="container-fluid py-4">
    <!-- Success Message -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show fade-in" role="alert" aria-live="assertive">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Batch Filter -->
    <div class="row mb-4">
        <div class="col-md-4">
            <label for="batchFilter" class="form-label fw-semibold">Filter by Batch Year</label>
            <select id="batchFilter" class="form-select" onchange="filterByBatch()" aria-label="Filter by batch year">
                <option value="">All Batches</option>
                @foreach ($batches as $batch)
                    <option value="{{ $batch->batch_year }}" {{ request('batch_year') == $batch->batch_year ? 'selected' : '' }}>
                        {{ $batch->batch_year }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Payment Table -->
    <div class="card shadow-sm border-0">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">Student Payments</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover table-sticky">
                    <thead>
                        <tr>
                            <th scope="col">Student ID</th>
                            <th scope="col">Name</th>
                            <th scope="col">Batch Year</th>
                            <th scope="col">Total Paid</th>
                            <th scope="col">Remaining Balance</th>
                            <th scope="col">Payables</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students->sortBy('last_name') as $student)
                            @if (!request('batch_year') || $student->batch_year == request('batch_year'))
                                <tr>
                                    <td>{{ $student->student_id }}</td>
                                    <td>{{ $student->first_name }} {{ $student->last_name }}</td>
                                    <td>{{ $student->batch_year }}</td>
                                    <td>₱{{ number_format($student->total_paid, 2) }}</td> <!-- Use the centralized logic -->
                                    <td>₱{{ number_format($student->remaining_balance, 2) }}</td> <!-- Use the centralized logic -->
                                    <td>₱{{ number_format($student->batchInfo->total_due ?? 0, 2) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#paymentModal" 
                                                    onclick="loadAddPaymentForm('{{ $student->student_id }}')" aria-label="Add payment for {{ $student->first_name }}">
                                                Add Payment
                                            </button>
                                            <a href="{{ route('finance.getPaymentHistory', ['studentId' => $student->student_id]) }}" class="btn btn-info btn-sm">
                                                View History
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    

    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Add Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addPaymentForm" method="POST" action="{{ route('finance.addPayment') }}">
                        @csrf
                        <input type="hidden" id="student_id" name="student_id">
                        <div class="mb-3">
                            <label for="amount" class="form-label fw-semibold">Amount</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" min="1" required aria-label="Payment amount">
                        </div>
                        <div class="mb-3">
                            <label for="payment_date" class="form-label fw-semibold">Payment Date</label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ date('Y-m-d') }}" required aria-label="Payment date">
                        </div>
                        <div class="mb-3">
                            <label for="payment_mode" class="form-label fw-semibold">Payment Mode</label>
                            <select class="form-select" id="payment_mode" name="payment_mode" required aria-label="Payment mode">
                                @php
                                    $paymentMethods = \App\Models\PaymentMethod::where('is_active', true)->get();
                                @endphp
                                @if($paymentMethods->count() > 0)
                                    @foreach($paymentMethods as $method)
                                        <option value="{{ strtolower(str_replace(' ', '_', $method->name)) }}">{{ $method->name }}</option>
                                    @endforeach
                                @else
                                    {{-- Fallback to original hardcoded options --}}
                                    <option value="cash">Cash</option>
                                    <option value="gcash">GCash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                @endif
                            </select>
                        </div>
                        <button type="submit" class="btn btn-success">Add Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Decline Payment Modal -->
    <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="declineModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="declineModalLabel">Decline Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="declineForm" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="remarks" class="form-label fw-semibold">Reason for Declining</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" required aria-label="Reason for declining payment"></textarea>
                        </div>
                        <input type="hidden" name="status" value="Declined">
                        <button type="submit" class="btn btn-danger">Decline Payment</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function loadAddPaymentForm(studentId) {
        document.getElementById('student_id').value = studentId;
    }

    function filterByBatch() {
        const batchYear = document.getElementById('batchFilter').value;
        const url = new URL(window.location.href);
        if (batchYear) {
            url.searchParams.set('batch_year', batchYear);
        } else {
            url.searchParams.delete('batch_year');
        }
        window.location.href = url.toString();
    }

    function showDeclineModal(paymentId) {
        const modal = new bootstrap.Modal(document.getElementById('declineModal'));
        const form = document.getElementById('declineForm');
        form.action = `{{ url('finance/payments/verify') }}/${paymentId}`;
        modal.show();
    }
</script>
@endsection