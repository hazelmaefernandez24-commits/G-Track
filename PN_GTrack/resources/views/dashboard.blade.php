@extends('layouts.app')

@section('title', 'Overview')
@section('subtitle', 'System status and analytics overview')

@push('styles')
<style>
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        box-shadow: var(--card-shadow);
    }

    .stat-content h3 {
        font-size: 14px;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 8px;
    }

    .stat-value {
        font-size: 32px;
        font-weight: 800;
        margin-bottom: 4px;
    }

    .stat-subtitle {
        font-size: 13px;
        color: var(--text-muted);
    }

    .stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .online-icon { color: var(--online); background: #DCFCE7; }
    .offline-icon { color: var(--offline); background: #FEE2E2; }
    .update-icon { color: var(--text-muted); background: #F1F5F9; }

    /* System Overview Cards */
    .system-overview {
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        padding: 24px;
        box-shadow: var(--card-shadow);
    }

    .system-header {
        margin-bottom: 24px;
    }

    .system-header h2 {
        font-size: 18px;
        font-weight: 700;
        color: var(--text-main);
    }

    .system-header p {
        font-size: 14px;
        color: var(--text-muted);
        margin-top: 4px;
    }

    .quick-access-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .qa-card {
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        display: flex;
        gap: 16px;
        text-decoration: none;
        transition: all 0.2s;
    }

    .qa-card:hover {
        border-color: var(--sidebar-active);
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        transform: translateY(-2px);
    }

    .qa-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .qa-blue { background: #EFF6FF; color: var(--sidebar-active); }
    .qa-green { background: #DCFCE7; color: var(--online); }
    .qa-purple { background: #F3E8FF; color: #9333EA; }
    .qa-orange { background: #FFEDD5; color: #EA580C; }

    .qa-content h3 {
        font-size: 16px;
        font-weight: 600;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .qa-content p {
        font-size: 13px;
        color: var(--text-muted);
        line-height: 1.4;
    }
</style>
@endpush

@section('content')

<!-- Top Stats -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-content">
            <h3>Online Students</h3>
            <div class="stat-value" style="color: var(--online);" id="online-count">{{ $onlineCount ?? 0 }}</div>
            <div class="stat-subtitle">Currently online</div>
        </div>
        <div class="stat-icon online-icon">
            <i data-lucide="user-check"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <h3>Offline Students</h3>
            <div class="stat-value" style="color: var(--offline);" id="offline-count">{{ $offlineCount ?? 0 }}</div>
            <div class="stat-subtitle">Currently offline</div>
        </div>
        <div class="stat-icon offline-icon">
            <i data-lucide="user-minus"></i>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-content">
            <h3>Latest Update</h3>
            <div class="stat-value" style="font-size: 24px; margin-top: 6px;" id="latest-time">{{ $latestTime ?? '--:--' }}</div>
            <div class="stat-subtitle" id="latest-date">{{ $latestDate ?? '' }}</div>
        </div>
        <div class="stat-icon update-icon">
            <i data-lucide="clock"></i>
        </div>
    </div>
</div>

<!-- System Overview -->
<div class="system-overview">
    <div class="system-header">
        <h2>System Overview</h2>
        <p>Quick access to key features</p>
    </div>

    <div class="quick-access-grid">
        <a href="/tracking" class="qa-card">
            <div class="qa-icon qa-blue">
                <i data-lucide="map-pin"></i>
            </div>
            <div class="qa-content">
                <h3>Real-Time Tracking</h3>
                <p>Monitor student locations on interactive map</p>
            </div>
        </a>

        <a href="/activity" class="qa-card">
            <div class="qa-icon qa-green">
                <i data-lucide="grid"></i>
            </div>
            <div class="qa-content">
                <h3>Student Activity</h3>
                <p>View detailed student status and information</p>
            </div>
        </a>

        <a href="/notifications" class="qa-card">
            <div class="qa-icon qa-purple">
                <i data-lucide="bell"></i>
            </div>
            <div class="qa-content">
                <h3>Notifications</h3>
                <p>Send announcements and emergency alerts</p>
            </div>
        </a>

        <a href="/notifications?tab=messages" class="qa-card">
            <div class="qa-icon qa-orange">
                <i data-lucide="message-square"></i>
            </div>
            <div class="qa-content">
                <h3>Messages Dashboard</h3>
                <p>View all student messages and communications</p>
            </div>
        </a>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Poll just for the dashboard stats
    function updateDashboardStats() {
        fetch('/api/dashboard/stats')
            .then(res => res.json())
            .then(data => {
                const onlineEl  = document.getElementById('online-count');
                const offlineEl = document.getElementById('offline-count');
                const timeEl    = document.getElementById('latest-time');
                const dateEl    = document.getElementById('latest-date');
                
                if (onlineEl)  onlineEl.textContent  = data.onlineCount;
                if (offlineEl) offlineEl.textContent = data.offlineCount;
                if (timeEl)    timeEl.textContent    = data.latestTime ?? '--:--';
                if (dateEl)    dateEl.textContent    = data.latestDate ?? '';
            })
            .catch(err => console.error('Dashboard stats update failed:', err));
    }

    setInterval(updateDashboardStats, 10000);
</script>
@endpush