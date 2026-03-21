@extends('layouts.finance')

@section('title', 'Student Payment History')
@section('page-title', 'Payment History for ' . $student->first_name . ' ' . $student->last_name)

@section('content')
<div class="container-fluid">
    <div class="card shadow-sm">
        <div class="card-header text-white" style="background-color: #FF9933;">
            <h5 class="mb-0">Payment History for {{ $student->first_name }} {{ $student->last_name }}</h5>
        </div>
        <div class="card-body">
            <p><strong>Student ID:</strong> {{ $student->student_id }}</p>
            <p><strong>Batch Year:</strong> {{ $student->batch_year }}</p>
            <p><strong>Total Paid:</strong> ₱{{ number_format($student->payments->where('status', 'Approved')->sum('amount'), 2) }}</p>
            <p><strong>Remaining Balance:</strong> ₱{{ number_format((500 * 24) - $student->payments->where('status', 'Approved')->sum('amount'), 2) }}</p>

            <h6>Payment History</h6>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Mode</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>₱{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_date }}</td>
                            <td>{{ ucfirst($payment->payment_mode) }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status === 'Approved' ? 'success' : ($payment->status === 'Declined' ? 'danger' : 'warning') }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection