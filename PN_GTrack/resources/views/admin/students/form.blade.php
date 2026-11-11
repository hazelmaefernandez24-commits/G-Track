@extends('layouts.app')

@php
    $isEditing = isset($student);
    $nameParts = $isEditing ? (preg_split('/\s+/', trim($student->name ?? '')) ?: []) : [];
    $firstName = !empty($student->first_name) ? $student->first_name : ($nameParts[0] ?? '');
    $lastName = !empty($student->last_name) ? $student->last_name : (count($nameParts) > 1 ? end($nameParts) : '');
    $middleInitial = !empty($student->middle_initial) ? $student->middle_initial : (count($nameParts) > 2 ? substr($nameParts[1], 0, 1) : '');
@endphp

@section('title', $isEditing ? 'Edit Student' : 'Add Student')
@section('subtitle', $isEditing ? 'Update student information' : 'Create a student record')

@push('styles')
<style>
    .form-card {
        max-width: 760px;
        padding: 28px;
        background: #fff;
        border: 1px solid var(--border-color);
        border-radius: 16px;
        box-shadow: var(--card-shadow);
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
    }
    .form-group { margin-bottom: 16px; }
    .form-group label {
        display: block;
        margin-bottom: 6px;
        color: #334155;
        font-size: 13px;
        font-weight: 600;
    }
    .form-control {
        width: 100%;
        box-sizing: border-box;
        padding: 10px 12px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #fff;
        color: var(--text-main);
        font-size: 14px;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--sidebar-active);
        box-shadow: 0 0 0 3px rgba(34, 187, 234, 0.15);
    }
    .form-help { color: var(--text-muted); font-size: 12px; }
    .alert-danger {
        margin-bottom: 20px;
        padding: 12px 16px;
        border: 1px solid #fecaca;
        border-radius: 8px;
        background: #fee2e2;
        color: #991b1b;
    }
    .alert-success {
        margin-bottom: 20px;
        padding: 12px 16px;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        background: #dcfce7;
        color: #166534;
    }
    .csv-import-card { margin-top: 24px; }
    .csv-import-form {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
        margin-top: 16px;
    }
    .csv-import-form input[type="file"] {
        max-width: 100%;
        padding: 8px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        background: #fff;
    }
    .csv-import-errors { margin: 8px 0 0; padding-left: 20px; }
    .form-actions { display: flex; gap: 10px; margin-top: 24px; }
    .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 10px 16px;
        border: 0;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        cursor: pointer;
    }
    .btn-primary { background: #22bbea; color: #fff; }
    .btn-secondary { background: #e2e8f0; color: #475569; }
    @media (max-width: 640px) {
        .form-grid { grid-template-columns: 1fr; gap: 0; }
        .form-card { padding: 20px; }
        .form-actions { flex-direction: column; }
    }
</style>
@endpush

@section('content')
<div class="form-card">
    @if($errors->any() && !$errors->has('students_csv'))
        <div class="alert-danger" role="alert">
            <ul style="margin:0; padding-left:20px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ $isEditing ? route('students.update', $student) : route('students.store') }}" method="POST">
        @csrf
        @if($isEditing)
            @method('PUT')
            <div class="form-group">
                <label for="student_id">Student ID</label>
                <input id="student_id" class="form-control" value="{{ $student->student_id }}" readonly>
            </div>
        @else
            <p class="form-help" style="margin:0 0 20px;">The Student ID is generated automatically when the student is saved.</p>
        @endif

        <div class="form-grid">
            <div class="form-group">
                <label for="first_name">First Name</label>
                <input id="first_name" type="text" name="first_name" class="form-control" value="{{ old('first_name', $firstName) }}" required>
            </div>
            <div class="form-group">
                <label for="middle_initial">Middle Initial</label>
                <input id="middle_initial" type="text" name="middle_initial" class="form-control" maxlength="1" placeholder="Optional" value="{{ old('middle_initial', $middleInitial) }}">
            </div>
            <div class="form-group">
                <label for="last_name">Last Name</label>
                <input id="last_name" type="text" name="last_name" class="form-control" value="{{ old('last_name', $lastName) }}" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" class="form-control" value="{{ old('email', $student->email ?? '') }}" required>
            </div>
            <div class="form-group">
                <label for="class">Class</label>
                <select id="class" name="class" class="form-control" required>
                    <option value="" disabled {{ old('class', $student->class ?? '') === '' ? 'selected' : '' }}>Select Class</option>
                    @foreach($classes as $classOption)
                        <option value="{{ $classOption->name }}" {{ old('class', $student->class ?? '') === $classOption->name ? 'selected' : '' }}>{{ $classOption->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender" class="form-control" required>
                    @foreach(['Male', 'Female'] as $genderOption)
                        <option value="{{ $genderOption }}" {{ old('gender', $student->gender ?? 'Male') === $genderOption ? 'selected' : '' }}>{{ $genderOption }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="contact">Contact</label>
                <input id="contact" type="tel" name="contact" class="form-control" value="{{ old('contact', $student->contact ?? '') }}" required>
            </div>
        </div>

        @if($isEditing)
            <hr style="border:0; border-top:1px solid var(--border-color); margin:8px 0 20px;">
            <h2 style="margin:0 0 16px; font-size:16px;">Change Student Password</h2>
            <p class="form-help" style="margin:-8px 0 16px;">Leave these fields blank to keep the current password.</p>
            <div class="form-grid">
                <div class="form-group">
                    <label for="current_password">Current Password</label>
                    <input id="current_password" type="password" name="current_password" class="form-control" autocomplete="current-password">
                </div>
                <div class="form-group">
                    <label for="new_password">New Password</label>
                    <input id="new_password" type="password" name="new_password" class="form-control" autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="new_password_confirmation">Confirm New Password</label>
                    <input id="new_password_confirmation" type="password" name="new_password_confirmation" class="form-control" autocomplete="new-password">
                </div>
            </div>
        @else
            <div class="form-grid">
                <div class="form-group">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" class="form-control" required autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label for="password_confirmation">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control" required autocomplete="new-password">
                </div>
            </div>
        @endif

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">{{ $isEditing ? 'Update Student' : 'Save Student' }}</button>
            <a href="{{ route('students.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

@unless($isEditing)
<div class="form-card csv-import-card">
    <h2 style="margin:0 0 8px; font-size:18px;">Add Multiple Students with CSV</h2>
    <p class="form-help" style="margin:0; line-height:1.6;">
        Upload a CSV file to add multiple students at once.
    </p>

    @if(session('csv_import'))
        <div class="{{ session('csv_import.skipped') ? 'alert-danger' : 'alert-success' }}" role="status" style="margin-top:20px;">
            Imported {{ session('csv_import.imported') }} student(s); skipped {{ session('csv_import.skipped') }} row(s).
            @if(session('csv_import.errors'))
                <ul class="csv-import-errors">
                    @foreach(session('csv_import.errors') as $rowError)
                        <li>{{ $rowError }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @if($errors->has('students_csv'))
        <div class="alert-danger" role="alert" style="margin-top:20px;">
            {{ $errors->first('students_csv') }}
        </div>
    @endif

    <form class="csv-import-form" action="{{ route('students.import') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <input type="file" name="students_csv" accept=".csv,text/csv" required aria-label="Choose student CSV file">
        <button type="submit" class="btn btn-primary">Upload CSV</button>
    </form>
</div>
@endunless
@endsection
