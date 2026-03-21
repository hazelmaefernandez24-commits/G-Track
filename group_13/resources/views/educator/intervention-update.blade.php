@extends('layouts.educator_layout')

@section('content')
<div class="page-container">
    <div class="header-section">
        <h1 style="font-weight: 300">✏️ Update Intervention</h1>
        <hr>
        <p class="text-muted">Update the status and assignment details for this intervention.</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-pencil-square me-2"></i>
                        Intervention Details
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('educator.intervention.store', $intervention->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                                <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                    <option value="pending" {{ old('status', $intervention->status) === 'pending' ? 'selected' : '' }}>
                                        Pending
                                    </option>
                                    <option value="done" {{ old('status', $intervention->status) === 'done' ? 'selected' : '' }}>
                                        Done
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="intervention_date" class="form-label">Intervention Date</label>
                                <input type="date" 
                                       class="form-control @error('intervention_date') is-invalid @enderror" 
                                       id="intervention_date" 
                                       name="intervention_date" 
                                       value="{{ old('intervention_date', $intervention->intervention_date ? $intervention->intervention_date->format('Y-m-d') : '') }}">
                                @error('intervention_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="educator_assigned" class="form-label">Educator Assigned</label>
                            <select class="form-select @error('educator_assigned') is-invalid @enderror" id="educator_assigned" name="educator_assigned">
                                <option value="">Select an educator...</option>
                                @foreach($educators as $educator)
                                    <option value="{{ $educator->user_id }}" 
                                            {{ old('educator_assigned', $intervention->educator_assigned) === $educator->user_id ? 'selected' : '' }}>
                                        {{ $educator->user_fname }} {{ $educator->user_lname }} ({{ $educator->user_email }})
                                    </option>
                                @endforeach
                            </select>
                            @error('educator_assigned')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="intervention_details" class="form-label">Intervention Details <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('intervention_details') is-invalid @enderror"
                                      id="intervention_details"
                                      name="intervention_details"
                                      rows="5"
                                      placeholder="Describe the specific interventions you implemented for the students (e.g., additional tutoring sessions, remedial activities, one-on-one mentoring, etc.)..."
                                      required>{{ old('intervention_details', $intervention->educator_assigned ? ($intervention->remarks ?? '') : '') }}</textarea>
                            @error('intervention_details')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <a href="{{ route('educator.intervention') }}" class="btn btn-secondary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Back to Interventions
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>
                                Update Intervention
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Intervention Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Subject:</strong>
                        <br>
                        <span class="text-muted">{{ $intervention->subject->name ?? 'N/A' }}</span>
                    </div>
                    
                    <!-- <div class="mb-3">
                        <strong>School:</strong>
                        <br>
                        <span class="text-muted">{{ $intervention->school->school_name ?? 'N/A' }}</span>
                    </div> -->
                    
                    <div class="mb-3">
                        <strong>Class:</strong>
                        <br>
                        <span class="text-muted">{{ $intervention->classModel->class_name ?? 'N/A' }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Students Needing Intervention:</strong>
                        <br>
                        <span class="badge bg-danger fs-6">{{ $intervention->student_count }}</span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Current Status:</strong>
                        <br>
                        <span class="badge {{ $intervention->status === 'done' ? 'bg-success' : 'bg-warning' }}">
                            {{ ucfirst($intervention->status) }}
                        </span>
                    </div>
                    
                    <div class="mb-3">
                        <strong>Created:</strong>
                        <br>
                        <span class="text-muted">{{ $intervention->created_at->format('M d, Y g:i A') }}</span>
                    </div>
                    
                    @if($intervention->updated_at && $intervention->updated_at != $intervention->created_at)
                        <div class="mb-3">
                            <strong>Last Updated:</strong>
                            <br>
                            <span class="text-muted">{{ $intervention->updated_at->format('M d, Y g:i A') }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Reset and Base Styles */
* {
    box-sizing: border-box;
}

/* Page Container */
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.header-section {
    margin-bottom: 30px;
}

.header-section h1 {
    font-weight: 300;
    color: #333;
    margin-bottom: 10px;
    font-size: 2rem;
    margin-top: 0;
}

.header-section hr {
    border: none;
    height: 1px;
    background-color: #ddd;
    margin-bottom: 15px;
    margin-top: 15px;
}

.header-section p {
    color: #6c757d;
    margin-bottom: 0;
    margin-top: 0;
}

/* Bootstrap Grid System Replacement */
.row {
    display: flex;
    flex-wrap: wrap;
    margin-left: -15px;
    margin-right: -15px;
}

.col-md-8 {
    flex: 0 0 66.666667%;
    max-width: 66.666667%;
    padding-left: 15px;
    padding-right: 15px;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding-left: 15px;
    padding-right: 15px;
}

.col-md-4 {
    flex: 0 0 33.333333%;
    max-width: 33.333333%;
    padding-left: 15px;
    padding-right: 15px;
}

@media (max-width: 768px) {
    .col-md-8,
    .col-md-6,
    .col-md-4 {
        flex: 0 0 100%;
        max-width: 100%;
        margin-bottom: 20px;
    }
}

/* Alert Styling */
.alert {
    padding: 12px 16px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 6px;
    position: relative;
    display: flex;
    align-items: center;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-danger {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}

.alert-dismissible {
    padding-right: 50px;
}

.btn-close {
    position: absolute;
    top: 50%;
    right: 16px;
    transform: translateY(-50%);
    background: none;
    border: none;
    font-size: 18px;
    cursor: pointer;
    opacity: 0.7;
    padding: 0;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-close:hover {
    opacity: 1;
}

.btn-close::before {
    content: "×";
    font-size: 20px;
    line-height: 1;
}

/* Card Styling */
.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    border: 1px solid #dee2e6;
    margin-bottom: 1.5rem;
    overflow: hidden;
}

.card-header {
    background-color: #22bbea;
    color: white;
    padding: 16px 24px;
    border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    font-weight: 600;
    font-size: 16px;
    letter-spacing: 0.5px;
    margin: 0;
}

.card-header h5 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    display: flex;
    align-items: center;
}

.card-body {
    padding: 24px;
}

/* Utility Classes */
.mb-3 {
    margin-bottom: 1rem;
}

.mb-0 {
    margin-bottom: 0;
}

.me-1 {
    margin-right: 0.25rem;
}

.me-2 {
    margin-right: 0.5rem;
}

.mt-4 {
    margin-top: 1.5rem;
}

.d-flex {
    display: flex;
}

.justify-content-between {
    justify-content: space-between;
}

.align-items-center {
    align-items: center;
}

.text-center {
    text-align: center;
}

.text-danger {
    color: #dc3545 !important;
}

.text-muted {
    color: #6c757d !important;
}

/* Form Styling */
.form-label {
    font-weight: 500;
    color: #495057;
    margin-bottom: 8px;
    font-size: 14px;
    display: block;
}

.form-select, .form-control {
    display: block;
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.5;
    color: #495057;
    background-color: #fff;
    background-clip: padding-box;
    border: 1px solid #ced4da;
    border-radius: 6px;
    transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.form-select:focus, .form-control:focus {
    color: #495057;
    background-color: #fff;
    border-color: #22bbea;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(34, 187, 234, 0.25);
}

.form-control[type="date"] {
    padding: 9px 12px;
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

.form-select {
    appearance: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%23343a40' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m1 6 7 7 7-7'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 12px center;
    background-size: 16px 12px;
    padding-right: 40px;
}

.is-invalid {
    border-color: #dc3545;
}

.is-invalid:focus {
    border-color: #dc3545;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.invalid-feedback {
    display: block;
    width: 100%;
    margin-top: 0.25rem;
    font-size: 0.875em;
    color: #dc3545;
}

/* Button Styling */
.btn {
    display: inline-block;
    font-weight: 500;
    line-height: 1.5;
    color: #212529;
    text-align: center;
    text-decoration: none;
    vertical-align: middle;
    cursor: pointer;
    user-select: none;
    background-color: transparent;
    border: 1px solid transparent;
    padding: 10px 20px;
    font-size: 14px;
    border-radius: 6px;
    transition: all 0.3s ease;
    min-width: 140px;
}

.btn:hover {
    text-decoration: none;
}

.btn:focus {
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn-primary {
    color: #fff;
    background-color: #22bbea;
    border-color: #22bbea;
}

.btn-primary:hover {
    color: #fff;
    background-color: #1a9bc7;
    border-color: #1a9bc7;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(34, 187, 234, 0.3);
}

.btn-primary:focus {
    color: #fff;
    background-color: #1a9bc7;
    border-color: #1a9bc7;
    box-shadow: 0 0 0 0.2rem rgba(34, 187, 234, 0.5);
}

.btn-secondary {
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
}

.btn-secondary:hover {
    color: #fff;
    background-color: #5a6268;
    border-color: #545b62;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(108, 117, 125, 0.3);
}

.btn-secondary:focus {
    color: #fff;
    background-color: #5a6268;
    border-color: #545b62;
    box-shadow: 0 0 0 0.2rem rgba(108, 117, 125, 0.5);
}

/* Badge Styling */
.badge {
    display: inline-block;
    padding: 6px 12px;
    font-size: 0.875em;
    font-weight: 600;
    line-height: 1;
    color: #fff;
    text-align: center;
    white-space: nowrap;
    vertical-align: baseline;
    border-radius: 6px;
}

.badge.fs-6 {
    font-size: 1rem !important;
    padding: 8px 16px;
}

.bg-danger {
    background-color: #dc3545 !important;
    color: #fff !important;
}

.bg-success {
    background-color: #28a745 !important;
    color: #fff !important;
}

.bg-warning {
    background-color: #ffc107 !important;
    color: #212529 !important;
}

/* Info Section Styling */
.card-body strong {
    color: #495057;
    font-weight: 600;
    display: block;
    margin-bottom: 4px;
}

.card-body .mb-3 {
    padding-bottom: 12px;
    border-bottom: 1px solid #f8f9fa;
    margin-bottom: 16px;
}

.card-body .mb-3:last-child {
    border-bottom: none;
    padding-bottom: 0;
    margin-bottom: 0;
}

/* Icon Styling */
.bi {
    display: inline-block;
    vertical-align: middle;
}

/* Form Row Styling */
.row.mb-3 {
    margin-bottom: 1rem;
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-container {
        padding: 15px;
        margin: 10px;
    }

    .card-body {
        padding: 20px;
    }

    .card-header {
        padding: 12px 20px;
    }

    .d-flex.justify-content-between {
        flex-direction: column;
        gap: 15px;
    }

    .btn {
        width: 100%;
        margin-bottom: 10px;
        min-width: auto;
    }

    .row {
        margin-left: -10px;
        margin-right: -10px;
    }

    .col-md-8,
    .col-md-6,
    .col-md-4 {
        padding-left: 10px;
        padding-right: 10px;
    }
}

@media (max-width: 576px) {
    .page-container {
        padding: 10px;
        margin: 5px;
        border-radius: 4px;
    }

    .header-section h1 {
        font-size: 1.5rem;
    }

    .card-header {
        padding: 10px 15px;
        font-size: 14px;
    }

    .card-body {
        padding: 15px;
    }
}

/* Focus and Hover States */
a {
    color: #22bbea;
    text-decoration: none;
}

a:hover {
    color: #1a9bc7;
    text-decoration: underline;
}

/* Form Validation */
.was-validated .form-control:valid,
.form-control.is-valid {
    border-color: #28a745;
}

.was-validated .form-control:valid:focus,
.form-control.is-valid:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}

.was-validated .form-select:valid,
.form-select.is-valid {
    border-color: #28a745;
}

.was-validated .form-select:valid:focus,
.form-select.is-valid:focus {
    border-color: #28a745;
    box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
}
</style>
@endsection

