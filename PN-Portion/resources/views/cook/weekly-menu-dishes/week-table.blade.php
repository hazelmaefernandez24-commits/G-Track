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
                                    <ul class="text-muted meal-ingredients mb-2" style="font-size: 0.85rem; line-height: 1.4; padding-left: 1.2rem;">
                                        @foreach($dish->ingredients as $ingredient)
                                            <li>{{ $ingredient->name }}: {{ $ingredient->pivot->quantity_used }} {{ $ingredient->pivot->unit }}</li>
                                        @endforeach
                                    </ul>
                                    <div class="mt-2">
                                        <button type="button" class="btn btn-outline-info btn-sm me-1" 
                                                onclick="viewDish({{ $dish->id }})" title="View Details">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary btn-sm me-1" 
                                                onclick="openEditDishModal({{ $dish->id }})" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-sm" 
                                                onclick="deleteDish({{ $dish->id }}, '{{ $dish->dish_name }}')" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            @else
                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                        onclick="openCreateDishModal({{ $weekCycle }}, '{{ $day }}', '{{ $mealType }}')">
                                    <i class="bi bi-plus-circle"></i> Add Dish
                                </button>
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
