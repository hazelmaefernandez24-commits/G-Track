@extends('layouts.admin_layout')

@section('content')
<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>Admin Dashboard</h1>
        <p class="text-muted">Overview of system statistics</p>
    </div>

    <!-- Stats Cards -->
    <div class="stats-grid">
        @foreach($rolesCount as $role => $count)
            <div class="stat-card">
                <div class="stat-icon">
                    @switch($role)
                        @case('admin')
                            <i class="fas fa-user-shield"></i>
                            @break
                        @case('educator')
                            <i class="fas fa-chalkboard-teacher"></i>
                            @break
                        @case('student')
                            <i class="fas fa-user-graduate"></i>
                            @break
                        @default
                            <i class="fas fa-users"></i>
                    @endswitch
                </div>
                <div class="stat-info">
                    <h3>{{ $count }}</h3>
                    <p>{{ ucfirst($role) }} Users</p>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Chart Section -->
    <div class="chart-container">
        <div class="chart-card">
            <h2>User Distribution</h2>
            <div class="chart-wrapper">
                <canvas id="userDistributionChart"></canvas>
            </div>
        </div>
    </div>
</div>

<style>
.admin-dashboard {
    padding: 20px;
    max-width: 1400px;
    margin: 0 auto;
}

.dashboard-header {
    margin-bottom: 30px;
}

.dashboard-header h1 {
    color: var(--text-color);
    margin-bottom: 5px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

.stat-icon {
    font-size: 2rem;
    color: var(--primary-color);
    margin-right: 15px;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: rgba(34, 187, 234, 0.1);
    display: flex;
    align-items: center;
    justify-content: center;
}

.stat-info h3 {
    font-size: 1.8rem;
    margin: 0;
    color: var(--text-color);
}

.stat-info p {
    margin: 5px 0 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.chart-container {
    background: white;
    border-radius: 10px;
    padding: 25px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    margin-bottom: 30px;
}

.chart-card h2 {
    color: var(--text-color);
    margin-top: 0;
    margin-bottom: 20px;
    font-size: 1.5rem;
}

.chart-wrapper {
    position: relative;
    height: 400px;
    width: 100%;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .chart-wrapper {
        height: 300px;
    }
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('userDistributionChart').getContext('2d');
    const dataKeys = @json(array_keys($rolesCount));
    const dataValues = @json(array_values($rolesCount));

    // Generate colors based on the number of roles
    const backgroundColors = [
        'rgba(54, 162, 235, 0.7)',
        'rgba(255, 99, 132, 0.7)',
        'rgba(75, 192, 192, 0.7)',
        'rgba(255, 159, 64, 0.7)',
        'rgba(153, 102, 255, 0.7)'
    ];

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: dataKeys,
            datasets: [{
                data: dataValues,
                backgroundColor: backgroundColors.slice(0, dataKeys.length),
                borderWidth: 1,
                borderColor: '#fff',
                hoverOffset: 15
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        padding: 20,
                        usePointStyle: true,
                        pointStyle: 'circle'
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0, 0, 0, 0.8)',
                    titleFont: { size: 14 },
                    bodyFont: { size: 14 },
                    padding: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = Math.round((value / total) * 100);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            },
            cutout: '60%',
            borderRadius: 10,
            spacing: 5
        }
    });
});
</script>
@endsection
