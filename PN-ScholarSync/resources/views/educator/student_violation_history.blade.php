@extends('layouts.educator')

@section('title', 'Violation History')

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
.date-column {
    min-width: 80px;
    text-align: center;
    vertical-align: middle;
}
.date-display {
    line-height: 1.3;
    font-size: 0.9em;
}
.violation-history-table {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.violation-history-table thead {
    background: #5a6c7d;
    color: white;
}

.violation-history-table thead th {
    padding: 15px 12px;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border: none;
    text-align: center;
}

.violation-history-table tbody tr {
    border-bottom: 1px solid #e9ecef;
}

.violation-history-table tbody tr:hover {
    background-color: #f8f9fa;
}

.violation-history-table tbody td {
    padding: 15px 12px;
    vertical-align: middle;
    border: none;
    font-size: 13px;
}

.student-info {
    font-weight: 600;
    color: #2c3e50;
}

.student-id {
    font-size: 11px;
    color: #6c757d;
    display: block;
    margin-top: 2px;
}

.severity-badge {
    padding: 4px 12px;
    border-radius: 15px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.severity-high {
    background: #dc3545;
    color: white;
}

.severity-low {
    background: #28a745;
    color: white;
}

.severity-medium {
    background: #ffc107;
    color: #212529;
}

.action-taken-container {
    display: flex;
    gap: 8px;
    justify-content: center;
    align-items: center;
}

.action-checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid #28a745;
    border-radius: 3px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: white;
    position: relative;
}

.action-checkbox.checked {
    background: #28a745;
}

.action-checkbox.checked::after {
    content: '✓';
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.action-label {
    font-size: 11px;
    font-weight: 600;
    color: #495057;
}

.remarks-text {
    font-size: 12px;
    color: #6c757d;
}

.prepared-by {
    font-size: 12px;
    color: #495057;
}
</style>
@endsection

@section('content')
<div class="container mt-4">

    <h4 class="mb-3">Violation History</h4>

    <div class="card">
        <div class="card-header" style="background: linear-gradient(135deg, #4fc3f7 0%, #29b6f6 50%, #03a9f4 100%); color: white; padding: 20px 25px; border-radius: 8px 8px 0 0;">
            <div>
                <h4 class="mb-1 fw-bold" style="font-size: 1.5rem; letter-spacing: 0.5px;">{{ $student->name }}</h4>
                <div class="d-flex align-items-center mt-2">
                    <span class="badge px-3 py-2" style="background-color: rgba(255, 255, 255, 0.9); color: #2c3e50; font-size: 0.85rem; border-radius: 20px; font-weight: 600; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                        <i class="fas fa-info-circle me-2" style="color: #3498db;"></i>Status: {{ $studentStatus }}
                    </span>
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            @if($violations->isEmpty())
                <div class="alert alert-success m-3">No violations found for this student.</div>
            @else
                <div class="table-responsive">
                    <table class="table violation-history-table mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th>Severity</th>
                                <th>Penalty</th>
                                <th>Consequence</th>
                                <th>Status</th>
                                <th>Date Resolved</th>
                                <th>Incident Date/Time</th>
                                <th>Place</th>
                                <th>Details</th>
                                <th>Prepared By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($violations as $violation)
                            <tr>
                                <td class="date-column">
                                    @php
                                        $date = \Carbon\Carbon::parse($violation->violation_date)->format('M d, Y');
                                        $parts = explode(', ', $date);
                                        if (count($parts) === 2) {
                                            echo '<div class="date-display">' . $parts[0] . ',<br>' . $parts[1] . '</div>';
                                        } else {
                                            echo $date;
                                        }
                                    @endphp
                                </td>
                                <td>{{ $violation->violationType->violation_name ?? 'N/A' }}</td>
                                <td style="text-align: center;">
                                    @php
                                        $severity = $violation->severity ?? ($violation->violationType->severity ?? 'N/A');
                                        $severityClass = match(strtolower($severity)) {
                                            'high' => 'severity-high',
                                            'low' => 'severity-low',
                                            'medium' => 'severity-medium',
                                            default => 'severity-medium'
                                        };
                                    @endphp
                                    <span class="severity-badge {{ $severityClass }}">{{ $severity }}</span>
                                </td>

                                <td>{{ $violation->penalty ?? 'N/A' }}</td>
                                <td>{{ $violation->consequence ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ $violation->status === 'active' ? 'bg-danger' : 'bg-secondary' }}">
                                        {{ ucfirst($violation->status) }}
                                    </span>
                                </td>
                                <td class="date-column">
                                    @if($violation->status === 'resolved' && $violation->updated_at)
                                        @php
                                            $resolvedDate = \Carbon\Carbon::parse($violation->updated_at)->format('M d, Y');
                                            $parts = explode(', ', $resolvedDate);
                                            if (count($parts) === 2) {
                                                echo '<div class="date-display">' . $parts[0] . ',<br>' . $parts[1] . '</div>';
                                            } else {
                                                echo $resolvedDate;
                                            }
                                        @endphp
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{ $violation->incident_datetime ?? 'N/A' }}</td>
                                <td>{{ $violation->incident_place ?? 'N/A' }}</td>
                                <td>{{ $violation->incident_details ?? 'N/A' }}</td>
                                <td>
                                    <div class="prepared-by">{{ $violation->prepared_by ?? 'N/A' }}</div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            <div class="p-3">
                <a href="{{ url('/educator/students') }}" class="btn btn-secondary">Back to Students List</a>
            </div>
        </div>
    </div>
</div>
@endsection
