@extends('layouts.educator')

@section('title', 'Invalid Students Catcher')

@section('css')
<link rel="stylesheet" href="{{ asset('css/educator/violation.css') }}">
<style>
    .catcher-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stats-card {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .stat-number {
        font-size: 2.5rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .catch-button {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .catch-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
        color: white;
    }
    
    .student-card {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        border-left: 4px solid #dc3545;
    }
    
    .student-card.processed {
        border-left-color: #28a745;
        background: #f8fff9;
    }
    
    .process-btn {
        background: #28a745;
        border: none;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">
            <i class="fas fa-exclamation-triangle text-danger me-2"></i>
            Invalid Students Catcher
        </h2>
        <form method="POST" action="{{ route('educator.invalid-students.catch') }}" style="display: inline;">
            @csrf
            <button type="submit" class="btn catch-button" onclick="return confirm('Catch all invalid students from G16_CAPSTONE?')">
                <i class="fas fa-net-fishing me-2"></i>Catch Invalid Students
            </button>
        </form>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error') || isset($error))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') ?? $error }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Statistics -->
    <div class="stats-card">
        <div class="stat-number">{{ $totalCount }}</div>
        <div>Invalid Students Caught</div>
        <small>From G16_CAPSTONE System</small>
    </div>

    <!-- Caught Students List -->
    <div class="catcher-card">
        <h4 class="mb-3">
            <i class="fas fa-users text-danger me-2"></i>
            Caught Invalid Students
        </h4>
        
        @if($caughtStudents->count() > 0)
            <div style="max-height: 600px; overflow-y: auto;">
                @foreach($caughtStudents as $student)
                    <div class="student-card {{ $student->status === 'processed' ? 'processed' : '' }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <strong>{{ $student->student_name }}</strong>
                                @if($student->student_id_code)
                                    <span class="badge bg-secondary ms-2">{{ $student->student_id_code }}</span>
                                @endif
                                @if($student->status === 'processed')
                                    <span class="badge bg-success ms-2">Processed</span>
                                @else
                                    <span class="badge bg-danger ms-2">Caught</span>
                                @endif
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-tasks me-1"></i>{{ ucfirst($student->task_category) }}
                                    @if($student->batch)
                                        | <i class="fas fa-graduation-cap me-1"></i>Batch {{ $student->batch }}
                                    @endif
                                    @if($student->gender)
                                        | <i class="fas fa-user me-1"></i>{{ ucfirst($student->gender) }}
                                    @endif
                                </small>
                                @if($student->description)
                                    <br>
                                    <small class="text-muted">{{ Str::limit($student->description, 100) }}</small>
                                @endif
                                <br>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>Validated: {{ \Carbon\Carbon::parse($student->validated_at)->format('M d, Y H:i') }}
                                    | <i class="fas fa-clock me-1"></i>Caught: {{ \Carbon\Carbon::parse($student->caught_at)->format('M d, Y H:i') }}
                                </small>
                            </div>
                            <div class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="{{ route('educator.invalid-students.show', $student->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    @if($student->status === 'caught')
                                        <button class="btn process-btn btn-sm" onclick="markAsProcessed({{ $student->id }})">
                                            <i class="fas fa-check me-1"></i>Mark Processed
                                        </button>
                                    @endif
                                </div>
                                <small class="text-muted d-block mt-1">G16 ID: {{ $student->g16_submission_id }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-4">
                <i class="fas fa-info-circle text-info fa-3x mb-3"></i>
                <p class="text-muted">No invalid students caught yet.</p>
                <p class="text-muted">Click "Catch Invalid Students" to scan G16_CAPSTONE for invalid task reports.</p>
            </div>
        @endif
    </div>

    <!-- How It Works -->
    <div class="catcher-card">
        <h4 class="mb-3">
            <i class="fas fa-info-circle text-info me-2"></i>
            How the Catcher Works
        </h4>
        <div class="row">
            <div class="col-md-3 text-center">
                <i class="fas fa-search fa-2x text-primary mb-2"></i>
                <h6>1. Scan G16_CAPSTONE</h6>
                <small class="text-muted">Searches for task reports marked as "Invalid"</small>
            </div>
            <div class="col-md-3 text-center">
                <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                <h6>2. Extract Student Info</h6>
                <small class="text-muted">Gets student names, IDs, and task details</small>
            </div>
            <div class="col-md-3 text-center">
                <i class="fas fa-database fa-2x text-warning mb-2"></i>
                <h6>3. Store Locally</h6>
                <small class="text-muted">Saves invalid student data in PN-ScholarSync</small>
            </div>
            <div class="col-md-3 text-center">
                <i class="fas fa-list fa-2x text-info mb-2"></i>
                <h6>4. Track & Manage</h6>
                <small class="text-muted">Monitor and process caught students</small>
            </div>
        </div>
    </div>
</div>

<script>
function markAsProcessed(studentId) {
    if (!confirm('Mark this student as processed?')) {
        return;
    }

    fetch(`/educator/invalid-students/${studentId}/mark-processed`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload(); // Refresh to show updated status
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>
@endsection
