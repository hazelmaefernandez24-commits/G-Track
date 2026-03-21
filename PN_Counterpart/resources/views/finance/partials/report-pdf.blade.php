<!DOCTYPE html>
<html lang="en">
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
<head>
  <meta charset="UTF-8" />
  <title>Finance Report</title>
  <style>
  body {
    font-family: Arial, sans-serif;
    font-size: 12px;
    line-height: 1.4;
    margin: 0;
    padding: 20px;
  }
  .header {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    border-bottom: 2px solid rgb(95, 194, 240);
    padding-bottom: 15px;
    margin-bottom: 20px;
    padding-top: 20px;
  }
  .logo {
    width: 55px;
    height: 65px;
    object-fit: contain;
    display: block;
  }
  .company-name {
    font-family: Arial, sans-serif;
    font-size: 16px;
    font-weight: bold;
    color: rgb(1, 5, 12);
    letter-spacing: 0.5px;
    margin: 0;
    line-height: 1.3;
  }
  .company-address {
    font-size: 12px;
    color: #333;
    text-align: center;
    margin-top: 8px;
    margin-bottom: 0;
    width: 100%;
    font-weight: normal;
    line-height: 1.4;
  }
    .report-title {
      font-size: 14px;
      font-weight: bold;
      text-align: center;
      color: #111;
      margin-top: 20px;
      margin-bottom: 5px;
      letter-spacing: 0.5px;
      line-height: 1.4;
    }
    .report-subtitle {
      font-size: 12px;
      text-align: center;
      color: #111;
      margin-bottom: 20px;
      font-weight: normal;
      line-height: 1.4;
    }
    .summary-box {
      background: #f8fafc;
      border: 1px solid #dbe1ec;
      border-radius: 8px;
      padding: 15px 20px;
      margin-bottom: 20px;
      max-width: 450px;
      margin-left: auto;
      margin-right: auto;
    }
    .summary-header {
      padding: 6px 10px;
      border-radius: 6px;
      color: #fff;
      font-weight: bold;
      display: inline-block;
      margin-bottom: 8px;
      font-size: 11px;
    }
  .summary-total { background: #ff8c00; } /* orange */
  .summary-modes { background: transparent; color: #111; font-size: 15px;} /* no background, dark text; enlarged by 5px */
    .summary-box p {
      margin: 0 0 8px 0;
      font-size: 11px;
      display: grid;
      grid-template-columns: 1fr auto;
      align-items: center;
      color: #111;
      font-weight: normal;
      line-height: 1.5;
    }
    .breakdown-item {
      padding-left: 14px; /* indent breakdown lines under the total label */
    }
    .summary-total-collection {
      padding-left: 14px; /* align with breakdown items */
      font-weight: 700;
      font-size: 13px;
      margin-top: 4px;
    }
    .summary-label {
      font-weight: bold;
      color: #111;
    }
    .summary-value {
      font-weight: bold;
      color: #27416b;
      margin-left: 15px;
    }
    .section-title {
      font-size: 13px;
      font-weight: bold;
      color: #111;
      margin-top: 25px;
      margin-bottom: 8px;
      text-align: center;
      line-height: 1.4;
    }
    .description {
      font-size: 11px;
      color: #111;
      margin-bottom: 15px;
      text-align: center;
      line-height: 1.4;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 10px;
      margin-bottom: 20px;
      background: #fff;
    }
    thead {
      background: #f3f6fa;
    }
    th, td {
      padding: 8px 6px;
      text-align: left;
      border-bottom: 1px solid #e3e6ed;
      color: #222;
      font-weight: normal;
      line-height: 1.3;
    }
    th {
      font-weight: bold;
      color: #1a2947;
      letter-spacing: 0.3px;
      font-size: 10px;
      background: #f3f6fa;
    }
    .remaining-balance {
      font-size: 10px;
      color: #b85c00;
      font-weight: bold;
    }
    tbody tr:last-child td {
      border-bottom: none;
    }
    tbody tr:nth-child(even) {
      background: #f8fafc;
    }
    .report-footer {
      text-align: right;
      font-size: 10px;
      color: #888;
      margin-top: 20px;
      border-top: 1px solid #e3e6ed;
      padding-top: 10px;
      font-style: italic;
      line-height: 1.4;
    }

    .text-center {
      text-align: center;
    }

    .text-right {
      text-align: right;
      font-weight: 600;
    }

    .text-left {
      text-align: left;
    }
    .amount-positive {
      color: #38a169;
      font-weight: 600;
    }

    .amount-negative {
      color: #e53e3e;
      font-weight: 600;
    }

    .amount-neutral {
      color: #4a5568;
      font-weight: 600;
    }

    .no-data {
      text-align: center;
      color: #a0aec0;
      font-style: italic;
      padding: 30px;
      font-size: 12px;
    }

    .footer {
      margin-top: 30px;
      text-align: center;
      font-size: 9px;
      color: #a0aec0;
      border-top: 1px solid #e2e8f0;
      padding-top: 15px;
    }
  </style>
</head>
<body>
  <div class="receipt-container">
    <div class="header">
      <img src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('photos/pnph.png'))) }}" alt="PN Logo" class="logo">
      <span class="company-name">PASSERELLES NUMERIQUES PHILIPPINES FOUNDATION, INC.</span>
    </div>
    <div class="company-address">The Bird Building, New Era St., Barangay Luz, Cebu City, Philippines</div>

    @if($reportType === 'student_payment_history')
      <div class="report-title">STUDENT PAYMENT HISTORY REPORT</div>
    @else
      <div class="report-title">PAYMENT SUMMARY REPORT</div>
      <div class="report-subtitle"><strong>{{ $description ?? 'Payment Summary Report' }}</strong></div>
    @endif

    @if($reportType === 'student_payment_history')
      {{-- Student Information Section --}}
      <div class="summary-box">
          <div class="summary-header summary-total">Total Amount</div>
          <p><span class="summary-label">Student Name:</span> <span class="summary-value">{{ $data->first()->first_name }} {{ $data->first()->last_name }}</span></p>
          <p><span class="summary-label">Student ID:</span> <span class="summary-value">{{ $data->first()->student_id }}</span></p>
          <p><span class="summary-label">Class Batch:</span> <span class="summary-value">C{{ $data->first()->batch_year }}</span></p>
          <p><span class="summary-label">Total Amount Paid:</span> <span class="summary-value">Php {{ number_format($data->sum('total_paid'), 2) }}</span></p>
        </div>
    @elseif(isset($modeTotals) && count($modeTotals) > 0)
    <div class="summary-box">
      <div class="summary-header summary-modes">Payment Mode Breakdown</div>
  <p class="summary-total-collection"><span class="summary-label">Total collection:</span> <span class="summary-value">Php {{ number_format(array_sum($modeTotals), 2) }}</span></p>
  @foreach($modeTotals as $mode => $total)
  <p class="breakdown-item"><span class="summary-label">{{ $mode }} Payments:</span> <span class="summary-value">Php {{ number_format($total, 2) }}</span></p>
  @endforeach
    </div>
    @endif

    <div class="section-title">
      @switch($reportType)
        @case('total_paid_per_student')
          Total Paid Amount Per Student
          @break
        @case('total_paid_per_month')
          Monthly Payment Summary
          @break
        @case('total_paid_per_year')
          Monthly Payment Breakdown
          @break
        @case('total_paid_per_batch_year')
          Yearly Payment Summary
          @break
        @case('per_year')
          Yearly Payment Summary
          @break
        @case('student_payment_history')
          Individual Student Payment Report
          @break
        @default
          Total Paid Amount Per Student
      @endswitch
    </div>
    <div class="description">
      An overview of all Parents Counterpart payments for monitoring and updates.
    </div>

    <table>
      <thead>
        <tr>
          @switch($reportType)
            @case('total_paid_per_student')
            @case('total_paid_per_year')
            @case('total_paid_per_batch_year')
              @if($reportType === 'total_paid_per_student')
                <th>Date</th>
              @endif
              <th>Student ID</th>
              <th>Name</th>
              <th>Paid (Php)</th>
              <th>Remaining Balance (Php)</th>
              @if($reportType === 'total_paid_per_student')
                <th>Payment Method</th>
              @endif
              @break
            @case('total_paid_per_month')
              {{-- Daily transaction history headers --}}
              <th>Date</th>
              <th>Student ID</th>
              <th>Student Name</th>
              <th>Class Batch</th>
              <th>Total Amount Paid (Php)</th>
              <th>Remaining Balance (Php)</th>
              <th>Payable Amount (Php)</th>
              <th>Payment Method</th>
              @break
            @case('student_payment_history')
              {{-- Student payment history headers --}}
              <th>Date</th>
              <th>Amount Paid (Php)</th>
              <th>Remaining Balance (Php)</th>
              <th>Payable Amount (Php)</th>
              <th>Payment Method</th>
              @break
            @case('per_year')
              <th>Batch</th>
              <th>Month</th>
              <th>Year</th>
              <th>Paid (Php)</th>
              @break
            @default
              <th colspan="3">No records available</th>
          @endswitch
        </tr>
      </thead>
      <tbody>
        @if($reportType === 'total_paid_per_student' && isset($perTypeData) && $perTypeData->isNotEmpty())
            @foreach($perTypeData as $row)
            <tr>
              @php
                $payable = $batchPayableAmounts[$row->batch_year] ?? 0;
                $remaining = $payable - ($row->total_by_mode ?? 0);
              @endphp
              <td>
                @if(isset($row->last_payment_date) && $row->last_payment_date)
                  {{ \Carbon\Carbon::parse($row->last_payment_date)->format('M d, Y') }}
                @else
                  -
                @endif
              </td>
              <td>{{ $row->student_id }}</td>
              <td>{{ $row->first_name }} {{ $row->last_name }}</td>
              <td>Php{{ number_format($row->total_by_mode ?? 0, 2) }}</td>
              <td>Php{{ number_format($remaining, 2) }}</td>
              <td>{{ $row->payment_mode ?? 'N/A' }}</td>
            </tr>
            @endforeach
        @else
            @foreach($data as $row)
            <tr>
          @if($reportType === 'total_paid_per_month')
            {{-- Daily transaction history data --}}
            <td><span style="white-space:nowrap">{{ \Carbon\Carbon::parse($row->payment_date)->format('M d, Y') }}</span></td>
            <td>{{ $row->student_id }}</td>
            <td>{{ $row->first_name }} {{ $row->last_name }}</td>
            <td>C{{ $row->batch_year }}</td>
            <td>Php{{ number_format($row->total_paid, 2) }}</td>
            <td>Php{{ number_format($row->remaining_balance, 2) }}</td>
            <td>Php{{ number_format($row->payable_amount ?? 0, 2) }}</td>
            <td>{{ $row->payment_mode ?? 'N/A' }}</td>
          @elseif($reportType === 'student_payment_history')
            {{-- Student payment history data --}}
            <td>{{ \Carbon\Carbon::parse($row->payment_date)->format('M d, Y') }}</td>
            <td>Php{{ number_format($row->total_paid, 2) }}</td>
            <td>Php{{ number_format($row->remaining_balance, 2) }}</td>
            <td>Php{{ number_format($row->payable_amount, 2) }}</td>
            <td>{{ $row->payment_mode ?? 'N/A' }}</td>
          @elseif($reportType === 'per_year')
            {{-- Per year data --}}
            <td>C{{ $row->batch_year }}</td>
            <td>
              @if(isset($row->payment_month) && $row->payment_month)
                {{ \Carbon\Carbon::create()->month($row->payment_month)->format('F') }}
              @else
                -
              @endif
            </td>
            <td>{{ $row->payment_year }}</td>
            <td>Php{{ number_format($row->total_paid, 2) }}</td>
          @else
            {{-- Individual student data --}}
            @php
              $payable = $batchPayableAmounts[$row->batch_year] ?? 0;
              $remaining = $payable - $row->total_paid;
            @endphp
            @if($reportType === 'total_paid_per_student')
              <td>
                @if(isset($row->last_payment_date) && $row->last_payment_date)
                  {{ \Carbon\Carbon::parse($row->last_payment_date)->format('M d, Y') }}
                @else
                  -
                @endif
              </td>
            @endif
            <td>{{ $row->student_id }}</td>
            <td>{{ $row->first_name }} {{ $row->last_name }}</td>
            <td>Php{{ number_format($row->total_paid,2) }}</td>
            <td>Php{{ number_format($remaining,2) }}</td>
            @if($reportType === 'total_paid_per_student')
              <td>{{ $row->payment_methods ?? 'N/A' }}</td>
            @endif
          @endif
        </tr>
        @endforeach
        @endif
      </tbody>
    </table>
    <div class="report-footer">
      Generated on {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y \a\t g:i A') }} (Philippine Time)
    </div>
    </div>
</body>
</html>