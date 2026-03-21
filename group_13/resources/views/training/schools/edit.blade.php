@extends('layouts.nav')

@section('content')
<div class="page-container">
    <div class="header-section">
        <h2>Edit School</h2>
    </div>

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

    <form action="{{ route('training.schools.update', $school) }}" method="POST" class="form-container" id="updateSchoolForm">
        @csrf
        @method('PUT')
        
        <input type="hidden" name="school_id" value="{{ $school->school_id }}">
        
        <div class="form-group">
            <label for="school_id">School ID</label>
            <input type="text" id="school_id" name="school_id" value="{{ old('school_id', $school->school_id) }}" required readonly>
            @error('school_id')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="name">School Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $school->name) }}" required>
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" value="{{ old('department', $school->department) }}" required>
            @error('department')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="course">Course</label>
            <input type="text" id="course" name="course" value="{{ old('course', $school->course) }}" required>
            @error('course')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="semester_count">Number of Semesters</label>
            <input type="number" id="semester_count" name="semester_count" value="{{ old('semester_count', $school->semester_count) }}" required>
            @error('semester_count')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label>Grade Range Configuration</label>
            <div class="grade-range-selector">
                <div class="input-group">
                    <label for="passingGradeMin">Passing Grade Min</label>
                    <input type="number" step="0.1" id="passingGradeMin" name="passing_grade_min" 
                        value="{{ old('passing_grade_min', $school->passing_grade_min) }}" required>
                </div>
                <div class="input-group">
                    <label for="passingGradeMax">Passing Grade Max</label>
                    <input type="number" step="0.1" id="passingGradeMax" name="passing_grade_max" 
                        value="{{ old('passing_grade_max', $school->passing_grade_max) }}" required>
                </div>
                <div class="input-group">
                    <label for="failingGradeMin">Failing Grade Min</label>
                    <input type="number" step="0.1" id="failingGradeMin" name="failing_grade_min" 
                        value="{{ old('failing_grade_min', $school->failing_grade_min) }}" required>
                </div>
                <div class="input-group">
                    <label for="failingGradeMax">Failing Grade Max</label>
                    <input type="number" step="0.1" id="failingGradeMax" name="failing_grade_max" 
                        value="{{ old('failing_grade_max', $school->failing_grade_max) }}" required>
                </div>
            </div>
        </div>


        <div class="form-group">
            <label>Terms</label>
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms[]" value="prelim" {{ in_array('prelim', old('terms', (array)($school->terms ?? []))) ? 'checked' : '' }}>
                    Prelim
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="terms[]" value="midterm" {{ in_array('midterm', old('terms', (array)($school->terms ?? []))) ? 'checked' : '' }}>
                    Midterm
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="terms[]" value="semi_final" {{ in_array('semi_final', old('terms', (array)($school->terms ?? []))) ? 'checked' : '' }}>
                    Semi Final
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="terms[]" value="final" {{ in_array('final', old('terms', (array)($school->terms ?? []))) ? 'checked' : '' }}>
                    Final
                </label>
            </div>
            @error('terms')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        

        <div class="subjects-section">
            <h3>Subjects</h3>
            <div id="subjects-container">
                @foreach(old('subjects', $school->subjects ?? []) as $index => $subject)
                    <div class="subject-row">
                        <input type="text" name="subjects[{{ $index }}][offer_code]" placeholder="Offer Code" value="{{ $subject['offer_code'] ?? '' }}" required>
                        <input type="text" name="subjects[{{ $index }}][name]" placeholder="Subject Name" value="{{ $subject['name'] ?? '' }}" required>
                        <input type="text" name="subjects[{{ $index }}][instructor]" placeholder="Instructor" value="{{ $subject['instructor'] ?? '' }}" required>
                        <input type="text" name="subjects[{{ $index }}][schedule]" placeholder="Schedule" value="{{ $subject['schedule'] ?? '' }}" required>
                        <button type="button" class="btn-remove" onclick="removeSubject(this)">×</button>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-subject" class="btn-add">Add Subject</button>
        </div>

        <div class="classes-section">
            <h3>Classes</h3>
            <div id="classes-container">
                @foreach($existingClasses as $index => $class)
                    <div class="class-row">
                        <div class="class-header">
                            <div class="class-display">
                                <strong>ID:</strong>
                                <input type="text" name="classes[{{ $index }}][class_id]" value="{{ $class->class_id }}" readonly>
                                <strong>Name:</strong>
                                <input type="text" name="classes[{{ $index }}][name]" value="{{ $class->name ?? $class->class_name ?? '' }}" readonly>
                            </div>
                            <div class="selected-students">
                                {{ $class->students->count() }} student(s) enrolled
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <h3>Add New Class</h3>
            <div id="new-classes-container">
                @foreach(old('new_classes', []) as $index => $class)
                    <div class="class-row">
                        <div class="class-header">
                            <div class="class-display">
                                <strong>ID:</strong>
                                <input type="text" name="new_classes[{{ $index }}][class_id]" placeholder="Class ID" value="{{ $class['class_id'] ?? '' }}" required>
                                <strong>Name:</strong>
                                <input type="text" name="new_classes[{{ $index }}][name]" placeholder="Class Name" value="{{ $class['name'] ?? '' }}" required>
                            </div>
                            <button type="button" class="btn-select-students" data-class-index="{{ $index }}">Select Students</button>
                            <button type="button" class="btn-remove" onclick="removeClass(this)">×</button>
                        </div>
                        <div id="students-container-{{ $index }}" class="students-container"></div>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-class" class="btn-add">Add New Class</button>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-submit" style="cursor: pointer; opacity: 1;">Update School</button>
            <a href="{{ route('training.manage-students') }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('updateSchoolForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Create FormData object
        const formData = new FormData(form);
        
        // Send the form data using fetch
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data) {
                if (data.success) {
                    // Show success message immediately
                    alert(data.message || 'School updated successfully!');
                    // Then redirect
                    window.location.href = data.redirect || '{{ route("training.manage-students") }}';
                } else {
                    // Display validation errors if available
                    if (data.errors) {
                        let errorMessages = 'Validation failed:\n';
                        for (const [field, messages] of Object.entries(data.errors)) {
                            errorMessages += `${field}: ${messages.join(', ')}\n`;
                        }
                        alert(errorMessages);
                    } else {
                        alert(data.message || 'An error occurred');
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the school');
        });
    });

    let subjectCount = {{ count(old('subjects', $school->subjects ?? [])) }};
    let newClassCount = {{ count(old('new_classes', [])) }};

    // Add Subject Button
    document.getElementById('add-subject').addEventListener('click', function() {
        const container = document.getElementById('subjects-container');
        const row = document.createElement('div');
        row.className = 'subject-row';
        row.innerHTML = `
            <input type="text" name="subjects[${subjectCount}][offer_code]" placeholder="Offer Code" required>
            <input type="text" name="subjects[${subjectCount}][name]" placeholder="Subject Name" required>
            <input type="text" name="subjects[${subjectCount}][instructor]" placeholder="Instructor" required>
            <input type="text" name="subjects[${subjectCount}][schedule]" placeholder="Schedule" required>
            <button type="button" class="btn-remove" onclick="removeSubject(this)">×</button>
        `;
        container.appendChild(row);
        subjectCount++;
    });

    // Add Class Button
    document.getElementById('add-class').addEventListener('click', function() {
        const container = document.getElementById('new-classes-container');
        const row = document.createElement('div');
        row.className = 'class-row';
        row.innerHTML = `
            <div class="class-header">
                <div class="class-display">
                    <strong>ID:</strong>
                    <input type="text" name="new_classes[${newClassCount}][class_id]" placeholder="Class ID" required>
                    <strong>Name:</strong>
                    <input type="text" name="new_classes[${newClassCount}][name]" placeholder="Class Name" required>
                </div>
                <button type="button" class="btn-select-students" data-class-index="${newClassCount}">Select Students</button>
                <button type="button" class="btn-remove" onclick="removeClass(this)">×</button>
            </div>
            <div id="students-container-${newClassCount}" class="students-container"></div>
        `;
        container.appendChild(row);
        attachStudentSelectionListener(newClassCount);
        newClassCount++;
    });

    // Attach student selection listeners to existing buttons
    document.querySelectorAll('.btn-select-students').forEach(button => {
        attachStudentSelectionListener(button.dataset.classIndex);
    });
});

function removeSubject(button) {
    const row = button.parentElement;
    row.remove();
    updateSubjectIndices();
}

function updateSubjectIndices() {
    const rows = document.querySelectorAll('.subject-row');
    rows.forEach((row, index) => {
        const inputs = row.querySelectorAll('input');
        inputs.forEach(input => {
            const name = input.name;
            input.name = name.replace(/\[\d+\]/, `[${index}]`);
        });
    });
    subjectCount = rows.length;
}

function removeClass(button) {
    const row = button.closest('.class-row');
    row.remove();
    updateNewClassIndices();
}

function updateNewClassIndices() {
    const rows = document.querySelectorAll('#new-classes-container .class-row');
    rows.forEach((row, index) => {
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => {
            const name = input.name;
            input.name = name.replace(/\[\d+\]/, `[${index}]`);
        });
        const button = row.querySelector('.btn-select-students');
        if (button) {
            button.dataset.classIndex = index;
        }
        const container = row.querySelector('.students-container');
        if (container) {
            container.id = `students-container-${index}`;
        }
    });
    newClassCount = rows.length;
}

function attachStudentSelectionListener(classIndex) {
    const button = document.querySelector(`.btn-select-students[data-class-index="${classIndex}"]`);
    if (button) {
        button.addEventListener('click', function() {
            // Create and show modal for student selection
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Select Students</h3>
                        <button type="button" class="close-modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="batch-filter">
                            <label for="batchFilter">Filter by Batch:</label>
                            <select id="batchFilter">
                                <option value="">All Batches</option>
                            </select>
                        </div>
                        <div class="students-list">
                            <!-- Students will be loaded here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-save">Save Selection</button>
                        <button type="button" class="btn-cancel">Cancel</button>
                    </div>
                </div>
            `;

            document.body.appendChild(modal);

            // Load all students
            fetch('/training/api/students')
                .then(response => response.json())
                .then(students => {
                    const studentsList = modal.querySelector('.students-list');
                    const batchFilter = modal.querySelector('#batchFilter');
                    const batches = new Set();

                    students.forEach(student => {
                        if (student.batch) {
                            batches.add(student.batch);
                        }
                    });

                    // Populate batch filter
                    batches.forEach(batch => {
                        const option = document.createElement('option');
                        option.value = batch;
                        option.textContent = `Batch ${batch}`;
                        batchFilter.appendChild(option);
                    });

                    // Populate students list
                    students.forEach(student => {
                        const div = document.createElement('div');
                        div.className = 'student-item';
                        div.innerHTML = `
                            <label class="student-checkbox">
                                <input type="checkbox" name="new_classes[${classIndex}][students][]" 
                                       value="${student.user_id}" data-batch="${student.batch || ''}">
                                <span>${student.user_fname} ${student.user_lname} (${student.student_number}) - Batch ${student.batch || 'N/A'}</span>
                            </label>
                        `;
                        studentsList.appendChild(div);
                    });

                    // Handle batch filter
                    batchFilter.addEventListener('change', function() {
                        const selectedBatch = this.value;
                        const checkboxes = studentsList.querySelectorAll('.student-checkbox');
                        checkboxes.forEach(checkbox => {
                            const studentBatch = checkbox.querySelector('input').dataset.batch;
                            checkbox.style.display = !selectedBatch || studentBatch === selectedBatch ? 'flex' : 'none';
                        });
                    });
                });

            // Handle modal close
            modal.querySelector('.close-modal').addEventListener('click', () => {
                modal.remove();
            });

            modal.querySelector('.btn-cancel').addEventListener('click', () => {
                modal.remove();
            });

            // Handle save selection
            modal.querySelector('.btn-save').addEventListener('click', () => {
                const selectedCheckboxes = Array.from(modal.querySelectorAll('input[type="checkbox"]:checked'));
                const selectedStudents = selectedCheckboxes.map(checkbox => ({
                    id: checkbox.value,
                    name: checkbox.parentElement.querySelector('span').textContent
                }));

                const studentsContainer = document.getElementById(`students-container-${classIndex}`);
                studentsContainer.innerHTML = `
                    <div class="selected-students">
                        ${selectedStudents.length} student(s) selected
                        <ul style="margin: 8px 0 0 0; padding-left: 18px;">
                            ${selectedStudents.map(student => `<li>${student.name}</li>`).join('')}
                        </ul>
                    </div>
                `;

                // Add hidden inputs for selected students
                selectedStudents.forEach(student => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `new_classes[${classIndex}][students][]`;
                    input.value = student.id;
                    studentsContainer.appendChild(input);
                });

                modal.remove();
            });
        });
    }
}

function batchesOptionsHtml() {
    return `@foreach($batches as $batch)<option value="{{ $batch->batch }}">{{ $batch->batch }}</option>@endforeach`;
}
</script>

<style>
.page-container {
    padding: 20px;
    max-width: 100%;
}

.header-section {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 20px;
}

.header-section h2 {
    font-size: 24px;
    color: #333;
    margin: 0;
}

.form-container {
    background: white;
    padding: 24px;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 500;
}

.form-group input[type="text"],
.form-group input[type="number"] {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.form-group input[type="text"]:focus,
.form-group input[type="number"]:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

.checkbox-group {
    display: flex;
    gap: 16px;
}

.grade-range-selector {
    display: flex;
    gap: 20px;
    margin-bottom: 12px;
}

.radio-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.grade-info {
    margin-top: 12px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 4px;
}

.grade-info div {
    margin-bottom: 8px;
    font-size: 14px;
}

.grade-info div:last-child {
    margin-bottom: 0;
}

.grade-info span {
    font-weight: 500;
    font-family: monospace;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.subjects-section {
    margin-top: 24px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 4px;
}

.subjects-section h3 {
    margin: 0 0 16px 0;
    color: #333;
    font-size: 18px;
}

.subject-row {
    display: flex;
    gap: 12px;
    margin-bottom: 12px;
    align-items: center;
}

.subject-row input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.btn-remove {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #dc3545;
    color: white;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
}

.btn-add {
    background: #22bbea;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.classes-section {
    margin-top: 24px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 4px;
}

.classes-section h3 {
    margin: 0 0 16px 0;
    color: #333;
    font-size: 18px;
}

.class-row {
    margin-bottom: 12px;
}

.class-header {
    display: flex;
    gap: 12px;
    align-items: center;
}

.class-header input {
    flex: 1;
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.btn-select-students {
    background: #22bbea;
    color: white;
    padding: 8px 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
}

.students-container {
    margin-top: 12px;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 24px;
}

.btn-submit {
    background: #22bbea;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer !important;
    font-size: 14px;
    width: auto;
    min-width: 120px;
    display: inline-block;
    text-align: center;
    text-decoration: none;
    opacity: 1 !important;
    pointer-events: auto !important;
}

.btn-submit:hover {
    background:rgb(20, 172, 219);
    text-decoration: none;
    opacity: 1 !important;
}

.btn-submit:active {
    background: #22bbea;
    text-decoration: none;
    opacity: 1 !important;
}

.btn-cancel {
    background: #ff9933;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    text-decoration: none;
    text-align: center;
}

.btn-cancel:hover {
    background:rgb(255, 128, 0);
    color: #000;
    text-decoration: none;
    text-align: center;
}

.error-message {
    color: #dc3545;
    font-size: 12px;
    margin-top: 4px;
}

.alert {
    padding: 12px 16px;
    margin-bottom: 16px;
    border-radius: 4px;
}

.alert-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
    color: #155724;
}

.alert-error {
    background-color: #f8d7da;
    border-color: #f5c6cb;
    color: #721c24;
}

@media (max-width: 768px) {
    .page-container {
        padding: 16px;
    }
    
    .subject-row {
        flex-direction: column;
        gap: 8px;
    }
    
    .checkbox-group {
        flex-direction: column;
        gap: 8px;
    }
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background: white;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 80vh;
    display: flex;
    flex-direction: column;
}

.modal-header {
    padding: 16px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-header h3 {
    margin: 0;
    color: #333;
}

.close-modal {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.modal-body {
    padding: 16px;
    overflow-y: auto;
}

.batch-filter {
    margin-bottom: 16px;
}

.batch-filter select {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.students-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.student-item {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.student-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.modal-footer {
    padding: 16px;
    border-top: 1px solid #ddd;
    display: flex;
    justify-content: flex-end;
    gap: 8px;
}

.btn-save {
    background: #22bbea;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-cancel {
    background: #ff9933;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 4px;
    cursor: pointer;
}

.selected-students {
    padding: 8px;
    background: #e9ecef;
    border-radius: 4px;
    font-size: 14px;
    color: #666;
}

.styled-batch-select {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    background: #fff;
    color: #333;
    margin-left: 8px;
    min-width: 120px;
}

.styled-batch-select:focus {
    border-color: #4CAF50;
    outline: none;
    box-shadow: 0 0 0 2px rgba(76, 175, 80, 0.2);
}

/* Alert Styling */
.alert {
    padding: 12px 16px;
    margin-bottom: 20px;
    border: 1px solid transparent;
    border-radius: 6px;
    position: relative;
    display: flex;
    align-items: center;
}

.alert-success {
    color: #155724;
    background-color: #d4edda;
    border-color: #c3e6cb;
}

.alert-error {
    color: #721c24;
    background-color: #f8d7da;
    border-color: #f5c6cb;
}
</style>
@endsection 