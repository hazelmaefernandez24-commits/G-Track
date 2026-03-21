@extends('layouts.educator')

@section('title', 'Invalid Student Details')

@section('content')
<div class="container" style="max-width: 900px;">
  <h2 class="mb-4">Violation Details</h2>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <div class="row mb-2">
        <div class="col-4 fw-semibold text-muted">Student:</div>
        <div class="col-8">{{ $invalid->student_name }} @if($invalid->student_id_code)<span class="badge bg-secondary ms-2">{{ $invalid->student_id_code }}</span>@endif</div>
      </div>

      <div class="row mb-2">
        <div class="col-4 fw-semibold text-muted">Violation Date:</div>
        <div class="col-8">{{ $invalid->validated_at ? \Carbon\Carbon::parse($invalid->validated_at)->format('M d, Y') : '—' }}</div>
      </div>

      <div class="row mb-2">
        <div class="col-4 fw-semibold text-muted">Category:</div>
        <div class="col-8">{{ ucfirst($invalid->task_category ?? '—') }}</div>
      </div>

      <div class="row mb-2">
        <div class="col-4 fw-semibold text-muted">Violation Type:</div>
        <div class="col-8">Late academic login/logout.</div>
      </div>

      <div class="row mb-2">
        <div class="col-4 fw-semibold text-muted">Severity:</div>
        <div class="col-8"><span class="text-success">Low</span></div>
      </div>

      <div class="row mb-2">
        <div class="col-4 fw-semibold text-muted">Penalty:</div>
        <div class="col-8">Written Warning</div>
      </div>

      <div class="row mb-3">
        <div class="col-4 fw-semibold text-muted">Consequence:</div>
        <div class="col-8">Pending</div>
      </div>

      <hr>

      <div class="p-3 border rounded-3 bg-light">
        <h6 class="mb-3">Incident Details</h6>
        <div class="row mb-2">
          <div class="col-5 text-muted">Date & Time of Incident:</div>
          <div class="col-7">{{ $invalid->caught_at ? \Carbon\Carbon::parse($invalid->caught_at)->format('M d, Y g:i A') : '—' }}</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 text-muted">Place of Incident:</div>
          <div class="col-7">Center</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 text-muted">Incident Details:</div>
          <div class="col-7">{{ $invalid->description ?? '—' }}</div>
        </div>
        <div class="row mb-2">
          <div class="col-5 text-muted">Prepared By:</div>
          <div class="col-7">{{ $invalid->validated_by ?? '—' }}</div>
        </div>
      </div>

      <div class="row mt-3">
        <div class="col-4 fw-semibold text-muted">Status:</div>
        <div class="col-8">{{ ucfirst($invalid->status ?? 'caught') }}</div>
      </div>

      <div class="d-flex gap-2 mt-4">
        <a href="{{ route('educator.invalid-students') }}" class="btn btn-secondary">Back to List</a>
      </div>
    </div>
  </div>

  <div class="small text-muted">
    <div>G16 Submission ID: {{ $invalid->g16_submission_id }}</div>
    <div>Email: {{ $invalid->student_email ?? '—' }} | Gender: {{ ucfirst($invalid->gender ?? '—') }} | Batch: {{ $invalid->batch ?? '—' }}</div>
  </div>
</div>
@endsection
