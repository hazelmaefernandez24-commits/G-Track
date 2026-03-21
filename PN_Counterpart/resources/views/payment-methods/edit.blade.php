@extends('layouts.finance')

@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Edit Payment Method</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('payment-methods.update', $paymentMethod) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" value="{{ old('name', $paymentMethod->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Account Name</label>
                            <input type="text" class="form-control" name="account_name" value="{{ old('account_name', $paymentMethod->account_name) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" class="form-control" name="account_number" value="{{ old('account_number', $paymentMethod->account_number) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3">{{ old('description', $paymentMethod->description) }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">QR Image (optional)</label>
                            @if($paymentMethod->qr_image)
                                <div class="mb-2">
                                    <strong>Current QR Code:</strong><br>
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="showQRModal('{{ asset('storage/' . $paymentMethod->qr_image) }}', '{{ $paymentMethod->name }}')">
                                        <i class="fas fa-qrcode"></i> View Current QR
                                    </button>
                                </div>
                            @endif
                            <input type="file" class="form-control" name="qr_image" accept="image/*">
                            <small class="form-text text-muted">Upload a new image to replace the current one.</small>
                        </div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" {{ old('is_active', $paymentMethod->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>

                        <div class="d-flex justify-content-between">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-save"></i> Update Payment Method
                            </button>
                            <a href="{{ route('payment-methods.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Modal -->
<div class="modal fade" id="qrViewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrModalTitle">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrModalImage" src="" alt="QR Code" class="img-fluid" style="max-width: 400px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function showQRModal(imageSrc, title) {
    document.getElementById('qrModalImage').src = imageSrc;
    document.getElementById('qrModalTitle').textContent = title + ' QR Code';
    new bootstrap.Modal(document.getElementById('qrViewModal')).show();
}
</script>

@endsection
