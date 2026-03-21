@if ($reportType === 'total_paid_per_student')
    <!-- Report Description -->
    @if(isset($description))
        <div class="alert alert-info mb-3">
            <h5 class="mb-0">{{ $description }}</h5>
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Class Batch</th>
                <th>Total Paid</th>
                <th>Remaining Balance</th>
                <th>Payable Amount</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($perTypeData) && $perTypeData->isNotEmpty())
                @foreach($perTypeData as $row)
                    <tr>
                        <td>
                            @if(isset($row->last_payment_date) && $row->last_payment_date)
                                <span style="white-space:nowrap">{{ \Carbon\Carbon::parse($row->last_payment_date)->format('M d, Y') }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $row->student_id }}</td>
                        <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                        <td>C{{ $row->batch_year }}</td>
                        <td>₱{{ number_format($row->total_by_mode ?? 0, 2) }}</td>
                        <td>
                            @php
                                $payableAmount = $batchPayableAmounts[$row->batch_year] ?? 0;
                                // For a per-method row, show remaining after this mode's grouped total (note: not cumulative)
                                $remainingBalance = $payableAmount - ($row->total_by_mode ?? 0);
                            @endphp
                            @if($remainingBalance < 0)
                                <span class="text-success">₱0.00</span>
                            @else
                                <span class="{{ $remainingBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    ₱{{ number_format($remainingBalance, 2) }}
                                </span>
                            @endif
                        </td>
                        <td>₱{{ number_format($payableAmount ?? 0, 2) }}</td>
                        <td>{{ $row->payment_mode ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            @else
                @forelse ($data as $row)
                    <tr>
                        <td>
                            @if(isset($row->last_payment_date) && $row->last_payment_date)
                                <span style="white-space:nowrap">{{ \Carbon\Carbon::parse($row->last_payment_date)->format('M d, Y') }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>{{ $row->student_id }}</td>
                        <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                        <td>C{{ $row->batch_year }}</td>
                        <td>₱{{ number_format($row->total_paid ?? 0, 2) }}</td>
                        <td>
                            @php
                                // Calculate remaining balance using payable amount minus total paid
                                $payableAmount = $row->payable_amount ?? 0;
                                $totalPaid = $row->total_paid ?? 0;
                                $remainingBalance = $payableAmount - $totalPaid;
                            @endphp
                            @if($remainingBalance < 0)
                                <span class="text-success">₱0.00</span>
                            @else
                                <span class="{{ $remainingBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    ₱{{ number_format($remainingBalance, 2) }}
                                </span>
                            @endif
                        </td>
                        <td>₱{{ number_format($row->payable_amount ?? 0, 2) }}</td>
                        <td>
                            @if($row->payment_methods)
                                {{ $row->payment_methods }}
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No students found for the selected criteria.</td>
                    </tr>
                @endforelse
            @endif
        </tbody>
    </table>

    <!-- Payment Mode Totals Summary -->
    @php
        // Normalize paymentModeBreakdown
        $paymentModeBreakdown = $paymentModeBreakdown ?? [];

        // Determine authoritative total in order of precedence:
        // 1) sum of paymentModeBreakdown (if present) - authoritative when modes are calculated server-side
        // 2) perTypeData grouped totals (student+payment method)
        // 3) fallback to sum of data->total_paid
        if (!empty($paymentModeBreakdown)) {
            $totalPaid = array_sum($paymentModeBreakdown);
        } elseif (isset($perTypeData) && $perTypeData instanceof \Illuminate\Support\Collection && $perTypeData->isNotEmpty()) {
            $totalPaid = $perTypeData->sum('total_by_mode');
        } else {
            $totalPaid = collect($data)->sum('total_paid');
        }
    @endphp

    @if($totalPaid > 0)
        <div class="mt-4">
            <h5 class="text-primary">Payment Summary</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-white" style="background:#ff8c00;">
                            <h6 class="mb-0">Total Amount</h6>
                        </div>
                        <div class="card-body">
                            <h4 class="text-dark mb-0">₱{{ number_format($totalPaid, 2) }}</h4>
                        </div>
                    </div>
                </div>
                @if(count($paymentModeBreakdown) > 0)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Payment Mode Breakdown</h6>
                            </div>
                            <div class="card-body">
                                @foreach($paymentModeBreakdown as $mode => $amount)
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $mode }}:</span>
                                        <strong>₱{{ number_format($amount, 2) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

@elseif ($reportType === 'total_paid_per_month')
    @if(isset($description))
        <div class="alert alert-info mb-3">
            <h5 class="mb-0">{{ $description }}</h5>
        </div>
    @endif

    {{-- Daily transaction history data --}}
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date</th>
                <th>Student ID</th>
                <th>Student Name</th>
                <th>Class Batch</th>
                <th>Total Amount Paid</th>
                <th>Remaining Balance</th>
                <th>Payable Amount</th>
                <th>Payment Method</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $row)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($row->payment_date)->format('M d, Y') }}</td>
                    <td>{{ $row->student_id }}</td>
                    <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                    <td>C{{ $row->batch_year }}</td>
                    <td>₱{{ number_format($row->total_paid, 2) }}</td>
                    <td>
                        @if($row->remaining_balance < 0)
                            <span class="text-success">₱0.00</span>
                        @else
                            <span class="{{ $row->remaining_balance > 0 ? 'text-danger' : 'text-success' }}">
                                ₱{{ number_format($row->remaining_balance, 2) }}
                            </span>
                        @endif
                    </td>
                    <td>₱{{ number_format($row->payable_amount ?? 0, 2) }}</td>
                    <td>
                        @if($row->payment_mode)
                            {{ $row->payment_mode }}
                        @else
                            <span class="text-muted">N/A</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center text-muted">No transaction data found for the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <!-- Grand total removed as requested -->
    </table>

    <!-- Payment Mode Totals Summary for Per Month -->
    @php
        $paymentModeBreakdown = $paymentModeBreakdown ?? [];
        if (!empty($paymentModeBreakdown)) {
            $totalPaid = array_sum($paymentModeBreakdown);
        } elseif (isset($perTypeData) && $perTypeData instanceof \Illuminate\Support\Collection && $perTypeData->isNotEmpty()) {
            $totalPaid = $perTypeData->sum('total_by_mode');
        } else {
            $totalPaid = $data->sum('total_paid');
        }
    @endphp

    @if($totalPaid > 0)
        <div class="mt-4">
            <h5 class="text-primary">Payment Summary</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-white" style="background:#ff8c00;">
                            <h6 class="mb-0">Total Amount</h6>
                        </div>
                        <div class="card-body">
                            <h4 class="text-dark mb-0">₱{{ number_format($totalPaid, 2) }}</h4>
                        </div>
                    </div>
                </div>
                @if(count($paymentModeBreakdown) > 0)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Payment Mode Breakdown</h6>
                            </div>
                            <div class="card-body">
                                @foreach($paymentModeBreakdown as $mode => $amount)
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $mode }}:</span>
                                        <strong>₱{{ number_format($amount, 2) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

@elseif ($reportType === 'total_paid_per_year')
    <!-- Report Description -->
    @if(isset($description))
        <div class="alert alert-info mb-3">
            <h5 class="mb-0">{{ $description }}</h5>
        </div>
    @endif

    @if($data->isNotEmpty() && isset($data->first()->student_id))
        {{-- Individual student data (when filtering by year) --}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class Batch</th>
                    <th>Payable Amount</th>
                    <th>Total Paid</th>
                    <th>Remaining Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr>
                        <td>{{ $row->student_id }}</td>
                        <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                        <td>C{{ $row->batch_year }}</td>
                        <td>₱{{ number_format($row->payable_amount ?? 0, 2) }}</td>
                        <td>₱{{ number_format($row->total_paid ?? 0, 2) }}</td>
                        <td>
                            @php
                                // Calculate remaining balance using payable amount minus total paid
                                $payableAmount = $row->payable_amount ?? 0;
                                $totalPaid = $row->total_paid ?? 0;
                                $remainingBalance = $payableAmount - $totalPaid;
                            @endphp
                            @if($remainingBalance < 0)
                                <span class="text-success">₱0.00</span>
                            @else
                                <span class="{{ $remainingBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    ₱{{ number_format($remainingBalance, 2) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No students found for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        {{-- Aggregated data (when showing yearly totals) --}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Class Batch</th>
                    <th>Year</th>
                    <th>Total Paid</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr>
                        <td>C{{ $row->batch_year }}</td>
                        <td>{{ $row->year }}</td>
                        <td>₱{{ number_format($row->total_paid, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No payment data found for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <!-- Payment Mode Totals Summary for Per Year -->
    @php
        $paymentModeBreakdown = $paymentModeBreakdown ?? [];
        if (!empty($paymentModeBreakdown)) {
            $totalPaid = array_sum($paymentModeBreakdown);
        } elseif (isset($perTypeData) && $perTypeData instanceof \Illuminate\Support\Collection && $perTypeData->isNotEmpty()) {
            $totalPaid = $perTypeData->sum('total_by_mode');
        } else {
            $totalPaid = $data->sum('total_paid');
        }
    @endphp

    @if($totalPaid > 0)
        <div class="mt-4">
            <h5 class="text-primary">Payment Summary</h5>
            <div class="row">
                <div class="col-md-6">
                        <div class="card">
                        <div class="card-header text-white" style="background:#ff8c00;">
                            <h6 class="mb-0">Total Amount</h6>
                        </div>
                        <div class="card-body">
                            <h4 class="text-dark mb-0">₱{{ number_format($totalPaid, 2) }}</h4>
                        </div>
                    </div>
                </div>
                @if(count($paymentModeBreakdown) > 0)
                    <div class="col-md-6">
                            <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Payment Mode Breakdown</h6>
                            </div>
                            <div class="card-body">
                                @foreach($paymentModeBreakdown as $mode => $amount)
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $mode }}:</span>
                                        <strong>₱{{ number_format($amount, 2) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif

@elseif ($reportType === 'total_paid_per_batch_year')
    <!-- Report Description -->
    @if(isset($description))
        <div class="alert alert-info mb-3">
            <h5 class="mb-0">{{ $description }}</h5>
        </div>
    @endif

    @if($data->isNotEmpty() && isset($data->first()->student_id))
        {{-- Individual student data (when filtering by year range) --}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Name</th>
                    <th>Class Batch</th>
                    <th>Payable Amount</th>
                    <th>Total Paid</th>
                    <th>Remaining Balance</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr>
                        <td>{{ $row->student_id }}</td>
                        <td>{{ $row->first_name }} {{ $row->last_name }}</td>
                        <td>C{{ $row->batch_year }}</td>
                        <td>₱{{ number_format($row->payable_amount ?? 0, 2) }}</td>
                        <td>₱{{ number_format($row->total_paid ?? 0, 2) }}</td>
                        <td>
                            @php
                                // Calculate remaining balance using payable amount minus total paid
                                $payableAmount = $row->payable_amount ?? 0;
                                $totalPaid = $row->total_paid ?? 0;
                                $remainingBalance = $payableAmount - $totalPaid;
                            @endphp
                            @if($remainingBalance < 0)
                                <span class="text-success">₱0.00</span>
                            @else
                                <span class="{{ $remainingBalance > 0 ? 'text-danger' : 'text-success' }}">
                                    ₱{{ number_format($remainingBalance, 2) }}
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No students found for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        {{-- Aggregated data (when showing batch/year totals) --}}
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Class Batch</th>
                    <th>Year</th>
                    <th>Total Paid</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($data as $row)
                    <tr>
                        <td>C{{ $row->batch_year }}</td>
                        <td>{{ $row->year }}</td>
                        <td>₱{{ number_format($row->total_paid, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">No payment data found for the selected criteria.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

@elseif ($reportType === 'per_year')


    <!-- Report Description -->
    @if(isset($description))
        <div class="alert alert-info mb-3">
            <h5 class="mb-0">{{ $description }}</h5>
        </div>
    @endif

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Class Batch</th>
                <th>Month</th>
                <th>Year</th>
                <th>Total Collected</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($data as $row)
                <tr>
                    <td>C{{ $row->batch_year }}</td>
                    <td>
                        @if(isset($row->payment_month) && $row->payment_month)
                            {{ \Carbon\Carbon::create()->month($row->payment_month)->format('F') }}
                        @else
                            <span class="text-muted">-</span>
                        @endif
                    </td>
                    <td>{{ $row->payment_year }}</td>
                    <td>₱{{ number_format($row->total_paid, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">No payment data found for the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <!-- Grand total removed as requested -->
    </table>

    <!-- Payment Mode Totals Summary for Per Batch Year -->
    @php
        $paymentModeBreakdown = $paymentModeBreakdown ?? [];
        if (!empty($paymentModeBreakdown)) {
            $totalPaid = array_sum($paymentModeBreakdown);
        } elseif (isset($perTypeData) && $perTypeData instanceof \Illuminate\Support\Collection && $perTypeData->isNotEmpty()) {
            $totalPaid = $perTypeData->sum('total_by_mode');
        } else {
            $totalPaid = $data->sum('total_paid');
        }
    @endphp

    @if($totalPaid > 0)
        <div class="mt-4">
            <h5 class="text-primary">Payment Summary</h5>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-white" style="background:#ff8c00;">
                            <h6 class="mb-0">Total Amount</h6>
                        </div>
                        <div class="card-body">
                            <h4 class="text-dark mb-0">₱{{ number_format($totalPaid, 2) }}</h4>
                        </div>
                    </div>
                </div>
                @if(count($paymentModeBreakdown) > 0)
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">Payment Mode Breakdown</h6>
                            </div>
                            <div class="card-body">
                                @foreach($paymentModeBreakdown as $mode => $amount)
                                    <div class="d-flex justify-content-between">
                                        <span>{{ $mode }}:</span>
                                        <strong>₱{{ number_format($amount, 2) }}</strong>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    @endif
@endif