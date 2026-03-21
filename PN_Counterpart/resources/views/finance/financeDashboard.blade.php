@extends('layouts.finance')

@section('title', 'Finance Dashboard')

@section('content')
<style>
    .fade-in {
        animation: fadeIn 0.3s ease-in;
    }
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    .card-header {
        background: linear-gradient(90deg, #FF9933, #FFAA55);
        color: white;
    }
    .form-control:focus, .form-select:focus {
        border-color: #FF9933;
        box-shadow: 0 0 0 0.2rem rgba(255, 153, 51, 0.25);
    }
    .spinner {
        display: none;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
    }
    .chart-container {  
        position: relative;
        min-height: 300px;
        width: 100%;
    }
    .no-data {
        text-align: center;
        color: #6c757d;
        padding: 20px;
    }
    /* Mobile-specific styles */
    @media (max-width: 768px) {
        .card-header h5 {
            font-size: 1.1rem;
        }
        .chart-container {
            min-height: 150px;
        }
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        .form-label {
            font-size: 0.9rem;
        }
        .form-select, .form-control {
            font-size: 0.9rem;
        }
        .no-data {
            font-size: 0.9rem;
        }
        .g-3 {
            gap: 0.75rem !important;
        }
    }
    @media (max-width: 576px) {
        .card-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .export-csv-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

<div class="py-4 container-fluid">
    <!-- Summary Boxes -->
    <div class="mb-4 row">
        <div class="col-md-4">
            <div class="border-0 shadow-sm card fade-in">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-container me-3">
                        <i class="fas fa-calendar-alt text-primary" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Monthly Collection</h6>
                        <h4 class="mb-0 fw-bold text-primary">₱<span id="monthlyCollected">0.00</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border-0 shadow-sm card fade-in">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-container me-3">
                        <i class="fas fa-calendar text-success" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Yearly Collection</h6>
                        <h4 class="mb-0 fw-bold text-success">₱<span id="yearlyCollected">0.00</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="border-0 shadow-sm card fade-in">
                <div class="card-body d-flex align-items-center">
                    <div class="icon-container me-3">
                        <i class="fas fa-wallet text-warning" style="font-size: 2rem;"></i>
                    </div>
                    <div>
                        <h6 class="mb-1 text-muted">Overall Collection</h6>
                        <h4 class="mb-0 fw-bold text-warning">₱<span id="overallCollected">0.00</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Batch Differentiated Charts -->
    <div class="mb-4 row">
        <div class="col-md-6">
            <div class="border-0 shadow-sm card fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Monthly Payments by Batch</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="batchMonthlyPaymentsChart"></canvas>
                        <div class="spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="batchMonthlyNoData" class="no-data" style="display: none;">No payment data available.</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="border-0 shadow-sm card fade-in">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-chart-area me-2"></i>Yearly Payments by Batch</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="batchYearlyPaymentsChart"></canvas>
                        <div class="spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="batchYearlyNoData" class="no-data" style="display: none;">No payment data available.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Target vs. Accomplishment Chart -->
    <div class="mb-4 row">
        <div class="col-12">
            <div class="border-0 shadow-sm card fade-in">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-bullseye me-2"></i>Target vs. Accomplishment Collection</h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="targetVsAccomplishmentChart"></canvas>
                        <div class="spinner">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <div id="targetChartNoData" class="no-data" style="display: none;">No target vs accomplishment data available.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        let batchMonthlyPaymentsChart, batchYearlyPaymentsChart, /* yearMonthComparisonChart, */ targetVsAccomplishmentChart;

        const batchMonthlyCtx = document.getElementById('batchMonthlyPaymentsChart').getContext('2d');
        const batchYearlyCtx = document.getElementById('batchYearlyPaymentsChart').getContext('2d');
        // const yearMonthCtx = document.getElementById('yearMonthComparisonChart').getContext('2d');
        const targetVsAccomplishmentCtx = document.getElementById('targetVsAccomplishmentChart').getContext('2d');

        // Helper function to create gradient fills
        function createGradient(ctx, color) {
            const gradient = ctx.createLinearGradient(0, 0, 0, 400);
            gradient.addColorStop(0, color.replace('1)', '0.4)'));
            gradient.addColorStop(1, color.replace('1)', '0.1)'));
            return gradient;
        }

        function showSpinner(show) {
            document.querySelectorAll('.spinner').forEach(spinner => {
                spinner.style.display = show ? 'block' : 'none';
            });
        }

        function fetchPayments(url, params = {}) {
            const query = new URLSearchParams(params).toString();
            showSpinner(true);
            return fetch(`${url}?${query}`)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                    return response.json();
                })
                .catch(error => {
                    console.error(`Error fetching data from ${url}:`, error);
                    return [];
                })
                .finally(() => showSpinner(false));
        }

        function updateCharts() {
            const params = {};

            // Update Batch Monthly Payments Chart
            fetchPayments('/finance/batch-monthly-payments', params).then(data => {
                console.log('Batch monthly payments data:', data);
                const batchMonthlyNoData = document.getElementById('batchMonthlyNoData');
                const batchMonthlyChart = document.getElementById('batchMonthlyPaymentsChart');

                if (!data || data.length === 0) {
                    batchMonthlyNoData.style.display = 'block';
                    batchMonthlyChart.style.display = 'none';
                    return;
                }

                batchMonthlyNoData.style.display = 'none';
                batchMonthlyChart.style.display = 'block';

                // Group data by batch
                const batchGroups = {};
                data.forEach(item => {
                    if (!batchGroups[item.batch_year]) {
                        batchGroups[item.batch_year] = [];
                    }
                    batchGroups[item.batch_year].push({
                        label: `${item.year}-${String(item.month).padStart(2, '0')}`,
                        value: item.total,
                        year: item.year,
                        month: item.month
                    });
                });

                // Create datasets for each batch
                const datasets = [];
                const colors = [
                    'rgba(255, 99, 132, 1)',   // Pink/Red
                    'rgba(54, 162, 235, 1)',   // Blue
                    'rgba(255, 205, 86, 1)',   // Yellow
                    'rgba(75, 192, 192, 1)',   // Teal
                    'rgba(153, 102, 255, 1)',  // Purple
                    'rgba(255, 159, 64, 1)',   // Orange
                    'rgba(46, 204, 113, 1)',   // Green
                    'rgba(231, 76, 60, 1)',    // Red
                    'rgba(52, 152, 219, 1)',   // Light Blue
                    'rgba(155, 89, 182, 1)'    // Violet
                ];

                let colorIndex = 0;
                Object.keys(batchGroups).forEach(batchYear => {
                    const batchData = batchGroups[batchYear];
                    const color = colors[colorIndex % colors.length];

                    datasets.push({
                        label: `Batch ${batchYear}`,
                        data: batchData.map(item => ({ x: item.label, y: item.value })),
                        backgroundColor: createGradient(batchMonthlyCtx, color), // Gradient fill
                        borderColor: color,
                        borderWidth: 3,
                        fill: 'origin', // Fill to the x-axis
                        tension: 0.4,
                        pointBackgroundColor: color,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: color,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3
                    });
                    colorIndex++;
                });

                // Get all unique labels (months) across all batches
                const allLabels = [...new Set(data.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`))].sort();

                if (batchMonthlyPaymentsChart) {
                    batchMonthlyPaymentsChart.destroy();
                }

                batchMonthlyPaymentsChart = new Chart(batchMonthlyCtx, {
                    type: 'line',
                    data: {
                        labels: allLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { size: window.innerWidth < 768 ? 10 : 12 } }
                            },
                            datalabels: {
                                display: false // Disable data labels for cleaner look with multiple lines
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: { size: window.innerWidth < 768 ? 10 : 10 },
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                ticks: { font: { size: window.innerWidth < 768 ? 10 : 10 } }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            });

            // Update Batch Yearly Payments Chart
            fetchPayments('/finance/batch-yearly-payments', params).then(data => {
                console.log('Batch yearly payments data:', data);
                const batchYearlyNoData = document.getElementById('batchYearlyNoData');
                const batchYearlyChart = document.getElementById('batchYearlyPaymentsChart');

                if (!data || data.length === 0) {
                    batchYearlyNoData.style.display = 'block';
                    batchYearlyChart.style.display = 'none';
                    return;
                }

                batchYearlyNoData.style.display = 'none';
                batchYearlyChart.style.display = 'block';

                // Group data by batch
                const batchGroups = {};
                data.forEach(item => {
                    if (!batchGroups[item.batch_year]) {
                        batchGroups[item.batch_year] = [];
                    }
                    batchGroups[item.batch_year].push({
                        label: item.year,
                        value: item.total
                    });
                });

                // Create datasets for each batch
                const datasets = [];
                const colors = [
                    'rgba(255, 99, 132, 1)',   // Pink/Red
                    'rgba(54, 162, 235, 1)',   // Blue
                    'rgba(255, 205, 86, 1)',   // Yellow
                    'rgba(75, 192, 192, 1)',   // Teal
                    'rgba(153, 102, 255, 1)',  // Purple
                    'rgba(255, 159, 64, 1)',   // Orange
                    'rgba(46, 204, 113, 1)',   // Green
                    'rgba(231, 76, 60, 1)',    // Red
                    'rgba(52, 152, 219, 1)',   // Light Blue
                    'rgba(155, 89, 182, 1)'    // Violet
                ];

                let colorIndex = 0;
                Object.keys(batchGroups).forEach(batchYear => {
                    const batchData = batchGroups[batchYear];
                    const color = colors[colorIndex % colors.length];

                    datasets.push({
                        label: `Batch ${batchYear}`,
                        data: batchData.map(item => ({ x: item.label, y: item.value })),
                        backgroundColor: createGradient(batchYearlyCtx, color), // Gradient fill
                        borderColor: color,
                        borderWidth: 3,
                        fill: 'origin', // Fill to the x-axis
                        tension: 0.4,
                        pointBackgroundColor: color,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 5,
                        pointHoverRadius: 8,
                        pointHoverBackgroundColor: color,
                        pointHoverBorderColor: '#fff',
                        pointHoverBorderWidth: 3
                    });
                    colorIndex++;
                });

                const allLabels = [...new Set(data.map(item => item.year))].sort();

                if (batchYearlyPaymentsChart) {
                    batchYearlyPaymentsChart.destroy();
                }

                batchYearlyPaymentsChart = new Chart(batchYearlyCtx, {
                    type: 'line',
                    data: {
                        labels: allLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { font: { size: window.innerWidth < 768 ? 10 : 12 } }
                            },
                            datalabels: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    font: { size: window.innerWidth < 768 ? 10 : 10 },
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                }
                            },
                            x: {
                                ticks: { font: { size: window.innerWidth < 768 ? 10 : 10 } }
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    plugins: [ChartDataLabels]
                });
            });


            // Update Target vs. Accomplishment Chart
            updateTargetVsAccomplishmentChart();
        }

        function updateTargetVsAccomplishmentChart() {
            // No year filter needed - show all data
            const params = {};

            // Fetch target vs accomplishment data
            fetchPayments('/finance/target-vs-accomplishment', params).then(data => {
                console.log('Target vs accomplishment data:', data);
                const targetChartNoData = document.getElementById('targetChartNoData');
                const targetChart = document.getElementById('targetVsAccomplishmentChart');

                if (!data || data.length === 0) {
                    targetChartNoData.style.display = 'block';
                    targetChart.style.display = 'none';
                    return;
                }

                targetChartNoData.style.display = 'none';
                targetChart.style.display = 'block';

                // Process data for the chart
                const monthlyData = {};
                const batchData = {};

                data.forEach(item => {
                    const monthKey = `${item.year}-${String(item.month).padStart(2, '0')}`;
                    const monthLabel = `${item.year}-${String(item.month).padStart(2, '0')}`;
                    
                    // Aggregate monthly targets
                    if (!monthlyData[monthKey]) {
                        monthlyData[monthKey] = {
                            label: monthLabel,
                            target: 0,
                            collections: [],
                            month: item.month,
                            year: item.year
                        };
                    }
                    monthlyData[monthKey].target += parseFloat(item.target_amount || 0);
                    
                    // Store batch collections
                    if (!batchData[monthKey]) {
                        batchData[monthKey] = [];
                    }
                    if (item.actual_collection > 0) {
                        batchData[monthKey].push({
                            batch: item.batch_year,
                            amount: parseFloat(item.actual_collection)
                        });
                    }
                });

                // Create continuous month labels (ensure all months are shown)
                const allMonths = Object.keys(monthlyData).sort();
                const monthLabels = allMonths.map(key => {
                    const monthData = monthlyData[key];
                    
                    let month, year;
                    
                    // Try to get month and year from monthData properties
                    if (monthData && monthData.month && monthData.year) {
                        month = monthData.month;
                        year = monthData.year;
                    } else {
                        // Fallback: parse from key format (YYYY-MM)
                        const keyParts = key.split('-');
                        if (keyParts.length === 2) {
                            year = parseInt(keyParts[0]);
                            month = parseInt(keyParts[1]);
                        } else {
                            return key; // Ultimate fallback to original key
                        }
                    }
                    
                    const monthNames = [
                        'JAN', 'FEB', 'MAR', 'APR', 'MAY', 'JUN',
                        'JUL', 'AUG', 'SEP', 'OCT', 'NOV', 'DEC'
                    ];
                    
                    return `${monthNames[month - 1]} ${year}`;
                });

                // Create datasets
                const datasets = [];

                // 1. Target Bars (Blue bars)
                const targetLineData = allMonths.map(key => monthlyData[key].target);
                datasets.push({
                    label: 'Monthly Collection Target',
                    data: targetLineData,
                    backgroundColor: 'rgba(54, 162, 235, 0.8)', // Blue bars
                    borderColor: '#007bff',
                    borderWidth: 1,
                    order: 1,
                    type: 'bar',
                    barPercentage: 0.8,
                    categoryPercentage: 0.9
                });

                // 2. Actual Collections (Dark gray bars)
                const batchCollectionsData = allMonths.map(key => {
                    const collections = batchData[key] || [];
                    return collections.reduce((sum, batch) => sum + batch.amount, 0);
                });

                datasets.push({
                    label: 'Actual Collections',
                    data: batchCollectionsData,
                    backgroundColor: 'rgba(75, 75, 75, 0.8)', // Dark gray bars  
                    borderColor: '#4b4b4b',
                    borderWidth: 1,
                    order: 2,
                    type: 'bar',
                    barPercentage: 0.8,
                    categoryPercentage: 0.9
                });

                // Destroy existing chart if it exists
                if (targetVsAccomplishmentChart) {
                    targetVsAccomplishmentChart.destroy();
                }

                // Create new chart
                targetVsAccomplishmentChart = new Chart(targetVsAccomplishmentCtx, {
                    type: 'bar',
                    data: {
                        labels: monthLabels,
                        datasets: datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: { 
                                    font: { size: window.innerWidth < 768 ? 10 : 12 },
                                    usePointStyle: true
                                }
                            },
                            tooltip: {
                                mode: 'index',
                                intersect: false,
                                callbacks: {
                                    title: function(context) {
                                        return monthLabels[context[0].dataIndex];
                                    },
                                    label: function(context) {
                                        const label = context.dataset.label;
                                        const value = context.parsed.y;
                                        return `${label}: ₱${value.toLocaleString()}`;
                                    },
                                    afterBody: function(context) {
                                        const monthIndex = context[0].dataIndex;
                                        const monthKey = allMonths[monthIndex];
                                        const monthData = monthlyData[monthKey];
                                        const batchCollections = batchData[monthKey] || [];
                                        
                                        if (monthData && monthData.target > 0) {
                                            const totalCollected = batchCollections.reduce((sum, batch) => sum + batch.amount, 0);
                                            const shortfall = monthData.target - totalCollected;
                                            const percentage = (totalCollected / monthData.target * 100).toFixed(1);
                                            
                                            let shortfallText = '';
                                            if (shortfall > 0) {
                                                shortfallText = `\nShortfall: ₱${shortfall.toLocaleString()} (${(100 - parseFloat(percentage)).toFixed(1)}%)`;
                                            } else if (shortfall < 0) {
                                                shortfallText = `\nExcess: ₱${Math.abs(shortfall).toLocaleString()} (${(parseFloat(percentage) - 100).toFixed(1)}%)`;
                                            } else {
                                                shortfallText = '\nTarget met exactly!';
                                            }
                                            
                                            return [
                                                `Performance: ${percentage}% of target`,
                                                shortfallText
                                            ];
                                        }
                                        return [];
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                beginAtZero: true,
                                ticks: {
                                    font: { size: window.innerWidth < 768 ? 10 : 10 },
                                    callback: function(value) {
                                        return '₱' + value.toLocaleString();
                                    }
                                },
                                title: {
                                    display: true,
                                    text: 'Amount (₱)'
                                }
                            },
                            x: {
                                ticks: { 
                                    font: { size: window.innerWidth < 768 ? 9 : 10 },
                                    maxRotation: 45,
                                    minRotation: 45
                                },
                                title: {
                                    display: true,
                                    text: 'Months (2024-2025)'
                                }   
                            }
                        },
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        }
                    }
                });
            });
        }

        function exportToCSV() {
            // Export overall batch monthly payments data
            const params = {};

            fetchPayments('/finance/batch-monthly-payments', params).then(data => {
                if (data.length === 0) {
                    alert('No data available to export.');
                    return;
                }
                const csvRows = ['Batch Year,Year,Month,Total Payments (₱)'];
                data.forEach(item => {
                    csvRows.push(`${item.batch_year},${item.year},${item.month},${item.total}`);
                });
                const csvContent = csvRows.join('\n');
                const blob = new Blob([csvContent], { type: 'text/csv' });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.setAttribute('href', url);
                a.setAttribute('download', 'batch_monthly_payments.csv');
                a.click();
                window.URL.revokeObjectURL(url);
            });
        }
        function fetchSummaryData() {
        fetch('/finance/summary-data')
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                // Update the dashboard values
                document.getElementById('monthlyCollected').textContent = parseFloat(data.monthly_collected).toLocaleString('en-US', { minimumFractionDigits: 2 });
                document.getElementById('yearlyCollected').textContent = parseFloat(data.yearly_collected).toLocaleString('en-US', { minimumFractionDigits: 2 });
                document.getElementById('overallCollected').textContent = parseFloat(data.overall_collected).toLocaleString('en-US', { minimumFractionDigits: 2 });
            })
            .catch(error => {
                console.error('Error fetching summary data:', error);
            });
        }

        function populateYearFilter() {
            // Fetch available years for the target chart
            fetchPayments('/finance/available-years', {}).then(data => {
                const yearSelect = document.getElementById('targetChartYear');
                yearSelect.innerHTML = '<option value="">All Years</option>';
                
                if (data && data.length > 0) {
                    data.forEach(year => {
                        const option = document.createElement('option');
                        option.value = year;
                        option.textContent = year;
                        yearSelect.appendChild(option);
                    });
                }
            }).catch(error => {
                console.error('Error fetching available years:', error);
            });
        }
        fetchSummaryData();
        populateYearFilter(); // Call populateYearFilter after fetching summary data

        // Attach event listeners
        const exportBtn = document.querySelector('.export-csv-btn');
        if (exportBtn) {
            exportBtn.addEventListener('click', exportToCSV);
        }

        // Initialize charts on page load
        updateCharts();

        // Update charts on window resize for font size adjustments
        window.addEventListener('resize', updateCharts);
    });
</script>
@endsection
