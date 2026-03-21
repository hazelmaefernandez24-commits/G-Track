@props(['violation'])

@php
    $statusColors = [
        'pending' => 'warning',
        'active' => 'danger',
        'resolved' => 'success'
    ];
    
    $statusText = [
        'pending' => 'Pending',
        'active' => 'Active',
        'resolved' => 'Resolved'
    ];
    
    $statusColor = $statusColors[$violation->consequence_status] ?? 'secondary';
    $statusDisplay = $statusText[$violation->consequence_status] ?? 'Unknown';
    
    $remainingTime = null;
    if ($violation->consequence_status === 'active' && $violation->consequence_end_date) {
        $remainingTime = $violation->getRemainingConsequenceTime();
    }
@endphp

<div class="consequence-status">
    <span class="badge bg-{{ $statusColor }}">{{ $statusDisplay }}</span>
    
    @if($violation->consequence_status === 'active')
        @if($remainingTime)
            <small class="d-block text-muted mt-1">
                <i class="fas fa-clock me-1"></i>{{ $remainingTime }} remaining
            </small>
        @endif
        
        @if($violation->consequence_end_date)
            <small class="d-block text-muted mt-1">
                <i class="fas fa-calendar-alt me-1"></i>Ends: {{ $violation->consequence_end_date->format('M d, Y') }}
            </small>
        @endif
    @endif
    
    @if($violation->consequence_status === 'resolved' && $violation->consequence_end_date)
        <small class="d-block text-muted mt-1">
            <i class="fas fa-check-circle me-1"></i>Ended: {{ $violation->consequence_end_date->format('M d, Y') }}
        </small>
    @endif
</div>
