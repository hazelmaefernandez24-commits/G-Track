@extends('layouts.student_layout')

@section('content')
<div class="submission-container">
    <div class="submission-card">
        <div class="card-header-custom">
            <h2 style="color: #333;">Grade Submission</h2>
            <p style="color: #555;">{{ $gradeSubmission->semester }} {{ $gradeSubmission->term }} {{ $gradeSubmission->academic_year }}</p>
        </div>

        <div class="card-body-custom">
            <!-- Display validation errors -->
            @if($errors->any())
                <div class="alert-custom alert-danger-custom">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-custom alert-danger-custom">
                    {{ session('error') }}
                </div>
            @endif

            @if(session('success'))
                <div class="alert-custom alert-success-custom">
                    {{ session('success') }}
                </div>
            @endif

            @php
                $proof = \App\Models\GradeSubmissionProof::where('grade_submission_id', $gradeSubmission->id)
                    ->where('user_id', Auth::user()->user_id)
                    ->first();
            @endphp

            @if($gradeSubmission->status === 'rejected' || ($proof && $proof->status === 'rejected'))
                <div class="rejection-notice">
                    <h3>Previous Submission Rejected</h3>
                    <p>Your previous submission was rejected. Please review and resubmit your grades and proof.</p>
                </div>
            @elseif(($proof && $proof->status === 'approved') || $gradeSubmission->status === 'approved')
                <div class="alert-custom alert-success-custom">
                    <h3>Grades Approved</h3>
                    <p>Your grades have been approved and cannot be modified.</p>
                </div>
                @php
                    // If somehow we got here with approved status, we should redirect
                    return redirect()->route('student.dashboard');
                @endphp
            @endif

            <form action="{{ route('student.submit-grades.store', $gradeSubmission->id) }}" method="POST" enctype="multipart/form-data" id="grade-submission-form">
                @csrf

                <div class="grades-section">
                    <h3>Enter Grades</h3>
                    @if($subjects->isNotEmpty())
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th class="subject-column">Subject</th>
                                    <th class="grade-column">Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($subjects as $subject)
                                    <tr>
                                        <td>{{ $subject->name }}</td>
                                        <td>
                                            <div class="grade-input-wrapper">
                                                <!-- Combined input with datalist for dropdown options -->
                                                <input type="text"
                                                       id="grade_input_{{ $subject->id }}"
                                                       name="grades[{{ $subject->id }}]"
                                                       value="{{ $subject->grade ?? '' }}"
                                                       class="grade-input {{ $errors->has('grades.' . $subject->id) ? 'is-invalid' : '' }}"
                                                       list="grade_options_{{ $subject->id }}"
                                                       placeholder="1.0-5.0 or select"
                                                       title="Enter a numeric grade (1.0-5.0) or select INC, NC, DR"
                                                       oninput="validateGradeInput({{ $subject->id }})"
                                                       required>

                                                <!-- Datalist for dropdown options -->
                                                <datalist id="grade_options_{{ $subject->id }}">
                                                    <option value="INC">INC (Incomplete)</option>
                                                    <option value="NC">NC (No Credit)</option>
                                                    <option value="DR">DR (Dropped)</option>
                                                </datalist>

                                                <!-- Custom validation message -->
                                                <div id="grade_error_{{ $subject->id }}" class="grade-error-message" style="display: none;">
                                                    Please enter a valid grade (1.0-5.0) or select INC, NC, or DR
                                                </div>
                                            </div>
                                            @error('grades.' . $subject->id)
                                                <div class="invalid-feedback">
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert-custom alert-warning-custom">
                            No subjects found for this submission. You may still upload your proof and submit.
                        </div>
                    @endif
                </div>

                <div class="proof-section mt-4">
                    <h3>Upload Proof</h3>
                    <div class="form-group">
                        <label for="proof">Upload your proof document (PDF, DOC, DOCX, JPG, JPEG, PNG)</label>
                        <input type="file"
                               name="proof"
                               id="proof"
                               class="form-control file-input-mobile"
                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                               capture="environment"
                               required>
                        <small class="form-text text-muted">Maximum file size: 10MB</small>
                        <div id="file-selected" class="file-feedback" style="display: none;">
                            <span class="file-name"></span>
                            <span class="file-size"></span>
                        </div>
                    </div>
                </div>

                <div class="form-actions mt-4">
                    <!-- SUBMIT BUTTON ALWAYS RENDERED -->
                    <button type="submit" class="btn-custom btn-primary-custom">
                        {{ $proof && $proof->status === 'rejected' ? 'Resubmit Grades' : 'Submit Grades' }}
                    </button>
                    <a href="{{ route('student.grade-submissions.list') }}" class="btn-custom btn-secondary-custom">
                        <i class="fas fa-arrow-left" style="margin-right: 5px;"></i>
                        Back to Submissions
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .submission-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 15px;
    }

    .submission-card {
        background-color: #fff;
        border-radius: 8px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        overflow: hidden;
    }

    .card-header-custom {
        background-color: var(--primary-color);
        color: #fff;
        padding: 15px 20px;
        border-bottom: 1px solid rgba(0, 0, 0, 0.125);
    }

    .card-header-custom h2 {
        margin: 0 0 5px 0;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .card-body-custom {
        padding: 20px;
    }

    .alert-custom {
        padding: 10px 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .alert-danger-custom {
        background-color: #f8d7da;
        border: 1px solid #f5c6cb;
        color: #721c24;
    }

    .alert-success-custom {
        background-color: #d4edda;
        border: 1px solid #c3e6cb;
        color: #155724;
    }

    .alert-warning-custom {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
    }

    .rejection-notice {
        background-color: #fff3cd;
        border: 1px solid #ffeeba;
        color: #856404;
        padding: 15px;
        border-radius: 5px;
        margin-bottom: 20px;
    }

    .rejection-notice h3 {
        margin-top: 0;
        margin-bottom: 10px;
        color: #856404;
    }

    .grades-section {
        margin-bottom: 30px;
    }

    .grades-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        color: var(--dark-text);
    }

    .grades-table {
        width: 80%;
        max-width: 600px;
        border-collapse: collapse;
        margin: 0 auto 20px auto;
        table-layout: fixed;
    }

    .grades-table th,
    .grades-table td {
        padding: 12px;
        border: 1px solid #ddd;
        vertical-align: middle;
    }

    .grades-table th {
        background-color: #22bbea;
        color: white;
        font-weight: 600;
        text-align: center;
    }

    .grades-table tbody tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .grades-table tbody tr:hover {
        background-color: #f0f8ff;
    }

    /* Table column widths */
    .subject-column {
        width: 70%;
        text-align: left;
    }

    .grade-column {
        width: 30%;
        text-align: center;
        min-width: 100px;
    }

    .grade-input-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .grade-input {
        width: 90px;
        min-width: 90px;
        max-width: 90px;
        padding: 8px 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
        text-align: center;
        font-weight: 500;
        margin: 0 auto;
        display: block;
        background-color: #fff;
    }

    .grade-input:focus {
        border-color: #22bbea;
        outline: none;
        box-shadow: 0 0 0 2px rgba(34, 187, 234, 0.2);
    }

    .grade-error-message {
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        background-color: #dc3545;
        color: white;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
        white-space: nowrap;
        z-index: 1000;
        margin-top: 2px;
    }

    .grade-error-message::before {
        content: '';
        position: absolute;
        top: -4px;
        left: 50%;
        transform: translateX(-50%);
        border-left: 4px solid transparent;
        border-right: 4px solid transparent;
        border-bottom: 4px solid #dc3545;
    }

    .form-text {
        display: block;
        margin-top: 5px;
        font-size: 0.875rem;
    }

    .text-muted {
        color: #6c757d;
    }

    .proof-section {
        margin-bottom: 30px;
    }

    .proof-section h3 {
        margin-top: 0;
        margin-bottom: 15px;
        color: var(--dark-text);
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: var(--dark-text);
    }

    .form-control {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }

    /* Mobile-friendly file input */
    .file-input-mobile {
        padding: 12px;
        font-size: 16px; /* Prevent zoom on iOS */
        border: 2px dashed #ddd;
        border-radius: 8px;
        background-color: #f9f9f9;
        cursor: pointer;
        touch-action: manipulation;
    }

    .file-input-mobile:focus {
        border-color: #22bbea;
        outline: none;
        box-shadow: 0 0 0 2px rgba(34, 187, 234, 0.2);
    }

    .file-feedback {
        margin-top: 10px;
        padding: 10px;
        background-color: #e8f5e8;
        border: 1px solid #4caf50;
        border-radius: 4px;
        color: #2e7d32;
    }

    .file-name {
        font-weight: bold;
        display: block;
    }

    .file-size {
        font-size: 0.9em;
        color: #666;
    }

    .form-actions {
        display: flex;
        gap: 10px;
    }

    .btn-custom {
        padding: 10px 15px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-custom.btn-primary-custom {
        background-color: #007bff !important;
        color: #fff !important;
        border: 2px solid #0056b3 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .btn-custom.btn-primary-custom:hover {
        background-color: #0056b3 !important;
    }
    .btn-custom.btn-secondary-custom {
        background-color: #6c757d !important;
        color: #fff !important;
        border: 2px solid #545b62 !important;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .btn-custom.btn-secondary-custom:hover {
        background-color: #545b62 !important;
    }

    /* Mobile responsive improvements */
    @media (max-width: 768px) {
        .submission-container {
            padding: 0 10px;
        }

        .grades-table {
            width: 90%;
            font-size: 0.9rem;
        }

        .grades-table th,
        .grades-table td {
            padding: 10px;
        }

        .subject-column {
            width: 65%;
        }

        .grade-column {
            width: 35%;
            min-width: 90px;
        }

        .grade-input {
            width: 85px;
            min-width: 85px;
            max-width: 85px;
            padding: 10px 6px;
            font-size: 16px; /* Prevent zoom on iOS */
        }

        .file-input-mobile {
            padding: 16px;
            font-size: 16px;
            min-height: 60px;
        }

        .form-actions {
            flex-direction: column;
        }

        .btn-custom {
            width: 100%;
            margin-bottom: 10px;
        }
    }

    /* Extra small screens */
    @media (max-width: 480px) {
        .grades-table {
            width: 95%;
            font-size: 0.85rem;
        }

        .grades-table th,
        .grades-table td {
            padding: 8px;
        }

        .subject-column {
            width: 60%;
        }

        .grade-column {
            width: 40%;
            min-width: 80px;
        }

        .grade-input {
            width: 80px;
            min-width: 80px;
            max-width: 80px;
            padding: 8px 4px;
            font-size: 16px;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('proof');
    const fileSelected = document.getElementById('file-selected');
    const fileName = fileSelected.querySelector('.file-name');
    const fileSize = fileSelected.querySelector('.file-size');
    const form = document.getElementById('grade-submission-form');

    // File input change handler
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];

        if (file) {
            console.log('File selected:', {
                name: file.name,
                size: file.size,
                type: file.type
            });

            // Show file feedback
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            fileSelected.style.display = 'block';

            // Validate file size (10MB = 10485760 bytes)
            if (file.size > 10485760) {
                alert('File size must be less than 10MB. Please choose a smaller file.');
                fileInput.value = '';
                fileSelected.style.display = 'none';
                return;
            }

            // Validate file type
            const allowedTypes = [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'image/jpeg',
                'image/jpg',
                'image/png'
            ];

            const allowedExtensions = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
            const fileExtension = file.name.split('.').pop().toLowerCase();

            if (!allowedTypes.includes(file.type) && !allowedExtensions.includes(fileExtension)) {
                alert('Please select a valid file type: PDF, DOC, DOCX, JPG, JPEG, or PNG');
                fileInput.value = '';
                fileSelected.style.display = 'none';
                return;
            }

            console.log('File validation passed');
        } else {
            fileSelected.style.display = 'none';
        }
    });

    // Form submit handler
    form.addEventListener('submit', function(e) {
        const file = fileInput.files[0];

        if (!file) {
            e.preventDefault();
            alert('Please select a proof file before submitting.');
            return false;
        }

        console.log('Form submitting with file:', file.name);

        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Uploading...';
        }
    });

    // Format file size for display
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    console.log('File upload JavaScript initialized');
});

// Function to validate grade input (numeric or special grades)
function validateGradeInput(subjectId) {
    const input = document.getElementById(`grade_input_${subjectId}`);
    const errorDiv = document.getElementById(`grade_error_${subjectId}`);
    const value = input.value.trim().toUpperCase();

    // Clear previous validation state
    input.setCustomValidity('');
    errorDiv.style.display = 'none';
    input.classList.remove('is-invalid');

    if (value === '') {
        return; // Let HTML5 required validation handle empty values
    }

    // Check if it's a special grade
    const specialGrades = ['INC', 'NC', 'DR'];
    if (specialGrades.includes(value)) {
        input.value = value; // Ensure uppercase
        return; // Valid special grade
    }

    // Check if it's a numeric grade
    const numericPattern = /^(5(\.0)?|[1-4](\.[0-9]{1,2})?)$/;
    if (numericPattern.test(value)) {
        return; // Valid numeric grade
    }

    // Invalid input
    input.setCustomValidity('Please enter a valid grade (1.0-5.0) or select INC, NC, or DR');
    input.classList.add('is-invalid');
    errorDiv.style.display = 'block';
}

// Add form validation before submit
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('grade-submission-form');

    form.addEventListener('submit', function(e) {
        let hasErrors = false;

        // Validate all grade inputs
        const gradeInputs = form.querySelectorAll('input[name^="grades["]');
        gradeInputs.forEach(function(input) {
            const subjectId = input.id.replace('grade_input_', '');
            validateGradeInput(subjectId);

            if (input.classList.contains('is-invalid')) {
                hasErrors = true;
            }
        });

        if (hasErrors) {
            e.preventDefault();
            alert('Please correct the grade input errors before submitting.');
            return false;
        }
    });
});
</script>
@endsection