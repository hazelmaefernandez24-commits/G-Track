<div class="card main-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="15%">Day</th>
                        <th width="28%">Breakfast</th>
                        <th width="28%">Lunch</th>
                        <th width="28%">Dinner</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day)
                    @php
                        $isToday = (isset($today) && $today === $day && $currentWeek == $weekCycle);
                        $rowClass = $isToday ? 'table-warning' : '';
                    @endphp
                    <tr class="{{ $rowClass }}">
                        <td class="fw-bold text-capitalize">
                            {{ ucfirst($day) }}
                            @if($isToday)
                                <span class="badge bg-primary ms-2">Today</span>
                            @endif
                        </td>
                        
                        @foreach(['breakfast', 'lunch', 'dinner'] as $mealType)
                        <td>
                            @if(isset($dishes[$day][$mealType]) && $dishes[$day][$mealType])
                                @php $dish = $dishes[$day][$mealType]; @endphp
                                <div class="meal-item">
                                    <div class="fw-bold meal-name">{{ $dish->dish_name }}</div>
                                    @if($dish->description)
                                        <small class="text-muted d-block mt-1">{{ $dish->description }}</small>
                                    @endif
                                </div>
                            @else
                                <div class="text-muted small">
                                    <i class="bi bi-x-circle"></i> No meal planned
                                </div>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
