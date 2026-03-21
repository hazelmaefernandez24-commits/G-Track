{{-- <!-- Make sure Font Awesome & Dropzone.js are loaded in your layout -->
<link
  rel="stylesheet"
  href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.2/css/all.min.css"
  integrity="sha512-1hcgKJ9iHo0rCzh5bZTYp9YkQuF7NvbjypJqdbt8bB5ZiC74wpsv6wF6Tq+6eKHnxpG7Aqu44vWl5wG1xE6ew=="
  crossorigin="anonymous"
/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/min/dropzone.min.js" integrity="…" crossorigin="anonymous"></script>

<!-- File Upload with Drag and Drop -->
<div class="mb-3">
  <label class="form-label">Upload Payment Proof (Screenshot/Image)</label>
  <div id="paymentProofDropzone" class="dropzone">
    <div class="dz-message">
      <i class="fas fa-cloud-upload-alt fa-3x dropzone-icon"></i>
      <p>Drag and drop your file here<br>or click to select</p>
    </div>
  </div>
  <input type="file" class="d-none" id="payment_proof" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required>
  <div class="form-text">Max 2MB (JPG, PNG, or PDF)</div>
</div>

@push('styles')
<style>
  /* Dropzone container */
  #paymentProofDropzone {
    border: 2px dashed var(--border);
    border-radius: 0.75rem;
    background: #fafafb;
    transition: background 0.2s, border-color 0.2s;
    cursor: pointer;
  }
  #paymentProofDropzone .dz-message {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 2.5rem;
    color: var(--text-muted);
  }
  #paymentProofDropzone .dropzone-icon {
    color: #08949e !important;
    margin-bottom: 0.5rem;
    transition: color 0.2s;
  }
  #paymentProofDropzone:hover {
    background: #f0fcfd;
    border-color: #08949e;
  }
  /* on dragover */
  #paymentProofDropzone.dz-dragover {
    background: #e6f7f9;
    border-color: #08949e;
  }
</style>
@endpush

@push('scripts')
<script>
  Dropzone.autoDiscover = false;

  new Dropzone("#paymentProofDropzone", {
    url: "#",              // handled on form submit
    autoProcessQueue: false,
    clickable: true,
    maxFiles: 1,
    maxFilesize: 2,        // MB
    acceptedFiles: ".jpg,.jpeg,.png,.pdf",
    previewsContainer: null,
    init: function() {
      this.on("addedfile", file => {
        // transfer to hidden input
        document.getElementById('payment_proof').files = this.getAcceptedFiles();
      });
      this.on("dragover", () => {
        this.element.classList.add('dz-dragover');
      });
      this.on("dragleave", () => {
        this.element.classList.remove('dz-dragover');
      });
    }
  });
</script>
@endpush --}}