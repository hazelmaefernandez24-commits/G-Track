@extends('layouts.nav')

@section('content')

<link rel="stylesheet" href="{{ asset('css/training/classes/create.css') }}">

<div class="page-container">
    <div class="header-section">
        <h2>Create New Class</h2>
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
                <small><i class="fas fa-info-circle"></i> Each student can only be enrolled in one class at a time. Please remove students from their current classes before adding them to a new class.</small>
            </div>
        </div>
    @endif

    <form action="{{ route('training.classes.store') }}" method="POST" class="form-container">
        @csrf
        <input type="hidden" name="school_id" value="{{ $school->school_id }}">

        <div class="form-group">
            <label for="class_id">Class ID</label>
            <input type="text" id="class_id" name="class_id" value="{{ old('class_id') }}" required>
            @error('class_id')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="class_name">Class Name</label>
            <input type="text" id="class_name" name="class_name" value="{{ old('class_name') }}" required>
            @error('class_name')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="student_ids">Select Students</label>
            <div class="filter-section">
                <select id="batchFilter" class="form-select">
                    <option value="">All Batches</option>
                    @foreach($students->pluck('studentDetail.batch')->unique() as $batch)
                        <option value="{{ $batch }}">{{ $batch }}</option>
                    @endforeach
                </select>
            </div>
            <div class="students-container">
                @foreach($students as $student)
                    <div class="student-checkbox" data-batch="{{ $student->studentDetail->batch }}">
                        <input type="checkbox" 
                            id="student_{{ $student->user_id }}" 
                            name="student_ids[]" 
                            value="{{ $student->user_id }}"
                            {{ (is_array(old('student_ids')) && in_array($student->user_id, old('student_ids'))) ? 'checked' : '' }}>
                        <label for="student_{{ $student->user_id }}">
                            {{ $student->user_id }} - {{ $student->user_fname }} {{ $student->user_mInitial }}. {{ $student->user_lname }}
                            <span class="batch-tag">{{ $student->batch }}</span>
                        </label>
                    </div>
                @endforeach
            </div>
            @error('student_ids')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const batchFilter = document.getElementById('batchFilter');
            const studentCheckboxes = document.querySelectorAll('.student-checkbox');

            batchFilter.addEventListener('change', function() {
                const selectedBatch = this.value;
                
                studentCheckboxes.forEach(checkbox => {
                    if (!selectedBatch || checkbox.dataset.batch === selectedBatch) {
                        checkbox.style.display = 'flex';
                    } else {
                        checkbox.style.display = 'none';
                    }
                });
            });
        });
        </script>

        <div class="form-actions">
            <button type="submit" class="btn-submit">Create Class</button>
            <a href="{{ route('training.schools.show', ['school' => $school->school_id]) }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>



<script>
document.getElementById('batch').addEventListener('change', function() {
    const batchId = this.value;
    const studentsContainer = document.getElementById('students-container');
    
    if (!batchId) {
        studentsContainer.innerHTML = '';
        return;
    }

    // Fetch students for the selected batch
    fetch(`/training/batches/${batchId}/students`)
        .then(response => response.json())
        .then(students => {
            studentsContainer.innerHTML = students.map(student => `
                <div class="student-item">
                    <input type="checkbox" 
                           name="student_ids[]" 
                           value="${student.id}" 
                           id="student_${student.id}">
                    <label for="student_${student.id}">
                        ${student.student_id} - ${student.name}
                    </label>
                </div>
            `).join('');
        })
        .catch(error => {
            console.error('Error fetching students:', error);
            studentsContainer.innerHTML = '<p class="error-message">Error loading students</p>';
        });
});
</script>
@endsection
