@extends('layouts.nav')

@section('title', $title)

@section('styles')
<style>
    .calendar-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        margin: 20px;
        overflow: hidden;
    }

    .calendar-header {
        background: #f8f9fa;
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
    }

    .calendar-title {
        color: #2c3e50;
        font-size: 1.8rem;
        font-weight: 600;
        margin: 0;
    }

    .calendar-subtitle {
        color: #6c757d;
        font-size: 0.9rem;
        margin-top: 5px;
    }

    .calendar-content {
        padding: 20px;
    }

    .action-buttons {
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .btn {
        padding: 8px 16px;
        border-radius: 4px;
        text-decoration: none;
        font-weight: 500;
        border: none;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        font-size: 0.9rem;
    }

    .btn-primary {
        background: #007bff;
        color: white;
    }

    .btn-secondary {
        background: #6c757d;
        color: white;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .calendar-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.85rem;
        color: #495057;
    }

    .legend-color {
        width: 14px;
        height: 14px;
        border-radius: 3px;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .semester-filter {
        margin-bottom: 20px;
    }

    .semester-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }

    .semester-btn {
        padding: 8px 16px;
        border: 1px solid #007bff;
        background: white;
        color: #007bff;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.2s ease;
        font-weight: 500;
        font-size: 0.85rem;
    }

    .semester-btn.active,
    .semester-btn:hover {
        background: #007bff;
        color: white;
    }

    /* Table Styles */
    .table-responsive { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; }
    thead th {
        background: #f8f9fa;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 0.75rem;
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
        white-space: nowrap;
    }
    tbody td { padding: 12px; border-bottom: 1px solid #f1f3f5; font-size: 0.9rem; }
    tbody tr:hover { background: #fafbfc; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 12px; font-size: 0.75rem; font-weight: 600; }
    .badge-activity { background: #eaf4fe; color: #1e74c8; }
    .badge-holiday { background: #fde8e7; color: #c0392b; }
    .badge-examination { background: #fff4e0; color: #b46900; }
    .badge-deadline { background: #fff0e6; color: #bf6516; }
    .badge-vacation { background: #eaf7ef; color: #1e8e4a; }
    .badge-special { background: #f2e9f7; color: #7e3aa6; }

    .calendar-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
        margin-bottom: 25px;
    }

    .stat-card {
        background: linear-gradient(135deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.7) 100%);
        padding: 20px;
        border-radius: 15px;
        text-align: center;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.3);
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 5px;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    @media (max-width: 768px) {
        .calendar-wrapper {
            margin: 10px;
            border-radius: 15px;
        }

        .calendar-header {
            padding: 20px;
        }

        .calendar-title {
            font-size: 1.8rem;
        }

        .calendar-subtitle {
            font-size: 1rem;
        }

        .calendar-content {
            padding: 20px;
        }

        .action-buttons {
            flex-direction: column;
            align-items: center;
        }

        .btn {
            width: 100%;
            max-width: 250px;
            justify-content: center;
        }

        .semester-buttons {
            flex-wrap: wrap;
            justify-content: center;
        }

        .calendar-legend {
            grid-template-columns: 1fr;
        }

        .calendar-stats {
            grid-template-columns: repeat(2, 1fr);
        }

        .fc-toolbar {
            flex-direction: column !important;
            gap: 10px !important;
        }

        .fc-toolbar-chunk {
            display: flex !important;
            justify-content: center !important;
        }
    }

    /* Loading Animation */
    .calendar-loading {
        display: flex;
        justify-content: center;
        align-items: center;
        height: 200px;
        font-size: 1.1rem;
        color: #667eea;
    }

    .loading-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid rgba(102, 126, 234, 0.3);
        border-top: 4px solid #667eea;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-right: 15px;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<div class="container-fluid" style="padding: 20px;">
    <div class="calendar-container">
        <div class="calendar-header">
            <h1 class="calendar-title">Academic Calendar</h1>
            <p class="calendar-subtitle">{{ now()->format('Y') }} Calendar</p>
        </div>

        <div class="calendar-content">
            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('training.calendar.manage') }}" class="btn btn-secondary">
                    <i class="fas fa-list"></i> Manage Events
                </a>
                <a href="{{ route('training.calendar.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Add New Event
                </a>
            </div>

            <!-- Category Filter -->
            <div class="semester-filter">
                <div class="semester-buttons">
                    <button type="button" class="semester-btn active" data-category="all">
                        <i class="fas fa-calendar"></i> All
                    </button>
                    <button type="button" class="semester-btn" data-category="school_activity">
                        <i class="fas fa-graduation-cap"></i> Activities
                    </button>
                    <button type="button" class="semester-btn" data-category="holiday">
                        <i class="fas fa-heart"></i> Holidays
                    </button>
                    <button type="button" class="semester-btn" data-category="examination">
                        <i class="fas fa-pencil-alt"></i> Examination
                    </button>
                    <button type="button" class="semester-btn" data-category="deadline">
                        <i class="fas fa-clock"></i> Deadline
                    </button>
                    <button type="button" class="semester-btn" data-category="vacation">
                        <i class="fas fa-umbrella-beach"></i> Vacation
                    </button>
                    <button type="button" class="semester-btn" data-category="special">
                        <i class="fas fa-star"></i> Special Event
                    </button>
                </div>
            </div>

            <!-- Events Table -->
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Category</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody id="events-tbody">
                        @foreach($events as $event)
                            <tr data-category="{{ $event['category'] }}">
                                <td>{{ $event['title'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($event['start'])->format('M d, Y') }}</td>
                                <td>{{ $event['end'] ? \Carbon\Carbon::parse($event['end'])->format('M d, Y') : \Carbon\Carbon::parse($event['start'])->format('M d, Y') }}</td>
                                <td>
                                    @if($event['category'] === 'school_activity')
                                        <span class="badge badge-activity">Activity</span>
                                    @elseif($event['category'] === 'holiday')
                                        <span class="badge badge-holiday">Holiday</span>
                                    @elseif($event['category'] === 'examination')
                                        <span class="badge badge-examination">Examination</span>
                                    @elseif($event['category'] === 'deadline')
                                        <span class="badge badge-deadline">Deadline</span>
                                    @elseif($event['category'] === 'vacation')
                                        <span class="badge badge-vacation">Vacation</span>
                                    @elseif($event['category'] === 'special')
                                        <span class="badge badge-special">Special Event</span>
                                    @else
                                        <span class="badge">{{ ucfirst(str_replace('_',' ',$event['category'])) }}</span>
                                    @endif
                                </td>
                                <td>{{ $event['description'] ?? '-' }}</td>
                            </tr>
                        @endforeach

                        @php
                            $year = now()->year;
                            $fixedHolidays = [
                                [ 'title' => "New Year's Day", 'date' => sprintf('%s-01-01', $year) ],
                                [ 'title' => 'EDSA People Power Revolution Anniversary', 'date' => sprintf('%s-02-25', $year) ],
                                [ 'title' => 'Araw ng Kagitingan (Day of Valor)', 'date' => sprintf('%s-04-09', $year) ],
                                [ 'title' => 'Labor Day', 'date' => sprintf('%s-05-01', $year) ],
                                [ 'title' => 'Independence Day', 'date' => sprintf('%s-06-12', $year) ],
                                [ 'title' => 'Ninoy Aquino Day', 'date' => sprintf('%s-08-21', $year) ],
                                [ 'title' => "All Saints' Day", 'date' => sprintf('%s-11-01', $year) ],
                                [ 'title' => 'All Souls Day', 'date' => sprintf('%s-11-02', $year) ],
                                [ 'title' => 'Bonifacio Day', 'date' => sprintf('%s-11-30', $year) ],
                                [ 'title' => 'Christmas Eve (Special Non-Working)', 'date' => sprintf('%s-12-24', $year) ],
                                [ 'title' => 'Christmas Day', 'date' => sprintf('%s-12-25', $year) ],
                                [ 'title' => 'Rizal Day', 'date' => sprintf('%s-12-30', $year) ],
                                [ 'title' => "New Year's Eve (Special Non-Working)", 'date' => sprintf('%s-12-31', $year) ],
                            ];
                        @endphp

                        @foreach($fixedHolidays as $h)
                            <tr data-category="holiday">
                                <td>{{ $h['title'] }}</td>
                                <td>{{ \Carbon\Carbon::parse($h['date'])->format('M d, Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($h['date'])->format('M d, Y') }}</td>
                                <td><span class="badge badge-holiday">Holiday</span></td>
                                <td>Fixed Philippine holiday</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = Array.from(document.querySelectorAll('#events-tbody tr'));
    function normalize(val){ return (val || '').toString().trim().toLowerCase(); }
    rows.forEach(row => {
        row.setAttribute('data-category', normalize(row.getAttribute('data-category')));
    });
    document.querySelectorAll('.semester-btn').forEach(btn => {
        btn.addEventListener('click', function(e){
            e.preventDefault();
            document.querySelectorAll('.semester-btn').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const category = normalize(this.dataset.category);
            rows.forEach(row => {
                const rc = normalize(row.getAttribute('data-category'));
                row.style.display = (category === 'all' || rc === category) ? '' : 'none';
            });
        });
    });
});
</script>
@endsection
