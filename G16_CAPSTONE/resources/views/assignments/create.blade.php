@extends('layouts.apps')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card shadow" style="width: 100%; max-width: none; margin: 0;">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0"><i class="fas fa-users"></i> Assign Students to Task</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ url('/assignments') }}" id="assignmentForm">
                        @csrf
                        <div class="row">
                            <!-- Full Width Content -->
                            <div class="col-12">
                                <div class="row mb-4">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Category:</label>
                                            <select name="category_id" class="form-select" required>
                                                <option value="">Select Category</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">Start Date:</label>
                                            <input type="date" name="start_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">End Date:</label>
                                            <input type="date" name="end_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-bold">Available Students:</label>
                                    <div class="input-group mb-3">
                                        <input type="text" id="studentSearch" class="form-control" placeholder="Search students...">
                                        <button type="button" class="btn btn-outline-secondary" onclick="clearSearch()">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="card h-100" style="width: 100%; max-width: none;">
                                                <div class="card-header bg-success text-white text-center">
                                                    <h5 class="mb-0">Batch 2025</h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; width: 100%;">
                                                        <table class="table table-hover mb-0" style="width: 100%;">
                                                            <tbody id="batch2025Table">
                                                                @foreach($students->where('batch', '2025') as $student)
                                                                    <tr class="student-row" data-id="{{ $student->id }}" data-name="{{ $student->name }}" data-batch="{{ $student->batch }}" data-gender="{{ $student->gender }}">
                                                                        <td class="py-3 px-4" style="cursor: pointer; width: 100%;">
                                                                            {{ $student->name }} ({{ $student->gender }})
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="card h-100" style="width: 100%; max-width: none;">
                                                <div class="card-header bg-primary text-white text-center">
                                                    <h5 class="mb-0">Batch 2026</h5>
                                                </div>
                                                <div class="card-body p-0">
                                                    <div class="table-responsive" style="max-height: 400px; overflow-y: auto; width: 100%;">
                                                        <table class="table table-hover mb-0" style="width: 100%;">
                                                            <tbody id="batch2026Table">
                                                                @foreach($students->where('batch', '2026') as $student)
                                                                    <tr class="student-row" data-id="{{ $student->id }}" data-name="{{ $student->name }}" data-batch="{{ $student->batch }}" data-gender="{{ $student->gender }}">
                                                                        <td class="py-3 px-4" style="cursor: pointer; width: 100%;">
                                                                            {{ $student->name }} ({{ $student->gender }})
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Selected Students Display -->
                                <div class="mt-4">
                                    <h5 class="mb-3">Selected Students:</h5>
                                    <div id="selectedStudentsDisplay">
                                        <!-- Selected students will appear here -->
                                    </div>
                                </div>

                                <!-- Selected Students (Hidden inputs) -->
                                <div id="selectedStudentsInputs"></div>

                                <!-- Assign and Cancel Buttons -->
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-secondary btn-lg px-5 me-3" onclick="window.history.back()">
                                        <i class="fas fa-times"></i> Cancel
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-lg px-5">
                                        <i class="fas fa-check"></i> Assign Students
                                    </button>
                                </div>

                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.student-card {
    background: white;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    padding: 12px;
    margin-bottom: 8px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.2s;
}

.student-card:hover {
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    border-color: #007bff;
}

.student-info {
    flex-grow: 1;
}

.student-name {
    font-weight: 600;
    color: #333;
}

.student-details {
    font-size: 0.85rem;
    color: #666;
}

.batch-2025 {
    border-left: 4px solid #28a745;
}

.batch-2026 {
    border-left: 4px solid #007bff;
}

.delete-btn {
    background: #dc3545;
    border: none;
    color: white;
    border-radius: 6px;
    width: 35px;
    height: 35px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
    font-weight: bold;
}

.delete-btn:hover {
    background: #c82333;
    transform: scale(1.05);
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}
</style>

<script>
let selectedStudents = [];

// Search functionality
document.getElementById('studentSearch').addEventListener('input', function() {
    const searchTerm = this.value.toLowerCase();
    const rows = document.querySelectorAll('.student-row');

    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
});

// Clear search
function clearSearch() {
    document.getElementById('studentSearch').value = '';
    const rows = document.querySelectorAll('.student-row');
    rows.forEach(row => row.style.display = '');
}

// Add student (click on table row)
function addStudent(row) {
    const studentId = row.dataset.id;
    const studentName = row.dataset.name;
    const studentBatch = row.dataset.batch;
    const studentGender = row.dataset.gender;

    // Check if already selected
    if (selectedStudents.find(s => s.id === studentId)) {
        alert('This student is already selected.');
        return;
    }

    // Add to selected students
    const student = {
        id: studentId,
        name: studentName,
        batch: studentBatch,
        gender: studentGender
    };

    selectedStudents.push(student);

    // Hide the row
    row.style.display = 'none';

    // Update display
    updateSelectedStudentsDisplay();
}

// Update selected students display (create hidden inputs)
function updateSelectedStudentsDisplay() {
    const container = document.getElementById('selectedStudentsInputs');
    const displayContainer = document.getElementById('selectedStudentsDisplay');

    // Create hidden inputs
    let html = '';
    selectedStudents.forEach(student => {
        html += `<input type="hidden" name="members[]" value="${student.id}">`;
    });
    container.innerHTML = html;

    // Create visual display with delete and cancel buttons
    let displayHtml = '';
    selectedStudents.forEach(student => {
        displayHtml += `
            <div class="d-flex justify-content-between align-items-center border rounded p-3 mb-2 bg-white">
                <span class="fw-medium">${student.name} (${student.gender})</span>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm px-3 me-2">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-info btn-sm px-3" onclick="removeStudent('${student.id}')">
                        Delete
                    </button>
                </div>
            </div>
        `;
    });
    displayContainer.innerHTML = displayHtml;

    // Update visual feedback - add selected class to rows
    document.querySelectorAll('.student-row').forEach(row => {
        const studentId = row.dataset.id;

        if (selectedStudents.find(s => s.id === studentId)) {
            row.classList.add('table-success');
            row.style.opacity = '0.6';
        } else {
            row.classList.remove('table-success');
            row.style.opacity = '1';
        }
    });
}

// Remove student from selection
function removeStudent(studentId) {
    selectedStudents = selectedStudents.filter(s => s.id !== studentId);
    updateSelectedStudentsDisplay();
}

// Form validation
document.getElementById('assignmentForm').addEventListener('submit', function(e) {
    if (selectedStudents.length === 0) {
        e.preventDefault();
        alert('Please select at least one student to assign.');
        return false;
    }
});

// Add click event listeners to student rows
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.student-row').forEach(row => {
        row.addEventListener('click', function() {
            addStudent(this);
        });
    });
});
</script>
@endsection