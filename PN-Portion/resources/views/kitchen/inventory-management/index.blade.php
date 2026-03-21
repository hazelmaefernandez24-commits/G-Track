@extends('layouts.app')

@push('styles')
<style>
    /* Remove gray backdrop - make it transparent or remove completely */
    .modal-backdrop {
        z-index: 1040 !important;
        pointer-events: none !important;
        opacity: 0 !important; /* Make backdrop invisible */
    }
    
    .modal {
        z-index: 1050 !important;
        pointer-events: none !important; /* Modal container doesn't capture clicks */
    }
    
    .modal-dialog {
        z-index: 1051 !important;
        position: relative;
        pointer-events: none !important; /* Dialog doesn't capture clicks */
    }
    
    .modal-content {
        z-index: 1052 !important;
        position: relative;
        pointer-events: auto !important; /* Only content captures clicks */
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3) !important; /* Add shadow for visibility */
        border: 2px solid #22bbea !important; /* Add border to make it stand out */
    }
    
    /* Ensure all modal content elements are clickable */
    .modal-content * {
        pointer-events: auto !important;
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
                     style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div>
                        <h4 class="mb-0"><i class="bi bi-box-seam"></i> Inventory Management</h4>
                        <small>Maintain and update inventory items</small>
                    </div>
                    <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addItemModal">
                        <i class="bi bi-plus-circle"></i> Add New Item
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Items</h6>
                            <h3 class="mb-0">{{ $stats['total_items'] }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-box-seam" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Low Stock Items</h6>
                            <h3 class="mb-0 text-warning">{{ $stats['low_stock_items'] }}</h3>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-exclamation-triangle" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Out of Stock</h6>
                            <h3 class="mb-0 text-danger">{{ $stats['out_of_stock_items'] }}</h3>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-x-circle" style="font-size: 2rem;"></i>
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

    <!-- Inventory Items Table -->
    <div class="card shadow">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Inventory Items</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Item Name</th>
                            <th>Quantity</th>
                            <th>Unit</th>
                            <th>Reorder Point</th>
                            <th>Status</th>
                            <th>Last Updated</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($inventoryItems as $item)
                        <tr>
                            <td>
                                <strong>{{ $item->name }}</strong>
                                @if($item->description)
                                    <br><small class="text-muted">{{ Str::limit($item->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ number_format($item->quantity, 2) }}</strong>
                            </td>
                            <td>{{ $item->unit }}</td>
                            <td>{{ number_format($item->reorder_point, 2) }}</td>
                            <td>
                                @if($item->quantity <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($item->quantity <= $item->reorder_point)
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success">Available</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $item->updated_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-primary" 
                                            onclick="editItem({{ $item->id }}, '{{ $item->name }}', {{ $item->quantity }}, '{{ $item->unit }}', '{{ addslashes($item->description ?? '') }}', {{ $item->reorder_point }})">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <a href="{{ route((auth()->user()->role === 'cook' ? 'cook' : 'kitchen') . '.inventory-management.history', $item->id) }}" 
                                       class="btn btn-outline-info" title="View History">
                                        <i class="bi bi-clock-history"></i>
                                    </a>
                                    <button type="button" class="btn btn-outline-danger" 
                                            onclick="deleteItem({{ $item->id }}, '{{ $item->name }}')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                <p class="mt-2">No inventory items found. Click "Add New Item" to get started.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $inventoryItems->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Add Item Modal -->
<div class="modal fade" id="addItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Add New Inventory Item</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('kitchen.inventory-management.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Item Name</strong> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Quantity</strong> <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Unit</strong> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="unit" placeholder="e.g., kg, pieces" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reorder Point</label>
                        <input type="number" class="form-control" name="reorder_point" min="0" step="0.01" value="10">
                        <small class="text-muted">Alert when quantity falls below this level</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-save"></i> Add Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Edit Inventory Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editItemForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><strong>Item Name</strong> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="edit_name" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Quantity</strong> <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="quantity" id="edit_quantity" min="0" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label"><strong>Unit</strong> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="unit" id="edit_unit" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reorder Point</label>
                        <input type="number" class="form-control" name="reorder_point" id="edit_reorder_point" min="0" step="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" id="edit_description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">
                        <i class="bi bi-save"></i> Update Item
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function editItem(id, name, quantity, unit, description, reorderPoint) {
    document.getElementById('editItemForm').action = `/kitchen/inventory-management/${id}`;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_quantity').value = quantity;
    document.getElementById('edit_unit').value = unit;
    document.getElementById('edit_description').value = description;
    document.getElementById('edit_reorder_point').value = reorderPoint;
    
    new bootstrap.Modal(document.getElementById('editItemModal')).show();
}

function deleteItem(id, name) {
    if (!confirm(`Are you sure you want to delete "${name}"?`)) {
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `/kitchen/inventory-management/${id}`;
    form.innerHTML = `
        @csrf
        @method('DELETE')
    `;
    document.body.appendChild(form);
    form.submit();
}
</script>
@endpush
