@extends('layouts.finance')
@section('page-title', 'Payment Options')
@section('content')
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                    <h5 class="mb-0">Manage Payment Options</h5>
                    <a href="{{ route('payment-methods.create') }}" class="btn btn-light btn-sm">
                        <i class="fas fa-plus-circle"></i> Add Payment Option
                    </a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if($paymentMethods->isEmpty())
                        <p class="text-muted">No payment methods found. Click "Add Payment Method" to create one.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Name</th>
                                        <th>Account Name</th>
                                        <th>Account Number</th>
                                        <th>QR Code</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($paymentMethods as $method)
                                        <tr>
                                            <td>
                                                <strong>{{ $method->name }}</strong>
                                                @if($method->description)
                                                    <br><small class="text-muted">{{ $method->description }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $method->account_name ?: '-' }}</td>
                                            <td>{{ $method->account_number ?: '-' }}</td>
                                            <td>
                                                @if($method->qr_image)
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#qrModal{{ $method->id }}">
                                                        <i class="fas fa-qrcode"></i> View QR
                                                    </button>

                                                    <!-- QR Code Modal -->
                                                    <div class="modal fade" id="qrModal{{ $method->id }}" tabindex="-1">
                                                        <div class="modal-dialog modal-dialog-centered">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title">{{ $method->name }} QR Code</h5>
                                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                                </div>
                                                                <div class="modal-body text-center">
                                                                    <img src="{{ asset('storage/' . $method->qr_image) }}"
                                                                         alt="{{ $method->name }} QR Code"
                                                                         class="img-fluid"
                                                                         style="max-width: 300px;"
                                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                                                                    <div class="alert alert-warning" style="display: none;">
                                                                        <strong>Image not found!</strong><br>
                                                                        Path: {{ $method->qr_image }}<br>
                                                                        URL: {{ asset('storage/' . $method->qr_image) }}
                                                                    </div>
                                                                    <div class="mt-3">
                                                                        <p class="mb-1"><strong>{{ $method->name }}</strong></p>
                                                                        @if($method->account_name)
                                                                            <p class="mb-1">{{ $method->account_name }}</p>
                                                                        @endif
                                                                        @if($method->account_number)
                                                                            <p class="mb-1">{{ $method->account_number }}</p>
                                                                        @endif
                                                                        @if($method->description)
                                                                            <p class="text-muted">{{ $method->description }}</p>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @else
                                                    <span class="text-muted">No QR Code</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge {{ $method->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                    {{ $method->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('payment-methods.edit', $method) }}" class="btn btn-sm btn-outline-primary me-1">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form action="{{ route('payment-methods.destroy', $method) }}" method="POST" class="d-inline delete-form">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger delete-btn">
                                                        <i class="fas fa-trash-alt"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const deleteButtons = document.querySelectorAll('.delete-btn');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const form = this.closest('form');
                
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
        
        // Show success message with SweetAlert if exists in session
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '{{ session('success') }}',
                timer: 3000,
                showConfirmButton: false
            });
        @endif
    });
</script>
@endpush
@endsection
