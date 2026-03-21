@extends('layouts.nav')

@section('content')


<link rel="stylesheet" href="{{ asset('css/training/classes/edit.css') }}">

<div class="page-container">
    <div class="header-section">
        <h2>Edit Class</h2>
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
                <small><i class="fas fa-info-circle"></i> Each student can only be enrolled in one class at a time. Please remove students from their current classes before adding them to this class.</small>
            </div>
        </div>
    @endif

    <form action="{{ route('training.classes.update', $class) }}" method="POST" class="form-container">
        @csrf
        @method('PUT')
        <input type="hidden" name="school_id" value="{{ $class->school_id }}">

        <div class="form-group">
            <label for="class_id">Class ID</label>
            <input type="text" id="class_id" name="class_id" value="{{ old('class_id', $class->class_id) }}" required>
            @error('class_id')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="class_name">Class Name</label>
            <input type="text" id="class_name" name="class_name" value="{{ old('class_name', $class->class_name) }}" required>
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
                    <div class="student-checkbox" data-batch="{{ $student->studentDetail->batch ?? '' }}">
                        <input type="checkbox" 
                            id="student_{{ $student->user_id }}" 
                            name="student_ids[]" 
                            value="{{ $student->user_id }}"
                            {{ (is_array(old('student_ids', $class->students->pluck('user_id')->toArray())) && 
                                in_array($student->user_id, old('student_ids', $class->students->pluck('user_id')->toArray()))) ? 'checked' : '' }}>
                        <label for="student_{{ $student->user_id }}">
                            {{ $student->user_id }} - {{ $student->user_fname }} {{ $student->user_mInitial }}. {{ $student->user_lname }}
                            <span class="batch-tag">{{ $student->studentDetail->batch ?? '' }}</span>
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
            <button type="submit" class="btn-submit">Update Class</button>
            <a href="{{ route('training.schools.show', ['school' => $class->school_id]) }}" class="btn-cancel">Cancel</a>
        </div>
    </form>
</div>

@endsection
