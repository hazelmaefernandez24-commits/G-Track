@extends('layouts.app')

@push('styles')
<style>
    /* Match Daily & Weekly Menu styling */
    .meal-item {
        margin-bottom: 4px;
        line-height: 1.3;
    }

    .meal-name {
        margin-bottom: 2px;
        line-height: 1.2;
        color: #333;
    }

    .meal-ingredients {
        line-height: 1.1;
        margin-bottom: 0;
        display: block;
    }
    
    .table td {
        vertical-align: top;
        padding: 12px;
        line-height: 1.2;
    }
    
    .table-warning {
        background-color: #fff3cd !important;
    }

    .main-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        border: none;
    }

    .card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #ff9933;
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white d-flex justify-content-between align-items-center" 
                     style="background: linear-gradient(135deg, #28a745, #20c997);">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-calendar-week"></i> Weekly Menu</h4>
                        <small>View this week's delicious meals</small>
                    </div>
                    <div class="text-end">
                        <span id="currentDateTime" class="fs-6 text-white"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Menu Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #28a745 !important;">
                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-calendar-day text-primary"></i> 
                        Today's Menu 
                        <span class="badge bg-primary ms-2">{{ ucfirst($today) }}</span>
                        <span class="badge bg-info ms-1">Week {{ $currentWeek }}</span>
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Breakfast -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-warning text-center">
                                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-sunrise"></i> Breakfast</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if(isset($todaysDishes['breakfast']) && $todaysDishes['breakfast'])
                                        <div class="fw-bold mb-2 fs-5 text-dark">
                                            {{ $todaysDishes['breakfast']->dish_name }}
                                        </div>
                                        @if($todaysDishes['breakfast']->description)
                                            <p class="text-muted small mb-2">{{ $todaysDishes['breakfast']->description }}</p>
                                        @endif
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-x-circle fs-3"></i>
                                            <p class="mb-0">No meal planned</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Lunch -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-success text-center">
                                    <h6 class="mb-0 fw-bold text-white"><i class="bi bi-sun"></i> Lunch</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if(isset($todaysDishes['lunch']) && $todaysDishes['lunch'])
                                        <div class="fw-bold mb-2 fs-5 text-dark">
                                            {{ $todaysDishes['lunch']->dish_name }}
                                        </div>
                                        @if($todaysDishes['lunch']->description)
                                            <p class="text-muted small mb-2">{{ $todaysDishes['lunch']->description }}</p>
                                        @endif
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-x-circle fs-3"></i>
                                            <p class="mb-0">No meal planned</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Dinner -->
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 border-0 shadow-sm">
                                <div class="card-header bg-danger text-center">
                                    <h6 class="mb-0 fw-bold text-white"><i class="bi bi-moon-stars"></i> Dinner</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if(isset($todaysDishes['dinner']) && $todaysDishes['dinner'])
                                        <div class="fw-bold mb-2 fs-5 text-dark">
                                            {{ $todaysDishes['dinner']->dish_name }}
                                        </div>
                                        @if($todaysDishes['dinner']->description)
                                            <p class="text-muted small mb-2">{{ $todaysDishes['dinner']->description }}</p>
                                        @endif
                                    @else
                                        <div class="text-muted">
                                            <i class="bi bi-x-circle fs-3"></i>
                                            <p class="mb-0">No meal planned</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Menu Section -->
    <div class="row">
        <div class="col-12">
            <div class="card main-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title">Weekly Menu</h5>
                    <div class="d-flex align-items-center gap-3">
                        <span class="text-muted me-2">View Menu for:</span>
                        <select id="weekCycleSelect" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="1" {{ $currentWeek == 1 ? 'selected' : '' }}>Week 1 & 3</option>
                            <option value="2" {{ $currentWeek == 2 ? 'selected' : '' }}>Week 2 & 4</option>
                        </select>
                        <small class="text-info ms-2">
                            <i class="bi bi-calendar-check"></i> Current: Week <span id="currentWeekNumber">{{ $currentWeek }}</span>
                        </small>
                    </div>
                </div>
                <div class="card-body p-0" id="weeklyMenuContainer">
                    <!-- Week 1 Content (Default) -->
                    <div id="week1Content" style="display: {{ $currentWeek == 1 ? 'block' : 'none' }};">
                        @include('student.weekly-menu-dishes.week-table', ['weekCycle' => 1, 'dishes' => $week1Dishes])
                    </div>
                    
                    <!-- Week 2 Content -->
                    <div id="week2Content" style="display: {{ $currentWeek == 2 ? 'block' : 'none' }};">
                        @include('student.weekly-menu-dishes.week-table', ['weekCycle' => 2, 'dishes' => $week2Dishes])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Update current date/time
function updateDateTime() {
    const now = new Date();
    const options = { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    };
    const dateTimeString = now.toLocaleDateString('en-US', options);
    const element = document.getElementById('currentDateTime');
    if (element) {
        element.textContent = dateTimeString;
    }
}

// Update time every minute
updateDateTime();
setInterval(updateDateTime, 60000);

// Handle week cycle dropdown change
document.getElementById('weekCycleSelect').addEventListener('change', function() {
    const selectedWeek = this.value;
    
    // Hide all week contents
    document.getElementById('week1Content').style.display = 'none';
    document.getElementById('week2Content').style.display = 'none';
    
    // Show selected week content
    if (selectedWeek == '1') {
        document.getElementById('week1Content').style.display = 'block';
    } else {
        document.getElementById('week2Content').style.display = 'block';
    }
});
</script>
@endpush
