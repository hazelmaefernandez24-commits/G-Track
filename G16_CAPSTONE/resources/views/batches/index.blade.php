@extends('layouts.apps')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Batch Management</h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Year</th>
                  <th>Display Name</th>
                  <th>Students Count</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="batchesTable">
                @foreach($batches as $batch)
                <tr id="batch-row-{{ $batch->id }}">
                  <td>{{ $batch->year }}</td>
                  <td>
                    <span id="name-display-{{ $batch->id }}">{{ $batch->display_name }}</span>
                    <input type="text" class="form-control form-control-sm d-none" 
                           id="name-edit-{{ $batch->id }}" value="{{ $batch->name }}">
                  </td>
                  <td>{{ $batch->students->count() }}</td>
                  <td>
                    <span class="badge bg-{{ $batch->is_active ? 'success' : 'secondary' }}">
                      {{ $batch->is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                  <td>
                    <div class="btn-group btn-group-sm">
                      <button class="btn btn-outline-primary" onclick="editBatch({{ $batch->id }})" 
                              id="edit-btn-{{ $batch->id }}">
                        <i class="bi bi-pencil"></i>
                      </button>
                      <button class="btn btn-success d-none" onclick="saveBatch({{ $batch->id }})" 
                              id="save-btn-{{ $batch->id }}">
                        <i class="bi bi-check"></i>
                      </button>
                      <button class="btn btn-secondary d-none" onclick="cancelEdit({{ $batch->id }})" 
                              id="cancel-btn-{{ $batch->id }}">
                        <i class="bi bi-x"></i>
                      </button>
                      <button class="btn btn-outline-{{ $batch->is_active ? 'warning' : 'success' }}" 
                              onclick="toggleBatch({{ $batch->id }}, {{ $batch->is_active ? 'false' : 'true' }})">
                        <i class="bi bi-{{ $batch->is_active ? 'pause' : 'play' }}"></i>
                      </button>
                      @if($batch->students->count() == 0)
                      <button class="btn btn-outline-danger" onclick="deleteBatch({{ $batch->id }}, {{ $batch->year }})">
                        <i class="bi bi-trash"></i>
                      </button>
                      @endif
                    </div>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Add New Batch</h5>
        </div>
        <div class="card-body">
          <form id="addBatchForm">
            @csrf
            <div class="mb-3">
              <label for="year" class="form-label">Year</label>
              <input type="number" class="form-control" id="year" name="year" 
                     min="2020" max="2050" required>
            </div>
            <div class="mb-3">
              <label for="name" class="form-label">Display Name (Optional)</label>
              <input type="text" class="form-control" id="name" name="name" 
                     placeholder="e.g., Batch 2027">
            </div>
            <button type="submit" class="btn btn-primary w-100">
              <i class="bi bi-plus"></i> Add Batch
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
// Add new batch
document.getElementById('addBatchForm').addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(this);
  const data = Object.fromEntries(formData);
  
  fetch('/batches', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify(data)
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('success', data.message);
      setTimeout(() => location.reload(), 1500);
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error adding batch');
  });
});

// Edit batch name
function editBatch(batchId) {
  const nameDisplay = document.getElementById(`name-display-${batchId}`);
  const nameEdit = document.getElementById(`name-edit-${batchId}`);
  const editBtn = document.getElementById(`edit-btn-${batchId}`);
  const saveBtn = document.getElementById(`save-btn-${batchId}`);
  const cancelBtn = document.getElementById(`cancel-btn-${batchId}`);

  nameDisplay.classList.add('d-none');
  nameEdit.classList.remove('d-none');
  editBtn.classList.add('d-none');
  saveBtn.classList.remove('d-none');
  cancelBtn.classList.remove('d-none');
  
  nameEdit.focus();
}

// Save batch
function saveBatch(batchId) {
  const nameEdit = document.getElementById(`name-edit-${batchId}`);
  const newName = nameEdit.value.trim();

  fetch(`/batches/${batchId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ name: newName })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('success', data.message);
      setTimeout(() => location.reload(), 1500);
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error updating batch');
  });
}

// Cancel edit
function cancelEdit(batchId) {
  location.reload(); // Simple approach to reset the form
}

// Toggle batch active status
function toggleBatch(batchId, isActive) {
  fetch(`/batches/${batchId}`, {
    method: 'PUT',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    },
    body: JSON.stringify({ is_active: isActive })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('success', data.message);
      setTimeout(() => location.reload(), 1500);
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error updating batch status');
  });
}

// Delete batch
function deleteBatch(batchId, year) {
  if (!confirm(`Are you sure you want to delete Batch ${year}?`)) {
    return;
  }

  fetch(`/batches/${batchId}`, {
    method: 'DELETE',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
    }
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      showNotification('success', data.message);
      setTimeout(() => location.reload(), 1500);
    } else {
      alert('Error: ' + data.message);
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error deleting batch');
  });
}

// Show notification
function showNotification(type, message) {
  const notification = document.createElement('div');
  notification.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show position-fixed`;
  notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
  notification.innerHTML = `
    ${message}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => {
    if (notification.parentNode) {
      notification.remove();
    }
  }, 3000);
}
</script>
@endsection