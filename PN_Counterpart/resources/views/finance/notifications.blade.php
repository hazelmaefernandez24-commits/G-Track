@extends('layouts.finance')

@section('title', 'Payment Notifications')
@section('page-title', 'Payment Notifications')

@push('styles')
<style>
    :root {
        --primary:    #005f99;
        --accent:     #FF9933;
        --bg-light:   #f5f6fa;
        --card-bg:    #ffffff;
        --text-dark:  #2c3e50;
        --text-muted: #6c757d;
        --border:     #e1e4e8;
        --success:    #28a745;
        --danger:     #dc3545;
        --warning:    #ffc107;
    }

    body, .container-fluid {
        font-family: 'Inter', sans-serif;
        background: var(--bg-light);
        color: var(--text-dark);
    }

    .card-modern {
        background: var(--card-bg);
        border: none;
        border-radius: .75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    .card-header-modern {
        background: var(--accent);
        color: #fff;
        padding: 1rem 1.5rem;
        border-top-left-radius: .75rem;
        border-top-right-radius: .75rem;
    }
    .card-header-modern h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.2rem;
    }

    .table-responsive {
        overflow-x: auto;
        border-radius: 0 0 .75rem .75rem;
    }
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        font-size: .9rem;
        min-width: 600px; /* allow scroll on small */
    }
    .table-modern thead th {
        background: #f1f3f5;
        color: var(--text-dark);
        font-weight: 600;
        padding: .75rem;
        text-transform: uppercase;
        border-bottom: 2px solid var(--border);
        position: sticky;
        top: 0;
        z-index: 2;
    }
    .table-modern tbody td {
        padding: .75rem;
        border-bottom: 1px solid var(--border);
    }
    .table-modern tbody tr:nth-child(even) {
        background: var(--bg-light);
    }

    .badge-status {
        font-size: .75rem;
        padding: .3em .8em;
        border-radius: 1rem;
        text-transform: capitalize;
    }
    .badge-approved { background: var(--success); color: #fff; }
    .badge-declined { background: var(--danger);  color: #fff; }
    .badge-pending  { background: var(--warning); color: #212529; }

    .btn-sm {
        padding: .3rem .75rem;
        font-size: .85rem;
    }

    /* MOBILE: keep horizontal scroll on small viewports */
    @media (max-width: 576px) {
        .table-modern {
            min-width: 500px;
        }
    }

    /* STATUS BOX STYLES */
    .status-box {
      display: inline-block;
      text-align: center;
      padding: 0.08em 0.65em;
      border-radius: 0.25em;
      font-weight: 600;
      font-size: 0.95em;
      background: #e2e3e5;
      color: #41464b;
      border: 1.2px solid transparent;
    }
    .status-approved {
      background:rgb(23, 193, 108);
      color:rgb(247, 253, 250);
      border-color: #badbcc;
    }
    .status-declined, .status-deleted {
      background:rgb(234, 28, 45);
      color:rgb(239, 236, 236);
      border-color: #f5c2c7;
    }
    .status-pending {
      background:rgb(248, 203, 56);
      color:rgb(102, 102, 101);
      border-color: #ffe69c;
    }

    .status-default {
      background: #e2e3e5;
      color: #41464b;
      border-color: #d3d6d8;
    }
</style>
@endpush

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="margin-top: 1rem;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="py-4 container-fluid">
    <div class="mb-4 card card-modern">
        <div class="card-header-modern">
            <h5>Recent Payments</h5>
        </div>
        <div class="p-0 card-body">
            <div class="table-responsive">
                <table class="mb-0 table-modern">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Sender Name</th>
                            <th>Date</th>
                            <th>Amount</th>
                            <th>Mode of Payment</th>
                            <th>Reference</th>
                            <th>Payment Proof</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                      @foreach ($pendingPayments as $payment)
                        <tr>
                          <td data-label="Student">{{ $payment->student->user->user_fname ?? 'N/A' }} {{ $payment->student->user->user_lname ?? 'N/A' }}</td>
                          <td data-label="Sender Name">{{ $payment->sender_name ?? 'N/A' }}</td>
                          <td data-label="Date">{{ \Carbon\Carbon::parse($payment->payment_date)->format('Y-m-d') }}</td>
                          <td data-label="Amount">₱{{ number_format($payment->amount,2) }}</td>
                          <td data-label="Mode of Payment">{{ $payment->payment_mode }}</td>
                          <td data-label="Reference">{{ $payment->reference_number ?? 'N/A' }}</td>
                          <td data-label="Proof">
                            @if($payment->payment_proof)
                              <button type="button"
                                      class="p-0 btn btn-link"
                                      onclick="showImageModal('{{ asset('storage/' . $payment->payment_proof) }}')">
                                View Proof
                              </button>
                            @else
                              N/A
                            @endif
                          </td>
                          <td data-label="Status">
                            @php
                              $status = strtolower($payment->status);
                              $boxClass = match($status) {
                                  'approved' => 'status-box status-approved',
                                  'declined' => 'status-box status-declined',
                                  'pending' => 'status-box status-pending',
                                  'cashier verified' => 'status-box status-pending',
                                  'deleted' => 'status-box status-declined',
                                  default => 'status-box status-default'
                              };
                            @endphp
                            <span class="{{ $boxClass }}">{{ ucfirst($payment->status) }}</span>
                          </td>
                          <td data-label="Actions">
                            @php
                                $sessionUser = session('user');
                                $localUser = \App\Models\User::where('user_email', $sessionUser['user_email'])->first();
                                $userRole = $localUser ? $localUser->user_role : 'finance';
                            @endphp
                            
                            @if($userRole === 'cashier' && $payment->status == 'Pending')
                              <form method="POST" action="{{ route('cashier.verifyPayment',['payment'=>$payment->payment_id]) }}" class="d-inline">
                                @csrf
                                <button type="submit" name="status" value="Approved" class="btn btn-sm btn-success">Verify</button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="showDeclineModal('{{ $payment->payment_id }}', 'cashier')">Decline</button>
                              </form>
                            @elseif($userRole === 'cashier' && $payment->status == 'Approved')
                              <span class="text-success">✓ Approved</span>
                            @elseif($userRole === 'cashier' && $payment->status == 'Declined')
                              <span class="text-danger">✗ Declined</span>
                            @elseif($userRole === 'cashier' && $payment->status == 'Cashier Verified')
                              <span class="text-info">Sent to Finance</span>
                            @elseif($userRole === 'finance' && $payment->status == 'Cashier Verified')
                              <form method="POST" action="{{ route('finance.verifyPayment',['payment'=>$payment->payment_id]) }}" class="d-inline">
                                @csrf
                                <button type="submit" name="status" value="Approved" class="btn btn-sm btn-success">Final Approve</button>
                                <button type="button" class="btn btn-sm btn-danger" onclick="showDeclineModal('{{ $payment->payment_id }}', 'finance')">Decline</button>
                              </form>
                            @elseif($userRole === 'finance' && $payment->status == 'Pending')
                              <span class="text-muted">Waiting for cashier review</span>
                            @elseif($userRole === 'finance' && $payment->status == 'Approved')
                              <span class="text-success">✓ Final Approved</span>
                            @elseif($userRole === 'finance' && $payment->status == 'Declined')
                              <span class="text-danger">✗ Final Declined</span>
                            @endif
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Decline Modal -->
<div class="modal fade" id="declineModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-md">
    <div class="modal-content" style="border-radius: 12px;">
      <div class="modal-header" style="background: #dc3545; color: #fff; border-top-left-radius: 12px; border-top-right-radius: 12px;">
        <h5 class="modal-title" style="font-weight: 600;">Decline Payment Proof</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding: 2rem 1.5rem;">
        <form id="declineForm" method="POST">
          @csrf
          <div class="mb-3">
            <label for="remarks" class="form-label" style="font-weight: 500; color: #2c3e50;">
              Please provide a reason for declining this payment proof:
            </label>
            <textarea class="form-control" id="remarks" name="remarks" rows="5" required
              style="resize: vertical; border-radius: 8px; font-size: 1rem; padding: 1rem; border: 1px solid #e1e4e8;"></textarea>
          </div>
          <input type="hidden" name="status" value="Declined">
          <button type="submit" class="btn btn-danger w-100" style="font-weight: 600; font-size: 1.08rem; border-radius: 8px;">
            Submit Reason & Decline
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Payment Proof</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="text-center modal-body">
        <img id="proofImage" src="" alt="Payment Proof" style="max-width:100%; max-height:70vh; border-radius:8px; box-shadow:0 2px 8px rgba(0,0,0,0.08);" />
      </div>
    </div>
  </div>
</div>

<script>
  function showDeclineModal(id, userRole) {
    const modal = new bootstrap.Modal(document.getElementById('declineModal'));
    const form  = document.getElementById('declineForm');
    
    if (userRole === 'cashier') {
      form.action = `{{ url('cashier/payments/verify') }}/${id}`;
    } else {
      form.action = `{{ url('finance/payments/verify') }}/${id}`;
    }
    
    modal.show();
  }

  function showImageModal(imageUrl) {
    document.getElementById('proofImage').src = imageUrl;
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
  }
</script>
@endsection
