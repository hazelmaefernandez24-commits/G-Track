@extends('layouts.educator')

@section('title', 'Task Violation Integration')

@section('css')
<link rel="stylesheet" href="{{ asset('css/educator/violation.css') }}">
<style>
    .integration-card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: white;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 8px;
        text-align: center;
    }
    
    .stat-card.danger {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);
    }
    
    .stat-card.success {
        background: linear-gradient(135deg, #4ecdc4 0%, #44a08d 100%);
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: bold;
        margin-bottom: 0.5rem;
    }
    
    .submission-card {
        border: 1px solid #e9ecef;
        border-radius: 6px;
        padding: 1rem;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
    }
    
    .submission-card.invalid {
        border-left: 4px solid #dc3545;
    }
    
    .sync-button {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .sync-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        color: white;
    }
    
    .preview-button {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        border: none;
        color: white;
        padding: 0.75rem 1.5rem;
        border-radius: 6px;
        font-weight: 600;
        margin-right: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Task Violation Integration</h2>
        <div>
            <button class="btn preview-button" onclick="showPreview()">
                <i class="fas fa-eye me-2"></i>Preview Sync
            </button>
            <form method="POST" action="{{ route('educator.task-violation-integration.sync') }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn sync-button" onclick="return confirm('Are you sure you want to sync all invalid task submissions to violations?')">
                    <i class="fas fa-sync me-2"></i>Sync Invalid Reports
                </button>
            </form>
        </div>
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

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card danger">
            <div class="stat-number">{{ $totalInvalid }}</div>
            <div>Invalid Task Reports</div>
            <small>From G16_CAPSTONE System</small>
        </div>
        <div class="stat-card success">
            <div class="stat-number">{{ $totalSynced }}</div>
            <div>Synced Violations</div>
            <small>Already in PN-ScholarSync</small>
        </div>
        <div class="stat-card">
            <div class="stat-number">{{ $totalInvalid - $totalSynced }}</div>
            <div>Pending Sync</div>
            <small>Ready to be synced</small>
        </div>
    </div>

    <div class="row">
        <!-- Invalid Submissions -->
        <div class="col-md-6">
            <div class="integration-card">
                <h4 class="mb-3">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    Invalid Task Submissions
                </h4>
                
                @if($invalidSubmissions->count() > 0)
                    <div style="max-height: 500px; overflow-y: auto;">
                        @foreach($invalidSubmissions->take(20) as $submission)
                            <div class="submission-card invalid">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $submission['student_name'] }}</strong>
                                        @if($submission['student_id'])
                                            <span class="badge bg-secondary ms-2">{{ $submission['student_id'] }}</span>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-tasks me-1"></i>{{ ucfirst($submission['task_category']) }}
                                            @if($submission['validated_at'])
                                                | <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($submission['validated_at'])->format('M d, Y H:i') }}
                                            @endif
                                        </small>
                                        @if($submission['description'])
                                            <br>
                                            <small class="text-muted">{{ Str::limit($submission['description'], 100) }}</small>
                                        @endif
                                    </div>
                                    <span class="badge bg-danger">Invalid</span>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($invalidSubmissions->count() > 20)
                            <div class="text-center mt-3">
                                <small class="text-muted">... and {{ $invalidSubmissions->count() - 20 }} more submissions</small>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                        <p class="text-muted">No invalid task submissions found!</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Existing Violations -->
        <div class="col-md-6">
            <div class="integration-card">
                <h4 class="mb-3">
                    <i class="fas fa-exclamation-circle text-warning me-2"></i>
                    Synced Violations
                </h4>
                
                @if($existingViolations->count() > 0)
                    <div style="max-height: 500px; overflow-y: auto;">
                        @foreach($existingViolations->take(20) as $violation)
                            <div class="submission-card">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <strong>{{ $violation->student ? $violation->student->name : 'Unknown Student' }}</strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="fas fa-exclamation me-1"></i>{{ $violation->violationType->violation_name ?? 'Unknown Violation' }}
                                            <br>
                                            <i class="fas fa-calendar me-1"></i>{{ \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y') }}
                                        </small>
                                    </div>
                                    <div class="text-end">
                                        <span class="badge bg-{{ $violation->severity === 'Low' ? 'warning' : ($violation->severity === 'Medium' ? 'info' : 'danger') }}">
                                            {{ $violation->severity }}
                                        </span>
                                        <br>
                                        <small class="text-muted">Task ID: {{ $violation->task_submission_id }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        
                        @if($existingViolations->count() > 20)
                            <div class="text-center mt-3">
                                <small class="text-muted">... and {{ $existingViolations->count() - 20 }} more violations</small>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-info-circle text-info fa-3x mb-3"></i>
                        <p class="text-muted">No violations synced yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- How It Works -->
    <div class="integration-card">
        <h4 class="mb-3">
            <i class="fas fa-info-circle text-info me-2"></i>
            How Integration Works
        </h4>
        <div class="row">
            <div class="col-md-3 text-center">
                <i class="fas fa-database fa-2x text-primary mb-2"></i>
                <h6>1. Scan G16_CAPSTONE</h6>
                <small class="text-muted">System scans for task reports marked as "Invalid"</small>
            </div>
            <div class="col-md-3 text-center">
                <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                <h6>2. Identify Students</h6>
                <small class="text-muted">Maps student IDs to actual student names</small>
            </div>
            <div class="col-md-3 text-center">
                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                <h6>3. Create Violations</h6>
                <small class="text-muted">Automatically creates violation records in PN-ScholarSync</small>
            </div>
            <div class="col-md-3 text-center">
                <i class="fas fa-sync fa-2x text-info mb-2"></i>
                <h6>4. Link Systems</h6>
                <small class="text-muted">Maintains connection between both systems</small>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal fade" id="previewModal" tabindex="-1" aria-labelledby="previewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="previewModalLabel">
                    <i class="fas fa-eye me-2"></i>Preview Sync Results
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="previewContent">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Loading preview...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <form method="POST" action="{{ route('educator.task-violation-integration.sync') }}" style="display: inline;">
                    @csrf
                    <button type="submit" class="btn sync-button">
                        <i class="fas fa-sync me-2"></i>Proceed with Sync
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showPreview() {
    const modal = new bootstrap.Modal(document.getElementById('previewModal'));
    modal.show();
    
    fetch('{{ route("educator.task-violation-integration.preview") }}')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                let html = '<div class="table-responsive"><table class="table table-striped">';
                html += '<thead><tr><th>Student</th><th>Task Category</th><th>Violation Type</th><th>Severity</th><th>Penalty</th></tr></thead><tbody>';
                
                data.preview.forEach(item => {
                    html += `<tr>
                        <td><strong>${item.student_name}</strong><br><small class="text-muted">${item.student_id || 'No ID'}</small></td>
                        <td><span class="badge bg-info">${item.task_category}</span></td>
                        <td>${item.violation_type}</td>
                        <td><span class="badge bg-warning">${item.severity}</span></td>
                        <td><span class="badge bg-secondary">${item.penalty}</span></td>
                    </tr>`;
                });
                
                html += '</tbody></table></div>';
                html += `<div class="alert alert-info mt-3"><i class="fas fa-info-circle me-2"></i>This will create ${data.total} new violation records.</div>`;
                
                document.getElementById('previewContent').innerHTML = html;
            } else {
                document.getElementById('previewContent').innerHTML = 
                    `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading preview: ${data.error}</div>`;
            }
        })
        .catch(error => {
            document.getElementById('previewContent').innerHTML = 
                `<div class="alert alert-danger"><i class="fas fa-exclamation-triangle me-2"></i>Error loading preview: ${error.message}</div>`;
        });
}
</script>
@endsection
