@extends('layouts.student_layout')

@section('content')
<div class="submission-view-container">
    <h1>Submitted Grades for {{ $gradeSubmission->term ?? 'N/A' }}</h1>

    <div class="submission-details">
        <p><strong>Semester:</strong> {{ $gradeSubmission->semester ?? 'N/A' }}</p>
        <p><strong>Term:</strong> {{ $gradeSubmission->term ?? 'N/A' }}</p>
        <p><strong>Academic Year:</strong> {{ $gradeSubmission->academic_year ?? 'N/A' }}</p>
    </div>

    <h3>Subjects and Grades</h3>
    
    @if($studentSubjectEntries->isEmpty())
        <p>No subjects found for this submission.</p>
    @else
        <table class="subjects-table">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Your Grade</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($studentSubjectEntries as $entry)
                    <tr>
                        <td>{{ $entry->subject_name ?? 'N/A' }}</td>
                        <td>
                            {{ $entry->grade ?? '-' }}
                        </td>
                        <td>
                             @php
                                $status = $entry->status ?? 'pending';
                             @endphp
                             <span class="status {{ $status }}">{{ ucfirst($status) }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($proof)
        <h3>Proof of Submission</h3>
        <div class="proof-container">
            <p><strong>Status:</strong> {{ ucfirst($proof->status) }}</p>
            <div class="proof-file">
                @if($proof->file_type === 'pdf')
                    <iframe src="{{ asset('storage/' . $proof->file_path) }}" width="100%" height="500px"></iframe>
                @elseif(in_array($proof->file_type, ['jpg', 'jpeg', 'png']))
                    <img src="{{ asset('storage/' . $proof->file_path) }}" alt="Proof" style="max-width: 100%; max-height: 500px;">
                @else
                    <a href="{{ asset('storage/' . $proof->file_path) }}" download="{{ $proof->file_name }}">Download Proof</a>
                @endif
            </div>
        </div>
    @endif

    <div class="back-link" style="margin-top: 20px;">
        <a href="{{ route('student.grade-submissions.list') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>
            Back to Grade Submissions
        </a>
    </div>

</div>

<style>
.submission-view-container {
    padding: 20px;
    max-width: 800px;
    margin: 20px auto;
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.submission-view-container h1 {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
    text-align: center;
}

.submission-details {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    border: 1px solid #e9ecef;
}

.submission-details p {
    margin: 5px 0;
    color: #555;
}

.submission-view-container h3 {
    margin-top: 25px;
    margin-bottom: 15px;
    color: #555;
    font-size: 20px;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}

.subjects-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.subjects-table th,
.subjects-table td {
    border: 1px solid #ddd;
    padding: 10px;
    text-align: left;
}

.subjects-table th {
    background-color: #f2f2f2;
    font-weight: bold;
    color: #333;
}

.status {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 14px;
    font-weight: 500;
}

.status.pending {
    background: #fff3cd;
    color: #856404;
}

.status.approved {
    background: #d4edda;
    color: #155724;
}

.status.rejected {
    background: #f8d7da;
    color: #721c24;
}

.status.submitted {
     background-color: #cce5ff;
     color: #004085;
}

.btn-secondary {
    display: inline-block;
    padding: 8px 16px;
    color: #fff;
    background-color: #6c757d;
    border-color: #6c757d;
    text-align: center;
    vertical-align: middle;
    border: 1px solid transparent;
    border-radius: .25rem;
    text-decoration: none;
    transition: color .15s ease-in-out,background-color .15s ease-in-out,border-color .15s ease-in-out,box-shadow .15s ease-in-out;
}

.btn-secondary:hover {
    color: #fff;
    background-color: #5a6268;
    border-color: #545b62;
}

</style>
<style>
/* Responsive adjustments for student submission detail */
@media (max-width: 768px) {
  .submission-view-container { padding: 15px; margin: 10px; }
  .subjects-table { display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; }
  .subjects-table table { min-width: 520px; }
  thead th { position: sticky; top: 0; z-index: 1; }
  .btn-secondary { width: 100%; text-align: center; }
}

@media (max-width: 480px) {
  .submission-view-container h1 { font-size: 20px; }
  .submission-details { padding: 12px; }
}
</style>
@endsection 