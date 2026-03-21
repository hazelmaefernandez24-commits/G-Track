@extends('layouts.student')
@section('title', 'Student Notifications')
@section('page-title', 'Student Notifications')
@section('content')
<style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;700&display=swap');
    body, .card, .list-group-item, .btn, .modal-content {
        font-family: 'Poppins', Arial, sans-serif !important;
    }
    .clickable-text {
        text-decoration: underline;
        color: #0d6efd;
        cursor: pointer;
    }
    .clickable-text:hover {
        color: #0a58ca;
    }
    .delete-btn {
        float: right;
        margin-top: -5px;
    }
    .btn-orange {
        background-color: #ff9933;
        border-color: #ff9933;
        color: white;
    }
    .btn-orange:hover {
        background-color:  #ff9933;
        border-color:  #ff9933;
        color: white;
    }
    .font-weight-bold {
        font-weight: bold;
    }
    .btn-secondary.btn-sm:hover, .btn-secondary.btn-primary:hover {
        background-color: #0d6efd !important;
        border-color: #0d6efd !important;
        color: #fff !important;
    }
</style>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Notifications</div>
                <div class="card-body">
                    @if($notifications->isEmpty())
                        <p>No notifications available.</p>
                    @else
                        <ul class="list-group">
                            @foreach($notifications as $notification)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h5 class="font-weight-bold mb-1">{{ $notification->title }}</h5>
                                        </div>
                                        <form action="{{ route('student.notifications.delete', $notification) }}" method="POST" class="delete-btn" id="delete-form-{{ $notification->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-orange btn-sm" onclick="confirmDeleteNotification({{ $notification->id }})">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                    @if(in_array($notification->type, ['payment_receipt', 'payment_verification']) && isset($notification->receipt_path))
                                        <p>
                                            @php
                                                $message = $notification->message;
                                                $clickablePart = 'Click here to view your receipt';
                                                $message = str_replace($clickablePart, '<a href="#" class="clickable-text" data-bs-toggle="modal" data-bs-target="#receiptModal' . $notification->id . '">' . $clickablePart . '</a>', $message);
                                            @endphp
                                            {!! $message !!}
                                        </p>
                                        <a href="{{ url('storage/' . $notification->receipt_path) }}" 
                                           download 
                                           class="btn btn-secondary btn-sm">
                                           <i class="fas fa-download"></i> Download Receipt
                                        </a>
                                        <div class="mt-1 text-muted" style="font-size: 0.75em; font-weight: normal;">
                                            Received on {{ $notification->created_at->format('F j, Y') }}
                                        </div>
                                        <!-- Receipt Preview Modal -->
                                        <div class="modal fade" id="receiptModal{{ $notification->id }}" tabindex="-1" aria-labelledby="receiptModalLabel{{ $notification->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="receiptModalLabel{{ $notification->id }}">Payment Receipt</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <iframe src="{{ url('storage/' . $notification->receipt_path) }}" 
                                                                style="width: 100%; height: 500px;" 
                                                                frameborder="0">
                                                        </iframe>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        <a href="{{ url('storage/' . $notification->receipt_path) }}" 
                                                           download 
                                                           class="btn btn-primary">
                                                           <i class="fas fa-download"></i> Download Receipt
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <h6>{!! $notification->message !!}</h6>
                                        <div class="mt-1 text-muted" style="font-size: 0.65em;">
                                            Received on {{ $notification->created_at->format('F j, Y') }}
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmDeleteNotification(notificationId) {
    Swal.fire({
        title: 'Delete Notification?',
        text: 'This action cannot be undone. Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + notificationId).submit();
        }
    });
}
</script>
@endpush
@endsection