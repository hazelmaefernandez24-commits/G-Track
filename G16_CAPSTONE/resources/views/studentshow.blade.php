@extends('layout')

@section('content')
<div class="container my-5">
    <h2 class="text-center mb-4">Student Assigned</h2>
    <div class="d-flex justify-content-end mb-2">
        <a href="{{ route('generalTask') }}" class="btn btn-secondary">Back</a>
    </div>
    <div class="card p-4 mx-auto" style="max-width: 700px;">
        <div class="text-center mb-3">
            <h4>{{ $studentAssignment['student'] ?? 'Student' }}</h4>
            <div>
                <strong>Coordinator:</strong> {{ $studentAssignment['coordinator'] ?? '-' }}<br>
                <strong>Assigned Task:</strong> {{ $studentAssignment['category'] ?? '-' }}
            </div>
        </div>
        <h5>Assigned Task Details</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Category</th>
                    <th>Week Assigned</th>
                    <th>Rotation Cycle</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $studentAssignment['category'] ?? '-' }}</td>
                    <td>{{ $studentAssignment['week'] ?? '-' }}</td>
                    <td>{{ $studentAssignment['rotation_cycle'] ?? '-' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection