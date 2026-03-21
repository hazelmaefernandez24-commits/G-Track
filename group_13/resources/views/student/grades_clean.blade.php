@extends('layouts.student_layout')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.css" rel="stylesheet">
<style>
    /* Layout */
    .dashboard-container {
        padding: 1.5rem;
    }
    
    /* Grade Status Chart */
    .grade-status-chart {
        background: white;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin: 20px 0;
    }
    
    /* Print Styles */
    @media print {
        .no-print {
            display: none !important;
        }
    }
</style>
@endpush

@section('content')
<div class="dashboard-container">
    <!-- Grade Status Chart -->
    @if(isset($subjectsWithGrades) && $subjectsWithGrades->count() > 0)
        @php
            $statuses = ['approved', 'pending', 'rejected', 'incomplete', 'no credit', 'dropped', 'passed', 'failed'];
            $statusCounts = array_fill_keys($statuses, 0);

            foreach($subjectsWithGrades as $subject) {
                $status = strtolower($subject->status ?? ($subject->pivot->status ?? 'pending'));
                $status = in_array($status, $statuses) ? $status : 'pending';
                $statusCounts[$status]++;
            }
        @endphp
        <div class="grade-status-chart">
            <h5>Grade Status Distribution</h5>
            <div class="chart-container">
                <canvas id="gradeStatusChart"></canvas>
            </div>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Grade Status</h1>
        <div>
            <button class="btn btn-outline-secondary" onclick="window.print()">
                <i class="fas fa-print me-1"></i> Print
            </button>
        </div>
    </div>

    <!-- Rest of your existing content -->
    <div class="tab-content" id="gradeTabsContent">
        <!-- All Grades Tab -->
        <div class="tab-pane fade show active" id="all" role="tabpanel" aria-labelledby="all-tab">
            @include('student.partials.grades_table', [
                'grades' => $subjectsWithGrades,
                'showActions' => true
            ])
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.0/dist/chart.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));

    // Initialize Grade Status Chart
    const ctx = document.getElementById('gradeStatusChart');
    if (!ctx) return;

    const statusCounts = @json($statusCounts ?? []);
    if (Object.keys(statusCounts).length === 0) {
        ctx.closest('.chart-container').innerHTML = `
            <div class="text-center p-4">
                <p class="text-muted">No grade data available yet.</p>
            </div>`;
        return;
    }


    const labels = Object.keys(statusCounts).filter(label => statusCounts[label] > 0);
    const data = labels.map(label => statusCounts[label]);
    
    const statusColors = {
        'approved': 'rgba(40, 167, 69, 0.8)',
        'pass': 'rgba(40, 167, 69, 0.8)',
        'pending': 'rgba(255, 193, 7, 0.8)',
        'rejected': 'rgba(220, 53, 69, 0.8)',
        'fail': 'rgba(220, 53, 69, 0.8)',
        'incomplete': 'rgba(23, 162, 184, 0.8)',
        'no credit': 'rgba(108, 117, 125, 0.8)',
        'dropped': 'rgba(52, 58, 64, 0.8)'
    };
    
    const backgroundColors = labels.map(status => statusColors[status.toLowerCase()] || 'rgba(108, 117, 125, 0.8)');
    const borderColors = backgroundColors.map(color => color.replace('0.8', '1'));
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels.map(label => label.charAt(0).toUpperCase() + label.slice(1)),
            datasets: [{
                label: 'Number of Subjects',
                data: data,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1,
                borderRadius: 4,
                barThickness: 'flex',
                maxBarThickness: 40
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: { size: 14, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 12,
                    cornerRadius: 6,
                    displayColors: false,
                    callbacks: {
                        label: ctx => `${ctx.parsed.y} subject${ctx.parsed.y !== 1 ? 's' : ''}`
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { 
                        precision: 0, 
                        font: { size: 12 },
                        stepSize: 1
                    },
                    grid: { 
                        display: true, 
                        drawBorder: false, 
                        color: 'rgba(0, 0, 0, 0.05)' 
                    },
                    title: { 
                        display: true, 
                        text: 'Number of Subjects', 
                        font: { size: 12 } 
                    }
                },
                x: {
                    grid: { display: false },
                    ticks: { 
                        font: { size: 12 }
                    },
                    title: { 
                        display: true, 
                        text: 'Status', 
                        font: { size: 12 } 
                    }
                }
            }
        }
    });
});
</script>
@endpush
