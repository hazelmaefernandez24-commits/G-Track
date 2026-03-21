@extends('layouts.nav')

@section('content')
<div class="create-submission-container">
    <div class="page-header">
        <h1>Edit Intern Grade</h1>
        <p class="subtitle">Update the intern's evaluation grades</p>
    </div>
    
    @if (session('error'))
        <div class="alert alert-error">
            {{ session('error') }}
        </div>
    @endif

    <form action="{{ route('training.intern-grades.update', $internGrade->id) }}" method="POST" class="submission-form">
        @csrf
        @method('PUT')

        <div class="form-section">
            <h3>Intern Information</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Student ID:</label>
                    <div class="form-text-display">{{ $internGrade->intern->studentDetail->student_id ?? 'N/A' }}</div>
                </div>

                <div class="form-group">
                    <label>Student Name:</label>
                    <div class="form-text-display">{{ $internGrade->intern->user_fname }} {{ $internGrade->intern->user_lname }}</div>
                </div>

                <div class="form-group">
                    <label for="company_name">Company:</label>
                    <input type="text"
                           name="company_name"
                           id="company_name"
                           value="{{ old('company_name', $internGrade->company_name) }}"
                           required
                           placeholder="Enter company name">
                    @error('company_name')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Evaluation Grades</h3>
            <div class="grades-grid">
                <div class="grade-item">
                    <label for="ict_learning">ICT Learning Competency:</label>
                    <div class="grade-input-wrapper">
                        <input type="number" 
                               name="grades[ict_learning_competency]" 
                               id="ict_learning"
                               value="{{ old('grades.ict_learning_competency', $internGrade->ict_learning_competency) }}"
                               min="1"
                               max="4"
                               step="1"
                               required
                               onchange="calculateFinalGrade()"
                               onkeypress="return event.charCode >= 49 && event.charCode <= 52">
                        <span class="grade-weight">(40%)</span>
                    </div>
                    @error('grades.ict_learning_competency')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grade-item">
                    <label for="century_skills">21st Century Skills:</label>
                    <div class="grade-input-wrapper">
                        <input type="number" 
                               name="grades[twenty_first_century_skills]" 
                               id="century_skills"
                               value="{{ old('grades.twenty_first_century_skills', $internGrade->twenty_first_century_skills) }}"
                               min="1"
                               max="4"
                               step="1"
                               required
                               onchange="calculateFinalGrade()"
                               onkeypress="return event.charCode >= 49 && event.charCode <= 52">
                        <span class="grade-weight">(30%)</span>
                    </div>
                    @error('grades.twenty_first_century_skills')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>

                <div class="grade-item">
                    <label for="outputs">Expected Outputs/Deliverables:</label>
                    <div class="grade-input-wrapper">
                        <input type="number" 
                               name="grades[expected_outputs_deliverables]" 
                               id="outputs"
                               value="{{ old('grades.expected_outputs_deliverables', $internGrade->expected_outputs_deliverables) }}"
                               min="1"
                               max="4"
                               step="1"
                               required
                               onchange="calculateFinalGrade()"
                               onkeypress="return event.charCode >= 49 && event.charCode <= 52">
                        <span class="grade-weight">(30%)</span>
                    </div>
                    @error('grades.expected_outputs_deliverables')
                        <span class="error-message">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <div class="final-grade-display">
                <div class="final-grade-box">
                    <label>Final Grade:</label>
                    <input type="text" 
                           id="final_grade" 
                           value="{{ $internGrade->final_grade }}" 
                           readonly>
                </div>
                <div class="status-display mt-4">
                    <label>Status:</label>
                    <span class="status-badge 
                        @if($internGrade->status === 'Fully Achieved') status-fully-achieved
                        @elseif($internGrade->status === 'Partially Achieved') status-partially-achieved
                        @elseif($internGrade->status === 'Barely Achieved') status-barely-achieved
                        @elseif($internGrade->status === 'No Achievement') status-no-achievement
                        @else status-unknown
                        @endif">
                        {{ $internGrade->status }}
                    </span>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Additional Information</h3>
            <div class="form-group">
                <label for="remarks">Remarks:</label>
                <textarea name="remarks" 
                          id="remarks" 
                          rows="3" 
                          placeholder="Enter any additional remarks about the evaluation">{{ old('remarks', $internGrade->remarks) }}</textarea>
                @error('remarks')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Update Grade
            </button>
            <a href="{{ route('training.intern-grades.index') }}" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </form>
</div>

@push('scripts')
<script>
function calculateFinalGrade() {
    const ictLearning = parseInt(document.getElementById('ict_learning').value) || 0;
    const centurySkills = parseInt(document.getElementById('century_skills').value) || 0;
    const outputs = parseInt(document.getElementById('outputs').value) || 0;

    // Validate input ranges
    if (ictLearning < 1 || ictLearning > 4 || 
        centurySkills < 1 || centurySkills > 4 || 
        outputs < 1 || outputs > 4) {
        document.getElementById('final_grade').value = '';
        return;
    }

    // Calculate weighted average
    const finalGrade = Math.round(
        (ictLearning * 0.4) + 
        (centurySkills * 0.3) + 
        (outputs * 0.3)
    );
    
    document.getElementById('final_grade').value = finalGrade;
}

// Add input validation for grade fields
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', function() {
        let value = parseInt(this.value);
        if (value < 1) this.value = 1;
        if (value > 4) this.value = 4;
        calculateFinalGrade();
    });
});

// Calculate initial final grade
calculateFinalGrade();
</script>
@endpush

<style>
.create-submission-container {
    max-width: 1000px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.page-header {
    margin-bottom: 2rem;
    text-align: center;
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

.form-section {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.form-section h3 {
    color: #2c3e50;
    margin: 0 0 1.5rem 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
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

.form-group select,
.form-group input[type="number"],
.form-group input[type="text"],
.form-group textarea {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-group select:focus,
.form-group input:focus,
.form-group textarea:focus {
    outline: none;
    border-color: #22bbea;
    box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.1);
}

.form-group select:disabled,
.form-group input:disabled {
    background-color: #f1f5f9;
    cursor: not-allowed;
}

.grades-grid {
    display: grid;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.grade-item {
    background: white;
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
    margin-top: 2rem;
    text-align: center;
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

.status-display {
    margin-top: 1rem;
    text-align: center;
}

.status-display label {
    display: block;
    margin-bottom: 0.5rem;
    color: #4a5568;
    font-weight: 500;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 9999px;
    font-weight: 600;
    font-size: 0.875rem;
}

.status-fully-achieved {
    background-color: #dcfce7;
    color: #166534;
}

.status-partially-achieved {
    background-color: #dbeafe;
    color: #1e40af;
}

.status-barely-achieved {
    background-color: #fef9c3;
    color: #854d0e;
}

.status-no-achievement {
    background-color: #fee2e2;
    color: #991b1b;
}

.status-unknown {
    background-color: #f3f4f6;
    color: #4b5563;
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
        padding: 1rem;
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
}
</style>
@endsection 