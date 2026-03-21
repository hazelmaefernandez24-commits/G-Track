function loadPaymentHistory(studentId) {
    document.getElementById('student_id').value = studentId;

    // Fetch payment history via AJAX
    fetch(`/finance/payments/history/${studentId}`)
        .then(response => response.json())
        .then(data => {
            const paymentHistory = document.getElementById('paymentHistory');
            paymentHistory.innerHTML = '';

            data.forEach((payment, index) => {
                paymentHistory.innerHTML += `
                    <tr>
                        <td>${index + 1}</td>
                        <td>₱${payment.amount.toFixed(2)}</td>
                        <td>${payment.payment_date}</td>
                        <td>${payment.payment_mode}</td>
                    </tr>
                `;
            });
        });
}
