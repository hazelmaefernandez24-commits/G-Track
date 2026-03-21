<!DOCTYPE html>
<html lang="en">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap" rel="stylesheet">
<head>
    <meta charset="UTF-8" />
    <title>Student Payment History - {{ $student->first_name }} {{ $student->last_name }}</title>
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
        .summary-box p {
            margin: 0 0 8px 0;
            font-size: 11px;
            display: flex;
            justify-content: space-between;
            color: #111;
            font-weight: normal;
            line-height: 1.5;
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
        .no-data {
            text-align: center;
            color: #a0aec0;
            font-style: italic;
            padding: 30px;
            font-size: 12px;
        }
        .status {
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
        }
        .status.approved {
            background: #d4edda;
            color: #155724;
        }
        .status.added-by-finance {
            background: #cce7ff;
            color: #004085;
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

        <div class="report-title">STUDENT PAYMENT HISTORY REPORT</div>
        <div class="report-subtitle"><strong>{{ $student->first_name }} {{ $student->last_name }} ({{ $student->student_id }})</strong></div>

        {{-- Student Information Section --}}
        <div class="summary-box">
            <p><span class="summary-label">Student Name:</span> <span class="summary-value">{{ $student->first_name }} {{ $student->last_name }}</span></p>
            <p><span class="summary-label">Student ID:</span> <span class="summary-value">{{ $student->student_id }}</span></p>
            <p><span class="summary-label">Class Batch:</span> <span class="summary-value">C{{ $student->batch }}</span></p>
            <p><span class="summary-label">Total Amount Paid:</span> <span class="summary-value">Php {{ number_format($student->total_paid, 2) }}</span></p>
            <p><span class="summary-label">Total Amount Due:</span> <span class="summary-value">Php {{ number_format($student->total_due, 2) }}</span></p>
            <p><span class="summary-label">Remaining Balance:</span> <span class="summary-value">Php {{ number_format(abs($student->remaining_balance), 2) }}</span></p>
        </div>

        <div class="section-title">Payment Transaction History</div>

        @if($payments->isEmpty())
            <div class="no-data">
                No payment transactions found for this student.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Payment Mode</th>
                        <th>Reference Number</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($payments as $payment)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($payment->payment_date)->format('M d, Y') }}</td>
                            <td>Php {{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_mode ?? 'N/A' }}</td>
                            <td>{{ $payment->reference_number ?? 'N/A' }}</td>
                            <td>
                                <span class="status {{ strtolower(str_replace(' ', '-', $payment->status)) }}">
                                    {{ $payment->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <div class="report-footer">
            Generated on {{ \Carbon\Carbon::now('Asia/Manila')->format('F d, Y \a\t g:i A') }} (Philippine Time)
        </div>
    </div>
</body>
</html>
