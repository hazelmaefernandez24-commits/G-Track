@extends('layouts.student')

@section('title', 'Notifications')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Notifications</h4>
        <button id="pageMarkAllRead" class="btn btn-sm btn-primary">Mark all as read</button>
    </div>

    @if($notifications->count() === 0)
        <div class="text-center text-muted py-5">
            <i class="fas fa-bell-slash fa-2x mb-2"></i>
            <div>No notifications</div>
        </div>
    @else
        <div class="list-group">
            @foreach($notifications as $n)
                <div class="list-group-item d-flex align-items-start {{ !$n->is_read ? 'bg-light' : '' }}" data-id="{{ $n->id }}">
                    <div class="me-3">
                        <i class="{{ $n->type === 'danger' ? 'fas fa-exclamation-triangle text-danger' : ($n->type === 'warning' ? 'fas fa-exclamation-circle text-warning' : ($n->type === 'success' ? 'fas fa-check-circle text-success' : 'fas fa-info-circle text-info')) }}"></i>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-semibold">{{ $n->title }}</div>
                        <div class="text-muted small">{{ $n->message }}</div>
                        <div class="text-muted small">{{ $n->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    @if(!$n->is_read)
                        <button class="btn btn-link btn-sm mark-read">Mark read</button>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-3">
            {{ $notifications->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Mark one notification as read
        document.querySelectorAll('.list-group-item .mark-read').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const row = this.closest('.list-group-item');
                const id = row.dataset.id;
                fetch(`{{ route('student.notifications.mark-read', ':id') }}`.replace(':id', id), {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        row.classList.remove('bg-light');
                        this.remove();
                        // update topbar badge if present
                        updateTopBadge();
                    }
                });
            });
        });

        // Mark all as read
        const pageMarkAll = document.getElementById('pageMarkAllRead');
        if (pageMarkAll) {
            pageMarkAll.addEventListener('click', function() {
                fetch(`{{ route('student.notifications.mark-all-read') }}`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
                }).then(r => r.json()).then(d => {
                    if (d.success) {
                        document.querySelectorAll('.list-group-item.bg-light').forEach(el => el.classList.remove('bg-light'));
                        document.querySelectorAll('.list-group-item .mark-read').forEach(btn => btn.remove());
                        updateTopBadge();
                    }
                });
            })
        }

        function updateTopBadge() {
            fetch('{{ route('student.notifications.unread-count') }}')
                .then(r => r.json())
                .then(d => {
                    const badge = document.getElementById('studentNotificationBadge');
                    if (!badge) return;
                    const c = d.success ? d.count : 0;
                    badge.textContent = c;
                    badge.style.display = c > 0 ? 'flex' : 'none';
                });
        }
    });
</script>
@endpush
