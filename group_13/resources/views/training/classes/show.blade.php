@extends('layouts.nav')

@section('content')

<link rel="stylesheet" href="{{ asset('css/training/classes/show.css') }}">
<div class="page-container">
    <div class="header-section">
        <a href="javascript:history.back()" class="back-button">
            <i class="fas fa-arrow-left"></i> Back
        </a>
        <h2>Class Details</h2>
        <a href="{{ route('training.schools.show', ['school' => $class->school->school_id]) }}" class="btn-back">
            <!-- <i class="fas fa-arrow-left"></i>--> Go to School page
        </a>
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

    <div class="content-section">
        <div class="class-details card">
            <h3>Class Information</h3>
            <div class="info-grid">
                <div class="info-item">
                    <label>Class ID:</label>
                    <span>{{ $class->class_id }}</span>
                </div>
                <div class="info-item">
                    <label>Class Name:</label>
                    <span>{{ $class->class_name }}</span>
                </div>
                <div class="info-item">
                    <label>School:</label>
                    <span>{{ $class->school->name }}</span>
                </div>
                <div class="info-item">
                    <label>Department:</label>
                    <span>{{ $class->school->department }}</span>
                </div>
                <div class="info-item">
                    <label>Course:</label>
                    <span>{{ $class->school->course }}</span>
                </div>
            </div>
        </div>

        <div class="students-list card">
            <h3>Students List</h3>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student ID</th>
                            <th>Name</th>
                            <th>Student Number</th>
                            <th>Training Code</th>
                            <th>Group</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($class->students as $student)
                            <tr>
                                <td>{{ $student->studentDetail->student_id ?? 'N/A' }}</td>
                                <td>{{ $student->user_fname }} {{ $student->user_mInitial }}. {{ $student->user_lname }}</td>
                                <td>{{ $student->studentDetail->student_number }}</td>
                                <td>{{ $student->studentDetail->training_code }}</td>
                                <td>{{ $student->studentDetail->group }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No students assigned to this class.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
