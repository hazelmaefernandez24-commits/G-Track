@extends('layouts.nav')

@section('content')
<div class="create-submission-container">
    <div class="page-header">
        <h1>Add Intern Grade</h1>
        <p class="subtitle">Enter the intern's evaluation grades</p>
    </div>
    
    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('training.intern-grades.store') }}" method="POST" class="submission-form">
        @csrf

        <!-- Intern Information Card -->
        <div class="form-card">
            <div class="card-header">
                <h3><i class="fas fa-user-graduate"></i> Intern Information</h3>
            </div>
            <div class="card-body">
            <div class="form-grid">
                    <!-- School Selection -->
                <div class="form-group">
                        <label for="school_id">School</label>
                        <select name="school_id" id="school_id" required class="form-control">
                        <option value="">-- Select School --</option>
                        @foreach ($schools as $school)
                            <option value="{{ $school->school_id }}" 
                                {{ old('school_id') == $school->school_id ? 'selected' : '' }}>
                                {{ $school->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('school_id')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                    <!-- Class Selection -->
                    <div class="form-group">
                        <label for="class_id">Class</label>
                        <select name="class_id" id="class_id" required class="form-control" disabled>
                            <option value="">-- Select Class --</option>
                        </select>
                        @error('class_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Intern Selection -->
                    <div class="form-group">
                        <label for="intern_id">Intern</label>
                        <select name="intern_id" id="intern_id" required class="form-control" disabled>
                            <option value="">-- Select Intern --</option>
                        </select>
                        @error('intern_id')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Company Name -->
                <div class="form-group">
                        <label for="company_name">Company Name</label>
                    <input type="text" 
                           name="company_name" 
                           id="company_name" 
                           value="{{ old('company_name') }}"
                           required
                               class="form-control"
                           placeholder="Enter company name">
                    @error('company_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                    <!-- Submission Date -->
                <div class="form-group">
                        <label for="submission_date">Submission Date</label>
                        <input type="date" 
                               name="submission_date" 
                               id="submission_date" 
                               value="{{ old('submission_date', date('Y-m-d')) }}"
                               required
                               class="form-control">
                        @error('submission_date')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                    <!-- Submission Number -->
                <div class="form-group">
                        <label for="submission_number">Submission Number</label>
                        <select name="submission_number" id="submission_number" required class="form-control">
                            <option value="">-- Select Submission --</option>
                            <option value="1st" {{ old('submission_number') == '1st' ? 'selected' : '' }}>1st Submission</option>
                            <option value="2nd" {{ old('submission_number') == '2nd' ? 'selected' : '' }}>2nd Submission</option>
                            <option value="3rd" {{ old('submission_number') == '3rd' ? 'selected' : '' }}>3rd Submission</option>
                            <option value="4th" {{ old('submission_number') == '4th' ? 'selected' : '' }}>4th Submission</option>
                    </select>
                        @error('submission_number')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                    </div>
                </div>
            </div>
        </div>

        <!-- Evaluation Grades Card -->
        <div class="form-card">
            <div class="card-header">
                <h3><i class="fas fa-chart-bar"></i> Evaluation Grades</h3>
            </div>
            <div class="card-body">
            <div class="grades-grid">
                    <!-- ICT Learning Competency -->
                <div class="grade-item">
                        <label for="ict_learning">ICT Learning Competency</label>
                    <div class="grade-input-wrapper">
                        <input type="number" 
                               name="grades[ict_learning_competency]" 
                               id="ict_learning"
                               min="1"
                               max="4"
                               step="1"
                               required
                               value="{{ old('grades.ict_learning_competency') }}"
                               onchange="calculateFinalGrade()"
                                   onkeypress="return event.charCode >= 49 && event.charCode <= 52"
                                   class="form-control">
                        <span class="grade-weight">(40%)</span>
                    </div>
                        @error('grades.ict_learning_competency')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                </div>

                    <!-- 21st Century Skills -->
                <div class="grade-item">
                        <label for="century_skills">21st Century Skills</label>
                    <div class="grade-input-wrapper">
                        <input type="number" 
                               name="grades[twenty_first_century_skills]" 
                               id="century_skills"
                               min="1"
                               max="4"
                               step="1"
                               required
                               value="{{ old('grades.twenty_first_century_skills') }}"
                               onchange="calculateFinalGrade()"
                                   onkeypress="return event.charCode >= 49 && event.charCode <= 52"
                                   class="form-control">
                        <span class="grade-weight">(30%)</span>
                    </div>
                        @error('grades.twenty_first_century_skills')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                </div>

                    <!-- Expected Outputs -->
                <div class="grade-item">
                        <label for="outputs">Expected Outputs/Deliverables</label>
                    <div class="grade-input-wrapper">
                        <input type="number" 
                               name="grades[expected_outputs_deliverables]" 
                               id="outputs"
                               min="1"
                               max="4"
                               step="1"
                               required
                               value="{{ old('grades.expected_outputs_deliverables') }}"
                               onchange="calculateFinalGrade()"
                                   onkeypress="return event.charCode >= 49 && event.charCode <= 52"
                                   class="form-control">
                        <span class="grade-weight">(30%)</span>
                    </div>
                        @error('grades.expected_outputs_deliverables')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                </div>
            </div>

                <!-- Final Grade Display -->
            <div class="final-grade-display">
                <div class="final-grade-box">
                        <label>Final Grade</label>
                    <input type="text" 
                           name="final_grade" 
                           id="final_grade" 
                           readonly 
                               value="{{ old('final_grade') }}"
                               class="form-control">
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information Card -->
        <div class="form-card">
            <div class="card-header">
                <h3><i class="fas fa-comment-alt"></i> Additional Information</h3>
            </div>
            <div class="card-body">
            <div class="form-group">
                    <label for="remarks">Remarks</label>
                <textarea name="remarks" 
                          id="remarks" 
                          rows="3" 
                              class="form-control"
                          placeholder="Enter any additional remarks about the evaluation">{{ old('remarks') }}</textarea>
                @error('remarks')
                    <span class="error-message">{{ $message }}</span>
                @enderror
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Grade
            </button>
            <a href="{{ route('training.intern-grades.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const schoolSelect = $('#school_id');
    const classSelect = $('#class_id');
    const internSelect = $('#intern_id');

    // Function to populate classes dropdown
    function populateClasses(classes) {
        classSelect.html('<option value="">-- Select Class --</option>');
        if (classes && classes.length > 0) {
            classes.forEach(function(class_) {
                classSelect.append(
                    $('<option></option>')
                        .val(class_.class_id)
                        .text(`${class_.class_name} (${class_.batch})`)
                );
            });
            classSelect.prop('disabled', false);
        } else {
            classSelect.prop('disabled', true);
            internSelect.prop('disabled', true);
        }
    }

    // Function to populate students dropdown
    function populateStudents(students) {
        internSelect.html('<option value="">-- Select Student --</option>');
        if (students && students.length > 0) {
            students.forEach(function(student) {
                internSelect.append(
                    $('<option></option>')
                        .val(student.user_id)
                        .text(`${student.user_fname} ${student.user_lname}`)
                );
            });
            internSelect.prop('disabled', false);
        } else {
            internSelect.prop('disabled', true);
        }
    }

    // Handle school selection
    schoolSelect.on('change', function() {
        const schoolId = $(this).val();
        // Reset and disable dependent fields
        classSelect.html('<option value="">-- Select Class --</option>').prop('disabled', true);
        internSelect.html('<option value="">-- Select Student --</option>').prop('disabled', true);

        if (schoolId) {
            // Show loading state
            classSelect.html('<option value="">Loading classes...</option>').prop('disabled', true);

            // Fetch classes for selected school
            $.ajax({
                url: `/training/api/schools/${schoolId}/classes`,
                method: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(classes) {
                    if (Array.isArray(classes) && classes.length > 0) {
                        classSelect.html('<option value="">-- Select Class --</option>');
                        classes.forEach(function(class_) {
                            classSelect.append(
                                $('<option></option>')
                                    .val(class_.class_id)
                                    .text(`${class_.class_name} (${class_.batch})`)
                            );
                        });
                        classSelect.prop('disabled', false);
                    } else {
                        classSelect.html('<option value="">No classes found</option>').prop('disabled', true);
                        internSelect.html('<option value="">-- Select Student --</option>').prop('disabled', true);
                    }
                },
                error: function(xhr) {
                    console.error('Error fetching classes:', xhr);
                    classSelect.html('<option value="">Error loading classes</option>').prop('disabled', true);
                    internSelect.html('<option value="">-- Select Student --</option>').prop('disabled', true);
                }
            });
        }
    });

    // Handle class selection
    classSelect.on('change', function() {
        const classId = $(this).val();
        const schoolId = schoolSelect.val();

        // Reset intern select
        internSelect.html('<option value="">-- Select Student --</option>').prop('disabled', true);

        if (classId && schoolId) {
            // Show loading state
            internSelect.html('<option value="">Loading students...</option>').prop('disabled', true);

            // Fetch students for selected class and school
            $.ajax({
                url: `/training/api/schools/${schoolId}/interns`,
                method: 'GET',
                data: { class_id: classId },
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(students) {
                    populateStudents(students);
                },
                error: function(xhr) {
                    console.error('Error fetching students:', xhr);
                    alert('Error loading students. Please try again.');
                    internSelect.html('<option value="">Error loading students</option>');
                }
            });
        }
    });

    // Initialize form state if there are old values
    if (schoolSelect.val()) {
        schoolSelect.trigger('change');
        if (classSelect.val()) {
            classSelect.trigger('change');
        }
    }

    // Add input validation for grade fields
    $('input[type="number"]').on('input', function() {
        let value = parseInt($(this).val());
        if (value < 1) $(this).val(1);
        if (value > 4) $(this).val(4);
        calculateFinalGrade();
    });
});

// Function to calculate final grade
function calculateFinalGrade() {
    const ictLearning = parseInt($('#ict_learning').val()) || 0;
    const centurySkills = parseInt($('#century_skills').val()) || 0;
    const outputs = parseInt($('#outputs').val()) || 0;

    // Validate input ranges
    if (ictLearning < 1 || ictLearning > 4 || 
        centurySkills < 1 || centurySkills > 4 || 
        outputs < 1 || outputs > 4) {
        $('#final_grade').val('');
        return;
    }

    // Calculate weighted average and round to nearest integer
    const finalGrade = Math.round(
        (ictLearning * 0.4) + 
        (centurySkills * 0.3) + 
        (outputs * 0.3)
    );
    
    // Update final grade input
    $('#final_grade').val(finalGrade);
}
</script>
@endpush

<style>
.create-submission-container {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
}

.page-header {
    text-align: center;
    margin-bottom: 2rem;
}

.page-header h1 {
    color: #2c3e50;
    font-size: 2rem;
    margin: 0;
    font-weight: 600;
}

.subtitle {
    color: #666;
    margin-top: 0.5rem;
    font-size: 1.1rem;
}

.form-card {
    background: white;
    border-radius: 10px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.card-header {
    background: #f8f9fa;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e9ecef;
}

.card-header h3 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.25rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.card-header h3 i {
    color: #22bbea;
}

.card-body {
    padding: 1.5rem;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.form-group {
    margin-bottom: 1rem;
}

.form-group label {
    display: block;
    margin-bottom: 0.5rem;
    color: #4a5568;
    font-weight: 500;
}

.form-control {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
    transition: all 0.2s;
}

.form-control:focus {
    outline: none;
    border-color: #22bbea;
    box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.1);
}

.form-control:disabled {
    background-color: #f8f9fa;
    cursor: not-allowed;
}

.grades-grid {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.grade-item {
    background: #f8f9fa;
    padding: 1.25rem;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
}

.grade-item label {
    display: block;
    margin-bottom: 0.75rem;
    color: #4a5568;
    font-weight: 500;
}

.grade-input-wrapper {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.grade-input-wrapper input {
    flex: 1;
}

.grade-weight {
    color: #666;
    font-size: 0.9rem;
    white-space: nowrap;
}

.final-grade-display {
    text-align: center;
    margin-top: 2rem;
}

.final-grade-box {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    background: #22bbea;
    padding: 1rem 2rem;
    border-radius: 8px;
    color: white;
}

.final-grade-box label {
    font-weight: 600;
    font-size: 1.1rem;
    margin: 0;
    color: white;
}

.final-grade-box input {
    background: white;
    color: #2c3e50;
    font-size: 1.2rem;
    font-weight: 600;
    text-align: center;
    width: 100px;
    border: none;
    padding: 0.5rem;
    border-radius: 4px;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
}

.btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: none;
    text-decoration: none;
    font-size: 1rem;
}

.btn i {
    font-size: 1rem;
}

.btn-primary {
    background: #22bbea;
    color: white;
}

.btn-primary:hover {
    background: #1a9bc7;
}

.btn-secondary {
    background: #e2e8f0;
    color: #4a5568;
}

.btn-secondary:hover {
    background: #cbd5e0;
}

.error-message {
    color: #dc2626;
    font-size: 0.875rem;
    margin-top: 0.5rem;
    display: block;
}

.alert {
    padding: 1rem;
    border-radius: 6px;
    margin-bottom: 1.5rem;
}

.alert-error {
    background: #fee2e2;
    color: #dc2626;
    border: 1px solid #fecaca;
}

@media (max-width: 768px) {
    .create-submission-container {
        margin: 1rem;
        padding: 0;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .grade-input-wrapper {
        flex-direction: column;
        align-items: stretch;
    }

    .grade-weight {
        text-align: right;
    }

    .form-actions {
        flex-direction: column;
    }

    .btn {
        width: 100%;
        justify-content: center;
    }

    .final-grade-box {
        flex-direction: column;
        padding: 1rem;
    }

    .final-grade-box input {
        width: 100%;
    }
}
</style>
@endsection 