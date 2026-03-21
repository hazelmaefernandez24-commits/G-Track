@extends('layouts.educator')

@section('title', 'Manual Analytics')

@section('content')
<div class="container mt-5">
    
    
    <!-- Summary Statistics Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-light p-3">
                <div class="row text-center" id="summaryStats">
                    <div class="col-md-4">
                        <h5 class="text-primary mb-1">📊 Total: <span id="totalViolations">0</span> violations</h5>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-danger mb-1">🔺 Most: <span id="mostCategory">-</span> (<span id="mostCount">0</span>)</h5>
                    </div>
                    <div class="col-md-4">
                        <h5 class="text-success mb-1">🔻 Least: <span id="leastCategory">-</span> (<span id="leastCount">0</span>)</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row mb-3">
        <div class="col-md-3">
            <label for="filterTime" class="form-label">Time Range</label>
            <select id="filterTime" class="form-select">
                <option value="all">All Time</option>
                <option value="this_month">This Month</option>
                <option value="this_year">This Year</option>
                <option value="last_30_days">Last 30 Days</option>
                <option value="custom">Custom Range...</option>
            </select>
        </div>
        <div class="col-md-3" id="customDateRange" style="display:none;">
            <label for="startDate" class="form-label">Start Date</label>
            <input type="date" id="startDate" class="form-control">
            <label for="endDate" class="form-label mt-2">End Date</label>
            <input type="date" id="endDate" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="filterClass" class="form-label">Class</label>
            <select id="filterClass" class="form-select">
                <option value="all">All</option>
                <option value="2025">2025</option>
                <option value="2026">2026</option>
            </select>
        </div>
    </div>
    <div class="card p-4" style="min-height: 500px;">
        <canvas id="violationsByCategoryChart" style="height:400px;max-height:400px;width:100%;" height="400"></canvas>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
let chartInstance = null;

function updateSummaryStats(categories, counts) {
    // Calculate total violations
    const totalViolations = counts.reduce((sum, count) => sum + count, 0);
    
    // Find most and least violated categories
    let mostCategory = '-', mostCount = 0;
    let leastCategory = '-', leastCount = Infinity;
    
    if (categories.length > 0 && counts.length > 0) {
        categories.forEach((category, index) => {
            const count = counts[index] || 0;
            if (count > mostCount) {
                mostCount = count;
                mostCategory = category;
            }
            if (count < leastCount && count > 0) {
                leastCount = count;
                leastCategory = category;
            }
        });
        
        // If no violations found, reset least category
        if (leastCount === Infinity) {
            leastCategory = '-';
            leastCount = 0;
        }
    }
    
    // Update the DOM elements
    document.getElementById('totalViolations').textContent = totalViolations;
    document.getElementById('mostCategory').textContent = mostCategory;
    document.getElementById('mostCount').textContent = mostCount;
    document.getElementById('leastCategory').textContent = leastCategory;
    document.getElementById('leastCount').textContent = leastCount;
}

function computeYAxisMax(values) {
    const maxVal = Math.max(0, ...(values || [0]));
    const rounded = Math.max(10, Math.ceil(maxVal / 10) * 10);
    return Math.min(100, rounded);
}

function fetchAndRenderChart() {
    const time = document.getElementById('filterTime').value;
    const classYear = document.getElementById('filterClass').value;
    let params = new URLSearchParams({ time, class: classYear });
    if (time === 'custom') {
        const startDate = document.getElementById('startDate').value;
        const endDate = document.getElementById('endDate').value;
        if (startDate) params.append('start_date', startDate);
        if (endDate) params.append('end_date', endDate);
    }
    fetch('/educator/manual/analytics/data?' + params.toString())
        .then(response => response.json())
        .then(data => {
            const categories = data.categories || [];
            const countsAll = data.counts_all || [];
            const countsMale = data.counts_male || [];
            const countsFemale = data.counts_female || [];
            const yMax = computeYAxisMax([...countsAll, ...countsMale, ...countsFemale]);
            
            // Update summary statistics
            updateSummaryStats(categories, countsAll);
            const ctx = document.getElementById('violationsByCategoryChart').getContext('2d');
            if (chartInstance) chartInstance.destroy();
            chartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: categories,
                    datasets: [
                        {
                            label: 'All',
                            data: countsAll,
                            backgroundColor: '#55A6F3',
                            borderColor: '#2e7bd9',
                            borderWidth: 1,
                            borderRadius: 6,
                            maxBarThickness: 40,
                            barPercentage: 0.7,
                            categoryPercentage: 0.6,
                            minBarLength: 8
                        },
                        {
                            label: 'Men',
                            data: countsMale,
                            backgroundColor: '#7ECF8B',
                            borderColor: '#55b366',
                            borderWidth: 1,
                            borderRadius: 6,
                            maxBarThickness: 40,
                            barPercentage: 0.7,
                            categoryPercentage: 0.6,
                            minBarLength: 8
                        },
                        {
                            label: 'Women',
                            data: countsFemale,
                            backgroundColor: '#F39AB6',
                            borderColor: '#d66b8f',
                            borderWidth: 1,
                            borderRadius: 6,
                            maxBarThickness: 40,
                            barPercentage: 0.7,
                            categoryPercentage: 0.6,
                            minBarLength: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: true },
                        title: { display: false },
                    },
                    scales: {
                        x: { title: { display: true, text: 'Violation Category' }, stacked: false },
                        y: {
                            beginAtZero: true,
                            title: { display: true, text: 'Number of Students' },
                            max: yMax,
                            ticks: { stepSize: yMax >= 50 ? 10 : 5 }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading analytics data:', error);
        });
}

document.addEventListener('DOMContentLoaded', function() {
    fetchAndRenderChart();
    document.getElementById('filterTime').addEventListener('change', function() {
        const showCustom = this.value === 'custom';
        document.getElementById('customDateRange').style.display = showCustom ? '' : 'none';
        fetchAndRenderChart();
    });
    document.getElementById('filterClass').addEventListener('change', fetchAndRenderChart);
    document.getElementById('startDate').addEventListener('change', fetchAndRenderChart);
    document.getElementById('endDate').addEventListener('change', fetchAndRenderChart);
});
</script>
@endsection
