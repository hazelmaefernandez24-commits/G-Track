@extends('layouts.apps')

@section('styles')
<style>
  /* Main container styling */
  .main-content {
    padding: 20px !important;
    background-color: #f8f9fa;
    min-height: 100vh;
  }

  /* Header styling */
  .dashboard-title {
    color: #333;
    font-weight: 600;
    margin-bottom: 20px !important;
  }

  /* Container optimization */
  .container-fluid {
    max-width: 100%;
    padding-left: 15px;
    padding-right: 15px;
  }

  /* Row layout - exactly 3 cards per row */
  .row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -10px;
    justify-content: flex-start;
    align-items: flex-start;
  }

  /* Column layout - perfect 3-column grid */
  .col-xl-4, .col-lg-4, .col-md-4 {
    flex: 0 0 calc(33.333% - 20px);
    max-width: calc(33.333% - 20px);
    padding: 0 10px;
    margin-bottom: 20px;
  }

  /* Card styling to match screenshot */
  .card {
    width: 100% !important;
    max-width: 100% !important;
    min-height: 450px;
    margin: 0 auto;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: 1px solid #dee2e6;
    border-radius: 6px;
    background: white;
  }

  /* Card header - blue background like screenshot */
  .card-header {
    background-color: #007bff !important;
    color: white !important;
    padding: 12px 16px;
    border-bottom: none;
    border-radius: 6px 6px 0 0 !important;
  }

  .card-header h5 {
    margin: 0;
    font-size: 1.1rem;
    font-weight: 600;
  }

  .card-header small {
    font-size: 0.85rem;
    opacity: 0.9;
  }

  /* Card body styling */
  .card-body {
    padding: 16px;
    background: white;
  }

  /* Batch headers */
  .batch-header {
    font-size: 0.9rem;
    font-weight: 700;
    color: #007bff;
    margin-bottom: 8px;
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 4px;
  }

  /* Member list styling */
  .member-list {
    padding: 0;
  }

  .member-item {
    display: flex;
    align-items: center;
    padding: 4px 8px;
    line-height: 1.4;
    border-bottom: none;
    border-radius: 4px;
    margin-bottom: 2px;
  }

  .coordinator-highlight {
    background-color: #fff3cd !important;
    border: 1px solid #ffeaa7 !important;
    box-shadow: 0 1px 3px rgba(255, 193, 7, 0.3) !important;
  }

  .coordinator-highlight .member-name {
    font-weight: 600 !important;
    color: #856404 !important;
  }

  .coordinator-indicator {
    color: #28a745 !important;
    font-size: 0.7rem !important;
    font-weight: 600 !important;
    margin-left: 8px !important;
  }

  .member-info {
    display: flex;
    align-items: center;
    gap: 6px;
  }

  .member-name {
    color: #333;
    font-size: 0.85rem;
    font-weight: 500;
  }

  .member-gender {
    color: #6c757d;
    font-size: 0.75rem;
  }

  /* Coordinator highlighting for table cells */
  .coordinator-highlight-cell {
    background-color: #fff3cd !important;
    border: 1px solid #ffeaa7 !important;
    padding: 8px !important;
  }

  .coordinator-name {
    font-weight: 600 !important;
    color: #856404 !important;
  }

  /* Table styling */
  .table td {
    vertical-align: middle;
    padding: 8px 12px;
    font-size: 0.85rem;
    line-height: 1.4;
  }

  .table th {
    padding: 10px 12px;
    font-size: 0.9rem;
    border-bottom: 2px solid #dee2e6;
  }



  /* Batch columns spacing */
  .card-body .row .col-md-6 {
    padding-left: 8px;
    padding-right: 8px;
  }

  /* No members text */
  .text-muted.small {
    font-size: 0.8rem;
    font-style: italic;
  }

  /* Hover effect */
  .card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    transition: all 0.2s ease;
  }

  /* Responsive adjustments */
  @media (max-width: 1200px) {
    .col-xl-4, .col-lg-4, .col-md-4 {
      flex: 0 0 calc(50% - 20px);
      max-width: calc(50% - 20px);
    }
  }

  @media (max-width: 768px) {
    .col-xl-4, .col-lg-4, .col-md-4 {
      flex: 0 0 calc(100% - 20px);
      max-width: calc(100% - 20px);
    }

    .main-content {
      padding: 15px !important;
    }
  }
</style>
@endsection

@section('content')
<div class="container-fluid main-content">
  <div class="d-flex justify-content-between align-items-center">
    <h2 class="dashboard-title">All Assignments</h2>
    <a href="{{ url('/assignments/create') }}" class="btn btn-primary">
      <i class="bi bi-plus-circle me-2"></i>Assign Students to Task
    </a>
  </div>

  @if($assignments->count() > 0)
    <div class="row">
      @foreach($assignments as $assignment)
        <div class="col-xl-4 col-lg-4 col-md-4">
          <div class="card">
            <div class="card-header bg-primary text-white">
              <h5 class="card-title mb-0">{{ $assignment->category->name }}</h5>
              <small>{{ $assignment->start_date }} - {{ $assignment->end_date }}</small>
            </div>
            <div class="card-body">
              @if($assignment->assignmentMembers->count() > 0)
                @php
                  $members2025 = $assignment->assignmentMembers->where('student.batch', 2025)->sortBy('student.name');
                  $members2026 = $assignment->assignmentMembers->where('student.batch', 2026)->sortBy('student.name');
                @endphp

                @php
                  $maxRows = max($members2025->count(), $members2026->count());
                  $members2025Values = $members2025->values();
                  $members2026Values = $members2026->values();
                @endphp

                <table class="table table-bordered table-sm">
                  <thead class="table-light">
                    <tr>
                      <th class="text-center fw-bold" style="width: 50%;">Batch 2025</th>
                      <th class="text-center fw-bold" style="width: 50%;">Batch 2026</th>
                    </tr>
                  </thead>
                  <tbody>
                    @for($i = 0; $i < $maxRows; $i++)
                      <tr>
                        <td class="{{ isset($members2025Values[$i]) && $members2025Values[$i]->is_coordinator ? 'coordinator-highlight-cell' : '' }}">
                          @if(isset($members2025Values[$i]))
                            <span class="{{ $members2025Values[$i]->is_coordinator ? 'coordinator-name' : '' }}">
                              {{ $members2025Values[$i]->student->name }} ({{ ucfirst($members2025Values[$i]->student->gender) }})
                            </span>
                            @if($members2025Values[$i]->is_coordinator)
                              <span class="coordinator-indicator">[COORDINATOR]</span>
                            @endif
                            @if($members2025Values[$i]->comments)
                              <span class="text-muted small"> ({{ $members2025Values[$i]->comments }})</span>
                            @endif
                          @endif
                        </td>
                        <td class="{{ isset($members2026Values[$i]) && $members2026Values[$i]->is_coordinator ? 'coordinator-highlight-cell' : '' }}">
                          @if(isset($members2026Values[$i]))
                            <span class="{{ $members2026Values[$i]->is_coordinator ? 'coordinator-name' : '' }}">
                              {{ $members2026Values[$i]->student->name }} ({{ ucfirst($members2026Values[$i]->student->gender) }})
                            </span>
                            @if($members2026Values[$i]->is_coordinator)
                              <span class="coordinator-indicator">[COORDINATOR]</span>
                            @endif
                            @if($members2026Values[$i]->comments)
                              <span class="text-muted small"> ({{ $members2026Values[$i]->comments }})</span>
                            @endif
                          @endif
                        </td>
                      </tr>
                    @endfor
                  </tbody>
                </table>
              @else
                <p class="text-muted mb-0">No members assigned yet.</p>
              @endif
            </div>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div class="text-center py-5">
      <div class="card">
        <div class="card-body">
          <i class="bi bi-clipboard-x display-1 text-muted mb-3"></i>
          <h4 class="text-muted">No Assignments Found</h4>
          <p class="text-muted">Start by creating your first assignment.</p>
          <a href="{{ url('/assignments/create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Create Assignment
          </a>
        </div>
      </div>
    </div>
  @endif
</div>
@endsection