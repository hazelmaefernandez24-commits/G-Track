@extends('layouts.nav')

@section('content')


<link rel="stylesheet" href="{{ asset('css/training/school/create.css') }}">
<div class="page-container">
    <div class="header-section">
        <h2>Create New School</h2>
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

    <!-- Student Conflict Validation Messages -->
    @if(session('student_conflicts'))
        <div class="alert alert-danger student-conflicts-alert">
            <div class="alert-header">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Student Enrollment Conflicts</strong>
            </div>
            <div class="alert-body">
                <p>{{ session('error') }}</p>
                <ul class="conflict-list">
                    @foreach(session('student_conflicts') as $conflict)
                        <li><i class="fas fa-user-times"></i> {{ $conflict }}</li>
                    @endforeach
                </ul>
            </div>
            <div class="alert-footer">
                <small><i class="fas fa-info-circle"></i> Each student can only be enrolled in one class at a time. Please remove students from their current classes or assign them to different classes within this school.</small>
            </div>
        </div>
    @endif

    <div id="formErrors" class="alert alert-error" style="display:none"></div>

    <form action="{{ route('training.schools.store') }}" method="POST" class="form-container" id="createSchoolForm">
        @csrf
        
        <div class="form-group">
            <label for="school_id">School ID</label>
            <input type="text" id="school_id" name="school_id" value="{{ old('school_id') }}" required>
            @error('school_id')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="name">School Name</label>
            <input type="text" id="name" name="name" value="{{ old('name') }}" required>
            @error('name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="department">Department</label>
            <input type="text" id="department" name="department" value="{{ old('department') }}" required>
            @error('department')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="course">Course</label>
            <input type="text" id="course" name="course" value="{{ old('course') }}" required>
            @error('course')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="semester_count">Number of Semesters</label>
            <input type="number" id="semester_count" name="semester_count" value="{{ old('semester_count') }}" required>
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
                        value="{{ old('passing_grade_min') }}" required>
                </div>
                <div class="input-group">
                    <label for="passingGradeMax">Passing Grade Max</label>
                    <input type="number" step="0.1" id="passingGradeMax" name="passing_grade_max" 
                        value="{{ old('passing_grade_max') }}" required>
                </div>
                <div class="input-group">
                    <label for="failingGradeMin">Failing Grade Min</label>
                    <input type="number" step="0.1" id="failingGradeMin" name="failing_grade_min" 
                        value="{{ old('failing_grade_min') }}" required>
                </div>
                <div class="input-group">
                    <label for="failingGradeMax">Failing Grade Max</label>
                    <input type="number" step="0.1" id="failingGradeMax" name="failing_grade_max" 
                        value="{{ old('failing_grade_max') }}" required>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label>Terms</label>
            <div class="checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terms[]" value="prelim" {{ in_array('prelim', old('terms', [])) ? 'checked' : '' }}>
                    Prelim
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="terms[]" value="midterm" {{ in_array('midterm', old('terms', [])) ? 'checked' : '' }}>
                    Midterm
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="terms[]" value="semi_final" {{ in_array('semi_final', old('terms', [])) ? 'checked' : '' }}>
                    Semi Final
                </label>
                <label class="checkbox-label">
                    <input type="checkbox" name="terms[]" value="final" {{ in_array('final', old('terms', [])) ? 'checked' : '' }}>
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
                @foreach(old('subjects', []) as $index => $subject)
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
                @foreach(old('classes', []) as $index => $class)
                    <div class="class-row">
                        <div class="class-header">
                            <div class="class-display">
                                <strong>ID:</strong>
                                <input type="text" name="classes[{{ $index }}][class_id]" placeholder="Class ID" value="{{ $class['class_id'] ?? '' }}" required>
                                <strong>Name:</strong>
                                <input type="text" name="classes[{{ $index }}][name]" placeholder="Class Name" value="{{ $class['name'] ?? '' }}" required>
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
            <button type="submit" class="btn-submit">Create School</button>
            <a href="{{ route('training.manage-students') }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

<!-- Student Selection Modal -->
<div id="studentModal" class="modal">
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
            <div id="modalStudentsContainer" class="students-list">
                <!-- Students will be loaded here -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-save" id="confirmStudentSelection">Save Selection</button>
            <button type="button" class="btn-cancel close-modal">Cancel</button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('createSchoolForm');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault(); // Prevent default submission
        
        const errorBox = document.getElementById('formErrors');
        if (errorBox) errorBox.style.display = 'none';
        
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
            } else if (response.status === 422) {
                // Validation error
                return response.json().then(data => {
                    displayValidationErrors(data.errors);
                });
            } else {
                return response.json();
            }
        })
        .then(data => {
            if (data && data.success) {
                window.location.href = '{{ route("training.manage-students") }}';
            } else if (data && data.message) {
                alert(data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while creating the school');
        });
    });

    let subjectCount = {{ count(old('subjects', [])) }};
    let classCount = {{ count(old('classes', [])) }};
    let currentClassIndex = null;
    const modal = document.getElementById('studentModal');
    const closeButtons = document.querySelectorAll('.close-modal');
    const confirmButton = document.getElementById('confirmStudentSelection');

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
        const container = document.getElementById('classes-container');
        const row = document.createElement('div');
        row.className = 'class-row';
        row.innerHTML = `
            <div class="class-header">
                <div class="class-display">
                    <strong>ID:</strong>
                    <input type="text" name="classes[${classCount}][class_id]" placeholder="Class ID" required>
                    <strong>Name:</strong>
                    <input type="text" name="classes[${classCount}][name]" placeholder="Class Name" required>
                </div>
                <button type="button" class="btn-select-students" data-class-index="${classCount}">Select Students</button>
                <button type="button" class="btn-remove" onclick="removeClass(this)">×</button>
            </div>
            <div id="students-container-${classCount}" class="students-container"></div>
        `;
        container.appendChild(row);
        classCount++;
        document.activeElement.blur(); // Prevent auto-focus triggering modal
    });

    // Close modal when clicking close button or outside the modal
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            modal.style.display = 'none';
        });
    });

    window.addEventListener('click', (event) => {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Handle confirm button click
    confirmButton.addEventListener('click', function() {
        const selectedStudents = Array.from(document.querySelectorAll('#modalStudentsContainer input[type="checkbox"]:checked'))
            .map(checkbox => ({
                id: checkbox.value,
                name: checkbox.getAttribute('data-name'),
                student_id: checkbox.getAttribute('data-student-id')
            }));

        updateSelectedStudentsList(currentClassIndex, selectedStudents);
        modal.style.display = 'none';
    });

    // Handle select students button click
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-select-students')) {
            currentClassIndex = e.target.getAttribute('data-class-index');
            modal.style.display = 'flex';
            loadStudents();
        }
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
    updateClassIndices();
}

function updateClassIndices() {
    const rows = document.querySelectorAll('.class-row');
    rows.forEach((row, index) => {
        const inputs = row.querySelectorAll('input');
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
    classCount = rows.length;
}

function loadStudents() {
    fetch('/training/api/students')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(students => {
            const container = document.getElementById('modalStudentsContainer');
            const batchFilter = document.getElementById('batchFilter');
            const batches = new Set();

            // Collect unique batches
            students.forEach(student => {
                if (student.batch) {
                    batches.add(student.batch);
                }
            });

            // Populate batch filter
            batchFilter.innerHTML = '<option value="">All Batches</option>';
            Array.from(batches).sort().forEach(batch => {
                const option = document.createElement('option');
                option.value = batch;
                option.textContent = `Batch ${batch}`;
                batchFilter.appendChild(option);
            });

            // Function to render students
            const renderStudents = (filteredStudents) => {
                container.innerHTML = filteredStudents.map(student => {
                    const studentId = `${student.batch}${student.group}${student.student_number}${student.training_code}`;
                    const fullName = `${student.user_lname}, ${student.user_fname}`;
                    return `
                        <div class="student-item" data-batch="${student.batch || ''}">
                            <label class="student-checkbox">
                                <input type="checkbox" 
                                       value="${student.user_id}"
                                       data-name="${fullName}"
                                       data-student-id="${studentId}">
                                <span>${studentId} - ${fullName}</span>
                            </label>
                        </div>
                    `;
                }).join('');
            };

            // Initial render
            renderStudents(students);

            // Add batch filter event listener
            batchFilter.addEventListener('change', function() {
                const selectedBatch = this.value;
                const filteredStudents = selectedBatch 
                    ? students.filter(student => student.batch === selectedBatch)
                    : students;
                renderStudents(filteredStudents);
            });
        })
        .catch(error => {
            console.error('Error loading students:', error);
            document.getElementById('modalStudentsContainer').innerHTML = 
                `<p class="error-message">Error loading students: ${error.message}</p>`;
        });
}

function updateSelectedStudentsList(classIndex, students) {
    const container = document.getElementById(`students-container-${classIndex}`);
    
    // Create selected students display
    const selectedStudentsHtml = `
        <div class="selected-students">
            <h4>Selected Students:</h4>
            <div class="selected-students-list">
                ${students.map(student => `
                    <div class="selected-student-tag" data-student-id="${student.id}">
                        ${student.student_id} - ${student.name}
                        <span class="remove-student" onclick="removeSelectedStudent(this, ${classIndex}, ${student.id})">&times;</span>
                    </div>
                `).join('')}
            </div>
        </div>
    `;

    // Add hidden inputs for student IDs
    const hiddenInputsHtml = students.map(student => 
        `<input type="hidden" name="classes[${classIndex}][student_ids][]" value="${student.id}">`
    ).join('');

    container.innerHTML = selectedStudentsHtml + hiddenInputsHtml;
}

function removeSelectedStudent(button, classIndex, studentId) {
    const tag = button.parentElement;
    tag.remove();
    
    // Remove the corresponding hidden input
    const hiddenInput = document.querySelector(`input[name="classes[${classIndex}][student_ids][]"][value="${studentId}"]`);
    if (hiddenInput) {
        hiddenInput.remove();
    }
}

function displayValidationErrors(errors) {
    // Collect all error messages
    let allMessages = [];
    for (const [field, messages] of Object.entries(errors)) {
        allMessages = allMessages.concat(messages);
    }

    // Display all errors in the error box
    const errorBox = document.getElementById('formErrors');
    if (errorBox) {
        errorBox.innerHTML = allMessages.map(msg => `<div>${msg}</div>`).join('');
        errorBox.style.display = 'block';
    } else {
        // Fallback: alert all errors
        alert(allMessages.join('\n'));
    }
}
</script>

@endsection 