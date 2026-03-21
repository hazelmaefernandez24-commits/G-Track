<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Monthly Counterpart Payment Reminder</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f8f9fa; padding: 20px; }
        .email-container { background: #fff; border-radius: 10px; padding: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 3px solid #32abe3; padding-bottom: 20px; margin-bottom: 30px; }
        .logo { font-size: 24px; font-weight: bold; color: #32abe3; margin-bottom: 10px; }
        .reminder-title { font-size: 22px; color: #32abe3; margin: 0; }
        .greeting { font-size: 18px; font-weight: 600; color: #2c3e50; margin-bottom: 20px; }
        .main-message { font-size: 16px; margin-bottom: 20px; }
        .balance-info { background: #fff4e6; border-left: 4px solid #ff9500; padding: 15px 20px; margin: 20px 0; border-radius: 5px; }
        .balance-amount { font-size: 20px; font-weight: bold; color: #cc7a00; }
        .months-unpaid { color: #d9534f; font-weight: bold; }
        .batch-info { font-size: 16px; margin-bottom: 10px; }
        .signature { margin-top: 25px; font-weight: 600; color: #2c3e50; }
        .footer { margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6; text-align: center; color: #6c757d; font-size: 14px; }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo">PN Philippines</div>
            <h2 class="reminder-title">Monthly Counterpart Payment Reminder</h2>
        </div>

        @if(empty($detailedMessage))
            <div class="greeting">
                Dear {{ $student->user_fname }} {{ $student->user_lname }},
            </div>
        @endif

        <div class="main-message">
            @if(!empty($detailedMessage))
                {!! $detailedMessage !!}
            @else
                @if(!empty($reminderData['months_unpaid']) && count($reminderData['months_unpaid']) > 0)
                    @php
                        $monthsCount = count($reminderData['months_unpaid']);
                        $monthsText = $monthsCount === 1 ? 'month' : 'months';
                        $firstMonth = $reminderData['months_unpaid'][0];
                        $lastMonth = end($reminderData['months_unpaid']);
                        $monthRange = $monthsCount == 1 ? $firstMonth : "from {$firstMonth} to {$lastMonth}";
                    @endphp
                    We've noticed that your parent counterpart payment has not been settled for the past <strong>{{ $monthsCount }} {{ $monthsText }}</strong>—{{ $monthRange }}. This is a gentle reminder to arrange payment for these {{ $monthsText }}.
                @else
                    We've noticed that your parent counterpart payment has an outstanding balance. This is a gentle reminder to arrange payment.
                @endif
            @endif
        </div>

        @if(empty($detailedMessage))
            <div class="balance-info">
                @if(!empty($reminderData['months_unpaid']) && count($reminderData['months_unpaid']) > 0)
                    <div style="margin-bottom: 10px;">
                        <strong>Unpaid Months:</strong>
                        <span class="months-unpaid">
                            {{ implode(', ', $reminderData['months_unpaid']) }}
                        </span>
                    </div>
                @endif
                <div><strong>Outstanding Balance:</strong> <span class="balance-amount">₱{{ number_format($student->studentDetails->remaining_balance ?? $student->remaining_balance ?? 0, 2) }}</span></div>
            </div>
        @endif

        <div class="main-message">
            Please inform your parent or guardian about these unpaid months and make arrangements to settle the balance as soon as possible. Your cooperation ensures the continued strength and service of PN Philippines.
        </div>

        <div class="signature">
            Best regards,<br>
            Finance Department
        </div>

        <div class="footer">
            <p>This is an automated reminder from PN Philippines Finance Department.</p>
            <p>&copy; {{ date('Y') }} Passerelles Numeriques Philippines. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
