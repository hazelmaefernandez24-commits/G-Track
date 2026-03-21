<!DOCTYPE html>
<html>
<head>
   <title font-weight=>Proof of Payment Declined</title>
</head>
<body>
    <p>Dear <strong>{{ optional(optional($payment->student)->user)->user_fname }} {{ optional(optional($payment->student)->user)->user_lname }}</strong>,</p>
    <p>We are reaching out regarding your uploaded proof of payment for <strong>₱{{ number_format($payment->amount, 2) }}</strong> for
     <strong>Counterpart Payment</strong>.  Upon review, we have identified the following issue: <strong>{{ $reason }}</strong>.<br></p>
    <p>To complete the verification process, kindly <strong>please resubmit a corrected proof</strong> at your earliest convenience. <br>
    Should you have any questions or require further assistance, feel free to reach out to us.</p>

    <p>Best regards,</p>
    <p><strong>Finance Team</strong></p>
</body>
</html>





