@extends('layouts.nav')

@section('content')
<link rel="stylesheet" href="{{ asset('css/training/school.css') }}">

<div class="page-container">
    <div class="header-section">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>

    <h1 style="font-weight: 300;">School Details</h1>
    <hr>

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

    <div class="school-details-card">
        <h3>School Information</h3>
        <div class="detail-row">
            <span class="label">School ID:</span>
            <span class="value">{{ $school->school_id }}</span>
        </div>
        <div class="detail-row">
            <span class="label">School Name:</span>
            <span class="value">{{ $school->name }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Department:</span>
            <span class="value">{{ $school->department }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Course:</span>
            <span class="value">{{ $school->course }}</span>
        </div>
        <div class="detail-row">
            <span class="label">No. of Semester:</span>
            <span class="value">{{ $school->semester_count }}</span>
        </div>
        <div class="detail-row">
            <span class="label">Terms:</span>
            <span class="value">
                <ul class="terms-list">
                    @foreach($school->terms as $term)
                        <li>{{ $term }}</li>
                    @endforeach
                </ul>
            </span>
        </div>
        <div class="detail-row">
            <span class="label">Grade Ranges:</span>
            <span class="value">
                <div class="grade-ranges">
                    <div class="grade-range passing">
                        <span class="grade-label">Passing:</span>
                        <span class="grade-value">{{ number_format($school->passing_grade_min, 1) }} - {{ number_format($school->passing_grade_max, 1) }}</span>
                    </div>
                    <div class="grade-range failing">
                        <span class="grade-label">Failing:</span>
                        <span class="grade-value">
                            @if($school->passing_grade_min == 1.0)
                                {{ number_format($school->passing_grade_max + 0.1, 1) }} - 5.0
                            @else
                                1.0 - {{ number_format($school->passing_grade_min - 0.1, 1) }}
                            @endif
                        </span>
                    </div>
                </div>
            </span>
        </div>
    </div>

    <div class="school-details-card">
        <h3>Subjects</h3>
        <div class="subjects-table-container">
            <div class="subjects-table-header">
                <div class="header-cell">Subject Name</div>
                <div class="header-cell">Offer Code</div>
                <div class="header-cell">Instructor</div>
                <div class="header-cell">Schedule</div>
            </div>
            @forelse($school->subjects as $subject)
                @if(is_object($subject))
                    <div class="subjects-table-row">
                        <div class="cell">{{ $subject->name }}</div>
                        <div class="cell">{{ $subject->offer_code }}</div>
                        <div class="cell">{{ $subject->instructor }}</div>
                        <div class="cell">{{ $subject->schedule }}</div>
                    </div>
                @else
                    <div class="subjects-table-row">
                        <div class="cell" colspan="4">Invalid subject data</div>
                    </div>
                @endif
            @empty
                <div class="subjects-table-row">
                    <div class="cell" colspan="4">No subjects found.</div>
                </div>
            @endforelse
        </div>
    </div>

    <div class="school-details-card">
        <h3>Classes</h3>
        <div class="table-wrapper">
            <div class="table-header">
                <div class="header-cell">Class ID</div>
                <div class="header-cell">Class Name</div>
                <div class="header-cell">No. of Students</div>
                <div class="header-cell">Actions</div>
            </div>
            @forelse($classes as $class)
                @if(is_object($class))
                    <div class="table-row">
                        <div class="cell">{{ $class->class_id }}</div>
                        <div class="cell">{{ $class->class_name }}</div>
                        <div class="cell">{{ $class->students->count() }}</div>
                        <div class="cell">
                            <div class="action-buttons">
                                <a href="{{ route('training.classes.show', $class) }}" class="btn-icon" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('training.classes.edit', $class) }}" class="btn-icon" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="table-row">
                        <div class="cell" colspan="4">Invalid class data</div>
                    </div>
                @endif
            @empty
                <div class="table-row">
                    <div class="cell" colspan="4">No classes found.</div>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
