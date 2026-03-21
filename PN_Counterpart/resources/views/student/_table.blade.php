{{-- Mobile “card” view --}}
<div class="d-md-none">
    @forelse($payments as $p)
      <div class="payment-card {{ Str::lower($p->status) }}">
        <div class="d-flex justify-content-between">
          <div>
            <div class="fw-bold">{{ $p->payment_date }}</div>
            <div class="text-muted small">Ref: {{ $p->reference_number ?? '–' }}</div>
          </div>
          <div class="text-end">
            <div class="fw-semibold">₱{{ number_format($p->amount,2) }}</div>
            @php
              $statusLower = strtolower($p->status ?? '');
              $computedBadge = 'secondary';
              if (isset($badgeClass) && is_callable($badgeClass)) {
                  $computedBadge = $badgeClass($p->status);
              } elseif (isset($badgeClass) && is_string($badgeClass)) {
                  $computedBadge = $badgeClass;
              } else {
                  $computedBadge = match($statusLower) {
                      'approved' => 'success',
                      'added by finance' => 'info',
                      'declined', 'deleted' => 'danger',
                      'pending' => 'warning',
                      default => 'secondary',
                  };
              }
            @endphp
            <span class="badge bg-{{ $computedBadge }}">{{ $p->status }}</span>
          </div>
        </div>
      </div>
    @empty
      <div class="text-center text-muted">{{ $emptyMessage }}</div>
    @endforelse
  </div>
  
  {{-- Desktop “table” view --}}
  <div class="table-responsive d-none d-md-block">
    <table class="table table-hover mb-0"
           id="{{ $payments->first()?->status==='Declined' ? 'declined-table' : 'approved-table' }}">
      <thead class="table-light">
        <tr>
          <th>Date</th>
          <th>Amount</th>
          <th>Reference #</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($payments as $p)
          <tr>
            <td>
  {{ \Carbon\Carbon::parse($p->payment_date)->format('Y-m-d') }}
</td>
            <td>Php {{ number_format($p->amount,2) }}</td>
            <td>{{ $p->reference_number ?? '–' }}</td>
            <td>
              @php
                $status = strtolower($p->status);
                $boxClass = match($status) {
                    'approved' => 'status-box status-approved',
                    'added by finance' => 'status-box status-added',
                    'declined' => 'status-box status-declined',
                    'pending' => 'status-box status-pending',
                    'deleted' => 'status-box status-declined',
                    default => 'status-box status-default'
                };
              @endphp
              <span class="{{ $boxClass }}">{{ ucfirst($p->status) }}</span>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="4" class="text-center text-muted">{{ $emptyMessage }}</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <style>
.status-box {
  display: inline-block;
  text-align: center;
  padding: 0.08em 0.65em;
  border-radius: 0.25em;
  font-weight: 600;
  font-size: 0.95em;
  background: #e2e3e5;
  color: #41464b;
  border: 1.2px solid transparent;
}
.status-approved {
  background:rgb(23, 193, 108);
  color:rgb(247, 253, 250);
  border-color: #badbcc;
}
.status-declined, .status-deleted {
  background:rgb(234, 28, 45);
  color:rgb(239, 236, 236);
  border-color: #f5c2c7;
}
.status-pending {
  background:rgb(248, 203, 56);
  color:rgb(102, 102, 101);
  border-color: #ffe69c;
}
.status-added {
  background: #22bbea;
  color:rgb(243, 248, 249);
  border-color: #b6effb;
}
.status-default {
  background: #e2e3e5;
  color: #41464b;
  border-color: #d3d6d8;
}
</style>