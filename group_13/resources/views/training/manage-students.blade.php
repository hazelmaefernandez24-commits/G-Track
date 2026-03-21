@extends('layouts.nav')

@section('content')

<link rel="stylesheet" href="{{ asset('css/training/school.css') }}">

<h1 style="font-weight: 300;">Schools</h1>
<hr>

<br>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif

<div class="page-container">
    <div class="header-section">
        <a href="{{ route('training.schools.create') }}" class="btn btn-primary">
            Add New School
        </a>
    </div>

    <div class="table-wrapper">
        <div class="table-header">
            <div class="header-cell">ID</div>
            <div class="header-cell">School</div>
            <div class="header-cell">Department</div>
            <div class="header-cell">Course</div>
            <div class="header-cell">Actions</div>
        </div>
        
        @forelse($schools as $school)
            <div class="table-row">
                @if(is_object($school))
                    <div class="cell">{{ $school->school_id }}</div>
                    <div class="cell">{{ $school->name }}</div>
                    <div class="cell">{{ $school->department }}</div>
                    <div class="cell">{{ $school->course }}</div>
                    <div class="cell">
                        <div class="action-buttons">
                            <a href="{{ route('training.schools.show', $school) }}" class="btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('training.schools.edit', $school) }}" class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('training.schools.destroy', $school) }}" method="POST" class="delete-form" id="delete-school-form-{{ $school->school_id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button" class="btn-icon" title="Delete" onclick="showDeleteModal('school', '{{ $school->name }}', '{{ $school->school_id }}')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="cell" colspan="5">Invalid school data</div>
                @endif
            </div>
        @empty
            <div class="table-row">
                <div class="cell empty-message">No schools found</div>
            </div>
        @endforelse
    </div>
</div>

<!-- Custom Delete Confirmation Modal -->
<div id="deleteModal" class="delete-modal-overlay">
    <div class="delete-modal">
        <div class="delete-modal-header">
            <div class="delete-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="delete-title">Confirm Deletion</h3>
        </div>
        <div class="delete-modal-body">
            <p class="delete-message">
                <strong>Records can only be deleted beyond 10 years.</strong><br>
                Are you sure you want to delete <span id="deleteItemType"></span> "<span id="deleteItemName"></span>"?
            </p>
            <p class="delete-warning">
                <i class="fas fa-info-circle"></i>
                This action cannot be undone and will permanently remove all associated data.
            </p>
        </div>
        <div class="delete-modal-footer">
            <button type="button" class="btn-cancel" onclick="hideDeleteModal()">
                <i class="fas fa-times"></i> Cancel
            </button>
            <button type="button" class="btn-delete" onclick="confirmDelete()">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
</div>

<style>
/* Delete Modal Styles */
.delete-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(5px);
    z-index: 10000;
    animation: fadeIn 0.3s ease;
}

.delete-modal {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 500px;
    width: 90%;
    overflow: hidden;
    animation: slideIn 0.3s ease;
}

.delete-modal-header {
    background: linear-gradient(135deg, #ff6b6b, #ee5a52);
    color: white;
    padding: 30px;
    text-align: center;
    position: relative;
}

.delete-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    animation: pulse 2s infinite;
}

.delete-title {
    margin: 0;
    font-size: 1.5rem;
    font-weight: 600;
    font-family: 'Poppins', sans-serif;
}

.delete-modal-body {
    padding: 30px;
    text-align: center;
}

.delete-message {
    font-size: 1.1rem;
    color: #2c3e50;
    margin-bottom: 20px;
    line-height: 1.6;
    font-family: 'Poppins', sans-serif;
}

.delete-message strong {
    color: #e74c3c;
    font-weight: 600;
}

.delete-warning {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 10px;
    padding: 15px;
    color: #856404;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 15px;
}

.delete-modal-footer {
    padding: 20px 30px 30px;
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-cancel, .btn-delete {
    padding: 12px 25px;
    border: none;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    font-family: 'Poppins', sans-serif;
    min-width: 120px;
    justify-content: center;
}

.btn-cancel {
    background: #6c757d;
    color: white;
}

.btn-cancel:hover {
    background: #5a6268;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
}

.btn-delete {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
}

.btn-delete:hover {
    background: linear-gradient(135deg, #c0392b, #a93226);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translate(-50%, -60%);
    }
    to {
        opacity: 1;
        transform: translate(-50%, -50%);
    }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .delete-modal {
        width: 95%;
        margin: 20px;
    }

    .delete-modal-header {
        padding: 20px;
    }

    .delete-icon {
        font-size: 2.5rem;
    }

    .delete-title {
        font-size: 1.3rem;
    }

    .delete-modal-body {
        padding: 20px;
    }

    .delete-modal-footer {
        flex-direction: column;
        padding: 15px 20px 20px;
    }

    .btn-cancel, .btn-delete {
        width: 100%;
    }
}
</style>

<script>
let currentDeleteForm = null;

function showDeleteModal(type, name, id) {
    document.getElementById('deleteItemType').textContent = type;
    document.getElementById('deleteItemName').textContent = name;
    currentDeleteForm = document.getElementById(`delete-${type}-form-${id}`);
    document.getElementById('deleteModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentDeleteForm = null;
}

function confirmDelete() {
    if (currentDeleteForm) {
        currentDeleteForm.submit();
    }
    hideDeleteModal();
}

// Close modal when clicking outside
document.getElementById('deleteModal').addEventListener('click', function(e) {
    if (e.target === this) {
        hideDeleteModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('deleteModal').style.display === 'block') {
        hideDeleteModal();
    }
});
</script>

@endsection
