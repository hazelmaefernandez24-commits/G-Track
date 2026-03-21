@extends('layouts.app')

@section('content')
<div class="container-fluid p-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white" style="background: linear-gradient(135deg, #22bbea, #1a9bd1);">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h4 class="mb-0"><i class="bi bi-clock-history"></i> Inventory History</h4>
                            <small>{{ $item->name }}</small>
                        </div>
                        <a href="{{ route((auth()->user()->role === 'cook' ? 'cook' : 'kitchen') . '.inventory-management.index') }}" class="btn btn-light btn-sm">
                            <i class="bi bi-arrow-left"></i> Back to Inventory
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Details Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <h6 class="text-muted">Item Name</h6>
                            <p class="mb-0"><strong>{{ $item->name }}</strong></p>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted">Item Type</h6>
                            <p class="mb-0">{{ $item->item_type ?? $item->category }}</p>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted">Current Quantity</h6>
                            <p class="mb-0"><strong>{{ number_format($item->quantity, 2) }} {{ $item->unit }}</strong></p>
                        </div>
                        <div class="col-md-2">
                            <h6 class="text-muted">Reorder Point</h6>
                            <p class="mb-0">{{ number_format($item->reorder_point, 2) }} {{ $item->unit }}</p>
                        </div>
                        <div class="col-md-3">
                            <h6 class="text-muted">Status</h6>
                            <p class="mb-0">
                                @if($item->quantity <= 0)
                                    <span class="badge bg-danger">Out of Stock</span>
                                @elseif($item->quantity <= $item->reorder_point)
                                    <span class="badge bg-warning">Low Stock</span>
                                @else
                                    <span class="badge bg-success">Available</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- History Table -->
    <div class="card shadow">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Transaction History</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Date & Time</th>
                            <th>Action Type</th>
                            <th>Quantity Change</th>
                            <th>Previous Qty</th>
                            <th>New Qty</th>
                            <th>Updated By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($history as $record)
                        <tr>
                            <td>
                                <small>{{ $record->created_at->format('M d, Y') }}</small><br>
                                <small class="text-muted">{{ $record->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                @php
                                    $actionBadge = [
                                        'created' => 'bg-info',
                                        'purchase_delivery' => 'bg-success',
                                        'weekly_menu_creation' => 'bg-warning',
                                        'weekly_menu_update' => 'bg-warning',
                                        'weekly_menu_update_restore' => 'bg-info',
                                        'weekly_menu_deletion' => 'bg-secondary',
                                        'manual_adjustment' => 'bg-primary',
                                        'meal_preparation' => 'bg-warning'
                                    ];
                                    $badge = $actionBadge[$record->action_type] ?? 'bg-secondary';
                                @endphp
                                <span class="badge {{ $badge }}">{{ ucwords(str_replace('_', ' ', $record->action_type)) }}</span>
                            </td>
                            <td>
                                @if($record->quantity_change > 0)
                                    <span class="text-success">
                                        <i class="bi bi-arrow-up"></i> +{{ number_format($record->quantity_change, 2) }}
                                    </span>
                                @elseif($record->quantity_change < 0)
                                    <span class="text-danger">
                                        <i class="bi bi-arrow-down"></i> {{ number_format($record->quantity_change, 2) }}
                                    </span>
                                @else
                                    <span class="text-muted">0</span>
                                @endif
                            </td>
                            <td>{{ number_format($record->previous_quantity, 2) }}</td>
                            <td>{{ number_format($record->new_quantity, 2) }}</td>
                            <td>
                                @if($record->user)
                                    {{ $record->user->name ?? $record->user->user_email }}
                                @else
                                    <span class="text-muted">System</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $record->notes ?? '-' }}</small>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2">No history records found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-3">
                {{ $history->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
