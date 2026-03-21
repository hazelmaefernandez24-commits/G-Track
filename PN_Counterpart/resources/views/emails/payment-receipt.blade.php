<!DOCTYPE html>
<html>
<head>
   <title><strong>Counterpart Payment</strong></title>
</head>
<body>
    <p>Dear <strong>{{ optional(optional($payment->student)->user)->user_fname }} {{ optional(optional($payment->student)->user)->user_lname }}</strong>,</p>
    <p>Your payment of <strong>₱{{ number_format($payment->amount, 2) }}</strong> for <strong>{{ $monthsText ?? 'Counterpart Payment' }}</strong> has been successfully processed and recorded. Attached here is your payment receipt for your reference.<br>
        Let us know if you have any questions!<br><br></p>

    <p>Best regards,</p>
    <p><strong>Finance Department</strong><br>
    <strong>{{ \App\Models\FinanceSetting::get('finance_department_email', 'finance@pnphilippines.com') }}</strong></p>
</body>
</html>

