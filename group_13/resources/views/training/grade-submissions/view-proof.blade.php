@extends('layouts.nav')

@section('content')
<div class="proof-container">
    <div class="proof-card">
        <div class="card-header-custom">
            <h2>View Proof</h2>
            <p>Student: {{ $student->user_fname }} {{ $student->user_lname }}</p>
        </div>

        <div class="card-body-custom">
            @if($proof)
                <div class="proof-details">
                    <h3>Proof Details</h3>
                    <p><strong>File Name:</strong> {{ $proof->file_name }}</p>
                    <p><strong>File Type:</strong> {{ strtoupper($proof->file_type) }}</p>
                    <p><strong>Uploaded:</strong> {{ $proof->created_at->format('M d, Y h:i A') }}</p>
                    <p><strong>Status:</strong> <span class="status-badge status-{{ $proof->status }}">{{ ucfirst($proof->status) }}</span></p>
                </div>

                <div class="proof-document">
                    <h3>Proof Document</h3>
                    @if(in_array($proof->file_type, ['jpg', 'jpeg', 'png']))
                        <img src="{{ asset('storage/' . $proof->file_path) }}" class="proof-image" alt="Proof Document">
                    @else
                        <iframe src="{{ asset('storage/' . $proof->file_path) }}" class="proof-iframe" frameborder="0"></iframe>
                    @endif
                </div>

                <div class="proof-actions">
                    @if($proof->status === 'pending')
                        <p class="text-muted">Please use the Action dropdown in the monitor view to approve or reject this proof.</p>
                    @endif
                </div>
            @else
                <div class="alert-custom alert-warning-custom">
                    No proof document found for this student.
                </div>
            @endif

            <div class="back-link">
                <a href="{{ route('training.grade-submissions.index', $gradeSubmission->id) }}" class="btn-custom btn-secondary-custom">
                    Back to Monitor
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    :root {
        --primary-color: #007bff; /* Assuming a standard blue as primary color */
        --success-color: #28a745;
        --danger-color: #dc3545;
        --secondary-color: #6c757d;
        --warning-color: #ffc107;
        --light-bg: #f8f9fa;
        --border-color: #dee2e6;
        --card-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }

    body {
        font-family: sans-serif;
        line-height: 1.6;
        margin: 0;
        padding: 0;
        background-color: var(--light-bg);
    }

    .proof-container {
        max-width: 1200px;
        margin: 20px auto;
        padding: 0 15px;
    }

    .proof-card {
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

    .card-header-custom p {
        margin: 0;
        font-size: 1rem;
        opacity: 0.9;
    }

    .card-body-custom {
        padding: 20px;
    }

    .proof-details,
    .proof-document,
    .proof-actions,
    .back-link {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--border-color);
    }
    
    .back-link {
        border-bottom: none;
        padding-bottom: 0;
        margin-bottom: 0;
    }

    .proof-details h3,
    .proof-document h3 {
        font-size: 1.25rem;
        margin-top: 0;
        margin-bottom: 10px;
        color: #333;
    }

    .proof-details p {
        margin: 5px 0;
    }

    .proof-image {
        max-width: 100%;
        height: auto;
        display: block;
        margin: 0 auto;
        border: 1px solid var(--border-color);
        border-radius: 5px;
        box-shadow: var(--card-shadow);
    }

    .proof-iframe {
        width: 100%;
        height: 600px;
        border: 1px solid var(--border-color);
        border-radius: 5px;
    }

    .proof-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }
    
    .action-form {
        margin: 0;
    }

    .btn-custom {
        padding: 10px 20px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 1rem;
        transition: background-color 0.3s ease;
        text-decoration: none;
        display: inline-block;
        text-align: center;
    }

    .btn-primary-custom {
        background-color: var(--primary-color);
        color: #fff;
    }

    .btn-primary-custom:hover {
        background-color: #0056b3;
    }

    .btn-success-custom {
        background-color: var(--success-color);
        color: #fff;
    }

    .btn-success-custom:hover {
        background-color: #218838;
    }

    .btn-danger-custom {
        background-color: var(--danger-color);
        color: #fff;
    }

    .btn-danger-custom:hover {
        background-color: #c82333;
    }

     .btn-secondary-custom {
        background-color: var(--secondary-color);
        color: #fff;
    }

    .btn-secondary-custom:hover {
        background-color: #545b62;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .status-pending {
        background-color: var(--warning-color);
        color: #000;
    }

    .status-approved {
        background-color: var(--success-color);
        color: #fff;
    }

    .status-rejected {
        background-color: var(--danger-color);
        color: #fff;
    }

    .alert-warning-custom {
        padding: 15px;
        margin-bottom: 20px;
        border: 1px solid var(--warning-color);
        border-radius: 5px;
        background-color: #fff3cd;
        color: #856404;
    }
</style>
@endsection 