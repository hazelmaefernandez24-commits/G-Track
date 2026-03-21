@extends('layouts.student')

@section('title', 'Upload Payment Proof')
@section('page-title', 'Upload Payment Proof')

@section('content')
<div class="container">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="shadow-sm card">
        <div class="text-white card-header" style="background-color: #FF9933;">
            <h5 class="mb-0">Submit Payment Proof</h5>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('student.uploadPaymentProof') }}" enctype="multipart/form-data" id="paymentForm">
                @csrf
                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="amount" class="form-label">Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">₱</span>
                            <input type="number"
                                class="form-control"
                                id="amount"
                                name="amount"
                                step="0.01"
                                min="1"
                                value="{{ old('amount') }}"
                                required>
                        </div>
                        @error('amount')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-6">
                        <label for="payment_date" class="form-label">Payment Date</label>
                        <input type="date"
                            class="form-control"
                            id="payment_date"
                            name="payment_date"
                            value="{{ old('payment_date', date('Y-m-d')) }}"
                            max="{{ date('Y-m-d') }}"
                            required>
                        @error('payment_date')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="mb-3 col-md-6">
                        <label for="payment_mode" class="form-label">Payment Mode</label>
                        <select class="form-select" id="payment_mode" name="payment_mode">
                            <option value="" hidden>Select Payment Mode</option>
                            @foreach($paymentMethods as $method)
                                @php
                                    $displayName = $method->name;
                                    if ($method->account_number) {
                                        $lastDigits = substr($method->account_number, -3);
                                        $displayName .= " (***{$lastDigits})";
                                    }
                                @endphp
                                <option value="{{ $method->name }}"
                                        data-account-name="{{ $method->account_name }}"
                                        data-account-number="{{ $method->account_number }}"
                                        data-description="{{ $method->description }}"
                                        data-qr-image="{{ $method->qr_image }}"
                                        {{ old('payment_mode') == $method->name ? 'selected' : '' }}>
                                    {{ $displayName }}
                                </option>
                            @endforeach
                        </select>
                        @error('payment_mode')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3 col-md-6" id="sender-name-container">
                        <label for="sender_name" class="form-label">Sender Name</label>
                        <input type="text"
                            class="form-control"
                            id="sender_name"
                            name="sender_name"
                            value="{{ old('sender_name') }}"
                            placeholder="Enter the name of the person who sent the payment">
                        <div class="form-text" id="sender-name-text">Enter the name shown on the payment receipt (e.g., parent, guardian, or your name)</div>
                        @error('sender_name')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <div class="row" id="reference-number-row">
                    <div class="mb-3 col-md-6">
                        <label for="reference_number" class="form-label">Reference Number</label>
                        <input type="text"
                            class="form-control"
                            id="reference_number"
                            name="reference_number"
                            value="{{ old('reference_number') }}"
                            placeholder="Enter payment reference number">
                        <div class="form-text">Enter the reference number from your payment receipt/screenshot</div>
                        @error('reference_number')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Payment Method Details Section -->
                <div id="payment-details" class="mb-4" style="display: none;">
                    <div class="card border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Payment Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div id="payment-info">
                                        <!-- Payment details will be populated here -->
                                    </div>
                                </div>
                                <div class="col-md-6 text-center">
                                    <div id="qr-code-container" style="display: none;">
                                        <label class="form-label fw-bold">QR Code</label>
                                        <div>
                                            <img id="qr-image" src="" alt="QR Code" class="img-fluid" style="max-width: 200px; border: 1px solid #ddd; border-radius: 8px;">
                                        </div>
                                        <small class="text-muted">Scan this QR code to make payment</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3" id="payment-proof-section">
                    <label for="payment_proof" class="form-label">Payment Proof</label>
                    <input type="file"
                        class="form-control"
                        id="payment_proof"
                        name="payment_proof"
                        accept=".jpg,.jpeg,.png,.pdf">
                    <div class="form-text" id="payment-proof-text">Upload a clear image/screenshot of your payment (Max 2MB, JPG/PNG/PDF only)</div>
                    @error('payment_proof')
                        <div class="invalid-feedback d-block">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="gap-2 d-grid d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-secondary me-md-2">Reset</button>
                    <button type="submit" class="btn btn-primary" onclick="return confirmSubmission(event)">
                        <i class="fas fa-paper-plane me-2"></i>Submit Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
// Handle payment method selection
document.addEventListener('DOMContentLoaded', function() {
    const paymentModeSelect = document.getElementById('payment_mode');
    const paymentDetails = document.getElementById('payment-details');
    const paymentInfo = document.getElementById('payment-info');
    const qrContainer = document.getElementById('qr-code-container');
    const qrImage = document.getElementById('qr-image');

    paymentModeSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const paymentProofSection = document.getElementById('payment-proof-section');
        const paymentProofInput = document.getElementById('payment_proof');
        const paymentProofText = document.getElementById('payment-proof-text');
        const senderNameInput = document.getElementById('sender_name');
        const senderNameText = document.getElementById('sender-name-text');
        const senderNameContainer = document.getElementById('sender-name-container');
        const referenceNumberRow = document.getElementById('reference-number-row');
        const referenceNumberInput = document.getElementById('reference_number');

        if (selectedOption.value) {
            const accountName = selectedOption.dataset.accountName;
            const accountNumber = selectedOption.dataset.accountNumber;
            const description = selectedOption.dataset.description;
            const qrImagePath = selectedOption.dataset.qrImage;

            // Handle cash payment differently
            if (selectedOption.value.toLowerCase() === 'cash') {
                // For cash payments, hide sender name and reference number fields
                senderNameContainer.style.display = 'none';
                referenceNumberRow.style.display = 'none';

                // Make fields not required for cash payments
                paymentProofInput.required = false;
                senderNameInput.required = false;
                referenceNumberInput.required = false;

                // Clear any browser validation errors left on these inputs
                paymentProofInput.setCustomValidity('');
                senderNameInput.setCustomValidity('');
                referenceNumberInput.setCustomValidity('');

                // Clear field values when switching to cash
                senderNameInput.value = '';
                referenceNumberInput.value = '';

                paymentProofText.textContent = 'Payment proof is optional for cash payments (Max 2MB, JPG/PNG/PDF only)';

                // Build cash payment info
                let infoHtml = `<h6 class="text-primary">Cash Payment</h6>`;
                infoHtml += `<p class="mb-2"><strong>Description:</strong> Cash payment made in person at the finance office</p>`;
                infoHtml += `<div class="alert alert-info"><i class="fas fa-info-circle me-2"></i>Please visit the finance office to complete your cash payment.</div>`;

                paymentInfo.innerHTML = infoHtml;
                qrContainer.style.display = 'none';
                paymentDetails.style.display = 'block';
            } else {
                // For other payment methods, show and require sender name and reference number
                senderNameContainer.style.display = 'block';
                referenceNumberRow.style.display = 'block';

                paymentProofInput.required = true;
                senderNameInput.required = true;
                referenceNumberInput.required = true;
                paymentProofText.textContent = 'Upload a clear image/screenshot of your payment (Max 2MB, JPG/PNG/PDF only)';

                // Reset sender name placeholder and form text for other payment methods
                senderNameInput.placeholder = 'Enter the name of the person who sent the payment';
                senderNameText.textContent = 'Enter the name shown on the payment receipt (e.g., parent, guardian, or your name)';

                // Build payment info HTML
                let infoHtml = `<h6 class="text-primary">${selectedOption.value}</h6>`;

                if (description) {
                    infoHtml += `<p class="mb-2"><strong>Description:</strong> ${description}</p>`;
                }

                if (accountName) {
                    infoHtml += `<p class="mb-2"><strong>Account Name:</strong> ${accountName}</p>`;
                }

                if (accountNumber) {
                    infoHtml += `<p class="mb-2"><strong>Account Number:</strong> ${accountNumber}</p>`;
                }

                paymentInfo.innerHTML = infoHtml;

                // Show/hide QR code
                if (qrImagePath && qrImagePath !== 'null' && qrImagePath !== '') {
                    qrImage.src = `/storage/${qrImagePath}`;
                    qrContainer.style.display = 'block';
                } else {
                    qrContainer.style.display = 'none';
                }

                paymentDetails.style.display = 'block';
            }
        } else {
            paymentDetails.style.display = 'none';
            senderNameContainer.style.display = 'block';
            referenceNumberRow.style.display = 'block';
            
            paymentProofInput.required = true;
            senderNameInput.required = true;
            referenceNumberInput.required = true;
            paymentProofText.textContent = 'Upload a clear image/screenshot of your payment (Max 2MB, JPG/PNG/PDF only)';
            senderNameInput.placeholder = 'Enter the name of the person who sent the payment';
            senderNameText.textContent = 'Enter the name shown on the payment receipt (e.g., parent, guardian, or your name)';
        }
    });

    // Trigger change event if there's a pre-selected value (for old input)
    if (paymentModeSelect.value) {
        paymentModeSelect.dispatchEvent(new Event('change'));
    }
});
</script>
<script>
function confirmSubmission(event) {
    event.preventDefault();
    const form = document.getElementById('paymentForm');
    const paymentMode = document.getElementById('payment_mode').value;

    // Check if the form is valid
    if (!form.checkValidity()) {
        form.reportValidity();
        return false;
    }

    // Different messages for cash vs other payment methods
    let title, text, successTitle, successText;

    if (paymentMode.toLowerCase() === 'cash') {
        title = 'Are you sure you want to submit your cash payment record?';
        text = "This will notify finance that you intend to make a cash payment. Please visit the finance office to complete the payment.";
        successTitle = 'Submitted!';
        successText = 'Cash payment record submitted. Please visit the finance office to complete your payment.';
    } else {
        title = 'Are you sure you want to submit your proof of payment?';
        text = "Please make sure that your uploaded proof of payment has the correct details.";
        successTitle = 'Submitted!';
        successText = 'Payment proof submitted to finance for review. You will be notified once it is verified.';
    }

    Swal.fire({
        title: title,
        text: text,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, submit',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
            Swal.fire({
                title: successTitle,
                text: successText,
                icon: 'success',
                timer: 20000,
                showConfirmButton: false
            });
        }
    });

    return false;
}
</script>
@endpush
@endsection
