@extends('layouts.educator')

@section('content')
<div class="container">
    <h2>Invalid Violation Details</h2>

    <div class="violation-details card p-3 mt-3">
        <div class="mb-2"><strong>Student:</strong>
            @if($violation->student)
                {{ $violation->student->user_fname }} {{ $violation->student->user_lname }}
            @else
                <span class="text-danger">Student data not available (ID: {{ $violation->student_id ?? 'N/A' }})</span>
            @endif
        </div>

        <div class="mb-2"><strong>Date:</strong> {{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}</div>

        <div class="mb-2"><strong>Category:</strong>
            {{ $violation->violationType->offenseCategory->category_name ?? 'Center Tasking' }}
        </div>

        <div class="mb-2"><strong>Violation:</strong>
            {{ $violation->violationType->violation_name ?? 'Invalid task submission' }}
        </div>

        <div class="mb-2"><strong>Severity:</strong> {{ $violation->severity ?? 'Low' }}</div>
        <div class="mb-2"><strong>Penalty:</strong> {{ $violation->penalty ?? 'VW' }}</div>

        <div class="mb-2"><strong>Consequence:</strong> {{ $violation->consequence ?? 'Invalid task submission' }}</div>
        <div class="mb-2"><strong>Consequence Status:</strong> {{ ucfirst($violation->consequence_status ?? 'active') }}</div>

        @if($violation->incident_datetime || $violation->incident_place || $violation->incident_details)
            <hr>
            <h5>Incident Details</h5>
            @if($violation->incident_datetime)
                <div class="mb-2"><strong>Date & Time:</strong> {{ \Carbon\Carbon::parse($violation->incident_datetime)->format('M d, Y g:i A') }}</div>
            @endif
            @if($violation->incident_place)
                <div class="mb-2"><strong>Place:</strong> {{ $violation->incident_place }}</div>
            @endif
            @if($violation->incident_details)
                <div class="mb-2"><strong>Details:</strong> {{ $violation->incident_details }}</div>
            @endif
        @endif

        <div class="mb-2"><strong>Prepared By:</strong> {{ $violation->prepared_by ?? 'G16 Bridge' }}</div>
        <div class="mb-2"><strong>Status:</strong> {{ ucfirst($violation->status ?? 'active') }}</div>
    </div>

    <div class="mt-3">
        <a href="{{ route('educator.violation') }}" class="btn btn-secondary">Back to List</a>
    </div>
</div>
@endsection
