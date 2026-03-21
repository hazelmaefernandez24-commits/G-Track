<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Receipt</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 14px;
            margin: 0; padding: 0;
            line-height: 1.5;
             }
        .receipt-container {
             max-width: 700px;
             width: 100%;
             margin: 0 auto;
             padding: 0;
             box-sizing: border-box;
            }
        .company-name {
             font-size: 20px;
             font-weight: bold;
             color: #1a2947;
             margin: 0;
             line-height: 1.1;
             }
        .company-address {
             text-align: center;
             font-size: 14px;
             margin: 6px 0 18px 0;
             color: #222;
             }
        .receipt-title {
            color: #0066cc;
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            margin: 18px 0 8px 0;
            }
        .receipt-date {
             text-align: right;
             font-size: 14px;
             margin-bottom: 12px;
             margin-right: 24px;
             }
        .receipt-content {
            margin: 16px 0 10px 0;
            font-size: 14px;
            }
        .details {
            margin-bottom: 18px;
            font-size: 14px;
             }
        .signature-section {
            margin-top: 24px;
            font-weight: bold;
            font-size: 12px;
             }
        .footer {
            text-align: center;
            margin-top: 18px;
            font-size: 12px;
            font-weight: normal;
             }
    </style>
</head>
<body>
    <div class="receipt-container">
        <table width="100%" style="margin-bottom:0;">
            <tr>
                <td style="width:80px;">
                    <img src="{{ public_path('photos/pnlogo.png') }}" alt="PN Logo" style="width:70px;">
                </td>
                <td style="text-align:left;">
                    <span class="company-name">PASSERELLES NUMERIQUES PHILIPPINES FOUNDATION, INC.</span>
                </td>
            </tr>
        </table>
        <div class="company-address">
            The Bird Building, New Era St., Barangay Luz, Cebu City, Philippines
        </div>
        <div class="receipt-title">ACKNOWLEDGEMENT RECEIPT</div>
        <div class="receipt-date">
            Date: <strong style="text-decoration:underline;">{{ date('F j, Y') }}</strong>
        </div>
        <div class="receipt-content">
            <p>Dear <strong>{{ $student->user->user_fname }} {{ $student->user->user_lname }}</strong>:</p>
            <p style="text-indent:30px;">
                We acknowledge the receipt of your payment amounting to <strong style="text-decoration:underline;">Php {{ number_format($payment->amount, 2) }}</strong> as payment for <strong style="text-decoration:underline;">Parents' Counterpart contribution.</strong>
                As of this date, your remaining balance stands at <strong style="text-decoration:underline;">Php {{ number_format($student->remaining_balance, 2) }}</strong>.
            </p>
        </div>
        <div class="details">
            <p>Below are the details of your transaction:</p>
            <p>Student Name:<strong> {{ $student->user->user_fname }} {{ $student->user->user_lname }}</strong></p>
            <p>Student ID: <strong>{{ $student->student_id }}</strong></p>
            @if($payment->sender_name)
            <p>Sender Name: <strong>{{ $payment->sender_name }}</strong></p>
            @endif
            <p>Payment Date: <strong>{{ \Carbon\Carbon::parse($payment->payment_date)->format('F j, Y') }}</strong></p>
            <p>Payment Mode: <strong>{{ ucfirst($payment->payment_mode) }}</strong></p>
            <p>Amount Paid: <strong>Php {{ number_format($payment->amount, 2) }}</strong></p>
            <p>Remaining Balance: <strong>Php {{ number_format($student->remaining_balance, 2) }}</strong></p>
        </div>
        <div class="signature-section">
            <p>This information has been duly verified by the finance team.</p>
        </div>
        <div class="footer">
            This is an electronically generated receipt. No signature is required.
        </div>
    </div>
</body>
</html>
