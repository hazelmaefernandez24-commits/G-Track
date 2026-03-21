<div class="row">
    <div class="col-md-12">
        <h5 class="mb-3">{{ $weeklyMenuDish->dish_name }}</h5>
        
        <div class="row mb-3">
            <div class="col-md-4">
                <strong>Day:</strong> {{ ucfirst($weeklyMenuDish->day_of_week) }}
            </div>
            <div class="col-md-4">
                <strong>Meal Type:</strong> {{ ucfirst($weeklyMenuDish->meal_type) }}
            </div>
            <div class="col-md-4">
                <strong>Week Cycle:</strong> Week {{ $weeklyMenuDish->week_cycle }}
            </div>
        </div>

        @if($weeklyMenuDish->description)
        <div class="mb-3">
            <strong>Description:</strong>
            <p>{{ $weeklyMenuDish->description }}</p>
        </div>
        @endif

        <div class="mb-3">
            <strong>Ingredients Used:</strong>
            <table class="table table-sm table-bordered mt-2">
                <thead class="table-light">
                    <tr>
                        <th>Ingredient Name</th>
                        <th>Item Type</th>
                        <th>Quantity Used</th>
                        <th>Current Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($weeklyMenuDish->ingredients as $ingredient)
                    <tr>
                        <td>{{ $ingredient->name }}</td>
                        <td>{{ $ingredient->item_type ?? $ingredient->category }}</td>
                        <td>{{ $ingredient->pivot->quantity_used }} {{ $ingredient->pivot->unit }}</td>
                        <td>
                            {{ $ingredient->quantity }} {{ $ingredient->unit }}
                            @if($ingredient->quantity < $ingredient->reorder_point)
                                <span class="badge bg-warning">Low Stock</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <strong>Created By:</strong> {{ $weeklyMenuDish->creator->name ?? 'Unknown' }}
            <br>
            <strong>Created At:</strong> {{ $weeklyMenuDish->created_at->format('M d, Y h:i A') }}
        </div>
    </div>
</div>
