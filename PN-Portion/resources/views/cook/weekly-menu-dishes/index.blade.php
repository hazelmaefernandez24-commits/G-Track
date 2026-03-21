@extends('layouts.app')

@push('styles')
<style>
    /* Modal styling - fix backdrop and close button */
    .modal-backdrop {
        display: none !important; /* Remove dark backdrop completely */
    }
    
    .modal {
        background-color: rgba(0, 0, 0, 0.5) !important; /* Add semi-transparent background to modal itself */
        z-index: 1050 !important;
    }
    
    .modal-dialog {
        z-index: 1051 !important;
        position: relative;
    }
    
    .modal-content {
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5) !important;
        border: 2px solid #667eea !important;
        position: relative;
        z-index: 1052 !important;
    }
    
    .modal-header .btn-close {
        opacity: 1 !important;
        background-color: rgba(255, 255, 255, 0.2) !important;
        border-radius: 50%;
        padding: 0.5rem;
    }
    
    .modal-header .btn-close:hover {
        background-color: rgba(255, 255, 255, 0.4) !important;
    }
    
    .sidebar {
        z-index: 1000 !important;
    }

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
                     style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-calendar-week"></i> Weekly Menu Management</h4>
                        <small>Create dishes and track ingredient usage automatically</small>
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
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #667eea !important;">
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
                                        <div class="small text-muted">
                                            <strong>Ingredients:</strong>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($todaysDishes['breakfast']->ingredients as $ingredient)
                                                    <li>{{ $ingredient->name }}: {{ $ingredient->pivot->quantity_used }} {{ $ingredient->pivot->unit }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
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
                                        <div class="small text-muted">
                                            <strong>Ingredients:</strong>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($todaysDishes['lunch']->ingredients as $ingredient)
                                                    <li>{{ $ingredient->name }}: {{ $ingredient->pivot->quantity_used }} {{ $ingredient->pivot->unit }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
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
                                        <div class="small text-muted">
                                            <strong>Ingredients:</strong>
                                            <ul class="list-unstyled mb-0">
                                                @foreach($todaysDishes['dinner']->ingredients as $ingredient)
                                                    <li>{{ $ingredient->name }}: {{ $ingredient->pivot->quantity_used }} {{ $ingredient->pivot->unit }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
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

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

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
                        @include('cook.weekly-menu-dishes.week-table', ['weekCycle' => 1, 'dishes' => $week1Dishes])
                    </div>
                    
                    <!-- Week 2 Content -->
                    <div id="week2Content" style="display: {{ $currentWeek == 2 ? 'block' : 'none' }};">
                        @include('cook.weekly-menu-dishes.week-table', ['weekCycle' => 2, 'dishes' => $week2Dishes])
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create/Edit Dish Modal -->
<div class="modal fade" id="dishModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="dishModalTitle">Create Weekly Menu Dish</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="dishForm" method="POST" action="{{ route('cook.weekly-menu-dishes.store') }}">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <input type="hidden" name="dish_id" id="dishId">
                <input type="hidden" name="week_cycle" id="weekCycle">
                <input type="hidden" name="day_of_week" id="dayOfWeek">
                <input type="hidden" name="meal_type" id="mealType">

                <div class="modal-body">
                    <!-- Dish Name -->
                    <div class="mb-3">
                        <label class="form-label"><strong>Dish Name</strong> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="dish_name" id="dishName" required>
                    </div>

                    <!-- Description -->
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="dishDescription" rows="2"></textarea>
                    </div>

                    <!-- Ingredients Section -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <label class="form-label mb-0"><strong>Ingredients</strong> <span class="text-danger">*</span></label>
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addIngredientRow()">
                                <i class="bi bi-plus"></i> Add Ingredient
                            </button>
                        </div>
                        <div id="ingredientsContainer">
                            <!-- Ingredient rows will be added here -->
                        </div>
                    </div>

                    <!-- Inventory Check Button -->
                    <button type="button" class="btn btn-info btn-sm" onclick="checkInventoryAvailability()">
                        <i class="bi bi-check-circle"></i> Check Ingredient Availability
                    </button>
                    <div id="availabilityResults" class="mt-2"></div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Save Dish
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Dish Details Modal -->
<div class="modal fade" id="viewDishModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Dish Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewDishContent">
                <!-- Content will be loaded dynamically -->
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let inventoryItems = @json($inventoryItems);
let ingredientRowCount = 0;

// Add ingredient row (supports both object and individual parameters)
function addIngredientRow(inventoryIdOrData = null, name = null, quantityUsed = null, unit = null) {
    ingredientRowCount++;
    const container = document.getElementById('ingredientsContainer');
    const row = document.createElement('div');
    row.className = 'row mb-2 ingredient-row';
    row.id = `ingredient-row-${ingredientRowCount}`;
    
    // Handle both object and individual parameters
    let ingredientData = null;
    if (typeof inventoryIdOrData === 'object' && inventoryIdOrData !== null) {
        // Called with object (for backward compatibility)
        ingredientData = inventoryIdOrData;
    } else if (inventoryIdOrData !== null) {
        // Called with individual parameters (for editing)
        ingredientData = {
            inventory_id: inventoryIdOrData,
            name: name,
            quantity_used: quantityUsed,
            unit: unit
        };
    }
    
    row.innerHTML = `
        <div class="col-md-5">
            <select class="form-select" name="ingredients[${ingredientRowCount}][inventory_id]" required>
                <option value="">Select Ingredient</option>
                ${inventoryItems.map(item => `
                    <option value="${item.id}" data-unit="${item.unit}" data-quantity="${item.quantity}"
                            ${ingredientData && ingredientData.inventory_id == item.id ? 'selected' : ''}>
                        ${item.name} (Available: ${item.quantity} ${item.unit})
                    </option>
                `).join('')}
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" class="form-control" name="ingredients[${ingredientRowCount}][quantity_used]" 
                   placeholder="Quantity" min="0.01" step="0.01" 
                   value="${ingredientData ? ingredientData.quantity_used : ''}" required>
        </div>
        <div class="col-md-3">
            <input type="text" class="form-control" name="ingredients[${ingredientRowCount}][unit]" 
                   placeholder="Unit" value="${ingredientData ? ingredientData.unit : ''}" required>
        </div>
        <div class="col-md-1">
            <button type="button" class="btn btn-danger btn-sm" onclick="removeIngredientRow(${ingredientRowCount})">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    
    container.appendChild(row);
    
    // Auto-fill unit when ingredient is selected
    const select = row.querySelector('select');
    const unitInput = row.querySelector('input[name*="[unit]"]');
    select.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            unitInput.value = selectedOption.dataset.unit;
        }
    });
}

// Remove ingredient row
function removeIngredientRow(rowId) {
    const row = document.getElementById(`ingredient-row-${rowId}`);
    if (row) {
        row.remove();
    }
}

// Open create dish modal
function openCreateDishModal(weekCycle, day, mealType) {
    document.getElementById('dishModalTitle').textContent = 'Create Weekly Menu Dish';
    document.getElementById('dishForm').action = '{{ route("cook.weekly-menu-dishes.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('dishId').value = '';
    document.getElementById('weekCycle').value = weekCycle;
    document.getElementById('dayOfWeek').value = day;
    document.getElementById('mealType').value = mealType;
    document.getElementById('dishName').value = '';
    document.getElementById('dishDescription').value = '';
    document.getElementById('ingredientsContainer').innerHTML = '';
    document.getElementById('availabilityResults').innerHTML = '';
    
    // Add one ingredient row by default
    addIngredientRow();
    
    const modalElement = document.getElementById('dishModal');
    const modal = new bootstrap.Modal(modalElement);
    modal.show();
    
    // Force fix pointer events after modal is shown
    setTimeout(() => {
        forceModalClickable();
    }, 100);
}

// Force modal to be clickable
function forceModalClickable() {
    const modal = document.getElementById('dishModal');
    const backdrops = document.querySelectorAll('.modal-backdrop');
    
    if (modal) {
        // Modal container should not capture clicks
        modal.style.zIndex = '9999';
        modal.style.pointerEvents = 'none';
        
        // Only modal-content should capture clicks
        const modalContent = modal.querySelector('.modal-content');
        if (modalContent) {
            modalContent.style.pointerEvents = 'auto';
            modalContent.style.zIndex = '10000';
            
            // Enable all child elements of modal-content
            modalContent.querySelectorAll('*').forEach(el => {
                el.style.pointerEvents = 'auto';
            });
        }
    }
    
    // Backdrop should NOT capture clicks - this is critical!
    backdrops.forEach(backdrop => {
        backdrop.style.zIndex = '9998';
        backdrop.style.pointerEvents = 'none'; // KEY FIX!
    });
    
    console.log('Modal forced clickable - backdrop pointer-events set to none');
}

// Check inventory availability
function checkInventoryAvailability() {
    const form = document.getElementById('dishForm');
    const formData = new FormData(form);
    const ingredients = [];
    
    // Collect all ingredients
    document.querySelectorAll('.ingredient-row').forEach((row, index) => {
        const inventoryId = row.querySelector('select').value;
        const quantityUsed = row.querySelector('input[name*="[quantity_used]"]').value;
        const unit = row.querySelector('input[name*="[unit]"]').value;
        
        if (inventoryId && quantityUsed && unit) {
            ingredients.push({
                inventory_id: inventoryId,
                quantity_used: quantityUsed,
                unit: unit
            });
        }
    });
    
    if (ingredients.length === 0) {
        alert('Please add at least one ingredient');
        return;
    }
    
    // Send AJAX request
    fetch('{{ route("cook.api.check-ingredient-availability") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ ingredients: ingredients })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            displayAvailabilityResults(data.results);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to check availability');
    });
}

// Display availability results
function displayAvailabilityResults(results) {
    const container = document.getElementById('availabilityResults');
    let html = '<div class="alert alert-info"><strong>Inventory Check Results:</strong><ul class="mb-0 mt-2">';
    
    results.forEach(result => {
        const status = result.sufficient ? 
            '<span class="badge bg-success">✓ Available</span>' : 
            '<span class="badge bg-danger">✗ Insufficient</span>';
        html += `<li>${result.name}: ${status} (Available: ${result.available} ${result.unit}, Required: ${result.required} ${result.unit})</li>`;
    });
    
    html += '</ul></div>';
    container.innerHTML = html;
}

// View dish details
function viewDish(dishId) {
    fetch(`/cook/weekly-menu-dishes/${dishId}`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('viewDishContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('viewDishModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load dish details');
        });
}

// Edit dish (opens create modal with dish data)
function openEditDishModal(dishId) {
    // Fetch dish data with JSON header
    fetch(`/cook/weekly-menu-dishes/${dishId}`, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then(response => response.json())
        .then(data => {
            // Change modal title
            document.getElementById('dishModalTitle').textContent = 'Edit Weekly Menu Dish';
            
            // Set form to PUT method
            document.getElementById('formMethod').value = 'PUT';
            document.getElementById('dishForm').action = `/cook/weekly-menu-dishes/${dishId}`;
            
            // Fill in dish data
            document.getElementById('dishId').value = data.id;
            document.getElementById('weekCycle').value = data.week_cycle;
            document.getElementById('dayOfWeek').value = data.day_of_week;
            document.getElementById('mealType').value = data.meal_type;
            document.getElementById('dishName').value = data.dish_name;
            document.getElementById('dishDescription').value = data.description || '';
            
            // Clear and populate ingredients
            document.getElementById('ingredientsContainer').innerHTML = '';
            data.ingredients.forEach(ingredient => {
                addIngredientRow(ingredient.id, ingredient.name, ingredient.pivot.quantity_used, ingredient.pivot.unit);
            });
            
            // Show modal
            new bootstrap.Modal(document.getElementById('dishModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load dish details for editing');
        });
}

// Delete dish
function deleteDish(dishId, dishName) {
    if (!confirm(`Are you sure you want to delete "${dishName}"? The ingredients will be restored to inventory.`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/cook/weekly-menu-dishes/${dishId}`;
    form.innerHTML = `
        @csrf
        @method('DELETE')
    `;
    document.body.appendChild(form);
    form.submit();
}

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
