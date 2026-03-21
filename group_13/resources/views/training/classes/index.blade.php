@extends('layouts.nav')

@section('content')


<h1 style="font-weight: 300;">Classes</h1>
<hr>
<div class="page-container">

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

    <div class="table-wrapper">
        <div class="table-header">
            <div class="header-cell">CLASS ID</div>
            <div class="header-cell">CLASS NAME</div>
            <div class="header-cell">SCHOOL</div>
            <div class="header-cell nos">NOS</div>
            <div class="header-cell actions">ACTIONS</div>
        </div>
        
        @forelse($classes as $class)
            <div class="table-row">
                <div class="cell">{{ $class->class_id }}</div>
                <div class="cell">{{ $class->class_name }}</div>
                <div class="cell">
                    @if($class->school)
                    <a href="{{ url('/training/schools/' . $class->school->school_id) }}" class="school-link">
                        {{ $class->school->name }}
                    </a>
                    @else
                    No School Assigned
                    @endif
                </div>
                <div class="cell nos">{{ $class->students->count() }} student(s)</div>
                <div class="cell">
                    <div class="action-buttons">
                        <a href="{{ route('training.classes.show', $class) }}" class="btn-icon" title="View">
                            <i class="fas fa-eye"></i>
                        </a>
                        <a href="{{ route('training.classes.edit', $class) }}" class="btn-icon" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('training.classes.destroy', $class) }}" method="POST" class="d-inline delete-form" id="delete-class-form-{{ $class->id }}">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn-icon" title="Delete" onclick="showDeleteModal('class', '{{ $class->class_name }}', '{{ $class->id }}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="table-row">
                <div class="cell empty-message">No Classes found</div>
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

<style>
.table-wrapper {
    background: white;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    margin: 20px 0;
}

.table-header {
    display: grid;
    grid-template-columns: 100px 150px 450px 120px 180px;
    background: #22bbea;
    color: white;
    border-radius: 8px 8px 0 0;
    margin-bottom: 1px;
}

.header-cell {
    padding: 15px;
    font-size: 13px;
    font-weight: 500;
    text-transform: uppercase;
}

.table-row {
    display: grid;
    grid-template-columns: 100px 150px 450px 120px 180px;
    border-bottom: 1px solid #eee;
    align-items: center;
    background: white;
    padding: 5px 0;
}

.table-row:last-child {
    border-bottom: none;
    border-radius: 0 0 8px 8px;
}

.cell {
    padding: 10px 15px;
    font-size: 13px;
}

.nos {
    text-align: center;
}

.action-buttons {
    display: flex;
    gap: 10px;
    justify-content: center;
    padding-left: 15px;
    cursor: pointer;
}

.action-btn {
    padding: 5px 12px;
    border-radius: 4px;
    font-size: 13px;
    text-decoration: none;
    color: white;
    text-align: center;
}

.view {
    background-color: #22bbea;
    width: 55px;
}

.view:hover {
    background-color: #17a2b8;
    text-decoration: none;
    color: white;
}

.edit {
    background-color: #ff9933;
    width: 55px;
}

.edit:hover {
    background-color: #ffc107;
    text-decoration: none;
    color: white;
}

.actions {
    margin-left: 15px;
    display: flex;
    gap: 10px;
    justify-content: center;
}

.action-buttons {
    display: flex;
    gap: 8px;
    justify-content: center;
}

.btn-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    color: #fff;
    background-color: #4a90e2;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    text-decoration: none;
}

.btn-icon:hover {
    transform: translateY(-2px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
}

.btn-icon i {
    font-size: 14px;
}

/* View button */
.btn-icon[title="View"] {
    background-color: #4a90e2;
}

.btn-icon[title="View"]:hover {
    background-color: #357abd;
}

/* Edit button */
.btn-icon[title="Edit"] {
    background-color: #f39c12;
}

.btn-icon[title="Edit"]:hover {
    background-color: #d68910;
}

/* Delete button */
.btn-icon[title="Delete"] {
    background-color: #dc3545;
}

.btn-icon[title="Delete"]:hover {
    background-color: #c82333;
}

/* Form styles */
.delete-form {
    display: inline;
    margin: 0;
    padding: 0;
}

.nos {
    text-align: center;
}

.school-link {
    text-decoration: none;
    color: #337ab7;
}

.school-link:hover {
    text-decoration: none;
    color: #23527c;
}

@media (max-width: 1200px) {
    .table-header,
    .table-row {
        grid-template-columns: 100px 150px 400px 120px 180px;
    }
}

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
@endsection