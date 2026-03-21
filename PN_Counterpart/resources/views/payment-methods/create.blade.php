
@extends('layouts.finance')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Add New Payment Option</div>
                <div class="card-body">
                    <form action="{{ route('payment-methods.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Mode of Payment</label>
                            <input type="text" class="form-control" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Name</label>
                            <input type="text" class="form-control" name="account_name">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Account Number</label>
                            <input type="text" class="form-control" name="account_number">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">QR Image (optional)</label>
                            <input type="file" class="form-control" name="qr_image" id="qr_image" accept="image/*" onchange="previewQRImage(this)">
                            <small class="form-text text-muted">Upload a QR code image for this payment method.</small>

                            <!-- QR Image Preview -->
                            <div id="qr_preview" class="mt-3" style="display: none;">
                                <label class="form-label">Preview:</label><br>
                                <button type="button" class="btn btn-sm btn-outline-primary" onclick="showQRModal()">
                                    <i class="fas fa-qrcode"></i> View Preview
                                </button>
                                <img id="qr_preview_img" src="" alt="QR Preview" style="display: none;">
                            </div>
                        </div>
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" name="is_active" value="1" checked>
                            <label class="form-check-label">Active</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ route('payment-methods.index') }}" class="btn btn-secondary">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Preview Modal -->
<div class="modal fade" id="qrPreviewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">QR Code Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="qr_modal_img" src="" alt="QR Code" class="img-fluid" style="max-width: 400px;">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function previewQRImage(input) {
    const preview = document.getElementById('qr_preview');
    const previewImg = document.getElementById('qr_preview_img');
    const modalImg = document.getElementById('qr_modal_img');

    if (input.files && input.files[0]) {
        const reader = new FileReader();

        reader.onload = function(e) {
            previewImg.src = e.target.result;
            modalImg.src = e.target.result;
            preview.style.display = 'block';
        };

        reader.readAsDataURL(input.files[0]);
    } else {
        preview.style.display = 'none';
    }
}

function showQRModal() {
    new bootstrap.Modal(document.getElementById('qrPreviewModal')).show();
}
</script>

@endsection